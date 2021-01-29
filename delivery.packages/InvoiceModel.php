<?php


/**
 * Class InvoiceModel
 */
abstract class InvoiceModel
{
    public $arResult = [];
    protected $rec_id;         // id созданной накладной
    protected $arChange = [];
    protected $spec_instr = '';
    protected $arPDF = [];
    protected $CREATOR;
    protected $data_creator;
    protected $inn_creator;
    protected $id_in_cur = 0;
    protected $idIblock = 83;  // инфоблок накладные
    protected const INF_INV = 83; // инфоблок накладные
    protected const ID_GEO = 6;  // инфоблок география
    protected $idlogoprint; // логотип для печати
    protected $uk_id = 2378056;  // новый партнер
    protected const UK_ID = 2378056;  // новый партнер
    protected const INF_CONF = 47;   // инфоблок с настройками
    protected const INF_ORG = 40;   // инфоблок с организациями
    protected const INF_CC = 87; // инфоблок Вызовы курьера
    protected const UK_EMAIL = ''; // email UK
    protected $objsetDocsList;
    protected $delivery_type1;
    protected $payment_type_seq;
    protected $delivery_type;
    protected $payment_type;
    protected $delivery_type_seq;
    protected $delivery_payer_seq;
    protected $delivery_payer;
    protected $delivery_condition_seq;
    protected $delivery_condition;
    protected $number_parent;

    /**
     * @var bool
     */
    protected $z_id;
    protected $date_create;
    /**
     * @var string
     */
    protected  $date_take_from;
    /**
     * @var string
     */
    protected  $date_take_to;
    protected $arrPropsInvoice = [
        544 => 'ID_IN',  // Порядковый номер ID_IN число
        545 => 'CREATOR', // CREATOR
        548 => 'COMPANY_SENDER', // Компания отправителя COMPANY_SENDER
        546 => 'NAME_SENDER', // Фамилия отправителя NAME_SENDER
        547 => 'PHONE_SENDER', // Телефон отправителя PHONE_SENDER
        549 => 'CITY_SENDER', // Город отправителя CITY_SENDER
        550 => 'INDEX_SENDER', // Индекс отправителя INDEX_SENDER
        551 => 'ADRESS_SENDER', // Адрес отправителя ADRESS_SENDER
        554 => 'COMPANY_RECIPIENT', // Компания получателя COMPANY_RECIPIENT
        552 => 'NAME_RECIPIENT', // Фамилия получателя NAME_RECIPIENT
        553 => 'PHONE_RECIPIENT', // Телефон получателя PHONE_RECIPIENT
        555 => 'CITY_RECIPIENT', // Город получателя CITY_RECIPIENT
        556 => 'INDEX_RECIPIENT', // Индекс получателя INDEX_RECIPIENT
        571 => 'ADRESS_RECIPIENT', // Адрес получателя ADRESS_RECIPIENT
        557 => ['TYPE_DELIVERY' => [
            345 => 'Экспресс 2', 346 => 'Экспресс 4', 338 => 'Экспресс 8', 243 => 'Экспресс', 244 => 'Стандарт',
            245 => 'Эконом', 308 => 'Склад-Склад'
        ]], // Тип доставки TYPE_DELIVERY 345 - Экспресс 2; 346 - Экспресс 4; 338 - Экспресс 8; 243 - Экспресс; 244 - Стандарт; 245 - Эконом; 308 - Склад-Склад
        558 => ['TYPE_PACK' => [246 => 'Документы', 247 => 'Не документы']], // Тип отправления TYPE_PACK 246 - Документы; 247 - Не документы
        559 => ['WHO_DELIVERY' => [248 => 'По адресу', 249 => 'До востребования', 250 => 'Лично в руки']], // Доставить WHO_DELIVERY 248 - По адресу; 249 - До востребования; 250 - Лично в руки
        560 => 'IN_DATE_DELIVERY', // Доставить в дату IN_DATE_DELIVERY
        561 => 'IN_TIME_DELIVERY', // Доставить до часа IN_TIME_DELIVERY
        562 => ['TYPE_PAYS' => [251 => 'Отправитель', 252 => 'Получатель', 253 => 'Другой', 254 => 'Служебное']], // Оплачивает TYPE_PAYS 251 - Отправитель; 252 - Получатель; 253 - Другой; 254 - Служебное
        563 => 'PAYS', // Оплачивает PAYS
        564 => ['PAYMENT' => [255 => 'Наличными', 256 => 'По счету', 309 => 'Банковской картой']], // Оплата PAYMENT 255 - Наличными; 256 - По счету; 309 - Банковской картой
        565 => 'FOR_PAYMENT', // К оплате FOR_PAYMENT
        733 => 'PAYMENT_COD', // Сумма наложенного платежа PAYMENT_COD
        566 => 'COST', // Объявленная стоимость COST
        567 => 'PLACES', // Мест PLACES
        568 => 'WEIGHT', // Вес WEIGHT
        787 => 'TOTAL_GABWEIGHT', // Объемный вес TOTAL_GABWEIGHT
        569 => 'DIMENSIONS', // Габариты DIMENSIONS
        682 => 'PACK_DESCRIPTION', // Описание отправления PACK_DESCRIPTION
        724 => 'PACK_GOODS', // Товары PACK_GOODS
        570 => 'INSTRUCTIONS', // Специальные инструкции INSTRUCTIONS
        573 => 'DATE_FOR_DELIVERY', // Дата принятия на доставку DATE_FOR_DELIVERY
        732 => 'USER_FOR_DELIVERY', // Кем принята на доставку USER_FOR_DELIVERY
        572 => ['STATE' => [
            257 => 'Оформлено',
            258 => 'принято',
            270 => 'В офисе до востребования',
            271 => 'Возврат интернет-магазину',
            272 => 'Возврат по просьбе отправителя',
            273 => 'Выдано курьеру на маршрут',
            274 => 'Выдано на областную доставку',
            275 => 'Доставлено',
            276 => 'Исключительная ситуация!',
            277 => 'Оприходовано офисом',
            278 => 'Отправлено в город',
            279 => 'Уничтожено по просьбе заказчика'
        ]],
        665 => 'STATE_DESCR', // Расшифровка статуса STATE_DESCR
        639 => 'AGENT', // Агент AGENT
        640 => 'CONTRACT', // Договор CONTRACT
        641 => 'BRANCH', // Филиал BRANCH
        642 => 'RATE', // Тариф клиента RATE
        646 => 'DATE_CHANGE_1C', // Дата изменения в 1с DATE_CHANGE_1C
        647 => 'TRANSIT_MOSCOW', // Транзит через Москву TRANSIT_MOSCOW
        679 => 'INFORMATION_ON_CREATE', // Информировать о создании INFORMATION_ON_CREATE
        680 => 'INFORMATION_SEND', // Отправлено информирование INFORMATION_SEND
        737 => 'WHOSE_ORDER', // Чей заказ WHOSE_ORDER
        764 => 'INNER_NUMBER_CLAIM', // Внутренний Номер Заявки INNER_NUMBER_CLAIM
        772 => 'TO_DELIVER_BEFORE_DATE', // Доставить до даты TO_DELIVER_BEFORE_DATE
        775 => 'REQUISITION_AND_INVOICE', // Накладная проведена REQUISITION_AND_INVOICE
        779 => 'PATH_TO_SCAN_DOCS', // Скан PATH_TO_SCAN_DOCS
        861 => 'TRANSPORT_TYPE', // Тип доставки TRANSPORT_TYPE
        977 => 'CALLING_COURIER', // Вызов курьера осуществлен CALLING_COURIER
        791 => 'CITY_RECIPIENT_STR', // Город получателя без справочника CITY_RECIPIENT_STR
        978 => 'NOTE_36015676', // Примечание(36015676) NOTE_36015676
        979 => 'SUMM_DEV', // Стоимость доставки SUMM_DEV
        980 => 'WITH_RETURN', // С возвратом WITH_RETURN
        981 => 'CENTER_EXPENSES', // Центр затрат (Абсолют страхование)) CENTER_EXPENSES
        982 => 'RAND', // Идентификатор накладной RAND
        983 => 'NUMBER_WITH_RETURN', // Номер накладной "С возвратом" NUMBER_WITH_RETURN
        984 => 'DATE_CALL_COURIER', // Дата Вызова Курьера
        985 => 'TIME_CALL_COURIER_FROM', // Интервал Вызова Курьера от
        986 => 'TIME_CALL_COURIER_TO', // Интервал Вызова Курьера до
    ];


    /**
     * @var array
     */
    public $arDeliverySequence;

    protected  $POST_TEMPL = [
        220 => [
            'COMPANY_F',
            'AGENT_EMAIL',
            'UK_EMAIL',
            'MESS_ERR_1C',
            'WITH_RETURN',
            'FOR_CACHE',
            'NUMBER',
            'COMPANY',
            'BRANCH',
            'DATE_TIME',
            'CITY',
            'ADRESS',
            'CONTACT',
            'PHONE',
            'WEIGHT',
            'SIZE_1',
            'SIZE_2',
            'SIZE_3',
            'TYPE_PAYS',
            'PAYER',
            'COMMENT',
            'SPEC_INSTR',
        ]
    ];
    protected  $AGENT;
    protected $EMAIL_CALLCOURIER;
    protected $USER_IN_BRANCH;
    protected $CURRENT_BRANCH;
    protected $ADMIN_AGENT;
    protected $UK;
    protected $CURRENT_CLIENT;
    protected $BRANCH_INFO;


    /**
     * InvoiceModel constructor.
     */

