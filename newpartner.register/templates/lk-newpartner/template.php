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
	<div class="col-md-4 col-md-offset-4">
    	<div class="well bs-component">
            <div class="row">
            	<div class="col-md-10 col-md-offset-1">
                	<h3><?=GetMessage("AUTH_REGISTER")?></h3>
                    <form method="post" action="<?=POST_FORM_ACTION_URI?>" name="regform" enctype="multipart/form-data" class="form-horizontal">
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
                                <label class="control-label">
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
            <input size="30" type="text" name="REGISTER[<?=$FIELD?>]" value="<?=$arResult["VALUES"][$FIELD]?>" class="form-control"><?
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
                    <? if ($arParams["USE_CAPTCHA"] == "Y") : ?>
                        <div class="form-group">
                            <div class="g-recaptcha" data-sitekey="6LeaLBkUAAAAAA3fL7xsBQ2nJqQXqBH60uFcG1BF"></div>
                        </div>
                    <? endif; ?>
                    <div class="form-group">
                    	<input type="submit" name="register_submit_button" value="<?=GetMessage("AUTH_REGISTER")?>" class="btn btn-primary">
                    </div>
                    </form>
                    <p class="help-block"><small><span class="starrequired">*</span><?=GetMessage("AUTH_REQ")?></small></p>
                </div>
            </div>
        </div>
    </div>
</div>



<?endif?>
