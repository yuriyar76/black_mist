<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
?>

<script type="text/javascript">
	$(document).ready(function(){
		AutoCompany();
		AutoCity();
		CalculateValues();
		$("#calculated_values input").change(function() {
			CalculateValues();
		});
		
		$('.maskdate').mask('99.99.9999');
		$('.masktime').mask('99:99');
		
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
            $('#addr'+i).html('<td><input type="text" name="goods['+i+'][name]" value="" class="form-control"></td>'+
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
                                    '<option value="18">18%</option>'+
                                    '<option value="0">0%</option>'+
                                    '<option value="10">10%</option>'+
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
    
	function AutoCompany()
	{
		var url = '/search_city.php?type=name_company&company=<?=$arResult["AGENT"]["ID"];?>&branch=<?=intval($arResult["CURRENT_BRANCH"]);?>';
		$('#company').autocomplete({
			source: url,
			minLength: 0,
			select: function( event, ui ) {
				$(this).val( ui.item.company);
				$('#name').val(ui.item.name);
				$('#phone').val(ui.item.phone);
				$('#autocity_recipient').val(ui.item.city);
				$('#index').val(ui.item.index);
				$('#adress').val(ui.item.adress);
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
				$(this).val( ui.item.value);
				return false;
			}
		});
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
		var coefficient_vw = parseInt($('input[name="coefficient_vw"]').val(), 10) || 6000;
		
		var place1 = parseInt($('input[name="pack_description[0][place]"]').val(), 10) || 0;
		var place2 = parseInt($('input[name="pack_description[1][place]"]').val(), 10) || 0;
		var place3 = parseInt($('input[name="pack_description[2][place]"]').val(), 10) || 0;
		var place4 = parseInt($('input[name="pack_description[3][place]"]').val(), 10) || 0;
		var place5 = parseInt($('input[name="pack_description[4][place]"]').val(), 10) || 0;
		
		var weight1 = parseFloat($('input[name="pack_description[0][weight]"]').val().replace(/[,]+/g, '.')) || 0;
		var weight2 = parseFloat($('input[name="pack_description[1][weight]"]').val().replace(/[,]+/g, '.')) || 0;
		var weight3 = parseFloat($('input[name="pack_description[2][weight]"]').val().replace(/[,]+/g, '.')) || 0;
		var weight4 = parseFloat($('input[name="pack_description[3][weight]"]').val().replace(/[,]+/g, '.')) || 0;
		var weight5 = parseFloat($('input[name="pack_description[4][weight]"]').val().replace(/[,]+/g, '.')) || 0;
		
		var size11 = parseFloat($('input[name="pack_description[0][size][0]"]').val().replace(/[,]+/g, '.')) || 0;
		var size12 = parseFloat($('input[name="pack_description[0][size][1]"]').val().replace(/[,]+/g, '.')) || 0;
		var size13 = parseFloat($('input[name="pack_description[0][size][2]"]').val().replace(/[,]+/g, '.')) || 0;
		
		var size21 = parseFloat($('input[name="pack_description[1][size][0]"]').val().replace(/[,]+/g, '.')) || 0;
		var size22 = parseFloat($('input[name="pack_description[1][size][1]"]').val().replace(/[,]+/g, '.')) || 0;
		var size23 = parseFloat($('input[name="pack_description[1][size][2]"]').val().replace(/[,]+/g, '.')) || 0;
		
		var size31 = parseFloat($('input[name="pack_description[2][size][0]"]').val().replace(/[,]+/g, '.')) || 0;
		var size32 = parseFloat($('input[name="pack_description[2][size][1]"]').val().replace(/[,]+/g, '.')) || 0;
		var size33 = parseFloat($('input[name="pack_description[2][size][2]"]').val().replace(/[,]+/g, '.')) || 0;
		
		var size41 = parseFloat($('input[name="pack_description[3][size][0]"]').val().replace(/[,]+/g, '.')) || 0;
		var size42 = parseFloat($('input[name="pack_description[3][size][1]"]').val().replace(/[,]+/g, '.')) || 0;
		var size43 = parseFloat($('input[name="pack_description[3][size][2]"]').val().replace(/[,]+/g, '.')) || 0;
		
		var size51 = parseFloat($('input[name="pack_description[4][size][0]"]').val().replace(/[,]+/g, '.')) || 0;
		var size52 = parseFloat($('input[name="pack_description[4][size][1]"]').val().replace(/[,]+/g, '.')) || 0;
		var size53 = parseFloat($('input[name="pack_description[4][size][2]"]').val().replace(/[,]+/g, '.')) || 0;
		
		var gabweight1 = (size11*size12*size13)/coefficient_vw;
		var gabweight2 = (size21*size22*size23)/coefficient_vw;
		var gabweight3 = (size31*size32*size33)/coefficient_vw;
		var gabweight4 = (size41*size42*size43)/coefficient_vw;
		var gabweight5 = (size51*size52*size53)/coefficient_vw;
		
		var total_place = place1 + place2 + place3 + place4 + place5;
		var total_weight = weight1 + weight2 + weight3 + weight4 + weight5;
		var total_gabweight = gabweight1 + gabweight2 + gabweight3 + gabweight4 + gabweight5;


		$('#total_place').val(total_place);
		$('#total_weight').val(total_weight.toFixed(2));
		$('#total_gabweight').val(total_gabweight.toFixed(2));
	}
</script>

<div class="row">
		<div class="col-md-12">
            <h3><?=$arResult['TITLE'];?></h3>
        </div>
</div>
<?
if (count($arResult["ERRORS"]) > 0) 
{
	/*
	?>
    <div class="alert alert-dismissable alert-danger"><?=implode('</br>',$arResult["ERRORS"]);?></div>
    <?
	*/
}
if (count($arResult["MESSAGE"]) > 0) 
{
	?>
    <div class="alert alert-dismissable alert-success"><?=implode('</br>',$arResult["MESSAGE"]);?></div>
    <?
}
if (count($arResult["WARNINGS"]) > 0)
{
	?>
    <div class="alert alert-dismissable alert-warning"><?=implode('</br>',$arResult["WARNINGS"]);?></div>
    <?
}
if ($arResult['OPEN'])
{
	
	echo "<!-- <pre> invoice";
	print_r ($arResult['INVOICE']);
	echo "</pre> -->";
	
	
	if ($arResult['INVOICE'])
	{
	?>
	<form action="<?=$arParams['LINK'];?>?mode=edit" method="post" name="curform" class="form-vertical">
		<input type="hidden" name="rand" value="<?=rand(100000,999999);?>">
		<input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>">
        <input type="hidden" name="id" value="<?=$arResult['INVOICE']['ID'];?>">
        <input type="hidden" name="number" value="<?=$arResult['INVOICE']['NAME'];?>">
        <input type="hidden" name="save_ctrl" value="Сохранить">
        <div class="row">
        	 <div class="col-md-3">
             	<h4>Отправитель</h4>
				<div class="edit1 form-group <?=$arResult['ERR_FIELDS']['COMPANY_SENDER'];?>">
					<label class="control-label">Компания</label>
					<input type="text" class="form-control" name="COMPANY_SENDER" value="<?=$arResult['INVOICE']['PROPERTY_COMPANY_SENDER_VALUE'];?>">
				</div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['NAME_SENDER'];?>">
					<label class="control-label">Фамилия</label>
					<input type="text" class="form-control" name="NAME_SENDER" value="<?=$arResult['INVOICE']['PROPERTY_NAME_SENDER_VALUE'];?>">
				</div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['PHONE_SENDER'];?>">
					<label class="control-label">Телефон</label>
					<input type="text" class="form-control" name="PHONE_SENDER" value="<?=$arResult['INVOICE']['PROPERTY_PHONE_SENDER_VALUE'];?>">
				</div>
                <div class="form-group <?=$arResult['ERR_FIELDS']['CITY_SENDER'];?>">
                    <label class="control-label">Город</label>
                    <input type="text" class="form-control autocity" name="CITY_SENDER" value="<?=$arResult['INVOICE']['PROPERTY_CITY_SENDER'];?>" id="autocity_sender">
                </div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['INDEX_SENDER'];?>">
                    <label class="control-label">Индекс</label>
                    <input type="text" class="form-control" name="INDEX_SENDER" value="<?=$arResult['INVOICE']['PROPERTY_INDEX_SENDER_VALUE'];?>">
                </div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['ADRESS_SENDER'];?>">
                    <label class="control-label">Адрес</label>
                    <textarea class="form-control" name="ADRESS_SENDER"><?=$arResult['INVOICE']['PROPERTY_ADRESS_SENDER_VALUE']['TEXT'];?></textarea>
                </div>
				
				<div class="form-group">
                    <label class="control-label">Вн. номер заявки</label>
                    <textarea class="form-control" name="INNER_NUMBER_CLAIM"><?=$arResult['INVOICE']['PROPERTY_INNER_NUMBER_CLAIM_VALUE'];?></textarea>
                </div>
				
             </div>
             <div class="col-md-3 col-md-offset-1">
             	<h4>Получатель</h4>
				<div class="form-group <?=$arResult['ERR_FIELDS']['COMPANY_RECIPIENT'];?>">
					<label class="control-label">Компания</label>
					<input type="text" class="form-control" name="COMPANY_RECIPIENT" value="<?=$arResult['INVOICE']['PROPERTY_COMPANY_RECIPIENT_VALUE'];?>" id="company">
				</div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['NAME_RECIPIENT'];?>">
					<label class="control-label">Фамилия</label>
					<input type="text" class="form-control" name="NAME_RECIPIENT" value="<?=$arResult['INVOICE']['PROPERTY_NAME_RECIPIENT_VALUE'];?>" id="name">
				</div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['PHONE_RECIPIENT'];?>">
					<label class="control-label">Телефон</label>
					<input type="text" class="form-control" name="PHONE_RECIPIENT" value="<?=$arResult['INVOICE']['PROPERTY_PHONE_RECIPIENT_VALUE'];?>" id="phone">
				</div>
                <div class="form-group <?=$arResult['ERR_FIELDS']['CITY_RECIPIENT'];?>">
                    <label class="control-label">Город</label>
                    <input type="text" class="form-control autocity" name="CITY_RECIPIENT" value="<?=$arResult['INVOICE']['PROPERTY_CITY_RECIPIENT'];?>" id="autocity_recipient">
                </div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['INDEX_RECIPIENT'];?>">
                    <label class="control-label">Индекс</label>
                    <input type="text" class="form-control" name="INDEX_RECIPIENT" value="<?=$arResult['INVOICE']['PROPERTY_INDEX_RECIPIENT_VALUE'];?>" id="index">
                </div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['ADRESS_RECIPIENT'];?>">
                    <label class="control-label">Адрес</label>
                    <textarea class="form-control" name="ADRESS_RECIPIENT" id="adress"><?=$arResult['INVOICE']['PROPERTY_ADRESS_RECIPIENT_VALUE']['TEXT'];?></textarea>
                </div>
             </div>
             <div class="col-md-4 col-md-offset-1">
                <div class="row">
                	<div class="col-md-6">
                    	<h4>Условия доставки</h4>
                    
                    
						<div class="form-group <?=$arResult['ERR_FIELDS']['TYPE_DELIVERY'];?>">
                            <label class="control-label">Тип доставки</label>
                            <div class="radio">
                              <label>
                                <input name="TYPE_DELIVERY" value="243" type="radio" <?=($arResult['INVOICE']['PROPERTY_TYPE_DELIVERY_ENUM_ID'] == 243) ? 'checked=""' : '';?>>
                                Экспресс
                              </label>
                            </div>
                            <div class="radio">
                              <label>
                                <input name="TYPE_DELIVERY" value="244" type="radio" <?=($arResult['INVOICE']['PROPERTY_TYPE_DELIVERY_ENUM_ID'] == 244) ? 'checked=""' : '';?>>
                                Стандарт
                              </label>
                            </div>
                            <div class="radio">
                              <label>
                                <input name="TYPE_DELIVERY" value="245" type="radio" <?=($arResult['INVOICE']['PROPERTY_TYPE_DELIVERY_ENUM_ID'] == 245) ? 'checked=""' : '';?>>
                                Эконом
                              </label>
                            </div>
                            <div class="radio">
                              <label>
                                <input name="TYPE_DELIVERY" value="308" type="radio" <?=($arResult['INVOICE']['PROPERTY_TYPE_DELIVERY_ENUM_ID'] == 308) ? 'checked=""' : '';?>>
                                Склад-Склад
                              </label>
                            </div>
                            <div class="radio">
                              <label>
                                <input name="TYPE_DELIVERY" value="338" type="radio" <?=($arResult['INVOICE']['PROPERTY_TYPE_DELIVERY_ENUM_ID'] == 338) ? 'checked=""' : '';?>>
                                Экспресс 8
                              </label>
                            </div>
                        </div>
                            
						<div class="form-group <?=$arResult['ERR_FIELDS']['TYPE_PACK'];?>">
                            <label class="control-label">Тип отправления</label>
                            <div class="radio">
                              <label>
                                <input name="TYPE_PACK" value="246" type="radio" <?=($arResult['INVOICE']['PROPERTY_TYPE_PACK_ENUM_ID'] == 246) ? 'checked=""' : '';?> onChange="ChangeTypePack('Документы','Не документы');">
                                Документы
                              </label>
                            </div>
                            <div class="radio">
                              <label>
                                <input name="TYPE_PACK" value="247" type="radio" <?=($arResult['INVOICE']['PROPERTY_TYPE_PACK_ENUM_ID'] == 247) ? 'checked=""' : '';?> onChange="ChangeTypePack('Не документы','Документы');">
                                Не документы
                              </label>
                            </div>
                        </div>
                        
						<div class="form-group <?=$arResult['ERR_FIELDS']['WHO_DELIVERY'];?>">
                        	<label class="control-label">Доставить</label>
                            <div class="radio">
                              <label>
                                <input name="WHO_DELIVERY" value="248" type="radio" <?=($arResult['INVOICE']['PROPERTY_WHO_DELIVERY_ENUM_ID'] == 248) ? 'checked=""' : '';?>>
                                По адресу
                              </label>
                            </div>
                            <div class="radio">
                              <label>
                                <input name="WHO_DELIVERY" value="249" type="radio" <?=($arResult['INVOICE']['PROPERTY_WHO_DELIVERY_ENUM_ID'] == 249) ? 'checked=""' : '';?>>
                                До востребования
                              </label>
                            </div>
                            <div class="radio">
                              <label>
                                <input name="WHO_DELIVERY" value="250" type="radio" <?=($arResult['INVOICE']['PROPERTY_WHO_DELIVERY_ENUM_ID'] == 250) ? 'checked=""' : '';?>>
                                Лично в руки
                              </label>
                            </div>
                        </div>
						<div class="form-group <?=$arResult['ERR_FIELDS']['IN_DATE_DELIVERY'];?>">
                            <label class="control-label">Доставить в дату</label>
                            <div class="row">
                            	<div class="col-md-10">
                                	<div class="input-group">
                                		<input type="text" class="form-control maskdate" placeholder="ДД.ММ.ГГГГ" value="<?=$arResult['INVOICE']['PROPERTY_IN_DATE_DELIVERY_VALUE'];?>" name="IN_DATE_DELIVERY">    
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
                                                    "INPUT_VALUE" => $arResult['INVOICE']['PROPERTY_IN_DATE_DELIVERY_VALUE'],
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
                                    <input type="text" class="form-control masktime" name="IN_TIME_DELIVERY" value="<?=$arResult['INVOICE']['PROPERTY_IN_TIME_DELIVERY_VALUE'];?>" placeholder="ЧЧ:ММ">
                                </div>
                            </div>
                        </div>
                         
                    </div>
					<div class="col-md-6">
                    	<h4>Условия оплаты</h4>
						<div class="form-group <?=$arResult['ERR_FIELDS']['PAYMENT'];?>">
                        	<label class="control-label">Оплата</label>
                            <div class="radio">
                              <label>
                                <input name="PAYMENT" value="255" type="radio" <?=($arResult['INVOICE']['PROPERTY_PAYMENT_ENUM_ID'] == 255) ? 'checked=""' : '';?>>
                                Наличными
                              </label>
                            </div>
                            <div class="radio">
                              <label>
                                <input name="PAYMENT" value="256" type="radio" <?=($arResult['INVOICE']['PROPERTY_PAYMENT_ENUM_ID'] == 256) ? 'checked=""' : '';?>>
                                По счету
                              </label>
                            </div>
                        </div>
                    
						<div class="form-group <?=$arResult['ERR_FIELDS']['TYPE_PAYS'];?>">
                            <label class="control-label">Оплачивает</label>
                            <div class="radio">
                              <label>
                                <input name="TYPE_PAYS" value="251" type="radio" <?=($arResult['INVOICE']['PROPERTY_TYPE_PAYS_ENUM_ID'] == 251) ? 'checked=""' : '';?>>
                                Отправитель
                              </label>
                            </div>
                            <div class="radio">
                              <label>
                                <input name="TYPE_PAYS" value="252" type="radio" <?=($arResult['INVOICE']['PROPERTY_TYPE_PAYS_ENUM_ID'] == 252) ? 'checked=""' : '';?> id="type_pays_252">
                                Получатель
                              </label>
                            </div>
                            <div class="radio<?=($arResult['INVOICE']['PROPERTY_PAYMENT_ENUM_ID'] == 256) ? '' : ' hidden';?>" id="type_pays_253_block">
                              <label>
                                <input name="TYPE_PAYS" value="253" type="radio" <?=($arResult['INVOICE']['PROPERTY_TYPE_PAYS_ENUM_ID'] == 253) ? 'checked=""' : '';?>>
                                Другой
                              </label>
                            </div>
                        </div>
                        <div class="form-group <?=$arResult['ERR_FIELDS']['WHOSE_ORDER'];?> <?=(($arResult['INVOICE']['PROPERTY_PAYMENT_ENUM_ID'] == 256) && (($arResult['INVOICE']['PROPERTY_TYPE_PAYS_ENUM_ID'] == 252) || ($arResult['INVOICE']['PROPERTY_TYPE_PAYS_ENUM_ID'] == 253))) ? '' : ' hidden';?>" id="whose_order_block">
							<div class="row">
                            	<div class="col-md-10">
									<? if ((count($arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT']) > 0) && (is_array($arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT']))) : ?>
									<select class="form-control" name="WHOSE_ORDER">
										<? if (count($arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT']) > 1) : ?>
										<option value="0"></option>
										<? endif;?>
										<? foreach ($arResult['CURRENT_CLIENT_INFO']['PROPERTY_BY_AGENT'] as $k => $v) : ?>
											<option value="<?=$k;?>" <?=($arResult['INVOICE']['PROPERTY_WHOSE_ORDER_VALUE'] == $k) ? 'selected' : '';?>><?=$v;?></option>
										<? endforeach;?>
									</select>
									<? else : ?>
									<input type="text" class="form-control" name="PAYS" value="<?=$arResult['INVOICE']['PROPERTY_PAYS_VALUE'];?>">
									<? endif;?>
                                </div>
                            </div>
						</div>

                        

                        <div class="form-group <?=$arResult['ERR_FIELDS']['FOR_PAYMENT'];?>">
							<div class="row">
                            	<div class="col-md-10">
                        			<label class="control-label">К оплате</label>
                                    <div class="input-group">
                            			<input type="text" class="form-control" name="FOR_PAYMENT" value="<?=$arResult['INVOICE']['PROPERTY_FOR_PAYMENT_VALUE'];?>" placeholder="0,00" aria-describedby="basic-addon-2" id="payment-value">
                                    	<span class="input-group-addon" id="basic-addon-2">руб.</span>
                                    </div>
                            	</div>
                            </div>
                        </div>
                        <div class="form-group <?=$arResult['ERR_FIELDS']['PAYMENT_COD'];?>">
							<div class="row">
                            	<div class="col-md-10">
                        			<label class="control-label">Сумма наложенного платежа</label>
                                    <div class="input-group">
                            			<input type="text" class="form-control" name="PAYMENT_COD" value="<?=$arResult['INVOICE']['PROPERTY_PAYMENT_COD_VALUE'];?>" placeholder="0,00" aria-describedby="basic-addon-3" id="payment-cod-value">
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
                            			<input type="text" class="form-control" name="COST" value="<?=$arResult['INVOICE']['PROPERTY_COST_VALUE'];?>" placeholder="0,00" aria-describedby="basic-addon-1" id="cost-value">
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
            <div class="col-md-9">
                <table class="table table-bordered" id="calculated_values">
                    <thead>
                        <tr>
                            <th width="50%">Описание отправления</th>
                            <th width="10%">Мест</th>
                            <th width="10%">Вес</th>
                            <th colspan="3" width="30%">Габариты</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" class="form-control" name="pack_description[0][name]" value="<?=strlen($_POST['pack_description'][0]['name']) ? $_POST['pack_description'][0]['name'] : $arResult['INVOICE']['PACK_DESCR'][0]['name'];?>" id="pack_description_first"></td>
                            <td>
                                <input type="text" class="form-control" name="pack_description[0][place]" value="<?=strlen($_POST['pack_description'][0]['place']) ? $_POST['pack_description'][0]['place'] : $arResult['INVOICE']['PACK_DESCR'][0]['place'];?>">
                            </td>
                            <td>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="pack_description[0][weight]" value="<?=strlen($_POST['pack_description'][0]['weight']) ? $_POST['pack_description'][0]['weight'] : $arResult['INVOICE']['PACK_DESCR'][0]['weight'];?>">
                                    <div class="input-group-addon">кг</div>
                                </div>
                            </td>
                            <td>
                                <div class="input-group">
                                <input type="text" class="form-control" name="pack_description[0][size][0]" value="<?=strlen($_POST['pack_description'][0]['size'][0]) ? $_POST['pack_description'][0]['size'][0] : $arResult['INVOICE']['PACK_DESCR'][0]['size'][0];?>">
                                <div class="input-group-addon">см</div>
                                </div>
                            </td>
                            <td>
                                <div class="input-group">
                                <input type="text" class="form-control" name="pack_description[0][size][1]" value="<?=strlen($_POST['pack_description'][0]['size'][1]) ? $_POST['pack_description'][0]['size'][1] : $arResult['INVOICE']['PACK_DESCR'][0]['size'][1];?>">
                                <div class="input-group-addon">см</div>
                                </div>
                            </td>
                            <td>
                                <div class="input-group">
                                <input type="text" class="form-control" name="pack_description[0][size][2]" value="<?=strlen($_POST['pack_description'][0]['size'][2]) ? $_POST['pack_description'][0]['size'][2] : $arResult['INVOICE']['PACK_DESCR'][0]['size'][2];?>">
                                <div class="input-group-addon">см</div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="text" class="form-control" name="pack_description[1][name]" value="<?=strlen($_POST['pack_description'][1]['name']) ? $_POST['pack_description'][1]['name'] : $arResult['INVOICE']['PACK_DESCR'][1]['name'];?>"></td>
                            <td><input type="text" class="form-control" name="pack_description[1][place]" value="<?=strlen($_POST['pack_description'][1]['place']) ? $_POST['pack_description'][1]['place'] : $arResult['INVOICE']['PACK_DESCR'][1]['place'];?>"></td>
                            <td><div class="input-group">
                            <input type="text" class="form-control" name="pack_description[1][weight]" value="<?=strlen($_POST['pack_description'][1]['weight']) ? $_POST['pack_description'][1]['weight'] : $arResult['INVOICE']['PACK_DESCR'][1]['weight'];?>">
                            <div class="input-group-addon">кг</div></div></td>
                            <td><div class="input-group">
                            <input type="text" class="form-control" name="pack_description[1][size][0]" value="<?=strlen($_POST['pack_description'][1]['size'][0]) ? $_POST['pack_description'][1]['size'][0] : $arResult['INVOICE']['PACK_DESCR'][1]['size'][0];?>">
                            <div class="input-group-addon">см</div></div></td>
                            <td><div class="input-group">
                            <input type="text" class="form-control" name="pack_description[1][size][1]" value="<?=strlen($_POST['pack_description'][1]['size'][1]) ? $_POST['pack_description'][1]['size'][1] : $arResult['INVOICE']['PACK_DESCR'][1]['size'][1];?>">
                            <div class="input-group-addon">см</div></div></td>
                            <td><div class="input-group">
                            <input type="text" class="form-control" name="pack_description[1][size][2]" value="<?=strlen($_POST['pack_description'][1]['size'][2]) ? $_POST['pack_description'][1]['size'][2] : $arResult['INVOICE']['PACK_DESCR'][1]['size'][2];?>">
                            <div class="input-group-addon">см</div></div></td>
                        </tr>
                        <tr>
                            <td><input type="text" class="form-control" name="pack_description[2][name]" value="<?=strlen($_POST['pack_description'][2]['name']) ? $_POST['pack_description'][2]['name'] : $arResult['INVOICE']['PACK_DESCR'][2]['name'];?>"></td>
                            <td><input type="text" class="form-control" name="pack_description[2][place]" value="<?=strlen($_POST['pack_description'][2]['place']) ? $_POST['pack_description'][2]['place'] : $arResult['INVOICE']['PACK_DESCR'][2]['place'];?>"></td>
                            <td><div class="input-group">
                            <input type="text" class="form-control" name="pack_description[2][weight]" value="<?=strlen($_POST['pack_description'][2]['weight']) ? $_POST['pack_description'][2]['weight'] : $arResult['INVOICE']['PACK_DESCR'][2]['weight'];?>">
                            <div class="input-group-addon">кг</div></div></td>
                            <td><div class="input-group">
                            <input type="text" class="form-control" name="pack_description[2][size][0]" value="<?=strlen($_POST['pack_description'][2]['size'][0]) ? $_POST['pack_description'][2]['size'][0] : $arResult['INVOICE']['PACK_DESCR'][2]['size'][0];?>">
                            <div class="input-group-addon">см</div></div></td>
                            <td><div class="input-group">
                            <input type="text" class="form-control" name="pack_description[2][size][1]" value="<?=strlen($_POST['pack_description'][2]['size'][1]) ? $_POST['pack_description'][2]['size'][1] : $arResult['INVOICE']['PACK_DESCR'][2]['size'][1];?>">
                            <div class="input-group-addon">см</div></div></td>
                            <td><div class="input-group">
                            <input type="text" class="form-control" name="pack_description[2][size][2]" value="<?=strlen($_POST['pack_description'][2]['size'][2]) ? $_POST['pack_description'][2]['size'][2] : $arResult['INVOICE']['PACK_DESCR'][2]['size'][2];?>">
                            <div class="input-group-addon">см</div></div></td>
                        </tr>
                        <tr>
                            <td><input type="text" class="form-control" name="pack_description[3][name]" value="<?=strlen($_POST['pack_description'][3]['name']) ? $_POST['pack_description'][3]['name'] : $arResult['INVOICE']['PACK_DESCR'][3]['name'];?>"></td>
                            <td><input type="text" class="form-control" name="pack_description[3][place]" value="<?=strlen($_POST['pack_description'][3]['place']) ? $_POST['pack_description'][3]['place'] : $arResult['INVOICE']['PACK_DESCR'][3]['place'];?>"></td>
                            <td><div class="input-group">
                            <input type="text" class="form-control" name="pack_description[3][weight]" value="<?=strlen($_POST['pack_description'][3]['weight']) ? $_POST['pack_description'][3]['weight'] : $arResult['INVOICE']['PACK_DESCR'][3]['weight'];?>">
                            <div class="input-group-addon">кг</div></div></td>
                            <td><div class="input-group">
                            <input type="text" class="form-control" name="pack_description[3][size][0]" value="<?=strlen($_POST['pack_description'][3]['size'][0]) ? $_POST['pack_description'][3]['size'][0] : $arResult['INVOICE']['PACK_DESCR'][3]['size'][0];?>">
                            <div class="input-group-addon">см</div></div></td>
                            <td><div class="input-group">
                            <input type="text" class="form-control" name="pack_description[3][size][1]" value="<?=strlen($_POST['pack_description'][3]['size'][1]) ? $_POST['pack_description'][3]['size'][1] : $arResult['INVOICE']['PACK_DESCR'][3]['size'][1];?>">
                            <div class="input-group-addon">см</div></div></td>
                            <td><div class="input-group">
                            <input type="text" class="form-control" name="pack_description[3][size][2]" value="<?=strlen($_POST['pack_description'][3]['size'][2]) ? $_POST['pack_description'][3]['size'][2] : $arResult['INVOICE']['PACK_DESCR'][3]['size'][2];?>">
                            <div class="input-group-addon">см</div></div></td>
                        </tr>
                        <tr>
                            <td><input type="text" class="form-control" name="pack_description[4][name]" value="<?=strlen($_POST['pack_description'][4]['name']) ? $_POST['pack_description'][4]['name'] : $arResult['INVOICE']['PACK_DESCR'][4]['name'];?>"></td>
                            <td><input type="text" class="form-control" name="pack_description[4][place]" value="<?=strlen($_POST['pack_description'][4]['place']) ? $_POST['pack_description'][4]['place'] : $arResult['INVOICE']['PACK_DESCR'][4]['place'];?>"></td>
                            <td><div class="input-group">
                            <input type="text" class="form-control" name="pack_description[4][weight]" value="<?=strlen($_POST['pack_description'][4]['weight']) ? $_POST['pack_description'][4]['weight'] : $arResult['INVOICE']['PACK_DESCR'][4]['weight'];?>">
                            <div class="input-group-addon">кг</div></div></td>
                            <td><div class="input-group">
                            <input type="text" class="form-control" name="pack_description[4][size][0]" value="<?=strlen($_POST['pack_description'][4]['size'][0]) ? $_POST['pack_description'][4]['size'][0] : $arResult['INVOICE']['PACK_DESCR'][4]['size'][0];?>">
                            <div class="input-group-addon">см</div></div></td>
                            <td><div class="input-group">
                            <input type="text" class="form-control" name="pack_description[4][size][1]" value="<?=strlen($_POST['pack_description'][4]['size'][1]) ? $_POST['pack_description'][4]['size'][1] : $arResult['INVOICE']['PACK_DESCR'][4]['size'][1];?>">
                            <div class="input-group-addon">см</div></div></td>
                            <td><div class="input-group">
                            <input type="text" class="form-control" name="pack_description[4][size][2]" value="<?=strlen($_POST['pack_description'][4]['size'][2]) ? $_POST['pack_description'][4]['size'][2] : $arResult['INVOICE']['PACK_DESCR'][4]['size'][2];?>">
                            <div class="input-group-addon">см</div></div></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th></th>
                            <th>
                            	<div class="form-group <?=$arResult['ERR_FIELDS']['PLACES'];?>">
                            		<input type="text" class="form-control" disabled id="total_place">
                            	</div>
                            </th>
                            <th>
                            	<div class="form-group <?=$arResult['ERR_FIELDS']['WEIGHT'];?>">
                            		<div class="input-group"><input type="text" class="form-control" disabled id="total_weight"><div class="input-group-addon">кг</div></div>
                            	</div>
                            </th>
                            <th colspan="3">
                            	<div class="form-group <?=$arResult['ERR_FIELDS']['SIZE'];?>">
                            		<div class="input-group"><input type="text" class="form-control" disabled id="total_gabweight"><div class="input-group-addon">кг</div></div>
                            	</div>
                            </th>

                        </tr>
                    </tfoot>
                </table>
            </div>
			<div class="col-md-3">
            	<div class="form-group">
                	<label class="control-label">Специальные инструкции</label>
                    <textarea name="INSTRUCTIONS" class="form-control" style="height:150px; resize:vertical;"><?=$arResult['INVOICE']['PROPERTY_INSTRUCTIONS_VALUE']['TEXT'];?></textarea>
                </div>
            </div>
        </div>
        <?
        if (isset($_POST['goods']))
        {
            $count_goods = (intval($_POST['count_goods']) > 1) ? intval($_POST['count_goods']) : 1;
        }
        else
        {
            $count_goods = (count($arResult['INVOICE']['PACK_GOODS']) > 1) ? count($arResult['INVOICE']['PACK_GOODS']) : 1;
        }
        ?>
        <div class="row">
            <div class="col-md-12 column"> 
                <input type="hidden" name="count_goods" value="<?=$count_goods;?>" id="count_goods">
                <h4>Товары</h4>
                <table class="table table-bordered" style="margin-bottom:5px;" id="tab_logic">
                    <thead>
                        <tr>
                            <th width="37%">Наименование товара</th>
                            <th>Количество</th>
                            <th>Цена за 1 шт., включая НДС</th>
                            <th>Сумма, включая НДС</th>
                            <th>Сумма НДС</th>
                            <th>Ставка НДС</th>
                        </tr>
                    </thead>
                    <tbody>
                        <? if (!isset($_POST['goods'])) : ?>
                            <? foreach ($arResult['INVOICE']['PACK_GOODS'] as $k => $v) : ?>
                                <tr id="addr<?=$k;?>">
                                    <td><input type="text" name="goods[<?=$k;?>][name]" value="<?=$v['GoodsName'];?>" class="form-control"></td>
                                    <td>
                                        <div class="input-group">
                                            <input type="text" name="goods[<?=$k;?>][amount]" value="<?=$v['Amount'];?>" class="form-control" aria-describedby="good-addon-0-1" id="input-goods-amount-<?=$k;?>" onChange="CalcGoods('<?=$k;?>');">
                                            <span class="input-group-addon" id="good-addon-0-1">шт.</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <input type="text" name="goods[<?=$k;?>][price]" value="<?=$v['Price'];?>" class="form-control" aria-describedby="good-addon-0-2" id="input-goods-price-<?=$k;?>" onChange="CalcGoods('<?=$k;?>');">
                                            <span class="input-group-addon" id="good-addon-0-2">руб.</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <input type="text" name="goods[<?=$k;?>][sum]" value="<?=$v['Sum'];?>" class="form-control" aria-describedby="good-addon-0-3" id="input-goods-sum-<?=$k;?>">
                                            <span class="input-group-addon" id="good-addon-0-3">руб.</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <input type="text" name="goods[<?=$k;?>][sumnds]" value="<?=$v['SumNDS'];?>" class="form-control" aria-describedby="good-addon-0-4" id="input-goods-sumnds-<?=$k;?>">
                                            <span class="input-group-addon" id="good-addon-0-4">руб.</span>
                                        </div>
                                    </td>
                                    <td>
                                        <select size="1" name="goods[<?=$k;?>][persentnds]" class="form-control" id="input-goods-persentnds-<?=$k;?>" onChange="CalcGoods('<?=$k;?>');">
                                            <option value="18"<?=((intval($v['PersentNDS']) == 18) && isset($v['PersentNDS'])) ? ' selected' : '';?>>18%</option>
                                            <option value="0"<?=((intval($v['PersentNDS']) == 0) && isset($v['PersentNDS'])) ? ' selected' : '';?>>0%</option>
                                            <option value="10"<?=((intval($v['PersentNDS']) == 10) && isset($v['PersentNDS'])) ? ' selected' : '';?>>10%</option>
                                        </select>
                                    </td>
                                </tr>
                            <? endforeach;?>
                        <? else : ?>
                            <? foreach ($_POST['goods'] as $k => $v) : ?>
                                <tr id="addr<?=$k;?>">
                                    <td><input type="text" name="goods[<?=$k;?>][name]" value="<?=$_POST['goods'][$k]['name'];?>" class="form-control"></td>
                                    <td>
                                        <div class="input-group">
                                            <input type="text" name="goods[<?=$k;?>][amount]" value="<?=$_POST['goods'][$k]['amount'];?>" class="form-control" aria-describedby="good-addon-0-1" id="input-goods-amount-<?=$k;?>" onChange="CalcGoods('<?=$k;?>');">
                                            <span class="input-group-addon" id="good-addon-0-1">шт.</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <input type="text" name="goods[<?=$k;?>][price]" value="<?=$_POST['goods'][$k]['price'];?>" class="form-control" aria-describedby="good-addon-0-2" id="input-goods-price-<?=$k;?>" onChange="CalcGoods('<?=$k;?>');">
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
                                        <select size="1" name="goods[<?=$k;?>][persentnds]" class="form-control" id="input-goods-persentnds-<?=$k;?>" onChange="CalcGoods('<?=$k;?>');">
                                            <option value="18"<?=((intval($_POST['goods'][$k]['persentnds']) == 18) && isset($_POST['goods'][$k]['persentnds'])) ? ' selected' : '';?>>18%</option>
                                            <option value="0"<?=((intval($_POST['goods'][$k]['persentnds']) == 0) && isset($_POST['goods'][$k]['persentnds'])) ? ' selected' : '';?>>0%</option>
                                            <option value="10"<?=((intval($_POST['goods'][$k]['persentnds']) == 10) && isset($_POST['goods'][$k]['persentnds'])) ? ' selected' : '';?>>10%</option>
                                        </select>
                                    </td>
                                </tr>
                            <? endforeach;?>
                        <? endif; ?>
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
            <div class="col-sm-12">
                <div class="btn-group btn-group-lg">
                    <button type="submit" name="save" class="btn btn-primary">Сохранить <span class="badge">CTRL+Enter</span></button>
                    <input type="submit" name="save-print" value="Сохранить и распечатать" class="btn btn-default">
                </div>
            </div>
        </div>
	</form>
	<?
	}
	else
	{
		?>
        <div class="alert alert-dismissable alert-danger">Накладная не найдена. <a href="<?=$arParams['LINK'];?>?mode=list">Вернуться к списку накладных</a>.</div>
        <?
	}
}
?>
<br>