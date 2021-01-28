<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
if ($arResult['ADMIN_AGENT'])
{
	echo '<pre>';
	foreach ($arResult['times'] as $v)
	{
		printf('%.4F сек.: '.$v['name'], $v['val']);
		echo '<br>';
	}
	echo '</pre>';
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
		
		Costmarker();
		
		$( "#cost-value" ).change(function()
		{
			Costmarker();
		});
        
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
        });
	});
	
	function Costmarker()
	{
		$('#cost-marker').removeClass('glyphicon-remove');
		$('#cost-marker').removeClass('glyphicon-ok');
		$('#cost-marker').css('color','#555555');
		var vall = parseFloat($("#cost-value").val().replace(/[,]+/g, '.')) || 0;
		if (vall > 0)
		{
			$('#cost-marker').addClass('glyphicon-ok');
			$('#cost-marker').css('color','#468847');
		}
		else
		{
			$('#cost-marker').addClass('glyphicon-remove');
			$('#cost-marker').css('color','#b94a48');
		}
	}
	
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
        <h3><?=$arResult['TITLE'];?></h3>
    </div>
</div>
<?

if (count($arResult["ERRORS"]) > 0) 
{
	?>
    <div class="alert alert-dismissable alert-danger"><?=implode('</br>',$arResult["ERRORS"]);?></div>
    <?
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
if ($arResult['OPEN']) 
{
	
	?>
	<form action="<?=$arParams['LINK'];?>index.php?mode=add" method="post" name="curform" class="form-vertical" enctype="multipart/form-data" id="curform">
		<input type="hidden" name="rand" value="<?=rand(100000,999999);?>">
		<input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>">
		<input type="hidden" name="add_ctrl" value="Создать">
		<div class="row">
			<div class="col-md-3">
            	<h4>Отправитель</h4>
                <div class="form-group <?=$arResult['ERR_FIELDS']['COMPANY_SENDER'];?>">
					<label class="control-label">Организация</label>
                    <input type="text" class="form-control" name="COMPANY_SENDER" value="<?=strlen($_POST['COMPANY_SENDER']) ? NewQuotes($_POST['COMPANY_SENDER']) : $arResult['DEAULTS']['COMPANY_SENDER'];?>" id="COMPANY_SENDER">
					
                </div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['NAME_SENDER'];?>">
					<label class="control-label">Фамилия</label>
					<input type="text" class="form-control" name="NAME_SENDER" value="<?=strlen($_POST['NAME_SENDER']) ? $_POST['NAME_SENDER'] : $arResult['DEAULTS']['NAME_SENDER'];?>" id="NAME_SENDER">
				</div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['PHONE_SENDER'];?>">
					<label class="control-label">Телефон</label>
					<input type="text" class="form-control" name="PHONE_SENDER" value="<?=strlen($_POST['PHONE_SENDER']) ? $_POST['PHONE_SENDER'] : $arResult['DEAULTS']['PHONE_SENDER'];?>" id="PHONE_SENDER">
				</div>
                <div class="form-group <?=$arResult['ERR_FIELDS']['CITY_SENDER'];?>">
                    <label class="control-label">Город</label>
                    <input type="text" class="form-control autocity" name="CITY_SENDER" value="<?=strlen($_POST['CITY_SENDER']) ? $_POST['CITY_SENDER'] : $arResult['DEAULTS']['CITY_SENDER'];?>" id="autocity_sender">
                </div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['INDEX_SENDER'];?>">
                    <label class="control-label">Индекс</label>
                    <input type="text" class="form-control" name="INDEX_SENDER" value="<?=strlen($_POST['INDEX_SENDER']) ? $_POST['INDEX_SENDER'] : $arResult['DEAULTS']['INDEX_SENDER'];?>" id="INDEX_SENDER">
                </div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['ADRESS_SENDER'];?>">
                    <label class="control-label">Адрес</label>
                    <textarea class="form-control" name="ADRESS_SENDER" id="ADRESS_SENDER"><?=strlen($_POST['ADRESS_SENDER']) ? NewQuotes($_POST['ADRESS_SENDER']) : $arResult['DEAULTS']['ADRESS_SENDER'];?></textarea>
                </div>
            </div>
            <div class="col-md-3">
				<h4>Получатель</h4>
				<div class="form-group <?=$arResult['ERR_FIELDS']['COMPANY_RECIPIENT'];?>">
					<label class="control-label">Организация</label>
					<input type="text" class="form-control" name="COMPANY_RECIPIENT" value="<?=strlen($_POST['COMPANY_RECIPIENT']) ? NewQuotes($_POST['COMPANY_RECIPIENT']) : $arResult['DEAULTS']['COMPANY_RECIPIENT'];?>" id="company">
				</div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['NAME_RECIPIENT'];?>">
					<label class="control-label">Фамилия</label>
					<input type="text" class="form-control" name="NAME_RECIPIENT" value="<?=strlen($_POST['NAME_RECIPIENT']) ? $_POST['NAME_RECIPIENT'] : $arResult['DEAULTS']['NAME_RECIPIENT'];?>" id="name">
				</div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['PHONE_RECIPIENT'];?>">
					<label class="control-label">Телефон</label>
					<input type="text" class="form-control" name="PHONE_RECIPIENT" value="<?=strlen($_POST['PHONE_RECIPIENT']) ? $_POST['PHONE_RECIPIENT'] : $arResult['DEAULTS']['PHONE_RECIPIENT'];?>" id="phone">
				</div>
                <div class="form-group <?=$arResult['ERR_FIELDS']['CITY_RECIPIENT'];?>">
                    <label class="control-label">Город</label>
                    <input type="text" class="form-control autocity" name="CITY_RECIPIENT" value="<?=strlen($_POST['CITY_RECIPIENT']) ? $_POST['CITY_RECIPIENT'] : $arResult['DEAULTS']['CITY_RECIPIENT'];?>" id="autocity_recipient">
                </div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['INDEX_RECIPIENT'];?>">
                    <label class="control-label">Индекс</label>
                    <input type="text" class="form-control" name="INDEX_RECIPIENT" value="<?=strlen($_POST['INDEX_RECIPIENT']) ? $_POST['INDEX_RECIPIENT'] : $arResult['DEAULTS']['INDEX_RECIPIENT'];?>" id="index">
                </div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['ADRESS_RECIPIENT'];?>">
                    <label class="control-label">Адрес</label>
                    <textarea class="form-control" name="ADRESS_RECIPIENT" id="adress"><?=strlen($_POST['ADRESS_RECIPIENT']) ? NewQuotes($_POST['ADRESS_RECIPIENT']) : $arResult['DEAULTS']['ADRESS_RECIPIENT'];?></textarea>
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
                                        <input type="text" name="DATE_TAKE" value="<?=$_POST['DATE_TAKE'];?>" placeholder="ДД.ММ.ГГГГ" class="form-control maskdate" aria-describedby="basic-addon-1">
                                        <span class="input-group-addon" id="basic-addon-1">
                                        <?
                                        $APPLICATION->IncludeComponent(
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
                                    <input type="text" name="TIME_TAKE_FROM" id="TIME_TAKE_FROM" placeholder="ЧЧ:ММ" class="form-control masktime" value="<?=$_POST['TIME_TAKE_FROM'];?>" aria-describedby="basic-addon-2">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-addon" id="basic-addon-3">до</span>
                                        <input type="text" name="TIME_TAKE_TO" id="TIME_TAKE_TO" placeholder="ЧЧ:ММ" class="form-control masktime" value="<?=$_POST['TIME_TAKE_TO'];?>" aria-describedby="basic-addon-3"> 
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!--col-->
                  <div class="col-md-4">
						<div class="form-group">
                            <label for="" class="control-label">Внутренний номер заявки</label>
                            <input type="text" name="number_in" value="<?/*=$_POST['number_in'];*/?>" class="form-control">
                        </div>
                    </div><!--col-->
                </div><!--row-->
				<div class="row">
                	<div class="col-md-4">
						<div class="form-group <?=$arResult['ERR_FIELDS']['TYPE'];?>">
                        	<?
							$type = ($_POST['TYPE']) ?  ($_POST['TYPE']) : $arResult['DEAULTS']['TYPE'];
 							?>
                            <label class="control-label">Тип отправления</label>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="TYPE" value="233" id="type_233" <?=($type == 233) ? 'checked' : '';?>>Документы
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="TYPE" value="234" id="type_234" <?=($type == 234) ? 'checked' : '';?>>Не документы
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="TYPE" value="235" id="type_235" <?=($type == 235) ? 'checked' : '';?>>Опасный груз
                                </label>
                            </div><!--radio-->
                        </div><!--form-group-->
                    </div><!--col-->
                    <div class="col-md-4">
						<div class="form-group">
                        	<?
							$TYPE_DELIVERY = ($_POST['TYPE_DELIVERY']) ?  ($_POST['TYPE_DELIVERY']) : $arResult['DEAULTS']['TYPE_DELIVERY'];
 							?>
                        	<label class="control-label">Тип доставки</label>
                            <div class="radio">
                            	<label><input type="radio" name="TYPE_DELIVERY" value="289" id="type_289" <?=($TYPE_DELIVERY == 289) ? 'checked' : '';?>>Экспресс</label>
                            </div>
                            <div class="radio">
                            	<label><input type="radio" name="TYPE_DELIVERY" value="290" id="type_290" <?=($TYPE_DELIVERY == 290) ? 'checked' : '';?>>Стандарт</label>
                            </div>
                           <!-- <div class="radio">
                            	<label><input type="radio" name="TYPE_DELIVERY" value="291" id="type_291" <?/*=($TYPE_DELIVERY == 291) ? 'checked' : '';*/?>>Эконом</label>
                            </div>-->
                            <div class="radio">
                            	<label><input type="radio" name="TYPE_DELIVERY" value="335" id="type_335" <?=($TYPE_DELIVERY == 335) ? 'checked' : '';?>>Склад-Склад</label>
                            </div>
                            <div class="radio">
                            	<label><input type="radio" name="TYPE_DELIVERY" value="337" id="type_337" <?=($TYPE_DELIVERY == 337) ? 'checked' : '';?>>Экспресс 8</label>
                            </div>
                            <div class="checkbox">
                                <label class="control-label btn-default btn " style="padding-left: 30px;">
                                    <input type="checkbox"  name="TRANSPORT_TYPE" id="transport_type">Наземный транспорт
                                </label>
                                <small style="display: block">Установите отметку если доставка наземным транспортом</small>
                            </div>
                        </div><!--form-group-->
                    </div><!--col-->
                    <div class="col-md-4">
                    	<div class="form-group">
                        	<?
							$DELIVERY_CONDITION = ($_POST['DELIVERY_CONDITION']) ?  ($_POST['DELIVERY_CONDITION']) : $arResult['DEAULTS']['DELIVERY_CONDITION'];
 							?>
                        	<label class="control-label">Условия доставки</label>
                            <div class="radio">
                            	<label>
                                	<input type="radio" name="DELIVERY_CONDITION" value="295" id="type_295" <?=($DELIVERY_CONDITION == 295) ? 'checked' : '';?>>По адресу
                                </label>
                            </div>
                            <div class="radio">
                            	<label>
                                	<input type="radio" name="DELIVERY_CONDITION" value="296" id="type_296" <?=($DELIVERY_CONDITION == 296) ? 'checked' : '';?>>До востребования
                                </label>
                            </div>
                            <div class="radio">
                            	<label>
                                	<input type="radio" name="DELIVERY_CONDITION" value="297" id="type_297" <?=($DELIVERY_CONDITION == 297) ? 'checked' : '';?>>Лично в руки
                                </label>
                            </div>
                        </div><!--form-group-->
                    </div><!--col-->
                </div><!--row-->
                <div class="row">
                	<div class="col-md-4">
						<div class="form-group">
                       	    <?
							$DELIVERY_PAYER = ($_POST['DELIVERY_PAYER']) ?  ($_POST['DELIVERY_PAYER']) : $arResult['DEAULTS']['DELIVERY_PAYER'];
 							?>
                        	<label class="control-label">Плательщик</label>
                            <div class="radio">
                            	<label><input type="radio" name="DELIVERY_PAYER" value="292" id="type_292" <?=($DELIVERY_PAYER == 292) ? 'checked' : '';?>>Отправитель</label>
                            </div>
                            <div class="radio">
                            	<label><input type="radio" name="DELIVERY_PAYER" value="293" id="type_293" <?=($DELIVERY_PAYER == 293) ? 'checked' : '';?>>Получатель</label>
                            </div>
                            <div class="radio">
                            	<label><input type="radio" name="DELIVERY_PAYER" value="294" id="type_294" <?=($DELIVERY_PAYER == 294) ? 'checked' : '';?>>Другой</label>
                            </div>
                        </div><!--form-group-->
                    </div><!--col-->
                    <div class="col-md-4">
						<div class="form-group <?=$arResult['ERR_FIELDS']['TYPE_CASH'];?>">
                       	    <?
							$TYPE_CASH = ($_POST['TYPE_CASH']) ?  ($_POST['TYPE_CASH']) : $arResult['DEAULTS']['TYPE_CASH'];
 							?>
                        	 <label class="control-label">Тип oплаты</label>
							 <div class="radio">
                                <label>
                                    <input type="radio" name="TYPE_CASH" value="264" id="type_264" <?=($TYPE_CASH == 264) ? 'checked' : '';?>>Наличными
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="TYPE_CASH" value="265" id="type_265" <?=($TYPE_CASH == 265) ? 'checked' : '';?>>Безналично
                                </label>
                            </div><!--radio-->
                        </div><!--form-group-->
                    </div><!--col-->
                    <div class="col-md-4">
						<div class="form-group">
                        	<label class="control-label">Сумма к оплате</label>
                            <div class="input-group">
                        		<input type="text" name="PAYMENT_AMOUNT" value="<?=$_POST['PAYMENT_AMOUNT'];?>" placeholder="0,00" class="form-control" aria-describedby="basic-addon-8">
                            	<span class="input-group-addon" id="basic-addon-8">руб.</span>
                            </div>
                        </div><!--form-group-->
						<div class="form-group <?=$arResult['ERR_FIELDS']['COST'];?>">
                        	<label class="control-label">Объявленная стоимость</label>
							<div class="input-group">
                            	<input type="text" class="form-control" name="COST" value="<?=$_POST['COST'];?>"  placeholder="0,00" aria-describedby="basic-addon-9" id="cost-value">
                                <span class="input-group-addon" id="basic-addon-9">руб.</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label">
                                <span class="glyphicon glyphicon-remove" aria-hidden="true" style="color:#b94a48;" id="cost-marker"></span> Заявка на страхование
                            </label>
                        </div>     
                    </div><!--col-->
                </div><!--row-->
                <div class="row">
                	<div class="col-md-3">
                    	<div class="form-group <?=$arResult['ERR_FIELDS']['PLACES'];?>">
                        	<label for="places" class="control-label">Количество мест</label>
							<input type="text" name="PLACES" value="<?=strlen($_POST['PLACES']) ? $_POST['PLACES'] : $arResult['DEAULTS']['PLACES'];?>" placeholder="0" id="places" class="form-control">
                        </div><!--form-group-->
                    </div><!--col-->
                    <div class="col-md-3">
                    	<div class="form-group <?=$arResult['ERR_FIELDS']['WEIGHT'];?>">
                            <label for="" class="control-label">Вес</label>
                            <div class="input-group">
                                <input type="text" name="WEIGHT" value="<?=$_POST['WEIGHT'];?>" placeholder="0,00" class="form-control" aria-describedby="basic-addon-4">
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
                                    <input type="text" name="size_1" value="<?=$_POST['size_1'];?>" placeholder="0,00" class="form-control" aria-describedby="basic-addon-5">
                                    <span class="input-group-addon" id="basic-addon-5">см</span>
                                    </div>
                                </div><!--col-->
                                <div class="col-md-4">
                                    <div class="input-group">
                                    <input type="text" name="size_2" value="<?=$_POST['size_2'];?>" placeholder="0,00" class="form-control" aria-describedby="basic-addon-6">
                                    <span class="input-group-addon" id="basic-addon-6">см</span>
                                    </div>
                                </div><!--col-->
                                <div class="col-md-4">
                                    <div class="input-group">
                                    <input type="text" name="size_3" value="<?=$_POST['size_3'];?>" placeholder="0,00" class="form-control" aria-describedby="basic-addon-7">
                                    <span class="input-group-addon" id="basic-addon-7">см</span>
                                    </div>
                                </div><!--col-->
                            </div><!--row-->
                        </div><!--form-group-->
                    </div><!--col-->
                </div><!--row-->
                <div class="row">
                	<div class="col-md-12">
						<div class="form-group">
                            <label for="" class="control-label">Спец. инструкции</label>
                            <textarea name="instructions" class="form-control"><?=strlen($_POST['instructions']) ? $_POST['instructions'] : $arResult['DEAULTS']['INSTRUCTIONS'];?></textarea>
                        </div>
                    </div>
                </div>
            </div>
		</div>
        <div class="row">
            <div class="col-md-12">
            <h4>Дополнительные файлы</h4>
            </div>
        </div>
        <div class="row">
            <?
            $ii = 0;
            if (count($arResult['FILES_ADD']) > 0)
            {
                foreach ($arResult['FILES_ADD'] as $ff)
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
                                    <input type="hidden" name="files_id_add[]" value="<?=$ff['ID'];?>" id="exampleInputFile_<?=$ii;?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <?
                    $ii++;
                 }
            }
            for ($i = 0; $i < $arResult['COUNT_FILES']; $i++)
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
        <button type="submit" name="add" class="btn btn-primary btn-lg">Создать заявку <span class="badge">CTRL+Enter</span></button>
	</form>
	<?
}
?>
<br>