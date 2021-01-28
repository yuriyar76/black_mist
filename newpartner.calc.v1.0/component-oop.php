<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
    die();
}
require_once ('Calc.php');
$arResult = [];
$arRrTarif = [];
$flag = false;
if($_GET['mode'] === "index"){
    if($_GET['request'] === 'Y'){
        $areq =  json_decode($_POST['data'], true);

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
        $id_city_send = AllFunc::GetCityId(iconv('utf-8', 'windows-1251',$areq['city_sender']));
        $id_city_rec = AllFunc::GetCityId(iconv('utf-8', 'windows-1251',$areq['city_recipient']));
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
    $obj_app = new Calc($arrPost, $_GET);
    if($obj_app->current){
      $arResult = $obj_app->GetResult();
        /* внутри основного города и Питер - Питер */
        if($obj_app->option_1)
        {
            if($arResult['SENDER']['IBLOCK_SECTION_ID'] == $arResult['RECIPIENT']['IBLOCK_SECTION_ID']){
                $flag = true;
                $tarif = 0.00;
                $coeff = 0.00;
                /* определить главный- не главный  */
                $q =  $obj_app->GetMainCity($arResult);
                if($q){
                    $arResult['TARIF_ITOG'] = $obj_app->GetTarifSPB($arResult, Calc::TMain, Calc::THMain);
                    $arResult['TIMEDEV'] = Calc::DEFAULT_DEV_CITY;
                }else{
                    $arResult['ERROR']['ERR_RASCHET'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_RASCHET');
                }
            }else{
                $arResult['ERROR']['ERR_RASCHET'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_RASCHET');
        }
           // AllFunc::AddToLogs("testCalс", ["MESS"=>"  внутри основного города ", $arResult]);
        }
         /* не Москва и не Питер */
         elseif($obj_app->option_2)
        {
            $coeff = $obj_app->GetRegion($arResult);
           // AllFunc::AddToLogs("testCalс", ["MESS"=>"  не Москва и не Питер ", $arResult]);
        }
        /* Москва - Москва */
        elseif ($obj_app->option_3){
            /* тариф Стандарт
            108
                Экспресс
            109
             */
            $flag = true;
            $weight = (float)$arResult['FULLWEIGTH'];
            $obj_app->GetTarifMsk($arResult);
            /* стандарт */
            $uplimit = (float) $arResult['FULLWEIGTH'] - 1.00;
            $uplimit = ceil($uplimit);
            if($weight > Calc::STANDART[0] && $weight <= Calc::STANDART[1]){
                $tarif = (float)$arResult['STANDART']['PROPERTIES']['ST_1']['VALUE'];
            }elseif($weight > Calc::STANDART[1] && $weight <= Calc::STANDART[2]){
                $tarif =  (float)$arResult['STANDART']['PROPERTIES']['ST_1']['VALUE'] +
                    (float)$arResult['STANDART']['PROPERTIES']['ST_2']['VALUE']*$uplimit;
            }elseif($weight > Calc::STANDART[2] && $weight <= Calc::STANDART[3]){
                $tarif =  (float)$arResult['STANDART']['PROPERTIES']['ST_1']['VALUE'] +
                    (float)$arResult['STANDART']['PROPERTIES']['ST_3']['VALUE']*$uplimit;
            }elseif($weight > Calc::STANDART[3] && $weight <= Calc::STANDART[4]){
                $tarif =  (float)$arResult['STANDART']['PROPERTIES']['ST_1']['VALUE'] +
                    (float)$arResult['STANDART']['PROPERTIES']['ST_4']['VALUE']*$uplimit;
            }elseif($weight > Calc::STANDART[4] && $weight <= Calc::STANDART[5]){
                $tarif =  (float)$arResult['STANDART']['PROPERTIES']['ST_1']['VALUE'] +
                    (float)$arResult['STANDART']['PROPERTIES']['ST_5']['VALUE']*$uplimit;
            }elseif($weight > Calc::STANDART[5]){
                $tarif =  (float)$arResult['STANDART']['PROPERTIES']['ST_1']['VALUE'] +
                    (float)$arResult['STANDART']['PROPERTIES']['ST_6']['VALUE']*$uplimit;
            }
            $arResult['TARIF_ITOG_MSK']['STANDART'] = round($tarif);
            $arResult['TIMEDEV']['STANDART'] =  $arResult['STANDART']['PROPERTIES']['TIMEDEV']['VALUE'];
            /* express-2 */
            if($weight > Calc::EXPRESS[0] && $weight <= Calc::EXPRESS[1]){
                $tarif_ex2 = (float)$arResult['EXPRESS_2']['PROPERTIES']['EXPR_3']['VALUE'];
            }elseif($weight > Calc::EXPRESS[1]){
                $tarif_ex2 = (float)$arResult['EXPRESS_2']['PROPERTIES']['EXPR_3']['VALUE'] +
                    (float)$arResult['EXPRESS_2']['PROPERTIES']['EXPR_4']['VALUE']*$uplimit;
            }
            $arResult['TARIF_ITOG_MSK']['EXPRESS_2'] = round($tarif_ex2);
            $arResult['TIMEDEV']['EXPRESS_2'] =  $arResult['EXPRESS_2']['PROPERTIES']['EXPR_1']['VALUE'];
            $arResult['CALLCOURIER']['EXPRESS_2'] =  $arResult['EXPRESS_2']['PROPERTIES']['EXPR_2']['VALUE'];
            /* express-4 */
            if($weight > Calc::EXPRESS[0] && $weight <= Calc::EXPRESS[1]){
                $tarif_ex4 = (float)$arResult['EXPRESS_4']['PROPERTIES']['EXPR_3']['VALUE'];
            }elseif($weight > Calc::EXPRESS[1]){
                $tarif_ex4 = (float)$arResult['EXPRESS_4']['PROPERTIES']['EXPR_3']['VALUE'] +
                    (float)$arResult['EXPRESS_4']['PROPERTIES']['EXPR_4']['VALUE']*$uplimit;
            }

            $arResult['TARIF_ITOG_MSK']['EXPRESS_4'] = round($tarif_ex4);
            $arResult['TIMEDEV']['EXPRESS_4'] =  $arResult['EXPRESS_4']['PROPERTIES']['EXPR_1']['VALUE'];
            $arResult['CALLCOURIER']['EXPRESS_4'] =  $arResult['EXPRESS_4']['PROPERTIES']['EXPR_2']['VALUE'];
            /* express-8 */
            if($weight > Calc::EXPRESS[0] && $weight <= Calc::EXPRESS[1]){
                $tarif_ex8 = (float)$arResult['EXPRESS_8']['PROPERTIES']['EXPR_3']['VALUE'];
            }elseif($weight > Calc::EXPRESS[1]){
                $tarif_ex8 = (float)$arResult['EXPRESS_8']['PROPERTIES']['EXPR_3']['VALUE'] +
                    (float)$arResult['EXPRESS_8']['PROPERTIES']['EXPR_4']['VALUE']*$uplimit;
            }

            $arResult['TARIF_ITOG_MSK']['EXPRESS_8'] = round($tarif_ex8);
            $arResult['TIMEDEV']['EXPRESS_8'] =  $arResult['EXPRESS_8']['PROPERTIES']['EXPR_1']['VALUE'];
            $arResult['CALLCOURIER']['EXPRESS_8'] =  $arResult['EXPRESS_8']['PROPERTIES']['EXPR_2']['VALUE'];
           // AllFunc::AddToLogs("testCalс", ["MESS"=>" Москва - Москва ",  $arResult['TARIF_ITOG_MSK']]);
        }
        /* Московская область - CПБ */
        elseif($obj_app->option_4)
        {
            $flag = true;
            $arResult['TARIF_ITOG'] = 0;
            if($arResult['RECIPIENT']['IBLOCK_SECTION_ID'] == 641){
                $dist_arr = AllFunc::GetInfoArr( false,$arResult['RECIPIENT']['ID'], 6, $obj_app->arSelect);
            }elseif($arResult['SENDER']['IBLOCK_SECTION_ID'] == 641){
                $dist_arr = AllFunc::GetInfoArr( false,$arResult['SENDER']['ID'], 6, $obj_app->arSelect);
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
            $tarif = $obj_app->GetTarifMskOblSPB($arResult, $dist_mkad, $ext);
            $arResult['TIMEDEV'] = Calc::DEFAULT_DEV_C;  /* переделать как дадут сроки */
           // AllFunc::AddToLogs("testCalс", ["MESS"=>"  Московская область - СПБ ", $dist_arr]);
        }
        /* Московская область - Ленинградская область */
        elseif($obj_app->option_5){
            $flag = true;
            $dist_arr_sender =  AllFunc::GetInfoArr( false,$arResult['SENDER']['ID'], 6, $obj_app->arSelect);
            $dist_arr_recipient =  AllFunc::GetInfoArr( false,$arResult['RECIPIENT']['ID'], 6, $obj_app->arSelect);
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
                $tr =  AllFunc::GetInfoArr( false,52440321, 111, $obj_app->arSelect);
            }
            if(($dist_mkad <= DEFAULT_DISTANCE && $dist_kad > DEFAULT_DISTANCE)){
                $tr =  AllFunc::GetInfoArr( false,52440573, 111, $obj_app->arSelect);
            }
            if(($dist_mkad > DEFAULT_DISTANCE && $dist_kad <= DEFAULT_DISTANCE)){
                $tr =  AllFunc::GetInfoArr( false,52440575, 111, $obj_app->arSelect);
            }
            if(($dist_mkad > DEFAULT_DISTANCE && $dist_kad > DEFAULT_DISTANCE)){
                $tr =  AllFunc::GetInfoArr( false,52440576, 111, $obj_app->arSelect);
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
            $arResult['TIMEDEV'] = Calc::DEFAULT_DEV_MSO;        /* 4-6 переделать как дадут сроки */
            if($ext == "Y"){
                $obj_app->GetTarifMSKSpbOBL($arResult, false);
            }
           // AllFunc::AddToLogs("testCalс", ["MESS"=>"  Московская область - Ленинградская область ", $arResult]);
        }
        /*  Москва - в другой регион кроме Московской области, СПБ и ЛО */
        elseif($obj_app->option_6)
        {
            $coeff = $obj_app->GetMsk($arResult);
          //  AllFunc::AddToLogs("testCalс", ["MESS"=>"  Москва - в другой регион кроме Московской области, СПБ и ЛО ", $arResult]);
        }
        /* любой - Питер */
        elseif($obj_app->option_7){
            $coeff = $obj_app->GetMsk($arResult);
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
           // AllFunc::AddToLogs("testCalс", ["MESS"=>" любой - Питер ", $arResult]);
        }
        /* москва - область, МО-МО */
        elseif($obj_app->option_8)
        {
          //  AllFunc::AddToLogs("testCalс", ["MESS"=>"  москва -область, МО-МО ", $arResult]);
            $arResult['ZONEDEV'] = \Bitrix\Main\Localization\Loc::GetMessage('MOS_OBL');
            $coeff = "Без коэфициента, МО";
            $tarifs = $obj_app->GetMskObl($arResult);
            $tarif_05 = $tarifs[0];
            $tarif_1 = $tarifs[1];
            $tarif_hight = $tarifs[2];
        }
        /* любой кроме Москвы и Московской области - Ленинградская область  */
        elseif($obj_app->option_9){
            $flag = true;
            /* вычислить не ЛО и посчитать его до Питера  */
            if(($arResult['SENDER']['IBLOCK_SECTION_ID'] != 656 &&
                    $arResult['RECIPIENT']['IBLOCK_SECTION_ID'] == 656) ||
                ($arResult['RECIPIENT']['IBLOCK_SECTION_ID'] != 656 &&
                    $arResult['SENDER']['IBLOCK_SECTION_ID'] == 656)
            ){
                $coeff = $obj_app->GetMsk($arResult);
                $arResult['RASCH_COEFF'] = $coeff;
                $obj_app->GetTarifNoMskObl($arResult);
                /* промежуточный тариф */
                $time_PSB = $arResult['TIMEDEV'];
                $tarif_itog_psb = (float)$arResult['TARIF_ITOG'];
                //AddToLogs("testCalc",  $arResult);
                $tarif = $obj_app->GetLenOblNew($arResult);
                AllFunc::AddToLogs("testCalс", $tarif);
                if(is_array($tarif)){
                    $arResult['TARIF_ITOG'] = $tarif[1]+$tarif_itog_psb;
                    $arResult['TIMEDEV'] = $obj_app->sumTimeDev($tarif[0], $time_PSB );
                }
            }
           // AllFunc::AddToLogs("testCalс", ["MESS"=>"любой кроме Москвы и Московской области - Ленинградская область область ", $arResult]);
        }
        /*Любой кроме москвы и питера - Мос область */
        elseif($obj_app->option_10)
        {
           $flag = true;
            /* вычислить не МО и посчитать его до Москвы  */
            if(($arResult['SENDER']['IBLOCK_SECTION_ID'] != 641 &&
                    $arResult['RECIPIENT']['IBLOCK_SECTION_ID'] == 641) ||
                ($arResult['RECIPIENT']['IBLOCK_SECTION_ID'] != 641 &&
                    $arResult['SENDER']['IBLOCK_SECTION_ID'] == 641)
            ){
                $coeff = $obj_app->GetMsk($arResult);
                $arResult['RASCH_COEFF'] = $coeff;
                $obj_app->GetTarifNoMskObl($arResult);
                /* промежуточный тариф */
                $time_Msk = $arResult['TIMEDEV'];
                $tarif_itog_msk = (float)$arResult['TARIF_ITOG'];
                //AddToLogs("testCalc",  $arResult);
                $tarif = $obj_app->GetMskOblNew($arResult);
                AllFunc::AddToLogs("testCalс", $tarif);
                if(is_array($tarif)){
                    $arResult['TARIF_ITOG'] = $tarif[1]+$tarif_itog_msk;
                    $arResult['TIMEDEV'] = $obj_app->sumTimeDev($tarif[0], $time_Msk );
                }
            }
           // AllFunc::AddToLogs("testCalс", ["MESS"=>" Любой кроме москвы и питера - Мос область ", $arResult]);
        }
        /* Москва - Питер */
        elseif($obj_app->option_11)
        {
            /* 107 51897803 - москва-питер*/
            $flag = true;
            $obj_app->GetTarifMskPt($arResult);
            $coeff = "N";
           // AllFunc::AddToLogs("testCalс", ["MESS"=>" Москва-Питер ", $arResult]);
        }
        /* Питер - ЛО область */
        elseif($obj_app->option_12){
            $flag = true;
            $obj_app->GetTarifSPBSpbOBL($arResult);
            AllFunc::AddToLogs("testCalс", ["MESS"=>" Питер  - ЛО область ", $arResult]);
        }
        /* Москва - ЛО область */
        elseif($obj_app->option_13){
            $flag = true;
            /* получить тариф и время доставки */
            $obj_app->GetTarifMSKSpbOBL($arResult);
            //AllFunc::AddToLogs("testCalс", ["MESS"=>" Москва - ЛО область ", $arResult]);
        }
        /* внутри областей кроме подмосковья и лен области */
        elseif($obj_app->option_14)
        {
            $req = $obj_app->GetMainCity($arResult, true);
            if($req==="Y"){
                $coeff = Calc::C_1;
                $arResult['ZONEDEV'] = Calc::DEFAULT_ZONE_REGION;
                $arResult['TIMEDEV'] = Calc::DEFAULT_DEV_REGION;
               // AllFunc::AddToLogs("testCalс", ["MESS"=>" внутри областей кроме подмосковья и лен области ", $arResult]);
            }
            elseif($req==="C"){
                $coeff = $obj_app->GetRegion($arResult);
                //AllFunc::AddToLogs("testCalс", ["MESS"=>" внутри областей, основные города ", $arResult]);
            }
            else{
                $arResult['ERROR']['ERR_RASCHET'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_RASCHET');
            }

        }
        else
        {
            $arResult['ERROR']['ERR_RASCHET'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_RASCHET');
           // AllFunc::AddToLogs("testCalс", ["MESS"=>" Общая ошибка ", $arResult]);
            //echo 'обработать ошибку - по введенным данным нас. пунктов расчет не возможен';
        }
        $arResult['RASCH_COEFF'] = $coeff;
        if(!$flag){
            if ((float)$coeff){
                $obj_app->GetTarifNoMskObl($arResult );
            }else{
                $obj_app->GetTarifMskObl($arResult, $tarif_05, $tarif_1, $tarif_hight);
            }
        }
        $tarif_rasch_itog = (float)$arResult['TARIF_ITOG'];
        $arResult['TARIF_ITOG'] =  round($tarif_rasch_itog);

    }else{
        $arResult['ERROR']['ERR_INPUT_CITY_INN'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_INPUT_CITY_INN');
    }
    if($_GET['request'] === 'Y' && $_GET['api'] === 'Y'){
        $arr_req = [
            'TARIF' => $arResult['TARIF_ITOG'],
            'FULLWEIGTH' => $arResult['FULLWEIGTH'],
            'CITY_FROM' => $arResult['SENDER']['FULLNAME'],
            'CITY_TO' => $arResult['RECIPIENT']['FULLNAME'],
        ];
        $arr_req_json = AllFunc::convArrayToUTF($arr_req);
        $data_req = [
            "data"=>$arr_req_json
        ];
        $dataJsonReq = json_encode($data_req);
        echo $dataJsonReq;
        exit;
    }
    $arResult = AllFunc::convArrayToUTF($arResult);
    $data = [
        "data"=>$arResult
    ];
    $dataJson = json_encode($data);
    echo $dataJson;
    exit;
}
$this->IncludeComponentTemplate();
