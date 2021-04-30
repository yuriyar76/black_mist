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

$APPLICATION->IncludeComponent(
	"black_mist:delivery.filter",
	"",
	array(
		"CACHE_TYPE" => "N",
		"CACHE_TIME" => "3600",
		"CACHE_NOTES" => "",
		"DATE_CREATE" => "N",
		"N_ZAKAZ" => "Y"
	),
false
);

if (count($arResult["LIST"]) > 0)
{
	?>
    <form action="" method="post" name="curform">
    	<input type="hidden" name="rand" value="<?=rand(100000,999999);?>" />
		<input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>" />
        <table width="100%" cellpadding="3" cellspacing="0" border="1" bordercolor="#ccc" class="rows date_icon_in">
        	<thead>
                <tr>
                	<td width="10"><input type="checkbox" name="set" onclick="setCheckedNew(this,'ids')" /></td>
                    <td><?=GetMessage('TABLE_HEAD_1');?></td>
                    <td><?=GetMessage('TABLE_HEAD_6');?></td>
                    <td><?=GetMessage('TABLE_HEAD_7');?></td>
                    <td><?=GetMessage('TABLE_HEAD_5');?></td>
                    <td><?=GetMessage('TABLE_HEAD_2');?></td>
                    <td><?=GetMessage('TABLE_HEAD_3');?></td>
					<td><?=GetMessage('TABLE_HEAD_4');?></td>
                    <td><?=GetMessage('TABLE_HEAD_8');?></td>
                </tr>
			</thead>
            <tbody>
            	<?
				$arPods = array();
				foreach ($arResult['LIST'] as $pack)
				{
					$arPods[] = $pack['PROPERTY_N_ZAKAZ_IN_VALUE'];
					?>
					<tr id="row_<?=$pack["ID"];?>" class="CheckedRows">
                    	<td>
                        	<input type="checkbox" name="ids[]" value="<?=$pack['ID'];?>" onChange="SelectRow('check_<?=$pack["ID"];?>','row_<?=$pack["ID"];?>');" id="check_<?=$pack["ID"];?>">
                            <input type="hidden" name="id_in[<?=$pack['ID'];?>]" value="<?=$pack['PROPERTY_N_ZAKAZ_IN_VALUE'];?>">
                            <input type="hidden" name="shop_id[<?=$pack['ID'];?>]" value="<?=$pack['PROPERTY_CREATOR_VALUE'];?>">
						</td>
						<td><a href="index.php?mode=package&id=<?=$pack['ID'];?>&back_url=<?=$_GET['mode'];?>"><?=$pack['PROPERTY_N_ZAKAZ_IN_VALUE'];?></a></td>
                        <td><?=$pack['PROPERTY_CITY_NAME'];?></td>
                        <td><?=$pack['PROPERTY_CONDITIONS_VALUE'];?></td>
                        <td><?=CurrencyFormat($pack['PROPERTY_SUMM_SHOP_VALUE'], 'RUU');?></td>
                        <td><input type="text" value="<?=$pack['PROPERTY_COST_RETURN_VALUE'];?>" name="summ_return[<?=$pack['ID'];?>]" class="medium"> руб.</td>
                        <td><input type="text" name="fio[<?=$pack['ID'];?>]" value=""></td>
						<td nowrap>
                            <input type="text" value="<?=date('d.m.Y H:i:00');?>" name="date_delivery[<?=$pack['ID'];?>]"  />
                            <? 
                            $APPLICATION->IncludeComponent("bitrix:main.calendar", ".default", array(
                                "SHOW_INPUT" => "N",
                                "FORM_NAME" => "curform",
                                "INPUT_NAME" => "date_delivery[".$pack['ID']."]",
                                "INPUT_NAME_FINISH" => "",
                                "INPUT_VALUE" => "",
                                "INPUT_VALUE_FINISH" => "",
                                "SHOW_TIME" => "Y",
                                "HIDE_TIMEBAR" => "N"
                                ),false
                            );
                            ?>
                    	</td>
						<td nowrap>
                        <span class="colors color_<?=$pack["PROPERTY_STATE_ENUM_ID"];?> <?=($pack['PROPERTY_EXCEPTIONAL_SITUATION_VALUE'] == 1) ? 'color_exc' : '';?>">
							<?=$pack["PROPERTY_STATE_VALUE"];?>
						</span>
                        </td>
                    </tr>
					<?
					
				}
				?>
            </tbody>
		</table>
        <input type="submit" name="save" value="<?=GetMessage('BTN_SAVE');?>">
    </form>
    <p><a href="http://newpartner.ru/forms/form2.php?f001=<?=implode(', ', $arPods);?>" target="_blank"><?=GetMessage('LINK_TO_PODS');?></a></p>
    <?
	$APPLICATION->IncludeComponent(
		'black_mist:delivery.pagination',
		'',
		array(
			'PAGE' => $APPLICATION->GetCurUri(),
			'HID_FIELDS' => array(
				'mode' => $_GET['mode']
			),
			'NAV_STRING' => $arResult['NAV_STRING']
		),
		false
	);
}
else
{
	?>
	<p><?=GetMessage('NO_ORDERS');?></p>
    <?
}
?>
<br>