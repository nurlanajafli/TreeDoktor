<?php

class TreeInventorySchemeActions
{
    protected $CI;
    function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('upload');
    }

    public function uploadOverlay($clientId, $tisId, $file = false){
        $this->removeOverlays($clientId, $tisId);
        $ext = '.' . pathinfo($file['tis_overlay']['name'], PATHINFO_EXTENSION);
        $config['allowed_types'] = 'gif|jpg|jpeg|png|GIF|JPG|JPEG|PNG';
        $config['overwrite'] = TRUE;
        $config['file_name'] = 'overlay'.rand(1,1000).$ext;
        $path = tree_inventory_project_overlay_path($clientId, $tisId);
        $config['upload_path'] = $path;
        $this->CI->upload->initialize($config);

        if (!$this->CI->upload->do_upload('tis_overlay', false))
            return ['status' => false, 'errors' => ['file' => 'File is not valid']];

        return ['status' => true, 'path' => '/' . tree_inventory_project_overlay_path($clientId, $tisId, $config['file_name'])];
    }
    private function removeOverlays($clientId, $tisId){
        $path = tree_inventory_project_overlay_path($clientId, $tisId);
        $files = bucket_get_filenames($path);
        if(!empty($files) && is_array($files)){
            foreach ($files as $file)
                bucket_unlink($file);
        }

    }
}