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
$client=false;
$agent=false;

$host = $_SERVER['SERVER_NAME'];

if($host == "client.newpartner.ru"){
    $client=true;
}
if($host == "agent.newpartner.ru"){
    $agent=true;
}

?>



<?if($USER->IsAuthorized()):?>

<p><?echo GetMessage("MAIN_REGISTER_AUTH")?></p>

<?else:?>
<script type="text/javascript">
    $(document).ready(function(){
        AutoCity();
        changeTypeCompany();
        changeOffice();
		getCompanies();
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
			$('#listcompanies').html('');
        }
        else
        {
            $('#label_company_name').html('<?=GetMessage("label_company_name");?><span class="starrequired">*</span>');
            $('.shownot').css('display','block');
			getCompanies();
        }
        return false;
    }
    
    function changeOffice()
    {
        var office = $('#office').val();
        $('.notshow').css('display','none');
        $('#dogovor_info_'+office).css('display','block');
    }


    function getCode(){
        var code = $('#code_agent').val();
        //console.log(code);
        var inn = $('#company_inn').val();
        if (code.length > 0){
            $.post("/search_city.php?get_code=Y", {code: code, inn: inn},
                function(data){
                    $.each(data, function( index, value ) {
                        console.log(value);
                      if(index === 'code'){
                          if(value !== 'N'){
                              $('#code_agent_1c').val(value);
                          }else{
                              $('#code_agent_1c').val("CODE_DEFAULT");
                          }
                      }
                    });
            }, "json");
        }
    }
	
	function getCompanies()
	{
		var inn = $('#company_inn').val();
		if (inn.length > 0)
		{
			$.post("/search_city.php?get_companies=Y", {inn: inn},
				function(data){
					if(data.length > 0)
					{
						var htmlresult = '<div class="form-group">'+
                            '<label for="listcompanies" class="control-label">Ваша компания:</label>'+
                            '<select class="form-control" size="1" id="listcompanies" name="listcompanies" onChange="ChooseCompany();">'+
							'<option value="0">Выберите компанию</option><optgroup>';
						$.each(data, function( index, value ) {
							htmlresult += '<option value="'+value['ID']+'" data-type="'+value['PROPERTY_TYPE_ENUM_ID']+'">'+value['NAME']+', '+value['PROPERTY_CITY_NAME']+', '+value['PROPERTY_ADRESS_VALUE']+'</option>';
							
						});
						htmlresult += '</optgroup><optgroup><option value="N">Моей компании нет в списке</option></optgroup>' +
                            '</select>'+
							'<span id="typecompanyinfo" class="help-block"></span>'+
                        '</div>'+
						'<div id="listbranches"></div>';
						$('#listcompanies').html(htmlresult);
					}
					else
					{
						$('#listcompanies').html('');
					}
				}
				, "json"
			);
		}
		else
		{
			$('#listcompanies').html('');
		}
	}
	
	function ChooseCompany()
	{
		var register_type = parseInt($('#register_type').val(), 10);
		var select_type = parseInt($('#listcompanies option:selected').data('type'),10);
		var select_id  = parseInt($('#listcompanies option:selected').val());
		var msg = '';
		if ((register_type > 0) && (select_type > 0) && (register_type != select_type))
		{
			if (select_type == 51) 
			{
				msg = 'Убедитесь в том, что введенный ИНН соответствует ИНН регистрируемой компании';
			}
			if (select_type == 53)
			{
				msg = 'Выбранная компания является агентом. Для регистрации перейдите, пожалуйста, в <a href="http://agent.newpartner.ru/registration.php">Личный Кабинет Агента</a>';
			}
			if (select_type == 242) 
			{
				msg = 'Выбранная компания является клиентом. Для регистрации перейдите, пожалуйста, в <a href="http://client.newpartner.ru/registration.php">Личный Кабинет Клиента</a>';
			}
			if ((select_type == 52) || (select_type == 222))
			{
				msg = 'Выбранная компания является интернет-магазином. Для регистрации перейдите, пожалуйста, в <a href="http://client.newpartner.ru/registration.php">DMS</a>';
			}
			msg = '<p><strong>'+msg+'</strong></p>';
		}
		$('#typecompanyinfo').html(msg);
		if (select_id > 0)
		{
			$.post("/search_city.php?get_branches=Y", {id: select_id},
				function(data){
					if(data.length > 0)
					{
						var htmlresult = '<div class="form-group">'+
                            '<label for="listbranches" class="control-label">Ваш филиал:</label>'+
                            '<select class="form-control" size="1" id="listbranches" name="listbranches">'+
							'<option value="0">Выберите филиал</option><optgroup>';
						$.each(data, function( index, value ) {
							htmlresult += '<option value="'+value['ID']+'">'+value['NAME']+', '+value['PROPERTY_CITY_NAME']+', '+value['PROPERTY_ADRESS_VALUE']+'</option>';
							
						});
						htmlresult += '</optgroup><optgroup><option value="N">Моего филиала нет в списке</option></optgroup>' +
                            '</select>'+
							'<span id="typecompanyinfo" class="help-block"></span>'+
                        '</div>';
						$('#listbranches').html(htmlresult);
					}
					else
					{
						$('#listbranches').html('');
					}
				}
				, "json"
			);
		}
		else
		{
			$('#listbranches').html('');
		}
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
				<input type="hidden" id="register_type" name="register_type" value="<?=$arParams['TYPE_COMPANY'];?>">
               <div class="row">
                   <div class="col-md-3 col-md-offset-1">
                       <h3><?=GetMessage("AUTH_REGISTER")?></h3>
                   </div>
                   <?if($client):?>
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
                   <?endif;?>
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
                      <?
                        if($_REQUEST['test']=="Y"){
                            //dump($arResult["LIST_OF_UKS"]);
                        }
                      ?>
                        <?if($client):?>
                        <div class="form-group">
                           <label class="control-label" for="office">
                                Офис обслуживания:<span class="starrequired">*</span>
                            </label>
                            <select class="form-control" size="1" name="OFFICE" id="office" onchange="changeOffice();">
                                <? foreach ($arResult["LIST_OF_UKS"] as $key=>$office) : ?>
                                <?   if($key == "2197189"){
                                        $namecity = GetMessage("uc_np");
                                    }elseif($key == "50161153"){
                                        $namecity = GetMessage("uc_sp");
                                    }elseif($key == "5873349"){
                                        $namecity = GetMessage("uc_yar");
                                    }elseif($key == "19713576"){
                                        $namecity = GetMessage("uc_iv");
                                    }elseif($key == "13794535"){
                                        $namecity = GetMessage("uc_kaz");
                                    }elseif($key == "39472059"){
                                        $namecity = GetMessage("uc_nn");
                                    }

                                    $s = ($_REQUEST["OFFICE"] == $office["ID"]) ? " selected" : ""; ?>
                                <option value="<?=$office["ID"];?>"<?=$s;?>><?=$namecity;?>, <?=$office["PROPERTY_BRAND_NAME_VALUE"];?>, <?=$office["PROPERTY_ADRESS_FACT_VALUE"];?></option>
                                <? endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="company_type" class="control-label">Тип:<span class="starrequired">*</span></label>
                            <select class="form-control" size="1" id="company_type" name="company_type" onchange="changeTypeCompany();">
                                <option value="310"<?=($_REQUEST["company_type"] == 310) ? " selected" : ""; ?>>Юридическое лицо / Индивидуальный предприниматель</option>
                                <option value="311"<?=($_REQUEST["company_type"] == 311) ? " selected" : ""; ?>>Физическое лицо</option>
                            </select>
                        </div>
                        <?endif;?>
                        <div class="form-group">
                            <label for="company_name" class="control-label" id="label_company_name"><?=GetMessage("label_company_name");?><span class="starrequired">*</span></label>
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
                            <?if($client):?>
                                <label for="company_inn" class="control-label">ИНН:<span class="starrequired">*</span></label>
                            <?elseif($agent):?>
                                <label for="company_inn" class="control-label">ID агента:<span class="starrequired">*</span></label>
                            <?endif;?>
                            <input type="text" class="form-control" name="company_inn" id="company_inn" value="" onChange="getCompanies();">
                        </div>
                        <?if($agent):?>
                        <div  class="form-group">
                            <input type="hidden" name="code_agent_1c" id="code_agent_1c">
                            <label for="code_agent" class="control-label">Код агента:<span class="starrequired">*</span></label>
                            <input type="text" class="form-control" name="code_agent" id="code_agent" value="" onChange="getCode();">
                        </div>
                        <?endif;?>
                        <div id="listcompanies"></div>
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
									<input name="form_checkbox_confirmation" value="Y"
                                           type="checkbox">
              кнопку «<?=GetMessage("AUTH_REGISTER")?>», я подтверждаю свою дееспособность, даю согласие на обработку своих персональных данных в соответствии с <a href="http://newpartner.ru/personal-data/" target="_blank">Условиями использования персональных данных<font color="red"><span class="form-required">*</span></font></a>
								</label>
							</div>
                        </div>
                   </div>
                <? endif; ?>
                <div class="row">
                    <div class="col-md-10 col-md-offset-1 text-center">
                        <div class="form-group">
                            <input type="submit" name="register_submit_button" value="<?=GetMessage("AUTH_REGISTER")?>"
                                   class="btn btn-primary">
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
