<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AWB428FinalAddEmailStatusesToEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $emails = DB::table('emails')->get();

        Schema::table('emails', function (Blueprint $table) {
            $table->dropColumn('email_status');
        });

        Schema::table('emails', function (Blueprint $table) {
            $table->enum('email_status', [
                'accepted', 'delivered', 'rejected', 'bounce', 'complained', 'unsubscribed', 'error', 'opened', 'clicked'
            ])->after('email_subject')->index('email_status_idx');
        });

        foreach ($emails as $email) {
            DB::table('emails')->where('email_id', $email->email_id)->update(['email_status' => $email->email_status]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $emails = DB::table('emails')->get();

        Schema::table('emails', function (Blueprint $table) {
            $table->dropColumn('email_status');
        });

        Schema::table('emails', function (Blueprint $table) {
            $table->enum('email_status', [
                'accepted', 'delivered', 'rejected', 'bounce', 'complained', 'unsubscribed', 'error'
            ])->after('email_subject')->index('email_status_idx');
        });

        foreach ($emails as $email) {
            DB::table('emails')->where('email_id', $email->email_id)->update(['email_status' => $email->email_status]);
        }
    }
}
