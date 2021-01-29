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
  //  $jsonParam = convArrayToUTF(['NumDoc' => , 'NumDocZ' => 'тк-06129']);
    $jsonParam = [
        "NumDoc" =>  "199-2627394",
        "NumDocZ" => "тк-06129"
    ];
    $jsonParam1c = convArrayToUTF($jsonParam);
    $client = soap_inc();
    $result = $client->GetDocInfoForPayment($jsonParam1c);
    $mResult = $result->return;
    $res = json_decode($mResult, true);
    $res=arFromUtfToWin($res);
    $ar_params_json = [
        'NumDoc' => "199-2627394"
    ];
    $result1 = $client->GetDocInfo($ar_params_json);
    $m_Result = $result1->return;
    dump($client);
    dump($mResult);
    dump($m_Result);
}

dump($arParams);

