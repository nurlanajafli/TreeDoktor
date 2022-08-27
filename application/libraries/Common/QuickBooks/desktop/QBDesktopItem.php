<?php


use application\modules\estimates\models\Service;

class QBDesktopItem
{
    private $id;
    private $name;
    private $description;
    private $qbId;
    private $categoryId;
    private $qbHeader = [
      '!INVITEM',
      'NAME',
      'REFNUM',
      'TIMESTAMP',
      'INVITEMTYPE',
      'DESC',
      'PURCHASEDESC',
      'ACCNT',
      'ASSETACCNT',
      'COGSACCNT',
      'QNTY',
      'VALUE',
      'PRICE',
      'COST',
      'TAXABLE',
      'SALESTAXCODE',
      'PAYMETH',
      'TAXVEND',
      'PREFVEND',
      'REORDERPOINT',
      'EXTRA',
      'CUSTFLD1',
      'CUSTFLD2',
      'CUSTFLD3',
      'CUSTFLD4',
      'CUSTFLD5',
      'DEP_TYPE',
      'ISPASSEDTHRU',
      'HIDDEN',
      'DELCOUNT',
      'USEID',
      'ISNEW',
      'PO_NUM',
      'SERIALNUM',
      'WARRANTY',
      'LOCATION',
      'VENDOR',
      'ASSETDESC',
      'SALEDATE',
      'SALEEXPENSE',
      'NOTES',
      'ASSETNUM',
      'COSTBASIS',
      'ACCUMDEPR',
      'UNRECBASIS',
      'PURCHASEDATE'
    ];
    public $qbEstimateHeader = [
        '!SPL',
        'SPLID',
        'TRNSTYPE',
        'DATE',
        'ACCNT',
        'NAME',
        'CLASS',
        'AMOUNT',
        'DOCNUM',
        'MEMO',
        'CLEAR',
        'QNTY',
        'PRICE',
        'INVITEM',
        'TAXABLE',
        'OTHER2',
        'YEARTODATE',
        'WAGEBASE'
    ];


    public function __construct($id = null, $name = '', $description = '', $qbId = null, $categoryId = null)
    {
        if(!empty($id))
            $this->id = $id;
        if(!empty($name))
            $this->name = $name;
        if(!empty($description))
            $this->description = $description;
        if(!empty($qbId))
            $this->qbId = $qbId;
        if(!empty($categoryId))
            $this->categoryId = $categoryId;
    }

    public function save(){
        if($this->isId())
            $this->update();
        else
            $this->create();
    }

    public function setItemFromIIFFileRow(array $header, array $row){
        $date = new DateTime();
        $timestamp = $date->getTimestamp();
        $nameKey = array_search('NAME', $header);
        $descriptionKey = array_search('DESC', $header);
        $this->qbId = $timestamp;
        $this->categoryId = 1;
        if($nameKey !== false && isset($row[$nameKey]))
            $this->name = $row[$nameKey];
        if($descriptionKey !== false && isset($row[$descriptionKey]))
            $this->description = $row[$descriptionKey];
    }

    public function getItemToQbDesktop(Service $item){
        if($item->is_bundle)
            return [];
        $service_name = $item->service_name;
        if(strlen($service_name) > 30)
            $service_name = substr($service_name, 0, 30);
        $description = str_replace(['\n', '&#13;', '&emsp;', '"'], ' ', json_encode($item->service_description)) ?? '';
        if(strlen($description) > 300)
            $description = substr($description, 0, 300);
        return [
            'INVITEM',
            $service_name,
            '',
            '',
            'SERV',
            $description,
            '',
            'Services',
            '',
            '',
            '',
            '',
            $item->cost ?? 0,
            $item->is_product ? $item->cost : 0,
            'Y',
            'Tax',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            0,
            'N',
            'N',
            0,
            'N',
            'Y',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            0,
            '',
            '',
            0,
            0,
            0,
            '',
            ''
        ];
    }

    public function getItemForQbDesktopInvoice(array $item, $date){
        $service_name = !empty($item['service']) && !empty($item['service']['service_name']) ? $item['service']['service_name'] : '';
        if(strlen($service_name) > 30)
            $service_name = substr($service_name, 0, 30);
        return [
            'SPL',
            '',
            'INVOICE',
            $date ?? '',
            'Services',
            '',
            '',
            $item['service_price'] * -1,
            '',
            str_replace(['\n', '&#13;', '&emsp;', '"'], ' ', json_encode( $item['service_description'])) ?? '',
            '',
            '',
            $item['service_price'],
            $service_name,
            !empty($item['non_taxable']) ? 'N' : 'Y',
            '',
            '',
            '',
            ''
        ];
    }

    public function getQbDesktopHeader(){
        return $this->qbHeader;
    }

    private function create(){
        $item = $this->getItemToDB();
        if(empty($item))
            return false;
        elseif($this->isName() && $this->checkItemInDbByName())
            return false;
        $newItem = Service::create($item);
        if(!empty($newItem) && !empty($newItem->service_id)){
            $this->id = $newItem->service_id;
            return true;
        }
        return false;
    }

    private function update(){
        if(!$this->isId())
            return false;
        return true;
    }

    private function isId(){
        return !empty($this->id);
    }

    private function isName(){
        return !empty($this->name);
    }

    private function isDescription(){
        return !empty($this->description);
    }

    private function isQBId(){
        return !empty($this->qbId);
    }

    private function isCategoryId(){
        return !empty($this->categoryId);
    }

    private function checkItemInDbByName():bool {
        if(!$this->isName())
            return false;
       $item = Service::where('service_name', $this->name)->get()->first();
       return !empty($item);
    }

    private function getItemToDB(){
        $item = [];
        if($this->isId())
            $item['service_id'] = $this->id;
        if($this->isName())
            $item['service_name'] = $this->name;
        if($this->isDescription())
            $item['service_description'] = $this->description;
        if($this->isQBId())
            $item['service_qb_id'] = $this->qbId;
        if($this->isCategoryId())
            $item['service_category_id'] = $this->categoryId;

        return $item;
    }
}