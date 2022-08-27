<?php if (!defined('BASEPATH')) exit('No direct script access allowed.');

use application\modules\dashboard\models\Search;
class Appsearch extends APP_Controller
{

    private $entities = [
        'client'=>'Client', 
        'lead'=>'Lead', 
        'estimate'=>'Estimate', 
        'workorder'=>'Workorder', 
        'invoice'=>'Invoice'
    ];

    function __construct() {
        parent::__construct();
    }


    /*
        *******
        
        $_POST: string query, string entity
        
        ********
    */
    public function index()
    {
        $query = trim($this->input->post('query'));
        $entity = $this->input->post('entity');

        if(!$query)
            return $this->response(['status' => TRUE, 'data' => []]);
        
        if(!$entity)
        {
            $SearchModel = new Search();
            $results = $SearchModel->global_serach($query)->paginate(100)->items();
            return $this->response(['status' => TRUE, 'count' => count($results), 'data' => $results]);
        }
        
        if(!isset($this->entities[$entity]))
            return $this->response(['status' => TRUE, 'data' => []]);

        
        $class = "application\modules\\".$entity."s\models\\".$this->entities[$entity];
        $SearchModel = new $class();
        $results = $SearchModel->globalSearchQuery($query)->paginate(100)->items();
        return $this->response(['status' => TRUE, 'count' => count($results), 'data' => $results]);
    }
}