<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        // ATRIBUTI
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->boolean('status')->default(true)->index();
            $table->boolean('is_filterable')->default(true)->index(); // npr. filter u listingu
            $table->boolean('is_visible')->default(true)->index();    // prikaz na product page
            $table->boolean('is_composite')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_attribute_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('product_attributes')->cascadeOnDelete();
            $table->string('locale', 5)->index();       // 'hr','en',...
            $table->string('title');                    // npr. 'Materijal'
            $table->string('slug')->nullable();
            $table->unique(['attribute_id', 'locale']);
            $table->unique(['slug', 'locale']);
        });

        // VRIJEDNOSTI ATRIBUTA (enumeracije)
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('product_attributes')->cascadeOnDelete();
            $table->boolean('status')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_attribute_value_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('value_id')->constrained('product_attribute_values')->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('title');                    // npr. 'Pamuk' / 'Cotton'
            $table->unique(['value_id', 'locale']);
        });

        // PIVOT: proizvod ↔ vrijednost atributa
        Schema::create('product_attribute_value_product', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained('product_attribute_values')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->decimal('share_percent', 5, 2)->nullable(); // 0-100.00
            $table->decimal('amount', 15, 4)->nullable();    // npr. 0.2500
            $table->string('unit', 16)->nullable();                 // npr. %, g, ml, cm
            $table->string('note', 255)->nullable();

            // UX/flags + prostor za budućnost
            $table->boolean('is_primary')->default(false);
            $table->json('extra')->nullable();

            // primarni ključ – sprječava duplikate i služi kao (leftmost) index za product_id
            $table->primary(['product_id', 'attribute_value_id'], 'pavp_pk');

            // kratki indexi (izbjegavamo preduga imena)
            // (opcionalno) reverzni lookup po value → proizvodi
            $table->index('sort_order', 'pavp_sort_idx');
            $table->index('share_percent', 'pavp_pct_idx');
            $table->index('attribute_value_id', 'pavp_val_idx'); // reverse lookup
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('product_attribute_value_product');
        Schema::dropIfExists('product_attribute_value_translations');
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('product_attribute_translations');
        Schema::dropIfExists('product_attributes');
    }
};
