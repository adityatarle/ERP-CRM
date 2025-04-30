<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartiesTable extends Migration
{
    public function up()
    {
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index(); // Party Name
            $table->string('gst_in')->nullable(); // GST/IN
            $table->string('email')->nullable(); // Email/EmailCC
            $table->string('phone_number')->nullable(); // Phone/Mobile Number
            $table->string('address')->nullable(); // Address
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parties');
    }
}