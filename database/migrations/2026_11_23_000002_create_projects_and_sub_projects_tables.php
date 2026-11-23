<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 12)->unique();
            $table->string('name');
            $table->unsignedBigInteger('next_task_number')->default(101);
            $table->timestamps();
        });

        Schema::create('sub_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->restrictOnDelete();
            $table->string('name', 150);
            $table->timestamps();

            $table->unique(['project_id', 'name']);
            $table->unique(['project_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_projects');
        Schema::dropIfExists('projects');
    }
};
