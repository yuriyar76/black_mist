<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
    die();
    // ������ ���������� ������ -bitrix\templates\lk-newpartner\js\invoice_custom.js
}
?>
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
        function ChangePeriodInApps() {
            var fd = $('#input-group-inapps-from-date');
            var td = $('#input-group-inapps-to-date');
            fd.removeClass('has-error');
            td.removeClass('has-error');
            var datefrom = $("input#inapps-from-date").val();
            var dateto = $("input#inapps-to-date").val();
            if ((dateto.length > 0) && (datefrom.length > 0)) {
                location.href = '<?=$arParams['LINK'];?>index.php?ChangePeriodInapps=Y&datefrom=' + datefrom + '&dateto=' + dateto;
            } else {
                if (dateto.length <= 0) {
                    fd.addClass('has-error');
                }
                if (datefrom.length <= 0) {
                    td.addClass('has-error');
                }
            }
        }

        function ChangeAgentInapps()
        {
            var ag = $("select#agent").val();
            location.href = '<?=$arParams['LINK'];?>index.php?ChangeAgentInapps=Y&agent='+ag;
        }

    </script>
<div class="container">
    <div class="row">
        <div class="col-md-6">
            <h3>�������� � ����������</h3>
        </div>
    </div>
</div>
   <?php if($arResult['OPEN']):?>
    <div class="container-fluid">
        <div class="row">

        </div>
       <div class="row">
        <div class="col-md-3">
            <div class="btn-group" role="group">
                <a href="" class="btn btn-warning" data-toggle="tooltip" data-placement="bottom" title=""
                   data-original-title="�������� ������"> <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
                </a>
            </div>
        </div>
           <div class="col-md-9 text-right">
               <form action="" method="get" name="filterform" class="form-inline">
                   <?
                   if ($arResult['LIST_OF_AGENTS'])
                   {
                       ?>
                       <div class="form-group">
                           <select name="agent" size="1" class="form-control selectpicker" id="agent" onChange="ChangeAgentInapps();"
                                   data-live-search="true" data-width="auto">
                               <option value="0"></option>
                               <?
                               foreach ($arResult['LIST_OF_AGENTS'] as $k => $v)
                               {
                                   $s = ($_SESSION['CURRENT_AGENT'] == $k) ? ' selected' : '';
                                   ?>
                                   <option value="<?=$k;?>"<?=$s;?>><?=$v;?></option>
                                   <?
                               }
                               ?>
                           </select>
                       </div>
                       <?
                   }
                   ?>
                   <div class="form-group">
                       <div class="input-group" id="input-group-inapps-from-date">
                           <input type="text" class="form-control maskdate" aria-describedby="basic-addon1"
                                  name="dateperiodfrom" placeholder="��.��.����"
                                  value="<?=$arResult['INAPPS_FROM_DATE'];?>"
                                  onChange="ChangePeriodInApps();" id="inapps-from-date">
                           <span class="input-group-addon" id="basic-addon1">
							<?
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
                   <div class="form-group">&nbsp;&mdash;&nbsp;</div>
                   <div class="form-group">
                       <div class="input-group" id="input-group-inapps-to-date">
                           <input type="text" class="form-control maskdate" aria-describedby="basic-addon2"
                                  name="dateperiodto" placeholder="��.��.����"
                                  value="<?=$arResult['INAPPS_TO_DATE'];?>" onChange="ChangePeriodInApps();"
                                  id="inapps-to-date">
                           <span class="input-group-addon" id="basic-addon2">
							<?
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
    </div>
