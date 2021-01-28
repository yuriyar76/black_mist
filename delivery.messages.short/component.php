<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

CModule::IncludeModule('iblock');
$u_id = $USER->GetID();
$agent_array = GetCurrentAgent($u_id);
$agent_id = $agent_array['id'];

$arResult["ERRORS"] = $arResult["OTHER"] = $cities_to_add = $cities_in_mess = $actives = $ne_zapolneny = $mail_to = array();

if (intval($agent_id) > 0)
{
	$limit = ($agent_array['type'] == 51) ? false : 6;
	$mess_to = ListOfMasagesPre(0, $agent_id, "", 0, $limit);
	foreach ($mess_to as $mess)
	{
		if (($mess["PROPERTY_TYPE_ENUM_ID"] != 83) && ($mess["ACTIVE"] == "Y"))
		{
			$arResult["MES_TO_SHOT"][] = $mess;
		}
		if ($mess["PROPERTY_TYPE_ENUM_ID"] == 83)
		{
			if (!in_array($mess["DETAIL_TEXT"],$cities_in_mess))
			{
				$cities_in_mess[] = $mess["DETAIL_TEXT"];
			}
			$names[$mess["DETAIL_TEXT"]] = $mess["PROPERTY_COMMENT_VALUE"];
			if ($mess["ACTIVE"] == "Y")
			{
				$actives[] = $mess["DETAIL_TEXT"];
				$mail_to[$mess["DETAIL_TEXT"]][$mess["ID"]] = $mess["PROPERTY_FROM_VALUE"];
			}
		}
	}

	if ($agent_array['type'] == 51)
	{
		$list_of_prices = TheListOfPrices($agent_id);
		if ((is_array($list_of_prices)) && (count($list_of_prices) > 0))
		{
			$cities_in_prices = $list_of_prices[0]["PROPERTY_CITIES_VALUE"];
			foreach ($cities_in_mess as $cit)
			{
				if (!in_array($cit,$cities_in_prices))
				{
					$cities_to_add[] = $cit;
					$arResult["OTHER"][] = '<p class="add_city"><a href="/price-lists/index.php?state=add_city&price_id='.$list_of_prices[0]["ID"].'">Добавьте '.$names[$cit].' в прайс-лист</a></p>';
					if (!in_array($cit,$ne_zapolneny))
					{
						$ne_zapolneny[] = $cit;
					}
				}
			}
		}
		else
		{
			$arResult["OTHER"][] = '<p class="add_price"><a href="/price-lists/index.php?state=create">Создайте новый прайс-лист</a></p>';
		}
		//провепка на заполненность полей 
		foreach ($list_of_prices as $pr)
		{
			$path_to_bd = CFile::GetPath($pr["PROPERTY_FILE_VALUE"]);
			$global_file = $_SERVER["DOCUMENT_ROOT"].$path_to_bd;
			$pr_array = read_price($global_file);
			foreach ($pr_array["CITIES"] as $city_id => $city_name)
			{
				if (!isset($pr_array["ORDERS"][$city_id]))
				{
					$arResult["OTHER"][] = '<p class="add_price"><a href="/price-lists/index.php?state=edit&price_id='.$pr["ID"].'">Заполните значения прайс-листа для '.$city_name.'</a></p>';
					if (!in_array($city_id,$ne_zapolneny))
					{
						$ne_zapolneny[] = $city_id;
					}
				}
			}
		}
		
		foreach ($actives as $v)
		{
			if (!in_array($v,$ne_zapolneny))
			{
				foreach ($mail_to[$v] as $mess_id => $to)
				{
					//сообщаем о заполенности города
					$el = new CIBlockElement;
					$arLoadProductArray = Array(
						"MODIFIED_BY" => $u_id,
						"IBLOCK_ID" => 50,
						"PROPERTY_VALUES"=> array(234 => $agent_id, 235 => $to, 236 => 86, 242=>GetFullNameOfCity($v)),
						"NAME" => "В систему добавлен город"
					);
					$PRODUCT_ID = $el->Add($arLoadProductArray);
					SendMessageMail($to,96,$PRODUCT_ID);
					//делаем сообщение неактивным
					$el = new CIBlockElement;
					$res = $el->Update($mess_id, array("MODIFIED_BY"=>$u_id,"ACTIVE"=>"N"));
				}
			}
		}
	}
}

$this->IncludeComponentTemplate();