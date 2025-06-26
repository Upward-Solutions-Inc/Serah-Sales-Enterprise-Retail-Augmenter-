<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('recipes', function (Blueprint $table) {
            // Drop old foreign keys
            $table->dropForeign(['product_id']);
            $table->dropForeign(['category_id']);
            // Make columns nullable
            $table->unsignedBigInteger('product_id')->nullable()->change();
            $table->unsignedBigInteger('category_id')->nullable()->change();
            // Add new foreign keys with ON DELETE SET NULL
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['category_id']);
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            $table->unsignedBigInteger('category_id')->nullable(false)->change();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }
};
