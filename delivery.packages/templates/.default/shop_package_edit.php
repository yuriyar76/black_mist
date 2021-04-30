<script>
	$(document).ready(function()
	{
		CalculateCostOfDelivery();
		Disabled();
		Disabled_2();
		
        $("#add_row").click(function(){
			var i = parseInt($('#count_goods').val());
            $('#addr'+i).html('<td><input type="text" name="goods['+i+'][name]" value=""></td>'+
                            '<td><input type="text" name="goods['+i+'][amount]" value="" onChange="CalcGoods(\''+i+'\');" id="input-goods-amount-'+i+'"></td>'+
                            '<td><input type="text" name="goods['+i+'][price]" value="" onChange="CalcGoods(\''+i+'\');" id="input-goods-price-'+i+'"></td>'+
                            '<td><input type="text" name="goods['+i+'][sum]" value="" id="input-goods-sum-'+i+'"></td>'+
                            '<td><input type="text" name="goods['+i+'][sumnds]" value="" id="input-goods-sumnds-'+i+'"></td>'+
                            '<td>'+
                                '<select size="1" name="goods['+i+'][persentnds]" onChange="CalcGoods(\''+i+'\');" id="input-goods-persentnds-'+i+'">'+
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
			var i = parseInt($('#count_goods').val());
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
	
	function Disabled()
	{
		var type = $('#conditions').val();
		if (type == '38')
		{
			$('.blocy').css('display','none');
		}
		if (type == '37')
		{
			$('.blocy').css('display','table-row');
		}
	}
	
	function Disabled_2()
	{
		if ($("#take_provider").is(":checked")) {
			$('.blocy_2').css('display','table-row');
		/*	$('.zabors_select').each(function() {
				var m = $(this).data('item');
				getInfoOfZabor(m);
			}); */
		}
		else
		{
			$('.blocy_2').css('display','none');
		}
	}
	
	function getInfoOfZabor(k)
	{
		var zabor = $("#zabor_"+k).val();
		$.get('/search_city.php', {request: 'true', request_id: zabor}, function(data)
		{
			$('#inser_zabors_'+k).html(data);
		})
	}
	
	function AddNewRow(shop)
	{
		var coun_rows_prev = parseInt($('#coun_rows').val());
		coun_rows = coun_rows_prev+1;
		var content = '<tr class="blocy_2">' +
						'<td><strong>Забор у поставщика</strong></td>' + 
						'<td>' +
						'<select name="zabor[]" size="1" onChange="getInfoOfZabor('+coun_rows+');" id="zabor_'+coun_rows+'">' +
						'</select>' +
						'</td>'+
            '</tr>'+
            '<tr class="blocy_2" id="insert_'+coun_rows+'">'+
            '<td colspan="2">'+
           ' <div id="inser_zabors_'+coun_rows+'">'+
            '</div>'+
            '</td>'+
            '</tr>';
		$.get('/search_city.php', {requests: 'true', shop_id: shop}, function(data) {
			data = $.parseJSON(data);
			var m = '';
			$.each(data,function(key,val){
				m = m + '<option value="'+val.id+'">'+val.name+'</option>';
			});
			$('#zabor_'+coun_rows).html(m);
			
		})		
		$(content).insertAfter('#insert_'+coun_rows_prev);
		$('#coun_rows').val(coun_rows);
	}
</script>

<div class="new_menu">
    <ul>
        <li class="active"><a href="javascript:void(0);"><?=$arResult["TITLE"];?></a></li>
        <?
        foreach ($arResult["MENU"] as $k => $v) {
            ?>
            <li><a href="index.php?mode=<?=$k?>"><?=$v;?></a></li>
            <?
        }
        ?>
        <li class="nobg">
        	<a href="print.php?ids=<?=$arResult['PACK']['ID'];?>" title="распечатать этикетку" target="_blank">
            	<img src="/bitrix/components/black_mist/delivery.packages/templates/.default/images/print_icon.png" width="20" height="20">
            </a>
        </li>
    </ul>
</div>
<?
if (count($arResult["ERRORS"]) > 0) 
	echo '
		<p class="red">'.implode('</br>',$arResult["ERRORS"]).'</p>';

if (count($arResult["MESSAGE"]) > 0) 
	echo '
		<p class="green">'.implode('</br>',$arResult["MESSAGE"]).'</p>';
else {
	if (count($arResult["WARNINGS"]) > 0) 
	echo '
		<p class="orange">'.implode('</br>',$arResult["WARNINGS"]).'</p>';
}

if ($arResult['PACK']) {
	?>
    
    <p id="inser"></p>
    
    <form action="" method="post">
        <input type="hidden" name="rand" value="<?=rand(100000,999999);?>" />
        <input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>" />
        <input type="hidden" name="pack_id" value="<?=$arResult['PACK']["ID"];?>">
        <input type="hidden" name="id_in" value="<?=$arResult['PACK']['PROPERTY_N_ZAKAZ_IN_VALUE'];?>">
        <input type="hidden" name="coun_rows" value="<?=count($arResult['PACK']['REQS'])-1;?>" id="coun_rows" />
        <div class="table_group">
            <table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="borders date_icon_in">
                <tbody>
                    <tr>
                        <td width="300"><strong>Дата создания</strong></td>
                        <td><?=$arResult['PACK']['DATE_CREATE'];?></td>
                    </tr>
                    <tr>
                        <td><strong>Внутренний номер заказа</strong><span class="red">*</span>&nbsp;<sup><a href="javascript:void(0);" class="help" title="<?=GetMessage("HELP_2");?>">?</a></sup></td>
                        <td><input type="text" name="PROPERTY_N_ZAKAZ_VALUE" value="<?=$arResult['PACK']['PROPERTY_N_ZAKAZ_VALUE'];?>"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="table_group">
			<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="borders date_icon_in">
                <tbody>
                    <tr>
                        <td width="300"><strong>Получатель</strong><span class="red">*</span></td>
                        <td><input type="text" name="PROPERTY_RECIPIENT_VALUE" value="<?=$arResult['PACK']['PROPERTY_RECIPIENT_VALUE'];?>"></td>
                    </tr>
                    <tr>
                        <td><strong>Номер телефона</strong><span class="red">*</span></td>
                        <td><input type="text" name="PROPERTY_PHONE_VALUE" value="<?=$arResult['PACK']['PROPERTY_PHONE_VALUE'];?>"></td>
                    </tr>
					<tr>
                        <td><strong>Город назначения</strong><span class="red">*</span></td>
                        <td>
                            <input type="text" name="PROPERTY_CITY" value="<? echo strlen($arResult['PACK']['PROPERTY_CITY']) ? $arResult['PACK']['PROPERTY_CITY'] : $arResult["SHOP_DEFAULT"]['city']; ?>" id="city_price_out">
                            <input type="hidden" name="price" value="<?=$arResult["PRICE"];?>" id="price">
                            <input type="hidden" name="price_2" value="<?=$arResult["PRICE_2"];?>" id="price_2">
                            <input type="hidden" name="price_3" value="<?=$arResult["PRICE_3"];?>" id="price_3">
                            <input type="hidden" name="city_id" value="<?=strlen($arResult['PACK']['PROPERTY_CITY_VALUE']) ? $arResult['PACK']['PROPERTY_CITY_VALUE'] : $arResult["SHOP_DEFAULT"]['city_id']; ?>" id="city_id">
                            <input type="hidden" name="persent_1" value="" id="persent_1">
                            <input type="hidden" name="persent_2" value="" id="persent_2">
                        </td>
                    </tr>
					<tr>
                        <td><strong>Условия доставки</strong><span class="red">*</span></td>
                        <td><select name="PROPERTY_CONDITIONS_ENUM_ID" size="1" onchange="Disabled(); CalculateCostOfDelivery();" id="conditions">
                                <option value="37" <? echo ($arResult['PACK']['PROPERTY_CONDITIONS_ENUM_ID'] == 37) ? ' selected' : ''; ?>>По адресу</option>
                                <option value="38" <? echo ($arResult['PACK']['PROPERTY_CONDITIONS_ENUM_ID'] == 38) ? ' selected' : ''; ?>>Самовывоз</option>
                            </select>
                        </td>
                    </tr>
					<tr class="blocy" style="display:none;">
                        <td><strong>Адрес доставки</strong><span class="red">*</span></td>
                        <td><textarea name="PROPERTY_ADRESS_VALUE"><?=$arResult['PACK']['PROPERTY_ADRESS_VALUE'];?></textarea></td>
                    </tr>
                    <tr class="blocy" style="display:none;">
                        <td><strong>Когда доставить</strong></td>
                        <td>
                            <? $APPLICATION->IncludeComponent("bitrix:main.calendar", ".default", array(
                                "SHOW_INPUT" => "Y",
                                "FORM_NAME" => "curform",
                                "INPUT_NAME" => "date_deliv",
                                "INPUT_NAME_FINISH" => false,
                                "INPUT_VALUE" => $arResult['PACK']["DELIV_DATE"],
                                "INPUT_VALUE_FINISH" => false,
                                "SHOW_TIME" => "N",
                                "HIDE_TIMEBAR" => "Y",
                                "INPUT_ADDITIONAL_ATTR" => 'placeholder="ДД.ММ.ГГГГ" class="small_inp date"'
                                ),false);
                            ?>
							<select name="TIME_PERIOD" size="1" class="short">
                            <?
							foreach ($arResult['time_periods'] as $k => $v)
							{
								?>
                                <option value="<?=$k;?>"<?=($arResult['PACK']['PROPERTY_TIME_PERIOD_ENUM_ID'] == $k) ? ' selected' : ' ';?>><?=$v;?></option>
                                <?
							}
							?>
                            </select>
                        </td>
                    </tr>
					<tr>
                        <td>
                            <strong>Срочный заказ</strong>&nbsp;<sup><a href="javascript:void(0);" class="help" title="<?=GetMessage("HELP_3");?>">?</a></sup>
                        </td>
                        <td>
                            <input type="checkbox" name="urgent" value="2" onChange="CalculateCostOfDelivery();" id="urgent" <? echo ($arResult['PACK']['PROPERTY_URGENCY_ORDER_ENUM_ID'] == 172) ? ' checked' : '';?>>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Доставка юр. лицу</strong>&nbsp;<sup><a href="javascript:void(0);" class="help" title="<?=GetMessage("HELP_4");?>">?</a></sup></td>
                        <td><input type="checkbox" name="to_legal" value="1" <? echo ($arResult['PACK']['PROPERTY_DELIVERY_LEGAL_VALUE'] ==  1) ? ' checked' : ''; ?>></td>
                    </tr>
					<tr>
                        <td><strong>Комментарий к заказу</strong></td>
                        <td><textarea name="PROPERTY_PREFERRED_TIME_VALUE"><?=$arResult['PACK']['PROPERTY_PREFERRED_TIME_VALUE']["TEXT"];?></textarea></td>
                    </tr>
                </tbody>
			</table>
        </div>
        <div class="table_group">
			<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="borders date_icon_in">
				<tbody>
                    <tr>
                        <td width="300"><strong>Вес</strong><span class="red">*</span></td>
                        <td><input type="text" name="PROPERTY_WEIGHT_VALUE" value="<?=$arResult['PACK']['PROPERTY_WEIGHT_VALUE'];?>" id="weight" onChange="CalculateCostOfDelivery();"> кг</td>
                    </tr>
                    <tr>
                        <td><strong>Габариты</strong></td>
                        <td>
                            <input type="text" name="PROPERTY_SIZE_1_VALUE" value="<?=$arResult['PACK']['PROPERTY_SIZE_1_VALUE'];?>" placeholder="длина" class="small_inp" id="size_1" onChange="CalculateCostOfDelivery();">  
                            <input type="text" name="PROPERTY_SIZE_2_VALUE" value="<?=$arResult['PACK']['PROPERTY_SIZE_2_VALUE'];?>" placeholder="ширина" class="small_inp" id="size_2" onChange="CalculateCostOfDelivery();">  
                            <input type="text" name="PROPERTY_SIZE_3_VALUE" value="<?=$arResult['PACK']['PROPERTY_SIZE_3_VALUE'];?>" placeholder="высота" class="small_inp" id="size_3" onChange="CalculateCostOfDelivery();"> см
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Количество мест</strong><span class="red">*</span></td>
                        <td><input type="text" name="PROPERTY_PLACES_VALUE" value="<?=$arResult['PACK']['PROPERTY_PLACES_VALUE'];?>"></td>
                    </tr>
				</tbody>
			</table>
        </div>
        <div class="table_group">
			<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="borders date_icon_in">
				<tbody>
                    <tr>
                        <td width="300"><strong>Стоимость заказа</strong><span class="red">*</span>&nbsp;<sup><a href="javascript:void(0);" class="help" title="<?=GetMessage("HELP_5");?>">?</a></sup></td>
                        <td>
                        	<input type="text" name="PROPERTY_COST_GOODS_VALUE" 
                            	value="<?=$arResult['PACK']['PROPERTY_COST_GOODS_VALUE'];?>" id="cost_goods_hid" onChange="CalDelivery(); ReCalcRate();"> руб.
						</td>
                    </tr>
                    <tr>
                        <td><strong>За доставку</strong></td>
                        <td><input type="text" name="PROPERTY_COST_3_VALUE" value="<?=$arResult['PACK']['PROPERTY_COST_3_VALUE'];?>" id="cost_3" onChange="CalDelivery(); ReCalcRate();"> руб.</td>
                    </tr>
                    <tr>
                        <td><strong>Сумма к оплате</strong><span class="red">*</span></td>
                        <td><input type="text" name="PROPERTY_COST_2_VALUE" value="<?=$arResult['PACK']['PROPERTY_COST_2_VALUE'];?>" onKeyUp="ReCalcRate();" onChange="ReCalcRate();" id="cost_2"> руб.</td>
                    </tr>
                    <!--
                    <tr>
                        <td><strong>Страховая стоимость заказа</strong></td>
                        <td><input type="text" name="PROPERTY_COST_1_VALUE" value="<?=$arResult['PACK']['PROPERTY_COST_1_VALUE'];?>" id="cost_1" onKeyUp="ReCalcRate();" onChange="ReCalcRate();"></td>
                    </tr>
                    -->
                    <tr>
                        <td><strong>Кассовое обслуживание?</strong><span class="red">*</span></td>
                        <td><select name="PROPERTY_CASH_VALUE" size="1" id="cash" onChange="ReCalcRate();">
                                <option value="124"<? echo ($arResult['PACK']['PROPERTY_CASH_ENUM_ID'] == 124) ? ' selected' : ''; ?>>да</option>
                                <option value="125"<? echo ($arResult['PACK']['PROPERTY_CASH_ENUM_ID'] == 125) ? ' selected' : ''; ?>>нет</option>
                            </select></td>
                    </tr>
					<tr>
                    	<td><strong>Брать c получателя оплату за доставку при отказе</strong></td>
                    	<td>
                        	<input type="checkbox" name="refusal" value="1" <? echo ($arResult['PACK']['PROPERTY_PAY_FOR_REFUSAL_VALUE'] == 1) ? ' checked' : ''; ?>>
                        </td>
                    </tr>
				</tbody>
			</table>
		</div>
        <div class="table_group">
        	<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="borders date_icon_in">
            	<tbody>
                    <tr>
						<td width="300"><strong>Сформировать из забора у поставщика</strong></td>
						<td>
                        	<input type="checkbox" name="take_provider" value="1" <? echo ($arResult['PACK']["PROPERTY_TAKE_PROVIDER_ENUM_ID"] == 174) ? ' checked' : ''; ?> 
                            	id="take_provider" onChange="Disabled_2(); CalculateCostOfDelivery();">
						</td>
					</tr>
						<?
						$index = 0;
			foreach ($arResult['PACK']['REQS'] as $j => $z)
			{
				?>
                <tr class="blocy_2" style="display:none;">
					<td><strong>Забор у поставщика</strong>
                    </td>
                    <td>
                    	<select name="zabor[<?=$j;?>]" size="1" onChange="getInfoOfZabor('<?=$j;?>');" id="zabor_<?=$j;?>" class="zabors_select" data-item="<?=$j;?>">
                        <?
                        foreach ($arResult["REQUESTS"] as $k => $v)
						{
							?>
                            <option value="<?=$k;?>" <? echo ($z == $k) ? ' selected' : '';?>><?=$v;?></option>
                            <?
						}
						?>
                        </select>
                        <?
						if ($index == 0)
						{
							?>
							<a href="javascript:void(0);" onClick="AddNewRow('<?=$arResult["SHOP_ID"];?>');">добавить забор</a>
							<?
						}
						?>
					</td>
                </tr>
				<tr class="blocy_2" style="display:none;" id="insert_<?=$j;?>">
					<td colspan="2">
						<div id="inser_zabors_<?=$j;?>">
                        <?
						if (count($arResult['PACK']["REQV_INFO"][$z]['orders']) > 0)
						{
							?>
                            <table cellpadding="5" cellspacing="0" border="1" bordercolor="#ccc" class="rows" width="590"><thead>
								<tr>
                                    <td width="20"></td>
                                    <td>Наименование</td>
                                    <td>Артикул</td>
                                    <td>Вес</td>
                                    <td>Количество</td>
								</tr>
							</thead><tbody>
                            <?
							foreach ($arResult['PACK']["REQV_INFO"][$z]['orders'] as $r)
							{
								?>
                                <tr>
									<td>
                                        <input type="checkbox" name="request_el[]" value="<?=$r["ID"];?>" 
                                        <? if (in_array($r["ID"],$arResult['PACK']["ZABORS_IDS"])) echo ' checked'; ?>
                                        />
                                    </td>
                                    <td><?=$r["NAME"];?></td>
                                    <td><?=$r["PROPERTY_431_VALUE"];?></td>
                                    <td><?=$r["PROPERTY_430_VALUE"];?></td>
                                    <td><?=$r["PROPERTY_432_VALUE"];?></td>
                                </tr>
                                <?
							}
							?>
                            </tbody></table>
                            <?
						}
						?>
						</div>
					</td>
				</tr>
                <?
				$index++;
			}
			?>
                </tbody>
            </table>
        </div>
        <div class="table_group">
        	<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="borders date_icon_in">
            	<tbody>
					<tr>
                        <td width="300"><strong>Агентское вознаграждение</strong &nbsp;><sup><a href="#" class="help" title="<?=GetMessage("HELP_1");?>">?</a></sup></td>
                        <td>
							<?
							foreach ($arResult["RATE"] as $k=> $v)
							{
								?>
								<input type="hidden" name="rate[<?=$k;?>]" value="<?=$v;?>" id="rate_<?=$k;?>">
								<?
							}
							?>
                            <input type="hidden" name="CONDITIONS_IM" value="<?=$arResult['CONDITIONS_IM'];?>" id="conditions_im">
							<span id="rate_value_new">0</span>% - 
							<input type="hidden" name="rate" value="0" id="rate">
							<input type="hidden" name="PROPERTY_RATE_VALUE" 
                            	value="<? echo  (isset($arResult['PACK']['PROPERTY_RATE_VALUE'])) ? $arResult['PACK']['PROPERTY_RATE_VALUE'] : 0; ?>" id="rate_value">
							<span id="rate_value_span"><? echo  (isset($arResult['PACK']['PROPERTY_RATE_VALUE'])) ? $arResult['PACK']['PROPERTY_RATE_VALUE'] : 0; ?></span> руб.
						</td>
					</tr>
					<tr>
                        <td><strong>Стоимость доставки</strong></td>
                        <td><?=CurrencyFormat($arResult['PACK']['PROPERTY_SUMM_SHOP_VALUE'],"RUU");?></td>
					</tr>
					<?
					if ($arResult['PACK']["PROPERTY_SUMM_SHOP_ZABOR_VALUE"] > 0)
					{
						?>
						 <tr>
                         	<td><strong>В том числе за забор</strong></td>
                            <td><?=CurrencyFormat($arResult['PACK']["PROPERTY_SUMM_SHOP_ZABOR_VALUE"],"RUU");?></td>
						</tr>
						<?
					}
					if ($arResult['PACK']["PROPERTY_CALL_COURIER_VALUE"])
					{
						?>
                        <tr>
                        	<td><strong>Заявка на вызов курьера</strong></td>
                            <td><a href="/suppliers/index.php?mode=call_courier&id=<?=$arResult['PACK']["PROPERTY_CALL_COURIER_VALUE"];?>" target="_blank"><?=$arResult['PACK']["CALL_COURIER"];?></a></td>
                        </tr>
                        <?
					}
					?>
				</tbody>
            </table>
        </div>
        <?
        if (isset($_POST['goods']))
        {
            $count_goods = (intval($_POST['count_goods']) > 1) ? intval($_POST['count_goods']) : 1;
        }
        else
        {
            $count_goods = (count($arResult['PACK']['PACK_GOODS']) > 1) ? count($arResult['PACK']['PACK_GOODS']) : 1;
        }
        ?>
        <input type="hidden" name="count_goods" value="<?=$count_goods;?>" id="count_goods">
        <div class="table_group">
        	<table width="100%" cellpadding="3" cellspacing="0" border="0" class="tbl_goods">
        		<tbody>
                	<tr>
						<td width="300" valign="top"><strong>Товары</strong></td>
						<td>
                        	<table cellpadding="0" cellspacing="1" border="0" class="" id="tab_logic">
								<thead>
									<tr>
										<th>Наименование товара</th>
										<th>Количество, шт.</th>
										<th>Цена за 1 шт., включая НДС, руб.</th>
										<th>Сумма, включая НДС, руб.</th>
										<th>Сумма НДС, руб.</th>
										<th>Ставка НДС</th>
									</tr>
								</thead>
                       			<tbody>
								<? if (!isset($_POST['goods'])) : ?>
									<? if ((is_array($arResult['PACK']['PACK_GOODS'])) && (count($arResult['PACK']['PACK_GOODS']) > 0)) : ?>
									<? foreach ($arResult['PACK']['PACK_GOODS'] as $k => $v) : ?>
										<tr id="addr<?=$k;?>">
											<td><input type="text" name="goods[<?=$k;?>][name]" value="<?=$v['GoodsName'];?>"></td>
											<td>
												<input type="text" name="goods[<?=$k;?>][amount]" value="<?=$v['Amount'];?>" onChange="CalcGoods('<?=$k;?>');" id="input-goods-amount-<?=$k;?>">
											</td>
											<td>
												<input type="text" name="goods[<?=$k;?>][price]" value="<?=$v['Price'];?>" onChange="CalcGoods('<?=$k;?>');" id="input-goods-price-<?=$k;?>">
											</td>
											<td>
												<input type="text" name="goods[<?=$k;?>][sum]" value="<?=$v['Sum'];?>" id="input-goods-sum-<?=$k;?>">
											</td>
											<td>
												<input type="text" name="goods[<?=$k;?>][sumnds]" value="<?=$v['SumNDS'];?>" id="input-goods-sumnds-<?=$k;?>">
											</td>
											<td>
												<select size="1" name="goods[<?=$k;?>][persentnds]" onChange="CalcGoods('<?=$k;?>');" id="input-goods-persentnds-<?=$k;?>">
													<option value="18"<?=((intval($v[$k]['PersentNDS']) == 18) && isset($v['PersentNDS'])) ? ' selected' : '';?>>18%</option>
													<option value="0"<?=((intval($v['PersentNDS']) == 0) && isset($v['PersentNDS'])) ? ' selected' : '';?>>0%</option>
													<option value="10"<?=((intval($v['PersentNDS']) == 10) && isset($v['PersentNDS'])) ? ' selected' : '';?>>10%</option>
												</select>
											</td>
										</tr>
                      				<? endforeach;?>
                      				<? else : ?>
                       				<tr id="addr0">
                       					<td>
                       						<input type="text" name="goods[0][name]" value="<?=$_POST['goods'][0]['name'];?>">
                       					</td>
                       					<td>
                       						<input type="text" name="goods[0][amount]" value="<?=$_POST['goods'][0]['amount'];?>" onChange="CalcGoods('0');" id="input-goods-amount-0">
                       					</td>
                       					<td>
                       						<input type="text" name="goods[0][price]" value="<?=$_POST['goods'][0]['price'];?>" onChange="CalcGoods('0');" id="input-goods-price-0">
                       					</td>
                       					<td>
                       						<input type="text" name="goods[0][sum]" value="<?=$_POST['goods'][0]['sum'];?>" id="input-goods-sum-0">
                       					</td>
                       					<td>
                       						<input type="text" name="goods[0][sumnds]" value="<?=$_POST['goods'][0]['sumnds'];?>" id="input-goods-sumnds-0">
                       					</td>
                       					<td>
                       						<select size="1" name="goods[0][persentnds]" onChange="CalcGoods('0');" id="input-goods-persentnds-0">
												<option value="18"<?=((intval($_POST['goods'][0]['persentnds']) == 18) && isset($_POST['goods'][0]['persentnds'])) ? ' selected' : '';?>>18%</option>
												<option value="0"<?=((intval($_POST['goods'][0]['persentnds']) == 0) && isset($_POST['goods'][0]['persentnds'])) ? ' selected' : '';?>>0%</option>
												<option value="10"<?=((intval($_POST['goods'][0]['persentnds']) == 10) && isset($_POST['goods'][0]['persentnds'])) ? ' selected' : '';?>>10%</option>
											</select>
                       					</td>
                       				</tr>
                      				<?endif;?>
                      			<?else:?>
									<? foreach ($_POST['goods'] as $k => $v) : ?>
										<tr id="addr<?=$k;?>">
											<td><input type="text" name="goods[<?=$k;?>][name]" value="<?=$_POST['goods'][$k]['name'];?>"></td>
											<td>
												<input type="text" name="goods[<?=$k;?>][amount]" value="<?=$_POST['goods'][$k]['amount'];?>" onChange="CalcGoods('<?=$k;?>');" id="input-goods-amount-<?=$k;?>">
											</td>
											<td>
												<input type="text" name="goods[<?=$k;?>][price]" value="<?=$_POST['goods'][$k]['price'];?>" onChange="CalcGoods('<?=$k;?>');" id="input-goods-price-<?=$k;?>">
											</td>
											<td>
												<input type="text" name="goods[<?=$k;?>][sum]" value="<?=$_POST['goods'][$k]['sum'];?>" id="input-goods-sum-<?=$k;?>">
											</td>
											<td>
												<input type="text" name="goods[<?=$k;?>][sumnds]" value="<?=$_POST['goods'][$k]['sumnds'];?>" id="input-goods-sumnds-<?=$k;?>">
											</td>
											<td>
												<select size="1" name="goods[<?=$k;?>][persentnds]" onChange="CalcGoods('<?=$k;?>');" id="input-goods-persentnds-<?=$k;?>">
													<option value="18"<?=((intval($_POST['goods'][$k]['persentnds']) == 18) && isset($_POST['goods'][$k]['persentnds'])) ? ' selected' : '';?>>18%</option>
													<option value="0"<?=((intval($_POST['goods'][$k]['persentnds']) == 0) && isset($_POST['goods'][$k]['persentnds'])) ? ' selected' : '';?>>0%</option>
													<option value="10"<?=((intval($_POST['goods'][$k]['persentnds']) == 10) && isset($_POST['goods'][$k]['persentnds'])) ? ' selected' : '';?>>10%</option>
												</select>
											</td>
										</tr>
									<? endforeach;?>
                      			<?endif;?>
                       				<tr id="addr<?=$count_goods;?>"></tr>
                       			</tbody>
                        	</table>
                        	<table cellpadding="0" cellspacing="0" border="0" width="100%">
                        		<tbody>
                        			<tr>
                        				<td width="50%"><a href="javascript:void(0);" id="add_row" class="">Добавить товар</a></td>
                        				<td width="50%" align="right"><a href="javascript:void(0);" id="delete_row" class="">Удалить последний товар</a></td>
                        			</tr>
                        		</tbody>
                        	</table>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
    	<input type="submit" value="Сохранить" name="save">
    </form>
    <div class="status_block"><?=$arResult['PACK']['PROPERTY_STATE_VALUE'];?></div>
	<?	
	
	if (count($arResult['PACK']['SHORT_HISTORY']) > 0) {
		?>
        <h4>История по заказу</h4>
        <table cellpadding="3" cellspacing="0" border="1" bordercolor="#ccc" class="rows"><thead>
        <tr>
        	<td>Дата</td>
            <td>Статус</td>
            <td>Расшифровка</td>
            <td>Кем изменен</td>
        </tr>
        </thead><tbody>
		<?
        foreach ($arResult['PACK']['SHORT_HISTORY'] as $h) {
			?>
            <tr>
            	<td><?=$h['DATE_CREATE'];?></td>
                <td><?=$h['NAME'];?></td>
                <td><?=$h['DETAIL_TEXT'];?></td>
                <td><?=$h['WHO']['LAST_NAME'];?> <?=$h['WHO']['NAME'];?> [<?=$h['MODIFIED_BY'];?>]</td>
            </tr>
            <?
        }
		?>
        </tbody></table>
	<?
    }
}
?>