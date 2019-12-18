<?php


namespace app\index\controller;
use app\index\model\Borrow_list;
use app\index\model\Info as InfoModel;
use app\index\model\User as UserModel;
use app\index\model\Borrow_list as BorrowModel;
use think\Request;
use think\Session;


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

    public function search(Request $request)
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
            $this->view->assign('borrow',$book->borrow);
        }

        return $this->view->fetch();
    }

    public function backBook(Request $request)
    {
        $book_name = $request->param('name');

        $borrow = new Borrow_list();
        $info = new InfoModel();
        $user = new UserModel();

        //将该本书的borrow字段改为null
        $infoData = ['borrow' => ""];
        $infoCondition = ['name' => $book_name];
        $infoResult = $info->update($infoData, $infoCondition);

        //将已借书本数量减一
        $id = Session::get('user_id');
        $userResult = $user->where(array('id' => $id))->setDec('borrow_num', 1);

        //在借书表中将关于此书的信息删除
        $borrowResult = $borrow->where(array('name' => $book_name))->delete();


        if ($infoResult == true) {
            if ($borrowResult == true) {
                if ($userResult == true) {
                    $message = '归还成功';
                    $status = '1';
                    return ['message' => $message, 'status' => $status];
                } else {
                    $message = '个人信息修改错误';
                    $status = '0';
                    return ['message' => $message, 'status' => $status];
                }
            }else{
                $message = '借书信息修改错误';
                $status = '0';
                return ['message' => $message, 'status' => $status];
            }
        } else {
            $message = '图书信息修改错误';
            $status = '0';
            return ['message' => $message, 'status' => $status];
        }

        $message = 'null';
        $status = '0';
        return ['message' => $message, 'status' => $status];
    }

    public function borrowBook(Request $request)
    {
        $status = 1;
        $message = '借阅成功';
        $book_name = $request->param('name');
        $user = new UserModel();
        $info = new InfoModel();
        $borrow = new BorrowModel();

        //将用户的借书数量加一
        $id = Session::get('user_id');
        $userResult = $user->where(array('id' => $id))->setInc('borrow_num', 1);

        //将该本书的borrow字段改为该用户id
        $infoData = ['borrow' => $id];
        $infoCondition = ['name' => $book_name];
        $infoResult = $info->update($infoData, $infoCondition);

        //添加条目到borrow表
        $info = $info->get(['name'=>$book_name]);
        $info_id = $info->id;
        $borrow_date = date('Y-m-d',time());
        $back_date = date("Y-m-d",strtotime('+2 month'));
        $data = ['info_id'=>$info_id,'user_id'=>$id,'borrow_date'=>$borrow_date,'back_date'=>$back_date,'name'=>$book_name];
        $Info = new BorrowModel();
        $borrowResult = $Info->insert($data);

        if ($infoResult == true) {
            if ($borrowResult == true) {
                if ($userResult == true) {
                    $message = '借阅成功';
                    $status = '1';
                    return ['message' => $message, 'status' => $status];
                } else {
                    $message = '个人信息修改错误';
                    $status = '0';
                    return ['message' => $message, 'status' => $status];
                }
            }else{
                $message = '借书信息修改错误';
                $status = '0';
                return ['message' => $message, 'status' => $status];
            }
        } else {
            $message = '图书信息修改错误';
            $status = '0';
            return ['message' => $message, 'status' => $status];
        }

        return ['status'=>$status, 'message'=>$message];
    }
}