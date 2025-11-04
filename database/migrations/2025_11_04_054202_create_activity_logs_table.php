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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action'); // create, update, delete
            $table->string('model_type'); // Model class name (e.g., App\Models\Product)
            $table->unsignedBigInteger('model_id')->nullable(); // ID of the model
            $table->unsignedBigInteger('user_id')->nullable(); // User who made the change
            $table->json('old_values')->nullable(); // Old values before update/delete
            $table->json('new_values')->nullable(); // New values after create/update
            $table->text('description')->nullable(); // Human-readable description
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['model_type', 'model_id']);
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
