<?php

include __DIR__ . '/../NPAllFunc.php';
include __DIR__ . '/../IndexComponent.php';

class UpdateInvoice extends IndexComponent
{
    protected $data = [];
    protected $dataFrom;
    protected $dataTo;
    protected $idCompany;
    protected $invoiceForUpdate;
    protected $company;
    protected $innCompany;
    protected $settingsId;
    public $newInvoiceId;
    public $updateInvoiceId;
    public $numberInvoice;
    public $propsE;
    public $eventsInvoiceId;
    public $lastStatusEvent;

    public function __construct($post)
    {
        parent::__construct();
        $this->data = $post;
        $this->valid();
        $this->request();
    }

    protected function valid()
    {
        if(!empty($this->data['number'])){
            $this->numberInvoice = $this->data['number'];
        }
        if($this->data['dateFrom'] && $this->data['date_to'] && $this->data['id_company']){
            if(preg_match('/^([0-9]{2}\.{1}){2}[0-9]{4}$/', $this->data['dateFrom'])){
                $this->dataFrom = NPAllFunc::NewQuotes($this->data['dateFrom']);
            }
            if(preg_match('/^([0-9]{2}\.{1}){2}[0-9]{4}$/', $this->data['date_to'])){
                $this->dataTo = NPAllFunc::NewQuotes($this->data['date_to']);
            }
            if(preg_match('/[0-9]+/', $this->data['id_company'])){
                $this->idCompany = NPAllFunc::NewQuotes($this->data['id_company']);
            }

            if(!($this->dataFrom && $this->dataTo)){
                throw new Exception('������ �� ������ ���������');
            }
        }
        if($this->idCompany){
            $arrCompany = NPAllFunc::GetInfoArr(false, $this->idCompany, 40, [
                'ID','NAME', 'ACTIVE', 'PROPERTY_INN_REAL', "PROPERTY_SETTINGS.ID"
            ]);
            $this->company = $arrCompany;
            $this->innCompany = $arrCompany['PROPERTY_INN_REAL_VALUE'];
            $this->settingsId = $arrCompany['PROPERTY_SETTINGS_ID'];
        }


    }

    protected function request()
    {
        $settings = $this->settingsId ? : $this->user["USER"]['SETTINGS_ID'];
        $client = NPAllFunc::soapLink($this->user["USER"]['UK_ID'], $settings);
        if(!$this->numberInvoice){
            $arParamsJson = [
                'INN' => $this->innCompany,
                'BranchID' => '',
                'BranchPrefix' =>  '',
                'StartDate' =>  date('Y-m-d',strtotime($this->dataFrom)),
                'EndDate' =>  date('Y-m-d',strtotime($this->dataTo)),
                'NumPage' => 0,
                'DocsToPage' => 100000
            ];

            $result = $client->GetDocsListClient($arParamsJson);
            $mResult = $result->return;
            if(!$mResult) throw new Exception('��� ������ �� 1�');
            $obj = json_decode($mResult, true);
            $obj = NPAllFunc::arrUtfToWin($obj);
            $this->invoiceForUpdate = $obj;
        }
        if($this->numberInvoice){
            $num = iconv('windows-1251','utf-8', $this->numberInvoice);
            $result = $client->GetDocInfo(["NumDoc" => $num]);
            $mResult = $result->return;
            if(!$mResult) throw new Exception('��� ������ �� 1�');
            $obj= json_decode($mResult, true);
            $obj = NPAllFunc::arrUtfToWin($obj);
            $this->invoiceForUpdate = $obj;
        }
    }