    public function __construct()
    {
        CModule::IncludeModule("iblock");
        CModule::IncludeModule("main");
        $this->idlogoprint = $this->GetSettingValue(716);
        $arUser = self::getUser();
        $agent_id = (int)$arUser["UF_COMPANY_RU_POST"];
        $this->AGENT = $AGENT = $this->GetCompany($agent_id);
        if ((int)$arUser["UF_BRANCH"])
        {
            $this->USER_IN_BRANCH = true;
            $this->CURRENT_BRANCH = (int)$arUser["UF_BRANCH"];
        }

        if ($AGENT["PROPERTY_TYPE_ENUM_ID"] == 51)
        {
            $this->ADMIN_AGENT = true;
            $this->UK = $AGENT["ID"];
        }
        else
        {
            $this->UK = $AGENT["PROPERTY_UK_VALUE"];
        }
        if (!$this->ADMIN_AGENT)
        {
            $this->CURRENT_CLIENT = $agent_id;
        }
        else
        {
            if (strlen($_SESSION['CURRENT_CLIENT']))
            {
                $this->CURRENT_CLIENT = $_SESSION['CURRENT_CLIENT'];
            }
            else
            {
                $this->CURRENT_CLIENT = 0;
            }
        }
        $this->BRANCH_INFO = $this->GetBranch($this->CURRENT_BRANCH, $this->CURRENT_CLIENT);
        $this->EMAIL_CALLCOURIER = self::GetSettingValue(709, false, $this->UK);
        if ((is_array($AGENT['PROPERTY_BY_AGENT_VALUE'])) && (count($AGENT['PROPERTY_BY_AGENT_VALUE']) > 0))
        {
            foreach ($AGENT['PROPERTY_BY_AGENT_VALUE'] as $ag)
            {
                $db_props = CIBlockElement::GetProperty(40, $ag, ["sort" => "asc"], ["CODE"=>"EMAIL"]);
                if($ar_props = $db_props->Fetch())
                {
                    if(strlen(trim($ar_props["VALUE"])))
                    {
                        $this->arResult['ADD_AGENT_EMAIL'] .= trim($ar_props["VALUE"]).', ';
                    }
                }
            }
        }
    }

    protected function makeArResult()
    {
        $res = CIBlockElement::GetList(
            ["NAME"=>"DESC"],
            $this->getFilterDef($this->number),
            false,
            false,
            $this->getSelDef());
        while($ob = $res->GetNextElement()) {
            $this->arResult = $ob->GetFields();
        }
        $this->date_take_from = substr($this->arResult['PROPERTY_984'],6,4).'-'.substr($this->arResult['PROPERTY_984'],3,2).'-'.substr($this->arResult['PROPERTY_984'],0,2).' '.$this->arResult['PROPERTY_985'].':00';
        $this->date_take_to = substr($this->arResult['PROPERTY_984'],6,4).'-'.substr($this->arResult['PROPERTY_984'],3,2).'-'.substr($this->arResult['PROPERTY_984'],0,2).' '.$this->arResult['PROPERTY_986'].':00';
    }

    protected  function dataCreator()
    {
        $creator_id = (int)$this->arResult['PROPERTY_545'];
        if (is_numeric($creator_id)) {
            $arSelect = [
                "ID", "PROPERTY_237"
            ];
            $data_creator = self::getInfoArr(false, $creator_id, static::INF_ORG, $arSelect);
            $this->CREATOR = $creator_id;
            $this->data_creator = $data_creator;
            $this->inn_creator = $data_creator['PROPERTY_237_VALUE'];
        }
    }

    protected function makeBaseInvoice()
    {
        global $USER;
        $number = $this->number . '-1';
        foreach ($this->arrPropsInvoice as $key=>$value){
            $this->arChange[$key] = $this->arResult['PROPERTY_' . $key];
        }
        $this->arChange['551'] =  $this->arResult['PROPERTY_551']['TEXT'];
        $this->arChange['571'] =  $this->arResult['PROPERTY_571']['TEXT'];
        $this->arChange['570'] =  $this->arResult['PROPERTY_570']['TEXT'];
        if($this->arResult['PROPERTY_764']) $this->arChange['764'] =  $this->arResult['PROPERTY_764'] . '-1';
        $arLoad = [
            "MODIFIED_BY" => $USER->GetID(),
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID" => self::INF_INV,
            "PROPERTY_VALUES" => $this->arChange,
            "NAME" => $number,
            "ACTIVE" => "Y"
        ];

        //->Add
        $el = new CIBlockElement;
        $this->rec_id = $el->Add($arLoad);
        $arSelect = [
            "ID", "DATE_CREATE"
        ];
        $data_rec = self::getInfoArr(false, $this->rec_id, self::INF_INV, $arSelect);
        $this->date_create = $data_rec['DATE_CREATE'];
        $this->arResult['DATE_CREATE'] = $data_rec['DATE_CREATE'];
        $this->number_parent =  $this->number;
        $this->number = $number;
    }

    protected function makeArrPDF(){
        $arJsonDescr = json_decode($this->arResult['PROPERTY_682'], true);
        $s_sender = self::GetCityTD( $this->arResult['PROPERTY_549'], 'city');
        $o_sender = self::GetCityTD( $this->arResult['PROPERTY_549'], 'region');
        $c_sender = self::GetCityTD( $this->arResult['PROPERTY_549'], 'country');
        $s_recepient = self::GetCityTD( $this->arResult['PROPERTY_555'], 'city');
        $o_recepient = self::GetCityTD( $this->arResult['PROPERTY_555'], 'region');
        $c_recepient = self::GetCityTD( $this->arResult['PROPERTY_555'], 'country');
        $this->arResult['LOGO_PRINT'] = CFile::GetPath($this->idlogoprint);
        $this->arResult['ADRESS_PRINT'] = $this->GetSettingValue(718);
        $this->arPDF['LOGO_PRINT'] = $this->arResult['LOGO_PRINT'];
        $this->arPDF['ADRESS_PRINT'] =  $this->arResult['ADRESS_PRINT'];
        $this->arPDF['REQUEST']['number_nakl'] =  $this->number;
        $this->arPDF['REQUEST']['NAME_SENDER'] = $this->arResult['PROPERTY_546'];
        $this->arPDF['REQUEST']['PHONE_SENDER'] = $this->arResult['PROPERTY_547'];
        $this->arPDF['REQUEST']['TYPE_DELIVERY'] = $this->arResult['PROPERTY_557'];
        $this->arPDF['REQUEST']['TYPE_PAYS'] = $this->arResult['PROPERTY_562'];
        $this->arPDF['REQUEST']['COMPANY_SENDER'] = $this->arResult['PROPERTY_548'];
        $this->arPDF['REQUEST']['c_sender'] = $c_sender;
        $this->arPDF['REQUEST']['o_sender'] = $o_sender;
        $this->arPDF['REQUEST']['WHO_DELIVERY'] = $this->arResult['PROPERTY_559'];
        $this->arPDF['REQUEST']['s_sender'] = $s_sender;
        $this->arPDF['REQUEST']['INDEX_SENDER'] = $this->arResult['PROPERTY_550'];
        $this->arPDF['REQUEST']['PAYMENT'] = $this->arResult['PROPERTY_564'];
        $this->arPDF['REQUEST']['ADRESS_SENDER'] = $this->arResult['PROPERTY_551'];
        $this->arPDF['REQUEST']['NAME_RECIPIENT'] = $this->arResult['PROPERTY_552'];
        $this->arPDF['REQUEST']['PHONE_RECIPIENT'] = $this->arResult['PROPERTY_553'];
        $this->arPDF['REQUEST']['INSTRUCTIONS'] =  $this->arResult['PROPERTY_570'];
        $this->arPDF['REQUEST']['COMPANY_RECIPIENT'] = $this->arResult['PROPERTY_554'];
        $this->arPDF['REQUEST']['c_recepient'] = $c_recepient;
        $this->arPDF['REQUEST']['o_recepient'] = $o_recepient;
        $this->arPDF['REQUEST']['s_recepient'] = $s_recepient;
        $this->arPDF['REQUEST']['INDEX_RECIPIENT'] = $this->arResult['PROPERTY_556'];
        $this->arPDF['REQUEST']['FOR_PAYMENT'] = (float)str_replace(',', '.', $this->arResult['PROPERTY_565']);
        $this->arPDF['REQUEST']['PAYMENT_COD'] = (float)str_replace(',', '.', $this->arResult['PROPERTY_733']);
        $this->arPDF['REQUEST']['COST'] = (float)str_replace(',', '.', $this->arResult['PROPERTY_566']);
        $this->arPDF['REQUEST']['ADRESS_RECIPIENT'] =$this->arResult['PROPERTY_571'];
        $this->arPDF['REQUEST']['total_place'] =  $this->arResult['PROPERTY_567'];
        $this->arPDF['REQUEST']['total_weight'] =  $this->arResult['PROPERTY_568'];
        $this->arPDF['REQUEST']['total_gabweight'] =  $this->arResult['PROPERTY_787'];
        $this->arPDF['REQUEST']['COST2'] = (float)str_replace(',', '.', $this->arResult['PROPERTY_566']);
        $this->arPDF['REQUEST']['gab_1_name'] = iconv('utf-8', 'windows-1251',$arJsonDescr[0]['name']);
        $this->arPDF['REQUEST']['gab_1_place'] = $arJsonDescr[0]['place'];
        $this->arPDF['REQUEST']['gab_1_weight'] = $arJsonDescr[0]['weight'];
        $this->arPDF['REQUEST']['gab_1_sizes'] =$arJsonDescr[0]['size'][0]."x".$arJsonDescr[0]['size'][1]."x".$arJsonDescr[0]['size'][2];
        $this->arPDF['REQUEST']['gab_2_name'] = iconv('utf-8', 'windows-1251',$arJsonDescr[1]['name']);
        $this->arPDF['REQUEST']['gab_2_place'] = $arJsonDescr[1]['place'];
        $this->arPDF['REQUEST']['gab_2_weight'] = $arJsonDescr[1]['weight'];
        $this->arPDF['REQUEST']['gab_2_sizes'] =$arJsonDescr[1]['size'][0]."x".$arJsonDescr[1]['size'][1]."x".$arJsonDescr[1]['size'][2];
        $this->arPDF['REQUEST']['gab_3_name'] = iconv('utf-8', 'windows-1251',$arJsonDescr[2]['name']);
        $this->arPDF['REQUEST']['gab_3_place'] = $arJsonDescr[2]['place'];
        $this->arPDF['REQUEST']['gab_3_weight'] = $arJsonDescr[2]['weight'];
        $this->arPDF['REQUEST']['gab_3_sizes'] =$arJsonDescr[2]['size'][0]."x".$arJsonDescr[2]['size'][1]."x".$arJsonDescr[2]['size'][2];
        $this->arPDF['REQUEST']['gab_4_name'] = iconv('utf-8', 'windows-1251',$arJsonDescr[3]['name']);
        $this->arPDF['REQUEST']['gab_4_place'] = $arJsonDescr[3]['place'];
        $this->arPDF['REQUEST']['gab_4_weight'] = $arJsonDescr[3]['weight'];
        $this->arPDF['REQUEST']['gab_4_sizes'] =$arJsonDescr[3]['size'][0]."x".$arJsonDescr[3]['size'][1]."x".$arJsonDescr[3]['size'][2];
        $this->arPDF['REQUEST']['gab_5_name'] = iconv('utf-8', 'windows-1251',$arJsonDescr[4]['name']);
        $this->arPDF['REQUEST']['gab_5_place'] = $arJsonDescr[4]['place'];
        $this->arPDF['REQUEST']['gab_5_weight'] = $arJsonDescr[4]['weight'];
        $this->arPDF['REQUEST']['gab_5_sizes'] =$arJsonDescr[4]['size'][0]."x".$arJsonDescr[4]['size'][1]."x".$arJsonDescr[4]['size'][2];
        // это массив с нашими описаниями в накладную целиком!
        $this->arPDF['REQUEST']['test'] = 12345;
        // это массив с нашими описаниями в накладную целиком!
        $this->arPDF['REQUEST']['fullArray'] = json_encode($arJsonDescr,JSON_PRETTY_PRINT);
        // посылается один! раз
        $this->arPDF['REQUEST']['deliver_before'] = $this->arResult['PROPERTY_772'];
        // включаем внутренний номер и массив с датой первой в серии накладной*
        $this->arPDF['REQUEST']['number_internal'] = $this->arResult['PROPERTY_764'];
        $this->arPDF['REQUEST']['number_internal_array'] = self::getRootInvoice($this->arResult['PROPERTY_764']);
        // пишем вычисленную дату
        $this->arPDF['REQUEST']['DATE_CREATE'] = $this->arResult['DATE_CREATE'];
        // получим данные курьерской заявки INSTRUCTIONS
        // ********************************************************************
        // передадим время и дату вызова курьера
        $this->arPDF['REQUEST']['IN_DATE_DELIVERY'] = $this->arResult['PROPERTY_560'];
        $this->arPDF['REQUEST']['IN_TIME_DELIVERY'] = $this->arResult['PROPERTY_561'];

    }

