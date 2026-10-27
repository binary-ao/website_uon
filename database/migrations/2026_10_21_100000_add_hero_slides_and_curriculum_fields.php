<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * - hero_slides: diapositivos do banner (carrossel) da página inicial.
 * - course_subjects: código e indicação de unidade curricular optativa.
 * - organic_units: mapa incorporado da unidade.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow', 120)->nullable();
            $table->string('title', 200);
            $table->string('title_highlight', 120)->nullable();
            $table->text('text')->nullable();
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('image_position', 20)->default('center');
            $table->string('primary_label', 60)->nullable();
            $table->string('primary_url', 2048)->nullable();
            $table->string('secondary_label', 60)->nullable();
            $table->string('secondary_url', 2048)->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_demo')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['is_active', 'sort_order']);
        });

        Schema::table('course_subjects', function (Blueprint $table) {
            $table->string('code', 30)->nullable()->after('semester');
            $table->boolean('is_optional')->default(false)->after('credits');
        });

        Schema::table('organic_units', function (Blueprint $table) {
            $table->string('map_embed_url', 2048)->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('organic_units', fn (Blueprint $t) => $t->dropColumn('map_embed_url'));
        Schema::table('course_subjects', fn (Blueprint $t) => $t->dropColumn(['code', 'is_optional']));
        Schema::dropIfExists('hero_slides');
    }
};
