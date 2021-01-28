<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/components/black_mist/delivery.packages/functions.php');
CModule::IncludeModule('iblock');
$u_id = $USER->GetID();
$agent_array = GetCurrentAgent($u_id);

if ($agent_array['type'] == 51)
{
	$uk = $agent_array['id'];
}
else
{
	$uk = $agent_array['uk'];
}

$arResult['ON_PAGE_GLOBAL'] = GetSettingValue(476, false, $uk);


if (isset($_SESSION['ON_PAGE_GLOBAL']))
{
	$arResult['ON_PAGE_GLOBAL'] = $_SESSION['ON_PAGE_GLOBAL'];
}

if (isset($_GET['on_page']))
{
	$arResult['ON_PAGE_GLOBAL'] = intval($_GET['on_page']);
	$_SESSION['ON_PAGE_GLOBAL'] = intval($_GET['on_page']);
}

$arResult["PAGES"] = array();

$db_enum_list = CIBlockProperty::GetPropertyEnum(476, array('sort' => 'asc'), array("IBLOCK_ID" => 47));
while ($ar_enum_list = $db_enum_list->GetNext())
{
	$arResult["PAGES"][] = $ar_enum_list["VALUE"];
}
$arResult["PAGE"] = $arParams["PAGE"];
$arResult["HID_FIELDS"] = $arParams["HID_FIELDS"];
$arResult["NAV_STRING"] = htmlspecialcharsBack($arParams["NAV_STRING"]);

$this->IncludeComponentTemplate();
?>