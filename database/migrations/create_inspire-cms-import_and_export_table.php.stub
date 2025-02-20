<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SolutionForest\InspireCms\Support\Base\BaseMigration;

return new class extends BaseMigration
{
    public function up()
    {
        $tableNames = $this->getTableNames();

        Schema::create($tableNames['import'], function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('file_disk');
            $table->string('file_name');

            $table->longText('payload')->nullable();

            $table->timestamp('available_at')->index();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('finished_at')->nullable()->index();
            $table->timestamp('failed_at')->nullable();

            $table->author(userType: 'uuid', nullable: true);
        });

        Schema::create($tableNames['export'], function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('file_disk');
            $table->string('file_name')->nullable();
            $table->string('exporter')->index();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('finished_at')->nullable()->index();
            $table->timestamp('failed_at')->nullable();
            
            $table->longText('payload')->nullable();

            $table->author(userType: 'uuid', nullable: true);
        });
    }

    public function down()
    {
        $tableNames = $this->getTableNames();


        Schema::dropIfExists($tableNames['export']);
        Schema::dropIfExists($tableNames['import']);
    }

    private function getTableNames()
    {
        return [
            'import' => $this->prefix . 'imports',
            'export' => $this->prefix . 'exports',
        ];
    }
};
