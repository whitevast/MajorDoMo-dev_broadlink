<?php
/**
* BroadlinkHTTPBrige
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 16:06:00 [Jun 28, 2016])
*/
//
//
include_once(DIR_MODULES.'dev_broadlink/broadlink.class.php');
class dev_broadlink extends module {
/**
* dev_httpbrige
*
* Module class constructor
*
* @access private
*/
function dev_broadlink() {
  $this->name="dev_broadlink";
  $this->title="Broadlink";
  $this->module_category="<#LANG_SECTION_DEVICES#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->data_source)) {
  $p["data_source"]=$this->data_source;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 if (IsSet($this->page)) {
  $p["page"]=$this->page;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;

  global $mode;
  global $mac;
  global $host;
  global $title;
  global $devtype;
  global $view_mode;
  global $edit_mode;
  global $data_source;
  global $tab;
  global $title_new;

   if (isset($title)) {
   $this->title=$title;
  }
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($mac)) {
   $this->mac=$mac;
  }
    if (isset($host)) {
   $this->host=$host;
  }
  if (isset($devtype)) {
   $this->devtype=$devtype;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($data_source)) {
   $this->data_source=$data_source;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
  if (isset($title_new)) {
   $this->title_new=$title_new;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['DATA_SOURCE']=$this->data_source;
  $out['TAB']=$this->tab;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
 $this->getConfig();
 $out['API_URL']=$this->config['API_URL'];
 if (!$out['API_URL']) {
  $out['API_URL']='http://';
 }
 $out['API_TYPE']=$this->config['API'];
 $out['IP_UPDATE']=$this->config['IP_UPDATE'];
 $out['VAL_UPDATE']=$this->config['VAL_UPDATE'];
 $out['VAL_PING']=$this->config['VAL_PING'];
 $out['DATA_EXPORT_TYPE']=$this->config['DATA_EXPORT_TYPE'];
 if ($this->view_mode=='update_settings') {
   global $api_type;
   $this->config['API']=$api_type;
   global $api_url;
   $this->config['API_URL']=$api_url;
   global $ip_update;
   if($ip_update==true) $this->config['IP_UPDATE']='need'; else $this->config['IP_UPDATE']='not';
   global $val_update;
   if($val_update==true) $this->config['VAL_UPDATE']='on_change'; else $this->config['VAL_UPDATE']='always';
   global $val_ping;
   if($val_ping==true) $this->config['VAL_PING']='true'; else $this->config['VAL_PING']='false';
   global $data_export_type;
   $this->config['DATA_EXPORT_TYPE']=$data_export_type;
   $this->saveConfig();
   $this->redirect("?");
 }
 if ($this->mode=='save_api_rm') {
	 $this->config['API']='rm-brige';
	 $this->saveConfig();
	 $this->redirect("?");
 }
 if ($this->mode=='save_api_hb') {
	 $this->config['API']='httpbrige';
	 $this->saveConfig();
	 $this->redirect("?");
 }
 if ($this->mode=='save_api_php') {
	 $this->config['API']='php';
	 $this->saveConfig();
	 $this->redirect("?");
 }
 if ($this->mode=='check_params') {
	 $this->check_params();
 }


 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='dev_httpbrige_devices' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_dev_httpbrige_devices') {
   $this->search_dev_httpbrige_devices($out);
  }
  if ($this->view_mode=='edit_dev_httpbrige_devices') {
   $this->edit_dev_httpbrige_devices($out, $this->id);
  }
  if ($this->view_mode=='delete_dev_httpbrige_devices') {
   $this->delete_dev_httpbrige_devices($this->id);
   $this->redirect("?data_source=dev_httpbrige_devices");
  }
  if ($this->view_mode=='broadlink_devices_scan') {
        $this->broadlink_devices_scan($out);
  }
  if ($this->view_mode=='cloud') {
        $this->cloud_func($out);
  }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='dev_broadlink_commands') {
  if ($this->view_mode=='' || $this->view_mode=='search_dev_broadlink_commands') {
   $this->search_dev_broadlink_commands($out);
  }
  if ($this->view_mode=='edit_dev_broadlink_commands') {
   $this->edit_dev_broadlink_commands($out, $this->id);
  }
 }
}

function api($params) {
    if($_REQUEST['ACTION']=='learn') {
        $did=$_REQUEST['ID'];
        $dname = $_REQUEST['CMD_NAME'];
        $info=SQLSelectOne("SELECT * FROM dev_httpbrige_devices WHERE ID='$did'");
        $rm = Broadlink::CreateDevice($info['IP'], $info['MAC'], 80, $info['DEVTYPE']);
        $decoded_keys=json_decode($info['KEYS']);
		$rm->Auth($decoded_keys->id, $decoded_keys->key);
		$rm->Enter_learning();
		$i = 0;
        do {
        	sleep(1);
        	$json['hex'] = $rm->Check_data();
        } while((count($json['hex']) == 0) && ($i++ < 15));

        $json['hex_number'] = '';
        foreach ($json['hex'] as $value) {
        	$json['hex_number'] .= sprintf("%02x", $value);
        }

		if(count($json['hex']) > 0){
			$prop=array('TITLE'=>(isset($dname) ? $dname : 'new_command'),'VALUE'=>$json['hex_number'],'DEVICE_ID'=>$info['ID']);
			$new_id=SQLInsert('dev_broadlink_commands',$prop);
			//занесена
			addToOperationsQueue('br_learn', 'ok', $prop['TITLE']);
		} else {
			//ошибка
			addToOperationsQueue('br_learn', 'error', 'Ошибка чтения команды');
		}
    }
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
    $this->admin($out);
    if ($this->ajax) {
        global $op;
        if ($op == 'api_learn_start') {
            header("HTTP/1.0: 200 OK\n");
            header('Content-Type: text/html; charset=utf-8');
            $did = $_GET['did'];
            $dname = $_GET['dname'];
            echo '{"result":"ok"}'.PHP_EOL;
            callAPI('/api/module/dev_broadlink','GET',array('ACTION'=>'learn', 'ID'=>$did, 'CMD_NAME'=>$dname));
            setTimeOut('broad_learn_wait', '', 10);
        } elseif ($op == 'api_learn_check') {
            header("HTTP/1.0: 200 OK\n");
            header('Content-Type: text/html; charset=utf-8');

            $result=checkOperationsQueue('br_learn');
            if(isset($result[0]['TOPIC'])) {
                if($result[0]['DATANAME']=='ok') {
                    echo '{"result":"ok", "command":"'.$result[0]['DATAVALUE'].'"}'.PHP_EOL;
                } elseif($result[0]['DATANAME']=='error') {
                    echo '{"error":"'.$result[0]['DATAVALUE'].'"}'.PHP_EOL;
                }
            } else {
                if(timeOutExists('broad_learn_wait')) {
                  echo '{"result":"wait"}'.PHP_EOL;
                } else {
                  echo '{"error":"Истекло время записи команды"}'.PHP_EOL;
                }
            }

        }
    }
}
/**
* dev_httpbrige_devices search
*
* @access public
*/
 function search_dev_httpbrige_devices(&$out) {
	require(DIR_MODULES.$this->name.'/dev_httpbrige_devices_search.inc.php');
 }
/**
* broadlink_devices_scan search
*
* @access public
*/
 function broadlink_devices_scan(&$out) {
    require(DIR_MODULES.$this->name.'/broadlink_devices_scan.inc.php');
 }

