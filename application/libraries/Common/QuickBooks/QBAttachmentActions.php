<?php
require_once('QBBase.php');

use QuickBooksOnline\API\QueryFilter\QueryMessage;
use QuickBooksOnline\API\Data\IPPAttachable;
use QuickBooksOnline\API\Data\IPPAttachableRef;
use QuickBooksOnline\API\Data\IPPReferenceType;
use QuickBooksOnline\API\Data\IPPIntuitEntity;

class QBAttachmentActions extends QBBase
{
    protected $module = 'attachable';
    public $type;
    public $typeId;
    public $fileName = 'Arbostar.txt';
    public $fileType = 'text/plain';
    public $attachment;

    public function __construct($type = null, $typeId = null, $fileName = null, $fileTipe = null)
    {
        parent::__construct();
        if ($type)
            $this->type = $type;
        if ($typeId)
            $this->typeId = $typeId;
        if ($fileName)
            $this->fileName = $fileName;
        if ($fileTipe)
            $this->fileTipe = $fileTipe;
    }

    public function get($type = null, $id = null)
    {
        if ($type)
            $this->type = $type;
        if ($id)
            $this->typeId = $id;
        if (!$this->typeId || !$this->type || !$this->dataService)
            return FALSE;

        $oneQuery = new QueryMessage();
        $oneQuery->sql = "SELECT";
        $oneQuery->entity = "attachable";
        $oneQuery->whereClause = [
            "AttachableRef.EntityRef.Type = '$this->type'",
            "AttachableRef.EntityRef.value = '$this->typeId'",
            "FileName = 'Arbostar.txt'"
        ];
        $result = $this->customQuery($oneQuery);
        if ($result == 'refresh')
            return 'refresh';
        elseif (!$result)
            return FALSE;
        $this->attachment = $result[0];
        $client = new GuzzleHttp\Client();
        $res = $client->request('GET', $result[0]->TempDownloadUri);
        if (!$res)
            return FALSE;
        if ($res->getStatusCode() == 200)
            return json_decode($res->getBody());
        return FALSE;

    }

    public function create($data, $type = null, $typeId = null)
    {
        if ($type)
            $this->type = $type;
        if ($typeId)
            $this->typeId = $typeId;
        if (!$this->typeId || !$this->type || !$this->dataService)
            return FALSE;

        $entityRef = new IPPReferenceType([
            'value' => $this->typeId,
            'type' => $this->type
        ]);
        $attachableRef = new IPPAttachableRef([
            'EntityRef' => $entityRef,
            'FileName' => $this->fileName
        ]);
        $objAttachable = new IPPAttachable();
        $objAttachable->FileName = $this->fileName;
        $objAttachable->AttachableRef = $attachableRef;
        $resultObj = $this->dataService->Upload(
            json_encode($data),
            $objAttachable->FileName,
            $this->fileType,
            $objAttachable
        );
        $error = $this->checkError();
        if ($error)
            return 'refresh';
        if (isset($resultObj->Id)) {
            $this->attachment = $resultObj;
            return $resultObj;
        }
        return FALSE;
    }

    public function delete()
    {
        $queryString = "select Id from attachable where AttachableRef.EntityRef.Type = '$this->type' and AttachableRef.EntityRef.value = '$this->typeId' and FileName = '$this->fileName'";
        $entities = $this->dataService->Query($queryString);
        if (isset($entities[0]))
            $this->dataService->Delete($entities[0]);
        $error = $this->checkError();
        if ($error)
            return FALSE;
        return TRUE;
    }

}