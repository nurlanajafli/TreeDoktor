<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');


class MY_Output extends CI_Output
{

    protected $content_type;


    /**
     * Set Content Type Header
     *
     * @access	public
     * @param	string	extension of the file we're outputting
     * @return	void
     */
    function set_content_type($mime_type, $charset = null)
    {
        if (strpos($mime_type, '/') === FALSE)
        {
            $extension = ltrim($mime_type, '.');

            // Is this extension supported?
            if (isset($this->mime_types[$extension]))
            {
                $mime_type =& $this->mime_types[$extension];

                if (is_array($mime_type))
                {
                    $mime_type = current($mime_type);
                }
            }
        }

        $header = 'Content-Type: '.$mime_type;

        $this->headers[] = array($header, TRUE);
        $this->content_type = $mime_type;

        return $this;
    }

    /**
     * Get ContentType
     *
     * Returns the current ContentType string
     *
     * @access	public
     * @return	string
     */
    function get_content_type()
    {
        return $this->content_type;
    }

    function get_parsed_output()
    {
        if($this->content_type && $this->content_type == 'application/json')
            return json_decode($this->get_output(),true);
        return $this->get_output();
    }
}