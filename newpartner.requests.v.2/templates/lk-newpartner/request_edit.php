<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
?>
<script type="text/javascript">
	$(document).ready(function(){
		AutoCompany();
		AutoCompanySender();
		AutoCity();
	});
	
	$(document).ready(function() {
		$('.maskdate').mask('99.99.9999');
		$('.masktime').mask('99:99');
        
        $('form[name="curform"]').keydown(function(event)
        {
            if (event.keyCode == 13 && event.ctrlKey)
            {
                $(this).submit();
                return true;
            }
            if(event.keyCode == 13)
            {
                event.preventDefault();
                return false;
            }
        })
	});
	
	function AutoCompanySender()
	{
		var url = '/search_city.php?type=name_company&company=<?=$arResult["AGENT"]["ID"];?>&type_company=259';
		$('#COMPANY_SENDER').autocomplete({
			source: url,
			minLength: 0,
			select: function( event, ui ) {
				$(this).val( ui.item.company);
				$('#NAME_SENDER').val(ui.item.name);
				$('#PHONE_SENDER').val(ui.item.phone);
				$('#autocity_sender').val(ui.item.city);
				$('#INDEX_SENDER').val(ui.item.index);
				$('#ADRESS_SENDER').val(ui.item.adress);
				return false;
			}
		});
	}

	
	function AutoCompany()
	{
		var url = '/search_city.php?type=name_company&company=<?=$arResult["AGENT"]["ID"];?>';
		$('#company').autocomplete({
			source: url,
			minLength: 0,
			select: function( event, ui ) {
				$(this).val( ui.item.company);
				$('#name').val(ui.item.name);
				$('#phone').val(ui.item.phone);
				$('#autocity_recipient').val(ui.item.city);
				$('#index').val(ui.item.index);
				$('#adress').val(ui.item.adress);
				return false;
			}
		});
	}
	
	function AutoCity()
	{
		var url = '/search_city.php?type=city';
		$('.autocity').autocomplete({
			source: url,
			minLength: 0,
			select: function( event, ui ) {
				$(this).val( ui.item.value);
				return false;
			}
		});
	}
</script>

<div class="row">
	<div class="col-md-12">
		<h3><?=$arResult['TITLE'];?> <?=strlen($arResult['REQUEST']['PROPERTY_COMMENT_VALUE']) ? '<span class="label label-danger">'.$arResult['REQUEST']['PROPERTY_COMMENT_VALUE'].'</span>' : '';?></h3>
	</div>
</div>
<?
if (count($arResult["ERRORS"]) > 0) 
{
	/*
	?>
    <div class="alert alert-dismissable alert-danger"><?=implode('</br>',$arResult["ERRORS"]);?></div>
    <?
	*/
}
if (count($arResult["MESSAGE"]) > 0) 
{
	?>
    <div class="alert alert-dismissable alert-success"><?=implode('</br>',$arResult["MESSAGE"]);?></div>
    <?
}
if (count($arResult["WARNINGS"]) > 0)
{
	?>
    <div class="alert alert-dismissable alert-warning"><?=implode('</br>',$arResult["WARNINGS"]);?></div>
    <?
}

