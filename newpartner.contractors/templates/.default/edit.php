<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
?>

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
    <div class="alert alert-dismissable alert-success"><?=implode('</br>',$arResult["WARNINGS"]);?></div>
    <?
}
if (($arResult['OPEN']) && ($arResult['COMPANY']))
{
	?>
	<form action="" method="post" name="curform" class="form-vertical">
		<input type="hidden" name="rand" value="<?=rand(100000,999999);?>">
		<input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>">
        <input type="hidden" name="id" value="<?=$arResult['COMPANY']['ID'];?>">
        <div class="row">
        	 <div class="col-md-4">
				<div class="form-group <?=$arResult['ERR_FIELDS']['COMPANY_SENDER'];?>">
					<label class="control-label">Компания</label>
					<input type="text" class="form-control" name="COMPANY_SENDER" value="<?=$arResult['COMPANY']['NAME'];?>">
				</div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['NAME_SENDER'];?>">
					<label class="control-label">Фамилия</label>
					<input type="text" class="form-control" name="NAME_SENDER" value="<?=$arResult['COMPANY']['PROPERTY_NAME_VALUE'];?>">
				</div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['PHONE_SENDER'];?>">
					<label class="control-label">Телефон</label>
					<input type="text" class="form-control" name="PHONE_SENDER" value="<?=$arResult['COMPANY']['PROPERTY_PHONE_VALUE'];?>">
				</div>
                <div class="form-group <?=$arResult['ERR_FIELDS']['CITY_SENDER'];?>">
                    <label class="control-label">Город</label>
                    <input type="text" class="form-control autocity" name="CITY_SENDER" value="<?=$arResult['COMPANY']['PROPERTY_CITY'];?>" id="autocity_sender">
                </div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['INDEX_SENDER'];?>">
                    <label class="control-label">Индекс</label>
                    <input type="text" class="form-control" name="INDEX_SENDER" value="<?=$arResult['COMPANY']['PROPERTY_INDEX_VALUE'];?>">
                </div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['ADRESS_SENDER'];?>">
                    <label class="control-label">Адрес</label>
                    <textarea class="form-control" name="ADRESS_SENDER"><?=$arResult['COMPANY']['PROPERTY_ADRESS_VALUE'];?></textarea>
                </div>
             </div>
        </div>
		<input type="submit" name="save" value="Сохранить" class="btn btn-primary">
	</form>
	<?
}
?>