<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
    die();
}?>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>-->
<link href="https://cdn.jsdelivr.net/npm/suggestions-jquery@20.3.0/dist/css/suggestions.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/suggestions-jquery@20.3.0/dist/js/jquery.suggestions.min.js"></script>

<? if($USER->isAdmin()){
    //dump( $arResult);
}
foreach($arResult["COUNTRY_EXEC"]  as $key=>$value){
    ${'srDistr'.$key} = [];
    $curcountry = $value;
    $rsSect = CIBlockSection::GetList(['name' => 'asc'], ["ACTIVE" => "Y", "IBLOCK_ID" => 6,
        "SECTION_ID" => $curcountry]);
    while ($arSect = $rsSect->GetNext())
    {
        ${'srDistr'.$key}[$arSect["ID"]] = $arSect["NAME"];
    }
}

?>

<script type="text/javascript">
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });
    $(document).ready(function(){


        // запрет вызова по воскресеньям
        $('#callcourierdate').on('change', function(){
            let dateForCalling = $('#callcourierdate').val();
            let myDate = new Date( dateForCalling.replace(/(\d+).(\d+).(\d+)/,"$3/$2/$1") );
            let dateT = new Date().getTime();
            if(!myDate.getDay()){
                $('#callcourierdate').val('');
                alert('Вызов курьера в воскресенье невозможен! Пожалуйста, смените дату.');
                return;
            }
            let dateV = myDate.getTime();
            console.log(`${dateT-dateV}`);
            if((dateT-dateV) > 19*60*60*1000){
                $('#callcourierdate').val('');
                alert('Вызов курьера на выбранную дату невозможен! Пожалуйста, смените дату.');
            }
        });

        /* получить сумму доставки по прайсу клиента из 1с */
        $('#sum_pay_request').on('click', function () {
            $('#pay_info_client').val('');
            let data = this.attributes;
            console.log(data);
            let datajson = {
                'data_client': data.data_client.value,
                'data_weight': data.data_weight.value,
                'data_gabweight': data.data_gabweight.value,
                'data_city_id': data.data_city_id.value
            };
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "/api/GetSum.php?mode=sumpay",
                data: datajson,
                success: function(data){
                    if(data){
                        $('#pay_info_client').html(`<h3>${data} руб.</h3>`);
                        console.log(data);
                    }

                }
            });
            console.log(datajson);
        } );


        /* чекбоксы экспресс 2-8 */
        let expr2 = $('#expr2');
        let expr4 = $('#expr4');
        let expr8 = $('#expr8');

        expr2.parent().attr('style', 'display:none');
        expr4.parent().attr('style', 'display:none');
        expr8.parent().attr('style', 'display:none');

        $('#autocity_recipient').change(function(){
            let expr2 = $('#expr2');
            let expr4 = $('#expr4');
            let expr8 = $('#expr8');
            let mskol = false;
            let userId = expr4[0].attributes.userid.value;   // 62587452 Москоллектор
            if (userId === '62587452'){
                mskol = true;
            }
            let str = this.value;
            let result = str.match(/Москва/);
            if(result !== null){
                console.log(result[0]);
                expr2.parent().attr('style', 'display:block');
                expr4.parent().attr('style', 'display:block');
                expr8.parent().attr('style', 'display:block');
            }else{
                expr2.parent().attr('style', 'display:none');
                expr4.parent().attr('style', 'display:none');
                expr8.parent().attr('style', 'display:none');
            }
            /* Костыль для Москоллектор  */
            if(mskol){
                let result = str.match(/Московская/);
                let result1 = str.match(/Москва/);
                if(result !== null || result1 !== null){
                    console.log(result[0]);
                    expr4.parent().attr('style', 'display:block');
                }else{
                    expr4.parent().attr('style', 'display:none');
                }
            }

        });

        function AutoCompany()
        {
            var url = '/search_city.php?type=name_company&company=<?=$arResult['CURRENT_CLIENT'];?>&type_company=<?=$arResult['TYPE_CLIENT_RECIPIENTS'];?>&branch=<?=intval($arResult["CURRENT_BRANCH"]);?>';
            $('#company').autocomplete({
                source: url,
                minLength: 0,
                select: function( event, ui ) {
                    console.log(ui);
                    $('#sum_pay_request').attr('data_city_id' , ui.item.id_city);
                    $(this).val( ui.item.company);
                    $('#name').val(ui.item.name);
                    $('#INN_RECIPIENT').val(ui.item.inn);
                    $('#phone').val(ui.item.phone);
                    $('#autocity_recipient').val(ui.item.city);
                    let str = ui.item.city;
                    let expr4 = $('#expr4');
                    let expr2 = $('#expr2');
                    let expr8 = $('#expr8');
                    let company = $('#company');
                    let mskol = false;
                    if(company[0].attributes.userid.value){
                        let userId = company[0].attributes.userid.value;
                        if (userId === '62587452'){
                            mskol = true;
                        }
                    }

                    console.log(mskol);
                    let result = str.match(/Москва/);
                    if (result !== null){
                        expr2.parent().attr('style', "display:block");
                        expr4.parent().attr('style', "display:block");
                        expr8.parent().attr('style', "display:block");
                    }
                    if(mskol){
                        let res = str.match(/Московская/);
                        let res1 = str.match(/Москва/);
                        if(res !== null || res1 !== null){

                            expr4.parent().attr('style', 'display:block');
                        }else{
                            expr4.parent().attr('style', 'display:none');
                        }
                    }
                    $('#index').val(ui.item.index);
                    $('#adress').val(ui.item.adress);
                    return false;
                }
            });
        }


        functioncallcourier();
        $("#add_geography_modal").on('click', function(){
            let req   = $('#form_add_g').serialize();
            $.ajaxSetup({cache: false});
            $.ajax({
                method: 'post',
                dataType: 'json',
                url: "/add_geography.php",
                data: req,
                success: function(data){
                    //console.log(data);
                    $('#results').text('');
                    $('#alert-err').css('display', 'none');
                    $.each(data , function (index, value){
                        if(index === "city"){
                            $('#autocity_recipient').val(value);
                            $('#btn-close').click();
                        }
                        if(index === "error"){
                            $('#results').text(value);
                            $('#alert-err').css('display', 'block');
                        }
                        if(index === 'city_id'){
                            $('#input-group-city').val(value);
                        }

                        if(index === 'full_adr'){
                            $('#adress').val(value);
                        }


                    });


                }

            });
        });
        $("#autocountry_recipient_mod").on('change',function () {
            let cntr = $("#autocountry_recipient_mod").val();
            let cnt = $.trim(cntr);

            if(cnt !== "Россия" && cnt !== "Украина" && cnt !== "Беларусь" && cnt !== "Казахстан"){
                $('#region_RUS').attr( "disabled", "true" );
                $('#region_UKR').attr( "disabled", "true" );
                $('#region_KAZ').attr( "disabled", "true" );
                $('#region_BEL').attr( "disabled", "true" );
                $('#type').attr( "disabled", "true" );
                $('#r_addclient').attr( "disabled", "true" );

            }else{
                $('#region_RUS').attr( "disabled", null );
                $('#region_UKR').attr( "disabled", null );
                $('#region_KAZ').attr( "disabled", null );
                $('#region_BEL').attr( "disabled", null );
                $('#type').attr( "disabled", null );
                $('#r_addclient').attr( "disabled", null );
            }
            if(cnt !== "Россия"){
                $('#r_addclient').attr( "disabled", true );
            }
            let rus =  $('#select_RUS');
            let ukr = $('#select_UKR');
            let kaz =  $('#select_KAZ');
            let bel =  $('#select_BEL');
            if(cnt === "Россия"){
                rus.css('display', 'block');
                ukr.css('display','none');
                kaz.css('display','none');
                bel.css('display','none');

            }
            if(cnt === "Украина"){
                rus.css('display', 'none');
                ukr.css('display','block');
                kaz.css('display','none');
                bel.css('display','none');


            }
            if(cnt === "Казахстан"){
                rus.css('display', 'none');
                ukr.css('display','none');
                kaz.css('display','block');
                bel.css('display','none');
            }
            if(cnt === "Беларусь"){
                rus.css('display', 'none');
                ukr.css('display','none');
                kaz.css('display','none');
                bel.css('display','block');
            }
        });

        $('#region_RUS').on('change', function () {
            $('#region_UKR option:selected').removeAttr('selected');
            $('#region_KAZ option:selected').removeAttr('selected');
            $('#region_BEL option:selected').removeAttr('selected');
        });
        $('#region_UKR').on('change', function () {
            $('#region_RUS option:selected').removeAttr('selected');
            $('#region_KAZ option:selected').removeAttr('selected');
            $('#region_BEL option:selected').removeAttr('selected');
        });
        $('#region_BEL').on('change', function () {
            $('#region_UKR option:selected').removeAttr('selected');
            $('#region_KAZ option:selected').removeAttr('selected');
            $('#region_RUS option:selected').removeAttr('selected');
        });
        $('#region_KAZ').on('change', function () {
            $('#region_UKR option:selected').removeAttr('selected');
            $('#region_RUS option:selected').removeAttr('selected');
            $('#region_BEL option:selected').removeAttr('selected');
        });


        $("#addFields").click(function(){
            <?
            // добавим  одну запись в описание отправления
            ?>
            var j = $('input.quantityItem').val();
            console.log (" == " + j);

            $('<tr><td class="test1"><input type="text" class="form-control" name="pack_description['+ parseInt(j) +'][name]" value="" id="pack_description_first"></td>  \
				<td>\
					<input type="text" class="form-control" name="pack_description['+ parseInt(j) +'][place]" value=""> \
				</td> \
				<td class="test2"> \
					<div class="input-group"> \
						<input type="text" class="form-control" name="pack_description['+ parseInt(j) +'][weight]" value="">\
						<div class="input-group-addon">кг</div>\
					</div>\
				</td>\
				<td class="test3">\
					<div class="input-group">\
					<input type="text" class="form-control" name="pack_description['+ parseInt(j) +'][size][0]" value="">\
					<div class="input-group-addon">см</div>\
					</div>\
				</td>\
				<td class="test4">\
					<div class="input-group">\
					<input type="text" class="form-control" name="pack_description['+ parseInt(j) +'][size][1]" value="">\
					<div class="input-group-addon">см</div>\
					</div>\
				</td>\
				<td class="test5">\
					<div class="input-group">\
					<input type="text" class="form-control" name="pack_description['+ parseInt(j) +'][size][2]" value="">\
					<div class="input-group-addon">см</div>\
					</div>\
				</td>\
				</tr> ').insertAfter($('tbody#description tr').last());
            // добавили
            j = parseInt(j) + 1;
            $("input.quantityItem").val(parseInt(j));

            // обновляем события с добавлением кнопки
            $("#calculated_values input").change(function() {
                CalculateValues();
            });
        });

        AutoCompany();
        <? if ($arResult['DEAULTS']['CHOICE_COMPANY'] == 2) : ?>
        AutoCompanySender();
        <? else : ?>
        <?
        if (count($arResult['SENDERS']) == 1)
        {
        ?>
        SelectFirst('<?=$arResult['SENDERS'][0]['id'];?>');
        <?
        }
        ?>
        <? endif; ?>
        AutoCountry();
        AutoCity();

        CalculateValues();
        $("#calculated_values input").change(function() {
            CalculateValues();
        });

        $('.maskdatetime').mask('99.99.9999 99:99');
        $('.maskdate').mask('99.99.9999');
        $('.masktime').mask('99:99');

        // functioncallcourier();

        Costmarker();

        $( "#cost-value" ).change(function()
        {
            Costmarker();
        });

        $( "#payment-value" ).change(function()
        {
            paymentChange();
        });

        $('form[name="curform"]').keydown(function(event)
        {
            if (event.keyCode == 13 && event.ctrlKey)
            {
                $(this).submit();
                return true;
            }
            if(event.keyCode == 13)
            {
                event.preventDefault();
                return false;
            }
        });

        $('input[type=radio][name=PAYMENT]').change(function() {
            var PAYMENT = parseInt(this.value, 10);
            var TYPE_PAYS = parseInt($('input[type=radio][name=TYPE_PAYS]:checked').val(), 10);
            if (PAYMENT == 256)
            {
                $('#type_pays_253_block').removeClass('hidden');
            }
            else
            {
                $('#type_pays_253_block').addClass('hidden');
                if (TYPE_PAYS == 253)
                {
                    $('input[type=radio][name=TYPE_PAYS]').attr('checked',false);
                }
            }
            if ((PAYMENT == 256) && ((TYPE_PAYS == 252) || (TYPE_PAYS == 253)))
            {
                $('#whose_order_block').removeClass('hidden');
            }
            else
            {
                $('#whose_order_block').addClass('hidden');
            }
        });

        $('input[type=radio][name=TYPE_PAYS]').change(function() {
            var PAYMENT = parseInt($('input[type=radio][name=PAYMENT]:checked').val(), 10);
            var TYPE_PAYS = parseInt(this.value, 10);
            if ((PAYMENT == 256) && ((TYPE_PAYS == 252) || (TYPE_PAYS == 253)))
            {
                $('#whose_order_block').removeClass('hidden');
            }
            else
            {
                $('#whose_order_block').addClass('hidden');
            }
        });
        var i = parseInt($('#count_goods').val());
        $("#add_row").click(function(){
            //$('#addr'+i).html("<td>"+ (i+1) +"</td><td><input name='name"+i+"' type='text' placeholder='Name' class='form-control input-md'  /> </td><td><input  name='mail"+i+"' type='text' placeholder='Mail'  class='form-control input-md'></td><td><input  name='mobile"+i+"' type='text' placeholder='Mobile'  class='form-control input-md'></td>");
            $('#addr'+i).html('<td><input type="text" name="goods['+i+'][name]" value="" class="form-control"></td>'+
                '<td>' +
                '<div class="input-group">' +
                '<input type="text" name="goods['+i+'][art]" value="" class="form-control" aria-describedby="good-addon-'+i+'-0" id="input-goods-art-'+i+'">'+
                '<span class="input-group-addon" id="good-addon-'+i+'-0">Арт.</span>'+
                '</div>'+
                '</td>'+
                '<td>' +
                '<div class="input-group">' +
                '<input type="text" name="goods['+i+'][amount]" value="" class="form-control" aria-describedby="good-addon-'+i+'-1" id="input-goods-amount-'+i+'" onChange="CalcGoods(\''+i+'\');">'+
                '<span class="input-group-addon" id="good-addon-'+i+'-1">шт.</span>'+
                '</div>'+
                '</td>'+
                '<td>'+
                '<div class="input-group">'+
                '<input type="text" name="goods['+i+'][price]" value="" class="form-control" aria-describedby="good-addon-'+i+'-2" id="input-goods-price-'+i+'" onChange="CalcGoods(\''+i+'\');">'+
                '<span class="input-group-addon" id="good-addon-'+i+'-2">руб.</span>'+
                '</div>'+
                '</td>'+
                '<td>'+
                '<div class="input-group">'+
                '<input type="text" name="goods['+i+'][sum]" value="" class="form-control" aria-describedby="good-addon-'+i+'-3" id="input-goods-sum-'+i+'">'+
                '<span class="input-group-addon" id="good-addon-'+i+'-3">руб.</span>'+
                '</div>'+
                '</td>'+
                '<td>'+
                '<div class="input-group">'+
                '<input type="text" name="goods['+i+'][sumnds]" value="" class="form-control" aria-describedby="good-addon-'+i+'-4" id="input-goods-sumnds-'+i+'">'+
                '<span class="input-group-addon" id="good-addon-'+i+'-4">руб.</span>'+
                '</div>'+
                '</td>'+
                '<td>'+
                '<select size="1" name="goods['+i+'][persentnds]" class="form-control" id="input-goods-persentnds-'+i+'" onChange="CalcGoods(\''+i+'\');">'+
                '<option value="20">20%</option>'+
                '<option value="10">10%</option>'+
                '<option value="0">Без НДС</option>'+
                '</select>'+
                '</td>');
            if ($('tr#addr'+(i+1)).length > 0) {
            } else {
                $('#tab_logic').append('<tr id="addr'+(i+1)+'"></tr>');
            }
            i++;
            $('#count_goods').val(i);
        });
        $("#delete_row").click(function(){
            if(i>1){
                $("#addr"+(i-1)).html('');
                i--;
                $('#count_goods').val(i);
            }
        });

        $('#add-btn-tour').on('click', function () {
            setTimeout(function () {
                $('#add-btn-tour').attr('disabled', 'disabled');
                $('input[type=submit][name=add-print]').attr('disabled', 'disabled');
            }, 200);

        });


    });

    function CalcGoods(row)
    {
        var amount = parseInt($('#input-goods-amount-'+row).val().replace(/[,]+/g, '.')) || 0;
        var price = parseFloat($('#input-goods-price-'+row).val().replace(/[,]+/g, '.')) || 0;
        var persentnds = parseInt($('#input-goods-persentnds-'+row).val());
        var sum = amount*price;
        var sumnds = (sum*persentnds)/100;
        $('#input-goods-sum-'+row).val(sum);
        $('#input-goods-sumnds-'+row).val(sumnds);
    }

    function Costmarker()
    {
        $('#cost-marker').removeClass('glyphicon-remove');
        $('#cost-marker').removeClass('glyphicon-ok');
        $('#cost-marker').css('color','#555555');
        var vall = parseFloat($("#cost-value").val().replace(/[,]+/g, '.')) || 0;
        if (vall > 0)
        {
            $('#cost-marker').addClass('glyphicon-ok');
            $('#cost-marker').css('color','#468847');
        }
        else
        {
            $('#cost-marker').addClass('glyphicon-remove');
            $('#cost-marker').css('color','#b94a48');
        }
    }

    function paymentChange()
    {
        var vall = parseFloat($("#payment-value").val().replace(/[,]+/g, '.')) || 0;
        if (vall > 0)
        {
            $( "#type_pays_252" ).prop( "checked", true );
        }
    }

    function functioncallcourier()
    {
        if ($('input#callcourier').prop('checked'))
        {
            $('input#callcourcomment').prop("disabled",false);
            $('input#callcourierdate').prop("disabled",false);
            $('input#callcourtime_from').prop("disabled",false);
            $('input#callcourtime_to').prop("disabled",false);
            $('#basic-addon5').css('visibility', 'visible');

        }
        else
        {
            $('input#callcourcomment').prop("disabled",true);
            $('input#callcourierdate').prop("disabled",true);
            $('input#callcourtime_from').prop("disabled",true);
            $('input#callcourtime_to').prop("disabled",true);
            $('#basic-addon5').css('visibility','hidden');
        }
    }



    function AutoCompanySender()
    {
        var url = '/search_city.php?type=name_company&company=<?=$arResult['CURRENT_CLIENT'];?>&type_company=<?=$arResult['TYPE_CLIENT_SENDERS'];?>&branch=<?=(int)$arResult["CURRENT_BRANCH"];?>';
        $('#COMPANY_SENDER').autocomplete({
            source: url,
            minLength: 0,
            select: function( event, ui ) {
                $(this).val( ui.item.company);
                $('#NAME_SENDER').val(ui.item.name);
                $('#INN_SENDER').val(ui.item.inn);
                $('#PHONE_SENDER').val(ui.item.phone);
                $('#autocity_sender').val(ui.item.city);
                $('#INDEX_SENDER').val(ui.item.index);
                $('#ADRESS_SENDER').val(ui.item.adress);
                return false;
            }
        });
    }

    function AutoCity()
    {
        var url = '/search_city.php?type=city';
        $('.autocity').autocomplete({
            source: url,
            minLength: 0,
            select: function( event, ui ) {
                console.log(ui);
                $(this).val( ui.item.value);
                $('#sum_pay_request').attr('data_city_id' , ui.item.id);
                return false;
            }
        });
    }

    function AutoCountry()
    {
        var url =  '/search_city.php?type=country';
        $('.autocountry').autocomplete({
            source: url,
            minLength: 0,
            select: function( event, ui ) {
                $(this).val( ui.item.value);
                return false;
            }
        });
    }

    function SelectCompanySender()
    {
        var kk = $('#company_sender_id').val();
        var company = '';
        var name = '';
        var phone = '';
        var city_full = '';
        var index = '';
        var adress = '';
        var inn = '';
        if (kk > 0)
        {
            company = $('#sender_'+kk+'_company').val();
            name = $('#sender_'+kk+'_name').val();
            phone = $('#sender_'+kk+'_phone').val();
            city_full = $('#sender_'+kk+'_city_full').val();
            index = $('#sender_'+kk+'_index').val();
            adress = $('#sender_'+kk+'_adress').val();
            inn = $('#sender_'+kk+'_inn').val();
        }
        $('#COMPANY_SENDER').val(company);
        $('#NAME_SENDER').val(name);
        $('#PHONE_SENDER').val(phone);
        $('#autocity_sender').val(city_full);
        $('#INDEX_SENDER').val(index);
        $('#ADRESS_SENDER').val(adress);
        $('#INN_SENDER').val(inn);
    }

    function SelectFirst(sel)
    {
        $("#company_sender_id [value='"+ sel +"']").attr("selected", "selected");
        SelectCompanySender();
    }

    function ChangeTypePack(newval,oldval)
    {
        var entertext = $('#pack_description_first').val();
        if ((entertext == oldval) || (entertext.length == 0))
        {
            $('#pack_description_first').val(newval);
        }
    }

    function CalculateValues()
    {
        var total_place = 0 ;
        var total_weight = 0;
        var total_gabweight = 0;

        var total_place1 = 0 ;
        var total_weight1 = 0;
        var total_gabweight1 = 0;


        var place  = 0;
        var weight = 0;

        var size1  = 0;
        var size2  = 0;
        var size3  = 0;

        var gabweight = 0;

        var coefficient_vw = parseInt($('input[name="coefficient_vw"]').val(), 10) || 5000;
        // перебрать весь массив наших tr
        var x = 0;
        $("tbody#description tr").each(function(){

            place = parseInt($('input[name="pack_description['+ parseInt(x) +'][place]"]').val(), 10) || 0;
            weight = parseFloat($('input[name="pack_description['+ parseInt(x) +'][weight]"]').val().replace(/[,]+/g, '.')) || 0;

            size1 = parseFloat($('input[name="pack_description['+ parseInt(x) +'][size][0]"]').val().replace(/[,]+/g, '.')) || 0;
            size2 = parseFloat($('input[name="pack_description['+ parseInt(x) +'][size][1]"]').val().replace(/[,]+/g, '.')) || 0;
            size3 = parseFloat($('input[name="pack_description['+ parseInt(x) +'][size][2]"]').val().replace(/[,]+/g, '.')) || 0;

            gabweight = (size1 * size2 * size3) / coefficient_vw;

            total_place1 =  total_place1 + place ;
            total_weight1 = total_weight1 + weight ;
            total_gabweight1 = total_gabweight1 + gabweight ;

            x++;
        });

        console.log(" == " + x + " == ");
        console.log(" total_place :: " + total_place1 + " == ");
        console.log(" total_weight :: " + total_weight1 + " == ");
        console.log(" total_gabweight :: " + total_gabweight1 + " == ");



        $('#total_place').val(total_place1);
        $('#total_weight').val(total_weight1.toFixed(2));
        $('#sum_pay_request').attr('data_weight' , total_weight1.toFixed(2));
        $('#total_gabweight').val(total_gabweight1.toFixed(2));
        $('#sum_pay_request').attr('data_gabweight' , total_gabweight1.toFixed(2));


    }
