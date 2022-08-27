<?php


class QBS3FileActions
{
    private $uploadPath = 'uploads/qb/import/';
    private $allowedTypes = 'iif|IIF';
    private $overwrite = true;
    private $CI;
    private $file;
    private $uploadConfig;
    private $filePath;
    private $uploadError;
    public $fileName;

    public function __construct(array $file = [])
    {
        $this->CI = &get_instance();
        $this->CI->load->library('upload');
        if(!empty($file)) {
            $this->file = $file;
            if(!empty($file['name'])) {
                $this->setFileName($file['name']);
                $this->setUploadConfig();
                $this->setFilePath();
            }
        }
    }

    public function upload():bool {
        if(!$this->isName())
            return false;
        if(!$this->isUploadConfig())
            return false;
        $this->CI->upload->initialize($this->uploadConfig);
        if (!$this->CI->upload->do_upload('file')) {
            $this->uploadError = $this->CI->upload->display_errors();
            return false;
        }
        return true;
    }

    public function getFile(){
        if(!$this->isFilePath())
            return false;
        return bucket_read_file($this->filePath);
    }

    public function setFileName(string $name){
        $this->fileName = str_replace(" ", "_", $name);
        $this->setFilePath();
    }

    public function getUploadError(){
        return $this->uploadError;
    }

    private function setFilePath():bool {
        if(!$this->isName())
            return false;
        $this->filePath = $this->uploadPath . $this->fileName;
        return true;
    }

    private function setUploadConfig():bool {
        if(!$this->isName())
            return false;
        $this->uploadConfig = [
            'allowed_types' => $this->allowedTypes,
            'overwrite' => $this->overwrite,
            'upload_path' => $this->uploadPath,
            'file_name' => $this->fileName
        ];
        return true;
    }

    private function isName(){
        return !empty($this->fileName) ? true : false;
    }

    private function isUploadConfig(){
        return !empty($this->uploadConfig) ? true : false;
    }

    private function isFilePath(){
        return !empty($this->filePath) ? true : false;
    }

}