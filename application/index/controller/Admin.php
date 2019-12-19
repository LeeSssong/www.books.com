<?php


namespace app\index\controller;
use app\index\controller\Base;
use app\index\model\Borrow_list as BorrowModel;
use app\index\model\Info as InfoModel;
use app\index\model\User as UserModel;
use think\Db;
use think\Request;
use think\Session;
use app\index\controller\Books;

class Admin extends User
{
    /*
     * 管理员功能：
     * 首页：查看用户人数、借阅书籍数、图书馆藏书数
     * 书籍管理：增删改查
     * 读者管理：增删改查
     * */
    public function index()
    {
        //管理员用户名
        $this->view->assign('name',Session::get('user_info.name'));

        //书本总数
        $books = new InfoModel();
        $books_num = $books->count();
        $this->view->assign('books_num',$books_num);

        //借阅记录总数
        $borrow = new BorrowModel();
        $borrow_num = $borrow->count();
        $this->view->assign('borrow_num',$borrow_num);

        //上次登陆时间
        $this->view->assign('login_time',UserModel::getLoginTimeAttr(Session::get('user_info.login_time')));

        return $this->view->fetch();
    }

    //图书增删改查
    //显示图书
    public function books()
    {
        $bookslist = InfoModel::paginate(10);
        $count = InfoModel::count();
        //TODO:获取记录数量
        $this->view->assign('count',$count);
        $this->view->assign('booksList',$bookslist);

        return $this->view->fetch('books');
    }

    //查找图书
    public function search(Request $request)
    {
        $data = $request->post();
        $keyword = $data['keyword'];
        $where = array();
        $book = '';
        if (!empty($data['type'])) {
            if ($data['type'] == 'name') {
                $where['name'] = array('like','%'.$keyword.'%');
                $book = (new \app\index\model\Info)->where($where)->select();
                $this->view->assign('booksList',$book);
            } else if ($data['type'] == 'num') {
                $book = InfoModel::select(['id' => $keyword]);
                $this->view->assign('booksList',$book);
            }
        }

        return $this->view->fetch();
    }

    //修改图书
    public function bookEdit(Request $request)
    {
        //获取数据
        $status = 0;
        $message = '修改失败';

        $param = $request -> param();

        $book = InfoModel::get(['name' => $param['name']]);
        $book_id = $param['book_id'];

        //成功
        Db::table('info')
            ->where('id',$book_id)
            ->data(
                [
                    'name' => $param['name'],
                    'author' => $param['author'],
                    'press_time' => $param['press_time'],
                    'price' => $param['price'],
                    'ISBN' => $param['ISBN'],
                    'desc' => $param['desc'],
                ])
            ->update();

        Db::table('borrow_list')
            ->where('info_id',$book_id)
            ->data(['name' => $param['name']])
            ->update();

        $status = 1;
        $message = '修改成功';


        return ['status'=>$status, 'message'=>$message];

    }

}