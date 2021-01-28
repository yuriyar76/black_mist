<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */

/**
 * Bitrix vars
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @global CDatabase $DB
 * @global CUserTypeManager $USER_FIELD_MANAGER
 * @param array $arParams
 * @param array $arResult
 * @param CBitrixComponent $this
 */

if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)
	die();

global $USER_FIELD_MANAGER;

include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

// apply default param values
$arDefaultValues = array(
	"SHOW_FIELDS" => array(),
	"REQUIRED_FIELDS" => array(),
	"AUTH" => "Y",
	"USE_BACKURL" => "Y",
	"SUCCESS_PAGE" => "",
);

$arResult["LIST_OF_UKS"] = TheListOfUKs(false, true, true, "sort");
foreach ($arResult["LIST_OF_UKS"] as $k => $v)
{
    $arResult["LIST_OF_UKS"][$k]["PROPERTY_PAGE_DOGOVOR_VALUE"] = GetSettingValue(722, false, $v["ID"]);
}

foreach ($arDefaultValues as $key => $value)
{
	if (!is_set($arParams, $key))
		$arParams[$key] = $value;
}
if(!is_array($arParams["SHOW_FIELDS"]))
	$arParams["SHOW_FIELDS"] = array();
if(!is_array($arParams["REQUIRED_FIELDS"]))
	$arParams["REQUIRED_FIELDS"] = array();

// if user registration blocked - return auth form
if (COption::GetOptionString("main", "new_user_registration", "N") == "N")
	$APPLICATION->AuthForm(array());

