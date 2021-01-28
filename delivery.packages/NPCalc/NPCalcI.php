<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
    die();
}

/**
 * Interface NPCalcI
 */
interface NPCalcI
{
    const DEFAULT_ZONE_REGION = 6;
    const DEFAULT_DEV_REGION = '3-5';  /* Основные города */
    const DEFAULT_DEV_C = '3-5';    /* Московская область - CПБ */
    const DEFAULT_DEV_MSO = '4-6';    /* Московская область - ЛО */
    const DEFAULT_DEV_CITY = 1;
    const DEFAULT_DISTANCE = 50;
    const C_1 = 1.0;
    const C_2 = 1.8;
    const C_3 = 2.5;
    const TMain = 300;
    const THMain = 30;
    const STANDART = [0.00, 1.00, 19.00, 49.00, 69.00, 99.00];
    const EXPRESS = [0.00, 1.00];


    /**
     * @param array $arResult
     * @return mixed
     */
    public function GetMsk(array &$Res);

    /**
     * @param array $arResult
     *
     * @return mixed
     */
    public function GetRegion(array &$Res);

    /**
     * @param array $arResult
     *
     * @param bool $flag
     * @return mixed
     */
    public  function GetMskObl(array &$Res, $flag = false);

    /**
     * @param array $arResult
     *
     * @return mixed
     */
    public  function GetLenOblNew(array $Res);

    /**
     * @param array $arResult
     *
     * @return mixed
     */
    public function GetMskOblNew(array $Res);

    /**
     * @param $arResult
     * @param $tarif_1
     * @param $tarif_hight
     * @return mixed
     */
    public function GetTarifSPB(&$Res, $tarif_1, $tarif_hight);

    /**
     * @param $arResult
     *
     * @param bool $f
     * @return mixed
     */
    public function GetTarifSPBSpbOBL(&$Res, $f=true );

    /**
     * @param $arResult
     *
     * @param bool $f
     * @return mixed
     */
    public function GetTarifMSKSpbOBL(&$Res, $f=true );

    /**
     * @param $arResult
     *
     * @return mixed
     */
    public function GetTarifNoMskObl(&$Res );

    /**
     * @param $arResult
     * @param $dist_mkad
     *
     * @param bool $ext
     * @return mixed
     */
    public function GetTarifMskOblSPB(&$Res, $dist_mkad,  $ext=false);

    /**
     * @param $arResult
     * @param $dist_mkad
     *
     * @return mixed
     */
    public function GetTarifMskOblDev(&$Res, $dist_mkad);

    /**
     * @param $arResult
     *
     * @param bool $flag
     * @return mixed
     */
    public function GetMainCity($Res, $flag=false);

    /**
     * @param $arResult
     * @param $tarif_05
     * @param $tarif_1
     * @param $tarif_hight
     * @return mixed
     */
    public function GetTarifMskObl(&$Res, $tarif_05, $tarif_1, $tarif_hight);

    /**
     * @param $arResult
     *
     * @return mixed
     */
    public function GetTarifMskPt(&$Res);

    /**
     * @param $arResult
     *
     * @return mixed
     */
    public function GetTarifMsk(&$Res);

    /**
     * @param $time_1
     * @param $time_2
     * @return mixed
     */
    public function sumTimeDev($time_1, $time_2);

    /*
     *  получить $Res
     *
     */
    public function GetResult();

    public function routCalc();

}