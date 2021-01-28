<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
include_once("functions.php");

CModule::IncludeModule('iblock');
CModule::IncludeModule("currency");
$u_id = $USER->GetID();
$arResult["CURRENT_USER"] = $u_id;
$agent_array = GetCurrentAgent($u_id);
if($USER->isAdmin()){
    //dump($agent_array);
}
//dump($agent_array);
$agent_id = $agent_array['id'];
$arResult["CURRENT_COMPANY"] = $agent_id;
$componentPage = "blank";

if (isset($arParams['PERM'][$agent_array['type']]['ALL']))
{
	$arResult["PERM"] = $arParams['PERM'][$agent_array['type']]['ALL'];
}

if ($arResult["PERM"] == "C")
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$arResult["ERRORS"] = array();

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

$arResult['ROLE_USER'] = GetRoleOfUser($u_id);

/************Параметры авторизации в TD************/
$TDsoapLink = GetSettingValue(673);
$TDsoapLogin = GetSettingValue(674);
$TDsoapPass = GetSettingValue(675);
$TDlogin = GetSettingValue(676);
$TDpass = GetSettingValue(677);
/**************************************************/

/*************************************************/
/************************УК***********************/
/*************************************************/

if ($agent_array['type'] == 51)
{
	$modes = array(
		'to_overhead',
		'distribution',
		'warehouse',
		'archive',
		'package',
		'makepackage',
		'makepackage_list',
		'formation',
		'print_labels',
		'package_print',
		'print_fence_supplier',
		'pods',
		'register',
		'package_edit',
		'package_manifest',
		'to_topdelivery',
		'returns',
		'search',
		'in_topdelivery',
		'archive_topdelivery'
	);
	$arResult["MENU"] = array(
		'to_overhead' => GetMessage("UK_MENU_1"),
		'distribution' => GetMessage("UK_MENU_2"),
		'to_topdelivery' => GetMessage("UK_MENU_8"),
		'warehouse' => GetMessage("UK_MENU_3"),
		'formation' => GetMessage("UK_MENU_4"),
		'makepackage_list' => GetMessage("UK_MENU_5"),
		'pods' => GetMessage("UK_MENU_6"),
		'in_topdelivery' => GetMessage("UK_MENU_10"),
		'returns' => GetMessage("UK_MENU_9"),
		'archive' => GetMessage("UK_MENU_7"),
		'archive_topdelivery' => 'Архив TopDelivery'
	);
	foreach ($arResult["MENU"] as $m => $name)
	{
		if ($arParams['PERM'][$agent_array['type']][$m][$arResult['ROLE_USER']] == "C")
		{
			unset($arResult["MENU"][$m]);
		}
	}
	if (in_array($_GET['mode'],$modes))
	{
		$mode = $_GET['mode'];
	}
	else
	{
		if ($arParams['MODE'])
		{
			$mode = $arParams['MODE'];
		}
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
	
	/*****************принятие заказов*****************/
	if ($mode == 'to_overhead')
	{
		if (isset($_POST["departure_packages"]))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST['key_session']] = $_POST['rand'];
				foreach ($_POST['id'] as $k => $id)
				{
					$massiv_to_change = array();
					$massiv_to_change[232] = intval($_POST['places'][$id]);
					$changes = false;
					if (
						(str_replace(',','.',$_POST['weight'][$id]) != $_POST['weight_old'][$id]) || 
						(str_replace(',','.',$_POST['size_1'][$id]) != $_POST['size_1_old'][$id]) || 
						(str_replace(',','.',$_POST['size_2'][$id]) != $_POST['size_2_old'][$id]) || 
						(str_replace(',','.',$_POST['size_3'][$id]) != $_POST['size_3_old'][$id])
					)
					{
						$changes = true;
					}
					
					if ((intval($_POST['city'][$id]) == $agent_array["city"]) && ($_POST['conditions'][$id] == 38))
					{
						$state_full = 79;
						$state_short = 74;
					}
					else
					{
						$state_full = 56;
						$state_short = 67;
					}
					
					/*
					if (intval($_POST['city'][$id]) == $agent_array["city"])
					{
						if ($_POST['conditions'][$id] == 37)
						{
							$state_full = 56;
							$state_short = 67;
						}
						else
						{
							$state_full = 79;
							$state_short = 74;
						}
					}
					else
					{
						$state_full = 56;
						$state_short = 67;
					}
					*/
					
					
					if ($changes)
					{
						if ($_POST['conditions'][$id] == 38)
						{
							$pr = WhatIsPrice($_POST['shop'][$id], 2);
						}
						else
						{
							$pr = WhatIsPrice($_POST['shop'][$id]);
						}
						$have_city = CheckCityToHave(
							$_POST['city'][$id],
							$pr,
							str_replace(',','.',$_POST['weight'][$id]),
							str_replace(',','.',$_POST['size_1'][$id]),
							str_replace(',','.',$_POST['size_2'][$id]),
							str_replace(',','.',$_POST['size_3'][$id]),
							$_POST['urgency'][$id]
						);
						if (!$have_city["LOG"])
						{
							$arResult['ERRORS'][] = GetMessage("ERR_EDIT_6", array("#ID#" => $id, "#NUMBER#" => $_POST['number'][$id]));
						}
						else
						{
							$massiv_to_change[225] = str_replace(',','.',$_POST['weight'][$id]);
							$massiv_to_change[247] = str_replace(',','.',$_POST['size_1'][$id]);
							$massiv_to_change[248] = str_replace(',','.',$_POST['size_2'][$id]);
							$massiv_to_change[249] = str_replace(',','.',$_POST['size_3'][$id]);
							$massiv_to_change[250] = $have_city["COST"];
							$massiv_to_change[203] = $state_full;
							$massiv_to_change[229] = $state_short;
							$massiv_to_change[499] = $agent_array['id'];
							CIBlockElement::SetPropertyValuesEx($id, 42, $massiv_to_change);
							$add_to_period = AddElementToPeriod($id, 283, $u_id);
							$history_id = AddToHistory($id,$agent_id,$u_id, $state_full, '', $_POST['date_departure'][$id]);
							$short_history_id = AddToShortHistory($id,$u_id, $state_short, '', $_POST['date_departure'][$id]);
							$arResult['MESSAGE'][] = GetMessage("MESS_PACK_CHANGE_WEIGHT", array(
								"#ID#" => $id, 
								"#NUMBER#" => $_POST['number'][$id], 
								"#SUMM#" => CurrencyFormat($have_city["COST"],"RUU")
							));
							$arResult['MESSAGE'][] = GetMessage("MESS_PACK_ACCEPT", array("#ID#" => $id, "#NUMBER#" => $_POST['number'][$id]));
						}
					}
					else
					{
						$massiv_to_change[203] = $state_full;
						$massiv_to_change[229] = $state_short;
						$massiv_to_change[499] = $agent_array['id'];
						CIBlockElement::SetPropertyValuesEx($id, 42, $massiv_to_change);
						$add_to_period = AddElementToPeriod($id, 283, $u_id);
						$history_id = AddToHistory($id,$agent_id,$u_id, $state_full,'',$_POST['date_departure'][$id]);
						$short_history_id = AddToShortHistory($id,$u_id, $state_short, '', $_POST['date_departure'][$id]);
						$arResult['MESSAGE'][] = GetMessage("MESS_PACK_ACCEPT", array("#ID#" => $id, "#NUMBER#" => $_POST['number'][$id]));
					}
				}
			}
		}
		
		$arResult["TITLE"] = GetMessage("TTL_ACCEPTANCE_SHIPMENTS");
		if (intval($_GET['shop']) > 0)
		{
			$arShopsIDs = intval($_GET['shop']);
		}
		else
		{
			$arShops = TheListOfShops(0, false, true, false, '', $agent_array["id"]);
			$arShopsIDs = array();
			foreach ($arShops as $s)
			{
				$arShopsIDs[] = $s["ID"];
			}
		}
		$number_order = strlen($_GET['number']) ? $_GET['number'] : false;
		$arResult["LIST"] = GetListOfPackeges($agent_array, $arShopsIDs, 0, array(39,54,118), false, 0, false, 0, 0, '', '', 0, true, false, array("PROPERTY_DATE_TO_DELIVERY"=>"ASC"), 0, 0, $number_order);
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"],$arResult["LIST"]["COUNT"]);
	}
	
	if ($mode == 'search')
	{
		if (strlen(trim($_GET['number'])))
		{
			$n_id = GetIDPackageByNumber(trim($_GET['number']));
			if ($n_id)
			{
				LocalRedirect("/warehouse/index.php?mode=package&id=".$n_id);
			}
			else
			{
				LocalRedirect("/warehouse/index.php?mode=archive&number=".trim($_GET['number']));
			}
		}
		else
		{
			LocalRedirect("/warehouse/index.php?mode=archive");
		}
	}

	/**************заказы на формировании**************/
	if ($mode == 'formation')
	{
		// echo '<pre>';
		// print_r($agent_array);
		// echo '</pre>';
		if (isset($_POST['submit_for_delivery']))
		{
			if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
			{
				$_POST = array();
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST['key_session']] = $_POST['rand'];
				foreach ($_POST['id'] as $k => $id)
				{
					$secss_or_not = WriteOffOfGoods($id);
					if ($secss_or_not['result'])
					{
						if ((in_array($_POST['city'][$id], $agent_array['city'])) && ($_POST['conditions'][$id] == 38))
						{
							$state_short = 74;
							$state_full = 79;
						}
						else
						{
							$state_full = 56;
							$state_short = 67;
						}
						
						/*
						if (intval($_POST['city'][$id]) == 8054)
						{
							if ($_POST['conditions'][$id] == 37)
							{
								$state_short = 71;
								$state_full = 40;
							}
							else
							{
								$state_short = 74;
								$state_full = 79;
							}
						}
						else
						{
							$state_full = 56;
							$state_short = 67;
						}
						*/
						
						$shop_id = $_POST['shop_id'][$id];
						$shop_info = GetCompany($_POST['shop_id'][$id]);
						$cost_formation = ($_POST['goods_count'][$id] > 1) ? $shop_info['PROPERTY_COST_ORDERING_VALUE'] : 0;
						CIBlockElement::SetPropertyValuesEx($id, 42, array(203 => $state_full, 229 => $state_short, 347 => $cost_formation, 499 => $agent_array['id']));
						$add_to_period = AddElementToPeriod($id, 283, $u_id);
						$history_id = AddToHistory($id,$agent_id,$u_id, $state_full, GetMessage("ORDER_FORMED"));
						$short_history_id = AddToShortHistory($id,$u_id, $state_short ,GetMessage("ORDER_FORMED"));
						$date_send = date('d.m.Y H:i');
						$qw = SendMessageInSystem(
							$u_id, 
							$agent_array['id'], 
							$shop_id, 
							GetMessage("SUBMIT_FOR_DELIVERY_NAME"),
							156,
							GetMessage("SUBMIT_FOR_DELIVERY_TEXT", 
								array(
									"#DATE_SEND#" => $date_send,
									"#ID#" => $id,
									"#NUMBER#" => $_POST['number'][$id]
								)
							),
							''
						);
						SendMessageMailNew($shop_id, $agent_array['id'], 155, 168, array(
							"ID_MESS"=>$qw,
							"ID_ORDER" => $id,
							"NUMBER_ORDER" => $_POST['number'][$id],
							"DATE_SEND" => $date_send
						));
						$arResult['MESSAGE'][] = GetMessage("MESS_PACK_GO_DELIVERY", array("#ID#" => $id, "#NUMBER#" => $_POST['number'][$id]));
					}
					else
					{
						$arResult['ERRORS'][] = GetMessage("ERR_NO_COUNT", array("#IDS#" => implode(', ',$secss_or_not['goodsLess'])));
					}
				}
			}
		}
		
		$arResult["TITLE"] = GetMessage("TTL_FORMATION");
		if (intval($_GET['shop']) > 0)
		{
			$sh = $_GET['shop'];
		}
		else
		{
			$sh = 0;
		}
		$arResult["LIST"] = GetListOfPackeges($agent_array, $sh ,0, 126, false, 0, false, 0, 0, '', '', 0, true, false, array("PROPERTY_DATE_TO_DELIVERY"=>"ASC") );
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"],$arResult["LIST"]["COUNT"]);
		foreach ($arResult["LIST"] as $k => $p)
		{
			$g = 0;
			$arResult["LIST"][$k]['GOODS_LIST'] = GetGoodsOdPack($p["ID"]);
			foreach ($arResult["LIST"][$k]['GOODS_LIST'] as $gg)
			{
				$g = $g + $gg["COUNT"];
			}
			$arResult["LIST"][$k]['GOODS'] = $g;
		}
		$arResult['SHOPS'] = TheListOfShops(0, false, true, false, '', $agent_array['id']);
	}
	
	/*****************печать этикеток******************/
	if ($mode == 'print_labels')
	{
		$ids = $_GET['ids'];
		$ids_array = explode(',',$ids);
		if (count($ids_array))
		{
			$arResult['PACKS'] = GetListOfPackeges($agent_array,0,$ids_array,false,false,0,false,0,0,'','',0,false);
		}
	}

	/*****************квитанция заказа*****************/
	if ($mode == 'package_print')
	{
		$pack_id = intval($_REQUEST['id']);
		$pack = GetListOfPackeges($agent_array, 0, $pack_id, false, false, 0, true);
		$arResult['PACK'] = false;
		if ($pack['COUNT'] == 1)
		{
			$arResult['PACK'] = $pack[0];
			$arResult["PACK"]['GOODS'] = GetGoodsOdPack($arResult['PACK']["ID"]);
			$arResult["PACK"]["SHOP"] = GetCompany($arResult['PACK']['PROPERTY_CREATOR_VALUE']);
			$arResult["PACK"]["AGENT_NAME"] = strlen($agent_array['legal_name']) ? $agent_array['legal_name'] : $agent_array['name'];
			MakeTicketPDF($arResult['PACK'],'D');
		}
	}

	/***********заявка на забор у поставщика***********/
	if ($mode == 'print_fence_supplier')
	{
		$pack_id = intval($_REQUEST['id']);
		$pack = GetListOfPackeges($agent_array,0,$pack_id);
		$arResult['PACK'] = false;
		if ($pack['COUNT'] == 1)
		{
			$arResult['PACK'] = $pack[0];
			$arResult["PACK"]["SHOP"] = GetCompany($arResult['PACK']['PROPERTY_CREATOR_VALUE']);
			$arResult["PACK"]["AGENT_NAME"] = strlen($agent_array['legal_name']) ? $agent_array['legal_name'] : $agent_array['name'];
			MakeSupplierTicketPDF($arResult['PACK'],'D');
		}
	}
	
	/***************заказы на подготовке***************/
	if ($mode == 'makepackage_list')
	{
		if (isset($_POST['delete']))
		{
			if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
			{
				$_POST = array();
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST['key_session']] = $_POST['rand'];
				if (DeleteOrders($_POST['ids']))
				{
					$arResult["MESSAGE"][] = GetMessage("MESS_DELETE");
				}
			}
		}

		$arResult["TITLE"] = GetMessage("TTL_MAKE_PACKAGE_LIST");
		if ($arParams["ONLY_MY_ORDERS"])
		{
			$uu = $u_id;
		}
		else
		{
			$uu = 0;
		}
		$arResult["LIST"] = GetListOfPackeges($agent_array,intval($arParams["SHOP"]),false,116,false,0,false,0, 0,'','',$uu);
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"],$arResult["LIST"]["COUNT"]);
		foreach ($arResult["LIST"] as $k => $p)
		{
			$g = 0;
			$arResult["LIST"][$k]['GOODS_LIST'] = GetGoodsOdPack($p["ID"]);
			foreach ($arResult["LIST"][$k]['GOODS_LIST'] as $gg)
			{
				$g = $g + $gg["COUNT"];
			}
			$arResult["LIST"][$k]['GOODS'] = $g;
		}
	}
	
	/************создание заказа с товарами************/
	if ($mode == 'makepackage')
	{
		$arResult['time_periods'] = array();
		$db_enum_list = CIBlockProperty::GetPropertyEnum(493, array("SORT"=>"asc"), array("IBLOCK_ID"=> 42));
		while($ar_enum_list = $db_enum_list->GetNext())
		{
			$arResult['time_periods'][$ar_enum_list["ID"]] = $ar_enum_list["VALUE"];
		}
		if (isset($_POST['save_package_shop']))
		{
			if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
			{
				$_POST = array();
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST['key_session']] = $_POST['rand'];
				$massiv_to_change = array();
				if (strlen($_POST['PROPERTY_COST_1_VALUE']))
				{
					if (floatval($_POST['PROPERTY_COST_1_VALUE']) < 0)
					{
						$arResult['ERRORS'][] = GetMessage("ERR_STRING_3");
					}
					else
					{
						$massiv_to_change[197] = floatval(str_replace(',','.',$_POST['PROPERTY_COST_1_VALUE']));
					}
				}
				else
				{
					$massiv_to_change[197] = 0;
				}
				if (intval($_POST['PROPERTY_PLACES_VALUE']) < 1)
				{
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_6");
				}
				else
				{
					$massiv_to_change[232] = intval($_POST['PROPERTY_PLACES_VALUE']);
				}
				if (!strlen($_POST['PROPERTY_RECIPIENT_VALUE']))
				{
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_7");
				}
				else
				{
					$massiv_to_change[208] = trim($_POST['PROPERTY_RECIPIENT_VALUE']);
				}
				if (!strlen($_POST['PROPERTY_PHONE_VALUE']))
				{
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_8");
				}
				else
				{
					$massiv_to_change[209] = $_POST['PROPERTY_PHONE_VALUE'];
				}
				if ($_POST['PROPERTY_CONDITIONS_ENUM_ID'] == 0)
				{
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_11");
				}
				else
				{
					$massiv_to_change[201] = $_POST['PROPERTY_CONDITIONS_ENUM_ID'];
				}
				if ($_POST['urgent'] == 2)
				{
					$urgent = 2;
					$urgent_db = 172;
				}
				else
				{
					$urgent = 1;
					$urgent_db =  false;
				}
				$massiv_to_change[376] = $urgent_db;
				if (!strlen($_POST['PROPERTY_CITY']))
				{
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_9");
				}
				if (strlen($_POST['PROPERTY_CITY']) && ($_POST['PROPERTY_CONDITIONS_ENUM_ID'] > 0))
				{
					if ($_POST['PROPERTY_CONDITIONS_ENUM_ID'] == 38)
					{
						$pr = $_POST['price_2'];
					}
					else
					{
						$pr = $_POST['price'];
					}
					$get_city_id = GetCityId($_POST['PROPERTY_CITY']);
					$name_of_city = GetFullNameOfCity($get_city_id);
					$have_city = CheckCityToHave(
						$get_city_id,
						$pr,
						$_POST['PROPERTY_WEIGHT_VALUE'],
						$_POST['PROPERTY_SIZE_1_VALUE'],
						$_POST['PROPERTY_SIZE_2_VALUE'],
						$_POST['PROPERTY_SIZE_3_VALUE'],
						$urgent
					);
					if (!$have_city["LOG"])
					{
						$massiv_to_change[250] = false;
						$massiv_to_change[212] = false;
					}
					else
					{
						$arResult["INFO"] = $have_city["TEXT"];
						$massiv_to_change[250] = $have_city['COST'];
						$massiv_to_change[212] = $get_city_id;
					}
				}
				if (!strlen($_POST['PROPERTY_ADRESS_VALUE']) && ($_POST['PROPERTY_CONDITIONS_ENUM_ID'] == 37))
				{
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_10");
				}
				else 
				{
					$massiv_to_change[202] = $_POST['PROPERTY_ADRESS_VALUE'];
				}
				if (strlen($_POST['PROPERTY_PREFERRED_TIME_VALUE']))
				{
					$massiv_to_change[339] = array("VALUE" => array ("TEXT" => $_POST['PROPERTY_PREFERRED_TIME_VALUE'], "TYPE" => "text"));;
				}
				$massiv_to_change[313] = $_POST['PROPERTY_CASH_VALUE'];
				$pers_key = $have_city['PERSENT_1'];
				if ($massiv_to_change[313] == 125)
				{
					$pers_key = $have_city['PERSENT_2'];
				}
				if (floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_1_VALUE'])) > 0)
					$massiv_to_change[247] = floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_1_VALUE']));
				if (floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_2_VALUE'])) > 0)
					$massiv_to_change[248] = floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_2_VALUE']));
				if (floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_3_VALUE'])) > 0)
					$massiv_to_change[249] = floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_3_VALUE']));
				if (floatval(str_replace(',','.',$_POST['PROPERTY_COST_3_VALUE'])) > 0)
					$massiv_to_change[199] = floatval(str_replace(',','.',$_POST['PROPERTY_COST_3_VALUE']));
				$weighs = $costs = 0;
				foreach ($_POST['count'] as $k => $v)
				{
					if (intval($v) <= 0)
					{
						$c = 1;
					}
					else
					{
						$c = intval($v);
					}
					CIBlockElement::SetPropertyValuesEx($k, 63, array(301 => $c));
					$weighs = $weighs + $_POST["weigh"][$k]*$c;
					$costs = $costs + $_POST["cost"][$k]*$c;
				}
				if (strlen($_POST['PROPERTY_COST_2_VALUE']))
				{
					if (floatval($_POST['PROPERTY_COST_2_VALUE']) <= 0)
					{
						$arResult['ERRORS'][] = GetMessage("ERR_STRING_2");
					}
					else
					{
						$massiv_to_change[198] = floatval(str_replace(',','.',$_POST['PROPERTY_COST_2_VALUE']));
					}
				}
				else
				{
					$massiv_to_change[198] = $costs;
				}
				$massiv_to_change[231] = $_POST['PROPERTY_RATE_VALUE'];
				$massiv_to_change[225] = $weighs;
				$massiv_to_change[307] = $costs;
				$massiv_to_change[390] = '';
				$massiv_to_change[446] = ($_POST["to_legal"] == 1) ? 1 : 0;
				$massiv_to_change[478] = ($_POST["refusal"] == 1) ? 1 : 0;
				$massiv_to_change[493] = $_POST['TIME_PERIOD'];
				if (strlen($_POST['date_deliv']))
				{
					$massiv_to_change[390] = DateFF($_POST['date_deliv']);
				}
				if (strlen($massiv_to_change[390]))
				{
					$massiv_to_change[390] .= ' ';
				}
				$massiv_to_change[390] .= $arResult['time_periods'][$_POST['TIME_PERIOD']];
				$massiv_to_change[196] = trim($_POST['PROPERTY_N_ZAKAZ']);
				CIBlockElement::SetPropertyValuesEx($_POST['pack_id'], 42, $massiv_to_change);
				if ((count($arResult['ERRORS']) == 0) && $massiv_to_change[212] && ($_POST['pack_finish'] == 1))
				{
					$secss_or_not = WriteOffOfGoods($_POST['pack_id']);
					if ($secss_or_not['result'])
					{
						CIBlockElement::SetPropertyValuesEx($_POST['pack_id'], 42, array(203 => 56, 229 => 67));
						$add_to_period = AddElementToPeriod($_POST['pack_id'], 283, $u_id);
						$history_id = AddToHistory($_POST['pack_id'],$agent_id,$u_id,56,'');
						$short_history_id = AddToShortHistory($_POST['pack_id'],$u_id,67);
						LocalRedirect("/warehouse/index.php?mode=distribution");
					}
					else
					{
						$arResult['ERRORS'][] = GetMessage("ERR_NO_COUNT", array("#IDS#" => implode(', ',$secss_or_not['goodsLess'])));
					}
				}
			}
		}
		
		if (isset($_POST['delete_goods']))
		{
			if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
			{
				$_POST = array();
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				foreach ($_POST['id_good_row'] as $del_g)
				{	
					CIBlockElement::Delete($del_g);
				}
				RecalculationWeightAndCost($_POST["pack_id"]);
			}
		}
		
		if (intval($_GET['id']) > 0)
		{
			$pack_id = intval($_GET['id']);
		}
		//dump($pack_id);
		$arResult["TITLE"] = GetMessage("TTL_PACK",array("#ID#"=>$pack_id));
		$pack = GetListOfPackeges($agent_array,0,$pack_id,116);
		if ($pack["COUNT"] > 0)
		{
			$arResult['PACK'] = $pack[0];
			$arResult["TITLE"] = GetMessage("TTL_PACK_OF_SHOP",array("#ID#"=>nZakaz($arResult['PACK']['PROPERTY_N_ZAKAZ_IN_VALUE']),"#SHOP#"=>$arResult['PACK']["PROPERTY_CREATOR_NAME"]));
			$arResult["RATE"] = WhatIsRate($arResult['PACK']["PROPERTY_CREATOR_VALUE"]);
			$arResult["PRICE"] = WhatIsPrice($arResult['PACK']["PROPERTY_CREATOR_VALUE"]);
			$arResult["PRICE_2"] = WhatIsPrice($arResult['PACK']["PROPERTY_CREATOR_VALUE"],2);
			$arResult['PACK']['GOOS'] = GetGoodsOdPack($arResult['PACK']["ID"]);
			$arResult['PACK']['HISTORY'] = HistoryOfPackage($arResult['PACK']["ID"]);
			if (($arResult['PACK']['PROPERTY_STATE_ENUM_ID'] == 116) && ($arResult['PACK']['CREATED_BY'] == $u_id))
			{
				$arResult['EDIT'] = true;
			}
			else
			{
				$arResult['EDIT'] = false;
				LocalRedirect("index.php?mode=package&id=".$arResult['PACK']["ID"]."&back_url=makepackage_list");
			}
			$arResult["SHOP_DEFAULT"] = GetDefaultValuesForShop($arResult['PACK']["PROPERTY_CREATOR_VALUE"]);
			$arResult["LIST_PVZ"] = TheListOfPVZ(0,$arResult['PACK']["PROPERTY_CITY_VALUE"] ,false,0,false);
			$db_props = CIBlockElement::GetProperty(40, $arResult['PACK']["PROPERTY_CREATOR_VALUE"], array("sort" => "asc"), array("ID" => 492));
			$ar_props = $db_props->Fetch();
			$arResult['CONDITIONS_IM'] = $ar_props["VALUE"];
		}
		else
		{
			$arResult['PACK'] = false;
		}
	}
	
	/*************распределение по агентам*************/
	if ($mode == 'distribution')
	{	
		/*
		if (isset($_POST['create_add_to_manifest']))
		{
			if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
			{
				$_POST = array();
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				if (intval($_POST['agent_id']) == 0)
				{
					$arResult["ERRORS"][] = GetMessage("NOT_SELECTED_AGENT");
				}
				else
				{
					$arResult["LIST_PVZ"] = TheListOfPVZ(intval($_POST['agent_id']),0,false,0,false);
				}
				if (count($_POST['pack_id']) == 0)
				{
					$arResult["ERRORS"][] = GetMessage("NOT_SELECTED_PACK");
				}
				if (count($arResult["ERRORS"]) == 0)
				{
					$ag_arr = GetAgentInfo($_POST['agent_id']);
					foreach($_POST['pack_id'] as $pack)
					{
						$change_pvz = false;
						if ($_POST['pvz_id'][$pack] > 0)
						{
							if (!in_array($_POST['pvz_id'][$pack],$arResult["LIST_PVZ"]))
							{
								$arResult["MESSAGE"][] = GetMessage("PVZ_CHANGE", array("#ID#" => $pack, "#NUMBER#" => $_POST['id_in'][$pack]));
								$change_pvz = true;
							}
						}
						$res = CIBlockElement::GetList(array("SORT"=>"ASC"), 
							array(
								"IBLOCK_ID"=>51,
								"ACTIVE"=>"Y",
								"PROPERTY_USER"=>$_POST['agent_id'],
								"PROPERTY_CITIES"=>$_POST['city'][$pack]
							),
							false,
							array("nTopCount"=>1),
							array("ID","PROPERTY_CITIES","PROPERTY_FILE")
						);
						if($ob = $res->GetNextElement())
						{
							$Prices = $ob->GetFields();
						}
						if (count($Prices) < 1)
						{
							$arResult['ERRORS'][] = GetMessage("ERR_NO_CITY_AGENT", array("#ID#" => $_POST["id_in"][$pack], "#CITY#" => $_POST["city_name"][$pack]));
						}
						else
						{
							$flag = true;
							$persent_to_agent = round(floatval($ag_arr["PROPERTY_PERCENT_VALUE"]*$_POST["cost"][$pack]/100),2);
							$summ = 0;
							$path_to_bd = CFile::GetPath($Prices["PROPERTY_FILE_VALUE"]);
							$global_file = $_SERVER["DOCUMENT_ROOT"].$path_to_bd;
							if (is_file($global_file))
							{
								include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/components/black_mist/delivery.price-list/functions.php');
								$html = read_price($global_file);
								$orders = $html["ORDERS"][$_POST['city'][$pack]];
								foreach ($orders as $key => $value)
								{
									$type_of_delivery = $key;
									$ord = $value;
									break;
								}
								$ww = $_POST["weight"][$pack];
								$doc_true = true;
								$start_value = $html["START_VALUE"];
								$start_index = $html["START_INDEX"];
								arsort($html["DOCS"]);
								foreach ($html["DOCS"] as $kk => $vv)
								{
									if ($ww <= $vv) $index_dd = $kk;
								}
								if ($index_dd != '')
								{
									$summ = $ord[$index_dd];
								}
								else
								{
									$doc_true = false;
								}
								if (!$doc_true)
								{
									if ($ww <= $start_value)
									{
										$summ = $ord[$start_index];
									}
									else {
										foreach ($html["WEIGHT"] as $kk => $vv)
										{
											if ($ww >= $vv) $index_ww = $kk;
										}
										$summ = $ord[$start_index] + ($ww - $start_value)*$ord[$index_ww];
									}
								}
							}
							else
							{
								$arResult['ERRORS'][] = GetMessage("ERR_NO_FILE", array("#ID#" => $pack));
								$flag = false;
							}
							$summ_shop_s = $summ_shop_ag = 0;
							$summ_shop_s = $_POST["summ_shop"][$pack] + $_POST["rate"][$pack];
							$summ_shop_ag = $persent_to_agent + $summ;
							
							if ($summ_shop_ag > $summ_shop_s)
							{
								$arResult["ERRORS"][] = GetMessage("ERR_COST_FOR_AGENTS", array(
									"#ID#" => $pack,
									"#NUMBER#" => $_POST["id_in"][$pack],
									"#summ_shop_ag#" => CurrencyFormat($summ_shop_ag,"RUU"),
									"#persent_to_agent#" => CurrencyFormat($persent_to_agent,"RUU"),
									"#summ#" => CurrencyFormat($summ,"RUU"),		
									"#summ_shop_s#" => CurrencyFormat($summ_shop_s,"RUU"),
									"#summ_shop#" => CurrencyFormat($_POST["summ_shop"][$pack],"RUU"),
									"#rate#" => CurrencyFormat($_POST["rate"][$pack],"RUU"),
								));
								$flag = false;
							}
							
							if ($flag)
							{
								$man_id = CheckManifests($agent_id,intval($_POST['agent_id']));
								if ($man_id <= 0)
								{
									$max_id_n = GetMaxIDIN(41,5,true,194,$_POST['agent_id']);
									$number_m= MakeManifestId($_POST['agent_id'], $agent_array['id'], $max_id_n);
									$el = new CIBlockElement;
									$PROP = array();
									$PROP[191] = $agent_id;
									$PROP[192] = 35;
									$PROP[194] = intval($_POST['agent_id']);
									$PROP[193] = $ag_arr['PROPERTY_CITY_VALUE'];
									$PROP[406] = $max_id_n;
									$PROP[407] = $number_m;
									$PROP[410] = $u_id;
									$arLoadProductArray = array(
										"MODIFIED_BY" => $u_id,
										"IBLOCK_SECTION_ID" => false,
										"IBLOCK_ID" => 41,
										"PROPERTY_VALUES"=> $PROP,
										"NAME" => GetMessage("MANIFEST_ID", array("#ID#" => $max_id_n)),
										"ACTIVE" => "Y");
									$man_id = $el->Add($arLoadProductArray);
									$arResult['MESSAGE'][] = GetMessage("MESS_NEW_MAN_CREATE", array("#ID#" => $man_id, "#NUMBER#" => $number_m));
								}
								$array_cahge =  array(203 => 55,195=>$man_id,240=>$summ,266=>$persent_to_agent);
								if ($change_pvz)
								{
									if (intval($_POST['pvz']) > 0)
									{
										$array_cahge[337] = intval($_POST['pvz']);
									}
									else {
										$array_cahge[337] = false;
									}
								}
								CIBlockElement::SetPropertyValuesEx($pack, 42, $array_cahge);
								$history_id = AddToHistory($pack,$agent_id,$u_id,55, GetMessage("MANIFEST_ID", array("#ID#" => $man_id)));
								$arResult['MESSAGE'][] = GetMessage(
									"MESS_PACK_ADD_TO_MAN", 
									array(
										"#ID#" => $_POST["id_in"][$pack],
										"#SUMM#"=>CurrencyFormat($summ,"RUU"),
										"#PERSENT#"=>CurrencyFormat($persent_to_agent,"RUU")
									)
								);
							}
						}
					}
				}
			}
		}
		
		$arResult["TITLE"] = GetMessage("TTL_DISTRIBUTION_AGENTS");
		*/
		
		if (intval($_GET['shop_f']) > 0)
		{
			$arShopsIDs = intval($_GET['shop_f']);
		}
		else
		{
			$arShops = TheListOfShops(0, false, true, false, '', $agent_array["id"]);
			$arShopsIDs = array();
			foreach ($arShops as $s)
			{
				$arShopsIDs[] = $s["ID"];
			}
		}
		$list_this_cities = GetListOfPackeges($agent_array, $arShopsIDs, 0,56,false,
			0,
			false,
			0,
			0,
			'',
			'',
			0, 
			false, 
			true, 
			array("ID"=>"DESC"), 
			0, 
			0, 
			false,
			true,
			false,
			false,
			'',
			'',
			true
		);
		$arResult["ALL_CITIES"] = array();
		foreach ($list_this_cities as $p)
		{
			$arResult["ALL_CITIES"][$p['PROPERTY_CITY_VALUE']] = $p['CITY_NAME'];
			$arResult["ALL_SHOPS"][$p['PROPERTY_CREATOR_VALUE']] = $p['PROPERTY_CREATOR_NAME'];
		}
		$arResult["LIST"] = GetListOfPackeges(
			$agent_array,
			$arShopsIDs,
			0,
			56,
			false,
			0,
			false,
			intval($_GET['city_f']),
			0,
			'',
			'',
			0, 
			true, 
			true, 
			array("ID"=>"DESC"), 
			0, 
			0, 
			false,
			true,
			false,
			false,
			'',
			'',
			true
		);
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"],$arResult["LIST"]["COUNT"]);
		// $arResult['AVAILABLE_AGENTS'] = AvailableAgents(true, $agent_array['id']);
	}
	
	if ($mode == 'to_topdelivery')
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
				if (count($_POST['pack_id']) > 0)
				{
					$arToTD = array();
					foreach ($_POST['pack_id'] as $p)
					{
						$packInfo = GetOnePackage($p, $agent_array['id'], $agent_array['type']);
						
						$db_props = CIBlockElement::GetProperty(6, $packInfo['PROPERTY_CITY_VALUE'], array("sort" => "asc"), array("CODE"=>"CODE_TOPDELIVERY"));
						if ($ar_props = $db_props->Fetch())
						{
							$TDcity = intval($ar_props["VALUE"]);
							if ($TDcity > 0)
							{
								$db_old_groups = CIBlockElement::GetElementGroups($packInfo['PROPERTY_CITY_VALUE'], true, array('ID'));
								if($ar_group = $db_old_groups->Fetch())
								{
									$db_list = CIBlockSection::GetList(array("name"=>"asc"), array('IBLOCK_ID'=>6, 'ID'=> $ar_group['ID']), false, array('ID','NAME','UF_CODE_TOPDELIVERY'));
									if($ar_result = $db_list->GetNext())
									{
										if (intval($ar_result['UF_CODE_TOPDELIVERY']) > 0)
										{
											$soap = new SoapClient($TDsoapLink, array('login'=> $TDsoapLogin,'password'=>$TDsoapPass));
											$params = array(
												'calcOrderCosts' => array(
													'auth' => array(
														'login' => $TDlogin,
														'password' => $TDpass,
													),
													'orderParams'=>array(
														'serviceType' => 'DELIVERY',
														'deliveryType' => 'COURIER',
														'deliveryWeight' => array(
															'weight' => $packInfo['PROPERTY_WEIGHT_VALUE']*1000,
															'volume'=>array(
																'length' => $packInfo['PROPERTY_SIZE_1_VALUE'],
																'height' => $packInfo['PROPERTY_SIZE_2_VALUE'],
																'width' => $packInfo['PROPERTY_SIZE_3_VALUE'],
															),
														),
														'clientCost' => $packInfo['PROPERTY_COST_2_VALUE'],
														'declaredSum' => $packInfo['PROPERTY_COST_GOODS_VALUE'],
														'declaredReturnSum' => $packInfo['PROPERTY_COST_GOODS_VALUE'],
														'addDelivery' => 0,
														'deliveryAddress' =>array(
															'region' => (string)intval($ar_result['UF_CODE_TOPDELIVERY']),
															'city' => (string)$TDcity,
															'zipcode' => '',
															'street'=>iconv('windows-1251','utf-8', $packInfo['PROPERTY_ADRESS_VALUE']),
															'building' => '',
															'appartment' => '',
															'type' => 'id',
														),
													),
												),
											);
											$calcOrderCostsResponse = $soap->__call('calcOrderCosts', $params);
											$a = (array)$calcOrderCostsResponse;
											$b = (array)$a['requestResult'];
											if ($b['status'] == 0)
											{
												$c = (array)$a['calcOrderCosts'];
												$arResult["MESSAGE"][] = 'Ответ TD при расчете стоимости доставки по <a href="/warehouse/index.php?mode=package&id='.$packInfo['ID'].'">заказу №'.$packInfo['PROPERTY_N_ZAKAZ_IN_VALUE'].'</a>:<br>
													Стоимость доставки: '.$c['delivery'].'<br>Стоимость РКО: '.$c['rko'].'<br>Стоимость страховки: '.$c['insurance'].'<br>Стоимость возврата: 
													'.$c['return'].'<br>Стоимость страховки возврата: '.$c['insuranceReturn'];
												$params = array(
													'addOrders'=>array(
														'auth'=>array(
															'login'=> $TDlogin,
															'password'=> $TDpass,
														),
														'addedOrders'=>array(
															array(
																'serviceType' => 'DELIVERY',
																'deliveryType' => ($packInfo['PROPERTY_CONDITIONS_ENUM_ID'] == 38) ? 'PICKUP' : 'COURIER',
																'orderSubtype' =>'SIMPLE',
																'webshopNumber'=> $packInfo['PROPERTY_N_ZAKAZ_IN_VALUE'],
																'orderUrl' => '',
																'desiredDateDelivery' => array(
																	'date' => $packInfo['DELIV_DATE'],
																	'bTime' => substr($packInfo['PROPERTY_TIME_PERIOD_VALUE'], 2, 5),
																	'eTime' => substr($packInfo['PROPERTY_TIME_PERIOD_VALUE'], 11, 5),
																),
																'deliveryAddress'=>array(
																	'type' => 'id',     
																	'region' => (string)intval($ar_result['UF_CODE_TOPDELIVERY']),
																	'city' => (string)$TDcity,
																	'zipcode' =>'',
																	'street'=> iconv('windows-1251','utf-8',$packInfo['PROPERTY_ADRESS_VALUE']),
																	'building' => '',
																	'appartment' => ''	
																),
																'pickupAddressId'=> 0,
																'clientInfo'=>array(
																	'fio'=> iconv('windows-1251','utf-8', $packInfo['PROPERTY_RECIPIENT_VALUE']),
																	'phone'=> iconv('windows-1251','utf-8', $packInfo['PROPERTY_PHONE_VALUE']),
																	'comment' => iconv('windows-1251','utf-8', $packInfo['PROPERTY_PREFERRED_TIME_VALUE']['TEXT']),
																	'address' => iconv('windows-1251','utf-8', $packInfo['PROPERTY_ADRESS_VALUE']),
																),
																'prePayed' => ($packInfo['PROPERTY_COST_2_VALUE'] > 0) ? 0 : 1,
																'paymentByCard' => 0,
																'clientCosts'=>array(
																	'discount'=>array(
																		'type'=>'SUM',
																		'value'=>0,
																	),
																	'clientDeliveryCost'=> $packInfo["PROPERTY_COST_3_VALUE"],
																	'recalcDelivery'=>0,
																),
																'services' => array(
																	'notOpen'=>0,
																	'marking'=>0,
																	'smsNotify'=>0,
																	'forChoise'=>0,
																	'places'=>1,
																	'pack'=>array(
																		'need'=>0,
																		'type'=>'',
																	),
																	'giftPack'=>array(
																		'need'=>0,
																		'type'=>'',
																	),
																	'deliveryWeight'=>array(
																		'weight'=> $packInfo['PROPERTY_WEIGHT_VALUE']*1000,
																		'volume'=>array(
																			'length'=> $packInfo['PROPERTY_SIZE_1_VALUE'],
																			'height'=> $packInfo['PROPERTY_SIZE_2_VALUE'],
																			'width'=> $packInfo['PROPERTY_SIZE_3_VALUE'],
																		),
																	),
																),
																'items'=>array(
																	array(
																		'name'=>iconv('windows-1251','utf-8','Заказ №'.$packInfo['PROPERTY_N_ZAKAZ_IN_VALUE']),
																		'article'=> $packInfo['PROPERTY_N_ZAKAZ_IN_VALUE'],
																		'count'=>1,
																		'push'=>1,
																		'declaredPrice'=> $packInfo['PROPERTY_COST_GOODS_VALUE'],
																		'clientPrice'=> $packInfo['PROPERTY_COST_GOODS_VALUE'],
																		'weight'=>$packInfo['PROPERTY_WEIGHT_VALUE']*1000,
																	),
																),
																'execution'=>array(
																	'executorId'=>0,
																),
															),
														),
													)
												);
												$addOrdersResponse = $soap->__call('addOrders', $params);
												$aa = (array)$addOrdersResponse;
												$bb = (array)$aa['requestResult'];
												$cc = (array)$aa['addOrdersResult'];
												if ($bb['status'] == 0)
												{
													$arResult["MESSAGE"][] = $packInfo['PROPERTY_N_ZAKAZ_IN_VALUE'].': '.iconv('utf-8','windows-1251',$bb['message']);
												}
												elseif  ($bb['status'] == 1)
												{
													$arResult["WARNINGS"][] =  $packInfo['PROPERTY_N_ZAKAZ_IN_VALUE'].': '.iconv('utf-8','windows-1251',$bb['message']);
												}
												else
												{
													$arResult["ERRORS"][] = $packInfo['PROPERTY_N_ZAKAZ_IN_VALUE'].': '.iconv('utf-8','windows-1251',$bb['message']);
												}
												if ($cc['status'] == 0)
												{
													$dd = (array)$cc['orderIdentity'];

													CIBlockElement::SetPropertyValuesEx(
														$packInfo['ID'],
														42,
														array(
															203 => 46,
															229 => 68,
															650 => 1,
															649 => $dd['orderId'],
															651 => $c['delivery'],
															652 => $c['rko'],
															653 => $c['insurance'],
															654 => $c['return'],
															655 => $c['insuranceReturn'],
															499 => 10396284
														)
													);
													$history_id = AddToHistory($p,$agent_id,$u_id,46,'TopDelivery, '.$packInfo['PROPERTY_CITY_NAME']);
													$short_history_id = AddToShortHistory($p,$u_id,68,$packInfo['PROPERTY_CITY_NAME']);

													$arResult["MESSAGE"][] = $packInfo['PROPERTY_N_ZAKAZ_IN_VALUE'].': '.iconv('utf-8','windows-1251',$cc['message']);
													
												}
												elseif  ($cc['status'] == 1)
												{
													$arResult["WARNINGS"][] = $packInfo['PROPERTY_N_ZAKAZ_IN_VALUE'].': '.iconv('utf-8','windows-1251',$cc['message']);
												}
												else
												{
													$arResult["ERRORS"][] = $packInfo['PROPERTY_N_ZAKAZ_IN_VALUE'].': '.iconv('utf-8','windows-1251',$cc['message']);
												}
											}
											else
											{
												$arResult["ERRORS"][] = 'Ответ TD при расчете стоимости доставки по заказу №'.$packInfo['PROPERTY_N_ZAKAZ_IN_VALUE'].': '.iconv('utf-8','windows-1251',$b['message']);
											}
										}
										else
										{
											$arResult["ERRORS"][] = 'Неизвестный идентификатор региона заказа №'.$packInfo['PROPERTY_N_ZAKAZ_IN_VALUE'].' в системе TD';
										}
									}
									else
									{
										$arResult["ERRORS"][] = 'Регион заказа №'.$packInfo['PROPERTY_N_ZAKAZ_IN_VALUE'].' не найден';
									}
								}
								else
								{
									$arResult["ERRORS"][] = 'Регион заказа №'.$packInfo['PROPERTY_N_ZAKAZ_IN_VALUE'].' не найден';
								}
							}
							else
							{
								$arResult["ERRORS"][] = 'Неизвестный идентификатор города заказа №'.$packInfo['PROPERTY_N_ZAKAZ_IN_VALUE'].' в системе TD';
							}
						}
						else
						{
							$arResult["ERRORS"][] = 'Неизвестный идентификатор города заказа №'.$packInfo['PROPERTY_N_ZAKAZ_IN_VALUE'].' в системе TD';	
						}

					}
				}
				else
				{
					$arResult["ERRORS"][] = 'Не выбран ни один заказ';
				}
			}
		}
	
