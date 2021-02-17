<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
    die();
}
?>
<script>
    $(function () {
        $(window).resize(function () {
            $('#tableId').bootstrapTable('resetView');
        });

    });
</script>


<div class="container">
    <div class="row">
        <div class="col-md-6">
            <h3>�������� � ����������</h3>
        </div>

    </div>
</div>


<?php
   if($_GET['dev'] == 1):?>

      <?php // dump($arResult['AGENT_DATA']); ?>
<table style="margin-bottom: 50px" class="table table-condensed table-hover" data-toggle="table" data-show-columns="true" data-search="true"
       data-select-item-name="toolbar1" data-height="600" id="tableId">
    <thead>
    <tr>
        <th data-field="column1" data-sortable="true" data-switchable="false">������</th>
        <th data-field="column2" data-sortable="true" data-switchable="true">���������</th>
        <th data-field="column3" data-sortable="true" data-switchable="true">����</th>
        <th data-field="column4" data-sortable="true" data-switchable="true">���������</th>
        <th data-field="column5" data-sortable="true" data-switchable="true">������</th>
        <th data-field="column6" data-sortable="false" data-switchable="true">
            <span class="glyphicon glyphicon-zoom-in" aria-hidden="true" data-toggle="tooltip"
               data-placement="right" title="" data-original-title="�������� ���������"></span>
        </th>
        <th data-field="column7" data-sortable="true" data-switchable="true">�����������</th>
        <th data-field="column8" data-sortable="true" data-switchable="false">��� �����������</th>
        <th data-field="column9" data-sortable="true" data-switchable="true">�������� �����������</th>
        <th data-field="column10" data-sortable="true" data-switchable="true">������� �����������</th>
        <th data-field="column11" data-sortable="true" data-switchable="true">����� �����������</th>
        <th data-field="column12" data-sortable="true" data-switchable="false">����� �����������</th>
        <th data-field="column13" data-sortable="true" data-switchable="true">����������</th>
        <th data-field="column14" data-sortable="true" data-switchable="true">��� ����������</th>
        <th data-field="column15" data-sortable="true" data-switchable="false">�������� ����������</th>
        <th data-field="column16" data-sortable="true" data-switchable="true">������� ����������</th>
        <th data-field="column17" data-sortable="true" data-switchable="true">����� ����������</th>
        <th data-field="column18" data-sortable="true" data-switchable="true">����� ����������</th>
    </tr>
    </thead>
    <tbody>
      <? foreach($arResult['AGENT_DATA'] as $res):?>
        <tr>
            <td><?=$res['NAME']?></td>
            <td><?=$res['PROPERTY_1023']?></td>
            <td><?=$res['PROPERTY_1061']?></td>
            <td><?=$res['PROPERTY_1053']?></td>
            <td><?=$res['PROPERTY_1062']?></td>
            <td>
                <a href="" data-toggle="modal"
                   data-target="#modal_<?=$res['ID']?>">
                         <span class="glyphicon glyphicon-zoom-in" aria-hidden="true" data-toggle="tooltip"
                         data-placement="right" title="" data-original-title="�������� ���������">
                   </span>
                </a>

            </td>
            <td><?=$res['PROPERTY_1025']?></td>
            <td><?=$res['PROPERTY_1026']?></td>
            <td><?=$res['PROPERTY_1027']?></td>
            <td><?=$res['PROPERTY_1028']?></td>
            <td><?=$res['PROPERTY_1032']?></td>
            <td><?=$res['PROPERTY_1033']?></td>
            <td><?=$res['PROPERTY_1053']?></td>
            <td><?=$res['PROPERTY_1037']?></td>
            <td><?=$res['PROPERTY_1038']?></td>
            <td><?=$res['PROPERTY_1039']?></td>
            <td><?=$res['PROPERTY_1043']?></td>
            <td><?=$res['PROPERTY_1060']?></td>
        </tr>

      <? endforeach;?>
    </tbody>
