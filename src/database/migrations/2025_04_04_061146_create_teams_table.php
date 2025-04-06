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
        Schema::create('teams', function (Blueprint $table) {
            $table->id(); // Первинний ключ BigInt AutoIncrement
            $table->string('name'); // Назва команди
            $table->text('description')->nullable(); // Опис команди (необов'язково)
            $table->softDeletes(); // Додає колонку `deleted_at` для м'якого видалення
            $table->timestamps(); // Додає колонки `created_at` та `updated_at`
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
