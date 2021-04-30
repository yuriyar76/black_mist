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

$APPLICATION->IncludeComponent(
	"black_mist:delivery.filter",
	"",
	array(
		"CACHE_TYPE" => "N",
		"CACHE_TIME" => "3600",
		"CACHE_NOTES" => "",
		"N_ZAKAZ" => "Y",
		"AGENTS" => "Y",
		"DATE_CREATE" => "N",
		"EXCEPTIONS" => "Y",
		"UK_ID" => $arResult["CURRENT_COMPANY"]
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
                    <td><?=GetMessage("TABLE_HEAD_2");?></td>
             <!--       <td><?=GetMessage("TABLE_HEAD_3");?></td> -->
                    <td><?=GetMessage("TABLE_HEAD_4");?></td>
                 <!--   <td><?=GetMessage("TABLE_HEAD_6");?></td>
                    <td><?=GetMessage("TABLE_HEAD_7");?></td> -->
                    <td><?=GetMessage("TABLE_HEAD_10");?></td>
                    <td width="80"><?=GetMessage("TABLE_HEAD_11");?></td>
                    <td><?=GetMessage("TABLE_HEAD_12");?></td>
                    <td><?=GetMessage("TABLE_HEAD_13");?></td>
                    <td colspan="2"><?=GetMessage("TABLE_HEAD_14");?></td>
                    <td colspan="2"><?=GetMessage("TABLE_HEAD_8");?></td>
                    <td><?=GetMessage("TABLE_HEAD_9");?></td>
                    <td></td>
                </tr>
        	</thead>
			<tbody>
		<?
		$arPods = array();
        foreach ($arResult['LIST'] as $pack)
		{
			$arPods[] = $pack['PROPERTY_N_ZAKAZ_IN_VALUE'];
			?>
            <tr>
            	<td nowrap><a href="index.php?mode=package&id=<?=$pack['ID'];?>&back_url=<?=$_GET['mode'];?>"><?=$pack['PROPERTY_N_ZAKAZ_IN_VALUE'];?></a></td>
                <td><?=$pack['PROPERTY_CITY_NAME'];?></td>
             <!--   <td><? echo ($pack['PROPERTY_CONDITIONS_ENUM_ID'] == 37) ? $pack['PROPERTY_ADRESS_VALUE'] : $pack['PROPERTY_CONDITIONS_VALUE']; ?></td> -->
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
                  <!-- <td>
					<?
					if ($pack["MAN_INFO"])
					{
						?>
						<a href="/agents/index.php?mode=agent&id=<?=$pack["MAN_INFO"]["PROPERTY_AGENT_TO_VALUE"];?>" target="_blank"><?=$pack["MAN_INFO"]["PROPERTY_AGENT_TO_NAME"];?></a>
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
					?>
                </td>
                <td>
					<? if ($pack['PROPERTY_COURIER_VALUE'] > 0)
					{ 
						?>
                        <?=$pack["PROPERTY_COURIER_NAME"];?>
                        <input type="hidden" name="COURIER_ID[<?=$pack['ID'];?>]" value="<?=$pack["PROPERTY_COURIER_VALUE"];?>">
                        <input type="hidden" name="cur_fio[<?=$pack['ID'];?>]" value="<?=$pack['PROPERTY_COURIER_NAME'];?>" />
						<?
                    } 
					if (intval($pack["PROPERTY_PVZ_VALUE"]) > 0)
					{ 
						?>
                        <?=$pack["PROPERTY_PVZ_NAME"];?>
                        <input type="hidden" name="PVZ_ID[<?=$pack['ID'];?>]" value="<?=$pack["PROPERTY_PVZ_VALUE"];?>">
                        <input type="hidden" name="pvz_name[<?=$pack['ID'];?>]" value="<?=$pack['PROPERTY_PVZ_NAME'];?>" />
						<?
                    }
					?>
                     </td>  -->
                <td><input type="text" value="<?=$pack["PROPERTY_COST_2_VALUE"];?>" name="summ[<?=$pack['ID'];?>]" class="medium"></td>
                <td align="center"><?=($pack['PROPERTY_PAY_FOR_REFUSAL_VALUE'] == 1) ? '&bull;' : '';?></td>
                <td>
                	<?=CurrencyFormat($pack['PROPERTY_SUMM_SHOP_VALUE'],"RUU");?>
                	<!-- <input type="text" value="<?=$pack['PROPERTY_SUMM_SHOP_VALUE'];?>" name="summ_delivery[<?=$pack['ID'];?>]" class="medium"> -->
				</td>
                <td>
                	<?=CurrencyFormat($pack["PROPERTY_RATE_VALUE"],"RUU");?>
                	<!-- <input type="text" value="<?=$pack["PROPERTY_RATE_VALUE"];?>" name="summ_rate[<?=$pack['ID'];?>]" class="medium"> -->
				</td>
                <td width="20"><input type="checkbox" name="return_yes[<?=$pack['ID'];?>]" value="1" <?=($pack['PROPERTY_RETURN_VALUE'] == 1) ? 'checked' : '';?>></td>
                <td><input type="text" value="<?=$pack['PROPERTY_COST_RETURN_VALUE'];?>" name="summ_return[<?=$pack['ID'];?>]" class="medium"></td>
                <td nowrap><span class="colors color_<?=$pack["PROPERTY_STATE_ENUM_ID"];?> <?=($pack['PROPERTY_EXCEPTIONAL_SITUATION_VALUE'] == 1) ? 'color_exc' : '';?>"><?=$pack["PROPERTY_STATE_VALUE"];?></span></td>
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
        <?
		if ($USER->IsAdmin())
		{
			/*
			?>
			<input type="submit" name="test_save" value="Тестирование формирования отчета агента">
            <?
			*/
		}
		?>
    </form>
    <p><a href="http://newpartner.ru/forms/form2.php?f001=<?=implode(', ', $arPods);?>&show_sec=Y" target="_blank"><?=GetMessage('LINK_TO_PODS');?></a></p>
	<? 
	$APPLICATION->IncludeComponent(
		"black_mist:delivery.pagination",
		".default",
		array(
			"PAGE" => $APPLICATION->GetCurPageParam("", array()),
			"HID_FIELDS" => array(
				"mode" => $_GET['mode'],
				"agent" => $_GET['agent'],
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