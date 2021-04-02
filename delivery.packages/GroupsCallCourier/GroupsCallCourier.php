<?php
require_once __DIR__ . '/../MakeData.php';


/**
 * Class GroupsCallCourier
 */
class GroupsCallCourier extends MakeData
{
    public $arrClient = [];
    public $invoiceList;
    public $invoiceListFor1c = [];
    public $mbDetect;
    public $arListForCall = [];
    public $arJsCall = [];
    public $arJsCallDoc = [];
    public $numbers = [];
    public $respSetDocs;
    public $current = false;
    public $apps = [];
    protected $instructions;
    protected $dataCall = [];

    /**
     * GroupsCallCourier constructor.
     * @param array $data
     */
    public function __construct(array $data)
   {
         parent::__construct($data);

   }

    /**
     * @return $this
     */
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

    /**
     * @return $this
     * @throws Exception
     */
    public function prepareListFor1c()
    {
       foreach($this->invoiceList as $key => $invoice) {
           if( empty($this->invoiceList[$key]['PROPERTY_569'])){
               $this->invoiceList[$key]['PROPERTY_569'] = 0;
           }
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
           $PAYMENT_TYPE = 'Б';
           switch ((int)$invoice['PROPERTY_563'])
           {
               case 255:
                   $PAYMENT_TYPE = 'Н';
                   break;
               case 256:
                   $PAYMENT_TYPE = 'Б';
                   break;
               case 309:
                   $PAYMENT_TYPE = 'К';
                   break;
           }
           $DELIVERY_PAYER = 'О';
           switch ((int)$invoice['PROPERTY_563'])
           {
               case 251:
                   $DELIVERY_PAYER = 'О';
                   break;
               case 252:
                   $DELIVERY_PAYER = 'П';
                   break;
               case 253:
                   $DELIVERY_PAYER = 'Д';
                   break;
           }
           $DELIVERY_CONDITION = 'А';
           switch ((int)$invoice['PROPERTY_559'])
           {
               case 248:
                   $DELIVERY_CONDITION = 'А';
                   break;
               case 249:
                   $DELIVERY_CONDITION = 'Д';
                   break;
               case 250:
                   $DELIVERY_CONDITION = 'Л';
                   break;
           }

           $DELIVERY_TYPE  = "С";
           switch ((int)$invoice['PROPERTY_557'])
           {
               case 345:
                   $DELIVERY_TYPE = 'Э';
                   break;
               case 346:
                   $DELIVERY_TYPE = 'Э';
                   break;
               case 338:
                   $DELIVERY_TYPE = 'Э';
                   break;
               case 243:
                   $DELIVERY_TYPE = 'Э';
                   break;
               case 244:
                   $DELIVERY_TYPE = 'С';
                   break;
               case 245:
                   $DELIVERY_TYPE = 'М';
                   break;
               case 308:
                   $DELIVERY_TYPE = 'Д';
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
           if($this->content['callcourcomment_ids'])
           $INSTRUCTIONS = $invoice['PROPERTY_570']['TEXT'] . ' ' .
               $this->content['callcourcomment_ids'] . ' ' .
               'ВЫЗОВ КУРЬЕРА: ' . 'с ' . $DATE_TAKE_FROM . ' по ' . $DATE_TAKE_TO;
           $DESCRIPTION = $invoice['PROPERTY_1082']['TEXT'];

           if(empty($this->content['callcourcomment_ids']))
               $INSTRUCTIONS = $invoice['PROPERTY_570']['TEXT'] . ' ' .
               'ВЫЗОВ КУРЬЕРА: ' . 'с ' . $DATE_TAKE_FROM . ' по ' . $DATE_TAKE_TO;
           $this->instructions = $INSTRUCTIONS;
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
               'DESCRIPTION' => $DESCRIPTION,
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
               'DESCRIPTION' => $DESCRIPTION,

           ];
           $this->arListForCall[] = $arListForCall[$key];

       }
           if( empty($this->invoiceListFor1c) && empty($this->arListForCall)){
               $this->errors['err_invoices'] = 'Нет накладных для вызова';
               throw new Exception('Нет накладных для вызова');
           }

           return $this;
    }

