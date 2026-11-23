<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tl_staff', function (Blueprint $table) {
            $table->foreignId('tl_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['tl_id', 'staff_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tl_staff');
    }
};
