<?php


namespace app\index\controller;


use app\index\model\User as UserModel;
use think\Request;
use think\Session;
use think\Db;

class User extends Base
{
    public function login(){
        $this->alreadyLogin();
        return $this->view->fetch();
    }

    public function checkLogin(Request $request)
    {
        $status = 0;
        $result = '验证失败';
        $data = $request->param();

        //验证规则
        $rule = [
            'name|用户名' => 'require',
            'password|密码'=>'require',
        ];

        //验证数据 $this->validate($data, $rule, $msg)
        $result = $this -> validate($data, $rule);

        //通过验证后,进行数据表查询
        //此处必须全等===才可以,因为验证不通过,$result保存错误信息字符串,返回非零
        if (true === $result) {
            //查询条件
            $map = [
                'name' => $data['name'],
                'password' => $data['password']
            ];

            //数据表查询,返回模型对象
            $user = UserModel::get($map);
            if (null === $user) {
                $result = '没有该用户,请检查';
            } else {
                $status = 1;
                $result = '验证通过,点击[确定]后进入后台';

                //创建2个session,用来检测用户登陆状态和防止重复登陆
                Session::set('user_id', $user -> id);
                Session::set('user_info', $user -> getData());

                //更新用户登录次数:自增1
                $user -> setInc('login_count');
            }
        }

        return ['status'=>$status, 'message'=>$result, 'data'=>$data];
    }

    public function register()
    {
        return $this->view->fetch();
    }

    public function checkUserName(Request $request)
    {
        $userName = trim($request->param('name'));
        $status = 1;
        $message = '用户名不可用';
        if (!empty($userName)) {
            if (UserModel::get(['name' => $userName])) {
                //如果查询到该用户名
                $status = 0;
                $message = '用户名重复，请重新输入';
            } else {
                $message = '用户名可用';
            }
        } else {
            $message = '用户名不可为空';
        }

        return ['status'=>$status, 'message'=>$message];
    }

    public function addUser(Request $request)
    {
        $status = 0;
        $result = '验证失败';
        $message = 'null';
        $swapMessage = '';
        $data = $request -> param();
        //验证规则
        $rule = [
            'name|用户名' => 'require',
            'password|密码'=>'require',
        ];

        //验证数据 $this->validate($data, $rule, $msg)
        $result = $this -> validate($data, $rule);

        if ($result === true) {
            $swapMessage = '此时result为true';
            $user = new UserModel($_POST);

            $user->allowField(true)->save();
            if ($user === null) {
                $status = 0;
                $message = '添加失败';
                $swapMessage = $message;
            } else {
                $status = 1;
                $message = '添加成功，点击[确定]返回登陆界面';
                $swapMessage = $message;
            }
        } else {
            $status = 0;
            $swapMessage = $result;
        }
        return ['status'=>$status, 'message'=>$swapMessage];    //传回register.html的data属性及其值
    }
}