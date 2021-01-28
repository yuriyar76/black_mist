<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
    die();
}

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
    <div class="row block_down">
        <div class="col-md-4 block_down_title">
            <h3>Выгрузка получателей в EXEL файл</h3>
        </div>
        <div class="col-md-4">
            <form action="" enctype="multipart/form-data" method="post" name="curform" class="form-inline">
                <input type="hidden" name="rand" value="<?=rand(100000,999999);?>">
                <input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>">
                <div class="form-group form-group-down">
                    <a href="<?=$arParams['TYPE_LINK'];?>Data_list.xls"
                       class="btn btn-success" data-toggle="tooltip" data-placement="bottom"
                       title="Скачать справочник получателей" download>
                        <span class="glyphicon glyphicon-cloud-download" aria-hidden="true"></span>
                    </a>
                </div>
            </form>
        </div>
    </div>


    <?
}
?>

