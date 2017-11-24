





## 一·使用说明：
	*先上代码
		//引入文件
		require_once(csu.class.php);
		//登录用户名
		$user = "xxxxxxxxxx";
		//登录密码
		$pwd = 'xxxxxxx';
		//实例化csu类
		$student = new CSU($user,$pwd);
		//执行登录操作（若不习惯，则修改构造函数末尾执行$this->login()）
		$student->login();
		if($student->state){//如果登录成功进行自己的功能操作
			//进行功能性操作,如下为为获取原始成绩
			$re = $student->getOriginGrade();
		}else{
			echo "登录失败";
		}
		
		//对象销毁时候自动执行logout()销毁cookie不必担心大规模登录cookie缓存问题*

## 二·功能函数：
### 1.getGrade():
获取该学生成绩（无平时成绩）
### 2.getOriginGrade():
获取该学生成绩（有平时成绩）已经提取出来课程编号
### 3.getRank():
获取该学生的学分，加权成绩，以及专业排名
### 4.getSubjectTimetable():
获取该学生的最新课程表（若要查询第几周的成绩，需进一步处理数据）
### 5.getLevelGrade():
获取该学生的等级考试成绩（教务系统里存在的，可能不是最新的）
### 6.getPingjiaoInfo():
获取该学生的评教信息

# 三·类属性介绍
### 1.userid
该学生的学号，在实例化对象的时候赋值
### 2.pwd
该学生的密码，在实例化对象的时候赋值
### 3.encoded
登录教务系统的加密码（base64），这个在使用时无需担心，执行login()自动生成
### 4.cookieJar
登录获取保存cookie的文件名
### 5.state
登录是否成功的状态(TRUE为成功，FALSE为失败)
### 6.pingjiaoInfo
评教信息为一键评教提供信息以及url
### 7.PreUrl
域名前缀http://csujwc.its.csu.edu.cn或者http://jwctest.its.csu.edu.cn/的任意一个
## 四·依赖说明 
为方便提取信息，仅用正则匹配有点麻烦，使用了simpleHtmlDom这个类库，相当于简单的html解析器，配合正则，快速定位自己需要的信息。
至于simpleHtmlDom使用方法具体查看./lib/simple_html_dom/example里内容或者查看手册./lib/simple_html_dom/manual
## 五· 教务系统域名问题
教务系统有两个域名可以使用，一个是http://csujwc.its.csu.edu.cn，另一个是测试用的http://jwctest.its.csu.edu.cn/
如果不加设置默认使用http://csujwc.its.csu.edu.cn
若想要设置使用那个域名，可在实例化对象之前使用setPreUrl方法
#### 代码如下：
	$student = new CSU($user,$pwd);
	//传入参数1为使用 http://csujwc.its.csu.edu.cn；传入参数2为使用 http://jwctest.its.csu.edu.cn/
	$student->setsetPreUrl(2);
	$student->login();
。。。进行你的操作
暂时情况这两个链接都可以成功使用，目测jwctest这个将来教务处会关闭，但是知道这个域名的人也不多，（比较安全，你懂的~~~）

## 五·类库应用
* 1.若只是简单应用，直接require_once('csu.class.php')实例化就可以简单应用
* 2.若要应用框架进行复杂使用，比如tp里面为了安全考虑已经资源文件只能放在public目录，默认设置的cookie目录是当前的的./cookie，但是tp5里可能无法在扩展类库文件夹（extend）直接创建临时文件，解决方法在实例化对象之后使用绝对路径设置cookie目录(PS.对该目录以及子文件必须要有读写权限否则可能会导致登录失败)
* 3.在框架里应用时候，因为存在依赖解决方法两个:
		(1)分别下载两个类，删除或者注释csu.class.php里第二行，加载两个类。
		(2)使用工具composer，（composer require mgargano/simplehtmldom）注意命名空间的问题就可以啦
## 六·目前问题
* 1.在使用simplehemldom时候，找到节点访问plaintext获取内容时候报一个不存在对象属性的warning，暂时无法解决，暂时采用屏蔽了warning错误
* 2.暂时想到的能从教务系统获取的信息就这些，希望大家集思广益，丰富这个类库更好的服务广大CSUERS，进步新媒体技术
## 七·新增app接口
### 1.简介
* 弱智科技（暂时这么叫他吧，习惯了），有一个app叫什么智校园，接下来说的是关于这个app的接口事情，我利用抓包工具fiddler，抓取了这个app的接口数据，发觉这个接口很有意思，（有一个小bug），可以实现一次登录，就可以查看多人信息
### 2.新增属性
* (1)app_url:教务app接口域名
* (2)app_state:记录了是否成功登录了app，布尔值
* (3)app_token:成功登录后服务器返回登录app的token（使用中用不到他）
* (4)app_cookie:成功登录后服务器返回登录app的cookie（使用中也用不到他2333，用完自动销毁了）
### 3.新增方法
#####  (1)app_http($url,$postData=""):
* 内部封装的发送http请求的方法，pravate方法
#####  (2)app_login():
* 登录app的方法，实例化对象后可以使用
#####  (3)app_getXnxq():
* 向服务器发起请求获得当前的学年学期，返回json数据
#####  (4)app_getCurrentTime():
* 获得当前时间，返回json数据
#####  (4)app_getUserInfo($userid=0):
* 获得学生信息，不传入学号默认为实例化对象的学号加，即$this->$userid,返回json数据
#####  (5)app_getStudentIdInfo($userid=0):
* 获得学生用户id信息，但是返回flag=0，暂时不清楚错误原因，不建议使用这个接口
#####  (6)app_getKbcxAzc($userid=0,$xnxqid='2017-2018-1',$zc=11)：
* 获取学生的课程表信息，传入学生学号，学年学期id，以及周数
#####  (6)app_getKxJscx():
* 空闲教室查询，传入参数较多，看源码吧，都是汉语拼音简写(别问我为啥这么命名，开发app的就是这么搞的233)
#####  (7)app_getCjcx($userid=0):
* 成绩查询，传入学号，和学年学期id（暂时不用传默认所有的，按需修改源码），不传学年学期id，默认获得所有的成绩，也可以使用下一个接口
#####  (8)app_getAllgrades($userid=0):
* 获取学生所有成绩（到目前为止所有的成绩）
#####  (9)app_getKscx($userid=0):
* 考试查询接口
#####  (10)app_getEarlyWarnInfo($userid=0):
* 学籍预警接口
#####  (11)app_getXqcx()：
* 校区查询接口
#####  (12)app_getYxcx()：
* 院系查询接口
#####  (13)app_getZycx()：
* 专业查询接口
#####  (14)app_getJxlcx($xqid=9)：
* 教学楼查询，输入参数是就校区id
### 4.使用实例
		$student = new CSU("4201150121",'xxx');
		$student->app_login();
		echo $student->app_getKbcxAzc('0701150108','2016-2017-2',3);
		echo $student->app_getCurrentTime();
		echo $student->app_getKxJscx();
		echo $student->app_getUserInfo();
		echo $student->app_getCjcx();
		var_dump($student->app_getKscx());
		var_dump($student->app_getEarlyWarnInfo());
		echo $student->app_getXqcx();
		echo $student->app_getYxcx();
		echo $student->app_getZycx();
		echo $student->app_getJxlcx(1);
		echo $student->app_getXnxq();