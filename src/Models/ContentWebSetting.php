<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SolutionForest\InspireCms\Dtos\SeoDto;
use SolutionForest\InspireCms\Helpers\SeoHelper;
use SolutionForest\InspireCms\Models\Contracts\ContentWebSetting as ContentWebSettingContract;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class ContentWebSetting extends BaseModel implements ContentWebSettingContract
{
    protected $guarded = ['id'];

    protected $casts = [
        'seo' => 'json',
        'robots' => 'json',
    ];

    public function redirectContent(): BelongsTo
    {
        return $this->belongsTo(InspireCmsConfig::getContentModelClass(), 'redirect_content_id');
    }

    //region Dto
    public function toDto(...$args)
    {
        $dtoClass = static::getDtoClass();

        $locale = $args[0] ?? null;

        $dtoParameters = [
            ...($this->seo ?? []),
            ...($this->robots ?? []),
        ];

        foreach ($dtoParameters as $key => $value) {

            if (in_array($key, SeoHelper::getTranslatableAttributes()) && is_array($value)) {
                if (filled($locale)) {
                    $value = data_get($value, $locale, null);
                } else {
                    $value = reset($value);
                }
            }

            $dtoParameters[$key] = $value;
        }

        return $dtoClass::fromArray($dtoParameters);
    }

    public static function getDtoClass(): string
    {
        return SeoDto::class;
    }
    //endregion Dto
}
