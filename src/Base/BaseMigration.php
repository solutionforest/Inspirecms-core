<?php

namespace SolutionForest\InspireCms\Base;

use Illuminate\Database\Migrations\Migration;

abstract class BaseMigration extends Migration
{
    protected ?string $prefix = null;
    
    public function __construct()
    {
        $this->prefix = config('inspirecms.models.table_name_prefix');
    }
}
