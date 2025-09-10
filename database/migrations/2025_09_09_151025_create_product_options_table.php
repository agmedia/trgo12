<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // OPTIONS (e.g., color, size)
        Schema::create('product_options', function (Blueprint $table) {
            $table->id();
            $table->boolean('status')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_option_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_id')->constrained('product_options')->cascadeOnDelete();
            $table->string('locale', 5)->index();               // 'hr', 'en', ...
            $table->string('title');                          // e.g., 'Color' / 'Boja'
            $table->string('slug')->nullable();               // optional
            $table->unique(['option_id','locale']);
            $table->unique(['slug','locale']);
        });

        // OPTION VALUES (e.g., red, blue)
        Schema::create('product_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_id')->constrained('product_options')->cascadeOnDelete();
            $table->boolean('status')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_option_value_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('value_id')->constrained('product_option_values')->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('title');                          // e.g., 'Red' / 'Crvena'
            $table->unique(['value_id','locale']);
        });

        // PIVOT: attach values to products (option is implied via value->option)
        Schema::create('product_option_value_product', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('option_value_id')->constrained('product_option_values')->cascadeOnDelete();

            // Link na sliku artikla (odabireš neku već uploaddanu iz product_images)
            $table->foreignId('product_image_id')->nullable()
                  ->constrained('product_images')->nullOnDelete();

            // SKU varijante
            $table->string('sku_full', 128)->nullable()->unique(); // globalno jedinstven ako koristiš full SKU
            $table->string('sku_suffix', 32)->nullable();          // npr. "-RED"

            // Stanje i cijena
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('price_delta', 15, 2)->default(0);     // +10.00 ili -5.00
            $table->decimal('price_override', 15, 2)->nullable();  // ako postavljeno, override-a baznu cijenu

            // UX flags
            $table->boolean('is_default')->default(false);

            $table->timestamps();

            // Buduće širenje bez migracija
            $table->json('extra')->nullable();

            // Osnovni ključ za sprječavanje duplikata po proizvodu/vrijednosti
            $table->primary(['product_id', 'option_value_id']);

            // Indeksi koje ćeš realno koristiti
            $table->index(['product_id', 'option_value_id']);
            $table->index('product_image_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_option_value_product');
        Schema::dropIfExists('product_option_value_translations');
        Schema::dropIfExists('product_option_values');
        Schema::dropIfExists('product_option_translations');
        Schema::dropIfExists('product_options');
    }
};
