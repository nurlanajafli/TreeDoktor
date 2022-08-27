<?php

class Migration_add_globalsearch_fulltext_indexes extends CI_Migration {

    public function up() {
        $this->db->query('CREATE FULLTEXT INDEX search ON clients(client_name, client_address, client_city, client_country, client_state, client_zip)');
        $this->db->query('CREATE FULLTEXT INDEX search ON clients_contacts(cc_name, cc_email, cc_phone)');
        $this->db->query('CREATE FULLTEXT INDEX search ON leads(lead_no,lead_body,lead_address,lead_city,lead_country,lead_state,lead_zip)');
    }

    public function down() {
        $this->db->query('ALTER TABLE clients DROP INDEX search;');
        $this->db->query('ALTER TABLE clients_contacts DROP INDEX search;');
        $this->db->query('ALTER TABLE leads DROP INDEX search;');
    }

}
