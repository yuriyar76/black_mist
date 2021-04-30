<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
?>
<script type="text/javascript" src="/bitrix/templates/newpartner/js/jquery.autocomplete.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		function liFormat (row, i, num)
		{
			var result = row[0];
			return result;
		}
		function selectItem(li)
		{
			if( li == null ) var sValue = 'А ничего не выбрано!';
			if( !!li.extra ) var sValue = li.extra[2];
			else var sValue = li.selectValue;
			//alert("Выбрана запись с ID: " + sValue);
		}
		$("#autoCITY_SENDER").autocomplete("/autocomplete.php", {
			delay:0,
			minChars:2,
			matchSubset:1,
			autoFill:false,
			matchContains:1,
			cacheLength:0,
			selectFirst:true,
			formatItem:liFormat,
			maxItemsToShow:50,
			onItemSelect:selectItem
		});
		$("#autocity_recipient").autocomplete("/autocomplete.php", {
			delay:0,
			minChars:2,
			matchSubset:1,
			autoFill:false,
			matchContains:1,
			cacheLength:0,
			selectFirst:true,
			formatItem:liFormat,
			maxItemsToShow:50,
			onItemSelect:selectItem
		});

	});
</script>
<h2 class="partner"><?=$arResult['TITLE'];?></h2>
<?
if (count($arResult["ERRORS"]) > 0) 
{
	echo '<p class="red">'.implode('</br>',$arResult["ERRORS"]).'</p>';
}
if (count($arResult["MESSAGE"]) > 0)
{
	echo '<p class="green">'.implode('</br>',$arResult["MESSAGE"]).'</p>';
}
if (count($arResult["WARNINGS"]) > 0)
{
	echo '<p class="orange">'.implode('</br>',$arResult["WARNINGS"]).'</p>';
}
if ($arResult['OPEN']) 
{
	?>
	<form action="" method="post" name="curform">
		<input type="hidden" name="rand" value="<?=rand(100000,999999);?>">
		<input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>">
		<table width="960" cellpadding="5" cellspacing="0" border="1" bordercolor="#CCCCCC" class="invoice">
			<tbody>
				<tr>
					<td rowspan="5" class="vertical_td" width="50">Отправитель</td>
					<td width="300">
						<label for="NAME_SENDER">ФИО отправителя</label>
						<input type="text" name="NAME_SENDER" value="<?=$_POST['NAME_SENDER'];?>" placeholder="ФИО отправителя" id="NAME_SENDER" tabindex="1">
					</td>
					<td rowspan="2" width="250">
						<label for="PHONE_SENDER">Телефон</label>
						<textarea name="PHONE_SENDER" placeholder="Телефон" tabindex="2"><?=$_POST['PHONE_SENDER'];?></textarea>
					</td>
					<td rowspan="9" width="5">&nbsp;</td>
					<td rowspan="2">
						<label for="date_request">Дата и временной интервал забора</label>
						<?
						$APPLICATION->IncludeComponent(
							"bitrix:main.calendar",
							".default",
							array(
								"SHOW_INPUT" => "Y",
								"FORM_NAME" => "curform",
								"INPUT_NAME" => "date_request",
								"INPUT_NAME_FINISH" => "",
								"INPUT_VALUE" => $_POST['date_request'],
								"INPUT_VALUE_FINISH" => false,
								"SHOW_TIME" => "N",
								"HIDE_TIMEBAR" => "Y",
								"INPUT_ADDITIONAL_ATTR" => 'placeholder="ДД.ММ.ГГГГ" pattern="[0-9]{2}.[0-9]{2}.[0-9]{4}" class="date" tabindex="13" id="date_request"'
							),
							false
						);
						?>
						<br><br>
						<input type="text" name="time_start" id="time_start" placeholder="ЧЧ:ММ" pattern="[0-9]{2}:[0-9]{2}" tabindex="14" class="small"> - 
						<input type="text" name="time_end" id="time_end" placeholder="ЧЧ:ММ" pattern="[0-9]{2}:[0-9]{2}" tabindex="15" class="small"> 
					</td>
				</tr>
				<tr>
					<td>
						<label for="">Компания-отправитель</label>
						<input type="text" name="COMPANY_SENDER" value="<?=$_POST['COMPANY_SENDER'];?>" placeholder="Компания-отправитель" tabindex="3">
					</td>
				</tr>
				<tr>
					<td>
						<label for="">Город</label>
						<input type="text" name="CITY_SENDER" value="<?=$_POST['CITY_SENDER'];?>" placeholder="Город" tabindex="4" id="autoCITY_SENDER">
					</td>
					<td>
						<label for="">Индекс</label>
						<input type="text" name="INDEX_SENDER" value="<?=$_POST['INDEX_SENDER'];?>" placeholder="123456" pattern="[0-9]{6}" tabindex="5">
					</td>
					<td rowspan="2">
						<label for="type_233"><input type="radio" name="TYPE" value="233" id="type_233" <?=($_POST['TYPE'] == 233) ? 'checked' : '';?> tabindex="16"> Документы</label>
						<label for="type_234"><input type="radio" name="TYPE" value="234" id="type_234" <?=($_POST['TYPE'] == 234) ? 'checked' : '';?> tabindex="17"> Не документы</label>
						<label for="type_235"><input type="radio" name="TYPE" value="235" id="type_235" <?=($_POST['TYPE'] == 235) ? 'checked' : '';?> tabindex="18"> Опасный груз</label>
					</td>
				</tr>
				<tr>
					<td rowspan="2" colspan="2">
						<label for="">Адрес</label>
						<textarea name="ADRESS_SENDER" placeholder="Адрес" tabindex="6"><?=$_POST['ADRESS_SENDER'];?></textarea>
					</td>
				</tr>
				<tr>
					<td rowspan="2">
						<label for="places">Количество мест</label>
						<input type="text" name="places" value="<?=$_POST['places'];?>" placeholder="0" pattern="[0-9]{0,2}" tabindex="19" class="small" id="places">
					</td>
				</tr>
				<tr>
					<td rowspan="4" class="vertical_td">Получатель</td>
					<td>
						<label for="">ФИО получателя</label>
						<input type="text" name="name_recipient" value="<?=$_POST['name_recipient'];?>" placeholder="ФИО получателя" tabindex="7">
					</td>
					<td rowspan="2">
						<label for="">Телефон</label>
						<textarea name="phone_recipient" placeholder="Телефон" tabindex="8"><?=$_POST['phone_recipient'];?></textarea>
					</td>
				</tr>
				<tr>
					<td>
						<label for="">Компания-получатель</label>
						<input type="text" name="company_recipient" value="<?=$_POST['company_recipient'];?>" placeholder="Компания-получатель" tabindex="9">
					</td>
					<td>
						<label for="">Вес</label>
						<input type="text" name="weight" value="<?=$_POST['weight'];?>" placeholder="0,00" pattern="\d+(,\d{2})?" tabindex="20" class="small">
					</td>
				</tr>
				<tr>
					<td>
						<label for="">Город</label>
						<input type="text" name="city_recipient" value="<?=$_POST['city_recipient'];?>" placeholder="Город" tabindex="10" id="autocity_recipient">
					</td>
					<td>
						<label for="">Индекс</label>
						<input type="text" name="index_recipient" value="<?=$_POST['index_recipient'];?>" placeholder="123456" pattern="[0-9]{6}" tabindex="11">
					</td>
					<td>
						<label for="">Габариты</label>
						<input type="text" name="size_1" value="<?=$_POST['size_1'];?>" placeholder="0,00" pattern="\d+(,\d{2})?" tabindex="21" class="extrasmall"> x
						<input type="text" name="size_2" value="<?=$_POST['size_2'];?>" placeholder="0,00" pattern="\d+(,\d{2})?" tabindex="22" class="extrasmall"> x
						<input type="text" name="size_3" value="<?=$_POST['size_3'];?>" placeholder="0,00" pattern="\d+(,\d{2})?" tabindex="23" class="extrasmall"> см
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<label for="">Адрес</label>
						<textarea name="adress_recipient" placeholder="Адрес" tabindex="12"><?=$_POST['adress_recipient'];?></textarea>
					</td>
					<td>
						<label for="">Спец. инструкции</label>
						<textarea name="instructions" placeholder="Спец. инструкции" tabindex="24"><?=$_POST['instructions'];?></textarea>
					</td>
				</tr>
			</tbody>
		</table>
		<br>
		<input type="submit" name="add" value="Создать">
	</form>
	<?
}
?>