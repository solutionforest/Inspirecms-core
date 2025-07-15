@php
    $basicDiff = collect($diff)->except('propertyData')->all();
    $propertyData = $diff['propertyData'] ?? [];
@endphp
<div class="content-history-details flex flex-col gap-4">
    <x-inspirecms::version-diff :items="$basicDiff" :heading="__('inspirecms::resources/content-version.content_history_detail.general_info')" />
    <x-inspirecms::version-diff :items="$propertyData" class="content-property-data" :heading="__('inspirecms::resources/content-version.content_history_detail.property_data')" />
</div>