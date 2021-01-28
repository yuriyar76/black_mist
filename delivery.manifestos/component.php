<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

CModule::IncludeModule('iblock');
CModule::IncludeModule('currency');
$u_id = $USER->GetID();
$arResult["CURRENT_USER"] = $u_id;

$agent_array = GetCurrentAgent($u_id);
$agent_id = $agent_array['id'];
$componentPage = "blank";

if (isset($arParams['PERM'][$agent_array['type']]['ALL']))
{
	$arResult["PERM"] = $arParams['PERM'][$agent_array['type']]['ALL'];
}

if ($arResult["PERM"] == "C")
{
	$APPLICATION->AuthForm("Доступ запрещен");
}

$arResult["ERRORS"] = array();

if (is_array($_SESSION['MESSAGE']))
{
	$arResult["MESSAGE"] = $_SESSION['MESSAGE'];
	$_SESSION['MESSAGE'] = false;
}

$arResult['ROLE_USER'] = GetRoleOfUser($u_id);

/**************************************************/ 
/************************УК************************/
/**************************************************/ 
if ($agent_array['type'] == 51)
{
	$modes = array(
		'list', 
		'inbox', 
		'outbox', 
		'manifest', 
		'manifest_xls',
		'from1c'
	);
	
	$arResult["MENU"] = array(
		'list' => GetMessage("MANIFESTS_TO_SEND_TTL"),
		'outbox' => GetMessage("OUTBOX_TTL"), 
		'inbox' => GetMessage("INBOX_TTL"),
		'from1c' => GetMessage("FROM_1C_TTL")
	);
	
	/**************убираем закрытое меню***************/
	foreach ($arResult["MENU"] as $m => $name)
	{
		if ($arParams['PERM'][$agent_array['type']][$m][$arResult['ROLE_USER']] == "C")
		{
			unset($arResult["MENU"][$m]);
		}
	}
	
	/*******опеределяем, какой режим показывать********/
	if (in_array($_GET['mode'],$modes))
	{
		$mode = $_GET['mode'];
	}
	else
	{
		if ($arParams['MODE'])
			$mode = $arParams['MODE'];
		else
		{
			foreach ($arResult["MENU"] as $k => $name)
			{
				$mode = $k;
				break;
			}
		}
	}
	if (strlen($mode))
	{
		$arResult['MODE'] = $mode;
		$componentPage = "upr_".$mode;
	}
	else
	{
		$APPLICATION->AuthForm("Доступ запрещен");
	}
	
	/****************права пользователя****************/
	if (isset($arParams['PERM'][$agent_array['type']]['ALL']))
	{
		$arResult["PERM"] = $arParams['PERM'][$agent_array['type']]['ALL'];
	}
	else
	{
		$arResult["PERM"] = $arParams['PERM'][$agent_array['type']][$mode][$arResult['ROLE_USER']];
	}
	if ($arResult["PERM"] == "C")
		$APPLICATION->AuthForm("Доступ запрещен");
	
	/**************Манифесты на отправку***************/
	if ($mode == 'list')
	{
		if (isset($_POST['delete_man']))
		{
			foreach ($_POST['manif_id'] as $man_id)
			{
				$packs = GetListPackeges($man_id);
				foreach ($packs as $p) {
					CIBlockElement::SetPropertyValuesEx($p['ID'], false, array(195=>false,203=>56));
					$his = AddToHistory($p['ID'],$agent_id,$u_id,56,GetMessage("MANIFESTO_REMOVED_HISTORY"));
				}
				CIBlockElement::Delete($man_id);
				$arResult['MESSAGE'][] = GetMessage("MANIFESTO_REMOVED",array("#ID#"=>$man_id));
			}
		}
		$arResult["TITLE"] = GetMessage("MANIFESTS_TO_SEND_TTL");
		$arResult["LIST"] = GetManifests($agent_id,35);
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"]);
	}
	
	/****************Входящие манифесты****************/
	if ($mode == 'inbox') {
		$arResult["TITLE"] = GetMessage("INBOX_TTL");
	}
	
	/***************Исходящие манифесты****************/
	if ($mode == 'outbox')
	{
		$arResult["TITLE"] = 'Исходящие манифесты';
		$arResult["LIST"] = GetManifests($agent_id, array(47,48));
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"]);
	}
	
	/*********************Манифест*********************/
	if ($mode == 'manifest')
	{
		$arResult['AVAILABLE_AGENTS'] = AvailableAgents();
		$arResult["CARRIERS"] = TheListOfCarriers();
		
		if (isset($_POST['send_manifests']))
		{
			if ((!strlen($_POST['number_send'])) || (!strlen($_POST['date_send'])))
			{
				$arResult["ERRORS"][] = 'Пожалуйста, заполните все поля';
			}
			else
			{
				$man_id = $_POST['man_id_r'];
				$ag_arr = GetAgentInfo($_POST['agent_id']);
				foreach ($_POST['packs_in_man'] as $pack)
				{
					CIBlockElement::SetPropertyValuesEx($pack, 42, array(203 => 46, 229 => 68));
					$history_id = AddToHistory($pack,$agent_id,$u_id,46,$ag_arr['NAME'].', '.$ag_arr['PROPERTY_CITY']);
					$short_history_id = AddToShortHistory($pack,$u_id,68,$ag_arr['PROPERTY_CITY']);
				}
				$props = array();
				$props[192] = 47;
				$props[233] = $_POST['carriers'];
				$props[270] = $_POST['date_send'];
				$props[271] = $_POST['number_send'];
				$props[194] = $_POST['agent_id'];
				$props[193] = $ag_arr['PROPERTY_CITY_VALUE'];
				$props[408] = $_POST["settlement_date"];
				$props[409] = intval($_POST['places']);
 				$props[410] = $u_id;
				CIBlockElement::SetPropertyValuesEx($man_id, 41, $props);
				
				$arSendsParams = array(
					"DATE_SEND" => $_POST['date_send'],
					"MAN_ID" => $man_id,
					"NUMBER" => $_POST['id_in'],
					"DATE_MANIFEST" => $_POST['date_send'],
					"CARRIER" => GetMessage('CARRIER', array("#NAME#" => $arResult["CARRIERS"][$_POST['carriers']]['NAME'])),
					"CARRIER_DOC" => GetMessage('CARRIER_DOC', array('#NAME#' => $_POST['number_send'])),
					"LINE" => "",
					"LINK_TO_MESS" => "",
					"AUTOMATICALLY" => ""
				);
				$qw = SendMessageInSystem(
					$u_id, 
					$agent_array['id'], 
					$ag_arr["ID"], 
					GetMessage('SEND_MANIFEST_TTL'), 
					105, 
					'', 
					'', 
					188,
					$arSendsParams
				);
				$arSendsParams['LINK_TO_MESS'] = GetMessage('LINK_TO_MESS', array('#ID#' => $qw));
				$arSendsParams['LINE'] = GetMessage('MESS_LINE');
				$arSendsParams['AUTOMATICALLY'] = GetMessage('MESS_AUTOMATICALLY');
				$ff = MakeManifestXls($man_id, 'F');
				SendMessageMailNew($ag_arr["ID"], $agent_array['id'], 103, 188, $arSendsParams, array($ff));
				
				/*
				$txt = '
					<p>Вам направлен манифест №'.$_POST['id_in'].':</p>
					<p>Дата отправления: '. $_POST['date_send'].'<br>
					Расчетная дата прибытия: '. $_POST['settlement_date'].'<br>
					Перевозочный документ: '.$_POST['number_send'].'<br>
					Перевозчик: '.$arResult["CARRIERS"][$_POST['carriers']]['NAME'].'</p>
					<p>Для принятия манифеста воспользуйтесь  <a href="http://dms.newpartner.ru/manifesty/index.php?mode=manifest&id='.$man_id.'">ссылкой</a></p>';
				
				$qw = SendMessageInSystem($u_id,$agent_array['id'],$ag_arr["ID"],"Отправлен манифест",105,$txt);
				$txt .= '<p><a href="http://dms.newpartner.ru/messages/index.php?mode=detail&id='.$qw.'">Ссылка на данное сообщение в системе DMS</a></p>';
				$agent_to = $ag_arr["ID"];
				$agent_from = $agent_array['id'];
				$info = GetCompany($agent_to);
				$info_from = GetCompany($agent_from);
				$type = 103;
				if (isset($info["PROPERTY_MAIL_SETTINGS_VALUE"][$type]))
				{
					$ff = MakeManifestXls($man_id, 'F');
					if (($agent_from == 2294524) || ($agent_from == 2249975))
					{
						$EMAIL_TO = GetSettingValue(331);
					}
					
					else
					{
						$EMAIL_TO = $info["PROPERTY_EMAIL_VALUE"];
					}
					if ($agent_to == 2197189)
					{
						$EMAIL_FROM = 'dms@newpartner.ru';
					}
					else
					{
						$EMAIL_FROM = 'im@newpartner.ru';
					}
					
					$SUBJECT = "DMS \"Новый Партнер\": ".$info["PROPERTY_MAIL_SETTINGS_VALUE"][$type];
					include $_SERVER['DOCUMENT_ROOT']."/bitrix/_kerk/class.phpmailer.php";
					$mail = new PHPMailer();
					$mail->Priority = 1; 
					$mail->From = $EMAIL_FROM;
					$mail->FromName = 'DMS "Новый Партнер"';                                                   
					$mail->AddAddress($EMAIL_TO, '');
					$mail->IsHTML(true);                                                        
					$mail->Subject = $SUBJECT;
					$mail->AddAttachment($ff);
					$mail->ContentType = "text/html";
					$mail->Body = $txt;
					$mail->Send();
					unlink($ff);
				}
				*/
				$arResult['MESSAGE'][] = 'Манифест №'.$_POST['id_in'].' успешно отправлен агенту '.$ag_arr['NAME'];
			}
		}	
		
		if (isset($_POST['edit_manifest']))
		{
			if (isset($_POST['agent_id']))
			{
				$ag_arr = GetAgentInfo($_POST['agent_id']);
				CIBlockElement::SetPropertyValuesEx($_POST['man_id_r'], false, array(194=>$_POST['agent_id'],193=>$ag_arr['PROPERTY_CITY_VALUE']));
			}
			foreach ($_POST['packs_to_del'] as $p)
			{
				CIBlockElement::SetPropertyValuesEx($p, false, array(195=>false,203=>56));
				$his = AddToHistory($p,$agent_id,$u_id,56,'Удалено из манифеста №'.$_POST['man_id_r']);
				$arResult['MESSAGE'][] = 'Заказ №'.$p.' успешно удалено из манифеста';
			}
			$arResult['MESSAGE'][] = 'Манифест №'.$_POST['man_id_r'].' успешно изменен';
		}
		
		if (isset($_POST['add_pack_to_man']))
		{
			$res = CIBlockElement::GetList(array("id" => "desc"), Array("IBLOCK_ID"=>42, "PROPERTY_N_ZAKAZ_IN"=>trim($_POST['number'])), false, Array("nTopCount"=>1),array("ID"));
			if($ob = $res->GetNextElement())
			{
				$arFields = $ob->GetFields();
				if ($_POST['type_send'] == 2)
				{
					$st = 45;
					$st_sh = 76;	
				}
				else
				{
					$st = 46;
					$st_sh = 68;
				}
				$props = array(
					203 => $st, 
					229 => $st_sh, 
					195 => $_POST['man_id']
				);
				
				if ($_POST['type_send'] == 1)
				{
					$props[240] = $_POST['summ'];
					$props[266] = $_POST['cash'];
				}
				
				CIBlockElement::SetPropertyValuesEx($arFields['ID'], 42, $props);
				$history_id = AddToHistory($arFields['ID'], $agent_id, $u_id, $st, $_POST['agent_name'].', '.$_POST['agent_city']);
				$short_history_id = AddToShortHistory($arFields['ID'], $u_id, $st_sh, $_POST['agent_name']);
				$arResult["MESSAGE"][] = 'Заказ '.trim($_POST['number']).' добавлен к манифесту '.$_POST['man_id_in'];
				unset($_POST);
			}
			else
			{
				$arResult["ERRORS"][] = 'Заказ '.trim($_POST['number']).' не найден';
			}
		}
		
		$arResult["MANIFEST"]["ID"] = intval($_REQUEST['id']);
		$arResult["PACKS"] = array();
		if ($arResult["MANIFEST"]["ID"] > 0)
		{
			$arResult["PACKS"] = GetListPackeges($arResult["MANIFEST"]["ID"], array("ID"=>"ASC"));
			$arResult["PATH"] = $this->GetPath();
			$arResult["MANIFEST"]["INFO"] = GetInfioOfManifest($arResult["MANIFEST"]["ID"]);
			$arResult["TITLE"] = "Манифест №".$arResult["MANIFEST"]["INFO"]["PROPERTY_ID_IN_VALUE"];
			$pl = $ww = 0;
			foreach ($arResult["PACKS"] as $p) {
				$pl = $pl + $p['PROPERTY_PLACES_VALUE'];
				$ww = $ww + $p['PROPERTY_WEIGHT_VALUE'];
			}
			$arResult["MANIFEST"]["INFO"]["PLACES"] = $pl;
			$arResult["MANIFEST"]["INFO"]["WEIGHT"] = $ww;
			$arResult["NAV_STRING"] = $arResult["PACKS"]["NAV_STRING"];
			unset($arResult["PACKS"]["NAV_STRING"]);
		}
	}
	
	if ($mode == "manifest_xls")
	{
		MakeManifestXls(intval($_GET['id']));
	}
	
	/******************Выгрузка из 1с******************/
	if ($mode == 'from1c')
	{
		$folder = '/var/www/admin/www/delivery-russia.ru/app/f/manifestos/';
		$folder_arch = '/var/www/admin/www/delivery-russia.ru/app/f/manifestos_archive/';
		if (!file_exists($folder_arch))
		{
			mkdir($folder_arch);
		}
		$arResult['AVAILABLE_AGENTS'] = AvailableAgents(false, $uk);
		
		if (isset($_POST['exchange_new']))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
				if (count($_POST['manifestos']) > 0)
				{
					foreach ($_POST['manifestos'] as $number_man)
					{
						if ($_POST['manifest'][$number_man]['agent_id'] > 0)
						{
							$id_in = GetMaxIDIN(41, 5);
							$el = new CIBlockElement;
							$PROP = array(
								406 => $id_in,
								407 => $number_man,
								194 => $_POST['manifest'][$number_man]['agent_id'],
								193 => $_POST['manifest'][$number_man]['city'],
								270 => $_POST['manifest'][$number_man]['date'],
								191 => $agent_array['id'],
								410 => $u_id,
								192 => 47,
								409 => 1
							);
							$arLoadProductArray = Array(
								"MODIFIED_BY" => $u_id,
								"IBLOCK_SECTION_ID" => false,
								"IBLOCK_ID" => 41,
								"PROPERTY_VALUES"=> $PROP,
								"NAME" => "Манифест №".$id_in,
								"ACTIVE" => "Y",
								"DATE_CREATE" => $_POST['manifest'][$number_man]['date']
							);
							$man_create_ID = $el->Add($arLoadProductArray);
							$arResult["MESSAGE"][] = GetMessage(
								'MAN_CREATE', 
								array(
									'#NAME#' => $number_man, 
									'#AGENT#' => $arResult['AVAILABLE_AGENTS'][$_POST['manifest'][$number_man]['agent_id']], 
									'#ID#' => $man_create_ID
								)
							);
							foreach ($_POST['manifest'][$number_man]['naks'] as $id_nakl)
							{
								$arMans = array();
								$res_man = CIBlockElement::GetProperty(42, $id_nakl, "ID", "asc", array("CODE" => "MANIFEST"));
								while ($ob_man = $res_man->GetNext())
								{
									$arMans[] = $ob_man['VALUE'];
								}
								$arMans[] = $man_create_ID;
								$s_f = 46;
								$s_s = 68;
								$return = 0;
								$state_text = 'Отправлен агенту';
								if ($_POST['type_send'][$id_nakl] == 2)
								{
									$s_f = 45;
									$s_s = 76;
									$state_text = 'Возврат отправителю';
									$return = 1;
								}
								$history_id = AddToHistory(
									$id_nakl, 
									$agent_array['id'], 
									$u_id, 
									$s_f, 
									$arResult['AVAILABLE_AGENTS'][$_POST['manifest'][$number_man]['agent_id']], 
									$_POST['manifest'][$number_man]['date']
								);
								$short_history_id = AddToShortHistory(
									$id_nakl, 
									$u_id,
									$s_s, 
									$_POST['manifest'][$number_man]['city_name'], 
									$_POST['manifest'][$number_man]['date']
								);
								CIBlockElement::SetPropertyValuesEx(
									$id_nakl, 
									42, 
									array(
										195 => $arMans,
										203 => $s_f, 
										229 => $s_s, 
										240 => $_POST['cost'][$id_nakl], 
										266 => $_POST['rate'][$id_nakl],
										444 => $return
									)
								);
								$arResult["MESSAGE"][] = GetMessage(
									'ORDER_STATE', 
									array(
										'#ID#' => $id_nakl,
										'#ORDER#' => $_POST['number'][$id_nakl],
										'#MAN#' => $number_man,
										'#TEXT#' => $state_text
									)
								);
							}
							if ($man_create_ID)
							{
								$arSendsParams = array(
									"DATE_SEND" => date('d.m.Y H:i'),
									"MAN_ID" => $man_create_ID,
									"NUMBER" => $number_man,
									"DATE_MANIFEST" => $_POST['manifest'][$number_man]['date'],
									"CARRIER" => "",
									"CARRIER_DOC" => "",
									"LINE" => "",
									"LINK_TO_MESS" => "",
									"AUTOMATICALLY" => ""
								);
								$qw = SendMessageInSystem(
									$u_id, 
									$agent_array['id'], 
									$_POST['manifest'][$number_man]['agent_id'], 
									GetMessage('SEND_MANIFEST_TTL'), 
									105, 
									'', 
									'', 
									188,
									$arSendsParams
								);
								$arSendsParams['LINK_TO_MESS'] = GetMessage('LINK_TO_MESS', array('#ID#' => $qw));
								$arSendsParams['LINE'] = GetMessage('MESS_LINE');
								$arSendsParams['AUTOMATICALLY'] = GetMessage('MESS_AUTOMATICALLY');
								$ff = MakeManifestXls($man_create_ID, 'F');
								SendMessageMailNew($_POST['manifest'][$number_man]['agent_id'], $agent_array['id'], 103, 188, $arSendsParams, array($ff));
								copy($folder.$_POST['manifest'][$number_man]['file_link'], $folder_arch.$_POST['manifest'][$number_man]['file_link']);
								unlink($folder.$_POST['manifest'][$number_man]['file_link']);
							}
						}
						else
						{
							$arResult["ERRORS"][] = 'Не выбран агент';
						}
					}
				}
				else
				{
					$arResult["ERRORS"][] = 'Не выбран ни один манифест';
				}
			}
		}
		

		
		
		if (is_dir($folder))
		{
			$files = array();
			$arFilesPaths = array();
			$files = scandir($folder);
			array_shift($files);
			array_shift($files);
			if (count($files) > 0)
			{
				foreach ($files as $f)
				{
					if (is_file($folder.$f))
					{
						$arFilesPaths[] = $f;
					}
				}
				$arResult['FILESINFO'] = ReadFilesManifestsDMSfrom1c($arFilesPaths);
				foreach ($arResult['FILESINFO']['MANS'] as $k => $v)
				{
					foreach ($v['Manifestos'] as $kk => $vv)
					{
						$arResult['FILESINFO']['MANS'][$k]['Manifestos'][$kk]['IDПартнера'] = 0;
						$city_id = GetCityId(trim($vv['ГородПартнера']));
						if (strlen(trim($vv['ИНН'])))
						{
							$ag_inn_id = GetIDAgentByINN(trim($vv['ИНН']), 53);
							if ($ag_inn_id)
							{
								$arResult['FILESINFO']['MANS'][$k]['Manifestos'][$kk]['IDПартнера'] = $ag_inn_id;
							}
						}
						else
						{
							$res = CIBlockElement::GetList(
								array(),
								array("IBLOCK_ID"=> 40, "PROPERTY_TYPE" => 53, "NAME" => trim($vv['Партнер']), "PROPERTY_CITY" => $city_id), 
								false, 
								false, 
								array("ID")
							);
							if($ob = $res->GetNextElement())
							{
								$arFields = $ob->GetFields();
								$arResult['FILESINFO']['MANS'][$k]['Manifestos'][$kk]['IDПартнера'] = $arFields['ID'];
							}
						}
						$arResult['FILESINFO']['MANS'][$k]['Manifestos'][$kk]['IDГорода'] = $city_id;
						$arResult['FILESINFO']['MANS'][$k]['Manifestos'][$kk]['ПроцентПартнера'] = 0;
						$arResult['FILESINFO']['MANS'][$k]['Manifestos'][$kk]['ПрайсЛистПартнера'] = false;
						if ($arResult['FILESINFO']['MANS'][$k]['Manifestos'][$kk]['IDПартнера'] > 0)
						{
							$db_props = CIBlockElement::GetProperty(40, $arResult['FILESINFO']['MANS'][$k]['Manifestos'][$kk]['IDПартнера'], array("sort" => "asc"), Array("CODE"=>"PERCENT"));
							if($ar_props = $db_props->Fetch())
							{
								$arResult['FILESINFO']['MANS'][$k]['Manifestos'][$kk]['ПроцентПартнера'] = $ar_props["VALUE"];
							}
							$res_2 = CIBlockElement::GetList(
								array('sort' => 'asc'),
								array("IBLOCK_ID"=> 51, "PROPERTY_USER" => $arResult['FILESINFO']['MANS'][$k]['Manifestos'][$kk]['IDПартнера'], "PROPERTY_CITIES" => $city_id), 
								false, 
								array('nTopCount' => 1), 
								array("ID")
							);
							if($ob_2 = $res_2->GetNextElement())
							{
								$arFields_2 = $ob_2->GetFields();
								$arResult['FILESINFO']['MANS'][$k]['Manifestos'][$kk]['ПрайсЛистПартнера']  = $arFields_2['ID'];
							}
							foreach ($vv['Накладные'] as $k_number => $number)
							{
								if ($arResult['FILESINFO']['MANS'][$k]['Manifestos'][$kk]['ПрайсЛистПартнера'])
								{
									$h = CheckCityToHave(
										$city_id, 
										$arResult['FILESINFO']['MANS'][$k]['Manifestos'][$kk]['ПрайсЛистПартнера'], 
										$arResult['FILESINFO']['States'][$number]['WEIGHT'], 
										$arResult['FILESINFO']['States'][$number]['SIZE_1'], 
										$arResult['FILESINFO']['States'][$number]['SIZE_2'], 
										$arResult['FILESINFO']['States'][$number]['SIZE_3']
									);
									if ($h['LOG'])
									{
										$arResult['FILESINFO']['States'][$number]['COST_AGENT'] = $h['COST'];
									}
								}
								$arResult['FILESINFO']['States'][$number]['RATE_AGENT'] = round(floatval($arResult['FILESINFO']['MANS'][$k]['Manifestos'][$kk]['ПроцентПартнера']*$arResult['FILESINFO']['States'][$number]['COST']/100),2);
							}
						}
					}
				}
			}
			else
			{
				$arResult["ERRORS"][] = GetMessage('ERR_NO_FILES');
			}
		}
		else
		{
			$arResult["ERRORS"][] = GetMessage('ERR_NO_FOLDER');
		}
	}
}

