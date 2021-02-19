<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true){die();}
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
    <style>
        @media print  {
           .print_block {
                page-break-inside: avoid;
            }
        }
        .print_block {
            page-break-inside: avoid;
        }
    </style>
           <?php
           $i=0;
           $j=1;
            foreach($arResult['REQUEST'] as $item):
                if(($j-1)%10 == 0 || $j == 1){
                    echo "<div class='print_block'>";
                }
                if($i%2 == 0){
                    echo "<div style='margin-bottom: 30px' class='row'>";
                }
                ?>
                <div class="col-6">
                    <div style="width: 330px; ">
                        <div style="border: 2px solid #00000069;" class="card">
                            <div  style="padding-top: 5px; padding-bottom: 0;" class="card-body">
                                <h5 style="font-weight: 700; font-size: 1.1rem; margin:0; padding: 0 "
                                    class="card-title">
                                    <?=$item['НомерНакладной']?>
                                </h5>
                                <ul style="padding-bottom: 0; padding-left:10px;" class="list-group list-group-flush">
                                    <li style="padding:5px;" class="list-group-item">
                                        <p style="line-height: 15px; font-weight: 700;"><?=$item['КомпанияОтправителя']?></p></li>
                                </ul>
                                <hr>
                                <p style="font-size: 0.72rem; margin-left: 10px; margin-bottom: 10px"
                                   class="card-text">
                                    <?=$item['ФамилияПолучателя']?><br>
                                    <?=$item['КомпанияПолучателя']?><br>
                                    <?=$item['АдресПолучателя']?><br>
                                    <?=$item['ТелефонПолучателя']?>
                                </p>
                            </div>
                            <div style="padding: 0;" class="card-footer">
                                 <?$idn = str_replace('-', '', $item['NUMDOC']);
                                  $idnum = str_replace('/', '', $idn);
                                  ?>
                                <script type="text/javascript">
                                    $(document).ready(function() {
                                        JsBarcode("#barcode_<?=$idnum;?>", "<?=$item['НомерНакладной'];?>", {
                                            format: "CODE39",
                                            width: 1.3,
                                            height: 30,
                                            displayValue: false
                                        });
                                    });
                                </script>
                                <svg id="barcode_<?=$idnum;?>" class="target"></svg>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
                if(($i-1)%2 == 0){
                    echo "</div>";
                }
                if(($j-10)%10 == 0 ){
                    echo "</div>";
                }

            $i++;
            $j++;
            endforeach;?>
            <?php
            foreach($arResult['INVOICE'] as $item):
                if(($j-1)%10 == 0 || $j == 1){
                    echo "<div class='print_block'>";
                }
                if($i%2 == 0){
                    echo "<div style='margin-bottom: 30px' class='row'>";
                }
                ?>
                <div class="col-6">
                    <div style="width: 330px; ">
                        <div  style="border: 2px solid #00000069;" class="card">
                            <div style="padding-top: 5px; padding-bottom: 0;" class="card-body">
                                <h5 style="font-weight: 700; font-size: 1.1rem; margin:0; padding: 0 "
                                    class="card-title"><?=$item['NUMDOC']?></h5>
                                <ul style="padding-bottom: 0; padding-left:10px;" class="list-group list-group-flush">
                                    <li style="padding:5px;" class="list-group-item">
                                        <p  style="line-height: 15px; font-weight: 700;"><?=$item['PROPERTY_COMPANY_SENDER_VALUE']?></p></li>
                                </ul>
                                <p style="font-size: 0.72rem; margin-left: 10px; margin-bottom: 10px"
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
                                            width: 1.3,
                                            height: 30.0,
                                            displayValue: false
                                        });
                                    });
                                </script>
                                <svg id="barcode_<?=$idnum;?>" class="target"></svg>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                if(($i-1)%2 == 0){
                    echo "</div>";
                }
                if(($j-10)%10 == 0 ){
                    echo "</div>";
                }
                $i++;
                $j++;
            endforeach;
            ?>
