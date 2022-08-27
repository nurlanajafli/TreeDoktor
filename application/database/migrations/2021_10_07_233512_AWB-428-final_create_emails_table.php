<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AWB428FinalCreateEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->id('email_id');
            $table->string('email_message_id')->nullable()->index('email_message_id_idx');
            $table->string('email_from');
            $table->text('email_to');
            $table->text('email_cc')->nullable();
            $table->text('email_bcc')->nullable();
            $table->string('email_subject')->nullable();
            $table->enum('email_status', [
                'accepted', 'delivered', 'rejected', 'bounce', 'complained', 'unsubscribed', 'error'
            ])->index('email_status_idx');
            $table->unsignedBigInteger('email_user_id')->nullable();
            $table->unsignedInteger('email_template_id')->nullable()->index('email_template_id_idx');
            $table->string('email_provider')->index('email_provider_idx');
            $table->string('email_section')->nullable()->index('email_section_idx');
            $table->text('email_error')->nullable();
            $table->dateTime('email_created_at');
            $table->dateTime('email_updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('emails');
    }
}
