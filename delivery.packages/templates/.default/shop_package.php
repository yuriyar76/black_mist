<div class="new_menu">
    <ul>
        <li class="active"><a href="javascript:void(0);"><?=$arResult["TITLE"];?></a></li>
        <?
        foreach ($arResult["MENU"] as $k => $v) {
            ?>
            <li><a href="index.php?mode=<?=$k?>"><?=$v;?></a></li>
            <?
        }
        ?>
        <li class="nobg">
        	<a href="print.php?ids=<?=$arResult['PACK']['ID'];?>" title="����������� ��������" target="_blank">
            	<img src="/bitrix/components/black_mist/delivery.packages/templates/.default/images/print_icon.png" width="20" height="20">
            </a>
        </li>
    </ul>
</div>

<?
if ($arResult['PACK'])
{
	?> 
    <div class="table_group">
		<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="rows">
        	<tbody>
				<tr>
                	<td width="350"><strong>���� ��������</strong></td>
                    <td><?=$arResult['PACK']['DATE_CREATE'];?></td>
				</tr>
				<tr>
                	<td><strong>����� ������ DMS</strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_N_ZAKAZ_IN_VALUE'];?></td>
				</tr>
				<tr>
                	<td><strong>���������� ����� ������</strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_N_ZAKAZ_VALUE'];?></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="table_group">
		<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="rows">
        	<tbody>
				<tr>
                	<td width="350"><strong>����������</strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_RECIPIENT_VALUE'];?></td>
				</tr>
				<tr>
                	<td><strong>����� ��������</strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_PHONE_VALUE'];?></td>
				</tr>
				<tr>
                	<td><strong>����� ����������</strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_CITY'];?></td>
				</tr>
				<tr>
                	<td><strong>�����</strong></td>
                    <td>
						<?
						if ($arResult['PACK']['PROPERTY_CONDITIONS_ENUM_ID'] == 38)
						{
							if (intval($arResult['PACK']['PROPERTY_PVZ_VALUE']) > 0)
							{
								echo '��� '.$arResult['PACK']['PVZ_NAME'];
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
						<td><strong>���������</strong></td>
						<td><?=$arResult['PACK']['PROPERTY_WHEN_TO_DELIVER_VALUE'];?></td>
					</tr>
					<?
				}
				if (strlen($arResult['PACK']['PROPERTY_PREFERRED_TIME_VALUE']["TEXT"]))
				{
					?>
					<tr>
                        <td><strong>����������� � ������</strong></td>
						<td><?=$arResult['PACK']['PROPERTY_PREFERRED_TIME_VALUE']["TEXT"];?></td>
					</tr>
					<?
				}
				if ($arResult['PACK']['PROPERTY_URGENCY_ORDER_ENUM_ID'] == 172)
				{
					?>
					<tr>
						<td><strong>��������� ������</strong></td>
						<td>��, ������� �����</td>
					</tr>
					<?
				} 
				if ($arResult['PACK']['PROPERTY_DELIVERY_LEGAL_VALUE'] == 1)
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
                    <td width="350"><strong>���</strong></td>
                    <td><?=WeightFormat($arResult['PACK']['PROPERTY_WEIGHT_VALUE']);?></td>
                </tr>
                <tr>
                    <td><strong>��������</strong></td>
                    <td>
                        <?
                        echo $arResult['PACK']['PROPERTY_SIZE_1_VALUE'].'*'.$arResult['PACK']['PROPERTY_SIZE_2_VALUE'].'*'.$arResult['PACK']['PROPERTY_SIZE_3_VALUE'];
                        ?>
                        �� 
                        (<?=WeightFormat(($arResult['PACK']['PROPERTY_SIZE_1_VALUE']*$arResult['PACK']['PROPERTY_SIZE_2_VALUE']*$arResult['PACK']['PROPERTY_SIZE_3_VALUE'])/$arResult['GAB_W']);?>)
                    </td>
                </tr>
                <tr>
                    <td><strong>���������� ����</strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_PLACES_VALUE'];?></td>
                </tr>
            </tbody>
        </table>
	</div>

	<div class="table_group">
		<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="rows">
        	<tbody>
				<tr>
                	<td width="350"><strong>��������� ������</strong></td>
                    <td><?=CurrencyFormat($arResult['PACK']['PROPERTY_COST_GOODS_VALUE'],"RUU");?></td>
				</tr>
				<tr>
                	<td><strong>�� ��������</strong></td>
                    <td><?=CurrencyFormat($arResult['PACK']['PROPERTY_COST_3_VALUE'],"RUU");?></td>
				</tr>
				<tr>
                	<td><strong>����� � ������</strong></td>
                    <td><?=CurrencyFormat($arResult['PACK']['PROPERTY_COST_2_VALUE'],"RUU");?></td>
				</tr>
				<tr>
                	<td><strong>��������� ��������� ������</strong></td>
                    <td><?=CurrencyFormat($arResult['PACK']['PROPERTY_COST_1_VALUE'],"RUU");?></td>
				</tr>
				<tr>
                	<td><strong>�������� ������������</strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_CASH_VALUE'];?></td>
				</tr>
				<?
				if (strlen($arResult['PACK']['PROPERTY_TYPE_PAYMENT_VALUE']))
				{
					?>
					<tr>
                    	<td><strong>��� ������</strong></td>
                        <td><?=$arResult['PACK']['PROPERTY_TYPE_PAYMENT_VALUE'];?></td>
					</tr>
					<?
				}
				?>
				<tr>
					<td><strong>����� c ���������� ������ �� �������� ��� ������</strong></td>
					<td>
					<?=($arResult['PACK']['PROPERTY_PAY_FOR_REFUSAL_VALUE'] == 1) ? GetMessage('YES') : GetMessage('NO');?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	
	<?
	if ($arResult['PACK']["PROPERTY_TAKE_PROVIDER_ENUM_ID"] == 174)
	{
		?>
		<div class="table_group">
			<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="rows">
            	<tbody>
					<tr>
                    	<td width="350"><strong>������� � ����������</strong></td>
                        <td>
							<?=$arResult['PACK']['PROPERTY_TAKE_DATE_VALUE'];?>
							<?
							if (count($arResult['PACK']["ZABORS"]) > 0)
							{
								?>
								<table cellpadding="3" cellspacing="0" border="1" bordercolor="#ccc" class="rows">
									<thead>
										<tr>
                                            <td>������������</td>
                                            <td>�������</td>
                                            <td>���</td>
                                            <td>����������</td>
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
                        	<td><strong>�����������</strong></td>
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
                	<td width="350"><strong>��������� ��������������</strong></td>
                    <td><?=CurrencyFormat($arResult['PACK']['PROPERTY_RATE_VALUE'],"RUU");?></td>
				</tr>
				<tr>
                	<td><strong>��������� ��������</strong></td>
                    <td><?=CurrencyFormat(floatval($arResult['PACK']['PROPERTY_SUMM_SHOP_VALUE']),"RUU");?></td>
				</tr>
				<?
				if (count($arResult['PACK']['GOODS']) > 0)
				{
					?>
					<tr>
						<td><strong>������������ ������</strong></td>
                        <td><?=CurrencyFormat($arResult['PACK']['PROPERTY_SUMM_ISSUE_VALUE'],"RUU");?></td>
                    </tr>
                    <?
				}
				if ($arResult['PACK']["PROPERTY_SUMM_SHOP_ZABOR_VALUE"] > 0)
				{
					?>
					<tr>
                    	<td><strong>� ��� ����� �� �����</strong></td>
                        <td><?=CurrencyFormat($arResult['PACK']["PROPERTY_SUMM_SHOP_ZABOR_VALUE"],"RUU");?></td>
					</tr>
					<?
				}
				if ($arResult['PACK']["PROPERTY_CALL_COURIER_VALUE"])
				{
					?>
					<tr>
						<td><strong>������ �� ����� �������</strong></td>
						<td>
                        	<a href="/suppliers/index.php?mode=call_courier&id=<?=$arResult['PACK']["PROPERTY_CALL_COURIER_VALUE"];?>" target="_blank">
								<?=$arResult['PACK']["CALL_COURIER"];?>
							</a>
                        </td>
					</tr>
					<?
				}
				?>
			</tbody>
		</table>
	</div>
	
	<?
	if (strlen($arResult['PACK']['PROPERTY_DATE_DELIVERY_VALUE']))
	{
		?>
		<div class="table_group">
			<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="rows">
            	<tbody>
					<tr>
                    	<td width="350"><strong>���� � ����� ��������</strong></td>
                        <td><?=$arResult['PACK']['PROPERTY_DATE_DELIVERY_VALUE'];?></td>
					</tr>
						<tr>
							<td><strong>�������� � �������</strong></td>
							<td><?=CurrencyFormat($arResult['PACK']['PROPERTY_OBTAINED_VALUE'], 'RUU');?></td>
						</tr>

				</tbody>
			</table>
		</div>
		<?
	}
	?>
    
    <div class="status_block">
		<?=$arResult['PACK']['PROPERTY_STATE_SHORT_VALUE'];?>
	</div>
	
	<?			
	if (count($arResult['PACK']['GOODS']) > 0)
	{
		?>
		<h4 style="position:relative; height:30px; line-height:30px;">
        	������ ������ 
            <a href="xls.php?id=<?=$arResult['PACK']['ID'];?>" style=" display:block; width:30px; height:30px; position:absolute; top:0; right:0;">
            	<img src="/bitrix/templates/portal/images/excel_ico.jpg" width="30" height="30" alt="" />
			</a>
		</h4>
		<table cellpadding="3" cellspacing="0" border="1" bordercolor="#ccc" class="rows" width="100%">
			<thead>
            	<tr>
                	<td width="30">�</td>
                    <td>������������</td>
                    <td>����������</td>
                    <td>���</td>
                    <td>��������� ���</td>
                    <td>����</td>
                    <td>���������</td>
				</tr>
			</thead>
			<tbody>
				<?
				$counts = $weighs = $costs = $i = 0;
				foreach ($arResult['PACK']['GOODS'] as $k=> $v)
				{
					$i++;
					?>
					<tr>
                    	<td><?=$i;?>.</td>
                        <td>
							<a href="/goods/lists.element.edit.php?list_id=62&section_id=0&element_id=<?=$v['GOOD_ID'];?>&list_section_id=" target="_blank">
								<?=$v['NAME'];?>
							</a>
						</td>
                        <td><?=$v['COUNT'];?></td>
                        <td><?=$v['WEIGHT'];?> ��</td>
                        <td><?=($v['WEIGHT']*$v['COUNT']);?> ��</td>
                        <td><?=$v['COST'];?> ���.</td>
                        <td><?=($v['COST']*$v['COUNT']);?> ���.</td>
					</tr>
					<?
					$counts = $counts + $v['COUNT'];
					$weighs = $weighs + $v['WEIGHT']*$v['COUNT'];
					$costs = $costs + $v['COST']*$v['COUNT'];
				}
				?>
				<tr>
                	<td colspan="2" align="right"><strong>�����:</strong></td>
                    <td align="right"><strong><?=$counts;?></strong></td>
                    <td>&nbsp;</td>
                    <td align="right"><strong><?=$weighs;?> ��</strong></td>
                    <td>&nbsp;</td>
                    <td align="right"><strong><?=$costs;?> ���.</strong></td>
				</tr>
			</tbody>
		</table>
		<?	
	}			
	if ((count($arResult['PACK']['PACK_GOODS']) > 0) && (is_array($arResult['PACK']['PACK_GOODS']))) :?>
		<h4>������ ������ </h4>
		<table cellpadding="3" cellspacing="0" border="1" bordercolor="#ccc" class="rows" width="100%">
			<thead>
				<tr>
					<th>������������ ������</th>
					<th>����������, ��.</th>
					<th>���� �� 1 ��., ������� ���, ���.</th>
					<th>�����, ������� ���, ���.</th>
					<th>����� ���, ���.</th>
					<th>������ ���</th>
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
	<?endif;
	if (count($arResult['PACK']['SHORT_HISTORY']) > 0)
	{
		?>
		<h4>������� �� ������</h4>
		<table cellpadding="3" cellspacing="0" border="1" bordercolor="#ccc" class="rows">
			<thead>
            	<tr>
                	<td>����</td>
                    <td>������</td>
                    <td>�����������</td>
                    <td>��� �������</td>
				</tr>
			</thead>
			<tbody>
				<?
				foreach ($arResult['PACK']['SHORT_HISTORY'] as $h)
				{
					echo '
					<tr>
					<td>'.$h['DATE_CREATE'].'</td>
					<td>'.$h['NAME'].'</td>
					<td>'.$h['DETAIL_TEXT'].'</td>
					<td>'.$h['WHO']['LAST_NAME'].' '.$h['WHO']['NAME'].' ['.$h['MODIFIED_BY'].']</td>
					
					</tr>';
				}
				?>
			</tbody>
		</table>
		<?
	}
}
?>