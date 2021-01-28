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
 * @param array $arParams
 * @param array $arResult
 * @param CBitrixComponentTemplate $this
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<div class="bx-auth">
	<?
    ShowMessage($arParams["~AUTH_RESULT"]);
    ?>
    <?if($arResult["USE_EMAIL_CONFIRMATION"] === "Y" && is_array($arParams["AUTH_RESULT"]) &&  $arParams["AUTH_RESULT"]["TYPE"] === "OK"):?>
    <p><?echo GetMessage("AUTH_EMAIL_SENT")?></p>
    <?else:?>
    <?if($arResult["USE_EMAIL_CONFIRMATION"] === "Y"):?>
        <p><?echo GetMessage("AUTH_EMAIL_WILL_BE_SENT")?></p>
    <?endif?>
    <noindex>
        <form method="post" action="<?=$arResult["AUTH_URL"]?>" name="bform" class="form-horizontal">
        	<fieldset>
			<?
            if (strlen($arResult["BACKURL"]) > 0)
            {
            ?>
                <input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
            <?
            }
            ?>
            <input type="hidden" name="AUTH_FORM" value="Y" />
            <input type="hidden" name="TYPE" value="REGISTRATION" />
            <div class="form-group">
                <label for="inputName" class="col-lg-3 control-label"><?=GetMessage("AUTH_NAME")?></label>
                <div class="col-lg-9">
                    <input type="text" name="USER_NAME" value="<?=$arResult["USER_NAME"]?>" class="form-control" id="inputName" placeholder="<?=GetMessage("AUTH_NAME_PLACEHOLDER")?>">
                </div>
            </div>
            <div class="form-group">
                <label for="USER_LAST_NAME" class="col-lg-3 control-label"><?=GetMessage("AUTH_LAST_NAME")?></label>
                <div class="col-lg-9">
                    <input type="text" name="USER_LAST_NAME" value="<?=$arResult["USER_LAST_NAME"]?>" class="form-control" placeholder="<?=GetMessage("AUTH_LAST_NAME_PLACEHOLDER")?>" id="USER_LAST_NAME">
                </div>
            </div>
            <div class="form-group">
                <label for="USER_LOGIN" class="col-lg-3 control-label"><?=GetMessage("AUTH_LOGIN_MIN")?> <span class="starrequired">*</span></label>
                <div class="col-lg-9">
                    <input type="text" name="USER_LOGIN" value="<?=$arResult["USER_LOGIN"]?>" class="form-control" placeholder="<?=GetMessage("AUTH_LOGIN_MIN_PLACEHOLDER")?>" id="USER_LOGIN">
                </div>
            </div>
            <div class="form-group">
                <label for="USER_PASSWORD" class="col-lg-3 control-label"><?=GetMessage("AUTH_PASSWORD_REQ")?> <span class="starrequired">*</span></label>
                <div class="col-lg-9">
                    <input type="password" name="USER_PASSWORD" value="<?=$arResult["USER_PASSWORD"]?>" class="form-control" id="USER_PASSWORD" placeholder="<?=GetMessage("AUTH_PASSWORD_REQ_PLACEHOLDER")?>">
                    <?
                    if ($arResult["SECURE_AUTH"]):
                        ?>
                        <span class="bx-auth-secure" id="bx_auth_secure" title="<?echo GetMessage("AUTH_SECURE_NOTE")?>" style="display:none">
                            <div class="bx-auth-secure-icon"></div>
                        </span>
                        <noscript>
                        <span class="bx-auth-secure" title="<?echo GetMessage("AUTH_NONSECURE_NOTE")?>">
                            <div class="bx-auth-secure-icon bx-auth-secure-unlock"></div>
                        </span>
                        </noscript>
                        <script type="text/javascript">
                            document.getElementById('bx_auth_secure').style.display = 'inline-block';
                        </script>
                    <? endif; ?>
                </div>
            </div>
            <div class="form-group">
                <label for="USER_CONFIRM_PASSWORD" class="col-lg-3 control-label"><?=GetMessage("AUTH_CONFIRM")?> <span class="starrequired">*</span></label>
                <div class="col-lg-9">
                    <input type="password" name="USER_CONFIRM_PASSWORD" value="<?=$arResult["USER_CONFIRM_PASSWORD"]?>" class="form-control" id="USER_CONFIRM_PASSWORD" placeholder="<?=GetMessage("AUTH_CONFIRM_PLACEHOLDER")?>">
                </div>
            </div>
            <div class="form-group">
                <label for="USER_EMAIL" class="col-lg-3 control-label"><?=GetMessage("AUTH_EMAIL")?> <span class="starrequired">*</span></label>
                <div class="col-lg-9">
                    <input type="text" name="USER_EMAIL" value="<?=$arResult["USER_EMAIL"]?>" class="form-control" id="USER_EMAIL" placeholder="<?=GetMessage("AUTH_EMAIL_PLACEHOLDER")?>">
                </div>
            </div>
            <? if($arResult["USER_PROPERTIES"]["SHOW"] == "Y"): ?>
                <? foreach ($arResult["USER_PROPERTIES"]["DATA"] as $FIELD_NAME => $arUserField): ?>
                    <div class="form-group">
                        <label for="" class="col-lg-3 control-label"><?=$arUserField["EDIT_FORM_LABEL"]?>: <?if ($arUserField["MANDATORY"]=="Y"):?><span class="starrequired">*</span><?endif;?></label>
                        <div class="col-lg-9">
                            <?
                            $APPLICATION->IncludeComponent(
                                "bitrix:system.field.edit",
                                $arUserField["USER_TYPE"]["USER_TYPE_ID"],
                                array("bVarsFromForm" => $arResult["bVarsFromForm"], "arUserField" => $arUserField, "form_name" => "bform"), null, array("HIDE_ICONS"=>"Y")
                            );
                            ?>
                        </div> 
                    </div>
                <? endforeach; ?>
            <?endif;?>
              <? /* ?>
               <div class="form-group">
                   <div class="col-lg-6 col-md-offset-3">
                        <div class="g-recaptcha" data-sitekey="6LcIhhUUAAAAABt4Bcpfc2vJ8OMgkjppXXMD5MqJ"></div>
                   </div>
               </div>
               <? */ ?>
            <? if ($arResult["USE_CAPTCHA"] == "Y"): ?>

                <div class="form-group">
                    <label for="" class="col-lg-3 control-label"><?=GetMessage("CAPTCHA_REGF_TITLE")?> <span class="starrequired">*</span></label>
                    <div class="col-lg-3">
                        <input type="hidden" name="captcha_sid" value="<?=$arResult["CAPTCHA_CODE"]?>">
                        <img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" />
                    </div>
                    <div class="col-lg-6">
                        <input type="text" name="captcha_word" value="" class="form-control">
                    </div>
                </div>
            <? endif; ?>
            <div class="form-group">
                <div class="col-lg-9 col-lg-offset-3">
                    <button type="submit" class="btn btn-primary" name="Register"><?=GetMessage("AUTH_REGISTER")?></button>
                </div>
            </div>
            <p><?echo $arResult["GROUP_POLICY"]["PASSWORD_REQUIREMENTS"];?></p>
            <p><span class="starrequired">*</span><?=GetMessage("AUTH_REQ")?></p>
            </fieldset>
           
        </form>
    </noindex>
    <script type="text/javascript">
    document.bform.USER_NAME.focus();
    </script>
    <?endif?>
</div>