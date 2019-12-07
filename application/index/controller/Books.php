<?php


namespace app\index\controller;
use app\index\model\Info as InfoModel;
use app\index\model\User as UserModel;
use think\Request;


class Books extends Base
{
    //图书查询页面
    public function books(Request $request)
    {
        //通过分页显示数据
        $bookslist = InfoModel::paginate(12);
        $count = InfoModel::count();
        //TODO:获取记录数量

        $this->view->assign('count',$count);
        $this->view->assign('booksList',$bookslist);

        return $this->view->fetch('books');
    }

    //图书详情页
    public function details(Request $request)
    {
        $book_name = $request->param('name');
        $message = '';
        //获取书本名字对应的数据，并返回对应模型
        $result = InfoModel::get(['name' => $book_name]);

        $name = $book_name;
        $author = $result->author;
        $press = $result->press;
        $press_time = $result->press_time;
        $price = $result->price;
        $ISBN = $result->ISBN;
        $desc = $result->desc;

        return ['name'=>$name,'author'=>$author,'author'=>$author,
            'press'=>$press,'press_time'=>$press_time,'price'=>$price,
            'ISBN'=>$ISBN,'desc'=>$desc];
    }

    public function borrow()
    {
        return $this->view->fetch();
    }

    public  function search(Request $request)
    {
        $data = $request->post();
        $book_name = '追风筝的人';
        $book = InfoModel::get(['name' => $book_name]);
//        $book = InfoModel::get(['id' => $id]);

        if (!empty($data['type'])) {
            if ($data['type'] == 'name') {
                $book = InfoModel::get(['name'=>$data['keyword']]);
            } else if ($data['type'] == 'num') {
                $book = InfoModel::get(['id'=>$data['keyword']]);
            }
        }
//        $this->view->assign('booksList',$book);
        if (null === $book) {
            $this->view->assign('id',1);
            $this->view->assign('name',2);
            $this->view->assign('author',3);
            $this->view->assign('user_id',4);
        } else {
            $this->view->assign('id',$book->id);
            $this->view->assign('name',$book->name);
            $this->view->assign('author',$book->author);
            $this->view->assign('user_id',$book->user_id);
        }

        return $this->view->fetch();
    }
}