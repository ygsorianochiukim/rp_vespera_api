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
        Schema::create('wbs_i_SMSStatus', function (Blueprint $table) {
            $table->id('customer_locks_id');
            $table->string('name1');
            $table->string('phone');
            $table->string('module');
            $table->integer('failed_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wbs_i_SMSStatus', function (Blueprint $table) {
            //
        });
    }
};
