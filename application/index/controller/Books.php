<?php


namespace app\index\controller;
use app\index\model\Info as InfoModel;
use app\index\model\User as UserModel;
use think\Request;


class Books extends Base
{
    //图书查询页面
    public function books()
    {
        //通过分页显示数据
        $bookslist = InfoModel::paginate(12);

        //TODO:获取记录数量

        $this->view->assign('booksList',$bookslist);

        return $this->view->fetch('books');
    }

    //图书详情页
    public function details(Request $request)
    {
        $book_name = $request->param('name');

        //获取书本名字对应的数据，并返回对应模型
        $result = InfoModel::get($book_name);
        $author = InfoModel::get(['name' => $book_name]);

        $name = $book_name;
        $author = InfoModel::get(['name' => $book_name]);
        $press = InfoModel::get(['name' => $book_name]);
        $press_time = InfoModel::get(['name' => $book_name]);
        $price = InfoModel::get(['name' => $book_name]);
        $ISBN = InfoModel::get(['name' => $book_name]);
        $desc = InfoModel::get(['name' => $book_name]);
//        $data = [
//            'name'=>(string)$name,
//            'author'=>(string)$author,
//            'press'=>(string)$press,
//            'press_time'=>(string)$press_time,
//            'price'=>(string)$price,
//            'ISBN'=>(string)$ISBN,
//            'desc'=>(string)$desc
//            ];
        return ['name'=>(string)$name,'author'=>(string)$author,'author'=>(string)$author,
            'press'=>(string)$press,'press_time'=>(string)$press_time,'price'=>(string)$price,
            'ISBN'=>(string)$ISBN,'desc'=>(string)$desc];
//        return json(['data'=>$data]);
//        $this->ajaxReturn($result);

//
//        //根据模型取出user_id字段对应值
//        $book_user_id = $result->getData('user_id');
//
//        //根据书本的user_id取出user表中对应用户的id
//        $user_id = UserModel::get(['id' => $book_user_id]);
//
//        //根据用户id取出用户名字
//        $user_name = $user_id->getData('name');
//
//        $this->view->assign('user_name',$user_name);
//        $this->view->assign('book_info',$result);

//        return ['data'=>$result];
    }
}