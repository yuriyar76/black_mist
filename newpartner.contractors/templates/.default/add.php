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
<?

?>
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
if ($arResult['OPEN']) 
{
	?>
	<form action="" method="post" name="curform" class="form-vertical">
		<input type="hidden" name="rand" value="<?=rand(100000,999999);?>">
		<input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>">
        <div class="row">
        	 <div class="col-md-4">
				<div class="form-group <?=$arResult['ERR_FIELDS']['COMPANY_SENDER'];?>">
					<label class="control-label">Компания</label>
					<input type="text" class="form-control" name="COMPANY_SENDER" value="<?=strlen($_POST['COMPANY_SENDER']) ? $_POST['COMPANY_SENDER'] : $arResult['DEAULTS']['COMPANY_SENDER'];?>">
				</div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['NAME_SENDER'];?>">
					<label class="control-label">Фамилия</label>
					<input type="text" class="form-control" name="NAME_SENDER" value="<?=strlen($_POST['NAME_SENDER']) ? $_POST['NAME_SENDER'] : $arResult['DEAULTS']['NAME_SENDER'];?>">
				</div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['PHONE_SENDER'];?>">
					<label class="control-label">Телефон</label>
					<input type="text" class="form-control" name="PHONE_SENDER" value="<?=strlen($_POST['PHONE_SENDER']) ? $_POST['PHONE_SENDER'] : $arResult['DEAULTS']['PHONE_SENDER'];?>">
				</div>
                <div class="form-group <?=$arResult['ERR_FIELDS']['CITY_SENDER'];?>">
                    <label class="control-label">Город</label>
                    <input type="text" class="form-control autocity" name="CITY_SENDER" value="<?=strlen($_POST['CITY_SENDER']) ? $_POST['CITY_SENDER'] : $arResult['DEAULTS']['CITY_SENDER'];?>" id="autocity_sender">
                </div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['INDEX_SENDER'];?>">
                    <label class="control-label">Индекс</label>
                    <input type="text" class="form-control" name="INDEX_SENDER" value="<?=$_POST['INDEX_SENDER'];?>">
                </div>
				<div class="form-group <?=$arResult['ERR_FIELDS']['ADRESS_SENDER'];?>">
                    <label class="control-label">Адрес</label>
                    <textarea class="form-control" name="ADRESS_SENDER"><?=strlen($_POST['ADRESS_SENDER']) ? $_POST['ADRESS_SENDER'] : $arResult['DEAULTS']['ADRESS_SENDER'];?></textarea>
                </div>
             </div>

        </div>

		<input type="submit" name="add" value="Добавить" class="btn btn-primary">
	</form>
	<?
}
?>