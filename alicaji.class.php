<?php
class Alicaji {

    public $purl = "";
    public $fl;
    public $page;
	private $_imgFloder = '/usr/data/www/jcantrade.com/images/';
    private $_fileFLloder ='http://192.168.1.99/images/';
    private $normalFolder = 'n/'; //普通大小的图片
    private $smallFolder = 's/'; //缩略图存放路径，注：必须是放在 $annexFolder下的子目录，默认为：smallimg
    private $largeFolder = 'l/'; //水印图片存放处 marking
    private $pruduct='product/'; //产品简介的图片
    public $mysql_link = false;
	public $d;
	public $webname='www.dinobridal.com';
	public $imgPathj = 'cjdesc/'; //存放文章里的图片
	public $imgPath;

    function __construct($purl, $fl, $page) {
		$this->d = date('Ymd');
        $this->purl = $purl;
        $this->fl = $fl;
        $this->page = $page;
	  // s 小图  l大图  n中图
		$this->smallFolder = $this->_imgFloder . $this->smallFolder  . $this->d;
		$this->largeFolder = $this->_imgFloder . $this->largeFolder   . $this->d;
		$this->normalFolder = $this->_imgFloder . $this->normalFolder  .  $this->d;
		$this->imgPath = $this->_imgFloder . $this->imgPathj . $this->d;
		if (!is_dir($this->smallFolder))
			@mkdir($this->smallFolder, 0777,true);
		
		if (!is_dir($this->largeFolder))
			@mkdir($this->largeFolder, 0777,true);
		
		if (!is_dir($this->normalFolder))
			@mkdir($this->normalFolder, 0777,true);
			
		if (!is_dir($this->imgPath))
			@mkdir($this->imgPath, 0777, true);
    }
    public function connection( $mysql ) {
        $this->mysql_link = mysql_connect($mysql['url'], $mysql['user_name'], $mysql['user_password']);
        if (!$this->mysql_link) {
            die('Could not connect: ' . mysql_error());
        }
        mysql_select_db($mysql['database'], $this->mysql_link) or die('Can\'t use foo : ' . mysql_error()); //l1ght1ntheb0x
        return $this->mysql_link;
    }
    public function mysql_select( $field = 'categories_id', $table = 'categories_description', $where = 'categories_id=' ){
        $where = $where . $this->fl;
        if( $this->mysql_link ){
            $categories_id = mysql_query("SELECT ". $field ." FROM ". $table ." WHERE " . $where);
            if (mysql_num_rows($categories_id) == 0) {
                echo "你选择或输入的分类不存在<br/>";
                exit();
            }else{
                return true;
            }
        }else{
            die('Could not connect mysql: ' . mysql_error());
        }
    }
    private function regex( $html,$collection_site,$attribute = 'list'){
        /* 这里将会放置某个网站的匹配规则，通过 $collection_site 变量区分使用的规则集，通过 $attribute 识别匹配的模式是列表还是详细页
         * 如果没有对应的网站的规则会输出 Unkonown site 并die()
         * 列表页可以得到你要采集的产品url
         * 返回的变量必须保持数据结构相同：
         *	详细页可以得到网页关键字，描述，产品名称，产品价钱，产品描述，产品的图片的url
         */
        if( $collection_site == $this->webname ){
			if($attribute == 'list'){
				$pc_one ='/<h3 class=\"product-name\">\s+<a href=\"(.*?)\">(.*?)<\/a><\/h3>/is';
                preg_match_all($pc_one,$html,$out);
				$preg_out_list = $out[1];
            }else if($attribute == 'detail'){
                preg_match("|<meta name=\"keywords\" content=\"(.*?)\" \/>|is", $html, $k); //获取meta中的关键字
                $preg_out_list['keywords'] = $k[1];

                preg_match("|<meta name=\"description\" content=\"(.*?)\" \/>|is", $html, $description); //获取meta中的描述
                $preg_out_list['description'] = $description[1];
				
				preg_match('/<h1 id=\"productTitle\" class=\"productGeneral\">(.*?)<\/h1>/is', $html, $pname);
				$preg_out_list['pname'] =$pname[1];
				
				preg_match('/<span class=\"pnormalprice\">(.*?)<\/span>/is', $html, $price);
				$preg_out_list['price'] =$price[1];
				
                $pd = '/<div class=\"descriptionText\">(.*?)<div style=\"padding-top:10px;\">/is';//获得产品描述
                preg_match($pd, $html, $desc);
                $preg_out_list['pro_description'] = strip_tags($desc[1],'<ul><li>');
				
				if( preg_match('/<div id=\"productAdditionalImages\">(.*?)<\/ul><\/div>/is',$html,$pro_img_con) ){
					preg_match_all('/src=\"(.*?)\"/is',$pro_img_con[1],$preg_out_list['pro_img_url']);
					$preg_out_list['pro_img_url'] =$preg_out_list['pro_img_url'][1];
				}else{
					 preg_match('/<div  id=\"MagicZoom\"(.*?)<div id=\"productMainImage\" class=\"centeredContent back\">/is',$html,$pro_img_con);
					 preg_match_all('/src=\"(.*?)\"/is',$pro_img_con[1],$preg_out_list['pro_img_url']);
					 $preg_out_list['pro_img_url'] =$preg_out_list['pro_img_url'][1][0];
				}
            }
        }else{
            echo "Unkonown site";
            die();
        }
        return $preg_out_list;
    }
	//获取图片内容
	private function getImgCurl($url, $referer) {
		sleep(1);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_REFERER, $referer);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		$r = curl_exec($ch);
		curl_close($ch);

