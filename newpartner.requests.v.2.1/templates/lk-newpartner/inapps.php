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
        $('#tableId').on('click', function(e){
            let el = e.target;
            let id = el.id;
            let uid = $('#'+id).attr('data-uid');
            let ukid = $('#'+id).attr('data-uk');
            let inn_agent = $('#'+id).attr('data-inn');
            let data = {
                'id': id, 'uid': uid, 'uk': ukid, 'inn_agent': inn_agent,
            };
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "/inapps/inapps_update.php",
                data: data,
                success: function(data){
                    let id = data.ID;
                    $(`#${id} #status_${id}`).text(data.PROPERTY_1062);

                    $(`#NAME_${id}`).text(data.NAME);
                    $(`#PROPERTY_1023_${id}`).text(data.PROPERTY_1023);
                    $(`#PROPERTY_1061_${id}`).text(data.PROPERTY_1061);
                    $(`#PROPERTY_1053_${id}`).text(data.PROPERTY_1053);
                    $(`#PROPERTY_1025_${id}`).text(data.PROPERTY_1025);
                    $(`#PROPERTY_1026_${id}`).text(data.PROPERTY_1026);
                    $(`#PROPERTY_1027_${id}`).text(data.PROPERTY_1027);
                    $(`#PROPERTY_1028_${id}`).text(data.PROPERTY_1028);
                    $(`#PROPERTY_1032_${id}`).text(data.PROPERTY_1032);
                    $(`#PROPERTY_1033_${id}`).text(data.PROPERTY_1033);
                    $(`#PROPERTY_1036_${id}`).text(data.PROPERTY_1036);
                    $(`#PROPERTY_1037_${id}`).text(data.PROPERTY_1037);
                    $(`#PROPERTY_1038_${id}`).text(data.PROPERTY_1038);
                    $(`#PROPERTY_1039_${id}`).text(data.PROPERTY_1039);
                    $(`#PROPERTY_1043_${id}`).text(data.PROPERTY_1043);
                    $(`#PROPERTY_1060_${id}`).text(data.PROPERTY_1060);
                    console.log(data);
                }
            });

        });
    });
</script>


