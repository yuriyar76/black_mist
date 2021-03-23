<?php
require_once __DIR__ . '/../NPAllFunc.php';

/**
 * Class GroupsCallCourier
 */
class GroupsCallCourier
{
    public $content = [];
    private $idClient = 0;
    public $arrClient = [];
    public $uCompId = 0;
    public $uCompSettingsId = 0;
    public $uCSettings = [];
    public $client;
    public $invoiceList;
    public $invoiceListFor1c = [];
    public $mbDetect;
    public $arListForCall = [];
    public $arJsCall = [];
    public $arJsCallDoc = [];
    public $errors = [];
    public $numbers = [];
    public $respSetDocs;
    public $current = false;
    public $apps = [];


    /**
     * GroupsCallCourier constructor.
     * @param array $data
     */
    public function __construct(array $data)
   {
         CModule::IncludeModule("iblock");
         $this->setData($data);
         $this->idClient = $this->content['current_client'];
   }

    /**
     * @param array $data
     * @return $this
     */
    private function setData(array $data)
    {
        $content = [];
        foreach($data as $key=>$value){
            if($key === 'data_json'){
                $content['ids'] = json_decode($value, true);
                if(is_array($content['ids'])){
                    foreach($content['ids'] as $k=>$val){
                        $val = htmlspecialchars(strip_tags(trim($val)));
                        $content['ids'][$k] = iconv('utf-8', 'windows-1251', $val);
                    }
                }
            }else{
                $content[$key] =  iconv('utf-8', 'windows-1251',htmlspecialchars(strip_tags(trim($value))));
            }
         }
        $this->content = $content;
        return $this;
    }



    /**
     * @return $this
     * @throws Exception
     */
    public function currentClient()
    {
        $arrClient = NPAllFunc::GetInfoArr(false, $this->idClient, 40, [
            'ID',
            "NAME",
            "ACTIVE",
            "PROPERTY_INN",
            "PROPERTY_INN_REAL",
            "PROPERTY_UK",
            "PROPERTY_TYPE"
        ]);

        if(empty($arrClient['PROPERTY_UK_VALUE'])){
            $this->errors['err_uk'] = '��� ��, ������ �� ���������';
            throw new Exception('��� ��, ������ �� ���������');
         }

            $this->arrClient = $arrClient;
            $this->uCompId = $this->arrClient['PROPERTY_UK_VALUE'];
            $arrUK = NPAllFunc::GetInfoArr(false, $this->uCompId, 40, [
            'ID',
            "NAME",
            "ACTIVE",
            "PROPERTY_INN",
            "PROPERTY_INN_REAL",
            "PROPERTY_SETTINGS.ID"
        ]);
        $this->uCompSettingsId = $arrUK['PROPERTY_SETTINGS_ID'];

        $arrUKSettings = NPAllFunc::GetInfoArr(false,  $this->uCompSettingsId, 47, [
            'ID',
            "NAME",
            "ACTIVE",
            "PROPERTY_683",
            "PROPERTY_761",
            "PROPERTY_704",
            "PROPERTY_705",
            "PROPERTY_706",
            "PROPERTY_709",

        ]);
        if(!$arrUKSettings['PROPERTY_683_VALUE'] && !$arrUKSettings['PROPERTY_704_VALUE'] &&
           !$arrUKSettings['PROPERTY_705_VALUE'] && !$arrUKSettings['PROPERTY_706_VALUE']){
            $this->errors['err_settings'] = '��� �������� ��, ���������� � 1� ����������';
            throw new Exception('��� �������� ��, ���������� � 1� ����������');
         }
        $this->uCSettings['ipaddr1c'] =  $arrUKSettings['PROPERTY_683_VALUE'];
        $this->uCSettings['port1c'] =  $arrUKSettings['PROPERTY_761_VALUE'];
        $this->uCSettings['url1c'] =  $arrUKSettings['PROPERTY_704_VALUE'];
        $this->uCSettings['login1c'] =  $arrUKSettings['PROPERTY_705_VALUE'];
        $this->uCSettings['pass1c'] =  $arrUKSettings['PROPERTY_706_VALUE'];
        $this->uCSettings['email'] =  $arrUKSettings['PROPERTY_709_VALUE'];

        return $this;

    }

