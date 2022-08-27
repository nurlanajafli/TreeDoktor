<?php

if (!function_exists('page_404')) {
    /**
     * 404 Page Handler
     *
     * This function is similar to the show_error() function above
     * However, instead of the standard error template it displays
     * 404 errors.
     *
     * @param string
     * @param bool
     * @return    void
     */
    function page_404($options = [])
    {
        $_error =& load_class('Exceptions', 'core');
        $_error->show_404('', true, $options);
        exit(4); // EXIT_UNKNOWN_FILE
    }
}

/*
function push_error_log($hash){
    $CI =& get_instance();

    $data = ['el_error_hash'=>$hash, 'el_cteated_time'=>time()];
    $CI->db->insert('error_logs', $data);
}




/*-----------------delete if not used----------14.07.2020*/
/*
function log_error( $num, $str, $file, $line, $context = null )
{
    die("log_error");
    log_exception( new ErrorException( $str, 0, $num, $file, $line ) );
}


function log_exception( Exception $e )
{
    die("log_exception");
    global $config;
   
    if ( $config["debug"] == true )
    {
        print "<div style='text-align: center;'>";
        print "<h2 style='color: rgb(190, 50, 50);'>Exception Occured:</h2>";
        print "<table style='width: 800px; display: inline-block;'>";
        print "<tr style='background-color:rgb(230,230,230);'><th style='width: 80px;'>Type</th><td>" . get_class( $e ) . "</td></tr>";
        print "<tr style='background-color:rgb(240,240,240);'><th>Message</th><td>{$e->getMessage()}</td></tr>";
        print "<tr style='background-color:rgb(230,230,230);'><th>File</th><td>{$e->getFile()}</td></tr>";
        print "<tr style='background-color:rgb(240,240,240);'><th>Line</th><td>{$e->getLine()}</td></tr>";
        print "</table></div>";
    }
    else
    {
        $message = "Type: " . get_class( $e ) . "; Message: {$e->getMessage()}; File: {$e->getFile()}; Line: {$e->getLine()};";
        file_put_contents( $config["app_dir"] . "/tmp/logs/exceptions.log", $message . PHP_EOL, FILE_APPEND );
        header( "Location: {$config["error_page"]}" );
    }
   
    exit();
}

function check_for_fatal()
{
    die("check_for_fatal");
    $error = error_get_last();
    if ( $error["type"] == E_ERROR )
        log_error( $error["type"], $error["message"], $error["file"], $error["line"] );
}
*/
/*
register_shutdown_function( "check_for_fatal" );
set_error_handler( "log_error" );
set_exception_handler( "log_exception" );
ini_set( "display_errors", "off" );
error_reporting( E_ALL );
*/

