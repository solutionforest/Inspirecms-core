@php
    $locale ??= request()->getLocale();
    $nav = inspirecms()->getNavigation('main', $locale);
    $otherLocales = collect(inspirecms()->getAllAvailableLanguages())
        ->where(fn ($dto, $l) => $l != $locale)
        ->mapWithKeys(fn ($item) => [$item->code => $item->getLabel($locale)])
        ->all();
@endphp
<!-- Sidebar -->
<aside class="sidebar">
    <!-- Desktop Sidebar Content -->
    <div class="desktop-sidebar">
        <div class="sidebar-main-content">
            <div class="vt">{{ config('app.name') }}</div>
            <div class="search-box">
                <input type="text" placeholder="Search...">
            </div>

            <nav class="nav-menu">
                @foreach ($nav as $item)
                    <div class="nav-section">
                        @if ($item->hasChildren())
                            <h3 class="has-dropdown">$item->getTitle()</h3>
                            <ul class="submenu">
                                @foreach ($item->children as $child)
                                    <li><a href="{{ $child->getUrl() }}">{{ $child->getTitle() }}</a></li>
                                @endforeach
                            </ul>
                        @else
                            <div>
                                <a href="{{ $item->getUrl() }}">{{ $item->getTitle() }}</a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </nav>
            <div class="dropdown">
                <button class="btn btn-white dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ strtoupper($locale) }}
                </button>
                <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                    @foreach ($otherLocales as $locale => $langDto)
                        <li><a class="dropdown-item" href="{{ url("/$locale") }}">{{ strtoupper($locale) }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="sidebar-footer">
            <span class="theme-credit">Theme Name by VitaThemes</span>
            <a href="#" class="privacy-policy">Privacy Policy</a>
        </div>
    </div>

    <!-- Mobile Menu Button (Outside mobile-menu-wrapper) -->
    <!-- Mobile Top Navigation -->
    <div class="mobile-top-nav">
        <button class="mobile-menu-btn">
            <span class="hamburger"></span>
        </button>
        <span class="mobile-title">{{ config('app.name') }}</span>
        <button class="mobile-search-btn">
            <img src="{{ asset('images/icon/search.svg') }}" alt="">
        </button>
    </div>


    <!-- Mobile Menu Wrapper -->
    <div class="mobile-menu-wrapper">
        <div class="mobile-menu">
            <div class="mobile-menu-header">
                <span class="vt">{{ config('app.name') }}</span>
                <button class="close-menu"><img src="{{ asset('images/icon/arrow1.svg') }}" alt="" style="transform: scaleX(-1); -webkit-transform: scaleX(-1);"></button>
            </div>

            <div class="search-box">
                <input type="text" placeholder="Search...">
            </div>

            <nav class="nav-menu">
                <!-- Same content as desktop -->
                @foreach ($nav as $item)
                    <div class="nav-section">
                        @if ($item->hasChildren())
                        
                            <h3 class="has-dropdown">{{ $item->getTitle() }}</h3>
                            <ul class="submenu">
                                @foreach ($item->children as $child)
                                    <li><a href="{{ $child->getUrl() }}">{{ $child->getTitle() }}</a></li>
                                @endforeach
                            </ul>
                        @else
                            <div>
                                <a href="{{ $item->getUrl() }}">{{ $item->getTitle() }}</a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </nav>
            <div class="dropdown">
                <button class="btn btn-white dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ strtoupper($locale) }}
                </button>
                <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                    @foreach ($otherLocales as $locale => $langDto)
                        <li><a class="dropdown-item" href="{{ url("/$locale") }}">{{ strtoupper($locale) }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</aside>