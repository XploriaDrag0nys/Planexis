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
        Schema::create('user_table_roles', function (Blueprint $table) {
            $table->id();
            
            // FK vers users
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // FK vers tables (plans d’action)
            $table->foreignId('table_id')
                  ->constrained('tables')
                  ->onDelete('cascade');

            // FK vers roles (project_manager ou contributor)
            $table->foreignId('role_id')
                  ->constrained('roles')
                  ->onDelete('restrict');

            $table->timestamps();

            // On empêche qu’un même user ait plusieurs fois le même rôle sur le même tableau
            $table->unique(['user_id', 'table_id', 'role_id'], 'utr_unique_user_table_role');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_table_roles');
    }
};
