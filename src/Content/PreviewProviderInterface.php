<?php

namespace SolutionForest\InspireCms\Content;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Models\Contracts\DocumentType;
use SolutionForest\InspireCms\Models\Contracts\Template;

interface PreviewProviderInterface
{
    /**
     * @param  int|Model|DocumentType|null  $documentType
     * @param  string|int|Model|Content|array|null  $content
     * @param  int|Model|Template|null  $template
     * @param  ?string  $locale
     * @param  array  $propertyData
     * @param  array  $data
     */
    public function renderContentPreview($documentType, $content, $template, $locale = null, $propertyData = [], $data = []);

    /**
     * @param  string|null  $template
     * @param  int|Model|DocumentType|null  $documentType
     * @param  string|null  $theme
     * @param  ?string  $locale
     * @param  array  $data
     */
    public function renderTemplatePreview($templateContent, $documentType, $theme = null, $locale = null, $data = []);
}
