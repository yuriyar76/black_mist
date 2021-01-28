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
	
	function sendcomment() {
		$('#comment_Comment').parent(".form-group").removeClass('has-error');
		$('#commentinfo').html('');
		var comment_l = $.trim($('#comment_Comment').val()).length;
		if (comment_l > 0)
		{
			var comment = $('#comment_Comment').val();
			var org = $('#comment_Org').val();
			var otv = $('#comment_Otv').val()
			$.post("/search_city.php?sendcomment=Y", {
					comment_NUMDOC: $('#comment_NUMDOC').val(), 
					comment_NUMREQUEST: $('#comment_NUMREQUEST').val(),
					comment_Otv: otv,
					comment_Org: org,
					comment_INN: $('#comment_INN').val(),
					comment_Comment: comment
				},
				function(data){
					if (data["result"] == 'Y')
					{
						$('#commentinfo').html('<div class="alert alert-dismissable alert-success fade in" role="alert"><button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">X</span><span class="sr-only">Закрыть</span></button>Сообщение успешно добавлено</div>');
						$('#bodycomment').append('<tr><td>'+data["date"]+'</td><td>'+comment+'</td><td>'+org+'</td><td>'+otv+'</td></tr>');
						$('#comment_Comment').val('');
					}
					else
					{
						$('#commentinfo').html('<div class="alert alert-dismissable alert-danger fade in" role="alert"><button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">X</span><span class="sr-only">Закрыть</span></button>Что-то пошло не так...</div>');
					}
				}
			, "json");
		}
		else
		{
			$('#comment_Comment').parent(".form-group").addClass('has-error');
		}
	}
</script>

