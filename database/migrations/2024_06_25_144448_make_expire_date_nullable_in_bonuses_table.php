<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('bonuses', function (Blueprint $table) {
        $table->date('expire_date')->nullable()->change();
    });
}

public function down()
{
    Schema::table('bonuses', function (Blueprint $table) {
        $table->date('expire_date')->nullable(false)->change();
    });
}

};
