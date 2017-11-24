<?php 
	require_once('./lib/simple_html_dom/simple_html_dom.php');

	/**
	* 
	*/
	class CSU
	{
		public $userid;
		public $pwd;
		public $encoded;
		public $cookieJar;
		public $cookieJar_app;
		private $app_cookie;
		public $state ;
		public $pingjiaoInfo;
		private $PreUrl;
		private $app_url;
		public $app_state;
		private $app_token;
		function __construct($userid,$pwd)
		{
			$this->setPreUrl();
			$this->userid = $userid;
			$this->pwd = $pwd;
			$this->app_url = "http://csujwc.its.csu.edu.cn/app.do?";
			$this->warning();
			
		}
		function __destruct()
		{
			//$this->logout();
		}
		//登录
		public function login()
		{
			$this->encoded();
			$this->setCookieJar();
			$re = $this->CSU_INIT();
			if ($re) {
				$this->state =  TRUE;
				return TRUE;
			}else{
				$this->state =  FALSE;
				return FALSE;
			}
		}
		public function logout()
		{
			unlink($this->cookieJar);
		}
		//设置cookiejar
		public function setCookieJar($filename='./cookie')
		{
			$this->cookieJar = tempnam($filename,'cookie');
		}
		//查询排名接口
		public function getRank()
		{
			$cookieJar = $this->cookieJar;
			$url = $this->PreUrl."/jsxsd/kscj/zybm_cx";
			$header = array(
				"User-Agent" => "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36"
				);
			$ch =curl_init();  
			curl_setopt($ch,CURLOPT_URL,$url);   
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
			curl_setopt($ch,CURLOPT_HEADER,1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
			$res = curl_exec($ch);
			curl_close($ch);
			$grade=[];
			$html = new simple_html_dom();
			$html->load($res);
			$tr = $html->find('tr',2);
			if ($tr) {
				$rank["计算学分"]=$tr->children(1)->plaintext;
				$rank["专业排名"]=$tr->children(2)->plaintext;
				$rank["平均分"]=$tr->children(3)->plaintext;
			}else{
				$rank["计算学分"]=0;
				$rank["专业排名"]=0;
				$rank["平均分"]=0;
			}
			return $rank;
		}
		//查询课表接口
		public function getSubjectTimetable()
		{
			$cookieJar = $this->cookieJar;
			$url = $this->PreUrl."/jsxsd/xskb/xskb_list.do?Ves632DSdyV=NEW_XSD_WDKB";
			$header = array(
				"User-Agent" => "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36"
				);
			$ch =curl_init();  
			curl_setopt($ch,CURLOPT_URL,$url);   
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
			curl_setopt($ch,CURLOPT_HEADER,1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
			$res = curl_exec($ch);
			curl_close($ch);
			$html = new simple_html_dom();
			$html->load($res);
			$td = $html->find('td');
			$kebiao = [];
			foreach ($td as $k=> $v) {
				$info = $v->children(3)->plaintext;
				if ($info=="&nbsp;") {
					$info="没课美滋滋";
				}else{
					$info1=$info;
					$info = explode("\n",$info);
					$info[]=$info1;
				}
				switch ($k%7) {
					case 1:
						$kebiao["星期一"][]=$info;
						break;
					case 2:
						$kebiao["星期二"][]=$info;
						break;
					case 3:
						$kebiao["星期三"][]=$info;
						break;
					case 4:
						$kebiao["星期四"][]=$info;
						break;
					case 5:
						$kebiao["星期五"][]=$info;
						break;
					case 6:
						$kebiao["星期六"][]=$info;
						break;
					case 0:
						$kebiao["星期日"][]=$info;
						break;
					default:
						# code...
						break;
				}
				if ($k>=42) {
					break;
				}
			}
			return $kebiao;
			
		}
		//查询等级考试接口
		public function getLevelGrade()
		{
			$cookieJar = $this->cookieJar;
			$url = $this->PreUrl."/jsxsd/kscj/djkscj_list";
			$header = array(
				"User-Agent" => "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36"
				);
			$ch =curl_init();  
			curl_setopt($ch,CURLOPT_URL,$url);   
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
			curl_setopt($ch,CURLOPT_HEADER,1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
			$res = curl_exec($ch);
			curl_close($ch);
			$grade=[];
			$html = new simple_html_dom();
			$html->load($res);
			$tr = $html->find('tr');
			$i=0;
			foreach ($tr as $k => $v) {
				if ($k<=2) {
					continue;
				}
				if ($v->children(0)->plaintext=="正在拼命加载中，请稍后...") {
					break;
				}
				$grade[]["序号"]=$v->children(0)->plaintext;
				$grade[$i]["考级课程(等级)"]=$v->children(1)->plaintext;
				$grade[$i]["分数类成绩笔试"]=$v->children(2)->plaintext;
				$grade[$i]["分数类成绩机试"]=$v->children(3)->plaintext;
				$grade[$i]["分数类成绩总成绩"]=$v->children(4)->plaintext;
				$grade[$i]["等级类成绩笔试"]=$v->children(5)->plaintext;
				$grade[$i]["等级类成绩机试"]=$v->children(6)->plaintext;
				$grade[$i]["等级类成绩总成绩"]=$v->children(7)->plaintext;
				$grade[$i]["考试时间"]=$v->children(8)->plaintext;
				$i++;
			}
			return $grade;
		}
		//查询成绩接口
		public function getGrade($data = '')
		{
			$cookieJar = $this->cookieJar;
			$url = $this->PreUrl."/jsxsd/kscj/cjcx_list";
			$postData = "xnxq01id=".$data;
			$header = array(
				"User-Agent" => "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36",
				"Content-Type" => "application/x-www-form-urlencoded"
				);
			$ch =curl_init();  
			curl_setopt($ch,CURLOPT_URL,$url);   
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
			curl_setopt($ch,CURLOPT_HEADER,1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
			$res = curl_exec($ch);
			curl_close($ch);
			// print_r($res);
			preg_match_all("/<td(.*)>(.*)<\/td>/", $res,$res1);
			preg_match_all("/<a(.*)>(.*)<\/a><\/td>/", $res,$res2);
			$res1 = $res1[2];
			$temp2 = $res2[2];
			for ($i=0; $i <(count($res1)-1)/9; $i++) {
				for ($j=0; $j <=8 ; $j++) { 
					$temp3[$j] = $res1[$i*9+$j+1];

				}
				$temp3[4] = $temp2[$i];
				$temp4[$i] = $temp3;
			}
			return isset($temp4) ? $temp4 : FALSE;
		} 
		//查询原始成绩接口
		public function getOriginGrade()
		{
			$cookieJar = $this->cookieJar;
			$url = $this->PreUrl."/jsxsd/kscj/yscjcx_list";
			$header = array(
				"User-Agent" => "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36"
				);
			$ch =curl_init();  
			curl_setopt($ch,CURLOPT_URL,$url);   
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
			curl_setopt($ch,CURLOPT_HEADER,1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
			$res = curl_exec($ch);
			curl_close($ch);
			$grade=[];
			$html = new simple_html_dom();
			$html->load($res);
			$tr = $html->find('tr');
			$i=0;
			foreach ($tr as $k => $v) {
				if ($k<=1) {
					continue;
				}
				$grade[$i]["序号"]=$v->children(0)->plaintext;
				$grade[$i]["初修学期"]=$v->children(1)->plaintext;
				$grade[$i]["获得学期"]=$v->children(2)->plaintext;
				$grade[$i]["课程"]=$v->children(3)->plaintext;
				$grade[$i]["课程编号"]=$this->get_between($grade[$i]["课程"],'[',']');
				$grade[$i]["过程成绩"]=$v->children(4)->plaintext;
				$grade[$i]["期末成绩"]=$v->children(5)->plaintext;
				$grade[$i]["成绩"]=$v->children(6)->plaintext;
				$grade[$i]["学分"]=$v->children(7)->plaintext;
				$grade[$i]["课程属性"]=$v->children(9)->plaintext;
				$grade[$i]["课程性质"]=$v->children(10)->plaintext;
				$i++;
			}
			return $grade;
		}
		//获得评教信息
		public function getPingjiaoInfo()
		{
			$url = $this->PreUrl."/jsxsd/xspj/xspj_find.do?Ves632DSdyV=NEW_XSD_JXPJ";
			$res = $this->http($url);
			$html = new simple_html_dom();
			$html->load($res);
			$as = $html->find('a');
			foreach ($as as $a) {
				$str = "jsxsd/xspj/xspj_list.do";
				$re = strstr($a->href,$str);
				if($re){
					$final_url = $this->PreUrl.$a->href;
				}
				
			}
			$re =$this->http($final_url);
			$html2 = new simple_html_dom();
			$html2->load($re);
			$tr = $html2->find('tr');
			$i=0;
			foreach ($tr as $k=>$v) {
				if ($k<=1) {
					continue;
				}
				if (strlen($v->children(0)->plaintext)>10) {

					break;
				}
				$info[$i]["序号"] = $v->children(0)->plaintext;
				$info[$i]["课程编号"] = $v->children(1)->plaintext;
				$info[$i]["课程名称"] = $v->children(2)->plaintext;
				$info[$i]["授课教师"] = $v->children(3)->plaintext;
				$info[$i]["评价类别"] = $v->children(4)->plaintext;
				$info[$i]["总评分"] = $v->children(5)->plaintext;
				$info[$i]["已评"] = $v->children(6)->plaintext;
				$info[$i]["是否提交"] = $v->children(7)->plaintext;	
				$info[$i]["很满意"] = $this->getPingjiaoUrl($v->children(9)->children(2)->attr['href']);
				$info[$i]["需改进"] = $this->getPingjiaoUrl($v->children(9)->children(3)->attr['href']);
				$i++;
			}
			$this->pingjiaoInfo = $info;
			return $info;
		}

		//一键评教
		public function oneKeyPingjiao()
		{
			if (!$this->pingjiaoInfo) {
				$this->getPingjiaoInfo();
			}
			//这里暂时有问题
			$data = "cj0701id=&pj0502id1=2C3C472A578849E5BCDA195CCD8158BF&isissub=1&isissub=1&isissub=1&isissub=1&isissub=1&isissub=1&isissub=1&isissub=1&isissub=1&isissub=1&isissub=1&pjrs=3&myyprs=3&gjyprs=3&pj0502id=2C3C472A578849E5BCDA195CCD8158BF&xnxq01id=2016-2017-2&tjzt=0";
			$res = $this->http($this->pingjiaoInfo[0]['很满意'],$postData=$data);
			foreach ($this->pingjiaoInfo as $v) {
				$res = $this->http($v['很满意'],$postData=$data);
				var_dump($res);
			}
			$url = $this->PreUrl."/jsxsd/xspj/pltj_save.do";
			$res = $this->http($url,$postData=$data);
			var_dump($res);	
			var_dump($this->pingjiaoInfo);	
		}
		public function setPreUrl($option=1)
		{
			if ($option==1) {
				$this->PreUrl = "http://csujwc.its.csu.edu.cn";
			}else{
				$this->PreUrl = "http://jwctest.its.csu.edu.cn/";
			}
		}

		//加密形成encoded
		private function encoded()
		{
			$user = base64_encode($this->userid);
			$pwd = base64_encode($this->pwd);
			$this->encoded = $user.'%%%'.$pwd;
		}
		//登录教务系统
		private function CSU_INIT()
		{
			$encoded="encoded=".urlencode($this->encoded);
			$cookieJar = $this->cookieJar;
			$ch =curl_init();  
			curl_setopt($ch,CURLOPT_URL,$this->PreUrl."/jsxsd/");   
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
			curl_setopt($ch,CURLOPT_HEADER,1);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
			$res = curl_exec($ch);   	
			curl_close($ch);
			$header = array(
				"User-Agent" => "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36",
				"Content-Type" => "application/x-www-form-urlencoded"
				);
			$ch =curl_init();  
			curl_setopt($ch,CURLOPT_URL,$this->PreUrl."/jsxsd/xk/LoginToXk");   
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
			curl_setopt($ch,CURLOPT_HEADER,1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
			$res = curl_exec($ch);   	
			curl_close($ch);

			$ch =curl_init();  
			curl_setopt($ch,CURLOPT_URL,$this->PreUrl."/jsxsd/framework/xsMain.jsp");   
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
			curl_setopt($ch,CURLOPT_HEADER,1);
			curl_setopt($ch, CURLOPT_NOBODY, 1);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
			$res = curl_exec($ch);  	
			curl_close($ch);
			// print_r($res);
			preg_match("/200 OK/", $res,$res);
			// print_r($res);
			if($res){
					return TRUE;
				}	
				else {
					return FALSE;
				}
		}
		private function get_between($input, $start, $end) {
		  $substr = substr($input, strlen($start)+strpos($input, $start),(strlen($input) - strpos($input, $end))*(-1));
		  return $substr;
		}
		private function warning()
		{
			error_reporting(7);
		}
		private function http($url,$postData="")
		{
			$cookieJar = $this->cookieJar;
			$header = array(
				"User-Agent" => "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36"
				);
			$ch =curl_init();  
			curl_setopt($ch,CURLOPT_URL,$url);   
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_HEADER,1);
			if ($postData !="") {
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
			}
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
			$res = curl_exec($ch);
			curl_close($ch);
			return $res;
		}
		private function app_http($url,$postData="")
		{
			$header = array("Host: csujwc.its.csu.edu.cn",
		"User-Agent: AppCanPlugin/00.00.0116 (iPhone; iOS 11.0.3; Scale/2.00)",
		"Accept: application/json",
		"Accept-Language: zh-Hans-CN;q=1",
		"Accept-Encoding: gzip, deflate",
		"Connection: keep-alive",
		"x-mas-app-id: 11435752",
		"appverify: md5=88a9c4a46be1183e4f0c7fe36292ae0f;ts=1510975299698;",
		"token: ".$this->app_token,
		"X-QZ-APP-INFO: 11435727/public",
		"Content-Length: 0",
		"Content-Type: application/x-www-form-urlencoded",
		"Pragma: no-cache",
		"Cache-Control: no-cache");	
			$ch =curl_init();  
			curl_setopt($ch,CURLOPT_URL,$url);   
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
			curl_setopt($ch,CURLOPT_HEADER,0);
			if ($postData !="") {
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
			}
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_COOKIE, $this->app_cookie);
			$res3 = curl_exec($ch);
			$hea = curl_getinfo($ch,CURLINFO_HEADER_OUT);
			curl_close($ch);
			return $res3;
		}
		private function getPingjiaoUrl($str)
		{
			preg_match_all("/[\'](.*?)[\']/",$str,$matches);
			$url = $this->PreUrl."/jsxsd/xspj/pjtype_save.do?pj0502id=".trim($matches[1][0])."&pj08id=".trim($matches[1][1])."&type=".trim($matches[1][3])."&jg0101id=".trim($matches[1][2]);
			return $url;
		}
		#弱智系统教务系统app接口
		public function app_login()
		{
			$url = $this->app_url."method=authUser&xh=".$this->userid."&pwd=".$this->pwd;
			$ch = curl_init($url); //初始化
			curl_setopt($ch,CURLOPT_HEADER,1); //将头文件的信息作为数据流输出
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); //返回获取的输出文本流
			$content = curl_exec($ch); //执行curl并赋值给$content
			preg_match_all('/Set-Cookie:(.*);/',$content,$str); //正则匹配
			preg_match_all('/(.*)}/',$content,$res1);
			$this->app_cookie = $str[1]; //获得COOKIE（SESSIONID）
			curl_close($ch); //关闭curl
			$res1 = $res1[0][0];
			$res = json_decode($res1,JSON_UNESCAPED_UNICODE);
			if (trim($res['msg'])=="登录成功") {
				$this->app_state = TRUE;
				$this->app_token = $res['token'];
			}else{
				$this->app_state = FALSE;
			}
			return $res1;
		}
		//获取学年学期
		public function app_getXnxq($userid=0)
		{
			$xh=$userid?$userid:$this->userid;
			$url = "http://csujwc.its.csu.edu.cn/app.do?method=getXnxq&xh=".$xh;
			return $this->app_http($url);
		}
		public function app_getCurrentTime()
		{
			$url = $this->app_url."method=getCurrentTime&currDate=".date('Y-m-d',time());
			return $this->app_http($url);
		}
		#获取用户信息，传入用户学号
		public function app_getUserInfo($userid=0)
		{
			if ($userid==0) {
				$url = $this->app_url."method=getUserInfo&xh=".$this->userid;
			}else{
				$url = $this->app_url."method=getUserInfo&xh=".$userid;
			}
			return $this->app_http($url);
			
		}
		#获取学生信息(推送专用)暂时无法使用flag=0
		public function app_getStudentIdInfo($userid=0)
		{
			if ($userid==0) {
				$url = $this->app_url."method=getStudentIdInfo&xh=".$this->userid;
			}else{
				$url = $this->app_url."method=getStudentIdInfo&xh=".$userid;
			}
			return $this->app_http($url);
		}
		#查询课表
		public function app_getKbcxAzc($userid=0,$xnxqid='2017-2018-1',$zc=11)
		{
			if ($userid==0) {
				$url = $this->app_url."method=getKbcxAzc&xh=".$this->userid."&xnxqid=$xnxqid&zc=$zc";
			}else{
				$url = $this->app_url."method=getKbcxAzc&xh=".$userid."&xnxqid=$xnxqid&zc=$zc";
			}
			return $this->app_http($url);
		}
		#教室查询
		public function app_getKxJscx($value='')
		{
			$time = date("Y-m-d",time());
			$idleTime = ['allday','am','pm','night','0102','0304','0506','0708'];
			$xqid = ['校本部'=>1,'南校区'=>2,'铁道校区'=>3,'湘雅新校区'=>4,'湘雅老校区'=>5,'湘雅医院'=>6,'湘雅二医院'=>7,'湘雅三医院'=>8,'新校区'=>9];
			#教室容量
			$num = ['_30','30-40','40-50','%2B60'];
			$url = $this->app_url."method=getKxJscx&time=$time&idleTime=".$idleTime[4]."&xqid=".$xqid['新校区']."&jxlid={jxlid}&classroomNumber={num}";
			echo $url;
			var_dump($idleTime);
		}
		//成绩查询
		public function app_getCjcx($userid=0)
		{
			$xnxqid = ["2013-2014-1","2013-2014-2","2014-2015-1","2014-2015-2","2015-2016-1","2015-2016-2","2016-2017-1","2016-2017-2","2017-2018-1"];
			$xh=$userid?$userid:$this->userid;
			$url = $this->app_url."method=getCjcx&xh=".$xh."&xnxqid=".array_reverse($xnxqid)[1];
			return $this->app_http($url);
		}
		public function app_getAllgrades($userid=0)
		{
			$xnxqid = ["2013-2014-1","2013-2014-2","2014-2015-1","2014-2015-2","2015-2016-1","2015-2016-2","2016-2017-1","2016-2017-2","2017-2018-1"];
			$xh=$userid?$userid:$this->userid;
			$url = $this->app_url."method=getCjcx&xh=".$xh;
			return $this->app_http($url);
		}
		//考试查询
		public function app_getKscx($userid=0)
		{
			$xh=$userid?$userid:$this->userid;
			$url = $this->app_url."method=getKscx&xh=".$xh;
			return $this->app_http($url);
		}
		//学籍预警
		public function app_getEarlyWarnInfo($userid=0)
		{
			$xh=$userid?$userid:$this->userid;
			$url = $this->app_url."method=getEarlyWarnInfo&xh=".$xh."&history=";echo $url;
			return [$this->app_http($url."0"),$this->app_http($url."1")];
		}
		//校区查询
		public function app_getXqcx()
		{
			$url = "http://csujwc.its.csu.edu.cn/app.do?method=getXqcx";
			return $this->app_http($url);
		}
		//院系查询
		public function app_getYxcx()
		{
			$url = "http://csujwc.its.csu.edu.cn/app.do?method=getYxcx";
			return $this->app_http($url);
		}
		//专业查询
		public function app_getZycx()
		{
			$url = "http://csujwc.its.csu.edu.cn/app.do?method=getZycx";
			return $this->app_http($url);
		}
		//教学楼查询
		public function app_getJxlcx($xqid=9)
		{
			$url = "http://csujwc.its.csu.edu.cn/app.do?method=getJxlcx&xqid=".$xqid;
			return $this->app_http($url);
		}
	}

	$student = new CSU("4201150121",'f675324370');
	// $student->login();
	// if ($student->state) {
	// 	var_dump($re = $student->getSubjectTimetable());
	// }
	$student->app_login();
	// echo $student->app_getKbcxAzc('0701150108','2016-2017-2',3);
	// echo $student->app_getCurrentTime();
	// echo $student->app_getKxJscx();
	// echo $student->app_getUserInfo();
	// echo $student->app_getCjcx();
	// var_dump($student->app_getKscx());
	// var_dump($student->app_getEarlyWarnInfo());
	// echo $student->app_getXqcx();
	// echo $student->app_getYxcx();
	// echo $student->app_getZycx();
	// echo $student->app_getJxlcx(1);
	// echo $student->app_getXnxq();
 ?>