</script>



<?php
if (count($arResult["ERRORS"]) > 0)
{
    ?>
    <div class="alert alert-dismissable alert-danger"><?=implode('</br>',$arResult["ERRORS"]);?></div>
    <?
}
if (count($arResult["MESSAGE"]) > 0)
{
    ?>
    <div id="alert-success-number" class="alert alert-dismissable alert-success"><?=implode('</br>',$arResult["MESSAGE"]);?></div>
    <?
}
if (count($arResult["WARNINGS"]) > 0)
{
    ?>
    <div class="alert alert-dismissable alert-warning"><?=implode('</br>',$arResult["WARNINGS"]);?></div>
    <?
}



if ($arResult['invoice_made']):?>
    <div class="alert alert-dismissable alert-success">
        Вы можете продолжить оформлять следующую накладную или выйти из формы<br><br>
        <a style="color:#fff; text-decoration: none;" href="index.php" class="btn btn-success">Выйти в список</a>
    </div>
<?php endif;?>
<?php


if ($arResult['OPEN'])
{
    ?>
    <form id="curform" action="<?=$arParams['LINK'];?>index.php?mode=add" method="post" name="curform" class="form-vertical">
        <input type="hidden" name="rand" value="<?=rand(100000,999999);?>">
        <input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>">
        <input type="hidden" name="coefficient_vw" value="<?=$arResult['CURRENT_CLIENT_COEFFICIENT_VW'];?>">
        <input type="hidden" name="add_ctrl" value="Создать">

        <div class="row">
            <div class="col-md-6">
                <h3><?=$arResult['TITLE'];?></h3>
                <?php
                // Вывод центра затрат для Абсолют Страхование  || $arResult['CURRENT_CLIENT'] == 56103010
                if ($arResult['CURRENT_CLIENT'] == 56103010):
                    $arSelect = [
                        "ID", "NAME"
                    ];
                    $resArr = GetInfoArr(false, false, 115, $arSelect, [], false);
                    ?>
                    <div class="form-group">
                        <label for="center_expenses" class="control-label">Центр затрат</label>
                        <select style = "width:50%" class="form-control" name="CENTER_EXPENSES" id="center_expenses" required>
                            <option value=""></option>
                            <?php foreach($resArr as $value):?>
                                <option value="<?=$value['ID']?>"><?=$value['NAME']?></option>
                            <?php endforeach;?>
                        </select>
                    </div>
                <?php endif;?>
            </div>
            <div class="col-md-5 col-md-offset-1">
                <div class="form-group <?=strlen($arResult['ERR_FIELDS']['NUMBER']) ? $arResult['ERR_FIELDS']['NUMBER'] : 'has-success';?>">
                    <table width="100%">
                        <tr>
                            <td>
                                <div class="input-group">
                                    <span class="input-group-addon" id="number">Номер накладной</span>
                                    <input type="text" class="form-control" name="NUMBER" value="<?=$_POST['NUMBER'];?>" id="NUMBER" aria-describedby="number"> <br/><br/>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td>

                                <?php if($arResult['CURRENT_CLIENT'] == 41478141 || $arResult['CURRENT_CLIENT'] == 9528186):?>
                                    <div class="form-group <?=$arResult['ERR_FIELDS']['INNER_NUMBER'];?>">
                                        <div class="input-group">
                                            <span class="input-group-addon" id="number">Внутренний номер накладной</span>
                                            <input type="text" class="form-control" name="InternalNumber"
                                                   value="" id="InternalNumber" aria-describedby="number">
                                        </div>
                                    </div>
                                <?endif;?>
                            </td>
                        </tr>
                    </table>
                    <span id="helpBlock" class="help-block"><i><?=GetMessage("HELP_TEXT");?></i></span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 unit-class1">
                <h4><?=GetMessage("TITLE_SENDER");?></h4>
                <div class="form-group <?=$arResult['ERR_FIELDS']['COMPANY_SENDER'];?>">
                    <label  class="control-label">Компания</label>
                    <? if ($arResult['DEAULTS']['CHOICE_COMPANY'] == 2) : ?>
                        <input type="text"  class="form-control" name="COMPANY_SENDER"
                               value="<?=strlen($_POST['COMPANY_SENDER']) ? NewQuotes($_POST['COMPANY_SENDER']) : $arResult['DEAULTS']['COMPANY_SENDER'];?>"
                               id="COMPANY_SENDER">
                    <? else :
                        foreach ($arResult['SENDERS'] as $s)
                        {
                            ?>
                            <input type="hidden" name="sender[<?=$s['id'];?>][company]" value="<?=$s['company'];?>" id="sender_<?=$s['id'];?>_company">
                            <input type="hidden" name="sender[<?=$s['id'];?>][name]" value="<?=$s['name'];?>" id="sender_<?=$s['id'];?>_name">
                            <input type="hidden" name="sender[<?=$s['id'];?>][phone]" value="<?=$s['phone'];?>" id="sender_<?=$s['id'];?>_phone">
                            <input type="hidden" name="sender[<?=$s['id'];?>][city_full]" value="<?=$s['city_full'];?>" id="sender_<?=$s['id'];?>_city_full">
                            <input type="hidden" name="sender[<?=$s['id'];?>][index]" value="<?=$s['index'];?>" id="sender_<?=$s['id'];?>_index">
                            <input type="hidden" name="sender[<?=$s['id'];?>][adress]" value="<?=$s['adress'];?>" id="sender_<?=$s['id'];?>_adress">
                            <input type="hidden" name="sender[<?=$s['id'];?>][inn]" value="<?=$s['inn'];?>" id="sender_<?=$s['id'];?>_inn">
                            <?
                        }
                        ?>
                        <select class="form-control selectpicker" name="company_sender_id" onChange="SelectCompanySender();"
                                id="company_sender_id" data-live-search="true">
                            <option value="0">
                                <?
                                if (((int)$_POST['company_sender_id'] == 0) && ($arResult['DEAULTS']['COMPANY_SENDER_ID'] == 0))
                                {
                                    echo strlen($_POST['COMPANY_SENDER']) ? $_POST['COMPANY_SENDER'] : $arResult['DEAULTS']['COMPANY_SENDER'];
                                }
                                ?>
                            </option>
                            <?
                            $search_comp_id = ((int)$_POST['company_sender_id'] > 0) ? (int)$_POST['company_sender_id'] : $arResult['DEAULTS']['COMPANY_SENDER_ID'];
                            foreach ($arResult['SENDERS'] as $s)
                            {
                                $ss = ($search_comp_id == $s['id']) ? ' selected' : '';
                                ?>
                                <option value="<?=$s['id'];?>"<?=$ss;?>><?=$s['value'];?></option>
                                <?
                            }
                            ?>
                        </select>
                        <input type="hidden" name="COMPANY_SENDER" value="<?=strlen($_POST['COMPANY_SENDER']) ? $_POST['COMPANY_SENDER'] : $arResult['DEAULTS']['COMPANY_SENDER'];?>" id="COMPANY_SENDER">
                    <? endif; ?>
                </div>
                <?php if ($arResult['CURRENT_CLIENT'] == 56103010):?>
                    <div class="form-group <?=$arResult['ERR_FIELDS']['INN_SENDER'];?>">
                        <label class="control-label">ИНН</label>
                        <input type="text" class="form-control" name="INN_SENDER" value="<?=strlen($_POST['INN_SENDER']) ? $_POST['INN_SENDER'] : $arResult['DEAULTS']['INN_SENDER'];?>" id="INN_SENDER">
                    </div>
                <?php endif; ?>
                <div class="form-group <?=$arResult['ERR_FIELDS']['NAME_SENDER'];?>">
                    <label class="control-label">Фамилия</label>
                    <input type="text" class="form-control" name="NAME_SENDER" value="<?=strlen($_POST['NAME_SENDER']) ? $_POST['NAME_SENDER'] : $arResult['DEAULTS']['NAME_SENDER'];?>" id="NAME_SENDER">
                </div>
                <div class="form-group <?=$arResult['ERR_FIELDS']['PHONE_SENDER'];?>">
                    <label class="control-label">Телефон</label>
                    <input type="text" class="form-control" name="PHONE_SENDER" value="<?=strlen($_POST['PHONE_SENDER']) ? $_POST['PHONE_SENDER'] : $arResult['DEAULTS']['PHONE_SENDER'];?>" id="PHONE_SENDER">
                </div>


                <div class="form-group <?=$arResult['ERR_FIELDS']['CITY_SENDER'];?>">
                    <label class="control-label">Город</label>
                    <input type="text" class="form-control autocity" name="CITY_SENDER" value="<?=strlen($_POST['CITY_SENDER']) ? $_POST['CITY_SENDER'] : $arResult['DEAULTS']['CITY_SENDER'];?>" id="autocity_sender">
                </div>
                <div class="form-group <?=$arResult['ERR_FIELDS']['INDEX_SENDER'];?>">
                    <label class="control-label">Индекс</label>
                    <input type="text" class="form-control" name="INDEX_SENDER" value="<?=strlen($_POST['INDEX_SENDER']) ? $_POST['INDEX_SENDER'] : $arResult['DEAULTS']['INDEX_SENDER'];?>" id="INDEX_SENDER">
                </div>
                <div class="form-group <?=$arResult['ERR_FIELDS']['ADRESS_SENDER'];?>">
                    <label class="control-label">Адрес</label>
                    <textarea class="form-control" name="ADRESS_SENDER" id="ADRESS_SENDER"><?=strlen($_POST['ADRESS_SENDER']) ? $_POST['ADRESS_SENDER'] : $arResult['DEAULTS']['ADRESS_SENDER'];?></textarea>
                </div>
            </div>
            <div class="col-md-3 col-md-offset-1">
                <h4><?=GetMessage("TITLE_RECIPIENT");?></h4>
                <div class="form-group <?=$arResult['ERR_FIELDS']['COMPANY_RECIPIENT'];?>">
                    <label class="control-label">Компания</label>
                    <input type="text" class="form-control" userid="<?=$arResult['CURRENT_CLIENT']?>" name="COMPANY_RECIPIENT" value="<?=strlen($_POST['COMPANY_RECIPIENT']) ? $_POST['COMPANY_RECIPIENT'] : $arResult['DEAULTS']['COMPANY_RECIPIENT'];?>" id="company">
                </div>
                <?php if ($arResult['CURRENT_CLIENT'] == 56103010):?>
                    <div class="form-group <?=$arResult['ERR_FIELDS']['INN_RECIPIENT'];?>">
                        <label class="control-label">ИНН</label>
                        <input type="text" class="form-control" name="INN_RECIPIENT" value="<?=strlen($_POST['INN_RECIPIENT']) ? $_POST['INN_RECIPIENT'] : $arResult['DEAULTS']['INN_RECIPIENT'];?>" id="INN_RECIPIENT">
                    </div>
                <?php endif;?>
                <div class="form-group <?=$arResult['ERR_FIELDS']['NAME_RECIPIENT'];?>">
                    <label class="control-label">Фамилия</label>
                    <input type="text" class="form-control" name="NAME_RECIPIENT" value="<?=strlen($_POST['NAME_RECIPIENT']) ? $_POST['NAME_RECIPIENT'] : $arResult['DEAULTS']['NAME_RECIPIENT'];?>" id="name">
                </div>
                <div class="form-group <?=$arResult['ERR_FIELDS']['PHONE_RECIPIENT'];?>">
                    <label class="control-label">Телефон</label>
                    <input type="text" class="form-control" name="PHONE_RECIPIENT" value="<?=strlen($_POST['PHONE_RECIPIENT']) ? $_POST['PHONE_RECIPIENT'] : $arResult['DEAULTS']['PHONE_RECIPIENT'];?>" id="phone">
                </div>
                <?//if($USER->isAdmin()){

                ?>
                <div class="form-group <?=$arResult['ERR_FIELDS']['COUNTRY_SENDER'];?>">
                    <label class="control-label">Страна</label>
                    <input type="text" class="form-control autocountry" name="COUNTRY_RECIPIENT"
                           value="<?=strlen($_POST['COUNTRY_RECIPIENT']) ? $_POST['COUNTRY_RECIPIENT'] : $arResult['DEAULTS']['COUNTRY_RECIPIENT'];?>" id="autocountry_recipient">
                </div>

                <?// }?>
                <div class="form-group <?=$arResult['ERR_FIELDS']['CITY_RECIPIENT'];?>">

                    <input id="input-group-city" type="hidden" value='<?=$_POST['CITY_RECIPIENT_ID']?>' name="CITY_RECIPIENT_ID">
                    <label class="control-label">Город</label>
                    <input placeholder="Начинайте вводить название Пункта получателя" type="text" class="form-control
                     autocity" name="CITY_RECIPIENT"
                           value="<?= strlen($_POST['CITY_RECIPIENT']) ? $_POST['CITY_RECIPIENT'] :
                               $arResult['DEAULTS']['CITY_RECIPIENT']; ?>" id="autocity_recipient">

                </div>

                <div class="form-group">

                    <button style="margin-top: 25px; width:100%" class="btn btn-warning" type="button"
                            data-toggle="modal" data-target="#add_town">
                        Добавить населенный пункт, <br>ТОЛЬКО если его нет в списке Город
                    </button>

                </div>

                <div class="form-group <?=$arResult['ERR_FIELDS']['INDEX_RECIPIENT'];?>">
                    <label class="control-label">Индекс</label>
                    <input type="text" class="form-control" name="INDEX_RECIPIENT" value="<?=strlen($_POST['INDEX_RECIPIENT']) ? $_POST['INDEX_RECIPIENT'] : $arResult['DEAULTS']['INDEX_RECIPIENT'];?>" id="index">
                </div>
                <div class="form-group <?=$arResult['ERR_FIELDS']['ADRESS_RECIPIENT'];?>">
                    <label class="control-label">Адрес</label>
                    <textarea class="form-control" name="ADRESS_RECIPIENT" id="adress"><?=strlen($_POST['ADRESS_RECIPIENT']) ? $_POST['ADRESS_RECIPIENT'] : $arResult['DEAULTS']['ADRESS_RECIPIENT'];?></textarea>
                </div>
            </div>
            <div class="col-md-4 col-md-offset-1">
                <div class="row">
                    <div class="col-md-6">
                        <h4>Условия доставки</h4>
                        <div class="form-group <?=$arResult['ERR_FIELDS']['TYPE_DELIVERY'];?>">
                            <?
                            $type_delivery = ($_POST['TYPE_DELIVERY']) ?  ($_POST['TYPE_DELIVERY']) : $arResult['DEAULTS']['TYPE_DELIVERY'];
                            ?>
                            <label class="control-label">Тип доставки</label>

                            <?php

                            $stringoneFileds = ' checked=""';
                            ?>

                            <?php if (((int)$arResult['CURRENT_CLIENT_INFO']['PROPERTY_AVAILABLE_EXPRESS2_VALUE']) == 0){ ?>
                                <div class="radio">
                                    <label>
                                        <input id="expr2" name="TYPE_DELIVERY" value="345" type="radio" <?=($type_delivery == 345) ? 'checked=""' : $stringoneFileds;?>>
                                        Экспресс 2
                                    </label>
                                </div>
                                <?php // если показал то это РАДИО-КНОПКИ (остальные не показываем)
                                $stringoneFileds = "";
                            } ?>


                            <?php if (((int)$arResult['CURRENT_CLIENT_INFO']['PROPERTY_AVAILABLE_EXPRESS4_VALUE']) == 0){ ?>
                                <div class="radio">
                                    <label>
                                        <input id="expr4"  name="TYPE_DELIVERY" userid = "<?=$arResult['CURRENT_CLIENT']?>" value="346" type="radio" <?=($type_delivery == 346) ? 'checked=""' : $stringoneFileds;?>>
                                        Экспресс 4
                                    </label>
                                </div>
                                <?php  $stringoneFileds = "";
                            } ?>
                            <?php if (((int)$arResult['CURRENT_CLIENT_INFO']['PROPERTY_AVAILABLE_EXPRESS8_VALUE']) == 0){ ?>
                                <div class="radio">
                                    <label>
                                        <input id="expr8" name="TYPE_DELIVERY" value="338" type="radio" <?=($type_delivery == 338) ? 'checked=""' : $stringoneFileds;?>>
                                        Экспресс 8
                                    </label>
                                </div>
                                <?php $stringoneFileds = "";
                            } ?>
                            <? if (((int)$arResult['CURRENT_CLIENT_INFO']['PROPERTY_AVAILABLE_EXPRESS_VALUE']) == 0){  ?>
                                <div class="radio">
                                    <label>
                                        <input name="TYPE_DELIVERY" value="243" type="radio" <?=($type_delivery == 243) ? 'checked=""' : $stringoneFileds;?>>
                                        Экспресс
                                    </label>
                                </div>
                                <? $stringoneFileds = "";
                            } ?>
                            <? if (((int)$arResult['CURRENT_CLIENT_INFO']['PROPERTY_AVAILABLE_STANDART_VALUE']) == 0){ ?>
                                <div class="radio">
                                    <label>
                                        <input name="TYPE_DELIVERY" value="244" type="radio" <?=($type_delivery == 244) ? 'checked=""' : $stringoneFileds;?>>
                                        Стандарт
                                    </label>
                                </div>
                                <? $stringoneFileds = "";
                            } ?>
                            <? if (((int)$arResult['CURRENT_CLIENT_INFO']['PROPERTY_AVAILABLE_ECONOME_VALUE']) == 0 && false){  ?>
                                <div class="radio">
                                    <label>
                                        <input name="TYPE_DELIVERY" value="245" type="radio" <?=($type_delivery == 245) ? 'checked=""' : '';?>>
                                        Эконом
                                    </label>
                                </div>
                            <? } ?>
                            <?php if ((int)$arResult['CURRENT_CLIENT_INFO']['PROPERTY_AVAILABLE_WH_WH_VALUE'] == 1) :?>
                                <div class="radio">
                                    <label>
                                        <input name="TYPE_DELIVERY" value="308" type="radio" <?=($type_delivery == 308) ? 'checked=""' : '';?>>
                                        Склад-Склад
                                    </label>
                                </div>

                            <?php endif; ?>
                            <?php
                            // вывод на печать функции С возвратом для всех кроме сухого
                            if ($arResult['CURRENT_CLIENT'] != 41478141):?>
                                <div class="checkbox">
                                    <label class="control-label btn-default btn " style="padding-left: 30px;">
                                        <input type="checkbox"  name="WITH_RETURN" >С возвратом
                                    </label>
                                </div>
                            <?php endif;?>
                            <div class="checkbox">
                                <label class="control-label btn-default btn " style="padding-left: 30px;">
                                    <input type="checkbox"  name="TRANSPORT_TYPE" id="transport_type">Наземный транспорт
                                </label>
                                <small style="display: block">Установите отметку если доставка наземным транспортом</small>
                            </div>
                            <a href="http://newpartner.ru/about/detail.php?ID=37544975" target="_blank">Подробнее об экспресс-доставке &rarr;</a>
                        </div>

                        <div class="form-group <?=$arResult['ERR_FIELDS']['TYPE_PACK'];?>">
                            <?php
                            $type_pack = ($_POST['TYPE_PACK']) ?  ($_POST['TYPE_PACK']) : $arResult['DEAULTS']['TYPE_PACK'];
                            $type_pack_name = ($type_pack == 247) ? 'Не документы' : 'Документы';
                            ?>
                            <label class="control-label">Тип отправления</label>
                            <div class="radio">
                                <label>
                                    <input name="TYPE_PACK" value="246" type="radio" <?=($type_pack == 246) ? 'checked=""' : '';?> onChange="ChangeTypePack('Документы','Не документы');">
                                    Документы
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="TYPE_PACK" value="247" type="radio" <?=($type_pack == 247) ? 'checked=""' : '';?> onChange="ChangeTypePack('Не документы','Документы');">
                                    Не документы
                                </label>
                            </div>
                        </div>

                        <div class="form-group <?=$arResult['ERR_FIELDS']['WHO_DELIVERY'];?>">
                            <?php
                            $who_delivery = ($_POST['WHO_DELIVERY']) ?  ($_POST['WHO_DELIVERY']) : $arResult['DEAULTS']['WHO_DELIVERY'];
                            ?>
                            <label class="control-label">Доставить</label>
                            <div class="radio">
                                <label>
                                    <input name="WHO_DELIVERY" value="248" type="radio" <?=($who_delivery == 248) ? 'checked=""' : '';?>>
                                    По адресу
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="WHO_DELIVERY" value="249" type="radio" <?=($who_delivery == 249) ? 'checked=""' : '';?>>
                                    До востребования
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="WHO_DELIVERY" value="250" type="radio" <?=($who_delivery == 250) ? 'checked=""' : '';?>>
                                    Лично в руки
                                </label>
                            </div>
                        </div>

                        <?php // Для клиентов Сухой и Абсолют страхование
                        if ($arResult['CURRENT_CLIENT']   == 41478141 || $arResult['CURRENT_CLIENT'] == 56103010) { ?>
                            <div class="form-group">
                                <label class="control-label">Доставить до даты:</label>
                                <div class="row">
                                    <div class="col-md-10">
                                        <div class="input-group">
                                            <?
                                            $TO_DELIVER_BEFORE_DATE = "";
                                            if(!empty($_POST['TO_DELIVER_BEFORE_DATE'])){
                                                $TO_DELIVER_BEFORE_DATE = $_POST['TO_DELIVER_BEFORE_DATE'];
                                            }elseif(!empty($arResult['DEAULTS']['TO_DELIVER_BEFORE_DATE'])){
                                                $dt = strtotime ($arResult['DEAULTS']['TO_DELIVER_BEFORE_DATE']);

                                                $TO_DELIVER_BEFORE_DATE = date("d.m.Y H:i",$dt);
                                            }

                                            ?>
                                            <input type="text" class="form-control maskdatetime" placeholder="ДД.ММ.ГГГГ чч:мм" value="<?=$TO_DELIVER_BEFORE_DATE ;?>" name="TO_DELIVER_BEFORE_DATE">

                                            <div class="input-group-addon">
                                                <?
                                                $APPLICATION->IncludeComponent(
                                                    "bitrix:main.calendar",
                                                    ".default",
                                                    array(
                                                        "SHOW_INPUT" => "N",
                                                        "FORM_NAME" => "curform",
                                                        "INPUT_NAME" => "TO_DELIVER_BEFORE_DATE",
                                                        "INPUT_NAME_FINISH" => "",
                                                        "INPUT_VALUE" => $_POST['TO_DELIVER_BEFORE_DATE'],
                                                        "INPUT_VALUE_FINISH" => false,
                                                        "SHOW_TIME" => "Y",
                                                        "HIDE_TIMEBAR" => "N",
                                                    ),
                                                    false
                                                );
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <? }; ?>

                        <div class="form-group <?=$arResult['ERR_FIELDS']['IN_DATE_DELIVERY'];?>">
                            <label class="control-label">Доставить в дату</label>
                            <div class="row">
                                <div class="col-md-10">
                                    <div class="input-group">
                                        <input type="text" class="form-control maskdate" placeholder="ДД.ММ.ГГГГ" value="<?=$_POST['IN_DATE_DELIVERY'];?>" name="IN_DATE_DELIVERY">
                                        <div class="input-group-addon">
                                            <?
                                            $APPLICATION->IncludeComponent(
                                                "bitrix:main.calendar",
                                                ".default",
                                                array(
                                                    "SHOW_INPUT" => "N",
                                                    "FORM_NAME" => "curform",
                                                    "INPUT_NAME" => "IN_DATE_DELIVERY",
                                                    "INPUT_NAME_FINISH" => "",
                                                    "INPUT_VALUE" => $_POST['IN_DATE_DELIVERY'],
                                                    "INPUT_VALUE_FINISH" => false,
                                                    "SHOW_TIME" => "N",
                                                    "HIDE_TIMEBAR" => "Y",
                                                ),
                                                false
                                            );
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group <?=$arResult['ERR_FIELDS']['IN_TIME_DELIVERY'];?>">
                            <div class="row">
                                <div class="col-md-10">
                                    <label class="control-label">Доставить до часа</label>
                                    <input type="text" class="form-control masktime" name="IN_TIME_DELIVERY" value="<?=$_POST['IN_TIME_DELIVERY'];?>" placeholder="ЧЧ:ММ">
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-6">
                        <h4>Условия оплаты</h4>

                        <div class="form-group <?=$arResult['ERR_FIELDS']['PAYMENT'];?>">
                            <?
                            $payment = ($_POST['PAYMENT']) ?  ($_POST['PAYMENT']) : $arResult['DEAULTS']['PAYMENT'];
                            ?>
                            <label class="control-label">Оплата</label>
                            <div class="radio">
                                <label>
                                    <input name="PAYMENT" value="255" type="radio" on <?=($payment == 255) ? 'checked=""' : '';?>>
                                    Наличными
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="PAYMENT" value="256" type="radio" <?=($payment == 256) ? 'checked=""' : '';?>>
                                    По счету
                                </label>
                            </div>
                        </div>

                        <div class="form-group <?=$arResult['ERR_FIELDS']['TYPE_PAYS'];?>">
                            <?
                            $type_pays = ($_POST['TYPE_PAYS']) ?  ($_POST['TYPE_PAYS']) : $arResult['DEAULTS']['TYPE_PAYS'];
                            ?>
                            <label class="control-label">Оплачивает</label>
                            <div class="radio">
                                <label>
                                    <input name="TYPE_PAYS" value="251" type="radio" <?=($type_pays == 251) ? 'checked=""' : '';?>>
                                    Отправитель
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="TYPE_PAYS" value="252" type="radio" <?=($type_pays == 252) ? 'checked=""' : '';?> id="type_pays_252">
                                    Получатель
                                </label>
                            </div>
                            <div class="radio<?=($payment == 256) ? '' : ' hidden';?>" id="type_pays_253_block">
                                <label>
                                    <input name="TYPE_PAYS" value="253" type="radio" <?=($type_pays == 253) ? 'checked=""' : '';?>>
                                    Другой
                                </label>
                            </div>
                        </div>

                        <div class="form-group <?=$arResult['ERR_FIELDS']['WHOSE_ORDER'];?> <?=(($payment == 256) && (($type_pays == 252) || ($type_pays == 253))) ? '' : ' hidden';?>" id="whose_order_block">
                            <div class="row">
                                <div class="col-md-10">
                                    <? if ((count($arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT']) > 0) && (is_array($arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT']))) { ?>
                                        <select class="form-control" name="WHOSE_ORDER">
                                            <? if (count($arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT']) > 1) { ?>
                                                <option value="0"></option>
                                            <? }?>
                                            <? foreach ($arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT'] as $k => $v) { ?>
                                                <option value="<?=$k;?>" <?=($_POST['WHOSE_ORDER'] == $k) ? 'selected' : '';?>><?=$v;?></option>
                                            <? } ?>
                                            <option value="<?echo $arResult['CURRENT_CLIENT_INFO']['ID'];?>" <?=($_POST['WHOSE_ORDER'] == $arResult['CURRENT_CLIENT_INFO']['ID']) ? 'selected' : '';?>><? echo $arResult['CURRENT_CLIENT_INFO']['NAME']; ?></option>
                                        </select>
                                    <? } else { ?>
                                        <? // может заказывать и из текстового поля:  платит заказчик (другой -> с именем заказчика) ?>
                                        <?
                                        if ((isset($_POST['PAYS']))||($_POST['PAYS']==""))
                                        {$client_pay = $arResult['CURRENT_CLIENT_INFO']['NAME'];}
                                        else
                                        {$client_pay = $_POST['PAYS'];}
                                        ?>
                                        <input type="text" class="test-form-control form-control" name="PAYS"        value="<?=$client_pay ;?>">
                                        <? /* <input type="text" class="form-control" name="WHOSE_ORDER" value="<?=$_POST['PAYS'];?>"> */ ?>
                                    <? }; ?>
                                </div>
                            </div>
                        </div>



                        <?
                        //TODO Вынести в настройки для компании
                        // if ($arResult['CURRENT_CLIENT'] == 16734506) :
                        ?>
                        <div class="form-group <?=$arResult['ERR_FIELDS']['FOR_PAYMENT'];?>">
                            <div class="row">
                                <div class="col-md-10">
                                    <label class="control-label">К оплате</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="FOR_PAYMENT" value="<?=$_POST['FOR_PAYMENT'];?>" placeholder="0,00" aria-describedby="basic-addon-2" id="payment-value">
                                        <span class="input-group-addon" id="basic-addon-2">руб.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <? //endif; ?>
                        <div class="form-group <?=$arResult['ERR_FIELDS']['PAYMENT_COD'];?>">
                            <div class="row">
                                <div class="col-md-10">
                                    <label class="control-label">Сумма наложенного платежа</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="PAYMENT_COD" value="<?=$_POST['PAYMENT_COD'];?>" placeholder="0,00" aria-describedby="basic-addon-3" id="payment-cod-value">
                                        <span class="input-group-addon" id="basic-addon-3">руб.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group <?=$arResult['ERR_FIELDS']['COST'];?>">
                            <div class="row">
                                <div class="col-md-10">
                                    <label class="control-label">Объявленная стоимость</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="COST" value="<?=$_POST['COST'];?>" placeholder="0,00" aria-describedby="basic-addon-1" id="cost-value">
                                        <span class="input-group-addon" id="basic-addon-1">руб.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label">
                                <span class="glyphicon glyphicon-remove" aria-hidden="true" style="color:#b94a48;" id="cost-marker"></span> Заявка на страхование
                            </label>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">

                <?php  // что бы не считать == 5 ?><input class="quantityItem" type="hidden" value="5">
                <table class="table table-bordered" id="calculated_values">
                    <thead>
                    <tr>
                        <th width="50%">Описание отправления</th>
                        <th width="10%">Мест</th>
                        <th width="10%">Вес</th>
                        <th colspan="3" width="30%">Габариты</th>
                    </tr>
                    </thead>
                    <tbody id="description">
                    <?php   // echo  description add-on begin::?>
                    <tr >
                        <td class="test1">
                            <?php if(!empty($_POST['pack_description'][0]['name'])):?>
                                <input type="text" class="form-control"
                                       name="pack_description[0][name]"
                                       value="<?=$_POST['pack_description'][0]['name'];?>" id="pack_description_first">
                            <?php elseif(strlen($arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][0]['name'])):?>
                                <input type="text" class="form-control"
                                       name="pack_description[0][name]"
                                       value="<?=$arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][0]['name'];?>"
                                       id="pack_description_first">
                            <?php else:?>
                                <input type="text" class="form-control"
                                       name="pack_description[0][name]"
                                       value="<?=$type_pack_name;?>"
                                       id="pack_description_first">
                            <?php endif;?>
                        </td>
                        <td>
                            <?php if(!empty($_POST['pack_description'][0]['place'])):?>
                                <input type="text" class="form-control" name="pack_description[0][place]"
                                       value="<?=$_POST['pack_description'][0]['place'];?>">
                            <?php elseif(!empty($arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][0]['place'])):?>
                                <input type="text" class="form-control" name="pack_description[0][place]"
                                       value="<?=$arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][0]['place'];?>">
                            <?php else:?>
                                <input type="text" class="form-control" name="pack_description[0][place]"
                                       value="<?=$arResult['DEAULTS']['PLACES'];?>">
                            <?php endif;?>
                        </td>
                        <td class="test2">
                            <div class="input-group">
                                <?php if(!empty($_POST['pack_description'][0]['weight'])):?>
                                    <input type="text" class="form-control" name="pack_description[0][weight]"
                                           value="<?=$_POST['pack_description'][0]['weight'];?>">
                                    <div class="input-group-addon">кг</div>
                                <?php elseif(!empty($arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][0]['weight'])):?>
                                    <input type="text" class="form-control" name="pack_description[0][weight]"
                                           value="<?=$arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][0]['weight'];?>">
                                    <div class="input-group-addon">кг</div>
                                <?php else:?>
                                    <input type="text" class="form-control" name="pack_description[0][weight]"
                                           value="<?=$arResult['DEAULTS']['WEIGHT'];?>">
                                    <div class="input-group-addon">кг</div>
                                <?php endif;?>
                            </div>
                        </td>
                        <td class="test3">
                            <div class="input-group">
                                <?php if(!empty($_POST['pack_description'][0]['size'][0])):?>
                                    <input type="text" class="form-control" name="pack_description[0][size][0]"
                                           value="<?=$_POST['pack_description'][0]['size'][0];?>">
                                    <div class="input-group-addon">см</div>
                                <?php elseif(!empty($arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][0]['size'][0])):?>
                                    <input type="text" class="form-control" name="pack_description[0][size][0]"
                                           value="<?=$arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][0]['size'][0];?>">
                                    <div class="input-group-addon">см</div>
                                <?php else:?>
                                    <input type="text" class="form-control" name="pack_description[0][size][0]"
                                           value="">
                                    <div class="input-group-addon">см</div>
                                <?php endif;?>

                            </div>
                        </td>
                        <td class="test4">
                            <div class="input-group">
                                <?php if(!empty($_POST['pack_description'][0]['size'][1])):?>
                                    <input type="text" class="form-control" name="pack_description[0][size][1]"
                                           value="<?=$_POST['pack_description'][0]['size'][1];?>">
                                    <div class="input-group-addon">см</div>
                                <?php elseif(!empty($arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][0]['size'][1])):?>
                                    <input type="text" class="form-control" name="pack_description[0][size][1]"
                                           value="<?=$arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][0]['size'][1];?>">
                                    <div class="input-group-addon">см</div>
                                <?php else:?>
                                    <input type="text" class="form-control" name="pack_description[0][size][1]"
                                           value="">
                                    <div class="input-group-addon">см</div>
                                <?php endif;?>
                            </div>
                        </td>
                        <td class="test5">
                            <div class="input-group">
                                <?php if(!empty($_POST['pack_description'][0]['size'][2])):?>
                                    <input type="text" class="form-control" name="pack_description[0][size][2]"
                                           value="<?=$_POST['pack_description'][0]['size'][2];?>">
                                    <div class="input-group-addon">см</div>
                                <?php elseif(!empty($arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][0]['size'][2])):?>
                                    <input type="text" class="form-control" name="pack_description[0][size][2]"
                                           value="<?=$arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][0]['size'][2];?>">
                                    <div class="input-group-addon">см</div>
                                <?php else:?>
                                    <input type="text" class="form-control" name="pack_description[0][size][2]"
                                           value="">
                                    <div class="input-group-addon">см</div>
                                <?php endif;?>
                            </div>
                        </td>
                    </tr>
                    <?php   // echo description add-on  end::?>

                    <?php for($i=1; $i<=4; $i++):?>
                        <tr>
                            <td>
                                <?php if(!empty($_POST['pack_description'][$i]['name'])):?>
                                    <input type="text" class="form-control" name="pack_description[1][name]"
                                           value="<?=$_POST['pack_description'][$i]['name'];?>">
                                <?php elseif(!empty($arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][$i]['name'])):?>
                                    <input type="text" class="form-control" name="pack_description[<?=$i;?>][name]"
                                           value="<?=$arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][$i]['name'];?>">
                                <?php else:?>
                                    <input type="text" class="form-control" name="pack_description[<?=$i;?>][name]"
                                           value="">
                                <?php endif;?>
                            </td>
                            <td>
                                <?php if(!empty($_POST['pack_description'][$i]['place'])):?>
                                    <input type="text" class="form-control" name="pack_description[<?=$i;?>][place]"
                                           value="<?=$_POST['pack_description'][$i]['place'];?>">
                                <?php elseif(!empty($arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][$i]['place'])):?>
                                    <input type="text" class="form-control" name="pack_description[<?=$i;?>][place]"
                                           value="<?=$arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][$i]['place'];?>">
                                <?php else:?>
                                    <input type="text" class="form-control" name="pack_description[<?=$i;?>][place]"
                                           value="">
                                <?php endif;?>
                            </td>
                            <td>
                                <div class="input-group">
                                    <?php if(!empty($_POST['pack_description'][$i]['weight'])):?>
                                        <input type="text" class="form-control" name="pack_description[<?=$i;?>][weight]"
                                               value="<?=$_POST['pack_description'][$i]['weight'];?>">
                                        <div class="input-group-addon">кг</div>
                                    <?php elseif(!empty($arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][$i]['weight'])):?>
                                        <input type="text" class="form-control" name="pack_description[<?=$i;?>][weight]"
                                               value="<?=$arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][$i]['weight'];?>">
                                        <div class="input-group-addon">кг</div>
                                    <?php else:?>
                                        <input type="text" class="form-control" name="pack_description[<?=$i;?>][weight]"
                                               value="">
                                        <div class="input-group-addon">кг</div>
                                    <?php endif;?>
                                </div>
                            </td>
                            <td>
                                <div class="input-group">
                                    <?php if(!empty($_POST['pack_description'][$i]['size'][0])):?>
                                        <input type="text" class="form-control" name="pack_description[<?=$i;?>][size][0]"
                                               value="<?=$_POST['pack_description'][$i]['size'][0];?>">
                                        <div class="input-group-addon">см</div>
                                    <?php elseif(!empty($arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][$i]['size'][0])):?>
                                        <input type="text" class="form-control" name="pack_description[<?=$i;?>][size][0]"
                                               value="<?=$arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][$i]['size'][0];?>">
                                        <div class="input-group-addon">см</div>
                                    <?php else:?>
                                        <input type="text" class="form-control" name="pack_description[<?=$i;?>][size][0]"
                                               value="">
                                        <div class="input-group-addon">см</div>
                                    <?php endif;?>
                                </div>
                            </td>
                            <td>
                                <div class="input-group">
                                    <?php if(!empty($_POST['pack_description'][$i]['size'][1])):?>
                                        <input type="text" class="form-control" name="pack_description[<?=$i;?>][size][1]"
                                               value="<?=$_POST['pack_description'][$i]['size'][1];?>">
                                        <div class="input-group-addon">см</div>
                                    <?php elseif(!empty($arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][$i]['size'][1])):?>
                                        <input type="text" class="form-control" name="pack_description[<?=$i;?>][size][1]"
                                               value="<?=$arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][$i]['size'][1];?>">
                                        <div class="input-group-addon">см</div>
                                    <?php else:?>
                                        <input type="text" class="form-control" name="pack_description[<?=$i;?>][size][1]"
                                               value="">
                                        <div class="input-group-addon">см</div>
                                    <?php endif;?>
                                </div>
                            </td>
                            <td>
                                <div class="input-group">
                                    <?php if(!empty($_POST['pack_description'][$i]['size'][2])):?>
                                        <input type="text" class="form-control" name="pack_description[<?=$i;?>][size][2]"
                                               value="<?=$_POST['pack_description'][$i]['size'][2];?>">
                                        <div class="input-group-addon">см</div>
                                    <?php elseif(!empty($arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][$i]['size'][2])):?>
                                        <input type="text" class="form-control" name="pack_description[<?=$i;?>][size][2]"
                                               value="<?=$arResult['DEAULTS']['PACK_DESCRIPTION_VALUE'][$i]['size'][2];?>">
                                        <div class="input-group-addon">см</div>
                                    <?php else:?>
                                        <input type="text" class="form-control" name="pack_description[<?=$i;?>][size][2]"
                                               value="">
                                        <div class="input-group-addon">см</div>
                                    <?php endif;?>
                                </div>
                            </td>
                        </tr>
                    <?php endfor;?>

                    </tbody>
                    <tfoot>
                    <tr>
                        <th>До 0,5 кг – минимальная единая цена отправления документов и грузов, далее по Москве шаг для расчета – 1 кг, по России и за рубеж - 0,5 кг</th>
                        <th>
                            <div class="form-group <?=$arResult['ERR_FIELDS']['PLACES'];?>">
                                <small>Мест Итого: </small>
                                <input type="text" class="form-control" disabled id="total_place">
                            </div>
                        </th>
                        <th>
                            <div class="form-group <?=$arResult['ERR_FIELDS']['WEIGHT'];?>">
                                <div class="input-group">
                                    <small>Вес Итого(кг): </small> <input type="text" class="form-control" disabled
                                                                          id="total_weight">
                                    <!--<div class="input-group-addon">кг</div>-->
                                </div>
                            </div>
                        </th>
                        <th >
                            <div class="form-group <?=$arResult['ERR_FIELDS']['SIZE'];?>">
                                <div class="input-group">
                                    <small>Объемный вес Итого(кг): </small><input type="text" class="form-control"
                                                                                  disabled id="total_gabweight">
                                    <!-- <div class="input-group-addon">кг</div>-->
                                </div>
                            </div>
                            <?php
                            // Для клиента Абсолют страхование  || $arResult['CURRENT_CLIENT'] == 56103010
                            if($USER->isAdmin() || $arResult['CURRENT_CLIENT'] == 56103010):
                            $client =  (int)$arResult['CURRENT_CLIENT'];
                            $arFilter = [
                                "ID" => $client
                            ];
                            $arSelect = [
                                "ID", "NAME","PROPERTY_INN"
                            ];
                            $resArr = GetInfoArr(false, false, 40, $arSelect, $arFilter);
                            $client_inn = (int)$resArr["PROPERTY_INN_VALUE"];
                            ?>
                        <th colspan="2">
                            <div style="display: flex; flex-direction: row">
                                <div style="display:flex; flex-direction: column; justify-content: start"
                                     class="form-group">
                                    <small style="display:block">Узнать стоимость </small>
                                    <div style="width: 100%;">
                                        <button id="sum_pay_request" data_client="<?= $client_inn ?>"
                                                class="btn btn-success" type="button">
                                            Рассчитать
                                        </button>
                                    </div>

                                </div>
                                <div id="pay_info_client"></div>
                            </div>
                        </th>
                        <?php endif;?>
                    </tr>
                    </tfoot>
                </table>
                <?php // если тестовый клиент ?>
                <?php if (($arResult['CURRENT_CLIENT'] == 41478141) || ($arResult['CURRENT_CLIENT'] == 9528186)) { ?>
                    <a id="addFields" class="btn btn-default pull-left btn-sm">Добавить описание отправления</a>
                <?php } ?>
                <?php
                //echo "<!-- 123456 <pre>";
                //print_r ($arResult);
                //echo "<pre> -->";
                ?>
                <!-- // 14082134 -->
            </div>

        </div>
        <br><br>
        <?
        $count_goods = ((int)$_POST['count_goods'] > 1) ? (int)$_POST['count_goods'] : 1;
        ?>
        <div class="row">
            <div class="col-md-12 column">
                <input type="hidden" name="count_goods" value="<?=$count_goods;?>" id="count_goods">
                <h4>Товары</h4>
                <table class="table table-bordered" style="margin-bottom:5px;" id="tab_logic">
                    <thead>
                    <tr>
                        <th width="30%">Наименование товара</th>
                        <th>Артикул</th>
                        <th>Количество</th>
                        <th>Цена за 1 шт., включая НДС</th>
                        <th>Сумма, включая НДС</th>
                        <th>Сумма НДС</th>
                        <th>Ставка НДС</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr id="addr0">
                        <td><input type="text" name="goods[0][name]" value="<?=$_POST['goods'][0]['name'];?>" class="form-control"></td>
                        <td>
                            <div class="input-group">
                                <input type="text" name="goods[0][art]" value="<?=$_POST['goods'][0]['art'];?>" class="form-control" aria-describedby="good-addon-0-0" id="input-goods-art-0" >
                                <span class="input-group-addon" id="good-addon-0-0">Арт.</span>
                            </div>
                        </td>
                        <td>
                            <div class="input-group">
                                <input type="text" name="goods[0][amount]" value="<?=$_POST['goods'][0]['amount'];?>" class="form-control" aria-describedby="good-addon-0-1" id="input-goods-amount-0" onChange="CalcGoods('0');">
                                <span class="input-group-addon" id="good-addon-0-1">шт.</span>
                            </div>
                        </td>
                        <td>
                            <div class="input-group">
                                <input type="text" name="goods[0][price]" value="<?=$_POST['goods'][0]['price'];?>" class="form-control" aria-describedby="good-addon-0-2" id="input-goods-price-0" onChange="CalcGoods('0');">
                                <span class="input-group-addon" id="good-addon-0-2">руб.</span>
                            </div>
                        </td>
                        <td>
                            <div class="input-group">
                                <input type="text" name="goods[0][sum]" value="<?=$_POST['goods'][0]['sum'];?>" class="form-control" aria-describedby="good-addon-0-3" id="input-goods-sum-0">
                                <span class="input-group-addon" id="good-addon-0-3">руб.</span>
                            </div>
                        </td>
                        <td>
                            <div class="input-group">
                                <input type="text" name="goods[0][sumnds]" value="<?=$_POST['goods'][0]['sumnds'];?>" class="form-control" aria-describedby="good-addon-0-4" id="input-goods-sumnds-0">
                                <span class="input-group-addon" id="good-addon-0-4">руб.</span>
                            </div>
                        </td>
                        <td>
                            <select size="1" name="goods[0][persentnds]" class="form-control" id="input-goods-persentnds-0" onChange="CalcGoods('0');">
                                <option value="20"<?=(((int)$_POST['goods'][0]['persentnds'] == 20) && isset($_POST['goods'][0]['persentnds'])) ? ' selected' : '';?>>20%</option>
                                <option value="10"<?=(((int)$_POST['goods'][0]['persentnds'] == 10) && isset($_POST['goods'][0]['persentnds'])) ? ' selected' : '';?>>10%</option>
                                <option value="0"<?=(((int)$_POST['goods'][0]['persentnds'] == 0) && isset($_POST['goods'][0]['persentnds'])) ? ' selected' : '';?>>Без НДС</option>
                            </select>
                        </td>
                    </tr>
                    <? foreach ($_POST['goods'] as $k => $v) : ?>
                        <? if ($k > 0) : ?>
                            <tr id="addr<?=$k;?>">
                                <td><input type="text" name="goods[<?=$k;?>][name]" value="<?=$_POST['goods'][$k]['name'];?>" class="form-control"></td>
                                <td>
                                    <div class="input-group">
                                        <input type="text" name="goods[<?=$k;?>][art]" value="<?=$_POST['goods'][$k]['art'];?>" class="form-control" aria-describedby="good-addon-0-0" id="input-goods-art-<?=$k;?>" >
                                        <span class="input-group-addon" id="good-addon-0-0">Арт.</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="text" name="goods[<?=$k;?>][amount]" value="<?=$_POST['goods'][$k]['amount'];?>" class="form-control" aria-describedby="good-addon-0-1" onChange="CalcGoods('<?=$k;?>');" id="input-goods-amount-<?=$k;?>">
                                        <span class="input-group-addon" id="good-addon-0-1">шт.</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="text" name="goods[<?=$k;?>][price]" value="<?=$_POST['goods'][$k]['price'];?>" class="form-control" aria-describedby="good-addon-0-2" onChange="CalcGoods('<?=$k;?>');" id="input-goods-price-<?=$k;?>">
                                        <span class="input-group-addon" id="good-addon-0-2">руб.</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="text" name="goods[<?=$k;?>][sum]" value="<?=$_POST['goods'][$k]['sum'];?>" class="form-control" aria-describedby="good-addon-0-3" id="input-goods-sum-<?=$k;?>">
                                        <span class="input-group-addon" id="good-addon-0-3">руб.</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="text" name="goods[<?=$k;?>][sumnds]" value="<?=$_POST['goods'][$k]['sumnds'];?>" class="form-control" aria-describedby="good-addon-0-4" id="input-goods-sumnds-<?=$k;?>">
                                        <span class="input-group-addon" id="good-addon-0-4">руб.</span>
                                    </div>
                                </td>
                                <td>
                                    <select size="1" name="goods[<?=$k;?>][persentnds]" class="form-control" onChange="CalcGoods('<?=$k;?>');" id="input-goods-persentnds-<?=$k;?>">
                                        <option value="20"<?=(((int)$_POST['goods'][$k]['persentnds'] == 20) && isset($_POST['goods'][$k]['persentnds'])) ? ' selected' : '';?>>20%</option>
                                        <option value="10"<?=(((int)$_POST['goods'][$k]['persentnds'] == 10) && isset($_POST['goods'][$k]['persentnds'])) ? ' selected' : '';?>>10%</option>
                                        <option value="0"<?=(((int)$_POST['goods'][$k]['persentnds'] == 0) && isset($_POST['goods'][$k]['persentnds'])) ? ' selected' : '';?>>Без НДС</option>
                                    </select>
                                </td>
                            </tr>
                        <? endif;?>
                    <? endforeach;?>
                    <tr id="addr<?=$count_goods;?>"></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row" style="margin-bottom:20px;">
            <div class="col-sm-12">
                <a id="add_row" class="btn btn-default pull-left btn-sm">Добавить товар</a><a id="delete_row" class="pull-right btn btn-default btn-sm">Удалить последний товар</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group <?=$arResult['ERR_FIELDS']['INSTRUCTIONS'];?>">
                    <label class="control-label">Специальные инструкции</label>
                    <!--$arResult['DEAULTS']['INSTRUCTIONS']-->
                    <textarea name="INSTRUCTIONS" class="form-control" style="height:100px; resize:vertical;"><?=($_POST['INSTRUCTIONS']) ?  ($_POST['INSTRUCTIONS']) : "";?></textarea>
                </div>
            </div>
            <?php
            // $arResult['CURRENT_CLIENT'] === '36015676'
            if($arResult['CURRENT_CLIENT'] == 36015676 || $USER->isAdmin()):?>
                <div class="col-md-6">
                    <div class="form-group <?=$arResult['ERR_FIELDS']['INSTRUCTIONS'];?>">
                        <label class="control-label">Примечание</label>
                        <!--$arResult['DEAULTS']['INSTRUCTIONS']-->
                        <textarea name="NOTE" class="form-control" style="height:100px; resize:vertical;"><?=($_POST['NOTE']) ?  ($_POST['NOTE']) : "";?></textarea>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?
        if ((isset($_POST['add'])) || (isset($_POST['add-print'])) || (isset($_POST['add_ctrl'])))
        {
            $callCurVal = $_POST['callcourier'];
        }
        else
        {
            $callCurVal = $arResult['DEAULTS']['callcourier'];
        }
        ?>
        <?php if($USER->GetID() != 4757):?>
            <div class="row">
                <div class="col-md-12">
                    <?php
                    if($arResult['CURRENT_CLIENT'] == 41478141){
                        $style='visibility: hidden;';
                    }else{
                        $style= "visibility: visible;";
                    }
                    ?>
                    <div style="<?=$style;?> " class="checkbox">
                        <label  class="control-label btn-success btn" style="padding-left: 30px;">
                            <input type="checkbox" name="callcourier" value="yes" onChange="functioncallcourier();"
                                   id="callcourier" <?=($callCurVal == 'yes') ? ' checked' : ''?>> Требуется вызов курьера
                        </label>
                        Без выбора этой опции курьер не будет вызван, а накладная сохранится в личном кабинете и будет доступна для редактирования.
                    </div>

                    <div>
                        <div class="well">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label"><?=GetMessage("LABEL_callcourierdate")?></label>
                                        <div class="input-group <?=$arResult["ERR_FIELDS"]["callcourierdate"]?>">
                                            <input required disabled id="callcourierdate" type="text"
                                                   value="<?=(strlen($_POST["callcourierdate"])) ?
                                                       $_POST["callcourierdate"] : $arResult['DEAULTS']['callcourierdate']?>"
                                                   class="form-control maskdate" aria-describedby="basic-addon5"
                                                   name="callcourierdate" placeholder="ДД.ММ.ГГГГ">
                                            <span style="visibility: hidden" class="input-group-addon" id="basic-addon5">
												<?
                                                $APPLICATION->IncludeComponent(
                                                    "bitrix:main.calendar",
                                                    ".default",
                                                    [
                                                        "SHOW_INPUT" => "N",
                                                        "FORM_NAME" => "curform",
                                                        "INPUT_NAME" => "callcourierdate",
                                                        "INPUT_NAME_FINISH" => "",
                                                        "INPUT_VALUE" => $_POST["callcourierdate"],
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
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label class="control-label">&nbsp;</label>
                                        <input disabled id="callcourtime_from" type="text" name="callcourtime_from" value="<?=(strlen($_POST["callcourtime_from"])) ? $_POST["callcourtime_from"] : $arResult['DEAULTS']['callcouriertime_from'];?>" class="form-control masktime" placeholder="ЧЧ:ММ">
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label class="control-label">&nbsp;</label>
                                        <input disabled id="callcourtime_to" type="text" name="callcourtime_to" value="<?=(strlen($_POST["callcourtime_to"])) ? $_POST["callcourtime_to"] : $arResult['DEAULTS']['callcouriertime_to'];?>" class="form-control masktime" placeholder="ЧЧ:ММ">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="control-label">Комментарий курьеру:</label>
                                        <input id="callcourcomment" disabled type="text" name="callcourcomment" class="form-control" value="<?=$_POST['callcourcomment'];?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif;?>
        <?
        //   }
        ?>
        <div class="row">
            <div class="col-sm-12">
                <div class="btn-group btn-group-lg">
                    <button type="submit" name="add" class="btn btn-primary" id="add-btn-tour">Создать
                        <span class="badge">CTRL+Enter</span></button>
                    <input type="submit" name="add-print" value="Создать и распечатать" class="btn btn-default">
                </div>
            </div>
        </div>
    </form>
    <?
}
?>

<!-- Modal -->
<div class="modal fade" id="add_town" tabindex="-1" role="dialog" aria-labelledby="addtownLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="addtownLabel">Добавить населенный пункт.</h4>
            </div>
            <form id="form_add_g" method="POST">
                <div class="input-hidden"></div>
                <div class="modal-body">

                    <ul style="font-weight: bold">  Правила ввода: вводить данные рекомендуется в следующей последовательности -
                        <li style="font-style: italic">1. Область ('Ярославская обл'),</li>
                        <li style="font-style: italic">2. Населенный пункт (город/поселок/деревня и тп 'село Некрасово'),</li>
                        <li style="font-style: italic">3. Адрес (вводить если хотите сразу добавить адрес в накладную - необязательно)</li>
                        <li><small>Далее выбрать в списке ниже то что подходит.</small></li>
                    </ul>

                    <div class="form-group">
                        <label class="control-label">Населенный пункт</label>
                        <input id="address" class="form-control" name="address" type="text" placeholder="начинайте вводить..."/>
                        <small for="address">
                            Если Вашего нас. пункта нет в выпадающем списке, обратитесь в техроддержку для внесения его в базу.
                        </small>
                    </div>

                </div>
                <div id="alert-err" class="alert alert-danger alert-dismissible fade in" role="alert">
                    <!-- <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                         <span aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i></span></button>-->
                    <span id="results"></span>
                </div>
                <div class="modal-footer">
                    <button id="btn-close" type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                    <button id="add_geography_modal" name="add_g" type="button" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>
<br>

<script>
    $("#address").suggestions({
        token: "2e413a3320e9d06cbe2cd4a92e9cfd7fe71ff1c6",   // токен DData
        type: "ADDRESS",
        /* Вызывается, когда пользователь выбирает одну из подсказок */
        onSelect: function(suggestion) {
            let fragment = '';
            let container = document.querySelector('#form_add_g .input-hidden');
            let err = document.querySelector('#alert-err');
            let errData = err.querySelector('#results');
            err.setAttribute('style', 'display: none');
            errData.innerHTML = '';
            container.innerHTML = '';
            let data = Object.values(suggestion);
            if(!data.length){
                err.setAttribute('style', 'display: block');
                errData.insertAdjacentHTML('afterbegin',`<p style="color: red;">Произошла ошибка! Возможно, Ваш браузер не поддерживает данный функционал.<br>
                        Для добавления города попробуйте обновить/сменить браузер, или обратитесь к менеджеру.<p>`);
                return;
            }
            data.forEach(function(item, key){
                if(typeof item === 'object' ){
                    let items = Object.entries(item);
                    items.forEach(function(v){
                        if(v[1] !== null && v[1] != 0){
                            fragment += `<input type="hidden" name="${v[0]}" value="${v[1]}">`;
                        }
                    });
                }else{
                    if (key == 0){
                        fragment += `<input type="hidden" name="adress_form" value="${item}">`;
                    }
                    if (key == 1){
                        fragment += `<input type="hidden" name="full_adress_form" value="${item}">`;
                    }
                }
            });
            container.insertAdjacentHTML('afterbegin',fragment);
        }
    });
</script>