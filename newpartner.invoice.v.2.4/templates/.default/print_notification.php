<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
    die();
}
//dump( $arResult['INVOICE']);
$adress = $arResult['INVOICE']['ADRESS'];

?>

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
<style>
   #print_notifycation .print_head_block {
        width: 1060px;
        overflow: hidden;
        position: relative;
        height: 280px;
        margin-top: 50px;
    }
   #print_notifycation .print_head_block .img_print_head {
        width: 80%;
        height: 299px;
        position: absolute;
        top: -30px;
        right: 0;
    }
   #print_notifycation div.mainBlock {
        -webkit-filter: blur(0);
        font-family: TimesNewRoman, sans-serif;
        font-size: 12px;
        color: black;
        width: 950px;
        page-break-inside: avoid;
    }

   #print_notifycation .logo_print_head,  #print_notifycation .seal, #print_notifycation .signature{
        position: relative;
    }
   #print_notifycation .img_print_head img{
        width: 84%;
    }
   #print_notifycation .logo_print_head img{
        display: block;
        position: absolute;
        top: 113px;
        width: 44%;
        right: 262px;
    }
   #print_notifycation .requisites{
        margin-top: 70px;
    }
   #print_notifycation .requisites_text{
        color: #0060aa;
        font-size: 12px;
    }
   #print_notifycation .devider{
        height: 100px;
    }
   #print_notifycation .mainBlock_title{
        display: flex;
        flex-direction: row;
        justify-content: center;
    }
   #print_notifycation .mainBlock_content p, #print_notifycation .mainBlock_footer p{
        font-size: 14px;
        font-weight: bold;
    }
   #print_notifycation .prolog_footer{
        display: flex;
        flex-direction: row;
        justify-content: space-between;
    }

   #print_notifycation .seal img{
       display: block;
       width: 100%;
       position: absolute;
       top: -38px;
       left: -48px;
    }

   #print_notifycation .signature img{
        width: 100%;
        position: absolute;
        top: -62px;
        left: 35px;
        z-index: 10;
    }
</style>
<?php if (!empty($arResult)):?>
    <div id="print_notifycation" class="container">
    <div class="row devider">
        <div id="print_block" class="mainBlock">
            <div class="print_head_block">
                <div class="img_print_head">
                    <img  src="/upload/img/logo-wave.png">
                </div>
                <div class="logo_print_head">
                    <img  src="/upload/img/logo-new.png">
                </div>
            </div>
            <div class="requisites">
                <p class="requisites_text">
                    109548 г. Москва, Шоссейный пр-д, д. 10 корп.1, (495) 663-99-18, www.newpartner.ru <br>
                    ИНН 7717739535, КПП 772301001

                </p>
            </div>
            <div class="devider"></div>
            <div class="mainBlock_title ">
                <h2>Уведомление</h2>
            </div>
            <br>
            <div class="mainBlock_content">
                <p>
                    Настоящим уведомляем, что отправление, принятое <?=$arResult[1]?> по накладной № <?=$arResult[0]?>
                    для доставки по адресу: <?=$arResult['INVOICE']['CITY']?>, <?=$adress;?>

                </p>
                <br>
                <p>
                    Для: <?=$arResult[4]?>
                </p>
                <br>
                <p>
                    Было доставлено: <?=$arResult[3]?>
                    <br>
                    <?php if(!empty($arResult['INVOICE']['PACK_DESC'])):?>
                    Описание отправления: <?=$arResult['INVOICE']['PACK_DESC']?>
                    <br>
                    <?php endif;?>
                    В накладной расписался: <?=$arResult[2]?>

                </p>
            </div>
            <div class="devider"></div>
            <div class="mainBlock_footer">
                <div class="prolog">
                    <p>Отследить выполнение Вашего заказа (накладной) Вы можете на сайте
                        <a href="https://newpartner.ru">newpartner.ru</a>
                    </p>
                </div>
                <br>
                <div class="prolog">
                    <p>Спасибо за обращение в нашу компанию!</p>
                </div>
                <div class="devider"></div>
                <div class="prolog_footer">
                    <div class="data_prolog_footer">
                        <p>
                            <?php echo substr($arResult[3], 0, 10);

                            ?>
                        </p>
                    </div>
                    <div class="signature_prolog_footer">
                        <p style="margin-right: 150px;">Подпись</p>
                        <div class="signature">
                            <img src="/upload/img/signature.png" alt="">
                        </div>
                        <br>
                        <p style="margin-left: 150px;">М.П.</p>
                        <div class="seal">
                            <img src="/upload/img/seal.png" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<?php else:?>
    <div id="print_notifycation" class="container">
        <div class="row devider">
            <div id="print_block" class="mainBlock">
                <div class="print_head_block">
                    <div class="img_print_head">
                        <img  src="/upload/img/logo-wave.png">
                    </div>
                    <div class="logo_print_head">
                        <img  src="/upload/img/logo-new.png">
                    </div>
                </div>
                <div class="requisites">
                    <p class="requisites_text">
                        109548 г. Москва, Шоссейный пр-д, д. 10 корп.1, (495) 663-99-18, www.newpartner.ru <br>
                        ИНН 7717739535, КПП 772301001

                    </p>
                </div>
                <div class="devider"></div>
                <div class="mainBlock_title ">
                    <h2>Нет данных для отображения</h2>
                </div>
                <br>
             </div>
        </div>

    </div>
<?php endif;?>
