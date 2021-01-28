<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

ini_set("soap.wsdl_cache_enabled", "0");
ini_set("default_socket_timeout", "300");

$start = microtime(true);
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

$modes = array(
    'list',
    'add',
    'request',
    'request_modal',
    'request_pdf',
    'request_edit',
    '1c',
    'invoice1c',
    'invoice1c_modal',
    'list_xls'
);

$arAcc = array(
    'list' => true,
    'add' => true,
    'request' => true,
    'request_modal' => true,
    'request_pdf' => true,
    'request_edit' => true,
    '1c' => false,
    'invoice1c' => true,
    'invoice1c_modal' => true
);

if ((strlen($arParams['MODE'])) && (in_array($arParams['MODE'], $modes)))
{
    $mode = $arParams['MODE'];
}
else
{
    if ((strlen(trim($_GET['mode']))) && (in_array(trim($_GET['mode']), $modes)))
    {
        $mode = trim($_GET['mode']);
    }
    else
    {
        $mode = $modes[0];
    }
}
		
if ($arAcc[$mode])
{
    $arResult['OPEN'] = true;
    $arResult['ADMIN_AGENT'] = false;
    $arResult["PAGES"] = array(20, 50, 100, 200);
    if ($arParams['REGISTRATION'] == 2)
    {
        $arResult['modes_edit'] = array(236, 261, 240);
    }
    if ($arParams['REGISTRATION'] == 1)
    {
        $arResult['modes_edit'] = array(236, 240);
    }
	$arResult["USER_ID"] = $USER->GetID();
    $rsUser = CUser::GetByID($arResult["USER_ID"]);
    $arUser = $rsUser->Fetch();
    $arResult['USER_NAME'] = $USER->GetFullName();
    $agent_id = intval($arUser["UF_COMPANY_RU_POST"]);
    $arResult['USER_BRANCH'] = false;

    if ($agent_id > 0)
    {
        $db_props = CIBlockElement::GetProperty(40, $agent_id, array("sort" => "asc"), array("ID" => 211, "ACTIVE" => "Y"));
        if ($ar_props = $db_props->Fetch())
        {
            $agent_type = $ar_props["VALUE"];
            if (!in_array($agent_type, array(51, $arParams["TYPE"])))
            {
                $arResult["OPEN"] = false;
                $arResult["ERRORS"][] = GetMessage("ERR_OPEN");
            }
            else
            {
                $arResult['AGENT'] = GetCompany($agent_id);
                if ($agent_type == 51)
                {
                    $arResult['ADMIN_AGENT'] = true;
                    $arResult["UK"] = $arResult["AGENT"]["ID"];
                }
                else
                {
                    $arResult["UK"] = $arResult["AGENT"]["PROPERTY_UK_VALUE"];
                }
                if (intval($arResult["UK"]) > 0)
                {
                    $currentip = GetSettingValue(683, false, $arResult["UK"]);
                    $currentlink = GetSettingValue(704, false, $arResult["UK"]);
                    $login1c = GetSettingValue(705, false, $arResult["UK"]);
                    $pass1c = GetSettingValue(706, false, $arResult["UK"]);
                    if ((strlen(trim($currentip))) && (strlen(trim($currentlink))) && (strlen(trim($login1c))) && (strlen(trim($pass1c))))
                    {
                        $url = "http://".$currentip.$currentlink;
                        $curl = curl_init();
                        curl_setopt_array($curl, array(    
							CURLOPT_URL => $url,
							CURLOPT_HEADER => true,
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_NOBODY => true,
							CURLOPT_TIMEOUT => 10)
						);
                        $header = explode("\n", curl_exec($curl));
                        curl_close($curl);
                        if (strlen(trim($header[0])))
                        {
                            $arResult['USER_BRANCH'] = (intval($arUser["UF_BRANCH"]) > 0) ? intval($arUser["UF_BRANCH"]) : false;
                            if ($arResult['USER_BRANCH'])
                            {
                                $arResult['BRANCH_INFO'] = GetBranch($arResult['USER_BRANCH'], $agent_id);
                                $arResult['BRANCH_IN_1C'] = $arResult['BRANCH_INFO']['PROPERTY_IN_1C_VALUE'];
                            }
                            $client = new SoapClient(
                                $url,  
                                array("login" => $login1c, "password" => $pass1c, "exceptions" => false)
                            );
                        }
                        else
                        {
                            $mode = 'close';
                        }
                    }
                    else
                    {
                        $mode = 'close';
                    }
                }
                else
                {
                    $arResult["OPEN"] = false;
                    $arResult["ERRORS"][] = GetMessage("ERR_UK");
                }
            }
        }
        else
        {
            $arResult["OPEN"] = false;
            $arResult["ERRORS"][] = GetMessage("ERR_OPEN");
        }
    }
    else
    {
        $arResult["OPEN"] = false;
        $arResult["ERRORS"][] = GetMessage("ERR_OPEN");
    }
}
		
$arResult['times'][] = array('name' => 'Первоначальные проверки', 'val' => microtime(true) - $start);

