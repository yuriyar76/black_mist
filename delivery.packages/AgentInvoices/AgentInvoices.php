<?php
include __DIR__ . '/../NPAllFunc.php';
include __DIR__ . '/../IndexComponent.php';
/*
 * Для работы с 117 инфоблоком  и другими инфоблоками агентов
 * */
class AgentInvoices extends IndexComponent
{
    protected $number;
    public $newNumber;
    protected $appName;
    public $idInvoice;
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
    public $newStatus;
    protected $descStatus;
    protected $dateStatus;
    protected $post;
    protected $commentStatus;
    public $newStatNum = 0;



  public function __construct(array $data, array $get=[])
  {
      parent::__construct();
      $this->get = $get;
      $this->post = $data;
      $this->setData();
  }

  protected function setData(){
      if(empty($this->post)) throw new Exception('Нет данных, операция невозможна.');
      if($this->get['number'] === 'Y' ){
          if(is_array($this->post)){
              foreach($this->post as $key=>$value){
                  if($key === 'number_edit_num'){
                      $this->number = iconv('utf-8', 'windows-1251', htmlspecialchars(strip_tags($value)));
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
      if($this->get['status'] === 'Y' ){
          if(is_array($this->post)){
              foreach($this->post as $key=>$value){
                  if($key === 'number_edit_status'){
                      $this->number = iconv('utf-8', 'windows-1251', htmlspecialchars(strip_tags($value)));
                  }
                  if($key === 'id_edit_status'){
                      $this->idInvoice = iconv('utf-8', 'windows-1251', htmlspecialchars(strip_tags($value)));
                  }
                  if($key === 'id_edit_app_status'){
                      $this->appName = iconv('utf-8', 'windows-1251', htmlspecialchars(strip_tags($value)));
                  }
                  if($key === 'select_status'){
                      if($value === '0'){
                          $this->newStatus = 'Принято у отправителя';
                      }
                      if($value === '1'){
                          $this->newStatus = 'Исключительная ситуация';
                      }
                      if($value === '2'){
                          $this->newStatus = 'Доставлено';
                      }
                      $this->newStatNum = (int)$value;

                  }
                  if($key === 'desc_status'){
                      if($value){
                          $this->descStatus = iconv('utf-8', 'windows-1251', htmlspecialchars(strip_tags($value)));
                      }

                  }
                  if($key === 'selectEx'){
                      if($value){
                          $this->descStatus = iconv('utf-8', 'windows-1251', htmlspecialchars(strip_tags($value)));
                      }

                  }
                  if($key === 'date_status'){
                      $this->dateStatus = iconv('utf-8', 'windows-1251', htmlspecialchars(strip_tags($value)));
                  }
                  if($key === 'comment_status'){
                      $this->commentStatus = iconv('utf-8', 'windows-1251', htmlspecialchars(strip_tags($value)));
                  }
                }
          }
          if(!$this->dateStatus){
              throw new Exception('Не заполнены обязательные поля.');
          }
          if($this->newStatNum === 1){
              if(!$this->descStatus){
                  throw new Exception('Не заполнены обязательные поля.');
              }
          }
          if($this->newStatNum === 2){
              if(!$this->descStatus){
                  throw new Exception('Не заполнены обязательные поля.');
              }
          }
      }

   }

   public function getData()
   {
       if($this->get['number'] === 'Y' || $this->get['status'] === 'Y') {
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
       if($this->get['status'] === 'Y' ) {
           $res = $this->client->SetDDPickup($this->dataJson);
           $mResult = $res->return;
           $mResult = json_decode($mResult, true);
           $this->mResult = NPAllFunc::arrUtfToWin($mResult);
           if (!$mResult['Status']) {
               throw new Exception('Невозможно изменить статус отправления');
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
                $oldNumber =iconv('windows-1251', 'utf-8',$this->number);
                $arJs[$new_date] = $oldNumber;
                $numbersJson = json_encode($arJs);
            }else{
                $new_date = date('d:m:Y H:i:s');
                $oldNumber =iconv('windows-1251', 'utf-8',$this->number);
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
                "NUMBER_OLD" => $this->number,
                "NUMBER_APP" => $this->appName
            ];

        }
        if($this->get['status'] === 'Y' ) {
            $dataJson = [
                'ID_EVENT' => 0,
                'NUMBER' => $this->number,
                "DATE" => $this->dateStatus,
                'EVENT' => $this->newStatus,
                'DESCRIPTION' => $this->descStatus,
                'COMMENT' => $this->commentStatus,
                'INN' => $this->innAgent,
                "UID" => $this->uId,
                "NUMBER_APP" => $this->appName
            ];
        }
        $dataJson = NPAllFunc::convArrToUTF($dataJson);
        $arJS = [];
        $arJS['ListOfDocs'] = json_encode($dataJson);
        $this->dataJson = $arJS;

    }
}

/*
 * <pre>AgentInvoices Object
(
    [number:protected] => 90-3488244
    [newNumber] =>
    [appName:protected] => ДМ/2-02740
    [idInvoice:protected] => 65214107
    [dataApp:protected] => Array
        (
            [ID] => 65214107
            [~ID] => 65214107
            [NAME] => ДМ/2-02740
            [~NAME] => ДМ/2-02740
            [PROPERTY_1023_VALUE] => 90-3488244
            [~PROPERTY_1023_VALUE] => 90-3488244
            [PROPERTY_1023_VALUE_ID] => 65214107:1023
            [~PROPERTY_1023_VALUE_ID] => 65214107:1023
            [PROPERTY_CREATOR_ID] => 7759871
            [~PROPERTY_CREATOR_ID] => 7759871
            [PROPERTY_1024_VALUE] => ДМ/2-02740
            [~PROPERTY_1024_VALUE] => ДМ/2-02740
            [PROPERTY_1024_VALUE_ID] => 65214107:1024
            [~PROPERTY_1024_VALUE_ID] => 65214107:1024
            [PROPERTY_1076_VALUE] => 7801539647
            [~PROPERTY_1076_VALUE] => 7801539647
            [PROPERTY_1076_VALUE_ID] => 65214107:1076
            [~PROPERTY_1076_VALUE_ID] => 65214107:1076
            [PROPERTY_1075_VALUE] => 2197189
            [~PROPERTY_1075_VALUE] => 2197189
            [PROPERTY_1075_VALUE_ID] => 65214107:1075
            [~PROPERTY_1075_VALUE_ID] => 65214107:1075
            [PROPERTY_1056_VALUE] => 975ea6bb-93aa-11eb-a2a1-000c29cf960f
            [~PROPERTY_1056_VALUE] => 975ea6bb-93aa-11eb-a2a1-000c29cf960f
            [PROPERTY_1056_VALUE_ID] => 65214107:1056
            [~PROPERTY_1056_VALUE_ID] => 65214107:1056
            [PROPERTY_1081_VALUE] => {&quot;06:04:2021 11:39:13&quot;:&quot;90-3488244&quot;}
            [~PROPERTY_1081_VALUE] => {"06:04:2021 11:39:13":"90-3488244"}
            [PROPERTY_1081_VALUE_ID] => 65214107:1081
            [~PROPERTY_1081_VALUE_ID] => 65214107:1081
            [PROPERTIES] => Array
                (
                )

        )

    [idAgent:protected] => 7759871
    [idUk:protected] => 2197189
    [uId:protected] => 975ea6bb-93aa-11eb-a2a1-000c29cf960f
    [innAgent:protected] => 7801539647
    [client:protected] => SoapClient Object
        (
            [_login] => DMSUser
            [_password] => 1597534682
            [_exceptions] =>
            [_stream_context] => Resource id #81
            [_soap_version] => 1
            [sdl] => Resource id #83
        )

    [dataJson:protected] => Array
        (
            [ListOfDocs] => {"ID_EVENT":"0","NUMBER":"90-3488244","DATE":"06.04.2021 12:33:00","EVENT":"\u0418\u0441\u043a\u043b\u044e\u0447\u0438\u0442\u0435\u043b\u044c\u043d\u0430\u044f \u0441\u0438\u0442\u0443\u0430\u0446\u0438\u044f","DESCRIPTION":"\u041a\u043e\u043c\u043c\u0435\u043d\u0442\u0430\u0440\u0438\u0439","INN":"7801539647","UID":"975ea6bb-93aa-11eb-a2a1-000c29cf960f","NUMBER_APP":"\u0414\u041c\/2-02740"}
        )

    [get:protected] => Array
        (
            [status] => Y
        )

    [historyNumbers:protected] => {&quot;06:04:2021 11:39:13&quot;:&quot;90-3488244&quot;}
    [mResult:protected] =>
    [newStatus:protected] => Исключительная ситуация
    [descStatus:protected] => Комментарий
    [dateStatus:protected] => 06.04.2021 12:33:00
)
</pre>
 * */