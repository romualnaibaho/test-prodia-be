<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWindDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wind_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('weather_condition_id');
            $table->float('speed');
            $table->integer('direction');
            $table->timestamps();

            $table->foreign('weather_condition_id')->references('id')->on('weather_conditions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wind_data');
    }
}
