<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendars', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('#3b82f6');
            $table->text('description')->nullable();
            // Polymorfe eigenaar
            $table->unsignedBigInteger('owner_id');
            $table->string('owner_type'); // App\Models\User of App\Models\Family
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendars');
    }
};
