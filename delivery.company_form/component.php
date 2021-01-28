<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/components/black_mist/delivery.packages/functions.php');
CModule::IncludeModule('iblock');
$u_id = $USER->GetID();
if (isset($_POST["change_company"]))
{
	$user = new CUser;
	$user->Update($u_id, array("UF_COMPANY_RU_POST" => $_POST["company_id"]));
}
$agent_array = GetCurrentAgent($u_id);
if ($agent_array)
{
	$agent_id = $agent_array['id'];
}
else
{
	$agent_id = 0;
}
$arResult["ALL_FIELDS"] = array();
$arResult["MENU"] = array();
$arResult['ALL_FIELDS']['PROPERTY_LEGAL_NAME_VALUE'] = array(
	'field_id' => 329,
	'type' => 'text',
	'for_types' => array(51, 52, 53),
	'reqv' => array(51, 52, 53),
	'label' => 'Юридическое наименование'
);
$arResult['ALL_FIELDS']['PROPERTY_LEGAL_NAME_FULL_VALUE'] = array(
	'field_id' => 378,
	'type' => 'text',
	'for_types' => array(51),
	'reqv' => array(51),
	'label' => 'Юридическое наименование полностью'
);
$arResult['ALL_FIELDS']['PROPERTY_RESPONSIBLE_PERSON_IN_VALUE'] = array(
	'field_id' => 471,
	'type' => 'text',
	'for_types' => array(51),
	'reqv' => array(51),
	'label' => 'Действует в лице'
);
$arResult['ALL_FIELDS']['PROPERTY_ACTING_VALUE'] = array(
	'field_id' => 338,
	'type' => 'text',
	'for_types' => array(51),
	'reqv' => array(51),
	'label' => 'Действует на основании'
);
$arResult['ALL_FIELDS']['PROPERTY_REPORT_SIGNS_VALUE'] = array(
	'field_id' => 473,
	'type' => 'text',
	'for_types' => array(51),
	'reqv' => array(51),
	'label' => 'Отчет подписывает'
);
$arResult['ALL_FIELDS']['PROPERTY_CITY'] = array(
	'field_id' => 187,
	'type' => 'city',
	'for_types' => array(51, 52, 53),
	'reqv' => array(51, 52, 53),
	'label' => 'Город'
);
$arResult['ALL_FIELDS']['PROPERTY_ADRESS_VALUE'] = array(
	'field_id' => 190,
	'type' => 'text',
	'for_types' => array(51, 52, 53),
	'reqv' => array(53),
	'label' => 'Адрес'
);
$arResult['ALL_FIELDS']['PROPERTY_INN_VALUE'] = array(
	'field_id' => 237,
	'type' => 'inn',
	'for_types' => array(51, 52, 53),
	'reqv' => array(51, 53),
	'label' => 'ИНН'
);
$arResult['ALL_FIELDS']['PROPERTY_EMAIL_VALUE'] = array(
	'field_id' => 243,
	'type' => 'email',
	'for_types' => array(51, 52, 53),
	'reqv' => array(51, 52, 53),
	'label' => 'E-mail для уведомлений'
);
$arResult['ALL_FIELDS']['PROPERTY_PHONES_VALUE'] = array(
	'field_id' => 265,
	'type' => 'phone',
	'for_types' => array(51, 52, 53),
	'reqv' => array(52, 53),
	'label' => 'Номер телефона'
);
$arResult['ALL_FIELDS']['PROPERTY_CITE_VALUE'] = array(
	'field_id' => 290,
	'type' => 'text',
	'for_types' => array(52),
	'reqv' => array(52),
	'label' => 'Адрес сайта'
);
$arResult['ALL_FIELDS']['PROPERTY_DEFAULT_CITY'] = array(
	'field_id' => 310,
	'type' => 'city',
	'for_types' => array(52),
	'reqv' => array(),
	'label' => 'Город доставки по умолчанию'
);
$arResult['ALL_FIELDS']['PROPERTY_DEFAULT_DELIVERY_ENUM_ID'] = array(
	'field_id' => 311,
	'type' => 'select',
	'for_types' => array(52),
	'reqv' => array(),
	'label' => 'Тип доставки по умолчанию',
	'values' => array(0 => '',120 => 'По адресу', 121 => 'Самовывоз')
);
$arResult['ALL_FIELDS']['PROPERTY_DEFAULT_CASH_ENUM_ID'] = array(
	'field_id' => 312,
	'type' => 'select',
	'for_types' => array(52),
	'reqv' => array(),
	'label' => 'Кассовое обслуживание по умолчанию',
	'values' => array(0 => '', 122 => 'С кассовым обслуживанием', 123 => 'Без кассового обслуживания')
);
$arResult['ALL_FIELDS']['PROPERTY_PREFIX_VALUE'] = array(
	'field_id' => 359,
	'type' => 'text',
	'for_types' => array(52),
	'reqv' => array(),
	'label' => 'Префикс артикула товаров'
);
$vals = array();
$db_enum_list = CIBlockProperty::GetPropertyEnum(477, array("sort" => "asc"), array("IBLOCK_ID" => 40));
while ($ar_enum_list = $db_enum_list->GetNext())
{
  $vals[$ar_enum_list['ID']] = $ar_enum_list['VALUE'];
}
$arResult['ALL_FIELDS']['PROPERTY_ON_PAGE_ENUM_ID'] = array(
	'field_id' => 477,
	'type' => 'select',
	'for_types' => array(51, 52, 53),
	'reqv' => array(),
	'label' => 'Количество элементов на странице',
	'values' => $vals
);
$arResult["COMPANY_TYPE"] = $agent_array['type'];
if (isset($_POST['delete_demo']))
{
	$packs = GetListOfPackeges($agent_array,$agent_id);
	unset($packs["NAV_STRING"],$packs["COUNT"]);
	foreach ($packs as $p)
	{
		CIBlockElement::Delete($p["ID"]);
	}
	$arResult['MESSAGE'][] = 'Демо-данные успешно удалены';
}
if (isset($_POST['start_work']))
{
	$packs = GetListOfPackeges($agent_array,$agent_id);
	unset($packs["NAV_STRING"],$packs["COUNT"]);
	foreach ($packs as $p)
	{
		CIBlockElement::Delete($p["ID"]);
	}
	$arResult['MESSAGE'][] = 'Демо-данные успешно удалены';
	CModule::IncludeModule("form");
	$city = GetFullNameOfCity($_POST['PROPERTY_CITY_VALUE']);
	$arValues = array(
		"form_text_88" => $_POST['NAME'],
		'form_url_98' => $_POST['PROPERTY_CITE_VALUE'],
		'form_text_89' => $city,
		'form_text_101' => $_POST['PROPERTY_ADRESS_VALUE'],
		'form_text_100' => $_POST['PROPERTY_INN_VALUE'],
		'form_text_91' => $_POST['user_name'],
		'form_email_93' => $_POST['PROPERTY_EMAIL_VALUE'],
		'form_text_92' => $_POST['PROPERTY_PHONES_VALUE'],
		'form_textarea_95' => 'Заявка на снятие демо-статуса и начала сотруднчиества'
	);
	if ($RESULT_ID = CFormResult::Add(10, $arValues, "N", $_POST['user_id']))
	{
		CFormResult::SetStatus($RESULT_ID, 14,"N");
		CFormResult::Mail($RESULT_ID,151);
		$arResult['MESSAGE'][] = 'Запрос на сотрудничество отправлен администратору системы';
	}
}
if (isset($_POST['save']))
{
	$massive_to_change = array();
	foreach ($arResult['ALL_FIELDS'] as $code => $v)
	{
		if ((in_array($arResult["COMPANY_TYPE"],$v['reqv']))&&(!strlen($_POST[$code])))
		{
			$arResult["ERRORS"][] = 'Поле "'.$v['label'].'" обязательно для заполнения';
		}
		else
		{
			if (($v['type'] == 'text') || ($v['type'] == 'phone'))
			{
				$massive_to_change[$v['field_id']] = NewQuotes($_POST[$code]);
			}
			elseif ($v['type'] == 'email')
			{
				if (strlen($_POST[$code]))
				{
					if (!preg_match("/^([-\._a-zA-Z0-9]+)@([-\._a-zA-Z0-9]+)\.([a-zA-Z]{2,4})/i", $_POST[$code])) 
					{
						$arResult["ERRORS"][] = 'E-mail некорректен';
					}
					else
					{
						$massive_to_change[$v['field_id']] = $_POST[$code];
					}
				}
				else
				{
					$massive_to_change[$v['field_id']] = $_POST[$code];
				}
			}
			elseif ($v['type'] == 'inn')
			{
				if (strlen($_POST[$code]))
				{
					if (!is_valid_inn($_POST[$code]))
					{
						$arResult["ERRORS"][] = $v['label'].' некорректен';
					}
					else
					{
						$massive_to_change[$v['field_id']] = $_POST[$code];
					}
				}
				else
				{
					$massive_to_change[$v['field_id']] = $_POST[$code];
				}
			}
			elseif ($v['type'] == 'city')
			{
				if (strlen($_POST[$code]))
				{
					$city_shop = GetCityId($_POST[$code]);
					if ($city_shop <= 0)
					{
						$arResult["ERRORS"][] = $v['label'].' не найден';
					}
					else
					{
						$massive_to_change[$v['field_id']] = $city_shop;
					}
				}
				else
				{
					$massive_to_change[$v['field_id']] = $_POST[$code];
				}
			}
			elseif ($v['type'] == 'select')
			{
				if ($_POST[$code] > 0)
				{
					$massive_to_change[$v['field_id']] = $_POST[$code];
				}
				else
				{
					$massive_to_change[$v['field_id']] = false;
				}
			}
		}
	}
	$massive_to_change[258]=$_POST["mail_sets"];
	CIBlockElement::SetPropertyValuesEx($agent_id, 40, $massive_to_change);
	$arResult['MESSAGE'][] = 'Профиль компании успешно изменен';
}
$mail_settings = $ava_sets = array();
$db_enum_list = CIBlockProperty::GetPropertyEnum(258, array(), array("IBLOCK_ID" => 40));
while ($ar_enum_list = $db_enum_list->GetNext())
{
	$mail_settings[$ar_enum_list["ID"]] = $ar_enum_list["VALUE"];
}
$ava_sets[51] = array(94, 97, 102, 129, 162, 165, 177, 189, 209, 221, 228, 263); //УК
$ava_sets[52] = array(96, 94, 114, 115, 155, 168, 179, 209, 228); //ИМ
$ava_sets[53] = array(98, 94, 103, 228); //Агент
foreach ($mail_settings as $k => $v)
{
	if (!in_array($k, $ava_sets[$agent_array['type']]))
	{
		unset($mail_settings[$k]);
	}
}
$arResult["MAIL_SETS"] = $mail_settings;
$arResult["ALL_UKS"] = TheListOfUKs($agent_array['id']);
$arResult["COMPANY"] = GetCompany($agent_id);
$arResult["TITLE"] = $arResult["COMPANY"]["NAME"].', редактирование профиля компании';
$this->IncludeComponentTemplate();
?>