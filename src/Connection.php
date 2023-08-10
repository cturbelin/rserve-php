<?php

namespace Sentiweb\Rserve;

/**
 * Rserve client for PHP
 * Supports Rserve protocol 0103 only (used by Rserve 0.5 and higher)
 * @author Clément TURBELIN
 * Developped using code from Simple Rserve client for PHP by Simon Urbanek Licensed under GPL v2 or at your option v3
 */
require_once __DIR__ . '/lib/helpers.php';

use Sentiweb\Rserve\Parser;
use Sentiweb\Rserve\Parser\NativeArray;

/**
 * Handle Connection and communicating with Rserve instance (QAP1 protocol)
 * @author Clément Turbelin
 *
 */
class Connection {

	const DT_INT = 1;
	const DT_CHAR = 2;
	const DT_DOUBLE = 3;
	const DT_STRING = 4;
	const DT_BYTESTREAM = 5;
	const DT_SEXP = 10;
	const DT_ARRAY = 11;
	
	const DEFAULT_HOST = '127.0.0.1';
	const DEFAULT_PORT = 6311;

	/** this is a flag saying that the contents is large (>0xfffff0) and hence uses 56-bit length field */
	const DT_LARGE = 64;

	const CMD_login			= 0x001;
	const CMD_voidEval		= 0x002;
	const CMD_eval			= 0x003;
	const CMD_shutdown		= 0x004;
	const CMD_openFile		= 0x010;
	const CMD_createFile	= 0x011;
	const CMD_closeFile		= 0x012;
	const CMD_readFile		= 0x013;
	const CMD_writeFile		= 0x014;
	const CMD_removeFile	= 0x015;
	const CMD_setSEXP		= 0x020;
	const CMD_assignSEXP	= 0x021;

	const CMD_setBufferSize	= 0x081;
	const CMD_setEncoding	= 0x082;

	const CMD_detachSession	= 0x030;
	const CMD_detachedVoidEval = 0x031;
	const CMD_attachSession = 0x032;

	// control commands since 0.6-0
	const CMD_ctrlEval		= 0x42;
	const CMD_ctrlSource	= 0x45;
	const CMD_ctrlShutdown	= 0x44;

	const CMD_Response = 0x10000;

	// errors as returned by Rserve
	const ERR_auth_failed	= 0x41;
	const ERR_conn_broken	= 0x42;
	const ERR_inv_cmd		= 0x43;
	const ERR_inv_par		= 0x44;
	const ERR_Rerror		= 0x45;
	const ERR_IOerror		= 0x46;
	const ERR_not_open		= 0x47;
	const ERR_access_denied = 0x48;
	const ERR_unsupported_cmd=0x49;
	const ERR_unknown_cmd	= 0x4a;
	const ERR_data_overflow	= 0x4b;
	const ERR_object_too_big = 0x4c;
	const ERR_out_of_mem	= 0x4d;
	const ERR_ctrl_closed	= 0x4e;
	const ERR_session_busy	= 0x50;
	const ERR_detach_failed	= 0x51;

	public static $machine_is_bigendian = null;

	private static $init = false;

	private $host;
	private $port;
	private $socket;
	private $auth_request;
	private $auth_method;

	private bool $debug;

	private bool $async;

	private ?string $username;

	private ?string $password;
	
	/**
	 * Encoding to use
	 * @var string
	 */
	private ?string $encoding;

	// Internal parser, used as default parser 
	// To handle internal operations
	private $parser;
	
	/**
	 * initialization of the library
	 */
	public static function init() {
		if( self::$init ) {
			return;
		}
		$m = pack('s', 1);
		self::$machine_is_bigendian = ($m[0] == 0);
		self::$init = true;
	}