		return $r;
	}
	//获取随机字符
	public function rand_Char($len){
		$chars =array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		shuffle($chars);
		$pint_char ='';
		for($_i = 0; $_i < $len;$_i++){
			$pint_char .= $chars[mt_rand(0, count($chars)-1)];
		}
		return $pint_char;
	}
	//获取url地址的网页内容
	public function curlFileGetContent($url, $postField = null, $referer = null, $hostPort = null, $proxyUserPwd = null, $time = null) {
		$ch = curl_init(); // 启动一个CURL会话
		curl_setopt($ch, CURLOPT_URL, $url); // 要访问的地址
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
		if ($referer)
			curl_setopt($curl, CURLOPT_REFERER, $referer);
		else
			curl_setopt($ch, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
		if ($this->isUseHttpSocksIP && (is_array($hostPort) || is_int($hostPort))) {
			if (is_int($hostPort))
				$_hostPort = $this->aryHttpSocks5[$hostPort];
			else
				$_hostPort = $hostPort;
				curl_setopt($ch, CURLOPT_PROXY, $_hostPort[0]);
			if (strcasecmp('SOCKS5', $_hostPort[1]) == 0)
				curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
			else
				curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			if ($proxyUserPwd) {
				curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyUserPwd);
			}
		}
		if ($postField) {
			curl_setopt($ch, CURLOPT_POST, 1);//启用POST提交
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postField); //设置POST提交的字符串
		}
		curl_setopt($ch, CURLOPT_TIMEOUT, intval($time) > 0 ? $time : 20); // 设置超时限制防止死循环
		curl_setopt($ch, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
		$r = curl_exec($ch); // 执行操作
		curl_close($ch);
		return $r;
	}
	//核心采集方法
    public function caiji($mod = 'regex'){
        $cid = $this->fl;
        $h = $this->page;
        $snoopy = "";
        $con = "";
        $mtourl = "";
		//判断是采集单页还是好多页面
        if(substr_count($this->purl, '(*)') > 0) {
            $mtourl =str_replace('(*)', $h, $this->purl);
        } else {
            $mtourl = $this->purl;
        }
		$con = $this->curlFileGetContent($mtourl);
        if ($con == "") {
            echo "采集页面不存在<br/>";
        }
        $row = $this->regex($con,$this->webname);
        set_time_limit(0);
        foreach($row as $key){
			$con1 = $this->curlFileGetContent($key);
            if (empty($con1)) {
                $con1 = @file_get_contents($key);
            }
            $pdesc = "";
            $keywords = "";
            $description = "";
			$price = "";
            if ($con1 != "") {
				$min = 1;
                $weight =400; //最后为单个产品的重量。
                $preg_out_detail = $this->regex($con1, $this->webname, 'detail');
                $keywords = $preg_out_detail['keywords']; //获取关键字
                $description = $preg_out_detail['description']; //获取描述标签的内容
                $pdesc = $preg_out_detail['pro_description']; //获得产品描述
				$pname = $preg_out_detail['pname'];
				$pname = preg_replace('/\(.*?\)/is','',$pname);
				$price = $preg_out_detail['price'];
				if( $price != ''){
					$price = trim(str_replace('US$','',$price))+30;//根据要求适当的计算价钱，有时候需要高于采集网站的价钱
				}else{
					$price = 1;
				}
				$img_url = $preg_out_detail['pro_img_url'];
				$_tim = time();
				$_tim = rand(0,999) . $_tim;
				$ipath = "";
				//判断的是否有多图，如果有多图，图片的命名需要区分开
				if( is_array($img_url)){
					$i=0;
					foreach($img_url as $img_url_list){
						$list_num = count($img_url);
						$img_url_list = $this->webname .'/'. $img_url_list;
						$mw = strtolower(substr($img_url_list, -3, 3));//获取图片的格式
						//图片命名很重要从这里可以得到你采集的是几张图片，如果网站已经有一些数据时这里命名时要注意
						$rndFileName = $this->imgPath . "/" . $_tim .'-' .  $list_num . '-' . $i .  "."  .  $mw;
						$ipath = "s/" . $this->d  . "/" . $_tim .'-' .  $list_num . '-0' . "."  . $mw ;
						$getFile = $this->getImgCurl($img_url_list,$key);
						if(empty($getFile)){
							echo '图片没有得到';
						}else{
							$fp = @fopen($rndFileName, "w");
							@fwrite($fp, $getFile);
							@fclose($fp);
							$this->createImg($rndFileName, ($this->normalFolder . '/'), ($this->smallFolder . '/'),($this->largeFolder . '/'));
						}
					$i++;
					}
				}else{
					$img_url = $this->webname .'/'. $img_url;
					$getFile = $this->getImgCurl($img_url,$key);
					$mw = strtolower(substr($img_url, -3, 3));
					$rndFileName = $this->imgPath .'/'.$_tim .'-1-0.'.$mw;
					$ipath = "s/" . $this->d  . "/" . $_tim .'-1-0.'. $mw ;
					if( $getFile ){
						$fp = @fopen($rndFileName, "w");
						@fwrite($fp, $getFile);
						@fclose($fp);
						$this->createImg($rndFileName, ($this->normalFolder . '/'), ($this->smallFolder . '/'),($this->largeFolder . '/'));
					}
				}
                $free_shipping = 0;
                if (strstr("free shipping", $key["pname"])) {
                    $free_shipping = 1;
                }
                $sql = "insert into products(products_quantity,products_model,
                    products_image,products_price,products_price_retail,
                    products_price_sample,products_date_added,products_status,
                    manufacturers_id,products_quantity_order_min,products_quantity_mixed,
                    product_is_always_free_shipping,products_sort_order,products_price_sorter,
                    master_categories_id,	products_weight)
                    values" . "('5000','','" . mysql_real_escape_string($ipath) . "','" . $price . "','" . $price . "','" . $price . "','" . date("Y-m-d H:i:s") . "',1,0," . $min . ",1," . $free_shipping . ",0,'" . $price . "'," . $cid . "," . $weight . ")";
                if (mysql_query($sql)) {
                    echo"第一成功";
                    $pid = mysql_insert_id();
                } else {
                    echo "<font color='red'>产品插入失败:" . $key["pname"] . "</font><br/>";
                    continue;
                }
                if (isset($pid)) {
					$char_num = $this->rand_Char(4);
					$pname = $pname .'('. $char_num .'-'.rand(0,1000).')';
                   $pname = ucfirst($pname); //字符串首字母大写
                    $sql = "insert into products_description(products_id, language_id, products_name , products_description , products_brief_description,  products_keywords , products_url , products_viewed) values" . "(" . $pid . ",1,'" . mysql_real_escape_string($pname) . "','" . mysql_real_escape_string($pdesc) . "','" . mysql_real_escape_string($description) . "','" . mysql_real_escape_string($keywords) . "','',10)";
                    if (mysql_query($sql)) {
                        echo"<br />第二成功";
                    } else {
                        $sql = "delete from `products` where `products_id` = `" . $pid . "`";
                        if (mysql_query($sql)) {
                            echo "<font color='red'>产品描述插入失败:" . $pname . " 已成功撤销</font><br/>";
                        }
                        continue;
                    }
                    $sql = "insert into products_to_categories(products_id,categories_id)values(" . $pid . "," . $cid . ")";
                    if (mysql_query($sql)) {
                        echo"<br />第三成功<br />";
                    } else {
                        $sql = "delete from `products_description` where `products_id` = `" . $pid . "`";
                        if (mysql_query($sql)) {
                            echo "<font color='red'>产品分类插入失败:" . $pname . " 已成功撤销</font><br/>";
                        }
                        continue;
                    }
                }
            }
            ob_end_flush();
        }

    }
	//实现创建图片的方法
    public function createImg($src,$normalFolder, $smallFolder,$largeFolder) {
        if ($src && file_exists($src)) {
            $_srcPath =$src;
            $_srcInfo = $this->_getInfo($_srcPath);
            //$_photo = $_srcPath; //获得图片源
            switch ($_srcInfo['type']) {
            case 1:
                $_imgHandle = imagecreatefromgif($_srcPath);
                break;
            case 2:
                $_imgHandle = imagecreatefromjpeg($_srcPath);
                break;
            case 3:
                $_imgHandle = imagecreatefrompng($_srcPath);
                break;
            default:
                break;
            }
            $_normalImg = $this->_createImg($_imgHandle, $normalFolder, $_srcInfo, 300, 300);
            $_smallImg = $this->_createImg($_imgHandle, $smallFolder, $_srcInfo, 150, 150);
			$_largeImg = $this->_createImg($_imgHandle, $largeFolder, $_srcInfo, 1000, 1000);
            imagedestroy($_imgHandle);

        }
        return false;
    }
	//获取图片信息
    private function _getInfo($src) {
        $_imgInfo = getimagesize($src);
        $_aryInfo = array();
        $_aryInfo['src'] = $src;
        $_aryInfo['width'] = $_imgInfo[0];
        $_aryInfo['height'] = $_imgInfo[1];
        $_aryInfo['type'] = $_imgInfo[2];
        $_aryInfo['name'] = basename($src);
        return $_aryInfo;
    }
	//生成自定义大小的图片
    private function _createImg($imgHandle, $path, $srcInfo, $width, $height) {
        $_srcName = $srcInfo['name']; //缩略图片名称
        $_srcW = $srcInfo['width'];
        $_srcH = $srcInfo['height'];
        if ($_srcW < $width && $_srcH < $height) {
            copy($srcInfo['src'], $path . $srcInfo['name']);
        } else {
            $_width = ($width > $_srcW) ? $_srcW : $width;
            $_height = ($height > $_srcH) ? $_srcH : $height;
            if ($_srcW * $_width > $_srcH * $_height) {
                $_height = round($_srcH * $_width / $_srcW);
            } else {
                $_width = round($_srcW * $_height / $_srcH);
            }
            $_tempImg = null;
            if (function_exists('imagecreatetruecolor')) {
                $_tempImg = imagecreatetruecolor($_width, $_height);
                imagecopyresampled($_tempImg, $imgHandle, 0, 0, 0, 0, $_width, $_height, $_srcW, $_srcH);
            } else {
                $_tempImg = imagecreate($_width, $_height);
                imagecopyresized($_tempImg, $imgHandle, 0, 0, 0, 0, $_width, $_height, $_srcW, $_srcH);
            }
            if (file_exists($path . $_tempImg))
                @unlink($path . $_tempImg);
            imagejpeg($_tempImg, $path . $srcInfo['name'], 90);
            imagedestroy($_tempImg);
        }
        return $srcInfo['name'];
    }
}


?>
