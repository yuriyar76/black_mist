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
	
	function ChangeLink()
	{
		var m = [];
		$('.cheks').each(function() {
			if ($(this).attr('checked'))
			{
				m.push($(this).val());
			}
		});
		var s = m.join(',');
		var ss = 'index.php?mode=register&pdf=Y&ids=' + s;
		$('#link_register').attr('href',ss);
	}
</script>

<?
//dump($arResult["BUTTONS"]);
foreach ($arResult["BUTTONS"] as $b)
{
	if (in_array($arResult['MODE'],$b["in_mode"]))
	{
		?>
		<a href="<?=$b["link"];?>" class="button button-blue-new add_pack"><span><?=$b["title"];?></span></a> 
		<?
	}
}
?>

<?// dump($arResult); ?>

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
		if ($arResult["SHOW_LINK_REESTR"])
		{
			?>
			<li class="nobg">
				<a href="index.php?mode=register&pdf=Y" title="<?=GetMessage("PRINT_REGISTER");?>" target="_blank" id="link_register">
                	<img src="/bitrix/components/black_mist/delivery.management/templates/.default/images/PDF-icon-20.png" width="20" height="20">
                </a>
			</li>
            <?
		}
        ?>
    </ul>
</div>

<br class="clear">

<?
if (count($arResult["ERRORS"]) > 0)
{
	echo '<p class="red">'.implode('</br>',$arResult["ERRORS"]).'</p>';
}

if (count($arResult["MESSAGE"]) > 0)
{
	$_POST = array();
	echo '<p class="green">'.implode('</br>',$arResult["MESSAGE"]).'</p>';
}
if (count($arResult["WARNINGS"]) > 0) 
{
	echo '<p class="orange">'.implode('</br>',$arResult["WARNINGS"]).'</p>';
}
	
$d_from = strlen($_GET['date_from']) ? $_GET['date_from'] : '';
$d_to = strlen($_GET['date_to']) ? $_GET['date_to'] : '';

$APPLICATION->IncludeComponent(
	"black_mist:delivery.filter",
	"",
	array(
		"CACHE_TYPE" => "N",
		"CACHE_TIME" => "3600",
		"CACHE_NOTES" => "",
		"N_ZAKAZ" => "Y",
		"AGENTS" => "N",
		"DATE_CREATE" => "Y",
		"STATES_SHORT" => "Y",
		"STYLE_PAGINATION" => 'margin-top: 0;'
	),
	false
);