if (is_array($_SESSION['MESSAGE']))
{
    $arResult["MESSAGE"] = $_SESSION['MESSAGE'];
    $_SESSION['MESSAGE'] = false;
}
if (is_array($_SESSION['ERRORS']))
{
    $arResult["ERRORS"] = $_SESSION['ERRORS'];
    $_SESSION['ERRORS'] = false;
}
if (is_array($_SESSION['WARNINGS']))
{
    $arResult["WARNINGS"] = $_SESSION['WARNINGS'];
    $_SESSION['WARNINGS'] = false;
}
		
		if ($mode == 'request_pdf')
		{
			$arResult['REQUEST'] = false;
			$id_reqv = intval($_GET['id']);
			if ($id_reqv > 0)
			{	
				$filter = array("IBLOCK_ID" => 82, "ID" => $id_reqv, "PROPERTY_CREATOR" => $agent_id);
				if ($arResult['ADMIN_AGENT'])
				{
					unset($filter["PROPERTY_CREATOR"]);
				}
				$res = CIBlockElement::GetList(
					array("id" => "desc"), 
					$filter,
					false, 
					false, 
					array(
						"ID",
						"PROPERTY_NAME_SENDER",
						"PROPERTY_PHONE_SENDER",
						"PROPERTY_COMPANY_SENDER",
						"PROPERTY_CITY_SENDER",
						"PROPERTY_INDEX_SENDER",
						"PROPERTY_ADRESS_SENDER",
						"PROPERTY_NAME_RECIPIENT",
						"PROPERTY_PHONE_RECIPIENT",
						"PROPERTY_COMPANY_RECIPIENT",
						"PROPERTY_CITY_RECIPIENT",
						"PROPERTY_INDEX_RECIPIENT",
						"PROPERTY_ADRESS_RECIPIENT",
						"PROPERTY_DATE_TAKE",
						"PROPERTY_TIME_TAKE_FROM",
						"PROPERTY_TIME_TAKE_TO",
						"PROPERTY_TYPE",
						"PROPERTY_DELIVERY_PAYER",
						"PROPERTY_TYPE_CASH",
						"PROPERTY_PAYMENT_AMOUNT",
						"PROPERTY_COST",
						"PROPERTY_PLACES",
						"PROPERTY_WEIGHT",
						"PROPERTY_OB_WEIGHT",
						"PROPERTY_STATE",
						"PROPERTY_NUMBER",
						"PROPERTY_INSTRUCTIONS",
						'PROPERTY_SIZE_1',
						'PROPERTY_SIZE_2',
						'PROPERTY_SIZE_3',
						'PROPERTY_CREATOR'
					)
				);
				if ($ob = $res->GetNextElement())
				{
					$r = $ob->GetFields();
					$COEFFICIENT_VW = WhatIsGabWeightCompany($r['PROPERTY_CREATOR_VALUE']);
					$r['PROPERTY_CITY_SENDER'] = GetFullNameOfCity($r['PROPERTY_CITY_SENDER_VALUE']);
					$r['PROPERTY_CITY_RECIPIENT'] = GetFullNameOfCity($r['PROPERTY_CITY_RECIPIENT_VALUE']);
					$a["PROPERTY_OB_WEIGHT"] = WeightFormat((($a["PROPERTY_SIZE_1_VALUE"]*$a["PROPERTY_SIZE_2_VALUE"]*$a["PROPERTY_SIZE_3_VALUE"])/$COEFFICIENT_VW), false);
					$arResult['REQUEST'] = $r;
				}
			}
			if ($arResult['REQUEST'])
			{
				MakeInvoicePDF($arResult['REQUEST']);
			}
			else
			{
				LocalRedirect($arParams['LINK'].'index.php?mode=request&id='.$_GET['id']);
			}
		}
		
		if ($mode == 'list')
		{
			$arResult['YEARS'] = array(2014 => 2014, 2015 => 2015, 2016 => 2016, 2017 => 2017);
			$arResult['MONTHS'] = array(
				'01' => 'январь',
				'02' => 'февраль',
				'03' => 'март',
				'04' => 'апрель',
				'05' => 'май',
				'06' => 'июнь',
				'07' => 'июль',
				'08' => 'август',
				'09' => 'сентябрь',
				'10' => 'октябрь',
				'11' => 'ноябрь',
				'12' => 'декабрь',
			);
			
			$arResult['CURRENT_MONTH'] =  date('m');
			$arResult['CURRENT_YEAR'] =  date('Y');
			
			if (!$arResult['ADMIN_AGENT'])
			{
				$arResult['CURRENT_INN'] = $arResult['AGENT']['PROPERTY_INN_VALUE'];
				$arResult['CURRENT_AGENT'] = $agent_id;
			}
			else
			{
				if (strlen($_SESSION['CURRENT_INN']))
				{
					$arResult['CURRENT_INN'] = $_SESSION['CURRENT_INN'];
				}
				else
				{
					$arResult['CURRENT_INN'] = false;
				}
				if (strlen($_SESSION['CURRENT_AGENT']))
				{
					$arResult['CURRENT_AGENT'] = $_SESSION['CURRENT_AGENT'];
				}
				else
				{
					$arResult['CURRENT_AGENT'] = 0;
				}
			}
			$arResult['CURRENT_AGENT_COEFFICIENT_VW'] = WhatIsGabWeightCompany($arResult['CURRENT_AGENT']);
			
			if (strlen($_SESSION['CURRENT_MONTH']))
			{
				$arResult['CURRENT_MONTH'] = $_SESSION['CURRENT_MONTH'];
			}
			if (strlen($_SESSION['CURRENT_YEAR']))
			{
				$arResult['CURRENT_YEAR'] = $_SESSION['CURRENT_YEAR'];
			}	
			if ($_GET['ChangePeriod'] == 'Y')
			{
				if (isset($arResult['YEARS'][$_GET['year']]))
				{
					$_SESSION['CURRENT_YEAR'] = $_GET['year'];
					$arResult['CURRENT_YEAR'] = $_GET['year'];
				}
				if (isset($arResult['MONTHS'][$_GET['month']]))
				{
					$_SESSION['CURRENT_MONTH'] = $_GET['month'];
					$arResult['CURRENT_MONTH'] = $_GET['month'];
				}
			}
			$datetime = strtotime($arResult['CURRENT_YEAR'].'-'.$arResult['CURRENT_MONTH'].'-01');
			$last_day = date('t', $datetime);
			
			
			$arResult['LIST_OF_AGENTS'] = false;
			if ($arResult['ADMIN_AGENT'])
			{
				if ($arParams["TYPE"] == 53)
				{
					$arResult['LIST_OF_AGENTS'] = AvailableAgents(false, $agent_id);
				}
				elseif ($arParams["TYPE"] == 242)
				{
					$arResult['LIST_OF_AGENTS'] = AvailableClients(false, false, $agent_id);
				}
				
				if ($_GET['ChangeAgent'] == 'Y')
				{
					if (isset($arResult['LIST_OF_AGENTS'][$_GET['agent']]))
					{
						$_SESSION['CURRENT_AGENT'] = $_GET['agent'];
						$arResult['CURRENT_AGENT'] = $_GET['agent'];
						
						$db_props = CIBlockElement::GetProperty(40,$arResult['CURRENT_AGENT'], array("sort" => "asc"), Array("CODE"=>"INN"));
						if($ar_props = $db_props->Fetch())
						{
							$arResult['CURRENT_INN'] = $ar_props["VALUE"];
						}
						else
						{
							$arResult['CURRENT_INN'] = false;
						}
						$_SESSION['CURRENT_INN'] = $arResult['CURRENT_INN'];
					}
					elseif (intval($_GET['agent']) == 0)
					{
						unset($_SESSION['CURRENT_AGENT']);
						unset($_SESSION['CURRENT_INN']);
						$arResult['CURRENT_AGENT'] = false;
						$arResult['CURRENT_INN'] = false;
					}
				}
			}
			
			$arResult['times'][] = array('name' => 'Первоначальные настройки функции', 'val' => microtime(true) - $start);
			
			if (isset($_POST['delete']))
			{
				if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
				{
					$_POST = array();
					$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
				}
				else
				{
					$_SESSION[$_POST["key_session"]] = $_POST["rand"];
					if (count($_POST['ids']) > 0)
					{
						foreach ($_POST['ids'] as $p)
						{
							CIBlockElement::Delete($p);
						}
						$arResult["MESSAGE"][] = 'Заявки удалены';
					}
				}
			}
			
			if ((isset($_POST['accept']))  ||  (isset($_POST['send'])))
			{
				if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
				{
					$_POST = array();
					$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
				}
				else
				{
					$_SESSION[$_POST["key_session"]] = $_POST["rand"];
					if (count($_POST['ids']) > 0)
					{
						$arCells = array();
						$arJson = array();
						$arCells[] = array(
							'',
							'Номер заявки',
							'Отправитель',
							'Город отправителя',
							'Адрес отправителя',
							'ФИО отправителя',
							'Телефон отправителя',
							'Получатель',
							'Город получателя',
							'Адрес получателя',
							'ФИО получателя',
							'Телефон получателя',
							'Мест',
							'Вес',
							'Объемный вес',
							'Специальные инструкции'
						);
						$filter = array("IBLOCK_ID" => 82, "ID" => $_POST['ids'], "PROPERTY_CREATOR" => $agent_id);
						if ($arResult['ADMIN_AGENT'])
						{
							unset($filter["PROPERTY_CREATOR"]);
						}
						$res = CIBlockElement::GetList(
							array("id" => "desc"), 
							$filter,
							false, 
							false, 
							array(
								"ID",
								"DATE_CREATE",
								"PROPERTY_CREATOR",
								"PROPERTY_NUMBER",
								"PROPERTY_NAME_SENDER",
								"PROPERTY_PHONE_SENDER",
								"PROPERTY_COMPANY_SENDER",
								"PROPERTY_CITY_SENDER",
								"PROPERTY_CITY_SENDER.NAME",
								"PROPERTY_INDEX_SENDER",
								"PROPERTY_ADRESS_SENDER",
								"PROPERTY_NAME_RECIPIENT",
								"PROPERTY_PHONE_RECIPIENT",
								"PROPERTY_COMPANY_RECIPIENT",
								"PROPERTY_CITY_RECIPIENT",
								"PROPERTY_CITY_RECIPIENT.NAME",
								"PROPERTY_INDEX_RECIPIENT",
								"PROPERTY_ADRESS_RECIPIENT",
								"PROPERTY_DATE_TAKE",
								"PROPERTY_TIME_TAKE_FROM",
								"PROPERTY_TIME_TAKE_TO",
								"PROPERTY_TYPE",
								"PROPERTY_TYPE_DELIVERY",
								"PROPERTY_DELIVERY_PAYER",
								"PROPERTY_TYPE_CASH",
								"PROPERTY_DELIVERY_CONDITION",
								"PROPERTY_PAYMENT_AMOUNT",
								"PROPERTY_COST",
								"PROPERTY_INSTRUCTIONS",
								"PROPERTY_PLACES",
								"PROPERTY_WEIGHT",
								"PROPERTY_SIZE_1",
								"PROPERTY_SIZE_2",
								"PROPERTY_SIZE_3",
								"PROPERTY_FILES",
								"PROPERTY_NUMBER_IN",
                                "PROPERTY_DATE_ADOPTION"
							)
						);
						while ($ob = $res->GetNextElement())
						{
							$reqv = $ob->GetFields();
                            if (strlen($reqv['PROPERTY_DATE_ADOPTION_VALUE']))
                            {
                                $logsD = $_SERVER['DOCUMENT_ROOT'].'/logs/log-send-double.txt';
                                $errors_fileD = fopen($logsD,'a');
                                fwrite($errors_fileD,date('d.m.Y H:i:s').' '.$reqv['ID'].' '.$reqv['PROPERTY_NUMBER_VALUE'].', Пользователь: '.$arResult['USER_NAME']."\n");
                                fwrite($errors_fileD,'Инфо: '.implode(',', $reqv)."\n");
                                fwrite($errors_fileD,"\n");
                                fclose($errors_fileD);
                                $arResult["ERRORS"][] = 'Повторная попытка отправки заявки '.$reqv['PROPERTY_NUMBER_VALUE'];
                                continue;
                            }
							$reqv["PROPERTY_OB_WEIGHT"] = number_format((($reqv["PROPERTY_SIZE_1_VALUE"]*$reqv["PROPERTY_SIZE_2_VALUE"]*$reqv["PROPERTY_SIZE_3_VALUE"])/$arResult['CURRENT_AGENT_COEFFICIENT_VW']), 2, '.', ' ');
							$when_z = $reqv['PROPERTY_TYPE_VALUE'].'. Забрать '.$reqv['PROPERTY_DATE_TAKE_VALUE'];
							if (strlen($reqv['PROPERTY_TIME_TAKE_FROM_VALUE']))
							{
								$when_z .= ' c '.$reqv['PROPERTY_TIME_TAKE_FROM_VALUE'];
							}
							if (strlen($reqv['PROPERTY_TIME_TAKE_TO_VALUE']))
							{
								$when_z .= ' до '.$reqv['PROPERTY_TIME_TAKE_TO_VALUE'];
							}
							if (strlen($reqv['PROPERTY_INSTRUCTIONS_VALUE']))
							{
								$when_z .= '. '.$reqv['PROPERTY_INSTRUCTIONS_VALUE'];
							}
							$date_take = substr($reqv['PROPERTY_DATE_TAKE_VALUE'], 6, 4).'-'.substr($reqv['PROPERTY_DATE_TAKE_VALUE'], 3, 2).'-'.substr($reqv['PROPERTY_DATE_TAKE_VALUE'], 0, 2);
							$t1 = strlen($reqv['PROPERTY_TIME_TAKE_FROM_VALUE']) ? $reqv['PROPERTY_TIME_TAKE_FROM_VALUE'].':00' : '00:00:00';
							$t2 = strlen($reqv['PROPERTY_TIME_TAKE_TO_VALUE']) ? $reqv['PROPERTY_TIME_TAKE_TO_VALUE'].':00' : '00:00:00';
							$when_z .= '. ';
							$d_cr = substr($reqv['DATE_CREATE'], 6, 4).'-'.substr($reqv['DATE_CREATE'], 3, 2).'-'.substr($reqv['DATE_CREATE'], 0, 2).substr($reqv['DATE_CREATE'], 10, 9);
							$type_cash = ($reqv['PROPERTY_TYPE_CASH_ENUM_ID'] == 264) ? 'cash' : 'non-cash';
							$cell = array(
								'',
								$reqv['PROPERTY_NUMBER_VALUE'],
								$reqv['PROPERTY_COMPANY_SENDER_VALUE'],
								$reqv['PROPERTY_CITY_SENDER_NAME'],
								$reqv['PROPERTY_ADRESS_SENDER_VALUE'],
								$reqv['PROPERTY_NAME_SENDER_VALUE'],
								$reqv['PROPERTY_PHONE_SENDER_VALUE'],
								$reqv['PROPERTY_COMPANY_RECIPIENT_VALUE'],
								$reqv['PROPERTY_CITY_RECIPIENT_NAME'],
								$reqv['PROPERTY_ADRESS_RECIPIENT_VALUE'],
								$reqv['PROPERTY_NAME_RECIPIENT_VALUE'],
								$reqv['PROPERTY_PHONE_RECIPIENT_VALUE'],
								$reqv['PROPERTY_PLACES_VALUE'],
								$reqv['PROPERTY_WEIGHT_VALUE'],
								$reqv['PROPERTY_OB_WEIGHT'],
								$when_z
							);
							$arCells[] = $cell;
							$arFiles = array();
							foreach ($reqv['PROPERTY_FILES_VALUE'] as $file_id)
							{
								$arfileInfo = CFile::GetFileArray($file_id);
								$arFiles[] = 'agent.newpartner.ru'.$arfileInfo['SRC'];
							}
							//define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"]."/log_inn.txt");
							AddMessage2Log($arResult['AGENT']['PROPERTY_INN_VALUE']);
							$arJson[] = array(
								'ID' => $reqv['ID'],
								"DATE_CREATE" => $d_cr,
								'INN' => $arResult['AGENT']['PROPERTY_INN_VALUE'],
								'NUMBER' => $reqv['PROPERTY_NUMBER_VALUE'],
								'NAME_SENDER' => $reqv['PROPERTY_NAME_SENDER_VALUE'],
								'PHONE_SENDER' => $reqv['PROPERTY_PHONE_SENDER_VALUE'],
								'COMPANY_SENDER' => $reqv['PROPERTY_COMPANY_SENDER_VALUE'],
								'CITY_SENDER' => $reqv['PROPERTY_CITY_SENDER_VALUE'],
								'INDEX_SENDER' => $reqv['PROPERTY_INDEX_SENDER_VALUE'],
								'ADRESS_SENDER' => $reqv['PROPERTY_ADRESS_SENDER_VALUE'],
								'NAME_RECIPIENT' => $reqv['PROPERTY_NAME_RECIPIENT_VALUE'],
								'PHONE_RECIPIENT' => $reqv['PROPERTY_PHONE_RECIPIENT_VALUE'],
								'COMPANY_RECIPIENT' => $reqv['PROPERTY_COMPANY_RECIPIENT_VALUE'],
								'CITY_RECIPIENT' => $reqv['PROPERTY_CITY_RECIPIENT_VALUE'],
								'INDEX_RECIPIENT' => $reqv['PROPERTY_INDEX_RECIPIENT_VALUE'],
								'ADRESS_RECIPIENT' => $reqv['PROPERTY_ADRESS_RECIPIENT_VALUE'],
								'DATE_TAKE_FROM' => $date_take.' '.$t1,
								'DATE_TAKE_TO' => $date_take.' '.$t2,
								'TYPE' => $reqv['PROPERTY_TYPE_VALUE'],
								'DELIVERY_TYPE' => $reqv['PROPERTY_TYPE_DELIVERY_VALUE'],
								'DELIVERY_PAYER' => $reqv['PROPERTY_DELIVERY_PAYER_VALUE'],
								'PAYMENT_TYPE' => $reqv['PROPERTY_TYPE_CASH_VALUE'],
								'DELIVERY_CONDITION' => $reqv['PROPERTY_DELIVERY_CONDITION_VALUE'],
								'PAYMENT_AMOUNT' => $reqv['PROPERTY_PAYMENT_AMOUNT_VALUE'],
								//'COST' => $reqv['PROPERTY_COST_VALUE'],
								'INSTRUCTIONS' => $reqv['PROPERTY_INSTRUCTIONS_VALUE'],
								'PLACES' => $reqv['PROPERTY_PLACES_VALUE'],
								'WEIGHT' => $reqv['PROPERTY_WEIGHT_VALUE'],
								'SIZE_1' => $reqv['PROPERTY_SIZE_1_VALUE'],
								'SIZE_2' => $reqv['PROPERTY_SIZE_2_VALUE'],
								'SIZE_3' => $reqv['PROPERTY_SIZE_3_VALUE'],
								'FILES' => $arFiles,
								//'TypeCash' => $type_cash,
								'InternalNumber' => $reqv["PROPERTY_NUMBER_IN_VALUE"]
							);
							
							$logs = $_SERVER['DOCUMENT_ROOT'].'/logs/log-send.txt';
							$errors_file = fopen($logs,'a');
							fwrite($errors_file,date('d.m.Y H:i:s').' '.$reqv['ID'].' '.$reqv['PROPERTY_NUMBER_VALUE']."\n");
							fwrite($errors_file,"\n");
							fclose($errors_file);
						}
						$path = "/files/applications/".date('Y-m-d').'_'.$agent_id.'_'.time().".xls";
						$a = GetManifestXLSwParams($arCells, $_SERVER['DOCUMENT_ROOT'].$path);
						if ($a)
						{
							if (isset($_POST['accept']))
							{
								foreach ($_POST['ids'] as $r)
								{
									CIBlockElement::SetPropertyValuesEx($r, 82, array(518 => 238, 534 => date('d.m.Y H:i:s')));
								}
								$arResult["MESSAGE"][] = 'Заявки успешно приняты. <a href="'.$path.'" target="_blank">Скачать манифест</a>';
							}
							if (isset($_POST['send']))
							{
								
								$arJsonSend = array();
								foreach ($arJson as $k => $v)
								{
									foreach ($v as $kk => $vv)
									{
										if (is_array($vv))
										{
											if (count($vv) > 0)
											{
												foreach ($vv as $kkk => $vvv)
												{
													if (is_array($vvv))
													{
														foreach ($vvv as $kkkk => $vvvv)
														{
															$arJsonSend[$kk][$kkk][$kkkk] = iconv('windows-1251','utf-8', $vvvv);
														}
													}
													else
													{
														$arJsonSend[$k][$kk][$kkk] = iconv('windows-1251','utf-8', $vvv);
													}
												}
											}
											else
											{
												$arJsonSend[$k][$kk] = array();
											}
										}
										else
										{
											$arJsonSend[$k][$kk] = iconv('windows-1251','utf-8', $vv);
										}
									}
								}
								$arParamsJson = array(
									'type' => 2,
									'ListOfDocs' => json_encode($arJsonSend)
								);
		
								
								$result = $client->SetDocsList($arParamsJson);
								$mResult = $result->return;
								$obj = json_decode($mResult, true);
								
								
								/*
								$params = array(
									"#CREATOR#" => $USER->GetFullName(), 
									"#EMAIL_FROM#" => $arResult['AGENT']['PROPERTY_EMAIL_VALUE'],
									"#COMPANY_FROM#" => ' от '.$arResult['AGENT']['NAME']
								);
								$rsEM = CEventMessage::GetByID(192);
								$arEM = $rsEM->Fetch();
								$txt = $arEM["MESSAGE"];
								foreach ($params as $k => $v)
								{
									$txt = str_replace($k, $v, $txt);
								}
								$subj = $arEM['SUBJECT'];
								foreach ($params as $k => $v)
								{
									$subj = str_replace($k, $v, $subj);
								}
								$from = $arEM['EMAIL_FROM'];
								foreach ($params as $k => $v)
								{
									$from = str_replace($k, $v, $from);
								}
								
								
								include_once $_SERVER['DOCUMENT_ROOT']."/bitrix/_kerk/class.phpmailer.php";
								$mail = new PHPMailer();
								$mail->Priority = 1; 
								$mail->From = $from;
								$mail->FromName = $arUser['LAST_NAME'].' '.$arUser['NAME'];                                                   
								$mail->AddAddress($arEM['EMAIL_TO'], ''); 
								$mail->IsHTML(true);                                                        
								$mail->Subject = $subj;
								$mail->AddAttachment($_SERVER['DOCUMENT_ROOT'].$path);
								$mail->ContentType = "text/html";
								$mail->Body = $txt;
								$mail->Send();
								*/
								
								if ($obj == 'OK')
								{	
										
									foreach ($_POST['ids'] as $r)
									{
										CIBlockElement::SetPropertyValuesEx($r, 82, array(518 => 237, 534 => date('d.m.Y H:i:s')));
									}
									
									$arResult["MESSAGE"][] = 'Заявки успешно отправлены';
									
								}
								else
								{
									$arResult["ERRORS"][] = 'Ошибка передачи заявок';
								}
							}
						}
						else
						{
							$arResult["ERRORS"][] = 'Произошла ошибка формирования манифеста';
						}
					}
					else
					{
						$arResult["ERRORS"][] = 'Не выбраны заявки';
					}
				}
			}
		
			$arResult['REQUESTS'] = array();
			$arResult['ARCHIVE'] = array();
			
			if ($arResult['CURRENT_AGENT'])
			{
				$nav_array = false;
				$filter = array("IBLOCK_ID" => 82, "PROPERTY_CREATOR" => $arResult['CURRENT_AGENT'], "ACTIVE" => "Y");
				
				$filter[">=DATE_CREATE"] = '01.'.$arResult['CURRENT_MONTH'].'.'.$arResult['CURRENT_YEAR'].' 00:00:00';
				$filter["<=DATE_CREATE"] = $last_day.'.'.$arResult['CURRENT_MONTH'].'.'.$arResult['CURRENT_YEAR'].' 23:59:59';
				$arResult['SORT_BY'] = "created";
				$arResult['SORT'] = "desc";
				
				/*
				$sorts_by = array("PROPERTY_NUMBER", "PROPERTY_STATE", "DATE_CREATE");
				$sorts = array("desc", "asc");
				if ((strlen($_GET['sort_by'])) && (in_array($_GET['sort_by'], $sorts_by)))
				{
					$arResult['SORT_BY'] = $_GET['sort_by'];
					$_SESSION['SORT_BY_REQVS'] = $arResult['SORT_BY'];
				}
				else
				{
					if (strlen($_SESSION['SORT_BY_REQVS']))
					{
						$arResult['SORT_BY'] = $_SESSION['SORT_BY_REQVS'];
					}
					else
					{
						$arResult['SORT_BY'] = $sorts_by[0];
					}
				}
				if ((strlen($_GET['sort'])) && (in_array($_GET['sort'], $sorts)))
				{
					$arResult['SORT'] = $_GET['sort'];
					$_SESSION['SORT_REQVS'] = $arResult['SORT'];
				}
				else
				{
					if (strlen($_SESSION['SORT_REQVS']))
					{
						$arResult['SORT'] = $_SESSION['SORT_REQVS'];
					}
					else
					{
						$arResult['SORT'] = $sorts[0];
					}
				}
				*/
				
				if ($arResult['USER_BRANCH'])
				{
					$filter['PROPERTY_BRANCH'] = $arResult['USER_BRANCH'];
				}
				$res = CIBlockElement::GetList(
					array($arResult['SORT_BY'] => $arResult['SORT']), 
					$filter, 
					false, 
					$nav_array, 
					array(
						"ID",
						"DATE_CREATE",
						"CREATED_BY",
						"PROPERTY_COMPANY_SENDER",
						"PROPERTY_CITY_SENDER.NAME",
						"PROPERTY_company_recipient",
						"PROPERTY_city_recipient.name",
						"PROPERTY_places",
						"PROPERTY_weight",
						"PROPERTY_SIZE_1",
						"PROPERTY_SIZE_2",
						"PROPERTY_SIZE_3",
						"PROPERTY_STATE",
						"PROPERTY_NUMBER",
						'PROPERTY_FILES',
						'PROPERTY_COMMENT',
						"PROPERTY_DATE_TAKE",
						"PROPERTY_TIME_TAKE_FROM",
						"PROPERTY_TIME_TAKE_TO"
					)
				);
				while ($ob = $res->GetNextElement())
				{
					$a = $ob->GetFields();
					$a["PROPERTY_OB_WEIGHT"] = ($a["PROPERTY_SIZE_1_VALUE"]*$a["PROPERTY_SIZE_2_VALUE"]*$a["PROPERTY_SIZE_3_VALUE"])/$arResult['CURRENT_AGENT_COEFFICIENT_VW'];
					$rsUserCr = CUser::GetByID($a['CREATED_BY']);
					$arUserCr = $rsUserCr->Fetch();
					$a['CREATED_BY_NAME'] = $arUserCr['NAME'].' '.$arUserCr['LAST_NAME'];
					$a['ColorRow'] = '';
					$a['state_icon'] = '';
					switch ($a['PROPERTY_STATE_VALUE'])
					{
						case 'Черновик' :
							if ($arResult['ADMIN_AGENT'])
							{
								$a['state_icon'] = '<span class="glyphicon glyphicon-file" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Открыть заявку"></span>';
							}
							else
							{
								$a['state_icon'] = '<span class="glyphicon glyphicon-edit" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Редактировать заявку"></span>';
							}
							
						break;
						case 'Оформлено' :
							$a['state_icon'] = '<span class="glyphicon glyphicon-edit" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Редактировать заявку"></span>';
							break;
						case 'Отправлено' :
							$a['state_icon'] = '<span class="glyphicon glyphicon-time" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Открыть заявку"></span>';
						break;
						case 'Отказ' :
							$a['state_icon'] = '<span class="glyphicon glyphicon-warning-sign" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Открыть заявку"></span>';
							$a['ColorRow'] = 'danger';
						break;
					}
					$arResult['REQUESTS'][] = $a;
				}
				$arResult['STATES'] = array();
				$db_enum_list = CIBlockProperty::GetPropertyEnum(518, Array(), Array("IBLOCK_ID"=>82));
				while($ar_enum_list = $db_enum_list->GetNext())
				{
					$arResult['STATES'][$ar_enum_list["ID"]] = $ar_enum_list["VALUE"];
				}
				$arResult['TITLE'] = GetMessage("TITLE_MODE_LIST");
				$APPLICATION->SetTitle(GetMessage("TITLE_MODE_LIST"));
				
				$datetime = strtotime($arResult['CURRENT_YEAR'].'-'.$arResult['CURRENT_MONTH'].'-01');
				$last_day = date('t', $datetime);
				
				$arResult['times'][] = array('name' => 'Выборка заявок на сайте', 'val' => microtime(true) - $start);
				
				
				if (in_array($arParams["TYPE"], array(51,53,242))) //запрашиваем заявки только для агентов и УК (+ для клиентов - 242)
				{
					if ($arResult['USER_BRANCH'])
					{
						if ((strlen(trim($arResult['BRANCH_INFO']['PROPERTY_IN_1C_CODE_VALUE']))) && (strlen(trim($arResult['BRANCH_INFO']['PROPERTY_IN_1C_PREFIX_VALUE']))))
						{
							$arParamsJson = array(
								'BranchID' => iconv('windows-1251','utf-8',$arResult['BRANCH_INFO']['PROPERTY_IN_1C_CODE_VALUE']),
								'BranchPrefix' => iconv('windows-1251','utf-8',$arResult['BRANCH_INFO']['PROPERTY_IN_1C_PREFIX_VALUE']),
								'StartDate' => $arResult['CURRENT_YEAR'].'-'.$arResult['CURRENT_MONTH'].'-01',
								'EndDate' => $arResult['CURRENT_YEAR'].'-'.$arResult['CURRENT_MONTH'].'-'.$last_day,
								'NumPage' => 0,
								'DocsToPage' => 1,
								'Type' => 2
							);
							

							/*
							$result_0 = $client->GetDocsListBranch($arParamsJson);
							
							$arResult['times'][] = array('name' => 'Получение данных из 1с 1 (предварительное)', 'val' => microtime(true) - $start);
							
							$mResult_0 = $result_0->return;
							$obj_0= json_decode($mResult_0, true);
							if (intval($obj_0['TotalDocs']) > 0)
							{
								$arParamsJson['DocsToPage'] = (intval($obj_0['TotalDocs']));
								*/	
								

								
								$arParamsJson['DocsToPage'] = 10000;	
								$result = $client->GetDocsListBranch($arParamsJson);
								
								$arResult['times'][] = array('name' => 'Получение данных из 1с 1', 'val' => microtime(true) - $start);
								
								$mResult = $result->return;
								$obj = json_decode($mResult, true);
								foreach ($obj['Docs'] as $d)
								{
									$h = $d;
									$events = $h['Events'];
									
									unset($h['Events']);
									$m = array();
									foreach ($h as $k => $v)
									{
										
										if ($k != 'Dimensions')
										{
											$m[$k] = iconv('utf-8', 'windows-1251', $v);
										}
										else
										{
											if (is_array($v['Dimension_1']))
											{
												foreach ($v['Dimension_1'] as $kk => $vv)
												{
													$m[$kk] = $vv;
												}
											}
										}
									}
									$m['CitySenderName'] = '';
									$m['CityRecipientName'] = '';
									if (intval($m['CitySender']) > 0)
									{
										$rr = CIBlockElement::GetByID(intval($m['CitySender']));
										if($ar_rr = $rr->GetNext())
										{
											$m['CitySenderName'] = $ar_rr['NAME'];
										}
									}
									if (intval($m['CityRecipient']) > 0)
									{
										$rr = CIBlockElement::GetByID(intval($m['CityRecipient']));
										if($ar_rr = $rr->GetNext())
										{
											$m['CityRecipientName'] = $ar_rr['NAME'];
										}
									}
									$m['ObW'] = WeightFormat((($m['Size_1']*$m['Size_2']*$m['Size_3'])/$arResult['CURRENT_AGENT_COEFFICIENT_VW']),false);
									$m['events'] = array();	
									$m['state'] = 'Принято';
									if (count($events) > 0)
									{
										foreach ($events as $ev)
										{
											$ee = array();
											foreach ($ev as $kkk => $vvv)
											{
												$ee[$kkk] = iconv('utf-8', 'windows-1251', $vvv);
											}
											$m['state'] = $ee['Event'];
											$m['events'][] = $ee;
										}
									}
									$arResult['ARCHIVE'][] = $m;
								}
								
								$arResult['times'][] = array('name' => 'Разбор данных из 1с 1', 'val' => microtime(true) - $start);
						//	}
							
							$arParamsJson['Type'] = 1;
							/*
							$arParamsJson['DocsToPage'] = 1;
							
							
							$result_1 = $client->GetDocsListBranch($arParamsJson);
							
							$arResult['times'][] = array('name' => 'Получение данных из 1с 2 (предварительное)', 'val' => microtime(true) - $start);
							
							$mResult_1 = $result_1->return;
							$obj_1= json_decode($mResult_1, true);
					
						
							if (intval($obj_1['TotalDocs']) > 0)
							{
						
								$arParamsJson['DocsToPage'] = (intval($obj_1['TotalDocs']));
								*/
								$arParamsJson['DocsToPage'] = 10000;
								$result = $client->GetDocsListBranch($arParamsJson);
								
								
								$arResult['times'][] = array('name' => 'Получение данных из 1с 2', 'val' => microtime(true) - $start);
								
								$mResult = $result->return;
								$obj = json_decode($mResult, true);
								foreach ($obj['Docs'] as $d)
								{
									$h = $d;
									$events = $h['Events'];
									
									unset($h['Events']);
									$m = array();
									foreach ($h as $k => $v)
									{
										
										if ($k != 'Dimensions')
										{
											$m[$k] = iconv('utf-8', 'windows-1251', $v);
										}
										else
										{
											if (is_array($v['Dimension_1']))
											{
												foreach ($v['Dimension_1'] as $kk => $vv)
												{
													$m[$kk] = $vv;
												}
											}
										}
									}
									$m['CitySenderName'] = '';
									$m['CityRecipientName'] = '';
									if (intval($m['CitySender']) > 0)
									{
										$rr = CIBlockElement::GetByID(intval($m['CitySender']));
										if($ar_rr = $rr->GetNext())
										{
											$m['CitySenderName'] = $ar_rr['NAME'];
										}
									}
									if (intval($m['CityRecipient']) > 0)
									{
										$rr = CIBlockElement::GetByID(intval($m['CityRecipient']));
										if($ar_rr = $rr->GetNext())
										{
											$m['CityRecipientName'] = $ar_rr['NAME'];
										}
									}
									$m['ObW'] = WeightFormat((($m['Size_1']*$m['Size_2']*$m['Size_3'])/$arResult['CURRENT_AGENT_COEFFICIENT_VW']),false);
									$m['events'] = array();	
									$m['state'] = 'Принято';
									if (count($events) > 0)
									{
										foreach ($events as $ev)
										{
											$ee = array();
											foreach ($ev as $kkk => $vvv)
											{
												$ee[$kkk] = iconv('utf-8', 'windows-1251', $vvv);
											}
											$m['state'] = $ee['Event'];
											$m['stateDescr'] = $ee['InfoEvent'];
											$m['events'][] = $ee;
										}
									}
									$arResult['ARCHIVE'][] = $m;
								}
								
								$arResult['times'][] = array('name' => 'Разбор данных из 1с 2', 'val' => microtime(true) - $start);
						//	}
						}
						else
						{
							$arResult["WARNINGS"][] = 'Не заданы необходимые параметры филиала. Обратитесь в <a href="/support/">тех. поддержку</a>.';
						}
					}
					else
					{
						$arParamsJson = array(
							'INN' => trim($arResult['CURRENT_INN']),
							'StartDate' => $arResult['CURRENT_YEAR'].'-'.$arResult['CURRENT_MONTH'].'-01',
							'EndDate' => $arResult['CURRENT_YEAR'].'-'.$arResult['CURRENT_MONTH'].'-'.$last_day,
							'NumPage' => 0,
							'DocsToPage' => 1,
							'Type' => 3
						);
						/*
						$result_0 = $client->GetDocsListAgent($arParamsJson);
						
						$arResult['times'][] = array('name' => 'Получение данных из 1с 3 (предварительное)', 'val' => microtime(true) - $start);
						
						$mResult_0 = $result_0->return;
						
						
						$obj_0= json_decode($mResult_0, true);
						if (intval($obj_0['TotalDocs']) > 0)
						{
							$arParamsJson['DocsToPage'] = (intval($obj_0['TotalDocs']));
							*/
							
							
			
			
							$arParamsJson['DocsToPage'] = 10000;
                        /*if ($arResult['CURRENT_AGENT'] == 10221516)
                        {
                            $arParamsJson['DocsToPage'] = 0;
                        }
                        */
						/*
            if ($arResult['ADMIN_AGENT'])
			{
				
				echo '<pre>';
				print_r($arParamsJson);
				echo '</pre>';
				
			}
						*/
                        
							$result = $client->GetDocsListAgent($arParamsJson);
							
							$arResult['times'][] = array('name' => 'Получение данных из 1с 3', 'val' => microtime(true) - $start);
		
							$mResult = $result->return;
							$obj = json_decode($mResult, true);
							
			/*if ($arResult['ADMIN_AGENT'])
			{
				
				echo '<pre>';
				print_r($obj);
				echo '</pre>';
				
			}*/
			
							foreach ($obj['Docs'] as $d)
							{
								/*
								if ($arResult['USER_BRANCH'])
								{
									if ($arResult['BRANCH_IN_1C'] != iconv('utf-8', 'windows-1251', $d['ZakazName']))
									{
										continue;
									}
								}
								*/
								$h = $d;
								$events = $h['Events'];
								
								unset($h['Events']);
								$m = array();
                                $m['Places'] = 0;
                                $m['ObW'] = 0;
                                $m['Weight'] = 0;
								foreach ($h as $k => $v)
								{
									
									if ($k != 'Dimensions')
									{
										$m[$k] = iconv('utf-8', 'windows-1251', $v);
									}
									else
									{
                                        foreach ($v as $arGb)
                                        {
                                            $m['Weight'] = $m['Weight'] + floatval(str_replace(',','.',$arGb['Weight']));
                                            $m['ObW'] = $m['ObW'] + floatval(str_replace(',','.',$arGb['WeightV']));
                                            $m['Places'] = $m['Places'] + intval($arGb['Places']);
                                        }
                                        $m['Weight'] = WeightFormat($m['Weight'], false);
                                        $m['ObW'] = WeightFormat($m['ObW'], false);
                                        /*
										if (is_array($v['Dimension_1']))
										{
											foreach ($v['Dimension_1'] as $kk => $vv)
											{
												$m[$kk] = $vv;
											}
										}
                                        */
									}
								}
                                if ($arResult['ADMIN_AGENT'])
                                {
                                    /*
                                    echo '<pre>';
                                    print_r($m);
                                    echo '</pre>';
                                    */
                                }
                                
								$m['CitySenderName'] = '';
								$m['CityRecipientName'] = '';
								if (intval($m['CitySender']) > 0)
								{
									$rr = CIBlockElement::GetByID(intval($m['CitySender']));
									if($ar_rr = $rr->GetNext())
									{
										$m['CitySenderName'] = $ar_rr['NAME'];
									}
								}
								if (intval($m['CityRecipient']) > 0)
								{
									$rr = CIBlockElement::GetByID(intval($m['CityRecipient']));
									if($ar_rr = $rr->GetNext())
									{
										$m['CityRecipientName'] = $ar_rr['NAME'];
									}
								}
								// $m['ObW'] = WeightFormat((($m['Size_1']*$m['Size_2']*$m['Size_3'])/$arResult['CURRENT_AGENT_COEFFICIENT_VW']),false);
								$m['events'] = array();	
								$m['state'] = 'Принято';
								if (count($events) > 0)
								{
									foreach ($events as $ev)
									{
										$ee = array();
										foreach ($ev as $kkk => $vvv)
										{
											$ee[$kkk] = iconv('utf-8', 'windows-1251', $vvv);
										}
										$m['state'] = $ee['Event'];
										$m['stateDescr'] = $ee['InfoEvent'];
										$m['events'][] = $ee;
									}
								}
								$arResult['ARCHIVE'][] = $m;
							}
							
							$arResult['times'][] = array('name' => 'Разбор данных из 1с 3', 'val' => microtime(true) - $start);
					//	}
					
						
						
						
					//	$arParamsJson['Type'] = 1;
						/*
						$arParamsJson['DocsToPage'] = 1;
						
						
						$result_1 = $client->GetDocsListAgent($arParamsJson);
						
						$arResult['times'][] = array('name' => 'Получение данных из 1с 4 (предварителное)', 'val' => microtime(true) - $start);
						
						$mResult_1 = $result_1->return;
						$obj_1= json_decode($mResult_1, true);
				
					
						if (intval($obj_1['TotalDocs']) > 0)
						{
					
							$arParamsJson['DocsToPage'] = (intval($obj_1['TotalDocs']));
							*/
							/*
							$arParamsJson['DocsToPage'] = 10000;
		
							$result = $client->GetDocsListAgent($arParamsJson);
							
							$arResult['times'][] = array('name' => 'Получение данных из 1с 4', 'val' => microtime(true) - $start);
							
							$mResult = $result->return;
							$obj = json_decode($mResult, true);
							
							/*
							if ($USER->IsAdmin())
							{
							echo '<pre>';
							print_r($mResult);
							echo '</pre>';
							}
							*/
							
							/*
							
							foreach ($obj['Docs'] as $d)
							{
								*/
								/*
								if ($arResult['USER_BRANCH'])
								{
									if ($arResult['BRANCH_IN_1C'] != iconv('utf-8', 'windows-1251', $d['ZakazName']))
									{
										continue;
									}
								}
								*/
								/*
								$h = $d;
								$events = $h['Events'];
								
								unset($h['Events']);
								$m = array();
								foreach ($h as $k => $v)
								{
									
									if ($k != 'Dimensions')
									{
										$m[$k] = iconv('utf-8', 'windows-1251', $v);
									}
									else
									{
										if (is_array($v['Dimension_1']))
										{
											foreach ($v['Dimension_1'] as $kk => $vv)
											{
												$m[$kk] = $vv;
											}
										}
									}
								}
								$m['CitySenderName'] = '';
								$m['CityRecipientName'] = '';
								if (intval($m['CitySender']) > 0)
								{
									$rr = CIBlockElement::GetByID(intval($m['CitySender']));
									if($ar_rr = $rr->GetNext())
									{
										$m['CitySenderName'] = $ar_rr['NAME'];
									}
								}
								if (intval($m['CityRecipient']) > 0)
								{
									$rr = CIBlockElement::GetByID(intval($m['CityRecipient']));
									if($ar_rr = $rr->GetNext())
									{
										$m['CityRecipientName'] = $ar_rr['NAME'];
									}
								}
								$m['ObW'] = WeightFormat((($m['Size_1']*$m['Size_2']*$m['Size_3'])/$arResult['CURRENT_AGENT_COEFFICIENT_VW']),false);
								$m['events'] = array();	
								$m['state'] = 'Принято';
								if (count($events) > 0)
								{
									foreach ($events as $ev)
									{
										$ee = array();
										foreach ($ev as $kkk => $vvv)
										{
											$ee[$kkk] = iconv('utf-8', 'windows-1251', $vvv);
										}
										$m['state'] = $ee['Event'];
										$m['stateDescr'] = $ee['InfoEvent'];
										$m['events'][] = $ee;
									}
								}
								$arResult['ARCHIVE'][] = $m;
							}
							
							$arResult['times'][] = array('name' => 'Разбор данных из 1с 4', 'val' => microtime(true) - $start);
				//		}
					*/
				
					}
				}  //запрашиваем заявки только для агентов
				
				foreach ($arResult['ARCHIVE']  as $k => $v)
				{
					if ($agent_type == 242)
					{
						$arResult['ARCHIVE'][$k]['stateEdit'] = 'Доставляется';
						$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-send" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр заявки"></span>';
						$arResult['ARCHIVE'][$k]['ColorRow'] = '';
					}
					else
					{
						$arResult['ARCHIVE'][$k]['stateEdit'] = $v['state'];
						$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-file" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр заявки"></span>';
						$arResult['ARCHIVE'][$k]['ColorRow'] = 'warning';
					}
					$arResult['ARCHIVE'][$k]['start_date'] = strlen($v['Date_Create']) ? substr($v['Date_Create'],8,2).'.'.substr($v['Date_Create'],5,2).'.'.substr($v['Date_Create'],0,4) : $v['DateDoc'];
					$arResult['ARCHIVE'][$k]['DateOfCompletion'] = substr($v['DateOfCompletion'],8,2).'.'.substr($v['DateOfCompletion'],5,2).'.'.substr($v['DateOfCompletion'],0,4);
					
					if ($agent_type == 242)
					{
						switch ($v['state'])
						{
							case 'Исключительная ситуация!' :
								$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр заявки"></span>';
								$arResult['ARCHIVE'][$k]['stateEdit'] = $v['stateDescr'];
								$arResult['ARCHIVE'][$k]['ColorRow'] = 'danger';
								break;
							case 'Доставлено' :
								$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-check" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр заявки"></span>';
								$arResult['ARCHIVE'][$k]['stateEdit'] = $v['state'];
								$arResult['ARCHIVE'][$k]['ColorRow'] = 'supersuccess';
							break;
						}
					}
					else
					{
						switch ($v['state'])
						{
							case 'Отправлено в город' :
								$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-send" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр заявки"></span>';
								$arResult['ARCHIVE'][$k]['stateEdit'] = $v['stateDescr'];
							break;
							case 'Выдано курьеру на маршрут' :
								$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-road" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр заявки"></span>';
								$arResult['ARCHIVE'][$k]['stateEdit'] = 'Выдано на маршрут';
							break;
							case 'Исключительная ситуация!' :
								$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр заявки"></span>';
								$arResult['ARCHIVE'][$k]['stateEdit'] = $v['stateDescr'];
								$arResult['ARCHIVE'][$k]['ColorRow'] = 'danger';
							break;
							case 'Отмена заявки' :
								$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр заявки"></span>';
								$arResult['ARCHIVE'][$k]['stateEdit'] = $v['state'].'&nbsp;'.$v['stateDescr'];
								$arResult['ARCHIVE'][$k]['ColorRow'] = 'danger';
							break;
							case 'Принято' :
								$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-log-in" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр заявки"></span>';
								$arResult['ARCHIVE'][$k]['stateEdit'] = $v['state'];
								$arResult['ARCHIVE'][$k]['ColorRow'] = 'success';
							break;
							case 'Оприходовано офисом' :
								$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-download-alt" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр заявки"></span>';
								$arResult['ARCHIVE'][$k]['stateEdit'] = $v['stateDescr'];
							break;
							case 'Доставлено' :
								$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-check" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр заявки"></span>';
								$arResult['ARCHIVE'][$k]['stateEdit'] = $v['state'];
								$arResult['ARCHIVE'][$k]['ColorRow'] = 'supersuccess';
							break;
							default:
								$arResult['ARCHIVE'][$k]['stateEdit'] = $v['state'];
							break;
						}
					}
					$arResult['ARCHIVE'][$k]['numberin'] = '';
					$arResult['ARCHIVE'][$k]['files'] = array();
					if (strlen($v['ID']))
					{
						$db_props = CIBlockElement::GetProperty(82, $v['ID'], array("sort" => "asc"), array("ID"=>583));
						while ($ar_props = $db_props->Fetch())
						{
							if (intval($ar_props["VALUE"]) > 0)
							{
								$arResult['ARCHIVE'][$k]['files'][] = $ar_props["VALUE"];
							}
						}
						$db_props = CIBlockElement::GetProperty(82, $v['ID'], array("sort" => "asc"), array("ID"=>626));
						if ($ar_props = $db_props->Fetch())
						{
							$arResult['ARCHIVE'][$k]['numberin'] = $ar_props["VALUE"];
						}
						
					}
					$arResult['ARCHIVE'][$k]['CREATED_BY_NAME'] = '';
					if (strlen($v['ID']))
					{
						$resCr = CIBlockElement::GetByID($v['ID']);
						if ($ar_resCr = $resCr->GetNext())
						{
							$u_cr = $ar_resCr['CREATED_BY'];
							$rsUserCr = CUser::GetByID($u_cr);
							$arUserCr = $rsUserCr->Fetch();
							$arResult['ARCHIVE'][$k]['CREATED_BY_NAME'] = $arUserCr['NAME'].' '.$arUserCr['LAST_NAME'];
						}
					}
				}
				
				$arResult['times'][] = array('name' => 'Анализ данных, полученных из 1с', 'val' => microtime(true) - $start);
			}
			else
			{
				if ($arResult['ADMIN_AGENT'])
				{
					if ($arParams["TYPE"] == 242)
					{
						$arResult["WARNINGS"][] = 'Не выбран клиент';
					}
					else
					{
						$arResult["WARNINGS"][] = 'Не выбран агент';
					}
					
				}
				else
				{
					$arResult["WARNINGS"][] = 'Ошибка в профайле пользователя. Пожалуйста, обратитесь в <a href="/support/">тех. поддержку</a>';
				}
			}
            
            //поик сообщений для агента
            $arResult["MESS"] = array();
            $res = CIBlockElement::GetList(
                array("id" => "desc"), 
                array("IBLOCK_ID" => 92, "ACTIVE" => "Y", "PROPERTY_TO" => $arResult['CURRENT_AGENT']),
                false, 
                false, 
                array(
                    "ID","NAME","PROPERTY_FROM","PROPERTY_TYPE","PROPERTY_COMMENT"
                )
            );
            while ($ob = $res->GetNextElement())
            {
                $arResult["MESS"][] = $ob->GetFields();
            }
            //поик сообщений для агента
            
            //Формирование JSON-строки для xls-файла
            $arARCHIVEutf = array( 
                array(
                    iconv('windows-1251', 'utf-8', 'Номер заявки'),
                    iconv('windows-1251', 'utf-8', 'Вн. номер заявки'),
                    iconv('windows-1251', 'utf-8', 'Дата'),
                    iconv('windows-1251', 'utf-8', 'Выполнить'),
                    iconv('windows-1251', 'utf-8', 'Город отправителя'),
                    iconv('windows-1251', 'utf-8', 'Комп. отправителя'),
                    iconv('windows-1251', 'utf-8', 'Город получателя'),
                    iconv('windows-1251', 'utf-8', 'Комп. получателя'),
                    iconv('windows-1251', 'utf-8', 'Кол.'),
                    iconv('windows-1251', 'utf-8', 'Вес'),
                    iconv('windows-1251', 'utf-8', 'Об. вес'),
                    iconv('windows-1251', 'utf-8', 'Номер накладной'),
                    iconv('windows-1251', 'utf-8', 'Статус'),
                    iconv('windows-1251', 'utf-8', 'Кем создана'),
                    iconv('windows-1251', 'utf-8', 'Отв. менеджер')
                )
            );
            foreach ($arResult['REQUESTS'] as $r)
            {
                $date_take_value = $r['PROPERTY_DATE_TAKE_VALUE'];
                $date_take_value .= strlen($r['PROPERTY_TIME_TAKE_FROM_VALUE']) ? ' с '.$r['PROPERTY_TIME_TAKE_FROM_VALUE'] : '';
                $date_take_value .= strlen($r['PROPERTY_TIME_TAKE_TO_VALUE']) ? ' до '.$r['PROPERTY_TIME_TAKE_TO_VALUE'] : '';
                $arARCHIVEutf[] = array(
                    iconv('windows-1251', 'utf-8', $r['PROPERTY_NUMBER_VALUE']),
                    iconv('windows-1251', 'utf-8', $r['PROPERTY_NUMBER_IN_VALUE']),
                    iconv('windows-1251', 'utf-8', substr($r['DATE_CREATE'],0,10)),
                    iconv('windows-1251', 'utf-8', $date_take_value),
                    iconv('windows-1251', 'utf-8', $r['PROPERTY_CITY_SENDER_NAME']),
                    iconv('windows-1251', 'utf-8', $r['PROPERTY_COMPANY_SENDER_VALUE']),
                    iconv('windows-1251', 'utf-8', $r['PROPERTY_CITY_RECIPIENT_NAME']),
                    iconv('windows-1251', 'utf-8', $r['PROPERTY_COMPANY_RECIPIENT_VALUE']),
                    iconv('windows-1251', 'utf-8', $r['PROPERTY_PLACES_VALUE']),
                    iconv('windows-1251', 'utf-8', $r['PROPERTY_WEIGHT_VALUE']),
                    iconv('windows-1251', 'utf-8', $r['PROPERTY_OB_WEIGHT']),
                    '',
                    iconv('windows-1251', 'utf-8', $r['PROPERTY_STATE_VALUE']),
                    iconv('windows-1251', 'utf-8', $r['CREATED_BY_NAME']),
                    ''
                );
            }
            foreach ($arResult['ARCHIVE'] as $r)
            {
                $name_sender = strlen($r['CompanySender']) ? $r['CompanySender'] : $r['NameSender'];
                $companyRecipient = strlen($r['CompanyRecipient']) ? $r['CompanyRecipient'] : $r['NameRecipient'];
                $arARCHIVEutf[] = array(
                    iconv('windows-1251', 'utf-8', $r['NumRequest']),
                    iconv('windows-1251', 'utf-8', $r['numberin']),
                    iconv('windows-1251', 'utf-8', $r['start_date']),
                    iconv('windows-1251', 'utf-8', $r['DateOfCompletion']),
                    iconv('windows-1251', 'utf-8', $r['CitySenderName']),
                    iconv('windows-1251', 'utf-8', $name_sender),
                    iconv('windows-1251', 'utf-8', $r['CityRecipientName']),
                    iconv('windows-1251', 'utf-8', $companyRecipient),
                    iconv('windows-1251', 'utf-8', $r['Places']),
                    iconv('windows-1251', 'utf-8', str_replace(',','.',$r['Weight'])),
                    iconv('windows-1251', 'utf-8', str_replace(',','.',$r['ObW'])),
                    iconv('windows-1251', 'utf-8', $r['NumDoc']),
                    iconv('windows-1251', 'utf-8', $r['stateEdit']),
                    iconv('windows-1251', 'utf-8', $r['CREATED_BY_NAME']),
                    iconv('windows-1251', 'utf-8', $r['Manager'])
                );
            }
            $arResult['ARCHIVE_STR_JSON'] = json_encode($arARCHIVEutf);
		}
		
		if (($mode == 'request') || ($mode == 'request_modal'))
		{
			$arResult['REQUEST'] = false;
			$id_reqv = intval($_GET['id']);
			$filter = array("IBLOCK_ID" => 82, "ID" => $id_reqv, "PROPERTY_CREATOR" => $agent_id);
			if ($arResult['ADMIN_AGENT'])
			{
				unset($filter["PROPERTY_CREATOR"]);
			}
			if ($id_reqv > 0)
			{
				$res = CIBlockElement::GetList(
					array("id" => "desc"), 
					$filter,
					false, 
					false, 
					array(
						"ID",
						"DATE_CREATE",
						"CREATED_BY",
						"PROPERTY_NAME_SENDER",
						"PROPERTY_PHONE_SENDER",
						"PROPERTY_COMPANY_SENDER",
						"PROPERTY_CITY_SENDER",
						"PROPERTY_INDEX_SENDER",
						"PROPERTY_ADRESS_SENDER",
						"PROPERTY_NAME_RECIPIENT",
						"PROPERTY_PHONE_RECIPIENT",
						"PROPERTY_COMPANY_RECIPIENT",
						"PROPERTY_CITY_RECIPIENT",
						"PROPERTY_INDEX_RECIPIENT",
						"PROPERTY_ADRESS_RECIPIENT",
						"PROPERTY_DATE_TAKE",
						"PROPERTY_TIME_TAKE_FROM",
						"PROPERTY_TIME_TAKE_TO",
						"PROPERTY_TYPE",
						"PROPERTY_TYPE_DELIVERY",
						"PROPERTY_DELIVERY_PAYER",
						"PROPERTY_TYPE_CASH",
						"PROPERTY_DELIVERY_CONDITION",
						"PROPERTY_PAYMENT_AMOUNT",
						"PROPERTY_COST",
						"PROPERTY_PLACES",
						"PROPERTY_WEIGHT",
						"PROPERTY_OB_WEIGHT",
						"PROPERTY_STATE",
						"PROPERTY_NUMBER",
						"PROPERTY_INSTRUCTIONS",
						'PROPERTY_SIZE_1',
						'PROPERTY_SIZE_2',
						'PROPERTY_SIZE_3',
						'PROPERTY_DATE',
						'PROPERTY_DATE_ADOPTION',
						'PROPERTY_FILES',
						'PROPERTY_NUMBER_IN',
						'PROPERTY_COMMENT'
					)
				);
				if ($ob = $res->GetNextElement())
				{
					$r = $ob->GetFields();
					if (in_array($r['PROPERTY_STATE_ENUM_ID'], $arResult['modes_edit']))
					{
						// LocalRedirect($arParams['LINK']."index.php?mode=request_edit&id=".$_GET['id']);
					}
					$d_start = substr($r['PROPERTY_COURIER_FROM_VALUE'], 0, 10);
					$d_end = substr($r['PROPERTY_COURIER_TO_VALUE'], 0, 10);
					if ($d_start == $d_end)
					{
						$r['DATE_COURIER'] = $d_start.' '.substr($r['PROPERTY_COURIER_FROM_VALUE'], 11, 5).' - '.substr($r['PROPERTY_COURIER_TO_VALUE'], 11, 5);
					}
					else
					{
						$r['DATE_COURIER'] = $d_start.' '.substr($r['PROPERTY_COURIER_FROM_VALUE'], 11, 5).' - '.$d_end.' '.substr($r['PROPERTY_COURIER_TO_VALUE'], 11, 5);
					}
					$r['PROPERTY_CITY_SENDER'] = GetFullNameOfCity($r['PROPERTY_CITY_SENDER_VALUE']);
					$r['PROPERTY_CITY_RECIPIENT'] = GetFullNameOfCity($r['PROPERTY_CITY_RECIPIENT_VALUE']);
					$r['FILES'] = array();
					foreach ($r['PROPERTY_FILES_VALUE'] as $file_id)
					{
						$r['FILES'][] = CFile::GetFileArray($file_id);
					}
					$rsUserCr = CUser::GetByID($r['CREATED_BY']);
					$arUserCr = $rsUserCr->Fetch();
					$r['CREATED_BY_NAME'] = $arUserCr['NAME'].'&nbsp;'.$arUserCr['LAST_NAME'];
					
					//получение сообщений//
					$result = $client->GetDocComments(array('NUMDOC' => iconv('windows-1251','utf-8', trim($_GET['NumDoc'])), 'NUMREQUEST' => iconv('windows-1251','utf-8', $r['PROPERTY_NUMBER_VALUE'])));
					$mResult = $result->return;
					$obj = json_decode($mResult, true);
					$r['Messages'] = false;
					if (is_array($obj[iconv('windows-1251','utf-8','Сообщения')]))
					{
						$r['Messages'] = $obj[iconv('windows-1251','utf-8','Сообщения')];
					}
					//получение сообщений//
					
					$arResult['REQUEST'] = $r;
					$arResult['TITLE'] = $arResult['REQUEST']['PROPERTY_NUMBER_VALUE'];
					$APPLICATION->SetTitle(GetMessage("TITLE_MODE_REQUEST", array("#NUMBER#" => $arResult['REQUEST']['PROPERTY_NUMBER_VALUE'])));
				}
				else
				{
					$arResult['TITLE'] = GetMessage('ERR_NO_REQUEST');
					$APPLICATION->SetTitle(GetMessage('ERR_NO_REQUEST'));
				}
			}
			else
			{
				$arResult['TITLE'] = GetMessage('ERR_NO_REQUEST');
				$APPLICATION->SetTitle(GetMessage('ERR_NO_REQUEST'));
			}
		}
		
		if ($mode == 'request_edit')
		{
			$arResult['FILES_ADD'] = array();
			$arResult['COUNT_FILES'] = 4;
			if ((isset($_POST['save'])) || (isset($_POST['save_ctrl'])))
			{
				if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
				{
					$_POST = array();
					$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
				}
				else
				{
					$_SESSION[$_POST["key_session"]] = $_POST["rand"];
					$arChanges = array(
						515 => deleteTabs($_POST['INDEX_SENDER']),
						526 => deleteTabs($_POST['INDEX_RECIPIENT']),
						533 => NewQuotes($_POST['INSTRUCTIONS']),
						522 => NewQuotes($_POST['NAME_RECIPIENT']),
						524 => NewQuotes($_POST['COMPANY_RECIPIENT']),
						523 => NewQuotes($_POST['PHONE_RECIPIENT']),
						527 => NewQuotes($_POST['ADRESS_RECIPIENT']),
						529 => deleteTabs($_POST['TIME_TAKE_FROM']),
						535 => deleteTabs($_POST['TIME_TAKE_TO']),
						517 => floatval(str_replace(',','.',$_POST['size_1'])),
						536 => floatval(str_replace(',','.',$_POST['size_2'])),
						537 => floatval(str_replace(',','.',$_POST['size_3'])),
						626 => NewQuotes($_POST['number_in']),
						687 => $_POST['TYPE_DELIVERY'],
						688 => $_POST['DELIVERY_PAYER'],
						689 => $_POST['DELIVERY_CONDITION'],
						690 => floatval(str_replace(',','.',$_POST['PAYMENT_AMOUNT'])),
						695 => floatval(str_replace(',','.',$_POST['COST']))
					);
					if (!strlen($_POST['NAME_SENDER']))
					{
						$arResult["ERRORS"][] = 'Не заполнено поле "ФИО отправителя"';
						$arResult['ERR_FIELDS']['NAME_SENDER'] = 'has-error';
					}
					else
					{
						$arChanges[511] = NewQuotes($_POST['NAME_SENDER']);
					}
					if (!strlen($_POST['PHONE_SENDER']))
					{
						$arResult["ERRORS"][] = 'Не заполнено поле "Телефон отправителя"';
						$arResult['ERR_FIELDS']['PHONE_SENDER'] = 'has-error';
					}
					else
					{
						$arChanges[512] = NewQuotes($_POST['PHONE_SENDER']);
					}
					if (!strlen($_POST['COMPANY_SENDER']))
					{
						$arResult["ERRORS"][] = 'Не заполнено поле "Компания-отправитель"';
						$arResult['ERR_FIELDS']['COMPANY_SENDER'] = 'has-error';
					}
					else
					{
						$arChanges[513] = NewQuotes($_POST['COMPANY_SENDER']);
					}
					if (!strlen($_POST['CITY_SENDER']))
					{
						$arResult["ERRORS"][] = 'Не заполнено поле "Город отправителя"';
						$arResult['ERR_FIELDS']['CITY_SENDER'] = 'has-error';
					}
					else
					{
						$city_cender = GetCityId(trim($_POST['CITY_SENDER']));
						if ($city_cender == 0)
						{
							$arResult["ERRORS"][] = GetMessage("ERR_NO_CITY_SENDER");
							$arResult['ERR_FIELDS']['CITY_SENDER'] = 'has-error';
						}
						else
						{
							$arChanges[514] = $city_cender;
						}
					}
					if (!strlen($_POST['ADRESS_SENDER']))
					{
						$arResult["ERRORS"][] = 'Не заполнено поле "Адрес отправителя"';
						$arResult['ERR_FIELDS']['ADRESS_SENDER'] = 'has-error';
					}
					else
					{
						$arChanges[516] = NewQuotes($_POST['ADRESS_SENDER']);
					}
					/*
					if (!strlen($_POST['NAME_RECIPIENT']))
					{
						$arResult["ERRORS"][] = 'Не заполнено поле "ФИО получателя"';
						$arResult['ERR_FIELDS']['NAME_RECIPIENT'] = 'has-error';
					}
					else
					{
						$arChanges[522] = trim($_POST['NAME_RECIPIENT']);
					}
					if (!strlen($_POST['PHONE_RECIPIENT']))
					{
						$arResult["ERRORS"][] = 'Не заполнено поле "Телефон получателя"';
					}
					else
					{
						$arChanges[523] = trim($_POST['PHONE_RECIPIENT']);
					}
					if (!strlen($_POST['COMPANY_RECIPIENT']))
					{
						$arResult["ERRORS"][] = 'Не заполнено поле "Компания-получатель"';
					}
					else
					{
						$arChanges[524] = trim($_POST['COMPANY_RECIPIENT']);
					}
					*/
					if (!strlen($_POST['CITY_RECIPIENT']))
					{
						$arResult["ERRORS"][] = 'Не заполнено поле "Город получателя"';
						$arResult['ERR_FIELDS']['CITY_RECIPIENT'] = 'has-error';
					}
					else
					{
						$city_recipient = GetCityId(trim($_POST['CITY_RECIPIENT']));
						if ($city_recipient == 0)
						{
							$arResult["ERRORS"][] = GetMessage("ERR_NO_CITY_RECIPIENT");
							$arResult['ERR_FIELDS']['CITY_RECIPIENT'] = 'has-error';
						}
						else
						{
							$arChanges[525] = $city_recipient;
						}
					}
					/*
					if (!strlen($_POST['ADRESS_RECIPIENT']))
					{
						$arResult["ERRORS"][] = 'Не заполнено поле "Адрес получателя"';
					}
					else
					{
						$arChanges[527] = trim($_POST['ADRESS_RECIPIENT']);
					}
					*/
					if (!strlen($_POST['DATE_TAKE'])) 
					{
						$arResult["ERRORS"][] = 'Не заполнено поле "Дата забора"';
						$arResult['ERR_FIELDS']['DATE_TAKE'] = 'has-error';
					}
					else
					{
						$arChanges[528] = deleteTabs($_POST['DATE_TAKE']);
					}
					if (!$_POST['TYPE'])
					{
						$arResult["ERRORS"][] = 'Не заполнено поле "Тип отправления"';
						$arResult['ERR_FIELDS']['TYPE'] = 'has-error';
					}
					else
					{
						$arChanges[530] = $_POST['TYPE'];
					}
					if (!$_POST['TYPE_CASH'])
					{
						$arResult["ERRORS"][] = 'Не заполнено поле "Оплата"';
						$arResult['ERR_FIELDS']['TYPE_CASH'] = 'has-error';
					}
					else
					{
						$arChanges[585] = $_POST['TYPE_CASH'];
					}
					
					$places = intval(str_replace(',','.',$_POST['PLACES']));
					$weight = floatval(str_replace(',','.',$_POST['WEIGHT']));
		
					if ($places <= 0)
					{
						$arResult["ERRORS"][] = 'Некорректное значения поля "Количество мест"';
						$arResult['ERR_FIELDS']['PLACES'] = 'has-error';
					}
					else
					{
						$arChanges[531] = $places;
					}
					if ($weight <= 0)
					{
						$arResult["ERRORS"][] = 'Некорректное значения поля "Вес"';
						$arResult['ERR_FIELDS']['WEIGHT'] = 'has-error';
					}
					else
					{
						$arChanges[532] = $weight;
					}
					/*
					if ($ob_weight <= 0)
					{
						$arResult["ERRORS"][] = GetMessage('ERR_WRONG_OB_WEIGHT');
					}
					else
					{
						$arChanges[517] = $ob_weight;
					}
					*/
					
					$arFilesAdd = array();
					foreach ($_POST['files_id_add'] as $fid)
					{
						if ($_POST['delete_file'][$fid] == 'Y')
						{
							CFile::Delete($fid);
						}
						else
						{
							$arFilesAdd[] = $fid;
							// $arResult['FILES_ADD'][] = CFile::GetFileArray($fid);
						}
					}
					
					foreach ($_FILES as $f)
					{
						if ((strlen($f['name'])) && ($f['size'] > 0))
						{
							$arr_file = array(
								"name" => $f['name'],
								"size" => $f['size'],
								"tmp_name" => $f['tmp_name'],
								"type" => "",
								"old_file" => "",
								"del" => "Y",
								"MODULE_ID" => "iblock"
							);
							$fid = CFile::SaveFile($arr_file, "files_requests");
							$arFilesAdd[] = $fid;
							$arResult['FILES_ADD'][] = CFile::GetFileArray($fid);
						}
					}
					$arResult['COUNT_FILES'] = $arResult['COUNT_FILES'] - count($arResult['FILES_ADD']);
					// $arChanges[583] = $arFilesAdd;
					CIBlockElement::SetPropertyValuesEx($_POST['id'], 82, $arChanges);
					$_SESSION['MESSAGE'][] = 'Заявка успешно изменена';
					LocalRedirect($arParams['LINK']."index.php");			
				}
			}
			$arResult['REQUEST'] = false;
			$id_reqv = intval($_GET['id']);
			if ($id_reqv > 0)
			{
				$filter = array("IBLOCK_ID" => 82, "ID" => $id_reqv, "PROPERTY_CREATOR" => $agent_id, "ACTIVE" => "Y");
				if ($arResult['ADMIN_AGENT'])
				{
					unset($filter["PROPERTY_CREATOR"]);
				}
				$res = CIBlockElement::GetList(
					array("id" => "desc"), 
					$filter, 
					false, 
					false, 
					array(
						"ID",
						"PROPERTY_NAME_SENDER",
						"PROPERTY_PHONE_SENDER",
						"PROPERTY_COMPANY_SENDER",
						"PROPERTY_CITY_SENDER",
						"PROPERTY_INDEX_SENDER",
						"PROPERTY_ADRESS_SENDER",
						"PROPERTY_NAME_RECIPIENT",
						"PROPERTY_PHONE_RECIPIENT",
						"PROPERTY_COMPANY_RECIPIENT",
						"PROPERTY_CITY_RECIPIENT",
						"PROPERTY_INDEX_RECIPIENT",
						"PROPERTY_ADRESS_RECIPIENT",
						"PROPERTY_DATE_TAKE",
						"PROPERTY_TIME_TAKE_FROM",
						"PROPERTY_TIME_TAKE_TO",
						"PROPERTY_TYPE",
						"PROPERTY_TYPE_DELIVERY",
						"PROPERTY_DELIVERY_PAYER",
						"PROPERTY_TYPE_CASH",
						"PROPERTY_DELIVERY_CONDITION",
						"PROPERTY_PAYMENT_AMOUNT",
						"PROPERTY_COST",
						"PROPERTY_PLACES",
						"PROPERTY_WEIGHT",
						"PROPERTY_SIZE_1",
						"PROPERTY_SIZE_2",
						"PROPERTY_SIZE_3",
						"PROPERTY_STATE",
						"PROPERTY_NUMBER",
						"PROPERTY_INSTRUCTIONS",
						"PROPERTY_FILES",
						'PROPERTY_COMMENT',
						"PROPERTY_NUMBER_IN",
					)
				);
				if ($ob = $res->GetNextElement())
				{
					$r = $ob->GetFields();
					
				//	if (!in_array($r['PROPERTY_STATE_ENUM_ID'], $arResult['modes_edit']))
					//{
						$d_start = substr($r['PROPERTY_COURIER_FROM_VALUE'], 0, 10);
						$d_end = substr($r['PROPERTY_COURIER_TO_VALUE'], 0, 10);
						if ($d_start == $d_end)
						{
							$r['DATE_COURIER'] = $d_start.' '.substr($r['PROPERTY_COURIER_FROM_VALUE'], 11, 5).' - '.substr($r['PROPERTY_COURIER_TO_VALUE'], 11, 5);
						}
						else
						{
							$r['DATE_COURIER'] = $d_start.' '.substr($r['PROPERTY_COURIER_FROM_VALUE'], 11, 5).' - '.$d_end.' '.substr($r['PROPERTY_COURIER_TO_VALUE'], 11, 5);
						}
						$r['PROPERTY_CITY_SENDER'] = GetFullNameOfCity($r['PROPERTY_CITY_SENDER_VALUE']);
						$r['PROPERTY_CITY_RECIPIENT'] = GetFullNameOfCity($r['PROPERTY_CITY_RECIPIENT_VALUE']);
						foreach ($r['PROPERTY_FILES_VALUE'] as $file_id)
						{
							$r['FILES'][] = CFile::GetFileArray($file_id);
						}
						$arResult['REQUEST'] = $r;
						$arResult['TITLE'] = $arResult['REQUEST']['PROPERTY_NUMBER_VALUE'];
						$APPLICATION->SetTitle('Редактирование заявки '.$arResult['TITLE']);
					/*
					}
					
					else
					{
						LocalRedirect($arParams["LINK"]."index.php?mode=request&id=".$_GET['id']);
					}
					*/
				}
				else
				{
					$arResult['TITLE'] = GetMessage('ERR_NO_REQUEST');
					$APPLICATION->SetTitle(GetMessage("ERR_NO_REQUEST"));
				}
			}
			else
			{
				$arResult['TITLE'] = GetMessage('ERR_NO_REQUEST');
				$APPLICATION->SetTitle(GetMessage("ERR_NO_REQUEST"));
			}
		}
		
		if ($mode == 'add')
		{
			$arResult['FILES_ADD'] = array();
			$arResult['COUNT_FILES'] = 4;
			if ((isset($_POST['add'])) || (isset($_POST['add_ctrl'])))
			{
				if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
				{
					$_POST = array();
					$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
				}
				else
				{
					$_SESSION[$_POST["key_session"]] = $_POST["rand"];
					
					if (!strlen($_POST['NAME_SENDER']))
					{
						//$arResult["ERRORS"][] = 'Не заполнено поле "ФИО отправителя"';
						$arResult["ERR_FIELDS"]["NAME_SENDER"] = 'has-error';
					}
					if (!strlen($_POST['PHONE_SENDER']))
					{
						//$arResult["ERRORS"][] = 'Не заполнено поле "Телефон отправителя"';
						$arResult["ERR_FIELDS"]["PHONE_SENDER"] = 'has-error';
					}
					if (!strlen($_POST['COMPANY_SENDER']))
					{
						//$arResult["ERRORS"][] = 'Не заполнено поле "Компания-отправитель"';
						$arResult["ERR_FIELDS"]["COMPANY_SENDER"] = 'has-error';
					}
					if (!strlen($_POST['CITY_SENDER']))
					{
						//$arResult["ERRORS"][] = 'Не заполнено поле "Город отправителя"';
						$arResult["ERR_FIELDS"]["CITY_SENDER"] = 'has-error';
					}
					else
					{
						$CITY_SENDER = GetCityId(trim($_POST['CITY_SENDER']));
						if ($CITY_SENDER == 0)
						{
							//$arResult["ERRORS"][] = 'Город отправителя не найден';
							$arResult["ERR_FIELDS"]["CITY_SENDER"] = 'has-error';
						}
					}
					if (!strlen($_POST['ADRESS_SENDER']))
					{
						//$arResult["ERRORS"][] = 'Не заполнено поле "Адрес отправителя"';
						$arResult["ERR_FIELDS"]["ADRESS_SENDER"] = 'has-error';
					}
					/*
					if (!strlen($_POST['name_recipient']))
					{
						$arResult["ERRORS"][] = 'Не заполнено поле "ФИО получателя"';
					}
					if (!strlen($_POST['phone_recipient']))
					{
						$arResult["ERRORS"][] = 'Не заполнено поле "Телефон получателя"';
					}
					if (!strlen($_POST['company_recipient']))
					{
						$arResult["ERRORS"][] = 'Не заполнено поле "Компания-получатель"';
					}
					*/
					if (!strlen($_POST['CITY_RECIPIENT']))
					{
						//$arResult["ERRORS"][] = 'Не заполнено поле "Город получателя"';
						$arResult["ERR_FIELDS"]["CITY_RECIPIENT"] = 'has-error';
					}
					else
					{
						$city_recipient = GetCityId(trim($_POST['CITY_RECIPIENT']));
						if ($city_recipient == 0)
						{
							//$arResult["ERRORS"][] = 'Город получателя не найден';
							$arResult["ERR_FIELDS"]["CITY_RECIPIENT"] = 'has-error';
						}
					}
					/*
					if (!strlen($_POST['adress_recipient']))
					{
						$arResult["ERRORS"][] = 'Не заполнено поле "Адрес получателя"';
					}
					*/
					if (!strlen($_POST['DATE_TAKE']))
					{
						//$arResult["ERRORS"][] = 'Не заполнено поле "Дата забора"';
						$arResult["ERR_FIELDS"]["DATE_TAKE"] = 'has-error';
					}
					if (!$_POST['TYPE'])
					{
						//$arResult["ERRORS"][] = 'Не заполнено поле "Тип отправления"';
						$arResult["ERR_FIELDS"]["TYPE"] = 'has-error';
					}
					if (!$_POST['TYPE_CASH'])
					{
						$arResult["ERR_FIELDS"]["TYPE_CASH"] = 'has-error';
					}
					$places = intval(str_replace(',','.',$_POST['PLACES']));
					$weight = floatval(str_replace(',','.',$_POST['WEIGHT']));
					$size_1 = floatval(str_replace(',','.',$_POST['size_1']));
					$size_2 = floatval(str_replace(',','.',$_POST['size_2']));
					$size_3 = floatval(str_replace(',','.',$_POST['size_3']));
					if ($places <= 0)
					{
						//$arResult["ERRORS"][] = 'Некорректное значения поля "Количество мест"';
						$arResult["ERR_FIELDS"]["PLACES"] = 'has-error';
					}
					if ($weight <= 0)
					{
						//$arResult["ERRORS"][] = 'Некорректное значения поля "Вес"';
						$arResult["ERR_FIELDS"]["WEIGHT"] = 'has-error';
					}
					/*
					if (($size_1 <= 0) || ($size_2 <= 0) || ($size_3 <= 0))
					{
						$arResult["ERRORS"][] = 'Некорректное значения одного или нескольких полей "Габаритов"';
					}
					*/
					$arFilesAdd = array();
					foreach ($_POST['files_id_add'] as $fid)
					{
						if ($_POST['delete_file'][$fid] == 'Y')
						{
							CFile::Delete($fid);
						}
						else
						{
							$arFilesAdd[] = $fid;
							$arResult['FILES_ADD'][] = CFile::GetFileArray($fid);
						}
					}
					
					foreach ($_FILES as $f)
					{
						if ((strlen($f['name'])) && ($f['size'] > 0))
						{
							$arr_file = array(
								"name" => $f['name'],
								"size" => $f['size'],
								"tmp_name" => $f['tmp_name'],
								"type" => "",
								"old_file" => "",
								"del" => "Y",
								"MODULE_ID" => "iblock"
							);
							$fid = CFile::SaveFile($arr_file, "files_requests");
							$arFilesAdd[] = $fid;
							$arResult['FILES_ADD'][] = CFile::GetFileArray($fid);
						}
					}
					$arResult['COUNT_FILES'] = $arResult['COUNT_FILES'] - count($arResult['FILES_ADD']);
					
					if (count($arResult["ERR_FIELDS"]) == 0)
					{
						$state = ($arParams['REGISTRATION'] == 1) ? 236 : 261;
						$id_in = GetMaxIDIN(82, 5, true, 538, $agent_id);
						
						$arParamsJson = array('INN' => $arResult['AGENT']['PROPERTY_INN_VALUE']);
						$result = $client->GetPrefixAgent1($arParamsJson);
						$mResult = $result->return;
						$obj = json_decode($mResult, true);
						
						/*
						if ($USER->GetID() == 211)
						{
							print_r($result);
						}
						*/
						
						$number = iconv('utf-8', 'windows-1251', $obj['Prefix_'.$arResult['AGENT']['PROPERTY_INN_VALUE']]);
						$number_success = intval($obj['Success']);
						// $number = $arResult['AGENT']['PREFIX_1C'].'-'.$id_in;
						
						if ((strlen($number)) && ($number_success == 1))
						//if (strlen($number))
						{		
							$el = new CIBlockElement;
							$arLoadProductArray = Array(
								"MODIFIED_BY" => $USER->GetID(), 
								"IBLOCK_SECTION_ID" => false,
								"IBLOCK_ID" => 82,
								"PROPERTY_VALUES" => array(
									511 => NewQuotes($_POST['NAME_SENDER']),
									512 => NewQuotes($_POST['PHONE_SENDER']),
									513 => NewQuotes($_POST['COMPANY_SENDER']),
									514 => $CITY_SENDER,
									515 => deleteTabs($_POST['INDEX_SENDER']),
									516 => NewQuotes($_POST['ADRESS_SENDER']),
									522 => NewQuotes($_POST['NAME_RECIPIENT']),
									523 => NewQuotes($_POST['PHONE_RECIPIENT']),
									524 => NewQuotes($_POST['COMPANY_RECIPIENT']),
									525 => $city_recipient,
									526 => deleteTabs($_POST['INDEX_RECIPIENT']),
									527 => NewQuotes($_POST['ADRESS_RECIPIENT']),
									528 => deleteTabs($_POST['DATE_TAKE']),
									529 => deleteTabs($_POST['TIME_TAKE_FROM']),
									535 => deleteTabs($_POST['TIME_TAKE_TO']),
									530 => $_POST['TYPE'],
									585 => $_POST['TYPE_CASH'],
									531 => $places,
									532 => $weight,
									517 => $size_1,
									536 => $size_2,
									537 => $size_3,
									518 => $state,
									519 => $number,
									582 => intval($id_in),
									533 => NewQuotes($_POST['instructions']),
									538 => $agent_id,
									583 => $arFilesAdd,
									626 => deleteTabs($_POST['number_in']),
									669 => $arResult['USER_BRANCH'],
									687 => $_POST['TYPE_DELIVERY'],
									688 => $_POST['DELIVERY_PAYER'],
									689 => $_POST['DELIVERY_CONDITION'],
									690 => floatval(str_replace(',','.',$_POST['PAYMENT_AMOUNT'])),
									695 => floatval(str_replace(',','.',$_POST['COST']))
								),
								"NAME" => $number,
								"ACTIVE" => "Y"
							);
							
							
							if ($z_id = $el->Add($arLoadProductArray))
							{
                                $arLog = array(
                                    'Type' => 'Новая заявка',
                                    'ID' => $z_id,
                                    'Number' => $number
                                );
                                AddToLogs('requests',$arLog);
								
								CIBlockElement::SetPropertyValuesEx($agent_id, 40, array(678 => date('d.m.Y H:i:s')));
								$_SESSION['MESSAGE'][] = "Заявка №".$number." успешно создана";
								
								if (strlen(trim($_POST['COMPANY_RECIPIENT'])))
								{
									$res = CIBlockElement::GetList(
										array("ID" =>"desc"), 
										array(
											"IBLOCK_ID" => 84, 
											"PROPERTY_CREATOR" => $agent_id, 
											"NAME" => NewQuotes($_POST['COMPANY_RECIPIENT']), 
											"PROPERTY_CITY" => $city_recipient, 
											"PROPERTY_ADRESS" => NewQuotes($_POST['ADRESS_RECIPIENT']),
											"PROPERTY_TYPE" => 260
										),
										false, 
										array("nTopCount" => 1), 
										array("ID")
									);
									
									if (!$ob = $res->GetNextElement())
									{
										$el2 = new CIBlockElement;
										$arLoadProductArray2 = Array(
											"MODIFIED_BY" => $USER->GetID(), 
											"IBLOCK_SECTION_ID" => false,
											"IBLOCK_ID" => 84,
											"PROPERTY_VALUES" => array(
												579 => $agent_id,
												574 => NewQuotes($_POST['NAME_RECIPIENT']),
												575 => NewQuotes($_POST['PHONE_RECIPIENT']),
												576 => $city_recipient,
												577 => $_POST['INDEX_RECIPIENT'],
												578 => NewQuotes($_POST['ADRESS_RECIPIENT']),
												580 => 260,
                                                713 => date('d.m.Y H:i:s')
											),
											"NAME" => NewQuotes($_POST['COMPANY_RECIPIENT']),
											"ACTIVE" => "Y"
										);
										$rec_id = $el2->Add($arLoadProductArray2);
									}
                                    else
                                    {
                                        $arFields = $ob->GetFields();
                                        CIBlockElement::SetPropertyValuesEx($arFields['ID'], 84, array(713 => date('d.m.Y H:i:s')));
                                    }
								}
								
								if (strlen(trim($_POST['COMPANY_SENDER'])))
								{
									$res = CIBlockElement::GetList(
										array("ID" =>"desc"), 
										array(
											"IBLOCK_ID" => 84, 
											"PROPERTY_CREATOR" => $agent_id, 
											"NAME" => NewQuotes($_POST['COMPANY_SENDER']), 
											"PROPERTY_CITY" => $CITY_SENDER, 
											"PROPERTY_ADRESS" => NewQuotes($_POST['ADRESS_SENDER']),
											"PROPERTY_TYPE" => 259
										),
										false, 
										array("nTopCount" => 1), 
										array("ID")
									);
									
									if (!$ob = $res->GetNextElement())
									{
										$el2 = new CIBlockElement;
										$arLoadProductArray2 = Array(
											"MODIFIED_BY" => $USER->GetID(), 
											"IBLOCK_SECTION_ID" => false,
											"IBLOCK_ID" => 84,
											"PROPERTY_VALUES" => array(
												579 => $agent_id,
												574 => NewQuotes($_POST['NAME_SENDER']),
												575 => NewQuotes($_POST['PHONE_SENDER']),
												576 => $CITY_SENDER,
												577 => $_POST['INDEX_SENDER'],
												578 => NewQuotes($_POST['ADRESS_SENDER']),
												580 => 259,
                                                713 => date('d.m.Y H:i:s')
											),
											"NAME" => NewQuotes($_POST['COMPANY_SENDER']),
											"ACTIVE" => "Y"
										);
										$rec_id = $el2->Add($arLoadProductArray2);
									}
                                    else
                                    {
                                        $arFields = $ob->GetFields();
                                        CIBlockElement::SetPropertyValuesEx($arFields['ID'], 84, array(713 => date('d.m.Y H:i:s')));
                                    }
								}
								
								
								LocalRedirect($arParams['LINK']."index.php");
							}
							else
							{
								$arResult['ERRORS'][] = $el->LAST_ERROR;
							}
						}
						else
						{
							//$arResult['ERRORS'][] = 'Невозможно получить номер заявки, обратитесь в <a href="/support/">тех. поддержку</a>. '.$number;
							$arResult['ERRORS'][] = 'Невозможно получить номер заявки, обратитесь в <a href="/support/">тех. поддержку</a>.';
						}
					}
				}
			}
			$arSettings = array();
			$settingsJson = $arResult['AGENT']['PROPERTY_ACCOUNT_LK_SETTINGS_VALUE']['TEXT'];
			$arSettings = array();
			if (strlen($settingsJson))
			{
				$arSettings = json_decode(htmlspecialcharsBack($settingsJson), true);
			}
			$arResult['USER_SETTINGS'] = $arSettings[$arResult["USER_ID"]];
			$arResult['DEAULTS'] = array(
			//	'NAME_SENDER' => $USER->GetFullName(),
			//	'PHONE_SENDER' => $arUser['PERSONAL_PHONE'],
				'PLACES' => 1,
				'TYPE' => (intval($arResult['USER_SETTINGS']['TYPE']) > 0) ? intval($arResult['USER_SETTINGS']['TYPE']) : '',
				'DELIVERY_PAYER' => (intval($arResult['USER_SETTINGS']['DELIVERY_PAYER']) > 0) ? intval($arResult['USER_SETTINGS']['DELIVERY_PAYER']) : 293,
				'TYPE_DELIVERY' => (intval($arResult['USER_SETTINGS']['AGENT_TYPE_DELIVERY']) > 0) ? intval($arResult['USER_SETTINGS']['AGENT_TYPE_DELIVERY']) : 290,
				'TYPE_CASH' => (intval($arResult['USER_SETTINGS']['TYPE_CASH']) > 0) ? intval($arResult['USER_SETTINGS']['TYPE_CASH']) : 265,
				'DELIVERY_CONDITION' => (intval($arResult['USER_SETTINGS']['DELIVERY_CONDITION']) > 0) ? intval($arResult['USER_SETTINGS']['DELIVERY_CONDITION']) : 295
			);
			$arResult['SENDERS'] = GetListContractors($agent_id, 259, false);
			if ((count($arResult['SENDERS']) == 0) && ($arParams['RESTRICTION_SENDERS'] != 'Y'))
			{
				$arResult['OPEN'] = false;
				$arResult["ERRORS"][] = GetMessage('ERR_NO_SENDERS');
			}
			$arResult['TITLE'] = GetMessage("TITLE_MODE_ADD");
			$APPLICATION->SetTitle(GetMessage("TITLE_MODE_ADD"));
		}
		
