<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

?>
<div class="container">
    <div style="margin-top: 5.0rem" class="row d-flex flex-row justify-content-center">
        <div class="col-md-6">
            <div class="card text-center">
                <div class="card-header">
                    Оплата заявки
                </div>
                <div class="card-body">
                    <h5 class="card-title">Сумма к оплате - <?=$arResult['summ']?> руб.</h5>
                    <?if($arResult['number_inv'] && !$arResult['number_z']):?>
                        <p class="card-text">Накладная -  <?=$arResult['number_inv']?></p>
                    <?elseif(!$arResult['number_inv'] && $arResult['number_z']):?>
                        <p class="card-text">Заявка -  <?=$arResult['number_z']?></p>
                    <?else:?>
                        <p class="card-text">Накладная/Заявка -  <?=$arResult['number']?></p>

                    <?endif;?>
                    <hr>
                    <div id="btncard" style="margin-top: 2.0rem" class="btn_pay_card d-flex flex-row justify-content-around">
                        <a style="color:#fff" onclick="ipayCheckout({
                            amount:'<?=$arResult['summ']?>',
                            currency:'RUB',
                            order_number:'<?=$arResult['number']?>',
                            description: ''},
                            function(order) { showSuccessfulPurchase(order) },
                            function(order) { showFailurefulPurchase(order) })"
                           class="btn btn-xs btn btn-success">
                            Перейти к оплате
                        </a>
                        <a href="https://newpartner.ru"  class="btn btn-xs btn btn-primary">На сайт</a>
                    </div>

                </div>
                <div class="card-footer text-muted">
                    Оплата осуществляется сервисом Сбербанка
                </div>
            </div>
        </div>
    </div>

</div>