    /**
     * @return array
     */
    protected function changeRowsReverse()
    {
        $arChange = [];
        $this->arResult['PROPERTY_554'] = $arChange['554'] = $this->arChange['548'];
        $this->arResult['PROPERTY_552'] = $arChange['552'] = $this->arChange['546'];
        $this->arResult['PROPERTY_553'] = $arChange['553'] = $this->arChange['547'];
        $this->arResult['PROPERTY_555'] = $arChange['555'] = $this->arChange['549'];
        $this->arResult['PROPERTY_556'] = $arChange['556'] = $this->arChange['550'];
        $this->arResult['PROPERTY_571'] = $arChange['571'] = $this->arChange['551'];
        $this->arResult['PROPERTY_548'] = $arChange['548'] = $this->arChange['554'];
        $this->arResult['PROPERTY_546'] = $arChange['546'] = $this->arChange['552'];
        $this->arResult['PROPERTY_547'] = $arChange['547'] = $this->arChange['553'];
        $this->arResult['PROPERTY_549'] = $arChange['549'] = $this->arChange['555'];
        $this->arResult['PROPERTY_550'] = $arChange['550'] = $this->arChange['556'];
        $this->arResult['PROPERTY_551'] = $arChange['551'] = $this->arChange['571'];
        $this->arResult['PROPERTY_560'] = $arChange['560'] = date('d.m.Y', strtotime($this->arChange['560'] . "1 day"));
        $this->arResult['PROPERTY_983'] = $arChange['983'] = $this->arResult['NAME'];
        $this->arResult['PROPERTY_980'] = $arChange['980'] = '';
        $this->arResult['PROPERTY_984'] = $arChange['984'] = date('d.m.Y', strtotime($this->arChange['984'] . "1 day"));

        $this->date_take_from = substr($this->arResult['PROPERTY_984'],6,4).'-'.substr($this->arResult['PROPERTY_984'],3,2).'-'.substr($this->arResult['PROPERTY_984'],0,2).' '.$this->arResult['PROPERTY_985'].':00';
        $this->date_take_to = substr($this->arResult['PROPERTY_984'],6,4).'-'.substr($this->arResult['PROPERTY_984'],3,2).'-'.substr($this->arResult['PROPERTY_984'],0,2).' '.$this->arResult['PROPERTY_986'].':00';

        $this->spec_instr .=  "ВНИМАНИЕ! Это обратный забор при доставке накладной - №" . $this->number_parent;
        if($this->arResult['PROPERTY_560']) $this->spec_instr .=  ' Доставить до даты - ' . $this->arResult['PROPERTY_560'] . '.';

        if( $this->arResult['PROPERTY_561']) $this->spec_instr .= ' Доставить до часа - ' . $this->arResult['PROPERTY_561'] . '.';

        $this->arResult['PROPERTY_570'] = $arChange['570'] = $this->spec_instr;
        return $arChange;
    }
    /**
     * @return $this
     */
    protected function writeCallCourier()
    {
        $this->spec_instr .= ' КОММЕНТАРИЙ КУРЬЕРУ - дата забора: ' .
            $this->arResult['PROPERTY_984'] . ' с: ' . $this->arResult['PROPERTY_985'] . ' до: ' .
            $this->arResult['PROPERTY_986'];
        $this->arResult['PROPERTY_570'] .=  $this->spec_instr;
        global $USER;
        $this->id_in_cur = $id_in_cur = self::GetMaxIDIN(static::INF_CC, 7);
        $arHistory = [['date' => date('d.m.Y H:i:s'), 'status' => 315,
            'status_descr' => 'Оформлена', 'comment' => '']];
        $arHistoryUTF = self::convArrayToUTF2($arHistory);

        $arLoadProductArray = [
            "MODIFIED_BY" => $USER->GetID(),
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID" => self::INF_CC,
            "PROPERTY_VALUES" => [
                611 => $id_in_cur,
                612 => $this->CREATOR,
                664 => '',
                613 => [
                    $this->arResult['PROPERTY_984'] .' ' . $this->arResult['PROPERTY_985'] .':00',
                    $this->arResult['PROPERTY_984'] .' ' . $this->arResult['PROPERTY_986'] .':00'
                ],
                614 => $this->arResult['PROPERTY_549'],
                615 => $this->arResult['PROPERTY_551'],
                616 => $this->arResult['PROPERTY_546'],
                617 => $this->arResult['PROPERTY_547'],
                618 => $this->arResult['PROPERTY_568'],
                619 => $this->arResult['PROPERTY_569'],
                620 => ' Накладная № ' . $this->number,
                712 => $this->EMAIL_CALLCOURIER,
                726 => 315,
                727 => json_encode($arHistoryUTF),
                771 => $this->number,
                862 => $this->arResult['PROPERTY_861'],
            ],
            "NAME" => 'Вызов курьера №' . $id_in_cur,
            "ACTIVE" => "Y"
        ];
        $el = new CIBlockElement;
        $this->z_id =  $el->Add($arLoadProductArray);
        return $this;
    }


    /**
     * @return mixed
     */
    protected  function setCallCourier()
    {
        $ob = $this->getTypeDev();
       /* $artest = $this->arResult;
        $artest1 = self::convArrayToUTF($artest);
        self::AddToLogs('CallingCourierI', ['InvoiceModel.468' => $artest1 ]);*/
        $arJs = [
            'IDWEB' => $this->z_id,
            'INN' =>  $this->inn_creator,
            'DATE' => date('Y-m-d'),
            'COMPANY_SENDER' => $this->arResult['PROPERTY_548'],
            'NAME_SENDER' => $this->arResult['PROPERTY_546'],
            'PHONE_SENDER' => $this->arResult['PROPERTY_547'],
            'ADRESS_SENDER' => $this->arResult['PROPERTY_551'],
            'INDEX_SENDER' => $this->arResult['PROPERTY_550'],
            'ID_CITY_SENDER' => $this->arResult['PROPERTY_549'],
            'DELIVERY_TYPE' => $ob->delivery_type,
            'PAYMENT_TYPE' => $ob->payment_type,
            'DELIVERY_PAYER' => $ob->delivery_payer,
            'DELIVERY_CONDITION' => $ob->delivery_condition,
            'DATE_TAKE_FROM' =>  $this->date_take_from,
            'DATE_TAKE_TO' => $this->date_take_to,
            'INSTRUCTIONS' => ' Накладная № '. $this->number,
            "TRANSPORT_TYPE" => $this->arResult['PROPERTY_861'],
        ];
        $arJs = self::convArrayToUTF2($arJs);
        self::AddToLogs('CallingCourierI', ['InvoiceModel.489' => $arJs ]);
        try {
            $client = self::soap_inc();
            self::AddToLogs('CallingCourierI', ['InvoiceModel.492' => $client ]);
            $result = $client->SetCallingTheCourier(['ListOfDocs' => json_encode($arJs)]);
            $mResult = $result->return;
            self::AddToLogs('CallingCourierI', ['InvoiceModel.495' => $mResult ]);
            $obj = json_decode($mResult, true);
            return $obj;
        } catch (SoapFault $e) {

        }

       return false;
    }


    /**
     * @param $arRes
     * @return $this
     */
    protected function changeStatCallCourier($arRes)
    {
        if ($arRes[0]['status'] == 'true')
        {
            $state_id = 317;
            $state_descr = 'Отправлена';
        }
        else
        {
            $state_id = 321;
            $state_descr = 'Отклонена';
        }
        $arHistory[] = ['date' => date('d.m.Y H:i:s'), 'status' => $state_id,
            'status_descr' => $state_descr, 'comment' => $arRes[0]['comment']];
        $arHistory = self::convArrayToUTF2($arHistory);
        CIBlockElement::SetPropertyValuesEx($this->z_id, 87, ["STATE"=>$state_id,
            "STATE_HISTORY"=>json_encode($arHistory)]);
        return $this;
    }

