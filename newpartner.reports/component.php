<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

ini_set("soap.wsdl_cache_enabled", "0" );
ini_set("default_socket_timeout", "300");

include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

$modes = array(
    'list',
    'close',
    'reconciliation_report',
    'report_delivery',
    'not_exposed_to_the_debt',
    'the_list_of_services_rendered',
    'pdf'
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
$arResult['ADMIN_AGENT'] = false;
$arResult['OPEN'] = false;
$arResult['LIST_OF_CONTRACTORS'] = false;
$rsUser = CUser::GetByID($USER->GetID());
$arUser = $rsUser->Fetch();
$agent_id = intval($arUser["UF_COMPANY_RU_POST"]);
if ($agent_id > 0)
{
    $arResult["AGENT"] = GetCompany($agent_id);
    if ($arResult["AGENT"]["PROPERTY_TYPE_ENUM_ID"] == 51)
    {
        $arResult['ADMIN_AGENT'] = true;
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
                $arResult['OPEN'] = true;
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
                if ($arResult['ADMIN_AGENT'])
                {
                    if ($arParams['TYPE'] == 242)
                    {
                        $arResult['LIST_OF_CONTRACTORS'] = AvailableClients(false, false, $agent_id);
                    }
                    elseif ($arParams['TYPE'] == 53)
                    {
                        $arResult['LIST_OF_CONTRACTORS'] = AvailableAgents(false, $agent_id);
                    }
                    
                    if ($_GET['ChangeContractor'] == 'Y')
                    {
                        if (isset($arResult['LIST_OF_CONTRACTORS'][$_GET['contractor']]))
                        {
                            $_SESSION['CURRENT_CONTRACTOR'] = $_GET['contractor'];

                            $db_props = CIBlockElement::GetProperty(40,$arResult['CURRENT_CONTRACTOR'], array("sort" => "asc"), Array("CODE"=>"INN"));
                            if($ar_props = $db_props->Fetch())
                            {
                                $arResult['CURRENT_INN'] = $ar_props["VALUE"];
                            }
                            $_SESSION['CURRENT_INN'] = $arResult['CURRENT_INN'];
                        }
                        elseif (intval($_GET['contractor']) == 0)
                        {
                            unset($_SESSION['CURRENT_CONTRACTOR']);
                            unset($_SESSION['CURRENT_INN']);
                        }
                    }
                    
                    
                    if (intval($_SESSION['CURRENT_CONTRACTOR']) > 0)
                    {
                        $arResult['CURRENT_CONTRACTOR'] = $_SESSION['CURRENT_CONTRACTOR'];
                        if (strlen($_SESSION['CURRENT_INN']))
                        {
                            $arResult['CURRENT_INN'] = $_SESSION['CURRENT_INN'];
                        }
                        else
                        {
                            $db_props = CIBlockElement::GetProperty(40,$arResult['CURRENT_CONTRACTOR'], array("sort" => "asc"), Array("CODE"=>"INN"));
                            if($ar_props = $db_props->Fetch())
                            {
                                $arResult['CURRENT_INN'] = $ar_props["VALUE"];
                            }
                        }
                    }
                    else
                    {
                        $arResult['CURRENT_CONTRACTOR'] = 0;
                        $arResult['CURRENT_INN'] = '';
                    }
                }
                else
                {
                    $arResult['CURRENT_CONTRACTOR'] = $arResult['AGENT']['ID'];
                    $arResult['CURRENT_INN'] = $arResult['AGENT']['PROPERTY_INN_VALUE'];
                }
            }
            else
            {
                $arResult['MODE'] = 'close';
            }
        }
        else
        {
            $arResult['MODE'] = 'close';
        }
    }
    else
    {
        $arResult['MODE'] = 'close';
    }
}
else
{
    $arResult['MODE'] = 'close';
}

