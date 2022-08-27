<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSettingEstorCommision extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $fields = DB::table('settings')->insert([
												'stt_key_name' => 'estimator_commission',
												'stt_key_value' => 3,
												'stt_key_validate' => 'numeric|floatval',
												'stt_section' => 'Prices',
												'stt_label' => 'Estimator Commission (%)'
												]);
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