    /**
     * @return $this;
     */
    public function setDocsList(){
        $ob = $this->getTypeDev();
        $date_create = date('Y-m-d h:i:s', strtotime($this->arResult['DATE_CREATE']));
        $CITY_RECIPIENT_NON = self::GetCityTD( $this->arResult['PROPERTY_555'], 'city');
        $arDeliverySequence = [
            "ID"            => $this->z_id,
            "DATE_CREATE"   => $date_create,
            "INN"           => $this->inn_creator,
            "NAME_SENDER"   => $this->arResult['PROPERTY_546'],
            "PHONE_SENDER"  => $this->arResult['PROPERTY_547'],
            "COMPANY_SENDER"=> $this->arResult['PROPERTY_548'],
            "CITY_SENDER"   => (int)$this->arResult['PROPERTY_549'],
            "INDEX_SENDER"  =>  (int)$this->arResult['PROPERTY_550'],
            "ADDRESS_SENDER" => $this->arResult['PROPERTY_551'],
            "ADDRESS_RECIPIENT"  =>$this->arResult['PROPERTY_571'],
            "NAME_RECIPIENT" => $this->arResult['PROPERTY_552'],
            "PHONE_RECIPIENT"   =>  $this->arResult['PROPERTY_553'],
            "COMPANY_RECIPIENT" =>  $this->arResult['PROPERTY_554'],
            "CITY_RECIPIENT"    =>   (int)$this->arResult['PROPERTY_555'],
            "CITY_RECIPIENT_NON" => $CITY_RECIPIENT_NON,
            "INDEX_RECIPIENT"   =>  (int)$this->arResult['PROPERTY_556'],
            'DATE_TAKE_FROM'    => $this->date_take_from,
            'DATE_TAKE_TO'      => $this->date_take_to,
            "TYPE" => (int) (int)$this->arResult['PROPERTY_558'],
            "DELIVERY_TYPE" => $ob->delivery_type_seq,
            "DELIVERY_PAYER" =>$ob->delivery_payer_seq,
            "PAYMENT_TYPE" =>$ob->payment_type_seq,
            "DELIVERY_CONDITION" =>$ob->delivery_condition_seq,
            "PAYMENT_AMOUNT" =>"0",
            "INSTRUCTIONS" =>  $this->arResult['PROPERTY_570'],
            "PLACES" =>  $this->arResult['PROPERTY_567'],
            "WEIGHT" => $this->arResult['PROPERTY_568'],
            "SIZE_1" => 0,
            "SIZE_2" => 0,
            "SIZE_3" => 0,
            "FILES" =>"",
            "DocNumber" => $this->number,
            "TRANSPORT_TYPE" => (int)$this->arResult['PROPERTY_861'],
        ];
        $this->arDeliverySequence = $arDeliverySequence;
        $arDeliverySequence = self::convArrayToUTF2($arDeliverySequence);
        self::AddToLogs('CallingCourierI', ['InvoiceModel.574' => $arDeliverySequence ]);
        $arParamsJson = [
            'ListOfDocs' => "[".json_encode($arDeliverySequence)."]"
        ];
        try {
            $client = self::soap_inc();
            $result = $client->SetDocsListClient($arParamsJson);
            $mResult = $result->return;
            $this->objsetDocsList = json_decode($mResult, true);
        } catch (SoapFault $e) {
            $this->objsetDocsList = false;
        }
        return $this;
     }


    /**
     * @return $this
     */
    public function getTypeDev()
    {
        $this->payment_type_seq = 'Н';
        $this->payment_type = 'Наличные';
        switch ($this->arResult['PROPERTY_564'])
        {
            case 255:
                $this->payment_type = 'Наличные';
                $this->payment_type_seq = 'Н';
                break;
            case 256:
                $this->payment_type = 'Безналичные';
                $this->payment_type_seq = 'Б';
                break;
        }
        $this->delivery_type_seq  = "С";
        $this->delivery_type = 'Стандарт';
        switch ($this->arResult['PROPERTY_557'])
        {
            case 345:
                $this->delivery_type1 = 'Экспресс 2';
                $this->delivery_type_seq = 'Э';
                break;
            case 346:
                $this->delivery_type1 = 'Экспресс 4';
                $this->delivery_type_seq = 'Э';
                break;
            case 338:
                $this->delivery_type1 = 'Экспресс 8';
                $this->delivery_type_seq = 'Э';
                break;
            case 243:
                $this->delivery_type = 'Экспресс';
                $this->delivery_type_seq = 'Э';
                break;
            case 244:
                $this->delivery_type = 'Стандарт';
                $this->delivery_type_seq = 'С';
                break;
            case 245:
                $this->delivery_type = 'Эконом';
                $this->delivery_type_seq = 'М';
                break;
            case 308:
                $this->delivery_type = 'Склад-Склад';
                $this->delivery_type_seq = 'Д';
                break;
        }
        //****
        $this->delivery_payer_seq = 'О';
        $this->delivery_payer = 'Отправитель';
        switch ($this->arResult['PROPERTY_562'])
        {
            case 251:
                $this->delivery_payer = 'Отправитель';
                $this->delivery_payer_seq = 'О';
                break;
            case 252:
                $this->delivery_payer = 'Получатель';
                $this->delivery_payer_seq = 'П';
                break;
            case 253:
                $this->delivery_payer = 'Другой';
                $this->delivery_payer_seq = 'Д';
                break;
        }
        $this->delivery_condition_seq = 'А';
        $this->delivery_condition = 'ПоАдресу';
        switch ($this->arResult['PROPERTY_559'])
        {
            case 248:
                $this->delivery_condition = 'ПоАдресу';
                $this->delivery_condition_seq = 'А';
                break;
            case 249:
                $this->delivery_condition = 'До востребования';
                $this->delivery_condition_seq = 'Д';
                break;
            case 250:
                $this->delivery_condition = 'ЛичноВРуки';
                $this->delivery_condition_seq = 'Л';
                break;
        }
        return $this;
    }

    /**
     * @param $data
     */
    static public function dump($data){
        echo "<pre>" . print_r($data, true) . "</pre>";
    }
    /**
     * @param $val_id
     * @param int $uk_sets_id
     * @param bool $uk_id
     * @return bool|mixed
     */
    protected function GetSettingValue($val_id, $uk_sets_id = self::UK_ID, $uk_id = false)
    {
        $sets_id = $uk_sets_id;
        if ($uk_id)
        {
            $db_props = CIBlockElement::GetProperty(40, $uk_id, ["sort" => "asc"], ["ID" => 474]);
            if ($ar_props = $db_props->Fetch())
            {
                $sets_id = $ar_props["VALUE"];
            }
        }
        $db_props = CIBlockElement::GetProperty(47, $sets_id, ["sort" => "asc"], ["ID" => $val_id]);
        if ($ar_props = $db_props->Fetch())
        {
            if ($ar_props['PROPERTY_TYPE'] == 'L')
            {
                $rate = $ar_props["VALUE_ENUM"];
            }
            else
            {
                $rate = $ar_props["VALUE"];
            }
        }
        else
        {
            $rate = false;
        }
        return $rate;
    }
    /**
     * @return array
     */
    protected function getSelDef(){

      return  [
            "ID", "ACTIVE", "NAME", "DATE_CREATE",  "PROPERTY_*"
        ];
    }

    /**
     * @param $number
     * @return array
     */
    protected function getFilterDef($number){
        return  [
            "NAME" => $number,
            "IBLOCK_ID" => self::INF_INV,
            "ACTIVE" => "Y"
        ];
    }

    /**
     * @param $city
     * @param string $type
     * @return bool|mixed
     */
    static public function GetCityTD($city, $type = 'city')
    {
        $result = false;
        if ($type == 'city')
        {
           $city = self::getInfoArr(false, $city, static::ID_GEO);
           $result = $city["NAME"];
        }
        elseif ($type == 'region')
        {
            $db_old_groups = CIBlockElement::GetElementGroups($city, true);
            if($ar_group = $db_old_groups->Fetch())
            {
                $result = $ar_group['NAME'];
            }
        }elseif($type == 'country')
        {
            $db_old_groups = CIBlockElement::GetElementGroups($city, true);
            if($ar_group = $db_old_groups->Fetch())
            {
                $res = CIBlockSection::GetByID($ar_group['IBLOCK_SECTION_ID']);
                if($ar_res = $res->GetNext())
                $result =  ucfirst(strtolower($ar_res["SEARCHABLE_CONTENT"]));
            }

        }

        return $result;
    }

