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
        Schema::create('organization_members', function (Blueprint $table) {
            $table->metadataColumns();
            $table->timestamps();

            $table->foreignId('organization_id')->constrained('organizations');
            $table->foreignId('user_id')->constrained('users');

            $table->dateTime('joined_at')->nullable();
            $table->dateTime('left_at')->nullable();

            $table->primary(['organization_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_members');
    }
};
