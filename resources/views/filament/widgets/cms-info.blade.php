
<x-filament-widgets::widget class="fi-wi-cms-info">
    <a
        href="{{ $this->getDocumentUrl() }}"
        id="docs-card"
        class="card flex-col"
    >
        <div id="screenshot-container" class="relative flex w-full flex-1 items-stretch">
            <img
                src="{{ $this->getLightScreenshotUrl() }}"
                alt="Laravel documentation screenshot"
                class="screenshot light-screenshot"
                onerror="
                    document.getElementById('screenshot-container').classList.add('!hidden');
                    document.getElementById('docs-card').classList.add('!row-span-1');
                    document.getElementById('docs-card-content').classList.add('!flex-row');
                    document.getElementById('background').classList.add('!hidden');
                "
            />
            <img
                src="{{ $this->getDarkScreenShotUrl() }}"
                alt="Laravel documentation screenshot"
                class="screenshot dark-screenshot"
            />
        </div>

        <div class="relative flex items-center gap-6 lg:items-end">
            <div id="docs-card-content" class="flex items-start gap-6 lg:flex-col">
                <div class="icon-container">
                    <x-filament::icon icon="heroicon-o-book-open" class="icon" />
                </div>

                <div class="pt-3 sm:pt-5 lg:pt-0">
                    <h2>Documentation</h2>

                    <p class="mt-4 text-sm/relaxed">
                        Lorem ipsum dolor sit, amet consectetur adipisicing elit. Qui voluptatum, voluptatibus sequi excepturi expedita distinctio vel recusandae, eligendi illo cum iusto incidunt libero, minus tempora alias quae at sed eaque!
                    </p>
                </div>
            </div>

            <x-filament::icon icon="heroicon-o-chevron-right" class="icon" />
        </div>
    </a>

    <a
        href="{{ $this->getNewsUrl() }}"
        class="card"
    >
        <div class="icon-container">
            <x-filament::icon icon="heroicon-o-newspaper" class="icon" />
        </div>

        <div class="pt-3 sm:pt-5">
            <h2>News</h2>

            <p class="mt-4 text-sm/relaxed">
                Lorem ipsum dolor, sit amet consectetur adipisicing elit. Nemo, maiores recusandae. Ex incidunt laboriosam suscipit asperiores deleniti accusantium magni eaque corporis enim, labore aspernatur nihil, culpa maxime rerum nisi? Error.
            </p>
        </div>

        <x-filament::icon icon="heroicon-o-chevron-right" class="icon" />
    </a>
</x-filament-widgets::widget>