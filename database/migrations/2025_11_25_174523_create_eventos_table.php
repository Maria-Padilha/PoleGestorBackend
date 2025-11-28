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
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->dateTime('data_inicio');
            $table->dateTime('data_fim')->nullable();
            $table->dateTime('montagem')->nullable();
            $table->dateTime('desmontagem')->nullable();
            $table->string('localizacao')->nullable();
            $table->decimal('orcamento', 15, 2)->default(0);
            $table->enum('status', ['planejado', 'em_andamento', 'concluido', 'cancelado', 'confirmado', 'iniciado'])->default('planejado');
            $table->string('tipo_evento')->nullable();
            $table->boolean('repetir_evento')->default(false);

            $table->timestamps();
        });

        Schema::create('evento_cliente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('pessoas')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('evento_funcionario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->foreignId('funcionario_id')->constrained('pessoas')->onDelete('cascade');
            $table->string('funcao')->nullable();
            $table->decimal('horas_trabalhadas', 8, 2)->default(0);
            $table->decimal('custo', 15, 2)->default(0);
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });

        Schema::create('evento_kit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->foreignId('kit_id')->constrained('kits')->onDelete('cascade');
            $table->integer('quantidade')->default(1);
            $table->timestamps();
        });

        Schema::create('evento_item_consumo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('itens_inventario')->onDelete('cascade');
            $table->decimal('quantidade_consumida', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evento_item_consumo');
        Schema::dropIfExists('evento_kit');
        Schema::dropIfExists('evento_funcionario');
        Schema::dropIfExists('evento_cliente');
        Schema::dropIfExists('eventos');
    }
};
