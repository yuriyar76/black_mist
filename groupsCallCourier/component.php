<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
    die();
}
require ($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/GroupsCallCourier/GroupsCallCourier.php");

use Bitrix\Main\Localization\Loc;

try {
    $arResult = new GroupsCallCourier($_POST);
    $arResult->currentClient()
        ->soapLink()
        ->invoiceList()
        ->prepareListFor1c()
        ->setDocsClient()
        ->sendPost();
}
catch(Exception $e){
    dump($e->getMessage());
}


dump($arResult);



/*
    [data_json] => {"0":"64513265","1":"64513266","2":"64513267","3":"64513268","4":"64513269","5":"64513270","6":"64513271"}
    [current_client] => 9528186
    [callcourierdate_ids] => 19.03.2021
    [callcourtime_from_ids] => 10:00
    [callcourtime_to_ids] => 11:00
    [callcourcomment_ids] => csdcvdscds


     [ids] => Array
        (
            [0] => 64513265
            [1] => 64513266
            [2] => 64513267
            [3] => 64513268
            [4] => 64513269
            [5] => 64513270
            [6] => 64513271
        )

    [current_client] => 9528186
    [callcourierdate_ids] => 19.03.2021
    [callcourtime_from_ids] => 10:00
    [callcourtime_to_ids] => 11:00
    [callcourcomment_ids] => kkkkkkkkkkkkkk
)


 [content] => Array
        (
            [ids] => Array
                (
                    [0] => 64513265
                    [1] => 64513266
                    [2] => 64513267
                    [3] => 64513268
                    [4] => 64513269
                    [5] => 64513270
                    [6] => 64513271
                )

            [current_client] => 9528186
            [callcourierdate_ids] => 19.03.2021
            [callcourtime_from_ids] => 10:00
            [callcourtime_to_ids] => 11:00
            [callcourcomment_ids] => kkkkkkkkkkkkkk
        )

    [idClient:GroupsCallCourier:private] => 9528186
    [arrClient] => Array
        (
            [NAME] => ???????? ??????
            [~NAME] => ???????? ??????
            [ID] => 9528186
            [~ID] => 9528186
            [TIMESTAMP_X] => 26.10.2020 11:18:56
            [~TIMESTAMP_X] => 26.10.2020 11:18:56
            [TIMESTAMP_X_UNIX] => 1603700336
            [~TIMESTAMP_X_UNIX] => 1603700336
            [MODIFIED_BY] => 4783
            [~MODIFIED_BY] => 4783
            [DATE_CREATE] => 13.04.2015 14:49:11
            [~DATE_CREATE] => 13.04.2015 14:49:11
            [DATE_CREATE_UNIX] => 1428925751
            [~DATE_CREATE_UNIX] => 1428925751
            [CREATED_BY] => 102
            [~CREATED_BY] => 102
            [IBLOCK_ID] => 40
            [~IBLOCK_ID] => 40
            [IBLOCK_SECTION_ID] =>
            [~IBLOCK_SECTION_ID] =>
            [ACTIVE] => Y
            [~ACTIVE] => Y
            [ACTIVE_FROM] =>
            [~ACTIVE_FROM] =>
            [ACTIVE_TO] =>
            [~ACTIVE_TO] =>
            [DATE_ACTIVE_FROM] =>
            [~DATE_ACTIVE_FROM] =>
            [DATE_ACTIVE_TO] =>
            [~DATE_ACTIVE_TO] =>
            [SORT] => 500
            [~SORT] => 500
            [PREVIEW_PICTURE] =>
            [~PREVIEW_PICTURE] =>
            [PREVIEW_TEXT] =>
            [~PREVIEW_TEXT] =>
            [PREVIEW_TEXT_TYPE] => text
            [~PREVIEW_TEXT_TYPE] => text
            [DETAIL_PICTURE] =>
            [~DETAIL_PICTURE] =>
            [DETAIL_TEXT] =>
            [~DETAIL_TEXT] =>
            [DETAIL_TEXT_TYPE] => text
            [~DETAIL_TEXT_TYPE] => text
            [SEARCHABLE_CONTENT] => ???????? ??????


            [~SEARCHABLE_CONTENT] => ???????? ??????


            [WF_STATUS_ID] => 1
            [~WF_STATUS_ID] => 1
            [WF_PARENT_ELEMENT_ID] =>
            [~WF_PARENT_ELEMENT_ID] =>
            [WF_LAST_HISTORY_ID] =>
            [~WF_LAST_HISTORY_ID] =>
            [WF_NEW] =>
            [~WF_NEW] =>
            [LOCK_STATUS] => green
            [~LOCK_STATUS] => green
            [WF_LOCKED_BY] =>
            [~WF_LOCKED_BY] =>
            [WF_DATE_LOCK] =>
            [~WF_DATE_LOCK] =>
            [WF_COMMENTS] =>
            [~WF_COMMENTS] =>
            [IN_SECTIONS] => N
            [~IN_SECTIONS] => N
            [SHOW_COUNTER] =>
            [~SHOW_COUNTER] =>
            [SHOW_COUNTER_START] =>
            [~SHOW_COUNTER_START] =>
            [SHOW_COUNTER_START_X] =>
            [~SHOW_COUNTER_START_X] =>
            [CODE] =>
            [~CODE] =>
            [TAGS] =>
            [~TAGS] =>
            [XML_ID] => 9528186
            [~XML_ID] => 9528186
            [EXTERNAL_ID] => 9528186
            [~EXTERNAL_ID] => 9528186
            [TMP_ID] => 0
            [~TMP_ID] => 0
            [USER_NAME] => (webprog) ???? ????????
            [~USER_NAME] => (webprog) ???? ????????
            [LOCKED_USER_NAME] =>
            [~LOCKED_USER_NAME] =>
            [CREATED_USER_NAME] => (black_mist) ????? ????????
            [~CREATED_USER_NAME] => (black_mist) ????? ????????
            [LANG_DIR] => /
            [~LANG_DIR] => /
            [LID] => s5
            [~LID] => s5
            [IBLOCK_TYPE_ID] => delivery
            [~IBLOCK_TYPE_ID] => delivery
            [IBLOCK_CODE] => delivery_companies
            [~IBLOCK_CODE] => delivery_companies
            [IBLOCK_NAME] => ????????
            [~IBLOCK_NAME] => ????????
            [IBLOCK_EXTERNAL_ID] =>
            [~IBLOCK_EXTERNAL_ID] =>
            [DETAIL_PAGE_URL] =>
            [~DETAIL_PAGE_URL] =>
            [LIST_PAGE_URL] =>
            [~LIST_PAGE_URL] =>
            [CANONICAL_PAGE_URL] =>
            [~CANONICAL_PAGE_URL] =>
            [CREATED_DATE] => 2015.04.13
            [~CREATED_DATE] => 2015.04.13
            [BP_PUBLISHED] => Y
            [~BP_PUBLISHED] => Y
            [PROPERTIES] => Array
                (
                    [ID_IN] => Array
                        (
                            [ID] => 304
                            [TIMESTAMP_X] => 2014-02-05 11:27:29
                            [IBLOCK_ID] => 40
                            [NAME] => ?????????? ID
                            [ACTIVE] => Y
                            [SORT] => 5
                            [CODE] => ID_IN
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:304
                            [VALUE] => 133
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => 133
                            [~DESCRIPTION] =>
                            [~NAME] => ?????????? ID
                            [~DEFAULT_VALUE] =>
                        )

                    [TYPE] => Array
                        (
                            [ID] => 211
                            [TIMESTAMP_X] => 2014-02-05 11:27:29
                            [IBLOCK_ID] => 40
                            [NAME] => ???
                            [ACTIVE] => Y
                            [SORT] => 10
                            [CODE] => TYPE
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => L
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => Y
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:211
                            [VALUE] => ??????
                            [DESCRIPTION] =>
                            [VALUE_ENUM] => ??????
                            [VALUE_XML_ID] => 0fd61d6ce065906663cd55326c64b060
                            [VALUE_SORT] => 500
                            [VALUE_ENUM_ID] => 242
                            [~VALUE] => ??????
                            [~DESCRIPTION] =>
                            [~NAME] => ???
                            [~DEFAULT_VALUE] =>
                        )

                    [BRAND_NAME] => Array
                        (
                            [ID] => 624
                            [TIMESTAMP_X] => 2015-03-05 16:15:39
                            [IBLOCK_ID] => 40
                            [NAME] => ????????? ????????????
                            [ACTIVE] => Y
                            [SORT] => 15
                            [CODE] => BRAND_NAME
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:624
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ????????? ????????????
                            [~DEFAULT_VALUE] =>
                        )

                    [CITE] => Array
                        (
                            [ID] => 290
                            [TIMESTAMP_X] => 2014-02-05 11:20:48
                            [IBLOCK_ID] => 40
                            [NAME] => ????? ?????
                            [ACTIVE] => Y
                            [SORT] => 20
                            [CODE] => CITE
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:290
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ????? ?????
                            [~DEFAULT_VALUE] =>
                        )

                    [CITY] => Array
                        (
                            [ID] => 187
                            [TIMESTAMP_X] => 2014-02-05 11:20:48
                            [IBLOCK_ID] => 40
                            [NAME] => ?????
                            [ACTIVE] => Y
                            [SORT] => 30
                            [CODE] => CITY
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => E
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 6
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:187
                            [VALUE] => 9435
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => 9435
                            [~DESCRIPTION] =>
                            [~NAME] => ?????
                            [~DEFAULT_VALUE] =>
                        )

                    [RESPONSIBLE_PERSON] => Array
                        (
                            [ID] => 379
                            [TIMESTAMP_X] => 2014-02-05 11:20:48
                            [IBLOCK_ID] => 40
                            [NAME] => ?????????? ????
                            [ACTIVE] => Y
                            [SORT] => 40
                            [CODE] => RESPONSIBLE_PERSON
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:379
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ?????????? ????
                            [~DEFAULT_VALUE] =>
                        )

                    [EMAIL] => Array
                        (
                            [ID] => 243
                            [TIMESTAMP_X] => 2014-02-05 11:20:48
                            [IBLOCK_ID] => 40
                            [NAME] => E-mail
                            [ACTIVE] => Y
                            [SORT] => 50
                            [CODE] => EMAIL
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:243
                            [VALUE] => email@test.ru
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => email@test.ru
                            [~DESCRIPTION] =>
                            [~NAME] => E-mail
                            [~DEFAULT_VALUE] =>
                        )

                    [PHONES] => Array
                        (
                            [ID] => 265
                            [TIMESTAMP_X] => 2014-02-05 11:20:48
                            [IBLOCK_ID] => 40
                            [NAME] => ????? ????????
                            [ACTIVE] => Y
                            [SORT] => 60
                            [CODE] => PHONES
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:265
                            [VALUE] => 88001234578
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => 88001234578
                            [~DESCRIPTION] =>
                            [~NAME] => ????? ????????
                            [~DEFAULT_VALUE] =>
                        )

                    [LEGAL_NAME] => Array
                        (
                            [ID] => 329
                            [TIMESTAMP_X] => 2014-02-05 11:20:48
                            [IBLOCK_ID] => 40
                            [NAME] => ??????????? ????????????
                            [ACTIVE] => Y
                            [SORT] => 70
                            [CODE] => LEGAL_NAME
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:329
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ??????????? ????????????
                            [~DEFAULT_VALUE] =>
                        )

                    [LEGAL_NAME_NDS] => Array
                        (
                            [ID] => 749
                            [TIMESTAMP_X] => 2017-12-05 13:51:33
                            [IBLOCK_ID] => 40
                            [NAME] => ??????????? ???????????? ???
                            [ACTIVE] => Y
                            [SORT] => 71
                            [CODE] => LEGAL_NAME_NDS
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:749
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ??????????? ???????????? ???
                            [~DEFAULT_VALUE] =>
                        )

                    [LEGAL_NAME_FULL] => Array
                        (
                            [ID] => 378
                            [TIMESTAMP_X] => 2014-02-05 11:20:48
                            [IBLOCK_ID] => 40
                            [NAME] => ??????????? ???????????? ?????????
                            [ACTIVE] => Y
                            [SORT] => 80
                            [CODE] => LEGAL_NAME_FULL
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:378
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ??????????? ???????????? ?????????
                            [~DEFAULT_VALUE] =>
                        )

                    [LEGAL_NAME_FULL_NDS] => Array
                        (
                            [ID] => 750
                            [TIMESTAMP_X] => 2017-12-05 13:51:33
                            [IBLOCK_ID] => 40
                            [NAME] => ??????????? ???????????? ????????? ???
                            [ACTIVE] => Y
                            [SORT] => 81
                            [CODE] => LEGAL_NAME_FULL_NDS
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:750
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ??????????? ???????????? ????????? ???
                            [~DEFAULT_VALUE] =>
                        )

                    [OWNERSHIP] => Array
                        (
                            [ID] => 472
                            [TIMESTAMP_X] => 2014-02-05 11:20:48
                            [IBLOCK_ID] => 40
                            [NAME] => ????? ?????????????
                            [ACTIVE] => Y
                            [SORT] => 90
                            [CODE] => OWNERSHIP
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => L
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:472
                            [VALUE] => ??? / ??? / ??? / ???
                            [DESCRIPTION] =>
                            [VALUE_ENUM] => ??? / ??? / ??? / ???
                            [VALUE_XML_ID] => b7d776668fdc7055e37b8cc56e310d58
                            [VALUE_SORT] => 300
                            [VALUE_ENUM_ID] => 197
                            [~VALUE] => ??? / ??? / ??? / ???
                            [~DESCRIPTION] =>
                            [~NAME] => ????? ?????????????
                            [~DEFAULT_VALUE] =>
                        )

                    [OWNERSHIP_REG] => Array
                        (
                            [ID] => 836
                            [TIMESTAMP_X] => 2020-02-26 16:47:44
                            [IBLOCK_ID] => 40
                            [NAME] => ????? ????????????? (??? ???????????)
                            [ACTIVE] => Y
                            [SORT] => 95
                            [CODE] => OWNERSHIP_REG
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => L
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:836
                            [VALUE] => ??????????? ???? / ?????????????? ???????????????
                            [DESCRIPTION] =>
                            [VALUE_ENUM] => ??????????? ???? / ?????????????? ???????????????
                            [VALUE_XML_ID] => f5b7a870fe6a3e28dbe6e56a0d560a2a
                            [VALUE_SORT] => 500
                            [VALUE_ENUM_ID] => 380
                            [~VALUE] => ??????????? ???? / ?????????????? ???????????????
                            [~DESCRIPTION] =>
                            [~NAME] => ????? ????????????? (??? ???????????)
                            [~DEFAULT_VALUE] =>
                        )

                    [ACTING] => Array
                        (
                            [ID] => 338
                            [TIMESTAMP_X] => 2014-02-05 11:20:48
                            [IBLOCK_ID] => 40
                            [NAME] => ????????? ?? ?????????
                            [ACTIVE] => Y
                            [SORT] => 100
                            [CODE] => ACTING
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:338
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ????????? ?? ?????????
                            [~DEFAULT_VALUE] =>
                        )

                    [ACTING_NDS] => Array
                        (
                            [ID] => 751
                            [TIMESTAMP_X] => 2017-12-05 13:51:33
                            [IBLOCK_ID] => 40
                            [NAME] => ????????? ?? ????????? ???
                            [ACTIVE] => Y
                            [SORT] => 101
                            [CODE] => ACTING_NDS
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:751
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ????????? ?? ????????? ???
                            [~DEFAULT_VALUE] =>
                        )

                    [RESPONSIBLE_PERSON_IN] => Array
                        (
                            [ID] => 471
                            [TIMESTAMP_X] => 2014-02-05 11:20:48
                            [IBLOCK_ID] => 40
                            [NAME] => ????????? ? ????
                            [ACTIVE] => Y
                            [SORT] => 110
                            [CODE] => RESPONSIBLE_PERSON_IN
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:471
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ????????? ? ????
                            [~DEFAULT_VALUE] =>
                        )

                    [RESPONSIBLE_PERSON_IN_NDS] => Array
                        (
                            [ID] => 752
                            [TIMESTAMP_X] => 2017-12-05 13:51:33
                            [IBLOCK_ID] => 40
                            [NAME] => ????????? ? ???? ???
                            [ACTIVE] => Y
                            [SORT] => 111
                            [CODE] => RESPONSIBLE_PERSON_IN_NDS
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:752
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ????????? ? ???? ???
                            [~DEFAULT_VALUE] =>
                        )

                    [CONTRACT] => Array
                        (
                            [ID] => 328
                            [TIMESTAMP_X] => 2014-02-05 11:20:48
                            [IBLOCK_ID] => 40
                            [NAME] => ????????? ????????
                            [ACTIVE] => Y
                            [SORT] => 120
                            [CODE] => CONTRACT
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:328
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ????????? ????????
                            [~DEFAULT_VALUE] =>
                        )

                    [CONTRACT_TYPE] => Array
                        (
                            [ID] => 466
                            [TIMESTAMP_X] => 2014-02-05 11:20:48
                            [IBLOCK_ID] => 40
                            [NAME] => ??? ????????
                            [ACTIVE] => Y
                            [SORT] => 130
                            [CODE] => CONTRACT_TYPE
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => L
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:466
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [VALUE_ENUM_ID] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ??? ????????
                            [~DEFAULT_VALUE] =>
                        )

                    [REPORT_SIGNS] => Array
                        (
                            [ID] => 473
                            [TIMESTAMP_X] => 2014-02-05 11:23:10
                            [IBLOCK_ID] => 40
                            [NAME] => ????? ???????????
                            [ACTIVE] => Y
                            [SORT] => 140
                            [CODE] => REPORT_SIGNS
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:473
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ????? ???????????
                            [~DEFAULT_VALUE] =>
                        )

                    [REPORT_SIGNS_NDS] => Array
                        (
                            [ID] => 753
                            [TIMESTAMP_X] => 2017-12-05 13:51:34
                            [IBLOCK_ID] => 40
                            [NAME] => ????? ??????????? ???
                            [ACTIVE] => Y
                            [SORT] => 141
                            [CODE] => REPORT_SIGNS_NDS
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:753
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ????? ??????????? ???
                            [~DEFAULT_VALUE] =>
                        )

                    [TYPE_IM] => Array
                        (
                            [ID] => 491
                            [TIMESTAMP_X] => 2014-06-02 12:40:28
                            [IBLOCK_ID] => 40
                            [NAME] => ??? ????????-????????
                            [ACTIVE] => Y
                            [SORT] => 145
                            [CODE] => TYPE_IM
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => L
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:491
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [VALUE_ENUM_ID] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ??? ????????-????????
                            [~DEFAULT_VALUE] =>
                        )

                    [PERCENT] => Array
                        (
                            [ID] => 227
                            [TIMESTAMP_X] => 2014-02-05 11:23:50
                            [IBLOCK_ID] => 40
                            [NAME] => ??????? 1
                            [ACTIVE] => Y
                            [SORT] => 150
                            [CODE] => PERCENT
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => N
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:227
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ??????? 1
                            [~DEFAULT_VALUE] =>
                        )

                    [PERCENT_2] => Array
                        (
                            [ID] => 308
                            [TIMESTAMP_X] => 2014-02-05 11:23:50
                            [IBLOCK_ID] => 40
                            [NAME] => ??????? 2
                            [ACTIVE] => Y
                            [SORT] => 160
                            [CODE] => PERCENT_2
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => N
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:308
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ??????? 2
                            [~DEFAULT_VALUE] =>
                        )

                    [PERCENT_3] => Array
                        (
                            [ID] => 314
                            [TIMESTAMP_X] => 2014-02-05 11:23:50
                            [IBLOCK_ID] => 40
                            [NAME] => ??????? 3
                            [ACTIVE] => Y
                            [SORT] => 170
                            [CODE] => PERCENT_3
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => N
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:314
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ??????? 3
                            [~DEFAULT_VALUE] =>
                        )

                    [PRICE] => Array
                        (
                            [ID] => 251
                            [TIMESTAMP_X] => 2014-02-05 11:23:50
                            [IBLOCK_ID] => 40
                            [NAME] => ?????-????
                            [ACTIVE] => Y
                            [SORT] => 180
                            [CODE] => PRICE
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => E
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 51
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:251
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ?????-????
                            [~DEFAULT_VALUE] =>
                        )

                    [PRICE_2] => Array
                        (
                            [ID] => 372
                            [TIMESTAMP_X] => 2014-02-05 11:23:50
                            [IBLOCK_ID] => 40
                            [NAME] => ?????-???? 2
                            [ACTIVE] => Y
                            [SORT] => 190
                            [CODE] => PRICE_2
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => E
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 51
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:372
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ?????-???? 2
                            [~DEFAULT_VALUE] =>
                        )

                    [PRICE_3] => Array
                        (
                            [ID] => 403
                            [TIMESTAMP_X] => 2014-02-05 11:23:50
                            [IBLOCK_ID] => 40
                            [NAME] => ?????-???? ?? ?????
                            [ACTIVE] => Y
                            [SORT] => 200
                            [CODE] => PRICE_3
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => E
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 51
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:403
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ?????-???? ?? ?????
                            [~DEFAULT_VALUE] =>
                        )

                    [COST_ORDERING] => Array
                        (
                            [ID] => 374
                            [TIMESTAMP_X] => 2014-02-05 11:23:50
                            [IBLOCK_ID] => 40
                            [NAME] => ????????? ???????????? ??????
                            [ACTIVE] => Y
                            [SORT] => 210
                            [CODE] => COST_ORDERING
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => N
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:374
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ????????? ???????????? ??????
                            [~DEFAULT_VALUE] =>
                        )

                    [TARIFF_TD] => Array
                        (
                            [ID] => 502
                            [TIMESTAMP_X] => 2014-08-04 11:00:13
                            [IBLOCK_ID] => 40
                            [NAME] => ????? TopDelivery
                            [ACTIVE] => Y
                            [SORT] => 215
                            [CODE] => TARIFF_TD
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => L
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:502
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [VALUE_ENUM_ID] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ????? TopDelivery
                            [~DEFAULT_VALUE] =>
                        )

                    [SELECTION_VAT_REPORT] => Array
                        (
                            [ID] => 747
                            [TIMESTAMP_X] => 2017-12-04 13:40:31
                            [IBLOCK_ID] => 40
                            [NAME] => ???????? ? ?????? ???
                            [ACTIVE] => Y
                            [SORT] => 216
                            [CODE] => SELECTION_VAT_REPORT
                            [DEFAULT_VALUE] => 0
                            [PROPERTY_TYPE] => N
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] => SASDCheckboxNum
                            [USER_TYPE_SETTINGS] => Array
                                (
                                    [VIEW] => Array
                                        (
                                            [0] => ???
                                            [1] => ??
                                        )

                                )

                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:747
                            [VALUE] => 0
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => 0
                            [~DESCRIPTION] =>
                            [~NAME] => ???????? ? ?????? ???
                            [~DEFAULT_VALUE] => 0
                        )

                    [SUBTRACT_AMOUNT_COD] => Array
                        (
                            [ID] => 748
                            [TIMESTAMP_X] => 2017-12-04 13:36:45
                            [IBLOCK_ID] => 40
                            [NAME] => ???????? ????? ????? ?? ??????????? ???????
                            [ACTIVE] => Y
                            [SORT] => 217
                            [CODE] => SUBTRACT_AMOUNT_COD
                            [DEFAULT_VALUE] => 1
                            [PROPERTY_TYPE] => N
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] => SASDCheckboxNum
                            [USER_TYPE_SETTINGS] => Array
                                (
                                    [VIEW] => Array
                                        (
                                            [0] => ???
                                            [1] => ??
                                        )

                                )

                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:748
                            [VALUE] => 0
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => 0
                            [~DESCRIPTION] =>
                            [~NAME] => ???????? ????? ????? ?? ??????????? ???????
                            [~DEFAULT_VALUE] => 1
                        )

                    [ADRESS] => Array
                        (
                            [ID] => 190
                            [TIMESTAMP_X] => 2018-06-14 16:41:21
                            [IBLOCK_ID] => 40
                            [NAME] => ??????????? ?????
                            [ACTIVE] => Y
                            [SORT] => 220
                            [CODE] => ADRESS
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:190
                            [VALUE] => ???????? ?????
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => ???????? ?????
                            [~DESCRIPTION] =>
                            [~NAME] => ??????????? ?????
                            [~DEFAULT_VALUE] =>
                        )

                    [ADRESS_FACT] => Array
                        (
                            [ID] => 625
                            [TIMESTAMP_X] => 2018-06-14 16:41:21
                            [IBLOCK_ID] => 40
                            [NAME] => ??????????? ?????
                            [ACTIVE] => Y
                            [SORT] => 225
                            [CODE] => ADRESS_FACT
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:625
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ??????????? ?????
                            [~DEFAULT_VALUE] =>
                        )

                    [INN] => Array
                        (
                            [ID] => 237
                            [TIMESTAMP_X] => 2017-06-07 15:18:05
                            [IBLOCK_ID] => 40
                            [NAME] => ID ?????? (???)
                            [ACTIVE] => Y
                            [SORT] => 230
                            [CODE] => INN
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:237
                            [VALUE] => 34327677
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => 34327677
                            [~DESCRIPTION] =>
                            [~NAME] => ID ?????? (???)
                            [~DEFAULT_VALUE] =>
                        )

                    [INN_REAL] => Array
                        (
                            [ID] => 729
                            [TIMESTAMP_X] => 2019-01-10 10:56:42
                            [IBLOCK_ID] => 40
                            [NAME] => ???
                            [ACTIVE] => Y
                            [SORT] => 235
                            [CODE] => INN_REAL
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:729
                            [VALUE] => 7700770077
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => 7700770077
                            [~DESCRIPTION] =>
                            [~NAME] => ???
                            [~DEFAULT_VALUE] =>
                        )

                    [ACCOUNT] => Array
                        (
                            [ID] => 219
                            [TIMESTAMP_X] => 2014-02-05 11:25:20
                            [IBLOCK_ID] => 40
                            [NAME] => ????
                            [ACTIVE] => Y
                            [SORT] => 240
                            [CODE] => ACCOUNT
                            [DEFAULT_VALUE] => 0
                            [PROPERTY_TYPE] => N
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:219
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ????
                            [~DEFAULT_VALUE] => 0
                        )

                    [SHOW_DELIVERY_BEFORE_DATE] => Array
                        (
                            [ID] => 774
                            [TIMESTAMP_X] => 2019-08-08 12:28:35
                            [IBLOCK_ID] => 40
                            [NAME] => ??????-???????? ????????? ??
                            [ACTIVE] => Y
                            [SORT] => 500
                            [CODE] => SHOW_DELIVERY_BEFORE_DATE
                            [DEFAULT_VALUE] => 0
                            [PROPERTY_TYPE] => N
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] => SASDCheckboxNum
                            [USER_TYPE_SETTINGS] => Array
                                (
                                    [VIEW] => Array
                                        (
                                            [0] => ???
                                            [1] => ??
                                        )

                                )

                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:774
                            [VALUE] => 0
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => 0
                            [~DESCRIPTION] =>
                            [~NAME] => ??????-???????? ????????? ??
                            [~DEFAULT_VALUE] => 0
                        )

                    [CITIES] => Array
                        (
                            [ID] => 188
                            [TIMESTAMP_X] => 2013-12-06 10:10:35
                            [IBLOCK_ID] => 40
                            [NAME] => ??????
                            [ACTIVE] => Y
                            [SORT] => 810
                            [CODE] => CITIES
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => E
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => Y
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 6
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] =>
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ??????
                            [~DEFAULT_VALUE] =>
                        )

                    [REGION] => Array
                        (
                            [ID] => 494
                            [TIMESTAMP_X] => 2014-06-23 16:56:45
                            [IBLOCK_ID] => 40
                            [NAME] => ???????
                            [ACTIVE] => Y
                            [SORT] => 811
                            [CODE] => REGION
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => G
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => Y
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 6
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] =>
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ???????
                            [~DEFAULT_VALUE] =>
                        )

                    [PVZ] => Array
                        (
                            [ID] => 330
                            [TIMESTAMP_X] => 2014-02-05 11:28:00
                            [IBLOCK_ID] => 40
                            [NAME] => ???
                            [ACTIVE] => Y
                            [SORT] => 820
                            [CODE] => PVZ
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => Y
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] =>
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ???
                            [~DEFAULT_VALUE] =>
                        )

                    [SHOW_HIDDEN_INNER_NUMBER] => Array
                        (
                            [ID] => 773
                            [TIMESTAMP_X] => 2019-08-08 10:31:40
                            [IBLOCK_ID] => 40
                            [NAME] => ??????-???????? ?????????? ?????
                            [ACTIVE] => Y
                            [SORT] => 900
                            [CODE] => SHOW_HIDDEN_INNER_NUMBER
                            [DEFAULT_VALUE] => 1
                            [PROPERTY_TYPE] => N
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] => SASDCheckboxNum
                            [USER_TYPE_SETTINGS] => Array
                                (
                                    [VIEW] => Array
                                        (
                                            [0] => ???
                                            [1] => ??
                                        )

                                )

                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:773
                            [VALUE] => 1
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => 1
                            [~DESCRIPTION] =>
                            [~NAME] => ??????-???????? ?????????? ?????
                            [~DEFAULT_VALUE] => 1
                        )

                    [SETTINGS] => Array
                        (
                            [ID] => 474
                            [TIMESTAMP_X] => 2014-02-05 11:28:00
                            [IBLOCK_ID] => 40
                            [NAME] => ?????????
                            [ACTIVE] => Y
                            [SORT] => 910
                            [CODE] => SETTINGS
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => E
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 47
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:474
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ?????????
                            [~DEFAULT_VALUE] =>
                        )

                    [USER] => Array
                        (
                            [ID] => 186
                            [TIMESTAMP_X] => 2014-02-05 11:21:22
                            [IBLOCK_ID] => 40
                            [NAME] => ????????????
                            [ACTIVE] => Y
                            [SORT] => 1000
                            [CODE] => USER
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => Y
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] => UserID
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => Array
                                (
                                    [0] => 35671
                                )

                            [VALUE] => Array
                                (
                                    [0] => 4359
                                )

                            [DESCRIPTION] => Array
                                (
                                    [0] =>
                                )

                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => Array
                                (
                                    [0] => 4359
                                )

                            [~DESCRIPTION] => Array
                                (
                                    [0] =>
                                )

                            [~NAME] => ????????????
                            [~DEFAULT_VALUE] =>
                        )

                    [MAIL_SETTINGS] => Array
                        (
                            [ID] => 258
                            [TIMESTAMP_X] => 2015-03-18 10:14:55
                            [IBLOCK_ID] => 40
                            [NAME] => ???????? ?????????
                            [ACTIVE] => Y
                            [SORT] => 1005
                            [CODE] => MAIL_SETTINGS
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => L
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => C
                            [MULTIPLE] => Y
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] =>
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [VALUE_ENUM_ID] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ???????? ?????????
                            [~DEFAULT_VALUE] =>
                        )

                    [UK] => Array
                        (
                            [ID] => 467
                            [TIMESTAMP_X] => 2014-02-05 11:27:29
                            [IBLOCK_ID] => 40
                            [NAME] => ??????????? ????????
                            [ACTIVE] => Y
                            [SORT] => 1010
                            [CODE] => UK
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => E
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 40
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:467
                            [VALUE] => 2197189
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => 2197189
                            [~DESCRIPTION] =>
                            [~NAME] => ??????????? ????????
                            [~DEFAULT_VALUE] =>
                        )

                    [DEMO] => Array
                        (
                            [ID] => 252
                            [TIMESTAMP_X] => 2014-04-28 12:26:31
                            [IBLOCK_ID] => 40
                            [NAME] => ????-??????
                            [ACTIVE] => Y
                            [SORT] => 1020
                            [CODE] => DEMO
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => L
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:252
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [VALUE_ENUM_ID] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ????-??????
                            [~DEFAULT_VALUE] =>
                        )

                    [DEFAULT_CITY] => Array
                        (
                            [ID] => 310
                            [TIMESTAMP_X] => 2014-02-05 11:27:29
                            [IBLOCK_ID] => 40
                            [NAME] => ?? ?????????: ????? ????????
                            [ACTIVE] => Y
                            [SORT] => 1030
                            [CODE] => DEFAULT_CITY
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => E
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 6
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:310
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ?? ?????????: ????? ????????
                            [~DEFAULT_VALUE] =>
                        )

                    [DEFAULT_DELIVERY] => Array
                        (
                            [ID] => 311
                            [TIMESTAMP_X] => 2014-02-05 11:27:29
                            [IBLOCK_ID] => 40
                            [NAME] => ?? ?????????: ?????? ????????
                            [ACTIVE] => Y
                            [SORT] => 1040
                            [CODE] => DEFAULT_DELIVERY
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => L
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:311
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [VALUE_ENUM_ID] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ?? ?????????: ?????? ????????
                            [~DEFAULT_VALUE] =>
                        )

                    [DEFAULT_CASH] => Array
                        (
                            [ID] => 312
                            [TIMESTAMP_X] => 2014-02-05 11:27:29
                            [IBLOCK_ID] => 40
                            [NAME] => ?? ?????????: ???????? ????????????
                            [ACTIVE] => Y
                            [SORT] => 1050
                            [CODE] => DEFAULT_CASH
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => L
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:312
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [VALUE_ENUM_ID] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ?? ?????????: ???????? ????????????
                            [~DEFAULT_VALUE] =>
                        )

                    [FOLDER] => Array
                        (
                            [ID] => 303
                            [TIMESTAMP_X] => 2014-02-05 11:27:29
                            [IBLOCK_ID] => 40
                            [NAME] => ????? ???????
                            [ACTIVE] => Y
                            [SORT] => 1060
                            [CODE] => FOLDER
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => G
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 62
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:303
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ????? ???????
                            [~DEFAULT_VALUE] =>
                        )

                    [PREFIX] => Array
                        (
                            [ID] => 359
                            [TIMESTAMP_X] => 2014-02-05 11:27:29
                            [IBLOCK_ID] => 40
                            [NAME] => ??????? ???????? ???????
                            [ACTIVE] => Y
                            [SORT] => 1070
                            [CODE] => PREFIX
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:359
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ??????? ???????? ???????
                            [~DEFAULT_VALUE] =>
                        )

                    [PREFIX_REPORTS] => Array
                        (
                            [ID] => 377
                            [TIMESTAMP_X] => 2019-01-18 09:45:48
                            [IBLOCK_ID] => 40
                            [NAME] => ???????
                            [ACTIVE] => Y
                            [SORT] => 1080
                            [CODE] => PREFIX_REPORTS
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:377
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ???????
                            [~DEFAULT_VALUE] =>
                        )

                    [ON_PAGE] => Array
                        (
                            [ID] => 477
                            [TIMESTAMP_X] => 2014-02-26 10:23:40
                            [IBLOCK_ID] => 40
                            [NAME] => ?????????? ????????? ?? ????????
                            [ACTIVE] => Y
                            [SORT] => 1200
                            [CODE] => ON_PAGE
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => L
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:477
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [VALUE_ENUM_ID] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ?????????? ????????? ?? ????????
                            [~DEFAULT_VALUE] =>
                        )

                    [CONDITIONS] => Array
                        (
                            [ID] => 492
                            [TIMESTAMP_X] => 2014-06-02 16:33:42
                            [IBLOCK_ID] => 40
                            [NAME] => ??????? ?????? ??
                            [ACTIVE] => Y
                            [SORT] => 1300
                            [CODE] => CONDITIONS
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => L
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:492
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [VALUE_ENUM_ID] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ??????? ?????? ??
                            [~DEFAULT_VALUE] =>
                        )

                    [UKEY] => Array
                        (
                            [ID] => 496
                            [TIMESTAMP_X] => 2014-06-26 11:30:51
                            [IBLOCK_ID] => 40
                            [NAME] => ???? ???????????
                            [ACTIVE] => Y
                            [SORT] => 1400
                            [CODE] => UKEY
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:496
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ???? ???????????
                            [~DEFAULT_VALUE] =>
                        )

                    [CODE_1C] => Array
                        (
                            [ID] => 543
                            [TIMESTAMP_X] => 2014-10-14 12:52:41
                            [IBLOCK_ID] => 40
                            [NAME] => ??? ??????????? 1?
                            [ACTIVE] => Y
                            [SORT] => 1500
                            [CODE] => CODE_1C
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:543
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ??? ??????????? 1?
                            [~DEFAULT_VALUE] =>
                        )

                    [BRANCH] => Array
                        (
                            [ID] => 610
                            [TIMESTAMP_X] => 2014-12-29 12:29:47
                            [IBLOCK_ID] => 40
                            [NAME] => ???????? ???????????? ????
                            [ACTIVE] => Y
                            [SORT] => 1550
                            [CODE] => BRANCH
                            [DEFAULT_VALUE] => 0
                            [PROPERTY_TYPE] => N
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] => SASDCheckboxNum
                            [USER_TYPE_SETTINGS] => Array
                                (
                                    [VIEW] => Array
                                        (
                                            [0] => ???
                                            [1] => ??
                                        )

                                )

                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:610
                            [VALUE] => 0
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => 0
                            [~DESCRIPTION] =>
                            [~NAME] => ???????? ???????????? ????
                            [~DEFAULT_VALUE] => 0
                        )

                    [TYPE_AGENT] => Array
                        (
                            [ID] => 670
                            [TIMESTAMP_X] => 2015-06-19 16:34:10
                            [IBLOCK_ID] => 40
                            [NAME] => ??? ??????
                            [ACTIVE] => Y
                            [SORT] => 1555
                            [CODE] => TYPE_AGENT
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => L
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:670
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [VALUE_ENUM_ID] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ??? ??????
                            [~DEFAULT_VALUE] =>
                        )

                    [AVAILABLE_FOR_AGENT] => Array
                        (
                            [ID] => 635
                            [TIMESTAMP_X] => 2015-03-31 14:49:53
                            [IBLOCK_ID] => 40
                            [NAME] => ?????? ???????? ??? ???????
                            [ACTIVE] => Y
                            [SORT] => 1600
                            [CODE] => AVAILABLE_FOR_AGENT
                            [DEFAULT_VALUE] => 0
                            [PROPERTY_TYPE] => N
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] => SASDCheckboxNum
                            [USER_TYPE_SETTINGS] => Array
                                (
                                    [VIEW] => Array
                                        (
                                            [0] => ???
                                            [1] => ??
                                        )

                                )

                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:635
                            [VALUE] => 0
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => 0
                            [~DESCRIPTION] =>
                            [~NAME] => ?????? ???????? ??? ???????
                            [~DEFAULT_VALUE] => 0
                        )

                    [BY_AGENT] => Array
                        (
                            [ID] => 714
                            [TIMESTAMP_X] => 2017-08-29 12:53:26
                            [IBLOCK_ID] => 40
                            [NAME] => ????????????? ???????
                            [ACTIVE] => Y
                            [SORT] => 1620
                            [CODE] => BY_AGENT
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => E
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => Y
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 40
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] =>
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ????????????? ???????
                            [~DEFAULT_VALUE] =>
                        )

                    [IM_BY] => Array
                        (
                            [ID] => 671
                            [TIMESTAMP_X] => 2015-07-03 12:13:00
                            [IBLOCK_ID] => 40
                            [NAME] => ?? ?????????? ???????????
                            [ACTIVE] => Y
                            [SORT] => 1650
                            [CODE] => IM_BY
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => L
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:671
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [VALUE_ENUM_ID] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ?? ?????????? ???????????
                            [~DEFAULT_VALUE] =>
                        )

                    [LAST_DATE_AGENT] => Array
                        (
                            [ID] => 678
                            [TIMESTAMP_X] => 2015-10-21 10:54:54
                            [IBLOCK_ID] => 40
                            [NAME] => ???? ????????? ?????? ??????
                            [ACTIVE] => Y
                            [SORT] => 1700
                            [CODE] => LAST_DATE_AGENT
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] => DateTime
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:678
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ???? ????????? ?????? ??????
                            [~DEFAULT_VALUE] =>
                        )

                    [COEFFICIENT_VW] => Array
                        (
                            [ID] => 681
                            [TIMESTAMP_X] => 2016-01-18 13:21:23
                            [IBLOCK_ID] => 40
                            [NAME] => ??????????? ????????? ????
                            [ACTIVE] => Y
                            [SORT] => 1750
                            [CODE] => COEFFICIENT_VW
                            [DEFAULT_VALUE] => 6000
                            [PROPERTY_TYPE] => N
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:681
                            [VALUE] => 5000
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => 5000
                            [~DESCRIPTION] =>
                            [~NAME] => ??????????? ????????? ????
                            [~DEFAULT_VALUE] => 6000
                        )

                    [ADDITIONAL_ADDRESSES] => Array
                        (
                            [ID] => 684
                            [TIMESTAMP_X] => 2016-04-19 17:10:40
                            [IBLOCK_ID] => 40
                            [NAME] => ?????????????? ??????
                            [ACTIVE] => Y
                            [SORT] => 1800
                            [CODE] => ADDITIONAL_ADDRESSES
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:684
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ?????????????? ??????
                            [~DEFAULT_VALUE] =>
                        )

                    [TYPE_WORK_BRANCHES] => Array
                        (
                            [ID] => 696
                            [TIMESTAMP_X] => 2016-12-06 19:07:35
                            [IBLOCK_ID] => 40
                            [NAME] => ??? ?????? ????????
                            [ACTIVE] => Y
                            [SORT] => 1850
                            [CODE] => TYPE_WORK_BRANCHES
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => L
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:696
                            [VALUE] =>
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [VALUE_ENUM_ID] =>
                            [~VALUE] =>
                            [~DESCRIPTION] =>
                            [~NAME] => ??? ?????? ????????
                            [~DEFAULT_VALUE] =>
                        )

                    [SHOW_LIMITS] => Array
                        (
                            [ID] => 697
                            [TIMESTAMP_X] => 2016-12-06 19:36:11
                            [IBLOCK_ID] => 40
                            [NAME] => ?????????? ??????
                            [ACTIVE] => Y
                            [SORT] => 1900
                            [CODE] => SHOW_LIMITS
                            [DEFAULT_VALUE] => 0
                            [PROPERTY_TYPE] => N
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] => SASDCheckboxNum
                            [USER_TYPE_SETTINGS] => Array
                                (
                                    [VIEW] => Array
                                        (
                                            [0] => ???
                                            [1] => ??
                                        )

                                )

                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:697
                            [VALUE] => 0
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => 0
                            [~DESCRIPTION] =>
                            [~NAME] => ?????????? ??????
                            [~DEFAULT_VALUE] => 0
                        )

                    [TYPE_FACE] => Array
                        (
                            [ID] => 719
                            [TIMESTAMP_X] => 2017-04-19 10:12:20
                            [IBLOCK_ID] => 40
                            [NAME] => ??? ????
                            [ACTIVE] => Y
                            [SORT] => 1920
                            [CODE] => TYPE_FACE
                            [DEFAULT_VALUE] =>
                            [PROPERTY_TYPE] => L
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] =>
                            [USER_TYPE_SETTINGS] =>
                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:719
                            [VALUE] => ???????????
                            [DESCRIPTION] =>
                            [VALUE_ENUM] => ???????????
                            [VALUE_XML_ID] => 6162d34019753377f1b4d1fb05953e9a
                            [VALUE_SORT] => 10
                            [VALUE_ENUM_ID] => 310
                            [~VALUE] => ???????????
                            [~DESCRIPTION] =>
                            [~NAME] => ??? ????
                            [~DEFAULT_VALUE] =>
                        )

                    [ACCOUNT_LK_SETTINGS] => Array
                        (
                            [ID] => 730
                            [TIMESTAMP_X] => 2017-06-28 12:41:59
                            [IBLOCK_ID] => 40
                            [NAME] => ????????? ????????????? ??
                            [ACTIVE] => Y
                            [SORT] => 1930
                            [CODE] => ACCOUNT_LK_SETTINGS
                            [DEFAULT_VALUE] => Array
                                (
                                    [TEXT] =>
                                    [TYPE] => HTML
                                )

                            [PROPERTY_TYPE] => S
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] => HTML
                            [USER_TYPE_SETTINGS] => Array
                                (
                                    [height] => 200
                                )

                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:730
                            [VALUE] => Array
                                (
                                    [TYPE] => TEXT
                                    [TEXT] => {&quot;1746&quot;:{&quot;CALLCOURIER&quot;:&quot;&quot;,&quot;CHOICE_COMPANY&quot;:&quot;1&quot;,&quot;TYPE_DELIVERY&quot;:&quot;308&quot;,&quot;WHO_DELIVERY&quot;:null,&quot;TYPE_PACK&quot;:null,&quot;TYPE_PAYS&quot;:null,&quot;PAYMENT&quot;:&quot;255&quot;,&quot;MERGE_RECIPIENTS&quot;:&quot;N&quot;,&quot;MERGE_SENDERS&quot;:&quot;N&quot;,&quot;DATE_CALLCOURIER&quot;:&quot;1&quot;,&quot;SENDER_DEFAULT&quot;:&quot;22235070&quot;},&quot;4359&quot;:{&quot;SENDER_DEFAULT&quot;:&quot;22234975&quot;,&quot;TYPE_PACK&quot;:&quot;246&quot;,&quot;CALLCOURIER&quot;:&quot;&quot;,&quot;TARIF_DEFAULT&quot;:&quot;N&quot;,&quot;TARIF_NON_DEFAULT&quot;:&quot;Y&quot;}}
                                )

                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => Array
                                (
                                    [TYPE] => TEXT
                                    [TEXT] => {"1746":{"CALLCOURIER":"","CHOICE_COMPANY":"1","TYPE_DELIVERY":"308","WHO_DELIVERY":null,"TYPE_PACK":null,"TYPE_PAYS":null,"PAYMENT":"255","MERGE_RECIPIENTS":"N","MERGE_SENDERS":"N","DATE_CALLCOURIER":"1","SENDER_DEFAULT":"22235070"},"4359":{"SENDER_DEFAULT":"22234975","TYPE_PACK":"246","CALLCOURIER":"","TARIF_DEFAULT":"N","TARIF_NON_DEFAULT":"Y"}}
                                )

                            [~DESCRIPTION] =>
                            [~NAME] => ????????? ????????????? ??
                            [~DEFAULT_VALUE] => Array
                                (
                                    [TEXT] =>
                                    [TYPE] => HTML
                                )

                        )

                    [AVAILABLE_WH_WH] => Array
                        (
                            [ID] => 746
                            [TIMESTAMP_X] => 2017-11-24 13:46:10
                            [IBLOCK_ID] => 40
                            [NAME] => ???????? ?????-?????
                            [ACTIVE] => Y
                            [SORT] => 1940
                            [CODE] => AVAILABLE_WH_WH
                            [DEFAULT_VALUE] => 0
                            [PROPERTY_TYPE] => N
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] => SASDCheckboxNum
                            [USER_TYPE_SETTINGS] => Array
                                (
                                    [VIEW] => Array
                                        (
                                            [0] => ???
                                            [1] => ??
                                        )

                                )

                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:746
                            [VALUE] => 1
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => 1
                            [~DESCRIPTION] =>
                            [~NAME] => ???????? ?????-?????
                            [~DEFAULT_VALUE] => 0
                        )

                    [AVAILABLE_CALL_COURIER] => Array
                        (
                            [ID] => 762
                            [TIMESTAMP_X] => 2019-04-16 21:58:43
                            [IBLOCK_ID] => 40
                            [NAME] => ?????? ????? ???????
                            [ACTIVE] => Y
                            [SORT] => 1945
                            [CODE] => AVAILABLE_CALL_COURIER
                            [DEFAULT_VALUE] => 0
                            [PROPERTY_TYPE] => N
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => Y
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] => SASDCheckboxNum
                            [USER_TYPE_SETTINGS] => Array
                                (
                                    [VIEW] => Array
                                        (
                                            [0] => ???
                                            [1] => ??
                                        )

                                )

                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:762
                            [VALUE] => 1
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => 1
                            [~DESCRIPTION] =>
                            [~NAME] => ?????? ????? ???????
                            [~DEFAULT_VALUE] => 0
                        )

                    [AVAILABLE_EXPRESS2] => Array
                        (
                            [ID] => 765
                            [TIMESTAMP_X] => 2019-07-29 10:54:28
                            [IBLOCK_ID] => 40
                            [NAME] => ??????-???????? ???????? 2
                            [ACTIVE] => Y
                            [SORT] => 1950
                            [CODE] => AVAILABLE_EXPRESS2
                            [DEFAULT_VALUE] => 0
                            [PROPERTY_TYPE] => N
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] => SASDCheckboxNum
                            [USER_TYPE_SETTINGS] => Array
                                (
                                    [VIEW] => Array
                                        (
                                            [0] => ???
                                            [1] => ??
                                        )

                                )

                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:765
                            [VALUE] => 0
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => 0
                            [~DESCRIPTION] =>
                            [~NAME] => ??????-???????? ???????? 2
                            [~DEFAULT_VALUE] => 0
                        )

                    [AVAILABLE_EXPRESS4] => Array
                        (
                            [ID] => 766
                            [TIMESTAMP_X] => 2019-07-29 10:54:28
                            [IBLOCK_ID] => 40
                            [NAME] => ??????-???????? ???????? 4
                            [ACTIVE] => Y
                            [SORT] => 1955
                            [CODE] => AVAILABLE_EXPRESS4
                            [DEFAULT_VALUE] => 0
                            [PROPERTY_TYPE] => N
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] => SASDCheckboxNum
                            [USER_TYPE_SETTINGS] => Array
                                (
                                    [VIEW] => Array
                                        (
                                            [0] => ???
                                            [1] => ??
                                        )

                                )

                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:766
                            [VALUE] => 0
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => 0
                            [~DESCRIPTION] =>
                            [~NAME] => ??????-???????? ???????? 4
                            [~DEFAULT_VALUE] => 0
                        )

                    [AVAILABLE_EXPRESS8] => Array
                        (
                            [ID] => 767
                            [TIMESTAMP_X] => 2019-07-29 10:54:28
                            [IBLOCK_ID] => 40
                            [NAME] => ??????-???????? ???????? 8
                            [ACTIVE] => Y
                            [SORT] => 1960
                            [CODE] => AVAILABLE_EXPRESS8
                            [DEFAULT_VALUE] => 0
                            [PROPERTY_TYPE] => N
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] => SASDCheckboxNum
                            [USER_TYPE_SETTINGS] => Array
                                (
                                    [VIEW] => Array
                                        (
                                            [0] => ???
                                            [1] => ??
                                        )

                                )

                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:767
                            [VALUE] => 1
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => 1
                            [~DESCRIPTION] =>
                            [~NAME] => ??????-???????? ???????? 8
                            [~DEFAULT_VALUE] => 0
                        )

                    [AVAILABLE_EXPRESS] => Array
                        (
                            [ID] => 770
                            [TIMESTAMP_X] => 2019-07-29 10:55:53
                            [IBLOCK_ID] => 40
                            [NAME] => ??????-???????? ????????
                            [ACTIVE] => Y
                            [SORT] => 1963
                            [CODE] => AVAILABLE_EXPRESS
                            [DEFAULT_VALUE] => 0
                            [PROPERTY_TYPE] => N
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] => SASDCheckboxNum
                            [USER_TYPE_SETTINGS] => Array
                                (
                                    [VIEW] => Array
                                        (
                                            [0] => ???
                                            [1] => ??
                                        )

                                )

                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:770
                            [VALUE] => 1
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => 1
                            [~DESCRIPTION] =>
                            [~NAME] => ??????-???????? ????????
                            [~DEFAULT_VALUE] => 0
                        )

                    [AVAILABLE_STANDART] => Array
                        (
                            [ID] => 768
                            [TIMESTAMP_X] => 2019-07-29 10:54:28
                            [IBLOCK_ID] => 40
                            [NAME] => ??????-???????? ????????
                            [ACTIVE] => Y
                            [SORT] => 1965
                            [CODE] => AVAILABLE_STANDART
                            [DEFAULT_VALUE] => 0
                            [PROPERTY_TYPE] => N
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] => SASDCheckboxNum
                            [USER_TYPE_SETTINGS] => Array
                                (
                                    [VIEW] => Array
                                        (
                                            [0] => ???
                                            [1] => ??
                                        )

                                )

                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:768
                            [VALUE] => 1
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => 1
                            [~DESCRIPTION] =>
                            [~NAME] => ??????-???????? ????????
                            [~DEFAULT_VALUE] => 0
                        )

                    [AVAILABLE_ECONOME] => Array
                        (
                            [ID] => 769
                            [TIMESTAMP_X] => 2019-07-29 10:54:28
                            [IBLOCK_ID] => 40
                            [NAME] => ??????-???????? ??????
                            [ACTIVE] => Y
                            [SORT] => 1970
                            [CODE] => AVAILABLE_ECONOME
                            [DEFAULT_VALUE] => 0
                            [PROPERTY_TYPE] => N
                            [ROW_COUNT] => 1
                            [COL_COUNT] => 30
                            [LIST_TYPE] => L
                            [MULTIPLE] => N
                            [XML_ID] =>
                            [FILE_TYPE] =>
                            [MULTIPLE_CNT] => 5
                            [TMP_ID] =>
                            [LINK_IBLOCK_ID] => 0
                            [WITH_DESCRIPTION] => N
                            [SEARCHABLE] => N
                            [FILTRABLE] => N
                            [IS_REQUIRED] => N
                            [VERSION] => 2
                            [USER_TYPE] => SASDCheckboxNum
                            [USER_TYPE_SETTINGS] => Array
                                (
                                    [VIEW] => Array
                                        (
                                            [0] => ???
                                            [1] => ??
                                        )

                                )

                            [HINT] =>
                            [PROPERTY_VALUE_ID] => 9528186:769
                            [VALUE] => 1
                            [DESCRIPTION] =>
                            [VALUE_ENUM] =>
                            [VALUE_XML_ID] =>
                            [VALUE_SORT] =>
                            [~VALUE] => 1
                            [~DESCRIPTION] =>
                            [~NAME] => ??????-???????? ??????
                            [~DEFAULT_VALUE] => 0
                        )

                )

        )


*/