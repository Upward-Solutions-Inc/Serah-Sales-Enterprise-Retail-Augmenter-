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
        Schema::create('payroll_components', function (Blueprint $table) {
            $table->id();
            $table->string('group'); // e.g. rate_basic, earnings
            $table->string('code');  // system reference key
            $table->string('label'); // display name
            $table->decimal('value', 12, 4); // rate or amount
            $table->boolean('is_rate')->default(0); // 1 = rate, 0 = fixed
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
        Schema::dropIfExists('payroll_components');
    }
};
