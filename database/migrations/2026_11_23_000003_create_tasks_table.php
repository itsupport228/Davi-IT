<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id');
            $table->foreignId('sub_project_id');
            $table->foreignId('assignee_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('reporter_id')->constrained('users')->restrictOnDelete();
            $table->string('code', 33)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['todo', 'in_progress', 'done'])->default('todo');
            $table->enum('category', ['maintenance', 'development']);
            $table->enum('priority', ['low', 'high', 'critical', 'basic', 'great', 'epic']);
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // The selected sub-project must belong to the selected board.
            $table->foreign(['project_id', 'sub_project_id'])
                ->references(['project_id', 'id'])
                ->on('sub_projects')
                ->restrictOnDelete();

            $table->index('created_at');
            $table->index(['assignee_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
