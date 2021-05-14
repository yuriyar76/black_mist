<?php

include __DIR__ . '/../NPAllFunc.php';
include __DIR__ . '/../IndexComponent.php';

class UpdateInvoice extends IndexComponent
{
    protected $data = [];
    protected $dataFrom;
    protected $dataTo;
    protected $idCompany;
    protected $invoiceForUpdate;
    protected $company;
    protected $innCompany;
    protected $settingsId;
    public $newInvoiceId;
    public $updateInvoiceId;
    public $numberInvoice;
    public $propsE;
    public $eventsInvoiceId;
    public $lastStatusEvent;

    public function __construct($post)
    {
        parent::__construct();
        $this->data = $post;
        $this->valid();
        $this->request();
    }

    protected function valid()
    {
        if(!empty($this->data['number'])){
            $this->numberInvoice = $this->data['number'];
        }
        if($this->data['dateFrom'] && $this->data['date_to'] && $this->data['id_company']){
            if(preg_match('/^([0-9]{2}\.{1}){2}[0-9]{4}$/', $this->data['dateFrom'])){
                $this->dataFrom = NPAllFunc::NewQuotes($this->data['dateFrom']);
            }
            if(preg_match('/^([0-9]{2}\.{1}){2}[0-9]{4}$/', $this->data['date_to'])){
                $this->dataTo = NPAllFunc::NewQuotes($this->data['date_to']);
            }
            if(preg_match('/[0-9]+/', $this->data['id_company'])){
                $this->idCompany = NPAllFunc::NewQuotes($this->data['id_company']);
            }

            if(!($this->dataFrom && $this->dataTo)){
                throw new Exception('Данные не прошли валидацию');
            }
        }
        if($this->idCompany){
            $arrCompany = NPAllFunc::GetInfoArr(false, $this->idCompany, 40, [
                'ID','NAME', 'ACTIVE', 'PROPERTY_INN_REAL', "PROPERTY_SETTINGS.ID"
            ]);
            $this->company = $arrCompany;
            $this->innCompany = $arrCompany['PROPERTY_INN_REAL_VALUE'];
            $this->settingsId = $arrCompany['PROPERTY_SETTINGS_ID'];
        }


    }

    protected function request()
    {
        $settings = $this->settingsId ? : $this->user["USER"]['SETTINGS_ID'];
        $client = NPAllFunc::soapLink($this->user["USER"]['UK_ID'], $settings);
        if(!$this->numberInvoice){
            $arParamsJson = [
                'INN' => $this->innCompany,
                'BranchID' => '',
                'BranchPrefix' =>  '',
                'StartDate' =>  date('Y-m-d',strtotime($this->dataFrom)),
                'EndDate' =>  date('Y-m-d',strtotime($this->dataTo)),
                'NumPage' => 0,
                'DocsToPage' => 100000
            ];

            $result = $client->GetDocsListClient($arParamsJson);
            $mResult = $result->return;
            if(!$mResult) throw new Exception('Нет ответа от 1с');
            $obj = json_decode($mResult, true);
            $obj = NPAllFunc::arrUtfToWin($obj);
            $this->invoiceForUpdate = $obj;
        }
        if($this->numberInvoice){
            $num = iconv('windows-1251','utf-8', $this->numberInvoice);
            $result = $client->GetDocInfo(["NumDoc" => $num]);
            $mResult = $result->return;
            if(!$mResult) throw new Exception('Нет ответа от 1с');
            $obj= json_decode($mResult, true);
            $obj = NPAllFunc::arrUtfToWin($obj);
            $this->invoiceForUpdate = $obj;
        }
    }

