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
   - **Guard Name**: Leave as "web" for most cases
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
[action]_[resource]
```

For example:
- `view_content`: Ability to view content
- `create_content`: Ability to create content
- `edit_settings`: Ability to modify system settings

### Customizing Permissions

You can add custom permissions by extending the permission system:

```php
use SolutionForest\InspireCms\Facades\PermissionManifest;
use SolutionForest\InspireCms\DataTypes\Manifest\PermissionOption;

// In a service provider
public function boot()
{
    PermissionManifest::addOption(
        new PermissionOption(
            name: 'manage_product_inventory',
            label: 'Manage Product Inventory',
            description: 'Allow users to manage product inventory levels'
        )
    );
}
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

### Customizing User Fields

To add custom fields to user profiles:

```php
use SolutionForest\InspireCms\Filament\Resources\UserResource;
use Filament\Forms\Components\TextInput;

// In a service provider
UserResource::form(function ($form) {
    $form->schema([
        // Original fields will be included automatically
        TextInput::make('department')
            ->label('Department')
            ->maxLength(100),
        TextInput::make('employee_id')
            ->label('Employee ID')
            ->maxLength(20),
    ]);
});
```

## Activity Monitoring

InspireCMS tracks user activity including:

- Login attempts (successful and failed)
- Content creation and modification
- System setting changes
- User management actions

View the activity log in:

```
Admin Panel → Dashboard → Activity
```

### User Login Security

Security features include:

- **IP Tracking**: Monitor login locations
- **Lockout**: Temporarily block accounts after multiple failed attempts
- **Session Management**: Control session lifetime and validation
- **Password Policies**: Enforce strong passwords

## API Authentication

For headless CMS implementations, InspireCMS supports API authentication:

- Token-based authentication
- Scoped API permissions
- Rate limiting

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

## User Import and Export

For bulk user management:

1. **Export Users**: Download user data in CSV/JSON format
2. **Import Users**: Bulk create users from a spreadsheet

Access these features in:

```
Admin Panel → Users → Import/Export
```

## Best Practices

- **Principle of Least Privilege**: Give users only the permissions they need
- **Role-Based Design**: Design roles based on job functions, not individuals
- **Regular Audits**: Review user accounts and permissions periodically
- **Password Policies**: Enforce strong passwords and regular changes
- **Activity Monitoring**: Review unusual login patterns or suspicious activity