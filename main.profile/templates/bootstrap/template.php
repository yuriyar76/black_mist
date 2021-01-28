<?
/**
 * @global CMain $APPLICATION
 * @param array $arParams
 * @param array $arResult
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();
?>
<div class="bx-auth-profile">

<?ShowError($arResult["strProfileError"]);?>
<?
if ($arResult['DATA_SAVED'] == 'Y')
	ShowNote(GetMessage('PROFILE_DATA_SAVED'));
?>
<script type="text/javascript">
<!--
var opened_sections = [<?
$arResult["opened"] = $_COOKIE[$arResult["COOKIE_PREFIX"]."_user_profile_open"];
$arResult["opened"] = preg_replace("/[^a-z0-9_,]/i", "", $arResult["opened"]);
if (strlen($arResult["opened"]) > 0)
{
	echo "'".implode("', '", explode(",", $arResult["opened"]))."'";
}
else
{
	$arResult["opened"] = "reg";
	echo "'reg'";
}
?>];
//-->

var cookie_prefix = '<?=$arResult["COOKIE_PREFIX"]?>';
</script>
<form method="post" name="form1" action="<?=$arResult["FORM_TARGET"]?>" enctype="multipart/form-data" class="form-horizontal">
<fieldset>
<?=$arResult["BX_SESSION_CHECK"]?>
<input type="hidden" name="lang" value="<?=LANG?>" />
<input type="hidden" name="ID" value=<?=$arResult["ID"]?> />

<div class="panel panel-default">
	<div class="panel-heading">
    	<div class="profile-link profile-user-div-link">
        	<a title="<?=GetMessage("REG_SHOW_HIDE")?>" href="javascript:void(0)" onclick="SectionClick('reg')"><?=GetMessage("REG_SHOW_HIDE")?></a>
		</div>
    </div>
	<div class="panel-body">
    	<div class="profile-block-<?=strpos($arResult["opened"], "reg") === false ? "hidden" : "shown"?>" id="user_div_reg">
        	<?
			if($arResult["ID"]>0)
			{
			?>
				<?
				if (strlen($arResult["arUser"]["TIMESTAMP_X"])>0)
				{
				?>
				<div class="form-group">
					<div class="col-lg-3"><?=GetMessage('LAST_UPDATE')?></div>
					<div class="col-lg-9"><?=$arResult["arUser"]["TIMESTAMP_X"]?></div>
				</div>
				<?
				}
				?>
				<?
				if (strlen($arResult["arUser"]["LAST_LOGIN"])>0)
				{
				?>
				<div class="form-group">
					<div class="col-lg-3"><?=GetMessage('LAST_LOGIN')?></div>
					<div class="col-lg-9"><?=$arResult["arUser"]["LAST_LOGIN"]?></div>
				</div>
				<?
				}
				?>
			<?
			}
			?>
            <div class="form-group">
				<label for="NAME" class="col-lg-3 control-label"><?=GetMessage('NAME')?></label>
				<div class="col-lg-9">
					<input type="text" name="NAME" maxlength="50" value="<?=$arResult["arUser"]["NAME"]?>" class="form-control" id="NAME">
				</div>
			</div>
			<div class="form-group">
				<label for="LAST_NAME" class="col-lg-3 control-label"><?=GetMessage('LAST_NAME')?></label>
				<div class="col-lg-9">
					<input type="text" name="LAST_NAME" maxlength="50" value="<?=$arResult["arUser"]["LAST_NAME"]?>" class="form-control" id="LAST_NAME">
				</div>
			</div>
			<div class="form-group">
				<label for="SECOND_NAME" class="col-lg-3 control-label"><?=GetMessage('SECOND_NAME')?></label>
				<div class="col-lg-9">
					<input type="text" name="SECOND_NAME" maxlength="50" value="<?=$arResult["arUser"]["SECOND_NAME"]?>" class="form-control" id="SECOND_NAME">
				</div>
			</div>
			<div class="form-group">
				<label for="EMAIL" class="col-lg-3 control-label"><?=GetMessage('EMAIL')?><?if($arResult["EMAIL_REQUIRED"]):?><span class="starrequired">*</span><?endif?></label>
				<div class="col-lg-9">
					<input type="text" name="EMAIL" maxlength="50" value="<? echo $arResult["arUser"]["EMAIL"]?>" class="form-control" id="EMAIL">
				</div>
			</div>
			<div class="form-group">
				<label for="LOGIN" class="col-lg-3 control-label"><?=GetMessage('LOGIN')?><span class="starrequired">*</span></label>
				<div class="col-lg-9">
					<input type="text" name="LOGIN" maxlength="50" value="<? echo $arResult["arUser"]["LOGIN"]?>" class="form-control" id="LOGIN">
				</div>
			</div>
			<?if($arResult["arUser"]["EXTERNAL_AUTH_ID"] == ''):?>
            	<div class="form-group">
                	<label for="NEW_PASSWORD" class="col-lg-3 control-label"><?=GetMessage('NEW_PASSWORD_REQ')?></label>
                    <div class="col-lg-9">
                    	<input type="password" name="NEW_PASSWORD" maxlength="50" value="" autocomplete="off" class="bx-auth-input form-control" id="NEW_PASSWORD">
                        <?if($arResult["SECURE_AUTH"]):?>
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
                        <?endif?>
					</div>
                </div>
                <div class="form-group">
					<label for="NEW_PASSWORD_CONFIRM" class="col-lg-3 control-label"><?=GetMessage('NEW_PASSWORD_CONFIRM')?></label>
					<div class="col-lg-9">
						<input type="password" name="NEW_PASSWORD_CONFIRM" maxlength="50" value="" autocomplete="off" id="NEW_PASSWORD_CONFIRM" class="form-control">
					</div>
				</div>
			<?endif?>
            <?if($arResult["TIME_ZONE_ENABLED"] == true):?>
            	<p><?echo GetMessage("main_profile_time_zones")?></p>
            	<div class="form-group">
                	<label for="AUTO_TIME_ZONE" class="col-lg-3 control-label"><?=GetMessage("main_profile_time_zones_auto")?></label>
                    <div class="col-lg-9">
						<select name="AUTO_TIME_ZONE" onchange="this.form.TIME_ZONE.disabled=(this.value != 'N')" class="form-control" id="AUTO_TIME_ZONE">
							<option value=""><?echo GetMessage("main_profile_time_zones_auto_def")?></option>
							<option value="Y"<?=($arResult["arUser"]["AUTO_TIME_ZONE"] == "Y"? ' SELECTED="SELECTED"' : '')?>><?echo GetMessage("main_profile_time_zones_auto_yes")?></option>
							<option value="N"<?=($arResult["arUser"]["AUTO_TIME_ZONE"] == "N"? ' SELECTED="SELECTED"' : '')?>><?echo GetMessage("main_profile_time_zones_auto_no")?></option>
						</select>
                    </div>
                </div>
                <div class="form-group">
                	<label for="TIME_ZONE" class="col-lg-3 control-label"><?=GetMessage("main_profile_time_zones_zones")?></label>
                    <div class="col-lg-9">
						<select name="TIME_ZONE"<? if($arResult["arUser"]["AUTO_TIME_ZONE"] <> "N") echo ' disabled="disabled"'?> id="TIME_ZONE" class="form-control">
                            <?foreach($arResult["TIME_ZONE_LIST"] as $tz=>$tz_name):?>
								<option value="<?=htmlspecialcharsbx($tz)?>"<?=($arResult["arUser"]["TIME_ZONE"] == $tz? ' SELECTED="SELECTED"' : '')?>><?=htmlspecialcharsbx($tz_name)?></option>
                            <?endforeach?>
						</select>
                    </div>
                </div>
            <?endif?>
		</div>
	</div>
</div>

<div class="panel panel-default">
	<div class="panel-heading">
		<div class="profile-link profile-user-div-link">
        	<a title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" onclick="SectionClick('personal')"><?=GetMessage("USER_PERSONAL_INFO")?></a>
		</div>
    </div>
    <div class="panel-body">
    	<div id="user_div_personal" class="profile-block-<?=strpos($arResult["opened"], "personal") === false ? "hidden" : "shown"?>">
        	<div class="form-group">
            	<label for="PERSONAL_PROFESSION" class="col-lg-3 control-label"><?=GetMessage('USER_PROFESSION')?></label>
                <div class="col-lg-9">
                	<input type="text" name="PERSONAL_PROFESSION" maxlength="255" value="<?=$arResult["arUser"]["PERSONAL_PROFESSION"]?>" class="form-control" id="PERSONAL_PROFESSION">
                </div>
            </div>
            <div class="form-group">
            	<label for="PERSONAL_WWW" class="col-lg-3 control-label"><?=GetMessage('USER_WWW')?></label>
                <div class="col-lg-9">
                	<input type="text" name="PERSONAL_WWW" maxlength="255" value="<?=$arResult["arUser"]["PERSONAL_WWW"]?>" class="form-control" id="PERSONAL_WWW">
                </div>
            </div>
            <div class="form-group">
            	<label for="PERSONAL_ICQ" class="col-lg-3 control-label"><?=GetMessage('USER_ICQ')?></label>
                <div class="col-lg-9">
                	<input type="text" name="PERSONAL_ICQ" maxlength="255" value="<?=$arResult["arUser"]["PERSONAL_ICQ"]?>" class="form-control" id="PERSONAL_ICQ">
                </div>
            </div>
            <div class="form-group">
            	<label for="PERSONAL_GENDER" class="col-lg-3 control-label"><?=GetMessage('USER_GENDER')?></label>
                <div class="col-lg-9">
					<select name="PERSONAL_GENDER" class="form-control" id="PERSONAL_GENDER">
						<option value=""><?=GetMessage("USER_DONT_KNOW")?></option>
						<option value="M"<?=$arResult["arUser"]["PERSONAL_GENDER"] == "M" ? " SELECTED=\"SELECTED\"" : ""?>><?=GetMessage("USER_MALE")?></option>
						<option value="F"<?=$arResult["arUser"]["PERSONAL_GENDER"] == "F" ? " SELECTED=\"SELECTED\"" : ""?>><?=GetMessage("USER_FEMALE")?></option>
					</select>
                </div>
            </div>
            <div class="form-group">
            	<label for="PERSONAL_BIRTHDAY" class="col-lg-3 control-label"><?=GetMessage("USER_BIRTHDAY_DT")?> (<?=$arResult["DATE_FORMAT"]?>):</label>
                <div class="col-lg-9">
					<?
					$APPLICATION->IncludeComponent(
						'bitrix:main.calendar',
						'',
						array(
							'SHOW_INPUT' => 'Y',
							'FORM_NAME' => 'form1',
							'INPUT_NAME' => 'PERSONAL_BIRTHDAY',
							'INPUT_VALUE' => $arResult["arUser"]["PERSONAL_BIRTHDAY"],
							'SHOW_TIME' => 'N'
						),
						null,
						array('HIDE_ICONS' => 'Y')
					);
		
					//=CalendarDate("PERSONAL_BIRTHDAY", $arResult["arUser"]["PERSONAL_BIRTHDAY"], "form1", "15")
					?>
                </div>
            </div>
            <div class="form-group">
            	<label for="" class="col-lg-3 control-label"><?=GetMessage("USER_PHOTO")?></label>
                <div class="col-lg-9">
					<?=$arResult["arUser"]["PERSONAL_PHOTO_INPUT"]?>
					<?
                    if (strlen($arResult["arUser"]["PERSONAL_PHOTO"])>0)
                    {
                    ?>
                    <br />
                        <?=$arResult["arUser"]["PERSONAL_PHOTO_HTML"]?>
                    <?
                    }
                    ?>
                </div>
            </div>
			<div class="form-group">
            	<label for="PERSONAL_PHONE" class="col-lg-3 control-label"><?=GetMessage("USER_PHONE")?></label>
                <div class="col-lg-9">
                	<input type="text" name="PERSONAL_PHONE" maxlength="255" value="<?=$arResult["arUser"]["PERSONAL_PHONE"]?>" id="PERSONAL_PHONE" class="form-control">
                </div>
            </div>
            <div class="form-group">
            	<label for="PERSONAL_FAX" class="col-lg-3 control-label"><?=GetMessage('USER_FAX')?></label>
                <div class="col-lg-9">
                	<input type="text" name="PERSONAL_FAX" maxlength="255" value="<?=$arResult["arUser"]["PERSONAL_FAX"]?>" class="form-control" id="PERSONAL_FAX">
                </div>
            </div>
            <div class="form-group">
            	<label for="PERSONAL_MOBILE" class="col-lg-3 control-label"><?=GetMessage('USER_MOBILE')?></label>
                <div class="col-lg-9">
                	<input type="text" name="PERSONAL_MOBILE" maxlength="255" value="<?=$arResult["arUser"]["PERSONAL_MOBILE"]?>" id="PERSONAL_MOBILE" class="form-control">
                </div>
            </div>
            <div class="form-group">
            	<label for="PERSONAL_PAGER" class="col-lg-3 control-label"><?=GetMessage('USER_PAGER')?></label>
                <div class="col-lg-9">
                	<input type="text" name="PERSONAL_PAGER" maxlength="255" value="<?=$arResult["arUser"]["PERSONAL_PAGER"]?>" id="PERSONAL_PAGER" class="form-control">
                </div>
            </div>
            <p><em><?=GetMessage("USER_POST_ADDRESS")?></em></p>
			<div class="form-group">
            	<label for="" class="col-lg-3 control-label"><?=GetMessage('USER_COUNTRY')?></label>
				<div class="col-lg-9">
                	<?=$arResult["COUNTRY_SELECT"]?>
				</div>
            </div>
            <div class="form-group">
            	<label for="PERSONAL_STATE" class="col-lg-3 control-label"><?=GetMessage('USER_STATE')?></label>
                <div class="col-lg-9">
                	<input type="text" name="PERSONAL_STATE" maxlength="255" value="<?=$arResult["arUser"]["PERSONAL_STATE"]?>" class="form-control" id="PERSONAL_STATE">
                </div>
            </div>
			<div class="form-group">
            	<label for="PERSONAL_CITY" class="col-lg-3 control-label"><?=GetMessage('USER_CITY')?></label>
                <div class="col-lg-9">
                	<input type="text" name="PERSONAL_CITY" maxlength="255" value="<?=$arResult["arUser"]["PERSONAL_CITY"]?>" id="PERSONAL_CITY" class="form-control">
                </div>
			</div>
			<div class="form-group">
            	<label for="PERSONAL_ZIP" class="col-lg-3 control-label"><?=GetMessage('USER_ZIP')?></label>
                <div class="col-lg-9">
                	<input type="text" name="PERSONAL_ZIP" maxlength="255" value="<?=$arResult["arUser"]["PERSONAL_ZIP"]?>" id="PERSONAL_ZIP" class="form-control">
                </div>
			</div>
			<div class="form-group">
            	<label for="PERSONAL_STREET" class="col-lg-3 control-label"><?=GetMessage("USER_STREET")?></label>
                <div class="col-lg-9">
                	<textarea cols="30" rows="5" name="PERSONAL_STREET" id="PERSONAL_STREET" class="form-control"><?=$arResult["arUser"]["PERSONAL_STREET"]?></textarea>
                </div>
			</div>
			<div class="form-group">
            	<label for="PERSONAL_MAILBOX" class="col-lg-3 control-label"><?=GetMessage('USER_MAILBOX')?></label>
                <div class="col-lg-9">
                	<input type="text" name="PERSONAL_MAILBOX" maxlength="255" value="<?=$arResult["arUser"]["PERSONAL_MAILBOX"]?>" id="PERSONAL_MAILBOX" class="form-control">
                </div>
			</div>
			<div class="form-group">
            	<label for="PERSONAL_NOTES" class="col-lg-3 control-label"><?=GetMessage("USER_NOTES")?></label>
                <div class="col-lg-9">
                	<textarea cols="30" rows="5" name="PERSONAL_NOTES" id="PERSONAL_NOTES" class="form-control"><?=$arResult["arUser"]["PERSONAL_NOTES"]?></textarea>
                </div>
			</div>
        </div>
    </div>
</div>

<div class="panel panel-default">
	<div class="panel-heading">
    	<div class="profile-link profile-user-div-link">
        	<a title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" onclick="SectionClick('work')"><?=GetMessage("USER_WORK_INFO")?></a>
		</div>
	</div>
	<div class="panel-body">
		<div id="user_div_work" class="profile-block-<?=strpos($arResult["opened"], "work") === false ? "hidden" : "shown"?>">
        	<div class="form-group">
            	<label for="WORK_COMPANY" class="col-lg-3 control-label"><?=GetMessage('USER_COMPANY')?></label>
                <div class="col-lg-9">
                	<input type="text" name="WORK_COMPANY" maxlength="255" value="<?=$arResult["arUser"]["WORK_COMPANY"]?>" id="WORK_COMPANY" class="form-control">
                </div>
            </div>
            <div class="form-group">
            	<label for="WORK_WWW" class="col-lg-3 control-label"><?=GetMessage('USER_WWW')?></label>
                <div class="col-lg-9">
                	<input type="text" name="WORK_WWW" maxlength="255" value="<?=$arResult["arUser"]["WORK_WWW"]?>" id="WORK_WWW" class="form-control">
                </div>
            </div>
            <div class="form-group">
            	<label for="WORK_DEPARTMENT" class="col-lg-3 control-label"><?=GetMessage('USER_DEPARTMENT')?></label>
                <div class="col-lg-9">
                	<input type="text" name="WORK_DEPARTMENT" maxlength="255" value="<?=$arResult["arUser"]["WORK_DEPARTMENT"]?>" id="WORK_DEPARTMENT" class="form-control">
                </div>
            </div>
            <div class="form-group">
            	<label for="WORK_POSITION" class="col-lg-3 control-label"><?=GetMessage('USER_POSITION')?></label>
                <div class="col-lg-9">
                	<input type="text" name="WORK_POSITION" maxlength="255" value="<?=$arResult["arUser"]["WORK_POSITION"]?>" id="WORK_POSITION" class="form-control">
                </div>
            </div>
            <div class="form-group">
            	<label for="WORK_PROFILE" class="col-lg-3 control-label"><?=GetMessage("USER_WORK_PROFILE")?></label>
                <div class="col-lg-9">
                	<textarea cols="30" rows="5" name="WORK_PROFILE" id="WORK_PROFILE" class="form-control"><?=$arResult["arUser"]["WORK_PROFILE"]?></textarea>
                </div>
            </div>
            <div class="form-group">
            	<label for="" class="col-lg-3 control-label"><?=GetMessage("USER_LOGO")?></label>
                <div class="col-lg-9">
					<?=$arResult["arUser"]["WORK_LOGO_INPUT"]?>
					<?
                    if (strlen($arResult["arUser"]["WORK_LOGO"])>0)
                    {
                    ?>
                        <br /><?=$arResult["arUser"]["WORK_LOGO_HTML"]?>
                    <?
                    }
                    ?>
                </div>
            </div>
            <p><em><?=GetMessage("USER_PHONES")?></em></p>
            <div class="form-group">
            	<label for="WORK_PHONE" class="col-lg-3 control-label"><?=GetMessage('USER_PHONE')?></label>
                <div class="col-lg-9">
                	<input type="text" name="WORK_PHONE" maxlength="255" value="<?=$arResult["arUser"]["WORK_PHONE"]?>" id="WORK_PHONE" class="form-control">
                </div>
			</div>
            <div class="form-group">
            	<label for="WORK_FAX" class="col-lg-3 control-label"><?=GetMessage('USER_FAX')?></label>
                <div class="col-lg-9">
                	<input type="text" name="WORK_FAX" maxlength="255" value="<?=$arResult["arUser"]["WORK_FAX"]?>" id="WORK_FAX" class="form-control">
                </div>
			</div>
            <div class="form-group">
            	<label for="WORK_PAGER" class="col-lg-3 control-label"><?=GetMessage('USER_PAGER')?></label>
                <div class="col-lg-9">
                	<input type="text" name="WORK_PAGER" maxlength="255" value="<?=$arResult["arUser"]["WORK_PAGER"]?>" id="WORK_PAGER" class="form-control">
                </div>
			</div>
            <p><em><?=GetMessage("USER_POST_ADDRESS")?></em></p>
            <div class="form-group">
                <label for="" class="col-lg-3 control-label"><?=GetMessage('USER_COUNTRY')?></label>
                <div class="col-lg-9">
                	<?=$arResult["COUNTRY_SELECT_WORK"]?>
                </div>
            </div>
			<div class="form-group">
                <label for="WORK_STATE" class="col-lg-3 control-label"><?=GetMessage('USER_STATE')?></label>
                <div class="col-lg-9">
                	<input type="text" name="WORK_STATE" maxlength="255" value="<?=$arResult["arUser"]["WORK_STATE"]?>" id="WORK_STATE" class="form-control">
                </div>
            </div>
			<div class="form-group">
                <label for="WORK_CITY" class="col-lg-3 control-label"><?=GetMessage('USER_CITY')?></label>
                <div class="col-lg-9">
                	<input type="text" name="WORK_CITY" maxlength="255" value="<?=$arResult["arUser"]["WORK_CITY"]?>" id="WORK_CITY" class="form-control">
                </div>
            </div>
			<div class="form-group">
                <label for="WORK_ZIP" class="col-lg-3 control-label"><?=GetMessage('USER_ZIP')?></label>
                <div class="col-lg-9">
                	<input type="text" name="WORK_ZIP" maxlength="255" value="<?=$arResult["arUser"]["WORK_ZIP"]?>" id="WORK_ZIP" class="form-control">
                </div>
            </div>
			<div class="form-group">
                <label for="WORK_STREET" class="col-lg-3 control-label"><?=GetMessage("USER_STREET")?></label>
                <div class="col-lg-9">
                	<textarea cols="30" rows="5" name="WORK_STREET" id="WORK_STREET" class="form-control"><?=$arResult["arUser"]["WORK_STREET"]?></textarea>
                </div>
            </div>
			<div class="form-group">
                <label for="WORK_MAILBOX" class="col-lg-3 control-label"><?=GetMessage('USER_MAILBOX')?></label>
                <div class="col-lg-9">
                	<input type="text" name="WORK_MAILBOX" maxlength="255" value="<?=$arResult["arUser"]["WORK_MAILBOX"]?>" id="WORK_MAILBOX" class="form-control">
                </div>
            </div>
			<div class="form-group">
                <label for="WORK_NOTES" class="col-lg-3 control-label"><?=GetMessage("USER_NOTES")?></label>
                <div class="col-lg-9">
                	<textarea cols="30" rows="5" name="WORK_NOTES" id="WORK_NOTES" class="form-control"><?=$arResult["arUser"]["WORK_NOTES"]?></textarea>
                </div>
            </div>
		</div>
    </div>
</div>

<?
if ($arResult["INCLUDE_FORUM"] == "Y")
{
	?>
	<div class="panel panel-default">
        <div class="panel-heading">
        	<div class="profile-link profile-user-div-link">
            	<a title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" onclick="SectionClick('forum')"><?=GetMessage("forum_INFO")?></a>
			</div>
        </div>
        <div class="panel-body">
        	<div id="user_div_forum" class="profile-block-<?=strpos($arResult["opened"], "forum") === false ? "hidden" : "shown"?>">
                <div class="form-group">
                    <label for="forum_SHOW_NAME" class="col-lg-3 control-label"><?=GetMessage("forum_SHOW_NAME")?></label>
                    <div class="col-lg-9">
                    	<div class="checkbox">
                    		<input type="checkbox" name="forum_SHOW_NAME" value="Y" <? if ($arResult["arForumUser"]["SHOW_NAME"]=="Y") echo "checked=\"checked\"";?> id="forum_SHOW_NAME">
                        </div>
                    </div>
                </div>
				<div class="form-group">
                    <label for="forum_DESCRIPTION" class="col-lg-3 control-label"><?=GetMessage('forum_DESCRIPTION')?></label>
                    <div class="col-lg-9">
                    	<input type="text" name="forum_DESCRIPTION" maxlength="255" value="<?=$arResult["arForumUser"]["DESCRIPTION"]?>" id="forum_DESCRIPTION" class="form-control">
                    </div>
                </div>
				<div class="form-group">
                    <label for="forum_INTERESTS" class="col-lg-3 control-label"><?=GetMessage('forum_INTERESTS')?></label>
                    <div class="col-lg-9">
                    	<textarea cols="30" rows="5" name="forum_INTERESTS" id="forum_INTERESTS" class="form-control"><?=$arResult["arForumUser"]["INTERESTS"]; ?></textarea>
                    </div>
                </div>
				<div class="form-group">
                    <label for="forum_SIGNATURE" class="col-lg-3 control-label"><?=GetMessage("forum_SIGNATURE")?></label>
                    <div class="col-lg-9">
                    	<textarea cols="30" rows="5" name="forum_SIGNATURE" id="forum_SIGNATURE" class="form-control"><?=$arResult["arForumUser"]["SIGNATURE"]; ?></textarea>
                    </div>
                </div>
				<div class="form-group">
                    <label for="" class="col-lg-3 control-label"><?=GetMessage("forum_AVATAR")?></label>
                    <div class="col-lg-9">
						<?=$arResult["arForumUser"]["AVATAR_INPUT"]?>
                        <?
                        if (strlen($arResult["arForumUser"]["AVATAR"])>0)
                        {
                        ?>
                            <br /><?=$arResult["arForumUser"]["AVATAR_HTML"]?>
                        <?
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
	</div>
    <?
}
if ($arResult["INCLUDE_BLOG"] == "Y")
{
	?>
	<div class="panel panel-default">
        <div class="panel-heading">
			<div class="profile-link profile-user-div-link">
            	<a title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" onclick="SectionClick('blog')"><?=GetMessage("blog_INFO")?></a>
			</div>
        </div>
        <div class="panel-body">
        	<div id="user_div_blog" class="profile-block-<?=strpos($arResult["opened"], "blog") === false ? "hidden" : "shown"?>">
            	<div class="form-group">
                    <label for="blog_ALIAS" class="col-lg-3 control-label"><?=GetMessage('blog_ALIAS')?></label>
                    <div class="col-lg-9">
                    	<input class="typeinput" type="text" name="blog_ALIAS" maxlength="255" value="<?=$arResult["arBlogUser"]["ALIAS"]?>" id="blog_ALIAS" class="form-control">
                    </div>
                </div>
				<div class="form-group">
                    <label for="blog_DESCRIPTION" class="col-lg-3 control-label"><?=GetMessage('blog_DESCRIPTION')?></label>
                    <div class="col-lg-9">
                    	<input class="typeinput" type="text" name="blog_DESCRIPTION" maxlength="255" value="<?=$arResult["arBlogUser"]["DESCRIPTION"]?>" id="blog_DESCRIPTION" class="form-control">
                    </div>
                </div>
				<div class="form-group">
                    <label for="blog_INTERESTS" class="col-lg-3 control-label"><?=GetMessage('blog_INTERESTS')?></label>
                    <div class="col-lg-9">
                    	<textarea cols="30" rows="5" class="typearea" name="blog_INTERESTS" id="blog_INTERESTS" class="form-control"><?echo $arResult["arBlogUser"]["INTERESTS"]; ?></textarea>
                    </div>
                </div>
				<div class="form-group">
                    <label for="" class="col-lg-3 control-label"><?=GetMessage("blog_AVATAR")?></label>
                    <div class="col-lg-9">
                    	<?=$arResult["arBlogUser"]["AVATAR_INPUT"]?>
						<?
                        if (strlen($arResult["arBlogUser"]["AVATAR"])>0)
                        {
                        ?>
                            <br /><?=$arResult["arBlogUser"]["AVATAR_HTML"]?>
                        <?
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
	</div> 
    <?
}
if ($arResult["INCLUDE_LEARNING"] == "Y")
{
	?>
	<div class="panel panel-default">
        <div class="panel-heading">
        	<div class="profile-link profile-user-div-link">
            	<a title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" onclick="SectionClick('learning')"><?=GetMessage("learning_INFO")?></a>
			</div>
        </div>
        <div class="panel-body">
        	<div id="user_div_learning" class="profile-block-<?=strpos($arResult["opened"], "learning") === false ? "hidden" : "shown"?>">
				<div class="form-group">
                    <label for="student_PUBLIC_PROFILE" class="col-lg-3 control-label"><?=GetMessage("learning_PUBLIC_PROFILE");?></label>
                    <div class="col-lg-9">
                    	<div class="checkbox">
                    		<input type="checkbox" name="student_PUBLIC_PROFILE" value="Y" <? if ($arResult["arStudent"]["PUBLIC_PROFILE"]=="Y") echo "checked=\"checked\"";?> id="student_PUBLIC_PROFILE">
                        </div>
                    </div>
                </div>
				<div class="form-group">
                    <label for="student_RESUME" class="col-lg-3 control-label"><?=GetMessage("learning_RESUME");?></label>
                    <div class="col-lg-9">
                    	<textarea cols="30" rows="5" name="student_RESUME" id="student_RESUME" class="form-control"><?=$arResult["arStudent"]["RESUME"]; ?></textarea>
                    </div>
                </div>
				<div class="form-group">
                    <label for="" class="col-lg-3 control-label"><?=GetMessage("learning_TRANSCRIPT");?></label>
                    <div class="col-lg-9">
                    	<?=$arResult["arStudent"]["TRANSCRIPT"];?>-<?=$arResult["ID"]?>
                    </div>
                </div>
            </div>
        </div>
	</div>
    <?
}
if($arResult["IS_ADMIN"])
{
	?>
	<div class="panel panel-default">
        <div class="panel-heading">
        	<div class="profile-link profile-user-div-link">
            	<a title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" onclick="SectionClick('admin')"><?=GetMessage("USER_ADMIN_NOTES")?></a>
			</div>
        </div>
        <div class="panel-body">
        	<div id="user_div_admin" class="profile-block-<?=strpos($arResult["opened"], "admin") === false ? "hidden" : "shown"?>">
            	<div class="form-group">
                    <label for="ADMIN_NOTES" class="col-lg-3 control-label"><?=GetMessage("USER_ADMIN_NOTES")?></label>
                    <div class="col-lg-9">
                    	<textarea cols="30" rows="5" name="ADMIN_NOTES" id="ADMIN_NOTES" class="form-control"><?=$arResult["arUser"]["ADMIN_NOTES"]?></textarea>
                    </div>
                </div>
            </div>
        </div>
	</div>
    <?
}
// ********************* User properties ***************************************************
if($arResult["USER_PROPERTIES"]["SHOW"] == "Y")
{
	?>
	<div class="panel panel-default">
        <div class="panel-heading">
			<div class="profile-link profile-user-div-link">
            	<a title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" onclick="SectionClick('user_properties')"><?=strlen(trim($arParams["USER_PROPERTY_NAME"])) > 0 ? $arParams["USER_PROPERTY_NAME"] : GetMessage("USER_TYPE_EDIT_TAB")?></a>
			</div>
        </div>
        <div class="panel-body">
        	<div id="user_div_user_properties" class="profile-block-<?=strpos($arResult["opened"], "user_properties") === false ? "hidden" : "shown"?>">
            	<?
				$first = true;
				foreach ($arResult["USER_PROPERTIES"]["DATA"] as $FIELD_NAME => $arUserField): ?>
                    <div class="form-group">
                        <label for="" class="col-lg-3 control-label"><?=$arUserField["EDIT_FORM_LABEL"]?> 
                            <?if ($arUserField["MANDATORY"]=="Y"):?>
                                <span class="starrequired">*</span>
                            <?endif;?>
                        </label>
                        <div class="col-lg-9">
							<?
                            $APPLICATION->IncludeComponent(
                                "bitrix:system.field.edit",
                                $arUserField["USER_TYPE"]["USER_TYPE_ID"],
                                array("bVarsFromForm" => $arResult["bVarsFromForm"], "arUserField" => $arUserField), null, array("HIDE_ICONS"=>"Y")
                            );
							?>
                        </div>
                    </div>
				<?
                endforeach;
				?>
            </div>
        </div>
	</div>
    <?
}
// ******************** /User properties ***************************************************?>
<p><?=$arResult["GROUP_POLICY"]["PASSWORD_REQUIREMENTS"];?></p>
<p>
	<input type="submit" name="save" value="<?=(($arResult["ID"]>0) ? GetMessage("MAIN_SAVE") : GetMessage("MAIN_ADD"))?>" class="btn btn-primary">&nbsp;&nbsp;
    <input type="reset" value="<?=GetMessage('MAIN_RESET');?>" class="btn btn-default">
</p>
</fieldset>
</form>
<?
if($arResult["SOCSERV_ENABLED"])
{
	$APPLICATION->IncludeComponent("bitrix:socserv.auth.split", ".default", array(
			"SHOW_PROFILES" => "Y",
			"ALLOW_DELETE" => "Y"
		),
		false
	);
}
?>
</div>