# Security Best Practices

Securing your InspireCMS installation is crucial to protect your content, user data, and site integrity. This guide outlines important security considerations and best practices for maintaining a secure InspireCMS website.

## Core Security Features

InspireCMS includes several built-in security features:

- Secure authentication system
- Role-based access control
- CSRF protection
- XSS mitigation
- SQL injection prevention
- Input validation
- Secure password handling
- Session management
- Rate limiting

## Environment Configuration

### Secure .env File

Your `.env` file contains sensitive information and should be protected:

```bash
# Never commit .env to version control
echo ".env" >> .gitignore

# Use proper production values
APP_ENV=production
APP_DEBUG=false
APP_KEY=[64-character-random-string]

# Set proper permissions
chmod 600 .env
```

### Production Settings

In production environments:

```php
// config/app.php
'debug' => env('APP_DEBUG', false),
'log' => env('APP_LOG', 'daily'),

// Clear caches and optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Authentication Security

### Password Policies

Implement strong password requirements:

```php
// app/Providers/AuthServiceProvider.php
use Illuminate\Validation\Rules\Password;

public function boot()
{
    // Configure password validation rules
    Password::defaults(function () {
        $rule = Password::min(12)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols();
            
        return $rule;
    });
}
```

### Account Security

Configure account security settings:

```php
// config/inspirecms.php
'auth' => [
    // Number of failed attempts before lockout
    'failed_login_attempts' => 5,
    
    // Lockout duration in minutes
    'lockout_duration' => 15,
    
    // Force email verification
    'skip_account_verification' => false,
    
    // Session settings
    'session' => [
        'lifetime' => 120, // minutes
        'expire_on_close' => true,
        'secure' => true, // only transmit cookies over HTTPS
    ],
],
```

### Two-Factor Authentication

Enable two-factor authentication for admin users:

```php
// Install Laravel Fortify
composer require laravel/fortify

// Enable 2FA in config/fortify.php
'features' => [
    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]),
    // Other features...
],
```

## API Security

### API Authentication

Secure API endpoints properly:

```php
// config/inspirecms.php
'api' => [
    'authentication' => [
        'token_lifetime' => 60 * 24, // minutes (24 hours)
        'refresh_token_lifetime' => 60 * 24 * 30, // 30 days
        'rate_limiting' => [
            'enabled' => true,
            'max_attempts' => 60,
            'decay_minutes' => 1,
        ],
    ],
],
```

### CORS Configuration

Configure Cross-Origin Resource Sharing:

```php
// config/cors.php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_methods' => ['*'],
'allowed_origins' => ['https://your-frontend-domain.com'],
'allowed_origins_patterns' => [],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => true,
```

## Content Security

### Content Validation

Validate all content before storing or displaying:

```php
// Example content validation rules
$rules = [
    'title' => ['required', 'string', 'max:255'],
    'body' => ['required', 'string', new CleanHtmlRule],
    'metadata.description' => ['nullable', 'string', 'max:160'],
    'attachments' => ['array', 'max:10'],
    'attachments.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,jpg,png'],
];
```

### Custom Validation Rules

Create custom validation rules for content security:

```php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use HTMLPurifier;
use HTMLPurifier_Config;

class CleanHtmlRule implements Rule
{
    public function passes($attribute, $value)
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,b,i,strong,em,a[href|title],ul,ol,li,br,span,img[src|alt|width|height],h1,h2,h3,h4,h5,h6');
        $purifier = new HTMLPurifier($config);
        $clean = $purifier->purify($value);
        
        return $clean === $value;
    }
    
    public function message()
    {
        return 'The :attribute contains disallowed HTML.';
    }
}
```

## File Upload Security

### Secure File Uploads

Configure secure file upload handling:

```php
// config/inspirecms.php
'media' => [
    'media_library' => [
        // Allowed file types
        'allowed_mime_types' => [
            'image/jpeg', 'image/png', 'image/gif', 'image/svg+xml',
            'application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
        
        // Maximum file size in KB
        'max_file_size' => 5 * 1024, // 5MB
        
        // SVG security
        'svg_security' => [
            'sanitize' => true,
        ],
    ],
],
```

### SVG Sanitization

Sanitize SVG files to prevent XSS:

```php
use enshrined\svgSanitize\Sanitizer;

// In your file upload handler
if ($file->getClientMimeType() === 'image/svg+xml') {
    $sanitizer = new Sanitizer();
    $sanitizer->setAllowedTags(['svg', 'path', 'rect', 'circle', /* other safe SVG tags */]);
    $sanitizer->setAllowedAttrs(['viewBox', 'width', 'height', 'fill', /* other safe attributes */]);
    
    $dirtyXml = file_get_contents($file->getRealPath());
    $cleanXml = $sanitizer->sanitize($dirtyXml);
    
    // Replace the original file content with sanitized version
    file_put_contents($file->getRealPath(), $cleanXml);
}
```

## Content Delivery Security

### Content Security Policy

Implement a Content Security Policy:

```php
// app/Http/Middleware/ContentSecurityPolicy.php
namespace App\Http\Middleware;

use Closure;

class ContentSecurityPolicy
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        $response->headers->set('Content-Security-Policy', "
            default-src 'self';
            script-src 'self' https://cdn.jsdelivr.net;
            style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com;
            img-src 'self' data: https://*.your-domain.com;
            font-src 'self' https://fonts.gstatic.com;
            connect-src 'self';
            frame-src 'self';
            object-src 'none';
            base-uri 'self';
            form-action 'self';
        ");
        
        return $response;
    }
}
```

Register the middleware in `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        // Other middleware...
        \App\Http\Middleware\ContentSecurityPolicy::class,
    ],
];
```

### HTTPS Enforcement

Force HTTPS connections:

```php
// app/Providers/AppServiceProvider.php
public function boot()
{
    if (config('app.env') === 'production') {
        \URL::forceScheme('https');
    }
}
```

Add security headers:

```php
// app/Http/Middleware/SecurityHeaders.php
namespace App\Http\Middleware;

