<?php

use application\modules\brands\models\Brand;
use application\modules\brands\models\BrandImage;
use application\modules\brands\models\BrandContact;
use Illuminate\Validation\ValidationException;
use application\modules\brands\requests\BrandRequest;
use application\modules\brands\requests\PdfPreviewRequest;
use application\modules\mail\helpers\MailCheck;
use application\modules\brands\models\BrandReview;
use application\modules\brands\models\BrandReviewLink;
use Illuminate\Http\Request;
class Brands extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!isUserLoggedIn()) {
            redirect('login');
        }
        $this->_title = SITE_NAME;
        $this->load->config('brands');
    }

    public function index($id = 0)
    {
        $data = $this->get_brands($id);
        $data['title'] = $this->_title . ' - Brands';
        $this->load->view('index', $data);
    }

    private function get_brands($id = 0)
    {
        $data['brands'] = Brand::withTrashed()->orderBy('deleted_at')->get();
        $data['form'] = [];

        if (intval($id))
            $data['form'] = Brand::withTrashed()->find($id);

        if (config_item('default_mail_driver') === 'amazon' && isset($data['form']->contact->bc_email)) {
            if ($emailCheck = (new MailCheck())->checkEmailIdentityStatus($data['form']->contact->bc_email)) {
                $verificationData = json_decode($emailCheck->verificationAttributes, true);
                if (true === array_key_exists('VerificationStatus', $verificationData)) {
                    $data['form']->current_email_identity_status = $verificationData['VerificationStatus'];
                    $data['form']->identity_id = $emailCheck->identity_id;
                }
            } else {
                $data['form']->current_email_identity_status = 'Unverified';
            }
        }

        if($data['form']){
            $data['form']->b_estimate_terms = get_estimate_terms($data['form']->b_id);
            $data['form']->b_payment_terms = get_invoice_terms($data['form']->b_id);
            $brandReview = BrandReview::where('brand_id', $data['form']->b_id)->first();
            if(!empty($brandReview)) {
                //var_dump($data['form']->br_header);die;
                $data['form']->br_header = $brandReview->br_header;
                $data['form']->br_dislike_message = $brandReview->br_dislike_message;
                $data['form']->br_like_message = $brandReview->br_like_message;
                $brandLinks = BrandReviewLink::where('br_id', $brandReview->br_id)->get();
                if(!empty($brandLinks))
                    $data['form']->br_links = json_encode(getReviewLinksForSelect2($brandLinks->toArray()));
            }
        }
        $data['active'] = ($data['form']) ? $data['form']->b_id : 0;
        return $data;
    }

    public function save()
    {
        try {
            $request = app(BrandRequest::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(400, $e->validator->errors());
        }

        $BrandModel = new Brand();
        if ($request->input('b_id'))
            $BrandModel = Brand::withTrashed()->find($request->input('b_id'));

        
        if ($request->input('b_is_default')){
            $request->request->set('b_is_default', 1);
        } else {
            $request->request->set('b_is_default', (int)$BrandModel->b_is_default);
        }

        $phone_clean = substr(numberFrom($request->input('bc_phone')), 0, config_item('phone_clean_length'));
        $request->request->set('bc_phone_clean', $phone_clean);

        $request->request->set('b_payment_terms', htmlentities($request->input('b_payment_terms', ENT_QUOTES, "UTF-8")));
        $request->request->set('b_estimate_terms', htmlentities($request->input('b_estimate_terms'), ENT_QUOTES, "UTF-8"));


        $BrandModel->fill($request->all());
        $BrandModel->save();

        if($BrandModel->b_is_default)
            $BrandModel->where('b_is_default', 1)->where('b_id', '<>', $BrandModel->b_id)->update(['b_is_default'=>0]);

        $request->request->add(['bc_brand_id' => $BrandModel->b_id]);
        $BrandModel->load(['contact']);

        $BrandModel->contact()->updateOrCreate([
                'bc_brand_id' => $BrandModel->b_id
            ],
            [
                'bc_brand_id' => $BrandModel->b_id,
                'bc_phone' => $request->bc_phone,
                'bc_email' => $request->bc_email,
                'bc_phone_clean' => $request->bc_phone_clean,
                'bc_site'   => $request->bc_site
        ]);

        foreach (config_item('logos') as $key => $value) {
            if ($request->hasFile($value['logo_file'])) {
                $BrandImageModel = BrandImage::withTrashed()->firstOrNew([
                    'bi_brand_id' => $BrandModel->b_id,
                    'bi_key' => $value['logo_file']
                ]);
                $BrandImageModel->loadLogo(
                    $request->file($value['logo_file']),
                    $value['logo_filename'].'_'.$request->input($value['logo_filename'])
                );
                $BrandImageModel->save();
            }
        }

        if($request->input('b_review_header')){
            $brandReview = BrandReview::updateOrCreate(
                ['brand_id' => $BrandModel->b_id],
                [
                    'br_header' => $request->input('b_review_header'),
                    'br_like_message' => $request->input('b_like_message'),
                    'br_dislike_message' => $request->input('b_dislike_message')
                ]
            );
            if($request->input('b_review_links')){
                BrandReviewLink::where('br_id', $brandReview->br_id)->delete();
                $links = json_decode($request->input('b_review_links'));
                foreach ($links as $key => $value){
                    $link = [
                        'br_id' => $brandReview->br_id,
                        'brl_name' => $value->text,
                        'brl_link' => $value->id
                    ];
                    BrandReviewLink::create($link);
                }
            }
        }


        return $this->successResponse($this->get_brands($BrandModel->b_id));
    }

    public function delete($id)
    {
        if (!$id || !$brand = Brand::find($id))
            return $this->errorResponse(400, ['error' => 'Brand is not defined']);
        
        if($brand->b_is_default==1)
            return $this->errorResponse(400, ['error' => "The default brand can't be deleted"]);

        $brand->images()->delete();
        $brand->contact()->delete();
        $brand->delete();

        $this->successResponse($this->get_brands());
    }

    public function restore($id)
    {
        if (!$id || !$brand = Brand::withTrashed()->where('b_id', $id))
            return $this->errorResponse(400, ['error' => 'Brand is not defined']);
        
        
        $brand->restore();
        BrandImage::withTrashed()->where('bi_brand_id', $id)->restore();
        BrandContact::withTrashed()->where('bc_brand_id', $id)->restore();

        $this->successResponse($this->get_brands());
    }

    public function create_preview_pdf()
    {
        try {
            $request = app(PdfPreviewRequest::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(400, $e->validator->errors());
        }

        $pdf_data = $request->input('pdf_data');
        $data['name'] = 'brand_preview'.time();
        $data['template'] = $template = ($this->input->post('pdf_template'))?$this->input->post('pdf_template'):'default';

        $data['link'] = base_url('/brands/get_preview_pdf/'.$template.'/'.$data['name']);

        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $data['name'];
        file_put_contents($path, htmlspecialchars_decode(html_entity_decode($pdf_data, ENT_COMPAT, 'UTF-8')));
        $this->successResponse($data);
    }

    public function get_preview_pdf($template, $name)
    {
        if(!$name)
            return $this->errorResponse(400, ['error' => 'Preview not created']);

        $data['template'] = $template;
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $name;
        $data['estimate_terms'] = file_exists($path) ? file_get_contents($path) : null;
        file_exists($path) ? unlink($path) : null;
        $view = $this->load->view('includes/pdf_templates/terms_conditions_preview', $data, true);
        $this->load->library('mpdf');
        $this->mpdf->WriteHTML($view);
        $this->mpdf->Output('brand_preview.pdf', 'I');
    }

    public function loading()
    {
        $this->load->view('partials/loading');
    }

    public function create_review_link(Request $request){
        $data = $request->all();
//        $name = $request->input('link_name');
        $this->successResponse([$data]);
    }

    public function ajax_isset_verify()
    {
        $data = $this->input->post('data');
        $result = (new MailCheck())->checkEmailIdentityStatus($data);
        if(!$result)
        {
            $domain_array = explode('@', $data);
            if(isset($domain_array[1]) && $domain_array[1])
                $result = (new MailCheck())->checkEmailIdentityStatus($domain_array[1]);
        }

        if (
            isset($result['verificationAttributes']) &&
            $verificationAttributes = json_decode($result['verificationAttributes'], true))
        {
            $response = ['status' => true,  'data' => $verificationAttributes];
        } else {
            $response = ['status' => false, 'data' => null];
        }

        $this->response($response);
    }

}
