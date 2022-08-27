<?php

class Migration_start_data extends CI_Migration {

    public function __construct()
    {
        parent::__construct();
        $this->load->config('migration');
        $this->load->helper('file');
        $this->load->helper('directory');
    }

    public function up() {
        if(config_item('install_migrations_need')==FALSE)
            return true;

        $files = directory_map(config_item('migration_data_path'));
        if(empty($files))
            return false;
        
        foreach ($files as $key => $filename) {
            $file = config_item('migration_data_path').$filename;
            $sql = read_file($file);
            $this->SplitSQL($file,');');
        }
        
        return false;
    }

    public function down() {
        
        if(config_item('install_migrations_need')==FALSE)
            return true;

        $files = directory_map(config_item('migration_data_path'));
        if(empty($files))
            return false;
        
        foreach ($files as $key => $filename) {
            $table = basename($filename, ".sql");
            $this->db->query('TRUNCATE '.$table);
        }
        
        return true;
    }

    public function SplitSQL($file, $delimiter = ';')
    {
        set_time_limit(0);
        
        $filename = basename($file, '.sql');

        if (is_file($file) === true)
        {
            $file = fopen($file, 'r');
            if (is_resource($file) === true)
            {
                $query = array();
                while (feof($file) === false)
                {
                    $query[] = fgets($file);
                    if (preg_match('~' . preg_quote($delimiter, '~') . '\s*$~iS', end($query)) === 1)
                    {
                        $query = trim(implode('', $query));
                        //echo $query."\n";
                        //echo $this->db->count_all_results($filename)."\n";
                        
                        if($this->db->count_all_results($filename)==0){
                            //var_dump($query);
                            $this->db->query("SET sql_mode='NO_AUTO_VALUE_ON_ZERO';");
                            $this->db->query($query);
                        }
                        
                        while (ob_get_level() > 0)
                        {
                            ob_end_flush();
                        }

                        flush();

                    }

                    if (is_string($query) === true)
                    {
                        $query = array();
                    }
                }

                return fclose($file);
            }
        }

        return false;
    }

}