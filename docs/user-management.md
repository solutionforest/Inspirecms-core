---
title: User Management
slug: user-management
path: docs/v1/user-management
uri: /docs/v1/user-management
heading: User Management
brief: InspireCMS provides a comprehensive system for managing users, roles, and permissions
quick_links: []
---

## Overview

The user management system in InspireCMS is built on:

-   A flexible authentication system
-   Role-based access control
-   Granular permissions
-   User profiles and preferences

---

## User Roles

### Managing Roles

Access role management through: **Admin Panel** > **Users** > **Roles**

![UserRole](https://inspirecms.net/storage/doc/pMu77FONCvIUlvZyOVwMg1xt5H9ETiOJ6V3PP2nW.png)

From here you can:

-   Create new roles
-   Edit existing roles
-   Assign permissions to roles
-   Delete roles

### Creating a Custom Role (only on Pro)

1. Navigate to **Users** > **Roles** in the admin panel
2. Click **Create Role**
3. Fill in the form:
    - **Name**: Unique identifier for the role (e.g., "Marketing")
    - **Guard Name**: Set to "inspirecms" (this is the default guard used by InspireCMS, which can configure on `config/inspirecms.php`)
    - **Permissions**: Select the permissions for this role
4. Click **Save** to create the role

---

## Permissions

### Permission Structure

Permissions follow a standard naming convention:

```plaintext
[resource].[action]
```

For example:

-   `content.view`: Ability to view content
-   `content.create`: Ability to create content

---

## Managing Users

Access user management through: **Users** > **Users**

![User](https://inspirecms.net/storage/doc/GnGnaJhgLji2Qv3JUYAIjnelj1WxQonlw6m0E5Y6.png)

### User Operations

From the users section, you can:

-   **View Users**: See all registered users in the system
-   **Create Users**: Add new user accounts manually
-   **Edit Users**: Modify user information and roles
-   **Delete Users**: Remove user accounts from the system

### User Authentication

InspireCMS supports various authentication features:

-   Password-based login
-   Password reset functionality
-   Remember me capability
-   Account lockout after failed attempts

### Configuring Authentication

Authentication settings can be modified in `config/inspirecms.php`, please reference on [configuration documentation](./configuration#content-authentication){.doc-link}

---

## User Profiles

Each user has a profile that includes:

-   Basic information (name, email)
-   Profile picture
-   Role assignments
-   Last login information

Users can edit their own profiles through the user menu: **User Menu (top-right)** > **Profile**

---

## Custom User Authentication

To use a custom user provider:

```php {title="config/inspirecms.php"}
'auth' => [
    'provider' => [
        'name' => 'cms_users',
        'driver' => 'eloquent',
        'model' => \App\Models\CmsUser::class, // Your custom user model
    ],
],
//...
'models' => [
    'fqcn' => [
        'user' => \App\Models\CmsUser::class,
    ],
],
```

Ensure your custom user model implements required interfaces:

```php
namespace App\Models;

use SolutionForest\InspireCms\Models\Contracts\User as UserContract;
use SolutionForest\InspireCms\Models\User as Authenticatable;

class CmsUser extends Authenticatable implements UserContract
{
    // Your custom implementation...
}
```
