<?php

namespace SolutionForest\InspireCms\Models\Contracts;

/**
 * @property int $id
 * @property string $user_id
 * @property ?\Carbon\CarbonInterface $last_logged_in_at_utc
 * @property ?\Carbon\CarbonInterface $last_logged_out_at_utc
 * @property string $ip_address
 */
interface UserLoginActivity
{
    /**
     * Get the user associated with the login activity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user();
}
