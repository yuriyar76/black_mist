<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
ini_set("soap.wsdl_cache_enabled", "0" );
ini_set("default_socket_timeout", "300");
$start = microtime(true);
$arResult['times'] = array();
//$this->IncludeComponentTemplate('close');

include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

$modes = array(
    'list',
    'list_xls',
    'close'
);

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

$arResult['OPEN'] = true;
$arResult['ADMIN_AGENT'] = false;
$arResult['SITUATION'] = array();
$arResult['SITUATION_TEXT'] = '';

$arResult["USER_ID"] =  $USER->GetID();
$rsUser = CUser::GetByID($arResult["USER_ID"]);
$arUser = $rsUser->Fetch();
$agent_id = intval($arUser["UF_COMPANY_RU_POST"]);
if ($agent_id > 0)
{
    $arResult['AGENT'] = GetCompany($agent_id);
    if (in_array($arResult["AGENT"]["PROPERTY_TYPE_ENUM_ID"], array(51, 53)))
    {
        if ($arResult["AGENT"]["PROPERTY_TYPE_ENUM_ID"] == 51)
        {
            $arResult["ADMIN_AGENT"] = true;
            $arResult["UK"] = $arResult["AGENT"]["ID"];
        }
        else
        {
            $arResult["UK"] = $arResult["AGENT"]["PROPERTY_UK_VALUE"];
        }
        if (intval($arResult["UK"]) > 0)
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
                CURLOPT_NOBODY => true,
                CURLOPT_TIMEOUT => 10));
                $header = explode("\n", curl_exec($curl));
                curl_close($curl);
                if (strlen(trim($header[0])))
                {
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
                    $arResult['OPEN'] = false;
                    $arResult["ERRORS"][] = GetMessage("ERR_TECH");
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
else
{
    $arResult['OPEN'] = false;
    $arResult["ERRORS"][] = GetMessage("ERR_OPEN");
}

$arResult['times'][] = array('name' => 'Первоначальные проверки', 'val' => microtime(true) - $start);

if (!$arResult['OPEN'])
{
    $arResult['MODE'] = 'close';
}
	
if ($arResult['MODE'] == 'list')
{
    $arResult['YEARS'] = array();
    for ($i = 2014; $i<=date('Y'); $i++)
    {
        $arResult['YEARS'][$i] = $i;
    }
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
            else
            {
                $_SESSION['CURRENT_AGENT'] = 0;
                $arResult['CURRENT_AGENT'] = 0;
                $_SESSION['CURRENT_INN'] = false;
                $arResult['CURRENT_INN'] = false;
            }
        }
    }

    $arResult['NOT_DELIVERED'] = array();
    $arResult['EVENTS'] = array();
    $arResult['times'][] = array('name' => 'Первоначальные настройки функции', 'val' => microtime(true) - $start); 

    if ($arResult['CURRENT_INN'])
    {
        $res_4 = CIBlockElement::GetList(
            array('NAME' => 'ASC'), 
            array('IBLOCK_ID' => 19, 'ACTIVE' => 'Y'), 
            false, 
            false, 
            array('ID', 'NAME')
        );
        while ($ob_4 = $res_4->GetNextElement())
        {
            $a = $ob_4->GetFields();
            $arResult['SITUATION'][$a['ID']] = $a['NAME'];
            $arResult['SITUATION_TEXT'] .= '<option value="'.$a['ID'].'">'.$a['NAME'].'</option>';
        }
        
        $arResult['times'][] = array('name' => 'Создание массива исключительных ситуаций', 'val' => microtime(true) - $start); 

        $arParamsJson = array(
            "INN" => $arResult['CURRENT_INN']
        );

        $result = $client->GetDocsListAgentNotPods($arParamsJson);

        $arResult['times'][] = array('name' => 'Получение данных из 1с', 'val' => microtime(true) - $start);

        $mResult = $result->return;
        $obj = json_decode($mResult, true);
        $arRes = arFromUtfToWin($obj);
        
        if ($obj)
        {
            $arRes = arFromUtfToWin($obj);
            if ($arRes['TotalDocs'] > 0)
            {
                $arNotDeliveredUTF = array();
                foreach ($arRes['Docs'] as $nakl)
                {
                    $arResult['NOT_DELIVERED'][] = $nakl['NumDoc'];
                    $arNotDeliveredUTF[] = iconv('windows-1251', 'utf-8', $nakl['NumDoc']);
                    foreach ($nakl['Events'] as $event)
                    {
                        $arResult['EVENTS'][$nakl['NumDoc']][] = array(
                            'date' => $event['DateEvent'].'&nbsp;'.$event['TimeEvent'],
                            'event' => $event['Event'],
                            'desc' => $event['InfoEvent']
                        );
                    }
                }
                $arResult['LIST_JSON'] = json_encode($arNotDeliveredUTF);
            }
            /*
            foreach ($obj as $m)
            {
                $numbers = array();
                $events = array();
                foreach ($m['DeliveryNotes'] as $nakl)
                {
                    $numbernakl = iconv("utf-8", "windows-1251", $nakl["DeliveryNote"]);
                    $numbers[$numbernakl] = "N";
                    $events[$numbernakl] = array();
                    foreach ($nakl['DeliveryEvents'] as $ev)
                    {
                        $event = iconv("utf-8", "windows-1251", $ev["Event"]);
                        $events[$numbernakl][] = array(
                            "date" => iconv("utf-8", "windows-1251", $ev["EventDate"]),
                            "event" => $event,
                            "desc" => iconv("utf-8", "windows-1251", $ev["EventExt"])
                        );
                        if ($event == "Доставлено")
                        {
                            $numbers[$numbernakl] = "Y";
                        }
                    }
                    if ($numbers[$numbernakl] == "N")
                    {
                        $arResult['NOT_DELIVERED'][] = $numbernakl;
                        $arResult['EVENTS'][$numbernakl] = $events[$numbernakl];
                    }

                }
            }
            $arNotDeliveredUTF = array();
            foreach ($arResult['NOT_DELIVERED'] as $n)
            {
                $arNotDeliveredUTF[] = iconv('windows-1251', 'utf-8', $n);
            }
            $arResult['LIST_JSON'] = json_encode($arNotDeliveredUTF);
            */
        }
        /*
        echo '<pre>';
        print_r($arResult['EVENTS']);
        echo '</pre>';
        */
        $arResult['times'][] = array('name' => 'Разбор данных из 1с 1', 'val' => microtime(true) - $start);
    }
    else
    {
        if ($arResult['ADMIN_AGENT'])
        {
            if (intval($arResult['CURRENT_AGENT']) > 0)
            {
                $arResult["ERRORS"][] = 'У агента <a href="/agents/index.php?mode=agent&id='.intval($arResult['CURRENT_AGENT']).'" target="_blank">'.$arResult['LIST_OF_AGENTS'][intval($arResult['CURRENT_AGENT'])].'</a> отсутствует ID обмена';
            }
            else
            {
                $arResult["WARNINGS"][] = 'Не выбран агент';
            }
        }
        else
        {
            $arResult["WARNINGS"][] = 'Ошибка доступа, обратитесь в тех. поддержку';
        }
    }
    $arResult['TITLE'] = GetMessage("TITLE_MODE_LIST");
    $APPLICATION->SetTitle(GetMessage("TITLE_MODE_LIST"));
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
        $arJ = array('A','B','C','D','E');
        $row = array(
            iconv('windows-1251', 'utf-8', 'Номер накладной'),
            iconv('windows-1251', 'utf-8', 'Дата доставки'),
            iconv('windows-1251', 'utf-8', 'Время доставки'),
            iconv('windows-1251', 'utf-8', 'ФИО'),
            iconv('windows-1251', 'utf-8', 'Должность')
        );
        foreach  ($row as $n => $v)
        {
            $num_sel = $arJ[$n].$i;
            $aSheet->setCellValue($num_sel,$v);
            $n++;    
        }
        $i++;
        foreach  ($arData as $v)
        {
            $num_sel = $arJ[0].$i;
            $aSheet->setCellValue($num_sel,$v);
            $i++;
        }
        $i--;
        foreach ($arJ as $cc)
        {
            $aSheet->getColumnDimension($cc)->setWidth(17);
        }
        $aSheet->getStyle('A1:E'.$i)->getAlignment()->setWrapText(true);
        $aSheet->getStyle('A1:E'.$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
        $aSheet->getStyle('A1:E'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $aSheet->getStyle('A1:E1')->applyFromArray($head_style);
        include_once $_SERVER['DOCUMENT_ROOT'].'bitrix/_black_mist/PhpExcel/Classes/PHPExcel/Writer/Excel5.php';
        $objWriter = new PHPExcel_Writer_Excel5($pExcel);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.date('Накладные Новый Партнер без ПОДов d.m.Y').'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
    }
}

$this->IncludeComponentTemplate($arResult['MODE']);