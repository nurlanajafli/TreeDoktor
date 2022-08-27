<?php


use application\modules\clients\models\Client;
use application\modules\clients\models\ClientsContact;

class QBDesktopClient
{
    const TIMESTAMP_LOGS = 1627803572;
    private $id;
    private $name;
    private $address;
    private $city;
    private $state;
    private $zip;
    private $country;
    private $source;
    private $type;
    private $qbId;
    private $dateCreate;
    private $contact = [];
    private $qbHeader = [
        '!CUST',
        'NAME',
        'REFNUM',
        'TIMESTAMP',
        'BADDR1',
        'BADDR2',
        'BADDR3',
        'BADDR4',
        'BADDR5',
        'SADDR1',
        'SADDR2',
        'SADDR3',
        'SADDR4',
        'SADDR5',
        'PHONE1',
        'PHONE2',
        'FAXNUM',
        'EMAIL',
        'NOTE',
        'CONT1',
        'CONT2',
        'CTYPE',
        'TERMS',
        'TAXABLE',
        'SALESTAXCODE',
        'LIMIT',
        'RESALENUM',
        'REP',
        'TAXITEM',
        'NOTEPAD',
        'SALUTATION',
        'COMPANYNAME',
        'FIRSTNAME',
        'MIDINIT',
        'LASTNAME',
        'CUSTFLD1',
        'CUSTFLD2',
        'CUSTFLD3',
        'CUSTFLD4',
        'CUSTFLD5',
        'CUSTFLD6',
        'CUSTFLD7',
        'CUSTFLD8',
        'CUSTFLD9',
        'CUSTFLD10',
        'CUSTFLD11',
        'CUSTFLD12',
        'CUSTFLD13',
        'CUSTFLD14',
        'CUSTFLD15',
        'JOBDESC',
        'JOBTYPE',
        'JOBSTATUS',
        'JOBSTART',
        'JOBPROJEND',
        'JOBEND',
        'HIDDEN',
        'DELCOUNT',
        'PRICELEVEL'
    ];

    public function __construct($id = null, $name = '', $address = '', $city = '', $state = '', $zip = '', $country = '', $source = '', $type = 1, $qbId = null, $dateCreate = '')
    {
        if (!empty($id))
            $this->id = $id;
        if (!empty($name))
            $this->name = $name;
        if (!empty($address))
            $this->address = $address;
        if (!empty($city))
            $this->city = $city;
        if (!empty($state))
            $this->state = $state;
        if (!empty($zip))
            $this->zip = $zip;
        if (!empty($country))
            $this->country = $country;
        if (!empty($source))
            $this->source = $source;
        if (!empty($type))
            $this->type = $type;
        if (!empty($qbId))
            $this->qbId = $qbId;
    }

    public function setClientFromIIFFileRow(array $header, array $row){
        $nameKey = array_search('BADDR1', $header);
        $secondNameKey = array_search('NAME', $header);
        $streetKey = array_search('BADDR2', $header);
        $addressKey = array_search('BADDR3', $header);
        $phoneKey = array_search('PHONE1', $header);
        $emailKey = array_search('EMAIL', $header);
        $dateCreateKey = array_search('TIMESTAMP', $header);
        $date = new DateTime();
        $timestamp = $date->getTimestamp();

        $this->qbId = $timestamp;
        $this->source = 'QuickBooks Desktop';
        $this->type = 1;
        $this->contact['cc_title'] = 'Contact #1';
        $this->contact['cc_print'] = '1';
        if($nameKey !== false && isset($row[$nameKey]) && !empty($row[$nameKey])) {
            $name = str_replace(['"'], '', $row[$nameKey]);
            $this->name = $name;
            $this->contact['cc_name'] = $name;
        }
        elseif($secondNameKey !== false && isset($row[$secondNameKey]) && !empty($row[$secondNameKey])) {
            $name = str_replace(['"'], '', $row[$secondNameKey]);
            $this->name = $name;
            $this->contact['cc_name'] = $name;
        }
        if(strripos($row[$streetKey], 'Email') === false && strripos($row[$streetKey], 'Ph:') === false && strripos($row[$streetKey], 'Fax:') === false && strripos($row[$streetKey], 'c/o') === false
            && strripos($row[$addressKey], 'Email') === false && strripos($row[$addressKey], 'Ph:') === false && strripos($row[$addressKey], 'Fax:') === false && strripos($row[$addressKey], 'c/o') === false) {
            if ($streetKey !== false && isset($row[$streetKey]))
                $this->address = $row[$streetKey];
            if ($addressKey !== false && isset($row[$addressKey])) {
                $address = $row[$addressKey];
                $address = explode(',', $address);
                if (is_array($address) && !empty($address)) {
                    if (!empty($address[0]))
                        $this->city = str_replace([',', '"'], '', $address[0]);
//                    if (!empty($address[1]))
//                        $this->state = $address[1];
//                    if (!empty($address[2]))
//                        $this->zip = str_replace([',', '"'], '', $address[2]);
                    if(!empty($address[1]) && count(explode(' ', trim($address[1]))) > 1){
                        $address = explode(' ', str_replace([',', '"'], '', trim($address[1])));
                        $this->state = $address[0];
                        $this->zip = $address[1];
                    }
                }
            }
        }
        if($phoneKey !== false && !empty($row[$phoneKey])){
            $phone = preg_replace('/[^0-9]/', '', $row[$phoneKey]);
            $this->contact['cc_phone'] = $phone;
            $this->contact['cc_phone_clean'] = $phone;
        }
        elseif (strripos($row[$streetKey], 'Ph:') !== false || strripos($row[$streetKey], 'Fax:') !== false){
            $phone = preg_replace('/[^0-9]/', '', $row[$streetKey]);
            $this->contact['cc_phone'] = $phone;
            $this->contact['cc_phone_clean'] = $phone;
        }
        elseif (strripos($row[$addressKey], 'Ph:') !== false || strripos($row[$addressKey], 'Fax:')){
            $phone = preg_replace('/[^0-9]/', '', $row[$addressKey]);
            $this->contact['cc_phone'] = $phone;
            $this->contact['cc_phone_clean'] = $phone;
        }
        if($emailKey !== false && !empty($row[$emailKey])){
            $this->contact['cc_email'] = $row[$emailKey];
        }
        elseif(strripos($row[$streetKey], 'Email') !== false){
            $this->contact['cc_email'] = trim(str_replace(['Email:', 'Email'], '', $row[$streetKey]));
        }
        elseif(strripos($row[$addressKey], 'Email') !== false){
            $this->contact['cc_email'] = trim(str_replace(['Email:', 'Email'], '', $row[$addressKey]));
        }
        if($dateCreateKey !== false && isset($row[$dateCreateKey])){
            $this->dateCreate = date('Y-m-d', $row[$dateCreateKey]);
        }
        if(strripos($row[$streetKey], 'c/o')  !== false){
            $this->contact['cc_title'] = trim(str_replace(['c/o:', 'c/o'], '', $row[$streetKey]));
        }
    }

