<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
?>

<div class="row">
	<div class="col-md-6">
		<h3><?=$arResult['TITLE'];?></h3>
	</div>
	<div class="col-md-6 text-right">
		<h3><?=$arResult['TITLE_2'];?></h3>
	</div>
</div>
<?

if (($arResult['OPEN']) && ($arResult['REQUEST']))
{
	?>
    <div class="row">
        <div class="col-md-4">
            <h4>�����������</h4>
            <div class="panel panel-default-1">
				<div class="panel-heading">��������</div>
				<div class="panel-body"><?=$arResult['REQUEST']['�������������������'];?></div>
			</div>
            <div class="panel panel-default-1">
                <div class="panel-heading">�������</div>
                <div class="panel-body"><?=strlen($arResult['REQUEST']['������������������']) ? $arResult['REQUEST']['������������������'] : '&nbsp;';?></div>
            </div>
            <div class="panel panel-default-1">
                <div class="panel-heading">�������</div>
                <div class="panel-body"><?=$arResult['REQUEST']['������������������'];?></div>
            </div>
            <div class="panel panel-default-1">
                <div class="panel-heading">�����</div>
                <div class="panel-body"><?=$arResult['REQUEST']['����������������'];?></div>
            </div>
            <div class="panel panel-default-1">
                <div class="panel-heading">������</div>
                <div class="panel-body"><?=strlen($arResult['REQUEST']['�����������������']) ? $arResult['REQUEST']['�����������������'] : '&nbsp;';?></div>
            </div>
            <div class="panel panel-default-1">
                <div class="panel-heading">�����</div>
                <div class="panel-body"><?=$arResult['REQUEST']['����������������'];?></div>
            </div>
        </div>
        <div class="col-md-4">
            <h4>����������</h4>
            <div class="panel panel-default">
                <div class="panel-heading">��������</div>
                <div class="panel-body"><?=$arResult['REQUEST']['������������������'];?></div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">�������</div>
                <div class="panel-body"><?=strlen($arResult['REQUEST']['�����������������']) ? $arResult['REQUEST']['�����������������'] : '&nbsp;';?></div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">�������</div>
                <div class="panel-body"><?=$arResult['REQUEST']['�����������������'];?></div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">�����</div>
                <div class="panel-body"><?=$arResult['REQUEST']['���������������'];?></div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">������</div>
                <div class="panel-body"><?=strlen($arResult['REQUEST']['����������������']) ? $arResult['REQUEST']['����������������'] : '&nbsp;';?></div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">�����</div>
                <div class="panel-body"><?=$arResult['REQUEST']['���������������'];?></div>
            </div>
        </div>
        <div class="col-md-4">
        	<h4>�������� �����������</h4>
            <div class="panel panel-default-3">
                <div class="panel-heading">���� � ��������� �������� ������</div>
				<div class="panel-body"><?=strlen($arResult['REQUEST']['DATETIME']) ? $arResult['REQUEST']['DATETIME'] : '&nbsp;';?></div>
            </div>
            <div class="panel panel-default-3">
                <div class="panel-heading">��� �����������</div>
				<div class="panel-body"><?=strlen($arResult['REQUEST']['PROPERTY_TYPE_VALUE']) ? $arResult['REQUEST']['PROPERTY_TYPE_VALUE'] : '&nbsp;';?></div>
            </div>
			<div class="panel panel-default-3">
            	<div class="panel-heading">���������� ����</div>
                <div class="panel-body"><?=$arResult['REQUEST']['��������������'];?></div>
			</div>
			<div class="panel panel-default-3">
            	<div class="panel-heading">���</div>
                <div class="panel-body"><?=WeightFormat($arResult['REQUEST']['��������������']);?></div>
			</div>
            <div class="panel panel-default-3">
                <div class="panel-heading">��������, ��</div>
                <div class="panel-body"><?=WeightFormat($arResult['REQUEST']['�����'], false);?> x <?=WeightFormat($arResult['REQUEST']['������'], false);?> x <?=WeightFormat($arResult['REQUEST']['������'], false);?> ��</div>
                
            </div>
            <div class="panel panel-default-3">
                <div class="panel-heading">����. ����������</div>
                <div class="panel-body"><?=strlen($arResult['REQUEST']['���������������������']) ? $arResult['REQUEST']['���������������������'] : '&nbsp;';?></div>
            </div>
        </div>
    </div>
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
            "TEST_MODE" => "N"
        ),
        false
    );
}
?>