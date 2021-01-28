<?$_SERVER[DOCUMENT_ROOT] = "/var/www/admin/www/delivery-russia.ru";
//require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");


error_reporting(0);


function getCourierNakl($courierId=0,$uk=0,$ninn=0,$currentip=0,$currentlink=0,$login1c=0,$pass1c=0,$tfrom=0,$tTo=0,$curType=0){

        if ((strlen(trim($currentip))) && (strlen(trim($currentlink))) && (strlen(trim($login1c))) && (strlen(trim($pass1c))))
            {
                $url = "http://".$currentip.$currentlink;
                $curl = curl_init();
                curl_setopt_array($curl, array(    
                CURLOPT_URL => $url,
                CURLOPT_HEADER => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_NOBODY => true,
                CURLOPT_TIMEOUT => 10));
                $header = explode("\n", curl_exec($curl));
                curl_close($curl);

//echo "<pre>";print_r($header);echo "</pre>";
                $newArr = array();
                $oldArr = array();
                $filterNak = array();
                $filterNakTmp = array();
                if (strlen(trim($header[0])))
                {
                    $client = new SoapClient(
                            $url,  
                            array("login" => $login1c, "password" => $pass1c, "exceptions" => false)
                        );

                    $arParamsJson = array(
                        "INN" => $ninn,
                        'StartDate' => $tfrom,
                        'EndDate' => $tTo,
                        "TypeManifest" => $curType
                    );
                    $result1 = $client->GetManifest($arParamsJson);

                    $mResult1 = $result1->return;
                    $obj1 = json_decode($mResult1, true);

                    for ($i = 1; $i <=count($obj1) ; $i++) {
                        $manKey = 'Manifest_'.$i;
                        //print_r($obj1[$manKey]);
                        $oldArr[NUMBER]=$obj1[$manKey][Number];
                        $oldArr[COUNT]=count($obj1[$manKey][DeliveryNotes]);
                        $oldArr[DeliveryNotes]=$obj1[$manKey][DeliveryNotes];
                
                        array_push($newArr, $oldArr);
                        
                    }
                    
                    foreach ($newArr as $key => $value) {                       
                        foreach ($value[DeliveryNotes] as $keynak => $valnak) {
                            $newNak[]=$valnak;
                        }                       
                    }

                //$filterNak = array();
                //$filterNakTmp = array();
                $arrayCount = count($newNak);

                for($inn = 0; $inn < $arrayCount; $inn++)
                {
                    if(empty($newNak[$inn][CURIER])){
                        UnSet($newNak[$inn]);
                    }
                }


                foreach ($newNak as $vn) {
                    if($vn['CURIER']==$courierId){
                        //$filterNakTmp['NAKNUMBER'] = $vn['DeliveryNote'];
                        //$filterNakTmp['CURIER'] = $vn['CURIER'];
                        $filterNakTmp['value'] = $vn['DeliveryNote'];
                        $filterNakTmp['text'] = $vn['DeliveryNote'];

                        array_push($filterNak, $filterNakTmp);
                    }
                }
            }
        }



        ////////
        //return json_encode($arNakl);
        return json_encode($filterNak);
        //return json_encode($url);
}
?>