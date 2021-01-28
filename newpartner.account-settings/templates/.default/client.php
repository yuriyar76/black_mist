<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true){die();}?>
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
?>
 <div class="row">
    <div class="col-md-6 col-md-offset-3">
        <div class="well bs-component">
            <form action="" method="post" name="curform">
               	<input type="hidden" name="rand" value="<?=rand(100000,999999);?>">
		        <input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>">
                <div class="row">
                    <div class="col-md-10 col-md-offset-1">
                        <h4>�������� �� ��������� (���������� ����� ���������)</h4> </div>
                </div>
                <div class="row">
                    <div class="col-md-5 col-md-offset-1">
                        <div class="form-group">
                            <label>������� �������</label>
                            <div class="checkbox">
                                <label>
                                    <input value="yes" name="CALLCOURIER" type="checkbox"<?=($arResult['USER_SETTINGS']['CALLCOURIER'] == 'yes') ? ' checked' : '';?>> ��</label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="DATE_CALLCOURIER" value="1" type="radio"<?=($arResult['USER_SETTINGS']['DATE_CALLCOURIER'] == 1) ? ' checked' : '';?>> �� ������� ����</label>
                                </label>
                            </div>
                            <div class="radio">                                <label>
                                    <input name="DATE_CALLCOURIER" value="2" type="radio"<?=($arResult['USER_SETTINGS']['DATE_CALLCOURIER'] == 2) ? ' checked' : '';?>> �� ��������� ����</label>
                                </label>
                            </div>
                        </div>
                        <?if (count($arResult["COMPANIES"]) > 1):?>
                        <div class="form-group">
                            <label class="control-label">����������� �� ���������</label>
                            <select class="form-control selectpicker" size="1" name="SENDER_DEFAULT" data-live-search="true" id="sender_default">
                                <option value="0"></option>
                                <?foreach($arResult["COMPANIES"] as $comp):?>
                                    <option value="<?=$comp["id"];?>"<?=($arResult['USER_SETTINGS']['SENDER_DEFAULT'] == $comp["id"]) ? ' selected' : '';?>><?=$comp["value"];?> - <?=$comp["name"];?></option>
                                <?endforeach;?>
                            </select>
                        </div>
                        <?endif;?>
                        <div class="form-group">
                            <label class="control-label">������ ��������</label>
                            <div class="radio">
                                <label>
                                    <input name="CHOICE_COMPANY" value="1" type="radio"<?=($arResult['USER_SETTINGS']['CHOICE_COMPANY'] == 1) ? ' checked' : '';?>> ���������� ������</label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="CHOICE_COMPANY" value="2" type="radio"<?=($arResult['USER_SETTINGS']['CHOICE_COMPANY'] == 2) ? ' checked' : '';?>> ������ �� ������ ������</label>
                            </div>
                        </div>
                        <?
                        ?>
                        <div class="form-group ">
                            <label class="control-label">��� ��������</label>
                            <div class="radio">
                                <label>
                                    <input name="TYPE_DELIVERY" value="345" type="radio"<?=($arResult['USER_SETTINGS']['TYPE_DELIVERY'] == 345) ? ' checked' : '';?>> �������� 2</label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="TYPE_DELIVERY" value="346" type="radio"<?=($arResult['USER_SETTINGS']['TYPE_DELIVERY'] == 346) ? ' checked' : '';?>> �������� 4</label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="TYPE_DELIVERY" value="338" type="radio"<?=($arResult['USER_SETTINGS']['TYPE_DELIVERY'] == 338) ? ' checked' : '';?>> �������� 8</label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="TYPE_DELIVERY" value="243" type="radio"<?=($arResult['USER_SETTINGS']['TYPE_DELIVERY'] == 243) ? ' checked' : '';?>> ��������</label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="TYPE_DELIVERY" value="244" type="radio"<?=($arResult['USER_SETTINGS']['TYPE_DELIVERY'] == 244) ? ' checked' : '';?>> ��������</label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="TYPE_DELIVERY" value="245" type="radio"<?=($arResult['USER_SETTINGS']['TYPE_DELIVERY'] == 245) ? ' checked' : '';?>> ������</label>
                            </div>
                            <? if (intval($arResult['AGENT']['PROPERTY_AVAILABLE_WH_WH_VALUE']) == 1) :?>
                            <div class="radio">
                              <label>
                                <input name="TYPE_DELIVERY" value="308" type="radio" <?=($arResult['USER_SETTINGS']['TYPE_DELIVERY'] == 308) ? 'checked=""' : '';?>>
                                �����-�����
                              </label>
                            </div>
                            <? endif; ?>
                        </div>
                        <div class="form-group">
                            <label class="control-label">���������</label>
                            <div class="radio">
                                <label>
                                    <input name="WHO_DELIVERY" value="248" type="radio"<?=($arResult['USER_SETTINGS']['WHO_DELIVERY'] == 248) ? ' checked' : '';?>> �� ������</label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="WHO_DELIVERY" value="249" type="radio"<?=($arResult['USER_SETTINGS']['WHO_DELIVERY'] == 249) ? ' checked' : '';?>> �� �������������</label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="WHO_DELIVERY" value="250" type="radio"<?=($arResult['USER_SETTINGS']['WHO_DELIVERY'] == 250) ? ' checked' : '';?>> ����� � ����</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 col-md-offset-1">
                        <div class="form-group">
                            <label class="control-label">��� �����������</label>
                            <div class="radio">
                                <label>
                                    <input name="TYPE_PACK" value="246" type="radio"<?=($arResult['USER_SETTINGS']['TYPE_PACK'] == 246) ? ' checked' : '';?>> ���������</label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="TYPE_PACK" value="247" type="radio"<?=($arResult['USER_SETTINGS']['TYPE_PACK'] == 247) ? ' checked' : '';?>> �� ���������</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label">����������</label>
                            <div class="radio">
                                <label>
                                    <input name="TYPE_PAYS" value="251" type="radio"<?=($arResult['USER_SETTINGS']['TYPE_PAYS'] == 251) ? ' checked' : '';?>> �����������</label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="TYPE_PAYS" value="252" type="radio"<?=($arResult['USER_SETTINGS']['TYPE_PAYS'] == 252) ? ' checked' : '';?>> ����������</label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="TYPE_PAYS" value="253" type="radio"<?=($arResult['USER_SETTINGS']['TYPE_PAYS'] == 253) ? ' checked' : '';?>> ������</label>
                            </div>
                            <? /* ?>
                            <div class="radio">
                                <label>
                                    <input name="TYPE_PAYS" value="254" type="radio"<?=($arResult['USER_SETTINGS']['TYPE_PAYS'] == 254) ? ' checked' : '';?>> ���������</label>
                            </div>
                            <? */ ?>
                        </div>
                        <div class="form-group">
                            <label class="control-label">������</label>
                            <div class="radio">
                                <label>
                                    <input name="PAYMENT" value="255" type="radio"<?=($arResult['USER_SETTINGS']['PAYMENT'] == 255) ? ' checked' : '';?>> ���������</label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="PAYMENT" value="256" type="radio"<?=($arResult['USER_SETTINGS']['PAYMENT'] == 256) ? ' checked' : '';?>> �� �����</label>
                            </div>
                        </div>
                        <div class="form-group">
                        	<label class="control-label">���������� ����� �������� �������������</label>
                            <div class="radio">
                                <label>
                                    <input name="MERGE_SENDERS" value="Y" type="radio"<?=($arResult['USER_SETTINGS']['MERGE_SENDERS'] == 'Y') ? ' checked' : '';?>> ��</label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="MERGE_SENDERS" value="N" type="radio"<?=($arResult['USER_SETTINGS']['MERGE_SENDERS'] == 'N') ? ' checked' : '';?>> ���</label>
                            </div>
                        </div>
                        <div class="form-group">
                        	<label class="control-label">����������� ����� �������� ������������</label>
                            <div class="radio">
                                <label>
                                    <input name="MERGE_RECIPIENTS" value="Y" type="radio"<?=($arResult['USER_SETTINGS']['MERGE_RECIPIENTS'] == 'Y') ? ' checked' : '';?>> ��</label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="MERGE_RECIPIENTS" value="N" type="radio"<?=($arResult['USER_SETTINGS']['MERGE_RECIPIENTS'] == 'N') ? ' checked' : '';?>> ���</label>
                            </div>
                        </div>
                       <!-- <div class="form-group">
                            <label class="control-label">������ ������ ��������</label>
                            <div class="radio">
                                <label>
                                    <input name="TARIF_NON_DEFAULT" value="Y" type="radio"<?/*=($arResult['USER_SETTINGS']['TARIF_NON_DEFAULT'] == 'Y') ? ' checked' : '';*/?>> ������������ �������������� ����� (�������)</label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input name="TARIF_NON_DEFAULT" value="N" type="radio"<?/*=($arResult['USER_SETTINGS']['TARIF_NON_DEFAULT'] !== 'Y') ? ' checked' : '';*/?>> ����� �� ���������  </label>
                            </div>
                        </div>-->
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-10 col-md-offset-1">
                        <div class="form-group text-center">
                            <input name="save" value="���������" class="btn btn-primary" type="submit"> </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>