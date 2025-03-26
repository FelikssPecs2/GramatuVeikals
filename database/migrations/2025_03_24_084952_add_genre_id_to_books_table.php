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
            $table->foreignId('genre_id')->nullable()->constrained();
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
