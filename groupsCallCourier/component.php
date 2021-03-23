<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
    die();
}
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");
require_once ($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/GroupsCallCourier/GroupsCallCourier.php");

$arrIds = [];
try {
    $arResult = new GroupsCallCourier($_POST);
    $arResult->currentClient()
        ->soapLink()
        ->invoiceList()
        ->prepareListFor1c()
        ->setDocsClient()
        ->checkInvBase()
        ->sendPost();
}
catch(Exception $e){
    echo(json_encode(['error' => iconv('windows-1251', 'utf-8', $e->getMessage())]));
    exit();
}

if($arResult->current){
    $arrIds['ids'] = $arResult->content['ids'];
    $arrIds['current'] = 1;
    $jsonIds = json_encode(convArrayToUTF($arrIds));
    echo $jsonIds;
}



/*<pre>GroupsCallCourier Object
(
    [content] => Array
(
    [ids] => Array
    (
        [0] => 64704256
        [1] => 64704257
    )

    [current_client] => 9528186
    [callcourierdate_ids] => 25.03.2021
    [callcourtime_from_ids] => 10:00
    [callcourtime_to_ids] => 14:00
    [callcourcomment_ids] => 10-14
)

 [idClient:GroupsCallCourier:private] => 9528186
 [arrClient] => Array
(
    [ID] => 9528186
            [~ID] => 9528186
            [NAME] => Тестовый клиент
[~NAME] => Тестовый клиент
[ACTIVE] => Y
[~ACTIVE] => Y
[PROPERTY_INN_VALUE] => 34327677
            [~PROPERTY_INN_VALUE] => 34327677
            [PROPERTY_INN_VALUE_ID] => 9528186:237
            [~PROPERTY_INN_VALUE_ID] => 9528186:237
            [PROPERTY_INN_REAL_VALUE] => 7700770077
            [~PROPERTY_INN_REAL_VALUE] => 7700770077
            [PROPERTY_INN_REAL_VALUE_ID] => 9528186:729
            [~PROPERTY_INN_REAL_VALUE_ID] => 9528186:729
            [PROPERTY_UK_VALUE] => 2197189
            [~PROPERTY_UK_VALUE] => 2197189
            [PROPERTY_UK_VALUE_ID] => 9528186:467
            [~PROPERTY_UK_VALUE_ID] => 9528186:467
            [PROPERTY_TYPE_VALUE] => Клиент
[~PROPERTY_TYPE_VALUE] => Клиент
[PROPERTY_TYPE_ENUM_ID] => 242
            [~PROPERTY_TYPE_ENUM_ID] => 242
            [PROPERTY_TYPE_VALUE_ID] => 9528186:211
            [~PROPERTY_TYPE_VALUE_ID] => 9528186:211
            [PROPERTIES] => Array
(
)

        )

    [uCompId] => 2197189
    [uCompSettingsId] => 2378056
    [uCSettings] => Array
(
    [ipaddr1c] => 92.42.209.242
            [port1c] =>
            [url1c] => /sd_msk/ws/DashboardExchange.1cws?wsdl
[login1c] => DMSUser
[pass1c] => 1597534682
        )

    [client] => SoapClient Object
(
    [_login] => DMSUser
[_password] => 1597534682
            [_exceptions] =>
            [_stream_context] => Resource id #71
[_soap_version] => 1
            [sdl] => Resource id #72
        )

    [invoiceList] => Array
(
    [0] => Array
    (
        [ID] => 64704256
        [~ID] => 64704256
        [NAME] => 90-3434487-2
        [~NAME] => 90-3434487-2
        [DATE_CREATE] => 23.03.2021 15:43:51
        [~DATE_CREATE] => 23.03.2021 15:43:51
        [ACTIVE] => Y
        [~ACTIVE] => Y
        [PROPERTY_CITY_RECIPIENT_NAME] => Москва
        [~PROPERTY_CITY_RECIPIENT_NAME] => Москва
        [PROPERTY_CALLING_COURIER_VALUE] =>
        [~PROPERTY_CALLING_COURIER_VALUE] =>
        [PROPERTY_CALLING_COURIER_VALUE_ID] => 64704256:977
        [~PROPERTY_CALLING_COURIER_VALUE_ID] => 64704256:977
        [IBLOCK_ELEMENT_ID] => 64704256
        [~IBLOCK_ELEMENT_ID] => 64704256
        [PROPERTY_544] => 3434487.0000
        [~PROPERTY_544] => 3434487.0000
        [PROPERTY_545] => 9528186
        [~PROPERTY_545] => 9528186
        [PROPERTY_546] => Прудникова Юлия
        [~PROPERTY_546] => Прудникова Юлия
        [PROPERTY_547] => 9 (906) 111-11-11
        [~PROPERTY_547] => 9 (906) 111-11-11
        [PROPERTY_548] => КП &quot;МПТЦ&quot;
        [~PROPERTY_548] => КП "МПТЦ"
        [PROPERTY_549] => 9432
        [~PROPERTY_549] => 9432
        [PROPERTY_550] =>
        [~PROPERTY_550] =>
        [PROPERTY_551] => Array
            (
                [TYPE] => TEXT
                [TEXT] => Можайское шоссе, д.
            )

        [~PROPERTY_551] => Array
            (
                [TYPE] => TEXT
                [TEXT] => Можайское шоссе, д.
            )

            [PROPERTY_552] => Кадыкова Наталья Александровна
            [~PROPERTY_552] => Кадыкова Наталья Александровна
            [PROPERTY_553] => 9 (495222-22-22
            [~PROPERTY_553] => 9 (495222-22-22
            [PROPERTY_554] => ФГУП «Госкорпорация по Орвд»
            [~PROPERTY_554] => ФГУП «Госкорпорация по Орвд»
            [PROPERTY_555] => 8054
            [~PROPERTY_555] => 8054
            [PROPERTY_556] =>
            [~PROPERTY_556] =>
            [PROPERTY_557] => 243
            [~PROPERTY_557] => 243
            [PROPERTY_558] => 246
            [~PROPERTY_558] => 246
            [PROPERTY_559] => 248
            [~PROPERTY_559] => 248
            [PROPERTY_560] => 03.03.2021
            [~PROPERTY_560] => 03.03.2021
            [PROPERTY_561] =>
            [~PROPERTY_561] =>
            [PROPERTY_562] => 251
            [~PROPERTY_562] => 251
            [PROPERTY_563] =>
            [~PROPERTY_563] =>
            [PROPERTY_564] => 256
            [~PROPERTY_564] => 256
            [PROPERTY_565] =>
            [~PROPERTY_565] =>
            [PROPERTY_566] =>
            [~PROPERTY_566] =>
            [PROPERTY_567] => 3.0000
            [~PROPERTY_567] => 3.0000
            [PROPERTY_568] => 1.1000
            [~PROPERTY_568] => 1.1000
            [PROPERTY_569] => Array
(
)

[~PROPERTY_569] => Array
(
)

[PROPERTY_570] => Array
(
    [TYPE] => TEXT
    [TEXT] => Передать договор - один экземпляр.
                        )

                    [~PROPERTY_570] => Array
(
    [TYPE] => TEXT
    [TEXT] => Передать договор - один экземпляр.
                        )

                    [PROPERTY_571] => Array
(
    [TYPE] => TEXT
    [TEXT] => ул. Большая Внуковская
                        )

                    [~PROPERTY_571] => Array
(
    [TYPE] => TEXT
    [TEXT] => ул. Большая Внуковская
                        )

                    [PROPERTY_572] => 257
                    [~PROPERTY_572] => 257
                    [PROPERTY_573] =>
                    [~PROPERTY_573] =>
                    [PROPERTY_639] =>
                    [~PROPERTY_639] =>
                    [PROPERTY_640] => 9528457
                    [~PROPERTY_640] => 9528457
                    [PROPERTY_641] =>
                    [~PROPERTY_641] =>
                    [PROPERTY_642] =>
                    [~PROPERTY_642] =>
                    [PROPERTY_646] =>
                    [~PROPERTY_646] =>
                    [PROPERTY_647] => 0
                    [~PROPERTY_647] => 0
                    [PROPERTY_665] =>
                    [~PROPERTY_665] =>
                    [PROPERTY_679] => 1
                    [~PROPERTY_679] => 1
                    [PROPERTY_680] => 0
                    [~PROPERTY_680] => 0
                    [PROPERTY_682] =>
                    [~PROPERTY_682] =>
                    [PROPERTY_724] =>
                    [~PROPERTY_724] =>
                    [PROPERTY_732] =>
                    [~PROPERTY_732] =>
                    [PROPERTY_733] =>
                    [~PROPERTY_733] =>
                    [PROPERTY_737] =>
                    [~PROPERTY_737] =>
                    [PROPERTY_764] =>
                    [~PROPERTY_764] =>
                    [PROPERTY_772] =>
                    [~PROPERTY_772] =>
                    [PROPERTY_775] =>
                    [~PROPERTY_775] =>
                    [PROPERTY_779] =>
                    [~PROPERTY_779] =>
                    [PROPERTY_787] =>
                    [~PROPERTY_787] =>
                    [PROPERTY_791] =>
                    [~PROPERTY_791] =>
                    [PROPERTY_861] =>
                    [~PROPERTY_861] =>
                    [PROPERTY_977] =>
                    [~PROPERTY_977] =>
                    [PROPERTY_978] =>
                    [~PROPERTY_978] =>
                    [PROPERTY_979] => 0.0000
                    [~PROPERTY_979] => 0.0000
                    [PROPERTY_980] =>
                    [~PROPERTY_980] =>
                    [PROPERTY_981] =>
                    [~PROPERTY_981] =>
                    [PROPERTY_982] =>
                    [~PROPERTY_982] =>
                    [PROPERTY_983] =>
                    [~PROPERTY_983] =>
                    [PROPERTY_984] =>
                    [~PROPERTY_984] =>
                    [PROPERTY_985] =>
                    [~PROPERTY_985] =>
                    [PROPERTY_986] =>
                    [~PROPERTY_986] =>
                    [PROPERTY_987] =>
                    [~PROPERTY_987] =>
                    [PROPERTY_988] =>
                    [~PROPERTY_988] =>
                    [PROPERTY_989] =>
                    [~PROPERTY_989] =>
                    [PROPERTY_991] =>
                    [~PROPERTY_991] =>
                    [PROPERTY_1078] =>
                    [~PROPERTY_1078] =>
                    [PROPERTY_1079] =>
                    [~PROPERTY_1079] =>
                    [DESCRIPTION_569] => Array
(
)

[~DESCRIPTION_569] => Array
(
)

[PROPERTY_VALUE_ID_569] => Array
(
)

[~PROPERTY_VALUE_ID_569] => Array
(
)

                )

            [1] => Array
(
    [ID] => 64704257
                    [~ID] => 64704257
                    [NAME] => 90-3434487-3
                    [~NAME] => 90-3434487-3
                    [DATE_CREATE] => 23.03.2021 15:43:51
                    [~DATE_CREATE] => 23.03.2021 15:43:51
                    [ACTIVE] => Y
[~ACTIVE] => Y
[PROPERTY_CITY_RECIPIENT_NAME] => Москва
[~PROPERTY_CITY_RECIPIENT_NAME] => Москва
[PROPERTY_CALLING_COURIER_VALUE] =>
                    [~PROPERTY_CALLING_COURIER_VALUE] =>
                    [PROPERTY_CALLING_COURIER_VALUE_ID] => 64704257:977
                    [~PROPERTY_CALLING_COURIER_VALUE_ID] => 64704257:977
                    [IBLOCK_ELEMENT_ID] => 64704257
                    [~IBLOCK_ELEMENT_ID] => 64704257
                    [PROPERTY_544] => 3434487.0000
                    [~PROPERTY_544] => 3434487.0000
                    [PROPERTY_545] => 9528186
                    [~PROPERTY_545] => 9528186
                    [PROPERTY_546] => Прудникова Юлия
[~PROPERTY_546] => Прудникова Юлия
[PROPERTY_547] => 10 (906) 111-11-11
                    [~PROPERTY_547] => 10 (906) 111-11-11
                    [PROPERTY_548] => КП &quot;МПТЦ&quot;
                    [~PROPERTY_548] => КП "МПТЦ"
[PROPERTY_549] => 8054
                    [~PROPERTY_549] => 8054
                    [PROPERTY_550] =>
                    [~PROPERTY_550] =>
                    [PROPERTY_551] => Array
(
    [TYPE] => TEXT
    [TEXT] => Можайское шоссе, д.
                        )

                    [~PROPERTY_551] => Array
(
    [TYPE] => TEXT
    [TEXT] => Можайское шоссе, д.
                        )

                    [PROPERTY_552] => Кадыкова Наталья Александровна
[~PROPERTY_552] => Кадыкова Наталья Александровна
[PROPERTY_553] => 10 (495222-22-22
                    [~PROPERTY_553] => 10 (495222-22-22
                    [PROPERTY_554] => ФГУП «Госкорпорация по Орвд»
[~PROPERTY_554] => ФГУП «Госкорпорация по Орвд»
[PROPERTY_555] => 8054
                    [~PROPERTY_555] => 8054
                    [PROPERTY_556] =>
                    [~PROPERTY_556] =>
                    [PROPERTY_557] => 243
                    [~PROPERTY_557] => 243
                    [PROPERTY_558] => 246
                    [~PROPERTY_558] => 246
                    [PROPERTY_559] => 248
                    [~PROPERTY_559] => 248
                    [PROPERTY_560] => 04.03.2021
                    [~PROPERTY_560] => 04.03.2021
                    [PROPERTY_561] =>
                    [~PROPERTY_561] =>
                    [PROPERTY_562] => 251
                    [~PROPERTY_562] => 251
                    [PROPERTY_563] =>
                    [~PROPERTY_563] =>
                    [PROPERTY_564] => 256
                    [~PROPERTY_564] => 256
                    [PROPERTY_565] =>
                    [~PROPERTY_565] =>
                    [PROPERTY_566] =>
                    [~PROPERTY_566] =>
                    [PROPERTY_567] => 4.0000
                    [~PROPERTY_567] => 4.0000
                    [PROPERTY_568] => 2.1000
                    [~PROPERTY_568] => 2.1000
                    [PROPERTY_569] => Array
(
)

[~PROPERTY_569] => Array
(
)

[PROPERTY_570] => Array
(
    [TYPE] => TEXT
    [TEXT] => Передать договор - один экземпляр.
                        )

                    [~PROPERTY_570] => Array
(
    [TYPE] => TEXT
    [TEXT] => Передать договор - один экземпляр.
                        )

                    [PROPERTY_571] => Array
(
    [TYPE] => TEXT
    [TEXT] => ул. Большая Внуковская
                        )

                    [~PROPERTY_571] => Array
(
    [TYPE] => TEXT
    [TEXT] => ул. Большая Внуковская
                        )

                    [PROPERTY_572] => 257
                    [~PROPERTY_572] => 257
                    [PROPERTY_573] =>
                    [~PROPERTY_573] =>
                    [PROPERTY_639] =>
                    [~PROPERTY_639] =>
                    [PROPERTY_640] => 9528457
                    [~PROPERTY_640] => 9528457
                    [PROPERTY_641] =>
                    [~PROPERTY_641] =>
                    [PROPERTY_642] =>
                    [~PROPERTY_642] =>
                    [PROPERTY_646] =>
                    [~PROPERTY_646] =>
                    [PROPERTY_647] => 0
                    [~PROPERTY_647] => 0
                    [PROPERTY_665] =>
                    [~PROPERTY_665] =>
                    [PROPERTY_679] => 1
                    [~PROPERTY_679] => 1
                    [PROPERTY_680] => 0
                    [~PROPERTY_680] => 0
                    [PROPERTY_682] =>
                    [~PROPERTY_682] =>
                    [PROPERTY_724] =>
                    [~PROPERTY_724] =>
                    [PROPERTY_732] =>
                    [~PROPERTY_732] =>
                    [PROPERTY_733] =>
                    [~PROPERTY_733] =>
                    [PROPERTY_737] =>
                    [~PROPERTY_737] =>
                    [PROPERTY_764] =>
                    [~PROPERTY_764] =>
                    [PROPERTY_772] =>
                    [~PROPERTY_772] =>
                    [PROPERTY_775] =>
                    [~PROPERTY_775] =>
                    [PROPERTY_779] =>
                    [~PROPERTY_779] =>
                    [PROPERTY_787] =>
                    [~PROPERTY_787] =>
                    [PROPERTY_791] =>
                    [~PROPERTY_791] =>
                    [PROPERTY_861] =>
                    [~PROPERTY_861] =>
                    [PROPERTY_977] =>
                    [~PROPERTY_977] =>
                    [PROPERTY_978] =>
                    [~PROPERTY_978] =>
                    [PROPERTY_979] => 0.0000
                    [~PROPERTY_979] => 0.0000
                    [PROPERTY_980] =>
                    [~PROPERTY_980] =>
                    [PROPERTY_981] =>
                    [~PROPERTY_981] =>
                    [PROPERTY_982] =>
                    [~PROPERTY_982] =>
                    [PROPERTY_983] =>
                    [~PROPERTY_983] =>
                    [PROPERTY_984] =>
                    [~PROPERTY_984] =>
                    [PROPERTY_985] =>
                    [~PROPERTY_985] =>
                    [PROPERTY_986] =>
                    [~PROPERTY_986] =>
                    [PROPERTY_987] =>
                    [~PROPERTY_987] =>
                    [PROPERTY_988] =>
                    [~PROPERTY_988] =>
                    [PROPERTY_989] =>
                    [~PROPERTY_989] =>
                    [PROPERTY_991] =>
                    [~PROPERTY_991] =>
                    [PROPERTY_1078] =>
                    [~PROPERTY_1078] =>
                    [PROPERTY_1079] =>
                    [~PROPERTY_1079] =>
                    [DESCRIPTION_569] => Array
(
)

[~DESCRIPTION_569] => Array
(
)

[PROPERTY_VALUE_ID_569] => Array
(
)

[~PROPERTY_VALUE_ID_569] => Array
(
)

                )

        )

    [invoiceListFor1c] => Array
(
    [0] => Array
    (
        [ID] => 64704256
                    [DATE_CREATE] => 23.03.2021 15:43:51
                    [INN] => 34327677
                    [NAME_SENDER] => Прудникова Юлия
[PHONE_SENDER] => 9 (906) 111-11-11
                    [COMPANY_SENDER] => КП «МПТЦ»
[CITY_SENDER] => 9432
                    [INDEX_SENDER] =>
                    [ADDRESS_SENDER] => Можайское шоссе, д.
[ADDRESS_RECIPIENT] => ул. Большая Внуковская
[NAME_RECIPIENT] => Кадыкова Наталья Александровна
[PHONE_RECIPIENT] => 9 (495222-22-22
                    [COMPANY_RECIPIENT] => ФГУП «Госкорпорация по Орвд»
[CITY_RECIPIENT] => 8054
                    [CITY_RECIPIENT_NON] => Москва
[INDEX_RECIPIENT] =>
                    [DATE_TAKE_FROM] => 2021-03-25 10:00:00
                    [DATE_TAKE_TO] => 2021-03-25 14:00:00
                    [TYPE] => 246
                    [DELIVERY_TYPE] => Э
[DELIVERY_PAYER] => О
[PAYMENT_TYPE] => Б
[DELIVERY_CONDITION] => А
[PAYMENT_AMOUNT] => 0
                    [PAYMENT] => 0
                    [INSTRUCTIONS] => Передать договор - один экземпляр. 10-14
                    [PLACES] => 3
                    [WEIGHT] => 1.1
                    [SIZE_1] => 0
                    [SIZE_2] => 0
                    [SIZE_3] => 0
                    [FILES] =>
                    [InternalNumber] =>
                    [DocNumber] => 90-3434487-2
                    [TRANSPORT_TYPE] =>
                    [ISOFFICE] =>
                    [INN_SENDER] =>
                    [INN_RECIPIENT] =>
                )

            [1] => Array
(
    [ID] => 64704257
                    [DATE_CREATE] => 23.03.2021 15:43:51
                    [INN] => 34327677
                    [NAME_SENDER] => Прудникова Юлия
[PHONE_SENDER] => 10 (906) 111-11-11
                    [COMPANY_SENDER] => КП «МПТЦ»
[CITY_SENDER] => 8054
                    [INDEX_SENDER] =>
                    [ADDRESS_SENDER] => Можайское шоссе, д.
[ADDRESS_RECIPIENT] => ул. Большая Внуковская
[NAME_RECIPIENT] => Кадыкова Наталья Александровна
[PHONE_RECIPIENT] => 10 (495222-22-22
                    [COMPANY_RECIPIENT] => ФГУП «Госкорпорация по Орвд»
[CITY_RECIPIENT] => 8054
                    [CITY_RECIPIENT_NON] => Москва
[INDEX_RECIPIENT] =>
                    [DATE_TAKE_FROM] => 2021-03-25 10:00:00
                    [DATE_TAKE_TO] => 2021-03-25 14:00:00
                    [TYPE] => 246
                    [DELIVERY_TYPE] => Э
[DELIVERY_PAYER] => О
[PAYMENT_TYPE] => Б
[DELIVERY_CONDITION] => А
[PAYMENT_AMOUNT] => 0
                    [PAYMENT] => 0
                    [INSTRUCTIONS] => Передать договор - один экземпляр. 10-14
                    [PLACES] => 4
                    [WEIGHT] => 2.1
                    [SIZE_1] => 0
                    [SIZE_2] => 0
                    [SIZE_3] => 0
                    [FILES] =>
                    [InternalNumber] =>
                    [DocNumber] => 90-3434487-3
                    [TRANSPORT_TYPE] =>
                    [ISOFFICE] =>
                    [INN_SENDER] =>
                    [INN_RECIPIENT] =>
                )

        )

    [mbDetect] =>
    [arListForCall] => Array
(
    [0] => Array
    (
        [IDWEB] => 64704256
                    [INN] => 34327677
                    [DATE] => 2021-03-23
                    [COMPANY_SENDER] => КП «МПТЦ»
[NAME_SENDER] => Прудникова Юлия
[PHONE_SENDER] => 9 (906) 111-11-11
                    [ADRESS_SENDER] => Можайское шоссе, д.
[INDEX_SENDER] =>
                    [ID_CITY_SENDER] => 9432
                    [DELIVERY_TYPE] => Э
[PAYMENT_TYPE] => Б
[DELIVERY_PAYER] => О
[DELIVERY_CONDITION] => А
[DATE_TAKE_FROM] => 2021-03-25 10:00:00
                    [DATE_TAKE_TO] => 2021-03-25 14:00:00
                    [INSTRUCTIONS] => Передать договор - один экземпляр. 10-14
                    [TRANSPORT_TYPE] =>
                )

            [1] => Array
(
    [IDWEB] => 64704257
                    [INN] => 34327677
                    [DATE] => 2021-03-23
                    [COMPANY_SENDER] => КП «МПТЦ»
[NAME_SENDER] => Прудникова Юлия
[PHONE_SENDER] => 10 (906) 111-11-11
                    [ADRESS_SENDER] => Можайское шоссе, д.
[INDEX_SENDER] =>
                    [ID_CITY_SENDER] => 8054
                    [DELIVERY_TYPE] => Э
[PAYMENT_TYPE] => Б
[DELIVERY_PAYER] => О
[DELIVERY_CONDITION] => А
[DATE_TAKE_FROM] => 2021-03-25 10:00:00
                    [DATE_TAKE_TO] => 2021-03-25 14:00:00
                    [INSTRUCTIONS] => Передать договор - один экземпляр. 10-14
                    [TRANSPORT_TYPE] =>
                )

        )

    [arJsCall] => Array
(
)

[arJsCallDoc] => Array
(
)

[errors] => Array
(
)

[respSetDocs] =>
    [current] => 1
)
</pre>*/