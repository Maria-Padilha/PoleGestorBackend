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
        Schema::create('itens_inventario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->string('codigo')->unique();
            $table->enum('tipo', ['equipamento', 'consumivel']);
            $table->string('categoria')->nullable();
            $table->string('unidade')->default('un');

            $table->boolean('controla_estoque')->default(true);
            $table->integer('quantidade_atual')->default(0);

            $table->integer('quantidade_total')->default(0)->nullable();
            $table->integer('quantidade_em_uso')->default(0)->nullable();
            $table->integer('quantidade_disponivel')->default(0)->nullable();

            $table->enum('status', ['disponivel', 'em_uso', 'manutencao', 'esgotado', 'acabando', 'perdido', 'em_estoque'])
                ->default('disponivel')->nullable();

            $table->string('localizacao')->nullable();
            $table->string('fornecedor')->nullable();
            $table->boolean('ativo')->default(true);
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itens_inventario');
    }
};
