<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBonusesTable extends Migration
{
    public function up()
    {
        Schema::create('bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['one-time', 'weekly']);
            $table->date('bonus_date');
            $table->date('expire_date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bonuses');
    }
}
