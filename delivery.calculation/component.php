<?
if (!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true) die();

$arParams["TYPE_QUERY"] = strlen($arParams["TYPE_QUERY"]) ? $arParams["TYPE_QUERY"] : "POST";
if ($arParams["TYPE_QUERY"] == "POST")
{
	$arReqv = $_POST;
}
elseif ($arParams["TYPE_QUERY"] == "GET")
{
	foreach ($_GET as $k => $v)
	{

		$arReqv[$k] = iconv("utf-8","windows-1251",$v);
	}	
}
else
{
	$arReqv = false;
}




function ReadPriceList($global_file)
{
	$out = array();
	$name0 = iconv("windows-1251", "utf-8", GetMessage("FIELD_NAME_01"));
	$name1 = iconv("windows-1251", "utf-8", GetMessage("FIELD_NAME_02"));
	$name2 = iconv("windows-1251", "utf-8", GetMessage("FIELD_NAME_03"));
	$name4 = iconv("windows-1251", "utf-8", GetMessage("FIELD_NAME_04"));
	$name5 = iconv("windows-1251", "utf-8", GetMessage("FIELD_NAME_05"));
	$name6 = iconv("windows-1251", "utf-8", GetMessage("FIELD_NAME_06"));
	$name7 = iconv("windows-1251", "utf-8", GetMessage("FIELD_NAME_07"));
	$name8 = iconv("windows-1251", "utf-8", GetMessage("FIELD_NAME_08"));
	$name11 = iconv("windows-1251", "utf-8", GetMessage("FIELD_NAME_09"));
	$name12 = iconv("windows-1251", "utf-8", GetMessage("FIELD_NAME_10"));
	$name13 = iconv("windows-1251", "utf-8", GetMessage("FIELD_NAME_11"));
	$text = file_get_contents($global_file);
	$res = simplexml_load_string($text);
	$productNames0 = $res->xpath('/'.$name0.'/'.$name1.'/'.$name2);
	$max_kod = 0;
	for ($i=0; $i<sizeof($productNames0); $i++)
	{
		$pokaz = (array)$productNames0[$i];
		$priznak = $pokaz['@attributes'][$name12];
		$kod = $pokaz['@attributes'][$name11];
		switch ($priznak)
		{
			case '0': $kod = $pokaz['@attributes'][$name11]; $weight[$kod] =  $pokaz['@attributes'][$name13]; break;
			case '1': $start_index = $pokaz['@attributes'][$name11]; $start_value = $pokaz['@attributes'][$name13]; break;
			case '2': $kod = $pokaz['@attributes'][$name11]; $docs[$kod] =  $pokaz['@attributes'][$name13]; break;
			case '3': $min_index = $kod; break;
			case '4': $max_index = $kod; break;
		}
		if ($kod >= $max_kod)
		{
			$max_kod = $kod;
		}
	}
	$out["WEIGHT"] = $weight;
	$out["START_INDEX"] = $start_index;
	$out["START_VALUE"] = $start_value;
	$out["DOCS"] = $docs;
	$out["MIN"] = $min_index;
	$out["MAX"] = $max_index;
	$out["MAX_CODE"] = $max_kod;
	$productNames2 = $res->xpath('/'.$name0.'/'.$name5.'/'.$name6);
	for ($i=0;$i<sizeof($productNames2);$i++)
	{
		$city = (array)$productNames2[$i];
		$ind_city_utf = $city['@attributes'][$name4];
		$city_n = intval(str_replace('%C2%A0','',urlencode($ind_city_utf)));
		$type_n = iconv("utf-8","windows-1251",$city['@attributes'][$name7]);
		$pokaz_n = $city['@attributes'][$name2];
		$summ_n_utf = $city['@attributes'][$name8];
		$summ_n = intval(str_replace('%C2%A0','',urlencode($summ_n_utf)));
		$result[$city_n][$type_n][$pokaz_n] = $summ_n;
	}
	$out["ORDERS"] = $result;
	return $out;		
}

$arResult["CITY_FROM"] = $arParams["CITY_FROM"];
$arResult["ERRORS"] = array();

