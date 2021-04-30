<script type="text/javascript">
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
</script>

<a href="javascript:void(0);" class="help" title="<?=GetMessage("GLOBAL_HELP");?>" style="display:block; position:absolute; top:10px; right:10px; width:50px; height:50px;">
	<img src="/bitrix/templates/portal/images/question.png" width="50" height="50">
</a>

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
                    <td width="20"><input type="checkbox" name="set" onclick="setCheckedNew(this,'pack_id')" /></td>
                    <td><?=GetMessage("TABLE_HEAD_1");?></td>
                    <td><?=GetMessage("TABLE_HEAD_2");?></td>
                    <td><?=GetMessage("TABLE_HEAD_3");?></td>
                    <td><?=GetMessage("TABLE_HEAD_4");?></td>
                    <td><?=GetMessage("TABLE_HEAD_5");?></td>
                    <td><?=GetMessage("TABLE_HEAD_6");?></td>
                    <td><?=GetMessage("TABLE_HEAD_7");?></td>
                    <td><?=GetMessage("TABLE_HEAD_8");?></td>
                    <td><?=GetMessage("TABLE_HEAD_9");?></td>
                    <td width="20">
                    	<a href="print.php?ids=<?=implode(',',$ids);?>" title="<?=GetMessage("PRINT_LABELS");?>" target="_blank">
                        	<img src="/bitrix/components/black_mist/delivery.packages/templates/.default/images/print_icon.png" width="20" height="20">
						</a>
					</td>
					<td width="20"></td>
				</tr>
			</thead>
            <tbody>
				<?
				foreach ($arResult["LIST"] as $p)
				{
					?>
					<tr id="row_<?=$p["ID"];?>" class="CheckedRows">
						<td align="center">
							<input type="checkbox" name="pack_id[]" value="<?=$p['ID'];?>" <? echo (in_array($p['ID'],$_POST['pack_id'])) ? 'checked' : ''; ?> 
                            	onChange="SelectRow('check_<?=$p["ID"];?>','row_<?=$p["ID"];?>');" id="check_<?=$p["ID"];?>">
						</td>
                    <td>
						<a href="index.php?mode=package&id=<?=$p['ID'];?>&back_url=<?=$_GET['mode'];?>">
							<?=$p['PROPERTY_N_ZAKAZ_IN_VALUE'];?>
						</a>
					</td>
                    <td><?=$p['PROPERTY_N_ZAKAZ_VALUE'];?></td>
                    <td><?=$p['PROPERTY_DATE_TO_DELIVERY_VALUE'];?></td>
                    <td><?=$p['PROPERTY_CITY_NAME'];?></td>
                    <td align="center"><?=WeightFormat($p['PROPERTY_WEIGHT_VALUE']);?></td>
					<td align="center"><?=$p['PROPERTY_PLACES_VALUE'];?></td>
                    <td><?=CurrencyFormat($p['PROPERTY_SUMM_SHOP_VALUE'],"RUU");?></td>
                    <td><a href="/shops/index.php?mode=shop&id=<?=$p['PROPERTY_CREATOR_VALUE'];?>"><?=$p['PROPERTY_CREATOR_NAME'];?></a></td>
					<td><? echo (($p['PROPERTY_CONDITIONS_ENUM_ID'] == 38) && (intval($p['PROPERTY_PVZ_VALUE']) > 0)) ? GetMessage("PVZ").' '.$p['PVZ_NAME'] : $p['PROPERTY_CONDITIONS_VALUE']; ?></td>
                    <td>
                    	<a href="print.php?ids=<?=$p['ID'];?>" title="<?=GetMessage("PRINT_LABEL");?>" target="_blank">
                        	<img src="/bitrix/components/black_mist/delivery.packages/templates/.default/images/print_icon.png" width="20" height="20">
						</a>
					</td>
					<td>
                    	<a href="/warehouse/index.php?mode=package_print&id=<?=$p['ID'];?>&pdf=Y" title="<?=GetMessage("PRINT");?>" target="_blank">
                        	<img src="/bitrix/components/black_mist/delivery.management/templates/.default/images/PDF-icon-20.png" width="20" height="20">
						</a>
					</td>
				</tr>
				<?
			}
			?>
			</tbody>
		</table>
		<input type="submit" name="send" value="<?=GetMessage("ADD_BTN");?>" />
	</form>
	<?
	$APPLICATION->IncludeComponent(
		"black_mist:delivery.pagination",
		"",
		array(
			"PAGE" => $APPLICATION->GetCurPageParam("", array()),
			"HID_FIELDS" => array(
				"mode" => $_GET['mode'],
				"city_f" => $_GET["city_f"],
				"shop_f" => $_GET["shop_f"]
			),
			"NAV_STRING" => $arResult["NAV_STRING"]
		),
		false
	);
}
else
{
	echo GetMessage("ORDERS_NOT");
	if ($USER->IsAdmin()) 
	{
		?>
		<form action="" method="post">
            <input type="hidden" name="rand" value="<?=rand(100000,999999);?>" />
            <input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>" />
            <input type="submit" name="send" value="<?=GetMessage("ADD_BTN");?>" />
		</form>
    	<?
	}
}
?>
<br>