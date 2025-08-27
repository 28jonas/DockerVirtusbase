<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->datetime('start');
            $table->datetime('end')->nullable();
            $table->string('location')->nullable();
            // Koppeling naar kalender
            $table->foreignId('calendar_id')->constrained()->onDelete('cascade');
            // Polymorfe eigenaar (voor wie het event is)
            $table->unsignedBigInteger('owner_id');
            $table->string('owner_type');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
