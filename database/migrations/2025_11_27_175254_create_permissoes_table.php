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
        Schema::create('permissoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('colaborador_id')->nullable();

            $table->boolean('gerenciar_pessoas')->default(false)->nullable();
            $table->boolean('gerenciar_eventos')->default(false)->nullable();
            $table->boolean('gerenciar_financeiro')->default(false)->nullable();
            $table->boolean('gerenciar_relatorios')->default(false)->nullable();
            $table->boolean('gerenciar_estoque')->default(false)->nullable();
            $table->boolean('gerenciar_inventario')->default(false)->nullable();
            $table->boolean('gerenciar_contratos')->default(false)->nullable();

            $table->foreign('colaborador_id')->references('id')->on('colaboradores')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissoes');
    }
};