$arResult["EMAIL_REQUIRED"] = (COption::GetOptionString("main", "new_user_email_required", "Y") <> "N");
$arResult["USE_EMAIL_CONFIRMATION"] = (COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") == "Y" && $arResult["EMAIL_REQUIRED"]? "Y" : "N");

// apply core fields to user defined
$arDefaultFields = array(
	"LOGIN",
	"PASSWORD",
	"CONFIRM_PASSWORD",
);

if($arResult["EMAIL_REQUIRED"])
{
	$arDefaultFields[] = "EMAIL";
}

$def_group = COption::GetOptionString("main", "new_user_registration_def_group", "");
if($def_group <> "")
	$arResult["GROUP_POLICY"] = CUser::GetGroupPolicy(explode(",", $def_group));
else
	$arResult["GROUP_POLICY"] = CUser::GetGroupPolicy(array());

$arResult["SHOW_FIELDS"] = array_unique(array_merge($arDefaultFields, $arParams["SHOW_FIELDS"]));
$arResult["REQUIRED_FIELDS"] = array_unique(array_merge($arDefaultFields, $arParams["REQUIRED_FIELDS"]));

// use captcha?
// $arResult["USE_CAPTCHA"] = COption::GetOptionString("main", "captcha_registration", "N") == "Y" ? "Y" : "N";
//$arResult["USE_CAPTCHA"] = "N";
$arResult["USE_CAPTCHA"] = ($arParams["USE_CAPTCHA"] == "Y") ? "Y" : "N";

// start values
$arResult["VALUES"] = array();
$arResult["ERRORS"] = array();
$register_done = false;

// register user
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_REQUEST["register_submit_button"]) && !$USER->IsAuthorized())
{
	if(COption::GetOptionString('main', 'use_encrypted_auth', 'N') == 'Y')
	{
		//possible encrypted user password
		$sec = new CRsaSecurity();
		if(($arKeys = $sec->LoadKeys()))
		{
			$sec->SetKeys($arKeys);
			$errno = $sec->AcceptFromForm(array('REGISTER'));
			if($errno == CRsaSecurity::ERROR_SESS_CHECK)
				$arResult["ERRORS"][] = GetMessage("main_register_sess_expired");
			elseif($errno < 0)
				$arResult["ERRORS"][] = GetMessage("main_register_decode_err", array("#ERRCODE#"=>$errno));
		}
	}

	// check emptiness of required fields
	foreach ($arResult["SHOW_FIELDS"] as $key)
	{
		if ($key != "PERSONAL_PHOTO" && $key != "WORK_LOGO")
		{
			$arResult["VALUES"][$key] = $_REQUEST["REGISTER"][$key];
			if (in_array($key, $arResult["REQUIRED_FIELDS"]) && trim($arResult["VALUES"][$key]) == '')
				$arResult["ERRORS"][$key] = GetMessage("REGISTER_FIELD_REQUIRED");
		}
		else
		{
			$_FILES["REGISTER_FILES_".$key]["MODULE_ID"] = "main";
			$arResult["VALUES"][$key] = $_FILES["REGISTER_FILES_".$key];
			if (in_array($key, $arResult["REQUIRED_FIELDS"]) && !is_uploaded_file($_FILES["REGISTER_FILES_".$key]["tmp_name"]))
				$arResult["ERRORS"][$key] = GetMessage("REGISTER_FIELD_REQUIRED");
		}
	}

	if(isset($_REQUEST["REGISTER"]["TIME_ZONE"]))
		$arResult["VALUES"]["TIME_ZONE"] = $_REQUEST["REGISTER"]["TIME_ZONE"];

	if(strlen($arResult["VALUES"]["EMAIL"]) > 0 && COption::GetOptionString("main", "new_user_email_uniq_check", "N") === "Y")
	{
		$res = CUser::GetList($b="", $o="", array("=EMAIL" => $arResult["VALUES"]["EMAIL"]));
		if($res->Fetch())
			$arResult["ERRORS"][] = GetMessage("REGISTER_USER_WITH_EMAIL_EXIST", array("#EMAIL#" => htmlspecialcharsbx($arResult["VALUES"]["EMAIL"])));
	}

	//this is a part of CheckFields() to show errors about user defined fields
	if (!$USER_FIELD_MANAGER->CheckFields("USER", 0, $arResult["VALUES"]))
	{
		$e = $APPLICATION->GetException();
		$arResult["ERRORS"][] = substr($e->GetString(), 0, -4); //cutting "<br>"
		$APPLICATION->ResetException();
	}
    
    //check company fields
    if (intval($_REQUEST["OFFICE"]) == 0)
    {
        $arResult["ERRORS"][] = GetMessage("REGISTER_FIELD_REQUIRED_OFFICE");
    }
    if (intval($_REQUEST["company_type"]) == 0)
    {
        $arResult["ERRORS"][] = GetMessage("REGISTER_FIELD_REQUIRED_company_type");
    }
    if (!strlen(trim($_REQUEST["company_name"])))
    {
        if (intval($_REQUEST["company_type"]) == 311)
        {
            $arResult["ERRORS"][] = GetMessage("REGISTER_FIELD_REQUIRED_company_name_fio");
        }
        else
        {
            $arResult["ERRORS"][] = GetMessage("REGISTER_FIELD_REQUIRED_company_name_office");
        }
    }
    if (!strlen(trim($_REQUEST["company_phone"])))
    {
        $arResult["ERRORS"][] = GetMessage("REGISTER_FIELD_REQUIRED_company_phone");
    }
    if (!strlen(trim($_REQUEST["company_city"])))
    {
        $arResult["ERRORS"][] = GetMessage("REGISTER_FIELD_REQUIRED_company_city");
    }
    if (!strlen(trim($_REQUEST["company_adress"])))
    {
        $arResult["ERRORS"][] = GetMessage("REGISTER_FIELD_REQUIRED_company_adress");
    }
    if ((!strlen(trim($_REQUEST["company_inn"]))) && (intval($_REQUEST["company_type"]) == 310))
    {
        $arResult["ERRORS"][] = GetMessage("REGISTER_FIELD_REQUIRED_company_inn");
    }
	if ($_REQUEST["form_checkbox_confirmation"] != 'Y')
	{
		$arResult["ERRORS"][] = GetMessage("REGISTER_FIELD_REQUIRED_form_checkbox_confirmation");
	}

	// check captcha
	if ($arResult["USE_CAPTCHA"] == "Y")
	{
        if (!strlen(trim($_POST['g-recaptcha-response'])))
        {
            $arResult["ERRORS"][] = GetMessage("REGISTER_WRONG_CAPTCHA");
        }
        /*
		if (!$APPLICATION->CaptchaCheckCode($_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]))
			$arResult["ERRORS"][] = GetMessage("REGISTER_WRONG_CAPTCHA");
        */
	}

	if(count($arResult["ERRORS"]) > 0)
	{
		if(COption::GetOptionString("main", "event_log_register_fail", "N") === "Y")
		{
			$arError = $arResult["ERRORS"];
			foreach($arError as $key => $error)
				if(intval($key) == 0 && $key !== 0) 
					$arError[$key] = str_replace("#FIELD_NAME#", '"'.$key.'"', $error);
			CEventLog::Log("SECURITY", "USER_REGISTER_FAIL", "main", false, implode("<br>", $arError));
		}
	}
	else // if there;s no any errors - create user
	{
		$bConfirmReq = (COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") == "Y" && $arResult["EMAIL_REQUIRED"]);

		$arResult['VALUES']["CHECKWORD"] = md5(CMain::GetServerUniqID().uniqid());
		$arResult['VALUES']["~CHECKWORD_TIME"] = $DB->CurrentTimeFunction();
		$arResult['VALUES']["ACTIVE"] = $bConfirmReq? "N": "Y";
		$arResult['VALUES']["CONFIRM_CODE"] = $bConfirmReq? randString(8): "";
		$arResult['VALUES']["LID"] = SITE_ID;

		$arResult['VALUES']["USER_IP"] = $_SERVER["REMOTE_ADDR"];
		$arResult['VALUES']["USER_HOST"] = @gethostbyaddr($_SERVER["REMOTE_ADDR"]);
		
		if($arResult["VALUES"]["AUTO_TIME_ZONE"] <> "Y" && $arResult["VALUES"]["AUTO_TIME_ZONE"] <> "N")
			$arResult["VALUES"]["AUTO_TIME_ZONE"] = "";

		$def_group = COption::GetOptionString("main", "new_user_registration_def_group", "");
		if($def_group != "")
			$arResult['VALUES']["GROUP_ID"] = explode(",", $def_group);

		$bOk = true;

		$USER_FIELD_MANAGER->EditFormAddFields("USER", $arResult["VALUES"]);

		$events = GetModuleEvents("main", "OnBeforeUserRegister", true);
		foreach($events as $arEvent)
		{
			if(ExecuteModuleEventEx($arEvent, array(&$arResult['VALUES'])) === false)
			{
				if($err = $APPLICATION->GetException())
					$arResult['ERRORS'][] = $err->GetString();

				$bOk = false;
				break;
			}
		}

		$ID = 0;
		$user = new CUser();
		if ($bOk)
		{
			$ID = $user->Add($arResult["VALUES"]);
		}

		if (intval($ID) > 0)
		{
            $arResult["UK"] = intval($_REQUEST["OFFICE"]);
            if (intval($_REQUEST["company_type"]) == 311)
            {
                $inn = $ID.rand(1000, 9999);
            }
            else
            {
                $inn = iconv('windows-1251','utf-8',trim($_REQUEST["company_inn"]));
            }
            $user_type = $arParams["TYPE_COMPANY"];
            $email_to_clients = GetSettingValue(720, false, $arResult["UK"]);
            $email_to_agents = GetSettingValue(721, false, $arResult["UK"]);
            $arEventFieldsDefault = array(
                'USER_ID' => $ID,
                'USER_LOGIN' => $arResult["VALUES"]['LOGIN'],
                'USER_NAME' => $arResult["VALUES"]['NAME'],
                'USER_LAST_NAME' => $arResult["VALUES"]['LAST_NAME'],
                'NAME' => NewQuotes($_REQUEST['company_name']),
                'EMAIL' => $arResult["VALUES"]['EMAIL'],
                'PHONES' => trim($_REQUEST['company_phone']),
                'CITY' => trim($_REQUEST['company_city']),
                'ADRESS' => trim($_REQUEST['company_adress']),
                'INN' => iconv('utf-8','windows-1251',$inn),
                'TYPE' => (intval($_REQUEST["company_type"]) == 311) ? '‘изическое лицо' : 'ёридическое лицо',
            );
            $arJson = array(
                'ID' => $ID,
                'NAME' => iconv('windows-1251','utf-8',NewQuotes($_REQUEST['company_name'])),
                'PROPERTY_EMAIL_VALUE' => iconv('windows-1251','utf-8',trim($arResult["VALUES"]['EMAIL'])),
                'PROPERTY_PHONES_VALUE' => iconv('windows-1251','utf-8',trim($_REQUEST["company_phone"])),
                'PROPERTY_CITY' => iconv('windows-1251','utf-8',trim($_REQUEST["company_city"])),
                'PROPERTY_ADRESS_VALUE' => iconv('windows-1251','utf-8',trim($_REQUEST["company_adress"])),
                'PROPERTY_INN_VALUE' => $inn,
                'PROPERTY_INN_REAL_VALUE' => $inn,
                'PROPERTY_TYPE_ENUM_ID' => $arParams["TYPE_COMPANY"],
                'INN_MORE_ONE' => $ID.rand(1000, 9999),
                'send' => 1
            );
            $json_string = json_encode($arJson);
			$UF_CONSENT = ($_REQUEST["form_checkbox_confirmation"] == 'Y') ? 1 : 0;
            $user = new CUser;
            $user->Update($ID, array("UF_COMPANY_JSON" => $json_string, "UF_CONSENT" => $UF_CONSENT));
            if (intval($_REQUEST["company_type"]) == 310)
            {
                $infofrominn = GetIDAgentByINN(trim($_REQUEST["company_inn"]), false, false, false, $arResult["UK"], true);
                $changeuser = false;
                if (count($infofrominn) == 1)
                {
                    $company_id = $infofrominn[0]["ID"];
                    $role = 5430579;
                    $changeuser = true;
                    $db_props = CIBlockElement::GetProperty(40, $company_id, array("sort" => "asc"), Array("CODE"=>"TYPE"));
                    if($ar_props = $db_props->Fetch())
                    {
                        $user_type = $ar_props["VALUE"];
                    }
                }
                elseif (count($infofrominn) == 0)
                {
                    $currentip = GetSettingValue(683, false, $arResult["UK"]);
                    $currentlink = GetSettingValue(704, false, $arResult["UK"]);
                    $login1c = GetSettingValue(705, false, $arResult["UK"]);
                    $pass1c = GetSettingValue(706, false, $arResult["UK"]);
                    if ((strlen(trim($currentip))) && (strlen(trim($currentlink))) && (strlen(trim($login1c))) && (strlen(trim($pass1c))))
                    {
                        $url = "http://".$currentip.$currentlink;
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
                            $client = new SoapClient($url, array('login' => $login1c, 'password' => $pass1c,'exceptions' => false));
                            $arParamsJson = array('INN' => $inn);
                            $result = $client->SetPrefix($arParamsJson);			
                            $mResult = $result->return;
                            $obj = json_decode($mResult, true);
                            $prefix = iconv('utf-8', 'windows-1251', trim($obj['Prefix_'.$inn]));
                            if (strlen($prefix))
                            {
                                $arChanges = array(
                                    243 => trim($arResult["VALUES"]['EMAIL']),
                                    265 => trim($_REQUEST["company_phone"]),
                                    187 => GetCityId(trim($_REQUEST["company_city"])),
                                    190 => trim($_REQUEST["company_adress"]),
                                    237 => $inn,
                                    211 => $user_type,
                                    227 => 0,
                                    304 => GetMaxIDIN(40, 3, false),
                                    377 => $prefix,
                                    379 => $arResult["VALUES"]['LAST_NAME'].' '.$arResult["VALUES"]['NAME'],
                                    467 => $arResult["UK"],
                                    670 => 280,
                                    681 => 5000
                                );
                                $el = new CIBlockElement;
                                $arLoadProductArray = array(
                                    "IBLOCK_SECTION_ID" => false,
                                    "IBLOCK_ID" => 40,
                                    "NAME" => NewQuotes($_REQUEST['company_name']),
                                    "ACTIVE" => "Y",
                                    "PROPERTY_VALUES" => $arChanges
                                );
                                if ($company_id = $el->Add($arLoadProductArray))
                                {
                                    $changeuser = true;
                                    $role = 4937477;
                                }
                                else
                                {
                                    if (intval($arParams["TEMPLATE_MODERATE_ID"]) > 0)
                                    {
                                        $arEventFieldsDefault['INFO'] = GetMessage("INFO_NEW_COMPANY");
                                        $arEventFieldsDefault["EMAIL_TO"] = ($user_type == 53) ? $email_to_agents : $email_to_clients;
                                        $event = new CEvent;
                                        $event->SendImmediate("NEWPARTNER_LK", "s5", $arEventFieldsDefault, "N", intval($arParams["TEMPLATE_MODERATE_ID"]));
                                    }
                                }
                                
                            }
                            else
                            {
                                if (intval($arParams["TEMPLATE_MODERATE_ID"]) > 0)
                                {
                                    if (intval($obj['ClientsCount']) > 1)
                                    {
                                        $arEventFieldsDefault["INFO"] = GetMessage("INFO_MORE_ONE_ONEC", array("#INN#" => trim($_REQUEST["company_inn"]), "#RANDINN#" => $arJson['INN_MORE_ONE']));
                                    }
                                    else
                                    {
                                        $arEventFieldsDefault["INFO"] = GetMessage("INFO_NOT_FOUND", array("#INN#" => trim($_REQUEST["company_inn"])));
                                    }
                                    $arEventFieldsDefault["EMAIL_TO"] = ($user_type == 53) ? $email_to_agents : $email_to_clients;
                                    $event = new CEvent;
                                    $event->SendImmediate("NEWPARTNER_LK", "s5", $arEventFieldsDefault, "N", intval($arParams["TEMPLATE_MODERATE_ID"]));
                                }
                            }
                        }
                    }
                    else
                    {
                        if (intval($arParams["TEMPLATE_MODERATE_ID"]) > 0)
                        {
                            $arEventFieldsDefault["INFO"] = GetMessage("INFO_NO_SETTINGS");
                            $arEventFieldsDefault["EMAIL_TO"] = ($user_type == 53) ? $email_to_agents : $email_to_clients;
                            $event = new CEvent;
                            $event->SendImmediate("NEWPARTNER_LK", "s5", $arEventFieldsDefault, "N", intval($arParams["TEMPLATE_MODERATE_ID"]));
                        }
                    }
                }
                else
                {
                    if (intval($arParams["TEMPLATE_MODERATE_ID"]) > 0)
                    {
                        $arEventFieldsDefault["INFO"] = GetMessage("INFO_MORE_ONE", array("#INN#" => trim($_REQUEST["company_inn"])));
                        $arEventFieldsDefault["EMAIL_TO"] = ($user_type == 53) ? $email_to_agents : $email_to_clients;
                        $event = new CEvent;
                        $event->SendImmediate("NEWPARTNER_LK", "s5", $arEventFieldsDefault, "N", intval($arParams["TEMPLATE_MODERATE_ID"]));
                    }
                }
                if ($changeuser)
                {
                    $arGroups = array(3);
                    switch ($user_type)
                    {
                        case 51:
                            $arGroups[] = 16;
                            $email_from = 'dms@newpartner.ru';
                            $site = 'dms.newpartner.ru';
                            $t_type = GetMessage("TYPE_51");
                            break;
                        case 52:
                            $arGroups[] = 17;
                            $email_from = 'dms@newpartner.ru';
                            $site = 'dms.newpartner.ru';
                            $t_type = GetMessage("TYPE_52");
                            break;
                        case 53:
                            $arGroups[] = 15;
                            $arGroups[] = 4;
                            $email_from = 'agent@newpartner.ru';
                            $site = 'agent.newpartner.ru';
                            $t_type = GetMessage("TYPE_53");
                            break;
                        case 222:
                            $email_from = 'dms@newpartner.ru';
                            $site = 'dms.newpartner.ru';
                            $t_type = GetMessage("TYPE_222");
                            break;
                        case 242:
                            $arGroups[] = 22;
                            $email_from_sets = GetSettingValue(723, false, $arResult["UK"]);
                            $email_from = strlen(trim($email_from_sets)) ? $email_from_sets : 'client@newpartner.ru';
                            $site = 'client.newpartner.ru';
                            $t_type = GetMessage("TYPE_242");
                            break;
                    } 
                    $user = new CUser;
                    $user->Update($ID, array("UF_COMPANY_RU_POST"=> $company_id, "UF_ROLE" => $role, "ACTIVE"=> "Y"));
                    CUser::SetUserGroup($ID, $arGroups);
                    if (intval($arParams["TEMPLATE_SUCCESS_ID"]) > 0)
                    {
                        $event = new CEvent;
                        $arEventFields = array(
                            'FROM' => $email_from,
                            'EMAIL' => $arResult["VALUES"]['EMAIL'],
                            'LINK' => $site,
                            'NAME' => $arResult["VALUES"]['NAME'].' '.$arResult["VALUES"]['LAST_NAME'],
                            'COMPANY' => NewQuotes($_REQUEST['company_name'])
                        );
				        $event->SendImmediate("NEWPARTNER_LK", "s5", $arEventFields, "N", intval($arParams["TEMPLATE_SUCCESS_ID"]));
                    }
                    if (intval($arParams["TEMPLATE_SUCCESS_ADMIN_ID"]) > 0)
                    {
						$email_to = ($user_type == 53) ? $email_to_agents : $email_to_clients;
                        $event = new CEvent;
                        $arEventFields = array(
                            'EMAIL' => $email_to,
							'LINK' => $site,
                            'NAME' => $arResult["VALUES"]['NAME'].' '.$arResult["VALUES"]['LAST_NAME'],
							'LOGIN' => $arResult["VALUES"]['LOGIN'].' ['.$ID.']',
							'EMAIL_USER' => $arResult["VALUES"]['EMAIL'],
							'TYPE' => (intval($_REQUEST["company_type"]) == 311) ? '‘изическое лицо' : 'ёридическое лицо',
                            'COMPANY' => NewQuotes($_REQUEST['company_name']),
							'PHONE' => trim($_REQUEST['company_phone']),
							'CITY' => trim($_REQUEST['company_city']),
							'ADRESS' => trim($_REQUEST['company_adress']),
							'INN' => $inn
                        );
				        $event->SendImmediate("NEWPARTNER_LK", "s5", $arEventFields, "N", intval($arParams["TEMPLATE_SUCCESS_ADMIN_ID"]));
                    }
                    $subscr = addAgentSubscription($user_type, $arResult["VALUES"]['EMAIL'], $ID);
                }
            }
            elseif (intval($_REQUEST["company_type"]) == 311)
            {
                if (intval($arParams["TEMPLATE_MODERATE_ID"]) > 0)
                {
                    $arEventFieldsDefault["EMAIL_TO"] = ($user_type == 53) ? $email_to_agents : $email_to_clients;
                    $event = new CEvent;
					$event->SendImmediate("NEWPARTNER_LK", "s5", $arEventFieldsDefault, "N", intval($arParams["TEMPLATE_MODERATE_ID"]));
                }
            }
            
			$register_done = true;

			// authorize user
			if ($arParams["AUTH"] == "Y" && $arResult["VALUES"]["ACTIVE"] == "Y")
			{
				if (!$arAuthResult = $USER->Login($arResult["VALUES"]["LOGIN"], $arResult["VALUES"]["PASSWORD"]))
					$arResult["ERRORS"][] = $arAuthResult;
			}

			$arResult['VALUES']["USER_ID"] = $ID;

			$arEventFields = $arResult['VALUES'];
			unset($arEventFields["PASSWORD"]);
			unset($arEventFields["CONFIRM_PASSWORD"]);

			$event = new CEvent;
			
			if (intval($arParams["TEMPLATE_ADMIN_ID"]) > 0)
			{
                $arEventFields["EMAIL_TO"] = ($user_type == 53) ? $email_to_agents : $email_to_clients;
				$event->SendImmediate("NEW_USER", SITE_ID, $arEventFields, "N", intval($arParams["TEMPLATE_ADMIN_ID"]));
			}
			else
			{
				$event->SendImmediate("NEW_USER", SITE_ID, $arEventFields);
			}
			
			
			if($bConfirmReq)
			{
				if (intval($arParams["TEMPLATE_ID"]) > 0)
				{
					$event->SendImmediate("NEW_USER_CONFIRM", SITE_ID, $arEventFields, "N", intval($arParams["TEMPLATE_ID"]));
				}
				else
				{
					$event->SendImmediate("NEW_USER_CONFIRM", SITE_ID, $arEventFields);
				}
			}
		}
		else
		{
			$arResult["ERRORS"][] = $user->LAST_ERROR;
		}

		if(count($arResult["ERRORS"]) <= 0)
		{
			if(COption::GetOptionString("main", "event_log_register", "N") === "Y")
				CEventLog::Log("SECURITY", "USER_REGISTER", "main", $ID);
		}
		else
		{
			if(COption::GetOptionString("main", "event_log_register_fail", "N") === "Y")
				CEventLog::Log("SECURITY", "USER_REGISTER_FAIL", "main", $ID, implode("<br>", $arResult["ERRORS"]));
		}

		$events = GetModuleEvents("main", "OnAfterUserRegister", true);
		foreach ($events as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$arResult['VALUES']));
	}
}

