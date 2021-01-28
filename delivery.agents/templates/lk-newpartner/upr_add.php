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
?>
<div class="row">
	<div class="col-md-12">
    	<h2>Добавление нового агента</h2>
    </div>
</div>
<div class="row">
    <div class="col-md-5">
        <form action="index.php?mode=<?=$_GET['mode'];?>" method="post" class="form-horizontal">
            <input type="hidden" name="rand" value="<?=rand(100000,999999);?>" />
            <input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>" />
            <div class="form-group">
                <label class="col-md-4 control-label"><?=GetMessage("LABEL_1");?></label>
                <div class="col-md-8">
                    <input type="text" name="fio" value="<?=$_POST['fio'];?>" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-4 control-label"><?=GetMessage("LABEL_8");?></label>
                <div class="col-md-8">
                    <input type="text" name="email" value="<?=$_POST['email'];?>" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-4 control-label"><?=GetMessage("LABEL_9");?></label>
                <div class="col-md-8">
                    <input type="text" name="phones" value="<?=$_POST['phones'];?>" class="form-control">
                </div>
            </div>
			<div class="form-group">
                <label class="col-md-4 control-label"><?=GetMessage("LABEL_4");?></label>
                <div class="col-md-8">
                    <input type="text" name="city" value="<?=$_POST['city'];?>" class="form-control autocity">
                </div>
            </div>
			<div class="form-group">
                <label class="col-md-4 control-label"><?=GetMessage("LABEL_5");?></label>
                <div class="col-md-8">
                    <input type="text" name="adress" value="<?=$_POST['adress'];?>" class="form-control">
                </div>
            </div>
			<div class="form-group">
                <label class="col-md-4 control-label"><?=GetMessage("LABEL_6");?></label>
                <div class="col-md-8">
                    <input type="text" name="inn" value="<?=$_POST['inn'];?>" class="form-control">
                </div>
            </div>
			<div class="form-group">
                <label class="col-md-4 control-label"><?=GetMessage("LABEL_14");?></label>
                <div class="col-md-8">
                    <select class="form-control" size="1" name="branch">
                        <option value="1"<?=($_POST['branch'] == 1) ? ' selected': '' ?>><?=GetMessage("YES");?></option>
                        <option value="0"<?=($_POST['branch'] != 1) ? ' selected': '' ?>><?=GetMessage("NO");?></option>
                    </select>
                </div>
            </div>
			<div class="form-group">
                <label class="col-md-4 control-label"><?=GetMessage("LABEL_20");?></label>
                <div class="col-md-8">
                    <select class="form-control" size="1" name="type_agent">
                        <option value="false"></option>
                        <option value="280"<?=($_POST['type_agent'] == 280) ? ' selected': '' ?>><?=GetMessage("TYPE_1");?></option>
                        <option value="281"<?=($_POST['type_agent'] == 281) ? ' selected': '' ?>><?=GetMessage("TYPE_2");?></option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-4 control-label"><?=GetMessage("LABEL_15");?></label>
                <div class="col-md-8">
                    <select class="form-control" size="1" name="active">
                        <option value="Y"<?=($_POST['active'] == 'Y') ? ' selected': '' ?>><?=GetMessage("YES");?></option>
                        <option value="N"<?=($_POST['active'] == 'N') ? ' selected': '' ?>><?=GetMessage("NO");?></option>
                    </select>
                </div>
            </div>
			<div class="form-group">
                <div class="col-md-8 col-md-offset-4">
                     <input type="submit" name="add_agent" value="<?=GetMessage("BTN_ADD");?>" class="btn btn-primary">
                </div>
            </div>
		</form>
	</div>
</div>