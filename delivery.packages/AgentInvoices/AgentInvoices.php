<?php
include __DIR__ . '/../NPAllFunc.php';
include __DIR__ . '/../IndexComponent.php';
/*
 * Для работы с 117 инфоблоком  и другими инфоблоками агентов
 * */
class AgentInvoices extends IndexComponent
{
    protected $oldNumber;
    public $newNumber;
    protected $appName;
    protected $idInvoice;
    protected $dataApp;
    protected $idAgent;
    protected $idUk;
    protected $uId;
    protected $innAgent;
    protected $client;
    protected $dataJson = [];
    protected $get = [];
    protected $historyNumbers;
    protected $mResult;


  public function __construct(array $data, array $get=[])
  {
      parent::__construct();
      $this->get = $get;
      $this->setData($data);
  }

  protected function setData($data){
      if($this->get['number'] === 'Y' ){
          if(!$data) throw new Exception('Нет данных, операция невозможна.');
          if(is_array($data)){
              foreach($data as $key=>$value){
                  if($key === 'number_edit_num'){
                      $this->oldNumber = iconv('utf-8', 'windows-1251', htmlspecialchars(strip_tags($value)));
                  }
                  if($key === 'id_edit_num'){
                      $this->idInvoice = iconv('utf-8', 'windows-1251', htmlspecialchars(strip_tags($value)));
                  }
                  if($key === 'id_edit_app'){
                      $this->appName = iconv('utf-8', 'windows-1251', htmlspecialchars(strip_tags($value)));
                  }
                  if($key === 'edit_number_new'){
                      if(!$value) throw new Exception('Номер накладной!');
                      $this->newNumber = iconv('utf-8', 'windows-1251', htmlspecialchars(strip_tags($value)));
                  }
              }
          }
      }

   }

   public function getData()
   {
       if($this->get['number'] === 'Y' ) {
           $arSelect = [
               'ID', 'NAME', "PROPERTY_1023", "PROPERTY_CREATOR.ID", "PROPERTY_1024",
               "PROPERTY_1076", "PROPERTY_1075", "PROPERTY_1056", "PROPERTY_1081",
           ];
           $dataApp = NPAllFunc::GetInfoArr(false, $this->idInvoice, 117, $arSelect);
           if (empty($dataApp)) {
               throw new Exception('Ошибка получения данных заявки');
           }
           $this->dataApp = $dataApp;
           $this->idUk = $dataApp['PROPERTY_1075_VALUE'];
           $this->uId = $dataApp['PROPERTY_1056_VALUE'];
           $this->innAgent = $dataApp['PROPERTY_1076_VALUE'];
           $this->idAgent = $dataApp['PROPERTY_CREATOR_ID'];
           $this->historyNumbers =  $dataApp['PROPERTY_1081_VALUE'];
           $this->client = NPAllFunc::soapLink($this->idUk);
       }
       return $this;
   }

   public function setData1c()
   {
       $this->prepData1c();
       if($this->get['number'] === 'Y' ) {
           $res = $this->client->SetDocsNumber($this->dataJson);
           $mResult = $res->return;
           $mResult = json_decode($mResult, true);
           $this->mResult = NPAllFunc::arrUtfToWin($mResult);
           if (!$mResult['Status']) {
               throw new Exception('Невозможно изменить номер накладной');
           }
       }
       return $this;

   }

    /**
     * @return $this
     *
     */
    public function checkInvBase()
    {
        if($this->get['number'] === 'Y' ) {

            if($this->historyNumbers){
                $arJs = json_decode($this->historyNumbers, true);
                $new_date =  date('d:m:Y H:i:s');
                $oldNumber =iconv('windows-1251', 'utf-8',$this->oldNumber);
                $arJs[$new_date] = $oldNumber;
                $numbersJson = json_encode($arJs);
            }else{
                $new_date = date('d:m:Y H:i:s');
                $oldNumber =iconv('windows-1251', 'utf-8',$this->oldNumber);
                $arJs = [$new_date => $oldNumber];
                $numbersJson = json_encode($arJs);
            }
            CIBlockElement::SetPropertyValuesEx($this->idInvoice, 117,
                [
                    1024 => $this->newNumber,
                    1081 => $numbersJson
                ]);

        }
        return $this;
    }


    protected function prepData1c()
    {
        if($this->get['number'] === 'Y' ) {
            $dataJson = [
                "INN_AGENT" => $this->innAgent,
                "UID" => $this->uId,
                "NUMBER_NEW" => $this->newNumber,
                "NUMBER_OLD" => $this->oldNumber,
                "NUMBER_APP" => $this->appName
            ];
            $dataJson = NPAllFunc::convArrToUTF($dataJson);
            $arJS = [];
            $arJS['ListOfDocs'] = json_encode($dataJson);
            $this->dataJson = $arJS;
        }
    }
}