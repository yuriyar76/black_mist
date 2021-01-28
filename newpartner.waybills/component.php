<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
ini_set("soap.wsdl_cache_enabled", "0" );
ini_set("default_socket_timeout", "300");
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");
$currentip = GetSettingValue(683);
$currentlink = GetSettingValue(704);

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
	$url = "http://".$currentip.$currentlink;
	$curl = curl_init();
	curl_setopt_array($curl, array(    
		CURLOPT_URL => $url,
		CURLOPT_HEADER => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_NOBODY => true,
		CURLOPT_TIMEOUT => 5));
	$header = explode("\n", curl_exec($curl));
	curl_close($curl);
	if (strlen(trim($header[0])))
	{
		$arResult['OPEN'] = true;
		$arResult['ADMIN_AGENT'] = false;
		// $arResult["PAGES"] = array(20, 50, 100, 200);
		$modes = array(
			'list',
			'add',
			'print',
			'invoice',
			'edit',
			'rates',
			'pdf',
			'invoice1c_modal',
            'list_xls'
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
		
		$rsUser = CUser::GetByID($USER->GetID());
		$arUser = $rsUser->Fetch();
		$agent_id = intval($arUser["UF_COMPANY_RU_POST"]);
		$arResult['USER_NAME'] = $USER->GetFullName();
		
		if ($agent_id > 0)
		{
			
			$db_props = CIBlockElement::GetProperty(40, $agent_id, array("sort" => "asc"), array ("ID" => 211) );
			if($ar_props = $db_props->Fetch())
			{
				$agent_type = $ar_props["VALUE"];
				if (!in_array($agent_type, array(51, 53)))
				{
					$arResult["OPEN"] = false;
					$arResult["ERRORS"][] = GetMessage("ERR_OPEN");
				}
				else
				{
					$arResult['AGENT'] = GetCompany($agent_id);
					if ($agent_type == 51)
					{
						$arResult['ADMIN_AGENT'] = true;
					}
					$arResult['USER_BRANCH'] = (intval($arUser["UF_BRANCH"]) > 0) ? intval($arUser["UF_BRANCH"]) : false;
					if ($arResult['USER_BRANCH'])
					{
						$arResult['BRANCH_INFO'] = GetBranch($arResult['USER_BRANCH'], $agent_id);
						$arResult['BRANCH_IN_1C'] = $arResult['BRANCH_INFO']['PROPERTY_IN_1C_VALUE'];
					}
				}
			}
			else
			{
				$arResult["OPEN"] = false;
				$arResult["ERRORS"][] = GetMessage("ERR_OPEN");
			}
		}
		else
		{
			$arResult["OPEN"] = false;
			$arResult["ERRORS"][] = GetMessage("ERR_OPEN");
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
		
		if (!$arResult['ADMIN_AGENT'])
		{
			$arResult['CURRENT_INN'] = $arResult['AGENT']['PROPERTY_INN_VALUE'];
			$arResult['CURRENT_AGENT'] = $agent_id;
		}
		else
		{
			if (strlen($_SESSION['CURRENT_INN']))
			{
				$arResult['CURRENT_INN'] = $_SESSION['CURRENT_INN'];
			}
			else
			{
				$arResult['CURRENT_INN'] = false;
			}
			if (strlen($_SESSION['CURRENT_AGENT']))
			{
				$arResult['CURRENT_AGENT'] = $_SESSION['CURRENT_AGENT'];
			}
			else
			{
				$arResult['CURRENT_AGENT'] = 0;
			}
		}
		$arResult['CURRENT_AGENT_COEFFICIENT_VW'] = WhatIsGabWeightCompany($arResult['CURRENT_AGENT']);
		
		/*
		if (!$arResult['ADMIN_AGENT'])
		{
			$arResult['CURRENT_AGENT'] = $agent_id;
			$arResult['CURRENT_CLIENT'] = 0;
		}
		else
		{
			if (strlen($_SESSION['CURRENT_AGENT']))
			{
				$arResult['CURRENT_AGENT'] = $_SESSION['CURRENT_AGENT'];
			}
			else
			{
				$arResult['CURRENT_AGENT'] = 0;
			}
			if (strlen($_SESSION['CURRENT_CLIENT']))
			{
				$arResult['CURRENT_CLIENT'] = $_SESSION['CURRENT_CLIENT'];
			}
			else
			{
				$arResult['CURRENT_CLIENT'] = 0;
			}
		}
		*/
		
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
			
			if (strlen($_SESSION['CURRENT_MONTH']))
			{
				$arResult['CURRENT_MONTH'] = $_SESSION['CURRENT_MONTH'];
			}
			if (strlen($_SESSION['CURRENT_YEAR']))
			{
				$arResult['CURRENT_YEAR'] = $_SESSION['CURRENT_YEAR'];
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
	
			$arResult['LIST_OF_AGENTS'] = false;
			if ($arResult['ADMIN_AGENT'])
			{
				$arResult['LIST_OF_AGENTS'] = AvailableAgents(false, $agent_id);
				if ($_GET['ChangeAgent'] == 'Y')
				{
					if (isset($arResult['LIST_OF_AGENTS'][$_GET['agent']]))
					{
						$_SESSION['CURRENT_AGENT'] = $_GET['agent'];
						$arResult['CURRENT_AGENT'] = $_GET['agent'];
						
						$db_props = CIBlockElement::GetProperty(40,$arResult['CURRENT_AGENT'], array("sort" => "asc"), Array("CODE"=>"INN"));
						if($ar_props = $db_props->Fetch())
						{
							$arResult['CURRENT_INN'] = $ar_props["VALUE"];
						}
						else
						{
							$arResult['CURRENT_INN'] = false;
						}
						$_SESSION['CURRENT_INN'] = $arResult['CURRENT_INN'];
					}
					elseif (intval($_GET['agent']) == 0)
					{
						unset($_SESSION['CURRENT_AGENT']);
						unset($_SESSION['CURRENT_INN']);
						$arResult['CURRENT_AGENT'] = false;
						$arResult['CURRENT_INN'] = false;
					}
				}
			}
			
			/*
			$arResult['LIST_OF_CLIENTS'] = AvailableClients();
			if ($_GET['ChangeClient'] == 'Y')
			{
				if (isset($arResult['LIST_OF_CLIENTS'][$_GET['client']]))
				{
					$_SESSION['CURRENT_CLIENT'] = $_GET['client'];
					$arResult['CURRENT_CLIENT'] = $_GET['client'];
				}
				else
				{
					$_SESSION['CURRENT_CLIENT'] = 0;
					$arResult['CURRENT_CLIENT'] = 0;
				}
			}
			
			$arResult['LIST_OF_BRANCHES'] = false;
			$res_3 = CIBlockElement::GetList(
				array("NAME" => "asc"), 
				array("IBLOCK_ID" => 89, "PROPERTY_CLIENT" =>  $arResult['CURRENT_CLIENT'], "ACTIVE" => "Y"), 
				false, 
				false, 
				array("ID","NAME","PROPERTY_CITY.NAME")
			);
			while ($ob_3 = $res_3->GetNextElement())
			{
				$arFields_3 = $ob_3->GetFields();
				$arResult['LIST_OF_BRANCHES'][$arFields_3["ID"]] = $arFields_3["NAME"].", ".$arFields_3["PROPERTY_CITY_NAME"];
			}
			
			if ($arResult['LIST_OF_BRANCHES'])
			{
				if ($_GET['ChangeBranch'] == 'Y')
				{
					if (isset($arResult['LIST_OF_BRANCHES'][$_GET['branch']]))
					{
						$_SESSION['CURRENT_BRANCH'] = $_GET['branch'];
						$arResult['CURRENT_BRANCH'] = $_GET['branch'];
					}
					else
					{
						$_SESSION['CURRENT_BRANCH'] = 0;
						$arResult['CURRENT_BRANCH'] = 0;
					}
				}
			}
			*/
		
			
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
			
			/*
			$search_nakls = false;
			if ($arResult['ADMIN_AGENT'])
			{
				if ((intval($arResult['CURRENT_AGENT']) > 0) || (intval($arResult['CURRENT_CLIENT'])))
				{
					$search_nakls = true;
				}
			}
			else
			{
				if (intval($arResult['CURRENT_AGENT']) > 0)
				{
					$search_nakls = true;
				}
			}
			*/
			
			$search_nakls = (intval($arResult['CURRENT_AGENT']) > 0) ? true : false;
			if ($search_nakls)
			{
                
				$nav_array = false;
				$filter = array("IBLOCK_ID" => 83, "ACTIVE" => "Y", "PROPERTY_AGENT" => $arResult["CURRENT_AGENT"]);
				$filter[">=DATE_CREATE"] = '01.'.$arResult['CURRENT_MONTH'].'.'.$arResult['CURRENT_YEAR'].' 00:00:00';
				$filter["<=DATE_CREATE"] = $last_day.'.'.$arResult['CURRENT_MONTH'].'.'.$arResult['CURRENT_YEAR'].' 23:59:59';
				/*
				if (intval($arResult['CURRENT_AGENT']) > 0)
				{
					$filter["PROPERTY_AGENT"] = $arResult["CURRENT_AGENT"];
				}
				if (strlen(trim($_GET['number'])))
				{
					$filter['NAME'] = '%'.trim($_GET['number']).'%';
				}
				if (intval($_GET['state']) > 0)
				{
					$filter["PROPERTY_STATE"] = intval($_GET['state']);
				}
				if (intval($arResult['CURRENT_CLIENT']) > 0)
				{
					$filter["PROPERTY_CREATOR"] = intval($arResult['CURRENT_CLIENT']);
				}
				if (intval($arResult['CURRENT_BRANCH']) > 0)
				{
					$filter["PROPERTY_BRANCH"] = intval($arResult['CURRENT_BRANCH']);
				}
				$sorts_by = array("created","name", "PROPERTY_STATE");
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
				*/
				$arResult['SORT_BY'] = "created";
				$arResult['SORT'] = "desc";
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
						"PROPERTY_AGENT",
						"PROPERTY_AGENT.NAME",
						"PROPERTY_BRANCH.NAME",
						"PROPERTY_CONTRACT.NAME"
					)
				);
				$arResult["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject,"Накладные","","Y");
				while ($ob = $res->GetNextElement())
				{
					$a = $ob->GetFields();
					if (is_array($a['PROPERTY_DIMENSIONS_VALUE']))
					{
						$w = 1;
						for ($i = 0; $i<3; $i++)
						{
							$w = $w*$a['PROPERTY_DIMENSIONS_VALUE'][$i];
						}
						$a["PROPERTY_OB_WEIGHT"] = $w/$arResult['CURRENT_AGENT_COEFFICIENT_VW'];
					}
					else
					{
						$a["PROPERTY_OB_WEIGHT"] = 0;
					}
					$arResult['REQUESTS'][] = $a;
				}
				$arResult['STATES'] = array();
				$db_enum_list = CIBlockProperty::GetPropertyEnum(572, Array(), Array("IBLOCK_ID"=>83));
				while($ar_enum_list = $db_enum_list->GetNext())
				{
					$arResult['STATES'][$ar_enum_list["ID"]] = $ar_enum_list["VALUE"];
				}
				
				/*накладные из 1с*/
				$arResult['ARCHIVE'] = array();
				$arParamsJson = array(
					'INN' => $arResult['CURRENT_INN'],
					'StartDate' => $arResult['CURRENT_YEAR'].'-'.$arResult['CURRENT_MONTH'].'-01',
					'EndDate' => $arResult['CURRENT_YEAR'].'-'.$arResult['CURRENT_MONTH'].'-'.$last_day,
					'NumPage' => 0,
					'DocsToPage' => 10000,
					'Type' => 1
				);
				$client = new SoapClient(
					'http://'.$currentip.'/sd_msk/ws/DashboardExchange.1cws?wsdl',  
					array('login' => 'DMSUser', 'password' => "1597534682",'exceptions' => false)
				);
				//echo '<pre>' ;
				//print_r($arParamsJson);
				//echo '</pre>';
				$result = $client->GetDocsListAgent($arParamsJson);
				$mResult = $result->return;
				$obj = json_decode($mResult, true);
				//echo count($obj['Docs']);
				foreach ($obj['Docs'] as $d)
				{
					$h = $d;
					$events = $h['Events'];
					
					unset($h['Events']);
					$m = array();
                    $m['Places'] = 0;
                    $m['ObW'] = 0;
                    $m['Weight'] = 0;
					foreach ($h as $k => $v)
					{
						
						if ($k != 'Dimensions')
						{
							$m[$k] = iconv('utf-8', 'windows-1251', $v);
						}
						else
						{
                            foreach ($v as $arGb)
                            {
                                $m['Weight'] = $m['Weight'] + floatval(str_replace(',','.',$arGb['Weight']));
                                $m['ObW'] = $m['ObW'] + floatval(str_replace(',','.',$arGb['WeightV']));
                                $m['Places'] = $m['Places'] + intval($arGb['Places']);
                            }
                            $m['Weight'] = WeightFormat($m['Weight'], false);
                            $m['ObW'] = WeightFormat($m['ObW'], false);
                            /*
							if (is_array($v['Dimension_1']))
							{
								foreach ($v['Dimension_1'] as $kk => $vv)
								{
									$m[$kk] = $vv;
								}
							}
                            */
						}
					}
					$m['CitySenderName'] = '';
					$m['CityRecipientName'] = '';
					if (intval($m['CitySender']) > 0)
					{
						$rr = CIBlockElement::GetByID(intval($m['CitySender']));
						if($ar_rr = $rr->GetNext())
						{
							$m['CitySenderName'] = $ar_rr['NAME'];
						}
					}
					if (intval($m['CityRecipient']) > 0)
					{
						$rr = CIBlockElement::GetByID(intval($m['CityRecipient']));
						if($ar_rr = $rr->GetNext())
						{
							$m['CityRecipientName'] = $ar_rr['NAME'];
						}
					}
					//$m['ObW'] = WeightFormat((($m['Size_1']*$m['Size_2']*$m['Size_3'])/$arResult['CURRENT_AGENT_COEFFICIENT_VW']),false);
					$m['events'] = array();	
					$m['state'] = 'Принято';
					if (count($events) > 0)
					{
						foreach ($events as $ev)
						{
							$ee = array();
							foreach ($ev as $kkk => $vvv)
							{
								$ee[$kkk] = iconv('utf-8', 'windows-1251', $vvv);
							}
							$m['state'] = $ee['Event'];
							$m['stateDescr'] = $ee['InfoEvent'];
							$m['events'][] = $ee;
						}
					}
					$arResult['ARCHIVE'][] = $m;
				}
				/*
				if ($arResult['ADMIN_AGENT'])
				{
					echo '<pre>';
					print_r($arResult['ARCHIVE']);
					echo '</pre>';	
				}
				*/
				/*накладные из 1с*/
				
				foreach ($arResult['ARCHIVE']  as $k => $v)
				{
					$arResult['ARCHIVE'][$k]['stateEdit'] = $v['state'];
					$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-file" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр заявки"></span>';
					$arResult['ARCHIVE'][$k]['ColorRow'] = 'warning';
					
					$arResult['ARCHIVE'][$k]['start_date'] = strlen($v['Date_Create']) ? substr($v['Date_Create'],8,2).'.'.substr($v['Date_Create'],5,2).'.'.substr($v['Date_Create'],0,4) : $v['DateDoc'];
					$arResult['ARCHIVE'][$k]['DateOfCompletion'] = substr($v['DateOfCompletion'],8,2).'.'.substr($v['DateOfCompletion'],5,2).'.'.substr($v['DateOfCompletion'],0,4);
					
					switch ($v['state'])
					{
						case 'Отправлено в город' :
							$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-send" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр заявки"></span>';
							$arResult['ARCHIVE'][$k]['stateEdit'] = $v['stateDescr'];
						break;
						case 'Выдано курьеру на маршрут' :
							$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-road" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр заявки"></span>';
							$arResult['ARCHIVE'][$k]['stateEdit'] = 'Выдано на маршрут';
						break;
						case 'Исключительная ситуация!' :
							$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр заявки"></span>';
							$arResult['ARCHIVE'][$k]['stateEdit'] = $v['stateDescr'];
							$arResult['ARCHIVE'][$k]['ColorRow'] = 'danger';
						break;
						case 'Отмена заявки' :
							$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр заявки"></span>';
							$arResult['ARCHIVE'][$k]['stateEdit'] = $v['state'].'&nbsp;'.$v['stateDescr'];
							$arResult['ARCHIVE'][$k]['ColorRow'] = 'danger';
						break;
						case 'Принято' :
							$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-log-in" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр заявки"></span>';
							$arResult['ARCHIVE'][$k]['stateEdit'] = $v['state'];
							$arResult['ARCHIVE'][$k]['ColorRow'] = 'success';
						break;
						case 'Оприходовано офисом' :
							$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-download-alt" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр заявки"></span>';
							$arResult['ARCHIVE'][$k]['stateEdit'] = $v['stateDescr'];
						break;
						case 'Доставлено' :
							$arResult['ARCHIVE'][$k]['state_icon'] = '<span class="glyphicon glyphicon-check" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр заявки"></span>';
							$arResult['ARCHIVE'][$k]['stateEdit'] = $v['state'];
							$arResult['ARCHIVE'][$k]['ColorRow'] = 'supersuccess';
						break;
						default:
							$arResult['ARCHIVE'][$k]['stateEdit'] = $v['state'];
						break;
					}
				}
			}
			$arResult['TITLE'] = GetMessage('TITLE_MODE_LIST');
			$APPLICATION->SetTitle(GetMessage('TITLE_MODE_LIST'));
		}
		
		if ($mode == 'rates')
		{
			if (isset($_POST['save']))
			{
				if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
				{
					$_POST = array();
					$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
				}
				else
				{
					$_SESSION[$_POST["key_session"]] = $_POST["rand"];
					foreach ($_POST['rate'] as $k => $v)
					{
						if (strlen(trim($v)))
						{
							CIBlockElement::SetPropertyValuesEx($k, 83, array(642 => floatval(str_replace(',','.',trim($v)))));
						}
					}
					$arResult["MESSAGE"][] = "Тарифы обновлены";
				}
			}
			$arResult['YEARS'] = array(2014 => 2014, 2015 => 2015);
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
		
			if (strlen($_SESSION['CURRENT_MONTH']))
			{
				$arResult['CURRENT_MONTH'] = $_SESSION['CURRENT_MONTH'];
			}
			if (strlen($_SESSION['CURRENT_YEAR']))
			{
				$arResult['CURRENT_YEAR'] = $_SESSION['CURRENT_YEAR'];
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
			
			$arResult['LIST_OF_AGENTS'] = false;
			if ($arResult['ADMIN_AGENT'])
			{
				$arResult['LIST_OF_AGENTS'] = AvailableAgents(false, $agent_id, true);
				if ($_GET['ChangeAgent'] == 'Y')
				{
					if (isset($arResult['LIST_OF_AGENTS'][$_GET['agent']]))
					{
						$_SESSION['CURRENT_AGENT'] = $_GET['agent'];
						$arResult['CURRENT_AGENT'] = $_GET['agent'];
					}
					else
					{
						$_SESSION['CURRENT_AGENT'] = 0;
						$arResult['CURRENT_AGENT'] = 0;
					}
				}
			}
			
			$arResult['LIST_OF_CLIENTS'] = AvailableClients();
			if ($_GET['ChangeClient'] == 'Y')
			{
				if (isset($arResult['LIST_OF_CLIENTS'][$_GET['client']]))
				{
					$_SESSION['CURRENT_CLIENT'] = $_GET['client'];
					$arResult['CURRENT_CLIENT'] = $_GET['client'];
				}
				else
				{
					$_SESSION['CURRENT_CLIENT'] = 0;
					$arResult['CURRENT_CLIENT'] = 0;
				}
			}
			
			$arResult['LIST_OF_BRANCHES'] = false;
			$res_3 = CIBlockElement::GetList(
				array("NAME" => "asc"), 
				array("IBLOCK_ID" => 89, "PROPERTY_CLIENT" =>  $arResult['CURRENT_CLIENT'], "ACTIVE" => "Y"), 
				false, 
				false, 
				array("ID","NAME","PROPERTY_CITY.NAME")
			);
			while ($ob_3 = $res_3->GetNextElement())
			{
				$arFields_3 = $ob_3->GetFields();
				$arResult['LIST_OF_BRANCHES'][$arFields_3["ID"]] = $arFields_3["NAME"].", ".$arFields_3["PROPERTY_CITY_NAME"];
			}
			
			if ($arResult['LIST_OF_BRANCHES'])
			{
				if ($_GET['ChangeBranch'] == 'Y')
				{
					if (isset($arResult['LIST_OF_BRANCHES'][$_GET['branch']]))
					{
						$_SESSION['CURRENT_BRANCH'] = $_GET['branch'];
						$arResult['CURRENT_BRANCH'] = $_GET['branch'];
					}
					else
					{
						$_SESSION['CURRENT_BRANCH'] = 0;
						$arResult['CURRENT_BRANCH'] = 0;
					}
				}
			}
			
			$arResult['REQUESTS'] = array();
			
				$search_nakls = false;
			if ($arResult['ADMIN_AGENT'])
			{
				if ((intval($arResult['CURRENT_AGENT']) > 0) || (intval($arResult['CURRENT_CLIENT'])))
				{
					$search_nakls = true;
				}
			}
			else
			{
				if (intval($arResult['CURRENT_AGENT']) > 0)
				{
					$search_nakls = true;
				}
			}
			
			if ($search_nakls)
			{
				$nav_array = false;
				$filter = array("IBLOCK_ID" => 83, "ACTIVE" => "Y");
				if (intval($arResult['CURRENT_AGENT']) > 0)
				{
					$filter["PROPERTY_AGENT"] = $arResult["CURRENT_AGENT"];
				}
				$filter[">=DATE_CREATE"] = '01-'.$arResult['CURRENT_MONTH'].'-'.$arResult['CURRENT_YEAR'].' 00:00:00';
				$filter["<=DATE_CREATE"] = $last_day.'-'.$arResult['CURRENT_MONTH'].'-'.$arResult['CURRENT_YEAR'].' 23:59:59';
						if (intval($arResult['CURRENT_CLIENT']) > 0)
				{
					$filter["PROPERTY_CREATOR"] = intval($arResult['CURRENT_CLIENT']);
				}
				if (intval($arResult['CURRENT_BRANCH']) > 0)
				{
					$filter["PROPERTY_BRANCH"] = intval($arResult['CURRENT_BRANCH']);
				}
				
				$sorts_by = array("created","name");
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
						"PROPERTY_CITY_SENDER",
						"PROPERTY_CITY_SENDER.NAME",
						"PROPERTY_COMPANY_RECIPIENT",
						"PROPERTY_CITY_RECIPIENT",
						"PROPERTY_CITY_RECIPIENT.name",
						"PROPERTY_PLACES",
						"PROPERTY_WEIGHT",
						"PROPERTY_DIMENSIONS",
						"PROPERTY_STATE",
						"PROPERTY_AGENT",
						"PROPERTY_AGENT.NAME",
						"PROPERTY_BRANCH.NAME",
						"PROPERTY_RATE",
						"PROPERTY_TRANSIT_MOSCOW"
					)
				);
				// $arResult["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject,"Накладные","","Y");
				while ($ob = $res->GetNextElement())
				{
					$a = $ob->GetFields();
					if (is_array($a['PROPERTY_DIMENSIONS_VALUE']))
					{
						$w = 1;
						for ($i = 0; $i<3; $i++)
						{
							$w = $w*$a['PROPERTY_DIMENSIONS_VALUE'][$i];
						}
						$a["PROPERTY_OB_WEIGHT"] = $w/6000;
					}
					else
					{
						$a["PROPERTY_OB_WEIGHT"] = 0;
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
			
			$arFromTo = array();
			$arCitiesTo = array(
				8054, //Москва
				7986, //Воскресенск
				8678, //Санкт-Петербург
				8683, //Тосно
				7145, //Брянск
				8208, //Нижний Новгород
				7278, //Вологда
				7619, //Киров
				7569, //Петрозаводск
				8509, //Псков
				7054, //Астрахань
				7732, //Краснодар
				8592, //Рыбное
				8720, //Саратов
				8618, //Кинель
				9306, //Магнитогорск
				8400, //Пенза
				8819, //Екатеринбург
				8632, //Самара
				8271, //Новосибирск
				8301, //Омск
				7206, //Улан-Удэ
				7137, //Старый Оскол
				8521, //Батайск
				8634, //Сызрань
				8448, //Чусовой
				7439, //Нижнеудинск
				8483, //Уссурийск
				9265, //Хабаровск
				7322, //Поворино
				9130, //Ишим
				7794, //Иланский
				8469, //Лесозаводск
				7600, //Тайга
				7443, //Тайшет
				9376, //Чернышевск
				8144, //Кандалакша 
				7029, //Вычегодский
				7592, //Ленинск-Кузнецкий	
				7253, //Котово
				7589, //Кемерово
				7383, //Иваново
				9435, //Ярославль
				7430, //Иркутск
				9155, //Сургут
				8564, //Ростов-на-Дону
				8567, //Таганрог
				9769583, //Никольское с.
				8661, //Кириши
				7708, //Анапа
				7470, //Калининград
				8635, //Тольятти
				8147, //Ковдор
				8842, //Нижний Тагил
				8626, //Новокуйбышевск
				8337, //Оренбург
				7116, //Уфа
				8437, //Пермь
				7424 //Железногорск-Илимский
			);
			$main_city = 8054;
			$weights = array(
				0.5, 1, 1.5, 2, 2.5, 3, 3.5,
				4,
				4.5,
				5,
				5.5,
				6,
				6.5,
				7,
				7.5,
				8,
				8.5,
				9,
				9.5,
				10,
				10.5,
				11,
				11.5,
				12,
				12.5,
				13,
				13.5 ,
				14,
				14.5,
				15,
				15.5,
				16,
				16.5,
				17,
				17.5,
				18,
				18.5,
				19,
				19.5,
				20
			);
			
			$tarifs = array(
				8054 => array(
					355.34, 355.34, 377.20, 399.07, 420.94, 442.80, 464.67, 486.54, 508.40, 530.27, 768.62, 805.79, 842.96, 880.13, 917.31, 954.48, 991.65, 1028.83, 1066.00, 1103.17, 1140.35, 1177.52, 
					1214.69, 1251.87, 1289.04, 1326.21, 1363.39, 1400.56, 1437.73, 1474.91, 1512.08, 1549.25, 1586.43, 1623.60, 1660.77, 1697.95, 1735.12, 1772.29, 1809.46, 1846.64
				),
				7986 => array(
					506.22, 530.27, 554.32, 578.38, 602.43, 626.48, 650.54, 674.59, 698.64, 722.70, 746.75, 770.80, 794.86, 818.91, 842.96, 867.02, 891.07, 915.12, 939.16, 963.22, 987.27, 1011.32, 1035.39, 
					1059.44, 1083.49, 1107.55, 1131.60, 1155.65, 1179.71, 1203.76, 1227.81, 1251.87, 1275.92, 1299.97, 1324.03, 1348.08, 1372.13, 1396.19, 1420.24, 1444.29
				),
				8678 => array(
					506.22, 530.27, 554.32, 578.38, 602.43, 626.48, 650.54, 674.59, 698.64, 722.70, 746.75, 770.80, 794.86, 818.91, 842.96, 867.02, 891.07, 915.12, 939.16, 963.22, 987.27, 1011.32, 1035.39, 
					1059.44, 1083.49, 1107.55, 1131.60, 1155.65, 1179.71, 1203.76, 1227.81, 1251.87, 1275.92, 1299.97, 1324.03, 1348.08, 1372.13, 1396.19, 1420.24, 1444.29
				),
				8683 => array(
					506.22, 530.27, 554.32, 578.38, 602.43, 626.48, 650.54, 674.59, 698.64, 722.70, 746.75, 770.80, 794.86, 818.91, 842.96, 867.02, 891.07, 915.12, 939.16, 963.22, 987.27, 1011.32, 1035.39, 
					1059.44, 1083.49, 1107.55, 1131.60, 1155.65, 1179.71, 1203.76, 1227.81, 1251.87, 1275.92, 1299.97, 1324.03, 1348.08, 1372.13, 1396.19, 1420.24, 1444.29
				),
				7145 => array(
					563.07, 594.77, 626.48, 658.18, 689.90, 721.60, 753.31, 785.01, 816.72, 848.42, 880.13, 911.84, 943.55, 975.25, 1006.96, 1038.66, 1070.37, 1102.08, 1133.79, 1165.49, 1197.20, 1228.90, 
					1260.61, 1292.31, 1324.03, 1355.73, 1387.44, 1419.14, 1450.85, 1482.55, 1514.27, 1545.97, 1577.68, 1609.38, 1641.09, 1672.79, 1704.51, 1736.21, 1767.92, 1799.62
				),
				8208 => array(
					563.07, 594.77, 626.48, 658.18, 689.90, 721.60, 753.31, 785.01, 816.72, 848.42, 880.13, 911.84, 943.55, 975.25, 1006.96, 1038.66, 1070.37, 1102.08, 1133.79, 1165.49, 1197.20, 1228.90, 
					1260.61, 1292.31, 1324.03, 1355.73, 1387.44, 1419.14, 1450.85, 1482.55, 1514.27, 1545.97, 1577.68, 1609.38, 1641.09, 1672.79, 1704.51, 1736.21, 1767.92, 1799.62
				),
				7278 => array(
					675.68, 725.97, 776.26, 826.56, 876.85, 927.14, 977.44, 1027.73, 1078.02, 1128.32, 1178.61, 1228.90, 1279.19, 1329.49, 1379.78, 1430.07, 1480.37, 1530.66, 1580.95, 1631.25, 1681.54, 
					1731.83, 1782.13, 1832.42, 1882.71, 1933.01, 1983.30, 2033.59, 2083.89, 2134.18, 2184.47, 2234.76, 2285.06, 2335.35, 2385.64, 2435.94, 2486.23, 2536.52, 2586.82, 2637.11
				),
				7619 => array(
					675.68, 725.97, 776.26, 826.56, 876.85, 927.14, 977.44, 1027.73, 1078.02, 1128.32, 1178.61, 1228.90, 1279.19, 1329.49, 1379.78, 1430.07, 1480.37, 1530.66, 1580.95, 1631.25, 1681.54, 
					1731.83, 1782.13, 1832.42, 1882.71, 1933.01, 1983.30, 2033.59, 2083.89, 2134.18, 2184.47, 2234.76, 2285.06, 2335.35, 2385.64, 2435.94, 2486.23, 2536.52, 2586.82, 2637.11
				),
				7569 => array(
					675.68, 725.97, 776.26, 826.56, 876.85, 927.14, 977.44, 1027.73, 1078.02, 1128.32, 1178.61, 1228.90, 1279.19, 1329.49, 1379.78, 1430.07, 1480.37, 1530.66, 1580.95, 1631.25, 1681.54, 
					1731.83, 1782.13, 1832.42, 1882.71, 1933.01, 1983.30, 2033.59, 2083.89, 2134.18, 2184.47, 2234.76, 2285.06, 2335.35, 2385.64, 2435.94, 2486.23, 2536.52, 2586.82, 2637.11
				),
				8509 => array(
					675.68, 725.97, 776.26, 826.56, 876.85, 927.14, 977.44, 1027.73, 1078.02, 1128.32, 1178.61, 1228.90, 1279.19, 1329.49, 1379.78, 1430.07, 1480.37, 1530.66, 1580.95, 1631.25, 1681.54, 
					1731.83, 1782.13, 1832.42, 1882.71, 1933.01, 1983.30, 2033.59, 2083.89, 2134.18, 2184.47, 2234.76, 2285.06, 2335.35, 2385.64, 2435.94, 2486.23, 2536.52, 2586.82, 2637.11
				),
				7054 => array(
					675.68, 725.97, 776.26, 826.56, 876.85, 927.14, 977.44, 1027.73, 1078.02, 1128.32, 1178.61, 1228.90, 1279.19, 1329.49, 1379.78, 1430.07, 1480.37, 1530.66, 1580.95, 1631.25, 1681.54, 
					1731.83, 1782.13, 1832.42, 1882.71, 1933.01, 1983.30, 2033.59, 2083.89, 2134.18, 2184.47, 2234.76, 2285.06, 2335.35, 2385.64, 2435.94, 2486.23, 2536.52, 2586.82, 2637.11
				),
				7732 => array(
					675.68, 725.97, 776.26, 826.56, 876.85, 927.14, 977.44, 1027.73, 1078.02, 1128.32, 1178.61, 1228.90, 1279.19, 1329.49, 1379.78, 1430.07, 1480.37, 1530.66, 1580.95, 1631.25, 1681.54, 
					1731.83, 1782.13, 1832.42, 1882.71, 1933.01, 1983.30, 2033.59, 2083.89, 2134.18, 2184.47, 2234.76, 2285.06, 2335.35, 2385.64, 2435.94, 2486.23, 2536.52, 2586.82, 2637.11
				),
				8592 => array(
					675.68, 725.97, 776.26, 826.56, 876.85, 927.14, 977.44, 1027.73, 1078.02, 1128.32, 1178.61, 1228.90, 1279.19, 1329.49, 1379.78, 1430.07, 1480.37, 1530.66, 1580.95, 1631.25, 1681.54, 
					1731.83, 1782.13, 1832.42, 1882.71, 1933.01, 1983.30, 2033.59, 2083.89, 2134.18, 2184.47, 2234.76, 2285.06, 2335.35, 2385.64, 2435.94, 2486.23, 2536.52, 2586.82, 2637.11
				),
				8720 => array(
					675.68, 725.97, 776.26, 826.56, 876.85, 927.14, 977.44, 1027.73, 1078.02, 1128.32, 1178.61, 1228.90, 1279.19, 1329.49, 1379.78, 1430.07, 1480.37, 1530.66, 1580.95, 1631.25, 1681.54, 
					1731.83, 1782.13, 1832.42, 1882.71, 1933.01, 1983.30, 2033.59, 2083.89, 2134.18, 2184.47, 2234.76, 2285.06, 2335.35, 2385.64, 2435.94, 2486.23, 2536.52, 2586.82, 2637.11
				),
				8618 => array(
					675.68, 725.97, 776.26, 826.56, 876.85, 927.14, 977.44, 1027.73, 1078.02, 1128.32, 1178.61, 1228.90, 1279.19, 1329.49, 1379.78, 1430.07, 1480.37, 1530.66, 1580.95, 1631.25, 1681.54, 
					1731.83, 1782.13, 1832.42, 1882.71, 1933.01, 1983.30, 2033.59, 2083.89, 2134.18, 2184.47, 2234.76, 2285.06, 2335.35, 2385.64, 2435.94, 2486.23, 2536.52, 2586.82, 2637.11
				),
				9306 => array(
					675.68, 725.97, 776.26, 826.56, 876.85, 927.14, 977.44, 1027.73, 1078.02, 1128.32, 1178.61, 1228.90, 1279.19, 1329.49, 1379.78, 1430.07, 1480.37, 1530.66, 1580.95, 1631.25, 1681.54, 
					1731.83, 1782.13, 1832.42, 1882.71, 1933.01, 1983.30, 2033.59, 2083.89, 2134.18, 2184.47, 2234.76, 2285.06, 2335.35, 2385.64, 2435.94, 2486.23, 2536.52, 2586.82, 2637.11
				),
				8400 => array(
					675.68, 725.97, 776.26, 826.56, 876.85, 927.14, 977.44, 1027.73, 1078.02, 1128.32, 1178.61, 1228.90, 1279.19, 1329.49, 1379.78, 1430.07, 1480.37, 1530.66, 1580.95, 1631.25, 1681.54, 
					1731.83, 1782.13, 1832.42, 1882.71, 1933.01, 1983.30, 2033.59, 2083.89, 2134.18, 2184.47, 2234.76, 2285.06, 2335.35, 2385.64, 2435.94, 2486.23, 2536.52, 2586.82, 2637.11
				),
				8819 => array(
					675.68, 725.97, 776.26, 826.56, 876.85, 927.14, 977.44, 1027.73, 1078.02, 1128.32, 1178.61, 1228.90, 1279.19, 1329.49, 1379.78, 1430.07, 1480.37, 1530.66, 1580.95, 1631.25, 1681.54, 
					1731.83, 1782.13, 1832.42, 1882.71, 1933.01, 1983.30, 2033.59, 2083.89, 2134.18, 2184.47, 2234.76, 2285.06, 2335.35, 2385.64, 2435.94, 2486.23, 2536.52, 2586.82, 2637.11
				),
				8632 => array(
					675.68, 725.97, 776.26, 826.56, 876.85, 927.14, 977.44, 1027.73, 1078.02, 1128.32, 1178.61, 1228.90, 1279.19, 1329.49, 1379.78, 1430.07, 1480.37, 1530.66, 1580.95, 1631.25, 1681.54, 
					1731.83, 1782.13, 1832.42, 1882.71, 1933.01, 1983.30, 2033.59, 2083.89, 2134.18, 2184.47, 2234.76, 2285.06, 2335.35, 2385.64, 2435.94, 2486.23, 2536.52, 2586.82, 2637.11
				),
				8271 => array(
					750.02, 818.91, 887.78, 956.67, 1025.54, 1094.43, 1163.30, 1232.19, 1301.06, 1369.95, 1438.82, 1507.71, 1576.58, 1645.47, 1714.34, 1783.22, 1852.10, 1920.98, 1989.86, 2058.74, 2127.62, 
					2196.50, 2265.38, 2334.26, 2403.14, 2472.02, 2540.90, 2609.78, 2678.66, 2747.54, 2816.42, 2885.30, 2954.17, 3023.06, 3091.93, 3160.82, 3229.69, 3298.58, 3367.45, 3436.34
				),
				8301 => array(750.02, 818.91, 887.78, 956.67, 1025.54, 1094.43, 1163.30, 1232.19, 1301.06, 1369.95, 1438.82, 1507.71, 1576.58, 1645.47, 1714.34, 1783.22, 1852.10, 1920.98, 1989.86, 2058.74, 2127.62, 2196.50, 2265.38, 2334.26, 2403.14, 2472.02, 2540.90, 2609.78, 2678.66, 2747.54, 2816.42, 2885.30, 2954.17, 3023.06, 3091.93, 3160.82, 3229.69, 3298.58, 3367.45, 3436.34),
				7206 => array(750.02, 818.91, 887.78, 956.67, 1025.54, 1094.43, 1163.30, 1232.19, 1301.06, 1369.95, 1438.82, 1507.71, 1576.58, 1645.47, 1714.34, 1783.22, 1852.10, 1920.98, 1989.86, 2058.74, 2127.62, 2196.50, 2265.38, 2334.26, 2403.14, 2472.02, 2540.90, 2609.78, 2678.66, 2747.54, 2816.42, 2885.30, 2954.17, 3023.06, 3091.93, 3160.82, 3229.69, 3298.58, 3367.45, 3436.34),
				7137 => array(750.02, 818.91, 887.78, 956.67, 1025.54, 1094.43, 1163.30, 1232.19, 1301.06, 1369.95, 1438.82, 1507.71, 1576.58, 1645.47, 1714.34, 1783.22, 1852.10, 1920.98, 1989.86, 2058.74, 2127.62, 2196.50, 2265.38, 2334.26, 2403.14, 2472.02, 2540.90, 2609.78, 2678.66, 2747.54, 2816.42, 2885.30, 2954.17, 3023.06, 3091.93, 3160.82, 3229.69, 3298.58, 3367.45, 3436.34),
				8521 => array(750.02, 818.91, 887.78, 956.67, 1025.54, 1094.43, 1163.30, 1232.19, 1301.06, 1369.95, 1438.82, 1507.71, 1576.58, 1645.47, 1714.34, 1783.22, 1852.10, 1920.98, 1989.86, 2058.74, 2127.62, 2196.50, 2265.38, 2334.26, 2403.14, 2472.02, 2540.90, 2609.78, 2678.66, 2747.54, 2816.42, 2885.30, 2954.17, 3023.06, 3091.93, 3160.82, 3229.69, 3298.58, 3367.45, 3436.34),
				8634 => array(750.02, 818.91, 887.78, 956.67, 1025.54, 1094.43, 1163.30, 1232.19, 1301.06, 1369.95, 1438.82, 1507.71, 1576.58, 1645.47, 1714.34, 1783.22, 1852.10, 1920.98, 1989.86, 2058.74, 2127.62, 2196.50, 2265.38, 2334.26, 2403.14, 2472.02, 2540.90, 2609.78, 2678.66, 2747.54, 2816.42, 2885.30, 2954.17, 3023.06, 3091.93, 3160.82, 3229.69, 3298.58, 3367.45, 3436.34),
				8448 => array(750.02, 818.91, 887.78, 956.67, 1025.54, 1094.43, 1163.30, 1232.19, 1301.06, 1369.95, 1438.82, 1507.71, 1576.58, 1645.47, 1714.34, 1783.22, 1852.10, 1920.98, 1989.86, 2058.74, 2127.62, 2196.50, 2265.38, 2334.26, 2403.14, 2472.02, 2540.90, 2609.78, 2678.66, 2747.54, 2816.42, 2885.30, 2954.17, 3023.06, 3091.93, 3160.82, 3229.69, 3298.58, 3367.45, 3436.34),
				7439 => array(857.17, 932.61, 1008.05, 1083.49, 1158.93, 1234.37, 1309.81, 1385.25, 1460.69, 1536.13, 1611.57, 1687.01, 1762.45, 1837.89, 1913.33, 1988.77, 2064.21, 2139.65, 2215.08, 2290.53, 2365.96, 2441.41, 2516.84, 2592.29, 2667.72, 2743.17, 2818.60, 2894.05, 2969.48, 3044.93, 3120.36, 3195.81, 3271.24, 3346.69, 3422.12, 3497.56, 3573.00, 3648.44, 3723.88, 3799.32),
				8483 => array(857.17, 932.61, 1008.05, 1083.49, 1158.93, 1234.37, 1309.81, 1385.25, 1460.69, 1536.13, 1611.57, 1687.01, 1762.45, 1837.89, 1913.33, 1988.77, 2064.21, 2139.65, 2215.08, 2290.53, 2365.96, 2441.41, 2516.84, 2592.29, 2667.72, 2743.17, 2818.60, 2894.05, 2969.48, 3044.93, 3120.36, 3195.81, 3271.24, 3346.69, 3422.12, 3497.56, 3573.00, 3648.44, 3723.88, 3799.32),
				9265 => array(857.17, 932.61, 1008.05, 1083.49, 1158.93, 1234.37, 1309.81, 1385.25, 1460.69, 1536.13, 1611.57, 1687.01, 1762.45, 1837.89, 1913.33, 1988.77, 2064.21, 2139.65, 2215.08, 2290.53, 2365.96, 2441.41, 2516.84, 2592.29, 2667.72, 2743.17, 2818.60, 2894.05, 2969.48, 3044.93, 3120.36, 3195.81, 3271.24, 3346.69, 3422.12, 3497.56, 3573.00, 3648.44, 3723.88, 3799.32),
				7322 => array(975.25, 1053.97, 1132.69, 1211.41, 1290.13, 1368.85, 1447.57, 1526.29, 1605.01, 1683.73, 1762.45, 1841.17, 1919.89, 1998.61, 2077.33, 2156.04, 2234.76, 2313.48, 2392.20, 2470.92, 2549.64, 2628.36, 2707.08, 2785.80, 2864.52, 2943.24, 3021.96, 3100.68, 3179.40, 3258.12, 3336.84, 3415.56, 3494.28, 3573.00, 3651.72, 3730.44, 3809.16, 3887.88, 3966.60, 4045.32),
				9130 => array(975.25, 1053.97, 1132.69, 1211.41, 1290.13, 1368.85, 1447.57, 1526.29, 1605.01, 1683.73, 1762.45, 1841.17, 1919.89, 1998.61, 2077.33, 2156.04, 2234.76, 2313.48, 2392.20, 2470.92, 2549.64, 2628.36, 2707.08, 2785.80, 2864.52, 2943.24, 3021.96, 3100.68, 3179.40, 3258.12, 3336.84, 3415.56, 3494.28, 3573.00, 3651.72, 3730.44, 3809.16, 3887.88, 3966.60, 4045.32),
				7794 => array(1326.21, 1423.51, 1520.83, 1618.13, 1715.44, 1812.74, 1910.05, 2007.35, 2104.66, 2201.96, 2299.28, 2396.58, 2493.89, 2591.19, 2688.50, 2785.80, 2883.11, 2980.41, 3077.73, 3175.03, 3272.34, 3369.64, 3466.95, 3564.25, 3661.56, 3758.87, 3856.18, 3953.48, 4050.79, 4148.09, 4245.40, 4342.70, 4440.01, 4537.32, 4634.63, 4731.93, 4829.24, 4926.54, 5023.85, 5121.15),
				8469 => array(1326.21, 1423.51, 1520.83, 1618.13, 1715.44, 1812.74, 1910.05, 2007.35, 2104.66, 2201.96, 2299.28, 2396.58, 2493.89, 2591.19, 2688.50, 2785.80, 2883.11, 2980.41, 3077.73, 3175.03, 3272.34, 3369.64, 3466.95, 3564.25, 3661.56, 3758.87, 3856.18, 3953.48, 4050.79, 4148.09, 4245.40, 4342.70, 4440.01, 4537.32, 4634.63, 4731.93, 4829.24, 4926.54, 5023.85, 5121.15),
				7600 => array(1326.21, 1423.51, 1520.83, 1618.13, 1715.44, 1812.74, 1910.05, 2007.35, 2104.66, 2201.96, 2299.28, 2396.58, 2493.89, 2591.19, 2688.50, 2785.80, 2883.11, 2980.41, 3077.73, 3175.03, 3272.34, 3369.64, 3466.95, 3564.25, 3661.56, 3758.87, 3856.18, 3953.48, 4050.79, 4148.09, 4245.40, 4342.70, 4440.01, 4537.32, 4634.63, 4731.93, 4829.24, 4926.54, 5023.85, 5121.15),
				7443 => array(1326.21, 1423.51, 1520.83, 1618.13, 1715.44, 1812.74, 1910.05, 2007.35, 2104.66, 2201.96, 2299.28, 2396.58, 2493.89, 2591.19, 2688.50, 2785.80, 2883.11, 2980.41, 3077.73, 3175.03, 3272.34, 3369.64, 3466.95, 3564.25, 3661.56, 3758.87, 3856.18, 3953.48, 4050.79, 4148.09, 4245.40, 4342.70, 4440.01, 4537.32, 4634.63, 4731.93, 4829.24, 4926.54, 5023.85, 5121.15),
				9376 => array(1326.21, 1423.51, 1520.83, 1618.13, 1715.44, 1812.74, 1910.05, 2007.35, 2104.66, 2201.96, 2299.28, 2396.58, 2493.89, 2591.19, 2688.50, 2785.80, 2883.11, 2980.41, 3077.73, 3175.03, 3272.34, 3369.64, 3466.95, 3564.25, 3661.56, 3758.87, 3856.18, 3953.48, 4050.79, 4148.09, 4245.40, 4342.70, 4440.01, 4537.32, 4634.63, 4731.93, 4829.24, 4926.54, 5023.85, 5121.15),
				8144 => array(1951.60, 2069.68, 2187.76, 2305.84, 2423.92, 2542.00, 2660.07, 2778.15, 2896.23, 3014.31, 3132.39, 3250.47, 3368.55, 3486.63, 3604.71, 3722.79, 3840.87, 3958.95, 4077.03, 4195.11, 4313.19, 4431.27, 4549.35, 4667.43, 4785.51, 4903.59, 5021.67, 5139.74, 5257.82, 5375.90, 5493.98, 5612.06, 5730.14, 5848.22, 5966.30, 6084.38, 6202.46, 6320.54, 6438.62, 6556.70),
				7029 => array(1951.60, 2069.68, 2187.76, 2305.84, 2423.92, 2542.00, 2660.07, 2778.15, 2896.23, 3014.31, 3132.39, 3250.47, 3368.55, 3486.63, 3604.71, 3722.79, 3840.87, 3958.95, 4077.03, 4195.11, 4313.19, 4431.27, 4549.35, 4667.43, 4785.51, 4903.59, 5021.67, 5139.74, 5257.82, 5375.90, 5493.98, 5612.06, 5730.14, 5848.22, 5966.30, 6084.38, 6202.46, 6320.54, 6438.62, 6556.70),
				7592 => array(1951.60, 2069.68, 2187.76, 2305.84, 2423.92, 2542.00, 2660.07, 2778.15, 2896.23, 3014.31, 3132.39, 3250.47, 3368.55, 3486.63, 3604.71, 3722.79, 3840.87, 3958.95, 4077.03, 4195.11, 4313.19, 4431.27, 4549.35, 4667.43, 4785.51, 4903.59, 5021.67, 5139.74, 5257.82, 5375.90, 5493.98, 5612.06, 5730.14, 5848.22, 5966.30, 6084.38, 6202.46, 6320.54, 6438.62, 6556.70),
				7253 => array(750.02, 818.91, 887.78, 956.67, 1025.54, 1094.43, 1163.30, 1232.19, 1301.06, 1369.94, 1438.82, 1507.71, 1576.59, 1645.46, 1714.34, 1783.23, 1852.10, 1920.98, 1989.86, 2058.75, 2127.62, 2196.50, 2265.38, 2334.26, 2403.14, 2472.02, 2540.89, 2609.78, 2678.66, 2747.54, 2816.41, 2885.30, 2954.18, 3023.07, 3091.93),
				7589 => array(750.02, 818.91, 887.78, 956.67, 1025.54, 1094.43, 1163.30, 1232.19, 1301.06, 1369.94, 1438.82, 1507.71, 1576.59, 1645.46, 1714.34, 1783.23, 1852.10, 1920.98, 1989.86, 2058.75, 2127.62, 2196.50, 2265.38, 2334.26, 2403.14, 2472.02, 2540.89, 2609.78, 2678.66, 2747.54, 2816.41, 2885.30, 2954.18, 3023.07, 3091.93),
				7383 => array(563.07, 594.77, 626.49, 658.18, 689.90, 721.59, 753.31, 785.01, 816.73, 848.42, 880.14, 911.83, 943.55, 975.25, 1006.96, 1038.66, 1070.38, 1102.07, 1133.79, 1165.49, 1197.20, 1228.90, 1260.62, 1292.31, 1324.03, 1355.73, 1387.44, 1419.14, 1450.86, 1482.55, 1514.27, 1545.97, 1577.68, 1609.38, 1641.10),
				9435 => array(563.07, 594.77, 626.49, 658.18, 689.90, 721.59, 753.31, 785.01, 816.73, 848.42, 880.14, 911.83, 943.55, 975.25, 1006.96, 1038.66, 1070.38, 1102.07, 1133.79, 1165.49, 1197.20, 1228.90, 1260.62, 1292.31, 1324.03, 1355.73, 1387.44, 1419.14, 1450.86, 1482.55, 1514.27, 1545.97, 1577.68, 1609.38, 1641.10),
				7430 => array(750.02, 818.91, 887.78, 956.67, 1025.54, 1094.43, 1163.30, 1232.19, 1301.06, 1369.94, 1438.82, 1507.71, 1576.59, 1645.46, 1714.34, 1783.23, 1852.10, 1920.98, 1989.86, 2058.75, 2127.62, 2196.50, 2265.38, 2334.26, 2403.14, 2472.02, 2540.89, 2609.78, 2678.66, 2747.54, 2816.41, 2885.30, 2954.18, 3023.07, 3091.93),
				9155 => array(750.02, 818.91, 887.78, 956.67, 1025.54, 1094.43, 1163.30, 1232.19, 1301.06, 1369.94, 1438.82, 1507.71, 1576.59, 1645.46, 1714.34, 1783.23, 1852.10, 1920.98, 1989.86, 2058.75, 2127.62, 2196.50, 2265.38, 2334.26, 2403.14, 2472.02, 2540.89, 2609.78, 2678.66, 2747.54, 2816.41, 2885.30, 2954.18, 3023.07, 3091.93),
				8564 => array(675.68, 725.97, 776.26, 826.55, 876.85, 927.14, 977.44, 1027.73, 1078.02, 1128.32, 1178.61, 1228.90, 1279.19, 1329.48, 1379.79, 1430.08, 1480.37, 1530.66, 1580.95, 1631.24, 1681.54, 1731.84, 1782.13, 1832.42, 1882.71, 1933.01, 1983.30, 2033.59, 2083.88, 2134.18, 2184.48, 2234.77, 2285.06, 2335.35, 2385.64),
				8567 => array(750.02, 818.91, 887.78, 956.67, 1025.54, 1094.43, 1163.30, 1232.19, 1301.06, 1369.94, 1438.82, 1507.71, 1576.59, 1645.46, 1714.34, 1783.23, 1852.10, 1920.98, 1989.86, 2058.75, 2127.62, 2196.50, 2265.38, 2334.26, 2403.14, 2472.02, 2540.89, 2609.78, 2678.66, 2747.54, 2816.41, 2885.30, 2954.18, 3023.07, 3091.93),
				9769583 => array(975.25, 1053.96, 1132.69, 1211.41, 1290.13, 1368.85, 1447.57, 1526.28, 1605.01, 1683.73, 1762.45, 1841.17, 1919.88, 1998.60, 2077.33, 2156.05, 2234.77, 2313.48, 2392.20, 2470.92, 2549.64, 2628.37, 2707.09, 2785.80, 2864.52, 2943.24, 3021.96, 3100.69, 3179.40, 3258.12, 3336.84, 3415.56, 3494.28, 3573.00, 3651.72),
				8661 => array(1326.21, 1423.51, 1520.83, 1618.12, 1715.44, 1812.74, 1910.05, 2007.36, 2104.66, 2201.96, 2299.28, 2396.58, 2493.88, 2591.19, 2688.50, 2785.80, 2883.12, 2980.42, 3077.72, 3175.03, 3272.34, 3369.64, 3466.95, 3564.25, 3661.56, 3758.87, 3856.18, 3953.47, 4050.79, 4148.09, 4245.40, 4342.71, 4440.01, 4537.31, 4634.63),
				7708 => array(750.02, 818.91, 887.78, 956.67, 1025.54, 1094.43, 1163.30, 1232.19, 1301.06, 1369.94, 1438.82, 1507.71, 1576.59, 1645.46, 1714.34, 1783.23, 1852.10, 1920.98, 1989.86, 2058.75, 2127.62, 2196.50, 2265.38, 2334.26, 2403.14, 2472.02, 2540.89, 2609.78, 2678.66, 2747.54, 2816.41, 2885.30, 2954.18, 3023.07, 3091.93),
				7470 => array(675.68, 725.97, 776.26, 826.55, 876.85, 927.14, 977.44, 1027.73, 1078.02, 1128.32, 1178.61, 1228.90, 1279.19, 1329.48, 1379.79, 1430.08, 1480.37, 1530.66, 1580.95, 1631.24, 1681.54, 1731.84, 1782.13, 1832.42, 1882.71, 1933.01, 1983.30, 2033.59, 2083.88, 2134.18, 2184.48, 2234.77, 2285.06, 2335.35, 2385.64),
				8635 => array(675.68, 725.97, 776.26, 826.55, 876.85, 927.14, 977.44, 1027.73, 1078.02, 1128.32, 1178.61, 1228.90, 1279.19, 1329.48, 1379.79, 1430.08, 1480.37, 1530.66, 1580.95, 1631.24, 1681.54, 1731.84, 1782.13, 1832.42, 1882.71, 1933.01, 1983.30, 2033.59, 2083.88, 2134.18, 2184.48, 2234.77, 2285.06, 2335.35, 2385.64),
				8147 => array(1951.60, 2069.67, 2187.76, 2305.84, 2423.92, 2541.99, 2660.07, 2778.16, 2896.24, 3014.31, 3132.39, 3250.48, 3368.55, 3486.63, 3604.71, 3722.79, 3840.86, 3958.95, 4077.03, 4195.11, 4313.18, 4431.27, 4549.35, 4667.43, 4785.50, 4903.58, 5021.67, 5139.75, 5257.82, 5375.90, 5493.99, 5612.07, 5730.14, 5848.22, 5966.30),
				8842 => array(857.17, 932.61, 1008.05, 1083.50, 1158.93, 1234.37, 1309.81, 1385.25, 1460.69, 1536.14, 1611.56, 1687.01, 1762.45, 1837.89, 1913.32, 1988.77, 2064.21, 2139.65, 2215.08, 2290.53, 2365.96, 2441.41, 2516.85, 2592.28, 2667.72, 2743.17, 2818.61, 2894.04, 2969.48, 3044.93, 3120.36, 3195.81, 3271.24, 3346.68, 3422.12),
				8626 => array(750.02, 818.91, 887.78, 956.67, 1025.54, 1094.43, 1163.30, 1232.19, 1301.06, 1369.94, 1438.82, 1507.71, 1576.59, 1645.46, 1714.34, 1783.23, 1852.10, 1920.98, 1989.86, 2058.75, 2127.62, 2196.50, 2265.38, 2334.26, 2403.14, 2472.02, 2540.89, 2609.78, 2678.66, 2747.54, 2816.41, 2885.30, 2954.18, 3023.07, 3091.93),
				8337 => array(675.68, 725.97, 776.26, 826.55, 876.85, 927.14, 977.44, 1027.73, 1078.02, 1128.32, 1178.61, 1228.90, 1279.19, 1329.48, 1379.79, 1430.08, 1480.37, 1530.66, 1580.95, 1631.24, 1681.54, 1731.84, 1782.13, 1832.42, 1882.71, 1933.01, 1983.30, 2033.59, 2083.88, 2134.18, 2184.48, 2234.77, 2285.06, 2335.35, 2385.64),
				7116 => array(675.68, 725.97, 776.26, 826.55, 876.85, 927.14, 977.44, 1027.73, 1078.02, 1128.32, 1178.61, 1228.90, 1279.19, 1329.48, 1379.79, 1430.08, 1480.37, 1530.66, 1580.95, 1631.24, 1681.54, 1731.84, 1782.13, 1832.42, 1882.71, 1933.01, 1983.30, 2033.59, 2083.88, 2134.18, 2184.48, 2234.77, 2285.06, 2335.35, 2385.64),
				8437 => array(675.68, 725.97, 776.26, 826.55, 876.85, 927.14, 977.44, 1027.73, 1078.02, 1128.32, 1178.61, 1228.90, 1279.19, 1329.48, 1379.79, 1430.08, 1480.37, 1530.66, 1580.95, 1631.24, 1681.54, 1731.84, 1782.13, 1832.42, 1882.71, 1933.01, 1983.30, 2033.59, 2083.88, 2134.18, 2184.48, 2234.77, 2285.06, 2335.35, 2385.64),
				7424 => array(857.17, 932.61, 1008.05, 1083.50, 1158.93, 1234.37, 1309.81, 1385.25, 1460.69, 1536.14, 1611.56, 1687.01, 1762.45, 1837.89, 1913.32, 1988.77, 2064.21, 2139.65, 2215.08, 2290.53, 2365.96, 2441.41, 2516.85, 2592.28, 2667.72, 2743.17, 2818.61, 2894.04, 2969.48, 3044.93, 3120.36, 3195.81, 3271.24, 3346.68, 3422.12)
			);
			$not_rach = array();
			foreach ($arResult['REQUESTS'] as $r)
			{
				$para = array(
					'FROM' => $r['PROPERTY_CITY_SENDER_VALUE'],
					'TO' => $r['PROPERTY_CITY_RECIPIENT_VALUE'],
					'TEXT' => $r['PROPERTY_CITY_SENDER_NAME'].' - '.$r['PROPERTY_CITY_RECIPIENT_NAME'],
					'WEIGHT' => $r['PROPERTY_WEIGHT_VALUE'],
					'TRANSIT' => $r['PROPERTY_TRANSIT_MOSCOW_VALUE']
				);
				if ((in_array($r['PROPERTY_CITY_SENDER_VALUE'], $arCitiesTo)) && (in_array($r['PROPERTY_CITY_RECIPIENT_VALUE'], $arCitiesTo)))
				{
					foreach ($weights as $k => $w)
					{
						if ($r['PROPERTY_WEIGHT_VALUE'] <= $w)
						{
							$para['INDEX'] = $k;
							break;
						}				
					}
					if ($para['FROM'] == $main_city)
					{
						$para['COST'] = $tarifs[$para['TO']][$para['INDEX']];
					}
					else
					{
						if ($para['TO'] == $main_city)
						{
							$para['COST'] = $tarifs[$para['FROM']][$para['INDEX']];
						}
						else
						{
							if ($para['TRANSIT'] == 1)
							{
								$para['COST'] = $tarifs[$para['TO']][$para['INDEX']] + $tarifs[$para['FROM']][$para['INDEX']];
							}
							else
							{
								$para['COST'] = max(array($tarifs[$para['TO']][$para['INDEX']], $tarifs[$para['FROM']][$para['INDEX']]));
							}
						}
					}
					$arFromTo[$r['NAME']] = $para;
				}
				else
				{
					if (!in_array($para['FROM'], $arCitiesTo))
					{
						$not_rach[] = $r['PROPERTY_CITY_SENDER_NAME'];
					}
					if (!in_array($para['TO'], $arCitiesTo))
					{
						$not_rach[] = $r['PROPERTY_CITY_RECIPIENT_NAME'];
					}
				}
			}
			
			$not_rach = array_unique($not_rach);
			
			if (count($not_rach) > 0)
			{
				$arResult["WARNINGS"][] = 'Отсутствуют данные для расчета по следующим направлениям:<br>'.implode('<br>',$not_rach).'<br>';
			}
			
			foreach ($arResult['REQUESTS'] as $k => $v)
			{
				$arResult['REQUESTS'][$k]['CALCULATED_COST'] = $arFromTo[$v['NAME']]['COST'];
			}
			/*
			echo '<pre>';
			print_r($arFromTo);
			echo '</pre>';
			*/
		
			$arResult['TITLE'] = 'Тарифы';
			$APPLICATION->SetTitle('Тарифы');
			
		}
		
		if ($mode == 'add')
		{
			$arResult['SHOW_CONTRACTS_AND_BRANCHES'] = false;
			if ($arResult['ADMIN_AGENT'])
			{
				$arResult['LIST_OF_AGENTS'] = AvailableAgents(false, $agent_id, true);
			}
			
			if (isset($_POST['blank']))
			{
				if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
				{
					$_POST = array();
					$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
				}
				else
				{
					$_SESSION[$_POST["key_session"]] = $_POST["rand"];
					$id_in = MakeInvoiceNumber(83, 7, '90-');
					$el = new CIBlockElement;
					$arLoadProductArray = Array(
						"MODIFIED_BY" => $USER->GetID(), 
						"IBLOCK_SECTION_ID" => false,
						"IBLOCK_ID" => 83,
						"PROPERTY_VALUES" => array(
							544 => $id_in['max_id'],
							572 => 257,
							639 => $_POST['current_agent'],
						),
						"NAME" => $id_in['number'],
						"ACTIVE" => "Y"
					);
					if ($z_id = $el->Add($arLoadProductArray))
					{
						$_SESSION['MESSAGE'][] = "Накладная №".$id_in['number']." успешно создана";
						LocalRedirect($arParams['LINK']);
					}
				}
			}
			
			if ((isset($_POST['add'])) || (isset($_POST['add_ctrl'])))
			{
				if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
				{
					$_POST = array();
					$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
				}
				else
				{
					$_SESSION[$_POST["key_session"]] = $_POST["rand"];
					if (intval($_POST['company_payer']) > 0)
					{
						$arResult['SHOW_CONTRACTS_AND_BRANCHES'] = true;
						$arResult["INFO"] = false;
						$res = CIBlockElement::GetList(
							array("NAME" => "asc"), 
							array("IBLOCK_ID" => 40, "PROPERTY_TYPE" => 242, "ID" =>intval($_POST['company_payer']), "ACTIVE" => "Y"), 
							false, 
							false, 
							array("ID","NAME")
						);
						if ($ob = $res->GetNextElement())
						{
							$arFields = $ob->GetFields();
							$arResult["INFO"] =  $arFields;
							$arResult["INFO"]["CONTRACTS"] = array();
							$arResult["INFO"]["BRANCHES"] = array();
							$res_2 = CIBlockElement::GetList(
								array("NAME" => "asc"), 
								array("IBLOCK_ID" => 88, "PROPERTY_CLIENT" =>  $arResult["INFO"]["ID"]), 
								false, 
								false, 
								array("ID", "PROPERTY_NUMBER", "PROPERTY_DATE")
							);
							while ($ob_2 = $res_2->GetNextElement())
							{
								$arFields_2 = $ob_2->GetFields();
								$arResult["INFO"]["CONTRACTS"][] = $arFields_2;
							}
							$res_3 = CIBlockElement::GetList(
								array("NAME" => "asc"), 
								array("IBLOCK_ID" => 89, "PROPERTY_CLIENT" =>  $arResult["INFO"]["ID"]), 
								false, 
								false, 
								array("ID","NAME","PROPERTY_CITY.NAME","PROPERTY_ADRESS")
							);
							while ($ob_3 = $res_3->GetNextElement())
							{
								$arFields_3 = $ob_3->GetFields();
								$arResult["INFO"]["BRANCHES"][] =  $arFields_3;
							}
						}
						
					}
					else
					{
						$arResult["ERR_FIELDS"]["COMPANY_PAYER"] = 'has-error';
					}
					if ((intval($_POST['company_contract']) <= 0) && (count($arResult["INFO"]["CONTRACTS"]) > 0))
					{
						$arResult["ERR_FIELDS"]["CONTRACT_PAYER"] = 'has-error';
					}
					if ((intval($_POST['company_branch']) <= 0) && (count($arResult["INFO"]["BRANCHES"]) > 0))
					{
						$arResult["ERR_FIELDS"]["BRANCH_PAYER"] = 'has-error';
					}
					if (!strlen(NewQuotes($_POST['NUMBER'])))
					{
						$arResult['ERR_FIELDS']['NUMBER'] = 'has-error';
					}
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
					if (count($arResult["ERR_FIELDS"]) == 0)
					{
						$id_in = MakeInvoiceNumber(83, 7, '90-');
						$el = new CIBlockElement;
						$arLoadProductArray = Array(
							"MODIFIED_BY" => $USER->GetID(), 
							"IBLOCK_SECTION_ID" => false,
							"IBLOCK_ID" => 83,
							"DATE_CREATE" => strlen(trim($_POST['DATE_INVOICE'])) ? deleteTabs($_POST['DATE_INVOICE']).' 00:00:00' : date('d.m.Y H:i:s'),
							"PROPERTY_VALUES" => array(
								544 => $id_in['max_id'],
								545 => $_POST['company_payer'],
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
								563 => NewQuotes($_POST['PAYS']),
								564 => deleteTabs($_POST['PAYMENT']),
								565 => floatval(str_replace(',','.',$_POST['FOR_PAYMENT'])),
								566 => floatval(str_replace(',','.',$_POST['COST'])),
								567 => intval($_POST['PLACES']),
								568 => floatval(str_replace(',','.',$_POST['WEIGHT'])),
								569 => $_POST['DIMENSIONS'],
								570 => array('VALUE' => array('TYPE' => 'text', 'TEXT' => NewQuotes($_POST['INSTRUCTIONS']))),
								571 => array('VALUE' => array('TYPE' => 'text', 'TEXT' => NewQuotes($_POST['ADRESS_RECIPIENT']))),
								572 => 258,
								639 => $_POST['current_agent'],
								640 => $_POST['company_contract'],
								641 => $_POST['company_branch'],
								573 => date('d.m.Y H:i:00')
							),
							"NAME" => NewQuotes($_POST['NUMBER']),
							"ACTIVE" => "Y"
						);
						if ($z_id = $el->Add($arLoadProductArray))
						{
							$_SESSION['MESSAGE'][] = "Накладная №".NewQuotes($_POST['NUMBER'])." успешно создана";
							if (strlen(trim($_POST['COMPANY_RECIPIENT'])))
							{
								$res = CIBlockElement::GetList(
									array("ID" =>"desc"), 
									array(
										"IBLOCK_ID" => 84, 
										"PROPERTY_CREATOR" => $_POST['current_agent'], 
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
											579 => $_POST['current_agent'],
											574 => NewQuotes($_POST['NAME_RECIPIENT']),
											575 => NewQuotes($_POST['PHONE_RECIPIENT']),
											576 => $city_recipient,
											577 => deleteTabs($_POST['INDEX_RECIPIENT']),
											578 => NewQuotes($_POST['ADRESS_RECIPIENT']),
											580 => 260
										),
										"NAME" => NewQuotes($_POST['COMPANY_RECIPIENT']),
										"ACTIVE" => "Y"
									);
									$rec_id = $el2->Add($arLoadProductArray2);
								}
							}
							
							if (strlen(trim($_POST['COMPANY_SENDER'])))
							{
								$res = CIBlockElement::GetList(
									array("ID" =>"desc"), 
									array(
										"IBLOCK_ID" => 84, 
										"PROPERTY_CREATOR" => $_POST['current_agent'], 
										"NAME" => NewQuotes($_POST['COMPANY_SENDER']), 
										"PROPERTY_CITY" => $city_sender, 
										"PROPERTY_ADRESS" => NewQuotes($_POST['ADRESS_SENDER']),
										"PROPERTY_TYPE" => 259
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
											579 => $_POST['current_agent'],
											574 => NewQuotes($_POST['NAME_SENDER']),
											575 => NewQuotes($_POST['PHONE_SENDER']),
											576 => $city_sender,
											577 => deleteTabs($_POST['INDEX_SENDER']),
											578 => NewQuotes($_POST['ADRESS_SENDER']),
											580 => 259
										),
										"NAME" => NewQuotes($_POST['COMPANY_SENDER']),
										"ACTIVE" => "Y"
									);
									$rec_id = $el2->Add($arLoadProductArray2);
								}
							}
							LocalRedirect($arParams['LINK']);
						}
					}
				}
			}
			
			$arResult['DEAULTS'] = array(
				'PLACES' => 1,
				'TYPE_DELIVERY' => 244,
				'TYPE_PACK' => 246,
				'WHO_DELIVERY' => 248,
				'TYPE_PAYS' => 251,
				'PAYMENT' => 256,
				'WEIGHT' => '0,2',
				'DIMENSIONS' => array(0,0,0),
				'FOR_PAYMENT' => 0,
				'COST' => 0
			);
			
			$arResult['PAYERS'] = array();
			$res = CIBlockElement::GetList(
				array("NAME" => "asc"), 
				array("IBLOCK_ID" => 40, "PROPERTY_TYPE" => 242, "PROPERTY_AVAILABLE_FOR_AGENT" => 1, "ACTIVE" => "Y"), 
				false, 
				false, 
				array("ID","NAME","PROPERTY_CITY.NAME")
			);
			while ($ob = $res->GetNextElement())
			{
				$arFields = $ob->GetFields();
				$arResult["PAYERS"][] = $arFields;
			}
			$arResult['TITLE'] = GetMessage('TITLE_MODE_ADD');
			$APPLICATION->SetTitle(GetMessage('TITLE_MODE_ADD'));
		}
		
		if ($mode == 'print')
		{
			$arResult['INVOICE'] = false;
			
			$search_nakls = false;
			if ($arResult['ADMIN_AGENT'])
			{
				if ((intval($arResult['CURRENT_AGENT']) > 0) || (intval($arResult['CURRENT_CLIENT'])))
				{
					$search_nakls = true;
				}
			}
			else
			{
				if (intval($arResult['CURRENT_AGENT']) > 0)
				{
					$search_nakls = true;
				}
			}
			
			$id_reqv = intval($_GET['id']);
			if (($id_reqv > 0) && $search_nakls)
			{
				$filter = array("IBLOCK_ID" => 83, "ID" => $id_reqv, "ACTIVE" => "Y");
				if (intval($arResult['CURRENT_CLIENT']) > 0)
				{
					$filter["PROPERTY_CREATOR"] = $arResult["CURRENT_CLIENT"];
				}
				if (intval($arResult['CURRENT_AGENT']) > 0)
				{
					$filter["PROPERTY_AGENT"] = $arResult["CURRENT_AGENT"];
				}
				$res = CIBlockElement::GetList(
					array("id" => "desc"), 
					$filter, 
					false, 
					false, 
					array(
						"ID",
						"NAME",
						"DATE_CREATE",
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
						"PROPERTY_INSTRUCTIONS"
					)
				);
				if ($ob = $res->GetNextElement())
				{
					$r = $ob->GetFields();
					$r['PROPERTY_CITY_SENDER'] = GetFullNameOfCity($r['PROPERTY_CITY_SENDER_VALUE']);
					$r['PROPERTY_CITY_RECIPIENT'] = GetFullNameOfCity($r['PROPERTY_CITY_RECIPIENT_VALUE']);
					$r['PROPERTY_CITY_SENDER_AR'] = explode(', ', $r['PROPERTY_CITY_SENDER']);
					$r['PROPERTY_CITY_RECIPIENT_AR'] = explode(', ', $r['PROPERTY_CITY_RECIPIENT']);
					if (is_array($r['PROPERTY_DIMENSIONS_VALUE']))
					{
						$w = 1;
						for ($i = 0; $i<3; $i++)
						{
							$w = $w*$r['PROPERTY_DIMENSIONS_VALUE'][$i];
						}
						$r["PROPERTY_OB_WEIGHT"] = ($w > 0) ? WeightFormat(($w/6000), false) : "";
					}
					else
					{
						$r["PROPERTY_OB_WEIGHT"] = "";
					}
					$r["PROPERTY_WEIGHT_VALUE"] = ($r["PROPERTY_WEIGHT_VALUE"] > 0) ?  WeightFormat($r["PROPERTY_WEIGHT_VALUE"], false) : "";
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
		
		if ($mode == 'pdf')
		{
			$arResult['INVOICE'] = false;
			$search_nakls = false;
			if ($arResult['ADMIN_AGENT'])
			{
				if ((intval($arResult['CURRENT_AGENT']) > 0) || (intval($arResult['CURRENT_CLIENT'])))
				{
					$search_nakls = true;
				}
			}
			else
			{
				if (intval($arResult['CURRENT_AGENT']) > 0)
				{
					$search_nakls = true;
				}
			}
			$id_reqv = intval($_GET['id']);
			if (($id_reqv > 0) && $search_nakls)
			{
				$filter = array("IBLOCK_ID" => 83, "ID" => $id_reqv, "ACTIVE" => "Y");
				if (intval($arResult['CURRENT_CLIENT']) > 0)
				{
					$filter["PROPERTY_CREATOR"] = $arResult["CURRENT_CLIENT"];
				}
				if (intval($arResult['CURRENT_AGENT']) > 0)
				{
					$filter["PROPERTY_AGENT"] = $arResult["CURRENT_AGENT"];
				}
				$res = CIBlockElement::GetList(
					array("id" => "desc"), 
					$filter, 
					false, 
					false, 
					array(
						"ID",
						"NAME",
						"DATE_CREATE",
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
						"PROPERTY_INSTRUCTIONS"
					)
				);
				if ($ob = $res->GetNextElement())
				{
					$r = $ob->GetFields();
					$r['PROPERTY_CITY_SENDER'] = GetFullNameOfCity($r['PROPERTY_CITY_SENDER_VALUE']);
					$r['PROPERTY_CITY_RECIPIENT'] = GetFullNameOfCity($r['PROPERTY_CITY_RECIPIENT_VALUE']);
					$r['PROPERTY_CITY_SENDER_AR'] = explode(', ', $r['PROPERTY_CITY_SENDER']);
					$r['PROPERTY_CITY_RECIPIENT_AR'] = explode(', ', $r['PROPERTY_CITY_RECIPIENT']);
					if (is_array($r['PROPERTY_DIMENSIONS_VALUE']))
					{
						$w = 1;
						for ($i = 0; $i<3; $i++)
						{
							$w = $w*$r['PROPERTY_DIMENSIONS_VALUE'][$i];
						}
						$r["PROPERTY_OB_WEIGHT"] = ($w > 0) ? WeightFormat(($w/6000), false) : "";
					}
					else
					{
						$r["PROPERTY_OB_WEIGHT"] = "";
					}
					$r["PROPERTY_WEIGHT_VALUE"] = ($r["PROPERTY_WEIGHT_VALUE"] > 0) ?  WeightFormat($r["PROPERTY_WEIGHT_VALUE"], false) : "";
					MakeWaybillPdf($r,'D');
				}
			}
			else
			{
				LocalRedirect($arParams['LINK']);
			}
		}
		
		if ($mode == 'invoice')
		{
			$arResult['INVOICE'] = false;
			$arResult['TRACKING'] = false;
			$arResult['EDIT'] = true;
			$id_reqv = intval($_GET['id']);
			
			$search_nakls = false;
			if ($arResult['ADMIN_AGENT'])
			{
				if ((intval($arResult['CURRENT_AGENT']) > 0) || (intval($arResult['CURRENT_CLIENT'])))
				{
					$search_nakls = true;
				}
			}
			else
			{
				if (intval($arResult['CURRENT_AGENT']) > 0)
				{
					$search_nakls = true;
				}
			}
			
			if (($id_reqv > 0) && $search_nakls)
			{
				
				$filter = array("IBLOCK_ID" => 83, "ID" => $id_reqv);
				if (intval($arResult['CURRENT_CLIENT']) > 0)
				{
					$filter["PROPERTY_CREATOR"] = $arResult["CURRENT_CLIENT"];
				}
				if (intval($arResult['CURRENT_AGENT']) > 0)
				{
					$filter["PROPERTY_AGENT"] = $arResult["CURRENT_AGENT"];
				}
				
				$res = CIBlockElement::GetList(
					array("id" => "desc"), 
					$filter, 
					false, 
					false, 
					array(
						"ID",
						"NAME",
						"DATE_CREATE",
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
						"PROPERTY_CONTRACT.NAME"
					)
				);
				if ($ob = $res->GetNextElement())
				{
					$r = $ob->GetFields();
					$r['PROPERTY_CITY_SENDER'] = GetFullNameOfCity($r['PROPERTY_CITY_SENDER_VALUE']);
					$r['PROPERTY_CITY_RECIPIENT'] = GetFullNameOfCity($r['PROPERTY_CITY_RECIPIENT_VALUE']);
					$arResult['INVOICE'] = $r;
					$_GET['f001'] = $arResult['INVOICE']['NAME'];
					$tr = $APPLICATION->IncludeComponent(
						"black_mist:delivery.get_pods", 
						".default", 
						array(
							"SHOW_FORM" => "N",
							"CACHE_TYPE" => "A",
							"CACHE_TIME" => "3600",
							"SAVE_TO_SITE" => "N",
							"SHOW_TITLE" => "N",
							"SET_TITLE" => "N",
							"TEST_MODE" => "N",
							"NO_TEMPLATE" => "Y"
						),
						false
					);
		
					if (isset($tr['ALL_EVENTS'][$arResult['INVOICE']['NAME']]))
					{
						$arResult['TRACKING'] = $tr;
						if (!$arResult['ADMIN_AGENT'])
						{
							$arResult['EDIT'] = false;
						}
					}
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
			if ((isset($_POST['save'])) || (isset($_POST['apply'])) || (isset($_POST['save_ctrl']))) 
			{
				if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
				{
					$_POST = array();
					$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
				}
				else
				{
					$_SESSION[$_POST["key_session"]] = $_POST["rand"];
					$arChanges = array(
						550 => deleteTabs($_POST['INDEX_SENDER']),
						556 => deleteTabs($_POST['INDEX_RECIPIENT']),
						560 => deleteTabs($_POST['IN_DATE_DELIVERY']),
						561 => deleteTabs($_POST['IN_TIME_DELIVERY']),
						565 => floatval(str_replace(',','.',$_POST['FOR_PAYMENT'])),
						566 => floatval(str_replace(',','.',$_POST['COST'])),
						569 => $_POST['DIMENSIONS'],
						570 => array('VALUE' => array('TYPE' => 'text', 'TEXT' => NewQuotes($_POST['INSTRUCTIONS'])))
					);
					if (intval($_POST['company_payer']) <= 0)
					{
						$arResult["ERR_FIELDS"]["COMPANY_PAYER"] = 'has-error';
					}
					else
					{
						$arChanges[545] = intval($_POST['company_payer']);
					}
					if (intval($_POST['yes_contracts']) == 1)
					{
						if (intval($_POST['company_contract']) <= 0)
						{
							$arResult["ERR_FIELDS"]["CONTRACT_PAYER"] = 'has-error';
						}
						else
						{
							$arChanges[640] = intval($_POST['company_contract']);
						}
					}
					if (intval($_POST['yes_branches']) == 1)
					{
						if (intval($_POST['company_branch']) <= 0)
						{
							$arResult["ERR_FIELDS"]["BRANCH_PAYER"] = 'has-error';
						}
						else
						{
							$arChanges[641] = intval($_POST['company_branch']);
						}
					}
					if ((intval($_POST['agent']) <= 0) && $arResult['ADMIN_AGENT'])
					{
						$arResult["ERR_FIELDS"]["CONTRACT_PAYER"] = 'has-error';
					}
					else
					{
						$arChanges[639] = intval($_POST['agent']);
					}
					if (!strlen(NewQuotes($_POST['NUMBER'])))
					{
						$arResult['ERR_FIELDS']['NUMBER'] = 'has-error';
						$change_name = false;
					}
					else
					{
						$change_name = true;
					}
					if (!strlen(trim($_POST['NAME_SENDER'])))
					{
						$arResult["ERR_FIELDS"]["NAME_SENDER"] = 'has-error';
					}
					else
					{
						$arChanges[546] = NewQuotes($_POST['NAME_SENDER']);
					}
					if (!strlen(trim($_POST['PHONE_SENDER'])))
					{
						$arResult["ERR_FIELDS"]["PHONE_SENDER"] = 'has-error';
					}
					else
					{
						$arChanges[547] = NewQuotes($_POST['PHONE_SENDER']);
					}
					if (!strlen(NewQuotes($_POST['COMPANY_SENDER'])))
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
					if (!strlen(trim($_POST['ADRESS_SENDER'])))
					{
						$arResult["ERR_FIELDS"]["ADRESS_SENDER"] = 'has-error';
					}
					else
					{
						$arChanges[551] = array('VALUE' => array('TYPE' => 'text', 'TEXT' => NewQuotes($_POST['ADRESS_SENDER'])));
					}
					if (!strlen(trim($_POST['NAME_RECIPIENT'])))
					{
						$arResult["ERR_FIELDS"]["NAME_RECIPIENT"] = 'has-error';
					}
					else
					{
						$arChanges[552] = NewQuotes($_POST['NAME_RECIPIENT']);
					}
					if (!strlen(trim($_POST['PHONE_RECIPIENT'])))
					{
						$arResult["ERR_FIELDS"]["PHONE_RECIPIENT"] = 'has-error';
					}
					else
					{
						$arChanges[553] = NewQuotes($_POST['PHONE_RECIPIENT']);
					}
					if (!strlen(NewQuotes($_POST['COMPANY_RECIPIENT'])))
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
						/*
						if (($_POST['TYPE_PAYS'] == 253) && (!strlen($_POST['PAYS'])))
						{
							$arResult["ERR_FIELDS"]["TYPE_PAYS"] = 'has-error';
						}
						else
						{
							*/
							$arChanges[562] = $_POST['TYPE_PAYS'];
							$arChanges[563] = NewQuotes($_POST['PAYS']);
						//}
					}
					if (!$_POST['PAYMENT'])
					{
						$arResult["ERR_FIELDS"]["PAYMENT"] = 'has-error';
					}
					else
					{
						$arChanges[564] = $_POST['PAYMENT'];
					}
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
					if ($change_name)
					{
						$el = new CIBlockElement;
						$res = $el->Update($_POST['id'], array("MODIFIED_BY" => $USER->GetID(),"NAME" => NewQuotes($_POST['NUMBER'])));
					}
					CIBlockElement::SetPropertyValuesEx($_POST['id'], 83, $arChanges);
					$arResult['MESSAGE'][] = 'Накладная '.NewQuotes($_POST['NUMBER']).' изменена';
					if ((isset($_POST['save'])) || (isset($_POST['save_ctrl'])))
					{
						$_SESSION['MESSAGE'][] = 'Накладная '.NewQuotes($_POST['NUMBER']).' изменена';
						LocalRedirect($arParams['LINK']);
					}
				}
			}
			$arResult['INVOICE'] = false;
			$arResult['SHOW_CONTRACTS_AND_BRANCHES'] = false;
			$arResult["INFO"] = false;
			$search_nakls = false;
			$arResult['LIST_OF_AGENTS'] = false;
			if ($arResult['ADMIN_AGENT'])
			{
				$arResult['LIST_OF_AGENTS'] = AvailableAgents(false, $agent_id, true);
			}
			$id_reqv = intval($_GET['id']);
			if ($id_reqv > 0)
			{
				$filter = array("IBLOCK_ID" => 83, "ID" => $id_reqv, "ACTIVE" => "Y");
				if (!$arResult['ADMIN_AGENT'])
				{
					$filter["PROPERTY_AGENT"] = $arResult["CURRENT_AGENT"];
				}
				$res = CIBlockElement::GetList(
					array("id" => "desc"), 
					$filter, 
					false, 
					false, 
					array(
						"ID",
						"NAME",
						"DATE_CREATE",
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
						"PROPERTY_CREATOR",
						"PROPERTY_CREATOR.NAME",
						"PROPERTY_BRANCH",
						"PROPERTY_BRANCH.NAME",
						"PROPERTY_CONTRACT",
						"PROPERTY_CONTRACT.NAME",
						"PROPERTY_AGENT"
					)
				);
				if ($ob = $res->GetNextElement())
				{
					$r = $ob->GetFields();
					$r['PROPERTY_CITY_SENDER'] = GetFullNameOfCity($r['PROPERTY_CITY_SENDER_VALUE']);
					$r['PROPERTY_CITY_RECIPIENT'] = GetFullNameOfCity($r['PROPERTY_CITY_RECIPIENT_VALUE']);
					$arResult['INVOICE'] = $r;
					
					$_GET['f001'] = $arResult['INVOICE']['NAME'];
					$tr = $APPLICATION->IncludeComponent(
						"black_mist:delivery.get_pods", 
						".default", 
						array(
							"SHOW_FORM" => "N",
							"CACHE_TYPE" => "A",
							"CACHE_TIME" => "3600",
							"SAVE_TO_SITE" => "N",
							"SHOW_TITLE" => "N",
							"SET_TITLE" => "N",
							"TEST_MODE" => "N",
							"NO_TEMPLATE" => "Y"
						),
						false
					);
		
					if ((isset($tr['ALL_EVENTS'][$arResult['INVOICE']['NAME']])) && (!$arResult['ADMIN_AGENT']))
					{
						LocalRedirect($arParams['LINK']."?mode=invoice&id=".$arResult['INVOICE']['ID']);
					}
					
					
					$res = CIBlockElement::GetList(
						array("NAME" => "asc"), 
						array("IBLOCK_ID" => 40, "PROPERTY_TYPE" => 242, "ID" => $arResult['INVOICE']["PROPERTY_CREATOR_VALUE"], "ACTIVE" => "Y"), 
						false, 
						false, 
						array("ID","NAME")
					);
					if ($ob = $res->GetNextElement())
					{
						$arFields = $ob->GetFields();
						$arResult['SHOW_CONTRACTS_AND_BRANCHES'] = true;
						$arResult["INFO"] =  $arFields;
						$arResult["INFO"]["CONTRACTS"] = array();
						$arResult["INFO"]["BRANCHES"] = array();
						$res_2 = CIBlockElement::GetList(
							array("NAME" => "asc"), 
							array("IBLOCK_ID" => 88, "PROPERTY_CLIENT" =>  $arResult["INFO"]["ID"]), 
							false, 
							false, 
							array("ID", "PROPERTY_NUMBER", "PROPERTY_DATE")
						);
						while ($ob_2 = $res_2->GetNextElement())
						{
							$arFields_2 = $ob_2->GetFields();
							$arResult["INFO"]["CONTRACTS"][] = $arFields_2;
						}
						$res_3 = CIBlockElement::GetList(
							array("NAME" => "asc"), 
							array("IBLOCK_ID" => 89, "PROPERTY_CLIENT" =>  $arResult["INFO"]["ID"]), 
							false, 
							false, 
							array("ID","NAME","PROPERTY_CITY.NAME","PROPERTY_ADRESS")
						);
						while ($ob_3 = $res_3->GetNextElement())
						{
							$arFields_3 = $ob_3->GetFields();
							$arResult["INFO"]["BRANCHES"][] =  $arFields_3;
						}
					}
					$arResult['TITLE'] = $arResult['INVOICE']['NAME'];
					$APPLICATION->SetTitle($arResult['INVOICE']['NAME']);
				}
				$arResult['PAYERS'] = array();
				$res = CIBlockElement::GetList(
					array("NAME" => "asc"), 
					array("IBLOCK_ID" => 40, "PROPERTY_TYPE" => 242, "PROPERTY_AVAILABLE_FOR_AGENT" => 1, "ACTIVE" => "Y"), 
					false, 
					false, 
					array("ID","NAME","PROPERTY_CITY.NAME")
				);
				while ($ob = $res->GetNextElement())
				{
					$arFields = $ob->GetFields();
					$arResult["PAYERS"][] = $arFields;
				}
			}
			else
			{
				$arResult['TITLE'] = GetMessage('ERR_NO_REQUEST');
				$APPLICATION->SetTitle(GetMessage('ERR_NO_REQUEST'));
			}
		}
		
		if ($mode == 'invoice1c_modal')
		{
			if (strlen(trim($_GET['f001'])))
			{
				$arParamsJson = array(
					'NumDoc' => trim($_GET['f001']),
				);
				$client = new SoapClient(
					'http://'.$currentip.'/sd_msk/ws/DashboardExchange.1cws?wsdl',  
					array('login' => 'DMSUser', 'password' => "1597534682",'exceptions' => false)
				);
			
				$result_0 = $client->GetDocInfo($arParamsJson);
				$mResult_0 = $result_0->return;
				$obj_0 = json_decode($mResult_0, true);
				
				$arResult['REQUEST'] = false;
				$arResult['TITLE'] = 'Накладная не найдена';
				$APPLICATION->SetTitle($arResult['TITLE']);
				if ((is_array($obj_0)) && (count($obj_0) > 0))
				{
					foreach ($obj_0 as $k => $v)
					{
						if (iconv('utf-8', 'windows-1251', $k) == 'Габариты')
						{
							$iii = 0;
							foreach ($v as $vv)
							{
								foreach ($vv as $kkk => $vvv)
								{
									$utf_kkk = iconv('utf-8', 'windows-1251', $kkk);
									$arResult['REQUEST'][$utf_kkk] = $arResult['REQUEST'][$utf_kkk] + iconv('utf-8', 'windows-1251', $vvv);
									$arResult['REQUEST'][iconv('utf-8', 'windows-1251', $k)][$iii][$utf_kkk] = iconv('utf-8', 'windows-1251', $vvv);
								}
								$iii++;
							}
							
						}
						else
						{
							$arResult['REQUEST'][iconv('utf-8', 'windows-1251', $k)] = iconv('utf-8', 'windows-1251', $v);
						}
					}
					
					//получение сообщений//
					$client = new SoapClient(
						'http://'.$currentip.'/sd_msk/ws/DashboardExchange.1cws?wsdl',  
						array('login' => 'DMSUser', 'password' => "1597534682",'exceptions' => false)
					);
					$result = $client->GetDocComments(array('NUMDOC' => iconv('windows-1251','utf-8', $arResult['REQUEST']['НомерНакладной']), 'NUMREQUEST' => iconv('windows-1251','utf-8', $arResult['REQUEST']['НомерЗаявки'])));
					$mResult = $result->return;
					$obj = json_decode($mResult, true);
					$arResult['REQUEST']['Messages'] = false;
					if (is_array($obj[iconv('windows-1251','utf-8','Сообщения')]))
					{
						$arResult['REQUEST']['Messages'] = $obj[iconv('windows-1251','utf-8','Сообщения')];
					}
					//получение сообщений//
					
					$arResult['TITLE'] = 'Номер накладной: '.$arResult['REQUEST']['НомерНакладной'];
					$arResult['TITLE_2'] = 'Номер заявки: '.$arResult['REQUEST']['НомерЗаявки'];
					$APPLICATION->SetTitle($arResult['REQUEST']['НомерНакладной'].' ('.$arResult['REQUEST']['НомерЗаявки'].')');
				}
			}
		}
	}
	else
	{
		$mode = 'close';
	}
}

$this->IncludeComponentTemplate($mode);