<?php
function connection($flid){
	    
	 	$link = mysql_connect("localhost","root","dq888");//'mallmysql', 'Mallmysql@468'
		if (!$link) {
			die('Could not connect: ' . mysql_error());
		}else{
			echo "数据库链接成功<br/>";
		}
		mysql_select_db('jcantrade.com', $link) or die ('Can\'t use foo : ' . mysql_error());//l1ght1ntheb0x
		$categories_id = mysql_query("SELECT categories_id FROM categories_description WHERE categories_id=".$flid);		
		if(mysql_num_rows($categories_id)== 0){			 			
			echo "你选择或输入的分类不存在<br/>";
			exit();
		}		
}
$flid = $_POST["flid"];
connection($flid);