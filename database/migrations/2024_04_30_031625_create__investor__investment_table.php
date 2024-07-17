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
        Schema::create('_investor__investment', function (Blueprint $table) {

                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('investment_id')->constrained()->onDelete('cascade');
                $table->primary(['user_id', 'investment_id']);
            });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_investor__investment');
    }
};
