<?
set_include_path($_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/PhpExcel/Classes/');
include_once 'PHPExcel.php';
	
$pExcel = new PHPExcel();
$pExcel->setActiveSheetIndex(0);
$aSheet = $pExcel->getActiveSheet();
	
$pExcel->getDefaultStyle()->getFont()->setName('Arial');
$pExcel->getDefaultStyle()->getFont()->setSize(10);
	
$Q = iconv("windows-1251", "utf-8", 'Манифест от '. $arResult['start']);
$aSheet->setTitle($Q);
	
	$boldFont = array(
		'font'=>array(
			'bold'=>true
		)
	);
	
	$small = array(
		'font'=>array(
			'size' => 8
		),
	);
	
	$center = array(
				'alignment'=>array(
					'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical'=>PHPExcel_Style_Alignment::VERTICAL_TOP
				)
			);
			
	$right = array(
		'alignment'=>array(
					'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
					'vertical'=>PHPExcel_Style_Alignment::VERTICAL_TOP
				)
			);
			
	$table = array(
		'alignment'=>array(
			'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			'vertical'=>PHPExcel_Style_Alignment::VERTICAL_TOP
		)
	);
			
	$head_style = array(
		'font' => array(
			'bold' => true,
		),
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		),
		'fill' => array(
			'type' => PHPExcel_Style_Fill::FILL_SOLID,
			'startcolor' => array(
				'argb' => 'FFFFF4E9',
			),
		),
	);
	
	$footer_style = array(
		'fill' => array(
			'type' => PHPExcel_Style_Fill::FILL_SOLID,
			'startcolor' => array(
				'argb' => 'FFE9FEFF',
			),
		),
	);
	
	$styleArray = array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('argb' => 'FF000000'),
			),
		),
	);
	
	$Q = iconv("windows-1251", "utf-8", 'Манифест от '. $arResult['start']);
	$aSheet->setCellValue('b2',$Q);
	
	$i = 4;
	foreach ($arResult["Cells_1"] as $k => $v)
	{
		$Q = iconv("windows-1251", "utf-8", $k);
		$aSheet->setCellValue('C'.$i,$Q);
		$Q = iconv("windows-1251", "utf-8", $v);
		$aSheet->setCellValue('D'.$i,$Q);
		$aSheet->getStyle('C'.$i)->applyFromArray($boldFont)->applyFromArray($right);
		$i++;
	}
	
	$i = 4;
	foreach ($arResult["Cells_2"] as $v)
	{
		$Q = iconv("windows-1251", "utf-8", $v);
		$aSheet->setCellValue('O'.$i,$Q);
		$aSheet->getStyle('O'.$i)->applyFromArray($right)->applyFromArray($small);
		$aSheet->mergeCells("O".$i.":Q".$i);
		$i++;
	}
	
	$aSheet->getStyle('O8:O10')->applyFromArray($boldFont);
	
	$i = 12;
	$arJ = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q');
	foreach  ($arResult["Cells_3"] as $k)
	{
		foreach ($k as $n => $v)
		{
			$num_sel = $arJ[$n].$i;
			$Q = iconv("windows-1251", "utf-8", $v);
			$aSheet->setCellValue($num_sel,$Q);
		}
		$i++;
	}
	$i--;
	
	$aSheet->getStyle('B12:Q12')->applyFromArray($head_style);
	$aSheet->getStyle('B'.$i.':Q'.$i)->applyFromArray($footer_style);
	
	$aSheet->getColumnDimension('A')->setWidth(3);
	$aSheet->getColumnDimension('B')->setWidth(17);
	$aSheet->getColumnDimension('C')->setWidth(17);
	$aSheet->getColumnDimension('D')->setWidth(17);
	$aSheet->getColumnDimension('E')->setWidth(17);
	$aSheet->getColumnDimension('F')->setWidth(17);
	$aSheet->getColumnDimension('G')->setWidth(17);
	$aSheet->getColumnDimension('H')->setWidth(17);
	$aSheet->getColumnDimension('I')->setWidth(17);
	$aSheet->getColumnDimension('J')->setWidth(17);
	$aSheet->getColumnDimension('K')->setWidth(17);
	$aSheet->getColumnDimension('L')->setWidth(17);
	$aSheet->getColumnDimension('M')->setWidth(17);
	$aSheet->getColumnDimension('N')->setWidth(17);
	$aSheet->getColumnDimension('O')->setWidth(17);
	$aSheet->getColumnDimension('P')->setWidth(17);
	$aSheet->getColumnDimension('Q')->setWidth(17);
	
	$aSheet->getStyle('B2')->applyFromArray($boldFont)->applyFromArray($center);
	$aSheet->mergeCells("B2:Q2");
	
	$aSheet->getStyle('B12:Q'.$i)->getAlignment()->setWrapText(true);
	$aSheet->getStyle('B12:Q'.$i)->applyFromArray($styleArray);
	$aSheet->getStyle('A1:Q'.$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

	
	include("PHPExcel/Writer/Excel5.php");
	$objWriter = new PHPExcel_Writer_Excel5($pExcel);
	
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="'.$arResult['start'].'.xls"');
	header('Cache-Control: max-age=0');
	$objWriter->save('php://output');
?>