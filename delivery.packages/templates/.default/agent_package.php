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
		<li class="nobg">
			<a href="/warehouse/index.php?mode=package_print&id=<?=$_GET["id"];?>&pdf=Y" title="Распечатать квитанцию" target="_blank">
            	<img src="/bitrix/components/black_mist/delivery.management/templates/.default/images/PDF-icon-20.png" width="20" height="20">
			</a>
		</li>
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
	<div class="table_group">
		<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="rows">
        	<tbody>
                <tr>
                    <td width="350"><strong>Отправитель</strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_CREATOR_NAME'];?></td>
                </tr>
                <tr>
                    <td><strong>Номер заказа DMS</strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_N_ZAKAZ_IN_VALUE'];?></td>
                </tr>
                <tr>
                    <td><strong>Номер заказа ИМ</strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_N_ZAKAZ_VALUE'];?></td>
                </tr>
                <tr>
                    <td><strong>Дата передачи на доставку</strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_DATE_TO_DELIVERY_VALUE'];?></td>
                </tr>
			</tbody>
		</table>
	</div>
    <div class="table_group">
		<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="rows">
        	<tbody>
				<tr>
                    <td width="350"><strong>Получатель</strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_RECIPIENT_VALUE'];?></td>
                </tr>
				<tr>
                    <td><strong>Номер телефона</strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_PHONE_VALUE'];?></td>
                </tr>
				<tr>
                    <td><strong>Город назначения</strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_CITY'];?></td>
                </tr>
				<tr>
                	<td><strong>Адрес</strong></td>
                    <td>
						<?
						if ($arResult['PACK']['PROPERTY_CONDITIONS_ENUM_ID'] == 38)
						{
							if (intval($arResult['PACK']['PROPERTY_PVZ_VALUE']) > 0)
							{
								echo 'ПВЗ '.$arResult['PACK']['PVZ_NAME'];
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
                        <td><strong>Доставить</strong></td>
                        <td><?=$arResult['PACK']['PROPERTY_WHEN_TO_DELIVER_VALUE'];?></td>
                    </tr>
                	<?
				}
				if(strlen($arResult['PACK']['PROPERTY_PREFERRED_TIME_VALUE']["TEXT"]))
				{
					?>
					<tr>
                    	<td><strong>Комментарий к заказу</strong></td>
                        <td><?=$arResult['PACK']['PROPERTY_PREFERRED_TIME_VALUE']["TEXT"];?></td>
					</tr>
					<?
				}
				if ($arResult['PACK']['PROPERTY_URGENCY_ORDER_ENUM_ID'] == 172)
				{
					?>
					<tr>
                    	<td><strong>Срочность заказа</strong></td>
                        <td>да, двойной тариф</td>
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
                    <td width="350 "><strong>Вес</strong></td>
                    <td><?=WeightFormat($arResult['PACK']['PROPERTY_WEIGHT_VALUE']);?></td>
                </tr>
                <tr>
                    <td><strong>Габариты</strong></td>
                    <td>
                        <?=strlen($arResult['PACK']['PROPERTY_SIZE_1_VALUE']) ? $arResult['PACK']['PROPERTY_SIZE_1_VALUE'] : '0';?>*<? echo strlen($arResult['PACK']['PROPERTY_SIZE_2_VALUE']) ? $arResult['PACK']['PROPERTY_SIZE_2_VALUE'] : '0';?>*<? echo strlen($arResult['PACK']['PROPERTY_SIZE_3_VALUE']) ? $arResult['PACK']['PROPERTY_SIZE_3_VALUE'] : '0';?> см
                         (<?=WeightFormat(($arResult['PACK']['PROPERTY_SIZE_1_VALUE']*$arResult['PACK']['PROPERTY_SIZE_2_VALUE']*$arResult['PACK']['PROPERTY_SIZE_3_VALUE'])/6000);?>)
                    </td>
                </tr>
                <tr>
                    <td><strong>Количество мест</strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_PLACES_VALUE'];?></td>
                </tr>
            </tbody>
		</table>
	</div>
	<div class="table_group">
		<table width="100%" cellpadding="3" cellspacing="0" border="0" bordercolor="#ccc" class="rows">
            <tbody>
                <tr>
                    <td width="350"><strong>Стоимость заказа</strong></td>
                    <td><?=CurrencyFormat($arResult['PACK']['PROPERTY_COST_GOODS_VALUE'],"RUU");?></td>
                </tr>
                <tr>
                    <td><strong>За доставку</strong></td>
                    <td><?=CurrencyFormat(intval($arResult['PACK']['PROPERTY_COST_3_VALUE']),"RUU");?></td>
                </tr>
                <tr>
                    <td><strong>Сумма к оплате</strong></td>
                    <td><?=CurrencyFormat($arResult['PACK']['PROPERTY_COST_2_VALUE'],"RUU");?></td>
                </tr>
                <tr>
                    <td><strong>Кассовое обслуживание</strong></td>
                    <td><?=$arResult['PACK']['PROPERTY_CASH_VALUE'];?></td>
                </tr>
                <?
                if (strlen($arResult['PACK']['PROPERTY_TYPE_PAYMENT_VALUE']))
                {
                    ?>
                    <tr>
                    	<td><strong>Тип оплаты</strong></td>
                        <td><?=$arResult['PACK']['PROPERTY_TYPE_PAYMENT_VALUE'];?></td>
					</tr>
                    <?
                }
                ?>
            </tbody>
        </table>
    </div>
	
	<?
	if ( strlen($arResult['PACK']['PROPERTY_COURIER_NAME']) || strlen($arResult['PACK']['PROPERTY_COMMENTS_COURIER_VALUE']) || strlen($arResult['PACK']['PROPERTY_DATE_DELIVERY_VALUE']) ||
	($arResult['PACK']['PROPERTY_OBTAINED_VALUE']))
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
                        <td width="350"><strong>Курьер</strong></td>
                        <td><?=$arResult['PACK']['PROPERTY_COURIER_NAME'];?></td>
                    </tr>
                    <?
                } 
                if (strlen($arResult['PACK']['PROPERTY_COMMENTS_COURIER_VALUE']))
                {
                    ?>
                    <tr>
                        <td width="350"><strong>Комментарий курьеру</strong></td>
                        <td><?=$arResult['PACK']['PROPERTY_COMMENTS_COURIER_VALUE'];?></td>
                    </tr>
                    <?
                } 
                if (strlen($arResult['PACK']['PROPERTY_DATE_DELIVERY_VALUE']))
                {
                    ?>
                    <tr>
                        <td width="350"><strong>Дата и время доставки</strong></td>
                        <td><?=$arResult['PACK']['PROPERTY_DATE_DELIVERY_VALUE'];?></td>
                    </tr>
                    <?
                } 
                if ($arResult['PACK']['PROPERTY_OBTAINED_VALUE'])
                {
                    ?>
                    <tr>
                        <td width="350"><strong>Получено с клиента</strong></td>
                        <td><?=CurrencyFormat($arResult['PACK']['PROPERTY_OBTAINED_VALUE'], 'RUU');?></td>
                    </tr>
                    <?
                }
                ?>
            </tbody>
        </table>
		</div>
		<?
	}
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
                        <td width="350"><strong>Вознаграждение за кассовое обслуживание</strong></td>
                        <td><?=CurrencyFormat($arResult['PACK']['PROPERTY_RATE_AGENT_VALUE'],"RUU");?></td>
                    </tr>
                    <?
                } 
                if (floatval($arResult['PACK']['PROPERTY_SUMM_AGENT_VALUE']) > 0)
                {
                    ?>
                    <tr>
                        <td width="350"><strong>Вознаграждение за доставку</strong></td>
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
	?>
	<div class="status_block">
		<?=$arResult['PACK']['PROPERTY_STATE_SHORT_VALUE'];?>
	</div>
	<?
	if (count($arResult['PACK']['GOOS']) > 0)
	{
		?>
		<h4 style="position:relative; height:30px; line-height:30px;">
        	Товары заказа 
            <a href="xls.php?id=<?=$arResult['PACK']['ID'];?>" style=" display:block; width:30px; height:30px; position:absolute; top:0; right:0;">
            	<img src="/bitrix/templates/portal/images/excel_ico.jpg" width="30" height="30" alt="" />
			</a>
		</h4>
		<table cellpadding="3" cellspacing="0" border="1" bordercolor="#ccc" class="rows" width="100%">
			<thead>
                <tr>
                    <td>№</td>
                    <td>Наименование</td>
                    <td>Артикул</td>
                    <td>Количество</td>
                    <td>Вес</td>
                    <td>Суммарный вес</td>
                    <td>Цена</td>
                    <td>Стоимость</td>
                </tr>
            </thead>
            <tbody>
                <?
                $counts = $weighs = $costs = $i = 0;
                foreach ($arResult['PACK']['GOOS'] as $k=> $v)
                {
                    $i++;
                    ?>
                    <tr>
                        <td width="30"><?=$i;?>.</td>
                        <td><?=$v['NAME'];?></td>
                        <td><?=$v['ARTICLE'];?></td>
                        <td><?=$v['COUNT'];?></td>
                        <td><?=$v['WEIGHT'];?> кг</td>
                        <td><?=($v['WEIGHT']*$v['COUNT']);?> кг</td>
                        <td><?=$v['COST'];?> руб.</td>
                        <td><?=($v['COST']*$v['COUNT']);?> руб.</td>
                    </tr>
                    <?
                    $counts = $counts + $v['COUNT'];
                    $weighs = $weighs + $v['WEIGHT']*$v['COUNT'];
                    $costs = $costs + $v['COST']*$v['COUNT'];
                }
                ?>
                <tr>
                    <td colspan="3" align="right"><strong>Итого:</strong></td>
                    <td align="right"><strong><?=$counts;?></strong></td>
                    <td>&nbsp;</td>
                    <td align="right"><strong><?=$weighs;?> кг</strong></td>
                    <td>&nbsp;</td>
                    <td align="right"><strong><?=$costs;?> руб.</strong></td>
                </tr>
            </tbody>
        </table>
        <?	
    }
	if (count($arResult['PACK']['SHORT_HISTORY']) > 0)
	{
		?>
		<h4>История по заказу</h4>
		<table cellpadding="3" cellspacing="0" border="1" bordercolor="#ccc" class="rows">
            <thead>
                <tr>
                    <td>Дата</td>
                    <td>Статус</td>
                    <td>Комментарий</td>
                    <td>Кем изменен</td>
                </tr>
            </thead>
            <tbody>
				<?
                $i = 0;
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