/*
$soap = new SoapClient('http://test.is.topdelivery.ru/api/soap/w/1.1/?wsdl', array('login'=>"tdsoap",'password'=>"5f3b5023270883afb9ead456c8985ba8"));
$params = array(
	'deleteOrder'=>array(
		'auth'=>array(
			'login'=>'webshop',
			'password'=>'pass',
		),
	'orderIdentity'=>array(
		array(
			'orderId'=>220,
		)
	)
));
$deleteOrderResponse = $soap->__call( 'deleteOrder', $params);
*/


		
		
		$arShopsIDs = false;
		$arShops = TheListOfShops(0, false, true, false, '', $agent_array["id"]);
		$arShopsIDs = array();
		foreach ($arShops as $s)
		{
			$arShopsIDs[] = $s["ID"];
		}
		$arResult["LIST"] = GetListOfPackeges(
			$agent_array,
			$arShopsIDs,
			0,
			56,
			false,
			0,
			false,
			intval($_GET['city_f']),
			0,
			'',
			'',
			0, 
			true, 
			true, 
			array("ID"=>"DESC"), 
			0, 
			0, 
			false,
			true
		);
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"],$arResult["LIST"]["COUNT"]);
	}
	/**********передача заказов в topdelivery**********/
	
	/*********заказы на доставке в TopDelivery*********/
	if ($mode == 'in_topdelivery')
	{
		if (isset($_POST['save']))
		{
			if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
			{
				$_POST = array();
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST['key_session']] = $_POST['rand'];
				
				foreach ($_POST['operation'] as $p => $oper)
				{
					if(intval($oper) > 0)
					{
						if ($oper == '001')
						{
							CIBlockElement::SetPropertyValuesEx($p, 42, array(
								203 => 44, 
								229 => 72, 
								218 => 61, 
								267 => $_POST['date_delivery'][$p], 
								479 => $_POST['summ'][$p],
								442 => 0,
								499 => 7326165,
								504 => 1,
								498 => substr($_POST['date_delivery'][$p], 0, 10),
								509 => false
							));
							$history_id = AddToHistory($p,$agent_id,$u_id,44,$_POST['fio'][$p],$_POST['date_delivery'][$p]);
							$short_history_id = AddToShortHistory($p,$u_id,72,$_POST['fio'][$p],$_POST['date_delivery'][$p]);
							$arResult['MESSAGE'][] = GetMessage("MESS_PACK_DELIVERED", array("#ID#" => $p, "#NUMBER#" => $_POST['id_in'][$p]));
						}
						else
						{
							$vozvr = (intval($_POST['return_yes'][$p]) == 1) ? 1 : 0;
							$summ_vozvr = (intval($_POST['summ_return'][$p]) > 0) ? $_POST['summ_return'][$p] : 0;
							CIBlockElement::SetPropertyValuesEx($p, 42, array(
								442 => 1,
								444 => $vozvr,
								445 => $summ_vozvr
							));
							$history_id = AddToHistory($p, $agent_id, $u_id, 40, $arParams["STATUS"][$oper], $_POST['date_delivery'][$p]);
							$short_history_id = AddToShortHistory($p, $u_id, 69, $arParams["STATUS"][$oper], $_POST['date_delivery'][$p]);
							$arResult['MESSAGE'][] = GetMessage("MOVED_TO_WAREHOUSE", array("#ID#" => $p, "#NUMBER#" => $_POST['id_in'][$p]));
						}
					}
					else
					{
						if (intval($_POST['return_yes'][$p]) == 1)
						{
							CIBlockElement::SetPropertyValuesEx($p, 42, array(
								442 => 1,
								444 => 1,
								445 => (intval($_POST['summ_return'][$p]) > 0) ? $_POST['summ_return'][$p] : 0
							));
							$arResult['MESSAGE'][] = GetMessage("MARKED_AS_RETURNED", array("#ID#" => $p, "#NUMBER#" => $_POST['id_in'][$p]));
						}
					}
				}
			}
		}
		$arShopsIDs = false;
		$arShops = TheListOfShops(0, false, true, false, '', $agent_array["id"]);
		$arShopsIDs = array();
		foreach ($arShops as $s)
		{
			$arShopsIDs[] = $s["ID"];
		}
		$arResult["LIST"] = GetListPackeges(
			$agent_array, array("ID"=>"DESC"), array(40,43,79,46,56), false, false, 0, $_GET['number'], true, true, $agent_array["id"], $_GET['exceptions'],'N');
			$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
			unset($arResult["LIST"]["NAV_STRING"],$arResult["LIST"]["COUNT"]);
		
		
		$arResult["LIST"] = GetListOfPackeges(
			$agent_array,
			$arShopsIDs,
			false,
			array(40,43,79,46,56),
			false,
			0,
			false,
			0,
			0,
			'',
			'',
			0, 
			true, 
			true, 
			array("ID"=>"DESC"), 
			0, 
			0, 
			false,
			false,
			false,
			false,
			'',
			'',
			false,
			'Y'
		);
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"],$arResult["LIST"]["COUNT"]);
		$arTDnumbers = array();
		foreach ($arResult["LIST"] as $p)
		{
			$arTDnumbers[] = array('orderId'=> $p["PROPERTY_TD_NUMBER_VALUE"]);
		}

		$soap = new SoapClient($TDsoapLink, array('login'=> $TDsoapLogin,'password'=>$TDsoapPass));
		$params = array(
			'getOrdersInfo'=>array(
			   'auth'=>array(
					'login' => $TDlogin,
					'password' => $TDpass,
				),
			 'order'=> $arTDnumbers
			),
		);
		$getOrdersInfoResponse = $soap->__call('getOrdersInfo', $params);
		$a = objectToArray($getOrdersInfoResponse, true);
		$arResult["TD_INFO"] = array();
		if ($a['requestResult']['status'] == 0)
		{
			foreach ($a['ordersInfo'] as $inf)
			{
				$arResult["TD_INFO"][$inf['orderInfo']['orderIdentity']['orderId']] = $inf['orderInfo'];
			}
		}
	}
	/*********заказы на доставке в TopDelivery*********/
	
	
	/*****************заказы на складе*****************/
	if ($mode == 'warehouse')
	{
		$arShops = TheListOfShops(0, false, true, false, '', $agent_array["id"]);
		$arShopsIDs = array();
		foreach ($arShops as $s)
		{
			$arShopsIDs[] = $s["ID"];
		}
		$arResult["TITLE"] = GetMessage("TTL_WAREHOUSE");
		$arResult["LIST"] = GetListOfPackeges($agent_array, $arShopsIDs,0,array(55,56));
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"],$arResult["LIST"]["COUNT"]);
	}
	
	/**********************архив***********************/
	if ($mode == 'archive')
	{
		$arShops = TheListOfShops(0, false, true, false, '', $agent_array["id"]);
		$arShopsIDs = array();
		foreach ($arShops as $s)
		{
			$arShopsIDs[] = $s["ID"];
		}
		$arResult["TITLE"] = GetMessage("TTL_ARCHIVE_SHIPMENTS");
		if (intval($_GET['state']) > 0) $st = intval($_GET['state']); else $st = false;
		if ((intval($_GET['shop']) > 0) && (in_array(intval($_GET['shop']), $arShopsIDs)))
		{
			$arShopsIDs = intval($_GET['shop']);
		}
		$number = strlen(trim($_GET['number'])) ? trim($_GET['number']) : false;
		$arResult["LIST"] = GetListOfPackeges($agent_array, $arShopsIDs, false, $st, false, 0, false, 0, 0,$_GET['date_from'], $_GET['date_to'], 0, true, false, array("ID"=>"DESC"), 0, 0, $number);
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"],$arResult["LIST"]["COUNT"]);
		$arResult['AGENT_ID'] = $agent_array['id'];
	}
	if ($mode == 'archive_topdelivery')
	{
		$arShops = TheListOfShops(0, false, true, false, '', $agent_array["id"]);
		$arShopsIDs = array();
		foreach ($arShops as $s)
		{
			$arShopsIDs[] = $s["ID"];
		}
		$arResult["TITLE"] = GetMessage("TTL_ARCHIVE_SHIPMENTS");
		if (intval($_GET['state']) > 0) $st = intval($_GET['state']); else $st = false;
		if ((intval($_GET['shop']) > 0) && (in_array(intval($_GET['shop']), $arShopsIDs)))
		{
			$arShopsIDs = intval($_GET['shop']);
		}
		$number = strlen(trim($_GET['number'])) ? trim($_GET['number']) : false;
		//$arResult["LIST"] = GetListOfPackeges($agent_array, $arShopsIDs, false, $st, false, 0, false, 0, 0,$_GET['date_from'], $_GET['date_to'], 0, true, false, array("ID"=>"DESC"), 0, 0, $number);
		$arResult["LIST"] = GetListOfPackeges(
			$agent_array,
			$arShopsIDs,
			false,
			$st,
			false,
			0,
			false,
			0,
			0,
			$_GET['date_from'],
			$_GET['date_to'],
			0, 
			true, 
			false, 
			array("ID"=>"DESC"), 
			0, 
			0, 
			$number,
			false,
			false,
			false,
			'',
			'',
			false,
			'Y'
		);
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"],$arResult["LIST"]["COUNT"]);
		$arResult['AGENT_ID'] = $agent_array['id'];
	}
	
	/**********************заказ***********************/
	if ($mode == 'package')
	{
		/*
		if (isset($_POST['departure_package']))
		{
			if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
			{
				$_POST = array();
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST['key_session']] = $_POST['rand'];
				if (intval($_POST['city']) == 8054)
				{
					if ($_POST['conditions'] == 37)
					{
						$state_short = 71;
						$state_full = 40;
					}
					else
					{
						$state_short = 74;
						$state_full = 79;
					}
				}
				else
				{
					$state_full = 56;
					$state_short = 67;
				}
				$id = $_POST['id'];
				$number = $_POST['number'];
				$comment = $_POST['comment'];
				CIBlockElement::SetPropertyValuesEx($id, 42, array(229 => $state_short, 203 => $state_full));
				$add_to_period = AddElementToPeriod($id, 283, $u_id);
				$history_id = AddToHistory($id,$agent_id,$u_id,$state_full,$comment);
				$short_history_id = AddToShortHistory($id,$u_id, $state_short,$comment);
				$arResult['MESSAGE'][] = GetMessage("MESS_PACK_ACCEPT", array("#ID#" => $id, "#NUMBER#" => $number));
			}
		}
		
		if (isset($_POST['reject_package']))
		{
			if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
			{
				$_POST = array();
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST['key_session']] = $_POST['rand'];
				$id = $_POST['id'];
				$comment = $_POST['comment'];
				if (!strlen($comment))
				{
					$arResult['ERRORS'][] = GetMessage("ERR_NO_COMMENT");
				}
				else
				{
					CIBlockElement::SetPropertyValuesEx($id, 42, array(203 => 57, 229 => 77));
					$history_id = AddToHistory($id,$agent_id,$u_id,57,$comment);
					$short_history_id = AddToShortHistory($id,$u_id,77,$comment);
					$arResult['MESSAGE'][] = GetMessage("MESS_PACK_NOT_ACCEPT", array("#ID#" => $id));
				}
			}
		}
		*/
		if (isset($_POST['submit_for_delivery']))
		{
			if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
			{
				$_POST = array();
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST['key_session']] = $_POST['rand'];
				$id = $_POST['pack_id'];
				$secss_or_not = WriteOffOfGoods($id);
				if ($secss_or_not['result'])
				{
					if ((in_array($_POST['city'], $agent_array['city'])) && ($_POST['conditions'] == 38))
					{
						$state_short = 74;
						$state_full = 79;
					}
					else
					{
						$state_full = 56;
						$state_short = 67;
					}
					/*
					if (intval($_POST['city']) == 8054)
					{
						if ($_POST['conditions'] == 37)
						{
							$state_short = 71;
							$state_full = 40;
						}
						else
						{
							$state_short = 74;
							$state_full = 79;
						}
					}
					else
					{
						$state_full = 56;
						$state_short = 67;
					}
					*/
					$shop_id = $_POST['shop'];
					$shop_info = GetCompany($shop_id);
					$cost_formation = ($_POST['goods_count'] > 1) ? $shop_info['PROPERTY_COST_ORDERING_VALUE'] : 0;
					CIBlockElement::SetPropertyValuesEx($id, 42, array(203 => $state_full, 229 => $state_short, 347 => $cost_formation, 499 => $agent_array['id']));
					$add_to_period = AddElementToPeriod($id, 283, $u_id);
					$history_id = AddToHistory($id,$agent_id,$u_id, $state_full, GetMessage("ORDER_FORMED"));
					$short_history_id = AddToShortHistory($id,$u_id, $state_short, GetMessage("ORDER_FORMED"));
					$date_send = date('d.m.Y H:i');
					$txt = GetMessage("SUBMIT_FOR_DELIVERY_TEXT", array("#DATE_SEND#" => $date_send, "#ID#" => $id, "#NUMBER#" => $_POST['number_pack']));
					$qw = SendMessageInSystem($u_id, $agent_array['id'], $shop_id, GetMessage("SUBMIT_FOR_DELIVERY_NAME"), 156, $txt);
					SendMessageMailNew($shop_id,$agent_array['id'],155,168,array(
						"ID_MESS" => $qw,
						"ID_ORDER" => $id,
						"NUMBER_ORDER" => $_POST['number_pack'],
						"DATE_SEND" => $date_send
					));
					$arResult['MESSAGE'][] = GetMessage("MESS_PACK_GO_DELIVERY", array("#ID#" => $_POST['id'], "#NUMBER#" => $_POST['id_in']));
				}
				else {
					$arResult['ERRORS'][] = GetMessage("ERR_NO_COUNT", array("#IDS#" => implode(', ',$secss_or_not['goodsLess'])));
				}
			}
		}
		
		if (isset($_POST['return']))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
				$pack_id = $_POST['pack_id'];
				$st_order = 0;
				$db_props = CIBlockElement::GetProperty(42, $pack_id, array("sort" => "asc"), Array("CODE"=>"STATE"));
				if($ar_props = $db_props->Fetch())
				{
					$st_order = $ar_props["VALUE"];
				}
				if (!in_array($st_order,$arParams['STATUS_RETURN'])) 
				{
					$arResult["ERRORS"][] = GetMessage("INCORRECT_STATUS_RETURN", array("#ID#" => $pack, "#NUMBER#" => $_POST['id_in'][$pack]));
				}
				else
				{
					$res = CIBlockElement::GetList(
						array("ID" => "DESC"), 
						array("IBLOCK_ID" => 65,"PROPERTY_351" => 153, "ACTIVE" => "Y", 'PROPERTY_352' => $pack_id), 
						false, 
						false, 
						array("ID")
					);
					$count_if_dv = 0;
					while($ob = $res->GetNextElement())
					{
						$arr = $ob->GetFields();
						$count_if_dv++;
					}
					if ($count_if_dv > 0)
					{
						$arResult["ERRORS"][] = 'По данному заказу уже производился возврат на склад, операция прервана';
					}
					else
					{
						if (!strlen($_POST['comment']))
						{
							$arResult["ERRORS"][] = 'Не указана причина возврата товаров на склад';
						}
						else
						{
							$pack_id = $_POST['pack_id'];
							$goods = GetGoodsOdPack($pack_id);
							if (count($goods) > 0)
							{
								foreach ($goods as $k => $v)
								{
									$count_spis = 0;
									$res = CIBlockElement::GetList(
										array("created"=>"desc"), 
										array(
											"IBLOCK_ID" => 65,
											"PROPERTY_352" => $pack_id,
											"PROPERTY_321" => $v['GOOD_ID'],
											"PROPERTY_351" => 154
										), 
										false, 
										false, 
										array("ID", "PROPERTY_325")
									);
									while($ob = $res->GetNextElement())
									{
										$a = $ob->GetFields();
										$count_spis = $count_spis + $a["PROPERTY_325_VALUE"];
									}
									if ($count_spis > 0)
									{
										$db_props = CIBlockElement::GetProperty(62, $v['GOOD_ID'], array("sort" => "asc"), array("ID" => 299));
										$ar_props = $db_props->Fetch();
										$count = intval($ar_props["VALUE"]);
										$count = $count + $count_spis;
										$el = new CIBlockElement;
										$PROP = array();
										$PROP[321] = $v['GOOD_ID'];
										$PROP[323] = $v['ARTICLE'];
										$PROP[324] = $v['WEIGHT'];
										$PROP[326] = $v['COST'];
										$PROP[352] = $pack_id;
										$PROP[325] = $count_spis;
										$PROP[351] = 153;
										$arLoadProductArray = array(
											"MODIFIED_BY" => $u_id,
											"IBLOCK_SECTION_ID" => false,
											"IBLOCK_ID" => 65,
											"NAME" => $v['NAME'],
											"PROPERTY_VALUES"=> $PROP,
											"ACTIVE" => "Y"
										);
										$zapis_id = $el->Add($arLoadProductArray);
										CIBlockElement::SetPropertyValuesEx($v['GOOD_ID'], 62, array(299 => $count));
										$arResult["MESSAGE"][] = 'Товар "'.$v['NAME'].'" возвращен на склад, новое количество - '.$count.' шт.';
									}
								}
								CIBlockElement::SetPropertyValuesEx($pack_id, 42, array(504 => 1, 203 => 218, 229 => 219, 499 => $_POST['shop'], 498 => date('d.m.Y'), 479 => 0));
								$h = AddToHistory($pack_id, $agent_array['id'], $u_id, 218, trim($_POST['comment']));
								$h = AddToShortHistory($pack_id, $u_id, 219, trim($_POST['comment']));
								$arResult["MESSAGE"][] = 'Заказ завершен';
							}
							else
							{
								$arResult["ERRORS"][] = 'Товары в заказе отсутствуют';
							}
						}
					}
				}
			}
		}
		
		if (isset($_POST['cancel']))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
				$pack_id = $_POST['pack_id'];
				$st_order = 0;
				$db_props = CIBlockElement::GetProperty(42, $pack_id, array("sort" => "asc"), Array("CODE"=>"STATE"));
				if($ar_props = $db_props->Fetch())
				{
					$st_order = $ar_props["VALUE"];
				}
				if (!in_array($st_order,$arParams['STATUS_CHANCEL'])) 
				{
					$arResult["ERRORS"][] = GetMessage("INCORRECT_STATUS_CANCELLATION", array("#ID#" => $pack_id, "#NUMBER#" => $_POST['number_pack']));
				}
				else
				{
					$max_id_5 = GetMaxIDIN(71,5);
					$el = new CIBlockElement;
					$PROP = array();
					$PROP[367] = $max_id_5;
					$PROP[369] = $pack_id;
					$arLoadProductArray = array(
						"MODIFIED_BY" => $u_id,
						"IBLOCK_SECTION_ID" => false,
						"IBLOCK_ID" => 71,
						"NAME" => GetMessage("CANCELLATION", array("#ID#" => $max_id_5)),
						"PROPERTY_VALUES" => $PROP,
						"ACTIVE" => "Y"
					);
					$an_id = $el->Add($arLoadProductArray);
					$goods = GetGoodsOdPack($pack_id);
		
					CIBlockElement::SetPropertyValuesEx($pack_id, 42, array(203 => 166, 229 => 167, 504 => 1, 499 => $_POST['shop'], 498 => date('d.m.Y'), 434 => 0));
					$text = strlen(trim($_POST['comment'])) ? GetMessage("CANCELLATION", array("#ID#" => $max_id_5)).'. '.trim($_POST['comment']) : GetMessage("CANCELLATION", array("#ID#" => $max_id_5));			
					$history_id = AddToHistory($pack_id, $agent_id, $u_id, 166, $text);
					$short_history_id = AddToShortHistory($pack_id, $u_id, 167, trim($_POST['comment']));
					$date_send = date('d.m.Y H:i');
					$txt = GetMessage("CANCELLATION_TEXT", array("#DATE#" => $date_send, "#ID#" => $pack_id, "#NUMBER#" => $_POST['number_pack']));
					$qw = SendMessageInSystem($u_id, $agent_array['id'], $_POST['shop'], GetMessage("CANCELLATION_TITLE"), 169, $txt);
					SendMessageMailNew(
						$_POST['shop'],
						$agent_array['id'],
						168,
						171,
						array(
							"ID_MESS" => $qw,
							"ID_PACK" => $pack_id,
							"NUMBER" => $_POST['number_pack'],
							"DATE_SEND" => $date_send
						)
					);
					$arResult["MESSAGE"][] = GetMessage("CANCELLATION_SUCCESS", array("#ID#" => $pack_id, "#NUMBER#" => $_POST['number_pack']));
				}
			}
		}
		
		$arResult['PACK'] = GetOnePackage(intval($_GET['id']), $agent_array['id'], $agent_array['type']);
		$arResult['GAB_W'] = WhatIsGabWeightCompany($arResult['PACK']['PROPERTY_CREATOR_VALUE']);
		if ($arResult['PACK'])
		{
			$arResult["TITLE"] = GetMessage("TTL_PACK", array("#ID#" => $arResult['PACK']["PROPERTY_ID_IN_VALUE"]));
		}
		else
		{
			$arResult["TITLE"] = GetMessage("TTL_PACK_NOT");
		}
	}
	
	/**************редактирование заказа***************/
	if ($mode == 'package_edit')
	{
		$arResult['time_periods'] = array();
		$db_enum_list = CIBlockProperty::GetPropertyEnum(493, array("SORT"=>"asc"), array("IBLOCK_ID"=> 42));
		while($ar_enum_list = $db_enum_list->GetNext())
		{
			$arResult['time_periods'][$ar_enum_list["ID"]] = $ar_enum_list["VALUE"];
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
				$arChange = array(
					313 => $_POST['CASH'],
					478 => $_POST['PAY_FOR_REFUSAL'],
					339 => array("VALUE" => array("TYPE" =>"TEXT","TEXT" => trim($_POST['PREFERRED_TIME'])))
				);
				if (!strlen($_POST['RECIPIENT']))
				{
					$arResult["ERRORS"][] = GetMessage('ERR_STRING_7');
				}
				else
				{
					$arChange[208] = trim($_POST['RECIPIENT']);
				}
				if (!strlen($_POST['PHONE']))
				{
					$arResult["ERRORS"][] = GetMessage('ERR_STRING_8');
				}
				else
				{
					$arChange[209] = trim($_POST['PHONE']);
				}
				if (($_POST['CONDITIONS'] == 37) && (!strlen($_POST['ADRESS'])))
				{
					$arResult["ERRORS"][] = GetMessage('ERR_STRING_10');
				}
				else
				{
					$arChange[201] = $_POST['CONDITIONS'];
					$arChange[202] = NewQuotes($_POST['ADRESS']);
				}
				if (!strlen($_POST['PROPERTY_CITY']))
				{
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_9");
				}
				else
				{
					$get_city_id = GetCityId($_POST['PROPERTY_CITY']);
					$arChange[212] = $get_city_id;
				}
				$w = floatval(str_replace(',', '.', $_POST['WEIGHT']));
				$s_1 = floatval(str_replace(',', '.', $_POST['size_1']));
				$s_2 = floatval(str_replace(',', '.', $_POST['size_2']));
				$s_3 = floatval(str_replace(',', '.', $_POST['size_3']));
				$places = intval($_POST['PLACES']);
				$cost_goods = floatval(str_replace(',', '.', $_POST['COST_GOODS']));
				$cost_3 = floatval(str_replace(',', '.', $_POST['COST_3']));
				$cost_2 = floatval(str_replace(',', '.', $_POST['COST_2']));
				$cost_1 = floatval(str_replace(',', '.', $_POST['COST_1']));
				$rate = floatval(str_replace(',', '.', $_POST['RATE']));
				$summ_shop = floatval(str_replace(',', '.', $_POST['SUMM_SHOP']));
				$cost_return = floatval(str_replace(',', '.', $_POST['COST_RETURN']));
				$cost_issue = floatval(str_replace(',', '.', $_POST['SUMM_ISSUE']));
				if ($w < 0)
				{
					$arResult["ERRORS"][] = GetMessage('ERR_STRING_5');
				}
				else
				{
					$arChange[225] = $w;
				}
				if (($s_1 < 0) || ($s_2 < 0) || ($s_3 < 0))
				{
					$arResult["ERRORS"][] = GetMessage('ERR_STRING_14');
				}
				else
				{
					$arChange[247] = $s_1;
					$arChange[248] = $s_2;
					$arChange[249] = $s_3;
				}
				if ($places < 1)
				{
					$arResult["ERRORS"][] = GetMessage('ERR_STRING_6');
				}
				else
				{
					$arChange[232] = $places;
				}
				if ($cost_goods < 0)
				{
					$arResult["ERRORS"][] = GetMessage('ERR_STRING_12');
				}
				else
				{
					$arChange[307] = $cost_goods;
				}
				if ($cost_3 < 0)
				{
					$arResult["ERRORS"][] = GetMessage('ERR_STRING_13');
				}
				else
				{
					$arChange[199] = $cost_3;
				}
				if ($cost_2 < 0)
				{
					$arResult["ERRORS"][] = GetMessage('ERR_STRING_2');
				}
				else
				{
					$arChange[198] = $cost_2;
				}
				if ($cost_1 < 0)
				{
					$arResult["ERRORS"][] = GetMessage('ERR_STRING_3');
				}
				else
				{
					$arChange[197] = $cost_1;
				}
				if ($rate < 0)
				{
					$arResult["ERRORS"][] = GetMessage('ERR_STRING_15');
				}
				else
				{
					$arChange[231] = $rate;
				}
				if ($summ_shop < 0)
				{
					$arResult["ERRORS"][] = GetMessage('ERR_STRING_16');
				}
				else
				{
					$arChange[250] = $summ_shop;
				}
				if ($cost_return < 0)
				{
					$arResult["ERRORS"][] = GetMessage('ERR_STRING_17');
				}
				else
				{
					$arChange[445] = $cost_return;
				}
				if ($cost_issue < 0)
				{
					$arResult["ERRORS"][] = GetMessage('ERR_STRING_18');
				}
				else
				{
					$arChange[347] = $cost_issue;
				}
				CIBlockElement::SetPropertyValuesEx($_POST['pack_id'], 42, $arChange);
				$arResult["MESSAGE"][] = GetMessage('MESS_PACK_CHANGE', array('#NUMBER#' => $_POST['number'], '#ID#' => $_POST['pack_id']));
			}
		}
		$arResult['PACK'] = GetOnePackage(intval($_GET['id']), $agent_array['id'], $agent_array['type']);
		if ($arResult['PACK'])
		{
			$arResult["TITLE"] = 'Редактирование заказа №'.$arResult['PACK']['PROPERTY_N_ZAKAZ_IN_VALUE'];
			$arResult["RATE"] = WhatIsRate($arResult['PACK']["PROPERTY_CREATOR_VALUE"]);
			$arResult["PRICE"] = WhatIsPrice($arResult['PACK']["PROPERTY_CREATOR_VALUE"]);
			$arResult["PRICE_2"] = WhatIsPrice($arResult['PACK']["PROPERTY_CREATOR_VALUE"],2);
		}
		else
		{
			$arResult["TITLE"] = 'Заказ не найден';
		}
	}

	/********************ввод подов********************/
	if ($mode == 'pods')
	{
		if (isset($_POST['save']))
		{
			if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
			{
				$_POST = array();
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST['key_session']] = $_POST['rand'];
				$summ_to_agents = array();
				$arUks = TheListOfUKs();
				foreach ($_POST['agent_id'] as $a)
				{
					$summ_to_agents[$a] = 0;
				}
				foreach ($_POST['operation'] as $p => $oper)
				{
					if(intval($oper) > 0)
					{

						if ($oper == '001')
						{
							$agent_who_delivered = $_POST['agent_id'][$p];
							$agent_who_delivered_is_uk =  (isset($arUks[$agent_who_delivered])) ? true : false;
							/*
							if (!$agent_who_delivered_is_uk)
							{
								$res = CIBlockElement::GetList(
									array("DATE_CREATE" => "DESC"),
									array("IBLOCK_ID" => 67, 'PROPERTY_SHOP' => $agent_who_delivered, 'PROPERTY_SIGNED' => false), 
									false, 
									array('nTopCount' => 1), 
									array('ID')
								);
								if ($ob = $res->GetNextElement())
								{
									$a = $ob->GetFields();
									$rep_id = $a['ID'];
								}
								else
								{
									$max_id_5 = GetMaxIDIN(67, 5, true, 342, $agent_who_delivered);
									$el = new CIBlockElement;
									$lastday = mktime(0, 0, 0, date('n',strtotime('+1 month')), 0, date('Y'));
									$arLoadProductArray = array(
										"MODIFIED_BY" => $u_id, 
										"IBLOCK_SECTION_ID" => false,
										"IBLOCK_ID" => 67,
										"NAME" => "Отчёт субагента №".$max_id_5,
										"ACTIVE" => "Y",
										"PROPERTY_VALUES" => array(
											342 => $agent_who_delivered,
											343 => $max_id_5,
											348 => date('d.m.Y H:i:00'),
											481 => date('01.m.Y'),
											482 => date('d.m.Y', $lastday),
											506 => 231
										)
									);
									$rep_id = $el->Add($arLoadProductArray);
								}
							}
							else
							{
								$rep_id = false;
							}
							*/
							$rep_id = false; //формирование отчета субагента переведено на крон 2 раза в неделю - понедельник и четверг
							CIBlockElement::SetPropertyValuesEx($p, 42, array(
								203 => 44, 
								229 => 72, 
								218 => $agent_who_delivered_is_uk ? 62 : 61, 
								267 => $_POST['date_delivery'][$p], 
								479 => $_POST['summ'][$p],
								442 => 0,
								499 => 7326165,
								504 => 1,
								498 => substr($_POST['date_delivery'][$p], 0, 10),
								509 => $rep_id
							));
							$add_to_period = AddElementToPeriod($p, 284, $u_id);
							$history_id = AddToHistory($p,$agent_id,$u_id,44,$_POST['fio'][$p],$_POST['date_delivery'][$p]);
							$short_history_id = AddToShortHistory($p,$u_id,72,$_POST['fio'][$p],$_POST['date_delivery'][$p]);
							if (strlen($_POST['cur_fio'][$p]))
							{
								$from_txt = GetMessage("BY_COURIER", array("#FIO#" => $_POST['cur_fio'][$p]));
							}
							if (strlen($_POST['pvz_name'][$p]))
							{
								$from_txt = GetMessage("BY_PVZ", array("#PVZ#" => $_POST['pvz_name'][$p]));
							}
							$arSendsParams = array(
								"ID" => $_POST['id_in'][$p],
								"ID_LINK" => $p,
								"DATE_RES" => $_POST['date_delivery'][$p],
								"ID_MESS" => $qw,
								"FROM" => $from_txt,
								"RESIVER" => $_POST['fio'][$p],
								"N_ZAKAZ" => $_POST['id_number'][$p],
								"LINK_TO_MESS" => ""
							);
							$qw = SendMessageInSystem(
								$u_id, 
								$agent_array['id'], 
								$_POST['shop'][$p], 
								GetMessage("ISSUED_BY_COURIER_TITLE", array("#NUMBER#" => $_POST['id_in'][$p])), 
								152, 
								'', 
								'', 
								166,
								$arSendsParams
							);
							$arSendsParams['LINK_TO_MESS'] = GetMessage('LINK_TO_MESS', array('#ID#' => $qw));
							SendMessageMailNew($_POST['shop'][$p], $_POST['agent_id'][$p], 114, 166, $arSendsParams);
							$trans_id = AddTransaction(101,'', $_POST['agent_id'][$p], $u_id, $_POST['date_delivery'][$p],$_POST['summ'][$p],'',GetMessage("TRANS_NAME_1"),$p);
							$summ_to_agents[$_POST['agent_id'][$p]] = $summ_to_agents[$_POST['agent_id'][$p]] + $_POST['summ'][$p];
							$arResult['MESSAGE'][] = GetMessage("MESS_PACK_DELIVERED", array("#ID#" => $p, "#NUMBER#" => $_POST['id_in'][$p]));
						}
						else
						{
							$vozvr = (intval($_POST['return_yes'][$p]) == 1) ? 1 : 0;
							$summ_vozvr = (intval($_POST['summ_return'][$p]) > 0) ? $_POST['summ_return'][$p] : 0;
							/*
							CIBlockElement::SetPropertyValuesEx($p, 42, array(
								203 => 40, 
								229 => 69, 
								204 => false, 
								337 => false, 
								442 => 1, 
								250 => $_POST['summ_delivery'][$p], 
								231 => $_POST['summ_rate'][$p],
								444 => $vozvr,
								445 => $summ_vozvr,
								479 => $_POST['summ'][$p]
							));
							*/
							CIBlockElement::SetPropertyValuesEx($p, 42, array(
								442 => 1,
								444 => $vozvr,
								445 => $summ_vozvr
							));
							$history_id = AddToHistory($p, $agent_id, $u_id, 40, $arParams["STATUS"][$oper], $_POST['date_delivery'][$p]);
							$short_history_id = AddToShortHistory($p, $u_id, 69, $arParams["STATUS"][$oper], $_POST['date_delivery'][$p]);
							$arSendsParams = array(
								"ID_PACK" => $p,
								"DATE_STATUS"=> $_POST['date_delivery'][$p],
								"ORDER" => $_POST['id_in'][$p],
								"STATUS" => $arParams["STATUS"][$oper],
								'LINK_TO_MESS' => ''
							);
							$qw = SendMessageInSystem(
								$u_id,
								$agent_array['id'], 
								$_POST['shop'][$p], 
								GetMessage("EXCEPTIONAL_SITUATION_TITLE", array("#NUMBER#" => $_POST['id_in'][$p])), 
								191, 
								'', 
								'', 
								162,
								$arSendsParams
							);
							$arSendsParams['LINK_TO_MESS'] = GetMessage('LINK_TO_MESS', array('#ID#' => $qw));
							SendMessageMailNew($_POST['shop'][$p], $_POST['agent_id'][$p], 115, 162, $arSendsParams);
							$arResult['MESSAGE'][] = GetMessage("MOVED_TO_WAREHOUSE", array("#ID#" => $p, "#NUMBER#" => $_POST['id_in'][$p]));
						}
					}
					else
					{
						if (intval($_POST['return_yes'][$p]) == 1)
						{
							CIBlockElement::SetPropertyValuesEx($p, 42, array(
								442 => 1,
								444 => 1,
								445 => (intval($_POST['summ_return'][$p]) > 0) ? $_POST['summ_return'][$p] : 0
							));
							$arResult['MESSAGE'][] = GetMessage("MARKED_AS_RETURNED", array("#ID#" => $p, "#NUMBER#" => $_POST['id_in'][$p]));
						}
					}
				}
				foreach ($summ_to_agents as $agent => $summ_upload)
				{
					if ($summ_upload > 0)
					{
						$db_props = CIBlockElement::GetProperty(40, $agent, array("sort" => "asc"), Array("CODE"=>"ACCOUNT"));
						if($ar_props = $db_props->Fetch())
						{
							$summ_agent = $ar_props["VALUE"];
						}
						else
						{
							$summ_agent = 0;
						}
						$summ_agent = $summ_agent + $summ_upload;
						CIBlockElement::SetPropertyValuesEx($agent, 40, array(219 => $summ_agent));
						$arResult['MESSAGE'][] = GetMessage("MESS_BALANCE", array("#SUMM#"=>CurrencyFormat($summ_agent,"RUU")));
					}
				}
			}
		}
		
		/*
		if (isset($_POST['test_save']))
		{
			if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
			{
				$_POST = array();
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST['key_session']] = $_POST['rand'];
			}
		}
		*/

		$arResult["AGENTS"] = AvailableAgents(true, $agent_array['id']);
		$arResult["TITLE"] = GetMessage("TTL_PODS");
		$arResult["CURRENT_AGENT_ID"] = $agent_array['id'];
		$arResult["CURRENT_AGENT_NAME"] = $agent_array['name'];
		if (intval($_GET['agent']) > 0)
		{
			$mans_ids = array();
			$mans = ManifestsToAgent($_GET['agent'], 0, false, false, false);
			foreach ($mans as $m)
			{
				$mans_ids[] =  $m["ID"];
			}
			if (count($mans_ids) > 0)
			{
				$arResult["LIST"] = GetListPackeges($mans_ids, array("ID"=>"DESC"), array(40,43,79,46,56), false, false, 0, $_GET['number'], true, true, $agent_array["id"], $_GET['exceptions'],'N');
				$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
				unset($arResult["LIST"]["NAV_STRING"],$arResult["LIST"]["COUNT"]);
			}
			else
			{
				$arResult['LIST'] = array();
			}
		}
		else
		{
			$mans_ids = 0;
			$arResult["LIST"] = GetListPackeges($mans_ids, array("ID"=>"DESC"), array(40,43,79,46,56), false, false, 0, $_GET['number'], true, true, $agent_array["id"], $_GET['exceptions'],'N');
			$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
			unset($arResult["LIST"]["NAV_STRING"],$arResult["LIST"]["COUNT"]);
		}
	}
	
	/************реестр заказов на доставку************/
	if ($mode == 'register')
	{
		$arLang['SHOP'] = '';
		$arLang['DATE'] = 'Реестр заказов на доставку от '.date('d.m.Y H:i');
		$arLang['FIELDS'] = array('№', 'Номер заказа', 'Вес', 'К оплате получателем');
		$pack_ids = false;
		$state = 54;
		if (strlen($_GET['ids']))
		{
			$ids = explode(',',$_GET['ids']);
			if (count($ids) > 0)
			{
				$pack_ids = $ids;
				$state = false;
			}
		}
		$arLang['LIST'] = GetListOfPackeges($agent_array, 0, $pack_ids, $state, false, 0, false, 0, 0, '', '', 0, false);
		$arLang['NAME_FILE'] = $arLang['DATE'];
		$arLang['SHOPS'] = array();
		foreach ($arLang['LIST'] as $p)
		{
			if (intval($_GET['shop']) > 0)
			{
				if (intval($_GET['shop']) == $p['PROPERTY_CREATOR_VALUE'])
				{
					$arLang['SHOPS'][$p['PROPERTY_CREATOR_VALUE']]['NAME'] = $p['PROPERTY_CREATOR_NAME'];
					$arLang['SHOPS'][$p['PROPERTY_CREATOR_VALUE']]['PACKS'][] = $p;
				}
			}
			else
			{
				$arLang['SHOPS'][$p['PROPERTY_CREATOR_VALUE']]['NAME'] = $p['PROPERTY_CREATOR_NAME'];
				$arLang['SHOPS'][$p['PROPERTY_CREATOR_VALUE']]['PACKS'][] = $p;
			}
		}
		$a = MakeRegisterPDF($arLang, 'D', true);
	}
	
	/****************манифест на заказ*****************/
	if ($mode == 'package_manifest')
	{
		$arResult['yest'] = strtotime('+1 day');
		$arResult['start'] = date('d.m.Y');
		$arResult['end'] = date('d.m.Y');
		
		$res = CIBlockElement::GetList(array("ID"=>"ASC"),array("IBLOCK_ID" => 42,"ID" => intval($_GET['id'])),false, false, array(
			"ID",
			"DATE_CREATE",
			"PROPERTY_STATE_VALUE",
			"PROPERTY_SIZE_1",
			"PROPERTY_SIZE_2",
			"PROPERTY_SIZE_3",
			"PROPERTY_CONDITIONS",
			"PROPERTY_ADRESS",
			"PROPERTY_CREATOR",
			"PROPERTY_WHEN_TO_DELIVER",
			"PROPERTY_PREFERRED_TIME",
			"PROPERTY_N_ZAKAZ_IN",
			"PROPERTY_PLACES",
			"PROPERTY_WEIGHT",
			"PROPERTY_CREATOR",
			"PROPERTY_CREATOR.NAME",
			"PROPERTY_CITY.NAME",
			"PROPERTY_RECIPIENT",
			"PROPERTY_PHONE",
			"PROPERTY_COST_2",
			"PROPERTY_DELIVERY_LEGAL",
			"PROPERTY_PAY_FOR_REFUSAL"
		));
		if ($ob = $res->GetNextElement())
		{
			$orders[] = $ob->GetFields();
			$arShop = GetCompany($orders[0]['PROPERTY_CREATOR_VALUE']);
			$arResult["Cells_1"] = array(
				'ПОЛУЧАТЕЛЬ:' => htmlspecialcharsBack($arShop['PROPERTY_UK_NAME']),
				'ГОРОД ПРИБЫТИЯ:' => $arShop['PROPERTY_UK_CITY'],
				"ПЕРЕВОЗЧИК:" => "",
				"ДАТА ОТПРАВЛЕНИЯ:" => $arResult['start'],
				"РАСЧЕТНАЯ ДАТА ПРИБЫТИЯ:" => $arResult['end'],
				"ПЕРЕВОЗОЧНЫЙ ДОКУМЕНТ:" => "",
				"ПЕРЕВОЗОЧНЫХ МЕСТ:" => ""
			);
			$arResult["Cells_3"][] = array(
				'',
				'Накладная',
				'Мест',
				'Вес',
				'Объемный вес',
				'РДД',
				'Отправитель',
				'Город получателя',
				'Получатель',
				'Адрес получателя',
				'Телефон получателя',
				'Специальные инструкции',
				'Город отправителя',
				'Адрес отправителя',
				'Телефон отправителя',
				'Сумма к оплате',
				'ID обмена'
			);
			$s_w = $s_ob_w = $s_p = 0;
			foreach ($orders as $v)
			{
				$s1 = (floatval($v["PROPERTY_SIZE_1_VALUE"]) > 0) ? $v["PROPERTY_SIZE_1_VALUE"] : 0;
				$s2 = (floatval($v["PROPERTY_SIZE_2_VALUE"]) > 0) ? $v["PROPERTY_SIZE_2_VALUE"] : 0;
				$s3 = (floatval($v["PROPERTY_SIZE_3_VALUE"]) > 0) ? $v["PROPERTY_SIZE_3_VALUE"] : 0;
				$ob_w = number_format((($s1 * $s2 * $s3) / 6000), 3, '.', '');
				$adr = ($v["PROPERTY_CONDITIONS_ENUM_ID"] == 37) ? $v["PROPERTY_ADRESS_VALUE"] : $v["PROPERTY_CONDITIONS_VALUE"];
				$instr = '';
				if (strlen($v["PROPERTY_WHEN_TO_DELIVER_VALUE"]))
				{
					$instr = 'Доставить '.$v["PROPERTY_WHEN_TO_DELIVER_VALUE"];
				}
				if (strlen($v["PROPERTY_PREFERRED_TIME_VALUE"]["TEXT"]))
				{
					if (strlen($instr))
					{
						$instr .= ' ';
					}
					$instr .= $v["PROPERTY_PREFERRED_TIME_VALUE"]["TEXT"];
				}
				if ($v["PROPERTY_DELIVERY_LEGAL_VALUE"] == 1)
				{
					if (strlen($instr))
					{
						$instr .= ' ';
					}
					$instr .= "Доставка юр. лицу: необходимо подписать товарную накладную.";
				}
				if ($v["PROPERTY_PAY_FOR_REFUSAL_VALUE"] == 1)
				{
					if (strlen($instr))
					{
						$instr .= ' ';
					}
					$instr .= "Стоимость доставки взимается независимо от принятия заказа получателем.";
				}
				
				$phone = $city = $adr_shop = $idshop = '';
				$db_props = CIBlockElement::GetProperty(40, $v["PROPERTY_CREATOR_VALUE"], array("sort" => "asc"), array("CODE"=>"CITY"));
				if ($ar_props = $db_props->Fetch())
				{
					$city_id = $ar_props["VALUE"];
					$res = CIBlockElement::GetByID($city_id);
					if($ar_res = $res->GetNext())
						$city = $ar_res['NAME'];
				}
				$db_props = CIBlockElement::GetProperty(40, $v["PROPERTY_CREATOR_VALUE"], array("sort" => "asc"), array("CODE"=>"ADRESS"));
				if ($ar_props = $db_props->Fetch())
				{
					$adr_shop = $ar_props["VALUE"];
				}
				$db_props = CIBlockElement::GetProperty(40, $v["PROPERTY_CREATOR_VALUE"], array("sort" => "asc"), array("CODE"=>"PHONES"));
				if ($ar_props = $db_props->Fetch())
				{
					$phone = $ar_props["VALUE"];
				}
				$db_props = CIBlockElement::GetProperty(40, $v["PROPERTY_CREATOR_VALUE"], array("sort" => "asc"), array("CODE"=>"INN"));
				if ($ar_props = $db_props->Fetch())
				{
					$idshop = $ar_props["VALUE"];
				}
				
				$arResult["Cells_3"][] = array(
					'',
					$v["PROPERTY_N_ZAKAZ_IN_VALUE"],
					$v["PROPERTY_PLACES_VALUE"],
					$v["PROPERTY_WEIGHT_VALUE"],
					$ob_w,
					'',
					$v["PROPERTY_CREATOR_NAME"],
					$v["PROPERTY_CITY_NAME"],
					$v["PROPERTY_RECIPIENT_VALUE"],
					$adr,
					$v["PROPERTY_PHONE_VALUE"],
					$instr,
					$city,
					$adr_shop,
					$phone,
					$v["PROPERTY_COST_2_VALUE"],
					$idshop
				);
				$s_w = $s_w + $v["PROPERTY_WEIGHT_VALUE"];
				$s_ob_w = $s_ob_w + $ob_w;
				$s_p = $s_p + $v["PROPERTY_PLACES_VALUE"];
				
				CIBlockElement::SetPropertyValuesEx($v['ID'], 42, array(434 => 0));
			}
			$arResult["Cells_3"][] = array(
				'',
				'',
				$s_p,
				$s_w,
				$s_ob_w,
			);
			$arResult["Cells_2"] = array(
				'Отправитель: Новый Партнер',
				'Манифест подготовил: Смирнова Дарья',
				'Дата создания манифеста: '.$arResult['start'],
				'',
				'ВСЕГО ОТПРАВЛЕНИЙ ПО МАНИФЕСТУ: '.count($orders),
				'ВЕС ПО МАНИФЕСТУ: '.$s_w.' КГ',
				'ОБЪЕМНЫЙ ВЕС ПО МАНИФЕСТУ: '.$s_ob_w.' КГ('. number_format((pow($s_ob_w, 1/3)), 3, '.', '').' Куб.М.)'
			);
		}
	}
	
	/*********************возвраты*********************/
	if ($mode == 'returns')
	{
		if(isset($_POST['save']))
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
						CIBlockElement::SetPropertyValuesEx($p, 42, array(
							203 => 223, 
							229 => 224, 
							499 => $_POST['shop_id'][$p],
							445 => $_POST['summ_return'][$p],
							504 => 1,
							498 => substr($_POST['date_delivery'][$p], 0, 10)
						));
						$add_to_period = AddElementToPeriod($p, 284, $u_id);
						$history_id = AddToHistory($p, $agent_array['id'], $u_id, 223, $_POST['fio'][$p], $_POST['date_delivery'][$p]);
						$short_history_id = AddToShortHistory($p, $u_id, 224, $_POST['fio'][$p], $_POST['date_delivery'][$p]);
						$arResult["MESSAGE"][] = '<a href="/warehouse/index.php?mode=package&id='.$p.'">Заказ №'.$_POST['id_in'][$p].'</a> выдан отправителю';
					}
				}
				else
				{
					$arResult["ERRORS"][] = 'Не выбран ни один заказ';
				}
			}
		}
		$arResult["LIST"] = GetListReturns($agent_array['id'], true, false, true, trim($_GET['number']));
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"],$arResult["LIST"]["COUNT"]);
	}
}

