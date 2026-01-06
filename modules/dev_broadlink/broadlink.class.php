<?php

function aes128_cbc_encrypt($key, $data, $iv) {
  $data = str_pad($data, ceil(strlen($data) / 16) * 16, chr(0), STR_PAD_RIGHT);
  return openssl_encrypt($data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
}

function aes128_cbc_decrypt($key, $data, $iv) {
  return rtrim(openssl_decrypt($data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv), chr(0));
}

function generateCsv($data) {
	# Generate CSV data from array
	$fh = fopen('php://temp', 'rw');	# don't create a file, attempt
										# to use memory instead
	# write out the headers
	fputcsv($fh, array_keys(current($data)));

	# write out the data
	foreach ( $data as $row ) {
		fputcsv($fh, $row);
	}
	rewind($fh);
	$csv = stream_get_contents($fh);
	fclose($fh);

	return $csv;
}

class Broadlink{
	protected $name;
	protected $host;
	protected $port = 80;
	protected $mac;
	protected $timeout = 10;
	protected $count;
	protected $key = array(0x09, 0x76, 0x28, 0x34, 0x3f, 0xe9, 0x9e, 0x23, 0x76, 0x5c, 0x15, 0x13, 0xac, 0xcf, 0x8b, 0x02);
	protected $iv = array(0x56, 0x2e, 0x17, 0x99, 0x6d, 0x09, 0x3d, 0x28, 0xdd, 0xb3, 0xba, 0x69, 0x5a, 0x2e, 0x6f, 0x58);
	protected $id = array(0, 0, 0, 0);
	protected $devtype;
	public $ping_response;
	public $ping_time = 'down';
	public $ping_status;

	function __construct($h = "", $m = "", $p = 80, $d = 0) {

		$this->host = $h;
		$this->port = $p;
		$this->devtype = is_string($d) ? hexdec($d) : $d;

		if(is_array($m)){

			$this->mac = $m;
		}
		else{

			$this->mac = array();
			$mac_str_array = explode(':', $m);

			foreach ( array_reverse($mac_str_array) as $value ) {
				array_push($this->mac, $value);
			}

		}

		$this->count = rand(0, 0xffff);

	}

	function __destruct() {


	}

	public static function CreateDevice($h = "", $m = "", $p = 80, $d = 0){
		switch (self::model($d)) {
			case 0:
				return new SP1($h, $m, $p, $d);
				break;
			case 1:
				return new SP2($h, $m, $p, $d);
				break;
			case 2:
				return new RM($h, $m, $p, $d);
				break;
			case 3:
				return new A1($h, $m, $p, $d);
				break;
			case 4:
				return new MP1($h, $m, $p, $d);
				break;
			case 5:
				return new MS1($h, $m, $p, $d);
				break;
			case 6:
				return new S1($h, $m, $p, $d);
				break;
			case 7:
				return new DOOYA($h, $m, $p, $d);
				break;
			case 8:
				return new HYSEN($h, $m, $p, $d);
				break;
			case 24:
				return new RM4($h, $m, $p, $d);
				break;
			case 100:
				return new UNK($h, $m, $p, $d);
				break;
			default:
		}

		return NULL;
	}

	protected function key(){
		return implode(array_map("chr", $this->key));
	}

	protected function iv(){
		return implode(array_map("chr", $this->iv));
	}

	public function mac(){

		$mac = "";

		foreach ($this->mac as $value) {
			$mac = sprintf("%02x", $value) . ':' . $mac;
		}

		return substr($mac, 0, strlen($mac) - 1);
	}

	public function host(){
		return $this->host;
	}

	public function name(){
		return $this->name;
	}

	public function devtype(){
		return sprintf("0x%x", $this->devtype);
	}

	public function devmodel(){
		return self::model($this->devtype, 'model');
	}

	public static function model($devtype, $needle='type'){

		$type = "Unknown";
		$model = "Unknown";
		if (is_string($devtype)) $devtype = hexdec($devtype);

		switch ($devtype) {
			case 0:
				$model = "SP1";
				$type = 0;
				break;
			case 0x2711:
				$model = "SP2";
				$type = 1;
				break;
			case 0x2719:
			case 0x7919:
			case 0x271a:
			case 0x791a:
				$model = "Honeywell SP2";
				$type = 1;
				break;
			case 0x2720:
				$model = "SPMini";
				$type = 1;
				break;
			case 0x753e:
				$model = "SP3";
				$type = 1;
				break;
			case 0x2728:
				$model = "SPMini2";
				$type = 1;
				break;
			case 0x2733:
			case 0x273e:
			case 0x7539:
			case 0x754e:
			case 0x753d:
			case 0x7536:
				$model = "OEM branded SPMini";
				$type = 1;
				break;
			case 0x7540:
				$model = "MP2";
				$type = 1;
				break;
			case 0x7530:
			case 0x7918:
			case 0x7549:
				$model = "OEM branded SPMini2";
				$type = 1;
				break;
			case 0x2736:
				$model = "SPMiniPlus";
				$type = 1;
				break;
			case 0x947c:
				$model = "SPMiniPlus2";
				$type = 1;
				break;
			case 0x7547:
				$model = "SC1 WiFi Box";
				$type = 1;
				break;
			case 0x947a:
			case 0x9479:
				$model = "SP3S";
				$type = 1;
				break;
			case 0x2710:
				$model = "RM1";
				$type = 2;
				break;
			case 0x2712:
				$model = "RM2";
				$type = 2;
				break;
			case 0x2737:
				$model = "RM Mini";
				$type = 2;
				break;
			case 0x27a2:
				$model = "RM Mini R2";
				$type = 2;
				break;
			case 0x273d:
				$model = "RM Pro Phicomm";
				$type = 2;
				break;
			case 0x2783:
				$model = "RM2 Home Plus";
				$type = 2;
				break;
			case 0x277c:
				$model = "RM2 Home Plus GDT";
				$type = 2;
				break;
			case 0x272a:
				$model = "RM2 Pro Plus";
				$type = 2;
				break;
			case 0x2787:
				$model = "RM2 Pro Plus2";
				$type = 2;
				break;
			case 0x279d:
				$model = "RM2 Pro Plus3";
				$type = 2;
				break;
			case 0x2797:
				$model = "RM2 Pro Plus HYC";
				$type = 2;
				break;
			case 0x278b:
				$model = "RM2 Pro Plus BL";
				$type = 2;
				break;
			case 0x27a1:
			case 0x27a9:
				$model = "RM2 Pro Plus R1";
				$type = 2;
				break;
			case 0x278f:
				$model = "RM Mini Shate";
				$type = 2;
				break;
			case 0x27c2:
				$model = "RM3 mini";
				$type = 2;
				break;
      case 0x51da:
      case 0x5f36:
      case 0x610e:
      case 0x62bc:
				$model = "RM4 mini";
				$type = 24;
				break;
      case 0x610f:
      case 0x62be:
				$model = "RM4c";
				$type = 24;
				break;
      case 0x6026:
				$model = "RM4 Pro";
				$type = 24;
				break;
			case 0x520b:
				$model = "RM4 Pro";
				$type = 24;
				break;
			case 0x2714:
			case 0x27a3:
				$model = "A1";
				$type = 3;
				break;
			case 0x4EB5:
				$model = "MP1";
				$type = 4;
				break;
			case 0x271F:
				$model = "MS1";
				$type = 5;
				break;
			case 0x2722:
				$model = "S1";
				$type = 6;
				break;
			case 0x273c:
				$model = "S1 Phicomm";
				$type = 6;
				break;
			case 0x4f34:
			case 0x4f35:
			case 0x4f36:
				$model = "TW2 Switch";
				$type = 1;
				break;
			case 0x4ee6:
			case 0x4eee:
			case 0x4eef:
				$model = "NEW Switch";
				$type = 1;
				break;
			case 0x271b:
			case 0x271c:
				$model = "Honyar switch";
				$type = 1;
				break;
			case 0x2721:
				$model = "Camera";
				$type = 100;
				break;
			case 0x42:
			case 0x4e62:
				$model = "DEYE HUMIDIFIER";
				$type = 100;
				break;
			case 0x2d:
			case 0x4f42:
			case 0x4e4d:
				$model = "DOOYA CURTAIN";
				$type = 7;
				break;
			case 0x2723:
			case 0x4eda:
				$model = "HONYAR MS";
				$type = 100;
				break;
			case 0x2727:
			case 0x2726:
			case 0x2724:
			case 0x2725:
				$model = "HONYAR SL";
				$type = 100;
				break;
			case 0x4c:
			case 0x4e6c:
				$model = "MFRESH AIR";
				$type = 100;
				break;
			case 0x271e:
			case 0x2746:
				$model = "PLC (TW_ROUTER)";
				$type = 100;
				break;
			case 0x2774:
			case 0x7530:
			case 0x2742:
			case 0x4e20:
				$model = "MIN/MAX AP/OEM";
				$type = 100;
				break;
			case 0x4e69:
				$model = "LIGHTMATES";
				$type = 100;
				break;
			case 0x4ead:
				$model = "HYSEN";
				$type = 8;
				break;
			default:
				break;
		}

		if($needle=='model') {
			return $model;
		} else {
			return $type;
		}
	}

	protected static function bytearray($size){

		$packet = array();

		for($i = 0 ; $i < $size ; $i++){
			$packet[$i] = 0;
		}

		return $packet;
	}

	protected static function byte2array($data){

		return array_merge(unpack('C*', $data));
	}

	protected static function byte($array){
		$array  = array_map('intval', $array);
		return implode(array_map("chr", $array));
	}

	public static function Discover(){

		$devices = array();

		$s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		socket_connect($s ,'8.8.8.8', 53);  // connecting to a UDP address doesn't send packets
		socket_getsockname($s, $local_ip_address, $port);
		@socket_shutdown($s, 2);
		socket_close($s);

		$cs = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

		if(!$cs){
			return $devices;
		}

		socket_set_option($cs, SOL_SOCKET, SO_REUSEADDR, 1);
		socket_set_option($cs, SOL_SOCKET, SO_BROADCAST, 1);
		socket_set_option($cs, SOL_SOCKET, SO_RCVTIMEO, array('sec'=>1, 'usec'=>0));
		socket_bind($cs, 0, 0);

		$address = explode('.', $local_ip_address);
		$packet = self::bytearray(0x30);

		$timezone = (int)intval(date("Z"))/-3600;
		$year = date("Y");

		if($timezone < 0){
			$packet[0x08] = 0xff + $timezone - 1;
			$packet[0x09] = 0xff;
			$packet[0x0a] = 0xff;
			$packet[0x0b] = 0xff;
		} else {
			$packet[0x08] = $timezone;
			$packet[0x09] = 0;
			$packet[0x0a] = 0;
			$packet[0x0b] = 0;
		}
		$packet[0x0c] = $year & 0xff;
		$packet[0x0d] = $year >> 8;
		$packet[0x0e] = intval(date("i"));
		$packet[0x0f] = intval(date("H"));
		$subyear = substr($year, 2);
		$packet[0x10] = intval($subyear);
		$packet[0x11] = intval(date('N'));
		$packet[0x12] = intval(date("d"));
		$packet[0x13] = intval(date("m"));
		$packet[0x18] = intval($address[0]);
		$packet[0x19] = intval($address[1]);
		$packet[0x1a] = intval($address[2]);
		$packet[0x1b] = intval($address[3]);
		$packet[0x1c] = $port & 0xff;
		$packet[0x1d] = $port >> 8;
		$packet[0x26] = 6;

		$checksum = 0xbeaf;
		for($i = 0 ; $i < sizeof($packet) ; $i++){
			$checksum += $packet[$i];
		}
		$checksum = $checksum & 0xffff;

		$packet[0x20] = $checksum & 0xff;
		$packet[0x21] = $checksum >> 8;

		socket_sendto($cs, self::byte($packet), sizeof($packet), 0, '255.255.255.255', 80);
		while(socket_recvfrom($cs, $response, 2048, 0, $from, $port)){
			$host = '';
			$responsepacket = self::byte2array($response);
			if($responsepacket[0x34] < 16) $devtype = hexdec(sprintf("%x0%x", $responsepacket[0x35], $responsepacket[0x34]));
			else $devtype = hexdec(sprintf("%x%x", $responsepacket[0x35], $responsepacket[0x34]));
			$host_array = array_slice($responsepacket, 0x36, 4);
			$mac = array_slice($responsepacket, 0x3a, 6);
			if (array_slice($responsepacket, 0, 8) !== array(0x5a, 0xa5, 0xaa, 0x55, 0x5a, 0xa5, 0xaa, 0x55)) {
				$host_array = array_reverse($host_array);
			}

			foreach ( $host_array as $ip ) {
				$host .= $ip . ".";
			}

			$host = substr($host, 0, strlen($host) - 1);
			$device = Broadlink::CreateDevice($host, $mac, 80, $devtype);
			if($device != NULL){
				$device->name = str_replace(array("\0","\2"), '', Broadlink::byte(array_slice($responsepacket, 0x40)));
				array_push($devices, $device);
			}
		}

		@socket_shutdown($cs, 2);
		socket_close($cs);

		return $devices;
	}

	function send_packet($command, $payload){

		$cs = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

		if (!$cs) {
			return array();
		}

		socket_set_option($cs, SOL_SOCKET, SO_REUSEADDR, 1);
		socket_set_option($cs, SOL_SOCKET, SO_BROADCAST, 1);
		socket_bind($cs, 0, 0);

		$this->count = ($this->count + 1) & 0xffff;

		$packet = $this->bytearray(0x38);
		$packet[0x00] = 0x5a;
		$packet[0x01] = 0xa5;
		$packet[0x02] = 0xaa;
		$packet[0x03] = 0x55;
		$packet[0x04] = 0x5a;
		$packet[0x05] = 0xa5;
		$packet[0x06] = 0xaa;
		$packet[0x07] = 0x55;
		$packet[0x24] = 0x0b;
		$packet[0x25] = 0x52;
		$packet[0x26] = $command;
		$packet[0x28] = $this->count & 0xff;
		$packet[0x29] = $this->count >> 8;
		$packet[0x2a] = hexdec($this->mac[0]);
		$packet[0x2b] = hexdec($this->mac[1]);
		$packet[0x2c] = hexdec($this->mac[2]);
		$packet[0x2d] = hexdec($this->mac[3]);
		$packet[0x2e] = hexdec($this->mac[4]);
		$packet[0x2f] = hexdec($this->mac[5]);
		$packet[0x30] = $this->id[0];
		$packet[0x31] = $this->id[1];
		$packet[0x32] = $this->id[2];
		$packet[0x33] = $this->id[3];

		$checksum = 0xbeaf;
		for($i = 0 ; $i < sizeof($payload) ; $i++){
			$checksum += $payload[$i];
			$checksum = $checksum & 0xffff;
		}

		$aes = $this->byte2array(aes128_cbc_encrypt($this->key(), $this->byte($payload), $this->iv()));
		$packet[0x34] = $checksum & 0xff;
		$packet[0x35] = $checksum >> 8;

		for($i = 0 ; $i < sizeof($aes) ; $i++){
		array_push($packet, $aes[$i]);
		}

		$checksum = 0xbeaf;
		for($i = 0 ; $i < sizeof($packet) ; $i++){
			$checksum += (int) $packet[$i];
			$checksum = $checksum & 0xffff;
		}

		$packet[0x20] = $checksum & 0xff;
		$packet[0x21] = $checksum >> 8;

		$starttime = time();
		$from = '';
		socket_sendto($cs, $this->byte($packet), sizeof($packet), 0, $this->host, $this->port);
		socket_set_option($cs, SOL_SOCKET, SO_RCVTIMEO, array('sec'=>$this->timeout, 'usec'=>0));

		$ret = socket_recvfrom($cs, $response, 2048, 0, $from, $port);

		@socket_shutdown($cs, 2);
		socket_close($cs);

		if($ret === FALSE)
			return array();

		return $this->byte2array($response);
	}

	public function Auth($id_authorized = null, $key_authorized = null){
		if (!isset($id_authorized) || !isset($key_authorized)) {
			$payload = $this->bytearray(0x50);
			$payload[0x04] = 0x31;
			$payload[0x05] = 0x31;
			$payload[0x06] = 0x31;
			$payload[0x07] = 0x31;
			$payload[0x08] = 0x31;
			$payload[0x09] = 0x31;
			$payload[0x0a] = 0x31;
			$payload[0x0b] = 0x31;
			$payload[0x0c] = 0x31;
			$payload[0x0d] = 0x31;
			$payload[0x0e] = 0x31;
			$payload[0x0f] = 0x31;
			$payload[0x10] = 0x31;
			$payload[0x11] = 0x31;
			$payload[0x12] = 0x31;
			$payload[0x12] = 0x31;
			$payload[0x1e] = 0x01;
			$payload[0x2d] = 0x01;
			$payload[0x30] = ord('T');
			$payload[0x31] = ord('e');
			$payload[0x32] = ord('s');
			$payload[0x33] = ord('t');
			$payload[0x34] = ord(' ');
			$payload[0x35] = ord('1');

			$response = $this->send_packet(0x65, $payload);
			
			if (empty($response))
				return false;

			$enc_payload = array_slice($response, 0x38);

			$payload = $this->byte2array(aes128_cbc_decrypt($this->key(), $this->byte($enc_payload), $this->iv()));

			$this->id = array_slice($payload, 0x00, 4);
			$this->key = array_slice($payload, 0x04, 16);

			$data['id']=$this->id;
			$data['key']=$this->key;
			$data['time']=time();

			return $data;
		} else {
			$this->id = $id_authorized;
			$this->key = $key_authorized;
		}
	}

	public static function Cloud($nickname = "", $userid = "", $loginsession = "") {

		return new Cloud($nickname, $userid, $loginsession);

	}

	protected static function str2hex_array($str){

		$str_arr = str_split(strToUpper($str), 2);
		$str_hex = array();
		for ($i=0; $i < count($str_arr); $i++){
			$ord1 = ord($str_arr[$i][0])-48;
			$ord2 = ord($str_arr[$i][1])-48;
			if ($ord1 > 16) $ord1 = $ord1 - 7;
			if ($ord2 > 16) $ord2 = $ord2 - 7;
			$str_hex[$i] = $ord1 * 16 + $ord2;
		}
		return $str_hex;
	}

	public function ping() {

		$timeout   = 500;
		$precision = 5;
		$udp_port  = 33439;
		$request   = 'broadlink-monitoring-system';

		switch (self::model($this->devtype)) {
			case 999: //???
				$ping_type = 'UDP';
				$retries = 3;
				break;
			default: // 1:SP2, 4:MP1 and other
				$ping_type = 'ICMP';
				$retries = 1;
		}

		if (!$this->host) {
			$this->ping_response = 'Destination address not specified';
			$this->ping_time     = 'down';
			$this->ping_status   = false;
			return false;
		}

		$to_sec  = floor($timeout/1000);
		$to_usec = ($timeout%1000)*1000;

		$this->ping_status   = false;
		$this->ping_time     = 'down';
		$this->ping_response = 'default';

		if ($ping_type === 'ICMP') {

			if (substr_count(strtolower(PHP_OS), 'sun')) {
				$result = shell_exec('ping '.$this->host);
			}else if (substr_count(strtolower(PHP_OS), 'hpux')) {
				$result = shell_exec('ping -m '.ceil($timeout/1000).' -n '.$retries.' '.$this->host);
			}else if (substr_count(strtolower(PHP_OS), 'mac')) {
				$result = shell_exec('ping -t '.ceil($timeout/1000).' -c '.$retries.' '.$this->host);
			}else if (substr_count(strtolower(PHP_OS), 'freebsd')) {
				$result = shell_exec('ping -t '.ceil($timeout/1000).' -c '.$retries.' '.$this->host);
			}else if (substr_count(strtolower(PHP_OS), 'darwin')) {
				$result = shell_exec('ping -t '.ceil($timeout/1000).' -c '.$retries.' '.$this->host);
			}else if (substr_count(strtolower(PHP_OS), 'bsd')) {
				$result = shell_exec('ping -w '.ceil($timeout/1000).' -c '.$retries.' '.$this->host);
			}else if (substr_count(strtolower(PHP_OS), 'aix')) {
				$result = shell_exec('ping -i '.ceil($timeout/1000).' -c '.$retries.' '.$this->host);
			}else if (substr_count(strtolower(PHP_OS), 'winnt')) {
				$result = shell_exec('chcp 437 && ping -w '.$timeout.' -n '.$retries.' '.$this->host);
			} else {
				$pattern  = bin2hex($request);
				$result = shell_exec('ping -W '.ceil($timeout/1000).' -c '.$retries.' -p '.$pattern.' '.$this->host.' 2>&1');
				if (substr_count($result, 'unknown host') && file_exists('/bin/ping6')) {
					$result = shell_exec('ping6 -W '.ceil($timeout/1000).' -c '.$retries.' -p '.$pattern.' '.$this->host);
				}
			}

			if (strtolower(PHP_OS) != 'winnt') {
				$position = strpos($result, 'min/avg/max');

				if ($position > 0) {
					$output  = trim(str_replace(' ms', '', substr($result, $position)));
					$pieces  = explode('=', $output);
					$results = explode('/', $pieces[1]);
					$this->ping_status   = true;
					$this->ping_time     = $results[1];
					$this->ping_response = 'ICMP Ping Success ('.$this->ping_time.' ms)';
					return true;
				} else {
					$this->ping_status   = false;
					$this->ping_time     = 'down';
					$this->ping_response = 'ICMP ping Timed out';
					return false;
				}
			} else {
				$position = strpos($result, 'Minimum');

				if ($position > 0) {
					$output  = trim(substr($result, $position));
					$pieces  = explode(',', $output);
					$results = explode('=', $pieces[2]);
					$this->ping_status   = true;
					$this->ping_time     = trim(str_replace('ms', '', $results[1]));
					$this->ping_response = 'ICMP Ping Success ('.$this->ping_time.' ms)';
					return true;
				} else {
					$this->ping_status   = false;
					$this->ping_time     = 'down';
					$this->ping_response = 'ICMP ping Timed out';
					return false;
				}
			}

		} else

		if ($ping_type === 'UDP') {
			if (substr_count($this->host,':') > 0) {
				if (defined('AF_INET6')) {
					$cs = socket_create(AF_INET6, SOCK_DGRAM, SOL_UDP);
				} else {
					$this->ping_response = 'PHP version does not support IPv6';
					$this->ping_time     = 'down';
					$this->ping_status   = false;
					return false;
				}
			} else {
				$cs = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
			}
			socket_set_nonblock($cs);
			socket_connect($cs, $this->host, $udp_port);
			$request = chr(0) . chr(1) . chr(0) . $request . chr(0);

			$error = '';
			$retry_count = 0;
			while (true) {
				if ($retry_count >= $retries) {
					$this->ping_status   = false;
					$this->ping_time     = 'down';
					$this->ping_response = 'UDP ping error: '.$error;
					@socket_shutdown($cs, 2);
					socket_close($cs);
					return false;
				}

				$timer_start_time = microtime(true);

				socket_write($cs, $request, strlen($request));

				$w = $f = array();
				$r = array($cs);
				$num_changed_sockets = socket_select($r, $w, $f, $to_sec, $to_usec);
				if ($num_changed_sockets === false) {
					$error = 'socket_select() failed, reason: ' . socket_strerror(socket_last_error());
				} else {
					switch($num_changed_sockets) {
					case 2: /* response received, so host is available */
					case 1:
						$start_time = $timer_start_time;
						$end_time = microtime(true);
						$time = number_format ($end_time - $start_time, $precision);
						$code = @socket_recv($cs, $this->reply, 256, 0);
						$err = socket_last_error($cs);
						$this->ping_status   = true;
						$this->ping_time     = $time * 1000;
						$this->ping_response = "UDP Ping Success (".$this->ping_time." ms)";
						@socket_shutdown($cs, 2);
						socket_close($cs);
						return true;
						break;
					case 0: /* timeout */
						$error = 'timeout';
						break;
					}
				}
				$retry_count++;
			}
		}
	}
}

class SP1 extends Broadlink{

	function __construct($h = "", $m = "", $p = 80, $d = 0x2712) {

		parent::__construct($h, $m, $p, $d);

	}

	public function Set_Power($state){

		$packet = self::bytearray(4);
		$packet[0] = $state;

		$this->send_packet(0x66, $packet);
	}

}

class SP2 extends Broadlink{

	function __construct($h = "", $m = "", $p = 80, $d = 0x2712) {

		parent::__construct($h, $m, $p, $d);

	}

	public function Set_Power($state){

		$packet = self::bytearray(16);
		$packet[0] = 0x02;
		$packet[4] = (int)$state;

		$this->send_packet(0x6a, $packet);
	}

	public function Check_Power(){

		$packet = self::bytearray(16);
		$packet[0] = 0x01;

		$response = $this->send_packet(0x6a, $packet);
		if (empty($response))
			return false;

		$err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));

		if($err == 0){
			$enc_payload = array_slice($response, 0x38);

			if(count($enc_payload) > 0){
				$payload = $this->byte2array(aes128_cbc_decrypt($this->key(), $this->byte($enc_payload), $this->iv()));
				if ($payload[0x4] & 0x01) $data['power_state'] = 1; else $data['power_state'] = 0;
				if ($payload[0x4] & 0x02) $data['light_state'] = 1; else $data['light_state'] = 0;	//for sp3
				return $data;
			}

		}

		return false;

	}

	public function Check_Energy(){

		$packet = self::bytearray(16);
			$packet[0x00] = 0x08;
			$packet[0x02] = 0xFE;
			$packet[0x03] = 0x01;
			$packet[0x04] = 0x05;
			$packet[0x05] = 0x01;
			$packet[0x09] = 0x2D;
		$response = $this->send_packet(0x6a, $packet);
		if (empty($response))
			return false;

		$err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));

		if($err == 0){
			$enc_payload = array_slice($response, 0x38);

			if(count($enc_payload) > 0){
				$payload = $this->byte2array(aes128_cbc_decrypt($this->key(), $this->byte($enc_payload), $this->iv()));
				$data= (dechex($payload[0x7])*10000+dechex($payload[0x6])*100+dechex($payload[0x5]))/100;
				return $data;
			}

		}

		return false;

	}
	public function Check_Energy_SP2(){

		$packet = self::bytearray(16);
		$packet[0x00] = 0x04;
		$packet[0x04] = 0xF2;
		$packet[0x05] = 0x20;
		$packet[0x06] = 0x02;
		$response = $this->send_packet(0x6a, $packet);
		if (empty($response))
			return false;

		$err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));

		if($err == 0){
			$enc_payload = array_slice($response, 0x38);

			if(count($enc_payload) > 0){
				$payload = $this->byte2array(aes128_cbc_decrypt($this->key(), $this->byte($enc_payload), $this->iv()));
				$data= (dechex($payload[0x4])*10000+dechex($payload[0x5])*100+dechex($payload[0x6]))/100;
				return $data;
			}

		}

		return false;

	}
}

