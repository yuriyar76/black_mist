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
			if ($arResult['PACK']['PROPERTY_STATE_ENUM_ID'] == 39) :?>
			<li class="nobg" style="color: #DE287A;">Статус заказа: <strong><?=$arResult['PACK']['PROPERTY_STATE_PACK'];?></strong></li>
			<?else :?>
            <li class="nobg"><a href="/warehouse/index.php?mode=package_print&id=<?=$_GET["id"];?>&pdf=Y" title="<?=GetMessage("PRINT_ICON_1");?>" target="_blank">
                <img src="/bitrix/components/black_mist/delivery.management/templates/.default/images/PDF-icon-20.png" width="20" height="20"></a>
            </li>
            <li class="nobg"><a href="print.php?ids=<?=$arResult['PACK']['ID'];?>" title="<?=GetMessage("PRINT_ICON_2");?>" target="_blank">
                <img src="/bitrix/components/black_mist/delivery.packages/templates/.default/images/print_icon.png" width="20" height="20"></a>
            </li>
            <li class="nobg">
            	<a href="/warehouse/index.php?mode=package_edit&id=<?=$_GET["id"];?>" title="<?=GetMessage("EDIT_ICON");?>">
                	<img src="/bitrix/templates/portal/images/edit_20.png" width="20" height="20">
                </a>
            </li>
			<li class="nobg">
            	<a href="/warehouse/index.php?mode=package_manifest&id=<?=$_GET["id"];?>&xls=Y" title="<?=GetMessage("MANIFESTOS_ICON");?>">
                	<img src="/bitrix/templates/portal/images/manifestos_20.png" width="20" height="20">
                </a>
            </li>
            <?endif;?>
            <li class="nobg">
            	<a href="/shops/index.php?mode=send_letter&id=<?=$arResult['PACK']['PROPERTY_CREATOR_VALUE'];?>" title="<?=GetMessage("LETTER_ICON");?>">
                	<img src="/bitrix/templates/portal/images/letter_20.png" width="20" height="20">
                </a>
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
else
{
	if (count($arResult["WARNINGS"]) > 0) 
	{
		echo '<p class="orange">'.implode('</br>',$arResult["WARNINGS"]).'</p>';
	}
}

