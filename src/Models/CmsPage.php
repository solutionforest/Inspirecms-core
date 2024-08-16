<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class CmsPage extends Model
{
    use Concerns\BelongToCmsComponentTree;
    use Concerns\HasComponentVersions;
    use Concerns\NestableTrait;

    protected $guarded = ['id'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(InspireCmsConfig::getPageTableName());
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(InspireCmsConfig::getDocumentTypeModelClass(), 'document_type_id');
    }

    /**
     * Generate a full slug base on parent.
     *
     * @return string
     */
    public function generateFullSlug()
    {
        // todo
    }

    protected function getParentId()
    {
        return $this->{$this->getNestableParentIdColumn()} ?? $this->fallbackParentId();
    }

    protected function getNestableParentIdColumn()
    {
        return 'parent_page_id';
    }
}
