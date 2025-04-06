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
        Schema::create('time_entry_custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('time_entry_id')
                ->constrained('time_entries')
                ->cascadeOnDelete();
            $table->foreignId('custom_field_definition_id')
                ->constrained('custom_field_definitions')
                ->cascadeOnDelete();
            $table->text('value');
            $table->timestamps();

            $table->unique(['time_entry_id', 'custom_field_definition_id'], 'time_entry_custom_field_unique');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_entry_custom_field_values');
    }
};
