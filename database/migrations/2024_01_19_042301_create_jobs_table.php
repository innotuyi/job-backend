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
        Schema::create('Job', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('posted_date');
            $table->string('deadline');
            $table->string('photo1')->nullable();
            $table->string('location');
            $table->unsignedBigInteger('categoryID')->nullable();
            $table->longText('description'); 
            $table->foreign('categoryID')->references('id')->on('Category')
            ->onDelete('restrict')
            ->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Job');
    }
};
