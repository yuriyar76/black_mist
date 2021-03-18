<?php


/**
 * Class CalcI
 */
abstract class AllFunc
{
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
    static function convArrayToUTF(&$obj) {
     array_walk_recursive($obj, function(&$item){
       $item = iconv('windows-1251', 'utf-8', htmlspecialchars($item));
     });
        return $obj;
    }
    /**
     * @param string $folder
     * @param array $params
     * @param string $mainfolder
     * @return mixed
     */
    static public function AddToLogs($folder = '', $params = array(), $mainfolder = ''){
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
        $params_str = array();
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
    /**
     * @param $city
     * @param bool $onlyone
     * @return mixed
     */
    static public function GetCityId($city, $onlyone = false)
    {
        $from_arr = explode(',',$city);
        $city_name = trim(str_replace(' город', '', $from_arr[0]));

        if (empty($city_name)){
            return 0;
        }

        $city_section = [];
        if (isset($from_arr[1]))
        {

            $res_0 = CIBlockSection::GetList(["SORT"=>"ASC"], ["NAME"=>trim($from_arr[1]),
                "IBLOCK_ID"=>6],false);
            while($res_0_from = $res_0->GetNext())
            {
                $city_section[] = $res_0_from['ID'];
            }
        }

        $arSelect = ["ID"];
        $arFilter = ["IBLOCK_ID"=>6, "NAME"=>$city_name];
        if(count($city_section) > 0)
        {
            $arFilter["SECTION_ID"] = $city_section;
        }

        $res3 = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
        while($ob = $res3->GetNextElement())
        {
            $arFields[] = $ob->GetFields();
        }

        if ($onlyone && count($arFields) > 1)
            return 0;
        if(!empty(isset($arFields))){
            $city_id = (int)$arFields[0]['ID'];
        }else{
            $arSelect1 = ["ID"];
            $arFilter1 = ["IBLOCK_ID"=>6, "NAME"=>$city_name, "ACTIVE"=>"N"];
            $res4 = CIBlockElement::GetList([], $arFilter1, false, false, $arSelect1);
            while($ob = $res4->GetNextElement())
            {
                $arFields1[] = $ob->GetFields();
            }
            if(!empty(isset($arFields1))) {
                $city_id = (int)$arFields1[0]['ID'];
            }
        }
        return $city_id;

    }

    /**
     * @param int $id_company
     * @param int $def_coef
     * @return int
     */
    static public function WhatIsGabWeightCompany($id_company = 0, $def_coef = 5000)
    {
        $rate = $def_coef;
        if ((int)$id_company > 0)
        {
            $db_props = CIBlockElement::GetProperty(40, $id_company, ["sort" => "asc"], ["ID"=>681]);

            if($ar_props = $db_props->Fetch())
            {
                $rate = ((int)$ar_props["VALUE"] > 0) ? (int)$ar_props["VALUE"] : $def_coef;
            }
        }
        return $rate;
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
    static public function GetInfoArr($code = '', $id = '', $iblock_id = 0, $arSelect = [], $arFilter = [], $flag=true, $prop=true)
    {

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
     * @inheritDoc
     */
    public function WhatIsGabWeight()
    {
        // TODO: Implement WhatIsGabWeight() method.

        $db_props = CIBlockElement::GetProperty(47, 2378056, ["sort" => "asc"], ["ID"=>254]);
        if($ar_props = $db_props->Fetch())
        {
            $rate = $ar_props["VALUE"];
        }
        else
        {
            $rate = false;
        }
        return $rate;
    }

}