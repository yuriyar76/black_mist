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
?>
<div class="row"><div class="col-md-6 col-md-offset-3"><div class="well bs-component"><div class="row"><div class="col-md-10 col-md-offset-1">
<h4>Настройки</h4>
<?
if (count($arResult['SETTINGS']) > 0)
{
	?>
	<form action="" method="post" name="curform">
        <?
        foreach ($arResult['SETTINGS'] as $set)
        {
            ?>
            <div class="form-group">
            <?
            if ($set["PROPERTY_TYPE"] == "S")
            {
                ?>
                
                    <label><?=$set["NAME"];?></label>
                    
                        <input type="text" name="<?=$set["CODE"];?>" value="<?=$set["VALUE"];?>" class="form-control">
                        <?
                        if ($set["ID"] == 274)
                        {
                            $APPLICATION->IncludeComponent(
                                "bitrix:main.calendar", 
                                ".default", 
                                array(
                                    "SHOW_INPUT" => "N",
                                    "FORM_NAME" => "curform",
                                    "INPUT_NAME" => $set["CODE"],
                                    "INPUT_NAME_FINISH" => "",
                                    "INPUT_VALUE" => $set["VALUE"],
                                    "INPUT_VALUE_FINISH" => "",
                                    "SHOW_TIME" => "Y",
                                    "HIDE_TIMEBAR" => "N"
                                ),
                                false
                            );
                        }
                        ?>
                    
                    
                        <?=$set["DESCRIPTION"];?>
                        <input type="hidden" name="names[<?=$set["ID"];?>]" value="<?=$set["CODE"];?>">
                    
                
                <?
            }

            if ($set["PROPERTY_TYPE"] == "N")
            {
                if ($set['USER_TYPE'] == 'SASDCheckboxNum')
                {
                    ?>
                    <label><?=$set["NAME"];?></label>
                    <div class="checkbox">
                        <label>
                          <input type="checkbox" value="1" name="<?=$set["CODE"];?>" <?=(intval($set['VALUE']) == 1) ? 'checked' : '';?>> <?=$set['USER_TYPE_SETTINGS']['VIEW'][1];?>
                        </label>
                      </div>
                    <?
                }
                else
                {
                ?>
                
                    <label><?=$set["NAME"];?></label>
                    <input type="text" name="<?=$set["CODE"];?>" value="<?=intval($set["VALUE"]);?>" class="form-control">
                    <?=$set["DESCRIPTION"];?><input type="hidden" name="names[<?=$set["ID"];?>]" value="<?=$set["CODE"];?>">
                
                <?
                }
            }

            if ($set["PROPERTY_TYPE"] == "L")
            {
                $val_id = $set["ID"];
                ?>
                
                    <label><?=$set["NAME"];?></label>
                    
                        <select name="<?=$set["CODE"];?>" size="1" class="form-control">
                            <?
                            
                            foreach ($arResult[$val_id] as $v)
                            {
                                if ($v["ID"] == $set["VALUE"])
                                {
                                    $s = ' selected';
                                }
                                else
                                {
                                    $s = '';
                                }
                                ?>
                                <option value="<?=$v["ID"];?>"<?=$s;?>><?=$v["VALUE"];?></option>
                                <?
                            }
                            ?>
                        </select>
                    
                    <?=$set["DESCRIPTION"];?><input type="hidden" name="names[<?=$set["ID"];?>]" value="<?=$set["CODE"];?>">
                
                <?
            }

            if (($set["PROPERTY_TYPE"] == "E") && (in_array($set['LINK_IBLOCK_ID'], array(40,51))))
            {
                ?>
                
                    <label><?=$set["NAME"];?></label>
                    
                        <select name="<?=$set["CODE"];?>" size="1" class="form-control">
                            <?
                            foreach ($arResult[$set['ID']] as $k => $v)
                            {
                                if ($k == $set["VALUE"])
                                {
                                    $s = ' selected';
                                }
                                else
                                {
                                    $s = '';
                                }
                                ?>
                                <option value="<?=$k;?>"<?=$s;?>><?=$v;?></option>
                                <?
                            }
                            ?>
                        </select>
                    
                    <?=$set["DESCRIPTION"];?><input type="hidden" name="names[<?=$set["ID"];?>]" value="<?=$set["CODE"];?>">
                
                <?
            }
            ?>
            </div>
            <?
        }
        ?>
		<input type="submit" name="save" value="Сохранить" class="btn btn-primary">
	</form>
	<?
}
?>
</div></div></div></div></div>