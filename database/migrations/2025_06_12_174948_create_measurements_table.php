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
        Schema::create('measurements', function (Blueprint $table) {
            $table->id();
            $table->string('type');        // e.g. volume, mass
            $table->string('unit');        // e.g. ml, L, g
            $table->string('label');       // e.g. Milliliter
            $table->float('multiplier');   // e.g. 1000
            $table->string('base_unit');   // e.g. ml, g
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
        Schema::dropIfExists('measurements');
    }
};
