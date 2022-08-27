<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFirstBrand extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $default_brand = DB::table('brands')->where(['b_is_default'=>1])->whereNull('deleted_at')->first();
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

        if (!$default_brand && !in_array(config_item('company_dir'), $existing_clients)) {

            $brand_id = DB::table('brands')->insertGetId([
                'b_name' => 'Arbostar',
                'b_company_address' => '',
                'b_company_region' => '',
                'b_company_city' => '',
                'b_company_state' => '',
                'b_company_zip' => '',
                'b_company_country' => '',
                'b_company_lat' => '',
                'b_company_lng' => '',
                'b_is_default' => 1,
                'b_estimate_terms' => '',
                'b_payment_terms' => '',
                'b_pdf_footer' => '',
                'b_created_at' => date("Y-m-d H:i:s")
            ]);

            if (!$brand_id) {
                return true;
            }

            DB::table('brand_contacts')->insert([
                'bc_brand_id' => $brand_id,
                'bc_phone' => '',
                'bc_phone_clean' => '',
                'bc_email' => '',
                'bc_site' => '',
                'bc_created_at' => date("Y-m-d H:i:s")
            ]);

            $logos = config_item('logos');
            $insert = [];
            foreach ($logos as $key => $logo){

                $filePath = '/uploads/brands/' . $brand_id . '/';
                $patchinfo = pathinfo($logo['default_image']);
                $file = str_replace('//', '/', base_path($logo['default_image']));

                if(!$patchinfo || !is_array($patchinfo) || !count($patchinfo) || !is_file($file))
                    continue;

                $name = $logo['logo_file'] . '_' . basename($file);
                Storage::put($filePath . $name, file_get_contents($file));

                $insert[$key] = [
                    'bi_brand_id' => $brand_id,
                    'bi_key' => $logo['logo_file'],
                    'bi_value' => $name,
                    'bi_width' => $logo['width'],
                    'bi_height' => $logo['height'],
                    'bi_position' => 'center',
                    'bi_created_at' => date("Y-m-d H:i:s")
                ];
            }

            DB::table('brand_images')->insert($insert);
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
