<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
$start = microtime(true);
$arResult['times'] = array();
//$this->IncludeComponentTemplate('close');

include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");
$currentip = GetSettingValue(683);
if (!strlen(trim($currentip)))
{
	$mode = 'close';
}
else
{
	$url = 'http://'.$currentip.'/sd_msk/ws/DashboardExchange.1cws?wsdl';
	
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
	
	
	
	$arResult['OPEN'] = true;
	$arResult['ADMIN_AGENT'] = false;
	$arResult['SITUATION'] = array();
	$arResult['SITUATION_TEXT'] = '';
	
	$modes = array(
		'list'
	);
	
	$arAcc = array(
		'list' => true
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
		$arResult['NOT_DELIVERED'] = array();
		
		$arResult['times'][] = array('name' => 'Первоначальные настройки функции', 'val' => microtime(true) - $start); 
		
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
			
			$arResult['times'][] = array('name' => 'Получение данных из 1с', 'val' => microtime(true) - $start);
			
			$mResult = $result->return;
			$obj = json_decode($mResult, true);
			if ($obj)
			{
				$i = 0;
				foreach ($obj as $m)
				{
					$count_nakls = count($m["DeliveryNotes"]);
					$count_deliverd = 0;
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
						if ($numbers[$numbernakl] == "N")
						{
							$arResult['NOT_DELIVERED'][] = $numbernakl;
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
			
			$arResult['times'][] = array('name' => 'Разбор данных из 1с 1', 'val' => microtime(true) - $start);

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
		
		$arResult['TITLE'] = GetMessage("TITLE_MODE_LIST");
		$APPLICATION->SetTitle(GetMessage("TITLE_MODE_LIST"));
	}
	
	}
	else
	{
		$mode = 'close';
	}
}
$this->IncludeComponentTemplate($mode);