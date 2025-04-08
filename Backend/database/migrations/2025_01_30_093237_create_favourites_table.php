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
        Schema::create('favourites', function (Blueprint $table) {
            $table->Integer('id')->autoIncrement();
            $table->Integer('userId')->nullable();
            // $table->foreign('userId')->references('id')->on('users');
            $table->Integer('filmId')->nullable();
            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('filmId')->references('id')->on('films')->onDelete('cascade');;
            $table->decimal('evaluation', 2, 1)->comment('0.5 to 5.0')->nullable();
            $table->string('content')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favourites');
    }
};