class A1 extends Broadlink{

	function __construct($h = "", $m = "", $p = 80) {

		 parent::__construct($h, $m, $p, 0x2714);

	}

	public function Check_sensors(){

		$data = array();

		$packet = self::bytearray(16);
		$packet[0] = 0x01;

		$response = $this->send_packet(0x6a, $packet);
		if (empty($response))
			return false;

		$err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));

		if($err == 0){
			$enc_payload = array_slice($response, 0x38);

			if(count($enc_payload) > 0){

				$payload = $this->byte2array(aes128_cbc_decrypt($this->key(), $this->byte($enc_payload), $this->iv()));

				$data['temperature'] = ($payload[0x4] * 10 + $payload[0x5]) / 10.0;
				$data['humidity'] = ($payload[0x6] * 10 + $payload[0x7]) / 10.0;
				$data['light'] = $payload[0x8];
				$data['air_quality'] = $payload[0x0a];
				$data['noise'] = $payload[0x0c];

				switch ($data['light']) {
					case 0:
						$data['light_word'] = constant('LANG_BR_DARK');
						break;
					case 1:
						$data['light_word'] = constant('LANG_BR_DIM');
						break;
					case 2:
						$data['light_word'] = constant('LANG_BR_NORMAL');
						break;
					case 3:
						$data['light_word'] = constant('LANG_BR_BRIGHT');
						break;
					default:
						$data['light_word'] = constant('LANG_BR_UNKNOWN');
						break;
				}

				switch ($data['air_quality']) {
					case 0:
						$data['air_quality_word'] = constant('LANG_BR_EXCELLENT');
						break;
					case 1:
						$data['air_quality_word'] = constant('LANG_BR_GOOD');
						break;
					case 2:
						$data['air_quality_word'] = constant('LANG_BR_NORMAL');
						break;
					case 3:
						$data['air_quality_word'] = constant('LANG_BR_BAD');
						break;
					default:
						$data['air_quality_word'] = constant('LANG_BR_UNKNOWN');
						break;
				}

				switch ($data['noise']) {
					case 0:
						$data['noise_word'] = constant('LANG_BR_QUIET');
						break;
					case 1:
						$data['noise_word'] = constant('LANG_BR_NORMAL');
						break;
					case 2:
						$data['noise_word'] = constant('LANG_BR_NOISY');
						break;
					case 3:
						$data['noise_word'] = constant('LANG_BR_EXTREME');
						break;
					default:
						$data['noise_word'] = constant('LANG_BR_UNKNOWN');
						break;
				}

			}

		}

		return $data;

	}

}

