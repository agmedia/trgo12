<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NestedSet;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('group')->index(); // products, blog, pages, footer
            $table->boolean('is_active')->default(true);
            $table->boolean('is_navbar')->default(false);
            $table->boolean('is_footer')->default(false);
            $table->integer('position')->default(0);
            
            NestedSet::columns($table); // adds _lft, _rgt, parent_id, depth
            
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
