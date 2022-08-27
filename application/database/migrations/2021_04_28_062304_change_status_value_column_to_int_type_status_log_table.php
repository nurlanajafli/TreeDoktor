<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeStatusValueColumnToIntTypeStatusLogTable extends Migration
{
    /**
     * @var array
     */
    const CHANGE_MODELS = [
        'lead' => [
            'status_name' => 'lead_status_name',
            'status_id' => 'lead_status_id'
        ],
        'estimate' => [
            'status_name' => 'est_status_name',
            'status_id' => 'est_status_id'
        ],
        'invoice' => [
            'status_name' => 'invoice_status_name',
            'status_id' => 'invoice_status_id'
        ]
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('status_log')->orderBy('status_id')->chunk(100, function ($status_logs) {
            foreach ($status_logs as $status_log) {

                if(!empty($status_log) && !is_numeric($status_log->status_value) && in_array($status_log->status_type, array_keys(static::CHANGE_MODELS))) {
                    $status = DB::table($status_log->status_type . '_statuses')->where([
                        static::CHANGE_MODELS[$status_log->status_type]['status_name'] => $status_log->status_value
                    ])->first();

                    if((bool)$status) {
                        DB::table('status_log')->where(['status_id' => $status_log->status_id])->update([
                            'status_value' => (int) $status->{static::CHANGE_MODELS[$status_log->status_type]['status_id']}
                        ]);
                    }
                }
            }
        });

        Schema::table('status_log', function (Blueprint $table) {
            $table->smallInteger('status_value')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('status_log', function (Blueprint $table) {
            $table->string('status_value')->change();
        });
    }
}
