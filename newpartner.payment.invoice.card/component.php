<? if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");
global $USER;
ini_set("soap.wsdl_cache_enabled", "0" );
ini_set("default_socket_timeout", "300");

$arResult = [];
if($arParams['PAYMENT_INVOICE_CARD'] === "Y"){
    foreach($_POST as $key=>$value){
        $arResult[$key] = htmlspecialchars($value);
    }
  //  $jsonParam = convArrayToUTF(['NumDoc' => , 'NumDocZ' => '��-06129']);
    $jsonParam = [
        "NumDoc" =>  $arResult['number'],
        "NumDocZ" => $arResult['number_z'],
    ];
    $jsonParam1c = convArrayToUTF($jsonParam);
    $client = soap_inc();
    $result = $client->GetDocInfoForPayment($jsonParam1c);
    $mResult = $result->return;
    $res = json_decode($mResult, true);
    $sum = str_replace(' ','',$res['Sum']);
    $res['Sum'] = $sum;
    $res = arFromUtfToWin($res);
    if($res['Error']){
        echo json_encode(convArrayToUTF(['error' => $res['Error']]));
    }
    if (!empty($res['Sum'])){
        $sum = (float)$res['Sum'];
        $inn = (int)$res['Organization'];
        if($inn == '7718905538'){
            $org = 'MSD';
        }elseif($inn == '7717739535'){
            $org = 'NP';
        }
         
        echo json_encode(convArrayToUTF(['sum' => $sum, 'Org' => $org]));
    }
}