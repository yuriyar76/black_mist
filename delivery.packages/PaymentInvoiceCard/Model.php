<?php
include __DIR__ . '/../IndexComponent.php';




abstract class Model extends IndexComponent
{
    public function __construct()
    {
        parent::__construct();

    }

    static public function getDataNumber(ValidateInvoice $obj)
    {
        if(!empty($obj->data['number_z'])){

            $data = NPAllFunc::GetInfoArr(false,false, self::IBLOCK_Z,
                ['ID', 'NAME', 'ACTIVE', 'PROPERTY_*'], ['NAME'=>$obj->data['number_z']]);
            if(empty($data)) throw new Exception('Îøèáêà ïîëó÷åíèÿ äàííûõ èç ìîäåëè' . $obj->data['number_z']);
            return $data;
        }

    }
}