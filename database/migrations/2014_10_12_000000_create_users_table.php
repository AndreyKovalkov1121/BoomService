<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->Increments('id');
            $table->string('first_name');
            $table->string('last_name');
            // $table->string('postcode');
            $table->string('address');
            $table->string('longitude');
            $table->string('latitude');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('mobile')->nullable();
            $table->integer('status');            
            $table->timestamp('email_verified_at')->nullable();           
            $table->string('homebtnid')->nullable(); 

            //$table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
