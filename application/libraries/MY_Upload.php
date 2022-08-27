<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use Aws\S3\S3Client;

class MY_Upload extends CI_Upload {

	public function __construct($props = array()) {
        parent::__construct();
	}

	public function do_upload($field = 'userfile', $watermark = true, $is_prefix = false)
	{

	    // Is $_FILES[$field] set? If not, no reason to continue.
		if ( ! isset($_FILES[$field]))
		{
			$this->set_error('upload_no_file_selected');
			return FALSE;
		}

		// Is the upload path valid?
		if ( ! $this->validate_upload_path())
		{
			// errors will already be set by validate_upload_path() so just return FALSE
			return FALSE;
		}
		// Was the file able to be uploaded? If not, determine the reason why.
		if ( ! is_uploaded_file($_FILES[$field]['tmp_name']))
		{
			$error = ( ! isset($_FILES[$field]['error'])) ? 4 : $_FILES[$field]['error'];

			switch($error)
			{
				case 1:	// UPLOAD_ERR_INI_SIZE
					$this->set_error('upload_file_exceeds_limit');
					break;
				case 2: // UPLOAD_ERR_FORM_SIZE
					$this->set_error('upload_file_exceeds_form_limit');
					break;
				case 3: // UPLOAD_ERR_PARTIAL
					$this->set_error('upload_file_partial');
					break;
				case 4: // UPLOAD_ERR_NO_FILE
					$this->set_error('upload_no_file_selected');
					break;
				case 6: // UPLOAD_ERR_NO_TMP_DIR
					$this->set_error('upload_no_temp_directory');
					break;
				case 7: // UPLOAD_ERR_CANT_WRITE
					$this->set_error('upload_unable_to_write_file');
					break;
				case 8: // UPLOAD_ERR_EXTENSION
					$this->set_error('upload_stopped_by_extension');
					break;
				default :   $this->set_error('upload_no_file_selected');
					break;
			}

			return FALSE;
		}


		// Set the uploaded data as class variables
		$this->file_temp = $_FILES[$field]['tmp_name'];
		$this->file_size = $_FILES[$field]['size'];
		$this->_file_mime_type($_FILES[$field]);
		$this->file_type = preg_replace("/^(.+?);.*$/", "\\1", $this->file_type);
		$this->file_type = strtolower(trim(stripslashes($this->file_type), '"'));
        if ($is_prefix) {
            $this->file_name = $this->_prep_filename($_FILES[$field]['name'], $field);
        } else {
            $this->file_name = $this->_prep_filename($_FILES[$field]['name']);
        }
		$this->file_ext	 = $this->get_extension($this->file_name);
		$this->client_name = $this->file_name;

		// Is the file type allowed to be uploaded?
		if ( ! $this->is_allowed_filetype())
		{
			$this->set_error('upload_invalid_filetype');
			return FALSE;
		}

		// if we're overriding, let's now make sure the new name and type is allowed
		if ($this->_file_name_override != '')
		{
			$this->file_name = $this->_prep_filename($this->_file_name_override);

			// If no extension was provided in the file_name config item, use the uploaded one
			if (strpos($this->_file_name_override, '.') === FALSE)
			{
				$this->file_name .= $this->file_ext;
			}

			// An extension was provided, lets have it!
			else
			{
				$this->file_ext	 = $this->get_extension($this->_file_name_override);
			}

			if ( ! $this->is_allowed_filetype(TRUE))
			{
				$this->set_error('upload_invalid_filetype');
				return FALSE;
			}
		}

		// Convert the file size to kilobytes
		if ($this->file_size > 0)
		{
			$this->file_size = round($this->file_size/1024, 2);
		}

		// Is the file size within the allowed maximum?
		if ( ! $this->is_allowed_filesize())
		{
			$this->set_error('upload_invalid_filesize');
			return FALSE;
		}

		// Are the image dimensions within the allowed size?
		// Note: This can fail if the server has an open_basdir restriction.
		if ( ! $this->is_allowed_dimensions())
		{
			$this->set_error('upload_invalid_dimensions');
			return FALSE;
		}

		// Sanitize the file name for security
        $this->file_name = $this->_CI->security->sanitize_filename($this->file_name);//replaced clean_file_name by RG 24.06.2020

		// Truncate the file name if it's too long
		if ($this->max_filename > 0)
		{
			$this->file_name = $this->limit_filename_length($this->file_name, $this->max_filename);
		}

		// Remove white spaces in the name
		if ($this->remove_spaces == TRUE)
		{
			$this->file_name = preg_replace("/\s+/", "_", $this->file_name);
		}

		/*
		 * Validate the file name
		 * This function appends an number onto the end of
		 * the file if one with the same name already exists.
		 * If it returns false there was a problem.
		 */
		$this->orig_name = $this->file_name;

		if ($this->overwrite == FALSE)
		{
			$this->file_name = $this->set_filename($this->upload_path, $this->file_name);

			if ($this->file_name === FALSE)
			{
				return FALSE;
			}
		}

		/*
		 * Run the file through the XSS hacking filter
		 * This helps prevent malicious code from being
		 * embedded within a file.  Scripts can easily
		 * be disguised as images or other file types.
		 */
		if ($this->xss_clean)
		{
			if ($this->do_xss_clean() === FALSE)
			{
				$this->set_error('upload_unable_to_write_file');
				return FALSE;
			}
		}

        if(@getimagesize($this->file_temp))
        {
            if ( ! @copy($this->file_temp, $this->file_temp . '_blank'))
            {
                if ( ! @move_uploaded_file($this->file_temp, $this->file_temp . '_blank'))
                {
                    $this->set_error('upload_destination_error');
                    return FALSE;
                }
            }
            resizeImage($this->file_temp . '_blank', $watermark);
            $this->file_temp .= '_blank';
        }

        bucket_move($this->file_temp, $this->upload_path.$this->file_name, ['ContentType' => $this->file_type]);

		/*
		 * Set the finalized image dimensions
		 * This sets the image width/height (assuming the
		 * file was an image).  We use this information
		 * in the "data" function.
		 */
		$this->set_image_properties($this->upload_path.$this->file_name);

		return TRUE;
	}

