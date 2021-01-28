<?
CModule::IncludeModule("iblock");
CModule::IncludeModule("currency");

define($_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/fpdf17/font/');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/fpdf17/fpdf.php');

/****************класс работы с pdf****************/	
class PDF_MC_Table extends FPDF
{
	var $widths;
	var $aligns;
	var $extgstates;
	function SetWidths($w)
	{
		$this->widths=$w;
	}
	function SetAligns($a)
	{
	$this->aligns=$a;
	}
	function Row($data, $header = false, $noborder = false, $mezh = 5)
	{
		$nb = 0;
		foreach ($data as $i => $val)
		{
			if (is_array($val))
			{
				$v = $val['value'];
			}
			else
			{
				$v = $val;
			}
			$nb = max($nb,$this->NbLines($this->widths[$i],$v));
		}
		$h = $mezh*$nb;
		$this->CheckPageBreak($h);
		foreach ($data as $i => $val)
		{
			if (is_array($val)) 
			{
				$v = $val['value'];
			}
			else
			{
				$v = $val;
			}
			$w = $this->widths[$i];
			if (strlen($val['align'])) 
			{
				$a = $val['align'];
			}
			else
			{ 
				$a = ($header) ? 'C' : 'L';
			}
			$x = $this->GetX();
			$y = $this->GetY();
			if ($noborder)
			{
				$border = 'F';
			}
			else 
			{
				$border = 'D';
			}
			$this->Rect($x,$y,$w,$h,$border);
			$this->SetAlpha(1);
			$this->MultiCell($w,5,$v,0,$a);
			$this->SetXY($x+$w,$y);
		}
		$this->Ln($h);
	}
	
	function RowNew($data, $align = 'J', $linestyle = 'D')
	{
		$nb = 0;
		foreach ($data as $i => $val)
		{
			$nb = max($nb,$this->NbLines($this->widths[$i],$val));
		}
		$h = 5*$nb;
		$this->CheckPageBreak($h);
		foreach ($data as $i => $val)
		{
			$w = $this->widths[$i];
			$x = $this->GetX();
			$y = $this->GetY();
			$this->Rect($x,$y,$w,$h, $linestyle);
			$this->SetAlpha(1);
			$this->MultiCell($w,5,$val,0, $align);
			$this->SetXY($x+$w,$y);
		}
		$this->Ln($h);
	}
	
	function CheckPageBreak($h)
	{
		if($this->GetY()+$h>$this->PageBreakTrigger)
		{
			$this->AddPage($this->CurOrientation);
		}
	}
	
	function NbLines($w, $txt)
	{
		$cw=&$this->CurrentFont['cw'];
		if ($w==0)
		{
			$w=$this->w-$this->rMargin-$this->x;
		}
		$wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
		$s=str_replace("\r",'',$txt);
		$nb=strlen($s);
		if ($nb>0 and $s[$nb-1]=="\n")
		{
			$nb--;
		}
		$sep=-1;
		$i=0;
		$j=0;
		$l=0;
		$nl=1;
		while($i<$nb)
		{
			$c=$s[$i];
			if ($c=="\n")
			{
				$i++;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
				continue;
			}
			if ($c==' ')
			{
				$sep=$i;
			}
			$l+=$cw[$c];
			if ($l>$wmax)
			{
				if ($sep==-1)
				{
					if ($i==$j)
					{
						$i++;
					}
				}
				else
				{
					$i=$sep+1;
				}
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
			}
			else
			{
				$i++;
			}
		}
		return $nl;
	}
	
	function Code39($x, $y, $code, $ext = true, $cks = false, $w = 0.4, $h = 20, $wide = true)
	{
		if ($ext)
		{
			$code = $this->encode_code39_ext($code);
		}
		else
		{
			$code = strtoupper($code);
			if(!preg_match('|^[0-9A-Z. $/+%-]*$|', $code))
			{
				$this->Error('Invalid barcode value: '.$code);
			}
		}
		if ($cks)
		{
			$code .= $this->checksum_code39($code);
		}
		$code = '*'.$code.'*';
		$narrow_encoding = array (
			'0' => '101001101101', '1' => '110100101011', '2' => '101100101011', 
			'3' => '110110010101', '4' => '101001101011', '5' => '110100110101', 
			'6' => '101100110101', '7' => '101001011011', '8' => '110100101101', 
			'9' => '101100101101', 'A' => '110101001011', 'B' => '101101001011', 
			'C' => '110110100101', 'D' => '101011001011', 'E' => '110101100101', 
			'F' => '101101100101', 'G' => '101010011011', 'H' => '110101001101', 
			'I' => '101101001101', 'J' => '101011001101', 'K' => '110101010011', 
			'L' => '101101010011', 'M' => '110110101001', 'N' => '101011010011', 
			'O' => '110101101001', 'P' => '101101101001', 'Q' => '101010110011', 
			'R' => '110101011001', 'S' => '101101011001', 'T' => '101011011001', 
			'U' => '110010101011', 'V' => '100110101011', 'W' => '110011010101', 
			'X' => '100101101011', 'Y' => '110010110101', 'Z' => '100110110101', 
			'-' => '100101011011', '.' => '110010101101', ' ' => '100110101101', 
			'*' => '100101101101', '$' => '100100100101', '/' => '100100101001', 
			'+' => '100101001001', '%' => '101001001001' 
		);
		$wide_encoding = array (
			'0' => '101000111011101', '1' => '111010001010111', '2' => '101110001010111', 
			'3' => '111011100010101', '4' => '101000111010111', '5' => '111010001110101', 
			'6' => '101110001110101', '7' => '101000101110111', '8' => '111010001011101', 
			'9' => '101110001011101', 'A' => '111010100010111', 'B' => '101110100010111', 
			'C' => '111011101000101', 'D' => '101011100010111', 'E' => '111010111000101', 
			'F' => '101110111000101', 'G' => '101010001110111', 'H' => '111010100011101', 
			'I' => '101110100011101', 'J' => '101011100011101', 'K' => '111010101000111', 
			'L' => '101110101000111', 'M' => '111011101010001', 'N' => '101011101000111', 
			'O' => '111010111010001', 'P' => '101110111010001', 'Q' => '101010111000111', 
			'R' => '111010101110001', 'S' => '101110101110001', 'T' => '101011101110001', 
			'U' => '111000101010111', 'V' => '100011101010111', 'W' => '111000111010101', 
			'X' => '100010111010111', 'Y' => '111000101110101', 'Z' => '100011101110101', 
			'-' => '100010101110111', '.' => '111000101011101', ' ' => '100011101011101', 
			'*' => '100010111011101', '$' => '100010001000101', '/' => '100010001010001', 
			'+' => '100010100010001', '%' => '101000100010001'
		);
		$encoding = $wide ? $wide_encoding : $narrow_encoding;
		$gap = ($w > 0.29) ? '00' : '0';
		$encode = '';
		for ($i = 0; $i< strlen($code); $i++)
		{
			$encode .= $encoding[$code{$i}].$gap;
		}
		$this->draw_code39($encode, $x, $y, $w, $h);
	}
	
	function checksum_code39($code)
	{
		$chars = array(
			'0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 
			'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 
			'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 
			'W', 'X', 'Y', 'Z', '-', '.', ' ', '$', '/', '+', '%'
		);
		$sum = 0;
		for ($i=0 ; $i<strlen($code); $i++)
		{
			$a = array_keys($chars, $code{$i});
			$sum += $a[0];
		}
		$r = $sum % 43;
		return $chars[$r];
	}
	
	function encode_code39_ext($code)
	{
		$encode = array(
			chr(0) => '%U', chr(1) => '$A', chr(2) => '$B', chr(3) => '$C',
			chr(4) => '$D', chr(5) => '$E', chr(6) => '$F', chr(7) => '$G', 
			chr(8) => '$H', chr(9) => '$I', chr(10) => '$J', 
			chr(12) => '$L', chr(13) => '$M', chr(14) => '$N', chr(15) => '$O', 
			chr(16) => '$P', chr(17) => '$Q', chr(18) => '$R', chr(19) => '$S', 
			chr(20) => '$T', chr(21) => '$U', chr(22) => '$V', chr(23) => '$W', 
			chr(24) => '$X', chr(25) => '$Y', chr(26) => '$Z', chr(27) => '%A', 
			chr(28) => '%B', chr(29) => '%C', chr(30) => '%D', chr(31) => '%E', 
			chr(32) => ' ', chr(33) => '/A', chr(34) => '/B', chr(35) => '/C', 
			chr(36) => '/D', chr(37) => '/E', chr(38) => '/F', chr(39) => '/G', 
			chr(40) => '/H', chr(41) => '/I', chr(42) => '/J', chr(43) => '/K', 
			chr(44) => '/L', chr(45) => '-', chr(46) => '.', chr(47) => '/O', 
			chr(48) => '0', chr(49) => '1', chr(50) => '2', chr(51) => '3', 
			chr(52) => '4', chr(53) => '5', chr(54) => '6', chr(55) => '7', 
			chr(56) => '8', chr(57) => '9', chr(58) => '/Z', chr(59) => '%F', 
			chr(60) => '%G', chr(61) => '%H', chr(62) => '%I', chr(63) => '%J', 
			chr(64) => '%V', chr(65) => 'A', chr(66) => 'B', chr(67) => 'C', 
			chr(68) => 'D', chr(69) => 'E', chr(70) => 'F', chr(71) => 'G', 
			chr(72) => 'H', chr(73) => 'I', chr(74) => 'J', chr(75) => 'K', 
			chr(76) => 'L', chr(77) => 'M', chr(78) => 'N', chr(79) => 'O', 
			chr(80) => 'P', chr(81) => 'Q', chr(82) => 'R', chr(83) => 'S', 
			chr(84) => 'T', chr(85) => 'U', chr(86) => 'V', chr(87) => 'W', 
			chr(88) => 'X', chr(89) => 'Y', chr(90) => 'Z', chr(91) => '%K', 
			chr(92) => '%L', chr(93) => '%M', chr(94) => '%N', chr(95) => '%O', 
			chr(96) => '%W', chr(97) => '+A', chr(98) => '+B', chr(99) => '+C', 
			chr(100) => '+D', chr(101) => '+E', chr(102) => '+F', chr(103) => '+G', 
			chr(104) => '+H', chr(105) => '+I', chr(106) => '+J', chr(107) => '+K', 
			chr(108) => '+L', chr(109) => '+M', chr(110) => '+N', chr(111) => '+O', 
			chr(112) => '+P', chr(113) => '+Q', chr(114) => '+R', chr(115) => '+S', 
			chr(116) => '+T', chr(117) => '+U', chr(118) => '+V', chr(119) => '+W', 
			chr(120) => '+X', chr(121) => '+Y', chr(122) => '+Z', chr(123) => '%P', 
			chr(124) => '%Q', chr(125) => '%R', chr(126) => '%S', chr(127) => '%T'
		);
	
		$code_ext = '';
		for ($i = 0 ; $i<strlen($code); $i++)
		{
			if (ord($code{$i}) > 127)
			{
				$this->Error('Invalid character: '.$code{$i});
			}
			$code_ext .= $encode[$code{$i}];
		}
		return $code_ext;
	}
	
	function draw_code39($code, $x, $y, $w, $h)
	{
		for($i=0; $i<strlen($code); $i++)
		{
			if($code{$i} == '1')
			{
				$this->Rect($x+$i*$w, $y, $w, $h, 'F');
			}
		}
	}

    function AlphaPDF($orientation='P', $unit='mm', $format='A4')
    {
        parent::FPDF($orientation, $unit, $format);
        $this->extgstates = array();
    }

    // alpha: real value from 0 (transparent) to 1 (opaque)
    // bm:    blend mode, one of the following:
    //          Normal, Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn, 
    //          HardLight, SoftLight, Difference, Exclusion, Hue, Saturation, Color, Luminosity
    function SetAlpha($alpha, $bm='Normal')
    {
        // set alpha for stroking (CA) and non-stroking (ca) operations
        $gs = $this->AddExtGState(array('ca'=>$alpha, 'CA'=>$alpha, 'BM'=>'/'.$bm));
        $this->SetExtGState($gs);
    }

    function AddExtGState($parms)
    {
        $n = count($this->extgstates)+1;
        $this->extgstates[$n]['parms'] = $parms;
        return $n;
    }

    function SetExtGState($gs)
    {
        $this->_out(sprintf('/GS%d gs', $gs));
    }

    function _enddoc()
    {
        if(!empty($this->extgstates) && $this->PDFVersion<'1.4')
            $this->PDFVersion='1.4';
        parent::_enddoc();
    }

    function _putextgstates()
    {
        for ($i = 1; $i <= count($this->extgstates); $i++)
        {
            $this->_newobj();
            $this->extgstates[$i]['n'] = $this->n;
            $this->_out('<</Type /ExtGState');
            foreach ($this->extgstates[$i]['parms'] as $k=>$v)
                $this->_out('/'.$k.' '.$v);
            $this->_out('>>');
            $this->_out('endobj');
        }
    }

    function _putresourcedict()
    {
        parent::_putresourcedict();
        $this->_out('/ExtGState <<');
        foreach($this->extgstates as $k=>$extgstate)
            $this->_out('/GS'.$k.' '.$extgstate['n'].' 0 R');
        $this->_out('>>');
    }

    function _putresources()
    {
        $this->_putextgstates();
        parent::_putresources();
    }
}

/******информация о компании по пользователю*******/
function GetCurrentAgent($u_id)
{
	$rsUser = CUser::GetByID($u_id);
	$arUser = $rsUser->Fetch();
	if (intval($arUser['UF_COMPANY_RU_POST']) > 0)
	{
		$agent['id'] = intval($arUser['UF_COMPANY_RU_POST']);
		$arFields = array();
		$res = CIBlockElement::GetList(
			array("ID" => "asc"), 
			array("IBLOCK_ID" => 40, "ID" => $agent['id']), 
			false, 
			false, 
			array("PROPERTY_TYPE","PROPERTY_DEMO","NAME","PROPERTY_ID_IN","PROPERTY_LEGAL_NAME","PROPERTY_UK","PROPERTY_INN", "PROPERTY_CITY", 'PROPERTY_CITY.NAME' ,'PROPERTY_ON_PAGE', 'PROPERTY_REGION', 'PROPERTY_PREFIX_REPORTS','PROPERTY_IM_BY','PROPERTY_PHONES','PROPERTY_ADRESS','PROPERTY_ADRESS_FACT')
		);
		if ($ob = $res->GetNextElement())
		{
			$arFields = $ob->GetFields();
			$agent['type'] = $arFields['PROPERTY_TYPE_ENUM_ID'];
			$agent['demo'] = $arFields['PROPERTY_DEMO_VALUE'];
			$agent['name'] = $arFields['NAME'];
			$agent['user_name'] = $arUser["LAST_NAME"].' '.$arUser['NAME'];
			$agent['id_in'] = $arFields['PROPERTY_ID_IN_VALUE'];
			$agent['legal_name'] = str_replace("&quot;", '"', $arFields['PROPERTY_LEGAL_NAME_VALUE']);
			$agent['uk'] = $arFields['PROPERTY_UK_VALUE'];
			$agent['inn'] = $arFields['PROPERTY_INN_VALUE'];
			$agent['city'] = $arFields['PROPERTY_CITY_VALUE'];
			$agent['city_name'] = $arFields['PROPERTY_CITY_NAME'];
			$agent['on_page'] = $arFields['PROPERTY_ON_PAGE_VALUE'];
			$agent['region'] = $arFields['PROPERTY_REGION_VALUE'];
			$agent['prefix'] = $arFields['PROPERTY_PREFIX_REPORTS_VALUE'];
			$agent['phones'] = $arFields['PROPERTY_PHONES_VALUE'];
			$agent['adress'] = $arFields['PROPERTY_ADRESS_VALUE'];
			$agent['adress_fact'] = $arFields['PROPERTY_ADRESS_FACT_VALUE'];
			$agent['region_cities'] = array();
			$res2 = CIBlockElement::GetList(
				array("ID" => "asc"), 
				array("IBLOCK_ID" => 6, "SECTION_ID" => $agent['region']), 
				false, 
				false, 
				array('ID')
			);
			while ($ob2 = $res2->GetNextElement())
			{
				$arFields2 = $ob2->GetFields();
				$agent['region_cities'][] = $arFields2['ID'];
			}
			$agent['uk_city_id'] = 0;
			if (intval($agent['uk']) > 0)
			{
				$db_props = CIBlockElement::GetProperty(40, $agent['uk'], array("sort" => "asc"), Array("CODE"=>"CITY"));
				if ($ar_props = $db_props->Fetch())
				{
					$agent['uk_city_id'] = $ar_props["VALUE"];
				}
			}
		}
		else
		{
			$agent = false;
		}
	}
	else
	{
		$agent = false;
	}
	return $agent;
}

/*********полное название города по его ID*********/
function GetFullNameOfCity($city_id, $onlyname = false, $returnarr = false)
{
	if (intval($city_id) > 0)
	{
		$name_of_city = false;
		$arSelect = Array("ID","NAME","IBLOCK_SECTION_ID");
		$arFilter = Array("IBLOCK_ID" => 6, "ID" => $city_id);
		$res = CIBlockElement::GetList(Array("NAME"=>"asc"), $arFilter, false, false, $arSelect);
		if ($ob = $res->GetNextElement())
		{
			$a = $ob->GetFields();
			$res2 = CIBlockSection::GetByID($a["IBLOCK_SECTION_ID"]);
			if($ar_res2 = $res2->GetNext())
			{
				$a["S_1"] = $ar_res2["NAME"];
				if (intval($ar_res2["IBLOCK_SECTION_ID"]) > 0)
				{
					$res3 = CIBlockSection::GetByID(intval($ar_res2["IBLOCK_SECTION_ID"]));
					if($ar_res3 = $res3->GetNext())
					{ 
						$a["S_2"] = $ar_res3["NAME"];
					}
				}
			}
			$name_of_city = $a["NAME"].', '.$a["S_1"].', '.$a["S_2"];
		}
		if ($returnarr)
		{
			return array($a["NAME"], $a["S_1"], $a["S_2"]);
		}
		else
		{
			return ($onlyname) ? $a["NAME"] : $name_of_city;
		}
	}
	else
	{
		return '';
	}
}

/******************список заказов******************/
function GetListOfPackeges(
	$super_agent_array, 
	$agent_id = 0, 
	$pack_id = false, 
	$status = false, 
	$available_agents = false,
	$man_id = 0, 
	$demo = false, 
	$city_f = 0, 
	$short_status = 0,
	$date_from = '',
	$date_to = '',
	$CREATED_USER_ID = 0, 
	$use_navigation = true, 
	$not_in_report = false, 
	$sort_array = array("ID" => "DESC"), 
	$id_report = 0, 
	$buh = 0, 
	$number_order = false, 
	$not_msk = false,
	$only_iskl = false,
	$only_returns = false,
	$date_to_delivery_from = '',
	$date_to_delivery_to = '',
	$only_not_iskl = false,
	$bytopdelivery = false
	)
{
	$arFields = array();
	$select = array(
		"ID","NAME","PROPERTY_N_ZAKAZ","PROPERTY_RECIPIENT","PROPERTY_PHONE","PROPERTY_ADRESS","PROPERTY_COST_1","PROPERTY_COST_2","PROPERTY_COST_3","PROPERTY_COST_4","PROPERTY_STATE",
		"PROPERTY_COURIER",'PROPERTY_CONDITIONS',"PROPERTY_COURIER.NAME","PROPERTY_CITY.NAME","PROPERTY_CITY","PROPERTY_CREATOR","PROPERTY_CREATOR.NAME","DATE_CREATE","PROPERTY_MANIFEST",
		"PROPERTY_SUMM","PROPERTY_WEIGHT","PROPERTY_STATE_SHORT","PROPERTY_RATE","PROPERTY_PLACES","PROPERTY_SIZE_1","PROPERTY_SIZE_2","PROPERTY_SIZE_3","PROPERTY_SUMM_SHOP","PROPERTY_SUMM_AGENT",
		"PROPERTY_RATE_AGENT","PROPERTY_DATE_DELIVERY","CREATED_BY","PROPERTY_COST_GOODS","PROPERTY_CASH","PROPERTY_ID_IN","PROPERTY_PVZ","PROPERTY_SUMM_ISSUE","PROPERTY_DATE_DELIVERY",
		"PROPERTY_TYPE_PAYMENT","PROPERTY_PREFERRED_TIME","PROPERTY_COMMENTS_COURIER","PROPERTY_URGENCY_ORDER","PROPERTY_WHEN_TO_DELIVER","PROPERTY_TAKE_PROVIDER","PROPERTY_TAKE_DATE",
		"PROPERTY_TAKE_COMMENT","PROPERTY_N_ZAKAZ_IN", "PROPERTY_SUMM_SHOP_ZABOR","PROPERTY_EXCEPTIONAL_SITUATION","PROPERTY_RETURN","PROPERTY_COST_RETURN","PROPERTY_DELIVERY_LEGAL", 
		"PROPERTY_CALL_COURIER", 'PROPERTY_PAY_FOR_REFUSAL', 'PROPERTY_OBTAINED', 'PROPERTY_REPORT', 'PROPERTY_REPORT.NAME', 'PROPERTY_DATE_TO_DELIVERY', 'PROPERTY_TIME_PERIOD', 
		'PROPERTY_AGENT.NAME', 'PROPERTY_TD_NUMBER', 'PROPERTY_PACK_GOODS'
	);
	$filter = array("IBLOCK_ID" => 42);
	if ((intval($agent_id) > 0) || is_array($agent_id))
	{
		if ((is_array($agent_id) && count($agent_id) > 0)  || (intval($agent_id) > 0))
		{
			$filter['PROPERTY_CREATOR'] = $agent_id;
		}
		
		else
		{
			$filter['PROPERTY_CREATOR'] = false;
		}
	}
	else
	{
		if (!$demo)
		{
			$list_shops = TheListOfShops(0, false, true, false, '', $super_agent_array['id']);
			$list_shops_ids = array();
			foreach ($list_shops as $s)
			{
				$list_shops_ids[] = $s["ID"];
			}
			$filter['PROPERTY_CREATOR'] = $list_shops_ids;
		}
	}
	if ($pack_id)
	{
		$filter['ID'] = $pack_id;
	}
	if ($status)
	{
		$filter['PROPERTY_STATE'] = $status;
	}
	if (intval($short_status) > 0)
	{
		$filter['PROPERTY_STATE_SHORT'] = $short_status;
	}
	if (intval($man_id) > 0)
	{
		$filter['PROPERTY_MANIFEST'] = intval($man_id);
	}
	if (intval($city_f) > 0)
	{
		$filter['PROPERTY_CITY'] = intval($city_f);
	}
	if (strlen($date_from))
	{
		$date_from = $date_from.' 00:00:00';
		$filter[">=DATE_CREATE"] = $date_from;
	}
	if (strlen($date_to))
	{
		$date_to = $date_to.' 23:59:59';
		$filter["<=DATE_CREATE"] = $date_to;
	}
	if (strlen($date_to_delivery_from))
	{
		$date_to_delivery_from = substr($date_to_delivery_from, 6, 4).'-'.substr($date_to_delivery_from, 3, 2).'-'.substr($date_to_delivery_from, 0, 2).' 00:00:00';
		$filter[">=PROPERTY_DATE_TO_DELIVERY"] = $date_to_delivery_from;
	}
	if (strlen($date_to_delivery_to))
	{
		$date_to_delivery_to = substr($date_to_delivery_to, 6, 4).'-'.substr($date_to_delivery_to, 3, 2).'-'.substr($date_to_delivery_to, 0, 2).' 23:59:59';
		$filter["<=PROPERTY_DATE_TO_DELIVERY"] = $date_to_delivery_to;
	}
	if (intval($CREATED_USER_ID) > 0)
	{
		$filter["CREATED_USER_ID"] = $CREATED_USER_ID;
	}
	if ($bytopdelivery)
	{
		if ($bytopdelivery == 'Y')
		{
			$filter['PROPERTY_TD'] = 1;
		}
		elseif ($bytopdelivery == 'N')
		{
			$filter['!PROPERTY_TD'] = 1;
		}
	}
	if ($use_navigation)
	{
		if (isset($_GET['on_page']))
		{
			$on_page = intval($_GET['on_page']);
		}
		else
		{
			if (isset($_SESSION['ON_PAGE_GLOBAL']))
			{
				$on_page = intval($_SESSION['ON_PAGE_GLOBAL']);
			}
			else
			{
				$on_page = 10;
			}
		}
		if ($on_page < 10)
		{
			$on_page = 10;
		}
		if ($on_page >= 200)
		{
			$on_page = 200;
		}
		$postran = array("nPageSize" => $on_page);
	}
	else
	{
		$postran = false;
	}
	if ($not_in_report)
	{
		$filter['PROPERTY_REPORT'] = false;
	}
	if ($id_report > 0)
	{
		$filter['PROPERTY_REPORT'] = $id_report;
	}
	if ($buh > 0)
	{
		$filter['PROPERTY_ACCOUNTING'] = $buh;
	}
	if ($number_order)
	{
		$filter['PROPERTY_N_ZAKAZ_IN'] = '%'.$number_order.'%';
	}
	if ($not_msk)
	{
		// $filter['!PROPERTY_CITY.SECTION_ID'] = $super_agent_array['region'];
		// $filter['!PROPERTY_CITY'] = 8054;
		$filter['!PROPERTY_CITY'] = $super_agent_array['region_cities'];
	}
	if ($only_iskl)
	{
		$filter['PROPERTY_EXCEPTIONAL_SITUATION'] = 1;
	}
	if ($only_not_iskl)
	{
		$filter['PROPERTY_EXCEPTIONAL_SITUATION'] = 0;
	}
	if ($only_returns)
	{
		$filter['PROPERTY_RETURN'] = 1;
	}


	$res_count = CIBlockElement::GetList($sort_array, $filter, array(), false, array());
	if ($use_navigation)
	{
		$arFields["COUNT"] = $res_count;
	}

	$res = CIBlockElement::GetList($sort_array, $filter, false, $postran, $select);
	if ($use_navigation)
	{
		$arFields["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject, "Заказы", "", "Y");
	}
	while($ob = $res->GetNextElement())
	{
		$arr = $ob->GetFields();
		$arr["CITY_NAME"] = GetFullNameOfCity($arr['PROPERTY_CITY_VALUE']);
		if (($super_agent_array['type'] == 52) or ($super_agent_array['type'] == 53))
		{
			$arr["PROPERTY_STATE_VALUE"] = $arr["PROPERTY_STATE_SHORT_VALUE"];
		}
		$agen_create = GetCurrentAgent($arr["CREATED_BY"]);
		$arr["CREATED_BY_NAME"] =  $agen_create['user_name'];
		$arr["CREATED_BY_COMPANY"] = $agen_create['name'];
		$arr["PVZ_NAME"] = '';
		if (intval($arr["PROPERTY_PVZ_VALUE"]) > 0)
		{
			$pvz_array = TheListOfPVZ(0,0,true,$arr["PROPERTY_PVZ_VALUE"],false);
			if (count($pvz_array) > 0)
			{
				$arr["PVZ_NAME"] =  '"'.$pvz_array[0]["NAME"].'", '.$pvz_array[0]["PROPERTY_ADRESS_VALUE"];
				if (strlen($pvz_array[0]["CODE"]))
				{
					$arr["PVZ_NAME"] .= ' ['.$pvz_array[0]["CODE"].']';
				}
			}
		}
		$deliv_array = DateFFReverse($arr['PROPERTY_WHEN_TO_DELIVER_VALUE']);
		$arr["DELIV_DATE"] = $deliv_array['date'];
		$arr["DELIV_TIME_1"] = $deliv_array['time_1'];
		$arr["DELIV_TIME_2"] = $deliv_array['time_2'];
		$take_array = DateFFReverse($arr['PROPERTY_TAKE_DATE_VALUE']);
		$arr["TAKE_DATE"] = $take_array['date'];
		$arr["TAKE_TIME_1"] = $take_array['time_1'];
		$arr["TAKE_TIME_2"] = $take_array['time_2'];
		
		$arr_zabors = array();
		$arr_zab_all = array();
		if ($pack_id)
		{
			$res_zabors = CIBlockElement::GetList(array("ID"=>"ASC"), array("IBLOCK_ID" => 77,"PROPERTY_429" => $pack_id), false, false, array(
				"ID",
				"NAME",
				"PROPERTY_428",
				"PROPERTY_429",
				"PROPERTY_430",
				"PROPERTY_431",
				"PROPERTY_432"
			));
			while ($ob_zabors = $res_zabors->GetNextElement())
			{
	
				$z_el_info = $ob_zabors->GetFields();
				$arr_zabors[] = $z_el_info;
				if (!in_array($z_el_info["PROPERTY_428_VALUE"],$arr_zab_all))
				{
					$arr_zab_all[] = $z_el_info["PROPERTY_428_VALUE"];
					$arr["REQV_INFO"][$z_el_info["PROPERTY_428_VALUE"]] = GetOneRequest($z_el_info["PROPERTY_428_VALUE"]);
				}
				$arr["ZABORS_IDS"][] = $z_el_info["ID"];
			}
		}
		$arr["ZABORS"] = $arr_zabors;
		$arr["REQS"] = $arr_zab_all;
		if (count($arr["REQS"]) == 0)
		{
			$arr["REQS"] = array(0=>0);
		}
		if (intval($arr["PROPERTY_EXCEPTIONAL_SITUATION_VALUE"]) == 1)
		{
			$arr["PROPERTY_STATE_SHORT_VALUE"] = "Исключительная ситуация! ".$arr["PROPERTY_STATE_SHORT_VALUE"];
			$arr["PROPERTY_STATE_VALUE"] = "Исключительная ситуация! ".$arr["PROPERTY_STATE_VALUE"];
		}
		if ($arr["PROPERTY_CALL_COURIER_VALUE"])
		{
			$db_props = CIBlockElement::GetProperty(76, $arr["PROPERTY_CALL_COURIER_VALUE"], array("sort" => "asc"), Array("ID"=> 436));
			if($ar_props = $db_props->Fetch())
			{
				$arr["CALL_COURIER"] = $ar_props["VALUE"];
			}
		}
		$arr['DATE_TO_DELIVERY'] = $arr['PROPERTY_DATE_TO_DELIVERY_VALUE'];
		$arr['MAN_NAME'] = false;
		$db_props = CIBlockElement::GetProperty(41, $arr['PROPERTY_MANIFEST_VALUE'], array("sort" => "asc"), Array("CODE"=>"NUMBER"));
		if ($ar_props = $db_props->Fetch())
		{
			$arr['MAN_NAME'] = $ar_props["VALUE"];
		}
		$arr['PACK_GOODS'] = '';
		if (strlen($arr['PROPERTY_PACK_GOODS_VALUE']))
		{
			$arr['PACK_GOODS'] = json_decode(htmlspecialcharsBack($arr['PROPERTY_PACK_GOODS_VALUE']), true);
			if ((is_array($arr['PACK_GOODS'])) && (count($arr['PACK_GOODS']) > 0))
			{
				foreach ($arr['PACK_GOODS'] as $k => $str)
				{
					$arr['PACK_GOODS'][$k]['GoodsName'] = iconv('utf-8','windows-1251',$str['GoodsName']);
					if (strlen(trim($arr['PACK_GOODS'][$k]['GoodsName'])) == 0)
					{
						unset($arr['PACK_GOODS'][$k]);
					}
				}
			}
		}
		$arFields[] = $arr;
	}
	return $arFields;
}

/*****************страховой тариф******************/
function WhatIsRate($agent)
{
	$rate = array('227' => 0,'308' => 0, '314' => 0);
	$db_props = CIBlockElement::GetProperty(40, $agent, array("sort" => "asc"), Array("ID"=>227));
	if($ar_props = $db_props->Fetch())
	{
		$rate[227] = $ar_props["VALUE"];
	}
	$db_props = CIBlockElement::GetProperty(40, $agent, array("sort" => "asc"), Array("ID"=>308));
	if($ar_props = $db_props->Fetch())
	{
		$rate[308] = $ar_props["VALUE"];
	}
	$db_props = CIBlockElement::GetProperty(40, $agent, array("sort" => "asc"), Array("ID"=>314));
	if($ar_props = $db_props->Fetch())
	{
		$rate[314] = $ar_props["VALUE"];
	}
	return $rate; 
}

/********************прайс-лист********************/
function WhatIsPrice($agent, $type = 1)
{
	switch ($type)
	{
		case 2:
			$param_id = 372;
			break;
		case 3:
			$param_id = 403;
			break;
		default:
			$param_id = 251;
			break;
	}
	$db_props = CIBlockElement::GetProperty(40, $agent, array("sort" => "asc"), array("ID" => $param_id));
	if($ar_props = $db_props->Fetch())
	{
		$rate = intval($ar_props["VALUE"]);
	}
	else
	{
		$rate = 0;
	}
	return $rate; 
}

/***********коэффициент габаритного веса***********/
function WhatIsGabWeight()
{
	$db_props = CIBlockElement::GetProperty(47, 2378056, array("sort" => "asc"), Array("ID"=>254));
	if($ar_props = $db_props->Fetch())
	{
		$rate = $ar_props["VALUE"];
	}
	else
	{
		$rate = false;
	}
	return $rate; 
}

/***********коэффициент габаритного веса компании***********/
function WhatIsGabWeightCompany($id_company, $def_coef = 5000)
{
	$rate = $def_coef;
	if (intval($id_company) > 0)
	{
		$db_props = CIBlockElement::GetProperty(40, $id_company, array("sort" => "asc"), Array("ID"=>681));
		if($ar_props = $db_props->Fetch())
		{
			$rate = (intval($ar_props["VALUE"]) > 0) ? intval($ar_props["VALUE"]) : $def_coef;
		}
	}
	return $rate; 
}

/***********значения параметра настроек************/
function GetSettingValue($val_id, $uk_sets_id = 2378056, $uk_id = false)
{
	$sets_id = $uk_sets_id;
	if ($uk_id)
	{
		$db_props = CIBlockElement::GetProperty(40, $uk_id, array("sort" => "asc"), array("ID" => 474));
		if ($ar_props = $db_props->Fetch())
		{
			$sets_id = $ar_props["VALUE"];
		}
	}
	$db_props = CIBlockElement::GetProperty(47, $sets_id, array("sort" => "asc"), array("ID" => $val_id));
	if ($ar_props = $db_props->Fetch())
	{
		if ($ar_props['PROPERTY_TYPE'] == 'L')
		{
			$rate = $ar_props["VALUE_ENUM"];
		}
		else
		{
			$rate = $ar_props["VALUE"];
		}
	}
	else
	{
		$rate = false;
	}
	return $rate; 
}

/**************краткая история заказа**************/
function HistoryShortOfPackage($pack_id)
{
	$arFields = array();
	$select = array("ID","NAME","DETAIL_TEXT","DATE_CREATE","MODIFIED_BY");
	$filter = array("IBLOCK_ID"=>48,"PROPERTY_PACKAGE" => $pack_id);
	$res = CIBlockElement::GetList(array("created" => "ASC"), $filter, false, false, $select);
	while($ob = $res->GetNextElement())
	{
		$arr = $ob->GetFields();
		$rsUser = CUser::GetByID($arr['MODIFIED_BY']);
		$arUser = $rsUser->Fetch();
		$arr['WHO'] = $arUser;
		$arFields[] = $arr;
	}
	return $arFields;
}

/******************история заказа******************/
function HistoryOfPackage($pack_id)
{
	$arFields = array();
	$select = array("ID","NAME","PROPERTY_COMPANY.NAME","DETAIL_TEXT","DATE_CREATE","MODIFIED_BY");
	$filter = array("IBLOCK_ID"=>45,"PROPERTY_PACKAGE" => $pack_id);
	$res = CIBlockElement::GetList(array("created" => "ASC"), $filter, false, false, $select);
	while($ob = $res->GetNextElement())
	{
		$arr = $ob->GetFields();
		$rsUser = CUser::GetByID($arr['MODIFIED_BY']);
		$arUser = $rsUser->Fetch();
		$arr['WHO'] = $arUser;
		$arFields[] = $arr;
	}
	return $arFields;
}

/************id города по его названию*************/
function GetCityId($city, $onlyone = false)
{
	$from_arr = explode(',',$city);
	$city_name = trim(str_replace(' город', '', $from_arr[0]));
	
	if (empty($city_name)){
		return 0;
	}
	
	$city_section = array();
	if (isset($from_arr[1]))
	{
		$res_0 = CIBlockSection::GetList(array("SORT"=>"ASC"),array("NAME"=>trim($from_arr[1]),"IBLOCK_ID"=>6),false);
		while($res_0_from = $res_0->GetNext())
		{
			$city_section[] = $res_0_from['ID'];
		}
	}
	$arSelect = Array("ID");
	$arFilter = Array("IBLOCK_ID"=>6, "NAME"=>$city_name);
	if(sizeof($city_section) > 0)
	{
		$arFilter["SECTION_ID"] = $city_section;
	}
	$res3 = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
	while($ob = $res3->GetNextElement())
	{
		$arFields[] = $ob->GetFields();
	}
	if ($onlyone && count($arFields) > 1)
		return 0;
	$city_id = intval($arFields[0]['ID']);
	return $city_id;
}

/************расчет стоимости доставки*************/
define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"] . "/LOG_" . date("Y_m_d") . "_PROJECT_UNIQUE.log");
function CheckCityToHave($id_city, $price_id, $w = 0, $s1 = 0,$s2 = 0, $s3 = 0, $double = 1, $price_zabor = 0, $iblock = 51, $type_delivery = array("Стандарт"), $kobw = 0, $returnOne = true)
{
	// вообще не будем привязвать к городу ??? 
	//array("IBLOCK_ID" => $iblock, "ACTIVE" => "Y", "ID" => $price_id,"PROPERTY_CITIES" => $id_city), 
	$cities = $arFields = array();
	// $w = ceil($w);
	$res = CIBlockElement::GetList(
		array("ID"=>"ASC"), 
		array("IBLOCK_ID" => $iblock, "ACTIVE" => "Y", "ID" => $price_id), 
		false, 
		array("nTopCount" => 1), 
		array("ID","PROPERTY_CITIES", "PROPERTY_FILE")
	);
	if($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();
	}
	if (count($arFields) > 0)
	{
		$resy = true;
		$price_id_bd = $arFields["ID"];
		$db_props = CIBlockElement::GetProperty($iblock, $price_id_bd, array("sort" => "asc"), Array("CODE"=>"FILE"));
		if($ar_props = $db_props->Fetch())
		{
			$file_value = IntVal($ar_props["VALUE"]);
		}
		else
		{
			$file_value = false;
		}
		$path_to_bd = CFile::GetPath($file_value);
		$global_file = $_SERVER["DOCUMENT_ROOT"].$path_to_bd;
//AddMessage2Log($global_file);
		if (is_file($global_file))
		{
			$html = read_price($global_file);
			$start_value = $html["START_VALUE"];
			$start_index = $html["START_INDEX"];
			$arResss = array();
			$arResss["LOG"] = true;
			///фикс для калькулятора по стране
			/*foreach($html["CITIES"] as $k1=>$v1){
				//echo "<pre>";print_r($k1);echo "</pre>";
				if($k1 != $id_city){
					unset($html["CITIES"][$k1]);
				    unset($html["ORDERS"][$k1]);
				}
			}*/
			///
			foreach ($type_delivery as $kkk => $t_del)
			{
				$ord = $html["ORDERS"][$id_city][$t_del];
				if ($ord)
				{
					$weight = floatval(str_replace(',','.',$w));
					$weight = ($weight < 1) ? $weight : ceil($weight);
					$size_1 = floatval(str_replace(',','.',$s1));
					$size_2 = floatval(str_replace(',','.',$s2));
					$size_3 = floatval(str_replace(',','.',$s3));
					if (intval($kobw) > 0)
					{
						$gab_w = intval($kobw);
					}
					else
					{
						$gab_w = WhatIsGabWeight();
					}
					//$ob_weight = ceil(($size_1*$size_2*$size_3)/$gab_w);
					//$ob_weight = round((($size_1*$size_2*$size_3)/$gab_w), 2);
					$ob_weight_start = ($size_1*$size_2*$size_3)/$gab_w;
					$ob_weight = ($ob_weight_start < 1) ? $ob_weight_start : ceil($ob_weight_start);
					$ob_weight_log = false;
					if ($ob_weight > $weight)
					{
						$weight = $ob_weight;
						$out .= 'Расчет ведется по объемному весу ('.$weight.' кг)<br>';
						$ob_weight_log = true;
					}
					$out .= '<span class="green">Город доступен для доставки.</span>';
					if ($ord[$html["MIN"]] == $ord[$html["MAX"]])
					{
						$days = $ord[$html["MIN"]];
					}
					else
					{
						$days = $ord[$html["MIN"]].'-'.$ord[$html["MAX"]];
					}
					$out .= '<br>Срок доставки, дней: '.$days.'<br>';
					$doc_true = true;
					arsort($html["DOCS"]);
					foreach ($html["DOCS"] as $k => $v)
					{
						if ($weight <= $v)
						{
							$index_dd = $k;
						}
					}
					if ($index_dd != '')
					{
						$summ = $ord[$index_dd];
					}
					else
					{
						$doc_true = false;
					}
					if (!$doc_true)
					{
						if ($weight <= $start_value)
						{
							$summ = $ord[$start_index];
						}
						else
						{
							foreach ($html["WEIGHT"] as $kk => $vv)
							{
								if ($weight >= $vv) $index_ww = $kk;
							}
							$summ = $ord[$start_index] + ($weight - $start_value)*$ord[$index_ww];
						}
					}
					$summ = $summ*$double;
					$out .= 'Стоимость доставки: <span id="cost">'.$summ.'</span> руб.';
					if ($double == 2)
					{
						$out .= ' (двойной тариф за срочность заказа)';
					}
					$cost_zabor = false;
					if (intval($price_zabor) > 0)
					{
						$cost_zabor = GetCostOfZabor($price_zabor,$w, $s1, $s2, $s3);
						if ($cost_zabor)
						{
							$out .= '<br>Стоимость забора у поставщика: <span id="cost">'.$cost_zabor.'</span> руб.';
							$summ = $summ + $cost_zabor;
							$out .= '<br>Общая стоимость доставки: <span id="cost">'.$summ.'</span> руб.';
						}
						else
						{
							$cost_zabor = $price_zabor.' '.$w.' '.$s1.' '.$s2.' '.$s3;
						}
					}
					
					$arResss['delivery'][$kkk]["TYPE_DELIVERY"] = iconv("windows-1251","utf-8",$t_del);
					$arResss['delivery'][$kkk]["LOG"] = true;
					$arResss['delivery'][$kkk]["TEXT"] = iconv("windows-1251","utf-8",$out);
					//$arResss['delivery'][$kkk]["TEXT"] = $out 
					$arResss['delivery'][$kkk]["COST"] = $summ;
					$arResss['delivery'][$kkk]["DAYS"] = $days;
					$arResss['delivery'][$kkk]['weight'] = $weight;
					$arResss['delivery'][$kkk]['ob_weight'] = $ob_weight_log;
	
					$return["COST_ZABOR"] =  ($cost_zabor) ?  $cost_zabor : 0;
					$return["LOG"] = true;
					$return["TEXT"] = $out;
					$return["COST"] = $summ;
					$return["DAYS"] = $days;
					$p1 = $html["PERSENT_1"];
					$p2 = $html["PERSENT_2"];
					$return["PERSENT_1"] = $html["ORDERS"][$id_city]["Проценты"][$p1];
					$return["PERSENT_2"] = $html["ORDERS"][$id_city]["Проценты"][$p2];
				}
				else
				{
					$arResss['delivery'][$kkk]["LOG"] = false;
				}
			}
		} 
	}
	else 
	{
		$return["LOG"] = false;
		$return["TEXT"] = 'Указанный город отсутствует в системе, заказ будет сохранен как черновик';
		$arResss["LOG"] = false;
		$arResss["TEXT"] = 'Указанный город отсутствует в системе, заказ будет сохранен как черновик';
	}
	if ((count($type_delivery) == 1) && $returnOne)
	{
		return $return;
	}
	else
	{
		return $arResss;
	}
}

/*************расчет стоимости забора**************/
function GetCostOfZabor($price_id, $w = 0, $s1 = 0, $s2 = 0, $s3 = 0, $id_city = 8054)
{
	$cities = $arFields = array();
	$w = ceil($w);
	$res = CIBlockElement::GetList(
		array("ID"=>"ASC"), 
		array("IBLOCK_ID" => 51,"ACTIVE" => "Y","ID" => $price_id, "PROPERTY_CITIES" => $id_city), 
		false, 
		array("nTopCount"=>1), 
		array("ID","PROPERTY_CITIES","PROPERTY_FILE")
	);
	if($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();
	}
	if (count($arFields) > 0)
	{
		$resy = true;
		$price_id_bd = $arFields["ID"];
		$db_props = CIBlockElement::GetProperty(51, $price_id_bd, array("sort" => "asc"), Array("CODE"=>"FILE"));
		if ($ar_props = $db_props->Fetch())
		{
			$file_value = IntVal($ar_props["VALUE"]);
		}
		else
		{
			$file_value = false;
		}
		$path_to_bd = CFile::GetPath($file_value);
		$global_file = $_SERVER["DOCUMENT_ROOT"].$path_to_bd;
		if (is_file($global_file))
		{
			// echo $global_file;
			$html = read_price($global_file);
			/*
			echo '<pre>';
			print_r($html);
			echo '</pre>';
			*/
			$start_value = $html["START_VALUE"];
			$start_index = $html["START_INDEX"];
			$ord = $html["ORDERS"][$id_city]["Стандарт"];
			$weight = ceil(str_replace(',','.',$w));
			$size_1 = floatval(str_replace(',','.',$s1));
			$size_2 = floatval(str_replace(',','.',$s2));
			$size_3 = floatval(str_replace(',','.',$s3));
			$gab_w = WhatIsGabWeight();
			$ob_weight = ceil(($size_1*$size_2*$size_3)/$gab_w);
			if ($ob_weight > $weight)
			{
				$weight = $ob_weight;
			}
			$doc_true = true;
			arsort($html["DOCS"]);
			foreach ($html["DOCS"] as $k => $v)
			{
				if ($weight <= $v)
				{
					$index_dd = $k;
				}
			}
			if ($index_dd != '')
			{
				$summ = $ord[$index_dd];
			}
			else
			{
				$doc_true = false;
			}
			if (!$doc_true)
			{
				if ($weight <= $start_value)
				{
					$summ = $ord[$start_index];
				}
				else
				{
					foreach ($html["WEIGHT"] as $kk => $vv)
					{
						if ($weight >= $vv)
						{
							$index_ww = $kk;
						}
					}
					$summ = $ord[$start_index] + ($weight - $start_value)*$ord[$index_ww];
				}
			}
			return $summ;
		} 
		else
		{
			return false;
		}
	}
	else
	{
		return false;
	}
}

/****************добавление истории****************/
function AddToHistory($idv, $agent_idv, $u_idv, $status_idv, $commentv = '', $date = false)
{
	if (!$date)
	{
		$date = date('d.m.Y H:i:s');
	}
	$el = new CIBlockElement;
	$PROP = array();
	$PROP[214] = $idv;
	$PROP[215] = $agent_idv;
	$a = CIBlockPropertyEnum::GetByID($status_idv);
	$arLoadProductArray = array(
		"MODIFIED_BY"    => $u_idv, 
		"IBLOCK_SECTION_ID" => false,
		"IBLOCK_ID"      => 45,
		"PROPERTY_VALUES"=> $PROP,
		"NAME" => $a['VALUE'],
		"ACTIVE" => "Y",
		"DATE_CREATE" => $date,
		"DETAIL_TEXT"=> $commentv);
	$PRODUCT_ID = $el->Add($arLoadProductArray);
	return $PRODUCT_ID;
}

/************добавление краткой истории************/
function AddToShortHistory($idv, $u_idv, $status_idv, $commentv = '', $date = false)
{
	if (!$date)
	{
		$date = date('d.m.Y H:i:s');
	}
	$el = new CIBlockElement;
	$PROP = array();
	$PROP[230] = $idv;
	$a = CIBlockPropertyEnum::GetByID($status_idv);
	$arLoadProductArray = Array(
		"MODIFIED_BY" => $u_idv, 
		"IBLOCK_SECTION_ID" => false,
		"IBLOCK_ID" => 48,
		"PROPERTY_VALUES" => $PROP,
		"NAME" => $a['VALUE'],
		"ACTIVE" => "Y",
		"DATE_CREATE" => $date,
		"DETAIL_TEXT"=> $commentv);
	$PRODUCT_ID = $el->Add($arLoadProductArray);
	return $PRODUCT_ID;
}

/******************список агентов******************/
function AvailableAgents($full = true, $uk = false, $addNP = false)
{
	$arr = array();
	if ($addNP)
	{
		$arr[2197189] = 'Новый Партнер, Москва';
	}
	$filter = array("IBLOCK_ID" => 40,"PROPERTY_TYPE" => 53,"ACTIVE" => "Y");
	if ($uk)
	{
		$filter['PROPERTY_UK'] = $uk;
	}
	$res2 = CIBlockElement::GetList(array("NAME" => "ASC"), $filter, false, false, array("ID", "NAME", "PROPERTY_CITY", "PROPERTY_CITY.NAME"));
	while($ob2 = $res2->GetNextElement())
	{
		$arr2 = $ob2->GetFields();
		if ($full)
		{
			$arr[$arr2["ID"]] = $arr2["NAME"].', '.GetFullNameOfCity($arr2["PROPERTY_CITY_VALUE"]);
		}
		else
		{
			$arr[$arr2["ID"]] = $arr2["NAME"].', '.$arr2["PROPERTY_CITY_NAME"];
		}		
	}
	//return $arr;
    $arClientsWithOut = array();
    foreach ($arr as $k => $test)
    {
        $test2 = strstr($test,"«",false);
        if ($test2)
        {
            $test2 = substr($test2,1);
        }
        else
        {
            $test2 = $test;
        }
        $arClientsWithOut[$k] = $test2;
    }
    asort($arClientsWithOut);
    $arResSort = array();
    foreach ($arClientsWithOut as $k => $v)
    {
        $arResSort[$k] = $arr[$k];
    }
	return $arResSort;
}

function AvailableClients($only_for_agents = true, $inn_index = false, $uk = 0)
{
	$arr = array();
	$filter = array("IBLOCK_ID" => 40,"PROPERTY_TYPE" => 242,"ACTIVE" => "Y", "PROPERTY_AVAILABLE_FOR_AGENT" => 1);
	if (!$only_for_agents)
	{
		unset($filter["PROPERTY_AVAILABLE_FOR_AGENT"]);
	}
    if (intval($uk) > 0)
    {
        $filter["PROPERTY_UK"] = intval($uk);
    }
	$res2 = CIBlockElement::GetList(array("NAME" => "ASC"), $filter, false, false, array("ID", "NAME", "PROPERTY_CITY.NAME", "PROPERTY_INN"));
	while($ob2 = $res2->GetNextElement())
	{
		$arr2 = $ob2->GetFields();
		if ($inn_index)
		{
			if (strlen($arr2["PROPERTY_INN_VALUE"]))
			{
				$arr[$arr2["PROPERTY_INN_VALUE"]] = $arr2["NAME"].', '.$arr2["PROPERTY_CITY_NAME"];	
			}
		}
		else
		{
			$arr[$arr2["ID"]] = $arr2["NAME"].', '.$arr2["PROPERTY_CITY_NAME"];	
		}
	}
    $arClientsWithOut = array();
    foreach ($arr as $k => $test)
    {
        $test2 = strstr($test,"«",false);
        if ($test2)
        {
            $test2 = substr($test2,1);
        }
        else
        {
            $test2 = $test;
        }
        $arClientsWithOut[$k] = $test2;
    }
    asort($arClientsWithOut);
    $arResSort = array();
    foreach ($arClientsWithOut as $k => $v)
    {
        $arResSort[$k] = $arr[$k];
    }
	return $arResSort;
}

/***************информация об агенте***************/
function GetAgentInfo($ag_id)
{
	$res = CIBlockElement::GetList(
		array("ID" => "asc"),
		array("IBLOCK_ID" => 40, "ID" => $ag_id), 
		false, 
		false, 
		array("ID","NAME","PROPERTY_CITY","PROPERTY_TYPE","PROPERTY_PERCENT","PROPERTY_ID_IN","PROPERTY_FOLDER")
	);
	if ($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$a['PROPERTY_CITY'] = GetFullNameOfCity($a['PROPERTY_CITY_VALUE']);
		$arFields = $a;
	}
	return $arFields;
}

/************проверка статуса манифеста************/
function CheckManifests($agentid, $agent_to = 0)
{
	$arFields = array();
	$filter = array("IBLOCK_ID" => 41,"PROPERTY_AGENT" => $agentid,"PROPERTY_STATE" => 35);
	if (intval($agent_to) > 0)
	{
		$filter["PROPERTY_AGENT_TO"] = intval($agent_to);
	}
	$res = CIBlockElement::GetList(array("ID"=>"asc"), $filter, false, false, array("ID","NAME"));
	while($ob = $res->GetNextElement())
	{
		$arFields[] = $ob->GetFields();
	}
	if (count($arFields) > 1)
	{
		$result = -1;
	}
	elseif (count($arFields) == 0)
	{
		$result = 0;
	}
	else
	{
		$result = $arFields[0]['ID'];
	}
	return $result;
}

/**************информация о манифесте**************/
function GetInfioOfManifest($manifest)
{
	$arFields = array();
	$res = CIBlockElement::GetList(array("ID"=>"DESC"), array("IBLOCK_ID" => 41, "ID" => $manifest), false, false, array(
		"ID",
		"NAME",
		"DATE_CREATE",
		"PROPERTY_ID_IN",
		"PROPERTY_NUMBER",
		"PROPERTY_AGENT_TO",
		"PROPERTY_AGENT_TO.NAME",
		"PROPERTY_CITY",
		'PROPERTY_CITY.NAME',
		"PROPERTY_CITY.IBLOCK_SECTION_ID",
		"PROPERTY_CARRIER",
		"PROPERTY_CARRIER.NAME",
		"PROPERTY_DATE_SEND",
		"PROPERTY_DATE_SETTLEMENT",
		"PROPERTY_NUMBER_SEND",
		"PROPERTY_PLACES",
		"PROPERTY_AGENT",
		'PROPERTY_AGENT.NAME',
		"PROPERTY_USER",
		'PROPERTY_STATE',
		"PROPERTY_DATE_RECEIVE",
		"PROPERTY_AGENT.NAME"
		)
	);
	$ob = $res->GetNextElement();
	$arFields = $ob->GetFields();
	$city = GetAgentInfo($arFields["PROPERTY_AGENT_TO_VALUE"]);
	$rsUser = CUser::GetByID($arFields["PROPERTY_USER_VALUE"]);
	$arUser = $rsUser->Fetch();
	$arFields['USER_NAME'] = $arUser["LAST_NAME"].' '.$arUser["NAME"];	
	$arFields['PROPERTY_CITY'] = $city["PROPERTY_CITY"];
	$arFields['DATE_CREATE_TXT'] = DateFF($arFields["DATE_CREATE"]);
	return $arFields;	
}

/*************список манифестов агенту*************/
function GetManifests($agentid, $state = false, $use_navigation = true)
{
	$arFields = array();
	$filter = array("IBLOCK_ID" => 41, "PROPERTY_AGENT" => $agentid);
	if ($state)
	{
		$filter["PROPERTY_STATE"] = $state;
	}
	if ($use_navigation)
	{
		if (isset($_GET['on_page']))
		{
			$on_page = intval($_GET['on_page']);
		}
		else
		{
			if (isset($_SESSION['ON_PAGE_GLOBAL']))
			{
				$on_page = intval($_SESSION['ON_PAGE_GLOBAL']);
			}
			else
			{
				$on_page = 10;
			}
		}
		if ($on_page < 10)
		{
			$on_page = 10;
		}
		if ($on_page >= 200)
		{
			$on_page = 200;
		}
		$postran = array("nPageSize" => $on_page);
	}
	else
	{
		$postran = false;
	}
	$res = CIBlockElement::GetList(
		array("PROPERTY_DATE_SEND" => "DESC"), 
		$filter, 
		false, 
		$postran, 
		array(
			'ID',
			'NAME',
			'PROPERTY_AGENT_TO.NAME',
			'PROPERTY_STATE',
			'DATE_CREATE',
			'PROPERTY_AGENT_TO','PROPERTY_CARRIER','PROPERTY_CARRIER.NAME','PROPERTY_DATE_SEND','PROPERTY_NUMBER_SEND',
			'PROPERTY_NUMBER',
			'PROPERTY_ID_IN',
			'PROPERTY_CITY'
		)
	);
	if ($use_navigation)
	{
		$arFields["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject,"Манифесты","","Y");
	}
	while($ob = $res->GetNextElement())
	{
		$r = $ob->GetFields();
		$r['CITY'] = GetAgentInfo($r['PROPERTY_AGENT_TO_VALUE']);
		$pacs = GetListPackeges($r['ID']);
		$r["COUNT"] = count($pacs)-1;
		$w = 0;
		foreach ($pacs as $p)
		{
			$w = $w + $p['PROPERTY_WEIGHT_VALUE'];
		}
		$r["WEIGHT"] = $w;
		$r['CITY_NAME'] = GetFullNameOfCity($r['PROPERTY_CITY_VALUE']);
		$arFields[] = $r;
	}
	return $arFields;
}

/************список заказов в манифесте************/
function GetListPackeges(
	$manifest = 0,
	$sort = array("ID" => "DESC"), 
	$status = 0, 
	$date_from = false, 
	$date_to = false, 
	$type_delivery = 0, 
	$order = '', 
	$use_navigation = true, 
	$not_in_report = false, 
	$only_for_uk = false,
	$iskl = '',
	$bytopdelivery = false
)
{
	$arFields = array();
	$select = array(
		"ID",
		"NAME",
		"PROPERTY_N_ZAKAZ",
		"PROPERTY_RECIPIENT",
		"PROPERTY_PHONE",
		"PROPERTY_ADRESS",
		"PROPERTY_COST_1",
		"PROPERTY_COST_2",
		"PROPERTY_COST_3",
		"PROPERTY_COST_4",
		"PROPERTY_STATE",
		"PROPERTY_COURIER",
		'PROPERTY_CONDITIONS',
		"PROPERTY_COURIER.NAME",
		"PROPERTY_WEIGHT",
		"PROPERTY_PLACES",
		"PROPERTY_SIZE_1",
		"PROPERTY_SIZE_2",
		"PROPERTY_SIZE_3",
		"PROPERTY_STATE_SHORT",
		"DATE_CREATE",
		"PROPERTY_SUMM_AGENT",
		"PROPERTY_RATE_AGENT",
		"PROPERTY_ID_IN",
		"PROPERTY_PVZ",
		"PROPERTY_PREFERRED_TIME",
		"PROPERTY_COMMENTS_COURIER",
		"PROPERTY_CREATOR",
		"PROPERTY_CREATOR.NAME",
		"PROPERTY_PVZ.NAME",
		'PROPERTY_CITY',
		"PROPERTY_CITY.NAME",
		"PROPERTY_MANIFEST",
		"PROPERTY_WHEN_TO_DELIVER",
		"PROPERTY_N_ZAKAZ_IN",
		"PROPERTY_COMMENT",
		"PROPERTY_EXCEPTIONAL_SITUATION",
		"PROPERTY_RATE",
		"PROPERTY_SUMM_SHOP",
		"PROPERTY_COST_RETURN",
		"PROPERTY_RETURN",
		'PROPERTY_PAY_FOR_REFUSAL',
		'PROPERTY_DATE_TO_DELIVERY'
	);
	$filter = array("IBLOCK_ID"=>42);
	if ($manifest > 0)
	{
		$filter["PROPERTY_195"] = $manifest;
	}
	if (($status > 0) || (is_array($status)))
	{
		$filter["PROPERTY_STATE"] = $status;
	}
	if ($date_from)
	{
		$filter[">=DATE_CREATE"] = $date_from;
	}
	if ($date_to)
	{
		$filter["<=DATE_CREATE"] = $date_to;
	}
	if ($type_delivery > 0)
	{
		$filter["PROPERTY_CONDITIONS"] = $type_delivery;
	}
	if (strlen($order))
	{
		$filter["?PROPERTY_N_ZAKAZ_IN"] = "%".$order."%" ;
	}
	if ($not_in_report)
	{
		$filter["PROPERTY_REPORT"] = false;
	}
	if ($only_for_uk)
	{
		$arShops = TheListOfShops(0, false, true, false, '', $only_for_uk);
		$arShopsIDs = array();
		foreach ($arShops as $s)
		{
			$arShopsIDs[] = $s["ID"];
		}
		if (count($arShopsIDs) > 0)
		{
			$filter["PROPERTY_CREATOR"] = $arShopsIDs;
		}
		else
		{
			$filter["PROPERTY_CREATOR"] = false;
		}
	}
	if (in_array($iskl, array('0','1')))
	{
		$filter['PROPERTY_EXCEPTIONAL_SITUATION'] = intval($iskl);
	}
	if ($bytopdelivery)
	{
		if ($bytopdelivery == 'Y')
		{
			$filter['PROPERTY_TD'] = 1;
		}
		elseif ($bytopdelivery == 'N')
		{
			$filter['!PROPERTY_TD'] = 1;
		}
	}
	if ($use_navigation)
	{
		if (isset($_GET['on_page']))
		{
			$on_page = intval($_GET['on_page']);
		}
		else
		{
			if (isset($_SESSION['ON_PAGE_GLOBAL']))
			{
				$on_page = intval($_SESSION['ON_PAGE_GLOBAL']);
			}
			else
			{
				$on_page = 10;
			}
		}
		if ($on_page < 10)
		{
			$on_page = 10;
		}
		if ($on_page >= 200)
		{
			$on_page = 200;
		}
		$postran = array("nPageSize" => $on_page);
	}
	else
	{
		$postran = false;
	}
	$res = CIBlockElement::GetList($sort, $filter, false, $postran, $select);
	if ($use_navigation)
	{
		$arFields["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject,"Заказы","","Y");
	}
	while($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		if (intval($a['PROPERTY_PVZ_VALUE']) > 0)
		{
			$pvz_list = TheListOfPVZ(0,0,false,$a["PROPERTY_PVZ_VALUE"],false);
			if (count($pvz_list) == 1)
			{
				$a["PVZ"] = 'ПВЗ &quot;'.$pvz_list[0]["NAME"].'&quot;, '.$pvz_list[0]["PROPERTY_ADRESS_VALUE"];
				if (strlen($pvz_list[0]["CODE"]))
				{
					$a["PVZ"] .= ' ['.$pvz_list[0]["CODE"].']';
				}
			}
			else
			{
				$a["PVZ"] = false;
			}
		}
		else
		{
			$a["PVZ"] = false;
		}
		if ($a["PROPERTY_MANIFEST_VALUE"])
		{
			$a["MAN_INFO"] = GetInfioOfManifest($a["PROPERTY_MANIFEST_VALUE"]);
		}
		else
		{
			$a["MAN_INFO"] = false;
		}
		if (intval($a["PROPERTY_EXCEPTIONAL_SITUATION_VALUE"]) == 1)
		{
			$a["PROPERTY_STATE_SHORT_VALUE"] = "Исключительная ситуация! ".$a["PROPERTY_STATE_SHORT_VALUE"];
			$a["PROPERTY_STATE_VALUE"] = "Исключительная ситуация! ".$a["PROPERTY_STATE_VALUE"];
		}
		$arFields[] = $a;
	}
	return $arFields;
}

/***************список перевозчиков****************/
function TheListOfCarriers($cur = 0, $act = true, $use_navigation = false, $agent = false)
{
	$arFields = array();
	$filter = array("IBLOCK_ID"=>49);
	if ($cur > 0)
	{
		$filter['ID'] = $cur;
	}
	if ($act)
	{
		$filter['ACTIVE'] = "Y";
	}
	if ($agent)
	{
		$filter['PROPERTY_AGENT'] = $agent;
	}
	if ($use_navigation)
	{
		if (isset($_GET['on_page']))
		{
			$on_page = intval($_GET['on_page']);
		}
		else
		{
			if (isset($_SESSION['ON_PAGE_GLOBAL']))
			{
				$on_page = intval($_SESSION['ON_PAGE_GLOBAL']);
			}
			else
			{
				$on_page = 10;
			}
		}
		if ($on_page < 10)
		{
			$on_page = 10;
		}
		if ($on_page >= 200)
		{
			$on_page = 200;
		}
		$postran = array("nPageSize" => $on_page);
	}
	else
	{
		$postran = false;
	}
	$res = CIBlockElement::GetList(array("NAME"=>"ASC"), $filter, false, $postran, array("ID","NAME","ACTIVE","PROPERTY_ID_IN"));
	if ($use_navigation)
	{
		$arFields["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject,"Перевозчики","","Y");
	}
	while($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$arFields[$a['ID']] = $a;
	}
	return $arFields;
}

/*****************манифесты агенту*****************/
function ManifestsToAgent($agentid, $state = 0, $date_from = false, $date_to = false, $nav = true, $sort = array("ID" => "DESC"))
{
	$arFields = array();
	$filer = array("IBLOCK_ID" => 41, "PROPERTY_AGENT_TO" => $agentid);
	if (intval($state) > 0)
	{
		$filer['PROPERTY_STATE'] = intval($state);
	}
	if ($date_from)
	{
		$filer[">=DATE_CREATE"] = $date_from.' 00:00:01';
	}
	if ($date_to)
	{
		$filer["<=DATE_CREATE"] = $date_to.' 23:59:59';
	}
	if ($nav)
	{
		$on_page = intval($_GET['on_page']);
		if ($on_page < 10) $on_page = 10;
		elseif ($on_page >= 500) $on_page = 500;
		else {}
		$nav_array = array("nPageSize"=>$on_page);
	}
	else
	{
		$nav_array = false;
	}
	$res = CIBlockElement::GetList(
		$sort, 
		$filer, 
		false, 
		$nav_array, 
		array(
			"ID","NAME",'PROPERTY_AGENT.NAME','PROPERTY_STATE',"DATE_CREATE","PROPERTY_CARRIER","PROPERTY_CARRIER.NAME","PROPERTY_DATE_SEND","PROPERTY_NUMBER_SEND","PROPERTY_DATE_RECEIVE",	
			"PROPERTY_NUMBER"
		)
	);
	if ($nav)
	{
		$arFields["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject,"Манифесты","","Y");
	}
	while($ob = $res->GetNextElement())
	{
		$r = $ob->GetFields();
		$pacs = GetListPackeges($r['ID']);
		$r["COUNT"] = count($pacs)-1;
		$w = 0;
		foreach ($pacs as $p)
		{
			$w = $w + $p['PROPERTY_WEIGHT_VALUE'];
		}
		$r["WEIGHT"] = $w;
		$arFields[] = $r;
	}
	return $arFields;
}

/*************список заказов у агента**************/
function GetListPacksCurrentAgent($agent_id, $status = false, $date_from = false, $date_to = false, $type_delivery = 0, $order = '')
{
	$list_mans = ManifestsToAgent($agent_id);
	$packs = $mans_array = array();
	foreach ($list_mans as $man)
	{
		$mans_array[] = $man['ID'];
	}
	$packs = GetListPackeges($mans_array,array("ID"=>"DESC"), $status, $date_from, $date_to, $type_delivery, $order);
	return $packs;
}

/**************список курьеров агента**************/
function TheListOfCouriers($agentid, $cur = 0, $act = '')
{
	$arFields = array();
	$filter = array("IBLOCK_ID" => 43, "PROPERTY_205" => $agentid);
	if (strlen($act))
	{
		$filter["ACTIVE"] = $act;
	}
	if ($cur > 0)
	{
		$filter['ID'] = $cur;
	}
	$res = CIBlockElement::GetList(array("NAME"=>"ASC"), $filter, false, false, array("ID","NAME","ACTIVE","PROPERTY_ID_IN"));
	while($ob = $res->GetNextElement())
	{
		$arFields[] = $ob->GetFields();
	}
	return $arFields;
}

/*****************состояние счета******************/
function GetAccount($a_id)
{
	$db_props = CIBlockElement::GetProperty(40, $a_id, array("sort" => "asc"), Array("CODE" => "ACCOUNT"));
	if($ar_props = $db_props->Fetch())
	{
		$FORUM_TOPIC_ID = floatval($ar_props["VALUE"]);
	}
	else
	{
		$FORUM_TOPIC_ID = 0;
	}
	return $FORUM_TOPIC_ID;
}

/**************************************************/
function debtAgent($a_id)
{
	$summ = 0;
	$res = CIBlockElement::GetList(
		array("ID"=>"asc"), 
		array("IBLOCK_ID" => 42,"PROPERTY_218" => 85), 
		false, 
		false, 
		array("ID","PROPERTY_CREATOR","PROPERTY_N_ZAKAZ","PROPERTY_COST_2","DATE_CREATE","PROPERTY_STATE","PROPERTY_CREATOR.NAME","PROPERTY_MANIFEST")
	);
	while ($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$info = GetInfioOfManifest($a["PROPERTY_MANIFEST_VALUE"]);
		if ($info["PROPERTY_AGENT_TO_VALUE"] == $a_id)
		{
			$summ = $summ + $a["PROPERTY_COST_2_VALUE"];
		}
	}
	return $summ;
}

/***********список заказов по магазинам************/
function ShopPackeges($buh = array(61,62,85,88), $all = false, $shop_s = 0)
{
	$on_page = intval($_GET['on_page']);
	if ($on_page < 10) $on_page = 10;
	elseif ($on_page >= 500) $on_page = 500;
	else {};
	$filter = array("IBLOCK_ID"=>40,"PROPERTY_TYPE"=>52,"ACTIVE"=>"Y");
	if (!$all)
	{
		$filter["!PROPERTY_DEMO"] = 89;
	}
	if ($shop_s > 0)
	{
		$filter["ID"] = $shop_s;
	}
	$res0 = CIBlockElement::GetList(array("NAME"=>"asc"), $filter, false, array("nPageSize" => $on_page), array("ID","NAME"));
	$arFields["NAV_STRING"] = $res0->GetPageNavStringEx($navComponentObject,"Интернет-магазины","","Y");
	while ($ob0 = $res0->GetNextElement())
	{
		$a0 = $ob0->GetFields();
		$shop_id = $a0["ID"];
		$arFields[$shop_id]["NAME"] = $a0["NAME"];
		$res = CIBlockElement::GetList(
			array("ID"=>"DESC"), 
			array("IBLOCK_ID" => 42,"PROPERTY_218" => $buh,"PROPERTY_CREATOR" => $shop_id), 
			false, 
			false, 
			array("ID","PROPERTY_N_ZAKAZ","PROPERTY_COST_2","DATE_CREATE","PROPERTY_STATE","PROPERTY_MANIFEST","PROPERTY_ACCOUNTING","PROPERTY_SUMM_SHOP","PROPERTY_RATE")
		);
		while ($ob = $res->GetNextElement())
		{
			$a = $ob->GetFields();
			$info = GetInfioOfManifest($a["PROPERTY_MANIFEST_VALUE"]);
			$agent_to = $info["PROPERTY_AGENT_TO_VALUE"];
			$arFields[$shop_id][$a["PROPERTY_ACCOUNTING_ENUM_ID"]]["SUMM"] = $arFields[$shop_id][$a["PROPERTY_ACCOUNTING_ENUM_ID"]]["SUMM"] + $a["PROPERTY_COST_2_VALUE"];
			$arFields[$shop_id][$a["PROPERTY_ACCOUNTING_ENUM_ID"]]["SUMM_TO_SHOP"] = $arFields[$shop_id][$a["PROPERTY_ACCOUNTING_ENUM_ID"]]["SUMM_TO_SHOP"] + $a["PROPERTY_COST_2_VALUE"] - 
				$a["PROPERTY_SUMM_SHOP_VALUE"] - $a["PROPERTY_RATE_VALUE"];
			$arFields[$shop_id][$a["PROPERTY_ACCOUNTING_ENUM_ID"]]["PACKS"][] = $a["ID"];
			$arFields["AGENTS"][$a["PROPERTY_ACCOUNTING_ENUM_ID"]][$shop_id][$agent_to] = $arFields["AGENTS"][$a["PROPERTY_ACCOUNTING_ENUM_ID"]][$shop_id][$agent_to] + $a["PROPERTY_COST_2_VALUE"];
		}
	}
	return $arFields;
}

/**************************************************/
function GetPacksInAccount($shop_id, $buh, $nav=true)
{
	if ($nav)
	{
		$on_page = intval($_GET['on_page']);
		if ($on_page < 10) $on_page = 10;
		elseif ($on_page >= 500) $on_page = 500;
		else {};
		$nav_array =  array("nPageSize"=>$on_page);
	}
	else
	{
		$nav_array = false;
	}
	$res = CIBlockElement::GetList(
		array("ID" => "DESC"), 
		array("IBLOCK_ID" => 42,"PROPERTY_218" => $buh,"PROPERTY_CREATOR" => $shop_id), 
		false, 
		$nav_array, 
		array("ID","PROPERTY_N_ZAKAZ","PROPERTY_COST_2","DATE_CREATE","PROPERTY_STATE","PROPERTY_MANIFEST","PROPERTY_RECIPIENT","PROPERTY_CREATOR.NAME","PROPERTY_CITY","PROPERTY_SUMM_SHOP",
			"PROPERTY_RATE","PROPERTY_DATE_DELIVERY","PROPERTY_CONDITIONS","PROPERTY_WEIGHT","PROPERTY_COST_3","PROPERTY_COST_GOODS"
		)
	);
	if ($nav)
	{
		$arFields["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject,"Заказы","","Y");
	}
	while ($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$info = GetInfioOfManifest($a["PROPERTY_MANIFEST_VALUE"]);
		$a["PROPERTY_AGENT_TO"] = $info["PROPERTY_AGENT_TO_VALUE"];
		$a["PROPERTY_AGENT_NAME"] = $info["PROPERTY_AGENT_TO_NAME"];
		$a["CITY_NAME"] = GetFullNameOfCity($a["PROPERTY_CITY_VALUE"]);
		$arFields[] = $a;
	}
	return $arFields;
}

/************список заказов по агентам*************/
function AgentsPackeges($buh = array(61,62,63,85,87,88))
{
	$on_page = intval($_GET['on_page']);
	if ($on_page < 10)
	{
		$on_page = 10;
	}
	elseif ($on_page >= 500)
	{
		$on_page = 500;
	}
	else {};
	$res0 = CIBlockElement::GetList(array("NAME"=>"asc"), $filter = array("IBLOCK_ID" => 40,"PROPERTY_TYPE" => 53,"ACTIVE" => "Y"), false, array("nPageSize" => $on_page), array("ID","NAME"));
	$arFields["NAV_STRING"] = $res0->GetPageNavStringEx($navComponentObject,"Агенты","","Y");
	while ($ob0 = $res0->GetNextElement())
	{
		$a0 = $ob0->GetFields();
		$shop_id = $a0["ID"];
		$arFields[$shop_id]["NAME"] = $a0["NAME"];
	}
	$res = CIBlockElement::GetList(
		array("ID"=>"DESC"), 
		array("IBLOCK_ID" => 42,"PROPERTY_218" => $buh), 
		false, 
		false, 
		array("ID","PROPERTY_N_ZAKAZ","PROPERTY_COST_2","DATE_CREATE","PROPERTY_STATE","PROPERTY_MANIFEST","PROPERTY_ACCOUNTING","PROPERTY_ACCOUNTING_AGENT","PROPERTY_SUMM_AGENT")
	);
	while ($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$info = GetInfioOfManifest($a["PROPERTY_MANIFEST_VALUE"]);
		$agent_to = $info["PROPERTY_AGENT_TO_VALUE"];
		$arFields[$agent_to][$a["PROPERTY_ACCOUNTING_ENUM_ID"]]["SUMM"] = $arFields[$agent_to][$a["PROPERTY_ACCOUNTING_ENUM_ID"]]["SUMM"] + $a["PROPERTY_COST_2_VALUE"];
		$arFields[$agent_to][$a["PROPERTY_ACCOUNTING_ENUM_ID"]]["PACKS"][] = $a["ID"];
		$arFields[$agent_to][$a["PROPERTY_ACCOUNTING_AGENT_ENUM_ID"]]["SUMM"] = $arFields[$agent_to][$a["PROPERTY_ACCOUNTING_AGENT_ENUM_ID"]]["SUMM"] + $a["PROPERTY_SUMM_AGENT_VALUE"];
		$arFields[$agent_to][$a["PROPERTY_ACCOUNTING_AGENT_ENUM_ID"]]["PACKS"][] = $a["ID"];
	}
	return $arFields;
}

/*****************список сообщений*****************/
function ListOfMasages($from = 0, $to = 0, $act= '', $idd = 0, $type = 0, $iblock = 50, $use_nav = true)
{
	$filter = array("IBLOCK_ID" => $iblock);
	if ($from > 0)
	{
		$filter["PROPERTY_FROM"] = $from;
	}
	if ($to > 0)
	{
		$filter["PROPERTY_TO"] = $to;
	}
	if ($idd > 0)
	{
		$filter["ID"] = $idd;
	}
	if (strlen($act))
	{
		$filter["ACTIVE"] = $act;
	}
	if ($type > 0)
	{
		$filter["PROPERTY_TYPE"] = $type;
	}
    if ($use_nav)
    {
        $on_page = intval($_GET['on_page']);
        if ($on_page < 10)
        {
            $on_page = 10;
        }
        elseif ($on_page >= 500)
        {
            $on_page = 500;
        }
        else {}
        $nav_array = array("nPageSize"=>$on_page);
    }
    else
    {
        $nav_array = false;
    }
	$res3 = CIBlockElement::GetList(
		array("created"=>"DESC"), 
		$filter, 
		false, 
		$nav_array, 
		array("ID","NAME","PROPERTY_TYPE","PROPERTY_COMMENT","DETAIL_TEXT","PROPERTY_TO.NAME","DATE_CREATE","PROPERTY_FROM.NAME","PROPERTY_TO","PROPERTY_FROM","ACTIVE")
	);
    if ($use_nav)
    {
        $arFields["NAV_STRING"] = $res3->GetPageNavStringEx($navComponentObject,"Сообщения","","Y");
    }
	while($ob3 = $res3->GetNextElement())
	{
		$a = $ob3->GetFields();
		$res1 = CIBlockElement::GetList(array("ID"=>"asc"), array("IBLOCK_ID"=>40,"ID"=>$a["PROPERTY_TO_VALUE"]), false, false, array("PROPERTY_TYPE"));
		if ($ob1 = $res1->GetNextElement())
		{
			$arFields_1 = $ob1->GetFields();
			$a['TYPE_TO'] = $arFields_1['PROPERTY_TYPE_ENUM_ID'];
		}
		$res2 = CIBlockElement::GetList(array("ID"=>"asc"), array("IBLOCK_ID"=>40,"ID"=>$a["PROPERTY_FROM_VALUE"]), false, false, array("PROPERTY_TYPE"));
		if ($ob2 = $res2->GetNextElement())
		{
			$arFields_2 = $ob2->GetFields();
			$a['TYPE_FROM'] = $arFields_2['PROPERTY_TYPE_ENUM_ID'];
		}
		if ($a['TYPE_FROM'] == 51) $a['FROM_LINK'] = '/lk/'; 
		if ($a['TYPE_FROM'] == 53) $a['FROM_LINK'] = '/agents/index.php?mode=agent&id='.$a["PROPERTY_FROM_VALUE"]; 
		if ($a['TYPE_FROM'] == 52) $a['FROM_LINK'] = '/shops/index.php?mode=shop&id='.$a["PROPERTY_FROM_VALUE"]; 
		if ($a['TYPE_TO'] == 51) $a['TO_LINK'] = '/lk/'; 
		if ($a['TYPE_TO'] == 53) $a['TO_LINK'] = '/agents/index.php?mode=agent&id='.$a["PROPERTY_TO_VALUE"]; 
		if ($a['TYPE_TO'] == 52) $a['TO_LINK'] = '/shops/index.php?mode=shop&id='.$a["PROPERTY_TO_VALUE"];
		$arFields[] = $a;
	}
	return $arFields;
}

/**************************************************/
function PacksInDebtAgent($a_id)
{
	$packs = array();
	$res = CIBlockElement::GetList(
		array("ID"=>"asc"), 
		array("IBLOCK_ID"=>42,"PROPERTY_218"=>85), 
		false, 
		false, 
		array("ID","PROPERTY_CREATOR","PROPERTY_N_ZAKAZ","PROPERTY_COST_2","DATE_CREATE","PROPERTY_STATE","PROPERTY_CREATOR.NAME","PROPERTY_MANIFEST")
	);
	while ($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$info = GetInfioOfManifest($a["PROPERTY_MANIFEST_VALUE"]);
		if ($info["PROPERTY_AGENT_TO_VALUE"] == $a_id)
		{
			$packs[] = $a["ID"];
		}
	}
	return $packs;
}

/****************чтение прайс-листа****************/
function read_price($global_file)
{
	$out = array();
	$time = date('Y-m-d H:i:s');
	$global_types[0] = 'Стандарт';
	$global_types[1] = 'Экспресс';
	$size_types = sizeof($global_types);
	$name0 = iconv('windows-1251','utf-8','ИнфоОПрайсе');
	$name1 = iconv('windows-1251','utf-8','Показатели');
	$name2 = iconv('windows-1251','utf-8','Показатель');
	$name3 = iconv('windows-1251','utf-8','Города');
	$name4 = iconv('windows-1251','utf-8','Город');
	$name5 = iconv('windows-1251','utf-8','ПрайсЛист');
	$name6 = iconv('windows-1251','utf-8','Запись');
	$name7 = iconv('windows-1251','utf-8','ТипДоставки');
	$name8 = iconv('windows-1251','utf-8','Сумма');
	$name11 = iconv('windows-1251','utf-8','Код');
	$name12 = iconv('windows-1251','utf-8','Признак');
	$name13 = iconv('windows-1251','utf-8','кг');
	$name14 = iconv('windows-1251','utf-8','Направление');
	$text = file_get_contents($global_file);
	$res = simplexml_load_string($text);
	$productNames0 = $res->xpath('/'.$name0.'/'.$name1.'/'.$name2);
	$max_kod = 0;
	for ($i=0;$i<sizeof($productNames0);$i++)
	{
		$pokaz = (array)$productNames0[$i];
		$priznak = $pokaz['@attributes'][$name12];
		$kod = $pokaz['@attributes'][$name11];
		switch ($priznak)
		{
			case '0': $kod = $pokaz['@attributes'][$name11]; $weight[$kod] =  floatval(str_replace(',','.',$pokaz['@attributes'][$name13])); break;
			case '1': $start_index = $pokaz['@attributes'][$name11]; $start_value = $pokaz['@attributes'][$name13]; break;
			case '2': $kod = $pokaz['@attributes'][$name11]; $docs[$kod] =  floatval(str_replace(',','.',$pokaz['@attributes'][$name13])); break;
			case '3': $min_index = $kod; break;
			case '4': $max_index = $kod; break;
			case '5': $persent_1 = $kod; break;
			case '6': $persent_2 = $kod; break;
		}
		if ($kod >= $max_kod)
		{
			$max_kod = $kod;
		}
	}
	$out["WEIGHT"] = $weight;
	$out["START_INDEX"] = $start_index;
	$out["START_VALUE"] = $start_value;
	$out["DOCS"] = $docs;
	$out["MIN"] = $min_index;
	$out["MAX"] = $max_index;
	$out["MAX_CODE"] = $max_kod;
	$out["PERSENT_1"] = $persent_1;
	$out["PERSENT_2"] = $persent_2;
	$productNames = $res->xpath('/'.$name0.'/'.$name3.'/'.$name4);
	for ($i=0;$i<sizeof($productNames);$i++)
	{
		$city = (array)$productNames[$i];
		$ind_city_utf = $city['@attributes'][$name4];
		
		// $ind_city = intval(str_replace('%C2%A0','',urlencode($ind_city_utf)));
		
		$ind_city_utf = iconv('utf-8','windows-1251',$ind_city_utf);
		$ind_city_utf = str_replace(' ','',$ind_city_utf);
		$ind_city = intval($ind_city_utf);
		
		$arSelect = array("ID","NAME","IBLOCK_SECTION_ID");
		$arFilter = array("IBLOCK_ID" => 6, "ID" => $ind_city);
		$res2 = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
		while ($ob = $res2->GetNextElement())
		{
			$arFields = $ob->GetFields();
		}
		$cities[$ind_city] = $arFields['NAME'];
		$res2 = CIBlockSection::GetByID($arFields["IBLOCK_SECTION_ID"]);
		if ($ar_res = $res2->GetNext())
		{
			$cities[$ind_city] .= ", ".$ar_res['NAME'];
		}
	}
	asort($cities);
	$out["CITIES"] = $cities;
	$productNames2 = $res->xpath('/'.$name0.'/'.$name5.'/'.$name6);
	for ($i=0;$i<sizeof($productNames2);$i++)
	{
		$city = (array)$productNames2[$i];
		$ind_city_utf = $city['@attributes'][$name4];
		
		// $city_n = intval(str_replace('%C2%A0','',urlencode($ind_city_utf)));
		
		$ind_city_utf = iconv('utf-8','windows-1251',$ind_city_utf);
		$ind_city_utf = str_replace(' ','',$ind_city_utf);
		$city_n = intval($ind_city_utf);
		
		$type_n = iconv('utf-8','windows-1251',$city['@attributes'][$name7]);
		$pokaz_n = $city['@attributes'][$name2];
		$summ_n_utf = $city['@attributes'][$name8];
		
		// $summ_n = intval(str_replace('%C2%A0','',urlencode($summ_n_utf)));
		
		$summ_n_utf = iconv('utf-8','windows-1251',$summ_n_utf);
		//$summ_n_utf = str_replace(' ','',$summ_n_utf);
		$summ_n = $summ_n_utf;
		
		$result[$city_n][$type_n][$pokaz_n] = $summ_n;
	}
	$out["ORDERS"] = $result;
	return $out;
}

/**************информация о компании***************/
function GetCompany($company)
{
	$arFields = array();
	$filter = array("IBLOCK_ID" => 40, "ID" => $company);
	$res = CIBlockElement::GetList(
		array("NAME"=>"ASC"), 
		$filter, 
		false, 
		false, 
		array("ID", "NAME", "ACTIVE", "PROPERTY_CITY", "PROPERTY_ADRESS", "PROPERTY_PERCENT", "PROPERTY_INN", "PROPERTY_CITIES", 
		"PROPERTY_ACCOUNT", "PROPERTY_EMAIL", "PROPERTY_MAIL_SETTINGS", "PROPERTY_CITE", "PROPERTY_PHONES", "PROPERTY_DEFAULT_CITY", 
		"PROPERTY_DEFAULT_DELIVERY", "PROPERTY_DEFAULT_CASH", "PROPERTY_ID_IN", "PROPERTY_LEGAL_NAME", "PROPERTY_CONTRACT", 
		"PROPERTY_ACTING", "PROPERTY_CITY.NAME", "PROPERTY_PREFIX", "PROPERTY_FOLDER", "PROPERTY_PREFIX_REPORTS", 
		"PROPERTY_RESPONSIBLE_PERSON", "PROPERTY_LEGAL_NAME_FULL", "PROPERTY_COST_ORDERING", "PROPERTY_TYPE", 
		"PROPERTY_UK", "PROPERTY_ON_PAGE", "PROPERTY_TYPE_IM", "PROPERTY_RESPONSIBLE_PERSON_IN", "PROPERTY_REPORT_SIGNS", 
		'PROPERTY_TARIFF_TD', 'PROPERTY_CODE_1C', 'PROPERTY_COEFFICIENT_VW', "PROPERTY_TYPE_WORK_BRANCHES", "PROPERTY_SHOW_LIMITS", 
		"PROPERTY_BY_AGENT", "PROPERTY_INN_REAL", "PROPERTY_ACCOUNT_LK_SETTINGS", "PROPERTY_BY_AGENT.NAME", 
		'PROPERTY_AVAILABLE_WH_WH', 'PROPERTY_AVAILABLE_CALL_COURIER', 'PROPERTY_LEGAL_NAME_NDS', 
		'PROPERTY_LEGAL_NAME_FULL_NDS', 'PROPERTY_ACTING_NDS', 'PROPERTY_RESPONSIBLE_PERSON_IN_NDS', 'PROPERTY_REPORT_SIGNS_NDS',
		"PROPERTY_AVAILABLE_EXPRESS2",
		"PROPERTY_AVAILABLE_EXPRESS4",
		"PROPERTY_AVAILABLE_EXPRESS8",
		"PROPERTY_AVAILABLE_EXPRESS",
		"PROPERTY_AVAILABLE_STANDART",
		"PROPERTY_AVAILABLE_ECONOME", 
		"PROPERTY_SHOW_HIDDEN_INNER_NUMBER"
		)
	);
	if($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$a['PROPERTY_CITY'] = GetFullNameOfCity($a['PROPERTY_CITY_VALUE']);
		$a['PROPERTY_DEFAULT_CITY'] = GetFullNameOfCity($a['PROPERTY_DEFAULT_CITY_VALUE']);
		$db_props = CIBlockElement::GetProperty(40, $a["PROPERTY_UK_VALUE"], array("sort" => "asc"), array("CODE"=>"LEGAL_NAME"));
		if($ar_props = $db_props->Fetch())
		{
			$a["PROPERTY_UK_NAME"] =  $ar_props["VALUE"];
		}
		else
		{
			$a["PROPERTY_UK_NAME"] = "ООО «МСД»";
		}
		$db_props = CIBlockElement::GetProperty(40, $a["PROPERTY_UK_VALUE"], array("sort" => "asc"), array("CODE" => "CITY"));
		if($ar_props = $db_props->Fetch())
		{
			$a["PROPERTY_UK_CITY"] =  GetFullNameOfCity($ar_props["VALUE"]);
			
		}
		$arFields = $a;
	}
    else
    {
        return false;
    }
	return $arFields;
}

/**************настройки по умолчанию**************/
function GetDefaultSettings($uk = false)
{
	$sets_id = 2378056;
	if ($uk)
	{
		$db_props = CIBlockElement::GetProperty(40, $uk, array("sort" => "asc"), array("CODE" => "SETTINGS"));
		if ($ar_props = $db_props->Fetch())
		{
			$sets_id = $ar_props["VALUE"];
		}
	}
	$db_props = CIBlockElement::GetProperty(47, $sets_id, array("sort" => "asc"), array());
	while ($ar_props = $db_props->Fetch())
	{
		$settings = $ar_props;
		$sets[$ar_props["ID"]] = $ar_props["VALUE"];
	}
	return $sets;
}

/*************проверка валидности ИНН**************/
function is_valid_inn($inn)
{
	if ( preg_match('/\D/', $inn) )
	{
		return false;
	}
    $inn = (string) $inn;
    $len = strlen($inn);
    if ( $len === 10 )
    {
        return $inn[9] === (string) (((
            2*$inn[0] + 4*$inn[1] + 10*$inn[2] + 
            3*$inn[3] + 5*$inn[4] +  9*$inn[5] + 
            4*$inn[6] + 6*$inn[7] +  8*$inn[8]
        ) % 11) % 10);
    }
    elseif ( $len === 12 )
    {
        $num10 = (string) (((
             7*$inn[0] + 2*$inn[1] + 4*$inn[2] +
            10*$inn[3] + 3*$inn[4] + 5*$inn[5] + 
             9*$inn[6] + 4*$inn[7] + 6*$inn[8] +
             8*$inn[9]
        ) % 11) % 10);
        $num11 = (string) (((
            3*$inn[0] +  7*$inn[1] + 2*$inn[2] +
            4*$inn[3] + 10*$inn[4] + 3*$inn[5] +
            5*$inn[6] +  9*$inn[7] + 4*$inn[8] +
            6*$inn[9] +  8*$inn[10]
        ) % 11) % 10);
        return $inn[11] === $num11 && $inn[10] === $num10;
    }    
    return false;
}

/******************список агентов******************/
function TheListOfAgents($agent = 0, $act = false, $use_navigation = false, $uk = false, $name = '', $branch = 0)
{
	$arFields = array();
	$filter = array("IBLOCK_ID" => 40,"PROPERTY_TYPE" => 53);
	if ($agent > 0)
	{
		$filter['ID'] = $agent;
	}
	if ($act)
	{
		$filter["ACTIVE"] = "Y";
	}
	if ($uk)
	{
		$filter['PROPERTY_UK'] = $uk;
	}
	if (strlen($name))
	{
		$filter['NAME'] = '%'.$name.'%';
	}
	if (intval($branch) == 1)
	{
		$filter['PROPERTY_BRANCH'] = 1;
	}
	if (intval($branch) == 2)
	{
		$filter['!PROPERTY_BRANCH'] = 1;
	}
	if ($use_navigation)
	{
		if (isset($_GET['on_page']))
		{
			$on_page = intval($_GET['on_page']);
		}
		else
		{
			if (isset($_SESSION['ON_PAGE_GLOBAL']))
			{
				$on_page = intval($_SESSION['ON_PAGE_GLOBAL']);
			}
			else
			{
				$on_page = 10;
			}
		}
		if ($on_page < 10)
		{
			$on_page = 10;
		}
		if ($on_page >= 200)
		{
			$on_page = 200;
		}
		$postran = array("nPageSize" => $on_page);
	}
	else
	{
		$postran = false;
	}
	$res = CIBlockElement::GetList(
		array("NAME"=>"ASC"), 
		$filter, 
		false, 
		$postran, 
		array("ID","NAME","ACTIVE","PROPERTY_CITY","PROPERTY_ADRESS","PROPERTY_PERCENT","PROPERTY_INN","PROPERTY_CITIES","PROPERTY_ACCOUNT","PROPERTY_PHONES","PROPERTY_EMAIL","PROPERTY_CITE",
			"PROPERTY_ID_IN","PROPERTY_LEGAL_NAME","PROPERTY_LEGAL_NAME_FULL","PROPERTY_CONTRACT","PROPERTY_RESPONSIBLE_PERSON","PROPERTY_PREFIX_REPORTS", "PROPERTY_REPORT_SIGNS", "PROPERTY_BRANCH", "PROPERTY_BRAND_NAME", "PROPERTY_ADRESS_FACT", "PROPERTY_TYPE_AGENT", "PROPERTY_LAST_DATE_AGENT", "PROPERTY_COEFFICIENT_VW", "PROPERTY_ADDITIONAL_ADDRESSES", "PROPERTY_INN_REAL"
		)
	);
	if ($use_navigation)
	{
		$arFields["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject,"Агенты","","Y");
	}
	while($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$a['PROPERTY_CITY'] = GetFullNameOfCity($a['PROPERTY_CITY_VALUE']);
		$arFields[] = $a;
	}
	return $arFields;
}

/************список интернет-магазинов*************/
function TheListOfShops($shop = 0, $demo = true, $active = false, $use_navigation = false, $name = '', $uk = false, $type_im = 0)
{
	$arFields = array();
	$filter = array("IBLOCK_ID" => 40, "PROPERTY_TYPE" => 52);
	if ($shop > 0)
	{
		$filter['ID'] = $shop;
	}
	if (!$demo)
	{
		$filter['!PROPERTY_DEMO'] = 89;
	}
	if ($active)
	{
		$filter['ACTIVE'] = "Y";
	}
	if ($use_navigation)
	{
		if (isset($_GET['on_page']))
		{
			$on_page = intval($_GET['on_page']);
		}
		else
		{
			if (isset($_SESSION['ON_PAGE_GLOBAL']))
			{
				$on_page = intval($_SESSION['ON_PAGE_GLOBAL']);
			}
			else
			{
				$on_page = 10;
			}
		}
		if ($on_page < 10)
		{
			$on_page = 10;
		}
		if ($on_page >= 200)
		{
			$on_page = 200;
		}
		$postran = array("nPageSize" => $on_page);
	}
	else
	{
		$postran = false;
	}
	if (strlen($name))
	{
		$filter['NAME'] = '%'.$name.'%';
	}
	if ($uk)
	{
		$filter['PROPERTY_UK'] = $uk;
	}
	if (intval($type_im) > 0)
	{
		$filter['PROPERTY_TYPE_IM'] = intval($type_im);
	}
	$res = CIBlockElement::GetList(
		array("NAME"=>"ASC"), 
		$filter, 
		false, 
		$postran, 
		array("ID","NAME","ACTIVE","PROPERTY_CITY","PROPERTY_ADRESS","PROPERTY_PERCENT","PROPERTY_PERCENT_2","PROPERTY_PERCENT_3","PROPERTY_INN","PROPERTY_CITIES","PROPERTY_ACCOUNT","PROPERTY_PRICE",
			"PROPERTY_PRICE_2","PROPERTY_DEMO","PROPERTY_PHONES","PROPERTY_EMAIL","PROPERTY_PRICE.NAME","PROPERTY_CITE","PROPERTY_USER","PROPERTY_FOLDER","PROPERTY_DEFAULT_CITY",
			"PROPERTY_DEFAULT_DELIVERY", "PROPERTY_DEFAULT_CASH","PROPERTY_LEGAL_NAME","PROPERTY_CONTRACT","PROPERTY_ACTING","PROPERTY_ID_IN",
			"PROPERTY_PREFIX","PROPERTY_COST_ORDERING","PROPERTY_PREFIX_REPORTS","PROPERTY_LEGAL_NAME_FULL","PROPERTY_RESPONSIBLE_PERSON","PROPERTY_PRICE_2.NAME", "PROPERTY_PRICE_3",
			"PROPERTY_PRICE_3.NAME","PROPERTY_FOLDER", "PROPERTY_CONTRACT_TYPE","PROPERTY_UK","PROPERTY_UK.NAME", "PROPERTY_RESPONSIBLE_PERSON_IN", 'PROPERTY_REPORT_SIGNS', 'PROPERTY_OWNERSHIP', 
			'PROPERTY_CITY.NAME', 'PROPERTY_TYPE_IM', 'PROPERTY_CONDITIONS', 'PROPERTY_TARIFF_TD', 'PROPERTY_IM_BY', 'PROPERTY_SELECTION_VAT_REPORT', 'PROPERTY_SUBTRACT_AMOUNT_COD'
		)
	);
	if ($use_navigation)
	{
		$arFields["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject,"Интернет-магазины","","Y");
	}
	while($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$a['PROPERTY_CITY'] = GetFullNameOfCity($a['PROPERTY_CITY_VALUE']);
		$a['PROPERTY_DEFAULT_CITY'] = GetFullNameOfCity($a['PROPERTY_DEFAULT_CITY_VALUE']);
		$arFields[$a["ID"]] = $a;
	}
	return $arFields;
}

/***********список управляющих компаний************/
function TheListOfUKs($current_uk = false, $onlyactive = false, $fullinfo = false, $sort = "NAME")
{
	$arFields = array();
	$filter = array("IBLOCK_ID" => 40, "PROPERTY_TYPE" => 51);
	if ($current_uk)
	{
		$filter["!ID"] = $current_uk;
	}
    if ($onlyactive)
    {
        $filter["ACTIVE"] = "Y";
    }
    $selected = array("ID","NAME","ACTIVE");
    if ($fullinfo)
    {
        $selected = array_merge($selected, array("PROPERTY_ADRESS_FACT", "PROPERTY_BRAND_NAME", "PROPERTY_PAGE_DOGOVOR", "PROPERTY_PHONES"));
    }
	$res = CIBlockElement::GetList(array($sort=>"ASC"), $filter, false, false, $selected);
	while($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
        if ($fullinfo)
        {
            $arFields[$a["ID"]] = $a;
        }
        else
        {
            $arFields[$a["ID"]] = $a["NAME"];
        }
		
	}
	return $arFields;
}

/***************список прайс-листов****************/
function TheListOfPrices($ag_id)
{
	$a = $result = array();
	$arSelect = Array("ID","NAME","PROPERTY_FILE","SORT","ACTIVE","PROPERTY_CITIES");
	$arFilter = Array("IBLOCK_ID"=>51, "PROPERTY_USER"=>$ag_id);
	$res = CIBlockElement::GetList(Array("SORT"=>"asc"), $arFilter, false, false, $arSelect);
	while ($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$a["FILE"] = CFile::GetPath($a["PROPERTY_FILE_VALUE"]);
		$result[] = $a;
	}
	return $result;
}

/*********предвариетльный список сообщений*********/
function ListOfMasagesPre($from = 0, $to = 0, $act='', $idd = 0, $limit = false)
{
	$filter = array("IBLOCK_ID"=>50);
	if ($from > 0)
	{
		$filter["PROPERTY_FROM"] = $from;
	}
	if ($to > 0)
	{
		$filter["PROPERTY_TO"] = $to;
	}
	if ($idd > 0)
	{
		$filter["ID"] = $idd;
	}
	if (strlen($act))
	{
		$filter["ACTIVE"] = $act;
	}
	$arLimit = false;
	if ($limit)
	{
		$arLimit = array("nTopCount" => $limit);
	}
	$res3 = CIBlockElement::GetList(
		array("ID"=>"DESC"), 
		$filter, 
		false, 
		$arLimit, 
		array("ID","NAME","PROPERTY_TYPE","PROPERTY_COMMENT","DETAIL_TEXT","PROPERTY_TO.NAME","DATE_CREATE","PROPERTY_FROM","PROPERTY_FROM.NAME","ACTIVE")
	);
	while($ob = $res3->GetNextElement())
	{
		$arFields[] = $ob->GetFields();
	}
	return $arFields;
}

/*****************удаление статуса*****************/
function DeleteState($pack_id,$history_id)
{
	$fun_res = array();
	$avail_hists = array(43);
	if (in_array($history_id,$avail_hists))
	{
		$short_history = HistoryShortOfPackage($pack_id,$history_id);
		$history = HistoryOfPackage($pack_id);
		CIBlockElement::Delete($short_history[0]["ID"]);
		CIBlockElement::Delete($history[0]["ID"]); 
		unset($short_history,$history);
		$short_history = HistoryShortOfPackage($pack_id);
		$history = HistoryOfPackage($pack_id);
		$db_enum_list = CIBlockProperty::GetPropertyEnum(229, Array(), Array("IBLOCK_ID"=>42, "VALUE"=>$short_history[0]["NAME"]));
		if($ar_enum_list = $db_enum_list->GetNext())
		{
			$short = $ar_enum_list["ID"];
		}
		$db_enum_list = CIBlockProperty::GetPropertyEnum(203, Array(), Array("IBLOCK_ID"=>42, "VALUE"=>$history[0]["NAME"]));
		if($ar_enum_list = $db_enum_list->GetNext())
		{
			$long = $ar_enum_list["ID"];
		}
		$props = array(203 => $long,229=>$short);
		if ($history_id == 43)
		{
			$props[204] = '';
		}
		CIBlockElement::SetPropertyValuesEx($pack_id, false, $props);
		$fun_res['mess'][] = 'Операция успешно отменена';
	}
	else
	{
		$fun_res['errors'][] = 'Невозможно отменить данную операцию';
	}
	return $fun_res;
}

/************устаревшая отправка писем*************/
function SendMessageMail($agent_to, $type, $id_mess = 0, $body = '',$agent_from = 2197189)
{
	$info = GetCompany($agent_to);
	if (($agent_from == 2294524) || ($agent_from == 2249975))
	{
		$email = GetSettingValue(331);
	}
	else
	{
		$email = $info["PROPERTY_EMAIL_VALUE"];
	}
	if ($agent_to == 2197189)
	{
		$email_from = 'dms@newpartner.ru';
	}
	else
	{
		$email_from = 'im@newpartner.ru';
	}
	if (isset($info["PROPERTY_MAIL_SETTINGS_VALUE"][$type]))
	{
		include_once $_SERVER['DOCUMENT_ROOT']."/bitrix/_kerk/class.phpmailer.php";
		$mail = new PHPMailer();
		$mail->Priority = 1; 
        $mail->From = $email_from;
        $mail->FromName = 'DMS "Новый Партнер"';                                                   
        $mail->AddAddress($email, ''); 
        $mail->IsHTML(true);                                                        
		$mail->Subject = "DMS \"Новый Партнер\": ".$info["PROPERTY_MAIL_SETTINGS_VALUE"][$type];
		if (intval($id_mess) > 0)
		{
			$body .= '<p><a href="http://dms.newpartner.ru/messages/index.php?mode=detail&id='.$id_mess.'">Ссылка на данное сообщение в системе DMS</a></p>';
		}
        $mail->Body = $body;
		$mail->Send();
	}
	return true;
}

/******************отправка писем******************/
function SendMessageMailNew($agent_to, $agent_from, $type, $id_template, $parameters = array(), $files = array())
{
	$info = GetCompany($agent_to);
	$info_from = GetCompany($agent_from);
	$test_send = false;
	if ($info['PROPERTY_TYPE_ENUM_ID'] == 51)
	{
		$arTests = array();
		$t_shop = GetSettingValue(507, 0, $agent_to);
		$t_agent = GetSettingValue(508, 0, $agent_to);
		if (intval($t_shop) > 0)
		{
			$arTests[] = $t_shop;
		}
		if (intval($t_agent) > 0)
		{
			$arTests[] = $t_agent;
		}
		if (in_array($agent_from, $arTests))
		{
			$test_send = true;
			$test_email = GetSettingValue(331, 0, $agent_to);
		}
	}
	if (isset($info["PROPERTY_MAIL_SETTINGS_VALUE"][$type]))
	{
		$parameters["EMAIL_TO"] = $EMAIL_TO = $info["PROPERTY_EMAIL_VALUE"];
		if ($type == 129)
		{
			$arEmails = array();
			$arEmails[] = $EMAIL_TO;
			$rsUser = CUser::GetList(($by = "last_name"), ($order = "asc"),array("GROUPS_ID" => 16, "UF_COMPANY_RU_POST" => $agent_to, 'UF_ROLE' => 4937478), array("SELECT" => array("UF_COMPANY_RU_POST","UF_ROLE")));
			while($arUser = $rsUser->Fetch())
			{
				$arEmails[] = $arUser['EMAIL'];
			}
			$parameters["EMAIL_TO"] = implode(', ', $arEmails);
			$EMAIL_TO = $parameters["EMAIL_TO"];
		}
		elseif ($type == 209)
		{
			$arEmails = array();
			$arEmails[] = $EMAIL_TO;
			$rsUser = CUser::GetList(($by = "last_name"), ($order = "asc"), array("GROUPS_ID" => 16, "UF_COMPANY_RU_POST" => $agent_to, 'UF_ROLE' => 7211188), array("SELECT" => array("UF_COMPANY_RU_POST","UF_ROLE")));
			while($arUser = $rsUser->Fetch())
			{
				$arEmails[] = $arUser['EMAIL'];
			}
			$parameters["EMAIL_TO"] = implode(', ', $arEmails);
			$EMAIL_TO = $parameters["EMAIL_TO"];
		}
		/*
		$arEmailsFrom = explode(",",$info_from["PROPERTY_EMAIL_VALUE"]);
		if (count($arEmailsFrom) > 1)
		{
			$parameters["EMAIL_FROM"] = $EMAIL_FROM = trim($arEmailsFrom[0]);
		}
		else
		{
			$parameters["EMAIL_FROM"] = $EMAIL_FROM = $info_from["PROPERTY_EMAIL_VALUE"];
		}
		*/
		$parameters["EMAIL_FROM"] = $EMAIL_FROM = 'dms@newpartner.ru';
		if ($type == 228)
		{
			$parameters["SUBJECT"] = $SUBJECT = "DMS \"Новый Партнер\": ".$parameters['SUBJ'];
		}
		else
		{
			$parameters["SUBJECT"] = $SUBJECT = "DMS \"Новый Партнер\": ".$info["PROPERTY_MAIL_SETTINGS_VALUE"][$type];
		}
		
		if ($test_send)
		{
			$EMAIL_TO = $parameters["EMAIL_TO"] = $test_email;
		}
		
		if (strlen($EMAIL_TO))
		{
			if (count($files) > 0)
			{
				$rsEM = CEventMessage::GetByID($id_template);
				$arEM = $rsEM->Fetch();
				$txt = $arEM["MESSAGE"];
				foreach ($parameters as $k => $v)
				{
					$txt = str_replace("#".$k."#", $v, $txt);
				}
				include_once $_SERVER['DOCUMENT_ROOT']."/bitrix/_kerk/class.phpmailer.php";
				$mail = new PHPMailer();
				$mail->Priority = 1; 
				$mail->From = $EMAIL_FROM;
				$mail->FromName = 'DMS "Новый Партнер"';                                                   
				$mail->AddAddress($EMAIL_TO, '');
				if (strlen(trim($arEM['CC'])))
				{
					$mail->AddCC(trim($arEM['CC']), '');
				}
				if (strlen(trim($arEM['BCC'])))
				{
					$mail->AddBCC(trim($arEM['BCC']), '');
				}
				$mail->IsHTML(true);                                                        
				$mail->Subject = $SUBJECT;
				foreach ($files as $f)
				{
					$mail->AddAttachment($f);
				}
				$mail->ContentType = "text/html";
				$mail->Body = $txt;
				$mail->Send();
				foreach ($files as $f)
				{
					unlink($f);
				}
			}
			else
			{
				$parameters["AGENT_ID"] = $agent_from;
				$parameters["AGENT_NAME"] = $info_from["NAME"];
				$parameters["DATE"] = date('d.m.Y H:i:s');
				if (CEvent::SendImmediate("DMS_EVENT","s5",$parameters,"N",$id_template))
				{
					return true;
				}
				else return false;
			}
		}
	}
	return true;
}

/*********список прайс-листов для магазина*********/
function GetListOfPricesForShop($uk = false)
{
	if ($uk)
	{
		$uks_ids = $uk;
	}
	else
	{
		$result = array();
		$uks = TheListOfUKs();
		$uks_ids = array();
		foreach ($uks as $k => $v)
		{
			$uks_ids[] = $k;
		}
	}
	$res = CIBlockElement::GetList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>51,"PROPERTY_USER"=>$uks_ids,"ACTIVE"=>"Y"), false, false, array("ID","NAME"));
	while($ob = $res->GetNextElement())
	{
		$arr = $ob->GetFields();
		$result[$arr["ID"]] = $arr["NAME"];
	}
	return $result;
}

/**************добавление транзакции***************/
function AddTransaction($type, $from, $to, $user, $date, $summ, $plat, $name, $packs, $true = false)
{
	$el = new CIBlockElement;
	$PROP = array();
	$PROP[259] = $from;
	$PROP[260] = $to;
	$PROP[261] = $summ;
	$PROP[262] = $type;
	$PROP[263] = $plat;
	$PROP[268] = $packs;
	if ($true)
	{
		$PROP[269] = 106;
	}
	$arLoadProductArray = array(
		"MODIFIED_BY" => $user, 
		"IBLOCK_SECTION_ID" => false,
		"IBLOCK_ID" => 53,
		"PROPERTY_VALUES" => $PROP,
		"NAME" => $name,
		"DATE_CREATE" => $date,
		"ACTIVE" => "Y");
	$PRODUCT_ID = $el->Add($arLoadProductArray);
	return $PRODUCT_ID;
}

/*****************заказы у курьера*****************/
function PackagesOfCourier($cur, $status = 0)
{
	$filter = array("IBLOCK_ID" => 42, "PROPERTY_COURIER" => $cur);
	if ($status > 0)
	{
		$filter["PROPERTY_STATE"] = $status;
	}
	$select = array("ID","NAME","PROPERTY_N_ZAKAZ","PROPERTY_RECIPIENT","PROPERTY_PHONE","PROPERTY_ADRESS","PROPERTY_COST_1","PROPERTY_COST_2","PROPERTY_COST_3","PROPERTY_COST_4","PROPERTY_STATE",
		"PROPERTY_STATE_SHORT","PROPERTY_COURIER",'PROPERTY_CONDITIONS',"PROPERTY_COURIER.NAME","PROPERTY_ID_IN","PROPERTY_CITY.NAME","PROPERTY_WEIGHT","PROPERTY_PLACES","PROPERTY_PVZ",
		"PROPERTY_CREATOR","PROPERTY_CREATOR.NAME","PROPERTY_PREFERRED_TIME","PROPERTY_COMMENTS_COURIER","PROPERTY_COST_GOODS","DATE_CREATE","PROPERTY_TYPE_PAYMENT"
	);
	$res = CIBlockElement::GetList(array("timestamp_x" => "DESC"), $filter, false, false, $select);
	while($ob = $res->GetNextElement())
	{
		 $a = $ob->GetFields();
		 if (count($a['PROPERTY_PREFERRED_TIME_VALUE']) == 2)
		 {
			$d1 =  substr($a['PROPERTY_PREFERRED_TIME_VALUE'][0], 0, 10);
			$d2 =  substr($a['PROPERTY_PREFERRED_TIME_VALUE'][1], 0, 10);
			if ($d1 == $d2)
			{
				$a['TIME'] = $d1.' '.substr($a['PROPERTY_PREFERRED_TIME_VALUE'][0], 11, 5).'-'.substr($a['PROPERTY_PREFERRED_TIME_VALUE'][1], 11, 5);
			}
			else
			{
				$a['TIME'] = $d1.' '.substr($a['PROPERTY_PREFERRED_TIME_VALUE'][0], 11, 5).' - '.$d2.' '.substr($a['PROPERTY_PREFERRED_TIME_VALUE'][1], 11, 5);
			}
		}
		elseif (count($a['PROPERTY_PREFERRED_TIME_VALUE']) == 1)
		{
			$a['TIME']  = substr($a['PROPERTY_PREFERRED_TIME_VALUE'][0], 0, 16);
		}
		else
		{
			$a['TIME'] = '';
		}
		$a['SHOP'] = GetCompany($a['PROPERTY_CREATOR_VALUE']);
		$a['GOODS'] = GetGoodsOdPack($a['ID']);
		$arFields[] = $a;
	}
	return $arFields;
}

/*************информация о транзакции**************/
function GetTransactions($from = 0,$to = 0,$type = 0,$id = 0, $pack = 0, $types_not_show = array(), $states_not_show = array(106))
{
	$filter = array("IBLOCK_ID" => 53);
	if (count($states_not_show) > 0)
	{
		$filter["!PROPERTY_STATE"] = $states_not_show;
	}
	if (count($types_not_show) > 0)
	{
		$filter["!PROPERTY_TYPE"] = $types_not_show;
	}
	if ($pack > 0)
	{
		$filter["PROPERTY_PACKAGES"] = $pack;
	}
	if ($from > 0)
	{
		$filter["PROPERTY_FROM"] = $from;
	}
	if ($to > 0)
	{
		$filter["PROPERTY_TO"] = $to;
	}
	if ($type > 0)
	{
		$filter["PROPERTY_TYPE"] = $type;
	}
	if ($id > 0)
	{
		$filter["ID"] = $id;
	}
	$select = array("ID","NAME","PROPERTY_FROM","PROPERTY_TO","PROPERTY_SUMM","PROPERTY_PAYMENT_ORDER","PROPERTY_TYPE","PROPERTY_FROM.NAME","PROPERTY_TO.NAME","DATE_CREATE","PROPERTY_STATE");
	$res = CIBlockElement::GetList(array("created" => "DESC"), $filter, false, false, $select);
	while($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$VALUES = array();
		$res1 = CIBlockElement::GetProperty(53, $a["ID"], "sort", "asc", array("CODE" => "PACKAGES"));
		while ($ob1 = $res1->GetNext())
		{
			if (intval($ob1['VALUE']) > 0)
			{
				$VALUES[] = $ob1['VALUE'];
			}
		}
		$a["PACKS"] = $VALUES;
		$arFields[] = $a;
	}
	return $arFields;
}

/****************список манифестов*****************/
function THeListOfManifests($cur,$status = 0, $nav = false)
{
	$arFields = array();
	$filter = array("IBLOCK_ID"=>41,"PROPERTY_CARRIER"=>$cur);
	if ($status > 0)
	{
		$filter["PROPERTY_STATE"] = $status;
	}
	if ($nav)
	{
		$on_page = intval($_GET['on_page']);
		if ($on_page < 10)
		{
			$on_page = 10;
		}
		elseif ($on_page >= 500)
		{
			$on_page = 500;
		}
		else {};
		$nav_array = array("nPageSize"=>$on_page);
	}
	else
	{
		$nav_array = false;
	}
	$res = CIBlockElement::GetList(
		array("ID"=>"DESC"), 
		$filter, 
		false, 
		$nav_array, 
		array("ID","NAME","ACTIVE","PROPERTY_CITY","PROPERTY_AGENT_TO","PROPERTY_STATE","DATE_CREATE","PROPERTY_AGENT_TO.NAME","PROPERTY_NUMBER")
	);
	if ($nav)
	{
		$arFields["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject,"Манифесты","","Y");
	}
	while($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$a['PROPERTY_CITY'] = GetFullNameOfCity($a['PROPERTY_CITY_VALUE']);
		$arFields[] = $a;
	}
	return $arFields;
}

/*********список отчетов интернет-магазина*********/
function GetReportOnShops($date_from ,$date_to, $shop_id = 0, $full = false)
{
	$date_from = $date_from.' 00:00:00';
	$date_to = $date_to.' 23:59:59';
	$filter = array("IBLOCK_ID"=>42,">=DATE_CREATE"=>$date_from,"<=DATE_CREATE"=>$date_to,"!PROPERTY_STATE"=>39);
	if ($shop_id > 0)
	{
		$filter["PROPERTY_CREATOR"] = $shop_id;
	}
	$res = CIBlockElement::GetList(
		array("created"=>"DESC"), 
		$filter, 
		false, 
		false, 
		array("ID","NAME","DATE_CREATE","PROPERTY_CREATOR","PROPERTY_CREATOR.NAME","PROPERTY_STATE","PROPERTY_COST_2","PROPERTY_SUMM_SHOP","PROPERTY_RATE","PROPERTY_ACCOUNTING")
	);
	while($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$arFields["ORDERS"][$a["PROPERTY_CREATOR_VALUE"]][] = $a;
		$arFields["SHOPS"][$a["PROPERTY_CREATOR_VALUE"]] = $a["PROPERTY_CREATOR_NAME"];
	}
	if (!$full)
	{
		$arFields["SHORT"] = array();
		foreach ($arFields["SHOPS"] as $k => $v)
		{
			$ords = $arFields["ORDERS"][$k];
			$arFields["SHORT"][$k]["NAME"] = $v;
			$arFields["SHORT"][$k]["CNT"] = $arFields["SHORT"][$k]["COST_2"] = $arFields["SHORT"][$k]["RATE"] = $arFields["SHORT"][$k]["SUMM_SHOP"] = $arFields["SHORT"][$k]["RAZN"] = 
				$arFields["SHORT"][$k]["STATE"] = $arFields["SHORT"][$k]["VYIR"] = $arFields["SHORT"][$k]["PERECHISLENO"] = 0;
			foreach ($ords as $kk => $vv)
			{
				$arFields["SHORT"][$k]["CNT"]++;
				$arFields["SHORT"][$k]["COST_2"] = $arFields["SHORT"][$k]["COST_2"] + $ords[$kk]["PROPERTY_COST_2_VALUE"];
				$arFields["SHORT"][$k]["RATE"] = $arFields["SHORT"][$k]["RATE"] + $ords[$kk]["PROPERTY_RATE_VALUE"];
				$arFields["SHORT"][$k]["SUMM_SHOP"] = $arFields["SHORT"][$k]["SUMM_SHOP"] + $ords[$kk]["PROPERTY_SUMM_SHOP_VALUE"];
				if ($ords[$kk]["PROPERTY_STATE_ENUM_ID"] == 44)
				{
					$arFields["SHORT"][$k]["STATE"]++;
				}
			}		
			$arFields["SHORT"][$k]["VYIR"] = $arFields["SHORT"][$k]["RATE"] + $arFields["SHORT"][$k]["SUMM_SHOP"];
			$arFields["SHORT"][$k]["RAZN"] = $arFields["SHORT"][$k]["COST_2"] - $arFields["SHORT"][$k]["VYIR"];
			$arFields["SHORT"][$k]["PERECHISLENO"] = SummReceived($date_from,$date_to,$k);
		}
	}
	return $arFields;
}

/**************************************************/
function SummReceived($date_from, $date_to, $agent)
{
	$summ = 0;
	$res = CIBlockElement::GetList(
		array("created"=>"DESC"), 
		array("IBLOCK_ID" => 53,">=DATE_CREATE" => $date_from,"<=DATE_CREATE" => $date_to,"PROPERTY_TO" => $agent,"PROPERTY_TYPE"=>99), 
		false, 
		false, 
		array("ID","PROPERTY_SUMM")
	);
	while($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$summ = $summ + $a["PROPERTY_SUMM_VALUE"];
	}
	return $summ;
}

/**************список отчетов агента***************/
function GetReportOnAgents($date_from ,$date_to, $agent_id = 0, $full = false, $uk = 2197189)
{
	$filter_0 = array("IBLOCK_ID" => 41,"><PROPERTY_DATE_SEND" => array(ConvertDateTime($date_from,"YYYY-MM-DD")." 00:00:01",ConvertDateTime($date_to,"YYYY-MM-DD")." 23:59:59"));
	if ($agent_id > 0)
	{
		$filter_0["AGENT_TO"] = $agent_id;
	}
	$res_0 = CIBlockElement::GetList(array("created"=>"DESC"), $filter_0, false, false, array("ID","PROPERTY_AGENT_TO","PROPERTY_AGENT_TO.NAME","PROPERTY_STATE"));
	while($ob_0 = $res_0->GetNextElement())
	{
		$a_0 = $ob_0->GetFields();
		$arFields["AGENTS"][$a_0["PROPERTY_AGENT_TO_VALUE"]] = $a_0['PROPERTY_AGENT_TO_NAME'];
		$arFields["MANIFESTOS"][$a_0["PROPERTY_AGENT_TO_VALUE"]][$a_0['ID']] = $a_0["PROPERTY_STATE_ENUM_ID"];
	}
	$arFields["SHORT"] = array();
	foreach ($arFields["MANIFESTOS"] as $k => $v)
	{
		$arFields["SHORT"][$k]["MANS"] = count($v);
		$arFields["SHORT"][$k]["MANS_IN"] = 0;
		$mans_in = array();
		foreach ($v as $m_k => $m_stat)
		{
			if ($m_stat == 48)
			{
				$arFields["SHORT"][$k]["MANS_IN"]++;
				$mans_in[] = $m_k;
			}
		}
		if (count($mans_in) > 0)
		{
			$filter = array("IBLOCK_ID" => 42, "PROPERTY_MANIFEST" => $mans_in);
			$res = CIBlockElement::GetList(array("created"=>"DESC"), $filter, false, false, array("ID","NAME","DATE_CREATE","PROPERTY_STATE","PROPERTY_COST_2","PROPERTY_SUMM_AGENT","PROPERTY_RATE_AGENT"));
			while($ob = $res->GetNextElement())
			{
				$a = $ob->GetFields();
				$arFields["ORDERS"][$k][] = $a;
			}
		}
	}
	if (!$full)
	{
		foreach ($arFields["AGENTS"] as $k => $v)
		{
			$ords = $arFields["ORDERS"][$k];
			$arFields["SHORT"][$k]["NAME"] = $v;
			$arFields["SHORT"][$k]["CNT"] = $arFields["SHORT"][$k]["COST_2"] = $arFields["SHORT"][$k]["RATE_AGENT"] = $arFields["SHORT"][$k]["RATE"] = $arFields["SHORT"][$k]["PRISLANO"] = 
				$arFields["SHORT"][$k]["STATE"] = $arFields["SHORT"][$k]["AGETU"] = 0;
			foreach ($ords as $kk => $vv)
			{
				$arFields["SHORT"][$k]["CNT"]++;
				$arFields["SHORT"][$k]["COST_2"] = $arFields["SHORT"][$k]["COST_2"] + $ords[$kk]["PROPERTY_COST_2_VALUE"];
				$arFields["SHORT"][$k]["RATE_AGENT"] = $arFields["SHORT"][$k]["RATE_AGENT"] + $ords[$kk]["PROPERTY_RATE_AGENT_VALUE"];
				$arFields["SHORT"][$k]["RATE"] = $arFields["SHORT"][$k]["RATE"] + $ords[$kk]["PROPERTY_SUMM_AGENT_VALUE"];
				if ($ords[$kk]["PROPERTY_STATE_ENUM_ID"] == 44)
				{
					$arFields["SHORT"][$k]["STATE"]++;
				}
			}
			$arFields["SHORT"][$k]["SUMM_AGENT"] = GetSummTransactions($uk, 'in', $date_from, $date_to, 0, $k);
			$arFields["SHORT"][$k]["AGETU"] = GetSummTransactions($uk, 'out', $date_from, $date_to, 0, $k);
			$arFields["SHORT"][$k]["VV"] = $arFields["SHORT"][$k]["SUMM_AGENT"] - $arFields["SHORT"][$k]["AGETU"];
			$arFields["SHORT"][$k]["RR"] = $arFields["SHORT"][$k]["RATE_AGENT"] + $arFields["SHORT"][$k]["RATE"];
		}
	}
	return $arFields;
}

/***********статистика интернет-магазина***********/
function StatisticShop($shop_id)
{
	$months = array();
	$months_names = array();
	$months_names[1] = "'Январь'";
	$months_names[2] = "'Февраль'";
	$months_names[3] = "'Март'";
	$months_names[4] = "'Апрель'";
	$months_names[5] = "'Май'";
	$months_names[6] = "'Июнь'";
	$months_names[7] = "'Июль'";
	$months_names[8] = "'Август'";
	$months_names[9] = "'Сентябрь'";
	$months_names[10] = "'Октябрь'";
	$months_names[11] = "'Ноябрь'";
	$months_names[12] = "'Декабрь'";
	for ($i=5; $i>=0; $i--)
	{
		$now_month = intval(date('m'));
		if ($now_month >= 6 )
		{
			$month_yet =  $now_month - $i;
			$year_yet = date('Y');
		}
		else
		{
			$month_yet =  $now_month - $i+12;
			$year_yet = date('Y') - 1;
		}
		if ($month_yet < 10)
		{
			$month_yet = '0'.$month_yet;
		}
		if ($now_month == 12)
		{
			$month_yet_2 = 1;
			$year_yet_2 = date('Y') + 1;
		}
		if (($now_month >= 5)&&($now_month != 12))
		{
			$month_yet_2 =  $now_month - $i + 1;
			$year_yet_2 = date('Y');
		}
		if ($now_month < 5)
		{
			$month_yet_2 =  $now_month - $i + 1 + 12;
			$year_yet_2 = date('Y') - 1;
		}
		if ($month_yet_2 < 10)
		{
			$month_yet_2 = '0'.$month_yet_2;
		}
		$date_from = '01.'.$month_yet.'.'.$year_yet;
		$date_to = '01.'.$month_yet_2.'.'.$year_yet_2;
		$months['month_name'][$i] = $months_names[intval($month_yet)];
		$months['count'][$i] = $months['dostavleno'][$i] = $months['cost'][$i] = $months['summ_shop'][$i] = $months['polucheno'][$i] = 0;
		$filter = array("IBLOCK_ID"=>42,">=DATE_CREATE"=>$date_from,"<DATE_CREATE"=>$date_to,"PROPERTY_CREATOR"=>$shop_id,"!PROPERTY_STATE"=>39);
		$res = CIBlockElement::GetList(
			array("created"=>"DESC"), 
			$filter, 
			false, 
			false, 
			array("ID","NAME","PROPERTY_STATE","PROPERTY_COST_2","PROPERTY_SUMM_SHOP","PROPERTY_RATE","PROPERTY_ACCOUNTING")
		);
		while($ob = $res->GetNextElement())
		{
			$a = $ob->GetFields();
			$months['count'][$i]++;
			if ($a["PROPERTY_STATE_ENUM_ID"] == 44)
			{
				$months['dostavleno'][$i]++;
			}
			$months['cost'][$i] = $months['cost'][$i] + $a["PROPERTY_COST_2_VALUE"];
			$months['summ_shop'][$i] = $months['summ_shop'][$i] + $a["PROPERTY_SUMM_SHOP_VALUE"] + $a["PROPERTY_RATE_VALUE"];
			$months["polucheno"][$i] = SummReceived($date_from, $date_to, $shop_id);
		}
	}
	$result = array();
	foreach ($months as $k => $v)
	{
		$result[$k] = implode(', ', $v);
	}
	return $result;
}

/****************список транзакций*****************/
function GetListTransactions($agent_id, $type = 'in', $date_from = '', $date_to = '')
{
	$filter = array("IBLOCK_ID" => 53);
	if ($agent_id > 0)
	{
		if ($type == 'out')
		{
			$filter["PROPERTY_FROM"] = $agent_id;
		}
		else
		{
			$filter["PROPERTY_TO"] = $agent_id;
		}
	}
	if (strlen($date_from))
	{
		$date_from = $date_from.' 00:00:00';
		$filter[">=DATE_CREATE"] = $date_from;
	}
	if (strlen($date_to))
	{
		$date_to = $date_to.' 23:59:59';
		$filter["<=DATE_CREATE"] = $date_to;
	}
	$on_page = intval($_GET['on_page']);
	if ($on_page < 20)
	{
		$on_page = 20;
	}
	elseif ($on_page >= 500)
	{
		$on_page = 500;
	}
	else {};
	$select = array("ID","NAME","DATE_CREATE","PROPERTY_SUMM","PROPERTY_TO","PROPERTY_FROM","PROPERTY_TO.NAME","PROPERTY_FROM.NAME","PROPERTY_PAYMENT_ORDER","PROPERTY_STATE");
	$res_count = CIBlockElement::GetList(array("created"=>"DESC"), $filter, array(), false, array());
	$arFields["COUNT"] = $res_count;
	$res = CIBlockElement::GetList(array("created"=>"DESC"), $filter, false, array("nPageSize"=>$on_page), $select);
	$arFields["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject,"Транзакции","","Y");
	while($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$a["PACKS"] = array();
		$res2 = CIBlockElement::GetProperty(53, $a["ID"], "sort", "asc", array("ID" => 268));
		while ($ob2 = $res2->GetNext())
		{
			$a["PACKS"][] = $ob2['VALUE'];
		}
		$arFields[] = $a;
	}
	return $arFields;
}

/*****************сумма транзакций*****************/
function GetSummTransactions($agent_id, $type = 'in', $date_from = '', $date_to = '',$type_contragent = 0, $contragent_id = 0)
{
	$filter = array("IBLOCK_ID"=>53,"PROPERTY_TYPE"=>99);
	if ($type == 'out')
	{
		$filter["PROPERTY_FROM"] = $agent_id;
	}
	else
	{
		$filter["PROPERTY_TO"] = $agent_id;
	}
	if (strlen($date_from))
	{
		$date_from = $date_from.' 00:00:00';
		$filter[">=DATE_CREATE"] = $date_from;
	}
	if (strlen($date_to))
	{
		$date_to = $date_to.' 23:59:59';
		$filter["<=DATE_CREATE"] = $date_to;
	}
	if ($contragent_id > 0)
	{
		if ($type == 'out')
		{
			$filter["PROPERTY_TO"] = $contragent_id;
		}
		else
		{
			$filter["PROPERTY_FROM"] = $contragent_id;
		}
	}
	$summ = 0;
	$select = array("PROPERTY_SUMM","PROPERTY_FROM","PROPERTY_TO");
	$res = CIBlockElement::GetList(array("ID"=>"DESC"), $filter, false, false, $select);
	while($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		if (intval($type_contragent) > 0)
		{
			if ($type == 'out')
			{
				$contr = $a["PROPERTY_TO_VALUE"];
			}
			else
			{
				$contr = $a["PROPERTY_FROM_VALUE"];
			}
			$info = GetAgentInfo($contr);
			if ($info["PROPERTY_TYPE_ENUM_ID"] == $type_contragent)
			{
				$summ = $summ + $a["PROPERTY_SUMM_VALUE"];
			}
		}
		else 
		{
			$summ = $summ + $a["PROPERTY_SUMM_VALUE"];
		}
	}
	return $summ;
}

/*************статистика по умолчанию**************/
function StatisticDefaut($agent_id)
{
	$months = array();
	$months_names = array();
	$months_names[1] = "'Январь'";
	$months_names[2] = "'Февраль'";
	$months_names[3] = "'Март'";
	$months_names[4] = "'Апрель'";
	$months_names[5] = "'Май'";
	$months_names[6] = "'Июнь'";
	$months_names[7] = "'Июль'";
	$months_names[8] = "'Август'";
	$months_names[9] = "'Сентябрь'";
	$months_names[10] = "'Октябрь'";
	$months_names[11] = "'Ноябрь'";
	$months_names[12] = "'Декабрь'";
	for ($i=5;$i>=0;$i--)
	{
		$now_month = intval(date('m'));
		if ($now_month >= 6 )
		{
			$month_yet =  $now_month - $i;
			$year_yet = date('Y');
		}
		else
		{
			$month_yet =  $now_month - $i+12;
			$year_yet = date('Y') - 1;
		}
		if ($month_yet < 10)
		{
			$month_yet = '0'.$month_yet;
		}
		if ($now_month == 12)
		{
			$month_yet_2 = 1;
			$year_yet_2 = date('Y') + 1;
		}
		if (($now_month >= 5)&&($now_month != 12))
		{
			$month_yet_2 =  $now_month - $i + 1;
			$year_yet_2 = date('Y');
		}
		if ($now_month < 5)
		{
			$month_yet_2 =  $now_month - $i + 1 + 12;
			$year_yet_2 = date('Y') - 1;
		}
		if ($month_yet_2 < 10) 
		{
			$month_yet_2 = '0'.$month_yet_2;
		}
		$date_from = '01.'.$month_yet.'.'.$year_yet;
		$date_to = '01.'.$month_yet_2.'.'.$year_yet_2;
		$months['month_name'][$i] = $months_names[intval($month_yet)];
		$months["PRISLANO"][$i] = $months["AGETU"][$i] = $months["TO_SHOPS"][$i] = $months["VV"][$i] = 0;
		$months["PRISLANO"][$i] = GetSummTransactions($agent_id,'in',$date_from,$date_to);
		$months["AGETU"][$i] = GetSummTransactions($agent_id,'out',$date_from,$date_to,53);
		$months["TO_SHOPS"][$i] = GetSummTransactions($agent_id,'out',$date_from,$date_to,52);
		$months["VV"][$i] = $months["PRISLANO"][$i] - $months["AGETU"][$i] - $months["TO_SHOPS"][$i];
	}
	$result = array();
	foreach ($months as $k => $v)
	{
		$result[$k] = implode(', ',$v);
	}
	return $result;
}

/*****************открытый период******************/
function GetOpenPeriod()
{
	$period = 0;
	$res = CIBlockElement::GetList(array("created"=>"DESC"), array("IBLOCK_ID"=>55, "PROPERTY_281"=>false), false, array("nTopCount"=>1), array("ID","PROPERTY_280"));
	if($ar_fields = $res->GetNext())
	{
		$period = $ar_fields["ID"];
	}
	return $period;
}

/************добавление заказа в период************/
function AddElementToPeriod($element, $propery_id, $user)
{
	$period = GetOpenPeriod();
	$VALUES = array();
	$res = CIBlockElement::GetProperty(55, $period, "sort", "asc", array("ID" => $propery_id));
	while ($ob = $res->GetNext())
	{
		$VALUES[] = $ob['VALUE'];
	}
	$VALUES[] = $element;
	CIBlockElement::SetPropertyValuesEx($period, 55, array($propery_id=>$VALUES));
	return true;
}

/***************информация о периоде***************/
function GetInfoOfPeriod($id)
{
	$res = CIBlockElement::GetByID($id);
	if ($ar_res = $res->GetNext())
	{
		$ar_res["START"] = $ar_res["END"] = '';
		$ar_res["COUNT_START"] = $ar_res["ADOPTED"] = $ar_res["DELIVERED"] = $ar_res["COUNT_END"] = array();
		$db_props = CIBlockElement::GetProperty(55, $id, "sort", "asc");
		while ($ob = $db_props->GetNext())
		{
			if (strlen($ob["VALUE"]))
			{
				if (in_array($ob["CODE"],array("START","END")))
				{
					$ar_res[$ob["CODE"]] = $ob["VALUE"];
				}
				else
				{
					$ar_res[$ob["CODE"]][] = $ob["VALUE"];
				}
			}
		}
		return $ar_res;
	}
	else
	{
		return array();
	}
}

/*****************список периодов******************/
function GetAllPeriods()
{
	$periods = array();
	$res = CIBlockElement::GetList(array("created" => "DESC"), array("IBLOCK_ID" => 55), false, array("nPageSize" => 10), array("ID","PROPERTY_280","PROPERTY_281","NAME"));
	$periods["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject,"Периоды","","Y");
	while($ar_fields = $res->GetNext())
	{
		$periods[] = $ar_fields;
	}
	return $periods;
}

/**************************************************/
function GetAgentsOfPacksAndPrices($array_packs = array(), $agents = array())
{
	$resy = array();
	foreach ($agents as $agent_id)
	{
		$resy['summ'][$agent_id] = $resy['summ_to_agent'][$agent_id] = array();
	}
	foreach ($array_packs as $pack_id)
	{
		$db_props = CIBlockElement::GetProperty(42, $pack_id, array("sort" => "asc"), array("CODE"=>"MANIFEST"));
		$ar_props = $db_props->Fetch();
		$manifest_id = $ar_props["VALUE"];
		$db_props = CIBlockElement::GetProperty(42, $pack_id, array("sort" => "asc"), array("CODE"=>"OBTAINED"));
		$ar_props = $db_props->Fetch();
		$price = floatval($ar_props["VALUE"]);
		$db_props = CIBlockElement::GetProperty(42, $pack_id, array("sort" => "asc"), array("CODE"=>"COST_2"));
		$ar_props = $db_props->Fetch();
		$cost = floatval($ar_props["VALUE"]);
		$db_props = CIBlockElement::GetProperty(42, $pack_id, array("sort" => "asc"), array("CODE"=>"SUMM_AGENT"));
		$ar_props = $db_props->Fetch();
		$price_to_agent = $ar_props["VALUE"];
		$db_props = CIBlockElement::GetProperty(42, $pack_id, array("sort" => "asc"), array("CODE"=>"RATE_AGENT"));
		$ar_props = $db_props->Fetch();
		$price_to_agent_2 = $ar_props["VALUE"];
		$db_props = CIBlockElement::GetProperty(41, $manifest_id, array("sort" => "asc"), array("CODE"=>"AGENT_TO"));
		$ar_props = $db_props->Fetch();
		$agent_id = intval($ar_props["VALUE"]);
		$resy['ids'][$agent_id][] = $pack_id;
		$resy['summ'][$agent_id][] = $price;
		$resy['cost'][$agent_id][] = $cost;
		$resy['summ_to_agent'][$agent_id][] = $price_to_agent + $price_to_agent_2;
	}
	return $resy;
}

/*****************стоимость заказа*****************/
function GetSummOfPacks($array_packs = array())
{
	$price = 0;
	foreach ($array_packs as $pack_id)
	{
		$db_props = CIBlockElement::GetProperty(42, $pack_id, array("sort" => "asc"), array("CODE"=>"COST_2"));
		$ar_props = $db_props->Fetch();
		$price = $price + $ar_props["VALUE"];
	}
	return $price;
}

/*****************закрытие периода*****************/
function ClosePeriod()
{
	$ostats = array();
	$res = CIBlockElement::GetList(array("created"=>"DESC"), array("IBLOCK_ID"=>42, "PROPERTY_203"=>array(40,43,45,46,55,56,79)), false, false, array("ID"));
	while($ar_fields = $res->GetNext())
	{
		$ostats[] = $ar_fields["ID"];
	}
	$res = CIBlockElement::GetList(array("created"=>"DESC"), array("IBLOCK_ID"=>55, "PROPERTY_281"=>false), false, array("nTopCount"=>1), array("ID","PROPERTY_280"));
	if($ar_fields = $res->GetNext())
	{
		$close_per_id = $ar_fields["ID"];
		CIBlockElement::SetPropertyValuesEx($close_per_id, 55, array(285=>$ostats));
		CIBlockElement::SetPropertyValuesEx($close_per_id, 55, array(281=>date("d.m.Y")));	
	}
	$PROP = array();
	$PROP[280] = date("d.m.Y");
	$PROP[282] = $ostats;
	$el = new CIBlockElement;
	$arLoadProductArray = array(
		"IBLOCK_SECTION_ID" => false,
		"IBLOCK_ID" => 55,
		"NAME" => date('Y-W'),
		"PROPERTY_VALUES" => $PROP
	);
	$PRODUCT_ID = $el->Add($arLoadProductArray);
	if ($close_per_id > 0)
	{
		$info = GetInfoOfPeriod($close_per_id);
		$arr = AvailableAgents();
		$ag_array = array();
		foreach ($arr as $ag_id => $v)
		{
			$ag_array[] = $ag_id;
		}
		$resy = GetAgentsOfPacksAndPrices($info["DELIVERED"], $ag_array);
		foreach ($resy['ids'] as $agent => $packs_array)
		{
			foreach ($packs_array as $p)
			{
				$trs = array();
				$filter = array("IBLOCK_ID" => 53,"PROPERTY_TO" => $agent,"PROPERTY_TYPE" => 101,"PROPERTY_PACKAGES" => $p);
				$select = array("ID","NAME","PROPERTY_FROM","PROPERTY_TO","PROPERTY_SUMM","PROPERTY_PAYMENT_ORDER","PROPERTY_TYPE","PROPERTY_FROM.NAME","PROPERTY_TO.NAME","DATE_CREATE",
					"PROPERTY_STATE"
				);
				$res = CIBlockElement::GetList(array("timestamp_x"=>"DESC"), $filter, false, false, $select);
				while($ob = $res->GetNextElement())
				{
					$a = $ob->GetFields();
					$VALUES = array();
					$res1 = CIBlockElement::GetProperty(53, $a["ID"], "sort", "asc", array("CODE" => "PACKAGES"));
					while ($ob1 = $res1->GetNext())
					{
						 $VALUES[] = $ob1['VALUE'];
					}
					$a["PACKS"] = $VALUES;
					$trs[] = $a;
				}
				foreach ($trs as $tr)
				{
					CIBlockElement::SetPropertyValuesEx($tr["ID"], false, array(269=>106));
				}
				CIBlockElement::SetPropertyValuesEx($p, false, array(218=>85));
			}
			$razn = array_sum($resy["summ"][$agent]) - array_sum($resy["summ_to_agent"][$agent]);
			$el = new CIBlockElement;
			$arLoadProductArray = Array(
				"IBLOCK_ID"      => 50,
  				"PROPERTY_VALUES"=> array(234=>2197189,235=>$agent,236=>82,242=>CurrencyFormat($razn,"RUU")), "NAME" => "Запрос денежных средств"
			);
			$PRODUCT_ID = $el->Add($arLoadProductArray);
			SendMessageMailNew($agent,2197189,98,173,array(
				"ID_MESS" => $PRODUCT_ID,
				"SUMM" => CurrencyFormat($razn,"RUU"),
			));
			$trans_name = "Запрос денежных средств у агента";
			$el = new CIBlockElement;
			$PROP = array();
			$PROP[259] = 2197189;
			$PROP[260] = $agent;
			$PROP[261] = $razn;
			$PROP[262] = 100;
			$PROP[263] = '';
			$PROP[268] = $packs_array;
			$arLoadProductArray = array(
				"IBLOCK_SECTION_ID" => false,
				"IBLOCK_ID" => 53,
				"PROPERTY_VALUES" => $PROP,
				"NAME" => $trans_name,
				"DATE_CREATE" => date('d.m.Y H:i:s'),
				"ACTIVE" => "Y");
			$trans_id = $el->Add($arLoadProductArray);
		}
	}
	return true;
}

/**************список товаров заказа***************/
function GetGoodsOdPack($ord_id)
{
	$goods_in_order = array();
	$res = CIBlockElement::GetList(
		array("ID" => "desc"), 
		array("IBLOCK_ID"=>63, "PROPERTY_302"=>$ord_id), 
		false, 
		false, 
		array("ID","PROPERTY_300","PROPERTY_301", "PROPERTY_300.NAME", "PROPERTY_360","PROPERTY_361","PROPERTY_362")
	);
	while ($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();
		$goods_in_order[$arFields["ID"]] = array();
		$goods_in_order[$arFields["ID"]]["COUNT"] = $arFields["PROPERTY_301_VALUE"];
		$goods_in_order[$arFields["ID"]]["GOOD_ID"] = $arFields["PROPERTY_300_VALUE"];
		$goods_in_order[$arFields["ID"]]["NAME"] = $arFields["PROPERTY_300_NAME"];
		$goods_in_order[$arFields["ID"]]["COST"] = strlen($arFields["PROPERTY_360_VALUE"]) ? $arFields["PROPERTY_360_VALUE"] : 0;
		$goods_in_order[$arFields["ID"]]["ARTICLE"] = $arFields["PROPERTY_361_VALUE"];
		$goods_in_order[$arFields["ID"]]["WEIGHT"] = strlen($arFields["PROPERTY_362_VALUE"]) ? $arFields["PROPERTY_362_VALUE"] : 0;
		
	}
	return $goods_in_order;
}

/*********пересчет стоимости и веса заказа*********/
function RecalculationWeightAndCost($id_pack)
{
	$goods_of_pack = GetGoodsOdPack($id_pack);
	$weighs = $costs = 0;
	foreach ($goods_of_pack as $k => $v)
	{
		$c = $v["COUNT"];
		$weighs = $weighs + $v["WEIGHT"]*$c;
		$costs = $costs + $v["COST"]*$c;
		$shop_id = $v["SHOP"];
	}
	$massiv_to_change[225] = $weighs;
	$massiv_to_change[307] = $costs;
	CIBlockElement::SetPropertyValuesEx($id_pack, 42, $massiv_to_change);
}

/*********параметры магазина по умолчанию**********/
function GetDefaultValuesForShop($shop_id)
{
	$result = array('city' => '', 'city_id' => 0,'cash' => 0,'delivery' => 0);
	$db_props = CIBlockElement::GetProperty(40, $shop_id, array("sort" => "asc"), array("ID"=>310));
	if ($ar_props = $db_props->Fetch())
	{
		$result['city_id'] = $ar_props["VALUE"];
		$result['city'] = GetFullNameOfCity($ar_props["VALUE"]);
		if ($result['city_id'] > 0)
		{
			$price_id_bd = WhatIsPrice($shop_id);
			$db_props = CIBlockElement::GetProperty(51, $price_id_bd, array("sort" => "asc"), Array("CODE"=>"FILE"));
			if ($ar_props = $db_props->Fetch())
			{
				$file_value = IntVal($ar_props["VALUE"]);
			}
			else
			{
				$file_value = false;
			}
			$path_to_bd = CFile::GetPath($file_value);
			$global_file = $_SERVER["DOCUMENT_ROOT"].$path_to_bd;
			if (is_file($global_file))
			{
				$html = read_price($global_file);
				$p1 = $html["PERSENT_1"];
				$p2 = $html["PERSENT_2"];
				$result["persent_1"] = $html["ORDERS"][$result['city_id']]["Проценты"][$p1];
				$result["persent_2"] = $html["ORDERS"][$result['city_id']]["Проценты"][$p2];
			}
		}
	}
	$db_props = CIBlockElement::GetProperty(40, $shop_id, array("sort" => "asc"), Array("ID"=>311));
	if ($ar_props = $db_props->Fetch())
	{
		$result['delivery'] = $ar_props["VALUE"];
	}
	$db_props = CIBlockElement::GetProperty(40, $shop_id, array("sort" => "asc"), Array("ID"=>312));
	if($ar_props = $db_props->Fetch())
	{
		$result['cash'] = $ar_props["VALUE"];
	}
	return $result;
}

/**************************************************/
function WriteOffOfGoods($pack_id)
{
	$result = array('result' => false,'goodsLess' => array());
	$goodsLess = $changCount = $info_goods = array();
	$argoods = GetGoodsOdPack($pack_id);
	foreach ($argoods as $k => $v)
	{
		$db_props = CIBlockElement::GetProperty(62, $v['GOOD_ID'], array("sort" => "asc"), array("ID"=>299));
		if($ar_props = $db_props->Fetch())
		{
			$count_on_sk = intval($ar_props["VALUE"]);
			if ($count_on_sk < $v['COUNT'])
			{
				$goodsLess[] = $v['GOOD_ID'];
			}
			else
			{
				$changCount[$v['GOOD_ID']] = $count_on_sk - $v['COUNT'];
				$info_goods[$v['GOOD_ID']]['NAME'] = $v['NAME'];
				$info_goods[$v['GOOD_ID']]['COUNT'] = $v['COUNT'];
				$info_goods[$v['GOOD_ID']]['ARTICLE'] = $v['ARTICLE'];
				$info_goods[$v['GOOD_ID']]['WEIGHT'] = $v['WEIGHT'];
				$info_goods[$v['GOOD_ID']]['COST'] = $v['COST'];
			}
		}
		else
		{
			$goodsLess[] = $v['GOOD_ID'];
		}
	}
	if (count($goodsLess) == 0)
	{
		foreach ($changCount as $k => $v)
		{
			CIBlockElement::SetPropertyValuesEx($k, 62, array(299=>$v));
			$el = new CIBlockElement;
			$PROP = array();
			$PROP[321] = $k;
			$PROP[352] = $pack_id;
			$PROP[325] = $info_goods[$k]["COUNT"];
			$PROP[351] = 154;
			$PROP[323] = $info_goods[$k]["ARTICLE"];
			$PROP[324] = $info_goods[$k]["WEIGHT"];
			$PROP[326] = $info_goods[$k]["COST"];	  
			$arLoadProductArray = array(
				"IBLOCK_SECTION_ID" => false,
				"IBLOCK_ID" => 65,
				"NAME" => $info_goods[$k]["NAME"],
				"PROPERTY_VALUES" => $PROP,
				"ACTIVE" => "Y"
			);
			$zapis_id = $el->Add($arLoadProductArray);
		}
		$result['result'] = true;
	}
	else
	{
		$result['goodsLess'] = $goodsLess;
	}
	return $result;
}

/************порядковый номер элемента*************/
function GetIdInOfElement($id_el, $type = 'pack')
{
	switch ($type)
	{
		case 'company':
			$iblock = 40;
			$field = 304;
			break;
		default:
			$iblock = 42;
			$field = 306;
			break;
	}
	$db_props = CIBlockElement::GetProperty($iblock, $id_el, array("sort" => "asc"), array("ID"=>$field));
	if ($ar_props = $db_props->Fetch())
	{
		return $ar_props["VALUE"];
	}
	else
	{
		return false;
	}
}

/*******************номер заказа*******************/
function GetNumberOfPack($id_el)
{
	$db_props = CIBlockElement::GetProperty(42, $id_el, array("sort" => "asc"), array("ID" => 402));
	if ($ar_props = $db_props->Fetch())
	{
		return $ar_props["VALUE"];
	}
	else
	{
		return false;
	}
}

/*****************список накладных*****************/
function GetListPurchase($shop, $date_from = false, $date_to = false, $type = 0)
{
	$arFields = array();
	$select = array("ID","NAME","DATE_CREATE","PROPERTY_327","PROPERTY_317","PROPERTY_318","PROPERTY_353","PROPERTY_319.NAME","CREATED_BY","PROPERTY_370");
	$filter = array("IBLOCK_ID" => 64,"PROPERTY_316" => $shop);
	if ($date_from)
	{
		$filter[">=DATE_CREATE"] = $date_from;
	}
	if ($date_to)
	{
		$filter["<=DATE_CREATE"] = $date_to;
	}
	if ($type > 0)
	{
		$filter["PROPERTY_370"] = $type;
	}
	$on_page = intval($_GET['on_page']);
	if ($on_page < 10)
	{
		$on_page = 10;
	}
	elseif ($on_page >= 500)
	{
		$on_page = 500;
	}
	else {};
	$res = CIBlockElement::GetList(array("created"=>"desc"), $filter, false, array("nPageSize"=>$on_page), $select);
	$arFields["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject,"Приходные накладные","","Y");
	while($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$rsUser = CUser::GetByID($a['CREATED_BY']);
		$arUser = $rsUser->Fetch();
		$a['CREATED_BY_NAME'] = $arUser['LAST_NAME'].' '.$arUser['NAME'].' ['.$a['CREATED_BY'].']';
		$arFields[] =$a;
	}
	return $arFields;
}

/***************список корректировок***************/
function GetListCorrections($shop, $date_from = false, $date_to = false, $type = 175)
{
	$arFields = array();
	$select = array("ID","NAME","DATE_CREATE","PROPERTY_355","PROPERTY_357.NAME","PROPERTY_358","CREATED_BY");
	$filter = array("IBLOCK_ID"=>69,"PROPERTY_356"=>$shop,"PROPERTY_394" => $type);
	if ($date_from)
	{
		$filter[">=DATE_CREATE"] = $date_from;
	}
	if ($date_to)
	{
		$filter["<=DATE_CREATE"] = $date_to;
	}
	$on_page = intval($_GET['on_page']);
	if ($on_page < 10)
	{
		$on_page = 10;
	}
	elseif ($on_page >= 500)
	{
		$on_page = 500;
	}
	else {};
	$res = CIBlockElement::GetList(array("created" => "desc"), $filter, false, array("nPageSize" => $on_page), $select);
	$name_string_nav = ($type == 176) ? "Корректировки цен" : "Корректировки остатков";
	$arFields["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject, $name_string_nav,"","Y");
	while($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$rsUser = CUser::GetByID($a['CREATED_BY']);
		$arUser = $rsUser->Fetch();
		$a['CREATED_BY_NAME'] = $arUser['LAST_NAME'].' '.$arUser['NAME'];
		$a['CREATED_BY_ID'] = '['.$a['CREATED_BY'].']';
		$arFields[] =$a;
	}
	return $arFields;
}

/**************информация о накладной**************/
function GetOnePurchase($id, $creator = 0, $sort_goods = array("NAME" => "asc"))
{
	$arFields = array();
	$select = array("ID","NAME","DATE_CREATE","PROPERTY_327","PROPERTY_317","PROPERTY_318","PROPERTY_319.NAME","CREATED_BY","PROPERTY_316","PROPERTY_316.NAME","PROPERTY_353","PROPERTY_370");
	$filter = array("IBLOCK_ID"=>64,"ID"=>$id);
	if ($creator > 0)
	{
		$filter["PROPERTY_316"] = $creator;
	}
	$res = CIBlockElement::GetList(array("created"=>"asc"), $filter, false, false, $select);
	if($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();
		$rsUser = CUser::GetByID($arFields['CREATED_BY']);
		$arUser = $rsUser->Fetch();
		$arFields['CREATED_BY_NAME'] = $arUser['LAST_NAME'].' '.$arUser['NAME'].' ['.$arFields['CREATED_BY'].']';
		$H = intval(substr($arFields["DATE_CREATE"],11,2));
		$i = intval(substr($arFields["DATE_CREATE"],14,2));
		$s = intval(substr($arFields["DATE_CREATE"],17,2));
		$n = intval(substr($arFields["DATE_CREATE"],3,2));
		$j = intval(substr($arFields["DATE_CREATE"],0,2));
		$Y = intval(substr($arFields["DATE_CREATE"],6,4));
		$arFields["DATE_UNIX"] = mktime($H, $i, $s, $n, $j, $Y);
		$moths = array('','января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
		$arFields["DATE"] = substr($arFields["DATE_CREATE"],0,2).' '.$moths[$n].' '.$Y.'г.';
		$n_2 = intval(substr($arFields["PROPERTY_317_VALUE"],3,2));
		$Y_2 = intval(substr($arFields["PROPERTY_317_VALUE"],6,4));
		$arFields["DATE_PURCHASE"] = substr($arFields["PROPERTY_317_VALUE"],0,2).' '.$moths[$n_2].' '.$Y_2.'г.';
		$arFields["GOODS"] = array();
		$res2 = CIBlockElement::GetList(
			$sort_goods, 
			array("IBLOCK_ID" => 65,"PROPERTY_320" => $id), 
			false, 
			false, 
			array("ID","NAME","PROPERTY_321",'PROPERTY_323','PROPERTY_324','PROPERTY_325','PROPERTY_326')
		);
		while ($ob2 = $res2->GetNextElement())
		{
			$a = $ob2->GetFields();
			$db_props = CIBlockElement::GetProperty(62, $a["PROPERTY_321_VALUE"], array("sort" => "asc"), array("ID"=>299));
			$ar_props = $db_props->Fetch();
			$a["COUNT_NOW"] = intval($ar_props["VALUE"]);
			$arFields["GOODS"][] = $a;
		}
		return $arFields;
	}
	else
	{
		return false;
	}
}

/************информация о корректировке************/
function GetOneCorrection($id)
{
	$arFields = array();
	$select = array("ID","NAME","DATE_CREATE","PROPERTY_355","PROPERTY_357.NAME","CREATED_BY","PROPERTY_356","PROPERTY_356.NAME",'DETAIL_TEXT',"PROPERTY_394","PROPERTY_358","PROPERTY_468");
	$filter = array("IBLOCK_ID"=>69,"ID"=>$id);
	$res = CIBlockElement::GetList(array("created"=>"asc"), $filter, false, false, $select);
	if ($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();
		$rsUser = CUser::GetByID($arFields['CREATED_BY']);
		$arUser = $rsUser->Fetch();
		$arFields['CREATED_BY_NAME'] = $arUser['LAST_NAME'].' '.$arUser['NAME'];
		$arFields['CREATED_BY_ID'] = '['.$arFields['CREATED_BY'].']';
		$H = intval(substr($arFields["DATE_CREATE"],11,2));
		$i = intval(substr($arFields["DATE_CREATE"],14,2));
		$s = intval(substr($arFields["DATE_CREATE"],17,2));
		$n = intval(substr($arFields["DATE_CREATE"],3,2));
		$j = intval(substr($arFields["DATE_CREATE"],0,2));
		$Y = intval(substr($arFields["DATE_CREATE"],6,4));
		$arFields["DATE_UNIX"] = mktime($H, $i, $s, $n, $j, $Y);
		$moths = array('','января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
		$arFields["DATE"] = substr($arFields["DATE_CREATE"],0,2).' '.$moths[$n].' '.$Y.'г.';
		$arFields["GOODS"] = array();
		if ($arFields["PROPERTY_394_ENUM_ID"] == 176)
		{
			$res2 = CIBlockElement::GetList(
				array("ID" => "asc"), 
				array("IBLOCK_ID" => 74,"PROPERTY_395" => $id), 
				false, 
				false, 
				array("ID","NAME","PROPERTY_396","PROPERTY_397","PROPERTY_398","PROPERTY_399","PROPERTY_400","PROPERTY_401")
			);
			while ($ob2 = $res2->GetNextElement())
			{
				$a = $ob2->GetFields();
				$arFields["GOODS"][] = $a;
			}
		}
		else
		{
			$res2 = CIBlockElement::GetList(
				array("ID" => "asc"), 
				array("IBLOCK_ID" => 65,"PROPERTY_354" => $id), 
				false, 
				false, 
				array("ID","NAME","PROPERTY_321",'PROPERTY_323','PROPERTY_324','PROPERTY_325','PROPERTY_326','PROPERTY_351')
			);
			while ($ob2 = $res2->GetNextElement())
			{
				$a = $ob2->GetFields();
				$db_props = CIBlockElement::GetProperty(62, $a["PROPERTY_321_VALUE"], array("sort" => "asc"), array("ID"=>299));
				$ar_props = $db_props->Fetch();
				$a["COUNT_NOW"] = intval($ar_props["VALUE"]);
				$a['OPERATION'][$a['PROPERTY_351_ENUM_ID']] = $a['PROPERTY_325_VALUE'];
				$arFields["GOODS"][] = $a;
			}
		}
		return $arFields;
	}
	else
	{
		return false;
	}
}

/********************список пвз********************/
function TheListOfPVZ($agent = 0, $city = 0, $all = true, $id_pvz = 0, $use_navigation = true, $other = 0)
{
	$arFields = array();
	$select = array("ID","NAME","DATE_CREATE","PROPERTY_AGENT","PROPERTY_CITY","PROPERTY_ADRESS","PROPERTY_PHONE","PROPERTY_CITY.NAME","PROPERTY_AGENT.NAME","ACTIVE","CODE","PROPERTY_ID_IN", 'PROPERTY_CITY');
	$filter = array("IBLOCK_ID" => 66);
	if ($agent > 0)
	{
		$db_props = CIBlockElement::GetProperty(40, $agent, array("NAME" => "asc"), Array("CODE" => "TYPE"));
		if($ar_props = $db_props->Fetch())
		{
			$type_agent = $ar_props["VALUE"];
		}
		if ($type_agent == 51)
		{
			$arAgIds = array();
			$arAgIds[] = $agent;
			$arAgents = AvailableAgents(true, $agent);
			foreach ($arAgents as $k => $v)
			{
				$arAgIds[] = $k;
			}
			$filter["PROPERTY_AGENT"] = $arAgIds;
		}
		elseif ($type_agent == 52)
		{
			$db_props = CIBlockElement::GetProperty(40, $agent, array("NAME" => "asc"), Array("CODE" => "UK"));
			if($ar_props = $db_props->Fetch())
			{
				$uk_shop = $ar_props["VALUE"];
				$arAgIds = array();
				$arAgIds[] = $uk_shop;
				$arAgents = AvailableAgents(true, $uk_shop);
				foreach ($arAgents as $k => $v)
				{
					$arAgIds[] = $k;
				}
				$filter["PROPERTY_AGENT"] = $arAgIds;
			}
			else
			{
				return $arFields;
			}
		}
		elseif ($type_agent == 53)
		{
			$filter["PROPERTY_AGENT"] = $agent;
		}
		else
		{
			return $arFields;
		}
	}
	if ($city > 0)
	{
		$filter["PROPERTY_CITY"] = $city;
	}
	if (!$all)
	{
		$filter["ACTIVE"] = "Y";
	}
	if ($id_pvz > 0)
	{
		$filter["ID"] = $id_pvz;
	}
	if ($other > 0)
	{
		$filter["!ID"] = $other;
	}
	if ($use_navigation)
	{
		if (isset($_GET['on_page']))
		{
			$on_page = intval($_GET['on_page']);
		}
		else
		{
			if (isset($_SESSION['ON_PAGE_GLOBAL']))
			{
				$on_page = intval($_SESSION['ON_PAGE_GLOBAL']);
			}
			else
			{
				$on_page = 10;
			}
		}
		if ($on_page < 10)
		{
			$on_page = 10;
		}
		if ($on_page >= 200)
		{
			$on_page = 200;
		}
		$postran = array("nPageSize" => $on_page);
	}
	else
	{
		$postran = false;
	}
	$res = CIBlockElement::GetList(array("PROPERTY_CITY.NAME"=>"asc"), $filter, false, $postran, $select);
	if ($use_navigation)
	{
		$arFields["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject,"ПВЗ","","Y");
	}
	while($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$a["CITY_NAME"] = GetFullNameOfCity($a['PROPERTY_CITY_VALUE']);
		$arFields[] =$a;
	}
	return $arFields;
}

/*******************заказы в пвз*******************/
function TheListPacksOfPVZ($pvz, $use_navigation = true)
{
	if ($use_navigation)
	{
		if (isset($_GET['on_page']))
		{
			$on_page = intval($_GET['on_page']);
		}
		else
		{
			if (isset($_SESSION['ON_PAGE_GLOBAL']))
			{
				$on_page = intval($_SESSION['ON_PAGE_GLOBAL']);
			}
			else
			{
				$on_page = 10;
			}
		}
		if ($on_page < 10)
		{
			$on_page = 10;
		}
		if ($on_page >= 200)
		{
			$on_page = 200;
		}
		$postran = array("nPageSize" => $on_page);
	}
	else
	{
		$postran = false;
	}

	$select = array("ID","NAME","PROPERTY_N_ZAKAZ","PROPERTY_RECIPIENT","PROPERTY_PHONE","PROPERTY_ADRESS","PROPERTY_COST_1","PROPERTY_COST_2","PROPERTY_COST_3","PROPERTY_COST_4","PROPERTY_STATE",
		"PROPERTY_COURIER",'PROPERTY_CONDITIONS',"PROPERTY_COURIER.NAME","PROPERTY_CITY.NAME","PROPERTY_CITY","PROPERTY_CREATOR","PROPERTY_CREATOR.NAME","DATE_CREATE","PROPERTY_MANIFEST",
		"PROPERTY_SUMM","PROPERTY_WEIGHT","PROPERTY_STATE_SHORT","PROPERTY_RATE","PROPERTY_PLACES","PROPERTY_SIZE_1","PROPERTY_SIZE_2","PROPERTY_SIZE_3","PROPERTY_SUMM_SHOP","PROPERTY_SUMM_AGENT",
		"PROPERTY_RATE_AGENT","PROPERTY_DATE_DELIVERY","CREATED_BY","PROPERTY_COST_GOODS","PROPERTY_CASH","PROPERTY_ID_IN","PROPERTY_PVZ", 'PROPERTY_N_ZAKAZ_IN'
	);
	$filter = array("IBLOCK_ID" => 42, "PROPERTY_PVZ" => $pvz, "PROPERTY_STATE" => 79);
	$res = CIBlockElement::GetList(array("ID"=>"desc"), $filter, false, $postran, $select);
	if ($use_navigation)
	{
		$arFields["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject,"Заказы","","Y");
	}
	while($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$a["CITY_NAME"] = GetFullNameOfCity($a['PROPERTY_CITY_VALUE']);
		$arFields[] =$a;
	}
	return $arFields;
}

function GetSummReportsShopLight($shop = false, $agent_array, $id_report = 0, $use_navigation = false, $payment = true, $signed = false, $sort = array("ID"=>"desc"), $date_from = false, $date_to = false)
{
	$arFields = array();
	$select = array("ID","NAME","PROPERTY_ID_IN","PROPERTY_DATE","PROPERTY_PAYMENT","PROPERTY_STORAGE","PROPERTY_START","PROPERTY_END","PROPERTY_SIGNED", 'PROPERTY_481', "PROPERTY_482", "PROPERTY_483", "PROPERTY_484", "PROPERTY_485", "PROPERTY_486", "PROPERTY_487", "PROPERTY_488", 'PROPERTY_SHOP', 'PROPERTY_SHOP.NAME','PROPERTY_CONFIRMED', 'CREATED_BY');
	$filter = array("IBLOCK_ID" => 67);
	if ($shop > 0)
	{
		$filter['PROPERTY_SHOP'] = $shop;
	}
	if (strlen($date_from))
	{
		$date_from = $date_from.' 00:00:00';
		$filter[">=DATE_CREATE"] = $date_from;
	}
	if (strlen($date_to))
	{
		$date_to = $date_to.' 23:59:59';
		$filter["<=DATE_CREATE"] = $date_to;
	}
	if ($id_report > 0)
	{
		$filter["ID"] = $id_report;
	}
	if (!$payment)
	{
		$filter['PROPERTY_PAYMENT'] = false;
	}
	else
	{
		if ($payment === 'Y')
		{
			$filter['!PROPERTY_PAYMENT'] = false;
		}
	}
	if ($signed)
	{
		$filter['PROPERTY_SIGNED'] = 173;
	}
	$res = CIBlockElement::GetList($sort, $filter, false, false, $select);
	$ITOGO_COST = 0;
	while($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$ITOGO_COST = $ITOGO_COST + $a['PROPERTY_488_VALUE'];

	}
	return $ITOGO_COST;
}

function GetListOfReportsShopLight($shop = false, $agent_array, $id_report = 0, $use_navigation = true, $payment = true, $signed = false, $sort = array("ID"=>"desc"), $date_from = false, $date_to = false)
{
	$arFields = array();
	$select = array("ID","NAME","PROPERTY_ID_IN","PROPERTY_DATE","PROPERTY_PAYMENT","PROPERTY_STORAGE","PROPERTY_START","PROPERTY_END","PROPERTY_SIGNED", 'PROPERTY_481', "PROPERTY_482", "PROPERTY_483", "PROPERTY_484", "PROPERTY_485", "PROPERTY_486", "PROPERTY_487", "PROPERTY_488", 'PROPERTY_SHOP', 'PROPERTY_SHOP.NAME','PROPERTY_CONFIRMED', 'CREATED_BY');
	$filter = array("IBLOCK_ID" => 67);
	if ($shop > 0)
	{
		$filter['PROPERTY_SHOP'] = $shop;
	}
	if (strlen($date_from))
	{
		$date_from = $date_from.' 00:00:00';
		$filter[">=DATE_CREATE"] = $date_from;
	}
	if (strlen($date_to))
	{
		$date_to = $date_to.' 23:59:59';
		$filter["<=DATE_CREATE"] = $date_to;
	}
	if ($id_report > 0)
	{
		$filter["ID"] = $id_report;
		$postran = false;
	}
	else
	{
		if ($use_navigation)
		{
			if (isset($_GET['on_page']))
			{
				$on_page = intval($_GET['on_page']);
			}
			else
			{
				if (isset($_SESSION['ON_PAGE_GLOBAL']))
				{
					$on_page = intval($_SESSION['ON_PAGE_GLOBAL']);
				}
				else
				{
					$on_page = 10;
				}
			}
			if ($on_page < 10)
			{
				$on_page = 10;
			}
			if ($on_page >= 200)
			{
				$on_page = 200;
			}
			$postran = array("nPageSize" => $on_page);
		}
		else
		{
			$postran = false;
		}
	}
	if (!$payment)
	{
		$filter['PROPERTY_PAYMENT'] = false;
	}
	else
	{
		if ($payment === 'Y')
		{
			$filter['!PROPERTY_PAYMENT'] = false;
		}
	}
	if ($signed)
	{
		$filter['PROPERTY_SIGNED'] = 173;
	}
	$res = CIBlockElement::GetList($sort, $filter, false, $postran, $select);
	if ($use_navigation)
	{
		$arFields["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject, "Отчеты", "", "Y");
	}
	while($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$a["COST_2"] = $a['PROPERTY_483_VALUE'];
		$a["SUMM_SHOP"] = $a['PROPERTY_484_VALUE'];
		$a["RATE"] = $a['PROPERTY_485_VALUE'];
		$a['REQVS_COST'] = $a['PROPERTY_486_VALUE'];
		$a['SUMM_FORMATION'] = $a['PROPERTY_487_VALUE'];
		$a['ITOGO_COST'] = $a['PROPERTY_488_VALUE'];
		$a["PERIOD"] = $a['PROPERTY_481_VALUE'].' - '.$a['PROPERTY_482_VALUE'];
		$a["SUMM_AGENT"] = $a["SUMM_SHOP"] + $a["RATE"] + $a['REQVS_COST'] + $a['SUMM_FORMATION'];
		$rsUser = CUser::GetByID($a['CREATED_BY']);
		$arUser = $rsUser->Fetch();
		$a['CREATED_BY_NAME'] = $arUser['LAST_NAME'].' '.$arUser['NAME'];
		$arFields[] = $a;
	}
	return $arFields;
}

function GetOneReport($id_report, $type = 'shop')
{
	$id_report = intval($id_report);
	$res_0 = CIBlockElement::GetByID($id_report);
	if ($ar_res = $res_0->GetNext())
	{
		$arReport = array();
		$filter = array("IBLOCK_ID" => 42);
		if ($type == 'shop')
		{
			$filter['PROPERTY_REPORT'] = $id_report;
		}
		elseif ($type == 'agent')
		{
			$filter['PROPERTY_REPORT_AGENT'] = $id_report;
		}
		else
		{
			return false;
		}
		$res = CIBlockElement::GetList(
			array('PROPERTY_DATE_DELIVERY' => 'asc'),
			$filter, 
			false, 
			false,
			array('ID', 'PROPERTY_N_ZAKAZ_IN', 'PROPERTY_N_ZAKAZ','PROPERTY_COST_2', 'PROPERTY_SUMM_SHOP', 'PROPERTY_RATE', 'PROPERTY_SUMM_ISSUE','PROPERTY_OBTAINED', 'PROPERTY_SUMM_AGENT', 'PROPERTY_RATE_AGENT', 'PROPERTY_COST_RETURN', 'PROPERTY_DATE_DELIVERY', 'PROPERTY_EXCEPTIONAL_SITUATION', 'PROPERTY_RETURN', 'PROPERTY_CONDITIONS', 'PROPERTY_WEIGHT', 'PROPERTY_COST_GOODS', 'PROPERTY_COST_3', 'PROPERTY_TYPE_PAYMENT', 'PROPERTY_CITY.NAME', 'PROPERTY_SUMM_ISSUE', 'PROPERTY_SIZE_1', 'PROPERTY_SIZE_2', 'PROPERTY_SIZE_3')
		);
		while($ob = $res->GetNextElement())
		{
			$a = $ob->GetFields();
			$a['V_WEIGHT'] = ($a['PROPERTY_SIZE_1_VALUE']*$a['PROPERTY_SIZE_2_VALUE']*$a['PROPERTY_SIZE_3_VALUE'])/5000;
			$arReport['PACKS'][$a['ID']] = $a;
			$arReport['COST_2'] = $arReport['COST_2'] + $a['PROPERTY_COST_2_VALUE'];
			$arReport['OBTAINED'] = $arReport['OBTAINED'] + $a['PROPERTY_OBTAINED_VALUE'];
			if ($a['PROPERTY_CONDITIONS_ENUM_ID'] == 37)
			{
				$arReport['SUMM_SHOP'] = $arReport['SUMM_SHOP'] + $a['PROPERTY_SUMM_SHOP_VALUE'] + $a['PROPERTY_COST_RETURN_VALUE'];
			}
			else
			{
				$arReport['SUMM_ISSUE'] = $arReport['SUMM_ISSUE'] + $a['PROPERTY_SUMM_SHOP_VALUE'] + $a['PROPERTY_COST_RETURN_VALUE'];
			}
			
			$arReport['RATE'] = $arReport['RATE'] + $a['PROPERTY_RATE_VALUE'];
			$arReport['SUMM_FORMATION'] = $arReport['SUMM_FORMATION'] + $a['PROPERTY_SUMM_ISSUE_VALUE'];
		}
		$arReport['SUMM_SHOP_AND_ISSUE'] = $arReport['SUMM_SHOP'] + $arReport['SUMM_ISSUE'];
		$arReport["REQVS_COST"] = 0;
		$arReport["REQVS"] = GetListRequests($shop,false,true,array("ID"=>"ASC"),0,186, $id_report);
		foreach ($arReport["REQVS"] as $r)
		{
			$arReport["REQVS_COST"] = $arReport["REQVS_COST"] + $r["PROPERTY_COST_VALUE"];
		}
		$res_2 = CIBlockElement::GetList(
			array('ID' => 'desc'),
			array('IBLOCK_ID' => 67, 'ID' => $id_report), 
			false, 
			array('nTopCount' => 1),
			array('PROPERTY_DATE', 'PROPERTY_PAYMENT', 'PROPERTY_STORAGE', 'PROPERTY_START', 'PROPERTY_END', 'PROPERTY_SIGNED', 'PROPERTY_CONFIRMED', 'PROPERTY_DATE_REPORT_FROM', 'PROPERTY_DATE_REPORT_TO', 'PROPERTY_ID_IN')
		);
		$ob_2 = $res_2->GetNextElement();
		$arReport['INFO'] = $ob_2->GetFields();
		$moths = array('','января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
		$min = mktime(11, 0, 0, substr($arReport['INFO']['PROPERTY_DATE_REPORT_FROM_VALUE'],3,2), substr($arReport['INFO']['PROPERTY_DATE_REPORT_FROM_VALUE'],0,2), substr($arReport['INFO']['PROPERTY_DATE_REPORT_FROM_VALUE'],6,4));
		$max = mktime(11, 0, 0, substr($arReport['INFO']['PROPERTY_DATE_REPORT_TO_VALUE'],3,2), substr($arReport['INFO']['PROPERTY_DATE_REPORT_TO_VALUE'],0,2), substr($arReport['INFO']['PROPERTY_DATE_REPORT_TO_VALUE'],6,4));
		$arReport['INFO']["START_DATE"] = '&laquo;'.date('d',$min).'&raquo; '.$moths[date('n',$min)].' '.date('Y',$min).' г.';
		$arReport['INFO']["END_DATE"] = '&laquo;'.date('d',$max).'&raquo; '.$moths[date('n',$max)].' '.date('Y',$max).' г.';
		$arReport['INFO']["PERIOD"] = date('d.m.Y',$min).' - '.date('d.m.Y',$max);
		$arReport['INFO']["PERIOD_1"] = date('d.m.Y',$min);
		$arReport['INFO']["PERIOD_2"] = date('d.m.Y',$max);
		$arReport['INFO']["DATE_FORMATED"] = '&laquo;'.substr($arReport['INFO']['PROPERTY_DATE_VALUE'],0,2).'&raquo; '.$moths[intval(substr($arReport['INFO']['PROPERTY_DATE_VALUE'],3,2))].' '.substr($arReport['INFO']['PROPERTY_DATE_VALUE'],6,4).' г.';
		$arReport['TO_SHOP'] = $arReport['OBTAINED'] - $arReport['SUMM_SHOP_AND_ISSUE'] - $arReport['RATE'] - $arReport["SUMM_FORMATION"] - $arReport["REQVS_COST"] - $arReport['INFO']['PROPERTY_STORAGE_VALUE'];
		$arReport['SUMM_AGENT'] = $arReport['SUMM_SHOP_AND_ISSUE'] + $arReport['RATE'] + $arReport["SUMM_FORMATION"] + $arReport["REQVS_COST"] + $arReport['INFO']['PROPERTY_STORAGE_VALUE'];
		return $arReport;
	}
	else
	{
		return false;
	}
}

/*********список отчетов интернет-магазина*********/
function GetListOfReportsShop($shop, $agent_array, $id_report = 0, $nav = true, $payment = true, $signed = false)
{
	$arFields = array();
	$select = array("ID","NAME","PROPERTY_ID_IN","PROPERTY_DATE","PROPERTY_PAYMENT","PROPERTY_STORAGE","PROPERTY_START","PROPERTY_END","PROPERTY_SIGNED", 'PROPERTY_CONFIRMED', 'PROPERTY_DATE_REPORT_FROM', 'PROPERTY_DATE_REPORT_TO');
	$filter = array("IBLOCK_ID" => 67, "PROPERTY_SHOP" => $shop);
	if ($id_report > 0)
	{
		$filter["ID"] = $id_report;
		$nav_array = false;
	}
	else
	{
		if ($nav)
		{
			$on_page = intval($_GET['on_page']);
			if ($on_page < 10)
			{
				$on_page = 10;
			}
			elseif ($on_page >= 500)
			{
				$on_page = 500;
			}
			else {};
			$nav_array = array("nPageSize"=>$on_page);
		}
		else
		{
			$nav_array = false;
		}
	}
	if (!$payment)
	{
		$filter['PROPERTY_PAYMENT'] = false;
	}
	if ($signed)
	{
		$filter['PROPERTY_SIGNED'] = 173;
	}
	$res = CIBlockElement::GetList(array("ID"=>"desc"), $filter, false, $nav_array, $select);
	if (is_array($nav_array))
	{
		$arFields["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject, "Отчеты", "", "Y");
	}
	while($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$packs = GetListOfPackeges($agent_array, $shop, false, 0, false, 0, false, 0, 0,'','',0, false, true, array("PROPERTY_DATE_DELIVERY"=>"ASC"), $a["ID"]);
		$a["COST_2"] = $a["RATE"] = $a["SUMM_SHOP"] = $a["SUMM_ISSUE"] = $a["COST_2_OB"] = $a['SUMM_FORMATION'] = 0;
		$dates = array();
		foreach ($packs as $pp)
		{
			$a["PACKS"][$pp['ID']]["N_ZAKAZ"] = $pp['PROPERTY_N_ZAKAZ_IN_VALUE'];
			$a["PACKS"][$pp['ID']]["N_ZAKAZ_SHOP"] = $pp['PROPERTY_N_ZAKAZ_VALUE'];
			$a["PACKS"][$pp['ID']]["COST_2"] = $pp['PROPERTY_COST_2_VALUE'];
			$a["PACKS"][$pp['ID']]["COST_3"] = $pp['PROPERTY_COST_3_VALUE'];
			$a["PACKS"][$pp['ID']]["RATE"] = $pp['PROPERTY_RATE_VALUE'];
			$a["PACKS"][$pp['ID']]["SUMM_SHOP"] = $pp['PROPERTY_SUMM_SHOP_VALUE'];
			$a["PACKS"][$pp['ID']]["SUMM_ISSUE"] = $pp['PROPERTY_SUMM_ISSUE_VALUE'];
			$a["PACKS"][$pp['ID']]["CONDITIONS"] = $pp['PROPERTY_CONDITIONS_VALUE'];
			$a["PACKS"][$pp['ID']]["CITY"] = $pp['PROPERTY_CITY_NAME'];
			$a["PACKS"][$pp['ID']]["CITY_ID"] = $pp['PROPERTY_CITY_VALUE'];
			$a["PACKS"][$pp['ID']]["CONDITIONS_ID"] = $pp['PROPERTY_CONDITIONS_ENUM_ID'];
			$a["PACKS"][$pp['ID']]["DATE_DELIVERY"] = substr($pp['PROPERTY_DATE_DELIVERY_VALUE'],0,10);
			$a["PACKS"][$pp['ID']]["WEIGHT"] = $pp['PROPERTY_WEIGHT_VALUE'];
			$a["PACKS"][$pp['ID']]["COST_GOODS"] = $pp['PROPERTY_COST_GOODS_VALUE'];
			$a["PACKS"][$pp['ID']]["TYPE_PAYMENT"] = $pp['PROPERTY_TYPE_PAYMENT_VALUE'];
			$a["PACKS"][$pp['ID']]["EXCEPTIONAL"] = $pp["PROPERTY_EXCEPTIONAL_SITUATION_VALUE"];
			$a["PACKS"][$pp['ID']]["RETURN"] = $pp["PROPERTY_RETURN_VALUE"];
			$a["PACKS"][$pp['ID']]["COST_RETURN"] = strlen($pp["PROPERTY_COST_RETURN_VALUE"]) ? $pp["PROPERTY_COST_RETURN_VALUE"] : 0;
			$a['PACKS'][$pp['ID']]['COST_POLUCHENO'] =  $pp['PROPERTY_OBTAINED_VALUE'];
			if ($pp['PROPERTY_URGENCY_ORDER_ENUM_ID'] == 172)
			{
				if ($pp['PROPERTY_CONDITIONS_ENUM_ID'] == 37)
				{
					$a["PACKS"][$pp['ID']]["CONDITIONS"] .= ', дв. тариф';
				}
				if ($pp['PROPERTY_CONDITIONS_ENUM_ID'] == 38)
				{
					$a["PACKS"][$pp['ID']]["CONDITIONS"] .= ' срочный';
				}
			}
			/*
			if ((intval($pp["PROPERTY_EXCEPTIONAL_SITUATION_VALUE"]) != 1) && (intval($pp["PROPERTY_RETURN_VALUE"]) != 1)) 
			{
				$a["COST_2"] = $a["COST_2"] + $pp['PROPERTY_COST_2_VALUE'];
				$a["RATE"] = $a["RATE"] + $pp['PROPERTY_RATE_VALUE'];
			}
			else
			{
				if ($pp['ID'] == 6078334)
				{
					$a["COST_2"] = $a["COST_2"] + 500;
					$a["RATE"] = $a["RATE"] + $pp['PROPERTY_RATE_VALUE'];
				}
			}
			*/
			
			if ((intval($pp["PROPERTY_EXCEPTIONAL_SITUATION_VALUE"]) != 1) && (intval($pp["PROPERTY_RETURN_VALUE"]) != 1)) 
			{
				$a["RATE"] = $a["RATE"] + $pp['PROPERTY_RATE_VALUE'];
			}
			else
			{
				if ($pp['ID'] == 6078334)
				{
					$a["RATE"] = $a["RATE"] + $pp['PROPERTY_RATE_VALUE'];
				}
			}
			
			
			$a["COST_2"] = $a["COST_2"] + $pp['PROPERTY_OBTAINED_VALUE'];
			$a["COST_2_OB"] = $a["COST_2_OB"] + $pp['PROPERTY_COST_2_VALUE'];
			$a['SUMM_FORMATION'] = $a['SUMM_FORMATION'] + $pp['PROPERTY_SUMM_ISSUE_VALUE'];
			if ($a["PACKS"][$pp['ID']]["CONDITIONS_ID"] == 37)
			{
				$a["SUMM_SHOP"] = $a["SUMM_SHOP"] + $pp['PROPERTY_SUMM_SHOP_VALUE'];
				if (intval($pp["PROPERTY_RETURN_VALUE"]) == 1)
				{
					$a["SUMM_SHOP"] = $a["SUMM_SHOP"] + $pp['PROPERTY_COST_RETURN_VALUE'];
				}
			}
			else
			{
				if (($pp["PROPERTY_EXCEPTIONAL_SITUATION_VALUE"] == 1) || (($pp['PROPERTY_CITY_VALUE'] == 8054) && ($pp["PROPERTY_RETURN_VALUE"] == 1)))
				{
				}
				else
				{
					$a["SUMM_ISSUE"] = $a["SUMM_ISSUE"] + $pp['PROPERTY_SUMM_SHOP_VALUE'];
				}
				if (intval($pp["PROPERTY_RETURN_VALUE"]) == 1)
				{
					$a["SUMM_ISSUE"] = $a["SUMM_ISSUE"] + $pp['PROPERTY_COST_RETURN_VALUE'];
				}
			}
			/*
			if (strlen($pp["PROPERTY_DATE_DELIVERY_VALUE"]))
			{
				$H = intval(substr($pp["PROPERTY_DATE_DELIVERY_VALUE"],11,2));
				$i = intval(substr($pp["PROPERTY_DATE_DELIVERY_VALUE"],14,2));
				$s = intval(substr($pp["PROPERTY_DATE_DELIVERY_VALUE"],17,2));
				$n = intval(substr($pp["PROPERTY_DATE_DELIVERY_VALUE"],3,2));
				$j = intval(substr($pp["PROPERTY_DATE_DELIVERY_VALUE"],0,2));
				$Y = intval(substr($pp["PROPERTY_DATE_DELIVERY_VALUE"],6,4));
				$dates[] = mktime($H, $i, $s, $n, $j, $Y);
			}
			*/
		}
		$a["REQVS"] = GetListRequests($shop,false,true,array("ID"=>"ASC"),0,186,$a["ID"]);
		$a["REQVS_COST"] = 0;
		foreach ($a["REQVS"] as $r)
		{
			$a["REQVS_COST"] = $a["REQVS_COST"] + $r["PROPERTY_COST_VALUE"];
			/*
			if (strlen($r["PROPERTY_DATE_VALUE"]))
			{
				$n = intval(substr($r["PROPERTY_DATE_VALUE"],3,2));
				$j = intval(substr($r["PROPERTY_DATE_VALUE"],0,2));
				$Y = intval(substr($r["PROPERTY_DATE_VALUE"],6,4));
				$dates[] = mktime(0, 0, 0, $n, $j, $Y);
			}*/
		}
		$moths = array('','января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
		/*
		$min = min($dates);
		$max = max($dates);
		*/
		$min = mktime(11, 0, 0, substr($a['PROPERTY_DATE_REPORT_FROM_VALUE'],3,2), substr($a['PROPERTY_DATE_REPORT_FROM_VALUE'],0,2), substr($a['PROPERTY_DATE_REPORT_FROM_VALUE'],6,4));
		$max = mktime(11, 0, 0, substr($a['PROPERTY_DATE_REPORT_TO_VALUE'],3,2), substr($a['PROPERTY_DATE_REPORT_TO_VALUE'],0,2), substr($a['PROPERTY_DATE_REPORT_TO_VALUE'],6,4));
		$a["START_DATE"] = '&laquo;'.date('d',$min).'&raquo; '.$moths[date('n',$min)].' '.date('Y',$min).' г.';
		$a["END_DATE"] = '&laquo;'.date('d',$max).'&raquo; '.$moths[date('n',$max)].' '.date('Y',$max).' г.';
		$a["PERIOD"] = date('d.m.Y',$min).' - '.date('d.m.Y',$max);
		$a["PERIOD_1"] = date('d.m.Y',$min);
		$a["PERIOD_2"] = date('d.m.Y',$max);
		$a["DATE_FORMATED"] = '&laquo;'.substr($a['PROPERTY_DATE_VALUE'],0,2).'&raquo; '.$moths[intval(substr($a['PROPERTY_DATE_VALUE'],3,2))].' '.substr($a['PROPERTY_DATE_VALUE'],6,4).' г.';
		$arFields[] = $a;
	}
	if ($id_report > 0)
	{
		return $arFields[0];
	}
	else
	{
		return $arFields;
	}
}

/*************склонение сумм в рублях**************/
function num2str($num)
{
    $nul='ноль';
    $ten=array(
        array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
        array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
    );
    $a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
    $tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
    $hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
    $unit=array(
        array('копейка' ,'копейки' ,'копеек',	 1),
        array('рубль'   ,'рубля'   ,'рублей'    ,0),
        array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
        array('миллион' ,'миллиона','миллионов' ,0),
        array('миллиард','милиарда','миллиардов',0),
    );
    list($rub,$kop) = explode('.',sprintf("%015.2f", floatval($num)));
    $out = array();
	$out[] = '(';
    if (intval($rub)>0)
	{
        foreach(str_split($rub,3) as $uk=>$v)
		{
            if (!intval($v))
			{
				continue;
			}
            $uk = sizeof($unit)-$uk-1;
            $gender = $unit[$uk][3];
            list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
            $out[] = $hundred[$i1];
            if ($i2>1)
			{
				$out[]= $tens[$i2].' '.$ten[$gender][$i3];
			}
            else
			{
				$out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3];
			}
            if ($uk>1)
			{
				$out[]= morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
			}
        }
    }
    else
	{
		$out[] = $nul;
	}
	$out[] = ')';
    $out[] = morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]);
    $out[] = $kop.' '.morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]);
	$str = trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
	$str = substr_replace($str, '', 1, 1);
	$ind = strpos($str,")")-1;
	$str = substr_replace($str, '', $ind, 1);
    return intval($rub).' '.$str;
}

/*************склонение сумм в рублях**************/
function morph($n, $f1, $f2, $f5)
{
    $n = abs(intval($n)) % 100;
    if ($n>10 && $n<20)
	{
		return $f5;
	}
    $n = $n % 10;
    if ($n>1 && $n<5)
	{
		return $f2;
	}
    if ($n==1)
	{
		return $f1;
	}
    return $f5;
}

/*************товары интернет-магазина*************/
function GetGoodsOfShop($shop)
{
	$res_array = array();
	$filter = array("IBLOCK_ID" => 62,"PROPERTY_295" => $shop);
	$select = array("ID","NAME","PROPERTY_294","PROPERTY_299","PROPERTY_297","PROPERTY_296");
	$res = CIBlockElement::GetList(array("NAME" => "asc"), $filter, false, false, $select);
	while($ob = $res->GetNextElement())
	{
		$res_array[] = $ob->GetFields();
	}
	return $res_array;
}

/*******************список ролей*******************/
function GetListRoles($id = 0)
{
	$filter = array("IBLOCK_ID"=>70,"ACTIVE"=>"Y");
	if ($id > 0)
	{
		$ar = false;
		$filter["ID"] = $id;
	}
	else
	{
		$ar = array();
	}
	$res = CIBlockElement::GetList(array("NAME"=>"asc"), $filter, false, false, array("ID","NAME","PROPERTY_363","PROPERTY_364","PROPERTY_365","PROPERTY_366"));
	while($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();
		$ar[$arFields["ID"]]['NAME'] = $arFields["NAME"];
		$ar[$arFields["ID"]]['FOR'] = $arFields["PROPERTY_363_VALUE"];
		$ar[$arFields["ID"]]['ROLES'][159] = $arFields["PROPERTY_364_VALUE"];
		$ar[$arFields["ID"]]['ROLES'][160] = $arFields["PROPERTY_365_VALUE"];
		$ar[$arFields["ID"]]['ROLES'][161] = $arFields["PROPERTY_366_VALUE"];
	}
	return $ar;
}

/***************формат номера заказа***************/
function NZakaz($n)
{
	if (!strpos($n,'-'))
	{
		$l = strlen($n);
		if ($l > 3)
		{
			$count = floor($l/3);
			$p = $l%3;
			$m = '';
			for ($i=0; $i < $count; $i++)
			{
				$m .= substr($n,($i*3),3);
				if (($count-1) > $i)
				{
					$m .= '-';
				}
			}
			if ($p > 0)
			{
				$m .= '-'.substr($n,($count*3),$p);
			}
			return $m;
		}
		else
		{
			return $n;
		}
	}
	else
	{
		return $n;
	}
}

/*****топ-значение порядкового номера элемента*****/
function GetMaxIDIN($ib, $syms = 5, $onlyforthisagent = false, $value_id = 0, $agent = 0)
{
	$max_id = 0;
	$filter = array("IBLOCK_ID"=>$ib);
	if (($onlyforthisagent) && (intval($agent) > 0)  && (intval($value_id) > 0))
	{
		$filter['PROPERTY_'.$value_id] = $agent;
	}
	$res = CIBlockElement::GetList(array("ID"=>"desc"), $filter, false, array("nTopCount"=>1), array("ID", "PROPERTY_ID_IN"));
	if ($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();
		$max_id = intval($arFields["PROPERTY_ID_IN_VALUE"]);
	}
	$max_id++;
	$max_id_n = str_pad($max_id,$syms,'0',STR_PAD_LEFT);
	return $max_id_n;
}

/************формирование номера заказа************/
function MakeOrderId($agent, $max = 0)
{
	$info_of_shop = GetAgentInfo($agent);
	$name_of_order = $info_of_shop["PROPERTY_ID_IN_VALUE"];
	if (intval($max) > 0)
	{
		$max_id_5 = $max;
	}
	else
	{
		$max_id_5 = GetMaxIDIN(42,5,true,213,$agent);
	}
	$name_of_order = $name_of_order.$max_id_5;
	$summ_ch = 0;
	for($i=0;$i<strlen($name_of_order);$i++)
	{
		$summ_ch = $summ_ch + intval($name_of_order[$i]);
	}
	$last_ch = $summ_ch%10;
	$name_of_order = $name_of_order.$last_ch;
	return nZakaz($name_of_order);
}

function MakeInvoiceNumber($iblock, $syms, $prefix = '')
{
	$out = array();
	$out['max_id'] = 1;
	$res = CIBlockElement::GetList(array("ID"=>"desc"), array("IBLOCK_ID"=>$iblock), false, array("nTopCount"=>1), array("ID", "PROPERTY_ID_IN"));
	if ($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();
		$out['max_id'] = intval($arFields["PROPERTY_ID_IN_VALUE"]) + 11;
	}
	$out['number'] = $prefix.str_pad($out['max_id'],$syms,'0',STR_PAD_LEFT);
	return $out;
}

function MakeInvoiceNumberNew($iblock, $syms, $prefix = '')
{
	$out = array();
	if (CModule::IncludeModule('highloadblock'))
	{
		$arHLBlock = Bitrix\Highloadblock\HighloadBlockTable::getById($iblock)->fetch();
		$obEntity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($arHLBlock);
		$strEntityDataClass = $obEntity->getDataClass();
		$arElementFields = array(
			'UF_USED' => 'N',
			'UF_NUMBER' => 'Not number'
		);
		$obResult = $strEntityDataClass::add($arElementFields);
		$ID = $obResult->getID();
		if ($bSuccess = $obResult->isSuccess())
		{
			$out['max_id'] = 407144 + (11)*$ID;
			$out['number'] = $prefix.str_pad($out['max_id'],$syms,'0',STR_PAD_LEFT);
			$arUpdateFields = array(
				'UF_USED' => 'Y',
				'UF_NUMBER' => $out['number']
			);
			$obResultUp = $strEntityDataClass::update($ID, $arUpdateFields);
			if ($bSuccessUp = $obResultUp->isSuccess())
			{
				return $out;
			}
			else 
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	else
	{
		return false;
	}
}

/*******формирование номера заявки на забор********/
function MakePickUpId($agent, $max = 0)
{
	$info_of_shop = GetAgentInfo($agent);
	$name_of_order = $info_of_shop["PROPERTY_ID_IN_VALUE"];
	if (intval($max) > 0)
	{
		$max_id_5 = $max;
	}
	else
	{
		$max_id_5 = GetMaxIDIN(76,5,true,419,$agent);
	}
	$name_of_order = $name_of_order.$max_id_5;
	$summ_ch = 0;
	for($i=0;$i<strlen($name_of_order);$i++)
	{
		$summ_ch = $summ_ch + intval($name_of_order[$i]);
	}
	$last_ch = $summ_ch%10;
	$name_of_order = $name_of_order.$last_ch;
	return 'P-'.nZakaz($name_of_order);
}

/**********формирование номера манифеста***********/
function MakeManifestId($agent, $agent_from, $max = 0)
{
	$db_props = CIBlockElement::GetProperty(40, $agent_from, array("sort" => "asc"), Array("ID"=>377));
	if ($ar_props = $db_props->Fetch())
	{
		$prefix = $ar_props["VALUE"];
	}
	else
	{
		$prefix = '';
	}
	if (intval($max) > 0)
	{
		$max_id_5 = $max;
	}
	else
	{
		$max_id_5 = GetMaxIDIN(41,5,true,194,$agent);
	}
	$name_of_order = $prefix.$max_id_5;
	$summ_ch = 0;
	for($i=0;$i<strlen($max_id_5);$i++)
	{
		$summ_ch = $summ_ch + intval($max_id_5[$i]);
	}
	$last_ch = $summ_ch%10;
	$name_of_order .= $last_ch;
	return $name_of_order;
}

/*******************формат даты********************/
function DateFF($d, $time = false)
{
	$H = intval(substr($d,11,2));
	$i = intval(substr($d,14,2));
	$s = intval(substr($d,17,2));
	$n = intval(substr($d,3,2));
	$j = intval(substr($d,0,2));
	$Y = intval(substr($d,6,4));
	$date_unix = mktime($H, $i, $s, $n, $j, $Y);
	$moths = array('','января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
	$date_ff = $j.' '.$moths[$n].' '.$Y.' г.';
	if ($time)
	{
		$date_ff .= '&nbsp;'.$H.':'.str_pad($i,2,'0',STR_PAD_LEFT);
	}
	return $date_ff;
}

/*********разбор даты в текстовом формате**********/
function DateFFReverse($string)
{
	$result = array();
	$moths = array(
		'января' => '01',
		'февраля' => '02',
		'марта' => '03',
		'апреля' => '04',
		'мая' => '05',
		'июня' => '06',
		'июля' => '07',
		'августа' => '08',
		'сентября' => '09',
		'октября' => '10',
		'ноября' => '11',
		'декабря' => '12');
	$st_array = explode(' ',$string);
	$c = count($st_array);
	switch ($c)
	{
		case 2:
			$result['date'] = '';
			if ($st_array[0] == 'c')
			{
				$result['time_1'] = $st_array[1];
				$result['time_2'] = '';
			}
			else
			{
				$result['time_1'] = '';
				$result['time_2'] = $st_array[1];
			}
			break;
		case 4:
			if ($st_array[0] == 'с')
			{
				$result['date'] = '';
				$result['time_1'] = $st_array[1];
				$result['time_2'] = $st_array[3];
			}
			else
			{
				$result['date'] = str_pad($st_array[0],2,'0',STR_PAD_LEFT).'.'.$moths[$st_array[1]].'.'.$st_array[2];
				$result['time_1'] = $result['time_2'] = '';
			}
			break;
		case 6:
			$result['date'] = str_pad($st_array[0],2,'0',STR_PAD_LEFT).'.'.$moths[$st_array[1]].'.'.$st_array[2];
			if ($st_array[4] == 'c')
			{
				$result['time_1'] = $st_array[5];
				$result['time_2'] = '';
			}
			else
			{
				$result['time_1'] = '';
				$result['time_2'] = $st_array[5];
			}
			break;
		case 8:
			$result['date'] = str_pad($st_array[0],2,'0',STR_PAD_LEFT).'.'.$moths[$st_array[1]].'.'.$st_array[2];
			$result['time_1'] = $st_array[5];
			$result['time_2'] = $st_array[7];
			break;
		default:
			$result['date'] = $result['time_1'] = $result['time_2'] = '';
		
	}
	return $result;
}

/*****************отчет агента pdf*****************/
function MakeReportPDF($arLang, $save = 'F')
{
	$pdf = new PDF_MC_Table();
	$pdf->AddFont('ArialMT','','arialTM.php');
	$pdf->SetFont('ArialMT','',20);
	$margin = (strlen($_GET['top'])) ? intval($_GET['top']) : 5;
	if (strlen($_GET['bottom']))
	{
		$pdf->SetAutoPageBreak(true, intval($_GET['bottom']));
	}
	$pdf->SetTopMargin($margin);
	$pdf->AddPage('L');
	$pdf->SetFillColor(255,255,255);
	$pdf->SetFontSize(6);
	$pdf->SetWidths(array(278));
	$data = array(array('value'=> $arLang[0],'align'=>'R'));
	$pdf->Row($data, false, true);
	$data = array(array('value'=> $arLang[1],'align'=>'R'));
	$pdf->Row($data, false, true);
	$pdf->SetFontSize(9);
	$data = array(array('value'=> ToUpper($arLang[2]),'align'=>'C'));
	$pdf->Row($data, true, true);
	$pdf->SetFontSize(6);
	$data = array(htmlspecialcharsBack($arLang[3]));
	$pdf->Row($data, false, true);
	$data = array(array('value'=> $arLang[4],'align'=>'R'));
	$pdf->Row($data, false, true);
	$pdf->SetFontSize(7);
	$data = array($arLang[5]);
	$pdf->Row($data, false, true);
	$pdf->SetFontSize(6);
	$cols = count($arLang[6]);
	$width = floor(278/$cols);
	$width2 = 278 - $width*($cols - 1);
	$arWidths = array();
	foreach ($arLang[6] as $k => $v)
	{
		if ($k == 1)
		{
			$arWidths[$k] = $width2;
		}
		else
		{
			$arWidths[$k] = $width;
		}
	}
	$ost_1 = floor(($arWidths[0] - 5)/2);
	$ost_2 = floor(($arWidths[6] - 12)/2);
	$ost_3 = floor(($arWidths[0] - 5)/3);
	$arWidths[8] = $arWidths[8] + $ost_1;
	$arWidths[5] = $arWidths[5] + $ost_2;
	$arWidths[12] = $arWidths[12] + $ost_3;
	$arWidths[13] = $arWidths[13] + $ost_3;
	$arWidths[14] = $arWidths[14] + $ost_3;
	$arWidths[0] = 5;
	$arWidths[1] = 18;
	$arWidths[6] = 12;
	$ost = 278;
	foreach ($arWidths as $v)
	{
		$ost = $ost - $v;	
	}
	$arWidths[5] = $arWidths[5] + $ost;
	$pdf->SetWidths($arWidths);
	$pdf->Row($arLang[6], true, false);
	foreach ($arLang[7] as $data)
	{
		$pdf->Row($data, false, false);
	}
	$arWidthsItogo = array();
	$index = 0;
	foreach ($arWidths as $k => $v)
	{
		if ($k <=10)
		{
			$arWidthsItogo[$index] = $arWidthsItogo[$index] + $v;
		}
		else
		{
			$index++;
			$arWidthsItogo[$index] = $v;
		}
	}
	$pdf->SetWidths($arWidthsItogo);
	$pdf->Row($arLang[8], false, false);
	$pdf->Ln(5);
	if ($_GET['page'] == 'Y')
	{
		$pdf->AddPage('L');
	}
	if ($arLang[15])
	{
		$pdf->Write(4, $arLang[15]['title']);
		$pdf->Ln();
		$pdf->SetWidths(array(8, 35, 35, 60, 35, 35, 35, 35));
		foreach ($arLang[15]['table'] as $data)
		{
			$pdf->Row($data, false, false);
		}
		$pdf->SetWidths(array(243, 35));
		$pdf->Row($arLang[15]['footer'], false, false);
		$pdf->Ln(5);
	}
	foreach ($arLang[9] as $st)
	{
		$pdf->Write(4, $st);
		$pdf->Ln();
	}
	$pdf->SetFontSize(9);
	$pdf->SetWidths(array(278));
	$data = array(array('value' => ToUpper($arLang[10]),'align'=>'C'));
	$pdf->Row($data, true, true);
	$pdf->SetFontSize(7);
	$pdf->SetWidths(array(69, 70, 69, 70));
	$pdf->Row($arLang[11], false, true, 6);
	$pdf->Row($arLang[12], false, true, 6);
	$pdf->Row($arLang[13], false, true, 6);
	$pdf->Row($arLang[14], false, true, 6);
	if ($save == 'F')
	{
		$pdf->Output($_SERVER['DOCUMENT_ROOT'].'/warehouse/packs_pdf/'.$arLang[16],'F');
		return $_SERVER['DOCUMENT_ROOT'].'/warehouse/packs_pdf/'.$arLang[16];
	}
	if ($save == 'D')
	{
		return $pdf->Output($arLang[16],'D');
	}
}

function MakeRegisterReportsPDF($arShop, $arReports, $arLang, $save = 'D')
{
	$pdf = new PDF_MC_Table();
	$pdf->AddFont('ArialMT','','arialTM.php');
	$pdf->SetFont('ArialMT','',11);
	$margin = (intval($_GET['top']) > 0) ? intval($_GET['top']) : 5;
	$pdf->SetTopMargin($margin);
	$pdf->AddPage('L');
	$pdf->SetFillColor(255,255,255);
	$pdf->SetWidths(array(120,155));
	$data = array(date('d.m.Y H:i'), array('value'=> $arShop['PROPERTY_LEGAL_NAME_FULL_VALUE'],'align'=>'R'));
	$pdf->Row($data,false,true);
	$pdf->Ln();
	$pdf->SetFontSize(8);
	$pdf->SetWidths(array(7,26,26,27,27,27,36,27,27,27,18));
	$pdf->Row($arLang['TABLE_HEAD'], true);
	foreach ($arReports as $r)
	{
		$st = $r["PROPERTY_STORAGE_VALUE"];
		$st .= ($r["PROPERTY_STORAGE_VALUE"] > 0) ? "\n".$r['PROPERTY_START_VALUE'].' - '.$r['PROPERTY_END_VALUE'] : '';
		$i++;
		$pdf->Row(array(
			$i,
			$r["PROPERTY_DATE_VALUE"],
			$r["PERIOD"],
			$r["COST_2"],
			$r["SUMM_SHOP"] + $r['SUMM_ISSUE'],
			$r["RATE"],
			$st,
			$r["REQVS_COST"],
			$r["SUMM_FORMATION"],
			$r['ITOGO_COST'],
			substr($r["PROPERTY_PAYMENT_VALUE"],0,10)
		));
	}
	if ($save == 'F')
	{
		$pdf->Output($_SERVER['DOCUMENT_ROOT'].'/warehouse/packs_pdf/'.$arLang['NAME_FILE'].'.pdf','F');
		return $_SERVER['DOCUMENT_ROOT'].'/warehouse/packs_pdf/'.$arLang['NAME_FILE'].'.pdf';
	}
	if ($save == 'D')
	{
		return $pdf->Output($arLang['NAME_FILE'].'.pdf','D');
	}
}

function MakeRegisterPDF($arLang, $save = 'D', $group_shops = false)
{
	$pdf = new PDF_MC_Table();
	$pdf->AddFont('ArialMT','','arialTM.php');
	$pdf->SetFont('ArialMT','',11);
	$margin = (intval($_GET['margin']) > 0) ? intval($_GET['margin']) : 5;
	$pdf->SetTopMargin($margin);
	$pdf->AddPage('P');
	$pdf->SetFillColor(255,255,255);
	$pdf->SetWidths(array(110,80));
	$data = array($arLang['DATE'], array('value'=> $arLang['SHOP'],'align'=>'R'));
	$pdf->Row($data,false,true);
	$pdf->Ln();
	if ($group_shops)
	{
		foreach ($arLang['SHOPS'] as $shop)
		{
			$pdf->SetFontSize(10);
			$pdf->SetWidths(array(190));
			$data = array(array('value' => $shop['NAME'], 'align' => 'R'));
			$pdf->Row($data, false, true);
			$i = 0;
			$pdf->SetFontSize(8);
			$pdf->SetWidths(array(10,60,60,60));
			$pdf->Row($arLang["FIELDS"],true);
			foreach ($shop['PACKS'] as $p)
			{
				$i++;
				$ob_w = ($p['PROPERTY_SIZE_1_VALUE']*$p['PROPERTY_SIZE_2_VALUE']*$p['PROPERTY_SIZE_3_VALUE'])/5000;
				$w =  WeightFormat($p['PROPERTY_WEIGHT_VALUE']);
				$w .= ($ob_w > $p['PROPERTY_WEIGHT_VALUE']) ? ' (объемн. '.WeightFormat($ob_w).')' : '';
				$pdf->Row(array($i, $p['PROPERTY_N_ZAKAZ_IN_VALUE'], $w, CurrencyFormat($p['PROPERTY_COST_2_VALUE'],'RUU')));
			}
			$pdf->Ln();
		}
	}
	else
	{
		$i = 0;
		$pdf->SetFontSize(8);
		$pdf->SetWidths(array(10,60,60,60));
		$pdf->Row($arLang["FIELDS"],true);
		foreach ($arLang["LIST"] as $p)
		{
			$i++;
			$ob_w = ($p['PROPERTY_SIZE_1_VALUE']*$p['PROPERTY_SIZE_2_VALUE']*$p['PROPERTY_SIZE_3_VALUE'])/5000;
			$w =  WeightFormat($p['PROPERTY_WEIGHT_VALUE']);
			$w .= ($ob_w > $p['PROPERTY_WEIGHT_VALUE']) ? ' (объемн. '.WeightFormat($ob_w).')' : '';
			$pdf->Row(array($i, $p['PROPERTY_N_ZAKAZ_IN_VALUE'], $w, CurrencyFormat($p['PROPERTY_COST_2_VALUE'],'RUU')));
		}
	}
	if ($save == 'F')
	{
		$pdf->Output($_SERVER['DOCUMENT_ROOT'].'/warehouse/packs_pdf/'.$arLang['NAME_FILE'].'.pdf','F');
		return $_SERVER['DOCUMENT_ROOT'].'/warehouse/packs_pdf/'.$arLang['NAME_FILE'].'.pdf';
	}
	if ($save == 'D')
	{
		return $pdf->Output($arLang['NAME_FILE'].'.pdf','D');
	}
}

/***************конвертация кавычек****************/
function convertDates($date)
{
	$date = str_replace('&laquo;','«', $date);
	$date = str_replace('&raquo;','»', $date);
	$date = str_replace('&quot;','"', $date);
	return $date;
}

/***************квитанция заказа pdf***************/
function MakeTicketPDF($pack_array, $save = 'F')
{
	$pdf = new PDF_MC_Table();
	$pdf->AddFont('ArialMT','','arialTM.php');
	$pdf->SetFont('ArialMT','',20);
	$pdf->AddPage('P');
	$adr = ($pack_array['PROPERTY_CONDITIONS_ENUM_ID'] == 37) ? $pack_array['PROPERTY_ADRESS_VALUE'] : $pack_array['PROPERTY_CONDITIONS_VALUE'];
	$comment = (is_array($pack_array['PROPERTY_COMMENTS_COURIER_VALUE'])) ? htmlspecialcharsBack($pack_array['PROPERTY_COMMENTS_COURIER_VALUE']['TEXT']) : '';
	$pdf->Code39(115, 8, $pack_array["PROPERTY_N_ZAKAZ_IN_VALUE"], true, false, 0.3, 10);
	$pdf->SetFillColor(255, 255, 255);
	$pdf->SetLineWidth(0.05);
	$pdf->SetFontSize(12);
	$pdf->Write(5,'Квитанция к заказу №'.$pack_array["PROPERTY_N_ZAKAZ_IN_VALUE"].' от '.substr($pack_array["DATE_CREATE"],0,10));
	$pdf->Ln(5);
	$pdf->SetFontSize(7);
	$pdf->SetWidths(array(95,95));
	$of_name_of_shop = strlen($pack_array["SHOP"]["PROPERTY_LEGAL_NAME_VALUE"]) ? $pack_array["SHOP"]["PROPERTY_LEGAL_NAME_VALUE"] : $pack_array["SHOP"]["PROPERTY_LEGAL_NAME_FULL_VALUE"];
	$of_name_of_uk = ($pack_array["SHOP"]["PROPERTY_TYPE_IM_ENUM_ID"] == 211) ? htmlspecialcharsBack($pack_array["SHOP"]["PROPERTY_UK_NAME"]) : '';
	$data = array(htmlspecialcharsBack($of_name_of_shop), array('value' => $of_name_of_uk,'align'=>'R'));
	$pdf->Row($data,false,true);
	$pdf->Ln(2);	
	$pdf->Line(10,20,200,20);
	$pdf->SetFontSize(12);
	$pdf->SetWidths(array(30,100,60));
	$opl = strlen($pack_array["PROPERTY_TYPE_PAYMENT_VALUE"]) ? $pack_array["PROPERTY_TYPE_PAYMENT_VALUE"] : 'Наличные';
	$data = array('Получатель:',htmlspecialcharsBack($pack_array["PROPERTY_RECIPIENT_VALUE"]),array('value'=>'Доставка: '.$pack_array["PROPERTY_CONDITIONS_VALUE"],'align'=>'R'));
	$pdf->Row($data,false,true);
	$data  = array('Адрес:',htmlspecialcharsBack($pack_array["PROPERTY_CITY_NAME"].', '.$adr),array('value'=>'Тип оплаты: '.$opl,'align'=>'R'));
	$pdf->Row($data,false,true);
	$data = array('Телефон:',htmlspecialcharsBack($pack_array["PROPERTY_PHONE_VALUE"]),'');
	$pdf->Row($data,false,true);
	if ((strlen($pack_array['PROPERTY_WHEN_TO_DELIVER_VALUE'])) || (strlen($pack_array['PROPERTY_PREFERRED_TIME_VALUE']["TEXT"])))
	{
		$pdf->Ln(2);
		$pdf->SetFontSize(8);
		$pdf->SetWidths(array(190));
	}	
	if(strlen($pack_array['PROPERTY_WHEN_TO_DELIVER_VALUE']))
	{
		$data = array('Доставить: '.htmlspecialcharsBack($pack_array['PROPERTY_WHEN_TO_DELIVER_VALUE']));
		$pdf->Row($data,false,true);
	}
	if(strlen($pack_array['PROPERTY_PREFERRED_TIME_VALUE']["TEXT"]))
	{
		$data = array('Комментарий к заказу: '.htmlspecialcharsBack($pack_array['PROPERTY_PREFERRED_TIME_VALUE']["TEXT"]));
		$pdf->Row($data,false,true);
	} 
	$pdf->Ln(2);
	$pdf->SetFontSize(6);
	$txt = 'Стоимость доставки';
	$txt .= ($pack_array['PROPERTY_PAY_FOR_REFUSAL_VALUE'] == 1) ? '*:' : ':';
	if (count($pack_array['GOODS']) > 0) 
	{
		$pdf->SetWidths(array(60,25,25,25,25,30));
		$data = array('Наименование','Артикул','Цена','Вес','Количество','Стоимость');
		$pdf->Row($data,true);
		$count_g = 0;
		foreach ($pack_array['GOODS'] as $goods)
		{
			$data = array(htmlspecialcharsBack($goods['NAME']),htmlspecialcharsBack($goods['ARTICLE']),CurrencyFormat($goods['COST'],"RUU"),$goods['WEIGHT'].' кг',$goods['COUNT'],CurrencyFormat(($goods['COST']*$goods['COUNT']),"RUU"));
			$pdf->Row($data);
			$count_g = $count_g + $goods['COUNT'];
		}
		$pdf->SetWidths(array(110,25,25,30));
		$data = array('Итого предметов и их стоимости:',str_replace('.',',',$pack_array['PROPERTY_WEIGHT_VALUE']).' кг',$count_g,CurrencyFormat($pack_array['PROPERTY_COST_GOODS_VALUE'],"RUU"));
		$pdf->Row($data);
		$pdf->SetWidths(array(160,30));
		$data = array($txt,CurrencyFormat($pack_array['PROPERTY_COST_3_VALUE'],"RUU"));
		$pdf->Row($data);
		$data = array('Сумма к оплате:',CurrencyFormat($pack_array['PROPERTY_COST_2_VALUE'],"RUU"));
		$pdf->Row($data);
	}
	elseif ((count($pack_array['PACK_GOODS']) > 0) && (is_array($pack_array['PACK_GOODS'])))
	{
		$pdf->SetWidths(array(60,20,35,25,25,25));
		$data = array('Наименование','Количество','Цена за 1 шт., включая НДС','Сумма, включая НДС','Сумма НДС','Ставка НДС');
		$pdf->Row($data,true);
		foreach ($pack_array['PACK_GOODS'] as $goods)
		{
			$data = array(
				htmlspecialcharsBack($goods['GoodsName']),
				$goods['Amount'].' шт.',
				CurrencyFormat($goods['Price'],"RUU"),
				CurrencyFormat($goods['Sum'],"RUU"),
				CurrencyFormat($goods['SumNDS'],"RUU"),
				$goods['PersentNDS'].'%'
			);
			$pdf->Row($data);
		}
		$pdf->Ln(2);
		$pdf->SetWidths(array(160,30));
		$data = array('Вес:',str_replace('.',',',$pack_array['PROPERTY_WEIGHT_VALUE']).' кг');
		$pdf->Row($data);
		$data = array('Стоимость заказа:',CurrencyFormat($pack_array['PROPERTY_COST_GOODS_VALUE'],"RUU"));
		$pdf->Row($data);
		$data = array($txt,CurrencyFormat($pack_array['PROPERTY_COST_3_VALUE'],"RUU"));
		$pdf->Row($data);
		$data = array('Сумма к оплате:',CurrencyFormat($pack_array['PROPERTY_COST_2_VALUE'],"RUU"));
		$pdf->Row($data);
	}
	else
	{
		$pdf->SetWidths(array(160,30));
		$data = array('Вес:',str_replace('.',',',$pack_array['PROPERTY_WEIGHT_VALUE']).' кг');
		$pdf->Row($data);
		$data = array('Стоимость заказа:',CurrencyFormat($pack_array['PROPERTY_COST_GOODS_VALUE'],"RUU"));
		$pdf->Row($data);
		$data = array($txt,CurrencyFormat($pack_array['PROPERTY_COST_3_VALUE'],"RUU"));
		$pdf->Row($data);
		$data = array('Сумма к оплате:',CurrencyFormat($pack_array['PROPERTY_COST_2_VALUE'],"RUU"));
		$pdf->Row($data);
	}
	if (count($pack_array["ZABORS"]) > 0)
	{
		$pdf->Ln(2);
		$pdf->SetWidths(array(110,25,25,30));
		$data = array('Наименование','Артикул','Вес','Количество');
		$pdf->Row($data,true);
		foreach ($pack_array["ZABORS"] as $z)
		{
			$data = array(htmlspecialcharsBack($z['NAME']),htmlspecialcharsBack($z['PROPERTY_431_VALUE']),WeightFormat($z['PROPERTY_430_VALUE']),$z['PROPERTY_432_VALUE']);
			$pdf->Row($data);
		}
	}
	$pdf->Ln(1);
	$pdf->SetFontSize(7);	
	if ($pack_array['PROPERTY_PAY_FOR_REFUSAL_VALUE'] == 1)
	{
		$txt = '* Стоимость доставки взимается независимо от принятия заказа получателем.';
		$pdf->Write(4,$txt);
		
	}
	$pdf->Ln(5);
	$txt = 'Заказ принял, комплектность полная, услуги по доставке оказаны, претензий по количеству, ассортименту, упаковке и внешнему виду товара не имею.';
	$pdf->Write(4,$txt);
	$pdf->Ln();
	
	$pdf->SetDrawColor(220, 220, 220);
	$txt = 'С условиями возврата товара ознакомлен';
	$pdf->Write(4,$txt);
	$x = $pdf->GetX() + 2;
	$y = $pdf->GetY();
	for ($i = 1; $i <= 24; $i++)
	{
		$pdf->Rect($x,$y,4,4);
		$x += 5;
	}
	$pdf->SetX($x);
	$txt = '(получатель).';
	$pdf->Write(4,$txt);
	$pdf->Ln();

	/*
	$txt = 'С условиями возврата товара ознакомлен ___________________ (получатель).';
	$pdf->Write(4,$txt);
	$pdf->Ln();
	*/
	
	$pdf->SetDrawColor(0, 0, 0);
	$txt = 'Дата ___________________ Время ___________________';
	$pdf->Write(4,$txt);
	$pdf->Ln(6);
	$pdf->Write(
		4,
		'_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _'
	);
	$pdf->Ln(10);
	$pdf->SetFontSize(17);
	$pdf->SetWidths(array(80,110));
	$data = array(htmlspecialcharsBack($arResult["AGENT_NAME"]),array('value'=>'Товарный чек №'.$pack_array["PROPERTY_N_ZAKAZ_IN_VALUE"],'align'=>'R'));
	$pdf->Row($data,false,true);
	$pdf->SetFontSize(7);
	$to_ho = 'ПОСТУПЛЕНИЕ ВЫРУЧКИ ';
	$to_ho .= ($pack_array["SHOP"]["PROPERTY_TYPE_IM_ENUM_ID"] == 211) ? $pack_array["SHOP"]["PROPERTY_UK_NAME"].' ПО АГЕНТСКОМУ ДОГОВОРУ' : mb_strtoupper($of_name_of_shop, 'windows-1251');
	$data = array('',array('value' =>$to_ho,'align'=>'R'));
	$pdf->Row($data,false,true);
	$pdf->Ln(5);
	$pdf->SetFontSize(10);
	$pdf->Write(5,'Дата "_____" ___________________ 20_____ г.');
	$pdf->Ln(8);
	$pdf->SetFontSize(6);
	if (count($pack_array['GOODS']) > 0)
	{
		$pdf->SetWidths(array(60,25,25,25,25,30));
		$data = array('Наименование','Артикул','Цена','Вес','Количество','Стоимость');
		$pdf->Row($data,true);
		$count_g = 0;
		foreach ($pack_array['GOODS'] as $goods)
		{
			$data = array(
				htmlspecialcharsBack($goods['NAME']),
				htmlspecialcharsBack($goods['ARTICLE']),
				CurrencyFormat($goods['COST'],"RUU"),
				$goods['WEIGHT'].' кг',
				$goods['COUNT'],
				CurrencyFormat(($goods['COST']*$goods['COUNT']),"RUU")
			);
			$pdf->Row($data);
			$count_g = $count_g + $goods['COUNT'];
		}
		$pdf->SetWidths(array(110,25,25,30));
		$data = array('Итого предметов и их стоимости:',str_replace('.',',',$pack_array['PROPERTY_WEIGHT_VALUE']).' кг',$count_g,CurrencyFormat($pack_array['PROPERTY_COST_GOODS_VALUE'],"RUU"));
		$pdf->Row($data);
		$pdf->SetWidths(array(160,30));
		$data = array('Стоимость доставки:', CurrencyFormat($pack_array['PROPERTY_COST_3_VALUE'],"RUU"));
		$pdf->Row($data);
		$data = array('Сумма к оплате:', CurrencyFormat($pack_array['PROPERTY_COST_2_VALUE'], "RUU"));
		$pdf->Row($data);
	}
	elseif ((count($pack_array['PACK_GOODS']) > 0) && (is_array($pack_array['PACK_GOODS'])))
	{
		$pdf->SetWidths(array(60,20,35,25,25,25));
		$data = array('Наименование','Количество','Цена за 1 шт., включая НДС','Сумма, включая НДС','Сумма НДС','Ставка НДС');
		$pdf->Row($data,true);
		foreach ($pack_array['PACK_GOODS'] as $goods)
		{
			$data = array(
				htmlspecialcharsBack($goods['GoodsName']),
				$goods['Amount'].' шт.',
				CurrencyFormat($goods['Price'],"RUU"),
				CurrencyFormat($goods['Sum'],"RUU"),
				CurrencyFormat($goods['SumNDS'],"RUU"),
				$goods['PersentNDS'].'%'
			);
			$pdf->Row($data);
		}
		$pdf->Ln(2);
		$pdf->SetWidths(array(160,30));
		$data = array('Вес:',str_replace('.',',',$pack_array['PROPERTY_WEIGHT_VALUE']).' кг');
		$pdf->Row($data);
		$data = array('Стоимость заказа:',CurrencyFormat($pack_array['PROPERTY_COST_GOODS_VALUE'],"RUU"));
		$pdf->Row($data);
		$data = array($txt,CurrencyFormat($pack_array['PROPERTY_COST_3_VALUE'],"RUU"));
		$pdf->Row($data);
		$data = array('Сумма к оплате:',CurrencyFormat($pack_array['PROPERTY_COST_2_VALUE'],"RUU"));
		$pdf->Row($data);
	}
	else
	{
		$pdf->SetWidths(array(160,30));
		$data = array('Вес:',str_replace('.',',',$pack_array['PROPERTY_WEIGHT_VALUE']).' кг');
		$pdf->Row($data);
		$data = array('Стоимость заказа:', CurrencyFormat($pack_array['PROPERTY_COST_GOODS_VALUE'],"RUU"));
		$pdf->Row($data);
		$data = array('Стоимость доставки:', CurrencyFormat($pack_array['PROPERTY_COST_3_VALUE'],"RUU"));
		$pdf->Row($data);
		$data = array('Сумма к оплате:', CurrencyFormat($pack_array['PROPERTY_COST_2_VALUE'],"RUU"));
		$pdf->Row($data);
	}
	if (count($pack_array["ZABORS"]) > 0)
	{
		$pdf->Ln(2);
		$pdf->SetWidths(array(110,25,25,30));
		$data = array('Наименование','Артикул','Вес','Количество');
		$pdf->Row($data,true);
		foreach ($pack_array["ZABORS"] as $z)
		{
			$data = array(htmlspecialcharsBack($z['NAME']), htmlspecialcharsBack($z['PROPERTY_431_VALUE']), WeightFormat($z['PROPERTY_430_VALUE']),$z['PROPERTY_432_VALUE']);
			$pdf->Row($data);
		}
	}
	$pdf->Ln();
	$pdf->SetFontSize(10);
	$pdf->Write(5,'Итого: '.num2str($pack_array['PROPERTY_COST_2_VALUE']));
	$pdf->Ln();
	$pdf->Write(5,'Подпись продавца ___________________');
	$pdf->Ln(8);
	$pdf->SetFontSize(5);
	$txts = array(
		'Информация для покупателей:',
		'Продавец: '.htmlspecialcharsBack($of_name_of_shop).', местонахождение: '.htmlspecialcharsBack($pack_array["SHOP"]["PROPERTY_ADRESS_VALUE"]),
		'При получении товара Покупатель должен проверить соответствие полученного товара заказанному, а также произвести внешний осмотр товара на предмет выявления механических повреждений и других видимых дефектов. Претензии к количеству, комплектности товара и внешним дефектам, заявленные после передачи товара Покупателю, удовлетворению не подлежат.',
		'Информация для физических лиц: Покупатель вправе отказаться от товара в любое время до его передачи, а после передачи товара — в течение 7 дней. Возврат товара надлежащего качества возможен в случае, если сохранены его товарный вид, потребительские свойства, а также документ, подтверждающий факт и условия покупки указанного товара.',
		'При обнаружении следов эксплуатации товара Продавец оставляет за собой право отказать в приеме товара.',
		'Покупатель не вправе отказаться от товара надлежащего качества, имеющего индивидуально-определенные свойства, если указанный товар может быть использован исключительно приобретающим его потребителем. B случае отказа покупателя от товара надлежащего качества, уплаченная им сумма, за исключением расходов продавца на доставку от покупателя возвращенного товара, подлежит возврату покупателю на основании его письменного заявления по форме, представленной на сайте www.dms.гu, не позднее чем через 10 дней c даты предъявления покупателем соответствующего требования и возврата товара.',
		'Информируем, что в утвержденный Постановлением Правительства РФ № 55 от 19 января 1998 г. перечень непродовольственных товаров надлежащего качества, не подлежащих возврату или обмену на аналогичный товар других размера, формы, габарита, фасона, расцветки или комплектации, входят:',
		'- Непериодические издания (книги, брошюры, альбомы, картографические и нотные издания, листовые издания, календари, буклеты, издания, воспроизведенные на технических носителях информации);',
		'- Технически сложные товары бытового назначения, на которые установлены гарантийные сроки;',
		'- Парфюмерно- косметические товары;',
		'- Швейные и трикотажные изделия (изделия швейные и трикотажные бельевые, изделия чулочно-носочные).',
		'При обнаружении недостатков товара в процессе его использования Покупатель вправе предъявить претензии в отношении недостатков товара, если они обнаружены в течение гарантийного срока или срока годности. B отношении товаров, на которые гарантийные сроки или сроки годности не установлены, Покупатель вправе предъявить указанные требования, если недостатки товаров обнаружены в разумный срок, но в пределах двух лет со дня передачи их Покупателю.',
		'Возврат товара и претензии к товару принимаются только при наличии кассового и товарного чеков.'
	);
	$pdf->SetWidths(array(190));
	foreach ($txts as $txt)
	{
		$pdf->Write(3,$txt);
		$pdf->Ln();
	}
	$pdf->Ln();
	$pdf->Write(5,'Заказ №'.nZakaz($pack_array["PROPERTY_N_ZAKAZ_IN_VALUE"]).' от '.substr($pack_array["DATE_CREATE"],0,10));
	$pdf->Ln(5);				
	$name_of_file = $pack_array["PROPERTY_N_ZAKAZ_IN_VALUE"].'.pdf';
	if ($save == 'F')
	{
		$pdf->Output($_SERVER['DOCUMENT_ROOT'].'/warehouse/packs_pdf/'.$name_of_file,'F');
		return $_SERVER['DOCUMENT_ROOT'].'/warehouse/packs_pdf/'.$name_of_file;
	}
	if ($save == 'D')
	{
		return $pdf->Output($pack_array["PROPERTY_N_ZAKAZ_IN_VALUE"].'.pdf','D');
	}
}

/***************заявка на забор pdf****************/
function MakeSupplierTicketPDF($pack_array,$save = 'F')
{
	$pdf = new PDF_MC_Table();
	$pdf->AddFont('ArialMT','','arialTM.php');
	$pdf->SetFillColor(255,255,255);
	$pdf->AddPage('P');
	$pdf->SetFont('ArialMT','',20);
	$adr = ($pack_array['PROPERTY_CONDITIONS_ENUM_ID'] == 37)? $pack_array['PROPERTY_ADRESS_VALUE'] : $pack_array['PROPERTY_CONDITIONS_VALUE'];
	$comment = (is_array($pack_array['PROPERTY_COMMENTS_COURIER_VALUE'])) ? htmlspecialcharsBack($pack_array['PROPERTY_COMMENTS_COURIER_VALUE']['TEXT']) : '';
	$pdf->SetFontSize(12);
	$pdf->Write(5,'Заявка на забор у поставщика заказа №'.$pack_array["PROPERTY_N_ZAKAZ_IN_VALUE"].' от '.substr($pack_array["DATE_CREATE"],0,10));
	$pdf->Ln(5);
	$pdf->SetFontSize(7);
	$pdf->SetWidths(array(95,95));
	$data = array(htmlspecialcharsBack($pack_array["SHOP"]["PROPERTY_LEGAL_NAME_VALUE"]), array('value'=>htmlspecialcharsBack($pack_array["AGENT_NAME"]),'align'=>'R'));
	$pdf->Row($data,false,true);
	$pdf->Ln(2);
	$pdf->Line(10,20,200,20);
	$pdf->SetFontSize(12);
	$pdf->SetWidths(array(45,145));
	$data  = array('Магазин:',htmlspecialcharsBack($pack_array["SHOP"]["NAME"]));
	$pdf->Row($data,false,true);
	$data = array('Забрать:',htmlspecialcharsBack($pack_array["PROPERTY_TAKE_DATE_VALUE"]));
	$pdf->Row($data,false,true);
	$data = array('Получатель:',htmlspecialcharsBack($pack_array["PROPERTY_RECIPIENT_VALUE"]));
	$pdf->Row($data,false,true);
	if(strlen($pack_array['PROPERTY_TAKE_COMMENT_VALUE']["TEXT"]))
	{
		$pdf->Ln(2);
		$pdf->SetFontSize(8);
		$pdf->SetWidths(array(190));
		$data = array(htmlspecialcharsBack($pack_array['PROPERTY_TAKE_COMMENT_VALUE']["TEXT"]));
		$pdf->Row($data,false,true);
	} 
	$pdf->Ln(2);	
	$pdf->SetFontSize(6);
	$name_of_file = 'pickup_'.$pack_array["ID"].'_'.nZakaz($pack_array["PROPERTY_N_ZAKAZ_IN_VALUE"]).'.pdf';
	if ($save == 'F')
	{
		$pdf->Output($_SERVER['DOCUMENT_ROOT'].'/warehouse/packs_pdf/'.$name_of_file,'F');
		return $_SERVER['DOCUMENT_ROOT'].'/warehouse/packs_pdf/'.$name_of_file;
	}
	if ($save == 'D')
	{
		return $pdf->Output('pickup_'.nZakaz($pack_array["PROPERTY_N_ZAKAZ_IN_VALUE"]).'.pdf','D');
	}
}

/**************************************************/
function GetGoodCorrections($good)
{
	$res_array = array();
	$filter = array("IBLOCK_ID"=>74,"PROPERTY_396"=>$good);
	$select = array("ID","PROPERTY_395","PROPERTY_397","PROPERTY_398");
	$res = CIBlockElement::GetList(array("created"=>"desc"), $filter, false, false, $select);
	while ($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$db_props = CIBlockElement::GetProperty(69, $a["PROPERTY_395_VALUE"], array("sort" => "asc"), Array("ID"=>358));
		$ar_props = $db_props->Fetch();
		$correction_true = IntVal($ar_props["VALUE"]);
		if ($correction_true == 158)
		{
			$db_props = CIBlockElement::GetProperty(69, $a["PROPERTY_395_VALUE"], array("sort" => "asc"), Array("ID"=>355));
			$ar_props = $db_props->Fetch();
			$a["ID_IN"] = $ar_props["VALUE"];
			$res_2 = CIBlockElement::GetByID($a["PROPERTY_395_VALUE"]);
			$ar_res = $res_2->GetNext();
			$a["DATE_CREATE_CORRECTION"] = $ar_res["DATE_CREATE"];
			$db_props = CIBlockElement::GetProperty(69, $a["PROPERTY_395_VALUE"], array("sort" => "asc"), Array("ID"=>357));
			$ar_props = $db_props->Fetch();
			$company = $ar_props["VALUE"];
			$company_info = GetCompany($company);
			$a["CREATED"] = $company_info["NAME"].', '.CUser::GetFullName($ar_res["CREATED_BY"]);
			$a["CREATED_ID"] = '['.$ar_res["CREATED_BY"].']';
			$res_array[] = $a;
		}
	}
	return $res_array;
}

/************формат представления веса*************/
function WeightFormat($w, $add = true, $ob = false, $count = 2)
{
	if ($w > 0)
	{
		if ($add)
		{
			$d = $ob ? 'Vкг' : 'кг';
			return number_format($w, $count, ',', ' ').' '.$d;
		}
		else
		{
			return number_format($w, $count, ',', ' ');
		}
	}
	else
	{
		return '';
	}
}

/***********отправка сообщения в системе***********/
function SendMessageInSystem($user, $from, $to, $name, $type, $text, $comment = '', $template = 0, $params = array())
{
	if (intval($template) > 0)
	{
		$rsEM = CEventMessage::GetByID($template);
		$arEM = $rsEM->Fetch();
		$txt = $arEM["MESSAGE"];
		foreach ($params as $k => $v)
		{
			$txt = str_replace("#".$k."#", $v, $txt);
		}
		$tt = $txt;
	}
	else
	{
		$tt = $text;
	}
	$el = new CIBlockElement;
	$arLoadProductArray = array (
		"MODIFIED_BY" => $user,
		"IBLOCK_ID" => 50,
		"DETAIL_TEXT" => $tt,
		"DETAIL_TEXT_TYPE" => "html",
		"PROPERTY_VALUES" => array(
			234 => $from,
			235 => $to,
			236 => $type,
			242 => $comment
		),
		"NAME" => $name
	);
	$qw = $el->Add($arLoadProductArray);
	return $qw;
}

/*****************удаление заказов*****************/
function DeleteOrders($packs = array())
{
	$delete_array = array();
	foreach ($packs as $pack)
	{
		$his_short = HistoryShortOfPackage($pack);
		foreach ($his_short as $v)
		{
			$delete_array[] = $v['ID'];
		}
		$his = HistoryOfPackage($pack);
		foreach ($his as $v)
		{
			$delete_array[] = $v['ID'];
		}
		$goods = GetGoodsOdPack($pack);
		foreach ($goods as $k => $v)
		{
			$delete_array[] = $k;
		}
		$delete_array[] = $pack;
	}
	if (count($delete_array) > 0)
	{
		foreach ($delete_array as $del)
		{
			CIBlockElement::Delete($del);
		}
		return true;
	}
	else {
		return false;
	}
}

/*******************манифест xls*******************/
function MakeManifestXls($m_id, $save = 'O')
{
	$arResult['MANIFEST']['ID'] = $m_id;
	$arResult['MANIFEST']['INFO'] = GetInfioOfManifest($arResult["MANIFEST"]["ID"]);
	$arResult['MANIFEST']['PACKS'] = GetListPackeges($arResult["MANIFEST"]["ID"], array("ID"=>"ASC"),0,false,false,0,'', false);
	$arResult['Cells_1'] = array(
		'ПОЛУЧАТЕЛЬ:' => htmlspecialcharsBack($arResult["MANIFEST"]["INFO"]["PROPERTY_AGENT_TO_NAME"]),
		'ГОРОД ПРИБЫТИЯ:' => $arResult["MANIFEST"]["INFO"]["PROPERTY_CITY"],
		'ПЕРЕВОЗЧИК:' => $arResult["MANIFEST"]["INFO"]["PROPERTY_CARRIER_NAME"],
		'ДАТА ОТПРАВЛЕНИЯ:' => substr($arResult["MANIFEST"]["INFO"]["PROPERTY_DATE_SEND_VALUE"],0,10),
		'РАСЧЕТНАЯ ДАТА ПРИБЫТИЯ:' => substr($arResult["MANIFEST"]["INFO"]["PROPERTY_DATE_SETTLEMENT_VALUE"],0,10),
		'ПЕРЕВОЗОЧНЫЙ ДОКУМЕНТ:' => $arResult["MANIFEST"]["INFO"]["PROPERTY_NUMBER_SEND_VALUE"],
		'ПЕРЕВОЗОЧНЫХ МЕСТ:' => $arResult["MANIFEST"]["INFO"]["PROPERTY_PLACES_VALUE"]
	);
	$arResult["Cells_3"][] = array(
		'',
		'Накладная',
		'Мест',
		'Вес',
		'Объемный вес',
		'РДД',
		'Отправитель',
		'Город получателя',
		'Получатель',
		'Адрес получателя',
		'Телефон получателя',
		'Специальные инструкции',
		'Город отправителя',
		'Адрес отправителя',
		'Телефон отправителя',
		'Сумма к оплате'
	);
	$s_w = $s_ob_w = $s_p = 0;
	foreach ($arResult["MANIFEST"]["PACKS"] as $v)
	{
		$s1 = (floatval($v["PROPERTY_SIZE_1_VALUE"]) > 0) ? $v["PROPERTY_SIZE_1_VALUE"] : 0;
		$s2 = (floatval($v["PROPERTY_SIZE_2_VALUE"]) > 0) ? $v["PROPERTY_SIZE_2_VALUE"] : 0;
		$s3 = (floatval($v["PROPERTY_SIZE_3_VALUE"]) > 0) ? $v["PROPERTY_SIZE_3_VALUE"] : 0;
		$ob_w = number_format((($s1 * $s2 * $s3) / 5000), 3, '.', '');
		$adr = ($v["PROPERTY_CONDITIONS_ENUM_ID"] == 37) ? $v["PROPERTY_ADRESS_VALUE"] : $v["PROPERTY_CONDITIONS_VALUE"];
		$company = GetCompany($v["PROPERTY_CREATOR_VALUE"]);
		$instr = '';
		if (strlen($v["PROPERTY_WHEN_TO_DELIVER_VALUE"]))
		{
			$instr = 'Доставить '.$v["PROPERTY_WHEN_TO_DELIVER_VALUE"];
		}
		if (strlen($v["PROPERTY_PREFERRED_TIME_VALUE"]["TEXT"]))
		{
			if (strlen($instr))
				$instr .= ' ';
			$instr .= $v["PROPERTY_PREFERRED_TIME_VALUE"]["TEXT"];
		}
		$arResult["Cells_3"][] = array(
			'',
			$v["PROPERTY_N_ZAKAZ_IN_VALUE"],
			$v["PROPERTY_PLACES_VALUE"],
			$v["PROPERTY_WEIGHT_VALUE"],
			$ob_w,
			'',
			$v["PROPERTY_CREATOR_NAME"],
			$v["PROPERTY_CITY_NAME"],
			$v["PROPERTY_RECIPIENT_VALUE"],
			$adr,
			$v["PROPERTY_PHONE_VALUE"],
			$instr,
			$company["PROPERTY_CITY_NAME"],
			$company["PROPERTY_ADRESS_VALUE"],
			$company["PROPERTY_PHONES_VALUE"],
			$v["PROPERTY_COST_2_VALUE"]
		);
		$s_w = $s_w + $v["PROPERTY_WEIGHT_VALUE"];
		$s_ob_w = $s_ob_w + $ob_w;
		$s_p = $s_p + $v["PROPERTY_PLACES_VALUE"];
	}
	$arResult["Cells_3"][] = array(
		'',
		'',
		$s_p,
		$s_w,
		$s_ob_w,
	);
	$arResult["Cells_2"] = array(
		'Отправитель: '.$arResult["MANIFEST"]["INFO"]["PROPERTY_AGENT_NAME"],
		'Манифест подготовил: '.$arResult["MANIFEST"]["INFO"]["USER_NAME"],
		'Дата создания манифеста: '.substr($arResult["MANIFEST"]["INFO"]["DATE_CREATE"],0,10),
		'',
		'ВСЕГО ОТПРАВЛЕНИЙ ПО МАНИФЕСТУ: '.count($arResult["MANIFEST"]["PACKS"]),
		'ВЕС ПО МАНИФЕСТУ: '.$s_w.' КГ',
		'ОБЪЕМНЫЙ ВЕС ПО МАНИФЕСТУ: '.$s_ob_w.' КГ('. number_format((pow($s_ob_w, 1/3)), 3, '.', '').' Куб.М.)'
	);
	set_include_path($_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/PhpExcel/Classes/');
	include_once 'PHPExcel.php';
	$pExcel = new PHPExcel();
	$pExcel->setActiveSheetIndex(0);
	$aSheet = $pExcel->getActiveSheet();
	$pExcel->getDefaultStyle()->getFont()->setName('Arial');
	$pExcel->getDefaultStyle()->getFont()->setSize(10);
	$Q = iconv("windows-1251", "utf-8", 'Манифест '.$arResult["MANIFEST"]["INFO"]["PROPERTY_NUMBER_VALUE"]);
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
	$Q = iconv("windows-1251", "utf-8", 'Манифест '.$arResult["MANIFEST"]["INFO"]["PROPERTY_NUMBER_VALUE"].' ОТ '.$arResult["MANIFEST"]["INFO"]["DATE_CREATE_TXT"]);
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
		$aSheet->setCellValue('N'.$i,$Q);
		$aSheet->getStyle('N'.$i)->applyFromArray($right)->applyFromArray($small);
		$aSheet->mergeCells("N".$i.":P".$i);
		$i++;
	}
	$aSheet->getStyle('N8:N10')->applyFromArray($boldFont);
	$i = 12;
	$arJ = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P');
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
	$aSheet->getStyle('B12:P12')->applyFromArray($head_style);
	$aSheet->getStyle('B'.$i.':P'.$i)->applyFromArray($footer_style);
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
	$aSheet->getStyle('B2')->applyFromArray($boldFont)->applyFromArray($center);
	$aSheet->mergeCells("B2:P2");
	$aSheet->getStyle('B12:P'.$i)->getAlignment()->setWrapText(true);
	$aSheet->getStyle('B12:P'.$i)->applyFromArray($styleArray);
	$aSheet->getStyle('A1:P'.$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
	include_once "PHPExcel/Writer/Excel5.php";
	$objWriter = new PHPExcel_Writer_Excel5($pExcel);
	if ($save == 'O')
	{
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$arResult["MANIFEST"]["INFO"]["NAME"].'.xls"');
		header('Cache-Control: max-age=0');
		$objWriter->save('php://output');
		return true;
	}
	if ($save == 'F')
	{
		$path = $_SERVER['DOCUMENT_ROOT']."/manifesty/files/".$arResult["MANIFEST"]["INFO"]["ID"].".xls";
		$objWriter->save($path);
		return $path;
	}
}

/****************список поставщиков****************/
function GetListSuppliers($shop, $use_navigation = true, $only_actives = false, $sort_array = array("ID"=>"ASC"))
{
	$arResult = array();
	$filter = array("IBLOCK_ID"=>75);
	if (($shop > 0) || (is_array($shop)))
	{
		if (intval($shop) > 0)
		{
			$filter["PROPERTY_SHOP"] = $shop;
		}
		elseif ((is_array($shop)) && (count($shop) > 0))
		{
			$filter["PROPERTY_SHOP"] = $shop;
		}
		else
		{
			$filter["PROPERTY_SHOP"] = false;
		}
	}
	if ($only_actives)
	{
		$filter["ACTIVE"] = "Y";
	}
	$select = array(
		"ID",
		"NAME",
		"ACTIVE",
		"PROPERTY_ID_IN",
		"PROPERTY_CITY.NAME",
		"PROPERTY_ADRESS",
		"PROPERTY_PHONE",
		"PROPERTY_MANAGER",
		"PROPERTY_INTRODUCE",
		'PROPERTY_CITY'
	);
	if ($use_navigation)
	{
		$on_page = intval($_GET['on_page']);
		if ($on_page < 10)
		{
			$on_page = 10;
		}
		elseif ($on_page >= 500)
		{
			$on_page = 500;
		}
		else {};
		$nav_array =  array("nPageSize"=>$on_page);
	}
	else
	{
		$nav_array = false;
	}
	$res = CIBlockElement::GetList($sort_array, $filter, false, $nav_array, $select);
	if ($use_navigation)
	{
		$arFields["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject,"Поставщики","","Y");
	}
	while($ob = $res->GetNextElement())
	{
		$arr = $ob->GetFields();
		$arFields[] = $arr;
	}
	return $arFields;
}

/**************список зявок на забор***************/
function GetListRequests($shop = 0, $use_navigation = true, $only_actives = false, $sort_array = array("ID"=>"DESC"), $type = 0, $status = 0, $report = '', $sup = 0, $date = '')
{
	$arResult = array();
	$filter = array("IBLOCK_ID"=>76);
	if ((intval($shop) > 0) || (is_array($shop)))
	{
		if (intval($shop) > 0)
		{
			$filter["PROPERTY_SHOP"] = $shop;
		}
		elseif ( (is_array($shop)) && (count($shop) > 0))
		{
			$filter["PROPERTY_SHOP"] = $shop;
		}
		else
		{
			$filter["PROPERTY_SHOP"] = false;
		}
	}
	if ($only_actives)
	{
		$filter["ACTIVE"] = "Y";
	}
	if ($type > 0)
	{
		$filter["PROPERTY_TYPE"] = $type;
	}
	if (($status > 0) || is_array($status))
	{
		$filter["PROPERTY_STATE"] = $status;
	}
	if (strlen($report))
	{
		if ($report == 'no')
		{
			$filter["PROPERTY_REPORT"] = false;
		}
		if (intval($report) > 0)
		{
			$filter["PROPERTY_REPORT"] = $report;
		}
			
	}
	if ($sup > 0)
	{
		$filter["PROPERTY_SUPPLIER"] = $sup;
	}
	if (strlen($date))
	{
		$filter["PROPERTY_DATE"] = substr($date,6,4)."-".substr($date,3,2)."-".substr($date,0,2)." 00:00:00";
	}
	$select = array(
		"ID",
		"NAME",
		"ACTIVE",
		"PROPERTY_ID_IN",
		"PROPERTY_NUMBER",
		"PROPERTY_SHOP",
		"PROPERTY_SHOP.NAME",
		"PROPERTY_TYPE",
		"PROPERTY_SUPPLIER",
		"PROPERTY_SUPPLIER.NAME",
		"PROPERTY_WEIGHT",
		"PROPERTY_SIZE_1",
		"PROPERTY_SIZE_2",
		"PROPERTY_SIZE_3",
		"PROPERTY_WEIGHT",
		"PROPERTY_COMMENT",
		"PROPERTY_WHEN",
		"PROPERTY_DATE",
		"PROPERTY_STATE",
		"PROPERTY_REPOR",
		"PROPERTY_COST"
	);
	if ($use_navigation)
	{
		$on_page = intval($_GET['on_page']);
		if ($on_page < 10)
		{
			$on_page = 10;
		}
		elseif ($on_page >= 500)
		{
			$on_page = 500;
		}
		else {};
		$nav_array =  array("nPageSize"=>$on_page);
	}
	else
	{
		$nav_array = false;
	}
	$res = CIBlockElement::GetList($sort_array, $filter, false, $nav_array, $select);
	if ($use_navigation)
	{
		$arFields["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject,"Заявки","","Y");
	}
	while($ob = $res->GetNextElement())
	{
		$arr = $ob->GetFields();
		$arr["OB_W"] = ($arr["PROPERTY_SIZE_1_VALUE"]*$arr["PROPERTY_SIZE_2_VALUE"]*$arr["PROPERTY_SIZE_3_VALUE"])/5000;
		$arFields[] = $arr;
	}
	return $arFields;
}

/*****************заявка на забор******************/
function GetOneRequest($id, $shop = 0, $type = 0)
{
	$arResult = array();
	$filter = array("IBLOCK_ID"=>76,"ID"=>$id);
	if ($shop > 0)
	{
		$filter["PROPERTY_SHOP"] = $shop;
	}
	if (intval($type) > 0)
	{
		$filter["PROPERTY_TYPE"] = intval($type);
	}
	$select = array(
		"ID",
		"DATE_CREATE",
		"ACTIVE",
		"NAME",
		"PROPERTY_ID_IN",
		"PROPERTY_NUMBER",
		"PROPERTY_SHOP",
		"PROPERTY_TYPE",
		"PROPERTY_SUPPLIER",
		"PROPERTY_SUPPLIER.NAME",
		"PROPERTY_WEIGHT",
		"PROPERTY_SIZE_1",
		"PROPERTY_SIZE_2",
		"PROPERTY_SIZE_3",
		"PROPERTY_WEIGHT",
		"PROPERTY_COMMENT",
		"PROPERTY_WHEN",
		"PROPERTY_DATE",
		"PROPERTY_STATE",
		"PROPERTY_SHOP.NAME",
		"PROPERTY_COST"
	);
	$res = CIBlockElement::GetList(array("ID"=>"DESC"), $filter, false, false, $select);
	if($ob = $res->GetNextElement())
	{
		$arr = $ob->GetFields();
		$arr["OB_W"] = ($arr["PROPERTY_SIZE_1_VALUE"]*$arr["PROPERTY_SIZE_2_VALUE"]*$arr["PROPERTY_SIZE_3_VALUE"])/5000;
		$date_massive = DateFFReverse($arr["PROPERTY_WHEN_VALUE"]);
		$arr["FROM"] = $date_massive['time_1'];
		$arr["TO"] = $date_massive['time_2'];
		$arr['orders'] = array();
		$arr['packs'] = array();
		$res_2 = CIBlockElement::GetList(array("ID"=>"ASC"), array("IBLOCK_ID"=>77,"PROPERTY_428"=>$id), false, false, array("ID","NAME","PROPERTY_429","PROPERTY_430","PROPERTY_431","PROPERTY_432"));
		while ($ob_2 = $res_2->GetNextElement())
		{
			$arOrders = $ob_2->GetFields();
			$arOrders['NUMBER'] = GetNumberOfPack($arOrders["PROPERTY_429_VALUE"]);
			$arr['orders'][] = $arOrders;
		}
		$res_3 = CIBlockElement::GetList(array("ID"=>"ASC"), array("IBLOCK_ID"=>42,"PROPERTY_443"=>$id), false, false, array("ID","NAME","PROPERTY_N_ZAKAZ_IN","PROPERTY_WEIGHT","PROPERTY_SIZE_2","PROPERTY_SIZE_1","PROPERTY_SIZE_3"));
		while ($ob_3 = $res_3->GetNextElement())
		{
			$arPacks = $ob_3->GetFields();
			$gb_w = WhatIsGabWeightCompany($arr['PROPERTY_SHOP_VALUE']);
			$arPacks['PROPERTY_VWEIGHT_VALUE'] = ($arPacks['PROPERTY_SIZE_1_VALUE']*$arPacks['PROPERTY_SIZE_2_VALUE']*$arPacks['PROPERTY_SIZE_3_VALUE'])/$gb_w;
			$arr['packs'][] = $arPacks;
		}
		$arFields = $arr;
		return $arFields;
	}
	else
	{ 
		return false;
	}
}

/***************заявка на забор xls****************/
function GetRequestsXLS($shop, $reqv = array(), $save = 'O')
{
	$arResult["Cells"] = array();
	$arResult["Cells"][] = array(
		'Номер накладной',
		'Номер Заказа',
		'Дата Выполнения Заявки',
		'Город Отправителя',
		'Компания Отправителя',
		'Адрес Отправителя',
		'Телефон Отправителя',
		'Контактное лицо',
		'Что забирать?',
		'Вес',
		'Сумма к получению по заявке',
		'Город Получателя',
		'Компания Получателя',
		'Адрес Получателя',
		'Телефон Получателя',
		'Фамилия Получателя',
		'Признак Тип Доставки',
		'Специальные Инструкции',
		'Форма оплаты',
		'Менеджер'
	);
	foreach ($reqv as $r)
	{
		$r_info = GetOneRequest($r,$shop);
		$shop = $r_info["PROPERTY_SHOP_VALUE"];
		$shop_info = GetCompany($shop);
		if ($r_info)
		{
			$what_txt = '';
			$what_arr = array();
			foreach ($r_info['orders'] as $ord)
			{
				$what = '';
				$what .= $ord["NAME"];
				if (strlen($ord["PROPERTY_431_VALUE"]))
				{
					$what .= ' (Артикул: '.$ord["PROPERTY_431_VALUE"].')';
				}
				$what .= ' '.WeightFormat($ord["PROPERTY_430_VALUE"]).' - '.$ord["PROPERTY_432_VALUE"].' шт.';
				$what_arr[] = $what;
			}
			$what_txt = implode($what_arr,'; ');
			$sup_info = GetInfoOfSupplier($r_info["PROPERTY_SUPPLIER_VALUE"],$shop);
			$comment = '';
			if (strlen($sup_info["PROPERTY_INTRODUCE_VALUE"]))
			{
				$comment .= 'Представиться как '.$sup_info["PROPERTY_INTRODUCE_VALUE"];
			}
			if (strlen($comment))
			{
				$comment .= '. ';
			}
			$comment .= $r_info["PROPERTY_COMMENT_VALUE"]["TEXT"];
			$arResult["Cells"][] = array(
				'',
				$r_info["PROPERTY_NUMBER_VALUE"],
				$r_info["PROPERTY_DATE_VALUE"],
				$sup_info["PROPERTY_CITY_NAME"],
				htmlspecialcharsBack($sup_info["NAME"]),
				$sup_info["PROPERTY_ADRESS_VALUE"],
				$sup_info["PROPERTY_PHONE_VALUE"],
				$sup_info["PROPERTY_MANAGER_VALUE"],
				htmlspecialcharsBack($what_txt),
				WeightFormat(max(array($r_info["PROPERTY_WEIGHT_VALUE"],$r_info["OB_W"])),false),
				'',
				$shop_info["PROPERTY_CITY_NAME"],
				htmlspecialcharsBack($shop_info["NAME"]),
				htmlspecialcharsBack($shop_info["PROPERTY_ADRESS_VALUE"]),
				$shop_info["PROPERTY_PHONES_VALUE"],
				$shop_info["PROPERTY_RESPONSIBLE_PERSON_VALUE"],
				'',
				htmlspecialcharsBack($comment),
				'',
				''
			);
		}
	}
	set_include_path($_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/PhpExcel/Classes/');
	include_once 'PHPExcel.php';
	$pExcel = new PHPExcel();
	$pExcel->setActiveSheetIndex(0);
	$aSheet = $pExcel->getActiveSheet();
	$pExcel->getDefaultStyle()->getFont()->setName('Arial');
	$pExcel->getDefaultStyle()->getFont()->setSize(10);
	$Q = iconv("windows-1251", "utf-8", 'Лист 1');
	$aSheet->setTitle($Q);
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
				'argb' => 'FFFFFBF0',
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
	$i = 1;
	$arJ = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P',"Q","R","S","T");
	foreach  ($arResult["Cells"] as $k)
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
	foreach ($arJ as $cc)
	{
		$aSheet->getColumnDimension($cc)->setWidth(17);
	}
	$aSheet->getStyle('A1:T1')->applyFromArray($head_style);
	$aSheet->getStyle('A1:T'.$i)->getAlignment()->setWrapText(true);
	$aSheet->getStyle('A1:T'.$i)->applyFromArray($styleArray);
	$aSheet->getStyle('A1:T'.$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
	include_once "PHPExcel/Writer/Excel5.php";
	$objWriter = new PHPExcel_Writer_Excel5($pExcel);
	if ($save == 'O')
	{
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.time().'.xls"');
		header('Cache-Control: max-age=0');
		$objWriter->save('php://output');
		return true;
	}
	if ($save == 'F')
	{
		$path = $_SERVER['DOCUMENT_ROOT']."/manifesty/files/".time().".xls";
		$objWriter->save($path);
		return $path;
	}
}

/***************заявка на забор pdf****************/
function GetRequestsPDF($shop, $reqv_id, $save = 'O', $send_to_other = false)
{
	$r_info = GetOneRequest($reqv_id,$shop);
	if ($r_info)
	{
		$shop_info = GetCompany($r_info);
		$sup_info = GetInfoOfSupplier($r_info["PROPERTY_SUPPLIER_VALUE"],$shop);
		if ($r_info["PROPERTY_TYPE_ENUM_ID"] == 181)
		{
			$t_txt = 'забор у поставщика';
		}
		if ($r_info["PROPERTY_TYPE_ENUM_ID"] == 182)
		{
			$t_txt = 'вызов курьера';
		}
		$pdf = new PDF_MC_Table();
		$pdf->AddFont('ArialMT','','arialTM.php');
		$pdf->SetFillColor(255,255,255);
		$pdf->AddPage('P');
		$pdf->SetFont('ArialMT','',20);
		$pdf->SetFontSize(12);
		$pdf->Write(5,'Заявка на '.$t_txt.' '.$r_info["PROPERTY_NUMBER_VALUE"]);
		$pdf->Ln(5);
		$pdf->SetFontSize(7);
		$pdf->SetWidths(array(95,95));
		$data = array(htmlspecialcharsBack($shop_info["PROPERTY_LEGAL_NAME_VALUE"]), array('value'=>htmlspecialcharsBack($shop_info["PROPERTY_UK_NAME"]),'align'=>'R'));
		$pdf->Row($data,false,true);
		$pdf->Ln(2);
		$pdf->Line(10,20,200,20);
		$pdf->SetFontSize(12);
		$pdf->SetWidths(array(45,145));
		$data  = array('Магазин:',htmlspecialcharsBack($shop_info["NAME"].', '.$shop_info["PROPERTY_CITY_NAME"]));
		$pdf->Row($data,false,true);
		$data = array('Забрать:',htmlspecialcharsBack($r_info["PROPERTY_WHEN_VALUE"]));
		$pdf->Row($data,false,true);
		if ($r_info["PROPERTY_TYPE_ENUM_ID"] == 181)
		{
			$data = array('Поставщик:',htmlspecialcharsBack($r_info["PROPERTY_SUPPLIER_NAME"]));
			$pdf->Row($data,false,true);
			$pdf->SetFontSize(8);
			$pdf->SetWidths(array(45,30,105));
			$data = array('','Город:',htmlspecialcharsBack($sup_info["PROPERTY_CITY"]));
			$pdf->Row($data,false,true);
			$data = array('','Адрес:',htmlspecialcharsBack($sup_info["PROPERTY_ADRESS_VALUE"]));
			$pdf->Row($data,false,true);
			$data = array('','Номер телефона:',htmlspecialcharsBack($sup_info["PROPERTY_PHONE_VALUE"]));
			$pdf->Row($data,false,true);
			$data = array('','Менеджер:',htmlspecialcharsBack($sup_info["PROPERTY_MANAGER_VALUE"]));
			$pdf->Row($data,false,true);
			$data = array('','Как представиться:',htmlspecialcharsBack($sup_info["PROPERTY_INTRODUCE_VALUE"]));
			$pdf->Row($data,false,true);
			$pdf->SetFontSize(12);
			$pdf->SetWidths(array(45,145));
		}
		$data = array('Вес:',WeightFormat($r_info["PROPERTY_WEIGHT_VALUE"]));
		$pdf->Row($data,false,true);
		$data = array('Габариты:',  $r_info["PROPERTY_SIZE_1_VALUE"].' * '.
				$r_info["PROPERTY_SIZE_2_VALUE"].' * '.
				$r_info["PROPERTY_SIZE_3_VALUE"].' см ('.
                WeightFormat($r_info["OB_W"]).')'
		);
		$pdf->Row($data,false,true);
		if (strlen($r_info["PROPERTY_COMMENT_VALUE"]['TEXT']))
		{
			$data = array('Комментарий:',htmlspecialcharsBack($r_info["PROPERTY_COMMENT_VALUE"]['TEXT']));
			$pdf->Row($data,false,true);
		}
		if (($send_to_other) || ($_GET['yes'] == '1'))
		{
			$pdf->SetFontSize(16);
			$data = array('',htmlspecialcharsBack('Отправить в город: '.$shop_info["PROPERTY_UK_CITY"]));
			$pdf->Row($data,false,true);
			$pdf->SetFontSize(12);
		}
		if ((count($r_info['orders']) > 0) || (count($r_info['packs']) > 0))
		{
			$pdf->Ln();
			$data = array('Забрать:','');
			$pdf->Row($data,false,true);
			$pdf->Ln();
			$pdf->SetFontSize(7);
		}
		if (count($r_info['orders']) > 0)
		{
			$pdf->SetWidths(array(10,95,30,30,25));
			$data = array('№','Наименование','Артикул','Вес','Количество');
			$pdf->Row($data,true);
			$i = 0;
			foreach ($r_info['orders'] as $r)
			{
				$i++;
				$data = array(
					$i,
					htmlspecialcharsBack($r['NAME']),
					htmlspecialcharsBack($r['PROPERTY_431_VALUE']),
					WeightFormat($r['PROPERTY_430_VALUE']),
					$r['PROPERTY_432_VALUE']
				);
				$pdf->Row($data);
			}
		}
		if (count($r_info['packs']) > 0)
		{
			$pdf->SetWidths(array(10,120,60));
			$data = array('№','Заказ','Вес');
			$pdf->Row($data,true);
			$i = 0;
			foreach ($r_info['packs'] as $r)
			{
				$i++;
				$data = array(
					$i,
					$r['PROPERTY_N_ZAKAZ_IN_VALUE'],
					WeightFormat($r['PROPERTY_WEIGHT_VALUE'])
				);
				$pdf->Row($data);
			}
		}
		$name_of_file = $r_info["PROPERTY_NUMBER_VALUE"].'.pdf';
		if ($save == 'F')
		{
			$pdf->Output($_SERVER['DOCUMENT_ROOT'].'/warehouse/packs_pdf/'.$name_of_file,'F');
			return $_SERVER['DOCUMENT_ROOT'].'/warehouse/packs_pdf/'.$name_of_file;
		}
		if ($save == 'O')
		{
			return $pdf->Output($name_of_file, 'D');
		}
	}
}

/*************информация о поставщике**************/
function GetInfoOfSupplier($sup, $agent = 0, $sort_array = array("id" => "asc"))
{
	$filter = array("IBLOCK_ID" => 75, "ID" => $sup);
	if ($agent > 0)
	{
		$filter["PROPERTY_SHOP"] = $agent;
	}
	$select = array(
		"ID",
		"NAME",
		"ACTIVE",
		"PROPERTY_ID_IN",
		"PROPERTY_SHOP",
		"PROPERTY_SHOP.NAME",
		"PROPERTY_CITY",
		"PROPERTY_CITY.NAME",
		"PROPERTY_ADRESS",
		"PROPERTY_PHONE",
		"PROPERTY_MANAGER",
		"PROPERTY_INTRODUCE"
	);
	$res = CIBlockElement::GetList($sort_array, $filter, false, false, $select);
	if ($ob = $res->GetNextElement())
	{
		$arr = $ob->GetFields();
		$arr["PROPERTY_CITY"] = GetFullNameOfCity($arr["PROPERTY_CITY_VALUE"]);
		return $arr;
	}
	else
	{
		return false;
	}
}

/****************роль пользователя*****************/
function GetRoleOfUser($user) 
{
	$rsUser = CUser::GetByID($user);
	$arUser = $rsUser->Fetch();
	if (intval($arUser["UF_ROLE"]) > 0)
	{
		$res = CIBlockElement::GetByID(intval($arUser["UF_ROLE"]));
		if ($ar_res = $res->GetNext())
		{
			return strlen($ar_res["CODE"]) ? $ar_res["CODE"] : "DIR";
		}
		else
		{
			return "DIR";
		}
	}
	else
	{
		return "DIR";
	}
}

function GenericKey($user_id)
{
	$c = 'qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM0123456789';
	$key = '1';
	$l = strlen($user_id);
	for ($i=0; $i<8-$l; $i++)
	{
		$key .= '0';
	}
	$key .= $user_id;
	$l = strlen($key);
	$n = strlen($c)-1;
	for ($i=0; $i<32-$l; $i++)
	{
		$key .= $c[rand(0, $n)];
	}
	return $key;
}

function GetOnePackage($pack_id, $who, $type_who)
{
	$select = array(
		"ID","NAME","PROPERTY_N_ZAKAZ","PROPERTY_RECIPIENT","PROPERTY_PHONE","PROPERTY_ADRESS","PROPERTY_COST_1","PROPERTY_COST_2","PROPERTY_COST_3","PROPERTY_COST_4","PROPERTY_STATE",
		"PROPERTY_COURIER",'PROPERTY_CONDITIONS',"PROPERTY_COURIER.NAME","PROPERTY_CITY.NAME","PROPERTY_CITY","PROPERTY_CREATOR","PROPERTY_CREATOR.NAME","DATE_CREATE","PROPERTY_MANIFEST",
		"PROPERTY_SUMM","PROPERTY_WEIGHT","PROPERTY_STATE_SHORT","PROPERTY_RATE","PROPERTY_PLACES","PROPERTY_SIZE_1","PROPERTY_SIZE_2","PROPERTY_SIZE_3","PROPERTY_SUMM_SHOP","PROPERTY_SUMM_AGENT",
		"PROPERTY_RATE_AGENT","PROPERTY_DATE_DELIVERY","CREATED_BY","PROPERTY_COST_GOODS","PROPERTY_CASH","PROPERTY_ID_IN","PROPERTY_PVZ","PROPERTY_SUMM_ISSUE","PROPERTY_DATE_DELIVERY",
		"PROPERTY_TYPE_PAYMENT","PROPERTY_PREFERRED_TIME","PROPERTY_COMMENTS_COURIER","PROPERTY_URGENCY_ORDER","PROPERTY_WHEN_TO_DELIVER","PROPERTY_TAKE_PROVIDER","PROPERTY_TAKE_DATE",
		"PROPERTY_TAKE_COMMENT","PROPERTY_N_ZAKAZ_IN", "PROPERTY_SUMM_SHOP_ZABOR","PROPERTY_EXCEPTIONAL_SITUATION","PROPERTY_RETURN","PROPERTY_COST_RETURN","PROPERTY_DELIVERY_LEGAL", 
		"PROPERTY_CALL_COURIER", 'PROPERTY_PAY_FOR_REFUSAL', 'PROPERTY_OBTAINED', 'PROPERTY_REPORT', 'PROPERTY_REPORT.NAME', 'PROPERTY_DATE_TO_DELIVERY', 'PROPERTY_TIME_PERIOD', 
		'PROPERTY_AGENT.NAME', 'PROPERTY_END', 'PROPERTY_PACK_GOODS'
	);
	$filter = array("IBLOCK_ID" => 42, "ID" => $pack_id);
	if ($type_who == 51)
	{
		$list_shops_ids = array();
		$list_shops = TheListOfShops(0, false, true, false, '', $who);
		foreach ($list_shops as $s)
		{
			$list_shops_ids[] = $s["ID"];
		}
		if (count($list_shops_ids) > 0)
		{
			$filter['PROPERTY_CREATOR'] = $list_shops_ids;
		}
		else
		{
			return false;
		}
	}
	elseif ($type_who == 52)
	{
		$filter['PROPERTY_CREATOR'] = $who;
	}
	elseif ($type_who == 53)
	{
		$list_mans = array();
		$mans = ManifestsToAgent($who, 0, false, false, false);
		foreach ($mans as $v)
		{
			$list_mans[] = $v['ID'];
		}
		if (count($list_mans) > 0)
		{
			$filter['PROPERTY_MANIFEST'] = $list_mans;
		}
		else
		{
			return false;
		}
	}
	else
	{
		return false;
	}
	
	$res = CIBlockElement::GetList(array("ID"=>"ASC"), $filter, false, false, $select);
	if ($ob = $res->GetNextElement())
	{
		$arr = $ob->GetFields();
		$arr['GOODS'] = GetGoodsOdPack($arr["ID"]);
		$arr['SHOP'] = GetCompany($arr['PROPERTY_CREATOR_VALUE']);
		$arr['PROPERTY_CITY'] = GetFullNameOfCity($arr['PROPERTY_CITY_VALUE']);
		$arr['PROPERTY_CITY_TD'] = GetCityTD($arr['PROPERTY_CITY_VALUE']);
		$arr['PROPERTY_REGION_TD'] = GetCityTD($arr['PROPERTY_CITY_VALUE'], 'region');
		$arr['HISTORY']= HistoryOfPackage($arr["ID"]);
		$arr['SHORT_HISTORY']= HistoryShortOfPackage($arr["ID"]);
		$st_f = $arr['PROPERTY_STATE_VALUE'];
		$st_s = $arr['PROPERTY_STATE_SHORT_VALUE'];
		if ($arr['PROPERTY_EXCEPTIONAL_SITUATION_VALUE'] == 1)
		{
			$arr['PROPERTY_STATE_VALUE'] = 'Исключительная ситуация! '.$st_f;
			$arr['PROPERTY_STATE_SHORT_VALUE'] = 'Исключительная ситуация! '.$st_s;
			$arr['PROPERTY_STATE_PACK'] = 'Исключительная ситуация<br>';
		}
		if ($arr['PROPERTY_RETURN_VALUE'] == 1)
		{
			$arr['PROPERTY_STATE_PACK'] .= 'Возврат<br>';
		}
		if ($type_who == 51)
		{
			$arr['PROPERTY_STATE_PACK'] .= $st_f;
		}
		else
		{
			$arr['PROPERTY_STATE_PACK'] .= $st_s;
		}
		$deliv_array = DateFFReverse($arr['PROPERTY_WHEN_TO_DELIVER_VALUE']);
		$arr["DELIV_DATE"] = $deliv_array['date'];
		$arr['PACK_GOODS'] = '';
		if (strlen($arr['PROPERTY_PACK_GOODS_VALUE']))
		{
			$arr['PACK_GOODS'] = json_decode(htmlspecialcharsBack($arr['PROPERTY_PACK_GOODS_VALUE']), true);
			if ((is_array($arr['PACK_GOODS'])) && (count($arr['PACK_GOODS']) > 0))
			{
				foreach ($arr['PACK_GOODS'] as $k => $str)
				{
					$arr['PACK_GOODS'][$k]['GoodsName'] = iconv('utf-8','windows-1251',$str['GoodsName']);
					if (strlen(trim($arr['PACK_GOODS'][$k]['GoodsName'])) == 0)
					{
						unset($arr['PACK_GOODS'][$k]);
					}
				}
			}
		}
		$arr_zabors = array();
		$arr_zab_all = array();
		$res_zabors = CIBlockElement::GetList(array("ID"=>"ASC"), array("IBLOCK_ID" => 77,"PROPERTY_429" => $arr["ID"]), false, false, array(
			"ID",
			"NAME",
			"PROPERTY_428",
			"PROPERTY_429",
			"PROPERTY_430",
			"PROPERTY_431",
			"PROPERTY_432"
		));
		while ($ob_zabors = $res_zabors->GetNextElement())
		{
			$z_el_info = $ob_zabors->GetFields();
			$arr_zabors[] = $z_el_info;
			if (!in_array($z_el_info["PROPERTY_428_VALUE"],$arr_zab_all))
			{
				$arr_zab_all[] = $z_el_info["PROPERTY_428_VALUE"];
				$arr["REQV_INFO"][$z_el_info["PROPERTY_428_VALUE"]] = GetOneRequest($z_el_info["PROPERTY_428_VALUE"]);
			}
			$arr["ZABORS_IDS"][] = $z_el_info["ID"];
		}
		$arr["ZABORS"] = $arr_zabors;
		$arr["REQS"] = $arr_zab_all;
		if (count($arr["REQS"]) == 0)
		{
			$arr["REQS"] = array(0=>0);
		}
		$arr['NAME_OF_MAN'] = array();
		if (count($arr['PROPERTY_MANIFEST_VALUE']) > 0)
		{
			foreach ($arr['PROPERTY_MANIFEST_VALUE'] as $k)
			{
				$db_props = CIBlockElement::GetProperty(41, $k, array("sort" => "asc"), array("CODE"=>"NUMBER"));
				if($ar_props = $db_props->Fetch())
				{
					$arr['NAME_OF_MAN'][$k] = $ar_props["VALUE"];
				}
			}
		}
		return $arr;
	}
	else
	{
		return false;
	}
}

function GetListReturns($agent, $use_navigation = true, $iskl = true, $return = false, $number = '')
{
	$select = array(
		"ID","NAME","PROPERTY_N_ZAKAZ_IN", "PROPERTY_WEIGHT", "PROPERTY_SIZE_1", "PROPERTY_SIZE_2", "PROPERTY_SIZE_3", "PROPERTY_PLACES", 'PROPERTY_COST_RETURN', 'PROPERTY_CREATOR', 
		'PROPERTY_SUMM_SHOP', 'PROPERTY_CITY.NAME', 'PROPERTY_CONDITIONS', 'PROPERTY_STATE', 'PROPERTY_EXCEPTIONAL_SITUATION'
	);
	$filter = array("IBLOCK_ID" => 42, 'PROPERTY_AGENT' => $agent, 'PROPERTY_END' => 0);
	if ($iskl)
	{
		$filter['PROPERTY_EXCEPTIONAL_SITUATION'] = 1;
	}
	if ($return)
	{
		$filter['PROPERTY_RETURN'] = 1;
	}
	else
	{
		$filter['PROPERTY_RETURN'] = 0;
	}
	if (strlen($number))
	{
		$filter['PROPERTY_N_ZAKAZ_IN'] = '%'.$number.'%';
	}
	if ($use_navigation)
	{
		if (isset($_GET['on_page']))
		{
			$on_page = intval($_GET['on_page']);
		}
		else
		{
			if (isset($_SESSION['ON_PAGE_GLOBAL']))
			{
				$on_page = intval($_SESSION['ON_PAGE_GLOBAL']);
			}
			else
			{
				$on_page = 10;
			}
		}
		if ($on_page < 10)
		{
			$on_page = 10;
		}
		if ($on_page >= 200)
		{
			$on_page = 200;
		}
		$postran = array("nPageSize" => $on_page);
	}
	else
	{
		$postran = false;
	}
	$arr = array();
	$res = CIBlockElement::GetList(array("ID"=>"ASC"), $filter, false, $postran, $select);
	if ($use_navigation)
	{
		$arr["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject, "Заказы", "", "Y");
	}
	while ($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$a['PROPERTY_OB_WEIGHT_VALUE'] = ($a['PROPERTY_SIZE_1_VALUE']*$a['PROPERTY_SIZE_2_VALUE']*$a['PROPERTY_SIZE_3_VALUE'])/5000;
		$arr[] = $a;
	}
	return $arr;
}

function GetCountOfGood($good_id)
{
	$db_props = CIBlockElement::GetProperty(62, $good_id, array("sort" => "asc"), Array("ID" => "299"));
	if($ar_props = $db_props->Fetch())
	{
		$FORUM_TOPIC_ID = floatval($ar_props["VALUE"]);
	}
	else
	{
		$FORUM_TOPIC_ID = 0;
	}
	return $FORUM_TOPIC_ID;
}

function CheckRequestThisWeek($agent, $uk)
{
	$count = 0;
	$now_w_day = jddayofweek(0);
	if ($now_w_day > 0)
	{
		$minus = $now_w_day - 1;
	}
	else
	{
		$minus = 6;
	}
	$timastamp = strtotime("- ".$minus." day");
	$date = date('d.m.Y 00:00:00',$timastamp);
	$res = CIBlockElement::GetList(
		array("ID" => "DESC"), 
		array("IBLOCK_ID" => 50, 'PROPERTY_FROM' => $agent, 'PROPERTY_TO' => $uk, 'PROPERTY_TYPE' => 220, '>=DATE_CREATE' => $date), 
		false, 
		false, 
		array('ID')
	);
	while ($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$count++;
	}
	if ($count > 0)
	{
		return true;
	}
	else
	{
		return false;
	}
}

function TheListOfCitiesPVZ($agent, $all = true)
{
	$arcities = array();
	$pvz_list = TheListOfPVZ($agent, 0, $all, 0, false, 0);
	if ($pvz_list)
	{
		foreach ($pvz_list as $v)
		{
			$arcities[$v['PROPERTY_CITY_VALUE']] = $v['CITY_NAME'];
		}
	}
	return $arcities;
}

function GetCityTD($city, $type = 'city')
{
	$result = false;
	if ($type == 'city')
	{
		$db_props = CIBlockElement::GetProperty(6, $city, array("sort" => "asc"), Array("CODE"=>"CODE_TOPDELIVERY"));
		if ($ar_props = $db_props->Fetch())
		{
			$result = $ar_props["VALUE"];
		}
	}
	elseif ($type == 'region')
	{
		
		$db_old_groups = CIBlockElement::GetElementGroups($city, true);
		if($ar_group = $db_old_groups->Fetch())
		{
			$db_list = CIBlockSection::GetList(array("sort"=>"asc"), array('IBLOCK_ID'=>6, 'ID'=>$ar_group["ID"]), false, array('UF_CODE_TOPDELIVERY'));
			if  ($ar_result = $db_list->GetNext())
			{
				$result = $ar_result['UF_CODE_TOPDELIVERY'];
			}
		}
	}
	else
	{
	}
	return $result;
}

/**************список отчетов агента***************/
function GetListOfReportsAgent($agent, $id_report = 0, $type = 0, $notsigned = false, $nav = true)
{
	$arFields = array();
	$select = array("ID","NAME","PROPERTY_ID_IN","PROPERTY_DATE","PROPERTY_PAYMENT","PROPERTY_STORAGE","PROPERTY_START","PROPERTY_END","PROPERTY_SIGNED", 'PROPERTY_CONFIRMED', 'PROPERTY_DATE_REPORT_FROM', 'PROPERTY_DATE_REPORT_TO');
	$filter = array("IBLOCK_ID" => 67, "PROPERTY_SHOP" => $agent);
	if ($id_report > 0)
	{
		$filter["ID"] = $id_report;
		$nav_array = false;
	}
	else
	{
		if ($nav)
		{
			$on_page = intval($_GET['on_page']);
			if ($on_page < 10)
			{
				$on_page = 10;
			}
			elseif ($on_page >= 500)
			{
				$on_page = 500;
			}
			else {};
			$nav_array = array("nPageSize"=>$on_page);
		}
		else
		{
			$nav_array = false;
		}
	}
	if ($type > 0)
	{
		$filter['PROPERTY_TYPE'] = $type;
	}
	if ($notsigned)
	{
		$filter['PROPERTY_SIGNED'] = false;
	}
	$res = CIBlockElement::GetList(array("ID" => "desc"), $filter, false, $nav_array, $select);
	if (is_array($nav_array))
	{
		$arFields["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject, "Отчеты", "", "Y");
	}
	while($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$a['PACKS'] = array();
		$res_2 = CIBlockElement::GetList(
			array("ID" => "asc"), 
			array(
				"IBLOCK_ID" => 42,
				'PROPERTY_REPORT_AGENT' => $a['ID']
			), 
			false, 
			false, 
			array('ID', 'PROPERTY_OBTAINED', 'PROPERTY_SUMM_AGENT', 'PROPERTY_RATE_AGENT', 'PROPERTY_N_ZAKAZ_IN')
		);
		while($ob_2 = $res_2->GetNextElement())
		{
			$a_2 = $ob_2->GetFields();
			$a['PACKS'][] = $a_2;
		}
		$a["COST"] = $a["SUMM_AGENT"] = $a["RATE"] = $a['TO_AGENT'] = 0;
		foreach ($a['PACKS'] as $p)
		{
			$a["COST"] = $a["COST"] + $p['PROPERTY_OBTAINED_VALUE'];
			$a['SUMM_AGENT'] = $a['SUMM_AGENT'] + $p['PROPERTY_SUMM_AGENT_VALUE'];
			$a["RATE"] = $a["RATE"] + $p['PROPERTY_RATE_AGENT_VALUE'];
		}
		$a['TO_AGENT'] = $a["COST"] - $a['SUMM_AGENT'] - $a["RATE"];
		$moths = array('','января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
		$min = mktime(11, 0, 0, substr($a['PROPERTY_DATE_REPORT_FROM_VALUE'],3,2), substr($a['PROPERTY_DATE_REPORT_FROM_VALUE'],0,2), substr($a['PROPERTY_DATE_REPORT_FROM_VALUE'],6,4));
		$max = mktime(11, 0, 0, substr($a['PROPERTY_DATE_REPORT_TO_VALUE'],3,2), substr($a['PROPERTY_DATE_REPORT_TO_VALUE'],0,2), substr($a['PROPERTY_DATE_REPORT_TO_VALUE'],6,4));
		$a["START_DATE"] = '&laquo;'.date('d',$min).'&raquo; '.$moths[date('n',$min)].' '.date('Y',$min).' г.';
		$a["END_DATE"] = '&laquo;'.date('d',$max).'&raquo; '.$moths[date('n',$max)].' '.date('Y',$max).' г.';
		$a["PERIOD"] = date('d.m.Y',$min).' - '.date('d.m.Y',$max);
		$a["PERIOD_1"] = date('d.m.Y',$min);
		$a["PERIOD_2"] = date('d.m.Y',$max);
		$a["DATE_FORMATED"] = '&laquo;'.substr($a['PROPERTY_DATE_VALUE'],0,2).'&raquo; '.$moths[intval(substr($a['PROPERTY_DATE_VALUE'],3,2))].' '.substr($a['PROPERTY_DATE_VALUE'],6,4).' г.';
		$arFields[] = $a;
	}
	if (count($arFields) > 0)
	{
		if ($id_report > 0)
		{
			return $arFields[0];
		}
		else
		{
			return $arFields;
		}
	}
	else
	{
		return false;
	}
}

function GetListContractors($creator, $type = 777, $nav = true, $name_nav = '', $sort = array("NAME"=>"ASC"), $name = false, $admin_agent = false, $all = false, $branch = false, $on_page_stat = 0)
{
	$json_data = array();
	if ($nav)
	{
		if (intval($on_page_stat) > 0)
		{
			$ON_PAGE = intval($on_page_stat);
		}
		else
		{
			if (intval($_GET['on_page']) > 0)
			{
				$ON_PAGE = intval($_GET['on_page']);
				$_SESSION['ON_PAGE_COMPS'] = $ON_PAGE;
			}
			else
			{
				if (intval($_SESSION['ON_PAGE_COMPS']) > 0)
				{
					$ON_PAGE = $_SESSION['ON_PAGE_COMPS'];
				}
				else
				{
					$ON_PAGE = 20;
				}
			}	
		}
		$arNav = array("nPageSize" => $ON_PAGE);
	}
	else
	{
		$arNav = false;
	}
	$filter = array("IBLOCK_ID"=>84, "PROPERTY_CREATOR"=> $creator);
	if ((intval($type) > 0) && (intval($type) != 777))
	{
		$filter['PROPERTY_TYPE'] = intval($type);
	}
	if (!$all)
	{
		$filter['ACTIVE'] = "Y";
	}
	if ($name)
	{
		$filter['NAME'] = '%'.$name.'%';
	}
	if ($branch)
	{
		$filter['PROPERTY_BRANCH'] = $branch;
	}
	if ($admin_agent)
	{
        $arclientsids = array();
        $LIST_OF_CLIENTS = AvailableClients(false, false, $creator);
        foreach ($LIST_OF_CLIENTS as $k => $v)
        {
            $arclientsids[] = $k;
        }
        $filter['PROPERTY_CREATOR'] = $arclientsids;
		//unset($filter['PROPERTY_CREATOR']);
	}
	$res = CIBlockElement::GetList(
		$sort, 
		$filter,
		false, 
		$arNav, 
		array("ID","NAME","ACTIVE","PROPERTY_NAME", "PROPERTY_PHONE", "PROPERTY_CITY", "PROPERTY_INDEX", "PROPERTY_ADRESS", "PROPERTY_CITY.NAME", "PROPERTY_CREATOR.NAME")
	);	
	if ($nav)
	{
		$navig = $res->GetPageNavStringEx($navComponentObject,$name_nav,"","Y");
	}
	while($ob = $res->GetNextElement()) {
		$coordArray = array();
		$arFields = $ob->GetFields();
		$coordArray['value'] = $arFields['NAME'].' ['.$arFields['PROPERTY_CITY_NAME'].', '.$arFields['PROPERTY_ADRESS_VALUE'].']'; 
		$coordArray['id'] = $arFields['ID'];
		$coordArray['name'] = $arFields['PROPERTY_NAME_VALUE'];
		$coordArray['phone'] = $arFields['PROPERTY_PHONE_VALUE'];
		$coordArray['city'] = $arFields['PROPERTY_CITY_NAME'];
		$coordArray['city_full'] = GetFullNameOfCity($arFields['PROPERTY_CITY_VALUE']);
		$coordArray['index'] = $arFields['PROPERTY_INDEX_VALUE'];
		$coordArray['company'] = $arFields['NAME'];
		$coordArray['adress'] = $arFields['PROPERTY_ADRESS_VALUE'];
		$coordArray['active'] = $arFields['ACTIVE'];
		$coordArray['creator_name'] = $arFields['PROPERTY_CREATOR_NAME'];
		$json_data[] = $coordArray; 
	}
	if ($nav)
	{
		$arR = array(
			'COMPANIES' => $json_data,
			'NAV_STRING' => $navig
		);
		return $arR;
	}
	else
	{
		return $json_data;
	}
}

function GetManifestXLSwParams($arCells = array(), $pathlocal = '')
{
	if ((count($arCells) > 0) && strlen($pathlocal))
	{
		set_include_path($_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/PhpExcel/Classes/');
		include_once 'PHPExcel.php';
		$pExcel = new PHPExcel();
		$pExcel->setActiveSheetIndex(0);
		$aSheet = $pExcel->getActiveSheet();
		$pExcel->getDefaultStyle()->getFont()->setName('Arial');
		$pExcel->getDefaultStyle()->getFont()->setSize(10);
		$Q = iconv("windows-1251", "utf-8", 'Манифест');
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
		$i = 1;
		$arJ = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA');
		$colsCount = count($arCells[0]) - 1;
		$endLetter = $arJ[$colsCount];
		foreach  ($arCells as $k)
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
		$aSheet->getStyle('B1:'.$endLetter.'1')->applyFromArray($head_style);
		$aSheet->getColumnDimension('A')->setWidth(3);
		for ($m = 1; $m <= $colsCount; $m++)
		{
			$aSheet->getColumnDimension($arJ[$m])->setWidth(17);
		}
		$aSheet->getStyle('B1:'.$endLetter.$i)->getAlignment()->setWrapText(true);
		$aSheet->getStyle('B1:'.$endLetter.$i)->applyFromArray($styleArray);
		$aSheet->getStyle('A1:'.$endLetter.$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
		include_once "PHPExcel/Writer/Excel5.php";
		$objWriter = new PHPExcel_Writer_Excel5($pExcel);
		$objWriter->save($pathlocal);
		return true;
	}
	else
	{
		return false;
	}
}

function MakeInvoicePDF($reqv)
{
	$pdf = new PDF_MC_Table();
	$pdf->AddFont('ArialMT','','arialTM.php');
	$pdf->SetFont('ArialMT','',20);
	$margin = 5;
	$pdf->SetTopMargin($margin);
	$pdf->AddPage('L');
	$pdf->SetFontSize(7);
	
	$pdf->SetWidths(array(278));
	
	$pdf->SetFillColor(100,100,100);
	$pdf->SetTextColor(255,255,255);
	$data = array('Агентский заказ № '.$reqv['PROPERTY_NUMBER_VALUE']);
	$pdf->RowNew($data, 'C', 'DF');
	
	$pdf->SetWidths(array(139,139));
	
	$data = array('Отправитель', 'Получатель');
	$pdf->RowNew($data, 'C', 'DF');
	
	$pdf->SetWidths(array(50,89,50,89));
	
	$pdf->SetFillColor(255,255,255);
	$pdf->SetTextColor (0,0,0);
	
	$data = array('Организация',$reqv['PROPERTY_COMPANY_SENDER_VALUE'],'Организация',$reqv['PROPERTY_COMPANY_RECIPIENT_VALUE']);
	$pdf->Row($data);
	$data = array('Фамилия',$reqv['PROPERTY_NAME_SENDER_VALUE'],'Фамилия',$reqv['PROPERTY_NAME_RECIPIENT_VALUE']);
	$pdf->Row($data);
	$data = array('Телефон',$reqv['PROPERTY_PHONE_SENDER_VALUE'],'Телефон',$reqv['PROPERTY_PHONE_RECIPIENT_VALUE']);
	$pdf->Row($data);
	$data = array('Город',$reqv['PROPERTY_CITY_SENDER'],'Город',$reqv['PROPERTY_CITY_RECIPIENT']);
	$pdf->Row($data);
	$data = array('Индекс',$reqv['PROPERTY_INDEX_SENDER_VALUE'],'Индекс',$reqv['PROPERTY_INDEX_RECIPIENT_VALUE']);
	$pdf->Row($data);
	$data = array('Адрес',$reqv['PROPERTY_ADRESS_SENDER_VALUE'],'Адрес',$reqv['PROPERTY_ADRESS_RECIPIENT_VALUE']);
	$pdf->Row($data);
	
	$pdf->SetWidths(array(278));
	
	$pdf->SetFillColor(100,100,100);
	$pdf->SetTextColor(255,255,255);
	$data = array('Примечание');
	$pdf->RowNew($data, 'C', 'DF');
	
	$pdf->SetFillColor(255,255,255);
	$pdf->SetTextColor(0,0,0);
	
	$data = array($reqv['PROPERTY_INSTRUCTIONS_VALUE']);
	$pdf->Row($data);
	
	$pdf->SetFillColor(100,100,100);
	$pdf->SetTextColor(255,255,255);
	
	$data = array('Информация о плательщике');
	$pdf->RowNew($data, 'C', 'DF');

	$pdf->SetWidths(array(93, 92, 93));
	
	$data = array('Плательщик','Вид оплаты','Сумма к оплате');
	$pdf->RowNew($data, 'C', 'DF');
	
	$pdf->SetFillColor(255,255,255);
	$pdf->SetTextColor(0,0,0);
	
	$data = array($reqv['PROPERTY_DELIVERY_PAYER_VALUE'], $reqv['PROPERTY_TYPE_CASH_VALUE'], $reqv['PROPERTY_PAYMENT_AMOUNT_VALUE']);
	$pdf->Row($data);
	
	$pdf->SetWidths(array(278));
	
	
	
	$pdf->SetFillColor(100,100,100);
	$pdf->SetTextColor(255,255,255);
	
	$data = array('Информация о заказе');
	$pdf->RowNew($data, 'C', 'DF');

	$pdf->SetWidths(array(93, 92, 93));
	
	$data = array('Статус','Номер накладной','Заказ принят');
	$pdf->RowNew($data, 'C', 'DF');
	
	$pdf->SetFillColor(255,255,255);
	$pdf->SetTextColor(0,0,0);
	
	$data = array('', '', '');
	$pdf->Row($data);
	
	$pdf->SetWidths(array(278));
	
	
	$pdf->SetFillColor(100,100,100);
	$pdf->SetTextColor(255,255,255);
	
	$data = array('Дата приезда');
	$pdf->RowNew($data, 'C', 'DF');

	$pdf->SetWidths(array(93, 92, 93));
	
	$data = array('Дата','Время с','Время по');
	$pdf->RowNew($data, 'C', 'DF');
	
	$pdf->SetFillColor(255,255,255);
	$pdf->SetTextColor(0,0,0);
	
	$data = array('', '', '');
	$pdf->Row($data);
	
	$pdf->SetWidths(array(278));
	
	$pdf->SetFillColor(100,100,100);
	$pdf->SetTextColor(255,255,255);
	
	$data = array('Информация о грузе');
	$pdf->RowNew($data, 'C', 'DF');

	$pdf->SetWidths(array(70,69,70,69));
	
	$data = array('Тип груза','Количество','Вес', 'Объемный вес');
	$pdf->RowNew($data, 'C', 'DF');
	
	$pdf->SetFillColor(255,255,255);
	$pdf->SetTextColor(0,0,0);
	
	$data = array($reqv['PROPERTY_TYPE_VALUE'], $reqv['PROPERTY_PLACES_VALUE'], $reqv['PROPERTY_WEIGHT_VALUE'], $reqv['PROPERTY_OB_WEIGHT']);
	$pdf->Row($data);
	
	return $pdf->Output($reqv['PROPERTY_NUMBER_VALUE'].'.pdf','D');
}

function GetIDAgentByINN($inn, $type = false, $onlyone = true, $returnname = false, $uk = false, $onlyactive = false, $real_inn = false)
{
	$a = array();
	$filter = array("IBLOCK_ID" => 40, "PROPERTY_INN" => $inn);
	if ($type)
	{
		$filter['PROPERTY_211'] = $type;
	}
    if ($uk)
    {
        $filter['PROPERTY_UK'] = $uk;
    }
    if ($onlyactive)
    {
        $filter['ACTIVE'] = 'Y';
    }
	$res = CIBlockElement::GetList(
		array("ID" => "asc"),
		$filter, 
		false, 
		false, 
		array("ID","NAME")
	);
	while ($ob = $res->GetNextElement())
	{
		$a[] = $ob->GetFields();
	}
	if ($onlyone)
	{
		if (count($a) == 1)
		{
			return ($returnname) ?  $a[0]['NAME'] : $a[0]['ID'];
		}
		else
		{
			return false;
		}
	}
	else
	{
		if (($real_inn) && (count($a) == 0))
		{
			unset($filter['PROPERTY_INN']);
			$filter['PROPERTY_INN_REAL'] = $inn;
			$res = CIBlockElement::GetList(
				array("ID" => "asc"),
				$filter, 
				false, 
				false, 
				array("ID","NAME")
			);
			while ($ob = $res->GetNextElement())
			{
				$a[] = $ob->GetFields();
			}
		}
		return $a;
	}
}

function GetIDPackageByNumber($number)
{
	$a = array();
	$filter = array("IBLOCK_ID" => 42, "PROPERTY_N_ZAKAZ_IN" => $number);
	$res = CIBlockElement::GetList(
		array("ID" => "asc"),
		$filter, 
		false, 
		false, 
		array("ID")
	);
		while ($ob = $res->GetNextElement())
	{
		$a[] = $ob->GetFields();
	}
	if (count($a) == 1)
	{
		return $a[0]['ID'];
	}
	else
	{
		return false;
	}
}

function ReadFilesManifestsDMSfrom1c($arFiles)
{
	$arOut = array();
	$folder = '/var/www/admin/www/delivery-russia.ru/app/f/manifestos/';
	$name0 = iconv('windows-1251','utf-8','КоммерческаяИнформация');
	$name1 = iconv('windows-1251','utf-8','Манифесты');
	$name2 = iconv('windows-1251','utf-8','Манифест');
	$name3 = iconv('windows-1251','utf-8','Накладная');
	$name4 = iconv('windows-1251','utf-8','НомерНакладной');
	foreach ($arFiles as $f)
	{
		if (is_file($folder.$f))
		{
			$arManifestos = array();
			$arNakls = array();
			$text = file_get_contents($folder.$f);
			$text .= '</'.$name0.'>';
			$res = simplexml_load_string($text);
			$productNames0 = $res->xpath('/'.$name0.'/'.$name1.'/'.$name2);
			for ($i=0;$i<sizeof($productNames0);$i++)
			{
				$pokaz = (array)$productNames0[$i];
				foreach ($pokaz['@attributes'] as $k => $v)
				{
					$k = iconv('utf-8','windows-1251',$k);
					$v = iconv('utf-8','windows-1251',$v);
					$arManifestos[$i][$k] = $v;
				}
				$v = (array)$pokaz[$name3];
				if (isset($v['@attributes']))
				{
					$arManifestos[$i]['Накладные'][] = $v['@attributes'][$name4];
					$arNakls[] = $v['@attributes'][$name4];
				}
				else
				{
					foreach ($v as $vv)
					{
						$vv = (array)$vv;
						$arManifestos[$i]['Накладные'][] = $vv['@attributes'][$name4];
						$arNakls[] = $vv['@attributes'][$name4];
					}
				}
			}
			$arOut['MANS'][] = array(
				'file_link' => $f,
				'Link' => '<a href="http://delivery-russia.ru/app/f/manifestos/'.$f.'" target="_blank">'.$f.'</a>',
				'Manifestos' => $arManifestos,
			);
			
		}
	}
	
	$arStateNakls = array();
	if (count($arNakls) > 0)
	{
		$res = CIBlockElement::GetList(
			array(),
			array("IBLOCK_ID"=> 42, "PROPERTY_N_ZAKAZ_IN" => $arNakls), 
			false, 
			false, 
			array("ID", "PROPERTY_STATE","PROPERTY_N_ZAKAZ_IN", "PROPERTY_RETURN", 'PROPERTY_COST_2', 'PROPERTY_WEIGHT', 'PROPERTY_SIZE_1', 'PROPERTY_SIZE_2', 'PROPERTY_SIZE_3')
		);
		while($ob = $res->GetNextElement())
		{
			$arFields = $ob->GetFields(); 
			$arStateNakls[$arFields['PROPERTY_N_ZAKAZ_IN_VALUE']]['ID'] = $arFields['ID'];
			$arStateNakls[$arFields['PROPERTY_N_ZAKAZ_IN_VALUE']]['STATE_ID'] = $arFields['PROPERTY_STATE_ENUM_ID'];
			$arStateNakls[$arFields['PROPERTY_N_ZAKAZ_IN_VALUE']]['STATE_NAME'] = $arFields['PROPERTY_STATE_VALUE'];
			$arStateNakls[$arFields['PROPERTY_N_ZAKAZ_IN_VALUE']]['RETURN'] = $arFields['PROPERTY_RETURN_VALUE'];
			$arStateNakls[$arFields['PROPERTY_N_ZAKAZ_IN_VALUE']]['COST'] = $arFields['PROPERTY_COST_2_VALUE'];
			$arStateNakls[$arFields['PROPERTY_N_ZAKAZ_IN_VALUE']]['WEIGHT'] = $arFields['PROPERTY_WEIGHT_VALUE'];
			$arStateNakls[$arFields['PROPERTY_N_ZAKAZ_IN_VALUE']]['SIZE_1'] = $arFields['PROPERTY_SIZE_1_VALUE'];
			$arStateNakls[$arFields['PROPERTY_N_ZAKAZ_IN_VALUE']]['SIZE_2'] = $arFields['PROPERTY_SIZE_2_VALUE'];
			$arStateNakls[$arFields['PROPERTY_N_ZAKAZ_IN_VALUE']]['SIZE_3'] = $arFields['PROPERTY_SIZE_3_VALUE'];
			$arStateNakls[$arFields['PROPERTY_N_ZAKAZ_IN_VALUE']]['V_WEIGHT'] = round(floatval(($arFields['PROPERTY_SIZE_1_VALUE']*$arFields['PROPERTY_SIZE_2_VALUE']*$arFields['PROPERTY_SIZE_3_VALUE'])/5000),2);
			$arStateNakls[$arFields['PROPERTY_N_ZAKAZ_IN_VALUE']]['RATE_AGENT'] = 0;
			$arStateNakls[$arFields['PROPERTY_N_ZAKAZ_IN_VALUE']]['COST_AGENT'] = 0;
		}
	}
	$arOut['States'] = $arStateNakls;
	return $arOut;
}

function NewQuotes($text)
{
	//return $text;
    $text = deleteTabs($text);
	$count_qw = substr_count($text,'"');
    $count_zam_qw = 0;
	if ($count_qw > 0)
	{
		$text = htmlspecialcharsEx(trim($text));
	}
	//$text = htmlspecialchars(trim($text));
	$count = substr_count($text,'&quot;');
	if ($count > 0)
	{
		for ($i=0; $i < $count; $i++)
		{
			$mod = ($i % 2);
			$pos = strpos($text, '&quot;');
			if ($mod == 0)
			{
				$text = substr_replace($text, '«', $pos, 6);
                $count_zam_qw++;
				//$text = substr_replace($text, '&laquo;', $pos, 6);
			}
			else
			{
				$text = substr_replace($text, '»', $pos, 6);
                $count_zam_qw++;
				//$text = substr_replace($text, '&raquo;', $pos, 6);
			}
		}
	}
	
	//замена''
	$count = substr_count($text,"''");
	if ($count > 0)
	{
		for ($i=0; $i < $count; $i++)
		{
			$mod = ($i % 2);
			$pos = strpos($text, "''");
			if ($mod == 0)
			{
				$text = substr_replace($text, '«', $pos, 2);
                $count_zam_qw++;
			}
			else
			{
				$text = substr_replace($text, '»', $pos, 2);
                $count_zam_qw++;
			}
		}
	}
	//замена''
	
	//замена'
	$count = substr_count($text,"'");
	if ($count > 0)
	{
		for ($i=0; $i < $count; $i++)
		{
			$pos = strpos($text, "'");
			$text = substr_replace($text, '`', $pos, 1);
            $count_zam_qw++;
		}
	}
	//замена'
    //нечетное кол-во кавычек, необходимо поменять местами
	if ($count_zam_qw%2 != 0)
    { 
        $pos_q1 = strrpos($text, "«");
        $pos_q2 = strrpos($text, "»");
        $text = substr_replace($text, '»', $pos_q1, 1);
        $text = substr_replace($text, '«', $pos_q2, 1);
    }
    //нечетное кол-во кавычек, необходимо поменять местами
	return $text;
	//return html_entity_decode($text);
}

function GetQuarter($month = false)
{
	if ($month)
	{
		$m = intval($month);
	}
	else
	{
		$m = date('n');
	}
	if (in_array($m, array(1,2,3)))
	{
		return 0;
	}
	elseif (in_array($m, array(4,5,6)))
	{
		return 1;
	}
	elseif (in_array($m, array(7,8,9)))
	{
		return 2;
	}
	else
	{
		return 3;
	}
}

/***********информация о филиале клиента***********/
function GetBranch($branch, $client)
{
	$arFields = false;
	$res = CIBlockElement::GetList(
		array("NAME"=>"ASC"), 
		array("IBLOCK_ID" => 89, "ID" => $branch, "PROPERTY_CLIENT" => $client), 
		false, 
		array("nTopCount" => 1), 
		array("ID","NAME","ACTIVE", "PROPERTY_FIO", "PROPERTY_PHONE", "PROPERTY_CITY", "PROPERTY_INDEX", "PROPERTY_ADRESS", "PROPERTY_EMAIL", "PROPERTY_LIMIT", "PROPERTY_IN_1C", "PROPERTY_IN_1C_CODE", "PROPERTY_IN_1C_PREFIX", "PROPERTY_BY_AGENT","PROPERTY_LIMITPERIODS","PROPERTY_BUDGETPERIODS", 'PROPERTY_HEAD_BRANCH')
	);
	if($ob = $res->GetNextElement())
	{
		$a = $ob->GetFields();
		$a['PROPERTY_CITY'] = GetFullNameOfCity($a['PROPERTY_CITY_VALUE']);
		$a['SPENT'] = array(
			0 => 0,
			1 => 0,
			2 => 0,
			3 => 0
		);
		$res_2 = CIBlockElement::GetList(
			array("PROPERTY_QUARTER"=>"ASC"), 
			array("IBLOCK_ID" => 90, "PROPERTY_BRANCH" => $branch, "PROPERTY_CLIENT" => $client, "PROPERTY_YEAR" => date('Y')), 
			false, 
			false, 
			array("ID","PROPERTY_QUARTER", "PROPERTY_SPENT")
		);
		while($ob_2 = $res_2->GetNextElement())
		{
			$b = $ob_2->GetFields();
			if ($b['PROPERTY_QUARTER_ENUM_ID'] == 266)
			{
				$a['SPENT'][0] = $b['PROPERTY_SPENT_VALUE'];
			}
			if ($b['PROPERTY_QUARTER_ENUM_ID'] == 267)
			{
				$a['SPENT'][1] = $b['PROPERTY_SPENT_VALUE'];
			}
			if ($b['PROPERTY_QUARTER_ENUM_ID'] == 268)
			{
				$a['SPENT'][2] = $b['PROPERTY_SPENT_VALUE'];
			}
			if ($b['PROPERTY_QUARTER_ENUM_ID'] == 269)
			{
				$a['SPENT'][3] = $b['PROPERTY_SPENT_VALUE'];
			}
		}
		$arFields = $a;
	}
	return $arFields;
}

function GetLimitsOfBranch($client, $branch, $qw, $year)
{
	$resy = array(
		'SPENT' => 0,
		'LEFT' => 0
	);
	$arQw = array(266, 267, 268, 269);
	$filer = array("IBLOCK_ID" => 90, "PROPERTY_CLIENT" => $client, "PROPERTY_QUARTER" => $arQw[$qw], "PROPERTY_YEAR" => $year);
	if ($branch)
	{
		$filer['PROPERTY_BRANCH'] = $branch;
	}
	$res_2 = CIBlockElement::GetList(
		array("PROPERTY_QUARTER"=>"ASC"), 
		$filer, 
		false, 
		false, 
		array("ID","PROPERTY_SPENT", "PROPERTY_LEFT")
	);
	while($ob_2 = $res_2->GetNextElement())
	{
		$b = $ob_2->GetFields();
		$resy['SPENT'] = $resy['SPENT'] + $b["PROPERTY_SPENT_VALUE"];
		$resy['LEFT'] = $resy['LEFT'] + $b["PROPERTY_LEFT_VALUE"];
	}
	return $resy;
}

function FormArrNaklfrom1c($number, $id_client)
{
  global $USER;
	$arOut = array();
  $u_id = $USER->GetID();
  $agent_array = GetCurrentAgent($u_id);
  $currentip = GetSettingValue(683, false, $agent_array['id']);
  $currentport = intval(GetSettingValue(761, false, $arResult["UK"]));
  $currentlink = GetSettingValue(704, false, $agent_array['id']);
  $login1c = GetSettingValue(705, false, $agent_array['id']);
  $pass1c = GetSettingValue(706, false, $agent_array['id']);
	if (!strlen(trim($currentip)))
	{
		return $arOut;
	}
	if ($currentport > 0) {
		$url = "http://".$currentip.':'.$currentport.$currentlink;
	}
	else {
		$url = "http://".$currentip.$currentlink;
	}
	if ($currentport > 0) {
		$client = new SoapClient($url, array('login' => $login1c, 'password' => $pass1c, 'proxy_host' => $currentip, 'proxy_port' => $currentport, 'exceptions' => false));
	}
	else {
		$client = new SoapClient($url, array('login' => $login1c, 'password' => $pass1c,'exceptions' => false));
	}
	$nnnn = iconv('windows-1251','utf-8', $number);
	$result_1 = $client->GetDocInfo(array("NumDoc" => $nnnn));
	$mResult_1 = $result_1->return;
	$obj_1= json_decode($mResult_1, true);
	
	if (!$obj_1) return false;
	
	$arNakl = array();
	foreach ($obj_1 as $kk => $vv)
	{
		if (is_array($vv))
		{
			foreach ($vv as $kkk => $vvv)
			{
				if (is_array($vvv))
				{
					foreach($vvv as $kkkk => $vvvv)
					{
						$arNakl[iconv('utf-8', 'windows-1251', $kk)][iconv('utf-8', 'windows-1251', $kkk)][iconv('utf-8', 'windows-1251', $kkkk)] = iconv('utf-8', 'windows-1251', $vvvv);
					}
				}
				else
				{
					$arNakl[iconv('utf-8', 'windows-1251', $kk)][iconv('utf-8', 'windows-1251', $kkk)] = iconv('utf-8', 'windows-1251', $vvv);
				}
			}
		}
		else
		{
			$arNakl[iconv('utf-8', 'windows-1251', $kk)] = iconv('utf-8', 'windows-1251', $vv);
		}
	}
	
	$id_branch = false;
	$id_agent = false;
	$id_contract = false;
	if (strlen($arNakl['ЧейЗаказ']))
	{
		$res = CIBlockElement::GetList(
			array("id" => "desc"), 
			array("IBLOCK_ID" => 89, "PROPERTY_CLIENT" => $id_client, "PROPERTY_IN_1C_CODE" => $arNakl['ЧейЗаказКод'], "PROPERTY_IN_1C_PREFIX" => $arNakl['ЧейЗаказПрефикс']),
			false, 
			array("nTopCount" => 1), 
			array(
				"ID", "PROPERTY_BY_AGENT"
			)
		);
		if ($ob = $res->GetNextElement())
		{
			$arFields = $ob->GetFields();
			$id_branch = $arFields["ID"];
			$id_agent = $arFields["PROPERTY_BY_AGENT_VALUE"];
		}
	}
	
	$arContracts = array();
	$res = CIBlockElement::GetList(
		array("id" => "desc"), 
		array("IBLOCK_ID" => 88, "PROPERTY_CLIENT" => $id_client),
		false, 
		false, 
		array(
			"ID"
		)
	);
	while ($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();
		$arContracts[] = $arFields["ID"];
	}
	if (count($arContracts) == 1)
	{
		$id_contract = $arContracts[0];
	}
	$arOut = array(
		'INFO' => $arNakl,
		'AGENT' => $id_agent,
		'BRANCH' => $id_branch,
		'CONTRACT' => $id_contract
	);
	return $arOut;
}


function objectToArray($d, $change_cod = false, $cod_in = "utf-8", $cod_out = "windows-1251")
{
	$out = array();
	$out_cod = array();
	if (is_object($d)) {
		$d = get_object_vars($d);
	}
	if (is_array($d))
	{
		$out = array_map(__FUNCTION__, $d);
	}
	else
	{
		$out = $d;
	}
	if (!$change_cod)
	{
		return $out;
	}
	else
	{
		foreach ($out as $k => $v)
		{
			if (is_array($v))
			{
				foreach ($v as $kk => $vv)
				{
					if (is_array($vv))
					{
						foreach ($vv as $kkk => $vvv)
						{
							if (is_array($vvv))
							{
								foreach ($vvv as $kkkk => $vvvv)
								{
									if (is_array($vvvv))
									{
										foreach ($vvvv as $kkkkk => $vvvvv)
										{
											if (is_array($vvvvv))
											{
												foreach ($vvvvv as $kkkkkk => $vvvvvv)
												{
													$out_cod[$k][$kk][$kkk][$kkkk][$kkkkk][$kkkkkk] = iconv($cod_in, $cod_out, $vvvvvv);
												}
											}
											else
											{
												$out_cod[$k][$kk][$kkk][$kkkk][$kkkkk] = iconv($cod_in, $cod_out, $vvvvv);
											}
											
										}
									}
									else
									{
										$out_cod[$k][$kk][$kkk][$kkkk] = iconv($cod_in, $cod_out, $vvvv);
									}
								}
							}
							else
							{
								$out_cod[$k][$kk][$kkk] = iconv($cod_in, $cod_out, $vvv);
							}
						}
					}
					else
					{
						$out_cod[$k][$kk] = iconv($cod_in, $cod_out, $vv);
					}
				}
			}
			else
			{
				$out_cod[$k] = iconv($cod_in, $cod_out, $v);
			}
		}
		return $out_cod;
	}
}

function MakeWaybillPdf($info, $save = 'F')
{
	$pdf = new PDF_MC_Table();
	$pdf->AddFont('ArialMT','','arialTM.php');
	$pdf->SetFont('ArialMT','',18);
	$pdf->AddPage('P');
	
	$k = 140;
	for ($i = 0; $i <= 1; $i++)
	{
		$pdf->SetFontSize(18);
		$pdf->SetTextColor(0,0,0);
		$pdf->Code39(8, 15 +($i*$k), $info["NAME"], true, false, 0.3, 10);
		$pdf->Text(80, 22+($i*$k), $info["NAME"]);
		
		$pdf->Image($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/image002.jpg', 152, 14+($i*$k), -156, -156);
		$pdf->Image($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/invoice.jpg', 8, 27+($i*$k), -156, -156);
		
		$pdf->SetFontSize(5);
		$pdf->SetTextColor(83,106,194);
		
		$arFields = array(
			array('left' => 14,'top' => 30,'text' => "Фамилия Отправителя / Shipper's Last Name"),
			array('left' => 14,'top' => 37,'text' => "Компания-Отправитель / Shipping Company"),
			array('left' => 14,'top' => 44,'text' => "Страна / Country"),
			array('left' => 14,'top' => 52,'text' => "Город / Sity"),
			array('left' => 14,'top' => 59,'text' => "Адрес / Street Address"),
			array('left' => 14,'top' => 67,'text' => "Фамилия Получателя / Consignee's Last Name"),
			array('left' => 14,'top' => 74,'text' => "Компания-Получатель / Consignee Company"),
			array('left' => 14,'top' => 81,'text' => "Страна / Country"),
			array('left' => 14,'top' => 89,'text' => "Город / Sity"),
			array('left' => 14,'top' => 96,'text' => "Адрес / Street Address"),
			array('left' => 86,'top' => 30,'text' => "Телефон / Phone"),
			array('left' => 86,'top' => 44,'text' => "Область / State"),
			array('left' => 86,'top' => 52,'text' => "Индекс / Postal Code"),
			array('left' => 86,'top' => 67,'text' => "Телефон / Phone"),
			array('left' => 86,'top' => 81,'text' => "Область / State"),
			array('left' => 86,'top' => 89,'text' => "Индекс / Postal Code"),
			array('left' => 23,'top' => 103,'text' => "Документы"),
			array('left' => 23,'top' => 105,'text' => "Documents"),
			array('left' => 40,'top' => 103,'text' => "Не документы"),
			array('left' => 40,'top' => 105,'text' => "Non documents"),
			array('left' => 72,'top' => 103,'text' => "Мест"),
			array('left' => 72,'top' => 105,'text' => "Pieces"),
			array('left' => 85,'top' => 103,'text' => "Вес"),
			array('left' => 85,'top' => 105,'text' => "Weight"),
			array('left' => 99,'top' => 103,'text' => "Габариты (см х см х см)"),
			array('left' => 99,'top' => 105,'text' => "Dimensions (cm x cm x cm)"),
			array('left' => 14,'top' => 130,'text' => "Мест"),
			array('left' => 14,'top' => 132,'text' => "Pieses"),
			array('left' => 37,'top' => 130,'text' => "Вес"),
			array('left' => 37,'top' => 132,'text' => "Weight"),
			array('left' => 55,'top' => 130,'text' => "Объемный вес "),
			array('left' => 55,'top' => 132,'text' => "Vol. WT"),
			array('left' => 75,'top' => 130,'text' => "Контр. взвеш. "),
			array('left' => 75,'top' => 132,'text' => "Control WT"),
			array('left' => 96,'top' => 130,'text' => "Объявл. стоимость"),
			array('left' => 96,'top' => 132,'text' => "Declared Value"),
			array('left' => 126,'top' => 86,'text' => "Тариф за услуги"),
			array('left' => 145,'top' => 86,'text' => "Страховой тариф"),
			array('left' => 172,'top' => 86,'text' => "Итого к оплате"),
			array('left' => 126,'top' => 94,'text' => "Фамилия и подпись отправителя / Shippers Signature"),
			array('left' => 126,'top' => 103,'text' => "Принято курьром"), 
			array('left' => 163,'top' => 103,'text' => "Подпись курьера"),
			array('left' => 126,'top' => 115,'text' => "ДОЛЖНОСТЬ"),
			array('left' => 163,'top' => 115,'text' => "ФАМИЛИЯ ПОЛУЧАТЕЛЯ"),
			array('left' => 126,'top' => 129,'text' => "ПОДПИСЬ ПОЛУЧАТЕЛЯ"),
			array('left' => 163,'top' => 129,'text' => "ДАТА И ВРЕМЯ ДОСТАВКИ"),
		);
		foreach ($arFields as $r)
		{
			$pdf->Text($r['left'], $r['top']+($i*$k), $r['text']);
		}
		$pdf->SetFontSize(6);
		$pdf->SetTextColor(0,0,0);
		$arFields = array(
			array('left' => 133,'top' => 31,'text' => "Экспресс / Express"),
			array('left' => 133,'top' => 34.5,'text' => "Стандарт / Standart"),
			array('left' => 133,'top' => 38,'text' => "Эконом / Econom"),
			array('left' => 133,'top' => 43,'text' => "По адресу"),
			array('left' => 133,'top' => 46.5,'text' => "До востребования"),
			array('left' => 133,'top' => 50.5,'text' => "Лично в руки"),
			array('left' => 129,'top' => 55,'text' => "Доставить в дату:"),
			array('left' => 129,'top' => 62,'text' => "Доставить до часа:"),
			array('left' => 177,'top' => 31,'text' => "Отправитель"),
			array('left' => 177,'top' => 34.5,'text' => "Получатель"),
			array('left' => 177,'top' => 38,'text' => "Другой:"),
			array('left' => 177,'top' => 50.5,'text' => "Служебное"),
			array('left' => 177,'top' => 58.5,'text' => "Наличными"),
			array('left' => 177,'top' => 61.5,'text' => "По счету")
		);
		foreach ($arFields as $r)
		{
			$pdf->Text($r['left'], $r['top']+($i*$k), $r['text']);
		}
		$pdf->SetFontSize(7);
		$pdf->Text(126, 68+($i*$k), "СПЕЦИАЛЬНЫЕ ИНСТРУКЦИИ / SPECIAL INSTRUCTIONS");
		$pdf->SetFontSize(8);
		$topay = ($arResult['INVOICE']['PROPERTY_FOR_PAYMENT_VALUE'] > 0) ? $arResult['INVOICE']['PROPERTY_FOR_PAYMENT_VALUE'] : '';
		switch ($info['PROPERTY_TYPE_PAYS_ENUM_ID'])
		{
			case 252:
				$TYPE_PAYS_top = 35;
				break;
			case 253:
				$TYPE_PAYS_top = 38.5;
				break;
			case 254:
				$TYPE_PAYS_top = 51;
				break;
			default:
				$TYPE_PAYS_top = 31.5;
				break;
		}
		$PAYMENT_top = ($info['PROPERTY_PAYMENT_ENUM_ID'] == 255) ? 58.5 : 62;
		switch ($info['PROPERTY_TYPE_DELIVERY_ENUM_ID'])
		{
			case 243:
				$TYPE_DELIVERY_top = 31.5;
				break;
			case 245:
				$TYPE_DELIVERY_top = 38.5;
				break;
			default:
				$TYPE_DELIVERY_top = 35;
				break;
		}
		switch ($info['PROPERTY_WHO_DELIVERY_ENUM_ID'])
		{
			case 249:
				$WHO_DELIVERY_top = 47;
				break;
			case 250:
				$WHO_DELIVERY_top = 50.5;
				break;
			default:
				$WHO_DELIVERY_top = 43.5;
				break;
		}
		$TYPE_PACK_left = ($info['PROPERTY_TYPE_PACK_ENUM_ID'] == 246) ? 19 : 36.5;
		$arFields = array(
			array('left' => 14,'top' => 33,'text' => $info['PROPERTY_NAME_SENDER_VALUE']),
			array('left' => 14,'top' => 40,'text' => $info['PROPERTY_COMPANY_SENDER_VALUE']),
			array('left' => 14,'top' => 47,'text' => $info['PROPERTY_CITY_SENDER_AR'][2]),
			array('left' => 14,'top' => 55,'text' => $info['PROPERTY_CITY_SENDER_AR'][0]),
			array('left' => 14,'top' => 62,'text' => $info['PROPERTY_ADRESS_SENDER_VALUE']['TEXT']),
			array('left' => 14,'top' => 70,'text' => $info['PROPERTY_NAME_RECIPIENT_VALUE']),
			array('left' => 14,'top' => 77,'text' => $info['PROPERTY_COMPANY_RECIPIENT_VALUE']),
			array('left' => 14,'top' => 84,'text' => $info['PROPERTY_CITY_RECIPIENT_AR'][2]),
			array('left' => 14,'top' => 92,'text' => $info['PROPERTY_CITY_RECIPIENT_AR'][0]),
			array('left' => 14,'top' => 99,'text' => $info['PROPERTY_ADRESS_RECIPIENT_VALUE']['TEXT']),
			array('left' => 86,'top' => 34,'text' => $info['PROPERTY_PHONE_SENDER_VALUE']),
			array('left' => 86,'top' => 47,'text' => $info['PROPERTY_CITY_SENDER_AR'][1]),
			array('left' => 86,'top' => 55,'text' => $info['PROPERTY_INDEX_SENDER_VALUE']),
			array('left' => 86,'top' => 70,'text' => $info['PROPERTY_PHONE_RECIPIENT_VALUE']),
			array('left' => 86,'top' => 84,'text' => $info['PROPERTY_CITY_RECIPIENT_AR'][1]),
			array('left' => 86,'top' => 92,'text' => $info['PROPERTY_INDEX_RECIPIENT_VALUE']),
			array('left' => 72,'top' => 109,'text' => $info['PROPERTY_PLACES_VALUE']),
			array('left' => 85,'top' => 109,'text' => $info['PROPERTY_WEIGHT_VALUE']),
			array('left' => 99,'top' => 109,'text' => $info['PROPERTY_DIMENSIONS_VALUE'][0].' х '.$info['PROPERTY_DIMENSIONS_VALUE'][1].' х '.$info['PROPERTY_DIMENSIONS_VALUE'][2]),
			array('left' => 14,'top' => 136,'text' => $info['PROPERTY_PLACES_VALUE']),
			array('left' => 37,'top' => 136,'text' => $info['PROPERTY_WEIGHT_VALUE']),
			array('left' => 55,'top' => 136,'text' => $info['PROPERTY_OB_WEIGHT']),
			array('left' => 96,'top' => 136,'text' => $info['PROPERTY_COST_VALUE']),
			array('left' => 172,'top' => 90,'text' => $topay),
			array('left' => 126,'top' => 72,'text' => $info['PROPERTY_INSTRUCTIONS_VALUE']['TEXT']),
			array('left' => 151,'top' => 53.5,'text' => $info['PROPERTY_IN_DATE_DELIVERY_VALUE']),
			array('left' => 151,'top' => 61,'text' => $info['PROPERTY_IN_TIME_DELIVERY_VALUE']),
			array('left' => 174,'top' => 44,'text' => $info['PROPERTY_PAYS_VALUE']),
			array('left' => 173,'top' => $TYPE_PAYS_top, 'text' => 'X'),
			array('left' => 173,'top' => $PAYMENT_top, 'text' => 'X'),
			array('left' => 130,'top' => $TYPE_DELIVERY_top, 'text' => 'X'),
			array('left' => 130,'top' => $WHO_DELIVERY_top, 'text' => 'X'),
			array('left' => $TYPE_PACK_left,'top' => 104.5, 'text' => 'X')
		);
		foreach ($arFields as $r)
		{
			$pdf->Text($r['left'], $r['top']+($i*$k), $r['text']);
		}
	}

	
	$name_of_file = $info["NAME"].'.pdf';
	if ($save == 'F')
	{
		$pdf->Output($_SERVER['DOCUMENT_ROOT'].'/upload/waybills/'.$name_of_file,'F');
		return $_SERVER['DOCUMENT_ROOT'].'/upload/waybills/'.$name_of_file;
	}
	else
	{
		return $pdf->Output($name_of_file, $save);
	}
}

function transformDateFrom1c($time, $mk = false)
{
	$day = substr($time, 8, 2);
	$month = substr($time, 5, 2);
	$year = substr($time, 0, 4);
	$hours = substr($time, 11, 2);
	$mins = substr($time, 14, 2);
	if ($mk)
	{
		// echo mktime(intval($hours), intval($mins), 0, intval($month), intval($day), intval($year)).'<br>';
		return mktime(intval($hours), intval($mins), 0, intval($month), intval($day), intval($year));
	}
	return substr($time, 8, 2).'.'.substr($time, 5, 2).'.'.substr($time, 0, 4).'&nbsp;'.substr($time, 11, 5);
}

function addAgentSubscription($type, $sf_EMAIL, $userid)
{
	if ($type == 53)
	{
		CModule::IncludeModule("subscribe");
		$yetsubscr = 0;
		$subscription = CSubscription::GetByEmail($sf_EMAIL);
		if($subscription->ExtractFields("str_"))
		{
			$yetsubscr = (integer)$str_ID;
		}
		if ($yetsubscr > 0)
		{
			$aSubscrRub = array();
			$subscr_rub = CSubscription::GetRubricList($yetsubscr);
			while($subscr_rub_arr = $subscr_rub->Fetch())
			{
				$aSubscrRub[] = $subscr_rub_arr["ID"];
			}
			if (!in_array(4,$aSubscrRub))
			{
				$aSubscrRub[] = 4;
			}
			$subscrarFields = Array(
				"USER_ID" => $userid,
				"SEND_CONFIRM" => "N",
				"CONFIRMED" => "Y",
				"FORMAT" => "html",
				"EMAIL" =>$sf_EMAIL,
				"ACTIVE" => "Y",
				"RUB_ID" => $aSubscrRub
			);
			$subscr = new CSubscription;
			$subscr->Update($yetsubscr, $subscrarFields,"s5");
		}
		else
		{
			$subscrarFields = Array(
				"USER_ID" => $userid,
				"SEND_CONFIRM" => "N",
				"CONFIRMED" => "Y",
				"FORMAT" => "html",
				"EMAIL" =>$sf_EMAIL,
				"ACTIVE" => "Y",
				"RUB_ID" => array(4)
			);
			$subscr = new CSubscription;
			$subscrID = $subscr->Add($subscrarFields,"s5");
		}
	}
	return true;
}

/***Удаление знаков табуляции и переноса строки****/
function deleteTabs($b1)
{  
    $b1 = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $b1);
    $b1 = trim($b1);
    return $b1;
}

/****Смена кодировки массива с UTF на Win-1251*****/
function arFromUtfToWin($obj)
{
    $arRes = array();
    foreach ($obj as $k => $v)
    {
        $k_tr = iconv('utf-8', 'windows-1251', $k);
        if (is_array($v))
        {
            foreach ($v as $kk => $vv)
            {
                $kk_tr = iconv('utf-8', 'windows-1251', $kk);
                if (is_array($vv))
                {
                    foreach ($vv as $kkk => $vvv)
                    {
                        $kkk_tr = iconv('utf-8', 'windows-1251', $kkk);
                        if (is_array($vvv))
                        {
                            foreach ($vvv as $kkkk => $vvvv)
                            {
                                $kkkk_tr = iconv('utf-8', 'windows-1251', $kkkk);
                                if (is_array($vvvv))
                                {
                                    foreach ($vvvv as $kkkkk => $vvvvv)
                                    {
                                        $kkkkk_tr = iconv('utf-8', 'windows-1251', $kkkkk);
                                        if (is_array($vvvvv))
                                        {
                                            foreach ($vvvvv as $kkkkkk => $vvvvvv)
                                            {
                                                $kkkkkk_tr = iconv('utf-8', 'windows-1251', $kkkkkk);
                                                if (is_array($vvvvvv))
                                                {
                                                    foreach ($vvvvvv as $kkkkkkk => $vvvvvvv)
                                                    {
                                                        $kkkkkkk_tr = iconv('utf-8', 'windows-1251', $kkkkkkk);
                                                        $arRes[$k_tr][$kk_tr][$kkk_tr][$kkkk_tr][$kkkkk_tr][$kkkkkk_tr][$kkkkkkk_tr] = iconv('utf-8', 'windows-1251', $vvvvvvv);
                                                    }
                                                }
                                                else
                                                {
                                                    $arRes[$k_tr][$kk_tr][$kkk_tr][$kkkk_tr][$kkkkk_tr][$kkkkkk_tr] = iconv('utf-8', 'windows-1251', $vvvvvv);
                                                }
                                            }
                                        }
                                        else
                                        {
                                            $arRes[$k_tr][$kk_tr][$kkk_tr][$kkkk_tr][$kkkkk_tr] = iconv('utf-8', 'windows-1251', $vvvvv);
                                        }
                                    }
                                }
                                else
                                {
                                    $arRes[$k_tr][$kk_tr][$kkk_tr][$kkkk_tr] = iconv('utf-8', 'windows-1251', $vvvv);
                                }
                            }
                        }
                        else
                        {
                            $arRes[$k_tr][$kk_tr][$kkk_tr] = iconv('utf-8', 'windows-1251', $vvv);
                        }
                    }
                }
                else
                {
                    $arRes[$k_tr][$kk_tr] = iconv('utf-8', 'windows-1251', $vv);
                }
            }
        }
        else
        {
            $arRes[$k_tr] = iconv('utf-8', 'windows-1251', $v);
        }
    }
    return $arRes;
}

/******************Запись в логи*******************/
function AddToLogs($folder = '', $params = array(), $mainfolder = '')
{
    if ((!strlen(trim($folder))) || (!is_array($params)))
    {
        return false;
    }
    if (!strlen(trim($mainfolder)))
    {
        $mainfolder = $_SERVER['DOCUMENT_ROOT'].'/logs';
    }
    if (!file_exists($mainfolder))
    {
        mkdir($mainfolder);
    }
    $mainfolder .= '/'.$folder;
    if (!file_exists($mainfolder))
    {
        mkdir($mainfolder);
    }
    $mainfolder .= '/'.date('Y');
    if (!file_exists($mainfolder))
    {
        mkdir($mainfolder);
    }
    $mainfolder .= '/'.date('m');
    if (!file_exists($mainfolder))
    {
        mkdir($mainfolder);
    }
    $mainfolder .= '/log.txt';
    $file = fopen($mainfolder,'a');
    global $USER;
    $user = "[".$USER->GetID()."] (".$USER->GetLogin().") ".$USER->GetFullName();
    fwrite($file,date('d.m.Y H:i:s').' '.$user."\n");
    $params_str = array();
    foreach ($params as $k => $v)
    {
        $params_str[] = $k.': '.$v;
    }
    fwrite($file,implode("\n",$params_str)."\n");
    fwrite($file,"\n");
    fclose($file);
    return true;
}

function convArrayToUTF($obj) {
    $arRes = array();
    foreach ($obj as $k => $v)
    {
        $k_tr = iconv('windows-1251', 'utf-8', $k);
        if (is_array($v))
        {
            foreach ($v as $kk => $vv)
            {
                $kk_tr = iconv('windows-1251', 'utf-8', $kk);
                if (is_array($vv))
                {
                    foreach ($vv as $kkk => $vvv)
                    {
                        $kkk_tr = iconv('windows-1251', 'utf-8', $kkk);
                        if (is_array($vvv))
                        {
                            foreach ($vvv as $kkkk => $vvvv)
                            {
                                $kkkk_tr = iconv('windows-1251', 'utf-8', $kkkk);
                                if (is_array($vvvv))
                                {
                                    foreach ($vvvv as $kkkkk => $vvvvv)
                                    {
                                        $kkkkk_tr = iconv('windows-1251', 'utf-8', $kkkkk);
                                        if (is_array($vvvvv))
                                        {
                                            foreach ($vvvvv as $kkkkkk => $vvvvvv)
                                            {
                                                $kkkkkk_tr = iconv('windows-1251', 'utf-8', $kkkkkk);
                                                if (is_array($vvvvvv))
                                                {
                                                    foreach ($vvvvvv as $kkkkkkk => $vvvvvvv)
                                                    {
                                                        $kkkkkkk_tr = iconv('windows-1251', 'utf-8', $kkkkkkk);
                                                        $arRes[$k_tr][$kk_tr][$kkk_tr][$kkkk_tr][$kkkkk_tr][$kkkkkk_tr][$kkkkkkk_tr] = iconv('windows-1251', 'utf-8', $vvvvvvv);
                                                    }
                                                }
                                                else
                                                {
                                                    $arRes[$k_tr][$kk_tr][$kkk_tr][$kkkk_tr][$kkkkk_tr][$kkkkkk_tr] = iconv('windows-1251', 'utf-8', $vvvvvv);
                                                }
                                            }
                                        }
                                        else
                                        {
                                            $arRes[$k_tr][$kk_tr][$kkk_tr][$kkkk_tr][$kkkkk_tr] = iconv('windows-1251', 'utf-8', $vvvvv);
                                        }
                                    }
                                }
                                else
                                {
                                    $arRes[$k_tr][$kk_tr][$kkk_tr][$kkkk_tr] = iconv('windows-1251', 'utf-8', $vvvv);
                                }
                            }
                        }
                        else
                        {
                            $arRes[$k_tr][$kk_tr][$kkk_tr] = iconv('windows-1251', 'utf-8', $vvv);
                        }
                    }
                }
                else
                {
                    $arRes[$k_tr][$kk_tr] = iconv('windows-1251', 'utf-8', $vv);
                }
            }
        }
        else
        {
            $arRes[$k_tr] = iconv('windows-1251', 'utf-8', $v);
        }
    }
    return $arRes;
}

function makeManifestOrderfromDMSOrder($arOrder = false , $idorder = 0, $number = '')
{
	$reqv2 = false;
	$arManifestTo1c = false;
	$arFilter = array(
		'ID', 'DATE_CREATE', 'PROPERTY_N_ZAKAZ_IN', 'PROPERTY_STATE', 'PROPERTY_N_ZAKAZ', 'PROPERTY_CREATOR', 'PROPERTY_CITY', 'PROPERTY_CITY.NAME', 'PROPERTY_PREFERRED_TIME', 'PROPERTY_URGENCY_ORDER', 'PROPERTY_DELIVERY_LEGAL', 'PROPERTY_WHEN_TO_DELIVER', 'PROPERTY_CONDITIONS', 'PROPERTY_TIME_PERIOD', 'PROPERTY_RECIPIENT', 'PROPERTY_PHONE', 'PROPERTY_ADRESS', 'PROPERTY_COST_2', 'PROPERTY_PLACES', 'PROPERTY_SIZE_1', 'PROPERTY_SIZE_2', 'PROPERTY_SIZE_3', 'PROPERTY_WEIGHT','PROPERTY_PACK_GOODS'
	);
	$arErrors = array();
	if (is_array($arOrder))
	{
		$reqv2 = $arOrder;
	}
	elseif (intval($idorder) > 0)
	{
		$res2 = CIBlockElement::GetList(
			array("id" => "desc"), 
			array("IBLOCK_ID" => 42, "ID" => intval($idorder)), 
			false, 
			array("nTopCount" => 1), 
			$arFilter

		);
		if ($ob2 = $res2->GetNextElement())
		{
			$reqv2 = $ob2->GetFields();
		}
	}
	elseif (strlen(trim($number)))
	{
		$res2 = CIBlockElement::GetList(
			array("id" => "desc"), 
			array("IBLOCK_ID" => 42, "PROPERTY_N_ZAKAZ_IN" => trim($number)), 
			false, 
			array("nTopCount" => 1), 
			$arFilter

		);
		if ($ob2 = $res2->GetNextElement())
		{
			$reqv2 = $ob2->GetFields();
		}
	}
	else
	{
		$arErrors[] = 'Неверно заданы параметры';
	}
	if (is_array($reqv2))
	{
		if (($reqv2['PROPERTY_STATE_ENUM_ID'] == 54) || ($reqv2['PROPERTY_STATE_ENUM_ID'] == 118))
		{
			$agentInfo = GetCompany($reqv2['PROPERTY_CREATOR_VALUE']);
			$arCitySENDER = explode(',', $agentInfo['PROPERTY_CITY']);
			$arCityRECIPIENT = GetFullNameOfCity($reqv2['PROPERTY_CITY_VALUE'],false,true);
			$comment = trim($reqv2['PROPERTY_PREFERRED_TIME_VALUE']['TEXT']);
			if (intval($reqv2['PROPERTY_URGENCY_ORDER_VALUE']) == 172)
			{
				$comment = strlen($comment) ? 'Срочный заказ! '.$comment : 'Срочный заказ!';
			}
			if (intval($reqv2['PROPERTY_DELIVERY_LEGAL_VALUE']) == 1)
			{
				$comment = strlen($comment) ? 'Необходимо подписать товарную накладную. '.$comment : 'Необходимо подписать товарную накладную.';
			}
			$DATE_TAKE_FROM = $reqv2['DATE_CREATE'];
			$DATE_TAKE_TO = $reqv2['DATE_CREATE'];
			$moths = array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
			foreach ($moths as $k => $m)
			{
				if ($pos = stripos($reqv2['PROPERTY_WHEN_TO_DELIVER_VALUE'],$m))
				{
					$d = str_pad(intval(substr($reqv2['PROPERTY_WHEN_TO_DELIVER_VALUE'],0,2)),2,'0',STR_PAD_LEFT);
					$mf = str_pad(($k+1),2,'0',STR_PAD_LEFT);
					$y = substr($reqv2['PROPERTY_WHEN_TO_DELIVER_VALUE'],($pos+strlen($m)+1),4);
					$DATE_TAKE_FROM = $d.'.'.$mf.'.'.$y;
					$DATE_TAKE_TO = $d.'.'.$mf.'.'.$y;
					break;
				}
			}
			if ($reqv2['PROPERTY_CONDITIONS_ENUM_ID'] == 37)
			{
				switch ($reqv2['PROPERTY_TIME_PERIOD_ENUM_ID'])
				{
					case 215:
						$DATE_TAKE_FROM .= ' 10:00:00';
						$DATE_TAKE_TO .= ' 14:00:00';
						break;
					case 216:
						$DATE_TAKE_FROM .= ' 15:00:00';
						$DATE_TAKE_TO .= ' 18:00:00';
						break;
					default:
						$DATE_TAKE_FROM .= ' 10:00:00';
						$DATE_TAKE_TO .= ' 18:00:00';
				}
			}
			$reqv2['PACK_GOODS'] = '';
			if (strlen($reqv2['PROPERTY_PACK_GOODS_VALUE']))
			{
				$reqv2['PACK_GOODS'] = json_decode(htmlspecialcharsBack($reqv2['PROPERTY_PACK_GOODS_VALUE']), true);
				if (is_array($reqv2['PACK_GOODS']) && (count($reqv2['PACK_GOODS']) > 0))
				{
					foreach ($reqv2['PACK_GOODS'] as $k => $str)
					{
						$reqv2['PACK_GOODS'][$k]['GoodsName'] = iconv('utf-8','windows-1251',$str['GoodsName']);
						if (strlen(trim($reqv2['PACK_GOODS'][$k]['GoodsName'])) == 0)
						{
							unset($reqv2['PACK_GOODS'][$k]);
						}
					}
				}
			}
			$arManifestTo1c = array(
				"DeliveryNote" => $reqv2['PROPERTY_N_ZAKAZ_IN_VALUE'],
				"DATE_CREATE" => $reqv2['DATE_CREATE'],
				"SMSINFO" => 0,
				"INN" => $agentInfo['PROPERTY_INN_VALUE'],
				"NAME_SENDER" => $agentInfo['PROPERTY_RESPONSIBLE_PERSON_VALUE'],
				"PHONE_SENDER" => $agentInfo['PROPERTY_PHONES_VALUE'],
				"COMPANY_SENDER" => $agentInfo['NAME'],
				"CITY_SENDER_ID" => $agentInfo['PROPERTY_CITY_VALUE'],
				"CITY_SENDER" => $agentInfo['PROPERTY_CITY_NAME'],
				"INDEX_SENDER" => '',
				"COUNTRY_SENDER" => $arCitySENDER[2],
				"REGION_SENDER" => $arCitySENDER[1],
				"ADRESS_SENDER" => $agentInfo['PROPERTY_ADRESS_VALUE'],
				"NAME_RECIPIENT" => $reqv2['PROPERTY_RECIPIENT_VALUE'],
				"PHONE_RECIPIENT" => $reqv2['PROPERTY_PHONE_VALUE'],
				"COMPANY_RECIPIENT" => '',
				"CITY_RECIPIENT_ID" => $reqv2['PROPERTY_CITY_VALUE'],
				"CITY_RECIPIENT" => $reqv2['PROPERTY_CITY_NAME'],
				"COUNTRY_RECIPIENT" => $arCityRECIPIENT[2],
				"INDEX_RECIPIENT" => '',
				"REGION_RECIPIENT" => $arCityRECIPIENT[1],
				"ADRESS_RECIPIENT" => $reqv2['PROPERTY_ADRESS_VALUE'],
				"PAYMENT" => 0,
				"PAYMENT_COD" => floatval($reqv2["PROPERTY_COST_2_VALUE"]),
				"DATE_TAKE_FROM" => $DATE_TAKE_FROM,
				"DATE_TAKE_TO" => $DATE_TAKE_TO,
				"DELIVERY_TYPE" => 'С',
				"DELIVERY_PAYER" => 'О',
				"PAYMENT_TYPE" => 'Н',
				"DELIVERY_CONDITION" => ($reqv2['PROPERTY_CONDITIONS_ENUM_ID'] == 38) ? 'Д' : 'А',
				"INSTRUCTIONS" => $comment,
				"TYPE" => 0,	
				"Dimensions" => array(
					array(
					'PLACES' => intval($reqv2['PROPERTY_PLACES_VALUE']),
					'WEIGHT' => floatval($reqv2['PROPERTY_WEIGHT_VALUE']),
					'SIZE_1' => intval($reqv2['PROPERTY_SIZE_1_VALUE']),
					'SIZE_2' => intval($reqv2['PROPERTY_SIZE_2_VALUE']),
					'SIZE_3' => intval($reqv2['PROPERTY_SIZE_3_VALUE']),
					"NAME" => ''
					)
				),
				'ID' => $reqv2['ID'],
				'ID_BRANCH' => '',
				//'Goods' => ''
			);
			if (is_array($reqv2['PACK_GOODS']) && (count($reqv2['PACK_GOODS']) > 0))
			{
				$arManifestTo1c['Goods'] = $reqv2['PACK_GOODS'];
			}
		}
		else
		{
			$arErrors = 'Неверный статус накладной '.$reqv2['PROPERTY_N_ZAKAZ_IN_VALUE'];
		}
	}
	else
	{
		$arErrors = 'Накладная не найдена';	
	}
	return array(
		'errors' => $arErrors,
		'result' => $arManifestTo1c
	);
}

function GetEvents($nakl_id)
{
	$ar = array();
	$res_2 = CIBlockElement::GetList(
		array('PROPERTY_DATE' => 'ASC'), 
		array('IBLOCK_ID' => 30, 'PROPERTY_NUM' => $nakl_id), 
		false, 
		false, 
		array('ID', 'PROPERTY_NUM', 'PROPERTY_EVENT', 'PROPERTY_DATE', 'PROPERTY_DESC', 'PROPERTY_INN', 'PROPERTY_EVENT_DELIVERY')
	);
	while ($ob_2 = $res_2->GetNextElement())
	{
		$ar[] = $ob_2->GetFields();
	}
	return $ar;
}

function IsDeliverd($arTracks, $dost)
{
	$res = false;
	foreach ($arTracks as $t)
	{
		if ($t['PROPERTY_EVENT_DELIVERY_VALUE'] == $dost)
		{
			$res = true;
		}
	}
	return $res;
}

function GetINNs($nakl_id)
{
	$arINN = array();
	$res_2 = CIBlockElement::GetList(
		array('ID' => 'ASC'), 
		array('IBLOCK_ID' => 29, 'PROPERTY_NUMBER' => $nakl_id), 
		false, 
		false, 
		array('PROPERTY_INN')
	);
	while ($ob_2 = $res_2->GetNextElement())
	{
		$ar = $ob_2->GetFields();
		if (!in_array($ar['PROPERTY_INN_VALUE'], $arINN))
		{
			$arINN[] = $ar['PROPERTY_INN_VALUE'];
		}
	}
	return $arINN;
}

function xml2array ( $xmlObject, $out = array () )
{
    foreach ( (array) $xmlObject as $index => $node )
	{
		$out[$index] = ( is_object ( $node ) ) ? xml2array ( $node ) : $node;
	}
    return $out;
}
?>