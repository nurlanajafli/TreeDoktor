<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
use application\modules\brands\models\BrandReview;
class Like extends MX_Controller
{
	var $invoice;
    var $client;
    var $estimate;
	function __construct()
	{
	    parent::__construct();
		$hash = $this->uri->segment(3);
        $brandSite = brand_site_http(default_brand());
        if(!$hash) {
            if(!empty($brandSite))
                redirect($brandSite);
            else
                redirect(base_url());
        }
		else
		{
			$this->load->model('mdl_invoices');
            $this->load->model('mdl_estimates_orm');
            $this->load->model('mdl_clients');

			if($this->input->post('feedback') === FALSE)
				$this->invoice = $this->mdl_invoices->find_by_fields(array('MD5(CONCAT(invoice_no, id)) = ' => $hash, 'invoice_like' => NULL));
			else
				$this->invoice = $this->mdl_invoices->find_by_fields(array('MD5(CONCAT(invoice_no, id)) = ' => $hash));
			if(!$this->invoice){
                if(!empty($brandSite))
                    redirect($brandSite);
                else
                    redirect(base_url());
            }

            $this->estimate = $this->mdl_estimates_orm->get($this->invoice->estimate_id);
            $this->client = $this->mdl_clients->find_by_id($this->invoice->client_id);
		}
	}

	function up()
	{
        $data['brand_id'] = get_brand_id($this->estimate, $this->client);
		$data['like'] = TRUE;
		$result['status'] = 'ok';
		$links = BrandReview::where('brand_id',  $data['brand_id'])->with('reviews')->first();
		$data['promoLinks'] = !empty($links) ? $links->toArray() : [];
		if($this->input->post('feedback') !== FALSE) {
			$result['feedback'] = $this->input->post('feedback');
			if(!$result['feedback']) {
				$result['status'] = 'error';
				$result['errors']['feedback'] = 'Please Enter Your Feedback';
			}
			else {
				$result['thankyou'] = true;
			}
			$this->mdl_invoices->update($this->invoice->id, array('invoice_feedback' => $this->input->post('feedback', TRUE)));
			die(json_encode($result));
		}
		else {
			$this->mdl_invoices->update($this->invoice->id, array('invoice_like' => 1));
		}
		$this->load->view('thankyou', $data);
	}

	function down()
	{
        $data['brand_id'] = get_brand_id($this->estimate, $this->client);
		$data['like'] = FALSE;
		$result['status'] = 'ok';
        $data['promoLinks'] = BrandReview::where('brand_id',  $data['brand_id'])->with('reviews')->first()->toArray();
		if($this->input->post('feedback') !== FALSE)
		{
			if(!$this->input->post('feedback')) {
				$result['status'] = 'error';
				$result['errors']['feedback'] = 'Please Enter Your Feedback';
			}
			else {
				$this->mdl_invoices->update($this->invoice->id, array('invoice_feedback' => $this->input->post('feedback', TRUE)));
				$this->load->library('email');
				$config['mailtype'] = 'html';
				$this->email->initialize($config);
				
				$this->email->to(brand_email($data['brand_id']));
				$this->email->from(brand_email($data['brand_id']), brand_name($data['brand_id']));
				$this->email->subject('We received bad review from our client.');
				$text = '<a href="' . base_url() . $this->invoice->client_id . '">Client Profile</a><br>Review: ';
				$text .= $this->input->post('feedback', TRUE);
				$this->email->message($text);
				$this->email->send();
			}
			die(json_encode($result));
		}
		else {
			$this->mdl_invoices->update($this->invoice->id, array('invoice_like' => 0));
		}

		$this->load->view('thankyou', $data);
	}
}
