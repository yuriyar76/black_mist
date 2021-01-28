<script type="text/javascript">
	$(document).ready(function(){
		AutoCity();
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
    <div role="alert" class=" ">
    
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

if (is_array($arResult["AGENT"]))
{
	?>
	<div class="row">
		<div class="col-md-5">
			<h2><?=$arResult["AGENT"]["NAME"];?></h2>
		</div>
        <div class="col-md-6 col-md-offset-1">
        	<h3>Дополнительные адреса</h3>
        </div>
	</div>  
    
    
    <form action="index.php?mode=<?=$_GET['mode'];?>&id=<?=$_GET['id'];?>" method="post" class="form-horizontal">
        <input type="hidden" name="id" value="<?=$arResult["AGENT"]["ID"];?>">
        <input type="hidden" name="rand" value="<?=rand(100000,999999);?>" />
        <input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>" />
        <div class="row">
            <div class="col-md-5">
                <div class="form-group">
                    <label class="col-md-4 control-label"><?=GetMessage("LABEL_1");?></label>
                    <div class="col-md-8">
                        <input type="text" name="name" value="<?=$arResult["AGENT"]["NAME"];?>" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-4 control-label"><?=GetMessage("LABEL_17");?></label>
                    <div class="col-md-8">
                        <input type="text" name="brand_name" value="<?=$arResult["AGENT"]["PROPERTY_BRAND_NAME_VALUE"];?>" class="form-control">
                    </div>
                </div>
                <div class="form-group">
					<label class="col-md-4 control-label"><?=GetMessage("LABEL_7");?></label>
                    <div class="col-md-8">
                		<input type="text" name="contact" value="<?=(isset($_POST['contact'])) ? $_POST['contact'] : $arResult["AGENT"]["PROPERTY_RESPONSIBLE_PERSON_VALUE"];?>" class="form-control">
                	</div>
                </div>
                <div class="form-group">
                    <label class="col-md-4 control-label"><?=GetMessage("LABEL_8");?></label>
                    <div class="col-md-8">
                        <input type="text" name="email" value="<?=(isset($_POST['email'])) ? $_POST['email'] : $arResult["AGENT"]["PROPERTY_EMAIL_VALUE"];?>" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-4 control-label"><?=GetMessage("LABEL_9");?></label>
                    <div class="col-md-8">
                        <input type="text" name="phones" value="<?=(isset($_POST['phones'])) ? $_POST['phones'] : $arResult["AGENT"]["PROPERTY_PHONES_VALUE"];?>" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-4 control-label"><?=GetMessage("LABEL_4");?></label>
                    <div class="col-md-8">
                        <input type="text" name="city" value="<?=(isset($_POST['city'])) ? $_POST['city'] : $arResult["AGENT"]["PROPERTY_CITY"];?>" class="form-control autocity">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-4 control-label"><?=GetMessage("LABEL_5");?></label>
                    <div class="col-md-8">
                        <input type="text" name="adress" value="<?=(isset($_POST['adress'])) ? $_POST['adress'] : $arResult["AGENT"]["PROPERTY_ADRESS_VALUE"];?>" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-4 control-label"><?=GetMessage("LABEL_6");?></label>
                    <div class="col-md-8">
                        <input type="text" name="inn" value="<?=(isset($_POST['inn'])) ? $_POST['inn'] : $arResult["AGENT"]["PROPERTY_INN_VALUE"];?>" class="form-control">
                    </div>
                </div>
                <div class="form-group <?=$arResult['ERR_FIELDS']['INN_REAL'];?>">
                    <label class="col-md-4 control-label">ИНН</label>
                    <div class="col-md-8">
                    	<input type="text" class="form-control" name="INN_REAL" value="<?=(isset($_POST['INN_REAL'])) ? $_POST['INN_REAL'] : $arResult["AGENT"]["PROPERTY_INN_REAL_VALUE"];?>">
					</div>
                </div>
                <div class="form-group">
                    <label class="col-md-4 control-label"><?=GetMessage("LABEL_10");?></label>
                    <div class="col-md-8">
                        <input type="text" name="cite" value="<?=(isset($_POST['cite'])) ? $_POST['cite'] : $arResult["AGENT"]["PROPERTY_CITE_VALUE"];?>" class="form-control">
                    </div>
                </div>
				<div class="form-group">
                    <label class="col-md-4 control-label"><?=GetMessage("LABEL_21");?></label>
                    <div class="col-md-8">
                        <input type="text" name="COEFFICIENT_VW" value="<?=(isset($_POST['COEFFICIENT_VW'])) ? $_POST['COEFFICIENT_VW'] : $arResult["AGENT"]["PROPERTY_COEFFICIENT_VW_VALUE"];?>" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                	<label class="col-md-4 control-label"><?=GetMessage("LABEL_16");?></label>
					<div class="col-md-8">
                        <select class="form-control" size="1" name="branch">
							<option value="1"<?=($arResult["AGENT"]["PROPERTY_BRANCH_VALUE"] == 1) ? ' selected': '' ?>><?=GetMessage("YES");?></option>
							<option value="0"<?=($arResult["AGENT"]["PROPERTY_BRANCH_VALUE"] != 1) ? ' selected': '' ?>><?=GetMessage("NO");?></option>
                        </select>
                    </div>
                </div>
				<div class="form-group">
                	<label class="col-md-4 control-label"><?=GetMessage("LABEL_20");?></label>
					<div class="col-md-8">
                        <select class="form-control" size="1" name="type_agent">
                        	<option value="false"></option>
							<option value="280"<?=($arResult["AGENT"]["PROPERTY_TYPE_AGENT_ENUM_ID"] == 280) ? ' selected': '' ?>><?=GetMessage("TYPE_1");?></option>
							<option value="281"<?=($arResult["AGENT"]["PROPERTY_TYPE_AGENT_ENUM_ID"] == 281) ? ' selected': '' ?>><?=GetMessage("TYPE_2");?></option>
                        </select>
                    </div>
                </div>
				<div class="form-group">
                	<label class="col-md-4 control-label"><?=GetMessage("LABEL_12");?></label>
					<div class="col-md-8">
                        <select class="form-control" size="1" name="active">
                            <option value="Y"<? echo ($arResult["AGENT"]["ACTIVE"] == 'Y') ? ' selected': '' ?>><?=GetMessage("YES");?></option>
                            <option value="N"<? echo ($arResult["AGENT"]["ACTIVE"] == 'N') ? ' selected': '' ?>><?=GetMessage("NO");?></option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-8 col-md-offset-4">
                         <input type="submit" name="save_agent" value="<?=GetMessage("SAVE_BTN");?>" class="btn btn-primary">
                    </div>
                </div>      
            </div>
            <div class="col-md-2 col-md-offset-1">
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Наименование" name="additional_addresses[0][name]" value="<?=$arResult["AGENT"]["ADDITIONAL_ADDRESSES"][0]["name"];?>">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control autocity" placeholder="Город" name="additional_addresses[0][city]" value="<?=$arResult["AGENT"]["ADDITIONAL_ADDRESSES"][0]["city_name"];?>">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Адрес" name="additional_addresses[0][adress]" value="<?=$arResult["AGENT"]["ADDITIONAL_ADDRESSES"][0]["adress"];?>">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Номер телефона" name="additional_addresses[0][phone]" value="<?=$arResult["AGENT"]["ADDITIONAL_ADDRESSES"][0]["phone"];?>">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="E-mail" name="additional_addresses[0][email]" value="<?=$arResult["AGENT"]["ADDITIONAL_ADDRESSES"][0]["email"];?>">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Адрес сайта" name="additional_addresses[0][site]" value="<?=$arResult["AGENT"]["ADDITIONAL_ADDRESSES"][0]["site"];?>">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Наименование" name="additional_addresses[1][name]">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control autocity" placeholder="Город" name="additional_addresses[1][city]">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Адрес" name="additional_addresses[1][adress]">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Номер телефона" name="additional_addresses[1][phone]">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="E-mail" name="additional_addresses[1][email]">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Адрес сайта" name="additional_addresses[1][site]">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Наименование" name="additional_addresses[2][name]">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control autocity" placeholder="Город" name="additional_addresses[2][city]">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Адрес" name="additional_addresses[2][adress]">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Номер телефона" name="additional_addresses[2][phone]">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="E-mail" name="additional_addresses[2][email]">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Адрес сайта" name="additional_addresses[2][site]">
                </div>
            </div>
        </div>
    </form>
	<?
	if (count($arResult["AGENT"]['USERS']) > 0)
	{
		?>
        <div class="row">
            <div class="col-md-12">
                <h3>Пользователи</h3>
			</div>
		</div>
		<div class="row">
		<div class="col-md-12">
			<div class="table-responsive">
                <table class="table table-striped table-bordered table-hover table-condensed">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ФИО</th>
                            <th>Логин</th>
                            <th>Роль</th>
                            <th>Дата регистрации</th>
                            <th>Последняя авторизация</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?
                        foreach ($arResult["AGENT"]['USERS'] as $u)
                        {
                            ?>
                            <tr>
                                <td><?=$u['ID'];?></td>
                                <td><?=$u['LAST_NAME'].' '.$u['NAME'];?></td>
                                <td><?=$u['LOGIN'];?></td>
                                <td><?=$u['ROLE']['NAME'];?></td>
                                <td><?=$u['DATE_REGISTER'];?></td>
                                <td><?=$u['LAST_LOGIN'];?></td>
                            </tr>
                            <?
                        }
                        ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
        <?
	}
}
?>