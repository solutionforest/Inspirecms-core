<?php

namespace SolutionForest\InspireCms\Commands;

use Closure;
use Illuminate\Console\Command;
use SolutionForest\InspireCms\Helpers\ModelHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Services\ImportDataServiceInterface;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'inspirecms:import-sample-data')]
class ImportSampleData extends Command
{
    protected array $mediaAssets = [];

    protected array $language = [];

    protected array $templates = [];

    protected array $fieldGroups = [];

    protected array $documentTypes = [];

    protected array $content = [];

    protected ImportDataServiceInterface $importDataService;

    public function handle(ImportDataServiceInterface $importDataService): int
    {
        $this->importDataService = $importDataService;

        // Ensure the default data is imported
        $this->call('inspirecms:import-default-data');

        $this->comment("\nImporting sample data ...");

        $this->call('vendor:publish', [
            '--tag' => 'inspirecms-sample-views',
            '--force' => true,
        ]);

        $this->makeSampleMedia();

        $this->makeSampleLanguages();

        $this->addSampleFields();
        $this->addSampleDocumentTypes();
        $this->addSampleContent();
        $this->addSampleNavigation();
        $this->addSampleTemplates();

        $this->comment("\nImporting sample data ...");
        $this->importDataService->run();

        if ($this->importDataService->hasErrors()) {
            $this->error("\nErrors occurred while importing sample data.");

            return static::FAILURE;
        }

        $this->info("\nSample data imported.");

        return static::SUCCESS;
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

    $recently_articles = $content->getPropertyGroup('recently_articles');
    $recently_articles_articles = $recently_articles?->getPropertyData('articles')?->getValue() ?? [];

    $image_slider = $content->getPropertyGroup('image_slider');
    $image_slider_image = $image_slider?->getPropertyData('image')?->getValue() ?? [];
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
    <div class="mt-16 sm:mt-20" id="slider">
      <div class="mt-6 grid grid-cols-5 gap-y-6 sm:gap-x-6 lg:gap-8">
        @foreach ($image_slider_image as $item)
          <div @class([
            'group relative overflow-hidden rounded-lg sm:aspect-auto',
            'rotate-2' => $loop->even,
            '-rotate-2' => $loop->odd,
          ])>
            <img alt="" loading="lazy" decoding="async" class="h-96 w-full rounded-lg object-cover sm:aspect-[2/3] sm:h-auto" srcset="{{$item->getUrl()}}" >
          </div>
        @endforeach
      </div>
    </div>
    <div class="sm:px-8 mt-24 md:mt-28">
      <div class="mx-auto w-full max-w-7xl lg:px-8">
        <div class="relative px-4 sm:px-8 lg:px-12">
          <div class="mx-auto max-w-2xl lg:max-w-5xl">
            <div class="mx-auto grid max-w-xl grid-cols-1 gap-y-20 lg:max-w-none lg:grid-cols-2">
              <div class="flex flex-col gap-16">
                @foreach ($recently_articles_articles as $item)
                    @php
                      $article_detail_content = $item->getPropertyGroup('article_detail_content');
                      $article_detail_content_title = $article_detail_content?->getPropertyData('title')?->getValue($locale);
                      $article_detail_content_content = $article_detail_content?->getPropertyData('content')?->getValue($locale);
                      @endphp
                    <article class="group relative flex flex-col items-start">
                      <h2 class="text-base font-semibold tracking-tight text-cyan-800 dark:text-cyan-100">
                        <div class="absolute -inset-x-4 -inset-y-6 z-0 scale-95 bg-cyan-50 opacity-0 transition group-hover:scale-100 group-hover:opacity-100 sm:-inset-x-6 sm:rounded-2xl dark:bg-cyan-800/50"></div>
                        <a  href="{{$item->getUrl($locale)}}">
                          <span class="absolute -inset-x-4 -inset-y-6 z-20 sm:-inset-x-6 sm:rounded-2xl"></span>
                          <span class="relative z-10">{{ $article_detail_content_title }}</span>
                        </a>
                      </h2>
                      <div class="relative z-10 mt-2 text-sm text-cyan-600 dark:text-cyan-400">{{$article_detail_content_content}}</div>
                      <div aria-hidden="true" class="relative z-10 mt-4 flex items-center text-sm font-medium text-cyan-500">Read article <svg viewBox="0 0 16 16" fill="none" aria-hidden="true" class="ml-1 h-4 w-4 stroke-current">
                          <path d="M6.75 5.75 9.25 8l-2.5 2.25" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                      </div>
                    </article>
                @endforeach
              </div>
              <div class="space-y-10 lg:pl-16 xl:pl-24">
                <form action="/thank-you" class="rounded-2xl border border-cyan-100 p-6 dark:border-cyan-700/40">
                  <h2 class="flex text-sm font-semibold text-cyan-900 dark:text-cyan-100">
                    <svg viewBox="0 0 1024 1024" fill="currentColor" class="icon text-cyan-800 w-5 h-5" version="1.1" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M538.4 1017.6h-6.4c-80-4-112.8-72-118.4-107.2-2.4-14.4 7.2-28 21.6-29.6h4.8c12.8 0 23.2 8.8 25.6 21.6 0.8 6.4 12.8 60.8 69.6 64h4.8c53.6 0 66.4-61.6 66.4-64.8 2.4-12 12.8-20.8 25.6-20.8 1.6 0 3.2 0 5.6 0.8 6.4 1.6 12.8 5.6 16.8 11.2s5.6 12.8 4 19.2c-8.8 36-43.2 105.6-120 105.6z m-453.6-144c-24 0-43.2-7.2-55.2-20.8-10.4-12-13.6-28-12.8-38.4V784c-3.2-18.4 4-61.6 84-136 78.4-72.8 127.2-271.2 127.2-413.6C228.8 69.6 461.6 48.8 471.2 48h4.8V28C476 16 485.6 6.4 497.6 6.4h21.6c12 0 21.6 9.6 21.6 21.6V48h9.6c10.4 0.8 244.8 20 244.8 185.6 0 140.8 52.8 340.8 132 413.6 80 74.4 80 115.2 79.2 138.4v27.2c0.8 14.4-0.8 28-8.8 38.4-11.2 14.4-28.8 22.4-53.6 22.4H84.8z m-15.2-55.2c0.8 0.8 5.6 3.2 15.2 3.2h868.8l1.6-44.8v-2.4c0-5.6-4.8-32.8-64-87.2-92-86.4-148.8-302.4-148.8-452.8 0-115.2-192.8-133.6-194.4-133.6h-74.4c-20 2.4-192.8 23.2-192.8 133.6 0 132-44.8 360-143.2 452-60.8 56-67.2 84.8-68 90.4l0.8 1.6-0.8 40z" fill=""></path></g></svg>
                    <span class="ml-3">Stay up to date</span>
                  </h2>
                  <p class="mt-2 text-sm text-cyan-600 dark:text-cyan-400">Get notified when I publish something new, and unsubscribe at any time.</p>
                  <div class="mt-6 flex">
                    <input placeholder="Email address" aria-label="Email address" required="" class="min-w-0 flex-auto appearance-none rounded-md border border-cyan-900/10 bg-white px-3 py-[calc(theme(spacing.2)-1px)] shadow-md shadow-cyan-800/5 placeholder:text-cyan-400 focus:border-teal-500 focus:outline-none focus:ring-4 focus:ring-teal-500/10 sm:text-sm dark:border-cyan-700 dark:bg-cyan-700/[0.15] dark:text-cyan-200 dark:placeholder:text-cyan-500 dark:focus:border-teal-400 dark:focus:ring-teal-400/10" type="email" fdprocessedid="szngue">
                    <button class="inline-flex items-center gap-2 justify-center rounded-md py-2 px-3 text-sm outline-offset-2 transition active:transition-none bg-cyan-800 font-semibold text-cyan-100 hover:bg-cyan-700 active:bg-cyan-800 active:text-cyan-100/70 dark:bg-cyan-700 dark:hover:bg-cyan-600 dark:active:bg-cyan-700 dark:active:text-cyan-100/70 ml-4 flex-none" type="submit" fdprocessedid="mt9iq">Join</button>
                  </div>
                </form>
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

    $article_detail_content = $content->getPropertyGroup('article_detail_content');
    $article_detail_content_title = $article_detail_content?->getPropertyData('title')?->getValue($locale);
    $article_detail_content_content = $article_detail_content?->getPropertyData('content')?->getValue($locale);
    $article_detail_content_image = collect($article_detail_content?->getPropertyData('image')?->getValue())->first();
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
                    @if ($article_detail_content_image)
                        <img alt="" loading="lazy" width="800" height="800" decoding="async" data-nimg="1" class="aspect-square rotate-3 rounded-2xl bg-cyan-100 object-cover dark:bg-cyan-800" sizes="(min-width: 1024px) 32rem, 20rem" srcset="{{ $article_detail_content_image->getUrl() }}" style="color: transparent;">
                    @endif
                </div>
                </div>
                <div class="lg:order-first lg:row-span-2">
                <h1 class="text-4xl font-bold tracking-tight text-cyan-800 sm:text-5xl dark:text-cyan-100">{{ $article_detail_content_title }}</h1>
                <div class="mt-6 space-y-7 text-base text-cyan-600 dark:text-cyan-400">
                    <p>{{ $article_detail_content_content }} </p>
                </div>
                </div>
            </div>
        </div>
        </div>
    </div>
  </main>
</x-page>
Html;
        $articles = <<<'Html'
@php
    $locale = $content->getLocale();

    $general_page_banner = $content->getPropertyGroup('general_page_banner');
    $general_page_banner_title = $general_page_banner?->getPropertyData('title')?->getValue($locale);
    $general_page_banner_description = $general_page_banner?->getPropertyData('description')?->getValue($locale);

    $articles = $content->getChildren()->paginate(3);
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
                        @foreach ($articles as $item)
                            @php
                                $article_detail_content = $item->getPropertyGroup('article_detail_content');
                                $article_detail_content_title = $article_detail_content?->getPropertyData('title')?->getValue($locale);
                                $article_detail_content_content = $article_detail_content?->getPropertyData('content')?->getValue($locale);
                            @endphp
                            <article class="md:grid md:grid-cols-4 md:items-baseline">
                                <div class="md:col-span-3 group relative flex flex-col items-start">
                                    <h2 class="text-base font-semibold tracking-tight text-cyan-800 dark:text-cyan-100">
                                        <div class="absolute -inset-x-4 -inset-y-6 z-0 scale-95 bg-cyan-50 opacity-0 transition group-hover:scale-100 group-hover:opacity-100 sm:-inset-x-6 sm:rounded-2xl dark:bg-cyan-800/50"></div>
                                        <a href="{{$item->getUrl($locale)}}">
                                            <span class="absolute -inset-x-4 -inset-y-6 z-20 sm:-inset-x-6 sm:rounded-2xl"></span>
                                            <span class="relative z-10">{{$article_detail_content_title}}</span>
                                        </a>
                                    </h2>
                                    <div class="relative z-10 mt-2 text-sm text-cyan-600 dark:text-cyan-400">{{$article_detail_content_content}}</div>
                                    <div aria-hidden="true" class="relative z-10 mt-4 flex items-center text-sm font-medium text-cyan-500">Read article <svg viewBox="0 0 16 16" fill="none" aria-hidden="true" class="ml-1 h-4 w-4 stroke-current">
                                        <path d="M6.75 5.75 9.25 8l-2.5 2.25" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                        {{ $articles }}
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
        $article = <<<'Html'
@php
    $locale = $content->getLocale();

    $article_detail_content = $content->getPropertyGroup('article_detail_content');
    $article_detail_content_title = $article_detail_content?->getPropertyData('title')?->getValue($locale);
    $article_detail_content_description = $article_detail_content?->getPropertyData('content')?->getValue($locale);
@endphp
<x-page :content="$content">
    <main class="flex-auto">
        <div class="sm:px-8 mt-16 lg:mt-32">
          <div class="mx-auto w-full max-w-7xl lg:px-8">
            <div class="relative px-4 sm:px-8 lg:px-12">
              <div class="mx-auto max-w-2xl lg:max-w-5xl">
                <div class="xl:relative">
                  <div class="mx-auto max-w-2xl">
                    <button type="button" aria-label="Go back to articles" class="group mb-8 flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-md shadow-cyan-200 ring-1 ring-cyan-100 transition lg:absolute lg:-left-5 lg:-mt-2 lg:mb-0 xl:-top-1.5 xl:left-0 xl:mt-0 dark:border dark:border-cyan-700/50 dark:bg-cyan-800 dark:ring-0 dark:ring-white dark:hover:border-cyan-700 dark:hover:ring-white" fdprocessedid="saltk" onclick="history.back()">
                      <svg viewBox="0 0 1024 1024" fill="currentColor" class="icon text-cyan-400" version="1.1" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M669.6 849.6c8.8 8 22.4 7.2 30.4-1.6s7.2-22.4-1.6-30.4l-309.6-280c-8-7.2-8-17.6 0-24.8l309.6-270.4c8.8-8 9.6-21.6 2.4-30.4-8-8.8-21.6-9.6-30.4-2.4L360.8 480.8c-27.2 24-28 64-0.8 88.8l309.6 280z" fill=""></path></g></svg>
                    </button>
                    <article>
                      <header class="flex flex-col">
                        <h1 class="mt-6 text-4xl font-bold tracking-tight text-cyan-800 sm:text-5xl dark:text-cyan-100">{{$article_detail_content_title}}</h1>
                      </header>
                      <div class="mt-8 text-cyan-800 dark:text-cyan-100">
                        <p>{{$article_detail_content_description}}</p>
                      </div>
                    </article>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
</x-page>   
Html;

        $this->importDataService->addTemplate('home', $home);
        $this->importDataService->addTemplate('about', $about);
        $this->importDataService->addTemplate('article', $article);
        $this->importDataService->addTemplate('articles', $articles);
        $this->importDataService->addTemplate('projects', $projects);
    }

