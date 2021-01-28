<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");
$currentip = GetSettingValue(683);
if (!strlen(trim($currentip)))
{
	?>
    <div class="alert alert-dismissable alert-danger fade in" role="alert">
    	Не указан IP для подключения к 1с<br>
    	Пожалуйста, обратитесь к администратору
	</div>
    <?
}
else
{
	$arResult['OPEN'] = true;
	$arResult['ADMIN_AGENT'] = false;
	$arResult["PAGES"] = array(20, 50, 100, 200);
	$arResult['USER_IN_BRANCH'] = false;
	$arResult['BRANCH_AGENT_BY'] = false;
	$arResult['CLIENT_CONTRACT'] = false;
	
	$modes = array(
		'list',
		'add',
		'print',
		'invoice',
		'invoice_modal',
		'invoice_tracking',
		'edit',
		'1c',
		'pdf',
		
	);
	
	$arAcc = array(
		'list' => true,
		'add' => true,
		'print' => true,
		'invoice' => true,
		'invoice_modal' => true,
		'invoice_tracking' => true,
		'edit' => true,
		'1c' => false,
		'pdf' => true
	);
	 
	if ((strlen($arParams['MODE'])) && (in_array($arParams['MODE'], $modes)))
	{
		$mode = $arParams['MODE'];
	}
	else
	{
		if ((strlen(trim($_GET['mode']))) && (in_array(trim($_GET['mode']), $modes)))
		{
			$mode = trim($_GET['mode']);
		}
		else
		{
			$mode = $modes[0];
		}
	}
	
	if ($arAcc[$mode])
	{
		$rsUser = CUser::GetByID($USER->GetID());
		$arUser = $rsUser->Fetch();
		$agent_id = intval($arUser["UF_COMPANY_RU_POST"]);
		
		if ($agent_id > 0)
		{
			$db_props = CIBlockElement::GetProperty(40, $agent_id, array("sort" => "asc"), array("ID"=>211));
			if($ar_props = $db_props->Fetch())
			{
				$agent_type = $ar_props["VALUE"];
				if (in_array($agent_type, array(51, 242)))
				{
					$arResult['AGENT'] = GetCompany($agent_id);
					if ($agent_type == 51)
					{
						$arResult['ADMIN_AGENT'] = true;
					}
					else
					{
                        /*Определяем тип работы клиента с филиалами*/
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
                        /*Определяем тип работы клиента с филиалами*/
                        /*Если работаем с филиалом*/
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
                         /*Если работаем с филиалом*/
						/*Контракты*/
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
                        /*контракты*/
					}
				}
				else
				{
					$arResult['OPEN'] = false;
					$arResult["ERRORS"][] = GetMessage("ERR_OPEN");
				}
			}
			else
			{
				$arResult['OPEN'] = false;
				$arResult["ERRORS"][] = GetMessage("ERR_OPEN");
			}
		}
		else
		{
			$arResult['OPEN'] = false;
			$arResult["ERRORS"][] = GetMessage("ERR_OPEN");
		}
	}
	
	
	
	
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
	
	if ($mode == 'list')
	{
		$arResult['YEARS'] = array(2014 => 2014, 2015 => 2015, 2016 => 2016, 2017 => 2017);
		$arResult['MONTHS'] = array(
			'01' => 'январь',
			'02' => 'февраль',
			'03' => 'март',
			'04' => 'апрель',
			'05' => 'май',
			'06' => 'июнь',
			'07' => 'июль',
			'08' => 'август',
			'09' => 'сентябрь',
			'10' => 'октябрь',
			'11' => 'ноябрь',
			'12' => 'декабрь',
		);
		
		$arResult['CURRENT_MONTH'] =  date('m');
		$arResult['CURRENT_YEAR'] =  date('Y');
		
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
	
		if (strlen($_SESSION['CURRENT_MONTH']))
		{
			$arResult['CURRENT_MONTH'] = $_SESSION['CURRENT_MONTH'];
		}
		if (strlen($_SESSION['CURRENT_YEAR']))
		{
			$arResult['CURRENT_YEAR'] = $_SESSION['CURRENT_YEAR'];
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
			if (isset($arResult['YEARS'][$_GET['year']]))
			{
				$_SESSION['CURRENT_YEAR'] = $_GET['year'];
				$arResult['CURRENT_YEAR'] = $_GET['year'];
			}
			if (isset($arResult['MONTHS'][$_GET['month']]))
			{
				$_SESSION['CURRENT_MONTH'] = $_GET['month'];
				$arResult['CURRENT_MONTH'] = $_GET['month'];
			}
		}
		$datetime = strtotime($arResult['CURRENT_YEAR'].'-'.$arResult['CURRENT_MONTH'].'-01');
		$last_day = date('t', $datetime);
		
		
		$arResult['LIST_OF_CLIENTS'] = false;
		
		if ($arResult['ADMIN_AGENT'])
		{
			$arResult['LIST_OF_CLIENTS'] = AvailableClients(false);
			if ($_GET['ChangeClient'] == 'Y')
			{
				if (isset($arResult['LIST_OF_CLIENTS'][$_GET['client']]))
				{
					$_SESSION['CURRENT_CLIENT'] = $_GET['client'];
					$arResult['CURRENT_CLIENT'] = $_GET['client'];
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
							"PROPERTY_COST",
							"PROPERTY_PLACES",
							"PROPERTY_WEIGHT",
							"PROPERTY_DIMENSIONS",
							"PROPERTY_STATE",
							"PROPERTY_INSTRUCTIONS",
							"PROPERTY_PACK_DESCRIPTION"
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
									"PLACES" => intval($str["place"])
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
								"PLACES" => intval($reqv['PROPERTY_PLACES_VALUE'])
							);
						}
						// $reqv["PROPERTY_OB_WEIGHT"] = WeightFormat($r['PROPERTY_OB_WEIGHT'],false);
						
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
                        $arManifestTo1c['Delivery'][] = array(
                            "DeliveryNote" => $reqv['NAME'],
                            "DATE_CREATE" => date('d.m.Y'),
                            "SMSINFO" => 0,
                            "INN" => $arResult['CURRENT_CLIENT_INN'],
                            "NAME_SENDER" => $reqv['PROPERTY_NAME_SENDER_VALUE'],
                            "PHONE_SENDER" => $reqv['PROPERTY_PHONE_SENDER_VALUE'],
                            "COMPANY_SENDER" => $reqv['PROPERTY_COMPANY_SENDER_VALUE'],
                            "CITY_SENDER" => $reqv['PROPERTY_CITY_SENDER_NAME'],
                            "INDEX_SENDER" => $reqv['PROPERTY_INDEX_SENDER_VALUE'],
                            "COUNTRY_SENDER" => $arCitySENDER[2],
                            "REGION_SENDER" => $arCitySENDER[1],
                            "ADRESS_SENDER" => $reqv['PROPERTY_ADRESS_SENDER_VALUE']['TEXT'],
                            "NAME_RECIPIENT" => $reqv['PROPERTY_NAME_RECIPIENT_VALUE'],
                            "PHONE_RECIPIENT" => $reqv['PROPERTY_PHONE_RECIPIENT_VALUE'],
                            "COMPANY_RECIPIENT" => $reqv['PROPERTY_COMPANY_RECIPIENT_VALUE'],
                            "CITY_RECIPIENT" => $reqv['PROPERTY_CITY_RECIPIENT_NAME'],
                            "COUNTRY_RECIPIENT" => $arCityRECIPIENT[2],
                            "INDEX_RECIPIENT" => $reqv['PROPERTY_INDEX_RECIPIENT_VALUE'],
                            "REGION_RECIPIENT" => $arCityRECIPIENT[1],
                            "ADRESS_RECIPIENT" => $reqv['PROPERTY_ADRESS_RECIPIENT_VALUE']['TEXT'],
                            "PAYMENT" => $reqv["PROPERTY_FOR_PAYMENT_VALUE"],
                            "DATE_TAKE_FROM" => $date_take_from,
                            "DELIVERY_TYPE" => $reqv['TO_1C_DELIVERY_TYPE'],
                            "DELIVERY_PAYER" => $reqv['TO_1C_DELIVERY_PAYER'],
                            "PAYMENT_TYPE" => $reqv['TO_1C_PAYMENT_TYPE'],
                            "DELIVERY_CONDITION" => $reqv['TO_1C_DELIVERY_CONDITION'],
                            "INSTRUCTIONS" => $reqv['PROPERTY_INSTRUCTIONS_VALUE']['TEXT'],
                            "TYPE" => ($reqv['PROPERTY_TYPE_PACK_ENUM_ID'] == 247) ? 0 : 1,	
                            "Dimensions" => $reqv['PROPERTY_Dimensions'],
                            'ID' => $reqv['ID']
                        );
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
					$sob_id = CEvent::Send("NEWPARTNER_LK", "s5", array("EMAIL_FROM" => $arUser['EMAIL'], "COMPANY_FROM" => '<a href="http://client.newpartner.ru/index.php?ChangeClient=Y&client='.$arResult['CURRENT_CLIENT'].'" target="_blank">'.$arResult['LIST_OF_CLIENTS'][$arResult['CURRENT_CLIENT']].'</a>', "CREATOR" => $arUser['LAST_NAME'].' '.$arUser['NAME']), "N", 192, array($fid));
					
					/*
					$rsUser = CUser::GetByID($USER->GetID());
					$arUser = $rsUser->Fetch();
					$params = array(
						"#CREATOR#" => $arUser['LAST_NAME'].' '.$arUser['NAME'],
						"#EMAIL_FROM#" => $arUser['EMAIL'],
						"#COMPANY_FROM#" => strlen($arUser['WORK_COMPANY']) ? ' от '.$arUser['WORK_COMPANY'] : ''
					);
					$rsEM = CEventMessage::GetByID(192);
					$arEM = $rsEM->Fetch();
					$txt = $arEM["MESSAGE"];
					foreach ($params as $k => $v)
					{
						$txt = str_replace($k, $v, $txt);
					}
					$subj = $arEM['SUBJECT'];
					foreach ($params as $k => $v)
					{
						$subj = str_replace($k, $v, $subj);
					}
					$from = $arEM['EMAIL_FROM'];
					foreach ($params as $k => $v)
					{
						$from = str_replace($k, $v, $from);
					}
					include_once $_SERVER['DOCUMENT_ROOT']."/bitrix/_kerk/class.phpmailer.php";
					$mail = new PHPMailer();
					$mail->Priority = 1; 
					$mail->From = $from;
					$mail->FromName = $arUser['LAST_NAME'].' '.$arUser['NAME'];                                                   
					$mail->AddAddress($arEM['EMAIL_TO'], ''); 
					$mail->IsHTML(true);                                                        
					$mail->Subject = $subj;
					$mail->AddAttachment($_SERVER['DOCUMENT_ROOT'].$path);
					$mail->ContentType = "text/html";
					$mail->Body = $txt;
					$mail->Send();
					*/
					
					foreach ($_POST['ids'] as $r)
					{
						CIBlockElement::SetPropertyValuesEx($r, 83, array(572 => 258, 573 => date('d.m.Y H:i:s')));
					}
					
					$arManifestTo1cUTF = array();		
					foreach ($arManifestTo1c as $kk => $vv)
					{
						if (is_array($vv))
						{
							foreach ($vv as $kkk => $vvv)
							{
								if (is_array($vvv))
								{
									foreach ($vvv as $kkkk => $vvvv)
									{
										//$arManifestTo1cUTF[$kk][$kkk][$kkkk] = iconv('windows-1251','utf-8', $vvvv);
										if (is_array($vvvv))
										{
											foreach ($vvvv as $kkkkk => $vvvvv)
											{
												if (is_array($vvvvv))
												{
													foreach ($vvvvv as $kkkkkk => $vvvvvv)
													{
														$arManifestTo1cUTF[$kk][$kkk][$kkkk][$kkkkk][$kkkkkk] = iconv('windows-1251','utf-8', $vvvvvv);
													}
												}
												else
												{
													$arManifestTo1cUTF[$kk][$kkk][$kkkk][$kkkkk] = iconv('windows-1251','utf-8', $vvvvv);
												}
											}
										}
										else
										{
											$arManifestTo1cUTF[$kk][$kkk][$kkkk] = iconv('windows-1251','utf-8', $vvvv);
										}
									}
								}
								else
								{
									$arManifestTo1cUTF[$kk][$kkk] = iconv('windows-1251','utf-8', $vvv);
								}
							}
						}
						else
						{
							$arManifestTo1cUTF[$kk] = iconv('windows-1251','utf-8', $vv);
						}
					}
					$infoMan = '';
					$url = 'http://'.$currentip.'/sd_msk/ws/DashboardExchange.1cws?wsdl';
					$curl = curl_init();
					curl_setopt_array($curl, array(    
						CURLOPT_URL => $url,
						CURLOPT_HEADER => true,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_NOBODY => true,
						CURLOPT_TIMEOUT => 10));
					
					$header = explode("\n", curl_exec($curl));
					curl_close($curl);
					
					if (strlen(trim($header[0])))
					{
						$client = new SoapClient(
							'http://'.$currentip.'/sd_msk/ws/DashboardExchange.1cws?wsdl',  
							array('login' => 'DMSUser', 'password' => "1597534682",'exceptions' => false)
						);
						/*
						$result = $client->SetManifest_test(array('ListOfDocs' => json_encode($arManifestTo1cUTF)));
						$mnumberMan = $result->return;
						$mnumberManUTF =  iconv('utf-8', 'windows-1251', $mnumberMan);
						if (strlen($mnumberManUTF))
						{
							$infoMan = ' <strong>Манифест '.$mnumberManUTF.' загружен в 1с</strong>.';
						}
						*/
						$result = $client->SetManifest(array('ListOfDocs' => json_encode($arManifestTo1cUTF)));
						$mResult = $result->return;
                        $obj = json_decode($mResult, true);
                        $arRes = arFromUtfToWin($obj);
						if (strlen($arRes['RecordedManifest']))
						{
							$infoMan = ' <strong>Манифест '.$arRes['RecordedManifest'].', содержащий накладные '.implode(', ',$arRes['ReceivedОrders']).' загружен в 1с</strong>.';
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
                            //$arResult["WARNINGS"][] = '';
                        }
					}
					else
					{
						echo 'нет подключения к 1с<br>'.$url;
					}
					//$sob_id = CEvent::Send("NEWPARTNER_LK", "s5", array("JSON" => json_encode($arManifestTo1cUTF).' '.$mnumberManUTF), "N", 211);
					$arResult["MESSAGE"][] = 'Накладные успешно приняты.'.$infoMan.' <a href="'.$path.'" target="_blank">Скачать манифест</a>';
				}
				else
				{
					$arResult["ERRORS"][] = 'Не выбраны накладные для передачи на доставку';
				}
			}
		}
		
		$arResult['REQUESTS'] = array();
		
		/*
		if (intval($_GET['on_page']) > 0)
		{
			$arResult['ON_PAGE'] = intval($_GET['on_page']);
			$_SESSION['ON_PAGE_REQVS'] = $arResult['ON_PAGE'];
		}
		else
		{
			if (intval($_SESSION['ON_PAGE_REQVS']) > 0)
			{
				$arResult['ON_PAGE'] = $_SESSION['ON_PAGE_REQVS'];
			}
			else
			{
				$arResult['ON_PAGE'] = 20;
			}
		}	
	
		$nav_array = array("nPageSize" => $arResult['ON_PAGE']);
		*/
		
		if (intval($arResult['CURRENT_CLIENT']) > 0)
		{
			$nav_array =  false;
			
			$filter = array("IBLOCK_ID" => 83, "PROPERTY_CREATOR" => intval($arResult['CURRENT_CLIENT']), "ACTIVE" => "Y");
			$filter[">=DATE_CREATE"] = '01.'.$arResult['CURRENT_MONTH'].'.'.$arResult['CURRENT_YEAR'].' 00:00:00';
			$filter["<=DATE_CREATE"] = $last_day.'.'.$arResult['CURRENT_MONTH'].'.'.$arResult['CURRENT_YEAR'].' 23:59:59';
			
			if (intval($arResult['CURRENT_BRANCH']) > 0)
			{
				$filter["PROPERTY_BRANCH"] = intval($arResult['CURRENT_BRANCH']);
			}
			/*
			if (strlen(trim($_GET['number'])))
			{
				$filter['NAME'] = '%'.trim($_GET['number']).'%';
			}
			if (intval($_GET['state']) > 0)
			{
				$filter["PROPERTY_STATE"] = intval($_GET['state']);
			}
		
			if (strlen(trim($_GET['date_from'])))
			{
				$filter[">=DATE_CREATE"] = trim($_GET['date_from']).' 00:00:00';
			}
			if (strlen(trim($_GET['date_to'])))
			{
				$filter["<=DATE_CREATE"] = trim($_GET['date_to']).' 23:59:59';
			}
			*/
			$sorts_by = array("created", "name", "PROPERTY_STATE");
			$sorts = array("desc", "asc");
			if ((strlen($_GET['sort_by'])) && (in_array($_GET['sort_by'], $sorts_by)))
			{
				$arResult['SORT_BY'] = $_GET['sort_by'];
				$_SESSION['SORT_BY_REQVS'] = $arResult['SORT_BY'];
			}
			else
			{
				if (strlen($_SESSION['SORT_BY_REQVS']))
				{
					$arResult['SORT_BY'] = $_SESSION['SORT_BY_REQVS'];
				}
				else
				{
					$arResult['SORT_BY'] = $sorts_by[0];
				}
			}
			if ((strlen($_GET['sort'])) && (in_array($_GET['sort'], $sorts)))
			{
				$arResult['SORT'] = $_GET['sort'];
				$_SESSION['SORT_REQVS'] = $arResult['SORT'];
			}
			else
			{
				if (strlen($_SESSION['SORT_REQVS']))
				{
					$arResult['SORT'] = $_SESSION['SORT_REQVS'];
				}
				else
				{
					$arResult['SORT'] = $sorts[0];
				}
			}
			$res = CIBlockElement::GetList(
				array($arResult['SORT_BY'] => $arResult['SORT']), 
				$filter, 
				false, 
				$nav_array, 
				array(
					"ID",
					"NAME",
					"DATE_CREATE",
					"PROPERTY_COMPANY_SENDER",
					"PROPERTY_CITY_SENDER.NAME",
					"PROPERTY_COMPANY_RECIPIENT",
					"PROPERTY_CITY_RECIPIENT.name",
					"PROPERTY_PLACES",
					"PROPERTY_WEIGHT",
					"PROPERTY_DIMENSIONS",
					"PROPERTY_STATE",
					"PROPERTY_STATE_DESCR",
					"PROPERTY_RATE",
					"PROPERTY_BRANCH.NAME",
					"PROPERTY_PACK_DESCRIPTION"
				)
			);
			$arResult["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject,"Накладные","","Y");
			while ($ob = $res->GetNextElement())
			{
				$a = $ob->GetFields();
				
				$a['ColorRow'] = '';
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
				
				if ($agent_type == 242)
				{
					switch ($a['PROPERTY_STATE_ENUM_ID'])
					{
						case 276:
							$a['ColorRow'] = 'danger';
							$a['state_icon'] = '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр трекинга"></span>';
							$a['state_text'] = $a['PROPERTY_STATE_DESCR_VALUE'];
							break;
						case 275:
							$a['ColorRow'] = 'supersuccess';
							$a['state_icon'] = '<span class="glyphicon glyphicon-check" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр трекинга"></span>';	
							$a['state_text'] = $a['PROPERTY_STATE_VALUE'];
					}
				}
				else
				{
					switch ($a['PROPERTY_STATE_ENUM_ID'])
					{
						case 278:
							$a['state_icon'] = '<span class="glyphicon glyphicon-send" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр трекинга"></span>';
							$a['state_text'] = $a['PROPERTY_STATE_DESCR_VALUE'];
							break;
						case 273:
							$a['state_icon'] = '<span class="glyphicon glyphicon-road" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр трекинга"></span>';
							$a['state_text'] = 'Выдано на маршрут';
							break;
						case 276:
							$a['ColorRow'] = 'danger';
							$a['state_icon'] = '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр трекинга"></span>';
							$a['state_text'] = $a['PROPERTY_STATE_DESCR_VALUE'];
							break;
						case 258:
							$a['ColorRow'] = 'success';
							$a['state_icon'] = '<span class="glyphicon glyphicon-log-in" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр трекинга"></span>';
							break;
						case 277:
							$a['state_icon'] = '<span class="glyphicon glyphicon-download-alt" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр трекинга"></span>';
							$a['state_text'] = $a['PROPERTY_STATE_DESCR_VALUE'];
							break;
						case 275:
							$a['ColorRow'] = 'supersuccess';
							$a['state_icon'] = '<span class="glyphicon glyphicon-check" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр трекинга"></span>';			
				}
				}
				$arResult['REQUESTS'][] = $a;
			}
			$arResult['STATES'] = array();
			$db_enum_list = CIBlockProperty::GetPropertyEnum(572, Array(), Array("IBLOCK_ID"=>83));
			while($ar_enum_list = $db_enum_list->GetNext())
			{
				$arResult['STATES'][$ar_enum_list["ID"]] = $ar_enum_list["VALUE"];
			}
		}
		
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
	
	if ($mode == 'print')
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
	
	if (($mode == 'invoice') || ($mode == 'invoice_modal'))
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
					"PROPERTY_PACK_DESCRIPTION"
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
	
	if ($mode == 'edit')
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
		
		if ((isset($_POST['save'])) || (isset($_POST['save-print'])) || (isset($_POST['save_ctrl'])))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
				$_SESSION[$_POST["key_session"]] = $_POST["rand"];
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
				$arChanges = array(
					550 => deleteTabs($_POST['INDEX_SENDER']),
					556 => $_POST['INDEX_RECIPIENT'],
					560 => deleteTabs($_POST['IN_DATE_DELIVERY']),
					561 => deleteTabs($_POST['IN_TIME_DELIVERY']),
					565 => floatval(str_replace(',','.',$_POST['FOR_PAYMENT'])),
					566 => floatval(str_replace(',','.',$_POST['COST'])),
					569 => $_POST['DIMENSIONS'],
					570 => array('VALUE' => array('TYPE' => 'text', 'TEXT' => deleteTabs($_POST['INSTRUCTIONS']))),
					682 => json_encode($arJsonDescr)
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
				if (!$_POST['PAYMENT'])
				{
					$arResult["ERR_FIELDS"]["PAYMENT"] = 'has-error';
				}
				else
				{
					$arChanges[564] = $_POST['PAYMENT'];
				}
				/*
				if (!strlen($_POST['PLACES']))
				{
					$arResult["ERR_FIELDS"]["PLACES"] = 'has-error';
				}
				else
				{
					if (intval($_POST['PLACES']) == 0)
					{
						$arResult["ERR_FIELDS"]["PLACES"] = 'has-error';
					}
					else
					{
						$arChanges[567] = intval($_POST['PLACES']);
					}
				}
				if (!strlen($_POST['WEIGHT']))
				{
					$arResult["ERR_FIELDS"]["WEIGHT"] = 'has-error';
				}
				else
				{
					if (floatval(str_replace(',','.',$_POST['WEIGHT'])) <= 0)
					{
						$arResult["ERR_FIELDS"]["WEIGHT"] = 'has-error';
					}
					else
					{
						$arChanges[568] = floatval(str_replace(',','.',$_POST['WEIGHT']));
					}
				}
				*/
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
				CIBlockElement::SetPropertyValuesEx($_POST['id'], 83, $arChanges);
				//$arResult["MESSAGE"][] = 'Накладная '.$_POST['number'].' успешно изменена';
                $_SESSION['MESSAGE'][] = 'Накладная '.$_POST['number'].' успешно изменена';
				// LocalRedirect("/index.php?mode=list");
				if (isset($_POST['save-print']))
				{
					//LocalRedirect("/index.php?mode=print&id=".$_POST['id']."&print=Y");
                    LocalRedirect("/index.php?openprint=Y&id=".$_POST['id']);
				}
                else
                {
                    LocalRedirect("/index.php");
                }
			}
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
					"PROPERTY_COST",
					"PROPERTY_PLACES",
					"PROPERTY_WEIGHT",
					"PROPERTY_DIMENSIONS",
					"PROPERTY_STATE",
					"PROPERTY_INSTRUCTIONS",
					"PROPERTY_PACK_DESCRIPTION"
				)
			);
			if ($ob = $res->GetNextElement())
			{
				$r = $ob->GetFields();
				if ($r['PROPERTY_STATE_ENUM_ID'] != 257)
				{
					LocalRedirect("/index.php?mode=list");
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
	
	if ($mode == 'add')
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
		
		if ((isset($_POST['add'])) || (isset($_POST['add-print'])) || (isset($_POST['add_ctrl'])))
		{
			if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
				$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
			}
			else
			{
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
				}
				if (!$_POST['PAYMENT'])
				{
					$arResult["ERR_FIELDS"]["PAYMENT"] = 'has-error';
				}
				
				/*
				if (!strlen($_POST['PLACES']))
				{
					$arResult["ERR_FIELDS"]["PLACES"] = 'has-error';
				}
				else
				{
					if (intval($_POST['PLACES']) == 0)
					{
						$arResult["ERR_FIELDS"]["PLACES"] = 'has-error';
					}
				}
				if (!strlen($_POST['WEIGHT']))
				{
					$arResult["ERR_FIELDS"]["WEIGHT"] = 'has-error';
				}
				else
				{
					if (floatval(str_replace(',','.',$_POST['WEIGHT'])) <= 0)
					{
						$arResult["ERR_FIELDS"]["WEIGHT"] = 'has-error';
					}
				}
				*/
				
	
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
	
	
				if (count($arResult["ERR_FIELDS"]) == 0)
				{
					$id_in = MakeInvoiceNumber(83, 7, '90-');
					$number_nakl = strlen(NewQuotes($_POST['NUMBER'])) ? NewQuotes($_POST['NUMBER']) : $id_in['number'];
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
							563 => deleteTabs($_POST['PAYS']),
							564 => $_POST['PAYMENT'],
							565 => floatval(str_replace(',','.',$_POST['FOR_PAYMENT'])),
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
						),
						"NAME" => $number_nakl,
						"ACTIVE" => "Y"
					);
					if ($z_id = $el->Add($arLoadProductArray))
					{
						$_SESSION['MESSAGE'][] = "Накладная №".$number_nakl." успешно создана";
						
						/*вызов курьера*/
						if ($_POST['callcourier'] == 'yes')
						{
							$id_in_cur = GetMaxIDIN(87, 7);
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
									620 => NewQuotes($_POST['callcourcomment']).' Накладная №'.$number_nakl
								),
								"NAME" => 'Вызов курьера №'.$id_in_cur,
								"ACTIVE" => "Y"
							);
						
							if ($z_id = $el->Add($arLoadProductArray))
							{
                               
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
									"AGENT_EMAIL" => $arResult['ADD_AGENT_EMAIL']
								);
								$_POST = array();
								$event = new CEvent;
								$event->SendImmediate("NEWPARTNER_LK", "S5", $arEventFields, "N", 199);
                                
                                /*звонок о новых накладных*/
                                if ((intval(date('G')) >=17) || (intval(date('G')) < 8))
                                {
                                    include_once $_SERVER["DOCUMENT_ROOT"].'bitrix/_black_mist/zadarma/Client.php';
                                    $params = array(
                                        'from' => '+79032444272',
                                        'to' => '+79031111111',
                                    );
                                    $zd = new \Zadarma_API\Client("44c738b94aef4db7b31b", "c6406ab4bc31d8657805");
                                    $answer = $zd->call('/v1/request/callback/', $params);
                                }
                                /*звонок о новых накладных*/
                                
								$_SESSION["MESSAGE"][] = "Вызов курьера №".$id_in_cur." успешно зарегистрирован";
							}
						}
						
						/*вызов курьера*/
						
						$res = CIBlockElement::GetList(
							array("ID" =>"desc"), 
							array(
								"IBLOCK_ID" => 84, 
								"PROPERTY_CREATOR" => $agent_id, 
								"NAME" => NewQuotes($_POST['COMPANY_RECIPIENT']), 
								"PROPERTY_CITY" => $city_recipient, 
								"PROPERTY_ADRESS" => NewQuotes($_POST['ADRESS_RECIPIENT']),
								"PROPERTY_TYPE" => 260
							),
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
									579 => $agent_id,
									574 => NewQuotes($_POST['NAME_RECIPIENT']),
									575 => NewQuotes($_POST['PHONE_RECIPIENT']),
									576 => $city_recipient,
									577 => $_POST['INDEX_RECIPIENT'],
									578 => NewQuotes($_POST['ADRESS_RECIPIENT']),
									580 => 260,
									668 => $arResult['USER_IN_BRANCH'] ? $arResult['CURRENT_BRANCH'] : false
								),
								"NAME" => NewQuotes($_POST['COMPANY_RECIPIENT']),
								"ACTIVE" => "Y"
							);
							$rec_id = $el2->Add($arLoadProductArray2);
						}
						if (isset($_POST['add-print']))
						{
                           LocalRedirect("/index.php?openprint=Y&id=".$z_id);
                            //LocalRedirect("/index.php?mode=print&id=".$z_id."&print=Y");
						}
						else
						{
                            LocalRedirect("/index.php");
							
						}
					}
					else
					{
						$arResult['ERRORS'][] = $el->LAST_ERROR;
					}
				}
			}
		}
		
		$arResult['DEAULTS'] = array(
			/*
			'COMPANY_SENDER' => strlen($arResult['AGENT']['PROPERTY_LEGAL_NAME_VALUE']) ? $arResult['AGENT']['PROPERTY_LEGAL_NAME_VALUE'] : $arResult['AGENT']['NAME'],
			'ADRESS_SENDER' => $arResult['AGENT']['PROPERTY_ADRESS_VALUE'],
			'CITY_SENDER' =>  $arResult['AGENT']['PROPERTY_CITY'],
			*/
			
			/*
			'NAME_SENDER' => $USER->GetFullName(),
			'PHONE_SENDER' => $arUser['PERSONAL_PHONE'],
			*/
			
				'callcourierdate' => date('d.m.Y', strtotime("+1 day")),
				'callcouriertime_from' => '10:00',
				'callcouriertime_to' => '18:00',
	
			'PLACES' => 1,
			'TYPE_DELIVERY' => 244,
			'TYPE_PACK' => 246,
			'WHO_DELIVERY' => 248,
			'TYPE_PAYS' => 251,
			'PAYMENT' => 256,
			'WEIGHT' => '0,2'
		);
		
	
		
		$br = $arResult['USER_IN_BRANCH'] ? $arResult['CURRENT_BRANCH'] : false;
		$arResult['SENDERS'] = GetListContractors($agent_id, 259, false, '', array("NAME"=>"ASC"), false, false, false, $br);
		if (count($arResult['SENDERS']) == 0)
		{
			/*
			$arResult['OPEN'] = false;
			$arResult["ERRORS"][] = GetMessage('ERR_NO_SENDERS');
			*/
			$props = array(
				579 => $agent_id,
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
				$branch_info = GetBranch($arResult['CURRENT_BRANCH'], $agent_id);
				$props[574] = $branch_info['PROPERTY_FIO_VALUE'];
				$props[575] = $branch_info['PROPERTY_PHONE_VALUE'];
				$props[576] = $branch_info['PROPERTY_CITY_VALUE'];
				$props[577] = $branch_info['PROPERTY_INDEX_VALUE'];
				$props[578] = $branch_info['PROPERTY_ADRESS_VALUE'];
				$props[668] = $arResult['CURRENT_BRANCH'];
				$name .= ', '.$branch_info['NAME'];
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
			$arResult['SENDERS'] = GetListContractors($agent_id, 259, false, '', array("NAME"=>"ASC"), false, false, false, $br);
			
		}
		$arResult['TITLE'] = GetMessage("TITLE_MODE_ADD");
		$APPLICATION->SetTitle(GetMessage("TITLE_MODE_ADD"));
	}
	
	if ($mode == '1c')
	{
		if ((strlen($_GET['login'])) && (strlen($_GET['pass'])))
		{
			if (($_GET['login'] == 'DMSUser') && ($_GET['pass'] == '1597534682'))
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
}
$this->IncludeComponentTemplate($mode);