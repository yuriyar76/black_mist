<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/components/black_mist/delivery.packages/functions.php');

CModule::IncludeModule('iblock');
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
	$APPLICATION->AuthForm("Доступ запрещен");
}

$arResult["ERRORS"] = array();

if (is_array($_SESSION['MESSAGE']))
{
	$arResult["MESSAGE"] = $_SESSION['MESSAGE'];
	$_SESSION['MESSAGE'] = false;
}

/*************************************************/
/************************УК***********************/
/*************************************************/

if ($agent_array['type'] == 51)
{
	$modes = array("default");
	if (in_array($_GET['mode'], $modes))
	{
		$mode = $_GET['mode'];
	}
	else
	{
		if ($arParams['MODE'])
			$mode = $arParams['MODE'];
		else
			$mode = 'default';
	}
	$componentPage = "upr_".$mode;
	
	if (isset($arParams['PERM'][$agent_array['type']]['ALL']))
	{
		$arResult["PERM"] = $arParams['PERM'][$agent_array['type']]['ALL'];
	}
	else
	{
		$role = GetRoleOfUser($u_id);
		$arResult["PERM"] = $arParams['PERM'][$agent_array['type']][$mode][$role];
	}

	if ($arResult["PERM"] == "C")
		$APPLICATION->AuthForm("Доступ запрещен");
		
	$db_props = CIBlockElement::GetProperty(40, $agent_array['id'], array("sort" => "asc"), Array("CODE" => "SETTINGS"));
	if($ar_props = $db_props->Fetch())
	{
		$sets_id = $ar_props["VALUE"];
	
		if(isset($_POST['save']))
		{
			$props = array();
			foreach ($_POST['names'] as $k => $v) {
				$props[$k] = str_replace(',','.',$_POST[$v]);
			}
			CIBlockElement::SetPropertyValuesEx($sets_id, false, $props);
			$arResult["MESSAGE"][] = 'Настройки успешно изменены';
		}
	
		$arResult["TITLE"] = 'Настройки';	
		$arResult['SETTINGS'] = $arResult['PRICES'] = array();
		$db_props = CIBlockElement::GetProperty(47, $sets_id, array("sort" => "asc"), array());
		while ($ar_props = $db_props->Fetch()) {
			$arResult['SETTINGS'][] = $ar_props;
		}
		

        
        foreach ($arResult['SETTINGS'] as $set)
        {
            if ($set["PROPERTY_TYPE"] == "L")
            {
                $arResult[$set['ID']] = array(false => '');
                $db_enum_list = CIBlockProperty::GetPropertyEnum($set['ID'], Array(), Array());
                while($ar_enum_list = $db_enum_list->GetNext())
                {
                  $arResult[$set['ID']][] = $ar_enum_list;
                }
            }
             if ($set["PROPERTY_TYPE"] == "E")
             {
                 $arResult[$set['ID']] = array(false => '');
                 if ($set['LINK_IBLOCK_ID'] == 40)
                 {
                     $filter = array("IBLOCK_ID"=>40,"ACTIVE"=>"Y","PROPERTY_UK"=> $agent_array['id']);
                     if ($set['ID'] == 507)
                     {
                         $filter['PROPERTY_TYPE'] = 52;
                     }
                     if ($set['ID'] == 508)
                     {
                         $filter['PROPERTY_TYPE'] = 53;
                     }
                    $res = CIBlockElement::GetList(array("SORT"=>"ASC"), $filter, false, false, array("ID","NAME"));
                    while($ob = $res->GetNextElement()){
                        $Prices = $ob->GetFields();
                        $arResult[$set['ID']][$Prices["ID"]] = $Prices["NAME"];
                    }
                 }
                 elseif ($set['LINK_IBLOCK_ID'] == 51)
                 {
                    $res = CIBlockElement::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>51,"ACTIVE"=>"Y","PROPERTY_USER"=> $agent_array['id']), false, false, array("ID","NAME"));
                    while($ob = $res->GetNextElement()){
                        $Prices = $ob->GetFields();
                        $arResult[$set['ID']][$Prices["ID"]] = $Prices["NAME"];
                    }
                 }
             }
            
        }
	}

}
$this->IncludeComponentTemplate($componentPage);

?>