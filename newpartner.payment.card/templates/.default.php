<div class="container">
    <div class="row">
        <div style="height: 100vh; margin-top:100px;" class="col-md-6 offset-md-3">
            <?php if($number):
            // dump($delivery_payment);
            /* ����� ������ ������ �� ������������ � �������� */
                if($delivery_payment_type === "CB"){
                    $arrN = [
                        "���������� ����", "����� ��������", "E-mail", "��� �����", "��� �����", "��� �����������",
                        "����", "�����", "����� ����������", "����� ����������", "��� ����������", "����� �������� ����������",
                        "����������"
                    ];


                    if($delivery_payment === 'C'){
                        $arrN = [
                            "�������� ������", "������� ���������", "E-mail ���������", "��� �����������", "����� �����������",
                            "����� �����������",  "����� �������� �����������", "��� ����������", "����� ����������",
                            "����� ����������",  "����� �������� ����������", "��� �����������",
                            "����", "�����","����������"
                        ];
                    }
                }


                /* ����� �������� ������ �� ������� */
                if($delivery_payment_type === 'AB'){
                    $arrN = [
                        "�������� ������", "�����������", "E-mail ���������", "��� �����������", "������� �����������",
                        "����� �����������","����� �����������", "��� ����������","����� ����������","����� ����������",
                        "����� �������� ����������", "��� �����������", "����", "�����","����������", "����� � ������",
                        "����� ��������"];
                }


                $sum_pay = preg_replace('/���\./', '', $sum_pay);
                $sum_pay = (int) $sum_pay ;
                $desc_pay = "������ � ".$number.' '.$desc;

                ?>
                <div class="wrap-pay">

                    <div class="alert alert-success" role="alert">
                        <h4> ������ ������ <?= $number ?></h4>
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
                        �������� ������
                    </a>
                    <?if($USER->isAdmin()):?>
                        <a href="/payment/index.php?status=1&orderNumber=<?=$number_pay?>">�</a>
                    <?endif;?>
                </div>
                <br>
            <?else:?>
            <div class="wrap-pay">
                <h1> <?='��� ������ ������ (������), ������ �� ��������'?></h1>
                <?endif;?>
            </div>
        </div>
    </div>

