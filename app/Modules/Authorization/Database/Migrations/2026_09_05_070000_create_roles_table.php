<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->metadataColumns();
            $table->timestamps();
            $table->foreignId('organization_id')->constrained('organizations');
            $table->string('scope', 30);
            $table->string('code', 100);
            $table->string('name');
            $table->integer('priority')->default(0);
            $table->text('remark')->nullable();
            $table->unique(['organization_id', 'scope', 'code']);
            $table->index('organization_id');
            $table->index('scope');
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
