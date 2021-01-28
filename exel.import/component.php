<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");
$oldini = ini_get('mbstring.func_overload');

if ($oldini > 0)
	ini_set('mbstring.func_overload', 0);
	
$arParams["USER_ID"] = $USER->GetID();
$agent_array = GetCurrentAgent($arParams["USER_ID"]);
$arParams["AGENT_ID"] = $agent_array['id'];

$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
$arParams["TMP_PATH"] = ($arParams["TMP_PATH"]=="" ? "/upload/" : $arParams["TMP_PATH"] ); 
/*
$arParams["PROP_TAXONOMY"] = array(
    "A" => "N_ZAKAZ",
    "B" => "",
    "C" => "",
    "D" => "",
    "E" => "",
    "F" => "RECIPIENT",
    "G" => "CITY",
    "H" => "ADRESS",
    "I" => "PHONE",
    "J" => "PREFERRED_TIME",
    "K" => "PLACES",
    "L" => "WEIGHT",
    "M" => "",
    "N" => "COST_2",
    "O" => ""
);
*/
$arParams["PROP_TAXONOMY"] = array(
	"A" => "N_ZAKAZ",
	"B" => "RECIPIENT",
	"C" => "PHONE",
	"D" => "CITY",
	"E" => "CONDITIONS",
	"F" => "ADRESS",
	"G" => "WHEN_TO_DELIVER",
	"H" => "URGENCY_ORDER",
	"I" => "DELIVERY_LEGAL",
	"J" => "PREFERRED_TIME",
	"K" => "WEIGHT",
	"L" => "SIZE_1",
	"M" => "SIZE_2",
	"N" => "SIZE_3",
	"O" => "PLACES",
	"P" => "COST_GOODS",
	"Q" => "COST_3",
	"R" => "COST_2",
	"S" => "CASH",
	"T" => "PAY_FOR_REFUSAL"
);
$dir = $_SERVER["DOCUMENT_ROOT"].$arParams["TMP_PATH"];
$arParams["PROPERTY_CODE"] = is_array($arParams["PROPERTY_CODE"]) ? $arParams["PROPERTY_CODE"] : array("N_ZAKAZ", "COST_2", "WEIGHT", "RECIPIENT", "PHONE", "CITY", "ADRESS", "PLACES", "PREFERRED_TIME");
$arParams["TYPE_CITIES"] = is_array($arParams["TYPE_CITIES"]) ? $arParams["TYPE_CITIES"] : array(GetMessage("CITY_TYPE_1"), GetMessage("CITY_TYPE_2"));

$arParams["DEFAULT_VALUES"] = array(
	"ID_IN" => "",
	"N_ZAKAZ_IN" => "",
	"N_ZAKAZ" => "",
	"CREATOR" => $arParams["AGENT_ID"],
	"COST_GOODS" => 0,
	"COST_2" => 0,
	"COST_3" => 0,
	"PAY_FOR_REFUSAL" => 0,
	"COST_1" => 0,
	"SUMM_SHOP" => 0,
	"SUMM_SHOP_ZABOR" => 0,
	"RATE" => 0,
	"SUMM_ISSUE" => 0,
	"WEIGHT" => 0,
	"SIZE_1" => 0,
	"SIZE_2" => 0,
	"SIZE_3" => 0,
	"CONDITIONS" => false,
	"WHEN_TO_DELIVER" => '',
	"TIME_PERIOD" => 217,
	"URGENCY_ORDER" => false,
	"DELIVERY_LEGAL" => 0,
	"RECIPIENT" => '',
	"PHONE" => '',
	"CITY" => false,
	"ADRESS" => '',
	"STATE" => 80,
	"STATE_SHORT" => 81,
	"PVZ" => false,
	"MANIFEST" => false,
	"PLACES" => 1,
	"DATE_TO_DELIVERY" => false,
	"COURIER" => false,
	"PREFERRED_TIME" => array("VALUE" => array("TYPE" => "TEXT", "TEXT" => "")),
	"COMMENTS_COURIER" => array("VALUE" => array("TYPE" => "TEXT", "TEXT" => "")),
	"DATE_DELIVERY" => false,
	"ACCOUNTING" => 60,
	"SUMM_AGENT" => 0,
	"RATE_AGENT" => 0,
	"ACCOUNTING_AGENT" => 91,
	"CASH" => false,
	"REPORT" => false,
	"TYPE_PAYMENT" => GetMessage("CASH"),
	"TAKE_PROVIDER" => false,
	"TAKE_DATE" => false,
	"TAKE_COMMENT" => array("VALUE" => array("TYPE" => "TEXT", "TEXT" => "")),
	"CALL_COURIER" => false,
	"CONSOLIDATED_MANIFEST" => 0,
	"EXCEPTIONAL_SITUATION" => 0,
	"RETURN" => 0,
	"COST_RETURN" => 0
);



