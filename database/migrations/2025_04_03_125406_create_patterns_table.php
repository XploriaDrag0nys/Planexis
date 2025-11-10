<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('table_patterns', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ex: "Fiche Employé", "Liste de tâches"
            $table->json('columns'); // Ex: [{ "name": "Nom", "type": "string" }, ...]
            $table->timestamps();
        });
        
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patterns');
    }
};
