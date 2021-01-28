<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

if (isset($_POST['change_company']))
{
	if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
	{
		$_POST = array();
		$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
	}
	else
	{
		$_SESSION[$_POST["key_session"]] = $_POST["rand"];
		unset($_SESSION['CURRENT_BRANCH']);
		unset($_SESSION['CURRENT_CLIENT']);
		unset($_SESSION['CURRENT_CLIENT_INN']);
		unset($_SESSION['CURRENT_INN']);
		unset($_SESSION['CURRENT_AGENT']);
		unset($_SESSION['MESSAGE']);
		unset($_SESSION['ERRORS']);
		unset($_SESSION['WARNINGS']);
		unset($_SESSION['NP_LK_VALUES']);
		unset($_SESSION['NAME_PANEL']);
		$user = new CUser;
		$user->Update($USER->GetID(), array("UF_COMPANY_RU_POST" => $_POST["company_id"]));
	}
}

$rsUser = CUser::GetByID($USER->GetID());
$arUser = $rsUser->Fetch();

$agent_id = intval($arUser["UF_COMPANY_RU_POST"]);

$arResult['OPEN'] = true;
$arResult['COMPANY_PROFILE'] = false;
$arResult['ADMIN_AGENT'] = false;

$arResult["ALL_UKS"] = TheListOfUKs($agent_id);

