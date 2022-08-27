<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');


class Chat extends MX_Controller
{
	function __construct()
	{
		parent::__construct();
	}

    public function sendAttachment()
    {
        if (isset($_FILES) && isset($_FILES[ 'chatAttachment' ])) {
            $from = $this->session->userdata('user_id');
            $to = $_POST[ 'to' ];
            $data = [];

            $this->load->library('upload');

            $types = [
                'image' => ['gif', 'jpg', 'jpeg', 'png'],
                'doc' => ['doc', 'docx'],
                'excel' => ['xls', 'xlsx', 'csv'],
                'pdf' => ['pdf'],
            ];

            $config = array(
                'upload_path' => $this->getFilePath($from, $to),
                'allowed_types' => "*",
                'remove_spaces' => true,
                'overwrite' 	=> FALSE,
            );

            $filesCount = count($_FILES[ 'chatAttachment' ][ 'name' ]);

            for ($i = 0; $i < $filesCount; $i++) {
                if ($_FILES[ 'chatAttachment' ][ 'error' ][ $i ] === 0) {
                    $type = 'file';
                    $_FILES[ 'file' ] = [];
                    $_FILES[ 'file' ][ 'name' ] = $_FILES[ 'chatAttachment' ][ 'name' ][ $i ];
                    $_FILES[ 'file' ][ 'type' ] = $_FILES[ 'chatAttachment' ][ 'type' ][ $i ];
                    $_FILES[ 'file' ][ 'tmp_name' ] = $_FILES[ 'chatAttachment' ][ 'tmp_name' ][ $i ];
                    $_FILES[ 'file' ][ 'error' ] = $_FILES[ 'chatAttachment' ][ 'error' ][ $i ];
                    $_FILES[ 'file' ][ 'size' ] = $_FILES[ 'chatAttachment' ][ 'size' ][ $i ];

                    $ext = (explode(".", $_FILES[ 'chatAttachment' ][ "name" ][ $i ]));
                    $ext = end($ext);

                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('file')) {
                        $response = array(
                            'status' => false,
                            'message' => $this->upload->display_errors(),
                        );

                        return $this->response($response, 400);
                    } else {
                        $uploaded = $this->upload->data();

                        if (!is_bucket_file($uploaded[ 'full_path' ])) {
                            return $this->response(['status' => false, 'message' => 'Cannot upload to bucket'], 400);
                        }

                        // get file type (doc, pfd, excel or image)
                        array_filter($types, function ($item, $key) use ($ext, &$type) {
                            if (array_search(strtolower($ext), $item) !== false) {
                                $type = $key;
                            }
                        }, ARRAY_FILTER_USE_BOTH);

                        $data[] = array(
                            'from' => $from,
                            'to' => $to,
                            'message' => base_url($uploaded[ 'full_path' ]),
                            'type' => $type
                        );
                    }
                } else {
                    $response = array(
                        'status' => false,
                        'message' => 'File must be image, PDF, word or excel',
                    );

                    return $this->response($response, 400);
                }
            }

            $response = [
                'status' => true,
                'data' => $data
            ];

            return $this->response($response);
        }

        return $this->response([
            'status' => true,
            'data' => []
        ]);
    }

	private function getFilePath($from, $to)
	{
		if (!is_empty_bucket_dir('uploads/chat/' . $from . '/' . $to . '/'))
			return 'uploads/chat/' . $from . '/' . $to . '/';
		if (!is_empty_bucket_dir('uploads/chat/' . $to . '/' . $from . '/'))
			return 'uploads/chat/' . $to . '/' . $from . '/';

		// by default make directory like $from/$to
		return 'uploads/chat/' . $from . '/' . $to . '/';
	}
}
