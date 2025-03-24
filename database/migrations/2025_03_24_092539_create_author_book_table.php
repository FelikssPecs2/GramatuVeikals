<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
// database/migrations/xxxx_create_author_book_table.php
public function up()
{
    Schema::create('author_book', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('author_id');
        $table->unsignedBigInteger('book_id');
        $table->timestamps();

        // Foreign keys
        $table->foreign('author_id')->references('id')->on('authors')->onDelete('cascade');
        $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');

        // Unique constraint to prevent duplicate entries
        $table->unique(['author_id', 'book_id']);
    });
}

public function down()
{
    Schema::dropIfExists('author_book');
}
};
