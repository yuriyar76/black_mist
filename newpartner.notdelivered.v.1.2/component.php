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
//TODO Вынос текстов в lang-файл
//TODO Добавление логов на загрузку файлов
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
            $currentport = intval(GetSettingValue(761, false, $arResult["UK"]));
            $currentlink = GetSettingValue(704, false, $arResult["UK"]);
            $login1c = GetSettingValue(705, false, $arResult["UK"]);
            $pass1c = GetSettingValue(706, false, $arResult["UK"]);
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
        
        if (isset($_POST['uploadfile']))
        {
            //TODO [x]Обработка повторной отправки формы
            if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
			{
				$_POST = array();
                $_FILES = array();
				$arResult["ERRORS"][] = 'Повторная отправка формы';
			}
			else
			{
                $_SESSION[$_POST["key_session"]] = $_POST["rand"];
                if (strlen($_FILES['file']['tmp_name']))
                {
                    $typesfile = array();
                    //TODO [x]Проверка на соответствие типа
					if (($_FILES['file']['type']  == "application/vnd.ms-excel") ||
                        ($_FILES['file']['type']  == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") ||
                        ($_FILES['file']['type'] == 'application/octet-stream'))
                    {
                        //TODO [x]Сохранение файла на сервере
                        $uploadfolder = $_SERVER['DOCUMENT_ROOT'].'/upload/upload_pods/'.$arResult['CURRENT_AGENT'];
                        if (!file_exists($uploadfolder))
                        {
                            mkdir($uploadfolder);
                        }
                        $uploadfolder .= '/'.date('Y');
                        if (!file_exists($uploadfolder))
                        {
                            mkdir($uploadfolder);
                        }
                        $uploadfolder .= '/'.date('m');
                        if (!file_exists($uploadfolder))
                        {
                            mkdir($uploadfolder);
                        }
                        $uploadfolder .= '/'.date('d');
                        if (!file_exists($uploadfolder))
                        {
                            mkdir($uploadfolder);
                        }
                        /*
                        $uploadfolder .= '/'.date('H_i_s');
                        if (!file_exists($uploadfolder))
                        {
                            mkdir($uploadfolder);
                        }
                        */
                        $uploadfolder .= '/';
                        //TODO Транслитерация русских имен файлов
                        $filepath = $uploadfolder.date('H_i_s').'_'.basename(str_replace(' ','_',$_FILES['file']['name']));
                        if (copy($_FILES['file']['tmp_name'], $filepath))
                        {
                            if (is_file($filepath))
                            {
                                include_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/PhpExcel/Classes/PHPExcel.php';
                                $arUpload = array();
                                $inputFileType = PHPExcel_IOFactory::identify($filepath);
                                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                                $objPHPExcel = $objReader->load($filepath);
                                $arUpload = $objPHPExcel->getActiveSheet()->toArray();
                                $arResUpload = arFromUtfToWin($arUpload);
                                if (intval($_POST['title_yes']) == 1)
                                {
                                    unset($arResUpload[0]);
                                }
                                $arUtfToSend = array();
                                foreach ($arResUpload as $arNakl)
                                {
                                    if ((strlen($arNakl[0])) && (strlen($arNakl[1])))
                                    {
                                        if (substr_count($arNakl[1], '-') == 2)
                                        {
                                            $ardate = explode('-',trim($arNakl[1]));
                                            $datetimeevent = $ardate[1].'.'.$ardate[0].'.';
                                            if (strlen($ardate[2]) == 2)
                                            {
                                                $datetimeevent.= '20'.$ardate[2];
                                            }
                                            else
                                            {
                                                $datetimeevent.= $ardate[2];
                                            }
                                        }
                                        elseif (substr_count($arNakl[1], '/') == 2)
                                        {
                                            $ardate = explode('/',trim($arNakl[1]));
                                            $datetimeevent = $ardate[0].'.'.$ardate[1].'.';
                                            if (strlen($ardate[2]) == 2)
                                            {
                                                $datetimeevent.= '20'.$ardate[2];
                                            }
                                            else
                                            {
                                                $datetimeevent.= $ardate[2];
                                            }
                                        }
                                        elseif ((substr_count($arNakl[1], '.') == 2) && ((strlen(trim($arNakl[1])) == 8) || (strlen(trim($arNakl[1])) == 10)))
                                        {
                                            $ardate = explode('.',trim($arNakl[1]));
                                            $datetimeevent = $ardate[0].'.'.$ardate[1].'.';
                                            if (strlen($ardate[2]) == 2)
                                            {
                                                $datetimeevent.= '20'.$ardate[2];
                                            }
                                            else
                                            {
                                                $datetimeevent.= $ardate[2];
                                            }
                                        }
                                        else
                                        {
                                            $arResult['ERRORS'][] = 'Невозможно распознать формат даты накладной <strong>'.$arNakl[0].'</strong>';
                                            continue;
                                        }
                                        //Время
                                        if (strlen($arNakl[2]))
                                        {
                                            if ((substr_count($arNakl[2], 'AM') > 0) || (substr_count($arNakl[2], 'PM') > 0))
                                            {
                                                $datetimeevent .= ' '.date("H:i:s", strtotime($arNakl[2]));
                                            }
                                            else
                                            {
                                                if (strlen($arNakl[2]) == 4)
                                                {
                                                    $arNakl[2] = '0'.$arNakl[2];
                                                }
                                                $arNakl[2] = str_replace('-',':',$arNakl[2]);
                                                $datetimeevent .= ' '.str_pad($arNakl[2], 8, ':00');
                                            }
                                        }
                                        else
                                        {
                                            //$datetimeevent .= ' 00:00:00';
                                            $arResult['ERRORS'][] = 'Не указано время у накладной <strong>'.$arNakl[0].'</strong>';
                                            continue;
                                        }
                                        //Время
                                        $descr = $arNakl[3];
                                        if (strlen($arNakl[4]))
                                        {
                                            if (strlen($descr))
                                            {
                                                $descr .= ' '.$arNakl[4];
                                            }
                                            else
                                            {
                                                $descr = $arNakl[4];
                                            }
                                        }
                                        $arUtfToSend[] = array(
                                            'ID_EVENT' => 0,
                                            'NUMBER' => iconv('windows-1251','utf-8', trim($arNakl[0])),
                                            'DATE' => $datetimeevent,
                                            'EVENT' => iconv('windows-1251','utf-8', 'Доставлено'),
                                            'DESCRIPTION' => iconv('windows-1251','utf-8', trim($descr)),
                                            'INN' => $arResult['CURRENT_INN']
                                        );
                                    }
                                    else
                                    {
                                        if (strlen($arNakl[0]))
                                        {
                                            $arResult['ERRORS'][] = 'Отсутствует дата доставки накладной <strong>'.$arNakl[0].'</strong>';
                                        }
                                    }
                                }
                                if (count($arUtfToSend) > 0)
                                {
                                    $arParamsJson['ListOfDocs'] = json_encode($arUtfToSend);
                                    AddToLogs('podsfromfile',array('ListOfDocs' => $arParamsJson['ListOfDocs']));
                                    /*
                                    if ($arResult['ADMIN_AGENT'])
                                    {
                                        echo $arParamsJson['ListOfDocs'];   
                                    }
                                    */
                                    $result = $client->SetDD($arParamsJson);
                                    //echo $result;
                                    $mResult = $result->return;
                                    /*
                                    if ($arResult['ADMIN_AGENT'])
                                    {
                                        echo $mResult;
                                    }
                                    */
                                    $obj = json_decode($mResult, true);

                                    $arRes = arFromUtfToWin($obj);
                                    foreach ($arRes as $v)
                                    {
                                        if ($v['status'] == 'true')
                                        {
                                            $arResult["MESSAGE"][] = 'Данные по накладной <strong>'.$v['number'].'</strong> успешно добавлены.';
                                        }
                                        else
                                        {
                                             $arResult['ERRORS'][] = 'Ошибка передачи данных по накладной <strong>'.$v['number'].'</strong>: '.$v['comment'];
                                        }
                                    }
                                }
                                else
                                {
                                    $arResult['ERRORS'][] = 'Отсутствуют данные для передачи';
                                }
                                //echo $arParamsJson['ListOfDocs'];
                                //echo '<pre>';
                                //print_r($arResUpload);
                                //echo '</pre>';
                            }
                            else
                            {
                                //TODO [x]Вывод ошибки, если файла не существует
                                $arResult['ERRORS'][] = 'Загруженный файл не существует, пожалуйста, обратитесь в <a href="/support/">тех. поддержку</a>';
                            }
                        }
                        else
                        {
                            $arResult['ERRORS'][] = 'Произошла ошибка при загрузке файла, пожалуйста, обратитесь в <a href="/support/">тех. поддержку</a>';
                        }
                    }
                    else
                    {
						AddToLogs('UploadFilesXLSErrors', array('TYPE' => $_FILES['file']['type']));
                        $arResult['ERRORS'][] = 'Неверный тип файла ('.$_FILES['file']['type'] .')';
                    }
                }
                else
                {
                    //TODO [x]Обработка пустого файла
                    $arResult['ERRORS'][] = 'Пустой файл';
                }
            }
        }
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
        }
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
        include_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/PhpExcel/Classes/PHPExcel.php';
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
        include_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/PhpExcel/Classes/PHPExcel/Writer/Excel5.php';
        $objWriter = new PHPExcel_Writer_Excel5($pExcel);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.date('Накладные Новый Партнер без ПОДов d.m.Y').'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
    }
}

$this->IncludeComponentTemplate($arResult['MODE']);