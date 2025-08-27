<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('family_id')->constrained()->onDelete('cascade');
            $table->string('role'); // 'owner', 'parent', 'child', 'guest'
            $table->timestamps();
            $table->unique(['user_id', 'family_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_user');
    }
};
