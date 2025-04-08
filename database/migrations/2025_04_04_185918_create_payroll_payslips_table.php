<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_payslips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('payroll_count_id');
            
            $table->decimal('basic_pay', 15, 2)->default(0);
            $table->decimal('overtime_pay', 15, 2)->default(0);
            $table->decimal('night_differential', 15, 2)->default(0);

            $table->decimal('allowance', 15, 2)->default(0);

            $table->decimal('income_tax', 15, 2)->default(0);
            $table->decimal('sss', 15, 2)->default(0);
            $table->decimal('pagibig', 15, 2)->default(0);
            $table->decimal('philhealth', 15, 2)->default(0);
        
            $table->json('earnings')->nullable();
            $table->json('deductions')->nullable();
        
            $table->decimal('gross', 15, 2)->default(0);
            $table->decimal('net', 15, 2)->default(0);
            $table->timestamps();
        
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('payroll_count_id')->references('id')->on('payroll_counts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_payslips');
    }
};
