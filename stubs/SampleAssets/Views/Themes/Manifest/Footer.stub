@php
    $locale ??= request()->getLocale();
    $nav = collect(inspirecms()->getNavigation('footer', $locale))->flatMap(fn ($nav) => $nav->children ?? [])->all();
@endphp
<footer class="footer">
    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <div class="row">
                <div class="col-md-4 text-start">
                    <strong>{{ config('app.name') }}</strong>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <ul class="list-unstyled social-links mb-0">
                        @foreach ($nav as $item)
                        <li>
                            <a class="text-dark text-decoration-none" href="{{ $item->getUrl() }}">{{ $item->getTitle() }}</a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-md-4">
                    <p class="mb-0 copyright">
                        © Copyright {{ config('app.name') }}. All rights reserved
                    </p>
                </div>
            </div>
        </div>
    </section>
</footer>