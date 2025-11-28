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
        Schema::create('reserva_equipamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('itens_inventario')->onDelete('cascade');
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->dateTime('data_inicio');
            $table->dateTime('data_fim');
            $table->integer('quantidade')->default(1);
            $table->enum('status', ['reservado', 'em_uso', 'devolvido', 'finalizado'])->default('reservado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reserva_equipamentos');
    }
};
