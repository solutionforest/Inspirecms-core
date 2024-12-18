<?php

namespace SolutionForest\InspireCms\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Helpers\ModelHelper;
use SolutionForest\InspireCms\ImportData\Entities as ImportDataEntities;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Services\ContentServiceInterface;
use SolutionForest\InspireCms\Services\ImportDataServiceInterface;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

class SampleSeeder extends Seeder
{
    protected $importDataService;

    protected $contentService;

    protected array $mediaAssets = [];

    protected array $language = [];

    protected array $templates = [];

    protected array $fieldGroups = [];

    protected array $documentTypes = [];

    protected array $content = [];

    public function __construct(ImportDataServiceInterface $importDataService, ContentServiceInterface $contentService)
    {
        $this->importDataService = $importDataService;
        $this->contentService = $contentService;
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

        // handle the content have contentPicker field
        if ($blog = $this->contentService->findByRealPath('home/blog')) {
            $availableBlogs = $this->contentService->getUnderRealPath('blogs');
            $propertyData['featured_blogs']['blogs'] = $availableBlogs->random($availableBlogs->count() >= 3 ? 3 : $availableBlogs->count())->map(fn ($item) => $item->getKey())->toArray();
            $blog->propertyData = json_encode($propertyData);
            $blog->setPublishableState('publish');
            $blog->save();
        }
    }

