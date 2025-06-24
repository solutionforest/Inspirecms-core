@php
    $locale ??= request()->getLocale();
    $nav = inspirecms()->getNavigation('footer', $locale);

    $config = inspirecms_content()->findByRealPath('config')->first()?->toDto($locale);
    $config_sns = $config?->getPropertyGroup('social_media');
@endphp
<!-- Newsletter and Footer -->
<footer class="site-footer">

    <!-- Main Footer -->
    <div class="main-footer">
        <div class="container">
            <div class="row">
                <!-- Brand Column -->
                <div class="col-md-6">
                    <div class="footer-brand">
                        <h3>{{ config('app.name') }}</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas quis eros sed risus sollicitudin fringilla dictum in metus. Sed ultrices mauris a facilisis varius.</p>
                        <div class="social-links">
                            @if ($fb = $config_sns?->getPropertyData('facebook')?->getValue())
                                <a href="{{ $fb }}"><img src="{{ asset('images/icon/fb.svg') }}" alt=""></a>
                            @endif
                            @if ($twitter = $config_sns?->getPropertyData('twitter')?->getValue())
                                <a href="{{ $twitter }}"><img src="{{ asset('images/icon/birdx.svg') }}" alt=""></a>
                            @endif
                            @if ($ig = $config_sns?->getPropertyData('instagram')?->getValue())
                                <a href="{{ $ig }}"><img src="{{ asset('images/icon/ig.svg') }}" alt=""></a>
                            @endif
                        </div>
                    </div>
                </div>
                
                @foreach ($nav as $item)
                    <div class="col-md-2">
                        <h4>{{ $item->getTitle() }}</h4>
                        @if ($item->hasChildren())
                            <ul>
                                @foreach ($item->children as $child)
                                    <li><a href="{{ $child->getUrl() }}">{{ $child->getTitle() }}</a></li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Copyright -->
    <div class="copyright">
        <div class="container">
            <p>Copyright inspireCMS. All rights reserved</p>
        </div>
    </div>
</footer>