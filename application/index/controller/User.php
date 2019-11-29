<?php


namespace app\index\controller;

class User extends Base
{
    public function login(){
        $this->alreadyLogin();
        return $this->view->fetch();
    }
}