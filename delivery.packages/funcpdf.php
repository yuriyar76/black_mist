<?$_SERVER['DOCUMENT_ROOT'] = "/var/www/admin/www/delivery-russia.ru";
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
require_once( $_SERVER["DOCUMENT_ROOT"]."/bitrix/_black_mist/mpdf7/vendor/autoload.php" );

error_reporting(0);


function encodeArray(array $array, string $sourceEncoding, string $destinationEncoding = 'UTF-8'): array
{
    if($sourceEncoding === $destinationEncoding){
        return $array;
    }
    array_walk_recursive($array,
        function(&$array) use ($sourceEncoding, $destinationEncoding) {
            $array = mb_convert_encoding($array, $destinationEncoding, $sourceEncoding);
        }
    );
    return $array;
}

/* увидим  кол-во записей */
// перечисляем все виды описаний (любое количество)
//  $m     = json_decode($arResult[REQUEST][fullArray], True);
//	$cnt   = count(json_decode($arResult[REQUEST][fullArray], True));
//	$cooef = (int)$cnt / 5;
/* ----------- */
//
//for ($i = 0; $i <= $cooef; $i++){
//}
// для Сухого
function MakeZakazPDF2($arResult)
{
	// 22.08.2019   Здесь не пишется никаких записей в поле описание

	// показать
	//file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/filename000test.txt', print_r($arResult, true), FILE_APPEND);

	// номер нашел накладной
	// number_internal_array][1][0]

	/* увидим  кол-во записей */
	// перечисляем все виды описаний (любое количество)
		$m     = json_decode($arResult['REQUEST']['fullArray'], True);
		$cnt   = count(json_decode($arResult['REQUEST']['fullArray'], True));
		$cooef = (int)(ceil(($cnt / 5)));

		//$a = array ($cooef, $cnt, $m);
		//file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/arraylist_20000.txt', print_r($a, true), FILE_APPEND);
	/*  */

    $PDF_NAME = $arResult['REQUEST']['number_nakl'].".pdf";
    $BE_DIR = $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main", "upload_dir")."/pdf/";
    $mpdf = new \Mpdf\Mpdf([
                  //  'debug' => true,
                  //  'allow_output_buffering' => true,
                    'table_error_report' => false,
                    'allow_html_optional_endtags' => false,
                    'ignore_invalid_utf8' => true,
                    'mode' => 'utf-8',
                ]);

$mpdf->SetTitle('Накладная № '.$arResult['REQUEST']['number_nakl']);
$mpdf->allow_charset_conversion = true;
$mpdf->charset_in='windows-1251'; /*не забываем про русский*/
//$mpdf->charset_in = 'utf-8';

//echo "<pre>";print_r($_SERVER[DOCUMENT_ROOT]);echo "</pre>";
$mpdf->showImageErrors = true;
$mpdf->list_indent_first_level = 0;

//$logo = $arResult[LOGO_PRINT];
//$product_code_39 = "<img alt='code 39 bar code' src='".$_SERVER[DOCUMENT_ROOT]."/bitrix/_black_mist/mpdf7/barcode.php?codetype=Code39&size=60&text=".$arResult[REQUEST][number_nakl]."&print=false' />";
$company = $arResult['REQUEST']['COMPANY_SENDER'];
$company1 = $arResult['REQUEST']['COMPANY_RECIPIENT'];

if($arResult['REQUEST']['gab_1_name']!='')
    {
        $gab_1_name = $arResult['REQUEST']['gab_1_name'];
    }

if($arResult['REQUEST']['gab_2_name']!='')
    {
        $gab_2_name = $arResult['REQUEST']['gab_2_name'];
    }

if($arResult['REQUEST']['gab_3_name']!='')
    {
        $gab_3_name = $arResult['REQUEST']['gab_3_name'];
    }
if($arResult['REQUEST']['gab_4_name']!='')
    {
        $gab_3_name = $arResult['REQUEST']['gab_4_name'];
    }
if($arResult['REQUEST']['gab_5_name']!='')
    {
        $gab_3_name = $arResult['REQUEST']['gab_5_name'];
    }

	// $arPDF[REQUEST][IN_DATE_DELIVERY] = NewQuotes($_POST['IN_DATE_DELIVERY']);
	// $arPDF[REQUEST][IN_TIME_DELIVERY] = NewQuotes($_POST['IN_TIME_DELIVERY']);
	// разобьем ее на немер и тдоп номер
	//$NumberInvoice   = preg_replace ("/(.*)-(.*)$/", "$1", $arResult[REQUEST][number_nakl]);
	// наше текущее дополнение
	//$DopInvoice      = preg_replace ("/(.*)-(.*)$/", "$2", $arResult[REQUEST][number_nakl]);
	// дополнительная информация
	//$instruction  = "Заявка N: ".$NumberInvoice." Доп.".$DopInvoice."<br/>";
	//$instruction .= "Вызов курьера. дата:  ".$arResult[REQUEST][IN_DATE_DELIVERY]."<br/>";
	//$instruction .= "               время: ".$arResult[REQUEST][IN_TIME_DELIVERY]."<br/>";
	// -------------------------
    // ничего добавлять не надо редактируемая накладная
	//if ($arResult[REQUEST][IN_DATE_DELIVERY] == '') {$instruction = '';}

$html = '';
for ($i = 0; $i <= 1; $i++){
$html .= '<style>
 @media print  {
           .print_block {
                page-break-inside: avoid;
            }
        }
        .print_block {
            page-break-inside: avoid;
        }
.label {
color: #536ac2;
font-size: 7pt;
display: block;
}
.value {
font-size: 12pt;
font-weight: bold;
display: block;
}
td{padding:5px;vertical-align: top;border: 1px solid #333333}
table{font-family: Arial, Helvetica, sans-serif;}
</style>
<div class="print_block">
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td style="border:0;vertical-align:middle;" width="286"><p style="font-size: 28px;font-weight: bold;text-align: left;">'.$arResult['REQUEST']['number_nakl'].'</p></td>
            <td style="border:0;vertical-align:middle" align="center">
                <barcode code="'.$arResult['REQUEST']['number_nakl'].'" type="C39" />
            </td>
            <td style="border:0;vertical-align:middle;" width="286">
                <p><img width="286" style="float:right" height="66" alt="" src="'.$_SERVER['DOCUMENT_ROOT'].$arResult['LOGO_PRINT'].'"></p>
                <p style="font-size: 7pt;">'.$arResult['ADRESS_PRINT'].'</p></td>
        </tr>
        </table>
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
                            <p class="label">Фамилия Отправителя / Shipper`s Last Name</p>
                            <p class="value">'.$arResult['REQUEST']['NAME_SENDER'].'</p>
                        </div>
                    </td>
                    <td width="250" rowspan="2">
                        <div style="width:250px; height:80px;">
                            <p class="label">Телефон / Phone</p>
                            <p class="value">'.$arResult['REQUEST']['PHONE_SENDER'].'</p>
                        </div>
                    </td>
                    <td rowspan="5" width="30" bgcolor="#d6ffcc" style="vertical-align:middle;">
                        <div style="width:30px; height:200px;">
                            <img width="30" height="200" alt="Условия доставки" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l3.png">
                        </div>
                    </td>
                    <td width="220" rowspan="2">
                        <div style="width:220px; height:80px;">
                            <p class="label">&nbsp;</p>
                            <p class="value">'.$arResult['REQUEST']['TYPE_DELIVERY'].'</p>
                        </div>
                    </td>
                    <td rowspan="5" width="30" bgcolor="#d6ffcc" style="vertical-align:middle;">
                        <div style="width:30px; height:200px;">
                            <img width="30" height="200" alt="Условия оплаты" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l4.png">
                        </div>
                    </td>
                    <td width="142" rowspan="3">
                        <div style="width:142px; height:120px;">
                            <p class="label"><!-- Оплачивает --></p>
                            <p class="value"></p>
                            <p class="value"></p>
                        </div>
                    </td>
                    <tr>
                        <td width="380">
                            <div style="width:380px; height:40px;">
                                <p class="label">Компания-Отправитель / Shipping Company</p>
                                <p class="value" style="font-size: 11pt; line-height: 0.85;">'.$company.'</p>
                            </div>
                        </td>
                        <tr>
                            <td width="380">
                                <div style="width:380px; height:40px;">
                                    <p class="label">Страна / Country</p>
                                    <p class="value">'.$arResult['REQUEST']['c_sender'].'</p>
                                </div>
                            </td>
                            <td width="220">
                                <div style="width:220px; height:40px;">
                                    <p class="label">Область / State</p>
                                    <p class="value">'.$arResult['REQUEST']['o_sender'].'</p>
                                </div>
                            </td>
                            <td width="220" rowspan="3">
                                <div style="width:220px; height:120px;">
                                    <p class="label">Доставить</p>
                                    <p class="value">'.$arResult['REQUEST']['WHO_DELIVERY'].'</p>
                                    <p class="label">Доставить в дату</p>
                                    <p class="value"></p>
                                    <p class="label">Доставить до часа</p>
                                    <p class="value"></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="380">
                                <div style="width:380px; height:40px;">
                                    <p class="label">Город / Sity</p>
                                    <p class="value">'.$arResult['REQUEST']['s_sender'].'</p>
                                </div>
                            </td>
                            <td width="220">
                                <div style="width:220px; height:40px;">
                                    <p class="label">Индекс / Postal Code</p>
                                    <p class="value">'.$arResult['REQUEST']['INDEX_SENDER'].'</p>
                                </div>
                            </td>
                            <td width="142" rowspan="2">
                                <div style="width:142px; height:80px;">
                                    <p class="label"><!-- Оплата --></p>
                                    <p class="value"></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="600" colspan="2">
                                <div style="width:600px; height:40px;">
                                    <p class="label">Адрес / Street Address</p>
                                    <p class="value" style="font-size: 11pt; line-height: 0.85;">'.$arResult['REQUEST']['ADRESS_SENDER'].'</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table width="100%" cellpadding="0" cellspacing="0" border="1" bordercolor="#333333" style="margin-top:-1px;">
                    <tbody>
                        <tr>
                            <td rowspan="5" width="30" bgcolor="#f4ecc5" style="vertical-align:middle;">
                                <div style="width:30px; height:200px;">
                                    <img width="30" height="200" alt="Получатель" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l2.png">
                                </div>
                            </td>
                            <td width="380">
                                <div style="width:380px; height:40px;">
                                    <p class="label">Фамилия Получателя / Consignee`s Last Name</p>
                                    <p class="value">'.$arResult['REQUEST']['NAME_RECIPIENT'].'</p>
                                </div>
                            </td>
                            <td width="220" rowspan="2">
                                <div style="width:220px; height:80px;">
                                    <p class="label">Телефон / Phone</p>
                                    <p class="value">'.$arResult['REQUEST']['PHONE_RECIPIENT'].'</p>
                                </div>
                            </td>
                            <td colspan="3" rowspan="3" width="425">
                                <div style="width:425px; height:120px;">
                                    <p class="label">СПЕЦИАЛЬНЫЕ ИНСТРУКЦИИ / SPECIAL INSTRUCTIONS</p>
                                    <p class="value">'.$arResult['REQUEST']['INSTRUCTIONS'].'</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="380">
                                <div style="width:380px; height:40px;">
                                    <p class="label">Компания-Получатель / Consignee Company</p>
                                    <p class="value" style="font-size: 11pt; line-height: 0.85;">'.$company1.'</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="380">
                                <div style="width:380px; height:40px;">
                                    <p class="label">Страна / Country</p>
                                    <p class="value">'.$arResult['REQUEST']['c_recepient'].'</p>
                                </div>
                            </td>
                            <td width="220">
                                <div style="width:220px; height:40px;">
                                    <p class="label">Область / State</p>
                                    <p class="value">'.$arResult['REQUEST']['o_recepient'].'</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="380">
                                <div style="width:380px; height:40px;">
                                    <p class="label">Город / Sity</p>
                                    <p class="value">'.$arResult['REQUEST']['s_recepient'].'</p>
                                </div>
                            </td>
                            <td width="220">
                                <div style="width:220px; height:40px;">
                                    <p class="label">Индекс / Postal Code</p>
                                    <p class="value">'.$arResult['REQUEST']['INDEX_RECIPIENT'].'</p>
                                </div>
                            </td>
                            <td width="140">
                                <div style="width:140px; height:40px;">
                                    <p class="label">Тариф за услуги</p>
                                    <p class="value">'.$arResult['REQUEST']['FOR_PAYMENT'].'</p>
                                </div>
                            </td>
                            <td width="140">
                                <div style="width:140px; height:40px;">
                                    <p class="label">Страховой тариф</p>
                                    <p class="value">'.$arResult['REQUEST']['PAYMENT_COD'].'</p>
                                </div>
                            </td>
                            <td width="140">
                                <div style="width:140px; height:40px;">
                                    <p class="label">Итого к оплате</p>
                                    <p class="value">'.$arResult['REQUEST']['COST'].'</p>
                                </div>
                            </td>
                        </tr>
                        <tr><td width="600" colspan="2">
                            <div style="width:600px; height:40px;">
                                <p class="label">Адрес / Street Address</p>
                                <p class="value" style="font-size: 11pt; line-height: 0.85;">'.$arResult['REQUEST']['ADRESS_RECIPIENT'].'</p>
                            </div>
                        </td>
                        <td colspan="3" width="425"><div style="width:425px; height:40px;"><p class="label">Фамилия отправителя / Shippers Signature</p><p class="value" style="font-size: 10pt;line-height: 0.95;padding-right: 160px;">'.$arResult['REQUEST']['NAME_SENDER'].'</p></div></td>
                    </tr>
                </tbody>
            </table>
            <table width="100%" cellpadding="0" cellspacing="0" border="1" bordercolor="#333333" style="margin-top:-1px;">
                <tbody>
                    <tr>
                        <td rowspan="7" width="30" bgcolor="#ccffff" valign="middle" style="vertical-align:middle;">
                            <div style="height:182px; width:30px;">
                                <img width="30" height="182" alt="Описание отправления" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l5.png">
                            </div>
                        </td>
                        <td colspan="3" width="298">
                            <div style="width:298px; height:15px;">
                            </div>
                        </td>
                        <td width="80" align="center">
                            <div style="width:80px; height:15px;">
                                <p class="label">Мест<br>Pieces</p>
                            </div>
                        </td>
                        <td width="80" align="center">
                            <div style="width:80px; height:15px;">
                                <p class="label">Вес<br>Weight</p>
                            </div>
                        </td>
                        <td width="140" align="center">
                            <div style="width:140px; height:21px;">
                                <p class="label">Габариты (см х см х см)<br>Dimensions (cm x cm x cm)</p>
                            </div>
                        </td>
                        <td colspan="2" rowspan="3" width="425"><div style="width:425px; height:65px;"><p class="label">Принято курьером</p></div></td>
                    </tr>
                    <tr>
                        <td colspan="3"><div style="width:298px; height:10px;"><p class="value" style="font-size:7pt;">'.$gab_1_name.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value"></p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value"></p></div></td>
                        <td><div style="width:140px; height:10px;"><p class="value"></p></div></td>
                    </tr>
                    <tr>
                        <td colspan="3"><div style="width:298px; height:10px;"><p class="value" style="font-size:7pt;">'.$gab_2_name.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value"></p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value"></p></div></td>
                        <td><div style="width:140px; height:10px;"><p class="value"></p></div></td>
                    </tr>
                    <tr>
                        <td colspan="3"><div style="width:298px; height:10px;"><p class="value" style="font-size:7pt;">'.$gab_3_name.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value"></p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value"></p></div></td>
                        <td><div style="width:140px; height:10px;"><p class="value"></p></div></td>
                        <td rowspan="3" width="212"><div style="width:212px; height:60px;"><p class="label">ДОЛЖНОСТЬ</p></div></td>
                        <td rowspan="3" width="212"><div style="width:212px; height:60px;"><p class="label">ФАМИЛИЯ ПОЛУЧАТЕЛЯ</p></div></td>
                        
                    </tr>
                    <tr>
                        <td colspan="3"><div style="width:298px; height:10px;"><p class="value" style="font-size:7pt;">'.$gab_4_name.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value"></p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value"></p></div></td>
                        <td><div style="width:140px; height:10px;"><p class="value"></p></div></td>
                    </tr>
                    <tr>
                        <td colspan="3"><div style="width:298px; height:10px;"><p class="value" style="font-size:7pt;">'.$gab_5_name.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value"></p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value"></p></div></td>
                        <td><div style="width:140px; height:10px;"><p class="value"></p></div></td>
                    </tr>
      
                    <tr>
                        <td width="98">
                            <div style="height:50px; width:98px;">
                                <p class="label">Мест<br>Pieses</p>
                                <p class="value"></p>
                            </div>
                        </td>
                        <td width="98">
                            <div style="height:50px; width:98px">
                                <p class="label">Вес<br>Weight</p>
                                <p class="value"></p>
                            </div>
                        </td>
                        <td width="98">
                            <div style="height:50px; width:98px;"><p class="label">Объемный вес<br>Vol. WT</p>
                            <p class="value"></p>
                        </div>
                    </td>
                    <td colspan="2"><div style="height:50px;"><p class="label">Контр. взвеш.<br>Control WT</p></div></td>
                    <td>
                        <div style="height:50px;"><p class="label">Объявл. стоимость<br>Declared Value</p>
                        <p class="value"></p>
                    </div>
                </td>
                <td><div style="height:50px;"><p class="label">ПОДПИСЬ ПОЛУЧАТЕЛЯ</p></div></td>
                <td><div style="height:50px;"><p class="label">ДАТА И ВРЕМЯ ДОСТАВКИ</p></div></td>
            </tr>
        </tbody>
    </table>
</div><p style="line-height:20px"></p>';
//if ($i!=1) {$html .= '<pagebreak>';}
}
//echo $html;
    $mpdf->WriteHTML($html);
    $mpdf->Output($BE_DIR.$PDF_NAME,"F");
    //$mpdf->Output();
}
function MakeZakazPDF($arResult)
{
	// * 22.08.2019   Здесь ровно пять описаний

	// показать
	//file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/filename000test.txt', print_r($arResult, true), FILE_APPEND);

	// номер нашел накладной
	// number_internal_array][1][0]

	//echo "<!--";
	//echo "<pre>";
	//print_r($arResult);
	//echo "<pre>";
	//echo "-->";

	/* увидим  кол-во записей */
	// перечисляем все виды описаний (любое количество)
		$m     = json_decode($arResult['REQUEST']['fullArray'], True);
		$cnt   = count(json_decode($arResult['REQUEST']['fullArray'], True));
		$cooef = (int)(ceil(($cnt / 5)));

		//$a = array ($cooef, $cnt, $m);
		//file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/arraylist_MakeZakazPDF.txt', print_r($a, true), FILE_APPEND);
	/*  */

    $PDF_NAME = $arResult['REQUEST']['number_nakl'].".pdf";
    $BE_DIR = $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main", "upload_dir")."/pdf/";
    $mpdf = new \Mpdf\Mpdf([
                  //  'debug' => true,
                  //  'allow_output_buffering' => true,
                    'table_error_report' => false,
                    'allow_html_optional_endtags' => false,
                    'ignore_invalid_utf8' => true,
                    'mode' => 'utf-8',
                ]);

$mpdf->SetTitle('Накладная № '.$arResult['REQUEST']['number_nakl']);
$mpdf->allow_charset_conversion = true;
$mpdf->charset_in='windows-1251'; /*не забываем про русский*/
//$mpdf->charset_in = 'utf-8';

//echo "<pre>";print_r($_SERVER[DOCUMENT_ROOT]);echo "</pre>";
$mpdf->showImageErrors = true;
$mpdf->list_indent_first_level = 0;

//$logo = $arResult[LOGO_PRINT];
//$product_code_39 = "<img alt='code 39 bar code' src='".$_SERVER[DOCUMENT_ROOT]."/bitrix/_black_mist/mpdf7/barcode.php?codetype=Code39&size=60&text=".$arResult[REQUEST][number_nakl]."&print=false' />";
$company = $arResult['REQUEST']['COMPANY_SENDER'];
$company1 = $arResult['REQUEST']['COMPANY_RECIPIENT'];

if($arResult['REQUEST']['gab_1_name']!='')
    {
        $gab_1_name = $arResult['REQUEST']['gab_1_name'];
    }
if($arResult['REQUEST']['gab_1_place']>0)
    {
        $gab_1_place = $arResult['REQUEST']['gab_1_place'];
    }
if($arResult['REQUEST']['gab_1_weight']>0){
    $gab_1_weight = $arResult['REQUEST']['gab_1_weight'];
}
if($arResult['REQUEST']['gab_1_sizes']!='0x0x0' AND $arResult['REQUEST']['gab_1_sizes']!='x0x0'){
    $gab_1_sizes = $arResult['REQUEST']['gab_1_sizes'];
}
if($arResult['REQUEST']['gab_2_name']!='')
    {
        $gab_2_name = $arResult['REQUEST']['gab_2_name'];
    }
if($arResult['REQUEST']['gab_2_place']>0)
    {
        $gab_2_place = $arResult['REQUEST']['gab_2_place'];
    }
if($arResult['REQUEST']['gab_2_weight']>0){
    $gab_2_weight = $arResult['REQUEST']['gab_2_weight'];
}
if($arResult['REQUEST']['gab_2_sizes']!='0x0x0' AND $arResult['REQUEST']['gab_2_sizes']!='x0x0'){
    $gab_2_sizes = $arResult['REQUEST']['gab_2_sizes'];
}
if($arResult['REQUEST']['gab_3_name']!='')
    {
        $gab_3_name = $arResult['REQUEST']['gab_3_name'];
    }
if($arResult['REQUEST']['gab_3_place']>0)
    {
        $gab_3_place = $arResult['REQUEST']['gab_3_place'];
    }
if($arResult['REQUEST']['gab_3_weight']>0){
    $gab_3_weight = $arResult['REQUEST']['gab_3_weight'];
}
if($arResult['REQUEST']['gab_3_sizes']!='0x0x0' AND $arResult['REQUEST']['gab_3_sizes']!='x0x0'){
    $gab_3_sizes = $arResult['REQUEST']['gab_3_sizes'];
}
if($arResult['REQUEST']['gab_4_name']!='')
    {
        $gab_4_name = $arResult['REQUEST']['gab_4_name'];
    }
if($arResult['REQUEST']['gab_4_place']>0)
    {
        $gab_4_place = $arResult['REQUEST']['gab_4_place'];
    }
if($arResult['REQUEST']['gab_4_weight']>0){
    $gab_4_weight = $arResult['REQUEST']['gab_4_weight'];
}
if($arResult['REQUEST']['gab_4_sizes']!='0x0x0' AND $arResult['REQUEST']['gab_4_sizes']!='x0x0'){
    $gab_4_sizes = $arResult['REQUEST']['gab_4_sizes'];
}
if($arResult['REQUEST']['gab_5_name']!='')
    {
        $gab_5_name = $arResult['REQUEST']['gab_5_name'];
    }
if($arResult['REQUEST']['gab_5_place']>0)
    {
        $gab_5_place = $arResult['REQUEST']['gab_5_place'];
    }
if($arResult['REQUEST']['gab_5_weight']>0){
    $gab_5_weight = $arResult['REQUEST']['gab_5_weight'];
}
if($arResult['REQUEST']['gab_5_sizes']!='0x0x0' AND $arResult['REQUEST']['gab_5_sizes']!='x0x0'){
    $gab_5_sizes = $arResult['REQUEST']['gab_5_sizes'];
}

$html = '';
for ($i = 0; $i <= 1; $i++){
$html .= '<style>
 @media print  {
           .print_block {
                page-break-inside: avoid;
            }
        }
        .print_block {
            page-break-inside: avoid;
        }
.label {
color: #536ac2;
font-size: 7pt;
display: block;
}
.value {
font-size: 12pt;
font-weight: bold;
display: block;
}
td{padding:5px;vertical-align: top;border: 1px solid #333333}
table{font-family: Arial, Helvetica, sans-serif;}
</style>
<div class="print_block">
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td style="border:0;vertical-align:middle;" width="286"><p style="font-size: 28px;font-weight: bold;text-align: left;">'.$arResult['REQUEST']['number_nakl'].'</p></td>
            <td style="border:0;vertical-align:middle" align="center">
                <barcode code="'.$arResult['REQUEST']['number_nakl'].'" type="C39" />
            </td>
            <td style="border:0;vertical-align:middle;" width="286">
                <p><img width="286" style="float:right" height="66" alt="" src="'.$_SERVER['DOCUMENT_ROOT'].$arResult['LOGO_PRINT'].'"></p>
                <p style="font-size: 7pt;">'.$arResult['ADRESS_PRINT'].'</p></td>
        </tr>
        </table>
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
                            <p class="label">Фамилия Отправителя / Shipper`s Last Name</p>
                            <p class="value">'.$arResult['REQUEST']['NAME_SENDER'].'</p>
                        </div>
                    </td>
                    <td width="250" rowspan="2">
                        <div style="width:250px; height:80px;">
                            <p class="label">Телефон / Phone</p>
                            <p class="value">'.$arResult['REQUEST']['PHONE_SENDER'].'</p>
                        </div>
                    </td>
                    <td rowspan="5" width="30" bgcolor="#d6ffcc" style="vertical-align:middle;">
                        <div style="width:30px; height:200px;">
                            <img width="30" height="200" alt="Условия доставки" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l3.png">
                        </div>
                    </td>
                    <td width="220" rowspan="2">
                        <div style="width:220px; height:80px;">
                            <p class="label">&nbsp;</p>
                            <p class="value">'.$arResult['REQUEST']['TYPE_DELIVERY'].'</p>
                        </div>
                    </td>
                    <td rowspan="5" width="30" bgcolor="#d6ffcc" style="vertical-align:middle;">
                        <div style="width:30px; height:200px;">
                            <img width="30" height="200" alt="Условия оплаты" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l4.png">
                        </div>
                    </td>
                    <td width="142" rowspan="3">
                        <div style="width:142px; height:120px;">
                            <p class="label">Оплачивает</p>
                            <p class="value">'.$arResult['REQUEST']['TYPE_PAYS'].'</p>
                            <p class="value"></p>
                        </div>
                    </td>
                    <tr>
                        <td width="380">
                            <div style="width:380px; height:40px;">
                                <p class="label">Компания-Отправитель / Shipping Company</p>
                                <p class="value" style="font-size: 11pt; line-height: 0.85;">'.$company.'</p>
                            </div>
                        </td>
                        <tr>
                            <td width="380">
                                <div style="width:380px; height:40px;">
                                    <p class="label">Страна / Country</p>
                                    <p class="value">'.$arResult['REQUEST']['c_sender'].'</p>
                                </div>
                            </td>
                            <td width="220">
                                <div style="width:220px; height:40px;">
                                    <p class="label">Область / State</p>
                                    <p class="value">'.$arResult['REQUEST']['o_sender'].'</p>
                                </div>
                            </td>
                            <td width="220" rowspan="3">
                                <div style="width:220px; height:120px;">
                                    <p class="label">Доставить</p>
                                    <p class="value">'.$arResult['REQUEST']['WHO_DELIVERY'].'</p>
                                    <p class="label">Доставить в дату</p>
                                    <p class="value"></p>
                                    <p class="label">Доставить до часа</p>
                                    <p class="value"></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="380">
                                <div style="width:380px; height:40px;">
                                    <p class="label">Город / Sity</p>
                                    <p class="value">'.$arResult['REQUEST']['s_sender'].'</p>
                                </div>
                            </td>
                            <td width="220">
                                <div style="width:220px; height:40px;">
                                    <p class="label">Индекс / Postal Code</p>
                                    <p class="value">'.$arResult['REQUEST']['INDEX_SENDER'].'</p>
                                </div>
                            </td>
                            <td width="142" rowspan="2">
                                <div style="width:142px; height:80px;">
                                    <p class="label">Оплата</p>
                                    <p class="value">'.$arResult['REQUEST']['PAYMENT'].'</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="600" colspan="2">
                                <div style="width:600px; height:40px;">
                                    <p class="label">Адрес / Street Address</p>
                                    <p class="value" style="font-size: 11pt; line-height: 0.85;">'.$arResult['REQUEST']['ADRESS_SENDER'].'</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table width="100%" cellpadding="0" cellspacing="0" border="1" bordercolor="#333333" style="margin-top:-1px;">
                    <tbody>
                        <tr>
                            <td rowspan="5" width="30" bgcolor="#f4ecc5" style="vertical-align:middle;">
                                <div style="width:30px; height:200px;">
                                    <img width="30" height="200" alt="Получатель" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l2.png">
                                </div>
                            </td>
                            <td width="380">
                                <div style="width:380px; height:40px;">
                                    <p class="label">Фамилия Получателя / Consignee`s Last Name</p>
                                    <p class="value">'.$arResult['REQUEST']['NAME_RECIPIENT'].'</p>
                                </div>
                            </td>
                            <td width="220" rowspan="2">
                                <div style="width:220px; height:80px;">
                                    <p class="label">Телефон / Phone</p>
                                    <p class="value">'.$arResult['REQUEST']['PHONE_RECIPIENT'].'</p>
                                </div>
                            </td>
                            <td colspan="3" rowspan="3" width="425">
                                <div style="width:425px; height:120px;">
                                    <p class="label">СПЕЦИАЛЬНЫЕ ИНСТРУКЦИИ / SPECIAL INSTRUCTIONS</p>
                                    <p class="value">'.$arResult['REQUEST']['INSTRUCTIONS'].'</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="380">
                                <div style="width:380px; height:40px;">
                                    <p class="label">Компания-Получатель / Consignee Company</p>
                                    <p class="value" style="font-size: 11pt; line-height: 0.85;">'.$company1.'</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="380">
                                <div style="width:380px; height:40px;">
                                    <p class="label">Страна / Country</p>
                                    <p class="value">'.$arResult['REQUEST']['c_recepient'].'</p>
                                </div>
                            </td>
                            <td width="220">
                                <div style="width:220px; height:40px;">
                                    <p class="label">Область / State</p>
                                    <p class="value">'.$arResult['REQUEST']['o_recepient'].'</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="380">
                                <div style="width:380px; height:40px;">
                                    <p class="label">Город / Sity</p>
                                    <p class="value">'.$arResult['REQUEST']['s_recepient'].'</p>
                                </div>
                            </td>
                            <td width="220">
                                <div style="width:220px; height:40px;">
                                    <p class="label">Индекс / Postal Code</p>
                                    <p class="value">'.$arResult['REQUEST']['INDEX_RECIPIENT'].'</p>
                                </div>
                            </td>
                            <td width="140">
                                <div style="width:140px; height:40px;">
                                    <p class="label">Тариф за услуги</p>
                                    <p class="value">'.$arResult['REQUEST']['FOR_PAYMENT'].'</p>
                                </div>
                            </td>
                            <td width="140">
                                <div style="width:140px; height:40px;">
                                    <p class="label">Страховой тариф</p>
                                    <p class="value">'.$arResult['REQUEST']['PAYMENT_COD'].'</p>
                                </div>
                            </td>
                            <td width="140">
                                <div style="width:140px; height:40px;">
                                    <p class="label">Итого к оплате</p>
                                    <p class="value">'.$arResult['REQUEST']['COST'].'</p>
                                </div>
                            </td>
                        </tr>
                        <tr><td width="600" colspan="2">
                            <div style="width:600px; height:40px;">
                                <p class="label">Адрес / Street Address</p>
                                <p class="value" style="font-size: 11pt; line-height: 0.85;">'.$arResult['REQUEST']['ADRESS_RECIPIENT'].'</p>
                            </div>
                        </td>
                        <td colspan="3" width="425"><div style="width:425px; height:40px;"><p class="label">Фамилия и подпись отправителя / Shippers Signature</p><p class="value" style="font-size: 10pt;line-height: 0.95;padding-right: 160px;">'.$arResult['REQUEST']['NAME_SENDER'].'</p></div></td>
                    </tr>
                </tbody>
            </table>
            <table width="100%" cellpadding="0" cellspacing="0" border="1" bordercolor="#333333" style="margin-top:-1px;">
                <tbody>
                    <tr>
                        <td rowspan="7" width="30" bgcolor="#ccffff" valign="middle" style="vertical-align:middle;">
                            <div style="height:182px; width:30px;">
                                <img width="30" height="182" alt="Описание отправления" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l5.png">
                            </div>
                        </td>
                        <td colspan="3" width="298">
                            <div style="width:298px; height:15px;">
                            </div>
                        </td>
                        <td width="80" align="center">
                            <div style="width:80px; height:15px;">
                                <p class="label">Мест<br>Pieces</p>
                            </div>
                        </td>
                        <td width="80" align="center">
                            <div style="width:80px; height:15px;">
                                <p class="label">Вес<br>Weight</p>
                            </div>
                        </td>
                        <td width="140" align="center">
                            <div style="width:140px; height:21px;">
                                <p class="label">Габариты (см х см х см)<br>Dimensions (cm x cm x cm)</p>
                            </div>
                        </td>
                        <td colspan="2" rowspan="3" width="425"><div style="width:425px; height:65px;"><p class="label">Принято курьером</p></div></td>
                    </tr>
                    <tr>
                        <td colspan="3"><div style="width:298px; height:10px;"><p class="value" style="font-size:7pt;">'.$gab_1_name.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value">'.$gab_1_place.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value">'.$gab_1_weight.'</p></div></td>
                        <td><div style="width:140px; height:10px;"><p class="value">'.$gab_1_sizes.'</p></div></td>
                    </tr>
                    <tr>
                        <td colspan="3"><div style="width:298px; height:10px;"><p class="value" style="font-size:7pt;">'.$gab_2_name.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value">'.$gab_2_place.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value">'.$gab_2_weight.'</p></div></td>
                        <td><div style="width:140px; height:10px;"><p class="value">'.$gab_2_sizes.'</p></div></td>
                    </tr>
                    <tr>
                        <td colspan="3"><div style="width:298px; height:10px;"><p class="value" style="font-size:7pt;">'.$gab_3_name.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value">'.$gab_3_place.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value">'.$gab_3_weight.'</p></div></td>
                        <td><div style="width:140px; height:10px;"><p class="value">'.$gab_3_sizes.'</p></div></td>
                        <td rowspan="3" width="212"><div style="width:212px; height:60px;"><p class="label">ДОЛЖНОСТЬ</p></div></td>
                        <td rowspan="3" width="212"><div style="width:212px; height:60px;"><p class="label">ФАМИЛИЯ ПОЛУЧАТЕЛЯ</p></div></td>
                    </tr>
                    <tr>
                        <td colspan="3"><div style="width:298px; height:10px;"><p class="value" style="font-size:7pt;">'.$gab_4_name.'</p></div></td>
                        <td><div style="width:80px; height:20px;"><p class="value">'.$gab_4_place.'</p></div></td>
                        <td><div style="width:80px; height:20px;"><p class="value">'.$gab_4_weight.'</p></div></td>
                        <td><div style="width:140px; height:20px;"><p class="value">'.$gab_4_sizes.'</p></div></td>
                    </tr>
                    <tr>
                        <td colspan="3"><div style="width:298px; height:10px;"><p class="value" style="font-size:7pt;">'.$gab_5_name.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value">'.$gab_5_place.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value">'.$gab_5_weight.'</p></div></td>
                        <td><div style="width:140px; height:10px;"><p class="value">'.$gab_5_sizes.'</p></div></td>
                    </tr>
                    <tr>
                        <td width="98">
                            <div style="height:50px; width:98px;">
                                <p class="label">Мест<br>Pieses</p>
                                <p class="value">'.$arResult['REQUEST']['total_place'].'</p>
                            </div>
                        </td>
                        <td width="98">
                            <div style="height:50px; width:98px">
                                <p class="label">Вес<br>Weight</p>
                                <p class="value">'.$arResult['REQUEST']['total_weight'].'</p>
                            </div>
                        </td>
                        <td width="98">
                            <div style="height:50px; width:98px;"><p class="label">Объемный вес<br>Vol. WT</p>
                            <p class="value">'.$arResult['REQUEST']['total_gabweight'].'</p>
                        </div>
                    </td>
                    <td colspan="2"><div style="height:50px;"><p class="label">Контр. взвеш.<br>Control WT</p></div></td>
                    <td>
                        <div style="height:50px;"><p class="label">Объявл. стоимость<br>Declared Value</p>
                        <p class="value">'.$arResult['REQUEST']['COST2'].'</p>
                    </div>
                </td>
                <td><div style="height:50px;"><p class="label">ПОДПИСЬ ПОЛУЧАТЕЛЯ</p></div></td>
                <td><div style="height:50px;"><p class="label">ДАТА И ВРЕМЯ ДОСТАВКИ</p></div></td>
            </tr>
        </tbody>
    </table>
</div><p style="line-height:20px"></p>';
//if ($i!=1) {$html .= '<pagebreak>';}
}


//echo $html;
    $mpdf->WriteHTML($html);
    $mpdf->Output($BE_DIR.$PDF_NAME,"F");
    //$mpdf->Output();
}
// форма для Вымпелкома
function MakeZakazPDFV($arResult)
{
    // * 22.08.2019   Здесь ровно пять описаний

    // показать
    //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/filename000test.txt', print_r($arResult, true), FILE_APPEND);

    // номер нашел накладной
    // number_internal_array][1][0]

    //echo "<!--";
    //echo "<pre>";
    //print_r($arResult);
    //echo "<pre>";
    //echo "-->";

    /* увидим  кол-во записей */
    // перечисляем все виды описаний (любое количество)
    $m     = json_decode($arResult['REQUEST']['fullArray'], True);
    $cnt   = count(json_decode($arResult['REQUEST']['fullArray'], True));
    $cooef = (int)(ceil(($cnt / 5)));

    //$a = array ($cooef, $cnt, $m);
    //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/arraylist_MakeZakazPDF.txt', print_r($a, true), FILE_APPEND);
    /*  */

    $PDF_NAME = $arResult['REQUEST']['number_nakl'].".pdf";
    $BE_DIR = $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main", "upload_dir")."/pdf/";
    $mpdf = new \Mpdf\Mpdf([
        //  'debug' => true,
        //  'allow_output_buffering' => true,
        'table_error_report' => false,
        'allow_html_optional_endtags' => false,
        'ignore_invalid_utf8' => true,
        'mode' => 'utf-8',
    ]);

    $mpdf->SetTitle('Накладная № '.$arResult['REQUEST']['number_nakl']);
    $mpdf->allow_charset_conversion = true;
    $mpdf->charset_in='windows-1251'; /*не забываем про русский*/
//$mpdf->charset_in = 'utf-8';

//echo "<pre>";print_r($_SERVER[DOCUMENT_ROOT]);echo "</pre>";
    $mpdf->showImageErrors = true;
    $mpdf->list_indent_first_level = 0;

//$logo = $arResult[LOGO_PRINT];
//$product_code_39 = "<img alt='code 39 bar code' src='".$_SERVER[DOCUMENT_ROOT]."/bitrix/_black_mist/mpdf7/barcode.php?codetype=Code39&size=60&text=".$arResult[REQUEST][number_nakl]."&print=false' />";
    $company = $arResult['REQUEST']['COMPANY_SENDER'];
    $company1 = $arResult['REQUEST']['COMPANY_RECIPIENT'];

    if($arResult['REQUEST']['gab_1_name']!='')
    {
        $gab_1_name = $arResult['REQUEST']['gab_1_name'];
    }
    if($arResult['REQUEST']['gab_1_place']>0)
    {
        $gab_1_place = $arResult['REQUEST']['gab_1_place'];
    }
    if($arResult['REQUEST']['gab_1_weight']>0){
        $gab_1_weight = $arResult['REQUEST']['gab_1_weight'];
    }
    if($arResult['REQUEST']['gab_1_sizes']!='0x0x0' AND $arResult['REQUEST']['gab_1_sizes']!='x0x0'){
        $gab_1_sizes = $arResult['REQUEST']['gab_1_sizes'];
    }
    if($arResult['REQUEST']['gab_2_name']!='')
    {
        $gab_2_name = $arResult['REQUEST']['gab_2_name'];
    }
    if($arResult['REQUEST']['gab_2_place']>0)
    {
        $gab_2_place = $arResult['REQUEST']['gab_2_place'];
    }
    if($arResult['REQUEST']['gab_2_weight']>0){
        $gab_2_weight = $arResult['REQUEST']['gab_2_weight'];
    }
    if($arResult['REQUEST']['gab_2_sizes']!='0x0x0' AND $arResult['REQUEST']['gab_2_sizes']!='x0x0'){
        $gab_2_sizes = $arResult['REQUEST']['gab_2_sizes'];
    }
    if($arResult['REQUEST']['gab_3_name']!='')
    {
        $gab_3_name = $arResult['REQUEST']['gab_3_name'];
    }
    if($arResult['REQUEST']['gab_3_place']>0)
    {
        $gab_3_place = $arResult['REQUEST']['gab_3_place'];
    }
    if($arResult['REQUEST']['gab_3_weight']>0){
        $gab_3_weight = $arResult['REQUEST']['gab_3_weight'];
    }
    if($arResult['REQUEST']['gab_3_sizes']!='0x0x0' AND $arResult['REQUEST']['gab_3_sizes']!='x0x0'){
        $gab_3_sizes = $arResult['REQUEST']['gab_3_sizes'];
    }
    if($arResult['REQUEST']['gab_4_name']!='')
    {
        $gab_4_name = $arResult['REQUEST']['gab_4_name'];
    }
    if($arResult['REQUEST']['gab_4_place']>0)
    {
        $gab_4_place = $arResult['REQUEST']['gab_4_place'];
    }
    if($arResult['REQUEST']['gab_4_weight']>0){
        $gab_4_weight = $arResult['REQUEST']['gab_4_weight'];
    }
    if($arResult['REQUEST']['gab_4_sizes']!='0x0x0' AND $arResult['REQUEST']['gab_4_sizes']!='x0x0'){
        $gab_4_sizes = $arResult['REQUEST']['gab_4_sizes'];
    }
    if($arResult['REQUEST']['gab_5_name']!='')
    {
        $gab_5_name = $arResult['REQUEST']['gab_5_name'];
    }
    if($arResult['REQUEST']['gab_5_place']>0)
    {
        $gab_5_place = $arResult['REQUEST']['gab_5_place'];
    }
    if($arResult['REQUEST']['gab_5_weight']>0){
        $gab_5_weight = $arResult['REQUEST']['gab_5_weight'];
    }
    if($arResult['REQUEST']['gab_5_sizes']!='0x0x0' AND $arResult['REQUEST']['gab_5_sizes']!='x0x0'){
        $gab_5_sizes = $arResult['REQUEST']['gab_5_sizes'];
    }

    $html = '';
    for ($i = 0; $i <= 1; $i++){
        $html .= '<style>
 @media print  {
           .print_block {
                page-break-inside: avoid;
            }
        }
        .print_block {
            page-break-inside: avoid;
        }
.label {
color: #536ac2;
font-size: 7pt;
display: block;
}
.value {
font-size: 12pt;
font-weight: bold;
display: block;
}
td{padding:5px;vertical-align: top;border: 1px solid #333333}
table{font-family: Arial, Helvetica, sans-serif;}
</style>
<div class="print_block">
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td style="border:0;vertical-align:middle;" width="286"><p style="font-size: 28px;font-weight: bold;text-align: left;">'.$arResult['REQUEST']['number_nakl'].'</p></td>
            <td style="border:0;vertical-align:middle" align="center">
                <barcode code="'.$arResult['REQUEST']['number_nakl'].'" type="C39" />
            </td>
            <td style="border:0;vertical-align:middle;" width="286">
                <p><img width="286" style="float:right" height="66" alt="" src="'.$_SERVER['DOCUMENT_ROOT'].$arResult['LOGO_PRINT'].'"></p>
                <p style="font-size: 7pt;">'.$arResult['ADRESS_PRINT'].'</p></td>
        </tr>
        </table>
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
                            <p class="label">Фамилия Отправителя / Shipper`s Last Name</p>
                            <p class="value">'.$arResult['REQUEST']['NAME_SENDER'].'</p>
                        </div>
                    </td>
                    <td width="250" rowspan="2">
                        <div style="width:250px; height:80px;">
                            <p class="label">Телефон / Phone</p>
                            <p class="value">'.$arResult['REQUEST']['PHONE_SENDER'].'</p>
                        </div>
                    </td>
                    <td rowspan="5" width="30" bgcolor="#d6ffcc" style="vertical-align:middle;">
                        <div style="width:30px; height:200px;">
                            <img width="30" height="200" alt="Условия доставки" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l3.png">
                        </div>
                    </td>
                    <td width="220" rowspan="2">
                        <div style="width:220px; height:80px;">
                            <p class="label">&nbsp;</p>
                            <p class="value">'.$arResult['REQUEST']['TYPE_DELIVERY'].'</p>
                            <p class="value">С ВОЗВРАТОМ</p>
                        </div>
                    </td>
                    <td rowspan="5" width="30" bgcolor="#d6ffcc" style="vertical-align:middle;">
                        <div style="width:30px; height:200px;">
                            <img width="30" height="200" alt="Условия оплаты" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l4.png">
                        </div>
                    </td>
                    <td width="142" rowspan="3">
                        <div style="width:142px; height:120px;">
                            <p class="label">Оплачивает</p>
                            <p class="value">'.$arResult['REQUEST']['TYPE_PAYS'].'</p>
                            <p class="value"></p>
                        </div>
                    </td>
                    <tr>
                        <td width="380">
                            <div style="width:380px; height:40px;">
                                <p class="label">Компания-Отправитель / Shipping Company</p>
                                <p class="value" style="font-size: 11pt; line-height: 0.85;">'.$company.'</p>
                            </div>
                        </td>
                        <tr>
                            <td width="380">
                                <div style="width:380px; height:40px;">
                                    <p class="label">Страна / Country</p>
                                    <p class="value">'.$arResult['REQUEST']['c_sender'].'</p>
                                </div>
                            </td>
                            <td width="220">
                                <div style="width:220px; height:40px;">
                                    <p class="label">Область / State</p>
                                    <p class="value">'.$arResult['REQUEST']['o_sender'].'</p>
                                </div>
                            </td>
                            <td width="220" rowspan="3">
                                <div style="width:220px; height:120px;">
                                    <p class="label">Доставить</p>
                                    <p class="value">'.$arResult['REQUEST']['WHO_DELIVERY'].'</p>
                                    <p class="label">Доставить в дату</p>
                                    <p class="value"></p>
                                    <p class="label">Доставить до часа</p>
                                    <p class="value"></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="380">
                                <div style="width:380px; height:40px;">
                                    <p class="label">Город / Sity</p>
                                    <p class="value">'.$arResult['REQUEST']['s_sender'].'</p>
                                </div>
                            </td>
                            <td width="220">
                                <div style="width:220px; height:40px;">
                                    <p class="label">Индекс / Postal Code</p>
                                    <p class="value">'.$arResult['REQUEST']['INDEX_SENDER'].'</p>
                                </div>
                            </td>
                            <td width="142" rowspan="2">
                                <div style="width:142px; height:80px;">
                                    <p class="label">Оплата</p>
                                    <p class="value">'.$arResult['REQUEST']['PAYMENT'].'</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="600" colspan="2">
                                <div style="width:600px; height:40px;">
                                    <p class="label">Адрес / Street Address</p>
                                    <p class="value" style="font-size: 11pt; line-height: 0.85;">'.$arResult['REQUEST']['ADRESS_SENDER'].'</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table width="100%" cellpadding="0" cellspacing="0" border="1" bordercolor="#333333" style="margin-top:-1px;">
                    <tbody>
                        <tr>
                            <td rowspan="5" width="30" bgcolor="#f4ecc5" style="vertical-align:middle;">
                                <div style="width:30px; height:200px;">
                                    <img width="30" height="200" alt="Получатель" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l2.png">
                                </div>
                            </td>
                            <td width="380">
                                <div style="width:380px; height:40px;">
                                    <p class="label">Фамилия Получателя / Consignee`s Last Name</p>
                                    <p class="value">'.$arResult['REQUEST']['NAME_RECIPIENT'].'</p>
                                </div>
                            </td>
                            <td width="220" rowspan="2">
                                <div style="width:220px; height:80px;">
                                    <p class="label">Телефон / Phone</p>
                                    <p class="value">'.$arResult['REQUEST']['PHONE_RECIPIENT'].'</p>
                                </div>
                            </td>
                            <td colspan="3" rowspan="3" width="425">
                                <div style="width:425px; height:120px;">
                                    <p class="label">СПЕЦИАЛЬНЫЕ ИНСТРУКЦИИ / SPECIAL INSTRUCTIONS</p>
                                    <p class="value">'.$arResult['REQUEST']['INSTRUCTIONS'].'</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="380">
                                <div style="width:380px; height:40px;">
                                    <p class="label">Компания-Получатель / Consignee Company</p>
                                    <p class="value" style="font-size: 11pt; line-height: 0.85;">'.$company1.'</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="380">
                                <div style="width:380px; height:40px;">
                                    <p class="label">Страна / Country</p>
                                    <p class="value">'.$arResult['REQUEST']['c_recepient'].'</p>
                                </div>
                            </td>
                            <td width="220">
                                <div style="width:220px; height:40px;">
                                    <p class="label">Область / State</p>
                                    <p class="value">'.$arResult['REQUEST']['o_recepient'].'</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="380">
                                <div style="width:380px; height:40px;">
                                    <p class="label">Город / Sity</p>
                                    <p class="value">'.$arResult['REQUEST']['s_recepient'].'</p>
                                </div>
                            </td>
                            <td width="220">
                                <div style="width:220px; height:40px;">
                                    <p class="label">Индекс / Postal Code</p>
                                    <p class="value">'.$arResult['REQUEST']['INDEX_RECIPIENT'].'</p>
                                </div>
                            </td>
                            <td width="140">
                                <div style="width:140px; height:40px;">
                                    <p class="label">Тариф за услуги</p>
                                    <p class="value">'.$arResult['REQUEST']['FOR_PAYMENT'].'</p>
                                </div>
                            </td>
                            <td width="140">
                                <div style="width:140px; height:40px;">
                                    <p class="label">Страховой тариф</p>
                                    <p class="value">'.$arResult['REQUEST']['PAYMENT_COD'].'</p>
                                </div>
                            </td>
                            <td width="140">
                                <div style="width:140px; height:40px;">
                                    <p class="label">Итого к оплате</p>
                                    <p class="value">'.$arResult['REQUEST']['COST'].'</p>
                                </div>
                            </td>
                        </tr>
                        <tr><td width="600" colspan="2">
                            <div style="width:600px; height:40px;">
                                <p class="label">Адрес / Street Address</p>
                                <p class="value" style="font-size: 11pt; line-height: 0.85;">'.$arResult['REQUEST']['ADRESS_RECIPIENT'].'</p>
                            </div>
                        </td>
                        <td colspan="3" width="425"><div style="width:425px; height:40px;"><p class="label">Фамилия и подпись отправителя / Shippers Signature</p><p class="value" style="font-size: 10pt;line-height: 0.95;padding-right: 160px;">'.$arResult['REQUEST']['NAME_SENDER'].'</p></div></td>
                    </tr>
                </tbody>
            </table>
            <table width="100%" cellpadding="0" cellspacing="0" border="1" bordercolor="#333333" style="margin-top:-1px;">
                <tbody>
                    <tr>
                        <td rowspan="7" width="30" bgcolor="#ccffff" valign="middle" style="vertical-align:middle;">
                            <div style="height:182px; width:30px;">
                                <img width="30" height="182" alt="Описание отправления" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l5.png">
                            </div>
                        </td>
                        <td colspan="3" width="298">
                            <div style="width:298px; height:15px;">
                            </div>
                        </td>
                        <td width="80" align="center">
                            <div style="width:80px; height:15px;">
                                <p class="label">Мест<br>Pieces</p>
                            </div>
                        </td>
                        <td width="80" align="center">
                            <div style="width:80px; height:15px;">
                                <p class="label">Вес<br>Weight</p>
                            </div>
                        </td>
                        <td width="140" align="center">
                            <div style="width:140px; height:21px;">
                                <p class="label">Габариты (см х см х см)<br>Dimensions (cm x cm x cm)</p>
                            </div>
                        </td>
                        <td colspan="2" rowspan="1" width="425"><div style="width:425px; height:65px;"><p class="label">Принято курьером</p></div></td>
                    </tr>
                    <tr>
                        <td colspan="3"><div style="width:298px; height:10px;"><p class="value" style="font-size:7pt;">'.$gab_1_name.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value">'.$gab_1_place.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value">'.$gab_1_weight.'</p></div></td>
                        <td><div style="width:140px; height:10px;"><p class="value">'.$gab_1_sizes.'</p></div></td>
                        <td rowspan="2" width="212"><div style="width:212px; height:60px;"><p class="label">ДОЛЖНОСТЬ</p></div></td><td rowspan="2" width="212"><div style="width:212px; height:60px;"><p class="label">ФАМИЛИЯ ПОЛУЧАТЕЛЯ</p></div></td>
                    </tr>
                    <tr>
                        <td colspan="3"><div style="width:298px; height:10px;"><p class="value" style="font-size:7pt;">'.$gab_2_name.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value">'.$gab_2_place.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value">'.$gab_2_weight.'</p></div></td>
                        <td><div style="width:140px; height:10px;"><p class="value">'.$gab_2_sizes.'</p></div></td>
                        
                    </tr>
                    <tr>
                        <td colspan="3"><div style="width:298px; height:10px;"><p class="value" style="font-size:7pt;">'.$gab_3_name.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value">'.$gab_3_place.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value">'.$gab_3_weight.'</p></div></td>
                        <td><div style="width:140px; height:10px;"><p class="value">'.$gab_3_sizes.'</p></div></td>
                        <td rowspan="2"><div style="height:50px;"><p class="label">ПОДПИСЬ ПОЛУЧАТЕЛЯ</p></div></td><td rowspan="2"><div style="height:50px;"><p class="label">ДАТА И ВРЕМЯ ДОСТАВКИ</p></div></td>
                    </tr>
                    <tr>
                        <td colspan="3"><div style="width:298px; height:10px;"><p class="value" style="font-size:7pt;">'.$gab_4_name.'</p></div></td>
                        <td><div style="width:80px; height:20px;"><p class="value">'.$gab_4_place.'</p></div></td>
                        <td><div style="width:80px; height:20px;"><p class="value">'.$gab_4_weight.'</p></div></td>
                        <td><div style="width:140px; height:20px;"><p class="value">'.$gab_4_sizes.'</p></div></td>
                    </tr>
                    <tr>
                        <td colspan="3"><div style="width:298px; height:10px;"><p class="value" style="font-size:7pt;">'.$gab_5_name.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value">'.$gab_5_place.'</p></div></td>
                        <td><div style="width:80px; height:10px;"><p class="value">'.$gab_5_weight.'</p></div></td>
                        <td><div style="width:140px; height:10px;"><p class="value">'.$gab_5_sizes.'</p></div></td>
                        <td rowspan="1" colspan="2">
                        <div style="height:50px; position:relative; overflow: visible;">
                              <p class="label">ПРИНЯТ ВОЗВРАТ</p>
                              <h1 style="position: absolute;font-size: 42px;top: -1px;left: 60px;
                              letter-spacing: 12px;z-index: 1000;opacity: 60%;color: #e4c010;">ВОЗВРАТ</h1>
                        </div>
                        </td>
                    </tr>
                    <tr>
                        <td width="98">
                            <div style="height:50px; width:98px;">
                                <p class="label">Мест<br>Pieses</p>
                                <p class="value">'.$arResult['REQUEST']['total_place'].'</p>
                            </div>
                        </td>
                        <td width="98">
                            <div style="height:50px; width:98px">
                                <p class="label">Вес<br>Weight</p>
                                <p class="value">'.$arResult['REQUEST']['total_weight'].'</p>
                            </div>
                        </td>
                        <td width="98">
                            <div style="height:50px; width:98px;"><p class="label">Объемный вес<br>Vol. WT</p>
                            <p class="value">'.$arResult['REQUEST']['total_gabweight'].'</p>
                        </div>
                        </td>
                        <td colspan="2"><div style="height:50px;"><p class="label">Контр. взвеш.<br>Control WT</p></div></td>
                        <td>
                        <div style="height:50px;"><p class="label">Объявл. стоимость<br>Declared Value</p>
                        <p class="value">'.$arResult['REQUEST']['COST2'].'</p>
                        </div>
                        </td>
                        <td rowspan="1" colspan="1">
                         <div style="height:50px;"><p class="label">ПОДПИСЬ</p>
                       </div>
                        </td>
                        <td rowspan="1" colspan="1">
                            <div style="height:50px;"><p class="label">ДАТА И ВРЕМЯ</p>
                            </div>
                        </td>
            </tr>
        </tbody>
    </table>
</div><p style="line-height:20px"></p>';
//if ($i!=1) {$html .= '<pagebreak>';}
    }


//echo $html;
    $mpdf->WriteHTML($html);
    $mpdf->Output($BE_DIR.$PDF_NAME,"F");
    //$mpdf->Output();
}
?>