if ($mode == '1c')
{
    /*
    $logs = $_SERVER['DOCUMENT_ROOT'].'/logs/log.txt';
    if (is_file($logs))
    {
        $errors_file = fopen($logs,'a');
        fwrite($errors_file,date('d.m.Y H:i:s')."\n");
        if (count($_POST) > 0)
        {
            foreach ($_POST as $k => $v)
            {
                if (is_array($v))
                {
                    foreach ($v as $kk => $vv)
                    {
                        fwrite($errors_file,$k.'['.$kk.'] => '.$vv."\n");
                    }
                }
                else
                {
                    fwrite($errors_file,$k.' => '.$v."\n");
                }
            }
        }
        else
        {
            fwrite($errors_file,'no POST'."\n");
        }
        fwrite($errors_file,"\n");
        fclose($errors_file);
    }
    */
    $arLogs = array();
    foreach ($_POST as $k => $v)
    {
        if (is_array($v))
        {
            foreach ($v as $kk => $vv)
            {
                $arLogs['POST '.$k.' '.$kk] = $vv;
            }
        }
        else
        {
            $arLogs['POST '.$k] = $v;
        }
    }
    $login1c = GetSettingValue(705);
    $pass1c = GetSettingValue(706);
    if ((strlen($_POST['login'])) && (strlen($_POST['pass'])))
    {
        if (($_POST['login'] == $login1c) && ($_POST['pass'] == $pass1c))
        {
            $arRes = array();
            $json_string = $_POST['Response'];
            $obj = json_decode($json_string, true);
            foreach ($obj as $k => $v)
            {
                $k_tr = iconv('utf-8', 'windows-1251', $k);
                $v_tr = iconv('utf-8', 'windows-1251', $v);
                $arRes[$k_tr] = $v_tr;
            }
            if ($_POST['type'] == 'newcomment')
            {

                $creator = GetIDAgentByINN(trim($arRes['creator']));
                $arForWhoInn = explode(',',$arRes['inn']);
                $arForWho = array();
                foreach ($arForWhoInn as $inn)
                {
                    if (trim($inn) == trim($arRes['creator']))
                        continue;
                    $agentIdTo = GetIDAgentByINN(trim($inn));
                    if ($agentIdTo)
                    {
                        $arForWho[] = $agentIdTo;
                    }
                }
                $user_from = false;
                if ($creator)
                {
                    $rsUser = CUser::GetList(($by="id"), ($order="asc"),array("GROUPS_ID" => array(4,16), "UF_COMPANY_RU_POST" => $creator), array("SELECT" => array("UF_BRANCH","UF_ROLE")));
                    if($arUser = $rsUser->Fetch())
                    {
                        $user_from = $arUser['ID'];
                    }
                }
                CModule::IncludeModule('im');
                foreach ($arForWho as $forwho)
                {
                    $el = new CIBlockElement;
                    $mess_id = $el->Add(
                        array(
                            "IBLOCK_SECTION_ID" => false,
                            "IBLOCK_ID" => 92,
                            "PROPERTY_VALUES"=> array(
                              699 => $creator,
                              700 => $forwho,
                              701 => 302,
                              702 => trim($arRes['number'])
                            ),
                            "NAME" => "Новый комментарий по накладной ".$arRes['number'],
                            "DETAIL_TEXT" => $arRes["newcomment"],
                            "ACTIVE" => "Y"
                      ));
                    $users_to = array();
                    $rsUser = CUser::GetList(($by="id"), ($order="asc"),array("GROUPS_ID" => array(4,16), "UF_COMPANY_RU_POST" => $forwho), array("SELECT" => array("UF_BRANCH","UF_ROLE")));
                    while($arUser = $rsUser->Fetch())
                    {
                        $users_to[] = $arUser['ID'];
                    }
                    foreach ($users_to as $user_to)
                    {
                        $arMessageFields = array(
                          "TO_USER_ID" => $user_to,
                          "FROM_USER_ID" => $user_from,
                          "NOTIFY_MODULE" => "im",
                          "NOTIFY_TYPE" => ($user_from) ? IM_NOTIFY_FROM : IM_NOTIFY_SYSTEM,
                          "NOTIFY_MESSAGE" => "Новый комментарий по накладной ".$arRes['number'].": ".$arRes["newcomment"].'. [url=/messages/?number='.$arRes['number'].'&ids='.$mess_id.']Перейти к диалогу по накладной[/url]'
                        );
                       CIMNotify::Add($arMessageFields);
                    }
                }

            }
            else
            {
                if (intval($arRes['ID']) > 0)
                {
                    $res = CIBlockElement::GetByID(intval($arRes['ID']));
                    if ($ar_res = $res->GetNext())
                    {
                        //NOTE Заявки
                        if (intval($ar_res['IBLOCK_ID']) == 82)
                        {
                            if ($_POST['type'] == 'accepted')
                            {
                                if (strlen(trim($arRes['Number'])))
                                {
                                    $date_accepted = '';
                                    $db_props = CIBlockElement::GetProperty(82, intval($arRes['ID']), array("sort" => "asc"), array("ID"=>584));
                                    if ($ar_props = $db_props->Fetch())
                                    {
                                        $date_accepted = trim($ar_props["VALUE"]);
                                    }
                                    if (strlen($date_accepted))
                                    {
                                        $arResult["ERRORS"][] = 'Повторное принятие заявки '.trim($arRes['Number']);
                                    }
                                    else
                                    {
                                        $el = new CIBlockElement;
										$res_2 = $el->Update(intval($arRes['ID']),array("ACTIVE" => "N","NAME" => trim($arRes['Number'])));
										CIBlockElement::SetPropertyValuesEx(intval($arRes['ID']), false, array(
											518 => 238,
											584 => date('d.m.Y H:i:s'),
											539 => $arRes['Reason']
										));
                                        $arResult["INFO"][] = 'Заявка '.trim($arRes['Number']).' ['.intval($arRes['ID']).'] принята';
                                    }
                                }
                                else
                                {
                                    $arResult["ERRORS"][] = 'Пустой Номер Заявки';
                                }
                            }
                            elseif ($_POST['type'] == 'rejected')
                            {
                                CIBlockElement::SetPropertyValuesEx(intval($arRes['ID']), 82, array(518 => 240, 539 => $arRes['Reason']));
                                $arResult["INFO"][] = 'Заявка '.trim($arRes['Number']).' ['.intval($arRes['ID']).'] отклонена на причине '.$arRes['Reason'];
                            }
                            else
                            {
                                $arResult["ERRORS"][] = 'Неизвестный тип';
                            }
                        }
                        //NOTE Накладные
                        elseif (intval($ar_res['IBLOCK_ID']) == 83)
                        {
                            if ($_POST['type'] == 'accepted')
                            {
                                $id_state = 0;
                                $db_props = CIBlockElement::GetProperty(83, intval($arRes['ID']), array("sort" => "asc"), array("ID"=>572));
                                if ($ar_props = $db_props->Fetch())
                                {
                                    $id_state = intval($ar_props["VALUE"]);
                                }
                                if ($id_state == 257)
                                {
                                    CIBlockElement::SetPropertyValuesEx(intval($arRes['ID']), 83, array(572 => 258, 573 => date('d.m.Y H:i:s')));
                                    $arResult["INFO"][] = 'Накладная '.$ar_res['NAME'].' принята';
                                }
                                else
                                {
                                    $arResult["ERRORS"][] = 'Неверный статус накладной '.$ar_res['NAME'];
                                }
                            }
                            else
                            {
                                $arResult["ERRORS"][] = 'Неизвестный тип';
                            }
                        }
                        else
                        {
                            $arResult["ERRORS"][] = 'Неизвестная структура БД';
                        }
                    }
                    else
                    {
                        $arResult["ERRORS"][] = 'Заявка или накладная с таким ID не найдена';
                    }
                }
                else
                {
                    $arResult["ERRORS"][] = 'Неверный формат поля ID';
                }
            }
        }
        else
        {
            $arResult["ERRORS"][] = 'Ошибка авторизации';
        }
    }
    else
    {
        $arResult["ERRORS"][] = 'Отсутствует логин или пароль';
    }
    foreach ($arResult["ERRORS"] as $k=> $err)
    {
        $arLogs['ERROR '.$k] = $err;
    }
    foreach ($arResult["INFO"] as $k=> $inf)
    {
        $arLogs['INFO '.$k] = $inf;
    }
    if ($_POST['type'] == 'newcomment')
    {
        AddToLogs('NewComments',$arLogs);
    }
    else
    {
        AddToLogs('Accepted',$arLogs);
    }
}
		
