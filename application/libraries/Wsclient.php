<?php

/**
 * Very basic websocket client.
 * Supporting handshake from drafts:
 *	draft-hixie-thewebsocketprotocol-76
 *	draft-ietf-hybi-thewebsocketprotocol-00
 *
 * @author Simon Samtleben
 * @version 2011-09-15
 */

class WsClient
{
    private $_Socket = null;

    public function __construct($params)
    {
        $this->_connect($params['host'], $params['port'], $params['request']);
    }

    public function __destruct()
    {
        $this->_disconnect();
    }

    public function sendData($data = '', $return = true)
    {
        // send actual data:
        fwrite($this->_Socket, "\x00" . $data . "\xff" ) or die('Error:' . $errno . ':' . $errstr);
        $wsData = fread($this->_Socket, 2000);
        $retData = trim($wsData, "\x00\xff");
        if($return)
            return $this->hybi10Decode($retData);
        else
            return true;
    }

    function hybi10Decode($data)
    {
        $bytes = $data;
        $dataLength = '';
        $mask = '';
        $coded_data = '';
        $decodedData = '';
        $secondByte = sprintf('%08b', ord($bytes[1]));
        $masked = ($secondByte[0] == '1') ? true : false;
        $dataLength = ($masked === true) ? ord($bytes[1]) & 127 : ord($bytes[1]);

        if($masked === true)
        {
            if($dataLength === 126)
            {
                $mask = substr($bytes, 4, 4);
                $coded_data = substr($bytes, 8);
            }
            elseif($dataLength === 127)
            {
                $mask = substr($bytes, 10, 4);
                $coded_data = substr($bytes, 14);
            }
            else
            {
                $mask = substr($bytes, 2, 4);
                $coded_data = substr($bytes, 6);
            }
            for($i = 0; $i < strlen($coded_data); $i++)
            {
                $decodedData .= $coded_data[$i] ^ $mask[$i % 4];
            }
        }
        else
        {
            if($dataLength === 126)
            {
                $decodedData = substr($bytes, 4);
            }
            elseif($dataLength === 127)
            {
                $decodedData = substr($bytes, 10);
            }
            else
            {
                $decodedData = substr($bytes, 2);
            }
        }

        return $decodedData;
    }

    private  function encode($payload, $type = 'text', $masked = false) {
        $frameHead = array();
        $payloadLength = strlen($payload);

        switch ($type) {
            case 'text':
                // first byte indicates FIN, Text-Frame (10000001):
                $frameHead[0] = 129;
                break;

            case 'close':
                // first byte indicates FIN, Close Frame(10001000):
                $frameHead[0] = 136;
                break;

            case 'ping':
                // first byte indicates FIN, Ping frame (10001001):
                $frameHead[0] = 137;
                break;

            case 'pong':
                // first byte indicates FIN, Pong frame (10001010):
                $frameHead[0] = 138;
                break;
        }

        // set mask and payload length (using 1, 3 or 9 bytes)
        if ($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for ($i = 0; $i < 8; $i++) {
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
            }
            // most significant bit MUST be 0
            if ($frameHead[2] > 127) {
                return array('type' => '', 'payload' => '', 'error' => 'frame too large (1004)');
            }
        } elseif ($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } else {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }

        // convert frame-head to string:
        foreach (array_keys($frameHead) as $i) {
            $frameHead[$i] = chr($frameHead[$i]);
        }
        if ($masked === true) {
            // generate a random mask:
            $mask = array();
            for ($i = 0; $i < 4; $i++) {
                $mask[$i] = chr(rand(0, 255));
            }

            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);

        // append payload to frame:
        for ($i = 0; $i < $payloadLength; $i++) {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }

        return $frame;
    }


    private function _connect($host, $port = NULL, $request = NULL)
    {
        $key1 = $this->_generateRandomString(32);

        $str = $request;

        if($request && is_array($request) && !empty($request)) {
            $str = NULL;
            foreach ($request as $key => $val) {
                $str .= $key . '=' . $val . '&';
            }
            $str = rtrim($str, '&');
        }
        $address = $host;
        if($port)
            $address .= ':' . $port;

        $header = "GET /?$str HTTP/1.1\r\n";
        $header.= "Upgrade: WebSocket\r\n";
        $header.= "Connection: Upgrade\r\n";
        $header.= "Host: ".$address."\r\n";
        $header.= "Origin: https://td.onlineoffice.io\r\n";
        $header.= "Sec-WebSocket-Key: " . $key1 . "\r\n";
        $header.= "Sec-WebSocket-Version:13\r\n";
        $header.= "\r\n";

        $contextOptions = [];/*array(
            'ssl' => array(
                'verify_peer' => false,
                'cafile' => 'uploads/td.onlineoffice.io.pem',
            )
        );*/

        $context = stream_context_create($contextOptions);

        $this->_Socket = stream_socket_client($address, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
        fwrite($this->_Socket, $header) or die('Error: ' . $errno . ':' . $errstr);
        $response = fread($this->_Socket, 2000);

        /**
         * @todo: check response here. Currently not implemented cause "2 key handshake" is already deprecated.
         * See: http://en.wikipedia.org/wiki/WebSocket#WebSocket_Protocol_Handshake
         */

        return true;
    }

    private function _disconnect()
    {
        fclose($this->_Socket);
    }

    private function _generateRandomString($length = 10, $addSpaces = true, $addNumbers = true)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!"§$%&/()=[]{}';
        $useChars = array();
        // select some random chars:
        for($i = 0; $i < $length; $i++)
        {
            $useChars[] = $characters[mt_rand(0, strlen($characters)-1)];
        }
        // add spaces and numbers:
        if($addSpaces === true)
        {
            array_push($useChars, ' ', ' ', ' ', ' ', ' ', ' ');
        }
        if($addNumbers === true)
        {
            array_push($useChars, rand(0,9), rand(0,9), rand(0,9));
        }
        shuffle($useChars);
        $randomString = trim(implode('', $useChars));
        $randomString = substr($randomString, 0, $length);
        return $randomString;
    }
}
