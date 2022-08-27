<?php
namespace application\modules\brands\models;

use application\core\Database\Casts\AppDate;
use application\core\Database\Casts\AppDateTime;
use application\core\Database\EloquentModel;
use application\modules\brands\models\traits\BrandsTrait;

use application\modules\brands\models\BrandContact;
use application\modules\brands\models\BrandImage;

use application\modules\mail\models\AmazonIdentity;

use application\modules\user\models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Brand extends EloquentModel
{
    use BrandsTrait;
    use SoftDeletes;

    protected $table = 'brands';
    protected $primaryKey = 'b_id';

    const CREATED_AT = 'b_created_at';
    const DELETED_AT = 'deleted_at';
    
    protected $fillable = [
        'b_name', 'b_company_address', 'b_company_city', 'b_company_state', 'b_company_zip', 'b_company_country', 'b_company_lat', 'b_company_lng', 'b_is_default', 'b_payment_terms', 'b_estimate_terms', 'b_pdf_footer'
    ];
    
    protected $appends = ['main_logo', 'full_address'];

    protected $with = [
        'images',
        'contact'
    ];
    
    
    const COLUMNS = [
        
        'b_name'            => 'Name',
        'b_company_address' => 'Address',
        'b_company_city'    => 'City',
        'b_company_state'   => 'State',
        'b_company_zip'     => 'Zip',
        'b_company_country' => 'Country',
        'bc_phone'          => 'Phone',
        'bc_email'          => 'Email',
        'bc_site'           => 'Site'
    ];
    /*
    const ENT_NAME = 'Equipment';
    const NAME_COL = 'eq_name';
    const COL_RELATIONS = [
        'group_id' => 'group',
        'user_id' => 'user',
        'seller_id' => 'seller',
    ];

    const CREATED_AT = 'eq_created_at';
    */

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    /*
    protected $casts = [
        'eq_schedule' => 'boolean',
        'eq_schedule_tool' => 'boolean',
        'eq_repair' => 'boolean',
        'eq_cost' => 'decimal:2',
        'eq_purchased_date' => AppDate::class,
        'eq_counter_type' => 'integer',
        'eq_gps_start_counter' => 'integer',
        'eq_gps_start_date' => 'datetime',
        'eq_created_at' => AppDateTime::class,
        'eq_sold_at' => AppDateTime::class,
        'eq_sold_cost' => 'decimal:2',
    ];
    */
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    
    /*
    protected static function boot()
    {
        parent::boot();

        static::with(function ($query) {
            if (($user = request()->user()) && !isset($query->attributes['user_id'])) {
                $query->user_id = $user->id;
            }
            if ($query->attributes['file_name'] instanceof UploadedFile) {
                $name = self::prepareName($query->attributes['file_name']->getClientOriginalName());
                $filePath = 'uploads/equipment/' . $query->attributes['eq_id'] . '/';
                $query->attributes['file_mime'] = $query->attributes['file_name']->getMimeType();
                $query->attributes['file_size'] = $query->attributes['file_name']->getSize();
                Storage::put($filePath . $name, file_get_contents($query->attributes['file_name']));
                $query->attributes['file_name'] = $name;

            }
        });
        static::deleted(function ($query) {
            $filePath = 'uploads/equipment/' . $query->eq_id . '/';
            Storage::delete($filePath . $query->file_name);
        });
    }*/

    
    
    
    public function images()
    {
        return $this->hasMany(BrandImage::class, 'bi_brand_id')->withTrashed();
    }

    public function contact()
    {
        return $this->hasOne(BrandContact::class, 'bc_brand_id')->withTrashed();
    }

    public function review(){
        return $this->hasOne(BrandReview::class, 'brand_id');
    }
    /*
    public function counters()
    {
        return $this->hasMany(EquipmentCounter::class, 'eq_id');
    }
    */
    
    public function getMainLogoAttribute()
    {  
        
        $image = BrandImage::where(['bi_brand_id'=>$this->attributes['b_id'], 'bi_key'=>'main_logo_file'])->withTrashed()->first();

        if(isset($image['file_url']))
            return base_url($image['file_url']);

        if(isset(config_item('brands')[$this->attributes['b_id']]))
            return base_url(config_item('brands')[$this->attributes['b_id']]->images['main_logo_file']['url']);

        return base_url('assets/img/nopic.jpg');
    }

    public function getFullAddressAttribute()
    {  
        $result_array = [
            element('b_company_address', $this->attributes, FALSE), 
            element('b_company_city', $this->attributes, FALSE),
            element('b_company_state', $this->attributes, FALSE),
            element('b_company_zip', $this->attributes, FALSE),
            element('b_company_country', $this->attributes, FALSE) 
        ];
        
        return implode(', ', array_filter($result_array));
    }

    public function getBEstimateTermsAttribute($value) {
        return trim(strip_tags(htmlspecialchars_decode(html_entity_decode($value, ENT_COMPAT, 'UTF-8')), ['div', 'input', 'font', 'ul', 'li', 'p', 'a', 'span', 'label', 'strong', 'b', 'i', 'u', 'img', 'br', 'pagebreak']));
    }

    public function getBPaymentTermsAttribute($value) {
        return trim(strip_tags(htmlspecialchars_decode(html_entity_decode($value, ENT_COMPAT, 'UTF-8')), ['div', 'input', 'font', 'ul', 'li', 'p', 'a', 'span', 'label', 'strong', 'b', 'i', 'u', 'img', 'br', 'pagebreak']));
    }

}
