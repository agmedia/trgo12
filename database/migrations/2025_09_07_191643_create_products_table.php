<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // PRODUCTS
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('manufacturer_id')->nullable()
                  ->constrained('manufacturers')->nullOnDelete();

            // Identifiers
            $table->string('sku', 64)->unique();   // unique = indexed
            $table->string('ean', 14)->nullable(); // no index
            $table->string('isbn', 14)->nullable();// no index

            // Pricing & stock
            $table->decimal('price', 15, 2)->default(0);
            $table->unsignedInteger('quantity')->default(0);
            $table->boolean('track_stock')->default(true);
            $table->boolean('decrease_on_purchase')->default(true);

            // Tax (optional)
            $table->integer('tax_id')->default(1);

            // Flags & meta
            $table->unsignedInteger('viewed')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('featured')->default(false)->index();
            $table->boolean('status')->default(true)->index();
            $table->timestamp('published_at')->nullable()->index();

            $table->softDeletes();
            $table->timestamps();
        });

        // TRANSLATIONS (one row per product+lang; no timestamps)
        Schema::create('product_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            $table->string('locale', 5)->index();   // e.g. 'hr', 'en'
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();

            // optional SEO
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();

            $table->unique(['product_id','locale']);
            $table->unique(['slug','locale']);
        });

        // IMAGES
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            $table->string('path'); // file path / media identifier
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_published')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });

        // IMAGE TRANSLATIONS
        Schema::create('product_image_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('image_id')->constrained('product_images')->cascadeOnDelete();

            $table->string('locale', 5)->index();
            $table->string('title')->nullable();
            $table->string('alt_text')->nullable();

            $table->unique(['image_id','locale']);
        });

        // PRODUCT <-> CATEGORY
        Schema::create('product_category', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->primary(['product_id','category_id']); // no dupes
        });

        // PRODUCT <-> PRODUCT (related)
        Schema::create('product_related', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('related_id')->constrained('products')->cascadeOnDelete();
            $table->primary(['product_id','related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_related');
        Schema::dropIfExists('product_category');
        Schema::dropIfExists('product_image_translations');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('product_translations');
        Schema::dropIfExists('products');
    }
};
