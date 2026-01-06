<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='dev_httpbrige_devices';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  include_once(DIR_MODULES.$this->name.'/broadlink.class.php');
  if ($this->mode=='learn') {
	if ($this->config['API_URL']=='httpbrige') {
	   $api_command=$this->config['API_URL'].'/?devMAC='.$rec['MAC'].'&action=study';
	   getUrl($api_command);
	   $out['MESSAGE']='Режим обучения';
	}
  }

  if ($this->mode=='add_from_scan') {
   $rec['TYPE']=gr('type');
   $rec['TITLE']=gr('title');
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }
   $rec['IP']=gr('ip');
   $rec['DEVTYPE']=gr('devtype');
   $rec['MAC']=gr('mac');
   $rec['LINKED_OBJECT']=gr('linked_object');
   $rec['LINKED_PROPERTY']=gr('linked_property');
   $rec['UPDATED']=date('Y-m-d H:i:s');

   }
  if ($this->mode=='save_code') {
   //$api_command=$this->config['API_URL'].'/?devMAC='. $rec['MAC'].'&action=save&name='.$this->code_name;
   //getUrl($api_command);
   $out['MESSAGE']='Сохранение команд пока не работает. Для сохранения последней команды используйте ссылку '.$this->config['API_URL'].'/?devMAC='.$rec['MAC'].'&action=save&name='.'<имя команды>';
  }
  if ($this->mode=='sp_on') {
	  if ($this->config['API_URL']=='httpbrige') {
		   $api_command=$this->config['API_URL'].'/?devMAC='.$rec['MAC'].'&action=on';
		   getUrl($api_command);
	  }
  }
  if ($this->mode=='sp_off') {
	  if ($this->config['API_URL']=='httpbrige') {
		   $api_command=$this->config['API_URL'].'/?devMAC='.$rec['MAC'].'&action=off';
		   getUrl($api_command);
      }
  }
  if ($this->mode=='sp_light_on') {
   $api_command=$this->config['API_URL'].'/?devMAC='.$rec['MAC'].'&action=&action=lighton';
   getUrl($api_command);
  }
  if ($this->mode=='sp_light_off') {
   $api_command=$this->config['API_URL'].'/?devMAC='.$rec['MAC'].'&action=&action=lightoff';
   getUrl($api_command);
  }

  if ($this->mode=='update') {
   $ok=1;

   if ($this->tab=='') {
  //updating 'LANG_TITLE' (varchar, required)
   //updating 'TYPE' (varchar)
   $rec['TYPE']=gr('type');
   $rec['TITLE']=gr('title');
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }
  //updating 'IP' (varchar)
   $rec['IP']=gr('ip');
  //updating 'DEVTYPE' (varchar)
   $rec['DEVTYPE']=gr('devtype');
  //updating 'MAC' (varchar)
   $rec['MAC']=gr('mac');
   $chtime = gr('chtime');
   $rec['CHTIME']=isset($chtime) ? $chtime : 'none';
  //updating 'LANG_LINKED_OBJECT' (varchar)
   $rec['LINKED_OBJECT']=gr('linked_object');
  //updating 'LANG_LINKED_PROPERTY' (varchar)
   $rec['LINKED_PROPERTY']=gr('linked_property');
  //updating 'LANG_UPDATED' (datetime)
   $rec['UPDATED']=date('Y-m-d H:i:s');
   }

  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
	 if ($this->config['API_URL']=='httpbrige') {
		if ($rec['TYPE'] == 'sp2' || $rec['TYPE'] == 'spmini' || $rec['TYPE'] == 'sp3') {
			 sg($rec['LINKED_OBJECT'].'.'.'status', '');
			 addLinkedProperty($rec['LINKED_OBJECT'], 'status', $this->name);
		}
		if ($rec['TYPE'] == 'sp3') {
			 sg($rec['LINKED_OBJECT'].'.'.'lightstatus', '');
			 addLinkedProperty($rec['LINKED_OBJECT'], 'lightstatus', $this->name);
		}
	 }
    }
    $out['OK']=1;
   } else {
    $out['ERR']=1;
   }
  }
  // step: default
  if ($this->tab=='') {
  if ($rec['UPDATED']!='') {
   $tmp=explode(' ', $rec['UPDATED']);
   $out['UPDATED_DATE']=$tmp[0];
   $tmp2=explode(':', $tmp[1]);
   $updated_hours=$tmp2[0];
   $updated_minutes=$tmp2[1];
  }
  for($i=0;$i<60;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$updated_minutes) {
    $out['UPDATED_MINUTES'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['UPDATED_MINUTES'][]=array('TITLE'=>$title);
   }
  }
  for($i=0;$i<24;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$updated_hours) {
    $out['UPDATED_HOURS'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['UPDATED_HOURS'][]=array('TITLE'=>$title);
   }
  }
  }

  if ($this->tab=='data'||$this->tab=='data_usage') {
	$this->getConfig();
	$new_id=0;

	$relearn_id = gr('relearn_id');
	if ($relearn_id) {
	$data=SQLSelectOne("SELECT * FROM dev_broadlink_commands WHERE ID='$relearn_id'");
	$rm = Broadlink::CreateDevice($rec['IP'], $rec['MAC'], 80, $rec['DEVTYPE']);
	$decoded_keys=json_decode($rec['KEYS']);
	$rm->Auth($decoded_keys->id, $decoded_keys->key);
	$rm->Enter_learning();
	$i = 0;
	do {
		sleep(1);
		$json['hex'] = $rm->Check_data();
	} while((count($json['hex']) == 0) && ($i++ < 10));
	$json['hex_number'] = '';
	foreach ($json['hex'] as $value) {
		$json['hex_number'] .= sprintf("%02x", $value);
	}
	if(count($json['hex']) > 0){
		$data['VALUE']=$json['hex_number'];
		SQLUpdate('dev_broadlink_commands',$data);
		$out['OK']=1;
	} else {
		$out['MESSAGE']='Команда не сохранена';
	}


   }
   $delete_id = gr('delete_id');
   if ($delete_id) {
    SQLExec("DELETE FROM dev_broadlink_commands WHERE ID='".(int)$delete_id."'");
   }
   $test_id = gr('test_id');
   if ($test_id) {
	$data=SQLSelectOne("SELECT * FROM dev_broadlink_commands WHERE ID='$test_id'");
	$rm = Broadlink::CreateDevice($rec['IP'], $rec['MAC'], 80, $rec['DEVTYPE']);
	$decoded_keys=json_decode($rec['KEYS']);
	$rm->Auth($decoded_keys->id, $decoded_keys->key);
	if($rec['TYPE']=='rm' || $rec['TYPE']=='rm3' || $rec['TYPE']=='rm4pro'){
		$rm->Send_data($data['VALUE']);
	} elseif($rec['TYPE'] == 'sp2' || $rec['TYPE'] == 'spmini' || $rec['TYPE']=='sp3s' || $rec['TYPE'] == 'sc1') {
		if($data['VALUE']==1){
			$data['VALUE']=0;
		} else {
			$data['VALUE']=1;
		}
		SQLUpdate('dev_broadlink_commands', $data);
		$rm->Set_Power($data['VALUE']);
	} elseif($rec['TYPE'] == 'sp3') {
		if($data['VALUE']==1){
			$data['VALUE']=0;
		} else {
			$data['VALUE']=1;
		}
		SQLUpdate('dev_broadlink_commands', $data);
		$powerstat=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='status' AND DEVICE_ID='".$rec['ID']."'");
		$lstat=SQLSelectOne("SELECT VALUE FROM dev_broadlink_commands WHERE TITLE='lightstatus' AND DEVICE_ID='".$rec['ID']."'");
		$rm->Set_Power($powerstat['VALUE']+$lstat['VALUE']*2);
	} elseif($rec['TYPE'] == 'mp1') {
		if($data['VALUE']){
			$data['VALUE']='0';
		} else {
			$data['VALUE']='1';
		}
		SQLUpdate('dev_broadlink_commands', $data);
		$rm->Set_Power(substr($data['TITLE'], -1), $data['VALUE']);
	} elseif($rec['TYPE'] == 'ms1') {
		if($data['TITLE']=='ButtonPower') $rm->send_str('{"command":"key","value":2}');
		if($data['TITLE']=='ButtonMute') $rm->send_str('{"command":"key","value":3}');
		if($data['TITLE']=='ButtonPause') $rm->send_str('{"command":"key","value":9}');
		if($data['TITLE']=='ButtonPlay') $rm->send_str('{"command":"key","value":1}');
		if($data['TITLE']=='ButtonNext') $rm->send_str('{"command":"key","value":7}');
		if($data['TITLE']=='ButtonPrev') $rm->send_str('{"command":"key","value":8}');
		if($data['TITLE']=='ButtonVolUp') $rm->send_str('{"command":"key","value":4}');
		if($data['TITLE']=='ButtonVolDown') $rm->send_str('{"command":"key","value":5}');
		if($data['TITLE']=='ButtonAux') $rm->send_str('{"command":"key","value":6}');
	} elseif($rec['TYPE'] == 's1') {
		$arm_pack=json_decode($data['VALUE'], true);
		if($arm_pack['status']==1) {
			$arm_pack['status']=2;
		} elseif($arm_pack['status']==2) {
			$arm_pack['status']=0;
		} else {
			$arm_pack['status']=1;
		}
		$rm->Set_Arm($arm_pack);
		$data['VALUE']=json_encode($arm_pack);
		SQLUpdate('dev_broadlink_commands', $data);
	}
	$this->redirect("?data_source=&view_mode=edit_dev_httpbrige_devices&id=".$rec['ID']."&tab=data");
   }
   $sort_by_name = gr('sort_by_name'); 
   if ($sort_by_name) {
	   $properties=SQLSelect("SELECT * FROM dev_broadlink_commands WHERE DEVICE_ID='".$rec['ID']."' ORDER BY TITLE");
   } else {
	   $properties=SQLSelect("SELECT * FROM dev_broadlink_commands WHERE DEVICE_ID='".$rec['ID']."' ORDER BY ID");
   }
   paging($properties, 20, $out);
   $total=count($properties);
   for($i=0;$i<$total;$i++) {
    if ($properties[$i]['ID']==$new_id) continue;
    if ($this->mode=='update') {
		//print_r($_REQUEST);exit;
		$title_new = gr('title_new');
		if ($title_new) {
		 $prop=array('TITLE'=>$title_new,'DEVICE_ID'=>$rec['ID']);
		 $new_id=SQLInsert('dev_broadlink_commands',$prop);
		}
      global ${'title'.$properties[$i]['ID']};
      $properties[$i]['TITLE']=trim(${'title'.$properties[$i]['ID']});
      global ${'value'.$properties[$i]['ID']};
      $properties[$i]['VALUE']=trim(${'value'.$properties[$i]['ID']});
      global ${'linked_object'.$properties[$i]['ID']};
      $properties[$i]['LINKED_OBJECT']=trim(${'linked_object'.$properties[$i]['ID']});
      global ${'linked_property'.$properties[$i]['ID']};
      $properties[$i]['LINKED_PROPERTY']=trim(${'linked_property'.$properties[$i]['ID']});
      SQLUpdate('dev_broadlink_commands', $properties[$i]);
      $old_linked_object=$properties[$i]['LINKED_OBJECT'];
      $old_linked_property=$properties[$i]['LINKED_PROPERTY'];
      if ($old_linked_object && $old_linked_object!=$properties[$i]['LINKED_OBJECT'] && $old_linked_property && $old_linked_property!=$properties[$i]['LINKED_PROPERTY']) {
       removeLinkedProperty($old_linked_object, $old_linked_property, $this->name);
      }
     }
	if ($properties[$i]['LINKED_OBJECT'] && $properties[$i]['LINKED_PROPERTY']) {
       addLinkedProperty($properties[$i]['LINKED_OBJECT'], $properties[$i]['LINKED_PROPERTY'], $this->name);
    }
	$properties[$i]['DEVTYPE']=$rec['TYPE'];
	if($rec['TYPE']=='s1'){
		$devinfo=json_decode($properties[$i]['VALUE']);
		$properties[$i]['VAL']=$devinfo->status;
		$properties[$i]['ZONE']=$devinfo->location;
		$properties[$i]['PARM']=$devinfo->armPart;
		$properties[$i]['FARM']=$devinfo->armFull;
		$properties[$i]['STAT']=$devinfo->status_val;
		$properties[$i]['VOL']=$devinfo->beep_mute;
		$properties[$i]['BATT']=$devinfo->batterylow;
		$properties[$i]['TAMPER']=$devinfo->tamper;
	}
	if ($properties[$i]['TITLE']=='temperature') {
		$properties[$i]['SDEVICE_TYPE']='sensor_temp';
	} elseif ($properties[$i]['TITLE']=='humidity') {
		$properties[$i]['SDEVICE_TYPE']='sensor_humidity';
	} elseif ($properties[$i]['TITLE']=='noise' || $properties[$i]['TITLE']=='light' || $properties[$i]['TITLE']=='air_quality' || $properties[$i]['TITLE']=='noise_word' || $properties[$i]['TITLE']=='light_word' || $properties[$i]['TITLE']=='air_quality_word') {
		$properties[$i]['SDEVICE_TYPE']='sensor_state';
	} elseif ($properties[$i]['TITLE']=='status' || $properties[$i]['TITLE']=='lightstatus' || $properties[$i]['TITLE']=='status1' || $properties[$i]['TITLE']=='status2' || $properties[$i]['TITLE']=='status3' || $properties[$i]['TITLE']=='status4' ) {
		$properties[$i]['SDEVICE_TYPE']='relay';
	} elseif ($properties[$i]['TITLE']=='level') {
		$properties[$i]['SDEVICE_TYPE']='dimmer';
	} else {
		$properties[$i]['SDEVICE_TYPE']='button';
	}
   }
   $out['PROPERTIES']=$properties;
  }
  if($this->tab=='data_export') {
	$properties=SQLSelect("SELECT * FROM dev_broadlink_commands WHERE DEVICE_ID='".$rec['ID']."' AND TITLE <> 'temperature' ORDER BY TITLE");
	$total=count($properties);
	for($i=0;$i<$total;$i++) {
		$export[$i]['name']=$properties[$i]['TITLE'];
		$export[$i]['data']=$properties[$i]['VALUE'];
		$export[$i]['mac']=$rec['MAC'];
	}
	
	if($this->config['DATA_EXPORT_TYPE'] == "CSV") {
			$out['TEXTAREA']=generateCsv($export);
	} else {
			$out['TEXTAREA']=json_encode($export, JSON_UNESCAPED_UNICODE);
	}
  }
  if($this->tab=='data_import' && $this->mode=='update') {
	$textarea = gr('textarea');
	if($this->config['DATA_EXPORT_TYPE'] == "CSV") {
				$flat_array = array_map("str_getcsv", explode("\n", $textarea));
				$columns = $flat_array[0];
				for ($i=1; $i<count($flat_array)-1; $i++){
					foreach ($columns as $column_index => $column){
						$obj[$i]->$column = $flat_array[$i][$column_index];
					}
				}
				$imported_data=json_encode($obj);
	} else {
		$imported_data=$textarea;
	}
	$decoded=json_decode($imported_data, true);
	foreach($decoded as $value) {
		$insert['TITLE']=$value['name'];
		$insert['VALUE']=$value['data'];
		$insert['DEVICE_ID']=$rec['ID'];
		SQLInsert('dev_broadlink_commands',$insert);
	}
	$this->redirect("?data_source=&view_mode=edit_dev_httpbrige_devices&id=".$rec['ID']."&tab=data");
  }

  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);
