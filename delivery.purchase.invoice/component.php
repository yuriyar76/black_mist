<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/components/black_mist/delivery.packages/functions.php');

CModule::IncludeModule('iblock');
CModule::IncludeModule("currency");
$u_id = $USER->GetID();
$arResult["CURRENT_USER"] = $u_id;

$agent_array = GetCurrentAgent($u_id);
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

/**************************************************/
/************************УК************************/
/**************************************************/

if ($agent_array['type'] == 51)
{
	$modes = array(
		"list_purchase",
		"list_purchase_outgo",
		"list_correction",
		"list_correction_price",
		"add_purchase",
		"add_purchase_outgo",
		"add_correction",
		"add_correction_price",
		"edit_purchase",
		"edit_purchase_outgo",
		"edit_correction",
		"edit_correction_price",
		"purchase",
		"purchase_outgo",
		"correction",
		"correction_price",
		"print_purchase",
		"print_purchase_outgo",
		"print_correction",
		"print_correction_price",
		"accounting",
		"accounting_xls",
		"goods_xls",
		"good_corrections_price"
	);
	$arResult["MENU"] = array(
		"list_purchase" => GetMessage("MENU_1"),
		"list_purchase_outgo" => GetMessage("MENU_2"),
		"list_correction" => GetMessage("MENU_3"),
		"list_correction_price" => GetMessage("MENU_4")
	);
	foreach ($arResult["MENU"] as $m => $name)
	{
		if ($arParams["PERM"][$agent_array["type"]][$m][$arResult["ROLE_USER"]] == "C")
			unset($arResult["MENU"][$m]);
	}
	if (in_array($_GET["mode"], $modes))
	{
		$mode = $_GET["mode"];
	}
	else
	{
		if ($arParams["MODE"])
			$mode = $arParams["MODE"];
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
		$arResult["MODE"] = $mode;
		$componentPage = "upr_".$mode;
	}
	else
	{
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	}
	if (isset($arParams["PERM"][$agent_array["type"]]["ALL"]))
	{
		$arResult["PERM"] = $arParams["PERM"][$agent_array["type"]]["ALL"];
	}
	else
	{
		$arResult["PERM"] = $arParams["PERM"][$agent_array["type"]][$mode][$arResult["ROLE_USER"]];
	}
	if ($arResult["PERM"] == "C")
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	
	if (intval($_GET["current_shop"]) > 0)
	{
		$_SESSION["CURRNET_SHOP"] = intval($_GET["current_shop"]);
	}
	
	/**********оформление приходной накладной**********/
	if ($mode == "add_purchase")
	{
		$current_shop = intval($_SESSION["CURRNET_SHOP"]);
		$current_folder = intval($_SESSION["CURRNET_FOLDER"]);
		if ($current_shop > 0)
		{
			$arResult["SHOP"] = GetCompany($current_shop);
			$arResult["TITLE"] = GetMessage("TITLE_1");
			$_SESSION["CurrentStep"] = (isset($_SESSION["CurrentStep"])) ? $_SESSION["CurrentStep"] : 1;
			if ($_SESSION["CurrentStep"] == 1)
			{
				if (isset($_POST["save_1"]))
				{
					if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
					{
						$_POST = array();
						$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
					}
					else
					{
						$_SESSION[$_POST['key_session']] = $_POST['rand'];
						if (!strlen($_POST["number"]))
						{
							$arResult["ERRORS"][] = GetMessage("ERR_PURCHASE_OUTGO_NUMBER");
						}
						if (!strlen($_POST["date"]))
						{
							$arResult["ERRORS"][] = GetMessage("ERR_PURCHASE_OUTGO_DATE");
						}
						if (count($arResult["ERRORS"]) == 0)
						{
							$max_id_5 = GetMaxIDIN(64, 5, true, 316, $current_shop);
							$el = new CIBlockElement;
							$PROP = array();
							$PROP[327] = $max_id_5;
							$PROP[316] = $current_shop;
							$PROP[317] = $_POST["date"];
							$PROP[318] = trim($_POST["number"]);
							$PROP[319] = $agent_array['id'];
							$PROP[370] = 170;
							$arLoadProductArray = array(
								"MODIFIED_BY" => $u_id,
								"IBLOCK_SECTION_ID" => false,
								"IBLOCK_ID" => 64,
								"NAME" => GetMessage("TITLE_PURCHASE_ELEMENT", array("#NUMBER#" => $PROP[318], "#DATE#" => $PROP[317])),
								"PROPERTY_VALUES"=> $PROP,
								"ACTIVE" => "Y"
							);
							$_SESSION["nakl_ID"] = $el->Add($arLoadProductArray);
							$_SESSION["CurrentStep"] = 2;
							LocalRedirect("/goods/purchase.invoice.php?mode=add_purchase");
						}
					}
				}
			}
			if ($_SESSION["CurrentStep"] == 2)
			{
				$arResult["Purchase"] = GetOnePurchase($_SESSION["nakl_ID"], 0, array("ID" => "asc"));
				if (isset($_POST["add"]))
				{
					if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
					{
						$_POST = array();
						$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
					}
					else
					{
						$_SESSION[$_POST['key_session']] = $_POST['rand'];
						if (intval($_POST["product_id"][0]) > 0)
						{
							$productId = intval($_POST["product_id"][0]);
							$new_article = htmlspecialcharsBack($_POST['article'][0]);
						}
						else
						{
							$el = new CIBlockElement;
							$PROP = array();
							if (strlen($_POST['article'][0]))
							{
								$PROP[294] = $new_article = htmlspecialcharsBack($_POST['article'][0]);
								$make_article = false;
							}
							else
							{
								$make_article = true;
								$db_props = CIBlockElement::GetProperty(40, $current_shop, array("sort" => "asc"), Array("ID"=>359));
								if($ar_props = $db_props->Fetch())
								{
									$new_article = $ar_props["VALUE"].'_';
								}
							}
							$PROP[296] = str_replace(',','.',$_POST['weight'][0]);
							$PROP[297] = str_replace(',','.',$_POST['price'][0]);
							$PROP[295] = $current_shop;
							$PROP[299] = 0;
							$arLoadProductArray = array(
								"MODIFIED_BY" => $u_id,
								"IBLOCK_SECTION_ID" => $current_folder,
								"IBLOCK_ID" => 62,
								"NAME" => htmlspecialcharsBack($_POST['name'][0]),
								"PROPERTY_VALUES" => $PROP,
								"ACTIVE" => "Y"
							);
							$productId = $el->Add($arLoadProductArray);
							if ($make_article)
							{
								$new_article .= $productId;
								CIBlockElement::SetPropertyValuesEx($productId, 62, array(294 => $new_article));
							}
						}
						$el = new CIBlockElement;
						$PROP = array();
						$PROP[321] = $productId;
						$PROP[323] = $new_article;
						$PROP[324] = str_replace(',','.',$_POST['weight'][0]);
						$PROP[326] = str_replace(',','.',$_POST['price'][0]);
						$PROP[320] = $arResult["Purchase"]["ID"];
						$PROP[325] = (intval($_POST['count'][0]) == 0) ? 1 : intval($_POST['count'][0]);
						$PROP[351] = 153;
						$arLoadProductArray = array(
							"MODIFIED_BY" => $u_id,
							"IBLOCK_SECTION_ID" => false,
							"IBLOCK_ID" => 65,
							"NAME" => htmlspecialcharsBack($_POST['name'][0]),
							"PROPERTY_VALUES" => $PROP,
							"ACTIVE" => "Y"
						);
						$zapis_id = $el->Add($arLoadProductArray);
						unset($arResult["Purchase"]);
						$arResult["Purchase"] = GetOnePurchase($_SESSION["nakl_ID"], 0, array("ID" => "asc"));
					}
				}
				if (isset($_POST["save_2"]))
				{
					if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
					{
						$_POST = array();
						$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
					}
					else
					{
						$_SESSION[$_POST['key_session']] = $_POST['rand'];
						foreach ($arResult["Purchase"]["GOODS"] as $arG)
						{
							$newcount = $arG["COUNT_NOW"] + $arG["PROPERTY_325_VALUE"];
							CIBlockElement::SetPropertyValuesEx($arG["PROPERTY_321_VALUE"], 62, array(
								294 => $arG["PROPERTY_323_VALUE"],
								296 => $arG["PROPERTY_324_VALUE"],
								297 => $arG["PROPERTY_326_VALUE"],
								299 => $newcount
							));
							$el = new CIBlockElement;
							$res = $el->Update($arG["PROPERTY_321_VALUE"], array("MODIFIED_BY" => $u_id, "NAME" => $arG["NAME"]));
						}
						CIBlockElement::SetPropertyValuesEx($arResult["Purchase"]["ID"], 64, array(353 => 157));
						$_SESSION["MESSAGE"][] = GetMessage("MES_PURCHASE_SPEND", array("#ID#" => $arResult["Purchase"]["ID"], "#NUMBER#" => $arResult["Purchase"]["PROPERTY_327_VALUE"]));
						unset($_SESSION["CurrentStep"], $_SESSION["nakl_ID"]);
						LocalRedirect("/goods/purchase.invoice.php?mode=list_purchase");
					}
				}	
			}
		}
		else
		{
			LocalRedirect('/goods/');
		}
	}
	
	/**********оформление расходной накладной**********/
	if ($mode == "add_purchase_outgo")
	{
		$current_shop = intval($_SESSION["CURRNET_SHOP"]);
		$current_folder = intval($_SESSION["CURRNET_FOLDER"]);
		if ($current_shop > 0)
		{
			$arResult["SHOP"] = GetCompany($current_shop);
			$arResult["TITLE"] = GetMessage("TITLE_9");
			$_SESSION["CurrentStep"] = (isset($_SESSION["CurrentStep"])) ? $_SESSION["CurrentStep"] : 1;
			if ($_SESSION["CurrentStep"] == 1)
			{
				if (isset($_POST["save_1"]))
				{
					if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
					{
						$_POST = array();
						$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
					}
					else
					{
						$_SESSION[$_POST['key_session']] = $_POST['rand'];
						if (!strlen($_POST["number"]))
						{
							$arResult["ERRORS"][] = GetMessage("ERR_PURCHASE_OUTGO_NUMBER");
						}
						if (!strlen($_POST["date"]))
						{
							$arResult["ERRORS"][] = GetMessage("ERR_PURCHASE_OUTGO_DATE");
						}
						if (count($arResult["ERRORS"]) == 0)
						{
							$max_id_5 = GetMaxIDIN(64, 5, true, 316, $current_shop);
							$el = new CIBlockElement;
							$PROP = array();
							$PROP[327] = $max_id_5;
							$PROP[316] = $current_shop;
							$PROP[317] = $_POST["date"];
							$PROP[318] = trim($_POST["number"]);
							$PROP[319] = $agent_array['id'];
							$PROP[370] = 171;
							$arLoadProductArray = array(
								"MODIFIED_BY" => $u_id,
								"IBLOCK_SECTION_ID" => false,
								"IBLOCK_ID" => 64,
								"NAME" => GetMessage("TITLE_PURCHASE_OUTGO_ELEMENT", array("#NUMBER#" => $PROP[318], "#DATE#" => $PROP[317])),
								"PROPERTY_VALUES"=> $PROP,
								"ACTIVE" => "Y"
							);
							$_SESSION["nakl_ID"] = $el->Add($arLoadProductArray);
							$_SESSION["CurrentStep"] = 2;
							LocalRedirect("/goods/purchase.invoice.php?mode=add_purchase_outgo");
						}
					}
				}
			}
			if ($_SESSION["CurrentStep"] == 2)
			{
				$arResult["Purchase"] = GetOnePurchase($_SESSION["nakl_ID"]);
				if (isset($_POST["add"]))
				{
					if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
					{
						$_POST = array();
						$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
					}
					else
					{
						$_SESSION[$_POST['key_session']] = $_POST['rand'];
						if (intval($_POST["product_id"][0]) > 0)
						{
							$productId = intval($_POST["product_id"][0]);
							$el = new CIBlockElement;
							$PROP = array();
							$PROP[321] = $productId;
							$PROP[323] = htmlspecialcharsBack($_POST['article'][0]);
							$PROP[324] = str_replace(',','.',$_POST['weight'][0]);
							$PROP[326] = str_replace(',','.',$_POST['price'][0]);
							$PROP[320] = $arResult["Purchase"]["ID"];
							$PROP[325] = (intval($_POST['count'][0]) == 0) ? 1 : intval($_POST['count'][0]);
							$PROP[351] = 154;
							$arLoadProductArray = array(
								"MODIFIED_BY" => $u_id,
								"IBLOCK_SECTION_ID" => false,
								"IBLOCK_ID" => 65,
								"NAME" => htmlspecialcharsBack($_POST['name'][0]),
								"PROPERTY_VALUES" => $PROP,
								"ACTIVE" => "Y"
							);
							$zapis_id = $el->Add($arLoadProductArray);
							unset($arResult["Purchase"]);
							$arResult["Purchase"] = GetOnePurchase($_SESSION["nakl_ID"]);
						}
						else
						{
							$arResult["ERRORS"][] = GetMessage("ERR_PURCHASE_OUTGO_GOOD_NOT_FOUND");
						}
					}
				}
				if (isset($_POST["save_2"]))
				{
					if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
					{
						$_POST = array();
						$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
					}
					else
					{
						$_SESSION[$_POST['key_session']] = $_POST['rand'];
						foreach ($arResult["Purchase"]["GOODS"] as $arG)
						{
							$newcount = $arG["COUNT_NOW"] - $arG["PROPERTY_325_VALUE"];
							if ($newcount >= 0)
							{
								CIBlockElement::SetPropertyValuesEx($arG["PROPERTY_321_VALUE"], 62, array(299 => $newcount));
							}
							else
							{
								CIBlockElement::SetPropertyValuesEx($arG["ID"], 65, array(320 => false));
								$_SESSION["WARNINGS"][] = GetMessage("ERR_PURCHASE_OUTGO_GOOD_NOT_COUNT",array("#NAME#" => $arG["NAME"]));
							}
						}
						CIBlockElement::SetPropertyValuesEx($arResult["Purchase"]["ID"], 64, array(353 => 157));
						$_SESSION["MESSAGE"][] = GetMessage("MES_PURCHASE_OUTGO_SPEND", array("#ID#" => $arResult["Purchase"]["ID"], "#NUMBER#" => $arResult["Purchase"]["PROPERTY_327_VALUE"]));
						unset($_SESSION["CurrentStep"], $_SESSION["nakl_ID"]);
						LocalRedirect("/goods/purchase.invoice.php?mode=list_purchase_outgo");
					}
				}
			}
		}
		else
		{
			LocalRedirect('/goods/');
		}
	}
	
	/********оформление корректировки остатков*********/
	if ($mode == "add_correction")
	{
		$current_shop = intval($_SESSION["CURRNET_SHOP"]);
		$current_folder = intval($_SESSION["CURRNET_FOLDER"]);
		if ($current_shop > 0)
		{
			$arResult["SHOP"] = GetCompany($current_shop);
			$arResult["TITLE"] = GetMessage('TITLE_12');
			$_SESSION["CurrentStep"] = (isset($_SESSION["CurrentStep"])) ? $_SESSION["CurrentStep"] : 1;
			if ($_SESSION["CurrentStep"] == 1)
			{
				if (isset($_POST["save_1"]))
				{
					if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
					{
						$_POST = array();
						$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
					}
					else
					{
						$_SESSION[$_POST['key_session']] = $_POST['rand'];
						if (!strlen($_POST["date"]))
						{
							$arResult["ERRORS"][] = GetMessage("ERR_CORRECTION_DATE");
						}
						if (count($arResult["ERRORS"]) == 0)
						{
							$max_id_5 = GetMaxIDIN(69, 5, true, 356, $current_shop);
							$el = new CIBlockElement;
							$PROP = array();
							$PROP[355] = $max_id_5;
							$PROP[356] = $current_shop;
							$PROP[357] = $agent_array['id'];
							$PROP[394] = 175;
							$PROP[468] = $_POST["date"];
							$arLoadProductArray = array(
								"MODIFIED_BY" => $u_id,
								"IBLOCK_SECTION_ID" => false,
								"IBLOCK_ID" => 69,
								"NAME" => GetMessage("TITLE_CORRECTION_ELEMENT", array("#NUMBER#" => $max_id_5)),
								"PROPERTY_VALUES" => $PROP,
								"ACTIVE" => "Y"
							);
							$_SESSION["CCORRECTION_ID"] = $el->Add($arLoadProductArray);
							$_SESSION["CurrentStep"] = 2;
							LocalRedirect("/goods/purchase.invoice.php?mode=add_correction");
						}
					}
				}
			}
			if ($_SESSION["CurrentStep"] == 2)
			{
				$arResult["CORRECTION"] = GetOneCorrection($_SESSION["CCORRECTION_ID"]);
				$arResult["TITLE"] = GetMessage("TITLE_CORRECTION");
				if (isset($_POST["add"]))
				{
					if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
					{
						$_POST = array();
						$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
					}
					else
					{
						$_SESSION[$_POST["key_session"]] = $_POST["rand"];
						$PROP = array();
						$newcount = 0;
						if (intval($_POST["product_id"][0]) > 0)
						{
							$productId = intval($_POST["product_id"][0]);
							if (intval($_POST["count_old"][0]) > intval($_POST["count"][0]))
							{
								$PROP[351] = 154;
								$newcount = intval($_POST["count_old"][0]) - intval($_POST["count"][0]);
								$do = true;
							}
							elseif (intval($_POST["count_old"][0]) < intval($_POST["count"][0]))
							{
								$PROP[351] = 153;
								$newcount = intval($_POST["count"][0]) - intval($_POST["count_old"][0]);
								$do = true;
							}
							else
							{
								$do = false;
							}
							if ($do)
							{
								$el = new CIBlockElement;
								$PROP[321] = $productId;
								$PROP[323] = $_POST["article"][0];
								$PROP[324] = $_POST["weight"][0];
								$PROP[326] = $_POST["price"][0];
								$PROP[354] = $arResult["CORRECTION"]["ID"];
								$PROP[325] = $newcount;
								$arLoadProductArray = array(
									"MODIFIED_BY" => $u_id,
									"IBLOCK_SECTION_ID" => false,
									"IBLOCK_ID" => 65,
									"NAME" => $_POST["name"][0],
									"PROPERTY_VALUES"=> $PROP,
									"ACTIVE" => "Y"
								);
								$zapis_id = $el->Add($arLoadProductArray);
								unset($arResult["CORRECTION"]);
								$arResult["CORRECTION"] = GetOneCorrection($_SESSION["CCORRECTION_ID"]);
							}
						}
						else
						{
							$arResult["ERRORS"][] = GetMessage("ERR_CORRECTION_GOOD_NOT_FOUND");
						}
					}
				}
				if (isset($_POST["save_2"]))
				{
					if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
					{
						$_POST = array();
						$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
					}
					else
					{
						$_SESSION[$_POST["key_session"]] = $_POST["rand"];
						foreach ($arResult["CORRECTION"]["GOODS"] as $arG)
						{
							$newcount = $arG["COUNT_NOW"];
							if ($arG["PROPERTY_351_ENUM_ID"] == 153)
							{
								$change = true;
								$newcount = intval($newcount + $arG["PROPERTY_325_VALUE"]);
							}
							elseif ($arG["PROPERTY_351_ENUM_ID"] == 154)
							{
								$newcount = intval($newcount - $arG["PROPERTY_325_VALUE"]);
								$change = true;
							}
							else
							{
								$change = false;
							}
							if ($change)
							{
								CIBlockElement::SetPropertyValuesEx($arG["PROPERTY_321_VALUE"], 62, array(299 => $newcount));
							}
						}
						CIBlockElement::SetPropertyValuesEx($arResult["CORRECTION"]["ID"], 69, array(358 => 158));
						$_SESSION["MESSAGE"][] = GetMessage("MES_CORRECTION_SPEND", array("#ID#" => $arResult["CORRECTION"]["ID"], "#NUMBER#" => $arResult["CORRECTION"]["PROPERTY_355_VALUE"]));
						unset($_SESSION["CurrentStep"], $_SESSION["CCORRECTION_ID"]);
						LocalRedirect("/goods/purchase.invoice.php?mode=list_correction");
					}
				}
			}
		}
		else
		{
			LocalRedirect('/goods/');
		}
	}
	
	/***********оформление корректировки цен***********/
	if ($mode == "add_correction_price")
	{
		$current_shop = intval($_SESSION['CURRNET_SHOP']);
		if ($current_shop > 0)
		{
			$arResult["TITLE"] = GetMessage("TITLE_CORRECTION_PRICE_NEW");
			$arResult["SHOP"] = GetCompany($current_shop);
			if (isset($_POST["add"]))
			{
				if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
				{
					$_POST = array();
					$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
				}
				else
				{
					$_SESSION[$_POST['key_session']] = $_POST['rand'];
					if (intval($_POST["product_id"][0]) > 0)
					{
						if (isset($_SESSION["correctionID"]))
						{
							$correctionID = $_SESSION["correctionID"];
						}
						else
						{
							$el = new CIBlockElement;
							$max_n = GetMaxIDIN(69, 5, true, 356, $current_shop);
							$arLoadProductArray = array(
								"MODIFIED_BY" => $u_id, 
								"IBLOCK_SECTION_ID" => false,
								"IBLOCK_ID" => 69,
								"PROPERTY_VALUES"=> array(
									355 => $max_n,
									356 => $current_shop,
									357 => $agent_array['id'],
									358 => false,
									394 => 176
								),
								"NAME" => GetMessage("CORRECTION_PRICE_NAME_ELEMENT", array("#NUMBER#" => $max_n)),
								"ACTIVE" => "Y"
							);
							$correctionID = $el->Add($arLoadProductArray);
							$_SESSION["correctionID"] = $correctionID;
						}
						$el = new CIBlockElement;
						$arLoadProductArray_2 = array(
							"MODIFIED_BY" => $u_id, 
							"IBLOCK_SECTION_ID" => false,
							"IBLOCK_ID" => 74,
							"PROPERTY_VALUES"=> array(
								395 => $correctionID,
								396 => $_POST["product_id"][0],
								397 => $_POST["prev_price"][0],
								398 => $_POST["price"][0],
								399 => $_POST["article"][0],
								400 => $_POST["weight"][0],
								401 => $_POST["count"][0],
							),
							"NAME" => $_POST["name"][0],
							"ACTIVE" => "Y"
						);
						$cor_el_id = $el->Add($arLoadProductArray_2);
					}
					else
					{
						$arResult["ERRORS"][] = GetMessage("CORRECTION_PRICE_GOOD_NOT_FOUND");
					}
				}
			}
			
			if (isset($_POST["close"]))
			{
				if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
				{
					$_POST = array();
					$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
				}
				else
				{
					$_SESSION[$_POST['key_session']] = $_POST['rand'];
					$correction = GetOneCorrection($_SESSION["correctionID"]);
					foreach ($correction["GOODS"] as $arGood)
					{
						CIBlockElement::SetPropertyValuesEx($arGood["PROPERTY_396_VALUE"], 62, array(297 => $arGood["PROPERTY_398_VALUE"]));
					}
					CIBlockElement::SetPropertyValuesEx($correction["ID"], 69, array(358 => 158));
					$send_params = array(
						"DATE_SEND" => date('d.m.Y H:i'),
						"COR_ID" => $correction["ID"],
						"COR_NUMBER" => $correction["PROPERTY_355_VALUE"],
						"LINK_TO_MSG" => ''
					);
					$qw = SendMessageInSystem($u_id, $agent_array['id'], $correction["PROPERTY_356_VALUE"], GetMessage("CORRECTION_PRICE_MAIL_TITLE_SHOP"), 180, "", "", 174, $send_params);
					$send_params["LINK_TO_MSG"] = GetMessage("LINK_TO_MSG", array("#ID#" => $qw));
					SendMessageMailNew($correction["PROPERTY_356_VALUE"], $agent_array['id'], 179, 174, $send_params);
					$_SESSION["MESSAGE"][] = GetMessage("MES_CORRECTION_PRICE_SPEND", array("#ID#" => $correction["ID"], "#NUMBER#" => $correction["PROPERTY_355_VALUE"]));
					unset($_SESSION["correctionID"]);
					LocalRedirect("/goods/purchase.invoice.php?mode=list_correction_price");
				}
			}
			
			$arResult["INFO"] = false;
			if (isset($_SESSION["correctionID"]))
			{
				$arResult["INFO"] = GetOneCorrection($_SESSION["correctionID"]);
				$arResult["TITLE"] = GetMessage("CORRECTION_PRICE_NAME_ELEMENT", array("#NUMBER#" => $arResult["INFO"]["PROPERTY_355_VALUE"]));
			}
		}
		else
		{
			LocalRedirect('/goods/');
		}
	}
	
	
	
	
	
	/********редактирование приходной накладной********/
	if ($mode == 'edit_purchase')
	{
		if (isset($_POST["spend"]))
		{
			foreach ($_POST["product_id"] as $rowId => $product_id)
			{
				CIBlockElement::SetPropertyValuesEx($rowId, 65, array(324 => str_replace(",",".",$_POST["weight"][$rowId]), 325 => intval($_POST["count"][$rowId]), 326 => str_replace(",",".",$_POST["price"][$rowId])));
				$db_props = CIBlockElement::GetProperty(62, $product_id, array("sort" => "asc"), array("ID"=>299));
				$ar_props = $db_props->Fetch();
				$newcount = intval($ar_props["VALUE"]) + intval($_POST["count"][$rowId]);
				CIBlockElement::SetPropertyValuesEx($_POST["product_id"][$rowId], 62, array(
					296 => str_replace(",",".",$_POST["weight"][$rowId]), 
					299 => $newcount,
					297 => $_POST["price"][$rowId]
					)
				);
			}
			CIBlockElement::SetPropertyValuesEx($_POST["invoice_id"], 64, array(353 => 157));
			$_SESSION["MESSAGE"][] = GetMessage("MES_PURCHASE_SPEND", array("#ID#" => $_POST["invoice_id"], "#NUMBER#" => $_POST["number"]));
			LocalRedirect("/goods/purchase.invoice.php?mode=list");
		}
		
		if (isset($_POST["add"]))
		{
			$shop_info = GetAgentInfo($_POST["shop_id"]);
			if (intval($_POST["product_id"][0]) > 0)
			{
				$productId = intval($_POST["product_id"][0]);
				$new_article = htmlspecialcharsBack($_POST['article'][0]);
			}
			else
			{
				$el = new CIBlockElement;
				$PROP = array();
				if (strlen($_POST['article'][0]))
				{
					$PROP[294] = $new_article = htmlspecialcharsBack($_POST['article'][0]);
					$make_article = false;
				}
				else
				{
					$make_article = true;
					$db_props = CIBlockElement::GetProperty(40, $_POST["shop_id"], array("sort" => "asc"), array("ID"=>359));
					if($ar_props = $db_props->Fetch())
					{
						$new_article = $ar_props["VALUE"].'_';
					}
				}
				$PROP[296] = str_replace(',','.',$_POST['weight'][0]);
				$PROP[297] = str_replace(',','.',$_POST['price'][0]);
				$PROP[295] = $_POST["shop_id"];
				$PROP[299] = 0;
				$arLoadProductArray = array(
					"MODIFIED_BY" => $u_id,
					"IBLOCK_SECTION_ID" => $shop_info["PROPERTY_FOLDER_VALUE"],
					"IBLOCK_ID" => 62,
					"NAME" => htmlspecialcharsBack($_POST['name'][0]),
					"PROPERTY_VALUES" => $PROP,
					"ACTIVE" => "Y"
				);
				$productId = $el->Add($arLoadProductArray);
				if ($make_article)
				{
					$new_article .= $productId;
					CIBlockElement::SetPropertyValuesEx($productId, 62, array(294 => $new_article));
				}
			}
			$el = new CIBlockElement;
			$PROP = array();
			$PROP[321] = $productId;
			$PROP[323] = $new_article;
			$PROP[324] = str_replace(',','.',$_POST['weight'][0]);
			$PROP[326] = str_replace(',','.',$_POST['price'][0]);
			$PROP[320] = $_POST["invoice_id"];
			$PROP[325] = (intval($_POST['count'][0]) == 0) ? 1 : intval($_POST['count'][0]);
			$PROP[351] = 153;
			$arLoadProductArray = array(
				"MODIFIED_BY" => $u_id,
				"IBLOCK_SECTION_ID" => false,
				"IBLOCK_ID" => 65,
				"NAME" => htmlspecialcharsBack($_POST['name'][0]),
				"PROPERTY_VALUES" => $PROP,
				"ACTIVE" => "Y"
			);
			$zapis_id = $el->Add($arLoadProductArray);
		}
		
		if(isset($_POST['delete']))
		{
			CIBlockElement::Delete($_POST['delete_row_id']);
		}
		
		$arResult["INFO"] = false;
		if (intval($_GET['id'] > 0))
		{
			$res = CIBlockElement::GetByID(intval($_GET['id']));
			if($ar_res = $res->GetNext())
			{
				$arResult["INFO"] = GetOnePurchase(intval($_GET['id']));
				$arResult["TITLE"] = GetMessage("TITLE_3", array("#ID#" => $arResult["INFO"]["PROPERTY_327_VALUE"]));
				if ($arResult["INFO"]["PROPERTY_353_ENUM_ID"] == 157)
				{
					LocalRedirect("/goods/purchase.invoice.php?mode=purchase&id=".$arResult["INFO"]["ID"]);
				}
			}
		}
	}
	
	/********редактирование расходной накладной********/
	if ($mode == "edit_purchase_outgo")
	{
	}
	
	/******редактирование корректировки остатков*******/
	if ($mode == "edit_correction")
	{
	}
	
	/*********редактирование корректировки цен*********/
	if ($mode == "edit_correction_price")
	{
		if (isset($_POST['save']))
		{
			if (count($_POST['price']) > 0)
			{
				foreach ($_POST['price'] as $k => $v)
				{
					CIBlockElement::SetPropertyValuesEx($k, 74, array(398 => $v));
				}
				$arResult["MESSAGE"][] = GetMessage("MES_CORRECTION_PRICE_SAVE", array("#ID#" => $_POST['correction_id'], "#NUMBER#" => $_POST['id_in']));
			}	
		}
		
		if (isset($_POST["spend"]))
		{
			foreach ($_POST["good_id"] as $k => $g_id)
			{
				CIBlockElement::SetPropertyValuesEx($g_id, 62, array(297 => $_POST["price"][$k]));
			}
			CIBlockElement::SetPropertyValuesEx($_POST['correction_id'], 69, array(358 => 158));
			$send_params = array(
				"DATE_SEND" => date('d.m.Y H:i'),
				"COR_ID" => $_POST['correction_id'],
				"COR_NUMBER" => $_POST['id_in'],
				"LINK_TO_MSG" => ''
			);
			$qw = SendMessageInSystem($u_id, $agent_array['id'], $_POST['shop_id'], GetMessage("CORRECTION_PRICE_MAIL_TITLE_SHOP"), 180, "", "",174, $send_params);
			$send_params["LINK_TO_MSG"] = GetMessage("LINK_TO_MSG", array("#ID#" => $qw));
			SendMessageMailNew($_POST['shop_id'], $agent_array['id'], 179, 174, $send_params);
			$arResult["MESSAGE"][] = GetMessage("MES_CORRECTION_PRICE_SPEND", array("#ID#" => $_POST['correction_id'], "#NUMBER#" => $_POST['id_in']));
		}
		
		$arResult["INFO"] = GetOneCorrection(intval($_GET['id']));
		if ($arResult["INFO"])
		{
			if ($arResult["INFO"]["PROPERTY_358_ENUM_ID"] != 158)
			{
				$arResult["EDIT"] = true;
				if ($arResult["INFO"]["CREATED_BY"] == $u_id)
				{
					$arResult["EDIT_PRICES"] = true;
				}
				else
				{
					$arResult["EDIT_PRICES"] = false;
				}
			}
			else
			{
				$arResult["EDIT"] = false;
			}
			$arResult["TITLE"] = GetMessage("TITLE_15", array("#ID#"=> $arResult["INFO"]["PROPERTY_355_VALUE"]));
		}
		else
		{
			$arResult["TITLE"] = GetMessage("TITLE_16");
		}
	}
	
	
	
	
	/************список приходных накладных************/
	if ($mode == "list_purchase")
	{
		$current_shop = intval($_SESSION['CURRNET_SHOP']);
		if ($current_shop > 0)
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
					$arDelete = array();
					foreach ($_POST['ids'] as $key)
					{
						$info_purch = GetOnePurchase($key);
						foreach ($info_purch['GOODS'] as $g)
						{
							$arDelete[] = $g["ID"];
						}
						$arDelete[] = $key;
					}
					if (count($arDelete) > 0)
					{
						foreach ($arDelete as $del)
						{
							CIBlockElement::Delete($del);
						}
						$arResult["MESSAGE"][] = GetMessage("MES_PURCHASE_DELETE");
					}
				}
			}
			$arResult["SHOP"] = GetCompany($current_shop);
			$arResult["TITLE"] = GetMessage("TITLE_2", array("#NAME#" => $arResult["SHOP"]["NAME"]));
			$arResult["LIST"] = GetListPurchase($current_shop, false, false, 170);
			$arResult['NAV_STRING'] = $arResult["LIST"]['NAV_STRING'];
			unset($arResult["LIST"]['NAV_STRING']);
		}
		else
		{
			LocalRedirect('/goods/');
		}
	}
	
	/************список расходных накладных************/
	if ($mode == "list_purchase_outgo")
	{
		$current_shop = intval($_SESSION['CURRNET_SHOP']);
		if ($current_shop > 0)
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
					$arDelete = array();
					foreach ($_POST['ids'] as $key)
					{
						$info_purch = GetOnePurchase($key);
						foreach ($info_purch['GOODS'] as $g)
						{
							$arDelete[] = $g["ID"];
						}
						$arDelete[] = $key;
					}
					if (count($arDelete) > 0)
					{
						foreach ($arDelete as $del)
						{
							CIBlockElement::Delete($del);
						}
						$arResult["MESSAGE"][] = GetMessage("MES_PURCHASE_OUTGO_DELETE");
					}
				}
			}
			
			$arResult["SHOP"] = GetCompany($current_shop);
			$arResult["TITLE"] = GetMessage("TITLE_10",array('#NAME#'=>$arResult["SHOP"]["NAME"]));
			$arResult["LIST"] = GetListPurchase($current_shop,false,false,171);
			$arResult['NAV_STRING'] = $arResult["LIST"]['NAV_STRING'];
			unset($arResult["LIST"]['NAV_STRING']);
		}
		else
		{
			LocalRedirect('/goods/');
		}
	}
	
	/**********список корректировок остатков***********/
	if ($mode == "list_correction")
	{
		$current_shop = intval($_SESSION['CURRNET_SHOP']);
		if ($current_shop > 0)
		{
			$arResult["SHOP"] = GetCompany($current_shop);
			$arResult["TITLE"] = GetMessage("TITLE_5",array('#NAME#'=>$arResult["SHOP"]["NAME"]));
			$arResult["LIST"] = GetListCorrections($current_shop);
			$arResult['NAV_STRING'] = $arResult["LIST"]['NAV_STRING'];
			unset($arResult["LIST"]['NAV_STRING']);
		}
		else
		{
			LocalRedirect('/goods/');
		}
	}
	
	/*************список корректировок цен*************/
	if ($mode == "list_correction_price")
	{
		$current_shop = intval($_SESSION['CURRNET_SHOP']);
		if ($current_shop > 0)
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
					$arDeletes = array();
					foreach ($_POST['ids'] as $v)
					{
						$correction = GetOneCorrection($v);
						foreach ($correction["GOODS"] as $g)
						{
							$arDeletes[] = $g["ID"];
						}
						$arDeletes[] = $v; 
					}
					if (count($arDeletes) > 0)
					{
						foreach ($arDeletes as $d)
						{
							CIBlockElement::Delete($d);
						}
						$arResult["MESSAGE"][] = GetMessage("MES_CORRECTION_PRICE_DELETE");
					}
				}
			}
			
			$arResult["SHOP"] = GetCompany($current_shop);
			$arResult["TITLE"] = GetMessage("TITLE_14", array("#NAME#"=> $arResult["SHOP"]["NAME"]));
			$arResult["LIST"] = GetListCorrections($current_shop,false,false,176);
			$arResult['NAV_STRING'] = $arResult["LIST"]['NAV_STRING'];
			unset($arResult["LIST"]['NAV_STRING']);
			$arResult["HOW_MANY_EDIT"] = 0;
			foreach ($arResult["LIST"] as $k => $r)
			{
				if ($r["PROPERTY_358_ENUM_ID"] != 158)
				{
					$arResult["LIST"][$k]["EDIT"] = true;
					if ($r["CREATED_BY"] == $u_id)
					{
						$arResult["LIST"][$k]["EDIT_PRICES"] = true;
						$arResult["HOW_MANY_EDIT"]++;
					}
					else
					{
						$arResult["LIST"][$k]["EDIT_PRICES"] = false;
					}
				}
				else
				{
					$arResult["LIST"][$k]["EDIT"] = false;
				}
			}
		}
		else
		{
			LocalRedirect('/goods/');
		}
	}
	

	
	/***************приходная накладная****************/
	if ($mode == "purchase")
	{
		$current_shop = intval($_SESSION['CURRNET_SHOP']);
		$n_id = intval($_GET['id']);
		if ($n_id > 0)
		{
			$arResult["INFO"] = GetOnePurchase($n_id);
			if ($arResult["INFO"])
			{
				if ($arResult["INFO"]["PROPERTY_370_ENUM_ID"] == 170)
				{
					$arResult["TITLE"] = GetMessage("TITLE_3",array('#ID#'=>$arResult["INFO"]['PROPERTY_327_VALUE']));
				}
				else
				{
					$arResult["TITLE"] = GetMessage("TITLE_11",array('#ID#'=>$arResult["INFO"]['PROPERTY_327_VALUE']));
				}
			}
			else $arResult["TITLE"] = GetMessage("TITLE_4");
		}
		else $arResult["TITLE"] = GetMessage("TITLE_4");
	}
	
	/***************расходная накладная****************/
	if ($mode == "purchase_outgo")
	{
		$current_shop = intval($_SESSION['CURRNET_SHOP']);
		$n_id = intval($_GET['id']);
		if ($n_id > 0)
		{
			$arResult["INFO"] = GetOnePurchase($n_id);
			if ($arResult["INFO"])
			{
				if ($arResult["INFO"]["PROPERTY_370_ENUM_ID"] == 170)
				{
					$arResult["TITLE"] = GetMessage("TITLE_3",array('#ID#'=>$arResult["INFO"]['PROPERTY_327_VALUE']));
				}
				else
				{
					$arResult["TITLE"] = GetMessage("TITLE_11",array('#ID#'=>$arResult["INFO"]['PROPERTY_327_VALUE']));
				}
			}
			else $arResult["TITLE"] = GetMessage("TITLE_4");
		}
		else $arResult["TITLE"] = GetMessage("TITLE_4");
	}
	
	/**************корректировка остатков**************/
	if ($mode == "correction")
	{
		if (intval($_GET['id']) > 0)
		{
			$arResult["INFO"] = GetOneCorrection(intval($_GET['id']));
			if ($arResult["INFO"])
			{
				$arResult["TITLE"] = GetMessage("TITLE_7",array('#ID#'=>$arResult["INFO"]['PROPERTY_355_VALUE']));
			}
			else
			{
				$arResult["TITLE"] = GetMessage("TITLE_6");
			}
		}
		else
		{
			$arResult["TITLE"] = GetMessage("TITLE_6");
		}
	}
		
	/****************корректировка цен*****************/
	if ($mode == "correction_price")
	{
		if (isset($_POST['accept']))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
				foreach ($_POST['new_price'] as $good_id => $new_price)
				{
					CIBlockElement::SetPropertyValuesEx($good_id, 62, array(297 => $new_price));
				}
				CIBlockElement::SetPropertyValuesEx($_POST['correction_id'], 69, array(358 => 158));
				$arResult['MESSAGE'][] = GetMessage("MES_CORRECTION_PRICE_SPEND", array("#ID#" => $_POST['correction_id'], "#NUMBER#" => $_POST['id_in']));
				
			}
		}
		$arResult["INFO"] = GetOneCorrection($_GET['id']);
		$arResult["TITLE"] = GetMessage("CORRECTION_PRICE_NAME_ELEMENT", array("#NUMBER#" => $arResult["INFO"]["PROPERTY_355_VALUE"]));
	}
	


	
	/************печать приходной накладной************/
	if ($mode == "print_purchase")
	{
		$n_id = intval($_GET['id']);
		if ($n_id > 0)
		{
			$arResult["INFO"] = GetOnePurchase($n_id);
			$arResult["COMAPNY"] = GetCompany($arResult["INFO"]["PROPERTY_316_VALUE"]);
			$arResult["UK"] = GetCompany($agent_array['id']);
		}
		else
		{
			$arResult["INFO"] = false;
		}
	}
	


	
	/****************движение по товару****************/
	if (($mode == "accounting") || ($mode == "accounting_xls"))
	{
		$good_id = intval($_GET['good']);
		if ($good_id > 0)
		{
			$res = CIBlockElement::GetByID($good_id);
			if ($ar_res = $res->GetNext())
			{
				$arResult["GOOD"] = $good_id;
				$arResult["TITLE"] = GetMessage("TITLE_ACCOUNTING", array("#NAME#" => $ar_res['NAME']));
				$arResult["TITLE_2"] = GetMessage("TITLE_ACCOUNTING", array("#NAME#" => str_replace('"','',$ar_res['NAME'])));
				$arResult['NAME_OF_GOOD'] = str_replace('"','',$ar_res['NAME']);
				$arResult['ART_OF_GOOD'] = false;
				$db_props = CIBlockElement::GetProperty(62, $good_id, array("sort" => "asc"), array("ID"=>294));
				if($ar_props = $db_props->Fetch())
				{
					$arResult['ART_OF_GOOD'] = $ar_props["VALUE"];
				}
				$arResult['IM_NAME_OF_GOOD'] = false;
				$db_props = CIBlockElement::GetProperty(62, $good_id, array("sort" => "asc"), array("ID"=>295));
				if($ar_props = $db_props->Fetch())
				{
					$arResult['IM_ID_OF_GOOD'] = $ar_props["VALUE"];
					$res_n = CIBlockElement::GetByID($arResult['IM_ID_OF_GOOD']);
					if ($ar_res_n = $res_n->GetNext())
					{
						$arResult['IM_NAME_OF_GOOD'] = $ar_res_n['NAME'];
					}
				}
				
				$arResult["SHOW_COUNTS"] = false;
				if ((strlen($_GET['date_from'])) && (strlen($_GET['date_to'])))
				{
					$arResult["SHOW_COUNTS"] = true;
					$res_0 = CIBlockElement::GetList(
						array("created"=>"asc"),
						array(
							"IBLOCK_ID"=>65,
							"PROPERTY_321"=>$good_id,
							"<DATE_CREATE"=>array($_GET['date_from'].' 00:00:00')
						),
						false,
						false,
						array("DATE_CREATE","PROPERTY_325","PROPERTY_351","PROPERTY_320","PROPERTY_352",'PROPERTY_354','PROPERTY_368')
					);
					$arResult["COUNT_START"] = 0;
					while($ob_0 = $res_0->GetNextElement())
					{
						$a = $ob_0->GetFields();
						if ($a["PROPERTY_351_ENUM_ID"] == 153)
						{
							if (intval($a['PROPERTY_320_VALUE']) > 0)
							{
								$db_props = CIBlockElement::GetProperty(64, $a['PROPERTY_320_VALUE'], array("sort" => "asc"), array("ID"=>353));
								if($ar_props = $db_props->Fetch())
									$opr = $ar_props["VALUE"];
								if ($opr == 157)
								{
									$arResult["COUNT_START"] = $arResult["COUNT_START"] + $a['PROPERTY_325_VALUE'];
								}
							}
							elseif (intval($a['PROPERTY_354_VALUE']) > 0)
							{
								$db_props = CIBlockElement::GetProperty(69, $a['PROPERTY_354_VALUE'], array("sort" => "asc"), array("ID"=>358));
								if($ar_props = $db_props->Fetch())
									$opr = $ar_props["VALUE"];
								if ($opr == 158)
								{
									$arResult["COUNT_START"] = $arResult["COUNT_START"] + $a['PROPERTY_325_VALUE'];
								}
							}
							elseif (intval($a['PROPERTY_368_VALUE']) > 0)
							{
								$arResult["COUNT_START"] = $arResult["COUNT_START"] + $a['PROPERTY_325_VALUE'];
							}
							elseif (intval($a['PROPERTY_352_VALUE']) > 0)
							{
								$arResult["COUNT_START"] = $arResult["COUNT_START"] + $a['PROPERTY_325_VALUE'];
							}
						}
						else
						{
							$arResult["COUNT_START"] = $arResult["COUNT_START"] - $a['PROPERTY_325_VALUE'];
						}
					}
					$arResult["COUNT_END"] = $arResult["COUNT_START"];
					$filter = array("IBLOCK_ID"=>65,"PROPERTY_321"=>$good_id,"><DATE_CREATE"=>array($_GET['date_from'],$_GET['date_to'].' 23:59:59'));
					$res = CIBlockElement::GetList(
						array("created"=>"asc"),
						$filter,
						false,
						false,
						array("DATE_CREATE","PROPERTY_325","PROPERTY_351","PROPERTY_320","PROPERTY_352","PROPERTY_354","PROPERTY_368")
					);
					$arResult["LIST"] = array();
					while($ob = $res->GetNextElement())
					{
						$a = $ob->GetFields();
						/*
						echo '<pre>';
						print_r($a);
						echo '</pre>';
						*/
						if ($a["PROPERTY_351_ENUM_ID"] == 153)
						{
							if (intval($a["PROPERTY_320_VALUE"]) > 0)
							{
								$db_props = CIBlockElement::GetProperty(64, $a['PROPERTY_320_VALUE'], array("sort" => "asc"), array("ID"=>353));
								if($ar_props = $db_props->Fetch())
								{
									$opr = $ar_props["VALUE"];
								}
								if ($opr == 157)
								{
									$zasch = true;
									$prop_block = 64;
									$proop_id = 318;
									$el = $a['PROPERTY_320_VALUE'];
									$txt = GetMessage("NAME_ACCOUNTING_ELEMENT_1");
									$a['LINK'] = '/goods/purchase.invoice.php?mode=purchase&id='.$el;
									$arResult["COUNT_END"] = $arResult["COUNT_END"] + $a['PROPERTY_325_VALUE'];
								}
								else
								{
									$zasch = false;
								}
							}
							elseif (intval($a['PROPERTY_354_VALUE']) > 0)
							{
								$zasch = true;
								$prop_block = 69;
								$proop_id = 355;
								$el = $a['PROPERTY_354_VALUE'];
								$txt = GetMessage("NAME_ACCOUNTING_ELEMENT_2");
								$a['LINK'] = 'javascript:void(0);';
								$arResult["COUNT_END"] = $arResult["COUNT_END"] + $a['PROPERTY_325_VALUE'];
							}
							elseif (intval($a['PROPERTY_368_VALUE']) > 0)
							{
								$zasch = true;
								$prop_block = 71;
								$proop_id = 367;
								$el = $a['PROPERTY_368_VALUE'];
								$txt = GetMessage("NAME_ACCOUNTING_ELEMENT_3");
								$a['LINK'] = 'javascript:void(0);';
								$arResult["COUNT_END"] = $arResult["COUNT_END"] + $a['PROPERTY_325_VALUE'];
							}
							elseif (intval($a['PROPERTY_352_VALUE']) > 0)
							{
								$zasch = true;
								$prop_block = 42;
								$proop_id = 402;
								$el = $a['PROPERTY_352_VALUE'];
								$txt = GetMessage("NAME_ACCOUNTING_ELEMENT_4");
								$a['LINK'] = '/warehouse/index.php?mode=package&id='.$el;
								$arResult["COUNT_END"] = $arResult["COUNT_END"] + $a['PROPERTY_325_VALUE'];
							}
							else
							{
								$zasch = false;
							}
							
						}
						elseif ($a["PROPERTY_351_ENUM_ID"] == 154)
						{
							if (intval($a['PROPERTY_320_VALUE']) > 0)
							{
								$db_props = CIBlockElement::GetProperty(64, $a['PROPERTY_320_VALUE'], array("sort" => "asc"), Array("ID"=>353));
								if($ar_props = $db_props->Fetch())
								{
									$opr = $ar_props["VALUE"];
								}
								if ($opr == 157)
								{
									$zasch = true;
									$prop_block = 64;
									$proop_id = 318;
									$el = $a['PROPERTY_320_VALUE'];
									$txt = GetMessage("NAME_ACCOUNTING_ELEMENT_1");
									$a['LINK'] = '/goods/purchase.invoice.php?mode=purchase_outgo&id='.$el;
									$arResult["COUNT_END"] = $arResult["COUNT_END"] - $a['PROPERTY_325_VALUE'];
								}
								else
								{
									$zasch = false;
								}
							}
							elseif (intval($a['PROPERTY_352_VALUE']) > 0)
							{
								$zasch = true;
								$prop_block = 42;
								$proop_id = 402;
								$el = $a['PROPERTY_352_VALUE'];
								$txt = GetMessage("NAME_ACCOUNTING_ELEMENT_4");
								$a['LINK'] = '/warehouse/index.php?mode=package&id='.$el;
								$arResult["COUNT_END"] = $arResult["COUNT_END"] - $a['PROPERTY_325_VALUE'];
							}
							elseif (intval($a['PROPERTY_354_VALUE']) > 0)
							{
								$zasch = true;
								$prop_block = 69;
								$proop_id = 355;
								$el = $a['PROPERTY_354_VALUE'];
								$txt = GetMessage("NAME_ACCOUNTING_ELEMENT_2");
								$a['LINK'] = '#';
								$arResult["COUNT_END"] = $arResult["COUNT_END"] - $a['PROPERTY_325_VALUE'];
							}
							else
							{
								$zasch = false;
							}
							
						}
						else
						{
							$prop_block = $proop_id = $el = $zasch = false;
						}
						
						if ($zasch)
						{
							$db_props = CIBlockElement::GetProperty($prop_block, $el, array("sort" => "asc"), array("ID"=>$proop_id));
							if($ar_props = $db_props->Fetch())
							{
								$name = $ar_props["VALUE"];
							}
							else
							{
								$name = false;
							}
							$a["NAME_OPER"] = $txt.' №'.$name;
							$arResult["LIST"][] = $a;
						}
							
					}
					if (count($arResult["LIST"])  == 0)
					{
						$arResult["ERRORS"][] = GetMessage("ERR_ACCOUNTING_NO_OPERATIONS", array("#NAME#" => $ar_res['NAME']));
					}
				}
				else
				{
					$arResult["ERRORS"][] = GetMessage("ERR_ACCOUNTING_NO_PERIOD");
				}
			}
			else
			{
				$arResult["TITLE"] = GetMessage("ERR_ACCOUNTING_NO_GOOD");
			}
		}
	}
	
	/**************изменение цен на товар**************/
	if ($mode == "good_corrections_price")
	{
		$good = intval($_GET['element_id']);
		$arResult["LIST"] = GetGoodCorrections($good);
	}
	
	/**************экспорт товаров в xls***************/
	if ($mode == 'goods_xls')
	{
		if (intval($_SESSION['CURRNET_SHOP']) > 0)
		{
			$shop = GetCompany(intval($_SESSION['CURRNET_SHOP']));
			$arResult["TITLE"] = GetMessage("TITLE_UK_GOODS_XLS", array("#NAME#" => $shop["NAME"], "#DATE#" => date('d.m.Y')));"Товары интернет-магазина ".$shop["NAME"]." на ".date('d.m.Y');
			$arResult['GOODS'] = GetGoodsOfShop(intval($_SESSION['CURRNET_SHOP']));
		}
		else
		{
			LocalRedirect('/goods/');
		}
	}
}

