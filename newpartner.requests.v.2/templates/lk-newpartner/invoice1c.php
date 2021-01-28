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
            <h4>Отправитель</h4>
            <div class="panel panel-default-1">
				<div class="panel-heading">Компания</div>
				<div class="panel-body"><?=$arResult['REQUEST']['КомпанияОтправителя'];?></div>
			</div>
            <div class="panel panel-default-1">
                <div class="panel-heading">Фамилия</div>
                <div class="panel-body"><?=strlen($arResult['REQUEST']['ФамилияОтправителя']) ? $arResult['REQUEST']['ФамилияОтправителя'] : '&nbsp;';?></div>
            </div>
            <div class="panel panel-default-1">
                <div class="panel-heading">Телефон</div>
                <div class="panel-body"><?=$arResult['REQUEST']['ТелефонОтправителя'];?></div>
            </div>
            <div class="panel panel-default-1">
                <div class="panel-heading">Город</div>
                <div class="panel-body"><?=$arResult['REQUEST']['ГородОтправителя'];?></div>
            </div>
            <div class="panel panel-default-1">
                <div class="panel-heading">Индекс</div>
                <div class="panel-body"><?=strlen($arResult['REQUEST']['ИндексОтправителя']) ? $arResult['REQUEST']['ИндексОтправителя'] : '&nbsp;';?></div>
            </div>
            <div class="panel panel-default-1">
                <div class="panel-heading">Адрес</div>
                <div class="panel-body"><?=$arResult['REQUEST']['АдресОтправителя'];?></div>
            </div>
        </div>
        <div class="col-md-4">
            <h4>Получатель</h4>
            <div class="panel panel-default">
                <div class="panel-heading">Компания</div>
                <div class="panel-body"><?=$arResult['REQUEST']['КомпанияПолучателя'];?></div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">Фамилия</div>
                <div class="panel-body"><?=strlen($arResult['REQUEST']['ФамилияПолучателя']) ? $arResult['REQUEST']['ФамилияПолучателя'] : '&nbsp;';?></div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">Телефон</div>
                <div class="panel-body"><?=$arResult['REQUEST']['ТелефонПолучателя'];?></div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">Город</div>
                <div class="panel-body"><?=$arResult['REQUEST']['ГородПолучателя'];?></div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">Индекс</div>
                <div class="panel-body"><?=strlen($arResult['REQUEST']['ИндексПолучателя']) ? $arResult['REQUEST']['ИндексПолучателя'] : '&nbsp;';?></div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">Адрес</div>
                <div class="panel-body"><?=$arResult['REQUEST']['АдресПолучателя'];?></div>
            </div>
        </div>
        <div class="col-md-4">
        	<h4>Описание отправления</h4>
            <div class="panel panel-default-3">
                <div class="panel-heading">Дата и временной интервал забора</div>
				<div class="panel-body"><?=strlen($arResult['REQUEST']['DATETIME']) ? $arResult['REQUEST']['DATETIME'] : '&nbsp;';?></div>
            </div>
            <div class="panel panel-default-3">
                <div class="panel-heading">Тип отправления</div>
				<div class="panel-body"><?=strlen($arResult['REQUEST']['PROPERTY_TYPE_VALUE']) ? $arResult['REQUEST']['PROPERTY_TYPE_VALUE'] : '&nbsp;';?></div>
            </div>
			<div class="panel panel-default-3">
            	<div class="panel-heading">Количество мест</div>
                <div class="panel-body"><?=$arResult['REQUEST']['КоличествоМест'];?></div>
			</div>
			<div class="panel panel-default-3">
            	<div class="panel-heading">Вес</div>
                <div class="panel-body"><?=WeightFormat($arResult['REQUEST']['ВесОтправления']);?></div>
			</div>
            <div class="panel panel-default-3">
                <div class="panel-heading">Габариты, см</div>
                <div class="panel-body"><?=WeightFormat($arResult['REQUEST']['Длина'], false);?> x <?=WeightFormat($arResult['REQUEST']['Ширина'], false);?> x <?=WeightFormat($arResult['REQUEST']['Высота'], false);?> см</div>
                
            </div>
            <div class="panel panel-default-3">
                <div class="panel-heading">Спец. инструкции</div>
                <div class="panel-body"><?=strlen($arResult['REQUEST']['СпециальныеИнструкции']) ? $arResult['REQUEST']['СпециальныеИнструкции'] : '&nbsp;';?></div>
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