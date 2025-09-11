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
        Schema::create('persones', function (Blueprint $table) {
            $table->id('id_persona');
            $table->integer('id_fide')->nullable()->unique();
            $table->integer('id_feda')->nullable()->unique();
            $table->integer('id_fcde')->nullable()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persones');
    }
};
