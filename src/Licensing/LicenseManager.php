<?php

namespace SolutionForest\InspireCms\Licensing;

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
    const ENDPOINT = 'https://license.solutionforest.com/validate';

    const REQUEST_TIMEOUT = 5;

    const CACHE_KEY_PREFIX = 'license:';

    private $cacheManager;

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

        try {

            // Try to verify the license online first

            $payload = $this->payload();
            $response = Http::timeout(self::REQUEST_TIMEOUT)->post(self::ENDPOINT, $payload);

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
                    $result = LicenseVerificationResult::successOnline($data['message'] ?? null);
                    $this->cache()->put($cacheKey, $result, now()->addHours(24));
    
                    return $result;
                }
            }

            // If online verification fails, fall back to offline verification
            return $this->verifyOffline();

        } catch (\Throwable $th) {

            logger()->warning('Failed to verify license online', ['exception' => $th]);

            // If online verification fails, try to verify the license offline
            return $this->verifyOffline();

        }
    }

    public function refresh(): void
    {
        $this->cache()->forget(self::CACHE_KEY_PREFIX . "verification_{$this->getLicenseKey()}_{$this->getCurrentDomain()}");

        event(new LicensesRefreshed);
    }

    public function usingLicenseKeyFile(): bool
    {
        return File::exists($this->licenseKeyPath());
    }

    /**
     * @return LicenseVerificationResult
     */
    protected function verifyOffline()
    {
        if (! $this->usingLicenseKeyFile()) {
            return LicenseVerificationResult::failureOffline('License file not found');
        }

        try {

            $licenseData = json_decode(File::get($this->licenseKeyPath()), true);

            return $this->dataVerification($licenseData) ?? LicenseVerificationResult::successOffline();

        } catch (\Throwable $th) {

            logger()->warning('Failed to read license file', ['exception' => $th]);

            return LicenseVerificationResult::failureOffline('Failed to read license file');

        }
    }

    protected function dataVerification($licenseData): ?LicenseVerificationResult
    {
        // Verify the license key is the same
        if ($licenseData['license_key'] !== $this->getLicenseKey()) {
            return LicenseVerificationResult::failureOffline('The license key in the file does not match the configured license key');
        }

        // Verify the data is for the current domain
        if ($licenseData['domain'] !== $this->getCurrentDomain()) {
            return LicenseVerificationResult::failureOffline('License file does not match the current domain');
        }

        // Verify the license is not expired
        if (Carbon::parse($licenseData['expiry_date'])->isPast(Carbon::now('UTC'))) {
            return LicenseVerificationResult::failureOffline('License expired');
        }

        // Verify the checksum
        if ($licenseData['checksum'] !== $this->calculateChecksum(Arr::except($licenseData, 'checksum'))) {
            return LicenseVerificationResult::failureOffline('License file verification failed due to checksum mismatch');
        }

        return null;
    }

    protected function getMachineId(): string
    {
        // Generate a unique identifier for this machine/installation
        if (function_exists('php_uname')) {
            return md5(php_uname());
        }

        // Fallback if php_uname is disabled
        return md5($_SERVER['HTTP_HOST'] . $_SERVER['SERVER_ADDR'] ?? '');
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
            'product_id' => 'cms',
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

    private function getLicenseKey()
    {
        return InspireCmsConfig::get('license.key');
    }

    private function getSecretKey()
    {
        return InspireCmsConfig::get('license.secret');
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
}
