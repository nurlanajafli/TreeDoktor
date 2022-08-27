<?php

use application\modules\equipment\models\EquipmentFile;
use application\modules\equipment\models\EquipmentPart;
use application\modules\equipment\models\EquipmentServiceReport;
use application\modules\equipment\models\EquipmentServiceType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Testing\MimeType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class EquipmentServiceReports extends MX_Controller
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
        $data['title'] = $this->_title . " - Equipment Service Reports";
        $data['serviceTypes'] = EquipmentServiceType::all();
        $this->load->view('equipment/service_reports', $data);
    }// End Index

    public function pdf($id)
    {
        if (!$id) {
            show_404();
        }
        /** @var EquipmentServiceReport $report */
        if (!$report = EquipmentServiceReport::with([
            'user',
            'service' => function (BelongsTo $query) {
                $query->withoutGlobalScope('fields');
            },
            'service_type',
            'parts',
            'employees',
            'files',
            'equipment'
        ])->find($id)) {
            show_404();
        }
        $html = $this->load->view('equipment/equipment_service_report_pdf', ['report' => $report], true);
        $this->load->library('mpdf');
        $this->mpdf->setAutoTopMargin = "pad";
        $this->mpdf->WriteHTML($html);
        foreach ($report->files as $file) {
            if (Str::endsWith(strtolower($file->file_name), ['.pdf'])) {
                $this->mpdf->AddPage();
                $this->mpdf->Thumbnail($file->file_stream, 1, 50, 5, 5);
            }
        }
        $pdf = $this->mpdf->Output('service_report_' . $report->service_report_id . '.pdf', 'I');
        CI::$APP->output
            ->set_content_type('application/pdf; charset=utf-8')
            ->set_header('filename: service_report_' . $report->service_report_id . '.pdf')
            //->set_status_header(200)
            ->set_output($pdf)
            ->_display();
        exit();
    }

    public function ajax_get_reports()
    {
        $page = request('page', 1);
        $sort = request('sort', ['service_report_created_at', 'desc']);
        /** @var \Illuminate\Database\Query\Builder $partQuery */
        $reportQuery = EquipmentServiceReport::with([
            'user',
            'service' => function (BelongsTo $query) {
                $query->withoutGlobalScope('fields');
            },
            'service_type',
            'parts',
            'employees',
            'files',
            'equipment',
            'counter'
        ]);
        $reportQuery->whereHas('equipment');
        if (!empty(request('where'))) {
            $reportQuery->where(request('where'));
        }
        if (!empty(request('filter')) && is_array(request('filter'))) {
            foreach (request('filter') as $filter => $value) {
                if (!array_key_exists($filter, EquipmentServiceReport::COLUMNS)) {
                    continue;
                }
                if (in_array($filter, ['service_type_form'])) {
                    $reportQuery->whereHas('service_type', function ($query) use ($filter, $value) {
                        /** @var Builder $query */
                        return $query->where($filter, '=', $value);
                    });
                } else {
                    $reportQuery->where($filter, '=', $value);
                }
            }
        }
        /** @var LengthAwarePaginator $reports */
        $reports = $reportQuery->orderBy(...$sort)
            ->orderBy('service_report_id', 'desc')
            ->paginate(30, ['*'], 'page', $page);
        return $this->successResponse($reports->toArray());
    }

    public function ajax_get_report()
    {
        $id = request('service_report_id', false);
        if (!$id) {
            return $this->errorResponse('Wrong Input parameters');
        }
        if (!$report = EquipmentServiceReport::with([
            'user',
            'service_type',
            'service',
            'notes',
            'parts',
            'employees',
            'employees.user',
            'files',
            'equipment',
            'counter'
        ])->find($id)) {
            return $this->errorResponse('Service Report not found');
        }
        return $this->successResponse($report->toArray());
    }

    public function ajax_update_report()
    {
        if (!request('service_report_id', false)) {
            return $this->errorResponse('Service Report error');
        }
        /** @var EquipmentServiceReport $report */
        if (!$report = EquipmentServiceReport::with([
            'user',
            'service_type',
            'service',
            'notes',
            'parts',
            'employees',
            'files',
            'counter'
        ])->find(request('service_report_id'))) {
            return $this->errorResponse('Service Report not found');
        }

        $parts = $report->parts()->sync(request('parts', []), true, ['eq_id' => $report->eq_id]);
        $report->load('parts');
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
                $part = $report->parts->where('part_id', $id)->first();
                $part->files()->create([
                    'eq_id' => $part->eq_id,
                    'file_name' => $file
                ]);
            }
        }

        $report->employees()->sync(request('employees', []));

        //EquipmentFile::sync($report, request()->file('files', []), request('exist_files', []));

        if (request()->has('counter') && !empty(request('counter'))) {
            $report->counter()->updateOrCreate(
                ['eq_id' => $report->eq_id],
                ['counter_value' => (int)request('counter')]
            );
        }
        $all = request()->all();
        $report->fill($all);
        $report->save();
        $report->refresh();
        return $this->successResponse($report->toArray());
    }

    public function ajax_delete_report()
    {
        if (!request('service_report_id', false)) {
            return $this->errorResponse('Delete error');
        }
        if (EquipmentServiceReport::destroy(request('service_report_id')) === 0) {
            return $this->errorResponse('Delete error');
        }
        return $this->successResponse();
    }

    public function ajax_file_upload()
    {
        if (!request('id', false)) {
            return $this->errorResponse('Service Report error');
        }
        /** @var EquipmentServiceReport $report */
        if (!$report = EquipmentServiceReport::with(['files'])->find(request('id'))) {
            return $this->errorResponse('Service Report not found');
        }
        $file = $report->files()->create([
            'eq_id' => $report->eq_id,
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
        if (EquipmentFile::destroy(request('key')) === 0) {
            return $this->errorResponse('Delete error');
        }
        return $this->successResponse();
    }
}
