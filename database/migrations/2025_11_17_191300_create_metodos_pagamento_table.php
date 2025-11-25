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
        Schema::create('metodos_pagamento', function (Blueprint $table) {
            $table->unsignedBigInteger('usuario_id');
            $table->id();
            $table->string('tipo');
            $table->longText('token_gateway');
            $table->string('bandeira')->nullable();
            $table->string('ultimo_digitos')->nullable();

            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metodos_pagamento');
    }
};
