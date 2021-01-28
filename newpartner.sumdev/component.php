<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
die();
}
include_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/components/black_mist/delivery.packages/functions.php");

$arResult = [];
if ($_GET['report_as'] === 'Y'){
    include_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/PhpExcel/Classes/PHPExcel.php';
    $Result = [];
    $numbers = [];
    if ($_POST){
        $result = json_decode($_POST['numbersphp'], true);
        foreach($result as $value){
            $key = $value['NAME'];
            $Result[$key] = htmlspecialcharsEx($value);
            $numbers[] = $value['NAME'];
        }
        $Result = arFromUtfToWin($Result);
        $numbers = arFromUtfToWin($numbers);
        $arFilter = [
            "NAME" => $numbers,
            "ACTIVE" => "Y"
        ];
        $arSelect = [
            "ID", "NAME", "PROPERTY_SUMM_DEV", "PROPERTY_CENTER_EXPENSES.NAME"
        ];
        $resArr = GetInfoArr(false, false, 83, $arSelect, $arFilter, false);
        foreach($resArr as $key => $value){
            $Result[$value['NAME']]['PROPERTY_SUMM_DEV_VALUE'] = $value['PROPERTY_SUMM_DEV_VALUE'];
            $Result[$value['NAME']]['PROPERTY_CENTER_EXPENSES_NAME'] = $value['PROPERTY_CENTER_EXPENSES_NAME'];
        }
        $Result = convArrayToUTF($Result);
        $arData = [];
        $arData[] =
            [ iconv('windows-1251', 'utf-8','Номер накладной'),
                iconv('windows-1251', 'utf-8','Принято'),
                iconv('windows-1251', 'utf-8','Статус'),
                iconv('windows-1251', 'utf-8','Доставлено'),
                iconv('windows-1251', 'utf-8','Город получателя'),
                iconv('windows-1251', 'utf-8','Получатель'),
                iconv('windows-1251', 'utf-8','Компания получателя'),
                iconv('windows-1251', 'utf-8','Город отправителя'),
                iconv('windows-1251', 'utf-8','Отправитель'),
                iconv('windows-1251', 'utf-8','Компания отправителя'),
                iconv('windows-1251', 'utf-8','Центр затрат'),
                iconv('windows-1251', 'utf-8','Тариф (руб.)'),
                iconv('windows-1251', 'utf-8','Вес')
            ];
        $i = 1;
        foreach ($Result as $value){
            if ($value['tarif']){
                $summ_dev = $value['tarif'];
            }else{
                $summ_dev =  $value['PROPERTY_SUMM_DEV_VALUE'];
            }
            if ($value['center_cost']){
                $center_cost = $value['center_cost'];
            }else{
                $center_cost =  $value['PROPERTY_CENTER_EXPENSES_NAME'];
            }
             $arData[$i] = [
                $value['NAME'],
                $value['DATE_CREATE'],
                $value['state_text'],
                $value['DATE_DELIVERY'],
                $value['PROPERTY_CITY_RECIPIENT_NAME'],
                $value['PROPERTY_NAME_RECIPIENT_VALUE'],
                $value['PROPERTY_COMPANY_RECIPIENT_VALUE'],
                $value['PROPERTY_CITY_SENDER_NAME'],
                $value['PROPERTY_NAME_SENDER_VALUE'],
                $value['PROPERTY_COMPANY_SENDER_VALUE'],
                $value['center_cost'],
                $summ_dev,
                $value['PROPERTY_WEIGHT_VALUE'],
            ];
            $i++;
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
        $aSheet->getStyle('A1:M1')->applyFromArray($head_style);
        $aSheet->getStyle('A1:M'.$i)->getAlignment()->setWrapText(true);
        $aSheet->getStyle('A1:M'.$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
        //AddToLogs('return', ['obj2'=>$_SERVER['DOCUMENT_ROOT']]);
        include_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/PhpExcel/Classes/PHPExcel/Writer/Excel5.php';
        $objwriter = new PHPExcel_Writer_Excel5($pExcel);
        $path = $_SERVER['DOCUMENT_ROOT'] . "/report_" . date('d.m.Y').'.xls';
        $objwriter->save($path);
        $pathutf = iconv('windows-1251', 'utf-8',"/report_" . date('d.m.Y').'.xls');
        $dataJson = [
            'path' => $pathutf
        ];
        echo json_encode($dataJson);
        exit;

      }
}

if ($_GET['numberNo'] === 'Y'){

    $number_invoice = $_GET['numberInvoice'];
    if(preg_match('/_{2}/', $number_invoice)){
        $number_invoice = preg_replace('/_{2}/', '\/', $number_invoice);
    }
    $invoice_data = GetInfoArr(false, false,83, [
        "ID", "IBLOCK_ID", "NAME", "PROPERTY_SUMM_DEV"], ['NAME' => $number_invoice]);
    AddToLogs('getNumber', $invoice_data);
    $sum_dev = $invoice_data['PROPERTIES']['SUMM_DEV']['VALUE'];
    $jsonRes = [
        'sum_dev' => $sum_dev
    ];
    $jsonRes = convArrayToUTF($jsonRes);
    echo json_encode($jsonRes);
    exit();
}

if ($_GET['number'] === 'Y'){

    $number_invoice = $_GET['numberInvoice'];
    if(preg_match('/_{2}/', $number_invoice)){
        $number_invoice = preg_replace('/_{2}/', '\/', $number_invoice);
    }
    $current_client = $_GET['curClient'];
    if($number_invoice){
        try {
            $client_data = GetInfoArr(false, $current_client, 40);
            $inn_client = $client_data['PROPERTIES']['INN']['VALUE'];
            $client = soap_inc();
            $ar_params_json = [
                'NumDoc' => trim($number_invoice)
            ];
            $result = $client->GetDocInfo($ar_params_json);
            $m_res = $result->return;
            $obj_res = json_decode($m_res, true);
            $obj_res = arFromUtfToWin($obj_res);
           // AddToLogs('getNumber', $obj_res);
            if(!empty($obj_res)) {
                $arr_for_calc = [
                    'city_sender' => $obj_res['ГородОтправителя'],
                    'region_sender' => $obj_res['ОбластьОтправителя'],
                    'country_sender' => $obj_res['СтранаОтправителя'],
                    'city_recipient' => $obj_res['ГородПолучателя'],
                    'region_recipient' => $obj_res['ОбластьПолучателя'],
                    'country_recipient' => $obj_res['СтранаПолучателя'],
                    'weight' => (float)$obj_res['ВесВходящий'],
                    'ob_weight' => (float)$obj_res['ВесВходящийОбъемный']
                ];
                $sender = "{$arr_for_calc['city_sender']}, {$arr_for_calc['region_sender']}, {$arr_for_calc['country_sender']}";
                $recipient = "{$arr_for_calc['city_recipient']}, {$arr_for_calc['region_recipient']}, {$arr_for_calc['country_recipient']}";
                $sender_id = GetCityId($sender, true);
                $recipient_id = GetCityId($recipient, true);
                if(empty($arr_for_calc['weight'])){
                    $arr_for_calc['weight'] = 0.1;
                }
                if(empty($arr_for_calc['ob_weight'])){
                    $arr_for_calc['ob_weight'] = 0;
                }
                $sum_dev = getSumDelivered($arr_for_calc['weight'], 0, $sender_id, $recipient_id, 0, $inn_client);
               // AddToLogs( 'getNumber',['weight'=>$arr_for_calc['weight'],'$sender_id'=>$sender_id, '$recipient_id'=>$recipient_id, '$inn_client'=>$inn_client, '$sum_dev'=>$sum_dev]);

            }else{
                echo json_encode('ERROR');
                exit();
            }
        } catch (SoapFault $e) {
            echo json_encode('ERROR - '.$e);
            exit();
        }

    }else{
        echo json_encode('ERROR');
        exit();
    }

    $jsonRes = [
        'sum_dev' => $sum_dev
    ];
    $jsonRes = convArrayToUTF($jsonRes);
    echo json_encode($jsonRes);
    exit();
}

if ($_GET['list'] === 'Y'){
    $Result = [];
   if ($_POST){
       $curclient = (int)json_decode($_POST['curclient'], true);
       $result = json_decode($_POST['numbers'], true);
       foreach($result as $value){
           $arvrem[] = htmlspecialcharsEx($value);
       }
       //AddToLogs('return', ['input_arr'=>$arvrem]);
       $Result = arFromUtfToWin($arvrem);
       $arFilter = [
           "NAME" => $Result,
           "ACTIVE" => "Y"
       ];
       $arSelect = [
           "ID", "NAME", "PROPERTY_SUMM_DEV", "PROPERTY_WEIGHT", "PROPERTY_TOTAL_GABWEIGHT",
           "PROPERTY_CITY_RECIPIENT"
       ];
       $collectnums = GetInfoArr(false, false, 83, $arSelect, $arFilter, false);
       //AddToLogs('return', ['data'=>$collectnums]);
       if($collectnums){
           foreach($collectnums as $value){
                   $total_weight = $value["PROPERTY_WEIGHT_VALUE"];
                   $total_gabweight = $value["PROPERTY_TOTAL_GABWEIGHT_VALUE"];
                   if(!$total_gabweight){
                       $total_gabweight = 0;
                   }
                   $city_recipient = $value['PROPERTY_CITY_RECIPIENT_VALUE'];
                   $city_sender = $value['PROPERTY_CITY_SENDER_VALUE'];
                   $sum_dev = getSumDev($total_weight, $total_gabweight, $city_recipient, $curclient);
                   if ($sum_dev) {
                       CIBlockElement::SetPropertyValuesEx($value["ID"], false, [979 => $sum_dev]);
                       $arResult[] =  ["SUM_DEV" => $sum_dev, "NUMBER" => $value['NAME']];
                   }else{
                       if (!empty($value['PROPERTY_SUMM_DEV_VALUE'])) {
                           $arResult[] =  ["SUM_DEV" => $value['PROPERTY_SUMM_DEV_VALUE'], "NUMBER" => $value['NAME']];
                       }else{
                           $arResult[] =  ["SUM_DEV" => 0, "NUMBER" => $value['NAME']];
                       }
                   }
           }
       }

       $arjson = convArrayToUTF($arResult);
       $arJson = json_encode($arjson);

       echo $arJson;
       exit();
   }


 }

/* центр затрат абсолют страхование */
if ($_GET['cost_center'] === 'Y'){
    $result = json_decode($_POST['numbers'], true);
    foreach ($result as $value) {
        $arvrem[] = htmlspecialcharsEx($value);
    }
    $Result = arFromUtfToWin($arvrem);
    $arFilter = [
        "NAME" => $Result,
        "ACTIVE" => "Y"
    ];
    $arSelect = [
        "ID", "NAME", "PROPERTY_CENTER_EXPENSES.NAME"
    ];
    $collectnums = GetInfoArr(false, false, 83, $arSelect, $arFilter, false);
    AddToLogs('return', ['data_return'=>$collectnums]);
    if ($collectnums) {
        $arResult = $collectnums;
    }

    if($arResult){
        $arjson = convArrayToUTF($arResult);
        $arJson = json_encode($arjson);
    }else{
        $arJson = false;
    }
    echo $arJson;
    exit();
}


if ($_GET['return'] === 'Y') {
    $result = json_decode($_POST['numbers'], true);
    foreach ($result as $value) {
        $arvrem[] = htmlspecialcharsEx($value);
    }
    $Result = arFromUtfToWin($arvrem);
    $arFilter = [
        "NAME" => $Result,
        "=PROPERTY_WITH_RETURN" => 1,
        "ACTIVE" => "Y"
    ];
    $arSelect = [
        "ID", "NAME",  "DATE_CREATE", "PROPERTY_CITY_RECIPIENT.NAME",
        "PROPERTY_CITY_SENDER.NAME", "PROPERTY_WITH_RETURN", "PROPERTY_COMPANY_SENDER", "PROPERTY_COMPANY_RECIPIENT",
        "PROPERTY_NAME_RECIPIENT", "PROPERTY_PLACES", "PROPERTY_WEIGHT", "PROPERTY_OB_WEIGHT"
    ];
    $resArr = GetInfoArr(false, false, 83, $arSelect, $arFilter, false);
    if($resArr){
        $arResult = $resArr;
    }

   /* foreach ($Result as $value) {
        $number = $value;
        $arFilter = [
            "NAME" => trim($number),
            "=PROPERTY_WITH_RETURN" => 1,
            "ACTIVE" => "Y"
        ];
        $arSelect = [
            "ID", "NAME",  "DATE_CREATE", "PROPERTY_CITY_RECIPIENT.NAME",
            "PROPERTY_CITY_SENDER.NAME", "PROPERTY_WITH_RETURN", "PROPERTY_COMPANY_SENDER", "PROPERTY_COMPANY_RECIPIENT",
            "PROPERTY_NAME_RECIPIENT", "PROPERTY_PLACES", "PROPERTY_WEIGHT", "PROPERTY_OB_WEIGHT"
        ];
        $resArr = GetInfoArr(false, false, 83, $arSelect, $arFilter);
        if($resArr){
            $arResult[] = $resArr;
        }

    }*/
    if($arResult){
        $arjson = convArrayToUTF($arResult);
        $arJson = json_encode($arjson);
    }else{
        $arJson = false;
    }
    echo $arJson;
    exit();
}


foreach ($_POST as $key=>$value){
    $key = htmlspecialchars($key);
    $arResult[$key] = htmlspecialchars($value);
}
$client = 0;  // id клиента
$client_inn = $arResult['data_client'];  // id обмена
$total_weight = $arResult['data_weight'];
$total_gabweight =  $arResult['data_gabweight'];
$city_recipient =  $arResult['data_city_id'];

$sum_dev = getSumDev($total_weight, $total_gabweight, $city_recipient, $client, $client_inn);

$data = json_encode($sum_dev);

echo $data;
