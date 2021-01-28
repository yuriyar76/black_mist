<div class="panel panel-default">
	<div class="panel-body">
        <form action="<?=$arResult["PAGE"]?>" method="get" class="form-inline">
            <?
            foreach ($arResult["HID_FIELDS"] as $k => $v)
            {
                ?>
                <input type="hidden" name="<?=$k?>" value="<?=$v;?>">
                <?
            }
            ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group form-group-sm">
                        <label>Показывать на одной странице:</label>
                        <select name="on_page" size="1" class="form-control">
                            <?
                            foreach ($arResult["PAGES"] as $p)
                            {
                                ?>
                                <option value="<?=$p;?>"<?=($arResult['ON_PAGE_GLOBAL'] == $p) ? ' selected': ''; ?>><?=$p;?></option>
                                <?
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group form-group-sm">
                        <input type="submit" name="" value="Применить" class="btn btn-default">
                    </div>
                </div>
                <div class="col-md-6 text-right">
                    <?=$arResult["NAV_STRING"];?>
                </div>
            </div>
        </form>
	</div>
</div>