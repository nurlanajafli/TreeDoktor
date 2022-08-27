<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use Aws\S3\S3Client;

function bucketScanDir($path = './', $include_path = FALSE, $recursive = FALSE) {
    if(config_item('bucket_sub_folder') && config_item('bucket_sub_folder') !== '') {
        $path = config_item('bucket_sub_folder') . '/' . ($path !== './' ? $path : null);
    }

    $CI = & get_instance();
    $result = [];

    if(!isset($CI->S3Client)) {
        $CI->S3Client = new S3Client([
            'region' => AWS_REGION,
            'version' => AWS_VERSION,
            'credentials' => [
                'key' => AWS_KEY,
                'secret' => AWS_SECRET_KEY
            ],
        ]);
    }

    $path = ltrim(rtrim($path, '/'), './') . '/';

    $options = array(
        "Bucket" => S3_BUCKET_NAME,
        "Prefix" => $path,
    );

    if(!$recursive)
        $options["Delimiter"] = '/';

    $objects = $CI->S3Client->getIterator('ListObjects', $options);

    foreach ($objects as $object) {
        if(isset($object['Key']) && $object['Key'] && trim($path, DIRECTORY_SEPARATOR) != trim($object['Key'], DIRECTORY_SEPARATOR))
            $result[] = $include_path ? $object['Key'] : basename($object['Key']);
    }

    return $result;
}

function get_client_notes_files($path = NULL, $full_path = false) {
    if(!$path)
        return [];
    $result = [];
    $files = bucketScanDir($path, TRUE, TRUE);

    foreach ($files as $filePath) {
        
        $path_parts = explode('/', $filePath);
        
        if(!$full_path){
            $fileName = $path_parts[count($path_parts)-1];
        } else {
            $fileName = $filePath;
        }    
        
        $noteId = $path_parts[count($path_parts)-2];
        $result[$noteId][] = $fileName;
    }

    return $result;
}

function is_bucket_file($key = NULL) {

    if(config_item('bucket_sub_folder')) {
        $key = config_item('bucket_sub_folder') . '/' . $key;
    }

    $CI = & get_instance();

    if(!isset($CI->S3Client)) {
        $CI->S3Client = new S3Client([
            'region' => AWS_REGION,
            'version' => AWS_VERSION,
            'credentials' => [
                'key' => AWS_KEY,
                'secret' => AWS_SECRET_KEY
            ],
        ]);
    }

    $key = str_replace('//', '/', $key);
    try {
        return $CI->S3Client->doesObjectExist(S3_BUCKET_NAME, $key);
    } catch (Exception $e) {
        return false;
    }
}

