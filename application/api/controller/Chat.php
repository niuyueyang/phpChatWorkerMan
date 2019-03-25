<?php
namespace app\api\controller;
use phpDocumentor\Reflection\Types\Array_;
use think\Controller;
use think\Request;
use think\Db;

class Chat extends Controller
{
    //文本数据保存
    public function save()
    {
        if(Request::instance()->isAjax()){
            $message=input('post.');
            $datas['fromid']=$message['message']['fromid'];
            $datas['fromname']=$this->getName($message['message']['fromid'])['nickname'];
            $datas['toname']=$this->getName($message['message']['toid'])['nickname'];
            $datas['toid']=$message['message']['toid'];
            $datas['content']=$message['message']['data'];
            $datas['time']=$message['message']['time'];
            //$datas['isread']=$message['message']['isread'];
            $datas['isread']=0;
            $datas['type']=1;
            Db::name('communication')->insert($datas);
        }
    }
    //获取用户名
    public function getName($uid){
        $userinfo=Db::name('user')->where("id=".$uid."")->field('nickname')->find();
        return $userinfo;
    }

    //获取用户头像
    public function getHead(){
        if(Request::instance()->isAjax()){
            $fromid=input('fromid');
            $toid=input('toid');
            $frominfo=Db::name('user')->where("id=".$fromid."")->field('headimgurl')->find();
            $toinfo=Db::name('user')->where("id=".$toid."")->field('headimgurl')->find();
            exit(json_encode(array('from_head'=>$frominfo['headimgurl'],'to_head'=>$toinfo['headimgurl']),JSON_UNESCAPED_UNICODE));
        }
    }

