<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
if ($arResult['INVOICE'])
{
	?>
    <div class="print_block">
        <div class="print_head_block">
            <div class="img_print_head">
                <img width="286" height="66" alt="" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/image002.png">
            </div>
            <div class="adress_print_head">
                ���: +7 495 783-99-18, �. ������, ��������� 1�
            </div>
            <div class="number"><?=$arResult['INVOICE']['NAME'];?></div>
            <script type="text/javascript">
                $(document).ready(function() {
                    $("#bcTarget").barcode("<?=$arResult['INVOICE']['NAME'];?>", "code39", {showHRI:false, barHeight:70, barWidth:2});
    
                });
            </script>
            <div id="bcTarget" class="target"></div>
        </div>
        <table cellpadding="0" cellspacing="0" border="1" bordercolor="#333333">
            <tbody>
                <tr>
                    <td rowspan="5" width="30" bgcolor="#f4ecc5" style="vertical-align:middle;">
                        <div style="width:30px; height:200px;">
                            <img width="30" height="200" alt="�����������" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l1.png">
                        </div>
                    </td>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <span class="label">������� ����������� / Shipper's Last Name</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_NAME_SENDER_VALUE'];?></span>
                        </div>
                    </td>
                    <td width="220" rowspan="2">
                        <div style="width:220px; height:80px;">
                            <span class="label">������� / Phone</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_PHONE_SENDER_VALUE'];?></span>
                        </div>
                    </td>
                    <td rowspan="5" width="30" bgcolor="#d6ffcc" style="vertical-align:middle;">
                        <div style="width:30px; height:200px;">
                            <img width="30" height="200" alt="������� ��������" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l3.png">
                        </div>
                    </td>
                    <td width="220" rowspan="2">
                        <div style="width:220px; height:80px;">
                            <span class="label">&nbsp;</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_TYPE_DELIVERY_VALUE'];?></span>
                        </div>
                    </td>
                    <td rowspan="5" width="30" bgcolor="#d6ffcc" style="vertical-align:middle;">
                        <div style="width:30px; height:200px;">
                            <img width="30" height="200" alt="������� ������" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l4.png">
                        </div>
                    </td>
                    <td width="142" rowspan="3">
                        <div style="width:142px; height:120px;">
                            <span class="label">����������</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_TYPE_PAYS_VALUE'];?></span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_PAYS_VALUE'];?></span>
                        </div>
                    </td>
                </tr>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <span class="label">��������-����������� / Shipping Company</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_COMPANY_SENDER_VALUE'];?></span>
                        </div>
                    </td>
                <tr>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <span class="label">������ / Country</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_CITY_SENDER_AR'][2];?></span>
                        </div>
                    </td>
                    <td width="220">
                        <div style="width:220px; height:40px;">
                            <span class="label">������� / State</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_CITY_SENDER_AR'][1];?></span>
                        </div>
                    </td>
                    <td width="220" rowspan="3">
                        <div style="width:220px; height:120px;">
                            <span class="label">���������</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_WHO_DELIVERY_VALUE'];?></span>
                            <span class="label">��������� � ����</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_IN_DATE_DELIVERY_VALUE'];?></span>
                            <span class="label">��������� �� ����</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_IN_TIME_DELIVERY_VALUE'];?></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <span class="label">����� / Sity</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_CITY_SENDER_AR'][0];?></span>
                        </div>
                    </td>
                    <td width="220">
                        <div style="width:220px; height:40px;">
                            <span class="label">������ / Postal Code</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_INDEX_SENDER_VALUE'];?></span>
                        </div>
                    </td>
                    <td width="142" rowspan="2">
                        <div style="width:142px; height:80px;">
                            <span class="label">������</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_PAYMENT_VALUE'];?></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td width="600" colspan="2">
                        <div style="width:600px; height:40px;">
                            <span class="label">����� / Street Address</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_ADRESS_SENDER_VALUE']['TEXT'];?></span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <table cellpadding="0" cellspacing="0" border="1" bordercolor="#333333" style="margin-top:-1px;">
            <tbody>
                <tr>
                    <td rowspan="5" width="30" bgcolor="#f4ecc5" style="vertical-align:middle;">
                        <div style="width:30px; height:200px;">
                            <img width="30" height="200" alt="����������" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l2.png">
                        </div>
                    </td>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <span class="label">������� ���������� / Consignee's Last Name</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_NAME_RECIPIENT_VALUE'];?></span>
                        </div>
                    </td>
                    <td width="220" rowspan="2">
                        <div style="width:220px; height:80px;">
                            <span class="label">������� / Phone</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_PHONE_RECIPIENT_VALUE'];?></span>
                        </div>
                    </td>
                    <td colspan="3" rowspan="3" width="425">
                        <div style="width:425px; height:120px;">
                            <span class="label">����������� ���������� / SPECIAL INSTRUCTIONS</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_INSTRUCTIONS_VALUE']['TEXT'];?></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <span class="label">��������-���������� / Consignee Company</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_COMPANY_RECIPIENT_VALUE'];?></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <span class="label">������ / Country</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_CITY_RECIPIENT_AR'][2];?></span>
                        </div>
                    </td>
                    <td width="220">
                        <div style="width:220px; height:40px;">
                            <span class="label">������� / State</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_CITY_RECIPIENT_AR'][1];?></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <span class="label">����� / Sity</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_CITY_RECIPIENT_AR'][0];?></span>
                        </div>
                    </td>
                    <td width="220">
                        <div style="width:220px; height:40px;">
                            <span class="label">������ / Postal Code</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_INDEX_RECIPIENT_VALUE'];?></span>
                        </div>
                    </td>
                    <td width="140">
                        <div style="width:140px; height:40px;">
                            <span class="label">����� �� ������</span>
                        </div>
                    </td>
                    <td width="140">
                        <div style="width:140px; height:40px;">
                            <span class="label">��������� �����</span>
                        </div>
                    </td>
                    <td width="140">
                        <div style="width:140px; height:40px;">
                            <span class="label">����� � ������</span>
                            <span class="value"><?=($arResult['INVOICE']['PROPERTY_FOR_PAYMENT_VALUE'] > 0) ? $arResult['INVOICE']['PROPERTY_FOR_PAYMENT_VALUE'] : '';?></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td width="600" colspan="2">
                        <div style="width:600px; height:40px;">
                            <span class="label">����� / Street Address</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_ADRESS_RECIPIENT_VALUE']['TEXT'];?></span>
                        </div>
                    </td>
                    <td colspan="3" width="425">
                        <div style="width:425px; height:40px;">
                            <span class="label">������� � ������� ����������� / Shippers Signature</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <table cellpadding="0" cellspacing="0" border="1" bordercolor="#333333" style="margin-top:-1px;">
            <tbody>
                <tr>
                    <td rowspan="7" width="30" bgcolor="#ccffff" valign="middle" style="vertical-align:middle;">
                        <div style="height:182px; width:30px;">
                            <img width="30" height="182" alt="�������� �����������" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l5.png">
                        </div>
                    </td>
                    <td colspan="3" width="298">
                        <div style="width:298px; height:25px;">
                            
                        </div>
                    </td>
                    <td width="80" align="center">
                        <div style="width:80px; height:25px;">
                            <span class="label">����<br>Pieces</span>
                        </div>
                    </td>
                    <td width="80" align="center">
                        <div style="width:80px; height:25px;">
                            <span class="label">���<br>Weight</span>
                        </div>
                    </td>
                    <td width="140" align="center">
                        <div style="width:140px; height:25px;">
                            <span class="label">�������� (�� � �� � ��)<br>Dimensions (cm x cm x cm)</span>
                        </div>
                    </td>
                    <td colspan="2" rowspan="3" width="425"><div style="width:425px; height:65px;"><span class="label">������� ��������</span></div></td>
                </tr>
                <tr>
                    <td colspan="3"><div style="width:298px; height:20px;"><span class="value"><?=$arResult['INVOICE']['PROPERTY_TYPE_PACK_VALUE'];?></span></div></td>
                    <td><div style="width:80px; height:20px;"><span class="value"><?=$arResult['INVOICE']['PROPERTY_PLACES_VALUE'];?></span></div></td>
                    <td><div style="width:80px; height:20px;"><span class="value"><?=$arResult['INVOICE']['PROPERTY_WEIGHT_VALUE'];?></span></div></td>
                    <td>
                        <div style="width:140px; height:20px;">
                        <span class="value">
                        <?
                        if (count($arResult['INVOICE']['PROPERTY_DIMENSIONS_VALUE']) > 0)
                        {
                            echo $arResult['INVOICE']['PROPERTY_DIMENSIONS_VALUE'][0].' � '.$arResult['INVOICE']['PROPERTY_DIMENSIONS_VALUE'][1].' � '.$arResult['INVOICE']['PROPERTY_DIMENSIONS_VALUE'][2];
                        }
                        ?>
                        </span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3"><div style="width:298px; height:20px;"></div></td>
                    <td><div style="width:80px; height:20px;"></div></td>
                    <td><div style="width:80px; height:20px;"></div></td>
                    <td><div style="width:140px; height:20px;"></div></td>
                </tr>
                <tr>
                    <td colspan="3"><div style="width:298px; height:20px;"></div></td>
                    <td><div style="width:80px; height:20px;"></div></td>
                    <td><div style="width:80px; height:20px;"></div></td>
                    <td><div style="width:140px; height:20px;"></div></td>
                    <td rowspan="3" width="212"><div style="width:212px; height:60px;"><span class="label">���������</span></div></td>
                    <td rowspan="3" width="212"><div style="width:212px; height:60px;"><span class="label">������� ����������</span></div></td>
                </tr>
                <tr>
                    <td colspan="3"><div style="width:298px; height:20px;"></div></td>
                    <td><div style="width:80px; height:20px;"></div></td>
                    <td><div style="width:80px; height:20px;"></div></td>
                    <td><div style="width:140px; height:20px;"></div></td>
                </tr>
                <tr>
                    <td colspan="3"><div style="width:298px; height:20px;"></div></td>
                    <td><div style="width:80px; height:20px;"></div></td>
                    <td><div style="width:80px; height:20px;"></div></td>
                    <td><div style="width:140px; height:20px;"></div></td>
                </tr>
                <tr>
                    <td width="98">
                        <div style="height:50px; width:98px;">
                            <span class="label">����<br>Pieses</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_PLACES_VALUE'];?></span>
                        </div>
                    </td>
                    <td width="98">
                        <div style="height:50px; width:98px">
                            <span class="label">���<br>Weight</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_WEIGHT_VALUE'];?></span>
                        </div>
                    </td>
                    <td width="98">
                        <div style="height:50px; width:98px;"><span class="label">�������� ���<br>Vol. WT</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_OB_WEIGHT'];?></span>
                        </div>
                    </td>
                    <td colspan="2"><div style="height:50px;"><span class="label">�����. �����.<br>Control WT</span></div></td>
                    <td>
                        <div style="height:50px;"><span class="label">������. ���������<br>Declared Value</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_COST_VALUE'];?></span>
                        </div>
                    </td>
                    <td><div style="height:50px;"><span class="label">������� ����������</span></div></td>
                    <td><div style="height:50px;"><span class="label">���� � ����� ��������</span></div></td>
                </tr>
            </tbody>
        </table>
        <div class="print_head_block">
            <div class="img_print_head">
                <img width="286" height="66" alt="" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/image002.png">
            </div>
            <div class="adress_print_head">
                ���: +7 495 783-99-18, �. ������, ��������� 1�
            </div>
            <div class="number"><?=$arResult['INVOICE']['NAME'];?></div>
            <script type="text/javascript">
                $(document).ready(function() {
                    $("#bcTarget_2").barcode("<?=$arResult['INVOICE']['NAME'];?>", "code39", {showHRI:false, barHeight:70, barWidth:2});
    
                });
            </script>
            <div id="bcTarget_2" class="target"></div>
        </div>
        <table cellpadding="0" cellspacing="0" border="1" bordercolor="#333333">
            <tbody>
                <tr>
                    <td rowspan="5" width="30" bgcolor="#f4ecc5" style="vertical-align:middle;">
                        <div style="width:30px; height:200px;">
                            <img width="30" height="200" alt="�����������" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l1.png">
                        </div>
                    </td>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <span class="label">������� ����������� / Shipper's Last Name</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_NAME_SENDER_VALUE'];?></span>
                        </div>
                    </td>
                    <td width="220" rowspan="2">
                        <div style="width:220px; height:80px;">
                            <span class="label">������� / Phone</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_PHONE_SENDER_VALUE'];?></span>
                        </div>
                    </td>
                    <td rowspan="5" width="30" bgcolor="#d6ffcc" style="vertical-align:middle;">
                        <div style="width:30px; height:200px;">
                            <img width="30" height="200" alt="������� ��������" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l3.png">
                        </div>
                    </td>
                    <td width="220" rowspan="2">
                        <div style="width:220px; height:80px;">
                            <span class="label">&nbsp;</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_TYPE_DELIVERY_VALUE'];?></span>
                        </div>
                    </td>
                    <td rowspan="5" width="30" bgcolor="#d6ffcc" style="vertical-align:middle;">
                        <div style="width:30px; height:200px;">
                            <img width="30" height="200" alt="������� ������" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l4.png">
                        </div>
                    </td>
                    <td width="142" rowspan="3">
                        <div style="width:142px; height:120px;">
                            <span class="label">����������</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_TYPE_PAYS_VALUE'];?></span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_PAYS_VALUE'];?></span>
                        </div>
                    </td>
                </tr>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <span class="label">��������-����������� / Shipping Company</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_COMPANY_SENDER_VALUE'];?></span>
                        </div>
                    </td>
                <tr>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <span class="label">������ / Country</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_CITY_SENDER_AR'][2];?></span>
                        </div>
                    </td>
                    <td width="220">
                        <div style="width:220px; height:40px;">
                            <span class="label">������� / State</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_CITY_SENDER_AR'][1];?></span>
                        </div>
                    </td>
                    <td width="220" rowspan="3">
                        <div style="width:220px; height:120px;">
                            <span class="label">���������</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_WHO_DELIVERY_VALUE'];?></span>
                            <span class="label">��������� � ����</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_IN_DATE_DELIVERY_VALUE'];?></span>
                            <span class="label">��������� �� ����</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_IN_TIME_DELIVERY_VALUE'];?></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <span class="label">����� / Sity</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_CITY_SENDER_AR'][0];?></span>
                        </div>
                    </td>
                    <td width="220">
                        <div style="width:220px; height:40px;">
                            <span class="label">������ / Postal Code</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_INDEX_SENDER_VALUE'];?></span>
                        </div>
                    </td>
                    <td width="142" rowspan="2">
                        <div style="width:142px; height:80px;">
                            <span class="label">������</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_PAYMENT_VALUE'];?></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td width="600" colspan="2">
                        <div style="width:600px; height:40px;">
                            <span class="label">����� / Street Address</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_ADRESS_SENDER_VALUE']['TEXT'];?></span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <table cellpadding="0" cellspacing="0" border="1" bordercolor="#333333" style="margin-top:-1px;">
            <tbody>
                <tr>
                    <td rowspan="5" width="30" bgcolor="#f4ecc5" style="vertical-align:middle;">
                        <div style="width:30px; height:200px;">
                            <img width="30" height="200" alt="����������" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l2.png">
                        </div>
                    </td>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <span class="label">������� ���������� / Consignee's Last Name</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_NAME_RECIPIENT_VALUE'];?></span>
                        </div>
                    </td>
                    <td width="220" rowspan="2">
                        <div style="width:220px; height:80px;">
                            <span class="label">������� / Phone</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_PHONE_RECIPIENT_VALUE'];?></span>
                        </div>
                    </td>
                    <td colspan="3" rowspan="3" width="425">
                        <div style="width:425px; height:120px;">
                            <span class="label">����������� ���������� / SPECIAL INSTRUCTIONS</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_INSTRUCTIONS_VALUE']['TEXT'];?></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <span class="label">��������-���������� / Consignee Company</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_COMPANY_RECIPIENT_VALUE'];?></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <span class="label">������ / Country</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_CITY_RECIPIENT_AR'][2];?></span>
                        </div>
                    </td>
                    <td width="220">
                        <div style="width:220px; height:40px;">
                            <span class="label">������� / State</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_CITY_RECIPIENT_AR'][1];?></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td width="380">
                        <div style="width:380px; height:40px;">
                            <span class="label">����� / Sity</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_CITY_RECIPIENT_AR'][0];?></span>
                        </div>
                    </td>
                    <td width="220">
                        <div style="width:220px; height:40px;">
                            <span class="label">������ / Postal Code</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_INDEX_RECIPIENT_VALUE'];?></span>
                        </div>
                    </td>
                    <td width="140">
                        <div style="width:140px; height:40px;">
                            <span class="label">����� �� ������</span>
                        </div>
                    </td>
                    <td width="140">
                        <div style="width:140px; height:40px;">
                            <span class="label">��������� �����</span>
                        </div>
                    </td>
                    <td width="140">
                        <div style="width:140px; height:40px;">
                            <span class="label">����� � ������</span>
                            <span class="value"><?=($arResult['INVOICE']['PROPERTY_FOR_PAYMENT_VALUE'] > 0) ? $arResult['INVOICE']['PROPERTY_FOR_PAYMENT_VALUE'] : '';?></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td width="600" colspan="2">
                        <div style="width:600px; height:40px;">
                            <span class="label">����� / Street Address</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_ADRESS_RECIPIENT_VALUE']['TEXT'];?></span>
                        </div>
                    </td>
                    <td colspan="3" width="425">
                        <div style="width:425px; height:40px;">
                            <span class="label">������� � ������� ����������� / Shippers Signature</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <table cellpadding="0" cellspacing="0" border="1" bordercolor="#333333" style="margin-top:-1px;">
            <tbody>
                <tr>
                    <td rowspan="7" width="30" bgcolor="#ccffff" valign="middle" style="vertical-align:middle;">
                        <div style="height:182px; width:30px;">
                            <img width="30" height="182" alt="�������� �����������" src="/bitrix/components/black_mist/newpartner.invoice/templates/.default/images/l5.png">
                        </div>
                    </td>
                    <td colspan="3" width="298">
                        <div style="width:298px; height:25px;">
                            
                        </div>
                    </td>
                    <td width="80" align="center">
                        <div style="width:80px; height:25px;">
                            <span class="label">����<br>Pieces</span>
                        </div>
                    </td>
                    <td width="80" align="center">
                        <div style="width:80px; height:25px;">
                            <span class="label">���<br>Weight</span>
                        </div>
                    </td>
                    <td width="140" align="center">
                        <div style="width:140px; height:25px;">
                            <span class="label">�������� (�� � �� � ��)<br>Dimensions (cm x cm x cm)</span>
                        </div>
                    </td>
                    <td colspan="2" rowspan="3" width="425"><div style="width:425px; height:65px;"><span class="label">������� ��������</span></div></td>
                </tr>
                <tr>
                    <td colspan="3"><div style="width:298px; height:20px;"><span class="value"><?=$arResult['INVOICE']['PROPERTY_TYPE_PACK_VALUE'];?></span></div></td>
                    <td><div style="width:80px; height:20px;"><span class="value"><?=$arResult['INVOICE']['PROPERTY_PLACES_VALUE'];?></span></div></td>
                    <td><div style="width:80px; height:20px;"><span class="value"><?=$arResult['INVOICE']['PROPERTY_WEIGHT_VALUE'];?></span></div></td>
                    <td>
                        <div style="width:140px; height:20px;">
                        <span class="value">
                        <?
                        if (count($arResult['INVOICE']['PROPERTY_DIMENSIONS_VALUE']) > 0)
                        {
                            echo $arResult['INVOICE']['PROPERTY_DIMENSIONS_VALUE'][0].' � '.$arResult['INVOICE']['PROPERTY_DIMENSIONS_VALUE'][1].' � '.$arResult['INVOICE']['PROPERTY_DIMENSIONS_VALUE'][2];
                        }
                        ?>
                        </span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3"><div style="width:298px; height:20px;"></div></td>
                    <td><div style="width:80px; height:20px;"></div></td>
                    <td><div style="width:80px; height:20px;"></div></td>
                    <td><div style="width:140px; height:20px;"></div></td>
                </tr>
                <tr>
                    <td colspan="3"><div style="width:298px; height:20px;"></div></td>
                    <td><div style="width:80px; height:20px;"></div></td>
                    <td><div style="width:80px; height:20px;"></div></td>
                    <td><div style="width:140px; height:20px;"></div></td>
                    <td rowspan="3" width="212"><div style="width:212px; height:60px;"><span class="label">���������</span></div></td>
                    <td rowspan="3" width="212"><div style="width:212px; height:60px;"><span class="label">������� ����������</span></div></td>
                </tr>
                <tr>
                    <td colspan="3"><div style="width:298px; height:20px;"></div></td>
                    <td><div style="width:80px; height:20px;"></div></td>
                    <td><div style="width:80px; height:20px;"></div></td>
                    <td><div style="width:140px; height:20px;"></div></td>
                </tr>
                <tr>
                    <td colspan="3"><div style="width:298px; height:20px;"></div></td>
                    <td><div style="width:80px; height:20px;"></div></td>
                    <td><div style="width:80px; height:20px;"></div></td>
                    <td><div style="width:140px; height:20px;"></div></td>
                </tr>
                <tr>
                    <td width="98">
                        <div style="height:50px; width:98px;">
                            <span class="label">����<br>Pieses</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_PLACES_VALUE'];?></span>
                        </div>
                    </td>
                    <td width="98">
                        <div style="height:50px; width:98px">
                            <span class="label">���<br>Weight</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_WEIGHT_VALUE'];?></span>
                        </div>
                    </td>
                    <td width="98">
                        <div style="height:50px; width:98px;"><span class="label">�������� ���<br>Vol. WT</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_OB_WEIGHT'];?></span>
                        </div>
                    </td>
                    <td colspan="2"><div style="height:50px;"><span class="label">�����. �����.<br>Control WT</span></div></td>
                    <td>
                        <div style="height:50px;"><span class="label">������. ���������<br>Declared Value</span>
                            <span class="value"><?=$arResult['INVOICE']['PROPERTY_COST_VALUE'];?></span>
                        </div>
                    </td>
                    <td><div style="height:50px;"><span class="label">������� ����������</span></div></td>
                    <td><div style="height:50px;"><span class="label">���� � ����� ��������</span></div></td>
                </tr>
            </tbody>
        </table>
    </div>
    <?
}
?>