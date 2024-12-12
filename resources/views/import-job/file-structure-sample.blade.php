@php
    use Illuminate\Support\Str;

    $getItemIcon = function ($file) {
        $icon = 'heroicon-o-document';
        $iconAlias = null;
        if (is_array($file)) {
            $icon = 'heroicon-o-folder';
            $iconAlias = null;
        } else if (Str::endsWith($file, '.json')) {
            $icon = null;
            $iconAlias = 'inspirecms::json-file';
        }
        return [$icon, $iconAlias];
    };
@endphp
<div role="tree" aria-orientation="vertical" class="select-none py-1 px-1.5 rounded-md shadow-md bg-gray-200/40">
    <div role="group">
        @foreach ($structure ?? [] as $lv_1_folder => $lv_1_files)

            <div role="treeitem" aria-label="level-1-file-container">
                <div class="py-0.5 flex items-center gap-x-0.5 w-full" aria-label="level-1-file">
                    <div class="grow px-1.5 rounded-md">
                        <div class="flex items-center gap-x-3">
                            <x-filament::icon
                                icon="heroicon-o-folder"
                                class="h-5 w-5 text-gray-500 dark:text-gray-100"
                            />
                            <div class="grow">
                                <span class="text-sm font-mono text-gray-800 dark:text-gray-100">
                                    {{ $lv_1_folder }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full overflow-hidden" role="group" aria-label="level-1-files-group">
                <div class="ms-1 ps-7 relative before:absolute before:top-0 before:start-3 before:w-0.5 before:-ms-px before:h-full before:bg-gray-800/20 before:dark:bg-gray-100/40" role="group">
                    @foreach ($lv_1_files as $lv_2_index_or_folder => $lv_2_file_or_files)
                        @php
                            [$icon, $iconAlias] = $getItemIcon($lv_2_file_or_files);
                        @endphp
                        <div role="treeitem" aria-label="level-2-file-container">
                            <div class="py-0.5 flex items-center gap-x-0.5 w-full" aria-label="level-2-file">
                                <div class="grow px-1.5 rounded-md">
                                    <div class="flex items-center gap-x-3">
                                        <x-filament::icon
                                            :alias="$iconAlias"
                                            :icon="$icon"
                                            class="fi-icon h-5 w-5 text-gray-500 dark:text-gray-100"
                                        />
                                        <div class="grow">
                                            <span class="text-sm font-mono text-gray-800 dark:text-gray-100">
                                                @if (is_array($lv_2_file_or_files))
                                                    {{ $lv_2_index_or_folder }}
                                                @else
                                                    {{ $lv_2_file_or_files }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if (is_array($lv_2_file_or_files))
                            <div class="w-full overflow-hidden" role="group" aria-label="level-2-files-group">
                                <div class="ms-4 ps-3 relative before:absolute before:top-0 before:start-0 before:w-0.5 before:-ms-px before:h-full before:bg-gray-800/20 before:dark:bg-gray-100/40">
                                    @foreach ($lv_2_file_or_files as $lv_3_file)
                                        @php
                                            [$icon, $iconAlias] = $getItemIcon($lv_3_file);
                                        @endphp
                                        <div class="px-2 rounded-md" role="treeitem" aria-label="level-3-file">
                                            <div class="flex items-center gap-x-3">
                                                <x-filament::icon
                                                    :alias="$iconAlias"
                                                    :icon="$icon"
                                                    class="fi-icon h-5 w-5 text-gray-500 dark:text-gray-100"
                                                />
                                                <div class="grow">
                                                    <span class="text-sm font-mono text-gray-800 dark:text-gray-100">
                                                        {{ $lv_3_file }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

        @endforeach
    </div>
</div>