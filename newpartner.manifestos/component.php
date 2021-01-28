<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

ini_set("soap.wsdl_cache_enabled", "0" );
ini_set("default_socket_timeout", "300");

include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");
$currentip = GetSettingValue(683);
$currentlink = GetSettingValue(704);
if ((!strlen(trim($currentip))) || (!strlen(trim($currentlink))))
{
	$mode = 'close';
}
else
{
	$url = "http://".$currentip.$currentlink;
	$curl = curl_init();
	curl_setopt_array($curl, array(    
		CURLOPT_URL => $url,
		CURLOPT_HEADER => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_NOBODY => true,
		CURLOPT_TIMEOUT => 5));
	
	$header = explode("\n", curl_exec($curl));
	curl_close($curl);
	
	if (strlen(trim($header[0])))
	{
	
	
	$arResult['OPEN'] = true;
	$arResult['ADMIN_AGENT'] = false;
	$arResult['SITUATION'] = array();
	$arResult['SITUATION_TEXT'] = '';
	
	$modes = array(
		'list',
		'upload',
		'upload_settings'
	);
	
	$arAcc = array(
		'list' => true,
		'upload' => true,
		'upload_settings' => true
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
		$arResult["USER_ID"] =  $USER->GetID();
		$rsUser = CUser::GetByID($arResult["USER_ID"]);
		$arUser = $rsUser->Fetch();
		$agent_id = intval($arUser["UF_COMPANY_RU_POST"]);
	
		if ($agent_id > 0)
		{
			$db_props = CIBlockElement::GetProperty(40, $agent_id, array("sort" => "asc"), array ("ID" => 211) );
			if($ar_props = $db_props->Fetch())
			{
				$agent_type = $ar_props["VALUE"];
				// if (!in_array($agent_type, array(51, $arParams['TYPE'])))
				if (!in_array($agent_type, array(51, 53)))
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
		
		if (strlen($_SESSION['CURRENT_MONTH']))
		{
			$arResult['CURRENT_MONTH'] = $_SESSION['CURRENT_MONTH'];
		}
		if (strlen($_SESSION['CURRENT_YEAR']))
		{
			$arResult['CURRENT_YEAR'] = $_SESSION['CURRENT_YEAR'];
		}
		if (strlen($_SESSION['CURRENT_TYPE_M']))
		{
			$arResult['CURRENT_TYPE_M'] = $_SESSION['CURRENT_TYPE_M'];
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
		if ($_GET['ChangeTypeM'] == 'Y')
		{
			$_SESSION['CURRENT_TYPE_M'] = $_GET['typem'];
			$arResult['CURRENT_TYPE_M'] = $_GET['typem'];
		}
		$datetime = strtotime($arResult['CURRENT_YEAR'].'-'.$arResult['CURRENT_MONTH'].'-01');
		$last_day = date('t', $datetime);
		
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
		}
		
		$arResult['MANIFESTOS'] = array();
		$arResult['MANIFESTOS_DATES'] = array();
		
		if ($arParams["METHOD_LOAD"] == "TYPE1")
		{
			if ($arResult['CURRENT_INN'])
			{
				$arParamsJson = array(
					"INN" => $arResult['CURRENT_INN'],
					"StartDate" => $arResult['CURRENT_YEAR'].'-'.$arResult['CURRENT_MONTH'].'-01',
					"EndDate" => $arResult['CURRENT_YEAR'].'-'.$arResult['CURRENT_MONTH'].'-'.$last_day,
					"TypeManifest" => $arResult['CURRENT_TYPE_M']
				);
		
				$client = new SoapClient(
					'http://'.$currentip.'/sd_msk/ws/DashboardExchange.1cws?wsdl',  
					array('login' => 'DMSUser', 'password' => "1597534682",'exceptions' => false)
				);
				$result = $client->GetManifest($arParamsJson);
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
								$events[$numbernakl][] = array(
									"date" => iconv("utf-8", "windows-1251", $ev["EventDate"]),
									"event" => $event,
									"desc" => iconv("utf-8", "windows-1251", $ev["EventExt"])
								);
								if ($event == "Доставлено")
								{
									$numbers[$numbernakl] = "Y";
									$count_deliverd++;
								}
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
							"INBOUND" => (iconv("utf-8", "windows-1251", $m['TypeManifest']) == "Входящий манифест") ? '<span class="glyphicon glyphicon-log-out" aria-hidden="true" title="Исходящий манифест"></span>' : '<span class="glyphicon glyphicon-log-in" aria-hidden="true" title="Входящий манифест"></span>'
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
			$nav_array = false;
			$filter = array("IBLOCK_ID" => 85, "PROPERTY_AGENT" => $arResult['CURRENT_AGENT'], "ACTIVE" => "Y");
			$filter[">=PROPERTY_DATEDOC"] = $arResult['CURRENT_YEAR'].'-'.$arResult['CURRENT_MONTH'].'-01 00:00:00';
			$filter["<=PROPERTY_DATEDOC"] = $arResult['CURRENT_YEAR'].'-'.$arResult['CURRENT_MONTH'].'-'.$last_day.' 23:59:59';
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
				$a["INBOUND"] = '<span class="glyphicon glyphicon-log-in" aria-hidden="true">';
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
		
		$arResult['TITLE'] = GetMessage("TITLE_MODE_LIST");
		$APPLICATION->SetTitle(GetMessage("TITLE_MODE_LIST"));
	}
		
	if ($mode == 'upload')
	{
		
	}
		
	if ($mode == 'upload_settings')
	{
		$arResult['AVAILABLE_FIELDS'] = array(
			'Номер накладной',
			'Компания отправителя',
			'Фамилия отправителя',
			'Телефон отправителя',
			'Город отправителя',
			'Индекс отправителя',
			'Адрес отправителя',
			'Компания получателя',
			'Фамилия получателя',
			'Телефон получателя',
			'Город получателя',
			'Индекс получателя',
			'Адрес получателя',
			'Тип доставки',
			'Тип отправления',
			'Доставить',
			'Доставить в дату',
			'Доставить до часа',
			'Оплачивает',
			'Оплачивает',
			'Оплата',
			'К оплате',
			'Сумма наложенного платежа',
			'Объявленная стоимость',
			'Мест',
			'Вес',
			'Длина',
			'Ширина',
			'Высота',
			'Объемный вес',
			'Специальные инструкции'
		);
	}
	}
	else
	{
		$mode = 'close';
	}
}

$this->IncludeComponentTemplate($mode);