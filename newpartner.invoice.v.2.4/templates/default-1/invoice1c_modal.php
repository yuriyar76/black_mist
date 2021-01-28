<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
if (($arResult['OPEN']) && ($arResult['REQUEST']))
{
	?>
    <div class="modal-body">
    	<div class="row">
        	<div class="col-md-12 text-right"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
        </div>
		<div class="row">
            <div class="col-md-12"><h3><?=$arResult['TITLE'];?></h3></div>
		</div>
        <div class="row">
			<div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-body">
                    	<div class="row"><div class="col-md-12"><h4>Отправитель</h4></div></div>
                        <div class="row">
                        	<div class="col-md-3">Компания</div>
                            <div class="col-md-9"><strong><?=(strlen($arResult['REQUEST']['КомпанияОтправителя'])) ? $arResult['REQUEST']['КомпанияОтправителя'] : $arResult['REQUEST']['ВыборОтправителя'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">Фамилия</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['ФамилияОтправителя'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">Телефон</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['ТелефонОтправителя'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">Город</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['ГородОтправителя'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">Индекс</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['ИндексОтправителя'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">Адрес</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['АдресОтправителя'];?></strong></div>
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
                            <div class="col-md-9"><strong><?=(strlen($arResult['REQUEST']['КомпанияПолучателя'])) ? $arResult['REQUEST']['КомпанияПолучателя'] : $arResult['REQUEST']['ВыборПолучателя'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">Фамилия</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['ФамилияПолучателя'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">Телефон</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['ТелефонПолучателя'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">Город</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['ГородПолучателя'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">Индекс</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['ИндексПолучателя'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">Адрес</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['АдресПолучателя'];?></strong></div>
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
						<table class="table table-bordered table-condensed">
							<thead>
								<tr>
									<th width="50%">Описание отправления</th>
									<th width="10%">Мест</th>
									<th width="10%">Вес</th>
									<th width="10%">Вес об.</th>
									<th colspan="3" width="30%">Габариты</th>
								</tr>
							</thead>
							<tbody>
								<?
								$wght = 0;
								$wght_ob = 0;
								$klvmest = 0;
								foreach ($arResult['REQUEST']['Габариты'] as $g):?>
								<?
								$wght = $wght + $g['ВесОтправления'];
								$wght_ob = $wght_ob + $g['ВесОтправленияОбъемный'];
								$klvmest = $klvmest + $g['КоличествоМест'];
								?>
								<tr>
									<td><?=$g['Габарит'];?></td>
									<td><?=$g['КоличествоМест'];?></td>
									<td><?=WeightFormat($g['ВесОтправления']);?></td>
									<td><?=WeightFormat($g['ВесОтправленияОбъемный']);?></td>
									<td>
									<?=$g['Длина'].'x'.$g['Ширина'].'x'.$g['Высота'].' см';
									?>
									</td>
								</tr>
								<?endforeach;?>
							</tbody>
							<tfoot>
								<tr>
									<th></th>
									<th><?=$klvmest;?></th>
									<th><?=WeightFormat($wght);?></th>
									<th><?=WeightFormat($wght_ob);?></th>
									<th></th>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
			</div>
			<div class="col-md-8">
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <h4>Условия доставки</h4>
                                <div class="row">
                                    <div class="col-md-4">Тип доставки</div>
                                    <div class="col-md-8"><strong><?=$arResult['REQUEST']['ПризнакТипДоставки'];?></strong></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">Тип отправления</div>
                                    <div class="col-md-8"><strong><?=(intval($arResult['REQUEST']['ПризнакДокументы']) == 1) ? 'Документы' : 'Не документы';?></strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-4">Доставить</div>
                                    <div class="col-md-8"><strong><?=$arResult['REQUEST']['СпециальныеУсловия'];?></strong></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">Дата выполнения</div>
                                    <div class="col-md-8"><strong>
                                    	<?
                                            if (strlen($arResult['REQUEST']['ДатаВыполненияЗаявки']))
                                            {
												echo substr($arResult['REQUEST']['ДатаВыполненияЗаявки'],8,2).'.'.substr($arResult['REQUEST']['ДатаВыполненияЗаявки'],5,2).'.'.substr($arResult['REQUEST']['ДатаВыполненияЗаявки'],0,4);
                                            }
                                        ?>
                                    </strong></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                 <h4>Условия оплаты</h4>
                            	<div class="row">
                                	<div class="col-md-4">Оплата</div>
                                    <div class="col-md-8"><strong><?=$arResult['REQUEST']['ПризнакТипОплаты'];?></strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-4">Оплачивает</div>
                                    <div class="col-md-8"><strong><?=$arResult['REQUEST']['ПризнакПлательщик'];?></strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-4">Сумма к оплате</div>
                                    <div class="col-md-8"><strong><?=$arResult['REQUEST']['СуммаКОплате'];?></strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-4">Тариф за услугу</div>
                                    <div class="col-md-8"><strong><?=$arResult['REQUEST']['СтоимостьУслуги'];?></strong></div>
                                </div>                  
                            </div>
                        </div>
					</div>
				</div>
                <div class="row">
                	<div class="col-md-6">
						<div class="panel panel-default">
							<div class="panel-body">
								<h4>Спец. инструкции</h4>
								<p><?=$arResult['REQUEST']['СпециальныеИнструкции'];?></p>
							</div>
						</div>
					</div>
                	<div class="col-md-6">
                    	<div class="panel panel-default">
                        	<div class="panel-body">
                            	<h4>Ответственный менеджер</h4>
                                <p><strong><?=$arResult['REQUEST']['Ответственный'];?></strong></p>
                            </div>
                        </div>
                    </div>
                </div>
			</div>
        </div>
        <div class="row">
        	<?if ((is_array($arResult['REQUEST']['Goods'])) && (count($arResult['REQUEST']['Goods']) > 0)):?>
        	<div class="col-md-6">
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
        						<? foreach ($arResult['REQUEST']['Goods'] as $g):?>
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
			</div>
        	<?endif;?>
        	<div class="col-md-6">
        	    <?
                if (count($arResult['REQUEST']['События']) > 0):
                ?>
                <table cellpadding="5" bordercolor="#ccc" border="1" width="600" style=" border-collapse: collapse;" class="show_tracks table table-striped table-hover">
                    <thead>
                        <tr>
                            <th colspan="3" class="text-center">Трек отправления <?=$arResult['REQUEST']['НомерНакладной'];?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?
                        foreach ($arResult['REQUEST']['События'] as $s):
							if (in_array($s['InfoEvent'], $arResult['HIDE_EVENTS']) && ($s['Event'] == 'Исключительная ситуация!'))
							{}
							else
							{
							?>
							<tr>
								<td width="30%"><?=$s['DateEvent'];?>&nbsp;<?=$s['TimeEvent'];?></td>
								<td width="35%"><?=$s['Event'];?></td>
								<td width="35%"><?=$s['InfoEvent'];?></td>
							</tr>
							<?
							}
                        endforeach;
                        ?>
                    </tbody>
                </table>
                <?
                endif;
                ?>
            </div>
        </div>
    </div>
    <?
}