	/**
	 *  @param mixed host or a Session instance or an array of parameters
	 *  @param int $port if 0 then host is interpreted as unix socket,
	 *  @param array params
	 *  
	 *  If host is an array then further arguments are ignored
	 *  (all options should be passed using this array)
	 *  
	 *  If
	 *
	 */
	public function __construct($host=self::DEFAULT_HOST, $port = self::DEFAULT_PORT, $params=[]) {
		if( !self::$init ) {
			self::init();
		}
		
		if( is_array($host) ) {
			$params = $host;
			$this->host =  $params['host'] ?? self::DEFAULT_HOST;
			$this->port =  $params['port'] ?? self::DEFAULT_PORT;
			
 		} elseif(is_object($host) AND $host instanceof Session) {
			$session = $host->key;
			$this->port = $host->port;
			$host = $host->host;
			if( !$host ) {
				$host = self::DEFAULT_HOST;
			}
			$this->host = $host;
		} else {
			$this->host = $host;
			$this->port = $port;
			$session = null;
		}
		$this->debug =  (bool)($params['debug'] ?? false);
		$this->async = (bool)($params['async'] ?? false);
		$this->username =  $params['username'] ?? null;
		$this->password = $params['password'] ?? null;
		$this->encoding = $params['encoding'] ?? null;
		
		// Internal parser used for basic command
		$this->parser = new NativeArray();
		
		$this->openSocket($session);
	}

	/**
	 * Open a new socket to Rserv
	 * @return resource socket
	 */
	private function openSocket($session_key = null) {

		if( $this->port == 0 ) {
			$socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
		} else {
			$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		}
		if( !$socket ) {
			throw new Exception('Unable to create socket ['.socket_strerror(socket_last_error()).']');
		}
		//socket_set_option($socket, SOL_TCP, SO_DEBUG,2);

		$ok = socket_connect($socket, $this->host, $this->port);
		if( !$ok ) {
			throw new Exception('Unable to connect ['.socket_strerror(socket_last_error()).']');
		}
		$this->socket = $socket;
		if( !is_null($session_key) ) {
			// Try to resume session
			$n = socket_send($socket, $session_key, 32, 0);
			if($n < 32) {
				throw new Exception('Unable to send session key');
			}
			$r = $this->getResponse();
			if($r['is_error']) {
				$msg = $this->getErrorMessage($r['error']);
				throw new Exception('invalid session key : '.$msg);
			}
			return;
		}

		// No session, check handshake
		$buf = '';
		$n = socket_recv($socket, $buf, 32, 0);
		if( $n < 32 || strncmp($buf, 'Rsrv', 4) != 0 ) {
			throw new Exception('Invalid response from server.');
		}
		$rv = substr($buf, 4, 4);
		if( strcmp($rv, '0103') != 0 ) {
			throw new Exception('Unsupported protocol version.');
		}
		$key=null;
		$this->auth_request = false;
		for($i = 12; $i < 32; $i += 4) {
			$attr = substr($buf, $i, 4);
			if($attr == 'ARpt') {
				$this->auth_request = true;
				$this->auth_method = 'plain';

			} elseif($attr == 'ARuc') {
				$this->auth_request = true;
				$this->auth_method = 'crypt';
			}
			if($attr[0] === 'K') {
				$key = substr($attr, 1, 3);
			}
		}
		if($this->auth_request === true) {
			if($this->auth_method=="plain") $this->login(); else $this->login($key);
		}
		
		if($this->encoding) {
			$this->setEncoding($this->encoding);
		}
	}

	/**
	 * Allow access to socket
	 */
	public function getSocket() {
		return $this->socket;
	}

	/**
	 * Set Asynchronous mode
	 * @param bool $async
	 */
	public function setAsync($async) {
		$this->async = (bool)$async;
	}

	/**
	 *
	 * Parse a response from Rserve
	 * @param $buf
	 * @param Parser $parser
	 * @return mixed parsed results
	 */
	private function parseResponse($buf, $parser=null) {
		$type = _rserve_int8($buf, 0);
		if($type != self::DT_SEXP) { // Check Data type of the packet
			throw new Exception('Unexpected packet Data type (expect DT_SEXP)', $buf);
		}
		$i = 4; // + 4 bytes (Data part HEADER)
		$r = null;
		if( is_null($parser) ) {
			$r = $this->parser->parse($buf, $i);
		} else {
			$r = $parser->parse($buf, $i);
		}
		return $r;
	}


