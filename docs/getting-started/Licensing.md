# Licensing

InspireCMS is licensed under the MIT License, with options for commercial support and extended features.

## Open Source License

InspireCMS Core is available under the [MIT License](../../LICENSE.md), which allows you to:

- Use the software for personal, commercial, and academic projects
- Modify the source code
- Distribute modifications under the same terms

The MIT License requires you to:
- Include the original license and copyright notice in your distribution
- Not hold the authors liable for damages

## Commercial Licensing

For businesses requiring additional features, support, and liability protection, we offer commercial licensing options.

### Commercial Features

Commercial licenses include access to:
- Priority support
- Extended templates and themes
- Advanced workflow features
- SLA guarantees
- Dedicated support team

### Pricing

Commercial licenses are available in these tiers:

| Feature | Starter | Professional | Enterprise |
|---------|---------|--------------|------------|
| Sites | 1 | Up to 5 | Unlimited |
| Support | Email | Email + Chat | 24/7 Priority |
| SLA | None | 48 hours | 8 hours |
| Custom Development | No | No | Yes |
| Price | $499/year | $999/year | Contact Sales |

## License Keys

If you have purchased a commercial license, you'll receive a license key that should be added to your `.env` file:

```env
INSPIRECMS_LICENSE_KEY=your-license-key-here
```

You can also configure the license key in your `config/inspirecms.php` file:

```php
'license_key' => env('INSPIRECMS_LICENSE_KEY'),
```

## License Validation

The system automatically validates your license key:

* At installation
* Periodically during operation
* When accessing certain commercial features

## License Verification

To verify your license status, run:

```bash
php artisan inspirecms:about
```

This will display your license information, including:

* License type
* Expiration date
* Licensed domains
* Available features

## Transitioning Between Licenses

From Open Source to Commercial

1. Purchase a commercial license
1. Add your license key to your .env file
1. Clear your configuration cache
1. Access newly available features

Renewing Commercial Licenses

1. Purchase a renewal from our website
1. Your existing license key will be automatically updated
1. No code changes necessary

## Questions?

If you have questions about licensing, please contact our sales team at [sales@example.com](mailto:sales@example.com).