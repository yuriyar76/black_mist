<?php


include __DIR__ . '/Model.php';

use Complex\Exception;

class PaymentInvoiceCard extends Model
{
    private $data = [];
    public $arResult = [];
    private $dataFrom1C;

    public function __construct(array $post)
  {
      parent::__construct();
      $this->data = $post;
      $this->dataValid();
  }

  private function dataValid()
  {
      foreach($this->data as $key=>$value){
          $arResult[$key] = \NPAllFunc::NewQuotes($value);
      }
      if(empty($arResult))
          throw new Exception('Пустая форма оплаты!');
      $this->arResult = $arResult;
      $this->getDataFrom1C();

  }

  private function getDataFrom1C()
  {
      if($this->arResult['number'] || $this->arResult['number_z']){
          $jsonParam = [
              "NumDoc" =>  $this->arResult['number'],
              "NumDocZ" => $this->arResult['number_z'],
          ];
      }else{
          throw new Exception('Нет номеров накладной/заявки');
      }
      $client =  \NPAllFunc::soap_inc();
      $result = $client->GetDocInfoForPayment($jsonParam);
      $mResult = $result->return;
      $res = json_decode($mResult, true);
      $res = \NPAllFunc::arFromUtfToWin($res);
      if(empty($res) || !empty($res['Error']))
          throw new Exception('Ошибка соединения с 1С! ' . $res['Error']);
      $this->dataFrom1C = $res;
      if(empty($res['Sum']))
          throw new Exception('Нет данных Сумма заказа');
      if(empty($res['Email']) || empty($res['Phone'])){
          $arrData = $this->getDataFromBase();
      }


  }

    private function getDataFromBase()
    {
        $res=[];

        if($this->arResult['number']){
            $iblock = 83;
            $arSelect = [
                'ID', 'NAME', "PROPERTY_1023", "PROPERTY_CREATOR.ID", "PROPERTY_1024",
                "PROPERTY_1076", "PROPERTY_1075", "PROPERTY_1056", "PROPERTY_1081",
            ];
            $dataApp = \NPAllFunc::GetInfoArr(false, $this->idInvoice, 117, $arSelect);
        }
        if($this->arResult['number_z']){
            $iblock = 113;
            $arFilter = [
                "NAME" => $this->arResult['number_z'],
                "IBLOCK_ID" => $iblock,
                "ACTIVE" => "Y"
            ];

            $arSelect = [
                'ID', 'NAME', "PROPERTY_1097", "PROPERTY_1098"
            ];
            $dataApp = \NPAllFunc::GetInfoArr(false, false, 117, $arSelect, $arFilter);
            $res = [
                'email_payer' => $dataApp['PROPERTY_1097_VALUE'],
                'phone_payer' =>  $dataApp['PROPERTY_1098_VALUE'],
            ];
        }



        return $res;
    }

}