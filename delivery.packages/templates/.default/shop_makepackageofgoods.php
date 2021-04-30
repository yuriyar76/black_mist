<script type="text/javascript">
	$(document).ready(function()
	{
		CalculateCostOfDelivery();
		// CalDelivery();
		Disabled();
	});
	
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

	function SelectRow(ch,ro)
	{
		if ($("#"+ch).is(":checked"))
		{
			$("#"+ro).addClass('CheckedRow');
		}
		else
		{
			$("#"+ro).removeClass('CheckedRow');
		}
	}
	
	function ChangeSummOfGoods()
	{
		var sum = 0;
		var counts = 0;
		var weights = 0;
		jQuery('.counts_of_goods').each(function() {
			var currentElement = $(this);
			var value = parseInt(currentElement.val());
			var el = currentElement.attr('data-grid');
			var price = parseFloat($('#cost_of_good_'+el).val(),10);
			var weight = parseFloat($('#weight_of_good_'+el).val(),10);
			var sum_el = value*price;
			var weight_el = value*weight;
			$('#summ_'+el).html(sum_el.toFixed(2));
			$('#weight_'+el).html(weight_el.toFixed(2));
			sum = sum + sum_el;
			counts = counts + value;
			weights = weights + weight_el;
		});
		sum = parseFloat(sum,10);
		weights = parseFloat(weights,10);
		counts = parseFloat(counts,10);
		$('#span_cost_goods_hid').html(sum.toFixed(2));
		$('#itogo_cost_of_goods').html(sum.toFixed(2));
		$('#cost_goods_hid').val(sum.toFixed(2));
		$('#itogo_count_of_goods').html(counts.toFixed(0));
		$('#itogo_weight_of_goods').html(weights.toFixed(2));
		$('#weight_of_order').html(weights.toFixed(2));
		$('#weight').val(weights.toFixed(2));
		CalculateCostOfDelivery();
		CalDelivery();
	}
</script>

<div class="new_menu">
    <ul>
        <li class="active"><a href="javascript:void(0);"><?=$arResult["TITLE"];?></a></li>
        <?
        foreach ($arResult["MENU"] as $k => $v)
		{
            ?>
            <li><a href="index.php?mode=<?=$k?>"><?=$v;?></a></li>
            <?
        }
        ?>
    </ul>
</div>

<?

if (count($arResult["ERRORS"]) > 0) 
	echo '
		<p class="red">'.implode('</br>',$arResult["ERRORS"]).'</p>';
if (count($arResult["MESSAGE"]) > 0) 
	echo '
		<p class="green">'.implode('</br>',$arResult["MESSAGE"]).'</p>';
if (count($arResult["WARNINGS"]) > 0) 
	echo '
		<p class="orange">'.implode('</br>',$arResult["WARNINGS"]).'</p>';

