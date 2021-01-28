<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
    die();
}
include(__DIR__ . '/InvoiceInterface.php');
include(__DIR__ . '/InvoiceModel.php');
include(__DIR__ . '/PDF_MC_Table.php');
/**
 * Class Invoice
 */

class Invoice extends InvoiceModel implements InvoiceInterface
{
    protected $number;
    protected $number_parent;


    /**
     * Invoice constructor.
     * @param $request
     */
    public function __construct($number)
{
    parent::__construct();
    $this->number = $number;

}

    /**
     * @param $number
     */
    public function getBaseInv(){
        /**
         *  заполнить arResult, date_take_from, date_take_to данными из базовой накладной
         */
        $this->makeArResult();
        /**
         *  CREATOR, data_creator, inn_creator
         */
        $this->dataCreator();
        /**
         *  number, date_create, rec_id - новый Номер - 1, дата создания, id в базе, запись в базу
         */
        $this->makeBaseInvoice();

        return $this;
}
    /**
     * arResult - реверс отправитель - получатель, date_take_from +1, date_take_to +1, spec_instr,
     * @return $this
     * @throws SoapFault
     */
    public function makeReturnInvoice(){
        $arChange = $this->changeRowsReverse();
        CIBlockElement::SetPropertyValuesEx( $this->rec_id, $this->idIblock, $arChange);

        if( $this->arResult['PROPERTY_977'] === 'Y')
        {
            $this->callingCourier();
        }

        return $this;
    }

    /**
     * @return $this
     * @throws SoapFault
     */
    public function callingCourier()
    {
        $this->writeCallCourier();
        $obj = $this->setCallCourier();
        $arRes = $this->arFromUtfToWin($obj);
        $this->changeStatCallCourier($arRes);
        $this->setDocsList();
        return $this;
    }
    /**
     * @return $this
     */
    public function makeInvoicePDF(){
        /**
         *  массив arPDF и изменения в arResult для файла pdf накладной
         */
        $this->makeArrPDF();
        /**
         *  создание и сохранение накладной на диск
         */
        $this->makeZakazPDF();
        return $this;
    }

    public function sendMailCallCourier()
    {
        /* получить файл накладной для отправки */
        $sendFilePath = $_SERVER["DOCUMENT_ROOT"] . "/" . COption::GetOptionString("main", "upload_dir") .
            "/pdf/" . $this->number . ".pdf";
        $fileId = CFile::SaveFile(
            [
                "name" => $this->number.".pdf",
                "tmp_name" => $sendFilePath,
                "old_file" => "0",
                "del" => "N",
                "MODULE_ID" => "",
                "description" => "",
            ],
            'sendfile',
            false,
            false
        );
        $arEventFields = $this->getEventFields();
        $arEventFields['FOR_CACHE']='';
        if($this->payment_type === 'Наличные'){
            $arEventFields['FOR_CACHE'] = "ЗА НАЛИЧНЫЕ";
        }
        $event = new CEvent;
        $event->SendImmediate("NEWPARTNER_LK", "S5", $arEventFields, "N", 220, [$fileId]);
        $arHistory[] = ['date' => date('d.m.Y H:i:s'), 'status' => 316, 'status_descr' => 'Отправлена на почту', 'comment' => $this->arResult['PROPERTY_570']];
        $arHistoryUTF = self::convArrayToUTF($arHistory);
        if( (int)$this->arResult['PROPERTIES_861'] == 1){
            $event->SendImmediate("NEWPARTNER_LK", "S5", $arEventFields, "N", 290);
        }
        CIBlockElement::SetPropertyValuesEx($this->z_id, self::INF_CC,
            ["STATE" => 316,"STATE_HISTORY" => json_encode($arHistoryUTF)]);
        CFile::Delete($fileId);
    }


}