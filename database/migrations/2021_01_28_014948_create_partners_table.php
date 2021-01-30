<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->Increments('id');
            $table->integer('category_id');
            $table->string('partner_name');
            $table->string('address');
            $table->string('partner_email')->unique();
            $table->string('password');
            $table->string('postcodes');
            $table->integer('contact_userid');
            $table->string('email')->unique();
            $table->string('telephone');
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
        Schema::dropIfExists('partners');
    }
}
