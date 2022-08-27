<?php

use application\modules\equipment\models\EquipmentRepair;
use application\modules\equipment\models\EquipmentRepairStatus;
use application\modules\equipment\models\EquipmentRepairType;
use application\modules\equipment\requests\EquipmentIdRequest;
use application\modules\equipment\requests\EquipmentRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use application\modules\equipment\models\Equipment;
use Illuminate\Http\Testing\MimeType;

class Appequipmentrepairs extends APP_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->library('Common/EquipmentRepairsActions');
        $this->load->helper('fileinput');
    }

    public function ajax_get_types(){
        $types = EquipmentRepairType::all();
        return $this->successResponse(['data' => $types->toArray()]);
    }

    public function ajax_get_equipment(){
        try {
            /** @var EquipmentIdRequest $request */
            $request = app(EquipmentRequest::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(null, $e->validator->errors());
        }

        $page = $request->input('page', 1);
        $sort = $request->input('sort', ['eq_created_at', 'desc']);
        /** @var \Illuminate\Database\Query\Builder $eqQuery */
        $eqQuery = Equipment::query()->with(['group']);
        if ($request->has('where')) {
            $eqQuery->where($request->input('where'));
        }
        if ($request->has('filter')) {
            $eqQuery->where($request->input('filter'));
        }
        if ($request->has('query')) {
            $eqQuery->where(function ($query) use ($request) {
                $query->where('eq_code', 'like', '%' . $request->input('query') . '%');
                $query->orWhere('eq_name', 'like', '%' . $request->input('query') . '%');
            });
        }
        /** @var LengthAwarePaginator $equipment */
        $eqQuery->orderBy(...$sort)
            ->orderBy('eq_id', 'desc');
        $equipment = $eqQuery->paginate(30, ['*'], 'page', $page);
        if ($equipment->currentPage() > $equipment->lastPage()) {
            $equipment = $eqQuery->paginate(30, ['*'], 'page', $equipment->lastPage());
        }
        return $this->successResponse($equipment->toArray());
    }

    public function ajax_create_repair(){
        if (!request('repair_type_id', false)) {
            return $this->errorResponse(null, ['repair_type_id' => 'Type is required']);
        }
        if (!request('eq_id', false)) {
            return $this->errorResponse('Wrong Input parameters');
        }
        if (request('eq_repair', 'off') === "on") {
            Equipment::whereKey(request('eq_id'))->update(['eq_repair' => 1]);
        }
        $startStatus = EquipmentRepairStatus::whereRepairStatusFlagDefault(1)->first();
        $all = request()->all();
        $all['repair_status_id'] = $startStatus->repair_status_id;
        /** @var EquipmentRepair $repair */
        $repair = EquipmentRepair::create($all);
        if (request()->has('tmp_files')) {
            foreach (request('tmp_files') as $tmpFile) {
                $fileName = pathinfo($tmpFile, PATHINFO_BASENAME);
                $filePath = 'uploads/equipment/' . $repair->eq_id . '/';
                Storage::move($tmpFile, $filePath . $fileName);
                $repair->files()->create([
                    'eq_id' => $repair->eq_id,
                    'file_name' => $fileName,
                    'file_size' => Storage::size($filePath . $fileName),
                    'file_mime' => MimeType::from($fileName)
                ]);
            }
        }
        return $this->successResponse($repair->toArray());
    }

    public function ajax_file_upload()
    {
        $uploadResult = $this->equipmentrepairsactions->fileUpload(request()->file('files')[0]);
        if($uploadResult['status'] == false)
            return $this->errorResponse($uploadResult['data']);
        elseif (!empty($uploadResult['data']['initialPreviewConfig'])) {
            return $this->successResponse(['data' => [
                [
                    'filename' => $uploadResult['data']['initialPreviewConfig'][0]['filename'],
                    'filepath' => $uploadResult['data']['initialPreviewConfig'][0]['key']
                    ]
            ]]);
        }
    }
}