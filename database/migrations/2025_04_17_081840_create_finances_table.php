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
        Schema::create('finances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->json('produits')->nullable();
            $table->json('vente_previsionnels')->nullable();
            $table->json('capital_demarrage')->nullable();
            $table->json('emprunts')->nullable();
            $table->json('personnel')->nullable();
            $table->json('investissements')->nullable();
            $table->json('frais_fonctionnement')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finances');
    }
};