if(isset($_POST['save']))
{
	if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
	{
		$_POST = array();
		$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
	}
	else
	{
		$_SESSION[$_POST["key_session"]] = $_POST["rand"];
		$arResult["ERRORS_FILEDS"] = array();
		$arChanges = array(
            329 => NewQuotes($_POST['LEGAL_NAME']),
            378 => NewQuotes($_POST['LEGAL_NAME_FULL'])
        );
		
		if (intval($_POST['company_id'] == 0))
		{
			$arResult['AGENT'] = array(
				'ID' => 0,
				'NAME' => NewQuotes(trim($_POST['NAME'])),
				'PROPERTY_EMAIL_VALUE' => $_POST['EMAIL'],
				'PROPERTY_PHONES_VALUE' => $_POST['PHONES'],
				'PROPERTY_CITY' => $_POST['CITY'],
				'PROPERTY_ADRESS_VALUE' => $_POST['ADRESS'],
				'PROPERTY_INN_VALUE' => $_POST['INN'],
                'PROPERTY_LEGAL_NAME_VALUE' => NewQuotes($_POST['LEGAL_NAME']),
                'PROPERTY_LEGAL_NAME_FULL_VALUE' => NewQuotes($_POST['LEGAL_NAME_FULL']),
			);
			if (!strlen(trim($_POST['NAME'])))
			{
				$arResult["ERRORS_FILEDS"]['NAME'] = 'has-error';
			}
		}


		if (!strlen(trim($_POST['EMAIL'])))
		{
			$arResult["ERRORS_FILEDS"]['EMAIL'] = 'has-error';
		}
		else
		{
			if (!preg_match("/^([-\._a-zA-Z0-9]+)@([-\._a-zA-Z0-9]+)\.([a-zA-Z]{2,4})/i", trim($_POST['EMAIL']))) 
			{
				$arResult["ERRORS_FILEDS"]['EMAIL'] = 'has-error';
			}
			else
			{
				$arChanges[243] = trim($_POST['EMAIL']);
			}
		}
		if (!strlen(trim($_POST['PHONES'])))
		{
			$arResult["ERRORS_FILEDS"]['PHONES'] = 'has-error';
		}
		else
		{
			$arChanges[265] = trim($_POST['PHONES']);
		}
		if (!strlen(trim($_POST['CITY'])))
		{
			$arResult["ERRORS_FILEDS"]['CITY'] = 'has-error';
		}
		else
		{
			$city = GetCityId(trim($_POST['CITY']));
			if ($city <= 0)
			{
				$arResult["ERRORS_FILEDS"]['CITY'] = 'has-error';
			}
			else
			{
				$arChanges[187] =  $city;
			}
		}
		if (!strlen(trim($_POST['ADRESS'])))
		{
			$arResult["ERRORS_FILEDS"]['ADRESS'] = 'has-error';
		}
		else
		{
			$arChanges[190] = trim($_POST['ADRESS']);
		}
		if (!strlen(trim($_POST['INN_REAL'])))
		{
			$arResult["ERRORS_FILEDS"]['INN_REAL'] = 'has-error';
		}
		else
		{
			if (!is_valid_inn(trim($_POST['INN_REAL'])))
			{
				$arResult["ERRORS_FILEDS"]['INN_REAL'] = 'has-error';
			}
			else
			{
				$arChanges[729] = trim($_POST['INN_REAL']);
			}
            //$arChanges[729] = trim($_POST['INN_REAL']);
		}
		if (intval($_POST['company_id'] == 0))
		{
			if (count($arResult["ERRORS_FILEDS"]) == 0)
			{
				$arJson = array(
					'ID' => 0,
					'NAME' => iconv('windows-1251','utf-8',NewQuotes(trim($_POST['NAME']))),
					'PROPERTY_EMAIL_VALUE' => iconv('windows-1251','utf-8',$arChanges['243']),
					'PROPERTY_PHONES_VALUE' => iconv('windows-1251','utf-8',$arChanges['265']),
					'PROPERTY_CITY' => iconv('windows-1251','utf-8',trim($_POST['CITY'])),
					'PROPERTY_ADRESS_VALUE' => iconv('windows-1251','utf-8',$arChanges['190']),
					'PROPERTY_INN_VALUE' => iconv('windows-1251','utf-8',$arChanges['237']),
                    'PROPERTY_LEGAL_NAME_VALUE' => iconv('windows-1251','utf-8',NewQuotes($_POST['LEGAL_NAME'])),
                    'PROPERTY_LEGAL_NAME_FULL_VALUE' => iconv('windows-1251','utf-8',NewQuotes($_POST['LEGAL_NAME_FULL'])),
					'PROPERTY_TYPE_ENUM_ID' => $arParams['TYPE_COMPANY'],
					'send' => 1
				);
				
				if (intval($arParams["TEMPLATE_ID"]) > 0)
				{
					$event = new CEvent;
					$arEventFields = array(
						'USER_ID' => $arUser['ID'],
						'USER_LOGIN' => $arUser['LOGIN'],
						'USER_NAME' => $arUser['NAME'],
						'USER_LAST_NAME' => $arUser['LAST_NAME'],
						'NAME' => $_POST['NAME'],
						'EMAIL' => $_POST['EMAIL'],
						'PHONES' => $_POST['PHONES'],
						'CITY' => $_POST['CITY'],
						'ADRESS' => $_POST['ADRESS'],
						'INN' => $_POST['INN']
					);
					$event->Send("NEWPARTNER_LK", "s5", $arEventFields, "N", intval($arParams["TEMPLATE_ID"]));
					$arResult["MESSAGE"][] = 'Профиль компании успешно отправлен на модерацию';
				}
				else
				{
					$arJson['send'] = 0;
					$arResult["ERRORS"][] = 'Отправка письма в настоящее время невозможна, повторите попытку позднее';
				}

				$json_string = json_encode($arJson);
				$user = new CUser;
				$user->Update($USER->GetID(), array("UF_COMPANY_JSON" => $json_string));
				$_POST = array();

			}
		}
		else
		{
			if (count($arChanges) > 0)
			{
				CIBlockElement::SetPropertyValuesEx($_POST['company_id'], 40, $arChanges);
				$arResult["MESSAGE"][] = 'Профиль компании изменен';
			}
		}
	}
}

