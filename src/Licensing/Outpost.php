<?php

namespace SolutionForest\InspireCms\Licensing;

use Carbon\CarbonInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Cache\NoLock;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use RuntimeException;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\InspireCmsConfig;

class Outpost
{
    const ENDPOINT = 'https://outpost.inspirecms.com/v1/query';

    const REQUEST_TIMEOUT = 5;

    const CACHE_KEY = 'inspirecms.outpost.response';

    const LOCK_KEY = 'inspirecms.outpost.lock';

    private $response;

    private $client;

    private $store;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function radio()
    {
        $this->response();
    }

    public function response()
    {
        return $this->response ?? $this->request();
    }

    private function request()
    {
        $lock = $this->lock(static::LOCK_KEY, 10);

        try {
            $lock->block(static::REQUEST_TIMEOUT);

            if ($this->hasCachedResponse()) {
                return $this->getCachedResponse();
            }

            return $this->performAndCacheRequest();
        } catch (ConnectException $e) {
            return $this->cacheAndReturnErrorResponse($e);
        } catch (RequestException $e) {
            return $this->handleRequestException($e);
        } catch (LockTimeoutException $e) {
            return $this->cacheAndReturnErrorResponse($e);
        } finally {
            $lock->release();
        }
    }

    private function performAndCacheRequest()
    {
        return $this->cacheResponse(now()->addHour(), $this->performRequest());
    }

    private function performRequest()
    {
        if ($this->usingLicenseKeyFile()) {
            return $this->licenseKeyFileResponse();
        }

        $response = $this->client->request('POST', self::ENDPOINT, [
            'headers' => ['accept' => 'application/json'],
            'json' => $this->payload(),
            'timeout' => self::REQUEST_TIMEOUT,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    private function licenseKeyFileResponse()
    {
        try {
            $encrypter = new Encrypter(InspireCmsConfig::get('license_key'));
            $decrypted = $encrypter->decrypt(File::get($this->licenseKeyPath()));
            $response = collect(json_decode($decrypted, true));

        } catch (DecryptException | RuntimeException $e) {
            return ['error' => 500];
        }

        return $response->toArray();
    }

    public function payload()
    {
        return [
            'key' => InspireCmsConfig::get('license_key'),
            'host' => request()->getHost(),
            'ip' => request()->server('SERVER_ADDR'),
            'port' => request()->server('SERVER_PORT'),
            'inspirecms_version' => InspireCms::version(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ];
    }

    private function cacheResponse(CarbonInterface $expiration, $contents)
    {
        $contents = array_merge($contents, [
            'expiry' => $expiration->timestamp,
            'payload' => $this->payload(),
        ]);

        $this->cache()->put(self::CACHE_KEY, $contents, $expiration);

        return $contents;
    }

    private function hasCachedResponse()
    {
        if (! $cached = $this->getCachedResponse()) {
            return false;
        }

        return ! $this->payloadHasChanged($cached['payload'], $this->payload());
    }

    private function payloadHasChanged($previous, $current)
    {
        $exclude = ['ip', 'php_version'];

        return Arr::except($previous, $exclude) !== Arr::except($current, $exclude);
    }

    private function getCachedResponse()
    {
        return $this->cache()->get(self::CACHE_KEY);
    }

    public function clearCachedResponse()
    {
        return $this->cache()->forget(self::CACHE_KEY);
    }

    private function handleRequestException(RequestException $e)
    {
        $code = $e->getCode();

        if ($code == 422) {
            return $this->cacheAndReturnValidationResponse($e);
        } elseif ($code == 429) {
            return $this->cacheAndReturnRateLimitResponse($e);
        }

        return $this->cacheAndReturnErrorResponse($e);
    }

    private function cacheAndReturnValidationResponse($e)
    {
        $json = json_decode($e->getResponse()->getBody()->getContents(), true);

        return $this->cacheResponse(now()->addHour(), [
            'error' => 422,
            'errors' => $json['errors'],
        ]);
    }

    private function cacheAndReturnRateLimitResponse($e)
    {
        $seconds = (int) $e->getResponse()->getHeader('Retry-After')[0];

        return $this->cacheResponse(now()->addSeconds($seconds), ['error' => 429]);
    }

    private function cacheAndReturnErrorResponse($e)
    {
        logger()->debug('Error contacting Outpost: ' . $e->getMessage());

        return $this->cacheResponse(now()->addMinutes(5), ['error' => $e->getCode()]);
    }

    private function cache()
    {
        if ($this->store) {
            return $this->store;
        }

        try {
            $store = Cache::store('outpost');
        } catch (InvalidArgumentException $e) {
            $store = Cache::store();
        }

        return $this->store = $store;
    }

    private function lock(string $key, int $seconds)
    {
        return $this->cache()->getStore() instanceof LockProvider
            ? $this->cache()->lock($key, $seconds)
            : new NoLock($key, $seconds);
    }

    public function usingLicenseKeyFile()
    {
        return File::exists($this->licenseKeyPath());
    }

    private function licenseKeyPath()
    {
        return storage_path('license.key');
    }
}
