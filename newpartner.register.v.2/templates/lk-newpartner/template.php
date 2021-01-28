<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */

/**
 * Bitrix vars
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @param array $arParams
 * @param array $arResult
 * @param CBitrixComponentTemplate $this
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();
?>



<?if($USER->IsAuthorized()):?>

<p><?echo GetMessage("MAIN_REGISTER_AUTH")?></p>

<?else:?>
<script type="text/javascript">
    $(document).ready(function(){
        AutoCity();
        changeTypeCompany();
        changeOffice();
        //$('.maskphone').mask('+99999999999?9');
    });
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
    
    function changeTypeCompany()
    {
        var type = parseInt($('#company_type').val());
        if (type == 311)
        {
            $('#label_company_name').html('ФИО:<span class="starrequired">*</span>');
            $('.shownot').css('display','none');
            $('#company_name').val($('#register_LAST_NAME').val() + ' ' + $('#register_NAME').val());
        }
        else
        {
            $('#label_company_name').html('Наименование компании:<span class="starrequired">*</span>');
            $('.shownot').css('display','block');
        }
        return false;
    }
    
    function changeOffice()
    {
        var office = $('#office').val();
        $('.notshow').css('display','none');
        $('#dogovor_info_'+office).css('display','block');
    }
</script>
<?
if (count($arResult["ERRORS"]) > 0):
	foreach ($arResult["ERRORS"] as $key => $error)
		if (intval($key) == 0 && $key !== 0) 
			$arResult["ERRORS"][$key] = str_replace("#FIELD_NAME#", "&quot;".GetMessage("REGISTER_FIELD_".$key)."&quot;", $error);
			
			echo '<div class="alert alert-dismissable alert-danger">'.implode("<br />", $arResult["ERRORS"]).'</div>';

	// ShowError(implode("<br />", $arResult["ERRORS"]));

elseif($arResult["USE_EMAIL_CONFIRMATION"] === "Y"):
?>

<?endif?>



<div class="row">
	<div class="col-md-8 col-md-offset-2">
    	<div class="well bs-component">
           <form method="post" action="<?=POST_FORM_ACTION_URI?>" name="regform" enctype="multipart/form-data" class="form-horizontal">
               <div class="row">
                   <div class="col-md-3 col-md-offset-1">
                       <h3><?=GetMessage("AUTH_REGISTER")?></h3>
                   </div>
                   <div class="col-md-7 col-md-offset-1 text-right">
                   <br>
                    <?
                    foreach ($arResult["LIST_OF_UKS"] as $office)
                    {
                        if (strlen($office["PROPERTY_PAGE_DOGOVOR_VALUE"])) :
                        ?>
                        <div role="alert" class="notshow alert alert-info" style="display:none;" id="dogovor_info_<?=$office["ID"];?>"><span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span> <strong>Перед началом работы в Личном Кабинете у вас должен быть заключен <a href="<?=$office["PROPERTY_PAGE_DOGOVOR_VALUE"];?>" target="_blank">договор</a>.</strong></div>
                        <? else : ?>
                        <div role="alert" class="notshow alert alert-info" style="display:none;" id="dogovor_info_<?=$office["ID"];?>"><span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span> <strong>Перед началом работы в Личном Кабинете у вас должен быть заключен договор.</strong></div>
                        <?
                        endif;
                    }
                    ?>
                   </div>
               </div>
                <div class="row">
                    <div class="col-md-4 col-md-offset-1">
                        
                    
                    <?
                    if($arResult["BACKURL"] <> ''):
                    ?>
                        <input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
                    <?
                    endif;
                    ?>
                    <? foreach ($arResult["SHOW_FIELDS"] as $FIELD): ?>
                    	<? if($FIELD == "AUTO_TIME_ZONE" && $arResult["TIME_ZONE_ENABLED"] == true):?>
                        <? else: ?>
							<div class="form-group">
                                <label class="control-label" for="register_<?=$FIELD?>">
                                    <?=GetMessage("REGISTER_FIELD_".$FIELD)?>:<?if ($arResult["REQUIRED_FIELDS_FLAGS"][$FIELD] == "Y"):?><span class="starrequired">*</span><?endif?>
                                </label>
								<?
	switch ($FIELD)
	{
		case "PASSWORD":
			?><input size="30" type="password" name="REGISTER[<?=$FIELD?>]" value="<?=$arResult["VALUES"][$FIELD]?>" autocomplete="off" class="bx-auth-input form-control">
            <p class="help-block" style="margin-bottom:0;"><small><?echo $arResult["GROUP_POLICY"]["PASSWORD_REQUIREMENTS"];?></small></p>
<?
			break;
		case "CONFIRM_PASSWORD":
			?><input size="30" type="password" name="REGISTER[<?=$FIELD?>]" value="<?=$arResult["VALUES"][$FIELD]?>" autocomplete="off" class="form-control"><?
			break;

		case "PERSONAL_GENDER":
			?><select name="REGISTER[<?=$FIELD?>]" class="form-control">
				<option value=""><?=GetMessage("USER_DONT_KNOW")?></option>
				<option value="M"<?=$arResult["VALUES"][$FIELD] == "M" ? " selected=\"selected\"" : ""?>><?=GetMessage("USER_MALE")?></option>
				<option value="F"<?=$arResult["VALUES"][$FIELD] == "F" ? " selected=\"selected\"" : ""?>><?=GetMessage("USER_FEMALE")?></option>
			</select><?
			break;

		case "PERSONAL_COUNTRY":
		case "WORK_COUNTRY":
			?><select name="REGISTER[<?=$FIELD?>]" class="form-control"><?
			foreach ($arResult["COUNTRIES"]["reference_id"] as $key => $value)
			{
				?><option value="<?=$value?>"<?if ($value == $arResult["VALUES"][$FIELD]):?> selected="selected"<?endif?>><?=$arResult["COUNTRIES"]["reference"][$key]?></option>
			<?
			}
			?></select><?
			break;

		case "PERSONAL_PHOTO":
		case "WORK_LOGO":
			?><input size="30" type="file" name="REGISTER_FILES_<?=$FIELD?>" class="form-control"><?
			break;

		case "PERSONAL_NOTES":
		case "WORK_NOTES":
			?><textarea cols="30" rows="5" name="REGISTER[<?=$FIELD?>]" class="form-control"><?=$arResult["VALUES"][$FIELD]?></textarea><?
			break;
		default:
			if ($FIELD == "PERSONAL_BIRTHDAY"):?><small><?=$arResult["DATE_FORMAT"]?></small><br /><?endif;?>
            <input type="text" name="REGISTER[<?=$FIELD?>]" value="<?=$arResult["VALUES"][$FIELD]?>" class="form-control" id="register_<?=$FIELD?>"><?
				if ($FIELD == "PERSONAL_BIRTHDAY")
					$APPLICATION->IncludeComponent(
						'bitrix:main.calendar',
						'',
						array(
							'SHOW_INPUT' => 'N',
							'FORM_NAME' => 'regform',
							'INPUT_NAME' => 'REGISTER[PERSONAL_BIRTHDAY]',
							'SHOW_TIME' => 'N'
						),
						null,
						array("HIDE_ICONS"=>"Y")
					);
				?><?
	}
	
	?>
                        </div>
                        <? endif;?>
                        <?endforeach?>

                    </div>
                    <div class="col-md-4 col-md-offset-2">
                        <div class="form-group">
                           <label class="control-label" for="office">
                                Офис обслуживания:<span class="starrequired">*</span>
                            </label>
                            <select class="form-control" size="1" name="OFFICE" id="office" onchange="changeOffice();">
                                <? foreach ($arResult["LIST_OF_UKS"] as $office) : ?>
                                <? $s = ($_REQUEST["OFFICE"] == $office["ID"]) ? " selected" : ""; ?>
                                <option value="<?=$office["ID"];?>"<?=$s;?>><?=$office["PROPERTY_BRAND_NAME_VALUE"];?>, <?=$office["PROPERTY_ADRESS_FACT_VALUE"];?></option>
                                <? endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="company_type" class="control-label">Тип:<span class="starrequired">*</span></label>
                            <select class="form-control" size="1" id="company_type" name="company_type" onchange="changeTypeCompany();">
                                <option value="310"<?=($_REQUEST["company_type"] == 310) ? " selected" : ""; ?>>Юридическое лицо</option>
                                <option value="311"<?=($_REQUEST["company_type"] == 311) ? " selected" : ""; ?>>Физическое лицо</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="company_name" class="control-label" id="label_company_name">Наименование компании:<span class="starrequired">*</span></label>
                            <input type="text" class="form-control" name="company_name" id="company_name" value="<?=NewQuotes($_REQUEST["company_name"]);?>">
                        </div>
                        <div class="form-group">
                            <label for="company_phone" class="control-label">Номер телефона:<span class="starrequired">*</span></label>
                            <input type="text" class="form-control maskphone" name="company_phone" id="company_phone" value="<?=$_REQUEST["company_phone"];?>">
                        </div>
                        <div class="form-group">
                            <label for="company_city" class="control-label">Город:<span class="starrequired">*</span></label>
                            <input type="text" class="form-control autocity" name="company_city" id="company_city" value="<?=$_REQUEST["company_city"];?>">
                        </div>
                        <div class="form-group">
                            <label for="company_adress" class="control-label">Адрес:<span class="starrequired">*</span></label>
                            <input type="text" class="form-control" name="company_adress" id="company_adress" value="<?=$_REQUEST["company_adress"];?>">
                        </div>
                        <div class="form-group shownot">
                            <label for="company_inn" class="control-label">ИНН:<span class="starrequired">*</span></label>
                            <input type="text" class="form-control" name="company_inn" id="company_inn" value="<?=$_REQUEST["company_inn"];?>">
                        </div>
                    </div>
                </div>
                <? if ($arParams["USE_CAPTCHA"] == "Y") : ?>
                    <div class="row">
                        <div class="col-md-4 col-md-offset-1 text-center">
                            <div class="form-group">
                                <div class="g-recaptcha" data-sitekey="6LeaLBkUAAAAAA3fL7xsBQ2nJqQXqBH60uFcG1BF"></div>
                            </div>
                        </div>
                        <div class="col-md-4 col-md-offset-2">
                        	<div class="checkbox">
								<label>
									<input name="form_checkbox_confirmation" value="Y" type="checkbox" <?=($_REQUEST["form_checkbox_confirmation"] == 'Y') ? 'checked' : '';?>> Нажимая кнопку «<?=GetMessage("AUTH_REGISTER")?>», я подтверждаю свою дееспособность, даю согласие на обработку своих персональных данных в соответствии с <a href="http://newpartner.ru/personal-data/" target="_blank">Условиями использования персональных данных<font color="red"><span class="form-required">*</span></font></a>
								</label>
							</div>
                        </div>
                   </div>
                <? endif; ?>
                <div class="row">
                    <div class="col-md-10 col-md-offset-1 text-center">
                        <div class="form-group">
                            <input type="submit" name="register_submit_button" value="<?=GetMessage("AUTH_REGISTER")?>" class="btn btn-primary">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-10 col-md-offset-1">
                        <p class="help-block"><small><span class="starrequired">*</span><?=GetMessage("AUTH_REQ")?></small></p>
                    </div>
               </div>
            </form>
        </div>
    </div>
</div>



<?endif?>
