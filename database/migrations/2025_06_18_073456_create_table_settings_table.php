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
        Schema::create('table_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained()->onDelete('cascade');

            // Priorités dynamiques
            $table->json('priorities')->nullable(); // exemple : [{"label": "P0", "delay": 0}, ...]
            $table->json('approach_thresholds')->nullable(); // exemple : {"P0": 30, "P1": 20, "P2": 10}

            // Objectifs de performance
            $table->unsignedTinyInteger('global_target')->default(80);
            $table->unsignedTinyInteger('target_p0')->default(90);
            $table->unsignedTinyInteger('target_p1')->default(80);
            $table->unsignedTinyInteger('target_p2')->default(70);

            // Statuts personnalisés
            $table->json('custom_statuses')->nullable(); // exemple : ["En pause", "En cours"]

            // Sources disponibles
            $table->json('sources')->nullable(); // ex: ["Audit", "Incident", etc.]

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_settings');
    }
};