    public function update()
    {
        set_time_limit(0);
        foreach( $this->invoiceForUpdate['Docs'] as $value){
            $events = json_encode(NPAllFunc::convArrToUTF(['Events'=>$value['Events']]));
            $Dimensions = json_encode(NPAllFunc::convArrToUTF(['Dimensions'=>$value['Dimensions']]));
            $dateDoc = date('d.m.Y',strtotime($value['DateDoc']));
            $props = [
                760 => $value['NumDoc'], //'90-3006235',
                1099 => $value['NumID'], //'����00001537706',
                1137 => $value['UID'], // uid  bcaecb39-b4a1-11eb-a2ab-000c29cf960f
                1133 => $value['ID'], //59910380,
                1100 => $value['NumRequest'], //'����-01552',
                1101 => $value['Manager'], //'������ �������',
                1102 => $value['DateOfCompletion'], //'2020-12-01T00:00:00',
                1103 => $value['CitySender'], //8054,
                1104 => $value['CityRecipient'], //8054,
                1105 => $value['AdressRecipient'], //'������, ���������, 29, ��.43',
                1106 => $value['CompanySender'], //'��� �������� �����������',
                1107 => $value['CompanyRecipient'], //'�������-������',
                1108 => $value['NameSender'], //'�������� ��',
                1109 => $value['NameRecipient'], //'���������',
                1110 => $dateDoc, //'',
                1111 => $value['Date_Change'], //'2020-12-03T15:08:38',
                1112 => $value['Tarif'], //163,
                1113 => $value['TransitMoscow'], //'',
                1114 => $value['ZakazName'], //'������� �����������',
                1115 => $value['ZakazId'], //'��0002306',
                1116 => $value['Delivery_Type'], //'��������',
                1117 => $value['Delivery_Payer'], //'�����������',
                1118 => $value['Payment_Type'], //'�����������',
                1119 => $value['Delivery_Condition'], //'�� ������',
                1120 => $value['InternalNumber'], //'',
                1121 => $value['CENTER_EXPENSES'], //'���',
                1122 => $value['INN_SENDER'], //'',
                1123 => $value['INN_RECIPIENT'], //'',
                1124 => $value['Delivery_Weight'], //0.1,
                1125 => $value['Delivery_Payment'], //0,
                1126 => $value['Delivery_Act'], //'� 3751 �� 31.12.2020',
                1127 => $value['DateDoc'], //'2020-11-30T17:38:47',
                1128 => $value['Date_Delivered'], //'03.12.2020',
                1129 => $value['Time_Delivered'], //'13:14:27',
                1130 => $value['Signature_Delivered'], //'��� �������',
                1131 => $events,
                1132 => $Dimensions,
                1134 => $this->idCompany,
                1135 => $this->innCompany,
                1136 => $value['FilesPath'],  // scan docs

            ];

             $arrSearch = NPAllFunc::GetInfoArr(false, false, 98, ['ID', 'NAME', 'ACTIVE'],
            ["PROPERTY_1137"=>$value['UID']], true, false);
            if(!empty($arrSearch['ID'])){
                $idEl = (int)$arrSearch['ID'];
                if($arrSearch['NAME'] != $value['NumDoc']){
                    $arLoad = ["NAME"  => $value['NumDoc']];
                    $el = new CIBlockElement;
                    $el->Update($idEl, $arLoad);
                }
                CIBlockElement::SetPropertyValuesEx($idEl, 98, $props);
                $arIdElUpd[] = $idEl;
            }else{
                $elnew = new CIBlockElement;
                $data = [
                    "IBLOCK_ID" => 98,
                    "IBLOCK_SECTION_ID" => false,
                    "NAME" => $value['NumDoc'],
                    "ACTIVE" => "Y"
                ];

                if($idnew = $elnew->add($data)){
                    CIBlockElement::SetPropertyValuesEx($idnew, 98, $props);
                    $this->newInvoiceId[] = $idnew;
                }
            }
        }

    }

    public function updateInvoice()
    {
        $data = $this->invoiceForUpdate;
        $events = json_encode(NPAllFunc::convArrToUTF(['Events'=>$data['�������']]));
        $cEvents = count($data['�������']);
        $statLastEvent = $data['�������']['Event_' . $cEvents]['Event'] . ' ' .
            $data['�������']['Event_' . $cEvents]['InfoEvent'] . ' ' .
            $data['�������']['Event_' . $cEvents]['DateEvent'];
        $this->lastStatusEvent = iconv('windows-1251', 'utf-8',$statLastEvent);
        $props = [
            760 => $data['��������������'], //'90-3006235',
            1133 => $data['ID������'], //59910380,
            1100 => $data['�����������'], //'����-01552',
            1101 => $data['�������������'], //'������ �������',
            1111 => $data['�������������'], //'2020-12-03T15:08:38',
            1112 => $data['�������������'], //163,
            1124 => $data['�����������'], //0.1,
            1131 => $events,
            1121 => $data['�����������']
          ];
        $this->propsE = $props;
        $arrSearch = NPAllFunc::GetInfoArr(false, false, 98, ['ID', 'NAME', 'ACTIVE'],
            ["NAME"=>$props[760]], true, false);
        if(!$arrSearch['ID']) throw new Exception('���������� �� �������, ���������� �����.');

            $idEl = (int)$arrSearch['ID'];
            CIBlockElement::SetPropertyValuesEx($idEl, 98, $props);
            $this->updateInvoiceId = $idEl;
            $this->eventsInvoiceId = $events;
            $this->numberInvoice = $props[760];
    }

}


