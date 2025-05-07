<?php

namespace SolutionForest\InspireCms\ImportData\Entities;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\Language as ContractsLanguage;

/**
 * @extends BaseEntity<Language>
 */
class Language extends BaseEntity
{
    protected static array $rules = [
        'code' => 'required|string',
        'isDefault' => 'nullable|boolean',
    ];

    protected static array $propertiesOrder = [
        'code',
        'isDefault',
    ];

    protected static array $limitedProperties = [
        'code',
        'isDefault',
    ];

    public function __construct(
        /**
         * The code of the language.
         *
         * @var string
         */
        public $code,
        /**
         * Whether the language is the default one (optional).
         *
         * @var bool|null
         */
        public $isDefault = null,
    ) {
        $this->initialize();
    }

    protected function initialize(): void
    {
        // Set the default values
        $this->isDefault ??= false;
    }

    public function getDataForModel(): array
    {
        return [
            'code' => $this->code,
            'is_default' => $this->isDefault,
        ];
    }

    /**
     * @param  ContractsLanguage|Model  $record
     */
    public static function fromRecord($record)
    {
        $data['code'] = $record->code;
        $data['isDefault'] = $record->is_default;

        return static::fromArray($data);
    }
}
