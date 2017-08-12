一·使用说明：
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
	
	//对象销毁时候自动执行logout()销毁cookie不必担心大规模登录cookie缓存问题

二·功能函数：
	1.getGrade()
		获取该学生成绩（无平时成绩）
	2.getOriginGrade()
		获取该学生成绩（有平时成绩）已经提取出来课程编号
	3.getRank()
		获取该学生的学分，加权成绩，以及专业排名
	4.getSubjectTimetable()
		获取该学生的最新课程表（若要查询第几周的成绩，需进一步处理数据）
	5.getLevelGrade()
		获取该学生的等级考试成绩（教务系统里存在的，可能不是最新的）

三·类属性介绍
	1.userid
		该学生的学号，在实例化对象的时候赋值
	2.pwd
		该学生的密码，在实例化对象的时候赋值
	3.encoded
		登录教务系统的加密码（base64），这个在使用时无需担心，执行login()自动生成
	4.cookieJar
		登录获取保存cookie的文件名
	5.state
		登录是否成功的状态(TRUE为成功，FALSE为失败)
四·依赖说明
	为方便提取信息，仅用正则匹配有点麻烦，使用了simpleHtmlDom这个类库，相当于简单的html解析器，配合正则，快速定位自己需要的信息。
	至于simpleHtmlDom使用方法具体查看./lib/simple_html_dom/example里内容或者查看手册./lib/simple_html_dom/manual
五· csu.class.php与csu.classs1.php
	这两个文件都可以使用只不过使用的链接不同
	csu.class.php ：使用的链接的域名是 http://jwctest.its.csu.edu.cn
	csu.class1.php ：使用的链接的域名是 http://csujwc.its.csu.edu.cn
	暂时情况这两个链接都可以成功使用，目测jwctest这个将来教务处会关闭，但是知道这个域名的人也不多，（比较安全，你懂的~~~）
五·类库应用
	1.若只是简单应用，直接require_once('csu.class.php')实例化就可以简单应用
	2.若要应用框架进行复杂使用，比如tp里面为了安全考虑已经资源文件只能放在public目录，默认设置的cookie目录是当前的的./cookie，但是tp5里可能无法在扩展类库文件夹（extend）直接创建临时文件，解决方法在实例化对象之后使用绝对路径设置cookie目录(PS.对该目录以及子文件必须要有读写权限否则可能会导致登录失败)
	3.在框架里应用时候，因为存在依赖解决方法两个:
		(1)分别下载两个类，删除或者注释csu.class.php里第二行，加载两个类。
		(2)使用工具composer，（composer require mgargano/simplehtmldom）注意命名空间的问题就可以啦
六·目前问题
	1.在使用simplehemldom时候，找到节点访问plaintext获取内容时候报一个不存在对象属性的warning，暂时无法解决，暂时采用屏蔽了warning错误
	2.暂时想到的能从教务系统获取的信息就这些，希望大家集思广益，丰富这个类库更好的服务广大CSUERS，进步新媒体技术
