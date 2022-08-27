<?php

use Illuminate\Database\Migrations\Migration;

class UpdateSmsSegment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \application\modules\messaging\models\Messages::where('sms_segment', '=', 0)->update(['sms_segment' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
