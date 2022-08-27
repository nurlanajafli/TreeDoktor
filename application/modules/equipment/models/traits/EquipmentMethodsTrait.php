<?php


namespace application\modules\equipment\models\traits;


use application\modules\equipment\models\EquipmentFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait EquipmentMethodsTrait
{
    public function loadPhoto(UploadedFile $file)
    {
        $name = EquipmentFile::prepareName($file->getClientOriginalName());
        $filePath = 'uploads/equipment/' . $this->eq_id . '/';
        Storage::put($filePath . $name, file_get_contents($file));
        if ($this->eq_photo != null) {
            Storage::delete($filePath . $this->eq_photo);
        }
        $this->eq_photo = $name;
        return $this;
    }
}