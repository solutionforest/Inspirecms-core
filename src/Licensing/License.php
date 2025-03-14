<?php

namespace SolutionForest\InspireCms\Licensing;

use Illuminate\Support\Arr;

abstract class License
{
    protected $response;

    public function __construct($response)
    {
        $this->response = $response;
    }

    public function response()
    {
        return $this->response;
    }

    public function valid()
    {
        return Arr::get($this->response, 'valid');
    }

    public function invalidReason()
    {
        if (! $reason = Arr::get($this->response, 'reason')) {
            return;
        }

        return __('inspirecms::messages.licensing_error_' . $reason);
    }
}
