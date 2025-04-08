# Navigation

Customize the admin panel navigation by defining menu items in your service provider.

Example:
```php
InspireCMS::setup()
    ->withMenuItems([
        'Dashboard' => '/admin/dashboard',
        'Content' => '/admin/content',
    ]);
```

You can also define nested menus and custom icons.