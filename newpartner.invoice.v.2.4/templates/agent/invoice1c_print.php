<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
if (($arResult['OPEN']) && ($arResult['REQUEST']))
{
	?>
    <div class="print_block">
       <?for ($i = 0; $i <= 1; $i++):?>
        <div class="print_head_block">
            <div class="img_print_head">
                <img width="286" height="66" alt="" src="<?=$arResult['LOGO_PRINT'];?>">
            </div>
            <div class="adress_print_head">
                <?=$arResult['ADRESS_PRINT'];?>
            </div>
            <div class="number"><?=$arResult['REQUEST']['НомерНакладной'];?></div>
            <script type="text/javascript">
                $(document).ready(function() {
					JsBarcode("#barcode_<?=$i;?>", "<?=$arResult['REQUEST']['НомерНакладной'];?>", {
					  format: "CODE39",
					  width: 1.8,
					  height: 60,
					  displayValue: false
					});
                });
            </script>
            <svg id="barcode_<?=$i;?>" class="target"></svg>
        </div>
        <table cellpadding="0" cellspacing="0" border="1" bordercolor="#333333">
            <tbody>
                <tr>
                    <td rowspan="5" width="30" bgcolor="#f4ecc5" style="vertical-align:middle;">
                        <div style="width:30px; height:200px;">
                            <img width="30" height="200" alt="Отправитель" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l1.png">
                        </div>
                    </td>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <span class="label">Фамилия Отправителя / Shipper's Last Name</span>
                            <span class="value"><?=$arResult['REQUEST']['ФамилияОтправителя'];?></span>
                        </div>
                    </td>
                    <td width="220" rowspan="2">
                        <div style="width:220px; height:80px;">
                            <span class="label">Телефон / Phone</span>
                            <span class="value"><?=$arResult['REQUEST']['ТелефонОтправителя'];?></span>
                        </div>
                    </td>
                    <td rowspan="5" width="30" bgcolor="#d6ffcc" style="vertical-align:middle;">
                        <div style="width:30px; height:200px;">
                            <img width="30" height="200" alt="Условия доставки" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l3.png">
                        </div>
                    </td>
                    <td width="220" rowspan="2">
                        <div style="width:220px; height:80px;">
                            <span class="label">&nbsp;</span>
                            <span class="value"><?=$arResult['REQUEST']['ПризнакТипДоставки'];?></span>
                        </div>
                    </td>
                    <td rowspan="5" width="30" bgcolor="#d6ffcc" style="vertical-align:middle;">
                        <div style="width:30px; height:200px;">
                            <img width="30" height="200" alt="Условия оплаты" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l4.png">
                        </div>
                    </td>
                    <td width="142" rowspan="3">
                        <div style="width:142px; height:120px;">
                            <span class="label">Оплачивает</span>
                            <span class="value"><?=$arResult['REQUEST']['ПризнакПлательщик'];?></span>
                        </div>
                    </td>
                </tr>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <span class="label">Компания-Отправитель / Shipping Company</span>
                            <span class="value" style="font-size: 11pt; line-height: 0.85;"><?=(strlen($arResult['REQUEST']['КомпанияОтправителя'])) ? $arResult['REQUEST']['КомпанияОтправителя'] : $arResult['REQUEST']['ВыборОтправителя'];?></span>
                        </div>
                    </td>
                <tr>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <span class="label">Страна / Country</span>
                            <span class="value"><?=$arResult['REQUEST']['СтранаОтправителя'];?></span>
                        </div>
                    </td>
                    <td width="220">
                        <div style="width:220px; height:40px;">
                            <span class="label">Область / State</span>
                            <span class="value"><?=$arResult['REQUEST']['ОбластьОтправителя'];?></span>
                        </div>
                    </td>
                    <td width="220" rowspan="3">
                        <div style="width:220px; height:120px;">
                            <span class="label">Доставить</span>
                            <span class="value"><?=$arResult['REQUEST']['СпециальныеУсловия'];?></span>
                            <span class="label">Доставить в дату</span>
                            <span class="value"></span>
                            <span class="label">Доставить до часа</span>
                            <span class="value"></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <span class="label">Город / Sity</span>
                            <span class="value"><?=$arResult['REQUEST']['ГородОтправителя'];?></span>
                        </div>
                    </td>
                    <td width="220">
                        <div style="width:220px; height:40px;">
                            <span class="label">Индекс / Postal Code</span>
                            <span class="value"><?=$arResult['REQUEST']['ИндексОтправителя'];?></span>
                        </div>
                    </td>
                    <td width="142" rowspan="2">
                        <div style="width:142px; height:80px;">
                            <span class="label">Оплата</span>
                            <span class="value"><?=$arResult['REQUEST']['ПризнакТипОплаты'];?></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td width="600" colspan="2">
                        <div style="width:600px; height:40px;">
                            <span class="label">Адрес / Street Address</span>
                            <span class="value" style="font-size: 11pt; line-height: 0.85;"><?=$arResult['REQUEST']['АдресОтправителя'];?></span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <table cellpadding="0" cellspacing="0" border="1" bordercolor="#333333" style="margin-top:-1px;">
            <tbody>
                <tr>
                    <td rowspan="5" width="30" bgcolor="#f4ecc5" style="vertical-align:middle;">
                        <div style="width:30px; height:200px;">
                            <img width="30" height="200" alt="Получатель" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l2.png">
                        </div>
                    </td>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <span class="label">Фамилия Получателя / Consignee's Last Name</span>
                            <span class="value"><?=$arResult['REQUEST']['ФамилияПолучателя'];?></span>
                        </div>
                    </td>
                    <td width="220" rowspan="2">
                        <div style="width:220px; height:80px;">
                            <span class="label">Телефон / Phone</span>
                            <span class="value"><?=$arResult['REQUEST']['ТелефонПолучателя'];?></span>
                        </div>
                    </td>
                    <td colspan="3" rowspan="3" width="425">
                        <div style="width:425px; height:120px;">
                            <span class="label">СПЕЦИАЛЬНЫЕ ИНСТРУКЦИИ / SPECIAL INSTRUCTIONS</span>
                            <span class="value"><?=$arResult['REQUEST']['СпециальныеИнструкции'];?></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <span class="label">Компания-Получатель / Consignee Company</span>
                            <span class="value" style="font-size: 11pt; line-height: 0.85;"><?=(strlen($arResult['REQUEST']['КомпанияПолучателя'])) ? $arResult['REQUEST']['КомпанияПолучателя'] : $arResult['REQUEST']['ВыборПолучателя'];?></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <span class="label">Страна / Country</span>
                            <span class="value"><?=$arResult['REQUEST']['СтранаПолучателя'];?></span>
                        </div>
                    </td>
                    <td width="220">
                        <div style="width:220px; height:40px;">
                            <span class="label">Область / State</span>
                            <span class="value"><?=$arResult['REQUEST']['ОбластьПолучателя'];?></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <span class="label">Город / Sity</span>
                            <span class="value"><?=$arResult['REQUEST']['ГородПолучателя'];?></span>
                        </div>
                    </td>
                    <td width="220">
                        <div style="width:220px; height:40px;">
                            <span class="label">Индекс / Postal Code</span>
                            <span class="value"><?=$arResult['REQUEST']['ИндексПолучателя'];?></span>
                        </div>
                    </td>
                    <td width="140">
                        <div style="width:140px; height:40px;">
                            <span class="label">Тариф за услуги</span>
                            <span class="value"><?=$arResult['REQUEST']['СуммаКОплате'];?></span>
                        </div>
                    </td>
                    <td width="140">
                        <div style="width:140px; height:40px;">
                            <span class="label">Страховой тариф</span>
                            <span class="value"><?=$arResult['REQUEST']['СтраховойТариф'];?></span>
                        </div>
                    </td>
                    <td width="140">
                        <div style="width:140px; height:40px;">
                            <span class="label">Итого к оплате</span>
                            <span class="value"><?=$arResult['REQUEST']['ИтогоКОплате'];?></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td width="600" colspan="2">
                        <div style="width:600px; height:40px;">
                            <span class="label">Адрес / Street Address</span>
                            <span class="value" style="font-size: 11pt; line-height: 0.85;"><?=$arResult['REQUEST']['АдресПолучателя'];?></span>
                        </div>
                    </td>
                    <td colspan="3" width="425">
                        <div style="width:425px; height:40px;">
                            <span class="label">Фамилия и подпись отправителя / Shippers Signature</span>
                            <span class="value" style="font-size: 10pt;line-height: 0.95;padding-right: 160px;"><?=$arResult['REQUEST']['ФамилияОтправителя'];?></span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <table cellpadding="0" cellspacing="0" border="1" bordercolor="#333333" style="margin-top:-1px;">
            <tbody>
                <tr>
                    <td rowspan="7" width="30" bgcolor="#ccffff" valign="middle" style="vertical-align:middle;">
                        <div style="height:182px; width:30px;">
                            <img width="30" height="182" alt="Описание отправления" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l5.png">
                        </div>
                    </td>
                    <td colspan="3" width="298">
                        <div style="width:298px; height:25px;">
                            
                        </div>
                    </td>
                    <td width="80" align="center">
                        <div style="width:80px; height:25px;">
                            <span class="label">Мест<br>Pieces</span>
                        </div>
                    </td>
                    <td width="80" align="center">
                        <div style="width:80px; height:25px;">
                            <span class="label">Вес<br>Weight</span>
                        </div>
                    </td>
                    <td width="140" align="center">
                        <div style="width:140px; height:25px;">
                            <span class="label">Габариты (см х см х см)<br>Dimensions (cm x cm x cm)</span>
                        </div>
                    </td>
                    <td colspan="2" rowspan="3" width="425"><div style="width:425px; height:65px;"><span class="label">Принято курьером</span></div></td>
                </tr>
                <tr>
                    <td colspan="3"><div style="width:298px; height:20px;"><span class="value" style="font-size:7pt;"><?=$arResult['REQUEST']['Габариты']['Габарит_1']['Габарит'];?></span></div></td>
                    <td><div style="width:80px; height:20px;"><span class="value"><?=$arResult['REQUEST']['Габариты']['Габарит_1']['КоличествоМест'];?></span></div></td>
                    <td><div style="width:80px; height:20px;"><span class="value"><?=$arResult['REQUEST']['Габариты']['Габарит_1']['ВесОтправления'];?></span></div></td>
                    <td><div style="width:140px; height:20px;"><span class="value"><?=$arResult['REQUEST']['Габариты']['Габарит_1']['sizes'];?></span></div></td>
                </tr>
                <tr>
                    <td colspan="3"><div style="width:298px; height:20px;"><span class="value" style="font-size:7pt;"><?=$arResult['REQUEST']['Габариты']['Габарит_2']['Габарит'];?></span></div></td>
                    <td><div style="width:80px; height:20px;"><span class="value"><?=$arResult['REQUEST']['Габариты']['Габарит_2']['КоличествоМест'];?></span></div></td>
                    <td><div style="width:80px; height:20px;"><span class="value"><?=$arResult['REQUEST']['Габариты']['Габарит_2']['ВесОтправления'];?></span></div></td>
                    <td><div style="width:140px; height:20px;"><span class="value"><?=$arResult['REQUEST']['Габариты']['Габарит_2']['sizes'];?></span></div></td>
                </tr>
                <tr>
                    <td colspan="3"><div style="width:298px; height:20px;"><span class="value" style="font-size:7pt;"><?=$arResult['REQUEST']['Габариты']['Габарит_3']['Габарит'];?></span></div></td>
                    <td><div style="width:80px; height:20px;"><span class="value"><?=$arResult['REQUEST']['Габариты']['Габарит_3']['КоличествоМест'];?></span></div></td>
                    <td><div style="width:80px; height:20px;"><span class="value"><?=$arResult['REQUEST']['Габариты']['Габарит_3']['ВесОтправления'];?></span></div></td>
                    <td><div style="width:140px; height:20px;"><span class="value"><?=$arResult['REQUEST']['Габариты']['Габарит_3']['sizes'];?></span></div></td>
                    <td rowspan="3" width="212"><div style="width:212px; height:60px;"><span class="label">ДОЛЖНОСТЬ</span></div></td>
                    <td rowspan="3" width="212"><div style="width:212px; height:60px;"><span class="label">ФАМИЛИЯ ПОЛУЧАТЕЛЯ</span></div></td>
                </tr>
                <tr>
                    <td colspan="3"><div style="width:298px; height:20px;"><span class="value" style="font-size:7pt;"><?=$arResult['REQUEST']['Габариты']['Габарит_4']['Габарит'];?></span></div></td>
                    <td><div style="width:80px; height:20px;"><span class="value"><?=$arResult['REQUEST']['Габариты']['Габарит_4']['КоличествоМест'];?></span></div></td>
                    <td><div style="width:80px; height:20px;"><span class="value"><?=$arResult['REQUEST']['Габариты']['Габарит_4']['ВесОтправления'];?></span></div></td>
                    <td><div style="width:140px; height:20px;"><span class="value"><?=$arResult['REQUEST']['Габариты']['Габарит_4']['sizes'];?></span></div></td>
                </tr>
                <tr>
                    <td colspan="3"><div style="width:298px; height:20px;"><span class="value" style="font-size:7pt;"><?=$arResult['REQUEST']['Габариты']['Габарит_5']['Габарит'];?></span></div></td>
                    <td><div style="width:80px; height:20px;"><span class="value"><?=$arResult['REQUEST']['Габариты']['Габарит_5']['КоличествоМест'];?></span></div></td>
                    <td><div style="width:80px; height:20px;"><span class="value"><?=$arResult['REQUEST']['Габариты']['Габарит_5']['ВесОтправления'];?></span></div></td>
                    <td><div style="width:140px; height:20px;"><span class="value"><?=$arResult['REQUEST']['Габариты']['Габарит_5']['sizes'];?></span></div></td>
                </tr>
                <tr>
                    <td width="98">
                        <div style="height:50px; width:98px;">
                            <span class="label">Мест<br>Pieses</span>
                            <span class="value"><?=$arResult['REQUEST']['КоличествоМест'];?></span>
                        </div>
                    </td>
                    <td width="98">
                        <div style="height:50px; width:98px">
                            <span class="label">Вес<br>Weight</span>
                            <span class="value"><?=$arResult['REQUEST']['ВесОтправления'];?></span>
                        </div>
                    </td>
                    <td width="98">
                        <div style="height:50px; width:98px;"><span class="label">Объемный вес<br>Vol. WT</span>
                            <span class="value"><?=$arResult['REQUEST']['ВесОтправленияОбъемный'];?></span>
                        </div>
                    </td>
                    <td colspan="2"><div style="height:50px;"><span class="label">Контр. взвеш.<br>Control WT</span></div></td>
                    <td>
                        <div style="height:50px;"><span class="label">Объявл. стоимость<br>Declared Value</span>
                            <span class="value"><?=$arResult['REQUEST']['ОбъявленнаяСтоимость'];?></span>
                        </div>
                    </td>
                    <td><div style="height:50px;"><span class="label">ПОДПИСЬ ПОЛУЧАТЕЛЯ</span></div></td>
                    <td><div style="height:50px;"><span class="label">ДАТА И ВРЕМЯ ДОСТАВКИ</span></div></td>
                </tr>
            </tbody>
        </table>
        <?endfor;?>
    </div>
    <?
}
?>