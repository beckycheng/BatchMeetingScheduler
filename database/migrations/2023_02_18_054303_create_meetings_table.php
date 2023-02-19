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
        Schema::create('meetings', function (Blueprint $table) {
            $table->string('id', 7)->primary;
            $table->string('title', 100);
            $table->string('subject')->nullable();
            $table->foreignId('moderator')->constrained('users', 'id');
            $table->unsignedSmallInteger('duration');
            $table->dateTime('deadline');
            $table->json('timeslots');
            $table->softDeletes();
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
        Schema::dropIfExists('meetings');
    }
};
