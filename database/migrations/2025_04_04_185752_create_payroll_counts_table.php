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
        Schema::create('payroll_counts', function (Blueprint $table) {
            $table->id();
            $table->date('date_range_start');
            $table->date('date_range_end');
            $table->string('payroll_type');
            $table->unsignedInteger('total_employees')->default(0);
            $table->decimal('total_basic_pay', 15, 2)->default(0);
            $table->decimal('total_overtime_pay', 15, 2)->default(0);
            
            $table->unsignedInteger('total_earnings')->default(0);
            $table->unsignedInteger('total_deductions')->default(0);

            $table->decimal('total_allowance', 15, 2)->default(0);
            $table->decimal('total_sss', 15, 2)->default(0);
            $table->decimal('total_philhealth', 15, 2)->default(0);
            $table->decimal('total_pagibig', 15, 2)->default(0);
            $table->decimal('total_gross', 15, 2)->default(0);
            $table->decimal('total_net', 15, 2)->default(0);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_counts');
    }
};
