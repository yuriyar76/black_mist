<script type="text/javascript">
	function GetPVZsOfAgent(agent_id)
	{
		var content = '<option value="0"></option>';
		if (agent_id == '0')
		{
			$('#pvz').html(content);
		}
		else
		{
			$.get('/search_city.php', {agent_pvzs: 'true', agent_id: agent_id}, function(data)
			{
				data = $.parseJSON(data);
				var m = parseInt(data.length);
				var st = '';
				if (m == 1) st = ' selected';
				$.each(data,function(key,val)
				{
					content = content + '<option'+st +' value="'+val.id+'">'+val.name+', '+val.city+', '+val.adress+' ['+val.code+']</option>';
				});
				$('#pvz').html(content);
			})
		}
	}
	
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

if (count($arResult["MESSAGE"]) > 0)  {
	echo '
		<p class="green">'.implode('</br>',$arResult["MESSAGE"]).'</p>';
	$_POST = array();
}
else {
	if (count($arResult["WARNINGS"]) > 0) 
	echo '
		<p class="orange">'.implode('</br>',$arResult["WARNINGS"]).'</p>';
}

?>
<div class="pagination">
	<form action="index.php" method="get">
		<input type="hidden" name="mode" value="<?=$_GET['mode'];?>" />
		<input type="hidden" name="on_page" value="<?=$_GET['on_page'];?>" />
		<label for="city_f"><?=GetMessage("LABEL_CITY");?></label> 
        <select name="city_f" size="1" id="city_f">
			<option value="0"><?=GetMessage("OPTION_ALL");?></option>
			<?
			foreach ($arResult["ALL_CITIES"] as $k => $v)
			{
				if (intval($_GET['city_f'] == $k))
					$s = ' selected';
				else
					$s='';
				?>
				<option value="<?=$k;?>"<?=$s;?>><?=$v;?></option>
				<?
			}
			?>
		</select> 
		<label for="shop_f"><?=GetMessage("LABEL_SHOP");?></label> 
        <select name="shop_f" size="1" id="shop_f">
			<option value="0"><?=GetMessage("OPTION_ALL");?></option>
			<?
			foreach ($arResult["ALL_SHOPS"] as $k => $v)
			{
				if (intval($_GET['shop_f'] == $k))
					$s = ' selected';
				else
					$s='';
				?>
				<option value="<?=$k;?>"<?=$s;?>><?=$v;?></option>
				<?
			}
			?>
		</select>
		<input type="submit" name="" value="<?=GetMessage("FILTER_BTN");?>" />
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
                            <input type="hidden" name="weight[<?=$p['ID'];?>]" value="<?=$p['PROPERTY_WEIGHT_VALUE'];?>">
                            <input type="hidden" name="city[<?=$p['ID'];?>]" value="<?=$p['PROPERTY_CITY_VALUE'];?>">
                            <input type="hidden" name="city_name[<?=$p['ID'];?>]" value="<?=$p['PROPERTY_CITY_NAME'];?>">
                            <input type="hidden" name="rate[<?=$p['ID'];?>]" value="<?=$p['PROPERTY_RATE_VALUE'];?>">
                            <input type="hidden" name="summ_shop[<?=$p['ID'];?>]" value="<?=$p['PROPERTY_SUMM_SHOP_VALUE'];?>">
                            <input type="hidden" name="cost[<?=$p['ID'];?>]" value="<?=$p['PROPERTY_COST_2_VALUE'];?>">
                            <input type="hidden" name="id_in[<?=$p['ID'];?>]" value="<?=$p['PROPERTY_N_ZAKAZ_IN_VALUE'];?>">
                            <input type="hidden" name="pvz_id[<?=$p['ID'];?>]" value="<?=$p['PROPERTY_PVZ_VALUE'];?>" />
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
		<br>
		<p>
        	<label for="agent_id"><?=GetMessage("LABEL_AGENT");?></label>
            <select name="agent_id" size="1" onchange="GetPVZsOfAgent(this.value);" id="agent_id">
				<option value="0"></option>
				<?
				foreach ($arResult['AVAILABLE_AGENTS'] as $k => $v)
				{
					if ($_POST['agent_id'] == $k)
						$s = ' selected';
					else
						$s = '';
					echo '<option value="'.$k.'"'.$s.'>'.$v.'</option>';
				}
				?>
			</select> 
			<label for="pvz"><?=GetMessage("LABEL_PVZ");?></label> 
            <select name="pvz" size="1" id="pvz">
				<option value="0"></option>
				<?
				if (is_array($arResult["LIST_PVZ"]))
				{
					foreach ($arResult["LIST_PVZ"] as $v)
					{
						if ($_POST['pvz'] ==$v["ID"])
							$s = ' selected';
						else
							$s = '';
						?>
						<option value="<?=$v["ID"];?>"<?=$s;?>><?=$v["NAME"];?>, <?=$v['PROPERTY_CITY_NAME'];?>, <?=$v["PROPERTY_ADRESS_VALUE"];?> [<?=$v["CODE"];?>]</option>
						<?
					}
				}
				?>
			</select>
			<input type="submit" name="create_add_to_manifest" value="<?=GetMessage("ADD_BTN");?>" />
		</p>
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
}
?>
<br>