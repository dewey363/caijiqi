<?php
$link = mysql_connect("localhost", "root", "dq888"); //'mallmysql', 'Mallmysql@468'
if (!$link) {
	die('Could not connect: ' . mysql_error());
}
mysql_select_db('jcantrade.com', $link) or die('Can\'t use foo : ' . mysql_error()); //l1ght1ntheb0x
$pattern1 = "/Weight:(.*?)[kg|K]/";
$products_ids = array();
$sql = "SELECT p.`products_id`,pd.`products_description` FROM `products` p,`products_description` pd where p.`products_id`=pd.`products_id` AND p.`product_is_always_free_shipping` =0";
$rs = mysql_query($sql);
while ($product = mysql_fetch_Array($rs)) {
	$product_id = $product['products_id'];
	$weight = 0;
	$desc = substr($product['products_description'], strpos($product['products_description'], "Weight:"), 24);
	preg_match($pattern1, $desc, $desc1);
	if (!empty($desc1[1])) {
		$weight = $desc1[1];
	}
	if ($weight == 0) {
		$sql = "delete from products where products_id = " . $product_id;
		$sql1 = "delete from products_description where products_id = " . $product_id;
		$sql2 = "delete from products_to_categories where products_id = " . $product_id;
		mysql_query($sql);
		mysql_query($sql1);
		mysql_query($sql2);
	} else {
		if ($weight > 100) {
			$weight = $weight / 1000;
		}
		$sql = "update products set products_weight = " . $weight . " where products_id = " . $product_id;
		mysql_query($sql);
	}
}
echo "执行成功<br/>";

