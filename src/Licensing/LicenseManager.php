<?php

namespace SolutionForest\InspireCms\Licensing;

use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use SolutionForest\InspireCms\Events\Licensing\LicensesRefreshed;
use SolutionForest\InspireCms\InspireCmsConfig;

class LicenseManager
{
    private const ENDPOINT = 'https://license.solutionforest.com';

    private const REQUEST_TIMEOUT = 5;

    private const CACHE_KEY_PREFIX = 'license:';

    private const SUPPORT_EMAIL = 'info@solutionforest.net';

    private const SUBSCRIPTION_URL = 'https://inspirecms.net/user';

    private $cacheManager;

    public function getLicenseKey()
    {
        return InspireCmsConfig::get('system.license.key');
    }

    /**
     * @return LicenseVerificationResult
     */
    public function verify()
    {
        // Check cache first to avoid frequent verifications
        $cacheKey = $this->buildCacheKey();

        if ($this->cache()->has($cacheKey)) {
            return $this->cache()->get($cacheKey);
        }

        // Verify the license offline first
        if (($offlineResult = $this->verifyOffline()) && $offlineResult->isSuccess()) {
            $this->cache()->put($cacheKey, $offlineResult, now()->addHours(24));

            return $offlineResult;
        }

        $failedReason = null;

        try {

            // Try to verify the license online first

            $payload = $this->payload();
            $response = Http::timeout(self::REQUEST_TIMEOUT)->post($this->fetchActionPath('validate'), $payload);

            if ($response->successful()) {

                $data = $response->json();

                if ($data['valid'] === true) {

                    $offlineData = array_merge($data['license'], $payload);
                    $offlineData['checksum'] = $this->calculateChecksum($offlineData);

                    // Save verification file for offline use
                    if (is_array($offlineData)) {
                        $this->saveLicenseFile(json_encode($offlineData, JSON_PRETTY_PRINT));
                    }

                    // Cache the result
                    $result = LicenseVerificationResult::successOnline($data['message'] ?? null, $data);
                    $this->cache()->put($cacheKey, $result, now()->addHours(24));

                    return $result;
                } else {
                    $failedReason = $data['reason'] ?? null;
                }
            }

        } catch (\Throwable $th) {

            logger()->warning('Failed to verify license online', ['exception' => $th]);

        }

        return LicenseVerificationResult::failureOnline(
            message: 'License verification failed',
            reason: $failedReason,
        );
    }

    public function refresh(): void
    {
        $this->cache()->forget($this->buildCacheKey());

        event(new LicensesRefreshed);
    }

    public function canUpgrade(): bool
    {
        $tier = $this->getLicenseTier();
        if (! $tier || ! is_string($tier)) {
            return true;
        }

        return $this->isFree();
    }

    public function getLimitedUserCount(): ?int
    {
        return match ($this->getLicenseTier()) {
            'pro' => null, // Pro tier has unlimited users
            default => 3,
        };
    }

    public function getLimitedRoleCount(): ?int
    {
        return match ($this->getLicenseTier()) {
            'pro' => null, // Pro tier has unlimited roles
            default => 1,
        };
    }

    public function canCreateUser(): bool
    {
        $limitedUserCount = $this->getLimitedUserCount();
        if (is_null($limitedUserCount)) {
            return true; // Unlimited users
        }
        $existingUserCount = InspireCmsConfig::getUserModelClass()::query()
            ->withoutGlobalScope(SoftDeletingScope::class)
            ->count();

        return $existingUserCount < $limitedUserCount;
    }

    public function canCreateRole(): bool
    {
        $limitedRoleCount = $this->getLimitedRoleCount();
        if (is_null($limitedRoleCount)) {
            return true; // Unlimited roles
        }
        $existingRoleCount = InspireCmsConfig::getRoleModelClass()::query()
            ->withoutGlobalScope(SoftDeletingScope::class)
            ->count();

        return $existingRoleCount < $limitedRoleCount;
    }

