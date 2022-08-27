<?php


use application\modules\equipment\models\EquipmentFile;
use application\modules\equipment\models\EquipmentRepair;
use Illuminate\Http\Testing\MimeType;
use Illuminate\Support\Facades\Storage;

class EquipmentRepairsActions
{
    public function __construct()
    {
    }

    public function fileUpload($fileToUpload, $repairId = null){
        if (!$repairId) {
            if (!$fileToUpload) {
                return [
                    'status' => false,
                    'data' => 'Repair Request error'
                ];
            }
            $fileBlob = $fileToUpload;
            $file = new stdClass;
            $file->file_name = EquipmentFile::prepareName($fileBlob->getClientOriginalName());
            $filePath = 'uploads/tmp/equipment/';
            $file->file_mime = $fileBlob->getMimeType();
            $file->file_size = $fileBlob->getSize();
            Storage::put($filePath . $file->file_name, file_get_contents($fileBlob));
            $file->file_url = Storage::url($filePath . $file->file_name);
            $file->file_id = $filePath . $file->file_name;
        } else {
            /** @var EquipmentRepair $report */
            if (!$repair = EquipmentRepair::with(['files'])->find($repairId)) {
                return [
                    'status' => false,
                    'data' => 'Repair Request not found'
                ];
            }
            $file = $repair->files()->create([
                'eq_id' => $repair->eq_id,
                'file_name' => $fileToUpload
            ]);
        }
        $initialPreview = [
            $file->file_url
        ];
        $initialPreviewConfig = [
            'caption' => $file->file_name,
            'filename' => $file->file_name,
            'width' => '60px',
            'downloadUrl' => $file->file_url,
            'key' => $file->file_id,
            'size' => $file->file_size,
        ];
        $initialPreviewConfig['filetype'] = $file->file_mime ?? MimeType::from($file->file_name);
        $initialPreviewConfig['type'] = fileTypeFromMime($file->file_mime ?? MimeType::from($file->file_name),
            $file->file_name);
        return [
            'status' => true,
            'data' => [
                'initialPreview' => $initialPreview,
                'initialPreviewConfig' => [$initialPreviewConfig],
                'append' => true
            ]
        ];
    }
}