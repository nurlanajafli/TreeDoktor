<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Paint extends APP_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('mdl_paint');
    }

    function source() {
        $source = $this->input->post('file');

        $sourceData = $this->mdl_paint->get_by(['paint_path' => $source]);

        if(!$sourceData)
            $sourceData['paint_path'] = $source;
        else
            $sourceData->paint_source_data = json_decode($sourceData->paint_source_data);

        return $this->response([
            'status' => TRUE,
            'data' => $sourceData
        ]);
    }

    function save() {
        $paintId = $this->input->post('paint_id');
        $fileUrl = $this->input->post('file_url');
        $obj = $this->input->post('objects') ?: [];

        if(!$fileUrl || !is_bucket_file($fileUrl)) {
            return $this->response([
                'status' => FALSE,
                'message' => 'Incorrect source path'
            ], 400);
        }

        $paintData = [];

        if(!$paintId) {
            $srcPath = 'uploads/paint/' . date('Y-m') . '/source_' . uniqid() . '.' . pathinfo($fileUrl, PATHINFO_EXTENSION);
            bucket_copy($fileUrl, $srcPath);

            $paintData = [
                'paint_path' => $fileUrl,
                'paint_source_path' => $srcPath,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        $this->load->library('upload');

        $config['upload_path'] = pathinfo($fileUrl, PATHINFO_DIRNAME) . '/';
        $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf|GIF|JPG|JPEG|PNG|PDF';
        $config['file_name'] = basename($fileUrl);

        $tmpBackup = 'uploads/tmp/' . uniqid() . '.' . pathinfo($fileUrl, PATHINFO_EXTENSION);
        bucket_copy($fileUrl, $tmpBackup);
        bucket_unlink($fileUrl);
        $this->upload->initialize($config);
        if ($this->upload->do_upload('file')) {
            bucket_unlink($tmpBackup);
            $uploadData = $this->upload->data();
            $image = [
                'filepath' => $fileUrl . '?' . time(),
                'filename' => $uploadData['file_name']
            ];
        } else {
            bucket_copy($tmpBackup, $fileUrl);
            return $this->response([
                'status' => FALSE,
                'message' => 'Upload Error',
                'error' => strip_tags($this->upload->display_errors())
            ], 400);
        }

        $paintData['paint_source_data'] = json_encode($obj);

        if(!$paintId) {
            $this->mdl_paint->insert($paintData);
        } else {
            $paintData['updated_at'] = date('Y-m-d H:i:s');
            $this->mdl_paint->update($paintId, $paintData);
        }

        return $this->response([
            'status' => TRUE,
            'data' => $image
        ]);
    }
}
