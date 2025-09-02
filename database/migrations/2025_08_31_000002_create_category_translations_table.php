<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnDelete();
            
            $table->string('locale', 5)->index(); // hr, en, etc.
            $table->string('title');
            $table->string('slug')->nullable();
            $table->string('link_url')->nullable();
            
            $table->mediumText('description')->nullable();
            
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->json('seo_json')->nullable();
            
            $table->unique(['category_id', 'locale']);
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('category_translations');
    }
};
