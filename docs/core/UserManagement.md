# User Management

InspireCMS provides a comprehensive system for managing users, roles, and permissions. This guide explains how to work with users in the system and configure access controls.

## User System Overview

The user management system in InspireCMS is built on:

- A flexible authentication system
- Role-based access control
- Granular permissions
- User profiles and preferences

## User Roles

InspireCMS comes with several predefined roles:

1. **Super Admin**: Full access to all system features
2. **Administrator**: Can manage content and users but with some limitations
3. **Editor**: Can create and edit content but cannot modify system settings
4. **Author**: Can create content but typically only edit their own work
5. **Viewer**: Read-only access to content

### Managing Roles

Access role management through:

```
Admin Panel → Users → Roles
```

From here you can:

- Create new roles
- Edit existing roles
- Assign permissions to roles
- Delete roles (except system roles)

### Creating a Custom Role

1. Navigate to **Users → Roles** in the admin panel
2. Click **Create Role**
3. Fill in the form:
    - **Name**: Unique identifier for the role (e.g., "Marketing")
    - **Guard Name**: Set to "inspirecms" (this is the default guard used by InspireCMS)
    - **Permissions**: Select the permissions for this role
4. Click **Save** to create the role

## Permissions System

InspireCMS uses a granular permission system that controls access to specific actions:

- Content permissions: view, create, edit, publish, delete
- User permissions: view, create, edit, delete
- System permissions: manage settings, access system tools

### Permission Structure

Permissions follow a standard naming convention:

```
[resource].[action]
```

For example:
- `content.view`: Ability to view content
- `content.create`: Ability to create content
- `settings.update`: Ability to modify system settings

### Customizing Permissions

You can add custom permissions directly using [Spatie's Permission](https://spatie.be/docs/laravel-permission/v6/introduction) package:

```php
use Spatie\Permission\Models\Permission;

// In a service provider or seeder
Permission::create(['name' => 'manage_product_inventory', 'guard_name' => 'inspirecms']);
```

You can also create permissions in a database seeder:

```php
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Create permissions
        $permissions = [
            'manage_product_inventory',
            'view_analytics',
            'export_reports',
        ];
        
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'inspirecms']);
        }
        
        // Optionally assign to existing roles
        $adminRole = Role::findByName('Administrator', 'inspirecms');
        $adminRole->givePermissionTo('manage_product_inventory');
    }
}
```

Run the seeder with:

```bash
php artisan db:seed --class=PermissionSeeder
```

### Using Permissions in Blade Templates

```php
@if(auth()->user()->can('manage_product_inventory'))
    <a href="{{ route('products.inventory') }}">Manage Inventory</a>
@endif
```

## User Management

Access user management through:

```
Admin Panel → Users → Users
```

### User Operations

From the users section, you can:

- **View Users**: See all registered users in the system
- **Create Users**: Add new user accounts manually
- **Edit Users**: Modify user information and roles
- **Block Users**: Temporarily prevent users from logging in
- **Delete Users**: Remove user accounts from the system

### Creating a New User

1. Navigate to **Users → Users**
2. Click **Create User**
3. Fill in the required information:
   - Name
   - Email
   - Password
   - Role assignment
4. Click **Save** to create the user

### User Authentication

InspireCMS supports various authentication features:

- Password-based login
- Password reset functionality
- Remember me capability
- Account lockout after failed attempts
- Optional email verification

### Configuring Authentication

Authentication settings can be modified in `config/inspirecms.php`:

```php
'auth' => [
    'guard' => [
        'name' => 'inspirecms',
        'driver' => 'session',
        'provider' => 'cms_users',
    ],
    'provider' => [
        'name' => 'cms_users',
        'driver' => 'eloquent',
        'model' => \SolutionForest\InspireCms\Models\User::class,
    ],
    'resetting_password' => [
        'enabled' => true,
        'name' => 'inspirecms',
        'provider' => 'cms_users',
        'table' => 'password_reset_tokens',
        'expire' => 60,
        'throttle' => 60,
    ],
    'failed_login_attempts' => 5,
    'lockout_duration' => 120, // minutes
],
```

## User Profiles

Each user has a profile that includes:

- Basic information (name, email)
- Profile picture
- Role assignments
- Last login information
- Activity history

Users can edit their own profiles through the user menu:

```
User Menu (top-right) → Profile
```

## Custom User Authentication

To use a custom user provider:

```php
// config/inspirecms.php
'auth' => [
    'provider' => [
        'name' => 'cms_users',
        'driver' => 'eloquent',
        'model' => \App\Models\CustomUser::class, // Your custom user model
    ],
],
//...
'models' => [
    'fqcn' => [
        'user' => \App\Models\CustomUser::class,
    ],
],
```

Ensure your custom user model implements required interfaces:

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use SolutionForest\InspireCms\Models\Concerns\CmsUserTrait;
use SolutionForest\InspireCms\Models\Contracts\CmsUser;

class CustomUser extends Authenticatable implements CmsUser
{
    use CmsUserTrait;
    use HasRoles;
    
    // Your custom implementation...
}
```

## Best Practices

- **Principle of Least Privilege**: Give users only the permissions they need
- **Role-Based Design**: Design roles based on job functions, not individuals
- **Regular Audits**: Review user accounts and permissions periodically
- **Password Policies**: Enforce strong passwords and regular changes
- **Activity Monitoring**: Review unusual login patterns or suspicious activity