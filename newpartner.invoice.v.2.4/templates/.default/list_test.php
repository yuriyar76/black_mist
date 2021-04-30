<?php
echo "<h1>�������� �����</h1>";
if($USER->isAdmin()){
    $start = microtime(true);
    AddToLogs('test_logs', ['time_start' => $start, 'mess' => '������ ���������� ������� � �������']);
}
?>
<script type="text/javascript">

    <?if( !$_SESSION['�ontractEndDate']):?>
    $(document).ready(function(){
        $('.maskdate').mask('99.99.9999');
        $('.bootstrap-table .fixed-table-toolbar').append('<div class="pull-left"><a href="/services/" class="btn btn-success">' +
            '<span class="glyphicon glyphicon-bell" aria-hidden="true"></span> ������� �������</a></div>');

    });
    <?endif;?>
    $(function () {
        $(window).resize(function () {
            $('#tableId').bootstrapTable('resetView');
        });
    });

    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
        $('.masktime').mask('99:99');
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

    function checkAll()
    {
        let allids = $('#allids');

        if(allids[0].checked === true){
            $('td input[type=checkbox]').each(function() {
                this.checked = true;
            });
        }else{
            $('td input[type=checkbox]').each(function() {
                this.checked = false;
            });
        }

    }

    function checkScan(idscan)
    {
        $elscan = $('#sdoc_'+ idscan);
        $el = $('#check_'+ idscan);
        if($el.prop("checked")){
            $elscan.prop('checked', true);
        }else{
            $elscan.prop('checked', false);
        }

    }

    function ChangePeriod()
    {
        var y = $("select#year").val();
        var m = $("select#month").val();
        location.href = '<?=$arParams['LINK'];?>index.php?ChangePeriod=Y&year='+y+'&month='+m;
    }

    function ChangePeriodNew()
    {
        $('#input-group-list-from-date').removeClass('has-error');
        $('#input-group-list-to-date').removeClass('has-error');
        var datefrom = $("input#list-from-date").val();
        var dateto = $("input#list-to-date").val();
        if ((dateto.length > 0) && (datefrom.length > 0))
        {
            location.href = '<?=$arParams['LINK'];?>index.php?ChangePeriod=Y&datefrom='+datefrom+'&dateto='+dateto;
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
        location.href = '<?=$arParams['LINK'];?>index.php?ChangeClient=Y&client='+cl;
    }

    function ChangeBranch()
    {
        var br = $("select#branch").val();
        location.href = '<?=$arParams['LINK'];?>index.php?ChangeBranch=Y&branch='+br;
    }
    <?php
    if (($_GET['openprint'] === 'Y') && ((int)$_GET['id'] > 0))
    {
    ?>
    $(document).ready(function() {
        window.open('<?=$arParams['LINK'];?>index.php?mode=print&id=<?=(int)$_GET['id'];?>&print=Y');
        //location.href = '<?=$arParams['LINK'];?>index.php';
    });
    <?php
    }
    if ((isset($_POST['prints'])) && (count($_POST['ids']) > 0)):

    if(isset($_POST['scandcs'])):?>

    $(document).ready(function() {
        window.open('<?=$arParams['LINK'];?>index.php?mode=prints&ids=<?=implode(',',$_POST['ids']);?>&scandocs=<?=implode(",",$_POST['scandcs']);?>&print=Y');
    });

    <?php else:?>
    $(document).ready(function() {

        window.open('<?=$arParams['LINK'];?>index.php?mode=prints&ids=<?=implode(',',$_POST['ids']);?>&print=Y');
    });
    <?php
    endif;
    endif;

    if ((isset($_POST['prints_mini'])) && (count($_POST['ids']) > 0)):
    ?>
    $(document).ready(function() {
        window.open('<?=$arParams['LINK'];?>index.php?mode=prints_mini&id=<?=implode(',',$_POST['ids']);?>&print=Y');
    });
    <?php
    endif;
    ?>
</script>

<?php


if (count($arResult["ERRORS"]) > 0)
{
    ?>
    <div class="alert alert-dismissable alert-danger fade in" role="alert">
        <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">X</span><span
                    class="sr-only">�������</span></button>
        <?=implode('</br>',$arResult["ERRORS"]);?>
    </div>
    <?php
}
if (count($arResult["MESSAGE"]) > 0)
{
    ?>
    <div class="alert alert-dismissable alert-success fade in" role="alert">
        <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">X</span><span
                    class="sr-only">�������</span></button>
        <?=implode('</br>',$arResult["MESSAGE"]);?>
    </div>
    <?php
}
if (count($arResult["WARNINGS"]) > 0)
{
    ?>
    <div class="alert alert-dismissable alert-warning fade in" role="alert">
        <button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">X</span>
            <span class="sr-only">�������</span></button>
        <?=implode('</br>',$arResult["WARNINGS"]);?>
    </div>
    <?php
}

if ($arResult['OPEN'])
{?>
    <?php
    /* json ��� ������ ������� ����������� */
    if($arResult['CURRENT_CLIENT'] == 56103010){
        $invselect = [];
        foreach($arResult['REQUESTS'] as $key=>$value){
            $invselect[$arResult['REQUESTS'][$key]['NAME']]['NAME']  =
                $arResult['REQUESTS'][$key]['NAME']; // ����� ����
            $invselect[$arResult['REQUESTS'][$key]['NAME']]['DATE_CREATE']  =
                $arResult['REQUESTS'][$key]['DATE_CREATE']; // ���� ������������
            $invselect[$arResult['REQUESTS'][$key]['NAME']]['state_text']  =
                $arResult['REQUESTS'][$key]['state_text']; // ������
            $invselect[$arResult['REQUESTS'][$key]['NAME']]['PROPERTY_CITY_RECIPIENT_NAME']  =
                $arResult['REQUESTS'][$key]['PROPERTY_CITY_RECIPIENT_NAME']; // ����� ����������
            $invselect[$arResult['REQUESTS'][$key]['NAME']]['PROPERTY_NAME_RECIPIENT_VALUE']  =
                $arResult['REQUESTS'][$key]['PROPERTY_NAME_RECIPIENT_VALUE']; // ��� ����������
            $invselect[$arResult['REQUESTS'][$key]['NAME']]['PROPERTY_COMPANY_RECIPIENT_VALUE']  =
                $arResult['REQUESTS'][$key]['PROPERTY_COMPANY_RECIPIENT_VALUE']; // �������� ����������


            $invselect[$arResult['REQUESTS'][$key]['NAME']]['PROPERTY_CITY_SENDER_NAME']  =
                $arResult['REQUESTS'][$key]['PROPERTY_CITY_SENDER_NAME'];
            $invselect[$arResult['REQUESTS'][$key]['NAME']]['PROPERTY_COMPANY_SENDER_VALUE']  = $arResult['REQUESTS'][$key]['PROPERTY_COMPANY_SENDER_VALUE'];



            $invselect[$arResult['REQUESTS'][$key]['NAME']]['PROPERTY_PLACES_VALUE']  = $arResult['REQUESTS'][$key]['PROPERTY_PLACES_VALUE'];
            $invselect[$arResult['REQUESTS'][$key]['NAME']]['PROPERTY_WEIGHT_VALUE']  = $arResult['REQUESTS'][$key]['PROPERTY_WEIGHT_VALUE'];
            $invselect[$arResult['REQUESTS'][$key]['NAME']]['PROPERTY_OB_WEIGHT']  = $arResult['REQUESTS'][$key]['PROPERTY_OB_WEIGHT'];
        }

    }

    /* � ��������� */
    /* ������ ��������� � ��������� ��� ������ � ������ � ������ (��������� 56280706)*/
    if($arResult['CURRENT_CLIENT'] == 56280706) {
        $numselect = [];
        foreach ($arResult['REQUESTS'] as $key => $value) {
            if ($value['PROPERTY_WITH_RETURN_VALUE'] == 1) {
                $numselect[$arResult['REQUESTS'][$key]['NAME']]['NAME'] = $arResult['REQUESTS'][$key]['NAME'];
                $numselect[$arResult['REQUESTS'][$key]['NAME']]['state_text'] = $arResult['REQUESTS'][$key]['state_text'];
                $numselect[$arResult['REQUESTS'][$key]['NAME']]['DATE_CREATE'] = $arResult['REQUESTS'][$key]['DATE_CREATE'];
                $numselect[$arResult['REQUESTS'][$key]['NAME']]['PROPERTY_CITY_SENDER_NAME'] = $arResult['REQUESTS'][$key]['PROPERTY_CITY_SENDER_NAME'];
                $numselect[$arResult['REQUESTS'][$key]['NAME']]['PROPERTY_COMPANY_SENDER_VALUE'] = $arResult['REQUESTS'][$key]['PROPERTY_COMPANY_SENDER_VALUE'];
                $numselect[$arResult['REQUESTS'][$key]['NAME']]['PROPERTY_CITY_RECIPIENT_NAME'] = $arResult['REQUESTS'][$key]['PROPERTY_CITY_RECIPIENT_NAME'];
                $numselect[$arResult['REQUESTS'][$key]['NAME']]['PROPERTY_COMPANY_RECIPIENT_VALUE'] = $arResult['REQUESTS'][$key]['PROPERTY_COMPANY_RECIPIENT_VALUE'];
                $numselect[$arResult['REQUESTS'][$key]['NAME']]['PROPERTY_NAME_RECIPIENT_VALUE'] = $arResult['REQUESTS'][$key]['PROPERTY_NAME_RECIPIENT_VALUE'];
                $numselect[$arResult['REQUESTS'][$key]['NAME']]['PROPERTY_PLACES_VALUE'] = $arResult['REQUESTS'][$key]['PROPERTY_PLACES_VALUE'];
                $numselect[$arResult['REQUESTS'][$key]['NAME']]['PROPERTY_WEIGHT_VALUE'] = $arResult['REQUESTS'][$key]['PROPERTY_WEIGHT_VALUE'];
                $numselect[$arResult['REQUESTS'][$key]['NAME']]['PROPERTY_OB_WEIGHT'] = $arResult['REQUESTS'][$key]['PROPERTY_OB_WEIGHT'];
            }
        }
        foreach ($arResult['ARCHIVE'] as $key => $value) {
            $arFilter = [
                "NAME" => trim($value['NAME']),
                "ACTIVE" => "Y"
            ];
            $arSelect = [
                "ID", "NAME", "PROPERTY_WITH_RETURN"
            ];

            $resArrSel = GetInfoArr(false, false, 83, $arSelect, $arFilter);
            if ($resArrSel['PROPERTY_WITH_RETURN_VALUE'] == 1) {
                $arResult['ARCHIVE'][$key]['PROPERTY_WITH_RETURN_VALUE'] = 1;
                $numselect[$arResult['ARCHIVE'][$key]['NAME']]['NAME'] = $arResult['ARCHIVE'][$key]['NAME'];
                $numselect[$arResult['ARCHIVE'][$key]['NAME']]['state_text'] = $arResult['ARCHIVE'][$key]['state_text'];
                $numselect[$arResult['ARCHIVE'][$key]['NAME']]['DATE_CREATE'] = $arResult['ARCHIVE'][$key]['DATE_CREATE'];
                $numselect[$arResult['ARCHIVE'][$key]['NAME']]['PROPERTY_CITY_SENDER_NAME'] = $arResult['ARCHIVE'][$key]['PROPERTY_CITY_SENDER_NAME'];
                $numselect[$arResult['ARCHIVE'][$key]['NAME']]['PROPERTY_COMPANY_SENDER_VALUE'] = $arResult['ARCHIVE'][$key]['PROPERTY_COMPANY_SENDER_VALUE'];
                $numselect[$arResult['ARCHIVE'][$key]['NAME']]['PROPERTY_CITY_RECIPIENT_NAME'] = $arResult['ARCHIVE'][$key]['PROPERTY_CITY_RECIPIENT_NAME'];
                $numselect[$arResult['ARCHIVE'][$key]['NAME']]['PROPERTY_COMPANY_RECIPIENT_VALUE'] = $arResult['ARCHIVE'][$key]['PROPERTY_COMPANY_RECIPIENT_VALUE'];
                $numselect[$arResult['ARCHIVE'][$key]['NAME']]['PROPERTY_NAME_RECIPIENT_VALUE'] = $arResult['ARCHIVE'][$key]['PROPERTY_NAME_RECIPIENT_VALUE'];
                $numselect[$arResult['ARCHIVE'][$key]['NAME']]['PROPERTY_PLACES_VALUE'] = $arResult['ARCHIVE'][$key]['PROPERTY_PLACES_VALUE'];
                $numselect[$arResult['ARCHIVE'][$key]['NAME']]['PROPERTY_WEIGHT_VALUE'] = $arResult['ARCHIVE'][$key]['PROPERTY_WEIGHT_VALUE'];
                $numselect[$arResult['ARCHIVE'][$key]['NAME']]['PROPERTY_OB_WEIGHT'] = $arResult['ARCHIVE'][$key]['PROPERTY_OB_WEIGHT'];
            }
        }
        if (!empty($numselect)) {   // ����� ��� ��������� � ��������� ���� � ������
            $viewcolv = "Y";
            $numselect = convArrayToUTF($numselect);
            // AddToLogs('return',  $numselect);
            $arResult['ARCHIVE_REPORTV_STR_JSON'] = json_encode($numselect);
        }
    }
    /* � ��������� end  */


    if($USER->isAdmin()){
        $finish = microtime(true);
        $delta = $finish - $start;
        AddToLogs('test_logs', ['time_1' => $delta,
            'mess' => '����� �� ������ ������']);

        $start = microtime(true);
        AddToLogs('test_logs', ['time_start' => $start, 'mess' => '������ ������ �������']);
    }
    ?>
    <div class="row">
        <div class="col-md-3">
            <?php if ($arResult['CURRENT_CLIENT'] > 0):?>
                <div style="display:flex; flex-direction: row; justify-content: start; margin-left: 5px;" class="btn-group">
                    <?php if ((count($arResult['REQUESTS']) > 0) ||  (count($arResult['ARCHIVE']) > 0)) :?>
                    <form action="<?=$arParams['LINK'];?>index.php?mode=list_xls&pdf=Y" method="post" name="xlsform"
                          target="_blank">
                        <input type="hidden" name="DATA"
                               value="<?=htmlspecialchars($arResult['ARCHIVE_STR_JSON'],ENT_COMPAT);?>">
                        <?php endif;?>
                        <?php if( !$_SESSION['�ontractEndDate']):?>
                            <div class="btn-group" role="group">
                                <a href="<?=$arParams['LINK'];?>index.php?mode=add" class="btn btn-warning testwarn"
                                   id="new_btn"><span class="glyphicon glyphicon-file" aria-hidden="true"></span>
                                    ����� ���������
                                </a>
                            </div>
                            <div class="btn-group" role="group">
                                <a href="<?=$arParams['LINK'];?>index.php" class="btn btn-default" data-toggle="tooltip"
                                   data-placement="bottom"  title="�������� ������ ���������">
                                    <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
                                </a>
                            </div>
                            <?php if ((count($arResult['REQUESTS']) > 0) ||  (count($arResult['ARCHIVE']) > 0)) :?>
                                <div class="btn-group" role="group">
                                    <button type="submit" class="btn btn-default" data-toggle="tooltip"
                                            data-placement="bottom" title="������� ������ ���������">
                                        <span class="glyphicon glyphicon-cloud-download" aria-hidden="true"></span>
                                    </button>
                                </div>
                            <?php endif;?>
                            <div class="btn-group" role="group">
                                <a href="<?=$arParams['LINK'];?>index.php?mode=upload" class="btn btn-default"
                                   data-toggle="tooltip"
                                   data-placement="bottom"  title="��������� ������ ���������">
                                    <span class="glyphicon glyphicon-cloud-upload" aria-hidden="true"></span>
                                </a>
                            </div>
                        <?php endif;?>
                        <?php if ((count($arResult['REQUESTS']) > 0) ||  (count($arResult['ARCHIVE']) > 0)) :?>
                    </form>
                <?php endif;?>
                    <?php
                    // ����� ��� ������� ����������� (56103010)
                    if(!$_SESSION['�ontractEndDate'] && ($USER->isAdmin() || $arResult['CURRENT_CLIENT'] == 56103010)):?>
                        <form action="<?=$arParams['LINK'];?>index.php?mode=report_xls" method="post" name="xlsreport"
                              target="_blank">
                            <input type="hidden" name="DATA_REPORT"
                                   value="<?=htmlspecialchars($arResult['ARCHIVE_REPORT_STR_JSON'],ENT_COMPAT);?>">
                            <div class="btn-group" role="group">
                                <button type="submit" class="btn btn-default" data-toggle="tooltip" data-placement="bottom"
                                        title="������� �����">
                                    <i style="font-weight: 600;" class="far fa-file-excel"></i>
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                    <?php
                    // ����� ��� ���������� ������� ��������� � ���������
                    if(!$_SESSION['�ontractEndDate'] && !empty($arResult['ARCHIVE_REPORTV_STR_JSON'])):?>
                        <form action="<?=$arParams['LINK'];?>api/reprt.php?mode=reportv_xls" method="post"
                              name="xlsvreport" target = "_blank">
                            <input type="hidden" name="DATA_REPORTV"
                                   value="<?=htmlspecialchars($arResult['ARCHIVE_REPORTV_STR_JSON'],ENT_COMPAT);?>">
                            <div class="btn-group" role="group">
                                <button type="submit" class="btn btn-default" data-toggle="tooltip" data-placement="bottom"
                                        title="� ���������">
                                    <i style="font-weight: 600;" class="far fa-file-excel"></i>
                                </button>
                            </div>
                        </form>

                    <?php endif;?>
                </div>
            <?php endif;?>

        </div>
        <div class="client-filterform col-md-9 text-right">
            <form action="" method="get" name="filterform" class="form-inline">
                <?php
                if ($arResult['LIST_OF_CLIENTS'])
                {
                    ?>
                    <div class="form-group">
                        <select name="client" size="1" class="form-control selectpicker" id="client" onChange="ChangeClient();" data-live-search="true" data-width="auto">
                            <option value="0"></option>
                            <?php
                            foreach ($arResult['LIST_OF_CLIENTS'] as $k => $v)
                            {
                                $s = ($arResult['CURRENT_CLIENT'] == $k) ? ' selected' : '';
                                ?>
                                <option value="<?=$k;?>"<?=$s;?>><?=$v;?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                    <?php
                }
                if ($arResult['USER_IN_BRANCH'])
                {
                    ?>
                    <div class="form-group">
                        <h3 style="margin:-4px 0 0;">
                            <span class="label label-success">������: <?=$arResult['LIST_OF_BRANCHES'][$arResult['CURRENT_BRANCH']];?></span>
                            <?php  if ($arResult['AGENT']["PROPERTY_TYPE_WORK_BRANCHES_ENUM_ID"] == 301) : ?>
                                <a href="/choice-branch/" class="btn btn-default" title="������� ������ ������">
                                    <span class="glyphicon glyphicon-retweet" aria-hidden="true"></span></a>
                            <?php  endif;?>
                        </h3>
                    </div>
                    <?php
                }
                else
                {
                    if ($arResult['LIST_OF_BRANCHES'])
                    {
                        ?>
                        <div class="form-group">
                            <select name="branch" size="1" class="form-control selectpicker" id="branch"
                                    onChange="ChangeBranch();" data-live-search="true" data-width="auto">
                                <option value="0">���</option>
                                <?php
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
                        <?php
                    }
                }
                ?>
                <div class="form-group">
                    <div class="input-group" id="input-group-list-from-date">
                        <input type="text" class="form-control maskdate" aria-describedby="basic-addon1"
                               name="dateperiodfrom" placeholder="��.��.����" value="<?=$arResult['LIST_FROM_DATE'];?>" onChange="ChangePeriodNew();" id="list-from-date">
                        <span class="input-group-addon" id="basic-addon1">
							<?php
                            $APPLICATION->IncludeComponent(
                                "bitrix:main.calendar",
                                ".default",
                                [
                                    "SHOW_INPUT" => "N",
                                    "FORM_NAME" => "",
                                    "INPUT_NAME" => "dateperiodfrom",
                                    "INPUT_NAME_FINISH" => "",
                                    "INPUT_VALUE" => "",
                                    "INPUT_VALUE_FINISH" => false,
                                    "SHOW_TIME" => "N",
                                    "HIDE_TIMEBAR" => "Y",
                                    "INPUT_ADDITIONAL_ATTR" => ''
                                ],
                                false
                            );
                            ?>
						</span>
                    </div>
                </div>
                <div class="dash form-group ">&nbsp;&mdash;&nbsp;</div>
                <div class="form-group">
                    <div class="input-group" id="input-group-list-to-date">
                        <input type="text" class="form-control maskdate" aria-describedby="basic-addon2" name="dateperiodto" placeholder="��.��.����" value="<?=$arResult['LIST_TO_DATE'];?>" onChange="ChangePeriodNew();" id="list-to-date">
                        <span class="input-group-addon" id="basic-addon2">
							<?php
                            $APPLICATION->IncludeComponent(
                                "bitrix:main.calendar",
                                ".default",
                                [
                                    "SHOW_INPUT" => "N",
                                    "FORM_NAME" => "",
                                    "INPUT_NAME" => "dateperiodto",
                                    "INPUT_NAME_FINISH" => "",
                                    "INPUT_VALUE" => "",
                                    "INPUT_VALUE_FINISH" => false,
                                    "SHOW_TIME" => "N",
                                    "HIDE_TIMEBAR" => "Y",
                                    "INPUT_ADDITIONAL_ATTR" => ''
                                ],
                                false
                            );
                            ?>
						</span>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">&nbsp;</div>
    </div>
    <div class="row">
    <div class="col-md-12">

    <?php
    if($USER->isAdmin()){
        $finish = microtime(true);
        $delta = $finish - $start;
        AddToLogs('test_logs', ['time_1' => $delta,
            'mess' => '����� �� ������ ������ �������']);

        $start = microtime(true);
        AddToLogs('test_logs', ['time_start' => $start, 'mess' => '������ ������ ���������� �������']);
    }
    if ((count($arResult['REQUESTS']) > 0) ||  (count($arResult['ARCHIVE']) > 0))
    {?>
        <form id="call_courier_form" action="?call_courier=Y" method="post"></form>
        <form  action="" method="POST">
            <input type="hidden" name="rand" value="<?=random_int(100000,999999);?>">
            <input type="hidden" name="key_session" value="key_session_<?=random_int(100000,999999);?>">
            <?php
             $itogo = 0;
            ?>
            <table class="table table-condensed table-hover" data-toggle="table" data-show-columns="true"
                   data-search="true" data-select-item-name="toolbar1" data-height="600" id="tableId" <?/*?> data-sort-name="date" data-sort-order="desc"<?*/?>>
                <thead>
                <tr>
                    <th width="20" data-field="column1" data-switchable="false">
                        <span class="glyphicon glyphicon-print"></span>
                        <input id="allids" onClick="return checkAll()"  type="checkbox" name="allids">
                    </th>

                    <th><span aria-hidden="true" data-toggle="tooltip"
                              data-placement="right" title="������� PDF">
                            <i style="color:red" class="far fa-file-pdf"></i>
                        </span>
                    </th>

                    <th width="20" data-field="column2" data-switchable="false">
                        <span class="glyphicon glyphicon-print" aria-hidden="true" data-toggle="tooltip"
                              data-placement="right" title="������ ���������"></span>
                    </th>
                    <?php
                    // ����� �� ������ ����������� � �������� ��� ���������� � ������� ���
                    if( $arResult['CURRENT_CLIENT'] == 56280706 ||
                        $arResult['CURRENT_CLIENT'] == 56389269 ||
                        $arResult['CURRENT_CLIENT'] == 56389270 ||
                        $arResult['CURRENT_CLIENT'] == 56389272 ||
                        $arResult['CURRENT_CLIENT'] == 49540621 ||
                        $USER->isAdmin()):?>
                        <th width="20">
                            <span class="glyphicon glyphicon-bullhorn" aria-hidden="true"></span>
                        </th>
                    <?php endif;?>

                    <th width="20" data-field="column14" data-switchable="false">
                        <span class="glyphicon glyphicon-zoom-in" aria-hidden="true" data-toggle="tooltip"
                              data-placement="right" title="�������� ���������"></span>
                    </th>
                    <th width="20"  aria-hidden="true"
                        data-toggle="tooltip" data-placement="right"
                        title="������� ����� ���������">
                        <span class="glyphicon glyphicon-paperclip"> </span>
                    </th>

                    <?php
                    /* ����� ������ ��� ������� ����������� || $USER->isAdmin()*/
                    if($arResult['CURRENT_CLIENT'] == 56103010 ):?>
                        <th data-switchable="false" data-sortable="true">
                            <?=GetMessage('TABLE_HEAD_14');?>
                        </th>
                    <?php endif;?>
                    <?php if (count($arResult['REQUESTS']) > 0 ):?>
                        <th width="20" data-field="column22" data-switchable="false"></th>
                    <?php endif;?>
                    <th data-field="number" data-switchable="false" data-sortable="true">
                        <?=GetMessage('TABLE_HEAD_1');?>
                    </th>
                    <?php
                    // ��� ���������� ��������� � ������� ����������� (56103010)
                    if($arResult['CURRENT_CLIENT'] == 52254529 ||
                        $arResult['CURRENT_CLIENT'] == 56103010 || $USER->isAdmin()):?>
                        <th>
                            <?=GetMessage('SUMM_DEV');?>
                        </th>
                    <?php endif;?>
                    <?php if($arResult['CURRENT_CLIENT'] != 41478141):?>
                        <th>
                        <span  class="glyphicon glyphicon-bell" style="color: #555555; font-size: 14px; ">
                        </span>
                        </th>
                    <?php endif;?>
                    <th width="20" data-field="column15" data-switchable="false" data-sortable="false"></th>

                    <?php
                    // $arResult['CURRENT_CLIENT'] === '36015676' ||
                    if($arResult['CURRENT_CLIENT'] == 36015676 || $USER->isAdmin()):?>
                        <th>����������</th>
                    <?php endif;?>
                    <?php
                    // �����. ����� ���������� ������ ������
                    if($arResult['CURRENT_CLIENT'] == 41478141 ):?>
                        <th data-field="column19" data-sortable="true"
                            data-switchable="true" width="20">��. ����� ������.
                        </th>
                    <?php endif;?>
                    <th data-field="column4" data-sortable="true"><?=GetMessage('TABLE_HEAD_2');?></th>

                    <th data-field="date" data-sortable="true"><?=GetMessage('TABLE_HEAD_3');?></th>
                    <?php // echo "..."; ���������� ����� ������  PROPERTY_SHOW_HIDDEN_INN_NUMBER ?>
                    <?php if (($arResult['LIST_OF_BRANCHES']) && (!$arResult['USER_IN_BRANCH']))
                    {
                        ?>
                        <th data-field="column22" data-sortable="true">������</th>
                        <?php
                    }
                    ?>
                    <th data-field="column6" data-sortable="true"><?=GetMessage('TABLE_HEAD_4');?></th>
                    <th data-field="column7" data-sortable="true"><?=GetMessage('TABLE_HEAD_5');?></th>
                    <th data-field="column17" data-sortable="true"><?=GetMessage('TABLE_HEAD_13');?></th>
                    <th data-field="column8" data-sortable="true"><?=GetMessage('TABLE_HEAD_7');?></th>
                    <th data-field="column9" data-sortable="true"><?=GetMessage('TABLE_HEAD_6');?></th>
                    <th data-field="column16" data-sortable="true"><?=GetMessage('TABLE_HEAD_12');?></th>
                    <th data-field="column10"><?=GetMessage('TABLE_HEAD_8');?></th>
                    <th data-field="column11"><?=GetMessage('TABLE_HEAD_9');?></th>
                    <th data-field="column12"><?=GetMessage('TABLE_HEAD_10');?></th>
                    <th data-field="column13" data-sortable="true"><?=GetMessage('TABLE_HEAD_11');?></th>
                    <th data-field="column20" data-sortable="true" data-switchable="true" width="20">�������������</th>
                    <th data-field="column21" width="20"> </th>
                    <?php
                    if ($viewcolv === "Y"):?>
                        <th data-switchable="false" data-sortable="true">� ���������</th>
                    <?php endif;?>
                </tr>
                </thead>

                <tbody>
                <?php
                if($USER->isAdmin()){
                    $finish = microtime(true);
                    $delta = $finish - $start;
                    AddToLogs('test_logs', ['time_1' => $delta,
                        'mess' => '����� ������ ���������� �������']);

                    $start = microtime(true);
                    AddToLogs('test_logs', ['time_start' => $start, 'mess' => '������ ������ ������� ����� ���������� REQUESTS']);
                }

                $path_pdf = $_SERVER['DOCUMENT_ROOT'] . '/upload/pdf';

                // /var/www/admin/www/delivery-russia.ru/upload/pdf

                if (!empty($arResult['REQUESTS'])):
                    foreach ($arResult['REQUESTS'] as $r):
                        if(trim($r['PROPERTY_CALLING_COURIER_VALUE']) !== 'Y'){
                            $flag_cc = true;
                        }else{
                            $flag_cc = false;
                        }
                        ?>
                        <tr class="a1 <?=$r['ColorRow'];?>">
                            <td class="a1" width="20">
                                <?php
                                if ($r['PROPERTY_STATE_ENUM_ID'] == 257):?>
                                    <input type="checkbox" name="ids[]" value="<?=$r['ID'];?>">
                                <?php endif;?>
                            </td>
                            <td>
                                <a  href="/upload/pdf/<?=$r['NAME']?>.pdf"  target="_blank">
                                    <i  style="color:red" class="far fa-file-pdf"></i>
                                </a>
                            </td>
                            <td class="a2" data-halign="center" data-align="center" data-valign="center">
                                <?php
                                /* ��� ������� ������ */
                                if (($arResult['CURRENT_CLIENT'] == ID_SUKHOI) || ($arResult['CURRENT_CLIENT'] == ID_TEST)):?>
                                    <a href="/index.php?mode=printsukhoi&id=<?=$r['ID'];?>&printsukhoi=Y&print=Y" target="_blank">
                            	<span class="glyphicon glyphicon-print" aria-hidden="true" data-toggle="tooltip"
                                      data-placement="right" title="������ ���������"></span>
                                    </a>
                                <?php  else:?>
                                    <a href="/index.php?mode=print&id=<?=$r['ID'];?>&print=Y" target="_blank">
                            	<span class="glyphicon glyphicon-print" aria-hidden="true" data-toggle="tooltip"
                                      data-placement="right" title="������ ���������"></span>
                                    </a>
                                <?php  endif; ?>
                            </td>
                            <?php
                            // ����� �� ������ ����������� � �������� ��� ���������� � ������� ���
                            if( $arResult['CURRENT_CLIENT'] == 56280706 ||
                                $arResult['CURRENT_CLIENT'] == 56389269 ||
                                $arResult['CURRENT_CLIENT'] == 56389270 ||
                                $arResult['CURRENT_CLIENT'] == 56389272 ||
                                $arResult['CURRENT_CLIENT'] == 49540621 ||
                                $USER->isAdmin()):?>
                                <td></td>
                            <?php endif;?>
                            <td class="a3" data-halign="center" data-align="center" data-valign="center">
                                <?php
                                if ((($r['PROPERTY_STATE_ENUM_ID'] == 257) && (!$arResult['ADMIN_AGENT'])) ||
                                    ($arResult['CURRENT_CLIENT'] == ID_TEST)):?>
                                    <a href="/index.php?mode=edit&id=<?=$r['ID'];?>"><span class="glyphicon glyphicon-pencil"
                                                                                           aria-hidden="true" data-toggle="tooltip" data-placement="right"
                                                                                           title="�������������� ���������"></span>
                                    </a>
                                <?php else:?>
                                    <a href="<?=$arParams['LINK'];?>index.php?mode=invoice_modal&id=<?=$r['ID'];?>&pdf=Y"
                                       data-toggle="modal" data-target="#modal_<?=$r['ID'];?>">
                                	<span class="glyphicon glyphicon-zoom-in" aria-hidden="true" data-toggle="tooltip"
                                          data-placement="right" title="�������� ���������"></span>
                                    </a>
                                <?php endif;?>
                            </td>

                            <?php if($arResult['CURRENT_CLIENT'] != 41478141 ):?>
                                <td></td>
                            <?php endif;?>
                            <?php
                            /* ����� ������ ������� ����������� */
                            if($arResult['CURRENT_CLIENT'] == 56103010):?>
                                <td>
                                    <?=$r['CENTER_EXPENSES_NAME']?>

                                </td>
                            <?php endif;?>
                            <td class="a21" width="20">
                                <?php if($arResult['CURRENT_CLIENT'] != 41478141 ):?>
                                    <span style="cursor: pointer;" data-toggle="modal"
                                          data-target="#myModal_<?=$r['ID'];?>"
                                          class="glyphicon glyphicon-trash"></span>
                                <?php endif;?>
                            </td>
                            <td>
                                <?php if($arResult['CURRENT_CLIENT'] == 41478141 ):?>
                                    <span style="cursor: pointer;" data-toggle="modal"
                                          data-target="#myModal_<?=$r['ID'];?>"
                                          class="glyphicon glyphicon-trash"></span>
                                <?php endif;?>
                                <?php if($arResult['CURRENT_CLIENT'] != 41478141 ):?>
                                    <?=$r['NAME'];?>
                                <?php endif;?>

                            </td>
                            <?php
                            // ��� ���������� ��������� � ������� ����������� (56103010)
                            if( $arResult['CURRENT_CLIENT'] == 52254529 ||
                                $arResult['CURRENT_CLIENT'] == 56103010 || $USER->isAdmin()):
                                $client =  $arResult['CURRENT_CLIENT'];
                                ?>

                                <td>
                                    <?php
                                    $arFilter = [
                                        "NAME" => trim($r['NAME']),
                                        "ACTIVE" => "Y"
                                    ];
                                    $arSelect = [
                                        "ID","NAME","PROPERTY_SUMM_DEV"
                                    ];
                                    $resArr =  GetInfoArr(false, false, 83, $arSelect, $arFilter );

                                    if (!empty($resArr['PROPERTY_SUMM_DEV_VALUE'])){
                                        echo ($resArr['PROPERTY_SUMM_DEV_VALUE']) . ' ���.';
                                    }else{
                                        $arFilter = [
                                            "NAME" => $r['NAME']
                                        ];
                                        $arSelect = [
                                            "ID", "NAME", "PROPERTY_WEIGHT", "PROPERTY_TOTAL_GABWEIGHT",
                                            "PROPERTY_CITY_RECIPIENT"
                                        ];
                                        $resArr = GetInfoArr(false, false, 83, $arSelect, $arFilter);
                                        $total_weight = $resArr["PROPERTY_WEIGHT_VALUE"];
                                        $total_gabweight = $resArr["PROPERTY_TOTAL_GABWEIGHT_VALUE"];
                                        $city_recipient = $resArr['PROPERTY_CITY_RECIPIENT_VALUE'];
                                        $sum_dev = getSumDev($total_weight, $total_gabweight, $city_recipient, $client);
                                        if($sum_dev){
                                            CIBlockElement::SetPropertyValuesEx($resArr["ID"], false, [979=>$sum_dev]);
                                            echo $sum_dev. ' ���.';
                                        }
                                    }
                                    ?>
                                </td>
                            <?php endif;?>
                            <td>
                                <?php if($flag_cc && $arResult['CURRENT_CLIENT'] != 41478141 ):?>
                                    <span style="color: #f36104; font-size: 14px; cursor: pointer" data-toggle="modal"
                                          data-target="#myCallCurier_<?=$r['ID'];?>">
                                    <span class="glyphicon glyphicon-bell" aria-hidden="true" data-toggle="tooltip"
                                          data-placement="right" title="�������� ����� ������� �������.">
                                    </span>
                                </span>
                                <?php elseif(!$flag_cc && $arResult['CURRENT_CLIENT'] != 41478141):?>
                                <span style="color: #56363534; font-size: 14px; cursor: pointer">
                                    <span class="glyphicon glyphicon-bell" aria-hidden="true" data-toggle="tooltip"
                                          data-placement="right" title="������ ������">
                             </span>
                        <?php endif;?>
                                    <?php
                                    // �����. ����� ���������� ������ ������
                                    if ($arResult['CURRENT_CLIENT'] == 41478141):?>
                                        <?=$r['NAME'];?>
                                    <?php endif;?>
                            </td>
                            <td class="a4" width="20"><?=$r['state_icon'];?></td>

                            <?php
                            // $arResult['CURRENT_CLIENT'] === '36015676' ||
                            if($arResult['CURRENT_CLIENT'] == 36015676 || $USER->isAdmin()):?>
                                <td>
                                    <?php
                                    $arFilter = [
                                        "NAME" => $r['NAME'],
                                        "ACTIVE" => "Y"
                                    ];
                                    $arSelect = [
                                        "ID","NAME","PROPERTY_NOTE_36015676"
                                    ];
                                    $resArr =  GetInfoArr(false, false, 83, $arSelect, $arFilter );
                                    echo ($resArr['PROPERTY_NOTE_36015676_VALUE']);
                                    ?>
                                </td>
                            <?php endif;?>
                            <?php if($arResult['CURRENT_CLIENT'] == 41478141 ):?>
                                <td class="a5" width="20">
                                    <? // echo "..."; ���������� ����� ������  :: PROPERTY_SHOW_HIDDEN_INN_NUMBER ?>
                                    <?=$r['PROPERTY_INNER_NUMBER_CLAIM_VALUE'];?>
                                </td>
                            <?php endif;?>

                            <td class="a6"><?=$r['state_text'];?></td>

                            <td class="a7">
                                <?php
                                /* ������� ����������� �������� ���� �������� ���������  */
                                if($arResult['CURRENT_CLIENT'] == 56103010 || $USER->isAdmin()){
                                    echo $r['DATE_CREATE'];
                                }else{
                                    echo substr($r['DATE_CREATE'],0,10);
                                }
                                ?>

                            </td>
                            <?php
                            if (($arResult['LIST_OF_BRANCHES']) && (!$arResult['USER_IN_BRANCH']))
                            {
                                ?>
                                <td class="a20"><?=$r['PROPERTY_BRANCH_NAME'];?></td>
                                <?php
                            }
                            ?>
                            <td class="a8"><?=$r['PROPERTY_CITY_SENDER_NAME'];?></td>
                            <td class="a9"><?=$r['PROPERTY_COMPANY_SENDER_VALUE'];?></td>
                            <td class="a10"><?=$r['PROPERTY_NAME_SENDER_VALUE'];?></td>
                            <td class="a11"><?=$r['PROPERTY_CITY_RECIPIENT_NAME'];?></td>
                            <td class="a12"><?=$r['PROPERTY_COMPANY_RECIPIENT_VALUE'];?></td>
                            <td class="a13"><?=$r['PROPERTY_NAME_RECIPIENT_VALUE'];?></td>
                            <td class="a14"><?=$r['PROPERTY_PLACES_VALUE'];?></td>
                            <td class="a15"><?=WeightFormat($r['PROPERTY_WEIGHT_VALUE'], false);?></td>
                            <td class="a16"><?=WeightFormat($r['PROPERTY_OB_WEIGHT'],false);?></td>
                            <td class="a17"><?=WeightFormat($r['PROPERTY_RATE_VALUE'],false);?></td>
                            <?php
                            $obElement = CIBlockElement::GetByID($r['ID']);
                            if($arEl = $obElement->GetNext())
                            {
                                $rsUser = CUser::GetByID($arEl["CREATED_BY"]);
                                $arUser = $rsUser->Fetch();
                                $Property_creator_name = $arUser["NAME"]." ".$arUser["LAST_NAME"];
                            }
                            ?>
                            <td class="a18"> <?=$Property_creator_name?> </td>
                            <td class="a19">
                                <!-- ����������(1): (  <?=$r['PROPERTY_TYPE_PAYS_VALUE'];?> -- <?=$r['PROPERTY_PAYS_VALUE'];?> -- <?=$r['PROPERTY_WHOSE_ORDER_VALUE'];?>  )-->
                                <a href="<?=$arParams['LINK'];?>index.php?mode=add&copyfrom=<?=$r['ID'];?>&copy=Y"><span class="glyphicon glyphicon-copy" aria-hidden="true"></span></a>
                            </td>
                            <?php
                            if ($viewcolv === "Y"):?>
                                <td >
                                    <?php if($r['PROPERTY_WITH_RETURN_VALUE']){
                                        echo "�";
                                    }
                                    ?>
                                </td>
                            <?php endif;?>
                        </tr>
                        <?php
                        $itogo  = $itogo  + $r['PROPERTY_RATE_VALUE'];?>

                        <!-- Modal ������� ��������� -->
                        <div class="modal fade" id="myModal_<?=$r['ID']?>" tabindex="-1" role="dialog"
                             aria-labelledby="myModal_<?=$r['ID']?>">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close"><span aria-hidden="true">&times;</span></button>

                                    </div>
                                    <div class="modal-body">
                                        <h4 class="modal-title" >������� ��������� <?=$r['NAME']?>?</h4>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">������</button>
                                        <a type="button" class="btn btn-primary" href="/index.php?mode=delone&n=<?=$r['ID'];?>&name=<?=$r['NAME'];?>">�������</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal ����� ������� -->
                        <div class="modal fade" id="myCallCurier_<?=$r['ID']?>" tabindex="-1" role="dialog"
                             aria-labelledby="myCallCurier_<?=$r['ID']?>">
                            <div class="modal-dialog modal-md" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title" >����� ������� ��� ��������� <?=$r['NAME']?></h4>
                                    </div>
                                    <div class="modal-body">
                                        <div style="display: flex; flex-direction: row; align-items: center;
                                                justify-content: space-between; width: 100%">
                                            <div class="form-group">
                                                <label form="call_courier_form" for="list-from-date_<?=$r['ID']?>">
                                                    ������� �� ���� <small style="color:darkred">*������������ ����</small></label>
                                                <div class="input-group" id="input-group-list-from-date_<?=$r['ID']?>">

                                                    <input form="call_courier_form" type="text" class="form-control maskdate"
                                                           name="callcourierdate_<?=$r['ID']?>" placeholder="��.��.����"
                                                           id="list-from-date_<?=$r['ID']?>">
                                                    <span style="padding: 6px 12px!important;" class="input-group-addon">
                                       							<?php
                                                                $APPLICATION->IncludeComponent(
                                                                    "bitrix:main.calendar",
                                                                    ".default",
                                                                    array(
                                                                        "SHOW_INPUT" => "N",
                                                                        "FORM_NAME" => "call_courier_form",
                                                                        "INPUT_NAME" => "callcourierdate_".$r['ID'],
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
                                            <div class="form-group">

                                                <input form="call_courier_form" type="hidden" name="id_<?=$r['ID']?>" value="<?=$r['ID']?>">
                                                <input  form="call_courier_form" type="hidden" name="name_<?=$r['ID']?>" value="<?=$r['NAME']?>">
                                                <label  form="call_courier_form" for="callcourtime_from_<?=$r['ID']?>">����� ��:</label>
                                                <input  form="call_courier_form" style="width: 100px;" type="text" class="form-control masktime"
                                                        id="callcourtime_from_<?=$r['ID']?>" name="callcourtime_from_<?=$r['ID']?>"
                                                        placeholder="��:��" >
                                            </div>
                                            <div class="form-group">
                                                <label  form="call_courier_form" for="callcourtime_to_<?=$r['ID']?>">��:</label>
                                                <input  form="call_courier_form" style="width: 100px;" type="text" class="form-control masktime"
                                                        id="callcourtime_to_<?=$r['ID']?>" name="callcourtime_to_<?=$r['ID']?>"
                                                        placeholder="��:��" >
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label  form="call_courier_form" for="callcourcomment_<?=$r['ID']?>">����������� �������:</label>
                                            <input  form="call_courier_form" id="callcourcomment_<?=$r['ID']?>" class="form-control"
                                                    name="callcourcomment_<?=$r['ID']?>" >
                                        </div>
                                        <div class="modal-footer">
                                            <div id="call_courier_form_mess_<?=$r['ID'];?>"></div>
                                            <button type="button" class="btn btn-default" data-dismiss="modal">������</button>
                                            <button form="call_courier_form" type="submit"  id="call_courier_form_<?=$r['ID'];?>"
                                                    class="btn btn-primary" >�������</button>
                                            <!--  form="call_courier_form" type="submit"-->
                                        </div>
                                    </div>
                                </div>
                                <script>

                                    $("#call_courier_form_<?=$r['ID'];?>").on('click', function () {
                                        $(this).attr('style', 'visibility: hidden');
                                        $("#call_courier_form_mess_<?=$r['ID'];?>").html("<p style='color: #287dd6; height: 50px' > ���������, ���� ����� �������... </p>");
                                    });
                                </script>
                            </div>
                        </div>
                    <?php endforeach;
                endif;
                ?>
                <?php
                if($USER->isAdmin()){
                    $finish = microtime(true);
                    $delta = $finish - $start;
                    AddToLogs('test_logs', ['time_1' => $delta,
                        'mess' => '����� ������ ����� �������  ������� REQUESTS']);

                    $start = microtime(true);
                    AddToLogs('test_logs', ['time_start' => $start, 'mess' => '������ ������ ����� ������� ARCHIVE']);
                }
                ?>
                <?php

                foreach ($arResult['ARCHIVE'] as $r):
                    // ����� �� ������ ����������� � �������� ��� ���������� � ������� ���

                    if( $arResult['CURRENT_CLIENT'] == 56280706 ||
                        $arResult['CURRENT_CLIENT'] == 56389269 ||
                        $arResult['CURRENT_CLIENT'] == 56389270 ||
                        $arResult['CURRENT_CLIENT'] == 56389272 ||
                        $arResult['CURRENT_CLIENT'] == 49540621 ||
                        $USER->isAdmin()){
                        if ($r['state_text'] === '����������'){
                            $state_v = 'Y';
                            $number_v = $r['NAME'];
                            $date_create = substr($r['DATE_CREATE'],0,10);
                            $key_arr = count($r['Events'])-1;
                            $info_event = $r['Events'][$key_arr]['InfoEvent'];
                            $date_event = $r['Events'][$key_arr]['Date'];
                            $company_rec = $r['PROPERTY_COMPANY_RECIPIENT_VALUE'];
                            $city_rec = $r['PROPERTY_CITY_RECIPIENT_NAME'];
                            $ar_json = [
                                'number_v'     => $number_v,
                                'date_create'  => $date_create,
                                'info_event'   => $info_event,
                                'date_event'   => $date_event,
                                'company_rec'  => $company_rec,
                                'city_rec'     => $city_rec
                            ];
                            $ar_json = convArrayToUTF($ar_json);
                            $str_json = json_encode($ar_json);
                            $url_data = urlencode ($str_json);
                        }else{
                            $state_v = 'N';
                        }
                    }
                    ?>
                    <tr class="b1 <?=$r['ColorRow'];?>">
                        <td class="b2" width="20">
                            <input id="check_<?=$r['ID'];?>" onclick="return checkScan(<?=$r['ID'];?>)"
                                   type="checkbox" name="ids[]" value="f001=<?=$r['NAME'];?>">
                        </td>
                        <td>
                            <a target="_blank" href="/upload/pdf/<?=$r['NAME']?>.pdf">
                                <i  style="color:red" class="far fa-file-pdf"></i>
                            </a>
                        </td>
                        <td class="b3"  data-halign="center" data-align="center" data-valign="center">
                            <?php // ������� ��������� � �������� ����� ��� "������"
                            if (strlen(trim($r['NAME']))) {?>

                                <?php if (($arResult['CURRENT_CLIENT'] == ID_SUKHOI) ||
                                    ($arResult['CURRENT_CLIENT'] == ID_TEST))  { ?>
                                    <a href="/index.php?mode=invoice1c_printsukhoi&f001=<?=$r['NAME'];?>&printsukhoi=Y&print=Y" target="_blank">
                                        <span class="glyphicon glyphicon-print" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="������ ���������"></span>
                                    </a>
                                <?php } else { ?>
                                    <a href="/index.php?mode=invoice1c_print&f001=<?=$r['NAME'];?>&print=Y" target="_blank">
                                        <span class="glyphicon glyphicon-print" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="������ ���������"></span>
                                    </a>
                                    <?php
                                }
                            }
                            ?>
                        </td>
                        <?php if($state_v === "Y" ):?>
                            <td>
                                <a target="_blank" href="index.php?mode=print_notification&data=<?=$url_data;?>&print=Y">
                                    <span class="glyphicon glyphicon-bullhorn" aria-hidden="true"
                                          data-toggle="tooltip" data-placement="right"
                                          title="������ �����������"></span>
                                </a>
                            </td>
                        <?php elseif($state_v === "N"):?>
                            <td></td>
                        <?php endif;?>
                        <td class="b4" data-halign="center" data-align="center" data-valign="center">
                            <?php if (strlen(trim($r['NAME']))):?>
                                <a href="<?=$arParams['LINK'];?>index.php?mode=invoice1c_modal&f001=<?=$r['NAME'];?>&pdf=Y" data-toggle="modal" data-target="#modal_inv1c_<?=$r['NAME'];?>">
                                <span class="glyphicon glyphicon-zoom-in" aria-hidden="true"
                                      data-toggle="tooltip" data-placement="right"
                                      title="�������� ���������"></span>
                                </a>
                            <?php endif;?>
                        </td>
                        <td>
                            <?php if(!empty($r['SCAN_DOCS_PATH'])):?>
                                <a  data-toggle="modal"
                                    data-target="#modal_scan_<?=$r['ID'];?>" href="">
                                       <span  aria-hidden="true" data-toggle="tooltip" data-placement="right" title="������� �����"
                                              style="cursor:pointer" class="glyphicon glyphicon-paperclip">
                                </span>
                                </a>
                                <?php $sdocs = implode(",", $r['SCAN_DOCS_PATH']);?>
                                <input style="display:none;" id="sdoc_<?=$r['ID'];?>" type="checkbox" name="scandcs[]" value = "<?=$sdocs;?>">
                            <?php endif;?>
                        </td>
                        <?php
                        /* ����� ������ ������� ����������� $arResult['CURRENT_CLIENT'] == 56103010 || $USER->isAdmin()*/
                        if($arResult['CURRENT_CLIENT'] == 56103010):?>
                            <td >
                                <?=$r['CENTER_EXPENSES_NAME']?>
                            </td>
                        <?php endif;?>

                        <?php if (count($arResult['REQUESTS']) > 0):?>
                            <td class="b22" width="20">
                            </td>
                        <?php endif;?>
                        <td  class="b5" ><?=$r['NAME'];?></td>
                        <?php
                        // ��� ���������� ��������� � ������� ����������� (56103010)
                        if( $arResult['CURRENT_CLIENT'] == 52254529 ||
                            $arResult['CURRENT_CLIENT'] == 56103010 || $USER->isAdmin()):?>
                            <td>
                               ����� (������)
                            </td>
                        <?php endif;?>
                        <?php if($arResult['CURRENT_CLIENT'] != 41478141 ):?>
                            <td>
                            </td>
                        <?php endif;?>
                        <td  class="b6"  width="20">
                            <a href="" data-toggle="modal" data-target="#modal_tr_<?=$r['ID'];?>">
                                <?=$r['state_icon'];?>
                            </a>
                        </td>

                        <?php
                        // $arResult['CURRENT_CLIENT'] === '36015676' ||
                        if($arResult['CURRENT_CLIENT'] == 36015676 || $USER->isAdmin()):?>
                            <td>
                                ���������� (������)
                            </td>
                        <?php endif;?>
                        <?php
                        // �����. ����� ���������� ������ ������
                        if($arResult['CURRENT_CLIENT'] == 41478141 ):?>
                            <td  class="b7"  width="20"><?=$r['test'];?></td>
                        <?php endif;?>
                        <td  class="b8" ><?=$r['state_text'];?></td>

                        <td  class="b9">
                            <?php
                            /* ������� ����������� �������� ���� �������� ���������  - �������!! */
                            if($arResult['CURRENT_CLIENT'] == 56103010 || $USER->isAdmin()){
                                echo ($r['DATE_CREATE']);
                            }else{
                                echo substr($r['DATE_CREATE'],0,10);
                            }?>
                        </td>

                        <?php
                        if (($arResult['LIST_OF_BRANCHES']) && (!$arResult['USER_IN_BRANCH']))
                        {
                            ?>
                            <td class="b23"><?=$r['PROPERTY_BRANCH_NAME'];?></td>
                            <?php
                        }?>
                        <td   class="b10" ><?=$r['PROPERTY_CITY_SENDER_NAME'];?></td>
                        <td   class="b11" ><?=$r['PROPERTY_COMPANY_SENDER_VALUE'];?></td>
                        <td   class="b12" ><?=$r['PROPERTY_NAME_SENDER_VALUE'];?></td>
                        <td   class="b13" ><?=$r['PROPERTY_CITY_RECIPIENT_NAME'];?></td>
                        <td   class="b14" ><?=$r['PROPERTY_COMPANY_RECIPIENT_VALUE'];?></td>
                        <td   class="b15" ><?=$r['PROPERTY_NAME_RECIPIENT_VALUE'];?></td>
                        <td   class="b16" ><?=$r['PROPERTY_PLACES_VALUE'];?></td>
                        <td   class="b17"  ><?=WeightFormat($r['PROPERTY_WEIGHT_VALUE'], false);?></td>
                        <td   class="b18" ><?=WeightFormat($r['PROPERTY_OB_WEIGHT'],false);?></td>
                        <td   class="b19" ><?=WeightFormat($r['PROPERTY_RATE_VALUE'],false);?></td>

                        <?php
                        $obElement = CIBlockElement::GetByID($r['ID']);
                        if($arEl = $obElement->GetNext())
                        {
                            $rsUser = CUser::GetByID($arEl["CREATED_BY"]);
                            $arUser = $rsUser->Fetch();
                            $Property_creator_name = $arUser["NAME"]." ".$arUser["LAST_NAME"];
                        }
                        ?>
                        <td class="b20"><?=$Property_creator_name;?></td>
                        <td class="b21">

                            <a href="<?=$arParams['LINK'];?>index.php?mode=add&copyfrom=<?=$r['ID_SITE'];?>&copy=Y&numdoc=<?=$r['NAME']?>">
                        		<span class="glyphicon glyphicon-copy"
                                      aria-hidden="true" data-toggle="tooltip"
                                      data-placement="left" title="����������"></span>
                            </a>

                        </td>
                        <?php
                        if ($viewcolv === "Y"):?>
                            <td>
                                <?php if($r['PROPERTY_WITH_RETURN_VALUE']){
                                    echo "�";
                                }
                                ?>
                            </td>
                        <?php endif;?>
                    </tr>
                    <?php
                    $itogo  = $itogo  + $r['PROPERTY_RATE_VALUE'];?>
                    <!-- Modal -->
                    <?php if(!empty($r ['SCAN_DOCS_PATH'])):?>
                    <div class="modal fade" id="modal_scan_<?=$r['ID'];?>" tabindex="-1" role="dialog"
                         aria-labelledby="myModalLabel">
                        <div class="modal-dialog" role="document">

                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal"
                                            aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h3>� <?=$r['NAME']?></h3>

                                </div>
                                <div style="padding-bottom: 20px;" class="row">
                                    <?php $count = count($r ['SCAN_DOCS_PATH']);?>
                                    <h4 style="margin-left: 32px;">������� �����, ������������� � ��������� (<?=$count;?> ��. )</h4>
                                    <ul>
                                        <?php
                                        //dump($r ['SCAN_DOCS_PATH']);
                                        foreach($r ['SCAN_DOCS_PATH'] as $key=>$value):?>
                                            <?php $ext = getExtensionPath($value);?>
                                            <li style="list-style: decimal ">
                                                <div class="col-md-12">
                                                    <a target="_blank" href="http://<?=$value;?>">
                                                        ������� ���� ��������� (<?=$ext;?>)
                                                    </a>
                                                </div>
                                            </li>
                                        <?php endforeach;?>
                                    </ul>
                                </div>


                            </div>
                        </div>
                    </div>
                <?php endif;
                endforeach;?>
                </tbody>
            </table>
            <p>����� ���������: <?=(count($arResult['REQUESTS'])+count($arResult['ARCHIVE']));?></p>
            <div style="margin-bottom:50px;" class="btn-group" role="group" aria-label="...">
                <button type="submit"  name="prints" value="����������� ���������"
                        class="btn btn-warning testwarn">����������� ���������� ���������</button>

            </div>
        </form>

        </div>
        </div>
        <?php if ($arResult['AGENT']['PROPERTY_SHOW_LIMITS_VALUE'] == 1) : ?>
        <div class="row">
            <div class="col-md-3"><i>����� �� �����: <strong><?=number_format($itogo, 2, ',', ' ');?></strong></i></div>
            <?php
            if ($arResult['LIMITS_OF_BRANCHES'])
            {
                ?>
                <div class="col-md-3 text-center">
                    <i>����� �� <?=$arResult['QW_TEXT'];?> �������: <strong><?=number_format($arResult['All_SPENT'], 2, ',', ' ');?></strong></i> <span class="label <?=$arResult['LABEL_CLASS'];?>"><?=$arResult['All_PERSENT'];?></span></div>
                <div class="col-md-3 text-center"><i>����� �� <?=$arResult['QW_TEXT'];?> �������:<strong><?=number_format($arResult['All_LIMIT'], 2, ',', ' ');?></strong></i></div>
                <div class="col-md-3 text-right"><i>�������� �� <?=$arResult['QW_TEXT'];?> �������:<strong><?=number_format($arResult['All_LEFT'], 2, ',', ' ');?></strong></i></div>
                <?php
            }?>
        </div>
    <?php
    endif;
        if (count($arResult['REQUESTS']) > 0):?>
            <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" id="modal_<?=$r['ID'];?>" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                    </div>
                </div>
            </div>

        <?php
        endif;

        foreach ($arResult['REQUESTS'] as $r)
        {?>
            <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" id="modal_<?=$r['ID'];?>" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                    </div>
                </div>
            </div>
            <?php
        }

        foreach ($arResult['ARCHIVE'] as $r)
        {?>
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
                                            <th colspan="3">���� ����������� <?=$r['NAME'];?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        foreach ($r['Events'] as $ev)
                                        {
                                            if (in_array($ev['InfoEvent'], $arResult['HIDE_EVENTS']) &&
                                                ($ev['Event'] == '�������������� ��������!'))
                                            {}
                                            else
                                            {
                                                ?>
                                                <tr>
                                                    <td width="30%"><?=$ev['Date'];?></td>
                                                    <td width="35%"><?=$ev['Event'];?></td>
                                                    <td width="35%"><?=$ev['InfoEvent'];?></td>
                                                </tr>
                                                <?php
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
            <?php
        }
    }
    else
    {
        if ((int)$arResult['CURRENT_CLIENT'] == 0)
        {
            ?>
            <div class="alert alert-dismissable alert-warning fade in" role="alert">�� ������ ������</div>
            <?php
        }
        else
        {
            ?>
            <div class="alert alert-dismissable alert-warning fade in" role="alert">������ ��������� �� ��������� ������ ����</div>
            <?php
        }
    }
}
if($USER->isAdmin()){
    $finish = microtime(true);
    $delta = $finish - $start;
    AddToLogs('test_logs', ['time_1' => $delta,
        'mess' => '����� ������ �������']);
}

?>

