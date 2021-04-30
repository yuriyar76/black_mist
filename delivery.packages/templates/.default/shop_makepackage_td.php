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
{
	echo '<p class="red">'.implode('</br>',$arResult["ERRORS"]).'</p>';
}
else
{
	$arResult["INFO"] = '';
}

if (count($arResult["MESSAGE"]) > 0) 
{
	echo '<p class="green">'.implode('</br>',$arResult["MESSAGE"]).'</p>';
}
else
{
	if (count($arResult["WARNINGS"]) > 0) 
	echo '
		<p class="orange">'.implode('</br>',$arResult["WARNINGS"]).'</p>';
}
if ((($arResult["DEMO"]) && ($arResult["COUNT"] < $arResult["LIMIT"])) || (!$arResult["DEMO"]))
{
	?>
    <form action="" method="post" name="makepackage" name="curform">
		<input type="hidden" name="rand" value="<?=rand(100000,999999);?>" />
        <input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>" />
		<div class="table_group">
			<table width="100%" cellpadding="3" cellspacing="0" border="0" class="borders date_icon_in">
            	<tbody>
					<tr>
						<td width="300"><strong>Внутренний номер заказа</strong><span class="red">*</span></td>
						<td><input type="text" name="n_zakaz" value="<?=$_POST['n_zakaz'];?>"></td>
					</tr>
				</tbody>
			</table>
        </div>
        <div class="table_group">
			<table width="100%" cellpadding="3" cellspacing="0" border="0" class="borders date_icon_in">
            	<tbody>
					<tr>
						<td width="300"><strong>Получатель</strong><span class="red">*</span></td>
						<td><input type="text" name="recipient" value="<?=$_POST['recipient'];?>"></td>
                    </tr>
					<tr>
						<td><strong>Номер телефона</strong><span class="red">*</span></td>
						<td><input type="text" name="phone" value="<?=$_POST['phone'];?>"></td>
					</tr>
                    <tr>
                    	<td><strong>Город назначения</strong><span class="red">*</span></td>
                        <td>
                        	<input type="text" name="city" value="<? echo (isset($_POST['city'])) ? $_POST['city'] : $arResult["SHOP_DEFAULT"]['city'];?>" id="city_price_out">
						</td>
					</tr>
                    <tr>
                    	<td><strong>Условия доставки</strong><span class="red">*</span></td>
                        <td>
							<?
							if ($_POST['conditions'] > 0)
							{
								$a = $_POST['conditions'];
							}
							else
							{
								if ($arResult["SHOP_DEFAULT"]['delivery'] > 0)
								{
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
					<tr class="blocy" style="display:none;">
						<td><strong>Адрес доставки</strong><span class="red">*</span></td>
						<td><textarea name="adress"><?=$_POST['adress'];?></textarea></td>
					</tr>
					<tr class="blocy" style="display:none;">
						<td><strong>Когда доставить</strong><span class="red">*</span></td>
						<td>
							<?
							$APPLICATION->IncludeComponent(
								"bitrix:main.calendar",
								".default",
								array(
									"SHOW_INPUT" => "Y",
									"FORM_NAME" => "curform",
									"INPUT_NAME" => "date_deliv",
									"INPUT_NAME_FINISH" => false,
									"INPUT_VALUE" => $_POST['date_deliv'],
									"INPUT_VALUE_FINISH" => false,
									"SHOW_TIME" => "N",
									"HIDE_TIMEBAR" => "Y",
									"INPUT_ADDITIONAL_ATTR" => 'placeholder="ДД.ММ.ГГГГ" class="small_inp date"'
								),
								false
							);
							?> 
                            <select name="TIME_PERIOD" size="1" class="short">
                            <?
							foreach ($arResult['time_periods'] as $k => $v)
							{
								?>
                                <option value="<?=$k;?>"<?=($_POST['TIME_PERIOD'] == $k) ? ' selected' : ' ';?>><?=$v;?></option>
                                <?
							}
							?>
                            </select>
						</td>
					</tr>
					<tr>
						<td><strong>Срочный заказ</strong>&nbsp;<sup><a href="javascript:void(0);" class="help" title="<?=GetMessage("HELP_3");?>">?</a></sup></td>
						<td><input type="checkbox" name="urgent" value="2" onChange="CalculateCostOfDelivery();" id="urgent" <? echo ($_POST['urgent'] == 2) ? ' checked' : ''; ?>></td>
					</tr>
					<tr>
						<td><strong>Доставка юр. лицу</strong>&nbsp;<sup><a href="javascript:void(0);" class="help" title="<?=GetMessage("HELP_4");?>">?</a></sup></td>
						<td><input type="checkbox" name="to_legal" value="1" <? echo ($_POST['to_legal'] == 1) ? ' checked' : ''; ?>></td>
					</tr>
					<tr>
						<td><strong>Комментарий к заказу</strong></td>
						<td><textarea name="time"><?=$_POST['time'];?></textarea></td>
					</tr>
				</tbody>
			</table>
        </div>
    </form>
    <?
}
?>