    /**
     * @return $this
     * @throws SoapFault
     */
    public function soapLink(){
            $currentip = $this->uCSettings['ipaddr1c'];
            $currentport = $this->uCSettings['port1c'];
            $currentlink = $this->uCSettings['url1c'];
            $login1c = $this->uCSettings['login1c'];
            $pass1c =  $this->uCSettings['pass1c'];

            // ����� ����������
            if (!$currentip && !$currentlink && !$login1c && !$pass1c){
                $this->errors['err_link'] = '��� ���������� � 1�';
                throw new Exception('��� ���������� � 1�');
            }

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
                    CURLOPT_TIMEOUT => 10
                ]);
                $header = explode("\n", curl_exec($curl));
                curl_close($curl);
                if (!strlen(trim($header[0]))){
                    $this->errors['err_curl'] = '�������� � curl';
                    throw new Exception('�������� � curl');
                }
                if (strlen(trim($header[0])))
                {
                    if ($currentport > 0) {
                        $client = new SoapClient($url, ['login' => $login1c, 'password' => $pass1c, 'proxy_host' => $currentip, 'proxy_port' => $currentport, 'exceptions' => false]);
                    }
                    else {
                        $client = new SoapClient($url, ['login' => $login1c, 'password' => $pass1c,'exceptions' => false]);
                    }
                     $this->client = $client;
                }
                return $this;

    }

    public function invoiceList()
    {
        $arrList = NPAllFunc::GetInfoArr(false,  false, 83,
            [
            'ID',
            "NAME",
            "DATE_CREATE",
            "ACTIVE",
            "PROPERTY_CITY_RECIPIENT.NAME",
            "PROPERTY_CALLING_COURIER",
            "PROPERTY_*",
            ],
            [
            "ID" => $this->content['ids'],
            "IBLOCK_ID" => 83,
            "ACTIVE" => "Y"
            ], false, false);
        $this->invoiceList = $arrList;
        return $this;
    }

    public function prepareListFor1c()
    {
       foreach($this->invoiceList as $key => $invoice) {
           if($invoice['PROPERTY_CALLING_COURIER_VALUE'] === "Y") {
               foreach($this->content['ids'] as $k=>$id){
                   if($id === $invoice['ID']){
                       unset($this->content['ids'][$k]);
                       break;
                   }
               }
               continue;
           }
           $this->numbers[] = $invoice['NAME'];
           $PAYMENT_TYPE = '�';
           switch ((int)$invoice['PROPERTY_563'])
           {
               case 255:
                   $PAYMENT_TYPE = '�';
                   break;
               case 256:
                   $PAYMENT_TYPE = '�';
                   break;
               case 309:
                   $PAYMENT_TYPE = '�';
                   break;
           }
           $DELIVERY_PAYER = '�';
           switch ((int)$invoice['PROPERTY_563'])
           {
               case 251:
                   $DELIVERY_PAYER = '�';
                   break;
               case 252:
                   $DELIVERY_PAYER = '�';
                   break;
               case 253:
                   $DELIVERY_PAYER = '�';
                   break;
           }
           $DELIVERY_CONDITION = '�';
           switch ((int)$invoice['PROPERTY_559'])
           {
               case 248:
                   $DELIVERY_CONDITION = '�';
                   break;
               case 249:
                   $DELIVERY_CONDITION = '�';
                   break;
               case 250:
                   $DELIVERY_CONDITION = '�';
                   break;
           }

           $DELIVERY_TYPE  = "�";
           switch ((int)$invoice['PROPERTY_557'])
           {
               case 345:
                   $DELIVERY_TYPE = '�';
                   break;
               case 346:
                   $DELIVERY_TYPE = '�';
                   break;
               case 338:
                   $DELIVERY_TYPE = '�';
                   break;
               case 243:
                   $DELIVERY_TYPE = '�';
                   break;
               case 244:
                   $DELIVERY_TYPE = '�';
                   break;
               case 245:
                   $DELIVERY_TYPE = '�';
                   break;
               case 308:
                   $DELIVERY_TYPE = '�';
                   break;
           }

           $ID = $invoice['ID'];
           $DATE_CREATE = $invoice['DATE_CREATE'];
           $INN = $this->arrClient['PROPERTY_INN_VALUE'];
           $NAME_SENDER = $invoice['PROPERTY_546'];
           $PHONE_SENDER =  $invoice['PROPERTY_547'];
           $COMPANY_SENDER = NPAllFunc::NewQuotes($invoice['PROPERTY_548']);
           $CITY_SENDER = $invoice['PROPERTY_549'];
           $INDEX_SENDER = $invoice['PROPERTY_550'];
           $INN_SENDER = $invoice['PROPERTY_1078'];
           $ADDRESS_SENDER = $invoice['PROPERTY_551']['TEXT'];
           $ADDRESS_RECIPIENT = $invoice['PROPERTY_571']['TEXT'];
           $NAME_RECIPIENT = $invoice['PROPERTY_552'];
           $PHONE_RECIPIENT = $invoice['PROPERTY_553'];
           $COMPANY_RECIPIENT = NPAllFunc::NewQuotes($invoice['PROPERTY_554']);
           $CITY_RECIPIENT = $invoice['PROPERTY_555'];
           $CITY_RECIPIENT_NON = $invoice['PROPERTY_CITY_RECIPIENT_NAME'];
           $INDEX_RECIPIENT = $invoice['PROPERTY_556'];
           $INN_RECIPIENT = $invoice['PROPERTY_1079'];
           $DATE_TAKE_FROM = $this->frmDate1c('from');
           $DATE_TAKE_TO = $this->frmDate1c('to');
           $TYPE = (int)$invoice['PROPERTY_558'];
           $PAYMENT_AMOUNT = '0';
           $PAYMENT = 0;
           $INSTRUCTIONS = $invoice['PROPERTY_570']['TEXT'] . ' ' .
               $this->content['callcourcomment_ids'];
           $PLACES = (int)$invoice['PROPERTY_567'];
           $WEIGHT = (float)$invoice['PROPERTY_568'];
           $SIZE_1 = 0;
           $SIZE_2 = 0;
           $SIZE_3 = 0;
           $FILES = "";
           $InternalNumber = $invoice['PROPERTY_764'];
           $DocNumber = $invoice['NAME'];
           $TRANSPORT_TYPE = $invoice['PROPERTY_861'];
           $ISOFFICE = $invoice['PROPERTY_988'];
           $CENTER_EXPENSES = $invoice['PROPERTY_981'];

           $arListFor1c[$key] = [
               "ID" => $ID,
               "DATE_CREATE" => $DATE_CREATE,
               "INN" => $INN,
               "NAME_SENDER" => $NAME_SENDER,
               "PHONE_SENDER" => $PHONE_SENDER,
               "COMPANY_SENDER" => $COMPANY_SENDER,
               "CITY_SENDER" => $CITY_SENDER,
               "INDEX_SENDER" => $INDEX_SENDER,
               "ADDRESS_SENDER" => $ADDRESS_SENDER,
               "ADDRESS_RECIPIENT" => $ADDRESS_RECIPIENT,
               "NAME_RECIPIENT" => $NAME_RECIPIENT,
               "PHONE_RECIPIENT" => $PHONE_RECIPIENT,
               "COMPANY_RECIPIENT" => $COMPANY_RECIPIENT,
               "CITY_RECIPIENT" => $CITY_RECIPIENT,
               "CITY_RECIPIENT_NON" => $CITY_RECIPIENT_NON,
               "INDEX_RECIPIENT" => $INDEX_RECIPIENT,
               'DATE_TAKE_FROM' => $DATE_TAKE_FROM,
               'DATE_TAKE_TO' => $DATE_TAKE_TO,
               "TYPE" => $TYPE,
               "DELIVERY_TYPE" => $DELIVERY_TYPE,
               "DELIVERY_PAYER" => $DELIVERY_PAYER,
               "PAYMENT_TYPE" => $PAYMENT_TYPE,
               "DELIVERY_CONDITION" => $DELIVERY_CONDITION,
               "PAYMENT_AMOUNT" => $PAYMENT_AMOUNT,
               "PAYMENT" => $PAYMENT,
               "INSTRUCTIONS" => $INSTRUCTIONS,
               "PLACES" => $PLACES,
               "WEIGHT" => $WEIGHT,
               "SIZE_1" => $SIZE_1,
               "SIZE_2" => $SIZE_2,
               "SIZE_3" => $SIZE_3,
               "FILES" => $FILES,
               "InternalNumber" => $InternalNumber,
               "DocNumber" => $DocNumber,
               "TRANSPORT_TYPE" => $TRANSPORT_TYPE,
               "ISOFFICE" => $ISOFFICE,
               'INN_SENDER' =>  $INN_SENDER,
               'INN_RECIPIENT' =>  $INN_RECIPIENT,
           ];
           $this->invoiceListFor1c[] = $arListFor1c[$key];

           $arListForCall[$key] = [
               'IDWEB' => $ID,
               'INN' => $INN,
               'DATE' => date('Y-m-d'),
               'COMPANY_SENDER' => $COMPANY_SENDER,
               'NAME_SENDER' =>$NAME_SENDER,
               'PHONE_SENDER' => $PHONE_SENDER,
               'ADRESS_SENDER' => $ADDRESS_SENDER,
               'INDEX_SENDER' => $INDEX_SENDER,
               'ID_CITY_SENDER' => $CITY_SENDER,
               'DELIVERY_TYPE' => $DELIVERY_TYPE,
               'PAYMENT_TYPE' => $PAYMENT_TYPE,
               'DELIVERY_PAYER' => $DELIVERY_PAYER,
               'DELIVERY_CONDITION' => $DELIVERY_CONDITION,
               'DATE_TAKE_FROM' => $DATE_TAKE_FROM,
               'DATE_TAKE_TO' => $DATE_TAKE_TO,
               'INSTRUCTIONS' => $INSTRUCTIONS,
               "TRANSPORT_TYPE" => $TRANSPORT_TYPE,

           ];
           $this->arListForCall[] = $arListForCall[$key];

       }
           if( empty($this->invoiceListFor1c) && empty($this->arListForCall)){
               $this->errors['err_invoices'] = '��� ��������� ��� ������';
               throw new Exception('��� ��������� ��� ������');
           }

           return $this;
    }

    protected function frmDate1c($str)
    {
        if($str==='from'){
            $return = date('Y-m-d', (strtotime($this->content['callcourierdate_ids']))) . ' ' .
                $this->content['callcourtime_from_ids'] . ':00';
        }

        if($str==='to'){
            $return =  date('Y-m-d', (strtotime($this->content['callcourierdate_ids']))) . ' ' .
                $this->content['callcourtime_to_ids'] . ':00';
        }

        return $return;
    }

    public function  setCallCourier()
    {
        $arJson =  $this->arListForCall;
        $arJson = NPAllFunc::convArrToUTF($arJson);
        $arJS = [];
        $arJS['ListOfDocs'] = json_encode($arJson);
        $this->arJsCall = $arJS;
        $result = $this->client->SetCallingTheCourier($arJS);
        $mResult = $result->return;
        $obj = json_decode($mResult, true);
        $arRes = NPAllFunc::arrUtfToWin($obj);
        if ($arRes[0]['status'] !== 'true')
        {
            $state_id = 321;
            $state_descr = '���������';
            $this->errors['errCallMess']  = ['state_id' => $state_id, 'message'=>'������� SetCallingTheCourier ' . $state_descr];
            throw new Exception("������ - $state_id, $state_descr");
        }
        return $this;

    }

    public function setDocsClient()
    {
        $arJson =  $this->invoiceListFor1c;
        $arJson = NPAllFunc::convArrToUTF($arJson);
        $arJS = [];
        $arJS['ListOfDocs'] = json_encode($arJson);
        $this->arJsCallDoc = $arJS;
        $result = $this->client->SetDocsListClient($arJS);
        $mResult = $result->return;
        $obj = json_decode($mResult, true);
        if (!($obj['Doc_1']["ID"]) && $obj['Doc_1']["ID"] > 0) {
            $this->errors['errSetDocsCl']  = 'SetDocsListClient. ��� ������ �� 1�';
            throw new Exception('��� ������ �� 1�');
        }
        if(is_array($obj)){
            $apps = [];
            foreach($obj as $k=>$app){
                if($k==='NUMBER'){
                    $apps[] = $app;
                }

            }
            $this->apps = $apps;
        }

        $this->respSetDocs = NPAllFunc::arFromUtfToWin($obj);
        return $this;
    }

    public function checkInvBase()
    {
        if(empty($this->content['ids'])){
            $this->current = false;
            $this->errors['notinvoice']  = '��� ��������� ��� ������.';
            throw new Exception('��� ��������� ��� ������.');

        }
        foreach ($this->content['ids'] as $id){
            CIBlockElement::SetPropertyValuesEx($id, 83,
                [
                    984 => $this->content['callcourierdate_ids'],
                    985 => $this->content['callcourtime_from_ids'],
                    986 => $this->content['callcourtime_to_ids'],
                    977 => 'Y'
                ]);
        }
        $this->current = true;
        return $this;
    }

    public function sendPost()
    {

        $sub = "�������� ����� �������!";
        $list_invoices_ids = implode(',', $this->content['ids']);
        $list_invoices_numbers =  implode(',', $this->numbers);
        $client = $this->arrClient['NAME'] . ' [' . $this->arrClient['ID'] . '] ';
        $inn = $this->arrClient['PROPERTY_INN_VALUE'];
        $email = $this->uCSettings['email'];
        $apps =implode(',', $this->apps);
        $datac = $this->content['callcourierdate_ids'] . ' c ' . $this->content['callcourtime_from_ids'] .
        ' �� ' . $this->content['callcourtime_to_ids'];
        $comments = $this->content['callcourcomment_ids']?:'��� �����������';
        $arEventFields = [
            "SUBJECT"=>$sub,
            "CLIENT"=>$client,
            "IDS"=>$list_invoices_ids,
            "NUMBERS"=>$list_invoices_numbers,
            "INN"=>$inn,
            "UK_EMAIL"=>$email,
           // "APPS" => $apps,
            "DATACALL" => $datac,
            "COMMENTS" => $comments

        ];
        CAllEvent::SendImmediate("NEWPARTNER_LK", "S5", $arEventFields, "N", 298);

        return $this;
    }


}