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
    Schema::create('tscoatings', function (Blueprint $table) {
      $table->id();
      $table->enum('equipo', ['SC-01', 'SC-02', 'SC-03']);
      $table->string('turno');
      $table->enum('tipo', ['1', '2', '3', 'MTTO', 'RMK']);
      $table->date('dia');
      $table->string('plan')->default(0);
      $table->string('c')->default(0);
      $table->string('type')->default(0);
      $table->string('planuser')->default(0)->nullable();
      $table->string('cuser')->default(0)->nullable();
      $table->string('typeuser')->default(0)->nullable();
      $table->smallInteger('priority')->default(1);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('tscoatings');
  }
};
