<script>
	$(document).ready(function(){
		CalculateCostOfDelivery();
		Disabled();
		Disabled_2();
	});
	
	function Disabled() {
		var type = $('#conditions').val();
		if (type == '38') {
            $('.blocy').css('display','none');
        }
        if (type == '37') {
            $('.blocy').css('display','table-row');
        }
    }
	
	function Disabled_2() {
		if ($("#take_provider").is(":checked")) {
			$('.blocy_2').css('display','table-row');
		}
		else {
			$('.blocy_2').css('display','none');
		}
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
    <input type="hidden" name="pack_id" value="<?=$arResult['PACK']["ID"];?>">
    <input type="hidden" name="PROPERTY_N_ZAKAZ_VALUE" value="<?=nZakaz($arResult['PACK']['PROPERTY_N_ZAKAZ_VALUE']);?>">
    
    <table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="borders date_icon_in"><tbody>
    <tr>
    	<td width="220"><strong>Дата создания</strong></td>
        <td><?=$arResult['PACK']['DATE_CREATE'];?></td>
    </tr>
    <tr>
    	<td><strong>Номер заказа</strong><span class="red">*</span>&nbsp;<sup><a href="javascript:void(0);" class="help" title="<?=GetMessage("HELP_2");?>">?</a></sup></td>
        <td><input type="text" name="PROPERTY_N_ZAKAZ_VALUE" value="<?=$arResult['PACK']['PROPERTY_N_ZAKAZ_VALUE'];?>"></td>
    </tr>
    <tr>
    	<td><strong>Вес</strong><span class="red">*</span></td>
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
   <tr>
    	<td><strong>Условия доставки</strong><span class="red">*</span></td>
        <td><select name="PROPERTY_CONDITIONS_ENUM_ID" size="1" onchange="Disabled(); CalculateCostOfDelivery();" id="conditions">
                <option value="37" <? echo ($arResult['PACK']['PROPERTY_CONDITIONS_ENUM_ID'] == 37) ? ' selected' : ''; ?>>По адресу</option>
                <option value="38" <? echo ($arResult['PACK']['PROPERTY_CONDITIONS_ENUM_ID'] == 38) ? ' selected' : ''; ?>>Самовывоз</option>
            </select>
        </td>
    </tr>
    <tr>
    	<td><strong>ФИО получателя</strong><span class="red">*</span></td>
        <td><input type="text" name="PROPERTY_RECIPIENT_VALUE" value="<?=$arResult['PACK']['PROPERTY_RECIPIENT_VALUE'];?>"></td>
    </tr>
    <tr>
    	<td><strong>Номер телефона получателя</strong><span class="red">*</span></td>
        <td><input type="text" name="PROPERTY_PHONE_VALUE" value="<?=$arResult['PACK']['PROPERTY_PHONE_VALUE'];?>"></td>
    </tr>
    <tr>
    	<td><strong>Город назначения</strong><span class="red">*</span></td>
        <td>
        	<input type="text" name="PROPERTY_CITY" value="<? echo strlen($arResult['PACK']['PROPERTY_CITY']) ? $arResult['PACK']['PROPERTY_CITY'] : $arResult["SHOP_DEFAULT"]['city']; ?>" id="city_price_out">
            <input type="hidden" name="price" value="<?=$arResult["PRICE"];?>" id="price">
            <input type="hidden" name="price_2" value="<?=$arResult["PRICE_2"];?>" id="price_2">
            <input type="hidden" name="price_3" value="<?=$arResult["PRICE_3"];?>" id="price_3">
            <input type="hidden" name="city_id" value="<? echo strlen($arResult['PACK']['PROPERTY_CITY_VALUE']) ? $arResult['PACK']['PROPERTY_CITY_VALUE'] : $arResult["SHOP_DEFAULT"]['city_id']; ?>" id="city_id">
            <input type="hidden" name="persent_1" value="" id="persent_1">
            <input type="hidden" name="persent_2" value="" id="persent_2">
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
            <input type="text" name="timedeliv[0]" value="<?=$arResult['PACK']["DELIV_TIME_1"];?>" placeholder="ЧЧ:ММ" class="timeonly" /> &mdash; 
            <input type="text" name="timedeliv[1]" value="<?=$arResult['PACK']["DELIV_TIME_2"];?>" placeholder="ЧЧ:ММ" class="timeonly" />
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
    	<td><strong>Стоимость заказа</strong><span class="red">*</span></td>
        <td><input type="text" name="PROPERTY_COST_GOODS_VALUE" value="<?=$arResult['PACK']['PROPERTY_COST_GOODS_VALUE'];?>" id="cost_goods_hid" onChange="CalDelivery(); CalRate();"> руб.</td>
    </tr>
    <tr>
    	<td><strong>Стоимость доставки</strong></td>
        <td><input type="text" name="PROPERTY_COST_3_VALUE" value="<?=$arResult['PACK']['PROPERTY_COST_3_VALUE'];?>" id="cost_3" onChange="CalDelivery(); CalRate();"> руб.</td>
    </tr>
    <tr>
    	<td><strong>Сумма к оплате</strong><span class="red">*</span></td>
        <td><input type="text" name="PROPERTY_COST_2_VALUE" value="<?=$arResult['PACK']['PROPERTY_COST_2_VALUE'];?>" onKeyUp="CalRate();" id="cost_2"> руб.</td>
    </tr>
    <tr>
    	<td><strong>Страховая стоимость заказа</strong></td>
        <td><input type="text" name="PROPERTY_COST_1_VALUE" value="<?=$arResult['PACK']['PROPERTY_COST_1_VALUE'];?>" id="cost_1"></td>
    </tr>
    <tr>
    	<td><strong>Кассовое обслуживание?</strong><span class="red">*</span></td>
        <td><select name="PROPERTY_CASH_VALUE" size="1" id="cash" onChange="ReCalcRate();">
        		<option value="124"<? echo ($arResult['PACK']['PROPERTY_CASH_ENUM_ID'] == 124) ? ' selected' : ''; ?>>да</option>
                <option value="125"<? echo ($arResult['PACK']['PROPERTY_CASH_ENUM_ID'] == 125) ? ' selected' : ''; ?>>нет</option>
            </select></td>
    </tr>
    <tr>
    	<td><strong>Забрать у поставщика</strong></td>
        <td><input type="checkbox" name="take_provider" value="1" <? echo ($arResult['PACK']["PROPERTY_TAKE_PROVIDER_ENUM_ID"] == 174) ? ' checked' : ''; ?> id="take_provider" onChange="Disabled_2(); CalculateCostOfDelivery();"></td>
    </tr>
    <tr class="blocy_2" style="display:none;">
    	<td><strong>Когда забрать у поставщика</strong></td>
        <td>
			<? $APPLICATION->IncludeComponent("bitrix:main.calendar", ".default", array(
				"SHOW_INPUT" => "Y",
				"FORM_NAME" => "curform",
				"INPUT_NAME" => "date_take",
				"INPUT_NAME_FINISH" => false,
				"INPUT_VALUE" => $arResult['PACK']["TAKE_DATE"],
				"INPUT_VALUE_FINISH" => false,
				"SHOW_TIME" => "N",
				"HIDE_TIMEBAR" => "Y",
				"INPUT_ADDITIONAL_ATTR" => 'placeholder="ДД.ММ.ГГГГ" class="small_inp date"'
				),false);
			?> 
             <input type="text" name="timetake[0]" value="<?=$arResult['PACK']["TAKE_TIME_1"];?>" placeholder="ЧЧ:ММ" class="timeonly" /> &mdash; 
             <input type="text" name="timetake[1]" value="<?=$arResult['PACK']["TAKE_TIME_2"];?>" placeholder="ЧЧ:ММ" class="timeonly" />
		</td>
    </tr>
    <tr class="blocy_2" style="display:none;">
    	<td><strong>Комментарий к забору у поставщика</strong></td>
        <td><textarea name="take_comment"><?=$arResult['PACK']["PROPERTY_TAKE_COMMENT_VALUE"]["TEXT"];?></textarea></td>
    </tr>
    <tr>
    	<td><strong>Комментарий к заказу</strong></td>
        <td><textarea name="PROPERTY_PREFERRED_TIME_VALUE"><?=$arResult['PACK']['PROPERTY_PREFERRED_TIME_VALUE']["TEXT"];?></textarea></td>
    </tr>
    
    <tr>
    	<td><strong>Агентское вознаграждение</strong &nbsp;><sup><a href="#" class="help" title="<?=GetMessage("HELP_1");?>">?</a></sup></td>
        <td>
			<?
            	foreach ($arResult["RATE"] as $k=> $v) {
					?>
                    <input type="hidden" name="rate[<?=$k;?>]" value="<?=$v;?>" id="rate_<?=$k;?>">
                    <?
				 }
			?>
            <span id="rate_value_new"><?=$arResult["RATE"][$pers_key];?></span>% - 
            <input type="hidden" name="rate" value="<?=$arResult["RATE"][$pers_key];?>" id="rate">
            <input type="hidden" name="PROPERTY_RATE_VALUE" value="<? echo  (isset($arResult['PACK']['PROPERTY_RATE_VALUE'])) ? $arResult['PACK']['PROPERTY_RATE_VALUE'] : 0; ?>" id="rate_value">
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
                 <tr><td><strong>В том числе за забор</strong></td><td><?=CurrencyFormat($arResult['PACK']["PROPERTY_SUMM_SHOP_ZABOR_VALUE"],"RUU");?></td></tr>
                <?
			}
			?>
    </tbody></table>
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