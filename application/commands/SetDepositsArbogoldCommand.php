<?php

namespace application\commands;

use application\modules\invoices\models\Invoice;
use application\modules\payments\models\ClientPayment;
use Illuminate\Console\Command;

class SetDepositsArbogoldCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'arbogold:setDeposits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
//        $this->output->text(floatval(str_replace('$', "", '$559.35')));
//        return;
        $file = bucket_read_file('uploads/import_files/invoices.csv');
        file_put_contents(storage_path('file.csv'), $file);
        if (($open = fopen(storage_path('file.csv'), "r")) !== FALSE) {

            $key = 0;

            while (($data = fgetcsv($open, 1000, ",")) !== FALSE) {
                $key++;

                if ($key == 1) {
                    $amountKey = array_search('Amount', $data);
                    $unpaidAmountKey = array_search('Unpaid Amount', $data);
                    $idKey = array_search('Inv#', $data);
                }
                if (!empty($amountKey) && !empty($unpaidAmountKey) && !empty($idKey)) {
                    $amount = floatval(str_replace(['$', ','], "", $data[$amountKey]));
                    $unpaidAmount = floatval(str_replace(['$', ','], "", $data[$unpaidAmountKey]));
                    $id = $data[$idKey];
                    if ($amount != $unpaidAmount && $amount - $unpaidAmount >= 0) {
                        $invoice = Invoice::where('invoice_qb_id', $id)->get()->first();
                        if (!empty($invoice)) {
                            $paymentToDB = [
                                'estimate_id' => $invoice->estimate_id,
                                'payment_method_int' => 1,
                                'payment_date' => '',
                                'payment_amount' => $amount - $unpaidAmount,
                                'payment_checked' => 1,
                                'payment_type' => 'deposit',
                                'payment_qb_id' => 0
                            ];
                            $this->output->text($paymentToDB);
                            $this->output->text($invoice->invoice_no);
                            ClientPayment::insert($paymentToDB);
                        }
                    }

                }

            }

            fclose($open);
        }
    }
}