class RM extends Broadlink{

	function __construct($h = "", $m = "", $p = 80, $d = 0x2712) {

		 parent::__construct($h, $m, $p, $d);

	}

	public function Enter_learning(){

		$packet = self::bytearray(16);
		$packet[0] = 0x03;
		$this->send_packet(0x6a, $packet);

	}

	public function Send_data($data){

		$packet = self::bytearray(4);
		$packet[0] = 0x02;

		if(is_array($data)){
			$packet = array_merge($packet, $data);
		}
		else{
			for($i = 0 ; $i < strlen($data) ; $i+=2){
				array_push($packet, hexdec(substr($data, $i, 2)));
			}
		}

		$this->send_packet(0x6a, $packet);
	}
	
	public function Check_data(){

		$code = array();

		$packet = self::bytearray(16);

		$packet[0] = 0x04;
		$response = $this->send_packet(0x6a, $packet);
		if (empty($response))
			return false;

		$err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));

		if($err == 0){
			$enc_payload = array_slice($response, 0x38);

			if(count($enc_payload) > 0){

				$payload = $this->byte2array(aes128_cbc_decrypt($this->key(), $this->byte($enc_payload), $this->iv()));

				$code = array_slice($payload, 0x04);
			}
		}

		return $code;
	}

	public function Check_temperature(){

		$temp = false;

		$packet = $this->bytearray(16);

		$packet[0] = 0x01;
		$response = $this->send_packet(0x6a, $packet);
		if (empty($response))
			return false;

		$err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));

		if($err == 0){
			$enc_payload = array_slice($response, 0x38);

			if(count($enc_payload) > 0){

				$payload = $this->byte2array(aes128_cbc_decrypt($this->key(), $this->byte($enc_payload), $this->iv()));

				$temp = ($payload[0x4] * 10 + $payload[0x5]) / 10.0;

			}
		}

		return $temp;

	}

}

