<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
    die();
}

include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");
define("DEFAULT_ZONE_REGION", 6);
define("DEFAULT_DEV_REGION", '3-5');
define("DEFAULT_DISTANCE_MO", 50);
$arResult = [];

if($_GET['mode']=="index"){
    $arrPost =  $_POST;
    $arrPost = arFromUtfToWin($arrPost);

    $city_0_ok = explode(',', $arrPost['city_0']);
    if(count($city_0_ok)==3){
        $city_0_ok = true;
    }else{
        $city_0_ok = false;
    }
    $city_1_ok = explode(',', $arrPost['city_1']);
    if(count($city_1_ok)==3){
        $city_1_ok = true;
    } else{
        $city_1_ok = false;
    }


    if(!empty($_POST['citycode_0']) && !empty($_POST['citycode_1']) &&  $city_0_ok && $city_1_ok){
        $kf =  WhatIsGabWeight();       /* 5000 */

        //AddToLogs("testCalc", $arrPost);
        foreach($arrPost as $key=>$value){
            $string = $key;
            $str = $string[0];
            if($str == "r"){
                $pref = $string[1];
                $k = $str.$pref;
                if(!empty($arrPost[$k])){
                    if($pref == 1){

                        foreach($arrPost[$k] as $i=>$v){
                            $arPack[0][$i] = $v;
                        }
                    }
                    if($pref == 2){
                        foreach($arrPost[$k] as $i=>$v){
                            $arPack[1][$i] = $v;
                        }
                    }
                    if($pref == 3){
                        foreach($arrPost[$k] as $i=>$v){
                            $arPack[2][$i] = $v;
                        }
                    }
                }
            }
        }
        $c=count($arPack[0]);

        //dump($arPack);
       //AddToLogs("testCalc", $arPack);
       //exit;
        foreach($arPack as $key=>$value){
            if($key==0){
                for($i=0;$i<$c;$i++){
                    $arr[$i]['h'] = (float)$value[$i];
                }
            }
            if($key==1){
                for($i=0;$i<$c;$i++){
                    $arr[$i]['l'] = (float)$value[$i];
                }
            }
            if($key==2){
                for($i=0;$i<$c;$i++){
                    $arr[$i]['w'] = (float)$value[$i];
                }
            }
        }
        foreach($arrPost['ves'] as $key=>$value){
            $arr[$key]['ves'] = (float)$value;
        }
        $obW = 0.00;
        $vesIt = 0.00;
        foreach($arr as $item=>$value){
            if($value['ves']>0 && ($value['h']>0 && $value['l']>0 && $value['w']>0)){
                $obW = ($value['h']*$value['l']*$value['w'])/$kf;
                if($value['ves']>$obW){
                    $vesIt += (float)$value['ves'];
                }else{
                    $vesIt += $obW;
                }
            }elseif($value['ves']>0 && ($value['h']==0 && $value['l']==0 && $value['w']==0)){
                $vesIt += (float)$value['ves'];
            }elseif($value['ves']==0 && ($value['h']>0 && $value['l']>0 && $value['w']>0)){

                $vesIt += ($value['h']*$value['l']*$value['w'])/$kf;
            }elseif($value['ves']==0 && ($value['h']==0 && $value['l']==0 && $value['w']==0)){
                $vesIt += 0.10;   /* письмо */
            }
            else{
                if($value['ves']>0){
                    $vesIt += (float)$value['ves'];
                }else{
                    $vesIt += 0.00;
                }
            }
        }

        $arResult['GAB'] = $arr;
        $arResult['FULLWEIGTH'] = $vesIt;
        $id_sender = (int)$arrPost['citycode_0'];
        $arResult['ID_SENDER'] = $id_sender;
        $id_recipient = (int)$arrPost['citycode_1'];
        $arResult['ID_RECIPIENT'] = $id_recipient;
        $name_sender = trim(strip_tags($arrPost['city_0']));
        $arResult['SENDER']['FULLNAME'] = $name_sender;
        $name_recipient = trim(strip_tags($arrPost['city_1']));
        $arResult['RECIPIENT']['FULLNAME'] = $name_recipient;
        $arSelect = [
            "ID","NAME","IBLOCK_SECTION_ID",
            "PROPERTY_ZONE", "PROPERTY_TIME_DELIVERY",
            "PROPERTY_SPRAV", "PROPERTY_COEFF"
        ];
        $arr_s = GetInfoArr(false, $id_sender, 6, $arSelect);
        $arr_r = GetInfoArr( false, $id_recipient, 6, $arSelect);
        if(is_array($arr_s)){
            foreach($arr_s as $key=>$value){
                $arResult['SENDER'][$key] = $value;
            }
        }else{
            $arResult['ERROR']['SENDER'] = GetMessage('ERR_INPUT_SENDER');
        }
        if(is_array($arr_r)) {
            foreach ($arr_r as $key => $value) {
                $arResult['RECIPIENT'][$key] = $value;
            }
        }else{
            $arResult['ERROR']['RECIPIENT'] = GetMessage('ERR_INPUT_RECIPIENT');
        }

        //AddToLogs("testCalc", $arResult);
        //exit;

        /************************************************************************/
        $tarif = 0.00;
        $coeff = 0.00;
        $arSelect = [
            "NAME",
            "IBLOCK_ID",
            "ID",
            "PROPERTY_*",
        ];
        /* не Москва и не Питер */
        if(($arResult['SENDER']['ID'] != '8054' && $arResult['RECIPIENT']['ID'] != '8054') &&
            ($arResult['SENDER']['NAME'] !=GetMessage('SPB') && $arResult['RECIPIENT']['NAME'] != GetMessage('SPB')) &&
            ($arResult['SENDER']['SECTION_NAME'] != GetMessage('MOS_OBL') && $arResult['RECIPIENT']['SECTION_NAME'] != GetMessage('MOS_OBL')) &&
            ($arResult['SENDER']['SECTION_NAME'] != GetMessage('LEN_OBL') && $arResult['RECIPIENT']['SECTION_NAME'] != GetMessage('LEN_OBL')) &&
            ($arResult['SENDER']['NAME'] != $arResult['RECIPIENT']['NAME'])&&($arResult['SENDER']['IBLOCK_SECTION_ID'] != $arResult['RECIPIENT']['IBLOCK_SECTION_ID'])
        ){
            if( $arResult['RECIPIENT']['PROPERTY_SPRAV_VALUE'] == "Y" && $arResult['SENDER']['PROPERTY_SPRAV_VALUE'] == "Y"){
                $coeff = 1.0;
            }elseif( ($arResult['RECIPIENT']['PROPERTY_SPRAV_VALUE'] != "Y" && $arResult['SENDER']['PROPERTY_SPRAV_VALUE'] == "Y") || ($arResult['RECIPIENT']['PROPERTY_SPRAV_VALUE'] == "Y" && $arResult['SENDER']['PROPERTY_SPRAV_VALUE'] != "Y")){
                $coeff = 1.8;
            }elseif( ($arResult['RECIPIENT']['PROPERTY_SPRAV_VALUE'] != "Y" && $arResult['SENDER']['PROPERTY_SPRAV_VALUE'] != "Y")){
                $coeff = 2.5;
            }
            if(!empty($arResult['SENDER']['PROPERTY_ZONE_VALUE'])){
                $zSend = (int)$arResult['SENDER']['PROPERTY_ZONE_VALUE'];
            }else{
                $arResult['ERROR']['SENDER_ZONE'] = GetMessage('ERR_INPUT_SENDER_ZONE');
                //echo "обработать ошибку - значение зона отправителя пусто<br>";
            }
            if(!empty($arResult['RECIPIENT']['PROPERTY_ZONE_VALUE'])){
                $zRep = (int)$arResult['RECIPIENT']['PROPERTY_ZONE_VALUE'];
            }else{
                $arResult['ERROR']['RECIPIENT_ZONE'] = GetMessage('ERR_INPUT_RECIPIENT_ZONE');
                //echo "обработать ошибку - значение зона получателя пусто<br>";
            }

            if($zSend>0 && $zRep>0){
                $code_recipient_zone = "zone-".(string)$zRep;
                //dump($code_recipient_zone);
                $zone = GetInfoArr( $code_recipient_zone,false, 101, $arSelect);
                //dump($zone);
                if((int)$zone['PROPERTIES']['ZONE_DISPATCH_'.(string)$zRep]['VALUE']>0){
                    $zoneDelivery = $zone['PROPERTIES']['ZONE_DISPATCH_'.(string)$zSend]['VALUE'];
                    $arResult['ZONEDEV'] = $zoneDelivery;
                }
                else
                {
                    $arResult['ERROR']['ZONE_RASCH'] = GetMessage('ERR_ZONE_RASCH');
                    //echo "обработать ошибку - значение зона расчета пусто<br>";
                }
                if((int)$zoneDelivery>0){
                    //dump($zoneDelivery);
                    $time = GetInfoArr( false,49531756, 102, $arSelect);
                    if((int)$time['PROPERTIES']['DELIVERY_TERMS_'.(string)$zoneDelivery]['VALUE']){
                        $timeDelivery = $time['PROPERTIES']['DELIVERY_TERMS_'.(string)$zoneDelivery]['VALUE'];
                        $arResult['TIMEDEV'] = $timeDelivery;
                        //dump($timeDelivery);
                    }
                    else
                    {
                        $arResult['ERROR']['TIMEDEV'] = GetMessage('ERR_TIMEDEV');
                        //echo "обработать ошибку - сроки доставки пусто<br>";
                    }
                }
                else
                {
                    $arResult['ERROR']['ZONE_RASCH_2'] = GetMessage('ERR_ZONE_RASCH_2');
                    //echo "обработать ошибку - зона расчетная пусто<br>";
                }
            }
            else
            {
                $arResult['ERROR']['ZONE_RASCH_3'] = GetMessage('ERR_ZONE_RASCH_3');
                //echo "обработать ошибку зона получателя или зона отправителя вернули 0<br>";
            }
        }
        elseif(($arResult['SENDER']['ID'] == 8054 && $arResult['RECIPIENT']['SECTION_NAME'] != GetMessage('MOS_OBL')) ||
            ($arResult['RECIPIENT']['ID'] == 8054 && $arResult['SENDER']['SECTION_NAME'] != GetMessage('MOS_OBL')))
        {  /* Москва - в другой регион кроме Московской области*/
            //dump($arResult);
            if(!empty($arResult['SENDER']['PROPERTY_COEFF_VALUE'])){
                $coeff = (float)$arResult['SENDER']['PROPERTY_COEFF_VALUE'];
            }
            elseif(!empty($arResult['RECIPIENT']['PROPERTY_COEFF_VALUE'])){
                $coeff = (float)$arResult['RECIPIENT']['PROPERTY_COEFF_VALUE'];
            }else{
                $arResult['ERROR']['BAZE_COEFF_VALUE'] = GetMessage('ERR_BAZE_COEFF_VALUE');
                //echo 'обработать ошибку - базовый коэфф. при доставке Москва отсутствует ';
            }

            if(!empty($arResult['SENDER']['PROPERTY_ZONE_VALUE'])){
                $arResult['ZONEDEV'] = (int)$arResult['SENDER']['PROPERTY_ZONE_VALUE'];
            }
            elseif(!empty($arResult['RECIPIENT']['PROPERTY_ZONE_VALUE'])){
                $arResult['ZONEDEV'] = (int)$arResult['RECIPIENT']['PROPERTY_ZONE_VALUE'];
            }
            else{
                $arResult['ERROR']['BAZE_ZONE'] = GetMessage('ERR_BAZE_ZONE');
                //echo 'обработать ошибку - базовая зона Москва отсутствует';
            }

            if(!empty($arResult['SENDER']['PROPERTY_TIME_DELIVERY_VALUE'])){
                $arResult['TIMEDEV'] = (string)$arResult['SENDER']['PROPERTY_TIME_DELIVERY_VALUE'];
            }
            elseif(!empty($arResult['RECIPIENT']['PROPERTY_TIME_DELIVERY_VALUE'])){
                $arResult['TIMEDEV'] = (string)$arResult['RECIPIENT']['PROPERTY_TIME_DELIVERY_VALUE'];
            }
            else{
                $arResult['ERROR']['TIME_DELIVERY_MSK'] = GetMessage('TIME_DELIVERY_MSK');
                //echo 'обработать ошибку - время доставки Москва отсутствует';
            }

        }
        elseif($arResult['SENDER']['NAME'] == GetMessage('SPB') || $arResult['RECIPIENT']['NAME'] == GetMessage('SPB'))
        {
           // здесь будет доставка из/в Питер
            $arResult['ERROR']['DELIVERY_LEN'] = GetMessage('ERR_RASCHET');
        }
        /* москва -область, МО-МО */
        elseif(($arResult['SENDER']['SECTION_NAME'] == GetMessage('MOS_OBL') ||  $arResult['SENDER']['ID'] == 8054) &&
            ($arResult['RECIPIENT']['SECTION_NAME'] == GetMessage('MOS_OBL') ||  $arResult['RECIPIENT']['ID'] == 8054))
        {
            $coeff = false;
            $arResult['ZONEDEV'] = GetMessage('MOS_OBL');
            $coeff = "Без коэфициента, МО";
            /* получить расстояние до мкад отправителя и получателя внутри области */

            $dist_mkad_sender = GetInfoArr( false,$arResult['SENDER']['ID'], 6, $arSelect);
            $dist_mkad_recipient =  GetInfoArr( false,$arResult['RECIPIENT']['ID'], 6, $arSelect);;
            $dist_mkad_sender = $dist_mkad_sender['PROPERTIES']['OUT_MKAD']['VALUE'];
            $dist_mkad_recipient = $dist_mkad_recipient['PROPERTIES']['OUT_MKAD']['VALUE'];
            //echo " Расстояние от МКАД Отправитель $dist_mkad_sender км.<br>";
            //echo " Расстояние от МКАД Получатель $dist_mkad_recipient км.<br>";
            $arResult['SENDER']["INTERVAL"] =  [" Расстояние от МКАД Отправитель $dist_mkad_sender км.<br>", $dist_mkad_sender];
            $arResult['RECIPIENT']["INTERVAL"] = [" Расстояние от МКАД Получатель $dist_mkad_recipient км.<br>", $dist_mkad_recipient];
            $tr = [];
            if($dist_mkad_sender>0 && $dist_mkad_recipient>0){
                /* область менее 50 км - область менее 50 км  id - 51213823 */
                if($dist_mkad_sender < DEFAULT_DISTANCE_MO && $dist_mkad_recipient < DEFAULT_DISTANCE_MO){
                    $arResult['ZONEDEV'] = "Московская обл.";
                    $tr = GetInfoArr( false,51213823, 103, $arSelect);
                    //echo "Тариф до 0,5кг  $tarif_05 руб. <br>";
                    //echo "Тариф от 0,5кг-1,0кг $tarif_1 руб. <br>";
                    //echo "Плюс на каждый кг свыше 1кг $tarif_hight руб. <br>";
                }
                /* область менее 50 км - область более 50 км  id - 51213826 */
                if(($dist_mkad_sender < DEFAULT_DISTANCE_MO && $dist_mkad_recipient > DEFAULT_DISTANCE_MO)||
                    ($dist_mkad_sender > DEFAULT_DISTANCE_MO && $dist_mkad_recipient < DEFAULT_DISTANCE_MO)){
                    $tr = GetInfoArr( false,51213826, 103, $arSelect);
                    //echo "Тариф до 0,5кг $tarif_05 руб. <br>";
                   // echo "Тариф от 0,5кг-1,0кг $tarif_1 руб. <br>";
                    //echo "Плюс на каждый кг свыше 1кг $tarif_hight руб. <br>";
                }
                /* область более 50 км - область более 50 км  id - 51213827 */
                if($dist_mkad_sender > DEFAULT_DISTANCE_MO && $dist_mkad_recipient > DEFAULT_DISTANCE_MO){
                    $tr = GetInfoArr( false,51213827, 103, $arSelect);
                    //echo "Тариф до 0,5кг $tarif_05 руб. <br>";
                    //echo "Тариф от 0,5кг-1,0кг $tarif_1 руб. <br>";
                    //echo "Плюс на каждый кг свыше 1кг $tarif_hight руб. <br>";
                }
            }
            elseif(($arResult['SENDER']['ID'] == 8054 && $dist_mkad_recipient>0)||
                ($arResult['RECIPIENT']['ID'] == 8054 && $dist_mkad_sender>0)){
                /* Москва - область более 50 км  id - 51213822 */
                if(($dist_mkad_recipient > DEFAULT_DISTANCE_MO && $arResult['SENDER']['ID'] == 8054)||
                    ($dist_mkad_sender > DEFAULT_DISTANCE_MO && $arResult['RECIPIENT']['ID'] == 8054)){
                    $tr = GetInfoArr( false,51213822, 103, $arSelect);
                }
                /* Москва - область менее 50 км  id - 51213813 */
                if(($dist_mkad_recipient < DEFAULT_DISTANCE_MO && $arResult['SENDER']['ID'] == 8054)||
                    ($dist_mkad_sender < DEFAULT_DISTANCE_MO && $arResult['RECIPIENT']['ID'] == 8054)){
                    $tr = GetInfoArr( false,51213813, 103, $arSelect);
                 }
            }
            else{
                $arResult['ERROR']['NO_DELIVERY_MSK'] = GetMessage('NO_DELIVERY_MSK');
                //echo "обработать ошибку - нет расстояния от мкад<br>";
            }
            $arResult['TIMEDEV']  = $tr['PROPERTIES']['TIME_DELIVERY']['VALUE'];
            $tarif_05 = $tr['PROPERTIES']['WEIGHT_05']['VALUE'];
            $tarif_1 = $tr['PROPERTIES']['WEIGHT_1']['VALUE'];
            $tarif_hight = $tr['PROPERTIES']['WEIGHT_HIGHER']['VALUE'];
        }
        /*Любой - Лен область */
        elseif($arResult['SENDER']['SECTION_NAME'] == GetMessage('LEN_OBL') ||
            $arResult['RECIPIENT']['SECTION_NAME'] == GetMessage('LEN_OBL'))
        {
            $arResult['ERROR']['DELIVERY_LEN_OBL'] = GetMessage('ERR_RASCHET');
        }
        /* внутри областей кроме подмосковья и лен области */
        elseif(($arResult['SENDER']['IBLOCK_SECTION_ID'] == $arResult['RECIPIENT']['IBLOCK_SECTION_ID']) &&
            ($arResult['SENDER']['SECTION_NAME'] != GetMessage('MOS_OBL') ) &&
            ($arResult['SENDER']['SECTION_NAME'] != GetMessage('LEN_OBL'))
        )
        {
            $coeff = 1.0;
            $arResult['ZONEDEV'] = DEFAULT_ZONE_REGION;
            $arResult['TIMEDEV'] = DEFAULT_DEV_REGION;
            // dump($arResult);
        }
        else
        {
            $arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');
            //echo 'обработать ошибку - по введенным данным нас. пунктов расчет не возможен';
        }

        $arResult['RASCH_COEFF'] = $coeff;
        //AddToLogs("testCalc", $arResult);
       // exit;
        //echo 'расчетный коэфф.';
        //dump($arResult['RASCH_COEFF']);
        //echo 'Зона';
        //dump($arResult['ZONEDEV']);
        //echo 'время доставки';
        //dump($arResult['TIMEDEV']);
        //echo 'Полный вес';
        //dump($arResult['FULLWEIGTH']);

        /******************************************************/
        if ((int)$coeff){  /* не Московская область */
            if( $arResult['FULLWEIGTH'] <= 0.50 &&  $arResult['FULLWEIGTH'] > 0){
                $tarif =  GetInfoArr( false,49528186, 100, $arSelect);
            }elseif( $arResult['FULLWEIGTH'] >0.5 &&  $arResult['FULLWEIGTH'] <= 1.00){
                $tarif =  GetInfoArr( false,49528187, 100, $arSelect);
            }
            elseif( $arResult['FULLWEIGTH'] > 1.00 &&  $arResult['FULLWEIGTH'] <= 2.00){
                $tarif =  GetInfoArr( false,49528190, 100, $arSelect);
            }
            elseif( $arResult['FULLWEIGTH'] > 2.00 &&  $arResult['FULLWEIGTH'] <= 3.00){
                $tarif =  GetInfoArr( false,49528223, 100, $arSelect);
            }
            elseif( $arResult['FULLWEIGTH'] > 3.00 &&  $arResult['FULLWEIGTH'] <= 4.00){
                $tarif =  GetInfoArr( false,49528230, 100, $arSelect);
            }
            elseif( $arResult['FULLWEIGTH'] > 4.00){

                $uplimit = (float) $arResult['FULLWEIGTH'] - 4.00;
                $uplimit = ceil($uplimit);
                $tarif4 =  GetInfoArr( false,49528230, 100, $arSelect);
                // dump($tarif4);
                $tarif4 = $tarif4['PROPERTIES']['ZONE_'.$arResult['ZONEDEV']]['VALUE'];
                $tarif =  GetInfoArr( false,49528419, 100, $arSelect);
                // dump($tarif);
            }
            $arResult['TARIF'] =  $tarif;
            if($arResult['FULLWEIGTH']<=4.00){
                $tarif = (float)$arResult['TARIF']['PROPERTIES']['ZONE_'.$arResult['ZONEDEV']]['VALUE']*(float)$arResult['RASCH_COEFF'];
            }else{
                $tarif = ((float)$tarif4+(float)$arResult['TARIF']['PROPERTIES']['ZONE_'.$arResult['ZONEDEV']]['VALUE']*$uplimit)*(float)$arResult['RASCH_COEFF'];
            }
            $arResult['TARIF_ITOG'] = (float) $tarif;
        }else{

            if($arResult['FULLWEIGTH']<=0.50)
            {
                $tarif = $tarif_05;
            }elseif($arResult['FULLWEIGTH']>0.50 && $arResult['FULLWEIGTH']<=1.00)
            {
                $tarif = $tarif_1;
            }elseif($arResult['FULLWEIGTH']>1.00){
                $w_hi = (float)$arResult['FULLWEIGTH'] - 1.00;
                $tarif = (float)($tarif_1 + (ceil($w_hi)*$tarif_hight));
            }
            //dump($tarif);
            $arResult['TARIF_ITOG'] =  $tarif;
        }

    }else{
        $arResult['ERROR']['ERR_INPUT_CITY_INN'] = GetMessage('ERR_INPUT_CITY_INN');
    }

    AddToLogs("testCalc", $arResult);
    $arResult = convArrayToUTF($arResult);

    $data = [
       "data"=>$arResult
    ];
    $dataJson = json_encode($data);

    echo $dataJson;

    exit;
}

$this->IncludeComponentTemplate();