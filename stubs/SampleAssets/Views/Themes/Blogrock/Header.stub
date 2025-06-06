@php
    $locale ??= request()->getLocale();
    $nav = inspirecms()->getNavigation('main', $locale);
    $otherLocales = collect(inspirecms()->getAllAvailableLanguages())
        ->where(fn ($dto, $l) => $l != $locale)
        ->mapWithKeys(fn ($item) => [$item->code => $item->getLabel($locale)])
        ->all();
@endphp
<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="/">
            {{ config('app.name') }}
        </a>
        <button class="menu-toggle" type="button">
            <span class="close-icon"><img src="{{ asset('images/icon/close-menu.svg') }}" alt=""></span>
            <span class="menu-icon"><span class="navbar-toggler-icon"></span></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                @foreach ($nav as $item)
                    <li class="nav-item">
                        <a class="nav-link" href="{{ $item->getUrl() }}">{{ $item->getTitle() }}</a>
                        @if ($item->hasChildren())
                            <ul class="submenu">
                                @foreach ($item->children as $child)
                                    <li><a href="{{ $child->getUrl() }}">{{ $child->getTitle() }}</a></li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
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
</nav>

<!-- Mobile Menu -->
<div class="mobile-menu">
    <ul>
        @foreach ($nav as $item)
            <li>
                <a class="nav-link" href="{{ $item->getUrl() }}">{{ $item->getTitle() }}</a>
            </li>
            @if ($item->hasChildren())
                <ul class="mobile-submenu">
                    @foreach ($item->children as $child)
                        <li><a href="{{ $child->getUrl() }}">{{ $child->getTitle() }}</a></li>
                    @endforeach
                </ul>
            @endif
        @endforeach
    </ul>
    <div class="dropdown">
        <button class="btn btn-white dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            {{ strtoupper($locale) }}
        </button>
        <ul class="dropdown-menu" aria-labelledby="languageDropdown">
            @foreach ($otherLocales as $locale => $langDto)
                <li class="px-2"><a class="dropdown-item" href="{{ url("/$locale") }}">{{ strtoupper($locale) }}</a></li>
            @endforeach
        </ul>
    </div>
</div>