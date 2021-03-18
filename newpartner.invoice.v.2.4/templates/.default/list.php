<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

 if($USER->isAdmin()){
            $start = microtime(true);
            AddToLogs('test_logs', ['time_start' => $start, 'mess' => 'Начало выполнения скрипта в шаблоне']);
           // dump($arResult);

        }

 /* для отчета Абсолют страхование и росгазефикация*/
if($arResult['CURRENT_CLIENT'] == 56103010 || $arResult['CURRENT_CLIENT'] == 62537553){
    $arraymerg = array_merge($arResult['REQUESTS'], $arResult['ARCHIVE']);

    $newarr = [];
    foreach ($arraymerg as $key => $value){
        if($value['state_text'] == 'Доставлено'){
           $cnt = count($value['Events'])-1;
           $date_dev =  $value['Events'][$cnt]['Date'];
           $date_dev = str_replace('&nbsp;', '-', $date_dev);
           $newarr[$key]['DATE_DELIVERY'] = $date_dev;
        }
        if (!empty($value['start_date'])){
            $date_start = date('d.m.Y-H:i', strtotime($value['start_date']));
        }
        $newarr[$key]['NAME'] = $value['NAME'];
        if( $date_start){
            $newarr[$key]['DATE_CREATE'] = $date_start;
        }else{
            $newarr[$key]['DATE_CREATE'] = $value['DATE_CREATE'];
        }

        $newarr[$key]['state_text'] = $value['state_text'];
        $newarr[$key]['center_cost'] = $value['center_cost'];
        $newarr[$key]['tarif'] = $value['Tarif'];
        $newarr[$key]['PROPERTY_CITY_RECIPIENT_NAME'] = $value['PROPERTY_CITY_RECIPIENT_NAME'];
        $newarr[$key]['PROPERTY_NAME_RECIPIENT_VALUE'] = $value['PROPERTY_NAME_RECIPIENT_VALUE'];
        $newarr[$key]['PROPERTY_COMPANY_RECIPIENT_VALUE'] = $value['PROPERTY_COMPANY_RECIPIENT_VALUE'];
        $newarr[$key]['PROPERTY_CITY_SENDER_NAME'] = $value['PROPERTY_CITY_SENDER_NAME'];
        $newarr[$key]['PROPERTY_NAME_SENDER_VALUE'] = $value['PROPERTY_NAME_SENDER_VALUE'];
        $newarr[$key]['PROPERTY_COMPANY_SENDER_VALUE'] = $value['PROPERTY_COMPANY_SENDER_VALUE'];
        $newarr[$key]['PROPERTY_WEIGHT_VALUE'] = $value['PROPERTY_WEIGHT_VALUE'];
    }

  $new_report = [];
  foreach($newarr as $key=>$value){
    if($value['DATE_DELIVERY']){
        $kl = trim($value['center_cost']) .'_'. trim($value['NAME']);
        preg_replace('/\s/', '-', $kl);
        $new_report[$kl] = $value;
    }
  }
    ksort($new_report);
    $new_report_utf = convArrayToUTF($new_report);
    $arrayreportjson = json_encode($new_report_utf);
    $newarrutf = convArrayToUTF($newarr);
    $arraymergutfjson = json_encode($newarrutf);
    //AddToLogs('report_abs', ['newreport'=>$arraymerg ]);
}

//var_dump($arResult['IndividualPrice']);
 ?>
