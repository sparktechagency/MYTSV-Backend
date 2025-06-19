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
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['video', 'link']);
            $table->string('video')->nullable();
            $table->longText('link')->nullable();
            $table->string('states');
            $table->string('city');
            $table->json('tags');
            $table->string('title');
            $table->boolean('is_promoted')->default(false);
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('thumbnail');
            $table->longText('description');
            $table->enum('visibility', ['Everyone', 'Only me'])->default('Everyone');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
