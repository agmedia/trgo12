<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MANUFACTURERS
        Schema::create('manufacturers', function (Blueprint $table) {
            $table->id();

            // Core
            $table->boolean('status')->default(true)->index();
            $table->boolean('featured')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);

            // Business / metadata
            $table->string('website_url')->nullable();
            $table->string('support_email')->nullable();
            $table->string('phone')->nullable();
            $table->string('country_code', 2)->nullable(); // ISO2
            $table->unsignedSmallInteger('established_year')->nullable();

            // Media
            $table->string('logo_path')->nullable(); // e.g. media/brands/acme.png

            // Publish controls
            $table->timestamp('published_at')->nullable()->index();

            $table->softDeletes();
            $table->timestamps();
        });

        // MANUFACTURER TRANSLATIONS
        Schema::create('manufacturer_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manufacturer_id')->constrained('manufacturers')->cascadeOnDelete();

            $table->string('locale', 5)->index();   // 'hr', 'en', ...
            $table->string('title');              // translated name
            $table->string('slug');               // per-locale slug
            $table->text('description')->nullable();

            // optional SEO
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();

            $table->unique(['manufacturer_id', 'locale']);
            $table->unique(['slug', 'locale']);
        });

        // MANUFACTURER <-> CATEGORY (optional but handy for faceting pages)
        Schema::create('manufacturer_category', function (Blueprint $table) {
            $table->foreignId('manufacturer_id')->constrained('manufacturers')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->primary(['manufacturer_id', 'category_id']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('manufacturer_category');
        Schema::dropIfExists('manufacturer_translations');
        Schema::dropIfExists('manufacturers');
    }
};