if (isset($_POST['save_branch']))
{
	if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
	{
		$_POST = array();
		$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
	}
	else
	{
		$_SESSION[$_POST["key_session"]] = $_POST["rand"];
		$arResult["ERRORS_FILEDS"] = array();
		$change_name = false;
		$arChanges = array(
			636 => NewQuotes($_POST['FIO']),
			637 => NewQuotes($_POST['PHONES']),
			638 => NewQuotes($_POST['INDEX']),
			633 => NewQuotes($_POST['ADRESS'])
		);
		if (!strlen(trim($_POST['CITY'])))
		{
			$arResult["ERRORS_FILEDS"]['CITY'] = 'has-error';
		}
		else
		{
			$city = GetCityId(trim($_POST['CITY']));
			if ($city <= 0)
			{
				$arResult["ERRORS_FILEDS"]['CITY'] = 'has-error';
			}
			else
			{
				$arChanges[632] =  $city;
			}
		}
		if (strlen(trim($_POST['EMAIL'])))
		{
			if (!preg_match("/^([-\._a-zA-Z0-9]+)@([-\._a-zA-Z0-9]+)\.([a-zA-Z]{2,4})/i", trim($_POST['EMAIL']))) 
			{
				$arResult["ERRORS_FILEDS"]['EMAIL'] = 'has-error';
			}
			else
			{
				$arChanges[645] = trim($_POST['EMAIL']);
			}
		}
		if (!strlen(trim($_POST['NAME'])))
		{
			$arResult["ERRORS_FILEDS"]['NAME'] = 'has-error';
		}
		else
		{
			if (NewQuotes($_POST['NAME_OLD']) != NewQuotes($_POST['NAME']))
			{
				$change_name = true;
			}
		}
		if ($change_name)
		{
			$el = new CIBlockElement;
			$res = $el->Update($_POST['branch_id'], array("MODIFIED_BY" => $USER->GetID(),"NAME" => NewQuotes($_POST['NAME'])));
			CIBlockElement::SetPropertyValuesEx($_POST['branch_id'], false, $arChanges);
		}
		else
		{
			CIBlockElement::SetPropertyValuesEx($_POST['branch_id'], 89, $arChanges);
		}
		$arResult["MESSAGE"][] = 'Профиль филиала изменен';
	}
}

$rsUser = CUser::GetByID($USER->GetID());
$arUser = $rsUser->Fetch();

$agent_id = intval($arUser["UF_COMPANY_RU_POST"]);
$arResult['USER_BRANCH'] = false;