if ($arResult['PACK'])
{
	if ($arResult['EDIT'])
	{
		?>
		<form action="" method="post">
            <input type="hidden" name="rand" value="<?=rand(100000,999999);?>" />
            <input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>" />
            <input type="hidden" name="pack_id" value="<?=$arResult['PACK']["ID"];?>">
            <input type="hidden" name="id_in" value="<?=$arResult['PACK']["PROPERTY_ID_IN_VALUE"];?>">
            <input type="hidden" name="shop_id" value="<?=$arResult['PACK']["PROPERTY_CREATOR_VALUE"];?>">
            <input type="hidden" name="d_create" value="<?=$arResult['PACK']["DATE_CREATE"];?>">
            <input type="hidden" name="number_order" value="<?=$arResult['PACK']["PROPERTY_N_ZAKAZ_IN_VALUE"];?>">
   		<?
	}
	?>
    <p id="inser"><?=$arResult["INFO"];?></p>
    <div class="table_group">
    	<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="<? echo $arResult['EDIT'] ? 'borders date_icon_in': 'rows';?>">
        	<tbody>
				<tr>
                    <td width="300"><strong>Дата создания</strong></td>
                    <td><?=$arResult['PACK']['DATE_CREATE'];?></td>
                </tr>
                <tr>
                    <td><strong>Номер заказа</strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_N_ZAKAZ_IN_VALUE'];?></td>
                </tr>
				<tr>
                    <td><strong>Внутренний номер заказа</strong></td>
                    <td>
                    	<?
						if ($arResult['EDIT'])
						{
							?>
                            <input type="text" name="PROPERTY_N_ZAKAZ" value="<?=$arResult['PACK']['PROPERTY_N_ZAKAZ_VALUE'];?>">
                            <?
						}
						else
						{
							echo $arResult['PACK']['PROPERTY_N_ZAKAZ_VALUE'];
						}
						?>
					</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="table_group">
    	<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="<? echo $arResult['EDIT'] ? 'borders date_icon_in': 'rows';?>">
        	<tbody>
				<tr>
                	<td width="300"><strong>Получатель</strong></td>
                    <td>
						<?
							echo $arResult['EDIT'] ? 
								'<input type="text" name="PROPERTY_RECIPIENT_VALUE" value="'.$arResult['PACK']['PROPERTY_RECIPIENT_VALUE'].'">' : 
								$arResult['PACK']['PROPERTY_RECIPIENT_VALUE'];
						?>
					</td>
				</tr>
				<tr>
                	<td><strong>Номер телефона получателя</strong></td>
                    <td>
						<?
			echo $arResult['EDIT'] ? '<input type="text" name="PROPERTY_PHONE_VALUE" value="'.$arResult['PACK']['PROPERTY_PHONE_VALUE'].'">' : $arResult['PACK']['PROPERTY_PHONE_VALUE'];
			?>
            </td></tr>
                 <tr><td><strong>Город назначения</strong></td><td>
			<?
			if ($arResult['EDIT']) { echo '<input type="text" name="PROPERTY_CITY" value="';
			echo strlen($arResult['PACK']['CITY_NAME']) ? $arResult['PACK']['CITY_NAME'] : $arResult["SHOP_DEFAULT"]['city'];
			echo '" id="city_price_out">
			<input type="hidden" name="price" value="'.$arResult["PRICE"].'" id="price">
			<input type="hidden" name="price_2" value="'.$arResult["PRICE_2"].'" id="price_2">
			<input type="hidden" name="city_id" value="';
			echo strlen($arResult['PACK']['PROPERTY_CITY_VALUE']) ? $arResult['PACK']['PROPERTY_CITY_VALUE'] : $arResult["SHOP_DEFAULT"]['city_id'];
			echo '" id="city_id">
<input type="hidden" name="persent_1" value="" id="persent_1">
<input type="hidden" name="persent_2" value="" id="persent_2">
			'; }
			else echo $arResult['PACK']['PROPERTY_CITY'];
			?>
            </td></tr>
             <? if ($arResult['EDIT']) { ?>
            <tr><td><strong>Условия доставки</strong></td><td><select name="PROPERTY_CONDITIONS_ENUM_ID" size="1" onchange="Disabled(); CalculateCostOfDelivery();" id="conditions"><option value="0"></option>
<option value="37"<? echo ($arResult['PACK']['PROPERTY_CONDITIONS_ENUM_ID'] == 37) ? ' selected' : ''; ?>>По адресу</option>
<option value="38"<? echo ($arResult['PACK']['PROPERTY_CONDITIONS_ENUM_ID'] == 38) ? ' selected' : ''; ?>>Самовывоз</option>
</select></td></tr>
<? } ?>
            <tr class="blocy" style="display:none;"><td><strong>Адрес доставки</strong></td><td><?
if ($arResult['EDIT']) 
echo '<textarea name="PROPERTY_ADRESS_VALUE">'.$arResult['PACK']['PROPERTY_ADRESS_VALUE'].'</textarea>';
else {
	$adr = ($arResult['PACK']['PROPERTY_CONDITIONS_ENUM_ID'] == 38) ? $arResult['PACK']['PROPERTY_CONDITIONS_VALUE'] : $arResult['PACK']['PROPERTY_ADRESS_VALUE']; echo $adr;
}
?></td></tr>

            <tr class="blocy" style="display:none;">
            	<td><strong>Когда доставить</strong></td>
                <td>
					<? 
					if  ($arResult['EDIT']) {
					$APPLICATION->IncludeComponent("bitrix:main.calendar", ".default", array(
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
                    <? } 
					else 
						echo $arResult['PACK']['PROPERTY_WHEN_TO_DELIVER_VALUE'];
					?>
				</td>
            </tr>  
                        <tr>
            <td><strong>Срочный заказ</strong>&nbsp;<sup><a href="javascript:void(0);" class="help" title="<?=GetMessage("HELP_3");?>">?</a></sup></td><td>
            <?
			if ($arResult['EDIT']) { ?>
<input type="checkbox" name="urgent" value="2" onChange="CalculateCostOfDelivery();" id="urgent" <? echo ($arResult['PACK']['PROPERTY_URGENCY_ORDER_ENUM_ID'] == 172) ? ' checked' : '';?>>
<? } else  {
	echo ($arResult['PACK']['PROPERTY_URGENCY_ORDER_ENUM_ID'] == 172) ? ' да' : 'нет';
	 } ?>
</td></tr>
			<tr>
				<td><strong>Доставка юр. лицу</strong>&nbsp;<sup><a href="javascript:void(0);" class="help" title="<?=GetMessage("HELP_4");?>">?</a></sup></td>
				<td>
                <? if ($arResult['EDIT']) { ?>
                <input type="checkbox" name="to_legal" value="1" <? echo ($arResult['PACK']['PROPERTY_DELIVERY_LEGAL_VALUE'] ==  1) ? ' checked' : ''; ?>>
                <? }
				else {
					echo ($arResult['PACK']['PROPERTY_DELIVERY_LEGAL_VALUE'] == 1) ? "да" : "нет";
				}?>
                </td>
            </tr>
            <tr><td><strong>Комментарий к заказу</strong></td><td><?
if ($arResult['EDIT']) 
echo '<textarea name="PROPERTY_PREFERRED_TIME_VALUE">'.$arResult['PACK']['PROPERTY_PREFERRED_TIME_VALUE']["TEXT"].'</textarea>';
else {
	echo $arResult['PACK']['PROPERTY_PREFERRED_TIME_VALUE']["TEXT"];
}
?></td></tr>
            </tbody>
        </table>
    </div>
	<div class="table_group">
		<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="<? echo $arResult['EDIT'] ? 'borders date_icon_in': 'rows';?>">
        	<tbody>
            			<tr>
            	<td width="300"><strong>Вес</strong></td>
                <td>
                	<?
					if ($arResult['EDIT'])
					{
						?>
                		<input type="hidden" name="PROPERTY_WEIGHT_VALUE" 
                        	value="<?=$arResult['PACK']['PROPERTY_WEIGHT_VALUE'];?>" id="weight"> <span id="weight_of_order"><?=$arResult['PACK']['PROPERTY_WEIGHT_VALUE'];?></span> кг
						<?
					}
					else
					{
						echo WeightFormat($arResult['PACK']['PROPERTY_WEIGHT_VALUE']);
					}
					?>
				</td>
			</tr>
                        <tr><td><strong>Габариты</strong></td><td>
            <?
			echo $arResult['EDIT'] ? '
			<input type="text" name="PROPERTY_SIZE_1_VALUE" value="'.$arResult['PACK']['PROPERTY_SIZE_1_VALUE'].'" placeholder="длина" class="small_inp" id="size_1" onChange="CalculateCostOfDelivery();" onKeyDown="CalculateCostOfDelivery();">  
			<input type="text" name="PROPERTY_SIZE_2_VALUE" value="'.$arResult['PACK']['PROPERTY_SIZE_2_VALUE'].'" placeholder="ширина" class="small_inp" id="size_2" onChange="CalculateCostOfDelivery();" onKeyDown="CalculateCostOfDelivery();">  
			<input type="text" name="PROPERTY_SIZE_3_VALUE" value="'.$arResult['PACK']['PROPERTY_SIZE_3_VALUE'].'" placeholder="высота" class="small_inp" id="size_3" onChange="CalculateCostOfDelivery();" onKeyDown="CalculateCostOfDelivery();">' : $arResult['PACK']['PROPERTY_SIZE_1_VALUE'].'*'.$arResult['PACK']['PROPERTY_SIZE_3_VALUE'].'*'.$arResult['PACK']['PROPERTY_SIZE_3_VALUE'];
			?> см</td></tr>
            <tr><td><strong>Количество мест</strong></td><td>
			<?
			if ($arResult['EDIT']) { 
				echo '<input type="text" name="PROPERTY_PLACES_VALUE" value="';
				echo strlen($arResult['PACK']['PROPERTY_PLACES_VALUE']) ? $arResult['PACK']['PROPERTY_PLACES_VALUE'] : '1';
				echo '">'; 
			}
			else echo $arResult['PACK']['PROPERTY_PLACES_VALUE'];
			?></td></tr>
            </tbody>
        </table>
	</div>
	<div class="table_group">
		<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="<? echo $arResult['EDIT'] ? 'borders date_icon_in': 'rows';?>">
			<tbody>
				<tr>
                	<td width="300"><strong>Стоимость заказа</strong>&nbsp;<sup><a href="javascript:void(0);" class="help" title="<?=GetMessage("HELP_5");?>">?</a></sup></td>
                    <td>
                    	<span id="span_cost_goods_hid"><?=$arResult['PACK']['PROPERTY_COST_GOODS_VALUE'];?></span> руб.
						<input type="hidden" name="cost_goods_hid" id="cost_goods_hid" value="<?=$arResult['PACK']['PROPERTY_COST_GOODS_VALUE'];?>">
					</td>
				</tr>
				<tr>
                	<td><strong>За доставку</strong></td>
                    <td>
                    	<?
						echo $arResult['EDIT'] ? '<input type="text" name="PROPERTY_COST_3_VALUE" value="'.$arResult['PACK']['PROPERTY_COST_3_VALUE'].'" id="cost_3" onChange="CalDelivery(); ReCalcRate();">' : $arResult['PACK']['PROPERTY_COST_3_VALUE'];
			?> руб.
					</td>
				</tr>
				<tr>
                	<td><strong>Сумма к оплате</strong></td>
                    <td>
						<?
						if ($arResult['EDIT'])
						{
							?>
							<input type="text" name="PROPERTY_COST_2_VALUE" value="<?=$arResult['PACK']['PROPERTY_COST_2_VALUE'];?>" id="cost_2" onKeyUp="ReCalcRate();" onChange="ReCalcRate();">
							<?
						}
						else
						{
							echo $arResult['PACK']['PROPERTY_COST_2_VALUE'];
						}
						?> руб.
					</td>
				</tr>
                <!--
				<tr>
                	<td><strong>Страховая стоимость заказа</strong></td>
                    <td>
                    	<?
						echo $arResult['EDIT'] ? '<input type="text" name="PROPERTY_COST_1_VALUE" value="'.$arResult['PACK']['PROPERTY_COST_1_VALUE'].'" id="cost_1" onKeyUp="ReCalcRate();" onChange="ReCalcRate();">' : $arResult['PACK']['PROPERTY_COST_1_VALUE'];
						?> руб.
					</td>
				</tr> 
                -->
             <tr><td><strong>Кассовое обслуживание?</strong></td><td>
          <? if ($arResult['EDIT']) { 
			if ($arResult['PACK']['PROPERTY_CASH_ENUM_ID'] > 0) $a = $arResult['PACK']['PROPERTY_CASH_ENUM_ID'];
			else {
				if ($arResult["SHOP_DEFAULT"]['cash'] > 0) {
					if ($arResult["SHOP_DEFAULT"]['cash'] == 122) $a = 124;
					if ($arResult["SHOP_DEFAULT"]['cash'] == 123) $a = 125;
				}
				else $a = 124;
			}
			?>
          <select name="PROPERTY_CASH_VALUE" size="1" id="cash" onChange="ReCalcRate();">
          <option value="124"<? echo ($a == 124) ? ' selected' : ''; ?>>да</option>
          <option value="125"<? echo ($a == 125) ? ' selected' : ''; ?>>нет</option>
          </select>
          <? } else echo $arResult['PACK']["PROPERTY_CASH_VALUE"]; ?>
          </td></tr>
          <tr>
                    	<td><strong>Брать c получателя оплату за доставку при отказе</strong></td>
                    	<td>
                        <?
if ($arResult['EDIT']) { 
						 ?>
                        	<input type="checkbox" name="refusal" value="1" <? echo ($arResult['PACK']['PROPERTY_PAY_FOR_REFUSAL_VALUE'] == 1) ? ' checked' : ''; ?>>
                            <?
}
else
{
}
?>
                        </td>
                    </tr>
          
            </tbody>
		</table>
    </div>
    <div class="table_group">
		<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="<? echo $arResult['EDIT'] ? 'borders date_icon_in': 'rows';?>">
			<tbody>
                          <tr>
<td width="300"><strong>Агентское вознаграждение</strong>
               <? if ($arResult['EDIT']) { ?>
              &nbsp;<sup><a href="javascript:void(0);" class="help" title="<?=GetMessage("HELP_1");?>">?</a></sup>
              <? } ?>
              </td><td> <?
			  if ($arResult['EDIT'])
			  {
				  foreach ($arResult["RATE"] as $k=> $v)
				  {
					  echo '
					  <input type="hidden" name="rate['.$k.']" value="'.$v.'" id="rate_'.$k.'">
					  ';
				  }
				  echo '<span id="rate_value_new">0</span>% - 
<input type="hidden" name="rate" value="0" id="rate">
<input type="hidden" name="PROPERTY_RATE_VALUE" value="'.$arResult['PACK']['PROPERTY_RATE_VALUE'].'" id="rate_value">'; 
			  }
			  ?>
               <input type="hidden" name="CONDITIONS_IM" value="<?=$arResult['CONDITIONS_IM'];?>" id="conditions_im">
             <span id="rate_value_span"><?=$arResult['PACK']['PROPERTY_RATE_VALUE'];?></span> руб.</td></tr>
             <? if (!$arResult['EDIT']) { ?>
             <tr><td><strong>Стоимость доставки</strong></td><td><?=floatval($arResult['PACK']['PROPERTY_SUMM_SHOP_VALUE']);?> руб.</td></tr>
             <? } ?>
            <? if ($arResult['EDIT']) { 
			if ($arResult['PACK']['PROPERTY_CONDITIONS_ENUM_ID'] > 0) $a = $arResult['PACK']['PROPERTY_CONDITIONS_ENUM_ID'];
			else {
				if ($arResult["SHOP_DEFAULT"]['delivery'] > 0) {
					if ($arResult["SHOP_DEFAULT"]['delivery'] == 120) $a = 37;
					if ($arResult["SHOP_DEFAULT"]['delivery'] == 121) $a = 38;
				}
			}
			?>
<? } ?>
            </tbody>
		</table>
    </div>
    
     <? if ((strlen($arResult['PACK']['PROPERTY_COURIER_NAME'])) || (strlen($arResult['PACK']['PROPERTY_DATE_DELIVERY_VALUE']))) { ?>
     <div class="table_group">
     <table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="<? echo $arResult['EDIT'] ? 'borders date_icon_in': 'rows';?>">
    	<tbody>
     <? } ?>
    



           


   
     

 

     
         


            <? if(strlen($arResult['PACK']['PROPERTY_COURIER_NAME'])) { ?>
            <tr><td width="300"><strong>Курьер</strong></td><td><?=$arResult['PACK']['PROPERTY_COURIER_NAME'];?></td></tr>
            <? } 
			if(strlen($arResult['PACK']['PROPERTY_DATE_DELIVERY_VALUE'])) { ?>
            <tr><td width="300"><strong>Дата и время доставки</strong></td><td><?=$arResult['PACK']['PROPERTY_DATE_DELIVERY_VALUE'];?></td></tr>
            <? } ?>
            
                <? if ((strlen($arResult['PACK']['PROPERTY_COURIER_NAME'])) || (strlen($arResult['PACK']['PROPERTY_DATE_DELIVERY_VALUE']))) { ?>
    </tbody></table></div>
     <? } ?>
            
             <div class="status_block">
            <?=$arResult['PACK']['PROPERTY_STATE_VALUE'];?>
            </div>
            <?
			
if (count($arResult['PACK']['GOODS']) > 0) {
?>
<h4 style="position:relative; height:30px; line-height:30px;">Товары заказа <a href="xls.php?id=<?=$arResult['PACK']['ID'];?>" style=" display:block; width:30px; height:30px; position:absolute; top:0; right:0;"><img src="/bitrix/templates/portal/images/excel_ico.jpg" width="30" height="30" alt="" /></a></h4>
<table cellpadding="3" cellspacing="0" border="1" bordercolor="#ccc" class="rows" width="100%">
<thead><tr>
 <? if ($arResult['EDIT']) { ?>
<td width="10" align="center">
<input type="checkbox" name="set" onclick="setCheckedNew(this,'id_good_row')" />
</td>
<? } ?>
<td>Наименование</td><td>Артикул</td><td width="60">Количество</td><td>Вес</td><td>Суммарный вес</td><td>Цена</td><td>Стоимость</td>

</tr></thead>
<tbody>
<?

$counts = $weighs = $costs = 0;
foreach ($arResult['PACK']['GOODS'] as $k=> $v)
{
	?>
	<tr id="row_<?=$k;?>" class="CheckedRows">
         <? if ($arResult['EDIT']) { ?>
    <td width="10" align="center"><input type="checkbox" name="id_good_row[]" value="<?=$k;?>" onChange="SelectRow('check_<?=$k;?>','row_<?=$k;?>');" id="check_<?=$k;?>"/></td>
    <? } ?>
    <td><?=$v['NAME'];?></td><td><?=$v['ARTICLE'];?></td>
    <td align="center">
    <? if ($arResult['EDIT']) { ?>
    <input type="text" name="count[<?=$k;?>]" value="<?=$v['COUNT'];?>" class="short counts_of_goods" onChange="ChangeSummOfGoods();" data-grid="<?=$k;?>">
    <? } else { 
	echo $v['COUNT'];
	} ?>
    <input type="hidden" name="weigh[<?=$k;?>]" value="<?=$v['WEIGHT'];?>" id="weight_of_good_<?=$k;?>">
    </td><td><?=WeightFormat($v['WEIGHT']);?></td>
    <td><span id="weight_<?=$k;?>"><?=$v['WEIGHT']*$v['COUNT'];?></span> кг</td>
    <td>
    <? if ($arResult['EDIT']) { ?>
    <input type="text" name="cost[<?=$k;?>]" value="<?=$v['COST'];?>" class="medium costs_of_goods" onChange="ChangeSummOfGoods();" id="cost_of_good_<?=$k;?>"> руб.
    <? } else {?>
    <input type="hidden" name="cost[<?=$k;?>]" value="<?=$v['COST'];?>"> <?=CurrencyFormat($v['COST'],'RUU');?>
    <? } ?>
    </td><td>
    <span id="summ_<?=$k;?>"><?=($v['COST']*$v['COUNT']);?></span> руб.
    </td>
    </tr>
    <?
	$counts = $counts + $v['COUNT'];
	$weighs = $weighs + $v['WEIGHT']*$v['COUNT'];
	$costs = $costs + $v['COST']*$v['COUNT'];
}
?>
<tr>
	<td colspan="<?=($arResult['EDIT']) ? 3 : 2;?>" align="right"><strong>Итого:</strong></td>
    <td align="right"><strong><span id="itogo_count_of_goods"><?=$counts;?></span> шт</strong></td>
    <td>&nbsp;</td>
    <td align="right"><strong><span id="itogo_weight_of_goods"><?=$weighs;?></span> кг</strong></td>
    <td></td>
    <td align="right"><strong><span id="itogo_cost_of_goods"><?=$costs;?></span> руб.</strong></td>
</tr>
</tbody>
</table>
<?	
}

if ($arResult['EDIT']) {
	?>
			<input type="submit" value="Сохранить заказ" name="save_package_shop">
           <? if (count($arResult['PACK']['GOODS']) > 0) { ?>
            <input name="delete_goods" value="Удалить товары" type="submit">
            <? } ?>
            <br />
            <input type="submit" value="Отправить заявку на формирование заказа" name="for_order_form">
			</form>
            <?
}

			if (count($arResult['PACK']['SHORT_HISTORY']) > 0) {
				?>
                <h4>История по заказу</h4>
                <table cellpadding="3" cellspacing="0" border="1" bordercolor="#ccc" class="rows">
                <thead><tr><td>Дата</td><td>Статус</td><td>Расшифровка</td><td>Кем изменен</td></tr></thead>
                <tbody>
                <?
				foreach ($arResult['PACK']['SHORT_HISTORY'] as $h)
					echo '
					<tr>
					<td>'.$h['DATE_CREATE'].'</td>
					<td>'.$h['NAME'].'</td>
					<td>'.$h['DETAIL_TEXT'].'</td>
					<td>'.$h['WHO']['LAST_NAME'].' '.$h['WHO']['NAME'].' ['.$h['MODIFIED_BY'].']</td>
					</tr>';
				?>
                </tbody>
                </table>
                <?
			}
			}
else {
	?>
    <p>Заказ не найден</p>
    <?
}
?>