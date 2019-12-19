<?php


namespace app\index\controller;
use think\Controller;
use think\Session;
use app\index\model\User as UserModel;

class Base extends Controller
{
    protected function _initialize()
    {
        parent::_initialize();
        define('APP_DEBUG',TRUE); // 开启调试模式
        define('DB_FIELD_CACHE',false);
        define('HTML_CACHE_ON',false);//
        define('USER_ID', Session::has('user_id') ? Session::get('user_id'):null);

    }

    //判断用户是否登陆,放在系统后台入口前面: index/index
    protected function isLogin()
    {
        if (is_null(USER_ID)) {
            $this -> error('用户未登陆,无权访问',url('user/login'));
        }
    }

    //防止用户重复登陆,放在登陆操作前面:user/login
    protected function alreadyLogin(){
        if (USER_ID) {
            $this -> error('用户已经登陆,请勿重复登陆',url('user/index'));
        }
    }
}