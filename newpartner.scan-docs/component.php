<?php
/* newpartner.order-services.php */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

ini_set("soap.wsdl_cache_enabled", "0" );
ini_set("default_socket_timeout", "300");
include_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/components/black_mist/delivery.packages/functions.php");

$arResult['OPEN'] = true;
if ($arParams['MODE'] == '1c'){
    if ($_POST['type'] == 'SetScanDoc'){
        $login1c = GetSettingValue(705);
        $pass1c = GetSettingValue(706);
        if ((strlen($_POST['login'])) && (strlen($_POST['pass']))){
            if (($_POST['login'] == $login1c) && ($_POST['pass'] == $pass1c))
            {
                $arUK = [];
                $select = ["ID","NAME","PROPERTY_UK"];
                $filter =["IBLOCK_ID" => 40, "PROPERTY_UK" => false];
                $res = CIBlockElement::GetList(["created" => "ASC"], $filter, false, false, $select);
                while($ob = $res->GetNextElement()){
                    $arField = $ob->GetFields();
                    $arUK[] = $arField;
                }
                file_put_contents($_SERVER['DOCUMENT_ROOT'].'/scandocs/log_uk.txt', print_r ( $arUK, true));
                if(!empty($arUK)){
                    $UK = [];
                    foreach($arUK as $key=>$value){
                        $UK[] = $value['ID'];
                    }
                }
                $arResponseUtf = json_decode($_POST['Response'], true);
                $arResponse = arFromUtfToWin($arResponseUtf);
                $inn_client = strip_tags(trim($arResponse['PrtnerID']));
                if($inn_client){
                    $arFields = [];
                    $select = ["ID","NAME","DATE_CREATE","MODIFIED_BY","PROPERTY_UK","PROPERTY_INN"];
                    $filter =["IBLOCK_ID" => 40, "PROPERTY_INN" => $inn_client];
                    $res = CIBlockElement::GetList(["created" => "ASC"], $filter, false, false, $select);
                    while($ob = $res->GetNextElement()){
                        $arField = $ob->GetFields();
                        $arFields[] = $arField;
                    }
                    $idUK = true;

                }else{
                    $idUK = false;
                }

                if(!empty($arFields)&&$idUK){
                    if($arFields[0]['PROPERTY_UK_VALUE']){
                        $idUK = trim($arFields[0]['PROPERTY_UK_VALUE']);
                    }else{
                        $idUK = false;
                    }
                }

                if($idUK){
                    if(in_array($idUK, $UK)){
                        foreach($UK as $key=>$value){
                            if($value == $idUK){
                                $DIRNAME = "scandocs-".$value;
                                break;
                            }
                        }
                    }
                    $dir = $_SERVER['DOCUMENT_ROOT'].'/scandocs/'.$DIRNAME;
                    if(!is_dir($dir)) {
                        mkdir($dir);
                    }
                    $currentport = intval(GetSettingValue(761, false));
                    $currentip = GetSettingValue(683, false, $idUK);
                    $currentlink = GetSettingValue(704, false, $idUK);
                    $login1c = GetSettingValue(705, false, $idUK);
                    $pass1c = GetSettingValue(706, false, $idUK);

                    if ($currentport > 0) {
                        $url = "http://".$currentip.':'.$currentport.$currentlink;
                        $client = new SoapClient($url, array('login' => $login1c,
                            'password' => $pass1c,
                            'proxy_host' => $currentip,
                            'proxy_port' => $currentport,
                            'exceptions' => false));
                    }
                    else {
                        $url = "http://".$currentip.$currentlink;
                        $client = new SoapClient($url,array('login' => $login1c,
                            'password' => $pass1c,
                            'exceptions' => false));
                    }

                   $arJson = [
                       'NumDoc'=> $arResponse['NumDoc'],
                       'FileName'=> $arResponse['FileName'],
                   ];
                   $file_name = $_SERVER['DOCUMENT_ROOT'].'/scandocs/'.$DIRNAME.'/'.trim(strip_tags($arResponse['NumDoc'])).'_'.trim(strip_tags($arResponse['FileNameForSite'])).
                       trim(strip_tags($arResponse['FileExt']));
                    $file_name_path = $_SERVER['SERVER_NAME'].'/scandocs/'.$DIRNAME.'/'.trim(strip_tags($arResponse['NumDoc'])).'_'.trim(strip_tags($arResponse['FileNameForSite'])).
                        trim(strip_tags($arResponse['FileExt']));
                   $res = $client->GetDocsFile($arJson);
                    $mResult = $res->return;
                    $file_img = base64_decode($mResult);
                    file_put_contents($file_name, print_r ($file_img, true));
                    if(is_file($file_name)) {
                        $date_log = date('d.m.Y H:i:s');
                        $strLog =  $DIRNAME." Дата загрузки изображения - $date_log".PHP_EOL."Файл -  $file_name".PHP_EOL."-------------------------------------------------------------------------------------".PHP_EOL;
                        $strlog =  iconv('windows-1251','utf-8',$strLog);
                        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/scandocs/logscandocs.txt', print_r( $strLog, true), FILE_APPEND);
                        $arres = [
                            'RESPONSE'=> 'Успешно',
                            'PATH' =>   $file_name_path
                        ];
                        $arres = convArrayToUTF($arres);
                        $string_json = json_encode($arres);
                        echo $string_json;
                    }else{
                        $arres = [
                            'RESPONSE'=> 'Ошибка записи на диск',
                            'PATH_ERR' => $file_name_path
                        ];
                        $arres = convArrayToUTF($arres);
                        $string_json = json_encode($arres);
                        echo $string_json;
                    }

                    //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/scandocs/log_response.txt', print_r ( $string_json, true));
                    //echo  "RESPONSE: Успешно".PHP_EOL."PATH: $file_name";

               }else{

                    echo  "ERROR: Нет управляющей компании";
               }
            }
        }
    }
}
