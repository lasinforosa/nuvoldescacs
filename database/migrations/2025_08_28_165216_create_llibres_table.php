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
        Schema::create('llibres', function (Blueprint $table) {
            $table->id('id_llibres'); // Clau primària autoincremental
            $table->string('categoria', 50)->nullable();
            $table->string('lloc', 50)->nullable();
            $table->string('autor', 100)->nullable();
            $table->string('titol', 200);
            $table->string('temes', 200)->nullable();
            $table->string('nota', 100)->nullable();
            $table->timestamps(); // Camps 'created_at' i 'updated_at' automàtics
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llibres');
    }
};
