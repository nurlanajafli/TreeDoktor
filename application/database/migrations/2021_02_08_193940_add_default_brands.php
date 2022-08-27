<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AddDefaultBrands extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('brand_contacts', 'bc_site')){
            Schema::table('brand_contacts', function (Blueprint $table) {
                $table->string('bc_site')->nullable(true)->after('bc_email');
            });
        }


        $default_brand = DB::table('brands')->where(['b_is_default'=>1])->whereNull('deleted_at')->first();
        if ($default_brand) {
            $brand_images = DB::table('brand_images')->where(['bi_brand_id' => $default_brand->b_id])->whereNull('deleted_at')->get()->toArray();

            if ($brand_images) {
                foreach ($brand_images as $image) {

                    if($image->bi_style){
                        continue;
                    }

                    if($image->bi_key=='estimate_logo_file' && config_item('company_header_pdf_logo_styles'))
                        DB::table('settings')->where(['bi_id'=>$image->bi_id])->update(['bi_style'=>config_item('company_header_pdf_logo_styles')]);
                }
            }
            return true;
        }

        $existing_clients = [
            'atlas',
            'cypresstree',
            'demo',
            'foxfiretree',
            'georgiapro',
            'gordonprotree',
            'juliantreecare',
            'lewistree',
            'pacific-coast',
            'staging',
            'tampabay',
            'treedoctors',
            'treenewal',
            'vitree-alberta',
            'vitree-bc',
            'vitree-vi',
            'vitree-vm',
            'vitree-yukon'
        ];

        $CI =& get_instance();

        if(in_array(config_item('company_dir'), $existing_clients)) {

            $estimate_terms = '';
            list($result, $view) = Modules::find('pdf_templates/' . config_item('company_dir') . '/terms_conditions', 'includes', 'views/');
            if($result) {
                $estimate_terms = $CI->load->view('includes/pdf_templates/'.config_item('company_dir').'/terms_conditions', [
                    'estimate_terms' => false,
                    'estimate_data' => [],
                    'client_data' => [],
                ], TRUE);
            }
            $estimate_terms = preg_replace('/<div class="address"(.*?)<\/div>/is', '', $estimate_terms);
            $payment_terms = '';
            list($result, $view) = Modules::find('pdf_templates/' . config_item('company_dir') . '/payment_terms', 'includes', 'views/');
            if($result) {
                $payment_terms = $CI->load->view('includes/pdf_templates/'.config_item('company_dir').'/payment_terms', [
                    'estimate_terms' => false,
                    'estimate_data' => [],
                    'invoice_data' => [],
                    'client_data' => [],
                ], TRUE);
            }

            $brand_id = DB::table('brands')->insertGetId([
                'b_name' => config_item('company_name_short'),
                'b_company_address' => config_item('office_address'),
                'b_company_region' => config_item('office_region'),
                'b_company_city' => config_item('office_city'),
                'b_company_state' => config_item('office_state'),
                'b_company_zip' => config_item('office_zip'),
                'b_company_country' => config_item('office_country'),
                'b_company_lat' => config_item('office_lat'),
                'b_company_lng' => config_item('office_lon'),
                'b_is_default' => 1,
                'b_estimate_terms' => $estimate_terms,
                'b_payment_terms' => $payment_terms,
                'b_pdf_footer' => config_item('footer_pdf_address'),
                'b_created_at' => date("Y-m-d H:i:s")
            ]);

            if (!$brand_id) {
                return true;
            }

            DB::table('brand_contacts')->insert([
                'bc_brand_id' => $brand_id,
                'bc_phone' => config_item('office_phone_mask'),
                'bc_phone_clean' => config_item('office_phone'),
                'bc_email' => config_item('account_email_address'),
                'bc_site' => config_item('company_site'),
                'bc_created_at' => date("Y-m-d H:i:s")
            ]);

            $logos = config_item('logos');
            $insert = [];
            foreach ($logos as $key => $logo){

                $filePath = '/uploads/brands/' . $brand_id . '/';
                //$patchinfo = pathinfo($logo['default_image']);
                $file = str_replace('//', '/', base_path($logo['default_image']));
                    /*
                    if(isset($logo['config_key']) && config_item($logo['config_key'])){
                        $patchinfo = pathinfo($logo['default_image']);
                        $file = str_replace('//', '/', base_path(config_item($logo['default_image'])));
                    }*/

                    if($logo['logo_file']=='main_logo_file'){
                        //$patchinfo = pathinfo('/assets/' . config_item('company_dir') . '/img/logo_header.png');
                        $file = 'assets/' . config_item('company_dir') . '/img/logo_header.png';
                    }

                    if($logo['logo_file']=='invoice_left_side_file' || $logo['logo_file']=='estimate_left_side_file')
                    {
                        //$patchinfo = pathinfo('/assets/'.config_item('company_dir').'/print/container_table_left_margin.png');
                        $file = 'assets/'.config_item('company_dir').'/print/container_table_left_margin.png';
                    }
                    if($logo['logo_file']=='watermark_logo_file'){
                        //$patchinfo = pathinfo('/assets/'.config_item('company_dir').'/print/watermark.png');
                        $file = 'assets/'.config_item('company_dir').'/print/watermark.png';
                    }
                    if($logo['logo_file']=='invoice_logo_file' || $logo['logo_file']=='payment_logo_file'){
                        //$patchinfo = pathinfo('/assets/'.config_item('company_dir').'/print/watermark.png');
                        $file = 'assets/'.config_item('company_dir').'/print/header2_short.png';
                    }
                    if($logo['logo_file']=='estimate_logo_file'){
                        //$patchinfo = pathinfo('/assets/'.config_item('company_dir').'/print/watermark.png');
                        $file = 'assets/'.config_item('company_dir').'/print/header.png';
                    }



                //if(!$patchinfo || !is_array($patchinfo) || !count($patchinfo) || !is_file($file))
                if(!is_bucket_file($file))
                    continue;

                $name = $logo['logo_file'] . '_' . basename($file);
                Storage::put($filePath . $name, file_get_contents(base_url($file)));

                $insert[$key] = [
                    'bi_brand_id' => $brand_id,
                    'bi_key' => $logo['logo_file'],
                    'bi_value' => $name,
                    'bi_width' => $logo['width'],
                    'bi_height' => $logo['height'],
                    'bi_position' => 'center',
                    'bi_created_at' => date("Y-m-d H:i:s")
                ];

                if($logo['logo_file']=='estimate_logo_file' || $logo['logo_file']=='invoice_logo_file')
                    $insert[$key]['bi_style'] = config_item('company_header_pdf_logo_styles');
                else
                    $insert[$key]['bi_style'] = '';

            }

            DB::table('brand_images')->insert($insert);
        }

        return true;
        /*
        $services = DB::table('services')->where([['is_bundle', 0],['is_product', 0]])->get()->toArray();

        DB::table('settings')->insert([
            'stt_key_name'  =>  'phone_clean_length',
            'stt_key_value' =>  '10',
            'stt_key_validate'  =>  null,
            'stt_section'       =>  'Phone Numbers',
            'stt_label'         =>  'Length Without Country Code',
            'stt_is_hidden'     =>  1,
            'stt_html_attrs'    => null
        ]);
         * */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('brand_contacts', 'bc_site')) {
            Schema::table('brand_contacts', function (Blueprint $table) {
                $table->dropColumn('bc_site');
            });
        }
        /*
         DB::table('settings')->where('stt_key_name', '=', 'phone_mask_php_regex_pattern')->delete();
         * */
    }
}
