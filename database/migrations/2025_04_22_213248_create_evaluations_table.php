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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->string('age')->nullable();
            $table->string('situation')->nullable();
            $table->string('diplome')->nullable();
            $table->string('experience')->nullable();
            $table->string('soutien')->nullable();
            $table->string('department')->nullable();
            $table->text('motivation')->nullable();
            $table->string('change_region')->nullable();
            $table->text('project')->nullable();
            $table->text('reason')->nullable();
            $table->string('alone_team')->nullable();
            $table->string('echeance')->nullable();
            $table->string('employe')->nullable();
            $table->string('employe_three')->nullable();
            $table->string('investment_amount')->nullable();
            $table->string('investment_share')->nullable();
            $table->string('finance')->nullable();
            $table->string('activity')->nullable();
            $table->text('development_stage')->nullable();
            $table->text('marketing_condition')->nullable();
            $table->string('season')->nullable();
            $table->text('eco_modal')->nullable();
            $table->text('start_strategy')->nullable();
            $table->text('market_qualification')->nullable();
            $table->text('market_geography')->nullable();
            $table->text('clients_identified')->nullable();
            $table->text('client_knowledge')->nullable();
            $table->text('competitors_identified')->nullable();
            $table->text('suppliers_identified')->nullable();
            $table->text('supplier_characteristics')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
