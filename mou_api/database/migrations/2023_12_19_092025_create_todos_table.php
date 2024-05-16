<?php

use App\Enums\TodoType;
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
        Schema::create('todos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', TodoType::getValues())->default(TodoType::SINGLE);
            $table->dateTime('done_time')->nullable();

            $table->timestamps();
            $table->foreignId('parent_id')->default(0)->constrained('todos')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todos');
    }
};
