<?php
/**
 * Rserve client for PHP
 * Supports Rserve protocol 0103 only (used by Rserve 0.5 and higher)
 * $Revision$
 * @author Clément TURBELIN
 * Developped using code from Simple Rserve client for PHP by Simon Urbanek Licensed under GPL v2 or at your option v3
 * $Id$
 */
require_once 'funclib.php';
require_once 'Parser.php';

/**
 * Handle Connection and communicating with Rserve instance
 * @author Clément Turbelin
 *
 */
class Rserve_Connection {

	const DT_INT = 1;
	const DT_CHAR = 2;
	const DT_DOUBLE = 3;
	const DT_STRING = 4;
	const DT_BYTESTREAM = 5;
	const DT_SEXP = 10;
	const DT_ARRAY = 11;

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

	public static $machine_is_bigendian = NULL;

	private static $init = FALSE;

	private $socket;
	private $auth_request;
	private $auth_method;

	/**
	 * initialization of the library
	 */
	public static function init() {
		$m = pack('s', 1);
		self::$machine_is_bigendian = ($m[0] == 0);
		spl_autoload_register('Rserve_Connection::autoload');
		self::$init = TRUE;
	}

	public static function autoload($name) {
		$s = strtolower(substr($name, 0,6));
		if($s != 'rserve') {
			return FALSE;
		}
		$s = substr($name, 7);
		$s = str_replace('_','/',$s);
		$s .= '.php';
		require $s;
		return TRUE; 
	}

	/**
	 *  if port is 0 then host is interpreted as unix socket, otherwise host is the host to connect to (default is local) and port is the TCP port number (6311 is the default)
	 */
	public function __construct($host='127.0.0.1', $port = 6311, $debug = FALSE) {
		if( !self::$init ) {
			self::init();
		}
		if( $port == 0 ) {
			$socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
		} else {
			$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		}
        if( !$socket ) {
            throw new Rserve_Exception("Unable to create socket<pre>".socket_strerror(socket_last_error())."</pre>");
        }
        //socket_set_option($socket, SOL_TCP, SO_DEBUG,2);
        $ok = socket_connect($socket, $host, $port);
        if( !$ok ) {
            throw new Rserve_Exception("Unable to connect<pre>".socket_strerror(socket_last_error())."</pre>");
        }
        $buf = '';
        $n = socket_recv($socket, $buf, 32, 0);
        if( $n < 32 || strncmp($buf, 'Rsrv', 4) != 0 ) {
            throw new Rserve_Exception('Invalid response from server.');
        }
        $rv = substr($buf, 4, 4);
        if( strcmp($rv, '0103') != 0 ) {
            throw new Rserve_Exception('Unsupported protocol version.');
        }
        for($i = 12; $i < 32; $i += 4) {
            $attr = substr($buf, $i, $i + 4);
            if($attr == 'ARpt') {
                $this->auth_request = TRUE;
                $this->auth_method = 'plain';
            } elseif($attr == 'ARuc') {
                $this->auth_request = TRUE;
                $this->auth_method = 'crypt';
            }
            if($attr[0] === 'K') {
                $key = substr($attr,1,3);
            }
        }
		$this->socket = $socket;
	}

	/**
	 * Evaluate a string as an R code and return result
	 * @param string $string
	 * @param boolean $asNative 
	 * @param REXP_List $attr
	 */
	public function evalString($string, $asNative = TRUE, $attr=NULL) {
		$r = $this->command(self::CMD_eval, $string );
		$i = 20;
		if( !$r['is_error'] ) {
			$buf = $r['contents'];
			$r = NULL;
			if($asNative) {
				$r = Rserve_Parser::parse($buf, $i, &$attr);
			} else {
				$r = Rserve_Parser::parseREXP($buf, $i, &$attr);
			}
			return $r;
		}
		// TODO: contents and code in exception
		throw new Rserve_Exception('unable to evaluate');
	}

	/**
	 * Close the current connection
	 */
	public function close() {
		if($this->socket) {
			return socket_close($this->socket);
		}
		return TRUE;
	}

	/**
	 * send a command to R
	 * @param int $command command code
	 * @param string $v command contents
	 */
	private function command($command, $v) {
		$pkt = _rserve_make_packet($command, $v);
		socket_send($this->socket, $pkt, strlen($pkt), 0);

		// get response
		$n = socket_recv($this->socket, $buf, 16, 0);
		if ($n != 16) {
			return FALSE;
		}
		$len = int32($buf, 4);
		$ltg = $len;
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
		$res = int32($buf);
		return(array(
			'code'=>$res,
			'is_error'=>($res & 15) != 1,
			'error'=>($res >> 24) & 127,
			'contents'=>$buf
		));
	}

	/**
	 * Assign a value to a symbol in R
	 * @param string $symbol name of the variable to set (should be compliant with R syntax !)
	 * @param Rserve_REXP $value value to set
     Commented because not ready for this release
	public function assign($symbol, $value) {
		if(! is_object($symbol) and !$symbol instanceof Rserve_REXP_Symbol) {
			$symbol = (string)$symbol;
			$s = new Rserve_REXP_Symbol();
			$s->setValue($symbol);
		}
		if(!is_object($value) AND ! $value instanceof Rserve_REXP) {
			throw new InvalidArgumentException('value should be REXP object');
		}
		$contents .= Rserve_Parser::createBinary($s);
		$contents .= Rserve_Parser::createBinary($value);
	}
   	 */

}

class Rserve_Exception extends Exception { }

class Rserve_Parser_Exception extends Rserve_Exception {
}

Rserve_Connection::init();

