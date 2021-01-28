<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");
CModule::IncludeModule('iblock');

$arModes = array('new', 'state', 'cancellation', 'cost');
$arResult['ERRORS'] = array();
$arResult["MESSAGE"] = array();
$arParams["AGENT_ID"] = false;
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
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
$arParams["STATUS_CHANCEL_NOT"] = array(116, 39, 80, 44, 166);

/************загрузка заказов через API************/
if ($arParams['MODE_UPLOAD'] == 'data')
{
	if (isset($_POST['data']))
	{
		$data = $_POST['data'];
		$res = simplexml_load_string($data);
		
		$field_2 = $res->xpath('/dms/auth');
		$field_2 = (array)$field_2;
		$field_2_atr = (array)$field_2[0];
		$mode = $field_2_atr['@attributes']['mode'];
		$ukey = $field_2_atr['@attributes']['ukey'];
		
		// $checksum = $field_2_atr['@attributes']['checksum'];
		
		if (strlen($ukey))
		{
			$ires = CIBlockElement::GetList(Array(), array("IBLOCK_ID"=> 40, "ACTIVE"=>"Y", "PROPERTY_UKEY" => $ukey), false, false,  array("ID"));
			if ($ob = $ires->GetNextElement())
			{
				$arFields = $ob->GetFields();
				$arParams["AGENT_ID"] = $arFields['ID'];
				$rsUser = CUser::GetList(($by="id"), ($order="asc"),array("GROUPS_ID" => array(17), "UF_COMPANY_RU_POST"=> $arParams["AGENT_ID"]), array("SELECT" => array("UF_ROLE", "ID")));
				if ($arUser = $rsUser->Fetch())
				{
					$arResult["USER"] = $arUser["ID"];
					$agent_array = GetCurrentAgent($arResult["USER"]);
				}
				else
				{
					$arResult['ERRORS'][] = GetMessage('ERR_NO_USER');
				}
			}
		}
		else
		{
			$arResult['ERRORS'][] = GetMessage('ERR_NO_KEY');
		}
	}
	else
	{
		$arResult['ERRORS'][] = GetMessage('ERR_EMPTY_REQUEST');
	}
}
elseif ($arParams['MODE_UPLOAD'] == 'file')
{
	$mode = 'new';
}
else
{
	$arResult['ERRORS'][] = GetMessage('ERR_TYPE_UPLOAD');
}

