<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

CModule::IncludeModule('iblock');
CModule::IncludeModule("currency");
$u_id = $USER->GetID();
$arResult["CURRENT_USER"] = $u_id;

$agent_array = GetCurrentAgent($u_id);
$agent_id = $agent_array['id'];
$arResult["CURRENT_COMPANY"] = $agent_id;
$componentPage = "blank";

if (isset($arParams['PERM'][$agent_array['type']]['ALL']))
{
    $arResult["PERM"] = $arParams['PERM'][$agent_array['type']]['ALL'];
}

if ($arResult["PERM"] == "C")
{
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$arResult["ERRORS"] = array();

if (is_array($_SESSION['MESSAGE']))
{
    $arResult["MESSAGE"] = $_SESSION['MESSAGE'];
    $_SESSION['MESSAGE'] = false;
}
if (is_array($_SESSION['ERRORS']))
{
    $arResult["ERRORS"] = $_SESSION['ERRORS'];
    $_SESSION['ERRORS'] = false;
}
if (is_array($_SESSION['WARNINGS']))
{
    $arResult["WARNINGS"] = $_SESSION['WARNINGS'];
    $_SESSION['WARNINGS'] = false;
}

$arResult['ROLE_USER'] = GetRoleOfUser($u_id);

/**************************************************/
/************************УК************************/
/**************************************************/

if ($agent_array['type'] == 51)
{
    $modes = array(
        'requests',
        'request',
        'request_xls',
        'request_pdf',
        'supplier',
        'call_courier'
    );
    $arResult['MENU'] = array(
        'requests' => GetMessage("TTL_3")
    );
    if (in_array($_GET['mode'],$modes))
    {
        $mode = $_GET['mode'];
    }
    else
    {
        $mode = 'requests';
    }
    unset($arResult["MENU"][$mode]);
    $componentPage = "upr_".$mode;

    /*****************Заявки на забор******************/
    if ($mode == 'requests')
    {
        /*****************принятие заявок******************/
        if (isset($_POST['accept']))
        {


            if($USER->isAdmin()){
                // dump($_POST);
                // exit;
            }

            foreach ($_POST['id'] as $order) /* если отмечены чекбоксы и нажата кнопка Принять */
            {
                if ($_POST['state'][$order] != 183)  /* если статус не Отправлена */
                {
                    $arResult["ERRORS"][] = GetMessage("ERR_STATE", array("#ORDER#" => $order, "#NUMBER#" => $_POST['names'][$order]));
                }
                else
                {
                    CIBlockElement::SetPropertyValuesEx($order, false, array(433 => 185)); /* 433 - статус изменить на Принято */
                    $arResult["MESSAGE"][] = GetMessage("MESS_ACC", array("#ORDER#" => $order, "#NUMBER#" => $_POST['names'][$order]));
                }
            }
        }
        /****************объединить заявки*****************/
        /*if (isset($_POST['spojit']))
        {
            if (count($_POST['id']) >= 2)
            {
                $arSaps = array();
                $arShops = array();
                foreach ($_POST['id'] as $z)
                {
                    $arSaps[] = $_POST['supplier'][$z];
                    $arShops[] = $_POST["creator"][$z];
                }
                $arSaps = array_unique($arSaps);
                $arShops = array_unique($arShops);
                if (count($arSaps) > 1)
                    $arResult["ERRORS"][] = GetMessage("ERR_MERGER_1");
                if (count($arShops) > 1)
                    $arResult["ERRORS"][] = GetMessage("ERR_MERGER_2");
                if (count($arResult["ERRORS"]) == 0)
                {
                    $all_weight = 0;
                    $arChange = array();
                    $arNotActive = array();
                    $max_z = max($_POST['id']);
                    foreach ($_POST['id'] as $z)
                    {
                        $r_info = GetOneRequest($z);
                        if ($z != $max_z)
                        {
                            $arNotActive[] = $z;
                        }
                        foreach ($r_info['orders'] as $r_zapis)
                        {
                            if ($z != $max_z)
                            {
                                $arChange[] = $r_zapis["ID"];
                            }
                            $all_weight = $all_weight + $r_zapis["PROPERTY_430_VALUE"]*$r_zapis["PROPERTY_432_VALUE"];
                        }
                    }
                    foreach ($arChange as $change_el)
                    {
                        CIBlockElement::SetPropertyValuesEx($change_el, 77, array(428 => $max_z));
                    }
                    CIBlockElement::SetPropertyValuesEx($max_z, 76, array(422 => $all_weight));
                    foreach ($arNotActive as $change_el)
                    {
                        $el = new CIBlockElement;
                        $res = $el->Update($change_el, array("ACTIVE" => "N"));
                    }
                    $arResult["MESSAGE"][] = GetMessage("MESS_MERGER");
                }
            }
            else
            {
                $arResult["ERRORS"][] = GetMessage("ERR_MERGER_3");
            }
        }*/
        $arResult["FILTER"]["SHOPS"] = array(0 => GetMessage("ALL"));
        $list_shops = array();
        $arShops = TheListOfShops(0, false, true, false, '', $agent_array['id']);
        foreach ($arShops as $s)
        {
            $list_shops[] = $s["ID"];
            $arResult["FILTER"]["SHOPS"][$s["ID"]] = $s["NAME"];
        }
        $arResult["FILTER"]["TYPES"] = array(0 => "Все");
        $db_enum_list = CIBlockProperty::GetPropertyEnum(420, array("value"=>"asc"));
        while($ar_enum_list = $db_enum_list->GetNext())
        {
            $arResult["FILTER"]["TYPES"][$ar_enum_list["ID"]] = $ar_enum_list["VALUE"];
        }
        $arResult["FILTER"]["SUPPLIERS"] = array(0 => "Все");

        $sups = GetListSuppliers($list_shops, false, true, array("NAME"=>"ASC"));
        foreach ($sups as $s)
        {
            $arResult["FILTER"]["SUPPLIERS"][$s["ID"]] = $s["NAME"];
        }
        $arResult["FILTER"]["STATES"] = array(0 => "Все");
        $db_enum_list = CIBlockProperty::GetPropertyEnum(433, array("value"=>"asc"));
        while($ar_enum_list = $db_enum_list->GetNext())
        {
            $arResult["FILTER"]["STATES"][$ar_enum_list["ID"]] = $ar_enum_list["VALUE"];
        }
        $arResult["TITLE"] = GetMessage("TTL_3");
        $state_values = (intval($_GET["state"]) > 0) ? intval($_GET["state"]) : array(183,185,186,187);
        $shop = (intval($_GET["shop"]) > 0) ? intval($_GET["shop"]) : $list_shops;
        $arResult["LIST"] = GetListRequests($shop, true, false, array("created"=>"DESC"), intval($_GET["type"]), $state_values, '', intval($_GET['supplier']), $_GET['pick_up']);
        $arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
        unset($arResult["LIST"]["NAV_STRING"]);
    }

    /*****************заявка на забор******************/
    if ($mode == 'request')
    {
        if (isset($_POST['save']))
        {
            $all = true;
            $cost = str_replace(',','.',$_POST['cost']);
            $weight = str_replace(',','.',$_POST['weight']);
            $size_1 = str_replace(',','.',$_POST['size_1']);
            $size_2 = str_replace(',','.',$_POST['size_2']);
            $size_3 = str_replace(',','.',$_POST['size_3']);
            if ($weight <= 0)
                $all = false;
            foreach ($_POST['weights'] as $w)
            {
                $w_w = str_replace(',','.',$w);
                if ($w_w <= 0)
                    $all = false;
            }
            if (!$all)
            {
                $arResult["ERRORS"][] = GetMessage("ERR_WEIGHT");
            }
            if ($cost < 0)
            {
                $arResult["ERRORS"][] = GetMessage("ERR_COST");
            }
            if (count($arResult["ERRORS"]) == 0)
            {
                CIBlockElement::SetPropertyValuesEx($_POST['reqv_id'], 76, array(
                    422 => $weight,
                    423 => $size_1,
                    424 => $size_2,
                    425 => $size_3,
                    433 => 186,
                    435 => $cost
                ));
                foreach ($_POST['weights'] as $k => $w)
                {
                    $w_w = str_replace(',','.',$w);
                    CIBlockElement::SetPropertyValuesEx($k, 77, array(430 => $w_w));
                }
                $arResult["MESSAGE"][] = GetMessage("MESS_DONE");
            }
        }
        $arResult["REQUEST"] = GetOneRequest(intval($_GET['id']), 0, 181);
        if ($arResult["REQUEST"])
        {
            $arResult["TITLE"] = GetMessage("TTL_6", array("#NUMBER#" => $arResult["REQUEST"]["PROPERTY_NUMBER_VALUE"]));
        }
        else
        {
            $arResult["TITLE"] = GetMessage("ERR_NOT");
        }
    }

    /*************заявка на вызов курьера**************/
    if ($mode == 'call_courier')
    {
        if (isset($_POST['save']))
        {
            $all = true;
            $cost = str_replace(',','.',$_POST['cost']);
            $weight = str_replace(',','.',$_POST['weight']);
            $size_1 = str_replace(',','.',$_POST['size_1']);
            $size_2 = str_replace(',','.',$_POST['size_2']);
            $size_3 = str_replace(',','.',$_POST['size_3']);
            if ($weight <= 0)
                $all = false;
            if (!$all)
            {
                $arResult["ERRORS"][] = GetMessage("ERR_WEIGHT");
            }
            if ($cost < 0)
            {
                $arResult["ERRORS"][] = GetMessage("ERR_COST");
            }
            if (count($arResult["ERRORS"]) == 0)
            {
                CIBlockElement::SetPropertyValuesEx($_POST['reqv_id'], 76, array(
                    422 => $weight,
                    423 => $size_1,
                    424 => $size_2,
                    425 => $size_3,
                    433 => 186,
                    435 => $cost
                ));
                $arResult["MESSAGE"][] = GetMessage("MESS_DONE");
            }
        }
        $arResult["REQUEST"] = GetOneRequest(intval($_GET['id']), 0, 182);
        if ($arResult["REQUEST"])
        {
            $arResult["TITLE"] = GetMessage("TTL_7", array("#NUMBER#" => $arResult["REQUEST"]["PROPERTY_NUMBER_VALUE"]));
        }
        else
        {
            $arResult["TITLE"] = GetMessage("ERR_NOT");
        }
    }

    /***********манифест на заявку на забор************/
    if ($mode == 'request_xls')
    {
        $arResult["REQUEST"] = GetOneRequest(intval($_GET['id']), 0);
        if ($arResult["REQUEST"])
        {
            $arResult["REQUEST"] = GetRequestsXLS(0,array(intval($_GET['id'])));
        }
        else
        {
            LocalRedirect("/suppliers/index.php?mode=request&id=".intval($_GET['id']));
        }
    }

    if ($mode == 'request_pdf')
    {
        $arResult["REQUEST"] = GetOneRequest(intval($_GET['id']), 0);
        if ($arResult["REQUEST"])
        {
            $arResult["REQUEST"] = GetRequestsPDF(0, intval($_GET['id']));
        }
        else
        {
            LocalRedirect("/suppliers/index.php?mode=request&id=".intval($_GET['id']));
        }
    }

    /********************поставщик*********************/
    if ($mode == 'supplier')
    {
        $arResult["INFO"] = GetInfoOfSupplier($_GET['id']);
        if ($arResult["INFO"])
            $arResult["TITLE"] = GetMessage("TTL_8", array("#NUMBER#" => $arResult["INFO"]["PROPERTY_ID_IN_VALUE"]));
        else
            $arResult["TITLE"] = GetMessage("TTL_9");
    }
}


/**************************************************/
/************************ИМ************************/
/**************************************************/

if ($agent_array['type'] == 52)
{

    //dump($agent_array['type']);
    $modes = array(
        'requests',
        'add_request',
        'request',
        'request_edit',
        'list',
        'add',
        'supplier',
        'add_call_courier',
        'call_courier_edit',
        'call_courier',
        'add_request_new'
    );
    $arResult['MENU'] = array(
        'requests' => GetMessage("TTL_3"),
        'list' => GetMessage("TTL_1")
    );

    foreach ($arResult["MENU"] as $m => $name)
    {
        if ($arParams['PERM'][$agent_array['type']][$m][$arResult['ROLE_USER']] == "C")
        {
            unset($arResult["MENU"][$m]);
        }
    }
    if (in_array($_GET['mode'],$modes))
    {
        $mode = $_GET['mode'];
    }
    else
    {
        if ($arParams['MODE'])
        {
            $mode = $arParams['MODE'];
        }
        else
        {
            foreach ($arResult["MENU"] as $k => $name)
            {
                $mode = $k;
                break;
            }
        }
    }
    if (strlen($mode))
    {
        $arResult['MODE'] = $mode;
        $componentPage = "shop_".$mode;
    }
    else
    {
        $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
    }
    if (isset($arParams['PERM'][$agent_array['type']]['ALL']))
    {
        $arResult["PERM"] = $arParams['PERM'][$agent_array['type']]['ALL'];
    }
    else
    {
        $arResult["PERM"] = $arParams['PERM'][$agent_array['type']][$mode][$arResult['ROLE_USER']];
    }
    if ($arResult["PERM"] == "C")
    {
        $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
    }

    /****************список поставщиков****************/
    if ($mode == 'list')
    {
        /* if($_GET['test']='Y'){
             dump($_POST);
         }*/
        if (isset($_POST['applay']) && (count($_POST['id']) > 0))
        {
            foreach ($_POST['id'] as $sup)
            {
                $el = new CIBlockElement;
                $res = $el->Update($sup, array("MODIFIED_BY" => $u_id, "ACTIVE" => $_POST['active']));
            }
            $arResult["MESSAGE"][] = (count($_POST['id']) == 1) ? GetMessage("MESS_ACTIVE_SUP_ONE") : GetMessage("MESS_ACTIVE_SUP_MANY");
        }
        $arResult["TITLE"] = GetMessage("TTL_1");
        $arResult["LIST"] = GetListSuppliers($agent_array['id']);

        $arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
        unset($arResult["LIST"]["NAV_STRING"]);
        /*if($_GET['test']=='Y'){
          dump($arResult["LIST"]);
      }*/
    }

    /********************поставщик*********************/
    if ($mode == 'supplier')
    {
        if (isset($_POST['save']))
        {
            $arChange = array();
            $arChangeGlobal = array(
                'MODIFIED_BY' => $u_id,
                'ACTIVE' => $_POST['active']
            );
            if (!strlen($_POST['name']))
                $arResult["ERRORS"][] = GetMessage("ERR_NAME");
            else
                $arChangeGlobal["NAME"] = $_POST['name'];
            if (!strlen($_POST['city']))
            {
                $arResult["ERRORS"][] = GetMessage("ERR_CITY");
            }
            else
            {
                $city_id = GetCityId($_POST['city']);
                $arChange[413] = $city_id;
            }
            if (!strlen($_POST['adress']))
                $arResult["ERRORS"][] = GetMessage("ERR_ADRESS");
            else
                $arChange[414] = $_POST['adress'];
            if (!strlen($_POST['phone']))
                $arResult["ERRORS"][] = GetMessage("ERR_PHONE");
            else
                $arChange[415] = $_POST['phone'];
            if (!strlen($_POST['manager']))
                $arResult["ERRORS"][] = GetMessage("ERR_MANAGER");
            else
                $arChange[416] = $_POST['manager'];
            if (!strlen($_POST['introduce']))
                $arResult["ERRORS"][] = GetMessage("ERR_INTRODUCE");
            else
                $arChange[417] = $_POST['introduce'];
            if (count($arChange) > 0)
            {
                CIBlockElement::SetPropertyValuesEx($_POST['id_sup'], false, $arChange);
            }
            $el = new CIBlockElement;
            if ($el->Update($_POST['id_sup'], $arChangeGlobal))
            {
                $arResult["MESSAGE"][] = GetMessage("MESS_SUP_CHANGE");
            }
        }
        $arResult["INFO"] = GetInfoOfSupplier($_GET['id'],$agent_array['id']);
        if ($arResult["INFO"])
            $arResult["TITLE"] = GetMessage("TTL_8", array("#NUMBER#" => $arResult["INFO"]["PROPERTY_ID_IN_VALUE"]));
        else
            $arResult["TITLE"] = GetMessage("TTL_9");
    }

    /***********добавление нового поставщика***********/
    if ($mode == 'add')
    {
        if (isset($_POST['add']))
        {
            $arToAdd = array();
            if (!strlen($_POST['name']))
            {
                $arResult["ERRORS"][] = GetMessage("ERR_NAME");
            }
            if (!strlen($_POST['city']))
            {
                $arResult["ERRORS"][] = GetMessage("ERR_CITY");
            }
            else
            {
                $city_id = GetCityId($_POST['city']);

                //$arFilter = array('IBLOCK_ID' => 40, 'ACTIVE' => 'Y', 'PROPERTY_TYPE' => array(51,53), 'PROPERTY_CITY' => $city_id);
                //$res = CIBlockElement::GetList(array(), $arFilter, false, false, array('ID'));
                /*if($ob = $res->GetNextElement())
                {
                    $arFields = $ob->GetFields();
                }
                else
                {
                    $arResult["ERRORS"][] = 'В данном городе отсутствует возможность осуществления забора у поставщика';
                }*/

                $arToAdd[413] = $city_id;
            }
            if (!strlen($_POST['adress']))
            {
                $arResult["ERRORS"][] = GetMessage("ERR_ADRESS");
            }
            else
            {
                $arToAdd[414] = $_POST['adress'];
            }
            if (!strlen($_POST['phone']))
            {
                $arResult["ERRORS"][] = GetMessage("ERR_PHONE");
            }
            else
            {
                $arToAdd[415] = $_POST['phone'];
            }
            if (!strlen($_POST['manager']))
            {
                $arResult["ERRORS"][] = GetMessage("ERR_MANAGER");
            }
            else
            {
                $arToAdd[416] = $_POST['manager'];
            }
            if (!strlen($_POST['introduce']))
            {
                $arResult["ERRORS"][] = GetMessage("ERR_INTRODUCE");
            }
            else
            {
                $arToAdd[417] = $_POST['introduce'];
            }
            if (count($arResult["ERRORS"]) == 0)
            {
                $max_id = GetMaxIDIN(75, 5, true, 412, $agent_array['id']);
                $arToAdd[412] = $agent_array['id'];
                $arToAdd[411] = $max_id;
                $el = new CIBlockElement;
                $arLoadProductArray = array(
                    "MODIFIED_BY" => $u_id,
                    "IBLOCK_SECTION_ID" => false,
                    "IBLOCK_ID" => 75,
                    "PROPERTY_VALUES" => $arToAdd,
                    "NAME" => $_POST['name'],
                    "ACTIVE" => $_POST['active']
                );
                if ($supplier = $el->Add($arLoadProductArray))
                {
                    $arResult["MESSAGE"][] = GetMessage("SUPPLIERS_ADD",array("#ID#" => $supplier, "#NUMBER#" => $max_id));
                }
                else
                {
                    $arResult["ERRORS"][] = $el->LAST_ERROR;
                }
            }
        }
        $arResult["TITLE"] = GetMessage("TTL_2");
    }

    /*****************заявки на забор******************/
    if ($mode == 'requests')
    {
        if (isset($_POST['del']) && (count($_POST['id']) > 0))
        {
            $arDelete = array();
            foreach ($_POST['id'] as $r)
            {
                $p = GetOneRequest($r, $agent_array['id']);
                foreach ($p['orders'] as $p_el)
                {
                    $arDelete[] = $p_el["ID"];
                }
                foreach ($p['packs'] as $p_el)
                {
                    CIBlockElement::SetPropertyValuesEx($p_el["ID"], 42, array(443 => false));
                }
                $arDelete[] = $r;
            }
            foreach ($arDelete as $d)
            {
                CIBlockElement::Delete($d);
            }
            $arResult["MESSAGE"][] = GetMessage("MESS_DEL");
        }
        /*
        if (isset($_POST['send']) && (count($_POST['id']) > 0))
        {
            $links = array();
            $files = array();
            foreach ($_POST['id'] as $r)
            {
                if ($_POST['type'][$r] == 182)
                {
                    $m = 'call_courier';
                }
                else
                {
                    $m = 'request';
                }
                $links[] = '<a href="http://dms.newpartner.ru/suppliers/index.php?mode='.$m.'&id='.$r.'" target="_blank">'.$_POST['names'][$r].'</a>';
                CIBlockElement::SetPropertyValuesEx($r, 76, array(433 => 183));
            }
            $arParamsMsg = array(
                "DATE_SEND" => date('d.m.Y H:i'),
                "SHOP_ID" => $agent_array['id'],
                "SHOP_NAME" => $agent_array['name'],
                "PICKUPS" => implode(', ',$links),
                "LINK_TO_MSG" => ""
            );
            $agent_to = $agent_array["uk"];
            $agent_from = $agent_array['id'];
            $qw = SendMessageInSystem($u_id,$agent_from,$agent_to, 'Новые заявки на забор (вызов курьера)',188, '', '', 177, $arParamsMsg);
            $arParamsMsg["LINK_TO_MSG"] = '<p><a target="_blank" href="http://dms.newpartner.ru/messages/index.php?mode=detail&id='.$qw.'">Ссылка на данное сообщение в DMS "Новый Партнер"</a></p>';
            foreach ($_POST['id'] as $r)
            {
                $files[] = GetRequestsPDF($agent_array['id'], $r, 'F');
                if ($_POST['type'][$r] == 182)
                {
                    $r_info = GetOneRequest($r,$agent_array['id']);
                    foreach ($r_info['packs'] as $ord)
                    {
                        CIBlockElement::SetPropertyValuesEx($ord["ID"], 42, array(203 => 118, 229 => 119));
                        $pack = GetListOfPackeges($agent_array,0, $ord["ID"]);
                        $arResult['PACK'] = $pack[0];
                        $arResult["PACK"]['GOODS'] = array();
                        $arResult["PACK"]["SHOP"] = GetCompany($arResult['PACK']['PROPERTY_CREATOR_VALUE']);
                        $arResult["PACK"]["AGENT_NAME"] = GetMessage("MSD_NAME");
                        $files[] = MakeTicketPDF($arResult["PACK"]);
                    }
                }
            }
            $sends = SendMessageMailNew($agent_to, $agent_from, 189, 177, $arParamsMsg, $files);
            $arResult["MESSAGE"][] = GetMessage("MESS_REQV_SEND");
        }
        */
        $arResult["TITLE"] = GetMessage("TTL_3");
        $arResult["LIST"] = GetListRequests($agent_array['id']);
        $arResult["NAV_STRING"] = $arResult["LIST"]["NAV_STRING"];
        unset($arResult["LIST"]["NAV_STRING"]);
    }

    /**************новая заявка на забор***************/
    if ($mode == 'add_request')
    {
        $arResult['SHOP_ID'] = $agent_array["id"];
        $arResult['PRICE'] = WhatIsPrice($agent_array['id'],3);
        $arResult['TITLE'] = GetMessage("TTL_4");
        $arResult['SUPPLIERS'] = GetListSuppliers($agent_array['id'], false, true, array("ID" => "NAME"));
        $arResult['DISPLAY'] = array(
            'coun_rows' => 0,
            'supplier' => 0,
            'city_id' => 0,
            'weight_all' => 0,
            'size_1' => '',
            'size_2' => '',
            'size_3' => '',
            'date' => '',
            'from' => '',
            'to' => '',
            'comment' => '',
            'el_id' => array( 0 => ''),
            'name' => array(0 => ''),
            'article' => array(0 => ''),
            'weight' => array(0 => 0),
            'count' => array(0 => 1)
        );

        if (isset($_POST['add']))
        {
            $arResult['DISPLAY'] = $_POST;
            unset($arResult['DISPLAY']['add']);

            if ($arResult['DISPLAY']['supplier'] == 0)
            {
                $arResult["ERRORS"][] = GetMessage("ERR_WRONG_SUP");
            }

            if (!strlen($arResult['DISPLAY']['date']))
            {
                $arResult["ERRORS"][] = GetMessage("ERR_WRONG_DATE");
            }

            $when_delivery = '';
            if (strlen($arResult['DISPLAY']['date']))
            {
                $when_delivery = DateFF($arResult['DISPLAY']['date']);
            }
            if (strlen($arResult['DISPLAY']['from']))
            {
                if (strlen($when_delivery))
                {
                    $when_delivery .= ' ';
                }
                $when_delivery .= GetMessage("FROM_TIME", array("#TIME#" => $arResult['DISPLAY']['from']));
            }
            if (strlen($arResult['DISPLAY']['to']))
            {
                if (strlen($when_delivery))
                {
                    $when_delivery .= ' ';
                }
                $when_delivery .= GetMessage("TO_TIME", array("#TIME#" => $arResult['DISPLAY']['to']));
            }

            $all_fields = true;

            foreach ($arResult['DISPLAY']['weight'] as $k => $v)
            {
                if (!strlen($arResult['DISPLAY']['name'][$k]))
                {
                    $all_fields = false;
                }
                if (str_replace(',','.',$arResult['DISPLAY']['weight'][$k]) <= 0)
                {
                    $all_fields = false;
                }
                if (intval($arResult['DISPLAY']['count'][$k]) == 0)
                {
                    $all_fields = false;
                }
                $arResult['DISPLAY']['el_id'][$k] = $k;
            }

            if (!$all_fields)
            {
                $arResult["ERRORS"][] = GetMessage("ERR_TABLE");
            }

            if (count($arResult["ERRORS"]) == 0)
            {
                $max_n = GetMaxIDIN(76, 5, true, 419, $agent_array['id']);
                $p_id = MakePickUpId($agent_array['id'], $max_n);
                $el = new CIBlockElement;
                $arProps = array(
                    418 => $max_n,
                    419 => $agent_array['id'],
                    420 => 181,
                    421 => $arResult['DISPLAY']['supplier'],
                    422 => $arResult['DISPLAY']['weight_all'],
                    423 => strlen($arResult['DISPLAY']['size_1']) ? str_replace(',','.',$arResult['DISPLAY']['size_1']) : 0,
                    424 => strlen($arResult['DISPLAY']['size_2']) ? str_replace(',','.',$arResult['DISPLAY']['size_2']) : 0,
                    425 => strlen($arResult['DISPLAY']['size_3']) ? str_replace(',','.',$arResult['DISPLAY']['size_3']) : 0,
                    426 => $when_delivery,
                    427 => array("VALUE" => array ("TEXT" => $arResult['DISPLAY']['comment'], "TYPE" => "text")),
                    435 => strlen($arResult['DISPLAY']['cost_of']) ? str_replace(',','.',$arResult['DISPLAY']['cost_of']) : 0,
                    436 => $p_id,
                    437 => $arResult['DISPLAY']['date']
                );
                if (intval($arResult['DISPLAY']['send']) == 1)
                {
                    $arProps[433] = 183;
                    $arProps[497] = 1;
                    $send = true;

                }
                else
                {
                    $arProps[433] = false;
                    $arProps[497] = 0;
                    $send = false;
                }
                $arLoadProductArray = array(
                    "MODIFIED_BY" => $u_id,
                    "IBLOCK_SECTION_ID" => false,
                    "IBLOCK_ID" => 76,
                    "PROPERTY_VALUES"=> $arProps,
                    "NAME" => GetMessage("TTL_6", array("#NUMBER#" => $p_id)),
                    "ACTIVE" => "Y"
                );
                if($zayav_id = $el->Add($arLoadProductArray))
                {
                    foreach ($arResult['DISPLAY']['el_id'] as $k => $v)
                    {
                        $order = false;
                        $nn = $arResult['DISPLAY']['name'][$k];
                        $art = $arResult['DISPLAY']['article'][$k];
                        $count = $arResult['DISPLAY']['count'][$k];
                        $t_txt = 'забор у поставщика';
                        $el_2 = new CIBlockElement;
                        $arLoadProductArray_2 = array(
                            "MODIFIED_BY"    => $u_id,
                            "IBLOCK_SECTION_ID" => false,
                            "IBLOCK_ID"      => 77,
                            "PROPERTY_VALUES"=> array(
                                428 => $zayav_id,
                                429 => $order,
                                430 => str_replace(',','.',$arResult['DISPLAY']['weight'][$k]),
                                431 => $art,
                                432 => $count
                            ),
                            "NAME" => $nn,
                            "ACTIVE" => "Y"
                        );
                        $zayav_element = $el_2->Add($arLoadProductArray_2);
                    }
                    if ($send)
                    {
                        $arUksSupls = array();
                        $res = CIBlockElement::GetList(
                            array("sort" => "asc"),
                            array("IBLOCK_ID" => 40, "ACTIVE" => "Y", 'PROPERTY_TYPE' => 51, 'PROPERTY_CITY' => $_POST['city_id']),
                            false,
                            false,
                            array("ID", "NAME", "DATE_ACTIVE_FROM")
                        );
                        while($ob = $res->GetNextElement())
                        {
                            $arFields = $ob->GetFields();
                            $arUksSupls[] = $arFields['ID'];
                        }
                        $agent_to = $agent_array['uk'];
                        $send_to_other = false;
                        if (count($arUksSupls) > 0)
                        {
                            if (!in_array($agent_array['uk'], $arUksSupls))
                            {
                                $agent_to = $arUksSupls[0];
                                $send_to_other = true;
                            }
                        }
                        $agent_from = $agent_array['id'];
                        $ddd = date('d.m.Y H:i');
                        $arParamsMsg = array(
                            "DATE_SEND" => date('d.m.Y H:i'),
                            "SHOP_ID" => $agent_array['id'],
                            "SHOP_NAME" => $agent_array['name'],
                            "ORDER_ID" => $zayav_id,
                            "ORDER_NUMBER" => $p_id,
                            "LINK_TO_MSG" => ""
                        );
                        $qw = SendMessageInSystem($u_id, $agent_from, $agent_to, 'Новая заявка на забор у поставщика', 188, '', '', 178, $arParamsMsg);
                        $arParamsMsg["LINK_TO_MSG"] = '<p><a href="http://dms.newpartner.ru/messages/index.php?mode=detail&id='.$qw.'">Ссылка на данное сообщение в DMS "Новый Партнер"</a></p>';
                        $f = GetRequestsPDF($agent_array['id'], $zayav_id, 'F', $send_to_other);
                        $send = SendMessageMailNew($agent_to, $agent_from, 189, 178, $arParamsMsg, array($f));
                        $_SESSION['MESSAGE'][] = '<a href="/suppliers/index.php?mode=request&id='.$zayav_id.'">Заявка на '.$t_txt.' №'.$p_id.'</a> отправлена';
                    }
                    else
                    {
                        $_SESSION['MESSAGE'][] = '<a href="/suppliers/index.php?mode=request_edit&id='.$zayav_id.'">Заявка на '.$t_txt.' №'.$p_id.'</a> сохранена';
                    }
                    LocalRedirect("/suppliers/index.php?mode=requests");
                }
                else
                {
                    $arResult["ERRORS"][] = $el->LAST_ERROR;
                }
            }
        }
    }

    if ($mode == 'add_request_new')
    {
        $arResult["SHOP"] = GetCompany($agent_array['id']);
        $arResult['PRICE'] = WhatIsPrice($agent_array['id'],3);
        $arResult['TITLE'] = GetMessage("TTL_4");
        $arResult['SUPPLIERS'] = GetListSuppliers($agent_array['id'], false, true, array("ID" => "NAME"));
        $current_folder = intval($arResult["SHOP"]['PROPERTY_FOLDER_VALUE']);
        if (($current_folder > 0) && (count($arResult['SUPPLIERS']) > 0))
        {
            $_SESSION["CurrentStep"] = (isset($_SESSION["CurrentStep"])) ? $_SESSION["CurrentStep"] : 1;
            if ($_SESSION["CurrentStep"] == 1)
            {
                if (isset($_POST["save_1"]))
                {
                    if ($_POST["rand"] == $_SESSION[$_POST["key_session"]])
                    {
                        $_POST = array();
                        $arResult["ERRORS"][] = GetMessage("ERR_REPEATED_FORM");
                    }
                    else
                    {
                        $_SESSION[$_POST["key_session"]] = $_POST["rand"];
                        if ($_POST['supplier'] == 0)
                        {
                            $arResult["ERRORS"][] = GetMessage("ERR_WRONG_SUP");
                        }
                        if (!strlen($_POST['date']))
                        {
                            $arResult["ERRORS"][] = GetMessage("ERR_WRONG_DATE");
                        }
                        if (count($arResult["ERRORS"]) == 0)
                        {
                            $when_delivery = DateFF($_POST['date']);
                            if (strlen($_POST['from']))
                            {
                                if (strlen($when_delivery))
                                {
                                    $when_delivery .= ' ';
                                }
                                $when_delivery .= GetMessage("FROM_TIME", array("#TIME#" => $_POST['from']));
                            }
                            if (strlen($_POST['to']))
                            {
                                if (strlen($when_delivery))
                                {
                                    $when_delivery .= ' ';
                                }
                                $when_delivery .= GetMessage("TO_TIME", array("#TIME#" => $_POST['to']));
                            }
                            $max_n = GetMaxIDIN(76, 5, true, 419, $agent_array['id']);
                            $p_id = MakePickUpId($agent_array['id'], $max_n);
                            $el = new CIBlockElement;
                            $arProps = array(
                                418 => $max_n,
                                419 => $agent_array['id'],
                                420 => 181,
                                421 => $_POST['supplier'],
                                422 => 0,
                                423 => 0,
                                424 => 0,
                                425 => 0,
                                426 => $when_delivery,
                                427 => array("VALUE" => array ("TEXT" => $_POST['comment'], "TYPE" => "text")),
                                435 => 0,
                                436 => $p_id,
                                437 => $_POST['date'],
                                433 => false,
                                497 => 0
                            );
                            $arLoadProductArray = array(
                                "MODIFIED_BY" => $u_id,
                                "IBLOCK_SECTION_ID" => false,
                                "IBLOCK_ID" => 76,
                                "PROPERTY_VALUES"=> $arProps,
                                "NAME" => GetMessage("TTL_6", array("#NUMBER#" => $p_id)),
                                "ACTIVE" => "Y"
                            );
                            $_SESSION["zayav_ID"] = $el->Add($arLoadProductArray);
                            $_SESSION["CurrentStep"] = 2;
                            LocalRedirect("/suppliers/index.php?mode=add_request_new");
                        }
                    }
                }
            }

            if ($_SESSION["CurrentStep"] == 2)
            {
                $arResult["REQUEST"] = GetOneRequest($_SESSION["zayav_ID"], $agent_array['id']);
            }
        }
        else
        {
            if ($current_folder == 0)
            {
                $arResult["ERRORS"][] = "";
            }
            if (count($arResult['SUPPLIERS']) > 0)
            {
                $arResult["ERRORS"][] = "";
            }
        }
    }

    /***************новый вызов курьера****************/
    if ($mode == "add_call_courier")
    {
        $arResult["TITLE"] = GetMessage("TTL_5");
        $arResult["SHOP_ID"] = $agent_array["id"];
        $arResult["PRICE"] = WhatIsPrice($agent_array['id'],3);
        $arResult['DISPLAY'] = array(
            'coun_rows' => 0,
            'weight_all' => 0,
            'size_1' => '',
            'size_2' => '',
            'size_3' => '',
            'date' => '',
            'from' => '',
            'to' => '',
            'comment' => '',
            'cost_of' => 0,
            'el_id' => array( 0 => ''),
            'order_id' => array( 0 => ''),
            'number' => array(0 => ''),
            'weight' => array(0 => 0),
        );
        if (isset($_POST['add']))
        {
            $arResult['DISPLAY'] = $_POST;
            unset($arResult['DISPLAY']['add']);
            if (!strlen($arResult['DISPLAY']['date']))
            {
                $arResult["ERRORS"][] = 'Не указана дата забора';
            }
            $when_delivery = '';
            if (strlen($arResult['DISPLAY']['date']))
            {
                $when_delivery = DateFF($arResult['DISPLAY']['date']);
            }
            if (strlen($arResult['DISPLAY']['from']))
            {
                if (strlen($when_delivery))
                {
                    $when_delivery .= ' ';
                }
                $when_delivery .= GetMessage("FROM_TIME", array("#TIME#" => $arResult['DISPLAY']['from']));
            }
            if (strlen($arResult['DISPLAY']['to']))
            {
                if (strlen($when_delivery))
                {
                    $when_delivery .= ' ';
                }
                $when_delivery .= GetMessage("TO_TIME", array("#TIME#" => $arResult['DISPLAY']['to']));
            }

            $all_fields = true;

            foreach ($arResult['DISPLAY']['weight'] as $k => $v)
            {
                if (str_replace(',','.',$arResult['DISPLAY']['weight'][$k]) <= 0)
                {
                    $all_fields = false;
                }
                if (intval($arResult['DISPLAY']['order_id'][$k]) == 0)
                {
                    $all_fields = false;
                }
                $arResult['DISPLAY']['el_id'][$k] = $k;
            }

            if (!$all_fields)
            {
                $arResult["ERRORS"][] = GetMessage("ERR_TABLE");
            }

            if (count($arResult["ERRORS"]) == 0)
            {
                $max_n = GetMaxIDIN(76, 5, true, 419, $agent_array['id']);
                $p_id = MakePickUpId($agent_array['id'], $max_n);
                $el = new CIBlockElement;
                $arProps = array(
                    418 => $max_n,
                    419 => $agent_array['id'],
                    420 => 182,
                    421 => false,
                    422 => $arResult['DISPLAY']['weight_all'],
                    423 => strlen($arResult['DISPLAY']['size_1']) ? str_replace(',','.',$arResult['DISPLAY']['size_1']) : 0,
                    424 => strlen($arResult['DISPLAY']['size_2']) ? str_replace(',','.',$arResult['DISPLAY']['size_2']) : 0,
                    425 => strlen($arResult['DISPLAY']['size_3']) ? str_replace(',','.',$arResult['DISPLAY']['size_3']) : 0,
                    426 => $when_delivery,
                    427 => array("VALUE" => array ("TEXT" => $arResult['DISPLAY']['comment'], "TYPE" => "text")),
                    435 => strlen($arResult['DISPLAY']['cost_of']) ? str_replace(',','.',$arResult['DISPLAY']['cost_of']) : 0,
                    436 => $p_id,
                    437 => $arResult['DISPLAY']['date'],
                    438 => false
                );
                if (intval($arResult['DISPLAY']['send']) == 1)
                {
                    $arProps[433] = 183;
                    $send = true;
                }
                else
                {
                    $arProps[433] = false;
                    $send = false;
                }
                $arLoadProductArray = array(
                    "MODIFIED_BY" => $u_id,
                    "IBLOCK_SECTION_ID" => false,
                    "IBLOCK_ID" => 76,
                    "PROPERTY_VALUES" => $arProps,
                    "NAME" => GetMessage("TTL_7", array("#NUMBER#" => $p_id)),
                    "ACTIVE" => "Y"
                );
                if($zayav_id = $el->Add($arLoadProductArray))
                {
                    foreach ($arResult['DISPLAY']['order_id'] as $ord)
                    {
                        CIBlockElement::SetPropertyValuesEx($ord, 42, array(443 => $zayav_id));
                    }
                    if ($send)
                    {
                        //настройки для передачи данных в 1с
                        $send_to_1c = false;
                        $currentip = GetSettingValue(683, false, $agent_array["uk"]);
                        $currentlink = GetSettingValue(704, false, $agent_array["uk"]);
                        $login1c = GetSettingValue(705, false, $agent_array["uk"]);
                        $pass1c = GetSettingValue(706, false, $agent_array["uk"]);
                        if ((strlen(trim($currentip))) && (strlen(trim($currentlink))) && (strlen(trim($login1c))) && (strlen(trim($pass1c))))
                        {
                            $url = "http://".$currentip.$currentlink;
                            $curl = curl_init();
                            curl_setopt_array($curl, array(
                                CURLOPT_URL => $url,
                                CURLOPT_HEADER => true,
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_NOBODY => true,
                                CURLOPT_TIMEOUT => 5
                            ));
                            $header = explode("\n", curl_exec($curl));
                            curl_close($curl);
                            if (strlen(trim($header[0])))
                            {
                                $send_to_1c = true;
                                $client = new SoapClient(
                                    $url,
                                    array("login" => $login1c, "password" => $pass1c, "exceptions" => false)
                                );
                                $arManifestTo1c = array(
                                    "TransportationDocument" => "",
                                    "Carrier" => "",
                                    "TransportationCost" => 0,
                                    "Partner" => $agent_array["inn"],
                                    "DepartureDate" => date('d.m.Y'),
                                    "Places" => 0,
                                    "Weight" => 0,
                                    "VolumeWeight" => 0,
                                    "Comment" => "",
                                    "City" => $agent_array['city_name'],
                                    "TransportationMethod" => "",
                                    "Delivery" => array()
                                );
                            }
                        }

                        $pdfs = array();
                        $orders = array();
                        foreach ($arResult['DISPLAY']['order_id'] as $ord)
                        {
                            CIBlockElement::SetPropertyValuesEx($ord, 42, array(203 => 118, 229 => 119));
                            $history_id = AddToHistory($ord,$agent_array['id'],$u_id, 118, '');
                            $short_history_id = AddToShortHistory($ord,$u_id, 119);
                            $pack = GetListOfPackeges($agent_array,0,$ord);
                            $arResult['PACK'] = $pack[0];
                            $arResult["PACK"]['GOODS'] = array();
                            $arResult["PACK"]["SHOP"] = GetCompany($arResult['PACK']['PROPERTY_CREATOR_VALUE']);
                            $arResult["PACK"]["AGENT_NAME"] = GetMessage("MSD_NAME");
                            $pdfs[] = MakeTicketPDF($arResult["PACK"]);
                            $orders[] = '<a href="http://dms.newpartner.ru/warehouse/index.php?mode=package&id='.$arResult['PACK']["ID"].'" target="_blank">'.
                                $arResult['PACK']["PROPERTY_N_ZAKAZ_IN_VALUE"].'</a>';
                            $orders_names[] = $arResult['PACK']["PROPERTY_N_ZAKAZ_IN_VALUE"];
                            if ($send_to_1c)
                            {
                                $orderTo1c = makeManifestOrderfromDMSOrder($arResult['PACK']);
                                if ($orderTo1c['result'])
                                {
                                    $arManifestTo1c['Delivery'][] = $orderTo1c['result'];
                                    $arManifestTo1c["Places"] = $arManifestTo1c["Places"] + $arResult['PACK']['PROPERTY_PLACES_VALUE'];
                                    $arManifestTo1c["Weight"] = $arManifestTo1c["Weight"] + $arResult['PACK']['PROPERTY_WEIGHT_VALUE'];
                                }
                            }
                        }
                        $pdfs[] = GetRequestsPDF($agent_array['id'], $zayav_id, 'F');
                        $agent_to = $agent_array['uk'];
                        $agent_from = $agent_array['id'];
                        $ddd = date('d.m.Y H:i');
                        $arParamsMsg = array(
                            "DATE_SEND" => $ddd,
                            "SHOP_ID" => $agent_array['id'],
                            "SHOP_NAME" => $agent_array['name'],
                            "ORDER_ID" => $zayav_id,
                            "ORDER_NUMBER" => $p_id,
                            "ORDERS" => implode(', ',$orders),
                            "LINK_TO_MSG" => ''
                        );
                        $qw = SendMessageInSystem($u_id, $agent_from, $agent_to, 'Новая заявка на вызов курьера', 188, '', '', 176, $arParamsMsg);
                        $arParamsMsg["LINK_TO_MSG"] = '<p><a href="http://dms.newpartner.ru/messages/index.php?mode=detail&id='.$qw.'">Ссылка на данное сообщение в системе DMS</a></p>';
                        $m = SendMessageMailNew($agent_to, $agent_from, 189, 176, $arParamsMsg, $pdfs);
                        //Передача накладных и заявки в 1с
                        if ($send_to_1c)
                        {
                            $arManifestTo1cUTF = convArrayToUTF($arManifestTo1c);
                            $arLogs = $arManifestTo1c;
                            foreach ($arLogs['Delivery'] as $key => $inv)
                            {
                                foreach ($inv as $inv_key => $data)
                                {
                                    $arLogs['Delivery '.$key.' '.$inv_key] = $data;
                                }
                            }
                            unset($arLogs['Delivery']);
                            AddToLogs('InvoicesSend',$arLogs);
                            $result = $client->SetManifest(array('ListOfDocs' => json_encode($arManifestTo1cUTF)));
                            $mResult = $result->return;
                            AddToLogs('InvoicesSendAnswer', array('Answer' => $mResult));
                            $arJs = array(
                                'IDWEB' => $zayav_id,
                                'INN' => $agent_array['inn'],
                                'DATE' => date('Y-m-d'),
                                'COMPANY_SENDER' => $agent_array['name'],
                                'NAME_SENDER' => $agent_array['user_name'],
                                'PHONE_SENDER' => $agent_array['phones'],
                                'ADRESS_SENDER' => strlen($agent_array['adress_fact']) ? $agent_array['adress_fact'] : $agent_array['adress'],
                                'INDEX_SENDER' => '',
                                'ID_CITY_SENDER' => $agent_array['city'],
                                'DELIVERY_TYPE' => 'Стандарт',
                                'PAYMENT_TYPE' => 'Наличные',
                                'DELIVERY_PAYER' => 'Отправитель',
                                'DELIVERY_CONDITION' => 'ПоАдресу',
                                'DATE_TAKE_FROM' => substr($_POST['date'],6,4).'-'.substr($_POST['date'],3,2).'-'.substr($_POST['date'],0,2).' '.$_POST['from'].':00',
                                'DATE_TAKE_TO' => substr($_POST['date'],6,4).'-'.substr($_POST['date'],3,2).'-'.substr($_POST['date'],0,2).' '.$_POST['to'].':00',
                                'INSTRUCTIONS' => 'Накладные: '.implode(', ',$orders_names).'. '.trim($_POST['comment'])
                            );
                            $arM = convArrayToUTF($arJs);
                            $result = $client->SetCallingTheCourier(array('ListOfDocs' => json_encode($arM)));
                            $mResult = $result->return;
                            $obj = json_decode($mResult, true);
                            $arRes = arFromUtfToWin($obj);
                            $arLogResult = array('Title' => 'Новая заявка на вызов курьера','Response' => $mResult, 'status' => $arRes[0]['status'], 'comment' => $arRes[0]['comment']);
                            $arLog = array_merge($arJs,$arLogResult);
                            AddToLogs('callingCourier',$arLog);
                        }

                        $_SESSION['MESSAGE'][] = '<a href="/suppliers/index.php?mode=call_courier&id='.$zayav_id.'">Заявка на вызов курьера №'.$p_id.'</a> отправлена';
                    }
                    else
                    {
                        $_SESSION['MESSAGE'][] = '<a href="/suppliers/index.php?mode=call_courier_edit&id='.$zayav_id.'">Заявка на вызов курьера №'.$p_id.'</a> сохранена';
                    }
                    LocalRedirect("/suppliers/index.php?mode=requests");
                }
                else
                {
                    $arResult["ERRORS"][] = $el->LAST_ERROR;
                }
            }

        }
    }

    /*************заявка на вызов курьера**************/
    if ($mode == "call_courier")
    {
        $arResult["REQUEST"] = GetOneRequest(intval($_GET['id']), $agent_array['id']);
        if ($arResult["REQUEST"])
        {
            if ($arResult["REQUEST"]["PROPERTY_TYPE_ENUM_ID"] == 181)
                $arResult["TITLE"] = GetMessage("TTL_6", array("#NUMBER#" => $arResult["REQUEST"]["PROPERTY_NUMBER_VALUE"]));
            else
                $arResult["TITLE"] = GetMessage("TTL_7", array("#NUMBER#" => $arResult["REQUEST"]["PROPERTY_NUMBER_VALUE"]));
        }
        else
        {
            $arResult["TITLE"] = GetMessage("ERR_NOT");
        }
    }

    /**********редактирование вызова курьера***********/
    if ($mode ==  "call_courier_edit")
    {
        if (isset($_POST['save']))
        {
            if ($_POST['rand'] == $_SESSION[$_POST['key_session']])
            {
                $_POST = array();
                $arResult['ERRORS'][] = GetMessage("ERR_REPEATED_FORM");
            }
            else
            {
                $_SESSION[$_POST['key_session']] = $_POST['rand'];
                $arResult['DISPLAY'] = $_POST;
                unset($arResult['DISPLAY']['save']);
                if (!strlen($arResult['DISPLAY']['date']))
                    $arResult["ERRORS"][] = GetMessage("ERR_WRONG_DATE");
                $when_delivery = '';
                if (strlen($arResult['DISPLAY']['date']))
                {
                    $when_delivery = DateFF($arResult['DISPLAY']['date']);
                }
                if (strlen($arResult['DISPLAY']['from']))
                {
                    if (strlen($when_delivery))
                        $when_delivery .= ' ';
                    $when_delivery .= GetMessage("FROM_TIME", array("#TIME#" => $arResult['DISPLAY']['from']));
                }
                if (strlen($arResult['DISPLAY']['to']))
                {
                    if (strlen($when_delivery))
                        $when_delivery .= ' ';
                    $when_delivery .= GetMessage("TO_TIME", array("#TIME#" => $arResult['DISPLAY']['to']));
                }
                $all_fields = true;
                foreach ($arResult['DISPLAY']['weight'] as $k => $v)
                {
                    if (str_replace(',','.',$arResult['DISPLAY']['weight'][$k]) <= 0)
                        $all_fields = false;
                    $arResult['DISPLAY']['el_id'][$k] = $k;
                }
                if (!$all_fields)
                    $arResult["ERRORS"][] = GetMessage("ERR_TABLE");
                if (count($arResult["ERRORS"]) == 0)
                {
                    $arChange = array(
                        421 => false,
                        422 => $arResult['DISPLAY']['weight_all'],
                        423 => str_replace(',','.',$arResult['DISPLAY']['size_1']),
                        424 => str_replace(',','.',$arResult['DISPLAY']['size_2']),
                        425 => str_replace(',','.',$arResult['DISPLAY']['size_3']),
                        426 => $when_delivery,
                        427 => array("VALUE" => array ("TEXT" => $arResult['DISPLAY']['comment'], "TYPE" => "text")),
                        435 => strlen($arResult['DISPLAY']['cost_of']) ? str_replace(',','.',$arResult['DISPLAY']['cost_of']) : 0,
                        437 => $arResult['DISPLAY']['date'],
                        438 => false
                    );
                    if (intval($arResult['DISPLAY']['send']) == 1)
                    {
                        $arChange[433] = 183;
                        $send = true;
                    }
                    else
                    {
                        $arChange[433] = false;
                        $send = false;
                    }
                    $zayav_id = $_POST['request_id'];
                    CIBlockElement::SetPropertyValuesEx($zayav_id, 76, $arChange);
                    foreach ($_POST['el_id_del'] as $ord)
                    {
                        CIBlockElement::SetPropertyValuesEx($ord, 42, array(443 => false));
                    }
                    foreach ($arResult['DISPLAY']['order_id'] as $ord)
                    {
                        CIBlockElement::SetPropertyValuesEx($ord, 42, array(443 => $zayav_id));
                    }
                    if ($send)
                    {
                        //настройки для передачи данных в 1с
                        $send_to_1c = false;
                        $currentip = GetSettingValue(683, false, $agent_array["uk"]);
                        $currentlink = GetSettingValue(704, false, $agent_array["uk"]);
                        $login1c = GetSettingValue(705, false, $agent_array["uk"]);
                        $pass1c = GetSettingValue(706, false, $agent_array["uk"]);
                        if ((strlen(trim($currentip))) && (strlen(trim($currentlink))) && (strlen(trim($login1c))) && (strlen(trim($pass1c))))
                        {
                            $url = "http://".$currentip.$currentlink;
                            $curl = curl_init();
                            curl_setopt_array($curl, array(
                                CURLOPT_URL => $url,
                                CURLOPT_HEADER => true,
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_NOBODY => true,
                                CURLOPT_TIMEOUT => 5
                            ));
                            $header = explode("\n", curl_exec($curl));
                            curl_close($curl);
                            if (strlen(trim($header[0])))
                            {
                                $send_to_1c = true;
                                $client = new SoapClient(
                                    $url,
                                    array("login" => $login1c, "password" => $pass1c, "exceptions" => false)
                                );
                                $arManifestTo1c = array(
                                    "TransportationDocument" => "",
                                    "Carrier" => "",
                                    "TransportationCost" => 0,
                                    "Partner" => $agent_array["inn"],
                                    "DepartureDate" => date('d.m.Y'),
                                    "Places" => 0,
                                    "Weight" => 0,
                                    "VolumeWeight" => 0,
                                    "Comment" => "",
                                    "City" => $agent_array['city_name'],
                                    "TransportationMethod" => "",
                                    "Delivery" => array()
                                );
                            }
                        }

                        $pdfs = array();
                        $orders = array();
                        foreach ($arResult['DISPLAY']['order_id'] as $ord)
                        {
                            CIBlockElement::SetPropertyValuesEx($ord, 42, array(203 => 118, 229 => 119));
                            $history_id = AddToHistory($ord,$agent_array['id'],$u_id, 118, 'Заявка на вызов курьера '.$_POST["max_n"]);
                            $short_history_id = AddToShortHistory($ord,$u_id, 119, 'Заявка на вызов курьера '.$_POST["max_n"]);
                            $pack = GetListOfPackeges($agent_array,0,$ord);
                            $arResult['PACK'] = $pack[0];
                            $arResult['PACK']['GOODS'] = array();
                            $arResult['PACK']['SHOP'] = GetCompany($arResult['PACK']['PROPERTY_CREATOR_VALUE']);
                            $arResult['PACK']['AGENT_NAME'] = GetMessage("MSD_NAME");
                            $pdfs[] = MakeTicketPDF($arResult["PACK"]);
                            $orders[] = '<a href="http://dms.newpartner.ru/warehouse/index.php?mode=package&id='.$arResult['PACK']["ID"].'" target="_blank">'.
                                $arResult['PACK']["PROPERTY_N_ZAKAZ_IN_VALUE"].'</a>';
                            $orders_names[] = $arResult['PACK']["PROPERTY_N_ZAKAZ_IN_VALUE"];
                            if ($send_to_1c)
                            {
                                $orderTo1c = makeManifestOrderfromDMSOrder($arResult['PACK']);
                                if ($orderTo1c['result'])
                                {
                                    $arManifestTo1c['Delivery'][] = $orderTo1c['result'];
                                    $arManifestTo1c["Places"] = $arManifestTo1c["Places"] + $arResult['PACK']['PROPERTY_PLACES_VALUE'];
                                    $arManifestTo1c["Weight"] = $arManifestTo1c["Weight"] + $arResult['PACK']['PROPERTY_WEIGHT_VALUE'];
                                }
                            }
                        }
                        $pdfs[] = GetRequestsPDF($agent_array['id'], $zayav_id, 'F');
                        $agent_to = $agent_array["uk"];
                        $agent_from = $agent_array['id'];
                        $ddd = date('d.m.Y H:i');
                        $arParamsMsg = array(
                            "DATE_SEND" => $ddd,
                            "SHOP_ID" => $agent_array['id'],
                            "SHOP_NAME" => $agent_array['name'],
                            "ORDER_ID" => $zayav_id,
                            "ORDER_NUMBER" => $p_id,
                            "ORDERS" => implode(', ',$orders),
                            "LINK_TO_MSG" => ''
                        );
                        $qw = SendMessageInSystem($u_id,$agent_from,$agent_to, 'Новая заявка на вызов курьера', 188, '', '', 176, $arParamsMsg);
                        $arParamsMsg["LINK_TO_MSG"] = '<p><a href="http://dms.newpartner.ru/messages/index.php?mode=detail&id='.$qw.'">Ссылка на данное сообщение в DMS "Новый партнер"</a></p>';
                        $m = SendMessageMailNew($agent_to, $agent_from, 189, 176, $arParamsMsg, $pdfs);
                        //Передача накладных и заявки в 1с
                        if ($send_to_1c)
                        {
                            $arManifestTo1cUTF = convArrayToUTF($arManifestTo1c);
                            $arLogs = $arManifestTo1c;
                            foreach ($arLogs['Delivery'] as $key => $inv)
                            {
                                foreach ($inv as $inv_key => $data)
                                {
                                    $arLogs['Delivery '.$key.' '.$inv_key] = $data;
                                }
                            }
                            unset($arLogs['Delivery']);
                            AddToLogs('InvoicesSend',$arLogs);
                            $result = $client->SetManifest(array('ListOfDocs' => json_encode($arManifestTo1cUTF)));
                            $mResult = $result->return;
                            AddToLogs('InvoicesSendAnswer', array('Answer' => $mResult));
                            $arJs = array(
                                'IDWEB' => $zayav_id,
                                'INN' => $agent_array['inn'],
                                'DATE' => date('Y-m-d'),
                                'COMPANY_SENDER' => $agent_array['name'],
                                'NAME_SENDER' => $agent_array['user_name'],
                                'PHONE_SENDER' => $agent_array['phones'],
                                'ADRESS_SENDER' => strlen($agent_array['adress_fact']) ? $agent_array['adress_fact'] : $agent_array['adress'],
                                'INDEX_SENDER' => '',
                                'ID_CITY_SENDER' => $agent_array['city'],
                                'DELIVERY_TYPE' => 'Стандарт',
                                'PAYMENT_TYPE' => 'Наличные',
                                'DELIVERY_PAYER' => 'Отправитель',
                                'DELIVERY_CONDITION' => 'ПоАдресу',
                                'DATE_TAKE_FROM' => substr($_POST['date'],6,4).'-'.substr($_POST['date'],3,2).'-'.substr($_POST['date'],0,2).' '.$_POST['from'].':00',
                                'DATE_TAKE_TO' => substr($_POST['date'],6,4).'-'.substr($_POST['date'],3,2).'-'.substr($_POST['date'],0,2).' '.$_POST['to'].':00',
                                'INSTRUCTIONS' => 'Накладные: '.implode(', ',$orders_names).'. '.trim($_POST['comment'])
                            );
                            $arM = convArrayToUTF($arJs);
                            $result = $client->SetCallingTheCourier(array('ListOfDocs' => json_encode($arM)));
                            $mResult = $result->return;
                            $obj = json_decode($mResult, true);
                            $arRes = arFromUtfToWin($obj);
                            $arLogResult = array('Title' => 'Новая заявка на вызов курьера (редактирование)','Response' => $mResult, 'status' => $arRes[0]['status'], 'comment' => $arRes[0]['comment']);
                            $arLog = array_merge($arJs,$arLogResult);
                            AddToLogs('callingCourier',$arLog);
                        }
                        $_SESSION['MESSAGE'][] = '<a href="/suppliers/index.php?mode=call_courier&id='.$zayav_id.'">Заявка на вызов курьера №'.$_POST['max_n'].'</a> отправлена';
                    }
                    else
                    {
                        $_SESSION['MESSAGE'][] = '<a href="/suppliers/index.php?mode=call_courier_edit&id='.$zayav_id.'">Заявка на вызов курьера №'.$_POST['max_n'].'</a> сохранена';
                    }
                    LocalRedirect("/suppliers/index.php?mode=requests");
                }
            }
        }

        $arResult["REQUEST"] = GetOneRequest(intval($_GET['id']), $agent_array['id']);
        if ($arResult["REQUEST"])
        {
            $arResult["TITLE"] = "Заявка вызов курьера №".$arResult["REQUEST"]["PROPERTY_NUMBER_VALUE"];
            $arResult["SHOP_ID"] = $agent_array["id"];
            $arResult["PRICE"] = WhatIsPrice($agent_array['id'],3);
            $arResult['DISPLAY'] = array(
                'coun_rows' => count($arResult["REQUEST"]['packs']),
                'weight_all' => $arResult["REQUEST"]['PROPERTY_WEIGHT_VALUE'],
                'size_1' => $arResult["REQUEST"]["PROPERTY_SIZE_1_VALUE"],
                'size_2' => $arResult["REQUEST"]["PROPERTY_SIZE_2_VALUE"],
                'size_3' => $arResult["REQUEST"]["PROPERTY_SIZE_3_VALUE"],
                'date' => $arResult["REQUEST"]["PROPERTY_DATE_VALUE"],
                'from' => $arResult["REQUEST"]["FROM"],
                'to' => $arResult["REQUEST"]["TO"],
                'comment' => $arResult["REQUEST"]["PROPERTY_COMMENT_VALUE"]["TEXT"]
            );
            $j = 0;
            if (count($arResult["REQUEST"]['packs']) > 0)
            {
                foreach ($arResult["REQUEST"]['packs'] as $ord)
                {
                    $arResult['DISPLAY']['el_id'][$j] = $j;
                    $arResult['DISPLAY']['order_id'][$j] = $ord['ID'];
                    $arResult['DISPLAY']['number'][$j] = $ord['PROPERTY_N_ZAKAZ_IN_VALUE'];
                    $arResult['DISPLAY']['weight'][$j] = $ord['PROPERTY_WEIGHT_VALUE'];
                    $arResult['DISPLAY']['count'][$j] = 1;
                    $j++;
                }
            }
            else
            {
                $arResult['DISPLAY']['el_id'][$j] = $j;
                $arResult['DISPLAY']['order_id'][$j] = '';
                $arResult['DISPLAY']['number'][$j] = '';
                $arResult['DISPLAY']['weight'][$j] = 0;
                $arResult['DISPLAY']['count'][$j] = 1;
            }
        }
        else
        {
            $arResult["TITLE"] = GetMessage("ERR_NOT");
        }
    }

    /*****************заявка на забор******************/
    if ($mode == 'request')
    {
        $arResult['REQUEST'] = GetOneRequest(intval($_GET['id']), $agent_array['id']);
        if ($arResult['REQUEST'])
        {
            if ($arResult['REQUEST']['PROPERTY_TYPE_ENUM_ID'] == 181)
            {
                $arResult['TITLE'] = GetMessage('TTL_6', array('#NUMBER#' => $arResult['REQUEST']['PROPERTY_NUMBER_VALUE']));
            }
            else
            {
                $arResult['TITLE'] = GetMessage('TTL_7', array('#NUMBER#' => $arResult['REQUEST']['PROPERTY_NUMBER_VALUE']));
            }
        }
        else
        {
            $arResult["TITLE"] = GetMessage("ERR_NOT");
        }
    }

    /**********редактирование заявки на забор**********/
    if ($mode == 'request_edit')
    {
        if (isset($_POST['save']))
        {
            $arResult['DISPLAY'] = $_POST;
            unset($arResult['DISPLAY']['add']);

            if ($arResult['DISPLAY']['supplier'] == 0)
            {
                $arResult["ERRORS"][] = GetMessage("ERR_WRONG_SUP");
            }

            if (!strlen($arResult['DISPLAY']['date']))
            {
                $arResult["ERRORS"][] = GetMessage("ERR_WRONG_DATE");
            }

            $when_delivery = '';
            if (strlen($arResult['DISPLAY']['date']))
            {
                $when_delivery = DateFF($arResult['DISPLAY']['date']);
            }
            if (strlen($arResult['DISPLAY']['from']))
            {
                if (strlen($when_delivery))
                    $when_delivery .= ' ';
                $when_delivery .= GetMessage("FROM_TIME", array("#TIME#" => $arResult['DISPLAY']['from']));
            }
            if (strlen($arResult['DISPLAY']['to']))
            {
                if (strlen($when_delivery))
                    $when_delivery .= ' ';
                $when_delivery .= GetMessage("TO_TIME", array("#TIME#" => $arResult['DISPLAY']['to']));
            }

            $all_fields = true;

            foreach ($arResult['DISPLAY']['weight'] as $k => $v)
            {
                if (!strlen($arResult['DISPLAY']['name'][$k]))
                    $all_fields = false;
                if (str_replace(',','.',$arResult['DISPLAY']['weight'][$k]) <= 0)
                    $all_fields = false;
                if (intval($arResult['DISPLAY']['count'][$k]) == 0)
                    $all_fields = false;
                $arResult['DISPLAY']['el_id'][$k] = $k;
            }

            if (!$all_fields)
                $arResult["ERRORS"][] = GetMessage("ERR_TABLE");

            if (count($arResult["ERRORS"]) == 0)
            {
                $sup = $arResult['DISPLAY']['supplier'];
                $arChange = array(
                    421 => $sup,
                    422 => $arResult['DISPLAY']['weight_all'],
                    423 => str_replace(',','.',$arResult['DISPLAY']['size_1']),
                    424 => str_replace(',','.',$arResult['DISPLAY']['size_2']),
                    425 => str_replace(',','.',$arResult['DISPLAY']['size_3']),
                    426 => $when_delivery,
                    427 => array("VALUE" => array ("TEXT" => $arResult['DISPLAY']['comment'], "TYPE" => "text")),
                    435 => 0,
                    437 => $arResult['DISPLAY']['date'],
                    435 => $arResult['DISPLAY']['cost_of']
                );
                if (intval($arResult['DISPLAY']['send']) == 1)
                {
                    $arChange[433] = 183;
                    $send = true;
                }
                else
                {
                    $arChange[433] = false;
                    $send = false;
                }
                $zayav_id = $_POST['request_id'];
                CIBlockElement::SetPropertyValuesEx($zayav_id, 76, $arChange);
                $array_to_delete = array();
                $array_to_cahge = array();
                $array_to_not_add_index = array();
                foreach ($_POST['el_id_del'] as $d) {
                    if(in_array($d,$arResult['DISPLAY']['id_element']))
                    {
                        $array_to_cahge[] = $d;
                        $array_to_not_add_index[] = array_search($d,$arResult['DISPLAY']['id_element']);
                    }
                    else
                    {
                        $array_to_delete[] = $d;
                    }
                }
                foreach ($array_to_delete as $d)
                {
                    CIBlockElement::Delete($d);
                }
                foreach ($array_to_cahge as $k => $v)
                {
                    $props_vals = array(430 => str_replace(',','.',$arResult['DISPLAY']['weight'][$k]));
                    $nn = $arResult['DISPLAY']['name'][$k];
                    $props_vals[431] = $arResult['DISPLAY']['article'][$k];
                    $props_vals[432] = $arResult['DISPLAY']['count'][$k];
                    $t_txt = 'забор у поставщика';
                    $el_update = new CIBlockElement;
                    $res_update = $el_update->Update($v, array("MODIFIED_BY" => $u_id, "NAME" => $nn));
                    CIBlockElement::SetPropertyValuesEx($v, false, $props_vals);
                }


                foreach ($arResult['DISPLAY']['el_id'] as $k => $v)
                {
                    if (!in_array($k,$array_to_not_add_index))
                    {
                        $order = false;
                        $nn = $arResult['DISPLAY']['name'][$k];
                        $art = $arResult['DISPLAY']['article'][$k];
                        $count = $arResult['DISPLAY']['count'][$k];
                        $t_txt = 'забор у поставщика';
                        $el_2 = new CIBlockElement;
                        $arLoadProductArray_2 = array(
                            "MODIFIED_BY"    => $u_id,
                            "IBLOCK_SECTION_ID" => false,
                            "IBLOCK_ID"      => 77,
                            "PROPERTY_VALUES"=> array(
                                428 => $zayav_id,
                                429 => $order,
                                430 => str_replace(',','.',$arResult['DISPLAY']['weight'][$k]),
                                431 => $art,
                                432 => $count
                            ),
                            "NAME"           => $nn,
                            "ACTIVE"         => "Y"
                        );
                        $zayav_element = $el_2->Add($arLoadProductArray_2);
                    }
                }
                unset($arResult['DISPLAY']);
                if ($send)
                {
                    $arUksSupls = array();
                    $res = CIBlockElement::GetList(
                        array("sort" => "asc"),
                        array("IBLOCK_ID" => 40, "ACTIVE" => "Y", 'PROPERTY_TYPE' => 51, 'PROPERTY_CITY' => $_POST['city_id']),
                        false,
                        false,
                        array("ID", "NAME", "DATE_ACTIVE_FROM")
                    );
                    while($ob = $res->GetNextElement())
                    {
                        $arFields = $ob->GetFields();
                        $arUksSupls[] = $arFields['ID'];
                    }
                    $agent_to = $agent_array['uk'];
                    $send_to_other = false;
                    if (count($arUksSupls) > 0)
                    {
                        if (!in_array($agent_array['uk'], $arUksSupls))
                        {
                            $agent_to = $arUksSupls[0];
                            $send_to_other = true;
                        }
                    }
                    $agent_from = $agent_array['id'];
                    $ddd = date('d.m.Y H:i');
                    $arParamsMsg = array(
                        "DATE_SEND" => date('d.m.Y H:i'),
                        "SHOP_ID" => $agent_array['id'],
                        "SHOP_NAME" => $agent_array['name'],
                        "ORDER_ID" => $zayav_id,
                        "ORDER_NUMBER" => $_POST['max_n'],
                        "LINK_TO_MSG" => ""
                    );
                    $qw = SendMessageInSystem($u_id, $agent_from, $agent_to, 'Новая заявка на забор у поставщика', 188, '', '', 178, $arParamsMsg);
                    $arParamsMsg["LINK_TO_MSG"] = '<p><a href="http://dms.newpartner.ru/messages/index.php?mode=detail&id='.$qw.'">Ссылка на данное сообщение в DMS "Новый Партнер"</a></p>';
                    $f = GetRequestsPDF($agent_array['id'], $zayav_id, 'F', $send_to_other);
                    $send = SendMessageMailNew($agent_to, $agent_from, 189, 178, $arParamsMsg, array($f));
                    $_SESSION['MESSAGE'][] = '<a href="/suppliers/index.php?mode=request&id='.$zayav_id.'">Заявка на забор у поставщика №'.$_POST['max_n'].'</a> отправлена';
                    LocalRedirect("/suppliers/index.php?mode=requests");
                }
                else
                {
                    $arResult['MESSAGE'][] = '<a href="/suppliers/index.php?mode=request_edit&id='.$zayav_id.'">Заявка на забор у поставщика №'.$_POST['max_n'].'</a> сохранена';
                }
            }
        }

        $arResult["REQUEST"] = GetOneRequest(intval($_GET['id']), $agent_array['id']);
        if ($arResult["REQUEST"])
        {
            $arResult['DISPLAY'] = array(
                'coun_rows' => count($arResult["REQUEST"]['orders']),
                'supplier' => $arResult["REQUEST"]["PROPERTY_SUPPLIER_VALUE"],
                'city_id' => 0,
                'weight_all' => $arResult["REQUEST"]['PROPERTY_WEIGHT_VALUE'],
                'size_1' => $arResult["REQUEST"]["PROPERTY_SIZE_1_VALUE"],
                'size_2' => $arResult["REQUEST"]["PROPERTY_SIZE_2_VALUE"],
                'size_3' => $arResult["REQUEST"]["PROPERTY_SIZE_3_VALUE"],
                'date' => $arResult["REQUEST"]["PROPERTY_DATE_VALUE"],
                'from' => $arResult["REQUEST"]["FROM"],
                'to' => $arResult["REQUEST"]["TO"],
                'comment' => $arResult["REQUEST"]["PROPERTY_COMMENT_VALUE"]["TEXT"]
            );
            if (count($arResult["REQUEST"]['orders']) <= 0)
            {
                $arResult['DISPLAY']['coun_rows'] = 1;
            }
            $arResult["TITLE"] = GetMessage("TTL_6", array("#NUMBER#" => $arResult["REQUEST"]["PROPERTY_NUMBER_VALUE"]));
            $arResult["TYPE"] = 2;
            $arResult["SUPPLIERS"] = GetListSuppliers($agent_array['id'],false,true,array("ID"=>"NAME"));
            $arResult['SHOP_ID'] = $agent_array["id"];
            $arResult['PRICE'] = WhatIsPrice($agent_array['id'],3);
            $j = 0;
            if (count($arResult["REQUEST"]['orders']) > 0)
            {
                foreach ($arResult["REQUEST"]['orders'] as $ord)
                {
                    $arResult['DISPLAY']['el_id'][$j] = $ord['ID'];
                    $arResult['DISPLAY']['name'][$j] = $ord['NAME'];
                    $arResult['DISPLAY']['article'][$j] = $ord['PROPERTY_431_VALUE'];
                    $arResult['DISPLAY']['weight'][$j] = $ord['PROPERTY_430_VALUE'];
                    $arResult['DISPLAY']['count'][$j] = $ord['PROPERTY_432_VALUE'];
                    $j++;
                }
            }
            else
            {
                $arResult['DISPLAY']['el_id'][$j] = '';
                $arResult['DISPLAY']['name'][$j] = '';
                $arResult['DISPLAY']['article'][$j] = '';
                $arResult['DISPLAY']['weight'][$j] = 0;
                $arResult['DISPLAY']['count'][$j] = 1;
            }
        }
        else
        {
            $arResult["TITLE"] = GetMessage("ERR_NOT");
        }
    }
}

$this->IncludeComponentTemplate($componentPage);