    public function update()
    {
        set_time_limit(0);
        foreach( $this->invoiceForUpdate['Docs'] as $value){
            $events = json_encode(NPAllFunc::convArrToUTF(['Events'=>$value['Events']]));
            $Dimensions = json_encode(NPAllFunc::convArrToUTF(['Dimensions'=>$value['Dimensions']]));
            $dateDoc = date('d.m.Y',strtotime($value['DateDoc']));
            $props = [
                760 => $value['NumDoc'], //'90-3006235',
                1099 => $value['NumID'], //'НПНП00001537706',
                1137 => $value['UID'], // uid  bcaecb39-b4a1-11eb-a2ab-000c29cf960f
                1133 => $value['ID'], //59910380,
                1100 => $value['NumRequest'], //'ТСТБ-01552',
                1101 => $value['Manager'], //'Волков Алексей',
                1102 => $value['DateOfCompletion'], //'2020-12-01T00:00:00',
                1103 => $value['CitySender'], //8054,
                1104 => $value['CityRecipient'], //8054,
                1105 => $value['AdressRecipient'], //'Москва, Ангарская, 29, кв.43',
                1106 => $value['CompanySender'], //'ООО «Абсолют Страхование»',
                1107 => $value['CompanyRecipient'], //'Форпост-оценка',
                1108 => $value['NameSender'], //'Потапова ЕЮ',
                1109 => $value['NameRecipient'], //'Анастасия',
                1110 => $dateDoc, //'',
                1111 => $value['Date_Change'], //'2020-12-03T15:08:38',
                1112 => $value['Tarif'], //163,
                1113 => $value['TransitMoscow'], //'',
                1114 => $value['ZakazName'], //'Абсолют Страхование',
                1115 => $value['ZakazId'], //'НП0002306',
                1116 => $value['Delivery_Type'], //'Стандарт',
                1117 => $value['Delivery_Payer'], //'Отправитель',
                1118 => $value['Payment_Type'], //'Безналичные',
                1119 => $value['Delivery_Condition'], //'По адресу',
                1120 => $value['InternalNumber'], //'',
                1121 => $value['CENTER_EXPENSES'], //'ДКС',
                1122 => $value['INN_SENDER'], //'',
                1123 => $value['INN_RECIPIENT'], //'',
                1124 => $value['Delivery_Weight'], //0.1,
                1125 => $value['Delivery_Payment'], //0,
                1126 => $value['Delivery_Act'], //'№ 3751 от 31.12.2020',
                1127 => $value['DateDoc'], //'2020-11-30T17:38:47',
                1128 => $value['Date_Delivered'], //'03.12.2020',
                1129 => $value['Time_Delivered'], //'13:14:27',
                1130 => $value['Signature_Delivered'], //'под роспись',
                1131 => $events,
                1132 => $Dimensions,
                1134 => $this->idCompany,
                1135 => $this->innCompany,
                1136 => $value['FilesPath'],  // scan docs

            ];

             $arrSearch = NPAllFunc::GetInfoArr(false, false, 98, ['ID', 'NAME', 'ACTIVE'],
            ["PROPERTY_1137"=>$value['UID']], true, false);
            if(!empty($arrSearch['ID'])){
                $idEl = (int)$arrSearch['ID'];
                if($arrSearch['NAME'] != $value['NumDoc']){
                    $arLoad = ["NAME"  => $value['NumDoc']];
                    $el = new CIBlockElement;
                    $el->Update($idEl, $arLoad);
                }
                CIBlockElement::SetPropertyValuesEx($idEl, 98, $props);
                $arIdElUpd[] = $idEl;
            }else{
                $elnew = new CIBlockElement;
                $data = [
                    "IBLOCK_ID" => 98,
                    "IBLOCK_SECTION_ID" => false,
                    "NAME" => $value['NumDoc'],
                    "ACTIVE" => "Y"
                ];

                if($idnew = $elnew->add($data)){
                    CIBlockElement::SetPropertyValuesEx($idnew, 98, $props);
                    $this->newInvoiceId[] = $idnew;
                }
            }
        }

    }

    public function updateInvoice()
    {
        $data = $this->invoiceForUpdate;
        $events = json_encode(NPAllFunc::convArrToUTF(['Events'=>$data['События']]));
        $cEvents = count($data['События']);
        $statLastEvent = $data['События']['Event_' . $cEvents]['Event'] . ' ' .
            $data['События']['Event_' . $cEvents]['InfoEvent'] . ' ' .
            $data['События']['Event_' . $cEvents]['DateEvent'];
        $this->lastStatusEvent = iconv('windows-1251', 'utf-8',$statLastEvent);
        $props = [
            760 => $data['НомерНакладной'], //'90-3006235',
            1133 => $data['IDсСайта'], //59910380,
            1100 => $data['НомерЗаявки'], //'ТСТБ-01552',
            1101 => $data['Ответственный'], //'Волков Алексей',
            1111 => $data['ДатаИзменения'], //'2020-12-03T15:08:38',
            1112 => $data['ТарифЗаУслуги'], //163,
            1124 => $data['ВесВходящий'], //0.1,
            1131 => $events,
            1121 => $data['ЦентрЗатрат']
          ];
        $this->propsE = $props;
        $arrSearch = NPAllFunc::GetInfoArr(false, false, 98, ['ID', 'NAME', 'ACTIVE'],
            ["NAME"=>$props[760]], true, false);
        if(!$arrSearch['ID']) throw new Exception('Обновление не удалось, попробуйте позже.');

            $idEl = (int)$arrSearch['ID'];
            CIBlockElement::SetPropertyValuesEx($idEl, 98, $props);
            $this->updateInvoiceId = $idEl;
            $this->eventsInvoiceId = $events;
            $this->numberInvoice = $props[760];
    }

}


