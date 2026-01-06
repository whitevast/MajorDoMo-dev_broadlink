<?php
include_once("broadlink.class.php");

global $session;

if ($this->owner->name == 'panel') {
    $out['CONTROLPANEL'] = 1;
}

$qry = "1";
global $save_qry;
if ($save_qry) {
    $qry = $session->data['ssdp_devices_qry'];
} else {
    $session->data['ssdp_devices_qry'] = $qry;
}
if (!$qry) $qry = "1";

$res = Scan();
$out['RESULT'] = $res;
if ($res[0]['ID']) {
$current .= "in if $res[0]['ID']\n";
    $total = count($res);
    for ($i = 0; $i < $total; $i++) {
        $tmp = explode(' ', $res[$i]['UPDATED']);
        $res[$i]['UPDATED'] = fromDBDate($tmp[0]) . " " . $tmp[1];
    }
    $out['RESULT'] = $res;
}


function Scan(){
	$result = array();
	$devices = Broadlink::Discover();
	foreach ($devices as $device) {
		$obj = array();
		$obj['DEVTYPE'] = $device->devtype();
		$obj['NAME'] = $device->name();
		$obj['MAC'] = $device->mac();
		$obj['HOST'] = $device->host();
		$obj['MODEL'] = $device->devmodel();
		array_push($result, $obj);
	}
    return $result;
}

function array_search_result($array, $key, $value){
    foreach ($array as $k => $v) {
        if (array_key_exists($key, $v) && ($v[$key] == $value)) {
            return true;
        }
    }
}