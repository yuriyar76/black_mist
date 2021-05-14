<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
    die();
}

include_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/components/black_mist/delivery.packages/UpdateInvoice/UpdateInvoice.php");

if(!empty($_GET['update'])){
    try {
        $data = new UpdateInvoice($_POST);
        $data->updateInvoice();

        $res = [
            'update'=>$data->updateInvoiceId,
            'list'=>$data->eventsInvoiceId,
            'number'=>$data->numberInvoice,
            'status'=>$data->lastStatusEvent
        ];
        echo(json_encode($res));
    }catch(Exception $e){
        echo(json_encode(['error' => iconv('windows-1251', 'utf-8', $e->getMessage())]));
        exit();
    }
}else{
    try {
        $data = new UpdateInvoice($_POST);
        $data->update();
        $res = [
            'update'=>1
        ];
        echo(json_encode($res));
    }catch(Exception $e){
        echo(json_encode(['error' => iconv('windows-1251', 'utf-8', $e->getMessage())]));
        exit();
    }

}

//dump(['new'=>$data->newInvoiceId, 'update'=>$data->updateInvoiceId]);
