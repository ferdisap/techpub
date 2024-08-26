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
        // Schema::connection('users_sqlite')->create('password_resets', function (Blueprint $table) {
        // Schema::connection('sqlite')->create('password_resets', function (Blueprint $table) {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::connection('users_sqlite')->dropIfExists('password_resets');
        // Schema::connection('sqlite')->dropIfExists('password_resets');
        Schema::dropIfExists('password_resets');
    }
};
