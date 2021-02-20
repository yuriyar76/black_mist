<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

ini_set("soap.wsdl_cache_enabled", "0");
ini_set("default_socket_timeout", "300");

$start = microtime(true);
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

// delete
// file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/test3.txt', "test3", FILE_APPEND);
// delete 

$modes = [
    'list',
    'add',
    'request',
    'request_modal',
    'request_pdf',
    'request_edit',
    '1c',
    'invoice1c',
    'invoice1c_modal',
    'list_xls',
    'delone',
    'inapps',
    'inapps_update'
];

$arAcc = [
    'list' => true,
    'add' => true,
    'request' => true,
    'request_modal' => true,
    'request_pdf' => true,
    'request_edit' => true,
    '1c' => false,
    'invoice1c' => true,
    'invoice1c_modal' => true,
    'delone' => true,
    'inapps' => true,
    'inapps_update' => true
];

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
    $arResult["PAGES"] = [20, 50, 100, 200];
    if ($arParams['REGISTRATION'] == 2)
    {
        $arResult['modes_edit'] = [236, 261, 240];
    }
    if ($arParams['REGISTRATION'] == 1)
    {
        $arResult['modes_edit'] = [236, 240];
    }
	$arResult["USER_ID"] = $USER->GetID();
    $rsUser = CUser::GetByID($arResult["USER_ID"]);
    $arUser = $rsUser->Fetch();
    $arResult['USER_NAME'] = $USER->GetFullName();
    $agent_id = (int)$arUser["UF_COMPANY_RU_POST"];
    $arResult['USER_BRANCH'] = false;

    if ($agent_id > 0)
    {
        $db_props = CIBlockElement::GetProperty(40, $agent_id, ["sort" => "asc"], ["ID" => 211, "ACTIVE" => "Y"]);
        if ($ar_props = $db_props->Fetch())
        {
            $agent_type = $ar_props["VALUE"];
            if (!in_array($agent_type, [51, $arParams["TYPE"]]))
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
                if ((int)$arResult["UK"] > 0)
                {
                    $currentip = GetSettingValue(683, false, $arResult["UK"]);
                    $currentport = (int)GetSettingValue(761, false, $arResult["UK"]);
                    $currentlink = GetSettingValue(704, false, $arResult["UK"]);
                    $login1c = GetSettingValue(705, false, $arResult["UK"]);
                    $pass1c = GetSettingValue(706, false, $arResult["UK"]);
                    //AddToLogs('test_api', ['data' => $arResult, 'info' => [ $currentip, $currentport, $currentlink, $login1c, $pass1c]]);
                    if ((strlen(trim($currentip))) && (strlen(trim($currentlink))) && (strlen(trim($login1c))) && (strlen(trim($pass1c))))
                    {
											if ($currentport > 0) {
												$url = "http://".$currentip.':'.$currentport.$currentlink;
											}
											else {
												$url = "http://".$currentip.$currentlink;
											}
						//echo  "<!-- DashboardExchange [".$login1c."] -->\n";
						//echo  "<!-- DashboardExchange [".$pass1c."] -->\n";
						//echo  "<!-- DashboardExchange [".$url."] -->\n";
						
                        $curl = curl_init();
                        curl_setopt_array($curl, [
							CURLOPT_URL => $url,
							CURLOPT_HEADER => true,
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_NOBODY => true,
							CURLOPT_TIMEOUT => 10
                        ]);
                        $header = explode("\n", curl_exec($curl));
                        curl_close($curl);
                        if (strlen(trim($header[0])))
                        {
							
                            $arResult['USER_BRANCH'] = ((int)($arUser["UF_BRANCH"]) > 0) ? (int)($arUser["UF_BRANCH"]) : false;
                            if ($arResult['USER_BRANCH'])
                            {
                                $arResult['BRANCH_INFO'] = GetBranch($arResult['USER_BRANCH'], $agent_id);
                                $arResult['BRANCH_IN_1C'] = $arResult['BRANCH_INFO']['PROPERTY_IN_1C_VALUE'];
                            }
                            if ($currentport > 0) {
                                $client = new SoapClient($url, ['login' => $login1c, 'password' => $pass1c, 'proxy_host' => $currentip, 'proxy_port' => $currentport, 'exceptions' => false]);
                            }
                            else {
                                $client = new SoapClient($url, ['login' => $login1c, 'password' => $pass1c,'exceptions' => false]);
                                //AddToLogs('test_api', ['data' => [$client], 'info' => [ $currentip, $currentport, $currentlink, $login1c, $pass1c]]);
                            }
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



$arResult['times'][] = ['name' => 'Первоначальные проверки', 'val' => microtime(true) - $start];

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

if ($mode === 'inapps'){

    $arFilter = ["IBLOCK_ID" => 117, "ACTIVE" => "Y",  "=PROPERTY_CREATOR" => $agent_id];
    $arSelect = [
        "ID","NAME", "ACTIVE", "IBLOCK_ID", "PROPERTY_*"
    ];
    $res = CIBlockElement::GetList(['ID'=>'DESC'], $arFilter, false, ["nPageSize" => 20], $arSelect);
    $res->NavStart(0);
    $i = 0;
    while ($ob = $res->GetNextElement()) {
        $arResult['AGENT_DATA'][] = $ob->GetFields();
        if(!empty($arResult['AGENT_DATA'][$i]['PROPERTY_1059']) && !empty($arResult['AGENT_DATA'][$i]['~PROPERTY_1059'])){
            $arResult['AGENT_DATA'][$i]['PROPERTY_1059'] = $arResult['AGENT_DATA'][$i]['~PROPERTY_1059'];
            $arrEv = json_decode($arResult['AGENT_DATA'][$i]['PROPERTY_1059'], true);
            $arrEvents = arFromUtfToWin($arrEv);
            foreach($arrEvents as $key=>$value){
                $date = date('d-m-Y H:i ', strtotime($value['Date']));
                $arrEvents[$key]['Date'] = $date;
            }
            $arResult['AGENT_DATA'][$i]['EVENTS_ARR'] = $arrEvents;

        }
        $i++;
    }

    $arResult['AGENT_DATA_OBJ']['obj'] = $res;

}

if ($mode == 'request_pdf')
{
	$arResult['REQUEST'] = false;
	$id_reqv = (int)$_GET['id'];
	if ($id_reqv > 0)
	{	
		$filter = array("IBLOCK_ID" => 82, "ID" => $id_reqv, "PROPERTY_CREATOR" => $agent_id);
		if ($arResult['ADMIN_AGENT'])
		{
			unset($filter["PROPERTY_CREATOR"]);
		}
		$res = CIBlockElement::GetList(
			["id" => "desc"],
			$filter,
			false, 
			false, 
			[
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
            ]
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
		
if ($mode === 'list')
{
	$arResult['LIST_TO_DATE'] = date('d.m.Y');
	$prevdate = strtotime('-1 month');
	$arResult['LIST_FROM_DATE'] = date('d.m.Y',$prevdate);
	$arResult['LIST_FROM_DATE_FOR_1C'] = date('Y-m-d',$prevdate);
	$arResult['LIST_TO_DATE_FOR_1C'] = date('Y-m-d');

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
				$timePostDateFrom = strtotime('-1 month',$timePostDateTo);
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
			elseif ((int)$_GET['agent'] == 0)
			{
				unset($_SESSION['CURRENT_AGENT']);
				unset($_SESSION['CURRENT_INN']);
				$arResult['CURRENT_AGENT'] = false;
				$arResult['CURRENT_INN'] = false;
			}
		}
	}

	$arResult['times'][] = ['name' => 'Первоначальные настройки функции', 'val' => microtime(true) - $start];

	if (isset($_POST['delete']))
	{
		if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
		{
			$_POST = [];
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

	//if ((isset($_POST['accept']))  ||  (isset($_POST['send'])))
	if (isset($_POST['send']))
	{
		if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
		{
			$_POST = array();
			$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
		}
		else
		{
			AddToLogs('ReqvSendPostValues',array('IDs' => implode(', ',$_POST['ids'])));
			$_SESSION[$_POST["key_session"]] = $_POST["rand"];
			if (count($_POST['ids']) > 0)
			{
				//$arCells = array();
				$arJson = array();
				/*
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
				*/
				$filter = array("IBLOCK_ID" => 82, "ID" => $_POST['ids'], "PROPERTY_CREATOR" => $agent_id);
				if ($arResult['ADMIN_AGENT'])
				{
					unset($filter["PROPERTY_CREATOR"]);
				}
				$res = CIBlockElement::GetList(
					["id" => "desc"],
					$filter,
					false, 
					false, 
					[
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
						"PROPERTY_DATE_ADOPTION",

                    ]
				);
				while ($ob = $res->GetNextElement())
				{
					$reqv = $ob->GetFields();
					if (strlen($reqv['PROPERTY_DATE_ADOPTION_VALUE']))
					{
						/*
						$logsD = $_SERVER['DOCUMENT_ROOT'].'/logs/log-send-double.txt';
						$errors_fileD = fopen($logsD,'a');
						fwrite($errors_fileD,date('d.m.Y H:i:s').' '.$reqv['ID'].' '.$reqv['PROPERTY_NUMBER_VALUE'].', Пользователь: '.$arResult['USER_NAME']."\n");
						fwrite($errors_fileD,'Инфо: '.implode(',', $reqv)."\n");
						fwrite($errors_fileD,"\n");
						fclose($errors_fileD);
						*/
						$arLogs = [
							'ID' => $reqv['ID'],
							'NUMBER' => $reqv['PROPERTY_NUMBER_VALUE'],
							'INFO' => implode(',', $reqv)
                        ];
						AddToLogs('ReqvSendDoubles',$arLogs);
						
						$arResult["ERRORS"][] = 'Повторная попытка отправки заявки '.$reqv['PROPERTY_NUMBER_VALUE'];
						continue;
					}
					/*
					$reqv['PROPERTY_OB_WEIGHT'] = number_format((($reqv["PROPERTY_SIZE_1_VALUE"]*$reqv["PROPERTY_SIZE_2_VALUE"]*$reqv["PROPERTY_SIZE_3_VALUE"])/$arResult['CURRENT_AGENT_COEFFICIENT_VW']), 2, '.', ' ');
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
					*/
					$date_take = substr($reqv['PROPERTY_DATE_TAKE_VALUE'], 6, 4).'-'.substr($reqv['PROPERTY_DATE_TAKE_VALUE'], 3, 2).'-'.substr($reqv['PROPERTY_DATE_TAKE_VALUE'], 0, 2);
					$t1 = strlen($reqv['PROPERTY_TIME_TAKE_FROM_VALUE']) ? $reqv['PROPERTY_TIME_TAKE_FROM_VALUE'].':00' : '00:00:00';
					$t2 = strlen($reqv['PROPERTY_TIME_TAKE_TO_VALUE']) ? $reqv['PROPERTY_TIME_TAKE_TO_VALUE'].':00' : '00:00:00';
					//$when_z .= '. ';
					$d_cr = substr($reqv['DATE_CREATE'], 6, 4).'-'.substr($reqv['DATE_CREATE'], 3, 2).'-'.substr($reqv['DATE_CREATE'], 0, 2).substr($reqv['DATE_CREATE'], 10, 9);
					$type_cash = ($reqv['PROPERTY_TYPE_CASH_ENUM_ID'] == 264) ? 'cash' : 'non-cash';
					/*
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
					*/

					$arFiles = [];
					foreach ($reqv['PROPERTY_FILES_VALUE'] as $file_id)
					{
						$arfileInfo = CFile::GetFileArray($file_id);
						$arFiles[] = 'agent.newpartner.ru'.$arfileInfo['SRC'];
                        AddToLogs('ReqvFiles',$arFiles);
					}
                    if (empty($arFiles)){
                        $arFiles = "";
                    }
					//define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"]."/log_inn.txt");
					AddMessage2Log($arResult['AGENT']['PROPERTY_INN_VALUE']);
					$arJsonData = [
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
						'INSTRUCTIONS' => $reqv['PROPERTY_INSTRUCTIONS_VALUE'],
						'PLACES' => $reqv['PROPERTY_PLACES_VALUE'],
						'WEIGHT' => $reqv['PROPERTY_WEIGHT_VALUE'],
						'SIZE_1' => $reqv['PROPERTY_SIZE_1_VALUE'],
						'SIZE_2' => $reqv['PROPERTY_SIZE_2_VALUE'],
						'SIZE_3' => $reqv['PROPERTY_SIZE_3_VALUE'],
						'FILES' => $arFiles,
						'InternalNumber' => $reqv["PROPERTY_NUMBER_IN_VALUE"],

                    ];
					$arJson[] = $arJsonData;
					/*
					$logs = $_SERVER['DOCUMENT_ROOT'].'/logs/log-send.txt';
					$errors_file = fopen($logs,'a');
					fwrite($errors_file,date('d.m.Y H:i:s').' '.$reqv['ID'].' '.$reqv['PROPERTY_NUMBER_VALUE']."\n");
					fwrite($errors_file,"\n");
					fclose($errors_file);
					*/

				}
				//$path = "files/applications/".date('Y-m-d').'_'.$agent_id.'_'.time().".xls";
				/*
				$path = "files/applications/".date('Y');
				if (!file_exists($_SERVER['DOCUMENT_ROOT'].$path))
				{
					mkdir($_SERVER['DOCUMENT_ROOT'].$path);	
				}
				$path .= '/'.date('m');
				if (!file_exists($_SERVER['DOCUMENT_ROOT'].$path))
				{
					mkdir($_SERVER['DOCUMENT_ROOT'].$path);	
				}
				$path .= '/'.date('Y-m-d').'_'.$agent_id.'_'.time().".xls";
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
						*/
						$arJsonSend = convArrayToUTF($arJson);
						$arParamsJson = [
							'type' => 2,
							'ListOfDocs' => json_encode($arJsonSend)
                        ];
                        AddToLogs('ReqvSend',$arParamsJson);
						$result = $client->SetDocsList($arParamsJson);
						$mResult = $result->return;
						AddToLogs('SetDocsListResult',array('Response' => $mResult, 'IDs' => implode(', ',$_POST['ids'])));
						$obj = json_decode($mResult, true);
						if ($obj == 'OK')
						{	
							foreach ($_POST['ids'] as $r)
							{
								CIBlockElement::SetPropertyValuesEx($r, 82, [518 => 237, 534 => date('d.m.Y H:i:s')]);
							}
							$arResult["MESSAGE"][] = 'Заявки успешно отправлены';
						}
						else
						{
							$arResult["ERRORS"][] = 'Ошибка передачи заявок';
						}
				/*
					}
				}
				else
				{
					$arResult["ERRORS"][] = 'Произошла ошибка формирования манифеста';
				}
				*/
			}
			else
			{
				$arResult["ERRORS"][] = 'Не выбраны заявки';
			}
		}
	}

	$arResult['REQUESTS'] = [];
	$arResult['ARCHIVE'] = [];

	if ($arResult['CURRENT_AGENT'])
	{
		$nav_array = false;
		$filter = ["IBLOCK_ID" => 82, "PROPERTY_CREATOR" => $arResult['CURRENT_AGENT'], "ACTIVE" => "Y"];
		$filter[">=DATE_CREATE"] = $arResult['LIST_FROM_DATE'].' 00:00:00';
		$filter["<=DATE_CREATE"] = $arResult['LIST_TO_DATE'].' 23:59:59';
		$arResult['SORT_BY'] = "created";
		$arResult['SORT'] = "desc";
		if ($arResult['USER_BRANCH'])
		{
			$filter['PROPERTY_BRANCH'] = $arResult['USER_BRANCH'];
		}
		$res = CIBlockElement::GetList(
			[$arResult['SORT_BY'] => $arResult['SORT']],
			$filter, 
			false, 
			$nav_array, 
			[
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
            ]
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
		$arResult['STATES'] = [];
		$db_enum_list = CIBlockProperty::GetPropertyEnum(518, [], ["IBLOCK_ID"=>82]);
		while($ar_enum_list = $db_enum_list->GetNext())
		{
			$arResult['STATES'][$ar_enum_list["ID"]] = $ar_enum_list["VALUE"];
		}
		$arResult['TITLE'] = GetMessage("TITLE_MODE_LIST");
		$APPLICATION->SetTitle(GetMessage("TITLE_MODE_LIST"));

		$arResult['times'][] = ['name' => 'Выборка заявок на сайте', 'val' => microtime(true) - $start];


		if (in_array($arParams["TYPE"], [51,53,242])) //запрашиваем заявки только для агентов и УК (+ для клиентов - 242)
		{
			if ($arResult['USER_BRANCH'])
			{
				if ((strlen(trim($arResult['BRANCH_INFO']['PROPERTY_IN_1C_CODE_VALUE']))) && (strlen(trim($arResult['BRANCH_INFO']['PROPERTY_IN_1C_PREFIX_VALUE']))))
				{
					$arParamsJson = [
						'BranchID' => iconv('windows-1251','utf-8',$arResult['BRANCH_INFO']['PROPERTY_IN_1C_CODE_VALUE']),
						'BranchPrefix' => iconv('windows-1251','utf-8',$arResult['BRANCH_INFO']['PROPERTY_IN_1C_PREFIX_VALUE']),
						'StartDate' => $arResult['LIST_FROM_DATE_FOR_1C'],
						'EndDate' => $arResult['LIST_TO_DATE_FOR_1C'],
						'NumPage' => 0,
						'DocsToPage' => 1,
						'Type' => 2
                    ];
					$arParamsJson['DocsToPage'] = 10000;	
					$result = $client->GetDocsListBranch($arParamsJson);
					
		// delete
		// file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/test6.txt', print_r($result, true), FILE_APPEND);
		// delete 
					

					$arResult['times'][] = ['name' => 'Получение данных из 1с 1', 'val' => microtime(true) - $start];

					$mResult = $result->return;
					$obj = json_decode($mResult, true);
					foreach ($obj['Docs'] as $d)
					{
						$h = $d;
						$events = $h['Events'];

						unset($h['Events']);
						$m = [];
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
						$m['events'] = [];
						$m['state'] = 'Принято';
						if (count($events) > 0)
						{
							foreach ($events as $ev)
							{
								$ee = [];
								foreach ($ev as $kkk => $vvv)
								{
									$ee[$kkk] = iconv('utf-8', 'windows-1251', $vvv);
								}
								$m['state'] = $ee['Event'];
								$m['events'][] = $ee;
							}
						}
						if ((int)$m['ID'] > 0)
						{
							$res_iblock = (int)CIBlockElement::GetIBlockByID(intval($m['ID']));
							if ($res_iblock != 82)
							{
								$m['ID'] = '';
							}
						}
						else
						{
							$m['ID'] = '';
						}
						$arResult['ARCHIVE'][] = $m;
					}

					$arResult['times'][] = ['name' => 'Разбор данных из 1с 1', 'val' => microtime(true) - $start];

					$arParamsJson['Type'] = 1;
					$arParamsJson['DocsToPage'] = 10000;
					$result = $client->GetDocsListBranch($arParamsJson);
					
					// delete
			        // file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/test7.txt', print_r($result, true), FILE_APPEND);
			        // delete 
					
					$arResult['times'][] = ['name' => 'Получение данных из 1с 2', 'val' => microtime(true) - $start];
					$mResult = $result->return;
					$obj = json_decode($mResult, true);
					foreach ($obj['Docs'] as $d)
					{
						$h = $d;
						$events = $h['Events'];

						unset($h['Events']);
						$m = [];
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
						if ((int)$m['CitySender'] > 0)
						{
							$rr = CIBlockElement::GetByID(intval($m['CitySender']));
							if($ar_rr = $rr->GetNext())
							{
								$m['CitySenderName'] = $ar_rr['NAME'];
							}
						}
						if ((int)$m['CityRecipient'] > 0)
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
						if (intval($m['ID']) > 0)
						{
							$res_iblock = intval(CIBlockElement::GetIBlockByID(intval($m['ID'])));
							if ($res_iblock != 82)
							{
								$m['ID'] = '';
							}
						}
						else
						{
							$m['ID'] = '';
						}
						$arResult['ARCHIVE'][] = $m;
					}
					$arResult['times'][] = array('name' => 'Разбор данных из 1с 2', 'val' => microtime(true) - $start);
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
					'StartDate' => $arResult['LIST_FROM_DATE_FOR_1C'],
					'EndDate' => $arResult['LIST_TO_DATE_FOR_1C'],
					'NumPage' => 0,
					'DocsToPage' => 1,
					'Type' => 3
				);
				$arParamsJson['DocsToPage'] = 10000;
				$result = $client->GetDocsListAgent($arParamsJson);
								
				// delete
				// file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/test9.txt', print_r($result, true), FILE_APPEND);
				// delete 								
				
				$arResult['times'][] = array('name' => 'Получение данных из 1с 3', 'val' => microtime(true) - $start);
				$mResult = $result->return;
				$obj = json_decode($mResult, true);
				foreach ($obj['Docs'] as $d)
				{
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
					if (intval($m['ID']) > 0)
					{
						$res_iblock = intval(CIBlockElement::GetIBlockByID(intval($m['ID'])));
						if ($res_iblock != 82)
						{
							$m['ID'] = '';
						}
					}
					else
					{
						$m['ID'] = '';
					}
					$arResult['ARCHIVE'][] = $m;
				}
				$arResult['times'][] = array('name' => 'Разбор данных из 1с 3', 'val' => microtime(true) - $start);
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
	if ((count($arResult['REQUESTS']) == 0) && (count($arResult['ARCHIVE']) == 0) && (intval($arResult['CURRENT_AGENT']) > 0))
	{
		$arResult["WARNINGS"][] = 'За выбранный период заявки отсутствуют';
	}
	
	//поик сообщений для агента
	$arResult["MESS"] = [];
	$res = CIBlockElement::GetList(
		["id" => "desc"],
		["IBLOCK_ID" => 92, "ACTIVE" => "Y", "PROPERTY_TO" => $arResult['CURRENT_AGENT']],
		false, 
		false, 
		[
			"ID","NAME","PROPERTY_FROM","PROPERTY_TYPE","PROPERTY_COMMENT"
        ]
	);
	while ($ob = $res->GetNextElement())
	{
		$arResult["MESS"][] = $ob->GetFields();
	}
	//поик сообщений для агента

	//Формирование JSON-строки для xls-файла
	$arARCHIVEutf = [
		[
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
        ]
    ];
	foreach ($arResult['REQUESTS'] as $r)
	{
		$date_take_value = $r['PROPERTY_DATE_TAKE_VALUE'];
		$date_take_value .= strlen($r['PROPERTY_TIME_TAKE_FROM_VALUE']) ? ' с '.$r['PROPERTY_TIME_TAKE_FROM_VALUE'] : '';
		$date_take_value .= strlen($r['PROPERTY_TIME_TAKE_TO_VALUE']) ? ' до '.$r['PROPERTY_TIME_TAKE_TO_VALUE'] : '';
		$arARCHIVEutf[] = [
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
        ];
	}
	foreach ($arResult['ARCHIVE'] as $r)
	{
		$name_sender = strlen($r['CompanySender']) ? $r['CompanySender'] : $r['NameSender'];
		$companyRecipient = strlen($r['CompanyRecipient']) ? $r['CompanyRecipient'] : $r['NameRecipient'];
		$arARCHIVEutf[] = [
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
        ];
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
			$result = $client->GetDocComments(['NUMDOC' => iconv('windows-1251','utf-8', trim($_GET['NumDoc'])), 'NUMREQUEST' => iconv('windows-1251','utf-8', $r['PROPERTY_NUMBER_VALUE'])]);
			
			// delete
			// file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/test10.txt', print_r($result, true), FILE_APPEND);
			// delete 
			
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
			AddToLogs('ReqvEditPostValues',$_POST);
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
		$arResult['ERRORS'] = (is_array($arResult['ERRORS'])) ? $arResult['ERRORS'] : array();
		$arResult['ERR_FIELDS'] = (is_array($arResult['ERR_FIELDS'])) ? $arResult['ERR_FIELDS'] : array();
		AddToLogs('RequestsEditErrors',array_merge($arResult['ERRORS'], $arResult["ERR_FIELDS"]));
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
		
if ($mode === 'add')
{


    $arResult['times'][] = ['name' => 'Начало обработки создания заявки', 'val' => microtime(true) - $start];
	$arResult['FILES_ADD'] = [];
	$arResult['COUNT_FILES'] = 4;

	if ((isset($_POST['add'])) || (isset($_POST['add_ctrl'])))
	{
	    if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
		{
			$_POST = [];
			$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
		}
		else
		{
		    AddToLogs('ReqvAddPostValues',$_POST);
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
			$places = (int)str_replace(',', '.', $_POST['PLACES']);
			$weight = (float)str_replace(',', '.', $_POST['WEIGHT']);
			$size_1 = (float)str_replace(',', '.', $_POST['size_1']);
			$size_2 = (float)str_replace(',', '.', $_POST['size_2']);
			$size_3 = (float)(str_replace(',','.',$_POST['size_3']));
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
			$arFilesAdd = [];
			foreach ($_POST['files_id_add'] as $fid)
			{
				if ($_POST['delete_file'][$fid] === 'Y')
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
					$arr_file = [
						"name" => $f['name'],
						"size" => $f['size'],
						"tmp_name" => $f['tmp_name'],
						"type" => "",
						"old_file" => "",
						"del" => "Y",
						"MODULE_ID" => "iblock"
                    ];
					$fid = CFile::SaveFile($arr_file, "files_requests");
					$arFilesAdd[] = $fid;
					$arResult['FILES_ADD'][] = CFile::GetFileArray($fid);
				}
			}
			$arResult['COUNT_FILES'] = $arResult['COUNT_FILES'] - count($arResult['FILES_ADD']);

			if (count($arResult["ERR_FIELDS"]) == 0)
			{
				$state = ($arParams['REGISTRATION'] == 1) ? 236 : 261;
                $state_new = ($arParams['REGISTRATION'] == 1) ? 397 : 398;
				$id_in = GetMaxIDIN(82, 5, true, 538, $agent_id);
                $id_in_new = GetMaxIDIN(105, 5, true, 868, $agent_id);

				$arParamsJson = ['INN' => $arResult['AGENT']['PROPERTY_INN_VALUE']];
				$result = $client->GetPrefixAgent1($arParamsJson);
				$mResult = $result->return;
				$obj = json_decode($mResult, true);

				/*
				if ($USER->GetID() == 211)
				{
					print_r($result);
				}
				*/

				$number = iconv('utf-8', 'windows-1251',
                    $obj['Prefix_'.$arResult['AGENT']['PROPERTY_INN_VALUE']]);
				$number_success = (int)$obj['Success'];

				AddToLogs('RequestsNumbers', [
					'INN' => $arResult['AGENT']['PROPERTY_INN_VALUE'],
					'number_1c' => $obj['Prefix_'.$arResult['AGENT']['PROPERTY_INN_VALUE']],
					'Success_1c' => $obj['Success'],
					'number_success' => $number_success
                ]);
				
				// $number = $arResult['AGENT']['PREFIX_1C'].'-'.$id_in;
				if ($USER->GetID() == 211)
				{
					/*
					$number = 'ТЕСТ-'.rand(1000,9999);
					$number_success = 1;
					*/
					echo $number.' '.$number_success;
				}


				if ((strlen($number)) && ($number_success == 1))
				//if (strlen($number))
				{
                    if(isset($_POST['TRANSPORT_TYPE'])){
                        $TRANSPORT_TYPE = "1";
                    }else{
                        $TRANSPORT_TYPE = "0";
                    }
                    switch ($_POST['PAYMENT'])
                    {
                        case 255:
                            $payment_type1 = 'Наличные';
                            break;
                        case 256:
                            $payment_type1 = 'Безналичные';
                            break;
                    }
                    switch ($_POST['TYPE_PAYS'])
                    {
                        case 251:
                            $delivery_payer1 = 'Отправитель';
                            break;
                        case 252:
                            $delivery_payer1 = 'Получатель';
                            break;
                        case 253:
                            $delivery_payer1 = 'Другой';
                            break;
                    }
                    $arEventFields = [
                        "NUMBER" => $number,
                        "COMPANY" => $arResult['AGENT']['NAME'],
                        "CITY" => $_POST['CITY_SENDER'],
                        "ADRESS" => NewQuotes($_POST['ADRESS_SENDER']),
                        "CONTACT" => NewQuotes($_POST['NAME_SENDER']),
                        "PHONE" => NewQuotes($_POST['PHONE_SENDER']),
                        "WEIGHT" => $weight,
                        'TYPE_PAYS' => $payment_type1,
                        "COMMENT" => NewQuotes($_POST['INSTRUCTIONS']),
                        'PAYER' => $delivery_payer1,
                        "POST" => "tranzit@newpartner.ru, logist@newpartner.ru",

                    ];

				    $el = new CIBlockElement;


					$arLoadProductArray = [
						"MODIFIED_BY" => $USER->GetID(), 
						"IBLOCK_SECTION_ID" => false,
						"IBLOCK_ID" => 82,
						"PROPERTY_VALUES" => [
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
							582 => (int)$id_in,
							533 => NewQuotes($_POST['instructions']),
							538 => $agent_id,
							583 => $arFilesAdd,
							626 => deleteTabs($_POST['number_in']),
							669 => $arResult['USER_BRANCH'],
							687 => $_POST['TYPE_DELIVERY'],
							688 => $_POST['DELIVERY_PAYER'],
							689 => $_POST['DELIVERY_CONDITION'],
							690 => (float)str_replace(',', '.', $_POST['PAYMENT_AMOUNT']),
							695 => (float)str_replace(',', '.', $_POST['COST']),
                            //850 => $TRANSPORT_TYPE,
                        ],
						"NAME" => $number,
						"ACTIVE" => "Y"
                    ];


					if ($z_id = $el->Add($arLoadProductArray))
					{

					    $arLogs = [
							'Type' => 'Новая заявка',
							'ID' => $z_id,
							'Number' => $number
                        ];

						AddToLogs('Requests',$arLogs);

						CIBlockElement::SetPropertyValuesEx($agent_id, 40, [678 => date('d.m.Y H:i:s')]);
						$_SESSION['MESSAGE'][] = "Заявка №".$number." успешно создана";

						if (strlen(trim($_POST['COMPANY_RECIPIENT'])))
						{
							$res = CIBlockElement::GetList(
								["ID" =>"desc"],
								[
									"IBLOCK_ID" => 84, 
									"PROPERTY_CREATOR" => $agent_id, 
									"NAME" => NewQuotes($_POST['COMPANY_RECIPIENT']), 
									"PROPERTY_CITY" => $city_recipient, 
									"PROPERTY_ADRESS" => NewQuotes($_POST['ADRESS_RECIPIENT']),
									"PROPERTY_TYPE" => 260
                                ],
								false, 
								["nTopCount" => 1],
								["ID"]
							);

							if (!$ob = $res->GetNextElement())
							{
								$el2 = new CIBlockElement;
								$arLoadProductArray2 = [
									"MODIFIED_BY" => $USER->GetID(), 
									"IBLOCK_SECTION_ID" => false,
									"IBLOCK_ID" => 84,
									"PROPERTY_VALUES" => [
										579 => $agent_id,
										574 => NewQuotes($_POST['NAME_RECIPIENT']),
										575 => NewQuotes($_POST['PHONE_RECIPIENT']),
										576 => $city_recipient,
										577 => $_POST['INDEX_RECIPIENT'],
										578 => NewQuotes($_POST['ADRESS_RECIPIENT']),
										580 => 260,
										713 => date('d.m.Y H:i:s')
                                    ],
									"NAME" => NewQuotes($_POST['COMPANY_RECIPIENT']),
									"ACTIVE" => "Y"
                                ];
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
								["ID" =>"desc"],
								[
									"IBLOCK_ID" => 84, 
									"PROPERTY_CREATOR" => $agent_id, 
									"NAME" => NewQuotes($_POST['COMPANY_SENDER']), 
									"PROPERTY_CITY" => $CITY_SENDER, 
									"PROPERTY_ADRESS" => NewQuotes($_POST['ADRESS_SENDER']),
									"PROPERTY_TYPE" => 259
                                ],
								false, 
								["nTopCount" => 1],
								["ID"]
							);

							if (!$ob = $res->GetNextElement())
							{
								$el2 = new CIBlockElement;
								$arLoadProductArray2 = [
									"MODIFIED_BY" => $USER->GetID(), 
									"IBLOCK_SECTION_ID" => false,
									"IBLOCK_ID" => 84,
									"PROPERTY_VALUES" => [
										579 => $agent_id,
										574 => NewQuotes($_POST['NAME_SENDER']),
										575 => NewQuotes($_POST['PHONE_SENDER']),
										576 => $CITY_SENDER,
										577 => $_POST['INDEX_SENDER'],
										578 => NewQuotes($_POST['ADRESS_SENDER']),
										580 => 259,
										713 => date('d.m.Y H:i:s')
                                    ],
									"NAME" => NewQuotes($_POST['COMPANY_SENDER']),
									"ACTIVE" => "Y"
                                ];
								$rec_id = $el2->Add($arLoadProductArray2);
							}
							else
							{
								$arFields = $ob->GetFields();
								CIBlockElement::SetPropertyValuesEx($arFields['ID'], 84, [713 => date('d.m.Y H:i:s')]);
							}
						}
                        if($TRANSPORT_TYPE==1){
                            $event = new CEvent;
                            $event->SendImmediate("NEWPARTNER_LK", "S5", $arEventFields, "N", 290);
                        }
						LocalRedirect($arParams['LINK']."index.php");
					}
					else
					{
						$error = $el->LAST_ERROR;
						AddToLogs('RequestsAddErrors', ['ERROR' => $error]);
						$arResult['ERRORS'][] = $error;
					}
				}
				else
				{
					//$arResult['ERRORS'][] = 'Невозможно получить номер заявки, обратитесь в <a href="/support/">тех. поддержку</a>. '.$number;
					$arResult['ERRORS'][] = 'Невозможно получить номер заявки, обратитесь в <a href="/support/">тех. поддержку</a>.';
				}
			}
		}
		$arResult['ERRORS'] = (is_array($arResult['ERRORS'])) ? $arResult['ERRORS'] : [];
		$arResult['ERR_FIELDS'] = (is_array($arResult['ERR_FIELDS'])) ? $arResult['ERR_FIELDS'] : [];
		AddToLogs('RequestsAddErrors',array_merge($arResult['ERRORS'], $arResult["ERR_FIELDS"]));
	}

	$arResult['times'][] = ['name' => 'Начало поиска настроек компании', 'val' => microtime(true) - $start];
	$arSettings = [];
	$settingsJson = $arResult['AGENT']['PROPERTY_ACCOUNT_LK_SETTINGS_VALUE']['TEXT'];
	$arSettings = [];
	if (strlen($settingsJson))
	{
		$arSettings = json_decode(htmlspecialcharsBack($settingsJson), true);
	}
	$arResult['USER_SETTINGS'] = $arSettings[$arResult["USER_ID"]];
	$arResult['DEAULTS'] = [
	//	'NAME_SENDER' => $USER->GetFullName(),
	//	'PHONE_SENDER' => $arUser['PERSONAL_PHONE'],
		'PLACES' => 1,
		'TYPE' => ((int)$arResult['USER_SETTINGS']['TYPE'] > 0) ? (int)$arResult['USER_SETTINGS']['TYPE'] : '',
		'DELIVERY_PAYER' => ((int)$arResult['USER_SETTINGS']['DELIVERY_PAYER'] > 0) ? (int)$arResult['USER_SETTINGS']['DELIVERY_PAYER'] : 293,
		'TYPE_DELIVERY' => ((int)$arResult['USER_SETTINGS']['AGENT_TYPE_DELIVERY'] > 0) ? (int)$arResult['USER_SETTINGS']['AGENT_TYPE_DELIVERY'] : 290,
		'TYPE_CASH' => ((int)$arResult['USER_SETTINGS']['TYPE_CASH'] > 0) ? (int)$arResult['USER_SETTINGS']['TYPE_CASH'] : 265,
		'DELIVERY_CONDITION' => ((int)$arResult['USER_SETTINGS']['DELIVERY_CONDITION'] > 0) ? (int)$arResult['USER_SETTINGS']['DELIVERY_CONDITION'] : 295
    ];
	if (($_GET['copy'] === 'Y') && ((int)$_GET['copyfrom'] > 0))
	{
		$filter = ["IBLOCK_ID" => 82, "ID" => (int)$_GET['copyfrom'], "PROPERTY_CREATOR" => $agent_id];
		if ($arResult['ADMIN_AGENT'])
		{
			unset($filter["PROPERTY_CREATOR"]);
		}
		$res = CIBlockElement::GetList(
			["id" => "desc"],
			$filter, 
			false, 
			false, 
			[
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
				"PROPERTY_TYPE",
				"PROPERTY_TYPE_DELIVERY",
				"PROPERTY_DELIVERY_CONDITION",
				"PROPERTY_DELIVERY_PAYER",
				"PROPERTY_TYPE_CASH",
				//"PROPERTY_INSTRUCTIONS"
            ]
		);
		if ($ob = $res->GetNextElement())
		{
			$r = $ob->GetFields();
			$arResult['DEAULTS']['COMPANY_SENDER'] = $r['PROPERTY_COMPANY_SENDER_VALUE'];
			$arResult['DEAULTS']['NAME_SENDER'] = $r['PROPERTY_NAME_SENDER_VALUE'];
			$arResult['DEAULTS']['PHONE_SENDER'] = $r['PROPERTY_PHONE_SENDER_VALUE'];
			$arResult['DEAULTS']['CITY_SENDER'] = GetFullNameOfCity($r['PROPERTY_CITY_SENDER_VALUE']);
			$arResult['DEAULTS']['INDEX_SENDER'] = $r['PROPERTY_INDEX_SENDER_VALUE'];
			$arResult['DEAULTS']['ADRESS_SENDER'] = $r['PROPERTY_ADRESS_SENDER_VALUE'];
			$arResult['DEAULTS']['COMPANY_RECIPIENT'] = $r['PROPERTY_COMPANY_RECIPIENT_VALUE'];
			$arResult['DEAULTS']['NAME_RECIPIENT'] = $r['PROPERTY_NAME_RECIPIENT_VALUE'];
			$arResult['DEAULTS']['PHONE_RECIPIENT'] = $r['PROPERTY_PHONE_RECIPIENT_VALUE'];
			$arResult['DEAULTS']['CITY_RECIPIENT'] = GetFullNameOfCity($r['PROPERTY_CITY_RECIPIENT_VALUE']);
			$arResult['DEAULTS']['INDEX_RECIPIENT'] = $r['PROPERTY_INDEX_RECIPIENT_VALUE'];
			$arResult['DEAULTS']['ADRESS_RECIPIENT'] = $r['PROPERTY_ADRESS_RECIPIENT_VALUE'];
			$arResult['DEAULTS']['TYPE'] = $r['PROPERTY_TYPE_ENUM_ID'];
			$arResult['DEAULTS']['TYPE_DELIVERY'] = $r['PROPERTY_TYPE_DELIVERY_ENUM_ID'];
			$arResult['DEAULTS']['DELIVERY_CONDITION'] = $r['PROPERTY_DELIVERY_CONDITION_ENUM_ID'];
			$arResult['DEAULTS']['DELIVERY_PAYER'] = $r['PROPERTY_DELIVERY_PAYER_ENUM_ID'];
			$arResult['DEAULTS']['TYPE_CASH'] = $r['PROPERTY_TYPE_CASH_ENUM_ID'];
			//$arResult['DEAULTS']['INSTRUCTIONS'] = $r['PROPERTY_INSTRUCTIONS_VALUE'];
		}
	}
	
	$arResult['times'][] = ['name' => 'Завершение обработки создания заявки', 'val' => microtime(true) - $start];
	$arResult['SENDERS'] = GetListContractors($agent_id, 259, false);
	if ((count($arResult['SENDERS']) == 0) && ($arParams['RESTRICTION_SENDERS'] !== 'Y'))
	{
		$arResult['OPEN'] = false;
		$arResult["ERRORS"][] = GetMessage('ERR_NO_SENDERS');
	}
	$arResult['TITLE'] = GetMessage("TITLE_MODE_ADD");
	$APPLICATION->SetTitle(GetMessage("TITLE_MODE_ADD"));
	$arResult['times'][] = ['name' => 'Завершение обработки создания заявки', 'val' => microtime(true) - $start];

}
		
if ($mode === '1c')
{
    $arLogs = [];
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
            $arRes = [];
            $json_string = $_POST['Response'];
            $obj = json_decode($json_string, true);
            foreach ($obj as $k => $v)
            {
                $k_tr = iconv('utf-8', 'windows-1251', $k);
                $v_tr = iconv('utf-8', 'windows-1251', $v);
                $arRes[$k_tr] = $v_tr;
            }

            // запись заявок на агента
            if ($_POST['type'] === 'pickup'){
                $weight = '';
                $weightV = '';
                $places =  '';
                $info = '';

                $inn_uk = (int)$arRes['creatorinn'];
                $inn_agent = $arRes['inn'];
                $number_uid = htmlspecialcharsEx(trim($arRes['uid']));
                AddToLogs('1c_pickup', ['newpartner.requests.v2.1-2340'=>['uid'=>$number_uid, 'uk'=>$inn_uk,
                    'agent'=>$inn_agent, 'post'=> $_POST]]);
                if(!( $inn_uk && $inn_agent && $number_uid) ){
                    exit();
                }
                $arrC = GetIDAgentByINN($inn_agent, 53, false, true);
                $creator = (int)$arrC[0]['ID'];
                if(!$creator){
                    AddToLogs('1c_pickup', ['newpartner.requests.v2.1-2360'=> ['Error' => 'Агент не найден', 'number_uid'=>$number_uid]] );
                    exit();
                }
                $id_uk = GetIDAgentByINN($inn_uk, 51);
                $client = soap_include($id_uk);
               // AddToLogs('1c_pickup', ['newpartner.requests.v2.1-2353'=>['client'=>$client, 'id_uk' => $id_uk]]);
                if(!$client) exit();
                $arParamsJson = [
                    'UID' => $number_uid
                ];
                $request = $client->GetAgentsPickup($arParamsJson);
                $result = $request->return;
                $result = arFromUtfToWin(json_decode($result, true));
                AddToLogs('1c_pickup', ['newpartner.requests.v2.1-2359'=>['result'=>$result]]);

                if(!empty($result['Error'])){
                    AddToLogs('1c_pickup', ['newpartner.requests.v2.1-2376'=> ['Error' => $result['Error']]] );
                    exit();
                }
                $rec_id = setAppForAgent($result, $id_uk, $arrC, $number_uid, $creator, $inn_agent);
                AddToLogs('1c_pickup', ['newpartner.requests.v2.1-2380'=> ['id' => $rec_id]] );
                exit();
            }

            if ($_POST['type'] === 'newcomment')
            {
                $creator = GetIDAgentByINN(trim($arRes['creator']));
                $arForWhoInn = explode(',',$arRes['inn']);
                $arForWho = [];
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
                    $rsUser = CUser::GetList(($by="id"), ($order="asc"), ["GROUPS_ID" => [4,16],
                        "UF_COMPANY_RU_POST" => $creator],
                        ["SELECT" => ["UF_BRANCH","UF_ROLE"]]);
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
                        [
                            "IBLOCK_SECTION_ID" => false,
                            "IBLOCK_ID" => 92,
                            "PROPERTY_VALUES"=> [
                              699 => $creator,
                              700 => $forwho,
                              701 => 302,
                              702 => trim($arRes['number'])
                            ],
                            "NAME" => "Новый комментарий по накладной ".$arRes['number'],
                            "DETAIL_TEXT" => $arRes["newcomment"],
                            "ACTIVE" => "Y"
                        ]);
                    $users_to = [];
                    $rsUser = CUser::GetList(($by="id"), ($order="asc"), ["GROUPS_ID" => [4,16],
                        "UF_COMPANY_RU_POST" => $forwho], ["SELECT" => ["UF_BRANCH","UF_ROLE"]]);
                    while($arUser = $rsUser->Fetch())
                    {
                        $users_to[] = $arUser['ID'];
                    }
                    foreach ($users_to as $user_to)
                    {
                        $arMessageFields = [
                          "TO_USER_ID" => $user_to,
                          "FROM_USER_ID" => $user_from,
                          "NOTIFY_MODULE" => "im",
                          "NOTIFY_TYPE" => ($user_from) ? IM_NOTIFY_FROM : IM_NOTIFY_SYSTEM,
                          "NOTIFY_MESSAGE" => "Новый комментарий по накладной ".$arRes['number'].": ".$arRes["newcomment"].'. [url=/messages/?number='.$arRes['number'].'&ids='.$mess_id.']Перейти к диалогу по накладной[/url]'
                        ];
                       CIMNotify::Add($arMessageFields);
                    }
                }

            }
            else
            {
                if ((int)$arRes['ID'] > 0)
                {
                    $res = CIBlockElement::GetByID((int)$arRes['ID']);
                    if ($ar_res = $res->GetNext())
                    {
                        //NOTE Заявки
                        if ((int)($ar_res['IBLOCK_ID']) == 82)
                        {
                            if ($_POST['type'] === 'accepted')
                            {
                                if (strlen(trim($arRes['Number'])))
                                {
                                    $date_accepted = '';
                                    $db_props = CIBlockElement::GetProperty(82, (int)$arRes['ID'], ["sort" => "asc"], array("ID"=>584));
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
										$res_2 = $el->Update((int)$arRes['ID'], ["ACTIVE" => "N","NAME" => trim($arRes['Number'])]);
										CIBlockElement::SetPropertyValuesEx((int)$arRes['ID'], false, [
											518 => 238,
											584 => date('d.m.Y H:i:s'),
											539 => $arRes['Reason']
                                        ]);
                                        $arResult["INFO"][] = 'Заявка '.trim($arRes['Number']).' ['. (int)$arRes['ID'] .'] принята';
                                    }
                                }
                                else
                                {
                                    $arResult["ERRORS"][] = 'Пустой Номер Заявки';
                                }
                            }
                            elseif ($_POST['type'] === 'rejected')
                            {
                                CIBlockElement::SetPropertyValuesEx((int)$arRes['ID'], 82, [518 => 240, 539 => $arRes['Reason']]);
                                $arResult["INFO"][] = 'Заявка '.trim($arRes['Number']).' ['. (int)$arRes['ID'] .'] отклонена на причине '.$arRes['Reason'];
                            }
                            else
                            {
                                $arResult["ERRORS"][] = 'Неизвестный тип';
                            }
                        }
                        //NOTE Накладные
                        elseif ((int)($ar_res['IBLOCK_ID']) == 83)
                        {
                            if ($_POST['type'] === 'accepted')
                            {
                                $id_state = 0;
                                $db_props = CIBlockElement::GetProperty(83, (int)$arRes['ID'], ["sort" => "asc"], ["ID"=>572]);
                                if ($ar_props = $db_props->Fetch())
                                {
                                    $id_state = (int)$ar_props["VALUE"];
                                }
                                if ($id_state == 257)
                                {
                                    CIBlockElement::SetPropertyValuesEx((int)$arRes['ID'], 83, [572 => 258, 573 => date('d.m.Y H:i:s')]);
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
    if ($_POST['type'] === 'newcomment')
    {
        AddToLogs('NewComments',$arLogs);
    }
    else
    {
        AddToLogs('Accepted',$arLogs);
    }
}

if ($mode === 'inapps_update'){

    $arRes = [];
    foreach($_POST as $item){
        $arRes[] = htmlspecialcharsEx(trim($item));
    }
    $arr_id_rec = explode('_', $arRes[0]);
   /*
    * arres
    [0] => update_63423166
    [1] => e482605f-727a-11eb-a29f-000c29cf960f
    [2] => 2197189
    [3] => postclub76  id обмена
   */

    $id_rec = $arr_id_rec[1];
    $number_uid =  $arRes[1];
    $id_uk = $arRes[2];
    $inn_agent = $arRes[3];

    if($id_uk){
        $client = soap_include($id_uk);
    }else{
        AddToLogs('1c_pickup', ['newpartner.requests.v2.1-2568'=>['Error'=>'Нет УК']]);
        exit();
    }
    if(!$client) {
        AddToLogs('1c_pickup', ['newpartner.requests.v2.1-2572'=>['Error'=>'Нет соединения с 1с']]);
        exit();
    }
    $arParamsJson = [
        'UID' => $number_uid
    ];
    $request = $client->GetAgentsPickup($arParamsJson);
    $result = $request->return;
    $result = arFromUtfToWin(json_decode($result, true));
    $arrC = GetIDAgentByINN($inn_agent, 53, false, true);
    $creator = $arrC[0]['ID'];
    if(!$creator){
        AddToLogs('1c_pickup', ['newpartner.requests.v2.1-2583'=> ['Error' => 'Агент не найден',
            'number_uid'=>$number_uid]] );
        exit();
    }
    setAppForAgent($result, $id_uk, $arrC, $number_uid, $creator, $inn_agent, $id_rec);

    if(!empty($id_rec)){
        $arFilter = ["IBLOCK_ID" => 117, "ACTIVE" => "Y",  "ID" => $id_rec];
        $arSelect = [
            "ID","NAME", "ACTIVE", "IBLOCK_ID", "PROPERTY_*"
        ];
        $resUid = CIBlockElement::GetList([], $arFilter, false,false, $arSelect);
        while ($ob = $resUid->GetNextElement()) {
            $rec = $ob->GetFields();
        }
        if(!empty($rec['PROPERTY_1059']) && !empty($rec['~PROPERTY_1059'])){
            $rec['PROPERTY_1059'] = $rec['~PROPERTY_1059'];
        }
        if (!empty($rec)){
            //AddToLogs('1c_pickup', ['newpartner.requests.v2.1-2601'=>['result'=>$rec]]);
            $jsonArr = json_encode(convArrayToUTF($rec));
            echo $jsonArr;
            exit();
        }
    }
    exit();
}
		
if (($mode === 'invoice1c') || ($mode === 'invoice1c_modal'))
{
    if (strlen(trim($_GET['f001'])))
    {
        $arParamsJson = [
            'NumDoc' => trim($_GET['f001']),
        ];
        $result_0 = $client->GetDocInfo($arParamsJson);
		
		// delete
		//	file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/test11.txt', print_r($result, true), FILE_APPEND);
		// delete 
		
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

if ($mode === 'list_xls')
{
    if (strlen($_POST['DATA']))
    {
        $arData = json_decode(htmlspecialchars_decode($_POST['DATA'],ENT_COMPAT), true);
        include_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/PhpExcel/Classes/PHPExcel.php';
        $pExcel = new PHPExcel();
        $pExcel->setActiveSheetIndex(0);
        $aSheet = $pExcel->getActiveSheet();
        $pExcel->getDefaultStyle()->getFont()->setName('Arial');
        $pExcel->getDefaultStyle()->getFont()->setSize(10);
        $Q = iconv("windows-1251", "utf-8", 'Заявки');
        $aSheet->setTitle($Q);
        $head_style = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ]
        ];
        $i = 1;
        $arJ = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O'];
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
        include_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/PhpExcel/Classes/PHPExcel/Writer/Excel5.php';
        $objWriter = new PHPExcel_Writer_Excel5($pExcel);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.date('Заявки d.m.Y').'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
    }
}

if ($mode === 'delone'){
    //dump ($arParams['LINK']);
    $id_invoice = (int)$_GET['n'];
    $name_invoice = strip_tags(htmlspecialchars($_GET['name']));
    $el = new CIBlockElement;
    $loc = $el->GetByID($id_invoice);
    if($ar_res = $loc->GetNext())
        $name_test =  $ar_res['NAME'];

    if($name_invoice == $name_test ){
        $res = $el->Update($id_invoice, ["ACTIVE"=>"N"]);
        if($res){
            AddToLogs('InvoicesDelete', ['ID' => $id_invoice]);
            $arResult['MESSAGE'][] = "Накладная  $name_invoice успешно удалена";
            $arParamsJson = [
                'ID' => $id_invoice
            ];
            $result = $client->SetPickupDelete($arParamsJson);
        }
    }

    LocalRedirect('http://agent.newpartner.ru/waybills/');
}

$arResult['times'][] = ['name' => 'Полное выполнение скрипта', 'val' => microtime(true) - $start];
$this->IncludeComponentTemplate($mode);