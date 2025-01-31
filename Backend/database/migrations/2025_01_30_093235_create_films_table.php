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
        Schema::create('films', function (Blueprint $table) {
            $table->Integer('id')->autoIncrement();
            $table->string('title', 60);
            $table->Integer('production')->nullable();
            $table->Integer('length')->nullable();
            $table->date('presentation')->nullable();
            $table->string('imdbLink',255)->nullable();
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('films');
    }
};
