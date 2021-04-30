<script>
	$(document).ready(function()
	{
		CalculateCostOfDelivery();
		Disabled();
	});
	
	function Disabled()
	{
		var type = $('#conditions').val();
		if (type == '38')
		{
			$('.blocy').css('display','none');
		}
		if (type == '37')
		{
			$('.blocy').css('display','table-row');
		}
	}
</script>
<div class="new_menu">
    <ul>
        <li class="active"><a href="javascript:void(0);"><?=$arResult["TITLE"];?></a></li>
        <?
        foreach ($arResult["MENU"] as $k => $v)
		{
            ?>
            <li><a href="index.php?mode=<?=$k?>"><?=$v?></a></li>
            <?
        }
		if ($arResult['PACK']) 
		{
			?>
            <li class="nobg"><a href="/warehouse/index.php?mode=package_print&id=<?=$_GET["id"];?>&pdf=Y" title="<?=GetMessage("PRINT_ICON_1");?>" target="_blank">
                <img src="/bitrix/components/black_mist/delivery.management/templates/.default/images/PDF-icon-20.png" width="20" height="20"></a>
            </li>
            <li class="nobg"><a href="print.php?ids=<?=$arResult['PACK']['ID'];?>" title="<?=GetMessage("PRINT_ICON_2");?>" target="_blank">
                <img src="/bitrix/components/black_mist/delivery.packages/templates/.default/images/print_icon.png" width="20" height="20"></a>
            </li>
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

if ($arResult['PACK'])
{
	?>
    <p id="inser"></p>
    <form action="" method="post">
    	<input type="hidden" name="rand" value="<?=rand(100000,999999);?>" />
		<input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>" />
    	<input type="hidden" name="pack_id" value="<?=$arResult['PACK']['ID'];?>">
        <input type="hidden" name="number" value="<?=$arResult['PACK']['PROPERTY_N_ZAKAZ_IN_VALUE'];?>">
	<div class="table_group">
        <table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="rows">
            <tbody>
                <tr>
                    <td width="350"><strong><?=GetMessage("LABEL_1");?></strong></td>
                    <td><a href="/shops/index.php?mode=shop&id=<?=$arResult['PACK']['PROPERTY_CREATOR_VALUE'];?>" target="_blank"><?=$arResult['PACK']['PROPERTY_CREATOR_NAME'];?></a></td>
                </tr>
                <tr>
                    <td><strong><?=GetMessage("LABEL_2");?></strong></td>
                    <td><a href="/warehouse/index.php?mode=package&id=<?=$arResult['PACK']['ID'];?>"><?=$arResult['PACK']['PROPERTY_N_ZAKAZ_IN_VALUE'];?></a></td>
                </tr>
                <tr>
                    <td><strong><?=GetMessage("LABEL_3");?></strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_N_ZAKAZ_VALUE'];?></td>
                </tr>
                <tr>
                    <td><strong><?=GetMessage("LABEL_4");?></strong></td>
                    <td><?=$arResult['PACK']['DATE_CREATE'];?></td>
                </tr>
				<tr>
                    <td><strong><?=GetMessage("LABEL_37");?></strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_DATE_TO_DELIVERY_VALUE'];?></td>
                </tr>
				<tr>
                    <td><strong><?=GetMessage("LABEL_38");?></strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_AGENT_NAME'];?></td>
                </tr>
            </tbody>
        </table>
	</div>
	<div class="table_group">
		<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="borders date_icon_in">
    		<tbody>
				<tr>
            		<td width="350"><strong><?=GetMessage("LABEL_5");?></strong></td>
                	<td><input type="text" name="RECIPIENT" value="<?=$arResult['PACK']['PROPERTY_RECIPIENT_VALUE'];?>" ></td>
				</tr>
				<tr>
                	<td><strong><?=GetMessage("LABEL_6");?></strong></td>
                    <td><input type="text" name="PHONE" value="<?=$arResult['PACK']['PROPERTY_PHONE_VALUE'];?>"></td>
				</tr>
				<tr>
                	<td><strong><?=GetMessage("LABEL_7");?></strong></td>
                    <td>
                    	<input type="text" name="PROPERTY_CITY" value="<?=$arResult['PACK']['PROPERTY_CITY'];?>" id="city_price_out">
						<input type="hidden" name="price" value="<?=$arResult["PRICE"];?>" id="price">
						<input type="hidden" name="price_2" value="<?=$arResult["PRICE_2"];?>" id="price_2">
						<input type="hidden" name="city_id" value="<?=$arResult['PACK']['PROPERTY_CITY_VALUE'];?>" id="city_id">
						<input type="hidden" name="persent_1" value="" id="persent_1">
						<input type="hidden" name="persent_2" value="" id="persent_2">
					</td>
				</tr>
                <tr>
                	<td></td>
                    <td>
                    	<select name="CONDITIONS" size="1" id="conditions" onchange="Disabled();">
							<option value="37"<?=($arResult['PACK']['PROPERTY_CONDITIONS_ENUM_ID'] == 37) ? ' selected' : ''; ?>><?=GetMessage('SELECT_CONDITIONS_1');?></option>
							<option value="38"<?=($arResult['PACK']['PROPERTY_CONDITIONS_ENUM_ID'] == 38) ? ' selected' : ''; ?>><?=GetMessage('SELECT_CONDITIONS_2');?></option>
						</select>
					</td>
                </tr>
				<tr class="blocy" style="display:none;">
                	<td><strong><?=GetMessage("LABEL_8");?></strong> </td>
                    <td><textarea name="ADRESS"><?=$arResult['PACK']['PROPERTY_ADRESS_VALUE'];?></textarea></td>
				</tr>
				<?
				if (strlen($arResult['PACK']['PROPERTY_WHEN_TO_DELIVER_VALUE']))
				{
					?>
					<tr class="blocy" style="display:none;">
                        <td><strong><?=GetMessage("LABEL_9");?></strong></td>
                        <td>
							<? $APPLICATION->IncludeComponent("bitrix:main.calendar", ".default", array(
                                "SHOW_INPUT" => "Y",
                                "FORM_NAME" => "curform",
                                "INPUT_NAME" => "date_deliv",
                                "INPUT_NAME_FINISH" => false,
                                "INPUT_VALUE" => $arResult['PACK']["DELIV_DATE"],
                                "INPUT_VALUE_FINISH" => false,
                                "SHOW_TIME" => "N",
                                "HIDE_TIMEBAR" => "Y",
                                "INPUT_ADDITIONAL_ATTR" => 'placeholder="ÄÄ.ÌÌ.ÃÃÃÃ" class="small_inp date"'
                                ),false);
                            ?>
                            <select name="TIME_PERIOD" size="1" class="short">
                            <?
							foreach ($arResult['time_periods'] as $k => $v)
							{
								?>
                                <option value="<?=$k;?>"<?=($arResult['PACK']['PROPERTY_TIME_PERIOD_ENUM_ID'] == $k) ? ' selected' : ' ';?>><?=$v;?></option>
                                <?
							}
							?>
                            </select></td>
					</tr>
					<?
				}
				?>
				<tr>
                        <td>
                            <strong><?=GetMessage("LABEL_11");?></strong></sup>
                        </td>
                        <td>
                            <input type="checkbox" name="urgent" value="2" onChange="CalculateCostOfDelivery();" id="urgent" <? echo ($arResult['PACK']['PROPERTY_URGENCY_ORDER_ENUM_ID'] == 172) ? ' checked' : '';?>>
                        </td>
                    </tr>
                    <tr>
                        <td><strong><?=GetMessage("LABEL_32");?></strong></td>
                        <td><input type="checkbox" name="to_legal" value="1" <? echo ($arResult['PACK']['PROPERTY_DELIVERY_LEGAL_VALUE'] ==  1) ? ' checked' : ''; ?>></td>
                    </tr>
				<tr>
					<td><strong><?=GetMessage("LABEL_10");?></strong></td>
					<td><textarea name="PREFERRED_TIME"><?=$arResult['PACK']['PROPERTY_PREFERRED_TIME_VALUE']["TEXT"];?></textarea></td>
				</tr> 
			</tbody>
	</table>
	</div>
	<div class="table_group">
		<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="borders">
			<tbody>
				<tr>
					<td width="350"><strong><?=GetMessage("LABEL_12");?></strong></td>
					<td><input type="text" name="WEIGHT"  value="<?=$arResult['PACK']['PROPERTY_WEIGHT_VALUE'];?>" id="weight" onChange="CalculateCostOfDelivery();"></td>
				</tr>
				<tr>
					<td><strong><?=GetMessage("LABEL_13");?></strong></td>
					<td>
					<input type="text" value="<?=$arResult['PACK']['PROPERTY_SIZE_1_VALUE']?>" name="size_1" class="small_inp" onChange="CalculateCostOfDelivery();"> 
                    <input type="text" value="<?=$arResult['PACK']['PROPERTY_SIZE_2_VALUE']?>" name="size_2" class="small_inp" onChange="CalculateCostOfDelivery();"> 
                    <input type="text" value="<?=$arResult['PACK']['PROPERTY_SIZE_3_VALUE']?>" name="size_3" class="small_inp" onChange="CalculateCostOfDelivery();"> 
                    </td>
				</tr>
				<tr>
					<td><strong><?=GetMessage("LABEL_14");?></strong></td>
					<td><input type="text" name="PLACES"  value="<?=$arResult['PACK']['PROPERTY_PLACES_VALUE'];?>"></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="table_group">
		<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="borders">
            <tbody>
				<tr>
                    <td width="350"><strong><?=GetMessage("LABEL_15");?></strong></td>
					<td><input type="text" name="COST_GOODS" value="<?=$arResult['PACK']['PROPERTY_COST_GOODS_VALUE'];?>"></td>
				</tr>
				<tr>
					<td><strong><?=GetMessage("LABEL_16");?></strong></td>
					<td><input type="text" name="COST_3" value="<?=$arResult['PACK']['PROPERTY_COST_3_VALUE'];?>"></td>
				</tr>
				<tr>
					<td><strong><?=GetMessage("LABEL_17");?></strong></td>
					<td><input type="text" name="COST_2" value="<?=$arResult['PACK']['PROPERTY_COST_2_VALUE'];?>"></td>
				</tr>
				<tr>
					<td><strong><?=GetMessage("LABEL_19");?></strong></td>
					<td>
                    	<select name="CASH" size="1">
                        	<option value="124"<?=($arResult['PACK']['PROPERTY_CASH_ENUM_ID'] == 124) ? ' selected' : ''; ?>><?=GetMessage('YES');?></option>
                            <option value="125"<?=($arResult['PACK']['PROPERTY_CASH_ENUM_ID'] == 125) ? ' selected' : ''; ?>><?=GetMessage('NO');?></option>
                        </select>
                    </td>
				</tr>
                <tr>
                    	<td><strong><?=GetMessage("LABEL_34");?></strong></td>
                    	<td>
                        	<input type="checkbox" name="PAY_FOR_REFUSAL" value="1" <? echo ($arResult['PACK']['PROPERTY_PAY_FOR_REFUSAL_VALUE'] == 1) ? ' checked' : ''; ?>>
                        </td>
				</tr>
			</tbody>
		</table>
	</div>
	<?
	if  ($arResult['PACK']["PROPERTY_TAKE_PROVIDER_ENUM_ID"] == 174)
	{
		?>
		<div class="table_group">
			<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="rows">
                <tbody>
					<tr>
                        <td width="350"><strong><?=GetMessage("LABEL_21");?></strong></td>
                        <td>
							<?=$arResult['PACK']['PROPERTY_TAKE_DATE_VALUE'];?>
							<?
							if (count($arResult['PACK']["ZABORS"]) > 0)
							{
								?>
								<table cellpadding="3" cellspacing="0" border="1" bordercolor="#ccc" class="rows">
									<thead>
										<tr>
                                            <td><?=GetMessage("TABLE_HEAD_1");?></td>
                                            <td><?=GetMessage("TABLE_HEAD_2");?></td>
											<td><?=GetMessage("TABLE_HEAD_3");?></td>
											<td><?=GetMessage("TABLE_HEAD_4");?></td>
											<td><?=GetMessage("TABLE_HEAD_5");?></td>
										</tr>
									</thead>
									<tbody>
										<?
										foreach ($arResult['PACK']["ZABORS"] as $z)
										{
											?>
											<tr>
												<td><?=$z["NAME"];?></td>
												<td><?=$z["PROPERTY_431_VALUE"];?></td>
												<td><?=WeightFormat($z["PROPERTY_430_VALUE"]);?></td>
												<td><?=$z["PROPERTY_432_VALUE"];?></td>
												<td>
                                                	<?
													if ($z["PROPERTY_428_VALUE"])
													{
														?>
                                                    	<a href="/suppliers/index.php?mode=request&id=<?=$z["PROPERTY_428_VALUE"];?>">
															<?=$arResult['PACK']["REQV_INFO"][$z["PROPERTY_428_VALUE"]]["PROPERTY_NUMBER_VALUE"];?>
														</a>, 
														<?=$arResult['PACK']["REQV_INFO"][$z["PROPERTY_428_VALUE"]]["PROPERTY_SUPPLIER_NAME"];?>
                                                    <?
                                                    }
													?>
												</td>
											</tr>
											<?
										}
										?>
									</tbody>
								</table>
								<?
							}
							?>
						</td>
					</tr>
					<?
					if (strlen($arResult['PACK']['PROPERTY_TAKE_COMMENT_VALUE']["TEXT"]))
					{
						?>
						<tr>
                            <td><strong><?=GetMessage("LABEL_22");?></strong></td>
							<td><?=$arResult['PACK']['PROPERTY_TAKE_COMMENT_VALUE']["TEXT"];?></td>
						</tr>
						<?
					}
					?>
				</tbody>
			</table>
		</div>
		<?
	}
	?>
	<div class="table_group">
		<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="borders">
			<tbody>
				<tr>
                    <td width="350"><strong><?=GetMessage("LABEL_23");?></strong></td>
					<td><input type="text" name="RATE" value="<?=$arResult['PACK']['PROPERTY_RATE_VALUE'];?>"></td>
				</tr>
				<tr>
					<td><strong><?=GetMessage("LABEL_24");?></strong></td>
					<td><input type="text" name="SUMM_SHOP" value="<?=$arResult['PACK']['PROPERTY_SUMM_SHOP_VALUE'];?>"></td>
				</tr>
				<?
				if ($arResult['PACK']["PROPERTY_SUMM_SHOP_ZABOR_VALUE"] > 0)
				{
					?>
					<tr>
						<td><strong><?=GetMessage("LABEL_25");?></strong></td>
						<td><?=CurrencyFormat($arResult['PACK']["PROPERTY_SUMM_SHOP_ZABOR_VALUE"],"RUU");?></td>
					</tr>
					<?
				}
				if (count($arResult['PACK']['GOODS']) > 0)
				{
					?>
					<tr>
						<td><strong><?=GetMessage("LABEL_33");?></strong></td>
                        <td><input type="text" name="SUMM_ISSUE" value="<?=$arResult['PACK']['PROPERTY_SUMM_ISSUE_VALUE'];?>">
                    </tr>
                    <?
				}
				if ($arResult['PACK']['PROPERTY_RETURN_VALUE'] == 1)
				{
					?>
					<tr>
						<td><strong><?=GetMessage("LABEL_36");?></strong></td>
                        <td><input type="text" name="COST_RETURN" value="<?=$arResult['PACK']['PROPERTY_COST_RETURN_VALUE'];?>"></td>
                    </tr>
                    <?
				}
				?>
			</tbody>
		</table>
	</div>
	<?
	if ((floatval($arResult['PACK']['PROPERTY_RATE_AGENT_VALUE']) > 0) || (floatval($arResult['PACK']['PROPERTY_SUMM_AGENT_VALUE']) > 0))
	{
		?>
		<div class="table_group">
			<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="rows">
				<tbody>
				<?
				if (floatval($arResult['PACK']['PROPERTY_RATE_AGENT_VALUE']) > 0)
				{
					?>
					<tr>
						<td width="350"><strong><?=GetMessage("LABEL_26");?></strong></td>
						<td><?=CurrencyFormat($arResult['PACK']['PROPERTY_RATE_AGENT_VALUE'],"RUU");?></td>
					</tr>
					<?
				} 
				if (floatval($arResult['PACK']['PROPERTY_SUMM_AGENT_VALUE']) > 0)
				{
					?>
					<tr>
						<td width="350"><strong><?=GetMessage("LABEL_27");?></strong></td>
						<td><?=CurrencyFormat($arResult['PACK']['PROPERTY_SUMM_AGENT_VALUE'],"RUU");?></td>
					</tr>
					<?
				}
				?>
				</tbody>
			</table>
		</div>
		<?
	}
	if ( strlen($arResult['PACK']['PROPERTY_COURIER_NAME']) || strlen($arResult['PACK']['PROPERTY_COMMENTS_COURIER_VALUE']) || strlen($arResult['PACK']['PROPERTY_DATE_DELIVERY_VALUE']) )
	{
		?>
		<div class="table_group">
			<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="rows">
                <tbody> 
					<?
					if(strlen($arResult['PACK']['PROPERTY_COURIER_NAME']))
					{
						?>
						<tr>
							<td width="350"><strong><?=GetMessage("LABEL_28");?></strong></td>
							<td><?=$arResult['PACK']['PROPERTY_COURIER_NAME'];?></td>
						</tr>
						<?
					} 
					if (strlen($arResult['PACK']['PROPERTY_COMMENTS_COURIER_VALUE']))
					{
						?>
						<tr>
							<td width="350"><strong><?=GetMessage("LABEL_29");?></strong></td>
							<td><?=$arResult['PACK']['PROPERTY_COMMENTS_COURIER_VALUE'];?></td>
						</tr>
						<?
					} 
					if (strlen($arResult['PACK']['PROPERTY_DATE_DELIVERY_VALUE']))
					{
						?>
						<tr>
							<td width="350"><strong><?=GetMessage("LABEL_30");?></strong></td>
							<td><?=$arResult['PACK']['PROPERTY_DATE_DELIVERY_VALUE'];?></td>
						</tr>
						<?
					}
					?>
				</tbody>
			</table>
		</div>
		<?
	}
	?>
		<input type="submit" name="save" value="<?=GetMessage('SAVE');?>" style="margin-top:0;">
    </form>
	<div class="status_block">
		<?=$arResult['PACK']['PROPERTY_STATE_VALUE'];?>
	</div>
	<? 
	if ($arResult['PACK']['PROPERTY_STATE_ENUM_ID'] == 56)
	{
	}
	if (count($arResult['PACK']['GOODS']) > 0)
	{
		?>
		<h4 style="position:relative; height:30px; line-height:30px;">
			<?=GetMessage("GOODS_TITLE");?> 
			<a href="xls.php?id=<?=$arResult['PACK']['ID'];?>" style=" display:block; width:30px; height:30px; position:absolute; top:0; right:0;">
				<img src="/bitrix/templates/portal/images/excel_ico.jpg" width="30" height="30" alt="" />
			</a>
		</h4>
		<table cellpadding="3" cellspacing="0" border="1" bordercolor="#ccc" class="rows" width="100%">
			<thead>
				<tr>
					<td><?=GetMessage("TABLE_HEAD_6");?></td>
					<td><?=GetMessage("TABLE_HEAD_7");?></td>
					<td><?=GetMessage("TABLE_HEAD_8");?></td>
					<td><?=GetMessage("TABLE_HEAD_9");?></td>
					<td><?=GetMessage("TABLE_HEAD_10");?></td>
					<td><?=GetMessage("TABLE_HEAD_11");?></td>
					<td><?=GetMessage("TABLE_HEAD_12");?></td>
					<td><?=GetMessage("TABLE_HEAD_13");?></td>
				</tr>
			</thead>
			<tbody>
				<?
				$counts = $weighs = $costs = 0;
				foreach ($arResult['PACK']['GOODS'] as $k=> $v)
				{
					?>
					<tr>
						<td width="80"><?=$v['GOOD_ID'];?></td>
						<td>
							<a href="/goods/lists.element.edit.php?list_id=62&section_id=0&element_id=<?=$v['GOOD_ID'];?>&list_section_id=" target="_blank">
								<?=$v['NAME'];?>
							</a>
						</td>
						<td><?=$v['ARTICLE'];?></td>
						<td><?=$v['COUNT'];?></td>
						<td><?=WeightFormat($v['WEIGHT']);?></td>
						<td><?=WeightFormat($v['WEIGHT']*$v['COUNT']);?></td>
						<td><?=CurrencyFormat($v['COST'],"RUU");?></td>
						<td><?=CurrencyFormat(($v['COST']*$v['COUNT']),"RUU");?></td>
					</tr>
					<?
					$counts = $counts + $v['COUNT'];
					$weighs = $weighs + $v['WEIGHT']*$v['COUNT'];
					$costs = $costs + $v['COST']*$v['COUNT'];
				}
				?>
				<tr>
					<td colspan="3" align="right"><strong><?=GetMessage("TABLE_HEAD_14");?></strong></td>
					<td align="right"><strong><?=$counts;?></strong></td>
					<td>&nbsp;</td>
					<td align="right"><strong><?=WeightFormat($weighs);?></strong></td>
					<td>&nbsp;</td>
					<td align="right"><strong><?=CurrencyFormat($costs,"RUU");?></strong></td>
				</tr>
			</tbody>
		</table>
		<?	
	}
	if (count($arResult['PACK']['HISTORY']) > 0)
	{
		?>
		<h4><?=GetMessage("HISTORY_TITLE");?></h4>
		<table cellpadding="3" cellspacing="0" border="1" bordercolor="#ccc" class="rows">
			<thead>
				<tr>
					<td><?=GetMessage("TABLE_HEAD_15");?></td>
					<td><?=GetMessage("TABLE_HEAD_16");?></td>
					<td><?=GetMessage("TABLE_HEAD_17");?></td>
					<td><?=GetMessage("TABLE_HEAD_18");?></td>
				</tr>
			</thead>
			<tbody>
			<?
			foreach ($arResult['PACK']['HISTORY'] as $h)
			{
				echo '
					<tr>
					<td>'.$h['DATE_CREATE'].'</td>
					<td>'.$h['NAME'].'</td>
					<td>'.$h['PROPERTY_COMPANY_NAME'].', '.$h['WHO']['LAST_NAME'].' '.$h['WHO']['NAME'].' ['.$h['MODIFIED_BY'].']</td>
					<td>'.$h['DETAIL_TEXT'].'</td>
					</tr>';
			}
			?>
			</tbody>
		</table>
		<?
	}
}