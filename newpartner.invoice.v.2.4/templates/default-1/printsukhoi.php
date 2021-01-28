<script type="text/javascript">
	$(document).ready(function() {
		var s = JsBarcode("#barcode_0", "<?=$arResult['INVOICE']['NAME'];?>", {
		  format: "CODE39",
		  width: 2,
		  height: 60,
		  displayValue: false
		});
		// вернули значение.
		//console.log(s._renderProperties[0].element.innerHTML);
		//$("div.a10").html(s._renderProperties[0].element.innerHTML);
	});
</script>

<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
if ($arResult['INVOICE'])
{
	
	//echo "<!-- 111111111111111111111111 <pre>";
	//	print_r ($arResult);
	//echo "</pre> --> ";
	
	/* увидим  кол-во записей */
	// перечисляем все виды описаний (любое количество) 
	
	//echo "<!-- 2222222222222222222 <pre>";
	//	print_r ($arResult['INVOICE']);
	//echo "</pre> -->";
	
	$m = $arResult['INVOICE']['PACK_DESCR'];
	$s = array();
	// удалить все пустые элементы по имени
		foreach ($m as $k=>$v){
			 if (trim($v['name']) !=''){
				 $s[]= $v;
			 }	 
		}
	// ---------------------------
	$cnt   = count($s);
	$cooef = (int)$cnt / 5;
	$partCnt = $cnt - ($cooef * 5); 
	if ($cnt < 5) {$cooef = 1;}
	// if ($cooef > 1) {echo "это не одна страница!";}
	// номера страниц!
	?>
    <div id="print_block" class="print_block1 printsukhoi">
	
	
<style>
body {
	margin:0;
	padding:0;
}
/*
table {
	border-collapse:collapse;
}

table.invoice td {
	vertical-align:top;
	position:relative;
}
*/
table.invoice td.vertical_td  {
	transform: rotate(-90deg);
	transform-origin: 50% 50% 0;
	width:50px;
	vertical-align: middle;
	text-align:center;
}


table.requests thead th.sorts div {
	position:relative;
	padding-right:9px;
}
table.requests thead th.sorts .asc, table.requests thead th.sorts .desc {
	display:block;
	width:9px;
	height:5px;
	position:absolute;
	right: 0px;
}
table.requests thead th.sorts .asc {
	top:2px;
}
table.requests thead th.sorts .desc {
	top:10px;
}
table.requests thead th.sorts .asc {
	background:url(images/arrows.png)  0 0 no-repeat;
}
table.requests thead th.sorts .desc {
	background:url(images/arrows.png)  0 -5px no-repeat;
}
table.requests thead th.sorts .asc:hover {
	background:url(images/arrows.png)  -9px 0 no-repeat;
}
table.requests thead th.sorts .desc:hover {
	background:url(images/arrows.png)  -9px -5px no-repeat;
}
table.requests thead th.sorts .asc.active {
	background:url(images/arrows.png)  -18px 0 no-repeat;
}
table.requests thead th.sorts .desc.active {
	background:url(images/arrows.png)  -18px -5px no-repeat;
}

.ac_results {
    background-color: #FFFFFF;
    border: 1px solid #CCCCCC;
    margin-left: 1px;
    margin-top: 0px;
    overflow: hidden;
    padding: 0;
    z-index: 5000;
}
.ac_results ul {
    list-style: none outside none;
    margin: 0;
    padding: 0;
    width: 100%;
}
.ac_results iframe {
    display: block;
    height: 3000px;
    left: 0;
    position: absolute;
    top: 0;
    width: 3000px;
    z-index: -1;
}
.ac_results li {
    color: #606060;
    cursor: pointer;
    display: block;
    font: menu;
    font-size: 12px;
    margin: 0;
    overflow: hidden;
    padding: 2px 5px;
    position: relative;
    width: 100%;
}
.ac_loading {
}
.ac_over {
    background-color: #2e83bf;
    color: #FFFFFF !important;
}

.print_block {
	width:1060px;
	overflow:hidden;
	font-family: Arial;
	font-style: normal;
	/*margin-bottom:5px;*/
}

.print_block1 {
	width:900px;
	overflow:hidden;
	font-family: Arial;
	font-style: normal;
	/*margin-bottom:5px;*/
}

.print_head_block {
	width:1060px;
	overflow:hidden;
	position:relative;
	height:85px;
}

.print_head_block1
{
	width:900px;
	overflow:hidden;
	position:relative;
	height:85px;
}

.print_head_block2
{
	width:900px;
	overflow:hidden;
	position:relative;
	height:80px;
}

/* накладная для сухого - */
.application_for_delivery_print_head {
	margin:0 auto;
	text-align:left;
	width:500px;
	height:60px;
	margin-top:20px;
}	

.application_for_delivery_print_head_inner {
	margin:0 auto;
	width:400px;
	height:60px;
	text-align:center;
	line-height: 14px;
}
/* -------------------- - */

.print_head_block1.print_head_block_mini {height:62px;}
.print_head_block1 .img_print_head {
	width:286px;
	height:66px;
	margin-left: 600px;
    margin-top: -75px;
	right:0;
}

.print_head_block1.print_head_block_mini .img_print_head {height: 60px; width: 260px; top: 0;}
.print_head_block1 .adress_print_head {
	width:285px;
	height:14px;
	position:absolute;
	top:68px;
	right:0px;
	font-size:7pt;
}
.print_head_block1.print_head_block_mini .adress_print_head {width: 260px; display: none;}
.print_head_block1 .number {
	width:900px;
	position:absolute;
	left:0;
	top:17px;
	/*text-align: center;*/
	text-align:left;
	height:50px;
	font-size:26pt;
}
.print_head_block1.print_head_block_mini .number {width: 275px; top: 12px;}
.print_head_block1 .target {
	position:absolute;
	/*left:0;*/
	left:275px;
	/*top:7px;*/
	top:2px;
}

.print_head_block1 .target2 {
	position:absolute;
	/*  left:0;*/
	/*  left:275px; */
	/*  top:7px;*/
	top:2px;
}
.print_head_block1.print_head_block_mini .target {top: 0;}
.print_block1 table td div {
	overflow:hidden;
}



.print_head_block.print_head_block_mini {height:62px;}
.print_head_block .img_print_head {
	width:286px;
	height:66px;
	position:absolute;
	top:7px;
	right:0;
}

.print_head_block.print_head_block_mini .img_print_head {height: 60px; width: 260px; top: 0;}
.print_head_block .adress_print_head {
	width:285px;
	height:14px;
	position:absolute;
	top:68px;
	right:0px;
	font-size:7pt;
}
.print_head_block.print_head_block_mini .adress_print_head {width: 260px; display: none;}
.print_head_block .number {
	width:1060px;
	position:absolute;
	left:0;
	top:17px;
	/*text-align: center;*/
	text-align:left;
	height:50px;
	font-size:26pt;
}
.print_head_block.print_head_block_mini .number {width: 275px; top: 12px;}
.print_head_block .target {
	position:absolute;
	/*left:0;*/
	left:275px;
	/*top:7px;*/
	top:2px;
}

.print_head_block .target2 {
	position:absolute;
	/*  left:0;*/
	/*  left:275px; */
	/*  top:7px;*/
	top:2px;
}
.print_head_block.print_head_block_mini .target {top: 0;}

.print_block table td div {
	overflow:hidden;
}
.print_block1 span.label {
	color: #536ac2;
    font-size: 7pt;
	display:block;
	margin-left:5px;
	margin-top:1px;
}

span.label_print {
	color: #536ac2;
    font-size: 7pt;
	display:block;
	margin-left:5px;
	margin-top:1px;
}

.print_block1 span.value {
    font-size: 9pt;
    font-weight: bold;
	display:block;
	margin-left:5px;
	margin-top:1px;
}
.print_block1 span.value_mini {
    font-size: 10pt;
    font-weight: bold;
	display:block;
	margin-left:5px;
	margin-top:1px;
	line-height: 1.1;
}
.print_block1 span.value_mini2 {
    font-size: 8pt;
    font-weight: bold;
	display:block;
	margin-left:5px;
	margin-top:1px;
}
.print_block1 table {
	border-collapse:collapse;
}
.print_block1 table td {
	vertical-align:top;
}

.print_block table td div {
	overflow:hidden;
}
.print_block span.label {
	color: #536ac2;
    font-size: 7pt;
	display:block;
	margin-left:5px;
	margin-top:1px;
}
.print_block span.value {
    font-size: 12pt;
    font-weight: bold;
	display:block;
	margin-left:5px;
	margin-top:1px;
}
.print_block span.value_mini {
    font-size: 10pt;
    font-weight: bold;
	display:block;
	margin-left:5px;
	margin-top:1px;
	line-height: 1.1;
}
.print_block span.value_mini2 {
    font-size: 8pt;
    font-weight: bold;
	display:block;
	margin-left:5px;
	margin-top:1px;
}
.print_block table {
	border-collapse:collapse;
}
.print_block table td {
	vertical-align:top;
}
.panel-default-3 > .panel-heading {
    background-color: #f6f8f9;
    border-color: #f6f8f9;
    color: #2c3e50;
}
.panel-default-3 {
    border-color: #f6f8f9 !important;
}
.panel-default-1 > .panel-heading {
    background-color: #dde3e4;
    border-color: #dde3e4;
    color: #2c3e50;
}
.panel-default-1 {
    border-color: #dde3e4 !important;
}

div.alert-info     {display:none;}
div.alert-warning  {display:none;}

div.print_block printsukhoi span.label {padding:0;}
div.print_block1 printsukhoi span.label {padding:0;}

span.value {
    font-size: 9pt;
    font-weight: bold;
    display: block;
    margin-left: 5px;
    margin-top: 1px;
}

</style>
	
	
	   <? /* 'i' bar-code ?*/ ?>
	   <?
		   $strIneerNameInvoice   = preg_replace ("/(.*)-(.*)$/", "$1", $arResult['INVOICE']['PROPERTY_MINIMAL_NUMBER_SERIES'][1][1]); 
		   // наше текущее дополнение
		   $intIneerNumberInvoice = preg_replace ("/(.*)-(.*)$/", "$2", $arResult['INVOICE']['PROPERTY_INNER_NUMBER_CLAIM_VALUE']);
	   ?>
	   
	   
	   
       <?for ($i = 0; $i <= $cooef; $i++) { 
	     if  (($partCnt == 0)&&($i == $cooef)) {break;}
	   ?>
	   
	   
	   
	   
	   
	   <? //echo "стр = ". $cnt ." ".($i+1)."= "; ?>
	   <div class="a11" style="page-break-after: always;">
	   
	   
		<?					
		$time = strtotime($arResult['INVOICE']['PROPERTY_MINIMAL_NUMBER_SERIES'][1][2]);      
		$month_name = array( 1 => 'января', 2 => 'февраля', 3 => 'марта', 
			4 => 'апреля', 5 => 'мая', 6 => 'июня', 
			7 => 'июля', 8 => 'августа', 9 => 'сентября', 
			10 => 'октября', 11 => 'ноября', 12 => 'декабря' 
		);		
		$month = $month_name[ date( 'n',$time ) ]; 
			
		$day   = date( 'j',$time ); # С помощью функции date() получаем число дня
		$year  = date( 'Y',$time ); # Получаем год
		$hour  = date( 'G',$time ); # Получаем значение часа
		$min   = date( 'i',$time ); # Получаем минуты		

		$strdate = " ".$day." ".$month."  ".$year . " г. ";	

		// 
		$dateCreate = strtotime($arResult['INVOICE']['DATE_CREATE']);
		$month = $month_name[ date( 'n',$dateCreate ) ]; 

		$day   = date( 'j',$dateCreate ); # С помощью функции date() получаем число дня
		$year  = date( 'Y',$dateCreate ); # Получаем год
		$hour  = date( 'G',$dateCreate ); # Получаем значение часа
		$min   = date( 'i',$dateCreate ); # Получаем минуты	

		$strdateCreate = " ".$day." ".$month."  ".$year . " г. ";								

		?>
		<div class="print_head_block1">
		<div style="display:none" class="CODE39" rel='CODE39'></div>
		<table border="0">
		<tr>
		<td valign="top">
			<div class="number_container" rel="<?=$arResult['INVOICE']['NAME'];?>" style="font-size:18px;width:255px;font-weight:bold;position:relative;float:left;height:70px;text-align:center;">
			<svg style="width:200px;height:35px;" id="barcode_0" >test</svg><br/>
			<p  class="naklnomer" style="width:255px;text-align:center;">
			<?=$arResult['INVOICE']['NAME'];?>
			</p>  
			</div>
		</td>
        <td valign="top">		
			<div style="text-align:center;font-size:22px;width:290px;position:relative;float:left;height:70px;" class="notice_container">
				<b>Заявка на доставку</b><br>
				<div style="text-align:center;font-size:13px;">
					<? if ($strIneerNameInvoice != '0000000000000000') {?>
					&#8470; <?=$strIneerNameInvoice;?>  
					<? };?>
					от <?echo $strdate; ?></span><br/>
					<? if ($strIneerNameInvoice != $intIneerNumberInvoice) { ?>
					<? // если нет дополнений! ?>
					<span> дополнение &#8470;  <?=$intIneerNumberInvoice;?>  от  <?=$strdateCreate;?><?//echo $strdate; ?></span><br/>
					<? };?>				
					<span style="font-size:9px"> к договору № 02317-01-19 от 01.07.2019 г. </span><br>
				</div>	
			</div>
		</td>	
        <td  valign="top">
			<div style="font-size:18px;width:340px;position:relative;float:left;height:90px;text-align:center;float:right;margin-top:-3px;" class="logo_container"> 
				<div class="img_print_head1">
				<img alt="" src="<?=$arResult['LOGO_PRINT'];?>" width="286" height="66">
				</div>
				<div class="adress_print_head1" style="font-size:11px">
                    Тел: +7 495 783-99-18, г. Москва, Шоссейный проезд 10к1
				</div>
			</div>
		</td>
        </table>		
		</div>

<table cellpadding="0" cellspacing="0" border="1"  bordercolor="#333333">
<tbody>
<tr>
<td rowspan="5" width="30" bgcolor="#f4ecc5" style="vertical-align:middle;border-left:1px solid black">
<div style="width:30px; height:200px;">
	<img width="30" height="200" alt="Отправитель" src="http://client.newpartner.ru/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l1.png">
</div>
</td>
<td width="380">
<div style="width:380px; min-height:40px;">
	<span class="label_print" style="border:0">Фамилия Отправителя / Shipper's Last Name</span>
	<span class="value"><?=$arResult['INVOICE']['PROPERTY_NAME_SENDER_VALUE'];?></span>
</div>
</td>
<td width="220" rowspan="2">
<div style="width:220px; height:80px;">
<span class="label_print" style="border:0">Телефон / Phone</span>
<span class="value"><?=$arResult['INVOICE']['PROPERTY_PHONE_SENDER_VALUE'];?></span>
</div>
</td>
<td rowspan="5" width="30" bgcolor="#d6ffcc" style="vertical-align:middle;">
<div style="width:30px; height:200px;">
<img width="30" height="200" alt="Условия доставки" src="http://client.newpartner.ru/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l3.png">
</div>
</td>
<td width="90" rowspan="2"> 
<div style="width:90px; height:80px;">
<span class="label_print" style="border:0">&nbsp;</span>
<span class="value"><?//$arResult['INVOICE']['PROPERTY_TYPE_DELIVERY_VALUE'];?></span>
</div>
</td>
<td rowspan="5" width="30" bgcolor="#d6ffcc" style="vertical-align:middle;">
<div style="width:30px; height:200px;">
<img width="30" height="200" alt="Условия оплаты" src="http://client.newpartner.ru/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l4.png">
</div>
</td>
<td width="105" rowspan="3">
<div style="width:105px; height:120px;">
<span class="label_print" style="border:0">Оплачивает</span>
<span class="value"> <? /*  
			<? $result_client_name = preg_replace ("/Другой/i", "Заказчик",  $arResult['INVOICE']['PROPERTY_TYPE_PAYS_VALUE']);?>
			<span class="value"><?=$result_client_name;?></span>
			<span class="value"><?=$arResult['INVOICE']['PROPERTY_PAYS_VALUE'];?></span>
			<!-- <?=$arResult['INVOICE']['PROPERTY_WHOSE_ORDER_VALUE'];?>  -->
			*/ ?></span>
</div>
</td>
</tr>
<tr>
<td width="380">
<div style="width:380px; height:40px;">
<span class="label_print" style="border:0">Компания-Отправитель / Shipping Company</span>
<span class="value" style="font-size: 11pt; line-height: 0.85;"><?=$arResult['INVOICE']['PROPERTY_COMPANY_SENDER_VALUE'];?></span>
</div>
</td>
</tr>
<tr>
<td width="380">
<div style="width:380px; height:40px;">
<span class="label_print" style="border:0">Страна / Country</span>
<span class="value"><?=$arResult['INVOICE']['PROPERTY_CITY_SENDER_AR'][2];?></span>
</div>
</td>
<td width="90">
<div style="width:210px; min-height:40px;">
<span class="label_print" style="border:0">Область / State</span>
<span class="value"><?=$arResult['INVOICE']['PROPERTY_CITY_SENDER_AR'][1];?></span>
</div>
</td>
<td width="90" rowspan="3"></td>
</tr>
<tr>
<td width="380">
<div style="width:380px; height:40px;">
<span class="label_print" style="border:0">Город / City</span>
<span class="value"><?=$arResult['INVOICE']['PROPERTY_CITY_SENDER_AR'][0];?></span>
</div>
</td>
<td width="220">
<div style="width:220px; height:40px;">
<span class="label_print" style="border:0">Индекс / Postal Code</span>
<span class="value"><?=$arResult['INVOICE']['PROPERTY_INDEX_SENDER_VALUE'];?></span>
</div>
</td>
<td width="105" rowspan="2">
<div style="width:105px; height:80px;">
<span class="label_print" style="border:0">Оплата</span>
<span class="value"><? //=$arResult['INVOICE']['PROPERTY_PAYMENT_VALUE'];?></span>
</div>
</td>
</tr>
<tr>
<td width="600" colspan="2">
<div style="width:600px; height:40px;">
<span class="label_print" style="border:0">Адрес / Street Address</span>
<span class="value" style="font-size: 11pt; line-height: 0.85;"><?=$arResult['INVOICE']['PROPERTY_ADRESS_SENDER_VALUE']['TEXT'];?></span>
</div>
</td>
</tr>
</tbody>
</table>
		
<table cellpadding="0" cellspacing="0" border="1" bordercolor="#333333">
<tbody>
<tr>
<td rowspan="5" width="30" bgcolor="#f4ecc5" style="vertical-align:middle;">
<div style="width:30px; height:200px;">
<img width="30" height="200" alt="Получатель" src="http://client.newpartner.ru/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l2.png">
</div>
</td>
<td>
<div style="width:380px; min-height:40px;">
<span class="label_print" style="border:0">Фамилия Получателя / Consignee's Last Name</span>
<span class="value"><?=$arResult['INVOICE']['PROPERTY_NAME_RECIPIENT_VALUE'];?></span>
</div>
</td>
<td  rowspan="2">
<div style="width:210px; height:80px;">
<span class="label_print" style="border:0">Телефон / Phone</span>
<span class="value"><?=$arResult['INVOICE']['PROPERTY_PHONE_RECIPIENT_VALUE'];?></span>
</div>
</td>
<td colspan="3" rowspan="6"   style="border-right:1px solid black">
<div style="width:267px; min-height:120px;font-size:12px;">
<span class="label_print" style="font-size:12px;border:0;white-space: normal;">СПЕЦИАЛЬНЫЕ ИНСТРУКЦИИ / SPECIAL INSTRUCTIONS</span>
<span class="value" style="font-size:10px;">
		<span class="value" style="font-size:14px">
		Заявка &#8470; <?=$strIneerNameInvoice;?> 
		<? if ($strIneerNameInvoice != $intIneerNumberInvoice) { ?>
		доп. &#8470; <?=$intIneerNumberInvoice;?> 
		<? } ?>
		&nbsp;
		<?=$arResult['INVOICE']['PROPERTY_INSTRUCTIONS_VALUE']['TEXT'];?>
		</span>
		<? if (trim($arResult['INVOICE']['PROPERTY_TO_DELIVER_BEFORE_DATE_VALUE']['TEXT']) !='') {?>
			<!--<span class="value" style="font-size:14px">
			  Доставить до : <?/*=($arResult['INVOICE']['PROPERTY_TO_DELIVER_BEFORE_DATE_VALUE']);*/?>
			</span>-->
		<? } ?>

</span>
</div>
</td>
</tr>
<tr>
<td >
<div style="width:380px; min-height:40px;">
<span class="label_print" style="border:0">Компания-Получатель / Consignee Company</span>
<span class="value" style="font-size: 10pt; line-height: 0.85;"><?=$arResult['INVOICE']['PROPERTY_COMPANY_RECIPIENT_VALUE'];?></span>
</div>
</td>
</tr>
<tr>
<td >
<div style="width:380px; height:40px;">
<span class="label_print" style="border:0">Страна / Country</span>
<span class="value"><?=$arResult['INVOICE']['PROPERTY_CITY_RECIPIENT_AR'][2];?></span>
</div>
</td>
<td >
<div style="width:210px; min-height:40px;">
<span class="label_print" style="border:0">Область / State</span>
<span class="value"><?=$arResult['INVOICE']['PROPERTY_CITY_RECIPIENT_AR'][1];?></span>
</div>
</td>
</tr>
<tr>
<td >
<div style="width:380px; height:40px;">
<span class="label_print" style="border:0">Город / City</span>
<span class="value"><?=$arResult['INVOICE']['PROPERTY_CITY_RECIPIENT_AR'][0];?></span>
</div>
</td>
<td  colspan="1">
<div style="width:210px; height:40px;">
<span class="label_print" style="border:0">Индекс / Postal Code</span>
<span class="value"><?=$arResult['INVOICE']['PROPERTY_INDEX_RECIPIENT_VALUE'];?></span>
</div>
</td>
</tr>
<tr>
<td colspan="2">
<div style="height:40px;">
<span class="label_print" style="border:0">Адрес / Street Address</span>
<span class="value" style="font-size: 11pt; line-height: 0.85;"><?=$arResult['INVOICE']['PROPERTY_ADRESS_RECIPIENT_VALUE']['TEXT'];?></span>
</div>
</td>
</tr>
</tbody>
</table>




<table cellpadding="0" cellspacing="0"  border="1" bordercolor="#333333">
<tbody>
<tr>
<td rowspan="7" width="30" bgcolor="#ccffff" valign="middle" style="vertical-align:middle;">
<div style="height:180px; width:30px;">
<img width="30" height="180" alt="Описание отправления" src="http://client.newpartner.ru/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l5.png">
</div>
</td>
<td colspan="3" >
<div style="width:298px;"></div>
</td>
<td  align="center">
<div style="width:80px; ">
<span class="label_print" style="border:0">Мест<br>Pieces</span>
</div>
</td>
<td align="center">
<div style="width:80px; ">
<span class="label_print" style="border:0">Вес<br>Weight</span>
</div>
</td>
<td  align="center">
<div style="width:140px;">
<span class="label_print" style="border:0">Габариты (см х см х см)<br>Dimensions (cm x cm x cm)</span>
</div>
</td>
<td colspan="2" rowspan="6" >
<div style="width:237px; padding-right:10px;padding-left:10px;">
<span class="label_print" style="text-align:left;border:0">ПОДПИСЬ ЛИЦА,  УПОЛНОМОЧЕННОГО НА <br> <br> ПОДПИСАНИЕ И ПОДАЧУ ЗАЯВОК</span>
<span class="value" style="font-size: 10pt;line-height:0.95;margin-top:5px;">Муратов Вадим Геннадьевич</span>
</div>
</td>
</tr>
<?for ($n = 0; $n <= 4; $n++) {  ?>
<?     $k = (($i) * 5 ) + $n;    ?>
<?if ($arResult['INVOICE']['PACK_DESCR'][$k]['place'] !='0') {?>
	<tr>
		<td colspan="3"><div style="width:298px; min-height:20px;text-align:center"><span class="value"><?=$arResult['INVOICE']['PACK_DESCR'][$k]['name'];?></span></div></td>
		<td><div  class="desc<?=$k;?>" style="width:80px; height:20px;text-align:center"><span class="value"><?=$arResult['INVOICE']['PACK_DESCR'][$k]['place'];?></span></div></td>
		<td><div style="width:80px; min-height:20px;text-align:center"><span class="value"><?=$arResult['INVOICE']['PACK_DESCR'][$k]['weight'];?></span></div></td>
		<td><div style="width:140px; min-height:20px;text-align:center"><span class="value">
		<?=$arResult['INVOICE']['PACK_DESCR'][$k]['sizes'];?>
		</span>
		</div>
		</td>
	</tr>
<? } else { ?>
	<tr>
		<td colspan="3"><div style="width:298px; min-height:20px;text-align:center"><span class="value"></span></div></td>
		<td><div  class="desc<?=$k;?>" style="width:80px; height:20px;text-align:center"><span class="value"></span></div></td>
		<td><div style="width:80px; min-height:20px;text-align:center"><span class="value"></span></div></td>
		<td><div style="width:140px; min-height:20px;text-align:center"><span class="value"></span></div>
		</td>
	</tr>	
<? } ?>
<? } ?>
<tr>
<td >
<div style="height:50px; width:98px;">
<span class="label_print" style="border:0">Мест<br>Pieses</span>
<span class="value"  style="text-align:center" ><?=$arResult['INVOICE']['PROPERTY_PLACES_VALUE'];?></span>
</div>
</td>
<td>
<div style="height:50px; width:98px">
<span class="label_print" style="border:0">Вес<br>Weight</span>
<span class="value" style="text-align:center" ><?=$arResult['INVOICE']['PROPERTY_WEIGHT_VALUE'];?></span>
</div>
</td>
<td >
<div style="height:50px; width:98px;"><span class="label_print" style="border:0">Объемный вес<br>Vol. WT</span>
<span class="value" style="text-align:center" ><?=sprintf('%0.2f', $arResult['INVOICE']['PROPERTY_OB_WEIGHT']);?></span>
</div>
</td>
<td colspan="2"><div style="height:50px;"><span class="label_print" style="border:0">Контр. взвеш.<br>Control WT</span></div></td>
<td>
<div style="height:50px;"><span class="label_print" style="border:0">Объявл. стоимость<br>Declared Value</span>
<span class="value"><?=$arResult['INVOICE']['PROPERTY_COST_VALUE'];?></span>
</div>
</td>
<td colspan='2'>
<table>
<tr>
<? posittion_and_attorney_show_1(); ?>
</tr>
<tr>
<td colspan="2">
<div style="height:33px;">
<span class="label test1" style="border:0" >ЗАЯВКУ ОФОРМИЛ</span>
<span class="value" style="font-size: 10pt;line-height:0.95;">
  <?
	$obElement = CIBlockElement::GetByID($arResult['INVOICE']['ID']);
	if($arEl = $obElement->GetNext()) 
	{
		  $rsUser = CUser::GetByID($arEl["CREATED_BY"]); 
		  $arUser = $rsUser->Fetch();
		  $Property_creator_name = $arUser["NAME"]." ".$arUser["LAST_NAME"];
	}
	?>
   <?  echo $Property_creator_name; ?>
</span>
</div>
</td>
</tr>
</table>
</td>
</tr>
</tbody>
</table>
		
        
		<span style="font-size:9px"> страница  <?=($i+1);?> из <?=ceil($cooef);?></span>
		<br/>
		</div>
		<? };?>
    </div>
	
<br/>
<br/>
	
	
    <? //MakeZakazPDF($arResult);
}


