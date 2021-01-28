<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
if (($arResult['OPEN']) && ($arResult['REQUEST']))
{
	?>
    <script type="text/javascript">
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
        	<div class="col-md-12 text-right"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
        </div>
		<div class="row">
            <div class="col-md-6"><h3><?=$arResult['TITLE'];?></h3></div>
            <div class="col-md-6 text-right"><h3><?=$arResult['TITLE_2'];?></h3></div>
		</div>
        <div class="row">
			<div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-body">
                    	<div class="row"><div class="col-md-12"><h4>Отправитель</h4></div></div>
                        <div class="row">
                        	<div class="col-md-3">Компания</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['КомпанияОтправителя'];?></strong></div>
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
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['КомпанияПолучателя'];?></strong></div>
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
			<div class="col-md-8">
            	<div class="panel panel-default">
                    <div class="panel-body">
                    	<h4>Характер отправления</h4>
                        <div class="row">
                        	<div class="col-md-4">
                            	<div class="row">
                                	<div class="col-md-5">Вес</div>
                                    <div class="col-md-7"><strong><?=WeightFormat($arResult['REQUEST']['ВесОтправления'], false);?></strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-5">Вес объемный</div>
                                    <div class="col-md-7"><strong><?=WeightFormat($arResult['REQUEST']['ВесОтправленияОбъемный'], false);?></strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-5">Количество мест</div>
                                    <div class="col-md-7"><strong><?=$arResult['REQUEST']['КоличествоМест'];?></strong></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                            	<div class="row">
                                	<div class="col-md-5">Дата выполнения</div>
                                    <div class="col-md-7">
                                    <strong>
										<?
                                            if (strlen($arResult['REQUEST']['ДатаВыполненияЗаявки']))
                                            {
												echo substr($arResult['REQUEST']['ДатаВыполненияЗаявки'],8,2).'.'.substr($arResult['REQUEST']['ДатаВыполненияЗаявки'],5,2).'.'.substr($arResult['REQUEST']['ДатаВыполненияЗаявки'],0,4);
                                            }
                                        ?>
                                    </strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-5">Тип отправления</div>
                                    <div class="col-md-7"><strong></strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-5">Тип доставки</div>
                                    <div class="col-md-7"><strong><?=$arResult['REQUEST']['ПризнакТипДоставки'];?></strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-5">Условия доставки</div>
                                    <div class="col-md-7"><strong><?=$arResult['REQUEST']['СпециальныеУсловия'];?></strong></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                            	<div class="row">
                                	<div class="col-md-5">Плательщик</div>
                                    <div class="col-md-7"><strong><?=$arResult['REQUEST']['ПризнакПлательщик'];?></strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-5">Тип оплаты</div>
                                    <div class="col-md-7"><strong><?=$arResult['REQUEST']['ПризнакТипОплаты'];?></strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-5">Сумма к оплате</div>
                                    <div class="col-md-7"><strong><?=$arResult['REQUEST']['СуммаКОплате'];?></strong></div>
                                </div>
                            </div>
                        </div>

					</div>
				</div>
			</div>
			<div class="col-md-4">
            	<div class="panel panel-default">
                    <div class="panel-body">
                    	<h4>Спец. инструкции</h4>
                        <?=$arResult['REQUEST']['СпециальныеИнструкции'];?>
					</div>
				</div>
				<div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-5">Ответственный менеджер</div>
                            <div class="col-md-7"><strong><?=$arResult['REQUEST']['Ответственный'];?></strong></div>
                        </div>
					</div>
				</div>
			</div>
        </div>
        <div class="row">
        	<div class="col-md-6">
				<?
				$APPLICATION->IncludeComponent(
					"black_mist:delivery.get_pods", 
					".default", 
					array(
						"SHOW_FORM" => "N",
						"CACHE_TYPE" => "A",
						"CACHE_TIME" => "3600",
						"SAVE_TO_SITE" => "N",
						"SHOW_TITLE" => "N",
						"SET_TITLE" => "N",
						"TEST_MODE" => "N",
						"COMPONENT_TEMPLATE" => ".default",
						"NO_TEMPLATE" => "N",
						"ONLY_1C_DATA" => "Y",
						"COMPOSITE_FRAME_MODE" => "A",
						"COMPOSITE_FRAME_TYPE" => "AUTO"
					),
					false
				);
				?>
            </div><!--col-->
        </div><!--row-->
		<div class="row">
			<div class="col-md-12">
				<h4>Комментарии</h4>
			</div>
        </div>
        <div class="row">
        	<div class="col-md-3">
                <div id="commentinfo"></div>
                <input type="hidden" id="comment_NUMDOC" value="<?=$arResult['REQUEST']['НомерНакладной'];?>">
                <input type="hidden" id="comment_NUMREQUEST" value="<?=$arResult['REQUEST']['НомерЗаявки'];?>">
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
    </div>
    <?
}