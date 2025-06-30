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
        $tableNames = $this->getTableNames();
        $spatiePermissionTableNames = config('permission.table_names');
        $cmsUserMorphType = app(InspireCmsConfig::getUserModelClass())->getMorphClass();
        
        $tmpUserTableName = $tableNames['user'] . '_tmp';
        
        $userData = collect(DB::table($tableNames['user'])->get())
            ->map(function ($user, $index) {
                $user->tmp_uuid = $user->id ?? (string) Str::uuid7();
                $user->tmp_id = $index + 1; // Assuming 'id' starts from 1 and is sequential
                return $user;
            });

        //region Update cms user table 

        // 1. Create tmp table
        Schema::create($tmpUserTableName, function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->uuid('uuid');
            $table->string('name');
            $table->string('email');
            $table->string('preferred_language');
            $table->string('avatar')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->integer('failed_login_attempt')->default(0)->unsigned();
            $table->timestamp('last_lockouted_at')->nullable();
            $table->timestamp('last_password_change_date')->nullable();
            $table->timestamp('last_logged_in_at')->nullable();
            $table->timestamp('email_confirmed_at')->nullable();
            $table->timestamps();
        });

        // 2. Fill tmp table with existing data
        foreach ($userData as $user) {
            DB::table($tmpUserTableName)->insert([
                'id' => $user->tmp_id,
                'uuid' => $user->tmp_uuid,
                'name' => $user->name,
                'email' => $user->email,
                'preferred_language' => $user->preferred_language ?? 'en',
                'avatar' => $user->avatar ?? null,
                'password' => $user->password ?? '',
                'failed_login_attempt' => $user->failed_login_attempt ?? 0,
                'last_lockouted_at' => $user->last_lockouted_at ?? null,
                'last_password_change_date' => $user->last_password_change_date ?? null,
                'last_logged_in_at' => $user->last_logged_in_at ?? null,
                'email_confirmed_at' => $user->email_confirmed_at ?? null,
                'created_at' => $user->created_at ?? now(),
                'updated_at' => $user->updated_at ?? now(),
            ]);
        }

        // 3. Drop old table
        Schema::dropIfExists($tableNames['user']);

        // 4. Rename tmp table to original name
        Schema::rename($tmpUserTableName, $tableNames['user']);

        // 5. Add back index and primary key etc
        Schema::table($tableNames['user'], function (Blueprint $table) {
            $table->primary('id');
            $table->index('uuid');
            $table->unique('email');
        });

        //endregion Update cms user table 

        //region Update cms user activities table
        
        // 1. Create tmp table
        Schema::dropIfExists($tableNames['user_login_activity']);

        // 2. Create new table with updated structure
        
        Schema::create($tableNames['user_login_activity'], function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');

            $table->timestamp('last_logged_in_at_utc')->nullable();
            $table->timestamp('last_logged_out_at_utc')->nullable();
            $table->string('ip_address');

            $table->index('user_id');
            $table->index('ip_address');
        });
        //endregiosn Update cms user activities table

        //region Update table has 'author' relationship
        $tablesHasAuthor = [
            $tableNames['content'],
            $tableNames['content_version'],
            $tableNames['media_asset'],
            $tableNames['import'],
            $tableNames['export'],
        ];
        foreach ($tablesHasAuthor as $hasAuthorTableName) {
            foreach ($userData as $user) {
                DB::table($hasAuthorTableName)
                    ->where('author_type', $cmsUserMorphType)
                    ->where('author_id', $user->tmp_uuid)
                    ->update(['author_id' => $user->tmp_id]);
            }
            Schema::table($hasAuthorTableName, function (Blueprint $table) {
                $table->unsignedBigInteger('author_id')->nullable()->change();
            });
        }
        //endregion

        //region Update 'owner' relationship
        $tablesHasOwner = [
            $tableNames['content_lock'],
        ];

        foreach ($tablesHasOwner as $hasOwnerTableName) {
            foreach ($userData as $user) {
                DB::table($hasOwnerTableName)
                    ->where('owner_type', $cmsUserMorphType)
                    ->where('owner_id', $user->tmp_uuid)
                    ->update(['owner_id' => $user->tmp_id]);
            }
            Schema::table($hasOwnerTableName, function (Blueprint $table) {
                $table->unsignedBigInteger('owner_id')->nullable()->change();
            });
        }

        //endregion


        //region Update Spatie permissions tables
        foreach ($userData as $user) {
            DB::table($spatiePermissionTableNames['model_has_roles'])
                ->where('model_type', $cmsUserMorphType)
                ->where('model_id', $user->tmp_uuid)
                ->update(['model_id' => $user->tmp_id]);
            DB::table($spatiePermissionTableNames['model_has_permissions'])
                ->where('model_type', $cmsUserMorphType)
                ->where('model_id', $user->tmp_uuid)
                ->update(['model_id' => $user->tmp_id]);
        }
        //endregion Update Spatie permissions tables
    }

    public function down(): void
    {
        $tableNames = $this->getTableNames();
        $spatiePermissionTableNames = config('permission.table_names');
        $cmsUserMorphType = app(InspireCmsConfig::getUserModelClass())->getMorphClass();
        
        $tmpUserTableName = $tableNames['user'] . '_tmp';

        $userData = collect(DB::table($tableNames['user'])->get())
            ->map(function ($user) {
                $user->tmp_uuid = $user->uuid ?? (string) Str::uuid7();
                $user->tmp_id = $user->id; 
                return $user;
            });

        //region Rollback Spatie permissions tables changes
        foreach ($userData as $user) {
            DB::table($spatiePermissionTableNames['model_has_roles'])
                ->where('model_type', $cmsUserMorphType)
                ->where('model_id', $user->tmp_id)
                ->update(['model_id' => $user->tmp_uuid]);
            DB::table($spatiePermissionTableNames['model_has_permissions'])
                ->where('model_type', $cmsUserMorphType)
                ->where('model_id', $user->tmp_id)
                ->update(['model_id' => $user->tmp_uuid]);
        }
        //endregion Rollback Spatie permissions tables changes

        //region Update table has 'author' relationship
        $tablesHasAuthor = [
            $tableNames['content'],
            $tableNames['content_version'],
            $tableNames['media_asset'],
            $tableNames['import'],
            $tableNames['export'],
        ];
        foreach ($tablesHasAuthor as $hasAuthorTableName) {
            Schema::table($hasAuthorTableName, function (Blueprint $table) {
                $table->uuid('author_id')->nullable()->change();
            });
            foreach ($userData as $user) {
                DB::table($hasAuthorTableName)
                    ->where('author_type', $cmsUserMorphType)
                    ->where('author_id', $user->tmp_id)
                    ->update(['author_id' => $user->tmp_uuid]);
            }
        }
        //endregion

        //region Update 'owner' relationship
        $tablesHasOwner = [
            $tableNames['content_lock'],
        ];
        foreach ($tablesHasOwner as $hasOwnerTableName) {
            Schema::table($hasOwnerTableName, function (Blueprint $table) {
                $table->uuid('owner_id')->nullable()->change();
            });
            foreach ($userData as $user) {
                DB::table($hasOwnerTableName)
                    ->where('owner_type', $cmsUserMorphType)
                    ->where('owner_id', $user->tmp_id)
                    ->update(['owner_id' => $user->tmp_uuid]);
            }
        }
        
        //endregion


        //region Rollback 'cms_user_login_activities' changes

        // 1. Drop table
        Schema::dropIfExists($tableNames['user_login_activity']);

        // 2. Create table
        Schema::create($tableNames['user_login_activity'], function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');

            $table->timestamp('last_logged_in_at_utc')->nullable();
            $table->timestamp('last_logged_out_at_utc')->nullable();
            $table->string('ip_address');

            $table->index('user_id');
            $table->index('ip_address');
        });


        //endregion Rollback 'cms_user_login_activities' changes


        //region Rollback 'cms_users' changes
        
        // 1. Create tmp table
        Schema::create($tmpUserTableName, function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('email');
            $table->string('preferred_language');
            $table->string('avatar')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->integer('failed_login_attempt')->default(0)->unsigned();
            $table->timestamp('last_lockouted_at')->nullable();
            $table->timestamp('last_password_change_date')->nullable();
            $table->timestamp('last_logged_in_at')->nullable();
            $table->timestamp('email_confirmed_at')->nullable();
            $table->timestamps();
        });


        // 2. Fill tmp table with existing data
        foreach ($userData as $user) {
            DB::table($tmpUserTableName)->insert([
                'id' => $user->tmp_uuid,
                'name' => $user->name,
                'email' => $user->email,
                'preferred_language' => $user->preferred_language ?? 'en',
                'avatar' => $user->avatar ?? null,
                'password' => $user->password ?? '',
                'failed_login_attempt' => $user->failed_login_attempt ?? 0,
                'last_lockouted_at' => $user->last_lockouted_at ?? null,
                'last_password_change_date' => $user->last_password_change_date ?? null,
                'last_logged_in_at' => $user->last_logged_in_at ?? null,
                'email_confirmed_at' => $user->email_confirmed_at ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Drop old table
        Schema::dropIfExists($tableNames['user']);

        // 4. Rename tmp table to original name
        Schema::rename($tmpUserTableName, $tableNames['user']);

        // 5. Add back index and primary key etc
        Schema::table($tableNames['user'], function (Blueprint $table) {
            $table->primary('id');
            $table->unique('email');
        });

        //endregion Rollback 'cms_users' changes

    }

    private function getTableNames()
    {
        return [
            'field_groupable' => $this->prefix . 'field_groupables',
            'content_version' => $this->prefix . 'content_versions',
            'content_publish_version' => $this->prefix . 'content_publish_version',
            'content_path' => $this->prefix . 'content_paths',
            'content_route' => $this->prefix . 'content_routes',
            'content' => $this->prefix . 'content',
            'content_lock' => $this->prefix . 'content_locks',
            'document_type' => $this->prefix . 'document_types',
            'document_type_inheritance' => $this->prefix . 'document_type_inheritance',
            'allowed_document_type' => $this->prefix . 'document_type_allowed_document_type',
            'language' => $this->prefix . 'languages',
            'user_login_activity' => $this->prefix . 'user_login_activities',
            'user' => $this->prefix . 'users',
            'template' => $this->prefix . 'templates',
            'templatable' => $this->prefix . 'templateable',
            'content_web_setting' => $this->prefix . 'content_web_settings',
            'sitemap' => $this->prefix . 'sitemaps',
            'navigation' => $this->prefix . 'navigation',
            'key_value' => $this->prefix . 'key_values',
            'media_asset' => $this->prefix . 'media_assets',
            'import' => $this->prefix . 'imports',
            'export' => $this->prefix . 'exports',
        ];
    }
};