if ($arParams["AGENT_ID"])
{
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
		
	if (in_array($mode, $arModes))
	{
		/**************загрузка новых заказов**************/
		if ($mode == 'new')
		{
			$field_4 = $res->xpath('/dms/orders');
			$field_4 = (array)$field_4;
			$ord_params = $field_4[0];
			$ord_params = (array)$ord_params;
			
			$field_3 = $res->xpath('/dms/orders/order');
			$field_3 = (array)$field_3;
			$arOrders = array();
			$arToSend = array();
			$arResult["ADDED"] = array();
			$i = 0;
			foreach ($field_3 as $order)
			{
				$arPropVr = $arParams["DEFAULT_VALUES"];
				
				$send_to_delivery = ($ord_params['@attributes']['send_to_delivery'] == 'yes') ? true : false;
				$order = (array)$order;
				$contacts = (array)$order['contacts'];
				$delivery = (array)$order['delivery'];
				$dimensions = (array)$order['dimensions'];
				$payment = (array)$order['payment'];
				$description = (array)$order['description'];
				$services = (array)$order['services'];
				
				$max_id_5 = GetMaxIDIN(42, 5, true, 213, $arParams["AGENT_ID"]);
				
				$arPropVr["ID_IN"] = $max_id_5;
				$arPropVr["N_ZAKAZ_IN"] = MakeOrderId($arParams["AGENT_ID"], $max_id_5);
				$arPropVr["N_ZAKAZ"] = iconv('utf-8','windows-1251',$order['@attributes']['inner_id']);
				$arPropVr["CREATOR"] = $arParams["AGENT_ID"];
				$arPropVr["COST_GOODS"] = floatval(str_replace(',',',',$payment['@attributes']['order_cost']));
				$arPropVr["COST_2"] = floatval(str_replace(',',',',$payment['@attributes']['payable']));
				$arPropVr["COST_3"] = floatval(str_replace(',',',',$payment['@attributes']['delivery_cost']));
				$arPropVr["PAY_FOR_REFUSAL"] = ($services['@attributes']['refusal'] == 'yes') ? 1 : 0;
				$arPropVr["WEIGHT"] = floatval(str_replace(',',',',$dimensions['@attributes']['weight']));
				$arPropVr["SIZE_1"] = floatval(str_replace(',',',',$dimensions['@attributes']['length']));
				$arPropVr["SIZE_2"] = floatval(str_replace(',',',',$dimensions['@attributes']['width']));
				$arPropVr["SIZE_3"] = floatval(str_replace(',',',',$dimensions['@attributes']['height']));
				$arPropVr["CONDITIONS"] = ($delivery['@attributes']['self-delivery'] == 'yes') ? 38 : 37;
				$d = DateFF($delivery['@attributes']['date']);
				if (strlen($delivery['@attributes']['start_time']))
				{
					$d .= GetMessage('FROM').$delivery['@attributes']['start_time'];
				}
				if (strlen($delivery['@attributes']['end_time']))
				{
					$d .= GetMessage('TO').$delivery['@attributes']['end_time'];
				}
				if (($delivery['@attributes']['start_time'] == '10:00') && ($delivery['@attributes']['end_time'] == '14:00'))
				{
					$arPropVr["TIME_PERIOD"] = 215;
				}
				if (($delivery['@attributes']['start_time'] == '15:00') && ($delivery['@attributes']['end_time'] == '18:00'))
				{
					$arPropVr["TIME_PERIOD"] = 215;
				}
				$arPropVr["WHEN_TO_DELIVER"] = $d;
				$arPropVr["URGENCY_ORDER"] = ($delivery['@attributes']['urgent'] == 'yes') ? 172 : false;
				$arPropVr["DELIVERY_LEGAL"] = ($services['@attributes']['legal'] == 'yes') ? 1 : 0;
				$arPropVr["RECIPIENT"] = iconv('utf-8','windows-1251',$contacts['@attributes']['name']);
				$arPropVr["PHONE"] = iconv('utf-8','windows-1251',$contacts['@attributes']['phone']);
				$arPropVr["CITY"] = GetCityId(iconv('utf-8','windows-1251', $delivery['@attributes']['city']));
				$arPropVr["ADRESS"] = iconv('utf-8','windows-1251',$delivery['@attributes']['address']);
				$arPropVr["PLACES"] = (intval(str_replace(',',',',$dimensions['@attributes']['places'])) >= 1) ? intval(str_replace(',',',',$dimensions['@attributes']['places'])) : 1;
				$arPropVr["PREFERRED_TIME"] = array("VALUE" => array("TYPE" =>"TEXT","TEXT" => iconv('utf-8','windows-1251',$description[0])));
				$arPropVr["CASH"] = ($services['@attributes']['cheque'] == 'yes') ? 124 : 125;
				$arPropVr["TYPE_PAYMENT"] = ($payment['@attributes']['type'] == 'cash') ? GetMessage('CASH') : iconv('utf-8','windows-1251',$payment['@attributes']['type']);
				
				/******расчеты стоимости доставки и статусов*******/
				$pr = ($delivery['@attributes']['self-delivery'] == 'yes') ? $arResult["PRICE_2"] : $pr = $arResult["PRICE"];
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
					//города нет, сохраняем как черновик, в манифест не добавляем, отправляем сообщение УК
					$send_to_delivery = false;
					$full_city_name = GetFullNameOfCity($arPropVr["CITY"]);
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
				/******расчеты стоимости доставки и статусов*******/
				
				if($send_to_delivery)
				{
					$arPropVr["DATE_TO_DELIVERY"] = date('d.m.Y H:i:s');
					$arPropVr["CONSOLIDATED_MANIFEST"] = 1;
					$arPropVr["STATE"] = 54;
					$arPropVr["STATE_SHORT"] = 78;
				}
				$arOrders[] = $arPropVr;
				$el = new CIBlockElement;
				$arLoadProductArray = array(
					"IBLOCK_SECTION_ID" => false,
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"PROPERTY_VALUES" => $arPropVr,
					"NAME" => GetMessage("ORDER_NAME", array("#NUMBER#" => $arPropVr["N_ZAKAZ_IN"])),
					"ACTIVE" => "Y",
					"MODIFIED_BY" => $arResult["USER"]
				);
				
				if ($ORDER_ID = $el->Add($arLoadProductArray))
				{
					$arResult["ADDED"][$arPropVr['N_ZAKAZ']]['NUMBER'] = $arPropVr['N_ZAKAZ_IN'];
					$arResult["ADDED"][$arPropVr['N_ZAKAZ']]['COST_DELIVERY'] = $have_city["COST"];
					$arResult["ADDED"][$arPropVr['N_ZAKAZ']]['RATE'] = $arPropVr['RATE'];
					if ($send_to_delivery)
					{
						$history_id = AddToHistory($ORDER_ID, $arParams["AGENT_ID"], $arResult["USER"], 54);
						$short_history_id = AddToShortHistory($ORDER_ID, $arResult["USER"], 78);
						$arToSend[$ORDER_ID] = '<a href="http://dms.newpartner.ru/warehouse/index.php?mode=package&id='.$ORDER_ID.'">'.$arPropVr['N_ZAKAZ_IN'].'</a>';
					}
				}
			}
			$arResult["MESSAGE"][] = GetMessage("ADDED", array("#COUNT#" => count($arResult["ADDED"])));
			
			/***********передача заказов на доставку***********/
			$send_to_delivery = ($ord_params['@attributes']['send_to_delivery'] == 'yes') ? true : false;
			$arResult["SEND_TO_DELIVERY"] = array();
			if (($send_to_delivery) && (count($arToSend) > 0))
			{
				$pdfs = array();
				foreach ($arToSend as $id => $v)
				{
					$pack = GetListOfPackeges($agent_array,0,$id);
					$arResult['PACK'] = $pack[0];
					$arResult["PACK"]['GOODS'] = GetGoodsOdPack($arResult['PACK']["ID"]);
					$arResult["PACK"]["SHOP"] = GetCompany($arResult['PACK']['PROPERTY_CREATOR_VALUE']);
					$arResult["PACK"]["AGENT_NAME"] = GetMessage("MSD_NAME");	
					$pdfs[] = MakeTicketPDF($arResult["PACK"]);
					$arResult["SEND_TO_DELIVERY"][$arResult['PACK']['PROPERTY_N_ZAKAZ_VALUE']] = $arResult['PACK']['PROPERTY_N_ZAKAZ_IN_VALUE'];
				}
				$arParamsTxt = array(
					"DATE_SEND" => date('d.m.Y H:i'),
					"AGENT_ID" => $agent_array['id'],
					"AGENT_NAME" => $agent_array['name'],
					"ORDERS" => implode(', ',$sends),
					"LINK_TO_MESS" => ''
				);
				$qw = SendMessageInSystem($arResult["USER"], $agent_array['id'], $agent_array["uk"], GetMessage("RECEIPT_TITLE"), 104, '', '', 164, $arParamsTxt);
				$arParamsTxt["#LINK_TO_MESS#"] = GetMessage("LINK_TO_MESS", array("#ID#" => $qw));
				$send = SendMessageMailNew($agent_array["uk"], $agent_array['id'], 102, 164, $arParamsTxt, $pdfs);
				$arResult["MESSAGE"][] = GetMessage("TODELIVERY", array("#COUNT#" => count($arResult["SEND_TO_DELIVERY"])));
			}
			/***********передача заказов на доставку***********/
		}
		/**************загрузка новых заказов**************/
		
		/*********запрос статуса и истории заказов*********/
		if ($mode == 'state')
		{
			$field_3 = $res->xpath('/dms/orders/order');
			$field_3 = (array)$field_3;
			foreach ($field_3 as $order)
			{
				$order = (array)$order;
				$number = $order['@attributes']['number'];
				$res = CIBlockElement::GetList(array("ID" => "asc"), array("IBLOCK_ID" => 42, "PROPERTY_N_ZAKAZ_IN" => $number, "PROPERTY_CREATOR" => $arParams["AGENT_ID"]), false, false, array("ID", "PROPERTY_STATE_SHORT",'PROPERTY_EXCEPTIONAL_SITUATION'));
				if ($ob = $res->GetNextElement())
				{
					$arFields = $ob->GetFields();
					$arResult["STATES"][$number]['CODE'] = $arFields["PROPERTY_STATE_SHORT_ENUM_ID"];
					$arResult["STATES"][$number]['NAME'] = $arFields["PROPERTY_STATE_SHORT_VALUE"];
					$arResult["STATES"][$number]['EXCEPTIONAL'] = ($arFields["PROPERTY_EXCEPTIONAL_SITUATION_VALUE"] == 1) ? 'yes' : 'no';
					$history = HistoryShortOfPackage($arFields['ID']);
					asort($history);
					$i = 0;
					foreach ($history as $h)
					{
						$arResult["HISTORY"][$number][$i]['DATE'] = $h['DATE_CREATE'];
						$arResult["HISTORY"][$number][$i]['STATE'] = $h['NAME'];
						$arResult["HISTORY"][$number][$i]['COMMENT'] = $h['DETAIL_TEXT'];
						$arResult["HISTORY"][$number][$i]['WHO'] = $h['WHO']['LAST_NAME'].' '.$h['WHO']['NAME'];
						$i++;
					}
				}
			}
		}
		/*********запрос статуса и истории заказов*********/
		
		/***********запрос аннулирования заказа************/
		if ($mode == 'cancellation')
		{
			$field_3 = $res->xpath('/dms/orders/order');
			$field_3 = (array)$field_3;
			foreach ($field_3 as $order)
			{
				$order = (array)$order;
				$number = $order['@attributes']['number'];
				$res = CIBlockElement::GetList(array("ID" => "asc"), array("IBLOCK_ID" => 42, "PROPERTY_N_ZAKAZ_IN" => $number, "PROPERTY_CREATOR" => $arParams["AGENT_ID"]), false, false, array("ID", "PROPERTY_STATE"));
				if ($ob = $res->GetNextElement())
				{
					$arFields = $ob->GetFields();
					if (in_array($arFields["PROPERTY_STATE_VALUE"],$arParams['STATUS_CHANCEL_NOT'])) 
					{
						$arResult["CANCEL"][$number] = "incorrect status";
					}
					else
					{
						$arParamsTxt = array(
							"SHOP_ID" => $agent_array['id'],
							"SHOP_NAME" => $agent_array['name'],
							"ID_PACK" => $arFields['ID'],
							"NUMBER" => $number,
							"DATE_SEND" => date('d.m.Y H:i'),
							"LINK" => ""
						);
						$qw = SendMessageInSystem($arResult["USER"], $agent_array['id'], $agent_array["uk"], GetMessage("INCORRECT_MESSAGE_TO_UK_TITLE"), 164, '', '', 170, $arParamsTxt);
						$arParamsTxt["LINK"] = GetMessage("LINK_TO_MESS", array("#ID#" => $qw));
						$send = SendMessageMailNew($agent_array["uk"], $agent_array['id'], 165, 170, $arParamsTxt);
						$arResult["CANCEL"][$number] = "request sent";
					}
				}
			}
		}
		/***********запрос аннулирования заказа************/
		
		/*************запрос стоимости дотавки*************/
		if ($mode == 'cost')
		{
			$field_3 = $res->xpath('/dms/delivery');
			$field_3 = (array)$field_3[0];
			$delivery_params = $field_3['@attributes'];
			$arPropVr["CITY"] = GetCityId(iconv('utf-8','windows-1251', $delivery_params['city']));
			$arPropVr["URGENCY_ORDER"] = ($delivery_params['urgent'] == 'yes') ? 2 : 1;
			$pr = ($delivery_params['self-delivery'] == 'yes') ? $arResult["PRICE_2"] : $pr = $arResult["PRICE"];
			$arPropVr["WEIGHT"] = floatval(str_replace(',',',',$delivery_params['weight']));
			$arPropVr["SIZE_1"] = floatval(str_replace(',',',',$delivery_params['length']));
			$arPropVr["SIZE_2"] = floatval(str_replace(',',',',$delivery_params['width']));
			$arPropVr["SIZE_3"] = floatval(str_replace(',',',',$delivery_params['height']));
			$have_city = CheckCityToHave(
				$arPropVr["CITY"],
				$pr,
				$arPropVr["WEIGHT"],
				$arPropVr["SIZE_1"],
				$arPropVr["SIZE_2"],
				$arPropVr["SIZE_3"],
				$arPropVr["URGENCY_ORDER"]
			);
			$arResult['DELIVERY']['CITY'] = GetFullNameOfCity($arPropVr["CITY"]);
			if (!$have_city['LOG'])
			{
				$arResult['DELIVERY']['ABILITY'] = 'no';
				
			}
			else
			{
				$arResult['DELIVERY']['ABILITY'] = 'yes';
				$arResult['DELIVERY']['COST'] = $have_city['cost'];
				$arResult['DELIVERY']['TEXT'] = $have_city['TEXT'];
			}
		}
		/*************запрос стоимости дотавки*************/
		
		$arResult["KEY"] = GenericKey($arParams["AGENT_ID"]);
		CIBlockElement::SetPropertyValuesEx($arParams["AGENT_ID"], 40, array('UKEY' => $arResult["KEY"]));
	}
	else
	{
		$arResult['ERRORS'][] = GetMessage('ERR_UNKNOWN_MODE');
	}
}
else
{
	$arResult['ERRORS'][] = GetMessage('ERR_UNKNOWN_USER');
}

$this->IncludeComponentTemplate();