<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
    die();
}
require_once __DIR__ . '/../NPAllFunc.php';
require_once __DIR__ . '/NPCalcI.php';
/**
 * Class NPCalc
 */
class NPCalc extends NPAllFunc implements NPCalcI
{

    public $arSelect = [
    "NAME",
    "IBLOCK_ID",
    "ID",
    "PROPERTY_*",
    ];
    private $coeff = 0.00;
    private $tarif;
    private $tarifs;
    private $tarif_05;
    private $tarif_1;
    private $tarif_hight;
    private $tarif_ex2;
    private $tarif_ex4;
    private $tarif_ex8;
    public $arrPost = [];
    public $current;
    private $in_get;
    public $id_sender;
    public $id_recipient;
    public $name_sender;
    public $name_recipient;
    public $Res = [];
    public  $option_1 = false;
    public  $option_2 = false;
    public  $option_3 = false;
    public  $option_4 = false;
    public  $option_5 = false;
    public  $option_6 = false;
    public  $option_7 = false;
    public  $option_8 = false;
    public  $option_9 = false;
    public  $option_10 = false;
    public  $option_11 = false;
    public  $option_12 = false;
    public  $option_13 = false;
    public  $option_14 = false;
    protected $flag = false;


    /**
     * NPCalc constructor.
     * @param $request
     */
    public function __construct($in_post, $in_get, $inner = false)
    {
        CModule::IncludeModule("iblock");
        $this->in_get = $in_get;
        if($inner){
            if ($in_get['api'] === 'Y'){
                $capital = 'Россия';
                if(mb_detect_encoding($in_post['city_sender'] === 'UTF-8')){
                    $city_send_api = iconv('utf-8', 'windows-1251', $in_post['city_sender']);
                }else{
                    $city_send_api =  $in_post['city_sender'];
                }

                if(mb_detect_encoding($in_post['region_sender'] === 'UTF-8')){
                    $region_send_api =  iconv('utf-8', 'windows-1251',$in_post['region_sender']);
                }else{
                    $region_send_api =  $in_post['region_sender'];
                }

                if(mb_detect_encoding($in_post['city_recipient'] === 'UTF-8')){
                    $city_rec_api =  iconv('utf-8', 'windows-1251',$in_post['city_recipient']);
                }else{
                    $city_rec_api =  $in_post['city_recipient'];
                }

                if(mb_detect_encoding($in_post['region_recipient'] === 'UTF-8')){
                    $region_rec_api =  iconv('utf-8', 'windows-1251',$in_post['region_recipient']);
                }else{
                    $region_rec_api = $in_post['region_recipient'];
                }


                $in_post['city_sender'] =  $city_send_api . ', ' . $region_send_api . ', ' . $capital;
                $in_post['city_recipient'] = $city_rec_api . ', ' . $region_rec_api . ', ' . $capital;



                if (empty($in_post['weight']))  $in_post['weight'] = 1.0;
                $id_city_send = NPAllFunc::GetCityId(iconv('utf-8', 'windows-1251',$in_post['city_sender']));
                $id_city_rec = NPAllFunc::GetCityId(iconv('utf-8', 'windows-1251',$in_post['city_recipient']));
                $data_in = [];
                $data_in['city_1'] = $in_post['city_sender'];
                $data_in['citycode_1'] = (int)$id_city_send;
                $data_in['city_0'] = $in_post['city_recipient'];
                $data_in['citycode_0'] = (int)$id_city_rec;
                $data_in['r1'][0] = '';
                $data_in['r2'][0] = '';
                $data_in['r3'][0] = '';
                $data_in['ves'][0] = $in_post['weight'];
                $this->arrPost = $data_in;

            }
        }else{
            $this->arrPost = static::arFromUtfToWin($in_post);
        }

            if (!empty($this->arrPost['ves'][0]) && !isset($in_get['type'])){
                if(preg_match('/,/i', $this->arrPost['ves'][0])){
                    $vzam =  preg_replace('/,/i', '.', $this->arrPost['ves'][0]);
                    $this->arrPost['ves'][0] = $vzam;
                }
            }
            if (!empty($this->arrPost['ves'] && $in_get['type'] === "z")){
                if(preg_match('/,/i', $this->arrPost['ves'])){
                    $vzam =  preg_replace('/,/i', '.', $this->arrPost['ves']);
                    $this->arrPost['ves'] = $vzam;
                }
            }
            //static::AddToLogs("testCalс", $this->arrPost);
            $city_0_ok = explode(',', $this->arrPost['city_0']);
            if(count($city_0_ok) === 3){
                if (empty($this->arrPost['citycode_0'])){
                    $this->arrPost['citycode_0'] = static::GetCityId($this->arrPost['city_0']);
                }
                $city_0_ok = true;
            }else{
                $city_0_ok = false;
            }
            $city_1_ok = explode(',', $this->arrPost['city_1']);
            if(count($city_1_ok) === 3){
                if (empty($this->arrPost['citycode_1'])){
                    $this->arrPost['citycode_1'] = static::GetCityId($this->arrPost['city_1']);
                }
                $city_1_ok = true;
            } else{
                $city_1_ok = false;
            }
            if(!empty($this->arrPost['citycode_0']) && !empty($this->arrPost['citycode_1']) && $city_0_ok && $city_1_ok){
                $this->current = true;
            }else{
                $this->current = false;
            }
            $this->GetResult();

    }

