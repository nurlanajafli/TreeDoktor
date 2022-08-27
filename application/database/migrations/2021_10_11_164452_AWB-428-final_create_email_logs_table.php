<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AWB428FinalCreateEmailLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id('email_log_id');
            $table->unsignedBigInteger('email_log_email_id')->index('email_log_email_id_idx');
            $table->string('email_log_message_id')->nullable()->index('email_log_message_id_idx');
            $table->string('email_log_tracking_id')->nullable();
            $table->enum('email_log_tracking_status', [
                'accepted', 'delivered', 'rejected', 'bounce', 'complained', 'unsubscribed', 'error', 'opened', 'clicked'
            ])->index('email_log_tracking_status_idx');
            $table->text('email_log_tracking_details')->nullable();
            $table->text('email_log_error')->nullable();
            $table->string('email_log_provider')->index('email_log_provider_idx');
            $table->dateTime('email_log_created_at');

            $table->foreign('email_log_email_id')->references('email_id')->on('emails')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_logs');
    }
}
