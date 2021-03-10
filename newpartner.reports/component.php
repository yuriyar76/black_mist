<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

ini_set("soap.wsdl_cache_enabled", "0" );
ini_set("default_socket_timeout", "300");

include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

$modes = [
    'list',
    'close',
    'reconciliation_report',
    'report_delivery',
    'not_exposed_to_the_debt',
    'the_list_of_services_rendered',
    'pdf',
    'xls'
];

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
$agent_id = (int)$arUser["UF_COMPANY_RU_POST"];
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
    if ((int)$arResult["UK"] > 0)
    {
        $currentip = GetSettingValue(683, false, $arResult["UK"]);
        $currentport = (int)GetSettingValue(761, false, $arResult["UK"]);
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
            curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_TIMEOUT => 5]);
            $header = explode("\n", curl_exec($curl));
            curl_close($curl);
            if (strlen(trim($header[0])))
            {
                $arResult['OPEN'] = true;
                if ($currentport > 0) {
                    $client = new SoapClient($url, ['login' => $login1c, 'password' => $pass1c, 'proxy_host' => $currentip, 'proxy_port' => $currentport, 'exceptions' => false]);
                }
                else {
                    $client = new SoapClient($url, ['login' => $login1c, 'password' => $pass1c,'exceptions' => false]);
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
                    
                    if (trim($_GET['ChangeContractor']) === 'Y')
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
                        elseif ((int)$_GET['contractor'] == 0)
                        {
                            unset($_SESSION['CURRENT_CONTRACTOR']);
                            unset($_SESSION['CURRENT_INN']);
                        }
                    }
                    
                    
                    if ((int)$_SESSION['CURRENT_CONTRACTOR'] > 0)
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

if (trim($arResult['MODE']) === 'list')
{
    $arResult['TYPES_REPORTS'] = [
        'reconciliation_report' => [
            'name' => 'Акт сверки',
            'access' => [53, 242]
        ],
        'report_delivery' => [
            'name' => 'Отчет по услугам экспресс-доставки',
            'access' => [242]
        ],
        'not_exposed_to_the_debt' => [
            'name' => 'Cписок оказанных, но не выставленных услуг (не выставленная задолженность)',
            'access' => [242]
        ],
        /*
        'the_list_of_services_rendered' => array(
            'name' => 'Расшифровка реализации (список оказанных услуг)',
            'access' => array(242)
        )
        */
    ];
}

if (trim($arResult['MODE']) === 'reconciliation_report')
{
    if (($arResult['CURRENT_CONTRACTOR'] > 0) && (strlen($arResult['CURRENT_INN'])))
    {
        if ((strlen($_GET['start'])) && (strlen($_GET['end'])))
        {
            $date_start = substr($_GET['start'],6,4).'-'.substr($_GET['start'],3,2).'-'.substr($_GET['start'],0,2);
            $date_end = substr($_GET['end'],6,4).'-'.substr($_GET['end'],3,2).'-'.substr($_GET['end'],0,2);
            //TODO [x]Проверка на то, что дата end больше даты start
            $result = $client->GetActSverkaClient(
                [
                    'INN' => iconv('windows-1251','utf-8',$arResult['CURRENT_INN']),
                    'BranchID' => '',
                    'BranchPrefix' => '',
                    'StartDate' => $date_start,
                    'EndDate' => $date_end
                ]
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

if (trim($arResult['MODE']) === 'report_delivery')
{
    if ($arResult['CURRENT_CONTRACTOR'] > 0)
    {
        if ((strlen($_GET['start'])) && (strlen($_GET['end'])))
        {
            $date_start = substr($_GET['start'],6,4).'-'.substr($_GET['start'],3,2).'-'.substr($_GET['start'],0,2);
            $date_end = substr($_GET['end'],6,4).'-'.substr($_GET['end'],3,2).'-'.substr($_GET['end'],0,2);
            //TODO [x]Проверка на то, что дата end больше даты start
            $result = $client->GetDocsListClient(
                [
                    'INN' => iconv('windows-1251','utf-8',$arResult['CURRENT_INN']),
                    'BranchID' => '',
                    'BranchPrefix' => '', 
                    'StartDate' => $date_start,
                    'EndDate' => $date_end,
                    'NumPage' => 0,
                    'DocsToPage' => 10000,
                    'Type' => 1
                ]
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

if (trim($arResult['MODE']) === 'not_exposed_to_the_debt')
{
    if ($arResult['CURRENT_CONTRACTOR'] > 0)
    {
        $result = $client->GetDocsListClient(
            [
                'INN' => iconv('windows-1251','utf-8',$arResult['CURRENT_INN']),
                'BranchID' => '',
                'BranchPrefix' => '', 
                'StartDate' => date('Y-m-d'),
                'EndDate' => date('Y-m-d'),
                'NumPage' => 0,
                'DocsToPage' => 10000,
                'Type' => 2
            ]
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

if (trim($arResult['MODE']) === 'the_list_of_services_rendered')
{
    if ($arResult['CURRENT_CONTRACTOR'] > 0)
    {
        if ((strlen($_GET['DocumentDate'])) && (strlen($_GET['DocumentNumber'])))
        {
            $params = [
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
            ];
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

if (trim($arResult['MODE']) === 'pdf')
{
    if ($arResult['CURRENT_CONTRACTOR'] > 0)
    {
        if (trim($_GET['type']) === 'reconciliation_report')
        {
            if ((strlen($_GET['start'])) && (strlen($_GET['end'])))
            {
                $date_start = substr($_GET['start'],6,4).'-'.substr($_GET['start'],3,2).'-'.substr($_GET['start'],0,2);
                $date_end = substr($_GET['end'],6,4).'-'.substr($_GET['end'],3,2).'-'.substr($_GET['end'],0,2);
                $result = $client->GetActSverkaClient(
                    [
                        'INN' => iconv('windows-1251','utf-8',$arResult['CURRENT_INN']),
                        'BranchID' => '',
                        'BranchPrefix' => '',
                        'StartDate' => $date_start,
                        'EndDate' => $date_end
                    ]
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
                            $pdf->SetWidths([190]);
                            $data = [['value'=> 'Акт сверки','align'=>'C']];
                            $pdf->Row($data, true, true);
                            $pdf->SetFontSize(9);
                            $data = [['value'=> 'взаимных расчетов за период с '.$arResult['REPORT']['StartOfPeriod'].' по '.$arResult['REPORT']['EndOfPeriod'],'align'=>'C']];
                            $pdf->Row($data, true, true);
                            $data = [['value'=> 'между '.$org['Name'],'align'=>'C']];
                            $pdf->Row($data, true, true);
                            $data = [['value'=> 'и '.$arResult['REPORT']['ClientName'],'align'=>'C']];
                            $pdf->Row($data, true, true);
                            $p1 = '';
                            $p1 .= strlen($dogovor['NumberDog']) ? 'по договору '.$dogovor['NumberDog'] : '';
                            $p1 .= strlen($dogovor['DateDog']) ? ' от '.$dogovor['DateDog'] : '';
                            if (strlen($p1))
                            {
                                $data = [['value'=> $p1,'align'=>'C']];
                                $pdf->Row($data, true, true);
                            }
                            $pdf->Row([''],false,true);
                            $data = [['value'=> 'Мы, нижеподписавшиеся, '.$org['Name'].', с одной стороны,','align'=>'C']];
                            $pdf->Row($data, true, true);
                            $data = [['value'=> 'и '.$arResult['REPORT']['ClientName'].', с другой стороны,','align'=>'C']];
                            $pdf->Row($data, true, true);
                            $data = [['value'=> 'составили настоящий акт сверки в том, что состояние взаимных расчетов по данным учета следующее:','align'=>'C']];
                            $pdf->Row($data, true, true);
                            $pdf->Row([''],false,true);
                            $pdf->SetFontSize(8);
                            $data = ['По данным '.$org['Name'].', руб.'];
                            $pdf->Row($data,false);
                            $pdf->SetWidths([30,100,30,30]);
                            $data = [
                                'Дата',
                                'Документ',
                                'Дебет',
                                'Кредит'
                            ];
                            $pdf->Row($data,false);
                            $pdf->SetWidths([130,30,30]);
                            $data = [
                                'Сальдо начальное',
                                $dogovor['SaldoDebetStart'],
                                $dogovor['SaldoCreditStart']
                            ];
                            $pdf->Row($data);
                            $pdf->SetWidths([30,100,30,30]);
                            foreach ($dogovor['Documents'] as $doc)
                            {
                                $data = [
                                    $doc['Date'],
                                    $doc['Document'],
                                    $doc['Debet'],
                                    $doc['Credit']
                                ];
                                $pdf->Row($data);
                            }
                            $pdf->SetWidths([130,30,30]);
                            $data = [
                                'Обороты за период',
                                $dogovor['ItogDebet'],
                                $dogovor['ItogCredit']
                            ];
                            $pdf->Row($data);
                            $data = [
                                'Сальдо конечное',
                                $dogovor['SaldoDebetEnd'],
                                $dogovor['SaldoCreditEnd']
                            ];
                            $pdf->Row($data);
                            $pdf->SetWidths([190]);
                            $pdf->Row([''],false,true);
                            $SaldoDebetEnd= (float)preg_replace("/[^x\d|*\.]/", "", str_replace(",", '.', $dogovor['SaldoDebetEnd']));
                            $SaldoCreditEnd= (float)preg_replace("/[^x\d|*\.]/", "", str_replace(",", '.', $dogovor['SaldoCreditEnd']));
                            if ($SaldoDebetEnd > 0)
                            {
                                $data = ['По состоянию на '.$arResult['REPORT']['EndOfPeriod'].' задолженность в пользу '.$org['Name'].' '.$dogovor['SaldoDebetEnd'].' руб.'];
                                $pdf->Row($data,false, true);
                            }
                            if ($SaldoCreditEnd > 0)
                            {
                                $data = ['По состоянию на '.$arResult['REPORT']['EndOfPeriod'].' задолженность в пользу '.$arResult['REPORT']['ClientName'].' '.$dogovor['SaldoCreditEnd'].' руб.'];
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
        elseif (trim($_GET['type']) === 'report_delivery')
        {
            if ((strlen($_GET['start'])) && (strlen($_GET['end'])))
            {
                $result = $client->GetDocsListClient(
                    [
                        'INN' => iconv('windows-1251','utf-8',$arResult['CURRENT_INN']),
                        'BranchID' => '',
                        'BranchPrefix' => '', 
                        'StartDate' => substr($_GET['start'],6,4).'-'.substr($_GET['start'],3,2).'-'.substr($_GET['start'],0,2),
                        'EndDate' => substr($_GET['end'],6,4).'-'.substr($_GET['end'],3,2).'-'.substr($_GET['end'],0,2),
                        'NumPage' => 0,
                        'DocsToPage' => 10000,
                        'Type' => 1
                    ]
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
                $pdf->SetWidths([278]);
                $data = [['value'=> 'Отчет по услугам экспресс-доставки для '.$arResult['LIST_OF_CONTRACTORS'][$arResult['CURRENT_CONTRACTOR']],'align'=>'C']];
                $pdf->Row($data, true, true);
                $pdf->SetFontSize(8);
                $data = [['value'=> 'c '.$_GET['start'].' по '.$_GET['end'],'align'=>'C']];
                $pdf->Row($data, true, true);
                $pdf->SetWidths([22,22,18,31,53,22,22,28,24,36]);
                $data = [
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
                ];
                $pdf->Row($data, true);
                $docs = array_reverse($arResult['REPORT']['Docs']);
                $itog = 0;
                foreach ($docs as $doc)
                {
                    $data = [
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
                    ];
                    $pdf->Row($data, false);
                    $itog = $itog + $doc['Tarif'];
                }
                $pdf->SetWidths([218,24,36]);
                $data = [['value' => 'Итого:', 'align' => 'R'],$itog,''];
                $pdf->Row($data, false);
                $pdf->Output('report.pdf','D'); 
            }
            else
            {
                LocalRedirect($arParams['LINK']);
            }
        }
        elseif (trim($_GET['type']) === 'not_exposed_to_the_debt')
        {
            $result = $client->GetDocsListClient(
                [
                    'INN' => iconv('windows-1251','utf-8',$arResult['CURRENT_INN']),
                    'BranchID' => '',
                    'BranchPrefix' => '', 
                    'StartDate' => date('Y-m-d'),
                    'EndDate' => date('Y-m-d'),
                    'NumPage' => 0,
                    'DocsToPage' => 10000,
                    'Type' => 2
                ]
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
            $pdf->SetWidths([278]);
            $data = [['value'=> 'Отчет по оказанным, но не выставленным услугам','align'=>'C']];
            $pdf->Row($data, true, true);
            $pdf->SetFontSize(8);
            $data = [['value'=> '(не выставленная задолженность) для '.$arResult['LIST_OF_CONTRACTORS'][$arResult['CURRENT_CONTRACTOR']],'align'=>'C']];
            $pdf->Row($data, true, true);
            $data = [['value'=> 'на '.date('d.m.Y'),'align'=>'C']];
            $pdf->Row($data, true, true);
            $pdf->SetWidths([22,22,18,31,53,22,22,28,24,36]);
            $data = [
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
            ];
            $pdf->Row($data, true);
            $docs = array_reverse($arResult['REPORT']['Docs']);
            $itog = 0;
            foreach ($docs as $doc)
            {
                $data = [
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
                ];
                $pdf->Row($data, false);
                $itog = $itog + $doc['Tarif'];
            }
            $pdf->SetWidths([218,24,36]);
            $data = [['value' => 'Итого:', 'align' => 'R'],$itog,''];
            $pdf->Row($data, false);
            $pdf->Output('report.pdf','D');
        }
        elseif (trim($_GET['type']) === 'the_list_of_services_rendered')
        {
            if ((strlen($_GET['DocumentDate'])) && (strlen($_GET['DocumentNumber'])))
            {
                $result = $client->GetDocsListClient(
                    [
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
                    ]
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
                $pdf->SetWidths([278]);
                $data = [['value'=> 'Расшифровка реализации №'.$arResult['DocumentNumber'].' от '.$arResult['DocumentDate'],'align'=>'C']];
                $pdf->Row($data, true, true);
                $data = [['value'=> 'для '.$arResult['LIST_OF_CONTRACTORS'][$arResult['CURRENT_CONTRACTOR']],'align'=>'C']];
                $pdf->Row($data, true, true);
                $pdf->SetFontSize(8);
                $pdf->SetWidths([22,22,18,31,53,22,22,28,24,36]);
                $data = [
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
                ];
                $pdf->Row($data, true);
                $docs = array_reverse($arResult['REPORT']['Docs']);
                $itog = 0;
                foreach ($docs as $doc)
                {
                    $data = [
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
                    ];
                    $pdf->Row($data, false);
                    $itog = $itog + $doc['Tarif'];
                }
                $pdf->SetWidths([218,24,36]);
                $data = [['value' => 'Итого:', 'align' => 'R'],$itog,''];
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

if (trim($arResult['MODE']) === 'xls')
{

    if ($arResult['CURRENT_CONTRACTOR'] > 0)
    {

        if (trim($_GET['type']) === 'the_list_of_services_rendered')
        {

            if ((strlen($_GET['DocumentDate'])) && (strlen($_GET['DocumentNumber']))) {
                $arResult['REPORT'] = [];
                $num_doc =  iconv('windows-1251', 'utf-8',$_GET['DocumentNumber']);
                $date_doc = iconv('windows-1251', 'utf-8',$_GET['DocumentDate']);
                $date_d = date('d-m-Y', strtotime($date_doc));
                $params = [
                    'INN' => iconv('windows-1251', 'utf-8', $arResult['CURRENT_INN']),
                    'BranchID' => '',
                    'BranchPrefix' => '',
                    'StartDate' => $date_doc,
                    'EndDate' => $date_doc,
                    'NumPage' => 0,
                    'DocsToPage' => 10000,
                    'Type' => 3,
                    'DocumentDate' => $date_doc,
                    'DocumentNumber' => $num_doc,
                ];
                $result = $client->GetDocsListClient(
                    $params
                );
                $mResult = $result->return;
                $obj = json_decode($mResult, true);
                $arResult['REPORT'] =$obj;

               /* dump($arResult['REPORT']);
                exit();*/

                $arData = $arResult['REPORT']['Docs'];
                include_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/PhpExcel/Classes/PHPExcel.php';
                $arARCHIVEutf = [
                    [
                        iconv('windows-1251', 'utf-8', 'Номер накладной'),
                        iconv('windows-1251', 'utf-8', 'Дата приема заказа'),
                        iconv('windows-1251', 'utf-8', 'Вес, кг'),
                        iconv('windows-1251', 'utf-8', 'Наименование получателя'),
                        iconv('windows-1251', 'utf-8', 'Адрес получателя'),
                        iconv('windows-1251', 'utf-8', 'Дата вручения'),
                        iconv('windows-1251', 'utf-8', 'Время вручения'),
                        iconv('windows-1251', 'utf-8', 'Фамилия получателя'),
                        iconv('windows-1251', 'utf-8', 'Стоимость, руб.'),
                        iconv('windows-1251', 'utf-8', 'Реализация, № Акта, дата'),

                    ]
                ];

                $k=1;
                $itog = 0;
                foreach ($arData as $doc){
                    $arARCHIVEutf[$k][] = $doc['NumDoc'];
                    $arARCHIVEutf[$k][] = substr($doc['DateDoc'],8,2).'.'.substr($doc['DateDoc'],5,2).'.'.substr($doc['DateDoc'],0,4);
                    $arARCHIVEutf[$k][] = $doc['Delivery_Weight'];
                    $arARCHIVEutf[$k][] = $doc['CompanyRecipient'] . ' ' . $doc['NameRecipient'];
                    $arARCHIVEutf[$k][] = $doc['AdressRecipient'];
                    $arARCHIVEutf[$k][] = $doc['Date_Delivered'];
                    $arARCHIVEutf[$k][] = $doc['Time_Delivered'];
                    $arARCHIVEutf[$k][] = $doc['Signature_Delivered'];
                    $arARCHIVEutf[$k][] = $doc['Tarif'];
                    $arARCHIVEutf[$k][] = $doc['Delivery_Act'];

                    $k++;
                    $itog = $itog + (float)$doc['Tarif'];
                }
                $pExcel = new PHPExcel();
                $pExcel->setActiveSheetIndex(0);
                $aSheet = $pExcel->getActiveSheet();
                $pExcel->getDefaultStyle()->getFont()->setName('Arial');
                $pExcel->getDefaultStyle()->getFont()->setSize(10);
                $Q = iconv("windows-1251", "utf-8", 'Отчет');
                $aSheet->setTitle($Q);
                $head_style = [
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ]
                ];
                $num_sel_title = 'E2';
                $title = iconv('windows-1251', 'utf-8','Расшифровка реализации №');
                $val_title = "{$title} {$num_doc} {$date_d}";
                $aSheet->setCellValue($num_sel_title, $val_title);
                $i = 5;
                $arJ = ['A','B','C','D','E','F','G','H','I','J'];
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
                $nm = $i++;
                $nm_sel = 'I' . $nm;
                $aSheet->setCellValue($nm_sel, iconv("windows-1251", "utf-8",'Итого: ') . $itog);

                $i--;
                foreach ($arJ as $cc)
                {
                    $aSheet->getColumnDimension($cc)->setWidth(20);
                }
                $aSheet->getColumnDimension('E')->setWidth(40);
                $aSheet->getStyle('A5:J5')->applyFromArray($head_style);
                $aSheet->getStyle('A5:J'.$i)->getAlignment()->setWrapText(true);
                $aSheet->getStyle('A5:J'.$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                include_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/PhpExcel/Classes/PHPExcel/Writer/Excel5.php';
                // AddToLogs('return', ['obj'=>$pExcel]);
                $objWriter = new PHPExcel_Writer_Excel5($pExcel);
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment; filename="Расшифровка реализации ' . $_GET['DocumentDate'] . '.xls"');
                header('Cache-Control: max-age=0');
                $objWriter->save('php://output');
                exit();

            }
        }
        if (trim($_GET['type']) === 'report_delivery'){
            if ((strlen($_GET['start'])) && (strlen($_GET['end'])))
            {
                $arResult['REPORT'] = [];
                $result = $client->GetDocsListClient(
                    [
                        'INN' => iconv('windows-1251','utf-8',$arResult['CURRENT_INN']),
                        'BranchID' => '',
                        'BranchPrefix' => '',
                        'StartDate' => substr($_GET['start'],6,4).'-'.substr($_GET['start'],3,2).'-'.substr($_GET['start'],0,2),
                        'EndDate' => substr($_GET['end'],6,4).'-'.substr($_GET['end'],3,2).'-'.substr($_GET['end'],0,2),
                        'NumPage' => 0,
                        'DocsToPage' => 10000,
                        'Type' => 1
                    ]
                );
                $mResult = $result->return;
                $obj = json_decode($mResult, true);
                $arResult['REPORT'] = $obj;

                /*dump($arResult['REPORT']);
                exit();*/

                    $arData = $arResult['REPORT']['Docs'];
                    include_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/PhpExcel/Classes/PHPExcel.php';
                    $arARCHIVEutf = [
                        [
                            iconv('windows-1251', 'utf-8', 'Номер накладной'),
                            iconv('windows-1251', 'utf-8', 'Дата приема заказа'),
                            iconv('windows-1251', 'utf-8', 'Вес, кг'),
                            iconv('windows-1251', 'utf-8', 'Наименование получателя'),
                            iconv('windows-1251', 'utf-8', 'Адрес получателя'),
                            iconv('windows-1251', 'utf-8', 'Дата вручения'),
                            iconv('windows-1251', 'utf-8', 'Время вручения'),
                            iconv('windows-1251', 'utf-8', 'Фамилия получателя'),
                            iconv('windows-1251', 'utf-8', 'Стоимость, руб.'),
                            iconv('windows-1251', 'utf-8', 'Реализация, № Акта, дата'),

                        ]
                    ];

                    $k=1;
                    $itog = 0;
                    foreach ($arData as $doc){
                        $arARCHIVEutf[$k][] = $doc['NumDoc'];
                        $arARCHIVEutf[$k][] = substr($doc['DateDoc'],8,2).'.'.substr($doc['DateDoc'],5,2).'.'.substr($doc['DateDoc'],0,4);
                        $arARCHIVEutf[$k][] = $doc['Delivery_Weight'];
                        $arARCHIVEutf[$k][] = $doc['CompanyRecipient'] . ' ' . $doc['NameRecipient'];
                        $arARCHIVEutf[$k][] = $doc['AdressRecipient'];
                        $arARCHIVEutf[$k][] = $doc['Date_Delivered'];
                        $arARCHIVEutf[$k][] = $doc['Time_Delivered'];
                        $arARCHIVEutf[$k][] = $doc['Signature_Delivered'];
                        $arARCHIVEutf[$k][] = $doc['Tarif'];
                        $arARCHIVEutf[$k][] = $doc['Delivery_Act'];

                        $k++;
                        $itog = $itog + (float)$doc['Tarif'];
                    }
                    $pExcel = new PHPExcel();
                    $pExcel->setActiveSheetIndex(0);
                    $aSheet = $pExcel->getActiveSheet();
                    $pExcel->getDefaultStyle()->getFont()->setName('Arial');
                    $pExcel->getDefaultStyle()->getFont()->setSize(10);
                    $Q = iconv("windows-1251", "utf-8", $title . ' ' . $_GET['start'] . '-' . $_GET['end']);
                    $aSheet->setTitle($Q);
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
                    $styleArray = [
                        'borders' => [
                            'allborders' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => ['argb' => 'FF000000'],
                            ],
                        ],
                    ];
                    $num_sel_title = 'E2';
                    $aSheet->getStyle('E2')->getFont()->setSize(16);
                    $aSheet->getStyle('E2')->getFont()->getColor()->setRGB('317eac');

                    $title = iconv('windows-1251', 'utf-8','Отчет по услугам экспресс-доставки ');
                    $val_title = $title . ' ' . $_GET['start'] . '-' . $_GET['end'];
                    $aSheet->setCellValue($num_sel_title, $val_title);
                    $i = 5;
                    $arJ = ['A','B','C','D','E','F','G','H','I','J'];
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
                    $nm = $i++;
                    $nm_sel = 'I' . $nm;
                    $aSheet->setCellValue($nm_sel, iconv("windows-1251", "utf-8",'Итого: ') . $itog);

                    $i--;
                    foreach ($arJ as $cc)
                    {
                        $aSheet->getColumnDimension($cc)->setWidth(20);
                    }
                    $aSheet->getColumnDimension('E')->setWidth(40);
                    $aSheet->getStyle('A5:J5')->applyFromArray($head_style);
                    $aSheet->getStyle('A5:J'.$i)->getAlignment()->setWrapText(true);
                    $aSheet->getStyle('A5:J'.$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

                    $aSheet->getStyle('A5:J'.$i)->applyFromArray($styleArray);

                    include_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/PhpExcel/Classes/PHPExcel/Writer/Excel5.php';
                    // AddToLogs('return', ['obj'=>$pExcel]);
                    $objWriter = new PHPExcel_Writer_Excel5($pExcel);
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment; filename="Отчет по услугам экспресс-доставки  ' .
                        $_GET['start'] . '-' . $_GET['end'] . '.xls"');
                    header('Cache-Control: max-age=0');
                    $objWriter->save('php://output');
                    exit();

            }
        }

    }


}

$this->IncludeComponentTemplate($arResult['MODE']);