<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


use application\modules\tree_inventory\models\WorkType;
use Illuminate\Validation\ValidationException;

class Work_types extends MX_Controller
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
        $data['title'] = $this->_title . ' - Work Types';

        $this->load->view('tree_inventory/work_types', $data);
    }

    public function get_work_types()
    {
        $request = request();

        $orderColumn = $request->columns[$request->order[0]['column']]['data'];
        $orderDir = $request->order[0]['dir'];
        $searchValue = $request->search['value'];
        $workTypeQuery = WorkType::orderBy($orderColumn, $orderDir);

        $totalWorkTypes = $workTypeQuery->count();

        if (isset($searchValue) && $searchValue !== '') {
            $searchableColumns = WorkType::$datatableSearchableColumns;

            $workTypeQuery->where(function($query) use($searchableColumns, $searchValue) {
                foreach($searchableColumns as $column) {
                    $query->orWhere($column, 'like', "%$searchValue%");
                }
            });
        }
        $totalQueryTrees =  $workTypeQuery->count();
        $workTypeQuery->offset($request->start)->limit($request->length);
        $trees = $workTypeQuery->get();

        die(json_encode([
            'data' => $trees->toArray(),
            'recordsTotal' => $totalQueryTrees,
            'recordsFiltered' => $totalQueryTrees,
            'totalWorkTypes' => $totalWorkTypes,
        ]));
    }

    public function update_work_type()
    {
        try {
            $validatedRequest = request()->validate(
                [
                    'ip_name_short' => 'required',
                    'ip_name' => 'required',
                ],
                [
                    'ip_name_short.required' => 'Work type Short Name required',
                    'ip_name.required' => 'Work type Name required'
                ]
            );
        } catch (ValidationException $e) {
            return $this->errorResponse(null, $e->validator->errors());
        }

        try {
            $workType = WorkType::findOrFail(request()->ip_id);
            $workType->ip_name_short = $validatedRequest['ip_name_short'];
            $workType->ip_name = $validatedRequest['ip_name'];

            if ($workType->save()) {
                die(json_encode([
                    'status' => 'ok'
                ]));
            }
        } catch (\Throwable $e) {
            die(json_encode([
                'status' => 'Error'
            ]));
        }
    }

    public function create_work_type()
    {
        try {
            $validatedRequest = request()->validate(
                [
                    'ip_name_short' => 'required',
                    'ip_name' => 'required',
                ],
                [
                    'ip_name_short.required' => 'Work type Short Name required',
                    'ip_name.required' => 'Work type Name required'
                ]
            );

        } catch (ValidationException $e) {
            return $this->errorResponse(null, $e->validator->errors());
        }

        try {
            $tree = WorkType::create([
                'ip_name_short' => $validatedRequest['ip_name_short'],
                'ip_name' => $validatedRequest['ip_name']
            ]);

            die(json_encode([
                'status' => 'ok'
            ]));
        } catch (\Throwable $e) {
            die(json_encode([
                'status' => 'Error'
            ]));
        }
    }

    public function delete_work_type()
    {
        $workTypeIpId = request()->ip_id;

        try {
            $workType = WorkType::findOrFail($workTypeIpId);

            if ($workType->delete()) {
                die(json_encode([
                    'status' => 'ok'
                ]));
            }
        } catch (\Throwable $e) {
            die(json_encode([
                'status' => 'Error'
            ]));
        }
    }
}