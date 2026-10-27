<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Conteúdos editoriais: páginas, notícias/comunicados, eventos e documentos.
 * Todos partilham o mesmo modelo de publicação:
 *   status: draft | pending | scheduled | published | archived
 *   published_at: data a partir da qual o conteúdo é visível publicamente.
 */
return new class extends Migration
{
    private function publication(Blueprint $table): void
    {
        $table->string('status', 20)->default('draft');
        $table->timestamp('published_at')->nullable();
        $table->index(['status', 'published_at']);
    }

    private function authorship(Blueprint $table): void
    {
        $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
    }

    private function seo(Blueprint $table): void
    {
        $table->string('meta_title')->nullable();
        $table->string('meta_description', 320)->nullable();
    }

    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('pages')->restrictOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('path')->unique();
            $table->string('layout', 40)->default('standard');
            $table->json('module_options')->nullable();
            $table->string('eyebrow')->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->json('blocks')->nullable();
            $table->string('sidebar_title')->nullable();
            $table->text('sidebar_items')->nullable();
            $table->foreignId('featured_media_id')->nullable()->constrained('media')->nullOnDelete();
            $this->seo($table);
            $this->publication($table);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_system')->default(false);
            $table->boolean('show_in_search')->default(true);
            $this->authorship($table);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['parent_id', 'slug']);
            $table->index(['parent_id', 'sort_order']);
        });

        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->default('news'); // news | announcement
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->foreignId('featured_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('author_name')->nullable();
            $table->boolean('is_featured')->default(false);
            $this->seo($table);
            $this->publication($table);
            $table->boolean('is_demo')->default(false)->index();
            $this->authorship($table);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['type', 'status', 'published_at']);
            $table->index('title');
        });

        Schema::create('news_tag', function (Blueprint $table) {
            $table->foreignId('news_id')->constrained('news')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->primary(['news_id', 'tag_id']);
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->string('location')->nullable();
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at')->nullable();
            $table->boolean('all_day')->default(false);
            $table->foreignId('featured_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('registration_url', 2048)->nullable();
            $table->string('registration_info')->nullable();
            $this->seo($table);
            $this->publication($table);
            $table->boolean('is_demo')->default(false)->index();
            $this->authorship($table);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->date('document_date')->nullable();
            $table->string('visibility', 20)->default('public'); // public | restricted
            $table->unsignedBigInteger('current_version_id')->nullable();
            $this->publication($table);
            $table->boolean('is_demo')->default(false)->index();
            $this->authorship($table);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['visibility', 'status']);
            $table->index('title');
        });

        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('disk', 32)->default('documents');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 120);
            $table->string('extension', 12);
            $table->unsignedBigInteger('size');
            $table->string('checksum', 64);
            $table->string('notes')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();
            $table->unique(['document_id', 'version']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreign('current_version_id')->references('id')->on('document_versions')->nullOnDelete();
        });

        // Associação de documentos a páginas e notícias (polimórfica).
        Schema::create('documentables', function (Blueprint $table) {
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->morphs('documentable');
            $table->unsignedInteger('sort_order')->default(0);
            $table->primary(['document_id', 'documentable_type', 'documentable_id'], 'documentables_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentables');
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['current_version_id']);
        });
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('events');
        Schema::dropIfExists('news_tag');
        Schema::dropIfExists('news');
        Schema::dropIfExists('pages');
    }
};
