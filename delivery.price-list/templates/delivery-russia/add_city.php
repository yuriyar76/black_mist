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

?>
<form action="" method="post">
<input type="hidden" name="min_index" value="<?=$arResult["HTML"]["MIN"];?>">
<input type="hidden" name="max_index" value="<?=$arResult["HTML"]["MAX"];?>">
<input type="hidden" name="start_index" value="<?=$arResult["HTML"]["START_INDEX"];?>">
<input type="hidden" name="start_value" value="<?=$arResult["HTML"]["START_VALUE"];?>">
<?
foreach ($arResult["HTML"]["WEIGHT"] as $key => $value) echo '
<input type="hidden" name="weight['.$key.']" value="'.$value.'">';
foreach ($arResult["HTML"]["DOCS"] as $key => $value) echo '
<input type="hidden" name="docs['.$key.']" value="'.$value.'">';
?>

<table width="100%" cellpadding="3" cellspacing="0" border="1" style="border-collapse:collapse;" class="price_table" bordercolor="#ccc"><thead>
<tr align="center"><td rowspan="3">Город</td><td colspan="<?=($arResult['size_types']*($arResult["NUMKG"]+$arResult["NUMDOC"]+3));?>">Типы доставки</td></tr>
<tr align="center">
<?
for ($i=0;$i<$arResult['size_types'];$i++)
	echo '<td colspan="'.($arResult["NUMKG"]+$arResult["NUMDOC"]+3).'">'.$arResult['global_types'][$i].'</td>';
			echo '</tr>
			<tr align="center" class="verty">';
			for ($i=0;$i<$arResult['size_types'];$i++) {
				echo '<td>Мин. срок доставки</td><td>Макс. срок доставки</td>';
				foreach ($arResult["HTML"]["DOCS"] as $key => $value) echo '<td>До '.$arResult["HTML"]["DOCS"][$key].'кг</td>';
				echo '<td>Начальный вес ('.$arResult["HTML"]["START_VALUE"].'кг)</td>';
				foreach ($arResult["HTML"]["WEIGHT"] as $key => $value) echo '<td>Свыше '.$arResult["HTML"]["WEIGHT"][$key].'кг</td>';
				
			}
?>
</tr>
</thead><tbody>
<tr align="center"><td align="left"><input type="text" size="40" name="city0" id="city"></td>
<?
for ($j=0;$j<$arResult['size_types'];$j++) {
	$name_type = $arResult['global_types'][$j];
	echo '<td><input type="text" size="3" value="" name="result['.$key.']['.$name_type.']['.$arResult["HTML"]["MIN"].']"></td>
	<td><input type="text" size="3" value="" name="result['.$key.']['.$name_type.']['.$arResult["HTML"]["MAX"].']"></td>
					';
foreach ($arResult["HTML"]["DOCS"] as $key2 => $value) echo '<td><input type="text" size="3" value="" name="result['.$key.']['.$name_type.']['.$key2.']"></td>';
					echo '<td><input type="text" size="3" value="" name="result['.$key.']['.$name_type.']['.$arResult["HTML"]["START_INDEX"].']"></td>';
					foreach ($arResult["HTML"]["WEIGHT"] as $key2 => $value) echo '<td><input type="text" size="3" value="" name="result['.$key.']['.$name_type.']['.$key2.']"></td>';
					
				}
				echo '</tr>';
			
			foreach ($arResult["HTML"]["CITIES"] as $key => $value) {
				echo '
				<tr align="center"><td align="left">'.$value.'</td>';
				for ($j=0;$j<$arResult['size_types'];$j++) {
					$name_type = $arResult['global_types'][$j];
					echo '<td>'.$arResult["HTML"]["ORDERS"][$key][$name_type][$arResult["HTML"]["MIN"]].'</td>
					<td>'.$arResult["HTML"]["ORDERS"][$key][$name_type][$arResult["HTML"]["MAX"]].'</td>
					';
					foreach ($arResult["HTML"]["DOCS"] as $key2 => $value) echo '<td>'.$arResult["HTML"]["ORDERS"][$key][$name_type][$key2].'</td>';
					echo '<td>'.$arResult["HTML"]["ORDERS"][$key][$name_type][$arResult["HTML"]["START_INDEX"]].'</td>';
					foreach ($arResult["HTML"]["WEIGHT"] as $key2 => $value) echo '<td>'.$arResult["HTML"]["ORDERS"][$key][$name_type][$key2].'</td>';
					
				}
				echo '</tr>';
			}
?>


<tbody></table>
<br>
<input type="submit" value=" " name="add_c" class="save">
</form>