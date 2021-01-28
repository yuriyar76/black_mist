<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

CModule::IncludeModule('iblock');
CModule::IncludeModule("currency");
$u_id = $USER->GetID();
$agent_array = GetCurrentAgent($u_id);
$agent_id = $agent_array['id'];
$componentPage = "blank";

if (isset($arParams['PERM'][$agent_array['type']]['ALL']))
{
	$arResult["PERM"] = $arParams['PERM'][$agent_array['type']]['ALL'];
}

if ($arResult["PERM"] == "C")
{
	$APPLICATION->AuthForm(GetMessage("AUTH_NOT"));
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
		'agent',
		'manifestos',
		'prices',
		'pvz',
		'pvz_list',
		'add'
	);
	$arResult["MENU"] = array(
		'agent' => GetMessage("MENU_1"),
		'manifestos' => GetMessage("MENU_2"),
		'prices' => GetMessage("MENU_3"),
		'pvz_list' => GetMessage("MENU_4")
	);
	$arResult["MENU_TOP"] = array(
		'list' => GetMessage("MENU_TOP_1")
	);
	$arResult["BUTTONS"] = array(
		'add' => array(
			'in_mode' => array("list"),
			'title' => GetMessage("ADD_AGENT_BTN"),
			'link' => "/agents/index.php?mode=add"
		)
	);
	/**************убираем закрытое меню***************/
	foreach ($arResult["MENU"] as $m => $name)
	{
		if ($arParams['PERM'][$agent_array['type']][$m][$arResult['ROLE_USER']] == "C")
			unset($arResult["MENU"][$m]);
	}
	foreach ($arResult["MENU_TOP"] as $m => $name)
	{
		if ($arParams['PERM'][$agent_array['type']][$m][$arResult['ROLE_USER']] == "C")
			unset($arResult["MENU_TOP"][$m]);
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
			foreach ($arResult["MENU_TOP"] as $k => $name)
			{
				$mode = $k;
				break;
			}
		}
	}
	if (strlen($mode))
	{
		$componentPage = "upr_".$mode;
		$arResult['MODE'] = $mode;
	}
	else
	{
		$APPLICATION->AuthForm(GetMessage("AUTH_NOT"));
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
		$APPLICATION->AuthForm(GetMessage("AUTH_NOT"));
	
	/**********************агент***********************/
	if ($arParams['MODE'] == 'agent')
	{
		if (isset($_POST['save_agent']))
		{
			if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
			{
				$_POST = array();
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				if (strlen($_POST['name']) <= 2)
				{
					$arResult["ERRORS"][] = GetMessage("ERROR_EDIT_01");
				}
				if (strlen($_POST['city']) <= 3)
				{
					$arResult["ERRORS"][] = GetMessage("ERROR_EDIT_02");
				}
				else
				{
					$city_shop = GetCityId($_POST['city']);
					if ($city_shop <= 0)
					{
						$arResult["ERRORS"][] = GetMessage("ERROR_EDIT_03");
					}
				}	
                /*
				if (strlen(trim($_POST['inn'])))
				{
					if (!is_valid_inn($_POST['inn']))
					{
						$arResult["ERRORS"][] = GetMessage("ERROR_EDIT_05");
					}
				}
                */
				if (strlen(trim($_POST['email'])))
				{
					if(!preg_match("|^[-0-9a-z_\.]+@[-0-9a-z_^\.]+\.[a-z]{2,6}$|i", trim($_POST['email']))) 
					{
						$arResult["ERRORS"][] = GetMessage("ERROR_EDIT_08");
					}
				}
				$create_user = false;
				if ($_POST['new_user'] == 1)
				{
					$UserLogin = trim($_POST['new_login']);
					if ((!strlen($UserLogin)) || (!strlen(trim($_POST['new_email']))))
					{
						$arResult["ERRORS"][] = 'Не указан логин или e-mail нового пользователя';
					}
					else
					{	
						$user_yet = false;
						$rsUsers = CUser::GetList(($by="id"), ($order="desc"), array("EMAIL"=>$_POST['new_email']));
						if ($arUser = $rsUsers->Fetch())
						{
							$user_yet = true;
							$arResult["ERRORS"][] = 'Пользователь с таким email уже существует';
						}
						$rsUser_2 = CUser::GetByLogin($UserLogin);
						if($arUser_2 = $rsUser_2->Fetch())
						{
							$user_yet = true;
							$arResult["ERRORS"][] = 'Пользователь с таким логином уже существует';
						}
						$create_user = ($user_yet) ? false : true;
					}
				}
				
				$additional_addresses = array();
				if (isset($_POST['additional_addresses']))
				{
					foreach ($_POST['additional_addresses'] as $k => $v)
					{
						foreach ($v as $kk => $vv)
						{
							if ($kk == 'city')
							{
								if (strlen($vv))
								{
									$additional_addresses[$k][$kk] = GetCityId($vv);
									$additional_addresses[$k]["city_name"] =  iconv('windows-1251','utf8', $vv);
								}
								else
								{
									$additional_addresses[$k][$kk] = '';
									$additional_addresses[$k]["city_name"] = '';
								}
								
							}
							else
							{
								$additional_addresses[$k][$kk] = iconv('windows-1251','utf8', $vv);
							}
						}
					}
				}
				$additional_addresses_string = json_encode($additional_addresses);
					
				if (count($arResult["ERRORS"]) == 0)
				{
					$uu = false;
					$props = array(
						187 => $city_shop,
						190 => NewQuotes($_POST['adress']),
						227 => floatval(str_replace(',','.',$_POST['persent'])),
						237 => $_POST['inn'],
						729 => $_POST['INN_REAL'],
						290 => $_POST['cite'],
						243 => $_POST['email'],
						265 => $_POST['phones'],
						329 => NewQuotes($_POST['LEGAL_NAME']),
						328 => NewQuotes($_POST['CONTRACT']),
						377 => $_POST['prefix'],
						379 => NewQuotes($_POST['contact']),
						473 => trim($_POST['REPORT_SIGNS']),
						610 => $_POST['branch'],
						624 => NewQuotes($_POST['brand_name']),
						625 => NewQuotes($_POST['adress_fact']),
						378 => NewQuotes($_POST['LEGAL_NAME_FULL']),
						670 => $_POST['type_agent'],
						684 => $additional_addresses_string
					);
					if (intval($_POST['COEFFICIENT_VW']) > 0)
					{
						$props[681] = intval($_POST['COEFFICIENT_VW']);
					}
					
					if ($create_user)
					{
						$chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
						$max = 10;
						$size = StrLen($chars)-1;
						$pass = '';
						while($max--)
						{
							$pass .= $chars[rand(0,$size)]; 
						}
						$user = new CUser;
						$arFields = array(
							"LAST_NAME" => trim($_POST['new_lastname']),
							'NAME' => trim($_POST['new_name']),
							'SECOND_NAME' => trim($_POST['new_surname']),
							"EMAIL" => $_POST['new_email'],
							"LOGIN" => $UserLogin,
							"ACTIVE" => "Y",
							"GROUP_ID" => array(3,15),
							"PASSWORD" => $pass,
							"CONFIRM_PASSWORD" => $pass,
							"LID" => "s5",
							'UF_COMPANY_RU_POST' => $_POST['id'], 
							"UF_ROLE" => 4937477
						);
						$uu = $user->Add($arFields);
						if (intval($uu) > 0)
						{
							CEvent::SendImmediate(
								"DMS_NEW_AGENT", 
								"s5", 
								array(
									"LOGIN" => $UserLogin,
									"PASS" => $pass,
									"MAIL" => $_POST['new_email'],
									"COMPANY" => trim($_POST['name']),
									"NAME" => trim($_POST['new_name']),
									"CITY" => $_POST['city'],
									"ADRESS" => trim($_POST['adress']),
									"INN" => $_POST['inn'],
									"PHONE" => trim($_POST['phones']),
								),
								"N",
								157
							);
							$VALUES = array();
							$res = CIBlockElement::GetProperty(40, $_POST['id'], "sort", "asc", array("CODE" => "USER"));
							while ($ob = $res->GetNext())
							{
								$VALUES[] = $ob['VALUE'];
							}
							$VALUES[] = $uu;
							$props[186] = $VALUES;
							$arResult['MESSAGE'][]  = GetMessage("MESSAGE_ADD_01", array("#LOGIN#" => $_POST['login']));
						}
						else
						{
							$arResult["ERRORS"][] = $user->LAST_ERROR;
						}
					}
					CIBlockElement::SetPropertyValuesEx($_POST['id'], false, $props);
					$el = new CIBlockElement;
					$res = $el->Update($_POST['id'],array("MODIFIED_BY"=>$u_id,"ACTIVE"=>$_POST['active'],"NAME"=>NewQuotes($_POST['name'])));
					$arResult['MESSAGE'][] = 'Агент "'.$_POST['name'].'" успешно изменен';
				}
			}
		}
		$id_c = intval($_GET['id']);
		if ($id_c > 0)
		{
			$cc_arr = array();
			$arResult["AGENT"] = false;
			$cc_arr = TheListOfAgents($id_c,  false, false, $agent_array['id']);
			if (count($cc_arr) > 0)
			{	
				$arResult["AGENT"] = $cc_arr[0];
				if (strlen($arResult["AGENT"]["PROPERTY_ADDITIONAL_ADDRESSES_VALUE"]))
				{
					$add_adr = json_decode(htmlspecialcharsBack($arResult["AGENT"]["PROPERTY_ADDITIONAL_ADDRESSES_VALUE"]), true);
					foreach ($add_adr as $k => $v)
					{
						foreach ($v as $kk => $vv)
						{
							$arResult["AGENT"]["ADDITIONAL_ADDRESSES"][$k][$kk] = iconv('utf8','windows-1251',$vv);
						}
					}
				}
				$arResult["AGENT"]['USERS'] = array();
				$rsUser = CUser::GetList(($by="last_name"), ($order="asc"),array("GROUPS_ID" => array(15), "UF_COMPANY_RU_POST" => $id_c), array("SELECT" => array("UF_COMPANY_RU_POST","UF_ROLE")));
				while($arUser = $rsUser->Fetch())
				{
					$a = $arUser;
					$r = GetListRoles($a['UF_ROLE']);
					$a['ROLE'] = $r[$a['UF_ROLE']];
					$arResult["AGENT"]['USERS'][] = $a;
					
				}
				
				$arResult["TITLE"] = 'Агент &laquo;'.$arResult["AGENT"]["NAME"].'&raquo;';		
				$arResult["NAV_STRING"] = $arResult["PACKAGES"]["NAV_STRING"];
				unset($arResult["PACKAGES"]["NAV_STRING"]);
				if ($arResult["PERM"] == "E")
					$arResult["EDIT"] = true;
				else
					$arResult["EDIT"] = false;
				
			}
			else
			{
				$arResult["TITLE"] = 'Агент не найден';
			}
		}
		else
		{
			$arResult["TITLE"] = 'Агент не найден';
		}
		$APPLICATION->SetTitle($arResult["TITLE"]);
	}
	
	/*****************манифесты агенту*****************/
	if ($mode == 'manifestos')
	{
		$id_c = intval($_GET['id']);
		if ($id_c > 0)
		{
			$cc_arr = array();
			$arResult["AGENT"] = false;
			$cc_arr = TheListOfAgents($id_c, false, false, $agent_array['id']);
			if (count($cc_arr) > 0)
			{
				$arResult["AGENT"] = $cc_arr[0];
				$arResult["TITLE"] = 'Манифесты агенту "'.$arResult["AGENT"]["NAME"].'"';
				$arResult["PACKAGES"] = ManifestsToAgent($id_c);
				$arResult["NAV_STRING"] = $arResult["PACKAGES"]["NAV_STRING"];
				unset($arResult["PACKAGES"]["NAV_STRING"]);
			}
			else
			{
				$arResult["TITLE"] = 'Агент №'.$id_c.' не найден';
			}
		}
	}
	
	/****************прайс-листы агенту****************/
	if ($mode == 'prices') {
		$id_c = intval($_GET['id']);
		if ($id_c > 0) {
			$cc_arr = array();
			$arResult["AGENT"] = false;
			$cc_arr = TheListOfAgents($id_c, false, false, $agent_array['id']);
			if (count($cc_arr) > 0) {
				$arResult["AGENT"] = $cc_arr[0];
				$arResult["TITLE"] = 'Прайс-листы агента "'.$arResult["AGENT"]["NAME"].'"';
			}
			else $arResult["TITLE"] = 'Агент №'.$id_c.' не найден';
		}
	}
	
	/********************пвз агента********************/
	if ($mode == "pvz")
	{
		$pvz_id = intval($_GET['id']);
		if ($pvz_id > 0)
		{
			if (isset($_POST['save_pvz']))
			{
				$massive_to_cange = array();
				if (strlen($_POST['name']) <= 2)
				{
					$arResult["ERRORS"][] = 'Наименование должно быть более двух символов';
					$edit_name = false;
				}
				else
				{
					$edit_name = true;
				}
				if (strlen($_POST['city']) <= 3)
				{
					$arResult["ERRORS"][] = 'Город должен быть более трех символов';
				}
				else
				{
					$city_shop = GetCityId($_POST['city']);
					if ($city_shop <= 0)
					{
						$arResult["ERRORS"][] = 'Город не найден';
					}
					else
					{
						$massive_to_cange[332] = $city_shop;
					}
				}
				if (strlen($_POST['adress']) <= 3)
				{
					$arResult["ERRORS"][] = 'Адрес должен быть более трех символов';
				}
				else
				{
					$massive_to_cange[333] = $_POST['adress'];
				}
				$phones = array();
				foreach ($_POST['phone'] as $p)
				{
					if (strlen(trim($p)))
						$phones[] = $p;
				}
				if (count($phones) > 0)
				{
					$massive_to_cange[334] = $phones;
				}
				if ((count($massive_to_cange) > 0) || ($edit_name))
				{
					$el = new CIBlockElement;
					$arLoadProductArray = array("MODIFIED_BY"=> $u_id,"ACTIVE" => $_POST["active"]);
					if ($edit_name)
						$arLoadProductArray["NAME"] = $_POST['name'];
					if (count($massive_to_cange) > 0)
					{
						CIBlockElement::SetPropertyValuesEx($_POST['pvz_id'], false, $massive_to_cange);
					}
					$res = $el->Update($_POST['pvz_id'], $arLoadProductArray);
					$arResult['MESSAGE'][] = 'ПВЗ №'.$_POST['id_in'].' успешно изменен';
				}	
			}
			$list_pvz = TheListOfPVZ(0,0,true,$pvz_id);
			unset($list_pvz["NAV_STRING"]);
			if(count($list_pvz) == 1)
			{
				$arResult["PVZ"] = $list_pvz[0];
				$arResult["TITLE"] = 'ПВЗ №'.$arResult["PVZ"]["PROPERTY_ID_IN_VALUE"];
				$arResult["AGENT_ID"] = $arResult["PVZ"]["PROPERTY_AGENT_VALUE"];
			}
			else
			{
				$arResult["TITLE"] = 'ПВЗ № не найден';
				$arResult["PVZ"] = false;
				$arResult["AGENT_ID"] = false;
			}
		}
		else
		{
			LocalRedirect('/agents/');
		}
	}
		
	/****************список пвз агента*****************/
	if ($mode == "pvz_list")
	{
		if (isset($_POST['add_pvz']))
		{
			if (strlen($_POST['name']) <= 2)
				$arResult["ERRORS"][] = 'Наименование должно быть более двух символов';
			if (strlen($_POST['city']) <= 3)
			{
				$arResult["ERRORS"][] = 'Город должен быть более трех символов';
			}
			else
			{
				$city_shop = GetCityId($_POST['city']);
				if ($city_shop <= 0)
					$arResult["ERRORS"][] = 'Город не найден';
			}
			if (strlen($_POST['adress']) <= 3)
				$arResult["ERRORS"][] = 'Адрес должен быть более трех символов';
			$phones = array();
			foreach ($_POST['phone'] as $p)
			{
				if (strlen(trim($p))) $phones[] = $p;
			}
				
			if (count($arResult["ERRORS"]) == 0)
			{
				$res = CIBlockElement::GetList(array("ID"=>"desc"), array("IBLOCK_ID"=>66), false, array("nTopCount"=>1), array("ID", "PROPERTY_ID_IN"));
				if($ob = $res->GetNextElement())
				{
					$arFields = $ob->GetFields();
					$max_id = intval($arFields["PROPERTY_ID_IN_VALUE"]);
				}
				$max_id++;
				$max_id_3 = str_pad($max_id,3,'0',STR_PAD_LEFT);
				
				$el = new CIBlockElement;
				$arLoadProductArray = Array(
					"MODIFIED_BY"    => $u_id,
					"IBLOCK_ID"      => 66,
					"NAME" => $_POST['name'],
					"CODE" => $_POST['code'],
					"PROPERTY_VALUES"=> array(335=>$_POST['agent_id'],332=>$city_shop,333=>$_POST['adress'],334=>$phones,336=>$max_id_3));
				$PRODUCT_ID = $el->Add($arLoadProductArray);
				$arResult['MESSAGE'][] = 'ПВЗ "'.$_POST['name'].'" успешно добавлен в систему';
			}
		}
		
		if (isset($_POST['applay']))
		{
			foreach ($_POST['pvzs'] as $id)
			{
				if ($_POST['action'] == 1)
				{
					$el = new CIBlockElement;
					$res = $el->Update($id, array("MODIFIED_BY"=>$u_id,"ACTIVE"=>"Y"));
					$arResult['MESSAGE'][] = 'ПВЗ №'.$_POST['id_in'][$id].' успешно активирован';
				}
				if ($_POST['action'] == 2)
				{
					$el = new CIBlockElement;
					$res = $el->Update($id, array("MODIFIED_BY"=>$u_id,"ACTIVE"=>"N"));
					$arResult['MESSAGE'][] = 'ПВЗ №'.$_POST['id_in'][$id].' успешно деактивирован';
				}
				if ($_POST['action'] == 3)
				{
					if (CIBlockElement::Delete($id))
						$arResult['MESSAGE'][] = 'ПВЗ №'.$_POST['id_in'][$id].' успешно удален';
				}
			}
		}
		
		$id_c = intval($_GET['id']);
		if ($id_c > 0)
		{
			$cc_arr = array();
			$arResult["AGENT"] = false;
			$cc_arr = TheListOfAgents($id_c, false, false, $agent_array['id']);
			if (count($cc_arr) > 0)
			{
				$arResult["AGENT"] = $cc_arr[0];
				$arResult["TITLE"] = 'ПВЗ агента "'.$arResult["AGENT"]["NAME"].'"';
				$arResult["LIST"] = TheListOfPVZ($id_c);
				$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
				unset($arResult["LIST"]["NAV_STRING"]);
			}
			else
			{
				$arResult["TITLE"] = 'Агент не найден';
			}
		}
	}
	
	/****************добавление агента*****************/
	if ($mode == 'add')
	{
		if (isset($_POST['add_agent']))
		{
			if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
			{
				$_POST = array();
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				if (strlen($_POST['fio']) <= 2)
				{
					$arResult["ERRORS"][] = GetMessage("ERROR_ADD_01");
				}
				if (strlen($_POST['city']) <= 3)
				{
					$arResult["ERRORS"][] = GetMessage("ERROR_ADD_02");
				}
				else
				{
					$city_shop = GetCityId($_POST['city']);
					if ($city_shop <= 0)
						$arResult["ERRORS"][] = GetMessage("ERROR_ADD_03");
				}
				/*
				if (strlen($_POST['adress']) <= 3)
				{
					$arResult["ERRORS"][] = GetMessage("ERROR_ADD_04");
				}
				if (!is_valid_inn($_POST['inn']))
				{
					$arResult["ERRORS"][] = GetMessage("ERROR_ADD_05");
				}
				if (strlen($_POST['contact']) <= 2)
				{
					$arResult["ERRORS"][] = GetMessage("ERROR_ADD_06");
				}
				if (strlen($_POST['email']) <= 3)
				{
					$arResult["ERRORS"][] = GetMessage("ERROR_ADD_07");
				}
				else
				{
					if(!preg_match("|^[-0-9a-z_\.]+@[-0-9a-z_^\.]+\.[a-z]{2,6}$|i", $_POST['email'])) 
						$arResult["ERRORS"][] = GetMessage("ERROR_ADD_08");
				}
				if (strlen($_POST['phones']) <= 3)
				{
					$arResult["ERRORS"][] = GetMessage("ERROR_ADD_09");
				}
				*/
				/*
				if (floatval(str_replace(',','.',$_POST['persent'])) <= 0)
				{
					$arResult["ERRORS"][] = GetMessage("ERROR_ADD_10");
				}
				*/
				/*
				if ($_POST['user_company'] == 0)
				{
					if (strlen($_POST['login']) <= 3)
						$arResult["ERRORS"][] = GetMessage("ERROR_ADD_11");
				}
				*/
					
				if (count($arResult["ERRORS"]) == 0)
				{
					if (($_POST['user_company'] == 0) && (strlen($_POST['new_login'])))
					{
						$user_not = true;
						$user_login_yet = $user_email_yet = false;
						$send_mail_to_user_yet = false;
						$user_uu_1 = $user_uu_2 = 0;
						$UserLogin = trim($_POST['new_login']);
						$rsUsers = CUser::GetList(($by="id"), ($order="desc"), array("EMAIL"=>$_POST['new_email']));
						if ($arUser = $rsUsers->Fetch())
						{
							$user_email_yet = true;
							$user_uu_1 = $arUser["ID"];
						}
						$rsUser_2 = CUser::GetByLogin($UserLogin);
						if ($arUser_2 = $rsUser_2->Fetch())
						{
							$user_login_yet = true;
							$user_uu_2 = $arUser_2["ID"];	
						}
						if (($user_uu_1 == $user_uu_2)  && $user_email_yet && $user_login_yet)
						{
							$user_not = false;
							$send_mail_to_user_yet = true;
							$arResult["WARNINGS"][] = GetMessage("WARNING_ADD_01", array("#USER_LOGIN#" => $UserLogin, "#EMAIL#" => $_POST['new_email'], "#USER_ID#" => $user_uu_1));
							$uu = $user_uu_1;
							$arGroups = CUser::GetUserGroup($user_uu_1);
							$change_uer = false;
							if (!in_array(3,$arGroups))
							{
								$arGroups[] = 3;
								$change_uer = true;
							}
							if (!in_array(15,$arGroups))
							{
								$arGroups[] = 15;
								$change_uer = true;
							}
							if ($change_uer)
							{
								CUser::SetUserGroup($uu, $arGroups);
								$arResult["WARNINGS"][] = GetMessage("WARNING_ADD_02");
							}
						}
						else
						{
							if ($user_email_yet)
							{
								$arResult["ERRORS"][] = GetMessage("ERROR_ADD_12", array("#USER_LOGIN#" => $arUser["LOGIN"], "#EMAIL#" => $_POST['new_email'], "#USER_ID#" => $arUser["ID"]));
								$user_not = false;
							}
							if ($user_login_yet)
							{
								$arResult["ERRORS"][] = GetMessage("ERROR_ADD_13", array("#USER_LOGIN#" => $UserLogin, "#EMAIL#" => $arUser_2["EMAIL"], "#USER_ID#" => $arUser_2["ID"]));
								$user_not = false;
							}
						}
						if ($user_not)
						{
							if (!strlen($_POST['new_pass']))
							{
								$chars="qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
								$max=10;
								$size=StrLen($chars)-1;
								$pass = '';
								while($max--)
									$pass .= $chars[rand(0,$size)]; 
							}
							else
							{
								$pass = $_POST['new_pass'];
							}
							$user = new CUser;
							$arFields = array(
								"LOGIN"=> $UserLogin,
								"NAME"=>trim($_POST['new_name']),
								"LAST_NAME" => trim($_POST['new_lastname']),
								"SECOND_NAME" => trim($_POST['new_surname']),
								"EMAIL"=> trim($_POST['new_email']),
								"ACTIVE"=> "Y",
								"GROUP_ID"=> array(3,15),
								"PASSWORD"=> $pass,
								"CONFIRM_PASSWORD" => $pass,
								"LID" => "s5"
							);
							$uu = $user->Add($arFields);
							if (intval($uu) > 0)
							{
								CEvent::SendImmediate(
									"DMS_NEW_AGENT", 
									"s5", 
									array(
										"LOGIN" => $UserLogin,
										"PASS" => $pass,
										"MAIL" => $_POST['email'],
										"COMPANY" => NewQuotes($_POST['fio']),
										"NAME" => trim($_POST['new_name']),
										"CITY" => $_POST['city'],
										"ADRESS" => NewQuotes($_POST['adress']),
										"INN" => $_POST['inn'],
										"PHONE" => trim($_POST['phones']),
										"CONTACT_NAME" => NewQuotes($_POST['contact'])
									),
									"N",
									157
								);
								$arResult['MESSAGE'][]  = GetMessage("MESSAGE_ADD_01", array("#LOGIN#" => $_POST['login']));
							}
							else
							{
								$arResult["ERRORS"][] = $user->LAST_ERROR;
							}
						}
					}
					elseif ((!strlen($_POST['login'])) && (intval($_POST['user_company']) > 0))
					{
						$uu = $_POST['user_company'];
						$send_mail_to_user_yet = true;
					}
					else
					{
						$uu = false;
						$send_mail_to_user_yet = false;
					}
					
					if (count($arResult["ERRORS"]) == 0)
					{
						if ($send_mail_to_user_yet)
						{
							CEvent::SendImmediate(
								"DMS_NEW_AGENT",
								"s5", 
								array(
									"LOGIN" => $UserLogin,
									"PASS"=> GetMessage("MESS_ADD_01"),
									"MAIL" => $_POST['email'],
									"COMPANY" => $_POST['fio'],
									"NAME" => $_POST['contact'],
									"CITY" => $_POST['city'],
									"ADRESS" => trim($_POST['adress']),
									"INN" => $_POST['inn'],
									"PHONE" => trim($_POST['phones'])
								),
								"N",
								157
							);
						}
						
						$max_id_3 = GetMaxIDIN(40, 3, false);
						
						$el = new CIBlockElement;
						$arLoadProductArray = array(
							"MODIFIED_BY" => $u_id, 
							"IBLOCK_SECTION_ID" => false,
							"IBLOCK_ID" => 40,
							"NAME" => NewQuotes($_POST['fio']),
							"ACTIVE" => $_POST['active'],
							"PROPERTY_VALUES" => array(
								187 => $city_shop, 
								211 => 53, 
								190 => NewQuotes($_POST['adress']),
								265 => trim($_POST['phones']),
								227 => floatval(str_replace(',','.',$_POST['persent'])),
								237 => $_POST['inn'],
								219 => 0, 
								243 => $_POST['email'],
								290 => trim($_POST['cite']),
								186 => $uu,
								304 => $max_id_3,
								329 => NewQuotes($_POST['LEGAL_NAME']),
								328 => NewQuotes($_POST['CONTRACT']),
								258 => array(94,98),
								377 => $_POST['prefix'],
								379 => NewQuotes($_POST['contact']),
								467 => $agent_array['id'],
								610 => $_POST['branch'],
								624 => NewQuotes($_POST['brand_name']),
								625 => NewQuotes($_POST['adress_fact']),
								378 => NewQuotes($_POST['LEGAL_NAME_FULL']),
								473 => trim($_POST['REPORT_SIGNS']),
								670 => $_POST['type_agent']
							)
						);
						if ($PRODUCT_ID = $el->Add($arLoadProductArray))
						{
							$_SESSION['MESSAGE'][] = GetMessage("MESSAGE_ADD_02", array("#NAME#" => $_POST['fio']));
						}
						else
						{
							$arResult['ERRORS'][] = $el->LAST_ERROR;
						}
							
						if ($uu)
						{
							$user = new CUser;
							$user->Update($uu, array("UF_COMPANY_RU_POST"=>$PRODUCT_ID, "UF_ROLE" => 4937477));
							$arResult['ERRORS'][] = $user->LAST_ERROR;
							$_POST = array();
						}
						if ($PRODUCT_ID)
						{
							LocalRedirect('/agents/index.php?mode=agent&id='.$PRODUCT_ID);
						}
					}
				}
			}
		}
		$arResult['SETS'] = GetDefaultSettings();
		$arResult["USERS_OF_G"] = array();
		$arUsers = CGroup::GetGroupUser(15);
		foreach ($arUsers as $u)
		{
			$rsUser = CUser::GetByID($u);
			$arUser2 = $rsUser->Fetch();
			if ($arUser2["ACTIVE"] == "Y")
			{
				$arResult["USERS_OF_G"][$arUser2["ID"]] = $arUser2["LAST_NAME"].' '.$arUser2["NAME"].' ['.$arUser2["LOGIN"].']';
			}
		}
		$APPLICATION->SetTitle("Добавление новго агента");
	}
	
	/******************список агентов******************/
	if ($mode == 'list')
	{
		if (isset($_POST['applay']))
		{
			if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
			{
				$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				foreach ($_POST['carriers'] as $id)
				{
					if ($_POST['action'] == 1)
					{
						$el = new CIBlockElement;
						$res = $el->Update($id, array("MODIFIED_BY"=>$u_id,"ACTIVE"=>"Y"));
						$arResult['MESSAGE'][] = GetMessage("MESSAGE_LIST_01", array("#ID#" => $_POST['id_in'][$id]));
					}
					if ($_POST['action'] == 2)
					{
						$el = new CIBlockElement;
						$res = $el->Update($id, array("MODIFIED_BY"=>$u_id, "ACTIVE"=>"N" ));
						$arResult['MESSAGE'][] = GetMessage("MESSAGE_LIST_02", array("#ID#" => $_POST['id_in'][$id]));
					}
				}	
			}
			$_POST = array();
		}
		$use_navigation = ($arParams['PAGINATION'] == "Y") ? true : false;
		$arResult["LIST"] = TheListOfAgents(0, false, $use_navigation, $agent_array['id'], trim($_GET['name_agent']), intval($_GET['branch']));
		if ($use_navigation)
		{
			$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
			unset($arResult["LIST"]["NAV_STRING"]);
		}
	}
}

$this->IncludeComponentTemplate($componentPage);
?>