<div class="modal-body">
    <div class="row">
        <div class="text-right col-md-12">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    </div>
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
                    <span class="glyphicon glyphicon glyphicon-info-sign tooltip-text" aria-hidden="true" data-toggle="tooltip" data-placement="left" title="<?=$arResult['REQUEST']['PROPERTY_COMMENT_VALUE'];?>"></span>
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
				Дата создания: <strong><?=substr($arResult['REQUEST']['DATE_CREATE'],0,16);?></strong><br>
                Дата передачи: <strong><?=substr($arResult['REQUEST']['PROPERTY_DATE_VALUE'],0,16);?></strong>
                <? if (strlen($arResult['REQUEST']['PROPERTY_DATE_ADOPTION_VALUE'])) : ?>
				<br>Дата принятия: <strong><?=substr($arResult['REQUEST']['PROPERTY_DATE_ADOPTION_VALUE'],0,16);?></strong>
				<? endif; ?>
                
			</div>
			<div class="col-md-4 text-center small">
				Ответственный: <strong><?=$arResult['REQUEST']['CREATED_BY_NAME'];?></strong>
			</div>
		</div>
        <div class="row"><div class="col-md-12 small">&nbsp;</div></div>
        <div class="row">
        	<div class="col-md-6">
				<div class="panel panel-default">
                    <div class="panel-body">
                    	<h4>Отправитель</h4>
						<div class="row">
                            <div class="col-md-3">Компания</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['PROPERTY_COMPANY_SENDER_VALUE'];?></strong></div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Фамилия</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['PROPERTY_NAME_SENDER_VALUE'];?></strong></div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Телефон</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['PROPERTY_PHONE_SENDER_VALUE'];?></strong></div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Город</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['PROPERTY_CITY_SENDER'];?></strong></div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Индекс</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['PROPERTY_INDEX_SENDER_VALUE'];?></strong></div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Адрес</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['PROPERTY_ADRESS_SENDER_VALUE'];?></strong></div>
                        </div>
                    </div>
				</div>
            </div>
            <div class="col-md-6">
				<div class="panel panel-default">
                    <div class="panel-body">
                    	<h4>Получатель</h4>
						<div class="row">
                            <div class="col-md-3">Компания</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['PROPERTY_COMPANY_RECIPIENT_VALUE'];?></strong></div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Фамилия</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['PROPERTY_NAME_RECIPIENT_VALUE'];?></strong></div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Телефон</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['PROPERTY_PHONE_RECIPIENT_VALUE'];?></strong></div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Город</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['PROPERTY_CITY_RECIPIENT'];?></strong></div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Индекс</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['PROPERTY_INDEX_RECIPIENT_VALUE'];?></strong></div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Адрес</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['PROPERTY_ADRESS_RECIPIENT_VALUE'];?></strong></div>
                        </div>
                    </div>
				</div>
            </div>
        </div>
		<div class="row">
			<div class="col-md-8">
            	<div class="panel panel-default">
                    <div class="panel-body">
                    	<h4>Характер отправления</h4>
                        <div class="row">
                        	<div class="col-md-4">
                            	<div class="row">
                                	<div class="col-md-5">Вес</div>
                                    <div class="col-md-7"><strong><?=WeightFormat($arResult['REQUEST']['PROPERTY_WEIGHT_VALUE']);?></strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-5">Габариты</div>
                                    <div class="col-md-7"><strong><?=WeightFormat($arResult['REQUEST']['PROPERTY_SIZE_1_VALUE'], false);?> x <?=WeightFormat($arResult['REQUEST']['PROPERTY_SIZE_2_VALUE'], false);?> x <?=WeightFormat($arResult['REQUEST']['PROPERTY_SIZE_3_VALUE'], false);?> см</strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-5">Количество мест</div>
                                    <div class="col-md-7"><strong><?=$arResult['REQUEST']['PROPERTY_PLACES_VALUE'];?></strong></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                            	<div class="row">
                                	<div class="col-md-5">Дата выполнения</div>
                                    <div class="col-md-7"><strong><?=$arResult['REQUEST']['PROPERTY_DATE_TAKE_VALUE'];?></strong></div>
                                </div>
								<div class="row">
                                	<div class="col-md-5">Интервал</div>
                                    <div class="col-md-7"><strong><?=$arResult['REQUEST']['PROPERTY_TIME_TAKE_FROM_VALUE'];?> - <?=$arResult['REQUEST']['PROPERTY_TIME_TAKE_TO_VALUE'];?></strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-5">Тип отправления</div>
                                    <div class="col-md-7"><strong><?=$arResult['REQUEST']['PROPERTY_TYPE_VALUE'];?></strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-5">Тип доставки</div>
                                    <div class="col-md-7"><strong><?=$arResult['REQUEST']['PROPERTY_TYPE_DELIVERY_VALUE'];?></strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-5">Условия доставки</div>
                                    <div class="col-md-7"><strong><?=$arResult['REQUEST']['PROPERTY_DELIVERY_CONDITION_VALUE'];?></strong></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                            	<div class="row">
                                	<div class="col-md-5">Плательщик</div>
                                    <div class="col-md-7"><strong><?=$arResult['REQUEST']['PROPERTY_DELIVERY_PAYER_VALUE'];?></strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-5">Тип оплаты</div>
                                    <div class="col-md-7"><strong><?=$arResult['REQUEST']['PROPERTY_TYPE_CASH_VALUE'];?></strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-5">Сумма к оплате</div>
                                    <div class="col-md-7"><strong><?=$arResult['REQUEST']['PROPERTY_PAYMENT_AMOUNT_VALUE'];?></strong></div>
                                </div>
                            </div>
                        </div>
                        

					</div>
				</div>
			</div>
            <div class="col-md-4">
				<div class="panel panel-default">
                    <div class="panel-body">
                    	<div class="row">
                        	<div class="col-md-5">Внутренний номер заявки</div>
                            <div class="col-md-7"><strong><?=$arResult['REQUEST']['PROPERTY_NUMBER_IN_VALUE'];?></strong></div>
                        </div>
                        <div class="row">
                        	<div class="col-md-5">Спец. инструкции</div>
                            <div class="col-md-7"><strong><?=$arResult['REQUEST']['PROPERTY_INSTRUCTIONS_VALUE'];?></strong></div>
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
		?>
        <div class="row">
			<div class="col-md-12">
				<h4>Комментарии</h4>
			</div>
        </div>
        <div class="row">
        	<div class="col-md-3">
                <div id="commentinfo"></div>
                <input type="hidden" id="comment_NUMDOC" value="<?=trim($_GET['NumDoc']);?>">
                <input type="hidden" id="comment_NUMREQUEST" value="<?=$arResult['REQUEST']['PROPERTY_NUMBER_VALUE'];?>">
                <input type="hidden" id="comment_Otv" value="<?=$arResult['USER_NAME'];?>">
                <input type="hidden" id="comment_Org" value="<?=$arResult['AGENT']['NAME'];?>">
                <input type="hidden" id="comment_INN" value="<?=$arResult['AGENT']['PROPERTY_INN_VALUE'];?>">
                <div class="form-group">
                    <textarea class="form-control" placeholder="Введите комментарий" id="comment_Comment"></textarea>
                </div>
                <br>
                <button class="btn btn-primary" id="comment_add" type="submit" onClick="sendcomment();">Добавить</button>
            </div>
            <div class="col-md-6">
                <table class="table table-striped table-bordered table-condensed">
                    <thead>
                        <tr>
                            <th>Дата</th>
                            <th>Комментарий</th>
                            <th>Компания</th>
                            <th>Автор</th>
                        </tr>
                    </thead>
                    <tbody id="bodycomment">
                    <?
                    foreach ($arResult['REQUEST']['Messages'] as $m)
                    {
                        ?>
                        <tr>
                            <td><?=substr($m['Date'],8,2).'.'.substr($m['Date'],5,2).'.'.substr($m['Date'],0,4).' '.substr($m['Date'],11,5);?></td>
                            <td><?=iconv('utf-8', 'windows-1251', $m['Comment']);?></td>
                            <td><?=iconv('utf-8', 'windows-1251', $m['Org']);?></td>
                            <td><?=iconv('utf-8', 'windows-1251', $m['Otv']);?></td>
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
	?>
</div>