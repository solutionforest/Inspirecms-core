<?php

namespace SolutionForest\InspireCms\Licensing;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\MessageBag;
use SolutionForest\InspireCms\Events\Licensing\LicensesRefreshed;

class LicenseManager
{
    protected $outpost;

    public function __construct(Outpost $outpost)
    {
        $this->outpost = $outpost;
    }

    public function requestFailed()
    {
        return (bool) $this->requestErrorCode();
    }

    public function requestErrorCode()
    {
        return $this->response('error');
    }

    public function requestRateLimited()
    {
        return $this->requestErrorCode() === 429;
    }

    public function failedRequestRetrySeconds()
    {
        return $this->requestRateLimited()
            ? (int) Carbon::createFromTimestamp($this->response('expiry'), config('app.timezone'))->diffInSeconds(absolute: true)
            : null;
    }

    public function requestValidationErrors()
    {
        return new MessageBag($this->response('error') === 422 ? $this->response('errors') : []);
    }

    public function isOnPublicDomain()
    {
        return $this->response('public');
    }

    public function isOnTestDomain()
    {
        return ! $this->isOnPublicDomain();
    }

    public function valid()
    {
        return $this->coreValid();
    }

    public function invalid()
    {
        return ! $this->valid();
    }

    public function coreValid()
    {
        return $this->core()->valid();
    }

    public function coreNeedsRenewal()
    {
        return $this->core()->needsRenewal();
    }

    public function response($key = null, $default = null)
    {
        $response = $this->outpost->response();

        return $key ? Arr::get($response, $key, $default) : $response;
    }

    public function core()
    {
        return new CoreLicense($this->response('core'));
    }

    public function refresh()
    {
        $this->outpost->clearCachedResponse();

        event(new LicensesRefreshed);
    }

    public function usingLicenseKeyFile()
    {
        return $this->outpost->usingLicenseKeyFile();
    }
}