if (($mode == 'invoice1c') || ($mode == 'invoice1c_modal'))
{
    if (strlen(trim($_GET['f001'])))
    {
        $arParamsJson = array(
            'NumDoc' => trim($_GET['f001']),
        );
        $result_0 = $client->GetDocInfo($arParamsJson);
        $mResult_0 = $result_0->return;
        $obj_0 = json_decode($mResult_0, true);

        $arResult['REQUEST'] = false;
        $arResult['TITLE'] = 'Заявка не найдена';
        $APPLICATION->SetTitle($arResult['TITLE']);
        if ((is_array($obj_0)) && (count($obj_0) > 0))
        {
            $arResult['REQUEST'] = arFromUtfToWin($obj_0);

            //получение сообщений//
            /*
            $result = $client->GetDocComments(array('NUMDOC' => iconv('windows-1251','utf-8', trim($arResult['REQUEST']['НомерНакладной'])), 'NUMREQUEST' => iconv('windows-1251','utf-8', $arResult['REQUEST']['НомерЗаявки'])));
            $mResult = $result->return;
            $obj = json_decode($mResult, true);
            $arResult['REQUEST']['Messages'] = false;
            if (is_array($obj[iconv('windows-1251','utf-8','Сообщения')]))
            {
                $arResult['REQUEST']['Messages'] = $obj[iconv('windows-1251','utf-8','Сообщения')];
            }
            */
            //получение сообщений//
			$arResult['REQUEST']['ВесОтправления'] = 0;
			$arResult['REQUEST']['ВесОтправленияОбъемный'] = 0;
			$arResult['REQUEST']['КоличествоМест'] = 0;
			foreach ($arResult['REQUEST']['Габариты'] as $d)
			{
				$arResult['REQUEST']['ВесОтправления'] = $arResult['REQUEST']['ВесОтправления'] + $d['ВесОтправления'];
				$arResult['REQUEST']['ВесОтправленияОбъемный'] = $arResult['REQUEST']['ВесОтправленияОбъемный'] + $d['ВесОтправленияОбъемный'];
				$arResult['REQUEST']['КоличествоМест'] = $arResult['REQUEST']['КоличествоМест'] + $d['КоличествоМест'];
			}
            $arResult['TITLE'] = 'Номер заявки: '.$arResult['REQUEST']['НомерЗаявки'];
            $arResult['TITLE_2'] = 'Номер накладной: '.$arResult['REQUEST']['НомерНакладной'];
            $APPLICATION->SetTitle($arResult['REQUEST']['НомерНакладной'].' ('.$arResult['REQUEST']['НомерЗаявки'].')');
        }
    }
}