    /**
     * @param $r_NAME
     * @return array
     */
    static public function  getRootInvoice($r_NAME){
        $nameWithoutPrefix = preg_replace ("/(.*)-(.*)$/", "$1", $r_NAME);
        $resTv = CIBlockElement::GetList(
            ["id" => "desc"],
            // не name а доп.поле.!
            ["IBLOCK_ID"=>83, "PROPERTY_INNER_NUMBER_CLAIM"=>"%".$nameWithoutPrefix."%"],
            false, false, ["ID", 'NAME', 'PROPERTY_INNER_NUMBER_CLAIM', 'DATE_ACTIVE_FROM' , 'DATE_CREATE']);
        $min = 99999999999999;
        while($obTv = $resTv->GetNextElement()){
            $m = $obTv->GetFields();
            // не name а доп.поле.!
            $minResult = preg_replace ("/(.*)-(.*)$/", "$2", $m['PROPERTY_INNER_NUMBER_CLAIM_VALUE']);
            if ($minResult < $min){
                $min = $minResult;
                $arrResult = [$m['ID'], $m['PROPERTY_INNER_NUMBER_CLAIM_VALUE'], $m['DATE_CREATE']];
            }
        };
        return [$min , $arrResult];
    }
    protected function makeZakazPDF(){
        $arResult = $this->arPDF;
        /* увидим  кол-во записей */
        // перечисляем все виды описаний (любое количество)
        $m     = json_decode($arResult['REQUEST']['fullArray'], true);
        $cnt   = count(json_decode($arResult['REQUEST']['fullArray'], true));
        $cooef = (int)(ceil(($cnt / 5)));
        $PDF_NAME = $arResult['REQUEST']['number_nakl'].".pdf";
        $BE_DIR = $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main", "upload_dir")."/pdf/";
        try {
            $mpdf = new \Mpdf\Mpdf([
                //  'debug' => true,
                //  'allow_output_buffering' => true,
                'table_error_report' => false,
                'allow_html_optional_endtags' => false,
                'ignore_invalid_utf8' => true,
                'mode' => 'utf-8',
            ]);
        } catch (\Mpdf\MpdfException $e) {
        }

        //$mpdf->SetTitle('Накладная № ' . $arResult['REQUEST']['number_nakl']);
        $mpdf->allow_charset_conversion = true;
        $mpdf->charset_in='windows-1251'; /*не забываем про русский*/
        $mpdf->showImageErrors = true;
        $mpdf->list_indent_first_level = 0;
        $company = $arResult['REQUEST']['COMPANY_SENDER'];
        $company1 = $arResult['REQUEST']['COMPANY_RECIPIENT'];
        if($arResult['REQUEST']['gab_1_name']!='')
        {
            $gab_1_name = $arResult['REQUEST']['gab_1_name'];
        }
        if($arResult['REQUEST']['gab_1_place']>0)
        {
            $gab_1_place = $arResult['REQUEST']['gab_1_place'];
        }
        if($arResult['REQUEST']['gab_1_weight']>0){
            $gab_1_weight = $arResult['REQUEST']['gab_1_weight'];
        }
        if($arResult['REQUEST']['gab_1_sizes']!='0x0x0' AND $arResult['REQUEST']['gab_1_sizes']!='x0x0'){
            $gab_1_sizes = $arResult['REQUEST']['gab_1_sizes'];
        }
        if($arResult['REQUEST']['gab_2_name']!='')
        {
            $gab_2_name = $arResult['REQUEST']['gab_2_name'];
        }
        if($arResult['REQUEST']['gab_2_place']>0)
        {
            $gab_2_place = $arResult['REQUEST']['gab_2_place'];
        }
        if($arResult['REQUEST']['gab_2_weight']>0){
            $gab_2_weight = $arResult['REQUEST']['gab_2_weight'];
        }
        if($arResult['REQUEST']['gab_2_sizes']!='0x0x0' AND $arResult['REQUEST']['gab_2_sizes']!='x0x0'){
            $gab_2_sizes = $arResult['REQUEST']['gab_2_sizes'];
        }
        if($arResult['REQUEST']['gab_3_name']!='')
        {
            $gab_3_name = $arResult['REQUEST']['gab_3_name'];
        }
        if($arResult['REQUEST']['gab_3_place']>0)
        {
            $gab_3_place = $arResult['REQUEST']['gab_3_place'];
        }
        if($arResult['REQUEST']['gab_3_weight']>0){
            $gab_3_weight = $arResult['REQUEST']['gab_3_weight'];
        }
        if($arResult['REQUEST']['gab_3_sizes']!='0x0x0' AND $arResult['REQUEST']['gab_3_sizes']!='x0x0'){
            $gab_3_sizes = $arResult['REQUEST']['gab_3_sizes'];
        }
        if($arResult['REQUEST']['gab_4_name']!='')
        {
            $gab_4_name = $arResult['REQUEST']['gab_4_name'];
        }
        if($arResult['REQUEST']['gab_4_place']>0)
        {
            $gab_4_place = $arResult['REQUEST']['gab_4_place'];
        }
        if($arResult['REQUEST']['gab_4_weight']>0){
            $gab_4_weight = $arResult['REQUEST']['gab_4_weight'];
        }
        if($arResult['REQUEST']['gab_4_sizes']!='0x0x0' AND $arResult['REQUEST']['gab_4_sizes']!='x0x0'){
            $gab_4_sizes = $arResult['REQUEST']['gab_4_sizes'];
        }
        if($arResult['REQUEST']['gab_5_name']!='')
        {
            $gab_5_name = $arResult['REQUEST']['gab_5_name'];
        }
        if($arResult['REQUEST']['gab_5_place']>0)
        {
            $gab_5_place = $arResult['REQUEST']['gab_5_place'];
        }
        if($arResult['REQUEST']['gab_5_weight']>0){
            $gab_5_weight = $arResult['REQUEST']['gab_5_weight'];
        }
        if($arResult['REQUEST']['gab_5_sizes']!='0x0x0' AND $arResult['REQUEST']['gab_5_sizes']!='x0x0'){
            $gab_5_sizes = $arResult['REQUEST']['gab_5_sizes'];
        }

        $html = '';
        for ($i = 0; $i <= 1; $i++){
            $html .= '<style>
@media print {
html, body {
    border: 1px solid white;
    height: 99%;
    page-break-after: avoid;
    page-break-before: avoid;
}
 }
.label {
color: #536ac2;
font-size: 7pt;
display: block;
}
.value {
font-size: 12pt;
font-weight: bold;
display: block;
}
td{padding:5px;vertical-align: top;border: 1px solid #333333}
table{font-family: Arial, Helvetica, sans-serif;}
</style>
<div class="print_block">
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td style="border:0;vertical-align:middle;" width="286"><p style="font-size: 28px;font-weight: bold;text-align: left;">'.$arResult['REQUEST']['number_nakl'].'</p></td>
            <td style="border:0;vertical-align:middle" align="center">
                <barcode code="'.$arResult['REQUEST']['number_nakl'].'" type="C39" />
            </td>
            <td style="border:0;vertical-align:middle;" width="286">
                <p><img width="286" style="float:right" height="66" alt="" src="'.$_SERVER['DOCUMENT_ROOT'].$arResult['LOGO_PRINT'].'"></p>
                <p style="font-size: 7pt;">'.$arResult['ADRESS_PRINT'].'</p></td>
        </tr>
        </table>
        <table cellpadding="0" cellspacing="0" border="1" bordercolor="#333333">
            <tbody>
                <tr>
                    <td rowspan="5" width="30" bgcolor="#f4ecc5" style="vertical-align:middle;">
                        <div style="width:30px; height:200px;">
                            <img width="30" height="200" alt="Отправитель" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l1.png">
                        </div>
                    </td>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <p class="label">Фамилия Отправителя / Shipper`s Last Name</p>
                            <p class="value">'.$arResult['REQUEST']['NAME_SENDER'].'</p>
                        </div>
                    </td>
                    <td width="250" rowspan="2">
                        <div style="width:250px; height:80px;">
                            <p class="label">Телефон / Phone</p>
                            <p class="value">'.$arResult['REQUEST']['PHONE_SENDER'].'</p>
                        </div>
                    </td>
                    <td rowspan="5" width="30" bgcolor="#d6ffcc" style="vertical-align:middle;">
                        <div style="width:30px; height:200px;">
                            <img width="30" height="200" alt="Условия доставки" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l3.png">
                        </div>
                    </td>
                    <td width="220" rowspan="2">
                        <div style="width:220px; height:80px;">
                            <p class="label">&nbsp;</p>
                            <p class="value">'.$arResult['REQUEST']['TYPE_DELIVERY'].'</p>
                        </div>
                    </td>
                    <td rowspan="5" width="30" bgcolor="#d6ffcc" style="vertical-align:middle;">
                        <div style="width:30px; height:200px;">
                            <img width="30" height="200" alt="Условия оплаты" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l4.png">
                        </div>
                    </td>
                    <td width="142" rowspan="3">
                        <div style="width:142px; height:120px;">
                            <p class="label">Оплачивает</p>
                            <p class="value">'.$arResult['REQUEST']['TYPE_PAYS'].'</p>
                            <p class="value"></p>
                        </div>
                    </td>
                    <tr>
                        <td width="380">
                            <div style="width:380px; height:40px;">
                                <p class="label">Компания-Отправитель / Shipping Company</p>
                                <p class="value" style="font-size: 11pt; line-height: 0.85;">'.$company.'</p>
                            </div>
                        </td>
                        <tr>
                            <td width="380">
                                <div style="width:380px; height:40px;">
                                    <p class="label">Страна / Country</p>
                                    <p class="value">'.$arResult['REQUEST']['c_sender'].'</p>
                                </div>
                            </td>
                            <td width="220">
                                <div style="width:220px; height:40px;">
                                    <p class="label">Область / State</p>
                                    <p class="value">'.$arResult['REQUEST']['o_sender'].'</p>
                                </div>
                            </td>
                            <td width="220" rowspan="3">
                                <div style="width:220px; height:120px;">
                                    <p class="label">Доставить</p>
                                    <p class="value">'.$arResult['REQUEST']['WHO_DELIVERY'].'</p>
                                    <p class="label">Доставить в дату</p>
                                    <p class="value"></p>
                                    <p class="label">Доставить до часа</p>
                                    <p class="value"></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="380">
                                <div style="width:380px; height:40px;">
                                    <p class="label">Город / Sity</p>
                                    <p class="value">'.$arResult['REQUEST']['s_sender'].'</p>
                                </div>
                            </td>
                            <td width="220">
                                <div style="width:220px; height:40px;">
                                    <p class="label">Индекс / Postal Code</p>
                                    <p class="value">'.$arResult['REQUEST']['INDEX_SENDER'].'</p>
                                </div>
                            </td>
                            <td width="142" rowspan="2">
                                <div style="width:142px; height:80px;">
                                    <p class="label">Оплата</p>
                                    <p class="value">'.$arResult['REQUEST']['PAYMENT'].'</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="600" colspan="2">
                                <div style="width:600px; height:40px;">
                                    <p class="label">Адрес / Street Address</p>
                                    <p class="value" style="font-size: 11pt; line-height: 0.85;">'.$arResult['REQUEST']['ADRESS_SENDER'].'</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table width="100%" cellpadding="0" cellspacing="0" border="1" bordercolor="#333333" style="margin-top:-1px;">
                    <tbody>
                        <tr>
                            <td rowspan="5" width="30" bgcolor="#f4ecc5" style="vertical-align:middle;">
                                <div style="width:30px; height:200px;">
                                    <img width="30" height="200" alt="Получатель" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l2.png">
                                </div>
                            </td>
                            <td width="380">
                                <div style="width:380px; height:40px;">
                                    <p class="label">Фамилия Получателя / Consignee`s Last Name</p>
                                    <p class="value">'.$arResult['REQUEST']['NAME_RECIPIENT'].'</p>
                                </div>
                            </td>
                            <td width="220" rowspan="2">
                                <div style="width:220px; height:80px;">
                                    <p class="label">Телефон / Phone</p>
                                    <p class="value">'.$arResult['REQUEST']['PHONE_RECIPIENT'].'</p>
                                </div>
                            </td>
                            <td colspan="3" rowspan="3" width="425">
                                <div style="width:425px; height:120px;">
                                    <p class="label">СПЕЦИАЛЬНЫЕ ИНСТРУКЦИИ / SPECIAL INSTRUCTIONS</p>
                                    <p class="value">'.$arResult['REQUEST']['INSTRUCTIONS'].'</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="380">
                                <div style="width:380px; height:40px;">
                                    <p class="label">Компания-Получатель / Consignee Company</p>
                                    <p class="value" style="font-size: 11pt; line-height: 0.85;">'.$company1.'</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="380">
                                <div style="width:380px; height:40px;">
                                    <p class="label">Страна / Country</p>
                                    <p class="value">'.$arResult['REQUEST']['c_recepient'].'</p>
                                </div>
                            </td>
                            <td width="220">
                                <div style="width:220px; height:40px;">
                                    <p class="label">Область / State</p>
                                    <p class="value">'.$arResult['REQUEST']['o_recepient'].'</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="380">
                                <div style="width:380px; height:40px;">
                                    <p class="label">Город / Sity</p>
                                    <p class="value">'.$arResult['REQUEST']['s_recepient'].'</p>
                                </div>
                            </td>
                            <td width="220">
                                <div style="width:220px; height:40px;">
                                    <p class="label">Индекс / Postal Code</p>
                                    <p class="value">'.$arResult['REQUEST']['INDEX_RECIPIENT'].'</p>
                                </div>
                            </td>
                            <td width="140">
                                <div style="width:140px; height:40px;">
                                    <p class="label">Тариф за услуги</p>
                                    <p class="value">'.$arResult['REQUEST']['FOR_PAYMENT'].'</p>
                                </div>
                            </td>
                            <td width="140">
                                <div style="width:140px; height:40px;">
                                    <p class="label">Страховой тариф</p>
                                    <p class="value">'.$arResult['REQUEST']['PAYMENT_COD'].'</p>
                                </div>
                            </td>
                            <td width="140">
                                <div style="width:140px; height:40px;">
                                    <p class="label">Итого к оплате</p>
                                    <p class="value">'.$arResult['REQUEST']['COST'].'</p>
                                </div>
                            </td>
                        </tr>
                        <tr><td width="600" colspan="2">
                            <div style="width:600px; height:40px;">
                                <p class="label">Адрес / Street Address</p>
                                <p class="value" style="font-size: 11pt; line-height: 0.85;">'.$arResult['REQUEST']['ADRESS_RECIPIENT'].'</p>
                            </div>
                        </td>
                        <td colspan="3" width="425"><div style="width:425px; height:40px;"><p class="label">Фамилия и подпись отправителя / Shippers Signature</p><p class="value" style="font-size: 10pt;line-height: 0.95;padding-right: 160px;">'.$arResult[REQUEST][NAME_SENDER].'</p></div></td>
                    </tr>
                </tbody>
            </table>
            <table width="100%" cellpadding="0" cellspacing="0" border="1" bordercolor="#333333" style="margin-top:-1px;">
                <tbody>
                    <tr>
                        <td rowspan="7" width="30" bgcolor="#ccffff" valign="middle" style="vertical-align:middle;">
                            <div style="height:182px; width:30px;">
                                <img width="30" height="182" alt="Описание отправления" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l5.png">
                            </div>
                        </td>
                        <td colspan="3" width="298">
                            <div style="width:298px; height:15px;">
                            </div>
                        </td>
                        <td width="80" align="center">
                            <div style="width:80px; height:15px;">
                                <p class="label">Мест<br>Pieces</p>
                            </div>
                        </td>
                        <td width="80" align="center">
                            <div style="width:80px; height:15px;">
                                <p class="label">Вес<br>Weight</p>
                            </div>
                        </td>
                        <td width="140" align="center">
                            <div style="width:140px; height:21px;">
                                <p class="label">Габариты (см х см х см)<br>Dimensions (cm x cm x cm)</p>
                            </div>
                        </td>
                        <td colspan="2" rowspan="3" width="425"><div style="width:425px; height:65px;"><p class="label">Принято курьером</p></div></td>
                    </tr>
                    <tr>
                        <td colspan="3"><div style="width:298px; height:10px;"><p class="value" style="font-size:7pt;">'.$gab_1_name.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value">'.$gab_1_place.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value">'.$gab_1_weight.'</p></div></td>
                        <td><div style="width:140px; height:10px;"><p class="value">'.$gab_1_sizes.'</p></div></td>
                    </tr>
                    <tr>
                        <td colspan="3"><div style="width:298px; height:10px;"><p class="value" style="font-size:7pt;">'.$gab_2_name.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value">'.$gab_2_place.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value">'.$gab_2_weight.'</p></div></td>
                        <td><div style="width:140px; height:10px;"><p class="value">'.$gab_2_sizes.'</p></div></td>
                    </tr>
                    <tr>
                        <td colspan="3"><div style="width:298px; height:10px;"><p class="value" style="font-size:7pt;">'.$gab_3_name.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value">'.$gab_3_place.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value">'.$gab_3_weight.'</p></div></td>
                        <td><div style="width:140px; height:10px;"><p class="value">'.$gab_3_sizes.'</p></div></td>
                        <td rowspan="3" width="212"><div style="width:212px; height:60px;"><p class="label">ДОЛЖНОСТЬ</p></div></td>
                        <td rowspan="3" width="212"><div style="width:212px; height:60px;"><p class="label">ФАМИЛИЯ ПОЛУЧАТЕЛЯ</p></div></td>
                    </tr>
                    <tr>
                        <td colspan="3"><div style="width:298px; height:10px;"><p class="value" style="font-size:7pt;">'.$gab_4_name.'</p></div></td>
                        <td><div style="width:80px; height:20px;"><p class="value">'.$gab_4_place.'</p></div></td>
                        <td><div style="width:80px; height:20px;"><p class="value">'.$gab_4_weight.'</p></div></td>
                        <td><div style="width:140px; height:20px;"><p class="value">'.$gab_4_sizes.'</p></div></td>
                    </tr>
                    <tr>
                        <td colspan="3"><div style="width:298px; height:10px;"><p class="value" style="font-size:7pt;">'.$gab_5_name.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value">'.$gab_5_place.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value">'.$gab_5_weight.'</p></div></td>
                        <td><div style="width:140px; height:10px;"><p class="value">'.$gab_5_sizes.'</p></div></td>
                    </tr>
                    <tr>
                        <td width="98">
                            <div style="height:50px; width:98px;">
                                <p class="label">Мест<br>Pieses</p>
                                <p class="value">'.$arResult['REQUEST']['total_place'].'</p>
                            </div>
                        </td>
                        <td width="98">
                            <div style="height:50px; width:98px">
                                <p class="label">Вес<br>Weight</p>
                                <p class="value">'.$arResult['REQUEST']['total_weight'].'</p>
                            </div>
                        </td>
                        <td width="98">
                            <div style="height:50px; width:98px;"><p class="label">Объемный вес<br>Vol. WT</p>
                            <p class="value">'.$arResult['REQUEST']['total_gabweight'].'</p>
                        </div>
                    </td>
                    <td colspan="2"><div style="height:50px;"><p class="label">Контр. взвеш.<br>Control WT</p></div></td>
                    <td>
                        <div style="height:50px;"><p class="label">Объявл. стоимость<br>Declared Value</p>
                        <p class="value">'.$arResult['REQUEST']['COST2'].'</p>
                    </div>
                </td>
                <td><div style="height:50px;"><p class="label">ПОДПИСЬ ПОЛУЧАТЕЛЯ</p></div></td>
                <td><div style="height:50px;"><p class="label">ДАТА И ВРЕМЯ ДОСТАВКИ</p></div></td>
            </tr>
        </tbody>
    </table>
</div><p style="line-height:20px"></p>';
//if ($i!=1) {$html .= '<pagebreak>';}
        }

        try {
            $mpdf->WriteHTML($html);
        } catch (\Mpdf\MpdfException $e) {
        }
        try {
            $mpdf->Output($BE_DIR . $PDF_NAME, "F");
        } catch (\Mpdf\MpdfException $e) {
        }

    }

    /**
     * @param string $code
     * @param string $id
     * @param int $iblock_id
     * @param array $arSelect
     * @param array $arFilter
     * @param bool $flag
     * @param bool $prop
     * @return mixed
     */
    static public function getInfoArr($code = '', $id = '', $iblock_id = 0, $arSelect = [], $arFilter = [], $flag=true, $prop=true){
        if($code){
            $arFilter = [
                "CODE" => $code,
                "IBLOCK_ID" => $iblock_id,
                "ACTIVE" => "Y"
            ];
        }elseif($id){
            $arFilter = [
                "ID" => $id,
                "IBLOCK_ID" => $iblock_id,
                "ACTIVE" => "Y"
            ];
        }elseif(empty($arFilter['IBLOCK_ID']) && $iblock_id){
            $arFilter["IBLOCK_ID"] = $iblock_id;
        }
        if(empty($arSelect)){
            $arSelect = [
                "ID", "NAME", "IBLOCK_ID", "DATE_CREATE", "PROPERTY_*"
            ];
        }
        $res = CIBlockElement::GetList(
            ["NAME"=>"ASC"],
            $arFilter,
            false,
            false,
            $arSelect);
        if($flag){
            while($ob = $res->GetNextElement()) {
                $arr = $ob->GetFields();
                if($prop){
                    $arr['PROPERTIES'] = $ob->GetProperties();
                }
            }
        }else{
            $cnt = 0;
            while($ob = $res->GetNextElement()) {
                $arr[$cnt] = $ob->GetFields();
                if($prop) {
                    $arr[$cnt]['PROPERTIES'] = $ob->GetProperties();
                }
                $cnt++;
            }
        }
        if(!empty($arr['IBLOCK_SECTION_ID'])){
            $res_0 = CIBlockSection::GetList(
                ["SORT"=>"ASC"],
                ["ID"=> (int)$arr['IBLOCK_SECTION_ID'],
                    "IBLOCK_ID"=> $iblock_id],
                false
            );
            while($res_0_from = $res_0->GetNext())
            {
                $arr['SECTION_NAME'] = $res_0_from['NAME'];
            }
        }

        return $arr;
    }

    /**
     * @return SoapClient|string
     * @throws SoapFault
     */
    static public function soap_inc()
    {
        $id_uk = static::UK_ID;
        $res = CIBlockElement::GetList([], array("IBLOCK_ID" => static::INF_CONF, "ID" => $id_uk),
            false, false, array("PROPERTY_683", "PROPERTY_704", "PROPERTY_705", "PROPERTY_706"));
        if ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $currentip = $arFields['PROPERTY_683_VALUE'];
            $currentlink = $arFields['PROPERTY_704_VALUE'];
            $login1c = $arFields['PROPERTY_705_VALUE'];
            $pass1c = $arFields['PROPERTY_706_VALUE'];
            if ((trim($currentip) !== '') && (trim($currentlink) !== '') && (trim($login1c) !== '') &&
                (trim($pass1c) !== '')) {
                $url = "http://" . $currentip . $currentlink;
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_HEADER => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_NOBODY => true,
                    CURLOPT_TIMEOUT => 10
                ));
                $header = explode("\n", curl_exec($curl));
                curl_close($curl);
                if (trim($header[0]) !== ''){
                    $clientw = new SoapClient($url, array("login" => $login1c, "password" => $pass1c,
                        "exceptions" => false));
                    return $clientw;
                }else{

                    return "Нет соединения";
                }
            }
        }
        return null;
    }
    /**
     *
     * @param $obj
     * @return mixed
     */
    static public function arFromUtfToWin(&$obj)
    {
        foreach ($obj as &$item) {
            if (is_array($item)) {
                self::arFromUtfToWin($item);
            } else {
                $item = iconv('utf-8', 'windows-1251', htmlspecialchars($item));
            }
        }
        return $obj;
    }
    /**
     * @param $obj
     * @return mixed
     */
    static public function convArrayToUTF($obj) {
        foreach ($obj as &$item) {
            if (is_array($item)) {
                self::convArrayToUTF($item);
            } else {
                $item = iconv('windows-1251', 'utf-8', htmlspecialchars($item));
            }
        }
        return $obj;

    }

    /**
     * @param $obj
     * @return mixed
     */
    static public function convArrayToUTF1($obj) {
        array_walk_recursive($obj, function(&$item){
            $item = iconv('windows-1251', 'utf-8', htmlspecialchars($item));
        });
        return $obj;
    }

    /**
     * @param $obj
     * @return array
     */
    static function convArrayToUTF2($obj) {
        $arRes = array();
        foreach ($obj as $k => $v)
        {
            $k_tr = iconv('windows-1251', 'utf-8', $k);
            if (is_array($v))
            {
                foreach ($v as $kk => $vv)
                {
                    $kk_tr = iconv('windows-1251', 'utf-8', $kk);
                    if (is_array($vv))
                    {
                        foreach ($vv as $kkk => $vvv)
                        {
                            $kkk_tr = iconv('windows-1251', 'utf-8', $kkk);
                            if (is_array($vvv))
                            {
                                foreach ($vvv as $kkkk => $vvvv)
                                {
                                    $kkkk_tr = iconv('windows-1251', 'utf-8', $kkkk);
                                    if (is_array($vvvv))
                                    {
                                        foreach ($vvvv as $kkkkk => $vvvvv)
                                        {
                                            $kkkkk_tr = iconv('windows-1251', 'utf-8', $kkkkk);
                                            if (is_array($vvvvv))
                                            {
                                                foreach ($vvvvv as $kkkkkk => $vvvvvv)
                                                {
                                                    $kkkkkk_tr = iconv('windows-1251', 'utf-8', $kkkkkk);
                                                    if (is_array($vvvvvv))
                                                    {
                                                        foreach ($vvvvvv as $kkkkkkk => $vvvvvvv)
                                                        {
                                                            $kkkkkkk_tr = iconv('windows-1251', 'utf-8', $kkkkkkk);
                                                            $arRes[$k_tr][$kk_tr][$kkk_tr][$kkkk_tr][$kkkkk_tr][$kkkkkk_tr][$kkkkkkk_tr] = iconv('windows-1251', 'utf-8', $vvvvvvv);
                                                        }
                                                    }
                                                    else
                                                    {
                                                        $arRes[$k_tr][$kk_tr][$kkk_tr][$kkkk_tr][$kkkkk_tr][$kkkkkk_tr] = iconv('windows-1251', 'utf-8', $vvvvvv);
                                                    }
                                                }
                                            }
                                            else
                                            {
                                                $arRes[$k_tr][$kk_tr][$kkk_tr][$kkkk_tr][$kkkkk_tr] = iconv('windows-1251', 'utf-8', $vvvvv);
                                            }
                                        }
                                    }
                                    else
                                    {
                                        $arRes[$k_tr][$kk_tr][$kkk_tr][$kkkk_tr] = iconv('windows-1251', 'utf-8', $vvvv);
                                    }
                                }
                            }
                            else
                            {
                                $arRes[$k_tr][$kk_tr][$kkk_tr] = iconv('windows-1251', 'utf-8', $vvv);
                            }
                        }
                    }
                    else
                    {
                        $arRes[$k_tr][$kk_tr] = iconv('windows-1251', 'utf-8', $vv);
                    }
                }
            }
            else
            {
                $arRes[$k_tr] = iconv('windows-1251', 'utf-8', $v);
            }
        }
        return $arRes;
    }

    /**
     * @param $obj
     * @return array
     */
    static function arFromUtfToWin2($obj)
    {
        $arRes = [];
        foreach ($obj as $k => $v)
        {
            $k_tr = iconv('utf-8', 'windows-1251', $k);
            if (is_array($v))
            {
                foreach ($v as $kk => $vv)
                {
                    $kk_tr = iconv('utf-8', 'windows-1251', $kk);
                    if (is_array($vv))
                    {
                        foreach ($vv as $kkk => $vvv)
                        {
                            $kkk_tr = iconv('utf-8', 'windows-1251', $kkk);
                            if (is_array($vvv))
                            {
                                foreach ($vvv as $kkkk => $vvvv)
                                {
                                    $kkkk_tr = iconv('utf-8', 'windows-1251', $kkkk);
                                    if (is_array($vvvv))
                                    {
                                        foreach ($vvvv as $kkkkk => $vvvvv)
                                        {
                                            $kkkkk_tr = iconv('utf-8', 'windows-1251', $kkkkk);
                                            if (is_array($vvvvv))
                                            {
                                                foreach ($vvvvv as $kkkkkk => $vvvvvv)
                                                {
                                                    $kkkkkk_tr = iconv('utf-8', 'windows-1251', $kkkkkk);
                                                    if (is_array($vvvvvv))
                                                    {
                                                        foreach ($vvvvvv as $kkkkkkk => $vvvvvvv)
                                                        {
                                                            $kkkkkkk_tr = iconv('utf-8', 'windows-1251', $kkkkkkk);
                                                            $arRes[$k_tr][$kk_tr][$kkk_tr][$kkkk_tr][$kkkkk_tr][$kkkkkk_tr][$kkkkkkk_tr] = iconv('utf-8', 'windows-1251', $vvvvvvv);
                                                        }
                                                    }
                                                    else
                                                    {
                                                        $arRes[$k_tr][$kk_tr][$kkk_tr][$kkkk_tr][$kkkkk_tr][$kkkkkk_tr] = iconv('utf-8', 'windows-1251', $vvvvvv);
                                                    }
                                                }
                                            }
                                            else
                                            {
                                                $arRes[$k_tr][$kk_tr][$kkk_tr][$kkkk_tr][$kkkkk_tr] = iconv('utf-8', 'windows-1251', $vvvvv);
                                            }
                                        }
                                    }
                                    else
                                    {
                                        $arRes[$k_tr][$kk_tr][$kkk_tr][$kkkk_tr] = iconv('utf-8', 'windows-1251', $vvvv);
                                    }
                                }
                            }
                            else
                            {
                                $arRes[$k_tr][$kk_tr][$kkk_tr] = iconv('utf-8', 'windows-1251', $vvv);
                            }
                        }
                    }
                    else
                    {
                        $arRes[$k_tr][$kk_tr] = iconv('utf-8', 'windows-1251', $vv);
                    }
                }
            }
            else
            {
                $arRes[$k_tr] = iconv('utf-8', 'windows-1251', $v);
            }
        }
        return $arRes;
    }

    /**
     * @param $ib
     * @param int $syms
     * @param bool $onlyforthisagent
     * @param int $value_id
     * @param int $agent
     * @return string
     */
    static public function GetMaxIDIN($ib, $syms = 5, $onlyforthisagent = false, $value_id = 0, $agent = 0)
    {
        $max_id = 0;
        $filter = array("IBLOCK_ID"=>$ib);
        if (($onlyforthisagent) && (intval($agent) > 0)  && (intval($value_id) > 0))
        {
            $filter['PROPERTY_'.$value_id] = $agent;
        }
        $res = CIBlockElement::GetList(array("ID"=>"desc"), $filter, false, array("nTopCount"=>1), array("ID", "PROPERTY_ID_IN"));
        if ($ob = $res->GetNextElement())
        {
            $arFields = $ob->GetFields();
            $max_id = intval($arFields["PROPERTY_ID_IN_VALUE"]);
        }
        $max_id++;
        $max_id_n = str_pad($max_id,$syms,'0',STR_PAD_LEFT);
        return $max_id_n;
    }

    /**
     * @param $total_weight
     * @param $total_gabweight
     * @param $city_recipient
     */
    static public function getSumm($total_weight, $total_gabweight, $city_recipient, $CURRENT_CLIENT)
    {
        $username =  iconv('windows-1251','utf-8',"DMSUser");
        $password =  iconv('windows-1251','utf-8',"1597534682");
        if ($CURRENT_CLIENT == 52254529){
            $host_api = "http://92.42.209.242/sd_msk/hs/Delivery/Account/7717739535/7701902970/GetTarif?ves=$total_weight&vesv=$total_gabweight&idcity2=$city_recipient&deliverytype=c";
        }
        if ($CURRENT_CLIENT == 56103010){
            $host_api = "http://92.42.209.242/sd_msk/hs/Delivery/Account/7717739535/7728178835/GetTarif?ves=$total_weight&vesv=$total_gabweight&idcity2=$city_recipient&deliverytype=c";
        }

        $url = iconv('windows-1251','utf-8',$host_api);

        if ($ch = curl_init()){
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec($ch);
            curl_close($ch);
            if(preg_match('/^[0-9]+(\.([0-9]){2})?$/', json_decode($output))){
                $sum_dev = json_decode($output);
                $sum_dev =  (float)iconv('utf-8','windows-1251',$sum_dev);
            }else{
                $sum_dev = 0;
            }
        }else{
            $sum_dev = 0;
        }
        return $sum_dev;
    }

    /**
     * @return array
     */

    static public function getUser()
    {
        global $USER;
        $rsUser = CUser::GetByID($USER->GetID());
        $arUser = $rsUser->Fetch();
        return $arUser;
    }


    /**
     * @param $company
     * @return array|bool
     */
    public function GetCompany($company)
    {
        $arFields = [];
        $filter = ["IBLOCK_ID" => 40, "ID" => $company];
        $res = CIBlockElement::GetList(
            ["NAME"=>"ASC"],
            $filter,
            false,
            false,
            ["ID", "NAME", "ACTIVE", "PROPERTY_*"]
        );
        if($ob = $res->GetNextElement())
        {
            $a = $ob->GetFields();
            $a['PROPERTY_CITY'] = $this->GetFullNameOfCity($a['PROPERTY_CITY_VALUE']);
            $a['PROPERTY_DEFAULT_CITY'] = $this->GetFullNameOfCity($a['PROPERTY_DEFAULT_CITY_VALUE']);
            $db_props = CIBlockElement::GetProperty(40, $a["PROPERTY_UK_VALUE"], array("sort" => "asc"),
                ["CODE"=>"LEGAL_NAME"]);
            if($ar_props = $db_props->Fetch())
            {
                $a["PROPERTY_UK_NAME"] =  $ar_props["VALUE"];
            }
            else
            {
                $a["PROPERTY_UK_NAME"] = "ООО «МСД»";
            }
            $db_props = CIBlockElement::GetProperty(40, $a["PROPERTY_UK_VALUE"], ["sort" => "asc"], ["CODE" => "CITY"]);
            if($ar_props = $db_props->Fetch())
            {
                $a["PROPERTY_UK_CITY"] = $this->GetFullNameOfCity($ar_props["VALUE"]);

            }
            $arFields = $a;
        }
        else
        {
            return false;
        }
        return $arFields;
    }

    /**
     * @param $city_id
     * @param bool $onlyname
     * @param bool $returnarr
     * @return array|bool|string
     */
    protected function GetFullNameOfCity($city_id, $onlyname = false, $returnarr = false)
    {
        if ((int)$city_id > 0)
        {
            $name_of_city = false;
            $arSelect = Array("ID","NAME","IBLOCK_SECTION_ID");
            $arFilter = Array("IBLOCK_ID" => 6, "ID" => $city_id);
            $res = CIBlockElement::GetList(Array("NAME"=>"asc"), $arFilter, false, false, $arSelect);
            if ($ob = $res->GetNextElement())
            {
                $a = $ob->GetFields();
                $res2 = CIBlockSection::GetByID($a["IBLOCK_SECTION_ID"]);
                if($ar_res2 = $res2->GetNext())
                {
                    $a["S_1"] = $ar_res2["NAME"];
                    if (intval($ar_res2["IBLOCK_SECTION_ID"]) > 0)
                    {
                        $res3 = CIBlockSection::GetByID(intval($ar_res2["IBLOCK_SECTION_ID"]));
                        if($ar_res3 = $res3->GetNext())
                        {
                            $a["S_2"] = $ar_res3["NAME"];
                        }
                    }
                }
                $name_of_city = $a["NAME"].', '.$a["S_1"].', '.$a["S_2"];
            }
            if ($returnarr)
            {
                return array($a["NAME"], $a["S_1"], $a["S_2"]);
            }
            else
            {
                return ($onlyname) ? $a["NAME"] : $name_of_city;
            }
        }
        else
        {
            return '';
        }
    }

    /**
     * @param $branch
     * @param $client
     * @return bool
     */
    protected function GetBranch($branch, $client)
    {
        $arFields = false;
        $res = CIBlockElement::GetList(
            ["NAME"=>"ASC"],
            ["IBLOCK_ID" => 89, "ID" => $branch, "PROPERTY_CLIENT" => $client],
            false,
            ["nTopCount" => 1],
            ["ID","NAME","ACTIVE", "PROPERTY_FIO", "PROPERTY_PHONE", "PROPERTY_CITY",
                "PROPERTY_INDEX", "PROPERTY_ADRESS", "PROPERTY_EMAIL",
                "PROPERTY_LIMIT", "PROPERTY_IN_1C", "PROPERTY_IN_1C_CODE",
                "PROPERTY_IN_1C_PREFIX", "PROPERTY_BY_AGENT","PROPERTY_LIMITPERIODS",
                "PROPERTY_BUDGETPERIODS", 'PROPERTY_HEAD_BRANCH']
        );
        if($ob = $res->GetNextElement())
        {
            $a = $ob->GetFields();
            $a['PROPERTY_CITY'] = self::GetFullNameOfCity($a['PROPERTY_CITY_VALUE']);
            $a['SPENT'] = [
                0 => 0,
                1 => 0,
                2 => 0,
                3 => 0
            ];
            $res_2 = CIBlockElement::GetList(
                ["PROPERTY_QUARTER"=>"ASC"],
                ["IBLOCK_ID" => 90, "PROPERTY_BRANCH" => $branch, "PROPERTY_CLIENT" => $client, "PROPERTY_YEAR" => date('Y')],
                false,
                false,
                ["ID","PROPERTY_QUARTER", "PROPERTY_SPENT"]
            );
            while($ob_2 = $res_2->GetNextElement())
            {
                $b = $ob_2->GetFields();
                if ($b['PROPERTY_QUARTER_ENUM_ID'] == 266)
                {
                    $a['SPENT'][0] = $b['PROPERTY_SPENT_VALUE'];
                }
                if ($b['PROPERTY_QUARTER_ENUM_ID'] == 267)
                {
                    $a['SPENT'][1] = $b['PROPERTY_SPENT_VALUE'];
                }
                if ($b['PROPERTY_QUARTER_ENUM_ID'] == 268)
                {
                    $a['SPENT'][2] = $b['PROPERTY_SPENT_VALUE'];
                }
                if ($b['PROPERTY_QUARTER_ENUM_ID'] == 269)
                {
                    $a['SPENT'][3] = $b['PROPERTY_SPENT_VALUE'];
                }
            }
            $arFields = $a;
        }
        return $arFields;
    }

    /**
     * @param $b1
     * @return string|string[]
     */
    protected function deleteTabs($b1)
    {
        $b1 = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $b1);
        $b1 = trim($b1);
        return $b1;
    }

    /**
     * @return array
     */
    protected function getEventFields()
    {
        $arEventFields = [
            "COMPANY_F" => ($this->USER_IN_BRANCH) ? $this->AGENT['NAME'].', филиал '.$this->BRANCH_INFO['NAME'] : $this->AGENT['NAME'],
            "NUMBER" => $this->id_in_cur,
            "COMPANY" => $this->AGENT['NAME'],
            "BRANCH" => ($this->USER_IN_BRANCH) ? 'Филиал: <strong>'.$this->BRANCH_INFO['NAME'].'</strong><br />' : '',
            "DATE_TIME" => $this->arResult['PROPERTY_984'] . ' с: ' . $this->arResult['PROPERTY_985'] . ' до: ' .
                $this->arResult['PROPERTY_986'],
            "CITY" =>  $this->arResult['PROPERTY_549'],
            "ADRESS" => $this->arResult['PROPERTY_551'],
            "CONTACT" => $this->arResult['PROPERTY_546'],
            "PHONE" => $this->arResult['PROPERTY_547'],
            "WEIGHT" => $this->arResult['PROPERTY_568'],
            "SIZE_1" => '',
            "SIZE_2" => '',
            "SIZE_3" => '',
            "COMMENT" => $this->arResult['PROPERTY_570'],
            'AGENT_EMAIL' => $this->arResult['ADD_AGENT_EMAIL'],
            'UK_EMAIL' => $this->EMAIL_CALLCOURIER,
            'TYPE_PAYS' => $this->payment_type,
            'SPEC_INSTR' => $this->arResult['PROPERTY_570'],
            'PAYER' => $this->delivery_payer,
            "POST" => "client@newpartner.ru, logist@newpartner.ru",
        ];
        return $arEventFields;

    }

    /**
     * вспомогательная для GetAllSectionIn
     * @param $SECTION_ID
     * @param $arParent
     * @return array
     */
    static protected function GetAllSectionInSel($SECTION_ID, $arParent){
        $arR=[];
        for($i=0, $k=count($arParent[$SECTION_ID]); $i<$k; $i++){
            array_push($arR, $arParent[$SECTION_ID][$i]);
            if(isset($arParent[$arParent[$SECTION_ID][$i]])){ //Если ребёнок является родителем
                $arR=array_merge($arR, self::GetAllSectionInSel($arParent[$SECTION_ID][$i], $arParent));
            }
        }
        return $arR;
    }
    /**
     * выводит массив section любой вложенности
     * @param $IBLOCK_ID
     * @param $SECTION_ID
     * @param $arFilter
     * @param $arSelect
     * @return array
     */
    static function GetAllSectionIn($IBLOCK_ID, $SECTION_ID, $arFilter, $arSelect){

        if($arSelect=='ID'){ //если нужны только ид
            $IDon=true;
            $arSelect=['ID','IBLOCK_SECTION_ID'];
        }else{
            $arSelect=array_merge(['ID','IBLOCK_SECTION_ID'], $arSelect);
        }

        $obSection=CIBlockSection::GetList(
            [],
            array_merge(['IBLOCK_ID'=>$IBLOCK_ID],$arFilter),
            false,
            $arSelect,
            false
        );

        $arAlId = []; //Для хранения результатов
        $arParent = []; //Для хранения детей разделов
        while($arResult=$obSection->GetNext()){

            $arAlId[$arResult['ID']] = $arResult;
            if(!is_array($arParent[$arResult['IBLOCK_SECTION_ID']])){ //Если родителя в списке нет, то добавляем
                $arParent[$arResult['IBLOCK_SECTION_ID']] = [];
            }
            array_push($arParent[$arResult['IBLOCK_SECTION_ID']], $arResult['ID']);

        }
        unset($obSection);

        $arR = self::GetAllSectionInSel($SECTION_ID, $arParent); //Ид всех детей и правнуков

        if(!$IDon){ //Если необходим не только ид
            $arId=$arR;
            $arR=array();
            for($i=0,$k=count($arId);$i<$k;$i++){
                array_push($arR,$arAlId[$arId[$i]]);
            }
        }
        return $arR;
    }

    /**
     * @param string $folder
     * @param array $params
     * @param string $mainfolder
     * @return bool
     */
    static public function AddToLogs($folder = '', $params = [], $mainfolder = '')
        {
            if ((!strlen(trim($folder))) || (!is_array($params)))
            {
                return false;
            }
        if (!strlen(trim($mainfolder)))
        {
            $mainfolder = $_SERVER['DOCUMENT_ROOT'].'/logs';
        }
        if (!file_exists($mainfolder))
        {
            mkdir($mainfolder);
        }
        $mainfolder .= '/'.$folder;
        if (!file_exists($mainfolder))
        {
            mkdir($mainfolder);
        }
        $mainfolder .= '/'.date('Y');
        if (!file_exists($mainfolder))
        {
            mkdir($mainfolder);
        }
        $mainfolder .= '/'.date('m');
        if (!file_exists($mainfolder))
        {
            mkdir($mainfolder);
        }
        $mainfolder .= '/log.txt';
        $file = fopen($mainfolder,'a');
        global $USER;
        $user = "[".$USER->GetID()."] (".$USER->GetLogin().") ".$USER->GetFullName();
        fwrite($file,date('d.m.Y H:i:s').' '.$user."\n");
        $params_str = [];
        foreach ($params as $k => $v)
        {
            $params_str[] = $k.': '.$v;
        }
        fwrite($file,implode("\n",$params_str)."\n");
        fwrite($file,"\n");
        fclose($file);
        file_put_contents($mainfolder, print_r($params, true), FILE_APPEND);
        return true;
        }
}