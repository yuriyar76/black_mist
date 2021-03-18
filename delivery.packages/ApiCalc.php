<?php


/**
 *
 * ќбщие правила: названи€ населенных пуктов можно писать как с заглавной, так и с прописной буквы;
 * ƒополнительные уточнени€ - город, облась, деревн€, республика в city_sender и city_recipient добавл€ть не надо.
 * ≈сли отправление идет в/из ћосква или —анкт-ѕетербург, region_sender и/или region_recipient заполн€ть не надо
 * ƒл€ получени€ стоимости доставки с сайта https://newpartner.ru, необходимо
 * послать кодом ниже post запрос, содержащий:
 * - city_sender - город, населенный пункт отправител€
 * - region_sender - область, республика, край, автономный округ ( раснодарский, ярославска€)
 * - city_recipient - город, населенный пункт получател€
 * - region_recipient - область, республика, край, автономный округ (“атарстан, ’анты-ћансийский)
 * ¬ ответ от сервера должен прийти массив в json формате:
 * {
"data":{
"TARIF":"832",
"FULLWEIGTH":"2",
"CITY_FROM":"санкт-петербург, , –осси€",
"CITY_TO":"рыбинск, €рославска€, –осси€"
}
}
 * ≈сли массив придет пустой, это значит - неправильно заполнен массив post
 * или такого нас. пункта нет в справочнике.
 * —в€житесь с нами и мы добавим интересующий населенный пункт в базу
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
