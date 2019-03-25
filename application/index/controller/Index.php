<?php
namespace app\index\controller;
use think\Controller;
use lib1\Test1;
//Vendor('phpqrcode.phpqrcode');
//vendor('Alipay.AlipaySubmit','','.class.php');  //vendor/Alipay/AlipaySubmit.class.php
require_once VENDOR_PATH.'phpqrcode/phpqrcode.php';
use phpqrcode\QRcode;
use think\Loader;
use think\Request;

class Index extends Controller
{
	public function sms(){
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		header('Access-Control-Allow-Methods: GET, POST, PUT');
		$number=input('number');
		Loader::import('alimsg.api_demo.SmsDemo',EXTEND_PATH);
		$code=$this->random();
		$msg=new \SmsDemo('LTAITvcY8LFQmsrF','HoFYMf9RHJwannAPJZdspvE1y5p78w');//\ 此处写的就是Access key id 和Access key secret
		$res = $msg->sendSms(
            //短信签名名称
            "宝山区社区警务信息平台",//此处填写你在阿里云平台配置的短信签名名称（第二步有说明）
            //短信模板code
            "SMS_143710665",//此处填写你在阿里云平台配置的短信模板code（第二步有说明）
            //短信接收者的手机号码
           	"$number",
            //模板信息
            Array(
                "code"=>$code,
            )
        );
        $response = array($res);
		exit(json_encode(array('status'=>0,'result'=>$response,'number'=>$number),JSON_UNESCAPED_UNICODE));
	}
	//生成所发送的验证码并返回
    public function random()
    {
        $length = 6;
        $char = '0123456789';
        $code = '';
        while(strlen($code) < $length){
            //截取字符串长度
            $code .= substr($char,(mt_rand()%strlen($char)),1);
        }
        return $code;
    }
    public function  test(){
        $test1=new Test1();
        return $test1->sh();
    }
	
	//二维码
    public function view($users_id=1)
    {
        //不带LOGO
        //生成二维码图片
        $object = new QRcode();//实例化二维码类
        $url='这里是二维码内容';//网址或者是文本内容
        $level=3;
        $size=4;
        $pathname = "./uploads/Qrcode";//public目录下面
        if(!is_dir($pathname)) { //若目录不存在则创建之
            mkdir($pathname);
        }
        $ad = $pathname . "/qrcode_" . rand(10000,99999) . ".png";
        $errorCorrectionLevel =intval($level) ;//容错级别
        $matrixPointSize = intval($size);//生成图片大小
        $object->png($url, $ad, $errorCorrectionLevel, $matrixPointSize, 2);

    }
    public function index()
    {
        $fromid=input('fromid');
        $toid=input('toid');
        $this->assign('fromid',$fromid);
        $this->assign('toid',$toid);
        return $this->fetch();
    }
    public function lists()
    {
        $fromid=input('fromid');
        $this->assign('fromid',$fromid);
        return $this->fetch();
    }
    public function merge(){
        $a = new stdClass();
        $a->id = 'id';
        $a->name = 'name';
        $b = [
            'id' => 'id',
            'name' => 'name',
        ];
        echo "<pre>";
        var_dump(json_encode($a));
        var_dump(json_encode($b));
        echo "</pre>";
    }
}
