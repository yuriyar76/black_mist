<?php


/**
 *
 * ����� �������: �������� ���������� ������ ����� ������ ��� � ���������, ��� � � ��������� �����;
 * �������������� ��������� - �����, ������, �������, ���������� � city_sender � city_recipient ��������� �� ����.
 * ���� ����������� ���� �/�� ������ ��� �����-���������, region_sender �/��� region_recipient ��������� �� ����
 * ��� ��������� ��������� �������� � ����� https://newpartner.ru, ����������
 * ������� ����� ���� post ������, ����������:
 * - city_sender - �����, ���������� ����� �����������
 * - region_sender - �������, ����������, ����, ���������� ����� (�������������, �����������)
 * - city_recipient - �����, ���������� ����� ����������
 * - region_recipient - �������, ����������, ����, ���������� ����� (���������, �����-����������)
 * � ����� �� ������� ������ ������ ������ � json �������:
 * {
"data":{
"TARIF":"832",
"FULLWEIGTH":"2",
"CITY_FROM":"�����-���������, , ������",
"CITY_TO":"�������, �����������, ������"
}
}
 * ���� ������ ������ ������, ��� ������ - ����������� �������� ������ post
 * ��� ������ ���. ������ ��� � �����������.
 * ��������� � ���� � �� ������� ������������ ���������� ����� � ����
 *
 *
 * Class ApiCalc
 */
class ApiCalc
{
    private $url = 'https://newpartner.ru/tools/calc.php?mode=index&request=Y&api=Y';
    private $post = [];
    private $city_sender;
    private $region_sender;
    private $city_recipient;
    private $region_recipient;
    private $weight;
    private $headers = [
        'headers' => [
            'Content-Type:application/json'],
    ];
    private $postJson;

    /**
     * ApiCalc constructor.
     * @param array $param
     */
    public function __construct(array $param)
    {
         $this->post['city_sender'] = $this->city_sender = $param['city_sender'];
         $this->post['region_sender'] = $this->region_sender = $param['region_sender'];
         $this->post['city_recipient'] = $this->city_recipient = $param['city_recipient'];
         $this->post['region_recipient'] = $this->region_recipient = $param['region_recipient'];
         $this->post['weight'] = $this->weight = $param['weight'];
         $this->postJson = ['data' => json_encode( $this->post)];
    }

    /**
     * @return bool|string
     */
    public function getPrice()
    {
        $c = curl_init($this->url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, $this->postJson);
        curl_setopt($c, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 2);
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 2);
        $data = curl_exec($c);
        curl_close($c);
        return $data;
    }
}
