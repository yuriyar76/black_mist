<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

CModule::IncludeModule('iblock');
CModule::IncludeModule("currency");
$u_id = $USER->GetID();
$arResult["CURRENT_USER"] = $u_id;

$agent_array = GetCurrentAgent($u_id);
$arResult["CURRENT_COMPANY"] = $agent_array['id'];
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
		'list',
		'add',
		'pvz'
	);
	$arResult["MENU"] = array(
		'list' => GetMessage("UK_MENU_1")
	);
	$arResult["BUTTONS"] = array(
		"add" => array(
			"in_mode" => array("list"),
			"title" => GetMessage("NEW_PVZ_BTN"),
			"link" => "/pvz/index.php?mode=add"
		)
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
	
	/********************список пвз********************/	
	if ($mode == 'list')
	{
		if (isset($_POST['applay']))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
				foreach ($_POST['pvzs'] as $id)
				{
					if ($_POST['action'] == 1)
					{
						$el = new CIBlockElement;
						$res = $el->Update($id, array("MODIFIED_BY"=>$u_id,"ACTIVE"=>"Y"));
						$arResult['MESSAGE'][] = GetMessage("MESSAGE_2",array("#ID#"=>$_POST['id_in'][$id]));
					}
					if ($_POST['action'] == 2)
					{
						$el = new CIBlockElement;
						$res = $el->Update($id, array("MODIFIED_BY"=>$u_id,"ACTIVE"=>"N"));
						$arResult['MESSAGE'][] = GetMessage("MESSAGE_3",array("#ID#"=>$_POST['id_in'][$id]));
					}
					if ($_POST['action'] == 3)
					{
						if (CIBlockElement::Delete($id))
						{
							$arResult['MESSAGE'][] = GetMessage("MESSAGE_4",array("#ID#"=>$_POST['id_in'][$id]));
						}
					}
				}
			}
		}
		
		$arResult["LIST"] = TheListOfPVZ($agent_array['id'], intval($_GET['city']));
		$arResult['CITIES'] = TheListOfCitiesPVZ($agent_array['id']);
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"]);
	}
	
	/**************добавление нового пвз***************/
	if ($mode == 'add')
	{
		if (isset($_POST['add_pvz']))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
				if (strlen($_POST['name']) <= 2)
				{
					$arResult["ERRORS"][] = GetMessage("ERROR_1");
				}
				if (strlen($_POST['city']) <= 3)
				{
					$arResult["ERRORS"][] = GetMessage("ERROR_2");
				}
				else
				{
					$city_shop = GetCityId($_POST['city']);
					if ($city_shop <= 0)
					{
						$arResult["ERRORS"][] = GetMessage("ERROR_3");
					}
				}
				if (strlen($_POST['adress']) <= 3)
				{
					$arResult["ERRORS"][] =  GetMessage("ERROR_4");
				}
				$phones = array();
				foreach ($_POST['phone'] as $p)
				{
					if (strlen(trim($p)))
					{
						$phones[] = trim($p);
					}
				}
				if (count($arResult["ERRORS"]) == 0)
				{
					$max_id_3 = GetMaxIDIN(66, 3);
					$el = new CIBlockElement;
					$arLoadProductArray = Array(
						"MODIFIED_BY" => $u_id,
						"IBLOCK_ID" => 66,
						"NAME" => trim($_POST['name']),
						"CODE" => trim($_POST['code']),
						"PROPERTY_VALUES" => array(
							335 => $_POST['agent'],
							332 => $city_shop,
							333 => trim($_POST['adress']),
							334 => $phones,
							336 => $max_id_3
						)
					);
					$PRODUCT_ID = $el->Add($arLoadProductArray);
					$arResult['MESSAGE'][] = GetMessage("MESSAGE_1",array("#NAME#" => trim($_POST['name'])));
					$_POST = array();
				}
			}
		}
		$arResult["TITLE"] = GetMessage('UK_ADD_TITLE');
		$arResult['AGENTS'][$agent_array['id']] = GetMessage('OWN_PVZ');
		$arAgs = AvailableAgents(true, $agent_array['id']);
		foreach ($arAgs as $k => $v)
		{
			$arResult['AGENTS'][$k] = $v;
		}
	}
	
	/***********************пвз************************/
	if ($mode == 'pvz')
	{
		if (isset($_POST['save_pvz']))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
				$massive_to_cange = array();
				if (strlen($_POST['name']) <= 2)
				{
					$arResult["ERRORS"][] = GetMessage("ERROR_1");
					$edit_name = false;
				}
				else
				{
					$edit_name = true;
				}
				if (strlen($_POST['city']) <= 3)
				{
					$arResult["ERRORS"][] = GetMessage("ERROR_2");
				}
				else
				{
					$city_shop = GetCityId($_POST['city']);
					if ($city_shop <= 0)
					{
						$arResult["ERRORS"][] = GetMessage("ERROR_3");
					}
					else
					{
						$massive_to_cange[332] = $city_shop;
					}
				}
				if (strlen($_POST['adress']) <= 3)
				{
					$arResult["ERRORS"][] = GetMessage("ERROR_4");
				}
				else
				{
					$massive_to_cange[333] = htmlspecialchars(trim($_POST['adress']));
				}
				$phones = array();
				foreach ($_POST['phone'] as $p)
				{
					if (strlen(trim($p)))
					{
						$phones[] = $p;
					}
				}
				$massive_to_cange[334] = $phones;
				$massive_to_cange[335] = $_POST['agent'];
				$el = new CIBlockElement;
				$arLoadProductArray = array("MODIFIED_BY"=> $u_id, "ACTIVE" => $_POST["active"]);
				if ($edit_name)
				{
					$arLoadProductArray["NAME"] = $_POST['name'];
				}
				$res = $el->Update($_POST['pvz_id'], $arLoadProductArray);
				CIBlockElement::SetPropertyValuesEx($_POST['pvz_id'], false, $massive_to_cange);
				$arResult['MESSAGE'][] = GetMessage("MESSAGE_5",array("#ID#"=>$_POST['id_in']));
			}
		}
		
		$arResult['PVZ'] = false;
		$arPVZ = TheListOfPVZ($agent_array['id'], 0, true, intval($_GET['id']), false);
		if (count($arPVZ) == 1)
		{
			$arResult['PVZ'] = $arPVZ[0];
			$arResult["TITLE"] = GetMessage('TTL_2', array('#ID#' => $arResult['PVZ']['PROPERTY_ID_IN_VALUE']));
			$arResult['AGENTS'][$agent_array['id']] = GetMessage('OWN_PVZ');
			$arAgs = AvailableAgents(true, $agent_array['id']);
			foreach ($arAgs as $k => $v)
			{
				$arResult['AGENTS'][$k] = $v;
			}
		}
		else
		{
			$arResult['TITLE'] = GetMessage('TTL_3');
		}
	}
}

