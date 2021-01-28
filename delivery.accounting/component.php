<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

CModule::IncludeModule("iblock");
CModule::IncludeModule("currency");
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
		'periods',
		// 'default',
		'transactions_in',
		'transactions_out',
		'detail_agent',
		'confirm',
		'pay_shop',
		'pay_shop_xls'
	);
	$arResult["MENU"] = array(
		'periods'=> GetMessage("TTL_19"),
		'pay_shop' => GetMessage("TTL_1"),
		'transactions_in' => GetMessage("TTL_12"),
		'transactions_out' => GetMessage("TTL_20"),
		'confirm' => GetMessage("TTL_17")
	);
	foreach ($arResult["MENU"] as $m => $name)
	{
		if ($arParams['PERM'][$agent_array['type']][$m][$arResult['ROLE_USER']] == "C")
			unset($arResult["MENU"][$m]);
	}
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
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	}
	if (isset($arParams['PERM'][$agent_array['type']]['ALL']))
	{
		$arResult["PERM"] = $arParams['PERM'][$agent_array['type']]['ALL'];
	}
	else
	{
		$arResult["PERM"] = $arParams['PERM'][$agent_array['type']][$mode][$arResult['ROLE_USER']];
	}
	if ($arResult["PERM"] == "C")
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	
	/****************Платежи за товары*****************/
	if ($mode == 'default')
	{
		if (isset($_POST['money_to_shop'])) {
			if (count($_POST['shop_id']) <= 0) $arResult["ERRORS"][] = GetMessage("ERR_2");
			if (count($arResult['ERRORS']) == 0)
			{
				foreach ($_POST['shop_id'] as $shop_id)
				{
					$now_time = date('d.m.Y H:i:s');
					$summ = $_POST['summ'][$shop_id];
					if ($summ > 0)
					{
						$packs = $_POST['packs'][$shop_id];
						$at_shop_now = GetAccount($shop_id);
						$at_upr_now = GetAccount(2197189);
						$at_shop = $at_shop_now + $summ;
						$at_upr = $at_upr_now - $summ;
						CIBlockElement::SetPropertyValuesEx($shop_id, false, array(219=>$at_shop));
						CIBlockElement::SetPropertyValuesEx(2197189, false, array(219=>$at_upr));
						$txt = GetMessage("MAIL_1",array("#NOW_TIME#" => $now_time, "#SUMM#" => CurrencyFormat($summ,"RUU"), "#PLAT#" => $_POST['plat'][$shop_id]));
						$el_0 = new CIBlockElement;
						$arLoadProductArray_0 = Array(
							"MODIFIED_BY"    => $u_id,
							"IBLOCK_ID"      => 50,
							"DETAIL_TEXT" => $txt,
							"DETAIL_TEXT_TYPE" => "html",
							"PROPERTY_VALUES"=> array(
								234 => $agent_id,
								235 => $shop_id,
								236 => 84,
								242 => CurrencyFormat($summ,"RUU")
							),
							"NAME" =>  GetMessage("NAME_OF_MESS")
						);
						$qw = $el_0->Add($arLoadProductArray_0);
						SendMessageMailNew($shop_id,$agent_id,94,167,array(
							"DATE_RES"=>$now_time,
							"SUMM"=>CurrencyFormat($summ,"RUU"),
							"PLAT"=>$_POST['plat'][$shop_id],
							"LINK"=>'/payments/',
							"ID_MESS"=>$qw
							));
						foreach  ($packs as $pp) {
							CIBlockElement::SetPropertyValuesEx($pp, false, array(218=>88));
						}
						foreach ($_POST['reports'][$shop_id] as $r) {
							CIBlockElement::SetPropertyValuesEx($r, false, array(349=>$now_time));
						}
						$trans_name = GetMessage("ZAPROS_9");
						$trans_id = AddTransaction(99,$agent_id,$shop_id,$u_id,$now_time,$summ,$_POST['plat'][$shop_id],$trans_name,$packs);
						$arResult['MESSAGE'][] = GetMessage("MESS_PEREVOD", array("#SUMM#"=>CurrencyFormat($summ,"RUU")));
						
					}
					else {
						$arResult['ERRORS'][] = GetMessage("ERR_3");
					}
				}
			}
		}
		$arResult["TITLE"] = GetMessage("TTL_1");
		$arResult["TO_SHOPS"] = TheListOfShops(0,false,true,true);
		$arResult["NAV_STRING"] = $arResult["TO_SHOPS"]["NAV_STRING"];
		unset($arResult["TO_SHOPS"]["NAV_STRING"]);
		foreach ($arResult["TO_SHOPS"] as $k => $v)
		{
			$arResult["TO_SHOPS"][$k]["REPORTS"] = GetListOfReportsShop($k,$agent_array,0,false,false,true);
			$arResult["TO_SHOPS"][$k]["COST_2"] = $arResult["TO_SHOPS"][$k]["SUMM_SHOP"] = $arResult["TO_SHOPS"][$k]["RATE"] = $arResult["TO_SHOPS"][$k]["OTV"] = 0;
			foreach ($arResult["TO_SHOPS"][$k]["REPORTS"] as $r) {
				$arResult["TO_SHOPS"][$k]["COST_2"] = $arResult["TO_SHOPS"][$k]["COST_2"] + $r["COST_2"];
				$arResult["TO_SHOPS"][$k]["SUMM_SHOP"] = $arResult["TO_SHOPS"][$k]["SUMM_SHOP"] + $r["SUMM_ISSUE"] + $r["SUMM_SHOP"];
				$arResult["TO_SHOPS"][$k]["RATE"] = $arResult["TO_SHOPS"][$k]["RATE"] + $r["RATE"];
				$arResult["TO_SHOPS"][$k]["OTV"] = $arResult["TO_SHOPS"][$k]["OTV"] + $r["PROPERTY_STORAGE_VALUE"];
			}
			$arResult["TO_SHOPS"][$k]["TO_SHOP"] = $arResult["TO_SHOPS"][$k]["COST_2"] - $arResult["TO_SHOPS"][$k]["SUMM_SHOP"] - $arResult["TO_SHOPS"][$k]["RATE"] - $arResult["TO_SHOPS"][$k]["OTV"];
		}
	}
	
	/*****************оплата по отчету*****************/
	if ($mode == 'pay_shop')
	{
		if (isset($_POST['money_to_shop']))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
				if (!isset($_POST['report_id']))
				{
					$arResult["ERRORS"][] = 'Не выбран отчет';
				}
				if (!strlen($_POST['date_plat']))
				{
					$arResult["ERRORS"][] = 'Не введена дата оплаты';
				}
				if (count($arResult["ERRORS"]) == 0)
				{
					$report_id = $_POST['report_id'];
					$summ = $_POST['summ'][$report_id];
					$shop_id = $_POST['shop'][$report_id];
					$at_upr_now = GetAccount($agent_array['id']);
					$at_upr_new = $at_upr_now - $summ;
					if ($_POST['yes'] == 1)
					{
						$at_shop_now = GetAccount($shop_id);
						$at_shop_new = $at_shop_now + $summ;
					}
					/*$repInfo = GetListOfReportsShop($shop_id, $agent_array, $report_id);*/
					$repInfo = GetOneReport($report_id);
					$arPacks = array();
					foreach ($repInfo['PACKS'] as $k => $v)
					{
						$arPacks[] = $k;
					}
					$true = ($_POST['yes'] == 1) ? true : false;
					$buh_state =  ($_POST['yes'] == 1) ? 63 : 88;
					$trans_id = AddTransaction(99, $agent_array['id'], $shop_id, $u_id, $_POST['date_plat'].' 00:00:00', $summ, $_POST['plat'], GetMessage("ZAPROS_9"), $arPacks, $true);
					foreach ($arPacks as $p)
					{
						CIBlockElement::SetPropertyValuesEx($p, 42, array(218 => $buh_state));
					}
					CIBlockElement::SetPropertyValuesEx($report_id, 67, array(349 => $_POST['date_plat'], 480 => $trans_id));
					CIBlockElement::SetPropertyValuesEx($agent_array['id'], 40, array(219 => $at_upr_new));
					if ($_POST['yes'] == 1)
					{
						CIBlockElement::SetPropertyValuesEx($shop_id, 40, array(219 => $at_shop_new));
					}
					if ($_POST['send'] == 1)
					{
						$params = array(
							'DATE_RES' => $_POST['date_plat'],
							'SUMM' => CurrencyFormat($summ,"RUU"),
							'PLAT' => $_POST['plat'],
							'LINK' => '/payments/',
							'LINK_TO_MSG' => ''
						);
						$qw = SendMessageInSystem($u_id, $agent_array['id'], $shop_id, GetMessage("NAME_OF_MESS"), 84, '', '', 167, $params);
						$params['LINK_TO_MSG'] = '<p><a href="http://dms.newpartner.ru/messages/index.php?mode=detail&id='.$qw.'">Ссылка на данное сообщение в системе DMS</a></p>';
						SendMessageMailNew($shop_id, $agent_array['id'], 94, 167, $params);
						$arResult['MESSAGE'][] = GetMessage("MESS_PEREVOD", array("#SUMM#"=>CurrencyFormat($summ,"RUU")));
					}
					else
					{
						$arResult['MESSAGE'][] = 'Транзакция на сумму '.CurrencyFormat($summ,"RUU").' успешно проведена';
					}
				}
			}
		}
		$arAllShopsOfUk = TheListOfShops($id_c, true, false, false, '', $agent_array['id']);
		$arAllShopIdssOfUk = array();
		foreach ($arAllShopsOfUk as $k => $v)
		{
			$arAllShopIdssOfUk[] = $k;
		}
		if (intval($_GET['shop']) > 0)
		{
			if (in_array(intval($_GET['shop']), $arAllShopIdssOfUk))
			{
				unset($arAllShopIdssOfUk);
				$arAllShopIdssOfUk = array(intval($_GET['shop']));
			}
			else
			{
				unset($arAllShopIdssOfUk);
			}
		}
		$arResult['REPORTS_SUMM'] = 0;
		if (count($arAllShopIdssOfUk) > 0)
		{
			$arResult['REPORTS'] = GetListOfReportsShopLight($arAllShopIdssOfUk, $agent_array, 0, true, false, true);
			$arResult["NAV_STRING"] = $arResult['REPORTS']['NAV_STRING'];
			unset($arResult['REPORTS']['NAV_STRING']);
			$arResult['REPORTS_SUMM'] = GetSummReportsShopLight($arAllShopIdssOfUk, $agent_array, 0, false, false, true);
		}
		$arResult['UK_ID'] = $agent_array['id'];
	}
	
	if ($mode == 'pay_shop_xls')
	{
		$arAllShopsOfUk = TheListOfShops($id_c, true, false, false, '', $agent_array['id']);
		$arAllShopIdssOfUk = array();
		foreach ($arAllShopsOfUk as $k => $v)
		{
			$arAllShopIdssOfUk[] = $k;
		}
		if (intval($_GET['shop']) > 0)
		{
			if (in_array(intval($_GET['shop']), $arAllShopIdssOfUk))
			{
				unset($arAllShopIdssOfUk);
				$arAllShopIdssOfUk = array(intval($_GET['shop']));
			}
			else
			{
				unset($arAllShopIdssOfUk);
			}
		}
		if (count($arAllShopIdssOfUk) > 0)
		{
			$arResult["TITLE"] = 'Платежи за товары на '.date('d.m.Y');
			$arResult['REPORTS'] = GetListOfReportsShopLight($arAllShopIdssOfUk, $agent_array, 0, false, false, true);
		}
		else
		{
			LocalRedirect('/payments/index.php?mode=pay_shop');
		}
	}
	
	/*
	if ($mode == 'detail_shop')
	{
		if ((intval($_GET['shop_id']) > 0) && (intval($_GET['operation']) > 0))
		{
			$arResult["PACKS"] = GetPacksInAccount(intval($_GET['shop_id']),intval($_GET['operation']));
			$name_of_t = "TTL_2_".intval($_GET['operation']);
			$t = GetMessage($name_of_t);
			$shop_info = GetAgentInfo(intval($_GET['shop_id']));
			$arResult["TITLE"] = GetMessage("TTL_2",array("#T#"=>$t,"#SHOP#"=>intval($_GET['shop_id']),"#NAME#"=>$shop_info["NAME"]));
			$t.'<a href="/shops/index.php?mode=shop&id='.intval($_GET['shop_id']).'">'.$shop_info["NAME"].'</a>", расшифровка';
			$arResult["NAV_STRING"] = $arResult["PACKS"]["NAV_STRING"];
			unset($arResult["PACKS"]["NAV_STRING"]);
		}
	}
	
	if ($mode == 'print_report')
	{
		if (intval($_GET['shop_id']) > 0)
		{
			$arResult["PACKS"] = GetPacksInAccount(intval($_GET['shop_id']),62,false);
			$arResult['agent_info'] = GetCompany(intval($_GET['shop_id']));
		}
	}
	*/
	
	/****Подтверждение поступления денежных средств****/
	if ($mode == 'confirm')
	{
		if (isset($_POST['conf_money'])) {
			$summ = 0;
			$packs_array = array();
			$now = GetAccount($agent_id);
			foreach ($_POST['trans'] as $tr) {
				$trans_info_arr = GetTransactions(0,0,0,$tr);
				$trans_info = $trans_info_arr[0];
				$ag_id = $trans_info["PROPERTY_FROM_VALUE"];
				foreach ($trans_info["PACKS"] as $p) {
					CIBlockElement::SetPropertyValuesEx($p, false, array(218=>62));
					$packs_array[] = $p;
				}
				$summ = $summ + $trans_info["PROPERTY_SUMM_VALUE"];
				CIBlockElement::SetPropertyValuesEx($tr, false, array(269=>106));
			}
			$now = $now + $summ;
			$trans_id = AddTransaction(101,$ag_id,$agent_id,$u_id,date('d.m.Y H:i:s'),$summ,$_POST['PAYMENT_ORDER'][$tr],GetMessage("ZAPROS_7"),$packs_array,true);
			$arResult['MESSAGE'][] = GetMessage("MESS_PODTV", array("#ID#" => $ag_id));
			CIBlockElement::SetPropertyValuesEx($agent_id, false, array(219=>$now));
		}	
		$arResult["TITLE"] = GetMessage("TTL_17");
		$arResult['TRANS'] = GetTransactions(0,$agent_id,99);
		$arResult["NAV_STRING"] = $arResult['TRANS']["NAV_STRING"];
		unset($arResult['TRANS']["NAV_STRING"],$arResult['TRANS']["COUNT"]);
	}
	
	/****************Список транзакций*****************/
	if ($mode == 'transactions_in')
	{
		$from = intval($_GET['from']);
		$to = intval($_GET['to']);
		$type = intval($_GET['type']);
		$arResult["TITLE"] = GetMessage("TTL_12");
		$arResult['TRANS'] = GetListTransactions($agent_array['id'],'in');
		$arResult["NAV_STRING"] = $arResult['TRANS']["NAV_STRING"];
		unset($arResult['TRANS']["NAV_STRING"],$arResult['TRANS']["COUNT"]);
	}
	
	if ($mode == 'transactions_out')
	{
		$from = intval($_GET['from']);
		$to = intval($_GET['to']);
		$type = intval($_GET['type']);
		
		$arResult["TITLE"] = GetMessage("TTL_20");
		$arResult['TRANS'] = GetListTransactions($agent_array['id'],'out', $_GET['date_from'], $_GET['date_to']);
		$arResult["NAV_STRING"] = $arResult['TRANS']["NAV_STRING"];
		unset($arResult['TRANS']["NAV_STRING"],$arResult['TRANS']["COUNT"]);
	}
	
	/************Список заказов транзакции*************/
	if ($mode == 'detail_agent')
	{
		$trans_id = intval($_GET['trans']);
		$arResult["TITLE"] = GetMessage("TTL_13",array("#ID#"=>$trans_id));
		$trans = $info = array();
		$trans = GetTransactions(0,0,0,$trans_id);
		if (count($trans) > 0)
		{
			$info = GetListOfPackeges($agent_array, 0, $trans[0]["PACKS"]);
			$arResult["NAV_STRING"] = $info["NAV_STRING"];
			unset($info["NAV_STRING"],$info["COUNT"]);
		}
		$arResult["PACKS"] = $info;
		
	}

	/*
	if ($mode == 'commission') {
		if (isset($_POST['money_to_agent'])) {
			if (!strlen($_POST['plat'])) $arResult['ERRORS'][] = GetMessage("ERR_1");
			if (count($_POST['agent_id']) <= 0) $arResult["ERRORS"][] = GetMessage("ERR_4");
			if (count($arResult['ERRORS']) == 0) {
				$now = GetAccount($agent_id);
				foreach ($_POST['agent_id'] as $ag_id) {
					$trans_ar = $_POST["trans_ids"][$ag_id]["VOZN_1"];
					$summ = $_POST['summ'][$ag_id]["VOZN_1"];
					$packs_array = array();
					foreach ($trans_ar as $tr) {
						$trans_info_arr = GetTransactions(0,0,0,$tr);
						$trans_info = $trans_info_arr[0];
						foreach ($trans_info["PACKS"] as $p) {
							CIBlockElement::SetPropertyValuesEx($p, false, array(257=>92));
							$packs_array[] = $p;
						}
						CIBlockElement::SetPropertyValuesEx($tr, false, array(269=>106));
					}
					$now = $now - $summ;
					$trans_id = AddTransaction(99,$agent_id,$ag_id,$u_id,date('d.m.Y H:i:s'),$summ,$_POST['plat'],GetMessage("ZAPROS_8"),$packs_array);
					$el = new CIBlockElement;
					$arLoadProductArray = Array(
						"MODIFIED_BY"    => $u_id,
  						"IBLOCK_ID"      => 50,
						"DETAIL_TEXT" => $_POST['plat'],
  						"PROPERTY_VALUES"=> array(234=>2197189,235=>$ag_id,236=>84,242=>CurrencyFormat($summ,"RUU")), "NAME" => GetMessage("NAME_OF_MESS"));
					$PRODUCT_ID = $el->Add($arLoadProductArray);
					SendMessageMail($ag_id,94,$PRODUCT_ID,GetMessage("ZAPROS_4", array("#SUMM#" => CurrencyFormat($summ,"RUU"))));
					$arResult['MESSAGE'][] =  GetMessage("MESS_PEREVOD", array("#SUMM#" => CurrencyFormat($summ,"RUU")));
				}
				CIBlockElement::SetPropertyValuesEx($agent_id, false, array(219=>$now));
			 }
		}
		
		$arResult["TITLE"] = GetMessage("TTL_5");
		$arResult["AGENTS"] = TheListOfAgents(0,true);
		$arResult["I"] = $agent_id;
		$arResult["VOZN_1"] = $arResult["VOZN_2"] = 0;
		$arResult["TRANS"] = array();
		foreach ($arResult["AGENTS"] as $ag_arr) {
			$summ = 0;
			$idd = $ag_arr["ID"];
			$sobrano_arr = GetTransactions($idd,$agent_id,101);
			foreach ($sobrano_arr as $tr) {
				$packs = $tr["PACKS"];
				$info = GetListOfPackeges($agent_array, 0, $packs);
				unset($info["COUNT"],$info["NAV_STRING"]);
				foreach ($info as $p) {
					$summ = $summ + $p['PROPERTY_SUMM_AGENT_VALUE'] + $p['PROPERTY_RATE_AGENT_VALUE'];
				}
				$arResult["TRANS"][$idd]["VOZN_1"][] = $tr["ID"];
			}
			$arResult["VOZN"][$idd] = $summ;
			$arResult["VOZN_1"] = $arResult["VOZN_1"] + $arResult["VOZN"][$idd];
			$otpr_arr = GetTransactions($agent_id,$idd,99);
			$summ = 0;
			foreach ($otpr_arr as $tr) {
				$packs = $tr["PACKS"];
				$info = GetListOfPackeges($agent_array, 0, $packs);
				unset($info["COUNT"],$info["NAV_STRING"]);
				foreach ($info as $p) {
					$summ = $summ + $p['PROPERTY_SUMM_AGENT_VALUE'] +  $p['PROPERTY_RATE_AGENT_VALUE'];
				}
				$arResult["TRANS"][$idd]["VOZN_2"][] = $tr["ID"];
			}
			$arResult["VOZN_OTPR"][$idd] = $summ;
			$arResult["VOZN_2"] = $arResult["VOZN_2"] + $arResult["VOZN_OTPR"][$idd];
		}
	}
	*/
	
	/******************Текущий период******************/
	if ($mode == 'periods')
	{
		if (isset($_POST['clode_period'])) {
			if ($res_close_period = ClosePeriod()) {
				foreach ($_POST['packs'] as $agent => $packs_array) {
					foreach ($packs_array as $p) {
						$trs = GetTransactions(0,$agent,101,0,$p);
						foreach ($trs as $tr) {
							CIBlockElement::SetPropertyValuesEx($tr["ID"], false, array(269=>106));
						}
						CIBlockElement::SetPropertyValuesEx($p, false, array(218=>85));
					}
					/*
					$el = new CIBlockElement;
					$name = GetMessage("ZAPROS_1");
					$arLoadProductArray = Array(
						"MODIFIED_BY"    => $u_id,
  						"IBLOCK_ID"      => 50,
  						"PROPERTY_VALUES"=> array(234=>$agent_id,235=>$agent,236=>82,242=>CurrencyFormat($_POST['summ'][$agent],"RUU")), "NAME" => $name
					);
					$PRODUCT_ID = $el->Add($arLoadProductArray);
					*/
					$body = GetMessage("ZAPROS_2",array("#SUMM#"=>CurrencyFormat($_POST['summ'][$agent],"RUU")));
					$PRODUCT_ID = SendMessageInSystem($u_id, $agent_id, $agent, GetMessage("ZAPROS_1"), 82, $body, CurrencyFormat($_POST['summ'][$agent],"RUU"));
					SendMessageMail($agent,98,$PRODUCT_ID,$body);
					$trans_name = GetMessage("ZAPROS_3");
					$trans_id = AddTransaction(100,$agent_id,$agent,$u_id,date('d.m.Y H:i:s'),$_POST['summ'][$agent],'',$trans_name,$packs_array);
					$arResult['MESSAGE'][] = GetMessage("MESS_ZAPROS_AGENT", array("#ID#" => $agent,"#SUMM#"=>CurrencyFormat($_POST['summ'][$agent],"RUU")));
				}
				
				$arResult["MESSAGE"][] = GetMessage("MESS_CLOSE");
			}
			else {
				$arResult["ERRORS"][] = GetMessage("MESS_CLOSE_ERR");
			}
			
		}
		$now = GetOpenPeriod();
		$info = GetInfoOfPeriod($now);
		$arResult["TITLE"] = GetMessage("TTL_16",array("#N#"=>$info["NAME"]));
		$arResult["NAME_PERIOD"] = $info["NAME"];
		$arResult["ID_PERIOD"] = $info["ID"];
		$arResult["CLOSED"] = true;
		if (strlen($info["END"]))
		{
			$arResult["DATES"] = GetMessage("MESS_CLOSE",array("#DATE_1#"=>$info["START"],"#DATE_2#"=>$info["END"]));
		}
		else
		{
			$arResult["DATES"] = GetMessage("TTL_PERIOD_OPEN",array("#DATE_1#"=>$info["START"]));
			$arResult["CLOSED"] = false;
		}
		$arResult["COUNT_START"] = count($ar_res["COUNT_START"]);
		$arResult["ADOPTED"] = $ar_res["ADOPTED"];
		$arResult["DELIVERED"] = $ar_res["DELIVERED"];
		$arResult["COUNT_END"] = count($ar_res["COUNT_END"]);
		$arResult["AGENTS"] = AvailableAgents(true, $agent_array['id']);
		$ag_array = array();
		foreach ($arResult["AGENTS"] as $ag_id => $v)
		{
			$ag_array[] = $ag_id;
		}
		$AGENTS_COUNT_END = GetAgentsOfPacksAndPrices($info["COUNT_END"],$ag_array);
		$arResult["AGENTS_COUNT_END"] = $AGENTS_COUNT_END['ids'];
		$arResult["AGENTS_COUNT_END_SUMM"] = $AGENTS_COUNT_END['summ'];
		$AGENTS_DELIVERED = GetAgentsOfPacksAndPrices($info["DELIVERED"],$ag_array);
		$arResult["AGENTS_DELIVERED"] = $AGENTS_DELIVERED['ids'];
		$arResult["AGENTS_DELIVERED_SUMM"] = $AGENTS_DELIVERED['summ'];
		$arResult["AGENTS_DELIVERED_SUMM_AGENT"] = $AGENTS_DELIVERED['summ_to_agent'];
		$AGENTS_COUNT_START = GetAgentsOfPacksAndPrices($info["COUNT_START"],$ag_array);
		$arResult["AGENTS_COUNT_START"] = $AGENTS_COUNT_START['ids'];
		$arResult["AGENTS_COUNT_START_SUMM"] = $AGENTS_COUNT_START['summ'];
		$arResult["AGENTS_ADOPTED"] = $info["ADOPTED"];
		$arResult["AGENTS_ADOPTED_SUMM"] = GetSummOfPacks($info["ADOPTED"]);
		$arResult["IT_1"] = count($info["COUNT_START"]);
		$arResult["IT_2"] = $arResult["IT_5"] = $arResult["IT_6"] = 0;
		foreach ($arResult["AGENTS_COUNT_START_SUMM"] as $v)
			$arResult["IT_2"] = $arResult["IT_2"] + array_sum($v);
		$arResult["IT_3"] = count($info["ADOPTED"]);
		$arResult["IT_4"] = count($info["DELIVERED"]);
		foreach ($arResult["AGENTS_DELIVERED_SUMM"] as $v)
			$arResult["IT_5"] = $arResult["IT_5"] + array_sum($v);
		foreach ($arResult["AGENTS_DELIVERED_SUMM_AGENT"] as $v)
			$arResult["IT_6"] = $arResult["IT_6"] + array_sum($v);
		$arResult["IT_7"] = $arResult["IT_5"]  - $arResult["IT_6"];
	}
}


