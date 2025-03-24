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
            $table->unsignedBigInteger('genre_id')->nullable(); // Add genre_id column
            $table->foreign('genre_id')->references('id')->on('genres')->onDelete('set null');
        });
    }
    
    public function down()
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['genre_id']); // Drop foreign key
            $table->dropColumn('genre_id'); // Drop genre_id column
        });
    }
};
