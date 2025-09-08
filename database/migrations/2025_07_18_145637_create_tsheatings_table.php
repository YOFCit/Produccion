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
    Schema::create('tsheatings', function (Blueprint $table) {
      $table->id();
      $table->string('equipo');
      $table->date('dia');
      $table->string('turno');
      $table->string('tipo');
      $table->integer('plan')->default(0);
      $table->integer('c')->default(0);
      $table->string('planuser')->nullable();
      $table->string('cuser')->nullable();
      $table->smallInteger('priority')->default(1);
      $table->timestamps();
      $table->integer('diaajustable');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('tsheatings');
  }
};
