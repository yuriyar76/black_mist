<script type="text/javascript">
function SelectRow(ch,ro) {
	if ($("#"+ch).is(":checked")) {
		$("#"+ro).addClass('CheckedRow');
    }
	else {
		$("#"+ro).removeClass('CheckedRow');
	}
}

</script>
<div class="new_menu">
    <ul>
        <?
        foreach ($arResult["MENU"] as $k => $v)
		{
			$s = ($arResult['MODE'] == $k) ? ' class="active"' : '';
            ?>
            <li<?=$s;?>><a href="index.php?mode=<?=$k?>"><?=$v?></a></li>
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
else {
	if (count($arResult["WARNINGS"]) > 0) 
	echo '
		<p class="orange">'.implode('</br>',$arResult["WARNINGS"]).'</p>';
}
?>

<div class="pagination">
    <form action="" method="get" name="curform">
        Тип доставки: <select name="type_delivery" size="1">
        <option value="0">Все</option>
        <option value="37" <? echo ($_GET['type_delivery'] == 37)? ' selected' : '';?>>По адресу</option>
        <option value="38" <? echo ($_GET['type_delivery'] == 38)? ' selected' : '';?>>Самовывоз</option>
        </select>
        <input type="submit" name="" value="фильтровать" />
    </form>
</div>

<?
if (count($arResult['LIST']) > 0)
{
	?>
    <form action="" method="post" name="curform">
    <table width="100%" cellpadding="3" cellspacing="0" border="1" bordercolor="#ccc" class="rows invoice_table"><thead>
    <tr>
    	<td width="20"><input type="checkbox" name="set" onclick="setCheckedNew(this,'packs')" /></td>
    	<td>Номер заказа</td>
    	<td>Стоимость</td>
        <td>Получатель</td><td>Номер телефона</td>
        <td>Доставить</td>
        <td>Адрес</td>
        <td nowrap width="260">Выдать курьеру / Переместить в ПВЗ</td>
        <!-- <td>Комментарий курьеру</td> -->
        <td width="20"></td>
    </tr>
    </thead><tbody>
    <?
    foreach ($arResult['LIST'] as $pack) {
		?>
        <tr id="row_<?=$pack["ID"];?>" class="CheckedRows">
        	<td><input type="checkbox" name="packs[]" value="<?=$pack['ID'];?>" onChange="SelectRow('check_<?=$pack['ID'];?>','row_<?=$pack['ID'];?>');" id="check_<?=$pack['ID'];?>"/></td>
            <td><a href="index.php?mode=package&id=<?=$pack['ID'];?>&back_url=<?=$_GET['mode'];?>"><?=$pack['PROPERTY_N_ZAKAZ_IN_VALUE'];?></a></td>
            <td><?=CurrencyFormat($pack['PROPERTY_COST_2_VALUE'],"RUU");?></td>
            <td><?=$pack['PROPERTY_RECIPIENT_VALUE'];?></td>
            <td><?=$pack['PROPERTY_PHONE_VALUE'];?></td>
            <td><?=$pack['PROPERTY_WHEN_TO_DELIVER_VALUE'];?></td>
            <td>
			<?
        	if ($pack['PROPERTY_CONDITIONS_ENUM_ID'] == 37) {
				$deli = $pack['PROPERTY_ADRESS_VALUE'];
				$show_pvz = true;
			}
			else {
				$show_pvz = false;
				if ($pack['PVZ']) {
					$deli = $pack['PVZ'];
				}
				else {
					$deli = $pack['PROPERTY_CONDITIONS_VALUE'];
				}
			}
			echo $deli;
			?>
            <input type="hidden" name="old_adress[<?=$pack['ID'];?>]" value="<?=$deli;?>" />
            <input type="hidden" name="delivery_type[<?=$pack['ID'];?>]" value="<?=$pack['PROPERTY_CONDITIONS_ENUM_ID'];?>" />
            <input type="hidden" name="fio[<?=$pack['ID'];?>]" value="<?=$pack['PROPERTY_RECIPIENT_VALUE'];?>">
            <input type="hidden" name="summ[<?=$pack['ID'];?>]" value="<?=$pack["PROPERTY_COST_2_VALUE"];?>">
            <input type="hidden" name="id_in[<?=$pack['ID'];?>]" value="<?=$pack['PROPERTY_N_ZAKAZ_IN_VALUE'];?>" />
            <input type="hidden" name="shop[<?=$pack['ID'];?>]" value="<?=$pack["PROPERTY_CREATOR_VALUE"];?>" />
            </td>
            <td>
			<?
            if (($pack['PROPERTY_CONDITIONS_ENUM_ID'] == 37) && count($arResult["COURIERS"]) > 0) {
				?>
                <select name="cur_new[<?=$pack['ID'];?>]" size="1" style="width:260px;">
                	<option value="0"></option>
					<?
                    foreach ($arResult["COURIERS"] as $c) {
						?>
                        <option value="<?=$c["ID"];?>"><?=$c["NAME"];?></option>
						<? 
					}
					?>
                </select>
				<?
            }
			if (($pack['PROPERTY_CONDITIONS_ENUM_ID'] == 38) && count($arResult["PVZ_LIST"] > 0)) {
				?>
                <select name="pvz_new[<?=$pack['ID'];?>]" size="1" style="width:260px;">
                	<option value="0"></option>
					<?
					foreach ($arResult["PVZ_LIST"] as $p) {
						?>
                        <option value="<?=$p["ID"];?>"><? echo $p["NAME"].', '.$p['PROPERTY_CITY_NAME'].', '.$p['PROPERTY_ADRESS_VALUE']; echo (strlen($p['CODE'])) ? ' ['.$p['CODE'].']' : '';?></option>
						<?
                    }
					?>
                </select>
				<?
            }
			?>
            </td>
            <!-- <td width="250"><textarea name="comment[<?=$pack['ID'];?>]" style="width:250px; height:30px;"><? echo (strlen($_POST['comment'][$pack['ID']])) ? $_POST['comment'][$pack['ID']] : $pack['PROPERTY_COMMENTS_COURIER_VALUE']['TEXT']; ?></textarea></td> -->
            <td>
            <a href="/warehouse/index.php?mode=package_print&id=<?=$pack['ID'];?>&pdf=Y" title="Распечатать квитанцию" target="_blank"><img src="/bitrix/components/black_mist/delivery.management/templates/.default/images/PDF-icon-20.png" width="20" height="20"></a>
            </td>
        </tr>
		<?
    }
	?>
    </tbody></table>
	<?
    foreach ($arResult["COURIERS"] as $v) {
		?>
        <input type="hidden" name="c_name[<?=$v["ID"];?>]" value="<?=$v["NAME"];?>">
		<?
    }
	foreach ($arResult["PVZ_LIST"] as $v) {
		?>
        <input type="hidden" name="pvz_name[<?=$v["ID"];?>]" value="<?=$v["NAME"];?>">
		<?
    }
	?>
    <div class="block_3">
    	Выдать курьеру: 
    	<select name="to_cur" size="1">
        	<option value="0"></option>
        	<?
            foreach ($arResult["COURIERS"] as $v) {
				?>
                <option type="hidden" value="<?=$v["ID"];?>"><?=$v["NAME"];?></option>
			<?
         }
		 ?>
        </select>
    </div>
    <div class="block_3">
    	Переместить в ПВЗ: 
    	<select name="to_pvz" size="1">
        	<option value="0"></option>
        	<?
            foreach ($arResult["PVZ_LIST"] as $v) {
				?>
                <option type="hidden" value="<?=$v["ID"];?>"><?=$v["NAME"];?></option>
			<?
         }
		 ?>
        </select>
    </div>
    <br class="clear">
    <input type="submit" name="save" value="Сохранить"/>
    </form>
    
    <div class="pagination">
        <div class="left_pag">
            <form action="<?=$APPLICATION->GetCurPageParam("", array());?>" method="get">
                <input type="hidden" name="mode" value="<?=$_GET['mode'];?>">
                Показывать на одной странице: 
                <select name="on_page" size="1">
                    <option value="10"<? echo ($_GET['on_page'] == 10) ? ' selected': ''; ?>>10</option>
                    <option value="20"<? echo ($_GET['on_page'] == 20) ? ' selected': ''; ?>>20</option>
                    <option value="50"<? echo ($_GET['on_page'] == 50) ? ' selected': ''; ?>>50</option>
                    <option value="100"<? echo ($_GET['on_page'] == 100) ? ' selected': ''; ?>>100</option>
                    <option value="500"<? echo ($_GET['on_page'] == 500) ? ' selected': ''; ?>>500</option>
                </select>
                <input type="submit" name="" value="Применить">
            </form>
        </div>
        <div class="right_pag">
            <?=$arResult["NAV_STRING"];?>
        </div>
        <br class="clear">
    </div>
	<?
}
else {
	?>
    <p>Заказы отсутствуют</p>
	<?
}
?>