if ($mode == 'list_xls')
{
    if (strlen($_POST['DATA']))
    {
        $arData = json_decode(htmlspecialchars_decode($_POST['DATA'],ENT_COMPAT), true);
        include_once $_SERVER['DOCUMENT_ROOT'].'bitrix/_black_mist/PhpExcel/Classes/PHPExcel.php';
        $pExcel = new PHPExcel();
        $pExcel->setActiveSheetIndex(0);
        $aSheet = $pExcel->getActiveSheet();
        $pExcel->getDefaultStyle()->getFont()->setName('Arial');
        $pExcel->getDefaultStyle()->getFont()->setSize(10);
        $Q = iconv("windows-1251", "utf-8", 'Заявки');
        $aSheet->setTitle($Q);
        $head_style = array(
            'font' => array(
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
        $i = 1;
        $arJ = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O');
        foreach  ($arData as $k)
        {
            $n = 0;
            foreach ($k as $v)
            {
                $num_sel = $arJ[$n].$i;
                $aSheet->setCellValue($num_sel,$v);
                $n++;
            }
            $i++;
        }
        $i--;
        foreach ($arJ as $cc)
        {
            $aSheet->getColumnDimension($cc)->setWidth(17);
        }
        $aSheet->getStyle('A1:O1')->applyFromArray($head_style);
        $aSheet->getStyle('A1:O'.$i)->getAlignment()->setWrapText(true);
        $aSheet->getStyle('A1:O'.$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
        include_once $_SERVER['DOCUMENT_ROOT'].'bitrix/_black_mist/PhpExcel/Classes/PHPExcel/Writer/Excel5.php';
        $objWriter = new PHPExcel_Writer_Excel5($pExcel);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.date('Заявки d.m.Y').'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
    }
}



$arResult['times'][] = array('name' => 'Полное выполнение скрипта', 'val' => microtime(true) - $start);
$this->IncludeComponentTemplate($mode);