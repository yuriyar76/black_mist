 <h2>��������-��������</h2>
<?
if (is_array($arResult["LIST"]))
{
	?>
	<form action="" method="post">
		<p>
			<label for="shop_id">��������-�������:</label> 
            <select id="shop_id" name="shop_id" size="1">
				<option value="0">&nbsp;</option>
				<?
                foreach ($arResult["LIST"] as $v)
                {
                    if ($_SESSION['CURRNET_SHOP'] == $v['ID']) $ch = ' selected'; else $ch = '';
                    ?>
                    <option value="<?=$v['ID'];?>"<?=$ch;?>><?=$v['NAME'];?></option>
                    <?
                }
                ?>
			</select>
		</p>
		<p>
			<label for="action">��������:</label> 
            <select id="action" name="action" size="1">
				<option value="select_shop">������� � �������</option>
                <option value="add_purchase">�������� ��������� ���������</option>
                <option value="add_purchase_outgo">�������� ��������� ���������</option>
                <option value="add_correction">�������� ������������� ��������</option>
                <option value="add_correction_price">�������� ������������� ���</option>
				<option value="list_purchase">������ ��������� ���������</option>
                <option value="list_purchase_outgo">������ ��������� ���������</option>
                <option value="list_correction">������ ������������� ��������</option>
                <option value="list_correction_price">������ ������������� ���</option>
			</select>
		</p>
		<input type="submit" name="save" value="�������">
	</form>
	<?
}
?>