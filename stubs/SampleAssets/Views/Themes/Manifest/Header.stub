@props(['locale' => null])
@aware(['isPreviewing'])
@php
    $locale ??= request()->getLocale();
    $nav = inspirecms()->getNavigation('main', $locale);
    $otherLocales = collect(inspirecms()->getAllAvailableLanguages())
        ->where(fn ($dto, $l) => $l != $locale)
        ->mapWithKeys(fn ($item) => [$item->code => $item->getLabel($locale)])
        ->all();
@endphp

<header>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="/">{{ config('app.name') }}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    @foreach ($nav as $item)
                    @php
                        $navUrl = $item->getUrl();
                        $isCurrent = request()->is($navUrl) || request()->is($navUrl . '/*') || (request()->is('/') && $navUrl === '/');
                    @endphp
                        <li class="nav-item">
                            <a class="nav-link {{ $isCurrent ? 'active' : '' }}" aria-current="{{ $isCurrent ? 'page' : '' }}" href="{{ $navUrl }}">{{ $item->getTitle() }}</a>
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
</header>