if ($arResult['MODE'] == 'list')
{
    $arResult['TYPES_REPORTS'] = array(
        'reconciliation_report' => array(
            'name' => 'Акт сверки',
            'access' => array(53, 242)
        ),
        'report_delivery' => array(
            'name' => 'Отчет по услугам экспресс-доставки',
            'access' => array(242)
        ),
        'not_exposed_to_the_debt' => array(
            'name' => 'Cписок оказанных, но не выставленных услуг (не выставленная задолженность)',
            'access' => array(242)
        ),
        /*
        'the_list_of_services_rendered' => array(
            'name' => 'Расшифровка реализации (список оказанных услуг)',
            'access' => array(242)
        )
        */
    );
}

if ($arResult['MODE'] == 'reconciliation_report')
{
    if (($arResult['CURRENT_CONTRACTOR'] > 0) && (strlen($arResult['CURRENT_INN'])))
    {
        if ((strlen($_GET['start'])) && (strlen($_GET['end'])))
        {
            $date_start = substr($_GET['start'],6,4).'-'.substr($_GET['start'],3,2).'-'.substr($_GET['start'],0,2);
            $date_end = substr($_GET['end'],6,4).'-'.substr($_GET['end'],3,2).'-'.substr($_GET['end'],0,2);
            //TODO [x]Проверка на то, что дата end больше даты start
            $result = $client->GetActSverkaClient(
                array(
                    'INN' => iconv('windows-1251','utf-8',$arResult['CURRENT_INN']),
                    'BranchID' => '',
                    'BranchPrefix' => '',
                    'StartDate' => $date_start,
                    'EndDate' => $date_end
                )
            );
            $mResult = $result->return;
            $obj = json_decode($mResult, true);
            $arResult['REPORT'] = arFromUtfToWin($obj);
            if (strlen($arResult['REPORT']['Error']))
            {
                $arResult['OPEN'] = false;
                $arResult["ERRORS"][] = $arResult['REPORT']['Error'];
            }
        }
        else
        {
            $arResult['OPEN'] = false;
            $arResult["ERRORS"][] = 'Неверные даты, обратитесь в тех. поддежку.';
        }
    }
    else
    {
        $arResult['OPEN'] = false;
        $arResult["ERRORS"][] = 'Неверные данные контрагента, обратитесь в тех. поддежку.';
    }
}

if ($arResult['MODE'] == 'report_delivery')
{
    if ($arResult['CURRENT_CONTRACTOR'] > 0)
    {
        if ((strlen($_GET['start'])) && (strlen($_GET['end'])))
        {
            $date_start = substr($_GET['start'],6,4).'-'.substr($_GET['start'],3,2).'-'.substr($_GET['start'],0,2);
            $date_end = substr($_GET['end'],6,4).'-'.substr($_GET['end'],3,2).'-'.substr($_GET['end'],0,2);
            //TODO [x]Проверка на то, что дата end больше даты start
            $result = $client->GetDocsListClient(
                array(
                    'INN' => iconv('windows-1251','utf-8',$arResult['CURRENT_INN']),
                    'BranchID' => '',
                    'BranchPrefix' => '', 
                    'StartDate' => $date_start,
                    'EndDate' => $date_end,
                    'NumPage' => 0,
                    'DocsToPage' => 10000,
                    'Type' => 1
                )
            );
            $mResult = $result->return;
            $obj = json_decode($mResult, true);
            $arResult['REPORT'] = arFromUtfToWin($obj);
            $APPLICATION->SetTitle('Отчет по услугам экспресс-доставки для '.$arResult['LIST_OF_CONTRACTORS'][$arResult['CURRENT_CONTRACTOR']].' с '.$_GET['start'].' по '.$_GET['end']);
        }
        else
        {
            $arResult['OPEN'] = false;
            $arResult["ERRORS"][] = 'Неверные даты, обратитесь в тех. поддежку.';
        }
    }
    else
    {
        $arResult['OPEN'] = false;
        $arResult["ERRORS"][] = 'Неверные данные контрагента, обратитесь в тех. поддежку.';
    }
}

