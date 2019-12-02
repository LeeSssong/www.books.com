<?php
namespace app\index\controller;



class Index extends Base
{
    public function index()
    {
        $this->isLogin();
        $this->redirect('User/index');
    }

    public function userIndex()
    {
        return $this->view->fetch();
    }
}
