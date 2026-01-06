<?php
	if (SETTINGS_SITE_LANGUAGE && file_exists(ROOT . 'languages/' . $this->name. '_' .SETTINGS_SITE_LANGUAGE . '.php')) {
		include_once (ROOT . 'languages/' . $this->name. '_' .SETTINGS_SITE_LANGUAGE . '.php');
	} else {
		include_once (ROOT . 'languages/'. $this->name. '_default.php');
	}
	$this->getConfig();
	if(isset($chtime) && $chtime!='all' && $chtime!='') {
		$db_rec=SQLSelect("SELECT * FROM dev_httpbrige_devices WHERE CHTIME='$chtime'");
	} elseif (isset($chtime) && $chtime!='all') {
		$db_rec=SQLSelect("SELECT * FROM dev_httpbrige_devices");
	} else {
		$db_rec=SQLSelect("SELECT * FROM dev_httpbrige_devices WHERE CHTIME<>'none'");
	}
	if ($this->config['API']=='httpbrige') {
		$db_rec=SQLSelect("SELECT * FROM dev_httpbrige_devices");
		for ($i = 1; $i <= count($db_rec); $i++) {
			$response ='';
			$rec=$db_rec[$i-1];
			if ($rec['TYPE']=='rm' || $rec['TYPE']=='rm4pro') {
					$ctx = stream_context_create(array('http' => array('timeout'=>2)));
					$response = file_get_contents($this->config['API_URL'].'/?devMAC='.$rec['MAC'], 0, $ctx);
					if(isset($response) && $response!=='') {
						sg($rec['LINKED_OBJECT'].'.temperature', (float)$response);
					}
			}
			if ($rec['TYPE']=='rm3') {
			}
			if ($rec['TYPE']=='a1') {
					$ctx = stream_context_create(array('http' => array('timeout'=>2)));
					$response = file_get_contents($this->config['API_URL'].'/?devMAC='.$rec['MAC'], 0, $ctx);
					if(isset($response) && $response!='') {
						$json = json_decode($response);
						sg($rec['LINKED_OBJECT'].'.temperature', (float)$json->{'temperature'});
						sg($rec['LINKED_OBJECT'].'.humidity', (float)$json->{'humidity'});
						sg($rec['LINKED_OBJECT'].'.noise', (int)$json->{'noisy'});
						sg($rec['LINKED_OBJECT'].'.luminosity', (int)$json->{'light'});
						sg($rec['LINKED_OBJECT'].'.air', (int)$json->{'air'});
					}
			}
			if ($rec['TYPE']=='sp2') {
					$ctx = stream_context_create(array('http' => array('timeout'=>2)));
					$response = file_get_contents($this->config['API_URL'].'/?devMAC='.$rec['MAC'], 0, $ctx);
					if(isset($response) && $response!=='') {
						sg($rec['LINKED_OBJECT'].'.status', (int)$response);
					}

					$response = file_get_contents($this->config['API_URL'].'/?devMAC='.$rec['MAC'].'&action=power ', 0, $ctx);
					if(isset($response) && $response!=='') {
						sg($rec['LINKED_OBJECT'].'.power', $response);
					}
			}
			if ($rec['TYPE']=='spmini') {
					$ctx = stream_context_create(array('http' => array('timeout'=>2)));
					$response = file_get_contents($this->config['API_URL'].'/?devMAC='.$rec['MAC'], 0, $ctx);
					if(isset($response) && $response!=='') {
						sg($rec['LINKED_OBJECT'].'.status', (int)$response);
					}
			}
			if ($rec['TYPE']=='sp3') {
					$ctx = stream_context_create(array('http' => array('timeout'=>2)));
					$response = file_get_contents($this->config['API_URL'].'/?devMAC='.$rec['MAC'], 0, $ctx);
					if(isset($response) && $response!=='') {
						sg($rec['LINKED_OBJECT'].'.status', (int)$response);
					}
					$response = file_get_contents($this->config['API_URL'].'/?devMAC='.$rec['MAC'].'&action=lightstatus', 0, $ctx);
					if(isset($response) && $response!=='') {
						sg($rec['LINKED_OBJECT'].'.lightstatus', $response);
					}
			}
			if(isset($response) && $response!='') {
				$rec['UPDATED']=date('Y-m-d H:i:s');
				SQLUpdate('dev_httpbrige_devices', $rec);
			}
		}
	} else {
		include_once(DIR_MODULES.$this->name.'/broadlink.class.php');
		foreach ($db_rec as $rec) {
			$response = '';
			$rm = Broadlink::CreateDevice($rec['IP'], $rec['MAC'], 80, $rec['DEVTYPE']);
			if( !is_null($rm) && ( ($this->config['VAL_PING']!='true') || $rm->ping()) ) {
				if(isset($rec['KEYS']) && $rec['KEYS']!=''&& $rec['KEYS']!= false) {
					$decoded_keys=json_decode($rec['KEYS']);
					if (time()-(int)$decoded_keys->time > 604800) {
						$keys=$rm->Auth();
						$rec['KEYS']=json_encode($keys);
					} else {
						$rm->Auth($decoded_keys->id, $decoded_keys->key);
					}
				} else {
					$keys=$rm->Auth();
					$rec['KEYS']=json_encode($keys);
				}

				if ($rec['TYPE']=='rm') {
						$response = $rm->Check_temperature();
						if(isset($response) && $response!==false) {
							if((int)$response!=249) $this->table_data_set('temperature', $rec['ID'], (float)$response);
						}
				}
				if ($rec['TYPE']=='rm3') {
				}
				if ($rec['TYPE']=='a1') {
						$response = $rm->Check_sensors();
						if(isset($response) && !empty($response)) {
							foreach ($response as $key => $value) {
								$this->table_data_set($key, $rec['ID'], $value);
							}
						}
				}
				if ($rec['TYPE']=='sp2' || $rec['TYPE'] == 'spmini' || $rec['TYPE'] == 'sp3' || $rec['TYPE']=='sp3s' || $rec['TYPE'] == 'sc1') {
					$response = $rm->Check_Power();
						if(isset($response) && !empty($response)) {
							$this->table_data_set('status', $rec['ID'], (int)$response['power_state']);
							if ($rec['TYPE'] == 'sp3') {
								$this->table_data_set('lightstatus', $rec['ID'], (int)$response['light_state']);
							}
						}

				}
				if ($rec['TYPE']=='sp2') {
					$response = $rm->Check_Energy_SP2();
						if(isset($response) && $response!==false) {
							$this->table_data_set('power', $rec['ID'], (float)$response);
						}

				}
				if ($rec['TYPE']=='sp3s') {
					$response = $rm->Check_Energy();
						if(isset($response) && $response!==false) {
							$this->table_data_set('power', $rec['ID'], (float)$response);
						}

				}
				if ($rec['TYPE']=='mp1') {
					$response = $rm->Check_Power();
						if(isset($response) && !empty($response)) {
							for($i=0;$i<4;$i++) {
								$this->table_data_set('status'.($i+1), $rec['ID'], (int)$response[$i]);
							}
						}
				}
				if ($rec['TYPE']=='ms1') {
					$response = 'add_val';
						$this->table_data_set('ButtonPower', $rec['ID'], $response);
						$this->table_data_set('ButtonMute', $rec['ID'], $response);
						$this->table_data_set('ButtonPause', $rec['ID'], $response);
						$this->table_data_set('ButtonPlay', $rec['ID'], $response);
						$this->table_data_set('ButtonNext', $rec['ID'], $response);
						$this->table_data_set('ButtonPrev', $rec['ID'], $response);
						$this->table_data_set('ButtonVolUp', $rec['ID'], $response);
						$this->table_data_set('ButtonVolDown', $rec['ID'], $response);
						$this->table_data_set('ButtonAux', $rec['ID'], $response);
					$response = $rm->send_str('{"command":"request-pb"}');
						if(isset($response) && !empty($response)) {
							//$this->table_data_set('info-pb', $rec['ID'], $response);
							$decoded=json_decode($response);
							$this->table_data_set('status', $rec['ID'], $decoded->status);
						} else {
							$this->table_data_set('status', $rec['ID'], 'Offline');
						}
					$response = $rm->send_str('{"command":"request-dev"}');
						if(isset($response) && !empty($response)) {
							//$this->table_data_set('info-dev', $rec['ID'], $response);
							$decoded=json_decode($response);
							$this->table_data_set('volume', $rec['ID'], $decoded->vol);
							$this->table_data_set('battery', $rec['ID'], $decoded->battery);
						}
				}
				if ($rec['TYPE']=='s1') {
					$response = $rm->Check_Sensors();
					if(isset($response) && !empty($response)) {
						for($sn=0;$sn<$response['col_sensors'];$sn++) {
							$sens_arr=$response[$sn];
							$sens_name='['.$sens_arr['sensor_number'].'] '.$sens_arr['product_type'];
							$this->table_data_set($sens_name, $rec['ID'], json_encode($sens_arr), $sens_arr['status'], true);
						}
					}
					$response = $rm->Check_Status();
					if(isset($response) && !empty($response)) {
							$this->table_data_set('status', $rec['ID'], json_encode($response), $response['status']);
					}
				}
				if ($rec['TYPE']=='dooya') {
					$response = $rm->get_level();
					if(isset($response) && $response!==false) {
						$this->table_data_set('level', $rec['ID'], $response);
					}
				}
				if ($rec['TYPE']=='hysen') {
					$response = $rm->get_status();
					if(isset($response) && !empty($response)) {
						foreach ($response as $key => $value) {
							$this->table_data_set($key, $rec['ID'], $value);
						}
					}
					$response = $rm->get_schedule();
					if(isset($response) && !empty($response)) {
						$this->table_data_set('schedule', $rec['ID'], json_encode($response));
					}
				}
				if(isset($response) && $response!==false) {
					$rec['UPDATED']=date('Y-m-d H:i:s');
					SQLUpdate('dev_httpbrige_devices', $rec);
				}
			} else {
				DebMes('Device '.$rec['TITLE'].' is not available');
			}
		}
	}
?>
