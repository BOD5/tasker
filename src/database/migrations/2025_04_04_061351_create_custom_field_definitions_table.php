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
        Schema::create('custom_field_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')
                  ->nullable()
                  ->constrained('teams')
                  ->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->index();
            $table->string('type')->index();
            $table->jsonb('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->softDeletes();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_field_definitions');
    }
};