class RM4 extends Broadlink{

	function __construct($h = "", $m = "", $p = 80, $d = 0x2712) {

		 parent::__construct($h, $m, $p, $d);

	}

	public function Enter_learning(){

		$packet = self::bytearray(16);
		$packet[0] = 0x04;
		$packet[1] = 0x00;
		$packet[2] = 0x03;
		$this->send_packet(0x6a, $packet);

	}

	public function Send_data($data){

		$packet = self::bytearray(4);
		$packet[0] = 0xd0;
		$packet[1] = 0x00;
		$packet[2] = 0x02;

		if(is_array($data)){
			$packet = array_merge($packet, $data);
		}
		else{
			for($i = 0 ; $i < strlen($data) ; $i+=2){
				array_push($packet, hexdec(substr($data, $i, 2)));
			}
		}

		$checksum = sizeof($packet) - 2;
		$packet[0] = $checksum & 0xFF;
		$packet[1] = $checksum >> 8;

		$response = $this->send_packet(0x6a, $packet);
        $err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));
        return($err);
		
	}

	public function Check_data(){

		$code = array();

		$packet = self::bytearray(16);
		$packet[0] = 0x04;
		$packet[1] = 0x00;
		$packet[2] = 0x04;
		$response = $this->send_packet(0x6a, $packet);
		if (empty($response))
			return false;

		$err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));

		if($err == 0){
			$enc_payload = array_slice($response, 0x38);

			if(count($enc_payload) > 0){

				$payload = $this->byte2array(aes128_cbc_decrypt($this->key(), $this->byte($enc_payload), $this->iv()));

				$code = array_slice($payload, 0x04);
			}
		}

		return $code;
	}
	public function Check_temperature(){
		unset($temp);
		$packet = $this->bytearray(16);
    $packet[0] = 0x04;
		$packet[1] = 0x00;
		$packet[2] = 0x01;
    $packet[3] = 0x04;
		$response = $this->send_packet(0x6a, $packet);
		if (empty($response))
			return false;

		$err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));

		if($err == 0){
			$enc_payload = array_slice($response, 0x38);

			if(count($enc_payload) > 0){

				$payload = $this->byte2array(aes128_cbc_decrypt($this->key(), $this->byte($enc_payload), $this->iv()));
				$temp = ($payload[0x4] * 10 + $payload[0x5]) / 100.0;

			}
		}

		return $temp;

	}
}

class MP1 extends Broadlink{

	function __construct($h = "", $m = "", $p = 80, $d = 0x4EB5) {

		parent::__construct($h, $m, $p, $d);

	}

	public function Set_Power_Mask($sid_mask, $state){

		$packet = self::bytearray(16);
		$packet[0x00] = 0x0d;
		$packet[0x02] = 0xa5;
		$packet[0x03] = 0xa5;
		$packet[0x04] = 0x5a;
		$packet[0x05] = 0x5a;
		$packet[0x06] = 0xb2 + ($state ? ($sid_mask<<1) : $sid_mask);
		$packet[0x07] = 0xc0;
		$packet[0x08] = 0x02;
		$packet[0x0a] = 0x03;
		$packet[0x0d] = $sid_mask;
		$packet[0x0e] = $state ? $sid_mask : 0;

		$this->send_packet(0x6a, $packet);
	}

	public function Set_Power($sid, $state){

		$sid_mask = 0x01 << ($sid - 1);

		$this->Set_Power_Mask($sid_mask, $state);
	}

	public function Check_Power_Raw(){

		$packet = self::bytearray(16);
		$packet[0x00] = 0x0a;
		$packet[0x02] = 0xa5;
		$packet[0x03] = 0xa5;
		$packet[0x04] = 0x5a;
		$packet[0x05] = 0x5a;
		$packet[0x06] = 0xae;
		$packet[0x07] = 0xc0;
		$packet[0x08] = 0x01;

		$response = $this->send_packet(0x6a, $packet);
		if (empty($response))
			return null;

		$err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));

		if($err == 0){
			$enc_payload = array_slice($response, 0x38);

			if(count($enc_payload) > 0){

				$payload = $this->byte2array(aes128_cbc_decrypt($this->key(), $this->byte($enc_payload), $this->iv()));
				return $payload[0x0e];
			}

		}

		return null;


	}

	public function Check_Power(){

		$data = array();

		if(!is_null($state = $this->Check_Power_Raw())){
			if ($state & 0x01) $data[0] = 1; else $data[0] = 0;
			if ($state & 0x02) $data[1] = 1; else $data[1] = 0;
			if ($state & 0x04) $data[2] = 1; else $data[2] = 0;
			if ($state & 0x08) $data[3] = 1; else $data[3] = 0;
		}
		return $data;

	}

}

class MS1 extends Broadlink{

	function __construct($h = "", $m = "", $p = 80, $d = 0x271F) {

		parent::__construct($h, $m, $p, $d);

	}
	public function send_str($ascii){

		$packet = self::bytearray(16);
		$ascii='LEN:'.strlen($ascii).chr(10).$ascii;
		$hex = '';
		for ($i = 0; $i < strlen($ascii); $i++) {
			$byte = strtoupper(dechex(ord($ascii[$i])));
			$byte = str_repeat('0', 2 - strlen($byte)).$byte;
			$hex.=$byte.' ';
		}
		$hex_arr=explode(' ', $hex);
		$i=0;
		foreach($hex_arr as $hex_arr_byte) {
			$packet[$i]='0x'.$hex_arr_byte;
			$i++;
		}

		$response = $this->send_packet(0x6a, $packet);
		if (empty($response))
			return false;

		$err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));

		if($err == 0){
			$enc_payload = array_slice($response, 0x38);

			if(count($enc_payload) > 0){
				$payload = $this->byte2array(aes128_cbc_decrypt($this->key(), $this->byte($enc_payload), $this->iv()));
				for($i=0; $i<count($payload); $i++) {
					$payload[$i]=dechex($payload[$i]);
					if (strlen($payload[$i])==1) $payload[$i]='0'.$payload[$i];
					if ($payload[$i]=='00') $payload[$i]='';
				}
				$hex=implode('', $payload);
				for($i=14;$i<strlen($hex);$i+=2) $str .= chr(hexdec(substr($hex,$i,2)));
				return $str;
			}

		}

		return false;

	}

}

