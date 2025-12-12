<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SolutionForest\InspireCms\InspireCmsConfig;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = InspireCmsConfig::getDocumentTypeTableName();

        Schema::table($tableName, function (Blueprint $table) {
            $table->json('api_settings')->nullable()->after('category');
        });
    }

    public function down(): void
    {
        $tableName = InspireCmsConfig::getDocumentTypeTableName();

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn('api_settings');
        });
    }
};
