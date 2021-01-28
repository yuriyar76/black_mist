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
if (is_array($_SESSION['ERRORS']))
{
	$arResult["ERRORS"] = $_SESSION['ERRORS'];
	$_SESSION['ERRORS'] = false;
}

$arResult['ROLE_USER'] = GetRoleOfUser($u_id);

/**************************************************/
/************************УК************************/
/**************************************************/

if ($agent_array['type'] == 51)
{
	$modes = array(
		'list',
		'shop',
		'packs',
		'reports',
		'report',
		'print_report',
		'select_shop',
		'add',
		'report_pdf',
		'make_report',
		'register',
		'send_letter',
		'make_report_return'
	);
	/*******************меню раздела*******************/
	$arResult["MENU"] = array(
		'shop' => "Интернет-магазин",
		'packs' => "Заказы",
		'reports' => "Отчеты",
		'send_letter' => 'Отправить сообщение'
	);
	$arResult["MENU_TOP"] = array(
		'list' => 'Список интернет-магазинов',
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
		unset($arResult["MENU"][$mode]);
		unset($arResult["MENU_TOP"][$mode]);
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
	{
		$APPLICATION->AuthForm("Доступ запрещен");
	}
	
	/****************отправка сообщения****************/
	if ($mode == 'send_letter')
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
				if (!strlen(trim($_POST['subject'])))
				{
					$arResult["ERRORS"][] = GetMessage("ERR_SUBJECT");
				}
				if (!strlen(trim($_POST['text'])))
				{
					$arResult["ERRORS"][] = GetMessage("ERR_TEXT");
				}
				if (count($arResult["ERRORS"]) == 0)
				{
					$send_params = array(
						'MESSAGE' => trim($_POST['text']),
						'LINE' => '',
						'LINK_TO_MESS' => '',
						'SUBJ' => trim($_POST['subject'])
					);
					$qw = SendMessageInSystem($u_id, $agent_array['id'], $_POST['shop'], trim($_POST['subject']), 227, '', '', 190, $send_params);
					$send_params['LINE'] = '<p>=====================================================================</p>';
					$send_params['LINK_TO_MESS'] = GetMessage('LINK_TO_MESS', array('#ID#' => $qw));
					$m = SendMessageMailNew($_POST['shop'], $agent_array['id'], 228, 190, $send_params);
					$arResult["MESSAGE"][] = GetMessage('SECC_SEND_MESS');
					$_POST = array();
				}
			}
		}
		$res = CIBlockElement::GetByID(intval($_GET['id']));
		if($ar_res = $res->GetNext())
		{
			$arResult["TITLE"] = GetMessage('SEND_LETTER_TITLE');
			$arResult['SHOP'] = GetCompany(intval($_GET['id']));
		}
		else
		{
		}
	}
	
	/***********добавление интернет-магазина***********/
	if ($mode == 'add')
	{
		if (isset($_POST['add_shop']))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
				if (strlen($_POST['fio']) <= 2)
				{
					$arResult["ERRORS"][] = 'Наименование интернет-магазина должно быть более двух символов';
				}
				if (strlen($_POST['cite']) <= 3)
				{
					$arResult["ERRORS"][] = 'Адрес сайта должен быть более трех символов';
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
				}
				if (strlen($_POST['contact']) <= 2)
				{
					$arResult["ERRORS"][] = 'ФИО контактного лица должно быть более двух символов';
				}
				if (strlen($_POST['email']) <= 3)
				{
					$arResult["ERRORS"][] = 'E-mail должен быть более трех символов';
				}
				else
				{
					if(!preg_match("|^[-0-9a-z_\.]+@[-0-9a-z_^\.]+\.[a-z]{2,6}$|i", $_POST['email']))
					{
						$arResult["ERRORS"][] = 'E-mail некорректен';
					}
				}
				if (strlen($_POST['phones']) <= 3)
				{
					$arResult["ERRORS"][] = 'Номер телефона быть более трех символов';
				}
				if (($_POST['user_company'] == 0) && (strlen($_POST['login']) <= 3))
				{
					$arResult["ERRORS"][] = 'Логин должен быть более трех символов';
				}
				if (count($arResult["ERRORS"]) == 0)
				{
					if ($_POST['user_company'] == 0)
					{
						$user_not = true;
						$user_login_yet = $user_email_yet = false;
						$send_mail_to_user_yet = false;
						$user_uu_1 = $user_uu_2 = 0;
						$UserLogin = trim($_POST['login']);
						$rsUsers = CUser::GetList(($by="id"), ($order="desc"), array("EMAIL"=>$_POST['email']));
						if ($arUser = $rsUsers->Fetch())
						{
							$user_email_yet = true;
							$user_uu_1 = $arUser["ID"];
						}
						$rsUser_2 = CUser::GetByLogin($UserLogin);
						if($arUser_2 = $rsUser_2->Fetch())
						{
							$user_login_yet = true;
							$user_uu_2 = $arUser_2["ID"];	
						}
						if (($user_uu_1 == $user_uu_2)  && $user_email_yet && $user_login_yet)
						{
							$user_not = false;
							$send_mail_to_user_yet = true;
							$arResult["WARNINGS"][] = 'Пользователь с логином '.$UserLogin.' и e-mail '.$_POST['email'].' уже существует (ID: '.$user_uu_1.')';
							$uu = $user_uu_1;
							$arGroups = CUser::GetUserGroup($user_uu_1);
							$change_uer = false;
							if (!in_array(3,$arGroups))
							{
								$arGroups[] = 3;
								$change_uer = true;
							}
							if (!in_array(17,$arGroups))
							{
								$arGroups[] = 17;
								$change_uer = true;
							}
							if ($change_uer)
							{
								CUser::SetUserGroup($uu, $arGroups);
								$arResult["WARNINGS"][] = 'Пользователь добавлен в группу "DMS магазины"';
							}
						}
						else
						{
							if ($user_email_yet)
							{
								$arResult["ERRORS"][] = 'Пользователь с e-mail '.$_POST['email'].' уже существует ('.$arUser["LOGIN"].' <strong>['.$arUser["ID"].']</strong>)';
								$user_not = false;
							}
							if ($user_login_yet)
							{
								$arResult["ERRORS"][] = 'Пользователь с логином '.$UserLogin.' уже существует ('.$arUser_2["EMAIL"].' <strong>['.$arUser_2["ID"].']</strong>)';
								$user_not = false;
							}
							if ($user_not)
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
									"LAST_NAME" => trim($_POST['contact']),
									'NAME' => trim($_POST['name']),
									'SECOND_NAME' => trim($_POST['surname']),
									"EMAIL" => $_POST['email'],
									"LOGIN" => $UserLogin,
									"ACTIVE" => "Y",
									"GROUP_ID" => array(3,17),
									"PASSWORD" => $pass,
									"CONFIRM_PASSWORD" => $pass,
									"LID" => "s5"
								);
								$uu = $user->Add($arFields);
								if (intval($uu) > 0)
								{
									CEvent::SendImmediate(
										"DMS_NEW_SHOP", 
										"s5", 
										array(
											"LOGIN" => $UserLogin,
											"PASS" => $pass,
											"MAIL" => $arFields['EMAIL'],
											"NAME" => $arFields['NAME'].' '.$arFields['SECOND_NAME']
										),
										"N",
										156
									);
									$arResult['MESSAGE'][]  = "Пользователь <b>".$_POST['login']."</b> успешно добавлен, информация для авторизации отправлена на указанный email.";
								}
								else
								{
									$arResult["ERRORS"][] = $user->LAST_ERROR;
								}
							}
						}
					}
					else
					{
						$uu = $_POST['user_company'];
						$send_mail_to_user_yet = true;
					}
					if (count($arResult["ERRORS"]) == 0)
					{
						if ($send_mail_to_user_yet)
						{
							CEvent::SendImmediate(
								"DMS_NEW_SHOP",
								"s5",
								array(
									"LOGIN" => $UserLogin,
									"PASS" => 'указанный при регистрации на сайте',
									"MAIL" => $_POST['email'],
									"NAME" => trim($_POST['contact'])
								),
								"N",
								156
							);
						}

						$max_id_3 = GetMaxIDIN(40, 3);
						
						$arSets = GetDefaultSettings($agent_array['id']);
						$contact = trim($_POST['contact']);
						if (strlen($_POST['name']))
						{
							$contact .= ' '.trim($_POST['name']);
						}
						if (strlen($_POST['surname']))
						{
							$contact .= ' '.trim($_POST['surname']);
						}
						$bs = new CIBlockSection;
						$arFieldsSec = array(
							"ACTIVE" => 'Y',
							"IBLOCK_SECTION_ID" => false,
							"IBLOCK_ID" => 62,
							"NAME" => trim($_POST['fio']),
						);
						$folder = $bs->Add($arFieldsSec);
						$el = new CIBlockElement;
						$arLoadProductArray = Array(
							"MODIFIED_BY" => $u_id, 
							"IBLOCK_SECTION_ID" => false,
							"IBLOCK_ID" => 40,
							"NAME" => trim($_POST['fio']),
							"ACTIVE" => "Y",
							"PROPERTY_VALUES" => array(
								304 => $max_id_3,
								211 => 52,
								290 => $_POST['cite'],
								187 => $city_shop,
								379 => $contact,
								243 => $_POST['email'],
								265 => $_POST['phones'],
								472 => false,
								466 => false,
								227 => $arSets[291],
								308 => $arSets[309],
								314 => $arSets[315],
								251 => $arSets[253],
								372 => $arSets[373],
								403 => $arSets[404],
								374 => $arSets[375],
								219 => 0,
								186 => $uu, 
								258 => array(94,96,114,115,155,168,179,209,228),
								467 => $agent_array['id'],
								252 => 89,
								310 => false,
								311 => false,
								312 => false,
								303 => $folder,
								491 => 211,
								492 => 214,
								747 => 0,
								748 => 1
							)
						);
						if ($PRODUCT_ID = $el->Add($arLoadProductArray))
						{
							$key = GenericKey($PRODUCT_ID);
							CIBlockElement::SetPropertyValuesEx($PRODUCT_ID, 40, array('UKEY' => $key));
							$arResult['MESSAGE'][] = 'Интернет-магазин <b>'.$_POST['fio'].'</b> успешно добавлен в систему';
						}
						else
						{
							$arResult['ERRORS'][] = $el->LAST_ERROR;
						}
						$user = new CUser;
						$user->Update($uu, array("UF_COMPANY_RU_POST" => $PRODUCT_ID, "UF_ROLE" => 4937477));
						$arResult['ERRORS'][] = $user->LAST_ERROR;
						$_POST = array();
					}
				}
			}
		}
		
		$arResult["TITLE"] = "Добавление интернет-магазина";
		$arResult["USERS_OF_G"] = array();
		$arUsers = CGroup::GetGroupUser(17);
		foreach ($arUsers as $u)
		{
			$rsUser = CUser::GetByID($u);
			$arUser2 = $rsUser->Fetch();
			if ($arUser2["ACTIVE"] == "Y")
			{
				$arResult["USERS_OF_G"][$arUser2["ID"]] = $arUser2["LAST_NAME"].' '.$arUser2["NAME"].' ['.$arUser2["LOGIN"].']';
			}
		}
	}
	
	/************Список интернет-магазинов*************/
	if ($mode == 'list')
	{
		$arResult["EDIT"] = false;
		if ($arResult["PERM"] == "E")
		{
			$arResult["EDIT"] = true;
		}	
		
		if (isset($_POST['applay']))
		{
			foreach ($_POST['shops'] as $id)
			{
				if ($_POST['action'] == 1)
				{
					$el = new CIBlockElement;
					$res = $el->Update($id, array("MODIFIED_BY"=>$u_id,"ACTIVE"=>"Y"));
					$arResult['MESSAGE'][] = 'Интернет-магазин "'.$_POST['name_shop'][$id].'" успешно активирован';
				}
				if ($_POST['action'] == 2)
				{
					$el = new CIBlockElement;
					$res = $el->Update($id, array("MODIFIED_BY"=>$u_id,"ACTIVE"=>"N"));
					$arResult['MESSAGE'][] = 'Интернет-магазин "'.$_POST['name_shop'][$id].'" успешно деактивирован';
				}
				if ($_POST['action'] == 4)
				{
					$shop_info = GetAgentInfo($id);
					if (intval($shop_info["PROPERTY_FOLDER_VALUE"]) > 0)
					{
						CIBlockElement::SetPropertyValuesEx($id, 40, array(252 => false));
						$arResult['MESSAGE'][] = 'Демо-доступ интернет-магазина "'.$_POST['name_shop'][$id].'" успешно деактивирован';
					}
					else
					{
						$arResult['ERRORS'][] = 'Для деактивации демо-доступа интернет-магазина "'.$_POST['name_shop'][$id].'" <a href="index.php?mode=shop&id='.$id.'">укажите раздел товаров</a>';
					}
				}
				if ($_POST['action'] == 5)
				{
					CIBlockElement::SetPropertyValuesEx($id, false, array(252=>89));
					$arResult['MESSAGE'][] = 'Демо-доступ интернет-магазина "'.$_POST['name_shop'][$id].'" успешно активирован';
				}
			}
		}
		
		$arResult["DEMO_FILTER"] = strlen($_GET['demo']) ? $_GET['demo'] : 0;
		$demo_filter = ($arResult["DEMO_FILTER"] == 0) ? false : true;
		$name_filter = strlen($_GET['shop_name']) ? $_GET['shop_name'] : '';
		$type_im_filter = (intval($_GET['type_im']) > 0) ? intval($_GET['type_im']) : 0;
		
		$arResult["TITLE"] = "Список интернет-магазинов";
		$arResult["LIST"] = TheListOfShops(0, $demo_filter, false, true, $name_filter, $agent_array['id'], $type_im_filter);
		$arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
		unset($arResult["LIST"]["NAV_STRING"],$arResult["LISTPACKAGES"]["COUNT"]);	
	}


	/***************************************************/
	if ($mode == 'shop')
	{
		if ($arResult["PERM"] == "E")
			$arResult["EDIT"] = true;
		else
			$arResult["EDIT"] = false;
		
		if (isset($_POST['save']))
		{
			if (strlen($_POST['name']) <= 2)
				$arResult["ERRORS"][] = 'Наименование должно быть более двух символов';
			if (strlen($_POST['cite']) <= 3)
				$arResult["ERRORS"][] = 'Адрес сайта должен быть более трех символов';
			if (strlen($_POST['city']) <= 3)
				$arResult["ERRORS"][] = 'Город должен быть более трех символов';
			else
			{
				$city_shop = GetCityId($_POST['city']);
				if ($city_shop <= 0)
					$arResult["ERRORS"][] = 'Город не найден';
			}
			if (strlen($_POST['inn']))
			{
				if(!is_valid_inn($_POST['inn']))
					$arResult["ERRORS"][] = 'ИНН некорректен';
				if (strlen($_POST['contact']) <= 2)
					$arResult["ERRORS"][] = 'ФИО ответственного лица должно быть более двух символов';
			}
			if (strlen($_POST['email']) <= 3)
				$arResult["ERRORS"][] = 'E-mail должен быть более трех символов';
			else
			{
				if(!preg_match("|^[-0-9a-z_\.]+@[-0-9a-z_^\.]+\.[a-z]{2,6}$|i", $_POST['email'])) 
				$arResult["ERRORS"][] = 'E-mail некорректен';
			}
			if (strlen($_POST['phones']) <= 3)
				$arResult["ERRORS"][] = 'Номер телефона быть более трех символов';
			if (floatval(str_replace(',','.',$_POST['persent'])) < 0)
				$arResult["ERRORS"][] = 'Процент 1 некорректен';
			if (floatval(str_replace(',','.',$_POST['persent2'])) < 0)
				$arResult["ERRORS"][] = 'Процент 2 некорректен';
			if (floatval(str_replace(',','.',$_POST['persent3'])) < 0)
				$arResult["ERRORS"][] = 'Процент 3 некорректен';
			if (floatval(str_replace(',','.',$_POST['cost_ordering'])) < 0)
				$arResult["ERRORS"][] = 'Стоимость формирования заказа некорректна';
			$default_city = $default_delivery = $default_cash = false;
			if (strlen($_POST['default_city'])) {
				$default_city = GetCityId($_POST['default_city']);
				if ($default_city <= 0) $arResult["ERRORS"][] = 'Город по умолчанию не найден';
			}
			if ($_POST['default_delivery'] > 0)
			{
				$default_delivery = $_POST['default_delivery'];
			}
			if ($_POST['default_cash'] > 0) $default_cash = $_POST['default_cash'];
			
			if (intval($_POST['folder']) > 0)
			{
				$folder = intval($_POST['folder']);
			}
			else
			{
				$folder = false;
			}
			/*
			if (intval($_POST["uk"]) <= 0)
			{
				$arResult["ERRORS"][] = "Не выбрана управляющая компания";
			}
			*/
			
			if ($_POST['demo'] == 89)
			{
				$demo = 89;
			}
			else
			{
				if (intval($_POST['folder']) <= 0)
				{
					$arResult['ERRORS'][] = 'Для деактивации демо-доступа укажите раздел товаров';
					$demo = 89;
				}
				else
				{
					$demo = false;
				}
			}
			
			if (count($arResult["ERRORS"]) == 0)
			{
				$el = new CIBlockElement;
				$res = $el->Update($_POST['id'], array("MODIFIED_BY"=>$u_id,"ACTIVE"=>$_POST['active'],"NAME"=>trim($_POST['name'])));
				CIBlockElement::SetPropertyValuesEx($_POST['id'], false, array(
						187 => $city_shop, 
						190 => trim($_POST['adress']),
						227 => floatval(str_replace(',','.',$_POST['persent'])),
						308 => floatval(str_replace(',','.',$_POST['persent2'])),
						314 => floatval(str_replace(',','.',$_POST['persent3'])),
						237 => $_POST['inn'],
						251 => $_POST['price'],
						252 => $demo,
						243 => $_POST['email'],
						265 => $_POST['phones'],
						290 => $_POST['cite'],
						310 => $default_city,
						311 => $default_delivery,
						312 => $default_cash,
						303 => $folder,
						329 => $_POST['LEGAL_NAME'],
						328 => $_POST['CONTRACT'],
						338 => $_POST['ACTING'],
						359 => $_POST['prefix'],
						374 => floatval(str_replace(',','.',$_POST['cost_ordering'])),
						372 => $_POST['price_2'],
						378 => $_POST['LEGAL_NAME_FULL'],
						379 => $_POST['contact'],
						377 => $_POST['prefix_report'],
						403 => $_POST['price_3'],
						466 => $_POST["CONTRACT_TYPE"],
						467 => isset($_POST["uk"]) ? intval($_POST["uk"]) : $agent_array['id'],
						471 => trim($_POST['contact_in']),
						472 => $_POST['OWNERSHIP'],
						473 => trim($_POST['REPORT_SIGNS']),
						491 => $_POST['TYPE_IM'],
						492 => $_POST['CONDITIONS'],
						502 => $_POST['TARIFF_TD'],
						671 => $_POST['IM_BY'],
						747 => $_POST['SELECTION_VAT_REPORT'],
						748 => $_POST['SUBTRACT_AMOUNT_COD']
					));
				$arResult['MESSAGE'][] = 'Интернет-магазин №'.$_POST['id_in'].' успешно изменен';
			}
		}
	
		$id_c = intval($_GET['id']);
		if ($id_c > 0)
		{
			$cc_arr = array();
			$arResult["SHOP"] = false;
			$cc_arr = TheListOfShops($id_c, true, false, false, '', $agent_array['id']);
			if (count($cc_arr) > 0)
			{
				$arResult["SHOP"] = $cc_arr[$id_c];
				$arResult["TITLE"] = 'Интернет-магазин №'.$arResult["SHOP"]["PROPERTY_ID_IN_VALUE"];
				$arResult["LISTPACKAGES"] = GetListOfPackeges($agent_array,$id_c);
				$arResult["PRICES"] = GetListOfPricesForShop($agent_array['id']);
				$arResult["NAV_STRING"] = $arResult["LISTPACKAGES"]["NAV_STRING"];
				unset ($arResult["LISTPACKAGES"]["NAV_STRING"], $arResult["LISTPACKAGES"]["COUNT"]);
				$arResult["UKS"] = TheListOfUKs();
				$arResult["FOLDERS"] = array();
				$db_list = CIBlockSection::GetList(array("name"=>"ASC"), array('IBLOCK_ID'=>62, 'GLOBAL_ACTIVE'=>'Y'));
				while($ar_result = $db_list->GetNext())
				{
					$arResult["FOLDERS"][$ar_result['ID']] = $ar_result['NAME'];
				}
				$arResult["TYPES"] = array();
				$db_enum_list = CIBlockProperty::GetPropertyEnum(466, array(), array("IBLOCK_ID"=>40));
				while($ar_enum_list = $db_enum_list->GetNext())
				{
					$arResult["TYPES"][] = $ar_enum_list;
				}
				$arResult["OWNERSHIPS"] = array();
				$db_enum_list = CIBlockProperty::GetPropertyEnum(472, array(), array("IBLOCK_ID"=>40));
				while($ar_enum_list = $db_enum_list->GetNext())
				{
					$arResult["OWNERSHIPS"][] = $ar_enum_list;
				}
				$arResult["TYPES_IM"] = array();
				$db_enum_list = CIBlockProperty::GetPropertyEnum(491, array(), array("IBLOCK_ID"=>40));
				while($ar_enum_list = $db_enum_list->GetNext())
				{
					$arResult["TYPES_IM"][] = $ar_enum_list;
				}
				$arResult["CONDITIONS"] = array();
				$db_enum_list = CIBlockProperty::GetPropertyEnum(492, array(), array("IBLOCK_ID"=>40));
				while($ar_enum_list = $db_enum_list->GetNext())
				{
					$arResult["CONDITIONS"][] = $ar_enum_list;
				}
				$arResult["TARIFF_TD"] = array();
				$db_enum_list = CIBlockProperty::GetPropertyEnum(502, array(), array("IBLOCK_ID"=>40));
				while($ar_enum_list = $db_enum_list->GetNext())
				{
					$arResult["TARIFF_TD"][] = $ar_enum_list;
				}
			}
			else
			{
				$arResult["TITLE"] = 'Интернет-магазин №'.$id_c.' не найден';
			}
		}
		else
		{
			$arResult["TITLE"] = 'Интернет-магазин №'.$id_c.' не найден';
		}
	}
	
	/***************************************************/
	if ($mode == 'select_shop')
	{
		$arResult["LIST"] = TheListOfShops(0, false, true, false, '', $agent_array['id']);
		
		if (isset($_POST['save']))
		{
			$shop = $_POST['shop_id'];
		
			if ($_POST['action'] == 'select_shop')
			{
				$_SESSION['CURRNET_SHOP'] = $_POST['shop_id'];
				$_SESSION['CURRNET_FOLDER'] = $arResult["LIST"][$_POST['shop_id']]["PROPERTY_FOLDER_VALUE"];
				LocalRedirect('/goods/lists.list.php');
			}
			if ($_POST['action'] ==  'add_purchase')
			{
				$_SESSION['CURRNET_SHOP'] = $_POST['shop_id'];
				$_SESSION['CURRNET_FOLDER'] = $arResult["LIST"][$_POST['shop_id']]["PROPERTY_FOLDER_VALUE"];
				LocalRedirect('/goods/purchase.invoice.php?mode=add_purchase');
			}
			if ($_POST['action'] == 'add_purchase_outgo')
			{
				$_SESSION['CURRNET_SHOP'] = $_POST['shop_id'];
				$_SESSION['CURRNET_FOLDER'] = $arResult["LIST"][$_POST['shop_id']]["PROPERTY_FOLDER_VALUE"];
				LocalRedirect('/goods/purchase.invoice.php?mode=add_purchase_outgo');
			}
			if ($_POST['action'] == 'add_correction')
			{
				$_SESSION['CURRNET_SHOP'] = $_POST['shop_id'];
				$_SESSION['CURRNET_FOLDER'] = $arResult["LIST"][$_POST['shop_id']]["PROPERTY_FOLDER_VALUE"];
				LocalRedirect('/goods/purchase.invoice.php?mode=add_correction');
			}
			if ($_POST['action'] == 'add_correction_price')
			{
				$_SESSION['CURRNET_SHOP'] = $_POST['shop_id'];
				$_SESSION['CURRNET_FOLDER'] = $arResult["LIST"][$_POST['shop_id']]["PROPERTY_FOLDER_VALUE"];
				LocalRedirect('/goods/purchase.invoice.php?mode=add_correction_price');
			}
			if ($_POST['action'] == 'list_purchase')
			{
				$_SESSION['CURRNET_SHOP'] = $_POST['shop_id'];
				$_SESSION['CURRNET_FOLDER'] = $arResult["LIST"][$_POST['shop_id']]["PROPERTY_FOLDER_VALUE"];
				LocalRedirect('/goods/purchase.invoice.php?mode=list_purchase');
			}
			if ($_POST['action'] == 'list_purchase_outgo')
			{
				$_SESSION['CURRNET_SHOP'] = $_POST['shop_id'];
				$_SESSION['CURRNET_FOLDER'] = $arResult["LIST"][$_POST['shop_id']]["PROPERTY_FOLDER_VALUE"];
				LocalRedirect('/goods/purchase.invoice.php?mode=list_purchase_outgo');
			}
			if ($_POST['action'] == 'list_correction')
			{
				$_SESSION['CURRNET_SHOP'] = $_POST['shop_id'];
				$_SESSION['CURRNET_FOLDER'] = $arResult["LIST"][$_POST['shop_id']]["PROPERTY_FOLDER_VALUE"];
				LocalRedirect('/goods/purchase.invoice.php?mode=list_correction');
			}
			if ($_POST['action'] == 'list_correction_price')
			{
				$_SESSION['CURRNET_SHOP'] = $_POST['shop_id'];
				$_SESSION['CURRNET_FOLDER'] = $arResult["LIST"][$_POST['shop_id']]["PROPERTY_FOLDER_VALUE"];
				LocalRedirect('/goods/purchase.invoice.php?mode=list_correction_price');
			}
		}
		/*
		foreach ($arResult["LIST"] as $k => $v)
		{
			unset($arResult["LIST"]);
			$arResult["LIST"] = TheListOfShops(0,false);
		}
		*/
	}
	
	/***************************************************/
	if ($mode == 'packs')
	{
		$id_c = intval($_GET['id']);
		if ($id_c > 0)
		{
			$cc_arr = array();
			$arResult["SHOP"] = false;
			$cc_arr = TheListOfShops($id_c, true, false, false, '', $agent_array['id']);
			if (count($cc_arr) > 0)
			{
				$arResult["SHOP"] = $cc_arr[$id_c];
				$state_filter = (intval($_GET['status']) > 0) ? intval($_GET['status']) : false;
				$only_iskl = ($_GET['iskl'] == 'yes') ? true : false;
				$in_report = ($_GET['in_report'] == 'no') ? true : false;
				$arResult["TITLE"] = 'Заказы интернет-магазина "'.$arResult["SHOP"]["NAME"].'"';
				$arResult["LISTPACKAGES"] = GetListOfPackeges(
					$agent_array,
					$id_c,
					false, 
					$state_filter, 
					false,
					0, 
					false, 
					0, 
					0,
					'',
					'',
					0, 
					true, 
					$in_report, 
					array("ID"=>"DESC"), 
					0, 
					0, 
					false, 
					false,
					$only_iskl
				);
				$arResult["NAV_STRING"] = $arResult["LISTPACKAGES"]["NAV_STRING"];
				unset($arResult["LISTPACKAGES"]["NAV_STRING"],$arResult["LISTPACKAGES"]["COUNT"]);
			}
			else
			{
				$arResult["SHOP"] = false;
				$arResult["TITLE"] = 'Интернет-магазин №'.$id_c.' не найден';
			}
		}
		else
		{
			$arResult["SHOP"] = false;
			$arResult["TITLE"] = 'Интернет-магазин №'.$id_c.' не найден';
		}
		
		$arResult["PROPS"] = array();
		$db_enum_list = CIBlockProperty::GetPropertyEnum(203, array("sort"=>"asc"));
		while($ar_enum_list = $db_enum_list->GetNext())
		{
			$arResult["PROPS"][] = $ar_enum_list;
		}
	}
	
	/***************формирование отчета****************/
	if ($mode == 'make_report')
	{
		if ($arResult["PERM"] == "E")
		{
			$arResult["EDIT"] = true;
		}
		else
		{
			$arResult["EDIT"] = false;
		}
		if (isset($_POST['make_report']))
		{
			$report_id = false;
			if (intval($_POST['report_add_id']) > 0)
			{
				$report_id = intval($_POST['report_add_id']);
				CIBlockElement::SetPropertyValuesEx($report_id, 67, array(
					348 => $_POST['date_form'],
					380 => str_replace(',','.',$_POST['otv']),
					381 => $_POST["date_otv_form"],
					382 => $_POST["date_otv_to"],
					481 => $_POST['date_report_form'],
					482 => $_POST['date_report_to']
				));
				$arResult['MESSAGE'][] = 'Заказы успешно добавлены к отчету №'.$_POST['report_number'];
			}
			else
			{
				$max_id_5 = GetMaxIDIN(67, 5, true, 342, $_POST['shop_id']);
				$el = new CIBlockElement;
				$arLoadProductArray = Array(
					"MODIFIED_BY" => $u_id, 
					"IBLOCK_SECTION_ID" => false,
					"IBLOCK_ID" => 67,
					"NAME" => "Отчёт агента ".$_POST['prefix']."-".intval($max_id_5),
					"ACTIVE" => "Y",
					"PROPERTY_VALUES" => array(
						342 => $_POST['shop_id'],
						343 => $max_id_5,
						348 => $_POST['date_form'],
						380 => str_replace(',','.',$_POST['otv']),
						381 => $_POST["date_otv_form"],
						382 => $_POST["date_otv_to"],
						481 => $_POST['date_report_form'],
						482 => $_POST['date_report_to'],
						506 => 229
					)
				);
				$report_id = $el->Add($arLoadProductArray);
				$arResult['MESSAGE'][] = 'Отчет '.$_POST['prefix'].'-'.$max_id_5.' успешно сформирован. Для отправки отчета интернет-магазину перейдите к 
				<a href="/shops/index.php?mode=reports&id='.$_GET['id'].'">списку отчетов</a> и отправьте отчет, нажав кнопку в соответствующей строке. 
				<a href="/shops/index.php?id='.$_POST['shop_id'].'&report_id='.$report_id.'&mode=report_pdf&pdf=Y" target="_blank">Скачать отчет в формате PDF</a>.';
			}
			if ($report_id)
			{
				foreach ($_POST['packs'] as $p)
				{
					CIBlockElement::SetPropertyValuesEx(
						$p, 
						42, 
						array(
							345 => $report_id, 
							231 => str_replace(',','.',$_POST['rate'][$p]), 
							250 => str_replace(',','.',$_POST['summ_shop'][$p]),
							445 => str_replace(',','.',$_POST['cost_return'][$p]),
						)
					);
				}
				foreach ($_POST['reqvs'] as $r)
				{
					CIBlockElement::SetPropertyValuesEx($r, 76, array(438 => $report_id));
				}
			}
			$arRepInfo = GetOneReport($report_id);		
			CIBlockElement::SetPropertyValuesEx(
				$report_id,  
				67, 
				array(
					483 => $arRepInfo['OBTAINED'],
					484 => $arRepInfo['SUMM_SHOP_AND_ISSUE'],
					485 => $arRepInfo['RATE'],
					486 => $arRepInfo['REQVS_COST'],
					487 => $arRepInfo['SUMM_FORMATION'],
					488 => $arRepInfo["TO_SHOP"]
				)
			);
		}
		
		$id_c = intval($_GET['id']);
		if ($id_c > 0)
		{
			$cc_arr = array();
			$arResult["SHOP"] = false;
			$cc_arr = TheListOfShops($id_c);
			if (count($cc_arr) > 0)
			{
				$arResult["SHOP"] = $cc_arr[$id_c];
				$arResult["RATE"] = WhatIsRate($arResult["SHOP"]['ID']);
				$arResult["TITLE"] = 'Формирование отчета интернет-магазина "'.$arResult["SHOP"]["NAME"].'"';
				$arResult["SHOP_ID"] = $id_c;
				$arResult["SHOP_PREFIX"] = $arResult["SHOP"]['PROPERTY_PREFIX_REPORTS_VALUE'];
				$arResult["PACKS"] = $arResult['PACKS_ISKL'] = array();
				$res = CIBlockElement::GetList(
					array("PROPERTY_DATE_DELIVERY" => "ASC"), 
					array(
						"IBLOCK_ID" => 42,
						'PROPERTY_CREATOR' => $id_c,
						'PROPERTY_REPORT' => false,
						'PROPERTY_STATE' => array(44)
						), 
					false, 
					false, 
					array(
						"ID", "PROPERTY_N_ZAKAZ_IN", 'PROPERTY_DATE_DELIVERY', 'PROPERTY_CONDITIONS', 'PROPERTY_URGENCY', 'PROPERTY_CITY', 'PROPERTY_CITY.NAME', 
						'PROPERTY_WEIGHT', 'PROPERTY_COST_GOODS', 'PROPERTY_COST_3', 'PROPERTY_COST_2', 'PROPERTY_OBTAINED', 'PROPERTY_RATE', 'PROPERTY_SUMM_SHOP',
						'PROPERTY_SIZE_1', 'PROPERTY_SIZE_2', 'PROPERTY_SIZE_3', 'PROPERTY_CASH','PROPERTY_ADRESS'
					)
				);
				while($ob = $res->GetNextElement())
				{
					$a = $ob->GetFields();
					$city = $a['PROPERTY_CITY_VALUE'];
					$conditions = $a['PROPERTY_CONDITIONS_ENUM_ID'];
					$weight = $a['PROPERTY_WEIGHT_VALUE'];
					$size_1 = $a['PROPERTY_SIZE_1_VALUE'];
					$size_2 = $a['PROPERTY_SIZE_2_VALUE'];
					$size_3 = $a['PROPERTY_SIZE_3_VALUE'];
					$price = ($conditions == 38) ? $arResult["SHOP"]["PROPERTY_PRICE_2_VALUE"] : $arResult["SHOP"]["PROPERTY_PRICE_VALUE"];
					$double = ($a['PROPERTY_URGENCY_VALUE'] == 1) ? 2 : 1;
					$have_city = CheckCityToHave($city, $price, $weight, $size_1, $size_2, $size_3, $double, 0);
					$a['CALCULATED_COST_DELIVERY'] = $have_city['COST'];
					$conditions_im = $arResult["SHOP"]["PROPERTY_CONDITIONS_ENUM_ID"];
					$cost = $a['PROPERTY_OBTAINED_VALUE'];
					if ($conditions_im == 214)
					{
						$price_key = ($cost > 0) ? $have_city['PERSENT_1'] : $have_city['PERSENT_2'];
						$pers = $arResult["RATE"][$price_key];
						$cost =  ($cost > 0) ? $cost : $a['PROPERTY_COST_GOODS_VALUE'];
						$r = $cost*$pers/100;
						$a['CALCULATED_RATE'] = number_format($r, 2, '.', '');
					}
					else
					{
						$price_key = ($a['PROPERTY_CASH_ENUM_ID'] == 125) ? $have_city['PERSENT_2'] : $have_city['PERSENT_1'];
						$pers = $arResult["RATE"][$price_key];
						$r = $cost*$pers/100;
						$a['CALCULATED_RATE'] = number_format($r, 2, '.', '');
					}
					$a['V_WEIGHT'] = ($size_1*$size_2*$size_3)/5000;
					$arResult["PACKS"][] = $a;
				}
				$res = CIBlockElement::GetList(
					array("PROPERTY_DATE_DELIVERY" => "ASC"), 
					array(
						"IBLOCK_ID" => 42,
						'PROPERTY_CREATOR' => $id_c,
						'PROPERTY_REPORT' => false,
						'PROPERTY_EXCEPTIONAL_SITUATION' => 1,
						'PROPERTY_END' => 1
						), 
					false, 
					false, 
					array(
						"ID", "PROPERTY_N_ZAKAZ_IN", 'PROPERTY_DATE_DELIVERY', 'PROPERTY_CONDITIONS', 'PROPERTY_URGENCY', 'PROPERTY_CITY', 'PROPERTY_CITY.NAME', 
						'PROPERTY_WEIGHT', 'PROPERTY_COST_GOODS', 'PROPERTY_COST_3', 'PROPERTY_COST_2', 'PROPERTY_OBTAINED', 'PROPERTY_RATE', 'PROPERTY_SUMM_SHOP',
						'PROPERTY_SIZE_1', 'PROPERTY_SIZE_2', 'PROPERTY_SIZE_3', 'PROPERTY_CASH', 'PROPERTY_COST_RETURN','PROPERTY_ADRESS'
					)
				);
				while($ob = $res->GetNextElement())
				{
					$a = $ob->GetFields();
					$city = $a['PROPERTY_CITY_VALUE'];
					$conditions = $a['PROPERTY_CONDITIONS_ENUM_ID'];
					$weight = $a['PROPERTY_WEIGHT_VALUE'];
					$size_1 = $a['PROPERTY_SIZE_1_VALUE'];
					$size_2 = $a['PROPERTY_SIZE_2_VALUE'];
					$size_3 = $a['PROPERTY_SIZE_3_VALUE'];
					$price = ($conditions == 38) ? $arResult["SHOP"]["PROPERTY_PRICE_2_VALUE"] : $arResult["SHOP"]["PROPERTY_PRICE_VALUE"];
					$double = ($a['PROPERTY_URGENCY_VALUE'] == 1) ? 2 : 1;
					$have_city = CheckCityToHave($city, $price, $weight, $size_1, $size_2, $size_3, $double, 0);
					$a['CALCULATED_COST_DELIVERY'] = $have_city['COST'];
					$conditions_im = $arResult["SHOP"]["PROPERTY_CONDITIONS_ENUM_ID"];
					$cost = $a['PROPERTY_OBTAINED_VALUE'];
					if ($conditions_im == 214)
					{
						$price_key = ($cost > 0) ? $have_city['PERSENT_1'] : $have_city['PERSENT_2'];
						$pers = $arResult["RATE"][$price_key];
						$cost =  ($cost > 0) ? $cost : $a['PROPERTY_COST_GOODS_VALUE'];
						$r = $cost*$pers/100;
						$a['CALCULATED_RATE'] = number_format($r, 2, '.', '');
					}
					else
					{
						$price_key = ($a['PROPERTY_CASH_ENUM_ID'] == 125) ? $have_city['PERSENT_2'] : $have_city['PERSENT_1'];
						$pers = $arResult["RATE"][$price_key];
						$r = $cost*$pers/100;
						$a['CALCULATED_RATE'] = number_format($r, 2, '.', '');
					}
					$a['V_WEIGHT'] = ($size_1*$size_2*$size_3)/6000;
					$arResult["PACKS_ISKL"][] = $a;
				}
				/*
				$arResult["PACKS"] = GetListOfPackeges($agent_array, $id_c, false, 44, false, 0, false, 0, 0,'','',0, false, true, array("ID" => "ASC"), 0, 62);
				$arResult["PACKS_ISKL"] = GetListOfPackeges($agent_array, $id_c, false, 0, false, 0, false,  0, 0,'','', 0, false, true, array("ID"=>"ASC"), 0, 0, false, false, true);
				$arResult["PACKS_RETURN"] = GetListOfPackeges($agent_array, $id_c, false, 0, false, 0, false,  0, 0,'','', 0, false, true, array("ID"=>"ASC"), 0, 0, false, false, false, true);
				$arResult["REPORTS"] = GetListOfReportsShop($id_c, $agent_array);
				*/
				$arResult["REQVS"] = GetListRequests($id_c, false, true, array("ID"=>"ASC"), 0, 186, 'no');

				$arResult["REPORTS_SIGNED"] = false;
				
				$arResult['DEFAULT_VALUES'] = array(
					'PROPERTY_DATE_VALUE' => date('d.m.Y'),
					'PROPERTY_DATE_REPORT_FROM_VALUE' => '',
					'PROPERTY_DATE_REPORT_TO_VALUE' => date('d.m.Y')
				);
				$res = CIBlockElement::GetList(
					array("DATE_CREATE" => "DESC"), 
					array(
						"IBLOCK_ID" => 67,
						'PROPERTY_SHOP' => $id_c,
						'PROPERTY_SIGNED'  => 173
						), 
					false, 
					array('nTopCount' => 1), 
					array("ID", 'PROPERTY_DATE_REPORT_TO')
				);
				if($ob = $res->GetNextElement())
				{
					$a = $ob->GetFields();
					$b = $a['PROPERTY_DATE_REPORT_TO_VALUE'];
					$c = strtotime('+1 day', mktime(11, 0, 0, substr($b,3,2), substr($b,0,2), substr($b,6,4)));
					$arResult['DEFAULT_VALUES']['PROPERTY_DATE_REPORT_FROM_VALUE'] = date('d.m.Y', $c);
				}
				
				
				$res = CIBlockElement::GetList(array("ID" => "desc"), array("IBLOCK_ID" => 67, "PROPERTY_SHOP" => $id_c, 'PROPERTY_SIGNED' => false), false, false, array("ID","NAME","PROPERTY_ID_IN","PROPERTY_DATE","PROPERTY_PAYMENT","PROPERTY_STORAGE","PROPERTY_START","PROPERTY_END","PROPERTY_SIGNED", 'PROPERTY_DATE_REPORT_FROM', 'PROPERTY_DATE_REPORT_TO'));
				if ($ob = $res->GetNextElement())
				{
					$arResult["REPORTS_SIGNED"] = $ob->GetFields();
					$arResult['DEFAULT_VALUES'] = $arResult["REPORTS_SIGNED"];
				}
				
				$res = CIBlockElement::GetList(
					array("DATE_CREATE" => "DESC"), 
					array(
						"IBLOCK_ID" => 67,
						'PROPERTY_SHOP' => $id_c,
						'>PROPERTY_STORAGE'  => 0
						), 
					false, 
					array('nTopCount' => 1), 
					array("ID", 'PROPERTY_STORAGE', 'PROPERTY_START', 'PROPERTY_END')
				);
				$arResult["OTV"] = false;
				$ar = array();
				while($ob = $res->GetNextElement())
				{
					$ar[] = $ob->GetFields();
				}
				if (count($ar) == 1)
				{
					$arResult["OTV"]['START'] = $ar[0]['PROPERTY_START_VALUE'];
					$arResult["OTV"]['END'] = $ar[0]['PROPERTY_END_VALUE'];
					$arResult["OTV"]['SUMM'] = $ar[0]['PROPERTY_STORAGE_VALUE'];
				}
			}
			else
			{
				$arResult["TITLE"] = 'Интернет-магазин №'.$id_c.' не найден';
			}
		}
		else
		{
			$arResult["TITLE"] = 'Интернет-магазин №'.$id_c.' не найден';	
		}
	}
	
	/*********формирование отчета по возвратам*********/
	if ($mode == 'make_report_return')
	{
		if ($arResult["PERM"] == "E")
		{
			$arResult["EDIT"] = true;
		}
		else
		{
			$arResult["EDIT"] = false;
		}
		$id_c = intval($_GET['id']);
		if ($id_c > 0)
		{
			$cc_arr = array();
			$arResult["SHOP"] = false;
			$cc_arr = TheListOfShops($id_c);
			if (count($cc_arr) > 0)
			{
				$arResult["SHOP"] = $cc_arr[$id_c];
				$arResult["TITLE"] = 'Формирование отчета по возвратам интернет-магазина "'.$arResult["SHOP"]["NAME"].'"';
				$arResult["SHOP_ID"] = $id_c;
				$arResult["SHOP_PREFIX"] = $arResult["SHOP"]['PROPERTY_PREFIX_REPORTS_VALUE'];
				$arResult["PACKS"] = array();
				$res = CIBlockElement::GetList(
					array("PROPERTY_DATE_TO_DELIVERY" => "ASC"), 
					array(
						"IBLOCK_ID" => 42,
						'PROPERTY_CREATOR' => $id_c,
						'PROPERTY_REPORT_RETURN' => false,
						'PROPERTY_RETURN' => 1
						), 
					false, 
					false, 
					array(
						"ID", "PROPERTY_N_ZAKAZ_IN", 'PROPERTY_DATE_DELIVERY', 'PROPERTY_CONDITIONS', 'PROPERTY_URGENCY', 'PROPERTY_CITY', 'PROPERTY_CITY.NAME', 
						'PROPERTY_WEIGHT', 'PROPERTY_COST_GOODS', 'PROPERTY_COST_3', 'PROPERTY_COST_2', 'PROPERTY_OBTAINED', 'PROPERTY_RATE', 'PROPERTY_SUMM_SHOP',
						'SIZE_1', 'SIZE_2', 'SIZE_3', 'PROPERTY_CASH', 'PROPERTY_COST_GOODS', 'PROPERTY_STATE', 'PROPERTY_DATE_TO_DELIVERY'
					)
				);
				while($ob = $res->GetNextElement())
				{
					$a = $ob->GetFields();
					$arResult["PACKS"][] = $a;
				}
			}
		}
	}
	
	/**********************Отчеты**********************/
	if ($mode == 'reports')
	{
		if ($arResult["PERM"] == "E")
		{
			$arResult["EDIT"] = true;
		}
		else
		{
			$arResult["EDIT"] = false;
		}
		if (isset($_POST['sign']))
		{
			CIBlockElement::SetPropertyValuesEx($_POST['report_id'], 67, array(383 => 173));
			$arResult['MESSAGE'][] = '<a href="http://dms.newpartner.ru/shops/index.php?id='.$_GET['id'].'&report_id='.$_POST['report_id'].'&mode=report">Отчет №'.$_POST['report_number'].'</a> успешно подписан';
		}
		if (isset($_POST['CONFIRMED']))
		{
			CIBlockElement::SetPropertyValuesEx($_POST['report_id'], 67, array(490 => 1));
			$arResult['MESSAGE'][] = '<a href="http://dms.newpartner.ru/shops/index.php?id='.$_GET['id'].'&report_id='.$_POST['report_id'].'&mode=report">Отчет №'.$_POST['report_number'].'</a> успешно согласован интернет-магазином';
		}
		if (isset($_POST['send_report']))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
				$params = array(
					'DATE_SEND' => date('d.m.Y H:i'),
					'REPORT_ID' => $_POST['report_id'],
					'REPORT_NUMBER' => $_POST['report_number'],
					'LINE' => '',
					'LINK_TO_MSG' => '',
					'AUTOMATICALLY' => ''
				);
				$qw = SendMessageInSystem(
					$u_id, 
					$agent_array['id'], 
					intval($_GET['id']), 
					'Отчет №'.$_POST['report_number'].' отправлен на согласование',
					210,
					'',
					'',
					183,
					$params
				);
				$params['LINE'] = '<p>=====================================================================</p>';
				$params['LINK_TO_MSG'] = '<p><a href="http://dms.newpartner.ru/messages/index.php?mode=detail&id='.$qw.'">Ссылка на данное сообщение в системе DMS</a></p>';
				$params['AUTOMATICALLY'] = '<p><i>Письмо сгенерировано автоматически и не требует ответа</i></p>';
				SendMessageMailNew(intval($_GET['id']), $agent_array['id'], 209, 183, $params);
				$arResult['MESSAGE'][] = '<a href="/shops/index.php?id='.$_GET['id'].'&report_id='.$_POST['report_id'].'&mode=report">Отчет №'.$_POST['report_number'].'</a> отправлен интернет-магазину';
			}
		}
		
		$id_c = intval($_GET['id']);
		if ($id_c > 0)
		{
			$cc_arr = array();
			$arResult["SHOP"] = false;
			$cc_arr = TheListOfShops($id_c, true, false, false, '', $agent_array['id']);
			if (count($cc_arr) > 0)
			{
				$arResult["SHOP"] = $cc_arr[$id_c];
				$arResult["TITLE"] = 'Отчеты интернет-магазина "'.$arResult["SHOP"]["NAME"].'"';
				// $arResult["REPORTS"] = GetListOfReportsShop($id_c, $agent_array);
				$arResult["REPORTS"] = GetListOfReportsShopLight($id_c, $agent_array);
				$arResult["SHOP_ID"] = $id_c;
				$arResult["NAV_STRING"] = $arResult["REPORTS"]["NAV_STRING"];
				unset($arResult["REPORTS"]["NAV_STRING"]);
			}
			else
			{
				$arResult["TITLE"] = 'Интернет-магазин №'.$id_c.' не найден';
			}
		}
		else
		{
			$arResult["TITLE"] = 'Интернет-магазин №'.$id_c.' не найден';	
		}
	}
	
	if ($mode == 'register')
	{
		$shop = intval($_GET['shop']);
		$arResult['SHOP'] = GetCompany($shop);
		$reports_ids = 0;
		$all = true;
		if (strlen($_GET['ids']))
		{
			$ids = explode(',',$_GET['ids']);
			if (count($ids) > 0)
			{
				$reports_ids = $ids;
				$all = false;
			}
		}
		$arResult["REPORTS"] = GetListOfReportsShopLight($shop, $agent_array, $reports_ids, false, true, false, array("ID" => "asc"));
		$arLang['NAME_FILE'] = 'Список отчетов '.$arResult['SHOP']['NAME'];
		$arLang['TABLE_HEAD'] = array(
			'№',
			'Дата формирования',
			'За период',
			'Общая стоимость заказов',
			'Сумма за выдачу и доставку',
			'За кассовое обслуживание',
			'Ответственное хранение',
			'Забор / вызов курьера',
			'Формирование',
			'Итого к перечислению Принципиалу',
			'Дата выплаты'
		);
		$a = MakeRegisterReportsPDF($arResult['SHOP'], $arResult["REPORTS"], $arLang, 'D');
	}
	
	/**********************Отчет***********************/
	if (($mode == 'report') || ($mode == 'print_report'))
	{
		if ($arResult["PERM"] == "E")
		{
			$arResult["EDIT"] = true;
		}
		else
		{
			$arResult["EDIT"] = false;
		}
		if (isset($_POST['delete_packs_from_report']))
		{
			foreach ($_POST['packs'] as $p)
			{
				CIBlockElement::SetPropertyValuesEx($p, 42, array(345 => false));
				$arResult['MESSAGE'][] = 'Заказ №'.$_POST['n_zakaz'][$p].' успешно удален из отчета';
			}
			//$arRepInfo = GetListOfReportsShop($_POST['shop_id'], $agent_array, $_POST['report_id'], false);
			$arRepInfo = GetOneReport($_POST['report_id']);

			CIBlockElement::SetPropertyValuesEx(
				$_POST['report_id'], 
				67, 
				array(
				/*
					481 => $arRepInfo['PERIOD_1'],
					482 => $arRepInfo['PERIOD_2'],
					*/
					483 => $arRepInfo['OBTAINED'],
					484 => $arRepInfo['SUMM_SHOP_AND_ISSUE'],
					485 => $arRepInfo['RATE'],
					486 => $arRepInfo['REQVS_COST'],
					487 => $arRepInfo['SUMM_FORMATION'],
					488 => $arRepInfo["TO_SHOP"]
				)
			);
		}
		$id_c = intval($_GET['id']);
		if ($id_c > 0)
		{
			$cc_arr = array();
			$arResult["SHOP"] = false;
			$cc_arr = TheListOfShops($id_c);
			if (count($cc_arr) > 0)
			{
				$arResult["SHOP"] = $cc_arr[$id_c];
				$arResult['REPORT'] = GetOneReport($_GET['report_id']);
				// $arResult["REPORTS"] = GetListOfReportsShop($id_c, $agent_array, intval($_GET['report_id']));
				$arResult["SHOW_N_ZAKAZ_SHOP"] = false;
				foreach ($arResult["REPORT"]["PACKS"] as $pack)
				{
					if ($pack["PROPERTY_N_ZAKAZ_IN_VALUE"] != $pack["PROPERTY_N_ZAKAZ_VALUE"])
					{
						$arResult["SHOW_N_ZAKAZ_SHOP"] = true;
						break;
					}
				}
				$arResult["TITLE"] = 'Отчет интернет-магазина "'.$arResult["SHOP"]["NAME"].'" №'.$arResult["REPORT"]['INFO']["PROPERTY_ID_IN_VALUE"];
			}
		}
	}
	
	if ($mode == 'report_pdf')
	{
		$id_c = intval($_GET['id']);
		if ($id_c > 0)
		{
			$cc_arr = array();
			$arResult["SHOP"] = false;
			$cc_arr = TheListOfShops($id_c);
			if (count($cc_arr) > 0)
			{
				$uk_info = GetCompany($agent_array['id']);
				$arResult["SHOP"] = $cc_arr[$id_c];
				if(intval($arResult["SHOP"]['PROPERTY_SELECTION_VAT_REPORT_VALUE']) == 1)
				{
					$default_text_values = array(
						$uk_info['PROPERTY_LEGAL_NAME_FULL_NDS_VALUE'].' ('.$uk_info['PROPERTY_LEGAL_NAME_NDS_VALUE'].')',
						$uk_info['PROPERTY_RESPONSIBLE_PERSON_IN_NDS_VALUE'].', действующего на основании '.$uk_info['PROPERTY_ACTING_NDS_VALUE'],
						$uk_info['PROPERTY_LEGAL_NAME_NDS_VALUE'],
						$uk_info['PROPERTY_REPORT_SIGNS_NDS_VALUE']
					);
				}
				else
				{
					$default_text_values = array(
						$uk_info['PROPERTY_LEGAL_NAME_FULL_VALUE'].' ('.$uk_info['PROPERTY_LEGAL_NAME_VALUE'].')',
						$uk_info['PROPERTY_RESPONSIBLE_PERSON_IN_VALUE'].', действующего на основании '.$uk_info['PROPERTY_ACTING_VALUE'],
						$uk_info['PROPERTY_LEGAL_NAME_VALUE'],
						$uk_info['PROPERTY_REPORT_SIGNS_VALUE']
					);
				}
				
				// $arResult["REPORTS"] = GetListOfReportsShop($id_c, $agent_array,intval($_GET['report_id']));
				$arResult['REPORT'] = GetOneReport($_GET['report_id']);
				
				$arResult["SHOW_N_ZAKAZ_SHOP"] = false;
				foreach ($arResult["REPORT"]["PACKS"] as $pack)
				{
					if ($pack["PROPERTY_N_ZAKAZ_IN_VALUE"] != $pack["PROPERTY_N_ZAKAZ_VALUE"])
					{
						$arResult["SHOW_N_ZAKAZ_SHOP"] = true;
						break;
					}
				}
				$txt = $arResult['SHOP']['PROPERTY_LEGAL_NAME_FULL_VALUE'];
				$txt .= ($arResult['SHOP']["PROPERTY_LEGAL_NAME_VALUE"]) ? ' ('.$arResult['SHOP']["PROPERTY_LEGAL_NAME_VALUE"].')' : '';
				$txt .= ', '.GetMessage("TEXT_12_".$arResult['SHOP']['PROPERTY_OWNERSHIP_ENUM_ID']).' в дальнейшем «'.GetMessage("TEXT_3_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"]).'», ';
				$txt .= (strlen($arResult['SHOP']['PROPERTY_RESPONSIBLE_PERSON_IN_VALUE'])) ? ' в лице '.$arResult['SHOP']['PROPERTY_RESPONSIBLE_PERSON_IN_VALUE'].', ' : '';
				$txt .= (strlen($arResult['SHOP']['PROPERTY_ACTING_VALUE'])) ? ' действующий на основании '.$arResult['SHOP']['PROPERTY_ACTING_VALUE'].', ' : '';
				$txt .=  'с одной стороны, и '.$default_text_values[0].', именуемое в дальнейшем «'.GetMessage("TEXT_4_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"]).'», ';
				$txt .= ' в лице '.$default_text_values[1].', с другой стороны, настоящим Отчетом удостоверяют, что в соответствии с условиями '.GetMessage("TEXT_5_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"]).' за указанный период '.GetMessage("TEXT_6_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"]).' '.
					GetMessage("TEXT_7_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"]).' выданы и доставлены следующие Заказы:';
				$arHeads = array(
					'№ п/п',
					'Номер заказа',
					'Номер заказа ИМ',
					'Дата выдачи клиенту',
					'Тип доставки',
					'Город',
					'Вес заказа, кг.',
					'Стоимость товаров, руб.',
					'Стоимость доставки/выдачи для покупателя, руб.',
					'Общая стоимость заказа, руб.',
					'Тип оплаты',
					'Получено с клиента, руб.',
					'Агентское вознаграждение за доставку, руб.',
					'Агентское вознаграждение за выдачу заказа, руб.',
					'Агентское вознаграждение за прием оплаты, руб.',
					'Формирование заказа, руб.'
				);
				if (!$arResult["SHOW_N_ZAKAZ_SHOP"])
				{
					unset($arHeads[2]);
				}
				if ($arResult["REPORT"]['SUMM_FORMATION'] <= 0)
				{
					unset($arHeads[15]);
				}
				$arDatas = array();
				$i = 0;
				foreach ($arResult['REPORT']['PACKS'] as $k => $p)
				{
					$i++;
					$d_delivery = $p['PROPERTY_DATE_DELIVERY_VALUE'];
					$d_delivery .= ($p["PROPERTY_EXCEPTIONAL_SITUATION_VALUE"] == 1) ? 'Искл. сит. ' : '';
					$d_delivery .= ($p["PROPERTY_RETURN_VALUE"] == 1) ? "Возврат" : '';
					$s1 = $s2 = $s3 = '';
					if ($p['PROPERTY_CONDITIONS_ENUM_ID'] == 37)
					{
						// $s1 =  ($p['SUMM_SHOP'] > 0) ? $p['SUMM_SHOP']+$p['COST_RETURN'] : '';
						$s1 = $p['PROPERTY_SUMM_SHOP_VALUE']+$p['PROPERTY_COST_RETURN_VALUE'];
					}
					if ($p['PROPERTY_CONDITIONS_ENUM_ID'] == 38)
					{
						/*
						if (($p["EXCEPTIONAL"] == 1) || (($p['CITY_ID'] == 8054) && ($p["RETURN"] == 1)))
						{
						}
						else
						{
							$s2 =  ($p['SUMM_SHOP'] > 0) ? $p['SUMM_SHOP']+$p['COST_RETURN'] : '';
						}
						*/
						$s2 = $p['PROPERTY_SUMM_SHOP_VALUE']+$p['PROPERTY_COST_RETURN_VALUE'];
						
					}
					/*
					if ($p["EXCEPTIONAL"] != 1)
					{
						$s3 = ($p['RATE'] > 0) ? number_format($p['RATE'], 2, ',', '') : '';
					}
					else
					{
						if ($k == 6078334)
						{
							$s3 = number_format($p['RATE'], 2, ',', '');
						}
					}
					*/
					$s3 = number_format($p['PROPERTY_RATE_VALUE'], 2, ',', '');
					/*
					if (($p["EXCEPTIONAL"] == 1) || ($p["RETURN"] == 1)) 
					{
						if ($k == 6078334)
						{
							$c_2 = 500;
						}
						else
						{
							$c_2 = 0;
						}
					}
					else
					{
						$c_2 =  $p['COST_2'];
					}
					$c_2 = $p['COST_POLUCHENO'];
					*/
					$www = ($p['V_WEIGHT'] > $p['PROPERTY_WEIGHT_VALUE']) ? WeightFormat($p['V_WEIGHT'], false) : WeightFormat($p['PROPERTY_WEIGHT_VALUE'], false);
					$c_2 = $p['PROPERTY_OBTAINED_VALUE'];
					$arData = array(
						$i,
						$p['PROPERTY_N_ZAKAZ_IN_VALUE'],
						$p['PROPERTY_N_ZAKAZ_VALUE'],
						$d_delivery,
						$p['PROPERTY_CONDITIONS_VALUE'],
						$p['PROPERTY_CITY_NAME'],
						// number_format($p['PROPERTY_WEIGHT_VALUE'], 2, ',', ''),
						$www,
						$p['PROPERTY_COST_GOODS_VALUE'],
						$p['PROPERTY_COST_3_VALUE'],
						$p['PROPERTY_COST_2_VALUE'],
						$p['PROPERTY_TYPE_PAYMENT_VALUE'],
						$c_2,
						$s1,
						$s2,
						$s3,
						number_format($p['PROPERTY_SUMM_ISSUE_VALUE'], 2, ',', '')
					);
					if (!$arResult["SHOW_N_ZAKAZ_SHOP"])
					{
						unset($arData[2]);
					}
					if ($arResult["REPORT"]['SUMM_FORMATION'] <= 0)
					{
						unset($arData[15]);
					}
					$arDatas[] = $arData;
				}
				$arItogo = array(
					'Итого',
					$arResult["REPORT"]["OBTAINED"],
					$arResult["REPORT"]["SUMM_SHOP"],
					$arResult["REPORT"]["SUMM_ISSUE"],
					number_format($arResult["REPORT"]["RATE"], 2, ',', '')
				);
				if ($arResult["REPORT"]['SUMM_FORMATION'] > 0)
				{
					$arItogo[] = number_format($arResult["REPORT"]['SUMM_FORMATION'], 2, ',', '');
				}
				$arStringsItogo = array();
				$arStringsItogo[] = 'Сумма денежных средств, полученных с клиентов - '.number_format($arResult["REPORT"]["OBTAINED"], 2, ',', '').' рублей.';
				if (intval($arResult["REPORT"]['INFO']["PROPERTY_STORAGE_VALUE"]) > 0)
				{
					$str_ins = 'Ответственное хранение с '.$arResult["REPORT"]['INFO']["PROPERTY_START_VALUE"].' по '.$arResult["REPORT"]['INFO']["PROPERTY_END_VALUE"].' - '.number_format($arResult["REPORT"]['INFO']["PROPERTY_STORAGE_VALUE"], 2, ',', '').' рублей';
					/*
					if(intval($arResult["SHOP"]['PROPERTY_SELECTION_VAT_REPORT_VALUE']) == 1)
					{
						$str_ins .= ', в том числе НДС '.number_format(($arResult["REPORT"]['INFO']["PROPERTY_STORAGE_VALUE"]/118*18), 2, ',', '').' рублей';
					}
					*/
					$str_ins .= '.';
					$arStringsItogo[] = $str_ins;

				}
				if (intval($arResult['REPORT']["REQVS_COST"]) > 0)
				{
					$str_ins = 'Сумма за забор заказов составляет '.number_format($arResult["REPORT"]["REQVS_COST"], 2, ',', '').' рублей';
					/*
					if(intval($arResult["SHOP"]['PROPERTY_SELECTION_VAT_REPORT_VALUE']) == 1)
					{
						$str_ins .= ', в том числе НДС '.number_format(($arResult["REPORT"]['REQVS_COST']/118*18), 2, ',', '').' рублей';
					}
					*/
					$str_ins .= '.';
					$arStringsItogo[] = $str_ins;
				}
				$str_ins = 'Сумма вознаграждения Агента за выдачу и доставку составляет '.number_format($arResult["REPORT"]["SUMM_SHOP_AND_ISSUE"],2, ',', '').' рублей';
				/*
				if(intval($arResult["SHOP"]['PROPERTY_SELECTION_VAT_REPORT_VALUE']) == 1)
				{
					$str_ins .= ', в том числе НДС '.number_format(($arResult["REPORT"]['SUMM_SHOP_AND_ISSUE']/118*18), 2, ',', '').' рублей';
				}
				*/
				$str_ins .= '.';
				$arStringsItogo[] = $str_ins;
				if (intval($arResult['REPORT']["SUMM_FORMATION"]) > 0)
				{
					$str_ins = 'Сумма формирование заказов составляет '.number_format($arResult["REPORT"]["SUMM_FORMATION"], 2, ',', '').' рублей';
					/*
					if(intval($arResult["SHOP"]['PROPERTY_SELECTION_VAT_REPORT_VALUE']) == 1)
					{
						$str_ins .= ', в том числе НДС '.number_format(($arResult["REPORT"]['SUMM_FORMATION']/118*18), 2, ',', '').' рублей';
					}
					*/
					$str_ins .= '.';
					$arStringsItogo[] = $str_ins;
				}
				$str_ins = GetMessage("TEXT_8_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"]).' за кассовое обслуживание составляет '.number_format($arResult["REPORT"]["RATE"], 2, ',', '').' рублей';
				/*
				if(intval($arResult["SHOP"]['PROPERTY_SELECTION_VAT_REPORT_VALUE']) == 1)
				{
					$str_ins .= ', в том числе НДС '.number_format(($arResult["REPORT"]['RATE']/118*18), 2, ',', '').' рублей';
				}
				*/
				$str_ins .= '.';
				$arStringsItogo[] = $str_ins;
				if(intval($arResult["SHOP"]['PROPERTY_SELECTION_VAT_REPORT_VALUE']) != 1)
				{
					$arStringsItogo[] = 'В связи с применением упрощенной системы налогообложения НДС не облагается.';
				}
				if(intval($arResult["SHOP"]['PROPERTY_SUBTRACT_AMOUNT_COD_VALUE']) == 1)
				{
					$to_shop = GetMessage("TEXT_9_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"]);
					if ($arResult["REPORT"]["TO_SHOP"] < 0)
					{
						$arResult["REPORT"]["TO_SHOP"] = (-1)*$arResult["REPORT"]["TO_SHOP"];
						$to_shop = GetMessage("TEXT_13_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"]);
					}
					$arStringsItogo[] = $to_shop.' составляет '.num2str($arResult["REPORT"]["TO_SHOP"]).'.';
				}
				else
				{
					$arStringsItogo[] = 'Сумма для перечисления Принципалу составляет '.num2str($arResult["REPORT"]["OBTAINED"]).'.';
					$str_ins = 'Сумма для перечисления Агенту составляет '.num2str($arResult["REPORT"]["SUMM_AGENT"]);
					if(intval($arResult["SHOP"]['PROPERTY_SELECTION_VAT_REPORT_VALUE']) == 1)
					{
						$str_ins .= ', в том числе НДС '.num2str(($arResult["REPORT"]['SUMM_AGENT']/118*18));
					}
					$arStringsItogo[] = $str_ins.'.';
				}
				$podp = '___________________ (';
				$podp .= strlen($arResult['SHOP']["PROPERTY_REPORT_SIGNS_VALUE"]) ? $arResult['SHOP']["PROPERTY_REPORT_SIGNS_VALUE"] : $arResult['SHOP']["PROPERTY_RESPONSIBLE_PERSON_VALUE"];
				$podp .= ')';
				$arReqv = false;
				if (count($arResult['REPORT']["REQVS"]) > 0)
				{
					$arReqv['title'] = 'Получение товаров у поставщиков:';
					$arReqv['table'][] = array(
						'№ п/п',
						'Номер заявки',
						'Тип',
						'Поставщик / Заказ',
						'Дата забора',
						'Вес, кг',
						'Объемный вес, кг',
						'Стоимость, руб.'
					);
					$i = 0;
					foreach ($arResult['REPORT']['REQVS'] as $r)
					{
						$i++;
						$arR = array(
							$i++,
							$r["PROPERTY_NUMBER_VALUE"],
							$r["PROPERTY_TYPE_VALUE"],
							convertDates($r["PROPERTY_SUPPLIER_NAME"]),
							$r["PROPERTY_DATE_VALUE"],
							number_format($r["PROPERTY_WEIGHT_VALUE"], 2, ',', ''),
							number_format(($r["PROPERTY_SIZE_1_VALUE"]*$r["PROPERTY_SIZE_2_VALUE"]*$r["PROPERTY_SIZE_3_VALUE"]/6000), 2, ',', ''),
							number_format($r["PROPERTY_COST_VALUE"], 2, ',', '')
						);
						$arReqv['table'][] = $arR;
					}
					$arReqv['footer'] = array('Итого', number_format($arResult['REPORT']["REQVS_COST"], 2, ',', ''));
				}
				$arLang = array(
					'Приложение № 2 «А»',
					GetMessage("TEXT_1_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"]).' '.$arResult['SHOP']['PROPERTY_CONTRACT_VALUE'],
					GetMessage("TEXT_2_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"]).' '.
						$arResult['SHOP']['PROPERTY_PREFIX_REPORTS_VALUE'].'-'.intval($arResult["REPORT"]['INFO']["PROPERTY_ID_IN_VALUE"]),
					'№ '.$arResult['SHOP']['PROPERTY_PREFIX_REPORTS_VALUE'].'-'.intval($arResult["REPORT"]['INFO']["PROPERTY_ID_IN_VALUE"]).' от '.convertDates($arResult["REPORT"]['INFO']["DATE_FORMATED"]),
					'За период с '.convertDates($arResult["REPORT"]['INFO']["START_DATE"]).' по '.convertDates($arResult["REPORT"]['INFO']["END_DATE"]),
					$txt,
					$arHeads,
					$arDatas,
					$arItogo,
					$arStringsItogo,
					'Подписи сторон',
					array(GetMessage("TEXT_10_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"]), '', GetMessage("TEXT_11_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"], '')),
					array($default_text_values[2], '', strlen($arResult['SHOP']["PROPERTY_LEGAL_NAME_VALUE"]) ? $arResult['SHOP']["PROPERTY_LEGAL_NAME_VALUE"] : $arResult['SHOP']["PROPERTY_LEGAL_NAME_FULL_VALUE"], ''),
					array('___________________ ('.$default_text_values[3].')', '', $podp, ''),
					array(array('value' => 'М.П.', 'align'=>'R'), '', array('value' => 'М.П.', 'align'=>'R'), ''),
					$arReqv,
					$arResult['SHOP']['PROPERTY_PREFIX_REPORTS_VALUE'].'-'.intval($arResult['REPORT']['INFO']["PROPERTY_ID_IN_VALUE"]).'.pdf'
				);
				MakeReportPDF($arLang, 'D');
			}
		}
	}
}

