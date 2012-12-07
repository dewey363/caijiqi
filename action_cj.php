<?php
//echo 'action_cj.php   ';
include_once("./alicaji.class.php");
set_time_limit(0);
$mysql['url'] = 'localhost';// '10.12.142.70:3306' 'caijiuser', 'CaijiAmanda569'
$mysql['user_name'] = 'root';
$mysql['user_password'] = 'dq888';
$mysql['database'] = 'jcantrade.com';

$page = (int) $_REQUEST["page"];
$count = $page;
$purl = $_REQUEST["purl"];
$flid = (int) $_REQUEST["flid"];
$purl = str_replace("abcd11111", "&", $purl);
$purl = str_replace("efgh22222", "+", $purl);
$purl = str_replace("jianhao33333", "-", $purl);
$purl = str_replace("xiahuaxian44444", "_", $purl);

$caiji = new Alicaji($purl, $flid, $page);
$link = $caiji->connection($mysql);
$link = $caiji->mysql_select();
//if($link){echo 'link ok';}

$caiji->caiji();
$caiji = null; //将会调用析构函数销毁对象，释放内存
unset($caiji); //将内存交与PHP内存房间，程序结束后再交给OS
unset($page);
unset($purl);
unset($flid);
//mysql_close($link);
if($count){
echo "成功采集第" . $count . "页<br/>";
}else{
	echo "采集成功";
}
exit;
