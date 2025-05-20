---
title: Navigation
slug: navigation
path: docs/v1/navigation
uri: /docs/1.x/navigation
---
# Navigation

Learn how to implement and use navigation components in InspireCMS to create menus and navigation structures.

---

## Overview

InspireCMS provides a robust navigation system that allows you to:

1. Create and manage navigation menus in the admin panel
2. Programmatically retrieve navigation items
3. Display navigation menus in your frontend templates
4. Support multi-level (nested) navigation structures
5. Handle multi-language navigation

---

## Retrieving Navigation Items

InspireCMS provides helper methods to retrieve navigation data:

```php
// Get navigation items for the "main" menu in the current locale
$mainNavItems = inspirecms()->getNavigation('main');

// Get navigation items for a specific locale
$mainNavItems = inspirecms()->getNavigation('main', 'en');
```

Each navigation item provides methods to access its properties:

```php
foreach($navItems as $item) {
    $title = $item->getTitle();       // Get the navigation item title
    $url = $item->getUrl();           // Get the navigation item URL
    $children = $item->children;      // Get child navigation items
    $isActive = $item->isActive;      // Check if the item is active
    $target = $item->target;          // Get the target attribute (_blank, _self, etc.)
}
```

---

## Basic Navigation Component

Here's a basic navigation component that can be used in your layouts:

```blade {title="resources/views/components/inspirecms/your-theme/navigation.blade.php"}
@props(['items' => [], 'class' => 'main-navigation', 'depth' => 1])

<ul class="{{ $class }}">
    @foreach($items as $item)
        <li class="{{ url()->current() == $item->getUrl() ? 'active' : '' }}">
            <a href="{{ $item->getUrl() }}"
               target="{{ $item->target }}"
            >
                {{ $item->getTitle() }}
            </a>

            @if($item->hasChildren() && $depth > 0)
                <x-dynamic-component
                    :component="$component"
                    :items="$item->children"
                    class="sub-menu"
                    :depth="$depth - 1"
                />
            @endif
        </li>
    @endforeach
</ul>
```

---

## Using Navigation in Layouts

Here's how to integrate navigation within your layout components:

### Header Component with Navigation

```blade {title="resources/views/components/inspirecms/your-theme/header.blade.php"}
@props(['locale' => app()->getLocale()])

<header class="site-header">
    <div class="container">
        <div class="header-content">
            <a href="{{ url('/') }}" class="logo">
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}">
            </a>

            <nav class="main-navigation">
                <ul>
                    @foreach(inspirecms()->getNavigation('main', $locale) as $item)
                        <li class="{{ url()->current() == $item->getUrl() ? 'active' : '' }}">
                            <a href="{{ $item->getUrl() }}">{{ $item->getTitle() }}</a>

                            @if($item->hasChildren())
                                <ul class="sub-menu">
                                    @foreach($item->children as $child)
                                        <li class="{{ url()->current() == $child->getUrl() ? 'active' : '' }}">
                                            <a href="{{ $child->getUrl() }}">{{ $child->getTitle() }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </nav>

            <button class="mobile-menu-toggle" aria-controls="mobile-menu" aria-expanded="false">
                <span class="sr-only">Toggle menu</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
    </div>
</header>
```

### Footer Navigation Example

```blade {title="resources/views/components/inspirecms/your-theme/footer.blade.php}
@props(['locale' => app()->getLocale()])

<footer class="site-footer">
    <div class="container">
        <div class="footer-widgets">
            <div class="row">
                <div class="col-md-4">
                    <div class="widget">
                        <h3 class="widget-title">About Us</h3>
                        <div class="widget-content">
                            <p>Welcome to our company! We're dedicated to providing exceptional services and products since 2005. Our mission is to create innovative solutions while maintaining the highest standards of quality and customer satisfaction.</p>
                        </div>
                </div>

                <div class="col-md-4">
                    <div class="widget">
                        <h3 class="widget-title">Quick Links</h3>
                        <ul class="footer-nav">
                            @foreach(inspirecms()->getNavigation('footer', $locale) as $item)
                                <li>
                                    <a href="{{ $item->getUrl() }}">{{ $item->getTitle() }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="widget">
                        <h3 class="widget-title">Contact</h3>
                        <div class="widget-content">
                            <address>
                                123 Main Street<br>
                                Suite 101<br>
                                Anytown, ST 12345<br>
                                <br>
                                Phone: (555) 123-4567<br>
                                Email: info@example.com<br>
                                Hours: Mon-Fri 9am-5pm
                            </address>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="copyright">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </div>

            <div class="footer-bottom-links">
                @foreach(inspirecms()->getNavigation('legal', $locale) as $item)
                    <a href="{{ $item->getUrl() }}">{{ $item->getTitle() }}</a>
                @endforeach
            </div>
        </div>
    </div>
</footer>
```

---

## Mobile Navigation

Create a responsive mobile navigation component:

