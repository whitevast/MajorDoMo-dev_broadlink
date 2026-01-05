<?php

chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");
set_time_limit(0);

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");
$ctl = new control_modules();
include_once(DIR_MODULES . 'dev_broadlink/dev_broadlink.class.php');
$br = new dev_broadlink();
$br->getConfig();

$old_second = date('s');
$old_minute = date('i');
$old_hour = date('h');

// Инициализация счетчиков
$s2 = 1;
$s3 = 1;
$s5 = 1;
$s20 = 1;
$m5 = 1;
$m10 = 1;

$tmp = SQLSelectOne("SELECT ID FROM dev_httpbrige_devices LIMIT 1");
if (!$tmp['ID']) {
   // Обновляем статус перед выходом, чтобы показать, что цикл проверялся
   setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
   DebMes("Cycle " . basename(__FILE__) . " exited: no devices found in dev_httpbrige_devices", 'boot');
   exit; // no devices added -- no need to run this cycle
}
echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;
$latest_check=0;
$checkEvery=5; // poll every 5 seconds

while (1)
{
   setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
   if ((time()-$latest_check)>$checkEvery) {
    $latest_check=time();
   }
	$s = date('s');
   	$m = date('i');
	$h = date('h');
	   if ($s != $old_second)
	   {
			$br->check_params('1s');
			if($s2>=2) {
				$br->check_params('2s');
				$s2=1;
			} else {
				$s2++;
			}
			if($s3>=3) {
				$br->check_params('3s');
				$s3=1;
			} else {
				$s3++;
			}
			if($s5>=5) {
				$br->check_params('5s');
				$s5=1;
			} else {
				$s5++;
			}
			if($s20>=20) {
				$br->check_params('20s');
				$s20=1;
			} else {
				$s20++;
			}
			$old_second = $s;
	   }	
	   if ($m != $old_minute)
	   {
			$br->check_params('1m');
			if($m5>=5) {
				$br->check_params('5m');
				$m5=1;
			} else {
				$m5++;
			}
			$old_minute = $m;
			if($m10>=10) {
				$br->check_params('10m');
				$m10=1;
			} else {
				$m10++;
			}
	   }

	   if ($h != $old_hour)
	   {
			if($br->config['IP_UPDATE']=='need') $br->refrash_ip();
			$br->check_params('1h');
			$old_hour = $h;
	   }

	   if (file_exists('./reboot') || IsSet($_GET['onetime']))
	   {
		  $db->Disconnect();
		  exit;
	   }
   sleep(1);
}
DebMes("Unexpected close of cycle: " . basename(__FILE__));
