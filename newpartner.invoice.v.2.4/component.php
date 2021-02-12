<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
    die();
}

use Bitrix\Main\Localization\Loc;

//use Myclass\DumpClass;
ini_set("soap.wsdl_cache_enabled", "0" );
ini_set("default_socket_timeout", "300");

include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");
require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/funcpdf.php");
require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/Invoice.php");
require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/NPCalc/NPCalc.php");


define('ID_SUKHOI', 41478141); // сухой
define('ID_TEST',   9528186);   // тестовый клиент
define('ID_ABSOLUT', 56103010);  // абсолют страхование
define('ID_VKOM1', 56280706);    // вымпелком1
define('ID_VKOM2', 56389269);    // вымпелком2
define('ID_VKOM3', 56389270);    // вымпелком3
define('ID_VKOM4', 56389272);    // вымпелком4
define('ID_SKOLKOVO', 52254529); // сколково
define('ID_UK_NP', 2197189);  // новый партнер
define('ID_NATIMBIO', 23522997);  // Нацимбио

$vcom = false;
$vcomm = false;

/*if($USER->isAdmin()){
    dump($_GET);
    dump($_POST);
}*/
/**
 * возмем наши поля потому что иногда их нет в 1С
 *
 * @param string $id - инфоблока
 * @return array       массив описаний
 */
function getFieldPackDescription($id){
    $res = CIBlockElement::GetList(
        ["id" => "desc"],
        ["IBLOCK_ID" => 83, "ID" => $id],
        false,false, ["ID","NAME","PROPERTY_PACK_DESCRIPTION"]);

    while($obj = $res->GetNextElement()){
        $req = $obj->GetFields();
        return $req['PROPERTY_PACK_DESCRIPTION_VALUE'];
    }
    return 0;
}
/**
 * найти все похожие имена ( без постфикса)
 * найти ID и дату создания элемента с минимальным постфиксом
 * по текущему номеру элемента
 *
 * @param string $r_NAME - имя накладной //$r['PROPERTY_INNER_NUMBER_CLAIM_VALUE']
 * @return array         - массив с минимальным постфиксом
 */
function  getRootInvoice($r_NAME){
    $nameWithoutPrefix = preg_replace ("/(.*)-(.*)$/", "$1", $r_NAME);
    $resTv = CIBlockElement::GetList(
        ["id" => "desc"],
        // не name а доп.поле.!
        ["IBLOCK_ID"=>83, "PROPERTY_INNER_NUMBER_CLAIM"=>"%".$nameWithoutPrefix."%"],
        false, false, ["ID", 'NAME', 'PROPERTY_INNER_NUMBER_CLAIM', 'DATE_ACTIVE_FROM' , 'DATE_CREATE']);
    $min = 99999999999999;
    while($obTv = $resTv->GetNextElement()){
        $m = $obTv->GetFields();
        // не name а доп.поле.!
        $minResult = preg_replace ("/(.*)-(.*)$/", "$2", $m['PROPERTY_INNER_NUMBER_CLAIM_VALUE']);
        if ($minResult < $min){
            $min = $minResult;
            $arrResult = [$m['ID'], $m['PROPERTY_INNER_NUMBER_CLAIM_VALUE'], $m['DATE_CREATE']];
        }
    };
    return [$min , $arrResult];
}

$modes = [
    'list',
    'add',
    'print_notification',
    'print',
    'printsukhoi',
    'invoice',
    'invoice_modal',
    'invoice1c_modal',
    'invoice1c_print',
    'invoice1c_printsukhoi',
    'invoice_tracking',
    'edit',
    '1c',
    'pdf',
    'close',
    'list_xls',
    'reportv_xls',
    'acceptance',
    'upload',
    'prints',
    'prints_mini',
    'delone',

];
// TODO: [x]Новый режим для просмотра накладной непосредственно из 1с
// TODO: [x]Новый режим для печати накладной непосредственно из 1с
$arResult['MODE'] = $modes[0];  //  list по умолчанию
if ((strlen($arParams["MODE"])) && (in_array($arParams["MODE"], $modes)))
{
    $arResult['MODE'] = $arParams["MODE"];
}
else
{
    if ((strlen(trim($_GET["mode"]))) && (in_array(trim($_GET["mode"]), $modes)))
    {
        $arResult['MODE'] = trim($_GET["mode"]);   /* возвращает mode из get параметров */

    }
}
/* оставаться в форме оформления накладной */
if($_GET['invoice_made']){
    $arResult['invoice_made'] = htmlspecialchars(trim($_GET['invoice_made']));
    $arResult['MODE'] = 'add';
}

