<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 100);
            $table->string('author', 50);
            $table->string('summary', 200)->nullable();
            $table->string('link', 200);
            $table->timestamp('pull_at');
            $table->tinyInteger('status', false, true)->default(1);
            $table->timestamps();
        });

        Schema::create('new_details', function (Blueprint $table) {
            $table->unsignedInteger('id', false, true);
            $table->mediumText('content');
            $table->timestamps();
            $table->unique('id');
        });

        Schema::create('new_correlations', function (Blueprint $table){
            $table->increments('id');
            $table->unsignedInteger('new_id');
            $table->string('title', 100);
            $table->string('author', 50);
            $table->string('link', 200);
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
        Schema::dropIfExists('news');
        Schema::dropIfExists('new_details');
        Schema::dropIfExists('new_correlations');
    }
}
