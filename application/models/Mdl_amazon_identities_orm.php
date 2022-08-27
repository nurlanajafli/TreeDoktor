<?php

class Mdl_amazon_identities_orm extends JR_Model
{
    protected $_table = 'amazon_identities';
    protected $primary_key = 'identity_id';
    public $belongs_to = array('mdl_users_orm' => array('primary_key' => 'user_id', 'model' => 'mdl_users_orm'));

    public function __construct() {
        parent::__construct();
    }
}
