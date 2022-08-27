<?php

class Tools extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        // can only be called from the command line
        if (!is_cli()) {
            exit('Direct access is not allowed. This is a command line tool, use the terminal');
        }
        $this->load->config('migration');
        $this->load->dbforge();
        $this->load->dbutil();
        // initiate faker
        $this->faker = Faker\Factory::create();
    }

    public function message($to = 'World')
    {
        echo "Hello {$to}!" . PHP_EOL;
    }

    public function help()
    {
        $result = "The following are the available command line interface commands\n\n";
        $result .= "php index.php tools migration \"file_name\"         Create new migration file\n";
        $result .= "php index.php tools migrate [\"version_number\"]    Run all migrations. The version number is optional.\n";
        $result .= "php index.php tools seeder \"file_name\"            Creates a new seed file.\n";
        $result .= "php index.php tools seed \"file_name\"              Run the specified seed file.\n";

        echo $result . PHP_EOL;
    }

    public function install()
    {
        $this->load->helper('file');
        $sql = read_file(config_item('migration_install_path'));

        $query = explode(';', $sql);
        array_pop($query);

        foreach ($query as $statement) {
            $statment = $statement . ";";
            $this->db->query($statement);
        }
    }

    public function uninstall()
    {
        $this->load->helper('file');
        $sql = read_file(config_item('migration_uninstall_path'));
        $query = explode(';', $sql);
        array_pop($query);

        foreach ($query as $statement) {
            $statment = $statement . ";";
            $this->db->query($statement);
        }
    }

    public function migration($name)
    {
        $this->make_migration_file($name);
    }

    public function migrate($version = null)
    {

        $this->load->library('migration');

        if ($version != null) {
            if ($this->migration->version($version) === false) {
                show_error($this->migration->error_string());
            } else {
                echo "Migrations run successfully" . PHP_EOL;
            }

            return;
        }

        if ($this->migration->latest() === false) {
            show_error($this->migration->error_string());
        } else {
            echo "Migrations run successfully" . PHP_EOL;
        }
    }

    public function seeder($name)
    {
        $this->make_seed_file($name);
    }

    public function seed($name)
    {
        $seeder = new Seeder();

        $seeder->call($name);
    }

    public function get_version()
    {
        $row = $this->db->get('migrations')->row();
        return $row ? $row->version : 0;
    }

    private function last_file()
    {
        $this->load->config('migration');
        $files = glob(rtrim(config_item('migration_path'), '/') . '/' . '*.php');
        if (!$files || empty($files)) {
            return str_pad(1, 3, '0', STR_PAD_LEFT);
        }

        $last_file = end($files);
        $name_array = explode('_', basename($last_file, '.php'));

        $result = (int)$name_array[0];
        $result++;

        return str_pad($result, 3, '0', STR_PAD_LEFT);
    }

    protected function make_migration_file($name)
    {

        $this->load->library('migration');
        $version = $this->last_file();
        $table_name = strtolower($name);
        $path = APPPATH . "migrations/$version" . "_" . "$name.php";
        $my_migration = fopen($path, "w") or die("Unable to create migration file!");
        $migration_template = "<?php

class Migration_$name extends CI_Migration {

    public function up() {
        \$this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            )
        ));
        \$this->dbforge->add_key('id', TRUE);
        \$this->dbforge->create_table('$table_name');
    }

    public function down() {
        \$this->dbforge->drop_table('$table_name',true);
    }

}";

        fwrite($my_migration, $migration_template);

        fclose($my_migration);

        echo "$path migration has successfully been created." . PHP_EOL;
    }

    protected function make_seed_file($name)
    {
        $path = APPPATH . "seeds/$name.php";

        $my_seed = fopen($path, "w") or die("Unable to create seed file!");

        $seed_template = "<?php

class $name extends Seeder {

    private \$table = 'users';

    public function run() {
        \$this->db->truncate(\$this->table);

        //seed records manually
        \$data = [
            'user_name' => 'admin',
            'password' => '9871'
        ];
        \$this->db->insert(\$this->table, \$data);

        //seed many records using faker
        \$limit = 33;
        echo \"seeding \$limit user accounts\";

        for (\$i = 0; \$i < \$limit; \$i++) {
            echo \".\";

            \$data = array(
                'user_name' => \$this->faker->unique()->userName,
                'password' => '1234',
            );

            \$this->db->insert(\$this->table, \$data);
        }

        echo PHP_EOL;
    }
}
";

        fwrite($my_seed, $seed_template);

        fclose($my_seed);

        echo "$path seeder has successfully been created." . PHP_EOL;
    }

}