if ($arResult['PACK'])
{
	?>
	<div class="status_block">
		<?=$arResult['PACK']['PROPERTY_STATE_PACK'];?>
	</div>
    <div style="width:53%; float:left; margin-right:2%;">
    
	<div class="table_group">
        <table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="rows">
            <tbody>
                <tr>
                    <td width="40%"><strong><?=GetMessage("LABEL_1");?></strong></td>
                    <td>
                    	<a href="/shops/index.php?mode=shop&id=<?=$arResult['PACK']['PROPERTY_CREATOR_VALUE'];?>" target="_blank"><?=$arResult['PACK']['PROPERTY_CREATOR_NAME'];?></a>
                    </td>
                </tr>
                <tr>
                    <td><strong><?=GetMessage("LABEL_2");?></strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_N_ZAKAZ_IN_VALUE'];?></td>
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
		<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="rows">
    		<tbody>
				<tr>
            		<td width="40%"><strong><?=GetMessage("LABEL_5");?></strong></td>
                	<td><?=$arResult['PACK']['PROPERTY_RECIPIENT_VALUE'];?></td>
				</tr>
				<tr>
                	<td><strong><?=GetMessage("LABEL_6");?></strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_PHONE_VALUE'];?></td>
				</tr>
				<tr>
                	<td><strong><?=GetMessage("LABEL_7");?></strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_CITY'];?></td>
				</tr>
				<tr>
                	<td><strong><?=GetMessage("LABEL_8");?></strong></td>
                    <td>
						<?
						if ($arResult['PACK']['PROPERTY_CONDITIONS_ENUM_ID'] == 38)
						{
							if (intval($arResult['PACK']['PROPERTY_PVZ_VALUE']) > 0)
							{
								echo GetMessage("PVZ").$arResult['PACK']['PVZ_NAME'];
							}
							else
							{
								echo $arResult['PACK']['PROPERTY_CONDITIONS_VALUE'];
							}
						}
						else
						{
							echo $arResult['PACK']['PROPERTY_ADRESS_VALUE'];
						}
						?>
					</td>
				</tr>
				<?
				if (strlen($arResult['PACK']['PROPERTY_WHEN_TO_DELIVER_VALUE']))
				{
					?>
					<tr>
                        <td><strong><?=GetMessage("LABEL_9");?></strong></td>
                        <td><?=$arResult['PACK']['PROPERTY_WHEN_TO_DELIVER_VALUE'];?></td>
					</tr>
					<?
				}
				if(strlen($arResult['PACK']['PROPERTY_PREFERRED_TIME_VALUE']["TEXT"]))
				{
					?>
					<tr>
                    	<td><strong><?=GetMessage("LABEL_10");?></strong></td>
                        <td><?=$arResult['PACK']['PROPERTY_PREFERRED_TIME_VALUE']["TEXT"];?></td>
					</tr>
					<?
				}
				if($arResult['PACK']['PROPERTY_URGENCY_ORDER_ENUM_ID'] == 172)
				{
					?>
					<tr>
                    	<td><strong><?=GetMessage("LABEL_11");?></strong></td>
                        <td><?=GetMessage("DOUBLE");?></td>
					</tr>
					<?
				}
				if($arResult['PACK']['PROPERTY_DELIVERY_LEGAL_VALUE'] == 1)
				{
					?>
					<tr>
                    	<td><strong><?=GetMessage("LABEL_32");?></strong></td>
                        <td><?=GetMessage("PODPIS_UR");?></td>
					</tr>
					<?
				}
				?>  
			</tbody>
	</table>
	</div>
	<div class="table_group">
		<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="rows">
			<tbody>
				<tr>
					<td width="40%"><strong><?=GetMessage("LABEL_12");?></strong></td>
					<td><?=WeightFormat($arResult['PACK']['PROPERTY_WEIGHT_VALUE']);?></td>
				</tr>
				<tr>
					<td><strong><?=GetMessage("LABEL_13");?></strong></td>
					<td><? echo $arResult['PACK']['PROPERTY_SIZE_1_VALUE'].'*'.$arResult['PACK']['PROPERTY_SIZE_2_VALUE'].'*'.$arResult['PACK']['PROPERTY_SIZE_3_VALUE'];?> <?=GetMessage("SM");?> (<?=WeightFormat(($arResult['PACK']['PROPERTY_SIZE_1_VALUE']*$arResult['PACK']['PROPERTY_SIZE_2_VALUE']*$arResult['PACK']['PROPERTY_SIZE_3_VALUE'])/$arResult['GAB_W']);?>)</td>
				</tr>
				<tr>
					<td><strong><?=GetMessage("LABEL_14");?></strong></td>
					<td><?=$arResult['PACK']['PROPERTY_PLACES_VALUE'];?></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="table_group">
		<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="rows">
            <tbody>
				<tr>
                    <td width="40%"><strong><?=GetMessage("LABEL_15");?></strong></td>
					<td><?=CurrencyFormat($arResult['PACK']['PROPERTY_COST_GOODS_VALUE'],"RUU");?></td>
				</tr>
				<tr>
					<td><strong><?=GetMessage("LABEL_16");?></strong></td>
					<td><?=CurrencyFormat($arResult['PACK']['PROPERTY_COST_3_VALUE'],"RUU");?></td>
				</tr>
				<tr>
					<td><strong><?=GetMessage("LABEL_17");?></strong></td>
					<td><?=CurrencyFormat($arResult['PACK']['PROPERTY_COST_2_VALUE'],"RUU");?></td>
				</tr>
				<tr>
					<td><strong><?=GetMessage("LABEL_18");?></strong></td>
					<td><?=CurrencyFormat($arResult['PACK']['PROPERTY_COST_1_VALUE'],"RUU");?></td>
				</tr>
				<tr>
					<td><strong><?=GetMessage("LABEL_19");?></strong></td>
					<td><?=$arResult['PACK']['PROPERTY_CASH_VALUE'];?></td>
				</tr>
				<?
				if (strlen($arResult['PACK']['PROPERTY_TYPE_PAYMENT_VALUE']))
				{
					?>
					<tr>
                        <td><strong><?=GetMessage("LABEL_20");?></strong></td>
                        <td><?=$arResult['PACK']['PROPERTY_TYPE_PAYMENT_VALUE'];?></td>
					</tr>
					<?
				}
				?>
				<tr>
					<td><strong><?=GetMessage("LABEL_34");?></strong></td>
					<td><?=($arResult['PACK']['PROPERTY_PAY_FOR_REFUSAL_VALUE'] == 1) ? GetMessage('YES') : GetMessage('NO');?></td>
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
                        <td width="40%"><strong><?=GetMessage("LABEL_21");?></strong></td>
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
		<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="rows">
			<tbody>
				<tr>
                    <td width="40%"><strong><?=GetMessage("LABEL_23");?></strong></td>
					<td><?=CurrencyFormat($arResult['PACK']['PROPERTY_RATE_VALUE'],"RUU");?></td>
				</tr>
				<tr>
					<td><strong><?=GetMessage("LABEL_24");?></strong></td>
					<td><?=CurrencyFormat(floatval($arResult['PACK']['PROPERTY_SUMM_SHOP_VALUE']),"RUU");?></td>
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
                        <td><?=CurrencyFormat($arResult['PACK']['PROPERTY_SUMM_ISSUE_VALUE'],"RUU");?></td>
                    </tr>
                    <?
				}
				if ($arResult['PACK']['PROPERTY_RETURN_VALUE'] == 1)
				{
					?>
					<tr>
						<td><strong><?=GetMessage("LABEL_36");?></strong></td>
                        <td><?=CurrencyFormat($arResult['PACK']['PROPERTY_COST_RETURN_VALUE'],"RUU");?></td>
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
						<td width="40%"><strong><?=GetMessage("LABEL_26");?></strong></td>
						<td><?=CurrencyFormat($arResult['PACK']['PROPERTY_RATE_AGENT_VALUE'],"RUU");?></td>
					</tr>
					<?
				} 
				if (floatval($arResult['PACK']['PROPERTY_SUMM_AGENT_VALUE']) > 0)
				{
					?>
					<tr>
						<td width="40%"><strong><?=GetMessage("LABEL_27");?></strong></td>
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
	if ( strlen($arResult['PACK']['PROPERTY_COURIER_NAME']) || strlen($arResult['PACK']['PROPERTY_COMMENTS_COURIER_VALUE']) || strlen($arResult['PACK']['PROPERTY_DATE_DELIVERY_VALUE']) || 
		($arResult['PACK']['PROPERTY_OBTAINED_VALUE']) || ($arResult['PACK']['PROPERTY_REPORT_VALUE']) || (count($arResult['PACK']['PROPERTY_MANIFEST_VALUE']) > 0))
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
							<td width="40%"><strong><?=GetMessage("LABEL_28");?></strong></td>
							<td><?=$arResult['PACK']['PROPERTY_COURIER_NAME'];?></td>
						</tr>
						<?
					} 
					if (strlen($arResult['PACK']['PROPERTY_COMMENTS_COURIER_VALUE']))
					{
						?>
						<tr>
							<td width="40%"><strong><?=GetMessage("LABEL_29");?></strong></td>
							<td><?=$arResult['PACK']['PROPERTY_COMMENTS_COURIER_VALUE'];?></td>
						</tr>
						<?
					} 
					if (strlen($arResult['PACK']['PROPERTY_DATE_DELIVERY_VALUE']))
					{
						?>
						<tr>
							<td width="40%"><strong><?=GetMessage("LABEL_30");?></strong></td>
							<td><?=$arResult['PACK']['PROPERTY_DATE_DELIVERY_VALUE'];?></td>
						</tr>
						<?
					}
					if ($arResult['PACK']['PROPERTY_OBTAINED_VALUE'])
					{
						?>
						<tr>
							<td width="40%"><strong><?=GetMessage('LABEL_35');?></strong></td>
							<td><?=CurrencyFormat($arResult['PACK']['PROPERTY_OBTAINED_VALUE'], 'RUU');?></td>
						</tr>
						<?
					}
					if ($arResult['PACK']['PROPERTY_REPORT_VALUE'])
					{
						?>
						<tr>
							<td colspan="2"><a href="http://dms.newpartner.ru/shops/index.php?id=<?=$arResult['PACK']['PROPERTY_CREATOR_VALUE'];?>&report_id=<?=$arResult['PACK']['PROPERTY_REPORT_VALUE'];?>&mode=report" target="_blank"><?=$arResult['PACK']['PROPERTY_REPORT_NAME'];?></a></td>
						</tr>
						<?
					}
					if (count($arResult['PACK']['PROPERTY_MANIFEST_VALUE']) > 0)
					{
						?>
                        <tr>
                        	<td width="40%"><strong>Манифесты</strong></td>
                        	<td>
                            <?
							$w_m = array();
							foreach ($arResult['PACK']['PROPERTY_MANIFEST_VALUE'] as $m)
							{
								$w_m[] = '<a href="/manifesty/index.php?mode=manifest&id='.$m.'">'.$arResult['PACK']['NAME_OF_MAN'][$m].'</a>';
							}
							echo implode(', ',$w_m);
							?>
                            </td>
                        </tr>
                        <?
					}
					?>
				</tbody>
			</table>
		</div>
		<?
	}
	/*
	if ($arResult['PACK']['PROPERTY_STATE_ENUM_ID'] == 54)
	{
		?>
		<h4><?=GetMessage("LABEL_31");?></h4>
		<form action="" method="post">

			<p>
                <input type="hidden" name="id" value="<?=$arResult['PACK']['ID'];?>" />
				<input type="hidden" name="city" value="<?=$arResult['PACK']['PROPERTY_CITY_VALUE'];?>" />
				<input type="hidden" name="conditions" value="<?=$arResult['PACK']['PROPERTY_CONDITIONS_ENUM_ID'];?>" />
                <input type="hidden" name="number" value="<?=$arResult['PACK']['PROPERTY_N_ZAKAZ_IN_VALUE'];?>">
				<input type="submit" name="departure_package" value="<?=GetMessage("BTN_1");?>" />
				<input type="submit" name="reject_package" value="<?=GetMessage("BTN_2");?>" />
			</p>
		</form>
		<?
	} 
	*/
	
	?>
    </div>
    <div style="width:45%; float:left;">
    	<?
		if ($arResult['PACK']['PROPERTY_END_VALUE'] != 1
			// (!in_array($arResult['PACK']['PROPERTY_STATE_ENUM_ID'], $arParams['STATUS_CHANCEL_NOT'])) 
			// || ($arResult['PACK']['PROPERTY_STATE_ENUM_ID'] == 126)
			// || ($arResult['PACK']['PROPERTY_STATE_ENUM_ID'] == 54)
		)
		{
			?>
            <div class="buttons_group marjbot">
            	<form action="" method="post">
					<input type="hidden" name="rand" value="<?=rand(100000,999999);?>">
					<input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>">
                    <input type="hidden" name="pack_id" value="<?=$arResult['PACK']['ID'];?>">
                    <input type="hidden" name="number_pack" value="<?=$arResult['PACK']['PROPERTY_N_ZAKAZ_IN_VALUE'];?>">
                    <input type="hidden" name="shop" value="<?=$arResult['PACK']['PROPERTY_CREATOR_VALUE'];?>">
                    <input type="hidden" name="city" value="<?=$arResult['PACK']['PROPERTY_CITY_VALUE'];?>">
                    <input type="hidden" name="conditions" value="<?=$arResult['PACK']['PROPERTY_CONDITIONS_ENUM_ID'];?>">
                    <input type="hidden" name="goods_count" value="<?=count($arResult['PACK']['GOODS']);?>">
                    <textarea placeholder="Введите причину или комментарий к совершаемому действию" name="comment"></textarea>
                    <?
					if ($arResult['PACK']['PROPERTY_STATE_ENUM_ID'] == 126)
					{
						?>
                        <input type="submit" name="submit_for_delivery" value="<?=GetMessage("BTN_3");?>"/>
                        <?
					}
					/*
					if ($arResult['PACK']['PROPERTY_STATE_ENUM_ID'] == 54)
					{
						?>
                        <input type="submit" name="departure_package" value="<?=GetMessage("BTN_1");?>" />
						<input type="submit" name="reject_package" value="<?=GetMessage("BTN_2");?>" />
                        <?
					}
					*/
					if (!in_array($arResult['PACK']['PROPERTY_STATE_ENUM_ID'],$arParams['STATUS_CHANCEL_NOT']))
					{
						if (count($arResult['PACK']['GOODS']) > 0)
						{
							?>
                            <input type="submit" name="return" value="Вернуть товары на склад">
                            <?
						}
						?>
                        <input type="submit" name="cancel" value="<?=GetMessage("BTN_4");?>">
                        <?
					}
					?>
                </form>
            </div>
            <?
		}
		elseif ($arResult['PACK']['PROPERTY_END_VALUE'] == 1)
		{
			?>
            <strong>Заказ завершен</strong><BR>
            <?
		}
		if (count($arResult['PACK']['GOODS']) > 0)
		{
			?>
            <div class="marjbot">
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
							<td><?=$v['NAME'];?></td>
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
            </div>
			<?	
		}
		if ((count($arResult['PACK']['PACK_GOODS']) > 0) && (is_array($arResult['PACK']['PACK_GOODS']))) :?>
        <div class="marjbot">
			<h4>Товары заказа </h4>
			<table cellpadding="3" cellspacing="0" border="1" bordercolor="#ccc" class="rows" width="100%">
				<thead>
					<tr>
						<th>Наименование товара</th>
						<th>Количество, шт.</th>
						<th>Цена за 1 шт., включая НДС, руб.</th>
						<th>Сумма, включая НДС, руб.</th>
						<th>Сумма НДС, руб.</th>
						<th>Ставка НДС</th>
					</tr>
				</thead>
				<tbody>
					<? foreach ($arResult['PACK']['PACK_GOODS'] as $k => $v) : ?>
						<tr id="addr<?=$k;?>">
							<td><?=$v['GoodsName'];?></td>
							<td><?=$v['Amount'];?></td>
							<td><?=$v['Price'];?></td>
							<td><?=$v['Sum'];?></td>
							<td><?=$v['SumNDS'];?></td>
							<td><?=$v['PersentNDS'];?>%</td>
						</tr>
					<? endforeach;?>
				</tbody>
			</table>
		</div>
		<?endif;
		if (count($arResult['PACK']['HISTORY']) > 0)
		{
			?>
            <div class="marjbot">
			<h4><?=GetMessage("HISTORY_TITLE");?></h4>
			<table cellpadding="3" cellspacing="0" border="1" bordercolor="#ccc" class="rows" width="100%">
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
            </div>
			<?
		}
		
		if (in_array($arResult['CURRENT_COMPANY'], array(2197189, 5873349)))
		{
			?>
			<h4>Данные по заказу в 1с</h4>
			<?
			$_GET['f001'] = $arResult['PACK']['PROPERTY_N_ZAKAZ_IN_VALUE'];
			$APPLICATION->IncludeComponent(
				"black_mist:delivery.get_pods", 
				"newpartner", 
				array(
					"SHOW_FORM" => "N",
					"CACHE_TYPE" => "A",
					"CACHE_TIME" => "3600",
					"SAVE_TO_SITE" => "Y",
					"SET_TITLE" => 'N',
					'SHOW_TITLE' => 'N'
				),
				false
			);

		}
		?>
    </div>
    <br class="clear">
    <?
}
?>
<br>