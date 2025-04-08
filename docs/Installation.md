### You can install the package via composer:

```bash
composer require solution-forest/inspirecms-core
```

### Run install command:
With sample data:
```bash
php artisan inspirecms:install
```

Without sample data:
```bash
php artisan inspirecms:install --skip-samples
```

Optional: Install required default data:
```bash
php artisan inspirecms:import-default-data
```

### Start schedule job
Execute the schedule command to run scheduled jobs:
```bash
php artisan schedule:work
```


### Accessing the Admin Panel

Once configured, you can access the admin panel at `/cms` (or your configured prefix).

### Create a Super Admin User

After completing the installation process, you'll need to set up the first administrator account:

1. Navigate to the `/cms` route in your browser
2. You'll be presented with an installation page
3. Fill in the required information:
    - Your name
    - Email address
    - Password
4. Submit the form to create your account
5. The system will automatically assign super admin privileges to this initial user

This account will have full administrative access to manage the CMS.