<script type="text/javascript">

    <?php

    if($arResult['CURRENT_CLIENT'] == 56103010 || $arResult['CURRENT_CLIENT'] == 62537553 ):?>

    $(document).ready(function() {
        // вывод формы отчета абсолют страхование и Росгазификация
        $('#report_as').on('click', function(){
            jsonStrPhp = <?=$arraymergutfjson?>;
            jsonStr = JSON.stringify(jsonStrPhp);
            $.ajax({
                url: "/api/GetSum.php?report_as=Y",
                type: "post",
                data: {'numbersphp': jsonStr},
                dataType: "json",
                success: function (data) {
                    if(data.path){
                        window.open(data.path, '_blank');
                    }else{
                        alert(' Ошибка формирования отчета. Обратитесь в техподдержку ');
                    }

                }
            });
        });
        $('#report_from_1c').on('click', function(){
            var jsonStrPhp = $('form[name="filterform"]').serializeArray();
            jsonStr = JSON.stringify(jsonStrPhp);
            $('#modal-for-alert').modal('show');
            $.ajax({
                url: "/api/GetSum.php?report_1с=Y",
                type: "post",
                data: {'dataForReport': jsonStr},
                dataType: "json",
                success: function (data) {
                    if(data.path){
                           $('#modal-for-alert').modal('hide');
                           window.open(data.path, '_blank');
                    }
                    if(data.error){
                            $('#modal-for-alert .modal-body').html('<p style="color:red">'+data.error+
                                '</p>');
                    }

                }
            });
        });

        // абсолют страхование вывод центра затрат - замедляет работ лк, брать при загрузке в 1с

        /*let collect = $('.cost_center');
        let obj = {};
        for (let i = 0; i < collect.length; i++) {
            obj[i] = $.trim($(collect[i]).attr('data-cost'));
        }
        jsonStr = JSON.stringify(obj);

        $.ajax({
            url: "/api/GetSum.php?cost_center=Y",
            type: "post",
            data: {'numbers': jsonStr},
            dataType: "json",
            success: function (data) {
                $.each(data, function (index, value) {
                    let num = value.NAME;
                    let cost = value.PROPERTY_CENTER_EXPENSES_NAME;
                    $(`#cost_center_${num} i`).remove();
                    $(`#cost_center_${num}`).text(cost);
                });

            }
        });*/
    });
    <?php endif;?>

    <?php
    // стоимость доставки без инд. прайса
    if($arResult['IndividualPrice'] && $arResult['CURRENT_CLIENT'] != 41478141):?>
    document.addEventListener('DOMContentLoaded', function() {
        function customHttp() {
            return {
                get(url, cb) {
                    try {
                        const xhr = new XMLHttpRequest();
                        xhr.open('GET', url);
                        xhr.addEventListener('load', () => {
                            if (Math.floor(xhr.status / 100) !== 2) {
                                cb(`Error. Status code: ${xhr.status}`, xhr);
                                return;
                            }
                            const response = JSON.parse(xhr.responseText);
                            cb(null, response);
                        });

                        xhr.addEventListener('error', () => {
                            cb(`Error. Status code: ${xhr.status}`, xhr);
                        });

                        xhr.send();
                    } catch (error) {
                        cb(error);
                    }
                },
                post(url, body, headers, cb) {
                    try {
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', url);
                        xhr.addEventListener('load', () => {
                            if (Math.floor(xhr.status / 100) !== 2) {
                                cb(`Error. Status code: ${xhr.status}`, xhr);
                                return;
                            }
                            const response = JSON.parse(xhr.responseText);
                            cb(null, response);
                        });

                        xhr.addEventListener('error', () => {
                            cb(`Error. Status code: ${xhr.status}`, xhr);
                        });

                        if (headers) {
                            Object.entries(headers).forEach(([key, value]) => {
                                xhr.setRequestHeader(key, value);
                            });
                        }

                        xhr.send(JSON.stringify(body));
                    } catch (error) {
                        cb(error);
                    }
                },
            };
        }
        const ajaxGetSum = customHttp();
        function getTarifFrom1C(){
            const divTable = document.querySelector('.fixed-table-container');
            const curClient = document.querySelector('#current_client').textContent;
            divTable.addEventListener('click', function(e){
                let el = e.target;
                let idEl = el.id;
                let t_dev = el.getAttribute('data-typedev');
                idEl = idEl.replace(/\s+/g, '');
                if(idEl.match(/tarif_no_.+/g))
                {
                    const number = idEl.replace(/tarif_no_/,'');

                    //console.log(idEl, number);
                    ajaxGetSum.get(`/api/GetSum.php?numberNo=Y&curClient=${curClient}&numberInvoice=${number}&typeDev=${t_dev}`, onGetResponse);
                    function onGetResponse(error,res){
                        const elSumm = document.querySelector(`#${idEl}`);
                        const elSummContainer = elSumm.parentElement;
                        const summ = +res.sum_dev;
                        if(summ > 0){
                            elSummContainer.innerHTML = '';
                            elSummContainer.insertAdjacentHTML('afterbegin', `<span>${summ}</span>`);
                            document.cookie = `tarif_no_${number}=${summ}`;
                        }else{
                            elSummContainer.innerHTML = '';
                            const mess_err = "Нет данных";
                            elSummContainer.insertAdjacentHTML('afterbegin', `<span>${mess_err}</span>`);
                            document.cookie = `tarif_no_${number}=${mess_err}`;
                        }
                        console.log(res, idEl, summ, t_dev);
                    }
                }
            });
        }
        getTarifFrom1C();
    });
    <?endif;?>

   <?php
    // стоимость доставки для инд. прайс
    if(!$arResult['IndividualPrice'] && $arResult['CURRENT_CLIENT'] != 41478141):?>
    document.addEventListener('DOMContentLoaded', function() {
        function customHttp() {
            return {
                get(url, cb) {
                    try {
                        const xhr = new XMLHttpRequest();
                        xhr.open('GET', url);
                        xhr.addEventListener('load', () => {
                            if (Math.floor(xhr.status / 100) !== 2) {
                                cb(`Error. Status code: ${xhr.status}`, xhr);
                                return;
                            }
                            const response = JSON.parse(xhr.responseText);
                            cb(null, response);
                        });

                        xhr.addEventListener('error', () => {
                            cb(`Error. Status code: ${xhr.status}`, xhr);
                        });

                        xhr.send();
                    } catch (error) {
                        cb(error);
                    }
                },
                post(url, body, headers, cb) {
                    try {
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', url);
                        xhr.addEventListener('load', () => {
                            if (Math.floor(xhr.status / 100) !== 2) {
                                cb(`Error. Status code: ${xhr.status}`, xhr);
                                return;
                            }
                            const response = JSON.parse(xhr.responseText);
                            cb(null, response);
                        });

                        xhr.addEventListener('error', () => {
                            cb(`Error. Status code: ${xhr.status}`, xhr);
                        });

                        if (headers) {
                            Object.entries(headers).forEach(([key, value]) => {
                                xhr.setRequestHeader(key, value);
                            });
                        }

                        xhr.send(JSON.stringify(body));
                    } catch (error) {
                        cb(error);
                    }
                },
            };
        }
        const ajaxGetSum = customHttp();

        function getTarifFrom1C(){
            const divTable = document.querySelector('.fixed-table-container');
            const curClient = document.querySelector('#current_client').textContent;
            divTable.addEventListener('click', function(e){
                let el = e.target;
                let idEl = el.id;
                let t_dev = el.getAttribute('data-typedev');
                idEl = idEl.replace(/\s+/g, '');
                if(idEl.match(/tarif_.+/g))
                {
                    const number = idEl.replace(/tarif_/,'');

                    //console.log(idEl, number);
                    ajaxGetSum.get(`/api/GetSum.php?number=Y&curClient=${curClient}&numberInvoice=${number}&typeDev=${t_dev}`, onGetResponse);
                    function onGetResponse(error,res){
                        const elSumm = document.querySelector(`#${idEl}`);
                        const elSummContainer = elSumm.parentElement;
                        const summ = +res.sum_dev;
                        if(summ > 0){
                            elSummContainer.innerHTML = '';
                            elSummContainer.insertAdjacentHTML('afterbegin', `<span>${summ}</span>`);
                            document.cookie = `tarif_${number}=${summ}`;
                        }else{
                            elSummContainer.innerHTML = '';
                            const mess_err = "Нет данных";
                            elSummContainer.insertAdjacentHTML('afterbegin', `<span>${mess_err}</span>`);
                            document.cookie = `tarif_${number}=${mess_err}`;
                        }
                        console.log(res, idEl, summ, t_dev);
                    }
                }
            });
        }
        getTarifFrom1C();
    });

    /*$(document).ready(function() {
        let curclient = $('#current_client').text();
        let collectnumbers = $('.numberinvoice_new');
        let arrobj = {};
        for (let i = 0; i < collectnumbers.length; i++) {
            arrobj[i] = $.trim($(collectnumbers[i]).text());
        }
        jsonString = JSON.stringify(arrobj);
        $.ajax({
            url: "/api/GetSum.php?list=Y",
            type: "post",
            data: {'numbers': jsonString, 'curclient': curclient},
            dataType: "json",
            success: function (data) {

                $.each(data, function (index, value) {
                    let num = value.NUMBER;
                    let sum = value.SUM_DEV;
                    $(`#sumdev_${num} i`).remove();
                    $(`#sumdev_${num}`).text(sum);
                });
            }
        });
    });*/
    <?php endif;?>

    <?php
    // массив накладных с Возвратом и Абсолют страхование для отчета и вывода в списке (Абсолют 56103010) ||
    //    $arResult['CURRENT_CLIENT'] == 56103010
    if($arResult['CURRENT_CLIENT'] == 56280706 ||
    $arResult['CURRENT_CLIENT'] == 56389269 ||
    $arResult['CURRENT_CLIENT'] == 56389270 ||
    $arResult['CURRENT_CLIENT'] == 56389272 ||
    ($arResult['CURRENT_CLIENT'] == 56103010 && $USER->GetID() == 1721)):
    ?>
    $(document).ready(function() {
        let collectnumbers = $('.numberinvoice');
        let arrobj = {};
        for (let i = 0; i < collectnumbers.length; i++) {
            arrobj[i] = $.trim($(collectnumbers[i]).text());
        }
        jsonString = JSON.stringify(arrobj);
        $.ajax({
            url: "/api/GetSum.php?return=Y&user=<?=$USER->GetID()?>&client=<?=$arResult['CURRENT_CLIENT']?>",
            type: "post",
            data: {'numbers': jsonString},
            dataType: "json",
            success: function (data) {

                $.each(data, function (index, value) {
                    let num = value.NAME;
                    $("#ret_"+num).text('В');
                    data[index]['state_text'] = $("#stat_"+num).text();
                    data[index]['state_date'] = $("#stat_date_"+num).text();

                });
                $("#report_return_form_input").attr('value', JSON.stringify(data));

                $("#report_return_form_submit").removeAttr('disabled');


             //   console.log(JSON.stringify(data));
            }
        });

    });
    <?php endif;?>

    <?php 
    // исключить кнопку Вызов курьера у Вымпелком3
    if( !$_SESSION['СontractEndDate'] && $arResult['CURRENT_CLIENT'] != 56389270):?>
    $(document).ready(function(){
        $('.maskdate').mask('99.99.9999');
        $('.bootstrap-table .fixed-table-toolbar').append('<div class="pull-left">' +
            '<a href="/services/" style="margin-right:10px" class="btn btn-success">' +
            '<span class="glyphicon glyphicon-bell" aria-hidden="true"></span> Вызвать курьера</a>' +
            <?if($arResult['CURRENT_CLIENT'] == 9528186):?>
            '<div id="call_courier_ids" class="btn btn-warning" data-toggle="tooltip" data-placement="right" ' +
            'title="" data-original-title="Отметьте в чекбоксах накладные, по которым нужно вызвать курьера">' +
            '<span class="glyphicon glyphicon-bell" aria-hidden="true" ></span> Массовый вызов курьера</div>' +
            <?endif;?>
            '</div>');
         /*Отметьте в чекбоксах накладные, по которым нужно вызвать курьера.*/

    });
    <?php endif;?>
    $(function () {
        $(window).resize(function () {
            $('#tableId').bootstrapTable('resetView');
        });
        $('[data-toggle="tooltip"]').tooltip();
        $('.masktime').mask('99:99');

        /*  массовый вызов курьера */
        $('#call_courier_ids').on('click', function () {
               obj = {};
            $('.a1 input:checkbox:checked').each(function(k,v){
               obj[k] = $(v).val();
            });
            if(obj[0]){
                $('#data_json').val(JSON.stringify(obj));
                $('#myCallCurier_ids').modal('show');
                console.log(obj);

            }
        });

       $('form[name=form_callcourierdate_ids]').submit(function (e) {
           e.preventDefault();
           let data = this;
           let fields = $(data).serializeArray();
           $.ajax({
               type: "POST",
               dataType: "json",
               url: "/api/groupsCallCourier.php",
               data: fields,
               success: function(data){
                   console.log(data);
                }
           });

       })


    });



    function setChecked(obj,name)
    {
        var check = document.getElementsByName(name+"[]");
        for (var i=0; i<check.length; i++)
        {
            check[i].checked = obj.checked;
        }
        $('tr.CheckedRows').each(function(){
            if(obj.checked)
            {
                $(this).addClass('info');
            }
            else
            {
                $(this).removeClass('info');
            }
        });
    }

    function checkAll()
    {
        let allids = $('#allids');

        if(allids[0].checked === true){
            $('td input[type=checkbox]').each(function() {
                this.checked = true;
            });
        }else{
            $('td input[type=checkbox]').each(function() {
                this.checked = false;
            });
        }

    }

    function checkScan(idscan)
    {
        $elscan = $('#sdoc_'+ idscan);
        $el = $('#check_'+ idscan);
        if($el.prop("checked")){
            $elscan.prop('checked', true);
        }else{
            $elscan.prop('checked', false);
        }

    }

    function ChangePeriod()
    {
        var y = $("select#year").val();
        var m = $("select#month").val();
        location.href = '<?=$arParams['LINK'];?>index.php?ChangePeriod=Y&year='+y+'&month='+m;
    }

    function ChangePeriodNew()
    {
        $('#input-group-list-from-date').removeClass('has-error');
        $('#input-group-list-to-date').removeClass('has-error');
        var datefrom = $("input#list-from-date").val();
        var dateto = $("input#list-to-date").val();
        if ((dateto.length > 0) && (datefrom.length > 0))
        {
            location.href = '<?=$arParams['LINK'];?>index.php?ChangePeriod=Y&datefrom='+datefrom+'&dateto='+dateto;
        }
        else
        {
            if (dateto.length <= 0)
            {
                $('#input-group-list-to-date').addClass('has-error');
            }
            if (datefrom.length <= 0)
            {
                $('#input-group-list-from-date').addClass('has-error');
            }
        }
    }

    function ChangeClient()
    {
        var cl = $("select#client").val();
        location.href = '<?=$arParams['LINK'];?>index.php?ChangeClient=Y&client='+cl;
    }

    function ChangeBranch()
    {
        var br = $("select#branch").val();
        location.href = '<?=$arParams['LINK'];?>index.php?ChangeBranch=Y&branch='+br;
    }
    <?php
    if (($_GET['openprint'] === 'Y') && ((int)$_GET['id'] > 0))
    {
    ?>
    $(document).ready(function() {
        window.open('<?=$arParams['LINK'];?>index.php?mode=print&id=<?=(int)$_GET['id'];?>&print=Y');
        //location.href = '<?=$arParams['LINK'];?>index.php';
    });
    <?php
    }
    if ((isset($_POST['prints_label'])) && (count($_POST['ids']) > 0)):?>
    window.open('<?=$arParams['LINK'];?>index.php?mode=prints&ids=<?=implode(',',$_POST['ids']);?>&label=Y&print=Y');
    <? endif;?>

    <? if ((isset($_POST['prints'])) && (count($_POST['ids']) > 0)):

    if(isset($_POST['scandcs'])):?>

    $(document).ready(function() {
        window.open('<?=$arParams['LINK'];?>index.php?mode=prints&ids=<?=implode(',',$_POST['ids']);?>&scandocs=<?=implode(",",$_POST['scandcs']);?>&print=Y');
    });

    <?php else:?>
    $(document).ready(function() {

        window.open('<?=$arParams['LINK'];?>index.php?mode=prints&ids=<?=implode(',',$_POST['ids']);?>&print=Y');
    });
    <?php
    endif;
    endif;

    if ((isset($_POST['prints_mini'])) && (count($_POST['ids']) > 0)):
    ?>
    $(document).ready(function() {
        window.open('<?=$arParams['LINK'];?>index.php?mode=prints_mini&id=<?=implode(',',$_POST['ids']);?>&print=Y');
    });
    <?php
    endif;
    ?>
