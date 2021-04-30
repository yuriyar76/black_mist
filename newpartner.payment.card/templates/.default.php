<div class="container">
    <div class="row">
        <div style="height: 100vh; margin-top:100px;" class="col-md-6 offset-md-3">
            <?php if($number):
            // dump($delivery_payment);
            /* форма оплаты картой из калькулятора и кабинета */
                if($delivery_payment_type === "CB"){
                    $arrN = [
                        "Контактное лицо", "Номер телефона", "E-mail", "Ваш город", "Ваш адрес", "Вес отправления",
                        "Дата", "Время", "Город получателя", "Адрес получателя", "ФИО получателя", "Номер телефона получателя",
                        "Примечание"
                    ];


                    if($delivery_payment === 'C'){
                        $arrN = [
                            "Заказчик услуги", "Телефон Заказчика", "E-mail Заказчика", "ФИО отправителя", "Город отправителя",
                            "Адрес отправителя",  "Номер телефона отправителя", "ФИО получателя", "Город получателя",
                            "Адрес получателя",  "Номер телефона получателя", "Вес отправления",
                            "Дата", "Время","Примечание"
                        ];
                    }
                }


                /* форма заказать услугу на главной */
                if($delivery_payment_type === 'AB'){
                    $arrN = [
                        "Заказчик услуги", "Организация", "E-mail Заказчика", "ФИО отправителя", "Телефон отправителя",
                        "Город отправителя","Адрес отправителя", "ФИО получателя","Город получателя","Адрес получателя",
                        "Номер телефона получателя", "Вес отправления", "Дата", "Время","Примечание", "Сумма к оплате",
                        "Сроки доставки"];
                }


                $sum_pay = preg_replace('/руб\./', '', $sum_pay);
                $sum_pay = (int) $sum_pay ;
                $desc_pay = "Заявка № ".$number.' '.$desc;

                ?>
                <div class="wrap-pay">

                    <div class="alert alert-success" role="alert">
                        <h4> Оплата заявки <?= $number ?></h4>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tbody>
                        <?foreach($arrN as $key=>$value):?>
                            <tr>
                                <td><?=$arrN[$key]?></td>
                                <td><?=$arrInf[$key];?></td>
                            </tr>
                        <?endforeach;?>
                        </tbody>
                    </table>
                    <?php global $USER; ?>
                    <a onclick="ipayCheckout({
                        amount:'<?=$sum_pay;?>',
                        currency:'RUB',
                        order_number:'<?=$number_pay?>',
                        description: '<?=$desc_pay?>'},
                        function(order) { showSuccessfulPurchase(order) },
                        function(order) { showFailurefulPurchase(order) })"
                       class="btn btn-xs btn btn-outline-success">
                        Оплатить заявку
                    </a>
                    <?if($USER->isAdmin()):?>
                        <a href="/payment/index.php?status=1&orderNumber=<?=$number_pay?>">т</a>
                    <?endif;?>
                </div>
                <br>
            <?else:?>
            <div class="wrap-pay">
                <h1> <?='Нет номера заказа (заявки), оплата не возможна'?></h1>
                <?endif;?>
            </div>
        </div>
    </div>