class S1 extends Broadlink{

	function __construct($h = "", $m = "", $p = 80, $d = 0x2722) {

		 parent::__construct($h, $m, $p, $d);

	}

	protected function sensors($payload){

		$data = array();

		$data['col_sensors'] = $payload[0x04];
		for ($i=0;$i<$data['col_sensors'];$i++) {
			$offset = 0x05+$i*0x53;
			$status = $payload[$offset+0x00]*256+$payload[$offset+0x01];
			$data[$i]['sensor_number'] = $payload[$offset+0x02];
			$data[$i]['product_id'] = $payload[$offset+0x04];
			$data[$i]['photo'] = 'http://jp-clouddb.ibroadlink.com/sensor/picture/'.$data[$i]['product_id'].'.png';
			$data[$i]['location'] = '';
			switch ($data[$i]['product_id']) {
				case 0x21:
					$data[$i]['product_type'] = 'Wall Motion Sensor';

					if ( $status & 0x10 )
					{
						$data[$i]['status'] = 1;
						$data[$i]['status_val'] = constant('LANG_BRS1_PERSON_DETECTED');
					}
					else
					{
						$data[$i]['status'] = 0;
						$data[$i]['status_val'] = constant('LANG_BRS1_NO_PERSON');
					}

					if ( $status & 0x40 )
					{
						$data[$i]['batterylow'] = 1;
					}
					else
					{
						$data[$i]['batterylow'] = 0;
					}
					if ( $status & 0x20 )
					{
						$data[$i]['tamper'] = 1;
					}
					else
					{
						$data[$i]['tamper'] = 0;
					}
					break;
				case 0x31:
					$data[$i]['product_type'] = 'Door Sensor';
					if ( $status & 0x10 )
					{
						$data[$i]['status'] = 1;
						$data[$i]['status_val'] = constant('LANG_BRS1_OPENED');
					}
					else
					{
						$data[$i]['status'] = 0;
						$data[$i]['status_val'] = constant('LANG_BRS1_CLOSED');
					}

					if ( $status & 0x40 )
					{
						$data[$i]['batterylow'] = 1;
					}
					else
					{
						$data[$i]['batterylow'] = 0;
					}

					if ( $status & 0x20 )
					{
						$data[$i]['tamper'] = 1;
					}
					else
					{
						$data[$i]['tamper'] = 0;
					}
					break;
				case 0x91:
					$data[$i]['product_type'] = 'Key Fob';
					$data[$i]['status']=$status;
					switch ($status) {
						case 0x0000:
							$data[$i]['status_val'] = constant('LANG_BRS1_CANCEL_SOS');
							break;
						case 0x0010:
							$data[$i]['status_val'] = constant('LANG_BRS1_DISARM');
							break;
						case 0x0020:
							$data[$i]['status_val'] = constant('LANG_BRS1_ARMED_FULL');
							break;
						case 0x0040:
							$data[$i]['status_val'] = constant('LANG_BRS1_ARMED_PART');
							break;
						case 0x0080:
							$data[$i]['status_val'] = 'SOS';
							break;
						default:
							$data[$i]['status_val'] = constant('LANG_BRS1_UNKNOWN').$status;
					}
					break;
				// for future:
				case 0x40:
					$data[$i]['product_type'] = 'Gaz Sensor';
					switch ($status) {
						case 0x0000:
						case 0x0010:
						default:
							$data[$i]['status'] = constant('LANG_BRS1_UNKNOWN').$status;
					}
					break;
				case 0x51:
					$data[$i]['product_type'] = 'Fire Sensor';
					switch ($status) {
						case 0x0000:
						case 0x0010:
						default:
							$data[$i]['status'] = constant('LANG_BRS1_UNKNOWN').$status;
					}
					break;
				default:
					$data[$i]['product_type'] = 'Unknown: '.$data[$i]['product_id'];
			}
			$data[$i]['product_name'] = ""; for ($j=$offset+0x05;$j<$offset+0x15;$j++) if (!$payload[$j]) $data[$i]['product_name'] .= chr($payload[$j]);
			$data[$i]['device_id'] = $payload[$offset+0x1e]*16777216+$payload[$offset+0x1d]*65536+$payload[$offset+0x1c]*256+$payload[$offset+0x1b];
			$data[$i]['s1_pwd'] = dechex($payload[$offset+0x22]).dechex($payload[$offset+0x21]).dechex($payload[$offset+0x20]).dechex($payload[$offset+0x1f]);

			switch ($payload[$offset+0x23]) {
				case 0x00:
					$data[$i]['armFull'] = false;
					$data[$i]['armPart'] = false;
					break;
				case 0x02:
					$data[$i]['armFull'] = true;
					$data[$i]['armPart'] = false;
					break;
				case 0x03:
					$data[$i]['armFull'] = true;
					$data[$i]['armPart'] = true;
					break;
				default:
					$data[$i]['armFull'] = true;
					$data[$i]['armPart'] = false;
			}

			switch ($payload[$offset+0x25]) {
				case 0x00:
					$data[$i]['zone'] = 'Not specified';
					break;
				case 0x01:
					$data[$i]['zone'] = 'Living room';
					break;
				case 0x02:
					$data[$i]['zone'] = 'Main bedroom';
					break;
				case 0x03:
					$data[$i]['zone'] = 'Secondary room 1';
					break;
				case 0x04:
					$data[$i]['zone'] = 'Secondary room 2';
					break;
				case 0x05:
					$data[$i]['zone'] = 'Kitchen';
					break;
				case 0x06:
					$data[$i]['zone'] = 'Bathroom';
					break;
				case 0x07:
					$data[$i]['zone'] = 'Veranda';
					break;
				case 0x08:
					$data[$i]['zone'] = 'Garage';
					break;
				default:
					$data[$i]['zone'] = constant('LANG_BRS1_UNKNOWN').$payload[$offset+0x2b];
			}

			$data[$i]['delay_online'] 			= $payload[$offset+0x39]*256+$payload[$offset+0x38];
			$data[$i]['delay_battery'] 			= $payload[$offset+0x41]*256+$payload[$offset+0x40];
			$data[$i]['delay_tamper_switch'] 	= $payload[$offset+0x49]*256+$payload[$offset+0x48];
			$data[$i]['delay_detect'] 			= $payload[$offset+0x50]*256+$payload[$offset+0x4f];
		}
		return $data;
	}

