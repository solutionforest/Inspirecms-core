<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Support\Base\BaseMigration;

return new class extends BaseMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableNames = $this->getCmsTableNames();
        Schema::table($tableNames['user'], function (Blueprint $table) {
            $table->bigIncrements('id')->change();
        });
    }
};
