<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropForeign(['project_id', 'sub_project_id']);
        });
        Schema::table('tasks', function (Blueprint $table): void {
            $table->foreignId('sub_project_id')->nullable()->change();
        });
        Schema::table('tasks', function (Blueprint $table): void {
            $table->foreign(['project_id', 'sub_project_id'])
                ->references(['project_id', 'id'])->on('sub_projects')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::table('tasks')->whereNull('sub_project_id')->exists()) {
            throw new \RuntimeException('Task tanpa sub-project harus dipindahkan sebelum rollback migration ini.');
        }
        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropForeign(['project_id', 'sub_project_id']);
        });
        Schema::table('tasks', function (Blueprint $table): void {
            $table->foreignId('sub_project_id')->nullable(false)->change();
        });
        Schema::table('tasks', function (Blueprint $table): void {
            $table->foreign(['project_id', 'sub_project_id'])
                ->references(['project_id', 'id'])->on('sub_projects')->restrictOnDelete();
        });
    }
};
