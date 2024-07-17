<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvestmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2); // Assuming 'amount' is a decimal field with precision 10 and scale 2
            $table->date('deposit_date');
            $table->decimal('profit_percentage', 5, 2); // Assuming 'profit_percentage' is a decimal field with precision 5 and scale 2
            $table->integer('cycle_days');
            $table->enum('status', ['open', 'pending_closure', 'closed'])->default('open');
            $table->date('profit_withdrawal_limit_date')->nullable();
            $table->date('due_profit')->nullable();
            $table->date('maturity_date')->nullable();
            $table->date('renewal_requested_at')->nullable();
            $table->date('renewal_approved_at')->nullable();
            $table->string('renewal_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('investments');
    }
}
