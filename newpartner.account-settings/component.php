<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true){die();}?>
<?
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");
$arResult['ERRORS'] = array();
$arResult['MESSAGE'] = array();
$arResult['WARNINGS'] = array();
if ($arParams['TYPE'] == 53)
{
	$mode = 'agent';
}
elseif ($arParams['TYPE'] == 242)
{
	$mode = 'client';
}
else
{
	$mode = '';
}
$rsUser = CUser::GetByID($USER->GetID());
$arUser = $rsUser->Fetch();
$arResult["USER_ID"] = $arUser["ID"];
$agent_id = intval($arUser["UF_COMPANY_RU_POST"]);
if ($agent_id > 0)
{
    $arResult['AGENT'] = GetCompany($agent_id);
    $settingsJson = $arResult['AGENT']['PROPERTY_ACCOUNT_LK_SETTINGS_VALUE']['TEXT'];
    $arSettings = array();
    if (strlen($settingsJson))
    {
        $arSettings = json_decode(htmlspecialcharsBack($settingsJson), true);
    }
    $arResult["COMPANIES"] = GetListContractors($arResult['AGENT']['ID'], 259, false);

    if (isset($_POST['save']))
    {
        if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
        {

            $_POST = array();
            $arResult['ERRORS'][] = GetMessage('ERR_REPEATED_FORM');
        }
        else
        {
            $_SESSION[$_POST['key_session']] = $_POST['rand'];
			$arTosave = array();
			if (isset($arSettings[$arResult["USER_ID"]]))
			{
				$arTosave = $arSettings[$arResult["USER_ID"]];
			}
			$arValuesTosave = $_POST;
			$arValuesTosave['CALLCOURIER'] = ($arValuesTosave['CALLCOURIER'] == 'yes') ? $arValuesTosave['CALLCOURIER'] : '';
			unset($arValuesTosave['save']);
			unset($arValuesTosave['rand']);
			unset($arValuesTosave['key_session']);
			foreach ($arValuesTosave as $k => $v)
			{
				$arTosave[$k] = $v;
			}
			/*
            $arTosave = array(
                'CALLCOURIER' => $_POST['CALLCOURIER'],
                'CHOICE_COMPANY' => $_POST['CHOICE_COMPANY'],
                'TYPE_DELIVERY' => $_POST['TYPE_DELIVERY'],
                'WHO_DELIVERY' => $_POST['WHO_DELIVERY'],
                'TYPE_PACK' => $_POST['TYPE_PACK'],
                'TYPE_PAYS' => $_POST['TYPE_PAYS'],
                'PAYMENT' => $_POST['PAYMENT']
            );
			*/
            $arSettings[$arResult["USER_ID"]] = $arTosave;
            $js_string = json_encode($arSettings);
            CIBlockElement::SetPropertyValuesEx($arResult['AGENT']['ID'], 40, array(730 => array('VALUE' => array('TYPE' => 'text', 'TEXT' => $js_string))));
            $arResult['MESSAGE'][] = 'Настройки успешно сохранены';
        }
    }
    $arResult['USER_SETTINGS'] = $arSettings[$arResult["USER_ID"]];
}
else
{
    //TODO [x]Ошибка доступа
	$arResult['WARNINGS'][] = 'Ошибка доступа';
}
$this->IncludeComponentTemplate($mode);
?>