/*
         [invoiceForUpdate:protected] => Array
       (
           [ВыборОтправителя] => ООО Абсолют Страхование
           [ФамилияОтправителя] => Голованова ЕА
           [КомпанияОтправителя] => ООО Абсолют Страхование
           [ТелефонОтправителя] => +749502577772432
           [ИндексОтправителя] => 115280
           [СтранаОтправителя] => Россия
           [ОбластьОтправителя] => Москва
           [ГородОтправителя] => Москва
           [АдресОтправителя] => ул. Ленинская Слобода, 26
           [ШиротаОтправителя] => 0
           [ДолготаОтправителя] => 0
           [КакПроехатьОтправителя] => CMC
           [ПримечаниеОтправителя] =>
           [ВыборПолучателя] => ГБУ Хозяйственное управление Администрации края
           [ФамилияПолучателя] => Яковлев ОИ
           [КомпанияПолучателя] => ГБУ Хозяйственное управление Администрации края
           [ТелефонПолучателя] => 89143262698
           [ИндексПолучателя] =>
           [СтранаПолучателя] => Россия
           [ОбластьПолучателя] => Приморский край
           [ГородПолучателя] => Владивосток
           [АдресПолучателя] => Алеутская, 16
           [ШиротаПолучателя] => 0
           [ДолготаПолучателя] => 0
           [КакПроехатьПолучателя] =>
           [ПримечаниеПолучателя] =>
           [ПризнакДокументы] => 1
           [ОписаниеОтправления] =>  Документы
           [ОбъявленнаяСтоимость] =>
           [ТаможеннаяСтоимость] =>
           [ПризнакТипДоставки] => Стандарт
           [СпециальныеУсловия] => По адресу
           [ДеньЧасДоставки] =>
           [ТипОтправления] =>
           [ПризнакПлательщик] => Отправитель
           [ПризнакТипОплаты] => Безналичные
           [СтоимостьУслуги] => 0
           [СпециальныеИнструкции] => ВЫЗОВ КУРЬЕРА: 07.04.2021 с 10:00 до 18:00.
           [РасчетнаяДатаДоставки] => 2021-04-13T00:00:00
           [ЭтоНашЗаказ] =>
           [Ответственный] => Волков Алексей
           [Прозвон] =>
           [ДатаВыполненияЗаявки] => 2021-04-07T00:00:00
           [НомерНакладной] => 90-3505096
           [этоЗаявка] =>
           [Организация] => ООО  "НОВЫЙ ПАРТНЕР"
           [ДоставитьДоЧаса] =>
           [СтраховойТариф] => 0
           [ИтогоКОплате] => 0
           [ИзначальныйID] => ecf0f0fb-9776-11eb-a2a5-000c29cf960f
           [ПришлаЗаявка] =>
           [Комментарий] =>
           [ЗаказАгента] =>
           [НомерДляСайта] => НПНП00001725611
           [ЕстьПроблема] =>
           [НеОтправлятьУведомления] =>
           [SMSОтравлена] =>
           [ТорговыйПредставитель] =>
           [ОплаченоНаличными] =>
           [МестКонсолидации] => 0
           [ЕдиницаИзмеренияМестКонсолидации] =>
           [СуммаКОплате] => 0
           [ХолостыеПробегиПартнеров] => 0
           [ЧейЗаказ] => Абсолют Страхование
           [ОплатаДоставки] => Абсолют Страхование
           [Служебное] =>
           [НеБратьСуммуКОплате] =>
           [ИнформацияДляКурьера] =>
           [НомерДоговора] =>
           [МестнаяДоставка] =>
           [НомерЗаявки] => ТСТБ-05765
           [ВыданоКурьеру] => 1
           [ПодтверждениеГотовностиЗаявкиОтправителем] =>
           [Срочно] =>
           [СтатусЗаявки] =>
           [IDсСайта] => 65387552
           [GUIDССайта] =>
           [НакладнаяЗакрыта] =>
           [ГеографическаяЗона] => 0
           [ДатаСоздания] =>
           [ДатаИзменения] => 2021-05-05T11:05:39
           [гкУлица] =>
           [гкДом] =>
           [СкладСклад] =>
           [Забор] =>
           [ТранзитМосква] =>
           [ДатаТранзита] => 2021-04-08T00:00:00
           [ДатаДоставки] =>
           [ВремяДоставкиС] =>
           [ВремяДоставкиПо] =>
           [ВремяЗабораС] => 0001-01-01T10:00:00
           [ВремяЗабораПо] => 0001-01-01T18:00:00
           [ВремяС] =>
           [ВремяПо] =>
           [SMSинформирование] => 0
           [СуммаНаложенныйПлатеж] => 0
           [ВызовКурьера] =>
           [ВесВходящий] => 0.1
           [ВесВходящийОбъемный] => 0
           [ПринципалНаименование] =>
           [ПринципалИНН] =>
           [АдресЭлПочтыПокупателя] =>
           [АдресЭлПочтыОтправителя] =>
           [ЧейНаложенныйПлатеж] =>
           [ТелефонПокупателяДляЧека] =>
           [МО] =>
           [КодВнешнегоКурьера] =>
           [МО1] =>
           [МО2] =>
           [МО3] =>
           [СпособПеревозки] => Любой
           [Свозвратом] =>
           [ЦентрЗатрат] => ДКС-3
           [ИННотправителя] =>
           [ИННполучателя] =>
           [Дата] => 2021-04-07T11:07:39
           [ЧейЗаказКод] => НП0002306
           [ЧейЗаказПрефикс] => ТСТБ
           [ТарифЗаУслуги] => 450.00
           [Габариты] => Array
               (
                   [Габарит_1] => Array
                       (
                           [Длина] => 0
                           [КоличествоМест] => 1
                           [ВесОтправления] => 0.1
                           [ВесОтправленияОбъемный] => 0.1
                           [Ширина] => 0
                           [Высота] => 0
                           [Габарит] => Документы
                       )

                   [Габарит_2] => Array
                       (
                           [Длина] => 0
                           [КоличествоМест] => 0
                           [ВесОтправления] => 0
                           [ВесОтправленияОбъемный] => 0
                           [Ширина] => 0
                           [Высота] => 0
                           [Габарит] =>
                       )

                   [Габарит_3] => Array
                       (
                           [Длина] => 0
                           [КоличествоМест] => 0
                           [ВесОтправления] => 0
                           [ВесОтправленияОбъемный] => 0
                           [Ширина] => 0
                           [Высота] => 0
                           [Габарит] =>
                       )

                   [Габарит_4] => Array
                       (
                           [Длина] => 0
                           [КоличествоМест] => 0
                           [ВесОтправления] => 0
                           [ВесОтправленияОбъемный] => 0
                           [Ширина] => 0
                           [Высота] => 0
                           [Габарит] =>
                       )

                   [Габарит_5] => Array
                       (
                           [Длина] => 0
                           [КоличествоМест] => 0
                           [ВесОтправления] => 0
                           [ВесОтправленияОбъемный] => 0
                           [Ширина] => 0
                           [Высота] => 0
                           [Габарит] =>
                       )

               )

           [Участники] => Array
               (
                   [ИНН_1] => 7700000001
                   [ИНН_2] => 2543031446
               )

           [События] => Array
               (
                   [Event_1] => Array
                       (
                           [DateEvent] => 07.04.2021
                           [TimeEvent] => 11:09
                           [Event] => Выдано курьеру на маршрут
                           [InfoEvent] =>
                           [INN] => 7700000001
                       )

                   [Event_2] => Array
                       (
                           [DateEvent] => 07.04.2021
                           [TimeEvent] => 17:35
                           [Event] => Оприходовано складом
                           [InfoEvent] => Москва
                           [INN] => 7700000001
                       )

                   [Event_3] => Array
                       (
                           [DateEvent] => 07.04.2021
                           [TimeEvent] => 22:06
                           [Event] => Распределено
                           [InfoEvent] => Владивосток
                           [INN] => 7700000001
                       )

                   [Event_4] => Array
                       (
                           [DateEvent] => 07.04.2021
                           [TimeEvent] => 23:00
                           [Event] => Отправлено в город
                           [InfoEvent] => Владивосток
                           [INN] => 7700000001
                       )

                   [Event_5] => Array
                       (
                           [DateEvent] => 12.04.2021
                           [TimeEvent] => 12:00
                           [Event] => Доставлено
                           [InfoEvent] => Коренчук
                           [INN] => 2543031446
                       )

               )

       )
              * */