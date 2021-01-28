<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

CModule::IncludeModule('iblock');
CModule::IncludeModule("currency");
$u_id = $USER->GetID();

$agent_array = GetCurrentAgent($u_id);
$agent_id = $agent_array['id'];
$componentPage = false;

/**************************************************/
/************************УК************************/
/**************************************************/
if ($agent_array['type'] == 51)
{
	$modes = array(
		'default_new',
		'period',
		'agent_period',
		'regions',
		'reports',
		'agentreports',
		'agentreport',
		'agentreport_pdf'
	);
	$arResult["MENU"] = array(
		'default_new' => 'Список периодов',
		'regions' => 'Заказы в регионы',
		'reports' => 'Отчеты интернет-магазинов',
		'agentreports' => 'Отчеты субагентов'
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
	if (in_array($_GET['mode'], $modes))
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
		$componentPage = "upr_".$mode;
		unset($arResult["MENU"][$mode]);
	}
	else
	{
		$APPLICATION->AuthForm("Доступ запрещен");
	}
	
	if ($mode == 'default_new')
	{
		$arResult["ALL_PERIODS"] = GetAllPeriods();
		$arResult["NAV_STRING"] = $arResult["ALL_PERIODS"]["NAV_STRING"];
		unset($arResult["ALL_PERIODS"]["NAV_STRING"]);
		$arResult["SHOPS"] = TheListOfShops(0,false,true);
		$arResult['TITLE'] = 'Список периодов';
	}
	
	if ($mode == 'reports')
	{
		$ShopsIds = array();
		$arShops = TheListOfShops(0, false, true, false, '', $agent_array['id']);
		foreach ($arShops as $s)
		{
			$ShopsIds[] = $s['ID'];
		}
		if (count($ShopsIds) > 0)
		{
			$shop = false;
			if (intval($_GET['shop']) > 0)
			{
				if (in_array($_GET['shop'], $ShopsIds))
				{
					$flag = true;
					$ShopsIds = intval($_GET['shop']);
				}
				else
				{
					$flag = false;
				}
			}
			else
			{
				$flag = true;
			}
			if ($flag)
			{
				$arResult['UK_ID'] = $agent_array['id'];
				$payments = (strlen($_GET['payments'])) ? $_GET['payments'] : true;
				$arResult["REPORTS"] = GetListOfReportsShopLight($ShopsIds, $agent_array, 0, true, $payments, false, array("ID"=>"desc"), $_GET['date_from'], $_GET['date_to']);
				$arResult["NAV_STRING"] = $arResult["REPORTS"]["NAV_STRING"];
				unset($arResult["REPORTS"]["NAV_STRING"]);
			}
		}
	}
	
	if ($mode == 'period')
	{
		if (intval($_GET['p_id']) > 0)
		{
			$now = intval($_GET['p_id']);
		}
		else
		{
			$now = GetOpenPeriod();
		}
		$info = GetInfoOfPeriod($now);
		$arResult["TITLE"] = GetMessage("TTL_16",array("#N#"=>$info["NAME"]));
		$arResult["NAME_PERIOD"] = $info["NAME"];
		$arResult["ID_PERIOD"] = $info["ID"];
		$arResult["CLOSED"] = true;
		if (strlen($info["END"]))
		{
			$arResult["DATES"] = "c ".$info["START"]." по ".$info["END"]." [закрыт]";
		}
		else
		{
			$arResult["DATES"] = "c ".$info["START"]." [открыт]";
			$arResult["CLOSED"] = false;
		}
		$arResult["AGENTS"] = AvailableAgents();
		$ag_array = array();
		foreach ($arResult["AGENTS"] as $ag_id => $v)
		{
			$ag_array[] = $ag_id;
		}
		$AGENTS_COUNT_END = GetAgentsOfPacksAndPrices($info["COUNT_END"],$ag_array);
		$arResult["AGENTS_COUNT_END"] = $AGENTS_COUNT_END['ids'];
		$arResult["AGENTS_COUNT_END_SUMM"] = $AGENTS_COUNT_END['summ'];
		// if (!strlen($arResult["AGENTS_COUNT_END_SUMM"][0])) $arResult["AGENTS_COUNT_END_SUMM"][0] = 0;
		if (!is_array($arResult["AGENTS_COUNT_END_SUMM"][0])) $arResult["AGENTS_COUNT_END_SUMM"][0] = array(0=>0);
		$AGENTS_DELIVERED = GetAgentsOfPacksAndPrices($info["DELIVERED"],$ag_array);
		$arResult["AGENTS_DELIVERED"] = $AGENTS_DELIVERED['ids'];
		$arResult["AGENTS_DELIVERED_SUMM"] = $AGENTS_DELIVERED['summ'];
		$arResult["AGENTS_DELIVERED_SUMM_AGENT"] = $AGENTS_DELIVERED['summ_to_agent'];
		$AGENTS_COUNT_START = GetAgentsOfPacksAndPrices($info["COUNT_START"],$ag_array);
		$arResult["AGENTS_COUNT_START"] = $AGENTS_COUNT_START['ids'];
		$arResult["AGENTS_COUNT_START_SUMM"] = $AGENTS_COUNT_START['cost'];
		$arResult["AGENTS_ADOPTED"] = $info["ADOPTED"];
		$arResult["AGENTS_ADOPTED_SUMM"] = GetSummOfPacks($info["ADOPTED"]);
		$arResult["IT_1"] = count($info["COUNT_START"]);
		$arResult["IT_2"] = $arResult["IT_5"] = $arResult["IT_6"] = 0;
		
		foreach ($arResult["AGENTS_COUNT_START_SUMM"] as $v)
		{
			$arResult["IT_2"] = $arResult["IT_2"] + array_sum($v);
		}
		$arResult["IT_3"] = count($info["ADOPTED"]);
		$arResult["IT_4"] = count($info["DELIVERED"]);
		foreach ($arResult["AGENTS_DELIVERED_SUMM"] as $v)
		{
			$arResult["IT_5"] = $arResult["IT_5"] + array_sum($v);
		}
		foreach ($arResult["AGENTS_DELIVERED_SUMM_AGENT"] as $v)
		{
			$arResult["IT_6"] = $arResult["IT_6"] + array_sum($v);
		}
		$arResult["IT_7"] = $arResult["IT_5"]  - $arResult["IT_6"];
		foreach ($arResult["AGENTS_COUNT_END_SUMM"] as $v)
		{
			$arResult["IT_8"] = $arResult["IT_8"] + array_sum($v);
		}
		$arResult["IT_9"] = count($info["COUNT_END"]);
		$arResult["PERIOD"] = $now;
		unset($arResult,$ag_array);
		
	}
	if ($mode == 'agent_period')
	{
		if (intval($_GET['p_id']) > 0)
		{
			$now = intval($_GET['p_id']);
		}
		else
		{
			$now = GetOpenPeriod();
		}
		$info = GetInfoOfPeriod($now);
		if (intval($_GET['ag_id']) > 0)
		{
			$ggg = intval($_GET['ag_id']);
		}
		else
		{
			$ggg = 0;
		}
		$ag_array_inf = AvailableAgents();
		$arResult["TITLE"] = GetMessage("TTL_17",array("#N#"=>$info["NAME"],"#AG_ID#"=>$ggg,"#AG_NAME#"=>$ag_array_inf[$ggg]));
		$arResult["NAME_PERIOD"] = $info["NAME"];
		$arResult["ID_PERIOD"] = $info["ID"];
		$arResult["CLOSED"] = true;
		if (strlen($info["END"]))
		{
			$arResult["DATES"] = "c ".$info["START"]." по ".$info["END"]." [закрыт]";
		}
		else
		{
			$arResult["DATES"] = "c ".$info["START"]." [открыт]";
			$arResult["CLOSED"] = false;
		}
		$ag_array = array($ggg);
		
		$arResult["key"] = $ggg;
	
		$AGENTS_COUNT_END = GetAgentsOfPacksAndPrices($info["COUNT_END"],$ag_array);
		$arResult["AGENTS_COUNT_END"] = $AGENTS_COUNT_END['ids'];
		$arResult["AGENTS_COUNT_END_SUMM"] = $AGENTS_COUNT_END['summ'];
		// if (!strlen($arResult["AGENTS_COUNT_END_SUMM"][0])) $arResult["AGENTS_COUNT_END_SUMM"][0] = 0;
		if (!is_array($arResult["AGENTS_COUNT_END_SUMM"][0])) $arResult["AGENTS_COUNT_END_SUMM"][0] = array(0=>0);
		$AGENTS_DELIVERED = GetAgentsOfPacksAndPrices($info["DELIVERED"],$ag_array);
		$arResult["AGENTS_DELIVERED"] = $AGENTS_DELIVERED['ids'];
		$arResult["AGENTS_DELIVERED_SUMM"] = $AGENTS_DELIVERED['summ'];
		$arResult["AGENTS_DELIVERED_SUMM_AGENT"] = $AGENTS_DELIVERED['summ_to_agent'];
		$AGENTS_COUNT_START = GetAgentsOfPacksAndPrices($info["COUNT_START"],$ag_array);
		$arResult["AGENTS_COUNT_START"] = $AGENTS_COUNT_START['ids'];
		$arResult["AGENTS_COUNT_START_SUMM"] = $AGENTS_COUNT_START['cost'];
		$arResult["AGENTS_ADOPTED"] = $info["ADOPTED"];
		$arResult["AGENTS_ADOPTED_SUMM"] = GetSummOfPacks($info["ADOPTED"]);

		if (is_array($arResult['AGENTS_COUNT_END']))
		{
			$all_packs = array_merge($arResult["AGENTS_DELIVERED"],$arResult['AGENTS_COUNT_START'],$arResult['AGENTS_COUNT_END']);
		}
		else
		{
			$all_packs = array_merge($arResult["AGENTS_DELIVERED"],$arResult['AGENTS_COUNT_START']);
		}

		$arResult['n_zakaz'] = array();
		foreach ($all_packs as $ag_arrays)
		{
			foreach ($ag_arrays as $pack)
			{
				if (!isset($arResult['n_zakaz'][$pack]))
				{
					$db_props = CIBlockElement::GetProperty(42, $pack, array("sort"=>"asc"),array("ID"=>402));
					if ($ob = $db_props->GetNext())
					{
						$n_zakaz = $ob["VALUE"];
					}
					else
					{
						$n_zakaz = $pack;
					}
					$arResult['n_zakaz'][$pack] = $n_zakaz;
				}
			}
		}

	}
	/*
	if ($mode == 'default') {
		if (isset($_GET['form'])) {
			$reports_on_shop = GetReportOnShops($_GET['date_from'],$_GET['date_to']);
		//	$reports_on_agent = GetReportOnAgents($_POST['date_from'],$_POST['date_to']);
			$sh = $reports_on_shop["SHORT"];
		//	$ag = $reports_on_agent["SHORT"];
		//	$arResult["COUNT"] = $arResult["COST"] = $arResult["TO_SHOPS"] = $arResult["UDERZ"] = $arResult["AGETU"] = $arResult["PRISLANO"] = $arResult["VV"] = 0;
			$arResult["COUNT"] = $arResult["COST"] = $arResult["STATE"] = 0;
			foreach ($sh as $v) {
				$arResult["COST"] = $arResult["COST"] + $v["COST_2"];
				$arResult["COUNT"] = $arResult["COUNT"] + $v["CNT"];
				$arResult["STATE"] = $arResult["STATE"] + $v["STATE"];
			
				//$arResult["TO_SHOPS"] = $arResult["TO_SHOPS"] + $v["RAZN"];
				//$arResult["UDERZ"] = $arResult["UDERZ"] + $v["VYIR"];
				
			}
		//	foreach ($ag as $v) {
		//		$arResult["AGETU"] = $arResult["AGETU"] + $v["AGETU"];
		//		$arResult["PRISLANO"] = $arResult["PRISLANO"] + $v["PRISLANO"];
		//	}
			//$arResult["VV"] = $arResult["PRISLANO"] - $arResult["TO_SHOPS"];
			
			$arResult["PRISLANO"] = GetSummTransactions($agent_id,'in',$_GET['date_from'],$_GET['date_to']);
			$arResult["AGETU"] = GetSummTransactions($agent_id,'out',$_GET['date_from'],$_GET['date_to'],53);
			$arResult["TO_SHOPS"] = GetSummTransactions($agent_id,'out',$_GET['date_from'],$_GET['date_to'],52);
			$arResult["VV"] = $arResult["PRISLANO"] - $arResult["AGETU"] - $arResult["TO_SHOPS"];
		}
		$arResult["DATA"] = StatisticDefaut($agent_id);
	}
	if ($mode == 'shops') {
		$arResult["TITLE"] = "Отчет по интернет-магазинам";
		if (isset($_GET['form'])) {
			$reports_on_shop = GetReportOnShops($_GET['date_from'],$_GET['date_to']);
			$arResult["LIST"] = $reports_on_shop["SHORT"];
		}
	}
	
	if ($mode == 'agents') {
		$arResult["TITLE"] = "Отчет по агентам";
		if (isset($_GET['form'])) {
			$reports_on_agent = GetReportOnAgents($_GET['date_from'],$_GET['date_to']);
			$arResult["LIST"] = $reports_on_agent["SHORT"];
		}
	}
	*/
	
	/*****************заказы в регионы*****************/
	if ($mode == 'regions')
	{
		$arResult["CITIES"] = false;
		$arResult['UK_ID'] = $agent_array['id'];
		if ((isset($_GET['date_to_delivery_from'])) || (isset($_GET['date_to_delivery_to'])))
		{
			$filter = array();
			$date_from = strlen($_GET['date_to_delivery_from']) ? $_GET['date_to_delivery_from'] : '';
			$date_to = strlen($_GET['date_to_delivery_to']) ? $_GET['date_to_delivery_to'] : '';	
			$arPacks = GetListOfPackeges(
				$agent_array, 
				intval($_GET['shop']), 
				false, 
				false, 
				false,
				0, 
				false, 
				0, 
				0,
				'',
				'',
				0, 
				false, 
				false, 
				array("PROPERTY_DATE_TO_DELIVERY" => "ASC"), 
				0, 
				0, 
				false, 
				true,
				false,
				false,
				$date_from,
				$date_to
			);
			$arResult["CITIES"] = array();
			$arResult["COUNT"] = 0;
			foreach($arPacks as $pack)
			{
				$arResult["CITIES"][$pack['PROPERTY_CITY_VALUE']]['NAME'] = $pack['PROPERTY_CITY_NAME'];
				$arResult["CITIES"][$pack['PROPERTY_CITY_VALUE']]['PACKS'][] = $pack;
				$arResult["CITIES"][$pack['PROPERTY_CITY_VALUE']]['COUNT']++;
				$arResult["COUNT"]++;
			}
		}
		$arResult['TITLE'] = 'Заказы в регионы';
	}
	
	if ($mode == 'agentreports')
	{
		$arResult["REPORTS"] = GetListOfReportsShopLight(false, $agent_array);
		$arResult["NAV_STRING"] = $arResult["REPORTS"]["NAV_STRING"];
		unset($arResult["REPORTS"]["NAV_STRING"]);
		$arResult["TITLE"] = 'Отчеты субагентов';
	}
	
	if ($mode == 'agentreport')
	{
		$arResult['REPORT'] = GetOneReport($_GET['report_id'],'agent');
		$arResult["TITLE"] = 'Отчет субагента №'.$arResult["REPORT"]['INFO']["PROPERTY_ID_IN_VALUE"];
	}
	
	if ($mode == 'agentreport_pdf')
	{
		
	}
}

