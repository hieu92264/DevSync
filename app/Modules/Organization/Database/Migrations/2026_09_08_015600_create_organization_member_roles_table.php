<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_member_roles', function (Blueprint $table) {
            $table->metadataColumns();
            $table->timestamps();
            $table->foreignId('organization_member_id')->constrained('organization_members');
            $table->foreignId('role_id')->constrained('roles');
            $table->dateTime('assigned_at')->default(now());
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();

            $table->primary(['organization_member_id', 'role_id']);
            $table->index('organization_member_id');
            $table->index('role_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_member_roles');
    }
};