 function cloud_func(&$out) {
    require(DIR_MODULES.$this->name.'/dev_broadlink_cloud.inc.php');
 }
/**
* dev_httpbrige_devices edit/add
*
* @access public
*/
 function edit_dev_httpbrige_devices(&$out, $id) {
	require(DIR_MODULES.$this->name.'/dev_httpbrige_devices_edit.inc.php');
 }
/**
* dev_httpbrige_devices delete record
*
* @access public
*/
 function delete_dev_httpbrige_devices($id) {
  $rec=SQLSelectOne("SELECT * FROM dev_httpbrige_devices WHERE ID='$id'");
  if ($rec['TYPE'] == 'sp2' || $rec['TYPE'] == 'spmini' || $rec['TYPE'] == 'sp3' || $rec['TYPE'] == 'sc1') {
	removeLinkedProperty($rec['LINKED_OBJECT'], 'status', $this->name);
  }
  if ($rec['TYPE'] == 'sp3') {
	removeLinkedProperty($rec['LINKED_OBJECT'], 'lightstatus', $this->name);
  }
  SQLExec("DELETE FROM dev_httpbrige_devices WHERE ID='".$rec['ID']."'");
  SQLExec("DELETE FROM dev_broadlink_commands WHERE DEVICE_ID='".$rec['ID']."'");
 }
/**
* dev_broadlink_commands search
*
* @access public
*/
 function search_dev_broadlink_commands(&$out) {
  require(DIR_MODULES.$this->name.'/dev_broadlink_commands_search.inc.php');
 }
/**
* dev_broadlink_commands edit/add
*
* @access public
*/
 function edit_dev_broadlink_commands(&$out, $id) {
  require(DIR_MODULES.$this->name.'/dev_broadlink_commands_edit.inc.php');
 }
 function propertySetHandle($object, $property, $value) {
  $this->getConfig();
  if ($this->config['API_URL']=='httpbrige') {
   $table='dev_httpbrige_devices';
   $properties=SQLSelect("SELECT * FROM $table WHERE LINKED_OBJECT LIKE '".DBSafe($object)."'");
   $total = is_array($properties) ? count($properties) : 0;
   if ($total) {
    for($i=0;$i<$total;$i++) {
     //to-do
		if ($properties[$i]['TYPE'] == 'sp2' || $properties[$i]['TYPE'] == 'spmini' || $properties[$i]['TYPE'] == 'sp3') {
			if ($property == 'status') {
				if (gg($properties[$i]['LINKED_OBJECT'].'.'.'status') == 1 ) {
					$api_command=$this->config['API_URL'].'/?devMAC='.$properties[$i]['MAC'].'&action=on';
					getUrl($api_command);
				} else {
					$api_command=$this->config['API_URL'].'/?devMAC='.$properties[$i]['MAC'].'&action=off';
					getUrl($api_command);
				}
			}
			if ($property == 'lightstatus') {
				if (gg($properties[$i]['LINKED_OBJECT'].'.'.'status') == 1 ) {
					$api_command=$this->config['API_URL'].'/?devMAC='.$properties[$i]['MAC'].'&action=lighton';
					getUrl($api_command);
				} else {
					$api_command=$this->config['API_URL'].'/?devMAC='.$properties[$i]['MAC'].'&action=lightoff';
					getUrl($api_command);
				}
			}
		}
    }
   }
  } else {
	$table='dev_broadlink_commands';
	$properties=SQLSelect("SELECT * FROM $table WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."'");
	$total = is_array($properties) ? count($properties) : 0;
	if ($total) {
    for($i=0;$i<$total;$i++) {
		$id=$properties[$i]['DEVICE_ID'];
		$rec=SQLSelectOne("SELECT * FROM dev_httpbrige_devices WHERE ID='$id'");
		$rm = Broadlink::CreateDevice($rec['IP'], $rec['MAC'], 80, $rec['DEVTYPE']);
		$decoded_keys=json_decode($rec['KEYS']);
		$rm->Auth($decoded_keys->id, $decoded_keys->key);
		if ($rec['TYPE']=='rm' || $rec['TYPE']=='rm3' || $rec['TYPE']=='rm4' || $rec['TYPE']=='rm4pro') {
			 if ($value==1) {
					$data=$properties[$i]['VALUE'];
					$rm->Send_data($data);
					sg($object.".".$property, 0);
			 }
		} elseif ($rec['TYPE']=='sp2' || $rec['TYPE']=='spmini' || $rec['TYPE']=='sp3s' || $rec['TYPE'] == 'sc1') {
			if($properties[$i]['TITLE']=='status') {
					$rm->Set_Power($value);
					$properties[$i]['VALUE']=$value;
					SQLUpdate('dev_broadlink_commands', $properties[$i]);
			}
		} elseif ($rec['TYPE']=='sp3') {
				$properties[$i]['VALUE']=$value;
				SQLUpdate('dev_broadlink_commands', $properties[$i]);
				$powerstat=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='status' AND DEVICE_ID='".$rec['ID']."'");
				$lstat=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='lightstatus' AND DEVICE_ID='".$rec['ID']."'");
				$rm->Set_Power($powerstat['VALUE']+$lstat['VALUE']*2);
		} elseif ($rec['TYPE']=='mp1') {
				$rm->Set_Power(substr($properties[$i]['TITLE'], -1), $value);
				$properties[$i]['VALUE']=$value;
				SQLUpdate('dev_broadlink_commands', $properties[$i]);
		} elseif ($rec['TYPE']=='ms1') {
				if($properties[$i]['TITLE']=='volume') {
					$rm->send_str("{\"command\":\"vol-setting\",\"value\":$value}");
				} elseif ($value==1) {
					if($properties[$i]['TITLE']=='ButtonPower') $rm->send_str('{"command":"key","value":2}');
					if($properties[$i]['TITLE']=='ButtonMute') $rm->send_str('{"command":"key","value":3}');
					if($properties[$i]['TITLE']=='ButtonPause') $rm->send_str('{"command":"key","value":9}');
					if($properties[$i]['TITLE']=='ButtonPlay') $rm->send_str('{"command":"key","value":1}');
					if($properties[$i]['TITLE']=='ButtonNext') $rm->send_str('{"command":"key","value":7}');
					if($properties[$i]['TITLE']=='ButtonPrev') $rm->send_str('{"command":"key","value":8}');
					if($properties[$i]['TITLE']=='ButtonVolUp') $rm->send_str('{"command":"key","value":4}');
					if($properties[$i]['TITLE']=='ButtonVolDown') $rm->send_str('{"command":"key","value":5}');
					if($properties[$i]['TITLE']=='ButtonAux') $rm->send_str('{"command":"key","value":6}');
				}
		} elseif ($rec['TYPE']=='s1') {
			if($properties[$i]['TITLE']=='status') {
				$arm_pack=json_decode($properties[$i]['VALUE'], true);
				$arm_pack['status']=$value;
				$rm->Set_Arm($arm_pack);
				$properties[$i]['VALUE']=json_encode($arm_pack);
				SQLUpdate('dev_broadlink_commands', $properties[$i]);
			}
		} elseif ($rec['TYPE']=='dooya') {
			if($properties[$i]['TITLE']=='level') {
					$rm->set_level($value);
					$properties[$i]['VALUE']=$value;
					SQLUpdate('dev_broadlink_commands', $properties[$i]);
			}
		} elseif ($rec['TYPE']=='hysen') {
                        if($properties[$i]['TITLE']=='remote_lock') {
					$properties[$i]['VALUE']=$value;
					SQLUpdate('dev_broadlink_commands', $properties[$i]);
					$power=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='power' AND DEVICE_ID='".$rec['ID']."'");
					$rm->set_power($value,$power['VALUE']);
                        }
                        if($properties[$i]['TITLE']=='power') {
					$properties[$i]['VALUE']=$value;
					SQLUpdate('dev_broadlink_commands', $properties[$i]);
					$remote_lock=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='remote_lock' AND DEVICE_ID='".$rec['ID']."'");
					$rm->set_power($remote_lock['VALUE'],$value);
                        }
                        if($properties[$i]['TITLE']=='loop_mode') {
					$properties[$i]['VALUE']=$value;
					SQLUpdate('dev_broadlink_commands', $properties[$i]);
					$auto_mode=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='auto_mode' AND DEVICE_ID='".$rec['ID']."'");
					$sensor=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='sensor' AND DEVICE_ID='".$rec['ID']."'");
					$mode_byte = ( $value << 4) + $auto_mode['VALUE'];
					$rm->set_mode($mode_byte,$sensor['VALUE']);
                        }
                        if($properties[$i]['TITLE']=='auto_mode') {
					$properties[$i]['VALUE']=$value;
					SQLUpdate('dev_broadlink_commands', $properties[$i]);
					$loop_mode=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='loop_mode' AND DEVICE_ID='".$rec['ID']."'");
					$sensor=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='sensor' AND DEVICE_ID='".$rec['ID']."'");
					$mode_byte = ( $loop_mode['VALUE'] << 4) + $value;
					$rm->set_mode($mode_byte,$sensor['VALUE']);
                        }
                        if($properties[$i]['TITLE']=='thermostat_temp') {
					$properties[$i]['VALUE']=$value;
					SQLUpdate('dev_broadlink_commands', $properties[$i]);
					$rm->set_temp($value);
                        }
/*                        if($properties[$i]['TITLE']=='hour') {
					$properties[$i]['VALUE']=$value;
					SQLUpdate('dev_broadlink_commands', $properties[$i]);
					$min=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='min' AND DEVICE_ID='".$rec['ID']."'");
					$second=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='second' AND DEVICE_ID='".$rec['ID']."'");
					$dayofweek=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='dayofweek' AND DEVICE_ID='".$rec['ID']."'");
					$rm->set_time($value,$min['VALUE'],$second['VALUE'],$dayofweek['VALUE']);
                        }
                        if($properties[$i]['TITLE']=='min') {
					$properties[$i]['VALUE']=$value;
					SQLUpdate('dev_broadlink_commands', $properties[$i]);
					$hour=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='hour' AND DEVICE_ID='".$rec['ID']."'");
					$second=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='second' AND DEVICE_ID='".$rec['ID']."'");
					$dayofweek=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='dayofweek' AND DEVICE_ID='".$rec['ID']."'");
					$rm->set_time($hour['VALUE'],$value,$second['VALUE'],$dayofweek['VALUE']);
                        }

                        if($properties[$i]['TITLE']=='dayofweek') {
					$properties[$i]['VALUE']=$value;
					SQLUpdate('dev_broadlink_commands', $properties[$i]);
					$min=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='min' AND DEVICE_ID='".$rec['ID']."'");
					$second=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='second' AND DEVICE_ID='".$rec['ID']."'");
					$hour=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='hour' AND DEVICE_ID='".$rec['ID']."'");
					$rm->set_time($hour['VALUE'],$min['VALUE'],$second['VALUE'],$value);
                        }
*/                        if($properties[$i]['TITLE']=='sensor') {
					$properties[$i]['VALUE']=$value;
					SQLUpdate('dev_broadlink_commands', $properties[$i]);
					$loop_mode=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='loop_mode' AND DEVICE_ID='".$rec['ID']."'");
					$osv=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='osv' AND DEVICE_ID='".$rec['ID']."'");
					$dif=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='dif' AND DEVICE_ID='".$rec['ID']."'");
					$svh=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='svh' AND DEVICE_ID='".$rec['ID']."'");
					$svl=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='svl' AND DEVICE_ID='".$rec['ID']."'");
					$room_temp_adj=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='room_temp_adj' AND DEVICE_ID='".$rec['ID']."'");
					$fre=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='fre' AND DEVICE_ID='".$rec['ID']."'");
					$poweron=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='poweron' AND DEVICE_ID='".$rec['ID']."'");
					$rm->set_advanced($loop_mode['VALUE'],$value,$osv['VALUE'],$dif['VALUE'],$svh['VALUE'],$svl['VALUE'],((int)($room_temp_adj['VALUE']*2)>>8 & 0xff),((int)($room_temp_adj['VALUE']*2) & 0xff),$fre['VALUE'],$poweron['VALUE']);
                        }
                        if($properties[$i]['TITLE']=='osv') {
					$properties[$i]['VALUE']=$value;
					SQLUpdate('dev_broadlink_commands', $properties[$i]);
					$loop_mode=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='loop_mode' AND DEVICE_ID='".$rec['ID']."'");
					$sensor=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='sensor' AND DEVICE_ID='".$rec['ID']."'");
					$dif=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='dif' AND DEVICE_ID='".$rec['ID']."'");
					$svh=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='svh' AND DEVICE_ID='".$rec['ID']."'");
					$svl=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='svl' AND DEVICE_ID='".$rec['ID']."'");
					$room_temp_adj=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='room_temp_adj' AND DEVICE_ID='".$rec['ID']."'");
					$fre=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='fre' AND DEVICE_ID='".$rec['ID']."'");
					$poweron=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='poweron' AND DEVICE_ID='".$rec['ID']."'");
					$rm->set_advanced($loop_mode['VALUE'],$sensor['VALUE'],$value,$dif['VALUE'],$svh['VALUE'],$svl['VALUE'],((int)($room_temp_adj['VALUE']*2)>>8 & 0xff),((int)($room_temp_adj['VALUE']*2) & 0xff),$fre['VALUE'],$poweron['VALUE']);
                        }
                        if($properties[$i]['TITLE']=='dif') {
					$properties[$i]['VALUE']=$value;
					SQLUpdate('dev_broadlink_commands', $properties[$i]);
					$loop_mode=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='loop_mode' AND DEVICE_ID='".$rec['ID']."'");
					$sensor=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='sensor' AND DEVICE_ID='".$rec['ID']."'");
					$osv=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='osv' AND DEVICE_ID='".$rec['ID']."'");
					$svh=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='svh' AND DEVICE_ID='".$rec['ID']."'");
					$svl=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='svl' AND DEVICE_ID='".$rec['ID']."'");
					$room_temp_adj=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='room_temp_adj' AND DEVICE_ID='".$rec['ID']."'");
					$fre=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='fre' AND DEVICE_ID='".$rec['ID']."'");
					$poweron=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='poweron' AND DEVICE_ID='".$rec['ID']."'");
					$rm->set_advanced($loop_mode['VALUE'],$sensor['VALUE'],$osv['VALUE'],$value,$svh['VALUE'],$svl['VALUE'],((int)($room_temp_adj['VALUE']*2)>>8 & 0xff),((int)($room_temp_adj['VALUE']*2) & 0xff),$fre['VALUE'],$poweron['VALUE']);
                        }
                        if($properties[$i]['TITLE']=='svh') {
					$properties[$i]['VALUE']=$value;
					SQLUpdate('dev_broadlink_commands', $properties[$i]);
					$loop_mode=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='loop_mode' AND DEVICE_ID='".$rec['ID']."'");
					$sensor=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='sensor' AND DEVICE_ID='".$rec['ID']."'");
					$osv=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='osv' AND DEVICE_ID='".$rec['ID']."'");
					$dif=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='dif' AND DEVICE_ID='".$rec['ID']."'");
					$svl=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='svl' AND DEVICE_ID='".$rec['ID']."'");
					$room_temp_adj=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='room_temp_adj' AND DEVICE_ID='".$rec['ID']."'");
					$fre=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='fre' AND DEVICE_ID='".$rec['ID']."'");
					$poweron=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='poweron' AND DEVICE_ID='".$rec['ID']."'");
					$rm->set_advanced($loop_mode['VALUE'],$sensor['VALUE'],$osv['VALUE'],$dif['VALUE'],$value,$svl['VALUE'],((int)($room_temp_adj['VALUE']*2)>>8 & 0xff),((int)($room_temp_adj['VALUE']*2) & 0xff),$fre['VALUE'],$poweron['VALUE']);
                        }
                        if($properties[$i]['TITLE']=='svl') {
					$properties[$i]['VALUE']=$value;
					SQLUpdate('dev_broadlink_commands', $properties[$i]);
					$loop_mode=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='loop_mode' AND DEVICE_ID='".$rec['ID']."'");
					$sensor=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='sensor' AND DEVICE_ID='".$rec['ID']."'");
					$osv=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='osv' AND DEVICE_ID='".$rec['ID']."'");
					$dif=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='dif' AND DEVICE_ID='".$rec['ID']."'");
					$svh=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='svh' AND DEVICE_ID='".$rec['ID']."'");
					$room_temp_adj=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='room_temp_adj' AND DEVICE_ID='".$rec['ID']."'");
					$fre=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='fre' AND DEVICE_ID='".$rec['ID']."'");
					$poweron=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='poweron' AND DEVICE_ID='".$rec['ID']."'");
					$rm->set_advanced($loop_mode['VALUE'],$sensor['VALUE'],$osv['VALUE'],$dif['VALUE'],$svh['VALUE'],$value,((int)($room_temp_adj['VALUE']*2)>>8 & 0xff),((int)($room_temp_adj['VALUE']*2) & 0xff),$fre['VALUE'],$poweron['VALUE']);
                        }
                        if($properties[$i]['TITLE']=='room_temp_adj') {
					$properties[$i]['VALUE']=$value;
					SQLUpdate('dev_broadlink_commands', $properties[$i]);
					$loop_mode=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='loop_mode' AND DEVICE_ID='".$rec['ID']."'");
					$sensor=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='sensor' AND DEVICE_ID='".$rec['ID']."'");
					$osv=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='osv' AND DEVICE_ID='".$rec['ID']."'");
					$dif=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='dif' AND DEVICE_ID='".$rec['ID']."'");
					$svh=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='svh' AND DEVICE_ID='".$rec['ID']."'");
					$svl=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='svl' AND DEVICE_ID='".$rec['ID']."'");
					$fre=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='fre' AND DEVICE_ID='".$rec['ID']."'");
					$poweron=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='poweron' AND DEVICE_ID='".$rec['ID']."'");
					$rm->set_advanced($loop_mode['VALUE'],$sensor['VALUE'],$osv['VALUE'],$dif['VALUE'],$svh['VALUE'],$svl['VALUE'],((int)($value*2)>>8 & 0xff),((int)($value*2) & 0xff),$fre['VALUE'],$poweron['VALUE']);
                        }
                        if($properties[$i]['TITLE']=='fre') {
					$properties[$i]['VALUE']=$value;
					SQLUpdate('dev_broadlink_commands', $properties[$i]);
					$loop_mode=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='loop_mode' AND DEVICE_ID='".$rec['ID']."'");
					$sensor=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='sensor' AND DEVICE_ID='".$rec['ID']."'");
					$osv=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='osv' AND DEVICE_ID='".$rec['ID']."'");
					$dif=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='dif' AND DEVICE_ID='".$rec['ID']."'");
					$svh=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='svh' AND DEVICE_ID='".$rec['ID']."'");
					$svl=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='svl' AND DEVICE_ID='".$rec['ID']."'");
					$room_temp_adj=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='room_temp_adj' AND DEVICE_ID='".$rec['ID']."'");
					$poweron=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='poweron' AND DEVICE_ID='".$rec['ID']."'");
					$rm->set_advanced($loop_mode['VALUE'],$sensor['VALUE'],$osv['VALUE'],$dif['VALUE'],$svh['VALUE'],$svl['VALUE'],((int)($room_temp_adj['VALUE']*2)>>8 & 0xff),((int)($room_temp_adj['VALUE']*2) & 0xff),$value,$poweron['VALUE']);
                        }
                        if($properties[$i]['TITLE']=='poweron') {
					$properties[$i]['VALUE']=$value;
					SQLUpdate('dev_broadlink_commands', $properties[$i]);
					$loop_mode=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='loop_mode' AND DEVICE_ID='".$rec['ID']."'");
					$sensor=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='sensor' AND DEVICE_ID='".$rec['ID']."'");
					$osv=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='osv' AND DEVICE_ID='".$rec['ID']."'");
					$dif=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='dif' AND DEVICE_ID='".$rec['ID']."'");
					$svh=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='svh' AND DEVICE_ID='".$rec['ID']."'");
					$svl=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='svl' AND DEVICE_ID='".$rec['ID']."'");
					$room_temp_adj=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='room_temp_adj' AND DEVICE_ID='".$rec['ID']."'");
					$fre=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='fre' AND DEVICE_ID='".$rec['ID']."'");
					$rm->set_advanced($loop_mode['VALUE'],$sensor['VALUE'],$osv['VALUE'],$dif['VALUE'],$svh['VALUE'],$svl['VALUE'],((int)($room_temp_adj['VALUE']*2)>>8 & 0xff),((int)($room_temp_adj['VALUE']*2) & 0xff),$fre['VALUE'],$value);
                        }
                        if($properties[$i]['TITLE']=='schedule') {
					$properties[$i]['VALUE']=$value;
					SQLUpdate('dev_broadlink_commands', $properties[$i]);
					$rm->set_schedule($value);
                        }
		}
    }
   }
  }
 }

 function check_params($chtime = '') {
	require(DIR_MODULES.$this->name.'/dev_broadlink_check.inc.php');
 }

 function table_data_set($prop, $dev_id, $val, $sg_val = NULL, $batt = false) {
	$table='dev_broadlink_commands';
	$properties=SQLSelectOne("SELECT * FROM $table WHERE TITLE='$prop' AND DEVICE_ID='$dev_id'");
	// Проверка на существование записи (совместимость с PHP 8+)
	$total = (is_array($properties) && isset($properties['ID'])) ? 1 : 0;
	if ($total) {
		if($this->config['VAL_UPDATE']=='on_change' && $val!=$properties['VALUE']) {
			$need_rec=true;
		} else if($this->config['VAL_UPDATE']=='on_change' && $val==$properties['VALUE']){
			$need_rec=false;
		} else if($this->config['VAL_UPDATE']=='always') {
			$need_rec=true;
		}
		if ($need_rec) {
			$properties['VALUE']=$val;
			SQLUpdate($table, $properties);
			if(isset($properties['LINKED_OBJECT']) && $properties['LINKED_OBJECT']!='' && isset($properties['LINKED_PROPERTY']) && $properties['LINKED_PROPERTY']!='') {
				if(is_null($sg_val)) {
					sg($properties['LINKED_OBJECT'].'.'.$properties['LINKED_PROPERTY'], $val);
				} else {
					sg($properties['LINKED_OBJECT'].'.'.$properties['LINKED_PROPERTY'], $sg_val);
				}
				if($batt) {
					$decoded=json_decode($val);
					if(!empty($decoded->batterylow) || !empty($decoded->tamper)){
						sg($properties['LINKED_OBJECT'].'.batterylow', $decoded->batterylow);
						sg($properties['LINKED_OBJECT'].'.tamper', $decoded->tamper);
					}
				}
			}

		} else if($batt) {
			$decoded=json_decode($val);
			if(!empty($decoded->batterylow) || !empty($decoded->tamper)){
				if(gg($properties['LINKED_OBJECT'].'.batterylow')!=$decoded->batterylow) sg($properties['LINKED_OBJECT'].'.batterylow', $decoded->batterylow);
				if(gg($properties['LINKED_OBJECT'].'.tamper')!=$decoded->tamper) sg($properties['LINKED_OBJECT'].'.tamper', $decoded->tamper);
			}
		}
	} else {
		$properties['VALUE']=$val;
		$properties['DEVICE_ID']=$dev_id;
		$properties['TITLE']=$prop;
		SQLInsert($table, $properties);
	}
 }

 function refrash_ip() {
	$devices = Broadlink::Discover();
	foreach ($devices as $device) {
		if($device->mac()!='0.0.0.0') {
			$mac=$device->mac();
			$rec=SQLSelectOne("SELECT * FROM dev_httpbrige_devices WHERE MAC='$mac'");
			$rec['IP']=$device->host();
			$rec['UPDATED']=date('Y-m-d H:i:s');
			SQLUpdate('dev_httpbrige_devices', $rec);
		}
	}
 }
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install();
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS dev_httpbrige_devices');
  SQLExec('DROP TABLE IF EXISTS dev_broadlink_commands');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data='') {
/*
dev_httpbrige_devices -
*/
  $data = <<<EOD
 dev_httpbrige_devices: ID int(10) unsigned NOT NULL auto_increment
 dev_httpbrige_devices: TYPE varchar(10) NOT NULL DEFAULT ''
 dev_httpbrige_devices: TITLE varchar(100) NOT NULL DEFAULT ''
 dev_httpbrige_devices: DEVTYPE varchar(10) NOT NULL DEFAULT ''
 dev_httpbrige_devices: IP varchar(20) NOT NULL DEFAULT ''
 dev_httpbrige_devices: MAC varchar(20) NOT NULL DEFAULT ''
 dev_httpbrige_devices: CHTIME varchar(10) NOT NULL DEFAULT ''
 dev_httpbrige_devices: KEYS varchar(128) NOT NULL DEFAULT ''
 dev_httpbrige_devices: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 dev_httpbrige_devices: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 dev_httpbrige_devices: UPDATED datetime
 dev_broadlink_commands: ID int(10) unsigned NOT NULL auto_increment
 dev_broadlink_commands: TITLE varchar(100) NOT NULL DEFAULT ''
 dev_broadlink_commands: VALUE TEXT NOT NULL DEFAULT ''
 dev_broadlink_commands: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 dev_broadlink_commands: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 dev_broadlink_commands: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 dev_broadlink_commands: LINKED_METHOD varchar(100) NOT NULL DEFAULT ''
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgSnVuIDI4LCAyMDE2IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
