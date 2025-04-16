<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SolutionForest\InspireCms\Support\Base\BaseMigration;

return new class extends BaseMigration
{
    public function up()
    {
        $tableNames = $this->getTableNames();

        Schema::create($tableNames['content'], function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->integer('document_type_id');
            $table->uuid('parent_id')->comment('Parent\'s content.');
            $table->json('title');
            $table->string('slug');
            $table->integer('status')->unsigned()->default(0); // Enum: 0 = Pending
            $table->boolean('is_default')->default(false)->comment('Indicates if this content is the default.');

            $table->author(userType: 'uuid', nullable: true);

            $table->timestamps();
            $table->softDeletes();

            $table->index('document_type_id');
            $table->index('status');
            $table->unique(['slug', 'parent_id']);
        });
        Schema::create($tableNames['content_path'], function (Blueprint $table) use ($tableNames) {
            $table->id();
            $table->foreignUuid('key')->references('id')->on($tableNames['content'])->cascadeOnDelete();
            $table->string('value');

            $table->index('value');
        });
        Schema::create($tableNames['content_route'], function (Blueprint $table) use ($tableNames) {
            $table->id();
            $table->foreignUuid('content_id')->references('id')->on($tableNames['content'])->cascadeOnDelete();
            $table->unsignedBigInteger('language_id')->nullable();
            $table->string('uri');

            $table->boolean('is_default_pattern')->default(true);
            $table->json('regex_constraints')->nullable();

            $table->index('uri');
            $table->index('language_id');
        });
        Schema::create($tableNames['content_lock'], function (Blueprint $table) {
            $table->uuid('content_id')->primary();
            $table->uuidMorphs('owner');
            $table->timestamp('locked_at')->nullable();
        });
        Schema::create($tableNames['document_type'], function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->string('category')->default('web');
            $table->string('icon')->nullable();
            $table->boolean('show_as_table')->default(false);

            $table->boolean('show_at_root')->default(true);

            $table->timestamps();

            $table->unique('slug');
            $table->index('category');
        });
        Schema::create($tableNames['template'], function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->json('content')->nullable();

            $table->timestamps();

            $table->unique('slug');
        });
        Schema::create($tableNames['templatable'], function (Blueprint $table) {
            $table->uuidMorphs('templateable');
            $table->unsignedBigInteger('template_id');
            $table->boolean('is_default')->default(false);

            $table->index('template_id');
        });
        Schema::create($tableNames['content_version'], function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at');

            $table->string('event_name')->nullable();
            $table->string('publish_state')->nullable();

            $table->uuid('content_id');
            $table->json('from_data');
            $table->json('to_data');
            $table->boolean('avoid_to_clean')->default(false);

            $table->author(userType: 'uuid', nullable: true);

            $table->index('content_id');
            $table->index('avoid_to_clean');
        });
        Schema::create($tableNames['content_publish_version'], function (Blueprint $table) {
            $table->uuid('content_id');
            $table->integer('version_id');
            $table->timestamp('published_at');

            $table->index('content_id');
            $table->index('version_id');
        });
        Schema::create($tableNames['content_web_setting'], function (Blueprint $table) {
            $table->id();
            $table->uuid('content_id');
            $table->json('seo')->nullable();
            $table->json('robots')->nullable();
            $table->string('redirect_path')->nullable();
            $table->uuid('redirect_content_id')->default(0);
            $table->integer('redirect_type')->unsigned()->nullable();

            $table->index('content_id');
            $table->index('redirect_content_id');
        });
        Schema::create($tableNames['sitemap'], function (Blueprint $table) {
            $table->id();
            $table->nullableUuidMorphs('model');
            $table->string('url')->nullable();
            $table->string('change_frequency')->default('monthly');
            $table->decimal('priority',2 ,1)->unsigned();
            $table->boolean('enable');
            $table->timestamps();
        });
        Schema::create($tableNames['field_groupable'], function (Blueprint $table) {
            $table->id();
            $table->integer('field_group_id');
            $table->morphs('groupabled');
            $table->integer('order')->default(0);
            $table->nullableMorphs('inherited_from');

            $table->index('field_group_id');
        });

        $documentTypeTable = $tableNames['document_type'];
        Schema::create($tableNames['document_type_inheritance'], function (Blueprint $table) use ($documentTypeTable) {
            $table->foreignId('document_type_id')->constrained($documentTypeTable, 'id')->onDelete('cascade');
            $table->foreignId('inherited_document_type_id')->constrained($documentTypeTable, 'id')->onDelete('cascade');
        });
        
        Schema::create($tableNames['allowed_document_type'], function (Blueprint $table) use ($documentTypeTable) {
            $table->foreignId('id')->constrained($documentTypeTable, 'id')->onDelete('cascade');
            $table->foreignId('allowed_id')->constrained($documentTypeTable, 'id')->onDelete('cascade');
        });
        
        Schema::create($tableNames['user'], function (Blueprint $table) {
            $table->uuid('id')->primary();
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

            $table->unique('email');
        });
        Schema::create($tableNames['user_login_activity'], function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');

            $table->timestamp('last_logged_in_at_utc')->nullable();
            $table->timestamp('last_logged_out_at_utc')->nullable();
            $table->string('ip_address');

            $table->index('user_id');
            $table->index('ip_address');
        });
        Schema::create($tableNames['language'], function (Blueprint $table) {
            $table->id();
            $table->string('code', 50);
            $table->boolean('is_default')->default(false);

            $table->timestamps();

            $table->unique('code');
            $table->index('is_default');
        });
        Schema::create($tableNames['navigation'], function (Blueprint $table) {
            $table->id();

            $table->uuid('content_id')->default('');
            $table->json('url')->nullable();
            $table->string('target')->nullable();

            $table->json('title');

            $table->string('type')->default('link');
            $table->string('category')->default('main');

            $table->boolean('is_active')->default(true);

            $table->nestedSet(); // This method adds left, right, depth columns.

            $table->timestamps();

            $table->index('content_id');
            $table->index('type');
            $table->index('category');
        });

        Schema::create($tableNames['key_value'], function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        $tableNames = $this->getTableNames();

        Schema::dropIfExists($tableNames['key_value']);

        Schema::dropIfExists($tableNames['navigation']);
        Schema::dropIfExists($tableNames['language']);
        Schema::dropIfExists($tableNames['user_login_activity']);
        Schema::dropIfExists($tableNames['user']);

        Schema::dropIfExists($tableNames['allowed_document_type']);
        Schema::dropIfExists($tableNames['document_type_inheritance']);
        Schema::dropIfExists($tableNames['field_groupable']);

        Schema::dropIfExists($tableNames['sitemap']);
        Schema::dropIfExists($tableNames['content_web_setting']);
        Schema::dropIfExists($tableNames['content_publish_version']);
        Schema::dropIfExists($tableNames['content_version']);

        Schema::dropIfExists($tableNames['templatable']);
        Schema::dropIfExists($tableNames['template']);

        Schema::dropIfExists($tableNames['document_type']);
        Schema::dropIfExists($tableNames['content_lock']);
        Schema::dropIfExists($tableNames['content_route']);
        Schema::dropIfExists($tableNames['content_path']);
        Schema::dropIfExists($tableNames['content']);
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
        ];
    }
};