/**************************************************/
/************************ИМ************************/
/**************************************************/

if ($agent_array['type'] == 52)
{
	$modes = array(
		"list_purchase",
		"list_purchase_outgo",
		"list_correction",
		"list_correction_price",
		"add_purchase",
		"add_correction_price",
		"edit_purchase",
		"edit_correction_price",
		"purchase",
		"purchase_outgo",
		"correction",
		"correction_price",
		"print_purchase",
		"accounting",
		"accounting_xls",
		"goods_xls",
		"good_corrections_price"
	);
	$arResult["MENU"] = array(
		"list_purchase" => GetMessage("MENU_1"),
		"list_correction_price" => GetMessage("MENU_4")
	);
	foreach ($arResult["MENU"] as $m => $name)
	{
		if ($arParams["PERM"][$agent_array["type"]][$m][$arResult["ROLE_USER"]] == "C")
			unset($arResult["MENU"][$m]);
	}
	if (in_array($_GET["mode"], $modes))
	{
		$mode = $_GET["mode"];
	}
	else
	{
		if ($arParams["MODE"])
			$mode = $arParams["MODE"];
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
		$arResult["MODE"] = $mode;
		$componentPage = "shop_".$mode;
	}
	else
	{
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	}
	if (isset($arParams["PERM"][$agent_array["type"]]["ALL"]))
	{
		$arResult["PERM"] = $arParams["PERM"][$agent_array["type"]]["ALL"];
	}
	else
	{
		$arResult["PERM"] = $arParams["PERM"][$agent_array["type"]][$mode][$arResult["ROLE_USER"]];
	}
	if ($arResult["PERM"] == "C")
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	
	/**************экспорт товаров в xls***************/
	if ($mode == "goods_xls")
	{
		$arResult["TITLE"] = GetMessage("TITLE_GOODS_XLS", array("#DATE#" => date('d.m.Y')));
		$arResult['GOODS'] = GetGoodsOfShop($agent_array['id']);
	}
	
	/*************корректировки цен товара*************/
	if ($mode == "good_corrections_price")
	{
		$good = intval($_GET['element_id']);
		$arResult["LIST"] = GetGoodCorrections($good);
	}
	
	/***********оформление корректировки цен***********/
	if ($mode == "add_correction_price")
	{
		$arResult["TITLE"] = GetMessage("TITLE_CORRECTION_PRICE_NEW");
		if (isset($_POST["add"]))
		{
			if (intval($_POST["product_id"][0]) > 0)
			{
				if (isset($_SESSION["correctionID"]))
				{
					$correctionID = $_SESSION["correctionID"];
				}
				else
				{
					$el = new CIBlockElement;
					$max_n = GetMaxIDIN(69, 5, true, 356, $agent_array['id']);
					$arLoadProductArray = array(
						"MODIFIED_BY" => $u_id, 
						"IBLOCK_SECTION_ID" => false,
						"IBLOCK_ID" => 69,
						"PROPERTY_VALUES"=> array(
							355 => $max_n,
							356 => $agent_array['id'],
							357 => $agent_array['id'],
							358 => false,
							394 => 176
						),
						"NAME" => GetMessage("CORRECTION_PRICE_NAME_ELEMENT", array("#NUMBER#" => $max_n)),
						"ACTIVE" => "Y"
					);
					$correctionID = $el->Add($arLoadProductArray);
					$_SESSION["correctionID"] = $correctionID;
				}
				$el = new CIBlockElement;
				$arLoadProductArray_2 = Array(
					"MODIFIED_BY" => $u_id, 
					"IBLOCK_SECTION_ID" => false,
					"IBLOCK_ID" => 74,
					"PROPERTY_VALUES"=> array(
						395 => $correctionID,
						396 => $_POST["product_id"][0],
						397 => $_POST["prev_price"][0],
						398 => $_POST["price"][0],
						399 => $_POST["article"][0],
						400 => $_POST["weight"][0],
						401 => $_POST["count"][0],
					),
					"NAME" => $_POST["name"][0],
					"ACTIVE" => "Y"
				);
				$cor_el_id = $el->Add($arLoadProductArray_2);
			}
			else
			{
				$arResult["ERRORS"][] = GetMessage("CORRECTION_PRICE_GOOD_NOT_FOUND");
			}
		}
		
		if (isset($_POST["close"]))
		{
			$correction = GetOneCorrection($_SESSION["correctionID"]);
			$send_params = array(
				"DATE_SEND" => date('d.m.Y H:i'),
				"AGENT_ID" => $agent_array['id'],
				"AGENT_NAME" => $agent_array['name'],
				"COR_ID" => $correction["ID"],
				"COR_NUMBER" => $correction["PROPERTY_355_VALUE"],
				"LINK_TO_MSG" => ''
			);
			$qw = SendMessageInSystem($u_id, $agent_array['id'], $agent_array["uk"], GetMessage("CORRECTION_PRICE_MAIL_TITLE"), 178, "", "", 163, $send_params);
			$send_params["LINK_TO_MSG"] = GetMessage("LINK_TO_MSG", array("#ID#" => $qw));
			SendMessageMailNew($agent_array["uk"], $agent_array["id"], 177, 163, $send_params);
			
			unset($_SESSION["correctionID"]);
			$_SESSION["MESSAGE"][] = GetMessage("MES_CORRECTION_PRICE_MAKED", array("#ID#" => $correction["ID"], "#NUMBER#" => $correction["PROPERTY_355_VALUE"]));
			LocalRedirect("/goods/purchase.invoice.php?mode=list_correction_price");
		}
		
		$arResult["INFO"] = false;
		if (isset($_SESSION["correctionID"]))
		{
			$arResult["INFO"] = GetOneCorrection($_SESSION["correctionID"]);
		}
	}
	
	/*************список корректировок цен*************/
	if ($mode == "list_correction_price")
	{
		if (isset($_POST["delete"]))
		{
			$arDelete = array();
			foreach ($_POST["ids"] as $c)
			{
				$correction = GetOneCorrection($c);
				foreach ($correction["GOODS"] as $g)
				{
					$arDelete[] = $g["ID"];
				}
				$arDelete[] = $c;
			}
			if (count($arDelete) > 0)
			{
				foreach ($arDelete as $d)
				{
					CIBlockElement::Delete($d);
				}
				$arResult['MESSAGE'][] = GetMessage("MES_CORRECTION_PRICE_DELETE");
			}

		}
		$arResult["LIST"] = GetListCorrections($agent_array['id'], false, false, 176);
		$arResult['NAV_STRING'] = $arResult["LIST"]['NAV_STRING'];
		unset($arResult["LIST"]['NAV_STRING']);
		$arResult['CURRENT_USER'] = $u_id;
	}
	
	/*********редактирование корректировки цен*********/
	if ($mode == "edit_correction_price")
	{
		LocalRedirect("/goods/purchase.invoice.php?mode=correction_price&id=".$_GET['id']);
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
				if (count($_POST['price']) > 0)
				{
					foreach ($_POST['price'] as $k => $v)
					{
						CIBlockElement::SetPropertyValuesEx($k, 74, array(398 => $v));
					}
					$arResult["MESSAGE"][] = GetMessage("MES_CORRECTION_PRICE_SAVE", array("#ID#" => $_GET['id'], "#NUMBER#" => $_POST['id_in']));
				}
			}
		}
		$arResult["INFO"] = GetOneCorrection(intval($_GET['id']));
		$arResult["TITLE"] = GetMessage("CORRECTION_PRICE_NAME_ELEMENT", array("#NUMBER#" => $arResult["INFO"]["PROPERTY_355_VALUE"]));
		if (($arResult["INFO"]["PROPERTY_358_ENUM_ID"] == 158) || ($arResult["INFO"]["CREATED_BY"] != $u_id))
		{
			LocalRedirect("/goods/purchase.invoice.php?mode=correction_price&id=".intval($_GET['id']));
		}
	}
	
	/**********оформление приходной накладной**********/
	if ($mode == "add_purchase")
	{
		$current_shop = $agent_array['id'];
		$arResult["SHOP"] = GetCompany($current_shop);
		$current_folder = intval($arResult["SHOP"]['PROPERTY_FOLDER_VALUE']);
		if ($current_folder > 0)
		{	
			$arResult["TITLE"] = GetMessage("TITLE_1");
	
			$_SESSION["CurrentStep"] = (isset($_SESSION["CurrentStep"])) ? $_SESSION["CurrentStep"] : 1;
			
			if ($_SESSION["CurrentStep"] == 1)
			{
				if (isset($_POST["save_1"]))
				{
					if (!strlen($_POST["number"]))
					{
						$arResult["ERRORS"][] = GetMessage("ERR_PURCHASE_NUMBER");
					}
					if (!strlen($_POST["date"]))
					{
						$arResult["ERRORS"][] = GetMessage("ERR_PURCHASE_DATE");
					}
					if (count($arResult["ERRORS"]) == 0)
					{
						$max_id_5 = GetMaxIDIN(64, 5, true, 316, $agent_array['id']);	
						$el = new CIBlockElement;
						$PROP = array();
						$PROP[327] = $max_id_5;
						$PROP[316] = $agent_array['id'];
						$PROP[317] = $_POST["date"];
						$PROP[318] = trim($_POST["number"]);
						$PROP[319] = $agent_array['id'];
						$PROP[370] = 170;		
						$arLoadProductArray = array(
							"MODIFIED_BY" => $u_id,
							"IBLOCK_SECTION_ID" => false,
							"IBLOCK_ID" => 64,
							"NAME" => GetMessage("TITLE_PURCHASE_ELEMENT", array("#NUMBER#" => $PROP[318], "#DATE#" => $PROP[317])),
							"PROPERTY_VALUES"=> $PROP,
							"ACTIVE" => "Y"
						);
						$_SESSION["nakl_ID"] = $el->Add($arLoadProductArray);
						$_SESSION["CurrentStep"] = 2;
						LocalRedirect("/goods/purchase.invoice.php?mode=add_purchase");
					}
				}
			}
			
			if ($_SESSION["CurrentStep"] == 2)
			{
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
						CIBlockElement::Delete($_POST['delete_row_id']);
					}
				}
				
				$arResult["Purchase"] = GetOnePurchase($_SESSION["nakl_ID"]);
				
				if (isset($_POST["add"]))
				{
					if (intval($_POST["product_id"][0]) > 0)
					{
						$productId = intval($_POST["product_id"][0]);
						$new_article = htmlspecialcharsBack($_POST['article'][0]);
					}
					else
					{
						$el = new CIBlockElement;
						$PROP = array();
						if (strlen($_POST['article'][0]))
						{
							$PROP[294] = $new_article = htmlspecialcharsBack($_POST['article'][0]);
							$make_article = false;
						}
						else
						{
							$make_article = true;
							$db_props = CIBlockElement::GetProperty(40, $agent_array['id'], array("sort" => "asc"), Array("ID"=>359));
							if($ar_props = $db_props->Fetch())
							{
								$new_article = $ar_props["VALUE"].'_';
							}
						}
						$PROP[296] = str_replace(',','.',$_POST['weight'][0]);
						$PROP[297] = str_replace(',','.',$_POST['price'][0]);
						$PROP[295] = $agent_array['id'];
						$PROP[299] = 0;
						$arLoadProductArray = array(
							"MODIFIED_BY" => $u_id,
							"IBLOCK_SECTION_ID" => $current_folder,
							"IBLOCK_ID" => 62,
							"NAME" => htmlspecialcharsBack($_POST['name'][0]),
							"PROPERTY_VALUES" => $PROP,
							"ACTIVE" => "Y"
						);
						$productId = $el->Add($arLoadProductArray);
						if ($make_article)
						{
							$new_article .= $productId;
							CIBlockElement::SetPropertyValuesEx($productId, 62, array(294 => $new_article));
						}
					}
					$el = new CIBlockElement;
					$PROP = array();
					$PROP[321] = $productId;
					$PROP[323] = $new_article;
					$PROP[324] = str_replace(',','.',$_POST['weight'][0]);
					$PROP[326] = str_replace(',','.',$_POST['price'][0]);
					$PROP[320] = $arResult["Purchase"]["ID"];
					$PROP[325] = (intval($_POST['count'][0]) == 0) ? 1 : intval($_POST['count'][0]);
					$PROP[351] = 153;
					$arLoadProductArray = array(
						"MODIFIED_BY" => $u_id,
						"IBLOCK_SECTION_ID" => false,
						"IBLOCK_ID" => 65,
						"NAME" => htmlspecialcharsBack($_POST['name'][0]),
						"PROPERTY_VALUES" => $PROP,
						"ACTIVE" => "Y"
					);
					$zapis_id = $el->Add($arLoadProductArray);
					unset($arResult["Purchase"]);
					$arResult["Purchase"] = GetOnePurchase($_SESSION["nakl_ID"]);
				}
				if (isset($_POST["save_2"]))
				{
					$arParamsSend = array(
						"SHOP_ID" => $agent_array['id'],
						"SHOP_NAME" => $agent_array['name'],
						"NAKL_ID" => $arResult["Purchase"]["ID"],
						"NAKL_NAME" => $arResult["Purchase"]["PROPERTY_327_VALUE"],
						"DATE_SEND" => date('d.m.Y H:i'),
						"LINK_TO_MSG" => ''
					);
					$qw = SendMessageInSystem($u_id, $agent_array['id'], $agent_array["uk"], GetMessage("MAIL_PURCHASE_REQUEST"), 163, "", "", 169, $arParamsSend);
					$arParamsSend["LINK_TO_MSG"] = GetMessage("LINK_TO_MSG", array("#ID#" => $qw));
					SendMessageMailNew($agent_array["uk"], $agent_array['id'], 162, 169, $arParamsSend);
					unset($_SESSION["CurrentStep"], $_SESSION["nakl_ID"]);
					$_SESSION["MESSAGE"][] = GetMessage("MES_PURCHASE_MAKED", array("#ID#" => $arResult["Purchase"]["ID"], "#NUMBER#" => $arResult["Purchase"]["PROPERTY_327_VALUE"]));
					LocalRedirect("/goods/purchase.invoice.php?mode=list_purchase");
				}
			}
		}
	}
	
	/********редактирование приходной накладной********/
	if ($mode == 'edit_purchase')
	{
		LocalRedirect("/goods/purchase.invoice.php?mode=purchase&id=".$_GET['id']);
		$arResult["SHOP"] = GetCompany($agent_array['id']);
		$current_folder = intval($arResult["SHOP"]['PROPERTY_FOLDER_VALUE']);
		
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
				CIBlockElement::Delete($_POST['delete_row_id']);
			}
		}
		
		if (isset($_POST['add']))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
				
				if (intval($_POST['product_id']) > 0)
				{
					$prod_id = intval($_POST['product_id']);
				}
				else
				{
					$el = new CIBlockElement;
					$PROP = array();
					if (strlen(trim($_POST['article'])))
					{
						$PROP[294] = htmlspecialcharsBack(trim($_POST['article']));
						$make_article = false;
					}
					else
					{
						$make_article = true;
						$db_props = CIBlockElement::GetProperty(40, $agent_array['id'], array("sort" => "asc"), array("ID" => 359));
						if ($ar_props = $db_props->Fetch())
						{
							$new_article = $ar_props["VALUE"].'_';
						}
					}
					$PROP[296] = 0;
					$PROP[297] = $_POST['price'];
					$PROP[295] = $agent_array['id'];
					$PROP[299] = 0;
					$arLoadProductArray = array(
						"MODIFIED_BY" => $u_id,
						"IBLOCK_SECTION_ID" => $current_folder,
						"IBLOCK_ID" => 62,
						"NAME" => htmlspecialcharsBack(trim($_POST['name'])),
						"PROPERTY_VALUES" => $PROP,
						"ACTIVE" => "Y"
					);
					$prod_id = $el->Add($arLoadProductArray);
					if ($make_article)
					{
						$new_article .= $prod_id;
						CIBlockElement::SetPropertyValuesEx($prod_id, 62, array(294 => $new_article));
					}
				}
				$el = new CIBlockElement;
				$PROP = array();
				$PROP[321] = $prod_id;
				$PROP[323] = htmlspecialcharsBack(trim($_POST['article']));
				$PROP[324] = $_POST['weight'];
				$PROP[326] = $_POST['price'];
				$PROP[320] = $_POST['nakl_id'];
				$PROP[325] = intval($_POST['count']);
				$PROP[351] = 153;
				$arLoadProductArray = array(
					"MODIFIED_BY" => $u_id,
					"IBLOCK_SECTION_ID" => false,
					"IBLOCK_ID" => 65,
					"NAME" => htmlspecialcharsBack($_POST['name']),
					"PROPERTY_VALUES" => $PROP,
					"ACTIVE" => "Y"
				);
				$zapis_id = $el->Add($arLoadProductArray);
			}
		}
		
		if (isset($_POST["reqv_spnend"]))
		{
			$arParamsSend = array(
				"SHOP_ID" => $agent_array['id'],
				"SHOP_NAME" => $agent_array['name'],
				"NAKL_ID" => $_POST['nakl_id'],
				"NAKL_NAME" => $_POST['nakl_name'],
				"DATE_SEND" => date('d.m.Y H:i'),
				"LINK_TO_MSG" => ''
			);
			$qw = SendMessageInSystem($u_id, $agent_array['id'], $agent_array["uk"], GetMessage("MAIL_PURCHASE_REQUEST"), 163, "", "", 169, $arParamsSend);
			$arParamsSend["LINK_TO_MSG"] = GetMessage("LINK_TO_MSG", array("#ID#" => $qw));
			SendMessageMailNew($agent_array["uk"], $agent_array['id'], 162, 169, $arParamsSend);
			$_SESSION["MESSAGE"][] = GetMessage("MES_PURCHASE_REQUEST_SEND", array("#ID#" => $_POST['nakl_id'], "#NUMBER#" => $_POST['nakl_name']));
			LocalRedirect("/goods/purchase.invoice.php?mode=list_purchase");
		}
		
		if (intval($_GET['id'] > 0))
		{
			$res = CIBlockElement::GetByID(intval($_GET['id']));
			if($ar_res = $res->GetNext())
			{
				$arResult['INFO'] = GetOnePurchase(intval($_GET['id']), $agent_array['id'], array('name' => 'asc'));
				if (($arResult['INFO']['PROPERTY_353_ENUM_ID'] == 157) || ($u_id != $arResult['INFO']['CREATED_BY']))
				{
					LocalRedirect('purchase.invoice.php?mode=purchase&id='.$arResult['INFO']['ID']);
				}
				else
				{
					$arResult['TITLE'] = GetMessage('TITLE_3', array('#ID#' => $arResult['INFO']['PROPERTY_327_VALUE']));
				}
			}
			else
			{
				$arResult['TITLE'] = GetMessage('TITLE_4');
			}
		}
		else
		{
			$arResult['TITLE'] = GetMessage('TITLE_4');
		}
	}
	
	/************список приходных накладных************/
	if ($mode == "list_purchase")
	{
		if (isset($_POST['delete']))
		{
			$arDelete = array();
			foreach ($_POST['ids'] as $key)
			{
				$info_purch = GetOnePurchase($key);
				foreach ($info_purch['GOODS'] as $g)
				{
					$arDelete[] = $g["ID"];
				}
				$arDelete[] = $key;
			}
			if (count($arDelete) > 0)
			{
				foreach ($arDelete as $d)
				{
					CIBlockElement::Delete($d);
				}
				$arResult["MESSAGE"][] = GetMessage("MES_PURCHASE_DELETE");
			}
		}
		$current_shop = intval($agent_array['id']);
		$arResult["SHOP"] = GetCompany($current_shop);
		$arResult["TITLE"] = GetMessage("MENU_1");
		$arResult["LIST"] = GetListPurchase($current_shop, false, false, 170);
		$arResult['NAV_STRING'] = $arResult["LIST"]['NAV_STRING'];
		unset($arResult["LIST"]['NAV_STRING']);
		$arResult['CURRENT_USER'] = $u_id;
	}
	
	/***************приходная накладная****************/
	if ($mode == "purchase")
	{
		$n_id = intval($_GET['id']);
		if ($n_id > 0) {
			$arResult["INFO"] = GetOnePurchase($n_id, $agent_array['id']);
			if ($arResult["INFO"]) {
				$arResult["TITLE"] = GetMessage("TITLE_3",array('#ID#'=>$arResult["INFO"]['PROPERTY_327_VALUE']));
			}
			else $arResult["TITLE"] = GetMessage("TITLE_4");
		}
		else $arResult["TITLE"] = GetMessage("TITLE_4");
	}
	
	/***************расходная накладная****************/
	if ($mode == "purchase_outgo")
	{
		$n_id = intval($_GET['id']);
		if ($n_id > 0) {
			$arResult["INFO"] = GetOnePurchase($n_id, $agent_array['id']);
			if ($arResult["INFO"]) {
				$arResult["TITLE"] = GetMessage("TITLE_3",array('#ID#'=>$arResult["INFO"]['PROPERTY_327_VALUE']));
			}
			else $arResult["TITLE"] = GetMessage("TITLE_4");
		}
		else $arResult["TITLE"] = GetMessage("TITLE_4");
	}
	
	/**************корректировка остатков**************/
	if ($mode == "correction")
	{
		if (intval($_GET['id']) > 0)
		{
			$arResult["INFO"] = GetOneCorrection(intval($_GET['id']));
			if ($arResult["INFO"])
			{
				$arResult["TITLE"] = GetMessage("TITLE_7",array('#ID#'=>$arResult["INFO"]['PROPERTY_355_VALUE']));
			}
			else
			{
				$arResult["TITLE"] = GetMessage("TITLE_6");
			}
		}
		else
		{
			$arResult["TITLE"] = GetMessage("TITLE_6");
		}
	}
	
	/****************корректировка цен*****************/
	if ($mode == "correction_price")
	{
		$arResult["INFO"] = GetOneCorrection($_GET['id']);
		$arResult["TITLE"] = GetMessage("CORRECTION_PRICE_NAME_ELEMENT", array("#NUMBER#" => $arResult["INFO"]["PROPERTY_355_VALUE"]));
	}
	
	/****************движение по товару****************/
	if (($mode == "accounting") || ($mode == "accounting_xls"))
	{
		$good_id = intval($_GET['good']);
		if ($good_id > 0)
		{
			$res = CIBlockElement::GetByID($good_id);
			if($ar_res = $res->GetNext())
			{
				$arResult["GOOD"] = $good_id;
				$arResult["TITLE"] = GetMessage("TITLE_ACCOUNTING", array("#NAME#" => $ar_res['NAME']));
				$arResult["TITLE_2"] = GetMessage("TITLE_ACCOUNTING", array("#NAME#" => str_replace('"','',$ar_res['NAME'])));
				$arResult['NAME_OF_GOOD'] = str_replace('"','',$ar_res['NAME']);
				$arResult['ART_OF_GOOD'] = false;
								$db_props = CIBlockElement::GetProperty(62, $good_id, array("sort" => "asc"), array("ID"=>294));
				if($ar_props = $db_props->Fetch())
				{
					$arResult['ART_OF_GOOD'] = $ar_props["VALUE"];
				}
				$arResult['IM_NAME_OF_GOOD'] = false;
				$db_props = CIBlockElement::GetProperty(62, $good_id, array("sort" => "asc"), array("ID"=>295));
				if($ar_props = $db_props->Fetch())
				{
					$arResult['IM_ID_OF_GOOD'] = $ar_props["VALUE"];
					$res_n = CIBlockElement::GetByID($arResult['IM_ID_OF_GOOD']);
					if ($ar_res_n = $res_n->GetNext())
					{
						$arResult['IM_NAME_OF_GOOD'] = $ar_res_n['NAME'];
					}
				}
				
				$arResult["SHOW_COUNTS"] = false;
				if ((strlen($_GET['date_from'])) && (strlen($_GET['date_to'])))
				{
					$arResult["SHOW_COUNTS"] = true;
					$res_0 = CIBlockElement::GetList(
						array("created"=>"asc"), 
						array("IBLOCK_ID"=>65,"PROPERTY_321"=>$good_id,"<DATE_CREATE"=>array($_GET['date_from'].' 00:00:00')), 
						false, 
						false, 
						array("DATE_CREATE","PROPERTY_325","PROPERTY_351","PROPERTY_320","PROPERTY_352",'PROPERTY_354','PROPERTY_368')
					);
					$arResult["COUNT_START"] = 0;
					while($ob_0 = $res_0->GetNextElement())
					{
						$a = $ob_0->GetFields();
						if ($a["PROPERTY_351_ENUM_ID"] == 153)
						{
							if (intval($a['PROPERTY_320_VALUE']) > 0)
							{
								$db_props = CIBlockElement::GetProperty(64, $a['PROPERTY_320_VALUE'], array("sort" => "asc"), array("ID"=>353));
								if($ar_props = $db_props->Fetch())
									$opr = $ar_props["VALUE"];
								if ($opr == 157)
								{
									$arResult["COUNT_START"] = $arResult["COUNT_START"] + $a['PROPERTY_325_VALUE'];
								}
							}
							elseif (intval($a['PROPERTY_354_VALUE']) > 0)
							{
								$db_props = CIBlockElement::GetProperty(69, $a['PROPERTY_354_VALUE'], array("sort" => "asc"), array("ID"=>358));
								if($ar_props = $db_props->Fetch())
									$opr = $ar_props["VALUE"];
								if ($opr == 158)
								{
									$arResult["COUNT_START"] = $arResult["COUNT_START"] + $a['PROPERTY_325_VALUE'];
								}
							}
							elseif (intval($a['PROPERTY_368_VALUE']) > 0)
							{
								$arResult["COUNT_START"] = $arResult["COUNT_START"] + $a['PROPERTY_325_VALUE'];
							}
							elseif (intval($a['PROPERTY_352_VALUE']) > 0)
							{
								$arResult["COUNT_START"] = $arResult["COUNT_START"] + $a['PROPERTY_325_VALUE'];
							}
						}
						else
						{
							$arResult["COUNT_START"] = $arResult["COUNT_START"] - $a['PROPERTY_325_VALUE'];
						}
					}
					$arResult["COUNT_END"] = $arResult["COUNT_START"];
					$filter = array("IBLOCK_ID"=>65,"PROPERTY_321"=>$good_id,"><DATE_CREATE"=>array($_GET['date_from'],$_GET['date_to'].' 23:59:59'));
					$res = CIBlockElement::GetList(
						array("created"=>"asc"),
						$filter,
						false,
						false,
						array("DATE_CREATE","PROPERTY_325","PROPERTY_351","PROPERTY_320","PROPERTY_352","PROPERTY_354","PROPERTY_368")
					);
					$arResult["LIST"] = array();
					while($ob = $res->GetNextElement())
					{
						$a = $ob->GetFields();
						if ($a["PROPERTY_351_ENUM_ID"] == 153)
						{
							if (intval($a["PROPERTY_320_VALUE"]) > 0)
							{
								$db_props = CIBlockElement::GetProperty(64, $a['PROPERTY_320_VALUE'], array("sort" => "asc"), array("ID"=>353));
								if($ar_props = $db_props->Fetch())
								{
									$opr = $ar_props["VALUE"];
								}
								if ($opr == 157)
								{
									$zasch = true;
									$prop_block = 64;
									$proop_id = 318;
									$el = $a['PROPERTY_320_VALUE'];
									$txt = GetMessage("NAME_ACCOUNTING_ELEMENT_1");
									$a['LINK'] = '/goods/purchase.invoice.php?mode=purchase&id='.$el;
									$arResult["COUNT_END"] = $arResult["COUNT_END"] + $a['PROPERTY_325_VALUE'];
								}
								else
								{
									$zasch = false;
								}
							}
							elseif (intval($a['PROPERTY_354_VALUE']) > 0)
							{
								$zasch = true;
								$prop_block = 69;
								$proop_id = 355;
								$el = $a['PROPERTY_354_VALUE'];
								$txt = GetMessage("NAME_ACCOUNTING_ELEMENT_2");
								$a['LINK'] = 'javascript:void(0);';
								$arResult["COUNT_END"] = $arResult["COUNT_END"] + $a['PROPERTY_325_VALUE'];
							}
							elseif (intval($a['PROPERTY_368_VALUE']) > 0)
							{
								$zasch = true;
								$prop_block = 71;
								$proop_id = 367;
								$el = $a['PROPERTY_368_VALUE'];
								$txt = GetMessage("NAME_ACCOUNTING_ELEMENT_3");
								$a['LINK'] = 'javascript:void(0);';
								$arResult["COUNT_END"] = $arResult["COUNT_END"] + $a['PROPERTY_325_VALUE'];
							}
							elseif (intval($a['PROPERTY_352_VALUE']) > 0)
							{
								$zasch = true;
								$prop_block = 42;
								$proop_id = 402;
								$el = $a['PROPERTY_352_VALUE'];
								$txt = GetMessage("NAME_ACCOUNTING_ELEMENT_4");
								$a['LINK'] = '/warehouse/index.php?mode=package&id='.$el;
								$arResult["COUNT_END"] = $arResult["COUNT_END"] + $a['PROPERTY_325_VALUE'];
							}
							else
							{
								$zasch = false;
							}
							
						}
						elseif ($a["PROPERTY_351_ENUM_ID"] == 154)
						{
							if (intval($a['PROPERTY_320_VALUE']) > 0)
							{
								$db_props = CIBlockElement::GetProperty(64, $a['PROPERTY_320_VALUE'], array("sort" => "asc"), Array("ID"=>353));
								if($ar_props = $db_props->Fetch())
								{
									$opr = $ar_props["VALUE"];
								}
								if ($opr == 157)
								{
									$zasch = true;
									$prop_block = 64;
									$proop_id = 318;
									$el = $a['PROPERTY_320_VALUE'];
									$txt = GetMessage("NAME_ACCOUNTING_ELEMENT_1");
									$a['LINK'] = '/goods/purchase.invoice.php?mode=purchase_outgo&id='.$el;
									$arResult["COUNT_END"] = $arResult["COUNT_END"] - $a['PROPERTY_325_VALUE'];
								}
								else
								{
									$zasch = false;
								}
							}
							elseif (intval($a['PROPERTY_352_VALUE']) > 0)
							{
								$zasch = true;
								$prop_block = 42;
								$proop_id = 402;
								$el = $a['PROPERTY_352_VALUE'];
								$txt = GetMessage("NAME_ACCOUNTING_ELEMENT_4");
								$a['LINK'] = '/warehouse/index.php?mode=package&id='.$el;
								$arResult["COUNT_END"] = $arResult["COUNT_END"] - $a['PROPERTY_325_VALUE'];
							}
							elseif (intval($a['PROPERTY_354_VALUE']) > 0)
							{
								$zasch = true;
								$prop_block = 69;
								$proop_id = 355;
								$el = $a['PROPERTY_354_VALUE'];
								$txt = GetMessage("NAME_ACCOUNTING_ELEMENT_2");
								$a['LINK'] = '#';
								$arResult["COUNT_END"] = $arResult["COUNT_END"] - $a['PROPERTY_325_VALUE'];
							}
							else
							{
								$zasch = false;
							}
							
						}
						else
						{
							$prop_block = $proop_id = $el = $zasch = false;
						}
						
						if ($zasch)
						{
							$db_props = CIBlockElement::GetProperty($prop_block, $el, array("sort" => "asc"), array("ID"=>$proop_id));
							if($ar_props = $db_props->Fetch())
							{
								$name = $ar_props["VALUE"];
							}
							else
							{
								$name = false;
							}
							$a["NAME_OPER"] = $txt.' №'.$name;
							$arResult["LIST"][] = $a;
						}
							
					}
					if (count($arResult["LIST"])  == 0)
					{
						$arResult["ERRORS"][] = GetMessage("ERR_ACCOUNTING_NO_OPERATIONS", array("#NAME#" => $ar_res['NAME']));
					}
				}
				else
				{
					$arResult["ERRORS"][] = GetMessage("ERR_ACCOUNTING_NO_PERIOD");
				}
			}
			else
			{
				$arResult["TITLE"] = GetMessage("ERR_ACCOUNTING_NO_GOOD");
			}
		}
	}
	
	/************печать приходной накладной************/
	if ($mode == "print_purchase")
	{
		$n_id = intval($_GET['id']);
		if ($n_id > 0)
		{
			$arResult["INFO"] = GetOnePurchase($n_id, $agent_array["id"]);
			$arResult["COMAPNY"] = GetCompany($agent_array["id"]);
			$arResult["UK"] = GetCompany($agent_array['uk']);
		}
		else $arResult["INFO"] = false;
	}
}

$this->IncludeComponentTemplate($componentPage);
?>