/**************************************************/
/************************ИМ************************/
/**************************************************/

if ($agent_array['type'] == 52)
{
	$modes = array(
		'select_shop',
		'reports',
		'report',
		'print_report',
		'report_pdf'
	);
	$arResult["MENU"] = array(
		'reports' => 'Список отчетов'
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
		$componentPage = "shop_".$mode;
		unset($arResult["MENU"][$mode]);
	}
	else
	{
		$APPLICATION->AuthForm("Доступ запрещен");
	}

	/******************Выбор магазина******************/
	if ($mode == 'select_shop')
	{
		$arResult["LIST"] = TheListOfShops();
		$_SESSION['CURRNET_SHOP'] = $agent_id;
		$_SESSION['CURRNET_FOLDER'] = $arResult["LIST"][$agent_id]["PROPERTY_FOLDER_VALUE"];
		LocalRedirect('/goods/lists.list.php');
	}
	
	/**********************Отчеты**********************/
	if ($mode == 'reports')
	{
		if ($_GET['request'] == 'Y')
		{
			$send = CheckRequestThisWeek($agent_array['id'], $agent_array['uk']);
			if ($send)
			{
				$_SESSION["ERRORS"][] = 'Запрос на формирование отчета за предыдущую неделю уже был отправлен ранее';
			}
			else
			{
				$params = array(
					'DATE_SEND' => date('d.m.Y H:i'),
					'SHOP_ID' => $agent_array['id'],
					'SHOP_NAME' => $agent_array['name'],
					'LINE' => '',
					'LINK_TO_MESS' => '',
					'AUTOMATICALLY' => ''
				);	
				$qw = SendMessageInSystem($u_id, $agent_array['id'], $agent_array['uk'], 'Запрос формирования отчета', 220, '', '', 189, $params);
				$params['LINE'] = '<p>=====================================================================</p>';
				$params['LINK_TO_MESS'] = '<p><a href="http://dms.newpartner.ru/messages/index.php?mode=detail&id='.$qw.'">Ссылка на данное сообщение в системе DMS</a></p>';
				$params['AUTOMATICALLY'] = '<p><i>Письмо сгенерировано автоматически и не требует ответа</i></p>';
				SendMessageMailNew($agent_array['uk'], $agent_array['id'], 221, 189, $params);
				$_SESSION['MESSAGE'][] = 'Запрос на формирование отчета успешно отправлен';
			}
			LocalRedirect('/accounting/');
		}
		$arResult["TITLE"] = 'Список отчетов';
		$arResult["SHOP_ID"] = $agent_array['id'];
		$cc_arr = TheListOfShops($agent_array['id']);
		$arResult["SHOP"] = $cc_arr[$agent_array['id']];
		$arResult["REPORTS"] = GetListOfReportsShopLight($agent_array['id'], $agent_array);
		$arResult["NAV_STRING"] = $arResult["REPORTS"]["NAV_STRING"];
		unset($arResult["REPORTS"]["NAV_STRING"]);
	}
	
	/**********************Отчет***********************/
	if (($mode == 'report') || ($mode == 'print_report'))
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
						'SHOP_ID' => $agent_array['id'],
						'SHOP_NAME' => $agent_array['name'],
						'TRUE_OR_FALSE' => 'опровергнул',
						'REPORT_ID' => $_POST['report_id'],
						'REPORT_NUMBER' => $_POST['report_number'],
						'REASON' => '<p>Причина несогласия с отчетом: <strong>'.trim($_POST['reason']).'</strong></p>',
						'SUMM' => '',
						'LINK_TO_SIGNATURE' => '',
						'AFTER_SIGNING' => '',
						'SHOP_OF_NAME' => $_POST['shop_of_name'],
						'SHOP_CONTACT' => $_POST['shop_contact'],
						'SHOP_PHONE' => $_POST['shop_phone'],
						'SHOP_EMAIL' => $_POST['shop_email'],
						'LINE' => '',
						'LINK_TO_MSG' => '',
						'AUTOMATICALLY' => ''
					);
					$qw = SendMessageInSystem(
						$u_id, 
						$agent_array['id'], 
						$agent_array['uk'], 
						'Отчет №'.$_POST['report_number'].' не согласован',
						210,
						'',
						'',
						182,
						$params
					);
					$params['LINE'] = '<p>=====================================================================</p>';
					$params['LINK_TO_MSG'] = '<p><a href="http://dms.newpartner.ru/messages/index.php?mode=detail&id='.$qw.'">Ссылка на данное сообщение в системе DMS</a></p>';
					$params['AUTOMATICALLY'] = '<p><i>Письмо сгенерировано автоматически и не требует ответа</i></p>';
					SendMessageMailNew($agent_array['uk'], $agent_array['id'], 209, 182, $params);
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
				CIBlockElement::SetPropertyValuesEx($_POST['report_id'], 67, array(490 => 1, 383 => 173));
				$params = array(
					'DATE_SEND' => date('d.m.Y H:i'),
					'SHOP_ID' => $agent_array['id'],
					'SHOP_NAME' => $agent_array['name'],
					'TRUE_OR_FALSE' => 'подтвердил',
					'REPORT_ID' => $_POST['report_id'],
					'REPORT_NUMBER' => $_POST['report_number'],
					'REASON' => '',
					'SUMM' => '<p>Выплата по отчету составляет: <strong>'.$_POST['summ_report'].'</strong></p>',
					/*
					'LINK_TO_SIGNATURE' => '<p>Для подписи и последующей оплаты перейдите к <a href="http://dms.newpartner.ru/shops/index.php?mode=reports&id='.$agent_array['id'].'">списку отчётов интернет-магазина</a> и нажмите кнопку "Подписать".</p>',
					'AFTER_SIGNING' => 'После подписания отчета необходимо уведомить интернет-магазин о дате и времени получения денежных средств. ',
					*/
					'LINK_TO_SIGNATURE' => '',
					'AFTER_SIGNING' => 'Пожалуйста, уведомите интернет-магазин о дате и времени получения денежных средств. ',
					'SHOP_OF_NAME' => $_POST['shop_of_name'],
					'SHOP_CONTACT' => $_POST['shop_contact'],
					'SHOP_PHONE' => $_POST['shop_phone'],
					'SHOP_EMAIL' => $_POST['shop_email'],
					'LINE' => '',
					'LINK_TO_MSG' => '',
					'AUTOMATICALLY' => ''
				);
				$qw = SendMessageInSystem(
					$u_id, 
					$agent_array['id'], 
					$agent_array['uk'], 
					'Отчет №'.$_POST['report_number'].' согласован',
					210,
					'',
					'',
					182,
					$params
				);
				$params['LINE'] = '<p>=====================================================================</p>';
				$params['LINK_TO_MSG'] = '<p><a href="http://dms.newpartner.ru/messages/index.php?mode=detail&id='.$qw.'">Ссылка на данное сообщение в системе DMS</a></p>';
				$params['AUTOMATICALLY'] = '<p><i>Письмо сгенерировано автоматически и не требует ответа</i></p>';
				SendMessageMailNew($agent_array['uk'], $agent_array['id'], 209, 182, $params);
				$arResult['MESSAGE'][] = 'Сообщение о согласии с отчетом успешно отправлено';
			}
		}
		
		$cc_arr = TheListOfShops($agent_array['id']);
		$arResult["SHOP"] = $cc_arr[$agent_array['id']];
		// $arResult["REPORTS"] = GetListOfReportsShop($agent_array['id'], $agent_array,intval($_GET['report_id']));
		$arResult['REPORT'] = GetOneReport($_GET['report_id']);
		
		$arResult["SHOW_N_ZAKAZ_SHOP"] = false;
		foreach ($arResult["REPORT"]["PACKS"] as $pack)
		{
			if ($pack["PROPERTY_N_ZAKAZ_IN_VALUE"] != $pack["PROPERTY_N_ZAKAZ_VALUE"])
			{
				$arResult["SHOW_N_ZAKAZ_SHOP"] = true;
				break;
			}
		}
		$arResult["TITLE"] = 'Отчет №'.$arResult["REPORT"]['INFO']["PROPERTY_ID_IN_VALUE"];
	}
	
	if ($mode == 'report_pdf')
	{
		$id_c = $agent_array['id'];
		if ($id_c > 0)
		{
			$cc_arr = array();
			$arResult["SHOP"] = false;
			$cc_arr = TheListOfShops($id_c);
			if (count($cc_arr) > 0)
			{
				$arResult["SHOP"] = $cc_arr[$id_c];
				$uk_info = GetCompany($arResult['SHOP']['PROPERTY_UK_VALUE']);
				if(intval($arResult["SHOP"]['PROPERTY_SELECTION_VAT_REPORT_VALUE']) == 1)
				{
					$default_text_values = array(
						$uk_info['PROPERTY_LEGAL_NAME_FULL_NDS_VALUE'].' ('.$uk_info['PROPERTY_LEGAL_NAME_NDS_VALUE'].')',
						$uk_info['PROPERTY_RESPONSIBLE_PERSON_IN_NDS_VALUE'].', действующего на основании '.$uk_info['PROPERTY_ACTING_NDS_VALUE'],
						$uk_info['PROPERTY_LEGAL_NAME_NDS_VALUE'],
						$uk_info['PROPERTY_REPORT_SIGNS_NDS_VALUE']
					);
				}
				else
				{
					$default_text_values = array(
						$uk_info['PROPERTY_LEGAL_NAME_FULL_VALUE'].' ('.$uk_info['PROPERTY_LEGAL_NAME_VALUE'].')',
						$uk_info['PROPERTY_RESPONSIBLE_PERSON_IN_VALUE'].', действующего на основании '.$uk_info['PROPERTY_ACTING_VALUE'],
						$uk_info['PROPERTY_LEGAL_NAME_VALUE'],
						$uk_info['PROPERTY_REPORT_SIGNS_VALUE']
					);
				}
				// $arResult["REPORTS"] = GetListOfReportsShop($id_c, $agent_array,intval($_GET['report_id']));
				$arResult['REPORT'] = GetOneReport($_GET['report_id']);
				
				$arResult["SHOW_N_ZAKAZ_SHOP"] = false;
				foreach ($arResult["REPORT"]["PACKS"] as $pack)
				{
					if ($pack["PROPERTY_N_ZAKAZ_IN_VALUE"] != $pack["PROPERTY_N_ZAKAZ_VALUE"])
					{
						$arResult["SHOW_N_ZAKAZ_SHOP"] = true;
						break;
					}
				}
				$txt = $arResult['SHOP']['PROPERTY_LEGAL_NAME_FULL_VALUE'];
				$txt .= ($arResult['SHOP']["PROPERTY_LEGAL_NAME_VALUE"]) ? ' ('.$arResult['SHOP']["PROPERTY_LEGAL_NAME_VALUE"].')' : '';
				$txt .= ', '.GetMessage("TEXT_12_".$arResult['SHOP']['PROPERTY_OWNERSHIP_ENUM_ID']).' в дальнейшем «'.GetMessage("TEXT_3_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"]).'», ';
				$txt .= (strlen($arResult['SHOP']['PROPERTY_RESPONSIBLE_PERSON_IN_VALUE'])) ? ' в лице '.$arResult['SHOP']['PROPERTY_RESPONSIBLE_PERSON_IN_VALUE'].', ' : '';
				$txt .= (strlen($arResult['SHOP']['PROPERTY_ACTING_VALUE'])) ? ' действующий на основании '.$arResult['SHOP']['PROPERTY_ACTING_VALUE'].', ' : '';
				$txt .=  'с одной стороны, и '.$default_text_values[0].', именуемое в дальнейшем «'.GetMessage("TEXT_4_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"]).'», ';
				$txt .= ' в лице '.$default_text_values[1].', с другой стороны, настоящим Отчетом удостоверяют, что в соответствии с условиями '.GetMessage("TEXT_5_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"]).' за указанный период '.GetMessage("TEXT_6_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"]).' '.
					GetMessage("TEXT_7_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"]).' выданы и доставлены следующие Заказы:';
				$arHeads = array(
					'№ п/п',
					'Номер заказа',
					'Номер заказа ИМ',
					'Дата выдачи клиенту',
					'Тип доставки',
					'Город',
					'Вес заказа, кг.',
					'Стоимость товаров, руб.',
					'Стоимость доставки/выдачи для покупателя, руб.',
					'Общая стоимость заказа, руб.',
					'Тип оплаты',
					'Получено с клиента, руб.',
					'Агентское вознаграждение за доставку, руб.',
					'Агентское вознаграждение за выдачу заказа, руб.',
					'Агентское вознаграждение за прием оплаты, руб.',
					'Формирование заказа, руб.'
				);
				if (!$arResult["SHOW_N_ZAKAZ_SHOP"])
				{
					unset($arHeads[2]);
				}
				if ($arResult["REPORT"]['SUMM_FORMATION'] <= 0)
				{
					unset($arHeads[15]);
				}
				$arDatas = array();
				$i = 0;
				foreach ($arResult['REPORT']['PACKS'] as $k => $p)
				{
					$i++;
					$d_delivery = $p['PROPERTY_DATE_DELIVERY_VALUE'];
					$d_delivery .= ($p["PROPERTY_EXCEPTIONAL_SITUATION_VALUE"] == 1) ? 'Искл. сит. ' : '';
					$d_delivery .= ($p["PROPERTY_RETURN_VALUE"] == 1) ? "Возврат" : '';
					$s1 = $s2 = $s3 = '';
					if ($p['PROPERTY_CONDITIONS_ENUM_ID'] == 37)
					{
						$s1 = $p['PROPERTY_SUMM_SHOP_VALUE']+$p['PROPERTY_COST_RETURN_VALUE'];
					}
					if ($p['PROPERTY_CONDITIONS_ENUM_ID'] == 38)
					{
						$s2 = $p['PROPERTY_SUMM_SHOP_VALUE']+$p['PROPERTY_COST_RETURN_VALUE'];
						
					}
					$s3 = number_format($p['PROPERTY_RATE_VALUE'], 2, ',', '');
					$c_2 = $p['PROPERTY_OBTAINED_VALUE'];
					$www = ($p['V_WEIGHT'] > $p['PROPERTY_WEIGHT_VALUE']) ? WeightFormat($p['V_WEIGHT'], false) : WeightFormat($p['PROPERTY_WEIGHT_VALUE'], false);
					$arData = array(
						$i,
						$p['PROPERTY_N_ZAKAZ_IN_VALUE'],
						$p['PROPERTY_N_ZAKAZ_VALUE'],
						$d_delivery,
						$p['PROPERTY_CONDITIONS_VALUE'],
						$p['PROPERTY_CITY_NAME'],
						$www,
						$p['PROPERTY_COST_GOODS_VALUE'],
						$p['PROPERTY_COST_3_VALUE'],
						$p['PROPERTY_COST_2_VALUE'],
						$p['PROPERTY_TYPE_PAYMENT_VALUE'],
						$c_2,
						$s1,
						$s2,
						$s3,
						number_format($p['PROPERTY_SUMM_ISSUE_VALUE'], 2, ',', '')
					);
					if (!$arResult["SHOW_N_ZAKAZ_SHOP"])
					{
						unset($arData[2]);
					}
					if ($arResult["REPORT"]['SUMM_FORMATION'] <= 0)
					{
						unset($arData[15]);
					}
					$arDatas[] = $arData;
				}
				$arItogo = array(
					'Итого',
					$arResult["REPORT"]["OBTAINED"],
					$arResult["REPORT"]["SUMM_SHOP"],
					$arResult["REPORT"]["SUMM_ISSUE"],
					number_format($arResult["REPORT"]["RATE"], 2, ',', '')
				);
				if ($arResult["REPORT"]['SUMM_FORMATION'] > 0)
				{
					$arItogo[] = number_format($arResult["REPORT"]['SUMM_FORMATION'], 2, ',', '');
				}
				$arStringsItogo = array();
				$arStringsItogo[] = 'Сумма денежных средств, полученных с клиентов - '.number_format($arResult["REPORT"]["OBTAINED"], 2, ',', '').' рублей.';
				/*
				if (intval($arResult["REPORT"]['INFO']["PROPERTY_STORAGE_VALUE"]) > 0)
				{
					$arStringsItogo[] = 'Ответственное хранение с '.$arResult["REPORT"]['INFO']["PROPERTY_START_VALUE"].' по '.$arResult["REPORT"]['INFO']["PROPERTY_END_VALUE"].' - '.
						number_format($arResult["REPORT"]['INFO']["PROPERTY_STORAGE_VALUE"], 2, ',', '').' рублей.';

				}
				if (intval($arResult['REPORT']["REQVS_COST"]) > 0)
				{
					$arStringsItogo[] = 'Сумма за забор заказов составляет '.number_format($arResult["REPORT"]["REQVS_COST"], 2, ',', '').' рублей.';
				}
				$arStringsItogo[] = 'Сумма вознаграждения Агента за выдачу и доставку составляет '.number_format($arResult["REPORT"]["SUMM_SHOP_AND_ISSUE"],2, ',', '').' рублей.';
				if (intval($arResult['REPORT']["SUMM_FORMATION"]) > 0)
				{
					$arStringsItogo[] = 'Сумма формирование заказов составляет '.number_format($arResult["REPORT"]["SUMM_FORMATION"], 2, ',', '').' рублей.';
				}
				$arStringsItogo[] = GetMessage("TEXT_8_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"]).' за кассовое обслуживание составляет '.number_format($arResult["REPORT"]["RATE"], 2, ',', '').' рублей.';
				$arStringsItogo[] = 'В связи с применением упрощенной системы налогообложения НДС не облагается.';
				*/
				if (intval($arResult["REPORT"]['INFO']["PROPERTY_STORAGE_VALUE"]) > 0)
				{
					$str_ins = 'Ответственное хранение с '.$arResult["REPORT"]['INFO']["PROPERTY_START_VALUE"].' по '.$arResult["REPORT"]['INFO']["PROPERTY_END_VALUE"].' - '.number_format($arResult["REPORT"]['INFO']["PROPERTY_STORAGE_VALUE"], 2, ',', '').' рублей';
					/*
					if(intval($arResult["SHOP"]['PROPERTY_SELECTION_VAT_REPORT_VALUE']) == 1)
					{
						$str_ins .= ', в том числе НДС '.number_format(($arResult["REPORT"]['INFO']["PROPERTY_STORAGE_VALUE"]/118*18), 2, ',', '').' рублей';
					}
					*/
					$str_ins .= '.';
					$arStringsItogo[] = $str_ins;

				}
				if (intval($arResult['REPORT']["REQVS_COST"]) > 0)
				{
					$str_ins = 'Сумма за забор заказов составляет '.number_format($arResult["REPORT"]["REQVS_COST"], 2, ',', '').' рублей';
					/*
					if(intval($arResult["SHOP"]['PROPERTY_SELECTION_VAT_REPORT_VALUE']) == 1)
					{
						$str_ins .= ', в том числе НДС '.number_format(($arResult["REPORT"]['REQVS_COST']/118*18), 2, ',', '').' рублей';
					}
					*/
					$str_ins .= '.';
					$arStringsItogo[] = $str_ins;
				}
				$str_ins = 'Сумма вознаграждения Агента за выдачу и доставку составляет '.number_format($arResult["REPORT"]["SUMM_SHOP_AND_ISSUE"],2, ',', '').' рублей';
				/*
				if(intval($arResult["SHOP"]['PROPERTY_SELECTION_VAT_REPORT_VALUE']) == 1)
				{
					$str_ins .= ', в том числе НДС '.number_format(($arResult["REPORT"]['SUMM_SHOP_AND_ISSUE']/118*18), 2, ',', '').' рублей';
				}
				*/
				$str_ins .= '.';
				$arStringsItogo[] = $str_ins;
				if (intval($arResult['REPORT']["SUMM_FORMATION"]) > 0)
				{
					$str_ins = 'Сумма формирование заказов составляет '.number_format($arResult["REPORT"]["SUMM_FORMATION"], 2, ',', '').' рублей';
					/*
					if(intval($arResult["SHOP"]['PROPERTY_SELECTION_VAT_REPORT_VALUE']) == 1)
					{
						$str_ins .= ', в том числе НДС '.number_format(($arResult["REPORT"]['SUMM_FORMATION']/118*18), 2, ',', '').' рублей';
					}
					*/
					$str_ins .= '.';
					$arStringsItogo[] = $str_ins;
				}
				$str_ins = GetMessage("TEXT_8_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"]).' за кассовое обслуживание составляет '.number_format($arResult["REPORT"]["RATE"], 2, ',', '').' рублей';
				/*
				if(intval($arResult["SHOP"]['PROPERTY_SELECTION_VAT_REPORT_VALUE']) == 1)
				{
					$str_ins .= ', в том числе НДС '.number_format(($arResult["REPORT"]['RATE']/118*18), 2, ',', '').' рублей';
				}
				*/
				$str_ins .= '.';
				$arStringsItogo[] = $str_ins;
				if(intval($arResult["SHOP"]['PROPERTY_SELECTION_VAT_REPORT_VALUE']) != 1)
				{
					$arStringsItogo[] = 'В связи с применением упрощенной системы налогообложения НДС не облагается.';
				}
				if(intval($arResult["SHOP"]['PROPERTY_SUBTRACT_AMOUNT_COD_VALUE']) == 1)
				{
					$to_shop = GetMessage("TEXT_9_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"]);
					if ($arResult["REPORT"]["TO_SHOP"] < 0)
					{
						$arResult["REPORT"]["TO_SHOP"] = (-1)*$arResult["REPORT"]["TO_SHOP"];
						$to_shop = GetMessage("TEXT_13_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"]);
					}
					$arStringsItogo[] = $to_shop.' составляет '.num2str($arResult["REPORT"]["TO_SHOP"]).'.';
				}
				else
				{
					$arStringsItogo[] = 'Сумма для перечисления Принципалу составляет '.num2str($arResult["REPORT"]["OBTAINED"]).'.';
					$str_ins = 'Сумма для перечисления Агенту составляет '.num2str($arResult["REPORT"]["SUMM_AGENT"]);
					if(intval($arResult["SHOP"]['PROPERTY_SELECTION_VAT_REPORT_VALUE']) == 1)
					{
						$str_ins .= ', в том числе НДС '.num2str(($arResult["REPORT"]['SUMM_AGENT']/118*18));
					}
					$arStringsItogo[] = $str_ins.'.';
				}
				$podp = '___________________ (';
				$podp .= strlen($arResult['SHOP']["PROPERTY_REPORT_SIGNS_VALUE"]) ? $arResult['SHOP']["PROPERTY_REPORT_SIGNS_VALUE"] : $arResult['SHOP']["PROPERTY_RESPONSIBLE_PERSON_VALUE"];
				$podp .= ')';
				$arReqv = false;
				if (count($arResult['REPORT']["REQVS"]) > 0)
				{
					$arReqv['title'] = 'Получение товаров у поставщиков:';
					$arReqv['table'][] = array(
						'№ п/п',
						'Номер заявки',
						'Тип',
						'Поставщик / Заказ',
						'Дата забора',
						'Вес, кг',
						'Объемный вес, кг',
						'Стоимость, руб.'
					);
					$i = 0;
					foreach ($arResult['REPORT']['REQVS'] as $r)
					{
						$i++;
						$arR = array(
							$i++,
							$r["PROPERTY_NUMBER_VALUE"],
							$r["PROPERTY_TYPE_VALUE"],
							convertDates($r["PROPERTY_SUPPLIER_NAME"]),
							$r["PROPERTY_DATE_VALUE"],
							number_format($r["PROPERTY_WEIGHT_VALUE"], 2, ',', ''),
							number_format(($r["PROPERTY_SIZE_1_VALUE"]*$r["PROPERTY_SIZE_2_VALUE"]*$r["PROPERTY_SIZE_3_VALUE"]/6000), 2, ',', ''),
							number_format($r["PROPERTY_COST_VALUE"], 2, ',', '')
						);
						$arReqv['table'][] = $arR;
					}
					$arReqv['footer'] = array('Итого', number_format($arResult['REPORT']["REQVS_COST"], 2, ',', ''));
				}
				$arLang = array(
					'Приложение № 2 «А»',
					GetMessage("TEXT_1_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"]).' '.$arResult['SHOP']['PROPERTY_CONTRACT_VALUE'],
					GetMessage("TEXT_2_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"]).' '.
						$arResult['SHOP']['PROPERTY_PREFIX_REPORTS_VALUE'].'-'.intval($arResult["REPORT"]['INFO']["PROPERTY_ID_IN_VALUE"]),
					'№ '.$arResult['SHOP']['PROPERTY_PREFIX_REPORTS_VALUE'].'-'.intval($arResult["REPORT"]['INFO']["PROPERTY_ID_IN_VALUE"]).' от '.convertDates($arResult["REPORT"]['INFO']["DATE_FORMATED"]),
					'За период с '.convertDates($arResult["REPORT"]['INFO']["START_DATE"]).' по '.convertDates($arResult["REPORT"]['INFO']["END_DATE"]),
					$txt,
					$arHeads,
					$arDatas,
					$arItogo,
					$arStringsItogo,
					'Подписи сторон',
					array(GetMessage("TEXT_10_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"]), '', GetMessage("TEXT_11_".$arResult['SHOP']["PROPERTY_CONTRACT_TYPE_ENUM_ID"], '')),
					array($default_text_values[2], '', strlen($arResult['SHOP']["PROPERTY_LEGAL_NAME_VALUE"]) ? $arResult['SHOP']["PROPERTY_LEGAL_NAME_VALUE"] : $arResult['SHOP']["PROPERTY_LEGAL_NAME_FULL_VALUE"], ''),
					array('___________________ ('.$default_text_values[3].')', '', $podp, ''),
					array(array('value' => 'М.П.', 'align'=>'R'), '', array('value' => 'М.П.', 'align'=>'R'), ''),
					$arReqv,
					$arResult['SHOP']['PROPERTY_PREFIX_REPORTS_VALUE'].'-'.intval($arResult['REPORT']['INFO']["PROPERTY_ID_IN_VALUE"]).'.pdf'
				);
				MakeReportPDF($arLang, 'D');
			}
		}
	}
}

$this->IncludeComponentTemplate($componentPage);
?>