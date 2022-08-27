<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use application\modules\brands\models\BrandReview;
use application\modules\brands\models\BrandReviewLink;

class SetDefaultReviewMessage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $promoLinks = config_item('promotion_links');
        $brands = \application\modules\brands\models\Brand::get();
        foreach ($brands as $brand) {

            $review = BrandReview::create([
                'brand_id' => $brand->b_id,
                'br_header' => '<div class="green">
						            Thank you for your review! We need your help. A lot of your neighbors don\'t know about us, but you can change that! Please take a moment to copy and paste your review to our Yelp or Homestars pages, linked below. Thank You!
                            </div>',
                'br_like_message' => 'THANK YOU FOR YOUR FEEDBACK.<br><br>We are happy that you were satisfied with our services and would love to serve you again in the future!<br><br>
						Please let us know if there is anything you want to share about your experience with ' . brand_name(isset($brand->b_id) ? $brand->b_id : 0) . ' or any suggestions that you have that can help us serve you better next time.',
                'br_dislike_message' => 'THANK YOU FOR YOUR FEEDBACK.<br><br>We are happy that you were satisfied with our services and would love to serve you again in the future!<br><br>
                        We are sorry to hear that you are not happy with our services.<br><br> Please let us know what did we do wrong and we<br>will do our best to fix it.<br>'
            ]);
            foreach ($promoLinks as $link) {
                if(!isset($link['name']) || !isset($link['link']) || !$link['name'] || !$link['link']) {
                    continue;
                }
                BrandReviewLink::create([
                    'br_id' => $review->br_id,
                    'brl_name' => $link['name'],
                    'brl_link' => $link['link']
                ]);
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
