<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $user_id
 * @property ?CarbonInterface $last_logged_in_at_utc
 * @property ?CarbonInterface $last_logged_out_at_utc
 * @property string $ip_address
 */
interface UserLoginActivity
{
    /**
     * Get the user associated with the login activity.
     *
     * @return BelongsTo
     */
    public function user();
}