if(!is_dir($dir))
{
	mkdir($dir, 0777, true);
}

$name0 = iconv('windows-1251','utf-8',GetMessage("UPLOAD_FIELD_0"));
$name1 = iconv('windows-1251','utf-8',GetMessage("UPLOAD_FIELD_1"));
$name2 = iconv('windows-1251','utf-8',GetMessage("UPLOAD_FIELD_2"));
$name3 = iconv('windows-1251','utf-8',GetMessage("UPLOAD_FIELD_3"));
$name4 = iconv('windows-1251','utf-8',GetMessage("UPLOAD_FIELD_4"));
$name5 = iconv('windows-1251','utf-8',GetMessage("UPLOAD_FIELD_5"));
$name6 = iconv('windows-1251','utf-8',GetMessage("UPLOAD_FIELD_6"));
$name7 = iconv('windows-1251','utf-8',GetMessage("UPLOAD_FIELD_7"));
$name8 = iconv('windows-1251','utf-8',GetMessage("UPLOAD_FIELD_8"));
$name9 = iconv('windows-1251','utf-8',GetMessage("UPLOAD_FIELD_9"));
$name10 = iconv('windows-1251','utf-8',GetMessage("UPLOAD_FIELD_10"));
$name11 = iconv('windows-1251','utf-8',GetMessage("UPLOAD_FIELD_11"));
$name12 = iconv('windows-1251','utf-8',GetMessage("UPLOAD_FIELD_12"));
$name13 = iconv('windows-1251','utf-8',GetMessage("UPLOAD_FIELD_13"));
$name14 = iconv('windows-1251','utf-8',GetMessage("UPLOAD_FIELD_14"));
$name15 = iconv('windows-1251','utf-8',GetMessage("UPLOAD_FIELD_15"));
$name16 = iconv('windows-1251','utf-8',GetMessage("UPLOAD_FIELD_16"));
$name17 = iconv('windows-1251','utf-8',GetMessage("UPLOAD_FIELD_17"));
$name18 = iconv('windows-1251','utf-8',GetMessage("UPLOAD_FIELD_18"));
$name19 = iconv('windows-1251','utf-8',GetMessage("UPLOAD_FIELD_19"));
$name20 = iconv('windows-1251','utf-8',GetMessage("UPLOAD_FIELD_20"));
$name21 = iconv('windows-1251','utf-8',GetMessage("UPLOAD_FIELD_21"));
$name22 = iconv('windows-1251','utf-8',GetMessage("UPLOAD_FIELD_22"));

$arResult["LIMIT_UPLOAD"] = GetSettingValue(255);

$arResult = array();
$arResult["MENU"] = array(
	array(
		"LINK" => "/warehouse/orders.php",
		"TITLE" => "Загрузка заказов",
		"ACTIVE" => "Y"
	),
	array(
		"LINK" => "/warehouse/index.php?mode=\"list\"",
		"TITLE" => "Список заказов",
		"ACTIVE" => "N"
	)
);
$arResult["RATE"] = WhatIsRate($arParams["AGENT_ID"]);
$arResult["PRICE"] = WhatIsPrice($arParams["AGENT_ID"]);
$arResult["PRICE_2"] = WhatIsPrice($arParams["AGENT_ID"], 2);
$arResult["CONDITIONS_AGENT"] = 214;
$db_props = CIBlockElement::GetProperty(40, $arParams["AGENT_ID"], array("sort" => "asc"), array("CODE" => "CONDITIONS"));
if ($ar_props = $db_props->Fetch())
	$arResult["CONDITIONS_AGENT"] =  $ar_props["VALUE"];
