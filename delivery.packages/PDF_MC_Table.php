<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
    die();
}
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/fpdf17/fpdf.php');
/**
 * Class PDF_MC_Table
 */
class PDF_MC_Table extends FPDF
{
    var $widths;
    var $aligns;
    var $extgstates;

    /**
     * @param $w
     */
    function SetWidths($w)
    {
        $this->widths=$w;
    }

    /**
     * @param $a
     */
    function SetAligns($a)
    {
        $this->aligns=$a;
    }

    /**
     * @param $data
     * @param bool $header
     * @param bool $noborder
     * @param int $mezh
     */
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

    /**
     * @param $data
     * @param string $align
     * @param string $linestyle
     */
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

    /**
     * @param $h
     */
    function CheckPageBreak($h)
    {
        if($this->GetY()+$h>$this->PageBreakTrigger)
        {
            $this->AddPage($this->CurOrientation);
        }
    }

    /**
     * @param $w
     * @param $txt
     * @return int
     */
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

    /**
     * @param $x
     * @param $y
     * @param $code
     * @param bool $ext
     * @param bool $cks
     * @param float $w
     * @param int $h
     * @param bool $wide
     */
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

    /**
     * @param $code
     * @return mixed
     */
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

    /**
     * @param $code
     * @return string
     */
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

    /**
     * @param $code
     * @param $x
     * @param $y
     * @param $w
     * @param $h
     */
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

    /**
     * @param string $orientation
     * @param string $unit
     * @param string $format
     */
    function AlphaPDF($orientation='P', $unit='mm', $format='A4')
    {
        parent::FPDF($orientation, $unit, $format);
        $this->extgstates = array();
    }

    // alpha: real value from 0 (transparent) to 1 (opaque)
    // bm:    blend mode, one of the following:
    //          Normal, Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn,
    //          HardLight, SoftLight, Difference, Exclusion, Hue, Saturation, Color, Luminosity
    /**
     * @param $alpha
     * @param string $bm
     */
    function SetAlpha($alpha, $bm='Normal')
    {
        // set alpha for stroking (CA) and non-stroking (ca) operations
        $gs = $this->AddExtGState(array('ca'=>$alpha, 'CA'=>$alpha, 'BM'=>'/'.$bm));
        $this->SetExtGState($gs);
    }

    /**
     * @param $parms
     * @return int
     */
    function AddExtGState($parms)
    {
        $n = count($this->extgstates)+1;
        $this->extgstates[$n]['parms'] = $parms;
        return $n;
    }

    /**
     * @param $gs
     */
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