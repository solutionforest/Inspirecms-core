<?php

namespace SolutionForest\InspireCms\Licensing;

use Illuminate\Database\Eloquent\SoftDeletingScope;
use SolutionForest\InspireCms\InspireCmsConfig;

/**
 * Enum representing the actions that can be restricted based on the license tier.
 */
enum LicenseTierAction
{
    case GlobalSearch;
    case CreateUser;
    case CreateRole;
    case RollbackContentVersion;

    private const RESTRICTED_ACTIONS_PRO = [
        self::GlobalSearch,
        self::CreateUser,
        self::CreateRole,
        self::RollbackContentVersion,
    ];

    private const RESTRICTED_ACTIONS_FREE = [
        self::CreateUser,
        self::CreateRole,
    ];

    public function isAllowed($tier = null): bool
    {
        $licenseManager = app(LicenseManager::class);

        $tier ??= $licenseManager->getLicenseTier();

        $preCheck = match ($tier) {
            'pro' => in_array($this, self::RESTRICTED_ACTIONS_PRO),
            'free' => in_array($this, self::RESTRICTED_ACTIONS_FREE),
            default => null,
        };

        if ($preCheck === false) {
            return false;
        }

        // Validate each action based on the tier
        switch ($this) {
            
            case self::CreateUser:
                $limitedUserCount = $licenseManager->getLimitedUserCount();
                if (is_null($limitedUserCount)) {
                    return true; // Unlimited users
                }
                $existingUserCount = InspireCmsConfig::getUserModelClass()::query()
                    ->withoutGlobalScope(SoftDeletingScope::class)
                    ->count();
                return $existingUserCount < $limitedUserCount;
                
            case self::CreateRole:
                $limitedRoleCount = $licenseManager->getLimitedRoleCount();
                if (is_null($limitedRoleCount)) {
                    return true; // Unlimited roles
                }
                $existingRoleCount = InspireCmsConfig::getRoleModelClass()::query()
                    ->withoutGlobalScope(SoftDeletingScope::class)
                    ->count();
                return $existingRoleCount < $limitedRoleCount;

            default:
                break;
        }

        return true;
    }
}
