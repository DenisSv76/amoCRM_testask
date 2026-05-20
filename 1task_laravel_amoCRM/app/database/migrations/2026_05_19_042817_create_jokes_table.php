<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJokesTable extends Migration
{
    public function up()
    {
        Schema::create('jokes', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->text('joke_text');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jokes');
    }
}
