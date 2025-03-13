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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('author_id')->constrained();
            $table->decimal('price', 8, 2)->nullable(); // Cena
            $table->integer('age')->nullable(); // Vecuma lauks
            $table->integer('pages')->nullable(); // Lappušu skaits
            $table->text('description')->nullable(); // Apraksts
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if the book_genre table exists before dropping foreign keys
        if (Schema::hasTable('book_genre')) {
            Schema::table('book_genre', function (Blueprint $table) {
                $table->dropForeign(['book_id']);
                $table->dropForeign(['genre_id']);
            });

            // Drop the book_genre pivot table
            Schema::dropIfExists('book_genre');
        }

        // Check if the books table exists before dropping foreign keys
        if (Schema::hasTable('books')) {
            Schema::table('books', function (Blueprint $table) {
                $table->dropForeign(['author_id']);
            });

            // Drop the books table
            Schema::dropIfExists('books');
        }
    }
};
