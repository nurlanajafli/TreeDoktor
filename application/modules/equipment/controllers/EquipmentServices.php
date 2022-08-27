<?php

use application\modules\equipment\models\Equipment;
use application\modules\equipment\models\EquipmentFile;
use application\modules\equipment\models\EquipmentPart;
use application\modules\equipment\models\EquipmentService;
use application\modules\equipment\models\EquipmentServiceReport;
use application\modules\equipment\models\EquipmentServiceType;
use application\modules\equipment\requests\EquipmentServiceCreateUpdateRequest;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Testing\MimeType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EquipmentServices extends MX_Controller
{
    function __construct()
    {
        parent::__construct();

        if (!isUserLoggedIn()) {
            redirect('login');
        }
        $this->_title = SITE_NAME;
    }

    public function index()
    {
        $data['title'] = $this->_title . " - Equipment Services";
        $data['serviceTypes'] = EquipmentServiceType::all();
        $this->load->view('equipment/services', $data);
    }// End Index

    public function pdf()
    {
        $page = request('page', 1);

        $serviceQuery = $this->_getListQuery();

        //if (request()->has('noPaginate') && request('noPaginate') == "true") {
        $items = $serviceQuery->get();
        //} else {
        //     $services = $serviceQuery->paginate(30, ['*'], 'page', $page);
        //    $items = $services->items();
        //}

        $html = $this->load->view('equipment/equipment_services_pdf', ['services' => $items], true);
        //die($html);
        $this->load->library('mpdf');
        $this->mpdf->setAutoTopMargin = "pad";
        $this->mpdf->AddPage('L', 'Letter', 0, 1, 'off', 2, 2, 1, 1, 0);
        $this->mpdf->WriteHTML($html);

        $filename = 'eq_services_' . Carbon::now()->format('Y-m-d-H-i-s') . '.pdf';
        $pdf = $this->mpdf->Output('eq_services_' . Carbon::now()->format('Y-m-d-H-i-s') . '.pdf', 'I');
        CI::$APP->output
            ->set_content_type('application/pdf; charset=utf-8')
            ->set_header('filename: ' . $filename)
            //->set_status_header(200)
            ->set_output($pdf)
            ->_display();
        exit();
    }

    private function _getListQuery()
    {
        $sort = request('sort', ['service_next_date', 'desc']);
        /** @var Builder $partQuery */
        $serviceQuery = EquipmentService::with(['user', 'equipment', 'service_type', 'last_report.counter']);
        $serviceQuery->whereHas('equipment');

        if (!empty(request('where'))) {
            $serviceQuery->where(request('where'));
        }
        if (!empty(request('filter')) && is_array(request('filter'))) {
            foreach (request('filter') as $filter => $value) {
                if (!array_key_exists($filter, EquipmentService::COLUMNS)) {
                    continue;
                }
                if (in_array($filter, ['service_type_form'])) {
                    $serviceQuery->whereHas('service_type', function ($query) use ($filter, $value) {
                        /** @var \Illuminate\Database\Eloquent\Builder $query */
                        return $query->where($filter, '=', $value);
                    });
                } else {
                    $serviceQuery->where($filter, '=', $value);
                }
            }
        }

        switch (request('filter.due', 'week')) {
            case 'week':
                $serviceQuery->where('service_next_date', '<=', Carbon::now()->endOfWeek()->format('Y-m-d'));
                break;
            case 'n-week':
                $serviceQuery->where('service_next_date', '>=',
                    Carbon::now()->addWeek()->startOfWeek()->format('Y-m-d'));
                $serviceQuery->where('service_next_date', '<=', Carbon::now()->addWeek()->endOfWeek()->format('Y-m-d'));
                break;
            case 'month':
                $serviceQuery->where('service_next_date', '<=', Carbon::now()->endOfMonth()->format('Y-m-d'));
                break;
            case 'n-month':
                $serviceQuery->where('service_next_date', '>=',
                    Carbon::now()->addMonth()->startOfMonth()->format('Y-m-d'));
                $serviceQuery->where('service_next_date', '<=',
                    Carbon::now()->addMonth()->endOfMonth()->format('Y-m-d'));
                break;
            case 'overdue':
                $serviceQuery->where('service_next_date', '<', Carbon::now()->format('Y-m-d'));
                break;
            case 'all':
            default:
                break;
        }

        /** @var LengthAwarePaginator $parts */
        $serviceQuery->orderBy(...$sort)
            ->orderBy('service_id', 'desc');
        return $serviceQuery;
    }

    public function ajax_get_services()
    {
        $page = request('page', 1);

        $serviceQuery = $this->_getListQuery();

        if (request()->has('noPaginate') && request('noPaginate') == "true") {
            $services = $serviceQuery->get();
            return $this->successResponse(['data' => $services->toArray()]);
        } else {
            $services = $serviceQuery->paginate(30, ['*'], 'page', $page);
            return $this->successResponse($services->toArray());
        }
        return false;
    }

    public function ajax_get_service_types()
    {
        $page = request('page', 1);
        $sort = request('sort', ['service_type_name', 'asc']);
        /** @var Builder $typesQuery */
        $typesQuery = EquipmentServiceType::query();
        if (!empty(request('filter', ""))) {
            $typesQuery->where('service_type_name', 'like', '%' . request('filter') . '%');
        }
        /** @var LengthAwarePaginator $types */
        $types = $typesQuery->orderBy(...$sort)
            ->orderBy('service_type_id', 'desc')
            ->paginate(20, ['*'], 'page', $page);
        return $this->successResponse($types->toArray());
    }

    public function ajax_get_service()
    {
        $id = request('service_id', false);
        if (!$id) {
            return $this->errorResponse('Wrong Input parameters');
        }
        if (!$service = EquipmentService::with(['user', 'equipment', 'service_type'])->find($id)) {
            return $this->errorResponse('Service not found');
        }
        return $this->successResponse($service->toArray());
    }

    public function ajax_create_service()
    {
        try {
            /** @var EquipmentServiceCreateUpdateRequest $request */
            $request = app(EquipmentServiceCreateUpdateRequest::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(null, $e->validator->errors());
        }
        $serviceType = EquipmentServiceType::find($request->get('service_type_id'));
        foreach ($request->get('eq_id') as $eq_id) {
            $eq = Equipment::find($eq_id);
            EquipmentService::create(array_merge($request->validated(), [
                'eq_id' => $eq_id,
                'service_name' => $serviceType->service_type_name,
                'service_next_counter' => !is_null($request->get('service_counter_period'))
                    ? $eq->last_counter + $request->get('service_counter_period')
                    : null,
            ]));
        }

        return $this->successResponse();
    }

    public function ajax_update_service()
    {
        try {
            /** @var EquipmentServiceCreateUpdateRequest $request */
            $request = app(EquipmentServiceCreateUpdateRequest::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(null, $e->validator->errors());
        }
        /** @var EquipmentService $service */
        $service = EquipmentService::with(['equipment'])->find($request->get('service_id'));
        $validated = $request->validated();
        if ($request->get('service_counter_period') !== (int)$service->service_counter_period) {
            $validated['service_next_counter'] = $request->get('service_counter_period') === null
                ? null
                : $service->equipment->last_counter + $request->get('service_counter_period');
        }
        if ($request->get('service_date_period') !== (int)$service->service_date_period
            || $request->get('service_start_date')->notEqualTo(Carbon::createFromFormat(getDateFormat(),
                $service->service_start_date)->startOfDay())) {
            $validated['service_next_date'] = $request->get('service_next_date');
        }
        if ($request->has('service_type_id')) {
            $serviceType = EquipmentServiceType::find($request->get('service_type_id'));
            $validated['service_name'] = $serviceType->service_type_name;
        }
        $service->fill($validated);
        $service->save();
        return $this->successResponse($service->toArray());
    }

    public function ajax_postpone_service()
    {
        if (!request('service_id', false)) {
            return $this->errorResponse('Postpone error');
        }
        /** @var EquipmentService $service */
        if (!$service = EquipmentService::with(['equipment'])->find(request('service_id'))) {
            return $this->errorResponse('Service not found');
        }
        if (request('postpone_date', false)) {
            $postponeDate = Carbon::createFromFormat(config_item('dateFormat'), request('postpone_date'))->startOfDay();
            $nextDate = Carbon::createFromFormat(config_item('dateFormat'), $service->service_next_date)->startOfDay();
            if ($nextDate->notEqualTo($postponeDate)) {
                $serviceReport = EquipmentServiceReport::create([
                    'eq_id' => $service->eq_id,
                    'service_type_id' => $service->service_type_id,
                    'service_id' => $service->service_id,
                    'service_report_type' => EquipmentServiceReport::TYPE_POSTPONED,
                    'service_report_postponed_to' => $postponeDate,
                    'service_report_note' => request('postpone_note'),
                ]);
                if (request()->has('postpone_counter') && !empty(request('postpone_counter'))) {
                    $serviceReport->counter()->updateOrCreate(
                        ['eq_id' => $service->eq_id],
                        ['counter_value' => (int)request('postpone_counter')]
                    );
                }

                $service->service_next_date = $postponeDate;
                $service->save();
            }
        }
        return $this->successResponse($service->toArray());
    }

    public function ajax_complete_service()
    {
        if (!request('service_id', false)) {
            return $this->errorResponse('Complete error');
        }
        /** @var EquipmentService $service */
        if (!$service = EquipmentService::with(['equipment'])->find(request('service_id'))) {
            return $this->errorResponse('Service not found');
        }
        $reportEndDate = Carbon::createFromFormat(config_item('dateFormat'),
            request('service_report_end_date'))->startOfDay();
        $serviceReport = EquipmentServiceReport::create([
            'eq_id' => $service->eq_id,
            'service_type_id' => $service->service_type_id,
            'service_id' => $service->service_id,
            'service_report_type' => EquipmentServiceReport::TYPE_COMPLETED,
            'service_report_postponed_to' => null,
            'service_report_note' => request('service_report_note'),
            'service_report_end_date' => $reportEndDate
        ]);
        if (request()->has('tmp_files')) {
            foreach (request('tmp_files') as $tmpFile) {
                $fileName = pathinfo($tmpFile, PATHINFO_BASENAME);
                $filePath = 'uploads/equipment/' . $serviceReport->eq_id . '/';
                Storage::move($tmpFile, $filePath . $fileName);
                $serviceReport->files()->create([
                    'eq_id' => $serviceReport->eq_id,
                    'file_name' => $fileName,
                    'file_size' => Storage::size($filePath . $fileName),
                    'file_mime' => MimeType::from($fileName)
                ]);
            }
        }
        if (request()->has('parts')) {
            $partFiles = [];
            if (request()->hasFile('part_files')) {
                $partFiles = request()->file('part_files');
            }
            foreach (request('parts') as $key => $partData) {
                $partData['eq_id'] = $serviceReport->eq_id;
                if (empty($partData['part_tax_name'])) {
                    $partData['part_tax_name'] = null;
                }
                //$partData['part_purchased_date'] = $serviceReport->service_report_end_date; //@todo Implement part_purchased_date
                /** @var EquipmentPart $part */
                $part = $serviceReport->parts()->create($partData);
                if (array_key_exists($key, $partFiles) && !empty($partFiles[$key])) {
                    $file = $partFiles[$key];
//                    $name = time() . $file->getClientOriginalName();
//                    $filePath = 'uploads/equipment/' . $service->eq_id . '/';
//                    Storage::put($filePath . $name, file_get_contents($file));
                    $part->files()->create([
                        'eq_id' => $serviceReport->eq_id,
                        'part_id' => $part->part_id,
                        'file_name' => $file,
                        'file_created_at' => $part->part_created_at,
                    ]);
                }
            }
        }
        if (request()->has('employees')) {
            foreach (request('employees') as $key => $empData) {
                $serviceReport->employees()->create($empData);
            }
        }
        if (request()->has('counter') && !empty(request('counter'))) {
            $serviceReport->counter()->create([
                'eq_id' => $serviceReport->eq_id,
                'counter_value' => (int)request('counter'),
                'counter_date' => $serviceReport->service_report_end_date
            ]);
        }
        if ($service->service_date_period !== null) {
            switch ((int)$service->service_date_period_type) {
                case EquipmentService::DATE_PERIOD_TYPE_DAY:
                    $service->service_next_date = $reportEndDate->addDays((int)$service->service_date_period);
                    break;
                case EquipmentService::DATE_PERIOD_TYPE_WEEK:
                    $service->service_next_date = $reportEndDate->addWeeks((int)$service->service_date_period);
                    break;
                case EquipmentService::DATE_PERIOD_TYPE_YEAR:
                    $service->service_next_date = $reportEndDate->addYears((int)$service->service_date_period);
                    break;
                case EquipmentService::DATE_PERIOD_TYPE_MONTH:
                default:
                    $service->service_next_date = $reportEndDate->addMonths((int)$service->service_date_period);
                    break;
            }
        }
        if ($service->service_counter_period !== null) {
            $service->equipment->refreshWithScopes();
            $service->service_next_counter = (int)$service->equipment->last_counter + (int)$service->service_counter_period;
        }
        $service->save();
        $serviceReport->refresh();
        $serviceReport->load([
            'equipment',
            'user',
            'service_type',
            'service',
            'notes',
            'parts',
            'employees',
            'employees.user',
            'files',
            'counter'
        ]);
        return $this->successResponse($serviceReport->toArray());
    }

    public function ajax_delete_service()
    {
        if (!request('service_id', false)) {
            return $this->errorResponse('Delete error');
        }
        if (EquipmentService::destroy(request('service_id')) === 0) {
            return $this->errorResponse('Delete error');
        }
        return $this->successResponse();
    }

    public function ajax_file_upload()
    {
        if (!request('id', false)) {
            if (!request()->hasFile('file')) {
                return $this->errorResponse('Service Report error');
            }
            $fileBlob = request()->file('file');
            $file = new stdClass;
            $file->file_name = EquipmentFile::prepareName($fileBlob->getClientOriginalName());
            $filePath = 'uploads/tmp/equipment/';
            $file->file_mime = $fileBlob->getMimeType();
            $file->file_size = $fileBlob->getSize();
            Storage::put($filePath . $file->file_name, file_get_contents($fileBlob));
            $file->file_url = Storage::url($filePath . $file->file_name);
            $file->file_id = $filePath . $file->file_name;
        } else {
            /** @var EquipmentServiceReport $report */
            if (!$report = EquipmentServiceReport::with(['files'])->find(request('id'))) {
                return $this->errorResponse('Service Report not found');
            }
            $file = $report->files()->create([
                'eq_id' => $report->eq_id,
                'file_name' => request()->file('file')
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
        return $this->successResponse([
            'initialPreview' => $initialPreview,
            'initialPreviewConfig' => [$initialPreviewConfig],
            'append' => true
        ]);
    }

    public function ajax_file_delete()
    {
        if (!request('key', false)) {
            return $this->errorResponse('Service Report error');
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