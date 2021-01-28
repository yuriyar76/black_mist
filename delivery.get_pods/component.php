<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
CModule::IncludeModule('iblock');
$arResult['NUMBERS'] = '';
$arResult['AR_NUMBERS'] = array();
$arResult['TYTLE'] = 'Проверка трекинга';
$arResult['HIDE_EVENTS'] = array('Задержка рейса','Задержка авиарейса');
$arResult['UK'] = 2197189;
$arResult['TIME_CORRECTION'] = false;
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

if ($arParams['SET_TITLE'] == 'Y')
{
	$APPLICATION->SetTitle($arResult['TYTLE']);
}
function get_timezone_offset($remote_tz, $origin_tz = 'Europe/Moscow') {
    $origin_dtz = new DateTimeZone($origin_tz);
    $remote_dtz = new DateTimeZone($remote_tz);
    $origin_dt = new DateTime("now", $origin_dtz);
    $remote_dt = new DateTime("now", $remote_dtz);
    $offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
    return $offset;
}
if ($USER->IsAuthorized())
{
	$rsUser = CUser::GetByID($USER->GetID());
	$arUser = $rsUser->Fetch();
	if (strlen($arUser['TIME_ZONE'])) 
	{
		$arResult['TIME_OFFSET'] = get_timezone_offset($arUser['TIME_ZONE']);
		$arResult['TIME_CORRECTION'] = true;
	}
}
if (strlen(trim($_REQUEST['f001'])))
{
	$nn = trim($_REQUEST['f001']);
	$ar_nn = explode(',',$nn);
	$ar_nn = array_slice($ar_nn, 0, 10);
	foreach ($ar_nn as $n)
	{
		$codeutf = trim($n);
		$coding = mb_detect_encoding($codeutf);
		if ($coding != 'UTF-8')
		{
			$arResult['AR_NUMBERS'][] = iconv('windows-1251','utf-8', $codeutf);
		}
		else
		{
			$arResult['AR_NUMBERS'][] = $codeutf;
		}
		//$arResult['AR_NUMBERS'][] = trim($n);
	}
	$arResult['NUMBERS'] = implode(',',$arResult['AR_NUMBERS']);
}
/*
if ($USER->IsAdmin())
{
	echo $arResult['NUMBERS'].'<br>';
}
*/
// $nakls_UTF = iconv('windows-1251','utf-8', $arResult['NUMBERS']);

$nakls_UTF = $arResult['NUMBERS'];

$arParams['SHOW_SEC'] = ($_REQUEST['show_sec'] == 'Y') ? true : false;
$arParams['INN'] = '7700000001';

