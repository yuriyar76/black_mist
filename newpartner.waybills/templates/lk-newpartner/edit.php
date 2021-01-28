<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
} ?>
<script type="text/javascript">
	$(document).ready(function(){
		AutoCompany();
		AutoCompanySender();
		AutoCity();
        
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
	
	function AutoCompanySender()
	{
		var url = '/search_city.php?type=name_company&company=<?=$arResult['CURRENT_AGENT'];?>&type_company=259';
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
		var url = '/search_city.php?type=name_company&company=<?=$arResult['CURRENT_AGENT'];?>';
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
	
	function SelectCompanyPayer()
	{
		var kk = $('#company_payer_id').val();
		if (kk > 0)
		{
			$.get('/search_city.php', {CompanyPayer: 'true', CompanyPayerID: kk}, function(data) {
				var c_text = '';
				var b_text = '';
				data = $.parseJSON(data);
				if (data.ID > 0)
				{
					if (data.CONTRACTS.length > 0)
					{
						c_text = '<input type="hidden" name="yes_contracts" value="1">' +
						'<select class="form-control" name="company_contract" id="company_contract">';
						if (data.CONTRACTS.length > 1)
						{
							c_text = c_text + '<option value="0"></option>';
						}
						$.each(data.CONTRACTS,function(key,val){
							c_text = c_text + '<option value="'+val.ID+'">№'+val.PROPERTY_NUMBER_VALUE+' от '+val.PROPERTY_DATE_VALUE+'</option>';
						});
						c_text = c_text +'</select>';
					}
					if (data.BRANCHES.length > 0)
					{
						b_text = '<input type="hidden" name="yes_branches" value="1">' + 
						'<select class="form-control" name="company_branch" id="company_branch" onChange="SelectBranchPayer();">';
						if (data.BRANCHES.length > 1)
						{
							b_text = b_text + '<option value="0"></option>';
						}
						$.each(data.BRANCHES,function(key,val){
							b_text = b_text + '<option value="'+val.ID+'">'+val.NAME+' ['+val.PROPERTY_CITY_NAME+', '+val.PROPERTY_ADRESS_VALUE+']</option>';
						});
						b_text = b_text +'</select>';
					}
				}
				$('#c_in_text').html(c_text);
				$('#b_in_text').html(b_text);
			})	
		}
		else
		{
			$('#c_in_text').html('');
			$('#b_in_text').html('');
			$('#hid_payer_company').val('');
		}
	}
	
	function SelectBranchPayer()
	{
		var payer = $('#company_payer_id').val();
		var branch = $('#company_branch').val();
		
		if ((branch > 0) && (payer > 0))
		{
			$.get('/search_city.php', {CompanyBranch: 'true', CompanyID: payer, CompanyBranchID: branch}, function(data) {
				data = $.parseJSON(data);
				if (data.ID > 0)
				{
					$('#hid_payer_company').val(data.NAME);
					$('#hid_payer_name').val(data.PROPERTY_FIO_VALUE);
					$('#hid_payer_phone').val(data.PROPERTY_PHONE_VALUE);
					$('#hid_payer_city').val(data.PROPERTY_CITY);
					$('#hid_payer_index').val(data.PROPERTY_INDEX_VALUE);
					$('#hid_payer_adress').val(data.PROPERTY_ADRESS_VALUE);
				}
				else
				{
					$('#hid_payer_company').val('');
					$('#hid_payer_name').val('');
					$('#hid_payer_phone').val('');
					$('#hid_payer_city').val('');
					$('#hid_payer_index').val('');
					$('#hid_payer_adress').val('');
				}
			})
		}
		else
		{
			$('#hid_payer_company').val('');
			$('#hid_payer_name').val('');
			$('#hid_payer_phone').val('');
			$('#hid_payer_city').val('');
			$('#hid_payer_index').val('');
			$('#hid_payer_adress').val('');
		}
	}
	
	function SelectType(type)
	{
		if (type == 'sender')
		{
			/*
			$('#company').val($('#COMPANY_SENDER').val());
			$('#name').val($('#NAME_SENDER').val());
			$('#phone').val($('#PHONE_SENDER').val());
			$('#autocity_recipient').val($('#autocity_sender').val());
			$('#index').val($('#INDEX_SENDER').val());
			$('#adress').val($('#ADRESS_SENDER').val());
			*/
			$('#COMPANY_SENDER').val($('#hid_payer_company').val());
			$('#NAME_SENDER').val($('#hid_payer_name').val());
			$('#PHONE_SENDER').val($('#hid_payer_phone').val());
			$('#autocity_sender').val($('#hid_payer_city').val());
			$('#INDEX_SENDER').val($('#hid_payer_index').val());
			$('#ADRESS_SENDER').val($('#hid_payer_adress').val());
		}
		if (type == 'recipient')
		{
			/*
			$('#COMPANY_SENDER').val($('#company').val());
			$('#NAME_SENDER').val($('#name').val());
			$('#PHONE_SENDER').val($('#phone').val());
			$('#autocity_sender').val($('#autocity_recipient').val());
			$('#INDEX_SENDER').val($('#index').val());
			$('#ADRESS_SENDER').val($('#adress').val());
			*/
			$('#company').val($('#hid_payer_company').val());
			$('#name').val($('#hid_payer_name').val());
			$('#phone').val($('#hid_payer_phone').val());
			$('#autocity_recipient').val($('#hid_payer_city').val());
			$('#index').val($('#hid_payer_index').val());
			$('#adress').val($('#hid_payer_adress').val());
		}
	}
</script>
<?
if (count($arResult["ERRORS"]) > 0) 
{
	?>
    <div class="alert alert-dismissable alert-danger fade in" role="alert">
        <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">X</span><span class="sr-only">Закрыть</span></button>
        <?=implode('</br>',$arResult["ERRORS"]);?>
    </div>
    <?
}
if (count($arResult["MESSAGE"]) > 0) 
{
	?>
    <div class="alert alert-dismissable alert-success fade in" role="alert">
        <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">X</span><span class="sr-only">Закрыть</span></button>
        <?=implode('</br>',$arResult["MESSAGE"]);?>
    </div>
    <?
}
if (count($arResult["WARNINGS"]) > 0)
{
	?>
    <div class="alert alert-dismissable alert-success fade in" role="alert">
    	<button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">X</span><span class="sr-only">Закрыть</span></button>
		<?=implode('</br>',$arResult["WARNINGS"]);?>
    </div>
    <?
}
if ($arResult['OPEN'] && $arResult['INVOICE'])
{
	?>
	<form action="" method="post" name="curform" class="form-vertical">
		<input type="hidden" name="rand" value="<?=rand(100000,999999);?>">
		<input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>">
        <input type="hidden" name="id" value="<?=$arResult['INVOICE']['ID'];?>">
        <input type="hidden" name="save_ctrl" value="Сохранить">
		<div class="panel panel-default">
            <div class="panel-body">
				<div class="row">
					<div class="col-md-3">
                    	<h4>Плательщик</h4>
                    </div>
					<div class="col-md-5 col-md-offset-4">
                    	<div class="form-group <?=strlen($arResult['ERR_FIELDS']['NUMBER']) ? $arResult['ERR_FIELDS']['NUMBER'] : 'has-success';?>">
                            <div class="input-group">
								<span class="input-group-addon" id="number">Номер накладной</span>
								<input type="text" class="form-control" name="NUMBER" value="<?=strlen(NewQuotes($_POST["NUMBER"])) ? NewQuotes($_POST["NUMBER"]) : $arResult['INVOICE']['NAME'];?>" id="NUMBER" aria-describedby="number">
                            </div>
                        </div>
                    </div>
				</div>
                <div class="row">
                	<div class="col-md-2">
                    	<div class="form-group <?=$arResult['ERR_FIELDS']['COMPANY_PAYER'];?>">
                        	<label class="control-label">Компания</label>
							<select class="form-control" name="company_payer" onChange="SelectCompanyPayer();" id="company_payer_id">
								<? if (count($arResult['PAYERS']) > 1) : ?>
									<option value="0"></option>
                                <? endif;?>
								<?
                                foreach ($arResult['PAYERS'] as $s)
                                {
                                    ?>
                                    <option value="<?=$s['ID'];?>"<?=($arResult['INVOICE']['PROPERTY_CREATOR_VALUE'] == $s['ID']) ? ' selected' : '';?>><?=$s['NAME'];?>, <?=$s["PROPERTY_CITY_NAME"];?></option>
                                    <?
                                }
                                ?>
                        	</select>
                            <input type="hidden" name="hid_payer_company" id="hid_payer_company" value="<?=$_POST["hid_payer_company"];?>">
                            <input type="hidden" name="hid_payer_name" id="hid_payer_name" value="<?=$_POST["hid_payer_name"];?>">
                            <input type="hidden" name="hid_payer_phone" id="hid_payer_phone" value="<?=$_POST["hid_payer_phone"];?>">
                            <input type="hidden" name="hid_payer_city" id="hid_payer_city" value="<?=$_POST["hid_payer_city"];?>">
                            <input type="hidden" name="hid_payer_index" id="hid_payer_index" value="<?=$_POST["hid_payer_index"];?>">
                            <input type="hidden" name="hid_payer_adress" id="hid_payer_adress" value="<?=$_POST["hid_payer_adress"];?>">
                        </div>
                    </div>
                    <div class="col-md-2 col-md-offset-1">
                        <div class="form-group <?=$arResult['ERR_FIELDS']['CONTRACT_PAYER'];?>">
                            <label class="control-label">Договор</label>
                            <div id="c_in_text">
                            	<?
								if (($arResult['SHOW_CONTRACTS_AND_BRANCHES']) && (count($arResult["INFO"]["CONTRACTS"]) > 0))
								{
									
									?>
                                    <input type="hidden" name="yes_contracts" value="1">
                                    <select class="form-control" name="company_contract" id="company_contract">
										<? if (count($arResult["INFO"]["CONTRACTS"]) > 1) : ?>
                                            <option value="0"></option>
                                        <? endif;?>
                                        <?
										foreach ($arResult["INFO"]["CONTRACTS"] as $val)
										{
											?>
                                            <option value="<?=$val['ID'];?>"<?=($arResult['INVOICE']['PROPERTY_CONTRACT_VALUE'] == $val['ID']) ? ' selected' : '';?>>№<?=$val["PROPERTY_NUMBER_VALUE"];?> от <?=$val["PROPERTY_DATE_VALUE"];?></option>
                                            <?
										}
										?>
                                    </select>
                                    <?
								}
								?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-md-offset-1">
                        <div class="form-group <?=$arResult['ERR_FIELDS']['BRANCH_PAYER'];?>">
                            <label class="control-label">Филиал</label>
                            <div id="b_in_text">
								<?
								if (($arResult['SHOW_CONTRACTS_AND_BRANCHES']) && (count($arResult["INFO"]["BRANCHES"]) > 0))
								{
									
									?>
                                    <input type="hidden" name="yes_branches" value="1">
                                    <select class="form-control" name="company_branch" id="company_branch" onChange="SelectBranchPayer();">
										<? if (count($arResult["INFO"]["BRANCHES"]) > 1) : ?>
                                            <option value="0"></option>
                                        <? endif;?>
                                        <?
										foreach ($arResult["INFO"]["BRANCHES"] as $val)
										{
											?>
                                            <option value="<?=$val['ID'];?>"<?=($arResult['INVOICE']['PROPERTY_BRANCH_VALUE'] == $val['ID']) ? ' selected' : '';?>><?=$val["NAME"];?> [<?=$val["PROPERTY_CITY_NAME"];?>, <?=$val["PROPERTY_ADRESS_VALUE"];?>]</option>
                                            <?
										}
										?>
                                    </select>
                                    <?
								}

								?>
                            </div>
                        </div>
                    </div>
                    <?
					if ($arResult['LIST_OF_AGENTS'])
					{
						?>
                        <div class="col-md-2 col-md-offset-1">
                            <div class="form-group">
                                <label class="control-label">Агент</label>
                                <select name="agent" size="1" class="form-control">
                                    <?
                                    foreach ($arResult['LIST_OF_AGENTS'] as $k => $v)
                                    {
                                        $s = ($arResult['INVOICE']['PROPERTY_AGENT_VALUE'] == $k) ? ' selected' : '';
                                        ?>
                                        <option value="<?=$k;?>"<?=$s;?>><?=$v;?></option>
                                        <?
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    	<?
					}
					?>
                </div>
            </div>
        </div>
        <div class="row">
        	 <div class="col-md-3">
             	<h4>Отправитель</h4>
				<div class="form-group <?=$arResult['ERR_FIELDS']['COMPANY_SENDER'];?>">
					<label class="control-label">Компания</label>
					<input type="text" class="form-control" name="COMPANY_SENDER" value="<?=strlen(NewQuotes($_POST['COMPANY_SENDER'])) ? NewQuotes($_POST['COMPANY_SENDER']) : $arResult['INVOICE']['PROPERTY_COMPANY_SENDER_VALUE'];?>" id="COMPANY_SENDER">
				</div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['NAME_SENDER'];?>">
					<label class="control-label">Фамилия</label>
					<input type="text" class="form-control" name="NAME_SENDER" value="<?=strlen($_POST['NAME_SENDER']) ? $_POST['NAME_SENDER'] : $arResult['INVOICE']['PROPERTY_NAME_SENDER_VALUE'];?>" id="NAME_SENDER">
				</div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['PHONE_SENDER'];?>">
					<label class="control-label">Телефон</label>
					<input type="text" class="form-control" name="PHONE_SENDER" value="<?=strlen($_POST['PHONE_SENDER']) ? $_POST['PHONE_SENDER'] : $arResult['INVOICE']['PROPERTY_PHONE_SENDER_VALUE'];?>" id="PHONE_SENDER">
				</div>
                <div class="form-group <?=$arResult['ERR_FIELDS']['CITY_SENDER'];?>">
                    <label class="control-label">Город</label>
                    <input type="text" class="form-control autocity" name="CITY_SENDER" value="<?=strlen($_POST['CITY_SENDER']) ? $_POST['CITY_SENDER'] : $arResult['INVOICE']['PROPERTY_CITY_SENDER'];?>" id="autocity_sender">
                </div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['INDEX_SENDER'];?>">
                    <label class="control-label">Индекс</label>
                    <input type="text" class="form-control" name="INDEX_SENDER" value="<?=strlen($_POST['INDEX_SENDER']) ? $_POST['INDEX_SENDER'] : $arResult['INVOICE']['PROPERTY_INDEX_SENDER_VALUE'];?>" id="INDEX_SENDER">
                </div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['ADRESS_SENDER'];?>">
                    <label class="control-label">Адрес</label>
                    <textarea class="form-control" name="ADRESS_SENDER" id="ADRESS_SENDER"><?=strlen($_POST['ADRESS_SENDER']) ? $_POST['ADRESS_SENDER'] : $arResult['INVOICE']['PROPERTY_ADRESS_SENDER_VALUE']['TEXT'];?></textarea>
                </div>
             </div>
             <div class="col-md-3 col-md-offset-1">
             	<h4>Получатель</h4>
				<div class="form-group <?=$arResult['ERR_FIELDS']['COMPANY_RECIPIENT'];?>">
					<label class="control-label">Компания</label>
					<input type="text" class="form-control" name="COMPANY_RECIPIENT" value="<?=strlen(NewQuotes($_POST['COMPANY_RECIPIENT'])) ? NewQuotes($_POST['COMPANY_RECIPIENT']) : $arResult['INVOICE']['PROPERTY_COMPANY_RECIPIENT_VALUE'];?>" id="company">
				</div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['NAME_RECIPIENT'];?>">
					<label class="control-label">Фамилия</label>
					<input type="text" class="form-control" name="NAME_RECIPIENT" value="<?=strlen($_POST['NAME_RECIPIENT']) ? $_POST['NAME_RECIPIENT'] : $arResult['INVOICE']['PROPERTY_NAME_RECIPIENT_VALUE'];?>" id="name">
				</div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['PHONE_RECIPIENT'];?>">
					<label class="control-label">Телефон</label>
					<input type="text" class="form-control" name="PHONE_RECIPIENT" value="<?=strlen($_POST['PHONE_RECIPIENT']) ? $_POST['PHONE_RECIPIENT'] : $arResult['INVOICE']['PROPERTY_PHONE_RECIPIENT_VALUE'];?>" id="phone">
				</div>
                <div class="form-group <?=$arResult['ERR_FIELDS']['CITY_RECIPIENT'];?>">
                    <label class="control-label">Город</label>
                    <input type="text" class="form-control autocity" name="CITY_RECIPIENT" value="<?=strlen($_POST['CITY_RECIPIENT']) ? $_POST['CITY_RECIPIENT'] : $arResult['INVOICE']['PROPERTY_CITY_RECIPIENT'];?>" id="autocity_recipient">
                </div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['INDEX_RECIPIENT'];?>">
                    <label class="control-label">Индекс</label>
                    <input type="text" class="form-control" name="INDEX_RECIPIENT" value="<?=strlen($_POST['INDEX_RECIPIENT']) ? $_POST['INDEX_RECIPIENT'] : $arResult['INVOICE']['PROPERTY_INDEX_RECIPIENT_VALUE'];?>" id="index">
                </div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['ADRESS_RECIPIENT'];?>">
                    <label class="control-label">Адрес</label>
                    <textarea class="form-control" name="ADRESS_RECIPIENT" id="adress"><?=strlen($_POST['ADRESS_RECIPIENT']) ? $_POST['ADRESS_RECIPIENT'] : $arResult['INVOICE']['PROPERTY_ADRESS_RECIPIENT_VALUE']['TEXT'];?></textarea>
                </div>
             </div>
             <div class="col-md-4 col-md-offset-1">
                <div class="row">
                	<div class="col-md-6">
                    	<h4>Условия доставки</h4>
						<div class="form-group <?=$arResult['ERR_FIELDS']['TYPE_DELIVERY'];?>">
                        	<?
							$type_delivery = ($_POST['TYPE_DELIVERY']) ?  ($_POST['TYPE_DELIVERY']) : $arResult['INVOICE']['PROPERTY_TYPE_DELIVERY_ENUM_ID'];
							?>
                            <label class="control-label">Тип доставки</label>
                            <div class="radio">
                              <label>
                                <input name="TYPE_DELIVERY" value="243" type="radio" <?=($type_delivery == 243) ? 'checked=""' : '';?>>
                                Экспресс
                              </label>
                            </div>
                            <div class="radio">
                              <label>
                                <input name="TYPE_DELIVERY" value="244" type="radio" <?=($type_delivery == 244) ? 'checked=""' : '';?>>
                                Стандарт
                              </label>
                            </div>
                            <div class="radio">
                              <label>
                                <input name="TYPE_DELIVERY" value="245" type="radio" <?=($type_delivery == 245) ? 'checked=""' : '';?>>
                                Эконом
                              </label>
                            </div>
                        </div>
                            
						<div class="form-group <?=$arResult['ERR_FIELDS']['TYPE_PACK'];?>">
                        	<?
							$type_pack = ($_POST['TYPE_PACK']) ?  ($_POST['TYPE_PACK']) : $arResult['INVOICE']['PROPERTY_TYPE_PACK_ENUM_ID'];
							?>
                            <label class="control-label">Тип отправления</label>
                            <div class="radio">
                              <label>
                                <input name="TYPE_PACK" value="246" type="radio" <?=($type_pack == 246) ? 'checked=""' : '';?>>
                                Документы
                              </label>
                            </div>
                            <div class="radio">
                              <label>
                                <input name="TYPE_PACK" value="247" type="radio" <?=($type_pack == 247) ? 'checked=""' : '';?>>
                                Не документы
                              </label>
                            </div>
                        </div>
                         
                    </div>
					<div class="col-md-6">
						<div class="form-group <?=$arResult['ERR_FIELDS']['DATE_INVOICE'];?>">
							<label class="control-label">Дата накладной</label>
							<div class="input-group">
                            	<input type="text" class="form-control" placeholder="ДД.ММ.ГГГГ" value="<?=strlen($_POST['DATE_INVOICE']) ? $_POST['DATE_INVOICE']: substr($arResult['INVOICE']['DATE_CREATE'],0,10);?>" name="DATE_INVOICE" aria-describedby="basic-addon-DATE_INVOICE">
								<span class="input-group-addon" id="basic-addon-DATE_INVOICE">
									<?
                                    $APPLICATION->IncludeComponent(
                                        "bitrix:main.calendar",
                                        ".default",
                                        array(
                                            "SHOW_INPUT" => "N",
                                            "FORM_NAME" => "curform",
                                            "INPUT_NAME" => "DATE_INVOICE",
                                            "INPUT_NAME_FINISH" => "",
                                            "INPUT_VALUE" => strlen($_POST['DATE_INVOICE']) ? $_POST['DATE_INVOICE']: substr($arResult['INVOICE']['DATE_CREATE'],0,10),
                                            "INPUT_VALUE_FINISH" => false,
                                            "SHOW_TIME" => "N",
                                            "HIDE_TIMEBAR" => "Y",
                                        ),
                                        false
                                    );
                                    ?>
                                </span>
                            </div>
                        </div> 
                    	<h4>Условия оплаты</h4>
						<div class="form-group <?=$arResult['ERR_FIELDS']['TYPE_PAYS'];?>">
                        	<?
							$type_pays = ($_POST['TYPE_PAYS']) ?  ($_POST['TYPE_PAYS']) : $arResult['INVOICE']['PROPERTY_TYPE_PAYS_ENUM_ID'];
							?>
                            <label class="control-label">Оплачивает</label>
                            <div class="radio">
                              <label>
                                <input name="TYPE_PAYS" value="251" type="radio" <?=($type_pays == 251) ? 'checked=""' : '';?>>
                                Отправитель
                              </label>
                            </div>
                            <div class="radio">
                              <label>
                                <input name="TYPE_PAYS" value="252" type="radio" <?=($type_pays == 252) ? 'checked=""' : '';?>>
                                Получатель
                              </label>
                            </div>
                                                        <div class="radio">
                              <label>
                                <input name="TYPE_PAYS" value="254" type="radio" <?=($type_pays == 254) ? 'checked=""' : '';?>>
                                Служебное
                              </label>
                            </div>
							<div class="input-group">
								<span class="input-group-addon">
									<input name="TYPE_PAYS" value="253" type="radio" <?=($type_pays == 253) ? 'checked=""' : '';?> aria-label="...">
								</span>
								<input type="text" class="form-control" name="PAYS" value="<?=strlen($_POST['PAYS']) ? $_POST['PAYS'] : $arResult['INVOICE']['PROPERTY_PAYS_VALUE'];?>" aria-label="..." placeholder="Другой">
							</div>
                        </div>
                    </div>
                </div>
                <div class="row">
                	<div class="col-md-6">
						<div class="form-group <?=$arResult['ERR_FIELDS']['WHO_DELIVERY'];?>">
                        	<?
							$who_delivery = ($_POST['WHO_DELIVERY']) ?  ($_POST['WHO_DELIVERY']) : $arResult['INVOICE']['PROPERTY_WHO_DELIVERY_ENUM_ID'];
							?>
                        	<label class="control-label">Доставить</label>
                            <div class="radio">
                              <label>
                                <input name="WHO_DELIVERY" value="248" type="radio" <?=($who_delivery == 248) ? 'checked=""' : '';?>>
                                По адресу
                              </label>
                            </div>
                            <div class="radio">
                              <label>
                                <input name="WHO_DELIVERY" value="249" type="radio" <?=($who_delivery == 249) ? 'checked=""' : '';?>>
                                До востребования
                              </label>
                            </div>
                            <div class="radio">
                              <label>
                                <input name="WHO_DELIVERY" value="250" type="radio" <?=($who_delivery == 250) ? 'checked=""' : '';?>>
                                Лично в руки
                              </label>
                            </div>
                        </div>
                        <div class="row">
                        	<div class="col-md-12"><label class="control-label">Доставить в дату, до часа</label></div>
                        </div>
						<div class="row">
							<div class="col-md-7">
                            	<div class="form-group <?=$arResult['ERR_FIELDS']['IN_DATE_DELIVERY'];?>">
									<div class="input-group">
										<input type="text" class="form-control" placeholder="ДД.ММ.ГГГГ" value="<?=strlen($_POST['IN_DATE_DELIVERY']) ? $_POST['IN_DATE_DELIVERY'] : substr($arResult['INVOICE']['PROPERTY_IN_DATE_DELIVERY_VALUE'],0,10);?>" name="IN_DATE_DELIVERY"  
                                        	aria-describedby="basic-addon-1">
   										<span class="input-group-addon" id="basic-addon-1">
											<?
                                            $APPLICATION->IncludeComponent(
                                                "bitrix:main.calendar",
                                                ".default",
                                                array(
                                                    "SHOW_INPUT" => "N",
                                                    "FORM_NAME" => "curform",
                                                    "INPUT_NAME" => "IN_DATE_DELIVERY",
                                                    "INPUT_NAME_FINISH" => "",
                                                    "INPUT_VALUE" =>strlen($_POST['IN_DATE_DELIVERY']) ? $_POST['IN_DATE_DELIVERY'] : substr($arResult['INVOICE']['PROPERTY_IN_DATE_DELIVERY_VALUE'],0,10),
                                                    "INPUT_VALUE_FINISH" => false,
                                                    "SHOW_TIME" => "N",
                                                    "HIDE_TIMEBAR" => "Y",
                                                ),
                                                false
                                            );
                                            ?>
										</span>
									</div>
                                </div>
                            </div>
                            <div class="col-md-5">
                            	<div class="form-group <?=$arResult['ERR_FIELDS']['IN_TIME_DELIVERY'];?>">
                                	<input type="text" class="form-control" name="IN_TIME_DELIVERY" value="<?=strlen($_POST['IN_TIME_DELIVERY']) ? $_POST['IN_TIME_DELIVERY'] : $arResult['INVOICE']['PROPERTY_IN_TIME_DELIVERY_VALUE'];?>" placeholder="ЧЧ:ММ">
                                </div>
                            </div>
						</div>
                        <div class="row">
                        	<div class="col-md-5">
								<div class="form-group <?=$arResult['ERR_FIELDS']['PLACES'];?>">
                                    <label class="control-label">Мест</label>
                                    <input type="text" class="form-control" name="PLACES" value="<?=strlen($_POST['PLACES']) ? $_POST['PLACES'] : $arResult['INVOICE']['PROPERTY_PLACES_VALUE'];?>">
                                </div>
                            </div>
                            <div class="col-md-7">
								<div class="form-group <?=$arResult['ERR_FIELDS']['WEIGHT'];?>">
                                    <label class="control-label">Вес</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="WEIGHT" value="<?=strlen($_POST['WEIGHT']) ? $_POST['WEIGHT'] : $arResult['INVOICE']['PROPERTY_WEIGHT_VALUE'];?>" aria-describedby="basic-addon-4">
                                        <span class="input-group-addon" id="basic-addon-4">кг</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
						<div class="form-group <?=$arResult['ERR_FIELDS']['PAYMENT'];?>">
							<?
								$payment = ($_POST['PAYMENT']) ?  ($_POST['PAYMENT']) : $arResult['INVOICE']['PROPERTY_PAYMENT_ENUM_ID'];
							?>
                        	<label class="control-label">Оплата</label>
                            <div class="radio">
                              <label>
                                <input name="PAYMENT" value="255" type="radio" <?=($payment == 255) ? 'checked=""' : '';?>>
                                Наличными
                              </label>
                            </div>
                            <div class="radio">
                              <label>
                                <input name="PAYMENT" value="256" type="radio" <?=($payment == 256) ? 'checked=""' : '';?>>
                                По счету
                              </label>
                            </div>
                            <p>&nbsp;</p>
                        </div>
                        
                        <div class="form-group <?=$arResult['ERR_FIELDS']['FOR_PAYMENT'];?>">
							<label class="control-label">К оплате</label>
                                    <div class="input-group">
                            		<input type="text" class="form-control" name="FOR_PAYMENT" value="<?=strlen($_POST['FOR_PAYMENT']) ? $_POST['FOR_PAYMENT'] : $arResult['INVOICE']['PROPERTY_FOR_PAYMENT_VALUE'];?>" aria-describedby="FOR_PAYMENT">
                                    <span class="input-group-addon" id="FOR_PAYMENT">руб.</span>
							</div>
                        </div>
                            
						<div class="form-group <?=$arResult['ERR_FIELDS']['COST'];?>">
							
                        			<label class="control-label">Объявленная стоимость</label>
                                    <div class="input-group">
                            		<input type="text" class="form-control" name="COST" value="<?=strlen($_POST['COST']) ? $_POST['COST'] : $arResult['INVOICE']['PROPERTY_COST_VALUE'];?>" aria-describedby="COST">
                                    <span class="input-group-addon" id="COST">руб.</span>
							</div>
                        </div>
                        
                    </div>
                </div>
                
             </div>
        </div>
		<div class="row">
            <div class="col-md-7">
                <div class="form-group <?=$arResult['ERR_FIELDS']['INSTRUCTIONS'];?>">
                    <label class="control-label">Специальные инструкции</label>
                    <textarea class="form-control input-sm" name="INSTRUCTIONS"><?=strlen($_POST['INSTRUCTIONS']) ? $_POST['INSTRUCTIONS'] : $arResult['INVOICE']['PROPERTY_INSTRUCTIONS_VALUE']['TEXT'];?></textarea>
                </div>
            </div>
			<div class="col-md-4 col-md-offset-1">
            	<div class="form-group <?=$arResult['ERR_FIELDS']['DIMENSIONS'];?>">
                    <label class="control-label">Габариты</label>
                    <div class="row">
                        <div class="col-md-4">
                        <div class="input-group">
                        <input type="text" class="form-control" name="DIMENSIONS[0]" value="<?=strlen($_POST['DIMENSIONS'][0]) ? $_POST['DIMENSIONS'][0] : $arResult['INVOICE']['PROPERTY_DIMENSIONS_VALUE'][0];?>" aria-describedby="size-1">
                        <span class="input-group-addon" id="size-1">см</span>
                        </div>
                        </div>
                        <div class="col-md-4">
                        <div class="input-group">
                        <input type="text" class="form-control" name="DIMENSIONS[1]" value="<?=strlen($_POST['DIMENSIONS'][1]) ? $_POST['DIMENSIONS'][1] : $arResult['INVOICE']['PROPERTY_DIMENSIONS_VALUE'][1];?>" aria-describedby="size-2">
                        <span class="input-group-addon" id="size-2">см</span>
                        </div>
                        </div>
                        <div class="col-md-4">
                        <div class="input-group">
                        <input type="text" class="form-control" name="DIMENSIONS[2]" value="<?=strlen($_POST['DIMENSIONS'][2]) ? $_POST['DIMENSIONS'][2] : $arResult['INVOICE']['PROPERTY_DIMENSIONS_VALUE'][2];?>" aria-describedby="size-3">
                        <span class="input-group-addon" id="size-3">см</span>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
        	<div class="col-md-12">
            	<div class="btn-group btn-group-lg" role="group" aria-label="...">
                    <button type="submit" name="save" class="btn btn-primary">Сохранить <span class="badge">CTRL+Enter</span></button>
                    <input type="submit" name="apply" value="Применить" class="btn btn-default">
                </div>
            </div>
        </div>
	</form>
	<?
}
?>
<br>