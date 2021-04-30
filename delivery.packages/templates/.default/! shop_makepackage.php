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
    </ul>
</div>


<?
if (count($arResult["ERRORS"]) > 0) 
	echo '
		<p class="red">'.implode('</br>',$arResult["ERRORS"]).'</p>';
else {
	$_POST = array();
	$arResult["INFO"] = '';
}

if (count($arResult["MESSAGE"]) > 0) 
	echo '
		<p class="green">'.implode('</br>',$arResult["MESSAGE"]).'</p>';
else {
	if (count($arResult["WARNINGS"]) > 0) 
	echo '
		<p class="orange">'.implode('</br>',$arResult["WARNINGS"]).'</p>';
}

if ((($arResult["DEMO"]) && ($arResult["COUNT"] < $arResult["LIMIT"])) || (!$arResult["DEMO"])) {
	?>
    <p id="inser"><?=$arResult["INFO"];?></p>
    <form action="" method="post" name="makepackage" name="curform">
    	<input type="hidden" name="rand" value="<?=rand(100000,999999);?>" />
        <input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>" />
    	<table width="100%" cellpadding="3" cellspacing="0" border="0" class="borders date_icon_in"><tbody>
        	<tr>
            	<td width="200"><strong>Номер заказа</strong><span class="red">*</span>&nbsp;<sup><a href="javascript:void(0);" class="help" title="<?=GetMessage("HELP_2");?>">?</a></sup></td>
                <td><input type="text" name="n_zakaz" value="<?=$_POST['n_zakaz'];?>"></td>
            </tr>
            <tr>
            	<td><strong>Вес</strong><span class="red">*</span></td>
                <td><input type="text" name="weight" value="<? $weight = isset($_POST['weight']) ? floatval(str_replace(',','.',$_POST['weight'])) : 0; echo $weight; ?>" id="weight" onChange="CalculateCostOfDelivery();" onKeyDown="CalculateCostOfDelivery();"> кг</td>
            </tr>
            <tr>
            	<td><strong>Габариты</strong></td>
                <td>
                	<input type="text" value="<? echo isset($_POST['size_1']) ? floatval(str_replace(',','.',$_POST['size_1'])) : ''; ?>" name="size_1" placeholder="длина" class="small_inp" id="size_1" onChange="CalculateCostOfDelivery();" onKeyDown="CalculateCostOfDelivery();"> 
                    <input type="text" value="<? echo isset($_POST['size_2']) ? floatval(str_replace(',','.',$_POST['size_2'])) : ''; ?>" name="size_2" placeholder="ширина" class="small_inp" id="size_2" onChange="CalculateCostOfDelivery();" onKeyDown="CalculateCostOfDelivery();"> 
                    <input type="text" value="<? echo isset($_POST['size_3']) ? floatval(str_replace(',','.',$_POST['size_3'])) : ''; ?>" name="size_3" placeholder="высота" class="small_inp" id="size_3" onChange="CalculateCostOfDelivery();" onKeyDown="CalculateCostOfDelivery();"> см
                </td>
            </tr>
            <tr>
            	<td><strong>Количество мест</strong><span class="red">*</span></td>
                <td><input type="text" name="places" value="<? $places = isset($_POST['places']) ? intval($_POST['places']) : 1; echo $places; ?>"></td>
            </tr>
            <tr>
            	<td><strong>Условия доставки</strong><span class="red">*</span></td>
                <td>
					<?
                    if ($_POST['conditions'] > 0)
						$a = $_POST['conditions'];
					else {
						if ($arResult["SHOP_DEFAULT"]['delivery'] > 0) {
							if ($arResult["SHOP_DEFAULT"]['delivery'] == 120) $a = 37;
							if ($arResult["SHOP_DEFAULT"]['delivery'] == 121) $a = 38;
						}
					}
					?>
                	<select name="conditions" size="1" onchange="Disabled(); CalculateCostOfDelivery();" id="conditions">
                    	<option value="0"></option>
                        <option value="37"<? echo ($a == 37) ? ' selected' : ''; ?>>По адресу</option>
                        <option value="38"<? echo ($a == 38) ? ' selected' : ''; ?>>Самовывоз</option>
                    </select>
                </td>
            </tr>
            <tr>
            	<td><strong>ФИО получателя</strong></td>
                <td><input type="text" name="recipient" value="<?=$_POST['recipient'];?>"></td>
            </tr>
            <tr>
            	<td><strong>Номер телефона получателя</strong></td>
                <td><input type="text" name="phone" value="<?=$_POST['phone'];?>"></td>
            </tr>
            <tr>
            	<td><strong>Город назначения</strong></td>
                <td>
                	<input type="text" name="city" value="<? echo (isset($_POST['city'])) ? $_POST['city'] : $arResult["SHOP_DEFAULT"]['city'];?>" id="city_price_out">
                    <input type="hidden" name="price" value="<?=$arResult["PRICE"];?>" id="price">
                    <input type="hidden" name="price_2" value="<?=$arResult["PRICE_2"];?>" id="price_2">
                    <input type="hidden" name="price_3" value="<?=$arResult["PRICE_3"];?>" id="price_3">
                    <input type="hidden" name="city_id" value="<? echo (isset($_POST['city_id'])) ? $_POST['city_id'] : $arResult["SHOP_DEFAULT"]['city_id'];?>" id="city_id">
                    <input type="hidden" name="persent_1" value="<? echo (isset($_POST['persent_1'])) ? $_POST['persent_1'] : $arResult["SHOP_DEFAULT"]['persent_1'];?>" id="persent_1">
                    <input type="hidden" name="persent_2" value="<? echo (isset($_POST['persent_2'])) ? $_POST['persent_2'] : $arResult["SHOP_DEFAULT"]['persent_2'];?>" id="persent_2">
                </td>
            </tr>
            <tr class="blocy" style="display:none;">
            	<td><strong>Адрес доставки</strong></td>
                <td><textarea name="adress"><?=$_POST['adress'];?></textarea></td>
            </tr>
            <tr class="blocy" style="display:none;">
            	<td><strong>Когда доставить</strong></td>
                <td>
					<? $APPLICATION->IncludeComponent("bitrix:main.calendar", ".default", array(
						"SHOW_INPUT" => "Y",
						"FORM_NAME" => "curform",
						"INPUT_NAME" => "date_deliv",
						"INPUT_NAME_FINISH" => false,
						"INPUT_VALUE" => $_POST['date_deliv'],
						"INPUT_VALUE_FINISH" => false,
						"SHOW_TIME" => "N",
						"HIDE_TIMEBAR" => "Y",
						"INPUT_ADDITIONAL_ATTR" => 'placeholder="ДД.ММ.ГГГГ" class="small_inp date"'
						),false);
					?> 
                    <input type="text" name="timedeliv[0]" value="<?=$_POST['timedeliv'][0];?>" placeholder="ЧЧ:ММ" class="timeonly" /> &mdash; 
                    <input type="text" name="timedeliv[1]" value="<?=$_POST['timedeliv'][1];?>" placeholder="ЧЧ:ММ" class="timeonly" />
				</td>
            </tr>
            <tr>
            	<td><strong>Срочный заказ</strong>&nbsp;<sup><a href="javascript:void(0);" class="help" title="<?=GetMessage("HELP_3");?>">?</a></sup></td>
                <td><input type="checkbox" name="urgent" value="2" onChange="CalculateCostOfDelivery();" id="urgent" <? echo ($_POST['urgent'] == 2) ? ' checked' : ''; ?>></td>
            </tr>
            <tr>
            	<td><strong>Стоимость заказа</strong></td>
                <td>
                	<input type="text" name="PROPERTY_COST_GOODS_VALUE" value="<? echo isset($_POST['PROPERTY_COST_GOODS_VALUE']) ? floatval(str_replace(',','.',$_POST['PROPERTY_COST_GOODS_VALUE'])): 0; ?>" id="cost_goods_hid" onChange="CalDelivery(); CalRate();"> руб.
                </td>
            </tr>
            <tr>
            	<td><strong>Стоимость доставки</strong></td>
                <td><input type="text" name="cost_3" value="<? $cost3 = isset($_POST['cost_3']) ? floatval(str_replace(',','.',$_POST['cost_3'])): 0; echo $cost3;?>" id="cost_3" onChange="CalDelivery();"> руб.</td>
            </tr>
            <tr>
            	<td><strong>Сумма к оплате</strong></td><td><input type="text" name="cost_2" value="<? $cost2 = isset($_POST['cost_2']) ? floatval(str_replace(',','.',$_POST['cost_2'])): 0; echo $cost2;?>" id="cost_2" onKeyUp="CalRate();" onChange="CalRate();"> руб.</td>
            </tr>
            <tr>
            	<td><strong>Страховая стоимость заказа</strong></td>
                <td><input type="text" name="cost_1" value="<? $cost1 = isset($_POST['cost_1']) ? floatval(str_replace(',','.',$_POST['cost_1'])) : 0; echo $cost1; ?>" id="cost_1"> руб.</td>
            </tr>
            <tr>
            	<td><strong>Кассовое обслуживание?</strong></td>
                <td>
					<?
					if ($_POST['PROPERTY_CASH_VALUE'] > 0) $a = $_POST['PROPERTY_CASH_VALUE'];
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
                </td>
            </tr>
            <tr>
            	<td><strong>Забрать у поставщика</strong></td>
                <td><input type="checkbox" name="take_provider" value="1" <? echo ($_POST['take_provider'] == 1) ? ' checked' : ''; ?> id="take_provider" onChange="Disabled_2(); CalculateCostOfDelivery();"></td>
            </tr>
            <tr class="blocy_2" style="display:none;">
            	<td><strong>Когда забрать у поставщика</strong></td>
                <td>
					<? $APPLICATION->IncludeComponent("bitrix:main.calendar", ".default", array(
						"SHOW_INPUT" => "Y",
						"FORM_NAME" => "curform",
						"INPUT_NAME" => "date_take",
						"INPUT_NAME_FINISH" => false,
						"INPUT_VALUE" => $_POST['date_take'],
						"INPUT_VALUE_FINISH" => false,
						"SHOW_TIME" => "N",
						"HIDE_TIMEBAR" => "Y",
						"INPUT_ADDITIONAL_ATTR" => 'placeholder="ДД.ММ.ГГГГ" class="small_inp date"'
						),false);
					?> 
                    <input type="text" name="timetake[0]" value="<?=$_POST['timetake'][0];?>" placeholder="ЧЧ:ММ" class="timeonly" /> &mdash; 
                    <input type="text" name="timetake[1]" value="<?=$_POST['timetake'][1];?>" placeholder="ЧЧ:ММ" class="timeonly" />
				</td>
            </tr>
            <tr class="blocy_2" style="display:none;">
            	<td><strong>Комментарий к забору у поставщика</strong></td>
                <td><textarea name="take_comment"><?=$_POST['take_comment'];?></textarea></td>
            </tr>
            <tr>
            	<td><strong>Комментарий к заказу</strong></td>
                <td><textarea name="time"><?=$_POST['time'];?></textarea></td>
            </tr>
            <tr>
            	<td><strong>Агентское вознаграждение</strong>&nbsp;<sup><a href="javascript:void(0);" class="help" title="<?=GetMessage("HELP_1");?>">?</a></sup></td>
                <td> 
					<?
                    foreach ($arResult["RATE"] as $k=> $v) {
						?>
                        <input type="hidden" name="rate[<?=$k;?>]" value="<?=$v;?>" id="rate_<?=$k;?>">
                        <?
                    }
					?>
                    <span id="rate_value_new"><?=$arResult["RATE"][$pers_key];?></span>% - <input type="hidden" name="rate" value="<?=$arResult["RATE"][$pers_key];?>" id="rate">
                    <input type="hidden" name="PROPERTY_RATE_VALUE" value="<?=(isset($_POST['PROPERTY_RATE_VALUE'])) ? $_POST['PROPERTY_RATE_VALUE'] : 0;?>" id="rate_value">
                    <span id="rate_value_span"><?=(isset($_POST['PROPERTY_RATE_VALUE'])) ? $_POST['PROPERTY_RATE_VALUE'] : 0; ?></span> руб.
                </td>
            </tr>
        </tbody></table>
        <input type="submit" value="Оформить заказ" name="make_package">
    </form>
	<?
}
?>