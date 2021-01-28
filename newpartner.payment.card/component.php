<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
//header('Content-Type: text/html; charset=utf-8');
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/black_mist/delivery.packages/functions.php");

AddToLogs('REQUESTS_SBER', ['get' => $_GET, 'data' => 'templates/newpartner-payment/header.php']);
$type_pay =  trim(htmlspecialcharsEx($_COOKIE['paycard_bank']));
/* обработка платежа */
if($_GET['status'] == 1){
    $data = [];
    $data_app = [];
    $stampfl = htmlspecialcharsEx($_COOKIE['pay_lk_fl']); /* метка если из лк физлиц */
    $delivery_payment = htmlspecialcharsEx($_COOKIE['pay_dp']); /* тип отправителя */
    $number_file = preg_replace('/^[^0-9]+-/','',$_GET['orderNumber']);
    $number_file = trim($number_file);
    $arrInfDocs = [];
    include $_SERVER['DOCUMENT_ROOT']."/docs/invoice/data_invoice_$number_file.php";
    $id_partner = 27122866;
    $arData =  $arrInfDocs;

    /* обработка оплаты банковской картой из формы Заказать услугу на главной */
    if(!empty($arrInfDocs) && $arrInfDocs[17] === 'AB')
    {
        foreach($arData as $key=>$value){
            $arData[$key] = trim(htmlspecialcharsEx($value));
        }



        $MAIL_PAYER = $arData[2];  // $mail_send
        $ID = 'AB-'.$number_file;
        $city_from_id_data = iconv("utf-8","windows-1251",$arData[5]);  // $city_send
        $city_to_id_data = iconv("utf-8","windows-1251",$arData[8]);  // $city_recipient
        $city_from_id = GetCityId($city_from_id_data);
        $city_to_id = GetCityId($city_to_id_data);
        $company_send = "";
        $company_rec = "";
        if(trim($arData[0]) === "Получатель"){   // $who_delivery
            $delivery_p = 'П';
            $del_pm = 'R';
            $company_rec = $arData[1];
            $name_dev = $arData[7];
            $phone_major = $arData[10]; /* для чека телефон плательщика  $phone_recipient  */
        }elseif(trim($arData[0]) === "Отправитель"){
            $delivery_p = 'O';
            $company_send = $arData[1];
            $name_dev = $arData[3];   // $sender_name
            $del_pm = 'S';
            $phone_major = $arData[4]; /* для чека телефон плательщика $phone_send */
        }
        $PAYMENT_AMOUNT = (float) preg_replace('/[^0-9]+/', '', htmlspecialcharsEx($arData[15]));
        $instr =$arData[14].' Оплачено в сумме - '.htmlspecialcharsEx($arData[15]);  // $sum_pay
        $date_take = substr($arData[12], 6, 4).'-'.substr($arData[12], 3, 2).'-'.  // $date_delivery
            substr($arData[12], 0, 2).' '.$arData[13].':00';  // $time
        $NUMBER = htmlspecialcharsEx($arData[18]); /* номер для чека $number */
        $summ_check = htmlspecialcharsEx($arData[15]); /* сумма для чека $sum_pay */

        $data = [
            'ID' => $ID,
            'NAME' => $name_dev,
            'NAME_SENDER' => $arData[3],
            'PHONE_SENDER' => $arData[4],
            'CITY_SENDER' => (int)$city_from_id,
            'ADRESS_SENDER' => $arData[6],
            'NAME_RECIPIENT' => $arData[7],
            'PHONE_RECIPIENT' => $arData[10],
            'CITY_RECIPIENT' => (int)$city_to_id,
            'ADRESS_RECIPIENT' => $arData[9],
            'DELIVERY_PAYER' => $delivery_p,
            'CITY_S' => $arData[5],
            'CITY_R' => $arData[8],
            'DATE_CREATE' => date('Y-m-d H:i:s'),
            'INN' => $id_partner,
            'NUMBER' => $arData[18],
            'MAIL_PAYER' => $MAIL_PAYER,
            'COMPANY_SENDER' =>  $company_send,
            'INDEX_SENDER' => '',
            'COMPANY_RECIPIENT' =>  $company_rec,
            'INDEX_RECIPIENT' => '',
            'DATE_TAKE_FROM' => $date_take,
            'DATE_TAKE_TO' => $date_take,
            'TYPE' => 'Не документы',
            'DELIVERY_TYPE' => 'С',
            'PAYMENT_TYPE' => 'Банковская карта',
            'DELIVERY_CONDITION' => 'А',
            'PAYMENT_AMOUNT' => $PAYMENT_AMOUNT,
            'PAYMENT_STATUS' => 1,
            'INSTRUCTIONS' => $instr,
            'PLACES' => 1,
            'WEIGHT' => (float)$arData[11],
            'SIZE_1' => 0,
            'SIZE_2' => 0,
            'SIZE_3' => 0,
            'TIME' => $arData[13],
        ];


    }
    /*   обработка оплаты банковской картой из формы Калькулятор на главной и из кабинета физ. лица */
    elseif(!empty($arrInfDocs && $arrInfDocs[17] !== 'AB'))
    {

        $MAIL_PAYER = htmlspecialcharsEx($arData[2]);

        $del_pm = trim(htmlspecialcharsEx($arData[15]));
        if ($delivery_payment !== "C"){
            $city_from_id_data = iconv("utf-8","windows-1251",$arData[3]);
            $city_to_id_data = iconv("utf-8","windows-1251",$arData[8]);
            $city_from_id = GetCityId(htmlspecialcharsEx($city_from_id_data));
            $city_to_id = GetCityId(htmlspecialcharsEx($city_to_id_data));
            $city_from_id = iconv("windows-1251","utf-8",$city_from_id);
            $city_to_id = iconv("windows-1251","utf-8",$city_to_id);
            $date_take = substr($arData[6], 6, 4).'-'.substr($arData[6], 3, 2).'-'.substr($arData[6], 0, 2).' '.$arData[7].':00';
            $instr =$arData[12].' Оплачено в сумме - '.htmlspecialcharsEx($arData[14]);
            $id_invoice  = $number_file;
            $summ_check = htmlspecialcharsEx($arData[14]);
            $NUMBER = htmlspecialcharsEx($arData[13]);
            $WEIGHT = (float) htmlspecialcharsEx($arData[5]);
            $time_take = htmlspecialcharsEx($arData[7]);
            $PAYMENT_AMOUNT = (float) preg_replace('/[^0-9]+/', '', htmlspecialcharsEx($arData[14]));
        }else{
            $city_from_id_data = iconv("utf-8","windows-1251",$arData[4]);
            $city_to_id_data = iconv("utf-8","windows-1251",$arData[8]);
            $city_from_id = GetCityId(htmlspecialcharsEx($city_from_id_data));
            $city_to_id = GetCityId(htmlspecialcharsEx($city_to_id_data));
            $city_from_id = iconv("windows-1251","utf-8",$city_from_id);
            $city_to_id = iconv("windows-1251","utf-8",$city_to_id);
            $date_take = substr($arData[12], 6, 4).'-'.substr($arData[12], 3, 2).'-'.
                substr($arData[12], 0, 2).' '.$arData[13].':00';
            $instr =$arData[14].' Оплачено в сумме - '.$arData[15];
            $id_invoice  = $number_file;
            $summ_check = htmlspecialcharsEx($arData[15]);
            $NUMBER = htmlspecialcharsEx($arData[17]);
            $WEIGHT = (float) htmlspecialcharsEx($arData[11]);
            $time_take = htmlspecialcharsEx($arData[13]);
            $PAYMENT_AMOUNT = (float) preg_replace('/[^0-9]+/', '', htmlspecialcharsEx($arData[15]));
        }
        $data_b = [
            'DATE_CREATE' => date('Y-m-d H:i:s'),
            'INN' => $id_partner,
            'NUMBER' => $NUMBER,
            'MAIL_PAYER' => $MAIL_PAYER,
            'COMPANY_SENDER' => "",
            'INDEX_SENDER' => '',
            'COMPANY_RECIPIENT' => '',
            'INDEX_RECIPIENT' => '',
            'DATE_TAKE_FROM' => htmlspecialcharsEx($date_take),
            'DATE_TAKE_TO' =>htmlspecialcharsEx($date_take),
            'TYPE' => 'Не документы',
            'DELIVERY_TYPE' => 'С',
            'PAYMENT_TYPE' => 'Банковская карта',
            'DELIVERY_CONDITION' => 'А',
            'PAYMENT_AMOUNT' => $PAYMENT_AMOUNT,
            'PAYMENT_STATUS' => 1,
            'INSTRUCTIONS' => htmlspecialcharsEx($instr),
            'PLACES' => 1,
            'WEIGHT' => $WEIGHT,
            'SIZE_1' => 0,
            'SIZE_2' => 0,
            'SIZE_3' => 0,
            'TIME' => $time_take,
        ];
        if($stampfl === "Y" && $delivery_payment !== "S"){
            if( $delivery_payment === "R"){
//dump($arData);
                $state = 412; /* статус - получатель */
                $ID = 'BFL-'.$id_invoice;
                $phone_major =  htmlspecialcharsEx($arData[1]);
                $name_dev = htmlspecialcharsEx($arData[0]);
                $delivery_p = 'П';
                $NAME_RECIPIENT = htmlspecialcharsEx($arData[0]);
                $PHONE_RECIPIENT = htmlspecialcharsEx($arData[1]);
                $ADRESS_RECIPIENT = htmlspecialcharsEx($arData[4]);
                $NAME_SENDER =  htmlspecialcharsEx($arData[10]);
                $PHONE_SENDER = htmlspecialcharsEx($arData[11]);
                $ADRESS_SENDER = htmlspecialcharsEx($arData[9]);

                $data_r = [
                    'ID' => $ID,
                    'NAME' => $name_dev,
                    'NAME_SENDER' => $NAME_SENDER,
                    'PHONE_SENDER' => $PHONE_SENDER,
                    'CITY_SENDER' => (int)$city_to_id,
                    'ADRESS_SENDER' => $ADRESS_SENDER,
                    'NAME_RECIPIENT' => $NAME_RECIPIENT,
                    'PHONE_RECIPIENT' => $PHONE_RECIPIENT,
                    'CITY_RECIPIENT' => (int)$city_from_id,
                    'ADRESS_RECIPIENT' => $ADRESS_RECIPIENT,
                    'DELIVERY_PAYER' => $delivery_p,
                    'CITY_S' => htmlspecialcharsEx($arData[8]),
                    'CITY_R' => htmlspecialcharsEx($arData[3]),
                ];
                $data = array_merge($data_b, $data_r);

            }
            if( $delivery_payment === "C"){
                $state = 413; /* статус - заказчик */
                $ID = 'BFL-'.$id_invoice;
                $phone_major =  htmlspecialcharsEx($arData[1]);
                $name_dev = htmlspecialcharsEx($arData[0]);
                $delivery_p = 'З';
                $NAME_RECIPIENT = htmlspecialcharsEx($arData[7]);
                $PHONE_RECIPIENT = htmlspecialcharsEx($arData[10]);
                $ADRESS_RECIPIENT = htmlspecialcharsEx($arData[9]);
                $NAME_SENDER =  htmlspecialcharsEx($arData[3]);
                $PHONE_SENDER = htmlspecialcharsEx($arData[6]);
                $ADRESS_SENDER = htmlspecialcharsEx($arData[5]);

                $data_c = [
                    'ID' => $ID,
                    'NAME' => $name_dev,
                    'NAME_SENDER' => $NAME_SENDER,
                    'PHONE_SENDER' => $PHONE_SENDER,
                    'CITY_SENDER' => (int)$city_from_id,
                    'ADRESS_SENDER' => $ADRESS_SENDER,
                    'NAME_RECIPIENT' => $NAME_RECIPIENT,
                    'PHONE_RECIPIENT' => $PHONE_RECIPIENT,
                    'CITY_RECIPIENT' => (int)$city_to_id,
                    'ADRESS_RECIPIENT' => $ADRESS_RECIPIENT,
                    'DELIVERY_PAYER' => $delivery_p,
                    'CITY_S' => htmlspecialcharsEx($arData[4]),
                    'CITY_R' => htmlspecialcharsEx($arData[8]),
                ];
                $data = array_merge($data_b, $data_c);

            }
        }else{

            if($delivery_payment === "S"){$ID = 'BFL-'.$id_invoice;}else{$ID = 'B-'.$id_invoice;}
            if( $del_pm === 'R')
            {
                $phone_major =  htmlspecialcharsEx($arData[11]);
                $name_dev = htmlspecialcharsEx($arData[10]);
                $delivery_p = 'П';
                $state = 412; /* статус - получатель */
            }elseif($del_pm === 'S')
            {
                $phone_major =  htmlspecialcharsEx($arData[1]);
                $name_dev = htmlspecialcharsEx($arData[0]);
                $delivery_p = 'О';
                $state = 411; /* статус - отправитель */
            }

            $NAME_SENDER = htmlspecialcharsEx($arData[0]);
            $PHONE_SENDER = htmlspecialcharsEx($arData[1]);
            $ADRESS_SENDER = htmlspecialcharsEx($arData[4]);
            $NAME_RECIPIENT =  htmlspecialcharsEx($arData[10]);
            $PHONE_RECIPIENT = htmlspecialcharsEx($arData[11]);
            $ADRESS_RECIPIENT = htmlspecialcharsEx($arData[9]);

            $data_s = [
                'ID' => $ID,
                'NAME' => $name_dev,
                'NAME_SENDER' => $NAME_SENDER,
                'PHONE_SENDER' => $PHONE_SENDER,
                'CITY_SENDER' => (int)$city_from_id,
                'ADRESS_SENDER' => $ADRESS_SENDER,
                'NAME_RECIPIENT' => $NAME_RECIPIENT,
                'PHONE_RECIPIENT' => $PHONE_RECIPIENT,
                'CITY_RECIPIENT' => (int)$city_to_id,
                'ADRESS_RECIPIENT' => $ADRESS_RECIPIENT,
                'DELIVERY_PAYER' => $delivery_p,
                'CITY_S' => htmlspecialcharsEx($arData[3]),
                'CITY_R' => htmlspecialcharsEx($arData[8]),
            ];
            $data = array_merge($data_b, $data_s);

        }
//AddToLogs('ardata', [$data]);

    }
    /* массив для чека */
    $arParamsCheck = [
        "DocNumber"=>'',
        "DocZNumber"=> $NUMBER,
        "isGoods"=>0,
        "PaymentType"=>1,
        "CheckEmail"=> $MAIL_PAYER,
        "CheckPhone"=>$phone_major,
        "Goods"=>[
            "Good_0"=>[
                "GoodsName"=>"Сумма к оплате",
                "GoodsCount"=>1,
                "GoodsPrice"=>(int)$summ_check,
                "GoodsSum"=>(int)$summ_check,
                "GoodsNDS"=>0,
                "GoodsSumNDS"=>0
            ],
        ],
    ];
    $arParamsJsonCheck = [
        'PaymentType'=>2,
        'ListOfDocs' => json_encode($arParamsCheck)
    ];
//  {"DocNumber":"99-4885033","DocZNumber":"","isGoods":0,"PaymentType":1,"CheckEmail":"","CheckPhone":"","Goods":{"Good_0":{"GoodsName":"Сумма к оплате","GoodsCount":1,"GoodsPrice":390,"GoodsSum":390,"GoodsNDS":"0","GoodsSumNDS":0}}}

    $client = soap_inc();
    if(is_object($client)){
        /* получить кассовый чек */
        $result = $client->SetNewCheck ($arParamsJsonCheck);
        $chResult = $result->return;
        $chResult = json_decode($chResult, true);
        if(isset($chResult['NUMBER'])){
            $data['NUMBER_CHECK'] = $chResult['NUMBER'];
        }
        AddToLogs('OrdersResultCheck', ['Response' => $chResult]);
    }else{
        AddToLogs('OrdersResultCheck', ['ERROR' => $client]);
    }


    $arJson[] = $data;
    $arJsonSend =$arJson;
    $arParamsJson = [
        'type' => 2,
        'ListOfDocs' => json_encode($arJsonSend)
    ];
    AddToLogs('Orders', ['json' => json_encode($arJsonSend)]);
    if(is_object($client)){
        /* записать заявку в 1с */
        $result = $client->SetDocsList($arParamsJson);
        $mResult = $result->return;
        AddToLogs('OrdersResult', ['Response' => $mResult, 'NUMBER' => $data['NUMBER'], 'NAME' => $name_dev]);
    }else{
        AddToLogs('OrdersResult', ['ERROR' => $client]);
    }
    /* автоматическая регистрация если не из лк фл */
    if (!$stampfl){
//AddToLogs('dataWin', [$dataWin]);
        $new_password = randString(7);
        $pass = $new_password;
        $login = $data['MAIL_PAYER'];
        $email = $data['MAIL_PAYER'];

        if($del_pm === 'S')
        {
            $phone = $data['PHONE_SENDER'];
            $state = 411;

        }elseif($del_pm === 'R')
        {
            $phone = $data['PHONE_RECIPIENT'];
            $state = 412;

        }

        $name = $data['NAME'];
        $access = true;
        $arFields = [
            "NAME" => $name ,
            "LOGIN" => $email,
            "EMAIL" => $email,                                 //$email,  заменить после тестов
            "LID" => "ru",
            "ACTIVE" => "Y",
            "PASSWORD" => $pass,
            "CONFIRM_PASSWORD" => $pass,
            "GROUP_ID" => [3,29]
        ];
        $rsUser = CUser::GetByLogin($login);
        if(!($arUser = $rsUser->Fetch())){
            /* новый пользователь */
            $arFields['DESCRIPTION'] = "На сайте newpartner.ru прошла автоматическая регистрация пользователя	при оплате
заявки банковской картой из формы на главной.";
            $arFieldsWIN = arFromUtfToWin($arFields);
            $user     = new CUser;
            $new_user_ID = $user->Add($arFieldsWIN);

            $fields = ["PERSONAL_PHONE" => $phone];
            $arFieldsUp = arFromUtfToWin($fields);
            $user->Update($new_user_ID, $arFieldsUp);
        }else{  /* если владелец лк оформляет заявку не через лк */
            $id_user = $arUser['ID'];
            $arGroups = CUser::GetUserGroup($id_user);
            foreach($arGroups as $value){
                if($value == 29){
                    $IdUser = $id_user;
                }
            }

        }

        if($new_user_ID){
            $arFieldsWIN['USER_ID'] = $new_user_ID;
            AddToLogs('NewUserFL', ['ID'=>$new_user_ID, 'Login'=>$login, 'pass'=>$pass,
                'name'=>$arFieldsWIN['NAME']]);

            $event = new CEvent;
            /* письмо на модерацию */
            $arFieldsWIN['PERSONAL_PHONE'] = $arFieldsUp['PERSONAL_PHONE'];
            if(!empty($arFieldsWIN)){
                $event->Send("NEW_USER", "s5", $arFieldsWIN, "N", 293 );
                /* письмо user`у */
                $event->Send("NEWPARTNER_LK", "s5", $arFieldsWIN, "N", 294 );
            }

        }
        if($new_user_ID){
            $user_ID = $new_user_ID;
        }else{
            $user_ID = $IdUser;
        }
        $USER->Authorize($user_ID);
        /* автоматическая регистрация end */
    }

    /* сохранение заявки в базе */
    if ($user_ID){
        $format = CSite::GetDateFormat(SHORT);
        $date_time = strtotime($data['DATE_TAKE_TO']);
        $date_to =  date('d.m.Y', $date_time);
        $prop = [
            957 => $data['ID'],
            944 => $user_ID,
            945 => $data['NAME_SENDER'],
            946 => $data['PHONE_SENDER'],
            947 => $data['CITY_SENDER'],
            948 => $data['ADRESS_SENDER'],
            949 => $data['NAME_RECIPIENT'],
            950 => $data['PHONE_RECIPIENT'],
            951 => $data['CITY_RECIPIENT'],
            952 => $data['ADRESS_RECIPIENT'],
            953 => $date_to,
            955 => $data['WEIGHT'],
            958 => $data['INSTRUCTIONS'].' Дата и время забора - '.$date_take,
            956 => $state,
            959 =>$chResult['SUM'],
            960 => $chResult['NUMBER'],
            962 => 408,

        ];
        $fields = [
            "ACTIVE_FROM" => date('d.m.Y H:i:s'),
            "IBLOCK_SECTION_ID" => false,
            "MODIFIED_BY" => $user_ID,
            "CREATED_BY" => $user_ID,
            "IBLOCK_ID" => 113,
            'NAME'=> $data['NUMBER'],
            'ACTIVE' => 'Y',
            "PROPERTY_VALUES" => $prop
        ];
        $arSelect = [
            "NAME",
            "IBLOCK_ID",
            "ID",
            "PROPERTY_*",
        ];
        $fields = arFromUtfToWin($fields);
//AddToLogs('newApplication', $fields);
        $arrNewApp = saveIblockElement($fields, $arSelect, true);
        if(!empty($arrNewApp)){
            AddToLogs('newApplication', $arrNewApp);
        }else{
            AddToLogs('newApplication', ["ERROR"=>iconv('utf-8', 'windows-1251',
                "Ошибка добавления заявки")]);
        }
    }


    /* отправка писем */
    if($arrInfDocs[17] !== 'AB'){
        $data['CREATOR_NAME_TITLE_SENDER'] = "Отправитель";
        $data['CREATOR_NAME_TITLE_RECIPIENT'] = "Получатель";
        $data['CREATOR_PHONE_TITLE_SENDER'] = "Телефон Отправителя";
        $data['CREATOR_PHONE_TITLE_RECIPIENT'] = "Телефон Получателя";
        $data_app = $data;  /* массив для usera */
        $event = new CEvent;
        if($delivery_payment !== "C"){
            if( $del_pm === 'S'){
                $data['CREATOR_NAME_TITLE_SENDER'] = "Отправитель(создал заявку)";
                $data['CREATOR_NAME_TITLE_RECIPIENT'] = "Получатель";
                $data['CREATOR_PHONE_TITLE_SENDER'] = "Телефон Отправителя(создал заявку)";
                $data['CREATOR_PHONE_TITLE_RECIPIENT'] = "Телефон Получателя";
            }elseif ( $del_pm === 'R')
            {
                $data['CREATOR_NAME_TITLE_SENDER'] = "Отправитель";
                $data['CREATOR_NAME_TITLE_RECIPIENT'] = "Получатель (создал заявку)";
                $data['CREATOR_PHONE_TITLE_SENDER'] = "Телефон Отправителя";
                $data['CREATOR_PHONE_TITLE_RECIPIENT'] = "Телефон Получателя(создал заявку)";
            }

            /* письмо нам */
            if ($stampfl !== 'Y'){
                $data['DESC'] = "Заказчик $name_dev оформил заявку {$data['NUMBER']} на сайте newpartner.ru на главной
из формы Калькулятор (оплата картой)";
            }else{
                $data['DESC'] = "Заказчик $name_dev оформил заявку {$data['NUMBER']} на сайте newpartner.ru
из личного кабинета (оплата картой)";
            }
            $data['SUBJECT'] = "Заказчик $name_dev оформил заявку {$data['NUMBER']}";
            $data['MAIL_TO'] = "info@newpartner.ru";
            $data = arFromUtfToWin($data);
            if (!empty($data)) {
                $event->SendImmediate("NEWPARTNER_LK", "s5", $data, "N", 295);
            }
            /* письмо клиенту */
            $data_app['MAIL_TO'] = $data_app['MAIL_PAYER'];
            $data_app['DESC'] = "Уважаемый(ая) $name_dev! На сайте 'Новый партнер' Вы оформили заявку на доставку
№{$data_app['NUMBER']}.
Телефон для справок - +7 495 663-99-18 8-800-55-123-89";
            $data_app['SUBJECT'] = "Заявка на доставку №{$data_app['NUMBER']} \"Новый партнер\"" ;
            $data_app = arFromUtfToWin($data_app);
            if (!empty($data_app)) {
                $event->SendImmediate("NEWPARTNER_LK", "s5", $data_app, "N", 295);
            }
        }else{
            /* отправка писем если заказчик */
            $data['DESC'] = "Заказчик $name_dev оформил и оплатил банковской картой  заявку  №{$data['NUMBER']}
на сайте newpartner.ru из Личного кабинета физического лица - {$USER->GetEmail()}";

            $data['EPILOG'] =  "<tr><td>Заказчик (создал заявку)</td><td>$name_dev</td></tr>
<tr><td>Телефон Заказчика</td><td>$phone_major</td></tr>";
            $data['SUBJECT'] = "Заказчик $name_dev оформил заявку {$data['NUMBER']}";
            $data['MAIL_TO'] = "info@newpartner.ru";
            $data = arFromUtfToWin($data);
            if (!empty($data)) {
                $event->SendImmediate("NEWPARTNER_LK", "s5", $data, "N", 295);
            }
            /* письмо заказчику */
            $data_app['MAIL_TO'] = $data_app['MAIL_PAYER'];
            $data_app['EPILOG'] =  "<tr><td>Заказчик</td><td>$name_dev</td></tr>
<tr><td>Телефон Заказчика</td><td>$phone_major</td></tr>";
            $data_app['DESC'] = "Уважаемый(ая) $name_dev! На сайте 'Новый партнер' Вы оформили заявку на доставку
№{$data_app['NUMBER']}. Телефон для справок - +7 495 663-99-18 8-800-55-123-89";
            $data_app['SUBJECT'] = "Заявка на доставку №{$data_app['NUMBER']} \"Новый партнер\"" ;
            $data_app = arFromUtfToWin($data_app);
            if (!empty($data_app)) {
                $event->SendImmediate("NEWPARTNER_LK", "s5", $data_app, "N", 295);
            }
        }
    }else{
        if($del_pm === 'S'){
            $data['CREATOR_NAME_TITLE_SENDER'] = "Отправитель (создал и оплатил заявку)";
            $data['CREATOR_NAME_TITLE_RECIPIENT'] = "Получатель";

        }
        if($del_pm === 'R'){
            $data['CREATOR_NAME_TITLE_SENDER'] = "Отправитель";
            $data['CREATOR_NAME_TITLE_RECIPIENT'] = "Получатель (создал и оплатил заявку)";

        }
        $data['CREATOR_PHONE_TITLE_SENDER'] = "Телефон Отправителя";
        $data['CREATOR_PHONE_TITLE_RECIPIENT'] = "Телефон Получателя";
        $data_app = $data;  /* массив для usera */
        $event = new CEvent;
        $data['DESC'] = "Заказчик $name_dev оформил заявку {$data['NUMBER']} на сайте newpartner.ru на главной
из формы Заказ Услуги (оплата картой)";
        $data['SUBJECT'] = "Заказчик $name_dev оформил заявку {$data['NUMBER']}";
        $data['MAIL_TO'] = "info@newpartner.ru";
        $data = arFromUtfToWin($data);
        if (!empty($data)){
            $event->SendImmediate("NEWPARTNER_LK", "s5", $data, "N", 295);
        }
        /* письмо клиенту */
        $data_app['MAIL_TO'] = $data_app['MAIL_PAYER'];
        $data_app['DESC'] = "Уважаемый(ая) $name_dev! На сайте 'Новый партнер' Вы оформили заявку на доставку
№{$data_app['NUMBER']}.
Телефон для справок - +7 495 663-99-18 8-800-55-123-89";
        $data_app['SUBJECT'] = "Заявка на доставку №{$data_app['NUMBER']} \"Новый партнер\"" ;
        $data_app = arFromUtfToWin($data_app);
        if (!empty($data_app)){
            $event->SendImmediate("NEWPARTNER_LK", "s5", $data_app, "N", 295);
        }

    }
    /* возврат в лк если пришли из лк или авторизованный пользователь */
    if ($stampfl === 'Y' || $USER->IsAuthorized()) {
        foreach($_COOKIE as $key=>$value){
            if(preg_match('/^pay_.+/', $key)){
                setcookie($key, "", time() - 3600);
                unset($_COOKIE[$key]);
            }
        }
        setcookie('dr_name', "", time() - 3600);
        unset($_COOKIE['dr_name']);
        setcookie('dr_phone', "", time() - 3600);
        unset($_COOKIE['dr_phone']);
        setcookie('dr_adr', "", time() - 3600);
        unset($_COOKIE['dr_adr']);
        header("Location: /customers-lk/");
        exit;
    }

    header("Location: /");
    exit;

} /* конец обработки платежа */

