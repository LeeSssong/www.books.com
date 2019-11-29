<?php


namespace app\index\controller;


use think\Request;

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

        return ['status'=>$status, 'message'=>$result, 'data'=>$data];
    }
}