if ($arResult['MODE'] == 'not_exposed_to_the_debt')
{
    if ($arResult['CURRENT_CONTRACTOR'] > 0)
    {
        $result = $client->GetDocsListClient(
            array(
                'INN' => iconv('windows-1251','utf-8',$arResult['CURRENT_INN']),
                'BranchID' => '',
                'BranchPrefix' => '', 
                'StartDate' => date('Y-m-d'),
                'EndDate' => date('Y-m-d'),
                'NumPage' => 0,
                'DocsToPage' => 10000,
                'Type' => 2
            )
        );
        $mResult = $result->return;
        $obj = json_decode($mResult, true);
        $arResult['REPORT'] = arFromUtfToWin($obj);
        $APPLICATION->SetTitle('Отчет по оказанным, но не выставленным услугам (не выставленная задолженность) для '.$arResult['LIST_OF_CONTRACTORS'][$arResult['CURRENT_CONTRACTOR']].'
                на '.date('d.m.Y'));
    }
    else
    {
        $arResult['OPEN'] = false;
        $arResult["ERRORS"][] = 'Неверные данные контрагента, обратитесь в тех. поддежку.';
    }
}

if ($arResult['MODE'] == 'the_list_of_services_rendered')
{
    if ($arResult['CURRENT_CONTRACTOR'] > 0)
    {
        if ((strlen($_GET['DocumentDate'])) && (strlen($_GET['DocumentNumber'])))
        {
            $params = array(
                    'INN' => iconv('windows-1251','utf-8',$arResult['CURRENT_INN']),
                    'BranchID' => '',
                    'BranchPrefix' => '', 
                    'StartDate' => $_GET['DocumentDate'],
                    'EndDate' => $_GET['DocumentDate'],
                    'NumPage' => 0,
                    'DocsToPage' => 10000,
                    'Type' => 3,
                    'DocumentDate' => $_GET['DocumentDate'],
                    'DocumentNumber' => iconv('windows-1251','utf-8',$_GET['DocumentNumber']),
                );
            $result = $client->GetDocsListClient(
                $params
            );
            $mResult = $result->return;
            $obj = json_decode($mResult, true);
            $arResult['REPORT'] = arFromUtfToWin($obj);
            $arResult['DocumentNumber'] = $_GET['DocumentNumber'];
            $arResult['DocumentDate'] = substr($_GET['DocumentDate'],8,2).'.'.substr($_GET['DocumentDate'],5,2).'.'.substr($_GET['DocumentDate'],0,4);
            $APPLICATION->SetTitle('Расшифровка реализации №'.$arResult['DocumentNumber'].' от '.$arResult['DocumentDate'].' для '.$arResult['LIST_OF_CONTRACTORS'][$arResult['CURRENT_CONTRACTOR']]);
        }
        else
        {
            $arResult['OPEN'] = false;
            $arResult["ERRORS"][] = 'Неверные параметры запроса, обратитесь в тех. поддежку.';
        }
    }
    else
    {
        $arResult['OPEN'] = false;
        $arResult["ERRORS"][] = 'Неверные данные контрагента, обратитесь в тех. поддежку.';
    }

}

if ($arResult['MODE'] == 'pdf')
{
    if ($arResult['CURRENT_CONTRACTOR'] > 0)
    {
        if ($_GET['type'] == 'reconciliation_report')
        {
            if ((strlen($_GET['start'])) && (strlen($_GET['end'])))
            {
                $date_start = substr($_GET['start'],6,4).'-'.substr($_GET['start'],3,2).'-'.substr($_GET['start'],0,2);
                $date_end = substr($_GET['end'],6,4).'-'.substr($_GET['end'],3,2).'-'.substr($_GET['end'],0,2);
                $result = $client->GetActSverkaClient(
                    array(
                        'INN' => iconv('windows-1251','utf-8',$arResult['CURRENT_INN']),
                        'BranchID' => '',
                        'BranchPrefix' => '',
                        'StartDate' => $date_start,
                        'EndDate' => $date_end
                    )
                );
                $mResult = $result->return;
                $obj = json_decode($mResult, true);
                $arResult['REPORT'] = arFromUtfToWin($obj);
                if (strlen($arResult['REPORT']['Error']))
                {
                    LocalRedirect($arParams['LINK']);
                }
                else
                {
                    $pdf = new PDF_MC_Table();
                    $pdf->AddFont('ArialMT','','arialTM.php');
                    $pdf->SetFont('ArialMT','',20);
                    foreach ($arResult['REPORT']['Sverka'] as $org)
                    {
                        foreach ($org['Dogovors'] as $dogovor)
                        {
                            $pdf->AddPage('P');
                            $pdf->SetFillColor(255, 255, 255);
                            $pdf->SetLineWidth(0.05);
                            $pdf->SetFontSize(12);
                            $pdf->SetWidths(array(190));
                            $data = array(array('value'=> 'Акт сверки','align'=>'C'));
                            $pdf->Row($data, true, true);
                            $pdf->SetFontSize(9);
                            $data = array(array('value'=> 'взаимных расчетов за период с '.$arResult['REPORT']['StartOfPeriod'].' по '.$arResult['REPORT']['EndOfPeriod'],'align'=>'C'));
                            $pdf->Row($data, true, true);
                            $data = array(array('value'=> 'между '.$org['Name'],'align'=>'C'));
                            $pdf->Row($data, true, true);
                            $data = array(array('value'=> 'и '.$arResult['REPORT']['ClientName'],'align'=>'C'));
                            $pdf->Row($data, true, true);
                            $p1 = '';
                            $p1 .= strlen($dogovor['NumberDog']) ? 'по договору '.$dogovor['NumberDog'] : '';
                            $p1 .= strlen($dogovor['DateDog']) ? ' от '.$dogovor['DateDog'] : '';
                            if (strlen($p1))
                            {
                                $data = array(array('value'=> $p1,'align'=>'C'));
                                $pdf->Row($data, true, true);
                            }
                            $pdf->Row(array(''),false,true);
                            $data = array(array('value'=> 'Мы, нижеподписавшиеся, '.$org['Name'].', с одной стороны,','align'=>'C'));
                            $pdf->Row($data, true, true);
                            $data = array(array('value'=> 'и '.$arResult['REPORT']['ClientName'].', с другой стороны,','align'=>'C'));
                            $pdf->Row($data, true, true);
                            $data = array(array('value'=> 'составили настоящий акт сверки в том, что состояние взаимных расчетов по данным учета следующее:','align'=>'C'));
                            $pdf->Row($data, true, true);
                            $pdf->Row(array(''),false,true);
                            $pdf->SetFontSize(8);
                            $data = array('По данным '.$org['Name'].', руб.');
                            $pdf->Row($data,false);
                            $pdf->SetWidths(array(30,100,30,30));
                            $data = array(
                                'Дата',
                                'Документ',
                                'Дебет',
                                'Кредит'
                            );
                            $pdf->Row($data,false);
                            $pdf->SetWidths(array(130,30,30));
                            $data = array(
                                'Сальдо начальное',
                                $dogovor['SaldoDebetStart'],
                                $dogovor['SaldoCreditStart']
                            );
                            $pdf->Row($data);
                            $pdf->SetWidths(array(30,100,30,30));
                            foreach ($dogovor['Documents'] as $doc)
                            {
                                $data = array(
                                    $doc['Date'],
                                    $doc['Document'],
                                    $doc['Debet'],
                                    $doc['Credit']
                                );
                                $pdf->Row($data);
                            }
                            $pdf->SetWidths(array(130,30,30));
                            $data = array(
                                'Обороты за период',
                                $dogovor['ItogDebet'],
                                $dogovor['ItogCredit']
                            );
                            $pdf->Row($data);
                            $data = array(
                                'Сальдо конечное',
                                $dogovor['SaldoDebetEnd'],
                                $dogovor['SaldoCreditEnd']
                            );
                            $pdf->Row($data);
                            $pdf->SetWidths(array(190));
                            $pdf->Row(array(''),false,true);
                            $SaldoDebetEnd=floatval(preg_replace("/[^x\d|*\.]/","",str_replace(",",'.',$dogovor['SaldoDebetEnd'])));
                            $SaldoCreditEnd=floatval(preg_replace("/[^x\d|*\.]/","",str_replace(",",'.',$dogovor['SaldoCreditEnd'])));
                            if ($SaldoDebetEnd > 0)
                            {
                                $data = array('По состоянию на '.$arResult['REPORT']['EndOfPeriod'].' задолженность в пользу '.$org['Name'].' '.$dogovor['SaldoDebetEnd'].' руб.');
                                $pdf->Row($data,false, true);
                            }
                            if ($SaldoCreditEnd > 0)
                            {
                                $data = array('По состоянию на '.$arResult['REPORT']['EndOfPeriod'].' задолженность в пользу '.$arResult['REPORT']['ClientName'].' '.$dogovor['SaldoCreditEnd'].' руб.');
                                $pdf->Row($data,false, true);
                            }
                        }
                    }
                    $pdf->Output('report.pdf','D'); 
                }
            }
            else
            {
                LocalRedirect($arParams['LINK']);
            }
        }
        elseif ($_GET['type'] == 'report_delivery')
        {
            if ((strlen($_GET['start'])) && (strlen($_GET['end'])))
            {
                $result = $client->GetDocsListClient(
                    array(
                        'INN' => iconv('windows-1251','utf-8',$arResult['CURRENT_INN']),
                        'BranchID' => '',
                        'BranchPrefix' => '', 
                        'StartDate' => substr($_GET['start'],6,4).'-'.substr($_GET['start'],3,2).'-'.substr($_GET['start'],0,2),
                        'EndDate' => substr($_GET['end'],6,4).'-'.substr($_GET['end'],3,2).'-'.substr($_GET['end'],0,2),
                        'NumPage' => 0,
                        'DocsToPage' => 10000,
                        'Type' => 1
                    )
                );
                $mResult = $result->return;
                $obj = json_decode($mResult, true);
                $arResult['REPORT'] = arFromUtfToWin($obj);
                $pdf = new PDF_MC_Table();
                $pdf->AddFont('ArialMT','','arialTM.php');
                $pdf->SetFont('ArialMT','',20);
                $pdf->AddPage('L');
                $pdf->SetFillColor(255, 255, 255);
                $pdf->SetLineWidth(0.05);
                $pdf->SetFontSize(12); 
                $pdf->SetWidths(array(278));
                $data = array(array('value'=> 'Отчет по услугам экспресс-доставки для '.$arResult['LIST_OF_CONTRACTORS'][$arResult['CURRENT_CONTRACTOR']],'align'=>'C'));
                $pdf->Row($data, true, true);
                $pdf->SetFontSize(8);
                $data = array(array('value'=> 'c '.$_GET['start'].' по '.$_GET['end'],'align'=>'C'));
                $pdf->Row($data, true, true);
                $pdf->SetWidths(array(22,22,18,31,53,22,22,28,24,36));
                $data = array(
                    'Номер накладной',
                    'Дата приема заказа',
                    'Вес, кг',
                    'Наименование получателя',
                    'Адрес получателя',
                    'Дата вручения',
                    'Время вручения',
                    'Фамилия получателя',
                    'Стоимость, руб.',
                    'Реализация, № Акта, дата'
                );
                $pdf->Row($data, true);
                $docs = array_reverse($arResult['REPORT']['Docs']);
                $itog = 0;
                foreach ($docs as $doc)
                {
                    $data = array(
                        $doc['NumDoc'],
                        substr($doc['DateDoc'],8,2).'.'.substr($doc['DateDoc'],5,2).'.'.substr($doc['DateDoc'],0,4),
                        $doc['Delivery_Weight'],
                        $doc['CompanyRecipient'],
                        $doc['AdressRecipient'],
                        $doc['Date_Delivered'],
                        $doc['Time_Delivered'],
                        $doc['Signature_Delivered'],
                        $doc['Tarif'],
                        $doc['Delivery_Act']
                    );
                    $pdf->Row($data, false);
                    $itog = $itog + $doc['Tarif'];
                }
                $pdf->SetWidths(array(218,24,36));
                $data = array(array('value' => 'Итого:', 'align' => 'R'),$itog,'');
                $pdf->Row($data, false);
                $pdf->Output('report.pdf','D'); 
            }
            else
            {
                LocalRedirect($arParams['LINK']);
            }
        }
        elseif ($_GET['type'] == 'not_exposed_to_the_debt')
        {
            $result = $client->GetDocsListClient(
                array(
                    'INN' => iconv('windows-1251','utf-8',$arResult['CURRENT_INN']),
                    'BranchID' => '',
                    'BranchPrefix' => '', 
                    'StartDate' => date('Y-m-d'),
                    'EndDate' => date('Y-m-d'),
                    'NumPage' => 0,
                    'DocsToPage' => 10000,
                    'Type' => 2
                )
            );
            $mResult = $result->return;
            $obj = json_decode($mResult, true);
            $arResult['REPORT'] = arFromUtfToWin($obj);
            $pdf = new PDF_MC_Table();
            $pdf->AddFont('ArialMT','','arialTM.php');
            $pdf->SetFont('ArialMT','',20);
            $pdf->AddPage('L');
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetLineWidth(0.05);
            $pdf->SetFontSize(12); 
            $pdf->SetWidths(array(278));
            $data = array(array('value'=> 'Отчет по оказанным, но не выставленным услугам','align'=>'C'));
            $pdf->Row($data, true, true);
            $pdf->SetFontSize(8);
            $data = array(array('value'=> '(не выставленная задолженность) для '.$arResult['LIST_OF_CONTRACTORS'][$arResult['CURRENT_CONTRACTOR']],'align'=>'C'));
            $pdf->Row($data, true, true);
            $data = array(array('value'=> 'на '.date('d.m.Y'),'align'=>'C'));
            $pdf->Row($data, true, true);
            $pdf->SetWidths(array(22,22,18,31,53,22,22,28,24,36));
            $data = array(
                'Номер накладной',
                'Дата приема заказа',
                'Вес, кг',
                'Наименование получателя',
                'Адрес получателя',
                'Дата вручения',
                'Время вручения',
                'Фамилия получателя',
                'Стоимость, руб.',
                'Реализация, № Акта, дата'
            );
            $pdf->Row($data, true);
            $docs = array_reverse($arResult['REPORT']['Docs']);
            $itog = 0;
            foreach ($docs as $doc)
            {
                $data = array(
                    $doc['NumDoc'],
                    substr($doc['DateDoc'],8,2).'.'.substr($doc['DateDoc'],5,2).'.'.substr($doc['DateDoc'],0,4),
                    $doc['Delivery_Weight'],
                    $doc['CompanyRecipient'],
                    $doc['AdressRecipient'],
                    $doc['Date_Delivered'],
                    $doc['Time_Delivered'],
                    $doc['Signature_Delivered'],
                    $doc['Tarif'],
                    $doc['Delivery_Act']
                );
                $pdf->Row($data, false);
                $itog = $itog + $doc['Tarif'];
            }
            $pdf->SetWidths(array(218,24,36));
            $data = array(array('value' => 'Итого:', 'align' => 'R'),$itog,'');
            $pdf->Row($data, false);
            $pdf->Output('report.pdf','D');
        }
        elseif ($_GET['type'] == 'the_list_of_services_rendered')
        {
            if ((strlen($_GET['DocumentDate'])) && (strlen($_GET['DocumentNumber'])))
            {
                $result = $client->GetDocsListClient(
                    array(
                        'INN' => iconv('windows-1251','utf-8',$arResult['CURRENT_INN']),
                        'BranchID' => '',
                        'BranchPrefix' => '', 
                        'StartDate' => $_GET['DocumentDate'],
                        'EndDate' => $_GET['DocumentDate'],
                        'NumPage' => 0,
                        'DocsToPage' => 10000,
                        'Type' => 3,
                        'DocumentDate' => $_GET['DocumentDate'],
                        'DocumentNumber' => iconv('windows-1251','utf-8',$_GET['DocumentNumber']),
                    )
                );
                $mResult = $result->return;
                $obj = json_decode($mResult, true);
                $arResult['REPORT'] = arFromUtfToWin($obj);
                $arResult['DocumentNumber'] = $_GET['DocumentNumber'];
                $arResult['DocumentDate'] = substr($_GET['DocumentDate'],8,2).'.'.substr($_GET['DocumentDate'],5,2).'.'.substr($_GET['DocumentDate'],0,4);
                $pdf = new PDF_MC_Table();
                $pdf->AddFont('ArialMT','','arialTM.php');
                $pdf->SetFont('ArialMT','',20);
                $pdf->AddPage('L');
                $pdf->SetFillColor(255, 255, 255);
                $pdf->SetLineWidth(0.05);
                $pdf->SetFontSize(12); 
                $pdf->SetWidths(array(278));
                $data = array(array('value'=> 'Расшифровка реализации №'.$arResult['DocumentNumber'].' от '.$arResult['DocumentDate'],'align'=>'C'));
                $pdf->Row($data, true, true);
                $data = array(array('value'=> 'для '.$arResult['LIST_OF_CONTRACTORS'][$arResult['CURRENT_CONTRACTOR']],'align'=>'C'));
                $pdf->Row($data, true, true);
                $pdf->SetFontSize(8);
                $pdf->SetWidths(array(22,22,18,31,53,22,22,28,24,36));
                $data = array(
                    'Номер накладной',
                    'Дата приема заказа',
                    'Вес, кг',
                    'Наименование получателя',
                    'Адрес получателя',
                    'Дата вручения',
                    'Время вручения',
                    'Фамилия получателя',
                    'Стоимость, руб.',
                    'Реализация, № Акта, дата'
                );
                $pdf->Row($data, true);
                $docs = array_reverse($arResult['REPORT']['Docs']);
                $itog = 0;
                foreach ($docs as $doc)
                {
                    $data = array(
                        $doc['NumDoc'],
                        substr($doc['DateDoc'],8,2).'.'.substr($doc['DateDoc'],5,2).'.'.substr($doc['DateDoc'],0,4),
                        $doc['Delivery_Weight'],
                        $doc['CompanyRecipient'],
                        $doc['AdressRecipient'],
                        $doc['Date_Delivered'],
                        $doc['Time_Delivered'],
                        $doc['Signature_Delivered'],
                        $doc['Tarif'],
                        $doc['Delivery_Act']
                    );
                    $pdf->Row($data, false);
                    $itog = $itog + $doc['Tarif'];
                }
                $pdf->SetWidths(array(218,24,36));
                $data = array(array('value' => 'Итого:', 'align' => 'R'),$itog,'');
                $pdf->Row($data, false);
                $pdf->Output('report.pdf','D');
            }
            else
            {
                LocalRedirect($arParams['LINK']);
            } 
        }
        else
        {
            LocalRedirect($arParams['LINK']);
        }
    }
    else
    {
        LocalRedirect($arParams['LINK']);
    }
}
$this->IncludeComponentTemplate($arResult['MODE']);