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
        Schema::table('books', function (Blueprint $table) {
            $table->integer('age')->nullable(); // Vecuma lauks
            $table->integer('pages')->nullable(); // Lappušu skaits
            $table->text('description')->nullable(); // Apraksts
        });
    }
    
    public function down()
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['age', 'pages', 'description']);
        });
    }
    
};
