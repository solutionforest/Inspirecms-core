<?php

namespace SolutionForest\InspireCms\Database\Seeders;

use Illuminate\Database\Seeder;
use SolutionForest\InspireCms\Helpers\ModelHelper;
use SolutionForest\InspireCms\ImportData\Entities as ImportDataEntities;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Services\ImportDataServiceInterface;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

class SampleSeeder extends Seeder
{
    protected $importDataService;

    protected array $mediaAssets = [];

    protected array $language = [];

    protected array $templates = [];

    protected array $fieldGroups = [];

    protected array $documentTypes = [];

    protected array $content = [];

    public function __construct(ImportDataServiceInterface $importDataService)
    {
        $this->importDataService = $importDataService;
    }

    public function run()
    {
        $this->makeSampleMedia();

        $this->makeSampleLanguages();

        $this->addSampleFields();
        $this->addSampleDocumentTypes();
        $this->addSampleContent();
        $this->addSampleNavigation();
        $this->addSampleTemplates();

        $this->importDataService->run();

    }

    protected function addSampleTemplates(): void
    {
        $home = <<<'Html'
@php
    $title = $content->getTitle();

    $locale = $content->getLocale();

    $general_page_banner = $content->getPropertyGroup('general_page_banner');
    $general_page_banner_title = $general_page_banner?->getPropertyData('title')?->getValue($locale);
    $general_page_banner_description = $general_page_banner?->getPropertyData('description')?->getValue($locale);

    $social_media = $content->getPropertyGroup('social_media');
    $social_media_linkedin = $social_media?->getPropertyData('linkedin')?->getValue();
    $social_media_instagram = $social_media?->getPropertyData('instagram')?->getValue();
    $social_media_twitter = $social_media?->getPropertyData('twitter')?->getValue();
    $social_media_github = $social_media?->getPropertyData('github')?->getValue();
    $social_media_email = $social_media?->getPropertyData('email')?->getValue();

@endphp
<x-page :content="$content">
  <main class="flex-auto">
    <div class="sm:px-8 mt-9">
      <div class="mx-auto w-full max-w-7xl lg:px-8">
        <div class="relative px-4 sm:px-8 lg:px-12">
          <div class="mx-auto max-w-2xl lg:max-w-5xl">
            <div class="max-w-2xl">
              <h1 class="text-4xl font-bold tracking-tight text-cyan-800 sm:text-5xl dark:text-cyan-100">{{ $general_page_banner_title }}</h1>
              <p class="mt-6 text-base text-cyan-600 dark:text-cyan-400">{{ $general_page_banner_description }}</p>
              <div class="mt-6 flex gap-6">
                @if (filled($social_media_twitter))
                  <a class="group -m-1 p-1" aria-label="Follow on X" href="{{$social_media_twitter}}">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="h-6 w-6 text-cyan-500 transition group-hover:text-cyan-600 dark:text-cyan-400 dark:group-hover:text-cyan-300">
                      <path d="M13.3174 10.7749L19.1457 4H17.7646L12.7039 9.88256L8.66193 4H4L10.1122 12.8955L4 20H5.38119L10.7254 13.7878L14.994 20H19.656L13.3171 10.7749H13.3174ZM11.4257 12.9738L10.8064 12.0881L5.87886 5.03974H8.00029L11.9769 10.728L12.5962 11.6137L17.7652 19.0075H15.6438L11.4257 12.9742V12.9738Z"></path>
                    </svg>
                  </a>
                @endif
                @if (filled($social_media_instagram))
                  <a class="group -m-1 p-1" aria-label="Follow on Instagram" href="{{$social_media_instagram}}">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="h-6 w-6 text-cyan-500 transition group-hover:text-cyan-600 dark:text-cyan-400 dark:group-hover:text-cyan-300">
                      <path d="M12 3c-2.444 0-2.75.01-3.71.054-.959.044-1.613.196-2.185.418A4.412 4.412 0 0 0 4.51 4.511c-.5.5-.809 1.002-1.039 1.594-.222.572-.374 1.226-.418 2.184C3.01 9.25 3 9.556 3 12s.01 2.75.054 3.71c.044.959.196 1.613.418 2.185.23.592.538 1.094 1.039 1.595.5.5 1.002.808 1.594 1.038.572.222 1.226.374 2.184.418C9.25 20.99 9.556 21 12 21s2.75-.01 3.71-.054c.959-.044 1.613-.196 2.185-.419a4.412 4.412 0 0 0 1.595-1.038c.5-.5.808-1.002 1.038-1.594.222-.572.374-1.226.418-2.184.044-.96.054-1.267.054-3.711s-.01-2.75-.054-3.71c-.044-.959-.196-1.613-.419-2.185A4.412 4.412 0 0 0 19.49 4.51c-.5-.5-1.002-.809-1.594-1.039-.572-.222-1.226-.374-2.184-.418C14.75 3.01 14.444 3 12 3Zm0 1.622c2.403 0 2.688.009 3.637.052.877.04 1.354.187 1.67.31.421.163.72.358 1.036.673.315.315.51.615.673 1.035.123.317.27.794.31 1.671.043.95.052 1.234.052 3.637s-.009 2.688-.052 3.637c-.04.877-.187 1.354-.31 1.67-.163.421-.358.72-.673 1.036a2.79 2.79 0 0 1-1.035.673c-.317.123-.794.27-1.671.31-.95.043-1.234.052-3.637.052s-2.688-.009-3.637-.052c-.877-.04-1.354-.187-1.67-.31a2.789 2.789 0 0 1-1.036-.673 2.79 2.79 0 0 1-.673-1.035c-.123-.317-.27-.794-.31-1.671-.043-.95-.052-1.234-.052-3.637s.009-2.688.052-3.637c.04-.877.187-1.354.31-1.67.163-.421.358-.72.673-1.036.315-.315.615-.51 1.035-.673.317-.123.794-.27 1.671-.31.95-.043 1.234-.052 3.637-.052Z"></path>
                      <path d="M12 15a3 3 0 1 1 0-6 3 3 0 0 1 0 6Zm0-7.622a4.622 4.622 0 1 0 0 9.244 4.622 4.622 0 0 0 0-9.244Zm5.884-.182a1.08 1.08 0 1 1-2.16 0 1.08 1.08 0 0 1 2.16 0Z"></path>
                    </svg>
                  </a>
                @endif
                @if (filled($social_media_github))
                  <a class="group -m-1 p-1" aria-label="Follow on GitHub" href="{{$social_media_github}}">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="h-6 w-6 text-cyan-500 transition group-hover:text-cyan-600 dark:text-cyan-400 dark:group-hover:text-cyan-300">
                      <path text-rule="evenodd" clip-rule="evenodd" d="M12 2C6.475 2 2 6.588 2 12.253c0 4.537 2.862 8.369 6.838 9.727.5.09.687-.218.687-.487 0-.243-.013-1.05-.013-1.91C7 20.059 6.35 18.957 6.15 18.38c-.113-.295-.6-1.205-1.025-1.448-.35-.192-.85-.667-.013-.68.788-.012 1.35.744 1.538 1.051.9 1.551 2.338 1.116 2.912.846.088-.666.35-1.115.638-1.371-2.225-.256-4.55-1.14-4.55-5.062 0-1.115.387-2.038 1.025-2.756-.1-.256-.45-1.307.1-2.717 0 0 .837-.269 2.75 1.051.8-.23 1.65-.346 2.5-.346.85 0 1.7.115 2.5.346 1.912-1.333 2.75-1.05 2.75-1.05.55 1.409.2 2.46.1 2.716.637.718 1.025 1.628 1.025 2.756 0 3.934-2.337 4.806-4.562 5.062.362.32.675.936.675 1.897 0 1.371-.013 2.473-.013 2.82 0 .268.188.589.688.486a10.039 10.039 0 0 0 4.932-3.74A10.447 10.447 0 0 0 22 12.253C22 6.588 17.525 2 12 2Z"></path>
                    </svg>
                  </a>
                @endif
                @if (filled($social_media_linkedin))
                  <a class="group -m-1 p-1" aria-label="Follow on LinkedIn" href="{{$social_media_linkedin}}">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="h-6 w-6 text-cyan-500 transition group-hover:text-cyan-600 dark:text-cyan-400 dark:group-hover:text-cyan-300">
                      <path d="M18.335 18.339H15.67v-4.177c0-.996-.02-2.278-1.39-2.278-1.389 0-1.601 1.084-1.601 2.205v4.25h-2.666V9.75h2.56v1.17h.035c.358-.674 1.228-1.387 2.528-1.387 2.7 0 3.2 1.778 3.2 4.091v4.715zM7.003 8.575a1.546 1.546 0 01-1.548-1.549 1.548 1.548 0 111.547 1.549zm1.336 9.764H5.666V9.75H8.34v8.589zM19.67 3H4.329C3.593 3 3 3.58 3 4.297v15.406C3 20.42 3.594 21 4.328 21h15.338C20.4 21 21 20.42 21 19.703V4.297C21 3.58 20.4 3 19.666 3h.003z"></path>
                    </svg>
                  </a>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</x-page>
Html;
        $projects = <<<'Html'
@php
    $title = $content->getTitle();

    $locale = $content->getLocale();

    $general_page_banner = $content->getPropertyGroup('general_page_banner');
    $general_page_banner_title = $general_page_banner?->getPropertyData('title')?->getValue($locale);
    $general_page_banner_description = $general_page_banner?->getPropertyData('description')?->getValue($locale);

    $projects = $content->getPropertyGroup('projects');
    $projects_projects = $projects?->getPropertyData('projects')?->getValue();
@endphp
<x-page :content="$content">
    <main class="flex-auto">
        <div class="sm:px-8 mt-16 sm:mt-32">
          <div class="mx-auto w-full max-w-7xl lg:px-8">
            <div class="relative px-4 sm:px-8 lg:px-12">
              <div class="mx-auto max-w-2xl lg:max-w-5xl">
                <header class="max-w-2xl">
                  <h1 class="text-4xl font-bold tracking-tight text-cyan-800 sm:text-5xl dark:text-cyan-100">{{$general_page_banner_title}}</h1>
                  <p class="mt-6 text-base text-cyan-600 dark:text-cyan-400">{{$general_page_banner_description}}</p>
                </header>
                <div class="mt-16 sm:mt-20">
                  <ul role="list" class="grid grid-cols-1 gap-x-12 gap-y-16 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects_projects ?? [] as $item)
                    @php
                        $title = $item->getPropertyData('title')?->getValue($locale);
                        $description = $item->getPropertyData('description')?->getValue($locale);
                        $link = $item->getPropertyData('link')?->getValue();
                        $image = array_values($item->getPropertyData('image')?->getSourceValue())[0] ?? null;
                    @endphp
                        
                        <li class="group relative flex flex-col items-start">
                            <div class="relative z-10 flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-md shadow-cyan-200 ring-1 ring-cyan-100 dark:border dark:border-cyan-400 dark:bg-cyan-800 dark:ring-0">
                              <img alt="" loading="lazy" width="100" height="100" decoding="async" data-nimg="1" class="rounded-full" src="{{$image?->getUrl()}}" style="color: transparent;">
                            </div>
                            <h2 class="mt-6 text-base font-semibold text-cyan-800 dark:text-cyan-100">
                            <div class="absolute -inset-x-4 -inset-y-6 z-0 scale-95 bg-cyan-50 opacity-0 transition group-hover:scale-100 group-hover:opacity-100 sm:-inset-x-6 sm:rounded-2xl dark:bg-cyan-800/50"></div>
                            <a href="{{$link}}">
                                <span class="absolute -inset-x-4 -inset-y-6 z-20 sm:-inset-x-6 sm:rounded-2xl"></span>
                                <span class="relative z-10">{{$title}}</span>
                            </a>
                            </h2>
                            <p class="relative z-10 mt-2 text-sm text-cyan-600 dark:text-cyan-400">{{$description}}</p>
                            <p class="relative z-10 mt-6 flex text-sm font-medium text-cyan-400 transition group-hover:text-teal-500 dark:text-cyan-200">
                            <svg viewBox="0 0 24 24" aria-hidden="true" class="h-6 w-6 flex-none">
                                <path d="M15.712 11.823a.75.75 0 1 0 1.06 1.06l-1.06-1.06Zm-4.95 1.768a.75.75 0 0 0 1.06-1.06l-1.06 1.06Zm-2.475-1.414a.75.75 0 1 0-1.06-1.06l1.06 1.06Zm4.95-1.768a.75.75 0 1 0-1.06 1.06l1.06-1.06Zm3.359.53-.884.884 1.06 1.06.885-.883-1.061-1.06Zm-4.95-2.12 1.414-1.415L12 6.344l-1.415 1.413 1.061 1.061Zm0 3.535a2.5 2.5 0 0 1 0-3.536l-1.06-1.06a4 4 0 0 0 0 5.656l1.06-1.06Zm4.95-4.95a2.5 2.5 0 0 1 0 3.535L17.656 12a4 4 0 0 0 0-5.657l-1.06 1.06Zm1.06-1.06a4 4 0 0 0-5.656 0l1.06 1.06a2.5 2.5 0 0 1 3.536 0l1.06-1.06Zm-7.07 7.07.176.177 1.06-1.06-.176-.177-1.06 1.06Zm-3.183-.353.884-.884-1.06-1.06-.884.883 1.06 1.06Zm4.95 2.121-1.414 1.414 1.06 1.06 1.415-1.413-1.06-1.061Zm0-3.536a2.5 2.5 0 0 1 0 3.536l1.06 1.06a4 4 0 0 0 0-5.656l-1.06 1.06Zm-4.95 4.95a2.5 2.5 0 0 1 0-3.535L6.344 12a4 4 0 0 0 0 5.656l1.06-1.06Zm-1.06 1.06a4 4 0 0 0 5.657 0l-1.061-1.06a2.5 2.5 0 0 1-3.535 0l-1.061 1.06Zm7.07-7.07-.176-.177-1.06 1.06.176.178 1.06-1.061Z" fill="currentColor"></path>
                            </svg>
                            <span class="ml-2">{{$link}}</span>
                            </p>
                        </li>
                    @endforeach
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
</x-page>
Html;
        $about = <<<'Html'
@php
    $locale = $content->getLocale();

    $blog_detail_content = $content->getPropertyGroup('blog_detail_content');
    $blog_detail_content_title = $blog_detail_content?->getPropertyData('title')?->getValue($locale);
    $blog_detail_content_content = $blog_detail_content?->getPropertyData('content')?->getValue($locale);
    $blog_detail_content_image = collect($blog_detail_content?->getPropertyData('image')?->getValue())->first();
@endphp
<x-page :content="$content">
  <main class="flex-auto">
    <div class="sm:px-8 mt-16 sm:mt-32">
        <div class="mx-auto w-full max-w-7xl lg:px-8">
        <div class="relative px-4 sm:px-8 lg:px-12">
            <div class="mx-auto max-w-2xl lg:max-w-5xl">
            <div class="grid grid-cols-1 gap-y-16 lg:grid-cols-2 lg:grid-rows-[auto_1fr] lg:gap-y-12">
                <div class="lg:pl-20">
                <div class="max-w-xs px-2.5 lg:max-w-none">
                    @if ($blog_detail_content_image)
                        <img alt="" loading="lazy" width="800" height="800" decoding="async" data-nimg="1" class="aspect-square rotate-3 rounded-2xl bg-cyan-100 object-cover dark:bg-cyan-800" sizes="(min-width: 1024px) 32rem, 20rem" srcset="{{ $blog_detail_content_image->getUrl() }}" style="color: transparent;">
                    @endif
                </div>
                </div>
                <div class="lg:order-first lg:row-span-2">
                <h1 class="text-4xl font-bold tracking-tight text-cyan-800 sm:text-5xl dark:text-cyan-100">{{ $blog_detail_content_title }}</h1>
                <div class="mt-6 space-y-7 text-base text-cyan-600 dark:text-cyan-400">
                    <p>{{ $blog_detail_content_content }} </p>
                </div>
                </div>
            </div>
        </div>
        </div>
    </div>
  </main>
</x-page>
Html;
        $blogs = <<<'Html'
@php
    $locale = $content->getLocale();

    $general_page_banner = $content->getPropertyGroup('general_page_banner');
    $general_page_banner_title = $general_page_banner?->getPropertyData('title')?->getValue($locale);
    $general_page_banner_description = $general_page_banner?->getPropertyData('description')?->getValue($locale);

    $blogs = $content->getChildren()->paginate(3);
@endphp
<x-page :content="$content">
    <main class="flex-auto">
        <div class="sm:px-8 mt-16 sm:mt-32">
          <div class="mx-auto w-full max-w-7xl lg:px-8">
            <div class="relative px-4 sm:px-8 lg:px-12">
              <div class="mx-auto max-w-2xl lg:max-w-5xl">
                <header class="max-w-2xl">
                  <h1 class="text-4xl font-bold tracking-tight text-cyan-800 sm:text-5xl dark:text-cyan-100">{{$general_page_banner_title}}</h1>
                  <p class="mt-6 text-base text-cyan-600 dark:text-cyan-400">{{$general_page_banner_description}}</p>
                </header>
                <div class="mt-16 sm:mt-20">
                  <div class="md:border-l md:border-cyan-100 md:pl-6 md:dark:border-cyan-700/40">
                    <div class="flex max-w-3xl flex-col space-y-16">
                        @foreach ($blogs as $item)
                            @php
                                $blog_detail_content = $item->getPropertyGroup('blog_detail_content');
                                $blog_detail_content_title = $blog_detail_content?->getPropertyData('title')?->getValue($locale);
                                $blog_detail_content_content = $blog_detail_content?->getPropertyData('content')?->getValue($locale);
                            @endphp
                            <blog class="md:grid md:grid-cols-4 md:items-baseline">
                                <div class="md:col-span-3 group relative flex flex-col items-start">
                                    <h2 class="text-base font-semibold tracking-tight text-cyan-800 dark:text-cyan-100">
                                        <div class="absolute -inset-x-4 -inset-y-6 z-0 scale-95 bg-cyan-50 opacity-0 transition group-hover:scale-100 group-hover:opacity-100 sm:-inset-x-6 sm:rounded-2xl dark:bg-cyan-800/50"></div>
                                        <a href="{{$item->getUrl($locale)}}">
                                            <span class="absolute -inset-x-4 -inset-y-6 z-20 sm:-inset-x-6 sm:rounded-2xl"></span>
                                            <span class="relative z-10">{{$blog_detail_content_title}}</span>
                                        </a>
                                    </h2>
                                    <div class="relative z-10 mt-2 text-sm text-cyan-600 dark:text-cyan-400">{{$blog_detail_content_content}}</div>
                                    <div aria-hidden="true" class="relative z-10 mt-4 flex items-center text-sm font-medium text-cyan-500">Read blog <svg viewBox="0 0 16 16" fill="none" aria-hidden="true" class="ml-1 h-4 w-4 stroke-current">
                                        <path d="M6.75 5.75 9.25 8l-2.5 2.25" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </div>
                                </div>
                            </blog>
                        @endforeach
                        {{ $blogs }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
</x-page>   
Html;
        $blog = <<<'Html'
@php
    $locale = $content->getLocale();

    $blog_detail_content = $content->getPropertyGroup('blog_detail_content');
    $blog_detail_content_title = $blog_detail_content?->getPropertyData('title')?->getValue($locale);
    $blog_detail_content_description = $blog_detail_content?->getPropertyData('content')?->getValue($locale);
@endphp
<x-page :content="$content">
    <main class="flex-auto">
        <div class="sm:px-8 mt-16 lg:mt-32">
          <div class="mx-auto w-full max-w-7xl lg:px-8">
            <div class="relative px-4 sm:px-8 lg:px-12">
              <div class="mx-auto max-w-2xl lg:max-w-5xl">
                <div class="xl:relative">
                  <div class="mx-auto max-w-2xl">
                    <button type="button" aria-label="Go back to blogs" class="group mb-8 flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-md shadow-cyan-200 ring-1 ring-cyan-100 transition lg:absolute lg:-left-5 lg:-mt-2 lg:mb-0 xl:-top-1.5 xl:left-0 xl:mt-0 dark:border dark:border-cyan-700/50 dark:bg-cyan-800 dark:ring-0 dark:ring-white dark:hover:border-cyan-700 dark:hover:ring-white" fdprocessedid="saltk" onclick="history.back()">
                      <svg viewBox="0 0 1024 1024" fill="currentColor" class="icon text-cyan-400" version="1.1" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M669.6 849.6c8.8 8 22.4 7.2 30.4-1.6s7.2-22.4-1.6-30.4l-309.6-280c-8-7.2-8-17.6 0-24.8l309.6-270.4c8.8-8 9.6-21.6 2.4-30.4-8-8.8-21.6-9.6-30.4-2.4L360.8 480.8c-27.2 24-28 64-0.8 88.8l309.6 280z" fill=""></path></g></svg>
                    </button>
                    <blog>
                      <header class="flex flex-col">
                        <h1 class="mt-6 text-4xl font-bold tracking-tight text-cyan-800 sm:text-5xl dark:text-cyan-100">{{$blog_detail_content_title}}</h1>
                      </header>
                      <div class="mt-8 text-cyan-800 dark:text-cyan-100">
                        <p>{{$blog_detail_content_description}}</p>
                      </div>
                    </blog>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
</x-page>   
Html;
        $blank_page = <<<'Html'
<x-page :content="$content">

</x-page>   
Html;
        $redirect_page = <<<'Html'
<x-page :content="$content">
    Redirecting...
</x-page>   
Html;

        $items = [
            'home' => $home,
            'about' => $about,
            'blog' => $blog,
            'blogs' => $blogs,
            'projects' => $projects,
            'blank-page' => $blank_page,
            'redirect-page' => $redirect_page,
        ];
        foreach ($items as $slug => $content) {
            $this->importDataService->addTemplate($slug, new ImportDataEntities\Template($slug, $content));
        }
    }

    protected function addSampleFields(): void
    {
        $items[] = [
            'data' => new ImportDataEntities\FieldGroup('general_page_banner'),
            'fields' => [
                new ImportDataEntities\Field('title', 'translate', ['field' => 'text']),
                new ImportDataEntities\Field('description', 'translate', ['field' => 'text']),
                new ImportDataEntities\Field('image', 'mediaPicker', ['mimeTypes' => ['image'], 'multiple' => false]),
            ],
        ];
        $items[] = [
            'data' => new ImportDataEntities\FieldGroup('recently_blogs'),
            'fields' => [
                new ImportDataEntities\Field('blogs', 'contentPicker', ['documentType' => 'blog', 'multiple' => true]),
            ],
        ];
        $items[] = [
            'data' => new ImportDataEntities\FieldGroup('image_slider'),
            'fields' => [
                new ImportDataEntities\Field('image', 'mediaPicker', ['mimeTypes' => ['image'], 'multiple' => true]),
            ],
        ];
        $items[] = [
            'data' => new ImportDataEntities\FieldGroup('social_media'),
            'fields' => [
                new ImportDataEntities\Field('github', 'text'),
                new ImportDataEntities\Field('twitter', 'text'),
                new ImportDataEntities\Field('instagram', 'text'),
                new ImportDataEntities\Field('linkedin', 'text'),
                new ImportDataEntities\Field('email', 'text'),
            ],
        ];
        $items[] = [
            'data' => new ImportDataEntities\FieldGroup('blog_detail_content'),
            'fields' => [
                new ImportDataEntities\Field('title', 'translate', ['field' => 'text']),
                new ImportDataEntities\Field('content', 'translate', ['field' => 'richEditor', 'fieldConfig' => ['toolbarButtons' => array_keys(\SolutionForest\InspireCms\Fields\Configs\RichEditor::getAllAvailableToolbarButtons())]]),
                new ImportDataEntities\Field('image', 'mediaPicker', ['mimeTypes' => ['image'], 'multiple' => false]),
            ],
        ];
        $items[] = [
            'data' => new ImportDataEntities\FieldGroup('projects'),
            'fields' => [
                new ImportDataEntities\Field('projects', 'repeater', [
                    'fields' => [
                        [
                            'field' => 'translate',
                            'name' => 'title',
                            'fieldConfig' => [
                                'field' => 'text',
                            ],
                        ],
                        [
                            'field' => 'translate',
                            'name' => 'description',
                            'fieldConfig' => [
                                'field' => 'textArea',
                            ],
                        ],
                        [
                            'field' => 'text',
                            'name' => 'link',
                            'fieldConfig' => [],
                        ],
                        [
                            'field' => 'mediaPicker',
                            'name' => 'image',
                            'fieldConfig' => ['mimeTypes' => ['image'], 'multiple' => false],
                        ],
                    ],
                ]),
            ],
        ];
        foreach ($items as $item) {
            $group = $item['data'];
            $this->importDataService->addFieldGroup($group->slug, $group, $item['fields']);
        }
    }

    protected function addSampleDocumentTypes(): void
    {
        $items[] = new ImportDataEntities\DocumentType(
            'general-page-banner',
            false,
            'inheritance',
            'Page Banner',
            [
                'general_page_banner',
            ]
        );
        $items[] = new ImportDataEntities\DocumentType(
            'homepage',
            false,
            'web',
            'Homepage',
            [
                'social_media',
            ],
            ['home'],
            'home',
            ['general-page-banner'],
        );
        $items[] = new ImportDataEntities\DocumentType(
            'about',
            false,
            'web',
            'About',
            [
                'blog_detail_content',
            ],
            ['about'],
            'about',
            ['general-page-banner'],
            'homepage',
        );
        $items[] = new ImportDataEntities\DocumentType(
            'blogs',
            true,
            'web',
            'Blogs',
            [],
            ['blogs'],
            'blogs',
            ['general-page-banner'],
            'homepage',
        );
        $items[] = new ImportDataEntities\DocumentType(
            'blog',
            false,
            'web',
            'Blog',
            [
                'blog_detail_content',
            ],
            ['blog'],
            'blog',
            [],
            'blogs',
        );
        $items[] = new ImportDataEntities\DocumentType(
            'projects',
            false,
            'web',
            'Projects',
            [
                'projects',
            ],
            ['projects'],
            'projects',
            ['general-page-banner'],
            'homepage',
        );
        $items[] = new ImportDataEntities\DocumentType(
            'blank_page',
            false,
            'web',
            'Blank Page',
            [],
            ['blank-page', 'redirect-page'],
            'blank-page',
            [],
            'homepage',
        );
        foreach ($items as $item) {
            $this->importDataService->addDocumentType($item->slug, $item);
        }

    }

    protected function addSampleContent(): void
    {
        $items[] = new ImportDataEntities\Content(
            'home',
            ['en' => 'Homepage', 'zh_Hant' => '首頁', 'zh_Hans' => '首页'],
            'homepage',
            [
                'social_media' => [
                    'github' => 'https://github.com',
                    'twitter' => 'https://twitter.com',
                    'instagram' => 'https://instagram.com',
                    'linkedin' => 'https://linkedin.com',
                    'email' => 'test@example.com',
                ],
                'general_page_banner' => [
                    'title' => [
                        'en' => 'Welcome to our website',
                        'zh_Hant' => '歡迎來到我們的網站',
                        'zh_Hans' => '欢迎来到我们的网站',
                    ],
                    'description' => [
                        'en' => 'We provide the best service for you',
                        'zh_Hant' => '我們為您提供最好的服務',
                        'zh_Hans' => '我们为您提供最好的服务',
                    ],
                ],
            ],
            'publish'
        );
        $items[] = new ImportDataEntities\Content(
            'about',
            ['en' => 'About', 'zh_Hant' => '關於', 'zh_Hans' => '关于'],
            'about',
            [
                'blog_detail_content' => [
                    'title' => [
                        'en' => 'About Us',
                        'zh_Hant' => '關於我們',
                        'zh_Hans' => '关于我们',
                    ],
                    'content' => [
                        'en' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla nec purus feugiat, molestie ipsum et, consectetur libero. Donec nec est)</p>',
                        'zh_Hant' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla nec purus feugiat, molestie ipsum et, consectetur libero. Donec nec est)</p>',
                        'zh_Hans' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla nec purus feugiat, molestie ipsum et, consectetur libero. Donec nec est)</p>',
                    ],
                    'image' => $this->mediaAssets[array_rand($this->mediaAssets)]->getKey(),
                ],
            ],
            'publish',
            [],
            [],
            'home',
        );
        $items[] = new ImportDataEntities\Content(
            'blogs',
            ['en' => 'Blogs', 'zh_Hant' => '文章', 'zh_Hans' => '文章'],
            'blogs',
            [
                'general_page_banner' => [
                    'title' => [
                        'en' => 'Blogs',
                        'zh_Hant' => '文章',
                        'zh_Hans' => '文章',
                    ],
                    'description' => [
                        'en' => 'List of blogs',
                        'zh_Hant' => '文章列表',
                        'zh_Hans' => '文章列表',
                    ],
                ],
            ],
            'publish',
            [],
            [],
            'home',
        );

        foreach (range(1, 5) as $i) {

            $items[] = new ImportDataEntities\Content(
                "blog-$i",
                ['en' => "Blog $i", 'zh_Hant' => "文章 $i", 'zh_Hans' => "文章 $i"],
                'blog',
                [
                    'blog_detail_content' => [
                        'title' => [
                            'en' => "Blog $i",
                            'zh_Hant' => "文章 $i",
                            'zh_Hans' => "文章 $i",
                        ],
                        'content' => [
                            'en' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. <b>Nulla nec purus feugiat</b>, molestie ipsum et, consectetur libero. Donec nec est)</p>',
                            'zh_Hant' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. <b>Nulla nec purus feugiat</b>, molestie ipsum et, consectetur libero. Donec nec est)</p>',
                            'zh_Hans' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. <b>Nulla nec purus feugiat</b>, molestie ipsum et, consectetur libero. Donec nec est)</p>',
                        ],
                        'image' => $this->mediaAssets[array_rand($this->mediaAssets)]->getKey(),
                    ],
                ],
                'publish',
                [],
                [],
                'home/blogs',
            );
        }

        $items[] = new ImportDataEntities\Content(
            'projects',
            ['en' => 'Projects', 'zh_Hant' => '項目', 'zh_Hans' => '项目'],
            'projects',
            [
                'general_page_banner' => [
                    'title' => [
                        'en' => 'Projects',
                        'zh_Hant' => '項目',
                        'zh_Hans' => '项目',
                    ],
                    'description' => [
                        'en' => 'List of projects',
                        'zh_Hant' => '項目列表',
                        'zh_Hans' => '项目列表',
                    ],
                ],
                'projects' => [
                    'projects' => [
                        [
                            'title' => [
                                'en' => 'Project 1',
                                'zh_Hant' => '項目 1',
                                'zh_Hans' => '项目 1',
                            ],
                            'description' => [
                                'en' => 'Description of project 1',
                                'zh_Hant' => '項目 1 的描述',
                                'zh_Hans' => '项目 1 的描述',
                            ],
                            'link' => 'https://project1.com',
                            'image' => $this->mediaAssets[array_rand($this->mediaAssets)]->getKey(),
                        ],
                        [
                            'title' => [
                                'en' => 'Project 2',
                                'zh_Hant' => '項目 2',
                                'zh_Hans' => '项目 2',
                            ],
                            'description' => [
                                'en' => 'Description of project 2',
                                'zh_Hant' => '項目 2 的描述',
                                'zh_Hans' => '项目 2 的描述',
                            ],
                            'link' => 'https://project2.com',
                            'image' => $this->mediaAssets[array_rand($this->mediaAssets)]->getKey(),
                        ],
                        [
                            'title' => [
                                'en' => 'Project 3',
                                'zh_Hant' => '項目 3',
                                'zh_Hans' => '项目 3',
                            ],
                            'description' => [
                                'en' => 'Description of project 3',
                                'zh_Hant' => '項目 3 的描述',
                                'zh_Hans' => '项目 3 的描述',
                            ],
                            'link' => 'https://project3.com',
                            'image' => $this->mediaAssets[array_rand($this->mediaAssets)]->getKey(),
                        ],
                    ],
                ],
            ],
            'publish',
            [],
            [],
            'home',
        );

        $items[] = new ImportDataEntities\Content(
            'redirect-page',
            ['en' => 'Redirect Page', 'zh_Hant' => '重定向頁面', 'zh_Hans' => '重定向页面'],
            'blank_page',
            [],
            'publish',
            [],
            ['redirect_path' => '/'],
            'home',
            'redirect-page',
        );

        foreach ($items as $item) {
            $this->importDataService->addContent($item->slug, $item->parent, $item);
        }
    }

    protected function addSampleNavigation(): void
    {
        $navigationData = [
            [
                'title' => ['en' => 'About', 'zh_Hant' => '關於', 'zh_Hans' => '关于'],
                'contentSlugPath' => 'home/about',
                'type' => 'content',
            ],
            [
                'title' => ['en' => 'Blogs', 'zh_Hant' => '文章', 'zh_Hans' => '文章'],
                'contentSlugPath' => 'home/blogs',
                'type' => 'content',
            ],
            [
                'title' => ['en' => 'Projects', 'zh_Hant' => '項目', 'zh_Hans' => '项目'],
                'contentSlugPath' => 'home/projects',
                'type' => 'content',
            ],
            [
                'title' => ['en' => 'Redirect to home page', 'zh_Hant' => '重定向到首頁', 'zh_Hans' => '重定向到首页'],
                'contentSlugPath' => 'home/redirect-page',
                'type' => 'content',
            ],
        ];
        foreach ($navigationData as $data) {

            $this->importDataService->addNavigation(ImportDataEntities\Navigation::fromArray(array_merge($data, [
                'category' => 'main',
            ])));
            $this->importDataService->addNavigation(ImportDataEntities\Navigation::fromArray(array_merge($data, [
                'category' => 'footer',
            ])));
        }
    }

    protected function makeSampleMedia(): void
    {
        $model = InspireCmsConfig::getMediaAssetModelClass();
        $mediaModel = config('media-library.media_model', \Spatie\MediaLibrary\MediaCollections\Models\Media::class);

        if (! $this->isTableExists($model) || ! $this->isTableExists($mediaModel)) {
            return;
        }

        $totalRetry = 5;

        foreach (range(1, 5) as $i) {

            try {

                $dir = storage_path('app/temp');

                if (! is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }

                //Retry x-times to create fake image
                $fakeImage = false;
                $retry = 0;
                while ($fakeImage === false && $retry < $totalRetry) {
                    $fakeImage = fake()->image($dir, 400, 400);
                    $retry++;
                }

                if (! $fakeImage) {
                    continue;
                }

                $filename = pathinfo($fakeImage, PATHINFO_BASENAME);

                /** @var MediaAsset */
                $mediaAsset = $model::create([
                    'title' => $filename,
                    'is_folder' => false,
                ]);

                $mediaAsset->addMedia($fakeImage)->toMediaCollection();

            } catch (\Throwable $th) {
                //
            }

            $this->mediaAssets[] = $mediaAsset;
        }

    }

    protected function makeSampleLanguages(): void
    {
        $model = InspireCmsConfig::getLanguageModelClass();

        if (! $this->isTableExists($model)) {
            return;
        }

        $languagesData = [
            'en' => [
                'is_default' => true,
            ],
            'zh_Hant' => [
                'is_default' => false,
            ],
            'zh_Hans' => [
                'is_default' => false,
            ],
        ];

        foreach ($languagesData as $code => $data) {

            $this->language[$code] = $model::firstOrCreate(['code' => $code], $data);

        }

    }

    protected function isTableExists(string $tableName): bool
    {
        if (! ModelHelper::isTableExists($tableName)) {

            return false;
        }

        return true;
    }
}
