---
title: Navigation
slug: fe-navigation
path: docs/v1/fe-navigation
uri: /docs/v1/fe-navigation
heading: Navigation
brief: 
quick_links: []
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

## Using Navigation in Layouts

Here's how to integrate navigation within your layout components:

2. Header Navigation Example

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

2. Footer Navigation Example

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