    public function save(){
        if($this->isId())
            $this->update();
        else
            $this->create();
    }

    public function getClientToQbDesktop(Client $client){
        return [
            'CUST',
            $client->client_name,
            '',
            '',
            $client->client_name,
            $client->client_address,
            $client->client_city ? $client->client_city . ', ' . $client->client_state . ' ' . $client->client_zip : '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            !empty($client->primary_contact) ? $client->primary_contact->cc_phone : '',
            '',
            '',
            !empty($client->primary_contact) ? $client->primary_contact->cc_email : '',
            '',
            '',
            '',
            '',
            '',
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
            '',
            'N',
            0,
            ''
        ];
    }

    public function getQbDesktopHeader(){
        return $this->qbHeader;
    }

    public function getLogsDataForSelect2(){
        return Client::groupBy('client_qb_id')->where('client_qb_id', '>', self::TIMESTAMP_LOGS)
            ->get(['client_qb_id as id','client_qb_id as value', DB::raw("date_format(from_unixtime(client_qb_id), '". getFormatDhlDefaultDate() . " %l:%i %p') as text")])->toArray();
    }

    public function getLogsContent(string $timestamp){
        return Client::where('client_qb_id', $timestamp)->with([
            'invoices.payments' => function($query) use ($timestamp){
                $query->where('payment_qb_id', $timestamp);
            },
            'invoices' => function($query) use ($timestamp){
                $query->where('invoice_qb_id', $timestamp);
            }
        ])->get()->toArray();
    }

    private function create(){
        $client = $this->getClientToDB();
        if(empty($client) || !$this->isName())
            return false;
        elseif($this->isName() && $this->checkClientInDbByName($this->name))
            return false;
        $newClient = Client::create($client);
        if(!empty($newClient) && !empty($newClient->client_id)){
            $this->id = $newClient->client_id;
            if($this->isId() && $this->isContact()){
                $this->contact['cc_client_id'] = $this->id;
                ClientsContact::create($this->contact);
            }
            return true;
        }
        return false;
    }

    private function update(){

    }

    private function getClientToDB(){
        $client = [];
        if($this->isName())
            $client['client_name'] = $this->name;
        if($this->isAddress())
            $client['client_address'] = $this->address;
        if($this->isCity())
            $client['client_city'] = $this->city;
        if($this->isState())
            $client['client_state'] = $this->state;
        if($this->isZip())
            $client['client_zip'] = $this->zip;
        if($this->isCountry())
            $client['client_country'] = $this->country;
        if($this->isSource())
            $client['client_source'] = $this->source;
        if($this->isQbId())
            $client['client_qb_id'] = $this->qbId;
        if($this->isType())
            $client['client_type'] = $this->type;
        if($this->isDateCreate())
            $client['client_date_created'] = $this->dateCreate;

        if(!empty($client))
            $client['client_brand_id'] = default_brand();
        return $client;
    }

    private function checkClientInDbByName():bool{
        if(!$this->isName())
            return false;
        $client = Client::where('client_name', $this->name)->get()->first();
        return !empty($client);
    }

    private function isId()
    {
        return !empty($this->id);
    }

    private function isName()
    {
        return !empty($this->name);
    }

    private function isAddress()
    {
        return !empty($this->address);
    }

    private function isCity()
    {
        return !empty($this->city);
    }

    private function isState()
    {
        return !empty($this->state);
    }

    private function isZip()
    {
        return !empty($this->zip);
    }

    private function isCountry()
    {
        return !empty($this->country);
    }

    private function isSource()
    {
        return !empty($this->source);
    }

    private function isQbId()
    {
        return !empty($this->qbId);
    }

    private function isType()
    {
        return !empty($this->type);
    }

    private function isDateCreate(){
        return !empty($this->dateCreate);
    }

    private function isContact()
    {
        return !empty($this->contact) && is_array($this->contact) && !empty($this->contact['cc_name']);
    }
}