//ИМ
/*
if ($agent_array['type'] == 52) {
	$modes = array("default","statistic");
	if (in_array($_GET['mode'],$modes)) $mode = $_GET['mode'];
	else $mode = 'default';
	$componentPage = "shop_".$mode;
	if ($mode == 'default') {
		$arResult["TITLE"] = GetMessage("MESS_1");
		if (isset($_POST['form'])) {
			$reports_on_shop = GetReportOnShops($_POST['date_from'],$_POST['date_to'],$agent_id);
			$arResult["LIST"] = $reports_on_shop["SHORT"][$agent_id];
		}
	}
	if ($mode == 'statistic') {
		$arResult["TITLE"] = GetMessage("MESS_2");
		$arResult["STAT"] = StatisticShop($agent_id);
	}
}
*/

//Агенты
if ($agent_array['type'] == 53)
{
	$modes = array(
		"default_new",
		"period",
		"reports",
		"report"
	);
	$arResult["MENU"] = array(
		'reports' => 'Список отчетов',
		'default_new' => 'Список периодов',
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
	if (in_array($_GET['mode'], $modes))
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
		$componentPage = "agent_".$mode;
		unset($arResult["MENU"][$mode]);
	}
	else
	{
		$APPLICATION->AuthForm("Доступ запрещен");
	}
	

	if ($mode == 'default_new')
	{
		$arResult["ALL_PERIODS"] = GetAllPeriods();
		$arResult["NAV_STRING"] = $arResult["ALL_PERIODS"]["NAV_STRING"];
		unset($arResult["ALL_PERIODS"]["NAV_STRING"]);
		$arResult["TITLE"] = "Список периодов";
	}
	if ($mode == 'period')
	{
		if (intval($_GET['p_id']) > 0)
		{
			$now = intval($_GET['p_id']);
		}
		else
		{
			$now = GetOpenPeriod();
		}
		$info = GetInfoOfPeriod($now);
		$arResult["TITLE"] = GetMessage("TTL_16",array("#N#"=>$info["NAME"]));
		$arResult["NAME_PERIOD"] = $info["NAME"];
		$arResult["ID_PERIOD"] = $info["ID"];
		$arResult["CLOSED"] = true;
		if (strlen($info["END"]))
		{
			$arResult["DATES"] = "c ".$info["START"]." по ".$info["END"]." [закрыт]";
		}
		else
		{
			$arResult["DATES"] = "c ".$info["START"]." [открыт]";
			$arResult["CLOSED"] = false;
		}
		$ag_array = array($agent_id);
		$AGENTS_COUNT_END = GetAgentsOfPacksAndPrices($info["COUNT_END"],$ag_array);
		$arResult["AGENTS_COUNT_END"] = $AGENTS_COUNT_END['ids'];
		$arResult["AGENTS_COUNT_END_SUMM"] = $AGENTS_COUNT_END['summ'];
		// if (!strlen($arResult["AGENTS_COUNT_END_SUMM"][0])) $arResult["AGENTS_COUNT_END_SUMM"][0] = 0;
		// if (!is_array($arResult["AGENTS_COUNT_END_SUMM"][0])) $arResult["AGENTS_COUNT_END_SUMM"][0] = array(0=>0);
		$AGENTS_DELIVERED = GetAgentsOfPacksAndPrices($info["DELIVERED"],$ag_array);
		$arResult["AGENTS_DELIVERED"] = $AGENTS_DELIVERED['ids'];
		$arResult["AGENTS_DELIVERED_SUMM"] = $AGENTS_DELIVERED['summ'];
		$arResult["AGENTS_DELIVERED_SUMM_AGENT"] = $AGENTS_DELIVERED['summ_to_agent'];
		$AGENTS_COUNT_START = GetAgentsOfPacksAndPrices($info["COUNT_START"],$ag_array);
		$arResult["AGENTS_COUNT_START"] = $AGENTS_COUNT_START['ids'];
		$arResult["AGENTS_COUNT_START_SUMM"] = $AGENTS_COUNT_START['summ'];
		$arResult["AGENTS_ADOPTED"] = $info["ADOPTED"];
		$arResult["AGENTS_ADOPTED_SUMM"] = GetSummOfPacks($info["ADOPTED"]);
		$arResult["AGENT_ID"] = $agent_id;
		$arResult["AGENT_NAMES"] = array();
		$numbers = array_merge($arResult["AGENTS_COUNT_START"][$arResult["AGENT_ID"]], $arResult["AGENTS_COUNT_END"][$arResult["AGENT_ID"]], $arResult["AGENTS_DELIVERED"][$arResult["AGENT_ID"]]);
		$res = CIBlockElement::GetList(Array(), array("IBLOCK_ID"=>42, "ID"=>$numbers), false, false, array("ID", "PROPERTY_N_ZAKAZ_IN"));
		while($ob = $res->GetNextElement())
		{
			$arFields = $ob->GetFields();
			$arResult["AGENT_NAMES"][$arFields["ID"]] = $arFields["PROPERTY_N_ZAKAZ_IN_VALUE"];
		}
	}
	/*
	$modes = array("default");
	if (in_array($_GET['mode'],$modes)) $mode = $_GET['mode'];
	else $mode = 'default';
	$componentPage = "agent_".$mode;
	if ($mode == 'default') {
		$arResult["TITLE"] = "Общий отчет";
		if (isset($_GET['form'])) {
			$reports_on_agent = GetReportOnAgents($_GET['date_from'],$_GET['date_to'],$agent_id);
			$arResult["LIST"] = $reports_on_agent["SHORT"][$agent_id];
		}
	}
	*/
	if ($mode == "reports")
	{
		$arResult["SHOP_ID"] = $agent_array['id'];
		$arResult["REPORTS"] = GetListOfReportsShopLight($agent_array['id'], $agent_array);
		$arResult["NAV_STRING"] = $arResult["REPORTS"]["NAV_STRING"];
		unset($arResult["REPORTS"]["NAV_STRING"]);
		$arResult["TITLE"] = 'Список отчетов';
	}
	if ($mode == "report")
	{
		if (isset($_POST['send']))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
				if (!strlen(trim($_POST['reason'])))
				{
					$arResult['ERRORS'][] = 'Не введена причина неоогласия с отчетом';
				}
				else
				{
					$params = array(
						'DATE_SEND' => date('d.m.Y H:i'),
						'AGENT_ID' => $agent_array['id'],
						'AGENT_NAME' => $agent_array['name'],
						'TRUE_OR_FALSE' => 'опровергнул',
						'REPORT_ID' => $_POST['report_id'],
						'REPORT_NUMBER' => $_POST['report_number'],
						'REASON' => '<p>Причина несогласия с отчетом: <strong>'.trim($_POST['reason']).'</strong></p>',
						'SUMM' => '',
						'LINE' => '',
						'LINK_TO_MSG' => '',
						'AUTOMATICALLY' => ''
					);
					$qw = SendMessageInSystem(
						$u_id, 
						$agent_array['id'], 
						$agent_array['uk'], 
						'Отчет субагента №'.$_POST['report_number'].' не согласован',
						210,
						'',
						'',
						231,
						$params
					);
					$params['LINE'] = '<p>=====================================================================</p>';
					$params['LINK_TO_MSG'] = '<p><a href="http://dms.newpartner.ru/messages/index.php?mode=detail&id='.$qw.'">Ссылка на данное сообщение в системе DMS</a></p>';
					$params['AUTOMATICALLY'] = '<p><i>Письмо сгенерировано автоматически и не требует ответа</i></p>';
					SendMessageMailNew($agent_array['uk'], $agent_array['id'], 209, 231, $params);
					$arResult['MESSAGE'][] = 'Сообщение о несогласии с отчетом успешно отправлено';
				}
			}
		}
		
		if (isset($_POST['true_report']))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
				CIBlockElement::SetPropertyValuesEx($_POST['report_id'], 67, array(490 => 1));
				$params = array(
					'DATE_SEND' => date('d.m.Y H:i'),
					'SHOP_ID' => $agent_array['id'],
					'SHOP_NAME' => $agent_array['name'],
					'TRUE_OR_FALSE' => 'подтвердил',
					'REPORT_ID' => $_POST['report_id'],
					'REPORT_NUMBER' => $_POST['report_number'],
					'REASON' => '',
					'SUMM' => '<p>Выплата по отчету составляет: <strong>'.$_POST['summ_report'].'</strong></p>',
					'LINE' => '',
					'LINK_TO_MSG' => '',
					'AUTOMATICALLY' => ''
				);
				$qw = SendMessageInSystem(
					$u_id, 
					$agent_array['id'], 
					$agent_array['uk'], 
					'Отчет субагента №'.$_POST['report_number'].' согласован',
					210,
					'',
					'',
					231,
					$params
				);
				$params['LINE'] = '<p>=====================================================================</p>';
				$params['LINK_TO_MSG'] = '<p><a href="http://dms.newpartner.ru/messages/index.php?mode=detail&id='.$qw.'">Ссылка на данное сообщение в системе DMS</a></p>';
				$params['AUTOMATICALLY'] = '<p><i>Письмо сгенерировано автоматически и не требует ответа</i></p>';
				SendMessageMailNew($agent_array['uk'], $agent_array['id'], 209, 231, $params);
				//TODO добавить информирование и транзакцию на запрос денежных средств
				$arResult['MESSAGE'][] = 'Сообщение о согласии с отчетом успешно отправлено';
			}
		}
		
		$arResult['REPORT'] = GetOneReport($_GET['report_id'],'agent');
		//TODO проверка на доступ только к своему отчету
		$arResult["TITLE"] = 'Отчет субагента №'.$arResult["REPORT"]['INFO']["PROPERTY_ID_IN_VALUE"];
	}
}


$this->IncludeComponentTemplate($componentPage);
?>