if (strlen($arResult['NUMBERS']))
{
	if ($_REQUEST['NOT1C'] != 'Y')
	{
		$currentip = GetSettingValue(683, false, $arResult['UK']);
		$currentlink = GetSettingValue(704, false, $arResult['UK']);
		$currentport = intval(GetSettingValue(761, false, $arResult["UK"]));
		$login1c = GetSettingValue(705, false, $arResult['UK']);
		$pass1c = GetSettingValue(706, false, $arResult['UK']);
		if ($currentport > 0) {
			$url = "http://".$currentip.':'.$currentport.$currentlink;
			$client = new SoapClient($url, array('login' => $login1c, 'password' => $pass1c, 'proxy_host' => $currentip, 'proxy_port' => $currentport, 'exceptions' => false));
		}
		else {
			$url = "http://".$currentip.$currentlink;
			$client = new SoapClient($url, array('login' => $login1c, 'password' => $pass1c,'exceptions' => false));
		}
		$result = $client->GetPods(array('NumDocs' => $nakls_UTF));
		$mResult = $result->return;
		$obj = json_decode($mResult, true);
		
		$arRes = array();
		foreach ($obj as $k => $v)
		{
			$k_tr = iconv('utf-8', 'windows-1251', $k);
			if (is_array($v))
			{
				foreach ($v as $kk => $vv)
				{
					$kk_tr = iconv('utf-8', 'windows-1251', $kk);
					if (is_array($vv))
					{
						foreach ($vv as $kkk => $vvv)
						{
							$kkk_tr = iconv('utf-8', 'windows-1251', $kkk);
							if (is_array($vvv))
							{
								foreach ($vvv as $kkkk => $vvvv)
								{
									$kkkk_tr = iconv('utf-8', 'windows-1251', $kkkk);
									if (is_array($vvvv))
									{
										foreach ($vvvv as $kkkkk => $vvvvv)
										{
											$kkkkk_tr = iconv('utf-8', 'windows-1251', $kkkkk);
											$arRes[$k_tr][$kk_tr][$kkk_tr][$kkkk_tr][$kkkkk_tr] = trim(iconv('utf-8', 'windows-1251', $vvvvv));
										}
									}
									else
									{
										$arRes[$k_tr][$kk_tr][$kkk_tr][$kkkk_tr] = trim(iconv('utf-8', 'windows-1251', $vvvv));
									}
								}
							}
							else
							{
								$arRes[$k_tr][$kk_tr][$kkk_tr] = trim(iconv('utf-8', 'windows-1251', $vvv));
							}
						}
					}
					else
					{
						$arRes[$k_tr][$kk_tr] = trim(iconv('utf-8', 'windows-1251', $vv));
					}
				}
			}
			else
			{
				$arRes[$k_tr] = trim(iconv('utf-8', 'windows-1251', $v));
			}
		}
	}
	
	$arResult['YET_NAKLS'] = array();
	$arResult['ALL_EVENTS'] = array();
	$arResult['DATES_NAKLS'] = array();
	$arResult['DATES_NAKLS_SORT'] = array();

	foreach ($arRes['Documents'] as $k => $v)
	{
		$arResult['NAKLS'][$v['NumDoc']] = $v['Events'];
	}
	
	if ($arParams['ONLY_1C_DATA'] != 'Y')
	{
		foreach ($arResult['AR_NUMBERS'] as $n)
		{
			$res = CIBlockElement::GetList(array("id"=>"desc"), array("IBLOCK_ID"=> 28, "PROPERTY_NUM"=>$n), false, array("nTopCount" => 1), array("ID"));
			if($ob = $res->GetNextElement())
			{
				$arF = $ob->GetFields();
				$arResult['YET_NAKLS'][$n]['ID'] = $arF['ID'];
				$arResult['YET_NAKLS'][$n]['INN'] = array();
				$db_props = CIBlockElement::GetProperty(28, $arF['ID'], array("sort" => "asc"), array("CODE"=>"INN"));
				while($ar_props = $db_props->Fetch())
				{
					$arResult['YET_NAKLS'][$n]['INN'][] = $ar_props["VALUE"];
				}
				$arResult['YET_NAKLS'][$n]['EVENTS'] = array();
				$res_2 = CIBlockElement::GetList(array("PROPERTY_DATE"=>"asc"), array("IBLOCK_ID"=> 30, "PROPERTY_NUM"=> $arF['ID'], "ACTIVE" => "Y"), false, false, array("ID", 'PROPERTY_DATE', 'PROPERTY_EVENT', 'PROPERTY_DESC', 'PROPERTY_INN'));
				while ($ob_2 = $res_2->GetNextElement())
				{
					$arF_2 = $ob_2->GetFields();
					if ($arResult['TIME_CORRECTION'])
					{
						$timestamp = mktime(
							intval(substr($arF_2['PROPERTY_DATE_VALUE'], 11, 2)), 
							intval(substr($arF_2['PROPERTY_DATE_VALUE'], 14, 2)), 
							intval(substr($arF_2['PROPERTY_DATE_VALUE'], 17, 2)), 
							intval(substr($arF_2['PROPERTY_DATE_VALUE'], 3, 2)), 
							intval(substr($arF_2['PROPERTY_DATE_VALUE'], 0, 2)), 
							intval(substr($arF_2['PROPERTY_DATE_VALUE'], 6, 4))
						) + $arResult['TIME_OFFSET'];
						$arF_2['PROPERTY_DATE_VALUE'] = date('d.m.Y H:i:00',$timestamp);
					}
					$arResult['YET_NAKLS'][$n]['EVENTS'][] = array(
						'DateEvent' => substr($arF_2['PROPERTY_DATE_VALUE'], 0, 10),
						'TimeEvent' => substr($arF_2['PROPERTY_DATE_VALUE'], 11, 5),
						'Event' => $arF_2['PROPERTY_EVENT_VALUE'],
						'InfoEvent' => trim($arF_2['PROPERTY_DESC_VALUE']),
						'INN' => $arF_2['PROPERTY_INN_VALUE'],
						/*
						'PROPERTY_DATE_VALUE' => $arF_2['PROPERTY_DATE_VALUE']
						'timecorrection' => $timecorrection,
						'timestamp' => $timestamp,
						'dtime' => date('d.m.Y H:i:00',$timestamp)
						*/
					);
				}
			}
		}
	}
	
	
	foreach ($arResult['AR_NUMBERS'] as $n)
	{
		if (mb_detect_encoding($n) == 'UTF-8')
		{
			$n = iconv('utf-8','windows-1251',$n);
		}
		$event_bd_1c = $arResult['NAKLS'][$n];
		/*
		if ($arParams['TEST_MODE'] == 'Y')
		{
			echo iconv('utf-8','windows-1251',$n).'<br>';
			print_r($event_bd_1c);
		}
		*/
		foreach ($event_bd_1c as $arEv)
		{
		//	if ($arEv['INN'] == $arParams['INN'])
		//	{
			//	unset($arEv['INN']);
				$arResult['ALL_EVENTS'][$n][] = $arEv;
			//}
		}
		foreach ($arResult['YET_NAKLS'][$n]['EVENTS'] as $arEv)
		{
		//	if ($arEv['INN'] != $arParams['INN'])
			//{
				// unset($arEv['INN']);
				$arResult['ALL_EVENTS'][$n][] = $arEv;
			//}
		}
	}
	
	
	$arResult['DELIVERED'] = array();
	foreach ($arResult['ALL_EVENTS'] as $number => $arrAddressList)
	{
		$arResult['DELIVERED'][$number] = array();
		foreach ($arrAddressList as $key => $event)
		{
			if ($arParams['HIDE_EVENTS'] == 'Y')
			{
				if (in_array($event['InfoEvent'], $arResult['HIDE_EVENTS']) && ($event['Event'] == 'Исключительная ситуация!'))
				{
					unset($arResult['ALL_EVENTS'][$number][$key]);
				}
			}
			if ($event['Event'] == 'Доставлено')
			{
				$arResult['DELIVERED'][$number][$key] = $event;
			}
		}
		
		/*Приоритет ИНН Москвы*/
		if (count($arResult['DELIVERED'][$number]) > 1)
		{
			$arINNs = array();
			foreach ($arResult['DELIVERED'][$number] as $k => $v)
			{
				$arINNs[$k] = $v['INN'];
			}
			
			if (in_array('7700000001', $arINNs))
			{
				foreach ($arINNs as $k => $v)
				{
					if ($v != '7700000001')
					{
						unset($arResult['ALL_EVENTS'][$number][$k]);
					}
				}
			}
		}
		/*Приоритет ИНН Москвы*/
	}
	
	$arResult['ALL_EVENTS_INNS'] = $arResult['ALL_EVENTS'];
	
	/*
	foreach ($arResult['ALL_EVENTS'] as $number => $arrAddressList)
	{
		foreach ($arrAddressList as $k => $v)
		{
			unset($arResult['ALL_EVENTS'][$number][$k]['INN']);
		}
	}
	*/
	
	/*
	if ($USER->IsAdmin()) 
	{
		echo '<pre>';
		print_r($arResult['ALL_EVENTS_INNS']);
		print_r($arResult['ALL_EVENTS']);
		echo '</pre>';
	}
	*/

	
	//удаление дубликатов событий
	foreach ($arResult['ALL_EVENTS'] as $number => $arrAddressList)
	{
		foreach ($arrAddressList AS $key => $arrAddress)
		{
			$arrAddressList[$key] = serialize($arrAddress);
		}
		$arrAddressList = array_unique($arrAddressList);
		foreach ($arrAddressList AS $key => $strAddress)
		{
			$arrAddressList[$key] = unserialize($strAddress);
		}
		$arResult['ALL_EVENTS'][$number] = $arrAddressList;
		foreach ($arrAddressList as $ii => $arEv)
		{
			$hour = intval(substr($arEv['TimeEvent'], 0, 2));
			$minute = intval(substr($arEv['TimeEvent'], 3, 2));
			$second = 0;
			$month = intval(substr($arEv['DateEvent'], 3, 2));
			$day = intval(substr($arEv['DateEvent'], 0, 2));
			$year = intval(substr($arEv['DateEvent'], 6, 4));
			$arResult['DATES_NAKLS'][$number][$ii] = mktime($hour, $minute, $second, $month, $day, $year);
		}
	}
	
	foreach ($arResult['DATES_NAKLS'] as $n => $v)
	{
		asort($v);
		$arResult['DATES_NAKLS_SORT'][$n] = $v;
	}
	
	
	//смотрим, что записать в БД на сайте (ищем новые события в 1с)
	foreach ($arResult['YET_NAKLS'] as $number => $v)
	{
		foreach ($v['EVENTS'] as $event)
		{
			foreach ($arResult['NAKLS'][$number] as $id_ev => $event_1c)
			{
				if ($event_1c == $event)
				{
					unset($arResult['NAKLS'][$number][$id_ev]);
				}
			}
		}
	}
	foreach ($arResult['NAKLS'] as $number => $events)
	{
		if (count($events) > 0)
		{
			// echo '<p>Записать в БД события по накладной '.$number.'</p>';
		}
	}
	
	/*
	echo '<pre>';
	print_r($arResult['NAKLS']);
	print_r($arResult['YET_NAKLS']);
	print_r($arResult['ALL_EVENTS']);
	echo '</pre>';
	*/
	
}

if ($arParams['TEST_MODE'] == 'Y')
{
	/*
	echo '<pre>';
	print_r($arResult['NAKLS']);
	print_r($arResult['YET_NAKLS']);
	print_r($arResult['ALL_EVENTS']);
	echo '</pre>';
	*/
}


if ($arParams['NO_TEMPLATE'] == 'Y')
{
	return $arResult;
}
else
{
	$this->IncludeComponentTemplate();
}


?>