	public function Check_Sensors(){

		$data = array();

		$packet = self::bytearray(16);
		$packet[0] = 0x06;

		$response = $this->send_packet(0x6a, $packet);
		if (empty($response))
			return $data;

		$err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));

		if($err == 0){
			$enc_payload = array_slice($response, 0x38);
			if(count($enc_payload) > 0){
				$payload = $this->byte2array(aes128_cbc_decrypt($this->key(), $this->byte($enc_payload), $this->iv()));
				$data = $this->sensors($payload);
			}
		}
		return $data;
	}

	public function Check_Status(){

		$data = array();

		$packet = self::bytearray(16);
		$packet[0] = 0x12;

		$response = $this->send_packet(0x6a, $packet);
		if (empty($response))
			return $data;

		$err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));

		if($err == 0){
			$enc_payload = array_slice($response, 0x38);
			if(count($enc_payload) > 0){
				$payload = $this->byte2array(aes128_cbc_decrypt($this->key(), $this->byte($enc_payload), $this->iv()));
				$data['status'] = $payload[0x04];
				$data['delay_time_m'] = $payload[0x08];
				$data['delay_time_s'] = $payload[0x09];
				$data['alarm_buzzing'] = $payload[0x0a];
				$data['alarm_buzzing_duration'] = $payload[0x0b];
				$data['beep_mute'] = $payload[0x0d];
				$data['alarm_detector'] = $payload[0x28];
				switch ($data['status']) {
					case 0x00:
						$data['status_val'] = constant('LANG_BRS1_DISARM');
						break;
					case 0x01:
						$data['status_val'] = constant('LANG_BRS1_PART');
						break;
					case 0x02:
						$data['status_val'] = constant('LANG_BRS1_FULL');
						break;
					default:
						$data['status'] = constant('LANG_BRS1_UNKNOWN').$data['status'];
				}
			}
		}
		return $data;
	}

	public function Set_Arm($params){

		$data = array();

		$packet = self::bytearray(48);

		$packet[0x00] = 0x11;
		$packet[0x04] = $params['status']; //2 - full, 1 - part, 0 - disarm
		$packet[0x08] = $params['delay_time_m'];
		$packet[0x09] = $params['delay_time_s'];
		$packet[0x0a] = $params['alarm_buzzing'];
		$packet[0x0b] = $params['alarm_buzzing_duration'];
		$packet[0x0d] = $params['beep_mute'];
		$packet[0x28] = $params['alarm_detector'];

		$response = $this->send_packet(0x6a, $packet);
		if (empty($response))
			return $data;

		$err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));

		if($err == 0){
			$enc_payload = array_slice($response, 0x38);
			if(count($enc_payload) > 0){
				$payload = $this->byte2array(aes128_cbc_decrypt($this->key(), $this->byte($enc_payload), $this->iv()));
				$data['status'] = $payload[0x04];
				$data['delay_time_m'] = $payload[0x08];
				$data['delay_time_s'] = $payload[0x09];
				$data['alarm_buzzing'] = $payload[0x0a];
				$data['alarm_buzzing_duration'] = $payload[0x0b];
				$data['beep_mute'] = $payload[0x0d];
				$data['alarm_detector'] = $payload[0x28];
				switch ($data['status']) {
					case 0x00:
						$data['status_val'] = constant('LANG_BRS1_DISARM');;
						break;
					case 0x01:
						$data['status_val'] = constant('LANG_BRS1_PART');
						break;
					case 0x02:
						$data['status_val'] = constant('LANG_BRS1_FULL');
						break;
					default:
						$data['status'] = constant('LANG_BRS1_UNKNOWN').$data['status'];
				}
			}
		}
		return $data;
	}

	public function Add_Sensor($serialnumb){

		$data = array();

		$serial[0] = mb_strtoupper($serialnumb[0].$serialnumb[1], "UTF-8");
		if ($serial[0] != 'BL') {
			return false;
		}
		for ($i=2; $i < strlen($serialnumb)-1; $i+=2){
			$serial[$i/2] = hexdec($serialnumb[$i].$serialnumb[$i+1]);
		}

		$packet = self::bytearray(96);
		$packet[0x00] = 0x07;
		$packet[0x05] = $serial[2];
		$packet[0x06] = $serial[3];
		switch ($serial[3]) {
			case 0x21:	//http://jp-clouddb.ibroadlink.com/sensor/picture/33.png /35.png and /36.png
				$packet[0x07] = ord('W');
				$packet[0x08] = ord('a');
				$packet[0x09] = ord('l');
				$packet[0x0A] = ord('l');
				$packet[0x0B] = ord(' ');
				$packet[0x0C] = ord('M');
				$packet[0x0D] = ord('o');
				$packet[0x0E] = ord('t');
				$packet[0x0F] = ord('i');
				$packet[0x10] = ord('o');
				$packet[0x11] = ord('n');
				$packet[0x12] = ord(' ');
				$packet[0x13] = ord('S');
				$packet[0x14] = ord('e');
				$packet[0x15] = ord('n');
				$packet[0x16] = ord('s');
				$packet[0x17] = ord('o');
				$packet[0x18] = ord('r');
				// ..0x1C - zeros
				break;
			case 0x31:	//http://jp-clouddb.ibroadlink.com/sensor/picture/49.png
				$packet[0x07] = ord('D');
				$packet[0x08] = ord('o');
				$packet[0x09] = ord('o');
				$packet[0x0A] = ord('r');
				$packet[0x0B] = ord(' ');
				$packet[0x0C] = ord('S');
				$packet[0x0D] = ord('e');
				$packet[0x0E] = ord('n');
				$packet[0x0F] = ord('s');
				$packet[0x10] = ord('o');
				$packet[0x11] = ord('r');
				// ..0x1C - zeros
				break;
			case 0x40:	//http://jp-clouddb.ibroadlink.com/sensor/picture/64.png
				$packet[0x07] = ord('G');
				$packet[0x08] = ord('a');
				$packet[0x09] = ord('z');
				$packet[0x0A] = ord(' ');
				$packet[0x0B] = ord('S');
				$packet[0x0C] = ord('e');
				$packet[0x0D] = ord('n');
				$packet[0x0E] = ord('s');
				$packet[0x0F] = ord('o');
				$packet[0x10] = ord('r');
				// ..0x1C - zeros
				break;
			case 0x51:	//http://jp-clouddb.ibroadlink.com/sensor/picture/81.png
				$packet[0x07] = ord('F');
				$packet[0x08] = ord('i');
				$packet[0x09] = ord('r');
				$packet[0x0A] = ord('e');
				$packet[0x0B] = ord(' ');
				$packet[0x0C] = ord('S');
				$packet[0x0D] = ord('e');
				$packet[0x0E] = ord('n');
				$packet[0x0F] = ord('s');
				$packet[0x10] = ord('o');
				$packet[0x11] = ord('r');
				// ..0x1C - zeros
				break;
			case 0x91:	//http://jp-clouddb.ibroadlink.com/sensor/picture/145.png
				$packet[0x07] = ord('K');
				$packet[0x08] = ord('e');
				$packet[0x09] = ord('y');
				$packet[0x0A] = ord(' ');
				$packet[0x0B] = ord('F');
				$packet[0x0C] = ord('o');
				$packet[0x0D] = ord('b');
				// ..0x1C - zeros
				break;
			default:	//http://jp-clouddb.ibroadlink.com/sensor/picture/224.png /239.png
				$packet[0x07] = ord('U');
				$packet[0x08] = ord('n');
				$packet[0x09] = ord('k');
				$packet[0x0A] = ord('n');
				$packet[0x0B] = ord('o');
				$packet[0x0C] = ord('w');
				$packet[0x0D] = ord('n');
				// ..0x1C - zeros
		}
		$packet[0x1D] = $serial[4];
		$packet[0x1E] = $serial[5];
		$packet[0x1F] = $serial[6];
		$packet[0x20] = $serial[7];
		switch ($serial[3]) {
			case 0x21:	//s1_pwd = 0x774eecd6
				$packet[0x21] = 0xd6;
				$packet[0x22] = 0xec;
				$packet[0x23] = 0x4e;
				$packet[0x24] = 0x77;
				break;
			case 0x31:	//s1_pwd = 0x95a1faf1
				$packet[0x21] = 0xf1;
				$packet[0x22] = 0xfa;
				$packet[0x23] = 0xa1;
				$packet[0x24] = 0x95;
				break;
			case 0x91:	//s1_pwd = 0x5d6f7647
				$packet[0x21] = 0x47;
				$packet[0x22] = 0x76;
				$packet[0x23] = 0x6f;
				$packet[0x24] = 0x5d;
				break;
		}
		$packet[0x25] = 0x02;	//0x00 = Full-arm disabled, Part-arm disabled
								//0x02 = Full-arm enabled, Part-arm disabled
								//0x03 = Full-arm enabled, Part-arm enabled
		//$packet[0x26] = 0x00;
		if (($serial[3] == 0x21)||($serial[3] == 0x31)) {
			$packet[0x27] = 0x00;	//0x00 = Not specified
									//0x01 = Living room
									//0x02 = Main bedroom
									//0x03 = Secondary room 1
									//0x04 = Secondary room 2
									//0x05 = Kitchen
									//0x06 = Bathroom
									//0x07 = Veranda
									//0x08 = Garage
		}
		if ($serial[3] == 0x31) {
			$packet[0x28] = 0x00;	//0x00 = Drawer (for "Door Sensor")
									//0x01 = Door
									//0x02 = Window
		}
		//0x29..0x34:	zeros
		//Online Status
		$packet[0x35] = 0x1f;
		if ($serial[3] == 0x91) {
			$packet[0x36] = 0x01;
			//$packet[0x37] = 0x00;
			//$packet[0x38] = 0x00;
			$packet[0x39] = 0x0a;
		}
		if ($serial[3] == 0x31) {
			$packet[0x3a] = 0x2d;	//Delay time 45 sec (0x00 0x2D)
			$packet[0x3b] = 0x00;	//Delay time 45 sec (0x00 0x2D)
		}
		//$packet[0x3c] = 0x00;

		//Battery
		$packet[0x3d] = 0x1e;
		if (($serial[3] == 0x21)||($serial[3] == 0x31)) {
			$packet[0x3e] = 0x08;
		}
		//$packet[0x3f] = 0x00;
		//$packet[0x40] = 0x00;
		//$packet[0x41] = 0x00;
		$packet[0x42] = 0x00;	//Delay time 0 sec (0x00 0x00)
		$packet[0x43] = 0x00;	//Delay time 0 sec (0x00 0x00)
		//$packet[0x44] = 0x00;

		//Tamper Switch
		$packet[0x45] = 0x1d;
		if (($serial[3] == 0x21)||($serial[3] == 0x31)) {
			$packet[0x46] = 0x08;
		}
		//$packet[0x47] = 0x00;
		//$packet[0x48] = 0x00;
		//$packet[0x49] = 0x00;
		$packet[0x4a] = 0x00;	//Delay time 0 sec (0x00 0x00)
		$packet[0x4b] = 0x00;	//Delay time 0 sec (0x00 0x00)
		//$packet[0x4c] = 0x00;

		//Detected Status
		$packet[0x4d] = 0x1c;
		if (($serial[3] == 0x21)||($serial[3] == 0x31)) {
			$packet[0x4e] = 0x0b;
		}
		//$packet[0x4f] = 0x00;
		//$packet[0x50] = 0x00;
		//$packet[0x51] = 0x00;
		if ($serial[3] == 0x21) {
			$packet[0x52] = 0x68;	//Delay time 6 min (0x01 0x68)
			$packet[0x53] = 0x01;	//Delay time 6 min (0x01 0x68)
		}
		//0x54..0x5f:	zeros

		$response = $this->send_packet(0x6a, $packet);
		if (empty($response))
			return $data;

		$err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));

		if($err == 0){
			$enc_payload = array_slice($response, 0x38);
			if(count($enc_payload) > 0){
				$payload = $this->byte2array(aes128_cbc_decrypt($this->key(), $this->byte($enc_payload), $this->iv()));
				$data = $this->sensors($payload);
			}
		}
		return $data;
	}


}

class DOOYA extends Broadlink{

