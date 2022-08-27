<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

set_time_limit(0);
ignore_user_abort(true);

class Websocket
{

	private $CI;
	private $connects = [];
	private $baseDir = 'uploads/';
	private $pidFile = 'uploads/pid_file.pid';
	private $certPath = 'uploads/cert.pem';
	private $certPassPhrase = '';
	private $socketTimelimit = 0;
	private $IP = "70.40.219.247";
	private $Port = "8889";

	function __construct()
	{
		$this->CI = & get_instance();
		$this->pidFile;

		ini_set('error_log', $this->baseDir . '/echowserrors.txt');
		//fclose(STDIN);
		//fclose(STDOUT);
		//fclose(STDERR);
		$STDIN = fopen('/dev/null', 'r');
		$STDOUT = fopen($this->baseDir . '/echowsconsolelog.txt', 'ab');
		$STDERR = fopen($this->baseDir . '/echowsconsoleerr.txt', 'ab');
		$GLOBALS['file'] = $this->baseDir . '/echowslog.html';
		$this->consolestart();
		$this->consolemsg("echows - try to start...");

		if ($this->isDaemonActive()) {
			$this->consolemsg("CANCEL echows - already active");
			$this->consoleend();
			exit();
		}
		file_put_contents($this->pidFile, getmypid());
		$this->consolemsg("OK getmypid = " . getmypid());

		$timelimit = $this->socketTimelimit;
		$starttime = round(microtime(true),2);

		$this->consolemsg("socket - try to start...");

		$context = stream_context_create();
		stream_context_set_option($context, 'ssl', 'local_cert', $this->certPath);
		stream_context_set_option($context, 'ssl', 'passphrase', $this->certPassPhrase);
		stream_context_set_option($context, 'ssl', 'allow_self_signed', true);
		stream_context_set_option($context, 'ssl', 'verify_peer', false);

		$socket = stream_socket_server("ssl://" . $this->IP . ":". $this->Port, $errno, $errstr, STREAM_SERVER_BIND|STREAM_SERVER_LISTEN, $context);

		if (!$socket) {
			$this->consolemsg("ERROR socket unavailable " . $errstr . "(" . $errno . ")");
			unlink($this->pidFile);
			$this->consolemsg("pidfile " . $this->pidFile . " ulinked");
			$this->consoleend();
			die($errstr. "(" . $errno . ")\n");
		}

		while (TRUE) {
			$this->consolemsg("socket - main while...");
			$read = $this->connects;
			$read []= $socket;
			$write = $except = null;

			if (!stream_select($read, $write, $except, null)) {
				break;
			}

			if (in_array($socket, $read)) {

				if (($connect = stream_socket_accept($socket, -1)) && $info = $this->handshake($connect)) {
					$this->consolemsg("new connection... connect=" . $connect . ", info=" . $info . " OK");
					$this->connects[] = $connect;
					$this->onOpen($connect, $info);
				}
				unset($read[ array_search($socket, $read) ]);
			}

			foreach($read as $connect) {
				$data = fread($connect, 100000);

				if (!$data) {
					$this->consolemsg("connection closed...");
					fclose($connect);
					unset($this->connects[ array_search($connect, $this->connects) ]);
					$this->onClose($connect);
					$this->consolemsg("OK");
					continue;
				}

				$this->onMessage($connect, $data);

				$f = $this->decode($data);

				if ($f['payload']=="OFF") {
					$this->consolemsg("OFF command receive");
					$this->consolemsg("time = " . (round(microtime(true),2) - $starttime));
					fclose($socket);
					$this->consolemsg("socket - closed");
					unlink($this->pidFile);
					$this->consolemsg("pidfile " . $this->pidFile . " ulinked");
					$this->consoleend();
					exit();
				}
			}

			if($timelimit!=0 && ( round(microtime(true),2) - $starttime) > $timelimit) {
				$this->consolemsg("time limit is over");
				$this->consolemsg("time = " . (round(microtime(true),2) - $starttime));
				fclose($socket);
				$this->consolemsg("socket - closed");
				unlink($this->pidFile);
				$this->consolemsg("pidfile " . $this->pidFile . " ulinked");
				$this->consoleend();
				exit();
			}
		}

		fclose($socket);
		$this->consolemsg("socket - closed");
		unlink($this->pidFile);
		$this->consolemsg("pidfile " . $this->pidFile . " ulinked");
		$this->consoleend();
	}

	private function handshake($connect) {
		$info = array();

		$line = fgets($connect);
		$header = explode(' ', $line);
		$info['method'] = $header[0];
		//$info['uri'] = $header[1];

		while ($line = rtrim(fgets($connect))) {
			if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
				$info[$matches[1]] = $matches[2];
			} else {
				break;
			}
		}

		$address = explode(':', stream_socket_get_name($connect, true));
		$info['ip'] = $address[0];
		$info['port'] = $address[1];

		if (empty($info['Sec-WebSocket-Key'])) {
			return false;
		}