    public function getLicenseTier(): ?string
    {
        $licenseKey = $this->getLicenseKey();

        if (filled($licenseKey)) {
            try {

                $this->verify();

                $cacheKey = $this->buildCacheKey();

                if (($verificationResult = $this->cache()->get($cacheKey)) && $verificationResult instanceof LicenseVerificationResult) {
                    $data = $verificationResult->getData();

                    return data_get($data, 'meta.product_variant_slug', null);
                }

            } catch (\Throwable $th) {
                //
            }
        }

        return null;
    }

    private function isFree(): bool
    {
        return $this->getLicenseTier() === 'free';
    }

    public function usingLicenseKeyFile(): bool
    {
        return File::exists($this->licenseKeyPath());
    }

    public function getSupportEmail(): ?string
    {
        return self::SUPPORT_EMAIL;
    }

    public function getSubscriptionUrl(): ?string
    {
        return self::SUBSCRIPTION_URL;
    }

    /**
     * @return LicenseVerificationResult
     */
    protected function verifyOffline()
    {
        if (! $this->usingLicenseKeyFile()) {
            return LicenseVerificationResult::failureOffline(message: 'License file not found');
        }

        try {

            $licenseData = json_decode(File::get($this->licenseKeyPath()), true);

            return $this->dataVerification($licenseData) ?? LicenseVerificationResult::successOffline(data: $licenseData);

        } catch (\Throwable $th) {

            logger()->warning('Failed to read license file', ['exception' => $th]);

            return LicenseVerificationResult::failureOffline(message: 'Failed to read license file');

        }
    }

    protected function dataVerification($licenseData): ?LicenseVerificationResult
    {
        // Verify the license key is the same
        if ($licenseData['license_key'] !== $this->getLicenseKey()) {
            return LicenseVerificationResult::failureOffline(reason: 'The license key in the file does not match the configured license key');
        }

        // Verify the data is for the current domain
        if ($licenseData['domain'] !== $this->getCurrentDomain()) {
            return LicenseVerificationResult::failureOffline(reason: 'domain_mismatch');
        }

        // Verify the license is not expired
        if (Carbon::parse($licenseData['expiry_date'])->isPast(Carbon::now('UTC'))) {
            return LicenseVerificationResult::failureOffline(reason: 'expired');
        }

        // Verify the checksum
        if ($licenseData['checksum'] !== $this->calculateChecksum(Arr::except($licenseData, 'checksum'))) {
            return LicenseVerificationResult::failureOffline(reason: 'checksum mismatch');
        }

        return null;
    }

    protected function calculateChecksum(array $data): string
    {
        $checksumData = $data['license_key'] . $data['domain'] . $data['product_id'];

        return hash('sha256', $checksumData);
    }

    private function getCurrentDomain(): string
    {
        return request()->getHost();
    }

    private function buildCacheKey(): string
    {
        return self::CACHE_KEY_PREFIX . "verification_{$this->getLicenseKey()}_{$this->getCurrentDomain()}";
    }

    private function payload(): array
    {
        $data = [
            'license_key' => $this->getLicenseKey(),
            'domain' => $this->getCurrentDomain(),
            'product_id' => 'inspirecms-licenses',
        ];

        $dataForChecksum = Arr::only($data, [
            'license_key',
            'domain',
            'product_id',
        ]);

        $data['checksum'] = $this->calculateChecksum($dataForChecksum);

        return $data;
    }

    private function saveLicenseFile($fileContent)
    {
        File::put($this->licenseKeyPath(), $fileContent);
    }

    private function getSecretKey()
    {
        return InspireCmsConfig::get('system.license.secret');
    }

    private function licenseKeyPath()
    {
        return storage_path('app/license.lic');
    }

    /**
     * @return \Illuminate\Contracts\Cache\Repository
     */
    private function cache()
    {
        if ($this->cacheManager) {
            return $this->cacheManager;
        }

        try {
            $store = Cache::store('license');
        } catch (InvalidArgumentException $e) {
            $store = Cache::store();
        }

        return $this->cacheManager = $store;
    }

    private function fetchActionPath($action)
    {
        return str(self::ENDPOINT)
            ->rtrim('/')
            ->append('/')
            ->append(trim(trim($action), '/'))
            ->toString();
    }
}