<div class="container">
    <div class="row">
        <div class="col-md-6">
            <h3>Страница в разработке</h3>
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
        <th data-field="column1" data-sortable="true" data-switchable="false">Заявка</th>
        <th data-field="column2" data-sortable="true" data-switchable="true">Накладная</th>
        <th data-field="column3" data-sortable="true" data-switchable="true">Дата</th>
        <th data-field="column4" data-sortable="true" data-switchable="true">Выполнить</th>
        <th data-field="column5" data-sortable="true" data-switchable="true">Статус</th>
        <th data-field="column6" data-sortable="false" data-switchable="true">
            <span class="glyphicon glyphicon-zoom-in" aria-hidden="true" data-toggle="tooltip"
               data-placement="right" title="" data-original-title="Просмотр накладной"></span>
        </th>
        <th data-field="column7" data-sortable="true" data-switchable="true">Отправитель</th>
        <th data-field="column8" data-sortable="true" data-switchable="false">ФИО Отправителя</th>
        <th data-field="column9" data-sortable="true" data-switchable="true">Компания Отправителя</th>
        <th data-field="column10" data-sortable="true" data-switchable="true">Телефон Отправителя</th>
        <th data-field="column11" data-sortable="true" data-switchable="true">Город Отправителя</th>
        <th data-field="column12" data-sortable="true" data-switchable="false">Адрес Отправителя</th>
        <th data-field="column13" data-sortable="true" data-switchable="true">Получатель</th>
        <th data-field="column14" data-sortable="true" data-switchable="true">ФИО Получателя</th>
        <th data-field="column15" data-sortable="true" data-switchable="false">Компания Получателя</th>
        <th data-field="column16" data-sortable="true" data-switchable="true">Телефон Получателя</th>
        <th data-field="column17" data-sortable="true" data-switchable="true">Город Получателя</th>
        <th data-field="column18" data-sortable="true" data-switchable="true">Адрес Получателя</th>
    </tr>
    </thead>
    <tbody>
      <? foreach($arResult['AGENT_DATA'] as $res):?>
        <tr id = "<?=$res['ID'];?>">
            <td id="NAME_<?=$res['ID'];?>"><?=$res['NAME']?></td>
            <td id="PROPERTY_1023_<?=$res['ID'];?>"><?=$res['PROPERTY_1023']?></td>
            <td id="PROPERTY_1061_<?=$res['ID'];?>"><?=$res['PROPERTY_1061']?></td>
            <td id="PROPERTY_1053_<?=$res['ID'];?>"><?=$res['PROPERTY_1053']?></td>
            <td>
             <span style="cursor: pointer;" id="update_<?=$res['ID'];?>"
                   class="glyphicon glyphicon-repeat" data-uk='<?=$res['PROPERTY_1075'];?>'
                   data-uid="<?=$res['PROPERTY_1056'];?>" data-inn="<?=$res['PROPERTY_1076'];?>"
                   aria-hidden="true" data-toggle="tooltip" data-placement="left"
                   title="Обновить">
             </span>
                <br>
             <span id="status_<?=$res['ID'];?>"><?=$res['PROPERTY_1062']?></span>
            </td>
            <td>
                <a href="" data-toggle="modal"
                   data-target="#modal_<?=$res['ID']?>">
                         <span class="glyphicon glyphicon-zoom-in" aria-hidden="true" data-toggle="tooltip"
                         data-placement="right" title="" data-original-title="Просмотр накладной">
                   </span>
                </a>

            </td>
            <td id="PROPERTY_1025_<?=$res['ID'];?>"><?=$res['PROPERTY_1025']?></td>
            <td id="PROPERTY_1026_<?=$res['ID'];?>"><?=$res['PROPERTY_1026']?></td>
            <td id="PROPERTY_1027_<?=$res['ID'];?>"><?=$res['PROPERTY_1027']?></td>
            <td id="PROPERTY_1028_<?=$res['ID'];?>"><?=$res['PROPERTY_1028']?></td>
            <td id="PROPERTY_1032_<?=$res['ID'];?>"><?=$res['PROPERTY_1032']?></td>
            <td id="PROPERTY_1033_<?=$res['ID'];?>"><?=$res['PROPERTY_1033']?></td>
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
                               <div class="col-md-6"><h3>Номер заявки: <?=$res['NAME']?></h3></div>
                               <? if($res['PROPERTY_1023']):?>
                               <div class="col-md-6 text-right">
                                   <h3>Номер накладной: <?=$res['PROPERTY_1023']?></h3>
                               </div>
                               <? endif;?>
                           </div>
                           <div class="row">
                               <div class="col-md-6">
                                   <div class="panel panel-default">
                                       <div class="panel-body">
                                           <div class="row">
                                               <div class="col-md-12">
                                                   <h4>Отправитель</h4>
                                               </div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">Компания</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1027']?></strong></div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">Фамилия</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1026']?></strong></div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">Телефон</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1028']?></strong></div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">Город</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1032']?></strong></div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">Индекс</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1029']?></strong></div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">Адрес</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1033']?></strong></div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">Примечание</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1035']?></strong></div>
                                           </div>
                                       </div>
                                   </div>
                               </div>
                               <div class="col-md-6">
                                   <div class="panel panel-default">
                                       <div class="panel-body">
                                           <div class="row"><div class="col-md-12"><h4>Получатель</h4></div></div>
                                           <div class="row">
                                               <div class="col-md-3">Компания</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1038']?></strong></div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">Фамилия</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1037']?></strong></div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">Телефон</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1039']?></strong></div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">Город</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1043']?></strong></div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">Индекс</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1040']?></strong></div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">Адрес</div>
                                               <div class="col-md-9"><strong><?=$res['PROPERTY_1060']?></strong></div>
                                           </div>
                                           <div class="row">
                                               <div class="col-md-3">Примечание</div>
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
                                           <h4>Характер отправления</h4>
                                           <div class="row">
                                               <div class="col-md-4">
                                                   <div class="row">
                                                       <div class="col-md-5">Вес</div>
                                                       <div class="col-md-7"><strong><?=$res['PROPERTY_1068']?> кг</strong></div>
                                                   </div>
                                                   <div class="row">
                                                       <div class="col-md-5">Вес объемный</div>
                                                       <div class="col-md-7"><strong><?=$res['PROPERTY_1069']?></strong></div>
                                                   </div>
                                                   <div class="row">
                                                       <div class="col-md-5">Количество мест</div>
                                                       <div class="col-md-7"><strong><?=$res['PROPERTY_1070']?></strong></div>
                                                   </div>

                                               </div>
                                               <div class="col-md-4">
                                                   <div class="row">
                                                       <div class="col-md-5">Дата выполнения</div>
                                                       <div class="col-md-7">
                                                           <strong>
                                                                <?=$res['PROPERTY_1053']?>
                                                           </strong>
                                                       </div>
                                                   </div>
                                                   <div class="row">
                                                       <div class="col-md-5">Время забора</div>
                                                       <div class="col-md-7">
                                                           <strong>
                                                               <?=$res['PROPERTY_1047']?> -  <?=$res['PROPERTY_1048']?>
                                                           </strong>
                                                       </div>
                                                   </div>
                                                   <div class="row">
                                                       <div class="col-md-5">Тип отправления</div>
                                                       <div class="col-md-7"><strong><?=$res['PROPERTY_1071']?></strong></div>
                                                   </div>
                                                   <div class="row">
                                                       <div class="col-md-5">Тип доставки</div>
                                                       <div class="col-md-7"><strong>Стандарт</strong></div>
                                                   </div>
                                                   <div class="row">
                                                       <div class="col-md-5">Условия доставки</div>
                                                       <div class="col-md-7"><strong>По адресу</strong></div>
                                                   </div>
                                               </div>
                                               <div class="col-md-4">
                                                   <div class="row">
                                                       <div class="col-md-5">Плательщик</div>
                                                       <div class="col-md-7"><strong>  <?=$res['PROPERTY_1065']?></strong></div>
                                                   </div>
                                                   <div class="row">
                                                       <div class="col-md-5">Тип оплаты</div>
                                                       <div class="col-md-7"><strong>
                                                               <?=$res['PROPERTY_1066']?>
                                                           </strong>
                                                       </div>
                                                   </div>
                                                   <div class="row">
                                                       <div class="col-md-5">Сумма к оплате</div>
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
                                           <h4>Спец. инструкции</h4>
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
                                           <th colspan="3" class="text-center">Трек отправления 78-00016894</th>
                                       </tr>
                                       </thead>
                                       <tbody>
                                       <tr>
                                           <td width="30%">11.02.2021&nbsp;19:52</td>
                                           <td width="35%">В пути</td>
                                           <td width="35%">Планируемое прибытие в Москва 12.02.2021</td>
                                       </tr>
                                       <tr>
                                           <td width="30%">12.02.2021&nbsp;08:31</td>
                                           <td width="35%">Оприходовано складом</td>
                                           <td width="35%">Москва</td>
                                       </tr>
                                       <tr>
                                           <td width="30%">12.02.2021&nbsp;09:27</td>
                                           <td width="35%">Распределено</td>
                                           <td width="35%">Воронеж</td>
                                       </tr>
                                       <tr>
                                           <td width="30%">14.02.2021&nbsp;10:40</td>
                                           <td width="35%">Отправлено в город</td>
                                           <td width="35%">Воронеж</td>
                                       </tr>
                                       </tbody>
                                   </table>
                               </div>
                               <div class="col-md-6">
                                   <div class="row">
                                       <div class="col-md-12">
                                           <h4>Комментарии</h4>
                                       </div>
                                   </div>
                                   <div class="row">
                                       <div class="col-md-5">
                                           <div id="commentinfo"></div>
                                           <input type="hidden" id="comment_NUMDOC" value="78-00016894">
                                           <input type="hidden" id="comment_NUMREQUEST" value="ЮМАКС-00569">
                                           <input type="hidden" id="comment_Otv" value="Анастасия  Санинская">
                                           <input type="hidden" id="comment_Org" value="ООО «Юмакс»">
                                           <input type="hidden" id="comment_INN" value="3664110447">
                                           <div class="form-group">
                                               <textarea class="form-control" placeholder="Введите комментарий" id="comment_Comment"></textarea>
                                           </div>
                                           <br>
                                           <button class="btn btn-primary" id="comment_add" type="submit" onclick="sendcomment();">Добавить</button>
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

