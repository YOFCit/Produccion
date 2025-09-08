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
    Schema::create('tcolorings', function (Blueprint $table) {
      $table->id();
      $table->enum('equipo', ['CL-01', 'CL-02', 'CL-03', 'CL-04', 'CL-05'])->nullable();
      $table->integer('turno')->nullable();
      $table->enum('dias', ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'])->nullable();
      $table->string('tipofibra')->nullable();
      $table->string('plan')->default(0)->nullable();
      $table->string('c')->default(0)->nullable();
      $table->string('type')->default(0)->nullable();
      $table->string('planuser')->default(0)->nullable();
      $table->string('cuser')->default(0)->nullable();
      $table->string('typeuser')->default(0)->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('tcolorings');
  }
};
