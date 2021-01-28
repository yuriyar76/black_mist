<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
//echo "<!-- == <pre>";
//	print_r ($arResult['REQUEST']['PROPERTY_PACK_DESC_INFO']);
//echo "</pre> -->";

//echo "<!-- === <pre>";
//	print_r ($arResult['REQUEST']['Габариты']);
//echo "</pre> -->";

/*
[0] => Array
        (
            [name] => РђРЅС‚РµРЅРЅР° РђР—-003 в„–1505, РіСЂСѓР· РґР»СЏ РљРЅРђРђР— РёРј. Р®.Рђ. Р“Р°РіР°СЂРёРЅР°
            [place] => 1
            [weight] => 5
            [size] => Array
                (
                    [0] => 30
                    [1] => 30
                    [2] => 30
                )

            [gabweight] => 4.5
        )
*/		


  
//

//iconv('utf-8','windows-1251',$str['name']);





if (($arResult['OPEN']) && ($arResult['REQUEST']))
{
    /* увидим  кол-во записей */
	// перечисляем все виды описаний (любое количество) 
	$m = $arResult['REQUEST']['Габариты'];
	$s = array();
	// удалить все пустые элементы по имени
		foreach ($m as $k=>$v){
			 if (trim($v['name']) !=''){
				 $s[]= $v;
			 }	 
		}
	$cnt   = count($s);
	$cooef = (int)$cnt / 5;
	$partCnt = $cnt - ($cooef * 5); 
	if ($cnt < 5) {$cooef = 1;}
	
	$strIneerNameInvoice   = preg_replace ("/(.*)-(.*)$/", "$1", $arResult['REQUEST']['PROPERTY_MINIMAL_NUMBER_SERIES'][1][1]); 
	// наше текущее дополнение
	$intIneerNumberInvoice = preg_replace ("/(.*)-(.*)$/", "$2", $arResult['REQUEST']['PROPERTY_INNER_NUMBER_CLAIM_VALUE']);
	// количество записей
	?>
<div  id="print_block" class="print_block1">
	<?for ($i = 0; $i <= $cooef; $i++) { 
	     if  (($partCnt == 0)&&($i == $cooef)) {break;}
	?>
<!-- <? echo " = ".$cooef." = "; ?> -->	
<div class="a11"<? if (($cooef != 1) && ($i != $cooef)) {echo ' style="page-break-after: always;" ';}?>>
<div class="print_head_block1">
<div class="application_for_delivery_print_head">
<div class="application_for_delivery_print_head_inner">
	<? //<!-- заявка на доставку 0000000000000000 --> ?>
	<b>Заявка на доставку</b><br/>
		<?					
			$time = strtotime($arResult['REQUEST']['PROPERTY_MINIMAL_NUMBER_SERIES'][1][2]);      
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
			
			$dateCreate = strtotime($arResult['REQUEST']['DATA_CREATE']);
			
			
			
			$month = $month_name[ date( 'n',$dateCreate ) ]; 
			
			$day   = date( 'j',$dateCreate ); # С помощью функции date() получаем число дня
			$year  = date( 'Y',$dateCreate ); # Получаем год
			$hour  = date( 'G',$dateCreate ); # Получаем значение часа
			$min   = date( 'i',$dateCreate ); # Получаем минуты	

			$strdateCreate = " ".$day." ".$month."  ".$year . " г. ";								
			
				// добаялем исключение от 10.07.2019 12:37 Кирилл  
					if ($arResult['REQUEST']['НомерНакладной'] == "199-1785091") {
						$strdateCreate = "30.09.2019";
					}
				// **
				// $a = array ($arResult['REQUEST']['DATA_CREATE'], $month, $day, $year, $hour, $min, $strIneerNameInvoice);  
				// file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../logs/1cfilename_date2.txt', $a, FILE_APPEND);
				// **
			
		// получим здесь "дату заявки" прямо  из базы по ID
		// $arResult['REQUEST']['IDсСайта'] 
		
	    $a = "";
		$obElement = CIBlockElement::GetByID($arResult['REQUEST']['IDсСайта']);
		if($arEl = $obElement->GetNext()){
			$dateCreate_t = strtotime($arEl['DATE_CREATE']);
			$month_t = $month_name[ date( 'n',$dateCreate ) ]; 
			$day_t   = date( 'j',$dateCreate ); # С помощью функции date() получаем число дня
			$year_t  = date( 'Y',$dateCreate ); # Получаем год
			$hour_t  = date( 'G',$dateCreate ); # Получаем значение часа
			$min_t   = date( 'i',$dateCreate ); # Получаем минуты	
			$strdateCreateTrue = " ".$day_t." ".$month_t."  ".$year_t . " г. ";								
		};  
	    ?>
		<span> &#8470; <?=$strIneerNameInvoice;?>  от <?echo $strdate; ?>  </span><br/>
		<? if ($strIneerNameInvoice != $intIneerNumberInvoice) { ?>
		<? // если нет дополнений! ?>
		<span> дополнение &#8470;  <?=$intIneerNumberInvoice;?>  от  <?=$strdateCreate;?></span><br/>
	<? }; ?>
	<span style="font-size:9px"> к договору &#8470; 02317-01-19 от 01.07.2019 г. <?//=$dateCreate;?></span><br/>
</div>
</div>
<div class="img_print_head">
    <img alt="" src="/upload/iblock/ef4/image002.png" width="286" height="66">
</div>
<div class="adress_print_head">
    Тел: +7 495 783-99-18, г. Москва, Шоссейный проезд 10к1            </div>
<div  class="number" style="font-size:18px;max-width:300px;font-weight:bold;">
<script type="text/javascript">
$(document).ready(function() {
	JsBarcode("#barcode_<?=$i?>", "<?=$arResult['REQUEST']['НомерНакладной'];?>", {
	  format: "CODE39",
	  width: 2,
	  height: 60,
	  displayValue: false
	});
});
</script>
<svg style="height:70%;width:70%;padding-bottom:10px;margon-left:0" id="barcode_<?=$i?>" class="target2"></svg><br/>
<p style="width:201px;text-align:center;margin-top:15px;">
<?=$arResult['REQUEST']['НомерНакладной'];?>
</p> 
</div>
</div>

<table cellpadding="0" cellspacing="0" border="1"  bordercolor="#333333">
<tbody>
<tr>
<td rowspan="5" width="30" bgcolor="#f4ecc5" style="vertical-align:middle;border-left:1px solid black">
<div style="width:30px; height:200px;">
	<img width="30" height="200" alt="Отправитель" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l1.png">
</div>
</td>
<td width="380">
<div style="width:380px; min-height:40px;">
	<span class="label" style="border:0">Фамилия Отправителя / Shipper's Last Name</span>
	<span class="value"><?=$arResult['REQUEST']['ФамилияОтправителя'];?></span>
</div>
</td>
<td width="220" rowspan="2">
<div style="width:220px; height:80px;">
<span class="label" style="border:0">Телефон / Phone</span>
<span class="value"><?=$arResult['REQUEST']['ТелефонОтправителя'];?></span>
</div>
</td>
<td rowspan="5" width="30" bgcolor="#d6ffcc" style="vertical-align:middle;">
<div style="width:30px; height:200px;">
<img width="30" height="200" alt="Условия доставки" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l3.png">
</div>
</td>
<td width="90" rowspan="2"> 
<div style="width:90px; height:80px;">
<span class="label" style="border:0">&nbsp;</span>
<span class="value"><? //$arResult['REQUEST']['ПризнакТипДоставки'];?></span>
</div>
</td>
<td rowspan="5" width="30" bgcolor="#d6ffcc" style="vertical-align:middle;">
<div style="width:30px; height:200px;">
<img width="30" height="200" alt="Условия оплаты" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l4.png">
</div>
</td>
<td width="105" rowspan="3">
<div style="width:105px; height:120px;">
<span class="label" style="border:0">Оплачивает</span>
<span class="value"><? //=$arResult['REQUEST']['ПризнакПлательщик'];?></span>
</div>
</td>
</tr>
<tr>
<td width="380">
<div style="width:380px; height:40px;">
<span class="label" style="border:0">Компания-Отправитель / Shipping Company</span>
<span class="value" style="font-size: 11pt; line-height: 0.85;"><?=(strlen($arResult['REQUEST']['КомпанияОтправителя'])) ? $arResult['REQUEST']['КомпанияОтправителя'] : $arResult['REQUEST']['ВыборОтправителя'];?></span>
</div>
</td>
</tr>
<tr>
<td width="380">
<div style="width:380px; height:40px;">
<span class="label" style="border:0">Страна / Country</span>
<span class="value"><?=$arResult['REQUEST']['СтранаОтправителя'];?></span>
</div>
</td>
<td width="90">
<div style="width:210px; min-height:40px;">
<span class="label" style="border:0">Область / State</span>
<span class="value"><?=$arResult['REQUEST']['ОбластьОтправителя'];?></span>
</div>
</td>
<td width="90" rowspan="3"></td>
</tr>
<tr>
<td width="380">
<div style="width:380px; height:40px;">
<span class="label" style="border:0">Город / City</span>
<span class="value"><?=$arResult['REQUEST']['ГородОтправителя'];?></span>
</div>
</td>
<td width="220">
<div style="width:220px; height:40px;">
<span class="label" style="border:0">Индекс / Postal Code</span>
<span class="value"><?=$arResult['REQUEST']['ИндексОтправителя'];?></span>
</div>
</td>
<td width="105" rowspan="2">
<div style="width:105px; height:80px;">
<span class="label" style="border:0">Оплата</span>
<span class="value"><? //=$arResult['REQUEST']['ПризнакТипОплаты'];?></span>
</div>
</td>
</tr>
<tr>
<td width="600" colspan="2">
<div style="width:600px; height:40px;">
<span class="label" style="border:0">Адрес / Street Address</span>
<span class="value" style="font-size: 11pt; line-height: 0.85;"><?=$arResult['REQUEST']['АдресОтправителя'];?></span>
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
<img width="30" height="200" alt="Получатель" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l2.png">
</div>
</td>
<td>
<div style="width:380px; min-height:40px;">
<span class="label" style="border:0">Фамилия Получателя / Consignee's Last Name</span>
<span class="value"><?=$arResult['REQUEST']['ФамилияПолучателя'];?></span>
</div>
</td>
<td  rowspan="2">
<div style="width:210px; height:80px;">
<span class="label" style="border:0">Телефон / Phone</span>
<span class="value"><?=$arResult['REQUEST']['ТелефонПолучателя'];?></span>
</div>
</td>
<td colspan="3" rowspan="6"  style="border-right:1px solid black">
<div style="width:267px; min-height:120px;font-size:12px;">
<span class="label" style="font-size:12px;border:0;white-space: normal;">СПЕЦИАЛЬНЫЕ ИНСТРУКЦИИ / SPECIAL INSTRUCTIONS</span>
<span class="value" style="font-size:10px;"><?=$arResult['REQUEST']['СпециальныеИнструкции'];?></span>
</div>
</td>
</tr>
<tr>
<td >
<div style="width:380px; min-height:40px;">
<span class="label" style="border:0">Компания-Получатель / Consignee Company</span>
<span class="value" style="font-size: 10pt; line-height: 0.85;"><?=(strlen($arResult['REQUEST']['КомпанияПолучателя'])) ? $arResult['REQUEST']['КомпанияПолучателя'] : $arResult['REQUEST']['ВыборПолучателя'];?></span>
</div>
</td>
</tr>
<tr>
<td >
<div style="width:380px; height:40px;">
<span class="label" style="border:0">Страна / Country</span>
<span class="value"><?=$arResult['REQUEST']['СтранаПолучателя'];?></span>
</div>
</td>
<td >
<div style="width:210px; min-height:40px;">
<span class="label" style="border:0">Область / State</span>
<span class="value"><?=$arResult['REQUEST']['ОбластьПолучателя'];?></span>
</div>
</td>
</tr>
<tr>
<td >
<div style="width:380px; height:40px;">
<span class="label" style="border:0">Город / City</span>
<span class="value"><?=$arResult['REQUEST']['ГородПолучателя'];?></span>
</div>
</td>
<td  colspan="1">
<div style="width:210px; height:40px;">
<span class="label" style="border:0">Индекс / Postal Code</span>
<span class="value"><?=$arResult['REQUEST']['ИндексПолучателя'];?></span>
</div>
</td>
</tr>
<tr>
<td  colspan="2">
<div style="height:40px;">
<span class="label" style="border:0">Адрес / Street Address</span>
<span class="value" style="font-size: 11pt; line-height: 0.85;"><?=$arResult['REQUEST']['АдресПолучателя'];?></span>
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
<img width="30" height="180" alt="Описание отправления" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l5.png">
</div>
</td>
<td colspan="3" >
<div style="width:298px;"></div>
</td>
<td  align="center">
<div style="width:80px; ">
<span class="label" style="border:0">Мест<br>Pieces</span>
</div>
</td>
<td align="center">
<div style="width:80px; ">
<span class="label" style="border:0">Вес<br>Weight</span>
</div>
</td>
<td  align="center">
<div style="width:140px;">
<span class="label" style="border:0">Габариты (см х см х см)<br>Dimensions (cm x cm x cm)</span>
</div>
</td>
<td colspan="2" rowspan="6" >
<div style="width:237px; padding-right:10px;padding-left:10px;">
<span class="label" style="text-align:left;border:0">ПОДПИСЬ ЛИЦА,  УПОЛНОМОЧЕННОГО НА <br> <br> ПОДПИСАНИЕ И ПОДАЧУ ЗАЯВОК</span>
<span class="value" style="font-size: 10pt;line-height:0.95;margin-top:5px;">Муратов Вадим Геннадьевич</span>
</div>
</td>
</tr>

<?
 $a_place = 0;
 $a_weight = 0;	
 $a_weightW = 0;												
?>

<? //foreach ($arResult['INVOICE']['PACK_DESCR'] as $k=>$v) { ?>	  
<?for ($n = 1; $n <= 5; $n++) {  
    $k = (($i) * 5 ) + $n;    

	$a_place   =  $a_place + $arResult['REQUEST']['PROPERTY_PACK_DESC_INFO'][($k-1)]['place'];
	$a_weight  =  $a_weight + $arResult['REQUEST']['PROPERTY_PACK_DESC_INFO'][($k-1)]['weight'];
	$a_weightW =  $a_weightW + ($arResult['REQUEST']['PROPERTY_PACK_DESC_INFO'][($k-1)]['size'][0] + $arResult['REQUEST']['PROPERTY_PACK_DESC_INFO'][($k-1)]['size'][1] + $arResult['REQUEST']['PROPERTY_PACK_DESC_INFO'][($k-1)]['size'][2]) / 6000 ;
?>
<tr>
<td colspan="3">
<div style="width:298px; min-height:20px;text-align:center">
<span class="value <?=$k ?>">
<?=iconv('utf-8','windows-1251',$arResult['REQUEST']['PROPERTY_PACK_DESC_INFO'][($k-1)]['name']);?></span>
</div>
</td>
<? if ($arResult['REQUEST']['PROPERTY_PACK_DESC_INFO'][($k-1)]['place'] != '0') { ?>
	<td>
		<div class="desc<?=$k;?>" style="width:80px; height:20px;text-align:center"><span class="value">
			 <?=$arResult['REQUEST']['PROPERTY_PACK_DESC_INFO'][($k-1)]['place'];?></span>
		</div>
	</td>
	<td>
	<div style="width:80px; height:20px;text-align:center">
		<span class="value"> 
			<?=$arResult['REQUEST']['PROPERTY_PACK_DESC_INFO'][($k-1)]['weight'];?>
		</span>
	</div>
	</td>
	<td>
	<div style="width:140px; height:20px;text-align:center">
		<span class="value">
		<?=$arResult['REQUEST']['PROPERTY_PACK_DESC_INFO'][($k-1)]['size'][0]."x".$arResult['REQUEST']['PROPERTY_PACK_DESC_INFO'][($k-1)]['size'][1]."x".$arResult['REQUEST']['PROPERTY_PACK_DESC_INFO'][($k-1)]['size'][2];?>
		</span>
	</div>
	</td>
<? }  else  {?>
	<td><div class="desc<?=$k;?>" style="width:80px; height:20px;text-align:center"><span class="value"></span></div></td>
	<td><div style="width:80px; height:20px;text-align:center"><span class="value"></span></div></td>
	<td><div style="width:140px; height:20px;text-align:center"><span class="value"></span></div></td>
<? } ?>
</tr>
<?  } 

//for ($x=1; $x<=($cnt+1);$x++){

//$a_place  =  $a_place + $arResult['REQUEST']['Габариты']['Габарит_'.$x]['КоличествоМест'];
//$a_weight = $a_weight + $arResult['REQUEST']['Габариты']['Габарит_'.$x]['ВесОтправления'];
//$a_weightW = $a_weightW + (($arResult['REQUEST']['Габариты']['Габарит_'.$x]['Длина'] *  $arResult['REQUEST']['Габариты']['Габарит_'.$x]['Ширина'] *  $arResult['REQUEST']['Габариты']['Габарит_'.$x]['Высота']) / 6000);
//}					
?>
<tr>
<td >
<div style="height:50px; width:98px;">
<span class="label" style="border:0">Мест<br>Pieses</span>
<span class="value"  style="text-align:center" ><?=$a_place;?></span>
</div>
</td>
<td>
<div style="height:50px; width:98px">
<span class="label" style="border:0">Вес<br>Weight</span>
<span class="value" style="text-align:center" ><?=$a_weight;?></span>
</div>
</td>
<td >
<div style="height:50px; width:98px;"><span class="label" style="border:0">Объемный вес<br>Vol. WT</span>
<span class="value" style="text-align:center" ><?= sprintf('%0.2f', $arResult['REQUEST']['ВесВходящийОбъемный']);?></span>
</div>
</td>
<td colspan="2"><div style="height:50px;"><span class="label" style="border:0">Контр. взвеш.<br>Control WT</span></div></td>
<td>
<div style="height:50px;"><span class="label" style="border:0">Объявл. стоимость<br>Declared Value</span>
<span class="value"><?=$arResult['REQUEST']['ОбъявленнаяСтоимость'];?></span>
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
	$obElement = CIBlockElement::GetByID($arResult['REQUEST']['IDсСайта']);
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
</div>
<? };?>
</div>
<br/>

<br/><br/>
<? 
}

function posittion_and_attorney_show_1(){
?>
	<!-- вынесем в отдельный блок - т.к баг верстки -->
		<td width="110"><div style="width:115px; height:60px;text-align:center; border-collapse: collapse;border-bottom:1px solid #333333;border-right:1px solid #333333">
		<span class="label" style="border:0">ДОЛЖНОСТЬ</span>
		<span class="value" style="font-size: 10pt;line-height:0.95;">НАЧАЛЬНИК УТКиГ</span>
		</div></td>
		<td width="137"><div style="width:137px; height:60px;text-align:center; border-collapse: collapse;border-bottom:1px solid #333333">
		<span class="label" style="border:0">ДОВЕРЕННОСТЬ</span>
		<span class="value" style="font-size: 10pt;line-height:0.95;">&#8470; 288 от 09.08.2019</span>
		</div>
		</td>
	<!-- -->
<? } ?>