if ($arResult['OPEN'] && $arResult['REQUEST'])
{
	?>
    	<form action="" method="post" name="curform" class="form-vertical" enctype="multipart/form-data">
		<input type="hidden" name="rand" value="<?=rand(100000,999999);?>">
		<input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>">
        <input type="hidden" name="id" value="<?=$arResult['REQUEST']['ID'];?>">
        <input type="hidden" name="save_ctrl" value="Сохранить">
		<div class="row">
			<div class="col-md-3">
            	<h4>Отправитель</h4>
                <div class="form-group <?=$arResult['ERR_FIELDS']['COMPANY_SENDER'];?>">
					<label class="control-label">Компания</label>
					<input type="text" class="form-control" name="COMPANY_SENDER" value="<?=$arResult['REQUEST']['PROPERTY_COMPANY_SENDER_VALUE'];?>" id="COMPANY_SENDER">
                </div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['NAME_SENDER'];?>">
					<label class="control-label">Фамилия</label>
					<input type="text" class="form-control" name="NAME_SENDER" value="<?=$arResult['REQUEST']['PROPERTY_NAME_SENDER_VALUE'];?>" id="NAME_SENDER">
				</div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['PHONE_SENDER'];?>">
					<label class="control-label">Телефон</label>
					<input type="text" class="form-control" name="PHONE_SENDER" value="<?=$arResult['REQUEST']['PROPERTY_PHONE_SENDER_VALUE'];?>" id="PHONE_SENDER">
				</div>
                <div class="form-group <?=$arResult['ERR_FIELDS']['CITY_SENDER'];?>">
                    <label class="control-label">Город</label>
                    <input type="text" class="form-control autocity" name="CITY_SENDER" value="<?=$arResult['REQUEST']['PROPERTY_CITY_SENDER'];?>" id="autocity_sender">
                </div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['INDEX_SENDER'];?>">
                    <label class="control-label">Индекс</label>
                    <input type="text" class="form-control" name="INDEX_SENDER" value="<?=$arResult['REQUEST']['PROPERTY_INDEX_SENDER_VALUE'];?>" id="INDEX_SENDER">
                </div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['ADRESS_SENDER'];?>">
                    <label class="control-label">Адрес</label>
                    <textarea class="form-control" name="ADRESS_SENDER" id="ADRESS_SENDER"><?=$arResult['REQUEST']['PROPERTY_ADRESS_SENDER_VALUE'];?></textarea>
                </div>
            </div>
            <div class="col-md-3">
				<h4>Получатель</h4>
				<div class="form-group <?=$arResult['ERR_FIELDS']['COMPANY_RECIPIENT'];?>">
					<label class="control-label">Компания</label>
					<input type="text" class="form-control" name="COMPANY_RECIPIENT" value="<?=$arResult['REQUEST']['PROPERTY_COMPANY_RECIPIENT_VALUE'];?>" id="company">
				</div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['NAME_RECIPIENT'];?>">
					<label class="control-label">Фамилия</label>
					<input type="text" class="form-control" name="NAME_RECIPIENT" value="<?=$arResult['REQUEST']['PROPERTY_NAME_RECIPIENT_VALUE'];?>" id="name">
				</div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['PHONE_RECIPIENT'];?>">
					<label class="control-label">Телефон</label>
					<input type="text" class="form-control" name="PHONE_RECIPIENT" value="<?=$arResult['REQUEST']['PROPERTY_PHONE_RECIPIENT_VALUE'];?>" id="phone">
				</div>
                <div class="form-group <?=$arResult['ERR_FIELDS']['CITY_RECIPIENT'];?>">
                    <label class="control-label">Город</label>
                    <input type="text" class="form-control autocity" name="CITY_RECIPIENT" value="<?=$arResult['REQUEST']['PROPERTY_CITY_RECIPIENT'];?>" id="autocity_recipient">
                </div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['INDEX_RECIPIENT'];?>">
                    <label class="control-label">Индекс</label>
                    <input type="text" class="form-control" name="INDEX_RECIPIENT" value="<?=$arResult['REQUEST']['PROPERTY_INDEX_RECIPIENT_VALUE'];?>" id="index">
                </div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['ADRESS_RECIPIENT'];?>">
                    <label class="control-label">Адрес</label>
                    <textarea class="form-control" name="ADRESS_RECIPIENT" id="adress"><?=$arResult['REQUEST']['PROPERTY_ADRESS_RECIPIENT_VALUE'];?></textarea>
                </div>
            </div>
            <div class="col-md-6">
            	<h4>Описание отправления</h4>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group <?=$arResult['ERR_FIELDS']['DATE_TAKE'];?>">
                            <label for="date_request" class="control-label">Дата и временной интервал забора</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <input type="text" name="DATE_TAKE" value="<?=$arResult['REQUEST']['PROPERTY_DATE_TAKE_VALUE'];?>" placeholder="ДД.ММ.ГГГГ" class="form-control maskdate" aria-describedby="basic-addon-1">
                                        <span class="input-group-addon" id="basic-addon-1">
                                            <?$APPLICATION->IncludeComponent(
                                                "bitrix:main.calendar",
                                                ".default",
                                                array(
                                                    "SHOW_INPUT" => "N",
                                                    "FORM_NAME" => "curform",
                                                    "INPUT_NAME" => "DATE_TAKE",
                                                    "INPUT_NAME_FINISH" => "",
                                                    "INPUT_VALUE" => $_POST['DATE_TAKE'],
                                                    "INPUT_VALUE_FINISH" => false,
                                                    "SHOW_TIME" => "N",
                                                    "HIDE_TIMEBAR" => "Y",
                                                    "INPUT_ADDITIONAL_ATTR" => ''
                                                ),
                                                false
                                            );
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-addon" id="basic-addon-2">с</span>
                                        <input type="text" name="TIME_TAKE_FROM" id="TIME_TAKE_FROM" placeholder="ЧЧ:ММ" class="form-control masktime" value="<?=$arResult['REQUEST']['PROPERTY_TIME_TAKE_FROM_VALUE'];?>" aria-describedby="basic-addon-2">
                                    </div>			
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-addon" id="basic-addon-3">до</span>
                                        <input type="text" name="TIME_TAKE_TO" id="TIME_TAKE_TO" placeholder="ЧЧ:ММ" class="form-control masktime" value="<?=$arResult['REQUEST']['PROPERTY_TIME_TAKE_TO_VALUE'];?>" aria-describedby="basic-addon-3">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="" class="control-label">Внутренний номер заявки</label>    
                            <input type="text" name="number_in" value="<?=$arResult['REQUEST']['PROPERTY_NUMBER_IN_VALUE'];?>" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="row">
                	<div class="col-md-4">
                        <div class="form-group <?=$arResult['ERR_FIELDS']['TYPE'];?>">
                            <label class="control-label">Тип отправления</label>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="TYPE" value="233" id="type_233" <?=($arResult['REQUEST']['PROPERTY_TYPE_ENUM_ID'] == 233) ? 'checked' : '';?>>Документы
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="TYPE" value="234" id="type_234" <?=($arResult['REQUEST']['PROPERTY_TYPE_ENUM_ID'] == 234) ? 'checked' : '';?>>Не документы
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="TYPE" value="235" id="type_235" <?=($arResult['REQUEST']['PROPERTY_TYPE_ENUM_ID'] == 235) ? 'checked' : '';?>>Опасный груз
                                </label>
                            </div>
                        </div><!--form-grou-->
                    </div><!--col-->
                    <div class="col-md-4">
						<div class="form-group">
                        	<label class="control-label">Тип доставки</label>
                            <div class="radio">
                            	<label><input type="radio" name="TYPE_DELIVERY" value="289" id="type_289" <?=($arResult['REQUEST']['PROPERTY_TYPE_DELIVERY_ENUM_ID'] == 289) ? 'checked' : '';?>>Экспресс</label>
                            </div>
                            <div class="radio">
                            	<label><input type="radio" name="TYPE_DELIVERY" value="290" id="type_290" <?=($arResult['REQUEST']['PROPERTY_TYPE_DELIVERY_ENUM_ID'] == 290) ? 'checked' : '';?>>Стандарт</label>
                            </div>
                            <div class="radio">
                            	<label><input type="radio" name="TYPE_DELIVERY" value="291" id="type_290" <?=($arResult['REQUEST']['PROPERTY_TYPE_DELIVERY_ENUM_ID'] == 291) ? 'checked' : '';?>>Эконом</label>
                            </div>
                        </div><!--form-group-->
                    </div><!--col-->
                    <div class="col-md-4">
                    	<div class="form-group">
                        	<label class="control-label">Условия доставки</label>
                            <div class="radio">
                            	<label>
                                	<input type="radio" name="DELIVERY_CONDITION" value="295" id="type_295" <?=($arResult['REQUEST']['PROPERTY_DELIVERY_CONDITION_ENUM_ID'] == 295) ? 'checked' : '';?>>По адресу
                                </label>
                            </div>
                            <div class="radio">
                            	<label>
                                	<input type="radio" name="DELIVERY_CONDITION" value="296" id="type_296" <?=($arResult['REQUEST']['PROPERTY_DELIVERY_CONDITION_ENUM_ID'] == 296) ? 'checked' : '';?>>До востребования
                                </label>
                            </div>
                            <div class="radio">
                            	<label>
                                	<input type="radio" name="DELIVERY_CONDITION" value="297" id="type_297" <?=($arResult['REQUEST']['PROPERTY_DELIVERY_CONDITION_ENUM_ID'] == 297) ? 'checked' : '';?>>Лично в руки
                                </label>
                            </div>
                        </div><!--form-group-->
                    </div><!--col-->
                </div><!--row-->
                <div class="row">
                    <div class="col-md-4">
						<div class="form-group">
                        	<label class="control-label">Плательщик</label>
                            <div class="radio">
                            	<label><input type="radio" name="DELIVERY_PAYER" value="292" id="type_292" <?=($arResult['REQUEST']['PROPERTY_DELIVERY_PAYER_ENUM_ID'] == 292) ? 'checked' : '';?>>Отправитель</label>
                            </div>
                            <div class="radio">
                            	<label><input type="radio" name="DELIVERY_PAYER" value="293" id="type_293" <?=($arResult['REQUEST']['PROPERTY_DELIVERY_PAYER_ENUM_ID'] == 293) ? 'checked' : '';?>>Получатель</label>
                            </div>
                            <div class="radio">
                            	<label><input type="radio" name="DELIVERY_PAYER" value="294" id="type_294" <?=($arResult['REQUEST']['PROPERTY_DELIVERY_PAYER_ENUM_ID'] == 294) ? 'checked' : '';?>>Другой</label>
                            </div>
                        </div><!--form-group-->
                    </div><!--col-->
                    <div class="col-md-4">
						<div class="form-group <?=$arResult['ERR_FIELDS']['TYPE_CASH'];?>">
                        	 <label class="control-label">Тип oплаты</label>
							 <div class="radio">
                                <label>
                                    <input type="radio" name="TYPE_CASH" value="264" id="type_264" <?=($arResult['REQUEST']['PROPERTY_TYPE_CASH_ENUM_ID'] == 264) ? 'checked' : '';?>>Наличными
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="TYPE_CASH" value="265" id="type_265" <?=($arResult['REQUEST']['PROPERTY_TYPE_CASH_ENUM_ID'] == 265) ? 'checked' : '';?>>Безналично
                                </label>
                            </div>
                        </div><!--form-group-->
                    </div><!--col-->
                    <div class="col-md-4">
						<div class="form-group">
                        	<label class="control-label">Сумма к оплате</label>
                            <div class="input-group">
                        		<input type="text" name="PAYMENT_AMOUNT" value="<?=$arResult['REQUEST']['PROPERTY_PAYMENT_AMOUNT_VALUE'];?>" placeholder="0,00" class="form-control" aria-describedby="basic-addon-8">
                            	<span class="input-group-addon" id="basic-addon-8">руб.</span>
                            </div>
                        </div><!--form-group-->
                    </div><!--col-->
                </div><!--row-->
                <div class="row">
                	<div class="col-md-3">
                        <div class="form-group <?=$arResult['ERR_FIELDS']['PLACES'];?>">
                            <label for="places" class="control-label">Количество мест</label>
                            <input type="text" name="PLACES" value="<?=$arResult['REQUEST']['PROPERTY_PLACES_VALUE'];?>" placeholder="0" id="places" class="form-control">
                        </div><!--form-group-->
                    </div><!--col-->
                    <div class="col-md-3">
                        <div class="form-group <?=$arResult['ERR_FIELDS']['WEIGHT'];?>">
                            <label for="" class="control-label">Вес</label>
                            <div class="input-group">
                                <input type="text" name="WEIGHT" value="<?=WeightFormat($arResult['REQUEST']['PROPERTY_WEIGHT_VALUE'], false);?>" placeholder="0,00" class="form-control"  aria-describedby="basic-addon-4">
                                <span class="input-group-addon" id="basic-addon-4">кг</span>
                            </div>
                        </div><!--form-group-->
                    </div><!--col-->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="" class="control-label">Габариты</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="input-group">
                                    <input type="text" name="size_1" value="<?=$arResult['REQUEST']['PROPERTY_SIZE_1_VALUE'];?>" placeholder="0,00" class="form-control" aria-describedby="basic-addon-5">
                                    <span class="input-group-addon" id="basic-addon-5">см</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                    <input type="text" name="size_2" value="<?=$arResult['REQUEST']['PROPERTY_SIZE_2_VALUE'];?>" placeholder="0,00" class="form-control" aria-describedby="basic-addon-6">
                                    <span class="input-group-addon" id="basic-addon-6">см</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                    <input type="text" name="size_3" value="<?=$arResult['REQUEST']['PROPERTY_SIZE_3_VALUE'];?>" placeholder="0,00" class="form-control" aria-describedby="basic-addon-7">
                                    <span class="input-group-addon" id="basic-addon-7">см</span>
                                    </div>
                                </div>
                            </div><!--row-->
                        </div><!--form-group-->
                    </div><!--col-->
                </div><!--row-->
                <div class="row">
                	<div class="col-md-12">
						<div class="form-group">
                            <label for="" class="control-label">Спец. инструкции</label>
                            <textarea name="INSTRUCTIONS" class="form-control"><?=$arResult['REQUEST']['PROPERTY_INSTRUCTIONS_VALUE'];?></textarea>
                        </div><!--form-group-->
                    </div><!--col-->
                </div><!--row-->
            </div>
		</div>

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
                                    <p>
                                        <a href="<?=$ff['SRC'];?>" target="_blank"><?=$ff['ORIGINAL_NAME'];?></a> 
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="delete_file[<?=$ff['ID'];?>]" value="Y"> Удалить
                                            </label>
                                        </div>
                                    </p>
                                    <input type="hidden" name="files_id_add[]" value="<?=$ff['ID'];?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <?
                }
				for ($i = 0; $i < ($arResult['COUNT_FILES'] - count($arResult['REQUEST']['FILES'])); $i++)
				{
					?>
					<div class="col-md-3">
                        <div class="form-group">
                        	<div class="panel panel-default">
								<div class="panel-body">
                            		<input type="file" id="exampleInputFile_<?=$i;?>_f" name="files_<?=$i;?>">
                            		<p>&nbsp;</p>
                            	</div>
                            </div>
                        </div>
                    </div>
                    <?
					$ii++;
				}
				?>
			</div>
			<div class="row">
                <div class="col-md-12">
                	<p class="help-block">Вы можете прикрепить к заявке до четырех файлов</p>
                </div>
            </div>
        
<button type="submit" name="save" class="btn btn-primary btn-lg">Сохранить <span class="badge">CTRL+Enter</span></button>
	</form>
	<?
}
?>
<br>