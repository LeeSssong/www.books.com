<?php
namespace app\index\controller;

class Index extends Base
{
    public function index()
    {
        $this->isLogin();
        return $this->view->fetch();
    }
}