function bucket_get_filenames($source_dir, $include_path = FALSE, $_recursion = FALSE)
{
    $CI = & get_instance();

    if(!isset($CI->S3Client)) {
        $CI->S3Client = new S3Client([
            'region' => AWS_REGION,
            'version' => AWS_VERSION,
            'credentials' => [
                'key' => AWS_KEY,
                'secret' => AWS_SECRET_KEY
            ],
        ]);
    }

    static $_filedata = array();

    if ($_recursion === FALSE)
    {
        $_filedata = array();
        $source_dir = rtrim($source_dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    }

    $dirFiles = bucketScanDir($source_dir, TRUE);

    foreach ($dirFiles as $file) {
        $_filedata[] = ($include_path == TRUE) ? $source_dir.$file : $file;
    }

    return $_filedata;
}

function bucket_move($file, $path, $options = []) {

    if(config_item('bucket_sub_folder')) {
        $path = config_item('bucket_sub_folder') . '/' . $path;
    }

    $CI = & get_instance();

    if(!isset($CI->S3Client)) {
        $CI->S3Client = new S3Client([
            'region' => AWS_REGION,
            'version' => AWS_VERSION,
            'credentials' => [
                'key' => AWS_KEY,
                'secret' => AWS_SECRET_KEY
            ],
        ]);
    }

    $CI->S3Client->putObject([
        'Bucket' => S3_BUCKET_NAME,
        'Key' => $path,
        'SourceFile' => $file,
        'ACL' => 'public-read'
    ] + $options);

    return TRUE;
}

function bucket_write_file($path, $data = NULL, $options = [], array $metadata = []) {

    if(config_item('bucket_sub_folder')) {
        $path = config_item('bucket_sub_folder') . '/' . $path;
    }

    $CI = & get_instance();

    if(!isset($CI->S3Client)) {
        $CI->S3Client = new S3Client([
            'region' => AWS_REGION,
            'version' => AWS_VERSION,
            'credentials' => [
                'key' => AWS_KEY,
                'secret' => AWS_SECRET_KEY
            ]
        ]);
    }

    $CI->S3Client->putObject([
        'Bucket' => S3_BUCKET_NAME,
        'Key' => $path,
        'Body' => $data,
        'ACL' => 'public-read',
            'Metadata' => $metadata
    ] + $options);
}

function bucket_read_file($path) {

    if(config_item('bucket_sub_folder')) {
        $path = config_item('bucket_sub_folder') . '/' . $path;
    }

    $CI = & get_instance();

    if(!isset($CI->S3Client)) {
        $CI->S3Client = new S3Client([
            'region' => AWS_REGION,
            'version' => AWS_VERSION,
            'credentials' => [
                'key' => AWS_KEY,
                'secret' => AWS_SECRET_KEY
            ],
        ]);
    }
    try {
        $result = $CI->S3Client->getObject([
            'Bucket' => S3_BUCKET_NAME,
            'Key' => $path,
        ]);
        return isset($result['Body']) ? $result['Body']->__toString() : FALSE;
    } catch (Exception $e) {
        return FALSE;
    }
}

function bucket_get_stream($path) {
    if(config_item('bucket_sub_folder')) {
        $path = config_item('bucket_sub_folder') . '/' . $path;
    }

    $CI = & get_instance();
    if(!isset($CI->S3Client)) {
        $CI->S3Client = new S3Client([
            'region' => AWS_REGION,
            'version' => AWS_VERSION,
            'credentials' => [
                'key' => AWS_KEY,
                'secret' => AWS_SECRET_KEY
            ],
        ]);
    }
    try {
        $result = $CI->S3Client->getObject([
            'Bucket' => S3_BUCKET_NAME,
            'Key' => $path,
        ]);
        return isset($result['Body']) ? $result['Body']->detach() : false;
    } catch (Exception $e) {
        return FALSE;
    }
}

function bucket_copy($source, $target, $options = []) {
    if(config_item('bucket_sub_folder')) {
        $source = config_item('bucket_sub_folder') . '/' . $source;
        $target = config_item('bucket_sub_folder') . '/' . $target;
    }

    $CI = & get_instance();

    if(!isset($CI->S3Client)) {
        $CI->S3Client = new S3Client([
            'region' => AWS_REGION,
            'version' => AWS_VERSION,
            'credentials' => [
                'key' => AWS_KEY,
                'secret' => AWS_SECRET_KEY
            ],
        ]);
    }
    try {
        $CI->S3Client->copyObject([
            'Bucket' => S3_BUCKET_NAME,
            'Key' => $target,
            'CopySource' => S3_BUCKET_NAME . '/' . $source,
            'ACL' => 'public-read'
        ]);
    }catch (Exception $ex){
        return FALSE;
    }

    /*$data = bucket_read_file($source);
    bucket_write_file($target, $data, $options);*/

    return TRUE;
}

function bucket_unlink($path) {
    if(config_item('bucket_sub_folder')) {
        $path = config_item('bucket_sub_folder') . '/' . $path;
    }

    $CI = & get_instance();

    if(!isset($CI->S3Client)) {
        $CI->S3Client = new S3Client([
            'region' => AWS_REGION,
            'version' => AWS_VERSION,
            'credentials' => [
                'key' => AWS_KEY,
                'secret' => AWS_SECRET_KEY
            ],
        ]);
    }

    $CI->S3Client->deleteObject([
        'Bucket' => S3_BUCKET_NAME,
        'Key' => $path,
    ]);

    return true;
}

function bucket_unlink_all($path) {
    if(config_item('bucket_sub_folder')) {
        $path = config_item('bucket_sub_folder') . '/' . $path;
    }

    $CI = & get_instance();

    if(!isset($CI->S3Client)) {
        $CI->S3Client = new S3Client([
            'region' => AWS_REGION,
            'version' => AWS_VERSION,
            'credentials' => [
                'key' => AWS_KEY,
                'secret' => AWS_SECRET_KEY
            ],
        ]);
    }

    $CI->S3Client->deleteMatchingObjects(S3_BUCKET_NAME, $path);

    return TRUE;
}

function bucket_get_file_info($file, $returned_values = array('name', 'server_path', 'size', 'date')) {
    if(config_item('bucket_sub_folder')) {
        $file = config_item('bucket_sub_folder') . '/' . $file;
    }

    $CI = & get_instance();

    if(!isset($CI->S3Client)) {
        $CI->S3Client = new S3Client([
            'region' => AWS_REGION,
            'version' => AWS_VERSION,
            'credentials' => [
                'key' => AWS_KEY,
                'secret' => AWS_SECRET_KEY
            ],
        ]);
    }
    try {
        $result = $CI->S3Client->getObject([
            'Bucket' => S3_BUCKET_NAME,
            'Key' => $file,
        ]);
        return [
            'name' => basename($file),
            'server_path' => $file,
            'size' => $result['ContentLength'],
            'date' => $result['LastModified']->getTimestamp(),
            'mimetype' => getMimeType($file),
            'metadata' => $result['Metadata']
        ];
    } catch (Exception $e) {
        return FALSE;
    }
}
function getMimeType($file) {
    // MIME types array
    
    if(!is_string($file))
		return false;
		
    $mimeTypes = array(
        "323"       => "text/h323",
        "acx"       => "application/internet-property-stream",
        "ai"        => "application/postscript",
        "aif"       => "audio/x-aiff",
        "aifc"      => "audio/x-aiff",
        "aiff"      => "audio/x-aiff",
        "asf"       => "video/x-ms-asf",
        "asr"       => "video/x-ms-asf",
        "asx"       => "video/x-ms-asf",
        "au"        => "audio/basic",
        "avi"       => "video/x-msvideo",
        "axs"       => "application/olescript",
        "bas"       => "text/plain",
        "bcpio"     => "application/x-bcpio",
        "bin"       => "application/octet-stream",
        "bmp"       => "image/bmp",
        "c"         => "text/plain",
        "cat"       => "application/vnd.ms-pkiseccat",
        "cdf"       => "application/x-cdf",
        "cer"       => "application/x-x509-ca-cert",
        "class"     => "application/octet-stream",
        "clp"       => "application/x-msclip",
        "cmx"       => "image/x-cmx",
        "cod"       => "image/cis-cod",
        "cpio"      => "application/x-cpio",
        "crd"       => "application/x-mscardfile",
        "crl"       => "application/pkix-crl",
        "crt"       => "application/x-x509-ca-cert",
        "csh"       => "application/x-csh",
        "css"       => "text/css",
        "dcr"       => "application/x-director",
        "der"       => "application/x-x509-ca-cert",
        "dir"       => "application/x-director",
        "dll"       => "application/x-msdownload",
        "dms"       => "application/octet-stream",
        "doc"       => "application/msword",
        "dot"       => "application/msword",
        "dvi"       => "application/x-dvi",
        "dxr"       => "application/x-director",
        "eps"       => "application/postscript",
        "etx"       => "text/x-setext",
        "evy"       => "application/envoy",
        "exe"       => "application/octet-stream",
        "fif"       => "application/fractals",
        "flr"       => "x-world/x-vrml",
        "gif"       => "image/gif",
        "gtar"      => "application/x-gtar",
        "gz"        => "application/x-gzip",
        "h"         => "text/plain",
        "hdf"       => "application/x-hdf",
        "hlp"       => "application/winhlp",
        "hqx"       => "application/mac-binhex40",
        "hta"       => "application/hta",
        "htc"       => "text/x-component",
        "htm"       => "text/html",
        "html"      => "text/html",
        "htt"       => "text/webviewhtml",
        "ico"       => "image/x-icon",
        "ief"       => "image/ief",
        "iii"       => "application/x-iphone",
        "ins"       => "application/x-internet-signup",
        "isp"       => "application/x-internet-signup",
        "jfif"      => "image/pipeg",
        "jpe"       => "image/jpeg",
        "jpeg"      => "image/jpeg",
        "jpg"       => "image/jpeg",
        "js"        => "application/x-javascript",
        "latex"     => "application/x-latex",
        "lha"       => "application/octet-stream",
        "lsf"       => "video/x-la-asf",
        "lsx"       => "video/x-la-asf",
        "lzh"       => "application/octet-stream",
        "m13"       => "application/x-msmediaview",
        "m14"       => "application/x-msmediaview",
        "m3u"       => "audio/x-mpegurl",
        "man"       => "application/x-troff-man",
        "mdb"       => "application/x-msaccess",
        "me"        => "application/x-troff-me",
        "mht"       => "message/rfc822",
        "mhtml"     => "message/rfc822",
        "mid"       => "audio/mid",
        "mny"       => "application/x-msmoney",
        "mov"       => "video/quicktime",
        "movie"     => "video/x-sgi-movie",
        "mp2"       => "video/mpeg",
        "mp3"       => "audio/mpeg",
        "mpa"       => "video/mpeg",
        "mpe"       => "video/mpeg",
        "mpeg"      => "video/mpeg",
        "mpg"       => "video/mpeg",
        "mpp"       => "application/vnd.ms-project",
        "mpv2"      => "video/mpeg",
        "ms"        => "application/x-troff-ms",
        "mvb"       => "application/x-msmediaview",
        "nws"       => "message/rfc822",
        "oda"       => "application/oda",
        "p10"       => "application/pkcs10",
        "p12"       => "application/x-pkcs12",
        "p7b"       => "application/x-pkcs7-certificates",
        "p7c"       => "application/x-pkcs7-mime",
        "p7m"       => "application/x-pkcs7-mime",
        "p7r"       => "application/x-pkcs7-certreqresp",
        "p7s"       => "application/x-pkcs7-signature",
        "pbm"       => "image/x-portable-bitmap",
        "pdf"       => "application/pdf",
        "pfx"       => "application/x-pkcs12",
        "pgm"       => "image/x-portable-graymap",
        "pko"       => "application/ynd.ms-pkipko",
        "pma"       => "application/x-perfmon",
        "pmc"       => "application/x-perfmon",
        "pml"       => "application/x-perfmon",
        "pmr"       => "application/x-perfmon",
        "pmw"       => "application/x-perfmon",
        "pnm"       => "image/x-portable-anymap",
        "png"       => "image/png",
        "pot"       => "application/vnd.ms-powerpoint",
        "ppm"       => "image/x-portable-pixmap",
        "pps"       => "application/vnd.ms-powerpoint",
        "ppt"       => "application/vnd.ms-powerpoint",
        "prf"       => "application/pics-rules",
        "ps"        => "application/postscript",
        "pub"       => "application/x-mspublisher",
        "qt"        => "video/quicktime",
        "ra"        => "audio/x-pn-realaudio",
        "ram"       => "audio/x-pn-realaudio",
        "ras"       => "image/x-cmu-raster",
        "rgb"       => "image/x-rgb",
        "rmi"       => "audio/mid",
        "roff"      => "application/x-troff",
        "rtf"       => "application/rtf",
        "rtx"       => "text/richtext",
        "scd"       => "application/x-msschedule",
        "sct"       => "text/scriptlet",
        "setpay"    => "application/set-payment-initiation",
        "setreg"    => "application/set-registration-initiation",
        "sh"        => "application/x-sh",
        "shar"      => "application/x-shar",
        "sit"       => "application/x-stuffit",
        "snd"       => "audio/basic",
        "spc"       => "application/x-pkcs7-certificates",
        "spl"       => "application/futuresplash",
        "src"       => "application/x-wais-source",
        "sst"       => "application/vnd.ms-pkicertstore",
        "stl"       => "application/vnd.ms-pkistl",
        "stm"       => "text/html",
        "svg"       => "image/svg+xml",
        "sv4cpio"   => "application/x-sv4cpio",
        "sv4crc"    => "application/x-sv4crc",
        "t"         => "application/x-troff",
        "tar"       => "application/x-tar",
        "tcl"       => "application/x-tcl",
        "tex"       => "application/x-tex",
        "texi"      => "application/x-texinfo",
        "texinfo"   => "application/x-texinfo",
        "tgz"       => "application/x-compressed",
        "tif"       => "image/tiff",
        "tiff"      => "image/tiff",
        "tr"        => "application/x-troff",
        "trm"       => "application/x-msterminal",
        "tsv"       => "text/tab-separated-values",
        "txt"       => "text/plain",
        "uls"       => "text/iuls",
        "ustar"     => "application/x-ustar",
        "vcf"       => "text/x-vcard",
        "vrml"      => "x-world/x-vrml",
        "wav"       => "audio/x-wav",
        "wcm"       => "application/vnd.ms-works",
        "wdb"       => "application/vnd.ms-works",
        "wks"       => "application/vnd.ms-works",
        "wmf"       => "application/x-msmetafile",
        "wps"       => "application/vnd.ms-works",
        "wri"       => "application/x-mswrite",
        "wrl"       => "x-world/x-vrml",
        "wrz"       => "x-world/x-vrml",
        "xaf"       => "x-world/x-vrml",
        "xbm"       => "image/x-xbitmap",
        "xla"       => "application/vnd.ms-excel",
        "xlc"       => "application/vnd.ms-excel",
        "xlm"       => "application/vnd.ms-excel",
        "xls"       => "application/vnd.ms-excel",
        "xlsx"      => "vnd.ms-excel",
        "xlt"       => "application/vnd.ms-excel",
        "xlw"       => "application/vnd.ms-excel",
        "xof"       => "x-world/x-vrml",
        "xpm"       => "image/x-xpixmap",
        "xwd"       => "image/x-xwindowdump",
        "z"         => "application/x-compress",
        "zip"       => "application/zip",
        'aac'       => 'audio/x-aac',
        'm4a'       => 'audio/x-m4a',
        'webm'      => 'audio/webm',
        'mp4'       => 'video/mp4'
    );
    $tmp = explode('.', $file);
    $extension = strtolower(end($tmp));
    return isset($mimeTypes[$extension]) ? $mimeTypes[$extension] : FALSE; // return the array value
}

function is_empty_bucket_dir($path = '/', $recursive = false) {
    if(config_item('bucket_sub_folder')) {
        $path = config_item('bucket_sub_folder') . '/' . ($path !== '/' ? $path : null);
    }

	$CI = & get_instance();

	if(!isset($CI->S3Client)) {
		$CI->S3Client = new S3Client([
			'region' => AWS_REGION,
			'version' => AWS_VERSION,
			'credentials' => [
				'key' => AWS_KEY,
				'secret' => AWS_SECRET_KEY
			],
		]);
	}

	$options = array(
		"Bucket" => S3_BUCKET_NAME,
		"Prefix" => $path,
	);

	if(!$recursive)
		$options["Delimiter"] = '/';

	$objects = $CI->S3Client->listObjects(['Bucket' => S3_BUCKET_NAME, 'Prefix' => $path]);

	// if has contents -> is not empty
	return $objects['Contents'] ? false : true;
}
