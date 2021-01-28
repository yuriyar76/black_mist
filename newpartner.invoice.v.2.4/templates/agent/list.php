<script type="text/javascript">
    $(document).ready(function(){
        $('.maskdate').mask('99.99.9999');
    });
    $(function () {
        $(window).resize(function () {
            $('#tableId').bootstrapTable('resetView');
        });
    });

    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });

    function setChecked(obj,name)
    {
        var check = document.getElementsByName(name+"[]");
        for (var i=0; i<check.length; i++)
        {
            check[i].checked = obj.checked;
        }
        $('tr.CheckedRows').each(function(){
            if(obj.checked)
            {
                $(this).addClass('info');
            }
            else
            {
                $(this).removeClass('info');
            }
        });
    }

    function ChangePeriod()
    {
        var y = $("select#year").val();
        var m = $("select#month").val();
        location.href = '<?=$arParams['LINK'];?>?ChangePeriod=Y&year='+y+'&month='+m;
    }

    function ChangePeriodNew()
    {
        $('#input-group-list-from-date').removeClass('has-error');
        $('#input-group-list-to-date').removeClass('has-error');
        var datefrom = $("input#list-from-date").val();
        var dateto = $("input#list-to-date").val();
        if ((dateto.length > 0) && (datefrom.length > 0))
        {
            location.href = '<?=$arParams['LINK'];?>?ChangePeriod=Y&datefrom='+datefrom+'&dateto='+dateto;
        }
        else
        {
            if (dateto.length <= 0)
            {
                $('#input-group-list-to-date').addClass('has-error');
            }
            if (datefrom.length <= 0)
            {
                $('#input-group-list-from-date').addClass('has-error');
            }
        }
    }

    function ChangeClient()
    {
        var cl = $("select#client").val();
        location.href = '<?=$arParams['LINK'];?>?ChangeClient=Y&client='+cl;
    }

    function ChangeBranch()
    {
        var br = $("select#branch").val();
        location.href = '<?=$arParams['LINK'];?>?ChangeBranch=Y&branch='+br;
    }
    <?
    if (($_GET['openprint'] == 'Y') && (intval($_GET['id']) > 0))
    {
    ?>
    $(document).ready(function() {
        window.open('<?=$arParams['LINK'];?>?mode=print&id=<?=intval($_GET['id']);?>&print=Y');
    });
    <?
    }
    ?>
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
    <div class="alert alert-dismissable alert-success fade in" role="alert">
        <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">X</span><span class="sr-only">Закрыть</span></button>
        <?=implode('</br>',$arResult["MESSAGE"]);?>
    </div>
    <?
}
if (count($arResult["WARNINGS"]) > 0)
{
    ?>
    <div class="alert alert-dismissable alert-warning fade in" role="alert">
        <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">X</span><span class="sr-only">Закрыть</span></button>
        <?=implode('</br>',$arResult["WARNINGS"]);?>
    </div>
    <?
}

