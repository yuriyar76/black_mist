<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}


/*if($USER->isAdmin()){
    echo '<pre>';
    print_r($arResult);
    echo '</pre>';
}*/
?>


<?

?>
<div class="row">
		<div class="col-md-12">
            <h3>Добавление новых получателей</h3>
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
    <div class="row">
        <div class="col-md-12">
            <form action="" enctype="multipart/form-data" method="post" name="curform" class="form-inline">
                <input type="hidden" name="rand" value="<?=rand(100000,999999);?>">
                <input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>">
                <input type="hidden" name="MAX_FILE_SIZE" value="204800" />

                <div class="form-group example-2">
                    <input accept=".xls,.xlsx" type="file" name="blank-recipient" id="file" class="input-file">
                    <label for="file" class="btn btn-tertiary js-labelFile">
                        <i class="glyphicon glyphicon-cloud-upload"></i>
                        <span class="js-fileName">Загрузить файл</span>
                    </label>
                </div>
                <a href="<?=$arParams['TYPE_LINK'];?>blanks/test.xlsx"
                   class="btn btn-default" data-toggle="tooltip" data-placement="bottom"
                   title="Скачать образец справочника" download>
                    <span class="glyphicon glyphicon-cloud-download" aria-hidden="true"></span>
                    &nbsp;<small>Скачать образец справочника</small>

                </a>
                <button name="add_exel" type="submit" class="btn btn-success">Загрузить в справочник</button>
            </form>
        </div>
    </div>


	<?
}
?>
<script>
    (function() {
        'use strict';
        jQuery('.input-file').each(function() {
            var $input = jQuery(this),
                $label = $input.next('.js-labelFile'),
                labelVal = $label.html();

            $input.on('change', function(element) {
                var fileName = '';
                if (element.target.value) fileName = element.target.value.split('\\').pop();
                fileName ? $label.addClass('has-file').find('.js-fileName').html(fileName) : $label.removeClass('has-file').html(labelVal);
            });
        });

    })();

</script>