function posittion_and_attorney_show_1(){
?>
	<!-- вынесем в отдельный блок - т.к баг верстки -->
		<td width="110"><div style="width:115px; height:60px;text-align:center; border-collapse: collapse;border-bottom:1px solid #333333;border-right:1px solid #333333">
		<span class="label_print" style="border:0">ДОЛЖНОСТЬ</span>
		<span class="value" style="font-size: 10pt;line-height:0.95;">НАЧАЛЬНИК УТКиГ</span>
		</div></td>
		<td width="137"><div style="width:137px; height:60px;text-align:center; border-collapse: collapse;border-bottom:1px solid #333333">
		<span class="label_print" style="border:0">ДОВЕРЕННОСТЬ</span>
				

		<span class="value" style="font-size: 10pt;line-height:0.95;">&#8470;   288 от 09.08.2019</span>
		</div>
		</td>
	<!-- -->
<? } 

function posittion_and_attorney_show_2(){
?>
	<!-- вынесем в отдельный блок - т.к баг верстки -->
		<td width="110"><div style="width:110px; height:60px;text-align:center; border-collapse: collapse;border-bottom:1px solid #333333;border-right:1px solid #333333">
		<span class="label_print" style="border:0">ДОЛЖНОСТЬ</span>
		<span class="value" style="font-size: 10pt;line-height:0.95;">НАЧАЛЬНИК УТКиГ</span>
		</div></td>
		<td width="140"><div style="width:140px; height:60px;text-align:center; border-collapse: collapse;border-bottom:1px solid #333333">
		<span class="label_print" style="border:0">ДОВЕРЕННОСТЬ</span>
		<span class="value" style="font-size: 10pt;line-height:0.95;">&#8470;  289 от 08.08.2018</span>
		</div>
		</td>
	<!-- -->
<? } ?>