/* обработка из формы заказать услугу на главной */
if($type_pay === 'Y' )
{
    /* массив полей из формы */
    foreach($_COOKIE as $key=>$value){
        if(preg_match('/^paycard_.+/',$key)){
            $k = preg_replace('/^paycard_/','', $key);
            $arResult['PAYCARD'][$k] = trim(htmlspecialcharsEx($value));
        }
    }
    if ($USER->isAdmin()){
        dump($arResult);
    }
    /* браузер клиента не поддерживает cookie */
    if (empty($arResult['PAYCARD'])){
        header("Location: https://newpartner.ru?nocard=1");
        exit;
    }
    /* запрос в 1с за номером */
    $clientw = soap_inc();
    $id_partner = 27122866;
    $resultw = $clientw->GetPrefixAgent1(['INN' => $id_partner]);
    $mResultw = $resultw->return;
    $objw = json_decode($mResultw, true);
    $number = $objw['Prefix_'.$id_partner];
    $number_pay = $objw['Prefix_'.$id_partner];
    $number_file = preg_replace('/^[^0-9]+-/', '', $number_pay );
    $number_file =  trim($number_file);
    $arrInf[] = $number;
    $delivery_payment_type = 'AB';

    if($arResult['PAYCARD']['form_radio_SIMPLE_QUESTION_971'] == '102'){
        $who_delivery = "Отправитель";
    }else{
        $who_delivery = "Получатель";
    }
    if($arResult['PAYCARD']['form_dropdown_PAYER'] == '139'){
        $delivery_payment_who = "Отправитель";
    }elseif($arResult['PAYCARD']['form_dropdown_PAYER'] == '140'){
        $delivery_payment_who = "Получатель";
    }elseif($arResult['PAYCARD']['form_dropdown_PAYER'] == '141'){
        $delivery_payment_who = "Другой";
    }
    $organization = $arResult['PAYCARD']['form_text_49'];
    $sender_name = $arResult['PAYCARD']['form_text_50'];
    $phone_send = $arResult['PAYCARD']['form_text_51'];
    $mail_send = $arResult['PAYCARD']['form_email_52'];
    $city_send = $arResult['PAYCARD']['form_text_55'];
    $sender_address = $arResult['PAYCARD']['form_textarea_56'];
    $recipient_name = $arResult['PAYCARD']['form_text_62'];
    $city_recipient = $arResult['PAYCARD']['form_text_57'];
    $recipient_address = $arResult['PAYCARD']['form_textarea_103'];
    $phone_recipient = $arResult['PAYCARD']['form_text_149'];
    $weight = $arResult['PAYCARD']['FULLWEIGTH'].' кг.';
    $date_delivery = $arResult['PAYCARD']['form_text_53'];
    $time = $arResult['PAYCARD']['form_text_54'];
    $desc = $arResult['PAYCARD']['form_textarea_61'];
    $sum_pay = $arResult['PAYCARD']['TARIF_ITOG'].' руб.';
    $time_dev = $arResult['PAYCARD']['TIMEDEV'].' дн.';

    $arrInf = [$who_delivery, $organization, $mail_send, $sender_name, $phone_send,  $city_send, $sender_address,
        $recipient_name, $city_recipient,  $recipient_address, $phone_recipient, $weight, $date_delivery,
        $time, $desc, $sum_pay, $time_dev];
    $data = '<?php $arrInfDocs ='."['{$who_delivery}', '{$organization}', '{$mail_send}', '{$sender_name}', 
                            '{$phone_send}', '{$city_send}', '{$sender_address}',
                            '{$recipient_name}', '{$city_recipient}','{$recipient_address}', '{$phone_recipient}',
                            '{$weight}', '{$date_delivery}', '{$time}', '{$desc}', '{$sum_pay}', '{$time_dev}',
                             '{$delivery_payment_type}','{$number}'];";
    file_put_contents($_SERVER['DOCUMENT_ROOT']."/docs/invoice/data_invoice_$number_file.php", $data );

    AddToLogs('PAYCARDSERV', ['desc' => ' Данные из формы заказ услуги на главной с оплатой картой -
templates/newpartner-payment/header.php','arrInf' => $arrInf]);
    $event = new CEvent;
    $dataE['MAIL_PAYER'] = $mail_send;
    $dataE['CREATOR_NAME_TITLE_SENDER'] = 'Отправитель';
    $dataE['NAME_SENDER'] = $sender_name;
    $dataE['CREATOR_NAME_TITLE_RECIPIENT'] = 'Получатель';
    $dataE['NAME_RECIPIENT'] = $recipient_name;
    $dataE['CREATOR_PHONE_TITLE_SENDER'] = $phone_send;
    $dataE['CREATOR_PHONE_TITLE_RECIPIENT'] = $phone_recipient;
    $dataE['PHONE_SENDER'] = 'Телефон отправителя';
    $dataE['PHONE_RECIPIENT'] = 'Телефон получателя';
    $dataE['DATE_TAKE_FROM'] = $date_delivery;
    $dataE['TIME'] = $time;
    $dataE['CITY_S'] = $city_send;
    $dataE['ADRESS_SENDER'] = $sender_address;
    $dataE['CITY_R'] = $city_recipient;
    $dataE['ADRESS_RECIPIENT'] = $recipient_address;
    $dataE['WEIGHT'] = $weight;
    $dataE['PAYMENT_TYPE'] = 'Банковская карта';
    $dataE['PAYMENT_AMOUNT'] = $sum_pay;
    $dataE['INSTRUCTIONS'] = $desc;

    $dataE['DESC'] = "Письмо создано для предварительного ознакомления с заявкой  $number_file клиента физ. лица, перед оплатой";
    $dataE['EPILOG'] =  "На данном этапе оформления Курьер не вызван, оплата не произведена. Заказчик - $who_delivery,  Оплачивает - $delivery_payment_who.";
    $dataE['SUBJECT'] = "Клиент оформляет заявку $number_file, с оплатой банковской картой";
    $dataE['MAIL_TO'] = "info@newpartner.ru";
    $dataE = arFromUtfToWin($dataE);
    $event->SendImmediate("NEWPARTNER_LK", "s5", $dataE, "N", 295);

}
else
{
    /* браузер клиента не поддерживает cookie */
    if (empty($_COOKIE['pay_form_email_52']) && empty($_COOKIE['pay_name_custom'])){
        header("Location: https://newpartner.ru?nocard=1");
        exit;
    }
    $delivery_payment_type = "CB";
    $clientw = soap_inc();
    if(is_object($clientw)){
        $d_p =  htmlspecialcharsEx($_COOKIE['pay_form_radio_SIMPLE_QUESTION_971']);
        $lk_fl =  trim(htmlspecialcharsEx($_COOKIE['pay_lk_fl']));
        if($d_p == 102){
            $delivery_payment = 'S';
            setcookie("pay_dp", "S");
        }elseif($d_p == 121){
            $delivery_payment = 'R';
            setcookie("pay_dp", "R");
        }elseif($d_p === 'creator'){
            $delivery_payment = 'C';
            setcookie("pay_dp", "C");
        }
        $name = htmlspecialcharsEx($_COOKIE['pay_name_custom']);
        $phone = htmlspecialcharsEx($_COOKIE['pay_phone_custom']);
        $city_send = htmlspecialcharsEx($_COOKIE['pay_form_text_hidden55']);
        $city_recipient =  htmlspecialcharsEx($_COOKIE['pay_form_text_hidden57']);
        $date_delivery = htmlspecialcharsEx($_COOKIE['pay_form_text_53']);
        $weight = htmlspecialcharsEx($_COOKIE['pay_form_text_hidden58']);
        $phone_send = htmlspecialcharsEx($_COOKIE['pay_form_text_51']);
        $sum_pay = htmlspecialcharsEx($_COOKIE['pay_price_calc']);
        $mail_send = htmlspecialcharsEx($_COOKIE['pay_form_email_52']);
        $sender_name = htmlspecialcharsEx($_COOKIE['pay_form_text_50']);
        $sender_address = htmlspecialcharsEx($_COOKIE['pay_form_textarea_56']);
        $time = htmlspecialcharsEx($_COOKIE['pay_form_text_54']);
        $recipient_address = htmlspecialcharsEx($_COOKIE['pay_form_textarea_103']);
        $recipient_name = htmlspecialcharsEx($_COOKIE['pay_form_text_62']);
        $phone_recipient = htmlspecialcharsEx($_COOKIE['pay_form_text_149']);
        $desc = htmlspecialcharsEx($_COOKIE['pay_form_textarea_61']);
        if($lk_fl === "Y"){
            $mail_send = htmlspecialcharsEx($_COOKIE['pay_form_email_52_hidden']);

            if($delivery_payment === 'S'){
                $sender_name = htmlspecialcharsEx($_COOKIE['pay_form_text_50_hidden']);
                $phone_send = htmlspecialcharsEx($_COOKIE['pay_form_text_51_hidden']);
                $recipient_name = htmlspecialcharsEx($_COOKIE['pay_form_text_62']);
                $phone_recipient = htmlspecialcharsEx($_COOKIE['pay_form_text_149']);
            }

            if($delivery_payment === 'R'){
                $recipient_name = htmlspecialcharsEx($_COOKIE['pay_form_text_50']);
                $phone_recipient = htmlspecialcharsEx($_COOKIE['pay_form_text_51']);
                $sender_name = htmlspecialcharsEx($_COOKIE['pay_form_text_62_hidden']);
                $phone_send = htmlspecialcharsEx($_COOKIE['pay_form_text_149_hidden']);
                $city_send = htmlspecialcharsEx($_COOKIE['pay_form_text_hidden57']);
                $sender_address = htmlspecialcharsEx($_COOKIE['pay_form_textarea_103']);
                $city_recipient = htmlspecialcharsEx($_COOKIE['pay_form_text_hidden55']);
                $recipient_address = htmlspecialcharsEx($_COOKIE['pay_form_textarea_56']);

            }
            if($delivery_payment === 'R' || $delivery_payment === 'S'){
                $arrInf = [$sender_name, $phone_send, $mail_send, $city_send, $sender_address, $weight, $date_delivery,
                    $time, $city_recipient, $recipient_address, $recipient_name, $phone_recipient, $desc, $sum_pay,
                    $delivery_payment];
            }

            if($delivery_payment === 'C'){
                $mail =  $mail_send;
                $sender_name = htmlspecialcharsEx($_COOKIE['pay_form_text_50']);
                $phone_send = htmlspecialcharsEx($_COOKIE['pay_form_text_51']);
                $recipient_name = htmlspecialcharsEx($_COOKIE['pay_form_text_62']);
                $phone_recipient = htmlspecialcharsEx($_COOKIE['pay_form_text_149']);

                $arrInf = [$name, $phone, $mail, $sender_name,  $city_send, $sender_address, $phone_send, $recipient_name,
                    $city_recipient, $recipient_address,  $phone_recipient, $weight, $date_delivery, $time,$desc, $sum_pay,
                    $delivery_payment];
            }
        }else{
            $arrInf = [$sender_name, $phone_send, $mail_send, $city_send, $sender_address, $weight, $date_delivery,
                $time, $city_recipient, $recipient_address, $recipient_name, $phone_recipient, $desc, $sum_pay,
                $delivery_payment
            ];
        }

        $sum_pay_send = $_COOKIE['pay_price_calc'];
        $id_partner = 27122866;
        $arParamsJsonw = ['INN' => $id_partner];
        $resultw = $clientw->GetPrefixAgent1($arParamsJsonw);
        $mResultw = $resultw->return;
        $objw = json_decode($mResultw, true);
        $number = $objw['Prefix_'.$id_partner];
        $number_pay = $objw['Prefix_'.$id_partner];
        $number_file = preg_replace('/^[^0-9]+-/', '', $number_pay );
        $number_file =  trim($number_file);
        $arrInf[] =$number;

        if($delivery_payment === 'C'){

            $data = '<?php $arrInfDocs ='."['{$name}', '{$phone}', '{$mail}', '{$sender_name}', 
                            '{$city_send}', '{$sender_address}', '{$phone_send}',
                            '{$recipient_name}', '{$city_recipient}','{$recipient_address}', '{$phone_recipient}',
                            '{$weight}', '{$date_delivery}', '{$time}', '{$desc}', '{$sum_pay}',
                             '{$delivery_payment}','{$number}'];";
        }else{
            $data = '<?php $arrInfDocs ='."['{$sender_name}', '{$phone_send}', '{$mail_send}', '{$city_send}', 
                            '{$sender_address}', '{$weight}', '{$date_delivery}',
                            '{$time}', '{$city_recipient}', '{$recipient_address}',
                            '{$recipient_name}', '{$phone_recipient}',
                            '{$desc}', '{$number}', '{$sum_pay}', '{$delivery_payment}'
                             ];";
        }

        file_put_contents($_SERVER['DOCUMENT_ROOT']."/docs/invoice/data_invoice_$number_file.php", $data );
    }
}
$this->IncludeComponentTemplate();
?>