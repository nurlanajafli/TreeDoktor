<?php

use application\modules\equipment\models\EquipmentFile;
use application\modules\equipment\models\EquipmentNote;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

class EquipmentNotes extends MX_Controller
{
    function __construct()
    {
        parent::__construct();

        if (!isUserLoggedIn()) {
            redirect('login');
        }
    }

    public function ajax_get_notes()
    {
        /** @var Builder $eqQuery */
        $noteQuery = EquipmentNote::query()->with(['user', 'replies', 'replies.user', 'replies.files', 'files'])
            ->whereNull('note_parent_id')
            ->orderByDesc('note_created_at');
        if (request('where', false)) {
            $noteQuery->where(request('where'));
        }
        if (request('type', false) && request('type') !== "false") {
            $noteQuery->where('note_type', '=', request('type'));
        }
        if (request('for', false) && request('for') !== "false") {
            if (request('for') === 'eq_id') {
                $noteQuery->whereNull(['repair_id', 'service_report_id']);
            } else {
                $noteQuery->whereNotNull(request('for'));
            }
        }
        $notes = $noteQuery->get();
        return $this->successResponse(['notes' => $notes->toArray()]);
    }

    public function ajax_get_note()
    {
        $id = request('note_id', false);
        if (!$id) {
            return $this->errorResponse('Wrong Input parameters');
        }
        if (!$part = EquipmentNote::find($id)) {
            return $this->errorResponse('Note not found');
        }
        return $this->successResponse($part->toArray());
    }

    public function ajax_create_note()
    {
        if (!request('note_description', false)) {
            return $this->errorResponse(null, ['note_description' => 'Note is required']);
        }
        $all = request()->all();
        if (empty($all['note_created_at'])) {
            $all['note_created_at'] = Carbon::now();
        }
        $note = EquipmentNote::create($all);
        if (request()->hasFile('file')) {
            $file = request()->file('file');
//            $name = time() . $file->getClientOriginalName();
//            $filePath = 'uploads/equipments/' . $note->eq_id . '/';
//            Storage::put($filePath . $name, file_get_contents($file));
            EquipmentFile::create([
                'user_id' => $note->user_id,
                'eq_id' => $note->eq_id,
                'note_id' => $note->note_id,
                'file_name' => $file,
                'file_created_at' => $note->note_created_at,
            ]);
            $note->load(['files']);
        }
        return $this->successResponse($note->toArray());
    }

    public function ajax_update_note()
    {
        if (!request('note_id', false)) {
            return $this->errorResponse('Update error');
        }
        if (!$note = EquipmentNote::with(['files'])->find(request('note_id'))) {
            return $this->errorResponse('Note not found');
        }
        $all = request()->all();
        foreach ($note->files as $file) {
            /** @var EquipmentFile $file */
            if (!in_array($file->file_id, $all['files'])) {
                $file->delete();
            }
        }
        $note->fill($all);
        if (request()->hasFile('file')) {
            $file = request()->file('file');
//            $name = time() . $file->getClientOriginalName();
//            $filePath = 'uploads/equipment/' . $note->eq_id . '/';
//            Storage::put($filePath . $name, file_get_contents($file));
            EquipmentFile::create([
                'user_id' => $note->user_id,
                'eq_id' => $note->eq_id,
                'note_id' => $note->note_id,
                'file_name' => $file,
                'file_created_at' => $note->note_created_at,
            ]);
            $note->load(['files']);
        }
        $note->save();
        return $this->successResponse($note->toArray());
    }

    public function ajax_delete_note()
    {
        if (!request('note_id', false)) {
            return $this->errorResponse('Delete error');
        }
        if (EquipmentNote::destroy(request('note_id')) === 0) {
            return $this->errorResponse('Delete error');
        }
        return $this->successResponse();
    }
}