if ($agent_id > 0)
{
	$db_props = CIBlockElement::GetProperty(40, $agent_id, array("sort" => "asc"), Array("ID"=>211));
	if($ar_props = $db_props->Fetch())
	{
    	$agent_type = $ar_props["VALUE"];
		$arResult['COMPANY_PROFILE'] = true;
		if (in_array($agent_type, array(51, 53, 242)))
		{
			$arResult['AGENT'] = GetCompany($agent_id);
			if ($agent_type == 51)
			{
				$arResult['ADMIN_AGENT'] = true;
			}
			if ($arResult['AGENT']["PROPERTY_TYPE_WORK_BRANCHES_ENUM_ID"] == 301)
			{
				if (intval($_SESSION['CURRENT_BRANCH']) == 0)
				{
					LocalRedirect('/choice-branch/');
				}
				else
				{
					$arResult['USER_BRANCH'] = $_SESSION['CURRENT_BRANCH'];
				}
			}
			else
			{
			     $arResult['USER_BRANCH'] = (intval($arUser["UF_BRANCH"]) > 0) ? intval($arUser["UF_BRANCH"]) : false;
			}
			if ($arResult['USER_BRANCH'])
			{
				$arResult['BRANCH_INFO'] = GetBranch($arResult['USER_BRANCH'], $agent_id);
                
                /*Контракты*/
                $arContracts = array();
                $res = CIBlockElement::GetList(
                    array("id" => "desc"), 
                    array("IBLOCK_ID" => 88, "PROPERTY_CLIENT" => $agent_id),
                    false, 
                    false, 
                    array(
                        "ID","NAME"
                    )
                );
                while ($ob = $res->GetNextElement())
                {
                    $arFields = $ob->GetFields();
                    $arContracts[] = $arFields;
                }
                if (count($arContracts) > 0)
                {
                    $arResult['CLIENT_CONTRACT'] = $arContracts[0]["ID"];
                    $arResult['CLIENT_CONTRACT_NAME'] = $arContracts[0]["NAME"];
                }
                /*Контракты*/
                

                
				if ($arResult['BRANCH_INFO'])
				{
                    /*
                    $arLimitsperiods = json_decode($arResult['BRANCH_INFO']['PROPERTY_LIMITPERIODS_VALUE']['TEXT'], false);
                    $arResult["LIMITPERIODS"]= $arLimitsperiods[$arResult['CLIENT_CONTRACT']];
                    echo '<pre>';
                    print_r($arLimitsperiods);
                    echo '</pre>';
					$arResult['QUATER_CLASS'] = array('', '', '', '');
					$quarter = GetQuarter();
					$arResult['QUATER_CLASS'][$quarter] = (($arResult['BRANCH_INFO']['PROPERTY_LIMIT_VALUE'][$quarter] -$arResult['BRANCH_INFO']['SPENT'][$quarter]) > 0) ? 'info' : 'danger';
                    */

                    if (strlen($arResult['BRANCH_INFO']['PROPERTY_BUDGETPERIODS_VALUE']['TEXT']))
                    {
                        $js_dcd = json_decode(htmlspecialcharsBack($arResult['BRANCH_INFO']['PROPERTY_BUDGETPERIODS_VALUE']['TEXT']), true);
                        $arResult["LIMITPERIODS"] = $js_dcd[$arResult['CLIENT_CONTRACT']];
                        if (is_array($arResult["LIMITPERIODS"]))
                        {
                            foreach ($arResult["LIMITPERIODS"] as $k => $v)
                            {
                                if (floatval($v['spend']) > 0)
                                {
                                    $arResult["LIMITPERIODS"][$k]['class'] = (floatval($v['left']) > 0) ? '' : 'danger';
                                }
                            }
                        }
                    }
				}
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
	if (strlen($arUser['UF_COMPANY_JSON']))
	{
		$arJ = json_decode($arUser['UF_COMPANY_JSON']);
		foreach ($arJ as $k => $v)
		{
			$arResult['AGENT'][$k] = iconv('utf-8','windows-1251',$v);
		}
		if ($arResult['AGENT']['send'] == 1)
		{
			$arResult["WARNINGS"][] = 'Профиль компании ожидает модерации';
		}
	}
	else
	{
		$arResult["WARNINGS"][] = GetMessage("ERR_NO_COMPANY");
	}
}
/*
if ($arUser["ID"] == 211)
{
	if(CModule::IncludeModule("subscribe"))
	{ 
		$rsRubric = CRubric::GetList(array("SORT"=>"ASC", "NAME"=>"ASC"), array("ACTIVE"=>"Y", "LID"=>"s5", "VISIBLE" => "Y")); 
		$arRubrics = array(); 
		while($arRubric = $rsRubric->GetNext()) 
		{ 
			$arResult["RUBRIC_LIST"][] = $arRubric; 
		} 
		echo '<pre>';
		print_r($arResult["RUBRIC_LIST"]);
		echo '</pre>';
		if (strlen($arResult['AGENT']['PROPERTY_EMAIL_VALUE']))
		{
			$subscription = CSubscription::GetByEmail($arResult['AGENT']['PROPERTY_EMAIL_VALUE']);
			print_r($subscription);
			if($subscription->ExtractFields("str_"))
				$IDstr = (integer)$str_ID;
			else
				$IDstr=0;
			echo $IDstr;
		}
	}
}
*/
if ($arResult['USER_BRANCH'])
{
	$APPLICATION->SetTitle("Профиль филиала");
	$this->IncludeComponentTemplate('template_branch');
}
else
{
	$this->IncludeComponentTemplate();
}
?>