$arResult['HIDE_EVENTS'] = ['Задержка рейса','Задержка авиарейса', 'Засыл'];
$arParams['TYPE'] = ((int)$arParams['TYPE'] > 0) ? (int)$arParams['TYPE'] : 242;
/*  -x if begin */
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
    $agent_id = (int)$arUser["UF_COMPANY_RU_POST"];  // id компании текущего usera
    // AddToLogs('uk_info', ['data'=>$agent_id]);
    /*  -a if begin */

    if ($agent_id > 0)
    {
        // получили текущего КЛИЕНТА с его информацией
        $arResult['CURRENT_CLIENT_ADDON'] = GetCompany($_SESSION['CURRENT_CLIENT']);
        // -------------------------

        $arResult['AGENT'] = GetCompany($agent_id);

        if (in_array($arResult['AGENT']["PROPERTY_TYPE_ENUM_ID"], [51, $arParams['TYPE']]))
        {
            /*  -3 if begin */
            if ($arResult['AGENT']["PROPERTY_TYPE_ENUM_ID"] == 51)
            {
                $arResult['ADMIN_AGENT'] = true;
                $arResult['UK'] = $arResult['AGENT']["ID"];
            }
            else
            {
                $arResult['UK'] = $arResult['AGENT']["PROPERTY_UK_VALUE"];
            }

            //AddToLogs('uk_info', ['data'=> $arResult['UK'] ]);
            /*  -2 if begin */
            /* тут вложенность связана с типом ошибок (тот же паттерн)*/
            if ((int)$arResult['UK'] > 0)
            {
                $currentip = GetSettingValue(683, false, $arResult['UK']);
                $currentport = (int)GetSettingValue(761, false, $arResult["UK"]);
                $currentlink = GetSettingValue(704, false, $arResult['UK']);
                $login1c = GetSettingValue(705, false, $arResult['UK']);
                $pass1c = GetSettingValue(706, false, $arResult['UK']);
                $arResult['ZADARMA'] = GetSettingValue(707, false, $arResult['UK']);
                $arResult['ZADARMA_FROM'] = GetSettingValue(708, false, $arResult['UK']);
                $arResult['EMAIL_CALLCOURIER'] = GetSettingValue(709, false, $arResult['UK']);
                $arResult['EMAIL_NEWINVOICES'] = GetSettingValue(710, false, $arResult['UK']);
                //AddToLogs('uk_info', ['data'=> [$currentip, $currentport, $currentlink, $login1c, $pass1c ] ]);
                if ((strlen(trim($currentip))) && (strlen(trim($currentlink))) && (strlen(trim($login1c))) && (strlen(trim($pass1c))))
                {
                    if ($currentport > 0) {
                        $url = "http://".$currentip.':'.$currentport.$currentlink;
                    }
                    else {
                        $url = "http://".$currentip.$currentlink;
                    }

                    $curl = curl_init();
                    curl_setopt_array($curl, [
                        CURLOPT_URL => $url,
                        CURLOPT_HEADER => true,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_NOBODY => true,
                        CURLOPT_TIMEOUT => 10
                    ]);
                    $header = explode("\n", curl_exec($curl));
                    curl_close($curl);
                    /*  0 if begin */
                    if (strlen(trim($header[0])))
                    {
                        // AddToLogs('uk_info', ['data'=> $header[0] ]);
                        /*  1 if */
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
                                    if ((int)$_SESSION['CURRENT_BRANCH'] == 0)
                                    {
                                        LocalRedirect('/choice-branch/');
                                    }
                                    else
                                    {
                                        $arResult['USER_IN_BRANCH'] = true;
                                        $arResult['CURRENT_BRANCH'] = (int)$_SESSION['CURRENT_BRANCH'];
                                    }
                                }
                                else
                                {
                                    if ((int)$arUser["UF_BRANCH"])
                                    {
                                        $arResult['USER_IN_BRANCH'] = true;
                                        $arResult['CURRENT_BRANCH'] = (int)$arUser["UF_BRANCH"];
                                    }
                                }
                                //NOTE Определяем тип работы клиента с филиалами
                                //NOTE Если работаем с филиалом
                                if ($arResult['USER_IN_BRANCH'])
                                {
                                    $arResult['BRANCH_INFO'] = GetBranch($arResult['CURRENT_BRANCH'], $agent_id);
                                    if((int)$arResult['BRANCH_INFO']['PROPERTY_BY_AGENT_VALUE'] > 0)
                                    {
                                        $db_props = CIBlockElement::GetProperty(40, (int)$arResult['BRANCH_INFO']['PROPERTY_BY_AGENT_VALUE'], array("sort" => "asc"), Array("CODE"=>"EMAIL"));
                                        if($ar_props = $db_props->Fetch())
                                        {
                                            if(strlen(trim($ar_props["VALUE"])))
                                            {
                                                $arResult['ADD_AGENT_EMAIL'] = trim($ar_props["VALUE"]).', ';
                                            }
                                        }
                                    }
                                    $db_props_2 = CIBlockElement::GetProperty(89, $arResult['CURRENT_BRANCH'], ["sort" => "asc"], ["ID"=>644]);
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
                                            $db_props = CIBlockElement::GetProperty(40, $ag, ["sort" => "asc"], ["CODE"=>"EMAIL"]);
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
                                $arContracts = [];
                                $res = CIBlockElement::GetList(
                                    ["id" => "desc"],
                                    ["IBLOCK_ID" => 88, "PROPERTY_CLIENT" => $agent_id],
                                    false,
                                    false,
                                    [
                                        "ID"
                                    ]
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

                        /*  2 if */
                        // login password---
                        //  *
                        $a = ["url" => $url, "login1c" => $login1c, 'password' => $pass1c, 'proxy_host' => $currentip, 'proxy_port' => $currentport];

                        // AddToLogs('uk_info', ['data'=> $a ]);
                        if ($currentport > 0) {
                            $client = new SoapClient($url, ['login' => $login1c,
                                'password' => $pass1c,
                                'proxy_host' => $currentip,
                                'proxy_port' => $currentport,
                                'exceptions' => false]);
                        }
                        else {
                            $client = new SoapClient($url, ['login' => $login1c,
                                'password' => $pass1c,
                                'exceptions' => false]);
                        }

                        // AddToLogs('uk_info', ['data'=> $client ]);

                        /*  3.1 if */
                        if (is_array($_SESSION['MESSAGE']))
                        {
                            $arResult["MESSAGE"] = $_SESSION['MESSAGE'];
                            $_SESSION['MESSAGE'] = false;
                        }
                        /*  3.2 if */
                        if (is_array($_SESSION['ERRORS']))
                        {
                            $arResult["ERRORS"] = $_SESSION['ERRORS'];
                            $_SESSION['ERRORS'] = false;
                        }
                        /*  3.3 if */
                        if (is_array($_SESSION['WARNINGS']))
                        {
                            $arResult["WARNINGS"] = $_SESSION['WARNINGS'];
                            $_SESSION['WARNINGS'] = false;
                        }
                    }
                    else
                    {
                        $error = curl_exec($curl);

                        $arResult['MODE'] = 'close';
                        $arResult["ERRORS"][] =  '<!-- '.$error. '--><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> <strong>Пользователи Личного Кабинета!</strong> В настоящий момент работа Личного Кабинета приостановлена в связи с аварией на оборудовании. Приносим извинения за доставленные неудобства.';

                    }
                    /*  0 if end */
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
    /*  -a if end */
}

/*  -x if end */
/*  -------------------------------- */

/*  -0.0 if begin   TODO *убрать*  неправильно убрать этот  IF */
if ($arResult['MODE'] != 'close')
{
    $arResult["OPEN"] = true;

    AddToLogs('uk_info', ['data'=> $arResult["OPEN"] ]);

    if ($arResult['MODE'] === 'list')
    {
        if($USER->isAdmin()){
            $start = microtime(true);
            AddToLogs('test_logs', ['time_start' => $start, 'mess' => 'Начало выполнения скрипта']);
        }
        $arResult['LIST_TO_DATE'] = date('d.m.Y');
        $prevdate = strtotime('-2 days');
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
                elseif ((int)$_GET['client'] == 0)
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

        $db_props = CIBlockElement::GetProperty(40, $arResult['CURRENT_CLIENT'],
            ["sort" => "asc"], ["CODE"=>"INN"]);
        if($ar_props = $db_props->Fetch())
        {
            $arResult['CURRENT_CLIENT_INN'] = $ar_props["VALUE"];
        }

        /* получить настройки клиента из 1с GetClientInfo */
        if($arResult['UK'] == ID_UK_NP){
            //$client = soap_inc();
            $arParamsJsonIdClient = [
                'INN' =>  $arResult['CURRENT_CLIENT_INN'],
            ];
            $result = $client->GetClientInfo($arParamsJsonIdClient);
            $mResult = $result->return;
            $res = json_decode($mResult, true);
            $res=arFromUtfToWin($res);
            if($res['IndividualPrice'] === '1'){
                $arResult['IndividualPrice'] = false;
            }else{
                $arResult['IndividualPrice'] = true; //  считать по общему прайсу
            }

        }

        /* end настройки */

        $arResult['LIST_OF_BRANCHES'] = false;
        $arResult['LIMITS_OF_BRANCHES'] = false;
        if ($arParams['TYPE'] == 242)
        {
            $res_3 = CIBlockElement::GetList(
                ["NAME" => "asc"],
                ["IBLOCK_ID" => 89, "PROPERTY_CLIENT" =>  $arResult['CURRENT_CLIENT'], "ACTIVE" => "Y"],
                false,
                false,
                ["ID","NAME","PROPERTY_CITY.NAME", "PROPERTY_LIMIT"]
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
                    $_SESSION['CURRENT_BRANCH'] = (int)$_GET['branch'];
                    $arResult['CURRENT_BRANCH'] = (int)$_GET['branch'];
                }
                if ($arResult['ADMIN_AGENT'] && ((int)$arResult['CURRENT_BRANCH'] > 0))
                {
                    $arResult['BRANCH_INFO'] = GetBranch($arResult['CURRENT_BRANCH'], $arResult['CURRENT_CLIENT']);
                }
            }
        }
        if (isset($_POST['delete']))
        {
            if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
            {
                $_POST = [];
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
                        $res = $el->Update($id, ["ACTIVE"=>"N"]);
                    }
                    AddToLogs('InvoicesDelete', ['IDs' => implode(', ',$_POST['ids'])]);
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
                $_POST = [];
                $arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
            }
            else
            {
                $_SESSION[$_POST["key_session"]] = $_POST["rand"];
                if (count($_POST['ids']) > 0)
                {
                    $arCells = [];
                    $arCells[] = [
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
                    ];
                    $arManifestTo1c = [
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
                        "Delivery" => []
                    ];
                    //TO_DELIVER_BEFORE_DATE 772
                    $res = CIBlockElement::GetList(
                        ["id" => "desc"],
                        ["IBLOCK_ID" => 83, "ID" => $_POST['ids']],
                        false,
                        false,
                        [
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
                            "PROPERTY_TO_DELIVER_BEFORE_DATE",
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
                        ]
                    );
                    while ($ob = $res->GetNextElement())
                    {
                        $reqv = $ob->GetFields();
                        $reqv["PROPERTY_OB_WEIGHT"] = 0;
                        $reqv["PROPERTY_Dimensions"] = [];
                        if (strlen($reqv['PROPERTY_PACK_DESCRIPTION_VALUE']))
                        {
                            $reqv['PACK_DESCR'] = json_decode(htmlspecialcharsBack($reqv['PROPERTY_PACK_DESCRIPTION_VALUE']), true);
                            foreach ($reqv['PACK_DESCR'] as $k => $str)
                            {
                                $reqv["PROPERTY_OB_WEIGHT"] = $reqv["PROPERTY_OB_WEIGHT"] + $str['gabweight'];
                                $reqv["PROPERTY_Dimensions"][] = [
                                    "WEIGHT" => ((float)$str['weight'] > 0) ? (float)$str['weight'] : 0,
                                    "SIZE_1" => ((float)$str["size"][0] > 0) ? (float)$str["size"][0] : 0,
                                    "SIZE_2" => ((float)$str["size"][1] > 0) ? (float)$str["size"][1] : 0,
                                    "SIZE_3" => ((float)$str["size"][2] > 0) ? (float)$str["size"][2] : 0,
                                    "PLACES" => (int)$str["place"],
                                    "NAME" => iconv('utf-8','windows-1251',$str['name'])
                                ];
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
                            $reqv["PROPERTY_Dimensions"][] = [
                                "WEIGHT" => ((float)$reqv['PROPERTY_WEIGHT_VALUE'] > 0) ? (float)$reqv['PROPERTY_WEIGHT_VALUE'] : 0,
                                "SIZE_1" => ((float)$reqv['PROPERTY_DIMENSIONS_VALUE'][0] > 0) ? (float)$reqv['PROPERTY_DIMENSIONS_VALUE'][0] : 0,
                                "SIZE_2" => ((float)$reqv['PROPERTY_DIMENSIONS_VALUE'][1] > 0) ? (float)$reqv['PROPERTY_DIMENSIONS_VALUE'][1] : 0,
                                "SIZE_3" => ((float)$reqv['PROPERTY_DIMENSIONS_VALUE'][2] > 0) ? (float)$reqv['PROPERTY_DIMENSIONS_VALUE'][2] : 0,
                                "PLACES" => (int)$reqv['PROPERTY_PLACES_VALUE'],
                                "NAME" => ''
                            ];
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
                        if ((int)$reqv['PROPERTY_BRANCH_VALUE'] > 0)
                        {
                            $db_props = CIBlockElement::GetProperty(89, $reqv['PROPERTY_BRANCH_VALUE'], array("sort" => "asc"), array("CODE"=>"IN_1C_CODE"));
                            if($ar_props = $db_props->Fetch())
                            {
                                $reqv['BRANCH_CODE'] = $ar_props["VALUE"];
                            }
                        }

                        //NOTE Определение значения "чей заказ"
                        $WHOSE_ORDER_ID = false;
                        if ((int)$reqv['PROPERTY_WHOSE_ORDER_VALUE'] > 0)
                        {
                            $db_props = CIBlockElement::GetProperty(40, (int)$reqv['PROPERTY_WHOSE_ORDER_VALUE'], array("sort" => "asc"), array("CODE"=>"INN"));
                            if($ar_props = $db_props->Fetch())
                            {
                                if (strlen(trim($ar_props["VALUE"])))
                                {
                                    $WHOSE_ORDER_ID = $ar_props["VALUE"];
                                }

                            }
                        }
                        //TO_DELIVER_BEFORE_DATE 772
                        //$reqv['PROPERTY_TO_DELIVER_BEFORE_DATE_VALUE'],
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

                        // дописываем "выслать до"
                        $c1deliveryFields = "";
                        if ($reqv['PROPERTY_TO_DELIVER_BEFORE_DATE_VALUE']!=''){
                            $c1deliveryFields = $reqv['PROPERTY_INSTRUCTIONS_VALUE']['TEXT'];
                        } else {
                            $c1deliveryFields = $reqv['PROPERTY_INSTRUCTIONS_VALUE']['TEXT'];
                        };
                        //------------------------

                        $arDelivery = [
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
                            "INSTRUCTIONS" => $c1deliveryFields,
                            // TODO [x]В поле TYPE передавать 0 или 1
                            "TYPE" => ($reqv['PROPERTY_TYPE_PACK_ENUM_ID'] == 247) ? 0 : 1,
                            "Dimensions" => $reqv['PROPERTY_Dimensions'],
                            'ID' => $reqv['ID'],
                            'ID_BRANCH' => $reqv['BRANCH_CODE'],
                            // 'Goods' => $reqv['PACK_GOODS']
                        ];
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
                    $boldFont = [
                        'font'=> [
                            'bold'=>true
                        ]
                    ];
                    $small = [
                        'font'=> [
                            'size' => 8
                        ],
                    ];
                    $center = [
                        'alignment'=> [
                            'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical'=>PHPExcel_Style_Alignment::VERTICAL_TOP
                        ]
                    ];
                    $right = [
                        'alignment'=> [
                            'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                            'vertical'=>PHPExcel_Style_Alignment::VERTICAL_TOP
                        ]
                    ];
                    $table = [
                        'alignment'=> [
                            'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            'vertical'=>PHPExcel_Style_Alignment::VERTICAL_TOP
                        ]
                    ];
                    $head_style = [
                        'font' => [
                            'bold' => true,
                        ],
                        'alignment' => [
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        ],
                        'fill' => [
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => [
                                'argb' => 'FFFFF4E9',
                            ],
                        ],
                    ];
                    $footer_style = [
                        'fill' => [
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => [
                                'argb' => 'FFE9FEFF',
                            ],
                        ],
                    ];
                    $styleArray = [
                        'borders' => [
                            'allborders' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => ['argb' => 'FF000000'],
                            ],
                        ],
                    ];
                    $i = 1;
                    $arJ = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA'];
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


                    $_SERVER_DOCUMENT_ROOT_1 = '/var/www/admin/www/client.newpartner.ru/';


                    include_once "PHPExcel/Writer/Excel5.php";
                    $objWriter = new PHPExcel_Writer_Excel5($pExcel);
                    $path = "/files/overheads/".date('Y-m-d').'_'.$arResult['CURRENT_CLIENT'].'_'.time().".xls";
                    $objWriter->save($_SERVER_DOCUMENT_ROOT_1.$path);

                    $arFile = CFile::MakeFileArray($_SERVER_DOCUMENT_ROOT_1.$path);
                    $fid = CFile::SaveFile($arFile, "files_overheads");
                    // почтовое событие
                    $sob_id = CEvent::Send(
                        "NEWPARTNER_LK",
                        "s5",
                        [
                            "EMAIL_FROM" => $arUser['EMAIL'],
                            "COMPANY_FROM" => '<a href="http://client.newpartner.ru/index.php?ChangeClient=Y&client='.$arResult['CURRENT_CLIENT'].'" target="_blank">'.$arResult['LIST_OF_CLIENTS'][$arResult['CURRENT_CLIENT']].'</a>',
                            "CREATOR" => $arUser['LAST_NAME'].' '.$arUser['NAME'],
                            'AGENT_EMAIL' => $arResult['ADD_AGENT_EMAIL'],
                            'UK_EMAIL' => $arResult['EMAIL_NEWINVOICES'],
                            'WHO_CREATE' => ($arParams['TYPE'] == 53) ? 'Агент' : 'Клиент'
                        ],
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
                    $result = $client->SetManifest(['ListOfDocs' => json_encode($arManifestTo1cUTF)]);
                    //TODO [x]Заменить функцию на SetManifest, изменив входящие значения
                    $mResult = $result->return;
                    AddToLogs('InvoicesSendAnswer', ['Answer' => $mResult]);
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
                                CIBlockElement::SetPropertyValuesEx($r, 83, [572 => 258,
                                    573 => date('d.m.Y H:i:s'), 732 => $arResult["USER_ID"]]);
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

        $arResult['REQUESTS'] = [];
        $arResult['ARCHIVE'] = [];
        // TODO [x]Разедлить массивы для накладных с сайта (неотправленных) и из 1с. С сайта выводить ссылки с ID, из 1с - по номеру накладной

        if ((int)$arResult['CURRENT_CLIENT'] > 0)
        {
            $filter = ["IBLOCK_ID" => 83, "PROPERTY_CREATOR" => (int)$arResult['CURRENT_CLIENT'], "ACTIVE" => "Y",
                "PROPERTY_STATE" => 257];  // статус оформлено


            $filter[">=DATE_CREATE"] = $arResult['LIST_FROM_DATE'].' 00:00:00';
            $filter["<=DATE_CREATE"] = $arResult['LIST_TO_DATE'].' 23:59:59';



            if ((int)$arResult['CURRENT_BRANCH'] > 0)
            {
                $filter["PROPERTY_BRANCH"] = (int)$arResult['CURRENT_BRANCH'];
            }

            $res = CIBlockElement::GetList(
                ['created' => 'desc'],
                $filter,
                false,
                false,
                [
                    "ID",
                    "NAME",
                    "DATE_CREATE",
                    "PROPERTY_COMPANY_SENDER",
                    "PROPERTY_CITY_SENDER.NAME",
                    "PROPERTY_COMPANY_RECIPIENT",
                    "PROPERTY_NAME_RECIPIENT",
                    "PROPERTY_NAME_SENDER",
                    "PROPERTY_CITY_RECIPIENT.NAME",
                    "PROPERTY_PLACES",
                    "PROPERTY_WEIGHT",
                    "PROPERTY_DIMENSIONS",
                    "PROPERTY_STATE",
                    "PROPERTY_STATE_DESCR",
                    "PROPERTY_RATE",
                    "PROPERTY_BRANCH.NAME",
                    "PROPERTY_PACK_DESCRIPTION",
                    "PROPERTY_WHOSE_ORDER.NAME",
                    "PROPERTY_PAYS",
                    "PROPERTY_INNER_NUMBER_CLAIM",
                    "PROPERTY_WHOSE_ORDER",
                    "PROPERTY_CALLING_COURIER",
                    "PROPERTY_WITH_RETURN",
                    "PROPERTY_CENTER_EXPENSES",
                    "PROPERTY_SUMM_DEV"
                ]
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
                    $a['PACK_DESCR'] = json_decode(htmlspecialcharsback($a['PROPERTY_PACK_DESCRIPTION_VALUE']), true);
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
                // получили минимальный номер в серии
                $a['PROPERTY_MINIMAL_NUMBER_SERIES'] = getRootInvoice($a["PROPERTY_INNER_NUMBER_CLAIM_VALUE"]);
                if($a['PROPERTY_CENTER_EXPENSES_VALUE'] > 0){
                    $carrrequest = CIBlockElement::GetByID((int)$a['PROPERTY_CENTER_EXPENSES_VALUE']);
                    if($arres = $carrrequest->GetNext()){
                        $a['CENTER_EXPENSES_NAME'] = $arres['NAME'];
                    }
                }

                $arResult['REQUESTS'][] = $a;

            }
            if($arResult['CURRENT_CLIENT'] == ID_TEST){
                //dump($arResult['REQUESTS']);
                //AddToLogs('test_logss', ["REQUESTS" => $arResult['REQUESTS']]);
            }


            if ($arParams['TYPE'] == 53)
            {
                $arParamsJson = [
                    'INN' => $arResult['CURRENT_CLIENT_INN'],
                    'StartDate' => $arResult['LIST_FROM_DATE_FOR_1C'],
                    'EndDate' => $arResult['LIST_TO_DATE_FOR_1C'],
                    'NumPage' => 0,
                    'DocsToPage' => 10000,
                    'Type' => 1
                ];
                $result = $client->GetDocsListAgent($arParamsJson);
            }
            else
            {
                   //  Заготовка Не удалять!!!
                   //  разбить получение данных из 1с на несколько запросов
                  /* if ($USER->isAdmin()) {
                       $segment = 4;  //  количество дней в одном запросе
                       $period_start = $arResult['LIST_FROM_DATE'] . ' 00:00:00';
                       $period_end = $arResult['LIST_TO_DATE'] . ' 23:59:59';
                       $non_ch = false;
                       $arr_periods = [];  //  массив периодов
                       // количество дней в периоде
                       $count_days_for_request = countDaysBetweenDates($filter[">=DATE_CREATE"], $filter["<=DATE_CREATE"]) + 1;
                       if ($count_days_for_request >= $segment) {
                           if ($count_days_for_request % $segment == 0) {
                               // количество запросов
                               $count_segments = (int)($count_days_for_request / $segment);
                           } else {
                               $count_segments = (int)($count_days_for_request / $segment) + 1;
                               $non_ch = true;
                           }
                       } else {
                           $count_segments = 1;
                       }

                       for ($i = 0; $i < $count_segments; $i++) {
                           if ($i == 0) {
                               $p_s = $period_start;
                               $p_e = date('d.m.Y H:i:s', (strtotime($p_s) + $segment * 24 * 60 * 60) - 1);
                               $arr_periods[] = [
                                   $p_s,
                                   $p_e,
                                   date('Y-m-d', strtotime($p_s)),
                                   date('Y-m-d', strtotime($p_e))
                               ];
                           } else {
                               $p_s = date('d.m.Y H:i:s', strtotime($arr_periods[$i - 1][1]) + 1);
                               if ($i + 1 != $count_segments) {
                                   $p_e = date('d.m.Y H:i:s', (strtotime($p_s) + $segment * 24 * 60 * 60) - 1);
                               } else {
                                   if ($non_ch) {
                                       $p_e = date('d.m.Y H:i:s', (strtotime($p_s) + ($segment - 1) * 24 * 60 * 60 - 1));
                                   } else {
                                       $p_e = date('d.m.Y H:i:s', (strtotime($p_s) + $segment * 24 * 60 * 60) - 1);
                                   }

                               }
                               $arr_periods[] = [
                                   $p_s,
                                   $p_e,
                                   date('Y-m-d', strtotime($p_s)),
                                   date('Y-m-d', strtotime($p_e))
                               ];
                           }
                       }
                       $arParamsJson = [];
                       $result_request = [];
                       $INN = trim($arResult['CURRENT_CLIENT_INN']);
                       $BranchID = ($arResult['CURRENT_BRANCH'] > 0) ? iconv('windows-1251', 'utf-8', $arResult['BRANCH_INFO']['PROPERTY_IN_1C_CODE_VALUE']) : '';
                       $BranchPrefix = ($arResult['CURRENT_BRANCH'] > 0) ? iconv('windows-1251', 'utf-8', $arResult['BRANCH_INFO']['PROPERTY_IN_1C_PREFIX_VALUE']) : '';
                       krsort($arr_periods);

                       foreach ($arr_periods as $period) {
                           $StartDate = $period[2];
                           $EndDate = $period[3];
                           $arParamsJson = [
                               'INN' => $INN,
                               'BranchID' => $BranchID,
                               'BranchPrefix' => $BranchPrefix,
                               'StartDate' => $StartDate,
                               'EndDate' => $EndDate,
                               'NumPage' => 0,
                               'DocsToPage' => 10000
                           ];
                           $__rr = $client->GetDocsListClient($arParamsJson);
                           $__mr = $__rr->return;
                           $__ob = arFromUtfToWin(json_decode($__mr, true));
                           $result_request[] = $__ob;
                       }

                       $ind_nakl = 0;
                       foreach ($result_request as $key => $value) {
                           foreach ($value['Docs'] as $d) {
                               $path_scan_docs = [];
                               if (isset($d['FilesPath']) && !empty($d['FilesPath'])) {
                                   $path_scan_docs = $d['FilesPath'];
                               }

                               $a = [
                                   'ID' => ((int)$d['ID'] > 0) ? (int)$d['ID'] : 'naklid_' . $ind_nakl,
                                   'ID_SITE' => ((int)$d['ID'] > 0) ? (int)$d['ID'] : 0,
                                   'NAME' => $d['NumDoc'],
                                   'DATE_CREATE' => substr($d['DateDoc'], 8, 2) . '.' . substr($d['DateDoc'], 5, 2) . '.' . substr($d['DateDoc'], 0, 4),
                                   'PROPERTY_STATE_ENUM_ID' => 258,
                                   'ColorRow' => ($arParams['TYPE'] == 53) ? 'warning' : '',
                                   'state_icon' => '',
                                   'PROPERTY_BRANCH_NAME' => $d['ZakazName'],
                                   'PROPERTY_CITY_SENDER_NAME' => '',
                                   'PROPERTY_CITY_RECIPIENT_NAME' => '',
                                   'PROPERTY_COMPANY_SENDER_VALUE' => $d['CompanySender'],
                                   'PROPERTY_COMPANY_RECIPIENT_VALUE' => $d['CompanyRecipient'],
                                   'PROPERTY_PLACES_VALUE' => 0,
                                   'PROPERTY_WEIGHT_VALUE' => 0,
                                   'PROPERTY_OB_WEIGHT' => 0,
                                   'PROPERTY_RATE_VALUE' => (float)str_replace(',', '.', $d['Tarif']),
                                   'PROPERTY_STATE_VALUE' => 'Принято',
                                   'PROPERTY_STATE_DESCR_VALUE' => '',
                                   'PROPERTY_NAME_RECIPIENT_VALUE' => $d['NameRecipient'],
                                   'PROPERTY_NAME_SENDER_VALUE' => $d['NameSender'],
                                   'start_date' => strlen($d['Date_Create']) ? substr($d['Date_Create'], 8, 2) . '.' . substr($d['Date_Create'], 5, 2) . '.' . substr($d['Date_Create'], 0, 4) : $d['DateDoc'],
                                   'ZakazName' => $d['ZakazName'],
                                   'Manager' => $d['Manager'],
                                   'PROPERTY_INNER_NUMBER_CLAIM_VALUE' => $d['InternalNumber'],
                                   'SCAN_DOCS_PATH' => $path_scan_docs,
                                   'Tarif' => $d['Tarif'],
                               ];
                               if ($arResult['CURRENT_CLIENT'] == ID_ABSOLUT) {
                                   if (preg_match('/[а-я]+/i', $d['CENTER_EXPENSES'])) {
                                       $a['center_cost'] = $d['CENTER_EXPENSES'];
                                   }

                               }
                               if ((int)$d['CitySender'] > 0) {
                                   $rr = CIBlockElement::GetByID((int)$d['CitySender']);
                                   if ($ar_rr = $rr->GetNext()) {
                                       $a['PROPERTY_CITY_SENDER_NAME'] = $ar_rr['NAME'];
                                   }
                               }

                               if ((int)$d['CityRecipient'] > 0) {
                                   $rr = CIBlockElement::GetByID((int)$d['CityRecipient']);
                                   if ($ar_rr = $rr->GetNext()) {
                                       $a['PROPERTY_CITY_RECIPIENT_NAME'] = $ar_rr['NAME'];
                                   }
                               }

                               foreach ($d['Dimensions'] as $dimensions) {
                                   $a['PROPERTY_PLACES_VALUE'] = $a['PROPERTY_PLACES_VALUE'] + $dimensions['Places'];
                                   $a['PROPERTY_WEIGHT_VALUE'] += (float)str_replace(',', '.', $dimensions['Weight']);
                                   if ((float)str_replace(',', '.', $dimensions['WeightV']) > 0) {
                                       $a['PROPERTY_OB_WEIGHT'] = (float)str_replace(',', '.', $dimensions['WeightV']);
                                   } else {
                                       $a['PROPERTY_OB_WEIGHT'] = $a['PROPERTY_OB_WEIGHT'] + ((float)str_replace(',', '.',
                                                   $dimensions['Size_1']) * (float)str_replace(',', '.',
                                                   $dimensions['Size_2']) * (float)str_replace(',', '.',
                                                   $dimensions['Size_3'])) / $arResult['CURRENT_CLIENT_COEFFICIENT_VW'];
                                   }
                               }
                               foreach ($d['Events'] as $ev) {
                                   $a['PROPERTY_STATE_VALUE'] = $ev['Event'];
                                   $a['PROPERTY_STATE_DESCR_VALUE'] = $ev['InfoEvent'];
                                   $a['Events'][] = [
                                       'Date' => $ev['DateEvent'] . '&nbsp;' . substr($ev['TimeEvent'], 0, 5),
                                       'Event' => $ev['Event'],
                                       'InfoEvent' => $ev['InfoEvent']
                                   ];
                               }
                               if (($a['ID'] == 0) || (!strlen($a['PROPERTY_COMPANY_SENDER_VALUE'])) || (!strlen($a['PROPERTY_COMPANY_RECIPIENT_VALUE']))) {
                                   $filter = ["IBLOCK_ID" => 83, "PROPERTY_CREATOR" => (int)$arResult['CURRENT_CLIENT'], "ACTIVE" => "Y"];
                                   if ((int)$a['ID'] > 0) {
                                       $filter['ID'] = (int)$a['ID'];
                                   }
                                   else
                                   {
                                       $filter['NAME'] = $a["NAME"];
                                   }
                                   if ((int)$arResult['CURRENT_BRANCH'] > 0) {
                                       $filter["PROPERTY_BRANCH"] = (int)$arResult['CURRENT_BRANCH'];
                                   }
                                   $res = CIBlockElement::GetList(["id" => "desc"], $filter, false, ["nTopCount" => 1],
                                       ["ID", "PROPERTY_COMPANY_SENDER", "PROPERTY_COMPANY_RECIPIENT"]);
                                   if ($ob = $res->GetNextElement()) {
                                       $arFields = $ob->GetFields();
                                       $a['ID'] = ($a['ID'] == 0) ? $arFields['ID'] : $a['ID'];
                                       $a['PROPERTY_COMPANY_SENDER_VALUE'] = (!strlen($a['PROPERTY_COMPANY_SENDER_VALUE'])) ? $arFields['PROPERTY_COMPANY_SENDER_VALUE'] : $a['PROPERTY_COMPANY_SENDER_VALUE'];
                                       $a['PROPERTY_COMPANY_RECIPIENT_VALUE'] = (!strlen($a['PROPERTY_COMPANY_RECIPIENT_VALUE'])) ? $arFields['PROPERTY_COMPANY_RECIPIENT_VALUE'] : $a['PROPERTY_COMPANY_RECIPIENT_VALUE'];

                                   }
                               }
                               if ($agent_type == 242) {
                                   $a['state_icon'] = '<span class="glyphicon glyphicon-send" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр трекинга"></span>';
                                   $a['state_text'] = 'Доставляется';
                               } else {
                                   $a['state_icon'] = '<span class="glyphicon glyphicon-new-window" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр трекинга"></span>';
                                   $a['state_text'] = $a['PROPERTY_STATE_VALUE'];
                               }
                               switch ($a['PROPERTY_STATE_VALUE']) {
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
                       }

                      dump(count($arResult['ARCHIVE']));

                   }*/


                    //dump($result_request);

                $arParamsJson = [
                    'INN' => trim($arResult['CURRENT_CLIENT_INN']),
                    'BranchID' => ($arResult['CURRENT_BRANCH'] > 0) ? iconv('windows-1251','utf-8',$arResult['BRANCH_INFO']['PROPERTY_IN_1C_CODE_VALUE']) : '',
                    'BranchPrefix' => ($arResult['CURRENT_BRANCH'] > 0) ? iconv('windows-1251','utf-8',$arResult['BRANCH_INFO']['PROPERTY_IN_1C_PREFIX_VALUE']) : '',
                    'StartDate' => $arResult['LIST_FROM_DATE_FOR_1C'],
                    'EndDate' => $arResult['LIST_TO_DATE_FOR_1C'],
                    'NumPage' => 0,
                    'DocsToPage' => 10000
                ];

                $result = $client->GetDocsListClient($arParamsJson);

            }

            $mResult = $result->return;
            $obj = json_decode($mResult, true);
            $obj = arFromUtfToWin($obj);

            $ind_nakl = 0;

         //   AddToLogs('ObjDocs', ['component-1475'=> $obj['Docs']]);
            $arResult['ARCHIVE'] = [];
            foreach ($obj['Docs'] as $d)
            {
                $path_scan_docs = [];
                if(isset($d['FilesPath']) && !empty($d['FilesPath'])){
                    $path_scan_docs = $d['FilesPath'];
                }

                $a = [
                    'ID' => ((int)$d['ID'] > 0) ? (int)$d['ID'] : 'naklid_'.$ind_nakl,
                    'ID_SITE' => ((int)$d['ID'] > 0) ? (int)$d['ID'] : 0,
                    'NAME' => $d['NumDoc'],
                    'DATE_CREATE' =>substr($d['DateDoc'],8,2).'.'.substr($d['DateDoc'],5,2).'.'.substr($d['DateDoc'],0,4),
                    'PROPERTY_STATE_ENUM_ID' => 258,
                    'ColorRow' => ($arParams['TYPE'] == 53) ? 'warning' : '',
                    'state_icon' => '',
                    'PROPERTY_BRANCH_NAME' =>  $d['ZakazName'],
                    'PROPERTY_CITY_SENDER_NAME' => '',
                    'PROPERTY_CITY_RECIPIENT_NAME' => '',
                    'PROPERTY_COMPANY_SENDER_VALUE' => $d['CompanySender'],
                    'PROPERTY_COMPANY_RECIPIENT_VALUE' =>  $d['CompanyRecipient'],
                    'PROPERTY_PLACES_VALUE' => 0,
                    'PROPERTY_WEIGHT_VALUE' => 0,
                    'PROPERTY_OB_WEIGHT' => 0,
                    'PROPERTY_RATE_VALUE' => (float)str_replace(',', '.', $d['Tarif']),
                    'PROPERTY_STATE_DESCR_VALUE' => '',
                    'PROPERTY_NAME_RECIPIENT_VALUE' =>  $d['NameRecipient'],
                    'PROPERTY_NAME_SENDER_VALUE' =>  $d['NameSender'],
                    'start_date'=> strlen($d['Date_Create']) ? substr($d['Date_Create'],8,2).'.'.substr($d['Date_Create'],5,2).'.'.substr($d['Date_Create'],0,4) : $d['DateDoc'],
                    'ZakazName' =>  $d['ZakazName'],
                    'Manager' => $d['Manager'],
                    'PROPERTY_INNER_NUMBER_CLAIM_VALUE' => $d['InternalNumber'],
                    'SCAN_DOCS_PATH' => $path_scan_docs,
                    'Tarif' => $d['Tarif'],
                ];

                if($arResult['CURRENT_CLIENT'] == ID_ABSOLUT){
                    if(preg_match('/[а-я]+/i', $d['CENTER_EXPENSES'])){
                        $a['center_cost'] = $d['CENTER_EXPENSES'];
                    }

                }

                if ((int)$d['CitySender'] > 0)
                {
                    $rr = CIBlockElement::GetByID((int)$d['CitySender']);
                    if($ar_rr = $rr->GetNext())
                    {
                        $a['PROPERTY_CITY_SENDER_NAME'] = $ar_rr['NAME'];
                    }
                }

                if ((int)$d['CityRecipient'] > 0)
                {
                    $rr = CIBlockElement::GetByID((int)$d['CityRecipient']);
                    if($ar_rr = $rr->GetNext())
                    {
                        $a['PROPERTY_CITY_RECIPIENT_NAME'] = $ar_rr['NAME'];
                    }
                }

                foreach ($d['Dimensions'] as $dimensions)
                {
                    $a['PROPERTY_PLACES_VALUE'] = $a['PROPERTY_PLACES_VALUE'] + $dimensions['Places'];
                    $a['PROPERTY_WEIGHT_VALUE'] += (float)str_replace(',', '.', $dimensions['Weight']);
                    if ((float)str_replace(',', '.', $dimensions['WeightV']) > 0)
                    {
                        $a['PROPERTY_OB_WEIGHT'] = (float)str_replace(',', '.', $dimensions['WeightV']);
                    }
                    else
                    {
                        $a['PROPERTY_OB_WEIGHT'] = $a['PROPERTY_OB_WEIGHT'] + ((float)str_replace(',', '.',
                                    $dimensions['Size_1']) * (float)str_replace(',', '.',
                                    $dimensions['Size_2']) * (float)str_replace(',', '.',
                                    $dimensions['Size_3']))/$arResult['CURRENT_CLIENT_COEFFICIENT_VW'];
                    }
                }
                foreach ($d['Events'] as $ev)
                {
                    $a['PROPERTY_STATE_VALUE'] = $ev['Event'];
                    $a['PROPERTY_STATE_DATE_VALUE'] = $ev['DateEvent'];
                    $a['PROPERTY_STATE_DESCR_VALUE'] = $ev['InfoEvent'];
                    $a['Events'][] = [
                        'Date' => $ev['DateEvent'].'&nbsp;'.substr($ev['TimeEvent'],0,5),
                        'Event' => $ev['Event'],
                        'InfoEvent' => $ev['InfoEvent']
                    ];
                }
                if (($a['ID'] == 0) || (!strlen($a['PROPERTY_COMPANY_SENDER_VALUE'])) || (!strlen($a['PROPERTY_COMPANY_RECIPIENT_VALUE'])))
                {
                    $filter = ["IBLOCK_ID" => 83, "PROPERTY_CREATOR" => (int)$arResult['CURRENT_CLIENT'], "ACTIVE" => "Y"];
                    if ((int)$a['ID'] > 0)
                    {
                        $filter['ID'] = (int)$a['ID'];
                    }
                    else

                    {
                        $filter['NAME'] = $a["NAME"];
                    }
                    if ((int)$arResult['CURRENT_BRANCH'] > 0)
                    {
                        $filter["PROPERTY_BRANCH"] = (int)$arResult['CURRENT_BRANCH'];
                    }
                    $res = CIBlockElement::GetList(["id" => "desc"], $filter, false, ["nTopCount"=>1],
                        ["ID","PROPERTY_COMPANY_SENDER","PROPERTY_COMPANY_RECIPIENT"]);
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
                $ind_nakl++;
               // AddToLogs('ObjDocs', ['component-1644'=> $a, 'N' =>  $ind_nak]);
                $arResult['ARCHIVE'][] = $a;

            }

            //  AddToLogs('ObjDocs', ['component-1644'=>  $arResult['ARCHIVE']]);
            /* записать в сессию для вывода отчета Excel в ЛК  */

            $arjsonexcel = array_merge( $arResult['REQUESTS'], $arResult['ARCHIVE']);
            $_SESSION['REPORTEXCEL'] = $arjsonexcel;
            // AddToLogs('test_logss', ["arjsonexcel" => $arjsonexcel]);

            foreach ($arResult['ARCHIVE'] as $k => $a)
            {
                $arResult['ARCHIVE'][$k]['test'] = $a['PROPERTY_INNER_NUMBER_CLAIM_VALUE'];

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

            //NOTE Формирование JSON-строки для xls-файла ['TYPE'] == 53 ?????


        }
        // TODO Подтягивать лимиты из записи филиала, фильтр периода по текущему месяцу  ЛИМИТЫ ???
        if ($arResult['LIMITS_OF_BRANCHES'])
        {
            $arResult['All_LIMIT'] = 0;
            $qw = GetQuarter($arResult['CURRENT_MONTH']);
            if ((int)$arResult['CURRENT_BRANCH'] > 0)
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
            $arQw = ['I','II','III','IV'];
            $arResult['QW_TEXT'] = $arQw[$qw];
        }

        $arResult['TITLE'] = GetMessage('TITLE_MODE_LIST');
        $APPLICATION->SetTitle(GetMessage('TITLE_MODE_LIST'));
        if($USER->isAdmin()){
            $finish = microtime(true);
            $delta = $finish - $start;
            AddToLogs('test_logs', ['time_1' => $delta,
                'mess' => 'время с начала до конца list']);
        }
        /*if ($USER->isAdmin()){
            $arResult['MODE'] = 'list_test';
        }*/

    }
    /* end list */
    if ($arResult['MODE'] == 'print_notification'){
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

        $arRes = json_decode($_GET['data'], true);
        foreach ($arRes as $value){
            $arResult[] = trim(htmlspecialcharsEx($value));
        }
        $arResult = arFromUtfToWin($arResult);

        $number =   $arResult[0];
        $city_rec = $arResult[5];
        $arFilter = [
            "NAME" => $number
        ];
        $arSelect = [
            "ID", "NAME", "PROPERTY_ADRESS_RECIPIENT","PROPERTY_CITY_RECIPIENT", "PROPERTY_DATE_FOR_DELIVERY",
            "PROPERTY_PACK_DESCRIPTION_STR"
        ];
        $resArr = GetInfoArr(false, false, 83, $arSelect, $arFilter);

        $arResult['INVOICE'] = $resArr;
        $adress = $arResult['INVOICE']['PROPERTY_ADRESS_RECIPIENT_VALUE']['TEXT'];
        $pack_descr_str = '';
        if(!empty($arResult['INVOICE']['PROPERTY_PACK_DESCRIPTION_STR_VALUE'])){
            $pack_descr_str = trim($arResult['INVOICE']['PROPERTY_PACK_DESCRIPTION_STR_VALUE']);
        }

        if(empty($adress)){
           // $client = soap_inc();
            $ar_params_json = [
                'NumDoc' => trim($number)
            ];
            $result = $client->GetDocInfo($ar_params_json);
            $m_res = $result->return;
            $obj_res = json_decode($m_res, true);
            $obj_res = arFromUtfToWin($obj_res);
            $adress = $obj_res['АдресПолучателя'];

        }
        $arResult['INVOICE']['CITY'] = $city_rec;
        $arResult['INVOICE']['ADRESS'] = $adress;
        $arResult['INVOICE']['PACK_DESC'] = $pack_descr_str;

        $APPLICATION->SetTitle('Уведомление о доставке по накладной № '.$number);


    }
    if (($arResult['MODE'] == 'print') || ($arResult['MODE'] == 'printsukhoi'))
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


        if ( $arResult['CURRENT_CLIENT'] == ID_VKOM1 ||
            $arResult['CURRENT_CLIENT'] == ID_VKOM2 ||
            $arResult['CURRENT_CLIENT'] == ID_VKOM3 ||
            $arResult['CURRENT_CLIENT'] == ID_VKOM4
        ){
            $vcom = true;
        }



        $arResult['CURRENT_CLIENT_COEFFICIENT_VW'] = WhatIsGabWeightCompany($arResult['CURRENT_CLIENT']);
        $idlogoprint = GetSettingValue(716, false, $arResult['UK']);
        $arResult['LOGO_PRINT'] = CFile::GetPath($idlogoprint);
        $arResult['ADRESS_PRINT'] = GetSettingValue(718, false, $arResult['UK']);
        $arResult['INVOICE'] = false;
        $id_reqv = (int)$_GET['id'];
        if ($id_reqv > 0)
        {
            //TO_DELIVER_BEFORE_DATE 772
            $filter = ["IBLOCK_ID" => 83, "ID" => $id_reqv, "PROPERTY_CREATOR" => $arResult["CURRENT_CLIENT"]];
            $ar_select =  [
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
                "PROPERTY_TO_DELIVER_BEFORE_DATE",
                "PROPERTY_PAYMENT",
                "PROPERTY_TYPE_PACK",
                "PROPERTY_PLACES",
                "PROPERTY_WEIGHT",
                "PROPERTY_COST",
                "PROPERTY_FOR_PAYMENT",
                "PROPERTY_DIMENSIONS",
                "PROPERTY_STATE",
                "PROPERTY_INSTRUCTIONS",
                "PROPERTY_PACK_DESCRIPTION",
                "PROPERTY_INNER_NUMBER_CLAIM",
                "PROPERTY_WHOSE_ORDER",
                "PROPERTY_TOTAL_GABWEIGHT",
                "PROPERTY_WITH_RETURN"
            ];

            $res = CIBlockElement::GetList(
                ["id" => "desc"],
                $filter,
                $ar_select
            );


            if ($ob = $res->GetNextElement())
            {
                // получаем дату создания корневой заявки(!)

                // -----------------------------------------

                $r = $ob->GetFields();

                // получаем дату создания корневой заявки(!) r
                //	file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/1cfilename002.txt', print_r($r, true), FILE_APPEND);
                // **************************************
                // найти все похожие имена ( без постфикса)
                // найдйти ID элемента с минимальным постфиксом

                // пишем параметры вызова курьера: "дата и время!"
                $r['PROPERTY_CALL_CURIER']  =  "+++ вызов курьера +++";

                $r['PROPERTY_INSTRUCTIONS_VALUE']['TEXT'] = $r['PROPERTY_INSTRUCTIONS_VALUE']['TEXT'];

                $r['PROPERTY_MINIMAL_NUMBER_SERIES'] = getRootInvoice($r["PROPERTY_INNER_NUMBER_CLAIM_VALUE"]);

                $r['PROPERTY_CITY_SENDER'] = GetFullNameOfCity($r['PROPERTY_CITY_SENDER_VALUE']);
                $r['PROPERTY_CITY_RECIPIENT'] = GetFullNameOfCity($r['PROPERTY_CITY_RECIPIENT_VALUE']);
                $r['PROPERTY_CITY_SENDER_AR'] = explode(', ', $r['PROPERTY_CITY_SENDER']);
                $r['PROPERTY_CITY_RECIPIENT_AR'] = explode(', ', $r['PROPERTY_CITY_RECIPIENT']);
                if(!empty($r["PROPERTY_TOTAL_GABWEIGHT"])){
                    $r["PROPERTY_OB_WEIGHT"] = round($r["PROPERTY_TOTAL_GABWEIGHT"], 2);
                }else{
                    $r["PROPERTY_OB_WEIGHT"] = 0;
                }

                if (strlen($r['PROPERTY_PACK_DESCRIPTION_VALUE']))
                {
                    $r['PACK_DESCR'] = json_decode(htmlspecialcharsBack($r['PROPERTY_PACK_DESCRIPTION_VALUE']), true);
                    foreach ($r['PACK_DESCR'] as $k => $str)
                    {
                        if(empty($r["PROPERTY_TOTAL_GABWEIGHT"])){
                            $r["PROPERTY_OB_WEIGHT"] = $r["PROPERTY_OB_WEIGHT"] + $str['gabweight'];
                        }
                        $r['PACK_DESCR'][$k]['name'] = iconv('utf-8','windows-1251',$str['name']);
                        $r['PACK_DESCR'][$k]['place'] = ((int)$r['PACK_DESCR'][$k]['place'] > 0) ? (int)$r['PACK_DESCR'][$k]['place'] : "";
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
                    $r['PACK_DESCR'][0] = [
                        'name' => $r['PROPERTY_TYPE_PACK_VALUE'],
                        'place' => $r['PROPERTY_PLACES_VALUE'],
                        'weight' => WeightFormat($r['PROPERTY_WEIGHT_VALUE'],false),
                        'size' => $r['PROPERTY_DIMENSIONS_VALUE'],
                        'gabweight' => $r['PROPERTY_OB_WEIGHT'],
                        'sizes' => ($r['PROPERTY_OB_WEIGHT'] > 0) ?  $r['PROPERTY_DIMENSIONS_VALUE'][0].' х '.$r['PROPERTY_DIMENSIONS_VALUE'][1].' х '.$r['PROPERTY_DIMENSIONS_VALUE'][2] : ""
                    ];
                }
                // смена шаблона для Вымпелком и Айсберг ЦКБ
                if($r['PROPERTY_WITH_RETURN_VALUE'] == 1 &&  $vcom){
                    $arResult['MODE'] = 'printV';
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

    if ($arResult['MODE'] == 'prints')
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
        if ( $arResult['CURRENT_CLIENT'] == ID_VKOM1 ||
            $arResult['CURRENT_CLIENT'] == ID_VKOM2 ||
            $arResult['CURRENT_CLIENT'] == ID_VKOM3 ||
            $arResult['CURRENT_CLIENT'] == ID_VKOM4
        ){
            $vcom = true;
        }

        /* скачать архив сканов со страницы массовой печати накладных  */
        if (!empty($_GET['scandocs'])){
            $dcs = [];
            $str = trim($_GET['scandocs']);
            $dcs = explode(',' , $str);
            if (file_exists($_SERVER['DOCUMENT_ROOT'].'/zip/')) {
                foreach (glob($_SERVER['DOCUMENT_ROOT'].'/zip/*.zip') as $file) {
                    unlink($file);
                }
            }
            $npref = mt_rand();
            $zip = new ZipArchive();
            $create = $zip->open($_SERVER['DOCUMENT_ROOT'].'/zip/scandocs-'.$npref.'.zip', ZipArchive::CREATE|ZipArchive::OVERWRITE);
            if ($create) {
                foreach($dcs as $documfile){
                    $doc = explode("/", $documfile);
                    $doc = explode("_",$doc[3]);
                    $ext = explode(".", $doc[2] );
                    $ext = $ext[1];
                    $doc = $doc[0];
                    $zip->addFile('/var/www/admin/www/'.$documfile, $doc.'.'.$ext);  /* пути к файлам должны быть относительные*/
                }
                $zip->close();
                if(is_file($_SERVER['DOCUMENT_ROOT'].'/zip/scandocs-'.$npref.'.zip')){
                    // dump("<a class='alert_scandocs' href='{$_SERVER['DOCUMENT_ROOT']}/scandocs.zip>");
                    echo("<a href='http://delivery-russia.ru/zip/scandocs-$npref.zip'>Скачать архив со Сканами</a>");
                }
            }else{
                echo("Ошибка");
            }
        }

        if (!empty($_GET['ids'])){
            /*if($USER->isAdmin()){
                dump($_GET['ids']);
                exit;
            }*/
            $ids = explode(',' , $_GET['ids']);
            $ids_inv = [];
            foreach($ids as $key=>$value){
                $str = explode('=', $value );
                if($str[0] == 'f001'){
                    $ids_inv[] = $str[1];
                }else{
                    $ids_inv[] = $value;
                }
            }


            if(!empty($ids_inv)){
                foreach($ids_inv as $key=>$value){
                    if(stripos($value,'-')){
                        $arParamsJson = [
                            'NumDoc' => trim($value),
                        ];
                        $result_0[$key] = $client->GetDocInfo($arParamsJson);
                        $mResult_0[$key] = $result_0[$key]->return;
                        $obj_0[$key] = json_decode($mResult_0[$key], true);
                        if(!empty(is_array($obj_0[$key]))){
                            $arResult['REQUEST'][$key] = arFromUtfToWin($obj_0[$key]); /* преобразовать из utf-8 в win-1251 */
                        }else{
                            $arResult['REQUEST'][$key] = false;
                            $arResult['TITLE'][$key] = 'Накладная не найдена';
                        }
                        if ((is_array($arResult['REQUEST'][$key]['Goods'])) && (count($arResult['REQUEST'][$key]['Goods']) > 0))
                        {
                            foreach ($arResult['REQUEST'][$key]['Goods'] as $k => $v)
                            {
                                if (strlen(trim($v['GoodsName'])) == 0)
                                {
                                    unset($arResult['REQUEST'][$key]['Goods'][$k]);
                                }
                            }
                        }

                        $resTv = CIBlockElement::GetList(
                            ["id" => "desc"],
                            ["IBLOCK_ID"=>83, "ID"=>$arResult['REQUEST'][$key]['IDсСайта']],
                            false, false, ["ID", 'NAME', 'PROPERTY_INNER_NUMBER_CLAIM', 'DATE_ACTIVE_FROM' , 'DATE_CREATE']);

                        $m = [];
                        while($obTv = $resTv->GetNextElement()){
                            $m = $obTv->GetFields();
                        }

                        $arResult['REQUEST'][$key]['NUMDOC'] = $arResult['REQUEST'][$key]['НомерНакладной'];
                        $idlogoprint = GetSettingValue(716, false, $arResult['UK']);
                        $adressprint = GetSettingValue(718, false, $arResult['UK']);
                        $arResult['REQUEST'][$key]['LOGO_PRINT'] = CFile::GetPath($idlogoprint);
                        $arResult['REQUEST'][$key]['DATA_CREATE'] = $m['DATE_CREATE'];
                        $arResult['REQUEST'][$key]['ADRESS_PRINT'] = $adressprint;
                        $APPLICATION->SetTitle( $arResult['REQUEST'][$key]['NUMDOC']);
                    }else{
                        // $arResult['CURRENT_CLIENT_COEFFICIENT_VW'][$key] = WhatIsGabWeightCompany($arResult['CURRENT_CLIENT']);
                        $id_reqv = (int)$value;
                        if ($id_reqv > 0)
                        {
                            $filter = ["IBLOCK_ID" => 83, "ID" => $id_reqv,
                                "PROPERTY_CREATOR" => $arResult["CURRENT_CLIENT"]];
                            $res = CIBlockElement::GetList(
                                ["id" => "desc"],
                                $filter,
                                false,
                                false,
                                [
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
                                    "PROPERTY_TO_DELIVER_BEFORE_DATE",
                                    "PROPERTY_PAYMENT",
                                    "PROPERTY_TYPE_PACK",
                                    "PROPERTY_PLACES",
                                    "PROPERTY_WEIGHT",
                                    "PROPERTY_COST",
                                    "PROPERTY_FOR_PAYMENT",
                                    "PROPERTY_DIMENSIONS",
                                    "PROPERTY_STATE",
                                    "PROPERTY_INSTRUCTIONS",
                                    "PROPERTY_PACK_DESCRIPTION",
                                    "PROPERTY_INNER_NUMBER_CLAIM",
                                    "PROPERTY_WHOSE_ORDER",
                                    "PROPERTY_WITH_RETURN"
                                ]
                            );
                            if ($ob = $res->GetNextElement())
                            {
                                $r = $ob->GetFields();

                                $r['PROPERTY_CALL_CURIER']  =  "+++ вызов курьера +++";
                                $r['PROPERTY_MINIMAL_NUMBER_SERIES'] = getRootInvoice($r["PROPERTY_INNER_NUMBER_CLAIM_VALUE"]);
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
                                        $r['PACK_DESCR'][$k]['place'] = ((int)$r['PACK_DESCR'][$k]['place'] > 0) ? (int)$r['PACK_DESCR'][$k]['place'] : "";
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
                                    $r['PACK_DESCR'][0] = [
                                        'name' => $r['PROPERTY_TYPE_PACK_VALUE'],
                                        'place' => $r['PROPERTY_PLACES_VALUE'],
                                        'weight' => WeightFormat($r['PROPERTY_WEIGHT_VALUE'],false),
                                        'size' => $r['PROPERTY_DIMENSIONS_VALUE'],
                                        'gabweight' => $r['PROPERTY_OB_WEIGHT'],
                                        'sizes' => ($r['PROPERTY_OB_WEIGHT'] > 0) ?  $r['PROPERTY_DIMENSIONS_VALUE'][0].' х '.$r['PROPERTY_DIMENSIONS_VALUE'][1].' х '.$r['PROPERTY_DIMENSIONS_VALUE'][2] : ""
                                    ];
                                }
                                $r["PROPERTY_OB_WEIGHT"] = WeightFormat($r["PROPERTY_OB_WEIGHT"], false);
                                $r["PROPERTY_WEIGHT_VALUE"] = WeightFormat($r["PROPERTY_WEIGHT_VALUE"], false);
                                $arResult['INVOICE'][$key] = $r;
                                $adressprint = GetSettingValue(718, false, $arResult['UK']);
                                $idlogoprint = GetSettingValue(716, false, $arResult['UK']);
                                $arResult['INVOICE'][$key]['LOGO_PRINT'] = CFile::GetPath($idlogoprint);
                                $arResult['INVOICE'][$key]['ADRESS_PRINT'] = $adressprint;
                                $arResult['INVOICE'][$key]['NUMDOC'] = $arResult['INVOICE'][$key]['NAME'];
                                $arResult['INVOICE'][$key]['PROPERTY_WITH_RETURN'] =  $r['PROPERTY_WITH_RETURN_VALUE'];
                                // метка для Вымпелком  с Возвратом
                                if ( $arResult['CURRENT_CLIENT'] == ID_VKOM1 ||
                                    $arResult['CURRENT_CLIENT'] == ID_VKOM2 ||
                                    $arResult['CURRENT_CLIENT'] == ID_VKOM3 ||
                                    $arResult['CURRENT_CLIENT'] == ID_VKOM4
                                )
                                {
                                    $arResult['INVOICE'][$key]['PROPERTY_WITH_RETURN'] =  $r['PROPERTY_WITH_RETURN_VALUE'];
                                }

                                $APPLICATION->SetTitle($arResult['INVOICE'][$key]['NAME']);
                                // dump($arResult['INVOICE']);
                            }
                        }
                        else
                        {

                            $APPLICATION->SetTitle(GetMessage('ERR_NO_REQUEST'));
                        }
                    }

                }

            }



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
        $id_reqv = (int)$_GET['id'];
        if ($id_reqv > 0)
        {
            //TO_DELIVER_BEFORE_DATE 772
            $res = CIBlockElement::GetList(
                ["id" => "desc"],
                ["IBLOCK_ID" => 83, "ID" => $id_reqv, "PROPERTY_CREATOR" => $arResult["CURRENT_CLIENT"]],
                false,
                false,
                [
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
                    "PROPERTY_TO_DELIVER_BEFORE_DATE",
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
                    "PROPERTY_PACK_GOODS",
                    "PROPERTY_INNER_NUMBER_CLAIM"
                ]
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
                        $r['PACK_DESCR'][$k]['place'] = ((int)$r['PACK_DESCR'][$k]['place'] > 0) ? intval($r['PACK_DESCR'][$k]['place']) : "";
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
                    $r['PACK_DESCR'][0] = [
                        'name' => $r['PROPERTY_TYPE_PACK_VALUE'],
                        'place' => $r['PROPERTY_PLACES_VALUE'],
                        'weight' => $r['PROPERTY_WEIGHT_VALUE'],
                        'size' => $r['PROPERTY_DIMENSIONS_VALUE'],
                        'gabweight' => $r["PROPERTY_OB_WEIGHT"]
                    ];
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

    if (($arResult['MODE'] == 'invoice1c_modal') || ($arResult['MODE'] == 'invoice1c_print')
        || ($arResult['MODE'] == 'invoice1c_printsukhoi'))
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
        //  Вымпелком
        if ( $arResult['CURRENT_CLIENT'] == ID_VKOM1 ||
            $arResult['CURRENT_CLIENT'] == ID_VKOM2 ||
            $arResult['CURRENT_CLIENT'] == ID_VKOM3 ||
            $arResult['CURRENT_CLIENT'] == ID_VKOM4){
            $vcom = true;
        }

        if (strlen(trim($_GET['f001'])))
        {

            $arParamsJson = [
                'NumDoc' => trim( iconv('windows-1251','utf-8',$_GET['f001'])),
            ];
            $result_0 = $client->GetDocInfo($arParamsJson);
            $mResult_0 = $result_0->return;
            $obj_0 = json_decode($mResult_0, true);
            $arResult['REQUEST'] = false;
            $arResult['TITLE'] = 'Накладная не найдена';
            $APPLICATION->SetTitle($arResult['TITLE']);
            if ((is_array($obj_0)) && (count($obj_0) > 0))
            {
                $arResult['REQUEST'] = arFromUtfToWin($obj_0);
                /* if($USER->isAdmin()){
                     dump( $arResult['REQUEST'] );
                 }*/
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

                // два  реквеста
                //$arResult['REQUEST']['PROPERTY_MINIMAL_NUMBER_SERIES'] =  ;

                //  получим все наши данные из 1С ------- из ПримечаниеОтправителя
                //  file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/filename_request_1.txt', print_r($arResult['REQUEST'], true), FILE_APPEND);
                //  -------------------------------------
                // TODO  решить с датами
                // ID тут  берется внутренний номер заявки из 1С для даты!  [НомерНакладной]

                //$resTv = CIBlockElement::GetList(
                //array("id" => "desc"),
                //array("IBLOCK_ID"=>83, "NAME"=>$arResult['REQUEST']['НомерНакладной']),
                //false, false, array("ID", 'NAME', 'PROPERTY_INNER_NUMBER_CLAIM', 'DATE_ACTIVE_FROM' , 'DATE_CREATE'));

                // ?
                $resTv = CIBlockElement::GetList(
                    ["id" => "desc"],
                    ["IBLOCK_ID"=>83, "ID"=>$arResult['REQUEST']['IDсСайта']],
                    false, false, ["ID", 'NAME', 'PROPERTY_INNER_NUMBER_CLAIM', 'PROPERTY_WITH_RETURN',
                    'DATE_ACTIVE_FROM' , 'DATE_CREATE']);

                $m = [];
                while($obTv = $resTv->GetNextElement()){
                    $m = $obTv->GetFields();
                }

                //*
                //if (trim($m['DATE_CREATE'])=="") {$m['DATE_CREATE'] = $obj_0['Дата'];}
                //	$m = "!!! IDсСайта= ".$arResult['REQUEST']['IDсСайта']. " = [".$m['DATE_CREATE']."]"."[REQUEST = ".$arResult['REQUEST']['НомерНакладной']."]  ==== ".$obj_0['Дата'];
                //	file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/dop.txt', print_r($m, true), FILE_APPEND);
                //	file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/dop11.txt', print_r($obj_0, true), FILE_APPEND);
                //*

                $arResult['REQUEST']['PROPERTY_MINIMAL_NUMBER_SERIES'] = getRootInvoice($arResult['REQUEST']['ПримечаниеОтправителя']);
                $arResult['REQUEST']['PROPERTY_INNER_NUMBER_CLAIM_VALUE'] = $arResult['REQUEST']['ПримечаниеОтправителя'];
                $arResult['REQUEST']['DATA_CREATE'] = $m['DATE_CREATE'];

                // ***** 0000000000000

                // *****

                // альтернативное описание взятое из инфоблоков

                $arResult['REQUEST']['PROPERTY_PACK_DESC_INFO'] = json_decode(htmlspecialcharsBack(getFieldPackDescription($arResult['REQUEST']['IDсСайта'])), true);

                // $arResult['REQUEST']['PROPERTY_MINIMAL_NUMBER_SERIES'] = getRootInvoice($m['PROPERTY_INNER_NUMBER_CLAIM_VALUE']);
                // $arResult['REQUEST']['PROPERTY_INNER_NUMBER_CLAIM_VALUE'] = $m['PROPERTY_INNER_NUMBER_CLAIM_VALUE'];
                // $arResult['REQUEST']['DATA_CREATE'] = $m['DATE_CREATE'];

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
                // смена шаблона для вымпелком  с Возвратом

                if ($m['PROPERTY_WITH_RETURN_VALUE'] == 1 &&  $vcom){
                    $arResult['MODE'] = 'invoice1c_printV';
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
        if ((int)$arResult['CURRENT_CLIENT'] == 0)
        {
            $arResult['OPEN'] = false;
            if ($arResult['ADMIN_AGENT'])
            {
                $arResult["WARNINGS"][] = GetMessage('ERR_OPEN_ADMIN',array('#LINK#' => $arParams['LINK']));
            }
            else
            {
                $arResult["WARNINGS"][] = GetMessage('ERR_OPEN');
            }
        }
        if ($arResult['CURRENT_CLIENT'] > 0)
        {
            $arResult['CURRENT_CLIENT_COEFFICIENT_VW'] = WhatIsGabWeightCompany($arResult['CURRENT_CLIENT']);
            $db_props = CIBlockElement::GetProperty(40, $arResult['CURRENT_CLIENT'], ["sort" => "asc"], array("CODE"=>"INN"));
            if($ar_props = $db_props->Fetch())
            {
                $arResult['CURRENT_CLIENT_INN'] = $ar_props["VALUE"];
            }
            if ($arParams['TYPE'] == 53)
            {
                $res = CIBlockElement::GetList(["name"=>"asc"], ["IBLOCK_ID"=>40, "ACTIVE"=>"Y", 'PROPERTY_BY_AGENT' => $arResult['CURRENT_CLIENT']], false, false, array("ID", "NAME"));
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
        $arResult['INVOICE'] = false;
        $id_reqv = intval($_GET['id']);
        if ($id_reqv > 0)
        {
            //TO_DELIVER_BEFORE_DATE 772
            $res = CIBlockElement::GetList(
                ["id" => "desc"],
                ["IBLOCK_ID" => 83, "ID" => $id_reqv, "PROPERTY_CREATOR" => $arResult['CURRENT_CLIENT']],
                false,
                false,
                [
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
                    "PROPERTY_TO_DELIVER_BEFORE_DATE",
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
                    "PROPERTY_WHOSE_ORDER",
                    "PROPERTY_INNER_NUMBER_CLAIM",
                    "PROPERTY_CENTER_EXPENSES",
                    "PROPERTY_CENTER_EXPENSES.NAME",
                    "PROPERTY_WITH_RETURN",
                    "PROPERTY_NUMBER_WITH_RETURN"
                ]
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
        if ((isset($_POST['save'])) || (isset($_POST['save-print'])) || (isset($_POST['save_ctrl'])))
        {
            if ($arResult['CURRENT_CLIENT'] == ID_TEST){
                //dump($arResult['INVOICE']);
                //exit;
            }
            if(!empty($_POST['WITH_RETURN'])){
                $WITH_RETURN = 1;
            }else{
                $WITH_RETURN = false;
            }

            // отмена С возвратом
            if (empty($_POST['WITH_RETURN']) && !empty($arResult['INVOICE']['PROPERTY_WITH_RETURN_VALUE'])){
                $arr = [];
                $number_nakl_ret = $arResult['INVOICE']['NAME'] . '-1';
                $arSelect = [
                    "ID", "ACTIVE", "NAME", "IBLOCK_ID"
                ];
                $arFilter = [
                    "IBLOCK_ID" => 83,
                    "ACTIVE" => "Y",
                    "NAME" => $number_nakl_ret
                ];
                $res = CIBlockElement::GetList(
                    ["ID"=>"ASC"],
                    $arFilter,
                    false,
                    false,
                    $arSelect);

                while($ob = $res->GetNextElement()) {
                    $arr = $ob->GetFields();
                }
                  $id_del = (int)$arr['ID'];
                if(is_numeric($id_del)){
                    CIBlockElement::Delete($id_del);
                    CIBlockElement::SetPropertyValuesEx($arResult['INVOICE']['ID'], 83, [980 => '']);
                 // $client = soap_inc();
                    $arParamsJson = [
                        'ID' => $id_del
                    ];
                    $arParamsJsonC = json_encode($arParamsJson);
                    $result = $client->SetPickupDelete($arParamsJsonC);
                }

            }

            if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
            {
                $_POST = [];
                $arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
            }
            else
            {
                $arPostLogsVal = [];
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
                //AddToLogs('InvEditPostValues',$arPostLogsVal);
                $_SESSION[$_POST["key_session"]] = $_POST["rand"];
                $arResult["ERR_FIELDS"] = [];
                $arJsonDescr = [];
                $total_place = 0;
                $total_weight = 0;
                $total_gabweight = 0;
                foreach ($_POST['pack_description'] as $description_str)
                {
                    $sizes = [];
                    foreach ($description_str['size'] as $sz)
                    {
                        $sizes[] = (float)str_replace(',', '.', $sz);
                    }
                    $arCurStr = [
                        'name' => iconv('windows-1251','utf-8',$description_str['name']),
                        'place' => (int)$description_str['place'],
                        'weight' => (float)str_replace(',', '.', $description_str['weight']),
                        'size' => $sizes,
                        'gabweight' => (($sizes[0]*$sizes[1]*$sizes[2])/$arResult['CURRENT_CLIENT_COEFFICIENT_VW'])
                    ];
                    $total_place = $total_place + $arCurStr['place'];
                    $total_weight = $total_weight + $arCurStr['weight'];
                    $total_gabweight = $total_gabweight + $arCurStr['gabweight'];
                    $arJsonDescr[] = $arCurStr;
                }
                $arJsonGoods = [];
                foreach ($_POST['goods'] as $goods_str)
                {
                    $arJsonGoods[] = [
                        'GoodsName' => iconv('windows-1251','utf-8',$goods_str['name']),
                        'Amount' => (int)$goods_str['amount'],
                        'Price' => (float)str_replace(',', '.', $goods_str['price']),
                        'Sum' => (float)str_replace(',', '.', $goods_str['sum']),
                        'SumNDS' => (float)str_replace(',', '.', $goods_str['sumnds']),
                        'PersentNDS' => (int)$goods_str['persentnds']
                    ];
                }
                //TO_DELIVER_BEFORE_DATE 772 ?
                $arChanges = [
                    550 => deleteTabs($_POST['INDEX_SENDER']),
                    556 => $_POST['INDEX_RECIPIENT'],
                    560 => deleteTabs($_POST['IN_DATE_DELIVERY']),
                    561 => deleteTabs($_POST['IN_TIME_DELIVERY']),
                    565 => (float)str_replace(',', '.', $_POST['FOR_PAYMENT']),
                    733 => (float)str_replace(',', '.', $_POST['PAYMENT_COD']),
                    566 => (float)str_replace(',', '.', $_POST['COST']),
                    569 => $_POST['DIMENSIONS'],
                    570 => ['VALUE' => ['TYPE' => 'text', 'TEXT' => deleteTabs($_POST['INSTRUCTIONS'])]],
                    682 => json_encode($arJsonDescr),
                    724 => json_encode($arJsonGoods),
                    563 => '',
                    737 => false,
                    764 => $_POST['INNER_NUMBER_CLAIM'],
                ];
                  if($WITH_RETURN) $arChanges[980] = $WITH_RETURN;
                if ($arResult['CURRENT_CLIENT'] == ID_ABSOLUT){
                    if (!strlen($_POST['CENTER_EXPENSES']))
                    {
                        $arResult["ERR_FIELDS"]["CENTER_EXPENSES"] = 'has-error';
                    }
                    else
                    {
                        $arChanges[981] = (int)$_POST['CENTER_EXPENSES'];
                    }
                }


                // если более 255 символов
                if (strlen($_POST['INSTRUCTIONS']) > 255)
                {
                    $arResult["ERR_FIELDS"]["INSTRUCTIONS"] = 'has-error err08inst';
                }

                if (!strlen($_POST['NAME_SENDER']))
                {
                    $arResult["ERR_FIELDS"]["NAME_SENDER"] = 'has-error err19';
                }
                else
                {
                    $arChanges[546] = NewQuotes($_POST['NAME_SENDER']);
                }
                if (!strlen($_POST['PHONE_SENDER']))
                {
                    $arResult["ERR_FIELDS"]["PHONE_SENDER"] = 'has-error err18';
                }
                else
                {
                    $arChanges[547] = NewQuotes($_POST['PHONE_SENDER']);
                }
                if (!strlen($_POST['COMPANY_SENDER']))
                {
                    $arResult["ERR_FIELDS"]["COMPANY_SENDER"] = 'has-error err17';
                }
                else
                {
                    $arChanges[548] = NewQuotes($_POST['COMPANY_SENDER']);
                }
                if (!strlen($_POST['CITY_SENDER']))
                {
                    $arResult["ERR_FIELDS"]["CITY_SENDER"] = 'has-error err16';
                }
                else
                {
                    $city_sender = GetCityId(trim($_POST['CITY_SENDER']));
                    if ($city_sender == 0)
                    {
                        $arResult["ERR_FIELDS"]["CITY_SENDER"] = 'has-error err15';
                    }
                    else
                    {
                        $arChanges[549] = $city_sender;
                    }
                }
                if (!strlen($_POST['ADRESS_SENDER']))
                {
                    $arResult["ERR_FIELDS"]["ADRESS_SENDER"] = 'has-error err14';
                }
                else
                {
                    $arChanges[551] = array('VALUE' => array('TYPE' => 'text', 'TEXT' => NewQuotes($_POST['ADRESS_SENDER'])));
                }
                if (!strlen($_POST['NAME_RECIPIENT']))
                {
                    $arResult["ERR_FIELDS"]["NAME_RECIPIENT"] = 'has-error err13';
                }
                else
                {
                    $arChanges[552] = NewQuotes($_POST['NAME_RECIPIENT']);
                }
                if (!strlen($_POST['PHONE_RECIPIENT']))
                {
                    $arResult["ERR_FIELDS"]["PHONE_RECIPIENT"] = 'has-error err12';
                }
                else
                {
                    $arChanges[553] = NewQuotes($_POST['PHONE_RECIPIENT']);
                }
                if (!strlen($_POST['COMPANY_RECIPIENT']))
                {
                    $arResult["ERR_FIELDS"]["COMPANY_RECIPIENT"] = 'has-error err11';
                }
                else
                {
                    $arChanges[554] = NewQuotes($_POST['COMPANY_RECIPIENT']);
                }
                if (!strlen($_POST['CITY_RECIPIENT']))
                {
                    $arResult["ERR_FIELDS"]["CITY_RECIPIENT"] = 'has-error err10';
                }
                else
                {
                    $city_recipient = GetCityId(trim($_POST['CITY_RECIPIENT']));
                    if ($city_recipient == 0)
                    {
                        $arResult["ERR_FIELDS"]["CITY_RECIPIENT"] = 'has-error err9';
                    }
                    else
                    {
                        $arChanges[555] = $city_recipient;
                    }
                }
                if (!strlen($_POST['ADRESS_RECIPIENT']))
                {
                    $arResult["ERR_FIELDS"]["ADRESS_RECIPIENT"] = 'has-error err8';
                }
                else
                {
                    $arChanges[571] = array('VALUE' => array('TYPE' => 'text', 'TEXT' => NewQuotes($_POST['ADRESS_RECIPIENT'])));
                }
                if (!$_POST['TYPE_DELIVERY'])
                {
                    $arResult["ERR_FIELDS"]["TYPE_DELIVERY"] = 'has-error err7';
                }
                else
                {
                    $arChanges[557] = $_POST['TYPE_DELIVERY'];
                }
                if (!$_POST['TYPE_PACK'])
                {
                    $arResult["ERR_FIELDS"]["TYPE_PACK"] = 'has-error err6';
                }
                else
                {
                    $arChanges[558] = $_POST['TYPE_PACK'];
                }
                if (!$_POST['WHO_DELIVERY'])
                {
                    $arResult["ERR_FIELDS"]["WHO_DELIVERY"] = 'has-error err5';
                }
                else
                {
                    $arChanges[559] = $_POST['WHO_DELIVERY'];
                }

                if (!$_POST['TYPE_PAYS'])
                {
                    $arResult["ERR_FIELDS"]["TYPE_PAYS"] = 'has-error err4';
                }
                else
                {
                    if (($_POST['TYPE_PAYS'] == 253) && (!strlen($_POST['PAYS'])))
                    {
                        $arResult["ERR_FIELDS"]["TYPE_PAYS"] = 'has-error err3';
                    }
                    else
                    {
                        $arChanges[562] = $_POST['TYPE_PAYS'];
                        $arChanges[563] = NewQuotes($_POST['PAYS']);
                    }
                }

                if (!$_POST['PAYMENT'])
                {
                    $arResult["ERR_FIELDS"]["PAYMENT"] = 'has-error err2';
                }
                else
                {
                    $arChanges[564] = $_POST['PAYMENT'];
                }
                /******/
                if (!$_POST['TYPE_PAYS'])
                {
                    $arResult["ERR_FIELDS"]["TYPE_PAYS"] = 'has-error err1';
                }
                else
                {
                    if ((intval($_POST['PAYMENT']) == 256) && ((intval($_POST['TYPE_PAYS']) == 252) || (intval($_POST['TYPE_PAYS']) == 253)))
                    {
                        if ((count($arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT_VALUE']) > 0) && (is_array($arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT_VALUE'])))
                        {
                            if (intval($_POST['WHOSE_ORDER']) == 0)
                            {
                                $arResult["ERR_FIELDS"]["TYPE_PAYS"] = 'has-error err01';
                                $arResult["ERR_FIELDS"]["WHOSE_ORDER"] = 'has-error err02';
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
                                $arResult["ERR_FIELDS"]["TYPE_PAYS"] = 'has-error err03';
                                $arResult["ERR_FIELDS"]["WHOSE_ORDER"] = 'has-error err04';
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
                if (($total_place <= 0) || ($total_place >= 10000))
                {
                    $arResult["ERR_FIELDS"]["PLACES"] = 'has-error err05';
                }
                else
                {
                    $arChanges[567] = $total_place;
                }
                if (($total_weight <= 0) || ($total_weight >= 10000))
                {
                    $arResult["ERR_FIELDS"]["WEIGHT"] = 'has-error err06';
                }
                else
                {
                    $arChanges[568] = $total_weight;
                }
                if ($total_gabweight >= 10000)
                {
                    $arResult["ERR_FIELDS"]["SIZE"] = 'has-error err07';
                }


                if (count($arResult["ERR_FIELDS"]) == 0)
                {

                    CIBlockElement::SetPropertyValuesEx($_POST['id'], 83, $arChanges);
                    // сделать из накладной накладную С возвратом

                    if (!empty($_POST['WITH_RETURN']) && empty($arResult['INVOICE']['PROPERTY_WITH_RETURN_VALUE'])){

                        $db_props_uk = CIBlockElement::GetProperty(40, $arResult['UK'], ["sort" => "asc"], ["ID" => 474]);
                        if ($ar_props_uk = $db_props_uk->Fetch())
                        {
                            $sets_id_uk = $ar_props_uk["VALUE"];
                        }


                        $invoice = new Invoice($arResult['INVOICE']['NAME'], $sets_id_uk);
                       try{
                           $invoice->getBaseInv()
                               ->makeReturnInvoice() // создаем возвратную накладную
                               ->makeInvoicePDF()
                               ->sendMailCallCourier();
                       }catch(Exception $e){

                       }

                    }

                    // **********
                    // урезанный вариант уведомления о редактировании Добавляем ИНСТРУКЦИИ
                    // **********
                    $id_in_cur = 0;
                    $payment_type1   = 0;
                    $delivery_payer1 = 0;
                    $total_weight1   = 0;

                    if (strlen(trim($_POST['NUMBER']))) {
                        $id_in = [
                            'max_id' => 0
                        ];
                        $number_nakl = trim($_POST['NUMBER']);

                    } else {
                        $id_in = MakeInvoiceNumberNew(1, 7, '90-');
                        $number_nakl = $id_in['number'];
                    }

                    $number_nakl = trim($_POST['number']);
                    $number_internal = trim($_POST['InternalNumber']);

                    // увидим номер накладной
                    //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/test1number_nakl.txt', $number_nakl, FILE_APPEND);
                    // ***

                    // увидим номер накладной
                    //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/test1number_nakl.txt', $number_nakl, FILE_APPEND);
                    //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/test1number_naklpost.txt', print_r($_POST, true), FILE_APPEND);
                    //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/test1number_naklarResult.txt', print_r($arResult, true), FILE_APPEND);

                    foreach ($_POST['pack_description'] as $description_str) {

                        $sizes = [];
                        foreach ($description_str['size'] as $sz)
                        {
                            $sizes[] = (float)str_replace(',', '.', $sz);
                        }

                        $arCurStr = array(
                            'name' => iconv('windows-1251','utf-8',$description_str['name']),
                            'place' => (int)$description_str['place'],
                            'weight' => (float)str_replace(',', '.', $description_str['weight']),
                            'size' => $sizes,
                            'gabweight' => (($sizes[0]*$sizes[1]*$sizes[2])/$arResult['CURRENT_CLIENT_COEFFICIENT_VW'])
                        );

                        $total_place1 = $total_place1 + $arCurStr['place'];
                        //$total_weight1 = $total_weight + $arCurStr['weight'];
                        $total_gabweight1 = $total_gabweight1 + $arCurStr['gabweight'];
                        $arJsonDescr[] = $arCurStr;

                        $total_weight1 = $total_weight1 + floatval(str_replace(',','.',$description_str['weight']));
                    }

                    // посмотрим что здесь есть
                    //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/test1number_naklarJsonDescr11.txt', print_r($arJsonDescr, true), FILE_APPEND);
                    // ------------------------
                    $payment_type1 = 'Наличные';
                    switch ($_POST['PAYMENT'])
                    {
                        case 255:
                            $payment_type1 = 'Наличные';
                            break;
                        case 256:
                            $payment_type1 = 'Безналичные';
                            break;
                    }

                    $delivery_payer1 = 'Отправитель';
                    switch ($_POST['TYPE_PAYS'])
                    {
                        case 251:
                            $delivery_payer1 = 'Отправитель';
                            break;
                        case 252:
                            $delivery_payer1 = 'Получатель';
                            break;
                        case 253:
                            $delivery_payer1 = 'Другой';
                            break;
                    }

                    $delivery_condition1 = 'По адресу';
                    switch ($_POST['WHO_DELIVERY'])
                    {
                        case 248:
                            $delivery_condition1 = 'По адресу';
                            break;
                        case 249:
                            $delivery_condition1 = 'До востребования';
                            break;
                        case 250:
                            $delivery_condition1 = 'Лично в руки';
                            break;
                    }

                    // **********


                        $arEventFields = [
                        "COMPANY_F" => ($arResult['USER_IN_BRANCH']) ? $arResult['AGENT']['NAME'].', филиал '.$arResult['BRANCH_INFO']['NAME'] : $arResult['AGENT']['NAME'],
                        "NUMBER" => $number_nakl,
                        "COMPANY" => $arResult['AGENT']['NAME'],
                        "BRANCH" => '' ,
                        "DATE_TIME" => '',
                        "CITY" => $_POST['CITY_SENDER'],
                        "ADRESS" => NewQuotes($_POST['ADRESS_SENDER']),
                        "CONTACT" => NewQuotes($_POST['NAME_SENDER']),
                        "PHONE" => NewQuotes($_POST['PHONE_SENDER']),
                        "WEIGHT" => $total_weight,
                        /*"SIZE_1" => $_POST['pack_description'][0]['size'][0].'x'.$_POST['pack_description'][0]['size'][1].'x'.$_POST['pack_description'][0]['size'][2],
                        "SIZE_2" => $_POST['pack_description'][1]['size'][0].'x'.$_POST['pack_description'][1]['size'][1].'x'.$_POST['pack_description'][1]['size'][2],
                        "SIZE_3" => $_POST['pack_description'][2]['size'][0].'x'.$_POST['pack_description'][2]['size'][1].'x'.$_POST['pack_description'][2]['size'][2],
                        "SIZE_4" => $_POST['pack_description'][3]['size'][0].'x'.$_POST['pack_description'][3]['size'][1].'x'.$_POST['pack_description'][3]['size'][2],
                        "SIZE_5" => $_POST['pack_description'][4]['size'][0].'x'.$_POST['pack_description'][4]['size'][1].'x'.$_POST['pack_description'][4]['size'][2],*/
                        "COMMENT" => deleteTabs($_POST['callcourcomment']).' Накладная №'.$number_nakl,
                        'AGENT_EMAIL' => $arResult['ADD_AGENT_EMAIL'],
                        'UK_EMAIL' => $arResult['EMAIL_CALLCOURIER'],
                        'TYPE_PAYS' =>  $payment_type1,
                        'PAYER' => $delivery_payer1
                    ];
                    if(!empty($_POST['pack_description'])){
                        foreach($_POST['pack_description'] as $key=>$value){
                            $prf = $key + 1;
                            $arEventFields['SIZE_'.$prf] = $_POST['pack_description'][$key]['size'][0].'x'.$_POST['pack_description'][$key]['size'][1].'x'.$_POST['pack_description'][$key]['size'][2];
                            $arEventFields['SIZE'] .= $_POST['pack_description'][$key]['size'][0].'x'.$_POST['pack_description'][$key]['size'][1].'x'.$_POST['pack_description'][$key]['size'][2] . "<br/>";
                        }
                    }

                    // *****  формируем файл pdf
                    $sitysender = explode(", ", $_POST['CITY_SENDER']);
                    $s_sender = $sitysender[0];
                    $o_sender = $sitysender[1];
                    $c_sender = $sitysender[2];

                    $sityrecepient = explode(", ", $_POST['CITY_RECIPIENT']);
                    $s_recepient = $sityrecepient[0];
                    $o_recepient = $sityrecepient[1];
                    $c_recepient = $sityrecepient[2];
                    $idlogoprint = GetSettingValue(716, false, $arResult['UK']);

                    $arResult['LOGO_PRINT'] = CFile::GetPath($idlogoprint);
                    $arResult['ADRESS_PRINT'] = GetSettingValue(718, false, $arResult['UK']);
                    $arPDF['LOGO_PRINT'] = $arResult['LOGO_PRINT'];
                    $arPDF['ADRESS_PRINT'] = $arResult['ADRESS_PRINT'];
                    $arPDF['REQUEST']['number_nakl'] = $number_nakl;
                    $arPDF['REQUEST']['NAME_SENDER'] = NewQuotes($_POST['NAME_SENDER']);
                    $arPDF['REQUEST']['PHONE_SENDER'] = NewQuotes($_POST['PHONE_SENDER']);
                    $arPDF['REQUEST']['TYPE_DELIVERY'] = $delivery_type1;
                    $arPDF['REQUEST']['TYPE_PAYS'] = $delivery_payer1;
                    $arPDF['REQUEST']['COMPANY_SENDER'] = NewQuotes($_POST['COMPANY_SENDER']);
                    //$arPDF[REQUEST][ВыборОтправителя] = 'Новый партнер 1';
                    $arPDF['REQUEST']['c_sender'] = $c_sender;
                    $arPDF['REQUEST']['o_sender'] = $o_sender;
                    $arPDF['REQUEST']['WHO_DELIVERY'] = $delivery_condition1;
                    $arPDF['REQUEST']['s_sender'] = $s_sender;
                    $arPDF['REQUEST']['INDEX_SENDER'] = deleteTabs($_POST['INDEX_SENDER']);
                    $arPDF['REQUEST']['PAYMENT'] = $payment_type1;
                    $arPDF['REQUEST']['ADRESS_SENDER'] = NewQuotes($_POST['ADRESS_SENDER']);
                    $arPDF['REQUEST']['NAME_RECIPIENT'] = NewQuotes($_POST['NAME_RECIPIENT']);
                    $arPDF['REQUEST']['PHONE_RECIPIENT'] = NewQuotes($_POST['PHONE_RECIPIENT']);
                    $arPDF['REQUEST']['INSTRUCTIONS'] = NewQuotes($_POST['INSTRUCTIONS']);
                    $arPDF['REQUEST']['COMPANY_RECIPIENT'] = NewQuotes($_POST['COMPANY_RECIPIENT']);
                    //$arPDF[REQUEST][ВыборПолучателя] = 'Новый партнер 2';
                    $arPDF['REQUEST']['c_recepient'] = $c_recepient;
                    $arPDF['REQUEST']['o_recepient'] = $o_recepient;
                    $arPDF['REQUEST']['s_recepient'] = $s_recepient;
                    $arPDF['REQUEST']['INDEX_RECIPIENT'] = deleteTabs($_POST['INDEX_RECIPIENT']);
                    $arPDF['REQUEST']['FOR_PAYMENT'] = (float)str_replace(',', '.', $_POST['FOR_PAYMENT']);
                    $arPDF['REQUEST']['PAYMENT_COD'] = (float)str_replace(',', '.', $_POST['PAYMENT_COD']);
                    $arPDF['REQUEST']['COST'] = (float)str_replace(',', '.', $_POST['COST']);
                    $arPDF['REQUEST']['ADRESS_RECIPIENT'] = NewQuotes($_POST['ADRESS_RECIPIENT']);
                    $arPDF['REQUEST']['total_place'] = $total_place1;
                    $arPDF['REQUEST']['total_weight'] = $total_weight1;
                    $arPDF['REQUEST']['total_gabweight'] = $total_gabweight1;
                    $arPDF['REQUEST']['COST2'] = (float)str_replace(',', '.', $_POST['COST']);
                    $arPDF['REQUEST']['gab_1_name'] = iconv('utf-8', 'windows-1251',$arJsonDescr[0]['name']);
                    $arPDF['REQUEST']['gab_1_place'] = $arJsonDescr[0]['place'];
                    $arPDF['REQUEST']['gab_1_weight'] = $arJsonDescr[0]['weight'];
                    $arPDF['REQUEST']['gab_1_sizes'] =$arJsonDescr[0]['size'][0]."x".$arJsonDescr[0]['size'][1]."x".$arJsonDescr[0]['size'][2];
                    $arPDF['REQUEST']['gab_2_name'] = iconv('utf-8', 'windows-1251',$arJsonDescr[1]['name']);
                    $arPDF['REQUEST']['gab_2_place'] = $arJsonDescr[1]['place'];
                    $arPDF['REQUEST']['gab_2_weight'] = $arJsonDescr[1]['weight'];
                    $arPDF['REQUEST']['gab_2_sizes'] =$arJsonDescr[1]['size'][0]."x".$arJsonDescr[1]['size'][1]."x".$arJsonDescr[1]['size'][2];
                    $arPDF['REQUEST']['gab_3_name'] = iconv('utf-8', 'windows-1251',$arJsonDescr[2]['name']);
                    $arPDF['REQUEST']['gab_3_place'] = $arJsonDescr[2]['place'];
                    $arPDF['REQUEST']['gab_3_weight'] = $arJsonDescr[2]['weight'];
                    $arPDF['REQUEST']['gab_3_sizes'] =$arJsonDescr[2]['size'][0]."x".$arJsonDescr[2]['size'][1]."x".$arJsonDescr[2]['size'][2];
                    $arPDF['REQUEST']['gab_4_name'] = iconv('utf-8', 'windows-1251',$arJsonDescr[3]['name']);
                    $arPDF['REQUEST']['gab_4_place'] = $arJsonDescr[3]['place'];
                    $arPDF['REQUEST']['gab_4_weight'] = $arJsonDescr[3]['weight'];
                    $arPDF['REQUEST']['gab_4_sizes'] =$arJsonDescr[3]['size'][0]."x".$arJsonDescr[3]['size'][1]."x".$arJsonDescr[3]['size'][2];
                    $arPDF['REQUEST']['gab_5_name'] = iconv('utf-8', 'windows-1251',$arJsonDescr[4]['name']);
                    $arPDF['REQUEST']['gab_5_place'] = $arJsonDescr[4]['place'];
                    $arPDF['REQUEST']['gab_5_weight'] = $arJsonDescr[4]['weight'];
                    $arPDF['REQUEST']['gab_5_sizes'] =$arJsonDescr[4]['size'][0]."x".$arJsonDescr[4]['size'][1]."x".$arJsonDescr[4]['size'][2];
                    // это массив с нашими описаниями в накладную целиком!
                    $arPDF['REQUEST']['test'] = 12345;
                    // это массив с нашими описаниями в накладную целиком!
                    $arPDF['REQUEST']['fullArray'] = json_encode($arJsonDescr,JSON_PRETTY_PRINT);
                    // посылается один! раз
                    $arPDF['REQUEST']['deliver_before'] = $_POST['TO_DELIVER_BEFORE_DATE'];
                    // включаем внутренний номер и массив с датой первой в серии накладной*
                    $arPDF['REQUEST']['number_internal'] = $number_internal;
                    $arPDF['REQUEST']['number_internal_array'] = getRootInvoice($number_internal);
                    // пишем вычисленную дату
                    $arPDF['REQUEST']['DATE_CREATE'] = $mdate['DATE_CREATE'];

                    // передадим время и дату вызова курьера
                    $arPDF['REQUEST']['IN_DATE_DELIVERY'] = "";
                    $arPDF['REQUEST']['IN_TIME_DELIVERY'] = "";
                    // ********************************************************************

                    // *****

                    // записать файл pdf
                    // Вымпелком   с Возвратом

                    if ( ($arResult['CURRENT_CLIENT'] == ID_VKOM1 ||
                            $arResult['CURRENT_CLIENT'] == ID_VKOM2  ||
                            $arResult['CURRENT_CLIENT'] == ID_VKOM3  ||
                            $arResult['CURRENT_CLIENT'] == ID_VKOM4 ) &&
                        ($WITH_RETURN == 1))
                    {
                        MakeZakazPDFV(encodeArray($arPDF, "cp1251")); //форма для Вымпелкома
                    }elseif (($arResult['CURRENT_CLIENT'] == ID_SUKHOI) || ($arResult['CURRENT_CLIENT'] == ID_TEST)){
                        MakeZakazPDF2(encodeArray($arPDF, "cp1251")); //создаем pdf с накладными для вложения в письмо
                    }else{
                        MakeZakazPDF(encodeArray($arPDF, "cp1251")); //создаем pdf с накладными для вложения в письмо
                    }


                    // файл, который будет приложен к письму
                    $sendFilePath = $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main", "upload_dir")."/pdf/".$number_nakl.".pdf";
                    // чтобы отправить файл с использованием шаблона в битрикс необходимо получить id этого файла, т.е. сделать так чтобы битрикс знал о его существовании
                    $fileId = CFile::SaveFile(
                        [
                            "name" => $number_nakl.".pdf",
                            "tmp_name" => $sendFilePath,
                            "old_file" => "0",
                            "del" => "N",
                            "MODULE_ID" => "",
                            "description" => "",
                        ],
                        'sendfile',
                        false,
                        false
                    );
                    $event = new CEvent;
                    $event->SendImmediate("NEWPARTNER_LK", "S5", $arEventFields, "N", 283, [$fileId]);
                    CFile::Delete($fileId);
                    // unlink($sendFilePath);

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

    }
    /*  -0.4 if begin */
    if ($arResult['MODE'] === 'add')
    {
        //**
        //   file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/filename0.txt', print_r($_POST, true), FILE_APPEND);
        //**

        /* if($USER->isAdmin()){
             dump($arResult);
             exit;
         }*/

        $arResult["COUNTRY_EXEC"] = [
            "RUS" => 605,
            "UKR" => 839,
            "KAZ" => 357,
            "BEL" => 63
        ];
        $_SESSION['DEFAULT_COUNTRY_EXEC'] = getMessage('DEFAULT_COUNTRY_EXEC');

        /*  1 if begin */
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
        /*  2 if begin */
        if ((int)$arResult['CURRENT_CLIENT'] == 0)
        {
            $arResult['OPEN'] = false;
            if ($arResult['ADMIN_AGENT'])
            {
                $arResult["WARNINGS"][] = GetMessage('ERR_OPEN_ADMIN', ['#LINK#' => $arParams['LINK']]);
            }
            else
            {
                $arResult["WARNINGS"][] = GetMessage('ERR_OPEN');
            }
        }
        /*  3 if begin */
        if ($arResult['CURRENT_CLIENT'] > 0)
        {
            $arResult['CURRENT_CLIENT_COEFFICIENT_VW'] = WhatIsGabWeightCompany($arResult['CURRENT_CLIENT']);
            /* if($USER->isAdmin()){
                 dump ($arResult['CURRENT_CLIENT_COEFFICIENT_VW']);
             }*/
            $db_props = CIBlockElement::GetProperty(40, $arResult['CURRENT_CLIENT'], array("sort" => "asc"), array("CODE"=>"INN"));

            if($ar_props = $db_props->Fetch())
            {
                $arResult['CURRENT_CLIENT_INN'] = $ar_props["VALUE"];
            }
            if ($arParams['TYPE'] == 53)
            {
                $res = CIBlockElement::GetList(["name"=>"asc"], ["IBLOCK_ID"=>40, "ACTIVE"=>"Y",
                    'PROPERTY_BY_AGENT' => $arResult['CURRENT_CLIENT']], false, false,
                    ["ID", "NAME"]);
                while($ob = $res->GetNextElement())
                {
                    $arFields = $ob->GetFields();
                    $arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT'][$arFields['ID']] = $arFields['NAME'];
                }
            }
            else
            {
                if ((count($arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT_VALUE']) > 0) &&
                    (is_array($arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT_VALUE'])))
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

        $arSettings = [];
        $settingsJson = $arResult['AGENT']['PROPERTY_ACCOUNT_LK_SETTINGS_VALUE']['TEXT'];
        if (strlen($settingsJson))
        {
            $arSettings = json_decode(htmlspecialcharsBack($settingsJson), true);
        }

        $arResult['USER_SETTINGS'] = $arSettings[$arResult["USER_ID"]];

        $current_N = date('N', strtotime("+1 day"));
        //*11111111111
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/filename_current_N.txt', $current_N, FILE_APPEND);
        //*
        /*  4 if begin */
        if ((int)$arResult['USER_SETTINGS']['DATE_CALLCOURIER'] == 2) {
            if ($current_N == 5)
                $date_callcourier = date('d.m.Y', strtotime("+3 day"));

            elseif ($current_N == 6)
                //$date_callcourier = date('d.m.Y', strtotime("+2 day"));
                $date_callcourier = "";
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
                //$date_callcourier = date('d.m.Y', strtotime("+2 day"));
                $date_callcourier = "";
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
        /*  5 if begin */
        $defaults_sender_data = [];
        if ((int)$arResult['USER_SETTINGS']['SENDER_DEFAULT'] > 0) {
            $res = CIBlockElement::GetList(
                ["id" => "desc"],
                ["IBLOCK_ID" => 84, "ID" => (int)$arResult['USER_SETTINGS']['SENDER_DEFAULT'], "PROPERTY_CREATOR" => $agent_id],
                false,
                false,
                [
                    "ID",
                    "NAME",
                    "PROPERTY_NAME",
                    "PROPERTY_PHONE",
                    "PROPERTY_CITY",
                    "PROPERTY_INDEX",
                    "PROPERTY_ADRESS",
                ]
            );
            if ($ob = $res->GetNextElement())
            {
                $r = $ob->GetFields();
                $r['PROPERTY_CITY'] = GetFullNameOfCity($r['PROPERTY_CITY_VALUE']);
                $defaults_sender_data = $r;
            }
        }
        $arResult['DEAULTS'] = array(
            'callcourier' => ($arResult['USER_SETTINGS']['CALLCOURIER'] == 'yes') ? 'yes' : '',
            'callcourierdate' => $date_callcourier,
            'callcouriertime_from' => '10:00',
            'callcouriertime_to' => '18:00',
            'PLACES' => 1,
            'TYPE_DELIVERY' => ((int)$arResult['USER_SETTINGS']['TYPE_DELIVERY'] > 0) ? (int)$arResult['USER_SETTINGS']['TYPE_DELIVERY'] : 244,
            'TYPE_PACK' => ((int)$arResult['USER_SETTINGS']['TYPE_PACK'] > 0) ? (int)$arResult['USER_SETTINGS']['TYPE_PACK'] : 246,
            'WHO_DELIVERY' => ((int)$arResult['USER_SETTINGS']['WHO_DELIVERY'] > 0) ? (int)$arResult['USER_SETTINGS']['WHO_DELIVERY'] : 248,
            'TYPE_PAYS' => ((int)$arResult['USER_SETTINGS']['TYPE_PAYS'] > 0) ? (int)$arResult['USER_SETTINGS']['TYPE_PAYS'] : 251,
            'PAYMENT' => ((int)$arResult['USER_SETTINGS']['PAYMENT'] > 0) ? (int)$arResult['USER_SETTINGS']['PAYMENT'] : 256,
            'WEIGHT' => '0,1',
            'CHOICE_COMPANY' => ((int)$arResult['USER_SETTINGS']['CHOICE_COMPANY'] == 2) ? 2 : 1,
            'MERGE_SENDERS' => ($arResult['USER_SETTINGS']['MERGE_SENDERS'] == 'Y') ? 'Y' : 'N',
            'MERGE_RECIPIENTS' => ($arResult['USER_SETTINGS']['MERGE_RECIPIENTS'] == 'Y') ? 'Y' : 'N',
            'COMPANY_SENDER_ID' => $defaults_sender_data["ID"],
            'COMPANY_SENDER' => $defaults_sender_data["NAME"],
            'NAME_SENDER' => $defaults_sender_data["PROPERTY_NAME_VALUE"],
            'PHONE_SENDER' => $defaults_sender_data["PROPERTY_PHONE_VALUE"],
            'CITY_SENDER' => $defaults_sender_data["PROPERTY_CITY"],
            'INDEX_SENDER' => $defaults_sender_data["PROPERTY_INDEX_VALUE"],
            'ADRESS_SENDER' => $defaults_sender_data["PROPERTY_ADRESS_VALUE"],
            'COUNTRY_RECIPIENT' => "Россия"

        );
        if ($arParams['TYPE'] == 53)
        {
            $arResult['DEAULTS']['CHOICE_COMPANY'] = ((int)$arResult['USER_SETTINGS']['CHOICE_COMPANY'] == 1) ? 1 : 2;
        }
        //print_r($arResult['USER_SETTINGS']);

        /*  6 if begin */
        //Данные из копируемой заявки

        if ($_GET['copy'] === 'Y' && isset($_GET['numdoc']) && (int)$_GET['copyfrom'] == 0){
            $num_doc = htmlspecialcharsEx($_GET['numdoc']);
            $ar_dev_sequence = convArrayToUTF($num_doc);

            $ar_params_json = [
                'NumDoc' => trim($num_doc)
            ];

            $result = $client->GetDocInfo($ar_params_json);
            $m_res = $result->return;
            $obj_res = json_decode($m_res, true);
            $obj_res = arFromUtfToWin($obj_res);
            if($obj_res['ПризнакТипДоставки'] === 'Стандарт'){
                $prop_type_id = 244;
            }elseif($obj_res['ПризнакТипДоставки'] === 'Экспресс'){
                $prop_type_id = 243;
            }elseif($obj_res['ПризнакТипДоставки'] === 'Экспресс 8'){
                $prop_type_id = 338;
            }elseif($obj_res['ПризнакТипДоставки'] === 'Экспресс 4'){
                $prop_type_id = 346;
            }elseif($obj_res['ПризнакТипДоставки'] === 'Экспресс 2'){
                $prop_type_id = 345;
            }

            if($obj_res['ПризнакПлательщик'] === 'Отправитель'){
                $prop_dev_pay = 251;
            }elseif($obj_res['ПризнакПлательщик'] === 'Получатель'){
                $prop_dev_pay = 252;
            }elseif($obj_res['ПризнакПлательщик'] === 'Другой'){
                $prop_dev_pay = 253;
            }

            if($obj_res['СпециальныеУсловия'] === 'По адресу'){
                $prop_who_dev = 248;
            }elseif($obj_res['СпециальныеУсловия'] === 'До востребования'){
                $prop_who_dev = 249;
            }elseif($obj_res['СпециальныеУсловия'] === 'Лично в руки'){
                $prop_who_dev = 250;
            }

            if($obj_res['ПризнакТипОплаты'] === 'Наличными'){
                $prop_pay = 255;
            }elseif($obj_res['ПризнакТипОплаты'] === 'Безналичные'){
                $prop_pay = 256;
            }

            if($obj_res['ПризнакДокументы'] === '1'){
                $prop_type_pack = 246;
            }else{
                $prop_type_pack = 247;
            }

            $id_city_send = GetCityId($obj_res['ГородПолучателя']);
            $id_city_rec = GetCityId($obj_res['ГородОтправителя']);

            $arResult['DEAULTS']['COMPANY_SENDER'] = $obj_res['ВыборОтправителя'];
            $arResult['DEAULTS']['NAME_SENDER'] = $obj_res['ФамилияОтправителя'];
            $arResult['DEAULTS']['PHONE_SENDER'] = $obj_res['ТелефонОтправителя'];
            $arResult['DEAULTS']['CITY_SENDER'] = GetFullNameOfCity($id_city_send);
            $arResult['DEAULTS']['INDEX_SENDER'] = $obj_res['ИндексОтправителя'];
            $arResult['DEAULTS']['ADRESS_SENDER'] = $obj_res['АдресОтправителя'];
            $arResult['DEAULTS']['COMPANY_RECIPIENT'] = $obj_res['ВыборПолучателя'];
            $arResult['DEAULTS']['NAME_RECIPIENT'] = $obj_res['ФамилияПолучателя'];
            $arResult['DEAULTS']['PHONE_RECIPIENT'] = $obj_res['ТелефонПолучателя'];
            $arResult['DEAULTS']['CITY_RECIPIENT'] = GetFullNameOfCity($id_city_rec);
            $arResult['DEAULTS']['COUNTRY_RECIPIENT'] = $obj_res['СтранаПолучателя'];
            $arResult['DEAULTS']['INDEX_RECIPIENT'] = $obj_res['ИндексПолучателя'];
            $arResult['DEAULTS']['ADRESS_RECIPIENT'] = $obj_res['АдресПолучателя'];
            $arResult['DEAULTS']['TYPE_DELIVERY'] = $prop_type_id;
            $arResult['DEAULTS']['TYPE_PACK'] = $prop_type_pack;
            $arResult['DEAULTS']['WHO_DELIVERY'] = $prop_who_dev;
            $arResult['DEAULTS']['PAYMENT'] = $prop_pay;
            $arResult['DEAULTS']['TYPE_PAYS'] = $prop_dev_pay;
            $arResult['DEAULTS']['PAYS'] = $obj_res['ПризнакПлательщик'];
            $arResult['DEAULTS']['WHOSE_ORDER'] = $obj_res['ЧейЗаказ'];


        }

        if (($_GET['copy'] === 'Y') && ((int)$_GET['copyfrom'] > 0))
        {
            $filter = array("IBLOCK_ID" => 83, "ID" => (int)$_GET['copyfrom'], "PROPERTY_CREATOR" => $arResult['CURRENT_CLIENT']);
            if ($arParams['TYPE'] == 53)
            {
                unset($filter['PROPERTY_CREATOR']);
            }
            $fields = [
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
                "PROPERTY_PAYS",
                "PROPERTY_TYPE_PAYS",
                "PROPERTY_WHOSE_ORDER",
            ];
            if($arResult['CURRENT_CLIENT'] == ID_SUKHOI){
                $fields =  array_merge($fields, [
                    "PROPERTY_TO_DELIVER_BEFORE_DATE",         /* доставить до даты */
                    "PROPERTY_INSTRUCTIONS",                   /* специальные инструкции */
                    "PROPERTY_PACK_DESCRIPTION",               /* описание отправления */
                    "PROPERTY_DIMENSIONS",                     /* габариты  */
                    "PROPERTY_TOTAL_GABWEIGHT",                /* объемный вес */
                    "PROPERTY_WEIGHT",                         /* вес */
                    "PROPERTY_PLACES",                         /* мест */
                    "PROPERTY_IN_DATE_DELIVERY",               /* доставить в дату */
                    "PROPERTY_IN_TIME_DELIVERY",
                ]);
            }


            $res = CIBlockElement::GetList(
                ["id" => "desc"],
                $filter,
                false,
                false,
                $fields
            );

            $arrvrem = [];
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
                $arResult['DEAULTS']['COUNTRY_RECIPIENT'] = "Россия";
                $arResult['DEAULTS']['INDEX_RECIPIENT'] = $r['PROPERTY_INDEX_RECIPIENT_VALUE'];
                $arResult['DEAULTS']['ADRESS_RECIPIENT'] = $r['PROPERTY_ADRESS_RECIPIENT_VALUE']['TEXT'];
                $arResult['DEAULTS']['TYPE_DELIVERY'] = $r['PROPERTY_TYPE_DELIVERY_ENUM_ID'];
                $arResult['DEAULTS']['TYPE_PACK'] = $r['PROPERTY_TYPE_PACK_ENUM_ID'];
                $arResult['DEAULTS']['WHO_DELIVERY'] = $r['PROPERTY_WHO_DELIVERY_ENUM_ID'];
                $arResult['DEAULTS']['PAYMENT'] = $r['PROPERTY_PAYMENT_ENUM_ID'];
                $arResult['DEAULTS']['TYPE_PAYS'] = $r['PROPERTY_TYPE_PAYS_ENUM_ID'];
                $arResult['DEAULTS']['PAYS'] = $r['PROPERTY_PAYS'];
                $arResult['DEAULTS']['WHOSE_ORDER'] = $r['PROPERTY_WHOSE_ORDER_VALUE'];
                if($arResult['CURRENT_CLIENT'] == ID_SUKHOI){
                    $arResult['DEAULTS']['TO_DELIVER_BEFORE_DATE'] = $r['PROPERTY_TO_DELIVER_BEFORE_DATE_VALUE'];
                    $arResult['DEAULTS']['INSTRUCTIONS'] = $r['PROPERTY_INSTRUCTIONS_VALUE']['TEXT'];
                    $arResult['DEAULTS']['PACK_DESCRIPTION'] =  json_decode(htmlspecialcharsback($r['PROPERTY_PACK_DESCRIPTION_VALUE'], true));
                    $arResult['DEAULTS']['DIMENSIONS'] = $r['PROPERTY_DIMENSIONS_VALUE'];
                    $arResult['DEAULTS']['TOTAL_GABWEIGHT'] = $r['PROPERTY_TOTAL_GABWEIGHT_VALUE'];
                    $arResult['DEAULTS']['TOTAL_WEIGHT'] = $r['PROPERTY_WEIGHT_VALUE'];
                    $arResult['DEAULTS']['PLACES'] = $r['PROPERTY_PLACES_VALUE'];
                    $arResult['DEAULTS']['IN_DATE_DELIVERY'] = $r['PROPERTY_IN_DATE_DELIVERY_VALUE'];
                    $arResult['DEAULTS']['IN_TIME_DELIVERY'] = $r['PROPERTY_IN_TIME_DELIVERY_VALUE'];
                }

                $arrvrem[] = $r;
            }
        }
        if($arResult['CURRENT_CLIENT'] == ID_SUKHOI){
            // dump($arResult['DEAULTS']['PACK_DESCRIPTION']);
            foreach($arResult['DEAULTS']['PACK_DESCRIPTION'] as $key=>$value){
                if(!$arResult['DEAULTS']['PACK_DESCRIPTION'][$key]->name){
                    unset($arResult['DEAULTS']['PACK_DESCRIPTION'][$key]);
                }else{
                    $arResult['DEAULTS']['PACK_DESCRIPTION'][$key]->name = iconv("utf-8", "windows-1251",  $arResult['DEAULTS']['PACK_DESCRIPTION'][$key]->name );
                    $arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][] = [
                        "name" => $arResult['DEAULTS']['PACK_DESCRIPTION'][$key]->name,
                        "place" => $arResult['DEAULTS']['PACK_DESCRIPTION'][$key]->place,
                        "weight" => $arResult['DEAULTS']['PACK_DESCRIPTION'][$key]->weight,
                        "size" => $arResult['DEAULTS']['PACK_DESCRIPTION'][$key]->size,
                    ];
                }
            }
            // dump($arResult['DEAULTS']['PACK_DESCRIPTION_VALUE']);
        }
        //Данные из копируемой заявки end
        /*  7 if begin */
        if ((isset($_POST['add'])) || (isset($_POST['add-print'])) || (isset($_POST['add_ctrl'])))
        {
            /* if ($USER->isAdmin()){
                 dump($_POST);
                 die();
             }*/

            if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
            {
                $_POST = [];
                $arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
            }
            else
            {
                $arPostLogsVal = [];
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


                // если более 255 символов
                if (strlen($_POST['INSTRUCTIONS']) > 255)
                {
                    $arResult["ERR_FIELDS"]["INSTRUCTIONS"] = 'has-error err08inst';
                }

                // --------------------------------------------------------------------
                if (($arResult['CURRENT_CLIENT'] == ID_TEST) || ($arResult['CURRENT_CLIENT'] == ID_SUKHOI)){


                    // посчитать сколько этих номеров у меня в накладных для сухого 83 блок
                    $rsOffers = CIBlockElement::GetList(["PRICE"=>"ASC"], ['IBLOCK_ID' => 83,
                        'PROPERTY_764' => $_POST['InternalNumber']]);
                    $arCnt = [];
                    while ($arOffer = $rsOffers->GetNext()){
                        $arCnt[] = $arOffer["ID"];
                    }

                    // проверить
                    //COMPANY_SENDER

                    // выводим сколько номеров найдено
                    //   file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/cnt.txt', count($arCnt), FILE_APPEND);
                    //

                    // *******************
                    // тестовый внутренний номер add
                    if ((false !== strpos($_POST['InternalNumber'], "999999999"))|| (trim($_POST['InternalNumber'])=='') || (count($arCnt)!=0)){
                        $arResult["ERR_FIELDS"]["INNER_NUMBER"] = 'has-error err081';
                    }
                }

                /* если не выбран Центр затрат для Абсолют страхование */
                if ($arResult['CURRENT_CLIENT'] == ID_ABSOLUT){
                    if(empty($_POST['CENTER_EXPENSES'])){
                        $arResult["ERR_FIELDS"]['CENTER_EXPENSES'] = 'has-error';
                    }
                }
                if (!strlen($_POST['NAME_SENDER']))
                {
                    $arResult["ERR_FIELDS"]["NAME_SENDER"] = 'has-error err08';
                }
                if (!strlen($_POST['PHONE_SENDER']))
                {
                    $arResult["ERR_FIELDS"]["PHONE_SENDER"] = 'has-error err09';
                }
                if (!strlen($_POST['COMPANY_SENDER']))
                {
                    $arResult["ERR_FIELDS"]["COMPANY_SENDER"] = 'has-error err10';
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
                    if(preg_match("/^(.+,\s){2}.+$/", trim($_POST['CITY_RECIPIENT'])))
                    {
                        $city_recipient = GetCityId(trim($_POST['CITY_RECIPIENT']));
                        if (!$city_recipient){
                            $city_recipient=(int)$_POST['CITY_RECIPIENT_ID'];
                            $el = new CIBlockElement;
                            $res = $el->Update($city_recipient, ["ACTIVE"=>"Y"]);
                        }
                    }else{
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
                    $arResult["ERR_FIELDS"]["PAYMENT"] = 'has-error err011';
                }
                if (!$_POST['TYPE_PAYS'])
                {
                    $arResult["ERR_FIELDS"]["TYPE_PAYS"] = 'has-error err012';
                }
                if($_POST['callcourier'] === 'yes' && $_POST['callcourierdate']){
                    $dd = strtotime($_POST['callcourierdate']);
                    if(date('N', $dd) == 7){
                        $arResult["ERR_FIELDS"]['callcourierdate'] = 'has-error';
                    }
                }


                if (((int)$_POST['PAYMENT'] == 256) && (((int)$_POST['TYPE_PAYS'] == 252) || ((int)$_POST['TYPE_PAYS'] == 253)))
                {
                   if ((count($arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT_VALUE']) > 0) && (is_array($arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT_VALUE'])))
                    {
                        //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/filenamepost000111.txt', "2<<<".$_POST['PAYS'].">>>", FILE_APPEND);
                        // или вообще не определено
                        if ((!(isset($_POST['WHOSE_ORDER']))) || ((int)$_POST['WHOSE_ORDER'] == 0))
                        {
                            //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/filenamepost000111.txt', "2.1<<<".$_POST['PAYS'].">>>", FILE_APPEND);
                            //$arResult["ERR_FIELDS"]["WHOSE_ORDER"] = 'has-error err013';

                            // Может быть определено произвольное поле оплачивающего клиента
                            if ((!strlen($_POST['PAYS'])) && ((int)$_POST['TYPE_PAYS'] == 253))
                            {
                                $arResult["ERR_FIELDS"]["WHOSE_ORDER"] = 'has-error err014.1';
                            }
                            else
                            {
                                //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/filenamepost000111.txt', "2.2<<<".$_POST['PAYS'].">>>", FILE_APPEND);
                                $pays_text = deleteTabs($_POST['PAYS']);
                            }
                            //------------------------------------------------------------
                        }
                        else
                        {
                            //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/filenamepost000111.txt', "3<<<".$_POST['PAYS'].">>>", FILE_APPEND);
                            $pays_text = $arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT'][(int)$_POST['WHOSE_ORDER']];
                            $WHOSE_ORDER = (int)$_POST['WHOSE_ORDER'];
                        }
                    }
                    else
                    {
                        //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/filenamepost000111.txt', "0<<<".$_POST['PAYS'].">>>", FILE_APPEND);
                        if ((!strlen($_POST['PAYS'])) && ((int)$_POST['TYPE_PAYS'] == 253))
                        {
                            $arResult["ERR_FIELDS"]["WHOSE_ORDER"] = 'has-error err014';
                        }
                        else
                        {
                            //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/filenamepost000111.txt', "1<<<".$_POST['PAYS'].">>>", FILE_APPEND);
                            $pays_text = deleteTabs($_POST['PAYS']);
                        }
                    }
                }

                // запишем что сюда приносится-- $_POST
                //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/filenamepost000111.txt', "-----".$pays_text."-----------", FILE_APPEND);
                // -----------------------------


                $arJsonDescr = [];
                $total_place = 0;
                $total_weight = 0;
                $total_gabweight = 0;
                $GoodsNamestr = '';
                foreach ($_POST['pack_description'] as $description_str)
                {
                    $sizes = [];
                    $GoodsNamestr .= ' ' . $description_str['name'];
                    foreach ($description_str['size'] as $sz)
                    {
                        $sizes[] = (float)str_replace(',', '.', $sz);
                    }
                    $arCurStr = [
                        'name' => iconv('windows-1251','utf-8',$description_str['name']),
                        'place' => (int)$description_str['place'],
                        'weight' => (float)str_replace(',', '.', $description_str['weight']),
                        'size' => $sizes,
                        'gabweight' => (($sizes[0]*$sizes[1]*$sizes[2])/$arResult['CURRENT_CLIENT_COEFFICIENT_VW'])
                    ];
                    $total_place = $total_place + $arCurStr['place'];
                    $total_weight = $total_weight + $arCurStr['weight'];
                    $total_gabweight = $total_gabweight + $arCurStr['gabweight'];
                    $arJsonDescr[] = $arCurStr;
                }
                if (($total_place <= 0) || ($total_place >= 10000))
                {
                    $arResult["ERR_FIELDS"]["PLACES"] = 'has-error';
                }
                if (($total_weight <= 0) || ($total_weight >= 10000))
                {
                    $arResult["ERR_FIELDS"]["WEIGHT"] = 'has-error';
                }
                if ($total_gabweight >= 10000)
                {
                    $arResult["ERR_FIELDS"]["SIZE"] = 'has-error';
                }

                $arJsonGoods = [];
                $arJsonGoodsSource = [];

                foreach ($_POST['goods'] as $goods_str)
                {

                    $arJsonGoods[] = [
                        'GoodsName' => iconv('windows-1251','utf-8',$goods_str['name']),
                        'Amount' => (int)$goods_str['amount'],
                        'Price' => (float)str_replace(',', '.', $goods_str['price']),
                        'Sum' => (float)str_replace(',', '.', $goods_str['sum']),
                        'SumNDS' => (float)str_replace(',', '.', $goods_str['sumnds']),
                        'PersentNDS' => (int)$goods_str['persentnds']
                    ];
                    $arJsonGoodsSource[] = [
                        'GoodsName' => iconv('windows-1251','utf-8',$goods_str['name']),
                        'Amount' => iconv('windows-1251','utf-8',$goods_str['amount']),
                        'Price' => iconv('windows-1251','utf-8',$goods_str['price']),
                        'Sum' => iconv('windows-1251','utf-8',$goods_str['sum']),
                        'SumNDS' => iconv('windows-1251','utf-8',$goods_str['sumnds']),
                        'PersentNDS' => iconv('windows-1251','utf-8',$goods_str['persentnds'])
                    ];
                }


                if (count($arResult["ERR_FIELDS"]) == 0)
                {
                    /* добавление получателя  */

                    $filter_search_recipient = [
                        "IBLOCK_ID" => 84,
                        "PROPERTY_CREATOR" => $arResult['CURRENT_CLIENT'],
                        "NAME" => NewQuotes($_POST['COMPANY_RECIPIENT']),
                        "PROPERTY_CITY" => $city_recipient,
                        "PROPERTY_ADRESS" => NewQuotes($_POST['ADRESS_RECIPIENT']),
                        "PROPERTY_TYPE" => 260
                    ];

                    $fields = [
                        "ID","NAME","PROPERTY_CREATOR"
                    ];


                    if ($arResult['USER_SETTINGS']['MERGE_RECIPIENTS'] === 'Y')
                    {
                        unset($filter_search_recipient['PROPERTY_TYPE']);
                    }
                    $res = CIBlockElement::GetList(
                        ["ID" =>"desc"],
                        $filter_search_recipient,
                        false,
                        false,
                        $fields
                    );
                    //TODO [x]Добавить дату последнего использования
                    if (!$ob = $res->GetNextElement())
                    {

                        $el2 = new CIBlockElement;
                        $arLoadProductArray2 = [
                            "MODIFIED_BY" => $USER->GetID(),
                            "IBLOCK_SECTION_ID" => false,
                            "IBLOCK_ID" => 84,
                            "PROPERTY_VALUES" => [
                                579 => $arResult['CURRENT_CLIENT'],
                                574 => NewQuotes($_POST['NAME_RECIPIENT']),
                                575 => NewQuotes($_POST['PHONE_RECIPIENT']),
                                576 => $city_recipient,
                                577 => $_POST['INDEX_RECIPIENT'],
                                578 => NewQuotes($_POST['ADRESS_RECIPIENT']),
                                580 => 260,
                                668 => $arResult['USER_IN_BRANCH'] ? $arResult['CURRENT_BRANCH'] : false,
                                713 => date('d.m.Y H:i:s')
                            ],
                            "NAME" => NewQuotes($_POST['COMPANY_RECIPIENT']),
                            "ACTIVE" => "Y"
                        ];
                        //->Add
                        $rec_id = $el2->Add($arLoadProductArray2);
                        if($rec_id>0){
                            $arLoadProductArray2['ID'] = $rec_id;
                            AddToLogs('SaveRecipientId', $arLoadProductArray2);
                        }else{
                            $errLog = [
                                $mess => "Ошибка записи",
                                $data => $arLoadProductArray2
                            ];
                            AddToLogs('SaveRecipientId',$errLog);
                        }
                    }
                    else
                    {
                        $arFields = $ob->GetFields();
                        CIBlockElement::SetPropertyValuesEx($arFields['ID'], 84, [713 => date('d.m.Y H:i:s')]);
                    }

                    if ((strlen(trim($_POST['COMPANY_SENDER']))) && ((int)$_POST['company_sender_id'] == 0))
                    {
                        $filter_search_senders = [
                            "IBLOCK_ID" => 84,
                            "PROPERTY_CREATOR" => $arResult['CURRENT_CLIENT'],
                            "NAME" => NewQuotes($_POST['COMPANY_SENDER']),
                            "PROPERTY_CITY" => $city_sender,
                            "PROPERTY_ADRESS" => NewQuotes($_POST['ADRESS_SENDER']),
                            "PROPERTY_TYPE" => 259
                        ];
                        if ($arResult['USER_SETTINGS']['MERGE_SENDERS'] == 'Y')
                        {
                            unset($filter_search_senders['PROPERTY_TYPE']);
                        }
                        $res = CIBlockElement::GetList(
                            ["ID" =>"desc"],
                            $filter_search_senders,
                            false,
                            ["nTopCount" => 1],
                            ["ID"]
                        );

                        if (!$ob = $res->GetNextElement())
                        {
                            $el2 = new CIBlockElement;
                            $arLoadProductArray2 = [
                                "MODIFIED_BY" => $USER->GetID(),
                                "IBLOCK_SECTION_ID" => false,
                                "IBLOCK_ID" => 84,
                                "PROPERTY_VALUES" => [
                                    579 => $arResult['CURRENT_CLIENT'],
                                    574 => NewQuotes($_POST['NAME_SENDER']),
                                    575 => NewQuotes($_POST['PHONE_SENDER']),
                                    576 => $city_sender,
                                    577 => $_POST['INDEX_SENDER'],
                                    578 => NewQuotes($_POST['ADRESS_SENDER']),
                                    580 => 259,
                                    713 => date('d.m.Y H:i:s')
                                ],
                                "NAME" => NewQuotes($_POST['COMPANY_SENDER']),
                                "ACTIVE" => "Y"
                            ];

                            //->Add
                            $rec_id = $el2->Add($arLoadProductArray2);
                        }
                        else
                        {
                            $arFields = $ob->GetFields();
                            CIBlockElement::SetPropertyValuesEx($arFields['ID'], 84,
                                [713 => date('d.m.Y H:i:s')]);
                        }
                    }

                /* получить стоимость по умолчанию всем кроме инд. прайс */

                if($arResult['UK'] == ID_UK_NP) {
                    /*if($arResult['CURRENT_CLIENT'] == ID_TEST){
                        dump(ID_UK_NP);
                        die();
                    }*/
                    /* получить настройки клиента из 1с GetClientInfo */
                   // $client = soap_inc();
                    $arParamsJsonIdClient = [
                        'INN' => $arResult['CURRENT_CLIENT_INN'],
                    ];
                    $result = $client->GetClientInfo($arParamsJsonIdClient);
                    $mResult = $result->return;
                    $res = json_decode($mResult, true);
                    $res = arFromUtfToWin($res);
                    if ($res['IndividualPrice'] == '1') {
                        $arResult['IndividualPrice'] = false;
                    } else {
                        $arResult['IndividualPrice'] = true; //  считать по общему прайсу
                    }
                    /* end настройки */

                    if($arResult['IndividualPrice']){
                         $cr = trim($_POST['CITY_RECIPIENT']);
                         $cr_arr = explode(',', $cr);
                         $cr_post = $cr_arr[0];
                         $rr_post = $cr_arr[1];
                         $cs = trim($_POST['CITY_SENDER']);
                         $cs_arr = explode(',', $cs);
                         $cs_post = $cs_arr[0];
                         $rs_post = $cs_arr[1];

                         if($total_weight >= $total_gabweight){
                             $tw = $total_weight;
                         }else{
                             $tw = $total_gabweight;
                         }
                         $post_in = [
                             'city_sender' => $cs_post,
                             'region_sender' => $rs_post,
                             'city_recipient' => $cr_post,
                             'region_recipient' => $rr_post,
                             'weight' => $tw
                         ];
                         $get_in = [
                             'api' => 'Y',
                             'inner' => 'Y',
                         ];

                         $arR = new NPCalc($post_in, $get_in, true );
                         $sum_dev = $arR->Res['TARIF_ITOG'];

                     }
                    else
                    {
                        //  сумма доставки для клиентов с инд.прайсом
                        $inn_val = $arResult['AGENT']['PROPERTY_INN_VALUE'];
                        $username =  iconv('windows-1251','utf-8',"DMSUser");
                        $password =  iconv('windows-1251','utf-8',"1597534682");
                        $host_api_sum = "http://92.42.209.242/sd_msk/hs/Delivery/Account/7717739535/$inn_val/GetTarif?ves=$total_weight&vesv=$total_gabweight&idcity1=$city_sender&idcity2=$city_recipient&deliverytype=c";
                        $url_for_sum_dev = iconv('windows-1251','utf-8', $host_api_sum);

                        if ($ch = curl_init()){
                            curl_setopt($ch, CURLOPT_URL, $url_for_sum_dev);
                            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $output = curl_exec($ch);
                            curl_close($ch);
                            if(preg_match('/^[0-9]+(\.([0-9]){2})?$/', json_decode($output))){
                                $sum_dev = json_decode($output);
                                $sum_dev =  (float)iconv('utf-8','windows-1251',$sum_dev);

                            }else{
                                $sum_dev = 0;
                            }
                        }else{
                            $sum_dev = 0;
                        }
                    }
                   }

                    /* получить стоимость для Технопарка Сколково и Абсолют страхование (56103010)*/

                    /*if($arResult['CURRENT_CLIENT'] == ID_SKOLKOVO ||
                        $arResult['CURRENT_CLIENT'] == ID_ABSOLUT
                    ){

                        $username =  iconv('windows-1251','utf-8',"DMSUser");
                        $password =  iconv('windows-1251','utf-8',"1597534682");
                        if ($arResult['CURRENT_CLIENT'] == ID_SKOLKOVO){
                            $host_api = "http://92.42.209.242/sd_msk/hs/Delivery/Account/7717739535/7701902970/GetTarif?ves=$total_weight&vesv=$total_gabweight&idcity2=$city_recipient&deliverytype=c";
                        }
                        if ($arResult['CURRENT_CLIENT'] == ID_ABSOLUT){
                            $host_api = "http://92.42.209.242/sd_msk/hs/Delivery/Account/7717739535/7728178835/GetTarif?ves=$total_weight&vesv=$total_gabweight&idcity2=$city_recipient&deliverytype=c";
                        }

                        $url = iconv('windows-1251','utf-8',$host_api);

                        if ($ch = curl_init()){
                            curl_setopt($ch, CURLOPT_URL, $url);
                            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $output = curl_exec($ch);
                            curl_close($ch);
                            if(preg_match('/^[0-9]+(\.([0-9]){2})?$/', json_decode($output))){
                                $sum_dev = json_decode($output);
                                $sum_dev =  (float)iconv('utf-8','windows-1251',$sum_dev);
                            }else{
                                $sum_dev = 0;
                            }
                        }else{
                            $sum_dev = 0;
                        }
                    }*/

                    // не считать сумму для Сухого
                    if($arResult['CURRENT_CLIENT'] == ID_SUKHOI) $sum_dev = 0;

                    /* сохранить накладную */
                    //$id_in = MakeInvoiceNumber(83, 7, '90-');
                    if (strlen(trim($_POST['NUMBER'])))
                    {
                        $id_in = [
                            'max_id' => 0
                        ];

                        $number_n = trim($_POST['NUMBER']);
                        $number_nakl = (string)$number_n;
                        if(preg_match('/[а-яё]+-/i', $number_nakl)){
                            $nm = preg_replace('/[а-яё]+-/i','90-', $number_nakl);
                            $number_nakl_pdf = trim($nm);
                            $number_nakl = $number_nakl_pdf;
                        }
                        if($arResult['CURRENT_CLIENT'] === '26133129'){
                            $number_nakl_pdf = preg_replace('/^(.+){1}(([0-9]+)(-[0-9]+)?)$/','$2', $number_nakl);
                            $number_nakl_pdf = trim($number_nakl_pdf);
                        }
                    }
                    else
                    {
                        $id_in = MakeInvoiceNumberNew(1, 7, '90-');
                        $number_nakl = $id_in['number'];
                    }
                    // сумма доставки для клиентов с инд. прайсом - лог url в 1с
                    AddToLogs('url_for_sum', [ $number_nakl => $url_for_sum_dev ]);
                    $number_internal = trim($_POST['InternalNumber']);
                    $date_to = deleteTabs($_POST['TO_DELIVER_BEFORE_DATE']);
                    // добавить в поле спец. инструкций
                    if($date_to){
                        $spec_instr = ' '.NewQuotes($_POST['INSTRUCTIONS']).'  '.'ДОСТАВИТЬ ДО ДАТЫ: '.$date_to.'. ';
                    }else{
                        $spec_instr = NewQuotes($_POST['INSTRUCTIONS']);
                    }

                    if(isset($_POST['TRANSPORT_TYPE'])){
                        $TRANSPORT_TYPE = 1;
                    }else{
                        $TRANSPORT_TYPE = 0;
                    }

                    if(isset($_POST['WITH_RETURN'])){
                        $WITH_RETURN = 1;
                    }else{
                        $WITH_RETURN = 0;
                    }
                    if(isset($_POST['NOTE'])){
                        $NOTE = NewQuotes($_POST['NOTE']);
                    }else{
                        $NOTE = '';
                    }
                    $prop = [
                        559 => $_POST['WHO_DELIVERY'],
                        557 => $_POST['TYPE_DELIVERY'],
                        558 => $_POST['TYPE_PACK'],
                        561 => deleteTabs($_POST['IN_TIME_DELIVERY']),
                        564 => $_POST['PAYMENT'],
                        567 => $total_place,
                        568 => $total_weight,
                        569 => $_POST['DIMENSIONS'],
                        572 => '257',
                        639 => $arResult['BRANCH_AGENT_BY'],
                        640 => $arResult['CLIENT_CONTRACT'],
                        641 => $arResult['CURRENT_BRANCH'],
                        679	=> 1,
                        682 => json_encode($arJsonDescr),
                        724 => json_encode($arJsonGoods),
                        737 => $WHOSE_ORDER,
                        764 => $number_internal,
                        787 => $total_gabweight,
                        861 => $TRANSPORT_TYPE,
                        978 => $NOTE,
                        979 => $sum_dev,
                        981 => $_POST['CENTER_EXPENSES'],
                        982 => trim($_POST['RAND'])
                    ];
                    $PROPERTY_VAL = [
                        545 => $arResult['CURRENT_CLIENT'],
                        544 => $id_in['max_id'],
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
                        562 => $_POST['TYPE_PAYS'],
                        570 => array('VALUE' => array('TYPE' => 'text', 'TEXT' => $spec_instr)),
                        571 => array('VALUE' => array('TYPE' => 'text', 'TEXT' => NewQuotes($_POST['ADRESS_RECIPIENT']))),
                        560 => deleteTabs($_POST['IN_DATE_DELIVERY']),
                        565 => (float)str_replace(',', '.', $_POST['FOR_PAYMENT']),
                        733 => (float)str_replace(',', '.', $_POST['PAYMENT_COD']),
                        566 => (float)str_replace(',', '.', $_POST['COST']),
                        563 => $pays_text,
                        772 => $date_to,
                        987 => trim($GoodsNamestr),

                    ];

                    // Вымпелком
                    if ( ($arResult['CURRENT_CLIENT'] == ID_VKOM1 ||
                            $arResult['CURRENT_CLIENT'] == ID_VKOM2  ||
                            $arResult['CURRENT_CLIENT'] == ID_VKOM3  ||
                            $arResult['CURRENT_CLIENT'] == ID_VKOM4 ))
                    {
                        $vcomm = true;       // метка Служебное для вымпелкома
                    }

                    if ($WITH_RETURN == 1) {
                        /* массив свойств для возвратной накладной */
                        $number_nakl_ret = $number_nakl . '-1';
                        $NAME_RECIPIENT = NewQuotes($_POST['NAME_SENDER']);
                        $PHONE_RECIPIENT = NewQuotes($_POST['PHONE_SENDER']);
                        $COMPANY_RECIPIENT = NewQuotes($_POST['COMPANY_SENDER']);
                        $INDEX_RECIPIENT = deleteTabs($_POST['INDEX_SENDER']);
                        $ADRESS_RECIPIENT = NewQuotes($_POST['ADRESS_SENDER']);
                        $NAME_SENDER = NewQuotes($_POST['NAME_RECIPIENT']);
                        $PHONE_SENDER = NewQuotes($_POST['PHONE_RECIPIENT']);
                        $COMPANY_SENDER = NewQuotes($_POST['COMPANY_RECIPIENT']);
                        $INDEX_SENDER = deleteTabs($_POST['INDEX_RECIPIENT']);
                        $ADRESS_SENDER = NewQuotes($_POST['ADRESS_RECIPIENT']);
                        $CITY_RECIPIENT = $_POST['CITY_SENDER'];
                        $CITY_SENDER = $_POST['CITY_RECIPIENT'];
                        $city_sender_ret = $city_recipient;
                        $city_recipient_ret = $city_sender;
                        $spec_instr_ret = "ВНИМАНИЕ! Это обратный забор при доставке накладной - № $number_nakl";
                        if ($_POST['TYPE_PAYS'] == 251) {
                            $TYPE_PAYS = 252;
                        } elseif ($_POST['TYPE_PAYS'] == 252) {
                            $TYPE_PAYS = 251;
                        } else {
                            $TYPE_PAYS = 253;
                        }
                        $delivery_payer_ret = 'Отправитель';
                        switch ($TYPE_PAYS)
                        {
                            case 251:
                                $delivery_payer_ret = 'Отправитель';
                                break;
                            case 252:
                                $delivery_payer_ret = 'Получатель';
                                break;
                            case 253:
                                $delivery_payer_ret = 'Другой';
                                break;
                        }
                        if (!empty($_POST['IN_DATE_DELIVERY'])) {
                            $IN_DATE = $_POST['IN_DATE_DELIVERY'];
                            $IN_DATE_DELIVERY = date('d.m.Y', strtotime($IN_DATE . "1 day"));
                        }
                        $date_to = deleteTabs($_POST['TO_DELIVER_BEFORE_DATE']);
                        // добавить в поле спец. инструкций
                        $date_to_ret = '';
                        if ($date_to) {
                            $date_to_ret = date('d.m.Y', strtotime($date_to . "1 day"));
                            $spec_instr_ret .= ' ' . 'ДОСТАВИТЬ ДО ДАТЫ: ' . $date_to_ret . '. ';
                        }

                        $PROPERTY_RET = [
                            545 => $arResult['CURRENT_CLIENT'],
                            544 => $id_in['max_id'],
                            546 => $NAME_SENDER,
                            547 => $PHONE_SENDER,
                            548 => $COMPANY_SENDER,
                            549 => $city_sender_ret,
                            550 => $INDEX_SENDER,
                            551 => ['VALUE' => ['TYPE' => 'text', 'TEXT' => $ADRESS_SENDER]],
                            552 => $NAME_RECIPIENT,
                            553 => $PHONE_RECIPIENT,
                            554 => $COMPANY_RECIPIENT,
                            555 => $city_recipient_ret,
                            556 => $INDEX_RECIPIENT,
                            562 => $TYPE_PAYS,
                            570 => ['VALUE' => ['TYPE' => 'text', 'TEXT' => $spec_instr_ret]],
                            571 => ['VALUE' => ['TYPE' => 'text', 'TEXT' => $ADRESS_RECIPIENT]],
                            560 => $IN_DATE_DELIVERY,
                            565 => 0.00,
                            733 => 0.00,
                            566 => 0.00,
                            563 => '',
                            772 => $date_to_ret,
                            983 => $number_nakl,
                            987 => trim($GoodsNamestr),
                            989 => '1'
                        ];

                        $PROPERTY_VALUES_RET = $PROPERTY_RET + $prop;

                    } // end свойства возвратной накладной

                    $PROPERTY_VALUES = $PROPERTY_VAL + $prop;

                    $el = new CIBlockElement;
                    $arLoadProductArray = [
                        "MODIFIED_BY" => $USER->GetID(),
                        "IBLOCK_SECTION_ID" => false,
                        "IBLOCK_ID" => 83,
                        "PROPERTY_VALUES" => $PROPERTY_VALUES,
                        "NAME" => $number_nakl,
                        "ACTIVE" => "Y"
                    ];
                    $i = 0;
                    while($i < 2){
                        $z_nakl_id = $el->Add($arLoadProductArray);
                        if($z_nakl_id){
                            break;
                        }else{
                            sleep(1);
                        }
                        $i++;
                    }

                    /* добавить в базу возвратную накладную */
                    if ($WITH_RETURN == 1 ) {
                        $el_ret = new CIBlockElement;
                        $arLoadProductArrayRet = [
                            "MODIFIED_BY" => $USER->GetID(),
                            "IBLOCK_SECTION_ID" => false,
                            "IBLOCK_ID" => 83,
                            "PROPERTY_VALUES" => $PROPERTY_VALUES_RET,
                            "NAME" => $number_nakl_ret,
                            "ACTIVE" => "Y"
                        ];

                        $i = 0;
                        while($i < 2){
                            $z_nakl_id_ret = $el_ret->Add($arLoadProductArrayRet);
                            if($z_nakl_id_ret){
                                break;
                            }else{
                                sleep(1);
                            }
                            $i++;
                        }


                        if ($_POST['callcourier'] === 'yes'){
                            CIBlockElement::SetPropertyValuesEx($z_nakl_id_ret, 83,
                                [
                                    977=>'Y',
                                    984 => date('d.m.Y', strtotime($_POST['callcourierdate'] . "1 day")),
                                    985 => NewQuotes($_POST['callcourtime_from']),
                                    986 => NewQuotes($_POST['callcourtime_to'])
                                ]);
                        }
                        if (!$z_nakl_id_ret)
                        {
                            $error = $el_ret->LAST_ERROR;
                            AddToLogs('InvAddErrors', ['ERROR' => $error, 'mess' => 'Проблемы с сохранением возвратной накладной']);
                            $arResult['ERRORS'][] = $error;
                        }else{
                            AddToLogs('invoices-return',convArrayToUTF($arLoadProductArray));
                        }
                    }


                    if ($_POST['callcourier'] === 'yes'){
                        CIBlockElement::SetPropertyValuesEx($z_nakl_id, 83,
                            [
                                977=>'Y',
                                984 => NewQuotes($_POST['callcourierdate']),
                                985 => NewQuotes($_POST['callcourtime_from']),
                                986 => NewQuotes($_POST['callcourtime_to'])
                            ]);
                    }

                    //  с Возвратом
                    if ($WITH_RETURN == 1)
                    {
                        $isoffice = '';
                        // метка Служебное для Вымпелкома в возвратной накладной
                        if ($vcomm ){
                             $isoffice = 1;
                            CIBlockElement::SetPropertyValuesEx($z_nakl_id_ret, 83,
                                [
                                   988 => $isoffice
                                ]);
                        }
                        CIBlockElement::SetPropertyValuesEx($z_nakl_id, 83,
                            [
                                980 => $WITH_RETURN,
                             ]);
                    }

                    //->Add

                    if (!$z_nakl_id)
                    {
                        $error = $el->LAST_ERROR;
                        AddToLogs('InvAddErrors', ['ERROR' => $error, 'mess' => 'Проблемы с сохранением накладной']);
                        $arResult['ERRORS'][] = $error;
                    }


                    // возмем дату накладной по $z_nakl_id --
                    // получили дату созданной	накладной
                    $resDate = CIBlockElement::GetList(
                        ["id" => "desc"],
                        ["IBLOCK_ID"=>83, "ID"=>$z_nakl_id],
                        false, false, ["ID" , 'DATE_CREATE']);

                    //  ------------------

                    while($obDate = $resDate->GetNextElement()){
                        $md = $obDate->GetFields();
                        $mdate = $md['DATE_CREATE'];
                    }
                    // так же по возвратной накладной
                    if ($WITH_RETURN == 1 )  {
                        $resDate = CIBlockElement::GetList(
                            ["id" => "desc"],
                            ["IBLOCK_ID"=>83, "ID"=>$z_nakl_id_ret],
                            false, false, ["ID" , 'DATE_CREATE']);

                        //  ------------------

                        while($obDate = $resDate->GetNextElement()){
                            $md = $obDate->GetFields();
                            $mdate_ret = $md['DATE_CREATE'];
                        }
                    }
                    // --------------------------------------

                    $arLog = [
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
                        'FOR_PAYMENT' => (float)str_replace(',', '.', $_POST['FOR_PAYMENT']),
                        'PAYMENT_COD' => (float)str_replace(',', '.', $_POST['PAYMENT_COD']),
                        'COST' => (float)str_replace(',', '.', $_POST['COST']),
                        'PLACES' => $total_place,
                        'WEIGHT' => $total_weight,
                        'DIMENSIONS' => $_POST['DIMENSIONS'],
                        'INSTRUCTIONS' => $spec_instr,
                        'ADRESS_RECIPIENT' => NewQuotes($_POST['ADRESS_RECIPIENT']),
                        'STATE' => 257,
                        'BRANCH_AGENT_BY' => $arResult['BRANCH_AGENT_BY'],
                        'CLIENT_CONTRACT' => $arResult['CLIENT_CONTRACT'],
                        'CURRENT_BRANCH' => $arResult['CURRENT_BRANCH'],
                        'INFORMATION_ON_CREATE'	=> 1,
                        'PACK_DESCRIPTION' => json_encode($arJsonDescr),
                        'PACK_GOODS' => json_encode($arJsonGoods),
                        'PACK_GOODS_SOURSE' => json_encode($arJsonGoodsSource),
                        'TRANSPORT_TYPE' => $TRANSPORT_TYPE,
                        'WITH_RETURN' => $WITH_RETURN,
                        'ISOFFICE' => $isoffice
                    ];
                    AddToLogs('invoices',convArrayToUTF($arLog));
                    ///////////////////////////
                    /* pdf */
                    $sitysender = explode(", ", $_POST['CITY_SENDER']);
                    $s_sender = $sitysender[0];
                    $o_sender = $sitysender[1];
                    $c_sender = $sitysender[2];

                    $sityrecepient = explode(", ", $_POST['CITY_RECIPIENT']);
                    $s_recepient = $sityrecepient[0];
                    $o_recepient = $sityrecepient[1];
                    $c_recepient = $sityrecepient[2];
                    $idlogoprint = GetSettingValue(716, false, $arResult['UK']);

                    $total_place1 = 0;
                    $total_weight1 = 0;
                    $total_gabweight1 = 0;
                    foreach ($arJsonDescr as $description_str1)
                    {
                        $sizes = [];
                        foreach ($description_str1['size'] as $sz)
                        {
                            $sizes1[] = (float)str_replace(',', '.', $sz);
                        }
                        $arCurStr1 = [
                            'name' => iconv('windows-1251','utf-8',$description_str1['name']),
                            'place' => (int)$description_str1['place'],
                            'weight' => (float)str_replace(',', '.', $description_str1['weight']),
                            'size' => $sizes1,
                            //'gabweight' => (($sizes1[0]*$sizes1[1]*$sizes1[2])/150)
                            'gabweight' => (($sizes1[0]*$sizes1[1]*$sizes1[2])/$arResult['CURRENT_CLIENT_COEFFICIENT_VW'])
                        ];
                        $total_place1 = $total_place1 + $arCurStr1['place'];
                        $total_weight1 = $total_weight1 + $arCurStr1['weight'];
                        $total_gabweight1 = $total_gabweight1 + $arCurStr1['gabweight'];

                    }
                    //
                    $payment_type1 = 'Наличные';
                    switch ($_POST['PAYMENT'])
                    {
                        case 255:
                            $payment_type1 = 'Наличные';
                            break;
                        case 256:
                            $payment_type1 = 'Безналичные';
                            break;
                    }
                    $delivery_type1 = 'Стандарт';
                    switch ($_POST['TYPE_DELIVERY'])
                    {
                        case 345:
                            $delivery_type1 = 'Экспресс 2';
                            break;
                        case 346:
                            $delivery_type1 = 'Экспресс 4';
                            break;
                        case 338:
                            $delivery_type1 = 'Экспресс 8';
                            break;
                        case 243:
                            $delivery_type1 = 'Экспресс';
                            break;
                        case 244:
                            $delivery_type1 = 'Стандарт';
                            break;
                        case 245:
                            $delivery_type1 = 'Эконом';
                            break;
                        case 308:
                            $delivery_type1 = 'Склад-Склад';
                            break;
                    }
                    $delivery_payer1 = 'Отправитель';
                    switch ($_POST['TYPE_PAYS'])
                    {
                        case 251:
                            $delivery_payer1 = 'Отправитель';
                            break;
                        case 252:
                            $delivery_payer1 = 'Получатель';
                            break;
                        case 253:
                            $delivery_payer1 = 'Другой';
                            break;
                    }
                    $delivery_condition1 = 'По адресу';
                    switch ($_POST['WHO_DELIVERY'])
                    {
                        case 248:
                            $delivery_condition1 = 'По адресу';
                            break;
                        case 249:
                            $delivery_condition1 = 'До востребования';
                            break;
                        case 250:
                            $delivery_condition1 = 'Лично в руки';
                            break;
                    }

                    $newInstructions = NewQuotes($_POST['INSTRUCTIONS']);
                    //if ($_POST['IN_DATE_DELIVERY'] == '')
                    //{
                    //  поправляем
                    $newInstructions .= '  ВЫЗОВ КУРЬЕРА: '.$_POST['callcourierdate'].' с '.$_POST['callcourtime_from'].' до '.$_POST['callcourtime_to'].'.';
                    if($date_to){
                        $newInstructions .= '  ДОСТАВИТЬ ДО ДАТЫ: '.$date_to.'.';
                    }

                    if (strlen(trim($_POST['callcourcomment'])))
                    {
                        $newInstructions .= ' КОММЕНТАРИЙ КУРЬЕРУ: '.NewQuotes($_POST['callcourcomment']);
                    }
                    //} else {
                    if ($_POST['IN_DATE_DELIVERY'] !=''){
                        $newInstructions .= ' Доставить в дату: '.$_POST['IN_DATE_DELIVERY'];
                    }
                    if ($_POST['IN_TIME_DELIVERY'] !=''){
                        $newInstructions .= ' до часа '.$_POST['IN_TIME_DELIVERY'];
                    }
                    //$newInstructions .= 'Комментарий: '.NewQuotes($_POST['INSTRUCTIONS']);
                    //}

                    if (($arResult['CURRENT_CLIENT'] == ID_SUKHOI) || ($arResult['CURRENT_CLIENT'] == ID_TEST)){
                        $NumberInvoiceSu   = preg_replace ("/(.*)-(.*)$/", "$1",  $_POST['InternalNumber']);
                        $DopInvoiceSu          = preg_replace ("/(.*)-(.*)$/", "$2", $_POST['InternalNumber']);
                         $instructionInvoice  = " Заявка №: ".$NumberInvoiceSu." Доп.№".$DopInvoiceSu." ";
                        $newInstructions = $instructionInvoice . "  ".$newInstructions;
                    }
                    /*if(preg_match('#\/#',$number_nakl)){
                        $number_nakl = str_replace('/', '-',  $number_nakl);
                    }*/
                    $arPDF = [];
                    $arResult['LOGO_PRINT'] = CFile::GetPath($idlogoprint);
                    $arResult['ADRESS_PRINT'] = GetSettingValue(718, false, $arResult['UK']);
                    $arPDF['LOGO_PRINT'] = $arResult['LOGO_PRINT'];
                    $arPDF['ADRESS_PRINT'] = $arResult['ADRESS_PRINT'];
                    if($arResult['CURRENT_CLIENT'] == 26133129){
                        $arPDF['REQUEST']['number_nakl'] = $number_nakl_pdf;
                    }else{
                        $arPDF['REQUEST']['number_nakl'] = $number_nakl;
                    }
                    $arPDF['REQUEST']['NAME_SENDER'] = NewQuotes($_POST['NAME_SENDER']);
                    $arPDF['REQUEST']['PHONE_SENDER'] = NewQuotes($_POST['PHONE_SENDER']);
                    $arPDF['REQUEST']['TYPE_DELIVERY'] = $delivery_type1;
                    $arPDF['REQUEST']['TYPE_PAYS'] = $delivery_payer1;
                    $arPDF['REQUEST']['COMPANY_SENDER'] = NewQuotes($_POST['COMPANY_SENDER']);
                    //$arPDF[REQUEST][ВыборОтправителя] = 'Новый партнер 1';
                    $arPDF['REQUEST']['c_sender'] = $c_sender;
                    $arPDF['REQUEST']['o_sender'] = $o_sender;
                    $arPDF['REQUEST']['WHO_DELIVERY'] = $delivery_condition1;
                    $arPDF['REQUEST']['s_sender'] = $s_sender;
                    $arPDF['REQUEST']['INDEX_SENDER'] = deleteTabs($_POST['INDEX_SENDER']);
                    $arPDF['REQUEST']['PAYMENT'] = $payment_type1;
                    $arPDF['REQUEST']['ADRESS_SENDER'] = NewQuotes($_POST['ADRESS_SENDER']);
                    $arPDF['REQUEST']['NAME_RECIPIENT'] = NewQuotes($_POST['NAME_RECIPIENT']);
                    $arPDF['REQUEST']['PHONE_RECIPIENT'] = NewQuotes($_POST['PHONE_RECIPIENT']);
                    //$arPDF['REQUEST']['INSTRUCTIONS'] = NewQuotes($_POST['INSTRUCTIONS']);
                    $arPDF['REQUEST']['INSTRUCTIONS'] = NewQuotes($newInstructions);
                    $arPDF['REQUEST']['COMPANY_RECIPIENT'] = NewQuotes($_POST['COMPANY_RECIPIENT']);
                    //$arPDF['REQUEST'][ВыборПолучателя] = 'Новый партнер 2';
                    $arPDF['REQUEST']['c_recepient'] = $c_recepient;
                    $arPDF['REQUEST']['o_recepient'] = $o_recepient;
                    $arPDF['REQUEST']['s_recepient'] = $s_recepient;
                    $arPDF['REQUEST']['INDEX_RECIPIENT'] = deleteTabs($_POST['INDEX_RECIPIENT']);
                    $arPDF['REQUEST']['FOR_PAYMENT'] = (float)str_replace(',', '.', $_POST['FOR_PAYMENT']);
                    $arPDF['REQUEST']['PAYMENT_COD'] = (float)str_replace(',', '.', $_POST['PAYMENT_COD']);
                    $arPDF['REQUEST']['COST'] = (float)str_replace(',', '.', $_POST['COST']);
                    $arPDF['REQUEST']['ADRESS_RECIPIENT'] = NewQuotes($_POST['ADRESS_RECIPIENT']);
                    $arPDF['REQUEST']['total_place'] = $total_place;
                    $arPDF['REQUEST']['total_weight'] = $total_weight;
                    $arPDF['REQUEST']['total_gabweight'] = $total_gabweight;
                    $arPDF['REQUEST']['COST2'] = (float)str_replace(',', '.', $_POST['COST']);
                    $arPDF['REQUEST']['gab_1_name'] = iconv('utf-8', 'windows-1251',$arJsonDescr[0]['name']);
                    $arPDF['REQUEST']['gab_1_place'] = $arJsonDescr[0]['place'];
                    $arPDF['REQUEST']['gab_1_weight'] = $arJsonDescr[0]['weight'];
                    $arPDF['REQUEST']['gab_1_sizes'] =$arJsonDescr[0]['size'][0]."x".$arJsonDescr[0]['size'][1]."x".$arJsonDescr[0]['size'][2];
                    $arPDF['REQUEST']['gab_2_name'] = iconv('utf-8', 'windows-1251',$arJsonDescr[1]['name']);
                    $arPDF['REQUEST']['gab_2_place'] = $arJsonDescr[1]['place'];
                    $arPDF['REQUEST']['gab_2_weight'] = $arJsonDescr[1]['weight'];
                    $arPDF['REQUEST']['gab_2_sizes'] =$arJsonDescr[1]['size'][0]."x".$arJsonDescr[1]['size'][1]."x".$arJsonDescr[1]['size'][2];
                    $arPDF['REQUEST']['gab_3_name'] = iconv('utf-8', 'windows-1251',$arJsonDescr[2]['name']);
                    $arPDF['REQUEST']['gab_3_place'] = $arJsonDescr[2]['place'];
                    $arPDF['REQUEST']['gab_3_weight'] = $arJsonDescr[2]['weight'];
                    $arPDF['REQUEST']['gab_3_sizes'] =$arJsonDescr[2]['size'][0]."x".$arJsonDescr[2]['size'][1]."x".$arJsonDescr[2]['size'][2];
                    $arPDF['REQUEST']['gab_4_name'] = iconv('utf-8', 'windows-1251',$arJsonDescr[3]['name']);
                    $arPDF['REQUEST']['gab_4_place'] = $arJsonDescr[3]['place'];
                    $arPDF['REQUEST']['gab_4_weight'] = $arJsonDescr[3]['weight'];
                    $arPDF['REQUEST']['gab_4_sizes'] =$arJsonDescr[3]['size'][0]."x".$arJsonDescr[3]['size'][1]."x".$arJsonDescr[3]['size'][2];
                    $arPDF['REQUEST']['gab_5_name'] = iconv('utf-8', 'windows-1251',$arJsonDescr[4]['name']);
                    $arPDF['REQUEST']['gab_5_place'] = $arJsonDescr[4]['place'];
                    $arPDF['REQUEST']['gab_5_weight'] = $arJsonDescr[4]['weight'];
                    $arPDF['REQUEST']['gab_5_sizes'] =$arJsonDescr[4]['size'][0]."x".$arJsonDescr[4]['size'][1]."x".$arJsonDescr[4]['size'][2];

                    // это массив с нашими описаниями в накладную целиком!
                    $arPDF['REQUEST']['test'] = 12345;
                    // это массив с нашими описаниями в накладную целиком!
                    $arPDF['REQUEST']['fullArray'] = json_encode($arJsonDescr,JSON_PRETTY_PRINT);

                    // посылается один! раз
                    $arPDF['REQUEST']['deliver_before'] = $_POST['TO_DELIVER_BEFORE_DATE'];

                    // включаем внутренний номер и массив с датой первой в серии накладной*
                    $arPDF['REQUEST']['number_internal'] = $number_internal;
                    $arPDF['REQUEST']['number_internal_array'] = getRootInvoice($number_internal);
                    // пишем вычисленную дату
                    $arPDF['REQUEST']['DATE_CREATE'] = $mdate['DATE_CREATE'];
                    // получим данные курьерской заявки INSTRUCTIONS
                    // ********************************************************************

                    // передадим время и дату вызова курьера
                    $arPDF['REQUEST']['IN_DATE_DELIVERY'] = NewQuotes($_POST['IN_DATE_DELIVERY']);
                    $arPDF['REQUEST']['IN_TIME_DELIVERY'] = NewQuotes($_POST['IN_TIME_DELIVERY']);
                    // ********************************************************************

                    $file = $_SERVER['DOCUMENT_ROOT']."/upload/pdf/".$number_nakl.".txt";

                    ob_start();
                    print_r($arPDF);
                    $textualRepresentation = ob_get_contents();
                    ob_end_clean();
                    file_put_contents($file, $textualRepresentation);

                    /* создать массив для pdf  возвратной  накладной */
                    if ($WITH_RETURN == 1 )  {
                        $newInstructionsret = $spec_instr_ret;
                       if (!empty($_POST['callcourierdate'])){
                           $datecall = date('d.m.Y', strtotime($_POST['callcourierdate'] . "1 day"));
                           $newInstructionsret .= '  ВЫЗОВ КУРЬЕРА: ' . $datecall . ' с '.$_POST['callcourtime_from'].' до '.$_POST['callcourtime_to'].'.';
                       }
                       if($date_to_ret){
                            $newInstructionsret .= '  ДОСТАВИТЬ ДО ДАТЫ: '.$date_to_ret.'.';
                        }

                        if ($_POST['IN_DATE_DELIVERY'] !=''){
                            $datecalldev = date('d.m.Y', strtotime($_POST['IN_DATE_DELIVERY'] . "1 day"));
                            $newInstructionsret .= ' Доставить в дату: ' . $datecalldev;
                        }
                        if ($_POST['IN_TIME_DELIVERY'] !=''){

                            $newInstructionsret .= ' до часа '.$_POST['IN_TIME_DELIVERY'];
                        }
                        $sitysender = explode(", ", $CITY_SENDER);
                        $s_sender = $sitysender[0];
                        $o_sender = $sitysender[1];
                        $c_sender = $sitysender[2];
                        $sityrecepient = explode(", ", $CITY_RECIPIENT);
                        $s_recepient = $sityrecepient[0];
                        $o_recepient = $sityrecepient[1];
                        $c_recepient = $sityrecepient[2];
                        $arPDF_RET = [];
                        $arPDF_RET['LOGO_PRINT'] = $arResult['LOGO_PRINT'];
                        $arPDF_RET['ADRESS_PRINT'] = $arResult['ADRESS_PRINT'];
                        $arPDF_RET['REQUEST']['number_nakl'] = $number_nakl_ret;
                        $arPDF_RET['REQUEST']['NAME_SENDER'] = $NAME_SENDER;
                        $arPDF_RET['REQUEST']['PHONE_SENDER'] = $PHONE_SENDER;
                        $arPDF_RET['REQUEST']['TYPE_DELIVERY'] = $delivery_type1;
                        $arPDF_RET['REQUEST']['TYPE_PAYS'] = $TYPE_PAYS;
                        $arPDF_RET['REQUEST']['COMPANY_SENDER'] = $COMPANY_SENDER;
                        $arPDF_RET['REQUEST']['c_sender'] = $c_sender;
                        $arPDF_RET['REQUEST']['o_sender'] = $o_sender;
                        $arPDF_RET['REQUEST']['WHO_DELIVERY'] = $delivery_condition1;
                        $arPDF_RET['REQUEST']['s_sender'] = $s_sender;
                        $arPDF_RET['REQUEST']['INDEX_SENDER'] = $INDEX_SENDER;
                        $arPDF_RET['REQUEST']['PAYMENT'] = $payment_type1;
                        $arPDF_RET['REQUEST']['ADRESS_SENDER'] = $ADRESS_SENDER;
                        $arPDF_RET['REQUEST']['NAME_RECIPIENT'] = $NAME_RECIPIENT;
                        $arPDF_RET['REQUEST']['PHONE_RECIPIENT'] = $PHONE_RECIPIENT;
                        $arPDF_RET['REQUEST']['INSTRUCTIONS'] =  $newInstructionsret;
                        $arPDF_RET['REQUEST']['COMPANY_RECIPIENT'] = $COMPANY_RECIPIENT;
                        $arPDF_RET['REQUEST']['c_recepient'] = $c_recepient;
                        $arPDF_RET['REQUEST']['o_recepient'] = $o_recepient;
                        $arPDF_RET['REQUEST']['s_recepient'] = $s_recepient;
                        $arPDF_RET['REQUEST']['INDEX_RECIPIENT'] = $INDEX_RECIPIENT;
                        $arPDF_RET['REQUEST']['FOR_PAYMENT'] = 0.00;
                        $arPDF_RET['REQUEST']['PAYMENT_COD'] = 0.00;
                        $arPDF_RET['REQUEST']['COST'] = 0.00;
                        $arPDF_RET['REQUEST']['ADRESS_RECIPIENT'] = $ADRESS_RECIPIENT;
                        $arPDF_RET['REQUEST']['total_place'] = $total_place;
                        $arPDF_RET['REQUEST']['total_weight'] = $total_weight;
                        $arPDF_RET['REQUEST']['total_gabweight'] = $total_gabweight;
                        $arPDF_RET['REQUEST']['COST2'] = 0.00;
                        $arPDF_RET['REQUEST']['gab_1_name'] = '';
                        $arPDF_RET['REQUEST']['gab_1_place'] = '';
                        $arPDF_RET['REQUEST']['gab_1_weight'] = '';
                        $arPDF_RET['REQUEST']['gab_1_sizes'] = '';
                        $arPDF_RET['REQUEST']['gab_2_name'] = '';
                        $arPDF_RET['REQUEST']['gab_2_place'] = '';
                        $arPDF_RET['REQUEST']['gab_2_weight'] = '';
                        $arPDF_RET['REQUEST']['gab_2_sizes'] = '';
                        $arPDF_RET['REQUEST']['gab_3_name'] = '';
                        $arPDF_RET['REQUEST']['gab_3_place'] = '';
                        $arPDF_RET['REQUEST']['gab_3_weight'] = '';
                        $arPDF_RET['REQUEST']['gab_3_sizes'] = '';
                        $arPDF_RET['REQUEST']['gab_4_name'] = '';
                        $arPDF_RET['REQUEST']['gab_4_place'] = '';
                        $arPDF_RET['REQUEST']['gab_4_weight'] = '';
                        $arPDF_RET['REQUEST']['gab_4_sizes'] = '';
                        $arPDF_RET['REQUEST']['gab_5_name'] = '';
                        $arPDF_RET['REQUEST']['gab_5_place'] = '';
                        $arPDF_RET['REQUEST']['gab_5_weight'] = '';
                        $arPDF_RET['REQUEST']['gab_5_sizes'] = '';
                        // это массив с нашими описаниями в накладную целиком!
                        $arPDF_RET['REQUEST']['fullArray'] = json_encode($arJsonDescr,JSON_PRETTY_PRINT);

                        $arPDF_RET['REQUEST']['deliver_before'] = '';
                        // включаем внутренний номер и массив с датой первой в серии накладной*
                        $arPDF_RET['REQUEST']['number_internal'] = '';
                        $arPDF_RET['REQUEST']['number_internal_array'] = '';
                        // пишем вычисленную дату
                        $arPDF_RET['REQUEST']['DATE_CREATE'] = $mdate_ret;
                        // получим данные курьерской заявки INSTRUCTIONS
                        // ********************************************************************

                        // передадим время и дату вызова курьера
                        $arPDF_RET['REQUEST']['IN_DATE_DELIVERY'] = $IN_DATE_DELIVERY;
                        $arPDF_RET['REQUEST']['IN_TIME_DELIVERY'] = NewQuotes($_POST['IN_TIME_DELIVERY']);
                        // ********************************************************************

                        $file = $_SERVER['DOCUMENT_ROOT']."/upload/pdf/".$number_nakl_ret.".txt";

                        ob_start();
                        print_r($arPDF_RET);
                        $textualRepresentation = ob_get_contents();
                        ob_end_clean();
                        file_put_contents($file, $textualRepresentation);
                        MakeZakazPDF(encodeArray($arPDF_RET, "cp1251"));
                        $_SESSION['MESSAGE'][] = "Накладная на обратный забор № <span id='new_invoice_made_ret'>".$number_nakl_ret."</span> успешно создана";
                    }

                    // Вымпелком  с Возвратом

                    if ( ($arResult['CURRENT_CLIENT'] == ID_VKOM1 ||
                            $arResult['CURRENT_CLIENT'] == ID_VKOM2  ||
                            $arResult['CURRENT_CLIENT'] == ID_VKOM3  ||
                            $arResult['CURRENT_CLIENT'] == ID_VKOM4 ) &&
                        ($WITH_RETURN == 1))
                    {
                        MakeZakazPDFV(encodeArray($arPDF, "cp1251")); //форма для Вымпелкома
                    }elseif (($arResult['CURRENT_CLIENT'] == ID_SUKHOI) || ($arResult['CURRENT_CLIENT'] == ID_TEST)){
                        MakeZakazPDF2(encodeArray($arPDF, "cp1251")); //создаем pdf с накладными для вложения в письмо
                    }else{
                        MakeZakazPDF(encodeArray($arPDF, "cp1251")); //создаем pdf с накладными для вложения в письмо
                    }

                    //NOTE письмо Ринату если клиент АО «ЦТЗ» оформил накладную в город Ноябрьск
                    if (($arResult['CURRENT_CLIENT'] == 17437417) && ($city_recipient == 9147))
                    {
                        $event = new CEvent;
                        $event->Send("NEWPARTNER_LK", "S5", ["DATE" => date('d.m.Y H:i'),
                            "NUMBER" => $number_nakl], "N", 245);
                    }


                    $_SESSION['MESSAGE'][] = "Накладная № <span id='new_invoice_made'>".$number_nakl."</span> успешно создана";

                    //ВЫЗОВ КУРЬЕРА
                    //771 номер связанной накладной
                    if ($_POST['callcourier'] == 'yes')
                    {
                        $id_in_cur = GetMaxIDIN(87, 7);
                        $arHistory = [['date' => date('d.m.Y H:i:s'), 'status' => 315,
                            'status_descr' => 'Оформлена', 'comment' => '']];
                        $arHistoryUTF = convArrayToUTF($arHistory);
                        $el = new CIBlockElement;
                        $arLoadProductArray = [
                            "MODIFIED_BY" => $USER->GetID(),
                            "IBLOCK_SECTION_ID" => false,
                            "IBLOCK_ID" => 87,
                            "PROPERTY_VALUES" => [
                                611 => $id_in_cur,
                                612 => $arResult['CURRENT_CLIENT'],
                                664 => $arResult['CURRENT_BRANCH'],
                                613 => [
                                    $_POST['callcourierdate'].' '.$_POST['callcourtime_from'].':00',
                                    $_POST['callcourierdate'].' '.$_POST['callcourtime_to'].':00'
                                ],
                                614 => $city_sender,
                                615 => NewQuotes($_POST['ADRESS_SENDER']),
                                616 => NewQuotes($_POST['NAME_SENDER']),
                                617 => NewQuotes($_POST['PHONE_SENDER']),
                                618 => $total_weight,
                                619 => $_POST['DIMENSIONS'],
                                620 => NewQuotes($_POST['callcourcomment']).' Накладная №'.$number_nakl,
                                712 => implode(', ', [$arResult['EMAIL_CALLCOURIER'], $arResult['ADD_AGENT_EMAIL']]),
                                726 => 315,
                                727 => json_encode($arHistoryUTF),
                                771 => $number_nakl,
                                862 => $TRANSPORT_TYPE
                            ],
                            "NAME" => 'Вызов курьера №'.$id_in_cur,
                            "ACTIVE" => "Y"
                        ];
                        //->Add  $z_id был обернут в if
                        $z_id = $el->Add($arLoadProductArray);

                        if (!$z_id)
                        {
                            $error = $el->LAST_ERROR;
                            AddToLogs('InvAddErrors', ['ERROR' => $error,
                                'mess' => 'Проблемы с сохранением вызова курьера']);
                            $arResult['ERRORS'][] = $error;
                        }

                        // запись вызова курьера для возвратной накладной в базу
                        if ($WITH_RETURN == 1)  {
                            $id_in_cur_ret = GetMaxIDIN(87, 7);
                            $arHistory = [['date' => date('d.m.Y H:i:s'), 'status' => 315,
                                'status_descr' => 'Оформлена', 'comment' => '']];
                            $arHistoryUTF = convArrayToUTF($arHistory);
                            $el = new CIBlockElement;
                            $arLoadProductArray = [
                                "MODIFIED_BY" => $USER->GetID(),
                                "IBLOCK_SECTION_ID" => false,
                                "IBLOCK_ID" => 87,
                                "PROPERTY_VALUES" => [
                                    611 => $id_in_cur_ret,
                                    612 => $arResult['CURRENT_CLIENT'],
                                    664 => $arResult['CURRENT_BRANCH'],
                                    613 => [
                                        $datecall . ' ' . $_POST['callcourtime_from'] . ':00',
                                        $datecall . ' ' . $_POST['callcourtime_to'] . ':00'
                                    ],
                                    614 => $city_sender_ret,
                                    615 => $ADRESS_SENDER,
                                    616 => $NAME_SENDER,
                                    617 => $PHONE_SENDER,
                                    618 => $total_weight,
                                    619 => $_POST['DIMENSIONS'],
                                    620 => 'Обратный забор к накладной №' . $number_nakl,
                                    712 => implode(', ', [$arResult['EMAIL_CALLCOURIER'], $arResult['ADD_AGENT_EMAIL']]),
                                    726 => 315,
                                    727 => json_encode($arHistoryUTF),
                                    771 => $number_nakl_ret,
                                    862 => $TRANSPORT_TYPE
                                ],
                                "NAME" => 'Вызов курьера № ' . $id_in_cur_ret,
                                "ACTIVE" => "Y"
                            ];

                            $z_id_cur_ret = $el->Add($arLoadProductArray);
                            CIBlockElement::SetPropertyValuesEx($z_nakl_id_ret, 83,
                                [570 => ['VALUE' => ['TYPE' => 'text', 'TEXT' => $newInstructionsret]]]);
                            if (!$z_id_cur_ret)
                            {
                                $error = $el->LAST_ERROR;
                                AddToLogs('InvAddErrors', ['ERROR' => $error,
                                    'mess' => 'Проблемы с сохранением вызова курьера (обратный забор)']);
                                $arResult['ERRORS'][] = $error;
                            }
                        }
                        // запись в бд вызова курьера для возвратной накладной end

                        //TODO [x]Добавить информацию для курьера в спец. инструкции накладной

                        $newInstructions = NewQuotes($_POST['INSTRUCTIONS']);

                        if (strlen($newInstructions))
                        {
                            $newInstructions .= '. ';
                        }
                        //  поправляем
                        $newInstructions .= '  ВЫЗОВ КУРЬЕРА: '.$_POST['callcourierdate'].' с '.$_POST['callcourtime_from'].' до '.$_POST['callcourtime_to'].'.';
                        if($date_to){
                            $newInstructions .= '  ДОСТАВИТЬ ДО ДАТЫ: '.$date_to.'.';
                        }

                        if (strlen(trim($_POST['callcourcomment'])))
                        {
                            $newInstructions .= ' КОММЕНТАРИЙ КУРЬЕРУ: '.NewQuotes($_POST['callcourcomment']);
                        }

                        if ($_POST['IN_DATE_DELIVERY'] !=''){
                            $newInstructions .= ' Доставить в дату: '.$_POST['IN_DATE_DELIVERY'];
                        }
                        if ($_POST['IN_TIME_DELIVERY'] !=''){
                            $newInstructions .= ' до часа '.$_POST['IN_TIME_DELIVERY'];
                        }

                        if (($arResult['CURRENT_CLIENT'] == ID_SUKHOI) || ($arResult['CURRENT_CLIENT'] == ID_TEST)){
                            $NumberInvoiceSu   = preg_replace ("/(.*)-(.*)$/", "$1",  $_POST['InternalNumber']);
                            $DopInvoiceSu          = preg_replace ("/(.*)-(.*)$/", "$2", $_POST['InternalNumber']);
                            $instructionInvoice  = " Заявка №: ".$NumberInvoiceSu." Доп.№".$DopInvoiceSu." ";

                            $newInstructions = $instructionInvoice."  ".$newInstructions;
                        }

                        CIBlockElement::SetPropertyValuesEx($z_nakl_id, 83,
                            [570 => ['VALUE' => ['TYPE' => 'text', 'TEXT' => $newInstructions]]]);


//$arResult['CURRENT_CLIENT'] == ID_SUKHOI || $arResult['CURRENT_CLIENT'] == ID_TEST
                        if($_POST['pack_description']) {
                            $gabarit1 = "Наименование: ".$_POST['pack_description'][0]['name']."; Мест: ".$_POST['pack_description'][0]['place']."; Вес: ".$_POST['pack_description'][0]['weight']."кг; Размеры: ".$_POST['pack_description'][0]['size'][0]."Х".$_POST['pack_description'][0]['size'][1]."Х".$_POST['pack_description'][0]['size'][2]." см";
                            $gabarit2 = "Наименование: ".$_POST['pack_description'][1]['name']."; Мест: ".$_POST['pack_description'][1]['place']."; Вес: ".$_POST['pack_description'][1]['weight']."кг; Размеры: ".$_POST['pack_description'][1]['size'][0]."Х".$_POST['pack_description'][1]['size'][1]."Х".$_POST['pack_description'][1]['size'][2]." см";
                            $gabarit3 = "Наименование: ".$_POST['pack_description'][2]['name']."; Мест: ".$_POST['pack_description'][2]['place']."; Вес: ".$_POST['pack_description'][2]['weight']."кг; Размеры: ".$_POST['pack_description'][2]['size'][0]."Х".$_POST['pack_description'][2]['size'][1]."Х".$_POST['pack_description'][2]['size'][2]." см";
                            $gabarit4 = "Наименование: ".$_POST['pack_description'][3]['name']."; Мест: ".$_POST['pack_description'][3]['place']."; Вес: ".$_POST['pack_description'][3]['weight']."кг; Размеры: ".$_POST['pack_description'][3]['size'][0]."Х".$_POST['pack_description'][3]['size'][1]."Х".$_POST['pack_description'][3]['size'][2]." см";
                            $gabarit5 = "Наименование: ".$_POST['pack_description'][4]['name']."; Мест: ".$_POST['pack_description'][4]['place']."; Вес: ".$_POST['pack_description'][4]['weight']."кг; Размеры: ".$_POST['pack_description'][4]['size'][0]."Х".$_POST['pack_description'][4]['size'][1]."Х".$_POST['pack_description'][4]['size'][2]." см";

                        }
                        else {
                            $gabarit1 = $_POST['DIMENSIONS'][0];
                            $gabarit2 = $_POST['DIMENSIONS'][1];
                            $gabarit3 = $_POST['DIMENSIONS'][2];
                            $gabarit4 = $_POST['DIMENSIONS'][3];
                            $gabarit5 = $_POST['DIMENSIONS'][4];

                        }


                        $arEventFields = [
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
                           /* "SIZE_1" => $gabarit1,
                            "SIZE_2" => $gabarit2,
                            "SIZE_3" => $gabarit3,
                            "SIZE_4" => $gabarit4,
                            "SIZE_5" => $gabarit5,*/
                            "COMMENT" => deleteTabs($_POST['callcourcomment']).' Накладная №'.$number_nakl,
                            'AGENT_EMAIL' => $arResult['ADD_AGENT_EMAIL'],
                            'UK_EMAIL' => $arResult['EMAIL_CALLCOURIER'],
                            'TYPE_PAYS' => $payment_type1,
                            'SPEC_INSTR' => $newInstructions,
                            'PAYER' => $delivery_payer1,
                            "POST" => "client@newpartner.ru, logist@newpartner.ru",
                        ];
                        // || $arResult['CURRENT_CLIENT'] == ID_TEST
                       // if($arResult['CURRENT_CLIENT'] == ID_SUKHOI ) {
                            if (!empty($_POST['pack_description'])) {
                                $arEventFields['SIZE'] = "Габариты:" . "<br />";
                                foreach ($_POST['pack_description'] as $key => $value) {
                                    $k = $key+1;
                                    if(!empty($value['name'])){
                                        $arEventFields['SIZE'] .= "Наименование:  {$value['name']} ; 
                                    Мест: {$value['place']}; Вес: {$value['weight']} кг;
                                     Размеры: {$value['size'][0]} Х {$value['size'][1]} Х
                                      {$value['size'][2]} см" . " <br/>" ;
                                        $arEventFields['SIZE_'.$k] = "Наименование:  {$value['name']} ; 
                                    Мест: {$value['place']}; Вес: {$value['weight']} кг;
                                     Размеры: {$value['size'][0]} Х {$value['size'][1]} Х
                                      {$value['size'][2]} см";
                                    }

                                }
                          //  }

                        }   else {
                            if (!empty($_POST['DIMENSIONS'])) {
                                $arEventFields['SIZE'] = "Габариты:" . "<br />";
                                foreach ($_POST['DIMENSIONS'] as $key => $value) {
                                    if(!empty($value)){
                                        $k = $key+1;
                                        $arEventFields['SIZE'] .= $_POST['DIMENSIONS'][$key] . " <br/>";
                                        $arEventFields['SIZE_'.$k] = $_POST['DIMENSIONS'][$key];
                                    }

                                }
                              }
                        }

                        $arEventFields['FOR_CACHE']='';
                        if($payment_type1 === 'Наличные'){
                            $arEventFields['FOR_CACHE'] = "ЗА НАЛИЧНЫЕ";
                        }

                        //  массив данных для почты возвратная накладная и отправка письма
                        if ($WITH_RETURN == 1)  {
                            $arEventFieldsRet = [
                                "COMPANY_F" => ($arResult['USER_IN_BRANCH']) ? $arResult['AGENT']['NAME'].', филиал '.$arResult['BRANCH_INFO']['NAME'] : $arResult['AGENT']['NAME'],
                                "NUMBER" => $id_in_cur_ret,
                                "COMPANY" => $arResult['AGENT']['NAME'],
                                "BRANCH" => ($arResult['USER_IN_BRANCH']) ? 'Филиал: <strong>'.$arResult['BRANCH_INFO']['NAME'].'</strong><br />' : '',
                                "DATE_TIME" => $datecall . ' с ' . $_POST['callcourtime_from'] . ' до ' . $_POST['callcourtime_to'],
                                "CITY" => $CITY_SENDER,
                                "ADRESS" => $ADRESS_SENDER,
                                "CONTACT" => $NAME_SENDER,
                                "PHONE" => $PHONE_SENDER,
                                "WEIGHT" => $total_weight,
                                "SIZE" => "{$gabarit1}<br />{$gabarit2}<br /> {$gabarit3}<br /> {$gabarit4}<br /> {$gabarit5} ",
                               /* "SIZE_2" => $gabarit2,
                                "SIZE_3" => $gabarit3,
                                "SIZE_4" => $gabarit4,
                                "SIZE_5" => $gabarit5,*/
                                "COMMENT" => "Обратный забор к накладной $number_nakl. Накладная № " . $number_nakl_ret,
                                'AGENT_EMAIL' => $arResult['ADD_AGENT_EMAIL'],
                                'UK_EMAIL' => $arResult['EMAIL_CALLCOURIER'],
                                'TYPE_PAYS' => $payment_type1,
                                'SPEC_INSTR' => $newInstructionsret,
                                'PAYER' => $delivery_payer_ret,
                                "POST" => "client@newpartner.ru, logist@newpartner.ru",
                            ];
                            $arEventFieldsRet['FOR_CACHE'] = '';
                            if($payment_type1 === 'Наличные'){
                                $arEventFieldsRet['FOR_CACHE'] = "ЗА НАЛИЧНЫЕ";
                            }
                            MakeZakazPDF(encodeArray($arPDF_RET, "cp1251")); //создаем pdf с накладными для вложения в письмо
                            $sendFilePathRET = $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main", "upload_dir")."/pdf/".$number_nakl_ret.".pdf";
                            $fileIdRet = CFile::SaveFile(
                                [
                                    "name" => $number_nakl_ret.".pdf",
                                    "tmp_name" => $sendFilePathRET,
                                    "old_file" => "0",
                                    "del" => "N",
                                    "MODULE_ID" => "",
                                    "description" => "",
                                ],
                                'sendfile',
                                false,
                                false
                            );
                            $event = new CEvent;
                            $event->SendImmediate("NEWPARTNER_LK", "S5", $arEventFieldsRet, "N", 220, [$fileIdRet]);
                            $arHistory[] = ['date' => date('d.m.Y H:i:s'), 'status' => 316, 'status_descr' => 'Отправлена на почту', 'comment' => ''];
                            $arHistoryUTF = convArrayToUTF($arHistory);
                            CIBlockElement::SetPropertyValuesEx($z_id_cur_ret, 87,
                                ["STATE" => 316,"STATE_HISTORY" => json_encode($arHistoryUTF)]);
                            AddToLogs('SendPost', ['Send'=>$arEventFieldsRet,  'Date'=>date("d.m.Y H:i:s")] );
                        }

                        // Вымпелком своя форма печати

                        if ( ($arResult['CURRENT_CLIENT'] == ID_VKOM1 ||
                                $arResult['CURRENT_CLIENT'] == ID_VKOM2  ||
                                $arResult['CURRENT_CLIENT'] == ID_VKOM3  ||
                                $arResult['CURRENT_CLIENT'] == ID_VKOM4  ) &&
                            ($WITH_RETURN == 1))
                        {
                            $arEventFields['WITH_RETURN'] = 'С ВОЗВРАТОМ!';
                            MakeZakazPDFV(encodeArray($arPDF, "cp1251")); //форма для Вымпелкома
                        }elseif (($arResult['CURRENT_CLIENT'] == ID_SUKHOI)||($arResult['CURRENT_CLIENT'] == ID_TEST)){
                            MakeZakazPDF2(encodeArray($arPDF, "cp1251")); //создаем pdf с накладными для вложения в письмо
                        }else{
                            MakeZakazPDF(encodeArray($arPDF, "cp1251")); //создаем pdf с накладными для вложения в письмо
                        }

                        // файл, который будет приложен к письму
                        $sendFilePath = $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main", "upload_dir")."/pdf/".$number_nakl.".pdf";
                        // чтобы отправить файл с использованием шаблона в битрикс необходимо получить id этого файла, т.е.
                        // сделать так чтобы битрикс знал о его существовании
                        $fileId = CFile::SaveFile(
                            [
                                "name" => $number_nakl.".pdf",
                                "tmp_name" => $sendFilePath,
                                "old_file" => "0",
                                "del" => "N",
                                "MODULE_ID" => "",
                                "description" => "",
                            ],
                            'sendfile',
                            false,
                            false
                        );

                        $arHistory[] = ['date' => date('d.m.Y H:i:s'), 'status' => 316, 'status_descr' => 'Отправлена на почту', 'comment' => ''];
                        $arHistoryUTF = convArrayToUTF($arHistory);
                        CIBlockElement::SetPropertyValuesEx($z_id, 87, ["STATE"=>316,"STATE_HISTORY"=>json_encode($arHistoryUTF)]);
                        //TODO [x]Настройка включения/выключения голосовых оповещений и номеров телефонов
                        if ((int)$arResult['ZADARMA'] == 1)
                        {
                            if (((int)date('G') >=17) || ((int)date('G') < 8))
                            {
                                include_once $_SERVER["DOCUMENT_ROOT"].'/bitrix/_black_mist/zadarma/Client.php';
                                $params = [
                                    'from' => $arResult['ZADARMA_FROM'],
                                    'to' => '+79003333333',
                                ];
                                $zd = new \Zadarma_API\Client("44c738b94aef4db7b31b", "c6406ab4bc31d8657805");
                                $answer = $zd->call('/v1/request/callback/', $params);
                            }
                        }

                        // Отправка уведомлений в 1с

                        $payment_type = 'Наличные';
                        switch ((int)$_POST['PAYMENT'])
                        {
                            case 255:
                                $payment_type = 'Наличные';
                                break;
                            case 256:
                                $payment_type = 'Безналичные';
                                break;
                        }
                        $delivery_type = 'Стандарт';
                        switch ((int)$_POST['TYPE_DELIVERY'])
                        {
                            case 345:
                                $delivery_type1 = 'Экспресс 2';
                                break;
                            case 346:
                                $delivery_type1 = 'Экспресс 4';
                                break;
                            case 338:
                                $delivery_type1 = 'Экспресс 8';
                                break;
                            case 243:
                                $delivery_type = 'Экспресс';
                                break;
                            case 244:
                                $delivery_type = 'Стандарт';
                                break;
                            case 245:
                                $delivery_type = 'Эконом';
                                break;
                            case 308:
                                $delivery_type = 'Склад-Склад';
                                break;
                        }
                        $delivery_payer = 'Отправитель';
                        switch ((int)$_POST['TYPE_PAYS'])
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
                        switch ((int)$_POST['WHO_DELIVERY'])
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

                        //****
                        $delivery_payer_seq = 'О';
                        switch ((int)$_POST['TYPE_PAYS'])
                        {
                            case 251:
                                $delivery_payer_seq = 'О';
                                break;
                            case 252:
                                $delivery_payer_seq = 'П';
                                break;
                            case 253:
                                $delivery_payer_seq = 'Д';
                                break;
                        }
                        $delivery_type_seq  = "С";
                        switch ((int)$_POST['TYPE_DELIVERY'])
                        {
                            case 345:
                                $delivery_type_seq = 'Э';
                                break;
                            case 346:
                                $delivery_type_seq = 'Э';
                                break;
                            case 338:
                                $delivery_type_seq = 'Э';
                                break;
                            case 243:
                                $delivery_type_seq = 'Э';
                                break;
                            case 244:
                                $delivery_type_seq = 'С';
                                break;
                            case 245:
                                $delivery_type_seq = 'М';
                                break;
                            case 308:
                                $delivery_type_seq = 'Д';
                                break;
                        }
                        $payment_type_seq = 'Н';
                        switch ((int)$_POST['PAYMENT'])
                        {
                            case 255:
                                $payment_type_seq = 'Н';
                                break;
                            case 256:
                                $payment_type_seq = 'Б';
                                break;
                        }

                        $delivery_condition_seq = 'А';
                        switch ((int)$_POST['WHO_DELIVERY'])
                        {
                            case 248:
                                $delivery_condition_seq = 'А';
                                break;
                            case 249:
                                $delivery_condition_seq = 'Д';
                                break;
                            case 250:
                                $delivery_condition_seq = 'Л';
                                break;
                        }
                        //***
                        // сюда дописываем "доставить до"
                        //$instruction = deleteTabs($_POST['callcourcomment']).' Накладная №'.$number_nakl;
                        $arJs = [
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
                            'INSTRUCTIONS' => deleteTabs($_POST['callcourcomment']).' Накладная №'.$number_nakl,
                            "TRANSPORT_TYPE" => (int)$TRANSPORT_TYPE,

                        ];
                        $m = [];
                        foreach ($arJs as $kk => $vv)
                        {
                            $m[$kk] = iconv('windows-1251','utf-8', $vv);
                        }
                        $result = $client->SetCallingTheCourier(['ListOfDocs' => json_encode($m)]);
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
                        $arHistory[] = ['date' => date('d.m.Y H:i:s'), 'status' => $state_id,
                            'status_descr' => $state_descr, 'comment' => $arRes[0]['comment']];
                        $arHistoryUTF = convArrayToUTF($arHistory);
                        CIBlockElement::SetPropertyValuesEx($z_id, 87, ["STATE"=>$state_id,
                            "STATE_HISTORY"=>json_encode($arHistoryUTF)]);
                        $arLogTitle = ['Title' => 'Вызов курьера из накладной'];
                        $arLogResult = ['Response' => $mResult, 'status' => $arRes[0]['status'],
                            'comment' => $arRes[0]['comment']];
                        $arLog = array_merge($arLogTitle,$arJs,$arLogResult);
                        AddToLogs('callingCourier',$arLogResult);
                        //    }
                        $_SESSION["MESSAGE"][] = "Вызов курьера №".$id_in_cur." успешно зарегистрирован";

               //массив и вызов курьера в 1с возвратная накладная
                        if ($WITH_RETURN == 1 )  {
                            $arJsRet = [
                                'IDWEB' => $z_id_cur_ret,
                                'INN' => $arResult['CURRENT_CLIENT_INN'],
                                'DATE' => date('Y-m-d'),
                                'COMPANY_SENDER' => ($arResult['USER_IN_BRANCH']) ? $arResult['AGENT']['NAME'].', филиал '.$arResult['BRANCH_INFO']['NAME'] : $arResult['AGENT']['NAME'],
                                'NAME_SENDER' => $NAME_SENDER,
                                'PHONE_SENDER' => $PHONE_SENDER,
                                'ADRESS_SENDER' => $ADRESS_SENDER,
                                'INDEX_SENDER' => $INDEX_SENDER,
                                'ID_CITY_SENDER' => $city_sender_ret,
                                'DELIVERY_TYPE' => $delivery_type,
                                'PAYMENT_TYPE' => $payment_type,
                                'DELIVERY_PAYER' => $delivery_payer_ret,
                                'DELIVERY_CONDITION' => $delivery_condition,
                                'DATE_TAKE_FROM' => substr($datecall,6,4).'-'.substr($datecall,3,2).'-'.substr($datecall,0,2).' '.$datecall.':00',
                                'DATE_TAKE_TO' => substr($datecall,6,4).'-'.substr($datecall,3,2).'-'.substr($datecall,0,2).' '.$datecall.':00',
                                'INSTRUCTIONS' => "Обратный забор по накладной $number_nakl. Накладная № " . $number_nakl_ret,
                                "TRANSPORT_TYPE" => (int)$TRANSPORT_TYPE,
                                "ISOFFICE" => $isoffice,
                            ];
                            $m = [];
                            foreach ($arJsRet as $kk => $vv)
                            {
                                $m[$kk] = iconv('windows-1251','utf-8', $vv);
                            }
                            $result = $client->SetCallingTheCourier(['ListOfDocs' => json_encode($m)]);
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
                            $arHistory[] = ['date' => date('d.m.Y H:i:s'), 'status' => $state_id,
                                'status_descr' => $state_descr, 'comment' => $arRes[0]['comment']];
                            $arHistoryUTF = convArrayToUTF($arHistory);
                            CIBlockElement::SetPropertyValuesEx($z_id_cur_ret, 87, ["STATE" => $state_id,
                                "STATE_HISTORY" => json_encode($arHistoryUTF)]);
                            $arLogTitle = ['Title' => 'Вызов курьера из накладной обратного забора'];
                            $arLogResult = ['Response' => $mResult, 'status' => $arRes[0]['status'],
                                'comment' => $arRes[0]['comment']];
                            $arLog = array_merge($arLogTitle,$arJsRet,$arLogResult);
                            AddToLogs('callingCourier',$arLogResult);
                            //    }
                            $_SESSION["MESSAGE"][] = "Вызов курьера на обратный забор №".$id_in_cur_ret." успешно зарегистрирован";


                        }  //массив и вызов курьера в 1с возвратная накладная Абсолют страхование end

                        // **
                        // *   вставить наш вызов в очередь (1)
                        // **
                        // ***
                        // SetDocsList Помещение в очередь По сохранению накладной
                        // ***
                        // убрали (begin)
                        $arCITY_RECIPIENT=explode(",", $_POST['CITY_RECIPIENT']);
                        $arCITY_SENDER=explode(",", $_POST['CITY_SENDER']);
                        $CITY_RECIPIENT = $arCITY_RECIPIENT[0];
                        $CITY_SENDER    = $arCITY_SENDER[0];
                        // поищем город по справочнику
                        $arSelect = ["ID","NAME","IBLOCK_SECTION_ID"];
                        $arFilter = ["IBLOCK_ID" => 6, "NAME" => $CITY_SENDER];
                        $res2 = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
                        while ($ob = $res2->GetNextElement())
                        {
                            $arFields = $ob->GetFields();
                            $a =  $arFields;
                        }
                        // поищем город по справочнику
                        $arSelect = ["ID","NAME","IBLOCK_SECTION_ID"];
                        $arFilter = ["IBLOCK_ID" => 6, "NAME" => $CITY_RECIPIENT];
                        $res2 = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
                        while ($ob = $res2->GetNextElement())
                        {
                            $arFields = $ob->GetFields();
                            $b =  $arFields;
                        }

                        $DATE_TAKE_FROM_DELIVER    = substr($_POST['callcourierdate'],6,4).'-'.substr($_POST['callcourierdate'],3,2).'-'.substr($_POST['callcourierdate'],0,2);
                        $DATE_TAKE_FROM    = substr($_POST['callcourierdate'],6,4).'-'.substr($_POST['callcourierdate'],3,2).'-'.substr($_POST['callcourierdate'],0,2).' '.$_POST['callcourtime_from'].':00';
                        $DATE_TAKE_TO      = substr($_POST['callcourierdate'],6,4).'-'.substr($_POST['callcourierdate'],3,2).'-'.substr($_POST['callcourierdate'],0,2).' '.$_POST['callcourtime_to'].':00';

                        $arDeliverySequence = [
                            "ID"            => $z_nakl_id,
                            "DATE_CREATE"   => $arPDF['REQUEST']['DATE_CREATE'],
                            "INN"           => $arResult['CURRENT_CLIENT_INN'],
                            "NAME_SENDER"   => NewQuotes($_POST['NAME_SENDER']),
                            "PHONE_SENDER"  => NewQuotes($_POST['PHONE_SENDER']),
                            "COMPANY_SENDER"=> NewQuotes($_POST['COMPANY_SENDER']),
                            "CITY_SENDER"   => $a['ID'],
                            "INDEX_SENDER"  => $_POST['INDEX_SENDER'],
                            "ADDRESS_SENDER" => NewQuotes($_POST['ADRESS_SENDER']),
                            "ADDRESS_RECIPIENT"  =>NewQuotes($_POST['ADRESS_RECIPIENT']),
                            "NAME_RECIPIENT" =>"-",
                            "PHONE_RECIPIENT"   =>  NewQuotes($_POST['PHONE_RECIPIENT']),
                            "COMPANY_RECIPIENT" =>  NewQuotes($_POST['COMPANY_RECIPIENT']),
                            "CITY_RECIPIENT"    =>  $b['ID'],
                            "CITY_RECIPIENT_NON" => NewQuotes($_POST['CITY_RECIPIENT']),
                            "INDEX_RECIPIENT"   =>  NewQuotes($_POST['INDEX_RECIPIENT']),
                            'DATE_TAKE_FROM'    => NewQuotes($DATE_TAKE_FROM),
                            'DATE_TAKE_TO'      => NewQuotes($DATE_TAKE_TO),
                            "TYPE" =>$arResult['DEAULTS']['TYPE_PACK'],
                            "DELIVERY_TYPE" => $delivery_type_seq,
                            "DELIVERY_PAYER" => $delivery_payer_seq,
                            "PAYMENT_TYPE" =>$payment_type_seq,
                            "DELIVERY_CONDITION" =>$delivery_condition_seq,
                            "PAYMENT_AMOUNT" =>"0",
                            "INSTRUCTIONS" => $newInstructions,
                            "PLACES" =>$total_place,
                            "WEIGHT" =>$total_weight,
                            "SIZE_1" => 0,
                            "SIZE_2" => 0,
                            "SIZE_3" => 0,
                            "FILES" =>"",
                            "InternalNumber"=>$_POST['InternalNumber'],
                            "DocNumber" => $number_nakl,
                            "TRANSPORT_TYPE" => (int)$TRANSPORT_TYPE,
                        ];
                        $m = [];
                        foreach ($arDeliverySequence as $kk => $vv)
                        {
                            $m[$kk] = iconv('windows-1251','utf-8', $vv);
                        }
                        $arParamsJson = [
                            'ListOfDocs' => "[".json_encode($m)."]"
                        ];
                        $error = json_last_error_msg();

                        //  вставить наш вызов в очередь возвратная накладная
                         if ($WITH_RETURN == 1)  {
                            $delivery_payer_seq = 'О';
                            switch ($TYPE_PAYS)
                            {
                                case 251:
                                    $delivery_payer_seq = 'О';
                                    break;
                                case 252:
                                    $delivery_payer_seq = 'П';
                                    break;
                                case 253:
                                    $delivery_payer_seq = 'Д';
                                    break;
                            }

                            $DATE_TAKE_FROM_RET    = substr($datecall,6,4).'-'.substr($datecall,3,2).'-'.substr($datecall,0,2).' '.$_POST['callcourtime_from'].':00';
                            $DATE_TAKE_TO_RET      = substr($datecall,6,4).'-'.substr($datecall,3,2).'-'.substr($datecall,0,2).' '.$_POST['callcourtime_to'].':00';

                            $arDeliverySequenceRet = [
                                "ID"            => $z_nakl_id_ret,
                                "DATE_CREATE"   => $arPDF_RET['REQUEST']['DATE_CREATE'],
                                "INN"           => $arResult['CURRENT_CLIENT_INN'],
                                "NAME_SENDER"   => $NAME_SENDER,
                                "PHONE_SENDER"  => $PHONE_SENDER,
                                "COMPANY_SENDER"=> $COMPANY_SENDER,
                                "CITY_SENDER"   => $city_sender_ret,
                                "INDEX_SENDER"  => $INDEX_SENDER,
                                "ADDRESS_SENDER" => $ADRESS_SENDER,
                                "ADDRESS_RECIPIENT"  => $ADRESS_RECIPIENT,
                                "NAME_RECIPIENT" =>"-",
                                "PHONE_RECIPIENT"   =>  $PHONE_RECIPIENT,
                                "COMPANY_RECIPIENT" =>  $COMPANY_RECIPIENT,
                                "CITY_RECIPIENT"    =>  $city_recipient_ret,
                                "CITY_RECIPIENT_NON" => $CITY_RECIPIENT,
                                "INDEX_RECIPIENT"   => $INDEX_RECIPIENT,
                                'DATE_TAKE_FROM'    => NewQuotes($DATE_TAKE_FROM_RET),
                                'DATE_TAKE_TO'      => NewQuotes($DATE_TAKE_TO_RET),
                                "TYPE" =>$arResult['DEAULTS']['TYPE_PACK'],
                                "DELIVERY_TYPE" => $delivery_type_seq,
                                "DELIVERY_PAYER" =>$delivery_payer_seq,
                                "PAYMENT_TYPE" =>$payment_type_seq,
                                "DELIVERY_CONDITION" =>$delivery_condition_seq,
                                "PAYMENT_AMOUNT" => "0",
                                "INSTRUCTIONS" => $newInstructionsret,
                                "PLACES" =>$total_place,
                                "WEIGHT" =>$total_weight,
                                "SIZE_1" => 0,
                                "SIZE_2" => 0,
                                "SIZE_3" => 0,
                                "FILES" =>"",
                                "InternalNumber" => $_POST['InternalNumber'],
                                "DocNumber" => $number_nakl_ret,
                                "TRANSPORT_TYPE" => (int)$TRANSPORT_TYPE,
                                "ISOFFICE" => $isoffice,
                            ];
                            $m = [];
                            foreach ($arDeliverySequenceRet as $kk => $vv)
                            {
                                $m[$kk] = iconv('windows-1251','utf-8', $vv);
                            }
                            $arParamsJsonRet = [
                                'ListOfDocs' => "[".json_encode($m)."]"
                            ];
                            $error = json_last_error_msg();
                        }

                        /* отправляем в 1с */

                        if ($arResult['CURRENT_CLIENT'] == ID_SUKHOI) {
                            set_time_limit(0);
                            $i = 1;
                            while (true) {
                                $result = $client->SetDocsListClient($arParamsJson);
                                $mResult = $result->return;
                                $obj = json_decode($mResult, true);
                                if ($i > 20){
                                    break;
                                }
                                if (isset($obj['Doc_1']["ID"]) && $obj['Doc_1']["ID"] > 0) {
                                    $obj['Doc_1']['DATE'] = date("d.m.Y H:i:s");
                                    break;
                                } else {
                                    $obj = [];
                                    $obj['Doc_1']['ERROR'] = "not response from 1c";
                                    $obj['Doc_1']['DATE'] = date("d.m.Y H:i:s");
                                    $obj['Doc_1']['arDeliverySequence'] = convArrayToUTF($arDeliverySequence);
                                    sleep(5);
                                }
                                $i++;
                            }
                            if (!isset($obj['Doc_1']["ID"])) {
                                $arEventFields['MESS_ERR_1C'] = "Вызов курьера из ЛК был произведен, но из-за ошибки соединения с 1С в очередь не попал!";
                                $event = new CEvent;
                                $event->SendImmediate("LK_WARN_SUKHOY", "S5", [
                                    "WARN" => "<p>На сайте оформлена заявка от компании Сухой. <br>
                                                 <span style='font-weight: bold'>По причине сбоя она не может попасть 
                                                 в очередь заявок в 1с!</span><br>
                                                 Данное сообщение отправлено автоматически. <br>
                                                 Было произведено 5 неуспешных попыток повторной постановки Заявки в 
                                                 очередь через каждые 2 минуты.<br>
                                                 </p>",
                                    "ID" => $z_nakl_id,
                                    "DocNumber" => $number_nakl,
                                    "Date" => date("d.m.Y H:i:s"),
                                ], "N", 287);
                            }

                        }else{
                            $i = 1;
                            while(true) {
                                $result = $client->SetDocsListClient($arParamsJson);
                                $mResult = $result->return;
                                $obj = json_decode($mResult, true);
                                if ($i > 20){
                                    break;
                                }
                                if (isset($obj['Doc_1']["ID"]) && $obj['Doc_1']["ID"] > 0) {
                                    $obj['Doc_1']['DATE'] = date("d.m.Y H:i:s");
                                    break;
                                } else {
                                    $obj = [];
                                    $obj['Doc_1']['ERROR'] = "not response from 1c";
                                    $obj['Doc_1']['DATE'] = date("d.m.Y H:i:s");
                                    $obj['Doc_1']['arDeliverySequence'] = convArrayToUTF($arDeliverySequence);
                                    sleep(5);
                                }
                                $i++;
                            }
                            if (!empty($obj['Doc_1']["ID"]) && $obj['Doc_1']["ID"] > 0) {
                                $ar_res = arFromUtfToWin($obj);
                                AddToLogs('RESPONSEInvoice1c',
                                    [   'Count' => $i,
                                        'Response'=>$ar_res,
                                        'Date'=>date("d.m.Y H:i:s")
                                    ] );
                                CIBlockElement::SetPropertyValuesEx($z_nakl_id, 83,
                                    [
                                        977=>'Y'   // курьер вызван
                                    ]);

                            }else{
                                $arEventFields['MESS_ERR_1C'] = 'Вызов курьера из ЛК был произведен, но из-за ошибки соединения с 1С в очередь не попал!';
                                CIBlockElement::SetPropertyValuesEx($z_nakl_id, 83,
                                    [
                                        977=>''    // курьер не вызван
                                    ]);
                            }
                            // отправляем в 1с возвратную накладную Абсолют страхование
                            if ($WITH_RETURN == 1)  {
                                $i = 1;
                                while ($i <= 20) {
                                    $result = $client->SetDocsListClient($arParamsJsonRet);
                                    $mResult = $result->return;
                                    $obj = json_decode($mResult, true);
                                    if (isset($obj['Doc_1']["ID"]) && $obj['Doc_1']["ID"] > 0) {
                                        $obj['Doc_1']['DATE'] = date("d.m.Y H:i:s");
                                        break;
                                    } else {
                                        $obj = [];
                                        $obj['Doc_1']['ERROR'] = "not response from 1c";
                                        $obj['Doc_1']['DATE'] = date("d.m.Y H:i:s");
                                        $obj['Doc_1']['arDeliverySequence'] = convArrayToUTF($arDeliverySequence);
                                        sleep(1);
                                    }
                                    $i++;
                                }
                                if (!empty($obj['Doc_1']["ID"]) && $obj['Doc_1']["ID"] > 0) {
                                    $ar_res = arFromUtfToWin($obj);
                                    AddToLogs('RESPONSEInvoice1c',
                                        [   'Count' => $i,
                                            'Response'=>$ar_res,
                                            'Date'=>date("d.m.Y H:i:s")
                                        ] );
                                    CIBlockElement::SetPropertyValuesEx($z_nakl_id_ret, 83,
                                        [
                                            977=>'Y'   // курьер вызван
                                        ]);

                                }else{
                                    $arEventFields['MESS_ERR_1C'] = 'Вызов курьера на обратный забор из ЛК был произведен, но из-за ошибки в очередь не попал!';
                                    CIBlockElement::SetPropertyValuesEx($z_nakl_id_ret, 83,
                                        [
                                            977=>''    // курьер не вызван
                                        ]);
                                }

                            }

                        }
                        $event = new CEvent;
                        if($TRANSPORT_TYPE==1){
                            $event->SendImmediate("NEWPARTNER_LK", "S5", $arEventFields, "N", 290);
                        }
                        if($arResult['CURRENT_CLIENT'] == ID_SUKHOI) $arEventFields['CITY_RECIPIENT'] = 'Город получателя: ' .
                            '<strong>' . $CITY_RECIPIENT . '</strong>';
                        $event->SendImmediate("NEWPARTNER_LK", "S5", $arEventFields, "N", 220, [$fileId]);
                        CFile::Delete($fileId);
                        AddToLogs('RESPONSEInvoice1c', ['Response'=>$obj,  'Date'=>date("d.m.Y H:i:s")] );

                        // прошла посылка в нашу очередь НЕ  успешна

                        $DATE_TAKE_FROM    = substr($_POST['callcourierdate'],6,4).'-'.substr($_POST['callcourierdate'],3,2).'-'.substr($_POST['callcourierdate'],0,2).' '.$_POST['callcourtime_from'].':00';
                        $DATE_TAKE_TO      = substr($_POST['callcourierdate'],6,4).'-'.substr($_POST['callcourierdate'],3,2).'-'.substr($_POST['callcourierdate'],0,2).' '.$_POST['callcourtime_to'].':00';
                        // кладем в лог наши даты
                        $BDate = $_POST['callcourierdate'].' '.$_POST['callcourtime_from'].':00';
                        $EDate = $_POST['callcourierdate'].' '.$_POST['callcourtime_to'].':00';
                        $SDATE = " DATE_CREATE:: ".$arPDF['REQUEST']['DATE_CREATE']." BDate=".$BDate." EDate=".$EDate."  z_nakl_id=".$z_nakl_id." number_nakl=".$number_nakl."</br>\n\n";
                        $MDATE  = $DATE_TAKE_FROM." ".$DATE_TAKE_TO;

                        /* если город добавлен из формы добавления, деактивировать его в базе */
                       /* if($_POST['CITY_RECIPIENT_ID']){
                            $id_el = (int)NewQuotes($_POST['CITY_RECIPIENT_ID']);
                            $el_city= new CIBlockElement;
                            $el_city->Update($id_el, ["ACTIVE"=>"N"]);
                        }*/

                        //}
                    }
                    //вызов курьера


                    if (isset($_POST['add-print']))
                    {
                        /* если город добавлен из формы добавления, деактивировать его в базе */
                        /*if($_POST['CITY_RECIPIENT_ID']){
                            $id_el = (int)NewQuotes($_POST['CITY_RECIPIENT_ID']);
                            $el_city= new CIBlockElement;
                            $el_city->Update($id_el, ["ACTIVE"=>"N"]);
                        }*/
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
                        /* если город добавлен из формы добавления, деактивировать его в базе */
                        /*if($_POST['CITY_RECIPIENT_ID']){
                            $id_el = (int)NewQuotes($_POST['CITY_RECIPIENT_ID']);
                            $el_city= new CIBlockElement;
                            $el_city->Update($id_el, ["ACTIVE"=>"N"]);
                        }*/
                        /* все кроме Нацимбио (23522997) */
                        if ($arResult['CURRENT_CLIENT'] != 23522997){
                            LocalRedirect("/index.php?invoice_made=$number_nakl");
                            exit();
                        }
                        LocalRedirect("/index.php");
                        exit();

                    }
                }
            }

            $arResult['ERRORS'] = (is_array($arResult['ERRORS'])) ? $arResult['ERRORS'] : [];
            $arResult['ERR_FIELDS'] = (is_array($arResult['ERR_FIELDS'])) ? $arResult['ERR_FIELDS'] : [];
            AddToLogs('InvAddErrors',array_merge($arResult['ERRORS'], $arResult["ERR_FIELDS"]));
            /* если город добавлен из формы добавления, деактивировать его в базе */
           /* if($_POST['CITY_RECIPIENT_ID']){
                $id_el = (int)NewQuotes($_POST['CITY_RECIPIENT_ID']);
                $el_city= new CIBlockElement;
                $el_city->Update($id_el, ["ACTIVE"=>"N"]);
            }*/
            //$_POST = [];
        }

        /* end сохранение накладной и вызов курьера   */
        /*  7 if end */

        $br = $arResult['USER_IN_BRANCH'] ? $arResult['CURRENT_BRANCH'] : false;
        //print_r($arResult['DEAULTS']);
        $arResult['TYPE_CLIENT_SENDERS'] = ($arResult['DEAULTS']['MERGE_SENDERS'] == 'Y') ? 777 : 259;
        $arResult['TYPE_CLIENT_RECIPIENTS'] = ($arResult['DEAULTS']['MERGE_RECIPIENTS'] == 'Y') ? 777 : 260;
        $arResult['SENDERS'] = GetListContractors($arResult['CURRENT_CLIENT'], $arResult['TYPE_CLIENT_SENDERS'],
            false, '', ["NAME"=>"ASC"], false, false, false, $br);

        if (count($arResult['SENDERS']) == 0)
        {
            $props = [
                579 => $arResult['CURRENT_CLIENT'],
                580 => 259,
                574 => $USER->GetFullName(),
                575 => $arResult['AGENT']['PROPERTY_PHONES_VALUE'],
                576 => $arResult['AGENT']['PROPERTY_CITY_VALUE'],
                577 => '',
                578 => $arResult['AGENT']['PROPERTY_ADRESS_VALUE'],
                581 => 1,
                668 => false
            ];
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
                if ((int)($arResult['BRANCH_INFO']['PROPERTY_HEAD_BRANCH_VALUE'] == 0))
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
            //->Add
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
    /*  -0.4 if begin */
    if ($arResult['MODE'] == '1c')
    {
        /* тут вложенность из-за ошибок */
        /*  -0.4.1 if begin */
        $ppp = [];
        if(!isset($_GET['Region'])){
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
                            $arResult['REQUESTS'] = [];
                            //TO_DELIVER_BEFORE_DATE 772
                            $res = CIBlockElement::GetList(
                                ['id' => 'asc'],
                                ["IBLOCK_ID" => 83, "PROPERTY_CREATOR" => $agent_inn, "PROPERTY_STATE" => 257],
                                false,
                                false,
                                [
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
                                    "PROPERTY_TO_DELIVER_BEFORE_DATE",
                                    "PROPERTY_TYPE_PAYS",
                                    "PROPERTY_PAYS",
                                    "PROPERTY_PAYMENT",
                                    "PROPERTY_FOR_PAYMENT",
                                    "PROPERTY_COST",
                                    "PROPERTY_PLACES",
                                    "PROPERTY_WEIGHT",
                                    "PROPERTY_DIMENSIONS",
                                    "PROPERTY_INSTRUCTIONS",
                                    "PROPERTY_INNER_NUMBER_CLAIM",
                                    "PROPERTY_SUMM_DEV"
                                ]
                            );
                            while ($ob = $res->GetNextElement())
                            {
                                $a = $ob->GetFields();

                                // вписываем доставить до
                                if (trim($a['PROPERTY_TO_DELIVER_BEFORE_DATE_VALUE']['TEXT'])!=''){
                                    $instruction = $a['PROPERTY_INSTRUCTIONS_VALUE']['TEXT'];
                                } else {
                                    $instruction = $a['PROPERTY_INSTRUCTIONS_VALUE']['TEXT'];
                                }

                                $arResult['REQUESTS'][] = [
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
                                    'INSTRUCTIONS' => $instruction,
                                    'InternalNumber' => $a["PROPERTY_INNER_NUMBER_CLAIM_VALUE"],
                                    'WHOSE_ORDER' => '123456789',
                                    'SUMM_DEV' => $a['PROPERTY_SUMM_DEV_VALUE']
                                ];
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
            /*  -0.4.1 if end */
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
        }else{
            if(!empty($_GET['Region'])){
                $ppp = [
                    "1597534682",
                    "1597534682",
                    "12032018",
                    "1597534682",
                    "wx87chcy",
                    "3001152152",
                    "18062019",
                    "XeT74g",
                    "26032020"
                ];


                $regnum = (int)$_GET['Region'];
                $arSelect = [
                    "NAME",
                    "IBLOCK_ID",
                    "ID",
                    "PROPERTY_*",
                ];
                $arFilter = [
                    "NAME" => $regnum
                ];
                $res = GetInfoArr(false,false, 106, $arSelect, $arFilter, false);
                $arr = [];
                $err = "";
                $rand_s = [
                    "&","^","%","@","$","#","~","-","_"
                ];
                $cr = count($rand_s);

                foreach($ppp as $key=>$value){
                    if($res[0]["PROPERTIES"]["PASSWORD"]["VALUE"] == md5($value)){
                        $pq = $value;
                        //$c = strlen ($pq);

                        $c = 4;
                        for ($i = 0; $i <= $c; $i++) {
                            $rnd = rand(0,$c-1);
                            $crr = rand(1,$cr);
                            $pq = substr_replace($pq, $rand_s[$crr],$rnd , 0);
                        }

                        break;
                    }
                }
                if(!empty($res)){
                    // AddToLogs("testRegion",  $res);
                    foreach($res as $key=>$value){
                        $arr[] = [
                            "REGION" => $value["NAME"],
                            "ORGANIZATION" => $value["PROPERTIES"]["ORGANIZATION"]["VALUE"],
                            "CITY" =>  $value["PROPERTIES"]["CITY"]["VALUE"],
                            "WEB_URL" => $value["PROPERTIES"]["WEB_URL"]["VALUE"],
                            "WEB_URL_GPS" => $value["PROPERTIES"]["WEB_URL_GPS"]["VALUE"],
                            "LOGIN" => $value["PROPERTIES"]["LOGIN"]["VALUE"],
                            "PASSWORD" => $pq,
                        ];
                    }

                    unset($ppp);
                }else{
                    $err = " Региональный код не найден ";
                }

                $arResult["REQUESTS"] = [
                    "REQUESTS" => $arr,
                    "ERROR" => $err
                ];
                $arResult["REQUESTS"] = convArrayToUTF($arResult["REQUESTS"]);
                $arResult['RES_JSON'] = json_encode($arResult["REQUESTS"]);
            }
        }


    }
    /*  -0.3 if begin */
    /* вывод списка накладных в формате ексель из ЛК */
    if ($arResult['MODE'] === 'list_xls')
    {
        $arData = $_SESSION['REPORTEXCEL'];
        include_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/PhpExcel/Classes/PHPExcel.php';
        $arARCHIVEutf = [
            [
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
            ]
        ];
        if ((!$arResult['LIST_OF_BRANCHES']) || ($arResult['USER_IN_BRANCH']))
        {
            unset($arARCHIVEutf[0][3]);
        }
        $k=1;
        foreach ($arData as $r){
            $arARCHIVEutf[$k] = [
                iconv('windows-1251', 'utf-8', $r['NAME']),
                iconv('windows-1251', 'utf-8', $r['state_text']),
                substr($r['DATE_CREATE'],0,10)
            ];
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

        $pExcel = new PHPExcel();
        $pExcel->setActiveSheetIndex(0);
        $aSheet = $pExcel->getActiveSheet();
        $pExcel->getDefaultStyle()->getFont()->setName('Arial');
        $pExcel->getDefaultStyle()->getFont()->setSize(10);
        $Q = iconv("windows-1251", "utf-8", 'Накладные');
        $aSheet->setTitle($Q);
        $head_style = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ]
        ];
        $i = 1;
        $arJ = ['A','B','C','D','E','F','G','H','I','J','K','L','M'];
        foreach  ($arARCHIVEutf as $k)
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
        include_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/PhpExcel/Classes/PHPExcel/Writer/Excel5.php';
        // AddToLogs('return', ['obj'=>$pExcel]);
        $objWriter = new PHPExcel_Writer_Excel5($pExcel);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="Накладные '.date('d.m.Y').'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        exit();
    }

    if ($arResult['MODE'] === 'reportv_xls')
    {
        if (strlen($_POST['DATA_REPORTV']))
        {
            $areq = iconv('windows-1251', 'utf-8', $_POST['DATA_REPORTV']);
            $arDataJ = json_decode($areq, true);
            include_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/PhpExcel/Classes/PHPExcel.php';
            //AddToLogs('return',  ['request'=>$areq, 'post_array'=>$arDataJ]);

            $arData = [];
            $arData[] =
                [ iconv('windows-1251', 'utf-8','Номер накладной'),
                    iconv('windows-1251', 'utf-8','Статус'),
                    iconv('windows-1251', 'utf-8','ДатаСтатус'),
                    iconv('windows-1251', 'utf-8','Дата'),
                    iconv('windows-1251', 'utf-8','Город отправителя'),
                    iconv('windows-1251', 'utf-8','Компания отправителя'),
                    iconv('windows-1251', 'utf-8','Город получателя'),
                    iconv('windows-1251', 'utf-8','Компания получателя'),
                    iconv('windows-1251', 'utf-8','Получатель'),
                    iconv('windows-1251', 'utf-8','Кол.'),
                    iconv('windows-1251', 'utf-8','Вес'),
                    iconv('windows-1251', 'utf-8','Об. вес')

                ];
            $i = 1;
            foreach ($arDataJ as $value){
                $arData[$i] = [
                    $value['NAME'],
                    $value['state_text'],
                    $value['state_date'],
                    $value['DATE_CREATE'],
                    $value['PROPERTY_CITY_SENDER_NAME'],
                    $value['PROPERTY_COMPANY_SENDER_VALUE'],
                    $value['PROPERTY_CITY_RECIPIENT_NAME'],
                    $value['PROPERTY_COMPANY_RECIPIENT_VALUE'],
                    $value['PROPERTY_NAME_RECIPIENT_VALUE'],
                    $value['PROPERTY_PLACES_VALUE'],
                    $value['PROPERTY_WEIGHT_VALUE'],
                    $value['PROPERTY_OB_WEIGHT']
                ];
                $i++;
            }
            //AddToLogs('return',  $arData);
            $pExcel = new PHPExcel();
            $pExcel->setActiveSheetIndex(0);
            $aSheet = $pExcel->getActiveSheet();
            $pExcel->getDefaultStyle()->getFont()->setName('Arial');
            $pExcel->getDefaultStyle()->getFont()->setSize(10);
            $Q = iconv("windows-1251", "utf-8", 'Накладные');
            $aSheet->setTitle($Q);
            $head_style = [
                'font' => [
                    'bold' => true,
                ],
                'alignment' => [
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ]
            ];
            $i = 1;
            $arJ = ['A','B','C','D','E','F','G','H','I','J','K','L'];

            foreach  ($arData as $items)
            {
                //AddToLogs('return',  $items);
                $n = 0;
                foreach ($items as $val)
                {
                    //AddToLogs('return',  ["res"=>$val]);
                    $num_sel = $arJ[$n].$i;
                    $aSheet->setCellValue($num_sel,$val);
                    $n++;
                }
                $i++;
            }
            $i--;
            foreach ($arJ as $cc)
            {
                $aSheet->getColumnDimension($cc)->setWidth(17);
            }
            $aSheet->getStyle('A1:L1')->applyFromArray($head_style);
            $aSheet->getStyle('A1:L'.$i)->getAlignment()->setWrapText(true);
            $aSheet->getStyle('A1:L'.$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            //AddToLogs('return', ['obj2'=>$_SERVER['DOCUMENT_ROOT']]);
            include_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/PhpExcel/Classes/PHPExcel/Writer/Excel5.php';
            /* $objWriter = new PHPExcel_Writer_Excel5($pExcel);
             $objWriter->save( $_SERVER['DOCUMENT_ROOT'].'/report.xls');
             echo "<a href='http://delivery-russia.ru/report.xls'>Скачать список накладных С Возвратом</a>";*/
            $objWriter = new PHPExcel_Writer_Excel5($pExcel);
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="Накладные С Возвратом'.date('d.m.Y').'.xls"');
            header('Cache-Control: max-age=0');
            $objWriter->save('php://output');
        }
    }

    /*  -0.2 if begin */
    if ($arResult['MODE'] == 'acceptance')
    {

        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') { $link = "https"; } else {$link = "http";}
        $link .= "://";
        $link .= $_SERVER['HTTP_HOST'];
        $link .= $_SERVER['REQUEST_URI'];

        // кладем содержимое всего поста
        //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/post_post.txt', "-------------------------------\n", FILE_APPEND);
        //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/post_post.txt', print_r($_POST, true), FILE_APPEND);
        //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/post_post.txt', $link, FILE_APPEND);
        //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/post_post.txt', "-------------------------------\n", FILE_APPEND);

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
            /* тут вложенность связана стипом ошибок (тот же паттерн)*/
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
                        //TO_DELIVER_BEFORE_DATE 772
                        $res = CIBlockElement::GetList(
                            ["id" => "desc"],
                            ["IBLOCK_ID" => 83, "NAME" => trim($arResponse['Number'])],
                            false,
                            ["nTopCount" => 1],
                            [
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
                                "PROPERTY_TO_DELIVER_BEFORE_DATE",
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
                                "PROPERTY_WHOSE_ORDER",
                                "PROPERTY_INNER_NUMBER_CLAIM",
                                "PROPERTY_TRANSPORT_TYPE",
                                "PROPERTY_WITH_RETURN",
                                "PROPERTY_CENTER_EXPENSES.NAME",
                                "PROPERTY_ISOFFICE"

                            ]
                        );
                        if ($ob = $res->GetNextElement())
                        {

                            $reqv = $ob->GetFields();

                            // взять из курьеров == $reqv['ID']
                            // 87 NUMBER_INVOICE_KEY  => DATE
                            // -----------------
                            $arSelect = ["ID", "IBLOCK_ID", "NAME", "DATE ","PROPERTY_*"];
                            $arFilter = ["IBLOCK_ID"=>87,
                                [
                                    "PROPERTY_NUMBER_INVOICE_KEY" => $reqv['NAME'],
                                ]
                            ];
                            $res = CIBlockElement::GetList([], $arFilter, false, [], $arSelect);
                            while($ob = $res->GetNextElement()){
                                $arFields = $ob->GetFields();
                                $a= explode (" ",$arFields['~PROPERTY_613'][0]);
                                $b= explode (" ",$arFields['~PROPERTY_613'][1]);
                                $b1 =  $a[0]; // год
                                $b2 =  $a[1]; // от
                                $b3 =  $b[1]; // до
                            }
                            //if ($reqv["PROPERTY_STATE_ENUM_ID"] == 257)
                            //{
                            $reqv["PROPERTY_OB_WEIGHT"] = 0;
                            $reqv["PROPERTY_Dimensions"] = [];
                            if (strlen($reqv['PROPERTY_PACK_DESCRIPTION_VALUE']))
                            {
                                $reqv['PACK_DESCR'] = json_decode(htmlspecialcharsBack($reqv['PROPERTY_PACK_DESCRIPTION_VALUE']), true);
                                foreach ($reqv['PACK_DESCR'] as $k => $str)
                                {
                                    $reqv["PROPERTY_OB_WEIGHT"] = $reqv["PROPERTY_OB_WEIGHT"] + $str['gabweight'];
                                    $reqv["PROPERTY_Dimensions"][] = [
                                        "WEIGHT" => ((float)$str['weight'] > 0) ? (float)$str['weight'] : 0,
                                        "SIZE_1" => ((float)$str["size"][0] > 0) ? (float)$str["size"][0] : 0,
                                        "SIZE_2" => ((float)$str["size"][1] > 0) ? (float)$str["size"][1] : 0,
                                        "SIZE_3" => ((float)$str["size"][2] > 0) ? (float)$str["size"][2] : 0,
                                        "PLACES" => (int)$str["place"],
                                        "NAME" => iconv('utf-8','windows-1251',$str['name'])
                                    ];
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
                                $reqv["PROPERTY_Dimensions"][] = [
                                    "WEIGHT" => ((float)$reqv['PROPERTY_WEIGHT_VALUE'] > 0) ? (float)$reqv['PROPERTY_WEIGHT_VALUE'] : 0,
                                    "SIZE_1" => ((float)$reqv['PROPERTY_DIMENSIONS_VALUE'][0] > 0) ? (float)$reqv['PROPERTY_DIMENSIONS_VALUE'][0] : 0,
                                    "SIZE_2" => ((float)$reqv['PROPERTY_DIMENSIONS_VALUE'][1] > 0) ? (float)$reqv['PROPERTY_DIMENSIONS_VALUE'][1] : 0,
                                    "SIZE_3" => ((float)$reqv['PROPERTY_DIMENSIONS_VALUE'][2] > 0) ? (float)$reqv['PROPERTY_DIMENSIONS_VALUE'][2] : 0,
                                    "PLACES" => (int)$reqv['PROPERTY_PLACES_VALUE'],
                                    "NAME" => ''
                                ];
                            }
                            $reqv['PACK_GOODS'] = '';
                            if (strlen($reqv['PROPERTY_PACK_GOODS_VALUE']))
                            {
                                $reqv['PACK_GOODS'] = json_decode(htmlspecialcharsback($reqv['PROPERTY_PACK_GOODS_VALUE']), true);
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
                            if ((int)$reqv['PROPERTY_BRANCH_VALUE'] > 0)
                            {
                                $db_props = CIBlockElement::GetProperty(89, $reqv['PROPERTY_BRANCH_VALUE'],
                                    ["sort" => "asc"], ["CODE"=>"IN_1C_CODE"]);
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
                            $property_enums = CIBlockPropertyEnum::GetList(["SORT"=>"ASC"], ["IBLOCK_ID"=>83, "CODE"=>"TYPE_DELIVERY", "ID" => $reqv['PROPERTY_TYPE_DELIVERY_ENUM_ID']]);
                            if($enum_fields = $property_enums->GetNext())
                            {
                                $reqv['TO_1C_DELIVERY_TYPE'] = $enum_fields['XML_ID'];
                            }
                            $property_enums = CIBlockPropertyEnum::GetList(["SORT"=>"ASC"], ["IBLOCK_ID"=>83, "CODE"=>"TYPE_PAYS", "ID" => $reqv['PROPERTY_TYPE_PAYS_ENUM_ID']]);
                            if($enum_fields = $property_enums->GetNext())
                            {
                                $reqv['TO_1C_DELIVERY_PAYER'] = $enum_fields['XML_ID'];
                            }
                            $property_enums = CIBlockPropertyEnum::GetList(["SORT"=>"ASC"], ["IBLOCK_ID"=>83, "CODE"=>"PAYMENT", "ID" => $reqv['PROPERTY_PAYMENT_ENUM_ID']]);
                            if($enum_fields = $property_enums->GetNext())
                            {
                                $reqv['TO_1C_PAYMENT_TYPE'] = $enum_fields['XML_ID'];
                            }
                            $property_enums = CIBlockPropertyEnum::GetList(["SORT"=>"ASC"], ["IBLOCK_ID"=>83, "CODE"=>"WHO_DELIVERY", "ID" => $reqv['PROPERTY_WHO_DELIVERY_ENUM_ID']]);
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
                            if ((int)$reqv['PROPERTY_WHOSE_ORDER_VALUE'] > 0)
                            {
                                $db_props = CIBlockElement::GetProperty(40, (int)$reqv['PROPERTY_WHOSE_ORDER_VALUE'], array("sort" => "asc"), array("CODE"=>"INN"));
                                if($ar_props = $db_props->Fetch())
                                {
                                    if (strlen(trim($ar_props["VALUE"])))
                                    {
                                        $WHOSE_ORDER_ID = $ar_props["VALUE"];
                                    }

                                }
                            }

                            $date_take_without_date = explode (" ",$date_take_from);

                            $agentInfo = GetCompany($reqv['PROPERTY_CREATOR_VALUE']);
                            $arManifestTo1c = [
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
                                "DATE_TAKE_TO" => $date_take_from,
                                "DATE_TAKE_FROM" => $date_take_without_date[0],
                                "DELIVERY_TYPE" => $reqv['TO_1C_DELIVERY_TYPE'],
                                "DELIVERY_PAYER" => $reqv['TO_1C_DELIVERY_PAYER'],
                                "DATE_TAKE_FROM1" => $b1,
                                "TIME_TAKE_FROM1" => $b2,
                                "TIME_TAKE_TO1"   => $b3,
                                "PAYMENT_TYPE" => $reqv['TO_1C_PAYMENT_TYPE'],
                                "DELIVERY_CONDITION" => $reqv['TO_1C_DELIVERY_CONDITION'],
                                "INSTRUCTIONS" => $reqv['PROPERTY_INSTRUCTIONS_VALUE']['TEXT'],
                                "TYPE" => ($reqv['PROPERTY_TYPE_PACK_ENUM_ID'] == 247) ? 0 : 1,
                                "Dimensions" => $reqv['PROPERTY_Dimensions'],
                                'ID' => $reqv['ID'],
                                'ID_BRANCH' => $reqv['BRANCH_CODE'],
                                'InternalNumber' => $reqv['PROPERTY_INNER_NUMBER_CLAIM_VALUE'], // Внутренний Номер Заявки
                                "TRANSPORT_TYPE" => (int)$reqv['PROPERTY_TRANSPORT_TYPE_VALUE'],
                                "RETURN" => (int)$reqv['PROPERTY_WITH_RETURN_VALUE'],
                                "CENTER_EXPENSES" => $reqv['PROPERTY_CENTER_EXPENSES_NAME'],
                                "ISOFFICE" => (int)$reqv['PROPERTY_ISOFFICE_VALUE'],
                                //'Goods' => $reqv['PACK_GOODS']
                            ];
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
                            //}
                            //else
                            //{
                            //    $arResult["ERRORS"][] = 'Неверный статус накладной '.trim($arResponse['Number']).': ' .$reqv["PROPERTY_STATE_VALUE"];
                            //}
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
                                AddToLogs('ZaprosFromDms', ['error' => $orderTo1c['errors']]);
                            }
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
        $arResult['RESULTS'] = [
            'ERRORS' => $arResult["ERRORS"],
            'INFO' => $arManifestTo1c
        ];
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
    /*  -0.1 if begin */
    if ($arResult['MODE'] == 'upload')
    {
        $arLogs = [];
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
        if ((int)$arResult['CURRENT_CLIENT'] == 0)
        {
            $arResult['OPEN'] = false;
            if ($arResult['ADMIN_AGENT'])
            {
                $arResult["WARNINGS"][] = GetMessage('ERR_OPEN_ADMIN',array('#LINK#' => $arParams['LINK']));
            }
            else
            {
                $arResult["WARNINGS"][] = GetMessage('ERR_OPEN');
            }
        }
        if ($arResult['CURRENT_CLIENT'] > 0)
        {
            $arResult['CURRENT_CLIENT_COEFFICIENT_VW'] = WhatIsGabWeightCompany($arResult['CURRENT_CLIENT']);
            $db_props = CIBlockElement::GetProperty(40, $arResult['CURRENT_CLIENT'], array("sort" => "asc"),
                array("CODE"=>"INN"));

            if($ar_props = $db_props->Fetch())
            {
                $arResult['CURRENT_CLIENT_INN'] = $ar_props["VALUE"];
            }
            $arResult['OPEN'] = true;
            if (isset($_POST['upload']))
            {
                if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
                {
                    $_POST = array();
                    $arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
                }
                else
                {
                    $_SESSION[$_POST["key_session"]] = $_POST["rand"];
                    $arResult["FILE_ID"] = false;
                    if ((int)$_POST['fileid'] > 0)
                    {
                        $arResult["FILE_ID"] = (int)$_POST['fileid'];
                    }
                    else
                    {
                        $arIMAGE = $_FILES["fileupload"];
                        $arIMAGE["MODULE_ID"] = "iblock";
                        if (strlen($arIMAGE["name"])>0)
                        {
                            $res = CFile::CheckFile($arIMAGE, 0, false, "xml");
                            if (strlen($res)>0)
                            {
                                $arResult["ERRORS"][] = $res;
                            }
                            else
                            {
                                $arResult["FILE_ID"] = CFile::SaveFile($arIMAGE, "iblock");
                                if ($arResult["FILE_ID"])
                                {
                                    $arResult["FILE_VALUES"] = CFile::GetFileArray($arResult["FILE_ID"]);
                                    $arResult["FILE_PATH"] = $arResult["FILE_VALUES"]["SRC"];
                                    $arLogs['FILE_PATH'] = $arResult["FILE_PATH"];
                                    $arResult["CONTENT_TYPE"] = $arResult["FILE_VALUES"]["CONTENT_TYPE"];
                                    if ($arResult["CONTENT_TYPE"] == 'text/xml')
                                    {
                                        $global_file = $_SERVER['DOCUMENT_ROOT'].$arResult["FILE_PATH"];
                                        $xml = simplexml_load_string(file_get_contents($global_file));
                                        $arXml = xml2array($xml);
                                        if (is_array($arXml['Sheeper']))
                                        {
                                            $arSettings = array();
                                            $settingsJson = $arResult['AGENT']['PROPERTY_ACCOUNT_LK_SETTINGS_VALUE']['TEXT'];
                                            if (strlen($settingsJson))
                                            {
                                                $arSettings = json_decode(htmlspecialcharsBack($settingsJson), true);
                                            }
                                            $arResult['USER_SETTINGS'] = $arSettings[$arResult["USER_ID"]];
                                            $arResult['DEAULTS'] = array(
                                                'PLACES' => 1,
                                                'TYPE_DELIVERY' => ((int)$arResult['USER_SETTINGS']['TYPE_DELIVERY'] > 0) ? (int)$arResult['USER_SETTINGS']['TYPE_DELIVERY'] : 244,
                                                'TYPE_PACK' => ((int)$arResult['USER_SETTINGS']['TYPE_PACK'] > 0) ? (int)$arResult['USER_SETTINGS']['TYPE_PACK'] : 246,
                                                'WHO_DELIVERY' => ((int)$arResult['USER_SETTINGS']['WHO_DELIVERY'] > 0) ? (int)$arResult['USER_SETTINGS']['WHO_DELIVERY'] : 248,
                                                'TYPE_PAYS' => ((int)$arResult['USER_SETTINGS']['TYPE_PAYS'] > 0) ? (int)$arResult['USER_SETTINGS']['TYPE_PAYS'] : 251,
                                                'PAYMENT' => ((int)$arResult['USER_SETTINGS']['PAYMENT'] > 0) ? (int)$arResult['USER_SETTINGS']['PAYMENT'] : 256
                                            );
                                            if (isset($arXml['Invoice']['ConsigneeFIO']))
                                            {
                                                $vrAr = $arXml['Invoice'];
                                                unset($arXml['Invoice']);
                                                $arXml['Invoice'][0] = $vrAr;
                                            }
                                            foreach ($arXml['Invoice'] as $index => $inv)
                                            {
                                                $invInfo = (array)$inv;
                                                $arXml['InvoiceNew'][$index] = $invInfo;
                                                foreach($invInfo['Good'] as $good)
                                                {
                                                    $arXml['InvoiceNew'][$index]['Goods'][] = (array)$good;
                                                }
                                                unset($arXml['InvoiceNew'][$index]['Good']);
                                                foreach($invInfo['PackDescription'] as $descr)
                                                {
                                                    $arXml['InvoiceNew'][$index]['Description'][] = (array)$descr;
                                                }
                                                unset($arXml['InvoiceNew'][$index]['PackDescription']);
                                            }
                                            unset($arXml['Invoice']);
                                            $arXmlWin = arFromUtfToWin($arXml);

                                            $city_sender = GetCityId($arXmlWin['Sheeper']['ShipperCity']);
                                            $countNakls = 0;
                                            $arLinks = array();
                                            $arLinksPrint = array();
                                            $id_in = MakeInvoiceNumberNew(1, 7, '90-');
                                            foreach ($arXmlWin['InvoiceNew'] as $index => $inv)
                                            {


                                                $number_nakl = $id_in['number'].'-'.($index+1);
                                                $city_recipient = GetCityId($inv['ConsigneeCity']);
                                                switch ($inv['TypeDelivery'])
                                                {
                                                    case 'Экспресс 2':
                                                        $TYPE_DELIVERY = 345;
                                                        break;
                                                    case 'Экспресс 4':
                                                        $TYPE_DELIVERY = 346;
                                                        break;
                                                    case 'Экспресс 8':
                                                        $TYPE_DELIVERY = 338;
                                                        break;
                                                    case 'Экспресс':
                                                        $TYPE_DELIVERY = 243;
                                                        break;
                                                    case 'Стандарт':
                                                        $TYPE_DELIVERY = 244;
                                                        break;
                                                    case 'Эконом':
                                                        $TYPE_DELIVERY = 245;
                                                        break;
                                                    case 'Склад-Склад':
                                                        $TYPE_DELIVERY = 308;
                                                        break;
                                                    default:
                                                        $TYPE_DELIVERY = $arResult['DEAULTS']['TYPE_DELIVERY'];
                                                        break;
                                                }
                                                switch ($inv['TypePack'])
                                                {
                                                    case 'Документы':
                                                        $TypePack = 246;
                                                        break;
                                                    case 'Не документы':
                                                        $TypePack = 247;
                                                        break;
                                                    default:
                                                        $TypePack = $arResult['DEAULTS']['TYPE_PACK'];
                                                        break;
                                                }
                                                switch ($inv['WhoDelivery'])
                                                {
                                                    case 'По адресу':
                                                        $WhoDelivery = 248;
                                                        break;
                                                    case 'До востребования':
                                                        $WhoDelivery = 249;
                                                        break;
                                                    case 'Лично в руки':
                                                        $WhoDelivery = 250;
                                                        break;
                                                    default:
                                                        $WhoDelivery = $arResult['DEAULTS']['WHO_DELIVERY'];
                                                        break;
                                                }
                                                switch ($inv['TypePyas'])
                                                {
                                                    case 'Отправитель':
                                                        $TypePyas = 251;
                                                        break;
                                                    case 'Получатель':
                                                        $TypePyas = 252;
                                                        break;
                                                    case 'Другой':
                                                        $TypePyas = 253;
                                                        break;
                                                    case 'Служебное':
                                                        $TypePyas = 254;
                                                        break;
                                                    default:
                                                        $TypePyas = $arResult['DEAULTS']['TYPE_PAYS'];
                                                        break;
                                                }
                                                switch($inv['Payment'])
                                                {
                                                    case 'Наличными':
                                                        $Payment = 255;
                                                        break;
                                                    case 'По счету':
                                                        $Payment = 256;
                                                        break;
                                                    case 'Банковской картой':
                                                        $Payment = 309;
                                                        break;
                                                    default:
                                                        $Payment = $arResult['DEAULTS']['PAYMENT'];
                                                        break;
                                                }
                                                if ((is_array($inv['Goods'])) && (count($inv['Goods']) > 0))
                                                {
                                                    foreach ($inv['Goods'] as $good)
                                                    {
                                                        $arGoods[] = array(
                                                            'GoodsName' => iconv('windows-1251','utf-8',$good['Name']),
                                                            'Amount' => (int)$good['Amount'],
                                                            'Price' => (float)str_replace(',', '.', $good['Price']),
                                                            'Sum' => (float)str_replace(',', '.', $good['Sum']),
                                                            'SumNDS' => (float)str_replace(',', '.', $good['SumNDS']),
                                                            'PersentNDS' => (int)$good['PersentNDS']
                                                        );
                                                    }
                                                }
                                                else
                                                {
                                                    $arGoods = array(
                                                        array(
                                                            'GoodsName' => '',
                                                            'Amount' => 0,
                                                            'Price' => 0,
                                                            'Sum' => 0,
                                                            'SumNDS' => 0,
                                                            'PersentNDS' => 20
                                                        )
                                                    );
                                                }
                                                //костыли для ИП Посконнов start
                                                if ((float)str_replace(',', '.', $inv['Weight']) > 0)
                                                {
                                                    $resecho_weight = (float)str_replace(',', '.', $inv['Weight']);
                                                }
                                                elseif ((float)str_replace(',', '.', $inv['WBWeight']) > 0)
                                                {
                                                    $resecho_weight = (float)str_replace(',', '.', $inv['WBWeight']);
                                                }
                                                else
                                                {
                                                    $resecho_weight = $arResult['DEAULTS']['WEIGHT'];
                                                }

                                                if ((int)$inv['Places'] >= 1)
                                                {
                                                    $resecho_places = (int)$inv['Places'];
                                                }
                                                elseif ((int)$inv['WBPlaceCount'] >= 1)
                                                {
                                                    $resecho_places = (int)$inv['WBPlaceCount'];
                                                }
                                                else
                                                {
                                                    $resecho_places = $arResult['DEAULTS']['PLACES'];
                                                }
                                                /*
												if (floatval(str_replace(',','.',$inv['Cost'])) > 0)
												{
													$resecho_cost = floatval(str_replace(',','.',$inv['Cost']));
												}
												elseif (floatval(str_replace(',','.',$inv['WBCost'])) > 0)
												{
													$resecho_cost = floatval(str_replace(',','.',$inv['WBCost']));
												}
												else
												{
													$resecho_cost = 0;
												}
												*/
                                                $SpecDelivery = $inv['SpecDelivery'];
                                                if (strlen(trim($inv['WBDescription'])))
                                                {
                                                    $SpecDelivery .= ' '.trim($inv['WBDescription']);
                                                }
                                                //костыли для ИП Посконнов end
                                                $resecho = array(
                                                    544 => $id_in['max_id'],
                                                    545 => $arResult['CURRENT_CLIENT'],
                                                    546 => $arXmlWin['Sheeper']['ShipperFIO'],
                                                    547 => $arXmlWin['Sheeper']['ShipperPhone'],
                                                    548 => $arXmlWin['Sheeper']['ShipperCompany'],
                                                    549 => $city_sender,
                                                    550 => $arXmlWin['Sheeper']['ShipperZip'],
                                                    551 => array('VALUE' => array('TYPE' => 'text', 'TEXT' => $arXmlWin['Sheeper']['ShipperAddress'])),
                                                    552 => $inv['ConsigneeFIO'],
                                                    553 => $inv['ConsigneePhone'],
                                                    554 => $inv['ConsigneeCompany'],
                                                    555 => $city_recipient,
                                                    556 => $inv['ConsigneeZip'],
                                                    557 => $TYPE_DELIVERY,
                                                    558 => $TypePack,
                                                    559 => $WhoDelivery,
                                                    560 => $inv['DateDelivery'],
                                                    561 => $inv['TimeDelivery'],
                                                    562 => $TypePyas,
                                                    563 => $inv['TypePyasDescription'],
                                                    564 => $Payment,
                                                    565 => (float)str_replace(',', '.', $inv['Cost']),
                                                    733 => (float)str_replace(',', '.', $inv['CodCost']),
                                                    566 => (float)str_replace(',', '.', $inv['DeclaredCost']),
                                                    567 => $resecho_places,
                                                    568 => $resecho_weight,
                                                    569 => '',
                                                    570 => array('VALUE' => array('TYPE' => 'text', 'TEXT' => $SpecDelivery)),
                                                    571 => array('VALUE' => array('TYPE' => 'text', 'TEXT' => $inv['ConsigneeAddress'])),
                                                    572 => 257,
                                                    639 => $arResult['BRANCH_AGENT_BY'],
                                                    640 => $arResult['CLIENT_CONTRACT'],
                                                    641 => $arResult['CURRENT_BRANCH'],
                                                    679	=> 1,
                                                    724 => json_encode($arGoods),
                                                    737 => false

                                                );
                                                //dump($resecho);
                                                //exit;
                                                //764 => $arResult['CURRENT_BRANCH']
                                                if ((is_array($inv['Description'])) && (count($inv['Description']) > 0))
                                                {
                                                    foreach ($inv['Description'] as $descr)
                                                    {
                                                        $sizesDescr = array(
                                                            (float)str_replace(',', '.', $descr['Length']),
                                                            (float)str_replace(',', '.', $descr['Height']),
                                                            (float)str_replace(',', '.', $descr['Width'])
                                                        );
                                                        $arDescr[] = array(
                                                            'name' => iconv('windows-1251','utf-8',$descr['Name']),
                                                            'place' => (int)$descr['Places'],
                                                            'weight' => (float)str_replace(',', '.', $descr['Weight']),
                                                            'size' => array($sizesDescr[0],$sizesDescr[1],$sizesDescr[2]),
                                                            'gabweight' => (($sizesDescr[0]*$sizesDescr[1]*$sizesDescr[2])/$arResult['CURRENT_CLIENT_COEFFICIENT_VW'])
                                                        );
                                                    }
                                                }
                                                else
                                                {
                                                    $sizes = array(
                                                        (float)str_replace(',', '.', $inv['Length']),
                                                        (float)str_replace(',', '.', $inv['Height']),
                                                        (float)str_replace(',', '.', $inv['Width'])
                                                    );
                                                    $name_descr = strlen(trim($inv['TypePack'])) ? trim($inv['TypePack']) : $inv['WBOldNumber'];
                                                    $arDescr = array(
                                                        array(
                                                            'name' => iconv('windows-1251','utf-8',$name_descr),
                                                            'place' => $resecho[567],
                                                            'weight' => $resecho[568],
                                                            'size' => array($sizes[0],$sizes[1],$sizes[2]),
                                                            'gabweight' => (($sizes[0]*$sizes[1]*$sizes[2])/$arResult['CURRENT_CLIENT_COEFFICIENT_VW'])
                                                        )
                                                    );
                                                }
                                                $resecho[682] = json_encode($arDescr);
                                                $el = new CIBlockElement;
                                                $arLoadProductArray = Array(
                                                    "MODIFIED_BY" => $USER->GetID(),
                                                    "IBLOCK_SECTION_ID" => false,
                                                    "IBLOCK_ID" => 83,
                                                    "PROPERTY_VALUES" => $resecho,
                                                    "NAME" => $number_nakl,
                                                    "ACTIVE" => "Y"
                                                );
                                                //->Add
                                                if ($z_nakl_id = $el->Add($arLoadProductArray))
                                                {
                                                    $countNakls++;
                                                    $arLinks[] = '<a href="'.$arParams['LINK'].'?mode=edit&id='.$z_nakl_id.'" target="_blank">'.$number_nakl.'</a>';
                                                    $arLogs['INV_'.$z_nakl_id] = $number_nakl;
                                                    $arLinksPrint[] = $z_nakl_id;
                                                }
                                                else
                                                {
                                                    $arResult['ERRORS'][] = $error;
                                                }
                                            }
                                            if ($countNakls > 0)
                                            {
                                                if ($countNakls == 1)
                                                {
                                                    $arResult["MESSAGE"][] = 'Накладная '.implode(', ',$arLinks).' успешно загружена. <a href="'.$arParams['LINK'].'?mode=print&id='.implode(',',$arLinksPrint).'&print=Y" target="_blank">Распечатать</a>.';
                                                }
                                                else
                                                {
                                                    $arResult["MESSAGE"][] = 'Накладные '.implode(', ',$arLinks).' успешно загружены. <a href="'.$arParams['LINK'].'?mode=prints&id='.implode(',',$arLinksPrint).'&print=Y" target="_blank">Распечатать</a>.';
                                                }
                                            }
                                            else
                                            {
                                                $arResult["WARNINGS"][] = 'Не загружено ни одной накладной';
                                            }
                                        }
                                        else
                                        {
                                            $arResult["ERRORS"][] = 'Неверная структура файла';
                                        }
                                    }
                                    else
                                    {
                                        $arResult["ERRORS"][] = 'Неверный тип файла';
                                    }
                                }
                                else
                                {
                                    $arResult["ERRORS"][] = 'Не удалось сохранить файл';
                                }
                            }
                        }
                        else
                        {
                            $arResult["ERRORS"][] = 'Пустое имя файла';
                        }
                    }
                }
            }
            if(isset($_POST['upload_ex'])){
                //dump($_FILES['fileupload_ex']['type']);
                //var_dump($arResult['IndividualPrice']);
                if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
                {
                    $_POST = array();
                    $arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
                }
                else {
                    $_SESSION[$_POST["key_session"]] = $_POST["rand"];
                    $id_client = $arResult['CURRENT_CLIENT'];
                    if($_FILES['fileupload_ex']['error']==0){
                        if(!empty($_FILES['fileupload_ex']['name'])){
                            if( $_FILES['fileupload_ex']['type'] === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                                  || $_FILES['fileupload_ex']['type'] === 'application/msexcel'
                                  || $_FILES['fileupload_ex']['type'] === 'application/octet-stream'){
                                $file = $_FILES['fileupload_ex']['tmp_name'];
                                $headers = [];
                                $res_arr = [];
                                $resfile = [];
                                $resfile = parse_excel_file($file);
                                $resfile = arFromUtfToWin($resfile);

                                $el_first = trim($resfile[0][0]);
                               // var_dump($el_first);
                                $el_first_value = 'ShipperFIO';
                                if($arResult['CURRENT_CLIENT'] == ID_ABSOLUT){
                                    $el_first_value = 'Number';
                                }

                                if($el_first === $el_first_value) {
                                    foreach($resfile as $key => &$value){
                                        if($key === 0){
                                            foreach($value as $k => $val){
                                                $val = htmlspecialcharsEx($val);
                                                $headers[$k] = trim($val);
                                            }
                                        }else{
                                            foreach($value as $k => &$val){
                                                $val = trim(htmlspecialcharsEx($val));
                                            }
                                        }
                                    }

                                    unset($resfile[0]);

                                    foreach($resfile as $key => $item){

                                        foreach($item as $k => $val){

                                           $res_arr[$key][$headers[$k]] = $val;
                                        }

                                    }
                                    $id_in = MakeInvoiceNumberNew(1, 7, '90-');
                                    if($arResult['CURRENT_CLIENT'] == ID_ABSOLUT){
                                        //dump($res_arr);
                                        foreach($res_arr as $key=>$value) {
                                            $city_sender = GetCityId(strip_tags($value['CitySender']));
                                            $city_recipient = GetCityId(strip_tags($value['CityRecipient']));
                                            if ($value['DateApplicationExecution']) {
                                                $date_call = preg_replace('/-+/', '/', strip_tags($value['DateApplicationExecution']));
                                                $date_call = date('d.m.Y', strtotime($date_call));

                                            } else {
                                                $date_call = '';
                                            }
                                            $Payment = 256;  //  банк
                                            $TYPE_DELIVERY = 244; // тип Стандарт
                                            $TypePays = 251;  // отправитель
                                            $resecho_places = (int)strip_tags($value['Places']);
                                            $resecho_weight = (float)str_replace(',', '.', strip_tags($value['Weight']));
                                            $SpecDelivery = '';
                                            if($value['SpecialInstructions'] || $value['WhatShouldTake']){
                                                $SpecDelivery = strip_tags($value['SpecialInstructions']).' Что забирать - '.strip_tags($value['WhatShouldTake']);
                                            }
                                            // посчитать стоимость если есть вес
                                            $sum_dev = 0;
                                            if($resecho_weight){
                                                $total_gabweight = 0;
                                                $host_api = '';
                                                    $username =  iconv('windows-1251','utf-8',"DMSUser");
                                                    $password =  iconv('windows-1251','utf-8',"1597534682");
                                                    $host_api = "http://92.42.209.242/sd_msk/hs/Delivery/Account/7717739535/7728178835/GetTarif?ves=$resecho_weight&vesv=$total_gabweight&idcity1=$city_sender&idcity2=$city_recipient&deliverytype=c";
                                               if ($host_api){
                                                    $url = iconv('windows-1251','utf-8',$host_api);

                                                    if ($ch = curl_init()){
                                                        curl_setopt($ch, CURLOPT_URL, $url);
                                                        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                                                        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
                                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                        $output = curl_exec($ch);
                                                        curl_close($ch);
                                                        if(preg_match('/^[0-9]+(\.([0-9]){2})?$/', json_decode($output))){
                                                            $sum_dev = json_decode($output);
                                                            $sum_dev =  (float)iconv('utf-8','windows-1251',$sum_dev);
                                                        }else{
                                                            $sum_dev = '';
                                                        }
                                                    }else{
                                                        $sum_dev = '';
                                                    }
                                                }

                                            }
                                            // end sum
                                            // центр затрат
                                            $center_cost = '';
                                            $center_cost_id = false;
                                            $cc = strip_tags($value['CenterCost']);
                                            if($cc){
                                                $arrCenterCost = GetInfoArr(false, false, 115,
                                                    ['ID','NAME', 'IBLOCK_ID'], ['ACTIVE'=>'Y','NAME' => trim($cc)]);
                                                        $center_cost_id = $arrCenterCost['ID'];
                                                        $center_cost = $arrCenterCost['NAME'];
                                                    if(!$center_cost_id){
                                                    $fields = [
                                                        'IBLOCK_ID' => 115,
                                                        'NAME' => $cc
                                                    ];
                                                    $el = new CIBlockElement();
                                                    $el_id = $el->Add($fields);
                                                    $center_cost_id = $el_id;
                                                }
                                            }

                                            $number_nakl = $id_in['number'].'-'.$key;
                                            $arNakl[] = $number_nakl;

                                            $resecho = [
                                                544 => $id_in['max_id'],  /* порядковый номер */
                                                545 => $arResult['CURRENT_CLIENT'],  /* id клиента  */
                                                981 => $center_cost_id,
                                                546 => strip_tags($value['ContactPerson']),
                                                547 => strip_tags($value['PhoneSender']),
                                                548 => strip_tags($value['Company']),
                                                549 => $city_sender,
                                                551 => ['VALUE' => ['TYPE' => 'text', 'TEXT' => strip_tags($value['AdressSender'])]],
                                                552 => strip_tags($value['NameRecipient']),
                                                553 => strip_tags($value['PhoneRecipient']),
                                                554 => strip_tags($value['CompanyRecipient']),
                                                555 => $city_recipient,
                                                557 => $TYPE_DELIVERY,
                                                560 => $date_call,
                                                562 => $TypePays,
                                                563 => 'Отправитель',
                                                564 => $Payment,
                                                567 => $resecho_places,
                                                568 => $resecho_weight,
                                                569 => '',
                                                570 => ($SpecDelivery)?['VALUE' => ['TYPE' => 'text', 'TEXT' => $SpecDelivery]]:'',
                                                571 => ($value['AdressRecipient'])?['VALUE' => ['TYPE' => 'text', 'TEXT' =>
                                                    strip_tags($value['AdressRecipient'])]]:'',
                                                572 => 257,
                                                679	=> 1,
                                                737 => false,
                                                979 => $sum_dev
                                            ];
                                            $el = new CIBlockElement;
                                            $arLoadProductArray = [
                                                "MODIFIED_BY" => $USER->GetID(),
                                                "IBLOCK_SECTION_ID" => false,
                                                "IBLOCK_ID" => 83,
                                                "PROPERTY_VALUES" => $resecho,
                                                "NAME" => $number_nakl,
                                                "ACTIVE" => "Y"
                                            ];
                                            if ($z_nakl_id = $el->Add($arLoadProductArray))
                                            {
                                                $arResult["MESSAGE"][] = "Загрузка прошла успешно";
                                            }
                                            else
                                            {
                                                $arResult['ERRORS'][] = "Ошибка загрузки";
                                            }
                                         }
                                        //dump($resecho);

                                        // end foreach
                                    }else{
                                        foreach($res_arr as $key=>$value){
                                            $city_sender = GetCityId($value['ShipperCity']);
                                            $city_recipient = GetCityId($value['CityRecipient']);
                                            if ($value['DateDelivery']){
                                                $date_call = preg_replace('/-+/','/',$value['DateDelivery']);
                                                $date_call =date('d.m.Y',strtotime($date_call));
                                            }else{
                                                $date_call = '';
                                            }


                                            switch($value['Payment'])
                                            {
                                                case Loc::getMessage("CASH"):
                                                    $Payment = 255;
                                                    break;
                                                case Loc::getMessage("SCHET_B"):
                                                    $Payment = 256;
                                                    break;
                                                case Loc::getMessage("CARD"):
                                                    $Payment = 309;
                                                    break;
                                                default:
                                                    $Payment = $arResult['DEAULTS']['PAYMENT'];
                                                    break;
                                            }
                                            switch ($value['TypeDelivery'])
                                            {
                                                case Loc::getMessage("EXPRESS-2"):
                                                case Loc::getMessage("EXPRESS 2"):
                                                    $TYPE_DELIVERY = 345;
                                                    break;
                                                case Loc::getMessage("EXPRESS-4"):
                                                case Loc::getMessage("EXPRESS 4"):
                                                    $TYPE_DELIVERY = 346;
                                                    break;
                                                case Loc::getMessage("EXPRESS-8"):
                                                case Loc::getMessage("EXPRESS 8"):
                                                    $TYPE_DELIVERY = 338;
                                                    break;
                                                case Loc::getMessage("EXPRESS"):
                                                    $TYPE_DELIVERY = 243;
                                                    break;
                                                case Loc::getMessage("STANDART"):
                                                    $TYPE_DELIVERY = 244;
                                                    break;
                                                case Loc::getMessage("ECON"):
                                                    $TYPE_DELIVERY = 245;
                                                    break;
                                                case Loc::getMessage("SKLAD"):
                                                    $TYPE_DELIVERY = 308;
                                                    break;
                                                default:
                                                    $TYPE_DELIVERY = $arResult['DEAULTS']['TYPE_DELIVERY'];
                                                    break;
                                            }
                                            switch ($value['TypePack'])
                                            {
                                                case Loc::getMessage("DOCUM"):
                                                    $TypePack = 246;
                                                    break;
                                                case Loc::getMessage("UNDOC"):
                                                    $TypePack = 247;
                                                    break;
                                                default:
                                                    $TypePack = $arResult['DEAULTS']['TYPE_PACK'];
                                                    break;
                                            }
                                            switch ($value['WhoDelivery'])
                                            {
                                                case 'По адресу':
                                                    $WhoDelivery = 248;
                                                    break;
                                                case 'До востребования':
                                                    $WhoDelivery = 249;
                                                    break;
                                                case 'Лично в руки':
                                                    $WhoDelivery = 250;
                                                    break;
                                                default:
                                                    $WhoDelivery = $arResult['DEAULTS']['WHO_DELIVERY'];
                                                    break;
                                            }
                                            switch ($value['TypePyas'])
                                            {
                                                case 'Отправитель':
                                                    $TypePyas = 251;
                                                    break;
                                                case 'Получатель':
                                                    $TypePyas = 252;
                                                    break;
                                                case 'Другой':
                                                    $TypePyas = 253;
                                                    break;
                                                case 'Служебное':
                                                    $TypePyas = 254;
                                                    break;
                                                default:
                                                    $TypePyas = $arResult['DEAULTS']['TYPE_PAYS'];
                                                    break;
                                            }
                                            $resecho_places = (int)$value['Places'];

                                            $resecho_weight = (float)str_replace(',', '.', $value['Weight']);
                                            if(!$resecho_weight){
                                                $resecho_weight = 0.1;
                                            }
                                            $SpecDelivery = htmlspecialchars($value['SpecDelivery']);
                                            $arGoods = [];
                                            for($i = 0; $i <= 5; $i++){
                                                $c = (string)$i;
                                                if($c === '0')$c='';
                                                if($value['GoodName'.$c]){
                                                    $arGoods[$i]['GoodsName'] = $value['GoodName'.$c];
                                                    $arGoods[$i]['Amount'] = $value['GoodAmount'.$c];
                                                    $arGoods[$i]['GoodPrice'] = $value['GoodPrice'.$c];
                                                    $arGoods[$i]['GoodSum'] = $value['GoodSum'.$c];
                                                    $arGoods[$i]['GoodSumNDS'] = $value['GoodSumNDS'.$c];
                                                    $arGoods[$i]['GoodPersentNDS'] = $value['GoodPersentNDS'.$c];
                                                }

                                            }
                                            $arGoods = convArrayToUTF($arGoods);
                                            $PackDescr = [];
                                            for($i=0;$i<=5;$i++){
                                                $c = (string)$i;
                                                if($c === '0')$c='';
                                                if($value['PackDescriptionName'.$c]){
                                                    $PackDescr[$i]['name'] = $value['PackDescriptionName'.$c];
                                                    $PackDescr[$i]['place'] = $value['PackDescriptionPlaces'.$c];
                                                    $PackDescr[$i]['weight'] = $value['PackDescriptionWeight'.$c];
                                                    $PackDescr[$i]['size'] = [
                                                        $value['PackDescriptionLength'.$c],
                                                        $value['PackDescriptionHeight'.$c],
                                                        $value['PackDescriptionWidth'.$c]
                                                    ];
                                                    $l = (int)$value['PackDescriptionLength'.$c];
                                                    $h = (int)$value['PackDescriptionHeight'.$c];
                                                    $w = (int) $value['PackDescriptionWidth'.$c];
                                                    $PackDescr[$i]['gabweight'] = (($l*$h*$w)/$arResult['CURRENT_CLIENT_COEFFICIENT_VW']);
                                                }
                                            }
                                            /* получить стоимость для Технопарка Сколково */
                                            $sum_dev = 0;
                                            if($arResult['CURRENT_CLIENT'] == ID_SKOLKOVO){
                                                $total_gabweight = 0;
                                                $host_api = '';
                                                if($arResult['CURRENT_CLIENT'] == ID_SKOLKOVO){
                                                    $username =  iconv('windows-1251','utf-8',"DMSUser");
                                                    $password =  iconv('windows-1251','utf-8',"1597534682");
                                                    $host_api = "http://92.42.209.242/sd_msk/hs/Delivery/Account/7717739535/7701902970/GetTarif?ves=$resecho_weight&vesv=$total_gabweight&idcity1=$city_sender&idcity2=$city_recipient&deliverytype=c";
                                                    AddToLogs("SKOLKOVO", ['host_api' => $host_api]);
                                                }

                                                if ($host_api){
                                                    $url = iconv('windows-1251','utf-8',$host_api);

                                                    if ($ch = curl_init()){
                                                        curl_setopt($ch, CURLOPT_URL, $url);
                                                        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                                                        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
                                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                        $output = curl_exec($ch);
                                                        curl_close($ch);
                                                        if(preg_match('/^[0-9]+(\.([0-9]){2})?$/', json_decode($output))){
                                                            $sum_dev = json_decode($output);
                                                            $sum_dev =  (float)iconv('utf-8','windows-1251',$sum_dev);
                                                        }else{
                                                            $sum_dev = 0;
                                                        }
                                                    }else{
                                                        $sum_dev = 0;
                                                    }
                                                }

                                            }
                                            $PackDescr = convArrayToUTF($PackDescr);
                                            $number_nakl = $id_in['number'].'-'.$key;
                                            $resecho = [
                                                544 => $id_in['max_id'],  /* порядковый номер */
                                                545 => $arResult['CURRENT_CLIENT'],  /* id клиента  */
                                                546 => $value['ShipperFIO'],
                                                547 => $value['ShipperPhone'],
                                                548 => $value['ShipperCompany'],
                                                549 => $city_sender,
                                                550 => $value['ShipperZip'],
                                                551 => ['VALUE' => ['TYPE' => 'text', 'TEXT' => $value['ShipperAddress']]],
                                                552 => $value['ConsigneeFIO'],
                                                553 => $value['ConsigneePhone'],
                                                554 => $value['ConsigneeCompany'],
                                                555 => $city_recipient,
                                                556 => $value['ConsigneeZip'],
                                                557 => $TYPE_DELIVERY,
                                                558 => $TypePack,
                                                559 => $WhoDelivery,
                                                560 => $date_call,
                                                561 => $value['TimeDelivery'],
                                                562 => $TypePyas,
                                                563 => $value['TypePyasDescription'],
                                                564 => $Payment,
                                                565 => ($value['Cost'])?(float)str_replace(',', '.', $value['Cost']):'',
                                                733 => ($value['CodCost'])?(float)str_replace(',', '.', $value['CodCost']):'',
                                                566 => ($value['DeclaredCost'])?(float)str_replace(',', '.', $value['DeclaredCost']):'',
                                                567 => $resecho_places,
                                                568 => $resecho_weight,
                                                569 => '',
                                                570 => ($SpecDelivery)?['VALUE' => ['TYPE' => 'text', 'TEXT' => $SpecDelivery]]:'',
                                                571 => ($value['ConsigneeAddress'])?['VALUE' => ['TYPE' => 'text', 'TEXT' =>
                                                    $value['ConsigneeAddress']]]:'',
                                                572 => 257,
                                                639 => $arResult['BRANCH_AGENT_BY'],
                                                640 => $arResult['CLIENT_CONTRACT'],
                                                641 => $arResult['CURRENT_BRANCH'],
                                                679	=> 1,
                                                724 => ($arGoods)?json_encode($arGoods):'',
                                                737 => false,
                                                682 => ($PackDescr)?json_encode($PackDescr):'',
                                                979 => $sum_dev
                                            ];
                                            //dump($resecho);
                                            //exit;
                                            $el = new CIBlockElement;
                                            $arLoadProductArray = Array(
                                                "MODIFIED_BY" => $USER->GetID(),
                                                "IBLOCK_SECTION_ID" => false,
                                                "IBLOCK_ID" => 83,
                                                "PROPERTY_VALUES" => $resecho,
                                                "NAME" => $number_nakl,
                                                "ACTIVE" => "Y"
                                            );
                                            if ($z_nakl_id = $el->Add($arLoadProductArray))
                                            {
                                                $arResult["MESSAGE"][] = "Загрузка прошла успешно";
                                            }
                                            else
                                            {
                                                $arResult['ERRORS'][] = "Ошибка загрузки";
                                            }

                                        }
                                    }


                                }else{
                                    $arResult["ERRORS"][] = 'Несоответствие полей файлу-образцу';
                                }

                            }else{
                                $arResult["ERRORS"][] = 'Неверный тип файла';
                            }
                        }else{
                            $arResult["ERRORS"][] = 'Пустое имя файла';
                        }
                    }else{
                        $arResult["ERRORS"][] = 'Ошибка загрузки';
                    }

                }
            }
        }
        if ($arResult['ADMIN_AGENT'])
        {
            $arResult['TITLE'] = GetMessage("TITLE_MODE_UPLOAD_ADMIN", array('#NAME#' => $arResult['CURRENT_CLIENT_INFO']['NAME']));
            $APPLICATION->SetTitle(GetMessage("TITLE_MODE_UPLOAD_ADMIN", array('#NAME#' => $arResult['CURRENT_CLIENT_INFO']['NAME'])));
        }
        else
        {
            $arResult['TITLE'] = GetMessage("TITLE_UPLOAD_ADD");
            $APPLICATION->SetTitle(GetMessage("TITLE_UPLOAD_ADD"));
        }
        if (count($arResult["ERRORS"]) > 0)
        {
            foreach ($arResult["ERRORS"] as $index => $value)
            {
                $arLogs['ERRORS_'.$index] = $value;
            }
        }
        if (count($arLogs) > 0)
        {
            AddToLogs('InvoicesUpload',$arLogs);
        }
    }
    /*  -0.1 if end */
    if ($arResult['MODE'] == 'delone'){
        $id_invoice = (int)$_GET['n'];
        $name_invoice = strip_tags(htmlspecialchars($_GET['name']));
        $el = new CIBlockElement;
        $loc = $el->GetByID($id_invoice);
        if($ar_res = $loc->GetNext())
            $name_test =  $ar_res['NAME'];

        if($name_invoice == $name_test ){
            $res = $el->Update($id_invoice, array("ACTIVE"=>"N"));
            if($res){

                $arResult['MESSAGE'][] = "Накладная  $name_invoice успешно удалена";
                AddToLogs('InvoicesDelete', $arResult);
              /*  $event = new CEvent;
                $event->SendImmediate("NEWPARTNER_LK", "S5", [
                    'COMPANY'=>$arResult['CURRENT_CLIENT_ADDON']['NAME'],
                    'NUMBER'=>$name_invoice
                ], "N", 291);*/
                $arParamsJson = array(
                    'ID' => $id_invoice
                );
                $result = $client->SetPickupDelete($arParamsJson);
            }
        }

        LocalRedirect($arParams['LINK']);
    }

    if ($_GET['call_courier'] == "Y"){
        $arResult['REQUEST'] = [];
        foreach($_POST as $name=>$value){
            if($name === "rand" || $name === "key_session") continue;
            if(preg_match('/^callcourierdate_[0-9]+/', $name)){
                if(!empty($value)){
                    $arResult['REQUEST']['callcourierdate'] = $value;
                    if(empty($arResult['REQUEST']['id'])){
                        $arResult['REQUEST']['id'] = (int)preg_replace('/^callcourierdate_/','', $name);
                    }
                }
            }
            elseif(preg_match('/^callcourtime_from_[0-9]+/', $name)){
                if(!empty($value))$arResult['REQUEST']['callcourtime_from'] = $value;
            }
            elseif(preg_match('/^callcourtime_to_[0-9]+/', $name)){
                if(!empty($value))$arResult['REQUEST']['callcourtime_to'] = $value;
            }
            elseif(preg_match('/^callcourcomment_[0-9]+/', $name)){
                if(!empty($value))$arResult['REQUEST']['callcourcomment'] = $value;
            }
        }


        $z_id = (int)$arResult['REQUEST']['id'];
        echo " <script>
                   let el = $('#myCallCurier_$z_id');
                   el.modal('hide');
           </script>";

        $arResult['REQUEST']['name'] = $_POST['name_'.$arResult['REQUEST']['id']];

        if(empty($arResult['REQUEST']['callcourierdate'])){
            $arResult['MODE'] = 'list';

            echo "<div class='alert alert-danger' role='alert'>
       <a  href='/'>Отсутствует дата вызова курьера! Перейдите в список накладных и повторите 
       действие.</a> 
       </div>";
            exit;

        }else{
            $date_call = (int)strtotime($arResult['REQUEST']['callcourierdate']);
            $date_time = (int)time();
            if(($date_call-$date_time)<0){
                echo "<div class='alert alert-danger' role='alert'>
       <a  href='/'>Некорректная дата вызова курьера! Перейдите в список накладных и повторите действие.</a> 
       </div>";
                exit;
            }
        }

        if( !empty($arResult['REQUEST']['callcourtime_from'])){
            $date_take_from = substr( $arResult['REQUEST']['callcourierdate'],6,4).'-'.
                substr( $arResult['REQUEST']['callcourierdate'],3,2).'-'.
                substr( $arResult['REQUEST']['callcourierdate'],0,2).' '.$arResult['REQUEST']['callcourtime_from'].':00';

        }
        if( !empty($arResult['REQUEST']['callcourtime_to'])){
            $date_take_to = substr( $arResult['REQUEST']['callcourierdate'],6,4).'-'.
                substr( $arResult['REQUEST']['callcourierdate'],3,2).'-'.
                substr( $arResult['REQUEST']['callcourierdate'],0,2).' '.$arResult['REQUEST']['callcourtime_to'].':00';
        }


        $number_nakl = $arResult['REQUEST']['name'];

        $result = GetInfoArr(false, $z_id, 83 );

        /* записать вызов в базу */
        $id_in_cur = GetMaxIDIN(87, 7);
        $arHistory = array(array('date' => date('d.m.Y H:i:s'), 'status' => 315,
            'status_descr' => 'Оформлена', 'comment' => ''));
        $arHistoryUTF = convArrayToUTF($arHistory);
        $el = new CIBlockElement;
        $arLoadProductArray = [
            "MODIFIED_BY" => $USER->GetID(),
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID" => 87,
            "PROPERTY_VALUES" => [
                611 => $id_in_cur,
                612 => $arResult['CURRENT_CLIENT'],
                664 => $arResult['CURRENT_BRANCH'],
                613 => [
                    $arResult['REQUEST']['callcourierdate'].' '.$arResult['REQUEST']['callcourtime_from'].':00',
                    $arResult['REQUEST']['callcourierdate'].' '.$arResult['REQUEST']['callcourtime_to'].':00'
                ],
                614 => (int) $result['PROPERTIES']['CITY_SENDER']['VALUE'],
                615 => NewQuotes($result['PROPERTIES']['ADRESS_SENDER']['VALUE']['TEXT']),
                616 => NewQuotes($result['PROPERTIES']['NAME_SENDER']['VALUE']),
                617 =>NewQuotes($result['PROPERTIES']['PHONE_SENDER']['VALUE']),
                618 =>  NewQuotes($result['PROPERTIES']['WEIGHT']['VALUE']),
                619 => 0,
                620 => NewQuotes($arResult['REQUEST']['callcourcomment']).' Накладная №'.$number_nakl,
                712 => implode(', ', [$arResult['EMAIL_CALLCOURIER'], $arResult['ADD_AGENT_EMAIL']]),
                726 => 315,
                727 => json_encode($arHistoryUTF),
                771 => $number_nakl,
                862 => (int)$result['PROPERTIES']['TRANSPORT_TYPE']['VALUE'],
            ],
            "NAME" => 'Вызов курьера №'.$id_in_cur,
            "ACTIVE" => "Y"
        ];

        $zv_id = $el->Add($arLoadProductArray);

        /* сформировать инструкции для вызова */
        $newInstructions = NewQuotes($_POST['INSTRUCTIONS']);
        //' '.NewQuotes($_POST['INSTRUCTIONS']).'  '.'ДОСТАВИТЬ ДО ДАТЫ: '.$date_to.'. ';
        if (strlen($newInstructions)) {
            $newInstructions .= '. ';
        }

        $newInstructions .= '  ВЫЗОВ КУРЬЕРА: ' . $arResult['REQUEST']['callcourcomment'] .
            ' с ' . $arResult['REQUEST']['callcourtime_from'] . ' до ' . $arResult['REQUEST']['callcourtime_to '] . '.';
        if ($date_to) {
            $newInstructions .= '  ДОСТАВИТЬ ДО ДАТЫ: ' . $arResult['REQUEST']['callcourierdate'] . '.';
        }

        if (!empty($arResult['REQUEST']['callcourcomment'])) {
            $newInstructions .= ' КОММЕНТАРИЙ КУРЬЕРУ: ' . NewQuotes($arResult['REQUEST']['callcourcomment']);
        }


        if ($result['PROPERTIES']['IN_DATE_DELIVERY']['VALUE'] != '') {
            $newInstructions .= ' Доставить в дату: ' . $result['PROPERTIES']['IN_DATE_DELIVERY']['VALUE'];
        }
        if ($result['PROPERTIES']['IN_TIME_DELIVERY']['VALUE'] != '') {
            $newInstructions .= ' до часа ' . $result['PROPERTIES']['IN_TIME_DELIVERY']['VALUE'];
        }

        CIBlockElement::SetPropertyValuesEx($z_id, 83,
            [570 => ['VALUE' => ['TYPE' => 'text', 'TEXT' => $newInstructions]]]);

        //}
        /* 1c */
        $delivery_condition = 'ПоАдресу';
        switch ((int)$result['PROPERTIES']['WHO_DELIVERY']['VALUE_ENUM_ID'])
        {
            case 248:
                $delivery_condition = 'ПоАдресу';
                break;
            case 249:
                $delivery_condition = 'ДоВостребования';
                break;
            case 250:
                $delivery_condition = 'ЛичноВРуки';
                break;
        }
        $delivery_payer = 'Отправитель';
        switch ((int)$result['PROPERTIES']['TYPE_PAYS']['VALUE_ENUM_ID'])
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
        $payment_type = 'Наличные';
        switch ((int)$result['PROPERTIES']['PAYMENT']['VALUE_ENUM_ID'])
        {
            case 255:
                $payment_type = 'Наличные';
                break;
            case 256:
                $payment_type = 'Безналичные';
                break;
        }
        $delivery_type = 'Стандарт';
        switch ((int)$result['PROPERTIES']['TYPE_DELIVERY']['VALUE_ENUM_ID'])
        {
            case 345:
                $delivery_type1 = 'Экспресс2';
                break;
            case 346:
                $delivery_type1 = 'Экспресс4';
                break;
            case 338:
                $delivery_type1 = 'Экспресс8';
                break;
            case 243:
                $delivery_type = 'Экспресс';
                break;
            case 244:
                $delivery_type = 'Стандарт';
                break;
            case 245:
                $delivery_type = 'Эконом';
                break;
            case 308:
                $delivery_type = 'СкладСклад';
                break;
        }

        $arJs = [
            'IDWEB' => $z_id,
            'INN' => $arResult['CURRENT_CLIENT_INN'],
            'DATE' => date('Y-m-d'),
            'COMPANY_SENDER' => ($arResult['USER_IN_BRANCH']) ? $arResult['AGENT']['NAME'].', филиал '.$arResult['BRANCH_INFO']['NAME'] : $arResult['AGENT']['NAME'],
            'NAME_SENDER' => NewQuotes($result['PROPERTIES']['NAME_SENDER']['VALUE']),
            'PHONE_SENDER' => NewQuotes($result['PROPERTIES']['PHONE_SENDER']['VALUE']),
            'ADRESS_SENDER' => NewQuotes($result['PROPERTIES']['ADRESS_SENDER']['VALUE']['TEXT']),
            'INDEX_SENDER' => (int) $result['PROPERTIES']['INDEX_SENDER']['VALUE'],
            'ID_CITY_SENDER' => (int) $result['PROPERTIES']['CITY_SENDER']['VALUE'],
            'DELIVERY_TYPE' => $delivery_type,
            'PAYMENT_TYPE' => $payment_type,
            'DELIVERY_PAYER' => $delivery_payer,
            'DELIVERY_CONDITION' => $delivery_condition,
            'DATE_TAKE_FROM' => $date_take_from,
            'DATE_TAKE_TO' => $date_take_to,
            'INSTRUCTIONS' => deleteTabs($arResult['REQUEST']['callcourcomment']).' Накладная №'.$number_nakl,
            "TRANSPORT_TYPE" => (int)$result['PROPERTIES']['TRANSPORT_TYPE']['VALUE'],
        ];

        $arJs = convArrayToUTF($arJs);
       // $client = soap_inc();
        $resultCC = $client->SetCallingTheCourier(['ListOfDocs' => json_encode($arJs)]);
        $mResult = $resultCC->return;
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

        $arHistory[] = ['date' => date('d.m.Y H:i:s'), 'status' => $state_id, 'status_descr' => $state_descr,
            'comment' => $arRes[0]['comment']];
        $arHistoryUTF = convArrayToUTF($arHistory);
        CIBlockElement::SetPropertyValuesEx($z_id, 87, ["STATE"=>$state_id,
            "STATE_HISTORY"=>json_encode($arHistoryUTF)]);
        $arLogTitle = ['Title' => 'Вызов курьера из накладной'];
        $arLogResult = ['Response' => $mResult, 'status' => $arRes[0]['status'], 'comment' => $arRes[0]['comment']];
        $arLog = array_merge($arLogTitle,$arJs,$arLogResult);
        AddToLogs('callingCourier',$arLogResult);



        $delivery_type_seq  = "С";
        switch ((int)$result['PROPERTIES']['TYPE_DELIVERY']['VALUE_ENUM_ID'])
        {
            case 345:
                $delivery_type_seq = 'Э';
                break;
            case 346:
                $delivery_type_seq = 'Э';
                break;
            case 338:
                $delivery_type_seq = 'Э';
                break;
            case 243:
                $delivery_type_seq = 'Э';
                break;
            case 244:
                $delivery_type_seq = 'С';
                break;
            case 245:
                $delivery_type_seq = 'М';
                break;
            case 308:
                $delivery_type_seq = 'Д';
                break;
        }
        $delivery_payer_seq = 'О';
        switch ((int)$result['PROPERTIES']['TYPE_PAYS']['VALUE_ENUM_ID'])
        {
            case 251:
                $delivery_payer_seq = 'О';
                break;
            case 252:
                $delivery_payer_seq = 'П';
                break;
            case 253:
                $delivery_payer_seq = 'Д';
                break;
        }
        $payment_type_seq = 'Н';
        switch ((int)$result['PROPERTIES']['PAYMENT']['VALUE_ENUM_ID'])
        {
            case 255:
                $payment_type_seq = 'Н';
                break;
            case 256:
                $payment_type_seq = 'Б';
                break;
        }
        $delivery_condition_seq = 'А';
        switch ((int)$result['PROPERTIES']['WHO_DELIVERY']['VALUE_ENUM_ID'])
        {
            case 248:
                $delivery_condition_seq = 'А';
                break;
            case 249:
                $delivery_condition_seq = 'Д';
                break;
            case 250:
                $delivery_condition_seq = 'Л';
                break;
        }

        $date_create = date('Y-m-d h:i:s', strtotime($result['DATE_CREATE']));
        $arDeliverySequence = [
            "ID"            => $z_id,
            "DATE_CREATE"   => $date_create,
            "INN"           => $arResult['CURRENT_CLIENT_INN'],
            "NAME_SENDER"   =>  NewQuotes($result['PROPERTIES']['NAME_SENDER']['VALUE']),
            "PHONE_SENDER"  => NewQuotes($result['PROPERTIES']['PHONE_SENDER']['VALUE']),
            "COMPANY_SENDER"=> NewQuotes($result['PROPERTIES']['COMPANY_SENDER']['VALUE']),
            "CITY_SENDER"   => (int)$result['PROPERTIES']['CITY_SENDER']['VALUE'],
            "INDEX_SENDER"  =>  (int) $result['PROPERTIES']['INDEX_SENDER']['VALUE'],
            "ADDRESS_SENDER" => NewQuotes($result['PROPERTIES']['ADRESS_SENDER']['VALUE']['TEXT']),
            "ADDRESS_RECIPIENT"  => NewQuotes($result['PROPERTIES']['ADRESS_RECIPIENT']['VALUE']['TEXT']),
            "NAME_RECIPIENT" => NewQuotes($result['PROPERTIES']['NAME_RECIPIENT']['VALUE']),
            "PHONE_RECIPIENT"   =>  NewQuotes($result['PROPERTIES']['PHONE_RECIPIENT']['VALUE']),
            "COMPANY_RECIPIENT" =>  NewQuotes($result['PROPERTIES']['COMPANY_RECIPIENT']['VALUE']),
            "CITY_RECIPIENT"    =>   (int)$result['PROPERTIES']['CITY_RECIPIENT']['VALUE'],
            "CITY_RECIPIENT_NON" => NewQuotes($result['PROPERTIES']['CITY_RECIPIENT']['NAME']),
            "INDEX_RECIPIENT"   =>  (int) $result['PROPERTIES']['INDEX_RECIPIENT']['VALUE'],
            'DATE_TAKE_FROM'    => $date_take_from,
            'DATE_TAKE_TO'      => $date_take_to,
            "TYPE" => (int) $result['PROPERTIES']['TYPE_PACK']['VALUE_ENUM_ID'],
            "DELIVERY_TYPE" => $delivery_type_seq,
            "DELIVERY_PAYER" =>$delivery_payer_seq,
            "PAYMENT_TYPE" =>$payment_type_seq,
            "DELIVERY_CONDITION" =>$delivery_condition_seq,
            "PAYMENT_AMOUNT" =>"0",
            "INSTRUCTIONS" =>  NewQuotes($result['PROPERTIES']['INSTRUCTIONS']['VALUE']['TEXT']),
            "PLACES" => NewQuotes($result['PROPERTIES']['PLACES']['VALUE']),
            "WEIGHT" => NewQuotes($result['PROPERTIES']['WEIGHT']['VALUE']),
            "SIZE_1" => 0,
            "SIZE_2" => 0,
            "SIZE_3" => 0,
            "FILES" =>"",
            "DocNumber" => $number_nakl,
            "TRANSPORT_TYPE" => (int)$result['PROPERTIES']['TRANSPORT_TYPE']['VALUE'],
        ];

        $arDeliverySequence = convArrayToUTF($arDeliverySequence);
        $arParamsJson = [
            'ListOfDocs' => "[".json_encode($arDeliverySequence)."]"
        ];
       // $client = soap_inc();
        set_time_limit(0);
        $i = 1;
        while ($i <= 12) {
            $resultSD = $client->SetDocsListClient($arParamsJson);
            $mResult = $resultSD->return;
            $obj = json_decode($mResult, true);
            if (isset($obj['Doc_1']["ID"]) && $obj['Doc_1']["ID"] > 0) {
                $obj['Doc_1']['DATE'] = date("d.m.Y H:i:s");
                break;
            } else {
                sleep(30);
            }
            $i++;
        }
        if (!empty($obj['Doc_1']["ID"]) && $obj['Doc_1']["ID"] > 0) {
            $arRes = arFromUtfToWin($obj);
            CIBlockElement::SetPropertyValuesEx($z_id, 83,
                [
                    977=>'Y'
                ]);

        }else{
            $arEventFieldsERR['MESS_ERR_1C'] = 'Вызов курьера из ЛК был произведен, но из-за ошибки соединения с 1С в очередь не попал!';
        }
        /* отправить письмо менеджеру */
        /* получить файл накладной для отправки */
        $sendFilePath = $_SERVER["DOCUMENT_ROOT"] . "/" . COption::GetOptionString("main", "upload_dir") .
            "/pdf/" . $number_nakl . ".pdf";
        $fileId = CFile::SaveFile(
            [
                "name" => $number_nakl.".pdf",
                "tmp_name" => $sendFilePath,
                "old_file" => "0",
                "del" => "N",
                "MODULE_ID" => "",
                "description" => "",
            ],
            'sendfile',
            false,
            false
        );
        /* массив для письма */
        $payment_type1 = 'Наличные';
        switch ((int)$result['PROPERTIES']['PAYMENT']['VALUE_ENUM_ID'])
        {
            case 255:
                $payment_type1 = 'Наличные';
                break;
            case 256:
                $payment_type1 = 'Безналичные';
                break;
        }
        $delivery_payer1 = 'Отправитель';
        switch ((int)$result['PROPERTIES']['TYPE_PAYS']['VALUE_ENUM_ID'])
        {
            case 251:
                $delivery_payer1 = 'Отправитель';
                break;
            case 252:
                $delivery_payer1 = 'Получатель';
                break;
            case 253:
                $delivery_payer1 = 'Другой';
                break;
        }

        $arEventFields = [
            "COMPANY_F" => ($arResult['USER_IN_BRANCH']) ? $arResult['AGENT']['NAME'].', филиал '.$arResult['BRANCH_INFO']['NAME'] : $arResult['AGENT']['NAME'],
            "NUMBER" => $id_in_cur,
            "COMPANY" => $arResult['AGENT']['NAME'],
            "BRANCH" => ($arResult['USER_IN_BRANCH']) ? 'Филиал: <strong>'.$arResult['BRANCH_INFO']['NAME'].'</strong><br />' : '',
            "DATE_TIME" => $_POST['callcourierdate'].' с '.$_POST['callcourtime_from'].' до '.$_POST['callcourtime_to'],
            "CITY" =>  NewQuotes($result['PROPERTIES']['CITY_SENDER']['NAME']),
            "ADRESS" =>  NewQuotes($result['PROPERTIES']['ADRESS_SENDER']['NAME']),
            "CONTACT" => NewQuotes($result['PROPERTIES']['NAME_SENDER']['VALUE']),
            "PHONE" => NewQuotes($result['PROPERTIES']['PHONE_SENDER']['VALUE']),
            "WEIGHT" => NewQuotes($result['PROPERTIES']['WEIGHT']['VALUE']),
           /* "SIZE_1" => NewQuotes($result['PROPERTIES']['DIMENSIONS']['VALUE'][0]),
            "SIZE_2" => NewQuotes($result['PROPERTIES']['DIMENSIONS']['VALUE'][1]),
            "SIZE_3" => NewQuotes($result['PROPERTIES']['DIMENSIONS']['VALUE'][2]),
            "SIZE_4" => NewQuotes($result['PROPERTIES']['DIMENSIONS']['VALUE'][3]),
            "SIZE_5" =>  NewQuotes($result['PROPERTIES']['DIMENSIONS']['VALUE'][4]),
           */
            "COMMENT" => deleteTabs($arResult['REQUEST']['callcourcomment']).' Накладная №'.$number_nakl,
            'AGENT_EMAIL' => $arResult['ADD_AGENT_EMAIL'],
            'UK_EMAIL' => $arResult['EMAIL_CALLCOURIER'],
            'TYPE_PAYS' => $payment_type1,
            'SPEC_INSTR' => $newInstructions,
            'PAYER' => $delivery_payer1,
            "POST" => "client@newpartner.ru, logist@newpartner.ru",
        ];
        if(!empty($result['PROPERTIES']['DIMENSIONS']['VALUE'])){
            $arEventFields['SIZE'] = "Габариты:" . "<br />";
            foreach($result['PROPERTIES']['DIMENSIONS']['VALUE'] as $key=>$value){
                if(!empty($value)){

                    $arEventFields['SIZE'] .= $value . "<br />";

                }
            }
        }


        $arEventFields['MESS_ERR_1C'] = $arEventFieldsERR['MESS_ERR_1C'];
        $arEventFields['FOR_CACHE']='';
        if($payment_type1 === 'Наличные'){
            $arEventFields['FOR_CACHE'] = "ЗА НАЛИЧНЫЕ";
        }

        /* отправка письма */

        $event = new CEvent;
        if( (int)$result['PROPERTIES']['TRANSPORT_TYPE']['VALUE'] == 1){
            $event->SendImmediate("NEWPARTNER_LK", "S5", $arEventFields, "N", 290);
        }
        if($arResult['CURRENT_CLIENT'] == ID_SUKHOI) $arEventFields['CITY_RECIPIENT'] = 'Город получателя: ' .
            '<strong>' . $CITY_RECIPIENT . '</strong>';
        $event->SendImmediate("NEWPARTNER_LK", "S5", $arEventFields, "N", 220, [$fileId]);
        CFile::Delete($fileId);
        //unlink($sendFilePath);

        /* добавить статус в бд */

        $arHistory[] =['date' => date('d.m.Y H:i:s'), 'status' => 316, 'status_descr' => 'Отправлена на почту',
            'comment' => ''];
        $arHistoryUTF = convArrayToUTF($arHistory);
        CIBlockElement::SetPropertyValuesEx($zv_id, 87, ["STATE"=>316,"STATE_HISTORY"=>json_encode($arHistoryUTF)]);
        header("Location: /");

    }
}

$this->IncludeComponentTemplate($arResult['MODE']);