    //获取用户名
    public function getNames(){
        if(Request::instance()->isAjax()) {
            $toid=input('toid');
            $userinfo = Db::name('user')->where("id=" . $toid . "")->field('nickname')->find();
            exit(json_encode(array('toname' => $userinfo['nickname']), JSON_UNESCAPED_UNICODE));
        }
    }
    /**
     * 页面加载返回聊天记录
     */
    public function load(){
        if(Request::instance()->isAjax()){
            $fromid = input('fromid');
            $toid = input('toid');
            $count =  Db::name('communication')->where('(fromid=:fromid and toid=:toid) || (fromid=:toid1 and toid=:fromid1)',['fromid'=>$fromid,'toid'=>$toid,'toid1'=>$toid,'fromid1'=>$fromid])->count('id');
            if($count>=10){
                $message =    Db::name('communication')->where('(fromid=:fromid and toid=:toid) || (fromid=:toid1 and toid=:fromid1)',['fromid'=>$fromid,'toid'=>$toid,'toid1'=>$toid,'fromid1'=>$fromid])->limit($count-10,10)->order('id')->select();
            }else{
                $message =   Db::name('communication')->where('(fromid=:fromid and toid=:toid) || (fromid=:toid1 and toid=:fromid1)',['fromid'=>$fromid,'toid'=>$toid,'toid1'=>$toid,'fromid1'=>$fromid])->order('id')->select();
            }
            $imgs=array('.png','.jpeg','.gif','.jpg');
            for($i=0;$i<count($message);$i++){
                $stuffix=strtolower(strchr($message[$i]['content'],'.'));
                $everyStr=$message[$i]['content'];
                if(stripos($everyStr,'.png')||stripos($everyStr,'.jpg')||stripos($everyStr,'.jpeg')||stripos($everyStr,'.gif')){
                    $message[$i]['content']='http://127.0.0.1/workermanTest/public/uploads/'.$message[$i]['content'];
                }
            }
            exit(json_encode(array('message' => $message), JSON_UNESCAPED_UNICODE));

        }
    }
    //图片上传
    public function uploadimg(){
        $file=$_FILES['file'];
        $fromid=input('fromid');
        $toid=input('toid');
        $online=input('online');
        $stuffix=strtolower(strchr($file['name'],'.'));
        $type=array('.gif','.jpg','.jpeg','.png');
        if(!in_array($stuffix,$type)){
            return array('status'=>1,'msg'=>'type error');
        }
        if($file['size']/1024>5120){
            return array('status'=>1,'msg'=>'img is too large');
        }
        $filename=uniqid('chat_img',false);
        $uploadpath=ROOT_PATH.'public\\uploads\\';
        $file_up=$uploadpath.$filename.$stuffix;
        $result=move_uploaded_file($file['tmp_name'],$file_up);
        if($result){
            $name=$filename.$stuffix;
            $data['content']=$name;
            $data['fromid']=$fromid;
            $data['toid']=$toid;
            $data['fromname']=$this->getName($fromid);
            $data['toname']=$this->getName($toid);
            $data['time']=time();
            $data['isread']=0;
            $data['type']=2;
            $message_id=Db::name('communication')->insertGetId($data);
            if($message_id){
                return array('status'=>0,'msg'=>'success','img_name'=>$name,'url'=>'http://127.0.0.1/workermanTest/public/uploads/'.$filename.$stuffix);
            }else{
                return array('status'=>1,'msg'=>'img error');
            }
        }
    }
    //修改未读信息为已读
    public function change(){
        if(Request::instance()->isAjax()){
            $toid=input('fromid');
            $fromid=input('toid');
            Db::name('communication')->where(['fromid'=>$fromid,'toid'=>$toid])->update(['isread'=>1]);
        }
    }
    //获取图片
    public function get_head_one($uid){
        $fromhead=Db::name('user')->where('id',$uid)->field('headimgurl')->find();
        return $fromhead['headimgurl'];
    }
    //获取未读信息
    public function getCountNoread($fromid,$toid){
        return Db::name('communication')->where(['fromid'=>$fromid,'toid'=>$toid,'isread'=>0])->count('id');
    }
    //获取最后一条信息
    public function getLastMessage($fromid,$toid){
        $info = Db::name('communication')->where("fromid=$fromid and toid=$toid or fromid=$toid and toid=$fromid")->order('id DESC')->find();
        return $info;
    }
    //用户列表
    public function get_list(){
        if(Request::instance()->isAjax()){
            $fromid=input('fromid');
//            $info1=Db::name('communication')->field(['fromid','toid','fromname'])->where('toid',$fromid)->group('fromid')->select();
//            $info2=Db::name('communication')->field(['fromid','toid','fromname'])->where('fromid',$fromid)->group('toid')->select();
//            var_dump($info1,$info2);
//            $info=array_merge_recursive($info1,$info2);
//            if(count($info1)==0&&count($info2)!=0||count($info2)==0&&count($info1)!=0){
//                $info=array_merge_recursive($info1,$info2);
//            }else if(count($info1)!=0){
//                $info=$info1;
//            }else{
//                $info=$info2;
//            }
            $info=Db::name('communication')->field(['fromid','toid','fromname'])->where('toid',$fromid)->group('fromid')->select();
            $list1=Db::name('communication')->field(['fromid','toid','fromname','toname','content','time'])->where('toid',$fromid)->select();
            $list2=Db::name('communication')->field(['fromid','toid','fromname','toname','content','time'])->where('fromid',$fromid)->select();
            $list=array_merge_recursive($list1,$list2);
            $result=array();
            for($i=0;$i<count($info);$i++){
                $rows['head_url']=$this->get_head_one($info[$i]['fromid']);
                $rows['username']=$info[$i]['fromname'];
                $rows['countNoread']=$this->getCountNoread($info[$i]['fromid'],$info[$i]['toid']);
                $rows['last_message']=$this->getLastMessage($info[$i]['fromid'],$info[$i]['toid']);
                $rows['chat_page']="http://127.0.0.1/workermanTest/public/index.php/index/index/index?fromid={$info[$i]['toid']}&toid={$info[$i]['fromid']}";
                $rows['lists']=$list;
                array_push($result,$rows);
            }
            exit(json_encode(array('message' => $result), JSON_UNESCAPED_UNICODE));
//            $rows=array_map(function ($res){
//                return [
//                    'head_url'=>$this->get_head_one($res['fromid']),
//                    'username'=>$res['fromname'],
//                    'countNoread'=>$this->getCountNoread($res['fromid'],$res['toid']),
//                    'last_message'=>$this->getLastMessage($res['fromid'],$res['toid']),
//                    'chat_page'=>"http://chat.com/index.php/index/index/index?fromid={$res['toid']}&toid={$res['fromid']}"
//                ];
//            },$info);
//            return $rows;
        }
    }
}