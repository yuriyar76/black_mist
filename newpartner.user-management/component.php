<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

$rsUser = CUser::GetByID($USER->GetID());
$arUser = $rsUser->Fetch();
$agent_id = intval($arUser["UF_COMPANY_RU_POST"]);
if (intval($_POST['UK']) > 0)
{
    $agent_id = intval($_POST['UK']);
}
if (intval($agent_id) == 0)
{
    die();
}
$arResult["AGENT"] = GetCompany($agent_id);
if ($arResult["AGENT"]["PROPERTY_TYPE_ENUM_ID"] != 51)
{
    die();
}
$arResult["UK"] = $arResult["AGENT"]["ID"];
$arResult["ALL_UKS"] = TheListOfUKs(false);
$currentip = GetSettingValue(683, false, $arResult["UK"]);
$currentport = intval(GetSettingValue(761, false, $arResult["UK"]));
$currentlink = GetSettingValue(704, false, $arResult["UK"]);
$login1c = GetSettingValue(705, false, $arResult["UK"]);
$pass1c = GetSettingValue(706, false, $arResult["UK"]);

if ((!strlen(trim($currentip))) || (!strlen(trim($currentlink))) || (!strlen(trim($login1c))) || (!strlen(trim($pass1c))))
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
	if ($currentport > 0) {
		$url = "http://".$currentip.':'.$currentport.$currentlink;
	}
	else {
		$url = "http://".$currentip.$currentlink;
	}
	$arResult["USER_SELECTED"] = false;
	$arResult["AGENTS"] = array(
		0 => ""
	);
	$arResult["AGENTS_TYPES"] = array();
	$res = CIBlockElement::GetList(
		array("PROPERTY_TYPE" => "asc","name" => "asc"), 
		array("IBLOCK_ID"=> 40, "ACTIVE"=>"Y", "PROPERTY_UK" =>$arResult["UK"]), 
		false, 
		false, 
		array("ID", "NAME","PROPERTY_TYPE")
	);
	while($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();
		$arResult["AGENTS"][$arFields["ID"]]  = $arFields["NAME"].' ('.$arFields["PROPERTY_TYPE_VALUE"].')';
		$arResult["AGENTS_TYPES"][$arFields["ID"]] = $arFields["PROPERTY_TYPE_ENUM_ID"];
	}
	
	if (isset($_POST['add']))
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
			$arChanges = array();
			$arResult['AGENT'] = array(
				'NAME' => NewQuotes($_POST['NAME']),
				'PROPERTY_EMAIL_VALUE' => $_POST['EMAIL'],
				'PROPERTY_PHONES_VALUE' => $_POST['PHONES'],
				'PROPERTY_CITY' => $_POST['CITY'],
				'PROPERTY_ADRESS_VALUE' => $_POST['ADRESS'],
				'PROPERTY_INN_VALUE' => $_POST['INN'],
                'PROPERTY_INN_REAL_VALUE' => $_POST['INN_REAL'],
				'PROPERTY_TYPE_ENUM_ID' => $_POST['user_type'],
                'PROPERTY_UK' => $arResult["UK"],
                'INN_MORE_ONE' => $_POST['INN_MORE_ONE']
			);
			if (!strlen(trim($_POST['NAME'])))
			{
				$arResult["ERRORS_FILEDS"]['NAME'] = 'has-error';
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
			if (!strlen(trim($_POST['INN'])))
			{
				$arResult["ERRORS_FILEDS"]['INN'] = 'has-error';
			}
			else
			{
				//if (!is_valid_inn(trim($_POST['INN'])))
				//{
				//	$arResult["ERRORS_FILEDS"]['INN'] = 'has-error';
				//}
				//else
				//{
				//	$arChanges[237] = trim($_POST['INN']);
				//}
				$arChanges[237] = trim($_POST['INN']);
			}
			if ($_POST['user_type'] == 0)
			{
				$arResult["ERRORS_FILEDS"]['USER_TYPE'] = 'has-error';
			}
			else
			{
				$arChanges[211] = $_POST['user_type'];
			}
			if (count($arResult["ERRORS_FILEDS"]) == 0)
			{
				$curl = curl_init();
				curl_setopt_array($curl, array(    
					CURLOPT_URL => $url,
					CURLOPT_HEADER => true,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_NOBODY => true));
				
				$header = explode("\n", curl_exec($curl));
				curl_close($curl);
				
				if (strlen(trim($header[0])))
				{
					if ($currentport > 0) {
						$client = new SoapClient($url, array('login' => $login1c, 'password' => $pass1c, 'proxy_host' => $currentip, 'proxy_port' => $currentport, 'exceptions' => false));
					}
					else {
						$client = new SoapClient($url, array('login' => $login1c, 'password' => $pass1c,'exceptions' => false));
					}
					$arParamsJson = array('INN' => trim($_POST['INN']));
					$result = $client->SetPrefix($arParamsJson);			
					$mResult = $result->return;
					$obj = json_decode($mResult, true);
					$prefix = iconv('utf-8', 'windows-1251', trim($obj['Prefix_'.trim($_POST['INN'])]));
					if (!strlen($prefix))
					{
                        $arResult["ERRORS"][] = iconv('utf-8', 'windows-1251', $obj['Error']);
                        if (intval($obj['ClientsCount']) > 1)
                        {
                            $innmoreone = (strlen($_POST['INN_MORE_ONE'])) ? $_POST['INN_MORE_ONE'] : $_POST['user_id'].rand(1000, 9999);
                            $arResult["WARNINGS"][] = "Используйте уже существующий ID обмена, присвоенный искомому контрагенту в 1с, либо укажите в качестве ID обмена (в 1с в том числе) <strong>".$innmoreone."</strong>.";
                            $arResult['AGENT']['PROPERTY_INN_VALUE'] = $innmoreone;
                        }
						//$arResult["ERRORS"][] = 'Отсутствует запись о контрагенте в базе 1с либо не указан префикс';
					}
					else
					{
						$arGroups = $_POST['arGroups'];
						$email_from = 'info@newpartner.ru';
						$site = 'newpartner.ru';
						switch ($_POST['user_type'])
						{
							case 51:
								$arGroups[] = 16;
								$email_from = 'dms@newpartner.ru';
								$site = 'dms.newpartner.ru';
								$t_type = 'Управляющая компания';
								break;
							case 52:
								$arGroups[] = 17;
								$email_from = 'dms@newpartner.ru';
								$site = 'dms.newpartner.ru';
								$t_type = 'Интернет-магазин';
								break;
							case 53:
								$arGroups[] = 15;
								$arGroups[] = 4;
								$email_from = 'agent@newpartner.ru';
								$site = 'agent.newpartner.ru';
								$t_type = 'Агент';
								break;
							case 222:
								$email_from = 'dms@newpartner.ru';
								$site = 'dms.newpartner.ru';
								$t_type = 'Получатель';
								break;
							case 242:
								$arGroups[] = 22;
								//$email_from = 'client@newpartner.ru';
                                $email_from_sets = GetSettingValue(723, false, $arResult["UK"]);
                                $email_from = strlen(trim($email_from_sets)) ? $email_from_sets : 'client@newpartner.ru';
								$site = 'client.newpartner.ru';
								$t_type = 'Клиент';
								break;
								
						}
						$arChanges[227] = 0;
						$arChanges[304] = GetMaxIDIN(40, 3, false);
						$arChanges[377] = $prefix;
						$arChanges[379] = $_POST['user_name'];
						$arChanges[467] = $arResult["UK"];
						$arChanges[670] = 280;
						$arChanges[681] = 5000;
                        $arChanges[729] = trim($_POST['INN_REAL']);
						$el = new CIBlockElement;
						$arLoadProductArray = array(
							"IBLOCK_SECTION_ID" => false,
							"IBLOCK_ID" => 40,
							"NAME" => NewQuotes($_POST['NAME']),
							"ACTIVE" => "Y",
							"PROPERTY_VALUES" => $arChanges
						);
						if ($company_id = $el->Add($arLoadProductArray))
						{
							$user = new CUser;
							$user->Update($_POST['user_id'], array("UF_COMPANY_RU_POST"=> $company_id, "UF_ROLE" => 4937477, "ACTIVE"=> "Y"));
							CUser::SetUserGroup($_POST['user_id'], $arGroups);
							$event = new CEvent;
							$arEventFields = array(
								'FROM' => $email_from,
								'EMAIL' => $_POST['user_email'],
								'LINK' => $site,
								'NAME' => $_POST['user_name'],
                                'COMPANY' => NewQuotes($_POST['NAME'])
							);
							$event->Send("NEWPARTNER_LK", "s5", $arEventFields, "N", 201);
							$arResult["AGENTS"][$company_id]  = NewQuotes($_POST['NAME']).' ('.$t_type.')';
							$arResult["COMAPNY_ID"] = $company_id;
							$subscr = addAgentSubscription($_POST['user_type'], trim($_POST['user_email']), $_POST['user_id']);
							$arResult["MESSAGE"][] = 'Компания успешно создана';
						}
						else
						{
							$arResult["ERRORS"][] = 'Произошла ошибка';
						}
					}
				}
				else
				{
					$arResult["ERRORS"][] = 'Невозможно подключиться к базе 1с';
				}
			}
		}
	}
	
	if (isset($_POST['connect']))
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
			/*
			if ($_POST['user_type'] == 0)
			{
				$arResult["ERRORS_FILEDS"]['USER_TYPE'] = 'has-error';
			}
			*/
			if ($_POST['company'] == 0)
			{
				$arResult["ERRORS_FILEDS"]['COMPANY'] = 'has-error';
			}
			if (count($arResult["ERRORS_FILEDS"]) == 0)
			{
				
				if ($arResult["AGENTS_TYPES"][$_POST['company']] != $_POST['user_type'])
				{
					$arResult["ERRORS"][] = 'Несоответствие выбранного типа компании';
				}
				else
				{
                    $arGroups = $_POST['arGroups'];
                    $email_from = 'info@newpartner.ru';
                    $site = 'newpartner.ru';
                    switch ($_POST['user_type'])
                    {
                        case 51:
                            $arGroups[] = 16;
                            $email_from = 'dms@newpartner.ru';
                            $site = 'dms.newpartner.ru';
                            break;
                        case 52:
                            $arGroups[] = 17;
                            $email_from = 'dms@newpartner.ru';
                            $site = 'dms.newpartner.ru';
                            break;
                        case 53:
                            $arGroups[] = 15;
                            $arGroups[] = 4;
                            $email_from = 'agent@newpartner.ru';
                            $site = 'agent.newpartner.ru';
                            break;
                        case 222:
                            $email_from = 'dms@newpartner.ru';
                            $site = 'dms.newpartner.ru';
                            break;
                        case 242:
                            $arGroups[] = 22;
                            //$email_from = 'client@newpartner.ru';
                            $email_from_sets = GetSettingValue(723, false, $arResult["UK"]);
                            $email_from = strlen(trim($email_from_sets)) ? $email_from_sets : 'client@newpartner.ru';
                            $site = 'client.newpartner.ru';
                            break;

                    }

                    $user = new CUser;
                    $user->Update($_POST['user_id'], array("UF_COMPANY_RU_POST"=> $_POST['company'], "UF_ROLE" => 5430579, "ACTIVE"=> "Y"));
                    CUser::SetUserGroup($_POST['user_id'], $arGroups);
                    $event = new CEvent;
                    $arEventFields = array(
                        'FROM' => $email_from,
                        'EMAIL' => $_POST['user_email'],
                        'LINK' => $site,
                        'NAME' => $_POST['user_name'],
                        'COMPANY' => NewQuotes($_POST['NAME'])
                    );
                    $event->Send("NEWPARTNER_LK", "s5", $arEventFields, "N", 201);
                    $subscr = addAgentSubscription($_POST['user_type'], trim($_POST['user_email']), $_POST['user_id']);
                    $arResult["MESSAGE"][] = 'Пользователь успешно привязан к компании';
				}
			}
		}
	}
	
	if (isset($_POST['deactivate']))
	{
		if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
		{
			$_POST = array();
			$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
		}
		else
		{
			$_SESSION[$_POST["key_session"]] = $_POST["rand"];
			$user = new CUser;
			$user->Update($_POST['user_id'], array("ACTIVE"=> "N"));
			$arResult["MESSAGE"][] = 'Пользователь успешно деактивирован';
		}
	}
	
	if (isset($_POST['delete']))
	{
		if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
		{
			$_POST = array();
			$arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
		}
		else
		{
			$_SESSION[$_POST["key_session"]] = $_POST["rand"];
			if (CUser::Delete($_POST['user_id']))
			{
				$arResult["MESSAGE"][] = 'Пользователь успешно удален';
			}
			else
			{
				$arResult["ERRORS"][] = 'Произошла ошибка удаления';
			}
		}
	}
	
	if (intval($_GET['userid']) > 0)
	{
		$rsUser = CUser::GetByID(intval($_GET['userid']));
		if ($arResult["USER_INFO"] = $rsUser->Fetch())
		{
			$arResult['USER_GROUPS'] = array();
			$arGroups = CUser::GetUserGroup($arResult["USER_INFO"]["ID"]);
			$arResult['arGroups'] = $arGroups;
			foreach ($arGroups as $g)
			{
				$rsGroup = CGroup::GetByID($g);
				$arResult['USER_GROUPS'][] = $rsGroup->Fetch();
			}
			$arResult["USER_COMPANY_YET"] = false;
			if (intval($arResult["USER_INFO"]["UF_COMPANY_RU_POST"]) > 0)
			{
				$arResult["WARNINGS"][] = 'Пользователь уже прикреплен к компании';
				$arResult["USER_COMPANY_YET"] = true;
			}
			if ($arResult["USER_INFO"]["ACTIVE"] == "N")
			{
				$arResult["WARNINGS"][] = 'Пользователь неактивен';
			}
			$arResult["USER_SELECTED"] = true;
			$arResult["COMAPNY_ID"] = 0;
			if ($arResult["USER_COMPANY_YET"])
			{
				$arResult['AGENT'] = GetCompany($arResult["USER_INFO"]["UF_COMPANY_RU_POST"]);
				$arResult["COMAPNY_ID"] = $arResult['AGENT']["ID"];
			}
			else
			{
				if (strlen($arResult["USER_INFO"]["UF_COMPANY_JSON"]))
				{
					if (!$_POST['add'])
					{
						$ArIgInfo = json_decode($arResult["USER_INFO"]["UF_COMPANY_JSON"], true);
						foreach ($ArIgInfo as $k => $v)
						{
							$arResult['AGENT'][$k] = NewQuotes(iconv('utf-8','windows-1251',$v));
						}
                        $arResult['AGENT']['PROPERTY_INN_REAL_VALUE'] = $arResult['AGENT']['PROPERTY_INN_VALUE'];
					}
					if(strlen(trim($arResult['AGENT']['PROPERTY_INN_VALUE'])))
					{
						$infofrominn = GetIDAgentByINN(trim($arResult['AGENT']['PROPERTY_INN_VALUE']), false, false);
						if (count($infofrominn) == 0)
						{
							$arResult['ABOUT_COMPANY'] = 1;
							$arResult["WARNINGS"][] = 'Комания с ID обмена '.$arResult['AGENT']['PROPERTY_INN_VALUE'].' не найдена';
						}
						elseif (count($infofrominn) == 1)
						{
							$arResult['ABOUT_COMPANY'] = 2;
							$arResult["COMAPNY_ID"] = $infofrominn[0]["ID"];
							$arResult["WARNINGS"][] = 'Комания с ID обмена '.$arResult['AGENT']['PROPERTY_INN_VALUE'].' уже существует';
						}
						else
						{
							$arResult['ABOUT_COMPANY'] = 3;
							$arResult["COMAPNY_ID"] = $infofrominn[0]["ID"];
							$arResult["WARNINGS"][] = 'Существует несколько компаний с ID обмена '.$arResult['AGENT']['PROPERTY_INN_VALUE'];
						}
					}
				}
			}
		}
		else
		{
			$arResult["ERRORS"][] = 'Пользователь не найден';
		}
	}
	else
	{
		$arResult["ERRORS"][] = 'Отсутствует пользователь в запросе, пожалуйста, введите ID пользователя';
	}
}

$this->IncludeComponentTemplate();
?>