	function __construct($h = "", $m = "", $p = 80, $d = 0x2d) {

		parent::__construct($h, $m, $p, $d);

	}
	public function send_req($magic1=0x06, $magic2=0x5d){
		$data = array();
		$packet = self::bytearray(16);
		$packet[0] = 0x09;
		$packet[2] = 0xbb;
		$packet[3] = $magic1;
		$packet[4] = $magic2;
		$packet[9] = 0xfa;
		$packet[10] = 0x44;
		$response=$this->send_packet(0x6a, $packet);
		if (empty($response))
			return $data;

		$err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));
		if($err == 0){
			$enc_payload = array_slice($response, 0x38);
			if(count($enc_payload) > 0){
				$payload = $this->byte2array(aes128_cbc_decrypt($this->key(), $this->byte($enc_payload), $this->iv()));
				$data = $payload[4];
			}
		}
		return $data;
	}
	public function get_level(){
		return $this->send_req();
	}
	public function set_level($level){
		$now_lvl=$this->get_level();
		if ($level!=$now_lvl) {
			if ($level==0) {
				$response=$this->send_req(0x02, 0x00); //закрыть
				return;
			} elseif ($level==100) {
				$response=$this->send_req(0x01, 0x00); //открыть
				return;
			}
			if ($now_lvl>$level) {
				$response=$this->send_req(0x02, 0x00);
				$action='close';
			} else {
				$response=$this->send_req(0x01, 0x00);
				$action='open';
			}
			if($action=='close') {
				while($now_lvl>$level) {
					$now_lvl=$this->get_level();
					usleep(200000);
				}
				$this->send_req(0x03, 0x00);
			} else {
				while($now_lvl<$level) {
					$now_lvl=$this->get_level();
					usleep(200000);
				}
				$this->send_req(0x03, 0x00);
			}
		}
	}
}

class HYSEN extends Broadlink{

	function __construct($h = "", $m = "", $p = 80, $d = 0x4ead) {

		parent::__construct($h, $m, $p, $d);

	}

	protected static function CRC16($data){
			$crc = 0xFFFF;
			for ($i = 0; $i < strlen($data); $i++){
				$crc ^=ord($data[$i]);
				for ($j = 8; $j !=0; $j--){
					if (($crc & 0x0001) !=0){
						$crc >>= 1;
						$crc ^= 0xA001;
					}
					else
						$crc >>= 1;
					}
			}
		return $crc;
		}

	protected static function prepare_request($payload){
			$crc = self::CRC16(implode(array_map("chr",$payload)));
			$packet = self::bytearray(2);
			$packet[0] = (int)(sizeof($payload) + 2);
			$packet = array_merge($packet,$payload);
			$crc1 = (int)$crc & 255;
			$crc2 = (int)($crc >> 8) & 255;
			$packet[] = $crc1;
			$packet[] = $crc2;
		return $packet;
	}

	public function get_status(){
		$data = array();
		$payload = self::prepare_request(array(0x01,0x03,0x00,0x00,0x00,0x16));
		$response=$this->send_packet(0x6a, $payload);
		if (empty($response))
			return $data;

		$err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));
		if($err == 0){
			$enc_payload = array_slice($response, 0x38);
			if(count($enc_payload) > 0 ){
				$payload = $this->byte2array(aes128_cbc_decrypt($this->key(), $this->byte($enc_payload), $this->iv()));
				$payload = array_slice($payload, 2);
				// Quick validate data received:
				if( (count($payload) > 22) // have at least 23 bytes
				    && ($payload[11]>$payload[12]) // temperature range is valid
				    && ($payload[22]>0) && ($payload[22]<8) // Week day is in range
				    && ($payload[19]<24) && ($payload[20]<60) // Hours and minutes are in range
				  ) {
					$data['remote_lock'] =  $payload[3] & 1;
					$data['power'] =  $payload[4] & 1;
					$data['active'] =  ($payload[4] >> 4) & 1;
					$data['temp_manual'] =  ($payload[4] >> 6) & 1;
					$data['room_temp'] =  ($payload[5] & 255) / 2.0;
					$data['thermostat_temp'] =  ($payload[6] & 255)/2.0;
					$data['auto_mode'] =  $payload[7] & 15;
					$data['loop_mode'] =  ($payload[7] >> 4) & 15;
					$data['sensor'] = $payload[8];
					$data['osv'] = $payload[9];
					$data['dif'] = $payload[10];
					$data['svh'] = $payload[11];
					$data['svl'] = $payload[12];
					$data['room_temp_adj'] = (($payload[13] << 8) + $payload[14])/2.0;
					if ($data['room_temp_adj'] > 32767) {
						$data['room_temp_adj'] = 32767 - $data['room_temp_adj'];
					}
					$data['fre'] = $payload[15];
					$data['poweron'] = $payload[16];
					$data['external_temp'] = ($payload[18] & 255)/2.0;
					$data['hour'] =  $payload[19];
					$data['min'] =  $payload[20];
//					$data['sec'] =  $payload[21];
					$data['dayofweek'] =  $payload[22];

					//*** Thermostat time validation
					$timeH = (int)date("G", time());
					$timeM = (int)date("i", time());
					$timeS = (int)date("s", time());
					$timeD = (int)date("N", time());

                                        // Get current time in week seconds
					$timeCurrent = ( (($timeD-1)*24 + $timeH) * 60 + $timeM ) * 60 + $timeS;

                                        // Get thermostat time in week seconds
					$timeTherm = ( (($data['dayofweek']-1)*24 + $data['hour']) * 60 + $data['min'] ) * 60 + $payload[21];

					// Delta time, seconds
					$timeDelta = abs( $timeCurrent - $timeTherm);

					// Compensate overflow with 1 minute confidence
					if( $timeDelta >= 7*24*60*60 - 60 ) $timeDelta = abs(7*24*60*60 - $timeDelta);

					// Check if thermostat time differs more than 30seconds
					if ( $timeDelta > 30 ) {
						self::set_time($timeH,$timeM,$timeS,$timeD);
					}
				}
			}
		}
		return $data;
	}

	public function get_schedule(){
		$data = array();
		$payload = self::prepare_request(array(0x01,0x03,0x00,0x00,0x00,0x16));
		$response=$this->send_packet(0x6a, $payload);
		$err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));
		if (empty($response))
			return $data;

		if($err == 0){
			$data = array();
			$enc_payload = array_slice($response, 0x38);
			if(count($enc_payload) > 0){
				$payload = $this->byte2array(aes128_cbc_decrypt($this->key(), $this->byte($enc_payload), $this->iv()));
				$payload = array_slice($payload, 2);

				for ($i = 0; $i < 6; $i++){
					$data[0][$i]['start_hour'] = $payload[2*$i + 23];
					$data[0][$i]['start_minute'] = $payload[2*$i + 24];
					$data[0][$i]['temp'] = $payload[$i + 39]/2.0;
				}

				for ($i = 0; $i < 2; $i++){
					$data[1][$i]['start_hour'] = $payload[2*($i+6) + 23];
					$data[1][$i]['start_minute'] = $payload[2*($i+6) + 24];
					$data[1][$i]['temp'] = $payload[($i+6) + 39]/2.0;
				}
			}
		}
		return $data;
	}

	public function get_temp(){
		$payload = self::prepare_request(array(0x01,0x03,0x00,0x00,0x00,0x08));
		$response=$this->send_packet(0x6a, $payload);
		if (empty($response))
			return false;

		$err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));
		if($err == 0){
			$payload = $this->byte2array(aes128_cbc_decrypt($this->key(), $this->byte($enc_payload), $this->iv()));
		}
		return ($payload[0x05] / 2.0);
	}

	public function set_power($remote_lock,$power){
		$payload = self::prepare_request(array(0x01,0x06,0x00,0x00,$remote_lock,$power));
		$response=$this->send_packet(0x6a, $payload);
	}

	public function set_mode($mode_byte,$sensor){
		$payload = self::prepare_request(array(0x01,0x06,0x00,0x02,$mode_byte,$sensor));
		$response=$this->send_packet(0x6a, $payload);
	}

	public function set_temp($param){
		$payload = self::prepare_request(array(0x01,0x06,0x00,0x01,0x00,(int)($param * 2)));
		$response=$this->send_packet(0x6a, $payload);
	}

	public function set_time($hour,$minute,$second,$day){
		$payload = self::prepare_request(array(0x01,0x10,0x00,0x08,0x00,0x02,0x04,$hour,$minute,$second,$day));
		$response=$this->send_packet(0x6a, $payload);
	}

	public function set_advanced($loop_mode,$sensor,$osv,$dif,$svh,$svl,$adj1,$adj2,$fre,$poweron){
		$payload = self::prepare_request(array(0x01,0x10,0x00,0x02,0x00,0x05,0x0a,$loop_mode,$sensor,$osv,$dif,$svh,$svl,$adj1,$adj2,$fre,$poweron));
		$response=$this->send_packet(0x6a, $payload);
	}

	public function set_schedule($param){
		$pararr = json_decode($param,true);
		$input_payload = array(0x01,0x10,0x00,0x0a,0x00,0x0c,0x18);
		for ($i = 0; $i < 6; $i++){
			$input_payload = array_push($input_payload,$pararr[0][$i]['start_hour'],$pararr[0][$i]['start_minute']);
		}
		for ($i = 0; $i < 2; $i++){
			$input_payload = array_push($input_payload,$pararr[1][$i]['start_hour'],$pararr[1][$i]['start_minute']);
		}
		for ($i = 0; $i < 6; $i++){
			$input_payload = array_push($input_payload,((int)$pararr[0][$i]['temp'] * 2));
		}
		for ($i = 0; $i < 2; $i++){
			$input_payload = array_push($input_payload,((int)$pararr[1][$i]['temp'] * 2));
		}
		$input_payload = array_merge(array(0x01,0x10,0x00,0x0a,0x00,0x0c,0x18),$input_payload);
		$payload = self::prepare_request($input_payload);
		$this->send_packet(0x6a, $payload);
	}

}