</script>
<style>
    .sumdelivery{
        display: block;
    }
</style>

<!-- Modal вызов курьера -->
<div class="modal fade" id="myCallCurier_ids" tabindex="-1" role="dialog"
     aria-labelledby="myCallCurier_ids">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" >Массовый вызов курьера</h4>
            </div>
            <div class="modal-body">
                <form name="form_callcourierdate_ids" method="post">
                    <div style="display: flex; flex-direction: row; align-items: center;
                                                justify-content: space-between; width: 100%">
                        <div class="form-group">
                            <label  for="list-from-date_ids">
                                Вызвать на дату <small style="color:darkred">*обязательное поле</small></label>
                            <div class="input-group" id="input-group-list-from-date_ids">
                                <input  type="hidden" name="data_json" id="data_json" value="" >
                                <input  type="hidden" name="current_client"  value="<?=$arResult['CURRENT_CLIENT']?>" >
                                <input  type="text" class="form-control maskdate"
                                        name="callcourierdate_ids" placeholder="ДД.ММ.ГГГГ"
                                        id="list-from-date_ids">
                                <span style="padding: 6px 12px!important;" class="input-group-addon">
                                    <?php
                                    $APPLICATION->IncludeComponent(
                                        "bitrix:main.calendar",
                                        ".default",
                                        [
                                            "SHOW_INPUT" => "N",
                                            "FORM_NAME" => "form_callcourierdate_ids",
                                            "INPUT_NAME" => "callcourierdate_ids",
                                            "INPUT_NAME_FINISH" => "",
                                            "INPUT_VALUE" => "",
                                            "INPUT_VALUE_FINISH" => false,
                                            "SHOW_TIME" => "N",
                                            "HIDE_TIMEBAR" => "Y",
                                            "INPUT_ADDITIONAL_ATTR" => ''
                                        ],
                                        false
                                    );
                                    ?>
                            </span>
                            </div>
                        </div>
                        <div class="form-group">

                            <label   for="callcourtime_from_ids">Время от:</label>
                            <input   style="width: 100px;" type="text" class="form-control masktime"
                                    id="callcourtime_from_ids" name="callcourtime_from_ids"
                                    placeholder="ЧЧ:ММ" >
                        </div>
                        <div class="form-group">
                            <label  for="callcourtime_to_ids">до:</label>
                            <input  style="width: 100px;" type="text" class="form-control masktime"
                                    id="callcourtime_to_ids" name="callcourtime_to_ids"
                                    placeholder="ЧЧ:ММ">
                        </div>
                    </div>
                    <div class="form-group">
                        <label  for="callcourcomment_ids">Комментарий курьеру:</label>
                        <input  id="callcourcomment_ids" class="form-control"
                                name="callcourcomment_ids" >
                    </div>
                    <div class="modal-footer">
                        <div id="call_courier_form_mess_ids"></div>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                        <button type="submit"  id="call_courier_form_ids"
                                class="btn btn-primary" >Вызвать</button>
                        <!--  form="call_courier_form" type="submit"-->
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>


<div class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" id="modal-for-alert" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div style="background-color: #ffffff;" class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
           <!-- <h4 class="modal-title" id="myModalLabel">Идет формирование отчета</h4>-->
            <h4 class="modal-title" id="myModalLabel">Скачать отчет из 1с </h4>
        </div>
        <div style="background-color: #ffffff;" class="modal-body">
            <small>Отчет выводится из 1с за период, который выбран для показа накладных в ЛК!</small><br>
            <small style="color:red; font-weight: bold">В зависимости от выбранного периода, время формирования отчета будет увеличиваться!</small>
            <div style="margin-top: 20px;display: flex;
            flex-direction: row;
            justify-content: center;" id="alert-preload">
                <img src="/bitrix/components/black_mist/newpartner.invoice.v.2.4/templates/.default/images/preloader.gif" alt="">
            </div>
        </div>
    </div>
</div>
<?php


if (count($arResult["ERRORS"]) > 0)
{
    ?>
    <div class="alert alert-dismissable alert-danger fade in" role="alert">
        <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">X</span><span
                    class="sr-only">Закрыть</span></button>
        <?=implode('</br>',$arResult["ERRORS"]);?>
    </div>
    <?php
}
if (count($arResult["MESSAGE"]) > 0)
{
    ?>
    <div class="alert alert-dismissable alert-success fade in" role="alert">
        <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">X</span><span
                    class="sr-only">Закрыть</span></button>
        <?=implode('</br>',$arResult["MESSAGE"]);?>
    </div>
    <?php
}
if (count($arResult["WARNINGS"]) > 0)
{
    ?>
    <div class="alert alert-dismissable alert-warning fade in" role="alert">
        <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">X</span>
            <span class="sr-only">Закрыть</span></button>
        <?=implode('</br>',$arResult["WARNINGS"]);?>
    </div>
    <?php
}

