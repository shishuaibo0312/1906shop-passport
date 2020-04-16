<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\ShopModel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cookie; 
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Redis;

class UserController extends Controller
{

    //登陆的视图
	public function login()
	{
	    return view('user.login');
	}


    //登陆的执行
	public function login_do()
	{
		$account=request()->account;
    	$pass=request()->pass;	
		$res=ShopModel::where(['name'=>$account])->orWhere(['mibble'=>$account])->orWhere(['email'=>$account])->first();
    	if($res){
    		$result=password_verify($pass,$res['pass']);
    		
			if($result){

                $data=[
                	'user_name'=>$res['name'],
                    'time'=>date('Y-m-d h:i:s',time()),
                    'ip'=>$_SERVER['REMOTE_ADDR'],
                    'url'=>env('APP_URL')
                ];
                //使用闭包函数，传递参数
                Mail::send('user.loginsuccess',$data,function($message) use ($account){ 	
					$user=ShopModel::where(['name'=>$account])->orWhere(['mibble'=>$account])->orWhere(['email'=>$account])->first();
					setcookie('user_name',$user['name'],NULL,'/','.1906.com',NULL,true);
					setcookie('user_id',$user['id'],NULL,'/','.1906.com',NULL,true);
					$to = [
						$user['email']
					];
					$message ->to($to)->subject('登陆成功');
            	});

				$token=Str::random(16);
				setcookie('token',$token,NULL,'/','.1906.com',NULL,true);


				$key='str:user:token'.$res['id'];
				$namekey='str:user:name'.$res['name'];
				Redis::setex($namekey,'3600',$res['name']);
				Redis::setex($key,'3600',$token);
				//Redis::expire($key,3600);

    			echo "<script>alert('登陆成功');location.href='http://shop.1906.com/';</script>";
               
    		}else{
    			echo "<script>alert('账号或密码错误');location.href='/login';</script>";
			}
		}else{
    		echo "<script>alert('账号不存在');location.href='/login';</script>";
    	}	
    }

	
	//注册视图
	public function register()
	{
        return view('user.register');
	}
	
	//执行注册
	public function store(Request $request)
	{
        request()->validate([
            'name' => 'required|unique:shop_user',
			'email'=>'required|email',
			'mibble'=>'required|regex:/^1[345789][0-9]{9}$/',
            'pass'=>'required|between:5,15',
            'passs'=>'same:pass'
        ],[ 
            'name.required'=>'请输入用户名',
            'name.unique'=>'用户名已存在',
			'email.required'=>'邮箱不能位空',
			'email.email'=>'邮箱格式不对',
			'pass.required'=>'密码不能位空',
			'mibble.required'=>'手机号不能为空',
			'mibble.regex'=>'手机号格式不对',
			'pass.between'=>'密码长短不够',
            'passs.same'=>'密码不一致'
        ]);
		$post=$request->except('_token');

        $post['pass']=password_hash($post['pass'],PASSWORD_BCRYPT);
        unset($post['passs']);
        $res=ShopModel::create($post);
        if($res){
			$data=[
			    'user_name' => $post['name'],
				'url' => "注册成功"
			];
			Mail::send('user.regemail',$data,function($message) use($post){
					$to = [
                        $post['email']
					];
					$message ->to($to)->subject('注册成功');
				});
				echo "<script>alert('恭喜用户注册成功,附加邮件请接收');location.href='/login';</script>";
        }
	}
	
	//修改密码
	public function changePass()
	{
		return view('user.changepass');
	}

	//执行修改密码
	public function dochangePass(Request $request)
	{
		$post=$request->except('_token');
		$user=session('user');
		$res=ShopModel::where('name','=',$user)->first()->toArray();

       
		$result=password_verify($post['pass'],$res['pass']);
		// echo $request;
		if($result != $res['pass']){
			echo "旧密码不正确";die;
		}

		if($post['newpass'] != $post['newpass2'] ){
			echo "新密码不一致";die;
		}
		
		$post['newpass']=password_hash($post['newpass'],PASSWORD_BCRYPT);
		$pass=ShopModel::where('name','=',$user)->update(['pass'=>$post['newpass']]);
		if($pass){
			echo "修改成功,请重新登录";
			$data=[
				'user_name' => $res['name'],
				'time'=>date('Y-m-d H:i:s'),
				'ip'=>$_SERVER['REMOTE_ADDR'],
			];
			Mail::send('pass.passemail',$data,function($message) use($res){
					$to = [
                        $res['email']
					];
					$message ->to($to)->subject('修改密码成功');
			});
			header('refresh:2;url=/login');
		}

	}

	//退出登录
	public function loginexit(){
	    $key='str:user:token'.$_COOKIE['user_id'];
		$namekey='str:user:name'.$_COOKIE['user_name'];
		Redis::setex($namekey,3600,NULL);
		Redis::setex($key,3600,NULL);


        echo "<script>alert('退出成功');location.href='/login';</script>";
	}


}