if (count($arResult["LISTPACKAGES"]) > 0)
{
	$ids = array();
	foreach ($arResult["LISTPACKAGES"] as $k => $p)
	{
		$ids[] = $p['ID'];
	}
	?>
    <form action="" method="post">
    	<input type="hidden" name="rand" value="<?=rand(100000,999999);?>" />
        <input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>" />
    	<table width="100%" cellpadding="3" cellspacing="0" border="1" bordercolor="#ccc" class="rows">
        	<thead>
                <tr>
                    <td rowspan="2"><input type="checkbox" name="set" onclick="setCheckedNew(this,'id'); ChangeLink();" /></td>
                    <td rowspan="2"><?=GetMessage("TABLE_HEAD_9");?></td>
                    <td rowspan="2"><?=GetMessage("TABLE_HEAD_1");?></td>
                    <td rowspan="2"><?=GetMessage("TABLE_HEAD_2");?></td>
                    <td rowspan="2"><?=GetMessage("TABLE_HEAD_3");?></td>
                    <td rowspan="2"><?=GetMessage("TABLE_HEAD_4");?></td>
                    <td colspan="3"><?=GetMessage("TABLE_HEAD_13");?></td>
                    <td colspan="4"><?=GetMessage("TABLE_HEAD_14");?></td>
                    <td rowspan="2"><?=GetMessage("TABLE_HEAD_15");?></td>
                    <td rowspan="2"><?=GetMessage("TABLE_HEAD_8");?></td>     
                    <td width="20" rowspan="2">
                        <a href="print.php?ids=<?=implode(',',$ids);?>" title="<?=GetMessage("PRINT_LABELS");?>" target="_blank">
                            <img src="/bitrix/components/black_mist/delivery.packages/templates/.default/images/print_icon.png" width="20" height="20">
                        </a>
                    </td>
                </tr>
                <tr>
                    <td><?=GetMessage("TABLE_HEAD_6");?></td>
                    <td><?=GetMessage("TABLE_HEAD_5");?></td>
                    <td><?=GetMessage("TABLE_HEAD_10");?></td>
                    <td><?=GetMessage("TABLE_HEAD_11");?></td>
                    <td><?=GetMessage("TABLE_HEAD_7");?></td>
                    <td><?=GetMessage("TABLE_HEAD_12");?></td>
                    <td><?=GetMessage("TABLE_HEAD_16");?></td>
                </tr>
			</thead>
			<tbody>
			<?
			foreach ($arResult["LISTPACKAGES"] as $k => $p)
			{
				$link = 'index.php?mode=package&id='.$p['ID'].'&back_url=list';
				if ($p['PROPERTY_STATE_ENUM_ID'] == 116) 
					$link = 'index.php?mode=makepackageofgoods&id='.$p['ID'].'&back_url=list';
				if (in_array($p['PROPERTY_STATE_ENUM_ID'],array(39,57,80))) 
					$link = 'index.php?mode=package_edit&id='.$p['ID'].'&back_url=list';
				?>
				<tr id="row_<?=$p["ID"];?>" 
					<?
					if ((!in_array($p['PROPERTY_STATE_ENUM_ID'],$arParams['STATUS_FINAL'])))
					{
						?>
						class="CheckedRows"
						<?
					}
					?>
				>
					<td align="center" width="10">
						<?
						if (((!in_array($p['PROPERTY_STATE_ENUM_ID'], $arParams['STATUS_FINAL'])) && ($arResult['ROLE_USER'] != "MAN")) || 
							(($arResult['ROLE_USER'] == "MAN") && ($p['PROPERTY_STATE_ENUM_ID'] == 39)))
							{
								if (in_array($p['ID'],$_POST['id']))
									$ch = ' checked'; 
								else
									$ch = '';
							?>
							<input type="checkbox" name="id[]" value="<?=$p['ID'];?>"<?=$ch;?> onChange="SelectRow('check_<?=$p["ID"];?>','row_<?=$p["ID"];?>'); ChangeLink();" 
                            	id="check_<?=$p["ID"];?>" class="cheks"
							>
							<input type="hidden" name="status[<?=$p['ID'];?>]" value="<?=$p['PROPERTY_STATE_ENUM_ID'];?>">
							<input type="hidden" name="id_in[<?=$p['ID'];?>]" value="<?=$p['PROPERTY_N_ZAKAZ_IN_VALUE'];?>">
							<?
						}
						?>
                    </td>
                    <td><a href="<?=$link;?>"><?=$p['PROPERTY_N_ZAKAZ_IN_VALUE'];?></a></td>
                    <td><?=$p['PROPERTY_N_ZAKAZ_VALUE'];?></td>
                    <td><?=$p["PROPERTY_WHEN_TO_DELIVER_VALUE"];?></td>
                    <td><?=$p['PROPERTY_CITY_NAME'];?></td>
                    <td><?=$p['PROPERTY_RECIPIENT_VALUE'];?></td>
                    <td class="mark"><?=CurrencyFormat($p['PROPERTY_COST_2_VALUE'],"RUU");?></td>
                    <td><?=CurrencyFormat($p['PROPERTY_COST_GOODS_VALUE'],"RUU");?></td>
                    <td><?=CurrencyFormat($p['PROPERTY_COST_3_VALUE'],"RUU");?></td>
                    <td class="mark"><?=CurrencyFormat(($p['PROPERTY_SUMM_SHOP_VALUE']+$p['PROPERTY_RATE_VALUE']+$p['PROPERTY_SUMM_ISSUE_VALUE']),"RUU");?></td>
                    <td><?=CurrencyFormat($p['PROPERTY_SUMM_SHOP_VALUE'],"RUU");?></td>
                    <td><?=CurrencyFormat($p['PROPERTY_RATE_VALUE'],"RUU");?></td>
                    <td><?=CurrencyFormat($p['PROPERTY_SUMM_ISSUE_VALUE'],"RUU");?></td>
                    <td class="mark"><?=CurrencyFormat(($p['PROPERTY_COST_2_VALUE']-$p['PROPERTY_SUMM_SHOP_VALUE']-$p['PROPERTY_RATE_VALUE']-$p['PROPERTY_SUMM_ISSUE_VALUE']),"RUU");?></td>
                    <td><span class="colors color_<?=$p['PROPERTY_STATE_SHORT_ENUM_ID'];?> <?=($p['PROPERTY_EXCEPTIONAL_SITUATION_VALUE'] == 1) ? 'color_exc' : '';?>"><?=$p['PROPERTY_STATE_SHORT_VALUE'];?></span></td>
                    <td>
                        <a href="print.php?ids=<?=$p['ID'];?>" title="<?=GetMessage("PRINT_LABELS");?>" target="_blank">
                        	<img src="/bitrix/components/black_mist/delivery.packages/templates/.default/images/print_icon.png" width="20" height="20">
						</a>
                    </td>
				</tr>
				<?
			}
			?>
			</tbody>
		</table>
        <input type="submit" name="send_package" value="<?=GetMessage("BTN_1");?>">
            <?

		
		if ($arResult['ROLE_USER'] != "MAN")
		{
			?>
            <input type="submit" name="delete_packages" value="<?=GetMessage("BTN_2");?>"> 
            <input type="submit" name="cancel" value="<?=GetMessage("BTN_3");?>">
            <input type="submit" name="return" value="<?=GetMessage("BTN_6");?>">
        	<?
		}
		?>
        <br class="clear">
    </form>
    
    <br class="clear">
    <?
	$APPLICATION->IncludeComponent(
		"black_mist:delivery.pagination",
		"",
		array(
			"PAGE" => $APPLICATION->GetCurUri(),
			"HID_FIELDS" => array(
				"mode" => $_GET['mode'],
				"number_order" => $_GET["number_order"],
				"date_from" => $_GET["date_from"],
				"date_to" => $_GET["date_to"],
				"status" => $_GET["status"]
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

if ((($arResult["DEMO"]) && ($arResult["COUNT"] < $arResult["LIMIT"])) || (!$arResult["DEMO"]))
{
}  
?>
<div style="width:250px; float:right; text-align:right;">
	<a href="/warehouse/pvz_list.php">Справочник ПВЗ</a>
</div>
<br class="clear">
<br>