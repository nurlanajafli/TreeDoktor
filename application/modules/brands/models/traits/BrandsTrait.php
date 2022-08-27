<?php

namespace application\modules\brands\models\traits;

use application\modules\brands\models\BrandImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage; 
use Illuminate\Support\Facades\MimeType;
trait BrandsTrait
{
    public function loadLogo(UploadedFile $file, $name=null)
    {
        if(!$name)
            $name = BrandImage::prepareName($file->getClientOriginalName());

        $filePath = 'uploads/brands/' . $this->bi_brand_id . '/';
        if ($this->bi_value != null) {
            Storage::delete($filePath . $this->bi_value);
        }

        Storage::put($filePath . $name, file_get_contents($file));
        
        $this->bi_value = $name;
        return $this;
    }

    
    /**
     * Scope a query that matches a full text search of term.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $term
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $term)
    {

    }
}