		$SecWebSocketAccept = base64_encode(pack('H*', sha1($info['Sec-WebSocket-Key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
		$upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
			"Upgrade: websocket\r\n" .
			"Connection: Upgrade\r\n" .
			"Sec-WebSocket-Accept:".$SecWebSocketAccept."\r\n\r\n";
		fwrite($connect, $upgrade);

		return $info;
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

	function decode($data){
		$unmaskedPayload = '';
		$decodedData = array();

		// estimate frame type:
		$firstByteBinary = sprintf('%08b', ord($data[0]));
		$secondByteBinary = sprintf('%08b', ord($data[1]));
		$opcode = bindec(substr($firstByteBinary, 4, 4));
		$isMasked = ($secondByteBinary[0] == '1') ? true : false;
		$payloadLength = ord($data[1]) & 127;

		// unmasked frame is received:
		if (!$isMasked) {
			return array('type' => '', 'payload' => '', 'error' => 'protocol error (1002)');
		}

		switch ($opcode) {
			// text frame:
			case 1:
				$decodedData['type'] = 'text';
				break;

			case 2:
				$decodedData['type'] = 'binary';
				break;

			// connection close frame:
			case 8:
				$decodedData['type'] = 'close';
				break;

			// ping frame:
			case 9:
				$decodedData['type'] = 'ping';
				break;

			// pong frame:
			case 10:
				$decodedData['type'] = 'pong';
				break;

			default:
				return array('type' => '', 'payload' => '', 'error' => 'unknown opcode (1003)');
		}

		if ($payloadLength === 126) {
			$mask = substr($data, 4, 4);
			$payloadOffset = 8;
			$dataLength = bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3]))) + $payloadOffset;
		} elseif ($payloadLength === 127) {
			$mask = substr($data, 10, 4);
			$payloadOffset = 14;
			$tmp = '';
			for ($i = 0; $i < 8; $i++) {
				$tmp .= sprintf('%08b', ord($data[$i + 2]));
			}
			$dataLength = bindec($tmp) + $payloadOffset;
			unset($tmp);
		} else {
			$mask = substr($data, 2, 4);
			$payloadOffset = 6;
			$dataLength = $payloadLength + $payloadOffset;
		}

		/**
		 * We have to check for large frames here. socket_recv cuts at 1024 bytes
		 * so if websocket-frame is > 1024 bytes we have to wait until whole
		 * data is transferd.
		 */
		if (strlen($data) < $dataLength) {
			return false;
		}

		if ($isMasked) {
			for ($i = $payloadOffset; $i < $dataLength; $i++) {
				$j = $i - $payloadOffset;
				if (isset($data[$i])) {
					$unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
				}
			}
			$decodedData['payload'] = $unmaskedPayload;
		} else {
			$payloadOffset = $payloadOffset - 4;
			$decodedData['payload'] = substr($data, $payloadOffset);
		}

		return $decodedData;
	}

	function onOpen($connect, $info) {
		$this->consolemsg("open OK");
	}

	function onClose($connect) {
		$this->consolemsg("close OK");
	}

	function onMessage($connect, $data) {
		$f = $this->decode($data);
		$this->consolemsg("Message:".$f['payload']);
		fwrite($connect, $this->encode($f['payload']));
	}

	private function isDaemonActive() {
		if(is_file($this->pidFile)) {
			$pid = file_get_contents($this->pidFile);
			$status = $this->getDaemonStatus($pid);
			if($status['run']) {
				$this->consolemsg("daemon already running info=" . $status['info']);
				return true;
			} else {
				$this->consolemsg("there is no process with PID = " . $pid . ", last termination was abnormal...");
				$this->consolemsg("try to unlink PID file...");
				if(!unlink($this->pidFile)) {
					$this->consolemsg("ERROR");
					exit(-1);
				}
				$this->consolemsg("OK");
			}
		}
		return false;
	}

	private function getDaemonStatus($pid) {
		$result = array ('run'=>false);
		$output = null;
		exec("ps -aux -p " . $pid, $output);
var_dump($output);die;
		if(is_array($output) && count($output)>1) {//countOk
			$result['run'] = true;
			$result['info'] = $output[1];
		}
		return $result;
	}

	private function consolestart()
	{
		$this->consolemsg("console - start");
	}

	private function consolemsg($msg)
	{
		$file = null;
		if(!file_exists($GLOBALS['file'])) {
			$file = fopen($GLOBALS['file'],"w");
			fputs($file, "<!DOCTYPE html>\r\n<html>\r\n<head>\r\n<title>GC - console log</title>\r\n\r\n<meta charset=\"UTF-8\" />\r\n</head>\r\n<body>\r\n");
		}else
			$file = fopen($GLOBALS['file'],"a");

		echo $msg."\r\n";
		fputs ($file, "[<b>".date("Y.m.d-H:i:s")."</b>]". $msg ."<br />\r\n");
		fclose($file);
	}

	private function consoleend()
	{
		$this->consolemsg("console - end");
	}
}
