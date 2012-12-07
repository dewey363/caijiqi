<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>采集</title>
<script type="text/javascript" src="jquery-1.5.1.js"></script>
<script type="text/javascript">
$(document).ready(function(){    
	  $("#tijiao").click(function(){
		  var url = $("#url").val();
		  if(url==""){
        	  alert("请输入采集url地！");
        	  return false;
          }
		  var page=Number( $("#page").val() );
		  
		  if(url.indexOf('(*)') > 0){
			  if(page<=0){
				  alert("请输入页号或者页号有误！");
				  return false;
			  }
			  var maxPage = Number( $("#maxPage").val() );
			  if(maxPage<=0){
				  alert("请输入最大页号或者页号有误！");
				  return false;
			  }
			  
			  if(page>maxPage){
				  alert("最大页号不能小于起始页号");
				  return false;
			  }
		  }
            
          var flid = $("#flid").val();         
          if(flid==0){
        	  alert("请输入分类ID！");
        	  return false;
          }	          
		  $("#tijiao").attr("disabled","disabled");//为提交按钮添加属性
          maxPage = parseInt(maxPage);//解析字符串并返回整数数值
    	  page = parseInt(page);
    	 
		  url = url.replace(/&/g,"abcd11111");   //替换所有的&，其中g为全局标志。    
    	  url = url.replace(/\+/g,"efgh22222");  //替换所有的+，其中g为全局标志。
    	  url = url.replace(/-/g,"jianhao33333");  //替换所有的-，其中g为全局标志。  
    	  url = url.replace(/_/g,"xiahuaxian44444");  //替换所有的_，其中g为全局标志。
          send(page,maxPage,url,flid);	           
	  });
    
      function send(page,maxPage,url,flid){   	    	     	  
    	  if(url.indexOf('(*)')>0 && page<=maxPage){
	    	  $.ajax({ 
	              type: "post",
	              url: "action_cj.php",
	              data:"purl="+url+"&flid="+flid+"&page="+page,
	              timeout: 30000000000000000000000000000,	
	              beforeSend: function(XMLHttpRequest){
					  if(page <=maxPage){
						 $("#state").html("正在采集第"+page+"页:<img src='load.gif'/><img src='wait.gif'/><br/>"); 
					  }
                  },                                    
	              success: function(data, textStatus){
	        	       $("#state").after(data);//after() 方法在被选元素后插入指定的内容。
	              },	              	              
	              complete: function(XMLHttpRequest, textStatus){
		              if(XMLHttpRequest.status==504){
		            	  $("#state").html("网络链接错误，采集还在进行中...<br/>"); 
				      }
		              page=page+1;//防止字符串相加
		              if(page > maxPage){ 
			              $("#state").html("采集结束<br/>"); 
						  $("#tijiao").attr("disabled","");
			          }else{
				        send(page,maxPage,url,flid);//调用自身循环发送
			          }
	              },
	              error: function(){
	            	  $("#state").append("请求出现错误<br/>");//追加内容到文件中
	              }
	          });
		  }else{
			   $.ajax({ 
	              type: "post",
	              url: "action_cj.php",
	              data:"purl="+url+"&flid="+flid,
	              timeout: 30000000000000000000000,
	              beforeSend: function(XMLHttpRequest){
            	 	$("#state").html("正在采集:<img src='load.gif'/><img src='wait.gif'/><br/>"); 
                  },                                    
	              success: function(data, textStatus){
	        	       $("#state").after(data);
	              },	              	              
	              complete: function(XMLHttpRequest, textStatus){
		              if(XMLHttpRequest.status==504){
		            	  $("#state").html("网络链接错误，采集还在进行中...<br/>"); 
				      }
			          else{
						  $("#state").html("采集结束<br/>"); 
						  $("#tijiao").attr("disabled","");
                         // alert(XMLHttpRequest.status);
			          }
	              },
	              error: function(){
	            	  $("#state").append("请求出现错误<br/>");
	              }
			   });
			  
			}
      }	  
});
</script>
</head>
<body>
<form action="" method="post" id="testform">
采集url地址： <input type="text" value="<?php echo $purl;?>" name="url" id="url" style="width:300px;" /><br/><br/>
起始采集页面号： <input type="text" value="<?php echo $page;?>" name="page" id="page" /> 
最大采集页面号：<input type="text" value="<?php echo $maxPage;?>" name="maxPage" id="maxPage" />
<br/><br/>
输入分类id:
		<input type="text" value="<?php echo $flid;?>" name="flid" id="flid" />
		<input type="button" name="submit" value="submit" id="tijiao"/> 
		<br/><br/><br/><br/>
		
		<div id="state"></div>
</form>
</body>
</html>

