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
        $table->foreignId('author_id')->constrained();
        $table->foreignId('book_id')->constrained();
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('author_book');
}
};
