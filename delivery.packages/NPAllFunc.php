<?php
/**
 * Class AllFunc
 */
abstract class NPAllFunc
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

    /**
     * только для Нового партнера!
     * @return SoapClient|string
     * @throws SoapFault
     */
    static public function soap_inc()
    {
        $id_uk = 2378056;

        $res = CIBlockElement::GetList(array(), array("IBLOCK_ID" => 47, "ID" => $id_uk),
            false, false, array("PROPERTY_683", "PROPERTY_704", "PROPERTY_705", "PROPERTY_706"));
        if ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $currentip = $arFields['PROPERTY_683_VALUE'];
            $currentlink = $arFields['PROPERTY_704_VALUE'];
            $login1c = $arFields['PROPERTY_705_VALUE'];
            $pass1c = $arFields['PROPERTY_706_VALUE'];
            if ((trim($currentip) !== '') && (trim($currentlink) !== '') && (trim($login1c) !== '') &&
                (trim($pass1c) !== '')) {
                $url = "http://" . $currentip . $currentlink;
                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => $url,
                    CURLOPT_HEADER => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_NOBODY => true,
                    CURLOPT_TIMEOUT => 10
                ]);
                $header = explode("\n", curl_exec($curl));
                curl_close($curl);
                if (trim($header[0]) !== ''){
                    $clientw = new SoapClient($url, array("login" => $login1c, "password" => $pass1c,
                        "exceptions" => false));
                    return $clientw;
                }else{

                    return "Нет соединения";
                }
            }
        }

    }

    /**
     * @param $company
     * @return array|bool
     */
    static public function GetCompany($company)
    {
        $arFields = [];
        $filter = ["IBLOCK_ID" => 40, "ID" => $company];
        $res = CIBlockElement::GetList(
            ["NAME"=>"ASC"],
            $filter,
            false,
            false,
            ["ID", "NAME", "ACTIVE", "PROPERTY_CITY", "PROPERTY_ADRESS", "PROPERTY_PERCENT", "PROPERTY_INN", "PROPERTY_CITIES",
                "PROPERTY_ACCOUNT", "PROPERTY_EMAIL", "PROPERTY_MAIL_SETTINGS", "PROPERTY_CITE", "PROPERTY_PHONES", "PROPERTY_DEFAULT_CITY",
                "PROPERTY_DEFAULT_DELIVERY", "PROPERTY_DEFAULT_CASH", "PROPERTY_ID_IN", "PROPERTY_LEGAL_NAME", "PROPERTY_CONTRACT",
                "PROPERTY_ACTING", "PROPERTY_CITY.NAME", "PROPERTY_PREFIX", "PROPERTY_FOLDER", "PROPERTY_PREFIX_REPORTS",
                "PROPERTY_RESPONSIBLE_PERSON", "PROPERTY_LEGAL_NAME_FULL", "PROPERTY_COST_ORDERING", "PROPERTY_TYPE",
                "PROPERTY_UK", "PROPERTY_ON_PAGE", "PROPERTY_TYPE_IM", "PROPERTY_RESPONSIBLE_PERSON_IN", "PROPERTY_REPORT_SIGNS",
                'PROPERTY_TARIFF_TD', 'PROPERTY_CODE_1C', 'PROPERTY_COEFFICIENT_VW', "PROPERTY_TYPE_WORK_BRANCHES", "PROPERTY_SHOW_LIMITS",
                "PROPERTY_BY_AGENT", "PROPERTY_INN_REAL", "PROPERTY_ACCOUNT_LK_SETTINGS", "PROPERTY_BY_AGENT.NAME",
                'PROPERTY_AVAILABLE_WH_WH', 'PROPERTY_AVAILABLE_CALL_COURIER', 'PROPERTY_LEGAL_NAME_NDS',
                'PROPERTY_LEGAL_NAME_FULL_NDS', 'PROPERTY_ACTING_NDS', 'PROPERTY_RESPONSIBLE_PERSON_IN_NDS', 'PROPERTY_REPORT_SIGNS_NDS',
                "PROPERTY_AVAILABLE_EXPRESS2",
                "PROPERTY_AVAILABLE_EXPRESS4",
                "PROPERTY_AVAILABLE_EXPRESS8",
                "PROPERTY_AVAILABLE_EXPRESS",
                "PROPERTY_AVAILABLE_STANDART",
                "PROPERTY_AVAILABLE_ECONOME",
                "PROPERTY_SHOW_HIDDEN_INNER_NUMBER"
            ]
        );
        if($ob = $res->GetNextElement())
        {
            $a = $ob->GetFields();
            $a['PROPERTY_CITY'] = self::GetFullNameOfCity($a['PROPERTY_CITY_VALUE']);
            $a['PROPERTY_DEFAULT_CITY'] = self::GetFullNameOfCity($a['PROPERTY_DEFAULT_CITY_VALUE']);
            $db_props = CIBlockElement::GetProperty(40, $a["PROPERTY_UK_VALUE"], array("sort" => "asc"),
                ["CODE"=>"LEGAL_NAME"]);
            if($ar_props = $db_props->Fetch())
            {
                $a["PROPERTY_UK_NAME"] =  $ar_props["VALUE"];
            }
            else
            {
                $a["PROPERTY_UK_NAME"] = "ООО «МСД»";
            }
            $db_props = CIBlockElement::GetProperty(40, $a["PROPERTY_UK_VALUE"], ["sort" => "asc"], ["CODE" => "CITY"]);
            if($ar_props = $db_props->Fetch())
            {
                $a["PROPERTY_UK_CITY"] =  self::GetFullNameOfCity($ar_props["VALUE"]);

            }
            $arFields = $a;
        }
        else
        {
            return false;
        }
        return $arFields;
    }


    /**
     * @param $city_id
     * @param bool $onlyname
     * @param bool $returnarr
     * @return array|bool|string
     */
    static public function GetFullNameOfCity($city_id, $onlyname = false, $returnarr = false)
    {
        if ((int)$city_id > 0)
        {
            $name_of_city = false;
            $arSelect = Array("ID","NAME","IBLOCK_SECTION_ID");
            $arFilter = Array("IBLOCK_ID" => 6, "ID" => $city_id);
            $res = CIBlockElement::GetList(Array("NAME"=>"asc"), $arFilter, false, false, $arSelect);
            if ($ob = $res->GetNextElement())
            {
                $a = $ob->GetFields();
                $res2 = CIBlockSection::GetByID($a["IBLOCK_SECTION_ID"]);
                if($ar_res2 = $res2->GetNext())
                {
                    $a["S_1"] = $ar_res2["NAME"];
                    if (intval($ar_res2["IBLOCK_SECTION_ID"]) > 0)
                    {
                        $res3 = CIBlockSection::GetByID(intval($ar_res2["IBLOCK_SECTION_ID"]));
                        if($ar_res3 = $res3->GetNext())
                        {
                            $a["S_2"] = $ar_res3["NAME"];
                        }
                    }
                }
                $name_of_city = $a["NAME"].', '.$a["S_1"].', '.$a["S_2"];
            }
            if ($returnarr)
            {
                return array($a["NAME"], $a["S_1"], $a["S_2"]);
            }
            else
            {
                return ($onlyname) ? $a["NAME"] : $name_of_city;
            }
        }
        else
        {
            return '';
        }
    }

    /**
     * @param $val_id
     * @param int $uk_sets_id
     * @param bool $uk_id
     * @return bool|mixed
     */
    static public function GetSettingValue($val_id, $uk_sets_id = 2378056, $uk_id = false)
    {
        $sets_id = $uk_sets_id;
        if ($uk_id)
        {
            $db_props = CIBlockElement::GetProperty(40, $uk_id, ["sort" => "asc"], ["ID" => 474]);
            if ($ar_props = $db_props->Fetch())
            {
                $sets_id = $ar_props["VALUE"];
            }
        }
        $db_props = CIBlockElement::GetProperty(47, $sets_id, ["sort" => "asc"], ["ID" => $val_id]);
        if ($ar_props = $db_props->Fetch())
        {
            if ($ar_props['PROPERTY_TYPE'] == 'L')
            {
                $rate = $ar_props["VALUE_ENUM"];
            }
            else
            {
                $rate = $ar_props["VALUE"];
            }
        }
        else
        {
            $rate = false;
        }
        return $rate;
    }

}