    protected function addSampleTemplates(): void
    {
        $home = <<<'Html'
@php
    use Illuminate\Support\Arr;
    $locale ??= $content->getLocale();
    $hero_banner = $content->getPropertyGroup('hero_banner');
    $hero_banner_brief = $hero_banner?->getPropertyData('brief')?->getValue($locale);
    if (is_array($hero_banner_brief)) {
        $hero_banner_brief = Arr::first($hero_banner_brief);
    }
    $hero_banner_image_slider = $hero_banner?->getPropertyData('image_slider')?->getValue();
    $profile = $content->getPropertyGroup('profile');
    $profile_brief = $profile?->getPropertyData('brief')?->getValue($locale);
    if (is_array($profile_brief)) {
        $profile_brief = Arr::first($profile_brief);
    }
    $profile_description = $profile?->getPropertyData('description')?->getValue($locale);

    $blogPage = request()->query('blog_page', 1);
    $blogs = inspirecms_page()->getContentUnderRealPath('blogs', $locale)->forPage($blogPage, 3);
@endphp
<x-dynamic-component :component="\SolutionForest\InspireCms\InspireCmsConfig::getComponentWithTheme('page')" :content="$content">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1>
                {!! $hero_banner_brief !!}
            </h1>
        </div>
    </section>

    <!-- Swiper Slider -->
    <div class="swiper">
        <div class="swiper-wrapper">
            @foreach ($hero_banner_image_slider ?? [] as $item)
                <div class="swiper-slide">
                    <img src="{{ $item->getUrl() }}" alt="Slide {{ $loop->iteration }}">
                    <p>{{ $item->description }}</p>
                </div>
            @endforeach
        </div>
        <!-- <div class="swiper-pagination"></div> -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>

    <!-- Profile Section -->
    <section class="profile-section">
        <div class="container">
            <div class="row">
                <div class="col-md-6 intro-title">
                    <p>{{ $profile_brief }}</p>
                    <img src="{{ asset('image/icon/arrow1.svg') }}" alt="">
                </div>
                <div class="col-md-6 intro-text">
                    {{ $profile_description }}
                </div>
            </div>
        </div>
    </section>

    <!-- Blog Section -->
    <section class="blog-section">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-5 ">
                <h2>Latest Posts</h2>
                <a href="/blog" class="view-all-btn">View all
                    <img src="{{ asset('image/icon/arrow1.svg') }}" alt="">
                </a>
            </div>
            <div class="row">
                @foreach ($blogs as $blog)
                    @php
                        $page_banner = $blog->getPropertyGroup('page_banner');
                        $page_banner_title = $page_banner?->getPropertyData('title')?->getValue($locale);
                        $page_banner_description = $page_banner?->getPropertyData('description')?->getValue($locale);
                        $page_banner_image = collect($page_banner?->getPropertyData('image')?->getValue())->first();
                        $blog_content = $blog->getPropertyGroup('blog_content');
                        $blog_content_categories = collect($blog_content?->getPropertyData('categories')->getValue())->implode(', ');
                        $publishTime = $blog->publishAt?->format('d M, Y');
                    @endphp
                    <div class="col-md-4">
                        <div class="blog-card">
                            @if ($page_banner_image)
                                <img src="{{ $page_banner_image->getUrl() }}" alt="{{ $page_banner_image->caption }}">
                            @endif
                            <div class="blog-meta">
                                <span>{{ $blog_content_categories }}</span>
                                <span>{{ $publishTime }}</span>
                            </div>
                            <h3>{{ $page_banner_title }}</h3>
                            <p>{{ $page_banner_description }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    
    @section('scripts')
        <script>
            const swiper = new Swiper('.swiper', {
                slidesPerView: 'auto',
                spaceBetween: 30,
                centeredSlides: true,
                loop: true,
                // pagination: {
                //     el: '.swiper-pagination',
                //     clickable: true,
                // },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                }
            });
        </script>
    @endsection
</x-dynamic-component>
Html;

        $about = <<<'Html'
@php
    $locale ??= $content->getLocale();
    $about_section = $content->getPropertyGroup('about_section');
    $about_section_brief = $about_section?->getPropertyData('brief')?->getValue($locale);
    $about_section_description = $about_section?->getPropertyData('description')?->getValue($locale);
    $about_section_image = collect($about_section?->getPropertyData('image')?->getValue($locale))->first();
    $about_section_resume = collect($about_section?->getPropertyData('resume')?->getValue())->first();
@endphp
<x-dynamic-component :component="\SolutionForest\InspireCms\InspireCmsConfig::getComponentWithTheme('page')" :content="$content">
    <div class="about-container">
        <div class="about-left">
            <h1 class="about-title">{{ $content->getTitle($locale) }}</h1>
            <div class="about-intro">
                {{ $about_section_brief }}
            </div>
            
            <div class="about-image">
                <div class="image-wrapper">
                    @if ($about_section_image)
                        <img src="{{ $about_section_image->getUrl() }}" alt="{{ $about_section_image->caption }}">
                    @endif
                </div>
            </div>
        </div>
    
        <div class="about-right">
            <div class="bio-content">
                {{ $about_section_description }}
    
                @if ($about_section_resume)
                    <button type="submit" class="send-button" onclick="window.open('{{ $about_section_resume->getUrl() }}')">
                        resume
                        <img src="{{ asset('image/icon/arrow1.svg') }}" alt="">
                    </button>
                @endif
            </div>
        </div>
    </div>
</x-dynamic-component> 
Html;

        $contact = <<<'Html'
@php
    $locale ??= $content->getLocale();

    $contact = $content?->getPropertyGroup('contact');
    $contact_email = $contact?->getPropertyData('email')?->getValue();
    $contact_phone = $contact?->getPropertyData('phone')?->getValue();
    $contact_address = $contact?->getPropertyData('address')?->getValue($locale);
    $page_banner = $content->getPropertyGroup('page_banner');
    $page_banner_title = $page_banner?->getPropertyData('title')?->getValue($locale);
    $page_banner_description = $page_banner?->getPropertyData('description')?->getValue($locale);
    
    $config = inspirecms_page()->findContentByRealPath('config', $locale);
    $sns = $config->getPropertyGroup('social_media');
    $sns_email = $sns?->getPropertyData('email')?->getValue();
    $sns_linkedin = $sns?->getPropertyData('linkedin')?->getValue();
    $sns_twitter = $sns?->getPropertyData('twitter')?->getValue();
    $sns_facebook = $sns?->getPropertyData('facebook')?->getValue();
@endphp
<x-dynamic-component :component="\SolutionForest\InspireCms\InspireCmsConfig::getComponentWithTheme('page')" :content="$content">
    <div class="contact-header">
        <div class="header-content">
            <h1 class="contact-title">{{ $page_banner_title }}</h1>
            <p class="contact-text">
                {{ $page_banner_description }}
            </p>
        </div>
    </div>

    <div class="contact-container">
        <div class="contact-left">
            <div class="contact-info">
                <h3>Contact information</h3>
                <address>
                    <p>{{ $contact_address }}</p>
                </address>
                <div class="contact-details">
                    <p><img src="{{ asset('image/icon/tel.svg') }}" alt=""><a href="tel:{{ $contact_phone }}">{{ $contact_phone }}</a></p>
                    <p><img src="{{ asset('image/icon/email.svg') }}" alt=""><a href="mailto:{{ $contact_email }}">{{ $contact_email }}</a></p>
                </div>
            </div>

            <div class="follow-links">
                <h3>Follow Us</h3>
                <div class="social-icons">
                   <a href="{{ $sns_facebook }}"><img src="{{ asset('image/icon/fb.svg') }}" alt=""></a>
                   <a href="{{ $sns_twitter }}"><img src="{{ asset('image/icon/birdx.svg') }}" alt=""></a>
                   <a href="{{ $sns_linkedin }}"><img src="{{ asset('image/icon/ilnkedin.svg') }}" alt=""></a>
                   <a href="{{ $sns_email }}"><img src="{{ asset('image/icon/ball-socialmedia.svg') }}" alt=""></a>
                </div>
            </div>
        </div>

        <div class="contact-right">
            <form class="contact-form">
                <div class="form-group">
                    <label for="name">Name*</label>
                    <input type="text" id="name" placeholder="Walter Moss" required>
                </div>

                <div class="form-group">
                    <label for="email">Email*</label>
                    <input type="email" id="email" placeholder="info@manifest.com" required>
                </div>

                <div class="form-group">
                    <label for="reason">Reason for contact</label>
                    <select id="reason">
                        <option value="" disabled selected>Select reason</option>
                        <option value="support">Technical Support</option>
                        <option value="account">Account Issues</option>
                        <option value="billing">Billing Questions</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="message">Message*</label>
                    <textarea id="message" placeholder="Hi there..." required></textarea>
                </div>

                <button type="submit" class="send-button">
                    Send
                    <img src="{{ asset('image/icon/arrow1.svg') }}" alt="">
                </button>
            </form>
        </div>
    </div>
</x-dynamic-component>
Html;

        $case_studies = <<<'Html'
@php
    $locale ??= $content->getLocale();
    $page_banner = $content->getPropertyGroup('page_banner');
    $page_banner_title = $page_banner?->getPropertyData('title')?->getValue($locale);
    $page_banner_description = $page_banner?->getPropertyData('description')?->getValue($locale);

    $cases = $content->getChildren()->paginate(3);
@endphp
<x-dynamic-component :component="\SolutionForest\InspireCms\InspireCmsConfig::getComponentWithTheme('page')" :content="$content">
    <div class="contact-header">
        <div class="header-content">
            <h1 class="contact-title">{{ $page_banner_title }}</h1>
            <p class="contact-text">
                {{ $page_banner_description }}
            </p>
        </div>
    </div>
    <div class="works-container">
        <div class="works-left">
            <div class="works-list">
                @foreach ($cases as $item)
                    @php
                        $page_banner = $item->getPropertyGroup('page_banner');
                        $page_banner_image = collect($page_banner?->getPropertyData('image')?->getValue($locale))->first();
                        $case_content = $item->getPropertyGroup('case_content');
                        $case_content_category = $case_content?->getPropertyData('category')?->getValue($locale);
                        $case_content_year = $case_content?->getPropertyData('year')?->getValue($locale);
                    @endphp
                    <article class="work-item">
                        <div class="work-content">
                            <h3>{{ $item->getTitle($locale) }}</h3>
                            <div class="divider1"></div>
                            <p class="category">{{ $case_content_category }}</p>
                            <p class="year">{{ $case_content_year }}</p>
                            
                            <button type="submit" class="send-button" onclick="window.location.href = '{{ $item->getUrl() }}'">
                                View More
                                <img src="{{ asset('image/icon/arrow1.svg') }}" alt="">
                            </button>
                        </div>
                        <div class="work-image">
                            @if ($page_banner_image)
                                <img src="{{ $page_banner_image->getUrl() }}" alt="{{ $page_banner_image->caption }}">
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</x-dynamic-component>
Html;

        $case_study = <<<'Html'
@php
    $locale ??= $content->getLocale();
    $page_banner = $content->getPropertyGroup('page_banner');
    $page_banner_title = $page_banner?->getPropertyData('title')?->getValue($locale);
    $page_banner_image = collect($page_banner?->getPropertyData('image')?->getValue($locale))->first();
    $case_content = $content->getPropertyGroup('case_content');
    $case_content_overview = $case_content?->getPropertyData('overview')?->getValue($locale);
    $case_content_category = $case_content?->getPropertyData('category')?->getValue($locale);
    $case_content_year = $case_content?->getPropertyData('year')?->getValue($locale);
    $case_content_deliverables = $case_content?->getPropertyData('deliverables')?->getValue($locale);
    $case_content_platforms = collect($case_content?->getPropertyData('platforms')?->getValue($locale))->implode(', ');
    $case_content_roles = collect($case_content?->getPropertyData('roles')?->getValue($locale))->implode(', ');
    $case_content_ = collect($case_content?->getPropertyData('roles')?->getValue($locale))->implode(', ');
    $case_content_content = $case_content?->getPropertyData('content')?->getValue($locale);
@endphp
<x-dynamic-component :component="\SolutionForest\InspireCms\InspireCmsConfig::getComponentWithTheme('page')" :content="$content">
    <div class="case-study-container">
        <header class="case-study-header">
            <p class="label">CASE STUDY</p>
            <h1 class="title">{{ $page_banner_title }}</h1>

            <div class="hero-image">
                @if ($page_banner_image)
                    <img src="{{ $page_banner_image->getUrl() }}" alt="{{ $page_banner_image->caption }}">
                @endif
            </div>
        </header>

        <div class="case-study-content">
            <div class="main-content">
                <div class="case-study-content">
                    <div class="">
                        <div class="row overview-section">
                            <div class="col-md-8 overview-left">
                                <h2>Project Overview</h2>
                                {{ $case_content_overview }}
                            </div>

                            <div class="col-md-4 overview-right">
                                <div class="details-grid">
                                    <div class="detail-item">
                                        <h3>Year</h3>
                                        <p>{{ $case_content_year }}</p>
                                    </div>
                                    <div class="detail-item">
                                        <h3>Platform</h3>
                                        <p>{{ $case_content_platforms }}</p>
                                    </div>
                                    <div class="detail-item">
                                        <h3>Role</h3>
                                        <p>{{ $case_content_roles }}</p>
                                    </div>
                                    <div class="detail-item">
                                        <h3>Deliverables</h3>
                                        <p>{{ $case_content_deliverables }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{ $case_content_content }}
                </div>
            </div>
        </div>
    </div>
</x-dynamic-component>
Html;

        $blogs = <<<'Html'
@php
    $blogPage = request()->query('page') ?? 1;
    $featuredPage = request()->query('featured') ?? 1;

    $locale ??= $content->getLocale();
    $blogs = inspirecms_page()->getContentUnderRealPath('blogs', $locale)->paginate(perPage: 4, pageName: 'page', page: $blogPage);

    $featured_blogs = $content->getPropertyGroup('featured_blogs');
    $featured_blogs_blogs = collect($featured_blogs?->getPropertyData('blogs')?->getValue());
    $featured_blogs_blog = $featured_blogs_blogs->forPage($featuredPage, 1)->first();
@endphp
<x-dynamic-component :component="\SolutionForest\InspireCms\InspireCmsConfig::getComponentWithTheme('page')" :content="$content">
    <div class="page-main-container">
        <section class="blog-section-ma">
            <!-- Hero Section -->
            <section class="hero-section">
                <div class="container-fluid ps-0">
                    <h1>{{ $content->getTitle($locale) }}</h1>
                </div>
            </section>
            <div class="container ps-0">
                <!-- Featured Blog Post -->
                @php
                    $featured_blog_page_banner = $featured_blogs_blog?->getPropertyGroup('page_banner');
                    $featured_blog_page_banner_title = $featured_blog_page_banner?->getPropertyData('title')?->getValue($locale);
                    $featured_blog_page_banner_description = $featured_blog_page_banner?->getPropertyData('description')?->getValue($locale);
                    $featured_blog_page_banner_image = collect($featured_blog_page_banner?->getPropertyData('image')?->getValue())->first();
                    $featured_blog_blog_content = $featured_blogs_blog?->getPropertyGroup('blog_content');
                    $featured_blog_blog_content_categories = collect($featured_blog_blog_content?->getPropertyData('categories')->getValue());
                    $publishTime = $featured_blogs_blog?->publishAt?->format('d M, Y');
                @endphp
                <div class="featured-post">
                    <div class="row align-items-start">
                        <div class="col-md-6">
                            @if ($featured_blog_page_banner_image)
                                <img src="{{ $featured_blog_page_banner_image->getUrl() }}" alt="{{ $featured_blog_page_banner_image->caption }}" class="featured-image">
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="featured-content">
                                <div class="post-categories">
                                    @foreach ($featured_blog_blog_content_categories as $category)
                                        <span class="category">{{ $category }}</span>
                                        @if (!$loop->last)
                                            <span class="separator">,</span>
                                        @endif
                                    @endforeach
                                </div>
                                <h2 class="featured-title">{{ $featured_blog_page_banner_title }}</h2>
                                <div class="post-date">{{ $publishTime }}</div>
                                <p class="featured-excerpt">
                                    {{ $featured_blog_page_banner_description }}
                                    <span class=""> <img src="{{ asset('image/icon/arrow1.svg') }}" alt=""></span>
                                </p>
                                <div class="pagination">
                                    @foreach ($featured_blogs_blogs as $item)
                                        <a href="{{ request()->fullUrlWithQuery(['featured' => $loop->iteration]) }}" class="text-decoration-none text-dark">
                                            <span @class(['active' => $featuredPage == $loop->iteration])>{{ $loop->iteration }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="categories-wrapper">
                    <label class="categories-label">Categories:</label>
                    <div class="categories-select">
                        <select class="custom-select">
                            <option value="interaction-design">Interaction Design</option>
                            <option value="ui-design">UI Design</option>
                            <option value="ux-design">UX Design</option>
                            <option value="user-research">User Research</option>
                        </select>
                    </div>
                </div>
                <!-- Regular Blog Posts Grid -->
                <div class="row blog-posts-grid">
                    @foreach ($blogs as $blog)
                        @php
                            $page_banner = $blog->getPropertyGroup('page_banner');
                            $page_banner_title = $page_banner?->getPropertyData('title')?->getValue($locale);
                            $page_banner_description = $page_banner?->getPropertyData('description')?->getValue($locale);
                            $page_banner_image = collect($page_banner?->getPropertyData('image')?->getValue())->first();
                            $blog_content = $blog->getPropertyGroup('blog_content');
                            $blog_content_categories = collect($blog_content?->getPropertyData('categories')->getValue())->implode(', ');
                            $publishTime = $blog->publishAt?->format('d M, Y');
                        @endphp
                        <!-- Post -->
                        <div class="col-12 col-md-6 mb-4">
                            <a href="{{ $blog->getUrl() }}" class="text-decoration-none">
                                <article class="blog-post">
                                    @if ($page_banner_image)
                                        <img src="{{ $page_banner_image?->getUrl() }}" alt="{{ $page_banner_image->caption }}" class="blog-image">
                                    @endif
                                    <div class="post-content">
                                        <div class="post-meta">
                                            <span class="category">{{ $blog_content_categories }}</span>
                                            <span class="date">{{ $publishTime }}</span>
                                        </div>
                                        <h3>{{ $page_banner_title }}</h3>
                                        <p>{{ $page_banner_description }}</p>
                                    </div>
                                </article>
                            </a>
                        </div>
                    @endforeach
                </div>

                <div class="pagination mt-4 mb-4">
                    {{ $blogs->links('components.'.\SolutionForest\InspireCms\InspireCmsConfig::getComponentWithTheme('pagination'), ['paginator' => $blogs]) }}
                </div>
            </div>
        </section>
    </div>
</x-dynamic-component>
Html;

        $blog = <<<'Html'
@php
    $locale ??= $content->getLocale();

    $social_media = $content->getPropertyGroup('social_media');
    
    $blog_content = $content->getPropertyGroup('blog_content');
    $blog_content_content = $blog_content?->getPropertyData('content')?->getValue($locale);
    $blog_content_categories = collect($blog_content?->getPropertyData('categories')?->getValue());
    $blog_content_tags = collect($blog_content?->getPropertyData('tags')?->getValue());

    $page_banner = $content->getPropertyGroup('page_banner');
    $page_banner_title = $page_banner?->getPropertyData('title')?->getValue($locale);
    $page_banner_image = collect($page_banner?->getPropertyData('image')?->getValue())->first();
    $page_banner_description = $page_banner?->getPropertyData('description')?->getValue($locale);

    $publishTime = $content->publishAt?->format('d M, Y');
@endphp
<x-dynamic-component :component="\SolutionForest\InspireCms\InspireCmsConfig::getComponentWithTheme('page')" :content="$content">
    <article class="blog-post-single page-main-container">
        <!-- Post Header - Full Width -->
        <div class="container-fluid ps-0 pe-0">
            <header class="post-header">
                <div class="post-meta">
                    @foreach ($blog_content_categories as $category)
                        <span class="category">{{ $category }}</span>
                        @if (!$loop->last)
                            <span class="separator">,</span>
                        @endif
                    @endforeach
                </div>
                <h1 class="post-title">{{ $page_banner_title }}</h1>
                <div class="header-meta">
                    <span class="post-date">{{ $publishTime }}</span>
                    <span class="post-date"> <img src="{{ asset('image/icon/comment-dialog-box.svg') }}" alt="" class="me-2">8 comments</span>
                </div>

            </header>

            <!-- Featured Image - Full Width -->
            <div class="post-featured-image">
                @if ($page_banner_image)
                    <img src="{{ $page_banner_image->getUrl() }}" alt="{{ $page_banner_image->caption }}">
                @endif
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="post-content-wrapper">
            <div class="container">
                <div class="row">
                    <!-- Left Column - Social Media -->
                    <div class="col-md-1 ps-0 pe-0">
                        <div class="social-share-fixed">
                            <ul class="social-icons">
                                <li><a href="#"><img src="{{ asset('image/icon/fb.svg') }}" alt=""></a></li>
                                <li><a href="#"><img src="{{ asset('image/icon/birdx.svg') }}" alt=""></a></li>
                                <li><a href="#"><img src="{{ asset('image/icon/ilnkedin.svg') }}" alt=""></a></li>
                                <li><a href="#"><img src="{{ asset('image/icon/ball-socialmedia.svg') }}" alt=""></a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Right Column - Main Content -->
                    <div class="col-md-11 ps-0 pe-0">
                        <div class="main-content">
                            <!-- Author Info -->
                            <div class="author-info">
                                <img src="./assets/image/home/Img 01.png" alt="Author Avatar" class="author-avatar">
                                <span class="author-name">Marina Silva</span>
                            </div>
                            <div class="mobile-social-icon">
                                <ul class="social-icons">
                                    <li><a href="#"><img src="{{ asset('image/icon/fb.svg') }}" alt=""></a></li>
                                    <li><a href="#"><img src="{{ asset('image/icon/birdx.svg') }}" alt=""></a></li>
                                    <li><a href="#"><img src="{{ asset('image/icon/ilnkedin.svg') }}" alt=""></a></li>
                                    <li><a href="#"><img src="{{ asset('image/icon/ball-socialmedia.svg') }}" alt=""></a></li>
                                </ul>
                            </div>
                            <!-- Post Content -->
                            <div class="post-content">
                                {{ $blog_content_content }}
                            </div>
                            <!-- tag -->
                            <div class="tag-wrapper">
                                <span>Tags: </span>
                                <span class="post-tag">
                                    @foreach ($blog_content_tags as $tag)
                                        <span class="category">{{ $tag }}</span>
                                        @if (!$loop->last)
                                            <span class="separator">,</span>
                                        @endif
                                    @endforeach
                                </span>
                            </div>

                            
                            <x-dynamic-component :component="\SolutionForest\InspireCms\InspireCmsConfig::getComponentWithTheme('comments')" />
                        </div>
                    </div>
                </div>
            </div>
    </article>
</x-dynamic-component>
Html;

        $items = [
            'home' => $home,
            'about' => $about,
            'blog' => $blog,
            'blogs' => $blogs,
            'contact' => $contact,
            'case-studies' => $case_studies,
            'case-study' => $case_study,
        ];
        foreach ($items as $slug => $content) {
            $this->importDataService->addTemplate($slug, new ImportDataEntities\Template(slug: $slug, content: $content));
        }
    }

    protected function addSampleFields(): void
    {
        $toolbarButtonsForRichEditor = array_keys(\SolutionForest\InspireCms\Fields\Configs\RichEditor::getAllAvailableToolbarButtons());
        $items[] = [
            'data' => new ImportDataEntities\FieldGroup(slug: 'social_media'),
            'fields' => [
                new ImportDataEntities\Field(slug: 'github', type: 'text'),
                new ImportDataEntities\Field(slug: 'twitter', type: 'text'),
                new ImportDataEntities\Field(slug: 'instagram', type: 'text'),
                new ImportDataEntities\Field(slug: 'linkedin', type: 'text'),
                new ImportDataEntities\Field(slug: 'email', type: 'text'),
                new ImportDataEntities\Field(slug: 'facebook', type: 'text'),
            ],
        ];
        $items[] = [
            'data' => new ImportDataEntities\FieldGroup(slug: 'hero_banner'),
            'fields' => [
                new ImportDataEntities\Field(slug: 'brief', type: 'richEditor', config: ['translatable' => true, 'toolbarButtons' => $toolbarButtonsForRichEditor]),
                new ImportDataEntities\Field(slug: 'image_slider', type: 'mediaPicker', config: ['mimeTypes' => ['image'], 'multiple' => true]),
            ],
        ];
        $items[] = [
            'data' => new ImportDataEntities\FieldGroup(slug: 'profile'),
            'fields' => [
                new ImportDataEntities\Field(slug: 'brief', type: 'richEditor', config: ['translatable' => true, 'toolbarButtons' => $toolbarButtonsForRichEditor]),
                new ImportDataEntities\Field(slug: 'description', type: 'richEditor', config: ['translatable' => true, 'toolbarButtons' => $toolbarButtonsForRichEditor]),
            ],
        ];
        $items[] = [
            'data' => new ImportDataEntities\FieldGroup(slug: 'about_section'),
            'fields' => [
                new ImportDataEntities\Field(slug: 'brief', type: 'richEditor', config: ['translatable' => true, 'toolbarButtons' => $toolbarButtonsForRichEditor]),
                new ImportDataEntities\Field(slug: 'description', type: 'richEditor', config: ['translatable' => true, 'toolbarButtons' => $toolbarButtonsForRichEditor]),
                new ImportDataEntities\Field(slug: 'image', type: 'mediaPicker', config: ['mimeTypes' => ['image'], 'multiple' => false]),
                new ImportDataEntities\Field(slug: 'resume', type: 'mediaPicker', config: ['mimeTypes' => ['pdf'], 'multiple' => false]),
            ],
        ];
        $items[] = [
            'data' => new ImportDataEntities\FieldGroup(slug: 'page_banner'),
            'fields' => [
                new ImportDataEntities\Field(slug: 'title', type: 'text', config: ['translatable' => true]),
                new ImportDataEntities\Field(slug: 'description', type: 'text', config: ['translatable' => true]),
                new ImportDataEntities\Field(slug: 'image', type: 'mediaPicker', config: ['mimeTypes' => ['image'], 'multiple' => false]),
            ],
        ];
        $items[] = [
            'data' => new ImportDataEntities\FieldGroup(slug: 'blog_content'),
            'fields' => [
                new ImportDataEntities\Field(slug: 'categories', type: 'tags', config: ['translatable' => false]),
                new ImportDataEntities\Field(slug: 'tags', type: 'tags', config: ['translatable' => false]),
                new ImportDataEntities\Field(slug: 'content', type: 'richEditor', config: ['translatable' => true, 'toolbarButtons' => $toolbarButtonsForRichEditor]),
            ],
        ];
        $items[] = [
            'data' => new ImportDataEntities\FieldGroup(slug: 'contact'),
            'fields' => [
                new ImportDataEntities\Field(slug: 'address', type: 'richEditor', config: ['translatable' => false, 'toolbarButtons' => $toolbarButtonsForRichEditor]),
                new ImportDataEntities\Field(slug: 'phone', type: 'text'),
                new ImportDataEntities\Field(slug: 'email', type: 'text'),
                new ImportDataEntities\Field(slug: 'map', type: 'text'),
            ],
        ];
        $items[] = [
            'data' => new ImportDataEntities\FieldGroup(slug: 'case_content'),
            'fields' => [
                new ImportDataEntities\Field(slug: 'category', type: 'text', config: ['translatable' => false]),
                new ImportDataEntities\Field(slug: 'overview', type: 'richEditor', config: ['translatable' => true, 'toolbarButtons' => $toolbarButtonsForRichEditor]),
                new ImportDataEntities\Field(slug: 'year', type: 'dateTimePicker', config: ['hasTime' => false, 'hasDate' => true, 'displayFormat' => 'Y']),
                new ImportDataEntities\Field(slug: 'platforms', type: 'tags', config: ['translatable' => false]),
                new ImportDataEntities\Field(slug: 'roles', type: 'tags', config: ['translatable' => false]),
                new ImportDataEntities\Field(slug: 'deliverables', type: 'url', config: ['translatable' => false]),
                new ImportDataEntities\Field(slug: 'content', type: 'richEditor', config: ['translatable' => true, 'toolbarButtons' => $toolbarButtonsForRichEditor]),
            ],
        ];
        $items[] = [
            'data' => new ImportDataEntities\FieldGroup(slug: 'featured_blogs'),
            'fields' => [
                new ImportDataEntities\Field(slug: 'blogs', type: 'contentPicker', config: ['translatable' => false, 'allowedDocumentTypes' => ['blog'], 'multiple' => true]),
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
            slug: 'homepage',
            showAsTable: false,
            category: 'web',
            fieldGroups: [
                'hero_banner',
                'profile',
            ],
            templates: ['home'],
            defaultTemplate: 'home',
            inheritance: [], // ['general-page-banner'],
            icon: 'heroicon-o-home',
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'about',
            showAsTable: false,
            category: 'web',
            fieldGroups: [
                'about_section',
            ],
            templates: ['about'],
            defaultTemplate: 'about',
            inheritance: [], // ['general-page-banner'],
            icon: 'heroicon-o-information-circle',
            rejected: ['homepage'],
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'blogs',
            showAsTable: false,
            category: 'web',
            fieldGroups: ['featured_blogs'],
            templates: ['blogs'],
            defaultTemplate: 'blogs',
            inheritance: [], // ['general-page-banner'],
            icon: 'heroicon-o-newspaper',
            rejected: ['homepage'],
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'contact-us',
            showAsTable: false,
            category: 'web',
            fieldGroups: [
                'page_banner',
                'contact',
            ],
            templates: ['contact'],
            defaultTemplate: 'contact',
            icon: 'heroicon-o-question-mark-circle',
            rejected: ['homepage'],
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'case-studies',
            showAsTable: true,
            category: 'web',
            fieldGroups: [
                'page_banner',
            ],
            templates: ['case-studies'],
            defaultTemplate: 'case-studies',
            icon: 'heroicon-o-clipboard-document-list',
            rejected: ['homepage'],
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'case-study',
            showAsTable: false,
            category: 'web',
            fieldGroups: [
                'page_banner',
                'case_content',
            ],
            templates: ['case-study'],
            defaultTemplate: 'case-study',
            icon: 'heroicon-o-clipboard-document-check',
            rejected: ['homepage'],
        );

        $items[] = new ImportDataEntities\DocumentType(
            slug: 'config',
            showAsTable: false,
            category: 'data',
            fieldGroups: [
                'social_media',
            ],
            templates: [],
            defaultTemplate: null,
            inheritance: [], // ['general-page-banner'],
            icon: 'heroicon-o-cog-6-tooth',
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'blog-management',
            showAsTable: true,
            category: 'data',
            fieldGroups: [],
            templates: [],
            defaultTemplate: null,
            inheritance: [], // ['general-page-banner'],
            icon: 'heroicon-o-newspaper',
        );
        $items[] = new ImportDataEntities\DocumentType(
            slug: 'blog',
            showAsTable: false,
            category: 'data',
            fieldGroups: [
                'page_banner',
                'social_media',
                'blog_content',
            ],
            templates: ['blog'],
            defaultTemplate: 'blog',
            icon: 'heroicon-o-newspaper',
            rejected: ['homepage'],
        );

        foreach ($items as $item) {
            switch ($item->slug) {
                case 'blog-management': 
                    $item->rejected = collect($items)->map(fn ($item) => $item->slug)->filter(fn ($slug) => $slug !== 'blog')->toArray();
                    break;
                case 'blog': 
                case 'config': 
                    $item->rejected = collect($items)->map(fn ($item) => $item->slug)->toArray();
                    break;
                default:
                    $item->rejected = array_unique(array_merge($item->rejected, ['blog']));
                    break;
            }
            $this->importDataService->addDocumentType($item->slug, $item);
        }

    }

    protected function addSampleContent(): void
    {
        $items[] = new ImportDataEntities\Content(
            slug: 'home',
            title: ['en' => 'Homepage', 'fr' => 'Page d\'accueil'],
            documentType: 'homepage',
            isDefault: true,
            properties: [
                'hero_banner' => [
                    'brief' => [
                        'en' => 'Manifest is a newborn theme. <br/> Clean, simple and fast.',
                        'fr' => 'Manifest est un thème nouveau-né. <br/> Propre, simple et rapide.',
                    ],
                    'image_slider' => $this->getRandomMediaAssetKeys(3, 'png'),
                ],
                'profile' => [
                    'brief' => [
                        'en' => 'Full-time UI/UX designer <br/> Head of Design at VeronaLabs.com',
                        'fr' => 'Designer UI/UX à plein temps <br/> Responsable du design chez VeronaLabs.com',
                    ],
                    'description' => [
                        'en' => '<p>We work with clients around the world from our headquarters in Charlotte, South Carolina</p><p>We focus on naming, branding, brand innovation, mobility design and development, and brand experiences.</p>',
                        'fr' => '<p>Nous travaillons avec des clients du monde entier depuis notre siège social à Charlotte, en Caroline du Sud</p><p>Nous nous concentrons sur la dénomination, le branding, l’innovation de la marque, la conception et le développement de la mobilité, et les expériences de marque.</p>',
                    ],
                ],
            ],
            publishState: 'publish'
        );
        $items[] = new ImportDataEntities\Content(
            slug: 'blogs',
            title: ['en' => 'Blog Management', 'fr' => 'Gestion des blogs'],
            documentType: 'blog-management',
            properties: [],
            publishState: 'publish',
            parent: null,
            sitemap: ['enable' => false],
        );
        $items[] = new ImportDataEntities\Content(
            slug: 'config',
            title: ['en' => 'Config', 'fr' => 'Config'],
            documentType: 'config',
            properties: [
                'social_media' => [
                    'facebook' => 'https://facebook.com',
                    'twitter' => 'https://twitter.com',
                    'linkedin' => 'https://linkedin.com',
                    'instagram' => 'https://instagram.com',
                ],
            ],
            publishState: 'publish',
            parent: null,
            sitemap: ['enable' => false],
        );

        $items[] = new ImportDataEntities\Content(
            slug: 'about',
            title: ['en' => 'About', 'fr' => 'À propos'],
            documentType: 'about',
            properties: [
                'about_section' => [
                    'brief' => [
                        'en' => "<p>I'm Manifest</p><p>Full-time UI/UX designer</p><p>Head of Design at VeronaLabs.com</p>",
                        'fr' => '<p>Je suis Manifest</p><p>Designer UI/UX à plein temps</p><p>Responsable du design chez VeronaLabs.com</p>',
                    ],
                    'image' => Arr::first($this->getRandomMediaAssetKeys(1, 'png')),
                    'description' => [
                        'en' => '<p>I was born in January 1990. After getting my Degree in computer science in 2002, I persuaded my higher study in Human Computer Interaction Design. I got my first job as Graphic Designer in the year 2008. After getting experience in graphic for a year, I moved to UI-UX Designing.</p><p>In 2010, I decided to work as a Freelance Web, UI-UX & Mobile Interface Designer. I find myself still in the learning phase and have strong desire to achieve as many skills as I can.</p>',
                        'fr' => '<p>Je suis né en janvier 1990. Après avoir obtenu mon diplôme en informatique en 2002, j’ai poursuivi mes études supérieures en conception d’interaction homme-machine. J’ai obtenu mon premier emploi en tant que graphiste en 2008. Après avoir acquis de l’expérience en graphisme pendant un an, je suis passé à la conception UI-UX.</p><p>En 2010, j’ai décidé de travailler en tant que designer d’interface Web, UI-UX et mobile indépendant. Je me trouve toujours en phase d’apprentissage et j’ai un fort dés ir d’acquérir autant de compétences que possible.</p>',
                    ],
                    'resume' => Arr::first($this->getRandomMediaAssetKeys(1, 'pdf')),
                ],
            ],
            publishState: 'publish',
            parent: 'home',
        );
        $items[] = new ImportDataEntities\Content(
            slug: 'blog',
            title: ['en' => 'Blog', 'fr' => 'Blog'],
            documentType: 'blogs',
            properties: [
                'featured_blogs' => [],
            ],
            publishState: 'publish',
            parent: 'home',
        );
        foreach (range(1, 10) as $i) {

            $items[] = new ImportDataEntities\Content(
                slug: "blog-$i",
                title: ['en' => "Blog $i", 'fr' => "Blog $i"],
                documentType: 'blog',
                properties: [
                    'page_banner' => [
                        'title' => [
                            'en' => fake()->sentence(5),
                            'fr' => fake()->sentence(5),
                        ],
                        'image' => Arr::first($this->getRandomMediaAssetKeys(1, 'png')),
                        'description' => [
                            'en' => fake()->sentence(10),
                            'fr' => fake()->sentence(10),
                        ],
                    ],
                    'blog_content' => [
                        'categories' => ['Technology', 'Interface Design'],
                        'tags' => ['Technology', 'Interface Design', 'Visual Design'],
                        'content' => [
                            'en' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. <b>Nulla nec purus feugiat</b>, molestie ipsum et, consectetur libero. Donec nec est)</p>',
                            'fr' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. <b>Nulla nec purus feugiat</b>, molestie ipsum et, consectetur libero. Donec nec est)</p>',
                        ],
                    ],
                    'social_media' => [
                        'facebook' => 'https://facebook.com',
                        'twitter' => 'https://twitter.com',
                        'linkedin' => 'https://linkedin.com',
                    ],
                ],
                publishState: 'publish',
                parent: 'blogs',
                sitemap: ['enable' => false],
            );
        }

        $items[] = new ImportDataEntities\Content(
            slug: 'contact-us',
            title: ['en' => 'Contact Us', 'fr' => 'Contactez-nous'],
            documentType: 'contact-us',
            properties: [
                'page_banner' => [
                    'title' => [
                        'en' => 'Contact Us',
                        'fr' => 'Contactez-nous',
                    ],
                    'description' => [
                        'en' => 'If you need our help with your user account, have questions about how to use the platform or are experiencing technical difficulties, please do not hesitate to contact us.',
                        'fr' => 'Si vous avez besoin de notre aide pour votre compte utilisateur, si vous avez des questions sur l’utilisation de la plateforme ou si vous rencontrez des difficultés techniques, n’hésitez pas à nous contacter.',
                    ],
                ],
                'contact' => [
                    'email' => 'example@example.com',
                    'phone' => '+1234567890',
                    'address' => '<p>486 Rahi street, Berlin .98</p><p>Germany</p>',
                ],
            ],
            publishState: 'publish',
            parent: 'home',
        );

        $items[] = new ImportDataEntities\Content(
            slug: 'case-studies',
            title: ['en' => 'Works', 'fr' => 'Travaux'],
            documentType: 'case-studies',
            properties: [
                'page_banner' => [
                    'title' => [
                        'en' => 'Works',
                        'fr' => 'Travaux',
                    ],
                    'description' => [
                        'en' => 'If you need our help with your user account, have questions about how to use the platform or are experiencing technical difficulties, please do not hesitate to contact us.',
                        'fr' => 'Si vous avez besoin de notre aide pour votre compte utilisateur, si vous avez des questions sur l’utilisation de la plateforme ou si vous rencontrez des difficultés techniques, n’hésitez pas à nous contacter.',
                    ],
                ],
            ],
            publishState: 'publish',
            parent: 'home',
        );

        foreach (range(1, 3) as $i) {
            $caseTitle = fake()->sentence(3);
            $content = collect(range(1, 3))->map(fn () => '<section class="research"><h3>User Research</h3><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. <b>Nulla nec purus feugiat</b>, molestie ipsum et, consectetur libero. Donec nec est)</p></section>')->implode('');
            $items[] = new ImportDataEntities\Content(
                slug: "case-$i",
                title: ['en' => $caseTitle, 'fr' => $caseTitle],
                documentType: 'case-study',
                properties: [
                    'page_banner' => [
                        'title' => [
                            'en' => fake()->sentence(3),
                            'fr' => fake()->sentence(3),
                        ],
                        'image' => Arr::first($this->getRandomMediaAssetKeys(1, 'png')),
                    ],
                    'case_content' => [
                        'category' => 'Product Design',
                        'overview' => [
                            'en' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. <b>Nulla nec purus feugiat</b>, molestie ipsum et, consectetur libero. Donec nec est)</p>',
                            'fr' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. <b>Nulla nec purus feugiat</b>, molestie ipsum et, consectetur libero. Donec nec est)</p>',
                        ],
                        'year' => fake()->year(),
                        'platforms' => ['Web', 'Mobile'],
                        'roles' => ['UI/UX Designer', 'Frontend Developer'],
                        'deliverables' => 'https://example.com',
                        'content' => [
                            'en' => $content,
                            'fr' => $content,
                        ],
                    ],
                ],
                publishState: 'publish',
                parent: 'home/case-studies',
                sitemap: ['enable' => false],
            );
        }

