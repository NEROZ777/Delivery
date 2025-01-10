<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('store_name');
            $table->string('store_type'); // example: restaurant, market and athors
            $table->string('cuisine')->nullable();
            $table->string('likes')->nullable();
            $table->string('location')->nullable();
            $table->string('dishes')->nullable();
            $table->string('store_image')->nullable();
            $table->unsignedTinyInteger('store_rate')->comment('Rating from 1 to 5')->nullable();
            
            $table->timestamps();
        });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
