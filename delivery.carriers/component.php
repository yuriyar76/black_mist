<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

CModule::IncludeModule('iblock');
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

/*************************************************/
/************************УК***********************/
/*************************************************/

if ($agent_array['type'] == 51)
{
	$modes = array(
		'list',
		'carrier'
	);
	$arResult["MENU"] = array(
		'list' => GetMessage("TITLE_LIST")
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
	{
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));	
	}
	
	/***************Список перевозчиков****************/
	if ($mode == 'list')
	{
		if (isset($_POST['add_courier']))
		{
			if (strlen($_POST['fio']) <= 3)
			{
				$arResult["ERRORS"][] = GetMessage("ERR_NAME");
			}
			else
			{
				$max_id_3 = GetMaxIDIN(49, 3, false);
				
				$el = new CIBlockElement;
				$arLoadProductArray = array(
					"MODIFIED_BY" => $u_id, 
					"IBLOCK_SECTION_ID" => false,
					"IBLOCK_ID" => 49,
					"NAME" => $_POST['fio'],
					"ACTIVE" => "Y",
					"PROPERTY_VALUES"=> array(384 => $max_id_3, 500 => $agent_array['id'])
				);
				if ($PRODUCT_ID = $el->Add($arLoadProductArray))
					$arResult['MESSAGE'][] = GetMessage("MESS_ADD",array("#FIO#"=>$_POST['fio']));
				else
					$arResult['ERRORS'][] = $el->LAST_ERROR;
			}
		}
		
		if (isset($_POST['applay'])) {
			foreach ($_POST['carriers'] as $id) {
				if ($_POST['action'] == 1) {
					$el = new CIBlockElement;
					$res = $el->Update($id, array("MODIFIED_BY"=>$u_id,"ACTIVE"=>"Y"));
					$arResult['MESSAGE'][] = GetMessage("MESS_ACT",array("#ID#"=>$_POST['id_in'][$id]));
				}
				if ($_POST['action'] == 2) {
					$el = new CIBlockElement;
					$res = $el->Update($id, array("MODIFIED_BY"=>$u_id,"ACTIVE"=>"N"));
					$arResult['MESSAGE'][] = GetMessage("MESS_DEACT",array("#ID#"=>$_POST['id_in'][$id]));
				}
			}
		}
		
		$arResult["TITLE"] = GetMessage("TITLE_LIST");
		$arResult["LIST"] = TheListOfCarriers(0,false,true);
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"]);
	}
	
	/********************Перевозчик********************/
	if ($mode == 'carrier')
	{
		if (isset($_POST['save']))
		{
			$el = new CIBlockElement;
			$res = $el->Update($_POST['id'], array("MODIFIED_BY"=>$u_id,"ACTIVE"=>$_POST['active'],"NAME"=>trim($_POST['name']))); 
			$arResult['MESSAGE'][] = GetMessage("MESS_EDIT",array("#ID#"=>$_POST['id_in']));
		}
		
		$id_c = intval($_GET['id']);
		if ($id_c > 0)
		{
			$cc_arr = array();
			$arResult["CARRIER"] = false;
			$cc_arr = TheListOfCarriers($id_c,false);
			if (count($cc_arr) > 0) {
				$arResult["CARRIER"] = $cc_arr[$id_c];
				$arResult["TITLE"] = GetMessage("TITLE_CARRIER",array("#ID#"=>$arResult["CARRIER"]["PROPERTY_ID_IN_VALUE"]));
				$arResult["LISTOFMANIFEST"] = THeListOfManifests($id_c,0,true);
				$arResult["NAV_STRING"] = $arResult["LISTOFMANIFEST"]["NAV_STRING"];
				unset($arResult["LISTOFMANIFEST"]["NAV_STRING"]);
			}
			else $arResult["TITLE"] = GetMessage("CARRIER_NOT");
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
		'carrier'
	);
	$arResult["MENU"] = array(
		'list' => GetMessage("TITLE_LIST")
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
	{
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));	
	}
	
	if ($mode == 'list')
	{
		if (isset($_POST['add_courier']))
		{
			if (strlen($_POST['fio']) <= 3)
			{
				$arResult["ERRORS"][] = GetMessage("ERR_NAME");
			}
			else
			{
				$max_id_3 = GetMaxIDIN(49, 3, false);
				
				$el = new CIBlockElement;
				$arLoadProductArray = Array(
					"MODIFIED_BY" => $u_id, 
					"IBLOCK_SECTION_ID" => false,
					"IBLOCK_ID" => 49,
					"NAME" => $_POST['fio'],
					"ACTIVE" => "Y",
					"PROPERTY_VALUES"=> array(384 => $max_id_3, 500 => $agent_array['id'])
				);
				if ($PRODUCT_ID = $el->Add($arLoadProductArray))
					$arResult['MESSAGE'][] = GetMessage("MESS_ADD",array("#FIO#"=>$_POST['fio']));
				else
					$arResult['ERRORS'][] = $el->LAST_ERROR;
			}
		}
		
		if (isset($_POST['applay']))
		{
			foreach ($_POST['carriers'] as $id)
			{
				if ($_POST['action'] == 1)
				{
					$el = new CIBlockElement;
					$res = $el->Update($id, array("MODIFIED_BY"=>$u_id,"ACTIVE"=>"Y"));
					$arResult['MESSAGE'][] = GetMessage("MESS_ACT",array("#ID#"=>$_POST['id_in'][$id]));
				}
				if ($_POST['action'] == 2)
				{
					$el = new CIBlockElement;
					$res = $el->Update($id, array("MODIFIED_BY"=>$u_id,"ACTIVE"=>"N"));
					$arResult['MESSAGE'][] = GetMessage("MESS_DEACT",array("#ID#"=>$_POST['id_in'][$id]));
				}
			}
		}
		
		$arResult["TITLE"] = GetMessage("TITLE_LIST");
		$arResult["LIST"] = TheListOfCarriers(0, false, true, $agent_array['id']);
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"]);
	}
	
	if ($mode == 'carrier')
	{
		if (isset($_POST['save']))
		{
			$el = new CIBlockElement;
			$res = $el->Update($_POST['id'], array("MODIFIED_BY"=>$u_id,"ACTIVE"=>$_POST['active'],"NAME"=>trim($_POST['name']))); 
			$arResult['MESSAGE'][] = GetMessage("MESS_EDIT",array("#ID#"=>$_POST['id_in']));
		}
		
		$id_c = intval($_GET['id']);
		if ($id_c > 0)
		{
			$cc_arr = array();
			$arResult["CARRIER"] = false;
			$cc_arr = TheListOfCarriers($id_c,false, false, $agent_array['id']);
			if (count($cc_arr) > 0)
			{
				$arResult["CARRIER"] = $cc_arr[$id_c];
				$arResult["TITLE"] = GetMessage("TITLE_CARRIER",array("#ID#"=>$arResult["CARRIER"]["PROPERTY_ID_IN_VALUE"]));
				$arResult["LISTOFMANIFEST"] = THeListOfManifests($id_c,0,true);
				$arResult["NAV_STRING"] = $arResult["LISTOFMANIFEST"]["NAV_STRING"];
				unset($arResult["LISTOFMANIFEST"]["NAV_STRING"]);
			}
			else $arResult["TITLE"] = GetMessage("CARRIER_NOT");
		}
	}
}
$this->IncludeComponentTemplate($componentPage);
?>