if (isset($arReqv["calculate"]))
{
	$arResult["CITY_FROM"] = htmlspecialchars($arReqv["city_from"]);
	$arResult["CITY_TO"] = htmlspecialchars($arReqv["city_to"]);
	$arResult["WEIGHT"] = floatval(str_replace(',','.',$arReqv["weight"]));
	$arResult["WEIGHT_CEIL"] = ceil($arResult["WEIGHT"]);
	$arResult["SIZE_1"] = floatval(str_replace(',','.',$arReqv["size_1"]));
	$arResult["SIZE_2"] = floatval(str_replace(',','.',$arReqv["size_2"]));
	$arResult["SIZE_3"] = floatval(str_replace(',','.',$arReqv["size_3"]));
	$arResult["WEIGHT_OB"] = ceil(($arResult["SIZE_1"]*$arResult["SIZE_2"]*$arResult["SIZE_3"])/6000);
	$ob_weight = false;
	if ($arResult["WEIGHT_OB"] > $arResult["WEIGHT_CEIL"])
	{
		$arResult["WEIGHT_CEIL"] = $arResult["WEIGHT_OB"];
		$ob_weight = true;
	}
	if (!strlen($arResult["CITY_FROM"]))
		$arResult["ERRORS"][] = GetMessage("ERROR_01");
	if (!strlen($arResult["CITY_TO"]))
		$arResult["ERRORS"][] = GetMessage("ERROR_02");
	if ($arResult["WEIGHT_CEIL"] <= 0)
		$arResult["ERRORS"][] = GetMessage("ERROR_03");
	if (count($arResult["ERRORS"]) == 0)
	{
		if (!CModule::IncludeModule("iblock") || !CModule::IncludeModule("currency"))
		{
			$arResult["ERRORS"][] = GetMessage("ERROR_04");
		}
		else
		{
			$cityFrom = false;
			$cityTo = false;
			$SectionFromFilter = array("IBLOCK_ID" => $arParams["GEOGRAPHY_IBLOCK_ID"]);
			$ElementFromFilter = array("IBLOCK_ID" => $arParams["GEOGRAPHY_IBLOCK_ID"]);
			
			$arCityFrom = explode(",", $arResult["CITY_FROM"]);
			if (strlen($arCityFrom["2"]))
			{
				$res_0 = CIBlockSection::GetList(array("SORT"=>"ASC"),array("NAME"=>trim($arCityFrom[2]), "IBLOCK_ID" => $arParams["GEOGRAPHY_IBLOCK_ID"], "SECTION_ID" => false),false);
				if($res_0_from = $res_0->GetNext()) 
				{
					$SectionFromFilter["SECTION_ID"] = $res_0_from["ID"];
				}
			}
			if (strlen($arCityFrom["1"]))
			{
				$SectionFromFilter["NAME"] = trim($arCityFrom[1]);
				$res_1 = CIBlockSection::GetList(array("SORT"=>"ASC"), $SectionFromFilter, false);
				if($res_1_from = $res_0->GetNext()) 
				{
					$ElementFromFilter["SECTION_ID"] = $res_1_from["ID"];
				}
			}
			$ElementFromFilter["NAME"] = $arCityFrom["0"];
			$res_2 = CIBlockElement::GetList(array("SORT"=>"ASC"), $ElementFromFilter, false, false, array("ID"));
			if ($ob_2 = $res_2->GetNextElement())
			{
				$arFields = $ob_2->GetFields();
				$cityFrom = $arFields["ID"];
			}
			unset($SectionFromFilter, $ElementFromFilter);
			
			$SectionFromFilter = array("IBLOCK_ID" => $arParams["GEOGRAPHY_IBLOCK_ID"]);
			$ElementFromFilter = array("IBLOCK_ID" => $arParams["GEOGRAPHY_IBLOCK_ID"]);
			$arCityTo = explode(",", $arResult["CITY_TO"]);
			if (strlen($arCityTo["2"]))
			{
				$res_0 = CIBlockSection::GetList(array("SORT" => "ASC"),array("NAME" => trim($arCityTo[2]), "IBLOCK_ID" => $arParams["GEOGRAPHY_IBLOCK_ID"], "SECTION_ID" => false),false);
				if($res_0_from = $res_0->GetNext()) 
				{
					$SectionFromFilter["SECTION_ID"] = $res_0_from["ID"];
				}
			}
			if (strlen($arCityTo["1"]))
			{
				$SectionFromFilter["NAME"] = trim($arCityTo[1]);
				$res_1 = CIBlockSection::GetList(array("SORT" => "ASC"), $SectionFromFilter, false);
				if($res_1_from = $res_0->GetNext()) 
				{
					$ElementFromFilter["SECTION_ID"] = $res_1_from["ID"];
				}
			}
			$ElementFromFilter["NAME"] = $arCityTo["0"];
			$res_2 = CIBlockElement::GetList(array("SORT" => "ASC"), $ElementFromFilter, false, false, array("ID"));
			if ($ob_2 = $res_2->GetNextElement())
			{
				$arFields = $ob_2->GetFields();
				$cityTo = $arFields["ID"];
			}
			
			if ($cityFrom && $cityTo)
			{
				$path = false;
				if ($arParams["USE_SQL"] == "Y")
				{
					$results = $DB->Query(
						"SELECT a.`PATH` FROM `".$arParams["BD_PRICES_NAME"]."` a LEFT JOIN `".$arParams["BD_CITIES_NAME"]."` b  ON a.`ID` = b.`ID_PRICE` 
						WHERE a.`ID_CITY` =".$cityFrom." AND a.`ACTIVE` = 'Y' AND b.`ID_CITY` = ".$cityTo." ORDER BY a.`SORT` LIMIT 1"
					);
					if ($row = $results->Fetch())
					{
						$path = $row['PATH'];
					}
					if (!$path)
					{
						$results = $DB->Query(
							"SELECT a.`PATH` FROM `".$arParams["BD_PRICES_NAME"]."` a LEFT JOIN `".$arParams["BD_CITIES_NAME"]."` b  ON a.`ID` = b.`ID_PRICE` 
							WHERE a.`ID_CITY` =".$cityTo." AND a.`ACTIVE` = 'Y' AND b.`ID_CITY` = ".$cityFrom." ORDER BY a.`SORT` LIMIT 1"
						);
						if ($row = $results->Fetch())
						{
							$path = $row['PATH'];
						}
						if ($path)
						{
							$prom = $cityFrom;
							$cityFrom = $cityTo;
							$cityTo = $prom;
						}
					}
				}
				if ($path)
				{
					$file = $_SERVER['DOCUMENT_ROOT'].$path;
					if (is_file($file))
					{
						$html = ReadPriceList($file);
						$arOrders = $html["ORDERS"][$cityTo];
						arsort($html["DOCS"]);
						$arResult["TypesDelivery"] = array();
						$i = 0;
						foreach ($arOrders as $k => $v)
						{
							$arResult["TypesDelivery"][$i]["MIN"] =  $arOrders[$k][$html["MIN"]];
							$arResult["TypesDelivery"][$i]["MAX"] =  $arOrders[$k][$html["MAX"]];
							$index_dd = false;
							foreach ($html["DOCS"] as $kk => $vv)
							{
								if ($arResult["WEIGHT_CEIL"] <= $vv)
								{
									$index_dd = $kk;
									$arResult["TypesDelivery"][$i]["SUMM"] = $arOrders[$k][$index_dd];
								}
							}
							if (!$index_dd)
							{
								if ($arResult["WEIGHT_CEIL"] <= $html["START_VALUE"])
								{
									$arResult["TypesDelivery"][$i]["SUMM"] = $arOrders[$k][$html["START_INDEX"]];
								}
								else
								{
									foreach ($html["WEIGHT"] as $kk => $vv)
									{
										if ($arResult["WEIGHT_CEIL"] >= $vv)
										{
											$index_ww = $kk;
										}
									}
									$arResult["TypesDelivery"][$i]["SUMM"] = $arOrders[$k][$html["START_INDEX"]] + ($arResult["WEIGHT_CEIL"] - $html["START_VALUE"])*$arOrders[$k][$index_ww];
								}
							}
							if ($ob_weight)
							{
								$arResult["TypesDelivery"][$i]["OB_WEIGHT"] = "Y";
							}
							else
							{
								$arResult["TypesDelivery"][$i]["OB_WEIGHT"] = "N";
							}
							$arResult["TypesDelivery"][$i]["WEIGHT"] = $arResult["WEIGHT_CEIL"];
							$arResult["TypesDelivery"][$i]["TYPE_DELIVERY"] = $k;
							$n = $arResult["TypesDelivery"][$i]["MAX"];
							$key = ($n%10 == 1 && $n%100 != 11 ? 0 : ($n%10 >= 2 && $n%10 <= 4 && ($n%100 < 10 || $n%100 >= 20) ? 1 : 2));
							$_plural_days = array(GetMessage("DAYS_1"), GetMessage("DAYS_2"), GetMessage("DAYS_3"));
							$arResult["TypesDelivery"][$i]["TIME"] = ($arResult["TypesDelivery"][$i]["MIN"] == $arResult["TypesDelivery"][$i]["MAX"]) ? 
								$arResult["TypesDelivery"][$i]["MAX"] : $arResult["TypesDelivery"][$i]["MIN"]."-".$arResult["TypesDelivery"][$i]["MAX"];
							$arResult["TypesDelivery"][$i]["TIME"] .= "&nbsp;".$_plural_days[$key];
							$i++;
						}
						if ((count($arResult["TypesDelivery"]) == 1) || (!strlen($arReqv["type_delivery"])))
						{
							$arResult["RESULT"] = $arResult["TypesDelivery"][0];
							$arResult["INDEX"] = 0;
						}
						if ((count($arResult["TypesDelivery"]) > 1) && (strlen($arReqv["type_delivery"])))
						{
							$arResult["RESULT"] = $arResult["TypesDelivery"][$arReqv["type_delivery"]];
							$arResult["INDEX"] = $arReqv["type_delivery"];
						}
						$arResult["TYPES"] = array();
						foreach ($arResult["TypesDelivery"] as $k => $v)
						{
							$arResult["TYPES"][$k] = $v["TYPE_DELIVERY"]."(<strong>".$v["TIME"].", ".CurrencyFormat($v["SUMM"],"RUU")."</strong>)";
						}
						unset($arResult["TypesDelivery"]);
						
					}
					else
					{
						$arResult["ERRORS"][] = GetMessage("ERROR_05");
					}
				}
				else
				{
					$arResult["ERRORS"][] = GetMessage("ERROR_05");
				}
			}
			else
			{
				if (!$cityFrom)
				{
					$arResult["ERRORS"][] = GetMessage("ERROR_06");
				}
				if (!$cityTo)
				{
					$arResult["ERRORS"][] = GetMessage("ERROR_07");
				}
			}
		}
	}
}

$this->IncludeComponentTemplate();