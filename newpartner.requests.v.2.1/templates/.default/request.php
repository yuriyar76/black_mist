<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
?>
<h2 class="partner"><?=$arResult['TITLE'];?></h2>
<?
if (count($arResult["ERRORS"]) > 0) 
{
	echo '<p class="red">'.implode('</br>',$arResult["ERRORS"]).'</p>';
}
if (count($arResult["MESSAGE"]) > 0)
{
	echo '<p class="green">'.implode('</br>',$arResult["MESSAGE"]).'</p>';
}
if (count($arResult["WARNINGS"]) > 0)
{
	echo '<p class="orange">'.implode('</br>',$arResult["WARNINGS"]).'</p>';
}
if (($arResult['REQUEST']) && ($arResult['OPEN']))
{
	?>
    <p>Статус: <strong><?=$arResult['REQUEST']['PROPERTY_STATE_VALUE'];?></strong></p>
    <table width="960" cellpadding="5" cellspacing="0" border="1" bordercolor="#CCCCCC" class="invoice">
        <tbody>
            <tr>
                <td rowspan="5" class="vertical_td" width="50">Отправитель</td>
                <td width="300">
                    <label for="name_sender">ФИО отправителя</label>
                    <strong><?=$arResult['REQUEST']['PROPERTY_NAME_SENDER_VALUE'];?></strong>
                </td>
                <td rowspan="2" width="250">
                    <label for="phone_sender">Телефон</label>
                    <strong><?=$arResult['REQUEST']['PROPERTY_PHONE_SENDER_VALUE'];?></strong>
                </td>
                <td rowspan="9" width="5">&nbsp;</td>
                <td rowspan="2">
                    <label for="courier_from">Интервал приезда курьера</label>
                    <strong><?=$arResult['REQUEST']['DATE_COURIER'];?></strong>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="">Компания-отправитель</label>
                    <strong><?=$arResult['REQUEST']['PROPERTY_COMPANY_SENDER_VALUE'];?></strong>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="">Город</label>
                    <strong><?=$arResult['REQUEST']['PROPERTY_CITY_SENDER'];?></strong>
                </td>
                <td>
                    <label for="">Индекс</label>
                    <strong><?=$arResult['REQUEST']['PROPERTY_INDEX_SENDER_VALUE'];?></strong>
                </td>
                <td rowspan="2">
                    <label for="">Тип отправления</label>
                    <strong><?=$arResult['REQUEST']['PROPERTY_TYPE_VALUE'];?></strong>
                </td>
            </tr>
            <tr>
                <td rowspan="2" colspan="2">
                    <label for="">Адрес</label>
                    <strong><?=$arResult['REQUEST']['PROPERTY_ADRESS_SENDER_VALUE'];?></strong>
                </td>
            </tr>
            <tr>
                <td rowspan="2">
                    <label for="">Спец. инструкции</label>
                    <strong><?=$arResult['REQUEST']['PROPERTY_INSTRUCTIONS_VALUE'];?></strong>
                </td>
            </tr>
            <tr>
                <td rowspan="4" class="vertical_td">Получатель</td>
                <td>
                    <label for="">ФИО получателя</label>
                    <strong><?=$arResult['REQUEST']['PROPERTY_NAME_RECIPIENT_VALUE'];?></strong>
                </td>
                <td rowspan="2">
                    <label for="">Телефон</label>
                    <strong><?=$arResult['REQUEST']['PROPERTY_PHONE_RECIPIENT_VALUE'];?></strong>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="">Компания-получатель</label>
                    <strong><?=$arResult['REQUEST']['PROPERTY_COMPANY_RECIPIENT_VALUE'];?></strong>
                </td>
                <td>
                    <label for="">Количество мест</label>
                    <strong><?=$arResult['REQUEST']['PROPERTY_PLACES_VALUE'];?></strong>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="">Город</label>
                    <strong><?=$arResult['REQUEST']['PROPERTY_CITY_RECIPIENT'];?></strong>
                </td>
                <td>
                    <label for="">Индекс</label>
                    <strong><?=$arResult['REQUEST']['PROPERTY_INDEX_RECIPIENT_VALUE'];?></strong>
                </td>
                <td>
                    <label for="">Вес</label>
                    <strong><?=WeightFormat($arResult['REQUEST']['PROPERTY_WEIGHT_VALUE']);?></strong>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <label for="">Адрес</label>
                    <strong><?=$arResult['REQUEST']['PROPERTY_ADRESS_RECIPIENT_VALUE'];?></strong>
                </td>
                <td>
                    <label for="">Объемный вес</label>
                    <strong><?=WeightFormat($arResult['REQUEST']['PROPERTY_OB_WEIGHT_VALUE']);?></strong>
                </td>
            </tr>
        </tbody>
    </table>
	<?
}
?>