<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('countryCode')->unique();
            $table->string('flag')->nullable();
            $table->unsignedBigInteger('parentAreaId')->nullable();
            $table->string('parentArea')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('areas');
    }
};