    protected function addSampleFields(): void
    {
        $this->importDataService->addFieldGroup('general_page_banner', [
            'title' => [
                'type' => 'translate',
                'config' => [
                    'field' => 'text',
                ],
            ],
            'description' => [
                'type' => 'translate',
                'config' => [
                    'field' => 'text',
                ],
            ],
            'image' => [
                'type' => 'mediaPicker',
                'config' => [
                    'mimeTypes' => ['image'],
                    'multiple' => false,
                ],
            ],
        ]);
        $this->importDataService->addFieldGroup('recently_articles', [
            'articles' => [
                'type' => 'contentPicker',
                'config' => [
                    'documentType' => 'article',
                    'multiple' => true,
                ],
            ],
        ]);
        $this->importDataService->addFieldGroup('image_slider', [
            'image' => [
                'type' => 'mediaPicker',
                'config' => [
                    'mimeTypes' => ['image'],
                    'multiple' => true,
                ],
            ],
        ]);
        $this->importDataService->addFieldGroup('social_media', [
            'github' => [
                'type' => 'text',
            ],
            'twitter' => [
                'type' => 'text',
            ],
            'instagram' => [
                'type' => 'text',
            ],
            'linkedin' => [
                'type' => 'text',
            ],
            'email' => [
                'type' => 'text',
            ],
        ]);
        $this->importDataService->addFieldGroup('article_detail_content', [
            'title' => [
                'type' => 'translate',
                'config' => [
                    'field' => 'text',
                ],
            ],
            'content' => [
                'type' => 'translate',
                'config' => [
                    'field' => 'richEditor',
                    'fieldConfig' => [
                        'toolbarButtons' => array_keys(\SolutionForest\InspireCms\Fields\Configs\RichEditor::getAllAvailableToolbarButtons()),
                    ],
                ],
            ],
            'image' => [
                'type' => 'mediaPicker',
                'config' => [
                    'mimeTypes' => ['image'],
                    'multiple' => false,
                ],
            ],
        ]);
        $this->importDataService->addFieldGroup('projects', [
            'projects' => [
                'type' => 'repeater',
                'config' => [
                    'fields' => [
                        [
                            'field' => 'translate',
                            'name' => 'title',
                            'fieldConfig' => [
                                'field' => 'text',
                                'config' => [],
                            ],
                        ], [
                            'field' => 'translate',
                            'name' => 'description',
                            'fieldConfig' => [
                                'field' => 'textArea',
                                'config' => [],
                            ],
                        ], [
                            'field' => 'text',
                            'name' => 'link',
                        ], [
                            'field' => 'mediaPicker',
                            'name' => 'image',
                            'fieldConfig' => [
                                'mimeTypes' => ['image'],
                                'multiple' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    protected function addSampleDocumentTypes(): void
    {
        $this->importDataService->addDocumentType('general-page-banner', [
            'general_page_banner' => [],
        ], [], null, false, 'inheritance', 'Page Banner');

        $this->importDataService->addDocumentType('homepage', [
            'image_slider' => [],
            'recently_articles' => [],
            'social_media' => [],
        ], 'home', 'general-page-banner', false, 'web');

        $this->importDataService->addDocumentType('about', [
            'article_detail_content' => [],
        ], 'about', null, false, 'web', null, 'homepage');
        $this->importDataService->addDocumentType('articles', [], 'articles', 'general-page-banner', true, 'web', null, 'homepage');
        $this->importDataService->addDocumentType('article', [
            'article_detail_content' => [],
        ], 'article', null, false, 'web', null, 'articles');
        $this->importDataService->addDocumentType('projects', [
            'projects' => [],
        ], 'projects', 'general-page-banner', false, 'web', null, 'homepage');

    }

    protected function addSampleContent(): void
    {
        $this->importDataService->addContent('home', ['en' => 'Homepage', 'zh_Hant' => '首頁', 'zh_Hans' => '首页'], 'homepage', [
            'image_slider' => [
                'image' => collect(array_rand($this->mediaAssets, 5))->map(fn ($i) => $this->mediaAssets[$i]?->getKey())->filter()->all(),
            ],
            'recently_articles' => [],
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
        ], 'publish');

        $this->importDataService->addContent('about', ['en' => 'About', 'zh_Hant' => '關於', 'zh_Hans' => '关于'], 'about', [
            'article_detail_content' => [
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
        ], 'publish', [], [], 'home');

        $this->importDataService->addContent('articles', ['en' => 'Articles', 'zh_Hant' => '文章', 'zh_Hans' => '文章'], 'articles', [], 'publish', [], [], 'home');

        foreach (range(1, 5) as $i) {

            $this->importDataService->addContent(
                "article-$i",
                ['en' => "Article $i", 'zh_Hant' => "文章 $i", 'zh_Hans' => "文章 $i"],
                'article',
                [
                    'article_detail_content' => [
                        'title' => [
                            'en' => "Article $i",
                            'zh_Hant' => "文章 $i",
                            'zh_Hans' => "文章 $i",
                        ],
                        'content' => [
                            'en' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. <b>Nulla nec purus feugiat</b>, molestie ipsum et, consectetur libero. Donec nec est)</p>',
                            'zh_Hant' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. <b>Nulla nec purus feugiat</b>, molestie ipsum et, consectetur libero. Donec nec est)</p>',
                            'zh_Hans' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. <b>Nulla nec purus feugiat</b>, molestie ipsum et, consectetur libero. Donec nec est)</p>',
                        ],
                    ],
                ],
                'publish',
                [],
                [],
                'home/articles'
            );
        }

        $this->importDataService->addContent('projects', ['en' => 'Projects', 'zh_Hant' => '項目', 'zh_Hans' => '项目'], 'projects', [
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
        ], 'publish', [], [], 'home');

        $this->importDataService->addContent('redirect-page', ['en' => 'Redirect Page', 'zh_Hant' => '重定向頁面', 'zh_Hans' => '重定向页面'], 'projects', [], 'publish', [], ['redirect_path' => '/'], 'home');
    }

    protected function addSampleNavigation(): void
    {
        $navigationData = [
            [
                'title' => ['en' => 'About', 'zh_Hant' => '關於', 'zh_Hans' => '关于'],
                'conten' => 'home/about',
                'type' => 'content',
            ],
            [
                'title' => ['en' => 'Articles', 'zh_Hant' => '文章', 'zh_Hans' => '文章'],
                'conten' => 'home/articles',
                'type' => 'content',
            ],
            [
                'title' => ['en' => 'Projects', 'zh_Hant' => '項目', 'zh_Hans' => '项目'],
                'conten' => 'home/projects',
                'type' => 'content',
            ],
            [
                'title' => ['en' => 'Redirect to home page', 'zh_Hant' => '重定向到首頁', 'zh_Hans' => '重定向到首页'],
                'conten' => 'home/redirect-page',
                'type' => 'content',
            ],
        ];
        foreach ($navigationData as $data) {
            $this->importDataService->addNavigation('main', $data['type'], $data['title'], $data['conten'], $data['url'] ?? null, $data['target'] ?? null);
            $this->importDataService->addNavigation('footer', $data['type'], $data['title'], $data['conten'], $data['url'] ?? null, $data['target'] ?? null);
        }
    }

    protected function makeSampleMedia(): void
    {
        $this->comment("\nCreate sample media ...");

        $model = InspireCmsConfig::getMediaAssetModelClass();
        $mediaModel = Media::class;

        if (! $this->isTableExists($model) || ! $this->isTableExists($mediaModel)) {
            return;
        }

        $mediaData = collect(range(1, 5))->map(function () {
            $size = '400x400';
            // Random color
            $backgroundColor = str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
            $foregroundColor = str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);

            $format = 'png';

            // Random text
            $text = str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);

            return "https://dummyimage.com/{$size}/{$backgroundColor}/{$foregroundColor}.{$format}&text={$text}";

        });

        $this->withCustomProgressBar($mediaData, function ($url, $key, $progress) use ($model) {

            $progress->setMessage("Creating media: '$url'");

            /** @var MediaAsset */
            $mediaAsset = $model::create([
                'title' => $url,
                'is_folder' => false,
            ]);
            $mediaAsset->addMediaFromUrl($url)->toMediaCollection();

            $this->mediaAssets[] = $mediaAsset;

        }, 'Creating media ...', 'Media created.');

    }

    protected function makeSampleLanguages(): void
    {
        $this->comment("\nCreate sample languages ...");

        $model = InspireCmsConfig::getLanguageModelClass();

        if (! $this->isTableExists($model)) {
            return;
        }

        $languagesData = [
            'en' => [
                'name' => 'English',
                'is_default' => false,
            ],
            'zh_Hant' => [
                'name' => 'Traditional Chinese',
                'route_pattern' => 'hk',
                'is_default' => true,
            ],
            'zh_Hans' => [
                'name' => 'Simplified Chinese',
                'route_pattern' => 'cn',
                'is_default' => false,
            ],
        ];

        $this->withCustomProgressBar($languagesData, function ($data, $code, $progress) use ($model) {

            $progress->setMessage("Creating language: '$code'");

            $this->language[$code] = $model::firstOrCreate(['code' => $code], $data);

        }, 'Creating languages ...', 'Languages created.');

    }

    protected function isTableExists(string $tableName): bool
    {
        if (! ModelHelper::isTableExists($tableName)) {
            $this->error("Table $tableName does not exist, please run migration first.");

            return false;
        }

        return true;
    }

    protected function withCustomProgressBar($data, Closure $callback, $startMessage = 'Starting ...', $finishedMessage = 'Finished.'): void
    {
        $total = count($data);

        $progress = $this->output->createProgressBar($total);

        $progress->setFormat('%current%/%max% [%bar%] %percent:3s%% %message%');

        $progress->setMessage($startMessage);

        foreach ($data as $key => $value) {

            $callback($value, $key, $progress);

            $progress->advance();
        }

        $progress->setMessage($finishedMessage);
        $progress->finish();
    }
}