/**************************************************/
/**********************АГЕНТ***********************/
/**************************************************/

if ($agent_array['type'] == 53)
{
	$modes = array(
		'list',
		'add',
		'pvz'
	);
	$arResult["MENU"] = array(
		'list' => GetMessage("UK_MENU_1")
	);
	$arResult["BUTTONS"] = array(
		"add" => array(
			"in_mode" => array("list"),
			"title" => GetMessage("NEW_PVZ_BTN"),
			"link" => "/pvz/index.php?mode=add"
		)
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
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	
	/********************список пвз********************/
	if ($mode == 'list')
	{
		if (isset($_POST['applay']))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
				foreach ($_POST['pvzs'] as $id)
				{
					if ($_POST['action'] == 1)
					{
						$el = new CIBlockElement;
						$res = $el->Update($id, array("MODIFIED_BY"=>$u_id,"ACTIVE"=>"Y"));
						$arResult['MESSAGE'][] = GetMessage("MESSAGE_2",array("#ID#"=>$_POST['id_in'][$id]));
					}
					if ($_POST['action'] == 2)
					{
						$el = new CIBlockElement;
						$res = $el->Update($id, array("MODIFIED_BY"=>$u_id,"ACTIVE"=>"N"));
						$arResult['MESSAGE'][] = GetMessage("MESSAGE_3",array("#ID#"=>$_POST['id_in'][$id]));
					}
					if ($_POST['action'] == 3)
					{
						if (CIBlockElement::Delete($id))
						{
							$arResult['MESSAGE'][] = GetMessage("MESSAGE_4",array("#ID#"=>$_POST['id_in'][$id]));
						}
					}
				}
			}
		}
		
		$arResult["LIST"] = TheListOfPVZ($agent_array['id']);
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"]);
	}
	
	/**************добавление нового пвз***************/
	if ($mode == 'add')
	{
		if (isset($_POST['add_pvz']))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
				if (strlen($_POST['name']) <= 2)
				{
					$arResult["ERRORS"][] = GetMessage("ERROR_1");
				}
				if (strlen($_POST['city']) <= 3)
				{
					$arResult["ERRORS"][] = GetMessage("ERROR_2");
				}
				else
				{
					$city_shop = GetCityId($_POST['city']);
					if ($city_shop <= 0)
					{
						$arResult["ERRORS"][] = GetMessage("ERROR_3");
					}
				}
				if (strlen($_POST['adress']) <= 3)
				{
					$arResult["ERRORS"][] =  GetMessage("ERROR_4");
				}
				$phones = array();
				foreach ($_POST['phone'] as $p)
				{
					if (strlen(trim($p)))
					{
						$phones[] = trim($p);
					}
				}
				if (count($arResult["ERRORS"]) == 0)
				{
					$max_id_3 = GetMaxIDIN(66, 3);
					$el = new CIBlockElement;
					$arLoadProductArray = Array(
						"MODIFIED_BY" => $u_id,
						"IBLOCK_ID" => 66,
						"NAME" => trim($_POST['name']),
						"CODE" => trim($_POST['code']),
						"PROPERTY_VALUES" => array(
							335 => $agent_array['id'],
							332 => $city_shop,
							333 => trim($_POST['adress']),
							334 => $phones,
							336 => $max_id_3
						)
					);
					$PRODUCT_ID = $el->Add($arLoadProductArray);
					$arResult['MESSAGE'][] = GetMessage("MESSAGE_1",array("#NAME#" => trim($_POST['name'])));
					$_POST = array();
				}
			}
		}
		$arResult["TITLE"] = GetMessage('UK_ADD_TITLE');
	}
	
	/***********************ПВЗ*************************/
	if ($mode == 'pvz')
	{
		if (isset($_POST['save_pvz']))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
				$massive_to_cange = array();
				if (strlen($_POST['name']) <= 2)
				{
					$arResult["ERRORS"][] = GetMessage("ERROR_1");
					$edit_name = false;
				}
				else
				{
					$edit_name = true;
				}
				if (strlen($_POST['city']) <= 3)
				{
					$arResult["ERRORS"][] = GetMessage("ERROR_2");
				}
				else
				{
					$city_shop = GetCityId($_POST['city']);
					if ($city_shop <= 0)
					{
						$arResult["ERRORS"][] = GetMessage("ERROR_3");
					}
					else
					{
						$massive_to_cange[332] = $city_shop;
					}
				}
				if (strlen($_POST['adress']) <= 3)
				{
					$arResult["ERRORS"][] = GetMessage("ERROR_4");
				}
				else
				{
					$massive_to_cange[333] = htmlspecialchars(trim($_POST['adress']));
				}
				$phones = array();
				foreach ($_POST['phone'] as $p)
				{
					if (strlen(trim($p)))
					{
						$phones[] = $p;
					}
				}
				$massive_to_cange[334] = $phones;
				$el = new CIBlockElement;
				$arLoadProductArray = array("MODIFIED_BY"=> $u_id, "ACTIVE" => $_POST["active"]);
				if ($edit_name)
				{
					$arLoadProductArray["NAME"] = $_POST['name'];
				}
				$res = $el->Update($_POST['pvz_id'], $arLoadProductArray);
				CIBlockElement::SetPropertyValuesEx($_POST['pvz_id'], false, $massive_to_cange);
				$arResult['MESSAGE'][] = GetMessage("MESSAGE_5",array("#ID#" => $_POST['id_in']));
			}
		}
		
		$arResult['PVZ'] = false;
		$arPVZ = TheListOfPVZ($agent_array['id'], 0, true, intval($_GET['id']), false);
		if (count($arPVZ) == 1)
		{
			$arResult['PVZ'] = $arPVZ[0];
			$arResult["TITLE"] = GetMessage('TTL_2', array('#ID#' => $arResult['PVZ']['PROPERTY_ID_IN_VALUE']));
			$arResult["PACKS_IN_PVZ"] = TheListPacksOfPVZ($arResult['PVZ']['ID']);
			$arResult["NAV_STRING"] = $arResult["PACKS_IN_PVZ"]["NAV_STRING"];
			unset($arResult["PACKS_IN_PVZ"]["NAV_STRING"]);
			$arResult["PVZ_LIST"] = TheListOfPVZ($agent_array['id'], 0, false, 0, false, $arResult["PVZ"]["ID"]);	
		}
		else
		{
			$arResult['TITLE'] = GetMessage('TTL_3');
		}
	}
}
$this->IncludeComponentTemplate($componentPage);
?>