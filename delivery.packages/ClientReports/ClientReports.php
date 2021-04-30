<?php
include __DIR__ . '/../NPAllFunc.php';
include __DIR__ . '/../IndexComponent.php';
include($_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/PhpExcel/Classes/PHPExcel.php');
include($_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/PhpExcel/Classes/PHPExcel/Writer/Excel5.php');

class ClientReports extends IndexComponent
{
    protected $get = [];
    protected $data = [];
    protected $numbers = [];
    public $dataJson;

    public function __construct(array $data, array $get=[])
    {
        parent::__construct();
        $this->get = $get;
        $this->setData($data);
    }

    protected function setData(array $data)
    {
        if ($this->get['report_as'] === 'Y'){
            if(!$data) throw new Exception('Нет данных, операция невозможна.');
            $result = json_decode($data['numbersphp'], true);
            foreach($result as $value){
                $key = $value['NAME'];
                $res[$key] = htmlspecialcharsEx($value);
                $numbers[] = $value['NAME'];
            }
            $this->data = NPAllFunc::arrUtfToWin($res);
            $this->numbers = NPAllFunc::arrUtfToWin($numbers);
            $this->updateDataBase();
        }

    }

    protected function updateDataBase()
    {
        $arFilter = [
            "NAME" => $this->numbers,
            "ACTIVE" => "Y"
        ];
        $arSelect = [
            "ID", "NAME", "PROPERTY_SUMM_DEV", "PROPERTY_CENTER_EXPENSES.NAME"
        ];
        $resArr =  NPAllFunc::GetInfoArr(false, false, 83, $arSelect, $arFilter, false);
        foreach($resArr as $key => $value){
                $this->data[$value['NAME']]['PROPERTY_SUMM_DEV_VALUE'] = $this->data[$value['NAME']]['tarif'];
                CIBlockElement::SetPropertyValuesEx($value['ID'], 83,
                    [
                        979 => $this->data[$value['NAME']]['tarif']
                    ]);
                $this->data[$value['NAME']]['PROPERTY_CENTER_EXPENSES_NAME'] = $value['PROPERTY_CENTER_EXPENSES_NAME'];
        }

    }

    public function repoEx()
{
    if ($this->get['report_as'] === 'Y'){
        $Result = NPAllFunc::convArrToUTF ($this->data);
        $arData = [];
        $arData[] =
            [ iconv('windows-1251', 'utf-8','Номер накладной'),
                iconv('windows-1251', 'utf-8','Дата формирования'),
                iconv('windows-1251', 'utf-8','Статус'),
                iconv('windows-1251', 'utf-8','Город получателя'),
                iconv('windows-1251', 'utf-8','Получатель'),
                iconv('windows-1251', 'utf-8','Компания получателя'),
                iconv('windows-1251', 'utf-8','ИНН Получателя'),
                iconv('windows-1251', 'utf-8','Город отправителя'),
                iconv('windows-1251', 'utf-8','Отправитель'),
                iconv('windows-1251', 'utf-8','Компания отправителя'),
                iconv('windows-1251', 'utf-8','ИНН отправителя'),
                iconv('windows-1251', 'utf-8','Центр затрат'),
                iconv('windows-1251', 'utf-8','Тариф (руб.)'),
                iconv('windows-1251', 'utf-8','Вес')
            ];
        $i = 1;
        foreach ($Result as $value){
            $arData[$i] = [
                $value['NAME'],
                $value['DATE_CREATE'],
                $value['state_text'],
                $value['PROPERTY_CITY_RECIPIENT_NAME'],
                $value['PROPERTY_NAME_RECIPIENT_VALUE'],
                $value['PROPERTY_COMPANY_RECIPIENT_VALUE'],
                $value['PROPERTY_INN_RECIPIENT_VALUE'],
                $value['PROPERTY_CITY_SENDER_NAME'],
                $value['PROPERTY_NAME_SENDER_VALUE'],
                $value['PROPERTY_COMPANY_SENDER_VALUE'],
                $value['PROPERTY_INN_SENDER_VALUE'],
                $value['PROPERTY_CENTER_EXPENSES_NAME'],
                $value['tarif'],
                $value['PROPERTY_WEIGHT_VALUE'],
            ];
            $i++;
        }

        $pExcel = new PHPExcel();
        $pExcel->setActiveSheetIndex(0);
        $aSheet = $pExcel->getActiveSheet();
        $pExcel->getDefaultStyle()->getFont()->setName('Arial');
        $pExcel->getDefaultStyle()->getFont()->setSize(10);
        $Q = iconv("windows-1251", "utf-8", 'Накладные');
        $aSheet->setTitle($Q);
        $head_style = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ]
        ];
        $i = 1;
        $arJ = ['A','B','C','D','E','F','G','H','I','J','K','L'];

        foreach  ($arData as $items)
        {

            $n = 0;
            foreach ($items as $val)
            {

                $num_sel = $arJ[$n].$i;
                $aSheet->setCellValue($num_sel,$val);
                $n++;
            }
            $i++;
        }
        $i--;
        foreach ($arJ as $cc)
        {
            $aSheet->getColumnDimension($cc)->setWidth(17);
        }
        $aSheet->getStyle('A1:L1')->applyFromArray($head_style);
        $aSheet->getStyle('A1:L'.$i)->getAlignment()->setWrapText(true);
        $aSheet->getStyle('A1:L'.$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
        $objwriter = new PHPExcel_Writer_Excel5($pExcel);
        $path = $_SERVER['DOCUMENT_ROOT'] . "/report_" . date('d.m.Y').'.xls';
        $objwriter->save($path);
        $pathutf = iconv('windows-1251', 'utf-8',"/report_" . date('d.m.Y').'.xls');
        $dataJson = [
            'path' => $pathutf
        ];
        $this->dataJson = json_encode($dataJson);
    }

    return $this;
}

}