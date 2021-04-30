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
{
	echo '<p class="green">'.implode('</br>',$arResult["MESSAGE"]).'</p>';
}
else
{
	if (count($arResult["WARNINGS"]) > 0)
	{
		echo '<p class="orange">'.implode('</br>',$arResult["WARNINGS"]).'</p>';
	}
}

$APPLICATION->IncludeComponent(
	"black_mist:delivery.filter",
	"",
	array(
		"CACHE_TYPE" => "N",
		"CACHE_TIME" => "3600",
		"CACHE_NOTES" => "",
		"N_ZAKAZ" => "Y",
		"AGENTS" => "N",
		"DATE_CREATE" => "N",
		"EXCEPTIONS" => "Y"
	),
	false
);

if (count($arResult['LIST']) > 0)
{
	?>
    <form action="" method="post" name="curform">
		<input type="hidden" name="rand" value="<?=rand(100000,999999);?>" />
		<input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>" />
    	<table width="100%" cellpadding="3" cellspacing="0" border="1" bordercolor="#ccc" class="rows date_icon_in">
        	<thead>
                <tr>
                    <td><?=GetMessage("TABLE_HEAD_1");?></td>
                    <td><?=GetMessage("TABLE_HEAD_4");?></td>
                    <td><?=GetMessage("TABLE_HEAD_10");?></td>
                    <td><?=GetMessage("TABLE_HEAD_12");?></td>
                    <td><?=GetMessage("TABLE_HEAD_13");?></td>
                    
                    <td colspan="2"><?=GetMessage("TABLE_HEAD_8");?></td>
                    <td><?=GetMessage("TABLE_HEAD_9");?></td>
                    <td width="20"></td>
                </tr>
        	</thead>
			<tbody>
		<?
        foreach ($arResult['LIST'] as $pack)
		{
			?>
            <tr>
            	<td nowrap><a href="index.php?mode=package&id=<?=$pack['ID'];?>&back_url=<?=$_GET['mode'];?>"><?=$pack['PROPERTY_N_ZAKAZ_IN_VALUE'];?></a></td>
                <td>
                	<input type="text" name="fio[<?=$pack['ID'];?>]" value="<?=$pack['PROPERTY_RECIPIENT_VALUE'];?>">
					<?
					if ($pack["MAN_INFO"])
					{
						?>
						<input type="hidden" name="agent_id[<?=$pack["ID"];?>]" value="<?=$pack["MAN_INFO"]["PROPERTY_AGENT_TO_VALUE"];?>" />
						<input type="hidden" name="agent_name[<?=$pack["MAN_INFO"]["PROPERTY_AGENT_TO_VALUE"];?>]" value="<?=$pack["MAN_INFO"]["PROPERTY_AGENT_TO_NAME"];?>" />
						<? 
					}
					else
					{
						?>
						<input type="hidden" name="agent_id[<?=$pack["ID"];?>]" value="<?=$arResult["CURRENT_AGENT_ID"];?>" />
						<input type="hidden" name="agent_name[<?=$arResult["CURRENT_AGENT_ID"];?>]" value="<?=$arResult["CURRENT_AGENT_NAME"];?>" />
						<?
					}
					if ($pack['PROPERTY_COURIER_VALUE'] > 0)
					{ 
						?>
                        <input type="hidden" name="COURIER_ID[<?=$pack['ID'];?>]" value="<?=$pack["PROPERTY_COURIER_VALUE"];?>">
                        <input type="hidden" name="cur_fio[<?=$pack['ID'];?>]" value="<?=$pack['PROPERTY_COURIER_NAME'];?>" />
						<?
                    } 
					if (intval($pack["PROPERTY_PVZ_VALUE"]) > 0)
					{ 
						?>
                        <input type="hidden" name="PVZ_ID[<?=$pack['ID'];?>]" value="<?=$pack["PROPERTY_PVZ_VALUE"];?>">
                        <input type="hidden" name="pvz_name[<?=$pack['ID'];?>]" value="<?=$pack['PROPERTY_PVZ_NAME'];?>" />
						<?
                    }
					?>
                </td>
                <td><input type="text" value="<?=$pack["PROPERTY_COST_2_VALUE"];?>" name="summ[<?=$pack['ID'];?>]" class="medium"></td>
                <td><input type="text" value="<?=$pack['PROPERTY_SUMM_AGENT_VALUE'];?>" name="agent_cost_delivery[<?=$pack['ID'];?>]"></td>
                <td><input type="text" value="<?=$pack['PROPERTY_RATE_AGENT_VALUE'];?>" name="agent_cost_rate[<?=$pack['ID'];?>]"></td>
                <td nowrap><span class="colors color_<?=$pack["PROPERTY_STATE_SHORT_ENUM_ID"];?>"><?=$pack["PROPERTY_STATE_SHORT_VALUE"];?></span></td>
                <td align="center" width="250">
                    	<select name="operation[<?=$pack['ID'];?>]" size="1" style="width:250px;">
                        	<option value="0"></option>
							<? 
							foreach ($arParams["STATUS"] as $k => $v) {
								?>
                                <option value="<?=$k?>">[<?=$k?>] <?=$v;?></option>
								<?
                            }
							?>
                        </select>
                        <input type="hidden" name="id_in[<?=$pack['ID'];?>]" value="<?=$pack['PROPERTY_N_ZAKAZ_IN_VALUE'];?>" />
                        <input type="hidden" name="id_number[<?=$pack['ID'];?>]" value="<?=$pack['PROPERTY_N_ZAKAZ_VALUE'];?>" />
                        <input type="hidden" name="shop[<?=$pack['ID'];?>]" value="<?=$pack['PROPERTY_CREATOR_VALUE'];?>" />
                    </td>
                    <td nowrap>
                    	<input type="text" value="<?=date('d.m.Y H:i:s');?>" name="date_delivery[<?=$pack['ID'];?>]"  />
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
                    <td>
                    	<a href="/warehouse/index.php?mode=package_print&id=<?=$pack['ID'];?>&pdf=Y" title="<?=GetMessage("PRINT_LABEL");?>" target="_blank">
                        	<img src="/bitrix/components/black_mist/delivery.management/templates/.default/images/PDF-icon-20.png" width="20" height="20">
						</a>
                    </td>
                </tr>
				<?
            }
		?>
        </tbody></table>
        <input type="submit" name="save" value="<?=GetMessage("SAVE_BTN");?>" />
    </form>
	<? 
	$APPLICATION->IncludeComponent(
		"black_mist:delivery.pagination",
		".default",
		array(
			"PAGE" => $APPLICATION->GetCurPageParam("", array()),
			"HID_FIELDS" => array(
				"mode" => $_GET['mode'],
				"number" => $_GET['number'],
				'exceptions' => $_GET['exceptions']
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