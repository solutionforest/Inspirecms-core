<?php

namespace SolutionForest\InspireCms\Licensing;

use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Facades\InspireCms;

class CoreLicense extends License
{
    public function version()
    {
        return InspireCms::version();
    }

    public function needsRenewal()
    {
        return Arr::get($this->response, 'reason') === 'outside_license_range';
    }

    public function invalidReason()
    {
        if (Arr::get($this->response, 'reason') === 'outside_license_range') {
            [$start, $end] = $this->response['range'];

            return __('inspirecms::messages.licensing_error_outside_license_range', compact('start', 'end'));
        }

        return parent::invalidReason();
    }
}