/**************************************************/
/************************ИМ************************/
/**************************************************/

if ($agent_array['type'] == 52) {
	$modes = array(
		"detail",
		"transaction"
	);
	$arResult["MENU"] = array(
		'transaction' => GetMessage("TTL_15")
	);
	/**************убираем закрытое меню***************/
	foreach ($arResult["MENU"] as $m => $name)
	{
		if ($arParams['PERM'][$agent_array['type']][$m][$arResult['ROLE_USER']] == "C")
			unset($arResult["MENU"][$m]);
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
		$componentPage = "shop_".$mode;
		unset($arResult["MENU"][$mode]);
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
	
	/********************Транзакция********************/
	if ($mode == 'detail') {
		if (intval($_GET['transaction']) > 0) {
			$tr = intval($_GET['transaction']);
			$arResult["TITLE"] = GetMessage("TTL_7",array("#ID#"=>$tr));
			$trans_info = GetTransactions(0,$agent_id,0,$tr,0,array(),array());
			if (count($trans_info) == 1) {
				$arResult["PACKS"] = GetListOfPackeges($agent_array, 0, $trans_info[0]["PACKS"], false, false, 0, false, 0, 0,'','',0,false);
			}
			else {
				$arResult["TITLE"] = GetMessage("TTL_18",array("#ID#"=>$_GET['transaction']));
			}
		}
	}
	
	/****************Список транзакций*****************/
	if ($mode == 'transaction') {
		if (isset($_POST['confirm_money'])) {
			foreach ($_POST['trans'] as $tr) {
				CIBlockElement::SetPropertyValuesEx($tr, false, array(269=>106));
				foreach ($_POST['packs'][$tr] as $pp) {
					CIBlockElement::SetPropertyValuesEx($pp, false, array(218=>63));
				}
				$arResult['MESSAGE'][] = GetMessage("MESS_PODTV_2",array("#SUMM#"=>CurrencyFormat($_POST['summ'][$tr],"RUU")));
			}
		}
		$arResult["TITLE"] = GetMessage("TTL_15");
		$arResult["TRANSACTIONS"] = GetListTransactions($agent_id,'in',$_GET['date_from'],$_GET['date_to']);
		$arResult["COUNT"] = $arResult["TRANSACTIONS"]["COUNT"];
		$arResult["NAV_STRING"] = $arResult["TRANSACTIONS"]["NAV_STRING"];
		unset($arResult["TRANSACTIONS"]["NAV_STRING"],$arResult["TRANSACTIONS"]["COUNT"]);
	}
}

/**************************************************/
/**********************АГЕНТ***********************/
/**************************************************/

if ($agent_array['type'] == 53)
{
	$modes = array(
		'detail',
		'in',
		'out'
	);
	if (in_array($_GET['mode'],$modes)) $mode = $_GET['mode'];
	else $mode = 'out';
	$arResult["MENU"] = array(
		'out' => GetMessage("TTL_11")
	); 
	unset($arResult["MENU"][$mode]);
	$componentPage = "agent_".$mode;
	
	/*************Запросы денежных средств*************/
	if ($mode == 'out')
	{
		if (isset($_POST['give_money'])) {
			if (count($_POST['transactions']) <= 0) $arResult["ERRORS"][] = GetMessage("ERR_NO_TRANS");
			else {
				if (!strlen($_POST['plat'])) $arResult["ERRORS"][] = GetMessage("ERR_1");
				else {
					$now = GetAccount($agent_id);
					$minus = 0;
					$all_packs = array();
					foreach ($_POST['transactions'] as $tr) {
						$minus = $minus + $_POST['summ'][$tr];
						$trans_array = GetTransactions(0,0,0,$tr);
						$packs_arr = $trans_array[0]["PACKS"];
						foreach ($packs_arr as $pp) {
							CIBlockElement::SetPropertyValuesEx($pp, false, array(218=>87));
							$all_packs[] = $pp;
						}
						CIBlockElement::SetPropertyValuesEx($tr, false, array(269=>106));
					}
					$trans_id = AddTransaction(99,$agent_id,2197189,$u_id,date('d.m.Y H:i:s'),$minus,$_POST['plat'],GetMessage("ZAPROS_6"),$all_packs);
					$now = $now - $minus;
					CIBlockElement::SetPropertyValuesEx($agent_id, false, array(219=>$now));
					$el = new CIBlockElement;
					$arLoadProductArray = Array(
						"MODIFIED_BY"    => $u_id,
						"IBLOCK_ID"      => 50,
						"DETAIL_TEXT" => $_POST['plat'],
						"PROPERTY_VALUES"=> array(
							234=>$agent_id,
							235=>2197189,
							236=>84,
							242=>CurrencyFormat($minus,"RUU")
						),
						"NAME" => GetMessage("ZAPROS_5")
					);
					$PRODUCT_ID = $el->Add($arLoadProductArray);
					SendMessageMail(2197189,94,$PRODUCT_ID,GetMessage("ZAPROS_4",array("#SUMM#"=>CurrencyFormat($minus,"RUU"))));
					$arResult['MESSAGE'][] =  GetMessage("MESS_PEREVOD", array("#SUMM#"=>CurrencyFormat($minus,"RUU")));
				}
			}
		}
		$arResult["I"] = $ag_id;
		$arResult["TRANS"] = GetTransactions(0,$agent_id,100);
		$arResult["TITLE"] = GetMessage("TTL_11");
	}
	
	/***********Поступления денежных средств***********/
	/*
	if ($mode == 'in') {
		if (isset($_POST['confirm_money'])) {
			$now = GetAccount($agent_id);
			$trans_ar = $_POST["transactions"];
			$summ = 0;
			$packs_array = array();
			foreach ($trans_ar as $tr) {
				$trans_info_arr = GetTransactions(0,0,0,$tr);
				$trans_info = $trans_info_arr[0];
				foreach ($trans_info["PACKS"] as $p) {
					CIBlockElement::SetPropertyValuesEx($p, false, array(257=>93));
					$packs_array[] = $p;
				}
				$summ = $summ + $_POST['summ'][$tr];
				CIBlockElement::SetPropertyValuesEx($tr, false, array(269=>106));
			}
			$now = $now + $summ;
			$arResult['MESSAGE'][] = GetMessage("MESS_PODTV_2");
			CIBlockElement::SetPropertyValuesEx($agent_id, false, array(219=>$now));
		}
		
		
		$arResult["I"] = $ag_id;
		$arResult["TRANS"] = GetTransactions(0,$agent_id,99);
		$arResult["TITLE"] = GetMessage("TTL_10");
	}
	*/
	
	/*******************Расшифровка********************/
	if ($mode == 'detail')
	{
		$trans_id = intval($_GET['trans']);
		$arResult["TITLE"] = GetMessage("TTL_14");
		$trans = GetTransactions(0,0,0,$trans_id);
		if (count($trans[0]["PACKS"]) > 0)
		{
			$info = GetListOfPackeges($agent_array, 0, $trans[0]["PACKS"]);
			$arResult["NAV_STRING"] = $info["NAV_STRING"];
			unset($info["NAV_STRING"],$info["COUNT"]);
			$arResult["PACKS"] = $info;
		}
	}
}

$arResult["ACCOUNT"] = GetAccount($agent_id); 

$this->IncludeComponentTemplate($componentPage);
?>