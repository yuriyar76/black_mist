<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
    die();
}
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");
define("DEFAULT_ZONE_REGION", 6); /* зона по умолчанию для доставки внутри региона */
define("DEFAULT_DEV_REGION", '3-5');  /* срок доставки внутри региона */
define("DEFAULT_DEV_CITY", '1');  /* срок доставки внутри основного города */
define("DEFAULT_DISTANCE", 50); /* расстояние от МКАД/КАД */
define("C_1", 1.0);  /* основной - основной */
define("C_2", 1.8);  /* основной - не основной */
define("C_3", 2.5);  /* не основной - не основной */
define("TMain", 300);  /* тариф внутри основных городов */
define("THMain", 30);   /* тариф превышения внутри основных городов */
$arResult = [];
$arRrTarif = [];
$flag = false;
if($_GET['mode'] === "index"){
    if($_GET['request'] === 'Y'){
        $areq =  json_decode($_POST['data'], true);
        /*  массив $aresult
         [city_recipient] => Ярославль, Ярославская обл., Россия
         [city_sender] => Москва, Москва, Россия
         [weight] => 0.1
      */
        if ($_GET['api'] === 'Y'){
            $capital = iconv('windows-1251', 'utf-8', 'Россия');
            $city_send_api = $areq['city_sender'];
            $region_send_api = $areq['region_cender'];
            $city_rec_api = $areq['city_recipient'];
            $region_rec_api = $areq['region_recipient'];
            $areq['city_sender'] =  $city_send_api . ', ' . $region_send_api . ', ' . $capital;
            $areq['city_recipient'] = $city_rec_api . ', ' . $region_rec_api . ', ' . $capital;
        }
        if (empty($areq['weight']))  $areq['weight'] = 1.0;
        $id_city_send = GetCityId(iconv('utf-8', 'windows-1251',$areq['city_sender']));
        $id_city_rec = GetCityId(iconv('utf-8', 'windows-1251',$areq['city_recipient']));
        $_POST = [];
        $_POST['city_1'] = $areq['city_sender'];
        $_POST['citycode_1'] = (int)$id_city_send;
        $_POST['city_0'] = $areq['city_recipient'];
        $_POST['citycode_0'] = (int)$id_city_rec;
        $_POST['r1'][0] = '';
        $_POST['r2'][0] = '';
        $_POST['r3'][0] = '';
        $_POST['ves'][0] = $areq['weight'];

    }
    $arrPost =  $_POST;
    $arrPost = arFromUtfToWin($arrPost);

    if (!empty($arrPost['ves'][0]) && !isset($_GET['type'])){
        if(preg_match('/,/i', $arrPost['ves'][0])){
            $vzam =  preg_replace('/,/i', '.', $arrPost['ves'][0]);
            $arrPost['ves'][0] = $vzam;
        }
    }
    if (!empty($arrPost['ves'] && $_GET['type']==="z")){
        if(preg_match('/,/i', $arrPost['ves'])){
            $vzam =  preg_replace('/,/i', '.', $arrPost['ves']);
            $arrPost['ves'] = $vzam;
        }
    }
    AddToLogs("testCalс", $arrPost);
    $city_0_ok = explode(',', $arrPost['city_0']);
    if(count($city_0_ok)==3){
        if (empty($arrPost['citycode_0'])){
            $arrPost['citycode_0'] = GetCityId($arrPost['city_0']);
        }
        $city_0_ok = true;
    }else{
        $city_0_ok = false;
    }
    $city_1_ok = explode(',', $arrPost['city_1']);
    if(count($city_1_ok)==3){
        if (empty($arrPost['citycode_1'])){
            $arrPost['citycode_1'] = GetCityId($arrPost['city_1']);
        }
        $city_1_ok = true;
    } else{
        $city_1_ok = false;
    }

    if(!empty($arrPost['citycode_0']) && !empty($arrPost['citycode_1']) &&  $city_0_ok && $city_1_ok){
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

       /* if($USER->isAdmin()){
            dump ($vesIt);
            exit;
        }*/
        if ($_GET['type'] === 'z' ){
            if(!$arrPost['ves']){
                $arrPost['ves'] = 0.10;
            }
            $arResult['FULLWEIGTH'] = $arrPost['ves'];
        }else{
            $arResult['FULLWEIGTH'] = $vesIt;
        }

        $arResult['GAB'] = $arr;

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
            "PROPERTY_SPRAV", "PROPERTY_COEFF", "PROPERTY_SMALL_DEV"
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
        /* Москва - в другой регион кроме Московской области*/
        function GetMsk(array &$arResult){
            if(!empty($arResult['SENDER']['PROPERTY_COEFF_VALUE'])){
                $coeff = (float)$arResult['SENDER']['PROPERTY_COEFF_VALUE'];
            }
            elseif(!empty($arResult['RECIPIENT']['PROPERTY_COEFF_VALUE'])){
                $coeff = (float)$arResult['RECIPIENT']['PROPERTY_COEFF_VALUE'];
            }else{
                $arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');
                //echo 'обработать ошибку - базовый коэфф. при доставке Москва отсутствует ';
            }

            if(!empty($arResult['SENDER']['PROPERTY_ZONE_VALUE'])){
                $arResult['ZONEDEV'] = (int)$arResult['SENDER']['PROPERTY_ZONE_VALUE'];
            }
            elseif(!empty($arResult['RECIPIENT']['PROPERTY_ZONE_VALUE'])){
                $arResult['ZONEDEV'] = (int)$arResult['RECIPIENT']['PROPERTY_ZONE_VALUE'];
            }
            else{
                $arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');
                //echo 'обработать ошибку - базовая зона Москва отсутствует';
            }

            if(!empty($arResult['SENDER']['PROPERTY_TIME_DELIVERY_VALUE'])){
                $arResult['TIMEDEV'] = (string)$arResult['SENDER']['PROPERTY_TIME_DELIVERY_VALUE'];
            }
            elseif(!empty($arResult['RECIPIENT']['PROPERTY_TIME_DELIVERY_VALUE'])){
                $arResult['TIMEDEV'] = (string)$arResult['RECIPIENT']['PROPERTY_TIME_DELIVERY_VALUE'];
            }
            else{
                $arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');
                //echo 'обработать ошибку - время доставки Москва отсутствует';
            }
            return $coeff;
        }
        /* для комплексного расчета */
        function getMskCompl(array $arrCompl){
            $coeff = GetMsk($arrCompl);
            $arrCompl['RASCH_COEFF'] = $coeff;
            return $arrCompl;
        }
        /* не Москва и не Питер */
        function GetRegion(array &$arResult, array $arSelect){
            if( $arResult['RECIPIENT']['PROPERTY_SPRAV_VALUE'] == "Y" && $arResult['SENDER']['PROPERTY_SPRAV_VALUE'] == "Y"){
                $coeff = C_1;
            }elseif( ($arResult['RECIPIENT']['PROPERTY_SPRAV_VALUE'] != "Y" && $arResult['SENDER']['PROPERTY_SPRAV_VALUE'] == "Y") ||
                ($arResult['RECIPIENT']['PROPERTY_SPRAV_VALUE'] == "Y" && $arResult['SENDER']['PROPERTY_SPRAV_VALUE'] != "Y"))
            {
                $coeff = C_2;
            }elseif( ($arResult['RECIPIENT']['PROPERTY_SPRAV_VALUE'] != "Y" && $arResult['SENDER']['PROPERTY_SPRAV_VALUE'] != "Y")){
                $coeff = C_3;
            }
            if(!empty($arResult['SENDER']['PROPERTY_ZONE_VALUE'])){
                $zSend = (int)$arResult['SENDER']['PROPERTY_ZONE_VALUE'];
            }else{
                $arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');
                //echo "обработать ошибку - значение зона отправителя пусто<br>";
            }
            if(!empty($arResult['RECIPIENT']['PROPERTY_ZONE_VALUE'])){
                $zRep = (int)$arResult['RECIPIENT']['PROPERTY_ZONE_VALUE'];
            }else{
                $arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');
                //echo "обработать ошибку - значение зона получателя пусто<br>";
            }
            if($zSend>0 && $zRep>0){
                $code_recipient_zone = "zone-".(string)$zRep;
                $zone = GetInfoArr( $code_recipient_zone,false, 101, $arSelect);
                if((int)$zone['PROPERTIES']['ZONE_DISPATCH_'.(string)$zRep]['VALUE']>0){
                    $zoneDelivery = $zone['PROPERTIES']['ZONE_DISPATCH_'.(string)$zSend]['VALUE'];
                    $arResult['ZONEDEV'] = $zoneDelivery;
                }
                else
                {
                    $arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');
                    //echo "обработать ошибку - значение зона расчета пусто<br>";
                }
                if((int)$zoneDelivery>0){
                    //dump($zoneDelivery);
                    $time = GetInfoArr( false,49531756, 102, $arSelect);
                    if((int)$time['PROPERTIES']['DELIVERY_TERMS_'.(string)$zoneDelivery]['VALUE']){
                        $timeDelivery = $time['PROPERTIES']['DELIVERY_TERMS_'.(string)$zoneDelivery]['VALUE'];
                        // основной - не основной
                        if($coeff === C_2) {
                            $arResult['TIMEDEV'] = GetMessage('NON_TIME');
                        }else{
                            $arResult['TIMEDEV'] = $timeDelivery;
                        }

                      }
                    else
                    {
                        $arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');
                        //echo "обработать ошибку - сроки доставки пусто<br>";
                    }
                }
                else
                {
                    $arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');
                    //echo "обработать ошибку - зона расчетная пусто<br>";
                }
            }
            else
            {
                $arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');
                //echo "обработать ошибку зона получателя или зона отправителя вернули 0<br>";
            }
            return $coeff;
        }
        /* москва - область, МО-МО */
        function GetMskObl(array &$arResult, array $arSelect, $flag = false){
            /* получить расстояние до мкад отправителя и получателя внутри области */
            $dist_mkad_sender_arr = GetInfoArr( false,$arResult['SENDER']['ID'], 6, $arSelect);
            $dist_mkad_recipient_arr =  GetInfoArr( false,$arResult['RECIPIENT']['ID'], 6, $arSelect);
            $dist_mkad_sender = (int)$dist_mkad_sender_arr['PROPERTIES']['OUT_MKAD']['VALUE'];
            $dist_mkad_recipient = (int) $dist_mkad_recipient_arr['PROPERTIES']['OUT_MKAD']['VALUE'];

            if(!empty($dist_mkad_sender_arr['PROPERTIES']['OUT_MKAD']['VALUE'])){
                $dist_mkad = $dist_mkad_sender_arr['PROPERTIES']['OUT_MKAD']['VALUE'];
        }
            if(!empty($dist_mkad_recipient_arr['PROPERTIES']['OUT_MKAD']['VALUE'])){
                $dist_mkad = $dist_mkad_recipient_arr['PROPERTIES']['OUT_MKAD']['VALUE'];
            }
            //AddToLogs("testCalc",  [$dist_mkad_sender]);
            //AddToLogs("testCalc",  [$dist_mkad_recipient]);

            //echo " Расстояние от МКАД Отправитель $dist_mkad_sender км.<br>";
            //echo " Расстояние от МКАД Получатель $dist_mkad_recipient км.<br>";
            $arResult['SENDER']["INTERVAL"] =  [" Расстояние от МКАД Отправитель $dist_mkad_sender км.<br>", $dist_mkad_sender];
            $arResult['RECIPIENT']["INTERVAL"] = [" Расстояние от МКАД Получатель $dist_mkad_recipient км.<br>", $dist_mkad_recipient];
            $tr = [];
            if($dist_mkad_sender>0 && $dist_mkad_recipient>0 && !$flag){
                /* область менее 50 км - область менее 50 км  id - 51213823 */
                if($dist_mkad_sender <= DEFAULT_DISTANCE && $dist_mkad_recipient <= DEFAULT_DISTANCE){
                    $arResult['ZONEDEV'] = GetMessage('MOS_OBL');
                    $tr = GetInfoArr( false,51213823, 103, $arSelect);
                    //echo "Тариф до 0,5кг  $tarif_05 руб. <br>";
                    //echo "Тариф от 0,5кг-1,0кг $tarif_1 руб. <br>";
                    //echo "Плюс на каждый кг свыше 1кг $tarif_hight руб. <br>";
                }
                /* область менее 50 км - область более 50 км  id - 51213826 */
                if(($dist_mkad_sender <= DEFAULT_DISTANCE && $dist_mkad_recipient > DEFAULT_DISTANCE)||
                    ($dist_mkad_sender > DEFAULT_DISTANCE && $dist_mkad_recipient <= DEFAULT_DISTANCE)){
                    $tr = GetInfoArr( false,51213826, 103, $arSelect);
                    //echo "Тариф до 0,5кг $tarif_05 руб. <br>";
                    // echo "Тариф от 0,5кг-1,0кг $tarif_1 руб. <br>";
                    //echo "Плюс на каждый кг свыше 1кг $tarif_hight руб. <br>";
                }
                /* область более 50 км - область более 50 км  id - 51213827 */
                if($dist_mkad_sender > DEFAULT_DISTANCE && $dist_mkad_recipient > DEFAULT_DISTANCE){
                    $tr = GetInfoArr( false,51213827, 103, $arSelect);
                    //echo "Тариф до 0,5кг $tarif_05 руб. <br>";
                    //echo "Тариф от 0,5кг-1,0кг $tarif_1 руб. <br>";
                    //echo "Плюс на каждый кг свыше 1кг $tarif_hight руб. <br>";
                }
            }
            elseif((($arResult['SENDER']['ID'] == 8054) && $dist_mkad_recipient>0)||
                (($arResult['RECIPIENT']['ID'] == 8054) && $dist_mkad_sender>0)){
                /* Москва - область более 50 км  id - 51213822 */
                if(($dist_mkad_recipient > DEFAULT_DISTANCE && $arResult['SENDER']['ID'] == 8054)||
                    ($dist_mkad_sender > DEFAULT_DISTANCE && $arResult['RECIPIENT']['ID'] == 8054)){
                    $tr = GetInfoArr( false,51213822, 103, $arSelect);
                }
                /* Москва - область менее или равно 50 км  id - 51213813 */
                if(($dist_mkad_recipient <= DEFAULT_DISTANCE && $arResult['SENDER']['ID'] == 8054)||
                    ($dist_mkad_sender <= DEFAULT_DISTANCE && $arResult['RECIPIENT']['ID'] == 8054)){
                    $tr = GetInfoArr( false,51213813, 103, $arSelect);
                }
            }
            elseif($flag){
                $tr = [];
                 /* Москва - область более 50 км  id - 51213822 */
                if(($dist_mkad > DEFAULT_DISTANCE)){
                    $tr = GetInfoArr( false,51213822, 103, $arSelect);
                }
                /* Москва - область менее или равно 50 км  id - 51213813 */
                if($dist_mkad <= DEFAULT_DISTANCE){
                    $tr = GetInfoArr( false,51213813, 103, $arSelect);
                }
               // AddToLogs("testCalc", $tr);
            }
            else{
                $arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');
                //echo "обработать ошибку - нет расстояния от мкад<br>";
            }
            $arResult['TIMEDEV']  = $tr['PROPERTIES']['TIME_DELIVERY']['VALUE'];
            $tarif_05 = $tr['PROPERTIES']['WEIGHT_05']['VALUE'];
            $tarif_1 = $tr['PROPERTIES']['WEIGHT_1']['VALUE'];
            $tarif_hight = $tr['PROPERTIES']['WEIGHT_HIGHER']['VALUE'];
            return [$tarif_05, $tarif_1, $tarif_hight];
        }
        function GetLenOblNew(array $arResult, array $arSelect){
            /* 110  52154989 */
            if($arResult['RECIPIENT']['IBLOCK_SECTION_ID'] == 656){
                $id_lo = $arResult['RECIPIENT']['ID'];
            }elseif($arResult['SENDER']['IBLOCK_SECTION_ID'] == 656){
                $id_lo = $arResult['SENDER']['ID'];
            }
            $tr = GetInfoArr( false, $id_lo, 6, $arSelect);

                $req = GetInfoArr( false,
                    52297979, 110, $arSelect);

                   $weight = (float)$arResult['FULLWEIGTH'];
                     if($tr['PROPERTIES']['OUT_MKAD']['VALUE']<DEFAULT_DISTANCE) {
                         $timedev = $req['PROPERTIES']['MO_TIME_1']['VALUE'];
                         $tar = (int)$req['PROPERTIES']['MO_TARIF_1']['VALUE'];
                     }elseif($tr['PROPERTIES']['OUT_MKAD']['VALUE']>=DEFAULT_DISTANCE){
                         $tar = (int)$req['PROPERTIES']['MO_TARIF_2']['VALUE'];
                         $timedev = $req['PROPERTIES']['MO_TIME_2']['VALUE'];
                     }
                   if($weight<=1.0){
                      $tarif = $tar;
                   }elseif($weight>1.0){
                       $tarif_hight = (int)$req['PROPERTIES']['MO_TARIF_SPREAD_1']['VALUE'];
                       $w_hi = $weight - 1.00;
                       $tarif = (float)($tar + (ceil($w_hi)*$tarif_hight));
                   }
                 // AddToLogs("testCalс", ["MESS"=>$tr['PROPERTIES']['OUT_MKAD']]);
                   return [$timedev, $tarif];

         }
        function GetMskOblNew(array $arResult, array $arSelect){
            /* 110  52154989 */
            if($arResult['RECIPIENT']['IBLOCK_SECTION_ID'] == 641){
                $id_mo = $arResult['RECIPIENT']['ID'];
            }elseif($arResult['SENDER']['IBLOCK_SECTION_ID'] == 641){
                $id_mo = $arResult['SENDER']['ID'];
            }
            $tr = GetInfoArr( false, $id_mo, 6, $arSelect);
            if($tr['PROPERTIES']['SMALL_DEV']['VALUE']=='Y'){
                $tarif = 0;
                return $tarif;
            }else{
                $req = GetInfoArr( false, 52154989, 110, $arSelect);

                $weight = (float)$arResult['FULLWEIGTH'];
                if($tr['PROPERTIES']['OUT_MKAD']['VALUE']<DEFAULT_DISTANCE) {
                    $timedev = $req['PROPERTIES']['MO_TIME_1']['VALUE'];
                    $tar = (int)$req['PROPERTIES']['MO_TARIF_1']['VALUE'];
                }elseif($tr['PROPERTIES']['OUT_MKAD']['VALUE']>=DEFAULT_DISTANCE){
                    $tar = (int)$req['PROPERTIES']['MO_TARIF_2']['VALUE'];
                    $timedev = $req['PROPERTIES']['MO_TIME_2']['VALUE'];
                }
                if($weight<=1.0){
                    $tarif = $tar;
                }elseif($weight>1.0){
                    $tarif_hight = (int)$req['PROPERTIES']['MO_TARIF_SPREAD_1']['VALUE'];
                    $w_hi = $weight - 1.00;
                    $tarif = (float)($tar + (ceil($w_hi)*$tarif_hight));
                }
                // AddToLogs("testCalс", ["MESS"=>$tr['PROPERTIES']['OUT_MKAD']]);
                return [$timedev, $tarif];
            }
        }
        function GetTarifSPB(&$arResult, $tarif_1, $tarif_hight){
            $tarif = 0.00;
            if($arResult['FULLWEIGTH']<=1.00){
              $tarif = $tarif_1;
            }elseif($arResult['FULLWEIGTH']>1.00){
                $w_hi = (float)$arResult['FULLWEIGTH'] - 1.00;
                $tarif = (float)($tarif_1 + (ceil($w_hi)*$tarif_hight));
            }

            return $tarif;
        }
        function GetTarifSPBSpbOBL(&$arResult, $arSelect, $f=true ){
            $id_obl = 0;
            $weight = (float)$arResult['FULLWEIGTH'];
            if($f){
                if($arResult['SENDER']["ID"] != 8678){
                    $id_obl = $arResult['SENDER']["ID"];
                }elseif($arResult['RECIPIENT']["ID"] != 8678){
                    $id_obl = $arResult['RECIPIENT']["ID"];
                }else{

                    $arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');
                }
            }else{
                if($arResult['SENDER']["IBLOCK_SECTION_ID"] == 656){
                    $id_obl = $arResult['SENDER']["ID"];
                }elseif($arResult['RECIPIENT']["IBLOCK_SECTION_ID"] == 656){
                    $id_obl = $arResult['RECIPIENT']["ID"];
                }else{

                    $arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');
                }
            }
            //AddToLogs("testCalс", ["MESS"=>" Питер - область ", "ID obl"=>$id_obl]);
            $arrDist = GetInfoArr(false, $id_obl, 6, $arSelect);
            $distance = 0;
            if($arrDist['PROPERTIES']['OUT_MKAD']['VALUE']){
                $distance =  $arrDist['PROPERTIES']['OUT_MKAD']['VALUE'];
            }else{
                $arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');
            }
            if($distance <= DEFAULT_DISTANCE){
                $tr = GetInfoArr( false,52650410, 112, $arSelect);
            }elseif($distance > DEFAULT_DISTANCE){
                $tr = GetInfoArr( false,52650411, 112, $arSelect);
            }
            $arResult['TIMEDEV']  = $tr['PROPERTIES']['TIME_DELIVERY']['VALUE'];
            if($weight<=1.00 && $weight>0.00){
                $tarif = $tr['PROPERTIES']['TARIF_1']['VALUE'];
            }elseif($weight>1.00){
                $uplimit = (float) $weight - 1.00;
                $uplimit = ceil($uplimit);
                $tarif = $tr['PROPERTIES']['TARIF_1']['VALUE']+$tr['PROPERTIES']['TARIF_HIGHT']['VALUE']*$uplimit;
            }
            $arResult['TARIF_ITOG'] = (float) $tarif;
            return $tarif;
        }
        function GetTarifMSKSpbOBL(&$arResult, $arSelect, $f=true ){
            $id_obl = 0;
            $weight = (float)$arResult['FULLWEIGTH'];
           if($f){
               if($arResult['SENDER']["ID"] != 8054){
                   $id_obl = $arResult['SENDER']["ID"];
               }elseif($arResult['RECIPIENT']["ID"] != 8054){
                   $id_obl = $arResult['RECIPIENT']["ID"];
               }else{

                   $arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');
               }
           }else{
               if($arResult['SENDER']["IBLOCK_SECTION_ID"] == 656){
                   $id_obl = $arResult['SENDER']["ID"];
               }elseif($arResult['RECIPIENT']["IBLOCK_SECTION_ID"] == 656){
                   $id_obl = $arResult['RECIPIENT']["ID"];
               }else{

                   $arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');
               }
           }
            //AddToLogs("testCalс", ["MESS"=>" Питер - область ", "ID obl"=>$id_obl]);
            $arrDist = GetInfoArr(false, $id_obl, 6, $arSelect);
            $distance = 0;
           if($arrDist['PROPERTIES']['OUT_MKAD']['VALUE']){
               $distance =  $arrDist['PROPERTIES']['OUT_MKAD']['VALUE'];
           }else{
               $arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');
           }
           if($distance <= DEFAULT_DISTANCE){
               $tr = GetInfoArr( false,51897804, 107, $arSelect);
           }elseif($distance > DEFAULT_DISTANCE){
               $tr = GetInfoArr( false,51897805, 107, $arSelect);
           }
            $arResult['TIMEDEV']  = $tr['PROPERTIES']['TIME_DELIVERY']['VALUE'];
            if($weight<=1.00 && $weight>0.00){
                $tarif = $tr['PROPERTIES']['TARIF_1']['VALUE'];
            }elseif($weight>1.00){
                $uplimit = (float) $weight - 1.00;
                $uplimit = ceil($uplimit);
                $tarif = $tr['PROPERTIES']['TARIF_1']['VALUE']+$tr['PROPERTIES']['TARIF_HIGHT']['VALUE']*$uplimit;
            }
            $arResult['TARIF_ITOG'] = (float) $tarif;
            return $tarif;

        }
        function GetTarifNoMskObl(&$arResult, $arSelect ){
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
            return $tarif;
        }
        function GetTarifMskOblSPB(&$arResult, $dist_mkad, $arSelect, $ext=false){
            $tr = [];
            $weight = (float)$arResult['FULLWEIGTH'];
            /* МО более 50 км  id - 52439704 */
           if(!$ext){
               if(($dist_mkad > DEFAULT_DISTANCE)){
                   $tr = GetInfoArr( false,52439704, 111, $arSelect);
               }
               /* МО менее 50 км  id - 52439702 */
               if($dist_mkad < DEFAULT_DISTANCE){
                   $tr = GetInfoArr( false,52439702, 111, $arSelect);
               }
               $base_t = (float)$tr['PROPERTIES']['BASE_T']['VALUE'];
               $base_n = (float)$tr['PROPERTIES']['BASE_N']['VALUE'];
               $base_up = (float)$tr['PROPERTIES']['BASE_UP']['VALUE'];
               $tar = $base_t + $base_n;
           } else {
               $tr = GetInfoArr( false,52439702, 111, $arSelect);
               $base_up = (float)$tr['PROPERTIES']['BASE_UP']['VALUE'];
               $tar = (float)$tr['PROPERTIES']['BASE_T']['VALUE'];
           }
            if($weight<=1.0){
                $tarif = $tar;
            }elseif($weight>1.0){
                $w_hi = $weight - 1.00;
                $tarif = (float)( $tar + (ceil($w_hi)* $base_up));
            }
            $arResult['TARIF_ITOG'] =  $tarif;
            return $tarif;
        }
        function GetTarifMskOblDev(&$arResult, $dist_mkad, $arSelect){
            $tr = [];
            /* Москва - область более 50 км  id - 51213822 */
            if(($dist_mkad > DEFAULT_DISTANCE)){
                $tr = GetInfoArr( false,51213822, 103, $arSelect);
            }
            /* Москва - область менее 50 км  id - 51213813 */
            if($dist_mkad < DEFAULT_DISTANCE){
                $tr = GetInfoArr( false,51213813, 103, $arSelect);
            }
            $time_dev_msk  = $tr['PROPERTIES']['TIME_DELIVERY']['VALUE'];
            $tarif_05_msk = $tr['PROPERTIES']['WEIGHT_05']['VALUE'];
            $tarif_1_msk = $tr['PROPERTIES']['WEIGHT_1']['VALUE'];
            $tarif_hight_msk = $tr['PROPERTIES']['WEIGHT_HIGHER']['VALUE'];
            GetTarifMskObl($arResult, $tarif_05_msk, $tarif_1_msk, $tarif_hight_msk);
            return $time_dev_msk;
        }
        function GetMainCity($arResult,  $arSelect, $flag=false){
           if(!$flag){
               $req = GetInfoArr( false,$arResult['SENDER']['ID'], 6, $arSelect);
               if($req['PROPERTIES']['SPRAV']['VALUE'] == 'Y'){
                   return true;
               }else{
                   return false;
               }
           } else{
               $req_send = GetInfoArr( false,$arResult['SENDER']['ID'], 6, $arSelect);
               $req_rec = GetInfoArr( false,$arResult['RECIPIENT']['ID'], 6, $arSelect);
               if($req_send['PROPERTIES']['SPRAV']['VALUE'] == 'Y' &&
                   $req_rec['PROPERTIES']['SPRAV']['VALUE'] != 'Y'){
                   return "Y";
               }
               elseif($req_rec['PROPERTIES']['SPRAV']['VALUE'] == 'Y' &&
                   $req_send['PROPERTIES']['SPRAV']['VALUE'] != 'Y')
               {
                   return "Y";
               }
               elseif($req_send['PROPERTIES']['SPRAV']['VALUE'] == 'Y' &&
                   $req_rec['PROPERTIES']['SPRAV']['VALUE'] == 'Y')
               {

                   return "C";
               }
               else{
                   return false;
               }
           }

        }
        function GetTarifMskObl(&$arResult, $tarif_05, $tarif_1, $tarif_hight){
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
            $arResult['TARIF_ITOG'] =  $tarif;
            return $tarif;
        }
        function GetTarifMskPt(&$arResult, $arSelect){
                $tr = GetInfoArr( false, 51897803, 107, $arSelect);
                $arResult['TIMEDEV']  = $tr['PROPERTIES']['TIME_DELIVERY']['VALUE'];
                $tarif_1 = (float)$tr['PROPERTIES']['TARIF_1']['VALUE'];
                $tarif_hight = (float)$tr['PROPERTIES']['TARIF_HIGHT']['VALUE'];
                $itogTarif = GetTarifSPB($arResult, $tarif_1, $tarif_hight );
                $arResult['TARIF_ITOG'] = $itogTarif;
                $tr['TARIF_ITOG'] = $itogTarif;
                //AddToLogs("testCalс", $arResult);
                return $tr;
        }
        function GetTarifMsk(&$arResult, $arSelect){

            $weight = (float)$arResult['FULLWEIGTH'];
            $arResult['STANDART'] = GetInfoArr(false, 51964190, 108, $arSelect);
            $arResult['EXPRESS_8'] = GetInfoArr(false, 51964682, 109, $arSelect);
            $arResult['EXPRESS_4'] = GetInfoArr(false, 51964677, 109, $arSelect);
            $arResult['EXPRESS_2'] = GetInfoArr(false, 51964676, 109, $arSelect);


        }
        function sumTimeDev($time_1, $time_2){
            if(preg_match('/^([0-9]{1,2}-[0-9]{1,2})$/',$time_1) &&
                preg_match('/^([0-9]{1,2}-[0-9]{1,2})$/',$time_2)){
                $arr1 = explode('-',$time_1);
                $vrem1 = $arr1[0];
                $vrem2 = $arr1[1];
                $arr2 = explode('-',$time_2);
                $vrem3 = $arr2[0];
                $vrem4 = $arr2[1];
                $timeit = ( $vrem1 +  $vrem3).'-'.($vrem2 + $vrem4);

            }
            if(!preg_match('/^([0-9]{1,2}-[0-9]{1,2})$/',$time_1) &&
                !preg_match('/^([0-9]{1,2}-[0-9]{1,2})$/',$time_2)){

                $timeit = (int)$time_1+(int)$time_2;

            }
            if(!preg_match('/^([0-9]{1,2}-[0-9]{1,2})$/',$time_1) &&  /* 2 */
                preg_match('/^([0-9]{1,2}-[0-9]{1,2})$/',$time_2)){   /* 3-4 */
                $vrem1 = $time_1;
                $arr1 = explode('-',$time_2);
                $vrem2 = $arr1[0];
                $vrem3 = $arr1[1];
                $timeit = ( $vrem1 +  $vrem2).'-'.($vrem3 + $vrem1);

            }
            if(preg_match('/^([0-9]{1,2}-[0-9]{1,2})$/',$time_1) &&
                !preg_match('/^([0-9]{1,2}-[0-9]{1,2})$/',$time_2)){
                $arr1 = explode('-',$time_1);
                $vrem1 = $arr1[0];
                $vrem2 = $arr1[1];
                $vrem4 = $time_2;
                $timeit = ( $vrem1 +  $vrem4).'-'.($vrem2 + $vrem4);
            }
            return $timeit;
        }

        /* внутри основного города и Питер - Питер */
        if(($arResult['SENDER']['ID'] == $arResult['RECIPIENT']['ID']) &&
            !($arResult['SENDER']['ID']==8054 && $arResult['RECIPIENT']['ID'] == 8054)
        )
        {
            if($arResult['SENDER']['IBLOCK_SECTION_ID'] == $arResult['RECIPIENT']['IBLOCK_SECTION_ID']){
                $flag = true;
                /* определить главный- не главный  */
                $q =  GetMainCity($arResult, $arSelect);
                if($q){
                    $arResult['TARIF_ITOG'] = GetTarifSPB($arResult, TMain, THMain);
                    $arResult['TIMEDEV'] = DEFAULT_DEV_CITY;
                }else{
                    $arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');
                }
            }else{
                $arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');
            }
            AddToLogs("testCalс", ["MESS"=>"  внутри основного города ", $arResult]);
        }
        /* не Москва и не Питер */
         elseif(($arResult['SENDER']['ID'] != 8054 && $arResult['RECIPIENT']['ID'] != 8054) &&
            ($arResult['SENDER']['ID'] != 8678 && $arResult['RECIPIENT']['ID'] != 8678) &&
            ($arResult['SENDER']['IBLOCK_SECTION_ID'] != 641 && $arResult['RECIPIENT']['IBLOCK_SECTION_ID'] != 641) &&
            ($arResult['SENDER']['IBLOCK_SECTION_ID'] != 656 && $arResult['RECIPIENT']['IBLOCK_SECTION_ID'] != 656) &&
            ($arResult['SENDER']['NAME'] != $arResult['RECIPIENT']['NAME']) && ($arResult['SENDER']['IBLOCK_SECTION_ID'] != $arResult['RECIPIENT']['IBLOCK_SECTION_ID'])
        )
        {
                $coeff = GetRegion($arResult, $arSelect);
                AddToLogs("testCalс", ["MESS"=>"  не Москва и не Питер ", $arResult]);
        }
        /* Москва - Москва */
        elseif ( $arResult['SENDER']['ID'] == 8054 && $arResult['RECIPIENT']['ID'] == 8054){
            /* тариф Стандарт
            108
                Экспресс
            109
             */
            $flag = true;
            $weight = (float)$arResult['FULLWEIGTH'];
            GetTarifMsk($arResult, $arSelect);
            /* стандарт */
            $uplimit = (float) $arResult['FULLWEIGTH'] - 1.00;
            $uplimit = ceil($uplimit);
            if($weight>0.00 && $weight<=1.00){
                $tarif = (float)$arResult['STANDART']['PROPERTIES']['ST_1']['VALUE'];
            }elseif($weight>1.00 && $weight<=19.00){
                $tarif =  (float)$arResult['STANDART']['PROPERTIES']['ST_1']['VALUE'] +
                    (float)$arResult['STANDART']['PROPERTIES']['ST_2']['VALUE']*$uplimit;
            }elseif($weight>19.00 && $weight<=49.00){

                $tarif =  (float)$arResult['STANDART']['PROPERTIES']['ST_1']['VALUE'] +
                    (float)$arResult['STANDART']['PROPERTIES']['ST_3']['VALUE']*$uplimit;
            }elseif($weight>49.00 && $weight<=69.00){

                $tarif =  (float)$arResult['STANDART']['PROPERTIES']['ST_1']['VALUE'] +
                    (float)$arResult['STANDART']['PROPERTIES']['ST_4']['VALUE']*$uplimit;
            }elseif($weight>69.00 && $weight<=99.00){

                $tarif =  (float)$arResult['STANDART']['PROPERTIES']['ST_1']['VALUE'] +
                    (float)$arResult['STANDART']['PROPERTIES']['ST_5']['VALUE']*$uplimit;
            }elseif($weight>99.00){

                $tarif =  (float)$arResult['STANDART']['PROPERTIES']['ST_1']['VALUE'] +
                    (float)$arResult['STANDART']['PROPERTIES']['ST_6']['VALUE']*$uplimit;
            }
            $arResult['TARIF_ITOG_MSK']['STANDART'] = round($tarif);
            $arResult['TIMEDEV']['STANDART'] =  $arResult['STANDART']['PROPERTIES']['TIMEDEV']['VALUE'];

            /* express-2 */
            if($weight>0.00 && $weight<=1.00){
                $tarif_ex2 = (float)$arResult['EXPRESS_2']['PROPERTIES']['EXPR_3']['VALUE'];
            }elseif($weight > 1.00){
                $tarif_ex2 = (float)$arResult['EXPRESS_2']['PROPERTIES']['EXPR_3']['VALUE'] +
                    (float)$arResult['EXPRESS_2']['PROPERTIES']['EXPR_4']['VALUE']*$uplimit;
            }
            $arResult['TARIF_ITOG_MSK']['EXPRESS_2'] = round($tarif_ex2);
            $arResult['TIMEDEV']['EXPRESS_2'] =  $arResult['EXPRESS_2']['PROPERTIES']['EXPR_1']['VALUE'];
            $arResult['CALLCOURIER']['EXPRESS_2'] =  $arResult['EXPRESS_2']['PROPERTIES']['EXPR_2']['VALUE'];
            /* express-4 */
            if($weight>0.00 && $weight<=1.00){
                $tarif_ex4 = (float)$arResult['EXPRESS_4']['PROPERTIES']['EXPR_3']['VALUE'];
            }elseif($weight > 1.00){
                $tarif_ex4 = (float)$arResult['EXPRESS_4']['PROPERTIES']['EXPR_3']['VALUE'] +
                    (float)$arResult['EXPRESS_4']['PROPERTIES']['EXPR_4']['VALUE']*$uplimit;
            }

            $arResult['TARIF_ITOG_MSK']['EXPRESS_4'] = round($tarif_ex4);
            $arResult['TIMEDEV']['EXPRESS_4'] =  $arResult['EXPRESS_4']['PROPERTIES']['EXPR_1']['VALUE'];
            $arResult['CALLCOURIER']['EXPRESS_4'] =  $arResult['EXPRESS_4']['PROPERTIES']['EXPR_2']['VALUE'];
            /* express-8 */
            if($weight>0.00 && $weight<=1.00){
                $tarif_ex8 = (float)$arResult['EXPRESS_8']['PROPERTIES']['EXPR_3']['VALUE'];
            }elseif($weight > 1.00){
                $tarif_ex8 = (float)$arResult['EXPRESS_8']['PROPERTIES']['EXPR_3']['VALUE'] +
                    (float)$arResult['EXPRESS_8']['PROPERTIES']['EXPR_4']['VALUE']*$uplimit;
            }

            $arResult['TARIF_ITOG_MSK']['EXPRESS_8'] = round($tarif_ex8);
            $arResult['TIMEDEV']['EXPRESS_8'] =  $arResult['EXPRESS_8']['PROPERTIES']['EXPR_1']['VALUE'];
            $arResult['CALLCOURIER']['EXPRESS_8'] =  $arResult['EXPRESS_8']['PROPERTIES']['EXPR_2']['VALUE'];
            AddToLogs("testCalс", ["MESS"=>" Москва - Москва ",  $arResult['TARIF_ITOG_MSK']]);
        }

        /* Московская область - CПБ */
       elseif(($arResult['SENDER']['ID'] == 8678 &&
               $arResult['RECIPIENT']['IBLOCK_SECTION_ID'] == 641) ||
           ($arResult['SENDER']['IBLOCK_SECTION_ID'] == 641 &&
               $arResult['RECIPIENT']['ID'] == 8678))
       {
           //$arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');  /* временно отключить */
           $flag = true;
           $arResult['TARIF_ITOG'] = 0;
           if($arResult['RECIPIENT']['IBLOCK_SECTION_ID'] == 641){
               $dist_arr = GetInfoArr( false,$arResult['RECIPIENT']['ID'], 6, $arSelect);
           }elseif($arResult['SENDER']['IBLOCK_SECTION_ID'] == 641){
               $dist_arr = GetInfoArr( false,$arResult['SENDER']['ID'], 6, $arSelect);
           }
           $dist_mkad = (int) $dist_arr['PROPERTIES']['OUT_MKAD']['VALUE'];
           $ext = $dist_arr['PROPERTIES']['SMALL_DEV']['VALUE'];
           if($ext == "Y"){
               $ext = true;
           }
           else
           {
               $ext = false;
           }
           $tarif = GetTarifMskOblSPB($arResult, $dist_mkad, $arSelect, $ext);
           $arResult['TIMEDEV'] = '3-5';                                              /* переделать как дадут сроки */
           AddToLogs("testCalс", ["MESS"=>"  Московская область - СПБ ", $dist_arr]);
       }

        /* Московская область - Ленинградская область */
        elseif(($arResult['SENDER']['IBLOCK_SECTION_ID'] == 641 &&
            $arResult['RECIPIENT']['IBLOCK_SECTION_ID'] == 656) ||
            ($arResult['SENDER']['IBLOCK_SECTION_ID'] == 656 &&
                $arResult['RECIPIENT']['IBLOCK_SECTION_ID'] == 641)
        ){
            //$arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');    /* временно отключить */
            $flag = true;
            $dist_arr_sender = GetInfoArr( false,$arResult['SENDER']['ID'], 6, $arSelect);
            $dist_arr_recipient =  GetInfoArr( false,$arResult['RECIPIENT']['ID'], 6, $arSelect);
            //AddToLogs("testCalс", ["MESS"=>"  Московская область - Ленинградская область ",  $dist_arr_sender]);
            if($arResult['SENDER']['IBLOCK_SECTION_ID'] == 641 &&
                $arResult['RECIPIENT']['IBLOCK_SECTION_ID'] == 656)
            {
                 $dist_mkad = (int) $dist_arr_sender['PROPERTIES']['OUT_MKAD']['VALUE'];
                 $dist_kad = (int)  $dist_arr_recipient['PROPERTIES']['OUT_MKAD']['VALUE'];
                 $ext = $dist_arr_sender['PROPERTIES']['SMALL_DEV']['VALUE'];
             }elseif($arResult['SENDER']['IBLOCK_SECTION_ID'] == 656 &&
                 $arResult['RECIPIENT']['IBLOCK_SECTION_ID'] == 641 )
             {
                 $dist_mkad = (int) $dist_arr_recipient['PROPERTIES']['OUT_MKAD']['VALUE'];
                 $dist_kad = (int) $dist_arr_sender['PROPERTIES']['OUT_MKAD']['VALUE'];
                 $ext = $dist_arr_recipient['PROPERTIES']['SMALL_DEV']['VALUE'];
             }

            $arResult['TARIF_ITOG'] = 0;
            $weight = (float)$arResult['FULLWEIGTH'];
            $tr = [];

                    if(($dist_mkad <= DEFAULT_DISTANCE && $dist_kad <= DEFAULT_DISTANCE)){
                        $tr = GetInfoArr( false,52440321, 111, $arSelect);
                    }
                    if(($dist_mkad <= DEFAULT_DISTANCE && $dist_kad > DEFAULT_DISTANCE)){
                        $tr = GetInfoArr( false,52440573, 111, $arSelect);
                    }
                    if(($dist_mkad > DEFAULT_DISTANCE && $dist_kad <= DEFAULT_DISTANCE)){
                        $tr = GetInfoArr( false,52440575, 111, $arSelect);
                    }
                    if(($dist_mkad > DEFAULT_DISTANCE && $dist_kad > DEFAULT_DISTANCE)){
                        $tr = GetInfoArr( false,52440576, 111, $arSelect);
                    }
            $base_t = (float)$tr['PROPERTIES']['BASE_T']['VALUE'];
            $base_n = (float)$tr['PROPERTIES']['BASE_N']['VALUE'];
            $base_up = (float)$tr['PROPERTIES']['BASE_UP']['VALUE'];
            $tar = $base_t + $base_n;

            if($weight<=1.0){
                $tarif = $tar;
            }elseif($weight>1.0){
                $w_hi = $weight - 1.00;
                $tarif = (float)( $tar + (ceil($w_hi)* $base_up));
            }
            $arResult['TARIF_ITOG'] =  $tarif;
            $arResult['TIMEDEV'] = '4-6';                                     /* переделать как дадут сроки */
            if($ext == "Y"){
                GetTarifMSKSpbOBL($arResult, $arSelect, false);
            }
            AddToLogs("testCalс", ["MESS"=>"  Московская область - Ленинградская область ", $arResult]);
        }

        /*  Москва - в другой регион кроме Московской области, СПБ и ЛО */
        elseif(
        (($arResult['SENDER']['ID'] == 8054 && $arResult['RECIPIENT']['ID'] != 8678 &&
                $arResult['RECIPIENT']['IBLOCK_SECTION_ID'] != 641) ||
                ($arResult['RECIPIENT']['ID'] == 8054 && $arResult['SENDER']['ID'] != 8678 &&
                $arResult['SENDER']['IBLOCK_SECTION_ID'] != 641)) &&
                !($arResult['SENDER']['ID'] == 8054 && $arResult['RECIPIENT']['ID'] == 8054 ) &&
                !($arResult['RECIPIENT']['IBLOCK_SECTION_ID'] == 656 ||
                 $arResult['SENDER']['IBLOCK_SECTION_ID'] == 656)
        )
        {
            $coeff = GetMsk($arResult);
            AddToLogs("testCalс", ["MESS"=>"  Москва - в другой регион кроме Московской области, СПБ и ЛО ", $arResult]);

        }
        /* любой - Питер */
        elseif(($arResult['SENDER']['ID'] == 8678 || $arResult['RECIPIENT']['ID'] == 8678) &&
            ($arResult['RECIPIENT']['ID'] != 8054 && $arResult['SENDER']['ID'] != 8054) &&
            ($arResult['RECIPIENT']['IBLOCK_SECTION_ID'] != 656 && $arResult['SENDER']['IBLOCK_SECTION_ID'] != 656) &&
            ($arResult['RECIPIENT']['IBLOCK_SECTION_ID'] != 641 && $arResult['SENDER']['IBLOCK_SECTION_ID'] != 641)
        ){
            $coeff = GetMsk($arResult);
            if(preg_match('/^[0-9]{1}\s?-\s?[0-9]{1,2}$/', $arResult['TIMEDEV'])){
                 $art = explode('-', $arResult['TIMEDEV']);

                 if(!empty(is_array($art))){
                     $res1 = (int)$art[0]+1;
                     $res2 = (int)$art[1]+1;
                     $arResult['TIMEDEV'] = "$res1-$res2";
                 }
            }
            if(preg_match('/^[0-9]{1,2}$/', $arResult['TIMEDEV'])){
                $arResult['TIMEDEV']+=1;
            }

            AddToLogs("testCalс", ["MESS"=>" любой - Питер ", $arResult]);
        }

        /* москва - область, МО-МО */
        elseif(($arResult['SENDER']['IBLOCK_SECTION_ID'] == 641 ||
                $arResult['SENDER']['ID'] == 8054) &&
            ($arResult['RECIPIENT']['IBLOCK_SECTION_ID'] == 641 ||
                $arResult['RECIPIENT']['ID'] == 8054))
        {
            AddToLogs("testCalс", ["MESS"=>"  москва -область, МО-МО ", $arResult]);
            $arResult['ZONEDEV'] = GetMessage('MOS_OBL');
            $coeff = "Без коэфициента, МО";

            $tarifs = GetMskObl($arResult,$arSelect);
            $tarif_05 = $tarifs[0];
            $tarif_1 = $tarifs[1];
            $tarif_hight = $tarifs[2];
        }

        /* любой кроме Москвы и Московской области - Ленинградская область  */
        elseif(($arResult['SENDER']['IBLOCK_SECTION_ID'] == 656 ||
                $arResult['RECIPIENT']['IBLOCK_SECTION_ID'] == 656) &&
            !($arResult['SENDER']['ID'] == 8678 || $arResult['RECIPIENT']['ID'] == 8678) &&
            !($arResult['RECIPIENT']['ID'] == 8054 || $arResult['SENDER']['ID'] == 8054) &&
            !($arResult['SENDER']['IBLOCK_SECTION_ID'] == 641 ||
                $arResult['RECIPIENT']['IBLOCK_SECTION_ID'] == 641)
        ){
            //$arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');  /* временно отключен */
            $flag = true;
            /* вычислить не ЛО и посчитать его до Питера  */
            if(($arResult['SENDER']['IBLOCK_SECTION_ID'] != 656 &&
                    $arResult['RECIPIENT']['IBLOCK_SECTION_ID'] == 656) ||
                ($arResult['RECIPIENT']['IBLOCK_SECTION_ID'] != 656 &&
                    $arResult['SENDER']['IBLOCK_SECTION_ID'] == 656)
            ){
                $coeff = GetMsk($arResult);
                $arResult['RASCH_COEFF'] = $coeff;
                GetTarifNoMskObl($arResult, $arSelect);
                /* промежуточный тариф */
                $time_PSB = $arResult['TIMEDEV'];
                $tarif_itog_psb = (float)$arResult['TARIF_ITOG'];
                //AddToLogs("testCalc",  $arResult);
                $tarif = GetLenOblNew($arResult, $arSelect);
                AddToLogs("testCalс", $tarif);
                if(is_array($tarif)){
                    $arResult['TARIF_ITOG'] = $tarif[1]+$tarif_itog_psb;
                    $arResult['TIMEDEV'] = sumTimeDev($tarif[0], $time_PSB );
                }
            }
            AddToLogs("testCalс", ["MESS"=>"любой кроме Москвы и Московской области - Ленинградская область область ", $arResult]);
        }

        /*Любой кроме москвы и питера - Мос область */
        elseif(( $arResult['SENDER']['IBLOCK_SECTION_ID'] == 641 ||
                 $arResult['RECIPIENT']['IBLOCK_SECTION_ID'] == 641) &&
                 $arResult['SENDER']['ID'] != 8054 && $arResult['RECIPIENT']['ID'] != 8054 &&
                 $arResult['SENDER']['ID'] != 8678 && $arResult['RECIPIENT']['ID'] != 8678 &&
                 $arResult['RECIPIENT']['IBLOCK_SECTION_ID'] != 656 &&
                 $arResult['SENDER']['IBLOCK_SECTION_ID'] != 656
        )
        {
            //$arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');  /* временно отключен */
            $flag = true;
            /* вычислить не МО и посчитать его до Москвы  */
            if(($arResult['SENDER']['IBLOCK_SECTION_ID'] != 641 &&
                $arResult['RECIPIENT']['IBLOCK_SECTION_ID'] == 641) ||
                ($arResult['RECIPIENT']['IBLOCK_SECTION_ID'] != 641 &&
                    $arResult['SENDER']['IBLOCK_SECTION_ID'] == 641)
            ){
                $coeff = GetMsk($arResult);
                $arResult['RASCH_COEFF'] = $coeff;
                GetTarifNoMskObl($arResult, $arSelect);
                /* промежуточный тариф */
                $time_Msk = $arResult['TIMEDEV'];
                $tarif_itog_msk = (float)$arResult['TARIF_ITOG'];
                //AddToLogs("testCalc",  $arResult);
                $tarif = GetMskOblNew($arResult, $arSelect);
                AddToLogs("testCalс", $tarif);
                if(is_array($tarif)){
                    $arResult['TARIF_ITOG'] = $tarif[1]+$tarif_itog_msk;
                    $arResult['TIMEDEV'] = sumTimeDev($tarif[0], $time_Msk );
                }
            }
            //AddToLogs("testCalс", ["MESS"=>" Любой кроме москвы и питера - Мос область ", $arResult]);
        }

         /* Москва - Питер */
        elseif(($arResult['RECIPIENT']['ID'] == 8054 && $arResult['SENDER']['ID'] == 8678) ||
            ($arResult['SENDER']['ID'] == 8054 && $arResult['RECIPIENT']['ID'] == 8678)){

            /* 107 51897803 - москва-питер*/
            $flag = true;
            GetTarifMskPt($arResult, $arSelect);
            $coeff = "N";
            AddToLogs("testCalс", ["MESS"=>" Москва-Питер ", $arResult]);
        }
        /* Питер - ЛО область */
        elseif(($arResult['SENDER']['ID'] == 8678 || $arResult['RECIPIENT']['ID'] == 8678) &&
            ($arResult['RECIPIENT']['IBLOCK_SECTION_ID'] == 656 ||
                $arResult['SENDER']['IBLOCK_SECTION_ID'] == 656)
        ){
            $flag = true;
            GetTarifSPBSpbOBL($arResult, $arSelect);
            AddToLogs("testCalс", ["MESS"=>" Питер  - ЛО область ", $arResult]);
        }
        /* Москва - ЛО область */
        elseif(($arResult['SENDER']['ID'] == 8054 || $arResult['RECIPIENT']['ID'] == 8054) &&
            ($arResult['RECIPIENT']['IBLOCK_SECTION_ID'] == 656 ||
                $arResult['SENDER']['IBLOCK_SECTION_ID'] == 656)
        ){
            $flag = true;
            /* получить тариф и время доставки */
            GetTarifMSKSpbOBL($arResult, $arSelect);
            AddToLogs("testCalс", ["MESS"=>" Москва - ЛО область ", $arResult]);
        }

        /* внутри областей кроме подмосковья и лен области */
        elseif(($arResult['SENDER']['IBLOCK_SECTION_ID'] == $arResult['RECIPIENT']['IBLOCK_SECTION_ID']) &&
            ($arResult['SENDER']['IBLOCK_SECTION_ID'] != 641 ) &&
            ($arResult['SENDER']['IBLOCK_SECTION_ID'] != 656)
        )
        {
            $req = GetMainCity($arResult, $arSelect, true);
            if($req==="Y"){
                $coeff = C_1;
                $arResult['ZONEDEV'] = DEFAULT_ZONE_REGION;
                $arResult['TIMEDEV'] = DEFAULT_DEV_REGION;
                AddToLogs("testCalс", ["MESS"=>" внутри областей кроме подмосковья и лен области ", $arResult]);
            }
            elseif($req==="C"){
                $coeff = GetRegion($arResult, $arSelect);
                AddToLogs("testCalс", ["MESS"=>" внутри областей, основные города ", $arResult]);
            }
            else{
                $arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');
            }

        }
        else
        {

            $arResult['ERROR']['ERR_RASCHET'] = GetMessage('ERR_RASCHET');
            AddToLogs("testCalс", ["MESS"=>" Общая ошибка ", $arResult]);
            //echo 'обработать ошибку - по введенным данным нас. пунктов расчет не возможен';
        }

        $arResult['RASCH_COEFF'] = $coeff;
        //AddToLogs("testCalc", $arResult);
        // exit;


        /******************************************************/
        /* не Московская область  и не комплексный расчет */
        if(!$flag){
            if ((float)$coeff){
                $tarif_no_msk = GetTarifNoMskObl($arResult, $arSelect );
            }else{
                GetTarifMskObl($arResult, $tarif_05, $tarif_1, $tarif_hight);
            }
        }

        $tarif_rasch_itog = (float)$arResult['TARIF_ITOG'];
        $arResult['TARIF_ITOG'] =  round($tarif_rasch_itog);
    }else{
        $arResult['ERROR']['ERR_INPUT_CITY_INN'] = GetMessage('ERR_INPUT_CITY_INN');
    }

   //AddToLogs("testCalc", $arResult);  апи
    if($_GET['request'] === 'Y' && $_GET['api'] === 'Y'){
        $arr_req = [
            'TARIF' => $arResult['TARIF_ITOG'],
            'FULLWEIGTH' => $arResult['FULLWEIGTH'],
            'CITY_FROM' => $arResult['SENDER']['FULLNAME'],
            'CITY_TO' => $arResult['RECIPIENT']['FULLNAME'],
        ];
        $arr_req_json = convArrayToUTF($arr_req);
        $data_req = [
            "data"=>$arr_req_json
        ];
        $dataJsonReq = json_encode($data_req);
        echo $dataJsonReq;
        exit;
    }
    $arResult = convArrayToUTF($arResult);
    $data = [
       "data"=>$arResult
    ];
    $dataJson = json_encode($data);
    echo $dataJson;
    exit;
}

$this->IncludeComponentTemplate();