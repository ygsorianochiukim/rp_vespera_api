<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customerOtp', function (Blueprint $table) {
            $table->id();
            $table->string('name1');
            $table->string('phone');
            $table->string('module')->default('SOA');
            $table->integer('otp');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customerOtp', function (Blueprint $table) {
            //
        });
    }
};
