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
	
	$(document).ready(function()
	{
		CalculateCostOfDelivery();
		// CalDelivery();
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
	if ($arResult['EDIT'])
	{
		?>
		<form action="" method="post">
			<input type="hidden" name="rand" value="<?=rand(100000,999999);?>" />
			<input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>" />
			<input type="hidden" name="pack_id" value="<?=$arResult['PACK']["ID"];?>">
			<input type="hidden" name="shop_id" value="<?=$arResult['PACK']["PROPERTY_CREATOR_VALUE"];?>">
   		<?
	}
	?> 

	<p id="inser"><?=$arResult["INFO"];?></p>
    <table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="<? echo $arResult['EDIT'] ? 'borders date_icon_in': 'rows';?>">
    	<tbody>
            <tr>
                <td width="200"><strong>����</strong></td>
                <td><?=$arResult['PACK']['DATE_CREATE'];?></td>
            </tr>
			<tr>
                    <td><strong>���������� ����� ������</strong></td>
                    <td>
                    	<?
						if ($arResult['EDIT'])
						{
							?>
                            <input type="text" name="PROPERTY_N_ZAKAZ" value="<?=$arResult['PACK']['PROPERTY_N_ZAKAZ_VALUE'];?>">
                            <?
						}
						else
						{
							echo $arResult['PACK']['PROPERTY_N_ZAKAZ_VALUE'];
						}
						?>
					</td>
                </tr>
            <tr>
                <td><strong>���</strong></td>
                <td><input type="hidden" name="PROPERTY_WEIGHT_VALUE" value="<?=$arResult['PACK']['PROPERTY_WEIGHT_VALUE'];?>" id="weight"><?=$arResult['PACK']['PROPERTY_WEIGHT_VALUE'];?> ��</td>
            </tr>
            <tr>
                <td><strong>��������</strong></td>
                <td>
					<?
					echo $arResult['EDIT'] ? '
						<input type="text" name="PROPERTY_SIZE_1_VALUE" value="'.$arResult['PACK']['PROPERTY_SIZE_1_VALUE'].'" placeholder="�����" class="small_inp" id="size_1" 
							onChange="CalculateCostOfDelivery();" onKeyDown="CalculateCostOfDelivery();">  
						<input type="text" name="PROPERTY_SIZE_2_VALUE" value="'.$arResult['PACK']['PROPERTY_SIZE_2_VALUE'].'" placeholder="������" class="small_inp" id="size_2" 
							onChange="CalculateCostOfDelivery();" onKeyDown="CalculateCostOfDelivery();">  
						<input type="text" name="PROPERTY_SIZE_3_VALUE" value="'.$arResult['PACK']['PROPERTY_SIZE_3_VALUE'].'" placeholder="������" class="small_inp" id="size_3" 
							onChange="CalculateCostOfDelivery();" onKeyDown="CalculateCostOfDelivery();">' 
					: 
						$arResult['PACK']['PROPERTY_SIZE_1_VALUE'].'*'.$arResult['PACK']['PROPERTY_SIZE_3_VALUE'].'*'.$arResult['PACK']['PROPERTY_SIZE_3_VALUE'];
					?> ��
				</td>
			</tr>
			<tr>
            	<td><strong>���������� ����</strong></td>
				<td>
					<?
					if ($arResult['EDIT'])
					{ 
						echo '<input type="text" name="PROPERTY_PLACES_VALUE" value="';
						echo strlen($arResult['PACK']['PROPERTY_PLACES_VALUE']) ? $arResult['PACK']['PROPERTY_PLACES_VALUE'] : '1';
						echo '">'; 
					}
					else
					{
						echo $arResult['PACK']['PROPERTY_PLACES_VALUE'];
					}
					?>
				</td>
			</tr>
			<tr>
            	<td><strong>������� ��������</strong></td>
                <td>
					<?
					if ($arResult['EDIT'])
					{
						?>
						<select name="PROPERTY_CONDITIONS_ENUM_ID" size="1" onchange="Disabled(); CalculateCostOfDelivery();" id="conditions">
                        	<option value="0"></option>
							<option value="37"<? echo ($arResult['PACK']['PROPERTY_CONDITIONS_ENUM_ID'] == 37) ? ' selected' : ''; ?>>�� ������</option>
							<option value="38"<? echo ($arResult['PACK']['PROPERTY_CONDITIONS_ENUM_ID'] == 38) ? ' selected' : ''; ?>>���������</option>
						</select>
						<?
					}
					else
					{
						echo $arResult['PACK']['PROPERTY_CONDITIONS_VALUE'];
					}
					?>
				</td>
			</tr>
			<tr>
            	<td><strong>��� ����������</strong></td>
                <td>
					<?
					echo $arResult['EDIT'] ? 
						'<input type="text" name="PROPERTY_RECIPIENT_VALUE" value="'.$arResult['PACK']['PROPERTY_RECIPIENT_VALUE'].'">' 
					: 
						$arResult['PACK']['PROPERTY_RECIPIENT_VALUE'];
					?>
				</td>
			</tr>
			<tr>
            	<td><strong>����� �������� ����������</strong></td>
                <td>
					<?
					echo $arResult['EDIT'] ? 
						'<input type="text" name="PROPERTY_PHONE_VALUE" value="'.$arResult['PACK']['PROPERTY_PHONE_VALUE'].'">' 
					: 
						$arResult['PACK']['PROPERTY_PHONE_VALUE'];
					?>
				</td>
			</tr>
			<tr>
            	<td><strong>����� ����������</strong></td>
                <td>
					<?
					if ($arResult['EDIT'])
					{
						echo '<input type="text" name="PROPERTY_CITY" value="';
						echo strlen($arResult['PACK']['CITY_NAME']) ? $arResult['PACK']['CITY_NAME'] : $arResult["SHOP_DEFAULT"]['city'];
						echo '" id="city_price_out">
							<input type="hidden" name="price" value="'.$arResult["PRICE"].'" id="price">
							<input type="hidden" name="price_2" value="'.$arResult["PRICE_2"].'" id="price_2">
							<input type="hidden" name="city_id" value="';
						echo strlen($arResult['PACK']['PROPERTY_CITY_VALUE']) ? $arResult['PACK']['PROPERTY_CITY_VALUE'] : $arResult["SHOP_DEFAULT"]['city_id'];
						echo '" id="city_id">
							<input type="hidden" name="persent_1" value="" id="persent_1">
							<input type="hidden" name="persent_2" value="" id="persent_2">';
					}
					else
					{
						echo $arResult['PACK']['PROPERTY_CITY'];
					}
					?>
				</td>
			</tr>
			<tr class="blocy" style="display:none;">
            	<td><strong>����� ��������</strong></td>
                <td>
					<?
					if ($arResult['EDIT'])
					{
						echo '<textarea name="PROPERTY_ADRESS_VALUE">'.$arResult['PACK']['PROPERTY_ADRESS_VALUE'].'</textarea>';
					}
					else
					{
						$adr = ($arResult['PACK']['PROPERTY_CONDITIONS_ENUM_ID'] == 38) ? $arResult['PACK']['PROPERTY_CONDITIONS_VALUE'] : $arResult['PACK']['PROPERTY_ADRESS_VALUE']; echo $adr;
					}
					?>
				</td>
			</tr>
			<tr class="blocy" style="display:none;">
            	<td><strong>����� ���������</strong></td>
                <td>
					<? 
					if  ($arResult['EDIT'])
					{
						$APPLICATION->IncludeComponent(
							"bitrix:main.calendar", 
							".default", 
							array(
								"SHOW_INPUT" => "Y",
								"FORM_NAME" => "curform",
								"INPUT_NAME" => "date_deliv",
								"INPUT_NAME_FINISH" => false,
								"INPUT_VALUE" => $arResult['PACK']["DELIV_DATE"],
								"INPUT_VALUE_FINISH" => false,
								"SHOW_TIME" => "N",
								"HIDE_TIMEBAR" => "Y",
								"INPUT_ADDITIONAL_ATTR" => 'placeholder="��.��.����" class="small_inp date"'
							),
							false
						);
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
                            </select>
						<?
					}
					else
					{
						echo $arResult['PACK']['PROPERTY_WHEN_TO_DELIVER_VALUE'];
					}
					?>
				</td>
            </tr>
			<tr>
				<td><strong>������� �����</strong>&nbsp;<sup><a href="javascript:void(0);" class="help" title="<?=GetMessage("HELP_2");?>">?</a></sup></td>
                <td>
					<?
					if ($arResult['EDIT'])
					{
						?>
						<input type="checkbox" name="urgent" value="2" onChange="CalculateCostOfDelivery();" id="urgent" 
							<?=($arResult['PACK']['PROPERTY_URGENCY_ORDER_ENUM_ID'] == 172) ? ' checked' : '';?>
						>
						<?
					}
					else
					{
						echo $arResult['PACK']['PROPERTY_URGENCY_VALUE'];
					}
					?>
				</td>
			</tr>
			<tr>
				<td><strong>�������� ��. ����</strong>&nbsp;<sup><a href="javascript:void(0);" class="help" title="<?=GetMessage("HELP_4");?>">?</a></sup></td>
				<td><input type="checkbox" name="to_legal" value="1" <?=($arResult['PACK']['PROPERTY_DELIVERY_LEGAL_VALUE'] ==  1) ? ' checked' : ''; ?>></td>
            </tr>
			<tr>
            	<td><strong>��������� ������</strong>&nbsp;<sup><a href="javascript:void(0);" class="help" title="<?=GetMessage("HELP_5");?>">?</a></sup></td>
                <td>
					<?=$arResult['PACK']['PROPERTY_COST_GOODS_VALUE'];?> ���.
					<input type="hidden" name="cost_goods_hid" id="cost_goods_hid" value="<?=$arResult['PACK']['PROPERTY_COST_GOODS_VALUE'];?>">
				</td>
			</tr>
			<tr>
            	<td><strong>��������� ��������</strong>&nbsp;<sup><a href="#" class="help" title="<?=GetMessage("HELP_3");?>">?</a></sup></td>
                <td>
                	<?=$arResult['EDIT'] ? 
						'<input type="text" name="PROPERTY_COST_3_VALUE" value="'.$arResult['PACK']['PROPERTY_COST_3_VALUE'].'" id="cost_3" onChange="CalDelivery(); ReCalcRate();">' 
					: 
						$arResult['PACK']['PROPERTY_COST_3_VALUE'];
					?> ���.
				</td>
			</tr>
			<tr>
            	<td><strong>����� � ������</strong></td>
                <td>
					<?
					if ($arResult['EDIT'])
					{
						?>
						<input type="text" name="PROPERTY_COST_2_VALUE" value="<?=$arResult['PACK']['PROPERTY_COST_2_VALUE'];?>" id="cost_2" onKeyUp="ReCalcRate();" onChange="ReCalcRate();">
						<?
					}
					else
					{
						echo $arResult['PACK']['PROPERTY_COST_2_VALUE'];
					}
					?> ���.
				</td>
			</tr>
            <!--
			<tr>
            	<td><strong>��������� ��������� ������</strong></td>
                <td>
					<?=$arResult['EDIT'] 
					? 
						'<input type="text" name="PROPERTY_COST_1_VALUE" value="'.$arResult['PACK']['PROPERTY_COST_1_VALUE'].'" id="cost_1" onKeyUp="ReCalcRate();" onChange="ReCalcRate();">' 
					: 
					$arResult['PACK']['PROPERTY_COST_1_VALUE'];
					?> ���.
				</td>
			</tr> 
            -->
			<tr>
            	<td><strong>�������� ������������?</strong></td>
                <td>
					<?
					if ($arResult['EDIT'])
					{ 
						if ($arResult['PACK']['PROPERTY_CASH_ENUM_ID'] > 0)
						{
							$a = $arResult['PACK']['PROPERTY_CASH_ENUM_ID'];
						}
						else
						{
							if ($arResult["SHOP_DEFAULT"]['cash'] > 0)
							{
								if ($arResult["SHOP_DEFAULT"]['cash'] == 122)
								{
									$a = 124;
								}
								if ($arResult["SHOP_DEFAULT"]['cash'] == 123)
								{
									$a = 125;
								}
							}
							else
							{
								$a = 124;
							}
						}
						?>
						<select name="PROPERTY_CASH_VALUE" size="1" id="cash" onChange="ReCalcRate();">
							<option value="124"<? echo ($a == 124) ? ' selected' : ''; ?>>��</option>
							<option value="125"<? echo ($a == 125) ? ' selected' : ''; ?>>���</option>
						</select>
						<?
                    }
                    else
                    {
                        echo $arResult['PACK']["PROPERTY_CASH_VALUE"];
                    }
                    ?>
                </td>
            </tr>
			<tr>
				<td><strong>����� c ���������� ������ �� �������� ��� ������</strong></td>
				<td>
                	<?
					if ($arResult['EDIT'])
					{
						?>
						<input type="checkbox" name="refusal" value="1" <? echo ($arResult['PACK']['PROPERTY_PAY_FOR_REFUSAL_VALUE'] == 1) ? ' checked' : ''; ?>>
                        <?
					}
					else
					{
						echo ($arResult['PACK']['PROPERTY_PAY_FOR_REFUSAL_VALUE'] == 1) ? '��' : '���';
					}
					?>
				</td>
			</tr>
            <tr>
                <td>
                    <strong>��������� ��������������</strong>
                    <?
                    if ($arResult['EDIT'])
                    {
                        ?>
                        &nbsp;<sup><a href="#" class="help" title="<?=GetMessage("HELP_1");?>">?</a></sup>
                        <?
                    }
                    ?>
                </td>
                <td>
                    <?
                    if ($arResult['EDIT'])
                    {
                        foreach ($arResult["RATE"] as $k=> $v)
                        {
                            echo '<input type="hidden" name="rate['.$k.']" value="'.$v.'" id="rate_'.$k.'">';
                        }
                        echo 
                            '
							<input type="hidden" name="CONDITIONS_IM" value="'.$arResult['CONDITIONS_IM'].'" id="conditions_im">
							<span id="rate_value_new">0</span>% - 
                            <input type="hidden" name="rate" value="0" id="rate">
                            <input type="hidden" name="PROPERTY_RATE_VALUE" value="'.$arResult['PACK']['PROPERTY_RATE_VALUE'].'" id="rate_value">'; 
                    }
                    ?>
                    <span id="rate_value_span"><?=$arResult['PACK']['PROPERTY_RATE_VALUE'];?></span> ���.
                </td>
            </tr>
            <?
            if (!$arResult['EDIT'])
            {
                ?>
                <tr>
                    <td><strong>��������� ��������</strong></td>
                    <td><?=floatval($arResult['PACK']['PROPERTY_SUMM_SHOP_VALUE']);?> ���.</td>
                </tr>
                <?
            }
            if ($arResult['EDIT'])
            { 
                if ($arResult['PACK']['PROPERTY_CONDITIONS_ENUM_ID'] > 0)
                {
                    $a = $arResult['PACK']['PROPERTY_CONDITIONS_ENUM_ID'];
                }
                else
                {
                    if ($arResult["SHOP_DEFAULT"]['delivery'] > 0)
                    {
                        if ($arResult["SHOP_DEFAULT"]['delivery'] == 120)
                        {
                            $a = 37;
                        }
                        if ($arResult["SHOP_DEFAULT"]['delivery'] == 121)
                        {
                            $a = 38;
                        }
                    }
                }
            }
            ?>
            <tr>
                <td><strong>����������� � ������</strong></td>
                <td>
                    <?
                    if ($arResult['EDIT'])
                    {
                        echo '<textarea name="PROPERTY_PREFERRED_TIME_VALUE">'.$arResult['PACK']['PROPERTY_PREFERRED_TIME_VALUE']["TEXT"].'</textarea>';
                    }
                    else
                    {
                        echo $arResult['PACK']['PROPERTY_PREFERRED_TIME_VALUE']["TEXT"];
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td><strong>������</strong></td>
                <td><?=$arResult['PACK']['PROPERTY_STATE_VALUE'];?></td>
            </tr>
            <?
            if(strlen($arResult['PACK']['PROPERTY_COURIER_NAME']))
            {
                ?>
                <tr>
                    <td><strong>������</strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_COURIER_NAME'];?></td>
                </tr>
                <?
            } 
            if (strlen($arResult['PACK']['PROPERTY_DATE_DELIVERY_VALUE']))
            {
                ?>
                <tr>
                    <td><strong>���� � ����� ��������</strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_DATE_DELIVERY_VALUE'];?></td>
                </tr>
                <?
            }
            ?>
        </tbody>
    </table>
	<?		
	if (count($arResult['PACK']['GOOS']) > 0)
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
					<?
					if ($arResult['EDIT'])
					{
						?>
						<td width="10" align="center">
							<input type="checkbox" name="set" onclick="setCheckedNew(this,'id_good_row')" />
						</td>
						<?
					}
					?>
					<td>ID</td>
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
				$counts = $weighs = $costs = 0;
				foreach ($arResult['PACK']['GOOS'] as $k=> $v)
				{
					?>
					<tr id="row_<?=$k;?>" class="CheckedRows">
						<?
						if ($arResult['EDIT'])
						{
							?>
							<td width="10" align="center">
                            	<input type="checkbox" name="id_good_row[]" value="<?=$k;?>" onChange="SelectRow('check_<?=$k;?>','row_<?=$k;?>');" id="check_<?=$k;?>"/>
							</td>
							<?
						}
						?>
						<td><?=$v['GOOD_ID'];?></td>
                        <td><?=$v['NAME'];?></td>
                        <td align="center" width="80">
							<?
							if ($arResult['EDIT'])
							{
								?>
								<input type="text" name="count[<?=$k;?>]" value="<?=$v['COUNT'];?>" class="short">
								<?
							}
							else
							{ 
								echo $v['COUNT'];
							}
							?>
							<input type="hidden" name="weigh[<?=$k;?>]" value="<?=$v['WEIGHT'];?>">
							<input type="hidden" name="cost[<?=$k;?>]" value="<?=$v['COST'];?>">
						</td>
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
					<?
					if ($arResult['EDIT'])
					{
						?>
						<td></td>
						<?
					}
					?>
				</tr>
			</tbody>
		</table>
		<?	
	}
	if ($arResult['EDIT'])
	{
		?>
		<p>
			<br>
			<input type="checkbox" name="pack_finish" value="1" id="pack_finish" <? echo ($_POST['pack_finish'] == 1)? 'checked' : ''; ?>> 
			<label for="pack_finish">�������� ����� �� ��������</label>
		</p>
		<input type="submit" value="��������� �����" name="save_package_shop">
		<?
		if (count($arResult['PACK']['GOOS']) > 0)
		{
			?>
			<input name="delete_goods" value="������� ������" type="submit">
			<?
		}
		?>
		</form>
		<?
	}
	if (count($arResult['PACK']['HISTORY']) > 0)
	{
		?>
		<h4>������� �� ������</h4>
		<table cellpadding="3" cellspacing="0" border="1" bordercolor="#ccc" class="rows">
			<thead>
				<tr>
					<td>����</td>
					<td>������</td>
					<td>��� �������</td>
					<td>�����������</td>
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
else
{
	?>
	<p>����� �� ������</p>
	<?
}
?>