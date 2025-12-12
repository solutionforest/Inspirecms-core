{{--
    Visual Editor Layout Wrapper

    This is the main wrapper for rendered visual editor layouts.
    It wraps the entire layout content and provides necessary CSS classes.

    @param \Illuminate\Support\HtmlString $content - The rendered layout content
    @param array $layout - The original layout data (optional)
--}}
<div class="ve-layout" @if(!empty($layout['id'])) data-layout-id="{{ $layout['id'] }}" @endif>
    {{ $content }}
</div>