        foreach ($items as $item) {
            $this->importDataService->addContent($item->slug, $item->parent, $item);
        }
    }

    protected function addSampleNavigation(): void
    {
        $mainNav = [
            [
                'title' => ['en' => 'Home', 'fr' => 'Accueil'],
                'contentSlugPath' => 'home',
                'type' => 'content',
            ],
            [
                'title' => ['en' => 'About', 'fr' => 'À propos'],
                'contentSlugPath' => 'home/about',
                'type' => 'content',
            ],
            [
                'title' => ['en' => 'Works', 'fr' => 'Travaux'],
                'contentSlugPath' => 'home/case-studies',
                'type' => 'content',
            ],
            [
                'title' => ['en' => 'Blog', 'fr' => 'Blog'],
                'contentSlugPath' => 'home/blog',
                'type' => 'content',
            ],
            [
                'title' => ['en' => 'Contact', 'fr' => 'Contact'],
                'contentSlugPath' => 'home/contact-us',
                'type' => 'content',
            ],
        ];
        $footerNav = [
            [
                'title' => ['en' => 'Facebook', 'fr' => 'Facebook'],
                'url' => 'https://facebook.com',
                'type' => 'link',
            ],
            [
                'title' => ['en' => 'Twitter', 'fr' => 'Twitter'],
                'url' => 'https://twitter.com',
                'type' => 'link',
            ],
            [
                'title' => ['en' => 'Instagram', 'fr' => 'Instagram'],
                'url' => 'https://instagram.com',
                'type' => 'link',
            ],
            [
                'title' => ['en' => 'LinkedIn', 'fr' => 'LinkedIn'],
                'url' => 'https://linkedin.com',
                'type' => 'link',
            ],
        ];
        $tempId = 0;
        foreach ($mainNav as $data) {
            $this->importDataService->addNavigation(ImportDataEntities\Navigation::fromArray(array_merge($data, [
                'category' => 'main',
                'id' => $tempId++,
            ])));
        }
        foreach ($footerNav as $data) {
            $this->importDataService->addNavigation(ImportDataEntities\Navigation::fromArray(array_merge($data, [
                'category' => 'footer',
                'id' => $tempId++,
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

        foreach (range(1, 3) as $i) {

            try {

                $dir = storage_path('app/temp');

                if (! is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }

                //Retry x-times to create fake image
                $fakeImage = false;
                $retry = 0;

                $fakeImageWord = "image-{$i}";
                while ($fakeImage === false && $retry < $totalRetry) {
                    $fakeImage = fake()->image(dir: $dir, width: 400, height: 400, word: $fakeImageWord, format: 'png');
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

        /** @var MediaAsset */
        $mediaAsset = $model::create([
            'title' => 'dummy.pdf',
            'is_folder' => false,
        ]);

        $mediaAsset->addMedia(\Illuminate\Http\UploadedFile::fake()->create(name: 'dummy.pdf', mimeType: 'application/pdf'))->toMediaCollection();
        $this->mediaAssets[] = $mediaAsset;
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
            'fr' => [
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

    /**
     * @return (MediaAsset&Model)[]
     */
    protected function getRandomMediaAsset(int $total, $extension = null): array
    {
        $items = collect($this->mediaAssets)
            ->when(
                $extension,
                fn (Collection $collection) => $collection
                    ->where(fn (MediaAsset $asset) => Str::after($asset->title, '.') === $extension)
            )->values()->all();
        $randIndexes = array_rand($items, $total);

        return array_map(fn ($index) => $items[$index], (array) $randIndexes);

    }

    protected function getRandomMediaAssetKeys(int $total, $extension = null): array
    {
        return collect($this->getRandomMediaAsset($total, $extension))
            ->map(fn (MediaAsset $asset) => $asset->getKey())
            ->all();
    }
}