	/**
	 * Login to rserve
	 * Similar to RSlogin  http://rforge.net/doc/packages/RSclient/Rclient.html
	 * Inspired from https://github.com/SurajGupta/RserveCLI2/blob/master/RServeCLI2/Qap1.cs
	 *               https://github.com/SurajGupta/RserveCLI2/blob/master/RServeCLI2/RConnection.cs
	 * @param string $salt
	 */
	public function login($salt=null) {
		switch ( $this->auth_method )
		{
		case "plain":
			break;
		case "crypt":
			if( !$salt ) {
				throw new Exception("Should pass the salt for login");
			}
			$this->password=crypt($this->password, $salt);
			break;
		default:
			throw new Exception( "Could not interpret login method '{$this->auth_method}'" );
		}
		$data = _rserve_make_data(self::DT_STRING, "{$this->username}\n{$this->password}");
		$r = $this->sendCommand(self::CMD_login, $data );
		if( !$r['is_error'] ) {
			return true;
		}
		throw new Exception( "Could not login" );
	}

	/**
	 * Evaluate a string as an R code and return result
	 * @param string $string
	 * @param int $parser
	 */
	public function evalString($string, $parser = null) {

		$data = _rserve_make_data(self::DT_STRING, $string);

		$r = $this->sendCommand(self::CMD_eval, $data );
		if($this->async) {
			return true;
		}
		if( !$r['is_error'] ) {
				return $this->parseResponse($r['contents'], $parser);
		}
		throw new Exception('unable to evaluate', $r);
	}

	/**
	 * Detach the current session from the current connection.
	 * Save envirnoment could be attached to another R connection later
	 * @return array with session_key used to
	 * @throws Exception
	 */
	public function detachSession() {
		$r = $this->sendCommand(self::CMD_detachSession, null);
		if( !$r['is_error'] ) {
			$x = $r['contents'];
			if( strlen($x) != (32 + 3 * 4) ) {
				throw new Exception('Invalid response to detach');
			}

			$port  =  _rserve_int32($x, 4);
			$key = substr($x, 12);
			$session = new Session($key, $this->host, $port);

			return $session;
		}
		throw new Exception('Unable to detach sesssion', $r);
	}

	/**
	 * Assign a value to a symbol in R
	 * @param string $symbol name of the variable to set (should be compliant with R syntax !)
	 * @param REXP $value value to set
	 */
	public function assign($symbol, REXP $value) {
		$symbol = (string)$symbol;
		$data = _rserve_make_data(self::DT_STRING, $symbol);
		$serializer = new Serializer();
		$bin = $serializer->serialize($value);
		$data .= _rserve_make_data(self::DT_SEXP, $bin);
		$r = $this->sendCommand(self::CMD_assignSEXP, $data);
		return $r;
	}
	
	public function setEncoding($encoding) {
		$this->sendCommand(self::CMD_setEncoding, _rserve_make_data(self::DT_STRING, $encoding));
	}

	/**
	 * Get the response from a command
	 * @param resource	$socket
	 * @return array contents
	 */
	protected function getResponse() {
		$header = null;
		$n = socket_recv($this->socket, $header, 16, 0);
		if ($n != 16) {
			// header should be sent in one block of 16 bytes
			return false;
		}
		$len = _rserve_int32($header, 4);
		$ltg = $len; // length to get
		$buf = '';
		while ($ltg > 0) {
			$n = socket_recv($this->socket, $b2, $ltg, 0);
			if ($n > 0) {
				$buf .= $b2;
				unset($b2);
				$ltg -= $n;
			} else {
			 break;
			}
		}
		$res = _rserve_int32($header);
		return(array(
			'code'=>$res,
			'is_error'=>($res & 15) != 1,
			'error'=>($res >> 24) & 127,
			'header'=>$header,
			'contents'=>$buf // Buffer contains messages part
		));
	}

	/**
	 * Create a new connection to Rserve for async calls
	 * @return	Rserve_Connection
	 */
	public function newConnection() {
		$newConnection = clone($this);
		$newConnection->openSocket();
		return $newConnection;
	}


	/**
	 * Get results from an eval command  in async mode
	 * @param Parser $parser, if null use internal parser
	 * @return mixed contents of response
	 */
	public function getResults($parser = null) {
		$r = $this->getResponse();
		if( !$r['is_error'] ) {
			return $this->parseResponse($r['contents'], $parser);
		}
		throw new Exception('unable to evaluate', $r);
	}