// if user is registered - redirect him to backurl or to success_page; currently added users too
if($register_done)
{
	if($arParams["USE_BACKURL"] == "Y" && $_REQUEST["backurl"] <> '')
		LocalRedirect($_REQUEST["backurl"]);
	elseif($arParams["SUCCESS_PAGE"] <> '')
		LocalRedirect($arParams["SUCCESS_PAGE"]);
}

$arResult["VALUES"] = htmlspecialcharsEx($arResult["VALUES"]);

// redefine required list - for better use in template
$arResult["REQUIRED_FIELDS_FLAGS"] = array();
foreach ($arResult["REQUIRED_FIELDS"] as $field)
	$arResult["REQUIRED_FIELDS_FLAGS"][$field] = "Y";

// check backurl existance
$arResult["BACKURL"] = htmlspecialcharsbx($_REQUEST["backurl"]);

// get countries list
if (in_array("PERSONAL_COUNTRY", $arResult["SHOW_FIELDS"]) || in_array("WORK_COUNTRY", $arResult["SHOW_FIELDS"])) 
	$arResult["COUNTRIES"] = GetCountryArray();

// get date format
if (in_array("PERSONAL_BIRTHDAY", $arResult["SHOW_FIELDS"])) 
	$arResult["DATE_FORMAT"] = CLang::GetDateFormat("SHORT");

