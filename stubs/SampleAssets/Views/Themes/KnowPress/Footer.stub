@php
    $locale ??= request()->getLocale();
    $nav = inspirecms()->getNavigation('footer', $locale);
@endphp
<!-- Newsletter and Footer -->
<footer class="site-footer">


    <!-- Main Footer -->
    <div class="main-footer">
        <div class="container">
            <div class="row">
                <!-- Brand Column -->
                <div class="col-md-6">
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
            <p>{{ config('app.name') }} &copy; {{ date('Y') }} All Right Reserved</p>
        </div>
    </div>
</footer>