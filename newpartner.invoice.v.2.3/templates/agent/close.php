<?		
if (count($arResult["ERRORS"]) > 0) 
{
	?>
    <div class="alert alert-dismissable alert-danger text-center fade in" role="alert">
        <?=implode('</br>',$arResult["ERRORS"]);?>
    </div>
    <?
}