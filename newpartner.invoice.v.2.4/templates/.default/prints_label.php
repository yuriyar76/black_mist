<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true){die();}
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
    <style>
        @media print  {
           .print_block {
               page-break-after: always;
            }
        }

    </style>

           <?php
            foreach($arResult['REQUEST'] as $item):?>
                 <div class='print_block'>
                    <div class="col-6">
                        <div>
                            <div style="border: 3px solid #00000069; margin-bottom: 31px;width:182px; height: 270px;" class="card">
                                <div  style="padding: 0.5rem; overflow: hidden" class="card-body">
                                    <h5 style="font-weight: 800; font-size: 1.1rem; margin:0; padding: 0 "
                                        class="card-title">
                                        <?=$item['НомерНакладной']?>
                                    </h5>
                                    <ul style="padding-bottom: 0; padding-left:0px;"
                                        class="list-group list-group-flush">
                                        <li style="padding:5px;" class="list-group-item">
                                            <p style="padding-left:0;font-size: 0.72rem;line-height: 15px; font-weight: 800;">
                                                <?=$item['КомпанияОтправителя']?>
                                            </p>
                                        </li>
                                    </ul>
                                    <hr>
                                    <p style="font-weight: 800; font-size: 0.72rem; margin-left: 10px; margin-bottom: 10px"
                                       class="card-text">
                                        <?=$item['ФамилияПолучателя']?><br>
                                        <?=$item['КомпанияПолучателя']?><br>
                                        <?=$item['АдресПолучателя']?><br>
                                        <?=$item['ТелефонПолучателя']?>
                                    </p>
                                </div>
                                <div style="padding: 0;" class="card-footer">
                                     <?$idn = str_replace('-', '', $item['НомерНакладной']);
                                      $idnum = str_replace('/', '', $idn);
                                      ?>
                                    <script type="text/javascript">
                                        $(document).ready(function() {
                                            JsBarcode("#barcode_<?=$idnum;?>", "<?=$item['НомерНакладной'];?>",
                                            {
                                                format: "CODE39",
                                                width: 0.81,
                                                height: 40.0,
                                                displayValue: false
                                            });
                                        });
                                    </script>
                                    <svg id="barcode_<?=$idnum;?>" class="target"></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                 </div>
           <?php endforeach;?>

            <?php
            foreach($arResult['INVOICE'] as $item):?>
                 <div class='print_block'>
                    <div class="col-6" >
                        <div >
                            <div  style="border: 2px solid #00000069; width:182px; height: 270px;" class="card">
                                <div style="padding: 0.5rem; overflow: hidden" class="card-body">
                                    <h5 style="font-weight: 800; font-size: 1.1rem; margin:0; padding: 0 "
                                        class="card-title"><?=$item['NUMDOC']?></h5>
                                    <ul style="padding-bottom: 0; padding-left:0px;" class="list-group list-group-flush">
                                        <li style="padding:5px;" class="list-group-item">
                                            <p  style="padding-left:0;font-size: 0.72rem;line-height: 15px; font-weight: 800;"><?=$item['PROPERTY_COMPANY_SENDER_VALUE']?></p></li>
                                    </ul>
                                    <p style=" font-weight: 800;font-size: 0.72rem; margin-left: 10px; margin-bottom: 10px"
                                       class="card-text">
                                        <?=$item['PROPERTY_NAME_RECIPIENT_VALUE']?><br>
                                        <?=$item['PROPERTY_COMPANY_RECIPIENT_VALUE']?><br>
                                        <?=$item['PROPERTY_ADRESS_RECIPIENT_VALUE']['TEXT']?><br>
                                        <?=$item['PROPERTY_PHONE_RECIPIENT_VALUE']?>
                                    </p>
                                </div>
                                <div style="padding: 0;" class="card-footer">
                                    <?$idn = str_replace('-', '', $item['NUMDOC']);
                                      $idnum = str_replace('/', '', $idn);
                                    ?>
                                    <script type="text/javascript">
                                        $(document).ready(function() {
                                            JsBarcode("#barcode_<?=$idnum;?>", "<?=$item['NUMDOC'];?>", {
                                                format: "CODE39",
                                                width: 0.81,
                                                height: 40.0,
                                                displayValue: false
                                            });
                                        });
                                    </script>
                                    <svg id="barcode_<?=$idnum;?>" class="target"></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                 </div>
             <?php endforeach;
            ?>
