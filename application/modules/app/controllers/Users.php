<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use application\modules\user\models\User;

class Users extends APP_Controller
{

	function __construct()
	{
		parent::__construct();
        $this->load->model('mdl_user');
	}

	function index()
	{
        $users = $this->mdl_user->get_user("users.id, users.firstname, users.lastname, users.picture, employees.emp_phone, employees.emp_field_estimator, employees.emp_feild_worker, user_email, users.color", [
            'users.active_status' => 'yes',
            'system_user' => 0
        ])->result();

        array_map(function(&$user) {
            if($user->picture)
                $user->picture = base_url(PICTURE_PATH) . $user->picture;
        }, $users);

        $response = array(
            'status' => TRUE,
            'data' => $users,
        );
        return $this->response($response);
	}

    function chat() {
        $users = $this->mdl_user->getChatUsersWithLastMessage($this->user->id);

        array_map(function(&$user) {
            if($user->picture)
                $user->picture = base_url(PICTURE_PATH) . $user->picture;
        }, $users);

        $response = array(
            'status' => TRUE,
            'data' => $users,
        );
        return $this->response($response);
    }

    public function sendAttachment()
    {
        if (isset($_FILES) && isset($_FILES[ 'chatAttachment' ])) {
            $from = $this->user->id;
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
                    $ext = strtolower(end($ext));

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

                        // get file type
                        array_filter($types, function ($item, $key) use ($ext, &$type) {
                            if (array_search($ext, $item) !== false) {
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

        $response = array(
            'status' => false,
            'message' => 'Please upload file!',
        );

        return $this->response($response, 400);
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

	public function picture() {
	    $dataUrl = request()->input('dataUrl');

        if(!$dataUrl) {
            return $this->response([
                'status' => false,
                'message' => 'File is required'
            ], 400);
        }

        $picture = str_replace('[removed]', '', $dataUrl);
        if($picture == $dataUrl)
            $picture = explode(',', $dataUrl)[1];

        $fileName = make_filename('png');
        $tmpPath = sys_get_temp_dir() . '/avatar_' . uniqid() . '.png';
        $path =  PICTURE_PATH . $fileName;

        $im = imagecreatefromstring(base64_decode($picture));
        imagepng($im, $tmpPath);
        imagedestroy($im);

        if(!$size = getimagesize($tmpPath)) {
            return $this->response([
                'status' => false,
                'message' => 'Incorrect file type'
            ], 400);
        }

        resizeImage($tmpPath, false, User::AVATAR_WIDTH, User::AVATAR_HEIGHT);

        $user = User::find($this->user->id);
        bucket_unlink(PICTURE_PATH . $user->picture);
        bucket_move($tmpPath, $path, ['ContentType' => 'image/png']);
        $user->picture = $fileName;
        $user->save();

        @unlink($tmpPath);

        return $this->response([
            'status' => true,
            'data' => ['picture' => $path]
        ]);
    }
}
