<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Estrutura académica (unidades orgânicas, graus, cursos e plano curricular)
 * e componentes do website (menus, avisos, parceiros, galeria).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organic_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('organic_units')->restrictOnDelete();
            // faculdade | instituto | escola | departamento | centro | servico | orgao
            $table->string('type', 30)->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_name')->nullable();
            $table->string('summary')->nullable();
            $table->longText('description')->nullable();
            $table->string('head_title')->nullable();
            $table->string('head_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 60)->nullable();
            $table->string('location')->nullable();
            $table->foreignId('featured_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('page_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_demo')->default(false)->index();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'published_at']);
        });

        Schema::create('degrees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('plural_name')->nullable();
            $table->string('slug')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organic_unit_id')->nullable()->constrained('organic_units')->nullOnDelete();
            $table->foreignId('degree_id')->constrained('degrees')->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon', 60)->nullable();
            $table->text('summary')->nullable();
            $table->string('duration', 60)->nullable();
            $table->string('modality', 120)->nullable();
            $table->string('regime', 120)->nullable();
            $table->longText('presentation')->nullable();
            $table->longText('objectives')->nullable();
            $table->text('entry_profile')->nullable();
            $table->text('exit_profile')->nullable();
            $table->json('careers')->nullable();
            $table->text('curriculum_note')->nullable();
            $table->string('coordinator_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->foreignId('featured_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_demo')->default(false)->index();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'published_at']);
            $table->index('name');
        });

        Schema::create('course_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->unsignedTinyInteger('year');
            $table->unsignedTinyInteger('semester')->nullable();
            $table->string('name');
            $table->unsignedSmallInteger('hours')->nullable();
            $table->decimal('credits', 5, 1)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->index(['course_id', 'year', 'sort_order']);
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('location', 60)->unique();
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();
            $table->string('label');
            $table->string('mobile_label')->nullable();
            // link | dropdown | mega | column | divider
            $table->string('display', 20)->default('link');
            // none | page | url
            $table->string('link_type', 20)->default('none');
            $table->foreignId('page_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->string('url', 2048)->nullable();
            $table->boolean('open_in_new_tab')->default(false);
            $table->string('icon', 60)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['menu_id', 'parent_id', 'sort_order']);
        });

        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('text');
            $table->string('icon', 60)->nullable();
            $table->string('url', 2048)->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_demo')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('logo_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('url', 2048)->nullable();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_demo')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->string('type', 10)->default('image'); // image | video
            $table->string('title');
            $table->string('caption')->nullable();
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('video_url', 2048)->nullable();
            $table->string('duration_label', 40)->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_demo')->default(false);
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['type', 'status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_items');
        Schema::dropIfExists('partners');
        Schema::dropIfExists('notices');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('course_subjects');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('degrees');
        Schema::dropIfExists('organic_units');
    }
};