	/**
	 * Close the current connection
	 */
	public function close() {
		return socket_close($this->socket);
	}

	/**
	 * send a command to Rserve
	 * @param int $command command code
	 * @param string $data data packets
	 * @return int	if $async, true
	 */
	protected function sendCommand($command, $data) {

		$pkt = _rserve_make_packet($command, $data);

		if($this->debug) {
			$this->debugPacket($pkt);
		}

		socket_send($this->socket, $pkt, strlen($pkt), 0);

		if($this->async) {
			return true;
		}
		// get response
		return $this->getResponse();
	}

	/**
	 * Debug a Rserve packet
	 * @param array|string $packet
	 */
	public function debugPacket($packet) {
		/*
		  [0]  (int) command
		  [4]  (int) length of the message (bits 0-31)
		  [8]  (int) offset of the data part
		  [12] (int) length of the message (bits 32-63)
		*/
		if(is_array($packet))  {
			$buf = $packet['contents'];
			$header = $packet['header'];
		} else {
			$header = substr($packet, 0, 16);
			$buf = substr($packet, 16);
		}
		$command = _rserve_int32($header, 0);
		$lengthLow = _rserve_int32($header, 4);
		$offset = _rserve_int32($header, 8);
		$lenghtHigh = _rserve_int32($header, 12);
		if($command & self::CMD_Response) {
			$is_error = $command & 15 != 1;
			$cmd = 'CMD Response'.(($is_error) ? 'OK' : 'Error');
			$err = ($command >> 24) & 0x7F;
		} else {
			$cmd = dechex($command) & 0xFFF;
		}
		echo '[header:<'.$cmd.' Length:'.dechex($lenghtHigh).'-'.dechex($lengthLow).' offset'.$offset.">\n";
		$len = strlen($buf);
		$i = 0;
		while($len > 0) {
			$type = _rserve_int8($buf, $i);
			$m_len = _rserve_int24($buf, $i+1);
			$i += 4;
			$i += $m_len;
			$len -= $m_len + 4;
			echo 'data:<'.$this->getDataTypeTitle($type).' length:'.$m_len.">\n";
		}
		echo "]\n";
	}

	/**
	 * Data Type value to label
	 * @param int $x
	 */
	public function getDataTypeTitle($x) {
		switch($x) {
		case self::DT_INT :
			$m = 'int';
			break;
		case self::DT_CHAR :
			$m = 'char';
			break;
		case self::DT_DOUBLE :
			$m = 'double';
			break;
		case self::DT_STRING :
			$m = 'string';
			break;
		case self::DT_BYTESTREAM :
			$m = 'stream';
			break;

		case self::DT_SEXP :
			$m = 'sexp';
			break;

		case self::DT_ARRAY :
			$m = 'array';
			break;
		default:
			$m = 'unknown';
		}
		return $m;
	}

	/**
	 * Translate an error code to an error message
	 * @param int $code
	 */
	public function getErrorMessage($code) {
		switch($code) {
			case self::ERR_auth_failed	: $m = 'auth failed'; break;
			case self::ERR_conn_broken	: $m = 'connexion broken'; break;
			case self::ERR_inv_cmd		:  $m = 'invalid command'; break;
			case self::ERR_inv_par		:  $m = 'invalid parameter'; break;
			case self::ERR_Rerror		:  $m = 'R error'; break;
			case self::ERR_IOerror		:  $m = 'IO error'; break;
			case self::ERR_not_open		:  $m = 'not open'; break;
			case self::ERR_access_denied :  $m = 'access denied'; break;
			case self::ERR_unsupported_cmd: $m = 'unsupported command'; break;
			case self::ERR_unknown_cmd	:  $m = 'unknown command'; break;
			case self::ERR_data_overflow	:  $m = 'data overflow'; break;
			case self::ERR_object_too_big :  $m = 'object too big'; break;
			case self::ERR_out_of_mem	:  $m = 'out of memory' ; break;
			case self::ERR_ctrl_closed	:  $m = 'control closed'; break;
			case self::ERR_session_busy	: $m = 'session busy'; break;
			case self::ERR_detach_failed	:  $m = 'detach failed'; break;
			default:
				$m = 'unknown error';
		}
		return $m;
	}

}






