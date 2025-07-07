<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Facades\PermissionManifest;
use SolutionForest\InspireCms\Helpers\AuthHelper;
use SolutionForest\InspireCms\Tests\Models\User;
use SolutionForest\InspireCms\Tests\TestCase;
use Spatie\Permission\Models\Role;

uses(TestCase::class);

describe('user management', function () {

    it('can create super admin user', function () {
        $this->createSuperAdminUser();

        $user = User::first();

        expect($user)->not->toBeNull();
        expect($user->name)->toBe('Super Admin');
        expect($user->email)->toBe('superadmin@example.com');
        expect($user->preferred_language)->toBe('en');
        expect($user->uuid)->not->toBeNull();
        expect(Str::isUuid($user->uuid))->toBeTrue();
        expect(Hash::check('password', $user->password))->toBeTrue();
    });

    it('assigns super admin role to created user', function () {
        $this->createSuperAdminUser();

        $user = User::first();
        $superAdminRoleName = PermissionManifest::getSuperAdminRoleName();
        $superAdminRole = Role::where('name', $superAdminRoleName)->first();

        expect($user->hasRole($superAdminRole))->toBeTrue();
    });

    it('can login as super admin', function () {
        $this->createSuperAdminUser();

        $response = $this->loginCmsPanelAsSuperAdmin();

        expect(Auth::guard(AuthHelper::guardName())->check())->toBeTrue();
        expect(Auth::guard(AuthHelper::guardName())->user())->toBeInstanceOf(User::class);
    });

});
