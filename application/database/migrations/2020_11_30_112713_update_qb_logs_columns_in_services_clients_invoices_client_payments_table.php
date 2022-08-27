<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateQbLogsColumnsInServicesClientsInvoicesClientPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // update services qb logs
        $fields = DB::table('services')->get();
        foreach ($fields as $field) {
            if(!empty($field->service_qb_id) && $field->service_qb_id > 0) {
                DB::table('services')->where(['service_id' => $field->service_id])->update([
                    'service_last_qb_sync_result' => 1
                ]);
            } elseif ($field->service_qb_id !== NULL){
                DB::table('services')->where(['service_id' => $field->service_id])->update([
                    'service_last_qb_sync_result' => 2
                ]);
            }
        }

        // update clients qb logs
        $fields = DB::table('clients')->get();
        foreach ($fields as $field) {
            if(!empty($field->client_qb_id) && $field->client_qb_id > 0) {
                DB::table('clients')->where(['client_id' => $field->client_id])->update([
                    'client_last_qb_sync_result' => 1
                ]);
            } elseif ($field->client_qb_id !== NULL){
                DB::table('clients')->where(['client_id' => $field->client_id])->update([
                    'client_last_qb_sync_result' => 2
                ]);
            }
        }

        // update invoices qb logs
        $fields = DB::table('invoices')->get();
        foreach ($fields as $field) {
            if(!empty($field->invoice_qb_id) && $field->invoice_qb_id > 0) {
                DB::table('invoices')->where(['id' => $field->id])->update([
                    'invoice_last_qb_sync_result' => 1
                ]);
            } elseif ($field->invoice_qb_id !== NULL){
                DB::table('invoices')->where(['id' => $field->id])->update([
                    'invoice_last_qb_sync_result' => 2
                ]);
            }
        }

        // update payments qb logs
        $fields = DB::table('client_payments')->get();
        foreach ($fields as $field) {
            if(!empty($field->payment_qb_id) && $field->payment_qb_id > 0) {
                DB::table('client_payments')->where(['payment_id' => $field->payment_id])->update([
                    'payment_last_qb_sync_result' => 1
                ]);
            } elseif ($field->payment_qb_id !== NULL){
                DB::table('client_payments')->where(['payment_id' => $field->payment_id])->update([
                    'payment_last_qb_sync_result' => 2
                ]);
            }
        }

        // update qb last_qb_sync_status, last_qb_time_log
        $fields = DB::table('jobs')->orderBy('job_id', 'desc')->get();
        $array = [
            'payment' => ['push' => [], 'pull' => []],
            'invoice' => ['push' => [], 'pull' => []],
            'client' => ['push' => [], 'pull' => []],
            'item' => ['push' => [], 'pull' => []]
        ];
        foreach ($fields as $field) {
            $route = !empty(strpos($field->job_driver, 'inqb')) ? 'push' : 'pull';
            preg_match('#\/(.*?)\/#', $field->job_driver, $match);
            if(!empty($match[1])) {
                $id = null;
                $module = $match[1];
                $payload = @unserialize($field->job_payload);
                if($payload === false)
                    continue;
                if(!empty($payload['id']))
                    $id = $payload['id'];
                elseif (!empty($payload['qbId']))
                    $id = $payload['qbId'];
                if(!empty($id) && array_key_exists($module, $array) && in_array($id, $array[$module][$route]) === false && $field->job_is_completed == 0){
                    if($module == 'payment'){
                        $where = ['payment_id' => $id];
                        if($route == 'pull')
                            $where = ['payment_qb_id' => $id];
                        DB::table('client_payments')->where($where)->update(['payment_last_qb_sync_result' => 2, 'payment_last_qb_time_log' => $field->job_created_at]);
                    }elseif($module == 'invoice'){
                        $where = ['id' => $id];
                        if($route == 'pull')
                            $where = ['invoice_qb_id' => $id];
                        DB::table('invoices')->where($where)->update(['invoice_last_qb_sync_result' => 2, 'invoice_last_qb_time_log' => $field->job_created_at]);
                    }elseif($module == 'customer'){
                        $where = ['client_id' => $id];
                        if($route == 'pull')
                            $where = ['client_qb_id' => $id];
                        DB::table('clients')->where($where)->update(['client_last_qb_sync_result' => 2, 'client_last_qb_time_log' => $field->job_created_at]);
                    }elseif($module == 'item'){
                        $where = ['service_id' => $id];
                        if($route == 'pull')
                            $where = ['service_qb_id' => $id];
                        DB::table('services')->where($where)->update(['service_last_qb_sync_result' => 2, 'service_last_qb_time_log' => $field->job_created_at]);
                    }
                    array_push($array[$module][$route], $id);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
