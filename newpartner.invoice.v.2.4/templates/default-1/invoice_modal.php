<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
if ($arResult['OPEN'])
{
	?>
    <div class="modal-body">
    	<div class="row">
        	<div class="col-md-12 text-right"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
        </div>
        <div class="row">
            <div class="col-md-4"><h3><?=$arResult['TITLE'];?></h3></div>
            <? if (strlen($arResult['INVOICE']['PROPERTY_BRANCH_NAME'])) : ?><div class="col-md-4 text-center"><br>Филиал: <strong><?=$arResult['INVOICE']['PROPERTY_BRANCH_NAME'];?></strong></div> <? endif;?>
            <? if (strlen($arResult['INVOICE']['PROPERTY_CONTRACT_NAME'])) : ?><div class="col-md-4 text-right"><br>Договор: <strong><?=$arResult['INVOICE']['PROPERTY_CONTRACT_NAME'];?></strong></div> <? endif; ?>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-body">
                    	<div class="row"><div class="col-md-12"><h4>Отправитель</h4></div></div>
                        <div class="row">
                        	<div class="col-md-3">Компания</div>
                            <div class="col-md-9"><strong><?=$arResult['INVOICE']['PROPERTY_COMPANY_SENDER_VALUE'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">Фамилия</div>
                            <div class="col-md-9"><strong><?=$arResult['INVOICE']['PROPERTY_NAME_SENDER_VALUE'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">Телефон</div>
                            <div class="col-md-9"><strong><?=$arResult['INVOICE']['PROPERTY_PHONE_SENDER_VALUE'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">Город</div>
                            <div class="col-md-9"><strong><?=$arResult['INVOICE']['PROPERTY_CITY_SENDER'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">Индекс</div>
                            <div class="col-md-9"><strong><?=$arResult['INVOICE']['PROPERTY_INDEX_SENDER_VALUE'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">Адрес</div>
                            <div class="col-md-9"><strong><?=$arResult['INVOICE']['PROPERTY_ADRESS_SENDER_VALUE']['TEXT'];?></strong></div>
						</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-body">
                    	<div class="row"><div class="col-md-12"><h4>Получатель</h4></div></div>
                        <div class="row">
                        	<div class="col-md-3">Компания</div>
                            <div class="col-md-9"><strong><?=$arResult['INVOICE']['PROPERTY_COMPANY_RECIPIENT_VALUE'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">Фамилия</div>
                            <div class="col-md-9"><strong><?=$arResult['INVOICE']['PROPERTY_NAME_RECIPIENT_VALUE'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">Телефон</div>
                            <div class="col-md-9"><strong><?=$arResult['INVOICE']['PROPERTY_PHONE_RECIPIENT_VALUE'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">Город</div>
                            <div class="col-md-9"><strong><?=$arResult['INVOICE']['PROPERTY_CITY_RECIPIENT'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">Индекс</div>
                            <div class="col-md-9"><strong><?=$arResult['INVOICE']['PROPERTY_INDEX_RECIPIENT_VALUE'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">Адрес</div>
                            <div class="col-md-9"><strong><?=$arResult['INVOICE']['PROPERTY_ADRESS_RECIPIENT_VALUE']['TEXT'];?></strong></div>
						</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
        	<div class="col-md-4">
            	<div class="panel panel-default">
                    <div class="panel-body">
                    	<h4>Характер отправления</h4>
               <table class="table table-bordered table-condensed" id="calculated_values">
                    <thead>
                        <tr>
                            <th width="50%">Описание отправления</th>
                            <th width="10%">Мест</th>
                            <th width="10%">Вес</th>
                            <th colspan="3" width="30%">Габариты</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?=$arResult['INVOICE']['PACK_DESCR'][0]['name'];?></td>
                            <td><?=$arResult['INVOICE']['PACK_DESCR'][0]['place'];?></td>
                            <td><?=WeightFormat($arResult['INVOICE']['PACK_DESCR'][0]['weight']);?></td>
                            <td>
                            <?
                            if ($arResult['INVOICE']['PACK_DESCR'][0]['gabweight'] > 0)
							{
							 echo $arResult['INVOICE']['PACK_DESCR'][0]['size'][0].'x'.$arResult['INVOICE']['PACK_DESCR'][0]['size'][1].'x'.$arResult['INVOICE']['PACK_DESCR'][0]['size'][2].' см';
							}
							?>
                            </td>
                        </tr>
                        <tr>
                            <td><?=$arResult['INVOICE']['PACK_DESCR'][1]['name'];?></td>
                            <td><?=$arResult['INVOICE']['PACK_DESCR'][1]['place'];?></td>
                            <td><?=WeightFormat($arResult['INVOICE']['PACK_DESCR'][1]['weight']);?></td>
                            <td>
                            <?
                            if ($arResult['INVOICE']['PACK_DESCR'][1]['gabweight'] > 0)
							{
							 echo $arResult['INVOICE']['PACK_DESCR'][1]['size'][0].'x'.$arResult['INVOICE']['PACK_DESCR'][1]['size'][1].'x'.$arResult['INVOICE']['PACK_DESCR'][1]['size'][2].' см';
							}
							?>
                            </td>
                        </tr>
                        <tr>
                            <td><?=$arResult['INVOICE']['PACK_DESCR'][2]['name'];?></td>
                            <td><?=$arResult['INVOICE']['PACK_DESCR'][2]['place'];?></td>
                            <td><?=WeightFormat($arResult['INVOICE']['PACK_DESCR'][2]['weight']);?></td>
                            <td>
                            <?
                            if ($arResult['INVOICE']['PACK_DESCR'][2]['gabweight'] > 0)
							{
							 echo $arResult['INVOICE']['PACK_DESCR'][2]['size'][0].'x'.$arResult['INVOICE']['PACK_DESCR'][2]['size'][1].'x'.$arResult['INVOICE']['PACK_DESCR'][2]['size'][2].' см';
							}
							?>
                            </td>
                        </tr>
                        <tr>
                            <td><?=$arResult['INVOICE']['PACK_DESCR'][3]['name'];?></td>
                            <td><?=$arResult['INVOICE']['PACK_DESCR'][3]['place'];?></td>
                            <td><?=WeightFormat($arResult['INVOICE']['PACK_DESCR'][3]['weight']);?></td>
                            <td>
							<?
                            if ($arResult['INVOICE']['PACK_DESCR'][3]['gabweight'] > 0)
							{
							 echo $arResult['INVOICE']['PACK_DESCR'][3]['size'][0].'x'.$arResult['INVOICE']['PACK_DESCR'][3]['size'][1].'x'.$arResult['INVOICE']['PACK_DESCR'][3]['size'][2].' см';
							}
							?>
                            </td>
                        </tr>
                        <tr>
                            <td><?=$arResult['INVOICE']['PACK_DESCR'][4]['name'];?></td>
                            <td><?=$arResult['INVOICE']['PACK_DESCR'][4]['place'];?></td>
                            <td><?=WeightFormat($arResult['INVOICE']['PACK_DESCR'][4]['weight']);?></td>
                            <td>
                            <?
                            if ($arResult['INVOICE']['PACK_DESCR'][4]['gabweight'] > 0)
							{
							 echo $arResult['INVOICE']['PACK_DESCR'][4]['size'][0].'x'.$arResult['INVOICE']['PACK_DESCR'][4]['size'][1].'x'.$arResult['INVOICE']['PACK_DESCR'][4]['size'][2].' см';
							}
							?>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th></th>
                            <th><?=intval($arResult['INVOICE']['PROPERTY_PLACES_VALUE']);?></th>
                            <th><?=WeightFormat($arResult['INVOICE']['PROPERTY_WEIGHT_VALUE']);?></th>
                            <th><?=WeightFormat($arResult['INVOICE']["PROPERTY_OB_WEIGHT"], true, true);?></th>
                        </tr>
                    </tfoot>
                </table>
                    </div>
                </div>
				<?if ((is_array($arResult['INVOICE']['PACK_GOODS'])) && (count($arResult['INVOICE']['PACK_GOODS']) > 0)):?>
				<div class="panel panel-default">
					<div class="panel-body">
						<h4>Товары</h4>
						<table class="table table-bordered table-condensed">
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
								<? foreach ($arResult['INVOICE']['PACK_GOODS'] as $g):?>
								<tr>
									<td><?=$g['GoodsName'];?></td>
									<td><?=$g['Amount'];?></td>
									<td><?=$g['Price'];?></td>
									<td><?=$g['Sum'];?></td>
									<td><?=$g['SumNDS'];?></td>
									<td><?=$g['PersentNDS'];?>%</td>
								</tr>
								<?endforeach;?>
							</tbody>
						</table>
					</div>
				</div>
				<?endif;?>
            </div>
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <h4>Условия доставки</h4>
                                <div class="row">
                                    <div class="col-md-4">Тип доставки</div>
                                    <div class="col-md-8"><strong><?=$arResult['INVOICE']['PROPERTY_TYPE_DELIVERY_VALUE'];?></strong></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">Тип отправления</div>
                                    <div class="col-md-8"><strong><?=$arResult['INVOICE']['PROPERTY_TYPE_PACK_VALUE'];?></strong></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">Доставить</div>
                                    <div class="col-md-8"><strong>
                                    <?=$arResult['INVOICE']['PROPERTY_WHO_DELIVERY_VALUE'];?> <?=$arResult['INVOICE']['PROPERTY_IN_DATE_DELIVERY_VALUE'];?><?=strlen($arResult['INVOICE']['PROPERTY_IN_TIME_DELIVERY_VALUE']) ? ' до '.$arResult['INVOICE']['PROPERTY_IN_TIME_DELIVERY_VALUE'] : '';?>
                                    </strong></div>
                                </div>
                                <div class="row"><div class="col-md-12">&nbsp;</div></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                 <h4>Условия оплаты</h4>
                                <div class="row">
                                    <div class="col-md-4">Оплата</div>
                                    <div class="col-md-8"><strong><?=$arResult['INVOICE']['PROPERTY_PAYMENT_VALUE'];?></strong></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">Оплачивает</div>
                                    <div class="col-md-8"><strong><?=($arResult['INVOICE']['PROPERTY_TYPE_PAYS_ENUM_ID'] == 253) ? $arResult['INVOICE']['PROPERTY_PAYS_VALUE'] : $arResult['INVOICE']['PROPERTY_TYPE_PAYS_VALUE'];?></strong></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">Объявленная стоимость</div>
                                    <div class="col-md-8"><strong><?=CurrencyFormat($arResult['INVOICE']['PROPERTY_COST_VALUE'],"RUU");?></strong></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">Тариф за услугу</div>
                                    <div class="col-md-8"><strong><?=CurrencyFormat($arResult['INVOICE']['PROPERTY_RATE_VALUE'],"RUU");?></strong></div>
                                </div>
                            </div>
                        </div>
					</div>
				</div>
                <div class="row">
                	<div class="col-md-12">
                    	<div class="panel panel-default">
                        	<div class="panel-body">
                            	<h4>Специальные инструкции</h4>
                                <p><?=$arResult['INVOICE']['PROPERTY_INSTRUCTIONS_VALUE']['TEXT'];?></p>
                            </div>
                        </div>
                    </div>
                </div>
			</div> 
        </div>
    </div>
	<?
}
?>