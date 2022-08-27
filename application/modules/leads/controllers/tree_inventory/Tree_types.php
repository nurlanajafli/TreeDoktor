<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


use application\modules\tree_inventory\models\TreeType;
use Illuminate\Validation\ValidationException;

class Tree_types extends MX_Controller
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
        $data['title'] = $this->_title . ' - Tree Types';

        $this->load->view('tree_inventory/tree_types', $data);
    }


    public function delete_tree()
    {
        $tree_id = request()->tree_id;

        try {
            $tree = TreeType::findOrFail($tree_id);

            if ($tree->delete()) {
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

    public function update_tree()
    {
        try {
            $validatedRequest = request()->validate([
                'trees_name_eng' => 'required',
                'trees_name_lat' => '',
            ],
                [
                    'trees_name_eng.required' => 'Tree\'s English Name required',
                    'trees_name_lat.required' => 'Tree\'s Latin  Name required'
                ]
            );
        } catch (ValidationException $e) {
            return $this->errorResponse(null, $e->validator->errors());
        }

        try {
            $tree = TreeType::findOrFail(request()->trees_id);
            $tree->trees_name_eng = trim($validatedRequest['trees_name_eng']);
            $tree->trees_name_lat = trim($validatedRequest['trees_name_lat']);

            if ($tree->save()) {
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

    public function create_tree()
    {
        try {
            $validatedRequest = request()->validate(
                [
                    'trees_name_eng' => 'required',
                    'trees_name_lat' => 'required',
                ],
                [
                    'trees_name_eng.required' => 'Tree\'s English Name required',
                    'trees_name_lat.required' => 'Tree\'s Latin  Name required'
                ]
            );

        } catch (ValidationException $e) {
            return $this->errorResponse(null, $e->validator->errors());
        }

        try {
            $tree = TreeType::create([
               'trees_name_eng' => $validatedRequest['trees_name_eng'],
               'trees_name_lat' => $validatedRequest['trees_name_lat']
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

    public function get_trees()
    {
        $request = request();

        $orderColumn = $request->columns[$request->order[0]['column']]['data'];
        $orderDir = $request->order[0]['dir'];
        $searchValue = $request->search['value'];
        $treesQuery = TreeType::orderBy($orderColumn, $orderDir);

        $totalTrees = $treesQuery->count();

        if (isset($searchValue) && $searchValue !== '') {
            $searchableColumns = TreeType::$datatableSearchableColumns;

            $treesQuery->where(function($query) use($searchableColumns, $searchValue) {
                foreach($searchableColumns as $column) {
                    $query->orWhere($column, 'like', "%$searchValue%");
                }
            });
        }
        $totalQueryTrees =  $treesQuery->count();
        $treesQuery->offset($request->start)->limit($request->length);
        $trees = $treesQuery->get();

        die(json_encode([
            'data' => $trees->toArray(),
            'recordsTotal' => $totalQueryTrees,
            'recordsFiltered' => $totalQueryTrees,
            'totalTrees' => $totalTrees,
        ]));
    }
}