class UNK extends Broadlink{

	function __construct($h = "", $m = "", $p = 80, $d = 0x2712) {

		 parent::__construct($h, $m, $p, $d);

	}

	public function some_action($params){//пример команды

		$packet = self::bytearray(16);
		$packet[0] = 0x02; //стартовый байт, определяющий действие (команда)
		$packet[4] = 1; // управляющий байт в команде
		$this->send_packet(0x6a, $packet);
	}

	public function some_req(){

		$packet = self::bytearray(16); //размер массива может быть другой...но как правило 16 или 48 байт
		$packet[0] = 0x01; //стартовый байт, определяющий действие (запрос)
		$response = $this->send_packet(0x6a, $packet);
		if (empty($response))
			return false;

		$err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));

		if($err == 0){
			$enc_payload = array_slice($response, 0x38);

			if(count($enc_payload) > 0){

				$payload = $this->byte2array(aes128_cbc_decrypt($this->key(), $this->byte($enc_payload), $this->iv()));
				return $payload;
			}

		}

		return false;


	}

}

class Cloud extends Broadlink{
	protected $authorized = false;
	protected $loginsession;
	protected $userid;
	protected $nickname;
	protected $workdir;
	protected static $file = 'bl_buckup.zip';

	function __construct($nickname = "", $userid = "", $loginsession = "") {

		$this->loginsession = $loginsession;
		$this->userid = $userid;
		$this->nickname = $nickname;
		$this->workdir = ROOT.'cms' . DIRECTORY_SEPARATOR . 'cached' . DIRECTORY_SEPARATOR . 'broadlink'.DIRECTORY_SEPARATOR;
		if (($nickname === "") || ($userid === "") || ($loginsession === "")) {
			$this->authorized = false;
		} else {
			$this->authorized = true;
		}
	}

	protected function geturi($host, $post, $headers, $request = 0) {

		$url = "https://".$host.$post;
		$timeout = 7;
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		if (preg_match("/\bPOST\b/i", $headers[0])) curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		if ($request) curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
		$result["msg"] = curl_exec($curl);
		$result["error"] = curl_errno($curl);
		if ($result["error"]) {
			$result["msg"] = curl_error($curl);
		}
		return $result;
	}

	protected function get_token($timestamp) {
		return md5(base64_encode(sha1("\x42\x72\x6F\x61\x64\x6C\x69\x6E\x6B\x3A290".$timestamp,true)));
	}

	public function Auth($email = "", $password = "") {

		if (($email === "") || (strlen($password) < 6)) {
			$result["error"] = -1005;
			$result["msg"] = "Data Error";
			return $result;
		}

		$authiv = array(-22, -86, -86, 58, -69, 88, 98, -94, 25, 24, -75, 119, 29, 22, 21, -86);
		$password = sha1($password."4969fj#k23#");
		$data_str = str_pad('{"email":"'.$email.'","password":"'.$password.'"}',  112, "\0");
		$token = md5('{"email":"'.$email.'","password":"'.$password.'"}'."xgx3d*fe3478\$ukx");

		$host = "account.ibroadlink.com";
		$post = "/v1/account/login/api?email=".$email."&password=".$password."&serialVersionUID=2297929119272048467";
		$headers = array(
			"GET ".$post." HTTP/1.1",
			"language: zh_cn",
			"serialVersionUID: -6225108491617746123",
			"Host: ".$host,
			"Connection: Keep-Alive"
		);
		$result = $this->geturi($host, $post, $headers);
		if ($result["error"]) {
			return $result;
		}
		$result = json_decode($result["msg"], true);

		if (($result["error"] != 0) || ($result["msg"] != "ok")) {
			return $result;
		}

		$timestamp = $result["timestamp"];
		$key = $this->byte($this->str2hex_array($result["key"]));
		$request = aes128_cbc_encrypt($key, $data_str, $this->byte($authiv));
		$post = "/v2/account/login/info";
		$host = "secure.ibroadlink.com";
		$headers = array(
			"POST ".$post." HTTP/1.1",
			"Timestamp: ".$timestamp,
			"Token: ".$token,
			"language: zh_cn",
			"serialVersionUID: -6225108491617746123",
			"Content-Length: 112",
			"Host: ".$host,
			"Connection: Keep-Alive",
			"Expect: 100-continue"
		);
		$result = $this->geturi($host, $post, $headers, $request);
		if ($result["error"]) {
			return $result;
		}
		$result = json_decode($result["msg"], true);
		return $result;
	}

	public function GetUserInfo() {

		if (!$this->authorized) {
			$result["error"] = -1009;
			$result["msg"] = "Authorization Required";
			return $result;
		}

		$post = "/v1/account/userinfo/get";
		$host = "account.ibroadlink.com";
		$headers = array(
			"GET ".$post." HTTP/1.1",
			"LOGINSESSION: ".$this->loginsession,
			"USERID: ".$this->userid,
			"language: zh_cn",
			"serialVersionUID: -6225108491617746123",
			"Host: ".$host,
			"Connection: Keep-Alive"
		);
		$result = $this->geturi($host, $post, $headers);
		if ($result["error"]) {
			return $result;
		}
		$result = json_decode($result["msg"], true);
		$this->nickname = $result["nickname"];
		return $result;
	}

	public function GetListBackups() {

		if (!$this->authorized) {
			$result["error"] = -1009;
			$result["msg"] = "Authorization Required";
			return $result;
		}

		$timestamp = round(microtime(true) * 1000);
		$post = "/rest/1.0/backup?method=list&user=".$this->nickname."&id=".$this->userid."&amp;timestamp=".$timestamp."&token=".$this->get_token($timestamp);
		$host = "ebackup.ibroadlink.com";
		$headers = array(
			"GET ".$post." HTTP/1.1",
			"accountType: bl",
			"reqUserId: ".$this->userid,
			"reqUserSession: ".$this->loginsession,
			"serialVersionUID: -855048957473660878",
			"Host: ".$host,
			"Connection: Keep-Alive"
		);
		$result = $this->geturi($host, $post, $headers, 0);
		if ($result["error"]) {
			return $result;
		}
		$result = json_decode($result["msg"], true);
		$result["error"] = 0;
		return $result;
	}

	public function GetBackup($pathname) {

		if (!$this->authorized) {
			$result["error"] = -1009;
			$result["msg"] = "Authorization Required";
			return $result;
		}

		$BLbackupFolderName = "SharedData";
		$timestamp = round(microtime(true) * 1000);
		$post = "/rest/1.0/backup?method=download&pathname=".$pathname."&amp;timestamp=".$timestamp."&token=".$this->get_token($timestamp);
		$host = "ebackup.ibroadlink.com";
		$timestamp = $timestamp + 56;
		$headers = array(
			"GET ".$post." HTTP/1.1",
			"timestamp: ".$timestamp,
			"token: ".$this->get_token($timestamp),
			"accountType: bl",
			"reqUserId: ".$this->userid,
			"reqUserSession: ".$this->loginsession,
			"serialVersionUID: -855048957473660878",
			"Host: ".$host,
			"Connection: Keep-Alive"
		);
		$result = $this->geturi($host, $post, $headers);
		if ($result["error"]) {
			return $result;
		}

		file_put_contents($this->workdir.self::$file, $result["msg"]);
		if (!is_dir($this->workdir)){
			@mkdir(ROOT . 'cms'. 'cached', 0777);
			@mkdir($this->workdir, 0777);
		}
		if (file_exists($this->workdir.self::$file)) {
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				if(file_exists($_SERVER['WINDIR']."\unzip.exe")) {
					exec(sprintf("rd /s /q ".$this->workdir.$BLbackupFolderName));
					exec('unzip '.$this->workdir.self::$file.' -d '.$this->workdir, $output, $res);
				} else {
					$result["need_unzip"]=true;
				}
			} else {
				if (file_exists($this->workdir.$BLbackupFolderName)) exec(sprintf("rm -rf ".$this->workdir.$BLbackupFolderName));
				exec('unzip '.$this->workdir.self::$file.' -d '.$this->workdir, $output, $res);
				exec("find ".$this->workdir.$BLbackupFolderName." -exec chmod 0777 {} +");
			}
			unlink($this->workdir.self::$file);
		}
		if (file_exists($this->workdir.$BLbackupFolderName.DIRECTORY_SEPARATOR.'jsonSubIr')) {
			$result["error"] = 0;
			$result["msg"] = $this->workdir.$BLbackupFolderName;
		} elseif($result["need_unzip"]) {
			$result["error"] = 404;
			$result["msg"] = 'unzip.exe not found in windows dir';
		} else {
			$result["error"] = -9999;
			$result["msg"] = "Something Went Wrong";
		}
		return $result;
	}

	public function GetLastBackup() {

		$LastBackupFile = "";
		$result = $this->GetListBackups();
		if ($result["code"] == "200") {
			$count_files = count($result["list"]);
			$LastBackupFile = $result["list"][$count_files-1]["pathname"];
			$result = $this->GetBackup($LastBackupFile);
		}
		return $result;
	}
}
?>