use Closure;

class SecurityHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        
        return $response;
    }
}
```

## Database Security

### Database Connections

Secure database connections:

```php
// .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inspirecms
DB_USERNAME=inspirecms_user
DB_PASSWORD=strong_password_here
```

### Query Protection

InspireCMS uses Laravel's query builder, which automatically protects against SQL injection. Always use parameter binding:

```php
// GOOD: Uses parameter binding
$results = DB::table('cms_contents')
    ->where('id', $request->id)
    ->get();

// BAD: Never do this
$query = "SELECT * FROM cms_contents WHERE id = " . $request->id;
$results = DB::select($query);
```

## Regular Updates

### Keep Dependencies Updated

Regularly update InspireCMS and its dependencies:

```bash
# Check for outdated packages
composer outdated

# Update InspireCMS
composer update inspirecms/core

# Update all dependencies
composer update
```

### Security Scanning

Implement security scanning in your workflow:

```bash
# Install security checker
composer require enlightn/security-checker --dev

# Scan for vulnerabilities
php artisan security:check
```

## Server Hardening

### File Permissions

Set proper file permissions:

```bash
# Set proper ownership
chown -R www-data:www-data /path/to/inspirecms

# Set directory permissions
find /path/to/inspirecms -type d -exec chmod 755 {} \;

# Set file permissions
find /path/to/inspirecms -type f -exec chmod 644 {} \;

# Set special permissions for storage and cache
chmod -R 775 /path/to/inspirecms/storage
chmod -R 775 /path/to/inspirecms/bootstrap/cache
```

### Web Server Configuration

Configure your web server securely:

**Nginx**

```nginx
# Security headers
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;

# Disable server information
server_tokens off;

# Block access to sensitive files
location ~ \.(env|git|htaccess|log|yaml|json|lock|md|yml)$ {
    deny all;
    return 404;
}

# Protect system directories
location ~ ^/(storage|bootstrap|vendor|node_modules|tests)/ {
    deny all;
    return 404;
}
```

**Apache**

```apache
# Security headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Protect sensitive files
<FilesMatch "\.(env|git|htaccess|log|yaml|json|lock|md|yml)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Protect system directories
<DirectoryMatch "^/(storage|bootstrap|vendor|node_modules|tests)/">
    Order allow,deny
    Deny from all
</DirectoryMatch>
```

## Monitoring and Logging

### Security Monitoring

Configure proper security logging:

```php
// config/logging.php
'channels' => [
    'security' => [
        'driver' => 'daily',
        'path' => storage_path('logs/security.log'),
        'level' => 'notice',
        'days' => 14,
    ],
],
```

Log security events:

```php
// Example security logging
\Log::channel('security')->notice('Failed login attempt', [
    'user' => $request->email,
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);
```

### Audit Logging

Track user actions for accountability:

```php
// Install an audit package
composer require owen-it/laravel-auditing

// Make models auditable
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Content extends Model implements Auditable
{
    use AuditableTrait;
    
    // ...
}
```

## Backups and Disaster Recovery

### Regular Backups

Configure automatic backups:

```php
// Install backup package
composer require spatie/laravel-backup

// Create a backup command
php artisan backup:run
```

Schedule backups:

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('backup:clean')->daily()->at('01:00');
    $schedule->command('backup:run')->daily()->at('02:00');
}
```

### Recovery Testing

Regularly test your backup restoration process:

```bash
# Test backup restoration in a separate environment
php artisan backup:restore-test
```

## Security Checklist

Implement this checklist for your InspireCMS installation:

1. **Application Security**
   - [ ] Run InspireCMS in production mode
   - [ ] Keep InspireCMS and all dependencies updated
   - [ ] Generate strong application key
   - [ ] Disable debugging in production
   - [ ] Configure proper error logging
   - [ ] Cache configurations in production

2. **Authentication & Authorization**
   - [ ] Implement strong password policies
   - [ ] Configure account lockout settings
   - [ ] Use two-factor authentication for admin accounts
   - [ ] Review and limit admin accounts
   - [ ] Implement principle of least privilege for user roles
   - [ ] Set secure session configurations
   - [ ] Implement proper CSRF protection

3. **Content Security**
   - [ ] Validate and sanitize all content
   - [ ] Implement Content Security Policy
   - [ ] Configure proper CORS settings
   - [ ] Use secure file upload handling
   - [ ] Sanitize SVG files
   - [ ] Set upload size and type restrictions

4. **Server Security**
   - [ ] Force HTTPS connections
   - [ ] Set proper file and directory permissions
   - [ ] Protect sensitive files and directories
   - [ ] Add security headers
   - [ ] Disable server information disclosure
   - [ ] Configure firewall rules
   - [ ] Use secure database connections
   - [ ] Implement rate limiting

5. **Monitoring & Recovery**
   - [ ] Configure security monitoring
   - [ ] Implement audit logging
   - [ ] Set up intrusion detection
   - [ ] Perform regular security scans
   - [ ] Maintain regular backups
   - [ ] Test backup restoration process
   - [ ] Document security incident response plan