<?php


namespace app\index\controller;


use app\index\model\Info;
use app\index\model\User as UserModel;
use app\index\model\Info as InfoModel;
use app\index\model\Borrow_list as BorrowModel;
use think\Request;
use think\Session;

class User extends Base
{
    //登陆
    public function login(){
        $this->alreadyLogin();
        return $this->view->fetch();
    }

    //防止重复登陆
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
                if ($user -> isDelete  == 0)
                {
                    $status = 0;
                    $result = '该用户已删除，请联系管理员';
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
        }

        return ['status'=>$status, 'message'=>$result, 'data'=>$data];
    }

    //注册
    public function register()
    {
        return $this->view->fetch();
    }

    //验证注册的用户名是否已被占用
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

    //添加自己到数据库
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

    //用户主页
    public function index()
    {
        $this->isLogin();
        $id = Session::get('user_id');


        $borrow = new BorrowModel();
        $where=array(
            "user_id"=>$id
        );

        $borrow = $borrow->where($where)->select();


        if (Session::get('user_info.role') == 1)
        {
            $this->redirect('Admin/index');
        }

        $books = new InfoModel();
        $books_num = $books->count();

        if( null === $borrow) {
            $borrow_num = 0;
        } else {
            $borrow_num= count($borrow);
        }

        $this->view->assign('count',$books_num);
        $this->view->assign('user_name',Session::get('user_info.name'));
        $this->view->assign('borrow_book_num',$borrow_num);
        $this->view->assign('login_time',UserModel::getLoginTimeAttr(Session::get('user_info.login_time')));
        $this->view->assign('out_date_book_num',Session::get('user_info.out_date_book_num'));
        return $this->view->fetch();
    }

    //用户详情页
    public function user_details()
    {
        $this->isLogin();
        $this->view->assign('name',Session::get('user_info.name'));
        $this->view->assign('id',Session::get('user_info.id'));
        $this->view->assign('login_time',UserModel::getLoginTimeAttr(Session::get('user_info.login_time')));
        $this->view->assign('status',Session::get('user_info.status'));
        if (Session::get('user_info.role') == 0) {
            $this->view->assign('role','普通用户');
        } else if (Session::get('user_info.role') == 1) {
            $this->view->assign('role','管理员');
        }
        $user = UserModel::get(['id' => Session::get('user_id')]);
        $this->view->assign('user',$user);


        $this->view->assign('login_count',Session::get('user_info.login_count'));

        //借阅详情
        $borrowList = BorrowModel::get(['user_id' => Session::get('user_id')]);

        $id = Session::get('user_id');
        $borrow = new BorrowModel();
        $where=array(
            "user_id"=>$id
        );
        $borrowList = $borrow->where($where)->select();

//        if ($borrowList == true){
//            //$borrowList = $borrowList->paginate(5);
//            //$count = $borrowList ->count();
//            $this->view->assign('count',$count);
//        }


        $this->view->assign('borrowList',$borrowList);

        return $this->view->fetch();
    }

    //退出登陆
    public function logout()
    {
        //退出前先更新登录时间字段,下次登录时就知道上次登录时间了
        UserModel::update(['login_time'=>time()],['id'=> Session::get('user_id')]);
        Session::delete('user_id');
        Session::delete('user_info');

        $this -> success('注销登陆,正在返回',url('user/login'));
    }

    //修改用户信息
    public function userEdit(Request $request)
    {
        //获取数据
        $param = $request -> param();

        //去掉表单中为空的数据，key为字段名,value为字段值
        foreach ($param as $key => $value) {
            if (!empty($value)){
                $data[$key] = $value;
            }
        }

        $condition = ['id' => $data['id']];
        $result = UserModel::update($data,$condition);

        if ($result == true) {
            return ['status'=>1, 'message'=>'更新成功，需重新登陆'];
        } else {
            return ['status'=>0, 'message'=>'更新失败'];
        }

    }

    //挂失账户
    public function lostUser(Request $request)
    {
        $data = ['status'=>'0'];
        $condition = ['id' => Session::get('user_id')];
        $result = UserModel::update($data,$condition);
        if ($result == true) {
            return ['status'=>1, 'message'=>'已挂失，需重新登陆'];
        } else if ($result == false) {
            return ['status'=>0, 'message'=>'挂失失败'];
        }

    }

    //删除账户
    public function delUser(Request $request)
    {
//        $data = ['isDelete'=>'0'];
//        $condition = ['id' => Session::get('user_id')];
//        $result = UserModel::update($data,$condition);

        $user_name = $request->param('name');
        $user = new UserModel();
        $where = array(
            'isDelete' => 0,
            'status' => 0
        );
        $result = $user->where('name',$user_name)->update($where);
        if ($result == true) {
            return ['status' => 1, 'message' => "已删除，点击[确定]退出"];
        } else if ($result == false) {
            return ['status' => 0, 'message' => '删除失败'];
        }
    }
}