/*
         [invoiceForUpdate:protected] => Array
       (
           [����������������] => ��� ������� �����������
           [������������������] => ���������� ��
           [�������������������] => ��� ������� �����������
           [������������������] => +749502577772432
           [�����������������] => 115280
           [�����������������] => ������
           [������������������] => ������
           [����������������] => ������
           [����������������] => ��. ��������� �������, 26
           [�����������������] => 0
           [������������������] => 0
           [����������������������] => CMC
           [���������������������] =>
           [���������������] => ��� ������������� ���������� ������������� ����
           [�����������������] => ������� ��
           [������������������] => ��� ������������� ���������� ������������� ����
           [�����������������] => 89143262698
           [����������������] =>
           [����������������] => ������
           [�����������������] => ���������� ����
           [���������������] => �����������
           [���������������] => ���������, 16
           [����������������] => 0
           [�����������������] => 0
           [���������������������] =>
           [��������������������] =>
           [����������������] => 1
           [�������������������] =>  ���������
           [��������������������] =>
           [�������������������] =>
           [������������������] => ��������
           [������������������] => �� ������
           [���������������] =>
           [��������������] =>
           [�����������������] => �����������
           [����������������] => �����������
           [���������������] => 0
           [���������������������] => ����� �������: 07.04.2021 � 10:00 �� 18:00.
           [���������������������] => 2021-04-13T00:00:00
           [�����������] =>
           [�������������] => ������ �������
           [�������] =>
           [��������������������] => 2021-04-07T00:00:00
           [��������������] => 90-3505096
           [���������] =>
           [�����������] => ���  "����� �������"
           [���������������] =>
           [��������������] => 0
           [������������] => 0
           [�����������ID] => ecf0f0fb-9776-11eb-a2a5-000c29cf960f
           [������������] =>
           [�����������] =>
           [�����������] =>
           [�������������] => ����00001725611
           [������������] =>
           [�����������������������] =>
           [SMS���������] =>
           [���������������������] =>
           [�����������������] =>
           [����������������] => 0
           [��������������������������������] =>
           [������������] => 0
           [������������������������] => 0
           [��������] => ������� �����������
           [��������������] => ������� �����������
           [���������] =>
           [�������������������] =>
           [��������������������] =>
           [�������������] =>
           [���������������] =>
           [�����������] => ����-05765
           [�������������] => 1
           [�����������������������������������������] =>
           [������] =>
           [������������] =>
           [ID������] => 65387552
           [GUID������] =>
           [����������������] =>
           [������������������] => 0
           [������������] =>
           [�������������] => 2021-05-05T11:05:39
           [�������] =>
           [�����] =>
           [����������] =>
           [�����] =>
           [�������������] =>
           [������������] => 2021-04-08T00:00:00
           [������������] =>
           [��������������] =>
           [���������������] =>
           [������������] => 0001-01-01T10:00:00
           [�������������] => 0001-01-01T18:00:00
           [������] =>
           [�������] =>
           [SMS��������������] => 0
           [���������������������] => 0
           [������������] =>
           [�����������] => 0.1
           [�������������������] => 0
           [���������������������] =>
           [������������] =>
           [����������������������] =>
           [�����������������������] =>
           [�������������������] =>
           [������������������������] =>
           [��] =>
           [������������������] =>
           [��1] =>
           [��2] =>
           [��3] =>
           [���������������] => �����
           [����������] =>
           [�����������] => ���-3
           [��������������] =>
           [�������������] =>
           [����] => 2021-04-07T11:07:39
           [�����������] => ��0002306
           [���������������] => ����
           [�������������] => 450.00
           [��������] => Array
               (
                   [�������_1] => Array
                       (
                           [�����] => 0
                           [��������������] => 1
                           [��������������] => 0.1
                           [����������������������] => 0.1
                           [������] => 0
                           [������] => 0
                           [�������] => ���������
                       )

                   [�������_2] => Array
                       (
                           [�����] => 0
                           [��������������] => 0
                           [��������������] => 0
                           [����������������������] => 0
                           [������] => 0
                           [������] => 0
                           [�������] =>
                       )

                   [�������_3] => Array
                       (
                           [�����] => 0
                           [��������������] => 0
                           [��������������] => 0
                           [����������������������] => 0
                           [������] => 0
                           [������] => 0
                           [�������] =>
                       )

                   [�������_4] => Array
                       (
                           [�����] => 0
                           [��������������] => 0
                           [��������������] => 0
                           [����������������������] => 0
                           [������] => 0
                           [������] => 0
                           [�������] =>
                       )

                   [�������_5] => Array
                       (
                           [�����] => 0
                           [��������������] => 0
                           [��������������] => 0
                           [����������������������] => 0
                           [������] => 0
                           [������] => 0
                           [�������] =>
                       )

               )

           [���������] => Array
               (
                   [���_1] => 7700000001
                   [���_2] => 2543031446
               )

           [�������] => Array
               (
                   [Event_1] => Array
                       (
                           [DateEvent] => 07.04.2021
                           [TimeEvent] => 11:09
                           [Event] => ������ ������� �� �������
                           [InfoEvent] =>
                           [INN] => 7700000001
                       )

                   [Event_2] => Array
                       (
                           [DateEvent] => 07.04.2021
                           [TimeEvent] => 17:35
                           [Event] => ������������ �������
                           [InfoEvent] => ������
                           [INN] => 7700000001
                       )

                   [Event_3] => Array
                       (
                           [DateEvent] => 07.04.2021
                           [TimeEvent] => 22:06
                           [Event] => ������������
                           [InfoEvent] => �����������
                           [INN] => 7700000001
                       )

                   [Event_4] => Array
                       (
                           [DateEvent] => 07.04.2021
                           [TimeEvent] => 23:00
                           [Event] => ���������� � �����
                           [InfoEvent] => �����������
                           [INN] => 7700000001
                       )

                   [Event_5] => Array
                       (
                           [DateEvent] => 12.04.2021
                           [TimeEvent] => 12:00
                           [Event] => ����������
                           [InfoEvent] => ��������
                           [INN] => 2543031446
                       )

               )

       )
              * */