/**************************************************/ 
/************************ИМ************************/
/**************************************************/

if ($agent_array['type'] == 52)
    //$arResult["DEMO"] = false;
{
	$modes = array(
		'list',
		'package',
		'makepackage',
		'makepackageofgoods',
		'print_labels',
		'package_edit',
		'makepackage_old',
		'package_edit_old',
		'register'
	);
	  //dump($modes);
      //dump($agent_array);
    if($agent_array['demo']){
        $arResult["DEMO"] = true;
    }else{
        $arResult["DEMO"] = false;
    }
    $arResult["MENU"] = array(
		"list" => GetMessage("TTL_LIST_PACK")
	);




	$arResult["BUTTONS"] = array(
		"upload" => array(
			"in_mode" => array("list"),
			"title" => GetMessage("UPLOAD_ORDERS_BTN"),
			"link" => "/warehouse/orders.php"
		),
		'makepackage' => array(
			"in_mode" => array("list"),
			"title" => GetMessage("NEW_ORDER_BTN"),
			"link" => "/warehouse/index.php?mode=makepackage"
		)
	);
	//dump( $arParams);
	foreach ($arResult["MENU"] as $m => $name)
	{
		//dump($m);

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
		$componentPage = "shop_".$mode;
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
	{
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	}
	
	foreach ($modes as $m)
	{
		$arResult["PERM"] = $arParams['PERM'][$agent_array['type']][$m][$arResult['ROLE_USER']];
		if ($arResult["PERM"] == "C")
			unset($arResult["BUTTONS"][$m]);
	}
	
	$arResult["RATE"] = WhatIsRate($agent_id);
	$arResult["PRICE"] = WhatIsPrice($agent_id);
	$arResult["PRICE_2"] = WhatIsPrice($agent_id, 2);
	//dump($arResult);

    /**************редактирование заказа***************/
	if ($mode == 'package_edit')
	{
		$arResult['time_periods'] = array();
		$db_enum_list = CIBlockProperty::GetPropertyEnum(493, array("SORT"=>"asc"), array("IBLOCK_ID"=> 42));
		while($ar_enum_list = $db_enum_list->GetNext())
		{
			$arResult['time_periods'][$ar_enum_list["ID"]] = $ar_enum_list["VALUE"];
		}
		if (isset($_POST['save']))
		{
			if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
			{
				$_POST = array();
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST['key_session']] = $_POST['rand'];
				$massiv_to_change = array();
				$pack_id = intval($_POST['pack_id']);
				$weight = floatval(str_replace(',','.',$_POST['PROPERTY_WEIGHT_VALUE']));
				$size_1 = floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_1_VALUE']));
				$size_2 = floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_2_VALUE']));
				$size_3 = floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_3_VALUE']));
				$cost_1 = floatval(str_replace(',','.',$_POST['PROPERTY_COST_1_VALUE']));
				$cost_2 = floatval(str_replace(',','.',$_POST['PROPERTY_COST_2_VALUE']));
				$cost_3 = floatval(str_replace(',','.',$_POST['PROPERTY_COST_3_VALUE']));
				$cost_goods = floatval(str_replace(',','.',$_POST['PROPERTY_COST_GOODS_VALUE']));
				if (!strlen($_POST['PROPERTY_N_ZAKAZ_VALUE']))
				{
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_1");
				}
				else
				{
					$massiv_to_change[196] = $_POST['PROPERTY_N_ZAKAZ_VALUE'];
				}
				if ($cost_goods < 0)
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_12");
				else 
					$massiv_to_change[307] = $cost_goods;
				if ($cost_3 < 0)
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_13");
				else 
					$massiv_to_change[199] = $cost_3;	
				if ($cost_2 < 0)
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_2");
				else 
					$massiv_to_change[198] = $cost_2;
				if ($cost_1 < 0)
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_3");
				else
					$massiv_to_change[197] = $cost_1;
				if ($weight < 0)
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_5");
				else
					$massiv_to_change[225] = $weight;
				if (intval($_POST['PROPERTY_PLACES_VALUE']) < 1)
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_6");
				else
					$massiv_to_change[232] = intval($_POST['PROPERTY_PLACES_VALUE']);
				if (!strlen($_POST['PROPERTY_RECIPIENT_VALUE']))
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_7");
				else
					$massiv_to_change[208] = trim($_POST['PROPERTY_RECIPIENT_VALUE']);
				if (!strlen($_POST['PROPERTY_PHONE_VALUE']))
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_8");
				else
					$massiv_to_change[209] = $_POST['PROPERTY_PHONE_VALUE'];
				if ($_POST['PROPERTY_CONDITIONS_ENUM_ID'] == 37)
				{
					if (!strlen(trim($_POST['PROPERTY_ADRESS_VALUE'])))
						$arResult['ERRORS'][] = GetMessage("ERR_STRING_10");
					else 
						$massiv_to_change[202] = NewQuotes($_POST['PROPERTY_ADRESS_VALUE']);
				}
				else
				{
					$massiv_to_change[202] = false;
				}
				$massiv_to_change[201] = $_POST['PROPERTY_CONDITIONS_ENUM_ID'];
				$massiv_to_change[390] = '';
				if (strlen($_POST['date_deliv']))
				{
					$massiv_to_change[390] = DateFF($_POST['date_deliv']);
				}
				if (strlen($massiv_to_change[390]))
				{
					$massiv_to_change[390].= ' ';
				}
				$massiv_to_change[390] .= $arResult['time_periods'][$_POST['TIME_PERIOD']];
				/*
				if (strlen($_POST['timedeliv'][0]))
				{
					if (strlen($massiv_to_change[390]))
						$massiv_to_change[390] .= ' ';
					$massiv_to_change[390] .= GetMessage("FROM", array("#TIME#" => $_POST['timedeliv'][0]));
				}
				if (strlen($_POST['timedeliv'][1]))
				{
					if (strlen($massiv_to_change[390]))
						$massiv_to_change[390] .= ' ';
					$massiv_to_change[390] .=  GetMessage("TO", array("#TIME#" => $_POST['timedeliv'][1]));
				}
				*/
				if ($_POST['take_provider'] == 1)
				{
					$massiv_to_change[391] = 174;
				}
				else
				{
					$massiv_to_change[391] = false;
				}
				if (!strlen($_POST['PROPERTY_CITY']))
				{
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_9");
				}
				else
				{
					$get_city_id = GetCityId($_POST['PROPERTY_CITY']);
					$massiv_to_change[212] = $get_city_id;
					if ($_POST['PROPERTY_CONDITIONS_ENUM_ID'] == 38)
					{
						$pr = $arResult["PRICE_2"];
					}
					else
					{
						$pr = $arResult["PRICE"];
					}
					if ($_POST['urgent'] == 2)
					{
						$urgent = 2;
						$urgent_db = 172;
					}
					else
					{
						$urgent = 1;
						$urgent_db =  false;
					}
					$have_city = CheckCityToHave($get_city_id,$pr,$weight,$size_1,$size_2,$size_3,$urgent,0);
					if (!$have_city["LOG"])
					{
						$massiv_to_change[250] = 0;
						$arResult['WARNINGS'][] = $have_city["TEXT"];
						$massiv_to_change[203] = 80;
						$massiv_to_change[229] = 81;
					//	if ($_POST['message'] == 1)
					//	{
						if (intval($get_city_id) > 0)
						{
							$full_city_name = GetFullNameOfCity($get_city_id);
							$qw = SendMessageInSystem($u_id, $agent_id, $agent_array['uk'], GetMessage("ADD_CITY_TITLE"), 83, $get_city_id, $full_city_name);
							SendMessageMailNew($agent_array['uk'], $agent_id, 97,165,array("CITY"=>$full_city_name,"ID_MESS"=>$qw));
						}
					//	}
					}
					else
					{
						$massiv_to_change[250] = $have_city["COST"];
						$massiv_to_change[203] = 39;
						$massiv_to_change[229] = 66;
					}
				}
				$massiv_to_change[247] = $size_1;
				$massiv_to_change[248] = $size_2;
				$massiv_to_change[249] = $size_3;
				$massiv_to_change[339] = array("VALUE" => array ("TEXT" => $_POST['PROPERTY_PREFERRED_TIME_VALUE'], "TYPE" => "text"));
				$massiv_to_change[313] = $_POST['PROPERTY_CASH_VALUE'];
				$massiv_to_change[231] = $_POST['PROPERTY_RATE_VALUE'];
				$massiv_to_change[376] = $urgent_db;
				$massiv_to_change[405] = $have_city["COST_ZABOR"];
				$massiv_to_change[446] = ($_POST['to_legal'] == 1) ? 1 : 0;
				$massiv_to_change[478] = ($_POST['refusal'] == 1) ? 1 : 0;
				$massiv_to_change[493] = $_POST['TIME_PERIOD'];
				
				$arJsonGoods = array();
				foreach ($_POST['goods'] as $goods_str)
				{
					$arJsonGoods[] = array(
						'GoodsName' => iconv('windows-1251','utf-8',$goods_str['name']),
						'Amount' => intval($goods_str['amount']),
						'Price' => floatval(str_replace(',','.',$goods_str['price'])),
						'Sum' => floatval(str_replace(',','.',$goods_str['sum'])),
						'SumNDS' => floatval(str_replace(',','.',$goods_str['sumnds'])),
						'PersentNDS' => intval($goods_str['persentnds'])
					);
				}
				$massiv_to_change[754] = json_encode($arJsonGoods);
				
				CIBlockElement::SetPropertyValuesEx($pack_id, 42, $massiv_to_change);
				$zabors_check_delete = array();
				$res_zabors = CIBlockElement::GetList(array("ID"=>"ASC"), array("IBLOCK_ID" => 77,"PROPERTY_429" => $pack_id), false, false, array("ID"));
				while($ob_zabors = $res_zabors->GetNextElement())
				{
					$z_el_info = $ob_zabors->GetFields();
					$zabors_check_delete[] = $z_el_info["ID"];
				}
				foreach ($zabors_check_delete as $r)
				{
					CIBlockElement::SetPropertyValuesEx($r, 77, array(429 => false));
				}
				foreach ($_POST['request_el'] as $r)
				{
					CIBlockElement::SetPropertyValuesEx($r, 77, array(429 => $pack_id));
				}
				$arResult['MESSAGE'][] = GetMessage("MESS_PACK_CHANGE", array("#ID#" => $pack_id, "#NUMBER#" => $_POST['id_in']));
				
				if (count($arResult['ERRORS']) == 0)
				{
					$_SESSION['MESSAGE'][] = GetMessage("MESS_PACK_CHANGE", array("#ID#" => $pack_id, "#NUMBER#" => $_POST['id_in']));
					LocalRedirect("/warehouse/");
				}
			}		
		}
		
		$arResult['PACK'] = GetOnePackage(intval($_GET['id']), $agent_array['id'], $agent_array['type']);
		if ($arResult['PACK'])
		{
			if (!in_array($arResult['PACK']['PROPERTY_STATE_ENUM_ID'], array(39,57,80)))
			{
				LocalRedirect("/warehouse/index.php?mode=package&id=".$arResult['PACK']["ID"]);
			}
			$arResult["TITLE"] = GetMessage("TTL_PACK", array("#ID#" => $arResult['PACK']["PROPERTY_N_ZAKAZ_IN_VALUE"]));
			$arResult["SHOP_ID"] = $agent_array['id'];
			$arResult["REQUESTS"] = array(0 => '');
			$r_s = GetListRequests($arResult["SHOP_ID"],false,true, array("ID"=>"DESC"), 181, array(false,183,185,186), 'no');
			foreach ($r_s as $r)
			{
				$arResult["REQUESTS"][$r["ID"]] = $r["PROPERTY_NUMBER_VALUE"];
			}
			$db_props = CIBlockElement::GetProperty(40, $arResult["SHOP_ID"], array("sort" => "asc"), array("ID" => 492));
			$ar_props = $db_props->Fetch();
			$arResult['CONDITIONS_IM'] = $ar_props["VALUE"];
		}
		else
		{
			$arResult["TITLE"] = GetMessage("TTL_PACK_NOT");
		}
	}
	
	/****************оформление заказа*****************/
	if ($mode == 'makepackage')
	{
		$arResult['zabors'] =  array(0 => 0);
		$arResult['time_periods'] = array();
		$db_enum_list = CIBlockProperty::GetPropertyEnum(493, array("SORT"=>"asc"), array("IBLOCK_ID"=> 42));
		while($ar_enum_list = $db_enum_list->GetNext())
		{
			$arResult['time_periods'][$ar_enum_list["ID"]] = $ar_enum_list["VALUE"];
		}
		if(isset($_POST['make_package']))
		{
			if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
			{
				$_POST = array();
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{	
				$_SESSION[$_POST['key_session']] = $_POST['rand'];
				$draft = false;
				if (!strlen($_POST['n_zakaz']))
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_1");
				if (floatval(str_replace(',','.',$_POST['cost_2'])) < 0)
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_2");
				if (floatval(str_replace(',','.',$_POST['PROPERTY_COST_GOODS_VALUE'])) < 0)
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_12");
				if (floatval(str_replace(',','.',$_POST['cost_1'])) < 0)
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_3");
				if (floatval(str_replace(',','.',$_POST['weight'])) <= 0)
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_5");
				if (intval($_POST['places']) < 1)
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_6");
				if (!strlen($_POST['recipient']))
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_7");
				if (!strlen($_POST['phone']))
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_8");
				$when_delivery = '';
				if (strlen($_POST['date_deliv']))
				{
					$when_delivery = DateFF($_POST['date_deliv']);
				}
				if (strlen($when_delivery))
				{
					$when_delivery .= ' ';
				}
				$when_delivery .= $arResult['time_periods'][$_POST['TIME_PERIOD']];
				if ($_POST['take_provider'] == 1)
				{
					$take_provider = 174;
					$arResult['zabors'] = $_POST['zabor'];
				}
				else
				{
					$take_provider = false;
				}
				if ($_POST['conditions'] == 0)
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_11");
				if (!strlen($_POST['city']))
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_9");
				if ((strlen($_POST['city']) > 0) && ($_POST['conditions'] > 0))
				{
					$get_city_id = GetCityId($_POST['city']);
					$name_of_city = GetFullNameOfCity($get_city_id);
					if ($_POST['conditions'] == 38)
					{
						$pr = $arResult["PRICE_2"];
					}
					else
					{
						$pr = $arResult["PRICE"];
					}
					
					if ($_POST['urgent'] == 2)
					{
						$urgent = 2;
						$urgent_db = 172;
					}
					else
					{
						$urgent = 1;
						$urgent_db =  false;
					}
					
					$have_city = CheckCityToHave(
						$get_city_id,
						$pr,
						floatval(str_replace(',','.',$_POST['weight'])),
						floatval(str_replace(',','.',$_POST['size_1'])),
						floatval(str_replace(',','.',$_POST['size_2'])),
						floatval(str_replace(',','.',$_POST['size_3'])),
						$urgent,
						0
					);
					if(!$have_city["LOG"])
					{
						$draft = true;
					}
					else
					{
						$arResult["INFO"] = $have_city["TEXT"];
					}
				}
				if (!strlen($_POST['adress']) && ($_POST['conditions'] == 37))
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_10");
				
				if (count($arResult['ERRORS']) == 0)
				{
					if (($draft) && (intval($get_city_id) > 0))
					{
						$qw = SendMessageInSystem($u_id, $agent_id, $agent_array['uk'], GetMessage("ADD_CITY_TITLE"), 83, $get_city_id, $name_of_city);
						SendMessageMailNew($agent_array['uk'], $agent_id, 97, 165, array("CITY" => $name_of_city, "ID_MESS" => $qw));
					}
					
					$cost_full = floatval(str_replace(',','.',$_POST['cost_2']));
					$cost_goods = floatval(str_replace(',','.',$_POST['PROPERTY_COST_GOODS_VALUE']));
					$calc_rate = 0;
					if ($_POST['CONDITIONS_IM'] == 214)
					{
						$price_key = ($cost_full > 0) ? $have_city['PERSENT_1'] : $have_city['PERSENT_2'];
						$pers = $arResult["RATE"][$price_key];
						$cost_full =  ($cost_full > 0) ? $cost_full : $cost_goods;
						$r = $cost_full*$pers/100;
						$calc_rate = number_format($r, 2, '.', '');
					}
					else
					{
						$price_key = ($_POST["PROPERTY_CASH_VALUE"] == 125) ? $have_city['PERSENT_2'] : $have_city['PERSENT_1'];
						$pers = $arResult["RATE"][$price_key];
						$r = $cost_full*$pers/100;
						$calc_rate = number_format($r, 2, '.', '');
					}
					
					$arJsonGoods = array();
					foreach ($_POST['goods'] as $goods_str)
					{
						$arJsonGoods[] = array(
							'GoodsName' => iconv('windows-1251','utf-8',$goods_str['name']),
							'Amount' => intval($goods_str['amount']),
							'Price' => floatval(str_replace(',','.',$goods_str['price'])),
							'Sum' => floatval(str_replace(',','.',$goods_str['sum'])),
							'SumNDS' => floatval(str_replace(',','.',$goods_str['sumnds'])),
							'PersentNDS' => intval($goods_str['persentnds'])
						);
					}
					
					$max_id_5 = GetMaxIDIN(42,5,true,213,$agent_id);
					$name_of_order = MakeOrderId($agent_id, $max_id_5);
					$el = new CIBlockElement;
					$PROP = array();
					$PROP[212] = $get_city_id;
					$PROP[196] = $_POST['n_zakaz'];
					$PROP[197] = floatval(str_replace(',','.',$_POST['cost_1']));
					$PROP[198] = floatval(str_replace(',','.',$_POST['cost_2']));
					$PROP[199] = floatval(str_replace(',','.',$_POST['cost_3']));
					$PROP[225] = floatval(str_replace(',','.',$_POST['weight']));
					$PROP[201] = $_POST['conditions'];
					$PROP[202] = NewQuotes($_POST['adress']);
					$PROP[339] = array("VALUE" => array ("TEXT" => $_POST['time'], "TYPE" => "text"));
					$PROP[203] = 39;
					$PROP[208] = trim($_POST['recipient']);
					$PROP[209] = $_POST['phone'];
					$PROP[213] = $agent_id;
					$PROP[229] = 66;
					// $PROP[231] = $_POST['PROPERTY_RATE_VALUE'];
					$PROP[231] = $calc_rate;
					$PROP[232] = intval($_POST['places']);
					$PROP[247] = floatval(str_replace(',','.',$_POST['size_1']));
					$PROP[248] = floatval(str_replace(',','.',$_POST['size_2']));
					$PROP[249] = floatval(str_replace(',','.',$_POST['size_3']));
					$PROP[250] = $have_city["COST"];
					if ($draft)
					{
						$PROP[203] = 80;
						$PROP[229] = 81;
					}
					$PROP[240] = 0;
					$PROP[257] = 91;
					$PROP[218] = 60;
					$PROP[313] = $_POST["PROPERTY_CASH_VALUE"];
					$PROP[307] = floatval(str_replace(',','.',$_POST['PROPERTY_COST_GOODS_VALUE']));
					$PROP[306] = $max_id_5;
					$PROP[346] = GetMessage("CASH");
					$PROP[347] = 0;
					$PROP[376] = $urgent_db;
					$PROP[390] = $when_delivery;
					$PROP[391] = $take_provider;
					$PROP[392] = $when_take;
					$PROP[393] = array("VALUE" => array ("TEXT" => $_POST['take_comment'], "TYPE" => "text"));
					$PROP[402] = $name_of_order;
					$PROP[405] = $have_city["COST_ZABOR"];
					$PROP[446] = ($_POST['to_legal'] == 1) ? 1 : 0;
					$PROP[478] = ($_POST['refusal'] == 1) ? 1 : 0;
					$PROP[493] = $_POST['TIME_PERIOD'];
					$PROP[499] = $agent_array['id'];
					$PROP[754] = json_encode($arJsonGoods);
					$arLoadProductArray = array(
						"MODIFIED_BY" => $u_id, 
						"IBLOCK_SECTION_ID" => false,
						"IBLOCK_ID" => 42,
						"PROPERTY_VALUES" => $PROP,
						"NAME" => GetMessage("ORDER", array("#NUMBER#" => $name_of_order)),
						"ACTIVE" => "Y"
					);
					if ($PRODUCT_ID = $el->Add($arLoadProductArray))
					{
						foreach ($_POST['request_el'] as $r)
						{
							CIBlockElement::SetPropertyValuesEx($r, 77, array(429 => $PRODUCT_ID));
						}
						$arResult['MESSAGE'][] = GetMessage("MESS_PACK_CREATE",array("#N_ZAKAZ#" => $name_of_order,"#ID#" => $PRODUCT_ID));
						$_POST = array();
					}
					else
						$arResult['ERRORS'][] = $el->LAST_ERROR;
						
				}
			}
		}
		$arResult["TITLE"] = GetMessage("TTL_MAKE_PACK");
		$paks = GetListOfPackeges($agent_array,$agent_id);
		$arResult["COUNT"] = $paks["COUNT"];
		if ($agent_array['demo'] == GetMessage("YES"))
		{
			$arResult["DEMO"] = true;
		}
		else
		{
			$arResult["DEMO"] = false;
		}
		$arResult["LIMIT"] = GetSettingValue(256);
		if (($arResult["DEMO"]) && ($arResult["COUNT"] >= $arResult["LIMIT"]))
		{
			$arResult["ERRORS"][] = GetMessage("LIMIT_OVER");
		}
		$arResult["SHOP_DEFAULT"] = GetDefaultValuesForShop($agent_id);
		$arResult["SHOP_ID"] = $agent_array['id'];
		$arResult["REQUESTS"] = array(
			'0' => ''
		);
		$r_s = GetListRequests($arResult["SHOP_ID"],false,true, array("ID"=>"DESC"), 181, array(false,183,185,186), 'no');
		foreach ($r_s as $r)
		{
			$arResult["REQUESTS"][$r["ID"]] = $r["PROPERTY_NUMBER_VALUE"];
		}
		
		$db_props = CIBlockElement::GetProperty(40, $arResult["SHOP_ID"], array("sort" => "asc"), array("ID" => 492));
		$ar_props = $db_props->Fetch();
		$arResult['CONDITIONS_IM'] = $ar_props["VALUE"];

	}


	
	/**********формирование заказа с товарами**********/
	if ($mode == 'makepackageofgoods')
	{
		$arResult['time_periods'] = array();
		$db_enum_list = CIBlockProperty::GetPropertyEnum(493, array("SORT"=>"asc"), array("IBLOCK_ID"=> 42));
		while($ar_enum_list = $db_enum_list->GetNext())
		{
			$arResult['time_periods'][$ar_enum_list["ID"]] = $ar_enum_list["VALUE"];
		}
		if (isset($_POST['delete_goods']))
		{
			if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
			{
				$_POST = array();
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST['key_session']] = $_POST['rand'];
				foreach ($_POST['id_good_row'] as $del_g)
				{	
					CIBlockElement::Delete($del_g);
				}
				RecalculationWeightAndCost($_POST["pack_id"]);
			}
		}
		
		if (isset($_POST['for_order_form']))
		{
			if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
			{
				$_POST = array();
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST['key_session']] = $_POST['rand'];
				$sends = array();
				$id = intval($_POST['pack_id']);
				$massiv_to_change = array();
				$massiv_to_change[196] = trim($_POST['PROPERTY_N_ZAKAZ']);
				if (strlen($_POST['PROPERTY_COST_1_VALUE']))
				{
					if (floatval($_POST['PROPERTY_COST_1_VALUE']) < 0)
					{
						$arResult['ERRORS'][] = GetMessage("ERR_STRING_3");
					}
					else
					{
						$massiv_to_change[197] = floatval(str_replace(',','.',$_POST['PROPERTY_COST_1_VALUE']));
					}
				}
				else
				{
					$massiv_to_change[197] = 0;
				}
				if (intval($_POST['PROPERTY_PLACES_VALUE']) < 1)
				{
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_6");
				}
				else
				{
					$massiv_to_change[232] = intval($_POST['PROPERTY_PLACES_VALUE']);
				}
				if (!strlen($_POST['PROPERTY_RECIPIENT_VALUE']))
				{
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_7");
				}
				else
				{
					$massiv_to_change[208] = trim($_POST['PROPERTY_RECIPIENT_VALUE']);
				}
				if (!strlen($_POST['PROPERTY_PHONE_VALUE']))
				{
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_8");
				}
				else
				{
					$massiv_to_change[209] = $_POST['PROPERTY_PHONE_VALUE'];
				}
				if ($_POST['PROPERTY_CONDITIONS_ENUM_ID'] == 0)
				{
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_11");
				}
				else
				{
					$massiv_to_change[201] = $_POST['PROPERTY_CONDITIONS_ENUM_ID'];
				}
				if ($_POST['urgent'] == 2)
				{
					$urgent = 2;
					$urgent_db = 172;
				}
				else
				{
					$urgent = 1;
					$urgent_db =  false;
				}
				if (!strlen($_POST['PROPERTY_CITY']))
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_9");
				if ((strlen($_POST['PROPERTY_CITY'])) && ($_POST['PROPERTY_CONDITIONS_ENUM_ID'] > 0))
				{
					$get_city_id = GetCityId($_POST['PROPERTY_CITY']);
					$name_of_city = GetFullNameOfCity($get_city_id);
					if ($_POST['PROPERTY_CONDITIONS_ENUM_ID'] == 38)
					{
						$pr = $_POST['price_2'];
					}
					else
					{
						$pr = $_POST['price'];
					}
					$have_city = CheckCityToHave(
						$get_city_id,
						$pr,
						$_POST['PROPERTY_WEIGHT_VALUE'],
						$_POST['PROPERTY_SIZE_1_VALUE'],
						$_POST['PROPERTY_SIZE_2_VALUE'],
						$_POST['PROPERTY_SIZE_3_VALUE'],
						$urgent
					);
					if (!$have_city["LOG"])
					{
						$arResult['INFO'] = '<p class="orange">'.GetMessage("ERR_MAKE_1").'</p>';
						$massiv_to_change[250] = false;
						$massiv_to_change[212] = false;
					}
					else
					{
						$arResult["INFO"] = $have_city["TEXT"];
						$massiv_to_change[250] = $have_city['COST'];
						$massiv_to_change[212] = $get_city_id;
					}
				}
				if (!strlen($_POST['PROPERTY_ADRESS_VALUE']) && ($_POST['PROPERTY_CONDITIONS_ENUM_ID'] == 37))
				{
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_10");
				}
				else
				{
					$massiv_to_change[202] = NewQuotes($_POST['PROPERTY_ADRESS_VALUE']);
				}
				if (strlen($_POST['PROPERTY_PREFERRED_TIME_VALUE']))
				{
					$massiv_to_change[339] = array("VALUE" => array ("TEXT" => $_POST['PROPERTY_PREFERRED_TIME_VALUE'], "TYPE" => "text"));;
				}
				$massiv_to_change[313] = $_POST['PROPERTY_CASH_VALUE'];
				$pers_key = 227;
				if ($massiv_to_change[313] == 125) $pers_key = 308;
				/*
				if (floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_1_VALUE'])) > 0)
					$massiv_to_change[247] = floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_1_VALUE']));
				if (floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_2_VALUE'])) > 0)
					$massiv_to_change[248] = floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_2_VALUE']));
				if (floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_3_VALUE'])) > 0)
					$massiv_to_change[249] = floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_3_VALUE']));
				if (floatval(str_replace(',','.',$_POST['PROPERTY_COST_3_VALUE'])) > 0)
					$massiv_to_change[199] = floatval(str_replace(',','.',$_POST['PROPERTY_COST_3_VALUE']));
					*/
				$massiv_to_change[247] = floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_1_VALUE']));
				$massiv_to_change[248] = floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_2_VALUE']));
				$massiv_to_change[249] = floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_3_VALUE']));
				$massiv_to_change[199] = floatval(str_replace(',','.',$_POST['PROPERTY_COST_3_VALUE']));
				
				$weighs = $costs = 0;
				foreach ($_POST['count'] as $k => $v)
				{
					if (intval($v) <= 0)
						$c = 1;
					else
						$c = intval($v);
					$cost_new = floatval(str_replace(',','.',$_POST["cost"][$k]));
					CIBlockElement::SetPropertyValuesEx($k, 63, array(301 => $c));
					CIBlockElement::SetPropertyValuesEx($k, 63, array(360 => $cost_new));
					$weighs = $weighs + $_POST["weigh"][$k]*$c;
					$costs = $costs + $_POST["cost"][$k]*$c;
				}
				if (strlen($_POST['PROPERTY_COST_2_VALUE']))
				{
					if (floatval($_POST['PROPERTY_COST_2_VALUE']) < 0)
					{
						$arResult['ERRORS'][] = GetMessage("ERR_STRING_2");
					}
					else {
						$massiv_to_change[198] = floatval(str_replace(',','.',$_POST['PROPERTY_COST_2_VALUE']));
					}
				}
				else
				{
					$massiv_to_change[198] = $costs;
				}
				$massiv_to_change[231] = $_POST['PROPERTY_RATE_VALUE'];
				$massiv_to_change[225] = $weighs;
				$massiv_to_change[307] = $costs;
				$massiv_to_change[376] = $urgent_db;
				$massiv_to_change[390] = '';
				$massiv_to_change[446] = ($_POST['to_legal'] == 1) ? 1 : 0;
				$massiv_to_change[478] = ($_POST['refusal'] == 1) ? 1 : 0;
				$massiv_to_change[493] = $_POST['TIME_PERIOD'];
				if (strlen($_POST['date_deliv']))
				{
					$massiv_to_change[390] = DateFF($_POST['date_deliv']);
				}
				if (strlen($massiv_to_change[390]))
				{
					$massiv_to_change[390] .= ' ';
				}
				$massiv_to_change[390] .= $arResult['time_periods'][$_POST['TIME_PERIOD']];
				/*
				if (strlen($_POST['timedeliv'][0]))
				{
					if (strlen($massiv_to_change[390]))
							$massiv_to_change[390] .= ' ';
					$massiv_to_change[390] .= GetMessage("FROM", array("#TIME#" => $_POST['timedeliv'][0]));
				}
				if (strlen($_POST['timedeliv'][1]))
				{
					if (strlen($massiv_to_change[390]))
						$massiv_to_change[390] .= ' ';
					$massiv_to_change[390] .= GetMessage("TO", array("#TIME#" => $_POST['timedeliv'][1]));
				}
				*/
				$massiv_to_change[434] = 1;
				CIBlockElement::SetPropertyValuesEx($_POST['pack_id'], 42, $massiv_to_change);
				if (count($arResult['ERRORS']) == 0)
				{
					$pack = GetListOfPackeges($agent_array,0,$id);
					$arResult['PACK'] = false;
					if ($pack['COUNT'] == 1)
					{
						$arResult['PACK'] = $pack[0];
						$arResult["PACK"]['GOODS'] = GetGoodsOdPack($arResult['PACK']["ID"]);
						$arResult["PACK"]["SHOP"] = GetCompany($arResult['PACK']['PROPERTY_CREATOR_VALUE']);
						$arResult["PACK"]["AGENT_NAME"] = GetMessage("MSD_NAME");
						CIBlockElement::SetPropertyValuesEx($id, 42, array(203 => 126, 229 => 127, 489 => date('d.m.Y H:i:s')));
						$history_id = AddToHistory($id,$agent_id,$u_id,126);
						$short_history_id = AddToShortHistory($id,$u_id,127);
						$arResult['MESSAGE'][] = GetMessage("MESS_PACK_GO_FORM", array("#ID#" => $arResult['PACK']["ID"],"#N_ZAKAZ#" => $arResult['PACK']['PROPERTY_N_ZAKAZ_IN_VALUE']));
						if ($agent_array['demo'] == GetMessage("YES"))
							$dd = true;
						else
							$dd = false;
						if (!$dd)
						{
							$ff = MakeTicketPDF($arResult["PACK"]);
							$when_deliveryTxt = (strlen($massiv_to_change[390])) ? GetMessage("FORMATION_TEXT_WHEN", array("#WHEN#" => $massiv_to_change[390])) : '';
							$commentTxt = (strlen($arResult["PACK"]["PROPERTY_PREFERRED_TIME_VALUE"]["TEXT"])) ? 
								GetMessage("FORMATION_TEXT_COMMENT", array("#COMMENT#" => $arResult["PACK"]["PROPERTY_PREFERRED_TIME_VALUE"]["TEXT"])) : '';
							$urgencyTxt = ($arResult["PACK"]["PROPERTY_URGENCY_ORDER_ENUM_ID"] == 172) ? GetMessage("FORMATION_TEXT_URGENCY") : '';
							$arParamsTxt = array(
								"DATE_SEND" => date('d.m.Y H:i'),
								"SHOP_ID" => $arResult["PACK"]["SHOP"]["ID"],
								"SHOP_NAME" => $arResult["PACK"]["SHOP"]["NAME"],
								"PACK_ID" => $arResult["PACK"]["ID"],
								"N_ZAKAZ" => $arResult["PACK"]["PROPERTY_N_ZAKAZ_IN_VALUE"],
								"DATE_CREATE" => $arResult["PACK"]["DATE_CREATE"],
								"RECIPIENT" => $arResult["PACK"]["PROPERTY_RECIPIENT_VALUE"],
								"PHONE" => $arResult["PACK"]["PROPERTY_PHONE_VALUE"],
								"CITY" => $arResult["PACK"]["PROPERTY_CITY_NAME"],
								"ADRESS" => ($arResult["PACK"]["PROPERTY_CONDITIONS_ENUM_ID"] == 38) ? $arResult["PACK"]["PROPERTY_CONDITIONS_VALUE"] : $arResult["PACK"]["PROPERTY_ADRESS_VALUE"],
								"WHEN_DELIVERY" => $when_deliveryTxt,
								"COMMENT" => $commentTxt,
								"URGENCY" => $urgencyTxt,
								"COST_GOODS" => CurrencyFormat($arResult["PACK"]["PROPERTY_COST_GOODS_VALUE"],"RUU"),
								"COST_2" => CurrencyFormat($arResult["PACK"]["PROPERTY_COST_2_VALUE"],"RUU"),
								"COST_3" => CurrencyFormat($arResult["PACK"]["PROPERTY_COST_3_VALUE"],"RUU"),
								"COST_1" => CurrencyFormat($arResult["PACK"]["PROPERTY_COST_1_VALUE"],"RUU"),
								"CASH" => $arResult["PACK"]["PROPERTY_CASH_VALUE"],
								"RATE" => CurrencyFormat($arResult["PACK"]["PROPERTY_RATE_VALUE"],"RUU"),
								"SUMM_SHOP" => CurrencyFormat($arResult["PACK"]["PROPERTY_SUMM_SHOP_VALUE"],"RUU"),
								"WEIGHT" => WeightFormat($arResult["PACK"]["PROPERTY_WEIGHT_VALUE"]),
								"SIZE_1" => $arResult["PACK"]["PROPERTY_SIZE_1_VALUE"],
								"SIZE_2" => $arResult["PACK"]["PROPERTY_SIZE_2_VALUE"],
								"SIZE_3" => $arResult["PACK"]["PROPERTY_SIZE_3_VALUE"],
								"PLACES" => $arResult["PACK"]["PROPERTY_PLACES_VALUE"]
							);
							$qw = SendMessageInSystem($u_id, $agent_array['id'], $agent_array['uk'], GetMessage("FORMATION_TITLE"), 128, '', '', 175, $arParamsTxt);
							$send = SendMessageMailNew($agent_array['uk'], $agent_array['id'], 129, 175, $arParamsTxt, array($ff));
						}
					}
					$_SESSION['MESSAGE'][] = GetMessage("MESS_PACK_GO_FORM", array("#ID#" => $arResult["PACK"]["ID"], "#N_ZAKAZ#" => $arResult["PACK"]["PROPERTY_N_ZAKAZ_IN_VALUE"]));
					LocalRedirect("/warehouse/");
				}
			}	
		}
		
		if (isset($_POST['save_package_shop']))
		{
			if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
			{
				$_POST = array();
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST['key_session']] = $_POST['rand'];
				$massiv_to_change = array();
				$massiv_to_change[196] = trim($_POST['PROPERTY_N_ZAKAZ']);
				if (strlen($_POST['PROPERTY_COST_1_VALUE']))
				{
					if (floatval(str_replace(',','.',$_POST['PROPERTY_COST_1_VALUE'])) < 0)
					{
						$arResult['ERRORS'][] = GetMessage("ERR_STRING_3");
					}
					else
					{
						$massiv_to_change[197] = floatval(str_replace(',','.',$_POST['PROPERTY_COST_1_VALUE']));
					}
				}
				else
				{
					$massiv_to_change[197] = 0;
				}
				if (intval($_POST['PROPERTY_PLACES_VALUE']) < 1)
				{
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_6");
				}
				else
				{
					$massiv_to_change[232] = intval($_POST['PROPERTY_PLACES_VALUE']);
				}
				if (!strlen($_POST['PROPERTY_RECIPIENT_VALUE']))
				{
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_7");
				}
				else
				{
					$massiv_to_change[208] = trim($_POST['PROPERTY_RECIPIENT_VALUE']);
				}
				if (!strlen($_POST['PROPERTY_PHONE_VALUE']))
				{
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_8");
				}
				else
				{
					$massiv_to_change[209] = $_POST['PROPERTY_PHONE_VALUE'];
				}
				if ($_POST['PROPERTY_CONDITIONS_ENUM_ID'] == 0)
				{
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_11");
				}
				else
				{
					$massiv_to_change[201] = $_POST['PROPERTY_CONDITIONS_ENUM_ID'];
				}
				if ($_POST['urgent'] == 2)
				{
					$urgent = 2;
					$urgent_db = 172;
				}
				else
				{
					$urgent = 1;
					$urgent_db =  false;
				}
				if (!strlen($_POST['PROPERTY_CITY']))
				{
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_9");
				}
				if ((strlen($_POST['PROPERTY_CITY'])) && ($_POST['PROPERTY_CONDITIONS_ENUM_ID'] > 0))
				{
					$get_city_id = GetCityId($_POST['PROPERTY_CITY']);
					$name_of_city = GetFullNameOfCity($get_city_id);
					if ($_POST['PROPERTY_CONDITIONS_ENUM_ID'] == 38)
					{
						$pr = $_POST['price_2'];
					}
					else
					{
						$pr = $_POST['price'];
					}
					$have_city = CheckCityToHave(
						$get_city_id,
						$pr,
						$_POST['PROPERTY_WEIGHT_VALUE'],
						$_POST['PROPERTY_SIZE_1_VALUE'],
						$_POST['PROPERTY_SIZE_2_VALUE'],
						$_POST['PROPERTY_SIZE_3_VALUE'],
						$urgent
					);
					if(!$have_city["LOG"])
					{
						$arResult['INFO'] = '<p class="orange">'.GetMessage("ERR_MAKE_1").'</p>';
						$massiv_to_change[250] = false;
						$massiv_to_change[212] = false;
					}
					else
					{
						$arResult["INFO"] = $have_city["TEXT"];
						$massiv_to_change[250] = $have_city['COST'];
						$massiv_to_change[212] = $get_city_id;
					}
				}
				if (!strlen($_POST['PROPERTY_ADRESS_VALUE']) && ($_POST['PROPERTY_CONDITIONS_ENUM_ID'] == 37))
				{
					$arResult['ERRORS'][] = GetMessage("ERR_STRING_10");
				}
				else
				{
					$massiv_to_change[202] = $_POST['PROPERTY_ADRESS_VALUE'];
				}
				if (strlen($_POST['PROPERTY_PREFERRED_TIME_VALUE']))
				{
					$massiv_to_change[339] = array("VALUE" => array ("TEXT" => $_POST['PROPERTY_PREFERRED_TIME_VALUE'], "TYPE" => "text"));;
				}
				$massiv_to_change[313] = $_POST['PROPERTY_CASH_VALUE'];
				$pers_key = 227;
				if ($massiv_to_change[313] == 125)
					$pers_key = 308;
				/*
				if (floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_1_VALUE'])) > 0)
					$massiv_to_change[247] = floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_1_VALUE']));
				if (floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_2_VALUE'])) > 0)
					$massiv_to_change[248] = floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_2_VALUE']));
				if (floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_3_VALUE'])) > 0)
					$massiv_to_change[249] = floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_3_VALUE']));
				if (floatval(str_replace(',','.',$_POST['PROPERTY_COST_3_VALUE'])) > 0)
					$massiv_to_change[199] = floatval(str_replace(',','.',$_POST['PROPERTY_COST_3_VALUE']));
					*/
					
				$massiv_to_change[247] = floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_1_VALUE']));
				$massiv_to_change[248] = floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_2_VALUE']));
				$massiv_to_change[249] = floatval(str_replace(',','.',$_POST['PROPERTY_SIZE_3_VALUE']));
				$massiv_to_change[199] = floatval(str_replace(',','.',$_POST['PROPERTY_COST_3_VALUE']));
				$weighs = $costs = 0;
				foreach ($_POST['count'] as $k => $v)
				{
					if (intval($v) <= 0) $c = 1;
					else $c = intval($v);
					$cost_new = floatval(str_replace(',','.',$_POST["cost"][$k]));
					CIBlockElement::SetPropertyValuesEx($k, 63, array(301 => $c));
					CIBlockElement::SetPropertyValuesEx($k, 63, array(360 => $cost_new));
					$weighs = $weighs + $_POST["weigh"][$k]*$c;
					$costs = $costs + $cost_new*$c;
				}
				if (strlen($_POST['PROPERTY_COST_2_VALUE']))
				{
					if (floatval(str_replace(',','.',$_POST['PROPERTY_COST_2_VALUE']) < 0))
					{
						$arResult['ERRORS'][] = GetMessage("ERR_STRING_2");
					}
					else
					{
						$massiv_to_change[198] = floatval(str_replace(',','.',$_POST['PROPERTY_COST_2_VALUE']));
					}
				}
				else
				{
					$massiv_to_change[198] = $costs;
				}
				$massiv_to_change[231] = $_POST['PROPERTY_RATE_VALUE'];
				$massiv_to_change[225] = $weighs;
				$massiv_to_change[307] = $costs;
				$massiv_to_change[376] = $urgent_db;
				$massiv_to_change[390] = '';
				$massiv_to_change[446] = ($_POST['to_legal'] == 1) ? 1 : 0;
				$massiv_to_change[478] = ($_POST['refusal'] == 1) ? 1 : 0;
				$massiv_to_change[493] = $_POST['TIME_PERIOD'];
				if (strlen($_POST['date_deliv']))
				{
					$massiv_to_change[390] = DateFF($_POST['date_deliv']);
				}
				if (strlen($massiv_to_change[390]))
				{
					$massiv_to_change[390] .= ' ';
				}
				$massiv_to_change[390] .= $arResult['time_periods'][$_POST['TIME_PERIOD']];
				/*
				if (strlen($_POST['timedeliv'][0]))
				{
					if (strlen($massiv_to_change[390]))
						$massiv_to_change[390] .= ' ';
					$massiv_to_change[390] .= GetMessage("FROM", array("#TIME#" => $_POST['timedeliv'][0]));
				}
				if (strlen($_POST['timedeliv'][1]))
				{
					if (strlen($massiv_to_change[390]))
						$massiv_to_change[390] .= ' ';
					$massiv_to_change[390] .= GetMessage("TO", array("#TIME#" => $_POST['timedeliv'][1]));
				}
				*/
				CIBlockElement::SetPropertyValuesEx($_POST['pack_id'], 42, $massiv_to_change);
				$arResult['MESSAGE'][] = GetMessage("MESS_SAVE_PACKS", array("#NUMBER#" => $_POST["number_order"], "#ID#" => $_POST['pack_id']));
			}
		}
		
		$arResult['PACK'] = GetOnePackage(intval($_GET['id']), $agent_array['id'], $agent_array['type']);
		if ($arResult['PACK'])
		{
			$arResult["TITLE"] = GetMessage("TTL_PACK",array("#ID#"=>$arResult['PACK']["PROPERTY_N_ZAKAZ_IN_VALUE"]));
			$arResult["RATE"] = WhatIsRate($arResult['PACK']["PROPERTY_CREATOR_VALUE"]);
			$arResult["PRICE"] = WhatIsPrice($arResult['PACK']["PROPERTY_CREATOR_VALUE"]);
			$arResult["SHOP_DEFAULT"] = GetDefaultValuesForShop($arResult['PACK']["PROPERTY_CREATOR_VALUE"]);
			if ($arResult['PACK']['CREATED_BY'] == $u_id)
				$arResult['EDIT'] = true;
			else
				$arResult['EDIT'] = false;
			
			$db_props = CIBlockElement::GetProperty(40, $arResult['PACK']["PROPERTY_CREATOR_VALUE"], array("sort" => "asc"), array("ID" => 492));
			$ar_props = $db_props->Fetch();
			$arResult['CONDITIONS_IM'] = $ar_props["VALUE"];
		}
		else
		{
			$arResult["TITLE"] = GetMessage("TTL_PACK_NOT");
		}
	}
	
	/**********список заказов на формировании**********/
	if ($mode == 'makepackage_list')
	{
		if ($arParams["ONLY_MY_ORDERS"])
			$uu = $u_id;
		else $uu = 0;
		$arResult["LIST"] = GetListOfPackeges($agent_array,$agent_id,false,116,false,0,false,0, 0,'','',$uu);
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"],$arResult["LIST"]["COUNT"]);
		foreach ($arResult["LIST"] as $k => $p)
		{
			$g = 0;
			$arResult["LIST"][$k]['GOODS_LIST'] = GetGoodsOdPack($p["ID"]);
			foreach ($arResult["LIST"][$k]['GOODS_LIST'] as $gg)
			{
				$g = $g + $gg["COUNT"];
			}
			$arResult["LIST"][$k]['GOODS'] = $g;
		}
	}
	
	/******************список заказов******************/
	if ($mode == 'list')
	{
		if (isset($_POST['send_package']))
		{
			if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
			{
				$_POST = array();
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST['key_session']] = $_POST['rand'];
				$sends = array();
				foreach ($_POST['id'] as $id)
				{
					$st_order = 0;
					$db_props = CIBlockElement::GetProperty(42, $id, array("sort" => "asc"), Array("CODE"=>"STATE"));
					if($ar_props = $db_props->Fetch())
					{
						$st_order = $ar_props["VALUE"];
					}
					
					if (in_array($st_order,$arParams['STATUS_TO_DELIVERY_IM']))
					{
						CIBlockElement::SetPropertyValuesEx($id, 42, array(203 => 54, 229 => 78, 434 => 1, 489 => date('d.m.Y H:i:s')));
						$history_id = AddToHistory($id, $agent_id,$u_id,54);
						$short_history_id = AddToShortHistory($id,$u_id,78);
						$sends[$id] = '<a href="http://dms.newpartner.ru/warehouse/index.php?mode=package&id='.$id.'">'.$_POST['id_in'][$id].'</a>';
						$arResult['MESSAGE'][] = GetMessage("MESS_PACK_GO_DELIVERY", array("#NUMBER#" => $_POST['id_in'][$id], "#ID#" => $id));
					}
					else
					{
						$arResult["ERRORS"][] = GetMessage("INCORRECT_STATUS_SEND", array("#ID#" => $id, "#NUMBER#" => $_POST['id_in'][$id]));
					}
				}
				if ($agent_array['demo'] == GetMessage("YES"))
				{
					$dd = true;
				}
				else
				{
					$dd = false;
				}
			
				if ((count($sends) > 0) && (!$dd))
				{
					$pdfs = array();
					
					//настройки для передачи в 1с
					$send_to_1c = false;
					$currentip = GetSettingValue(683, false, $agent_array["uk"]);
					$currentlink = GetSettingValue(704, false, $agent_array["uk"]);
					$login1c = GetSettingValue(705, false, $agent_array["uk"]);
					$pass1c = GetSettingValue(706, false, $agent_array["uk"]);
					if ((strlen(trim($currentip))) && (strlen(trim($currentlink))) && (strlen(trim($login1c))) && (strlen(trim($pass1c))))
					{
						$url = "http://".$currentip.$currentlink;
						$curl = curl_init();
                        curl_setopt_array($curl, array(    
							CURLOPT_URL => $url,
							CURLOPT_HEADER => true,
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_NOBODY => true,
							CURLOPT_TIMEOUT => 5
						));
                        $header = explode("\n", curl_exec($curl));
                        curl_close($curl);
                        if (strlen(trim($header[0])))
                        {
							$send_to_1c = true;
							$client = new SoapClient(
								$url,  
								array("login" => $login1c, "password" => $pass1c, "exceptions" => false)
							);
							$arManifestTo1c = array(
								"TransportationDocument" => "",
								"Carrier" => "",
								"TransportationCost" => 0,
								"Partner" => $agent_array["inn"],
								"DepartureDate" => date('d.m.Y'),
								"Places" => 0,
								"Weight" => 0,
								"VolumeWeight" => 0,
								"Comment" => "",
								"City" => $agent_array['city_name'],
								"TransportationMethod" => "",
								"Delivery" => array()
							);
						}
					}
					
					foreach ($sends as $id => $v)
					{
						$pack = GetListOfPackeges($agent_array,0,$id);
						$arResult['PACK'] = $pack[0];
						$arResult["PACK"]['GOODS'] = GetGoodsOdPack($arResult['PACK']["ID"]);
						$arResult["PACK"]["SHOP"] = GetCompany($arResult['PACK']['PROPERTY_CREATOR_VALUE']);
						$arResult["PACK"]["AGENT_NAME"] = GetMessage("MSD_NAME");	
						$pdfs[] = MakeTicketPDF($arResult["PACK"]);	
						if ($arResult["PACK"]["PROPERTY_TAKE_PROVIDER_ENUM_ID"] == 174) 
						{
							$sends[$id] .= GetMessage("FORMATION_INFO");
						}
						if ($send_to_1c)
						{
							$orderTo1c = makeManifestOrderfromDMSOrder($arResult['PACK']);
							if ($orderTo1c['result'])
							{
								$arManifestTo1c['Delivery'][] = $orderTo1c['result'];
								$arManifestTo1c["Places"] = $arManifestTo1c["Places"] + $arResult['PACK']['PROPERTY_PLACES_VALUE'];
								$arManifestTo1c["Weight"] = $arManifestTo1c["Weight"] + $arResult['PACK']['PROPERTY_WEIGHT_VALUE'];
							}
						}
					}
					$arParamsTxt = array(
						"DATE_SEND" => date('d.m.Y H:i'),
						"AGENT_ID" => $agent_array['id'],
						"AGENT_NAME" => $agent_array['name'],
						"ORDERS" => implode(', ',$sends),
						"LINK_TO_MESS" => ''
					);
					$qw = SendMessageInSystem($u_id, $agent_array['id'], $agent_array["uk"], GetMessage("RECEIPT_TITLE"), 104, '', '', 164, $arParamsTxt);
					$arParamsTxt["#LINK_TO_MESS#"] = GetMessage("LINK_TO_MESS", array("#ID#" => $qw));
					AddToLogs('SendMessageMail', array('UK' => $agent_array["uk"], 'ID_AGENT' =>  $agent_array['id'], 'TYPE' => 102, 'template' => 164, 'ORDERS' => implode(', ',$sends), 'pdfs' => implode(', ',$pdfs)));
					$send = SendMessageMailNew($agent_array["uk"], $agent_array['id'], 102, 164, $arParamsTxt, $pdfs);
					//передача заказов в 1с
					if ($send_to_1c)
					{
						$arManifestTo1cUTF = convArrayToUTF($arManifestTo1c);
						$arLogs = $arManifestTo1c;
						foreach ($arLogs['Delivery'] as $key => $inv)
						{
							foreach ($inv as $inv_key => $data)
							{
								$arLogs['Delivery '.$key.' '.$inv_key] = $data;
							}
						}
						unset($arLogs['Delivery']);
						AddToLogs('InvoicesSend',$arLogs);
						$result = $client->SetManifest(array('ListOfDocs' => json_encode($arManifestTo1cUTF)));
						$mResult = $result->return;
						AddToLogs('InvoicesSendAnswer', array('Answer' => $mResult));
					}
				}
			}
		}
		
		if (isset($_POST['delete_packages']))
		{
			if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
			{
				$_POST = array();
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST['key_session']] = $_POST['rand'];
				foreach ($_POST['id'] as $pack)
				{
					$st_order = 0;
					$db_props = CIBlockElement::GetProperty(42, $pack, array("sort" => "asc"), Array("CODE"=>"STATE"));
					if($ar_props = $db_props->Fetch())
					{
						$st_order = $ar_props["VALUE"];
					}
					
					if (in_array($st_order,$arParams['STATUS_DELEETE_IM']))
					{
						$delete_array = array();
						$his_short = HistoryShortOfPackage($pack);
						foreach ($his_short as $v)
						{
							$delete_array[] = $v['ID'];
						}
						$his = HistoryOfPackage($pack);
						foreach ($his as $v)
						{
							$delete_array[] = $v['ID'];
						}
						$goods = GetGoodsOdPack($pack);
						foreach ($goods as $k => $v)
						{
							$delete_array[] = $k;
						}
						$delete_array[] = $pack;
						foreach ($delete_array as $del)
						{
							CIBlockElement::Delete($del);
						}
						$arResult['MESSAGE'][] = GetMessage("MESS_DELETE_PACK", array("#ID#" => $_POST['id_in'][$pack]));
					}
					else
					{
						$arResult["ERRORS"][] = GetMessage("INCORRECT_STATUS_DELETE", array("#ID#" => $pack, "#NUMBER#" => $_POST['id_in'][$pack]));
					}
				}
			}
		}
	
		if(isset($_POST['cancel']))
		{
			if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
			{
				$_POST = array();
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST['key_session']] = $_POST['rand'];
				foreach ($_POST['id'] as $pack)
				{
					$st_order = 0;
					$db_props = CIBlockElement::GetProperty(42, $pack, array("sort" => "asc"), Array("CODE"=>"STATE"));
					if($ar_props = $db_props->Fetch())
					{
						$st_order = $ar_props["VALUE"];
					}
					
					if (!in_array($st_order,$arParams['STATUS_CHANCEL'])) 
					{
						$arResult["ERRORS"][] = GetMessage("INCORRECT_STATUS_CANCELLATION", array("#ID#" => $pack, "#NUMBER#" => $_POST['id_in'][$pack]));
					}
					else
					{
						$arParamsTxt = array(
							"SHOP_ID" => $agent_array['id'],
							"SHOP_NAME" => $agent_array['name'],
							"ID_PACK" => $pack,
							"NUMBER" => $_POST['id_in'][$pack],
							"DATE_SEND" => date('d.m.Y H:i'),
							"LINK" => ""
						);
						$qw = SendMessageInSystem($u_id, $agent_array['id'], $agent_array["uk"], GetMessage("INCORRECT_MESSAGE_TO_UK_TITLE"), 164, '', '', 170, $arParamsTxt);
						$arParamsTxt["LINK"] = GetMessage("LINK_TO_MESS", array("#ID#" => $qw));
						$send = SendMessageMailNew($agent_array["uk"], $agent_array['id'], 165, 170, $arParamsTxt);
						$arResult['MESSAGE'][] = GetMessage("INCORRECT_SEND_SUCCESS", array("#ID#" => $pack, "#NUMBER#" => $_POST['id_in'][$pack]));
					}
				}
			}
		}
		
		if(isset($_POST['return']))
		{
			if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
			{
				$_POST = array();
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST['key_session']] = $_POST['rand'];
				foreach ($_POST['id'] as $pack)
				{
					$st_order = 0;
					$db_props = CIBlockElement::GetProperty(42, $pack, array("sort" => "asc"), Array("CODE"=>"STATE"));
					if($ar_props = $db_props->Fetch())
					{
						$st_order = $ar_props["VALUE"];
					}
					
					if (!in_array($st_order,$arParams['STATUS_RETURN'])) 
					{
						$arResult["ERRORS"][] = GetMessage("INCORRECT_STATUS_RETURN", array("#ID#" => $pack, "#NUMBER#" => $_POST['id_in'][$pack]));
					}
					else
					{
						$arParamsTxt = array(
							"SHOP_ID" => $agent_array['id'],
							"SHOP_NAME" => $agent_array['name'],
							"ID_PACK" => $pack,
							"NUMBER" => $_POST['id_in'][$pack],
							"DATE_SEND" => date('d.m.Y H:i'),
							"LINK" => ""
						);
						$qw = SendMessageInSystem($u_id, $agent_array['id'], $agent_array["uk"], GetMessage("RETURN_MESSAGE_TO_UK_TITLE"), 262, '', '', 195, $arParamsTxt);
						$arParamsTxt["LINK"] = GetMessage("LINK_TO_MESS", array("#ID#" => $qw));
						$send = SendMessageMailNew($agent_array["uk"], $agent_array['id'], 263, 195, $arParamsTxt);
						$arResult['MESSAGE'][] = GetMessage("RETURN_SEND_SUCCESS", array("#ID#" => $pack, "#NUMBER#" => $_POST['id_in'][$pack]));
					}
				}
			}
		}		
		
	
		
		$arResult["LISTPACKAGES"] = GetListOfPackeges(
			$agent_array,
			$agent_id,
			false,
			false,
			false,
			0,
			false,
			0,
			intval($_GET['status_short']),
			$_GET['date_from'],
			$_GET['date_to'],
			0,
			true,
			false,
			array("ID"=>"DESC"), 
			0, 
			0,
			(strlen($_GET['number'])) ? trim($_GET['number']) : false
		);
	
		//dump($arResult["LISTPACKAGES"]);
		$arResult["COUNT"] = $arResult["LISTPACKAGES"]["COUNT"];
		$arResult["NAV_STRING"] = $arResult["LISTPACKAGES"]["NAV_STRING"];
		unset($arResult["LISTPACKAGES"]["NAV_STRING"],$arResult["LISTPACKAGES"]["COUNT"]);
		
		$arResult["DEMO"] = ($agent_array["demo"] == GetMessage("YES")) ? true : false;
		$arResult["LIMIT"] = GetSettingValue(256);
		
		if (($arResult["DEMO"]) && ($arResult["COUNT"] >= $arResult["LIMIT"]))
		{
			unset($arResult["BUTTONS"]['makepackage'], $arResult["BUTTONS"]["upload"]);
		}
		$arResult["SHOW_LINK_REESTR"] = false;
		$list_to_deliver = GetListOfPackeges($agent_array, $agent_id, false, 54, false, 0, false, 0, 0, '', '', 0, false);
		if (count($list_to_deliver) > 0)
		{
			$arResult["SHOW_LINK_REESTR"] = true;
		}
	}
	
	/*****************печать этикеток******************/
	if ($mode == "print_labels")
	{
		$ids = $_GET['ids'];
		$ids_array = explode(',',$ids);
		if (count($ids_array))
		{
			$arResult['PACKS'] = GetListOfPackeges($agent_array, $agent_id, $ids_array, false, false,0,false,0,0,'','',0,false);
		}
	}
	
	/**********************заказ***********************/
	if ($mode == 'package')
	{
		$arResult['PACK'] = GetOnePackage(intval($_GET['id']), $agent_array['id'], $agent_array['type']);
		$arResult['GAB_W'] = WhatIsGabWeightCompany($arResult['PACK']['PROPERTY_CREATOR_VALUE']);
		if ($arResult['PACK'])
		{
			if (in_array($arResult['PACK']['PROPERTY_STATE_ENUM_ID'], array(39,57,80)))
			{
				LocalRedirect("/warehouse/index.php?mode=package_edit&id=".$arResult['PACK']["ID"]);
			}
			$arResult["TITLE"] = GetMessage("TTL_PACK", array("#ID#" => $arResult['PACK']["PROPERTY_N_ZAKAZ_IN_VALUE"]));
		}
		else
		{
			$arResult["TITLE"] = GetMessage("TTL_PACK_NOT");
		}
	}
	
	/************реестр заказов на доставку************/
	if ($mode == 'register')
	{
		$arLang['SHOP'] = $agent_array['name'];
		$arLang['DATE'] = 'Реестр заказов на доставку от '.date('d.m.Y H:i');
		$arLang['FIELDS'] = array('№', 'Номер заказа', 'Вес', 'К оплате получателем');
		$pack_ids = false;
		$state = 54;
		if (strlen($_GET['ids']))
		{
			$ids = explode(',',$_GET['ids']);
			if (count($ids) > 0)
			{
				$pack_ids = $ids;
				$state = false;
			}
		}
		$arLang['LIST'] = GetListOfPackeges($agent_array, $agent_id, $pack_ids, $state, false, 0, false, 0, 0, '', '', 0, false);
		$arLang['NAME_FILE'] = $arLang['DATE'].' '.$arLang['SHOP'];
		$a = MakeRegisterPDF($arLang);
	}
	
}

