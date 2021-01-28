<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
if (($arResult['OPEN']) && ($arResult['REQUEST']))
{
	?>
    <script type="text/javascript">
		function sendcomment() {
			$('#comment_Comment').parent(".form-group").removeClass('has-error');
			$('#commentinfo').html('');
			var comment_l = $.trim($('#comment_Comment').val()).length;
			if (comment_l > 0)
			{
				var comment = $('#comment_Comment').val();
				var org = $('#comment_Org').val();
				var otv = $('#comment_Otv').val()
				$.post("/search_city.php?sendcomment=Y", {
						comment_NUMDOC: $('#comment_NUMDOC').val(), 
						comment_NUMREQUEST: $('#comment_NUMREQUEST').val(),
						comment_Otv: otv,
						comment_Org: org,
						comment_INN: $('#comment_INN').val(),
						comment_Comment: comment
					},
					function(data){
						if (data["result"] == 'Y')
						{
							$('#commentinfo').html('<div class="alert alert-dismissable alert-success fade in" role="alert"><button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">X</span><span class="sr-only">�������</span></button>��������� ������� ���������</div>');
							$('#bodycomment').append('<tr><td>'+data["date"]+'</td><td>'+comment+'</td><td>'+org+'</td><td>'+otv+'</td></tr>');
							$('#comment_Comment').val('');
						}
						else
						{
							$('#commentinfo').html('<div class="alert alert-dismissable alert-danger fade in" role="alert"><button data-dismiss="alert" class="close" type="button"><span aria-hidden="true">X</span><span class="sr-only">�������</span></button>���-�� ����� �� ���...</div>');
						}
					}
				, "json");
			}
			else
			{
				$('#comment_Comment').parent(".form-group").addClass('has-error');
			}
		}
	</script>
    
    <div class="modal-body">
    	<div class="row">
        	<div class="col-md-12 text-right"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
        </div>
		<div class="row">
            <div class="col-md-6"><h3><?=$arResult['TITLE'];?></h3></div>
            <div class="col-md-6 text-right"><h3><?=$arResult['TITLE_2'];?></h3></div>
		</div>
        <div class="row">
			<div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-body">
                    	<div class="row"><div class="col-md-12"><h4>�����������</h4></div></div>
                        <div class="row">
                        	<div class="col-md-3">��������</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['�������������������'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">�������</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['������������������'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">�������</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['������������������'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">�����</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['����������������'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">������</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['�����������������'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">�����</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['����������������'];?></strong></div>
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
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['������������������'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">�������</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['�����������������'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">�������</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['�����������������'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">�����</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['���������������'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">������</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['����������������'];?></strong></div>
						</div>
                        <div class="row">
                        	<div class="col-md-3">�����</div>
                            <div class="col-md-9"><strong><?=$arResult['REQUEST']['���������������'];?></strong></div>
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
                                    <div class="col-md-7"><strong><?=WeightFormat($arResult['REQUEST']['��������������'], false);?></strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-5">��� ��������</div>
                                    <div class="col-md-7"><strong><?=WeightFormat($arResult['REQUEST']['����������������������'], false);?></strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-5">���������� ����</div>
                                    <div class="col-md-7"><strong><?=$arResult['REQUEST']['��������������'];?></strong></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                            	<div class="row">
                                	<div class="col-md-5">���� ����������</div>
                                    <div class="col-md-7">
                                    <strong>
										<?
                                            if (strlen($arResult['REQUEST']['��������������������']))
                                            {
												echo substr($arResult['REQUEST']['��������������������'],8,2).'.'.substr($arResult['REQUEST']['��������������������'],5,2).'.'.substr($arResult['REQUEST']['��������������������'],0,4);
                                            }
                                        ?>
                                    </strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-5">��� �����������</div>
                                    <div class="col-md-7"><strong></strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-5">��� ��������</div>
                                    <div class="col-md-7"><strong><?=$arResult['REQUEST']['������������������'];?></strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-5">������� ��������</div>
                                    <div class="col-md-7"><strong><?=$arResult['REQUEST']['������������������'];?></strong></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                            	<div class="row">
                                	<div class="col-md-5">����������</div>
                                    <div class="col-md-7"><strong><?=$arResult['REQUEST']['�����������������'];?></strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-5">��� ������</div>
                                    <div class="col-md-7"><strong><?=$arResult['REQUEST']['����������������'];?></strong></div>
                                </div>
                            	<div class="row">
                                	<div class="col-md-5">����� � ������</div>
                                    <div class="col-md-7"><strong><?=$arResult['REQUEST']['������������'];?></strong></div>
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
                        <?=$arResult['REQUEST']['���������������������'];?>
					</div>
				</div>
				<div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-5">������������� ��������</div>
                            <div class="col-md-7"><strong><?=$arResult['REQUEST']['�������������'];?></strong></div>
                        </div>
					</div>
				</div>
			</div>
        </div>
        <div class="row">
        	<div class="col-md-6">
				<?
				$APPLICATION->IncludeComponent(
					"black_mist:delivery.get_pods", 
					".default", 
					array(
						"SHOW_FORM" => "N",
						"CACHE_TYPE" => "A",
						"CACHE_TIME" => "3600",
						"SAVE_TO_SITE" => "N",
						"SHOW_TITLE" => "N",
						"SET_TITLE" => "N",
						"TEST_MODE" => "N",
						"COMPONENT_TEMPLATE" => ".default",
						"NO_TEMPLATE" => "N",
						"ONLY_1C_DATA" => "Y",
						"COMPOSITE_FRAME_MODE" => "A",
						"COMPOSITE_FRAME_TYPE" => "AUTO"
					),
					false
				);
				?>
            </div><!--col-->
        </div><!--row-->
		<div class="row">
			<div class="col-md-12">
				<h4>�����������</h4>
			</div>
        </div>
        <div class="row">
        	<div class="col-md-3">
                <div id="commentinfo"></div>
                <input type="hidden" id="comment_NUMDOC" value="<?=$arResult['REQUEST']['��������������'];?>">
                <input type="hidden" id="comment_NUMREQUEST" value="<?=$arResult['REQUEST']['�����������'];?>">
                <input type="hidden" id="comment_Otv" value="<?=$arResult['USER_NAME'];?>">
                <input type="hidden" id="comment_Org" value="<?=$arResult['AGENT']['NAME'];?>">
                <input type="hidden" id="comment_INN" value="<?=$arResult['AGENT']['PROPERTY_INN_VALUE'];?>">
                <div class="form-group">
                    <textarea class="form-control" placeholder="������� �����������" id="comment_Comment"></textarea>
                </div>
                <br>
                <button class="btn btn-primary" id="comment_add" type="submit" onClick="sendcomment();">��������</button>
            </div>
            <div class="col-md-6">
                <table class="table table-striped table-bordered table-condensed">
                    <thead>
                        <tr>
                            <th>����</th>
                            <th>�����������</th>
                            <th>��������</th>
                            <th>�����</th>
                        </tr>
                    </thead>
                    <tbody id="bodycomment">
                    <?
                    foreach ($arResult['REQUEST']['Messages'] as $m)
                    {
                        ?>
                        <tr>
                            <td><?=substr($m['Date'],8,2).'.'.substr($m['Date'],5,2).'.'.substr($m['Date'],0,4).' '.substr($m['Date'],11,5);?></td>
                            <td><?=iconv('utf-8', 'windows-1251', $m['Comment']);?></td>
                            <td><?=iconv('utf-8', 'windows-1251', $m['Org']);?></td>
                            <td><?=iconv('utf-8', 'windows-1251', $m['Otv']);?></td>
                        </tr>
                        <?
                    }
                    ?>
                    </tbody>
                </table>
            </div>
		</div>
    </div>
    <?
}