<?php
//if($_GET['dev'] == 1):
    $pagen = $arResult['AGENT_DATA_OBJ']['obj'];
   // dump( $arResult['AGENT_DATA']);
    ?>
    <div id="update_alert" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
               <!-- <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                 </div>-->
                <div class="modal-body">
                    <h4 style="text-align: center">���������...</h4>
                    <div style="display: flex; flex-direction: row; justify-content: center">
                        <img style="display:block" src="/bitrix/components/black_mist/newpartner.requests.v.2.1/templates/lk-newpartner/images/spinner.gif"
                        alt="">
                    </div>
                </div>
             </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <div id="edit_number" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                 <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                 </div>
                <div style='display:none;' class="alert alert-danger" role="alert"></div>
                <div class="modal-body"></div>

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <?php foreach($arResult['AGENT_DATA'] as $res):?>
        <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" id="modal_<?=$res['ID']?>" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 text-right">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">x</span>
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <div id="NAME_MOD_<?=$res['ID'];?>" class="col-md-6"><h3>����� ������:
                                    <?=$res['NAME']?></h3>
                            </div>
                            <div id="PROPERTY_1023_MOD_<?=$res['ID'];?>" class="col-md-6 text-right">
                                <h3>����� ���������: <?=$res['PROPERTY_1023']?></h3>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4>�����������</h4>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">��������</div>
                                            <div id="PROPERTY_1027_MOD_<?=$res['ID'];?>" class="col-md-9">
                                                <strong><?=$res['PROPERTY_1027']?></strong>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">�������</div>
                                            <div id="PROPERTY_1026_MOD_<?=$res['ID'];?>" class="col-md-9">
                                                <strong><?=$res['PROPERTY_1026']?></strong>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">�������</div>
                                            <div id="PROPERTY_1028_MOD_<?=$res['ID'];?>" class="col-md-9">
                                                <strong><?=$res['PROPERTY_1028']?></strong>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">�����</div>
                                            <div id="PROPERTY_1032_MOD_<?=$res['ID'];?>" class="col-md-9">
                                                <strong><?=$res['PROPERTY_1032']?></strong>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">������</div>
                                            <div id="PROPERTY_1029_MOD_<?=$res['ID'];?>" class="col-md-9">
                                                <strong><?=$res['PROPERTY_1029']?></strong>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">�����</div>
                                            <div id="PROPERTY_1033_MOD_<?=$res['ID'];?>" class="col-md-9">
                                                <strong><?=$res['PROPERTY_1033']?></strong>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">����������</div>
                                            <div id="PROPERTY_1035_MOD_<?=$res['ID'];?>" class="col-md-9">
                                                <strong><?=$res['PROPERTY_1035']?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <div class="row"><div class="col-md-12"><h4>����������</h4></div></div>
                                        <div class="row">
                                            <div class="col-md-3">��������</div>
                                            <div id="PROPERTY_1038_MOD_<?=$res['ID'];?>" class="col-md-9">
                                                <strong><?=$res['PROPERTY_1038']?></strong>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">�������</div>
                                            <div id="PROPERTY_1037_MOD_<?=$res['ID'];?>" class="col-md-9">
                                                <strong><?=$res['PROPERTY_1037']?></strong>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">�������</div>
                                            <div id="PROPERTY_1039_MOD_<?=$res['ID'];?>" class="col-md-9">
                                                <strong><?=$res['PROPERTY_1039']?></strong>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">�����</div>
                                            <div id="PROPERTY_1043_MOD_<?=$res['ID'];?>" class="col-md-9">
                                                <strong><?=$res['PROPERTY_1043']?></strong>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">������</div>
                                            <div id="PROPERTY_1040_MOD_<?=$res['ID'];?>" class="col-md-9">
                                                <strong><?=$res['PROPERTY_1040']?></strong>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">�����</div>
                                            <div id="PROPERTY_1060_MOD_<?=$res['ID'];?>" class="col-md-9">
                                                <strong><?=$res['PROPERTY_1060']?></strong>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">����������</div>
                                            <div id="PROPERTY_1045_MOD_<?=$res['ID'];?>" class="col-md-9">
                                                <strong><?=$res['PROPERTY_1045']?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <h4>�������� �����������</h4>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="row">
                                                    <div  class="col-md-5">���</div>
                                                    <div id="PROPERTY_1068_MOD_<?=$res['ID'];?>"class="col-md-7">
                                                        <strong><?=$res['PROPERTY_1068']?> ��</strong>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-5">��� ��������</div>
                                                    <div id="PROPERTY_1069_MOD_<?=$res['ID'];?>" class="col-md-7">
                                                        <strong><?=$res['PROPERTY_1069']?></strong>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-5">���������� ����</div>
                                                    <div id="PROPERTY_1070_MOD_<?=$res['ID'];?>" class="col-md-7">
                                                        <strong><?=$res['PROPERTY_1070']?></strong>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="col-md-4">
                                                <div class="row">
                                                    <div class="col-md-5">���� ����������</div>
                                                    <div id="PROPERTY_1053_MOD_<?=$res['ID'];?>" class="col-md-7">
                                                        <strong>
                                                            <?=$res['PROPERTY_1053']?>
                                                        </strong>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-5">����� ������</div>
                                                    <div id="PROPERTY_1047_MOD_<?=$res['ID'];?>" class="col-md-7">
                                                        <strong>
                                                            <?=$res['PROPERTY_1047']?> -  <?=$res['PROPERTY_1048']?>
                                                        </strong>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-5">��� �����������</div>
                                                    <div id="PROPERTY_1071_MOD_<?=$res['ID'];?>" class="col-md-7">
                                                        <strong><?=$res['PROPERTY_1071']?></strong>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-5">��� ��������</div>
                                                    <div class="col-md-7">
                                                        <strong>��������</strong>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-5">������� ��������</div>
                                                    <div class="col-md-7">
                                                        <strong>�� ������</strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="row">
                                                    <div class="col-md-5">����������</div>
                                                    <div id="PROPERTY_1065_MOD_<?=$res['ID'];?>" class="col-md-7">
                                                        <strong>  <?=$res['PROPERTY_1065']?></strong>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-5">��� ������</div>
                                                    <div id="PROPERTY_1066_MOD_<?=$res['ID'];?>" class="col-md-7">
                                                        <strong>
                                                            <?=$res['PROPERTY_1066']?>
                                                        </strong>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-5">����� � ������</div>
                                                    <div id="PROPERTY_1050_MOD_<?=$res['ID'];?>" class="col-md-7">
                                                        <strong> <?=$res['PROPERTY_1050']?></strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="panel panel-default">
                                    <div id="PROPERTY_1067_MOD_<?=$res['ID'];?>" class="panel-body">
                                        <h4>����. ����������</h4>
                                        <strong><?=$res['PROPERTY_1067']?></strong>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <table cellpadding="5" bordercolor="#ccc" border="1" width="600" style=" border-collapse: collapse;" class="show_tracks table table-striped table-hover">
                                    <thead>
                                    <tr>
                                        <th colspan="3" class="text-center">����</th>
                                    </tr>
                                    </thead>
                                    <tbody id="PROPERTY_1059_MOD_<?=$res['ID'];?>">
                                     <?
                                     if($res['EVENTS_ARR']):
                                     foreach($res['EVENTS_ARR'] as $k=>$item): ?>
                                     <tr>
                                        <td width="30%"><?=$item['Date']?></td>
                                        <td width="35%"><?=$item['Event']?></td>
                                        <td width="35%"><?=$item['Info']?></td>
                                    </tr>
                                    <? endforeach;?>
                                    <?else:?>
                                         <tr>
                                             <td width="30%"><?=$res['PROPERTY_1061']?></td>
                                             <td width="35%"><?=$res['PROPERTY_1062'];?></td>
                                             <td width="35%"><?=$res['PROPERTY_1062'];?></td>
                                         </tr>
                                    <?endif;?>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    <?php endforeach;?>
    <table class="table table-condensed table-hover" data-toggle="table" data-show-columns="true"
           data-search="true"
           data-select-item-name="toolbar1" data-height="500" id="tableId">
        <thead>
        <tr>
            <th data-field="column1" data-sortable="true" data-switchable="false">������</th>
            <th data-field="column2" data-sortable="true" data-switchable="false">���������</th>
            <th data-field="column3" data-sortable="true" data-switchable="true">����</th>
            <th data-field="column4" data-sortable="true" data-switchable="false">���������</th>
            <th data-field="column5" data-sortable="true" data-switchable="true">������</th>
            <th data-field="column6" data-sortable="false" data-switchable="false">
            <span class="glyphicon glyphicon-zoom-in" aria-hidden="true" data-toggle="tooltip"
                  data-placement="right" title="" data-original-title="�������� ���������"></span>
            </th>
            <th data-field="column7" data-sortable="true" data-switchable="true">�����������</th>
            <th data-field="column8" data-sortable="true" data-switchable="true">��� �����������</th>
            <th data-field="column9" data-sortable="true" data-switchable="true">�������� �����������</th>
            <th data-field="column10" data-sortable="true" data-switchable="true">������� �����������</th>
            <th data-field="column11" data-sortable="true" data-switchable="true">����� �����������</th>
            <th data-field="column12" data-sortable="true" data-switchable="true">����� �����������</th>

            <th data-field="column13" data-sortable="true" data-switchable="true">����</th>
            <th data-field="column14" data-sortable="true" data-switchable="true">���</th>
            <th data-field="column15" data-sortable="true" data-switchable="true">��.���</th>
            <th data-field="column16" data-sortable="true" data-switchable="true">����������� ����������</th>

            <th data-field="column17" data-sortable="true" data-switchable="true">����������</th>
            <th data-field="column18" data-sortable="true" data-switchable="true">��� ����������</th>
            <th data-field="column19" data-sortable="true" data-switchable="true">�������� ����������</th>
            <th data-field="column20" data-sortable="true" data-switchable="true">������� ����������</th>
            <th data-field="column21" data-sortable="true" data-switchable="true">����� ����������</th>
            <th data-field="column22" data-sortable="true" data-switchable="true">����� ����������</th>
        </tr>
        </thead>
        <tbody>
        <? foreach($arResult['AGENT_DATA'] as $res):
            $color_class = '';
            if($res['PROPERTY_1062'] === "����������"){
                $color_class = 'supersuccess';
            }
            elseif($res['PROPERTY_1062'] === "��������� ������"){
                $color_class = 'success';
            }
            elseif(($res['PROPERTY_1062'] === "�������������� ��������!") ||
                ($res['PROPERTY_1062'] === "������ ������") ){
                $color_class = 'danger';
            }
            else{
                $color_class = 'warning';
            }
            ?>
            <tr id = "<?=$res['ID'];?>" class="<?=$color_class;?>">
                <td id="NAME_<?=$res['ID'];?>"><?=$res['NAME']?></td>
                <td  id="PROPERTY_1023_<?=$res['ID'];?>">
                    <div style="display: flex; flex-direction: column; align-items: center">
                        <?php if($res['PROPERTY_1062'] !== '����������'):?>
                        <p style="cursor: pointer;" id="num_edit_<?=$res['ID'];?>"
                           class="glyphicon glyphicon-pencil"
                           aria-hidden="true" data-toggle="tooltip" data-placement="right"
                           title="�������� ����� ���������">
                        </p>
                        <?endif;?>
                        <p id="PROPERTY_1023_<?=$res['ID'];?>_p"><?=$res['PROPERTY_1023']?></p>
                    </div>

                </td>
                <td id="PROPERTY_1061_<?=$res['ID'];?>"><?=$res['PROPERTY_1061']?></td>
                <td id="PROPERTY_1053_<?=$res['ID'];?>"><?=$res['PROPERTY_1053']?></td>
                <td>
                    <div style="display: flex; flex-direction: column; align-items: center">
                     <p style="cursor: pointer;" id="update_<?=$res['ID'];?>"
                           class="glyphicon glyphicon-repeat" data-uk='<?=$res['PROPERTY_1075'];?>'
                           data-uid="<?=$res['PROPERTY_1056'];?>" data-inn="<?=$res['PROPERTY_1076'];?>"
                           aria-hidden="true" data-toggle="tooltip" data-placement="left"
                           title="��������">
                     </p>

                    <p id="status_<?=$res['ID'];?>"><?=$res['PROPERTY_1062']?></p>
                    </div>
                </td>
                <td>
                    <a href="" data-toggle="modal"
                       data-target="#modal_<?=$res['ID']?>">
                         <span class="glyphicon glyphicon-zoom-in" aria-hidden="true" data-toggle="tooltip"
                               data-placement="right" title="" data-original-title="�������� ���������">
                   </span>
                    </a>
                </td>
                <td id="PROPERTY_1025_<?=$res['ID'];?>"><?=$res['PROPERTY_1025']?></td>
                <td id="PROPERTY_1026_<?=$res['ID'];?>"><?=$res['PROPERTY_1026']?></td>
                <td id="PROPERTY_1027_<?=$res['ID'];?>"><?=$res['PROPERTY_1027']?></td>
                <td id="PROPERTY_1028_<?=$res['ID'];?>"><?=$res['PROPERTY_1028']?></td>
                <td id="PROPERTY_1032_<?=$res['ID'];?>"><?=$res['PROPERTY_1032']?></td>
                <td id="PROPERTY_1033_<?=$res['ID'];?>"><?=$res['PROPERTY_1033']?></td>

                <td id="PROPERTY_1070_<?=$res['ID'];?>"><?=$res['PROPERTY_1070']?></td>
                <td id="PROPERTY_1068_<?=$res['ID'];?>"><?=$res['PROPERTY_1068']?></td>
                <td id="PROPERTY_1069_<?=$res['ID'];?>"><?=$res['PROPERTY_1069']?></td>
                <td id="PROPERTY_1067_<?=$res['ID'];?>"><?=$res['PROPERTY_1067']?></td>

                <td id="PROPERTY_1036_<?=$res['ID'];?>"><?=$res['PROPERTY_1036']?></td>
                <td id="PROPERTY_1037_<?=$res['ID'];?>"><?=$res['PROPERTY_1037']?></td>
                <td id="PROPERTY_1038_<?=$res['ID'];?>"><?=$res['PROPERTY_1038']?></td>
                <td id="PROPERTY_1039_<?=$res['ID'];?>"><?=$res['PROPERTY_1039']?></td>
                <td id="PROPERTY_1043_<?=$res['ID'];?>"><?=$res['PROPERTY_1043']?></td>
                <td id="PROPERTY_1060_<?=$res['ID'];?>"><?=$res['PROPERTY_1060']?></td>
            </tr>
        <? endforeach;?>
        </tbody>
    </table>
    <div style="display:flex; flex-direction: row; justify-content: flex-start; " class="row">
        <div class="pagination">
            <?php echo $pagen; ?>
        </div>
    </div>
<?endif;?>

    <script>
    $('#edit_number_form').submit(function (e) {
        e.preventDefault();
        let data = $(this).serializeArray();
        console.log(data);
        return false;
    });
</script>

