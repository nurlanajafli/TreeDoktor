<?php

use application\modules\equipment\models\Equipment;
use application\modules\equipment\models\EquipmentFile;
use application\modules\equipment\models\EquipmentPart;
use application\modules\equipment\models\EquipmentRepair;
use application\modules\equipment\models\EquipmentRepairStatus;
use application\modules\equipment\models\EquipmentRepairType;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Testing\MimeType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Class EquipmentRepairs
 * @property \Mpdf\Mpdf $mpdf
 */
class EquipmentRepairs extends MX_Controller
{
    function __construct()
    {
        parent::__construct();

        if (!isUserLoggedIn()) {
            redirect('login');
        }
        $this->_title = SITE_NAME;
        $this->load->helper('fileinput');
        $this->load->library('Common/EquipmentRepairsActions');
    }

    public function index()
    {
        $data['title'] = $this->_title . " - Equipment Repair Requests";
        $data['repairStatuses'] = EquipmentRepairStatus::all();
        $data['repairTypes'] = EquipmentRepairType::all();
        $this->load->view('equipment/repairs', $data);
    }// End Index

    public function pdf($id)
    {
        if (!$id) {
            show_404();
        }
        /** @var EquipmentRepair $repair */
        if (!$repair = EquipmentRepair::with([
            'user',
            'repair_status',
            'assigned',
            'repair_type',
            'parts',
            'employees',
            'files',
            'equipment'
        ])->find($id)) {
            show_404();
        }
        $html = $this->load->view('equipment/equipment_repair_pdf', ['repair' => $repair], true);
        //die($html);
        $this->load->library('mpdf');
        $this->mpdf->setAutoTopMargin = "pad";
        $this->mpdf->WriteHTML($html);
        foreach ($repair->files as $file) {
            if (Str::endsWith(strtolower($file->file_name), ['.pdf'])) {
                $this->mpdf->AddPage();
                $this->mpdf->Thumbnail($file->file_stream, 1, 10, 5, 5);
            }
        }
        $pdf = $this->mpdf->Output('repair_' . $repair->repair_id . '.pdf', 'I');
        CI::$APP->output
            ->set_content_type('application/pdf; charset=utf-8')
            ->set_header('filename: repair_' . $repair->repair_id . '.pdf')
            //->set_status_header(200)
            ->set_output($pdf)
            ->_display();
        exit();
    }

    public function ajax_get_repairs()
    {
        $page = request('page', 1);
        $sort = request('sort', ['repair_created_at', 'desc']);
        /** @var Builder $partQuery */
        $repairQuery = EquipmentRepair::with([
            'user',
            'repair_status',
            'assigned',
            'repair_type',
            'equipment',
            'counter'
        ]);
        $repairQuery->whereHas('equipment');
//        if (request('filter', false)) {
//            $partQuery->where('part_name', 'like', '%' . request('filter') . '%');
//        }
        if (!empty(request('where'))) {
            $repairQuery->where(request('where'));
        }
        if (!empty(request('filter')) && is_array(request('filter'))) {
            foreach (request('filter') as $filter => $value) {
                if (!array_key_exists($filter, EquipmentRepair::COLUMNS)) {
                    continue;
                }
                $repairQuery->where($filter, '=', $value);
            }
        }
        /** @var LengthAwarePaginator $parts */
        $repairs = $repairQuery->orderBy(...$sort)
            ->orderBy('repair_id', 'desc')
            ->paginate(30, ['*'], 'page', $page);
        return $this->successResponse($repairs->toArray());
    }

    public function ajax_get_statuses()
    {
        $statuses = EquipmentRepairStatus::all();
        return $this->successResponse(['data' => $statuses->toArray()]);
    }

    public function ajax_get_types()
    {
        $types = EquipmentRepairType::all();
        return $this->successResponse(['data' => $types->toArray()]);
    }

