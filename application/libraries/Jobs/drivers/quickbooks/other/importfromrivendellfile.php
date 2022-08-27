<?php


class importfromrivendellfile extends CI_Driver implements JobsInterface
{
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();

        $this->CI->load->library('Googlemaps');
    }

    public function getPayload($data = NULL)
    {
        return $data;
    }

    public function execute($job = NULL)
    {
        $file = bucket_read_file('uploads/import_files/importClients.csv');
//        $file = bucket_read_file('uploads/import_files/export_client.csv');
        file_put_contents(storage_path('file.csv'), $file);
        $result[] = [
            'client_name',
            'client_address',
            'client_city',
            'client_state',
            'client_zip',
            'client_country',
            'client_lng',
            'client_lat',
            'emails',
            'phone',
            'mobile',
            'last_update'
        ];
        if (($open = fopen(storage_path('file.csv'), "r")) !== FALSE)
        {

            $key = 0;

            while (($data = fgetcsv($open, 1000, ",")) !== FALSE)
            {
//                $array[] = $data;
//                continue;

                $key++;
//                debug2($key);
                if($key == 1){
                    $clientName = array_search('BillingName', $data);
                    $address = array_search('BillingAddress', $data);
                    $shipAddress = array_search('ShippingAddress', $data);
                    $email = array_search('Email', $data);
                    $phone = array_search('Phone', $data);
                    $mobile = array_search('Mobile', $data);
                    $date = array_search('LastUpdated', $data);
//                    debug2($clientName); debug2($mobile); die;
                } elseif($key == 10){
//                    debug2($result);
//                    break;
                } else {
                    if(empty($data) || empty($data[$clientName])) {
//                        debug2($key);
//                        debug2($data);
                        continue;
                    }

                    $geocode = null;
                    if(!empty($data[$address]))
                        $geocode = $this->CI->googlemaps->get_lat_long_from_address($data[$address], 0, true);
                    elseif(!empty($data[$shipAddress]))
                        $geocode = $this->CI->googlemaps->get_lat_long_from_address($data[$shipAddress],  0, true);

                    $clientAddress = '';
                    $clientCity= '';
                    $clientState = '';
                    $clientZip = '';
                    $clientCountry = '';
                    $clientLng= '';
                    $clientLat = '';

                    if(!empty($geocode) && is_object($geocode) && $geocode->status == "OK"){
//                        $formattedAddress = $geocode->results[0]->formatted_address;
                        $addressComponents = $geocode->results[0]->address_components;
//                        $addressArr = explode(',', $formattedAddress);
//                        $stateCodeArr = explode(' ', trim($addressArr[2]));
                        $street_number = '';
                        $street = '';
                        $city = '';
                        $state = '';
                        $zip = '';
                        $country = '';
                        foreach ($addressComponents as $component){
                            if($component->types[0] == 'street_number')
                                $street_number = $component->short_name;
                            elseif($component->types[0] == 'route')
                                $street = $component->long_name;
                            elseif($component->types[0] == 'locality')
                                $city = $component->short_name;
                            elseif($component->types[0] == 'administrative_area_level_1')
                                $state = $component->short_name;
                            elseif($component->types[0] == 'postal_code')
                                $zip = $component->short_name;
                            elseif($component->types[0] == 'country')
                                $country = $component->short_name;
                        }
                        $clientAddress = $street_number. ' ' . $street;
                        $clientCity = $city;
                        $clientState = $state;
                        $clientZip = $zip;
                        $clientCountry = $country;
                        $clientLat = $geocode->results[0]->geometry->location->lat;
                        $clientLng = $geocode->results[0]->geometry->location->lng;
                    }


                    $result[] = [
                        trim($data[$clientName]),
                        $clientAddress,
                        $clientCity,
                        $clientState,
                        $clientZip,
                        $clientCountry,
                        $clientLng,
                        $clientLat,
                        $data[$email],
                        $data[$phone],
                        $data[$mobile],
                        $data[$date]
                    ];
                }


//                $array[] = $data;
            }

            fclose($open);
        }
//debug2($array); die;
        if(!empty($result)){
            $path = '/tmp/';
            $fileName = 'export_client.csv';
            $file = fopen($path.$fileName, 'w+');
            foreach ($result as $fields) {
                if(is_array($fields))
                    fputcsv($file, $fields, ",");
            }
            bucket_write_file('uploads/import_files/' . $fileName , $file);
            fclose($file);

//            $this->successResponse(['link' => "uploads/qb/export/" . $fileName, 'name' => $fileName], "uploads/qb/export/" . $fileName);
//            return;
        }
        return true;
    }

}