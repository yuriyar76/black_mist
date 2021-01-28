<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
?>

<script type="text/javascript">
	$(function () {
		$('[data-toggle="tooltip"]').tooltip()
	})
</script>

<div class="row">
	<div class="col-md-10">
		<h3><?=$arResult['TITLE'];?></h3>
	</div>
    <div class="col-md-2 text-right">
    	<h3>
            <span class="label label-warning" style="display:inline-block;">
			<?
			if (strlen($arResult['REQUEST']['PROPERTY_COMMENT_VALUE']))
			{
				?>
                <span class="glyphicon glyphicon-info-sign tooltip-text" aria-hidden="true" data-toggle="tooltip" data-placement="left" title="<?=$arResult['REQUEST']['PROPERTY_COMMENT_VALUE'];?>"></span>
				<?
			}
			?>
				<?=$arResult['REQUEST']['state_icon'].$arResult['REQUEST']['PROPERTY_STATE_VALUE'];?>

			</span>
        </h3>
    </div>
</div>
<?
if (($arResult['OPEN']) && ($arResult['REQUEST']))
{
	?>
    <div class="row">
    	<div class="col-md-4 small">
        	Дата создания: <strong><?=substr($arResult['REQUEST']['DATE_CREATE'],0,16);?></strong>
        </div>
		<div class="col-md-4 text-center small">
        	<? if (strlen($arResult['REQUEST']['PROPERTY_DATE_ADOPTION_VALUE'])) : ?>
        	Дата принятия: <strong><?=substr($arResult['REQUEST']['PROPERTY_DATE_ADOPTION_VALUE'],0,16);?></strong>
            <? endif; ?>
        </div>
        <div class="col-md-4 text-right small">
        	Дата передачи в обработку: <strong><?=substr($arResult['REQUEST']['PROPERTY_DATE_VALUE'],0,16);?></strong>
        </div>
    </div>
    <div class="row">
    	<div class="col-md-4 small">
        	Ответственный: <strong><?=$arResult['REQUEST']['CREATED_BY_NAME'];?></strong>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <h4>Отправитель</h4>
            <div class="panel panel-default-1">
				<div class="panel-heading">Компания</div>
				<div class="panel-body"><?=$arResult['REQUEST']['PROPERTY_COMPANY_SENDER_VALUE'];?></div>
			</div>
            <div class="panel panel-default-1">
                <div class="panel-heading">Фамилия</div>
                <div class="panel-body"><?=strlen($arResult['REQUEST']['PROPERTY_NAME_SENDER_VALUE']) ? $arResult['REQUEST']['PROPERTY_NAME_SENDER_VALUE'] : '&nbsp;';?></div>
            </div>
            <div class="panel panel-default-1">
                <div class="panel-heading">Телефон</div>
                <div class="panel-body"><?=$arResult['REQUEST']['PROPERTY_PHONE_SENDER_VALUE'];?></div>
            </div>
            <div class="panel panel-default-1">
                <div class="panel-heading">Город</div>
                <div class="panel-body"><?=$arResult['REQUEST']['PROPERTY_CITY_SENDER'];?></div>
            </div>
            <div class="panel panel-default-1">
                <div class="panel-heading">Индекс</div>
                <div class="panel-body"><?=strlen($arResult['REQUEST']['PROPERTY_INDEX_SENDER_VALUE']) ? $arResult['REQUEST']['PROPERTY_INDEX_SENDER_VALUE'] : '&nbsp;';?></div>
            </div>
            <div class="panel panel-default-1">
                <div class="panel-heading">Адрес</div>
                <div class="panel-body"><?=$arResult['REQUEST']['PROPERTY_ADRESS_SENDER_VALUE'];?></div>
            </div>
        </div>
        <div class="col-md-3">
            <h4>Получатель</h4>
            <div class="panel panel-default">
                <div class="panel-heading">Компания</div>
                <div class="panel-body"><?=$arResult['REQUEST']['PROPERTY_COMPANY_RECIPIENT_VALUE'];?></div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">Фамилия</div>
                <div class="panel-body"><?=strlen($arResult['REQUEST']['PROPERTY_NAME_RECIPIENT_VALUE']) ? $arResult['REQUEST']['PROPERTY_NAME_RECIPIENT_VALUE'] : '&nbsp;';?></div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">Телефон</div>
                <div class="panel-body"><?=$arResult['REQUEST']['PROPERTY_PHONE_RECIPIENT_VALUE'];?></div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">Город</div>
                <div class="panel-body"><?=$arResult['REQUEST']['PROPERTY_CITY_RECIPIENT'];?></div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">Индекс</div>
                <div class="panel-body"><?=strlen($arResult['REQUEST']['PROPERTY_INDEX_RECIPIENT_VALUE']) ? $arResult['REQUEST']['PROPERTY_INDEX_RECIPIENT_VALUE'] : '&nbsp;';?></div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">Адрес</div>
                <div class="panel-body"><?=$arResult['REQUEST']['PROPERTY_ADRESS_RECIPIENT_VALUE'];?></div>
            </div>
        </div>
        <div class="col-md-6">
        	<h4>Описание отправления</h4>
            <div class="row">
            	<div class="col-md-6">
					<div class="panel panel-default-3">
                        <div class="panel-heading">Дата и временной интервал забора</div>
                        <div class="panel-body">
                            <?=$arResult['REQUEST']['PROPERTY_DATE_TAKE_VALUE'];?>
                            <?=strlen($arResult['REQUEST']['PROPERTY_TIME_TAKE_FROM_VALUE']) ? ' c '.$arResult['REQUEST']['PROPERTY_TIME_TAKE_FROM_VALUE'] : '';?>
                            <?=strlen($arResult['REQUEST']['PROPERTY_TIME_TAKE_TO_VALUE']) ? ' до '.$arResult['REQUEST']['PROPERTY_TIME_TAKE_TO_VALUE'] : '';?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="panel panel-default-3">
                        <div class="panel-heading">Внутренний номер заявки</div>
                        <div class="panel-body"><?=strlen($arResult['REQUEST']['PROPERTY_NUMBER_IN_VALUE']) ? $arResult['REQUEST']['PROPERTY_NUMBER_IN_VALUE'] : '&nbsp;';?></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
					<div class="panel panel-default-3">
                        <div class="panel-heading">Тип отправления</div>
                        <div class="panel-body"><?=strlen($arResult['REQUEST']['PROPERTY_TYPE_VALUE']) ? $arResult['REQUEST']['PROPERTY_TYPE_VALUE'] : '&nbsp;';?></div>
                    </div>
                </div>
            	<div class="col-md-4">
					<div class="panel panel-default-3">
                        <div class="panel-heading">Тип доставки</div>
                        <div class="panel-body"><?=strlen($arResult['REQUEST']['PROPERTY_TYPE_DELIVERY_VALUE']) ? $arResult['REQUEST']['PROPERTY_TYPE_DELIVERY_VALUE'] : '&nbsp;';?></div>
                    </div>
                </div>
                <div class="col-md-4">
					<div class="panel panel-default-3">
                        <div class="panel-heading">Условия доставки</div>
                        <div class="panel-body"><?=strlen($arResult['REQUEST']['PROPERTY_DELIVERY_CONDITION_VALUE']) ? $arResult['REQUEST']['PROPERTY_DELIVERY_CONDITION_VALUE'] : '&nbsp;';?></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
					<div class="panel panel-default-3">
                        <div class="panel-heading">Плательщик</div>
                        <div class="panel-body"><?=strlen($arResult['REQUEST']['PROPERTY_DELIVERY_PAYER_VALUE']) ? $arResult['REQUEST']['PROPERTY_DELIVERY_PAYER_VALUE'] : '&nbsp;';?></div>
                    </div>
                </div>
                <div class="col-md-4">
					<div class="panel panel-default-3">
                        <div class="panel-heading">Тип оплаты</div>
                        <div class="panel-body"><?=strlen($arResult['REQUEST']['PROPERTY_TYPE_CASH_VALUE']) ? $arResult['REQUEST']['PROPERTY_TYPE_CASH_VALUE'] : '&nbsp;';?></div>
                    </div>
                </div>
				<div class="col-md-4">
					<div class="panel panel-default-3">
                        <div class="panel-heading">Сумма к оплате</div>
                        <div class="panel-body"><?=$arResult['REQUEST']['PROPERTY_PAYMENT_AMOUNT_VALUE'];?></div>
                    </div>
                </div>
            </div>
            <div class="row">
            	<div class="col-md-4">
                    <div class="panel panel-default-3">
                        <div class="panel-heading">Количество мест</div>
                        <div class="panel-body"><?=$arResult['REQUEST']['PROPERTY_PLACES_VALUE'];?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="panel panel-default-3">
                        <div class="panel-heading">Вес</div>
                        <div class="panel-body"><?=WeightFormat($arResult['REQUEST']['PROPERTY_WEIGHT_VALUE']);?></div>
                    </div>
                </div>
            	<div class="col-md-4">
                    <div class="panel panel-default-3">
                        <div class="panel-heading">Габариты, см</div>
                        <div class="panel-body"><?=WeightFormat($arResult['REQUEST']['PROPERTY_SIZE_1_VALUE'], false);?> x <?=WeightFormat($arResult['REQUEST']['PROPERTY_SIZE_2_VALUE'], false);?> x <?=WeightFormat($arResult['REQUEST']['PROPERTY_SIZE_3_VALUE'], false);?> см</div>
                        
                    </div>
                </div>
			</div>
			<div class="row">
            	<div class="col-md-12">
                    <div class="panel panel-default-3">
                        <div class="panel-heading">Спец. инструкции</div>
                        <div class="panel-body"><?=strlen($arResult['REQUEST']['PROPERTY_INSTRUCTIONS_VALUE']) ? $arResult['REQUEST']['PROPERTY_INSTRUCTIONS_VALUE'] : '&nbsp;';?></div>
                    </div>
				</div>
			</div>
        </div>
    </div>
    <?
	if (count($arResult['REQUEST']['FILES']) > 0)
	{
		?>
		<div class="row">
            <div class="col-md-12">
                <h4>Дополнительные файлы</h4>
            </div>
		</div>
        <div class="row">
        <?
		foreach ($arResult['REQUEST']['FILES'] as $ff)
		{
			?>
			<div class="col-md-3">
				<div class="form-group">
					<div class="panel panel-default">
						<div class="panel-body">
							<a href="<?=$ff['SRC'];?>" target="_blank"><?=$ff['ORIGINAL_NAME'];?></a> 
						</div>
					</div>
				</div>
			</div>
			<?
		}
		?>
        </div>
        <?
	}
}
?>