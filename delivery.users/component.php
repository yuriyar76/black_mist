<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

CModule::IncludeModule('iblock');
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
		'list',
		'list_role',
		'role'
	);
	$arResult["MENU"] = array(
		'list' => GetMessage("TTL_1"),
		'list_role' => GetMessage("TTL_2")
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
		$arResult['MODE'] = $mode;
		$componentPage = "upr_".$mode;
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
	
	/***************список пользователей***************/
	if ($mode == 'list')
	{
		if (isset($_POST['save']))
		{
			$secs = 1;
			foreach ($_POST['role'] as $user => $role)
			{
				if ($role > 0)
				{
					$new_role = $role;
				}
				else
				{
					$new_role = false;
				}
				$uf = new CUser;
				if ($uf->Update($user, array("UF_ROLE"=>$new_role)))
					$secs = $secs*1;
				else
					$secs = $secs*0;

			}
			if ($secs == 1) $arResult["MESSAGE"][] = GetMessage("SAVE_YES");
			else $arResult["ERRORS"][] = GetMessage("SAVE_NO");
			
		}
		
		$arResult['USERS'] = $arResult['ROLE_LIST'] = array();
		$rsUser = CUser::GetList(($by="last_name"), ($order="asc"),array("GROUPS_ID" => array(15,16,17)), array("SELECT" => array("UF_COMPANY_RU_POST","UF_ROLE")));
		while($arUser = $rsUser->Fetch())
		{
			$arResult['USERS'][$arUser["UF_COMPANY_RU_POST"]][] = $arUser;
		}
		foreach ($arResult['USERS'] as $k => $v)
		{
			$arResult["COMPANIES"][$k] = GetCompany($k);
		}
		$res = CIBlockElement::GetList(array("NAME"=>"asc"), array("IBLOCK_ID"=>70,"ACTIVE"=>"Y"), false, false, array("ID","NAME","PROPERTY_363"));
		while($ob = $res->GetNextElement()) {
			$arFields = $ob->GetFields();
			foreach ($arFields["PROPERTY_363_VALUE"] as $k => $v)
			{
				if ($k == 159) $type = 51;
				if ($k == 160) $type = 53;
				if ($k == 161) $type = 52;
				$arResult['ROLE_LIST'][$type][$arFields["ID"]] = $arFields["NAME"];
			}
		}
	}
	
	/****************роли пользователей****************/
	if ($mode == 'list_role')
	{
		if (isset($_POST['add']))
		{
			if (!strlen($_POST['name'])) 
			{
				$arResult["ERRORS"][] = GetMessage("ERR_ADD_NAME");
			}
			else 
			{
				$el = new CIBlockElement;
				$role_id = $el->Add(Array(
					"MODIFIED_BY"    => $u_id,
					"IBLOCK_SECTION_ID" => false,
					"IBLOCK_ID"      => 70,
					"NAME"           => trim($_POST['name']),
					"ACTIVE"         => "Y"
				));
				$arResult["MESSAGE"][] = GetMessage("ERR_ADD_SEC",array("#NAME#"=>trim($_POST['name'])));
			}
		}
		
		if (isset($_POST['save'])) 
		{
			foreach ($_POST['role'] as $k => $v)
			{
				CIBlockElement::SetPropertyValuesEx($k, false, array(363 => $v));
			}
			$arResult["MESSAGE"][] = GetMessage("SAVE_YES");
		}
		
		if(isset($_POST['delete']))
		{
			if (count($_POST['roles'] == 1)) $r = "DELETE_YES_ONE";
			if (count($_POST['roles'] > 1)) $r = "DELETE_YES_MANY";
			foreach ($_POST['roles'] as $z)
			{
				CIBlockElement::Delete($z);
			}
			$arResult["MESSAGE"][] = GetMessage($r);
		}
		
		$arResult["TITLE"] = GetMessage("TTL_2");
		$arResult['ROLE_LIST'] = GetListRoles();
	}
	
	/***********************роль***********************/
	if ($mode == 'role')
	{
		if (isset($_POST['save']))
		{
			$array_to_change = array();
			foreach ($_POST['role'] as $k => $v)
			{
				$array_to_change['FOR_'.$k] = $v;
			}
			CIBlockElement::SetPropertyValuesEx($_POST['role_id'], 70, $array_to_change);
			$arResult["MESSAGE"][] = GetMessage("SAVE_YES");
		}
		
		$arResult["ROLE"] = GetListRoles(intval($_GET["id"]));
		if ($arResult["ROLE"])
		{	
			$arResult["ROLE"] = $arResult["ROLE"][intval($_GET["id"])];
			$arResult["ROLE"]["ID"] = intval($_GET["id"]);
			$arResult["TITLE"] = GetMessage("TTL_3",array("#NAME#"=>$arResult["ROLE"]['NAME']));
			$arResult["ALL_MENUS"] = $arResult["MENUS"] = array();
			$files = array(
				159 => $_SERVER['DOCUMENT_ROOT'].'/.left.menu.php',
				160 => $_SERVER['DOCUMENT_ROOT'].'/.left_agents.menu.php',
				161 => $_SERVER['DOCUMENT_ROOT'].'/.left_shops.menu.php'
			);
			foreach ($arResult["ROLE"]["FOR"] as $k => $v)
			{
				include $files[$k];
				foreach ($aMenuLinks as $p)
				{
					if ($p[1][0] == '/')
					{
						$link = substr($p[1],1);
					}
					$link = strstr($link, '/', true);
					$arResult["MENUS"][$k][$link] = $p[0];
					if (!array_key_exists($link))
					{
						$arResult["ALL_MENUS"][$link] = $p[0];
					}
				}
				unset($aMenuLinks);
			}
		}
		else {
			$arResult["TITLE"] = GetMessage("TTL_4");
		}
	}
}

$this->IncludeComponentTemplate($componentPage);