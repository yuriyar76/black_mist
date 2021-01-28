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
<input type="hidden" value="<?=$arResult["HTML"]["MAX_CODE"];?>" name="max_kod">
<input type="hidden" value="<?=$arResult["HTML"]["START_INDEX"];?>" name="start_index">
<table cellpadding="3" cellspacing="0" border="0"><tbody>
<tr><td>Наименование прайс-листа:</td><td colspan="3"><input type="text" name="user_n_file" value="<?=$arResult["HTML"]["NAME"];?>"  class="inp2"></td></tr>
<tr><td>Начальный вес:</td><td><input type="text" class="inp" value="<?=$arResult["HTML"]["START_VALUE"];?>" name="start_value"></td><td colspan="2">кг</td></tr>
<tr><td colspan="4"><strong>Градации по весу "Свыше":</strong></td></tr>
<?
	foreach($arResult["HTML"]["WEIGHT"]  as $k => $v) {
		echo '
		<tr><td>Свыше</td><td><input type="text" value="'.$v.'" class="inp" name="weight_value_'.$k.'"></td><td>кг</td><td>
		<input type="checkbox" name="edit_yes[]" value="'.$k.'" id="edit_value_'.$k.'"> <label for="edit_value_'.$k.'">изменить</label>
		<input type="checkbox" name="delete_yes[]" value="'.$k.'" id="weight_value_'.$k.'"> <label for="weight_value_'.$k.'">удалить</label></td></tr>';
	}
	if ((intval($_POST['add_kg']) > 0) and (intval($_POST['add_kg']) != $arResult['result_add_kg'])) echo $arResult['msg1'].'<input type="hidden" value="'.intval($_POST['add_kg']).'" name="add_kg">';
	else {
		if (intval($_POST['add_kg']) == $arResult['result_add_kg']) $r_w = 0; else $r_w = intval($_POST['add_kg']);
		echo '<tr><td>Добавить</td><td><input type="text" class="inp" value="'.$r_w.'" name="add_kg"></td><td colspan="2">градаций по весу "Cвыше"</td></tr>';
	}
?>
<tr><td colspan="4"><strong>Градации по весу "До":</strong></td></tr>
<?
foreach($arResult["HTML"]["DOCS"] as $k => $v) {
?>
		<tr><td>До</td><td><input type="text" value="<?=$v;?>" class="inp" name="weight_value_<?=$k;?>"></td><td>кг</td><td>
		<input type="checkbox" name="edit_yes[]" value="<?=$k;?>" id="edit_value_<?=$k;?>"> <label for="edit_value_<?=$k;?>">изменить</label>
		<input type="checkbox" name="delete_yes[]" value="<?=$k;?>" id="docs_value_<?=$k;?>"> <label for="docs_value_<?=$k;?>">удалить</label></td></tr>
<?
	}
	if ((intval($_POST['add_doc']) > 0) and (intval($_POST['add_doc']) != $arResult['result_add_docs'])) echo $arResult['msg2'].'<input type="hidden" value="'.intval($_POST['add_doc']).'" name="add_doc">';
	else {
		if (intval($_POST['add_doc']) == $arResult['result_add_docs']) $r_d = 0; else $r_d = intval($_POST['add_doc']);
		?>
        <tr><td>Добавить</td><td><input type="text" class="inp" value="<?=$r_d;?>" name="add_doc"></td><td colspan="2">градаций по весу "До" (спеццены)</td></tr>
        <?
	}
?>
</tbody></table>
<input type="submit" value=" " class="save" name="save">
</form>