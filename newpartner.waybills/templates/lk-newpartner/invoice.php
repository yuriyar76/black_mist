<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
if ($arResult['OPEN'] && $arResult['INVOICE'])
{
	?>
	<div class="row">
        <div class="col-md-6">
            <h2><?=$arResult['TITLE'];?></h2>
        </div>
        <div class="col-md-6 text-right">
            <div class="btn-group" role="group" aria-label="...">
                <a href="<?=$arParams['LINK'];?>index.php?mode=print&id=<?=$arResult['INVOICE']['ID'];?>&print=Y" target="_blank" class="btn btn-default">
                    <span class="glyphicon glyphicon-print" aria-hidden="true"></span>
                </a>
                <?
				if ($arResult['EDIT'])
				{
					?>
                    <a href="<?=$arParams['LINK'];?>index.php?mode=edit&id=<?=$arResult['INVOICE']['ID'];?>" class="btn btn-default">
                        <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
                    </a>
                    <?
				}
				?>
            </div>
        </div>
    </div>
    <div class="row">
    	<div class="col-md-12">
        	<div class="table-responsive">
            	<table class="table table-bordered inv-table">
                	<tdead>
						<tr class="inv-bg-success-1">
                        	<th width="10%">Дата</th>
                            <td width="15%"><strong><?=substr($arResult['INVOICE']['DATE_CREATE'],0,10);?></strong></td>
                            <th width="10%">Клиент</th>
                            <td width="15%"><strong><?=$arResult['INVOICE']['PROPERTY_CREATOR_NAME'];?></strong></td>
                            <th width="10%">Филиал</th>
                            <td width="15%"><strong><?=$arResult['INVOICE']['PROPERTY_BRANCH_NAME'];?></strong></td>
                            <th width="10%">Договор</th>
                            <td><strong><?=$arResult['INVOICE']['PROPERTY_CONTRACT_NAME'];?></strong></td>
                        </tr>
                    	<tr>
                        	<th colspan="2" class="inv-bg-success">Отправитель</th>
                            <th colspan="2" class="inv-bg-info">Получатель</th>
                            <th colspan="2" class="inv-bg-success">Условия доставки</th>
                            <th colspan="2" class="inv-bg-info">Условия оплаты</th>
                        </tr>
                        <tr>
                        	<td class="inv-bg-success">Компания</td>
                            <td class="inv-bg-success"><strong><?=$arResult['INVOICE']['PROPERTY_COMPANY_SENDER_VALUE'];?></strong></td>
							<td class="inv-bg-info">Компания</td>
                            <td class="inv-bg-info"><strong><?=$arResult['INVOICE']['PROPERTY_COMPANY_RECIPIENT_VALUE'];?></strong></td>
							<td class="inv-bg-success">Условия доставки</td>
                            <td class="inv-bg-success"><strong><?=$arResult['INVOICE']['PROPERTY_TYPE_DELIVERY_VALUE'];?></strong></td>
							<td class="inv-bg-info">Оплата</td>
                            <td class="inv-bg-info"><strong><?=$arResult['INVOICE']['PROPERTY_PAYMENT_VALUE'];?></strong></td>
                        </tr>
                        <tr>
                        	<td class="inv-bg-success">Фамилия</td>
                            <td class="inv-bg-success"><strong><?=$arResult['INVOICE']['PROPERTY_NAME_SENDER_VALUE'];?></strong></td>
                            <td class="inv-bg-info">Фамилия</td>
                            <td class="inv-bg-info"><strong><?=$arResult['INVOICE']['PROPERTY_NAME_RECIPIENT_VALUE'];?></strong></td>
							<td class="inv-bg-success">Тип отправления</td>
                            <td class="inv-bg-success"><strong><?=$arResult['INVOICE']['PROPERTY_TYPE_PACK_VALUE'];?></strong></td>
							<td class="inv-bg-info">Оплачивает</td>
                            <td class="inv-bg-info">
                                <strong>
                                    <?
                                    if ($arResult['INVOICE']['PROPERTY_TYPE_PAYS_ENUM_ID'] == 253)
                                    {
                                        echo (strlen($arResult['INVOICE']['PROPERTY_PAYS_VALUE'])) ? $arResult['INVOICE']['PROPERTY_PAYS_VALUE'] : $arResult['INVOICE']['PROPERTY_TYPE_PAYS_VALUE'];
                                    }
                                    else
                                    {
                                        echo $arResult['INVOICE']['PROPERTY_TYPE_PAYS_VALUE'];
                                    }
                                    ?>
                                </strong>
                            </td>
                        </tr>
						<tr>
                        	<td class="inv-bg-success">Телефон</td>
                            <td class="inv-bg-success"><strong><?=$arResult['INVOICE']['PROPERTY_PHONE_SENDER_VALUE'];?></strong></td>
                            <td class="inv-bg-info">Телефон</td>
                            <td class="inv-bg-info"><strong><?=$arResult['INVOICE']['PROPERTY_PHONE_RECIPIENT_VALUE'];?></strong></td>
							<td rowspan="2" class="inv-bg-success">Доставить</td>
                            <td class="inv-bg-success"><strong><?=$arResult['INVOICE']['PROPERTY_WHO_DELIVERY_VALUE'];?></strong></td>
							<td class="inv-bg-info">К оплате</td>
                            <td class="inv-bg-info"><strong><?=CurrencyFormat($arResult['INVOICE']['PROPERTY_FOR_PAYMENT_VALUE'],"RUU");?></strong></td>
                        </tr>
						<tr>
                        	<td class="inv-bg-success">Город</td>
                            <td class="inv-bg-success"><strong><?=$arResult['INVOICE']['PROPERTY_CITY_SENDER'];?></strong></td>
                            <td class="inv-bg-info">Город</td>
                            <td class="inv-bg-info"><strong><?=$arResult['INVOICE']['PROPERTY_CITY_RECIPIENT'];?></strong></td>
                            <td class="inv-bg-success">
                            <strong>
								<?=substr($arResult['INVOICE']['PROPERTY_IN_DATE_DELIVERY_VALUE'],0,10);?> 
								<?=strlen($arResult['INVOICE']['PROPERTY_IN_TIME_DELIVERY_VALUE']) ? ' до '.$arResult['INVOICE']['PROPERTY_IN_TIME_DELIVERY_VALUE'] : '';?>
                            </strong>
                            </td>
							<td class="inv-bg-info">Объявленная стоимость</td>
                            <td class="inv-bg-info"><strong><?=CurrencyFormat($arResult['INVOICE']['PROPERTY_COST_VALUE'],"RUU");?></strong></td>
                        </tr>
						<tr>
                        	<td class="inv-bg-success">Индекс</td>
                            <td class="inv-bg-success"><strong><?=$arResult['INVOICE']['PROPERTY_INDEX_SENDER_VALUE'];?></strong></td>
                            <td class="inv-bg-info">Индекс</td>
                            <td class="inv-bg-info"><strong><?=$arResult['INVOICE']['PROPERTY_INDEX_RECIPIENT_VALUE'];?></td>
                            <td rowspan="2" class="inv-bg-danger">Спец. инструкции</td>
                            <td rowspan="2" colspan="3" class="inv-bg-danger"><strong><?=$arResult['INVOICE']['PROPERTY_INSTRUCTIONS_VALUE']['TEXT'];?></strong></td>
                        </tr>
						<tr>
                        	<td class="inv-bg-success">Адрес</td>
                            <td class="inv-bg-success"><strong><?=$arResult['INVOICE']['PROPERTY_ADRESS_SENDER_VALUE']['TEXT'];?></strong></td>
                            <td class="inv-bg-info">Адрес</td>
                            <td class="inv-bg-info"><strong><?=$arResult['INVOICE']['PROPERTY_ADRESS_RECIPIENT_VALUE']['TEXT'];?></strong></td>
                        </tr>
 						<tr class="inv-bg-warning">
                        	<th colspan="2">Характер отправления</th>
                        	<td>Вес</td>
                            <td><strong><?=WeightFormat($arResult['INVOICE']['PROPERTY_WEIGHT_VALUE']);?></strong></td>
                            <td>Габариты</td>
                            <td><strong><?=$arResult['INVOICE']['PROPERTY_DIMENSIONS_VALUE'][0];?>*<?=$arResult['INVOICE']['PROPERTY_DIMENSIONS_VALUE'][1];?>*<?=$arResult['INVOICE']['PROPERTY_DIMENSIONS_VALUE'][2];?> см</strong></td>
                            <td>Места</td>
                            <td><strong><?=intval($arResult['INVOICE']['PROPERTY_PLACES_VALUE']);?></strong></td>
                        </tr>
                    </tdead>
                </table>
			</div>
        </div>
    </div>
    <?
	if ($arResult['TRACKING'])
	{
		?>
        <div class="row">
            <div class="col-md-4">
				<table cellpadding="5" bordercolor="#ccc" border="1" width="600" style=" border-collapse: collapse;" class="show_tracks table table-striped table-hover">
                    <thead>
                        <tr>
                            <th colspan="3">Трек отправления <?=$arResult['INVOICE']['NAME'];?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?
                        
                        foreach ($arResult['TRACKING']['DATES_NAKLS_SORT'][$arResult['INVOICE']['NAME']] as $key => $date)
                        {
                            $event = $arResult['TRACKING']['ALL_EVENTS'][$arResult['INVOICE']['NAME']][$key];
                            ?>
                            <tr>
                                <td width="30%"><?=$event['DateEvent'].' '.$event['TimeEvent'];?></td>
                                <td width="35%"><?=$event['Event'];?></td>
                                <td width="35%"><?=$event['InfoEvent'];?></td>
                            </tr>
                            <?
                        }
                        
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?
	}
}
?>