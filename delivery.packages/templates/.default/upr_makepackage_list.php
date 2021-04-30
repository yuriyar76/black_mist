<script type="text/javascript">
	function SelectRow(ch,ro)
	{
		if ($("#"+ch).is(":checked")) {
			$("#"+ro).addClass('CheckedRow');
		}
		else
		{
			$("#"+ro).removeClass('CheckedRow');
		}
	}
</script>

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

if (count($arResult["LIST"]) > 0)
{
	?>
	<form action="" method="post">
		<input type="hidden" name="rand" value="<?=rand(100000,999999);?>" />
		<input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>" />
		<table width="100%" cellpadding="3" cellspacing="0" border="1" bordercolor="#CCCCCC" class="rows">
        	<thead>
            	<tr>
					<td width="20"><input type="checkbox" name="set" onclick="setCheckedNew(this,'ids')" /></td>
					<td><?=GetMessage("TABLE_HEAD_1");?></td>
                    <td><?=GetMessage("TABLE_HEAD_2");?></td>
					<td><?=GetMessage("TABLE_HEAD_3");?></td>
                    <td><?=GetMessage("TABLE_HEAD_4");?></td>
                    <td><?=GetMessage("TABLE_HEAD_5");?></td>
                    <td><?=GetMessage("TABLE_HEAD_6");?></td>
                    <td><?=GetMessage("TABLE_HEAD_7");?></td>
                    <td><?=GetMessage("TABLE_HEAD_8");?></td>
					<td><?=GetMessage("TABLE_HEAD_9");?></td>
                    <td><?=GetMessage("TABLE_HEAD_10");?></td>
				</tr>
			</thead>
            <tbody>
				<?
				foreach ($arResult["LIST"] as $p)
				{
					?>
					<tr id="row_<?=$p["ID"];?>" class="CheckedRows">
						<td>
							<?
							if ($p["CREATED_BY"] == $arResult['CURRENT_USER'])
							{
								?>
								<input type="checkbox" name="ids[]" value="<?=$p['ID'];?>" onChange="SelectRow('check_<?=$p["ID"];?>','row_<?=$p["ID"];?>');" id="check_<?=$p["ID"];?>">
								<?
							}
							?>
						</td>
                        <td><a href="index.php?mode=makepackage&id=<?=$p['ID'];?>&back_url=<?=$_GET['mode'];?>"><?=$p['PROPERTY_N_ZAKAZ_IN_VALUE'];?></a></td>
						<td><?=$p['DATE_CREATE'];?></td>
						<td><?=$p['CITY_NAME'];?></td>
                        <td nowrap><?=WeightFormat($p['PROPERTY_WEIGHT_VALUE']);?></td>
						<td nowrap><?=$p['GOODS'];?></td>
						<td nowrap><?=CurrencyFormat($p["PROPERTY_COST_GOODS_VALUE"],"RUU")?></td>
						<td nowrap><?=CurrencyFormat($p["PROPERTY_COST_2_VALUE"],"RUU");?></td>
                        <td align="center"><?=$p['PROPERTY_PLACES_VALUE'];?></td>
                        <td><a href="/shops/index.php?mode=shop&id=<?=$p['PROPERTY_CREATOR_VALUE'];?>"><?=$p['PROPERTY_CREATOR_NAME'];?></a></td>
						<td><?=$p["CREATED_BY_COMPANY"];?>, <?=$p["CREATED_BY_NAME"];?> [<?=$p["CREATED_BY"];?>]</td>
					</tr>
					<?
				}
				?>
			</tbody>
		</table>
		
        <input type="submit" name="delete" value="<?=GetMessage("DELETE_BTN");?>">
	</form>
    
    <?
	$APPLICATION->IncludeComponent(
		"black_mist:delivery.pagination",
		".default",
		array(
			"PAGE" => $APPLICATION->GetCurPageParam("", array()),
			"HID_FIELDS" => array(
				"mode" => $_GET['mode']
			),
			"NAV_STRING" => $arResult["NAV_STRING"]
		), 
		false
	);
}
else
{
	echo GetMessage("ORDERS_NOT");
}
?>
<br>