/**************************************************/
/**********************АГЕНТ***********************/
/**************************************************/

if ($agent_array['type'] == 53)
{
	$modes = array(
		'warehouse',
		'archive',
		'package',
		'on_delivery',
		'package_print',
		'pods',
		'returns'
	);
	$arResult["MENU"] = array(
		'warehouse' => GetMessage("AGENT_MENU_1"),
		'on_delivery' => GetMessage("AGENT_MENU_2"),
		'archive' => GetMessage("AGENT_MENU_3"),
		'pods' => GetMessage("AGENT_MENU_4"),
		'returns' => GetMessage('AGENT_MENU_5')
	);
	
	foreach ($arResult["MENU"] as $m => $name)
	{
		if ($arParams['PERM'][$agent_array['type']][$m][$arResult['ROLE_USER']] == "C")
		{
			unset($arResult["MENU"][$m]);
		}
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
		$componentPage = "agent_".$mode;
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
	{
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));	
	}
	
	/*********************возвраты*********************/
	if ($mode == 'returns')
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
				if (count($_POST['ids']) > 0)
				{
					$max_id_n = GetMaxIDIN(41, 5, true, 194, $_POST['agent_id']);
					$number_m = MakeManifestId($agent_array['uk'], $agent_array['id'], $max_id_n);
					/*
					$el = new CIBlockElement;
					$arLoadProductArray = array(
						"MODIFIED_BY" => $u_id,
						"IBLOCK_SECTION_ID" => false,
						"IBLOCK_ID" => 41,
						"PROPERTY_VALUES"=> array(
							406 => $max_id_n,
							407 => $number_m,
							194 => $agent_array['uk'],
							193 => $agent_array['uk_city_id'],
							233 => $_POST['carriers'],
							270 => $_POST['date_send'],
							408 => $_POST['settlement_date'],
							271 => $_POST['number_send'],
							409 => inval($_POST['places']),
							191 => $agent_array['id'],
							410 => $u_id,
							192 => 47,
							273 => false
						),
						"NAME" => GetMessage("MANIFEST_ID", array("#ID#" => $max_id_n)),
						"ACTIVE" => "Y"
					);
					$man_id = $el->Add($arLoadProductArray);
					$array_cahge =  array(
						498 => $man_id,
						444 => 1
					);
					*/
				}
				else
				{
					$arResult["ERRORS"][] = GetMessage("ERR_NO_ORDERS_CHECK");
				}
			}
		}
		$arResult["CARRIERS"] = TheListOfCarriers($id_c, false, false, $agent_array['id']);
		$arResult["LIST"] = GetListReturns($agent_array['id']);
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"],$arResult["LIST"]["COUNT"]);
	}
	
	/**********************заказ***********************/
	if ($mode == 'package')
	{
		if(isset($_POST['delete_state']))
		{
			$result_delete = DeleteState($_POST['pack_id'],$_POST['history_id']);
			foreach ($result_delete['errors'] as $m)
			{
				$arResult["ERRORS"][] = $m;
			}
			foreach ($result_delete['mess'] as $m)
			{
				$arResult["MESSAGE"][] = $m;
			}
		}
		
		$pack_id = intval($_REQUEST['id']);
		$arResult['PACK'] = GetOnePackage($pack_id, $agent_array['id'], $agent_array['type']);
		if ($arResult['PACK'])
		{
			$arResult["TITLE"] = GetMessage("TTL_PACK", array("#ID#" => $arResult['PACK']["PROPERTY_N_ZAKAZ_IN_VALUE"]));
		}
		else
		{
			$arResult["TITLE"] = GetMessage("TTL_PACK_NOT");
		}
	}
	
	/********************ввод подов********************/
	if ($mode == 'pods')
	{
		if ($u_id == 211)
		{

		}
		if (isset($_POST['save']))
		{
			if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
			{
				$_POST = array();
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST['key_session']] = $_POST['rand'];
				$summ_to_agent = 0;
				
				$count_dost = 0;
				foreach ($_POST['operation'] as $p => $oper)
				{
					if(intval($oper) > 0)
					{
						if ($oper == '001')
						{
							$count_dost++;
						}
					}
				}
				
				if ($count_dost > 0)
				{
					$rep = GetListOfReportsAgent($agent_array['id'], 0, 231 ,true, false);
					if ($rep)
					{
						$report_id = $rep[0]['ID'];
					}
					else
					{
						$max_id_5 = GetMaxIDIN(67, 5, true, 342, $agent_array['id']);
						$el = new CIBlockElement;
						$arLoadProductArray = Array(
							"MODIFIED_BY" => $u_id, 
							"IBLOCK_SECTION_ID" => false,
							"IBLOCK_ID" => 67,
							"NAME" => "Отчёт субагента ".$agent_array['prefix']."-".intval($max_id_5),
							"ACTIVE" => "Y",
							"PROPERTY_VALUES" => array(
								342 => $agent_array['id'],
								343 => $max_id_5,
								348 => date('d.m.Y H:i:s'),
								481 => date('d.m.Y H:i:s'),
								482 => date('d.m.Y H:i:s'),
								506 => 231
							)
						);
						$report_id = $el->Add($arLoadProductArray);
					}
				}
				
				foreach ($_POST['operation'] as $p => $oper)
				{
					if(intval($oper) > 0)
					{
						if ($oper == '001')
						{
							CIBlockElement::SetPropertyValuesEx($p, 42, array(
								203 => 44, 
								229 => 72, 
								218 => 61, 
								267 => $_POST['date_delivery'][$p], 
								479 => $_POST['summ'][$p], 
								442 => 0,
								499 => 7326165,
								504 => 1,
								240 => floatval(str_replace(',','.',$_POST['agent_cost_delivery'][$p])),
								266 => floatval(str_replace(',','.',$_POST['agent_cost_rate'][$p])),
								509 => $report_id,
								498 => substr($_POST['date_delivery'][$p], 0, 10)
							));
							$add_to_period = AddElementToPeriod($p, 284, $u_id);
							$history_id = AddToHistory($p, $agent_array['id'], $u_id,44,$_POST['fio'][$p],$_POST['date_delivery'][$p]);
							$short_history_id = AddToShortHistory($p,$u_id,72,$_POST['fio'][$p],$_POST['date_delivery'][$p]);
							if (strlen($_POST['cur_fio'][$p]))
							{
								$from_txt = GetMessage("BY_COURIER", array("#FIO#" => $_POST['cur_fio'][$p]));
							}
							if (strlen($_POST['pvz_name'][$p]))
							{
								$from_txt = GetMessage("BY_PVZ", array("#PVZ#" => $_POST['pvz_name'][$p]));
							}
							$arSendsParams = array(
								"ID" => $_POST['id_in'][$p],
								"ID_LINK" => $p,
								"DATE_RES" => $_POST['date_delivery'][$p],
								"FROM" => $from_txt,
								"RESIVER" => $_POST['fio'][$p],
								"N_ZAKAZ" => $_POST['id_number'][$p],
								"LINK_TO_MESS" => ""
							);
							$qw = SendMessageInSystem(
								$u_id, 
								$agent_array['uk'], 
								$_POST['shop'][$p], 
								GetMessage("ISSUED_BY_COURIER_TITLE", array("#NUMBER#" => $_POST['id_in'][$p])), 
								152, 
								'', 
								'', 
								166,
								$arSendsParams
							);
							$arSendsParams['LINK_TO_MESS'] = GetMessage('LINK_TO_MESS', array('#ID#' => $qw));
							SendMessageMailNew($_POST['shop'][$p], $agent_array['uk'], 114, 166, $arSendsParams);
							$trans_id = AddTransaction(101,'', $agent_array['id'], $u_id, $_POST['date_delivery'][$p],$_POST['summ'][$p],'',GetMessage("TRANS_NAME_1"),$p);
							$summ_to_agent = $summ_to_agent + $_POST['summ'][$p];
							
							$arResult['MESSAGE'][] = GetMessage("MESS_PACK_DELIVERED", array("#ID#" => $p, "#NUMBER#" => $_POST['id_in'][$p]));
						}
						else
						{
							/*
							CIBlockElement::SetPropertyValuesEx($p, 42, array(
								203 => 40, 
								229 => 69, 
								204 => false, 
								337 => false, 
								442 => 1
							));
							*/
							CIBlockElement::SetPropertyValuesEx($p, 42, array(
								442 => 1
							));
							$history_id = AddToHistory($p, $agent_array['id'], $u_id, 40, $arParams["STATUS"][$oper], $_POST['date_delivery'][$p]);
							$short_history_id = AddToShortHistory($p, $u_id, 69, $arParams["STATUS"][$oper], $_POST['date_delivery'][$p]);
							$arSendsParams = array(
								"ID_PACK" => $p,
								"DATE_STATUS"=> $_POST['date_delivery'][$p],
								"ORDER" => $_POST['id_in'][$p],
								"STATUS" => $arParams["STATUS"][$oper],
								'LINK_TO_MESS' => ''
							);
							$qw = SendMessageInSystem(
								$u_id,
								$agent_array['uk'], 
								$_POST['shop'][$p], 
								GetMessage("EXCEPTIONAL_SITUATION_TITLE", array("#NUMBER#" => $_POST['id_in'][$p])), 
								191, 
								'', 
								'', 
								162,
								$arSendsParams
							);
							$arSendsParams['LINK_TO_MESS'] = GetMessage('LINK_TO_MESS', array('#ID#' => $qw));
							SendMessageMailNew($_POST['shop'][$p], $agent_array['uk'], 115, 162, $arSendsParams);
							$arResult['MESSAGE'][] = GetMessage("MOVED_TO_WAREHOUSE", array("#ID#" => $p, "#NUMBER#" => $_POST['id_in'][$p]));
						}
					}
				}
				
				if ($count_dost > 0)
				{
					$rep_info = GetListOfReportsAgent($agent_array['id'], $report_id, 231 ,true, false);
					CIBlockElement::SetPropertyValuesEx(
						$report_id, 
						67, 
						array(
							483 => $rep_info['COST'],
							484 => $rep_info['SUMM_AGENT'],
							485 => $rep_info['RATE'],
							488 => $rep_info["TO_AGENT"],
							348 => date('d.m.Y H:i:s'),
							482 => date('d.m.Y H:i:s')
						)
					);
				}
			}
			if ($summ_to_agent > 0)
			{
				$db_props = CIBlockElement::GetProperty(40, $agent_array['id'], array("sort" => "asc"), Array("CODE"=>"ACCOUNT"));
				if($ar_props = $db_props->Fetch())
				{
					$summ_agent = $ar_props["VALUE"];
				}
				else
				{
					$summ_agent = 0;
				}
				$summ_agent = $summ_agent + $summ_to_agent;
				CIBlockElement::SetPropertyValuesEx($agent_array['id'], 40, array(219 => $summ_agent));
				$arResult['MESSAGE'][] = GetMessage("MESS_BALANCE", array("#SUMM#"=>CurrencyFormat($summ_agent,"RUU")));
			}
		}
		$arResult["TITLE"] = GetMessage("TTL_PODS");
		$list_mans = ManifestsToAgent($agent_array['id'], 0, false, false, false);
		$mans_array = array();
		foreach ($list_mans as $man)
		{
			$mans_array[] = $man['ID'];
		}
		$arResult["LIST"] = GetListPackeges($mans_array, array("ID"=>"DESC"),array(40,43,79,46,56), false, false, 0, $_GET['number'], true, true, false, $_GET['exceptions']);
		
		// $arResult["LIST"] = GetListPacksCurrentAgent($agent_id,array(40,43,79,46,56));
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"],$arResult["LIST"]["COUNT"]);
	}
	
	/*************печать квитанции заказа**************/
	if ($mode == 'package_print')
	{
		$pack_id = intval($_REQUEST['id']);
		$pack = GetListOfPackeges($agent_array,0,$pack_id);
		$arResult['PACK'] = false;
		if ($pack['COUNT'] == 1)
		{
			$arResult['PACK'] = $pack[0];
			$arResult["PACK"]['GOODS'] = GetGoodsOdPack($arResult['PACK']["ID"]);
			$arResult["PACK"]["SHOP"] = GetCompany($arResult['PACK']['PROPERTY_CREATOR_VALUE']);
			$arResult["PACK"]["AGENT_NAME"] = strlen($agent_array['legal_name']) ? $agent_array['legal_name'] : $agent_array['name'];
			MakeTicketPDF($arResult['PACK'],'D');
		}
	}
	
	/**********************склад***********************/
	if ($mode == 'warehouse')
	{
		if (isset($_POST['save']))
		{
			$processed_orders = array();
			if (is_array($_POST['cur_new']))
			{
				foreach ($_POST['cur_new'] as $id => $c_id)
				{
					if ($c_id > 0)
					{
						CIBlockElement::SetPropertyValuesEx($id, 42, array(203 => 43, 204 => $c_id, 229 => 71));
						$history_id = AddToHistory($k,$agent_id,$u_id,43,$_POST['c_name'][$c_id]);
						$short_history_id = AddToShortHistory($id,$u_id,71,$_POST['c_name'][$c_id]);
						$processed_orders[] = $id;
						$arResult['MESSAGE'][] = GetMessage("MESS_PERMIT_DELIVERY", array("#ID#" => $_POST['id_in'][$id]));
					}
				}
			}

			if (is_array($_POST['pvz_new']))
			{
				foreach ($_POST['pvz_new'] as $id => $pvz_new)
				{
					if (($pvz_new > 0) && !in_array($id,$processed_orders))
					{
						CIBlockElement::SetPropertyValuesEx($id, 42, array(203 => 79, 229 => 74, 337 => $pvz_new));
						$history_id = AddToHistory($id,$agent_id,$u_id,79,$_POST['pvz_name'][$pvz_new]);
						$short_history_id = AddToShortHistory($id,$u_id,74,$_POST['pvz_name'][$pvz_new]);
						$processed_orders[] = $id;
						$arResult['MESSAGE'][] = GetMessage("MESS_TO_PVZ", array("#ID#" => $_POST['id_in'][$id],"#PVZ#"=>$_POST['pvz_name'][$pvz_new]));
					}
				}
			}

			if (intval($_POST['to_cur']) > 0)
			{
				$c_id = intval($_POST['to_cur']);
				foreach ($_POST['packs'] as $id)
				{
					if (!in_array($id,$processed_orders))
					{
						CIBlockElement::SetPropertyValuesEx($id, 42, array(203 => 43, 204 => $c_id, 229 => 71));
						$history_id = AddToHistory($k,$agent_id,$u_id,43,$_POST['c_name'][$c_id]);
						$short_history_id = AddToShortHistory($id,$u_id,71,$_POST['c_name'][$c_id]);
						$processed_orders[] = $id;
						$arResult['MESSAGE'][] = GetMessage("MESS_PERMIT_DELIVERY", array("#ID#" => $_POST['id_in'][$id]));
					}
				}
			}

			if (intval($_POST['to_pvz']) > 0)
			{
				$pvz_new = intval($_POST['to_pvz']);
				foreach ($_POST['packs'] as $id)
				{
					if (!in_array($id, $processed_orders))
					{
						CIBlockElement::SetPropertyValuesEx($id, 42, array(203 => 79, 229 => 74, 337 => $pvz_new));
						$history_id = AddToHistory($id,$agent_id,$u_id,79,$_POST['pvz_name'][$pvz_new]);
						$short_history_id = AddToShortHistory($id,$u_id,74,$_POST['pvz_name'][$pvz_new]);
						$arResult['MESSAGE'][] = GetMessage("MESS_TO_PVZ", array("#ID#" => $_POST['id_in'][$id],"#PVZ#"=>$_POST['pvz_name'][$pvz_new]));
					}
				}
			}
		}
		
		$arResult["TITLE"] = GetMessage("TTL_WAREHOUSE");
		$arResult["COURIERS"] = TheListOfCouriers($agent_id,0,"Y");
		$arResult["LIST"] = GetListPacksCurrentAgent($agent_id, array(40), false, false,intval($_GET['type_delivery']));
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"],$arResult["LIST"]["COUNT"]);
		$arResult["PVZ_LIST"] = TheListOfPVZ($agent_id,0,false,0,false);
	}
	
	/****************заказы на доставке****************/
	if ($mode == 'on_delivery')
	{
		if (isset($_POST['save']))
		{
			$summ_upload = 0;
			foreach ($_POST['operation'] as $p => $oper)
			{
				if (intval($oper) > 0)
				{
					if ($oper == '001')
					{
						CIBlockElement::SetPropertyValuesEx($p, 42, array(203 => 44, 229 => 72, 218 => 61, 267 => $_POST['date_delivery'][$p]));
						$add_to_period = AddElementToPeriod($p, 284, $u_id);
						$history_id = AddToHistory($p,$agent_id,$u_id,44,$_POST['fio'][$p]);
						$short_history_id = AddToShortHistory($p,$u_id,72,$_POST['fio'][$p]);
						$txt = GetMessage("ISSUED_BY_COURIER", array("#DATE#" => $_POST['date_delivery'], "#ID#" => $p, "#NUMBER#" => $_POST['id_in'][$p], "#CUR#" => $_POST['cur_fio'][$p]));
						$el_0 = new CIBlockElement;
						$arLoadProductArray_0 = array(
							"MODIFIED_BY" => $u_id,
							"IBLOCK_ID" => 50,
							"DETAIL_TEXT" => $txt,
							"DETAIL_TEXT_TYPE" => "html",
							"PROPERTY_VALUES" => array(
								234 => $agent_array['id'],
								235 => $_POST['shop'][$p],
								236 => 152
							),
							"NAME" => GetMessage("ISSUED_BY_COURIER_TITLE", array("#NUMBER#" => $_POST['id_in'][$p]))
						);
						$qw = $el_0->Add($arLoadProductArray_0);
						SendMessageMailNew(
							$_POST['shop'][$p],
							$agent_array['id'],
							114,
							166,
							array(
								"ID" => $_POST['id_in'][$p],
								"DATE_RES" => $_POST['date_delivery'][$p],
								"ID_MESS" => $qw,
								"FROM" => GetMessage("BY_COURIER", array("#FIO#" => $_POST['cur_fio'][$p])),
								"RESIVER" => $_POST['fio'][$p]
							)
						);
						$trans_id = AddTransaction(101,'',$agent_array['id'],$u_id,$_POST['date_delivery'][$p],$_POST['summ'][$p],'',GetMessage("TRANS_NAME_1"),$p);
						$summ_upload = $summ_upload + $_POST['summ'][$p];
						$arResult['MESSAGE'][] = GetMessage("MESS_PACK_DELIVERED", array("#ID#" => $p, "#NUMBER#" => $_POST['id_in'][$p]));
					}
					else
					{
						CIBlockElement::SetPropertyValuesEx($p, 42, array(203 => 40, 229 => 69, 204 => false, 337 => false, 442 => 1));
						$history_id = AddToHistory($p,$agent_id,$u_id,40,$arParams["STATUS"][$oper]);
						$short_history_id = AddToShortHistory($p,$u_id,69,$arParams["STATUS"][$oper]);
						$arResult['MESSAGE'][] = GetMessage("MOVED_TO_WAREHOUSE", array("#ID#" => $p, "#NUMBER#" => $_POST['id_in'][$p]));
					}
				}
			}
			if ($summ_upload > 0)
			{
				$db_props = CIBlockElement::GetProperty(40, $agent_array['id'], array("sort" => "asc"), array("CODE"=>"ACCOUNT"));
				if($ar_props = $db_props->Fetch())
				{
					$summ_agent = $ar_props["VALUE"];
				}
				else
				{
					$summ_agent = 0;
				}
				$summ_agent = $summ_agent + $summ_upload;
				CIBlockElement::SetPropertyValuesEx($agent_array['id'], 40, array(219 => $summ_agent));
				$arResult['MESSAGE'][] = GetMessage("MESS_BALANCE", array("#SUMM#"=>CurrencyFormat($summ_agent,"RUU")));
			}
		}
		$arResult["TITLE"] = GetMessage("TTL_DEPARTURE_DELIVERY");
		$arResult["LIST"] = GetListPacksCurrentAgent($agent_id, 43);
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"],$arResult["LIST"]["COUNT"]);
	}
	
	/***************доставленные заказы****************/
	if ($mode == 'archive')
	{
		$arResult["TITLE"] = GetMessage("TTL_CONSIGNMENT_DELIVERED");
		if (strlen($_GET['date_from']))
		{
			$date_from = $_GET['date_from'];
		}
		else
		{
			$date_from = false;
		}
		if (strlen($_GET['date_to']))
		{
			$date_to = $_GET['date_to'];
		}
		else
		{
			$date_to = false;
		}
		$arResult["LIST"] = GetListPacksCurrentAgent($agent_id, 44, $date_from, $date_to, 0, $_GET['number']);
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"],$arResult["LIST"]["COUNT"]);
	}
}

    //dump ($arResult['MODE']);

$this->IncludeComponentTemplate($componentPage);
