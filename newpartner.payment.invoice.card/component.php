<? if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
include $_SERVER['DOCUMENT_ROOT'] . "/bitrix/components/black_mist/delivery.packages/PaymentInvoiceCard/ValidateInvoice/ValidateInvoice.php";
include_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/components/black_mist/delivery.packages/functions.php");
global $USER;
ini_set("soap.wsdl_cache_enabled", "0" );
ini_set("default_socket_timeout", "300");

$arResult = [];

if($arParams['PAYMENT_INVOICE_CARD_VALIDATE'] === "Y"){

    try {
        $obj = new ValidateInvoice($_POST);
        $num = ['number_z' => $obj->data['number_z']];
        $user = $obj->data['user'];
        $resJs = [$num, $user];
        $resJs = convArrayToUTF($resJs);
        echo json_encode($resJs);
        exit;
    } catch (Exception $e){
        $res = [
            'error' => $e->getMessage()
        ];
        $resJs = convArrayToUTF($res);
        echo json_encode($resJs);
        exit;
    }


}

if($arParams['PAYMENT_INVOICE_CARD'] === "Y"){
    foreach($_POST as $key=>$value){
        $arResult[$key] = htmlspecialchars($value);
    }
    //  $jsonParam = convArrayToUTF(['NumDoc' => , 'NumDocZ' => 'ิห-06129']);
    $jsonParam = [
        "NumDoc" =>  $arResult['number'],
        "NumDocZ" => $arResult['number_z'],
    ];
    //$jsonParam1c = convArrayToUTF($jsonParam);
    $client = soap_inc();
    $result = $client->GetDocInfoForPayment($jsonParam);
    $mResult = $result->return;
    $res = json_decode($mResult, true);

    $sum = str_replace('ย ','',$res['Sum']);
    $res['Sum'] = $sum;
    $res = arFromUtfToWin($res);
    if($res['Error']){
        echo json_encode(convArrayToUTF(['error' => $res['Error']]));
    }
    if (!empty($res['Sum'])){
        $number = trim($arResult['number']);
        $number_z = trim($arResult['number_z']);
        $sum = (float)str_replace(',','.',$res['Sum']);
        $inn = (int)$res['Organization'];
        if($inn == '7718905538'){
            $org = 'MSD';
        }elseif($inn == '7717739535'){
            $org = 'NP';
        }
        $arJsonP = [
            'Sum' => $sum,
            'Org' => $org,
            'Number_inv' => $number,
            'Number_inv_z' => $number_z
        ];
        $arrJson = json_encode($arJsonP);
        $_SESSION['DataInvoicePay'] = $arrJson;
        echo $arrJson;
    }
}
