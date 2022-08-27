<?php

use application\modules\equipment\models\EquipmentFile;
use application\modules\equipment\models\EquipmentPart;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class EquipmentParts extends MX_Controller
{
    function __construct()
    {
        parent::__construct();

        if (!isUserLoggedIn()) {
            redirect('login');
        }
        $this->_title = SITE_NAME;
    }

    public function ajax_get_parts()
    {
        $page = request('page', 1);
        $sort = request('sort', ['part_created_at', 'desc']);
        /** @var Builder $partQuery */
        $partQuery = EquipmentPart::with(['user', 'files']);
        if (request('where', false)) {
            $partQuery->where(request('where'));
        }
        if (request('filter', false)) {
            $partQuery->where(request('filter'));
        }
        if (request('query', false)) {
            $partQuery->where('part_name', 'like', '%' . request('query') . '%');
        }

        /** @var LengthAwarePaginator $parts */
        $parts = $partQuery->orderBy(...$sort)
            ->orderBy('part_id', 'desc')
            ->paginate(30, ['*'], 'page', $page);
        return $this->successResponse($parts->toArray());
    }

    public function ajax_get_part()
    {
        $id = request('part_id', false);
        if (!$id) {
            return $this->errorResponse('Wrong Input parameters');
        }
        if (!$part = EquipmentPart::with(['user', 'files'])->find($id)) {
            return $this->errorResponse('Part not found');
        }
        return $this->successResponse($part->toArray());
    }

    public function ajax_create_part()
    {
        if (!request('part_name', false)) {
            return $this->errorResponse(null, ['part_name' => 'Part Name is required']);
        }
        if (!request('eq_id', false)) {
            return $this->errorResponse('Wrong Input parameters');
        }
        $all = request()->all();
        if (empty($all['part_created_at'])) {
            $all['part_created_at'] = Carbon::now();
        }
        $part = EquipmentPart::create($all);
        if (request()->hasFile('file')) {
            $file = request()->file('file');
//            $name = time() . $file->getClientOriginalName();
//            $filePath = 'uploads/equipments/' . $part->eq_id . '/';
//            Storage::put($filePath . $name, file_get_contents($file));
            EquipmentFile::create([
                'user_id' => $part->user_id,
                'eq_id' => $part->eq_id,
                'part_id' => $part->part_id,
                'file_name' => $file,
                'file_created_at' => $part->part_created_at,
            ]);
            $part->load(['files']);
        }
        return $this->successResponse($part->toArray());
    }

    public function ajax_update_part()
    {
        if (!request('part_id', false)) {
            return $this->errorResponse('Update error');
        }
        if (!$part = EquipmentPart::with(['files'])->find(request('part_id'))) {
            return $this->errorResponse('Part not found');
        }
        $all = request()->all();
        foreach ($part->files as $file) {
            /** @var EquipmentFile $file */
            if (!in_array($file->file_id, request()->get('files', []))) {
                $file->delete();
            }
        }
        $part->fill($all);
        if (request()->hasFile('file')) {
            $file = request()->file('file');
//            $name = time() . $file->getClientOriginalName();
//            $filePath = 'uploads/equipments/' . $part->eq_id . '/';
//            Storage::put($filePath . $name, file_get_contents($file));
            EquipmentFile::create([
                'user_id' => $part->user_id,
                'eq_id' => $part->eq_id,
                'part_id' => $part->part_id,
                'file_name' => $file,
                'file_created_at' => $part->part_created_at,
            ]);
            $part->load(['files']);
        }
        $part->save();
        return $this->successResponse($part->toArray());
    }

    public function ajax_delete_part()
    {
        if (!request('part_id', false)) {
            return $this->errorResponse('Delete error');
        }
        if (EquipmentPart::destroy(request('part_id')) === 0) {
            return $this->errorResponse('Delete error');
        }
        return $this->successResponse();
    }
}