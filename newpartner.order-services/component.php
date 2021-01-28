<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

$arResult['OPEN'] = true;

if ($arParams['MODE'] == '1c')
{
    $arLog = array();
    foreach ($_POST as $k => $v)
    {
        $arLog['POST '.$k] = $v;
    }
    if ($_POST['type'] == 'CallingTheCourierAccepted')
    {
        $login1c = GetSettingValue(705);
        $pass1c = GetSettingValue(706);
        if ((strlen($_POST['login'])) && (strlen($_POST['pass'])))
        {
            if (($_POST['login'] == $login1c) && ($_POST['pass'] == $pass1c))
            {
                $arResponseUtf = json_decode($_POST['Response'], true);
                $arResponse = arFromUtfToWin($arResponseUtf);
                $order_id = intval($arResponse["ID"]);
                $order_id_res = CIBlockElement::GetByID($order_id);
                if($ar_order_id_res = $order_id_res->GetNext())
                {
                    $arHistory = array();
                    $db_props = CIBlockElement::GetProperty(87, $order_id, array("sort" => "asc"), Array("CODE"=>"STATE_HISTORY"));
                    if($ar_props = $db_props->Fetch())
                    {
                        $historyyetjson = $ar_props["VALUE"];
                        if (strlen($historyyetjson))
                        {
                            $arHistoryUtfYet = json_decode(htmlspecialchars_decode($historyyetjson,ENT_COMPAT),true);
                            $arHistory = arFromUtfToWin($arHistoryUtfYet);
                        }
                    }
                    if ($arResponse['Accepted'])
                    {
                        if (strlen(trim($arResponse['Courier'])))
                        {
                            //TODO [x]назначен курьер
                            $state_id = 319;
                            $state_descr = 'Назначен курьер';
                            $state_comment = trim($arResponse['Courier']);
                        }
                        else
                        {
                            //TODO [x]заявка принята в 1с
                            $state_id = 318;
                            $state_descr = 'Принята';
                            $state_comment = '';
                        }
                    }
                    else
                    {
                        //TODO [x]заявка отменена
                        $state_id = 321;
                        $state_descr = 'Отклонена';
                        $state_comment = '';
                    }
                    $arHistory[] = array('date' => date('d.m.Y H:i:s'), 'status' => $state_id, 'status_descr' => $state_descr, 'comment' => $state_comment);
                    $arHistoryUTF = convArrayToUTF($arHistory);
                    CIBlockElement::SetPropertyValuesEx($order_id, 87, array("STATE" => $state_id,"STATE_HISTORY"=>json_encode($arHistoryUTF), "NUMBER" => $arResponse['Number']));
                    $arResult["INFO"][] = 'Информация по заявке на вызов курьера №'.$arResponse['Number'].' обновлена: '.$state_descr.' '.$state_comment;
                    //TODO [x]Уведомление в мессенджере создателю заявки
                    if (CModule::IncludeModule('im'))
                    {
                        $arMessageFields = array(
                            "TO_USER_ID" => $ar_order_id_res['CREATED_BY'],
                            "FROM_USER_ID" => false,
                            "NOTIFY_MODULE" => "im",
                            "NOTIFY_TYPE" => IM_NOTIFY_SYSTEM,
                            "NOTIFY_MESSAGE" => 'Информация по заявке на вызов курьера №'.$arResponse['Number'].' обновлена: '.$state_descr.' '.$state_comment
                        );
                        CIMNotify::Add($arMessageFields);
                    }
                    
                    
                }
                else
                {
                     $arResult["ERRORS"][] = 'Не найдена заявка с ID '.$arResponse["ID"];
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
        'INFO' => $arResult['INFO']
    );
    foreach ($arResult['RESULTS'] as $k => $v)
    {
        foreach ($v as $kk => $vv)
        {
            $arLog[$k.' '.$kk] = $vv;
        }
    }
    AddToLogs('callingCourier',$arLog);
    $arResult['RESULTS'] = convArrayToUTF($arResult['RESULTS']);
    $arResult['RES_JSON'] = json_encode($arResult['RESULTS']);
}
else
{
    $arResult["USER_ID"] = $USER->GetID();
    $rsUser = CUser::GetByID($arResult["USER_ID"]);
    $arUser = $rsUser->Fetch();
    $agent_id = intval($arUser["UF_COMPANY_RU_POST"]);
    $arResult['USER_BRANCH'] = false;
    $arResult['ADMIN_AGENT'] = false;
    $arResult['ADD_AGENT_EMAIL'] = '';
    $arResult['LAST_ORDERS'] = array();
    if ($agent_id > 0)
    {
        $db_props = CIBlockElement::GetProperty(40, $agent_id, array("sort" => "asc"), Array("ID"=>211));
        if($ar_props = $db_props->Fetch())
        {
            $agent_type = $ar_props["VALUE"];
            if (in_array($agent_type, array(51, 242)))
            {
                $arResult['AGENT'] = GetCompany($agent_id);
                if ($agent_type == 51)
                {
                    $arResult['ADMIN_AGENT'] = true;
                    $arResult["UK"] = $arResult["AGENT"]["ID"];
                }
                else
                {
                    $arResult["UK"] = $arResult["AGENT"]["PROPERTY_UK_VALUE"];
                }
                $arResult['EMAIL_CALLCOURIER'] = '';
                $arResult['EMAIL_SUPPLIES'] = '';
                $arResult['ZADARMA'] = 0;
                $arResult['ZADARMA_FROM'] = '';
                if (intval($arResult["UK"]) > 0)
                {
                    $arResult['EMAIL_CALLCOURIER'] = GetSettingValue(709, false, $arResult["UK"]);
                    $arResult['EMAIL_SUPPLIES'] = GetSettingValue(711, false, $arResult["UK"]);
                    $arResult['ZADARMA'] = GetSettingValue(707, false, $arResult["UK"]);
                    $arResult['ZADARMA_FROM'] = GetSettingValue(708, false, $arResult["UK"]);
                    $currentip = GetSettingValue(683, false, $arResult['UK']);
                    $currentport = intval(GetSettingValue(761, false, $arResult["UK"]));
                    $currentlink = GetSettingValue(704, false, $arResult['UK']);
                    $login1c = GetSettingValue(705, false, $arResult['UK']);
                    $pass1c = GetSettingValue(706, false, $arResult['UK']);
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
                }
                else
                {
                    //TODO Привязка клиентов к агенту, указание e-mail агента
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

    $arResult["ERR_FIELDS"] = array();

    $arSettings = array();
    $settingsJson = $arResult['AGENT']['PROPERTY_ACCOUNT_LK_SETTINGS_VALUE']['TEXT'];
    if (strlen($settingsJson))
    {
        $arSettings = json_decode(htmlspecialcharsBack($settingsJson), true);
    }
    $arResult['USER_SETTINGS'] = $arSettings[$arResult["USER_ID"]];
    $current_N = date('N', strtotime("+1 day"));
    if (intval($arResult['USER_SETTINGS']['DATE_CALLCOURIER']) == 2) {
        if ($current_N == 5)
            $date_callcourier = date('d.m.Y', strtotime("+3 day"));
        elseif ($current_N == 6)
            $date_callcourier = date('d.m.Y', strtotime("+2 day"));
        else 
            $date_callcourier = date('d.m.Y', strtotime("+1 day"));
    }
    else {
        if ($current_N == 5) {
            if (date('H') >= 14) {
                $date_callcourier = date('d.m.Y', strtotime("+3 day"));
            }
            else {
                $date_callcourier = date('d.m.Y');
            }
        }
        if ($current_N == 6) {
            $date_callcourier = date('d.m.Y', strtotime("+2 day"));
        }
        elseif ($current_N == 7) {
            $date_callcourier = date('d.m.Y', strtotime("+1 day"));
        }
        else {
            if (date('H') >= 14) {
                $date_callcourier = date('d.m.Y', strtotime("+1 day"));
            }
            else {
                $date_callcourier = date('d.m.Y');
            }
        }
    }

    $arResult['DEAULTS'] = array(
        'date' => $date_callcourier,
        'time_from' => '10:00',
        'time_to' => '18:00',
        'adress' => ($arResult['USER_BRANCH']) ? $arResult['BRANCH_INFO']["PROPERTY_ADRESS_VALUE"] : $arResult['AGENT']['PROPERTY_ADRESS_VALUE'],
        'city' =>  ($arResult['USER_BRANCH']) ? $arResult['BRANCH_INFO']["PROPERTY_CITY"] : $arResult['AGENT']['PROPERTY_CITY'],
        'name' => $USER->GetFullName(),
        'phone' => $arUser['PERSONAL_PHONE'],
        'weight' => '0,2'
    );

    if (isset($_POST['call']))
    {
        if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
        {
            $_POST = array();
            $arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
        }
        else
        {
            $_SESSION[$_POST["key_session"]] = $_POST["rand"];

            $arResult['DEAULTS'] = array(
                'date' => $_POST['date'],
                'time_from' => $_POST['time_from'],
                'time_to' => $_POST['time_to'],
                'adress' => trim($_POST['adress']),
                'city' =>  trim($_POST['city']),
                'name' => trim($_POST['name']),
                'phone' => trim($_POST['phone']),
                'weight' => trim($_POST['weight'])
            );

            if ((!strlen($_POST['date'])) || (!strlen($_POST['time_from'])) || (!strlen($_POST['time_to'])))
            {
                $arResult["ERR_FIELDS"]["date_time"] = 'has-error';
            }
            if (!strlen($_POST['city']))
            {
                $arResult["ERR_FIELDS"]["city"] = 'has-error';
            }
            else
            {
                $city = GetCityId(trim($_POST['city']));
                if ($city == 0)
                {
                    $arResult["ERR_FIELDS"]["city"] = 'has-error';
                }
            }
            if (!strlen($_POST['adress']))
            {
                $arResult["ERR_FIELDS"]["adress"] = 'has-error';
            }
            if (!strlen($_POST['name']))
            {
                $arResult["ERR_FIELDS"]["name"] = 'has-error';
            }
            if (!strlen($_POST['phone']))
            {
                $arResult["ERR_FIELDS"]["phone"] = 'has-error';
            }
            $weight = floatval(str_replace(',','.',$_POST['weight']));
            $size = array(
                floatval(str_replace(',','.',$_POST['size'][0])),
                floatval(str_replace(',','.',$_POST['size'][1])),
                floatval(str_replace(',','.',$_POST['size'][2]))
            );
            if ($weight <= 0)
            {
                $arResult["ERR_FIELDS"]["weight"] = 'has-error';
            }
            if (count($arResult["ERR_FIELDS"]) == 0)
            {
				
				// печатать [$arResult]
				// печатать [Post] 
				// хочу получить номер заявки!?
				//	file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/cuerier-1.txt', print_r($arFields, true), FILE_APPEND);
				//	file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/cuerier-2.txt', print_r($_POST, true), FILE_APPEND);
				//
				
                $id_in = GetMaxIDIN(87, 7);
                $arHistory = array(array('date' => date('d.m.Y H:i:s'), 'status' => 315, 'status_descr' => 'Оформлена', 'comment' => ''));
                $arHistoryUTF = convArrayToUTF($arHistory);
                $el = new CIBlockElement;
                $arLoadProductArray = Array(
                    "MODIFIED_BY" => $USER->GetID(), 
                    "IBLOCK_SECTION_ID" => false,
                    "IBLOCK_ID" => 87,
                    "PROPERTY_VALUES" => array(
                        611 => $id_in,
                        612 => $agent_id,
                        664 => $arResult['USER_BRANCH'],
                        613 => array(
                            $_POST['date'].' '.$_POST['time_from'].':00',
                            $_POST['date'].' '.$_POST['time_to'].':00'
                        ),
                        614 => $city,
                        615 => trim($_POST['adress']),
                        616 => trim($_POST['name']),
                        617 => trim($_POST['phone']),
                        618 => $weight,
                        619 => $size,
                        725 => $_POST['payment_type'],
                        620 => trim($_POST['comment']),
                        712 => implode(', ',array($arResult['EMAIL_CALLCOURIER'], $arResult['ADD_AGENT_EMAIL'])),
                        726 => 315,
                        727 => json_encode($arHistoryUTF),
						771 => "test (2)"
                    ),
                    "NAME" => 'Вызов курьера №'.$id_in,
                    "ACTIVE" => "Y"
                );
                if ($z_id = $el->Add($arLoadProductArray))
                {
                    $arEventFields = array(
                        "COMPANY_F" => ($arResult['USER_BRANCH']) ? $arResult['AGENT']['NAME'].', филиал '.$arResult['BRANCH_INFO']['NAME'] : $arResult['AGENT']['NAME'], 
                        "NUMBER" => $id_in,
                        "COMPANY" => $arResult['AGENT']['NAME'],
                        "BRANCH" => ($arResult['USER_BRANCH']) ? 'Филиал: <strong>'.$arResult['BRANCH_INFO']['NAME'].'</strong><br />' : '',
                        "DATE_TIME" => $_POST['date'].' с '.$_POST['time_from'].' до '.$_POST['time_to'],
                        "CITY" => $_POST['city'],
                        "ADRESS" => trim($_POST['adress']),
                        "CONTACT" => trim($_POST['name']),
                        "PHONE" => trim($_POST['phone']),
                        "WEIGHT" => $weight,
                        "SIZE_1" => $size[0],
                        "SIZE_2" => $size[1],
                        "SIZE_3" => $size[2],
                        "COMMENT" => trim($_POST['comment']),
                        "AGENT_EMAIL" => $arResult['ADD_AGENT_EMAIL'],
                        'UK_EMAIL' => $arResult['EMAIL_CALLCOURIER']
                    );
                    $event = new CEvent;
                    $event->Send("NEWPARTNER_LK", "S5", $arEventFields, "N", 220);
                    $arHistory[] = array('date' => date('d.m.Y H:i:s'), 'status' => 316, 'status_descr' => 'Отправлена на почту', 'comment' => '');
                    $arHistoryUTF = convArrayToUTF($arHistory);
                    CIBlockElement::SetPropertyValuesEx($z_id, 87, array("STATE"=>316,"STATE_HISTORY"=>json_encode($arHistoryUTF)));
					//echo "<pre>";print_r($arResult);echo "</pre>";
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
							//define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"]."/log_sms.txt");
							//AddMessage2Log($answer);
                        }
                    }
                    //NOTE Отправка уведомлений в 1с
                //   if ($arResult["UK"] == 5873349)
                 //  {
                        if ((strlen(trim($currentip))) && (strlen(trim($currentlink))) && (strlen(trim($login1c))) && (strlen(trim($pass1c))))
                        {
                            if ($currentport > 0) {
                                $url = "http://".$currentip.':'.$currentport.$currentlink;
                            }
                            else {
                                $url = "http://".$currentip.$currentlink;
                            }
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
                                if ($currentport > 0) {
                                    $client = new SoapClient($url, array('login' => $login1c, 'password' => $pass1c, 'proxy_host' => $currentip, 'proxy_port' => $currentport, 'exceptions' => false));
                                }
                                else {
                                    $client = new SoapClient($url, array('login' => $login1c, 'password' => $pass1c,'exceptions' => false));
                                }
                                $payment_type = 'Наличные';
                                switch (intval($_POST['payment_type']))
                                {
                                    case 312:
                                        $payment_type = 'Наличные';
                                        break;
                                    case 313:
                                        $payment_type = 'Безналичные';
                                        break;
                                    case 314:
                                        $payment_type = 'Карта банка';
                                        break;
                                }
                                $arJs = array(
                                    'IDWEB' => $z_id,
                                    'INN' => $arResult['AGENT']['PROPERTY_INN_VALUE'],
                                    'DATE' => date('Y-m-d'),
                                    'COMPANY_SENDER' => ($arResult['USER_BRANCH']) ? $arResult['AGENT']['NAME'].', филиал '.$arResult['BRANCH_INFO']['NAME'] : $arResult['AGENT']['NAME'],
                                    'NAME_SENDER' => trim($_POST['name']),
                                    'PHONE_SENDER' => trim($_POST['phone']),
                                    'ADRESS_SENDER' => trim($_POST['adress']),
                                    'INDEX_SENDER' => '',
                                    'ID_CITY_SENDER' => $city,
                                    'DELIVERY_TYPE' => 'Стандарт',
                                    'PAYMENT_TYPE' => $payment_type,
                                    'DELIVERY_PAYER' => 'Отправитель',
                                    'DELIVERY_CONDITION' => 'ПоАдресу',
                                    'DATE_TAKE_FROM' => substr($_POST['date'],6,4).'-'.substr($_POST['date'],3,2).'-'.substr($_POST['date'],0,2).' '.$_POST['time_from'].':00',
                                    'DATE_TAKE_TO' => substr($_POST['date'],6,4).'-'.substr($_POST['date'],3,2).'-'.substr($_POST['date'],0,2).' '.$_POST['time_to'].':00',
                                    'INSTRUCTIONS' => trim($_POST['comment'])
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
                                $arLogTitle = array('Title' => 'Вызов курьера из раздела "Услуги"');
                                $arLogResult = array('Response' => $mResult, 'status' => $arRes[0]['status'], 'comment' => $arRes[0]['comment']);
                                $arLog = array_merge($arLogTitle,$arJs,$arLogResult);
                                AddToLogs('callingCourier',$arLog);
                            }
                        }
               //     }
                    $_POST = array();
                    $arResult["MESSAGE"][] = "Вызов курьера №".$id_in." успешно зарегистрирован";
                }
            }
        }
    }

    if (isset($_POST['order']))
    {
        if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
        {
            $_POST = array();
            $arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
        }
        else
        {
            $_SESSION[$_POST["key_session"]] = $_POST["rand"];
            if (count($_POST['what']) > 0)
            {
                $arEventFields = array(
                    "COMPANY" => $arResult['AGENT']['NAME'],
                    "BRANCH" => ($arResult['USER_BRANCH']) ? 'Филиал: <strong>'.$arResult['BRANCH_INFO']['NAME'].'</strong><br />' : '',
                    "CITY" => ($arResult['USER_BRANCH']) ? $arResult['BRANCH_INFO']['PROPERTY_CITY'] : $arResult['AGENT']['PROPERTY_CITY'],
                    "ADRESS" => ($arResult['USER_BRANCH']) ? $arResult['BRANCH_INFO']['PROPERTY_ADRESS_VALUE'] : $arResult['AGENT']['PROPERTY_ADRESS_VALUE'],
                    'COMPANY_PHONE' => ($arResult['USER_BRANCH']) ? $arResult['BRANCH_INFO']['PROPERTY_PHONE_VALUE'] : $arResult['AGENT']['PROPERTY_PHONES_VALUE'],
                    'COMPANY_EMAIL' => ($arResult['USER_BRANCH']) ? $arResult['BRANCH_INFO']['PROPERTY_EMAIL_VALUE'] : $arResult['AGENT']['PROPERTY_EMAIL_VALUE'],
                    'FIO' => $arUser['NAME'].' '.$arUser['LAST_NAME'],
                    'PHONE' => $arUser['PERSONAL_PHONE'],
                    'EMAIL' => $arUser['EMAIL'],
                    "WHAT" => implode(', ', $_POST['what']),
                    "COMMENT" => trim($_POST['comment']),
                    "AGENT_EMAIL" => $arResult['ADD_AGENT_EMAIL'],
                    'UK_EMAIL' => $arResult['EMAIL_SUPPLIES']
                );
                $_POST = array();
                $event = new CEvent;
                $event->Send("NEWPARTNER_LK", "S5", $arEventFields, "N", 200);
                $arResult["MESSAGE"][] = "Заказ расходных материалов успешно оформлен";
            }
            else
            {
                $arResult["ERRORS"][] = 'Не выбраны материалы';
            }
        }
    }


    $filter = array("IBLOCK_ID" => 87, "PROPERTY_CLIENT" => $agent_id);
    if ($arResult['ADMIN_AGENT'])
    {
        $arclientsids = array();
        $LIST_OF_CLIENTS = AvailableClients(false, false, $agent_id);
        foreach ($LIST_OF_CLIENTS as $k => $v)
        {
            $arclientsids[] = $k;
        }
        $filter['PROPERTY_CLIENT'] = $arclientsids;
    }
    $res = CIBlockElement::GetList(
        array("id" => "desc"), 
        $filter,
        false, 
        array("nTopCount" => 20), 
        array(
            "ID","PROPERTY_CLIENT","PROPERTY_CLIENT.NAME", "PROPERTY_BRANCH.NAME", "PROPERTY_DATE", "PROPERTY_CITY.NAME", "PROPERTY_ADRESS", "PROPERTY_CONTACT","PROPERTY_PHONE","PROPERTY_WEIGHT","PROPERTY_SIZE","PROPERTY_COMMENT", "PROPERTY_STATE", "PROPERTY_STATE_HISTORY"
        )
    );
    while ($ob = $res->GetNextElement())
    {
        $a = $ob->GetFields();
        $d_start = $a['PROPERTY_DATE_VALUE'][0];
        $ard_start = explode(' ',$d_start);
        $d_end = $a['PROPERTY_DATE_VALUE'][1];
        $ard_end = explode(' ',$d_end);
        if ($ard_start[0] == $ard_end[0])
        {
            $a['DATE_ORDER'] = $ard_start[0];
            if ($ard_start[1] == $ard_end[1])
            {
                $a['DATE_ORDER'] .= ' '.substr($ard_start[1], 0, 5);
            }
            else
            {
                $a['DATE_ORDER'] .= ' '.substr($ard_start[1], 0, 5).' - '.substr($ard_end[1], 0, 5);
            }
        }
        else
        {
            $a['DATE_ORDER'] = substr($a['PROPERTY_DATE_VALUE'][0], 0, 16).' '.substr($a['PROPERTY_DATE_VALUE'][1], 0, 16);
        }
        $a['HISTORY'] = false;
        if (strlen($a['PROPERTY_STATE_HISTORY_VALUE']))
        {
            $history = json_decode(htmlspecialchars_decode($a['PROPERTY_STATE_HISTORY_VALUE'],ENT_COMPAT),true);
            $a['HISTORY'] = arFromUtfToWin($history);
        }
        $arResult['LAST_ORDERS'][] = $a;
    }
}
$this->IncludeComponentTemplate();
?>