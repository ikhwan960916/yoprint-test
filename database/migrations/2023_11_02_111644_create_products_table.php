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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->integer('unique_key')->unique();
            $table->text('title');
            $table->text('description');
            $table->string('style', 20);
            $table->string('sanmar_mainframe_size', 20);
            $table->string('size', 5);
            $table->string('color_name', 20);
            $table->unsignedDecimal('piece_price', $precision = 8, $scale = 2);
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
        Schema::dropIfExists('products');
    }
};
