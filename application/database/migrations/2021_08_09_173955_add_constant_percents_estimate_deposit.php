<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConstantPercentsEstimateDeposit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->insert([
            'stt_key_name'  =>  'percents_estimate_deposit',
            'stt_key_value' =>  10,
            'stt_key_validate'  =>  null,
            'stt_section'       =>  'Prices Management',
            'stt_label'         =>  'Estimate Deposit Percentes',
            'stt_is_hidden'     =>  1,
            'stt_html_attrs'    => null
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')->where('stt_key_name', '=', 'percents_estimate_deposit')->delete();
    }
}
