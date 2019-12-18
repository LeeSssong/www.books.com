<?php


namespace app\index\controller;
use app\index\controller\Base;

class Admin extends Base
{
    public function index()
    {
        return $this->view->fetch();
    }
}