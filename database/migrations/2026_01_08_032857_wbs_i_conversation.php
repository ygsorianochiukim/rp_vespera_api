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
        Schema::create('wbs_i_conversation', function (Blueprint $table) {
            $table->id('conversation_id');
            $table->bigInteger('customer_psid');
            $table->string('conversation_name');
            $table->string('assigned_status');
            $table->string('assigned_agent');
            $table->string('status');
            $table->string('last_message');
            $table->integer('transfer_count_bot');
            $table->integer('transfer_count_human');
            $table->date('date_created');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
