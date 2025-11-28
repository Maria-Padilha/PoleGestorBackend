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
        Schema::create('pessoas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();

            // Identificação básica
            $table->string('nome');
            $table->string('cpf_cnpj')->unique()->nullable();
            $table->enum('genero', ['masculino', 'feminino', 'outro'])->nullable();
            $table->enum('pessoalidade', ['fisica', 'juridica'])->default('fisica');
            $table->date('data_nascimento')->nullable();

            // Contato
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();

            // Endereço
            $table->string('cep')->nullable();
            $table->string('endereco')->nullable();
            $table->string('numero')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado')->nullable();
            $table->string('complemento')->nullable();

            // Dados profissionais
            $table->boolean('ativo')->default(true);
            $table->enum('tipo_pessoa', ['funcionario', 'terceirizado', 'cliente', 'fornecedor'])->default('funcionario');

            // Informações adicionais úteis para eventos
            $table->text('observacoes')->nullable();

            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('set null');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pessoas');
    }
};
