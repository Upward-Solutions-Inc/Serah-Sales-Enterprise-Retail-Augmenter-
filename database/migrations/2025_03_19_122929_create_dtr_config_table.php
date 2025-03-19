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
            $table->integer('grace_period')->default(5);
            $table->integer('overtime')->default(15);
            $table->time('morning_shift_start')->nullable();
            $table->time('morning_shift_end')->nullable();
            $table->time('afternoon_shift_start')->nullable();
            $table->time('afternoon_shift_end')->nullable();
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
