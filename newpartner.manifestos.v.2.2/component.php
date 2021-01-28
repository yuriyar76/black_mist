<?

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

ini_set("soap.wsdl_cache_enabled", "0" );
ini_set("default_socket_timeout", "300");
set_include_path($_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/PhpExcel/Classes/');
include_once ($_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/PhpExcel/Classes/PHPExcel.php');
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");
$modes = array(
	'list',
	'upload',
	'upload_settings'
);
$arResult['MODE'] = $modes[0];
if ((strlen($arParams["MODE"])) && (in_array($arParams["MODE"], $modes)))
{
    $arResult['MODE'] = $arParams["MODE"];
}
else
{
    if ((strlen(trim($_GET["mode"]))) && (in_array(trim($_GET["mode"]), $modes)))
    {
        $arResult['MODE'] = trim($_GET["mode"]);
    }
}

$arResult['ADMIN_AGENT'] = false;
$arResult['SITUATION'] = array();
$arResult['SITUATION_TEXT'] = '';
$arResult['CELLS'] = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P',"Q","R","S","T",'U','V','W','X','Y','Z');
$arResult['AVAILABLE_FIELDS'] = array(
	'' => '---Пустое---',
	'PROPERTY_ADRESS_SENDER' => 'Адрес отправителя',
	'PROPERTY_ADRESS_RECIPIENT' => 'Адрес получателя',
	'PROPERTY_WEIGHT' => 'Вес отправления',
	'PROPERTY_VWEIGHT' => 'Вес отправления объемный',
	'PROPERTY_HEIGHT' => 'Высота отправления',
	'PROPERTY_CITY_SENDER' => 'Город отправителя',
	'PROPERTY_CITY_RECIPIENT' => 'Город получателя',
	'PROPERTY_LENGTH' => 'Длина отправления',
	'PROPERTY_IN_DATE_DELIVERY' => 'Доставить в дату',
	'PROPERTY_IN_DATETIME_DELIVERY' => 'Доставить в дату до часа',
	'PROPERTY_IN_TIME_DELIVERY' => 'Доставить до часа',
	'PROPERTY_WHO_DELIVERY' => 'Доставить как',
	'PROPERTY_INDEX_SENDER' => 'Индекс отправителя',
	'PROPERTY_INDEX_RECIPIENT' => 'Индекс получателя',
	'PROPERTY_PLACES' => 'Количество мест',
	'PROPERTY_COMPANY_SENDER' => 'Компания отправителя',
	'PROPERTY_COMPANY_RECIPIENT' => 'Компания получателя',
	'PROPERTY_TYPE_PAYS' => 'Кто оплачивает',
	'PROPERTY_PAYS' => 'Кто оплачивает, расшифровка',
	'NAME' => 'Номер накладной',
	'PROPERTY_COST' => 'Объявленная стоимость',
	'PROPERTY_DESCRIPTION' => 'Описание отправления',
	'PROPERTY_INSTRUCTIONS' => 'Специальные инструкции',
	'PROPERTY_FOR_PAYMENT' => 'Сумма к оплате',
	'PROPERTY_PAYMENT_COD' => 'Сумма наложенного платежа',
	'PROPERTY_PHONE_SENDER' => 'Телефон отправителя',
	'PROPERTY_PHONE_RECIPIENT' => 'Телефон получателя',
	'PROPERTY_TYPE_DELIVERY' => 'Тип доставки',
	'PROPERTY_PAYMENT' => 'Тип оплаты',
	'PROPERTY_TYPE_PACK' => 'Тип отправления',
	'PROPERTY_NAME_SENDER' => 'Фамилия отправителя',
	'PROPERTY_NAME_RECIPIENT' => 'Фамилия получателя',
	'PROPERTY_WIDTH' => 'Ширина отправления',
);
$arResult['DATES_FORMAT'] = array(
	'd.m.Y' => 'ДД.ММ.ГГГГ'
);
$arResult['TIMES_FORMAT'] = array(
	'H:i' => 'чч:мм'
);
$arResult['DATETIMES_FORMAT'] = array(
	'd.m.Y H:i' => 'ДД.ММ.ГГГГ чч:мм'
);
$arResult['TYPE_DELIVERY_VALUES'] = array(
	243 => 'Экспресс',
	244 => 'Стандарт',
	245 => 'Эконом',
	308 => 'Склад-Склад',
	338 => 'Экспресс 8'
);
$arResult['TYPE_PACK_VALUES'] = array(
	246 => 'Документы',
	247 => 'Не документы'
);
$arResult['WHO_DELIVERY_VALUES'] = array(
	248 => 'По адресу',
	249 => 'До востребования',
	250 => 'Лично в руки'
);
$arResult['TYPE_PAYS_VALUES'] = array(
	251 => 'Отправитель',
	252 => 'Получатель',
	253 => 'Другой',
	254 => 'Служебное'
);
$arResult['PAYMENT_VALUES'] = array(
	255 => 'Наличными',
	256 => 'По счету',
	309 => 'Банковской картой'
);
$arResult['WEIGHT_INDEX'] = 1;
$arResult['LENGTH_INDEX'] = 1;

$rsUser = CUser::GetByID($USER->GetID());
$arUser = $rsUser->Fetch();
$arResult["USER_ID"] = $arUser["ID"];
$agent_id = (int)$arUser["UF_COMPANY_RU_POST"];
$sets_json = $arUser["UF_UPLOAD_M_SETTINGS"];
$arResult['EMAIL_SEND_MANIFEST'] = false;
$arResult['CURRENT_DATE'] = date('d.m.Y H:i:00');
if (strlen($arUser['TIME_ZONE']))
{
	$timestamp = time();
	$dt = new DateTime("now", new DateTimeZone($arUser['TIME_ZONE']));
	$dt->setTimestamp($timestamp);
	$arResult['CURRENT_DATE'] = $dt->format('d.m.Y H:i:00');
}

if ($agent_id > 0)
{
	$arResult['AGENT'] = GetCompany($agent_id);
	if (in_array($arResult['AGENT']["PROPERTY_TYPE_ENUM_ID"], array(51, 53)))
	{
		if ($arResult['AGENT']["PROPERTY_TYPE_ENUM_ID"] == 51)
		{
			$arResult['ADMIN_AGENT'] = true;
			$arResult['UK'] = $arResult['AGENT']["ID"];
		}
		else
		{
			$arResult['UK'] = $arResult['AGENT']["PROPERTY_UK_VALUE"];
			$arResult['AGENT_INFO'] = $arResult['AGENT'];
		}
		if ((int)$arResult['UK'] > 0)
		{
			$currentip = GetSettingValue(683, false, $arResult['UK']);
			$currentport = (int)GetSettingValue(761, false, $arResult["UK"]);
			$currentlink = GetSettingValue(704, false, $arResult['UK']);
			$login1c = GetSettingValue(705, false, $arResult['UK']);
			$pass1c = GetSettingValue(706, false, $arResult['UK']);
			if ((strlen(trim($currentip))) && (strlen(trim($currentlink))) && (strlen(trim($login1c))) && (strlen(trim($pass1c))))
			{
				if ($currentport > 0) {
					$url = "http://".$currentip.':'.$currentport.$currentlink;
				}
				else {
					$url = "http://".$currentip.$currentlink;
				}
				$curl = curl_init();
				curl_setopt_array($curl, array(    
				CURLOPT_URL => $url,
				CURLOPT_HEADER => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_NOBODY => true,
				CURLOPT_TIMEOUT => 10));
				$header = explode("\n", curl_exec($curl));
				curl_close($curl);
				if (strlen(trim($header[0])))
				{
					if ($currentport > 0) {
						$client = new SoapClient($url, array('login' => $login1c, 'password' => $pass1c, 'proxy_host' => $currentip, 'proxy_port' => $currentport, 'exceptions' => false));
					}
					else {
						$client = new SoapClient($url, array('login' => $login1c, 'password' => $pass1c,'exceptions' => false));
					}

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
					$res_4 = CIBlockElement::GetList(
						array('NAME' => 'ASC'), 
						array('IBLOCK_ID' => 19, 'ACTIVE' => 'Y'), 
						false, 
						false, 
						array('ID', 'NAME')
					);
					while ($ob_4 = $res_4->GetNextElement())
					{
						$a = $ob_4->GetFields();
						$arResult['SITUATION'][$a['ID']] = $a['NAME'];
						$arResult['SITUATION_TEXT'] .= '<option value="'.$a['ID'].'">'.$a['NAME'].'</option>';
					}
					$emailSendManifest = GetSettingValue(745, false, $arResult['UK']);
					if (strlen(trim($emailSendManifest)))
					{
						$arResult['EMAIL_SEND_MANIFEST'] = trim($emailSendManifest);
					}
				}
				else
				{
					$arResult['MODE'] = 'close';
				}
			}
			else
			{
				$arResult['MODE'] = 'close';
			}
		}
		else
		{
			$arResult['MODE'] = 'close';
		}
	}
	else
	{
		$arResult['MODE'] = 'close';
	}
}
else
{
	$arResult['MODE'] = 'close';
}
if ($arResult['MODE'] != 'close')
{
	if ($arResult['MODE'] == 'list')
	{
		$arResult['LIST_TO_DATE'] = date('d.m.Y');
		$prevdate = strtotime('-10 days');
		$arResult['LIST_FROM_DATE'] = date('d.m.Y',$prevdate);
		$arResult['LIST_FROM_DATE_FOR_1C'] = date('Y-m-d',$prevdate);
		$arResult['LIST_TO_DATE_FOR_1C'] = date('Y-m-d');
		$arResult['CURRENT_TYPE_M'] = 'A';
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
		
		if (strlen($_SESSION['LIST_TO_DATE']))
		{
			$arResult['LIST_TO_DATE'] = $_SESSION['LIST_TO_DATE'];
		}
		if (strlen($_SESSION['LIST_FROM_DATE']))
		{
			$arResult['LIST_FROM_DATE'] = $_SESSION['LIST_FROM_DATE'];
		}
		if (strlen($_SESSION['LIST_TO_DATE_FOR_1C']))
		{
			$arResult['LIST_TO_DATE_FOR_1C'] = $_SESSION['LIST_TO_DATE_FOR_1C'];
		}
		if (strlen($_SESSION['LIST_FROM_DATE_FOR_1C']))
		{
			$arResult['LIST_FROM_DATE_FOR_1C'] = $_SESSION['LIST_FROM_DATE_FOR_1C'];
		}
		if (strlen($_SESSION['CURRENT_TYPE_M']))
		{
			$arResult['CURRENT_TYPE_M'] = $_SESSION['CURRENT_TYPE_M'];
		}
		if ($_GET['ChangePeriod'] == 'Y')
		{
			if ((strlen(trim($_GET['datefrom'])) > 0) && (strlen(trim($_GET['dateto']))))
			{
				$arPostDateFrom = date_parse_from_format("d.m.Y", trim($_GET['datefrom']));
				$arPostDateTo = date_parse_from_format("d.m.Y", trim($_GET['dateto']));
				$currentdate = strtotime(date('Y-m-d'));
				$timePostDateTo = strtotime($arPostDateTo['year'].'-'.str_pad($arPostDateTo['month'],2,'0',STR_PAD_LEFT).'-'.str_pad($arPostDateTo['day'],2,'0',STR_PAD_LEFT));
				$timePostDateFrom = strtotime($arPostDateFrom['year'].'-'.str_pad($arPostDateFrom['month'],2,'0',STR_PAD_LEFT).'-'.str_pad($arPostDateFrom['day'],2,'0',STR_PAD_LEFT));
				if ($timePostDateFrom > $timePostDateTo)
				{
					$vremVar = $timePostDateTo;
					$timePostDateTo = $timePostDateFrom;
					$timePostDateFrom = $vremVar;
					$timeFromToRazn = $timePostDateTo - $timePostDateFrom;
				}
				if ($timePostDateTo > $currentdate)
				{
					$timePostDateTo = $currentdate;
				}
				if ($timePostDateFrom > $timePostDateTo)
				{
					$timePostDateFrom = strtotime('-10 days',$timePostDateTo);
				}
				$timeFromToRazn = $timePostDateTo - $timePostDateFrom;
				if (($timeFromToRazn/86400) > 90)
				{
					$timePostDateFrom = strtotime('-3 month',$timePostDateTo);
				}
				$arResult['LIST_FROM_DATE'] = date('d.m.Y',$timePostDateFrom);
				$_SESSION['LIST_FROM_DATE'] = date('d.m.Y',$timePostDateFrom);
				$arResult['LIST_TO_DATE'] = date('d.m.Y',$timePostDateTo);
				$_SESSION['LIST_TO_DATE'] = date('d.m.Y',$timePostDateTo);
				$arResult['LIST_FROM_DATE_FOR_1C'] = date('Y-m-d',$timePostDateFrom);
				$_SESSION['LIST_FROM_DATE_FOR_1C'] = date('Y-m-d',$timePostDateFrom);
				$arResult['LIST_TO_DATE_FOR_1C'] = date('Y-m-d',$timePostDateTo);
				$_SESSION['LIST_TO_DATE_FOR_1C'] = date('Y-m-d',$timePostDateTo);	
			}
		}
		if ($_GET['ChangeTypeM'] == 'Y')
		{
			$_SESSION['CURRENT_TYPE_M'] = $_GET['typem'];
			$arResult['CURRENT_TYPE_M'] = $_GET['typem'];
		}
		
		$arResult['LIST_OF_AGENTS'] = false;
		if ($arResult['ADMIN_AGENT'])
		{
			$arResult['LIST_OF_AGENTS'] = AvailableAgents(false, $agent_id);
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
				else
				{
					$_SESSION['CURRENT_AGENT'] = 0;
					$arResult['CURRENT_AGENT'] = 0;
					$_SESSION['CURRENT_INN'] = false;
					$arResult['CURRENT_INN'] = false;
				}
			}
			if ((int)$arResult['CURRENT_AGENT'] > 0)
			{
				$arResult['AGENT_INFO'] = GetCompany($arResult['CURRENT_AGENT']);
			}
		}
		
		$arResult['MANIFESTOS'] = array();
		$arResult['MANIFESTOS_DATES'] = array();
		$arResult['debits'] = array();
		
		if ($arParams["METHOD_LOAD"] == "TYPE1")
		{
			if ($arResult['CURRENT_INN'])
			{
				$arParamsJson = array(
					"INN" => $arResult['CURRENT_INN'],
					'StartDate' => $arResult['LIST_FROM_DATE_FOR_1C'],
                	'EndDate' => $arResult['LIST_TO_DATE_FOR_1C'],
					"TypeManifest" => $arResult['CURRENT_TYPE_M']
				);
				$result = $client->GetManifest($arParamsJson);
				/*
				if ($USER->GetID() == 102)
				{
					echo '<pre>';
					print_r($arParamsJson);
					print_r($result);
					echo '</pre>';
				}
				*/
				$mResult = $result->return;
				$obj = json_decode($mResult, true);
				if ($obj)
				{
					$i = 0;
					foreach ($obj as $m)
					{
						$count_nakls = count($m["DeliveryNotes"]);
						$count_deliverd = 0;
						// $arDelivered = array();
						$numbers = array();
						$events = array();
						
						foreach ($m['DeliveryNotes'] as $nakl)
						{
							$numbernakl = iconv("utf-8", "windows-1251", $nakl["DeliveryNote"]);
							$numbers[$numbernakl] = "N";
							$events[$numbernakl] = array();
							foreach ($nakl['DeliveryEvents'] as $ev)
							{
								$event = iconv("utf-8", "windows-1251", $ev["Event"]);
								$desc = strlen(iconv("utf-8", "windows-1251", $ev["EventExt"])) ? iconv("utf-8", "windows-1251", $ev["EventExt"]) : iconv("utf-8", "windows-1251", $ev["EventErr"]);
								$events[$numbernakl][] = array(
									"date" => iconv("utf-8", "windows-1251", $ev["EventDate"]),
									"event" => $event,
									"desc" => $desc
								);
								if ($event == "Доставлено")
								{
									$numbers[$numbernakl] = "Y";
									$count_deliverd++;
								}
								if (($event == "Оприходовано складом") && ($desc == $arResult['AGENT_INFO']['PROPERTY_CITY_NAME']))
								{
									$arResult['debits'][$numbernakl] = "Y";

								}
							}
						}
						$to_debit = 0;
						foreach ($numbers as $number => $dost)
						{
							if (($dost == 'N') && ($arResult['debits'][$number] != 'Y'))
							{
								$to_debit++;
							}
						}
						$a = array(
							"ID" => $i,
							"PROPERTY_DATEDOC_VALUE" => transformDateFrom1c(iconv("utf-8", "windows-1251", $m["DateDoc"])),
							"PROPERTY_NUMBER_VALUE" => iconv("utf-8", "windows-1251", $m["Number"]),
							"DELIVERED" => ($count_deliverd == $count_nakls) ? "Y" : "N",
							"COUNT" => $count_nakls,
							"COUNT_DELIVERED" => $count_deliverd,
							"PROPERTY_DEPARTUREDATE_VALUE" => transformDateFrom1c(iconv("utf-8", "windows-1251", $m["DepartureDate"])),
							"PROPERTY_CALCULATEDDATE_VALUE" => transformDateFrom1c(iconv("utf-8", "windows-1251", $m["CalculatedDate"])),
							"PROPERTY_WEIGHT_VALUE" => $m["Weight"],
							"PROPERTY_VOLUMEWEIGHT_VALUE" => $m["VolumeWeight"],
							"PROPERTY_PLACES_VALUE" => $m["Places"],
							"NUMBERS" => $numbers,
							"PROPERTY_CITY_NAME" => GetFullNameOfCity($m["City"], true),
							"PROPERTY_AGENT_NAME" => (iconv("utf-8", "windows-1251", $m['TypeManifest']) == "Входящий манифест") ? iconv("utf-8", "windows-1251", $m["Organization"]) : GetIDAgentByINN($m["Partner"],false, true, true),
							"PROPERTY_ORGANIZATION_VALUE" => (iconv("utf-8", "windows-1251", $m['TypeManifest']) == "Входящий манифест") ? GetIDAgentByINN($m["Partner"],false, true, true) : iconv("utf-8", "windows-1251", $m["Organization"]),
							"PROPERTY_CARRIER_VALUE" => iconv("utf-8", "windows-1251", $m["Carrier"]),
							"PROPERTY_TRANSPORTATIONDOCUMENT_VALUE" => iconv("utf-8", "windows-1251", $m["TransportationDocument"]),
							"PROPERTY_RESPONSIBLY_VALUE" => iconv("utf-8", "windows-1251", $m["Responsibly"]),
							"PROPERTY_CALCULATIONVARIANT_VALUE" => $m["CalculationVariant"],
							"PROPERTY_TRANSPORTATIONMETHOD_VALUE" => iconv("utf-8", "windows-1251", $m["TransportationMethod"]),
							"PROPERTY_TRANSPORTATIONCOST_VALUE" => iconv("utf-8", "windows-1251", $m["TransportationCost"]),
							"PROPERTY_COMMENT_VALUE" => iconv("utf-8", "windows-1251", $m["Comment"]),
							"EVENTS" => $events,
							"INBOUND" => (iconv("utf-8", "windows-1251", $m['TypeManifest']) == "Входящий манифест") ? '<span class="glyphicon glyphicon-log-out" aria-hidden="true" title="Исходящий манифест"></span>' : '<span class="glyphicon glyphicon-log-in" aria-hidden="true" title="Входящий манифест"></span>',
							"INBOUND_IN" => (iconv("utf-8", "windows-1251", $m['TypeManifest']) == "Входящий манифест") ? 0 : 1,
							"DEBIT" => ($to_debit > 0) ? "Y" : "N",
						);
						$i++;
						$arResult['MANIFESTOS'][] = $a;
						$arResult['MANIFESTOS_DATES'][] = transformDateFrom1c(iconv("utf-8", "windows-1251", $m["DateDoc"]), true);
					}
					arsort($arResult['MANIFESTOS_DATES']);
				}
				
				/*
				if ($arResult['ADMIN_AGENT'])
				{
					echo '<pre>';
					print_r($obj);
					echo '</pre>';
				}
				*/
				
			}
			else
			{
				if ($arResult['ADMIN_AGENT'])
				{
					if ((int)$arResult['CURRENT_AGENT'] > 0)
					{
						$arResult["ERRORS"][] = 'У агента <a href="/agents/index.php?mode=agent&id='.intval($arResult['CURRENT_AGENT']).'" target="_blank">'.$arResult['LIST_OF_AGENTS'][intval($arResult['CURRENT_AGENT'])].'</a> отсутствует ID обмена';
					}
					else
					{
						$arResult["WARNINGS"][] = 'Не выбран агент';
					}
				}
				else
				{
					$arResult["WARNINGS"][] = 'Ошибка доступа, обратитесь в тех. поддержку';
				}
			}
		}
		else
		{
			$nav_array = false;
			$filter = array("IBLOCK_ID" => 85, "PROPERTY_AGENT" => $arResult['CURRENT_AGENT'], "ACTIVE" => "Y");
			$filter[">=PROPERTY_DATEDOC"] = $arResult['LIST_FROM_DATE'].' 00:00:00';
			$filter["<=PROPERTY_DATEDOC"] = $arResult['LIST_TO_DATE'].' 23:59:59';
			
			$res = CIBlockElement::GetList(
				array("PROPERTY_DATEDOC" => "desc"), 
				$filter, 
				false, 
				$nav_array, 
				array(
					"ID",
					"PROPERTY_AGENT",
					"PROPERTY_AGENT.NAME",
					"PROPERTY_NUMBER",
					"PROPERTY_DATEDOC",
					"PROPERTY_CALCULATEDDATE",
					"PROPERTY_DEPARTUREDATE",
					"PROPERTY_PLACES",
					"PROPERTY_WEIGHT",
					"PROPERTY_VOLUMEWEIGHT",
					"PROPERTY_TRANSPORTATIONDOCUMENT",
					"PROPERTY_CARRIER",
					"PROPERTY_TRANSPORTATIONCOST",
					"PROPERTY_PARTNER",
					"PROPERTY_INBOUNDMANIFEST",
					"PROPERTY_RESPONSIBLY",
					"PROPERTY_ORGANIZATION",
					"PROPERTY_COMMENT",
					"PROPERTY_CITY",
					"PROPERTY_CITY.NAME",
					"PROPERTY_CALCULATIONVARIANT",
					"PROPERTY_TRANSPORTATIONMETHOD",
				)
			);
			while ($ob = $res->GetNextElement())
			{
				$a = $ob->GetFields();
				
				$a["PROPERTY_DATEDOC_VALUE"] = substr($a["PROPERTY_DATEDOC_VALUE"], 0, 16);
				if (strlen($a["PROPERTY_DATEDOC_VALUE"]) == 10)
				{
					$a["PROPERTY_DATEDOC_VALUE"] .= ' 00:00';
				}
				$a["PROPERTY_DEPARTUREDATE_VALUE"] = substr($a["PROPERTY_DEPARTUREDATE_VALUE"], 0, 16);
				if (strlen($a["PROPERTY_DEPARTUREDATE_VALUE"]) == 10)
				{
					$a["PROPERTY_DEPARTUREDATE_VALUE"] .= ' 00:00';
				}
				// $a['PACKS'] = array();
				$res_2 = CIBlockElement::GetList(
					array("PROPERTY_DeliveryNote" => "asc"), 
					array("IBLOCK_ID" => 86, "PROPERTY_MANIFEST" => $a['ID'], "ACTIVE" => "Y"), 
					false, 
					false, 
					array(
						"ID",
						"PROPERTY_DELIVERYNOTE",
						"PROPERTY_SEALNUMBER",
						"PROPERTY_PLACES"
					)
				);
				$count_nakls = 0;
				$count_deliverd = 0;
				$arDelivered = array();
				while ($ob_2 = $res_2->GetNextElement())
				{
					$b = $ob_2->GetFields();
					//$a['PACKS'][] = $b;
					
					$a['NUMBERS'][$b['PROPERTY_DELIVERYNOTE_VALUE']] = 'N';
					$count_nakls++;
					
					$n_block = 28;
					$s_block = 30;
					$db_ev = CIBlockElement::GetList(array('PROPERTY_DATE' => 'DESC'), array('IBLOCK_ID' => $n_block, 'NAME' => $b['PROPERTY_DELIVERYNOTE_VALUE']), false, array('nTopCount' => 1), array('ID', 'PROPERTY_NUM'));
					if ($el_ev = $db_ev->Fetch())
					{
						$id_nakl = $el_ev['ID'];
						$db_ev_2 = CIBlockElement::GetList(
							array('PROPERTY_DATE' => 'ASC'), 
							array('IBLOCK_ID' => $s_block, 'PROPERTY_NUM' => $id_nakl), 
							false, 
							false, 
							array('ID', 'PROPERTY_NUM', 'PROPERTY_EVENT', 'PROPERTY_DATE', 'PROPERTY_DESC')
						);
						while ($el_ev_2 = $db_ev_2->Fetch())
						{
							$aRev = array(
								'date' => substr($el_ev_2['PROPERTY_DATE_VALUE'], 0, 10).' '.substr($el_ev_2['PROPERTY_DATE_VALUE'], 11, 5),
								'event' => $el_ev_2['PROPERTY_EVENT_VALUE'],
								'desc' => $el_ev_2['PROPERTY_DESC_VALUE']
							);
							$a['EVENTS'][$b['PROPERTY_DELIVERYNOTE_VALUE']][] = $aRev;
							if ($aRev['event'] == 'Доставлено')
							{
								$a['NUMBERS'][$b['PROPERTY_DELIVERYNOTE_VALUE']] = 'Y';
								if (!in_array($b['PROPERTY_DELIVERYNOTE_VALUE'],$arDelivered))
								{
									$arDelivered[] = $b['PROPERTY_DELIVERYNOTE_VALUE'];
									$count_deliverd++;
								}
							}
						}
					}
					else
					{
						$a['EVENTS'][$b['PROPERTY_DELIVERYNOTE_VALUE']] = array();
					}
					
				}
				$a['DELIVERED'] = ($count_nakls == $count_deliverd) ? 'Y' : 'N';
				$a['COUNT'] = $count_nakls;
				$a['COUNT_DELIVERED'] = $count_deliverd;
				$a["INBOUND"] = '<span class="glyphicon glyphicon-log-in" aria-hidden="true"></span>';
				$arResult['MANIFESTOS'][] = $a;
			}
			/*
			if ($arResult["USER_ID"] == 102)
			{
				echo '<pre>';
				print_r($arResult['MANIFESTOS']);
				echo '</pre>';
			}
			*/
		}
		
		if (count($arResult['MANIFESTOS']) == 0)
		{
			$arResult["WARNINGS"][] = 'Манифесты за указанный период отсутствуют';
		}
		
		$arResult['TITLE'] = GetMessage("TITLE_MODE_LIST");
		$APPLICATION->SetTitle(GetMessage("TITLE_MODE_LIST"));
	}
		
	if ($arResult['MODE'] == 'upload')
	{
		$arResult['OPEN'] = false;
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
		$arResult['LIST_OF_AGENTS'] = false;
		if ($arResult['ADMIN_AGENT'])
		{
			$arResult['LIST_OF_AGENTS'] = AvailableAgents(false, $agent_id);
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
				else
				{
					$_SESSION['CURRENT_AGENT'] = 0;
					$arResult['CURRENT_AGENT'] = 0;
					$_SESSION['CURRENT_INN'] = false;
					$arResult['CURRENT_INN'] = false;
				}
			}
			if (intval($arResult['CURRENT_AGENT']) > 0)
			{
				$arResult['AGENT_INFO'] = GetCompany($arResult['CURRENT_AGENT']);
			}
		}
		if (!CModule::IncludeModule("echogroup.exelimport"))
		{
			$arResult["ERRORS"][] = 'Невозможно загрузить файл, обратитесь в <a href="/support/">тех. поддержку</a>';
		}
		else
		{
			if (strlen($sets_json))
			{
				$arSetsUTF = json_decode($sets_json,true);
				if (is_array($arSetsUTF))
				{
					$arResult['DEFAULT'] = arFromUtfToWin($arSetsUTF);
					if (isset($arResult['DEFAULT']['cells'][$arResult['CELLS'][0]]))
					{
						$arResult['NAME_CELL'] = array_search('NAME', $arResult['DEFAULT']['cells']);
						if (strlen($arResult['NAME_CELL']))
						{
							if ($arResult['CURRENT_INN'])
							{
								$arResult['OPEN'] = true;
								$arResult['CITY_CELLS'] = array();
								$arResult['SELECTED_CELLS'] = array();
								$arResult['FLOAT_CELLS'] = array();
								$arResult['WEIGHT_CELLS'] = array();
								$arResult['LENGTH_CELLS'] = array();
								$arResult['FLOAT_CELLS_NAMES'] = array(
									'PROPERTY_WEIGHT',
									'PROPERTY_VWEIGHT',
									'PROPERTY_HEIGHT',
									'PROPERTY_LENGTH',
									'PROPERTY_WIDTH',
									'PROPERTY_COST',
									'PROPERTY_FOR_PAYMENT',
									'PROPERTY_PAYMENT_COD'
								);
								$arResult['WEIGHT_CELLS_NAMES'] = array(
									'PROPERTY_WEIGHT',
									'PROPERTY_VWEIGHT'
								);
								$arResult['LENGTH_CELLS_NAMES'] = array(
									'PROPERTY_HEIGHT',
									'PROPERTY_LENGTH',
									'PROPERTY_WIDTH'
								);
								if ($arResult['DEFAULT']['weight_format'] == 'gr')
								{
									$arResult['WEIGHT_INDEX'] = 0.001;
								}
								if ($arResult['DEFAULT']['dimensions_format'] == 'm')
								{
									$arResult['LENGTH_INDEX'] = 100;
								}
								if (in_array('PROPERTY_CITY_SENDER',$arResult['DEFAULT']['cells']))
								{
									$arResult['CITY_CELLS'][] = array_search('PROPERTY_CITY_SENDER', $arResult['DEFAULT']['cells']);
								}
								if (in_array('PROPERTY_CITY_RECIPIENT',$arResult['DEFAULT']['cells']))
								{
									$arResult['CITY_CELLS'][] = array_search('PROPERTY_CITY_RECIPIENT', $arResult['DEFAULT']['cells']);
								}
								if (in_array('PROPERTY_TYPE_DELIVERY',$arResult['DEFAULT']['cells']))
								{
									$arResult['TYPE_DELIVERY_CELL'] = array_search('PROPERTY_TYPE_DELIVERY', $arResult['DEFAULT']['cells']);
									$arResult['SELECTED_CELLS'][] = $arResult['TYPE_DELIVERY_CELL'];
								}
								if (in_array('PROPERTY_TYPE_PACK',$arResult['DEFAULT']['cells']))
								{
									$arResult['TYPE_PACK_CELL'] = array_search('PROPERTY_TYPE_PACK', $arResult['DEFAULT']['cells']);
									$arResult['SELECTED_CELLS'][] = $arResult['TYPE_PACK_CELL'];
								}
								if (in_array('PROPERTY_WHO_DELIVERY',$arResult['DEFAULT']['cells']))
								{
									$arResult['WHO_DELIVERY_CELL'] = array_search('PROPERTY_WHO_DELIVERY', $arResult['DEFAULT']['cells']);
									$arResult['SELECTED_CELLS'][] = $arResult['WHO_DELIVERY_CELL'];
								}
								if (in_array('PROPERTY_TYPE_PAYS',$arResult['DEFAULT']['cells']))
								{
									$arResult['TYPE_PAYS_CELL'] = array_search('PROPERTY_TYPE_PAYS', $arResult['DEFAULT']['cells']);
									$arResult['SELECTED_CELLS'][] = $arResult['TYPE_PAYS_CELL'];
								}
								if (in_array('PROPERTY_PAYMENT',$arResult['DEFAULT']['cells']))
								{
									$arResult['PAYMENT_CELL'] = array_search('PROPERTY_PAYMENT', $arResult['DEFAULT']['cells']);
									$arResult['SELECTED_CELLS'][] = $arResult['PAYMENT_CELL'];
								}
								if (in_array('PROPERTY_PLACES',$arResult['DEFAULT']['cells']))
								{
									$arResult['PLACES_CELL'] = array_search('PROPERTY_PLACES', $arResult['DEFAULT']['cells']);
								}
								foreach ($arResult['DEFAULT']['cells'] as $cell => $val)
								{
									if (in_array($val,$arResult['FLOAT_CELLS_NAMES']))
									{
										$arResult['FLOAT_CELLS'][] = $cell;
									}
									if (in_array($val,$arResult['WEIGHT_CELLS_NAMES']))
									{
										$arResult['WEIGHT_CELLS'][] = $cell;
									}
									if (in_array($val,$arResult['LENGTH_CELLS_NAMES']))
									{
										$arResult['LENGTH_CELLS'][] = $cell;
									}
								}
								if (in_array('PROPERTY_IN_DATE_DELIVERY',$arResult['DEFAULT']['cells']))
								{
									$arResult['IN_DATE_DELIVERY_CELL'] = array_search('PROPERTY_IN_DATE_DELIVERY', $arResult['DEFAULT']['cells']);
								}
								if (in_array('PROPERTY_IN_DATETIME_DELIVERY',$arResult['DEFAULT']['cells']))
								{
									$arResult['IN_DATETIME_DELIVERY_CELL'] = array_search('PROPERTY_IN_DATETIME_DELIVERY', $arResult['DEFAULT']['cells']);
								}
								if (in_array('PROPERTY_IN_TIME_DELIVERY',$arResult['DEFAULT']['cells']))
								{
									$arResult['IN_TIME_DELIVERY_CELL'] = array_search('PROPERTY_IN_TIME_DELIVERY', $arResult['DEFAULT']['cells']);
								}
							}
							else
							{
								if ($arResult['ADMIN_AGENT'])
								{
									if (intval($arResult['CURRENT_AGENT']) > 0)
									{
										$arResult["ERRORS"][] = 'У агента <a href="/agents/index.php?mode=agent&id='.intval($arResult['CURRENT_AGENT']).'" target="_blank">'.$arResult['LIST_OF_AGENTS'][intval($arResult['CURRENT_AGENT'])].'</a> отсутствует ID обмена';
									}
									else
									{
										$arResult["WARNINGS"][] = 'Не выбран агент';
									}
								}
								else
								{
									$arResult["WARNINGS"][] = 'Ошибка доступа, обратитесь в тех. поддержку';
								}
							}
						}
						else
						{
							$arResult["ERRORS"][] = 'Некорректно настроена загрузка манифестов: отсутствует столбец с номером накладной';
						}
						
						//echo '<pre>';
						//print_r($arResult['DEFAULT']);
						//echo '</pre>';
						
					}
					else
					{
						$arResult["ERRORS"][] = 'Отсуствутют настройки загрузки манифестов';
					}
				}
				else
				{
					$arResult["ERRORS"][] = 'Отсуствутют настройки загрузки манифестов';
				}
			}
			else
			{
				$arResult["ERRORS"][] = 'Отсуствутют настройки загрузки манифестов';
			}
			if ($arResult['OPEN'])
			{
				if (($_GET['delete'] == 'Y') && (intval($_GET['fileid']) > 0) && (intval($_GET['manid']) > 0))
				{

					$db_props = CIBlockElement::GetProperty(85, intval($_GET['manid']), array("sort" => "asc"), array("CODE"=>"STATE"));
					if($ar_props = $db_props->Fetch())
					{
						if (intval($ar_props["VALUE"]) == 332)
						{
							CFile::Delete(intval($_GET['fileid']));
							CIBlockElement::Delete(intval($_GET['manid']));
							$_SESSION['MESSAGE'][] = 'Манифест №'.intval($_GET['manid']). ' успешно удален';
							LocalRedirect($arParams['LINK_UPLOAD']);
						}
						else
						{
							$arResult["ERRORS"][] = 'Манифест №'.intval($_GET['manid']).' невозможно удалить: неверный статус';
						}
					}
					else
					{
						$arResult["ERRORS"][] = 'Манифест №'.intval($_GET['manid']).' невозможно удалить: неверный статус';
					}
				}
				/* отправить манифест */

                if (isset($_POST['upload']))
				{
					if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
					{
						$_POST = array();
						$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
					}
					else
					{
						$_SESSION[$_POST["key_session"]] = $_POST["rand"];
						$arResult["FILE_ID"] = false;
						$arResult["ZAPIS_ID"] = false;
						if (((int)$_POST['fileid'] > 0) && ((int)$_POST['manid'] > 0))
						{
							$arResult["FILE_ID"] = (int)$_POST['fileid'];
							$arResult["ZAPIS_ID"] = (int)$_POST['manid'];
							//TODO проверка на существование файла
							//TODO проверка на существование записи манифеста
						}
						else
						{
							$arIMAGE = $_FILES["man"];
							$arIMAGE["MODULE_ID"] = "echogroup.exelimport";
							if (strlen($arIMAGE["name"])>0) 
							{
								$res = CFile::CheckFile($arIMAGE, 0, false, "xls, xlsx");
								if (strlen($res)>0)
								{
									$arResult["ERRORS"][] = $res;
								}
								else
								{	
									$arResult["FILE_ID"] = CFile::SaveFile($arIMAGE, "echogroup.exelimport");

									$el = new CIBlockElement;
									$arResult["ZAPIS_ID"] = $el->Add(
										array(
											"MODIFIED_BY"    => $USER->GetID(),
											"IBLOCK_SECTION_ID" => false,
											"IBLOCK_ID"      => 85,
											"PROPERTY_VALUES"=> array(
												605 => $arResult['CURRENT_AGENT'],			
												741 => 332,
												742 => 1,
												743 => $arResult["FILE_ID"],
											),
											"NAME"           => "Загруженный манифест",
											"ACTIVE"         => "Y", 
										)
									);
								}
							}
							else
							{
								$arResult["ERRORS"][] = 'Пустое имя файла';
							}
						}
						if ($arResult["FILE_ID"] && $arResult["ZAPIS_ID"])
						{

						    $arResult["FILE_VALUES"] = CFile::GetFileArray($arResult["FILE_ID"]);
							$arResult["FILE_PATH"] = $arResult["FILE_VALUES"]["SRC"];
							$arResult["CONTENT_TYPE"] = $arResult["FILE_VALUES"]["CONTENT_TYPE"];
							if (($arResult["CONTENT_TYPE"] == "application/vnd.ms-excel") || ($arResult["CONTENT_TYPE"] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") || ($arResult["CONTENT_TYPE"] == 'application/octet-stream'))
							{

							    $objPHPExcel = PHPExcel_IOFactory::load($_SERVER["DOCUMENT_ROOT"].$arResult["FILE_PATH"]);

								$cell_list = $objPHPExcel->getActiveSheet()->getCellCollection();

								$arDeleteRows = array();
								foreach ($cell_list as $cell)
								{
									$letter=preg_replace("!([0-9])!","",$cell);
									$num=preg_replace("!([A-Z])!","",$cell);
									$val = iconv(mb_detect_encoding($objPHPExcel->getActiveSheet()->getCell($cell)->getValue()),"WINDOWS-1251",$objPHPExcel->getActiveSheet()->getCell($cell)->getValue());
									if ($num >= (int)$arResult['DEFAULT']['row_start'])
									{
										$arResult["DATA"][$num][$letter] = $val;
										if (($letter == $arResult['NAME_CELL']) && (!strlen(trim($val))))
										{
											$arDeleteRows[] = $num;
										}
									}
								}
								foreach ($arDeleteRows as $num)
								{
									unset($arResult["DATA"][$num]);
								}
								foreach ($arResult["DATA"] as $num => $row)
								{
									foreach ($row as $cell => $val)
									{
										if (in_array($cell,$arResult['CITY_CELLS']))
										{
											$id_city = GetCityId($val, true);
											$arResult["DATA"][$num][$cell.'_cityid'] = $id_city;
											if ($id_city > 0)
											{
												$arResult["DATA"][$num][$cell.'_cityname'] = GetFullNameOfCity($id_city);
											}
											else
											{
												$arResult["DATA"][$num][$cell.'_cityname'] = $val;
												$arResult["DATA"][$num][$cell.'_error'] = ' class="danger"';
											}
										}
										elseif ($cell == $arResult['TYPE_DELIVERY_CELL'])
										{
											$selected = array_search($val, $arResult['DEFAULT']['TYPE_DELIVERY_VALUES']);
											$arResult["DATA"][$num][$cell.'_name'] = $arResult['TYPE_DELIVERY_VALUES'][$selected];
											$arResult["DATA"][$num][$cell.'_id'] = $selected;
										}
										elseif ($cell == $arResult['TYPE_PACK_CELL'])
										{
											$selected = array_search($val, $arResult['DEFAULT']['TYPE_PACK_VALUES']);
											$arResult["DATA"][$num][$cell.'_name'] = $arResult['TYPE_PACK_VALUES'][$selected];
											$arResult["DATA"][$num][$cell.'_id'] = $selected;
										}
										elseif ($cell == $arResult['WHO_DELIVERY_CELL'])
										{
											$selected = array_search($val, $arResult['DEFAULT']['WHO_DELIVERY_VALUES']);
											$arResult["DATA"][$num][$cell.'_name'] = $arResult['WHO_DELIVERY_VALUES'][$selected];
											$arResult["DATA"][$num][$cell.'_id'] = $selected;
										}
										elseif ($cell == $arResult['TYPE_PAYS_CELL'])
										{
											$selected = array_search($val, $arResult['DEFAULT']['TYPE_PAYS_VALUES']);
											$arResult["DATA"][$num][$cell.'_name'] = $arResult['TYPE_PAYS_VALUES'][$selected];
											$arResult["DATA"][$num][$cell.'_id'] = $selected;
										}
										elseif ($cell == $arResult['PAYMENT_CELL'])
										{
											$selected = array_search($val, $arResult['DEFAULT']['PAYMENT_VALUES']);
											$arResult["DATA"][$num][$cell.'_name'] = $arResult['PAYMENT_VALUES'][$selected];
											$arResult["DATA"][$num][$cell.'_id'] = $selected;
										}
									}
								}
								/*
								if ($arResult["USER_ID"] == 211)
								{
									echo '<pre>';
									print_r($arResult["DATA"]);
									echo '</pre>';
								}
								*/
							}
							else
							{
								AddToLogs('UploadFilesXLSErrors', array('TYPE' => $arResult["CONTENT_TYPE"]));
								$arResult["ERRORS"][] = 'Неверный тип файла ('.$arResult["CONTENT_TYPE"].')';
							}
						}
					}
				}
				if (isset($_POST['send']))
				{
					if ($_POST["rand2"] == $_SESSION[$_POST["key_session2"]])
					{
						$_POST = array();
						$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
					}
					else
					{
						$_SESSION[$_POST["key_session2"]] = $_POST["rand2"];
						if ($arResult['EMAIL_SEND_MANIFEST'])
						{
							$event = new CEvent;
							$event->Send(
								"NEWPARTNER_LK", 
								"s5", 
								array(
									"DATE" => date('d.m.Y H:i'),
									'EMAIL_TO' => $arResult['EMAIL_SEND_MANIFEST'],
									'EMAIL_FROM' => $arResult['AGENT_INFO']['PROPERTY_EMAIL_VALUE'],
									'AGENT_NAME' => $arResult['AGENT_INFO']['NAME']
								), 
								"N", 
								240,
								array($_POST['fileid'])
							);
						}
						$arDelivery = array();
						$arP = array();
						foreach ($arResult['DEFAULT']['cells'] as $cell => $val)
						{
							if (strlen(trim($val)))
							{
								$arP[$val] = $cell;
							}
						}
						
						if (in_array('PROPERTY_TYPE_DELIVERY',$arResult['DEFAULT']['cells']))
						{
							$property_enums = CIBlockPropertyEnum::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>83, "CODE"=>"TYPE_DELIVERY"));
							while($enum_fields = $property_enums->GetNext())
							{
								$arValues['TYPE_DELIVERY'][$enum_fields['ID']] = $enum_fields['XML_ID'];
							}
						}
						if (in_array('PROPERTY_WHO_DELIVERY',$arResult['DEFAULT']['cells']))
						{
							$property_enums = CIBlockPropertyEnum::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>83, "CODE"=>"WHO_DELIVERY"));
							while($enum_fields = $property_enums->GetNext())
							{
								$arValues['WHO_DELIVERY'][$enum_fields['ID']] = $enum_fields['XML_ID'];
							}
						}
						if (in_array('PROPERTY_TYPE_PAYS',$arResult['DEFAULT']['cells']))
						{
							$property_enums = CIBlockPropertyEnum::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>83, "CODE"=>"TYPE_PAYS"));
							while($enum_fields = $property_enums->GetNext())
							{
								$arValues['TYPE_PAYS'][$enum_fields['ID']] = $enum_fields['XML_ID'];
							}
						}
						if (in_array('PROPERTY_PAYMENT',$arResult['DEFAULT']['cells']))
						{
							$property_enums = CIBlockPropertyEnum::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>83, "CODE"=>"PAYMENT"));
							while($enum_fields = $property_enums->GetNext())
							{
								$arValues['PAYMENT'][$enum_fields['ID']] = $enum_fields['XML_ID'];
							}
						}
						$arManifestTo1c = array(
							"ID" => $_POST['manid'],
							"TransportationDocument" => "",
							"Carrier" => "",
							"TransportationCost" => 0,
							"Partner" => $arResult['CURRENT_INN'],
							"DepartureDate" => date('d.m.Y'),
							"Places" => 0,
							"Weight" => 0,
							"VolumeWeight" => 0,
							"Comment" => NewQuotes($_POST['comment']),
							"City" => $arResult['AGENT_INFO']['PROPERTY_CITY_NAME'],
							"TransportationMethod" => "",
							"Delivery" => array()
						);
						foreach ($_POST['send_data'] as $row_id => $yes)
						{
							$data = $_POST['checked_data'][$row_id];
							$arCitySENDER = explode(', ', $data[$arP['PROPERTY_CITY_SENDER']]);
							$arCityRECIPIENT = explode(', ', $data[$arP['PROPERTY_CITY_RECIPIENT']]);
							$reqv['TO_1C_DELIVERY_TYPE'] = 'С';
							$reqv['TO_1C_DELIVERY_CONDITION'] = 'А';
							$reqv['TO_1C_DELIVERY_PAYER'] = 'О';
							$reqv['TO_1C_PAYMENT_TYPE'] = 'Б';
							$reqv['TO_1C_TYPE'] = 1;
							if (intval($data[$arP['PROPERTY_TYPE_DELIVERY'].'_id']) > 0)
							{
								$reqv['TO_1C_DELIVERY_TYPE'] = $arValues['TYPE_DELIVERY'][$data[$arP['PROPERTY_TYPE_DELIVERY'].'_id']];
							}
							if (intval($data[$arP['PROPERTY_WHO_DELIVERY'].'_id']) > 0)
							{
								$reqv['TO_1C_DELIVERY_CONDITION'] = $arValues['WHO_DELIVERY'][$data[$arP['PROPERTY_WHO_DELIVERY'].'_id']];
							}
							if (intval($data[$arP['PROPERTY_TYPE_PAYS'].'_id']) > 0)
							{
								$reqv['TO_1C_DELIVERY_PAYER'] = $arValues['TYPE_PAYS'][$data[$arP['PROPERTY_TYPE_PAYS'].'_id']];
							}
							if (intval($data[$arP['PROPERTY_PAYMENT'].'_id']) > 0)
							{
								$reqv['TO_1C_PAYMENT_TYPE'] = $arValues['PAYMENT'][$data[$arP['PROPERTY_PAYMENT'].'_id']];
							}
							if (intval($data[$arP['PROPERTY_TYPE_PACK'].'_id']) == 247)
							{
								$reqv['TO_1C_TYPE'] = 0;
							}
							$date_take_from = '';
							if (strlen($data[$arP['PROPERTY_IN_DATETIME_DELIVERY']]))
							{
								$date_take_from = $data[$arP['PROPERTY_IN_DATETIME_DELIVERY']];
							}
							else
							{
								$date_take_from = $data[$arP['PROPERTY_IN_DATE_DELIVERY']];
								if (strlen($date_take_from))
								{
									$date_take_from .= strlen($data[$arP['PROPERTY_IN_TIME_DELIVERY']]) ? ' '.$data[$arP['PROPERTY_IN_TIME_DELIVERY']] : '';
								}
							}
							$arManifestTo1c['Delivery'][] = array(
								"DeliveryNote" => $data[$arP['NAME']],
								"DATE_CREATE" => date('d.m.Y'),
								"SMSINFO" => 0,
								"INN" => $arResult['CURRENT_INN'],
								"NAME_SENDER" => NewQuotes($data[$arP['PROPERTY_NAME_SENDER']]),
								"PHONE_SENDER" => $data[$arP['PROPERTY_PHONE_SENDER']],
								"COMPANY_SENDER" => NewQuotes($data[$arP['PROPERTY_COMPANY_SENDER']]),
								//"CITY_SENDER_ID" => $data[$arP['PROPERTY_CITY_SENDER'].'_value'],
								"CITY_SENDER_ID" => 0,
								"CITY_SENDER" => $arCitySENDER[0],
								"INDEX_SENDER" => $data[$arP['PROPERTY_INDEX_SENDER']],
								"COUNTRY_SENDER" => $arCitySENDER[2],
								"REGION_SENDER" => $arCitySENDER[1],
								"ADRESS_SENDER" => NewQuotes($data[$arP['PROPERTY_ADRESS_SENDER']]),
								"NAME_RECIPIENT" => NewQuotes($data[$arP['PROPERTY_NAME_RECIPIENT']]),
								"PHONE_RECIPIENT" => $data[$arP['PROPERTY_PHONE_RECIPIENT']],
								"COMPANY_RECIPIENT" => NewQuotes($data[$arP['PROPERTY_COMPANY_RECIPIENT']]),
								//"CITY_RECIPIENT_ID" => $data[$arP['PROPERTY_CITY_RECIPIENT'].'_value'],
								"CITY_RECIPIENT_ID" => 0,
								"CITY_RECIPIENT" => $arCityRECIPIENT[0],
								"COUNTRY_RECIPIENT" => $arCityRECIPIENT[2],
								"INDEX_RECIPIENT" => $data[$arP['PROPERTY_INDEX_RECIPIENT']],
								"REGION_RECIPIENT" => $arCityRECIPIENT[1],
								"ADRESS_RECIPIENT" => NewQuotes($data[$arP['PROPERTY_ADRESS_RECIPIENT']]),
								"PAYMENT" => $data[$arP["PROPERTY_FOR_PAYMENT"]],
								"PAYMENT_COD" => $data[$arP["PROPERTY_PAYMENT_COD"]],
								"DATE_TAKE_FROM" => $date_take_from,
								"DELIVERY_TYPE" => $reqv['TO_1C_DELIVERY_TYPE'],
								"DELIVERY_PAYER" => $reqv['TO_1C_DELIVERY_PAYER'],
								"PAYMENT_TYPE" => $reqv['TO_1C_PAYMENT_TYPE'],
								"DELIVERY_CONDITION" => $reqv['TO_1C_DELIVERY_CONDITION'],
								"INSTRUCTIONS" => NewQuotes($data[$arP['PROPERTY_INSTRUCTIONS']]),
								"TYPE" => $reqv['TO_1C_TYPE'],	
								"Dimensions" => array(
									array(
										"WEIGHT" => (floatval(str_replace(',','.',$data[$arP['PROPERTY_WEIGHT']])) > 0) ? floatval(str_replace(',','.',$data[$arP['PROPERTY_WEIGHT']])) : 0,
										"VWEIGHT" => (floatval(str_replace(',','.',$data[$arP['PROPERTY_VWEIGHT']])) > 0) ? floatval(str_replace(',','.',$data[$arP['PROPERTY_VWEIGHT']])) : 0,
										"SIZE_1" => (floatval(str_replace(',','.',$data[$arP['PROPERTY_LENGTH']])) > 0) ? floatval(str_replace(',','.',$data[$arP['PROPERTY_LENGTH']])) : 0,
										"SIZE_2" => (floatval(str_replace(',','.',$data[$arP['PROPERTY_WIDTH']])) > 0) ? floatval(str_replace(',','.',$data[$arP['PROPERTY_WIDTH']])) : 0,
										"SIZE_3" => (floatval(str_replace(',','.',$data[$arP['PROPERTY_HEIGHT']])) > 0) ? floatval(str_replace(',','.',$data[$arP['PROPERTY_HEIGHT']])) : 0,
										"PLACES" => intval($data[$arP['PROPERTY_PLACES']]),
										"NAME" => ''
									)
								),
								//'ID' => $reqv['ID'],
								//'ID_BRANCH' => $reqv['BRANCH_CODE'],
								'Goods' => '',
								'DESCRIPTION' => $data[$arP['PROPERTY_DESCRIPTION']]
							);
							$arManifestTo1c["Places"] = $arManifestTo1c["Places"] + intval($data[$arP['PROPERTY_PLACES']]);
							$arManifestTo1c["Weight"] = $arManifestTo1c["Weight"] + floatval(str_replace(',','.',$data[$arP['PROPERTY_WEIGHT']]));
							$arManifestTo1c["VolumeWeight"] = $arManifestTo1c["VolumeWeight"] + floatval(str_replace(',','.',$data[$arP['PROPERTY_VWEIGHT']]));
						}
						
						/*
						echo '<pre>';
						print_r($_POST);
						print_r($arDelivery);
						echo '</pre>';
						*/
						$arManifestTo1cUTF = convArrayToUTF($arManifestTo1c);
						$result = $client->SetManifestsList(array('ListOfDocs' => json_encode($arManifestTo1cUTF)));
						AddToLogs('UploadFilesXLS', array('ListOfDocs' => json_encode($arManifestTo1cUTF)));
						$mResult = $result->return;
						$obj = json_decode($mResult, true);
                        //$arRes = arFromUtfToWin($obj);
						/*
						echo '<pre>';
						print_r($result);
						echo($mResult);
						echo($obj);
						echo '</pre>';
						*/
						if ($obj == 'OK')
						{
							CIBlockElement::SetPropertyValuesEx($_POST['manid'], 85, array(741 => 333, 744 => date('d.m.Y H:i:s')));
							$arResult["MESSAGE"][] = 'Данные успешно отправлены';
							//$arResult["MESSAGE"][] = json_encode($arManifestTo1cUTF);
						}
						else
						{
							$arResult["ERRORS"][] = 'Ошибка передачи манифеста';
						}
					}
				}
			}
		}
		$arResult['MANIFESTOS'] = array();
		$res = CIBlockElement::GetList(
			array("ID" => "desc"), 
			array("IBLOCK_ID" => 85, "PROPERTY_AGENT" => $arResult['CURRENT_AGENT'], "ACTIVE" => "Y","PROPERTY_UPLOAD" => 1), 
			false, 
			array("nTopCount" => 10), 
			array(
				"ID",
				"DATE_CREATE",
				"CREATED_BY",
				"PROPERTY_FILE",
				"PROPERTY_STATE",
			)
		);
		while ($ob = $res->GetNextElement())
		{
			$a = $ob->GetFields();
			$a['FILE_PATH'] = CFile::GetPath($a['PROPERTY_FILE_VALUE']);
			$rsUser2 = CUser::GetByID($a['CREATED_BY']);
			$arUser2 = $rsUser2->Fetch();
			$a['CREATED'] = $arUser2['LAST_NAME'].' '.$arUser2['NAME'].' ['.$arUser2['LOGIN'].']';
			$arResult['MANIFESTOS'][] = $a;
		}
	}
		
	if ($arResult['MODE'] == 'upload_settings')
	{
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
		$arResult['LIST_OF_AGENTS'] = false;
		$arResult["AGENT_INFO"]['USERS'] = array();
		if ($arResult['ADMIN_AGENT'])
		{
			$arResult['LIST_OF_AGENTS'] = AvailableAgents(false, $agent_id);
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
				else
				{
					$_SESSION['CURRENT_AGENT'] = 0;
					$arResult['CURRENT_AGENT'] = 0;
					$_SESSION['CURRENT_INN'] = false;
					$arResult['CURRENT_INN'] = false;
				}
			}
			if (intval($arResult['CURRENT_AGENT']) > 0)
			{
				$arResult['AGENT_INFO'] = GetCompany($arResult['CURRENT_AGENT']);
				$rsUser = CUser::GetList(
					($by="last_name"), 
					($order="asc"), 
					array("ACTIVE" =>"Y", "UF_COMPANY_RU_POST" => $arResult['CURRENT_AGENT']), 
					array("SELECT" => array("UF_UPLOAD_M_SETTINGS"))
				);
				while($arUser = $rsUser->Fetch())
				{
					$arUser['UPLOAD_SETTINGS'] = false;
					if (strlen($arUser['UF_UPLOAD_M_SETTINGS']))
					{
						$arSetsUTF = json_decode($arUser['UF_UPLOAD_M_SETTINGS'],true);
						if (is_array($arSetsUTF))
						{
							$arUser['UPLOAD_SETTINGS'] = arFromUtfToWin($arSetsUTF);
						}
					}
					$arResult["AGENT_INFO"]['USERS'][$arUser['ID']] = $arUser;
				}
			}
		}
		//TODO копирование настроек, если у другого пользователя компании они уже есть
		$arResult['DISPLAY_BTN_GO'] = false;
		$arResult['DEFAULT'] = array(
			'row_start' => 2,
			//'date_format' => 'ДД.ММ.ГГГГ',
			//'time_format' => 'ЧЧ:ММ',
			//'datetime_format' => 'ДД.ММ.ГГГГ ЧЧ:ММ',
			'weight_format' => 'kg',
			'dimensions_format' => 'sm'
		);
		if (strlen($sets_json))
		{
			$arSetsUTF = json_decode($sets_json,true);
			if (is_array($arSetsUTF))
			{
				$arResult['DEFAULT'] = arFromUtfToWin($arSetsUTF);
				$arResult['DISPLAY_BTN_GO'] = true;
			}
		}
		if (isset($_POST['apply_settings']))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
				if (!strlen($sets_json))
				{
					$arResult["ERRORS"][] = 'Пустые настройки загрузки манифеста';
				}
				else
				{
					if (count($_POST['apply_settings_users']) == 0)
					{
						$arResult["ERRORS"][] = 'Не выбран ни один пользователь';
					}
					else
					{
						foreach($_POST['apply_settings_users'] as $userid)
						{
							$user = new CUser;
							$user->Update($userid, array("UF_UPLOAD_M_SETTINGS" => $sets_json));
							$arResult["AGENT_INFO"]['USERS'][$userid]['UPLOAD_SETTINGS'] = $arResult['DEFAULT'];
						}
						$arResult["MESSAGE"][] = 'Настройки загрузки манифеста для пользователей успешно сохранены';
					}
				}
			}
		}
		
		if (isset($_POST['save']))
		{
			
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
				$arResult['DEFAULT'] = $_POST;
				unset($arResult['DEFAULT']['rand']);
				unset($arResult['DEFAULT']['key_session']);
				unset($arResult['DEFAULT']['save']);
				unset($arResult['DEFAULT']['AUTH_FORM']);
				unset($arResult['DEFAULT']['TYPE']);
				unset($arResult['DEFAULT']['backurl']);
				$arResult['ERR_FIELDS'] = array();
				/*
				$arResult['DEFAULT']['date_format'] = ToUpper($arResult['DEFAULT']['date_format']);
				$arResult['DEFAULT']['time_format'] = ToUpper($arResult['DEFAULT']['time_format']);
				
				$arResult['DEFAULT']['date_format_en'] = $arResult['DEFAULT']['date_format'];
				$arResult['DEFAULT']['time_format_en'] = $arResult['DEFAULT']['time_format'];
				$arResult['DEFAULT']['date_format_en'] = str_replace('Д','d',$arResult['DEFAULT']['date_format_en']);
				$arResult['DEFAULT']['date_format_en'] = str_replace('М','m',$arResult['DEFAULT']['date_format_en']);
				$arResult['DEFAULT']['date_format_en'] = str_replace('Г','Y',$arResult['DEFAULT']['date_format_en']);
				$arResult['DEFAULT']['time_format_en'] = str_replace('Ч','H',$arResult['DEFAULT']['time_format_en']);
				$arResult['DEFAULT']['time_format_en'] = str_replace('М','i',$arResult['DEFAULT']['time_format_en']);
				$arResult['DEFAULT']['time_format_en'] = str_replace('С','s',$arResult['DEFAULT']['time_format_en']);
				*/
				/*
				echo '<pre>';
				print_r($arResult['DEFAULT']);
				echo '</pre>';
				*/
				if ((intval($arResult['DEFAULT']['row_start']) == 0) && ($arResult['DEFAULT']['row_start'] != '0'))
				{
					$arResult['ERR_FIELDS']['row_start'] = ' has-error';
				}
				$cells_null_count = 0;
				foreach ($arResult['CELLS'] as $c)
				{
					if (!strlen(trim($arResult['DEFAULT']['cells'][$c])))
					{
						$cells_null_count++;
					}
				}
				if ($cells_null_count == count($arResult['CELLS']))
				{
					$arResult["ERRORS"][] = 'Не выбран порядок столбцов';
					$arResult['ERR_FIELDS_CELLS'] = ' has-error';
				}
				else
				{
					if (!in_array('NAME',$arResult['DEFAULT']['cells']))
					{
						$arResult["ERRORS"][] = 'Не указан столбец с номером накладной';
						$arResult['ERR_FIELDS_CELLS'] = ' has-error';
					}
					if (!in_array('PROPERTY_PLACES',$arResult['DEFAULT']['cells']))
					{
						$arResult["ERRORS"][] = 'Не указан столбец с количеством мест';
						$arResult['ERR_FIELDS_CELLS'] = ' has-error';
					}
					if (!in_array('PROPERTY_WEIGHT',$arResult['DEFAULT']['cells']))
					{
						$arResult["ERRORS"][] = 'Не указан столбец с весом отправления';
						$arResult['ERR_FIELDS_CELLS'] = ' has-error';
					}
					if (!in_array('PROPERTY_CITY_SENDER',$arResult['DEFAULT']['cells']))
					{
						$arResult["ERRORS"][] = 'Не указан столбец с городом отправителя';
						$arResult['ERR_FIELDS_CELLS'] = ' has-error';
					}
					if (!in_array('PROPERTY_CITY_RECIPIENT',$arResult['DEFAULT']['cells']))
					{
						$arResult["ERRORS"][] = 'Не указан столбец с городом получателя';
						$arResult['ERR_FIELDS_CELLS'] = ' has-error';
					}
				}
				if (in_array('PROPERTY_TYPE_DELIVERY',$arResult['DEFAULT']['cells']))
				{
					$TYPE_DELIVERY_ERR = false;
					foreach ($arResult['DEFAULT']['TYPE_DELIVERY_VALUES'] as $k => $v)
					{
						if (!strlen(trim($v)))
						{
							$TYPE_DELIVERY_ERR = true;
							$arResult['ERR_FIELDS']['TYPE_DELIVERY_VALUES_'.$k] = ' has-error';
						}
					}
					if ($TYPE_DELIVERY_ERR)
					{
						$key = array_search('PROPERTY_TYPE_DELIVERY', $arResult['DEFAULT']['cells']);
						$arResult['ERR_FIELDS']['CELL_'.$key] = ' has-warning';
					}
				}
				if (in_array('PROPERTY_TYPE_PACK',$arResult['DEFAULT']['cells']))
				{
					$TYPE_PACK_ERR = false;
					foreach ($arResult['DEFAULT']['TYPE_PACK_VALUES'] as $k => $v)
					{
						if (!strlen(trim($v)))
						{
							$TYPE_PACK_ERR = true;
							$arResult['ERR_FIELDS']['TYPE_PACK_VALUES_'.$k] = ' has-error';
						}
					}
					if ($TYPE_PACK_ERR)
					{
						$key = array_search('PROPERTY_TYPE_PACK', $arResult['DEFAULT']['cells']);
						$arResult['ERR_FIELDS']['CELL_'.$key] = ' has-warning';
					}
				}
				if (in_array('PROPERTY_WHO_DELIVERY',$arResult['DEFAULT']['cells']))
				{
					$WHO_DELIVERY_ERR = false;
					foreach ($arResult['DEFAULT']['WHO_DELIVERY_VALUES'] as $k => $v)
					{
						if (!strlen(trim($v)))
						{
							$WHO_DELIVERY_ERR = true;
							$arResult['ERR_FIELDS']['WHO_DELIVERY_VALUES_'.$k] = ' has-error';
						}
					}
					if ($WHO_DELIVERY_ERR)
					{
						$key = array_search('PROPERTY_WHO_DELIVERY', $arResult['DEFAULT']['cells']);
						$arResult['ERR_FIELDS']['CELL_'.$key] = ' has-warning';
					}
				}
				if (in_array('PROPERTY_TYPE_PAYS',$arResult['DEFAULT']['cells']))
				{
					$TYPE_PAYS_ERR = false;
					foreach ($arResult['DEFAULT']['TYPE_PAYS_VALUES'] as $k => $v)
					{
						if (!strlen(trim($v)))
						{
							$TYPE_PAYS_ERR = true;
							$arResult['ERR_FIELDS']['TYPE_PAYS_VALUES_'.$k] = ' has-error';
						}
					}
					if ($TYPE_PAYS_ERR)
					{
						$key = array_search('PROPERTY_TYPE_PAYS', $arResult['DEFAULT']['cells']);
						$arResult['ERR_FIELDS']['CELL_'.$key] = ' has-warning';
					}
				}
				if (in_array('PROPERTY_PAYMENT',$arResult['DEFAULT']['cells']))
				{
					$PAYMENT_ERR = false;
					foreach ($arResult['DEFAULT']['PAYMENT_VALUES'] as $k => $v)
					{
						if (!strlen(trim($v)))
						{
							$PAYMENT_ERR = true;
							$arResult['ERR_FIELDS']['PAYMENT_VALUES_'.$k] = ' has-error';
						}
					}
					if ($PAYMENT_ERR)
					{
						$key = array_search('PROPERTY_PAYMENT', $arResult['DEFAULT']['cells']);
						$arResult['ERR_FIELDS']['CELL_'.$key] = ' has-warning';
					}
				}
				/*
				if (in_array('PROPERTY_IN_DATE_DELIVERY',$arResult['DEFAULT']['cells']))
				{
					$IN_DATE_DELIVERY_ERR = false;
					if (strlen(trim($arResult['DEFAULT']['date_format'])))
					{
						if (strpos($arResult['DEFAULT']['date_format'],'ДД') === false)
						{
							$IN_DATE_DELIVERY_ERR = true;
						}
						if (strpos($arResult['DEFAULT']['date_format'],'ММ') === false)
						{
							$IN_DATE_DELIVERY_ERR = true;
						}
						if ((strpos($arResult['DEFAULT']['date_format'],'ГГГГ') === false) && (strpos($arResult['DEFAULT']['date_format'],'ГГ') === false))
						{
 							$IN_DATE_DELIVERY_ERR = true;
						}
					}
					else
					{
						$IN_DATE_DELIVERY_ERR = true;
					}
					if ($IN_DATE_DELIVERY_ERR)
					{
						$key = array_search('PROPERTY_IN_DATE_DELIVERY', $arResult['DEFAULT']['cells']);
						$arResult['ERR_FIELDS']['CELL_'.$key] = ' has-warning';
						$arResult['ERR_FIELDS']['date_format'] = ' has-error';
					}
				}
				if (in_array('PROPERTY_IN_TIME_DELIVERY',$arResult['DEFAULT']['cells']))
				{
					$IN_TIME_DELIVERY_ERR = false;
					if (strlen(trim($arResult['DEFAULT']['time_format'])))
					{
						if (strpos($arResult['DEFAULT']['time_format'],'ЧЧ') === false)
						{
							$IN_TIME_DELIVERY_ERR = true;
						}
					}
					else
					{
						$IN_TIME_DELIVERY_ERR = true;
					}
					if ($IN_TIME_DELIVERY_ERR)
					{
						$key = array_search('PROPERTY_IN_TIME_DELIVERY', $arResult['DEFAULT']['cells']);
						$arResult['ERR_FIELDS']['CELL_'.$key] = ' has-warning';
						$arResult['ERR_FIELDS']['time_format'] = ' has-error';
					}
				}
				*/
				if (count($arResult['ERR_FIELDS']) > 0)
				{
					$arResult["ERRORS"][] = 'Допущены ошибки в форме';
				}
				if ((count($arResult['ERR_FIELDS']) == 0) && (count($arResult["ERRORS"]) == 0))
				{
					$arToSave = convArrayToUTF($arResult['DEFAULT']);
					$user = new CUser;
            		$user->Update($USER->GetID(), array("UF_UPLOAD_M_SETTINGS" => json_encode($arToSave)));
					$arResult["MESSAGE"][] = 'Настройки успешно сохранены';
				}
			}
		}
	}
}
$this->IncludeComponentTemplate($arResult['MODE']);