    /**
     * @param $str
     * @return string
     */
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

    /**
     * @return $this
     * @throws Exception
     */
    public function  setCallCourier()
    {
        $arrR = [];
        foreach ($this->invoiceList as $invoice){
            $arHistory = [['date' => date('d.m.Y H:i:s'), 'status' => 315,
                'status_descr' => 'Оформлена', 'comment' => '']];
            $arHistoryUTF = NPAllFunc::convArrToUTF($arHistory);
            $id_in = GetMaxIDIN(87, 7);
            $el = new CIBlockElement;
            $arLoadProductArray = [
                "IBLOCK_SECTION_ID" => false,
                "IBLOCK_ID" => 87,
                "PROPERTY_VALUES" => [
                    611 => $id_in,
                    612 => $this->arrClient['ID'],
                    664 => '',  // филиал
                    613 => [
                        date('d.m.Y', (strtotime($this->content['callcourierdate_ids']))) . ' ' .
                        $this->content['callcourtime_from_ids'] . ':00',
                        date('d.m.Y', (strtotime($this->content['callcourierdate_ids']))) . ' ' .
                        $this->content['callcourtime_to_ids'] . ':00'
                    ],
                    614 => $invoice['PROPERTY_549'],
                    615 => $invoice['PROPERTY_551']['TEXT'],
                    616 => $invoice['PROPERTY_546'],
                    617 => $invoice['PROPERTY_547'],
                    618 => $invoice['PROPERTY_568'],
                    619 => $invoice['PROPERTY_569'],
                    620 => $this->content['callcourcomment_ids'],
                    712 => '', // email, на который ушла заявка
                    726 => 315,
                    727 => json_encode($arHistoryUTF),
                    771 => $invoice['NAME'],
                    862 => 0
                ],
                "NAME" => 'Вызов курьера № ' . $id_in,
                "ACTIVE" => "Y"
            ];
            $arrR[] = $arLoadProductArray;
            $z_id = $el->Add($arLoadProductArray);
            if($z_id){
                $this->dataCall[] = ['id' => $z_id, 'number' => $id_in];
            }else{
                $this->dataCall[] = ['arr' => $arrR , 'error' => 'Ошибка записи вызова курьера в базу по накл. ' . $invoice['NAME']];
            }
        }
        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
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
            $this->errors['errSetDocsCl']  = 'SetDocsListClient. Нет ответа от 1с';
            throw new Exception('Нет ответа от 1с');
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

    /**
     * @return $this
     * @throws Exception
     */
    public function checkInvBase()
    {
        if(empty($this->content['ids'])){
            $this->current = false;
            $this->errors['notinvoice']  = 'Нет накладных для вызова.';
            throw new Exception('Нет накладных для вызова.');

        }
        foreach ($this->content['ids'] as $id){
            CIBlockElement::SetPropertyValuesEx($id, 83,
                [
                    984 => $this->content['callcourierdate_ids'],
                    985 => $this->content['callcourtime_from_ids'],
                    986 => $this->content['callcourtime_to_ids'],
                    570 => ['VALUE' =>['TYPE' => 'text', 'TEXT' => $this->instructions]],
                    977 => 'Y'
                ]);
        }
        $this->current = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function sendPost()
    {

        $sub = "Массовый вызов курьера!";
        $list_invoices_ids = implode(',', $this->content['ids']);
        $list_invoices_numbers =  implode(',', $this->numbers);
        $client = $this->arrClient['NAME'] . ' [' . $this->arrClient['ID'] . '] ';
        $inn = $this->arrClient['PROPERTY_INN_VALUE'];
        $email = $this->uCSettings['email'];
        $apps =implode(',', $this->apps);
        $datac = $this->content['callcourierdate_ids'] . ' c ' . $this->content['callcourtime_from_ids'] .
        ' до ' . $this->content['callcourtime_to_ids'];
        $comments = $this->content['callcourcomment_ids']?:'Без комментария';
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