    /**
     * Set the file name
     *
     * This function takes a filename/path as input and looks for the
     * existence of a file with the same name. If found, it will append a
     * number to the end of the filename to avoid overwriting a pre-existing file.
     *
     * @param	string
     * @param	string
     * @return	string
     */
    public function set_filename($path, $filename)
    {
        if ($this->encrypt_name == TRUE)
        {
            mt_srand();
            $filename = md5(uniqid(mt_rand())).$this->file_ext;
        }

        if ( ! is_bucket_file($path.$filename))
        {
            return $filename;
        }

        $filename = str_replace($this->file_ext, '', $filename);

        $new_filename = '';
        for ($i = 1; $i < 100; $i++)
        {
            if ( ! is_bucket_file($path.$filename.$i.$this->file_ext))
            {
                $new_filename = $filename.$i.$this->file_ext;
                break;
            }
        }

        if ($new_filename == '')
        {
            $this->set_error('upload_bad_filename');
            return FALSE;
        }
        else
        {
            return $new_filename;
        }
    }

    public function set_image_properties($path = '')
    {
        /*if ( ! $this->is_image())
        {
            return;
        }

        if (function_exists('getimagesize'))
        {
            if (FALSE !== ($D = @getimagesize($path)))
            {
                $types = array(1 => 'gif', 2 => 'jpeg', 3 => 'png');

                $this->image_width		= $D['0'];
                $this->image_height		= $D['1'];
                $this->image_type		= ( ! isset($types[$D['2']])) ? 'unknown' : $types[$D['2']];
                $this->image_size_str	= $D['3'];  // string containing height and width
            }
        }*/
    }

    public function validate_upload_path()
    {
        if ($this->upload_path == '')
        {
            $this->set_error('upload_no_filepath');
            return FALSE;
        }

        if (function_exists('realpath') AND @realpath($this->upload_path) !== FALSE)
        {
            $this->upload_path = str_replace("\\", "/", $this->upload_path);
        }

        /*if ( ! @is_dir($this->upload_path))
        {
            $this->set_error('upload_no_filepath');
            return FALSE;
        }

        if ( ! is_really_writable($this->upload_path))
        {
            $this->set_error('upload_not_writable');
            return FALSE;
        }*/

        $this->upload_path = preg_replace("/(.+?)\/*$/", "\\1/",  $this->upload_path);
        return TRUE;
    }
}
// END Upload Class

/* End of file Upload.php */
/* Location: ./system/libraries/Upload.php */