    /**
     * @return NPCalc
     */
    public function GetResult()
    {
        $Res = [];
        $kf =  $this->WhatIsGabWeight();
        foreach($this->arrPost as $key=>$value){
            $string = $key;
            $str = $string[0];
            if($str == "r"){
                $pref = $string[1];
                $k = $str.$pref;
                if(!empty($this->arrPost[$k])){
                    if($pref == 1){

                        foreach($this->arrPost[$k] as $i=>$v){
                            $arPack[0][$i] = $v;
                        }
                    }
                    if($pref == 2){
                        foreach($this->arrPost[$k] as $i=>$v){
                            $arPack[1][$i] = $v;
                        }
                    }
                    if($pref == 3){
                        foreach($this->arrPost[$k] as $i=>$v){
                            $arPack[2][$i] = $v;
                        }
                    }
                }
            }
        }
        $c=count($arPack[0]);
        foreach($arPack as $key=>$value){
            if($key == 0){
                for($i = 0; $i < $c; $i++){
                    $arr[$i]['h'] = (float)$value[$i];
                }
            }
            if($key == 1){
                for($i = 0; $i < $c; $i++){
                    $arr[$i]['l'] = (float)$value[$i];
                }
            }
            if($key == 2){
                for($i = 0; $i < $c; $i++){
                    $arr[$i]['w'] = (float)$value[$i];
                }
            }

        }
        foreach($this->arrPost['ves'] as $key=>$value){
            $arr[$key]['ves'] = (float)$value;
        }
        $obW = 0.00;
        $vesIt = 0.00;
        foreach($arr as $item=>$value){
            if($value['ves'] > 0 && ($value['h'] > 0 && $value['l'] > 0 && $value['w'] >  0)){
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
        if ($this->in_get['type'] === 'z' ){
            if(!$this->arrPost['ves']){
                $this->arrPost['ves'] = 0.10;
            }
            $Res['FULLWEIGTH'] = $this->arrPost['ves'];
        }else{
            $Res['FULLWEIGTH'] = $vesIt;
        }
        $Res['GAB'] = $arr;
        $this->id_sender = $this->arrPost['citycode_0'];
        $Res['ID_SENDER'] = $this->id_sender;
        $this->id_recipient = $this->arrPost['citycode_1'];
        $Res['ID_RECIPIENT'] = $this->id_recipient;
        $this->name_sender = trim(strip_tags($this->arrPost['city_0']));
        $Res['SENDER']['FULLNAME'] = $this->name_sender;
        $this->name_recipient = trim(strip_tags($this->arrPost['city_1']));
        $Res['RECIPIENT']['FULLNAME'] = $this->name_recipient;
        $arSelect = [
            "ID","NAME","IBLOCK_SECTION_ID",
            "PROPERTY_ZONE", "PROPERTY_TIME_DELIVERY",
            "PROPERTY_SPRAV", "PROPERTY_COEFF", "PROPERTY_SMALL_DEV"
        ];
        $arr_s = static::GetInfoArr(false, $this->id_sender, 6, $arSelect);
        $arr_r = static::GetInfoArr( false, $this->id_recipient, 6, $arSelect);
        if(is_array($arr_s))
        {
            foreach($arr_s as $key=>$value){
                $Res['SENDER'][$key] = $value;
            }
        }else{
            $Res['ERROR']['SENDER'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_INPUT_SENDER');
        }
        if(is_array($arr_r))
        {
            foreach ($arr_r as $key => $value) {
                $Res['RECIPIENT'][$key] = $value;
            }
        }else{
            $Res['ERROR']['RECIPIENT'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_INPUT_RECIPIENT');
        }

        if(($Res['SENDER']['ID'] == $Res['RECIPIENT']['ID']) &&
            !($Res['SENDER']['ID']==8054 && $Res['RECIPIENT']['ID'] == 8054))
        {
            $this->option_1 = true;
        }
        if(($Res['SENDER']['ID'] != 8054 && $Res['RECIPIENT']['ID'] != 8054) &&
            ($Res['SENDER']['ID'] != 8678 && $Res['RECIPIENT']['ID'] != 8678) &&
            ($Res['SENDER']['IBLOCK_SECTION_ID'] != 641 && $Res['RECIPIENT']['IBLOCK_SECTION_ID'] != 641) &&
            ($Res['SENDER']['IBLOCK_SECTION_ID'] != 656 && $Res['RECIPIENT']['IBLOCK_SECTION_ID'] != 656) &&
            ($Res['SENDER']['NAME'] != $Res['RECIPIENT']['NAME']) && ($Res['SENDER']['IBLOCK_SECTION_ID'] != $Res['RECIPIENT']['IBLOCK_SECTION_ID']))
        {
            $this->option_2 = true;
        }
        if($Res['SENDER']['ID'] == 8054 && $Res['RECIPIENT']['ID'] == 8054)
        {
            $this->option_3 = true;
        }
        if(($Res['SENDER']['ID'] == 8678 &&
                $Res['RECIPIENT']['IBLOCK_SECTION_ID'] == 641) ||
            ($Res['SENDER']['IBLOCK_SECTION_ID'] == 641 &&
                $Res['RECIPIENT']['ID'] == 8678))
        {
            $this->option_4 = true;
        }
        if(($Res['SENDER']['IBLOCK_SECTION_ID'] == 641 &&
                $Res['RECIPIENT']['IBLOCK_SECTION_ID'] == 656) ||
            ($Res['SENDER']['IBLOCK_SECTION_ID'] == 656 &&
                $Res['RECIPIENT']['IBLOCK_SECTION_ID'] == 641))
        {
            $this->option_5 = true;
        }
        if((($Res['SENDER']['ID'] == 8054 && $Res['RECIPIENT']['ID'] != 8678 &&
                    $Res['RECIPIENT']['IBLOCK_SECTION_ID'] != 641) ||
                ($Res['RECIPIENT']['ID'] == 8054 && $Res['SENDER']['ID'] != 8678 &&
                    $Res['SENDER']['IBLOCK_SECTION_ID'] != 641)) &&
            !($Res['SENDER']['ID'] == 8054 && $Res['RECIPIENT']['ID'] == 8054 ) &&
            !($Res['RECIPIENT']['IBLOCK_SECTION_ID'] == 656 ||
                $Res['SENDER']['IBLOCK_SECTION_ID'] == 656))
        {
            $this->option_6 = true;
        }
        if(($Res['SENDER']['ID'] == 8678 || $Res['RECIPIENT']['ID'] == 8678) &&
            ($Res['RECIPIENT']['ID'] != 8054 && $Res['SENDER']['ID'] != 8054) &&
            ($Res['RECIPIENT']['IBLOCK_SECTION_ID'] != 656 && $Res['SENDER']['IBLOCK_SECTION_ID'] != 656) &&
            ($Res['RECIPIENT']['IBLOCK_SECTION_ID'] != 641 && $Res['SENDER']['IBLOCK_SECTION_ID'] != 641))
        {
            $this->option_7 = true;
        }
        if(($Res['SENDER']['IBLOCK_SECTION_ID'] == 641 ||
                $Res['SENDER']['ID'] == 8054) &&
            ($Res['RECIPIENT']['IBLOCK_SECTION_ID'] == 641 ||
                $Res['RECIPIENT']['ID'] == 8054))
        {
            $this->option_8 = true;
        }
        if(($Res['SENDER']['IBLOCK_SECTION_ID'] == 656 ||
                $Res['RECIPIENT']['IBLOCK_SECTION_ID'] == 656) &&
            !($Res['SENDER']['ID'] == 8678 || $Res['RECIPIENT']['ID'] == 8678) &&
            !($Res['RECIPIENT']['ID'] == 8054 || $Res['SENDER']['ID'] == 8054) &&
            !($Res['SENDER']['IBLOCK_SECTION_ID'] == 641 ||
                $Res['RECIPIENT']['IBLOCK_SECTION_ID'] == 641))
        {
            $this->option_9 = true;
        }
        if(( $Res['SENDER']['IBLOCK_SECTION_ID'] == 641 ||
                $Res['RECIPIENT']['IBLOCK_SECTION_ID'] == 641) &&
            $Res['SENDER']['ID'] != 8054 && $Res['RECIPIENT']['ID'] != 8054 &&
            $Res['SENDER']['ID'] != 8678 && $Res['RECIPIENT']['ID'] != 8678 &&
            $Res['RECIPIENT']['IBLOCK_SECTION_ID'] != 656 &&
            $Res['SENDER']['IBLOCK_SECTION_ID'] != 656)
        {
            $this->option_10 = true;
        }
        if(($Res['RECIPIENT']['ID'] == 8054 && $Res['SENDER']['ID'] == 8678) ||
            ($Res['SENDER']['ID'] == 8054 && $Res['RECIPIENT']['ID'] == 8678))
        {
            $this->option_11 = true;
        }
        if(($Res['SENDER']['ID'] == 8678 || $Res['RECIPIENT']['ID'] == 8678) &&
            ($Res['RECIPIENT']['IBLOCK_SECTION_ID'] == 656 ||
                $Res['SENDER']['IBLOCK_SECTION_ID'] == 656))
        {
            $this->option_12 = true;
        }
        if(($Res['SENDER']['ID'] == 8054 || $Res['RECIPIENT']['ID'] == 8054) &&
            ($Res['RECIPIENT']['IBLOCK_SECTION_ID'] == 656 ||
                $Res['SENDER']['IBLOCK_SECTION_ID'] == 656))
        {
            $this->option_13 = true;
        }
        if(($Res['SENDER']['IBLOCK_SECTION_ID'] == $Res['RECIPIENT']['IBLOCK_SECTION_ID']) &&
            ($Res['SENDER']['IBLOCK_SECTION_ID'] != 641 ) &&
            ($Res['SENDER']['IBLOCK_SECTION_ID'] != 656))
        {
            $this->option_14 = true;
        }
        $this->Res = $Res;
        $this->routCalc();
        return $this;
    }

    /**
     * @return array
     */
    public function routCalc()
    {
      if($this->option_1) {
            if ($this->Res['SENDER']['IBLOCK_SECTION_ID'] == $this->Res['RECIPIENT']['IBLOCK_SECTION_ID']) {
                $this->flag = true;
                $this->tarif = 0.00;
                $this->coeff = 0.00;
                /* определить главный- не главный  */
                $q = $this->GetMainCity($this->Res);
                if ($q) {
                    $this->Res['TARIF_ITOG'] = $this->GetTarifSPB($this->Res, self::TMain, self::THMain);
                    $this->Res['TIMEDEV'] = self::DEFAULT_DEV_CITY;
                } else {
                    $this->Res['ERROR']['ERR_RASCHET'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_RASCHET');
                }
            } else {
                $this->Res['ERROR']['ERR_RASCHET'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_RASCHET');
            }
        }
        elseif($this->option_2){
            $this->coeff = $this->GetRegion($this->Res);
        }
        elseif($this->option_3){
            /* тариф Стандарт
           108
               Экспресс
           109
            */
            $this->flag = true;
            $weight = (float)$this->Res['FULLWEIGTH'];
            $this->GetTarifMsk($this->Res);
            /* стандарт */
            $uplimit = (float) $this->Res['FULLWEIGTH'] - 1.00;
            $uplimit = ceil($uplimit);
            if($weight > self::STANDART[0] && $weight <= self::STANDART[1]){
                $this->tarif = (float)$this->Res['STANDART']['PROPERTIES']['ST_1']['VALUE'];
            }elseif($weight > self::STANDART[1] && $weight <= self::STANDART[2]){
                $this->tarif =  (float)$this->Res['STANDART']['PROPERTIES']['ST_1']['VALUE'] +
                    (float)$this->Res['STANDART']['PROPERTIES']['ST_2']['VALUE']*$uplimit;
            }elseif($weight > self::STANDART[2] && $weight <= self::STANDART[3]){
                $this->tarif =  (float)$this->Res['STANDART']['PROPERTIES']['ST_1']['VALUE'] +
                    (float)$this->Res['STANDART']['PROPERTIES']['ST_3']['VALUE']*$uplimit;
            }elseif($weight > self::STANDART[3] && $weight <= self::STANDART[4]){
                $this->tarif =  (float)$this->Res['STANDART']['PROPERTIES']['ST_1']['VALUE'] +
                    (float)$this->Res['STANDART']['PROPERTIES']['ST_4']['VALUE']*$uplimit;
            }elseif($weight > self::STANDART[4] && $weight <= self::STANDART[5]){
                $this->tarif =  (float)$this->Res['STANDART']['PROPERTIES']['ST_1']['VALUE'] +
                    (float)$this->Res['STANDART']['PROPERTIES']['ST_5']['VALUE']*$uplimit;
            }elseif($weight > self::STANDART[5]){
                $this->tarif =  (float)$this->Res['STANDART']['PROPERTIES']['ST_1']['VALUE'] +
                    (float)$this->Res['STANDART']['PROPERTIES']['ST_6']['VALUE']*$uplimit;
            }
            $this->Res['TARIF_ITOG_MSK']['STANDART'] = round($this->tarif);
            $this->Res['TIMEDEV']['STANDART'] =  $this->Res['STANDART']['PROPERTIES']['TIMEDEV']['VALUE'];
            /* express-2 */
            if($weight > self::EXPRESS[0] && $weight <= self::EXPRESS[1]){
                $this->tarif_ex2 = (float)$this->Res['EXPRESS_2']['PROPERTIES']['EXPR_3']['VALUE'];
            }elseif($weight > self::EXPRESS[1]){
                $this->tarif_ex2 = (float)$this->Res['EXPRESS_2']['PROPERTIES']['EXPR_3']['VALUE'] +
                    (float)$this->Res['EXPRESS_2']['PROPERTIES']['EXPR_4']['VALUE']*$uplimit;
            }
            $this->Res['TARIF_ITOG_MSK']['EXPRESS_2'] = round($this->tarif_ex2);
            $this->Res['TIMEDEV']['EXPRESS_2'] =  $this->Res['EXPRESS_2']['PROPERTIES']['EXPR_1']['VALUE'];
            $this->Res['CALLCOURIER']['EXPRESS_2'] =  $this->Res['EXPRESS_2']['PROPERTIES']['EXPR_2']['VALUE'];
            /* express-4 */
            if($weight > self::EXPRESS[0] && $weight <= self::EXPRESS[1]){
                $this->tarif_ex4 = (float)$this->Res['EXPRESS_4']['PROPERTIES']['EXPR_3']['VALUE'];
            }elseif($weight > self::EXPRESS[1]){
                $this->tarif_ex4 = (float)$this->Res['EXPRESS_4']['PROPERTIES']['EXPR_3']['VALUE'] +
                    (float)$this->Res['EXPRESS_4']['PROPERTIES']['EXPR_4']['VALUE']*$uplimit;
            }

            $this->Res['TARIF_ITOG_MSK']['EXPRESS_4'] = round($this->tarif_ex4);
            $this->Res['TIMEDEV']['EXPRESS_4'] =  $this->Res['EXPRESS_4']['PROPERTIES']['EXPR_1']['VALUE'];
            $this->Res['CALLCOURIER']['EXPRESS_4'] =  $this->Res['EXPRESS_4']['PROPERTIES']['EXPR_2']['VALUE'];
            /* express-8 */
            if($weight > self::EXPRESS[0] && $weight <= self::EXPRESS[1]){
                $this->tarif_ex8 = (float)$this->Res['EXPRESS_8']['PROPERTIES']['EXPR_3']['VALUE'];
            }elseif($weight > self::EXPRESS[1]){
                $this->tarif_ex8 = (float)$this->Res['EXPRESS_8']['PROPERTIES']['EXPR_3']['VALUE'] +
                    (float)$this->Res['EXPRESS_8']['PROPERTIES']['EXPR_4']['VALUE']*$uplimit;
            }

            $this->Res['TARIF_ITOG_MSK']['EXPRESS_8'] = round($this->tarif_ex8);
            $this->Res['TIMEDEV']['EXPRESS_8'] =  $this->Res['EXPRESS_8']['PROPERTIES']['EXPR_1']['VALUE'];
            $this->Res['CALLCOURIER']['EXPRESS_8'] =  $this->Res['EXPRESS_8']['PROPERTIES']['EXPR_2']['VALUE'];
            // static::AddToLogs("testCalс", ["MESS"=>" Москва - Москва ",  $Res['TARIF_ITOG_MSK']]);
        }
        elseif($this->option_4){
            $this->flag = true;
            $this->Res['TARIF_ITOG'] = 0;
            if($this->Res['RECIPIENT']['IBLOCK_SECTION_ID'] == 641){
                $dist_arr = static::GetInfoArr( false,$this->Res['RECIPIENT']['ID'], 6, $this->arSelect);
            }elseif($this->Res['SENDER']['IBLOCK_SECTION_ID'] == 641){
                $dist_arr = static::GetInfoArr( false,$this->Res['SENDER']['ID'], 6, $this->arSelect);
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
            $this->tarif = $this->GetTarifMskOblSPB($this->Res, $dist_mkad, $ext);
            $this->Res['TIMEDEV'] = self::DEFAULT_DEV_C;  /* переделать как дадут сроки */
            // static::AddToLogs("testCalс", ["MESS"=>"  Московская область - СПБ ", $dist_arr]);
        }
        elseif($this->option_5){
            $this->flag = true;
            $dist_arr_sender =  static::GetInfoArr( false,$this->Res['SENDER']['ID'], 6, $this->arSelect);
            $dist_arr_recipient =  static::GetInfoArr( false,$this->Res['RECIPIENT']['ID'], 6, $this->arSelect);
            //AddToLogs("testCalс", ["MESS"=>"  Московская область - Ленинградская область ",  $dist_arr_sender]);
            if($this->Res['SENDER']['IBLOCK_SECTION_ID'] == 641 &&
                $this->Res['RECIPIENT']['IBLOCK_SECTION_ID'] == 656)
            {
                $dist_mkad = (int) $dist_arr_sender['PROPERTIES']['OUT_MKAD']['VALUE'];
                $dist_kad = (int)  $dist_arr_recipient['PROPERTIES']['OUT_MKAD']['VALUE'];
                $ext = $dist_arr_sender['PROPERTIES']['SMALL_DEV']['VALUE'];
            }elseif($this->Res['SENDER']['IBLOCK_SECTION_ID'] == 656 &&
                $this->Res['RECIPIENT']['IBLOCK_SECTION_ID'] == 641 )
            {
                $dist_mkad = (int) $dist_arr_recipient['PROPERTIES']['OUT_MKAD']['VALUE'];
                $dist_kad = (int) $dist_arr_sender['PROPERTIES']['OUT_MKAD']['VALUE'];
                $ext = $dist_arr_recipient['PROPERTIES']['SMALL_DEV']['VALUE'];
            }

            $this->Res['TARIF_ITOG'] = 0;
            $weight = (float)$this->Res['FULLWEIGTH'];
            $tr = [];

            if(($dist_mkad <= DEFAULT_DISTANCE && $dist_kad <= DEFAULT_DISTANCE)){
                $tr =  static::GetInfoArr( false,52440321, 111, $this->arSelect);
            }
            if(($dist_mkad <= DEFAULT_DISTANCE && $dist_kad > DEFAULT_DISTANCE)){
                $tr =  static::GetInfoArr( false,52440573, 111, $this->arSelect);
            }
            if(($dist_mkad > DEFAULT_DISTANCE && $dist_kad <= DEFAULT_DISTANCE)){
                $tr =  static::GetInfoArr( false,52440575, 111, $this->arSelect);
            }
            if(($dist_mkad > DEFAULT_DISTANCE && $dist_kad > DEFAULT_DISTANCE)){
                $tr =  static::GetInfoArr( false,52440576, 111, $this->arSelect);
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
            $this->Res['TARIF_ITOG'] =  $tarif;
            $this->Res['TIMEDEV'] = self::DEFAULT_DEV_MSO;        /* 4-6 переделать как дадут сроки */
            if($ext == "Y"){
                $this->GetTarifMSKSpbOBL($this->Res, false);
            }
            // static::AddToLogs("testCalс", ["MESS"=>"  Московская область - Ленинградская область ", $Res]);
        }
        elseif($this->option_6){
            $this->coeff = $this->GetMsk($this->Res);
        }
        elseif($this->option_7){
            $this->coeff = $this->GetMsk($this->Res);
            if(preg_match('/^[0-9]{1}\s?-\s?[0-9]{1,2}$/', $this->Res['TIMEDEV'])){
                $art = explode('-', $this->Res['TIMEDEV']);
                if(!empty(is_array($art))){
                    $res1 = (int)$art[0]+1;
                    $res2 = (int)$art[1]+1;
                    $this->Res['TIMEDEV'] = "$res1-$res2";
                }
            }
            if(preg_match('/^[0-9]{1,2}$/', $this->Res['TIMEDEV'])){
                $this->Res['TIMEDEV'] += 1;
            }
        }
        elseif($this->option_8){
            $Res['ZONEDEV'] = \Bitrix\Main\Localization\Loc::GetMessage('MOS_OBL');
            $this->coeff = "Без коэфициента, МО";
            $this->tarifs = $this->GetMskObl($this->Res);
            $this->tarif_05 = $this->tarifs[0];
            $this->tarif_1 = $this->tarifs[1];
            $this->tarif_hight = $this->tarifs[2];
        }
        elseif($this->option_9){
            $this->flag = true;
            /* вычислить не ЛО и посчитать его до Питера  */
            if(($this->Res['SENDER']['IBLOCK_SECTION_ID'] != 656 &&
                    $this->Res['RECIPIENT']['IBLOCK_SECTION_ID'] == 656) ||
                ($this->Res['RECIPIENT']['IBLOCK_SECTION_ID'] != 656 &&
                    $this->Res['SENDER']['IBLOCK_SECTION_ID'] == 656)
            ){
                $this->coeff = $this->GetMsk($this->Res);
                $this->Res['RASCH_COEFF'] = $this->coeff;
                $this->GetTarifNoMskObl($this->Res);
                /* промежуточный тариф */
                $time_PSB = $this->Res['TIMEDEV'];
                $tarif_itog_psb = (float)$this->Res['TARIF_ITOG'];
                //AddToLogs("testCalc",  $Res);
                $this->tarif = $this->GetLenOblNew($this->Res);
                static::AddToLogs("testCalс", $this->tarif);
                if(is_array($this->tarif)){
                    $this->Res['TARIF_ITOG'] = $this->tarif[1]+$tarif_itog_psb;
                    $this->Res['TIMEDEV'] = $this->sumTimeDev($this->tarif[0], $time_PSB );
                }
            }
        }
        elseif($this->option_10){
            $this->flag = true;
            /* вычислить не МО и посчитать его до Москвы  */
            if(($this->Res['SENDER']['IBLOCK_SECTION_ID'] != 641 &&
                    $this->Res['RECIPIENT']['IBLOCK_SECTION_ID'] == 641) ||
                ($this->Res['RECIPIENT']['IBLOCK_SECTION_ID'] != 641 &&
                    $this->Res['SENDER']['IBLOCK_SECTION_ID'] == 641)
            ){
                $this->coeff = $this->GetMsk($this->Res);
                $this->Res['RASCH_COEFF'] = $this->coeff;
                $this->GetTarifNoMskObl($this->Res);
                /* промежуточный тариф */
                $time_Msk = $this->Res['TIMEDEV'];
                $tarif_itog_msk = (float)$this->Res['TARIF_ITOG'];
                //AddToLogs("testCalc",  $Res);
                $this->tarif = $this->GetMskOblNew($this->Res);
                static::AddToLogs("testCalс", $this->tarif);
                if(is_array($this->tarif)){
                    $this->Res['TARIF_ITOG'] = $this->tarif[1]+$tarif_itog_msk;
                    $this->Res['TIMEDEV'] = $this->sumTimeDev($this->tarif[0], $time_Msk );
                }
            }
        }
        elseif($this->option_11){
            /* 107 51897803 - москва-питер*/
            $this->flag = true;
            $this->GetTarifMskPt($this->Res);
            $this->coeff = "N";
        }
        elseif($this->option_12){
            $this->flag = true;
            $this->GetTarifSPBSpbOBL($this->Res);
            static::AddToLogs("testCalс", ["MESS"=>" Питер  - ЛО область ", $this->Res]);
        }
        elseif($this->option_13){
            $this->flag = true;
            /* получить тариф и время доставки */
            $this->GetTarifMSKSpbOBL($this->Res);
        }
        elseif($this->option_14){
            $req = $this->GetMainCity($this->Res, true);
            if($req==="Y"){
                $this->coeff = self::C_1;
                $this->Res['ZONEDEV'] = self::DEFAULT_ZONE_REGION;
                $this->Res['TIMEDEV'] = self::DEFAULT_DEV_REGION;
                // static::AddToLogs("testCalс", ["MESS"=>" внутри областей кроме подмосковья и лен области ", $Res]);
            }
            elseif($req==="C"){
                $coeff = $this->GetRegion($Res);
                //static::AddToLogs("testCalс", ["MESS"=>" внутри областей, основные города ", $Res]);
            }
            else{
                $Res['ERROR']['ERR_RASCHET'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_RASCHET');
            }
        }
      else
        {
            $this->Res['ERROR']['ERR_RASCHET'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_RASCHET');
            static::AddToLogs("testCalс", ["MESS"=>" Общая ошибка, по введенным данным нас. пунктов расчет не возможен ", $Res]);
        }
        $this->Res['RASCH_COEFF'] = $this->coeff;
        if(!$this->flag)
        {
            if ((float)$this->coeff){
                $this->GetTarifNoMskObl($this->Res );
            }else{
                $this->GetTarifMskObl($this->Res, $this->tarif_05, $this->tarif_1, $this->tarif_hight);
            }
        }
        $tarif_rasch_itog = (float)$this->Res['TARIF_ITOG'];
        $this->Res['TARIF_ITOG'] =  round($tarif_rasch_itog);
        return $this->Res;
    }


    /* Москва - в другой регион кроме Московской области*/
    /**
     * @param array $Res
     * @return mixed
     */
    function GetMsk(array &$Res)
    {
        if(!empty($Res['SENDER']['PROPERTY_COEFF_VALUE'])){
            $this->coeff = (float)$Res['SENDER']['PROPERTY_COEFF_VALUE'];
        }
        elseif(!empty($Res['RECIPIENT']['PROPERTY_COEFF_VALUE'])){
            $this->coeff = (float)$Res['RECIPIENT']['PROPERTY_COEFF_VALUE'];
        }else{
            $Res['ERROR']['ERR_RASCHET'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_RASCHET');
            //echo 'обработать ошибку - базовый коэфф. при доставке Москва отсутствует ';
        }

        if(!empty($Res['SENDER']['PROPERTY_ZONE_VALUE'])){
            $Res['ZONEDEV'] = (int)$Res['SENDER']['PROPERTY_ZONE_VALUE'];
        }
        elseif(!empty($Res['RECIPIENT']['PROPERTY_ZONE_VALUE'])){
            $Res['ZONEDEV'] = (int)$Res['RECIPIENT']['PROPERTY_ZONE_VALUE'];
        }
        else{
            $Res['ERROR']['ERR_RASCHET'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_RASCHET');
            //echo 'обработать ошибку - базовая зона Москва отсутствует';
        }

        if(!empty($Res['SENDER']['PROPERTY_TIME_DELIVERY_VALUE'])){
            $Res['TIMEDEV'] = (string)$Res['SENDER']['PROPERTY_TIME_DELIVERY_VALUE'];
        }
        elseif(!empty($Res['RECIPIENT']['PROPERTY_TIME_DELIVERY_VALUE'])){
            $Res['TIMEDEV'] = (string)$Res['RECIPIENT']['PROPERTY_TIME_DELIVERY_VALUE'];
        }
        else{
            $Res['ERROR']['ERR_RASCHET'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_RASCHET');
            //echo 'обработать ошибку - время доставки Москва отсутствует';
        }
        return $this->coeff;
    }

    /**
     * @param array $arrCompl
     * @return array
     */
    function getMskCompl(array $arrCompl){
        $cff = $this->GetMsk($arrCompl);
        $arrCompl['RASCH_COEFF'] = $cff;
        return $arrCompl;
    }

    /**
     *  не Москва и не Питер
     * @param array $Res
     *
     * @return mixed
     */
    public function GetRegion(array &$Res)
    {

        if( $Res['RECIPIENT']['PROPERTY_SPRAV_VALUE'] == "Y" && $Res['SENDER']['PROPERTY_SPRAV_VALUE'] == "Y"){
            $this->coeff = self::C_1;
        }elseif( ($Res['RECIPIENT']['PROPERTY_SPRAV_VALUE'] != "Y" && $Res['SENDER']['PROPERTY_SPRAV_VALUE'] == "Y") ||
            ($Res['RECIPIENT']['PROPERTY_SPRAV_VALUE'] == "Y" && $Res['SENDER']['PROPERTY_SPRAV_VALUE'] != "Y"))
        {
            $this->coeff = self::C_2;
        }elseif( ($Res['RECIPIENT']['PROPERTY_SPRAV_VALUE'] != "Y" && $Res['SENDER']['PROPERTY_SPRAV_VALUE'] != "Y")){
            $this->coeff = self::C_3;
        }
        if(!empty($Res['SENDER']['PROPERTY_ZONE_VALUE'])){
            $zSend = (int)$Res['SENDER']['PROPERTY_ZONE_VALUE'];
        }else{
            $Res['ERROR']['ERR_RASCHET'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_RASCHET');
            //echo "обработать ошибку - значение зона отправителя пусто<br>";
        }
        if(!empty($Res['RECIPIENT']['PROPERTY_ZONE_VALUE'])){
            $zRep = (int)$Res['RECIPIENT']['PROPERTY_ZONE_VALUE'];
        }else{
            $Res['ERROR']['ERR_RASCHET'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_RASCHET');
            //echo "обработать ошибку - значение зона получателя пусто<br>";
        }
        if($zSend > 0 && $zRep > 0){
            $code_recipient_zone = "zone-".(string)$zRep;
            $zone = static::GetInfoArr( $code_recipient_zone,false, 101, $this->arSelect);
            if((int)$zone['PROPERTIES']['ZONE_DISPATCH_'.(string)$zRep]['VALUE'] > 0){
                $zoneDelivery = $zone['PROPERTIES']['ZONE_DISPATCH_'.(string)$zSend]['VALUE'];
                $Res['ZONEDEV'] = $zoneDelivery;
            }
            else
            {
                $Res['ERROR']['ERR_RASCHET'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_RASCHET');
                //echo "обработать ошибку - значение зона расчета пусто<br>";
            }
            if((int)$zoneDelivery>0){
                //dump($zoneDelivery);
                $time = static::GetInfoArr( false,49531756, 102, $this->arSelect);
                if((int)$time['PROPERTIES']['DELIVERY_TERMS_'.(string)$zoneDelivery]['VALUE']){
                    $timeDelivery = $time['PROPERTIES']['DELIVERY_TERMS_'.(string)$zoneDelivery]['VALUE'];
                    // основной - не основной
                    if($this->coeff === self::C_2) {
                        $Res['TIMEDEV'] = \Bitrix\Main\Localization\Loc::GetMessage('NON_TIME');
                    }else{
                        $Res['TIMEDEV'] = $timeDelivery;
                    }

                }
                else
                {
                    $Res['ERROR']['ERR_RASCHET'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_RASCHET');
                    //echo "обработать ошибку - сроки доставки пусто<br>";
                }
            }
            else
            {
                $Res['ERROR']['ERR_RASCHET'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_RASCHET');
                //echo "обработать ошибку - зона расчетная пусто<br>";
            }
        }
        else
        {
            $Res['ERROR']['ERR_RASCHET'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_RASCHET');
            //echo "обработать ошибку зона получателя или зона отправителя вернули 0<br>";
        }
        return $this->coeff;
    }
    /* москва - область, МО-МО */
    /**
     * @param array $Res
     *
     * @param bool $flag
     * @return mixed
     */
    function GetMskObl(array &$Res, $flag = false){
        /* получить расстояние до мкад отправителя и получателя внутри области */
        $dist_mkad_sender_arr = static::GetInfoArr( false,$Res['SENDER']['ID'], 6, $this->arSelect);
        $dist_mkad_recipient_arr =  static::GetInfoArr( false,$Res['RECIPIENT']['ID'], 6,  $this->arSelect);
        $dist_mkad_sender = (int)$dist_mkad_sender_arr['PROPERTIES']['OUT_MKAD']['VALUE'];
        $dist_mkad_recipient = (int) $dist_mkad_recipient_arr['PROPERTIES']['OUT_MKAD']['VALUE'];

        if(!empty($dist_mkad_sender_arr['PROPERTIES']['OUT_MKAD']['VALUE'])){
            $dist_mkad = $dist_mkad_sender_arr['PROPERTIES']['OUT_MKAD']['VALUE'];
        }
        if(!empty($dist_mkad_recipient_arr['PROPERTIES']['OUT_MKAD']['VALUE'])){
            $dist_mkad = $dist_mkad_recipient_arr['PROPERTIES']['OUT_MKAD']['VALUE'];
        }
        // static::AddToLogs("testCalc",  [$dist_mkad_sender]);
        // static::AddToLogs("testCalc",  [$dist_mkad_recipient]);

        //echo " Расстояние от МКАД Отправитель $dist_mkad_sender км.<br>";
        //echo " Расстояние от МКАД Получатель $dist_mkad_recipient км.<br>";
        $Res['SENDER']["INTERVAL"] =  [" Расстояние от МКАД Отправитель $dist_mkad_sender км.<br>", $dist_mkad_sender];
        $Res['RECIPIENT']["INTERVAL"] = [" Расстояние от МКАД Получатель $dist_mkad_recipient км.<br>", $dist_mkad_recipient];
        $tr = [];
        if($dist_mkad_sender>0 && $dist_mkad_recipient>0 && !$flag){
            /* область менее 50 км - область менее 50 км  id - 51213823 */
            if($dist_mkad_sender <= self::DEFAULT_DISTANCE && $dist_mkad_recipient <= self::DEFAULT_DISTANCE){
                $Res['ZONEDEV'] = \Bitrix\Main\Localization\Loc::GetMessage('MOS_OBL');
                $tr = static::GetInfoArr( false,51213823, 103,  $this->arSelect);
                //echo "Тариф до 0,5кг  $tarif_05 руб. <br>";
                //echo "Тариф от 0,5кг-1,0кг $tarif_1 руб. <br>";
                //echo "Плюс на каждый кг свыше 1кг $tarif_hight руб. <br>";
            }
            /* область менее 50 км - область более 50 км  id - 51213826 */
            if(($dist_mkad_sender <= self::DEFAULT_DISTANCE && $dist_mkad_recipient > self::DEFAULT_DISTANCE)||
                ($dist_mkad_sender > self::DEFAULT_DISTANCE && $dist_mkad_recipient <= self::DEFAULT_DISTANCE)){
                $tr = static::GetInfoArr( false,51213826, 103,  $this->arSelect);
                //echo "Тариф до 0,5кг $tarif_05 руб. <br>";
                // echo "Тариф от 0,5кг-1,0кг $tarif_1 руб. <br>";
                //echo "Плюс на каждый кг свыше 1кг $tarif_hight руб. <br>";
            }
            /* область более 50 км - область более 50 км  id - 51213827 */
            if($dist_mkad_sender > self::DEFAULT_DISTANCE && $dist_mkad_recipient > self::DEFAULT_DISTANCE){
                $tr = static::GetInfoArr( false,51213827, 103,  $this->arSelect);
                //echo "Тариф до 0,5кг $tarif_05 руб. <br>";
                //echo "Тариф от 0,5кг-1,0кг $tarif_1 руб. <br>";
                //echo "Плюс на каждый кг свыше 1кг $tarif_hight руб. <br>";
            }
        }
        elseif((($Res['SENDER']['ID'] == 8054) && $dist_mkad_recipient>0)||
            (($Res['RECIPIENT']['ID'] == 8054) && $dist_mkad_sender>0)){
            /* Москва - область более 50 км  id - 51213822 */
            if(($dist_mkad_recipient > self::DEFAULT_DISTANCE && $Res['SENDER']['ID'] == 8054)||
                ($dist_mkad_sender > self::DEFAULT_DISTANCE && $Res['RECIPIENT']['ID'] == 8054)){
                $tr = static::GetInfoArr( false,51213822, 103,  $this->arSelect);
            }
            /* Москва - область менее или равно 50 км  id - 51213813 */
            if(($dist_mkad_recipient <= self::DEFAULT_DISTANCE && $Res['SENDER']['ID'] == 8054)||
                ($dist_mkad_sender <= self::DEFAULT_DISTANCE && $Res['RECIPIENT']['ID'] == 8054)){
                $tr = static::GetInfoArr( false,51213813, 103,  $this->arSelect);
            }
        }
        elseif($flag){
            $tr = [];
            /* Москва - область более 50 км  id - 51213822 */
            if(($dist_mkad > self::DEFAULT_DISTANCE)){
                $tr = static::GetInfoArr( false,51213822, 103, $this->arSelect);
            }
            /* Москва - область менее или равно 50 км  id - 51213813 */
            if($dist_mkad <= self::DEFAULT_DISTANCE){
                $tr = static::GetInfoArr( false,51213813, 103, $this->arSelect);
            }
            //  static::AddToLogs("testCalc", $tr);
        }
        else{
            $Res['ERROR']['ERR_RASCHET'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_RASCHET');
            //echo "обработать ошибку - нет расстояния от мкад<br>";
        }
        $Res['TIMEDEV']  = $tr['PROPERTIES']['TIME_DELIVERY']['VALUE'];
        $tarif_05 = $tr['PROPERTIES']['WEIGHT_05']['VALUE'];
        $tarif_1 = $tr['PROPERTIES']['WEIGHT_1']['VALUE'];
        $tarif_hight = $tr['PROPERTIES']['WEIGHT_HIGHER']['VALUE'];
        return [$tarif_05, $tarif_1, $tarif_hight];
    }

    /**
     * @param array $Res
     *
     * @return mixed
     */
    function GetLenOblNew(array $Res){
        /* 110  52154989 */
        if($Res['RECIPIENT']['IBLOCK_SECTION_ID'] == 656){
            $id_lo = $Res['RECIPIENT']['ID'];
        }elseif($Res['SENDER']['IBLOCK_SECTION_ID'] == 656){
            $id_lo = $Res['SENDER']['ID'];
        }
        $tr = static::GetInfoArr( false, $id_lo, 6, $this->arSelect);

        $req = static::GetInfoArr( false,
            52297979, 110, $this->arSelect);

        $weight = (float)$Res['FULLWEIGTH'];
        if($tr['PROPERTIES']['OUT_MKAD']['VALUE'] < self::DEFAULT_DISTANCE) {
            $timedev = $req['PROPERTIES']['MO_TIME_1']['VALUE'];
            $tar = (int)$req['PROPERTIES']['MO_TARIF_1']['VALUE'];
        }elseif($tr['PROPERTIES']['OUT_MKAD']['VALUE'] >= self::DEFAULT_DISTANCE){
            $tar = (int)$req['PROPERTIES']['MO_TARIF_2']['VALUE'];
            $timedev = $req['PROPERTIES']['MO_TIME_2']['VALUE'];
        }
        if($weight <= 1.0){
            $tarif = $tar;
        }elseif($weight>1.0){
            $tarif_hight = (int)$req['PROPERTIES']['MO_TARIF_SPREAD_1']['VALUE'];
            $w_hi = $weight - 1.00;
            $tarif = (float)($tar + (ceil($w_hi)*$tarif_hight));
        }
        //  static::AddToLogs("testCalс", ["MESS"=>$tr['PROPERTIES']['OUT_MKAD']]);
        return [$timedev, $tarif];

    }

    /**
     * @param array $Res
     *
     * @return mixed
     */
    function GetMskOblNew(array $Res) {
        /* 110  52154989 */
        if($Res['RECIPIENT']['IBLOCK_SECTION_ID'] == 641){
            $id_mo = $Res['RECIPIENT']['ID'];
        }elseif($Res['SENDER']['IBLOCK_SECTION_ID'] == 641){
            $id_mo = $Res['SENDER']['ID'];
        }
        $tr = static::GetInfoArr( false, $id_mo, 6,  $this->arSelect);
        if($tr['PROPERTIES']['SMALL_DEV']['VALUE']=='Y'){
            $tarif = 0;
            return $tarif;
        }else{
            $req = static::GetInfoArr( false, 52154989, 110,  $this->arSelect);

            $weight = (float)$Res['FULLWEIGTH'];
            if($tr['PROPERTIES']['OUT_MKAD']['VALUE'] < self::DEFAULT_DISTANCE) {
                $timedev = $req['PROPERTIES']['MO_TIME_1']['VALUE'];
                $tar = (int)$req['PROPERTIES']['MO_TARIF_1']['VALUE'];
            }elseif($tr['PROPERTIES']['OUT_MKAD']['VALUE'] >= self::DEFAULT_DISTANCE){
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
            //  static::AddToLogs("testCalс", ["MESS"=>$tr['PROPERTIES']['OUT_MKAD']]);
            return [$timedev, $tarif];
        }
    }

    /**
     * @param $Res
     * @param $tarif_1
     * @param $tarif_hight
     * @return mixed
     */
    function GetTarifSPB(&$Res, $tarif_1, $tarif_hight){
        $tarif = 0.00;
        if($Res['FULLWEIGTH']<=1.00){
            $tarif = $tarif_1;
        }elseif($Res['FULLWEIGTH']>1.00){
            $w_hi = (float)$Res['FULLWEIGTH'] - 1.00;
            $tarif = (float)($tarif_1 + (ceil($w_hi)*$tarif_hight));
        }

        return $tarif;
    }

    /**
     * @param $Res
     * @param bool $f
     * @return mixed
     */
    function GetTarifSPBSpbOBL(&$Res, $f=true ){
        $id_obl = 0;
        $weight = (float)$Res['FULLWEIGTH'];
        if($f){
            if($Res['SENDER']["ID"] != 8678){
                $id_obl = $Res['SENDER']["ID"];
            }elseif($Res['RECIPIENT']["ID"] != 8678){
                $id_obl = $Res['RECIPIENT']["ID"];
            }else{

                $Res['ERROR']['ERR_RASCHET'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_RASCHET');
            }
        }else{
            if($Res['SENDER']["IBLOCK_SECTION_ID"] == 656){
                $id_obl = $Res['SENDER']["ID"];
            }elseif($Res['RECIPIENT']["IBLOCK_SECTION_ID"] == 656){
                $id_obl = $Res['RECIPIENT']["ID"];
            }else{

                $Res['ERROR']['ERR_RASCHET'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_RASCHET');
            }
        }
        // static::AddToLogs("testCalс", ["MESS"=>" Питер - область ", "ID obl"=>$id_obl]);
        $arrDist = static::GetInfoArr(false, $id_obl, 6,  $this->arSelect);
        $distance = 0;
        if($arrDist['PROPERTIES']['OUT_MKAD']['VALUE']){
            $distance =  $arrDist['PROPERTIES']['OUT_MKAD']['VALUE'];
        }else{
            $Res['ERROR']['ERR_RASCHET'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_RASCHET');
        }
        if($distance <= self::DEFAULT_DISTANCE){
            $tr = static::GetInfoArr( false,52650410, 112,  $this->arSelect);
        }elseif($distance > self::DEFAULT_DISTANCE){
            $tr = static::GetInfoArr( false,52650411, 112,  $this->arSelect);
        }
        $Res['TIMEDEV']  = $tr['PROPERTIES']['TIME_DELIVERY']['VALUE'];
        if($weight <= 1.00 && $weight > 0.00){
            $tarif = $tr['PROPERTIES']['TARIF_1']['VALUE'];
        }elseif($weight>1.00){
            $uplimit = (float) $weight - 1.00;
            $uplimit = ceil($uplimit);
            $tarif = $tr['PROPERTIES']['TARIF_1']['VALUE']+$tr['PROPERTIES']['TARIF_HIGHT']['VALUE']*$uplimit;
        }
        $Res['TARIF_ITOG'] = (float) $tarif;
        return $tarif;
    }

    /**
     * @param $Res
     * @param bool $f
     * @return mixed
     */
    function GetTarifMSKSpbOBL(&$Res, $f=true ){
        $id_obl = 0;
        $weight = (float)$Res['FULLWEIGTH'];
        if($f){
            if($Res['SENDER']["ID"] != 8054){
                $id_obl = $Res['SENDER']["ID"];
            }elseif($Res['RECIPIENT']["ID"] != 8054){
                $id_obl = $Res['RECIPIENT']["ID"];
            }else{

                $Res['ERROR']['ERR_RASCHET'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_RASCHET');
            }
        }else{
            if($Res['SENDER']["IBLOCK_SECTION_ID"] == 656){
                $id_obl = $Res['SENDER']["ID"];
            }elseif($Res['RECIPIENT']["IBLOCK_SECTION_ID"] == 656){
                $id_obl = $Res['RECIPIENT']["ID"];
            }else{

                $Res['ERROR']['ERR_RASCHET'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_RASCHET');
            }
        }
        // static::AddToLogs("testCalс", ["MESS"=>" Питер - область ", "ID obl"=>$id_obl]);
        $arrDist = static::GetInfoArr(false, $id_obl, 6,  $this->arSelect);
        $distance = 0;
        if($arrDist['PROPERTIES']['OUT_MKAD']['VALUE']){
            $distance =  $arrDist['PROPERTIES']['OUT_MKAD']['VALUE'];
        }else{
            $Res['ERROR']['ERR_RASCHET'] = \Bitrix\Main\Localization\Loc::GetMessage('ERR_RASCHET');
        }
        if($distance <= self::DEFAULT_DISTANCE){
            $tr = static::GetInfoArr( false,51897804, 107,  $this->arSelect);
        }elseif($distance > self::DEFAULT_DISTANCE){
            $tr = static::GetInfoArr( false,51897805, 107,  $this->arSelect);
        }
        $Res['TIMEDEV']  = $tr['PROPERTIES']['TIME_DELIVERY']['VALUE'];
        if($weight<=1.00 && $weight>0.00){
            $tarif = $tr['PROPERTIES']['TARIF_1']['VALUE'];
        }elseif($weight>1.00){
            $uplimit = (float) $weight - 1.00;
            $uplimit = ceil($uplimit);
            $tarif = $tr['PROPERTIES']['TARIF_1']['VALUE']+$tr['PROPERTIES']['TARIF_HIGHT']['VALUE']*$uplimit;
        }
        $Res['TARIF_ITOG'] = (float) $tarif;
        return $tarif;

    }

    /**
     * @param $Res
     *
     * @return mixed
     */
    function GetTarifNoMskObl(&$Res ){
        if( $Res['FULLWEIGTH'] <= 0.50 &&  $Res['FULLWEIGTH'] > 0){
            $tarif =  static::GetInfoArr( false,49528186, 100,  $this->arSelect);
        }elseif( $Res['FULLWEIGTH'] >0.5 &&  $Res['FULLWEIGTH'] <= 1.00){
            $tarif =  static::GetInfoArr( false,49528187, 100,  $this->arSelect);
        }
        elseif( $Res['FULLWEIGTH'] > 1.00 &&  $Res['FULLWEIGTH'] <= 2.00){
            $tarif =  static::GetInfoArr( false,49528190, 100,  $this->arSelect);
        }
        elseif( $Res['FULLWEIGTH'] > 2.00 &&  $Res['FULLWEIGTH'] <= 3.00){
            $tarif =  static::GetInfoArr( false,49528223, 100,  $this->arSelect);
        }
        elseif( $Res['FULLWEIGTH'] > 3.00 &&  $Res['FULLWEIGTH'] <= 4.00){
            $tarif =  static::GetInfoArr( false,49528230, 100,  $this->arSelect);
        }
        elseif( $Res['FULLWEIGTH'] > 4.00){

            $uplimit = (float) $Res['FULLWEIGTH'] - 4.00;
            $uplimit = ceil($uplimit);
            $tarif4 =  static::GetInfoArr( false,49528230, 100,  $this->arSelect);
            // dump($tarif4);
            $tarif4 = $tarif4['PROPERTIES']['ZONE_'.$Res['ZONEDEV']]['VALUE'];
            $tarif =  static::GetInfoArr( false,49528419, 100,  $this->arSelect);
            // dump($tarif);
        }
        $Res['TARIF'] =  $tarif;
        if($Res['FULLWEIGTH']<=4.00){
            $tarif = (float)$Res['TARIF']['PROPERTIES']['ZONE_'.$Res['ZONEDEV']]['VALUE']*(float)$Res['RASCH_COEFF'];
        }else{
            $tarif = ((float)$tarif4+(float)$Res['TARIF']['PROPERTIES']['ZONE_'.$Res['ZONEDEV']]['VALUE']*$uplimit)*(float)$Res['RASCH_COEFF'];
        }
        $Res['TARIF_ITOG'] = (float) $tarif;
        return $tarif;
    }

    /**
     * @param $Res
     * @param $dist_mkad
     * @param bool $ext
     * @return mixed
     */
    function GetTarifMskOblSPB(&$Res, $dist_mkad, $ext=false){
        $tr = [];
        $weight = (float)$Res['FULLWEIGTH'];
        /* МО более 50 км  id - 52439704 */
        if(!$ext){
            if(($dist_mkad > self::DEFAULT_DISTANCE)){
                $tr = static::GetInfoArr( false,52439704, 111,  $this->arSelect);
            }
            /* МО менее 50 км  id - 52439702 */
            if($dist_mkad < self::DEFAULT_DISTANCE){
                $tr = static::GetInfoArr( false,52439702, 111,  $this->arSelect);
            }
            $base_t = (float)$tr['PROPERTIES']['BASE_T']['VALUE'];
            $base_n = (float)$tr['PROPERTIES']['BASE_N']['VALUE'];
            $base_up = (float)$tr['PROPERTIES']['BASE_UP']['VALUE'];
            $tar = $base_t + $base_n;
        } else {
            $tr = static::GetInfoArr( false,52439702, 111,  $this->arSelect);
            $base_up = (float)$tr['PROPERTIES']['BASE_UP']['VALUE'];
            $tar = (float)$tr['PROPERTIES']['BASE_T']['VALUE'];
        }
        if($weight <= 1.0){
            $tarif = $tar;
        }elseif($weight > 1.0){
            $w_hi = $weight - 1.00;
            $tarif = (float)( $tar + (ceil($w_hi)* $base_up));
        }
        $Res['TARIF_ITOG'] =  $tarif;
        return $tarif;
    }

    /**
     * @param $Res
     * @param $dist_mkad
     * @return mixed
     */
    function GetTarifMskOblDev(&$Res, $dist_mkad){
        $tr = [];
        /* Москва - область более 50 км  id - 51213822 */
        if(($dist_mkad > self::DEFAULT_DISTANCE)){
            $tr = static::GetInfoArr( false,51213822, 103,  $this->arSelect);
        }
        /* Москва - область менее 50 км  id - 51213813 */
        if($dist_mkad < self::DEFAULT_DISTANCE){
            $tr = static::GetInfoArr( false,51213813, 103,  $this->arSelect);
        }
        $time_dev_msk  = $tr['PROPERTIES']['TIME_DELIVERY']['VALUE'];
        $tarif_05_msk = $tr['PROPERTIES']['WEIGHT_05']['VALUE'];
        $tarif_1_msk = $tr['PROPERTIES']['WEIGHT_1']['VALUE'];
        $tarif_hight_msk = $tr['PROPERTIES']['WEIGHT_HIGHER']['VALUE'];
        $this->GetTarifMskObl($Res, $tarif_05_msk, $tarif_1_msk, $tarif_hight_msk);
        return $time_dev_msk;
    }

    /**
     * @param $Res
     * @param bool $flag
     * @return mixed
     */
    function GetMainCity($Res, $flag = false){
        if(!$flag){
            $req = static::GetInfoArr( false,$Res['SENDER']['ID'], 6,  $this->arSelect);
            if($req['PROPERTIES']['SPRAV']['VALUE'] === 'Y'){
                return true;
            }else{
                return false;
            }
        } else{
            $req_send = static::GetInfoArr( false,$Res['SENDER']['ID'], 6,  $this->arSelect);
            $req_rec = static::GetInfoArr( false,$Res['RECIPIENT']['ID'], 6,  $this->arSelect);
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

    /**
     * @param $Res
     * @param $tarif_05
     * @param $tarif_1
     * @param $tarif_hight
     * @return mixed
     */
    function GetTarifMskObl(&$Res, $tarif_05, $tarif_1, $tarif_hight){
        if($Res['FULLWEIGTH'] <= 0.50)
        {
            $tarif = $tarif_05;
        }elseif($Res['FULLWEIGTH'] > 0.50 && $Res['FULLWEIGTH'] <= 1.00)
        {
            $tarif = $tarif_1;
        }elseif($Res['FULLWEIGTH'] > 1.00){
            $w_hi = (float)$Res['FULLWEIGTH'] - 1.00;
            $tarif = (float)($tarif_1 + (ceil($w_hi)*$tarif_hight));
        }
        $Res['TARIF_ITOG'] =  $tarif;
        return $tarif;
    }

    /**
     * @param $Res
     * @return mixed
     */
    function GetTarifMskPt(&$Res){
        $tr = static::GetInfoArr( false, 51897803, 107,  $this->arSelect);
        $Res['TIMEDEV']  = $tr['PROPERTIES']['TIME_DELIVERY']['VALUE'];
        $tarif_1 = (float)$tr['PROPERTIES']['TARIF_1']['VALUE'];
        $tarif_hight = (float)$tr['PROPERTIES']['TARIF_HIGHT']['VALUE'];
        $itogTarif = $this->GetTarifSPB($Res, $tarif_1, $tarif_hight );
        $Res['TARIF_ITOG'] = $itogTarif;
        $tr['TARIF_ITOG'] = $itogTarif;
        // static::AddToLogs("testCalс", $Res);
        return $tr;
    }

    /**
     * @param $Res
     * @return mixed
     */
    function GetTarifMsk(&$Res){
        $weight = (float)$Res['FULLWEIGTH'];
        $Res['STANDART'] = static::GetInfoArr(false, 51964190, 108,  $this->arSelect);
        $Res['EXPRESS_8'] = static::GetInfoArr(false, 51964682, 109,  $this->arSelect);
        $Res['EXPRESS_4'] = static::GetInfoArr(false, 51964677, 109,  $this->arSelect);
        $Res['EXPRESS_2'] = static::GetInfoArr(false, 51964676, 109,  $this->arSelect);
        return true;
    }

    /**
     * @param $time_1
     * @param $time_2
     * @return mixed
     */
    function sumTimeDev($time_1, $time_2){
        if(preg_match('/^([0-9]{1,2}-[0-9]{1,2})$/', $time_1) &&
            preg_match('/^([0-9]{1,2}-[0-9]{1,2})$/', $time_2)){
            $arr1 = explode('-', $time_1);
            $vrem1 = $arr1[0];
            $vrem2 = $arr1[1];
            $arr2 = explode('-', $time_2);
            $vrem3 = $arr2[0];
            $vrem4 = $arr2[1];
            $timeit = ( $vrem1 +  $vrem3) . '-' . ($vrem2 + $vrem4);

        }
        if(!preg_match('/^([0-9]{1,2}-[0-9]{1,2})$/', $time_1) &&
            !preg_match('/^([0-9]{1,2}-[0-9]{1,2})$/', $time_2)){

            $timeit = (int)$time_1+(int)$time_2;

        }
        if(!preg_match('/^([0-9]{1,2}-[0-9]{1,2})$/', $time_1) &&  /* 2 */
            preg_match('/^([0-9]{1,2}-[0-9]{1,2})$/', $time_2)){   /* 3-4 */
            $vrem1 = $time_1;
            $arr1 = explode('-',$time_2);
            $vrem2 = $arr1[0];
            $vrem3 = $arr1[1];
            $timeit = ( $vrem1 +  $vrem2) . '-' . ($vrem3 + $vrem1);

        }
        if(preg_match('/^([0-9]{1,2}-[0-9]{1,2})$/', $time_1) &&
            !preg_match('/^([0-9]{1,2}-[0-9]{1,2})$/', $time_2)){
            $arr1 = explode('-', $time_1);
            $vrem1 = $arr1[0];
            $vrem2 = $arr1[1];
            $vrem4 = $time_2;
            $timeit = ( $vrem1 +  $vrem4) . '-' . ($vrem2 + $vrem4);
        }
        return $timeit;
    }




}