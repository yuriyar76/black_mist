<script type="text/javascript">
	function SelectRow(ch,ro)
	{
		if ($("#"+ch).is(":checked"))
		{
			$("#"+ro).addClass('CheckedRow');
		}
		else {
			$("#"+ro).removeClass('CheckedRow');
		}
	}

	function ShowGoods(idd)
	{
		$('.show_blocks').css('display','none');
		$('#show_block_'+idd).css('display','block');
		$('.show_links').html('показать');
		$('#link_'+idd).html('');
	}
</script>

<a href="javascript:void(0);" class="help" title="<?=GetMessage("GLOBAL_HELP");?>" style="display:block; position:absolute; top:10px; right:10px; width:50px; height:50px;">
	<img src="/bitrix/templates/portal/images/question.png" width="50" height="50">
</a>

<div class="new_menu">
    <ul>
        <?
        foreach ($arResult["MENU"] as $k => $v) {
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
	<form action="" method="get">
    <input type="hidden" name="mode" value="<?=$_GET['mode'];?>" />
    <input type="hidden" name="on_page" value="<?=$_GET['on_page'];?>" />
    <label for="shop">Интернет-магазин:</label> 
    <select name="shop" id="shop" size="1">
    	<option value="0">Все</option>
        <?
		foreach ($arResult['SHOPS'] as $v) {
			if ($_GET['shop'] == $v['ID']) 
				$s = ' selected';
			else 
				$s = '';
			?>
            <option value="<?=$v['ID'];?>"<?=$s;?>><?=$v['NAME'];?></option>
            <?
		}
		?>
    </select> 
    <input type="submit" name="" value="Фильтровать" />
    </form>
</div>

<?
if (count($arResult["LIST"]) > 0)
{
	$ids = array();
	foreach ($arResult["LIST"] as $k => $p)
	{
		$ids[] = $p['ID'];
	}
	?>
	<form action="" method="post">
		<input type="hidden" name="rand" value="<?=rand(100000,999999);?>" />
		<input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>" />
		<table width="100%" cellpadding="3" cellspacing="0" border="1" bordercolor="#CCCCCC" class="rows">
			<thead>
				<tr>
					<td width="10" align="center"><input type="checkbox" name="set" onclick="setCheckedNew(this,'id')" /></td>
					<td width="120">Номер заказа</td>
					<td width="120">Дата создания</td>
					<td width="250">Город назначения</td>
					<td width="40">Вес</td>
					<td width="30">Места</td>
					<td>Отправитель</td>
					<td>Товары</td>
					<td width="20">
						<a href="print.php?ids=<?=implode(',',$ids);?>" title="распечатать этикетки" target="_blank">
							<img src="/bitrix/components/black_mist/delivery.packages/templates/.default/images/print_icon.png" width="20" height="20">
						</a>
					</td>
					<td width="20"></td>
				</tr>
			</thead>
			<tbody>
			<?
			$arPods = array();
			foreach ($arResult["LIST"] as $p)
			{
				$arPods[] = $p['PROPERTY_N_ZAKAZ_IN_VALUE'];
				?>
				<tr id="row_<?=$p["ID"];?>" class="CheckedRows">
					<td align="center">
						<input type="checkbox" name="id[]" value="<?=$p['ID'];?>" onChange="SelectRow('check_<?=$p["ID"];?>','row_<?=$p["ID"];?>');" id="check_<?=$p["ID"];?>"/>
						<input type="hidden" name="number[<?=$p['ID'];?>]" value="<?=$p['PROPERTY_N_ZAKAZ_IN_VALUE'];?>" />
						<input type="hidden" name="shop_id[<?=$p['ID'];?>]" value="<?=$p['PROPERTY_CREATOR_VALUE'];?>" />
						<input type="hidden" name="city[<?=$p['ID'];?>]" value="<?=$p['PROPERTY_CITY_VALUE'];?>" />
						<input type="hidden" name="conditions[<?=$p['ID'];?>]" value="<?=$p['PROPERTY_CONDITIONS_ENUM_ID'];?>" />
                        <input type="hidden" name="goods_count[<?=$p['ID'];?>]" value="<?=count($p['GOODS_LIST']);?>" />
					</td>
					<td><a href="index.php?mode=package&id=<?=$p['ID'];?>&back_url=<?=$_GET['mode'];?>"><?=$p['PROPERTY_N_ZAKAZ_IN_VALUE'];?></a></td> 
					<td><?=substr($p['DATE_CREATE'],0,16);?></td>
					<td><?=$p['CITY_NAME'];?></td>
					<td nowrap="nowrap"><?=WeightFormat($p['PROPERTY_WEIGHT_VALUE']);?></td>
					<td align="center"><?=$p['PROPERTY_PLACES_VALUE'];?></td>
					<td><a href="/shops/index.php?mode=shop&id=<?=$p['PROPERTY_CREATOR_VALUE'];?>"><?=$p['PROPERTY_CREATOR_NAME'];?></a></td>
					<td width="30%">
						<a href="javascript:void(0);" id="link_<?=$p['ID'];?>" onclick="ShowGoods(<?=$p['ID'];?>);" class="show_links">показать</a>
						<div id="show_block_<?=$p['ID'];?>" style="display:none;" class="show_blocks">
						<?
						if (count($p['GOODS_LIST']) > 0)
						{
							?>
							<table cellpadding="0" cellspacing="0" border="0" class="good_table_in" width="100%">
								<thead>
									<tr>
										<td width="50%">Наименование</td>
										<td width="20%">Артикул</td>
										<td  width="15%">Количество</td>
										<td>Цена</td>
									</tr>
								</thead>
								<tbody>
								<?
								foreach ($p['GOODS_LIST'] as $k => $v)
								{
									?>
									<tr>
										<td><?=$v['NAME'];?></td>
										<td><?=$v['ARTICLE'];?></td>
										<td><?=$v['COUNT'];?> шт.</td>
										<td><?=CurrencyFormat($v['COST']);?></td>
									</tr>
									<?
								}
								?>
								</tbody>
							</table>
							<?
						}
						?>
						</div>
					</td>
					<td>
						<a href="/warehouse/index.php?mode=print_labels&ids=<?=$p['ID'];?>&print=Y" title="распечатать этикетку" target="_blank">
							<img src="/bitrix/components/black_mist/delivery.packages/templates/.default/images/print_icon.png" width="20" height="20">
						</a>
					</td>
					<td>
						<a href="/warehouse/index.php?mode=package_print&id=<?=$p['ID'];?>&pdf=Y" title="Распечатать квитанцию" target="_blank">
							<img src="/bitrix/components/black_mist/delivery.management/templates/.default/images/PDF-icon-20.png" width="20" height="20">
						</a>
					</td>
				</tr>
				<?
			}
			?>
			</tbody>
		</table>
	
		<input type="submit" name="submit_for_delivery" value="Заказ сформирован"/>
	</form>
    <p><a href="http://express-russia.ru/app/tracking.php?f001=<?=implode(', ', $arPods);?>" target="_blank"><?=GetMessage('LINK_TO_PODS');?></a></p>	
    <?
	$APPLICATION->IncludeComponent(
		'black_mist:delivery.pagination',
		'',
		array(
			'PAGE' => $APPLICATION->GetCurUri(),
			'HID_FIELDS' => array(
				'mode' => $_GET['mode'],
				'shop' => $_GET['shop'],
			),
			'NAV_STRING' => $arResult['NAV_STRING']
		),
		false
	);
}
else
{
	?>
	Заказы на формировании отсутствуют
	<?
}
?>
<br>