if ($arResult['OPEN'])
{
    ?>
    <div class="row">
        <div class="col-md-3">
            <?if ($arResult['CURRENT_CLIENT'] > 0):?>
                <?if ((count($arResult['REQUESTS']) > 0) ||  (count($arResult['ARCHIVE']) > 0)) :?>
                    <form action="<?=$arParams['LINK'];?>?mode=list_xls&pdf=Y" method="post" name="xlsform" target="_blank">
                    <input type="hidden" name="DATA" value="<?=htmlspecialchars($arResult['ARCHIVE_STR_JSON'],ENT_COMPAT);?>">
                <?endif;?>
                <div class="btn-group">
                    <div class="btn-group" role="group">
                        <a href="<?=$arParams['LINK'];?>?mode=add" class="btn btn-warning test_unit" id="new_btn"><span class="glyphicon glyphicon-file" aria-hidden="true"></span> Новая накладная</a>
                    </div>
                    <div class="btn-group" role="group">
                        <a href="<?=$arParams['LINK'];?>" class="btn btn-default" data-toggle="tooltip" data-placement="bottom"  title="Обновить список накладных">
                            <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
                        </a>
                    </div>
                    <?if ((count($arResult['REQUESTS']) > 0) ||  (count($arResult['ARCHIVE']) > 0)) :?>
                        <div class="btn-group" role="group">
                            <button type="submit" class="btn btn-default" data-toggle="tooltip" data-placement="bottom" title="Скачать список накладных">
                                <span class="glyphicon glyphicon-cloud-download" aria-hidden="true"></span>
                            </button>
                        </div>
                    <?endif;?>
                    <div class="btn-group" role="group">
                        <a href="<?=$arParams['LINK'];?>?mode=upload" class="btn btn-default" data-toggle="tooltip" data-placement="bottom"  title="Загурзить список накладных">
                            <span class="glyphicon glyphicon-cloud-upload" aria-hidden="true"></span>
                        </a>
                    </div>
                </div>
                <?if ((count($arResult['REQUESTS']) > 0) ||  (count($arResult['ARCHIVE']) > 0)) :?>
                    </form>
                <?endif;?>
            <?endif;?>
        </div>
        <div class=" col-md-9 text-right">

            <form action="" method="get" name="filterform" class="form-inline">
                <?
                if ($arResult['LIST_OF_CLIENTS'])
                {
                    ?>
                    <div class="form-group">
                        <select name="client" size="1" class="form-control selectpicker" id="client" onChange="ChangeClient();" data-live-search="true" data-width="auto">
                            <option value="0"></option>
                            <?
                            foreach ($arResult['LIST_OF_CLIENTS'] as $k => $v)
                            {
                                $s = ($arResult['CURRENT_CLIENT'] == $k) ? ' selected' : '';
                                ?>
                                <option value="<?=$k;?>"<?=$s;?>><?=$v;?></option>
                                <?
                            }
                            ?>
                        </select>
                    </div>
                    <?
                }
                if ($arResult['USER_IN_BRANCH'])
                {
                    ?>
                    <div class="form-group">
                        <h3 style="margin:-4px 0 0;">
                            <span class="label label-success">Филиал: <?=$arResult['LIST_OF_BRANCHES'][$arResult['CURRENT_BRANCH']];?></span>
                            <? if ($arResult['AGENT']["PROPERTY_TYPE_WORK_BRANCHES_ENUM_ID"] == 301) : ?>
                                <a href="/choice-branch/" class="btn btn-default" title="Выбрать другой филиал"><span class="glyphicon glyphicon-retweet" aria-hidden="true"></span></a>
                            <? endif;?>
                        </h3>
                    </div>
                    <?
                }
                else
                {
                    if ($arResult['LIST_OF_BRANCHES'])
                    {
                        ?>
                        <div class="form-group">
                            <select name="branch" size="1" class="form-control selectpicker" id="branch" onChange="ChangeBranch();" data-live-search="true" data-width="auto">
                                <option value="0">Все</option>
                                <?
                                foreach ($arResult['LIST_OF_BRANCHES'] as $k => $v)
                                {
                                    $s = ($arResult['CURRENT_BRANCH'] == $k) ? ' selected' : '';
                                    ?>
                                    <option value="<?=$k;?>"<?=$s;?>><?=$v;?></option>
                                    <?
                                }
                                ?>
                            </select>
                        </div>
                        <?
                    }
                }
                ?>
                <div class="form-group">
                    <div class="input-group" id="input-group-list-from-date">
                        <input type="text" class="form-control maskdate" aria-describedby="basic-addon1" name="dateperiodfrom" placeholder="ДД.ММ.ГГГГ" value="<?=$arResult['LIST_FROM_DATE'];?>" onChange="ChangePeriodNew();" id="list-from-date">
                        <span class="input-group-addon" id="basic-addon1">
							<?
                            $APPLICATION->IncludeComponent(
                                "bitrix:main.calendar",
                                ".default",
                                array(
                                    "SHOW_INPUT" => "N",
                                    "FORM_NAME" => "",
                                    "INPUT_NAME" => "dateperiodfrom",
                                    "INPUT_NAME_FINISH" => "",
                                    "INPUT_VALUE" => "",
                                    "INPUT_VALUE_FINISH" => false,
                                    "SHOW_TIME" => "N",
                                    "HIDE_TIMEBAR" => "Y",
                                    "INPUT_ADDITIONAL_ATTR" => ''
                                ),
                                false
                            );
                            ?>
						</span>
                    </div>
                </div>
                <div class="form-group">&nbsp;&mdash;&nbsp;</div>
                <div class="form-group">
                    <div class="input-group" id="input-group-list-to-date">
                        <input type="text" class="form-control maskdate" aria-describedby="basic-addon2" name="dateperiodto" placeholder="ДД.ММ.ГГГГ" value="<?=$arResult['LIST_TO_DATE'];?>" onChange="ChangePeriodNew();" id="list-to-date">
                        <span class="input-group-addon" id="basic-addon2">
							<?
                            $APPLICATION->IncludeComponent(
                                "bitrix:main.calendar",
                                ".default",
                                array(
                                    "SHOW_INPUT" => "N",
                                    "FORM_NAME" => "",
                                    "INPUT_NAME" => "dateperiodto",
                                    "INPUT_NAME_FINISH" => "",
                                    "INPUT_VALUE" => "",
                                    "INPUT_VALUE_FINISH" => false,
                                    "SHOW_TIME" => "N",
                                    "HIDE_TIMEBAR" => "Y",
                                    "INPUT_ADDITIONAL_ATTR" => ''
                                ),
                                false
                            );
                            ?>
						</span>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row"><div class="col-md-12">&nbsp;</div></div>
    <div class="row">
    <div class="col-md-12">
    <?
    if ((count($arResult['REQUESTS']) > 0) ||  (count($arResult['ARCHIVE']) > 0))
    {
        if (count($arResult['REQUESTS']) > 0)
        {
            ?>
            <form action="" method="post">
            <input type="hidden" name="rand" value="<?=rand(100000,999999);?>">
            <input type="hidden" name="key_session" value="key_session_<?=rand(100000,999999);?>">
            <?
        }
        $itogo = 0;

        ?>

        <table class="table table-condensed table-hover" data-toggle="table" data-show-columns="true"
               data-search="true" data-select-item-name="toolbar1" data-height="600"
               id="tableId" <?/*?> data-sort-name="date" data-sort-order="desc"<?*/?>>
            <thead>
            <tr>
                <!-- <th width="20" data-field="column1"></th>-->
                <th width="20" data-field="column2" data-switchable="false"></th>
                <th width="20" data-field="column14" data-switchable="false"></th>
                <!--удаление  -->
                <?if (count($arResult['REQUESTS']) > 0):?>
                   <th width="20" data-field="column22" data-switchable="false"></th>
                <?endif;?>
                <!--удаление  -->


                    <th width="20"  aria-hidden="true"
                        data-toggle="tooltip" data-placement="right"
                        title="Скачать сканы накладных">
                        <span class="glyphicon glyphicon-paperclip" </span>
                    </th>


                <th data-field="number" data-switchable="false" data-sortable="true"><?=GetMessage('TABLE_HEAD_1');?></th>
                <th width="20" class="inner_number_claim" data-field="column19" data-switchable="false">Вн. номер заявки</th>
                <th data-field="date" data-sortable="false"><?=GetMessage('TABLE_HEAD_3');?></th>
                <th data-field="client" data-sortable="true"><?=GetMessage('TABLE_HEAD_14');?></th>
                <th data-field="column6" data-sortable="true"><?=GetMessage('TABLE_HEAD_4');?></th>
                <th data-field="column7" data-sortable="true"><?=GetMessage('TABLE_HEAD_5');?></th>
                <th data-field="column8" data-sortable="true"><?=GetMessage('TABLE_HEAD_7');?></th>
                <th data-field="column9" data-sortable="true"><?=GetMessage('TABLE_HEAD_6');?></th>
                <th data-field="column10"><?=GetMessage('TABLE_HEAD_8');?></th>
                <th data-field="column11"><?=GetMessage('TABLE_HEAD_9');?></th>
                <th data-field="column12"><?=GetMessage('TABLE_HEAD_10');?></th>
                <th width="20" data-field="column15" data-switchable="false"></th>
                <th data-field="column4" data-sortable="true"><?=GetMessage('TABLE_HEAD_2');?></th>
                <th data-field="manager"><?=GetMessage('TABLE_HEAD_17');?></th>
                <!--<th data-field="column20" data-switchable="false" width="20">Ответственный</th>-->
                <th data-field="column18" data-switchable="false" width="20"></th>
                <th data-field="column21" data-switchable="false" width="20"></th>

            </tr>
            </thead>
            <tbody>
            <?
            foreach ($arResult['REQUESTS'] as $r)
            {

                /* --- */
                //	echo '<!-- <pre> 6::';
                //		print_r($r);
                //	echo '</pre> -->';
                /* --- */


                ?>
                <tr class="<?=$r['ColorRow'];?>">
                    <!--<td width="20">
							<?/*
							if ($r['PROPERTY_STATE_ENUM_ID'] == 257)
							{
								// здесь выясняется наше поле накладных
								*/?>
								<input class="test_unit" type="checkbox" name="ids[]" value="<?/*=$r['ID'];*/?>">
								<?/*
							}
							*/?>
						</td>-->
                    <td data-halign="center" data-align="center" data-valign="center">
                        <a href="<?=$arParams['LINK'];?>?mode=print&id=<?=$r['ID'];?>&print=Y" target="_blank">
                            <span class="glyphicon glyphicon-print" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Печать накладной"></span>
                        </a>
                    </td>
                    <td data-halign="center" data-align="center" data-valign="center">
                        <?
                        if (($r['PROPERTY_STATE_ENUM_ID'] == 257) && (!$arResult['ADMIN_AGENT']))
                        {
                            ?>
                            <a href="<?=$arParams['LINK'];?>?mode=edit&id=<?=$r['ID'];?>"><span class="glyphicon glyphicon-pencil" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Редактирование накладной"></span></a>
                            <?
                        }
                        else
                        {
                            ?>
                            <a href="<?=$arParams['LINK'];?>?mode=invoice_modal&id=<?=$r['ID'];?>&pdf=Y" data-toggle="modal" data-target="#modal_<?=$r['ID'];?>">
                                <span class="glyphicon glyphicon-zoom-in" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Просмотр накладной"></span>
                            </a>
                            <?
                        }

                        ?>
                    </td>
                    <!--удаление  -->
                    <td>
                            <span style="cursor: pointer;" data-toggle="modal"
                                  data-target="#myModal_<?=$r['ID'];?>"
                                  class="glyphicon glyphicon-trash"></span>
                    </td>
                    <!--    удаление    -->

                    <td></td>

                    <td><?=$r['NAME'];?></td>

                    <td>
                        <?/* //echo "..."; */?>
                        <?=($r['PROPERTY_INNER_NUMBER_CLAIM_VALUE'])?$r['PROPERTY_INNER_NUMBER_CLAIM_VALUE']:0;?>
                    </td>
                    <td><?=substr($r['DATE_CREATE'],0,10);?></td>
                    <td><?=strlen($r['PROPERTY_WHOSE_ORDER_NAME']) ? $r['PROPERTY_WHOSE_ORDER_NAME'] : $r['PROPERTY_PAYS_VALUE'];?></td>
                    <td><?=$r['PROPERTY_CITY_SENDER_NAME'];?></td>
                    <td><?=$r['PROPERTY_COMPANY_SENDER_VALUE'];?></td>
                    <td><?=$r['PROPERTY_CITY_RECIPIENT_NAME'];?></td>
                    <td><?=$r['PROPERTY_COMPANY_RECIPIENT_VALUE'];?></td>
                    <td><?=$r['PROPERTY_PLACES_VALUE'];?></td>
                    <td><?=WeightFormat($r['PROPERTY_WEIGHT_VALUE'], false);?></td>
                    <td><?=WeightFormat($r['PROPERTY_OB_WEIGHT'],false);?></td>
                    <td width="20"><?=$r['state_icon'];?></td>
                    <td><?=$r['state_text'];?></td>
                    <td><?=$r['Manager'];?></td>
                    <!-- <td></td>-->
                    <td data-halign="center" data-align="center" data-valign="center">
                        <?
                        $obElement = CIBlockElement::GetByID($r['ID']);
                        if($arEl = $obElement->GetNext())
                        {
                            $rsUser = CUser::GetByID($arEl["CREATED_BY"]);
                            $arUser = $rsUser->Fetch();
                            $Property_creator_name = $arUser["NAME"]." ".$arUser["LAST_NAME"];
                        }
                        ?>
                        <? echo $Property_creator_name; ?>
                    </td>
                    <td><a href="<?=$arParams['LINK'];?>?mode=add&copyfrom=<?=$r['ID'];?>&copy=Y"><span class="glyphicon glyphicon-copy" aria-hidden="true"></span></a></td>
                </tr>
                <?
                $itogo  = $itogo  + $r['PROPERTY_RATE_VALUE'];?>
                <!--модальное окно удаления-->

                <!-- Modal Удалить накладную -->
                <div class="modal fade" id="myModal_<?=$r['ID']?>" tabindex="-1" role="dialog"
                     aria-labelledby="myModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"
                                        aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <h4 class="modal-title" >Удалить накладную <?=$r['NAME']?>?</h4>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                                <a type="button" class="btn btn-primary" href="/index.php?mode=delone&n=<?=$r['ID'];?>&name=<?=$r['NAME'];?>">Удалить</a>
                            </div>
                        </div>
                    </div>
                </div>

            <?   }
            foreach ($arResult['ARCHIVE'] as $r)
            {

                /* --- */
                //	echo '<!-- <pre> 5::';
                //		print_r($r);
                //	echo '</pre> -->';
                /* --- */


                ?>
                <tr class="<?=$r['ColorRow'];?>">
                    <!--<td width="20"></td>-->
                    <td data-halign="center" data-align="center" data-valign="center">
                        <?if (strlen(trim($r['NAME']))):?>
                            <a href="<?=$arParams['LINK'];?>?mode=invoice1c_print&f001=<?=$r['NAME'];?>&print=Y" target="_blank">
                                <span class="glyphicon glyphicon-print" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Печать накладной"></span>
                            </a>
                        <?php endif;?>
                    </td>
                    <td data-halign="center" data-align="center" data-valign="center">
                        <?if (strlen(trim($r['NAME']))):?>
                            <a href="<?=$arParams['LINK']?>?mode=invoice1c_modal&f001=<?=$r['NAME']?>&pdf=Y"
                               data-toggle="modal" data-target="#modal_inv1c_<?=$r['NAME']?>">
                                <span class="glyphicon glyphicon-zoom-in" aria-hidden="true" data-toggle="tooltip"
                                      data-placement="right" title="Просмотр накладной"></span>
                            </a>
                        <?php endif;?>
                    </td>

                    <td>
                        <?if(!empty($r['SCAN_DOCS_PATH'])):?>
                            <a  data-toggle="modal"
                                data-target="#modal_scan_<?=$r['ID'];?>" href="">
                                <span  aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Скачать сканы"
                                              style="cursor:pointer" class="glyphicon glyphicon-paperclip">
                                </span>
                            </a>
                        <?php endif;?>
                    </td>

                    <td><?=$r['NAME'];?></td>
                    <td></td>
                    <td><?=$r['start_date'];?></td>
                    <td><?=$r['ZakazName'];?></td>
                    <td><?=$r['PROPERTY_CITY_SENDER_NAME'];?></td>
                    <td><?=$r['PROPERTY_COMPANY_SENDER_VALUE'];?></td>
                    <td><?=$r['PROPERTY_CITY_RECIPIENT_NAME'];?></td>
                    <td><?=$r['PROPERTY_COMPANY_RECIPIENT_VALUE'];?></td>
                    <td><?=$r['PROPERTY_PLACES_VALUE'];?></td>
                    <td><?=WeightFormat($r['PROPERTY_WEIGHT_VALUE'], false);?></td>
                    <td><?=WeightFormat($r['PROPERTY_OB_WEIGHT'],false);?></td>
                    <td width="20">
                        <a href="" data-toggle="modal" data-target="#modal_tr_<?=$r['ID'];?>">
                            <?=$r['state_icon'];?>
                        </a>
                    </td>
                    <td><?=$r['state_text'];?></td>
                    <td><?=$r['Manager'];?></td>
                    <!--<td></td>-->
                    <td data-halign="center" data-align="center" data-valign="center">
                        <?
                        $obElement = CIBlockElement::GetByID($r['ID']);
                        if($arEl = $obElement->GetNext())
                        {
                            $rsUser = CUser::GetByID($arEl["CREATED_BY"]);
                            $arUser = $rsUser->Fetch();
                            $Property_creator_name = $arUser["NAME"]." ".$arUser["LAST_NAME"];
                        }
                        ?>
                        <? echo $Property_creator_name; ?>
                    </td>
                    <td>
                        <?if (intval($r['ID_SITE']) > 0) :?>
                            <a href="<?=$arParams['LINK'];?>?mode=add&copyfrom=<?=$r['ID_SITE'];?>&copy=Y">
                                <span class="glyphicon glyphicon-copy" aria-hidden="true" data-toggle="tooltip" data-placement="left" title="Копировать"></span>
                            </a>
                        <?endif;?>
                    </td>
                </tr>
                <?
                $itogo  = $itogo  + $r['PROPERTY_RATE_VALUE'];
                if(!empty($r ['SCAN_DOCS_PATH'])):?>
                    <div class="modal fade" id="modal_scan_<?=$r['ID'];?>" tabindex="-1" role="dialog"
                         aria-labelledby="myModalLabel">
                        <div class="modal-dialog" role="document">

                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal"
                                            aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h3>№ <?=$r['NAME']?></h3>

                                </div>
                                <div style="padding-bottom: 20px;" class="row">
                                    <?$count = count($r ['SCAN_DOCS_PATH']);?>
                                    <h4 style="margin-left: 32px;">Скачать сканы, прикрепленные к документу (<?=$count;?> шт. )</h4>
                                    <ul>
                                        <?
                                        //dump($r ['SCAN_DOCS_PATH']);
                                        foreach($r ['SCAN_DOCS_PATH'] as $key=>$value):?>
                                            <?$ext = getExtensionPath($value);?>
                                            <li style="list-style: decimal ">
                                                <div class="col-md-12">
                                                    <a target="_blank" href="http://<?=$value;?>">
                                                        Скачать скан накладной (<?=$ext;?>)
                                                    </a>
                                                </div>
                                            </li>
                                        <?endforeach;?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                <?endif;
            }
            ?>
            </tbody>
        </table>
        <p>Всего накладных: <?=(count($arResult['REQUESTS'])+count($arResult['ARCHIVE']));?></p>
        <?
        if (count($arResult['REQUESTS']) > 0)
        {

            ?>
            <div class="btn-group" role="group" aria-label="...">
                <?if ($arResult['ADMIN_AGENT']):?>
                    <input type="submit" name="accept" value="Принять и сформировать манифест" class="btn btn-primary">
                <?endif;?>
                <input type="submit" name="prints" value="Распечатать накладные" class="btn btn-default">
                <input type="submit" name="delete" value="Удалить накладные" class="btn btn-default">
            </div>
            </form>
            <?
        }
        ?>
        </div>
        </div>
        <? if ($arResult['AGENT']['PROPERTY_SHOW_LIMITS_VALUE'] == 1) : ?>
        <div class="row">
            <div class="col-md-3"><i>Итого за месяц: <strong><?=number_format($itogo, 2, ',', ' ');?></strong></i></div>
            <?
            if ($arResult['LIMITS_OF_BRANCHES'])
            {
                ?>
                <div class="col-md-3 text-center">
                    <i>Итого за <?=$arResult['QW_TEXT'];?> квартал: <strong><?=number_format($arResult['All_SPENT'], 2, ',', ' ');?></strong></i> <span class="label <?=$arResult['LABEL_CLASS'];?>"><?=$arResult['All_PERSENT'];?></span></div>
                <div class="col-md-3 text-center"><i>Лимит за <?=$arResult['QW_TEXT'];?> квартал:<strong><?=number_format($arResult['All_LIMIT'], 2, ',', ' ');?></strong></i></div>
                <div class="col-md-3 text-right"><i>Осталось за <?=$arResult['QW_TEXT'];?> квартал:<strong><?=number_format($arResult['All_LEFT'], 2, ',', ' ');?></strong></i></div>
                <?
            }
            ?>
        </div>
    <?
    endif;
        foreach ($arResult['REQUESTS'] as $r)
        {
            ?>
            <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" id="modal_<?=$r['ID'];?>" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                    </div>
                </div>
            </div>
            <?
        }
        foreach ($arResult['ARCHIVE'] as $r)
        {
            ?>
            <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" id="modal_inv1c_<?=$r['NAME'];?>" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                    </div>
                </div>
            </div>
            <div class="modal fade" tabindex="-1" role="dialog" id="modal_tr_<?=$r['ID'];?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12" class="text-right">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <p>&nbsp;</p>
                                    <table cellpadding="5" bordercolor="#ccc" border="1" width="600" style=" border-collapse: collapse;" class="show_tracks table table-striped table-hover">
                                        <thead>
                                        <tr>
                                            <th colspan="3">Трек отправления <?=$r['NAME'];?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?
                                        foreach ($r['Events'] as $ev)
                                        {
                                            if (in_array($ev['InfoEvent'], $arResult['HIDE_EVENTS']) && ($ev['Event'] == 'Исключительная ситуация!'))
                                            {}
                                            else
                                            {
                                                ?>
                                                <tr>
                                                    <td width="30%"><?=$ev['Date'];?></td>
                                                    <td width="35%"><?=$ev['Event'];?></td>
                                                    <td width="35%"><?=$ev['InfoEvent'];?></td>
                                                </tr>
                                                <?
                                            }
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                    <p>&nbsp;</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?
        }
    }
    else
    {
        if (intval($arResult['CURRENT_CLIENT']) == 0)
        {
            ?>
            <div class="alert alert-dismissable alert-warning fade in" role="alert">Не выбран агент</div>
            <?
        }
        else
        {
            ?>
            <div class="alert alert-dismissable alert-warning fade in" role="alert">Список накладных за выбранный период пуст</div>
            <?
        }
    }
}
?>