// ********************* User properties ***************************************************
$arResult["USER_PROPERTIES"] = array("SHOW" => "N");
$arUserFields = $USER_FIELD_MANAGER->GetUserFields("USER", 0, LANGUAGE_ID);
if (is_array($arUserFields) && count($arUserFields) > 0)
{
	if (!is_array($arParams["USER_PROPERTY"]))
		$arParams["USER_PROPERTY"] = array($arParams["USER_PROPERTY"]);

	foreach ($arUserFields as $FIELD_NAME => $arUserField)
	{
		if (!in_array($FIELD_NAME, $arParams["USER_PROPERTY"]) && $arUserField["MANDATORY"] != "Y")
			continue;

		$arUserField["EDIT_FORM_LABEL"] = strLen($arUserField["EDIT_FORM_LABEL"]) > 0 ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"];
		$arUserField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arUserField["EDIT_FORM_LABEL"]);
		$arUserField["~EDIT_FORM_LABEL"] = $arUserField["EDIT_FORM_LABEL"];
		$arResult["USER_PROPERTIES"]["DATA"][$FIELD_NAME] = $arUserField;
	}
}
if (!empty($arResult["USER_PROPERTIES"]["DATA"]))
{
	$arResult["USER_PROPERTIES"]["SHOW"] = "Y";
	$arResult["bVarsFromForm"] = (count($arResult['ERRORS']) <= 0) ? false : true;
}
// ******************** /User properties ***************************************************

// initialize captcha
/*
if ($arResult["USE_CAPTCHA"] == "Y")
	$arResult["CAPTCHA_CODE"] = htmlspecialcharsbx($APPLICATION->CaptchaGetCode());
*/

// set title
if ($arParams["SET_TITLE"] == "Y") 
	$APPLICATION->SetTitle(GetMessage("REGISTER_DEFAULT_TITLE"));

//time zones
$arResult["TIME_ZONE_ENABLED"] = CTimeZone::Enabled();
if($arResult["TIME_ZONE_ENABLED"])
	$arResult["TIME_ZONE_LIST"] = CTimeZone::GetZones();

$arResult["SECURE_AUTH"] = false;
if(!CMain::IsHTTPS() && COption::GetOptionString('main', 'use_encrypted_auth', 'N') == 'Y')
{
	$sec = new CRsaSecurity();
	if(($arKeys = $sec->LoadKeys()))
	{
		$sec->SetKeys($arKeys);
		$sec->AddToForm('regform', array('REGISTER[PASSWORD]', 'REGISTER[CONFIRM_PASSWORD]'));
		$arResult["SECURE_AUTH"] = true;
	}
}

// all done
$this->IncludeComponentTemplate();
