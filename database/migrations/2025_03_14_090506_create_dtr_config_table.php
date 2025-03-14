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
        Schema::create('dtr_config', function (Blueprint $table) {
            $table->id();
            $table->time('grace_period_morning')->nullable();
            $table->time('morning_shift_start')->nullable();
            $table->time('morning_shift_end')->nullable();
            $table->time('grace_period_afternoon')->nullable();
            $table->time('afternoon_shift_start')->nullable();
            $table->time('afternoon_shift_out')->nullable();
            $table->time('overtime_threshold')->nullable();
            $table->time('grace_period_night')->nullable();
            $table->time('night_shift_start')->nullable();
            $table->time('night_shift_end')->nullable();
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
        Schema::dropIfExists('dtr_config');
    }
};
