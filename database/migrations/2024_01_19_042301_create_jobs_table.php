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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedInteger('views_count')->default(0); // Define default value
            $table->date('posted_date');
            $table->date('deadline');
            $table->string('photo1')->nullable();
            $table->string('video')->nullable();
            $table->string('document')->nullable();
            $table->string('location');
            $table->unsignedBigInteger('categoryID')->nullable();
            $table->longText('description'); 
            $table->foreign('categoryID')->references('id')->on('categories')
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
        Schema::dropIfExists('jobs');
    }
};