$db_props = CIBlockElement::GetProperty(40, $arParams["AGENT_ID"], array("sort" => "asc"), array("CODE" => "UK"));
if ($ar_props = $db_props->Fetch())
	$arParams["UK_ID"] =  $ar_props["VALUE"];
$arResult["ADDED"] = array();

if (isset($_POST['upload']))
{
	if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
	{
		$_POST = array();
		$arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
	}
	else
	{
		$_SESSION[$_POST['key_session']] = $_POST['rand'];
		if (CModule::IncludeModule("echogroup.exelimport") && CModule::IncludeModule("iblock"))
		{
			$arIMAGE = $_FILES["IMAGE_ID"];
			$arIMAGE["MODULE_ID"] = "echogroup.exelimport";
		
			if (strlen($arIMAGE["name"])>0) 
			{
				$res = CFile::CheckFile($arIMAGE, 0, false, "xls, xml");
				if (strlen($res)>0)
				{
					$arResult["ERRORS"][] = $res;
				}
				else
				{	
					$fid = CFile::SaveFile($arIMAGE, "echogroup.exelimport");
					$arResult["FILE_ID"] = $fid;
				}
			}
		
			if($arResult["FILE_ID"] > 0) 
			{
				$arResult["FILE_VALUES"] = CFile::GetFileArray($arResult["FILE_ID"]);
				$arResult["FILE_PATH"] = $arResult["FILE_VALUES"]["SRC"];
				$arResult["CONTENT_TYPE"] = $arResult["FILE_VALUES"]["CONTENT_TYPE"];
			}
			else
			{
				$arResult["ERRORS"][] = GetMessage("NO_FILE"); 
				unset($_REQUEST['start_import']);
				unset($_REQUEST['config_import']);
			}
		
			if (isset($_REQUEST['start_import']))
			{
				if ($arResult["CONTENT_TYPE"] == "application/vnd.ms-excel")
				{
					$objPHPExcel = PHPExcel_IOFactory::load($_SERVER["DOCUMENT_ROOT"].$arResult["FILE_PATH"]);
					$cell_list = $objPHPExcel->getActiveSheet()->getCellCollection();
					$index_start = false;
					foreach ($cell_list as $cell)
					{
						$letter=preg_replace("!([0-9])!","",$cell);
						$num=preg_replace("!([A-Z])!","",$cell);
						$val = iconv(mb_detect_encoding($objPHPExcel->getActiveSheet()->getCell($cell)->getValue()),"WINDOWS-1251",$objPHPExcel->getActiveSheet()->getCell($cell)->getValue());
						if ($val == GetMessage("FIRST_FIELD"))
						{
							// $index_start = $num+2;
							$index_start = $num+1;
						}
						if (($num >= $index_start) && $index_start)
						{
							$cell_data[$num][$letter] = $val;
						}
					}
					$arResult["DATA"] = $cell_data;
					
					/*
					$arProp = CIBlockProperty::GetList(array("sort"=>"asc"),array("IBLOCK_ID"=>$arParams["IBLOCK_ID"],"CHECK_PERMISSIONS"=>"N"));
					while($arProperty=$arProp->Fetch())
					{
						if (in_array($arProperty["CODE"], $arParams["PROPERTY_CODE"]))
						{
							$arResult["PROP_LIST"][$arProperty["CODE"]]=$arProperty;
							$arResult["SELECT"][$arProperty["CODE"]] = $arProperty["NAME"];
						}
			
					}
					*/
					
					if (!empty($arResult["DATA"]))
					{
						foreach($arResult["DATA"] as $k => $v)
						{
							$arPropVr = $arParams["DEFAULT_VALUES"];
							foreach($v as $key => $val)
							{
								$code = $arParams["PROP_TAXONOMY"][$key];
								if (strlen($code))
								{
									if ($code == "CITY")
									{
										foreach ($arParams["TYPE_CITIES"] as $type)
										{
											$val = str_replace($type,'',$val);
										}
										$city_id = GetCityId(trim($val));
										if ($city_id > 0)
										{
											$arPropVr[$code] = $city_id;
										}
									}
									elseif ($code == "PREFERRED_TIME")
									{
										$arPropVr[$code]["VALUE"]["TEXT"] = $val;
									}
									elseif ($code == "CONDITIONS")
									{
										$arPropVr[$code] = ($val == GetMessage("PICKUP")) ? 38 : 37;
									}
									elseif ($code == "URGENCY_ORDER")
									{
										$arPropVr[$code] = ($val == GetMessage("YES")) ? 172 : false;
									}
									elseif ($code == "DELIVERY_LEGAL")
									{
										$arPropVr[$code] = ($val == GetMessage("YES")) ? 1 : 0;
									}
									elseif ($code == "CASH")
									{
										$arPropVr[$code] = ($val == GetMessage("YES")) ? 124 : 125;
									}
									elseif ($code == "PAY_FOR_REFUSAL")
									{
										$arPropVr[$code] = ($val == GetMessage("YES")) ? 1 : 0;
									}
									elseif ($code == "WHEN_TO_DELIVER")
									{
										$deliv_array = DateFFReverse($val);
										$arPropVr[$code] = $val;
										if (($deliv_array['time_1'] == '10:00') && ($deliv_array['time_2'] == '14:00'))
										{
											$arPropVr["TIME_PERIOD"] = 215;
										}
										if (($deliv_array['time_1'] == '15:00') && ($deliv_array['time_2'] == '18:00'))
										{
											$arPropVr["TIME_PERIOD"] = 216;
										}
									}
									else
									{
										$arPropVr[$code] = $val;
									}
									// $arPropVr["COST_GOODS"] = $arPropVr["COST_2"];
								}
							}
							if ($arPropVr["CITY"])
							{
								if ($arPropVr['CONDITIONS'] == 38)
								{
									$pr = $arResult["PRICE_2"];
									$rate_type = "PERSENT_2";
								}
								else
								{
									$pr = $arResult["PRICE"];
									$rate_type = "PERSENT_1";
								}
								$have_city = CheckCityToHave(
									$arPropVr["CITY"],
									$pr,
									$arPropVr["WEIGHT"],
									$arPropVr["SIZE_1"],
									$arPropVr["SIZE_2"],
									$arPropVr["SIZE_3"],
									($arPropVr["URGENCY_ORDER"]) ? 2 : 1,
									0
								);
								if(!$have_city["LOG"])
								{
									$full_city_name = GetFullNameOfCity($arPropVr["CITY"]);
									$qw = SendMessageInSystem($arParams["USER_ID"], $arParams["AGENT_ID"], $arParams["UK_ID"], GetMessage("ADD_CITY_TITLE"), 83, $arPropVr["CITY"], $full_city_name);
									SendMessageMailNew($arParams["UK_ID"] , $arParams["AGENT_ID"], 97, 165, array("CITY"=>$full_city_name,"ID_MESS"=>$qw));
								}
								else
								{
									$arPropVr["STATE"] = 39;
									$arPropVr["STATE_SHORT"] = 66;
									$arPropVr["SUMM_SHOP"] = $have_city["COST"];
									if ($arResult["CONDITIONS_AGENT"] == 213)
									{
										$rate_index = ($arPropVr["CASH"] == 124) ? $have_city['PERSENT_1'] : $have_city['PERSENT_2'];
										$arPropVr["RATE"] = round(($arResult["RATE"][$rate_index]*$arPropVr["COST_2"])/100,2);
									}
									elseif ($arResult["CONDITIONS_AGENT"] == 214)
									{
										if ($arPropVr["COST_2"] > 0)
										{
											$rate_index = $have_city['PERSENT_1'];
											$arPropVr["RATE"] = round(($arResult["RATE"][$rate_index]*$arPropVr["COST_2"])/100,2);
										}
										else
										{
											$rate_index = $have_city['PERSENT_2'];
											$arPropVr["RATE"] = round(($arResult["RATE"][$rate_index]*$arPropVr["COST_GOODS"])/100,2);
										}
									}
									else
									{
									}
								}
							}
							$arPropVr["ID_IN"] = GetMaxIDIN($arParams["IBLOCK_ID"],5,true,213,$arParams["AGENT_ID"]);
							$arPropVr["N_ZAKAZ_IN"] = MakeOrderId($arParams["AGENT_ID"], $max_id_5);
							$el = new CIBlockElement;
							$arLoadProductArray = array(
								"IBLOCK_SECTION_ID" => false,
								"IBLOCK_ID" => $arParams["IBLOCK_ID"],
								"PROPERTY_VALUES" => $arPropVr,
								"NAME" => GetMessage("ORDER_NAME", array("#NUMBER#" => $arPropVr["N_ZAKAZ_IN"])),
								"ACTIVE" => "Y"
							);
							
							if ($PRODUCT_ID = $el->Add($arLoadProductArray))
							{
								$arResult["ADDED"][] = $PRODUCT_ID;
							}
							
						}
						$arResult["MESSAGE"][] = GetMessage("ADDED", array("#COUNT#" => count($arResult["ADDED"])));
					}
				}
				elseif ($arResult["CONTENT_TYPE"] == "text/xml")
				{
					$text = file_get_contents($_SERVER["DOCUMENT_ROOT"].$arResult["FILE_PATH"]);
					$res = simplexml_load_string($text);
					$orders = $res->xpath('/'.$name0.'/'.$name1);
					$global_orders = array();
					$i = 0;
					foreach ($orders as $order_x)
					{
						$arPropVr = $arParams["DEFAULT_VALUES"];
						$order = (array)$order_x[0];
						$arPropVr["N_ZAKAZ"] = iconv('utf-8','windows-1251',$order[$name2]);
						$arPropVr["COST_2"] = floatval(str_replace(',','.',$order[$name3]));
						// $arPropVr["COST_1"] = (floatval(str_replace(',','.',$order[$name5])) > $arPropVr["COST_2"]) ? $arPropVr["COST_2"] : floatval(str_replace(',','.',$order[$name5]));
						$arPropVr["COST_3"] = floatval(str_replace(',','.',$order[$name4]));
						$arPropVr["WEIGHT"] = floatval(str_replace(',','.',$order[$name6]));
						$arPropVr["PLACES"] = (intval(str_replace(',','.',$order[$name7])) >= 1) ? intval(str_replace(',','.',$order[$name7])) : 1;
						$arPropVr["SIZE_1"] = floatval(str_replace(',','.',$order[$name15]));
						$arPropVr["SIZE_2"] = floatval(str_replace(',','.',$order[$name16]));
						$arPropVr["SIZE_3"] = floatval(str_replace(',','.',$order[$name17]));				
						$arPropVr["COST_GOODS"] = floatval(str_replace(',','.',$order[$name18]));
						$contr = (array)$order[$name8];
						$arPropVr["RECIPIENT"] = iconv('utf-8','windows-1251',$contr[$name9]);
						$arPropVr["PHONE"] = iconv('utf-8','windows-1251',$contr[$name10]);
						$dost = (array)$order[$name11];
						$type_of_dost = iconv('utf-8','windows-1251',$dost[$name12]);
						$arPropVr["CITY"] = intval(iconv('utf-8','windows-1251',$dost[$name13]));
		
						if (iconv('utf-8','windows-1251',$dost[$name21]) == GetMessage("YES"))
						{
							$arPropVr["URGENCY_ORDER"] = 172;
						}
						$arPropVr["WHEN_TO_DELIVER"] = iconv('utf-8','windows-1251',$dost[$name22]);
						$arPropVr["ADRESS"] = iconv('utf-8','windows-1251',$dost[$name14]);
						if (!strlen($arPropVr["ADRESS"]) && ($type_of_dost != GetMessage("PICKUP")))
						{
							$type_of_dost = GetMessage("PICKUP");
						}
						if ($type_of_dost == GetMessage("PICKUP"))
						{
							$arPropVr["CONDITIONS"] = 38;
							$pr = $arResult["PRICE_2"];
						}
						else
						{
							$pr = $arResult["PRICE"];
						}
		
						$arPropVr["PREFERRED_TIME"] = array("VALUE" => array ("TEXT" => iconv('utf-8','windows-1251',$dost[$name20]), "TYPE" => "text"));
						$cash = iconv('utf-8','windows-1251',$order[$name19]);
						$have_city = CheckCityToHave(
							$arPropVr["CITY"],
							$pr,
							$arPropVr["WEIGHT"],
							$arPropVr["SIZE_1"],
							$arPropVr["SIZE_2"],
							$arPropVr["SIZE_3"],
							($arPropVr["URGENCY_ORDER"]) ? 2 : 1
						);
						if (!$have_city["LOG"])
						{
							$full_city_name = GetFullNameOfCity($arPropVr["CITY"]);
							$qw = SendMessageInSystem($arParams["USER_ID"], $arParams["AGENT_ID"], $arParams["UK_ID"], GetMessage("ADD_CITY_TITLE"), 83, $arPropVr["CITY"], $full_city_name);
							SendMessageMailNew($arParams["UK_ID"] , $arParams["AGENT_ID"], 97, 165, array("CITY"=>$full_city_name, "ID_MESS"=>$qw));
						}
						else
						{
							$rate_index = $have_city['PERSENT_1'];
							if ($cash == GetMessage("NO"))
							{
								$arPropVr["CASH"] = 125;
								$rate_index = $have_city['PERSENT_2'];
							}
							$arPropVr["STATE"] = 39;
							$arPropVr["STATE_SHORT"] = 66;
							$arPropVr["SUMM_SHOP"] = $have_city["COST"];
							if ($arResult["CONDITIONS_AGENT"] == 213)
							{
								$rate_index = ($arPropVr["CASH"] == 124) ? $have_city['PERSENT_1'] : $have_city['PERSENT_2'];
								$arPropVr["RATE"] = round(($arResult["RATE"][$rate_index]*$arPropVr["COST_2"])/100,2);
							}
							elseif ($arResult["CONDITIONS_AGENT"] == 214)
							{
								if ($arPropVr["COST_2"] > 0)
								{
									$rate_index = $have_city['PERSENT_1'];
									$arPropVr["RATE"] = round(($arResult["RATE"][$rate_index]*$arPropVr["COST_2"])/100,2);
								}
								else
								{
									$rate_index = $have_city['PERSENT_2'];
									$arPropVr["RATE"] = round(($arResult["RATE"][$rate_index]*$arPropVr["COST_GOODS"])/100,2);
								}
							}
							else
							{
							}
						}
						$arPropVr["ID_IN"] = GetMaxIDIN($arParams["IBLOCK_ID"], 5, true, 213, $arParams["AGENT_ID"]);
						$arPropVr["N_ZAKAZ_IN"] = MakeOrderId($arParams["AGENT_ID"], $max_id_5);
						$el = new CIBlockElement;
						$arLoadProductArray = array(
							"IBLOCK_SECTION_ID" => false,
							"IBLOCK_ID" => $arParams["IBLOCK_ID"],
							"PROPERTY_VALUES" => $arPropVr,
							"NAME" => GetMessage("ORDER_NAME", array("#NUMBER#" => $arPropVr["N_ZAKAZ_IN"])),
							"ACTIVE" => "Y"
						);
							
						if ($PRODUCT_ID = $el->Add($arLoadProductArray))
						{
							$arResult["ADDED"][] = $PRODUCT_ID;
						}
					}
					$arResult["MESSAGE"][] = GetMessage("ADDED", array("#COUNT#" => count($arResult["ADDED"])));
				}
				else
				{
					$arResult["ERRORS"][] = GetMessage("WRONG_TYPE");
				}
			}
		}
	}
}
if ($oldini>0)
{
	ini_set('mbstring.func_overload', $oldini);
}

$this->IncludeComponentTemplate();
?>