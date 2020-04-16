<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\ShopModel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;

class ApiController extends Controller
{
    //api登录接口
    function login(){
        $account=request()->name;
        $pass=request()->pass;
        //echo $account;
        $user=ShopModel::where(['name'=>$account])->orWhere(['mibble'=>$account])->orWhere(['email'=>$account])->first();

        if($user){
            $result=password_verify($pass,$user['pass']);
            if($result){

                $token=Str::random(16);
//                $key='str:user:token'.$user['id'];
//                Redis::set($key,$token);
//                Redis::expire($key,3600);
                $key='str:user:token'.$user['id'];
                $namekey='str:user:name'.$user['name'];
                Redis::setex($namekey,'3600',$user['name']);
                Redis::setex($key,'3600',$token);
                $data=[
                    'error'=>0,
                    'uid'=>$user['id'],
                    'token'=>$token,
                    'status'=>'登陆成功',
                    'user_name'=>$user['name']
                ];
                return $data;
            }else{
                $data=[
                    'error'=>'1',
                    'status'=>'账号或密码错误'
                ];
                return $data;

            }
        }else{
            $data=[
                'error'=>'1',
                'status'=>'用户不存在'
            ];
            return $data;
        }

    }

    //退出的接口
    function logout(){
        $account=request()->name;
        //echo $account;
        $user=ShopModel::where(['name'=>$account])->orWhere(['mibble'=>$account])->orWhere(['email'=>$account])->first();
        if($user){
           // echo 111;
            $key='str:user:token'.$user['id'];
            $namekey='str:user:name'.$user['name'];
            $name=Redis::setex($namekey,3600,NULL);
            $token=Redis::setex($key,3600,NULL);
            if($name=='ok'||$token=='ok'){
               $data=[
                   'error'=>0,
                   'status'=>'退出成功',
                   'name'=>$user['name']
               ];
                return $data;
            }
        }else{
            $data=[
                'error'=>1,
                'status'=>'账号有误'
            ];
            return $data;

        }

    }
}
