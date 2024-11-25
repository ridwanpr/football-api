<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('type')->nullable();
            $table->string('emblem')->nullable();
            $table->string('plan')->nullable();
            $table->string('area_name')->nullable();
            $table->string('area_code')->nullable();
            $table->json('current_season')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('competitions');
    }
};