</table>
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
                               <div class="col-md-6"><h3>����� ������: <?=$res['NAME']?></h3></div>
                               <? if($res['PROPERTY_1023']):?>
                               <div class="col-md-6 text-right">
                                   <h3>����� ���������: <?=$res['PROPERTY_1023']?></h3>
                               </div>
                               <? endif;?>
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
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1027']?></strong></div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">�������</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1026']?></strong></div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">�������</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1028']?></strong></div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">�����</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1032']?></strong></div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">������</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1029']?></strong></div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">�����</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1033']?></strong></div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">����������</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1035']?></strong></div>
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
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1038']?></strong></div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">�������</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1037']?></strong></div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">�������</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1039']?></strong></div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">�����</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1043']?></strong></div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">������</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1040']?></strong></div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">�����</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1060']?></strong></div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">����������</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1045']?></strong></div>
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
                                                       <div class="col-md-5">���</div>
                                                       <div class="col-md-7"><strong><?=$res['PROPERTY_1068']?> ��</strong></div>
                                                   </div>
                                                   <div class="row">
                                                       <div class="col-md-5">��� ��������</div>
                                                       <div class="col-md-7"><strong><?=$res['PROPERTY_1069']?></strong></div>
                                                   </div>
                                                   <div class="row">
                                                       <div class="col-md-5">���������� ����</div>
                                                       <div class="col-md-7"><strong><?=$res['PROPERTY_1070']?></strong></div>
                                                   </div>

                                               </div>
                                               <div class="col-md-4">
                                                   <div class="row">
                                                       <div class="col-md-5">���� ����������</div>
                                                       <div class="col-md-7">
                                                           <strong>
                                                                <?=$res['PROPERTY_1053']?>
                                                           </strong>
                                                       </div>
                                                   </div>
                                                   <div class="row">
                                                       <div class="col-md-5">����� ������</div>
                                                       <div class="col-md-7">
                                                           <strong>
                                                               <?=$res['PROPERTY_1047']?> -  <?=$res['PROPERTY_1048']?>
                                                           </strong>
                                                       </div>
                                                   </div>
                                                   <div class="row">
                                                       <div class="col-md-5">��� �����������</div>
                                                       <div class="col-md-7"><strong><?=$res['PROPERTY_1071']?></strong></div>
                                                   </div>
                                                   <div class="row">
                                                       <div class="col-md-5">��� ��������</div>
                                                       <div class="col-md-7"><strong>��������</strong></div>
                                                   </div>
                                                   <div class="row">
                                                       <div class="col-md-5">������� ��������</div>
                                                       <div class="col-md-7"><strong>�� ������</strong></div>
                                                   </div>
                                               </div>
                                               <div class="col-md-4">
                                                   <div class="row">
                                                       <div class="col-md-5">����������</div>
                                                       <div class="col-md-7"><strong>  <?=$res['PROPERTY_1065']?></strong></div>
                                                   </div>
                                                   <div class="row">
                                                       <div class="col-md-5">��� ������</div>
                                                       <div class="col-md-7"><strong>
                                                               <?=$res['PROPERTY_1066']?>
                                                           </strong>
                                                       </div>
                                                   </div>
                                                   <div class="row">
                                                       <div class="col-md-5">����� � ������</div>
                                                       <div class="col-md-7">
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
                                       <div class="panel-body">
                                           <h4>����. ����������</h4>
                                           <?=$res['PROPERTY_1067']?>
                                       </div>
                                   </div>

                               </div>
                           </div>
                           <div class="row">
                               <div class="col-md-6">
                                   <table cellpadding="5" bordercolor="#ccc" border="1" width="600" style=" border-collapse: collapse;" class="show_tracks table table-striped table-hover">
                                       <thead>
                                       <tr>
                                           <th colspan="3" class="text-center">���� ����������� 78-00016894</th>
                                       </tr>
                                       </thead>
                                       <tbody>
                                       <tr>
                                           <td width="30%">11.02.2021&nbsp;19:52</td>
                                           <td width="35%">� ����</td>
                                           <td width="35%">����������� �������� � ������ 12.02.2021</td>
                                       </tr>
                                       <tr>
                                           <td width="30%">12.02.2021&nbsp;08:31</td>
                                           <td width="35%">������������ �������</td>
                                           <td width="35%">������</td>
                                       </tr>
                                       <tr>
                                           <td width="30%">12.02.2021&nbsp;09:27</td>
                                           <td width="35%">������������</td>
                                           <td width="35%">�������</td>
                                       </tr>
                                       <tr>
                                           <td width="30%">14.02.2021&nbsp;10:40</td>
                                           <td width="35%">���������� � �����</td>
                                           <td width="35%">�������</td>
                                       </tr>
                                       </tbody>
                                   </table>
                               </div>
                               <div class="col-md-6">
                                   <div class="row">
                                       <div class="col-md-12">
                                           <h4>�����������</h4>
                                       </div>
                                   </div>
                                   <div class="row">
                                       <div class="col-md-5">
                                           <div id="commentinfo"></div>
                                           <input type="hidden" id="comment_NUMDOC" value="78-00016894">
                                           <input type="hidden" id="comment_NUMREQUEST" value="�����-00569">
                                           <input type="hidden" id="comment_Otv" value="���������  ���������">
                                           <input type="hidden" id="comment_Org" value="��� ������">
                                           <input type="hidden" id="comment_INN" value="3664110447">
                                           <div class="form-group">
                                               <textarea class="form-control" placeholder="������� �����������" id="comment_Comment"></textarea>
                                           </div>
                                           <br>
                                           <button class="btn btn-primary" id="comment_add" type="submit" onclick="sendcomment();">��������</button>
                                       </div>
                                       <div class="col-md-7">
                                       </div>
                                   </div>
                               </div>
                           </div>
                       </div>

                   </div>
               </div>
           </div>
   <?php endforeach;?>
   <?php endif; ?>

