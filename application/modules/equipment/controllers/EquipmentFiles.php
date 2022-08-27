<?php

use application\modules\equipment\models\Equipment;
use application\modules\equipment\models\EquipmentFile;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Testing\MimeType;
use Illuminate\Pagination\LengthAwarePaginator;

class EquipmentFiles extends MX_Controller
{
    function __construct()
    {
        parent::__construct();

        if (!isUserLoggedIn()) {
            redirect('login');
        }
        $this->_title = SITE_NAME;
        $this->load->helper('fileinput');
    }

    public function ajax_get_files()
    {
        $page = request('page', 1);
        $sort = request('sort', ['file_created_at', 'desc']);
        /** @var Builder $fileQuery */
        $fileQuery = EquipmentFile::with([
            'user',
            'repair',
            'part',
            'note',
            'service_report.service' => function (BelongsTo $query) {
                $query->withoutGlobalScope('fields');
            }
        ]);
        if (!empty(request('where'))) {
            $fileQuery->where(request('where'));
        }
        /** @var LengthAwarePaginator $files */
        $files = $fileQuery->orderBy(...$sort)
            ->orderBy('file_id', 'desc')
            ->paginate(30, ['*'], 'page', $page);
        return $this->successResponse($files->toArray());
    }

    public function ajax_create_file()
    {
        if (!request('id', false)) {
            return $this->errorResponse('File Upload error');
        }

        /** @var Equipment $eq */
        if (!$eq = Equipment::with(['files'])->find(request('id'))) {
            return $this->errorResponse('Equipment not found');
        }
        $file = $eq->files()->create([
            'file_name' => request()->file('file')
        ]);

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
        return $this->successResponse([
            'initialPreview' => $initialPreview,
            'initialPreviewConfig' => [$initialPreviewConfig],
            'append' => true
        ]);
    }

    public function ajax_delete_file()
    {
        if (!request('key', false)) {
            return $this->errorResponse('Delete error');
        }
        if (EquipmentFile::destroy(request('key')) === 0) {
            return $this->errorResponse('Delete error');
        }
        return $this->successResponse();
    }
}