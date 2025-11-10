<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_row_permissions', function (Blueprint $table) {
            $table->id();

            // FK vers users (contributeur)
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // FK vers table_rows (lignes du tableau)
            $table->foreignId('row_id')
                  ->constrained('table_rows')
                  ->onDelete('cascade');

            // Flag d’autorisation d’édition (0 ou 1)
            $table->boolean('can_edit')->default(true);

            $table->timestamps();

            // Éviter que le même user-row soit dupliqué
            $table->unique(['user_id', 'row_id'], 'urp_unique_user_row');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_row_permissions');
    }
};