    public function ajax_get_repair()
    {
        $id = request('repair_id', false);
        if (!$id) {
            return $this->errorResponse('Wrong Input parameters');
        }
        if (!$repair = EquipmentRepair::with([
            'user',
            'repair_status',
            'assigned',
            'repair_type',
            'parts',
            'employees',
            'employees.user',
            'files',
            'equipment',
            'counter'
        ])->find($id)) {
            return $this->errorResponse('Repair request not found');
        }
        return $this->successResponse($repair->toArray());
    }

    public function ajax_create_repair()
    {
        if (!request('repair_type_id', false)) {
            return $this->errorResponse(null, ['repair_type_id' => 'Type is required']);
        }
//        if (!request('repair_status_id', false)) {
//            return $this->errorResponse(null, ['repair_status_id' => 'Status is required']);
//        }
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

    public function ajax_update_repair()
    {
        if (!request('repair_id', false)) {
            return $this->errorResponse('Repair Request error');
        }
        /** @var EquipmentRepair $repair */
        if (!$repair = EquipmentRepair::with([
            'user',
            'repair_status',
            'assigned',
            'repair_type',
            'parts',
            'employees',
            'files'
        ])->find(request('repair_id'))) {
            return $this->errorResponse('Repair Request not found');
        }

        $parts = $repair->parts()->sync(request('parts', []), true, ['eq_id' => $repair->eq_id]);
        $repair->load('parts');
        if (request()->hasFile('part_files')) {
            foreach (request()->file('part_files') as $key => $file) {
                if (in_array($key, $parts['updated'])) {
                    $id = $key;
                } elseif (isset($parts["created"][$key])) {
                    $id = $parts["created"][$key];
                } else {
                    continue;
                }
                /** @var EquipmentPart $part */
                $part = $repair->parts->where('part_id', $id)->first();
                $part->files()->create([
                    'eq_id' => $part->eq_id,
                    'file_name' => $file
                ]);
            }
        }

        $repair->employees()->sync(request('employees', []));

        //EquipmentFile::sync($repair, request()->file('files', []), request('exist_files', []));

        if (request()->has('counter') && !empty(request('counter'))) {
            $repair->counter()->updateOrCreate(
                ['eq_id' => $repair->eq_id],
                ['counter_value' => (int)request('counter')]
            );
        }
        $all = request()->all();
        $repair->fill($all);
        $repair->save();
        $repair->refresh();
        return $this->successResponse($repair->toArray());
    }

    public function ajax_assign_user()
    {
        if (!request('repair_id', false)) {
            return $this->errorResponse('Assign error');
        }
        if (!request('user_id', false)) {
            return $this->errorResponse(null, ['user_id' => 'User is required']);
        }
        if (!$repair = EquipmentRepair::find(request('repair_id'))) {
            return $this->errorResponse('Repair Request not found');
        }

        $repair->fill(['assigned_id' => request('user_id')]);
        $repair->save();
        return $this->successResponse($repair->toArray());
    }

    public function ajax_delete_repair()
    {
        if (!request('repair_id', false)) {
            return $this->errorResponse('Delete error');
        }
        if (EquipmentRepair::destroy(request('repair_id')) === 0) {
            return $this->errorResponse('Delete error');
        }
        return $this->successResponse();
    }

    public function ajax_file_upload()
    {
        $uploadResult = $this->equipmentrepairsactions->fileUpload(request()->file('file'), request('id'));
        if($uploadResult['status'] == false)
            return $this->errorResponse($uploadResult['data']);
        return $this->successResponse($uploadResult['data']);
    }

    public function ajax_file_delete()
    {
        if (!request('key', false)) {
            return $this->errorResponse('Delete error');
        }
        if (Str::startsWith(request('key'), 'uploads/tmp')) {
            Storage::delete(request('key'));
        } else {
            if (EquipmentFile::destroy(request('key')) === 0) {
                return $this->errorResponse('Delete error');
            }
        }
        return $this->successResponse();
    }
}