/**************************************************/ 
/**********************Агент***********************/
/**************************************************/
if ($agent_array['type'] == 53)
{
	$modes = array(
		'list',
		'inbox',
		'outbox',
		'manifest'
	);
	$arResult["MENU"] = array(
		'list' => GetMessage("LIST_TTL"),
		'inbox' => GetMessage("INBOX_TTL"),
		'outbox' => GetMessage("OUTBOX_TTL")
	);
	
	/**************убираем закрытое меню***************/
	foreach ($arResult["MENU"] as $m => $name)
	{
		if ($arParams['PERM'][$agent_array['type']][$m][$arResult['ROLE_USER']] == "C")
		{
			unset($arResult["MENU"][$m]);
		}
	}
	
	/*******опеределяем, какой режим показывать********/
	if (in_array($_GET['mode'],$modes))
	{
		$mode = $_GET['mode'];
	}
	else
	{
		if ($arParams['MODE'])
			$mode = $arParams['MODE'];
		else
		{
			foreach ($arResult["MENU"] as $k => $name)
			{
				$mode = $k;
				break;
			}
		}
	}
	if (strlen($mode))
	{
		$arResult['MODE'] = $mode;
		$componentPage = "agent_".$mode;
	}
	else
	{
		$APPLICATION->AuthForm("Доступ запрещен");
	}
	
	/****************права пользователя****************/
	if (isset($arParams['PERM'][$agent_array['type']]['ALL']))
	{
		$arResult["PERM"] = $arParams['PERM'][$agent_array['type']]['ALL'];
	}
	else
	{
		$arResult["PERM"] = $arParams['PERM'][$agent_array['type']][$mode][$arResult['ROLE_USER']];
	}
	if ($arResult["PERM"] == "C")
		$APPLICATION->AuthForm("Доступ запрещен");
	
	/***************Ожидаемые манифесты****************/
	if ($mode == 'list')
	{
		if (isset($_POST['resive_manifests']))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
				foreach ($_POST['manif_id'] as $man_id)
				{
					$man_info = GetInfioOfManifest($man_id);
					$packs = GetListPackeges($man_id,array("ID"=>"ASC"));
					unset($packs["NAV_STRING"]);
					foreach ($packs as $pack)
					{
						if ($pack['PROPERTY_STATE_ENUM_ID'] == 46)
						{
							CIBlockElement::SetPropertyValuesEx($pack['ID'], 42, array(203 => 40, 229 => 69, 499 => $agent_array['id']));
							$history_id = AddToHistory($pack,$agent_id,$u_id, 40,''); 
							$short_history_id = AddToShortHistory($pack,$u_id, 69);	
						}
					}
					CIBlockElement::SetPropertyValuesEx($man_id, 41,  array(192 => 48, 273 => date('d.m.Y H:i:s')));
					$arResult['MESSAGE'][] = GetMessage("MANIFESTO_ADOPTED",array("#ID#" => $man_id, "#ID_IN#" => $_POST['number'][$man_id]));
				}
			}
		}
		
		$arResult["TITLE"] = GetMessage("LIST_TTL");
		$arResult["LIST"] = ManifestsToAgent($agent_id,47);
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"]);
	}
	
	/****************Входящие манифесты****************/
	if ($mode == 'inbox')
	{
		$arResult["TITLE"] = GetMessage("INBOX_TTL");
		if (strlen($_GET['date_from'])) $date_from = $_GET['date_from']; else $date_from = false;
		if (strlen($_GET['date_to'])) $date_to = $_GET['date_to']; else $date_to = false;
		$arResult["LIST"] = ManifestsToAgent($agent_id,48,$date_from,$date_to);
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"]);
	}
	
	/***************Исходящие манифесты****************/
	if ($mode == 'outbox')
	{
		$arResult["TITLE"] = GetMessage("OUTBOX_TTL");
	}
	
	/*********************Манифест*********************/
	if ($mode == 'manifest')
	{
		if (isset($_POST['departure_packages_agent']))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
				foreach ($_POST['packs'] as $id)
				{
					CIBlockElement::SetPropertyValuesEx($id, false, array(203 => 40, 229 => 69, 499 => $agent_array['id']));
					$history_id = AddToHistory($id, $agent_id, $u_id, 40,'');
					$short_history_id = AddToShortHistory($id, $u_id, 69);
					$arResult['MESSAGE'][] = GetMessage("ORDER_ACCEPTED",array("#ID#"=>$id,"#ID_IN#"=>$_POST['id_in'][$id]));
				}
				$man_id = $_POST['man_id'];
				$packs_of_man = GetListPackeges($man_id);
				unset($packs_of_man["NAV_STRING"]);
				$all_rec = 1;
				foreach ($packs_of_man as $p)
				{
					if ($p['PROPERTY_STATE_ENUM_ID'] == 68)
					{
						$all_rec = $all_rec*0;
					}
					else
					{
						$all_rec = $all_rec*1;
					}
				}
				if ($all_rec == 1)
				{
					CIBlockElement::SetPropertyValuesEx($man_id, false, array(192 => 48, 273 => date('d.m.Y H:i:s')));
					$arResult['MESSAGE'][] = GetMessage("MANIFESTO_ADOPTED",array("#ID#"=>$man_id,"#ID_IN#"=>$man_id));
				}
			}
		}
		
		$res = CIBlockElement::GetByID(intval($_REQUEST['id']));
		if ($ar_res = $res->GetNext())
		{
			$arResult["MANIFEST"]["ID"] = intval($_REQUEST['id']);
			
			$arResult["PACKS"] = array();
			if ($arResult["MANIFEST"]["ID"] > 0)
			{
				$arResult["PACKS"] = GetListPackeges($arResult["MANIFEST"]["ID"], array("ID"=>"SC"));
				$arResult["NAV_STRING"] = $arResult["PACKS"]["NAV_STRING"];
				unset($arResult["PACKS"]["NAV_STRING"]);
				$arResult["PATH"] = $this->GetPath();
				$arResult["MANIFEST"]["INFO"] = GetInfioOfManifest($arResult["MANIFEST"]["ID"]);
				$arResult["TITLE"] = GetMessage("MANIFESTO",array("#ID#"=>$arResult["MANIFEST"]["INFO"]["PROPERTY_NUMBER_VALUE"]));
				$pl = $ww = 0;
				foreach ($arResult["PACKS"] as $p)
				{
					$pl = $pl + $p['PROPERTY_PLACES_VALUE'];
					$ww = $ww + $p['PROPERTY_WEIGHT_VALUE'];
				}
				$arResult["MANIFEST"]["INFO"]["PLACES"] = $pl;
				$arResult["MANIFEST"]["INFO"]["WEIGHT"] = $ww;
			}
		}
		else
		{
			$arResult["MANIFEST"] = false;
			$arResult["TITLE"] = GetMessage('NO_MANIFESTO');
		}
	}
}

$this->IncludeComponentTemplate($componentPage);
?>