if ($arResult['OPEN'])
{?>
    <?php if($USER->isAdmin()){
        $finish = microtime(true);
        $delta = $finish - $start;
        AddToLogs('test_logs', ['time_1' => $delta,
            'mess' => 'время до начала вывода']);

        $start = microtime(true);
        AddToLogs('test_logs', ['time_start' => $start, 'mess' => 'Начало вывода шаблона']);
    }
    ?>
    <span id = "current_client" style="visibility: hidden; font-size: 1px"><?=$arResult['CURRENT_CLIENT'] ?></span>
    <div class="row">
        <div class="col-md-3">
            <?php if ($arResult['CURRENT_CLIENT'] > 0):?>
                <div style="display:flex; flex-direction: row; justify-content: start; margin-left: 5px;" class="btn-group">
                    <?php if ((count($arResult['REQUESTS']) > 0) ||  (count($arResult['ARCHIVE']) > 0)) :?>
                    <form style="display: flex; flex-direction: row;" action="<?=$arParams['LINK'];?>index.php?mode=list_xls&pdf=Y" method="post"
                          name="xlsform"  target="_blank">  <input type="hidden" name="DATA"  value="">
                        <?php endif;?>
                        <?php if( !$_SESSION['СontractEndDate']):?>
                            <div class="btn-group" role="group">
                                <a href="<?=$arParams['LINK'];?>index.php?mode=add" class="btn btn-warning testwarn"
                                   id="new_btn"><span class="glyphicon glyphicon-file" aria-hidden="true"></span>
                                    Новая накладная
                                </a>
                            </div>
                            <div class="btn-group" role="group">
                                <a href="<?=$arParams['LINK'];?>index.php" class="btn btn-default" data-toggle="tooltip"
                                   data-placement="bottom"  title="Обновить список накладных">
                                    <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
                                </a>
                            </div>
                            <?php if ((count($arResult['REQUESTS']) > 0) ||  (count($arResult['ARCHIVE']) > 0)) :?>
                                <div class="btn-group" role="group">
                                    <button type="submit" class="btn btn-default" data-toggle="tooltip"
                                            data-placement="bottom" title="Скачать список накладных">
                                        <span class="glyphicon glyphicon-cloud-download" aria-hidden="true"></span>
                                    </button>
                                </div>
                            <?php endif;?>
                            <div class="btn-group" role="group">
                                <a href="<?=$arParams['LINK'];?>index.php?mode=upload" class="btn btn-default"
                                   data-toggle="tooltip" target="_blank"
                                   data-placement="bottom"  title="Загрузить список накладных">
                                    <span class="glyphicon glyphicon-cloud-upload" aria-hidden="true"></span>
                                </a>
                            </div>
                        <?php endif;?>
                        <?php if ((count($arResult['REQUESTS']) > 0) || (count($arResult['ARCHIVE']) > 0)) :?>
                    </form>
                <?php endif;?>
                    <?php
                    // отчет для Абсолют страхование (56103010) и АО «Росгазификация» (62537553)
                   if(!$_SESSION['СontractEndDate'] &&  ($arResult['CURRENT_CLIENT'] == 56103010 ||
                           $arResult['CURRENT_CLIENT'] == 62537553)):?>
                      <div class="btn-group" role="group">
                         <button id = "report_as"  class="btn btn-default" data-toggle="tooltip"
                                 data-placement="bottom" title="Скачать отчет">
                               <i style="font-weight: 600;" class="far fa-file-excel"></i>
                         </button>
                      </div>
                     <?if ($arResult['CURRENT_CLIENT'] == 56103010 ):?>
                       <div class="btn-group" role="group">
                           <button id = "report_from_1c"  class="btn btn-default" data-toggle="tooltip"
                                   data-placement="bottom" title="Скачать отчет из 1с">
                               <i style="font-weight: 600;" class="far fa-file-excel"></i>
                           </button>
                       </div>
                   <?php endif;?>
                    <?php endif; ?>
                    <?php
                    // отчет для Вымпелкома и Абсолюта выводит накладные С Возвратом
                    if(!$_SESSION['СontractEndDate']):?>
                        <form action="<?=$arParams['LINK'];?>api/reprt.php?mode=reportv_xls" method="post"
                              name="xlsvreport" target = "_blank">
                            <input id = "report_return_form_input" type="hidden" name="DATA_REPORTV">
                            <div class="btn-group" role="group">

                            <button disabled id = "report_return_form_submit" type="submit" class="btn btn-default"
                                    data-toggle="tooltip" data-placement="bottom"
                                        title="С Возвратом">
                                   <i style="font-weight: 600;" class="far fa-file-excel"></i>
                            </button>

                            </div>
                        </form>

                    <?php endif;?>
                </div>
            <?php endif;?>

        </div>
        <div class="client-filterform col-md-9 text-right">
            <form action="" method="get" name="filterform" class="form-inline">
                <?php
                if ($arResult['LIST_OF_CLIENTS'])
                {
                    ?>
                    <div class="form-group">
                        <select name="client" size="1" class="form-control selectpicker" id="client" onChange="ChangeClient();" data-live-search="true" data-width="auto">
                            <option value="0"></option>
                            <?php
                            foreach ($arResult['LIST_OF_CLIENTS'] as $k => $v)
                            {
                                $s = ($arResult['CURRENT_CLIENT'] == $k) ? ' selected' : '';
                                ?>
                                <option value="<?=$k;?>"<?=$s;?>><?=$v;?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                    <?php
                }
                if ($arResult['USER_IN_BRANCH'])
                {
                    ?>
                    <div class="form-group">
                        <h3 style="margin:-4px 0 0;">
                            <span class="label label-success">Филиал: <?=$arResult['LIST_OF_BRANCHES'][$arResult['CURRENT_BRANCH']];?></span>
                            <?php  if ($arResult['AGENT']["PROPERTY_TYPE_WORK_BRANCHES_ENUM_ID"] == 301) : ?>
                                <a href="/choice-branch/" class="btn btn-default" title="Выбрать другой филиал">
                                    <span class="glyphicon glyphicon-retweet" aria-hidden="true"></span></a>
                            <?php  endif;?>
                        </h3>
                    </div>
                    <?php
                }
                else
                {
                    if ($arResult['LIST_OF_BRANCHES'])
                    {
                        ?>
                        <div class="form-group">
                            <select name="branch" size="1" class="form-control selectpicker" id="branch"
                                    onChange="ChangeBranch();" data-live-search="true" data-width="auto">
                                <option value="0">Все</option>
                                <?php
                                foreach ($arResult['LIST_OF_BRANCHES'] as $k => $v)
                                {
                                    $s = ($arResult['CURRENT_BRANCH'] == $k) ? ' selected' : '';
                                    ?>
                                    <option value="<?=$k;?>"<?=$s;?>><?=$v;?></option>
                                    <?
                                }
                                ?>
                            </select>
                        </div>
                        <?php
                    }
                }
                ?>
                <div class="form-group">
                    <div class="input-group" id="input-group-list-from-date">
                        <input type="hidden" name="hidden_inn" value="<?=$arResult['CURRENT_CLIENT_INN']?>">
                        <input type="text" class="form-control maskdate" aria-describedby="basic-addon1"
                               name="dateperiodfrom" placeholder="ДД.ММ.ГГГГ" value="<?=$arResult['LIST_FROM_DATE'];?>" onChange="ChangePeriodNew();" id="list-from-date">
                        <span class="input-group-addon" id="basic-addon1">
							<?php
                            $APPLICATION->IncludeComponent(
                                "bitrix:main.calendar",
                                ".default",
                                [
                                    "SHOW_INPUT" => "N",
                                    "FORM_NAME" => "",
                                    "INPUT_NAME" => "dateperiodfrom",
                                    "INPUT_NAME_FINISH" => "",
                                    "INPUT_VALUE" => "",
                                    "INPUT_VALUE_FINISH" => false,
                                    "SHOW_TIME" => "N",
                                    "HIDE_TIMEBAR" => "Y",
                                    "INPUT_ADDITIONAL_ATTR" => ''
                                ],
                                false
                            );
                            ?>
						</span>
                    </div>
                </div>
                <div class="dash form-group ">&nbsp;&mdash;&nbsp;</div>
                <div class="form-group">
                    <div class="input-group" id="input-group-list-to-date">
                        <input type="text" class="form-control maskdate" aria-describedby="basic-addon2" name="dateperiodto" placeholder="ДД.ММ.ГГГГ" value="<?=$arResult['LIST_TO_DATE'];?>" onChange="ChangePeriodNew();" id="list-to-date">
                        <span class="input-group-addon" id="basic-addon2">
							<?php
                            $APPLICATION->IncludeComponent(
                                "bitrix:main.calendar",
                                ".default",
                                [
                                    "SHOW_INPUT" => "N",
                                    "FORM_NAME" => "",
                                    "INPUT_NAME" => "dateperiodto",
                                    "INPUT_NAME_FINISH" => "",
                                    "INPUT_VALUE" => "",
                                    "INPUT_VALUE_FINISH" => false,
                                    "SHOW_TIME" => "N",
                                    "HIDE_TIMEBAR" => "Y",
                                    "INPUT_ADDITIONAL_ATTR" => ''
                                ],
                                false
                            );
                            ?>
						</span>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row"><div class="col-md-12">&nbsp;</div></div>
    <div class="row">
    <div class="col-md-12">

    <?php
    if($USER->isAdmin()){
        $finish = microtime(true);
        $delta = $finish - $start;
        AddToLogs('test_logs', ['time_1' => $delta,
            'mess' => 'время до начала вывода таблицы']);

        $start = microtime(true);
        AddToLogs('test_logs', ['time_start' => $start, 'mess' => 'Начало вывода заголовков таблицы']);
    }
    if ((count($arResult['REQUESTS']) > 0) ||  (count($arResult['ARCHIVE']) > 0))
    {
        //if (count($arResult['REQUESTS']) > 0)
        //{
        ?>


        <form id="call_courier_form" action="?call_courier=Y" method="post"></form>

        <form  action="" method="POST">
            <input type="hidden" name="rand" value="<?=random_int(100000,999999);?>">
            <input type="hidden" name="key_session" value="key_session_<?=random_int(100000,999999);?>">

            <?php
            //}
            $itogo = 0;
            ?>

            <table class="table table-condensed table-hover" data-toggle="table" data-show-columns="true"
                   data-search="true" data-select-item-name="toolbar1" data-height="600" id="tableId" <?/*?> data-sort-name="date" data-sort-order="desc"<?*/?>>
                <thead>
                <tr>
                    <th width="20" data-field="column1" data-switchable="false">
                        <span class="glyphicon glyphicon-print"></span>
                        <input id="allids" onClick="return checkAll()"  type="checkbox" name="allids">
                    </th>

                    <th><span aria-hidden="true" data-toggle="tooltip"
                              data-placement="right" title="Скачать PDF">
                            <i style="color:red" class="far fa-file-pdf"></i>
                        </span>
                    </th>

                    <th width="20" data-field="column2" data-switchable="false">
                        <span class="glyphicon glyphicon-print" aria-hidden="true" data-toggle="tooltip"
                              data-placement="right" title="Печать накладной"></span>
                    </th>
                    <?php
                    // вывод на печать уведомления о доставке для Вымпелкома и Айсберг ЦКБ 49540621
                    if( $arResult['CURRENT_CLIENT'] == 56280706 ||
                        $arResult['CURRENT_CLIENT'] == 56389269 ||
                        $arResult['CURRENT_CLIENT'] == 56389270 ||
                        $arResult['CURRENT_CLIENT'] == 56389272 ||
                        $arResult['CURRENT_CLIENT'] == 49540621):?>
                        <th width="20">
                            <span class="glyphicon glyphicon-bullhorn" aria-hidden="true"></span>
                        </th>
                    <?php endif;?>

                    <th width="20" data-field="column14" data-switchable="false">
                        <span class="glyphicon glyphicon-zoom-in" aria-hidden="true" data-toggle="tooltip"
                              data-placement="right" title="Просмотр накладной"></span>
                    </th>
                    <th width="20"  aria-hidden="true"
                        data-toggle="tooltip" data-placement="right"
                        title="Скачать сканы накладных">
                        <span class="glyphicon glyphicon-paperclip"> </span>
                    </th>

                    <?php
                    /* Центр затрат для Абсолют страхование || $USER->isAdmin()*/
                    if($arResult['CURRENT_CLIENT'] == 56103010 ):?>
                        <th data-switchable="false" data-sortable="true">
                            <?=GetMessage('TABLE_HEAD_14');?>
                        </th>
                    <?php endif;?>
                    <?php if (count($arResult['REQUESTS']) > 0 ):?>
                        <th width="20" data-field="column22" data-switchable="false"></th>
                    <?php endif;?>
                    <th data-field="number" data-switchable="false" data-sortable="true">
                        <?=GetMessage('TABLE_HEAD_1');?>
                    </th>
                    <?php

                    // Сумма доставки для инд. прайс

                    if(!$arResult['IndividualPrice'] && $arResult['CURRENT_CLIENT'] != 41478141):?>
                        <th>
                            <?=GetMessage('SUMM_DEV');?>
                        </th>
                    <?php endif;?>
                    <?php
                    // или для тех у кого общий прайс
                     if($arResult['IndividualPrice'] && $arResult['CURRENT_CLIENT'] != 41478141):
                        // почему то у Сухого нет в 1с признака Инд. прайс
                        ?>
                        <th>
                            <?=GetMessage('SUMM_DEV');?>
                        </th>
                    <?php endif?>
                    <?php if($arResult['CURRENT_CLIENT'] != 41478141):?>
                        <th>
                        <span  class="glyphicon glyphicon-bell" style="color: #555555; font-size: 14px; ">
                        </span>
                        </th>
                    <?php endif;?>
                    <th width="20" data-field="column15" data-switchable="false" data-sortable="false"></th>

                    <?php
                    // $arResult['CURRENT_CLIENT'] === '36015676' ||
                    if($arResult['CURRENT_CLIENT'] == 36015676):?>
                        <th>Примечание</th>
                    <?php endif;?>
                    <?php
                    // внутр. номер показывать только Сухому
                    if($arResult['CURRENT_CLIENT'] == 41478141 ):?>
                        <th data-field="column19" data-sortable="true"
                            data-switchable="true" width="20">Вн. номер заявки.
                        </th>
                    <?php endif;?>
                    <th data-field="column4" data-sortable="true"><?=GetMessage('TABLE_HEAD_2');?></th>

                    <th data-field="date" data-sortable="true"><?=GetMessage('TABLE_HEAD_3');?></th>
                    <?php // echo "..."; внутренний номер заявки  PROPERTY_SHOW_HIDDEN_INN_NUMBER ?>
                    <?php if (($arResult['LIST_OF_BRANCHES']) && (!$arResult['USER_IN_BRANCH']))
                    {
                        ?>
                        <th data-field="column22" data-sortable="true">Филиал</th>
                        <?php
                    }
                    ?>
                    <th data-field="column6" data-sortable="true"><?=GetMessage('TABLE_HEAD_4');?></th>
                    <th data-field="column7" data-sortable="true"><?=GetMessage('TABLE_HEAD_5');?></th>
                    <th data-field="column17" data-sortable="true"><?=GetMessage('TABLE_HEAD_13');?></th>
                    <th data-field="column8" data-sortable="true"><?=GetMessage('TABLE_HEAD_7');?></th>
                    <th data-field="column9" data-sortable="true"><?=GetMessage('TABLE_HEAD_6');?></th>
                    <th data-field="column16" data-sortable="true"><?=GetMessage('TABLE_HEAD_12');?></th>
                    <th data-field="column10"><?=GetMessage('TABLE_HEAD_8');?></th>
                    <th data-field="column11"><?=GetMessage('TABLE_HEAD_9');?></th>
                    <th data-field="column12"><?=GetMessage('TABLE_HEAD_10');?></th>
                    <th data-field="column13" data-sortable="true"><?=GetMessage('TABLE_HEAD_11');?></th>
                    <th data-field="column20" data-sortable="true" data-switchable="true" width="20">Ответственный</th>
                    <th data-field="column21" width="20"> </th>
                    <th data-switchable="false" data-sortable="true">С Возвратом</th>

                </tr>
                </thead>

                <tbody>
                <?php
                if($USER->isAdmin()){
                    $finish = microtime(true);
                    $delta = $finish - $start;
                    AddToLogs('test_logs', ['time_1' => $delta,
                        'mess' => 'время вывода заголовков таблицы']);

                    $start = microtime(true);
                    AddToLogs('test_logs', ['time_start' => $start, 'mess' => 'Начало вывода таблицы после заголовков REQUESTS']);
                }

                $path_pdf = $_SERVER['DOCUMENT_ROOT'] . '/upload/pdf';

                // /var/www/admin/www/delivery-russia.ru/upload/pdf

        if (!empty($arResult['REQUESTS'])):
            foreach ($arResult['REQUESTS'] as $r):
                if(trim($r['PROPERTY_CALLING_COURIER_VALUE']) !== 'Y'){
                    $flag_cc = true;
                }else{
                    $flag_cc = false;
                }
                ?>
                <tr class="a1 <?=$r['ColorRow'];?>">
                    <td class="a1" width="20">
                        <?php
                        if ($r['PROPERTY_STATE_ENUM_ID'] == 257):?>
                            <input type="checkbox" name="ids[]" value="<?=$r['ID'];?>">
                         <?php endif;?>
                    </td>
                    <td>
                        <a  href="/upload/pdf/<?=$r['NAME']?>.pdf"  target="_blank">
                            <i  style="color:red" class="far fa-file-pdf"></i>
                        </a>
                    </td>
                    <td class="a2" data-halign="center" data-align="center" data-valign="center">
                       <?php
                        /* для клиента сухого */
                        if (($arResult['CURRENT_CLIENT'] == ID_SUKHOI) || ($arResult['CURRENT_CLIENT'] == ID_TEST)):?>
                            <a href="/index.php?mode=printsukhoi&id=<?=$r['ID'];?>&printsukhoi=Y&print=Y" target="_blank">
                            	<span class="glyphicon glyphicon-print" aria-hidden="true" data-toggle="tooltip"
                                      data-placement="right" title="Печать накладной"></span>
                            </a>
                        <?php  else:?>
                            <a href="/index.php?mode=print&id=<?=$r['ID'];?>&print=Y" target="_blank">
                            	<span class="glyphicon glyphicon-print" aria-hidden="true" data-toggle="tooltip"
                                      data-placement="right" title="Печать накладной"></span>
                            </a>
                        <?php  endif; ?>
                    </td>
                    <?php
                    // вывод на печать уведомления о доставке для Вымпелкома и Айсберг ЦКБ
                    if( $arResult['CURRENT_CLIENT'] == 56280706 ||
                        $arResult['CURRENT_CLIENT'] == 56389269 ||
                        $arResult['CURRENT_CLIENT'] == 56389270 ||
                        $arResult['CURRENT_CLIENT'] == 56389272 ||
                        $arResult['CURRENT_CLIENT'] == 49540621 ||
                        $USER->isAdmin()):?>
                        <td></td>
                        <?php endif;?>
                    <td class="a3" data-halign="center" data-align="center" data-valign="center">
                        <?php
                        if ((($r['PROPERTY_STATE_ENUM_ID'] == 257) && (!$arResult['ADMIN_AGENT'])) ||
                            ($arResult['CURRENT_CLIENT'] == ID_TEST)):?>
                            <a href="/index.php?mode=edit&id=<?=$r['ID'];?>"><span class="glyphicon glyphicon-pencil"
                            aria-hidden="true" data-toggle="tooltip" data-placement="right"
                            title="Редактирование накладной"></span>
                            </a>
                            <?php else:?>
                            <a href="<?=$arParams['LINK'];?>index.php?mode=invoice_modal&id=<?=$r['ID'];?>&pdf=Y"
                               data-toggle="modal" data-target="#modal_<?=$r['ID'];?>">
                                	<span class="glyphicon glyphicon-zoom-in" aria-hidden="true" data-toggle="tooltip"
                                          data-placement="right" title="Просмотр накладной"></span>
                            </a>
                            <?php endif;?>
                    </td>

                    <?php if($arResult['CURRENT_CLIENT'] != 41478141 ):?>
                        <td></td>
                    <?php endif;?>
                    <?php
                    /* Центр затрат Абсолют страхование */
                    if($arResult['CURRENT_CLIENT'] == 56103010):?>
                        <td >
                            <span><?=$r['CENTER_EXPENSES_NAME']?></span>
                        </td>
                    <?php endif;?>
                     <td class="a21" width="20">
                        <?php if($arResult['CURRENT_CLIENT'] != 41478141 ):?>
                            <span style="cursor: pointer;" data-toggle="modal"
                                  data-target="#myModal_<?=$r['ID'];?>"
                                  class="glyphicon glyphicon-trash"></span>
                        <?php endif;?>
                    </td>
                    <td>
                        <?php if($arResult['CURRENT_CLIENT'] == 41478141 ):?>
                            <span style="cursor: pointer;" data-toggle="modal"
                                  data-target="#myModal_<?=$r['ID'];?>"
                                  class="glyphicon glyphicon-trash"></span>
                        <?php endif;?>
                        <?php if($arResult['CURRENT_CLIENT'] != 41478141 ):?>
                            <span  class="numberinvoice">
                                    <?=$r['NAME'];?>
                            </span>
                        <?php endif;?>
                    </td>
                    <?php
                    //Стоимость доставки для клиентов с инд прайсом
                    if( !$arResult['IndividualPrice'] && $arResult['CURRENT_CLIENT'] != 41478141):
                        $client =  $arResult['CURRENT_CLIENT'];
                        ?>
                        <td >
                            <?=$r['PROPERTY_SUMM_DEV_VALUE']?>
                        </td>
                    <?php endif;?>
                    <?php
                    // стоимость доставки для клиентов без инд. прайса
                      if($arResult['IndividualPrice'] && $arResult['CURRENT_CLIENT'] != 41478141):
                          // почему то у Сухого нет в 1с признака Инд. прайс
                          ?>
                        <td>
                            <?=$r['PROPERTY_SUMM_DEV_VALUE']?>
                        </td>
                      <?endif;?>
                       <td>
                        <?php if($flag_cc && $arResult['CURRENT_CLIENT'] != 41478141 ):?>
                            <span style="color: #f36104; font-size: 14px; cursor: pointer" data-toggle="modal"
                                  data-target="#myCallCurier_<?=$r['ID'];?>">
                                    <span class="glyphicon glyphicon-bell" aria-hidden="true" data-toggle="tooltip"
                                          data-placement="right" title="Кликните чтобы вызвать курьера.">
                                    </span>
                                </span>
                        <?php elseif(!$flag_cc && $arResult['CURRENT_CLIENT'] != 41478141):?>
                        <span style="color: #56363534; font-size: 14px; cursor: pointer">
                                    <span class="glyphicon glyphicon-bell" aria-hidden="true" data-toggle="tooltip"
                                          data-placement="right" title="Курьер вызван">
                             </span>
                        <?php endif;?>
                            <?php
                            // внутр. номер показывать только Сухому
                            if ($arResult['CURRENT_CLIENT'] == 41478141):?>
                                <?=$r['NAME'];?>
                            <?php endif;?>
                    </td>
                    <td class="a4" width="20"><?=$r['state_icon'];?></td>

                    <?php
                    // $arResult['CURRENT_CLIENT'] === '36015676' ||
                    if($arResult['CURRENT_CLIENT'] == 36015676):?>
                        <td>
                           
                        </td>
                    <?php endif;?>
                    <?php if($arResult['CURRENT_CLIENT'] == 41478141 ):?>
                        <td class="a5" width="20">
                            <? // echo "..."; внутренний номер заявки  :: PROPERTY_SHOW_HIDDEN_INN_NUMBER ?>
                            <?=$r['PROPERTY_INNER_NUMBER_CLAIM_VALUE'];?>
                        </td>
                    <?php endif;?>

                    <td class="a6"><?=$r['state_text'];?></td>

                    <td class="a7">
                        <?php
                        /* Абсолют страхование показать дату создания накладной  */
                        if($arResult['CURRENT_CLIENT'] == 56103010){
                            echo $r['DATE_CREATE'];
                        }else{
                            echo substr($r['DATE_CREATE'],0,10);
                        }
                        ?>

                    </td>
                    <?php
                    if (($arResult['LIST_OF_BRANCHES']) && (!$arResult['USER_IN_BRANCH']))
                    {
                        ?>
                        <td class="a20"><?=$r['PROPERTY_BRANCH_NAME'];?></td>
                        <?php
                    }
                    ?>
                    <td class="a8"><?=$r['PROPERTY_CITY_SENDER_NAME'];?></td>
                    <td class="a9"><?=$r['PROPERTY_COMPANY_SENDER_VALUE'];?></td>
                    <td class="a10"><?=$r['PROPERTY_NAME_SENDER_VALUE'];?></td>
                    <td class="a11"><?=$r['PROPERTY_CITY_RECIPIENT_NAME'];?></td>
                    <td class="a12"><?=$r['PROPERTY_COMPANY_RECIPIENT_VALUE'];?></td>
                    <td class="a13"><?=$r['PROPERTY_NAME_RECIPIENT_VALUE'];?></td>
                    <td class="a14"><?=$r['PROPERTY_PLACES_VALUE'];?></td>
                    <td class="a15"><?=WeightFormat($r['PROPERTY_WEIGHT_VALUE'], false);?></td>
                    <td class="a16"><?=WeightFormat($r['PROPERTY_OB_WEIGHT'],false);?></td>
                    <td class="a17"><?=WeightFormat($r['PROPERTY_RATE_VALUE'],false);?></td>
                    <?php
                    $obElement = CIBlockElement::GetByID($r['ID']);
                    if($arEl = $obElement->GetNext())
                    {
                        $rsUser = CUser::GetByID($arEl["CREATED_BY"]);
                        $arUser = $rsUser->Fetch();
                        $Property_creator_name = $arUser["NAME"]." ".$arUser["LAST_NAME"];
                    }
                    ?>
                    <td class="a18"> <?=$Property_creator_name?> </td>
                    <td class="a19">
                        <!-- оплачивает(1): (  <?=$r['PROPERTY_TYPE_PAYS_VALUE'];?> -- <?=$r['PROPERTY_PAYS_VALUE'];?> -- <?=$r['PROPERTY_WHOSE_ORDER_VALUE'];?>  )-->
                        <a href="<?=$arParams['LINK'];?>index.php?mode=add&copyfrom=<?=$r['ID'];?>&copy=Y"><span class="glyphicon glyphicon-copy" aria-hidden="true"></span></a>
                    </td>

                        <td >
                            <span id="ret_<?=$r['NAME']?>" class='withreturn'></span>

                        </td>

                </tr>
                <?php
                $itogo  = $itogo  + $r['PROPERTY_RATE_VALUE'];?>

                <!-- Modal Удалить накладную -->
                <div class="modal fade" id="myModal_<?=$r['ID']?>" tabindex="-1" role="dialog"
                     aria-labelledby="myModal_<?=$r['ID']?>">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"
                                        aria-label="Close"><span aria-hidden="true">&times;</span></button>

                            </div>
                            <div class="modal-body">
                                <h4 class="modal-title" >Удалить накладную <?=$r['NAME']?>?</h4>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                                <a type="button" class="btn btn-primary" href="/index.php?mode=delone&n=<?=$r['ID'];?>&name=<?=$r['NAME'];?>">Удалить</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal вызов курьера -->
                <div class="modal fade" id="myCallCurier_<?=$r['ID']?>" tabindex="-1" role="dialog"
                     aria-labelledby="myCallCurier_<?=$r['ID']?>">
                    <div class="modal-dialog modal-md" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" >Вызов курьера для накладной <?=$r['NAME']?></h4>
                            </div>
                            <div class="modal-body">
                                <div style="display: flex; flex-direction: row; align-items: center;
                                                justify-content: space-between; width: 100%">
                                    <div class="form-group">
                                        <label form="call_courier_form" for="list-from-date_<?=$r['ID']?>">
                                            Вызвать на дату <small style="color:darkred">*обязательное поле</small></label>
                                        <div class="input-group" id="input-group-list-from-date_<?=$r['ID']?>">

                                            <input form="call_courier_form" type="text" class="form-control maskdate"
                                                   name="callcourierdate_<?=$r['ID']?>" placeholder="ДД.ММ.ГГГГ"
                                                   id="list-from-date_<?=$r['ID']?>">
                                            <span style="padding: 6px 12px!important;" class="input-group-addon">
                                       							<?php
                                                                $APPLICATION->IncludeComponent(
                                                                    "bitrix:main.calendar",
                                                                    ".default",
                                                                    array(
                                                                        "SHOW_INPUT" => "N",
                                                                        "FORM_NAME" => "call_courier_form",
                                                                        "INPUT_NAME" => "callcourierdate_".$r['ID'],
                                                                        "INPUT_NAME_FINISH" => "",
                                                                        "INPUT_VALUE" => "",
                                                                        "INPUT_VALUE_FINISH" => false,
                                                                        "SHOW_TIME" => "N",
                                                                        "HIDE_TIMEBAR" => "Y",
                                                                        "INPUT_ADDITIONAL_ATTR" => ''
                                                                    ),
                                                                    false
                                                                );
                                                                ?>
                                       						</span>
                                        </div>
                                    </div>
                                    <div class="form-group">

                                        <input form="call_courier_form" type="hidden" name="id_<?=$r['ID']?>" value="<?=$r['ID']?>">
                                        <input  form="call_courier_form" type="hidden" name="name_<?=$r['ID']?>" value="<?=$r['NAME']?>">
                                        <label  form="call_courier_form" for="callcourtime_from_<?=$r['ID']?>">Время от:</label>
                                        <input  form="call_courier_form" style="width: 100px;" type="text" class="form-control masktime"
                                                id="callcourtime_from_<?=$r['ID']?>" name="callcourtime_from_<?=$r['ID']?>"
                                                placeholder="ЧЧ:ММ" >
                                    </div>
                                    <div class="form-group">
                                        <label  form="call_courier_form" for="callcourtime_to_<?=$r['ID']?>">до:</label>
                                        <input  form="call_courier_form" style="width: 100px;" type="text" class="form-control masktime"
                                                id="callcourtime_to_<?=$r['ID']?>" name="callcourtime_to_<?=$r['ID']?>"
                                                placeholder="ЧЧ:ММ" >
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label  form="call_courier_form" for="callcourcomment_<?=$r['ID']?>">Комментарий курьеру:</label>
                                    <input  form="call_courier_form" id="callcourcomment_<?=$r['ID']?>" class="form-control"
                                            name="callcourcomment_<?=$r['ID']?>" >
                                </div>
                                <div class="modal-footer">
                                    <div id="call_courier_form_mess_<?=$r['ID'];?>"></div>
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                                    <button form="call_courier_form" type="submit"  id="call_courier_form_<?=$r['ID'];?>"
                                            class="btn btn-primary" >Вызвать</button>
                                    <!--  form="call_courier_form" type="submit"-->
                                </div>
                            </div>
                        </div>
                        <script>

                            $("#call_courier_form_<?=$r['ID'];?>").on('click', function () {
                                $(this).attr('style', 'visibility: hidden');
                                $("#call_courier_form_mess_<?=$r['ID'];?>").html("<p style='color: #287dd6; height: 50px' > Подождите, идет вызов курьера... </p>");
                            });
                        </script>
                    </div>
            </div>
            <?php endforeach;
            endif;
            ?>
<?php
if($USER->isAdmin()){
    $finish = microtime(true);
    $delta = $finish - $start;
    AddToLogs('test_logs', ['time_1' => $delta,
        'mess' => 'время вывода полей таблицы  таблицы REQUESTS']);

    $start = microtime(true);
    AddToLogs('test_logs', ['time_start' => $start, 'mess' => 'Начало вывода полей таблицы ARCHIVE']);
}
?>
            <?php

            foreach ($arResult['ARCHIVE'] as $r)
                    {
                        // вывод на печать уведомления о доставке для Вымпелкома и Айсберг ЦКБ

                        if( $arResult['CURRENT_CLIENT'] == 56280706 ||
                            $arResult['CURRENT_CLIENT'] == 56389269 ||
                            $arResult['CURRENT_CLIENT'] == 56389270 ||
                            $arResult['CURRENT_CLIENT'] == 56389272 ||
                            $arResult['CURRENT_CLIENT'] == 49540621 ):?>
                            <?php if ($r['state_text'] === 'Доставлено'){
                                $state_v = 'Y';
                                $number_v = $r['NAME'];
                                $date_create = substr($r['DATE_CREATE'],0,10);
                                $key_arr = count($r['Events'])-1;
                                $info_event = $r['Events'][$key_arr]['InfoEvent'];
                                $date_event = $r['Events'][$key_arr]['Date'];
                                $company_rec = $r['PROPERTY_COMPANY_RECIPIENT_VALUE'];
                                $city_rec = $r['PROPERTY_CITY_RECIPIENT_NAME'];
                                $ar_json = [
                                    'number_v'     => $number_v,
                                    'date_create'  => $date_create,
                                    'info_event'   => $info_event,
                                    'date_event'   => $date_event,
                                    'company_rec'  => $company_rec,
                                    'city_rec'     => $city_rec
                                ];
                                $ar_json = convArrayToUTF($ar_json);
                                $str_json = json_encode($ar_json);
                                $url_data = urlencode ($str_json);
                            }else{
                                $state_v = 'N';
                            }?>
                         <?php endif;?>
                        <tr class="b1 <?=$r['ColorRow'];?>">
                            <td class="b2" width="20">
                                <input id="check_<?=$r['ID'];?>" onclick="return checkScan(<?=$r['ID'];?>)"
                                       type="checkbox" name="ids[]" value="f001=<?=$r['NAME'];?>">
                            </td>
                            <td>
                               <a target="_blank" href="/upload/pdf/<?=$r['NAME']?>.pdf">
                                  <i  style="color:red" class="far fa-file-pdf"></i>
                               </a>
                            </td>
                            <td class="b3"  data-halign="center" data-align="center" data-valign="center">
                                <?php // выводим накладные в печатной форме для "сухого"
                                if (strlen(trim($r['NAME']))) {?>

                                    <?php if (($arResult['CURRENT_CLIENT'] == ID_SUKHOI) ||
                                        ($arResult['CURRENT_CLIENT'] == ID_TEST))  { ?>
                                        <a href="/index.php?mode=invoice1c_printsukhoi&f001=<?=$r['NAME'];?>&printsukhoi=Y&print=Y" target="_blank">
                                            <span class="glyphicon glyphicon-print" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Печать накладной"></span>
                                        </a>
                                    <?php } else { ?>
                                        <a href="/index.php?mode=invoice1c_print&f001=<?=$r['NAME'];?>&print=Y" target="_blank">
                                            <span class="glyphicon glyphicon-print" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Печать накладной"></span>
                                        </a>
                                        <?php
                                    }
                                }
                                ?>
                            </td>
                            <?php
                            /* уведомление о доставке  */
                            if($state_v === "Y" ):?>
                                <td>
                                    <a target="_blank" href="index.php?mode=print_notification&data=<?=$url_data;?>&print=Y">
                                    <span class="glyphicon glyphicon-bullhorn" aria-hidden="true"
                                          data-toggle="tooltip" data-placement="right"
                                          title="Печать уведомления"></span>
                                    </a>
                                </td>
                            <?php elseif($state_v === "N"):?>
                                <td></td>
                            <?php endif;?>
                            <td class="b4" data-halign="center" data-align="center" data-valign="center">
                                <?php if (strlen(trim($r['NAME']))):?>
                                    <a href="<?=$arParams['LINK'];?>index.php?mode=invoice1c_modal&f001=<?=$r['NAME'];?>&pdf=Y" data-toggle="modal" data-target="#modal_inv1c_<?=$r['NAME'];?>">
                                <span class="glyphicon glyphicon-zoom-in" aria-hidden="true"
                                      data-toggle="tooltip" data-placement="right"
                                      title="Просмотр накладной"></span>
                                    </a>
                                <?php endif;?>
                            </td>

                            <td >
                                <?php if(!empty($r['SCAN_DOCS_PATH'])):?>
                                    <a  data-toggle="modal"
                                        data-target="#modal_scan_<?=$r['ID'];?>" href="">
                                       <span  aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Скачать сканы"
                                              style="cursor:pointer" class="glyphicon glyphicon-paperclip">
                                </span>
                                    </a>
                                    <?php $sdocs = implode(",", $r['SCAN_DOCS_PATH']);?>
                                    <input style="display:none;" id="sdoc_<?=$r['ID'];?>" type="checkbox" name="scandcs[]" value = "<?=$sdocs;?>">
                                <?php endif;?>
                            </td>
                            <?php
                            /* Центр затрат Абсолют страхование $arResult['CURRENT_CLIENT'] == 56103010 || $USER->isAdmin()*/
                            if($arResult['CURRENT_CLIENT'] == 56103010):?>
                                <td >
                                    <?php if($r['center_cost']):?>
                                       <span><?=$r['center_cost'];?></span>
                                    <?php endif;?>
                                </td>
                            <?php endif;?>

                            <?php if (count($arResult['REQUESTS']) > 0):?>
                                <td class="b22" width="20">
                                </td>
                            <?php endif;?>
                            <td  class="b5" >
                                <span class="numberinvoice" <?php if($r['state_text'] === 'Доставлено'){echo " delivered='Y'";}?>>
                                    <?=$r['NAME'];?>
                                </span>
                            </td>
                            <?php
                            //  Стоимость доставки для клиентов с инд. прайсом
                            if(!$arResult['IndividualPrice'] && $arResult['CURRENT_CLIENT'] != 41478141):?>
                                <td >
                                    <?php if($r['Tarif']):?>
                                      <span>
                                          <?=$r['Tarif']?>
                                      </span>
                                    <?else:
                                        $id_from_name = $r['NAME'];
                                        if(preg_match('&[\/]+&', $r['NAME'])){
                                            $id_from_name = preg_replace('&\/&','__', $r['NAME']);
                                        } ?>
                                        <?php if($_COOKIE['tarif_'.$id_from_name]):?>
                                              <span><?=iconv('utf-8', 'windows-1251',$_COOKIE['tarif_'.$id_from_name]);?></span>
                                        <?php else:?>
                                        <span style="cursor: pointer;" id = "tarif_<?=$id_from_name?>" class="glyphicon glyphicon-repeat"
                                         aria-hidden="true" data-toggle="tooltip" data-placement="left" title="Запрос цены"
                                         data-typedev="<?=$r['delivery_t'];?>">
                                        </span>
                                        <?php endif;?>
                                    <?php endif;?>
                                </td>
                            <?php endif;?>
                            <?php
                            // стоимость доставки для клиентов без инд. прайс
                            if($arResult['IndividualPrice'] && $arResult['CURRENT_CLIENT'] != 41478141):
                                // почему то у Сухого нет в 1с признака Инд. прайс
                                ?>
                                <td>
                                    <?php if($r['Tarif']):?>
                                        <span>
                                          <?=$r['Tarif']?>
                                        </span>
                                    <?else:
                                        $id_from_name = $r['NAME'];
                                        if(preg_match('&[\/]+&', $r['NAME'])){
                                            $id_from_name = preg_replace('&\/&','__', $r['NAME']);
                                        } ?>
                                        <?php if($_COOKIE['tarif_no_'.$id_from_name]):?>
                                        <span><?=iconv('utf-8', 'windows-1251',$_COOKIE['tarif_no_'.$id_from_name]);?></span>
                                        <?php else:?>
                                            <span style="cursor: pointer;" id = "tarif_no_<?=$id_from_name?>" class="glyphicon glyphicon-repeat"
                                                  aria-hidden="true" data-toggle="tooltip" data-placement="left" title="Запрос цены">
                                            </span>
                                        <?php endif;?>
                                    <?php endif;?>
                                </td>
                            <?endif;?>
                            <?php if($arResult['CURRENT_CLIENT'] != 41478141 ):?>
                                <td>
                                </td>
                            <?php endif;?>
                            <td  class="b6"  width="20">
                                <a href="" data-toggle="modal" data-target="#modal_tr_<?=$r['ID'];?>">
                                    <?=$r['state_icon'];?>
                                </a>
                            </td>
                            <?php
                            // $arResult['CURRENT_CLIENT'] === '36015676' ||
                            if($arResult['CURRENT_CLIENT'] == 36015676):?>
                                <td>
                                   
                                </td>
                            <?php endif;?>
                            <?php
                            // внутр. номер показывать только Сухому
                            if($arResult['CURRENT_CLIENT'] == 41478141 ):?>
                                <td  class="b7"  width="20"><?=$r['test'];?></td>
                            <?php endif;?>
                            <td  class="b8" >
                                <span id = "stat_<?=$r['NAME']?>"><?=$r['state_text'];?></span><br />
                                <span id = "stat_date_<?=$r['NAME']?>"><?=$r['PROPERTY_STATE_DATE_VALUE'];?></span><br />
                            </td>
                            <td  class="b9">
                                <?php
                                /* Абсолют страхование показать дату создания накладной  */
                                if($arResult['CURRENT_CLIENT'] == 56103010){
                                   echo ($r['DATE_CREATE']);
                                }else{
                                    echo substr($r['DATE_CREATE'],0,10);
                                }?>
                            </td>
                            <?php
                            if (($arResult['LIST_OF_BRANCHES']) && (!$arResult['USER_IN_BRANCH']))
                            {
                                ?>
                                <td class="b23"><?=$r['PROPERTY_BRANCH_NAME'];?></td>
                                <?php
                            }?>
                            <td   class="b10" ><?=$r['PROPERTY_CITY_SENDER_NAME'];?></td>
                            <td   class="b11" ><?=$r['PROPERTY_COMPANY_SENDER_VALUE'];?></td>
                            <td   class="b12" ><?=$r['PROPERTY_NAME_SENDER_VALUE'];?></td>
                            <td   class="b13" ><?=$r['PROPERTY_CITY_RECIPIENT_NAME'];?></td>
                            <td   class="b14" ><?=$r['PROPERTY_COMPANY_RECIPIENT_VALUE'];?></td>
                            <td   class="b15" ><?=$r['PROPERTY_NAME_RECIPIENT_VALUE'];?></td>
                            <td   class="b16" ><?=$r['PROPERTY_PLACES_VALUE'];?></td>
                            <td   class="b17"  ><?=WeightFormat($r['PROPERTY_WEIGHT_VALUE'], false);?></td>
                            <td   class="b18" ><?=WeightFormat($r['PROPERTY_OB_WEIGHT'],false);?></td>
                            <td   class="b19" ><?=WeightFormat($r['PROPERTY_RATE_VALUE'],false);?></td>

                            <?php
                            $obElement = CIBlockElement::GetByID($r['ID']);
                            if($arEl = $obElement->GetNext())
                            {
                                $rsUser = CUser::GetByID($arEl["CREATED_BY"]);
                                $arUser = $rsUser->Fetch();
                                $Property_creator_name = $arUser["NAME"]." ".$arUser["LAST_NAME"];
                            }
                            ?>
                            <td class="b20"><?=$Property_creator_name;?></td>
                            <td class="b21">
                                <a href="<?=$arParams['LINK'];?>index.php?mode=add&copyfrom=<?=$r['ID_SITE'];?>&copy=Y&numdoc=<?=$r['NAME']?>">
                        		<span class="glyphicon glyphicon-copy"
                                      aria-hidden="true" data-toggle="tooltip"
                                      data-placement="left" title="Копировать"></span>
                                </a>
                            </td>

                                <td>
                                    <span id="ret_<?=$r['NAME']?>" class='withreturn'></span>
                                </td>

                        </tr>
                        <?php
                        $itogo  = $itogo  + $r['PROPERTY_RATE_VALUE'];?>
                        <!-- Modal -->
                        <?php if(!empty($r ['SCAN_DOCS_PATH'])):?>
                        <div class="modal fade" id="modal_scan_<?=$r['ID'];?>" tabindex="-1" role="dialog"
                             aria-labelledby="myModalLabel">
                            <div class="modal-dialog" role="document">

                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <h3>№ <?=$r['NAME']?></h3>
                                    </div>
                                    <div style="padding-bottom: 20px;" class="row">
                                        <?php $count = count($r ['SCAN_DOCS_PATH']);?>
                                        <h4 style="margin-left: 32px;">Скачать сканы, прикрепленные к документу (<?=$count;?> шт. )</h4>
                                        <ul>
                                            <?php
                                            //dump($r ['SCAN_DOCS_PATH']);
                                            foreach($r ['SCAN_DOCS_PATH'] as $key=>$value):?>
                                                <?php $ext = getExtensionPath($value);?>
                                                <li style="list-style: decimal ">
                                                    <div class="col-md-12">
                                                        <a target="_blank" href="http://<?=$value;?>">
                                                            Скачать скан накладной (<?=$ext;?>)
                                                        </a>
                                                    </div>
                                                </li>
                                            <?php endforeach;?>
                                        </ul>
                                    </div>


                                </div>
                            </div>
                        </div>
                    <?php endif;  }
                    ?>
                </tbody>
            </table>
            <p>Всего накладных: <?=(count($arResult['REQUESTS'])+count($arResult['ARCHIVE']));?></p>
            <div style="display:flex; flex-direction: row; justify-content: end">
                <div style="margin-bottom:50px;" class="btn-group" role="group" aria-label="...">
                    <button type="submit"  name="prints" value="Распечатать накладные"
                            class="btn btn-warning testwarn">Распечатать отмеченные накладные</button>

                </div>
                <div style="margin-bottom:50px; margin-left:20px;" class="btn-group" role="group" aria-label="...">
                    <button type="submit"  name="prints_label" value="Распечатать этикетки"
                            class="btn btn-warning testwarn">Распечатать этикетки</button>

                </div>
            </div>
        </form>
        </div>
        </div>

        <?php if ($arResult['AGENT']['PROPERTY_SHOW_LIMITS_VALUE'] == 1) : ?>
        <div class="row">
            <div class="col-md-3"><i>Итого за месяц: <strong><?=number_format($itogo, 2, ',', ' ');?></strong></i></div>
            <?php
            if ($arResult['LIMITS_OF_BRANCHES'])
            {
                ?>
                <div class="col-md-3 text-center">
                    <i>Итого за <?=$arResult['QW_TEXT'];?> квартал: <strong><?=number_format($arResult['All_SPENT'], 2, ',', ' ');?></strong></i> <span class="label <?=$arResult['LABEL_CLASS'];?>"><?=$arResult['All_PERSENT'];?></span></div>
                <div class="col-md-3 text-center"><i>Лимит за <?=$arResult['QW_TEXT'];?> квартал:<strong><?=number_format($arResult['All_LIMIT'], 2, ',', ' ');?></strong></i></div>
                <div class="col-md-3 text-right"><i>Осталось за <?=$arResult['QW_TEXT'];?> квартал:<strong><?=number_format($arResult['All_LEFT'], 2, ',', ' ');?></strong></i></div>
                <?php
            }?>
        </div>
    <?php
    endif;
        if (count($arResult['REQUESTS']) > 0):?>
            <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" id="modal_<?=$r['ID'];?>" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                    </div>
                </div>
            </div>

        <?php
        endif;

        foreach ($arResult['REQUESTS'] as $r)
        {?>
            <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" id="modal_<?=$r['ID'];?>" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                    </div>
                </div>
            </div>
            <?php
        }

        foreach ($arResult['ARCHIVE'] as $r)
        {?>
            <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" id="modal_inv1c_<?=$r['NAME'];?>" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                    </div>
                </div>
            </div>
            <div class="modal fade" tabindex="-1" role="dialog" id="modal_tr_<?=$r['ID'];?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12" class="text-right">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <p>&nbsp;</p>
                                    <table cellpadding="5" bordercolor="#ccc" border="1" width="600" style=" border-collapse: collapse;" class="show_tracks table table-striped table-hover">
                                        <thead>
                                        <tr>
                                            <th colspan="3">Трек отправления <?=$r['NAME'];?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        foreach ($r['Events'] as $ev)
                                        {
                                            if (in_array($ev['InfoEvent'], $arResult['HIDE_EVENTS']) &&
                                                ($ev['Event'] == 'Исключительная ситуация!'))
                                            {}
                                            else
                                            {
                                                ?>
                                                <tr>
                                                    <td width="30%"><?=$ev['Date'];?></td>
                                                    <td width="35%"><?=$ev['Event'];?></td>
                                                    <td width="35%"><?=$ev['InfoEvent'];?></td>
                                                </tr>
                                                <?php
                                            }
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                    <p>&nbsp;</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    else
    {
        if ((int)$arResult['CURRENT_CLIENT'] == 0)
        {
            ?>
            <div class="alert alert-dismissable alert-warning fade in" role="alert">Не выбран клиент</div>
            <?php
        }
        else
        {
            ?>
            <div class="alert alert-dismissable alert-warning fade in" role="alert">Список накладных за выбранный период пуст</div>
            <?php
        }
    }
}
if($USER->isAdmin()){
    $finish = microtime(true);
    $delta = $finish - $start;
    AddToLogs('test_logs', ['time_1' => $delta,
        'mess' => 'Время вывода шаблона']);
}

?>

