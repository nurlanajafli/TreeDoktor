<?php


namespace application\commands;

use application\core\Console\Command;
use application\models\PaymentTransaction;

class GetPaymentTransactionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transaction:get {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get transaction by ID';

    public $CI;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->CI->load->library('Payment/ArboStarProcessing', null, 'arboStarProcessing');
        $this->CI->load->model('mdl_client_payments');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $id = $this->argument('id');
        if (!$payment = $this->CI->mdl_client_payments->fetch($id)) {
            $this->error('Payment not found');
            return;
        }
        if (!$transaction = PaymentTransaction::find($payment->payment_trans_id)) {
            $this->error('Transaction not found');
            return;
        }

        if ($transaction->payment_transaction_remote_id !== null && $transaction->payment_transaction_remote_id !== "") {
            try {
                $tr = $this->CI->arboStarProcessing->getTransaction($transaction->payment_transaction_remote_id, $transaction->payment_driver);
            }
            catch (\PaymentException $e) {
                $this->error($e->getMessage());
                return;
            }

            $this->info(json_encode($tr, JSON_PRETTY_PRINT));
        }
    }
}