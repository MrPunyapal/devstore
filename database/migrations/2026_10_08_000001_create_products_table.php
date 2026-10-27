<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('brand')->index();
            $table->string('category')->index();
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->unsignedSmallInteger('ram_gb')->nullable();
            $table->unsignedInteger('storage_gb')->nullable();
            $table->string('processor')->nullable();
            $table->decimal('screen_size', 4, 1)->nullable();
            $table->decimal('weight_kg', 5, 2);
            $table->decimal('rating', 2, 1);
            $table->boolean('in_stock');
            $table->boolean('wireless')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
