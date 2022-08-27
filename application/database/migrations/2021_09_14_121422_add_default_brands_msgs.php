<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use application\modules\brands\models\BrandReview;
use application\modules\brands\models\BrandReviewLink;

class AddDefaultBrandsMsgs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $brand = \application\modules\brands\models\Brand::where('b_is_default', '=', 1)->with('review')->first();
        //echo '<pre>'; var_dump($brand->review['br_header']); die;
        if(isset($brand->review['br_header']) && $brand->review['br_header'] == '')
            BrandReview::where('brand_id', '=', $brand->b_id)->update(['br_header' => '<div class="green">Thank you for your review!</div>']);
        if(isset($brand->review['br_dislike_message']) && $brand->review['br_dislike_message'] == '')
            BrandReview::where('brand_id', '=', $brand->b_id)->update(['br_dislike_message' => 'THANK YOU FOR YOUR FEEDBACK.<br><br>We are sorry to hear that you are not happy with our services.<br><br>Please let us know what did we do wrong and we will do our best to fix it.']);
        if(isset($brand->review['br_like_message']) && $brand->review['br_like_message'] == '')
            BrandReview::where('brand_id', '=', $brand->b_id)->update(['br_like_message' => 'THANK YOU FOR YOUR FEEDBACK.<br><br>We are happy that you were satisfied with our services and would love to serve you again in the future!<br><br>Please let us know if there is anything you want to share about your experience with us or any suggestions that you have that can help us serve you better next time.']);

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
