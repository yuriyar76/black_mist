<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

ini_set("soap.wsdl_cache_enabled", "0" );
ini_set("default_socket_timeout", "300");

include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

$modes = array(
    'list',
    'add',
    'print',
    'invoice',
    'invoice_modal',
    'invoice1c_modal',
    'invoice1c_print',
    'invoice_tracking',
    'edit',
    '1c',
    'pdf',
    'close',
    'list_xls',
    'acceptance'
);
// TODO: [x]Новый режим для просмотра накладной непосредственно из 1с
// TODO: [x]Новый режим для печати накладной непосредственно из 1с
$arResult['MODE'] = $modes[0];
if ((strlen($arParams["MODE"])) && (in_array($arParams["MODE"], $modes)))
{
    $arResult['MODE'] = $arParams["MODE"];
}
else
{
    if ((strlen(trim($_GET["mode"]))) && (in_array(trim($_GET["mode"]), $modes)))
    {
        $arResult['MODE'] = trim($_GET["mode"]);
    }
}
$arResult['HIDE_EVENTS'] = array('Задержка рейса','Задержка авиарейса');
$arParams['TYPE'] = (intval($arParams['TYPE']) > 0) ? intval($arParams['TYPE']) : 242;
if (($arResult['MODE'] != '1c') && ($arResult['MODE'] != 'acceptance'))
{
    $arResult['ADMIN_AGENT'] = false;
    $arResult['USER_IN_BRANCH'] = false;
	$arResult['BRANCH_AGENT_BY'] = false;
	$arResult['CLIENT_CONTRACT'] = false;

    $rsUser = CUser::GetByID($USER->GetID());
    $arUser = $rsUser->Fetch();
    $arResult["USER_ID"] = $arUser["ID"];
	$arResult['USER_NAME'] = $USER->GetFullName();
    $agent_id = intval($arUser["UF_COMPANY_RU_POST"]);

    if ($agent_id > 0)
    {
        $arResult['AGENT'] = GetCompany($agent_id);
        if (in_array($arResult['AGENT']["PROPERTY_TYPE_ENUM_ID"], array(51, $arParams['TYPE'])))
        {
            if ($arResult['AGENT']["PROPERTY_TYPE_ENUM_ID"] == 51)
            {
                $arResult['ADMIN_AGENT'] = true;
                $arResult['UK'] = $arResult['AGENT']["ID"];
            }
            else
            {
                $arResult['UK'] = $arResult['AGENT']["PROPERTY_UK_VALUE"];
            }
            if (intval($arResult['UK']) > 0)
            {
                $currentip = GetSettingValue(683, false, $arResult['UK']);
                $currentlink = GetSettingValue(704, false, $arResult['UK']);
                $login1c = GetSettingValue(705, false, $arResult['UK']);
                $pass1c = GetSettingValue(706, false, $arResult['UK']);
                $arResult['ZADARMA'] = GetSettingValue(707, false, $arResult['UK']);
                $arResult['ZADARMA_FROM'] = GetSettingValue(708, false, $arResult['UK']);
                $arResult['EMAIL_CALLCOURIER'] = GetSettingValue(709, false, $arResult['UK']);
                $arResult['EMAIL_NEWINVOICES'] = GetSettingValue(710, false, $arResult['UK']);
                if ((strlen(trim($currentip))) && (strlen(trim($currentlink))) && (strlen(trim($login1c))) && (strlen(trim($pass1c))))
                {
                    $url = "http://".$currentip.$currentlink;
					
                    $curl = curl_init();
                    curl_setopt_array($curl, array(    
						CURLOPT_URL => $url,
						CURLOPT_HEADER => true,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_NOBODY => true,
						CURLOPT_TIMEOUT => 10
					));
                    $header = explode("\n", curl_exec($curl));
                    curl_close($curl);
                    if (strlen(trim($header[0])))
                    {
						
                        if ($arResult['ADMIN_AGENT'])
                        {

                        }
                        else
                        {
							if ($arParams['TYPE'] == 53)
							{
								//NOTE Определение параметров для агентов
							}
							else
							{
								//NOTE Определяем тип работы клиента с филиалами
								if ($arResult['AGENT']["PROPERTY_TYPE_WORK_BRANCHES_ENUM_ID"] == 301)
								{
									if (intval($_SESSION['CURRENT_BRANCH']) == 0)
									{
										LocalRedirect('/choice-branch/');
									}
									else
									{
										$arResult['USER_IN_BRANCH'] = true;
										$arResult['CURRENT_BRANCH'] = intval($_SESSION['CURRENT_BRANCH']);
									}
								}
								else
								{
									if (intval($arUser["UF_BRANCH"]))
									{
										$arResult['USER_IN_BRANCH'] = true;
										$arResult['CURRENT_BRANCH'] = intval($arUser["UF_BRANCH"]);
									}
								}
								//NOTE Определяем тип работы клиента с филиалами
								//NOTE Если работаем с филиалом
								if ($arResult['USER_IN_BRANCH'])
								{
									$arResult['BRANCH_INFO'] = GetBranch($arResult['CURRENT_BRANCH'], $agent_id);
									if(intval($arResult['BRANCH_INFO']['PROPERTY_BY_AGENT_VALUE']) > 0)
									{
										$db_props = CIBlockElement::GetProperty(40, intval($arResult['BRANCH_INFO']['PROPERTY_BY_AGENT_VALUE']), array("sort" => "asc"), Array("CODE"=>"EMAIL"));
										if($ar_props = $db_props->Fetch())
										{
											if(strlen(trim($ar_props["VALUE"])))
											{
												$arResult['ADD_AGENT_EMAIL'] = trim($ar_props["VALUE"]).', ';
											}
										}
									}

									$db_props_2 = CIBlockElement::GetProperty(89, $arResult['CURRENT_BRANCH'], array("sort" => "asc"), Array("ID"=>644));	
									if($ar_props_2 = $db_props_2->Fetch())
									{
										$arResult['BRANCH_AGENT_BY'] = $ar_props_2["VALUE"];
									}
								}
								else
								{
									//TODO [x]Привязка клиентов к агенту, указание e-mail агента
									if ((is_array($arResult['AGENT']['PROPERTY_BY_AGENT_VALUE'])) && (count($arResult['AGENT']['PROPERTY_BY_AGENT_VALUE']) > 0))
									{
										foreach ($arResult['AGENT']['PROPERTY_BY_AGENT_VALUE'] as $ag)
										{
											$db_props = CIBlockElement::GetProperty(40, $ag, array("sort" => "asc"), Array("CODE"=>"EMAIL"));
											if($ar_props = $db_props->Fetch())
											{
												if(strlen(trim($ar_props["VALUE"])))
												{
													$arResult['ADD_AGENT_EMAIL'] .= trim($ar_props["VALUE"]).', ';
												}
											}
										}
									}
								}
								// Контракты
								$arContracts = array();
								$res = CIBlockElement::GetList(
									array("id" => "desc"), 
									array("IBLOCK_ID" => 88, "PROPERTY_CLIENT" => $agent_id),
									false, 
									false, 
									array(
										"ID"
									)
								);
								while ($ob = $res->GetNextElement())
								{
									$arFields = $ob->GetFields();
									$arContracts[] = $arFields["ID"];
								}
								if (count($arContracts) > 0)
								{
									$arResult['CLIENT_CONTRACT'] = $arContracts[0];
								}
								// Контракты
							}
						}
                        $client = new SoapClient(
                            $url,  
                            array("login" => $login1c, "password" => $pass1c, "exceptions" => false)
                        );
                        
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
                        if (is_array($_SESSION['WARNINGS']))
                        {
                            $arResult["WARNINGS"] = $_SESSION['WARNINGS'];
                            $_SESSION['WARNINGS'] = false;
                        }
                    }
                    else
                    {
                        $arResult['MODE'] = 'close';
						$arResult["ERRORS"][] = '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> <strong>Пользователи Личного Кабинета!</strong> В настоящий момент работа Личного Кабинета приостановлена в связи с аварией на оборудовании. Приносим извинения за доставленные неудобства.';
                    }
                }
                else
                {
                    $arResult['MODE'] = 'close';
					$arResult["ERRORS"][] = 'Не заданы настройки подключения. Пожалуйста, обратитесь в <a href="/support/">тех. поддержку</a>.';
                }
            }
            else
            {
                $arResult['MODE'] = 'close';
				$arResult["ERRORS"][] = 'Ошибка настройки пользователя. Пожалуйста, обратитесь в <a href="/support/">тех. поддержку</a>.';
            }
        }
        else
        {
            $arResult['MODE'] = 'close';
			$arResult["ERRORS"][] = 'Ошибка доступа. Пожалуйста, обратитесь в <a href="/support/">тех. поддержку</a>.';
        }
    }
    else
    {
        $arResult['MODE'] = 'close';
		$arResult["ERRORS"][] = 'Ошибка настройки пользователя. Пожалуйста, обратитесь в <a href="/support/">тех. поддержку</a>.';
    }
}
if ($arResult['MODE'] != 'close')
{
    $arResult["OPEN"] = true;
	if ($arResult['MODE'] == 'list')
	{
		$arResult['LIST_TO_DATE'] = date('d.m.Y');
		$prevdate = strtotime('-10 days');
		$arResult['LIST_FROM_DATE'] = date('d.m.Y',$prevdate);
		$arResult['LIST_FROM_DATE_FOR_1C'] = date('Y-m-d',$prevdate);
		$arResult['LIST_TO_DATE_FOR_1C'] = date('Y-m-d');
		
		if (!$arResult['ADMIN_AGENT'])
		{
			$arResult['CURRENT_CLIENT'] = $agent_id;
		}
		else
		{
			if (strlen($_SESSION['CURRENT_CLIENT']))
			{
				$arResult['CURRENT_CLIENT'] = $_SESSION['CURRENT_CLIENT'];
			}
			else
			{
				$arResult['CURRENT_CLIENT'] = 0;
			}
		}
		if (strlen($_SESSION['LIST_TO_DATE']))
		{
			$arResult['LIST_TO_DATE'] = $_SESSION['LIST_TO_DATE'];
		}
		if (strlen($_SESSION['LIST_FROM_DATE']))
		{
			$arResult['LIST_FROM_DATE'] = $_SESSION['LIST_FROM_DATE'];
		}
		if (strlen($_SESSION['LIST_TO_DATE_FOR_1C']))
		{
			$arResult['LIST_TO_DATE_FOR_1C'] = $_SESSION['LIST_TO_DATE_FOR_1C'];
		}
		if (strlen($_SESSION['LIST_FROM_DATE_FOR_1C']))
		{
			$arResult['LIST_FROM_DATE_FOR_1C'] = $_SESSION['LIST_FROM_DATE_FOR_1C'];
		}
		if (!$arResult['USER_IN_BRANCH'])
		{
			if (strlen($_SESSION['CURRENT_BRANCH']))
			{
				$arResult['CURRENT_BRANCH'] = $_SESSION['CURRENT_BRANCH'];
			}	
		}
		if ($_GET['ChangePeriod'] == 'Y')
		{
			if ((strlen(trim($_GET['datefrom'])) > 0) && (strlen(trim($_GET['dateto']))))
			{
				$arPostDateFrom = date_parse_from_format("d.m.Y", trim($_GET['datefrom']));
				$arPostDateTo = date_parse_from_format("d.m.Y", trim($_GET['dateto']));
				$currentdate = strtotime(date('Y-m-d'));
				$timePostDateTo = strtotime($arPostDateTo['year'].'-'.str_pad($arPostDateTo['month'],2,'0',STR_PAD_LEFT).'-'.str_pad($arPostDateTo['day'],2,'0',STR_PAD_LEFT));
				$timePostDateFrom = strtotime($arPostDateFrom['year'].'-'.str_pad($arPostDateFrom['month'],2,'0',STR_PAD_LEFT).'-'.str_pad($arPostDateFrom['day'],2,'0',STR_PAD_LEFT));
				if ($timePostDateFrom > $timePostDateTo)
				{
					$vremVar = $timePostDateTo;
					$timePostDateTo = $timePostDateFrom;
					$timePostDateFrom = $vremVar;
					$timeFromToRazn = $timePostDateTo - $timePostDateFrom;
				}
				if ($timePostDateTo > $currentdate)
				{
					$timePostDateTo = $currentdate;
				}
				if ($timePostDateFrom > $timePostDateTo)
				{
					$timePostDateFrom = strtotime('-10 days',$timePostDateTo);
				}
				$timeFromToRazn = $timePostDateTo - $timePostDateFrom;
				if (($timeFromToRazn/86400) > 90)
				{
					$timePostDateFrom = strtotime('-3 month',$timePostDateTo);
				}
				$arResult['LIST_FROM_DATE'] = date('d.m.Y',$timePostDateFrom);
				$_SESSION['LIST_FROM_DATE'] = date('d.m.Y',$timePostDateFrom);
				$arResult['LIST_TO_DATE'] = date('d.m.Y',$timePostDateTo);
				$_SESSION['LIST_TO_DATE'] = date('d.m.Y',$timePostDateTo);
				$arResult['LIST_FROM_DATE_FOR_1C'] = date('Y-m-d',$timePostDateFrom);
				$_SESSION['LIST_FROM_DATE_FOR_1C'] = date('Y-m-d',$timePostDateFrom);
				$arResult['LIST_TO_DATE_FOR_1C'] = date('Y-m-d',$timePostDateTo);
				$_SESSION['LIST_TO_DATE_FOR_1C'] = date('Y-m-d',$timePostDateTo);	
			}
		}
		$arResult['LIST_OF_CLIENTS'] = false;
		
		if ($arResult['ADMIN_AGENT'])
		{
			if ($arParams['TYPE'] == 53)
			{
				$arResult['LIST_OF_CLIENTS'] = AvailableAgents(false, $agent_id);
			}
			else
			{
				$arResult['LIST_OF_CLIENTS'] = AvailableClients(false, false, $agent_id);
			}
			if ($_GET['ChangeClient'] == 'Y')
			{
				if (isset($arResult['LIST_OF_CLIENTS'][$_GET['client']]))
				{
					$_SESSION['CURRENT_CLIENT'] = $_GET['client'];
					$arResult['CURRENT_CLIENT'] = $_GET['client'];
				}
                elseif (intval($_GET['client']) == 0)
                {
                    unset($_SESSION['CURRENT_CLIENT']);
                    unset($_SESSION['CURRENT_CLIENT_INN']);
                    $arResult['CURRENT_CLIENT'] = false;
                    $arResult['CURRENT_CLIENT_INN'] = false;
                }
			}
		}
		
		$arResult['CURRENT_CLIENT_COEFFICIENT_VW'] = WhatIsGabWeightCompany($arResult['CURRENT_CLIENT']);
		$arResult['CURRENT_CLIENT_INN'] = "";
		
		$db_props = CIBlockElement::GetProperty(40, $arResult['CURRENT_CLIENT'], array("sort" => "asc"), array("CODE"=>"INN"));
		if($ar_props = $db_props->Fetch())
		{
			$arResult['CURRENT_CLIENT_INN'] = $ar_props["VALUE"];
		}
		
		$arResult['LIST_OF_BRANCHES'] = false;
		$arResult['LIMITS_OF_BRANCHES'] = false;
		if ($arParams['TYPE'] == 242)
		{
			$res_3 = CIBlockElement::GetList(
				array("NAME" => "asc"), 
				array("IBLOCK_ID" => 89, "PROPERTY_CLIENT" =>  $arResult['CURRENT_CLIENT'], "ACTIVE" => "Y"), 
				false, 
				false, 
				array("ID","NAME","PROPERTY_CITY.NAME", "PROPERTY_LIMIT")
			);
			while ($ob_3 = $res_3->GetNextElement())
			{
				$arFields_3 = $ob_3->GetFields();
				$arResult['LIST_OF_BRANCHES'][$arFields_3["ID"]] = $arFields_3["NAME"].", ".$arFields_3["PROPERTY_CITY_NAME"];
				$arResult['LIMITS_OF_BRANCHES'][$arFields_3["ID"]] = $arFields_3["PROPERTY_LIMIT_VALUE"];
			}

			if ($arResult['LIST_OF_BRANCHES'])
			{
				if ($_GET['ChangeBranch'] == 'Y')
				{
					$_SESSION['CURRENT_BRANCH'] = intval($_GET['branch']);
					$arResult['CURRENT_BRANCH'] = intval($_GET['branch']);
				}
				if ($arResult['ADMIN_AGENT'] && (intval($arResult['CURRENT_BRANCH']) > 0))
				{
					$arResult['BRANCH_INFO'] = GetBranch($arResult['CURRENT_BRANCH'], $arResult['CURRENT_CLIENT']);
				}
			}
		}
		if ((isset($_POST['delete'])) && ($arResult['ADMIN_AGENT']))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
				if (count($_POST['ids']) > 0)
				{
					foreach ($_POST['ids'] as $id)
					{
						$el = new CIBlockElement;
						$res = $el->Update($id, array("ACTIVE"=>"N"));
					}
					$arResult['MESSAGE'][] = 'Накладные успешно удалены';
				}
				else
				{
					$arResult["ERRORS"][] = 'Не выбраны накладные для удаления';
				}
			}
		}
		
		if ((isset($_POST['accept'])) && ($arResult['ADMIN_AGENT']))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
				if (count($_POST['ids']) > 0)
				{
					$arCells = array();
					$arCells[] = array(
						'',
						'Номер накладной',
						'Отправитель',
						'Фамилия отправителя',
						'Город отправителя',
						'Индекс отправителя',
						'Адрес отправителя',
						'Телефон отправителя',
						'Получатель',
						'Фамилия получателя',
						'Город получателя',
						'Индекс получателя',
						'Адрес получателя',
						'Телефон получателя',
						'Мест',
						'Вес',
						'Объемный вес',
						'Тип доставки',
						'Тип отправления',
						'Доставить',
						'Доставить в дату',
						'Доставить до часа',
						'Оплачивает',
						'Оплата',
						'Сумма к оплате',
						'Объявленная стоимость',
						'Специальные инструкции'	
					);
					$arManifestTo1c = array(
						"TransportationDocument" => "",
						"Carrier" => "",
						"TransportationCost" => 0,
						"Partner" => $arResult['CURRENT_CLIENT_INN'],
						"DepartureDate" => date('d.m.Y'),
						"Places" => 0,
						"Weight" => 0,
						"VolumeWeight" => 0,
						"Comment" => "",
						"City" => "",
						"TransportationMethod" => "",
						"Delivery" => array()
					);
					$res = CIBlockElement::GetList(
						array("id" => "desc"), 
						array("IBLOCK_ID" => 83, "ID" => $_POST['ids']), 
						false, 
						false, 
						array(
							"ID",
							"NAME",
							"PROPERTY_NAME_SENDER",
							"PROPERTY_PHONE_SENDER",
							"PROPERTY_COMPANY_SENDER",
							"PROPERTY_CITY_SENDER",
							"PROPERTY_CITY_SENDER.NAME",
							"PROPERTY_INDEX_SENDER",
							"PROPERTY_ADRESS_SENDER",
							"PROPERTY_NAME_RECIPIENT",
							"PROPERTY_PHONE_RECIPIENT",
							"PROPERTY_COMPANY_RECIPIENT",
							"PROPERTY_CITY_RECIPIENT",
							"PROPERTY_CITY_RECIPIENT.NAME",
							"PROPERTY_INDEX_RECIPIENT",
							"PROPERTY_ADRESS_RECIPIENT",
							"PROPERTY_TYPE_DELIVERY",
							"PROPERTY_TYPE_PACK",
							"PROPERTY_WHO_DELIVERY",
							"PROPERTY_IN_DATE_DELIVERY",
							"PROPERTY_IN_TIME_DELIVERY",
							"PROPERTY_TYPE_PAYS",
							"PROPERTY_PAYS",
							"PROPERTY_PAYMENT",
							"PROPERTY_FOR_PAYMENT",
							"PROPERTY_PAYMENT_COD",
							"PROPERTY_COST",
							"PROPERTY_PLACES",
							"PROPERTY_WEIGHT",
							"PROPERTY_DIMENSIONS",
							"PROPERTY_STATE",
							"PROPERTY_INSTRUCTIONS",
							"PROPERTY_PACK_DESCRIPTION",
                            "PROPERTY_BRANCH",
                            "PROPERTY_PACK_GOODS",
							"PROPERTY_WHOSE_ORDER"
						)
					);
					while ($ob = $res->GetNextElement())
					{
						$reqv = $ob->GetFields();
						$reqv["PROPERTY_OB_WEIGHT"] = 0;
						$reqv["PROPERTY_Dimensions"] = array();
						if (strlen($reqv['PROPERTY_PACK_DESCRIPTION_VALUE']))
						{
							$reqv['PACK_DESCR'] = json_decode(htmlspecialcharsBack($reqv['PROPERTY_PACK_DESCRIPTION_VALUE']), true);
							foreach ($reqv['PACK_DESCR'] as $k => $str)
							{
								$reqv["PROPERTY_OB_WEIGHT"] = $reqv["PROPERTY_OB_WEIGHT"] + $str['gabweight'];
								$reqv["PROPERTY_Dimensions"][] = array(
									"WEIGHT" => (floatval($str['weight']) > 0) ? floatval($str['weight']) : 0,
									"SIZE_1" => (floatval($str["size"][0]) > 0) ? floatval($str["size"][0]) : 0,
									"SIZE_2" => (floatval($str["size"][1]) > 0) ? floatval($str["size"][1]) : 0,
									"SIZE_3" => (floatval($str["size"][2]) > 0) ? floatval($str["size"][2]) : 0,
									"PLACES" => intval($str["place"]),
                                    "NAME" => iconv('utf-8','windows-1251',$str['name'])
								);
							}
						}
						else
						{
							if (is_array($reqv['PROPERTY_DIMENSIONS_VALUE']))
							{
								$w = 1;
								for ($i = 0; $i<3; $i++)
								{
									$w = $w*$reqv['PROPERTY_DIMENSIONS_VALUE'][$i];
								}
								$reqv["PROPERTY_OB_WEIGHT"] = $w/$arResult['CURRENT_CLIENT_COEFFICIENT_VW'];
							}
							$reqv["PROPERTY_Dimensions"][] = array(
								"WEIGHT" => (floatval($reqv['PROPERTY_WEIGHT_VALUE']) > 0) ? floatval($reqv['PROPERTY_WEIGHT_VALUE']) : 0,
								"SIZE_1" => (floatval($reqv['PROPERTY_DIMENSIONS_VALUE'][0]) > 0) ? floatval($reqv['PROPERTY_DIMENSIONS_VALUE'][0]) : 0,
								"SIZE_2" => (floatval($reqv['PROPERTY_DIMENSIONS_VALUE'][1]) > 0) ? floatval($reqv['PROPERTY_DIMENSIONS_VALUE'][1]) : 0,
								"SIZE_3" => (floatval($reqv['PROPERTY_DIMENSIONS_VALUE'][2]) > 0) ? floatval($reqv['PROPERTY_DIMENSIONS_VALUE'][2]) : 0,
								"PLACES" => intval($reqv['PROPERTY_PLACES_VALUE']),
                                "NAME" => ''
							);
						}
						$reqv['PACK_GOODS'] = '';
                        if (strlen($reqv['PROPERTY_PACK_GOODS_VALUE']))
                        {
                            $reqv['PACK_GOODS'] = json_decode(htmlspecialcharsBack($reqv['PROPERTY_PACK_GOODS_VALUE']), true);
							if ((is_array($reqv['PACK_GOODS'])) && (count($reqv['PACK_GOODS']) > 0))
							{
								foreach ($reqv['PACK_GOODS'] as $k => $str)
								{
									$reqv['PACK_GOODS'][$k]['GoodsName'] = iconv('utf-8','windows-1251',$str['GoodsName']);
									if (strlen(trim($reqv['PACK_GOODS'][$k]['GoodsName'])) == 0)
									{
										unset($reqv['PACK_GOODS'][$k]);
									}
								}
							}
                        }
						// $reqv["PROPERTY_OB_WEIGHT"] = WeightFormat($r['PROPERTY_OB_WEIGHT'],false);
                        
                        $reqv['BRANCH_CODE'] = '';
                        if (intval($reqv['PROPERTY_BRANCH_VALUE']) > 0)
                        {
                            $db_props = CIBlockElement::GetProperty(89, $reqv['PROPERTY_BRANCH_VALUE'], array("sort" => "asc"), array("CODE"=>"IN_1C_CODE"));
                            if($ar_props = $db_props->Fetch())
                            {
                                $reqv['BRANCH_CODE'] = $ar_props["VALUE"];
                            }
                        }
						
						//NOTE Определение значения "чей заказ"
						$WHOSE_ORDER_ID = false;
						if (intval($reqv['PROPERTY_WHOSE_ORDER_VALUE']) > 0)
						{
							$db_props = CIBlockElement::GetProperty(40, intval($reqv['PROPERTY_WHOSE_ORDER_VALUE']), array("sort" => "asc"), array("CODE"=>"INN"));
							if($ar_props = $db_props->Fetch())
							{
								if (strlen(trim($ar_props["VALUE"])))
								{
									$WHOSE_ORDER_ID = $ar_props["VALUE"];
								}
								
							}	
						}
						
						$cell = array(
							'',
							$reqv['NAME'],
							$reqv['PROPERTY_COMPANY_SENDER_VALUE'],
							$reqv['PROPERTY_NAME_SENDER_VALUE'],
							$reqv['PROPERTY_CITY_SENDER_NAME'],
							$reqv['PROPERTY_INDEX_SENDER_VALUE'],
							$reqv['PROPERTY_ADRESS_SENDER_VALUE']['TEXT'],
							$reqv['PROPERTY_PHONE_SENDER_VALUE'],
							$reqv['PROPERTY_COMPANY_RECIPIENT_VALUE'],
							$reqv['PROPERTY_NAME_RECIPIENT_VALUE'],
							$reqv['PROPERTY_CITY_RECIPIENT_NAME'],
							$reqv['PROPERTY_INDEX_RECIPIENT_VALUE'],
							$reqv['PROPERTY_ADRESS_RECIPIENT_VALUE']['TEXT'],
							$reqv['PROPERTY_PHONE_RECIPIENT_VALUE'],
							$reqv['PROPERTY_PLACES_VALUE'],
							$reqv['PROPERTY_WEIGHT_VALUE'],
							$reqv['PROPERTY_OB_WEIGHT'],
							$reqv['PROPERTY_TYPE_DELIVERY_VALUE'],
							$reqv['PROPERTY_TYPE_PACK_VALUE'],
							$reqv['PROPERTY_WHO_DELIVERY_VALUE'],
							$reqv['PROPERTY_IN_DATE_DELIVERY_VALUE'],
							$reqv['PROPERTY_IN_TIME_DELIVERY_VALUE'],
							($reqv['PROPERTY_TYPE_PAYS_ENUM_ID'] == 253) ? $reqv['PROPERTY_PAYS_VALUE'] : $reqv['PROPERTY_TYPE_PAYS_VALUE'],
							$reqv['PROPERTY_PAYMENT_VALUE'],
							$reqv['PROPERTY_FOR_PAYMENT_VALUE'],
							$reqv['PROPERTY_COST_VALUE'],
							$reqv['PROPERTY_INSTRUCTIONS_VALUE']['TEXT']
						);
						$arCells[] = $cell;
						$arCitySENDER = GetFullNameOfCity($reqv['PROPERTY_CITY_SENDER_VALUE'], false, true);
						$arCityRECIPIENT = GetFullNameOfCity($reqv['PROPERTY_CITY_RECIPIENT_VALUE'], false, true);
						/*
						$arManifestTo1c["Delivery"][] = array(
							"DeliveryNote" => $reqv['NAME'],
							"DATE_CREATE" => date('d.m.Y'),
							// "SMSINFO" => 0,
							"INN" => $arResult['CURRENT_CLIENT_INN'],
							"NAME_SENDER" => $reqv['PROPERTY_NAME_SENDER_VALUE'],
							"PHONE_SENDER" => $reqv['PROPERTY_PHONE_SENDER_VALUE'],
							"COMPANY_SENDER" => $reqv['PROPERTY_COMPANY_SENDER_VALUE'],
							"COUNTRY_SENDER" => $arCitySENDER[2],
							"REGION_SENDER" => $arCitySENDER[1],
							"CITY_SENDER" => $reqv['PROPERTY_CITY_SENDER_NAME'],
							"INDEX_SENDER" => $reqv['PROPERTY_INDEX_SENDER_VALUE'],
							"ADRESS_SENDER" => $reqv['PROPERTY_ADRESS_SENDER_VALUE']['TEXT'],
							"NAME_RECIPIENT" => $reqv['PROPERTY_NAME_RECIPIENT_VALUE'],
							"PHONE_RECIPIENT" => $reqv['PROPERTY_PHONE_RECIPIENT_VALUE'],
							"COMPANY_RECIPIENT" => $reqv['PROPERTY_COMPANY_RECIPIENT_VALUE'],
							"COUNTRY_RECIPIENT" => $arCityRECIPIENT[2],
							"REGION_RECIPIENT" => $arCityRECIPIENT[1],
							"CITY_RECIPIENT" => $reqv['PROPERTY_CITY_RECIPIENT_NAME'],
							"INDEX_RECIPIENT" => $reqv['PROPERTY_INDEX_RECIPIENT_VALUE'],
							"ADRESS_RECIPIENT" => $reqv['PROPERTY_ADRESS_RECIPIENT_VALUE']['TEXT'],
							"DATE_TAKE_FROM" => "",
							"TYPE" => $reqv['PROPERTY_TYPE_PACK_VALUE'],
							"INSTRUCTIONS" => $reqv['PROPERTY_INSTRUCTIONS_VALUE']['TEXT'],
							//"PLACES" => $reqv['PROPERTY_PLACES_VALUE'],		
							//"WEIGHT" => $reqv['PROPERTY_WEIGHT_VALUE'],
							//"SIZE_1" => (floatval($reqv['PROPERTY_DIMENSIONS_VALUE'][0]) > 0) ? floatval($reqv['PROPERTY_DIMENSIONS_VALUE'][0]) : 0,
							//"SIZE_2" => (floatval($reqv['PROPERTY_DIMENSIONS_VALUE'][1]) > 0) ? floatval($reqv['PROPERTY_DIMENSIONS_VALUE'][1]) : 0,
							//"SIZE_3" => (floatval($reqv['PROPERTY_DIMENSIONS_VALUE'][2]) > 0) ? floatval($reqv['PROPERTY_DIMENSIONS_VALUE'][2]) : 0,
							//"DELIVERY_TYPE" => "",
							//"DELIVERY_PAYER" => "",
							//"PAYMENT_TYPE" => "",
							//"DELIVERY_CONDITION" => "",
							"Dimensions" => $reqv["PROPERTY_Dimensions"],
							"PAYMENT" => $reqv["PROPERTY_FOR_PAYMENT_VALUE"]
						);
                        */
                        $date_take_from = $reqv['PROPERTY_IN_DATE_DELIVERY_VALUE'];
                        $date_take_from .= strlen($reqv['PROPERTY_IN_TIME_DELIVERY_VALUE']) ? ' '.$reqv['PROPERTY_IN_TIME_DELIVERY_VALUE'] : '';
                        $reqv['TO_1C_DELIVERY_TYPE'] = 'С';
                        $reqv['TO_1C_DELIVERY_PAYER'] = 'О';
                        $reqv['TO_1C_PAYMENT_TYPE'] = 'Б';
                        $reqv['TO_1C_DELIVERY_CONDITION'] = 'А';
                        $property_enums = CIBlockPropertyEnum::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>83, "CODE"=>"TYPE_DELIVERY", "ID" => $reqv['PROPERTY_TYPE_DELIVERY_ENUM_ID']));
                        if($enum_fields = $property_enums->GetNext())
                        {
                            $reqv['TO_1C_DELIVERY_TYPE'] = $enum_fields['XML_ID'];
                        }
                        $property_enums = CIBlockPropertyEnum::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>83, "CODE"=>"TYPE_PAYS", "ID" => $reqv['PROPERTY_TYPE_PAYS_ENUM_ID']));
                        if($enum_fields = $property_enums->GetNext())
                        {
                            $reqv['TO_1C_DELIVERY_PAYER'] = $enum_fields['XML_ID'];
                        }

                        $property_enums = CIBlockPropertyEnum::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>83, "CODE"=>"PAYMENT", "ID" => $reqv['PROPERTY_PAYMENT_ENUM_ID']));
                        if($enum_fields = $property_enums->GetNext())
                        {
                            $reqv['TO_1C_PAYMENT_TYPE'] = $enum_fields['XML_ID'];
                        }
                        $property_enums = CIBlockPropertyEnum::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>83, "CODE"=>"WHO_DELIVERY", "ID" => $reqv['PROPERTY_WHO_DELIVERY_ENUM_ID']));
                        if($enum_fields = $property_enums->GetNext())
                        {
                            $reqv['TO_1C_DELIVERY_CONDITION'] = $enum_fields['XML_ID'];
                        }
                        if ($reqv['PROPERTY_TYPE_PAYS_ENUM_ID'] == 254)
                        {
                            $reqv['TO_1C_DELIVERY_PAYER'] = 'Д';
                            if (strlen($reqv['PROPERTY_INSTRUCTIONS_VALUE']['TEXT']))
                            {
                                $reqv['PROPERTY_INSTRUCTIONS_VALUE']['TEXT'] .= ' ';
                            }
                            $reqv['PROPERTY_INSTRUCTIONS_VALUE']['TEXT'] .= 'Служебное.';
                        }
                        $arDelivery = array(
                            "DeliveryNote" => $reqv['NAME'],
                            "DATE_CREATE" => date('d.m.Y'),
                            "SMSINFO" => 0,
                            "INN" => $arResult['CURRENT_CLIENT_INN'],
                            "NAME_SENDER" => $reqv['PROPERTY_NAME_SENDER_VALUE'],
                            "PHONE_SENDER" => $reqv['PROPERTY_PHONE_SENDER_VALUE'],
                            "COMPANY_SENDER" => $reqv['PROPERTY_COMPANY_SENDER_VALUE'],
                            "CITY_SENDER_ID" => $reqv['PROPERTY_CITY_SENDER_VALUE'],
                            "CITY_SENDER" => $reqv['PROPERTY_CITY_SENDER_NAME'],
                            "INDEX_SENDER" => $reqv['PROPERTY_INDEX_SENDER_VALUE'],
                            "COUNTRY_SENDER" => $arCitySENDER[2],
                            "REGION_SENDER" => $arCitySENDER[1],
                            "ADRESS_SENDER" => $reqv['PROPERTY_ADRESS_SENDER_VALUE']['TEXT'],
                            "NAME_RECIPIENT" => $reqv['PROPERTY_NAME_RECIPIENT_VALUE'],
                            "PHONE_RECIPIENT" => $reqv['PROPERTY_PHONE_RECIPIENT_VALUE'],
                            "COMPANY_RECIPIENT" => $reqv['PROPERTY_COMPANY_RECIPIENT_VALUE'],
                            "CITY_RECIPIENT_ID" => $reqv['PROPERTY_CITY_RECIPIENT_VALUE'],
                            "CITY_RECIPIENT" => $reqv['PROPERTY_CITY_RECIPIENT_NAME'],
                            "COUNTRY_RECIPIENT" => $arCityRECIPIENT[2],
                            "INDEX_RECIPIENT" => $reqv['PROPERTY_INDEX_RECIPIENT_VALUE'],
                            "REGION_RECIPIENT" => $arCityRECIPIENT[1],
                            "ADRESS_RECIPIENT" => $reqv['PROPERTY_ADRESS_RECIPIENT_VALUE']['TEXT'],
                            "PAYMENT" => $reqv["PROPERTY_FOR_PAYMENT_VALUE"],
							"PAYMENT_COD" => $reqv["PROPERTY_PAYMENT_COD_VALUE"],
                            // TODO [x]Заполнить следующие 5 полей
                            "DATE_TAKE_FROM" => $date_take_from,
                            "DELIVERY_TYPE" => $reqv['TO_1C_DELIVERY_TYPE'],
                            "DELIVERY_PAYER" => $reqv['TO_1C_DELIVERY_PAYER'],
                            "PAYMENT_TYPE" => $reqv['TO_1C_PAYMENT_TYPE'],
                            "DELIVERY_CONDITION" => $reqv['TO_1C_DELIVERY_CONDITION'],
                            "INSTRUCTIONS" => $reqv['PROPERTY_INSTRUCTIONS_VALUE']['TEXT'],
                            // TODO [x]В поле TYPE передавать 0 или 1
                            "TYPE" => ($reqv['PROPERTY_TYPE_PACK_ENUM_ID'] == 247) ? 0 : 1,	
                            "Dimensions" => $reqv['PROPERTY_Dimensions'],
                            'ID' => $reqv['ID'],
                            'ID_BRANCH' => $reqv['BRANCH_CODE'],
                            // 'Goods' => $reqv['PACK_GOODS']
                        );
						if (is_array($reqv['PACK_GOODS']) && (count($reqv['PACK_GOODS']) > 0))
						{
							$arDelivery['Goods'] = $reqv['PACK_GOODS'];
						}
						if ($WHOSE_ORDER_ID)
						{
							$arDelivery['WHOSE_ORDER'] = $WHOSE_ORDER_ID;
						}
						$arManifestTo1c['Delivery'][] = $arDelivery;
						$arManifestTo1c["Places"] = $arManifestTo1c["Places"] + $reqv['PROPERTY_PLACES_VALUE'];
						$arManifestTo1c["Weight"] = $arManifestTo1c["Weight"] + $reqv['PROPERTY_WEIGHT_VALUE'];
						$arManifestTo1c["VolumeWeight"] = $arManifestTo1c["VolumeWeight"] + $reqv["PROPERTY_OB_WEIGHT"];
					}
					set_include_path($_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/PhpExcel/Classes/');
					include_once 'PHPExcel.php';
					$pExcel = new PHPExcel();
					$pExcel->setActiveSheetIndex(0);
					$aSheet = $pExcel->getActiveSheet();
					$pExcel->getDefaultStyle()->getFont()->setName('Arial');
					$pExcel->getDefaultStyle()->getFont()->setSize(10);
					$Q = iconv("windows-1251", "utf-8", 'Манифест');
					$boldFont = array(
						'font'=>array(
							'bold'=>true
						)
					);
					$small = array(
						'font'=>array(
							'size' => 8
						),
					);
					$center = array(
								'alignment'=>array(
									'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
									'vertical'=>PHPExcel_Style_Alignment::VERTICAL_TOP
								)
							);
					$right = array(
						'alignment'=>array(
									'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
									'vertical'=>PHPExcel_Style_Alignment::VERTICAL_TOP
								)
							);
					$table = array(
						'alignment'=>array(
									'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
									'vertical'=>PHPExcel_Style_Alignment::VERTICAL_TOP
								)
							);
					$head_style = array(
						'font' => array(
							'bold' => true,
						),
						'alignment' => array(
							'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						),
						'fill' => array(
							'type' => PHPExcel_Style_Fill::FILL_SOLID,
							'startcolor' => array(
								'argb' => 'FFFFF4E9',
							),
						),
					);
					$footer_style = array(
						'fill' => array(
							'type' => PHPExcel_Style_Fill::FILL_SOLID,
							'startcolor' => array(
								'argb' => 'FFE9FEFF',
							),
						),
					);
					$styleArray = array(
						'borders' => array(
							'allborders' => array(
								'style' => PHPExcel_Style_Border::BORDER_THIN,
								'color' => array('argb' => 'FF000000'),
							),
						),
					);
					$i = 1;
					$arJ = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA');
					foreach  ($arCells as $k)
					{
						foreach ($k as $n => $v)
						{
							$num_sel = $arJ[$n].$i;
							$Q = iconv("windows-1251", "utf-8", $v);
							$aSheet->setCellValue($num_sel,$Q);
						}
						$i++;
					}
					$i--;
					$aSheet->getStyle('B1:AA1')->applyFromArray($head_style);
					$aSheet->getColumnDimension('A')->setWidth(3);
					$aSheet->getColumnDimension('B')->setWidth(17);
					$aSheet->getColumnDimension('C')->setWidth(17);
					$aSheet->getColumnDimension('D')->setWidth(17);
					$aSheet->getColumnDimension('E')->setWidth(17);
					$aSheet->getColumnDimension('F')->setWidth(17);
					$aSheet->getColumnDimension('G')->setWidth(17);
					$aSheet->getColumnDimension('H')->setWidth(17);
					$aSheet->getColumnDimension('I')->setWidth(17);
					$aSheet->getColumnDimension('J')->setWidth(17);
					$aSheet->getColumnDimension('K')->setWidth(17);
					$aSheet->getColumnDimension('L')->setWidth(17);
					$aSheet->getColumnDimension('M')->setWidth(17);
					$aSheet->getColumnDimension('N')->setWidth(17);
					$aSheet->getColumnDimension('O')->setWidth(17);
					$aSheet->getColumnDimension('P')->setWidth(17);
					$aSheet->getColumnDimension('Q')->setWidth(17);
					$aSheet->getColumnDimension('R')->setWidth(17);
					$aSheet->getColumnDimension('S')->setWidth(17);
					$aSheet->getColumnDimension('T')->setWidth(17);
					$aSheet->getColumnDimension('U')->setWidth(17);
					$aSheet->getColumnDimension('V')->setWidth(17);
					$aSheet->getColumnDimension('W')->setWidth(17);
					$aSheet->getColumnDimension('X')->setWidth(17);
					$aSheet->getColumnDimension('Y')->setWidth(17);
					$aSheet->getColumnDimension('Z')->setWidth(17);
					$aSheet->getColumnDimension('AA')->setWidth(17);
					$aSheet->getStyle('B1:AA'.$i)->getAlignment()->setWrapText(true);
					$aSheet->getStyle('B1:AA'.$i)->applyFromArray($styleArray);
					$aSheet->getStyle('A1:AA'.$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
					include_once "PHPExcel/Writer/Excel5.php";
					$objWriter = new PHPExcel_Writer_Excel5($pExcel);
					$path = "/files/overheads/".date('Y-m-d').'_'.$arResult['CURRENT_CLIENT'].'_'.time().".xls";
					$objWriter->save( $_SERVER['DOCUMENT_ROOT'].$path);
					
					$arFile = CFile::MakeFileArray($_SERVER['DOCUMENT_ROOT'].$path);
					$fid = CFile::SaveFile($arFile, "files_overheads");
					$sob_id = CEvent::Send(
                        "NEWPARTNER_LK", 
                        "s5", 
                        array(
                            "EMAIL_FROM" => $arUser['EMAIL'], 
                            "COMPANY_FROM" => '<a href="http://client.newpartner.ru/index.php?ChangeClient=Y&client='.$arResult['CURRENT_CLIENT'].'" target="_blank">'.$arResult['LIST_OF_CLIENTS'][$arResult['CURRENT_CLIENT']].'</a>', 
                            "CREATOR" => $arUser['LAST_NAME'].' '.$arUser['NAME'],
                            'AGENT_EMAIL' => $arResult['ADD_AGENT_EMAIL'],
                            'UK_EMAIL' => $arResult['EMAIL_NEWINVOICES'],
							'WHO_CREATE' => ($arParams['TYPE'] == 53) ? 'Агент' : 'Клиент'
                        ), 
                        "N", 
                        192, 
                        array($fid)
                    );
					$arResult["MESSAGE"][] = 'Манифест .'.$infoMan.' сформирован. <a href="'.$path.'" target="_blank">Скачать манифест</a>';
					$arManifestTo1cUTF = convArrayToUTF($arManifestTo1c);
					$infoMan = '';
					$arLogs = $arManifestTo1c;
					foreach ($arLogs['Delivery'] as $key => $inv)
					{
						foreach ($inv as $inv_key => $data)
						{
							$arLogs['Delivery '.$key.' '.$inv_key] = $data;
						}
					}
					unset($arLogs['Delivery']);
					AddToLogs('InvoicesSend',$arLogs);
					$result = $client->SetManifest(array('ListOfDocs' => json_encode($arManifestTo1cUTF)));
					//TODO [x]Заменить функцию на SetManifest, изменив входящие значения
					$mResult = $result->return;
					AddToLogs('InvoicesSendAnswer', array('Answer' => $mResult));
					//TODO [x]Разбор полученных данных из SetManifest
					$obj = json_decode($mResult, true);
					$arRes = arFromUtfToWin($obj);
					if (strlen($arRes['RecordedManifest']))
					{
						//TODO [x]Принятие только накладных, принятых непосредственно в 1с
						if (count($arRes['ReceivedIDs']) > 0)
						{
							foreach ($arRes['ReceivedIDs'] as $r)
							{
								CIBlockElement::SetPropertyValuesEx($r, 83, array(572 => 258, 573 => date('d.m.Y H:i:s'), 732 => $arResult["USER_ID"]));
							}
							$arResult["MESSAGE"][] = '<strong>Манифест '.$arRes['RecordedManifest'].', содержащий накладные '.implode(', ',$arRes['ReceivedОrders']).' загружен в 1с</strong>.';
						}
						else
						{
							$arResult["WARNINGS"][] = 'Накладные не загружены в 1с';
						}
					}
					else
					{
						$arResult["WARNINGS"][] = 'Манифест не загружен в 1с';
					}
					if (count($arRes['DoublesОrders']) > 0)
					{
						$arResult["WARNINGS"][] = 'Накладные '.implode(', ',$arRes['DoublesОrders']).' уже присутствуют в 1с, их загрузка не произведена';
					}
					if (count($arRes['OrdersError']) > 0)
					{
						$arResult["ERRORS"][] = 'Ошибка загрузки накладных '.implode(', ',$arRes['OrdersError']).' в 1с';
					}
					if (count($arRes['OrderNumberСhanged']) > 0)
					{
						$arResult["WARNINGS"][] = 'Измененные накладные: '.implode(', ',$arRes['OrderNumberСhanged']);
					}
				}
				else
				{
					$arResult["ERRORS"][] = 'Не выбраны накладные для передачи на доставку';
				}
			}
		}
		
		$arResult['REQUESTS'] = array();
        $arResult['ARCHIVE'] = array();
        // TODO [x]Разедлить массивы для накладных с сайта (неотправленных) и из 1с. С сайта выводить ссылки с ID, из 1с - по номеру накладной
		
		if (intval($arResult['CURRENT_CLIENT']) > 0)
		{
			$filter = array("IBLOCK_ID" => 83, "PROPERTY_CREATOR" => intval($arResult['CURRENT_CLIENT']), "ACTIVE" => "Y", "PROPERTY_STATE" => 257);
			/*
			$filter[">=DATE_CREATE"] = '01.'.$arResult['CURRENT_MONTH'].'.'.$arResult['CURRENT_YEAR'].' 00:00:00';
			$filter["<=DATE_CREATE"] = $last_day.'.'.$arResult['CURRENT_MONTH'].'.'.$arResult['CURRENT_YEAR'].' 23:59:59';
			*/
			$filter[">=DATE_CREATE"] = $arResult['LIST_FROM_DATE'].' 00:00:00';
			$filter["<=DATE_CREATE"] = $arResult['LIST_TO_DATE'].' 23:59:59';
			
			if (intval($arResult['CURRENT_BRANCH']) > 0)
			{
				$filter["PROPERTY_BRANCH"] = intval($arResult['CURRENT_BRANCH']);
			}
			/*
			if ($USER->GetID() == 1746)
			{
				print_r($filter);
			}
			*/
			$res = CIBlockElement::GetList(
				array('created' => 'desc'), 
				$filter, 
				false, 
				false, 
				array(
					"ID",
					"NAME",
					"DATE_CREATE",
					"PROPERTY_COMPANY_SENDER",
					"PROPERTY_CITY_SENDER.NAME",
					"PROPERTY_COMPANY_RECIPIENT",
                    "PROPERTY_NAME_RECIPIENT",
                    "PROPERTY_NAME_SENDER",
					"PROPERTY_CITY_RECIPIENT.name",
					"PROPERTY_PLACES",
					"PROPERTY_WEIGHT",
					"PROPERTY_DIMENSIONS",
					"PROPERTY_STATE",
					"PROPERTY_STATE_DESCR",
					"PROPERTY_RATE",
					"PROPERTY_BRANCH.NAME",
					"PROPERTY_PACK_DESCRIPTION",
					"PROPERTY_WHOSE_ORDER.NAME",
					"PROPERTY_PAYS"
				)
			);
			while ($ob = $res->GetNextElement())
			{
				$a = $ob->GetFields();
				
				$a['ColorRow'] = '';
				$a['state_icon'] = '<span class="glyphicon glyphicon-new-window" aria-hidden="true" data-toggle="tooltip" data-placement="right" title=""></span>';
				$a['state_text'] = $a['PROPERTY_STATE_VALUE'];
				$a["PROPERTY_OB_WEIGHT"] = 0;
				if (strlen($a['PROPERTY_PACK_DESCRIPTION_VALUE']))
				{
					$a['PACK_DESCR'] = json_decode(htmlspecialcharsBack($a['PROPERTY_PACK_DESCRIPTION_VALUE']), true);
					foreach ($a['PACK_DESCR'] as $k => $str)
					{
						$a["PROPERTY_OB_WEIGHT"] = $a["PROPERTY_OB_WEIGHT"] + $str['gabweight'];
					}
				}
				else
				{
					if (is_array($a['PROPERTY_DIMENSIONS_VALUE']))
					{
						$w = 1;
						for ($i = 0; $i<3; $i++)
						{
							$w = $w*$a['PROPERTY_DIMENSIONS_VALUE'][$i];
						}
						$a["PROPERTY_OB_WEIGHT"] = $w/$arResult['CURRENT_CLIENT_COEFFICIENT_VW'];
					}
				}
				$arResult['REQUESTS'][] = $a;
			}
			if ($arParams['TYPE'] == 53)
			{
				$arParamsJson = array(
					'INN' => $arResult['CURRENT_CLIENT_INN'],
					'StartDate' => $arResult['LIST_FROM_DATE_FOR_1C'],
					'EndDate' => $arResult['LIST_TO_DATE_FOR_1C'],
					'NumPage' => 0,
					'DocsToPage' => 10000,
					'Type' => 1
				);
				$result = $client->GetDocsListAgent($arParamsJson);
			}
			else
			{
				$arParamsJson = array(
					'INN' => trim($arResult['CURRENT_CLIENT_INN']),
					'BranchID' => ($arResult['CURRENT_BRANCH'] > 0) ? iconv('windows-1251','utf-8',$arResult['BRANCH_INFO']['PROPERTY_IN_1C_CODE_VALUE']) : '',
					'BranchPrefix' => ($arResult['CURRENT_BRANCH'] > 0) ? iconv('windows-1251','utf-8',$arResult['BRANCH_INFO']['PROPERTY_IN_1C_PREFIX_VALUE']) : '', 
					'StartDate' => $arResult['LIST_FROM_DATE_FOR_1C'],
					'EndDate' => $arResult['LIST_TO_DATE_FOR_1C'],
					'NumPage' => 0,
					'DocsToPage' => 10000
				);
				$result = $client->GetDocsListClient($arParamsJson);
			}
            $mResult = $result->return;
            $obj = json_decode($mResult, true);
			/*
			if ($arResult["USER_ID"] == 1746)
			{
				echo '<pre>';
				print_r($arParamsJson);
				print_r($mResult);
				echo '</pre>';
			}
            if ($arResult['ADMIN_AGENT'])
            {
            echo '<pre>';
            print_r($arParamsJson);
            print_r($obj);
            echo '</pre>';
            }
			*/
            $ind_nakl = 0;
            foreach ($obj['Docs'] as $d)
            {
                $a = array(
                    'ID' => (intval($d['ID']) > 0) ? intval($d['ID']) : 'naklid_'.$ind_nakl,
					'ID_SITE' => (intval($d['ID']) > 0) ? intval($d['ID']) : 0,
                    'NAME' => iconv('utf-8', 'windows-1251', $d['NumDoc']),
                    'DATE_CREATE' => iconv('utf-8', 'windows-1251', substr($d['DateDoc'],8,2).'.'.substr($d['DateDoc'],5,2).'.'.substr($d['DateDoc'],0,4)),
                    'PROPERTY_STATE_ENUM_ID' => 258,
                    'ColorRow' => ($arParams['TYPE'] == 53) ? 'warning' : '',
                    'state_icon' => '',
                    'PROPERTY_BRANCH_NAME' => iconv('utf-8', 'windows-1251', $d['ZakazName']),
                    'PROPERTY_CITY_SENDER_NAME' => '',
                    'PROPERTY_CITY_RECIPIENT_NAME' => '',
                    'PROPERTY_COMPANY_SENDER_VALUE' => iconv('utf-8', 'windows-1251', $d['CompanySender']),
                    'PROPERTY_COMPANY_RECIPIENT_VALUE' => iconv('utf-8', 'windows-1251', $d['CompanyRecipient']),
                    'PROPERTY_PLACES_VALUE' => 0,
                    'PROPERTY_WEIGHT_VALUE' => 0,
                    'PROPERTY_OB_WEIGHT' => 0,
                    'PROPERTY_RATE_VALUE' => floatval(str_replace(',','.',$d['Tarif'])),
                    'PROPERTY_STATE_VALUE' => 'Принято',
                    'PROPERTY_STATE_DESCR_VALUE' => '',
                    'PROPERTY_NAME_RECIPIENT_VALUE' => iconv('utf-8', 'windows-1251', $d['NameRecipient']),
                    'PROPERTY_NAME_SENDER_VALUE' => iconv('utf-8', 'windows-1251', $d['NameSender']),
                    //'state_text' => 'Принято',
					'start_date'=> strlen($d['Date_Create']) ? substr($d['Date_Create'],8,2).'.'.substr($d['Date_Create'],5,2).'.'.substr($d['Date_Create'],0,4) : $d['DateDoc'],
					'ZakazName' => iconv('utf-8', 'windows-1251', $d['ZakazName']),
					'Manager' => iconv('utf-8', 'windows-1251', $d['Manager']),
                );
                if (intval($d['CitySender']) > 0)
                {
                    $rr = CIBlockElement::GetByID(intval($d['CitySender']));
                    if($ar_rr = $rr->GetNext())
                    {
                        $a['PROPERTY_CITY_SENDER_NAME'] = $ar_rr['NAME'];
                    }
                }
                if (intval($d['CityRecipient']) > 0)
                {
                    $rr = CIBlockElement::GetByID(intval($d['CityRecipient']));
                    if($ar_rr = $rr->GetNext())
                    {
                        $a['PROPERTY_CITY_RECIPIENT_NAME'] = $ar_rr['NAME'];
                    }
                }
                foreach ($d['Dimensions'] as $dimensions)
                {
                    $a['PROPERTY_PLACES_VALUE'] = $a['PROPERTY_PLACES_VALUE'] + $dimensions['Places'];
                    $a['PROPERTY_WEIGHT_VALUE'] = $a['PROPERTY_WEIGHT_VALUE'] + floatval(str_replace(',','.',$dimensions['Weight']));
					if (floatval(str_replace(',','.',$dimensions['WeightV'])) > 0)
					{
						$a['PROPERTY_OB_WEIGHT'] = floatval(str_replace(',','.',$dimensions['WeightV']));
					}
					else
					{
	                    $a['PROPERTY_OB_WEIGHT'] = $a['PROPERTY_OB_WEIGHT'] + (floatval(str_replace(',','.',$dimensions['Size_1']))*floatval(str_replace(',','.',$dimensions['Size_2']))*floatval(str_replace(',','.',$dimensions['Size_3'])))/$arResult['CURRENT_CLIENT_COEFFICIENT_VW'];
					}
                }
                foreach ($d['Events'] as $ev)
                {
                    $a['PROPERTY_STATE_VALUE'] = iconv('utf-8', 'windows-1251', $ev['Event']);
                    $a['PROPERTY_STATE_DESCR_VALUE'] = iconv('utf-8', 'windows-1251', $ev['InfoEvent']);
                    $a['Events'][] = array(
                        'Date' => $ev['DateEvent'].'&nbsp;'.substr($ev['TimeEvent'],0,5),
                        'Event' => iconv('utf-8', 'windows-1251', $ev['Event']),
                        'InfoEvent' => iconv('utf-8', 'windows-1251', $ev['InfoEvent'])
                    );
                }
                if (($a['ID'] == 0) || (!strlen($a['PROPERTY_COMPANY_SENDER_VALUE'])) || (!strlen($a['PROPERTY_COMPANY_RECIPIENT_VALUE'])))
                {
                    $filter = array("IBLOCK_ID" => 83, "PROPERTY_CREATOR" => intval($arResult['CURRENT_CLIENT']), "ACTIVE" => "Y");
                    if (intval($a['ID']) > 0)
                    {
                        $filter['ID'] = intval($a['ID']);
                    }
                    else
                    {
                        $filter['NAME'] = $a["NAME"];
                    }
                    if (intval($arResult['CURRENT_BRANCH']) > 0)
                    {
                        $filter["PROPERTY_BRANCH"] = intval($arResult['CURRENT_BRANCH']);
                    }
                    $res = CIBlockElement::GetList(array("id" => "desc"), $filter, false, array("nTopCount"=>1), array("ID","PROPERTY_COMPANY_SENDER","PROPERTY_COMPANY_RECIPIENT"));
                    if($ob = $res->GetNextElement())
                    {
                        $arFields = $ob->GetFields();
                        $a['ID'] = ($a['ID'] == 0) ? $arFields['ID'] : $a['ID'];
                        $a['PROPERTY_COMPANY_SENDER_VALUE'] = (!strlen($a['PROPERTY_COMPANY_SENDER_VALUE'])) ? $arFields['PROPERTY_COMPANY_SENDER_VALUE'] : $a['PROPERTY_COMPANY_SENDER_VALUE'];
                        $a['PROPERTY_COMPANY_RECIPIENT_VALUE'] = (!strlen($a['PROPERTY_COMPANY_RECIPIENT_VALUE'])) ? $arFields['PROPERTY_COMPANY_RECIPIENT_VALUE'] : $a['PROPERTY_COMPANY_RECIPIENT_VALUE'];
                        
                    }
                }
				if ($agent_type == 242)
				{
					$a['state_icon'] = '<span class="glyphicon glyphicon-send" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр трекинга"></span>';
					$a['state_text'] = 'Доставляется';
				}
				else
				{
					$a['state_icon'] = '<span class="glyphicon glyphicon-new-window" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр трекинга"></span>';
					$a['state_text'] = $a['PROPERTY_STATE_VALUE'];
				}
                switch ($a['PROPERTY_STATE_VALUE'])
                {
                    case 'В офисе до востребования':
                        $a['PROPERTY_STATE_ENUM_ID'] = 270;
                        break;
                    case 'Возврат интернет-магазину':
                        $a['PROPERTY_STATE_ENUM_ID'] = 271;
                        break;
                    case 'Возврат по просьбе отправителя':
                        $a['PROPERTY_STATE_ENUM_ID'] = 272;
                        break;
                    case 'Выдано курьеру на маршрут':
                        $a['PROPERTY_STATE_ENUM_ID'] = 273;
                        break;
                    case 'Выдано на областную доставку':
                        $a['PROPERTY_STATE_ENUM_ID'] = 274;
                        break;
                    case 'Доставлено':
                        $a['PROPERTY_STATE_ENUM_ID'] = 275;
                        break;
                    case 'Исключительная ситуация!':
                        $a['PROPERTY_STATE_ENUM_ID'] = 276;
                        break;
                    case 'Оприходовано офисом':
                        $a['PROPERTY_STATE_ENUM_ID'] = 277;
                        break;
                    case 'Отправлено в город':
                        $a['PROPERTY_STATE_ENUM_ID'] = 278;
                        break;
                    case 'Уничтожено по просьбе заказчика':
                        $a['PROPERTY_STATE_ENUM_ID'] = 279;
                        break;
                }
                $arResult['ARCHIVE'][] = $a;
                $ind_nakl++;
            }
            foreach ($arResult['ARCHIVE'] as $k => $a)
            {
				if ($agent_type == 242)
				{
					switch ($a['PROPERTY_STATE_ENUM_ID'])
					{
						case 276:
							$arResult['ARCHIVE'][$k]['ColorRow'] = 'danger';
							$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр трекинга"></span>';
							$arResult['ARCHIVE'][$k]['state_text'] = $a['PROPERTY_STATE_DESCR_VALUE'];
							break;
						case 275:
							$arResult['ARCHIVE'][$k]['ColorRow'] = 'supersuccess';
							$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-check" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр трекинга"></span>';	
							$arResult['ARCHIVE'][$k]['state_text'] = $a['PROPERTY_STATE_VALUE'];
					}
				}
				else
				{
					switch ($a['PROPERTY_STATE_ENUM_ID'])
					{
						case 278:
							$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-send" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр трекинга"></span>';
							$arResult['ARCHIVE'][$k]['state_text'] = $a['PROPERTY_STATE_DESCR_VALUE'];
							break;
						case 273:
							$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-road" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр трекинга"></span>';
							$arResult['ARCHIVE'][$k]['state_text'] = 'Выдано на маршрут';
							break;
						case 276:
							$arResult['ARCHIVE'][$k]['ColorRow'] = 'danger';
							$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр трекинга"></span>';
							$arResult['ARCHIVE'][$k]['state_text'] = $a['PROPERTY_STATE_DESCR_VALUE'];
							break;
						case 258:
							$arResult['ARCHIVE'][$k]['ColorRow'] = 'success';
							$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-log-in" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр трекинга"></span>';
							break;
						case 277:
							$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-download-alt" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр трекинга"></span>';
							$arResult['ARCHIVE'][$k]['state_text'] = $a['PROPERTY_STATE_DESCR_VALUE'];
							break;
						case 275:
							$arResult['ARCHIVE'][$k]['ColorRow'] = 'supersuccess';
							$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-check" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр трекинга"></span>';			
				    }
				}
            }
            
            //NOTE Формирование JSON-строки для xls-файла
			if ($arParams['TYPE'] == 53)
			{
				$arARCHIVEutf = array( 
					array(
						iconv('windows-1251', 'utf-8', 'Номер накладной'),
						iconv('windows-1251', 'utf-8', 'Дата'),
						iconv('windows-1251', 'utf-8', 'Клиент'),
						iconv('windows-1251', 'utf-8', 'Город отправителя'),
						iconv('windows-1251', 'utf-8', 'Компания отправителя'),
						iconv('windows-1251', 'utf-8', 'Город получателя'),
						iconv('windows-1251', 'utf-8', 'Компания получателя'),
						iconv('windows-1251', 'utf-8', 'Кол.'),
						iconv('windows-1251', 'utf-8', 'Вес'),
						iconv('windows-1251', 'utf-8', 'Об. вес'),
						iconv('windows-1251', 'utf-8', 'Статус'),
						iconv('windows-1251', 'utf-8', 'Отв. менеджер')
					)
				);
				$k = 1;
				foreach ($arResult['REQUESTS'] as $r)
				{
					//TODO Добавить Чей Заказ
					$w_order = strlen($r['PROPERTY_WHOSE_ORDER_NAME']) ? $r['PROPERTY_WHOSE_ORDER_NAME'] : $r['PROPERTY_PAYS_VALUE'];
					$arARCHIVEutf[$k] = array(
						iconv('windows-1251', 'utf-8', $r['NAME']),
						substr($r['DATE_CREATE'],0,10),
						iconv('windows-1251', 'utf-8', $w_order),
						iconv('windows-1251', 'utf-8', $r['PROPERTY_CITY_SENDER_NAME']),
						iconv('windows-1251', 'utf-8', $r['PROPERTY_COMPANY_SENDER_VALUE']),
						iconv('windows-1251', 'utf-8', $r['PROPERTY_CITY_RECIPIENT_NAME']),
						iconv('windows-1251', 'utf-8', $r['PROPERTY_COMPANY_RECIPIENT_VALUE']),
						iconv('windows-1251', 'utf-8', $r['PROPERTY_PLACES_VALUE']),
						iconv('windows-1251', 'utf-8', $r['PROPERTY_WEIGHT_VALUE']),
						iconv('windows-1251', 'utf-8', $r['PROPERTY_OB_WEIGHT']),
						iconv('windows-1251', 'utf-8', $r['state_text']),
						''
					);
					$k++;
				}
				foreach ($arResult['ARCHIVE'] as $r)
				{
					$arARCHIVEutf[$k] = array(
						iconv('windows-1251', 'utf-8', $r['NAME']),
						iconv('windows-1251', 'utf-8', $r['start_date']),
						iconv('windows-1251', 'utf-8', $r['ZakazName']),
						iconv('windows-1251', 'utf-8', $r['PROPERTY_CITY_SENDER_NAME']),
						iconv('windows-1251', 'utf-8', $r['PROPERTY_COMPANY_SENDER_VALUE']),
						iconv('windows-1251', 'utf-8', $r['PROPERTY_CITY_RECIPIENT_NAME']),
						iconv('windows-1251', 'utf-8', $r['PROPERTY_COMPANY_RECIPIENT_VALUE']),
						iconv('windows-1251', 'utf-8', $r['PROPERTY_PLACES_VALUE']),
						iconv('windows-1251', 'utf-8', str_replace(',','.',$r['PROPERTY_WEIGHT_VALUE'])),
						iconv('windows-1251', 'utf-8', str_replace(',','.',$r['PROPERTY_OB_WEIGHT'])),
						iconv('windows-1251', 'utf-8', $r['state_text']),
						iconv('windows-1251', 'utf-8', $r['Manager'])
					);
					$k++;
				}
			}
			else
			{
				$arARCHIVEutf = array( 
					array(
						iconv('windows-1251', 'utf-8', 'Номер накладной'),
						iconv('windows-1251', 'utf-8', 'Статус'),
						iconv('windows-1251', 'utf-8', 'Дата'),
						iconv('windows-1251', 'utf-8', 'Филиал'),
						iconv('windows-1251', 'utf-8', 'Город отправителя'),
						iconv('windows-1251', 'utf-8', 'Компания отправителя'),
						iconv('windows-1251', 'utf-8', 'Город получателя'),
						iconv('windows-1251', 'utf-8', 'Компания получателя'),
						iconv('windows-1251', 'utf-8', 'Получатель'),
						iconv('windows-1251', 'utf-8', 'Кол.'),
						iconv('windows-1251', 'utf-8', 'Вес'),
						iconv('windows-1251', 'utf-8', 'Об. вес'),
						iconv('windows-1251', 'utf-8', 'Тариф за услуги')
					)
				);
				if ((!$arResult['LIST_OF_BRANCHES']) || ($arResult['USER_IN_BRANCH']))
				{
					unset($arARCHIVEutf[0][3]);
				}
				$k = 1;
				foreach ($arResult['REQUESTS'] as $r)
				{
					$arARCHIVEutf[$k] = array(
						iconv('windows-1251', 'utf-8', $r['NAME']),
						iconv('windows-1251', 'utf-8', $r['state_text']),
						substr($r['DATE_CREATE'],0,10)
					);
					if (($arResult['LIST_OF_BRANCHES']) && (!$arResult['USER_IN_BRANCH']))
					{
						$arARCHIVEutf[$k][] = iconv('windows-1251', 'utf-8', $r['PROPERTY_BRANCH_NAME']);
					}
					$arARCHIVEutf[$k][] = iconv('windows-1251', 'utf-8', $r['PROPERTY_CITY_SENDER_NAME']);
					$arARCHIVEutf[$k][] = iconv('windows-1251', 'utf-8', $r['PROPERTY_COMPANY_SENDER_VALUE']);
					$arARCHIVEutf[$k][] = iconv('windows-1251', 'utf-8', $r['PROPERTY_CITY_RECIPIENT_NAME']);
					$arARCHIVEutf[$k][] = iconv('windows-1251', 'utf-8', $r['PROPERTY_COMPANY_RECIPIENT_VALUE']);
					$arARCHIVEutf[$k][] = iconv('windows-1251', 'utf-8', $r['PROPERTY_NAME_RECIPIENT_VALUE']);
					$arARCHIVEutf[$k][] = iconv('windows-1251', 'utf-8', $r['PROPERTY_PLACES_VALUE']);
					$arARCHIVEutf[$k][] = iconv('windows-1251', 'utf-8', $r['PROPERTY_WEIGHT_VALUE']);
					$arARCHIVEutf[$k][] = iconv('windows-1251', 'utf-8', $r['PROPERTY_OB_WEIGHT']);
					$arARCHIVEutf[$k][] = iconv('windows-1251', 'utf-8', $r['PROPERTY_RATE_VALUE']);
					$k++;
				}
				foreach ($arResult['ARCHIVE'] as $r)
				{
					$arARCHIVEutf[$k] = array(
						iconv('windows-1251', 'utf-8', $r['NAME']),
						iconv('windows-1251', 'utf-8', $r['state_text']),
						substr($r['DATE_CREATE'],0,10)
					);
					if (($arResult['LIST_OF_BRANCHES']) && (!$arResult['USER_IN_BRANCH']))
					{
						$arARCHIVEutf[$k][] = iconv('windows-1251', 'utf-8', $r['PROPERTY_BRANCH_NAME']);
					}
					$arARCHIVEutf[$k][] = iconv('windows-1251', 'utf-8', $r['PROPERTY_CITY_SENDER_NAME']);
					$arARCHIVEutf[$k][] = iconv('windows-1251', 'utf-8', $r['PROPERTY_COMPANY_SENDER_VALUE']);
					$arARCHIVEutf[$k][] = iconv('windows-1251', 'utf-8', $r['PROPERTY_CITY_RECIPIENT_NAME']);
					$arARCHIVEutf[$k][] = iconv('windows-1251', 'utf-8', $r['PROPERTY_COMPANY_RECIPIENT_VALUE']);
					$arARCHIVEutf[$k][] = iconv('windows-1251', 'utf-8', $r['PROPERTY_NAME_RECIPIENT_VALUE']);
					$arARCHIVEutf[$k][] = iconv('windows-1251', 'utf-8', $r['PROPERTY_PLACES_VALUE']);
					$arARCHIVEutf[$k][] = iconv('windows-1251', 'utf-8', str_replace(',','.',$r['PROPERTY_WEIGHT_VALUE']));
					$arARCHIVEutf[$k][] = iconv('windows-1251', 'utf-8', str_replace(',','.',$r['PROPERTY_OB_WEIGHT']));
					$arARCHIVEutf[$k][] = iconv('windows-1251', 'utf-8', $r['PROPERTY_RATE_VALUE']);
					$k++;
				}
			}
            $arResult['ARCHIVE_STR_JSON'] = json_encode($arARCHIVEutf);

		}
		// TODO Подтягивать лимиты из записи филиала, фильтр периода по текущему месяцу
		if ($arResult['LIMITS_OF_BRANCHES'])
		{
			$arResult['All_LIMIT'] = 0;
			$qw = GetQuarter($arResult['CURRENT_MONTH']);
			if (intval($arResult['CURRENT_BRANCH']) > 0)
			{
				$arResult['All_LIMIT'] = $arResult['LIMITS_OF_BRANCHES'][$arResult['CURRENT_BRANCH']][$qw];
				$search_lims = GetLimitsOfBranch($arResult['CURRENT_CLIENT'], $arResult['CURRENT_BRANCH'], $qw, $arResult['CURRENT_YEAR']);
			}
			else
			{
				foreach ($arResult['LIMITS_OF_BRANCHES'] as $l)
				{
					$arResult['All_LIMIT'] = $arResult['All_LIMIT'] + $l[$qw];
				}
				$search_lims = GetLimitsOfBranch($arResult['CURRENT_CLIENT'], false, $qw, $arResult['CURRENT_YEAR']);
			}
			$arResult['All_SPENT'] = $search_lims["SPENT"];
			$arResult['All_LEFT'] = $search_lims["LEFT"];
			$arResult['LABEL_CLASS'] = 'label-info';
			$arResult['All_PERSENT'] = '';
			if ($arResult['All_LIMIT'] > 0)
			{
				$arResult['All_PERSENT'] = number_format((($arResult['All_SPENT']/$arResult['All_LIMIT'])*100), 2, ',', '').'%';
				if (($arResult['All_SPENT']/$arResult['All_LIMIT']) > 1)
				{
					$arResult['LABEL_CLASS'] = 'label-danger';
				}
			}
			else
			{
				if ($arResult['All_SPENT'] > 0)
				{
					$arResult['LABEL_CLASS'] = 'label-danger';
					$arResult['All_PERSENT'] = '!!!';
				}
				else
				{
					$arResult['LABEL_CLASS'] = 'label-warning';
					$arResult['All_PERSENT'] = '0,00%';
				}
			}
			$arQw = array('I','II','III','IV');
			$arResult['QW_TEXT'] = $arQw[$qw];
		}
		
		$arResult['TITLE'] = GetMessage('TITLE_MODE_LIST');
		$APPLICATION->SetTitle(GetMessage('TITLE_MODE_LIST'));
	
	}
	
	if ($arResult['MODE'] == 'print')
	{
		if (!$arResult['ADMIN_AGENT'])
		{
			$arResult['CURRENT_CLIENT'] = $agent_id;
		}
		else
		{
			if (strlen($_SESSION['CURRENT_CLIENT']))
			{
				$arResult['CURRENT_CLIENT'] = $_SESSION['CURRENT_CLIENT'];
			}
			else
			{
				$arResult['CURRENT_CLIENT'] = 0;
			}
		}
		$arResult['CURRENT_CLIENT_COEFFICIENT_VW'] = WhatIsGabWeightCompany($arResult['CURRENT_CLIENT']);
        $idlogoprint = GetSettingValue(716, false, $arResult['UK']);
        $arResult['LOGO_PRINT'] = CFile::GetPath($idlogoprint);
        $arResult['ADRESS_PRINT'] = GetSettingValue(718, false, $arResult['UK']);
		$arResult['INVOICE'] = false;
		$id_reqv = intval($_GET['id']);
		if ($id_reqv > 0)
		{
			$filter = array("IBLOCK_ID" => 83, "ID" => $id_reqv, "PROPERTY_CREATOR" => $arResult["CURRENT_CLIENT"]);
			$res = CIBlockElement::GetList(
				array("id" => "desc"), 
				$filter, 
				false, 
				false, 
				array(
					"ID",
					"NAME",
					"PROPERTY_NAME_SENDER",
					"PROPERTY_PHONE_SENDER",
					"PROPERTY_COMPANY_SENDER",
					"PROPERTY_CITY_SENDER",
					"PROPERTY_INDEX_SENDER",
					"PROPERTY_ADRESS_SENDER",
					"PROPERTY_NAME_RECIPIENT",
					"PROPERTY_PHONE_RECIPIENT",
					"PROPERTY_COMPANY_RECIPIENT",
					"PROPERTY_CITY_RECIPIENT",
					"PROPERTY_INDEX_RECIPIENT",
					"PROPERTY_ADRESS_RECIPIENT",
					"PROPERTY_TYPE_DELIVERY",
					"PROPERTY_TYPE_PAYS",
					"PROPERTY_PAYS",
					"PROPERTY_WHO_DELIVERY",
					"PROPERTY_IN_DATE_DELIVERY",
					"PROPERTY_IN_TIME_DELIVERY",
					"PROPERTY_PAYMENT",
					"PROPERTY_TYPE_PACK",
					"PROPERTY_PLACES",
					"PROPERTY_WEIGHT",
					"PROPERTY_COST",
					"PROPERTY_FOR_PAYMENT",
					"PROPERTY_DIMENSIONS",
					"PROPERTY_STATE",
					"PROPERTY_INSTRUCTIONS",
					"PROPERTY_PACK_DESCRIPTION"
				)
			);
			if ($ob = $res->GetNextElement())
			{
				$r = $ob->GetFields();
				$r['PROPERTY_CITY_SENDER'] = GetFullNameOfCity($r['PROPERTY_CITY_SENDER_VALUE']);
				$r['PROPERTY_CITY_RECIPIENT'] = GetFullNameOfCity($r['PROPERTY_CITY_RECIPIENT_VALUE']);
				$r['PROPERTY_CITY_SENDER_AR'] = explode(', ', $r['PROPERTY_CITY_SENDER']);
				$r['PROPERTY_CITY_RECIPIENT_AR'] = explode(', ', $r['PROPERTY_CITY_RECIPIENT']);
				$r["PROPERTY_OB_WEIGHT"] = 0;
				if (strlen($r['PROPERTY_PACK_DESCRIPTION_VALUE']))
				{
					$r['PACK_DESCR'] = json_decode(htmlspecialcharsBack($r['PROPERTY_PACK_DESCRIPTION_VALUE']), true);
					foreach ($r['PACK_DESCR'] as $k => $str)
					{
						$r["PROPERTY_OB_WEIGHT"] = $r["PROPERTY_OB_WEIGHT"] + $str['gabweight'];
						$r['PACK_DESCR'][$k]['name'] = iconv('utf-8','windows-1251',$str['name']);
						$r['PACK_DESCR'][$k]['place'] = (intval($r['PACK_DESCR'][$k]['place']) > 0) ? intval($r['PACK_DESCR'][$k]['place']) : "";
						$r['PACK_DESCR'][$k]['weight'] = ($r['PACK_DESCR'][$k]['weight'] > 0) ? WeightFormat($r['PACK_DESCR'][$k]['weight'], false) : "";
						$r['PACK_DESCR'][$k]['sizes'] = ($r['PACK_DESCR'][$k]['gabweight'] > 0) ? $r['PACK_DESCR'][$k]['size'][0].' х '.$r['PACK_DESCR'][$k]['size'][1].' х '.$r['PACK_DESCR'][$k]['size'][2] : "";
					}
				}
				else
				{
					if (is_array($r['PROPERTY_DIMENSIONS_VALUE']))
					{
						$w = 1;
						for ($i = 0; $i<3; $i++)
						{
							$w = $w*$r['PROPERTY_DIMENSIONS_VALUE'][$i];
						}
						$r["PROPERTY_OB_WEIGHT"] = $w/$arResult['CURRENT_CLIENT_COEFFICIENT_VW'];
					}
					$r['PACK_DESCR'][0] = array(
						'name' => $r['PROPERTY_TYPE_PACK_VALUE'],
						'place' => $r['PROPERTY_PLACES_VALUE'],
						'weight' => WeightFormat($r['PROPERTY_WEIGHT_VALUE'],false),
						'size' => $r['PROPERTY_DIMENSIONS_VALUE'],
						'gabweight' => $r['PROPERTY_OB_WEIGHT'],
						'sizes' => ($r['PROPERTY_OB_WEIGHT'] > 0) ?  $r['PROPERTY_DIMENSIONS_VALUE'][0].' х '.$r['PROPERTY_DIMENSIONS_VALUE'][1].' х '.$r['PROPERTY_DIMENSIONS_VALUE'][2] : ""
					);			
				}
				$r["PROPERTY_OB_WEIGHT"] = WeightFormat($r["PROPERTY_OB_WEIGHT"], false);
				$r["PROPERTY_WEIGHT_VALUE"] = WeightFormat($r["PROPERTY_WEIGHT_VALUE"], false);
				$arResult['INVOICE'] = $r;
				$arResult['TITLE'] = $arResult['INVOICE']['NAME'];
				$APPLICATION->SetTitle($arResult['INVOICE']['NAME']);
			}
		}
		else
		{
			$arResult['TITLE'] = GetMessage('ERR_NO_REQUEST');
			$APPLICATION->SetTitle(GetMessage('ERR_NO_REQUEST'));
		}
	}
	
	if (($arResult['MODE'] == 'invoice') || ($arResult['MODE'] == 'invoice_modal'))
	{
		if (!$arResult['ADMIN_AGENT'])
		{
			$arResult['CURRENT_CLIENT'] = $agent_id;
		}
		else
		{
			if (strlen($_SESSION['CURRENT_CLIENT']))
			{
				$arResult['CURRENT_CLIENT'] = $_SESSION['CURRENT_CLIENT'];
			}
			else
			{
				$arResult['CURRENT_CLIENT'] = 0;
			}
		}
		$arResult['INVOICE'] = false;
		$id_reqv = intval($_GET['id']);
		if ($id_reqv > 0)
		{
			$res = CIBlockElement::GetList(
				array("id" => "desc"), 
				array("IBLOCK_ID" => 83, "ID" => $id_reqv, "PROPERTY_CREATOR" => $arResult["CURRENT_CLIENT"]), 
				false, 
				false, 
				array(
					"ID",
					"NAME",
					"PROPERTY_NAME_SENDER",
					"PROPERTY_PHONE_SENDER",
					"PROPERTY_COMPANY_SENDER",
					"PROPERTY_CITY_SENDER",
					"PROPERTY_INDEX_SENDER",
					"PROPERTY_ADRESS_SENDER",
					"PROPERTY_NAME_RECIPIENT",
					"PROPERTY_PHONE_RECIPIENT",
					"PROPERTY_COMPANY_RECIPIENT",
					"PROPERTY_CITY_RECIPIENT",
					"PROPERTY_INDEX_RECIPIENT",
					"PROPERTY_ADRESS_RECIPIENT",
					"PROPERTY_TYPE_DELIVERY",
					"PROPERTY_TYPE_PACK",
					"PROPERTY_WHO_DELIVERY",
					"PROPERTY_IN_DATE_DELIVERY",
					"PROPERTY_IN_TIME_DELIVERY",
					"PROPERTY_TYPE_PAYS",
					"PROPERTY_PAYS",
					"PROPERTY_PAYMENT",
					"PROPERTY_FOR_PAYMENT",
					"PROPERTY_COST",
					"PROPERTY_PLACES",
					"PROPERTY_WEIGHT",
					"PROPERTY_DIMENSIONS",
					"PROPERTY_STATE",
					"PROPERTY_INSTRUCTIONS",
					"PROPERTY_CREATOR.NAME",
					"PROPERTY_BRANCH.NAME",
					"PROPERTY_CONTRACT.NAME",
					"PROPERTY_RATE",
					"PROPERTY_PACK_DESCRIPTION",
					"PROPERTY_PACK_GOODS"
				)
			);
			if ($ob = $res->GetNextElement())
			{
				$r = $ob->GetFields();
				$r['PROPERTY_CITY_SENDER'] = GetFullNameOfCity($r['PROPERTY_CITY_SENDER_VALUE']);
				$r['PROPERTY_CITY_RECIPIENT'] = GetFullNameOfCity($r['PROPERTY_CITY_RECIPIENT_VALUE']);
				$r["PROPERTY_OB_WEIGHT"] = 0;
				if (strlen($r['PROPERTY_PACK_DESCRIPTION_VALUE']))
				{
					$r['PACK_DESCR'] = json_decode(htmlspecialcharsBack($r['PROPERTY_PACK_DESCRIPTION_VALUE']), true);
					foreach ($r['PACK_DESCR'] as $k => $str)
					{
						$r['PACK_DESCR'][$k]['name'] = iconv('utf-8','windows-1251',$str['name']);
						$r['PACK_DESCR'][$k]['place'] = (intval($r['PACK_DESCR'][$k]['place']) > 0) ? intval($r['PACK_DESCR'][$k]['place']) : "";
						$r['PACK_DESCR'][$k]['weight'] = ($r['PACK_DESCR'][$k]['weight'] > 0) ? $r['PACK_DESCR'][$k]['weight'] : "";
						$r["PROPERTY_OB_WEIGHT"] = $r["PROPERTY_OB_WEIGHT"] + $r['PACK_DESCR'][$k]['gabweight'];
					}
				}
				else
				{
					if (is_array($r['PROPERTY_DIMENSIONS_VALUE']))
					{
						$w = 1;
						for ($i = 0; $i<3; $i++)
						{
							$w = $w*$r['PROPERTY_DIMENSIONS_VALUE'][$i];
						}
						$r["PROPERTY_OB_WEIGHT"] = $w/$arResult['CURRENT_CLIENT_COEFFICIENT_VW'];
						
					}		
					$r['PACK_DESCR'][0] = array(
						'name' => $r['PROPERTY_TYPE_PACK_VALUE'],
						'place' => $r['PROPERTY_PLACES_VALUE'],
						'weight' => $r['PROPERTY_WEIGHT_VALUE'],
						'size' => $r['PROPERTY_DIMENSIONS_VALUE'],
						'gabweight' => $r["PROPERTY_OB_WEIGHT"]
					);	
				}
				$r['PACK_GOODS'] = '';
				if (strlen($r['PROPERTY_PACK_GOODS_VALUE']))
				{
					$r['PACK_GOODS'] = json_decode(htmlspecialcharsBack($r['PROPERTY_PACK_GOODS_VALUE']), true);
					if ((is_array($r['PACK_GOODS'])) && (count($r['PACK_GOODS']) > 0))
					{
						foreach ($r['PACK_GOODS'] as $k => $str)
						{
							$r['PACK_GOODS'][$k]['GoodsName'] = iconv('utf-8','windows-1251',$str['GoodsName']);
							if (strlen(trim($r['PACK_GOODS'][$k]['GoodsName'])) == 0)
							{
								unset($r['PACK_GOODS'][$k]);
							}
						}
					}
				}
				$arResult['INVOICE'] = $r;
				$arResult['TITLE'] = $arResult['INVOICE']['NAME'];
				$APPLICATION->SetTitle($arResult['INVOICE']['NAME']);
			}
		}
		else
		{
			$arResult['TITLE'] = GetMessage('ERR_NO_REQUEST');
			$APPLICATION->SetTitle(GetMessage('ERR_NO_REQUEST'));
		}
	}
    
    if (($arResult['MODE'] == 'invoice1c_modal') || ($arResult['MODE'] == 'invoice1c_print'))
    {
        if (strlen(trim($_GET['f001'])))
        {
            $arParamsJson = array(
                'NumDoc' => trim($_GET['f001']),
            );
            $result_0 = $client->GetDocInfo($arParamsJson);
            $mResult_0 = $result_0->return;
            $obj_0 = json_decode($mResult_0, true);
            $arResult['REQUEST'] = false;
            $arResult['TITLE'] = 'Накладная не найдена';
            $APPLICATION->SetTitle($arResult['TITLE']);
            if ((is_array($obj_0)) && (count($obj_0) > 0))
            {
                $arResult['REQUEST'] = arFromUtfToWin($obj_0);
				if ((is_array($arResult['REQUEST']['Goods'])) && (count($arResult['REQUEST']['Goods']) > 0))
				{
					foreach ($arResult['REQUEST']['Goods'] as $k => $v)
					{
						if (strlen(trim($v['GoodsName'])) == 0)
						{
							unset($arResult['REQUEST']['Goods'][$k]);
						}
					}
				}
                $arResult['TITLE'] = 'Номер накладной: '.$arResult['REQUEST']['НомерНакладной'];
                if ($arResult['MODE'] == 'invoice1c_print')
                {
                    $idlogoprint = GetSettingValue(716, false, $arResult['UK']);
                    $arResult['LOGO_PRINT'] = CFile::GetPath($idlogoprint);
                    $arResult['ADRESS_PRINT'] = GetSettingValue(718, false, $arResult['UK']);
                    $arResult['REQUEST']['КоличествоМест'] = 0;
                    $arResult['REQUEST']['ВесОтправления'] = 0;
                    $arResult['REQUEST']['ВесОтправленияОбъемный'] = 0;
                    foreach ($arResult['REQUEST']['Габариты'] as $k => $v)
                    {
                        if ((strlen($v['Длина'])) && (strlen($v['Ширина'])) && (strlen($v['Высота'])))
                        {
                            $arResult['REQUEST']['Габариты'][$k]['sizes'] = $v['Длина'].'x'.$v['Ширина'].'x'.$v['Высота'];
                        }
                        $arResult['REQUEST']['КоличествоМест'] = $arResult['REQUEST']['КоличествоМест'] + $v['КоличествоМест'];
                        $arResult['REQUEST']['ВесОтправления'] = $arResult['REQUEST']['ВесОтправления'] + $v['ВесОтправления'];
                        $arResult['REQUEST']['ВесОтправленияОбъемный'] = $arResult['REQUEST']['ВесОтправленияОбъемный'] + $v['ВесОтправленияОбъемный'];
                    }
                }
                $APPLICATION->SetTitle($arResult['REQUEST']['НомерНакладной']);
            }
        }
    }
	
	if ($arResult['MODE'] == 'edit')
	{
		if (!$arResult['ADMIN_AGENT'])
		{
			$arResult['CURRENT_CLIENT'] = $agent_id;
			$arResult['CURRENT_CLIENT_INFO'] = $arResult['AGENT'];
		}
		else
		{
			if (strlen($_SESSION['CURRENT_CLIENT']))
			{
				$arResult['CURRENT_CLIENT'] = $_SESSION['CURRENT_CLIENT'];
				$arResult['CURRENT_CLIENT_INFO'] = GetCompany($arResult['CURRENT_CLIENT']);
			}
			else
			{
				$arResult['CURRENT_CLIENT'] = 0;
				$arResult['CURRENT_CLIENT_INFO'] = false;
			}
		}
		if (intval($arResult['CURRENT_CLIENT']) == 0)
		{
			$arResult['OPEN'] = false;
			if ($arResult['ADMIN_AGENT'])
			{
				$arResult["WARNINGS"][] = GetMessage('ERR_OPEN_ADMIN');
			}
			else
			{
				$arResult["WARNINGS"][] = GetMessage('ERR_OPEN');
			}
		}
        if ($arResult['CURRENT_CLIENT'] > 0)
        {
			$arResult['CURRENT_CLIENT_COEFFICIENT_VW'] = WhatIsGabWeightCompany($arResult['CURRENT_CLIENT']);
            $db_props = CIBlockElement::GetProperty(40, $arResult['CURRENT_CLIENT'], array("sort" => "asc"), array("CODE"=>"INN"));
            if($ar_props = $db_props->Fetch())
            {
                $arResult['CURRENT_CLIENT_INN'] = $ar_props["VALUE"];
            }
			if ($arParams['TYPE'] == 53)
			{
				$res = CIBlockElement::GetList(array("name"=>"asc"), array("IBLOCK_ID"=>40, "ACTIVE"=>"Y", 'PROPERTY_BY_AGENT' => $arResult['CURRENT_CLIENT']), false, false, array("ID", "NAME"));
				while($ob = $res->GetNextElement())
				{
					$arFields = $ob->GetFields();
					$arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT'][$arFields['ID']] = $arFields['NAME'];
				}
			}
			else
			{
				if ((count($arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT_VALUE']) > 0) && (is_array($arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT_VALUE'])))
				{
					foreach ($arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT_VALUE'] as $k)
					{
						$res = CIBlockElement::GetByID($k);
						if($ar_res = $res->GetNext())
						{
							$arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT'][$k] = $ar_res['NAME'];
						}

					}
				}
			}
        }
		
		if ((isset($_POST['save'])) || (isset($_POST['save-print'])) || (isset($_POST['save_ctrl'])))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$arPostLogsVal = array();
				foreach ($_POST as $k => $v)
				{
					if (is_array($v))
					{
						foreach ($v as $kk => $vv)
						{
							if (is_array($vv))
							{
								foreach ($vv as $kkk => $vvv)
								{
									$arPostLogsVal[$k.'_'.$kk.'_'.$kkk] = $vvv;
								}
							}
							else
							{
								$arPostLogsVal[$k.'_'.$kk] = $vv;
							}
						}
					}
					else
					{
						$arPostLogsVal[$k] = $v;
					}
				}
				AddToLogs('InvEditPostValues',$arPostLogsVal);
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
				$arResult["ERR_FIELDS"] = array();
				$arJsonDescr = array();
				$total_place = 0;
				$total_weight = 0;
				$total_gabweight = 0;
				foreach ($_POST['pack_description'] as $description_str)
				{
					$sizes = array();
					foreach ($description_str['size'] as $sz)
					{
						$sizes[] = floatval(str_replace(',','.',$sz));
					}
					$arCurStr = array(
						'name' => iconv('windows-1251','utf-8',$description_str['name']),
						'place' => intval($description_str['place']),
						'weight' => floatval(str_replace(',','.',$description_str['weight'])),
						'size' => $sizes,
						'gabweight' => (($sizes[0]*$sizes[1]*$sizes[2])/$arResult['CURRENT_CLIENT_COEFFICIENT_VW'])
					);
					$total_place = $total_place + $arCurStr['place'];
					$total_weight = $total_weight + $arCurStr['weight'];
					$total_gabweight = $total_gabweight + $arCurStr['gabweight'];
					$arJsonDescr[] = $arCurStr;
				}
                $arJsonGoods = array();
                foreach ($_POST['goods'] as $goods_str)
                {
                    $arJsonGoods[] = array(
                        'GoodsName' => iconv('windows-1251','utf-8',$goods_str['name']),
                        'Amount' => intval($goods_str['amount']),
                        'Price' => floatval(str_replace(',','.',$goods_str['price'])),
                        'Sum' => floatval(str_replace(',','.',$goods_str['sum'])),
                        'SumNDS' => floatval(str_replace(',','.',$goods_str['sumnds'])),
                        'PersentNDS' => intval($goods_str['persentnds'])
                    );
                }
				$arChanges = array(
					550 => deleteTabs($_POST['INDEX_SENDER']),
					556 => $_POST['INDEX_RECIPIENT'],
					560 => deleteTabs($_POST['IN_DATE_DELIVERY']),
					561 => deleteTabs($_POST['IN_TIME_DELIVERY']),
					565 => floatval(str_replace(',','.',$_POST['FOR_PAYMENT'])),
					733 => floatval(str_replace(',','.',$_POST['PAYMENT_COD'])),
					566 => floatval(str_replace(',','.',$_POST['COST'])),
					569 => $_POST['DIMENSIONS'],
					570 => array('VALUE' => array('TYPE' => 'text', 'TEXT' => deleteTabs($_POST['INSTRUCTIONS']))),
					682 => json_encode($arJsonDescr),
                    724 => json_encode($arJsonGoods),
					563 => '',
					737 => false
				);
				if (!strlen($_POST['NAME_SENDER']))
				{
					$arResult["ERR_FIELDS"]["NAME_SENDER"] = 'has-error';
				}
				else
				{
					$arChanges[546] = NewQuotes($_POST['NAME_SENDER']);
				}
				if (!strlen($_POST['PHONE_SENDER']))
				{
					$arResult["ERR_FIELDS"]["PHONE_SENDER"] = 'has-error';
				}
				else
				{
					$arChanges[547] = NewQuotes($_POST['PHONE_SENDER']);
				}
				if (!strlen($_POST['COMPANY_SENDER']))
				{
					$arResult["ERR_FIELDS"]["COMPANY_SENDER"] = 'has-error';
				}
				else
				{
					$arChanges[548] = NewQuotes($_POST['COMPANY_SENDER']);
				}
				if (!strlen($_POST['CITY_SENDER']))
				{
					$arResult["ERR_FIELDS"]["CITY_SENDER"] = 'has-error';
				}
				else
				{
					$city_sender = GetCityId(trim($_POST['CITY_SENDER']));
					if ($city_sender == 0)
					{
						$arResult["ERR_FIELDS"]["CITY_SENDER"] = 'has-error';
					}
					else
					{
						$arChanges[549] = $city_sender;
					}
				}
				if (!strlen($_POST['ADRESS_SENDER']))
				{
					$arResult["ERR_FIELDS"]["ADRESS_SENDER"] = 'has-error';
				}
				else
				{
					$arChanges[551] = array('VALUE' => array('TYPE' => 'text', 'TEXT' => NewQuotes($_POST['ADRESS_SENDER'])));
				}
				if (!strlen($_POST['NAME_RECIPIENT']))
				{
					$arResult["ERR_FIELDS"]["NAME_RECIPIENT"] = 'has-error';
				}
				else
				{
					$arChanges[552] = NewQuotes($_POST['NAME_RECIPIENT']);
				}
				if (!strlen($_POST['PHONE_RECIPIENT']))
				{
					$arResult["ERR_FIELDS"]["PHONE_RECIPIENT"] = 'has-error';
				}
				else
				{
					$arChanges[553] = NewQuotes($_POST['PHONE_RECIPIENT']);
				}
				if (!strlen($_POST['COMPANY_RECIPIENT']))
				{
					$arResult["ERR_FIELDS"]["COMPANY_RECIPIENT"] = 'has-error';
				}
				else
				{
					$arChanges[554] = NewQuotes($_POST['COMPANY_RECIPIENT']);
				}
				if (!strlen($_POST['CITY_RECIPIENT']))
				{
					$arResult["ERR_FIELDS"]["CITY_RECIPIENT"] = 'has-error';
				}
				else
				{
					$city_recipient = GetCityId(trim($_POST['CITY_RECIPIENT']));
					if ($city_recipient == 0)
					{
						$arResult["ERR_FIELDS"]["CITY_RECIPIENT"] = 'has-error';
					}
					else
					{
						$arChanges[555] = $city_recipient;
					}
				}
				if (!strlen($_POST['ADRESS_RECIPIENT']))
				{
					$arResult["ERR_FIELDS"]["ADRESS_RECIPIENT"] = 'has-error';
				}
				else
				{
					$arChanges[571] = array('VALUE' => array('TYPE' => 'text', 'TEXT' => NewQuotes($_POST['ADRESS_RECIPIENT'])));
				}
				if (!$_POST['TYPE_DELIVERY'])
				{
					$arResult["ERR_FIELDS"]["TYPE_DELIVERY"] = 'has-error';
				}
				else
				{
					$arChanges[557] = $_POST['TYPE_DELIVERY'];
				}
				if (!$_POST['TYPE_PACK'])
				{
					$arResult["ERR_FIELDS"]["TYPE_PACK"] = 'has-error';
				}
				else
				{
					$arChanges[558] = $_POST['TYPE_PACK'];
				}
				if (!$_POST['WHO_DELIVERY'])
				{
					$arResult["ERR_FIELDS"]["WHO_DELIVERY"] = 'has-error';
				}
				else
				{
					$arChanges[559] = $_POST['WHO_DELIVERY'];
				}
				/*
				if (!$_POST['TYPE_PAYS'])
				{
					$arResult["ERR_FIELDS"]["TYPE_PAYS"] = 'has-error';
				}
				else
				{
					if (($_POST['TYPE_PAYS'] == 253) && (!strlen($_POST['PAYS'])))
					{
						$arResult["ERR_FIELDS"]["TYPE_PAYS"] = 'has-error';
					}
					else
					{
						$arChanges[562] = $_POST['TYPE_PAYS'];
						$arChanges[563] = NewQuotes($_POST['PAYS']);
					}
				}
				*/
				if (!$_POST['PAYMENT'])
				{
					$arResult["ERR_FIELDS"]["PAYMENT"] = 'has-error';
				}
				else
				{
					$arChanges[564] = $_POST['PAYMENT'];
				}
				/******/
				if (!$_POST['TYPE_PAYS'])
				{
					$arResult["ERR_FIELDS"]["TYPE_PAYS"] = 'has-error';
				}
				else
				{
					if ((intval($_POST['PAYMENT']) == 256) && ((intval($_POST['TYPE_PAYS']) == 252) || (intval($_POST['TYPE_PAYS']) == 253)))
					{
						if ((count($arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT_VALUE']) > 0) && (is_array($arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT_VALUE'])))
						{
							if (intval($_POST['WHOSE_ORDER']) == 0)
							{
								$arResult["ERR_FIELDS"]["TYPE_PAYS"] = 'has-error';
								$arResult["ERR_FIELDS"]["WHOSE_ORDER"] = 'has-error';
							}
							else
							{
								$arChanges[562] = $_POST['TYPE_PAYS'];
								$arChanges[563] = $arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT'][intval($_POST['WHOSE_ORDER'])];
								$arChanges[737] = intval($_POST['WHOSE_ORDER']);
							}
						}
						else
						{
							if ((!strlen($_POST['PAYS'])) && (intval($_POST['TYPE_PAYS']) == 253))
							{
								$arResult["ERR_FIELDS"]["TYPE_PAYS"] = 'has-error';
								$arResult["ERR_FIELDS"]["WHOSE_ORDER"] = 'has-error';
							}
							else
							{
								$arChanges[562] = $_POST['TYPE_PAYS'];
								$arChanges[563] = deleteTabs($_POST['PAYS']);
							}
						}
					}
					else
					{
						$arChanges[562] = $_POST['TYPE_PAYS'];
					}
				}
				if ($total_place <= 0)
				{
					$arResult["ERR_FIELDS"]["PLACES"] = 'has-error';
				}
				else
				{
					$arChanges[567] = $total_place;
				}
				if ($total_weight <= 0)
				{
					$arResult["ERR_FIELDS"]["WEIGHT"] = 'has-error';
				}
				else
				{
					$arChanges[568] = $total_weight;
				}
				if (count($arResult["ERR_FIELDS"]) == 0)
				{
					CIBlockElement::SetPropertyValuesEx($_POST['id'], 83, $arChanges);
					//$arResult["MESSAGE"][] = 'Накладная '.$_POST['number'].' успешно изменена';
					$_SESSION['MESSAGE'][] = 'Накладная '.$_POST['number'].' успешно изменена';
					if (isset($_POST['save-print']))
					{
						if (strlen(trim($arParams['LINK'])))
						{
							LocalRedirect($arParams['LINK']."?openprint=Y&id=".$_POST['id']);
						}
						else
						{
							LocalRedirect("/index.php?openprint=Y&id=".$_POST['id']);
						}
					}
					else
					{
						if (strlen(trim($arParams['LINK'])))
						{
							LocalRedirect($arParams['LINK']);
						}
						else
						{
							LocalRedirect("/index.php");
						}
					}
				}

			}
			$arResult['ERRORS'] = (is_array($arResult['ERRORS'])) ? $arResult['ERRORS'] : array();
			$arResult['ERR_FIELDS'] = (is_array($arResult['ERR_FIELDS'])) ? $arResult['ERR_FIELDS'] : array();
			AddToLogs('InvEditErrors',array_merge($arResult['ERRORS'], $arResult["ERR_FIELDS"]));
		}
		$arResult['INVOICE'] = false;
		$id_reqv = intval($_GET['id']);
		if ($id_reqv > 0)
		{
			$res = CIBlockElement::GetList(
				array("id" => "desc"), 
				array("IBLOCK_ID" => 83, "ID" => $id_reqv, "PROPERTY_CREATOR" => $arResult['CURRENT_CLIENT']), 
				false, 
				false, 
				array(
					"ID",
					"NAME",
					"PROPERTY_NAME_SENDER",
					"PROPERTY_PHONE_SENDER",
					"PROPERTY_COMPANY_SENDER",
					"PROPERTY_CITY_SENDER",
					"PROPERTY_INDEX_SENDER",
					"PROPERTY_ADRESS_SENDER",
					"PROPERTY_NAME_RECIPIENT",
					"PROPERTY_PHONE_RECIPIENT",
					"PROPERTY_COMPANY_RECIPIENT",
					"PROPERTY_CITY_RECIPIENT",
					"PROPERTY_INDEX_RECIPIENT",
					"PROPERTY_ADRESS_RECIPIENT",
					"PROPERTY_TYPE_DELIVERY",
					"PROPERTY_TYPE_PACK",
					"PROPERTY_WHO_DELIVERY",
					"PROPERTY_IN_DATE_DELIVERY",
					"PROPERTY_IN_TIME_DELIVERY",
					"PROPERTY_TYPE_PAYS",
					"PROPERTY_PAYS",
					"PROPERTY_PAYMENT",
					"PROPERTY_FOR_PAYMENT",
					"PROPERTY_PAYMENT_COD",
					"PROPERTY_COST",
					"PROPERTY_PLACES",
					"PROPERTY_WEIGHT",
					"PROPERTY_DIMENSIONS",
					"PROPERTY_STATE",
					"PROPERTY_INSTRUCTIONS",
					"PROPERTY_PACK_DESCRIPTION",
                    "PROPERTY_PACK_GOODS",
					"PROPERTY_WHOSE_ORDER"
				)
			);
			if ($ob = $res->GetNextElement())
			{
				$r = $ob->GetFields();
				if ($r['PROPERTY_STATE_ENUM_ID'] != 257)
				{
					if (strlen(trim($arParams['LINK'])))
					{
						LocalRedirect($arParams['LINK'].'?mode=list');
					}
					else
					{
						LocalRedirect("/index.php?mode=list");
					}
				}
				$r['PROPERTY_CITY_SENDER'] = GetFullNameOfCity($r['PROPERTY_CITY_SENDER_VALUE']);
				$r['PROPERTY_CITY_RECIPIENT'] = GetFullNameOfCity($r['PROPERTY_CITY_RECIPIENT_VALUE']);
				if (strlen($r['PROPERTY_PACK_DESCRIPTION_VALUE']))
				{
					$r['PACK_DESCR'] = json_decode(htmlspecialcharsBack($r['PROPERTY_PACK_DESCRIPTION_VALUE']), true);
					foreach ($r['PACK_DESCR'] as $k => $str)
					{
						$r['PACK_DESCR'][$k]['name'] = iconv('utf-8','windows-1251',$str['name']);
						$r['PACK_DESCR'][$k]['place'] = (intval($r['PACK_DESCR'][$k]['place']) > 0) ? intval($r['PACK_DESCR'][$k]['place']) : "";
						$r['PACK_DESCR'][$k]['weight'] = ($r['PACK_DESCR'][$k]['weight'] > 0) ? $r['PACK_DESCR'][$k]['weight'] : "";
					}
				}
				else
				{
					if (is_array($r['PROPERTY_DIMENSIONS_VALUE']))
					{
						$w = 1;
						for ($i = 0; $i<3; $i++)
						{
							$w = $w*$r['PROPERTY_DIMENSIONS_VALUE'][$i];
						}
						$r["PROPERTY_OB_WEIGHT"] = $w/$arResult['CURRENT_CLIENT_COEFFICIENT_VW'];
					}
					else
					{
						$r["PROPERTY_OB_WEIGHT"] = 0;
					}
					$r['PACK_DESCR'][0] = array(
						'name' => $r['PROPERTY_TYPE_PACK_VALUE'],
						'place' => $r['PROPERTY_PLACES_VALUE'],
						'weight' => $r['PROPERTY_WEIGHT_VALUE'],
						'size' => $r['PROPERTY_DIMENSIONS_VALUE'],
					);	
				}
                if (strlen($r['PROPERTY_PACK_GOODS_VALUE']))
                {
                    $r['PACK_GOODS'] = json_decode(htmlspecialcharsBack($r['PROPERTY_PACK_GOODS_VALUE']), true);
                    foreach ($r['PACK_GOODS'] as $k => $str)
                    {
                        $r['PACK_GOODS'][$k]['GoodsName'] = iconv('utf-8','windows-1251',$str['GoodsName']);
                    }
                }
                else
                {
                    $r['PACK_GOODS'][0] = array(
						'name' => '',
						'amount' => '',
						'price' => '',
						'sum' => '',
                        'sumnds' => '',
                        'persentnds' => ''
					);
                }
				$arResult['INVOICE'] = $r;
				$arResult['TITLE'] = $arResult['INVOICE']['NAME'];
				$APPLICATION->SetTitle($arResult['INVOICE']['NAME']);
				
				/***/
				$arSettings = array();
				$settingsJson = $arResult['AGENT']['PROPERTY_ACCOUNT_LK_SETTINGS_VALUE']['TEXT'];
				$arSettings = array();
				if (strlen($settingsJson))
				{
					$arSettings = json_decode(htmlspecialcharsBack($settingsJson), true);
				}
				$arResult['USER_SETTINGS'] = $arSettings[$arResult["USER_ID"]];
				$arResult['DEAULTS'] = array(
					'MERGE_SENDERS' => ($arResult['USER_SETTINGS']['MERGE_SENDERS'] == 'Y') ? 'Y' : 'N',
					'MERGE_RECIPIENTS' => ($arResult['USER_SETTINGS']['MERGE_RECIPIENTS'] == 'Y') ? 'Y' : 'N'
				);
				$arResult['TYPE_CLIENT_SENDERS'] = ($arResult['DEAULTS']['MERGE_SENDERS'] == 'Y') ? 777 : 259;
				$arResult['TYPE_CLIENT_RECIPIENTS'] = ($arResult['DEAULTS']['MERGE_RECIPIENTS'] == 'Y') ? 777 : 260;
				/***/
			}
			else
			{
				$arResult['TITLE'] = GetMessage('ERR_NO_REQUEST');
				$APPLICATION->SetTitle(GetMessage('ERR_NO_REQUEST'));
			}
		}
		else
		{
			$arResult['TITLE'] = GetMessage('ERR_NO_REQUEST');
			$APPLICATION->SetTitle(GetMessage('ERR_NO_REQUEST'));
		}
	}
	
	if ($arResult['MODE'] == 'add')
	{
		if (!$arResult['ADMIN_AGENT'])
		{
			$arResult['CURRENT_CLIENT'] = $agent_id;
			$arResult['CURRENT_CLIENT_INFO'] = $arResult['AGENT'];
		}
		else
		{
			if (strlen($_SESSION['CURRENT_CLIENT']))
			{
				$arResult['CURRENT_CLIENT'] = $_SESSION['CURRENT_CLIENT'];
				$arResult['CURRENT_CLIENT_INFO'] = GetCompany($arResult['CURRENT_CLIENT']);
			}
			else
			{
				$arResult['CURRENT_CLIENT'] = 0;
				$arResult['CURRENT_CLIENT_INFO'] = false;
			}
		}
		if (intval($arResult['CURRENT_CLIENT']) == 0)
		{
			$arResult['OPEN'] = false;
			if ($arResult['ADMIN_AGENT'])
			{
				$arResult["WARNINGS"][] = GetMessage('ERR_OPEN_ADMIN');
			}
			else
			{
				$arResult["WARNINGS"][] = GetMessage('ERR_OPEN');
			}
		}
        if ($arResult['CURRENT_CLIENT'] > 0)
        {
			$arResult['CURRENT_CLIENT_COEFFICIENT_VW'] = WhatIsGabWeightCompany($arResult['CURRENT_CLIENT']);
            $db_props = CIBlockElement::GetProperty(40, $arResult['CURRENT_CLIENT'], array("sort" => "asc"), array("CODE"=>"INN"));
            if($ar_props = $db_props->Fetch())
            {
                $arResult['CURRENT_CLIENT_INN'] = $ar_props["VALUE"];
            }
			if ($arParams['TYPE'] == 53)
			{
				$res = CIBlockElement::GetList(array("name"=>"asc"), array("IBLOCK_ID"=>40, "ACTIVE"=>"Y", 'PROPERTY_BY_AGENT' => $arResult['CURRENT_CLIENT']), false, false, array("ID", "NAME"));
				while($ob = $res->GetNextElement())
				{
					$arFields = $ob->GetFields();
					$arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT'][$arFields['ID']] = $arFields['NAME'];
				}
			}
			else
			{
				if ((count($arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT_VALUE']) > 0) && (is_array($arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT_VALUE'])))
				{
					foreach ($arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT_VALUE'] as $k)
					{
						$res = CIBlockElement::GetByID($k);
						if($ar_res = $res->GetNext())
						{
							$arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT'][$k] = $ar_res['NAME'];
						}

					}
				}
			}
        }
		
        $arSettings = array();
        $settingsJson = $arResult['AGENT']['PROPERTY_ACCOUNT_LK_SETTINGS_VALUE']['TEXT'];
        $arSettings = array();
        if (strlen($settingsJson))
        {
            $arSettings = json_decode(htmlspecialcharsBack($settingsJson), true);
        }
        $arResult['USER_SETTINGS'] = $arSettings[$arResult["USER_ID"]];
		$arResult['DEAULTS'] = array(
            'callcourier' => ($arResult['USER_SETTINGS']['CALLCOURIER'] == 'yes') ? 'yes' : '',
            'callcourierdate' => date('d.m.Y', strtotime("+1 day")),
            'callcouriertime_from' => '10:00',
            'callcouriertime_to' => '18:00',
			'PLACES' => 1,
			'TYPE_DELIVERY' => (intval($arResult['USER_SETTINGS']['TYPE_DELIVERY']) > 0) ? intval($arResult['USER_SETTINGS']['TYPE_DELIVERY']) : 244,
			'TYPE_PACK' => (intval($arResult['USER_SETTINGS']['TYPE_PACK']) > 0) ? intval($arResult['USER_SETTINGS']['TYPE_PACK']) : 246,
			'WHO_DELIVERY' => (intval($arResult['USER_SETTINGS']['WHO_DELIVERY']) > 0) ? intval($arResult['USER_SETTINGS']['WHO_DELIVERY']) : 248,
			'TYPE_PAYS' => (intval($arResult['USER_SETTINGS']['TYPE_PAYS']) > 0) ? intval($arResult['USER_SETTINGS']['TYPE_PAYS']) : 251,
			'PAYMENT' => (intval($arResult['USER_SETTINGS']['PAYMENT']) > 0) ? intval($arResult['USER_SETTINGS']['PAYMENT']) : 256,
			'WEIGHT' => '0,2',
			'CHOICE_COMPANY' => (intval($arResult['USER_SETTINGS']['CHOICE_COMPANY']) == 2) ? 2 : 1,
			'MERGE_SENDERS' => ($arResult['USER_SETTINGS']['MERGE_SENDERS'] == 'Y') ? 'Y' : 'N',
			'MERGE_RECIPIENTS' => ($arResult['USER_SETTINGS']['MERGE_RECIPIENTS'] == 'Y') ? 'Y' : 'N'
		);
		if ($arParams['TYPE'] == 53)
		{
			$arResult['DEAULTS']['CHOICE_COMPANY'] = (intval($arResult['USER_SETTINGS']['CHOICE_COMPANY']) == 1) ? 1 : 2;
		}
        //print_r($arResult['USER_SETTINGS']);
		
		//Данные из копируемой заявки
		if (($_GET['copy'] == 'Y') && (intval($_GET['copyfrom']) > 0))
		{
			$filter = array("IBLOCK_ID" => 83, "ID" => intval($_GET['copyfrom']), "PROPERTY_CREATOR" => $arResult['CURRENT_CLIENT']);
			if ($arParams['TYPE'] == 53)
			{
				unset($filter['PROPERTY_CREATOR']);
			}
			$res = CIBlockElement::GetList(
				array("id" => "desc"), 
				$filter, 
				false, 
				false, 
				array(
					"PROPERTY_NAME_SENDER",
					"PROPERTY_PHONE_SENDER",
					"PROPERTY_COMPANY_SENDER",
					"PROPERTY_CITY_SENDER",
					"PROPERTY_INDEX_SENDER",
					"PROPERTY_ADRESS_SENDER",
					"PROPERTY_NAME_RECIPIENT",
					"PROPERTY_PHONE_RECIPIENT",
					"PROPERTY_COMPANY_RECIPIENT",
					"PROPERTY_CITY_RECIPIENT",
					"PROPERTY_INDEX_RECIPIENT",
					"PROPERTY_ADRESS_RECIPIENT",
					"PROPERTY_TYPE_DELIVERY",
					"PROPERTY_TYPE_PACK",
					"PROPERTY_WHO_DELIVERY",
					"PROPERTY_PAYMENT",
					"PROPERTY_TYPE_PAYS",
					"PROPERTY_WHOSE_ORDER",
					//"PROPERTY_INSTRUCTIONS",
				)
			);
			if ($ob = $res->GetNextElement())
			{
				$r = $ob->GetFields();
				$arResult['DEAULTS']['COMPANY_SENDER'] = $r['PROPERTY_COMPANY_SENDER_VALUE'];
				$arResult['DEAULTS']['NAME_SENDER'] = $r['PROPERTY_NAME_SENDER_VALUE'];
				$arResult['DEAULTS']['PHONE_SENDER'] = $r['PROPERTY_PHONE_SENDER_VALUE'];
				$arResult['DEAULTS']['CITY_SENDER'] = GetFullNameOfCity($r['PROPERTY_CITY_SENDER_VALUE']);
				$arResult['DEAULTS']['INDEX_SENDER'] = $r['PROPERTY_INDEX_SENDER_VALUE'];
				$arResult['DEAULTS']['ADRESS_SENDER'] = $r['PROPERTY_ADRESS_SENDER_VALUE']['TEXT'];
				$arResult['DEAULTS']['COMPANY_RECIPIENT'] = $r['PROPERTY_COMPANY_RECIPIENT_VALUE'];
				$arResult['DEAULTS']['NAME_RECIPIENT'] = $r['PROPERTY_NAME_RECIPIENT_VALUE'];
				$arResult['DEAULTS']['PHONE_RECIPIENT'] = $r['PROPERTY_PHONE_RECIPIENT_VALUE'];
				$arResult['DEAULTS']['CITY_RECIPIENT'] = GetFullNameOfCity($r['PROPERTY_CITY_RECIPIENT_VALUE']);
				$arResult['DEAULTS']['INDEX_RECIPIENT'] = $r['PROPERTY_INDEX_RECIPIENT_VALUE'];
				$arResult['DEAULTS']['ADRESS_RECIPIENT'] = $r['PROPERTY_ADRESS_RECIPIENT_VALUE']['TEXT'];
				$arResult['DEAULTS']['TYPE_DELIVERY'] = $r['PROPERTY_TYPE_DELIVERY_ENUM_ID'];
				$arResult['DEAULTS']['TYPE_PACK'] = $r['PROPERTY_TYPE_PACK_ENUM_ID'];
				$arResult['DEAULTS']['WHO_DELIVERY'] = $r['PROPERTY_WHO_DELIVERY_ENUM_ID'];
				$arResult['DEAULTS']['PAYMENT'] = $r['PROPERTY_PAYMENT_ENUM_ID'];
				$arResult['DEAULTS']['TYPE_PAYS'] = $r['PROPERTY_TYPE_PAYS_ENUM_ID'];
				$arResult['DEAULTS']['WHOSE_ORDER'] = $r['PROPERTY_WHOSE_ORDER_VALUE'];
				//$arResult['DEAULTS']['INSTRUCTIONS'] = $r['PROPERTY_INSTRUCTIONS_VALUE']['TEXT'];
			}
		}
		//Данные из копируемой заявки
		
		if ((isset($_POST['add'])) || (isset($_POST['add-print'])) || (isset($_POST['add_ctrl'])))
		{
			/*
			if ($arResult['ADMIN_AGENT'])
			{
				echo '<pre>';
				print_r($_POST);
				echo '</pre>';
			}
			*/
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$arPostLogsVal = array();
				foreach ($_POST as $k => $v)
				{
					if (is_array($v))
					{
						foreach ($v as $kk => $vv)
						{
							if (is_array($vv))
							{
								foreach ($vv as $kkk => $vvv)
								{
									$arPostLogsVal[$k.'_'.$kk.'_'.$kkk] = $vvv;
								}
							}
							else
							{
								$arPostLogsVal[$k.'_'.$kk] = $vv;
							}
						}
					}
					else
					{
						$arPostLogsVal[$k] = $v;
					}
				}
				AddToLogs('InvAddPostValues',$arPostLogsVal);
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
				if (!strlen($_POST['NAME_SENDER']))
				{
					$arResult["ERR_FIELDS"]["NAME_SENDER"] = 'has-error';
				}
				if (!strlen($_POST['PHONE_SENDER']))
				{
					$arResult["ERR_FIELDS"]["PHONE_SENDER"] = 'has-error';
				}
				if (!strlen($_POST['COMPANY_SENDER']))
				{
					$arResult["ERR_FIELDS"]["COMPANY_SENDER"] = 'has-error';
				}
				if (!strlen($_POST['CITY_SENDER']))
				{
					$arResult["ERR_FIELDS"]["CITY_SENDER"] = 'has-error';
				}
				else
				{
					$city_sender = GetCityId(trim($_POST['CITY_SENDER']));
					if ($city_sender == 0)
					{
						$arResult["ERR_FIELDS"]["CITY_SENDER"] = 'has-error';
					}
				}
				if (!strlen($_POST['ADRESS_SENDER']))
				{
					$arResult["ERR_FIELDS"]["ADRESS_SENDER"] = 'has-error';
				}
				
				if (!strlen($_POST['NAME_RECIPIENT']))
				{
					$arResult["ERR_FIELDS"]["NAME_RECIPIENT"] = 'has-error';
				}
				if (!strlen($_POST['PHONE_RECIPIENT']))
				{
					$arResult["ERR_FIELDS"]["PHONE_RECIPIENT"] = 'has-error';
				}
				if (!strlen($_POST['COMPANY_RECIPIENT']))
				{
					$arResult["ERR_FIELDS"]["COMPANY_RECIPIENT"] = 'has-error';
				}
				if (!strlen($_POST['CITY_RECIPIENT']))
				{
					$arResult["ERR_FIELDS"]["CITY_RECIPIENT"] = 'has-error';
				}
				else
				{
					$city_recipient = GetCityId(trim($_POST['CITY_RECIPIENT']));
					if ($city_recipient == 0)
					{
						$arResult["ERR_FIELDS"]["CITY_RECIPIENT"] = 'has-error';
					}
				}
				if (!strlen($_POST['ADRESS_RECIPIENT']))
				{
					$arResult["ERR_FIELDS"]["ADRESS_RECIPIENT"] = 'has-error';
				}
				
				if (!$_POST['TYPE_DELIVERY'])
				{
					$arResult["ERR_FIELDS"]["TYPE_DELIVERY"] = 'has-error';
				}
				if (!$_POST['TYPE_PACK'])
				{
					$arResult["ERR_FIELDS"]["TYPE_PACK"] = 'has-error';
				}
				if (!$_POST['WHO_DELIVERY'])
				{
					$arResult["ERR_FIELDS"]["WHO_DELIVERY"] = 'has-error';
				}
				$WHOSE_ORDER = false;
				$pays_text = '';
				if (!$_POST['PAYMENT'])
				{
					$arResult["ERR_FIELDS"]["PAYMENT"] = 'has-error';
				}
				if (!$_POST['TYPE_PAYS'])
				{
					$arResult["ERR_FIELDS"]["TYPE_PAYS"] = 'has-error';
				}
				if ((intval($_POST['PAYMENT']) == 256) && ((intval($_POST['TYPE_PAYS']) == 252) || (intval($_POST['TYPE_PAYS']) == 253)))
				{
					if ((count($arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT_VALUE']) > 0) && (is_array($arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT_VALUE'])))
					{
						if (intval($_POST['WHOSE_ORDER']) == 0)
						{
							$arResult["ERR_FIELDS"]["WHOSE_ORDER"] = 'has-error';
						}
						else
						{
							$pays_text = $arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT'][intval($_POST['WHOSE_ORDER'])];
							$WHOSE_ORDER = intval($_POST['WHOSE_ORDER']);
						}
					}
					else
					{
						if ((!strlen($_POST['PAYS'])) && (intval($_POST['TYPE_PAYS']) == 253))
						{
							$arResult["ERR_FIELDS"]["WHOSE_ORDER"] = 'has-error';
						}
						else
						{
							$pays_text = deleteTabs($_POST['PAYS']);
						}
					}
				}
				$arJsonDescr = array();
				$total_place = 0;
				$total_weight = 0;
				$total_gabweight = 0;
				foreach ($_POST['pack_description'] as $description_str)
				{
					$sizes = array();
					foreach ($description_str['size'] as $sz)
					{
						$sizes[] = floatval(str_replace(',','.',$sz));
					}
					$arCurStr = array(
						'name' => iconv('windows-1251','utf-8',$description_str['name']),
						'place' => intval($description_str['place']),
						'weight' => floatval(str_replace(',','.',$description_str['weight'])),
						'size' => $sizes,
						'gabweight' => (($sizes[0]*$sizes[1]*$sizes[2])/$arResult['CURRENT_CLIENT_COEFFICIENT_VW'])
					);
					$total_place = $total_place + $arCurStr['place'];
					$total_weight = $total_weight + $arCurStr['weight'];
					$total_gabweight = $total_gabweight + $arCurStr['gabweight'];
					$arJsonDescr[] = $arCurStr;
				}
				if ($total_place <= 0)
				{
					$arResult["ERR_FIELDS"]["PLACES"] = 'has-error';
				}
				if ($total_weight <= 0)
				{
					$arResult["ERR_FIELDS"]["WEIGHT"] = 'has-error';
				}
				
				if ((!strlen($_POST['callcourierdate'])) && ($_POST['callcourier'] == 'yes'))
				{
					$arResult["ERR_FIELDS"]["callcourierdate"] = 'has-error';
				}
                $arJsonGoods = array();
                $arJsonGoodsSource = array();
                foreach ($_POST['goods'] as $goods_str)
                {
                    $arJsonGoods[] = array(
                        'GoodsName' => iconv('windows-1251','utf-8',$goods_str['name']),
                        'Amount' => intval($goods_str['amount']),
                        'Price' => floatval(str_replace(',','.',$goods_str['price'])),
                        'Sum' => floatval(str_replace(',','.',$goods_str['sum'])),
                        'SumNDS' => floatval(str_replace(',','.',$goods_str['sumnds'])),
                        'PersentNDS' => intval($goods_str['persentnds'])
                    );
                    $arJsonGoodsSource[] = array(
                        'GoodsName' => iconv('windows-1251','utf-8',$goods_str['name']),
                        'Amount' => iconv('windows-1251','utf-8',$goods_str['amount']),
                        'Price' => iconv('windows-1251','utf-8',$goods_str['price']),
                        'Sum' => iconv('windows-1251','utf-8',$goods_str['sum']),
                        'SumNDS' => iconv('windows-1251','utf-8',$goods_str['sumnds']),
                        'PersentNDS' => iconv('windows-1251','utf-8',$goods_str['persentnds'])
                    );
                }
	
				if (count($arResult["ERR_FIELDS"]) == 0)
				{
					//$id_in = MakeInvoiceNumber(83, 7, '90-');
					if (strlen(trim($_POST['NUMBER'])))
					{
						$id_in = array(
							'max_id' => 0
						);
						$number_nakl = trim($_POST['NUMBER']);
					}
					else
					{
						$id_in = MakeInvoiceNumberNew(1, 7, '90-');
						$number_nakl = $id_in['number'];
					}
					//$id_in = MakeInvoiceNumberNew(1, 7, '90-');
					//$number_nakl = strlen(NewQuotes($_POST['NUMBER'])) ? NewQuotes($_POST['NUMBER']) : $id_in['number'];
					$el = new CIBlockElement;
					$arLoadProductArray = Array(
						"MODIFIED_BY" => $USER->GetID(), 
						"IBLOCK_SECTION_ID" => false,
						"IBLOCK_ID" => 83,
						"PROPERTY_VALUES" => array(
							544 => $id_in['max_id'],
							545 => $arResult['CURRENT_CLIENT'],
							546 => NewQuotes($_POST['NAME_SENDER']),
							547 => NewQuotes($_POST['PHONE_SENDER']),
							548 => NewQuotes($_POST['COMPANY_SENDER']),
							549 => $city_sender,
							550 => deleteTabs($_POST['INDEX_SENDER']),
							551 => array('VALUE' => array('TYPE' => 'text', 'TEXT' => NewQuotes($_POST['ADRESS_SENDER']))),
							552 => NewQuotes($_POST['NAME_RECIPIENT']),
							553 => NewQuotes($_POST['PHONE_RECIPIENT']),
							554 => NewQuotes($_POST['COMPANY_RECIPIENT']),
							555 => $city_recipient,
							556 => deleteTabs($_POST['INDEX_RECIPIENT']),
							557 => $_POST['TYPE_DELIVERY'],
							558 => $_POST['TYPE_PACK'],
							559 => $_POST['WHO_DELIVERY'],
							560 => deleteTabs($_POST['IN_DATE_DELIVERY']),
							561 => deleteTabs($_POST['IN_TIME_DELIVERY']),
							562 => $_POST['TYPE_PAYS'],
							563 => $pays_text,
							564 => $_POST['PAYMENT'],
							565 => floatval(str_replace(',','.',$_POST['FOR_PAYMENT'])),
							733 => floatval(str_replace(',','.',$_POST['PAYMENT_COD'])),
							566 => floatval(str_replace(',','.',$_POST['COST'])),
							567 => $total_place,
							568 => $total_weight,
							569 => $_POST['DIMENSIONS'],
							570 => array('VALUE' => array('TYPE' => 'text', 'TEXT' => NewQuotes($_POST['INSTRUCTIONS']))),
							571 => array('VALUE' => array('TYPE' => 'text', 'TEXT' => NewQuotes($_POST['ADRESS_RECIPIENT']))),
							572 => 257,
							639 => $arResult['BRANCH_AGENT_BY'],
							640 => $arResult['CLIENT_CONTRACT'],
							641 => $arResult['CURRENT_BRANCH'],
							679	=> 1,
							682 => json_encode($arJsonDescr),
                            724 => json_encode($arJsonGoods),
							737 => $WHOSE_ORDER
						),
						"NAME" => $number_nakl,
						"ACTIVE" => "Y"
					);
					if ($z_nakl_id = $el->Add($arLoadProductArray))
					{
                        $arLog = array(
                            'Type' => 'Новая накладная',
                            'OwnNumber' => strlen(NewQuotes($_POST['NUMBER'])) ? 'Y' : 'N',
                            'Number' => $number_nakl,
                            'ID_IN' => $id_in['max_id'],
							'CREATOR' => $arResult['CURRENT_CLIENT'],
							'NAME_SENDER' => NewQuotes($_POST['NAME_SENDER']),
							'PHONE_SENDER' => NewQuotes($_POST['PHONE_SENDER']),
							'COMPANY_SENDER' => NewQuotes($_POST['COMPANY_SENDER']),
							'CITY_SENDER' => $city_sender,
							'INDEX_SENDER' => deleteTabs($_POST['INDEX_SENDER']),
							'ADRESS_SENDER' => NewQuotes($_POST['ADRESS_SENDER']),
							'NAME_RECIPIENT' => NewQuotes($_POST['NAME_RECIPIENT']),
							'PHONE_RECIPIENT' => NewQuotes($_POST['PHONE_RECIPIENT']),
							'COMPANY_RECIPIENT' => NewQuotes($_POST['COMPANY_RECIPIENT']),
							'CITY_RECIPIENT' => $city_recipient,
							'INDEX_RECIPIENT' => deleteTabs($_POST['INDEX_RECIPIENT']),
							'TYPE_DELIVERY' => $_POST['TYPE_DELIVERY'],
							'TYPE_PACK' => $_POST['TYPE_PACK'],
							'WHO_DELIVERY' => $_POST['WHO_DELIVERY'],
							'IN_DATE_DELIVERY' => deleteTabs($_POST['IN_DATE_DELIVERY']),
							'IN_TIME_DELIVERY' => deleteTabs($_POST['IN_TIME_DELIVERY']),
							'TYPE_PAYS' => $_POST['TYPE_PAYS'],
							'PAYS' => deleteTabs($_POST['PAYS']),
							'PAYMENT' => $_POST['PAYMENT'],
							'FOR_PAYMENT' => floatval(str_replace(',','.',$_POST['FOR_PAYMENT'])),
							'PAYMENT_COD' => floatval(str_replace(',','.',$_POST['PAYMENT_COD'])),
							'COST' => floatval(str_replace(',','.',$_POST['COST'])),
							'PLACES' => $total_place,
							'WEIGHT' => $total_weight,
							'DIMENSIONS' => $_POST['DIMENSIONS'],
							'INSTRUCTIONS' => NewQuotes($_POST['INSTRUCTIONS']),
							'ADRESS_RECIPIENT' => NewQuotes($_POST['ADRESS_RECIPIENT']),
							'STATE' => 257,
							'BRANCH_AGENT_BY' => $arResult['BRANCH_AGENT_BY'],
							'CLIENT_CONTRACT' => $arResult['CLIENT_CONTRACT'],
							'CURRENT_BRANCH' => $arResult['CURRENT_BRANCH'],
							'INFORMATION_ON_CREATE'	=> 1,
							'PACK_DESCRIPTION' => json_encode($arJsonDescr),
                            'PACK_GOODS' => json_encode($arJsonGoods),
                            'PACK_GOODS_SOURSE' => json_encode($arJsonGoodsSource),
                        );
                        AddToLogs('invoices',$arLog);
                        
                        
						$_SESSION['MESSAGE'][] = "Накладная №".$number_nakl." успешно создана";
						
						//вызов курьера
						if ($_POST['callcourier'] == 'yes')
						{
							$id_in_cur = GetMaxIDIN(87, 7);
                            $arHistory = array(array('date' => date('d.m.Y H:i:s'), 'status' => 315, 'status_descr' => 'Оформлена', 'comment' => ''));
                            $arHistoryUTF = convArrayToUTF($arHistory);
							$el = new CIBlockElement;
							$arLoadProductArray = Array(
								"MODIFIED_BY" => $USER->GetID(), 
								"IBLOCK_SECTION_ID" => false,
								"IBLOCK_ID" => 87,
								"PROPERTY_VALUES" => array(
									611 => $id_in_cur,
									612 => $arResult['CURRENT_CLIENT'],
									664 => $arResult['CURRENT_BRANCH'],
									613 => array(
										$_POST['callcourierdate'].' '.$_POST['callcourtime_from'].':00',
										$_POST['callcourierdate'].' '.$_POST['callcourtime_to'].':00'
									),
									614 => $city_sender,
									615 => NewQuotes($_POST['ADRESS_SENDER']),
									616 => NewQuotes($_POST['NAME_SENDER']),
									617 => NewQuotes($_POST['PHONE_SENDER']),
									618 => $total_weight,
									619 => $_POST['DIMENSIONS'],
									620 => NewQuotes($_POST['callcourcomment']).' Накладная №'.$number_nakl,
                                    712 => implode(', ',array($arResult['EMAIL_CALLCOURIER'], $arResult['ADD_AGENT_EMAIL'])),
                                    726 => 315,
                                    727 => json_encode($arHistoryUTF)
								),
								"NAME" => 'Вызов курьера №'.$id_in_cur,
								"ACTIVE" => "Y"
							);
						
							if ($z_id = $el->Add($arLoadProductArray))
							{
                                //TODO [x]Добавить информацию для курьера в спец. инструкции накладной
                                $newInstructions = NewQuotes($_POST['INSTRUCTIONS']);
                                if (strlen($newInstructions))
                                {
                                    $newInstructions .= '. ';
                                }
                                $newInstructions .= 'ВЫЗОВ КУРЬЕРА: '.$_POST['callcourierdate'].' с '.$_POST['callcourtime_from'].' до '.$_POST['callcourtime_to'].'.';
                                if (strlen(trim($_POST['callcourcomment'])))
                                {
                                    $newInstructions .= 'КОММЕНТАРИЙ КУРЬЕРУ: '.NewQuotes($_POST['callcourcomment']);
                                }
                                CIBlockElement::SetPropertyValuesEx($z_nakl_id, 83, array(570 => array('VALUE' => array('TYPE' => 'text', 'TEXT' => $newInstructions))));
                                
								$arEventFields = array(
									"COMPANY_F" => ($arResult['USER_IN_BRANCH']) ? $arResult['AGENT']['NAME'].', филиал '.$arResult['BRANCH_INFO']['NAME'] : $arResult['AGENT']['NAME'], 
									"NUMBER" => $id_in_cur,
									"COMPANY" => $arResult['AGENT']['NAME'],
									"BRANCH" => ($arResult['USER_IN_BRANCH']) ? 'Филиал: <strong>'.$arResult['BRANCH_INFO']['NAME'].'</strong><br />' : '',
									"DATE_TIME" => $_POST['callcourierdate'].' с '.$_POST['callcourtime_from'].' до '.$_POST['callcourtime_to'],
									"CITY" => $_POST['CITY_SENDER'],
									"ADRESS" => NewQuotes($_POST['ADRESS_SENDER']),
									"CONTACT" => NewQuotes($_POST['NAME_SENDER']),
									"PHONE" => NewQuotes($_POST['PHONE_SENDER']),
									"WEIGHT" => $total_weight,
									"SIZE_1" => $_POST['DIMENSIONS'][0],
									"SIZE_2" => $_POST['DIMENSIONS'][1],
									"SIZE_3" => $_POST['DIMENSIONS'][2],
									"COMMENT" => deleteTabs($_POST['callcourcomment']).' Накладная №'.$number_nakl,
									'AGENT_EMAIL' => $arResult['ADD_AGENT_EMAIL'],
                                    'UK_EMAIL' => $arResult['EMAIL_CALLCOURIER']
								);
                                //TODO [x]Настройка почтового события на email-ы управляющей компании
								$event = new CEvent;
								$event->SendImmediate("NEWPARTNER_LK", "S5", $arEventFields, "N", 220);
                                $arHistory[] = array('date' => date('d.m.Y H:i:s'), 'status' => 316, 'status_descr' => 'Отправлена на почту', 'comment' => '');
                                $arHistoryUTF = convArrayToUTF($arHistory);
                                CIBlockElement::SetPropertyValuesEx($z_id, 87, array("STATE"=>316,"STATE_HISTORY"=>json_encode($arHistoryUTF)));
                                //TODO [x]Настройка включения/выключения голосовых оповещений и номеров телефонов
                                if (intval($arResult['ZADARMA']) == 1)
                                {
                                    if ((intval(date('G')) >=17) || (intval(date('G')) < 8))
                                    {
                                        include_once $_SERVER["DOCUMENT_ROOT"].'bitrix/_black_mist/zadarma/Client.php';
                                        $params = array(
                                            'from' => $arResult['ZADARMA_FROM'],
                                            'to' => '+79003333333',
                                        );
                                        $zd = new \Zadarma_API\Client("44c738b94aef4db7b31b", "c6406ab4bc31d8657805");
                                        $answer = $zd->call('/v1/request/callback/', $params);
                                    }     
                                }
                                //NOTE Отправка уведомлений в 1с
                          //      if ($arResult["UK"] == 5873349)
                           //     {
                                    $payment_type = 'Наличные';
                                    switch (intval($_POST['PAYMENT']))
                                    {
                                        case 255:
                                            $payment_type = 'Наличные';
                                            break;
                                        case 256:
                                            $payment_type = 'Безналичные';
                                            break;
                                    }
                                    $delivery_type = 'Стандарт';
                                    switch (intval($_POST['TYPE_DELIVERY']))
                                    {
                                        case 243:
                                            $delivery_type = 'Экспресс';
                                            break;
                                        case 244:
                                            $delivery_type = 'Стандарт';
                                            break;
                                    }
                                    $delivery_payer = 'Отправитель';
                                    switch (intval($_POST['TYPE_PAYS']))
                                    {
                                        case 251:
                                            $delivery_payer = 'Отправитель';
                                            break;
                                        case 252:
                                            $delivery_payer = 'Получатель';
                                            break;
                                        case 253:
                                            $delivery_payer = 'Другой';
                                            break;
                                    }
                                    $delivery_condition = 'ПоАдресу';
                                    switch (intval($_POST['WHO_DELIVERY']))
                                    {
                                        case 248:
                                            $delivery_condition = 'ПоАдресу';
                                            break;
                                        case 249:
                                            $delivery_condition = 'До востребования';
                                            break;
                                        case 250:
                                            $delivery_condition = 'ЛичноВРуки';
                                            break;
                                    }
                                    $arJs = array(
                                        'IDWEB' => $z_id,
                                        'INN' => $arResult['CURRENT_CLIENT_INN'],
                                        'DATE' => date('Y-m-d'),
                                        'COMPANY_SENDER' => ($arResult['USER_IN_BRANCH']) ? $arResult['AGENT']['NAME'].', филиал '.$arResult['BRANCH_INFO']['NAME'] : $arResult['AGENT']['NAME'],
                                        'NAME_SENDER' => NewQuotes($_POST['NAME_SENDER']),
                                        'PHONE_SENDER' => NewQuotes($_POST['PHONE_SENDER']),
                                        'ADRESS_SENDER' => NewQuotes($_POST['ADRESS_SENDER']),
                                        'INDEX_SENDER' => $_POST['INDEX_SENDER'],
                                        'ID_CITY_SENDER' => $city_sender,
                                        'DELIVERY_TYPE' => $delivery_type,
                                        'PAYMENT_TYPE' => $payment_type,
                                        'DELIVERY_PAYER' => $delivery_payer,
                                        'DELIVERY_CONDITION' => $delivery_condition,
                                        'DATE_TAKE_FROM' => substr($_POST['callcourierdate'],6,4).'-'.substr($_POST['callcourierdate'],3,2).'-'.substr($_POST['callcourierdate'],0,2).' '.$_POST['callcourtime_from'].':00',
                                        'DATE_TAKE_TO' => substr($_POST['callcourierdate'],6,4).'-'.substr($_POST['callcourierdate'],3,2).'-'.substr($_POST['callcourierdate'],0,2).' '.$_POST['callcourtime_to'].':00',
                                        'INSTRUCTIONS' => deleteTabs($_POST['callcourcomment']).' Накладная №'.$number_nakl
                                    );
                                    $m = array();
                                    foreach ($arJs as $kk => $vv)
                                    {
                                        $m[$kk] = iconv('windows-1251','utf-8', $vv);
                                    }
                                    $result = $client->SetCallingTheCourier(array('ListOfDocs' => json_encode($m)));
                                    $mResult = $result->return;
                                    $obj = json_decode($mResult, true);
                                    $arRes = arFromUtfToWin($obj);
                                    if ($arRes[0]['status'] == 'true')
                                    {
                                        $state_id = 317;
                                        $state_descr = 'Отправлена';
                                    }
                                    else
                                    {
                                        $state_id = 321;
                                        $state_descr = 'Отклонена';
                                    }
                                    $arHistory[] = array('date' => date('d.m.Y H:i:s'), 'status' => $state_id, 'status_descr' => $state_descr, 'comment' => $arRes[0]['comment']);
                                    $arHistoryUTF = convArrayToUTF($arHistory);
                                    CIBlockElement::SetPropertyValuesEx($z_id, 87, array("STATE"=>$state_id, "STATE_HISTORY"=>json_encode($arHistoryUTF)));
                                    $arLogTitle = array('Title' => 'Вызов курьера из накладной');
                                    $arLogResult = array('Response' => $mResult, 'status' => $arRes[0]['status'], 'comment' => $arRes[0]['comment']);
                                    $arLog = array_merge($arLogTitle,$arJs,$arLogResult);
                                    AddToLogs('callingCourier',$arLog);
                            //    }
								$_SESSION["MESSAGE"][] = "Вызов курьера №".$id_in_cur." успешно зарегистрирован";
                                $_POST = array();
							}
						}
						
						//вызов курьера
						$filter_search_recipient = array(
							"IBLOCK_ID" => 84, 
							"PROPERTY_CREATOR" => $arResult['CURRENT_CLIENT'], 
							"NAME" => NewQuotes($_POST['COMPANY_RECIPIENT']), 
							"PROPERTY_CITY" => $city_recipient, 
							"PROPERTY_ADRESS" => NewQuotes($_POST['ADRESS_RECIPIENT']),
							"PROPERTY_TYPE" => 260
						);
						if ($arResult['USER_SETTINGS']['MERGE_RECIPIENTS'] == 'Y')
						{
							unset($filter_search_recipient['PROPERTY_TYPE']);
						}
						$res = CIBlockElement::GetList(
							array("ID" =>"desc"), 
							$filter_search_recipient,
							false, 
							array("nTopCount" => 1), 
							array("ID")
						);
						//TODO [x]Добавить дату последнего использования
						if (!$ob = $res->GetNextElement())
						{
							$el2 = new CIBlockElement;
							$arLoadProductArray2 = Array(
								"MODIFIED_BY" => $USER->GetID(), 
								"IBLOCK_SECTION_ID" => false,
								"IBLOCK_ID" => 84,
								"PROPERTY_VALUES" => array(
									579 => $arResult['CURRENT_CLIENT'],
									574 => NewQuotes($_POST['NAME_RECIPIENT']),
									575 => NewQuotes($_POST['PHONE_RECIPIENT']),
									576 => $city_recipient,
									577 => $_POST['INDEX_RECIPIENT'],
									578 => NewQuotes($_POST['ADRESS_RECIPIENT']),
									580 => 260,
									668 => $arResult['USER_IN_BRANCH'] ? $arResult['CURRENT_BRANCH'] : false,
                                    713 => date('d.m.Y H:i:s')
								),
								"NAME" => NewQuotes($_POST['COMPANY_RECIPIENT']),
								"ACTIVE" => "Y"
							);
							$rec_id = $el2->Add($arLoadProductArray2);
						}
                        else
                        {
                            $arFields = $ob->GetFields();
                            CIBlockElement::SetPropertyValuesEx($arFields['ID'], 84, array(713 => date('d.m.Y H:i:s')));
                        }
						
						if ((strlen(trim($_POST['COMPANY_SENDER']))) && (intval($_POST['company_sender_id']) == 0))
						{
							$filter_search_senders = array(
								"IBLOCK_ID" => 84, 
								"PROPERTY_CREATOR" => $arResult['CURRENT_CLIENT'], 
								"NAME" => NewQuotes($_POST['COMPANY_SENDER']), 
								"PROPERTY_CITY" => $city_sender, 
								"PROPERTY_ADRESS" => NewQuotes($_POST['ADRESS_SENDER']),
								"PROPERTY_TYPE" => 259
							);
							if ($arResult['USER_SETTINGS']['MERGE_SENDERS'] == 'Y')
							{
								unset($filter_search_senders['PROPERTY_TYPE']);
							}
							$res = CIBlockElement::GetList(
								array("ID" =>"desc"), 
								$filter_search_senders,
								false, 
								array("nTopCount" => 1), 
								array("ID")
							);

							if (!$ob = $res->GetNextElement())
							{
								$el2 = new CIBlockElement;
								$arLoadProductArray2 = Array(
									"MODIFIED_BY" => $USER->GetID(), 
									"IBLOCK_SECTION_ID" => false,
									"IBLOCK_ID" => 84,
									"PROPERTY_VALUES" => array(
										579 => $arResult['CURRENT_CLIENT'],
										574 => NewQuotes($_POST['NAME_SENDER']),
										575 => NewQuotes($_POST['PHONE_SENDER']),
										576 => $city_sender,
										577 => $_POST['INDEX_SENDER'],
										578 => NewQuotes($_POST['ADRESS_SENDER']),
										580 => 259,
										713 => date('d.m.Y H:i:s')
									),
									"NAME" => NewQuotes($_POST['COMPANY_SENDER']),
									"ACTIVE" => "Y"
								);
								$rec_id = $el2->Add($arLoadProductArray2);
							}
							else
							{
								$arFields = $ob->GetFields();
								CIBlockElement::SetPropertyValuesEx($arFields['ID'], 84, array(713 => date('d.m.Y H:i:s')));
							}
						}
						
						
						if (isset($_POST['add-print']))
						{
							if (strlen(trim($arParams['LINK'])))
							{
								LocalRedirect($arParams['LINK']."?openprint=Y&id=".$z_nakl_id);
							}
							else
							{
								LocalRedirect("/index.php?openprint=Y&id=".$z_nakl_id);
							}
						}
						else
						{
							if (strlen(trim($arParams['LINK'])))
							{
								LocalRedirect($arParams['LINK']);
							}
							else
							{
								LocalRedirect("/index.php");
							}
						}
					}
					else
					{
						$error = $el->LAST_ERROR;
						AddToLogs('InvAddErrors',array('ERROR' => $error));
						$arResult['ERRORS'][] = $error;
					}
				}
			}
			$arResult['ERRORS'] = (is_array($arResult['ERRORS'])) ? $arResult['ERRORS'] : array();
			$arResult['ERR_FIELDS'] = (is_array($arResult['ERR_FIELDS'])) ? $arResult['ERR_FIELDS'] : array();
			AddToLogs('InvAddErrors',array_merge($arResult['ERRORS'], $arResult["ERR_FIELDS"]));
		}
		
		$br = $arResult['USER_IN_BRANCH'] ? $arResult['CURRENT_BRANCH'] : false;
		//print_r($arResult['DEAULTS']);
		$arResult['TYPE_CLIENT_SENDERS'] = ($arResult['DEAULTS']['MERGE_SENDERS'] == 'Y') ? 777 : 259;
		$arResult['TYPE_CLIENT_RECIPIENTS'] = ($arResult['DEAULTS']['MERGE_RECIPIENTS'] == 'Y') ? 777 : 260;
		$arResult['SENDERS'] = GetListContractors($arResult['CURRENT_CLIENT'], $arResult['TYPE_CLIENT_SENDERS'], false, '', array("NAME"=>"ASC"), false, false, false, $br);
		if (count($arResult['SENDERS']) == 0)
		{
			$props = array(
				579 => $arResult['CURRENT_CLIENT'],
				580 => 259,
				574 => $USER->GetFullName(),
				575 => $arResult['AGENT']['PROPERTY_PHONES_VALUE'],
				576 => $arResult['AGENT']['PROPERTY_CITY_VALUE'],
				577 => '',
				578 => $arResult['AGENT']['PROPERTY_ADRESS_VALUE'],
				581 => 1,
				668 => false
			);
			$name = $arResult['AGENT']['NAME'];
			if ($arResult['USER_IN_BRANCH'])
			{
				$branch_info = GetBranch($arResult['CURRENT_BRANCH'], $arResult['CURRENT_CLIENT']);
				$props[574] = $branch_info['PROPERTY_FIO_VALUE'];
				$props[575] = $branch_info['PROPERTY_PHONE_VALUE'];
				$props[576] = $branch_info['PROPERTY_CITY_VALUE'];
				$props[577] = $branch_info['PROPERTY_INDEX_VALUE'];
				$props[578] = $branch_info['PROPERTY_ADRESS_VALUE'];
				$props[668] = $arResult['CURRENT_BRANCH'];
                if (intval($arResult['BRANCH_INFO']['PROPERTY_HEAD_BRANCH_VALUE'] == 0))
                {
                    $name .= ', '.$branch_info['NAME'];
                }
			}
			$el = new CIBlockElement;
			$arLoadProductArray = Array(
				"MODIFIED_BY" => $USER->GetID(), 
				"IBLOCK_SECTION_ID" => false,
				"IBLOCK_ID" => 84,
				"PROPERTY_VALUES" => $props,
				"NAME" => $name,
				"ACTIVE" => "Y"
			);
			$first = $el->Add($arLoadProductArray);
			$arResult['SENDERS'] = GetListContractors($agent_id, $arResult['TYPE_CLIENT_SENDERS'], false, '', array("NAME"=>"ASC"), false, false, false, $br);
			
		}
		if ($arResult['ADMIN_AGENT'])
		{
			$arResult['TITLE'] = GetMessage("TITLE_MODE_ADD_ADMIN", array('#NAME#' => $arResult['CURRENT_CLIENT_INFO']['NAME']));
			$APPLICATION->SetTitle(GetMessage("TITLE_MODE_ADD_ADMIN", array('#NAME#' => $arResult['CURRENT_CLIENT_INFO']['NAME'])));
		}
		else
		{
			$arResult['TITLE'] = GetMessage("TITLE_MODE_ADD");
			$APPLICATION->SetTitle(GetMessage("TITLE_MODE_ADD"));
		}
	}
	
	if ($arResult['MODE'] == '1c')
	{
		if ((strlen($_GET['login'])) && (strlen($_GET['pass'])))
		{
            $login1c = GetSettingValue(705);
            $pass1c = GetSettingValue(706);
			if (($_GET['login'] == $login1c) && ($_GET['pass'] == $pass1c))
			{
				if (strlen(trim($_GET['INN'])))
				{
					$agent_inn = GetIDAgentByINN(trim($_GET['INN']), 242);
					if ($agent_inn)
					{
						$arResult['REQUESTS'] = array();
						$res = CIBlockElement::GetList(
							array('id' => 'asc'), 
							array("IBLOCK_ID" => 83, "PROPERTY_CREATOR" => $agent_inn, "PROPERTY_STATE" => 257), 
							false, 
							false, 
							array(
								"ID",
								"NAME",
								"DATE_CREATE",
								"PROPERTY_COMPANY_SENDER",
								"PROPERTY_NAME_SENDER",
								"PROPERTY_PHONE_SENDER",
								"PROPERTY_CITY_SENDER",
								"PROPERTY_INDEX_SENDER",
								"PROPERTY_ADRESS_SENDER",
								"PROPERTY_COMPANY_RECIPIENT",
								"PROPERTY_NAME_RECIPIENT",
								"PROPERTY_PHONE_RECIPIENT",
								"PROPERTY_CITY_RECIPIENT",
								"PROPERTY_INDEX_RECIPIENT",
								"PROPERTY_ADRESS_RECIPIENT",
								"PROPERTY_TYPE_DELIVERY",
								"PROPERTY_TYPE_PACK",
								"PROPERTY_WHO_DELIVERY",
								"PROPERTY_IN_DATE_DELIVERY",
								"PROPERTY_IN_TIME_DELIVERY",
								"PROPERTY_TYPE_PAYS",
								"PROPERTY_PAYS",
								"PROPERTY_PAYMENT",
								"PROPERTY_FOR_PAYMENT",
								"PROPERTY_COST",
								"PROPERTY_PLACES",
								"PROPERTY_WEIGHT",
								"PROPERTY_DIMENSIONS",
								"PROPERTY_INSTRUCTIONS",
							)
						);
						while ($ob = $res->GetNextElement())
						{
							$a = $ob->GetFields();
							$arResult['REQUESTS'][] = array(
								'ID' => $a['ID'],
								'NUMBER' => $a['NAME'],
								'COMPANY_SENDER' => $a['PROPERTY_COMPANY_SENDER_VALUE'],
								'NAME_SENDER' => $a['PROPERTY_NAME_SENDER_VALUE'],
								'PHONE_SENDER' => $a['PROPERTY_PHONE_SENDER_VALUE'],
								'CITY_SENDER' => $a['PROPERTY_CITY_SENDER_VALUE'],

								'INDEX_SENDER' => $a['PROPERTY_INDEX_SENDER_VALUE'],
								'ADRESS_SENDER' => $a['PROPERTY_ADRESS_SENDER_VALUE']['TEXT'],
								'COMPANY_RECIPIENT' => $a['PROPERTY_COMPANY_RECIPIENT_VALUE'],
								'NAME_RECIPIENT' => $a['PROPERTY_NAME_RECIPIENT_VALUE'],
								'PHONE_RECIPIENT' => $a['PROPERTY_PHONE_RECIPIENT_VALUE'],
								'CITY_RECIPIENT' => $a['PROPERTY_CITY_RECIPIENT_VALUE'],
								'INDEX_RECIPIENT' => $a['PROPERTY_INDEX_RECIPIENT_VALUE'],
								'ADRESS_RECIPIENT' => $a['PROPERTY_ADRESS_RECIPIENT_VALUE']['TEXT'],
								'TYPE_DELIVERY' => $a['PROPERTY_TYPE_DELIVERY_VALUE'],
								'TYPE_PACK' => $a['PROPERTY_TYPE_PACK_VALUE'],
								'WHO_DELIVERY' => $a['PROPERTY_WHO_DELIVERY_VALUE'],
								'IN_DATE_DELIVERY' => $a['PROPERTY_IN_DATE_DELIVERY_VALUE'],
								'IN_TIME_DELIVERY' => $a['PROPERTY_IN_TIME_DELIVERY_VALUE'],
								'PAYS' => ( $a['PROPERTY_TYPE_PAYS_ENUM_ID'] == 253) ? $a['PROPERTY_PAYS_VALUE'] : $a['PROPERTY_TYPE_PAYS_VALUE'],
								'PAYMENT' => $a['PROPERTY_PAYMENT_VALUE'],
								'FOR_PAYMENT' => $a['PROPERTY_FOR_PAYMENT_VALUE'],
								'COST' => $a['PROPERTY_COST_VALUE'],
								'PLACES' => $a['PROPERTY_PLACES_VALUE'],
								'WEIGHT' => $a['PROPERTY_WEIGHT_VALUE'],
								'SIZE_1' => $a['PROPERTY_DIMENSIONS_VALUE'][0],
								'SIZE_2' => $a['PROPERTY_DIMENSIONS_VALUE'][1],
								'SIZE_3' => $a['PROPERTY_DIMENSIONS_VALUE'][2],
								'INSTRUCTIONS' => $a['PROPERTY_INSTRUCTIONS_VALUE']['TEXT']	
							);
						}
					}
					else
					{
						$arResult["ERRORS"][] = 'Некорректный ИНН';
					}
				}
				else
				{
					$arResult["ERRORS"][] = 'Отсутствует ИНН в запросе';
				}
				}
			else
			{
				$arResult["ERRORS"][] = 'Ошибка авторизации';
			}
		}
		else
		{
			$arResult["ERRORS"][] = 'Отсутствует логин или пароль';
		}
		$arResult['RESULTS'] = array(
			'ERRORS' => $arResult["ERRORS"],
			'REQUESTS' => $arResult["REQUESTS"]
		);
		foreach ($arResult['RESULTS'] as $k => $v)
		{
			foreach ($v as $kk => $vv)
			{
				if (is_array($vv))
				{
					foreach ($vv as $kkk => $vvv)
					{
						$arResult['RESULTS'][$k][$kk][$kkk] = iconv('windows-1251','utf-8', $vvv);
					}
				}
				else
				{
					$arResult['RESULTS'][$k][$kk] = iconv('windows-1251','utf-8', $vv);
				}
			}
		}
		$arResult['RES_JSON'] = json_encode($arResult['RESULTS']);
	
	}
    
    if ($arResult['MODE'] == 'list_xls')
    {
        if (strlen($_POST['DATA']))
        {
            $arData = json_decode(htmlspecialchars_decode($_POST['DATA'],ENT_COMPAT), true);
            include_once $_SERVER['DOCUMENT_ROOT'].'bitrix/_black_mist/PhpExcel/Classes/PHPExcel.php';
            $pExcel = new PHPExcel();
            $pExcel->setActiveSheetIndex(0);
            $aSheet = $pExcel->getActiveSheet();
            $pExcel->getDefaultStyle()->getFont()->setName('Arial');
            $pExcel->getDefaultStyle()->getFont()->setSize(10);
            $Q = iconv("windows-1251", "utf-8", 'Накладные');
            $aSheet->setTitle($Q);
            $head_style = array(
                'font' => array(
                    'bold' => true,
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            );
            $i = 1;
            $arJ = array('A','B','C','D','E','F','G','H','I','J','K','L','M');
            foreach  ($arData as $k)
            {
                $n = 0;
                foreach ($k as $v)
                {
                    $num_sel = $arJ[$n].$i;
                    $aSheet->setCellValue($num_sel,$v);
                    $n++;
                }
                $i++;
            }
            $i--;
            foreach ($arJ as $cc)
            {
                $aSheet->getColumnDimension($cc)->setWidth(17);
            }
            $aSheet->getStyle('A1:M1')->applyFromArray($head_style);
            $aSheet->getStyle('A1:M'.$i)->getAlignment()->setWrapText(true);
            $aSheet->getStyle('A1:M'.$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            include_once $_SERVER['DOCUMENT_ROOT'].'bitrix/_black_mist/PhpExcel/Classes/PHPExcel/Writer/Excel5.php';
            $objWriter = new PHPExcel_Writer_Excel5($pExcel);
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.date('Накладные d.m.Y').'.xls"');
            header('Cache-Control: max-age=0');
            $objWriter->save('php://output');
        }
    }
    
    if ($arResult['MODE'] == 'acceptance')
    {
        $arLog = array();
        foreach ($_POST as $k => $v)
        {
            $arLog['POST '.$k] = $v;
        }
        $arLog = array();
        foreach ($_GET as $k => $v)
        {
            $arLog['GET '.$k] = $v;
        }
        foreach ($_REQUEST as $k => $v)
        {
            $arLog['REQUEST '.$k] = $v;
        }
        if ($_POST['type'] == 'AcceptanceOnRequest')
        {
            if ((strlen($_POST['login'])) && (strlen($_POST['pass'])))
            {
                $login1c = GetSettingValue(705);
                $pass1c = GetSettingValue(706);
                if (($_POST['login'] == $login1c) && ($_POST['pass'] == $pass1c))
                {
                    $arResponseUtf = json_decode($_POST['Response'], true);
                    $arResponse = arFromUtfToWin($arResponseUtf);
                    if (strlen(trim($arResponse['Number'])))
                    {
                        $res = CIBlockElement::GetList(
                            array("id" => "desc"), 
                            array("IBLOCK_ID" => 83, "NAME" => trim($arResponse['Number'])), 
                            false, 
                            array("nTopCount" => 1), 
                            array(
                                "ID",
                                "NAME",
                                "PROPERTY_CREATOR",
                                "PROPERTY_NAME_SENDER",
                                "PROPERTY_PHONE_SENDER",
                                "PROPERTY_COMPANY_SENDER",
                                "PROPERTY_CITY_SENDER",
                                "PROPERTY_CITY_SENDER.NAME",
                                "PROPERTY_INDEX_SENDER",
                                "PROPERTY_ADRESS_SENDER",
                                "PROPERTY_NAME_RECIPIENT",
                                "PROPERTY_PHONE_RECIPIENT",
                                "PROPERTY_COMPANY_RECIPIENT",
                                "PROPERTY_CITY_RECIPIENT",
                                "PROPERTY_CITY_RECIPIENT.NAME",
                                "PROPERTY_INDEX_RECIPIENT",
                                "PROPERTY_ADRESS_RECIPIENT",
                                "PROPERTY_TYPE_DELIVERY",
                                "PROPERTY_TYPE_PACK",
                                "PROPERTY_WHO_DELIVERY",
                                "PROPERTY_IN_DATE_DELIVERY",
                                "PROPERTY_IN_TIME_DELIVERY",
                                "PROPERTY_TYPE_PAYS",
                                "PROPERTY_PAYS",
                                "PROPERTY_PAYMENT",
                                "PROPERTY_FOR_PAYMENT",
								"PROPERTY_PAYMENT_COD",
                                "PROPERTY_COST",
                                "PROPERTY_PLACES",
                                "PROPERTY_WEIGHT",
                                "PROPERTY_DIMENSIONS",
                                "PROPERTY_STATE",
                                "PROPERTY_INSTRUCTIONS",
                                "PROPERTY_PACK_DESCRIPTION",
                                "PROPERTY_BRANCH",
                                "PROPERTY_PACK_GOODS",
								"PROPERTY_WHOSE_ORDER"
                            )
                        );
                        if ($ob = $res->GetNextElement())
                        {
                            $reqv = $ob->GetFields();
                            if ($reqv["PROPERTY_STATE_ENUM_ID"] == 257)
                            {
                                $reqv["PROPERTY_OB_WEIGHT"] = 0;
                                $reqv["PROPERTY_Dimensions"] = array();
                                if (strlen($reqv['PROPERTY_PACK_DESCRIPTION_VALUE']))
                                {
                                    $reqv['PACK_DESCR'] = json_decode(htmlspecialcharsBack($reqv['PROPERTY_PACK_DESCRIPTION_VALUE']), true);
                                    foreach ($reqv['PACK_DESCR'] as $k => $str)
                                    {
                                        $reqv["PROPERTY_OB_WEIGHT"] = $reqv["PROPERTY_OB_WEIGHT"] + $str['gabweight'];
                                        $reqv["PROPERTY_Dimensions"][] = array(
                                            "WEIGHT" => (floatval($str['weight']) > 0) ? floatval($str['weight']) : 0,
                                            "SIZE_1" => (floatval($str["size"][0]) > 0) ? floatval($str["size"][0]) : 0,
                                            "SIZE_2" => (floatval($str["size"][1]) > 0) ? floatval($str["size"][1]) : 0,
                                            "SIZE_3" => (floatval($str["size"][2]) > 0) ? floatval($str["size"][2]) : 0,
                                            "PLACES" => intval($str["place"]),
                                            "NAME" => iconv('utf-8','windows-1251',$str['name'])
                                        );
                                    }
                                }
                                else
                                {
                                    if (is_array($reqv['PROPERTY_DIMENSIONS_VALUE']))
                                    {
                                        $w = 1;
                                        for ($i = 0; $i<3; $i++)
                                        {
                                            $w = $w*$reqv['PROPERTY_DIMENSIONS_VALUE'][$i];
                                        }
                                        $reqv["PROPERTY_OB_WEIGHT"] = $w/$arResult['CURRENT_CLIENT_COEFFICIENT_VW'];
                                    }
                                    $reqv["PROPERTY_Dimensions"][] = array(
                                        "WEIGHT" => (floatval($reqv['PROPERTY_WEIGHT_VALUE']) > 0) ? floatval($reqv['PROPERTY_WEIGHT_VALUE']) : 0,
                                        "SIZE_1" => (floatval($reqv['PROPERTY_DIMENSIONS_VALUE'][0]) > 0) ? floatval($reqv['PROPERTY_DIMENSIONS_VALUE'][0]) : 0,
                                        "SIZE_2" => (floatval($reqv['PROPERTY_DIMENSIONS_VALUE'][1]) > 0) ? floatval($reqv['PROPERTY_DIMENSIONS_VALUE'][1]) : 0,
                                        "SIZE_3" => (floatval($reqv['PROPERTY_DIMENSIONS_VALUE'][2]) > 0) ? floatval($reqv['PROPERTY_DIMENSIONS_VALUE'][2]) : 0,
                                        "PLACES" => intval($reqv['PROPERTY_PLACES_VALUE']),
                                        "NAME" => ''
                                    );
                                }
								$reqv['PACK_GOODS'] = '';
                                if (strlen($reqv['PROPERTY_PACK_GOODS_VALUE']))
                                {
                                    $reqv['PACK_GOODS'] = json_decode(htmlspecialcharsBack($reqv['PROPERTY_PACK_GOODS_VALUE']), true);
									if ((is_array($reqv['PACK_GOODS'])) && (count($reqv['PACK_GOODS']) > 0))
									{
										foreach ($reqv['PACK_GOODS'] as $k => $str)
										{
											$reqv['PACK_GOODS'][$k]['GoodsName'] = iconv('utf-8','windows-1251',$str['GoodsName']);
											if (strlen(trim($reqv['PACK_GOODS'][$k]['GoodsName'])) == 0)
											{
												unset($reqv['PACK_GOODS'][$k]);
											}
										}
									}
                                }
                                $reqv['BRANCH_CODE'] = '';
                                if (intval($reqv['PROPERTY_BRANCH_VALUE']) > 0)
                                {
                                    $db_props = CIBlockElement::GetProperty(89, $reqv['PROPERTY_BRANCH_VALUE'], array("sort" => "asc"), array("CODE"=>"IN_1C_CODE"));
                                    if($ar_props = $db_props->Fetch())
                                    {
                                        $reqv['BRANCH_CODE'] = $ar_props["VALUE"];
                                    }
                                }
                                $arCitySENDER = GetFullNameOfCity($reqv['PROPERTY_CITY_SENDER_VALUE'], false, true);
                                $arCityRECIPIENT = GetFullNameOfCity($reqv['PROPERTY_CITY_RECIPIENT_VALUE'], false, true);
                                $date_take_from = $reqv['PROPERTY_IN_DATE_DELIVERY_VALUE'];
                                $date_take_from .= strlen($reqv['PROPERTY_IN_TIME_DELIVERY_VALUE']) ? ' '.$reqv['PROPERTY_IN_TIME_DELIVERY_VALUE'] : '';
                                $reqv['TO_1C_DELIVERY_TYPE'] = 'С';
                                $reqv['TO_1C_DELIVERY_PAYER'] = 'О';
                                $reqv['TO_1C_PAYMENT_TYPE'] = 'Б';
                                $reqv['TO_1C_DELIVERY_CONDITION'] = 'А';
                                $property_enums = CIBlockPropertyEnum::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>83, "CODE"=>"TYPE_DELIVERY", "ID" => $reqv['PROPERTY_TYPE_DELIVERY_ENUM_ID']));
                                if($enum_fields = $property_enums->GetNext())
                                {
                                    $reqv['TO_1C_DELIVERY_TYPE'] = $enum_fields['XML_ID'];
                                }
                                $property_enums = CIBlockPropertyEnum::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>83, "CODE"=>"TYPE_PAYS", "ID" => $reqv['PROPERTY_TYPE_PAYS_ENUM_ID']));
                                if($enum_fields = $property_enums->GetNext())
                                {
                                    $reqv['TO_1C_DELIVERY_PAYER'] = $enum_fields['XML_ID'];
                                }
                                $property_enums = CIBlockPropertyEnum::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>83, "CODE"=>"PAYMENT", "ID" => $reqv['PROPERTY_PAYMENT_ENUM_ID']));
                                if($enum_fields = $property_enums->GetNext())
                                {
                                    $reqv['TO_1C_PAYMENT_TYPE'] = $enum_fields['XML_ID'];
                                }
                                $property_enums = CIBlockPropertyEnum::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>83, "CODE"=>"WHO_DELIVERY", "ID" => $reqv['PROPERTY_WHO_DELIVERY_ENUM_ID']));
                                if($enum_fields = $property_enums->GetNext())
                                {
                                    $reqv['TO_1C_DELIVERY_CONDITION'] = $enum_fields['XML_ID'];
                                }
                                if ($reqv['PROPERTY_TYPE_PAYS_ENUM_ID'] == 254)
                                {
                                    $reqv['TO_1C_DELIVERY_PAYER'] = 'Д';
                                    if (strlen($reqv['PROPERTY_INSTRUCTIONS_VALUE']['TEXT']))
                                    {
                                        $reqv['PROPERTY_INSTRUCTIONS_VALUE']['TEXT'] .= ' ';
                                    }
                                    $reqv['PROPERTY_INSTRUCTIONS_VALUE']['TEXT'] .= 'Служебное.';
                                }
								$WHOSE_ORDER_ID = false;
								if (intval($reqv['PROPERTY_WHOSE_ORDER_VALUE']) > 0)
								{
									$db_props = CIBlockElement::GetProperty(40, intval($reqv['PROPERTY_WHOSE_ORDER_VALUE']), array("sort" => "asc"), array("CODE"=>"INN"));
									if($ar_props = $db_props->Fetch())
									{
										if (strlen(trim($ar_props["VALUE"])))
										{
											$WHOSE_ORDER_ID = $ar_props["VALUE"];
										}

									}	
								}
                                $agentInfo = GetCompany($reqv['PROPERTY_CREATOR_VALUE']);
                                $arManifestTo1c = array(
                                    "DeliveryNote" => $reqv['NAME'],
                                    "DATE_CREATE" => date('d.m.Y'),
                                    "SMSINFO" => 0,
                                    "INN" => $agentInfo['PROPERTY_INN_VALUE'],
                                    "NAME_SENDER" => $reqv['PROPERTY_NAME_SENDER_VALUE'],
                                    "PHONE_SENDER" => $reqv['PROPERTY_PHONE_SENDER_VALUE'],
                                    "COMPANY_SENDER" => $reqv['PROPERTY_COMPANY_SENDER_VALUE'],
                                    "CITY_SENDER_ID" => $reqv['PROPERTY_CITY_SENDER_VALUE'],
                                    "CITY_SENDER" => $reqv['PROPERTY_CITY_SENDER_NAME'],
                                    "INDEX_SENDER" => $reqv['PROPERTY_INDEX_SENDER_VALUE'],
                                    "COUNTRY_SENDER" => $arCitySENDER[2],
                                    "REGION_SENDER" => $arCitySENDER[1],
                                    "ADRESS_SENDER" => $reqv['PROPERTY_ADRESS_SENDER_VALUE']['TEXT'],
                                    "NAME_RECIPIENT" => $reqv['PROPERTY_NAME_RECIPIENT_VALUE'],
                                    "PHONE_RECIPIENT" => $reqv['PROPERTY_PHONE_RECIPIENT_VALUE'],
                                    "COMPANY_RECIPIENT" => $reqv['PROPERTY_COMPANY_RECIPIENT_VALUE'],
                                    "CITY_RECIPIENT_ID" => $reqv['PROPERTY_CITY_RECIPIENT_VALUE'],
                                    "CITY_RECIPIENT" => $reqv['PROPERTY_CITY_RECIPIENT_NAME'],
                                    "COUNTRY_RECIPIENT" => $arCityRECIPIENT[2],
                                    "INDEX_RECIPIENT" => $reqv['PROPERTY_INDEX_RECIPIENT_VALUE'],
                                    "REGION_RECIPIENT" => $arCityRECIPIENT[1],
                                    "ADRESS_RECIPIENT" => $reqv['PROPERTY_ADRESS_RECIPIENT_VALUE']['TEXT'],
                                    "PAYMENT" => $reqv["PROPERTY_FOR_PAYMENT_VALUE"],
									"PAYMENT_COD" => $reqv["PROPERTY_PAYMENT_COD_VALUE"],
                                    "DATE_TAKE_FROM" => $date_take_from,
                                    "DELIVERY_TYPE" => $reqv['TO_1C_DELIVERY_TYPE'],
                                    "DELIVERY_PAYER" => $reqv['TO_1C_DELIVERY_PAYER'],
                                    "PAYMENT_TYPE" => $reqv['TO_1C_PAYMENT_TYPE'],
                                    "DELIVERY_CONDITION" => $reqv['TO_1C_DELIVERY_CONDITION'],
                                    "INSTRUCTIONS" => $reqv['PROPERTY_INSTRUCTIONS_VALUE']['TEXT'],
                                    "TYPE" => ($reqv['PROPERTY_TYPE_PACK_ENUM_ID'] == 247) ? 0 : 1,	
                                    "Dimensions" => $reqv['PROPERTY_Dimensions'],
                                    'ID' => $reqv['ID'],
                                    'ID_BRANCH' => $reqv['BRANCH_CODE'],
                                    //'Goods' => $reqv['PACK_GOODS']
                                );
								if (is_array($reqv['PACK_GOODS']) && (count($reqv['PACK_GOODS']) > 0))
								{
									$arManifestTo1c['Goods'] = $reqv['PACK_GOODS'];
								}
								if ($WHOSE_ORDER_ID)
								{
									$arManifestTo1c['WHOSE_ORDER'] = $WHOSE_ORDER_ID;
								}
                                $arManifestTo1c["Places"] = $arManifestTo1c["Places"] + $reqv['PROPERTY_PLACES_VALUE'];
                                $arManifestTo1c["Weight"] = $arManifestTo1c["Weight"] + $reqv['PROPERTY_WEIGHT_VALUE'];
                                $arManifestTo1c["VolumeWeight"] = $arManifestTo1c["VolumeWeight"] + $reqv["PROPERTY_OB_WEIGHT"];
                            }
                            else
                            {
                                $arResult["ERRORS"][] = 'Неверный статус накладной '.trim($arResponse['Number']).': ' .$reqv["PROPERTY_STATE_VALUE"];
                            }
                        }
                        else
                        {
							$orderTo1c = makeManifestOrderfromDMSOrder(false,0,$arResponse['Number']);
							if ($orderTo1c['result'])
							{
								$arManifestTo1c = $orderTo1c['result'];
								AddToLogs('ZaprosFromDms',$orderTo1c['result']);
							}
							else
							{
								$arResult["ERRORS"][] = $orderTo1c['errors'];
								AddToLogs('ZaprosFromDms',array('error' => $orderTo1c['errors']));
							}
							/*
							$res2 = CIBlockElement::GetList(
								array("id" => "desc"), 
								array("IBLOCK_ID" => 42, "PROPERTY_N_ZAKAZ_IN" => trim($arResponse['Number'])), 
								false, 
								array("nTopCount" => 1), 
								array(
									"ID",
									"DATE_CREATE",
									"PROPERTY_*",
									"PROPERTY_212.NAME"
								)
							);
							if ($ob2 = $res2->GetNextElement())
							{
								$reqv2 = $ob2->GetFields();
								if (($reqv2['PROPERTY_203'] == 54) || ($reqv2['PROPERTY_203'] == 118))
								{
									$agentInfo = GetCompany($reqv2['PROPERTY_213']);
									$arCitySENDER = explode(',', $agentInfo['PROPERTY_CITY']);
									$arCityRECIPIENT = GetFullNameOfCity($reqv2['PROPERTY_212'],false,true);
									$comment = trim($reqv2['PROPERTY_339']['TEXT']);
									if (intval($reqv2['PROPERTY_376']) == 172)
									{
										$comment = strlen($comment) ? 'Срочный заказ! '.$comment : 'Срочный заказ!';
									}
									if (intval($reqv2['PROPERTY_446']) == 1)
									{
										$comment = strlen($comment) ? 'Необходимо подписать товарную накладную. '.$comment : 'Необходимо подписать товарную накладную.';
									}
									$DATE_TAKE_FROM = $reqv2['DATE_CREATE'];
									$DATE_TAKE_TO = $reqv2['DATE_CREATE'];
									$moths = array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
									foreach ($moths as $k => $m)
									{
										if ($pos = stripos($reqv2['PROPERTY_390'],$m))
										{
											$d = str_pad(intval(substr($reqv2['PROPERTY_390'],0,2)),2,'0',STR_PAD_LEFT);
											$mf = str_pad(($k+1),2,'0',STR_PAD_LEFT);
											$y = substr($reqv2['PROPERTY_390'],($pos+strlen($m)+1),4);
											$DATE_TAKE_FROM = $d.'.'.$mf.'.'.$y;
											$DATE_TAKE_TO = $d.'.'.$mf.'.'.$y;
											break;
										}
									}
									if ($reqv2['PROPERTY_201'] == 37)
									{
										switch ($reqv2['PROPERTY_493'])
										{
											case 215:
												$DATE_TAKE_FROM .= ' 10:00:00';
												$DATE_TAKE_TO .= ' 14:00:00';
												break;
											case 216:
												$DATE_TAKE_FROM .= ' 15:00:00';
												$DATE_TAKE_TO .= ' 18:00:00';
												break;
											default:
												$DATE_TAKE_FROM .= ' 10:00:00';
												$DATE_TAKE_TO .= ' 18:00:00';
										}
									}
									$arManifestTo1c = array(
										"DeliveryNote" => $reqv2['PROPERTY_402'],
										"DATE_CREATE" => $reqv2['DATE_CREATE'],
										"SMSINFO" => 0,
										"INN" => $agentInfo['PROPERTY_INN_VALUE'],
										"NAME_SENDER" => $agentInfo['PROPERTY_RESPONSIBLE_PERSON_VALUE'],
										"PHONE_SENDER" => $agentInfo['PROPERTY_PHONES_VALUE'],
										"COMPANY_SENDER" => $agentInfo['NAME'],
										"CITY_SENDER_ID" => $agentInfo['PROPERTY_CITY_VALUE'],
										"CITY_SENDER" => $agentInfo['PROPERTY_CITY_NAME'],
										"INDEX_SENDER" => '',
										"COUNTRY_SENDER" => $arCitySENDER[2],
										"REGION_SENDER" => $arCitySENDER[1],
										"ADRESS_SENDER" => $agentInfo['PROPERTY_ADRESS_VALUE'],
										"NAME_RECIPIENT" => $reqv2['PROPERTY_208'],
										"PHONE_RECIPIENT" => $reqv2['PROPERTY_209'],
										"COMPANY_RECIPIENT" => '',
										"CITY_RECIPIENT_ID" => $reqv2['PROPERTY_212'],
										"CITY_RECIPIENT" => $reqv2['PROPERTY_212_NAME'],
										"COUNTRY_RECIPIENT" => $arCityRECIPIENT[2],
										"INDEX_RECIPIENT" => '',
										"REGION_RECIPIENT" => $arCityRECIPIENT[1],
										"ADRESS_RECIPIENT" => $reqv2['PROPERTY_202'],
										"PAYMENT" => 0,
										"PAYMENT_COD" => floatval($reqv2["PROPERTY_198"]),
										"DATE_TAKE_FROM" => $DATE_TAKE_FROM,
										"DATE_TAKE_TO" => $DATE_TAKE_TO,
										"DELIVERY_TYPE" => 'С',
										"DELIVERY_PAYER" => 'О',
										"PAYMENT_TYPE" => 'Н',
										"DELIVERY_CONDITION" => ($reqv2['PROPERTY_201'] == 38) ? 'Д' : 'А',
										"INSTRUCTIONS" => $comment,
										"TYPE" => 0,	
										"Dimensions" => array(
											array(
											'PLACES' => intval($reqv2['PROPERTY_232']),
											'WEIGHT' => floatval($reqv2['PROPERTY_225']),
											'SIZE_1' => intval($reqv2['PROPERTY_247']),
											'SIZE_2' => intval($reqv2['PROPERTY_248']),
											'SIZE_3' => intval($reqv2['PROPERTY_249']),
											"NAME" => ''
											)
										),
										'ID' => $reqv2['ID'],
										'ID_BRANCH' => '',
									);
								}
								else
								{
									$arResult["ERRORS"][] = 'Неверный статус накладной '.trim($arResponse['Number']);
								}
							}
							else
							{
								$arResult["ERRORS"][] = 'Накладная '.trim($arResponse['Number']).' не найдена';
							}
							*/
                        }
                    }
                    else
                    {
                        $arResult["ERRORS"][] = 'Пустой номер накладной';
                    }
                }
                else
                {
                    $arResult["ERRORS"][] = 'Ошибка авторизации';
                }
            }
            else
            {
                $arResult["ERRORS"][] = 'Отсутствует логин или пароль';   
            }
        }
        else
        {
            $arResult["ERRORS"][] = 'Некорректный запрос';
        }
        $arResult['RESULTS'] = array(
            'ERRORS' => $arResult["ERRORS"],
            'INFO' => $arManifestTo1c
        );
        foreach ($arResult['RESULTS'] as $k => $v)
        {
            foreach ($v as $kk => $vv)
            {
				if (is_array($vv))
				{
					foreach ($vv as $kkk => $vvv)
					{
						if (is_array($vvv))
						{
							foreach ($vvv as $kkkk => $vvvv)
							{
								$arLog[$k.' '.$kk.' '.$kkk.' '.$kkkk] = $vvvv;
							}
						}
						else
						{
							$arLog[$k.' '.$kk.' '.$kkk] = $vvv;
						}
					}
				}
				else
				{
					$arLog[$k.' '.$kk] = $vv;
				}
                
            }
        }
        AddToLogs('AcceptanceOnRequest',$arLog);
        $arResult['RESULTS'] = convArrayToUTF($arResult['RESULTS']);
        $arResult['RES_JSON'] = json_encode($arResult['RESULTS']);
    }
}
$this->IncludeComponentTemplate($arResult['MODE']);