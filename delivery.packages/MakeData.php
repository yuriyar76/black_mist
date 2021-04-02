<?php

require_once __DIR__ . '/NPAllFunc.php';

class MakeData
{
    public $content = [];
    protected $idClient;
    protected $uCSettings = [];
    public $errors = [];
    public $uCompId = 0;
    public $uCompSettingsId = 0;
    public $client;
  public function __construct($data)
  {
      CModule::IncludeModule("iblock");
      ini_set("soap.wsdl_cache_enabled", "0" );
      ini_set("default_socket_timeout", "300");
      $this->setData($data);
      $this->idClient = $this->content['current_client'];
  }
    /**
     * @param array $data
     * @return $this
     */
    protected function setData(array $data)
    {
        $content = [];
        if(empty($data)){
            $this->errors['err_data'] = 'Нет данных';
            throw new Exception('Нет данных');
        }

        // данные для массового вызова курьера

        if(empty($data['callcourierdate_ids'])){
            $this->errors['err_date'] = 'Не заполнено обязательное поле Дата';
            throw new Exception('Не заполнено обязательное поле Дата');
        }
        foreach($data as $key=>$value){
            if($key === 'data_json'){
                $content['ids'] = json_decode($value, true);
                if(is_array($content['ids'])){
                    foreach($content['ids'] as $k=>$val){
                        $val = htmlspecialchars(strip_tags(trim($val)));
                        $content['ids'][$k] = iconv('utf-8', 'windows-1251', $val);
                    }
                }
            }else{
                $content[$key] =  iconv('utf-8', 'windows-1251',htmlspecialchars(strip_tags(trim($value))));
            }
        }
        $this->content = $content;
        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function currentClient()
    {
        $arrClient = NPAllFunc::GetInfoArr(false, $this->idClient, 40, [
            'ID',
            "NAME",
            "ACTIVE",
            "PROPERTY_INN",
            "PROPERTY_INN_REAL",
            "PROPERTY_UK",
            "PROPERTY_TYPE"
        ]);

        if(empty($arrClient['PROPERTY_UK_VALUE'])){
            $this->errors['err_uk'] = 'Нет УК, Клиент не определен';
            throw new Exception('Нет УК, Клиент не определен');
        }

        $this->arrClient = $arrClient;
        $this->uCompId = $this->arrClient['PROPERTY_UK_VALUE'];
        $arrUK = NPAllFunc::GetInfoArr(false, $this->uCompId, 40, [
            'ID',
            "NAME",
            "ACTIVE",
            "PROPERTY_INN",
            "PROPERTY_INN_REAL",
            "PROPERTY_SETTINGS.ID"
        ]);
        $this->uCompSettingsId = $arrUK['PROPERTY_SETTINGS_ID'];

        $arrUKSettings = NPAllFunc::GetInfoArr(false,  $this->uCompSettingsId, 47, [
            'ID',
            "NAME",
            "ACTIVE",
            "PROPERTY_683",
            "PROPERTY_761",
            "PROPERTY_704",
            "PROPERTY_705",
            "PROPERTY_706",
            "PROPERTY_709",

        ]);
        if(!$arrUKSettings['PROPERTY_683_VALUE'] && !$arrUKSettings['PROPERTY_704_VALUE'] &&
            !$arrUKSettings['PROPERTY_705_VALUE'] && !$arrUKSettings['PROPERTY_706_VALUE']){
            $this->errors['err_settings'] = 'Нет настроек УК, соединение с 1с невозможно';
            throw new Exception('Нет настроек УК, соединение с 1с невозможно');
        }
        $this->uCSettings['ipaddr1c'] =  $arrUKSettings['PROPERTY_683_VALUE'];
        $this->uCSettings['port1c'] =  $arrUKSettings['PROPERTY_761_VALUE'];
        $this->uCSettings['url1c'] =  $arrUKSettings['PROPERTY_704_VALUE'];
        $this->uCSettings['login1c'] =  $arrUKSettings['PROPERTY_705_VALUE'];
        $this->uCSettings['pass1c'] =  $arrUKSettings['PROPERTY_706_VALUE'];
        $this->uCSettings['email'] =  $arrUKSettings['PROPERTY_709_VALUE'];

        return $this;

    }

    /**
     * @return $this
     * @throws SoapFault
     */
    public function soapLink(){
        $currentip = $this->uCSettings['ipaddr1c'];
        $currentport = $this->uCSettings['port1c'];
        $currentlink = $this->uCSettings['url1c'];
        $login1c = $this->uCSettings['login1c'];
        $pass1c =  $this->uCSettings['pass1c'];


        if (!$currentip && !$currentlink && !$login1c && !$pass1c){
            $this->errors['err_link'] = 'Нет соединения с 1с';
            throw new Exception('Нет соединения с 1с');
        }

        if ($currentport > 0) {
            $url = "http://".$currentip.':'.$currentport.$currentlink;
        }
        else {
            $url = "http://".$currentip.$currentlink;
        }
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_TIMEOUT => 10
        ]);
        $header = explode("\n", curl_exec($curl));
        curl_close($curl);
        if (!strlen(trim($header[0]))){
            $this->errors['err_curl'] = 'Проблемы с curl';
            throw new Exception('Проблемы с curl');
        }
        if (strlen(trim($header[0])))
        {
            if ($currentport > 0) {
                $client = new SoapClient($url, ['login' => $login1c, 'password' => $pass1c, 'proxy_host' => $currentip, 'proxy_port' => $currentport, 'exceptions' => false]);
            }
            else {
                $client = new SoapClient($url, ['login' => $login1c, 'password' => $pass1c,'exceptions' => false]);
            }
            $this->client = $client;
        }
        return $this;

    }
}