```blade {title="resources/views/components/inspirecms/your-theme/mobile-navigation.blade.php"}
@props(['locale' => app()->getLocale()])

<div id="mobile-menu" class="mobile-menu" aria-hidden="true">
    <div class="mobile-menu-inner">
        <button class="mobile-menu-close">
            <span class="sr-only">Close menu</span>
            <svg><!-- SVG icon --></svg>
        </button>

        <nav class="mobile-navigation">
            <ul>
                @foreach(inspirecms()->getNavigation('main', $locale) as $item)
                    <li class="{{ url()->current() == $item->getUrl() ? 'active' : '' }}">
                        @if($item->hasChildren())
                            <span class="sub-menu-toggle" aria-expanded="false"></span>
                        @endif

                        <a href="{{ $item->getUrl() }}">{{ $item->getTitle() }}</a>

                        @if($item->hasChildren())
                            <ul class="sub-menu" aria-hidden="true">
                                @foreach($item->children as $child)
                                    <li class="{{ url()->current() == $child->getUrl() ? 'active' : '' }}">
                                        <a href="{{ $child->getUrl() }}">{{ $child->getTitle() }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile menu toggle logic
        const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        const mobileMenuClose = document.querySelector('.mobile-menu-close');
        const subMenuToggles = document.querySelectorAll('.sub-menu-toggle');

        if (mobileMenuToggle && mobileMenu) {
            mobileMenuToggle.addEventListener('click', function() {
                const expanded = this.getAttribute('aria-expanded') === 'true';
                this.setAttribute('aria-expanded', !expanded);
                mobileMenu.setAttribute('aria-hidden', expanded);
                document.body.classList.toggle('mobile-menu-open');
            });

            if (mobileMenuClose) {
                mobileMenuClose.addEventListener('click', function() {
                    mobileMenuToggle.setAttribute('aria-expanded', 'false');
                    mobileMenu.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('mobile-menu-open');
                });
            }

            subMenuToggles.forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const expanded = this.getAttribute('aria-expanded') === 'true';
                    this.setAttribute('aria-expanded', !expanded);
                    const subMenu = this.closest('li').querySelector('.sub-menu');
                    if (subMenu) {
                        subMenu.setAttribute('aria-hidden', expanded);
                    }
                });
            });
        }
    });
</script>
@endpush
```

---

## Language Switcher Navigation

Create a language switcher component:

```blade {title="resources/views/components/inspirecms/your-theme/language-switcher.blade.php"}
@php
    $langs = inspirecms()->getAllAvailableLanguages();
    $localeCodes = collect($langs)->keys()->all();
    $currentLocale = app()->getLocale();
    $currentLanguage = $langs[$currentLocale] ?? null;
@endphp

<div class="language-switcher">
    <button class="language-selector">
        {{ $currentLanguage ? $currentLanguage->getLabel() : 'Language' }}
        <svg class="icon"><!-- SVG icon --></svg>
    </button>

    <ul class="language-options">
        @foreach($langs as $localeCode => $langDto)
            @php
                // Create the URL for the current page in the different language
                $path = preg_replace(
                    array_map(fn($code) => '/^' . preg_quote($code, '/') . '\//', $localeCodes),
                    '',
                    request()->decodedPath()
                );
            @endphp
            <li>
                <a
                    href="{{ url("{$localeCode}/{$path}") }}"
                    class="{{ $currentLocale == $localeCode ? 'active' : '' }}"
                >
                    {{ $langDto->getLabel() }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
```

---

## Accessibility Considerations

When implementing navigation:

1. **Keyboard Navigation**: Ensure all menu items are accessible via keyboard
2. **ARIA Attributes**: Use appropriate ARIA attributes (`aria-expanded`, `aria-hidden`, etc.)
3. **Focus Management**: Properly manage focus for dropdowns and mobile menus
4. **Skip Links**: Provide skip links to bypass navigation for keyboard users
5. **Color Contrast**: Ensure sufficient contrast for navigation elements

---

## Navigation Best Practices

1. **Clear Structure**: Keep navigation hierarchies logical and intuitive
2. **Consistent Naming**: Use consistent naming conventions for menu items
3. **Current Page Indication**: Clearly indicate the current active page
4. **Responsive Design**: Ensure navigation works on all screen sizes
5. **Performance Optimization**: Cache navigation results when possible
6. **Fallback Handling**: Provide graceful fallbacks for missing navigation items
7. **Deep Linking**: Ensure all navigation links work with deep linking
8. **Localization**: Handle navigation in multiple languages properly

---

## Troubleshooting

### Common Issues

1. **Navigation items not showing up**

    - Check that the navigation menu exists in the admin panel
    - Verify the correct menu name is being used in the code
    - Ensure the selected locale has navigation items

2. **Active state not working**

    - Verify URL comparison logic matches the actual URLs being used
    - Check for trailing slashes or other URL normalization issues

3. **Mobile menu not toggling**
    - Check for JavaScript errors in the console
    - Verify that the ARIA attributes are properly set
    - Ensure proper event listeners are connected

---

## Additional Resources

For more information on working with navigation, see:

-   [Layouts Documentation](./layouts){.doc-link} for integrating navigation in page layouts
-   [Templates Documentation](./templates){.doc-link} for using navigation in page templates
