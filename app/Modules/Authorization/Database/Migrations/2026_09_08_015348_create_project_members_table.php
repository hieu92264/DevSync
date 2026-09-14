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
        Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->metadataColumns();
            $table->timestamps();
            $table->foreignId('project_id')->constrained('projects');
            $table->foreignId('user_id')->constrained('users');
            $table->string('team_type', 50)->nullable();
            $table->dateTime('joined_at')->default(now());
            $table->dateTime('left_at')->nullable();

            $table->unique(['project_id', 'user_id']);
            $table->index('project_id');
            $table->index('user_id');
            $table->index('team_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_members');
    }
};
