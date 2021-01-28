<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

$mode = 'add';

if ($arResult['MODE'] == 'add')
{
    $arResult['CURRENT_CLIENT_COEFFICIENT_VW'] = 5000;

    $arResult['DEAULTS'] = array(
        'callcourierdate' => date('d.m.Y', strtotime("+1 day")),
        'callcouriertime_from' => '10:00',
        'callcouriertime_to' => '18:00',
        'PLACES' => 1,
        'TYPE_DELIVERY' => 244,
        'TYPE_PACK' => 246,
        'WHO_DELIVERY' => 248,
        'TYPE_PAYS' => 251,
        'PAYMENT' => 256,
        'WEIGHT' => '0,2'
    );
    
    if (isset($_POST['add-print']))
    {
        if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
        {
            $_POST = array();
            $arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
        }
        else
        {
            $_SESSION[$_POST["key_session"]] = $_POST["rand"];
            if (!strlen($_POST['NAME_SENDER']))
            {
                $arResult["ERR_FIELDS"]["NAME_SENDER"] = 'has-error';
            }
            if (!strlen($_POST['PHONE_SENDER']))
            {
                $arResult["ERR_FIELDS"]["PHONE_SENDER"] = 'has-error';
            }
            if (!strlen($_POST['COMPANY_SENDER']))
            {
                $arResult["ERR_FIELDS"]["COMPANY_SENDER"] = 'has-error';
            }
            if (!strlen($_POST['CITY_SENDER']))
            {
                $arResult["ERR_FIELDS"]["CITY_SENDER"] = 'has-error';
            }
            else
            {
                $city_sender = GetCityId(trim($_POST['CITY_SENDER']));
                if ($city_sender == 0)
                {
                    $arResult["ERR_FIELDS"]["CITY_SENDER"] = 'has-error';
                }
            }
            if (!strlen($_POST['ADRESS_SENDER']))
            {
                $arResult["ERR_FIELDS"]["ADRESS_SENDER"] = 'has-error';
            }

            if (!strlen($_POST['NAME_RECIPIENT']))
            {
                $arResult["ERR_FIELDS"]["NAME_RECIPIENT"] = 'has-error';
            }
            if (!strlen($_POST['PHONE_RECIPIENT']))
            {
                $arResult["ERR_FIELDS"]["PHONE_RECIPIENT"] = 'has-error';
            }
            if (!strlen($_POST['COMPANY_RECIPIENT']))
            {
                $arResult["ERR_FIELDS"]["COMPANY_RECIPIENT"] = 'has-error';
            }
            if (!strlen($_POST['CITY_RECIPIENT']))
            {
                $arResult["ERR_FIELDS"]["CITY_RECIPIENT"] = 'has-error';
            }
            else
            {
                $city_recipient = GetCityId(trim($_POST['CITY_RECIPIENT']));
                if ($city_recipient == 0)
                {
                    $arResult["ERR_FIELDS"]["CITY_RECIPIENT"] = 'has-error';
                }
            }
            if (!strlen($_POST['ADRESS_RECIPIENT']))
            {
                $arResult["ERR_FIELDS"]["ADRESS_RECIPIENT"] = 'has-error';
            }

            if (!$_POST['TYPE_DELIVERY'])
            {
                $arResult["ERR_FIELDS"]["TYPE_DELIVERY"] = 'has-error';
            }
            if (!$_POST['TYPE_PACK'])
            {
                $arResult["ERR_FIELDS"]["TYPE_PACK"] = 'has-error';
            }
            if (!$_POST['WHO_DELIVERY'])
            {
                $arResult["ERR_FIELDS"]["WHO_DELIVERY"] = 'has-error';
            }
            if (!$_POST['TYPE_PAYS'])
            {
                $arResult["ERR_FIELDS"]["TYPE_PAYS"] = 'has-error';
            }
            else
            {
                if (($_POST['TYPE_PAYS'] == 253) && (!strlen($_POST['PAYS'])))
                {
                    $arResult["ERR_FIELDS"]["TYPE_PAYS"] = 'has-error';
                }
            }
            if (!$_POST['PAYMENT'])
            {
                $arResult["ERR_FIELDS"]["PAYMENT"] = 'has-error';
            }




            $arJsonDescr = array();
            $total_place = 0;
            $total_weight = 0;
            $total_gabweight = 0;
            foreach ($_POST['pack_description'] as $description_str)
            {
                $sizes = array();
                foreach ($description_str['size'] as $sz)
                {
                    $sizes[] = floatval(str_replace(',','.',$sz));
                }
                $arCurStr = array(
                    'name' => iconv('windows-1251','utf-8',$description_str['name']),
                    'place' => intval($description_str['place']),
                    'weight' => floatval(str_replace(',','.',$description_str['weight'])),
                    'size' => $sizes,
                    'gabweight' => (($sizes[0]*$sizes[1]*$sizes[2])/$arResult['CURRENT_CLIENT_COEFFICIENT_VW'])
                );
                $total_place = $total_place + $arCurStr['place'];
                $total_weight = $total_weight + $arCurStr['weight'];
                $total_gabweight = $total_gabweight + $arCurStr['gabweight'];
                $arJsonDescr[] = $arCurStr;
            }
            if ($total_place <= 0)
            {
                $arResult["ERR_FIELDS"]["PLACES"] = 'has-error';
            }
            if ($total_weight <= 0)
            {
                $arResult["ERR_FIELDS"]["WEIGHT"] = 'has-error';
            }



            if (count($arResult["ERR_FIELDS"]) == 0)
            {
                $id_in = MakeInvoiceNumber(83, 7, '90-');
                $number_nakl = strlen(NewQuotes($_POST['NUMBER'])) ? NewQuotes($_POST['NUMBER']) : $id_in['number'];
                $el = new CIBlockElement;
                $arLoadProductArray = Array(
                    "MODIFIED_BY" => $USER->GetID(), 
                    "IBLOCK_SECTION_ID" => false,
                    "IBLOCK_ID" => 83,
                    "PROPERTY_VALUES" => array(
                        544 => $id_in['max_id'],
                        545 => $arResult['CURRENT_CLIENT'],
                        546 => NewQuotes($_POST['NAME_SENDER']),
                        547 => NewQuotes($_POST['PHONE_SENDER']),
                        548 => NewQuotes($_POST['COMPANY_SENDER']),
                        549 => $city_sender,
                        550 => deleteTabs($_POST['INDEX_SENDER']),
                        551 => array('VALUE' => array('TYPE' => 'text', 'TEXT' => NewQuotes($_POST['ADRESS_SENDER']))),
                        552 => NewQuotes($_POST['NAME_RECIPIENT']),
                        553 => NewQuotes($_POST['PHONE_RECIPIENT']),
                        554 => NewQuotes($_POST['COMPANY_RECIPIENT']),
                        555 => $city_recipient,
                        556 => deleteTabs($_POST['INDEX_RECIPIENT']),
                        557 => $_POST['TYPE_DELIVERY'],
                        558 => $_POST['TYPE_PACK'],
                        559 => $_POST['WHO_DELIVERY'],
                        560 => deleteTabs($_POST['IN_DATE_DELIVERY']),
                        561 => deleteTabs($_POST['IN_TIME_DELIVERY']),
                        562 => $_POST['TYPE_PAYS'],
                        563 => deleteTabs($_POST['PAYS']),
                        564 => $_POST['PAYMENT'],
                        565 => floatval(str_replace(',','.',$_POST['FOR_PAYMENT'])),
                        566 => floatval(str_replace(',','.',$_POST['COST'])),
                        567 => $total_place,
                        568 => $total_weight,
                        569 => $_POST['DIMENSIONS'],
                        570 => array('VALUE' => array('TYPE' => 'text', 'TEXT' => NewQuotes($_POST['INSTRUCTIONS']))),
                        571 => array('VALUE' => array('TYPE' => 'text', 'TEXT' => NewQuotes($_POST['ADRESS_RECIPIENT']))),
                        572 => 257,
                        639 => $arResult['BRANCH_AGENT_BY'],
                        640 => $arResult['CLIENT_CONTRACT'],
                        641 => $arResult['CURRENT_BRANCH'],
                        679	=> 1,
                        682 => json_encode($arJsonDescr), 
                    ),
                    "NAME" => $number_nakl,
                    "ACTIVE" => "Y"
                );
                if ($z_id = $el->Add($arLoadProductArray))
                {
                    $arLog = array(
                        'Type' => 'Новая накладная',
                        'OwnNumber' => strlen(NewQuotes($_POST['NUMBER'])) ? 'Y' : 'N',
                        'Number' => $number_nakl,
                        'ID_IN' => $id_in['max_id'],
                        'CREATOR' => $arResult['CURRENT_CLIENT'],
                        'NAME_SENDER' => NewQuotes($_POST['NAME_SENDER']),
                        'PHONE_SENDER' => NewQuotes($_POST['PHONE_SENDER']),
                        'COMPANY_SENDER' => NewQuotes($_POST['COMPANY_SENDER']),
                        'CITY_SENDER' => $city_sender,
                        'INDEX_SENDER' => deleteTabs($_POST['INDEX_SENDER']),
                        'ADRESS_SENDER' => NewQuotes($_POST['ADRESS_SENDER']),
                        'NAME_RECIPIENT' => NewQuotes($_POST['NAME_RECIPIENT']),
                        'PHONE_RECIPIENT' => NewQuotes($_POST['PHONE_RECIPIENT']),
                        'COMPANY_RECIPIENT' => NewQuotes($_POST['COMPANY_RECIPIENT']),
                        'CITY_RECIPIENT' => $city_recipient,
                        'INDEX_RECIPIENT' => deleteTabs($_POST['INDEX_RECIPIENT']),
                        'TYPE_DELIVERY' => $_POST['TYPE_DELIVERY'],
                        'TYPE_PACK' => $_POST['TYPE_PACK'],
                        'WHO_DELIVERY' => $_POST['WHO_DELIVERY'],
                        'IN_DATE_DELIVERY' => deleteTabs($_POST['IN_DATE_DELIVERY']),
                        'IN_TIME_DELIVERY' => deleteTabs($_POST['IN_TIME_DELIVERY']),
                        'TYPE_PAYS' => $_POST['TYPE_PAYS'],
                        'PAYS' => deleteTabs($_POST['PAYS']),
                        'PAYMENT' => $_POST['PAYMENT'],
                        'FOR_PAYMENT' => floatval(str_replace(',','.',$_POST['FOR_PAYMENT'])),
                        'COST' => floatval(str_replace(',','.',$_POST['COST'])),
                        'PLACES' => $total_place,
                        'WEIGHT' => $total_weight,
                        'DIMENSIONS' => $_POST['DIMENSIONS'],
                        'INSTRUCTIONS' => NewQuotes($_POST['INSTRUCTIONS']),
                        'ADRESS_RECIPIENT' => NewQuotes($_POST['ADRESS_RECIPIENT']),
                        'STATE' => 257,
                        'BRANCH_AGENT_BY' => $arResult['BRANCH_AGENT_BY'],
                        'CLIENT_CONTRACT' => $arResult['CLIENT_CONTRACT'],
                        'CURRENT_BRANCH' => $arResult['CURRENT_BRANCH'],
                        'INFORMATION_ON_CREATE'	=> 1,
                        'PACK_DESCRIPTION' => json_encode($arJsonDescr)
                    );
                    AddToLogs('invoices',$arLog);


                    $_SESSION['MESSAGE'][] = "Накладная №".$number_nakl." успешно создана";





                    // LocalRedirect("/index.php?openprint=Y&id=".$z_id);

                }
                else
                {
                    $arResult['ERRORS'][] = $el->LAST_ERROR;
                }
            }
        }
    }






    $APPLICATION->SetTitle(GetMessage("TITLE_MODE_ADD"));
}

$this->IncludeComponentTemplate($mode);