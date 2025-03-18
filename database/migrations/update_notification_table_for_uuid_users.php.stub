<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('notifications')) {
            return;
        }
        $columnToCheck = collect(Schema::getColumns('notifications'))->firstWhere('name', 'notifiable_id');
        if ($columnToCheck['type'] ?? '' == 'integer') {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropMorphs('notifiable');
            });
            Schema::table('notifications', function (Blueprint $table) {
                $table->after('type', function (Blueprint $table) {
                    $table->uuidMorphs('notifiable');
                });
            });
        }
    }
};
