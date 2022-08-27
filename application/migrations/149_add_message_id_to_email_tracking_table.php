<?php

class Migration_add_message_id_to_email_tracking_table extends CI_Migration {

    public function up() {
        $fields = array(
            'trackMessageId' => array('type' => 'VARCHAR', 'constraint' => 225,  'null' => false, 'comment' => 'Api returns message-id under message->headers'),
            'emailMessageId' => array('type' => 'VARCHAR', 'constraint' => 225,  'null' => true, 'comment' => 'after email sent it returns messageId in _headers array, null if getting results from api'),
            'mailHash' => array('type' => 'VARCHAR', 'constraint' => 225,  'null' => false, 'comment' => 'Hash of emailMessageId+trackMessageId'),
        );
        $this->dbforge->add_column('email_tracking', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('email_tracking', 'trackMessageId');
        $this->dbforge->drop_column('email_tracking', 'emailMessageId');
        $this->dbforge->drop_column('email_tracking', 'mailHash');
    }
}
