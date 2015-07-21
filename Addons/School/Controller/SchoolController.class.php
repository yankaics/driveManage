<?php

namespace Addons\School\Controller;

use Home\Controller\AddonsController;



class SchoolController extends BaseController
{
    function _initialize()
    {
        $this->model = $this->getModel('school');
        parent::_initialize();

        // 子导航
        $action = strtolower(_ACTION);
        $res ['title'] = '信息';
        $res ['url'] = addons_url('School://school/lists');
        $res ['class'] = 'cur';
        $nav [] = $res;


        $this->assign('sub_nav', $nav);
    }

    // 通用插件的列表模型
    public function lists()
    {
        // data
        $select_data = parent::_get_model_list($this->model);
        $this->assign($select_data);

        // plugin
        $this->assign('del_button', '0');
        $this->assign('search_button', '0');
        $this->assign('check_all', '0');
        if (sizeof($select_data['list_data']) > 0) {
            $this->assign('add_button', '0');
        }

        // display
        $this->display("lists");
    }

    // 首页幻灯片
    function photos()
    {
        $param = array('type' => '0', 'object_id' => $_REQUEST['object_id']);
        $url = addons_url('School://photo/lists', $param);
        redirect($url);
    }


    function show()
    {

        // gete compnay info
        $token =  get_token($_REQUEST['token']);
        get_openid($_REQUEST['openid']);
        $info = $this->getCompanyInfo();
        $this->assign($info);

        // get img
        $select_data = M('school_photo')->query('select t1.*, t2.path from wp_school_photo t1 left join wp_picture t2 on t1.photo = t2.id where t1.type="0" and t1.object_id = '.$info['id'].' and  t1.token="' . $token . '" order by t1.sort');
        $this->assign('imgs', $select_data);

        // display
        $this->display(T('Addons://School@School/index'));
    }

    function company()
    {
        $this->assign($this->getCompanyInfo());
        $data = M('school_info')->where('token = "' . get_token() . '" and type = 2')->find();
        $this->assign($data);
        $this->display(T('Addons://School@School/company'));
    }


    private function  getCompanyInfo()
    {
        $Model = M($this->model['name']);
        $map = array('token' => get_token());
        $info = $Model->where($map)->find();
        return $info;
    }

    function top()
    {
        $vote_id = I('id', 0, 'intval');
        $openid = get_openid();
        $token = get_token();

        // 取得数据
        $list = M('teacher')->query('select @x:=ifnull(@x,0)+1 as rank, t.* from wp_teacher_rank_all t');

        //
        $this->assign('list', $list);
        $this->display(T('Addons://School@School/top'));
    }


    function register()
    {
        $vote_id = I('id', 0, 'intval');
        $openid = get_openid();
        $token = get_token();

        // 活的驾校排名
        $list = M('teacher')->query('select @x:=ifnull(@x,0)+1 as rank, t.* from wp_teacher_rank_all t');

        if (IS_POST) {
            $_POST['token'] = get_token();
            $_POST['status'] = '-1';
            $_POST['intro_source'] = '0';
            $_POST['time_sign'] = date("Y-m-d");
            if (empty($_POST['id_in_teacher'])) {
                $_POST['intro_source'] = '0';
            } else {
                $_POST['intro_source'] = '1';
            }
            parent::common_add($this->getModel('student'));
        } else {
            // 报名须知
            $sign_info = M('school_info')->where('token = "' . get_token() . '" and type = 0')->find();
            $lean_flow = M('school_info')->where('token = "' . get_token() . '" and type = 1')->find();
            $this->assign('sign_info', $sign_info['info']);
            $this->assign('learn_flow', $lean_flow['info']);

            // get data
            $data = M('school')->where('token = "' . get_token() . '"')->find();
            $this->assign('data', $data);
            $this->assign('section', $_REQUEST['section']);
            $this->display(T('Addons://School@School/regGuide'));
        }
    }

    function navigation()
    {
        $token = get_token();

        // 取得数据
        $_POST['token'] = get_token();
        $select_data = M('school_place')->query('select t1.*, t2.path from wp_school_place t1 left join wp_picture t2 on t1.photo = t2.id where t1.token="' . $token . '"');
        foreach ($select_data as &$data) {
            if (empty($data['coordinate'])) {
                continue;
            }

            $coor = explode(',', $data['coordinate']);
            $data['map_url'] = U('map', 'hor=' . $coor[0] . '&ver=' . $coor[1]);
        }


        $this->assign('list', $select_data);
        $this->display(T('Addons://School@School/navigation'));
    }

    function map()
    {
        $token = get_token();
        $this->assign('hor', $_REQUEST['hor']);
        $this->assign('ver', $_REQUEST['ver']);


        // 取得数据
        $this->display(T('Addons://School@School/map'));
    }


    function ask()
    {
        $token = get_token();
        $_POST['token'] = get_token();
        $number = i('load_number');
        $sql = 'select t.*, from_unixtime(t.time) str_time,t1.nickname, t1.headimgurl, t2.path   from wp_student_question t left join wp_follow t1 on t.openid = t1.openid left join wp_picture t2 on t1.headimgurl = t2.id where t.token ="'.$token.'"  order by id desc ';
        $select_data = M('student_question')->query($sql);
        $this->assign('list',$select_data);
        // 取得数据
        $this->display(T('Addons://School@School/ask'));
    }

    function getAskData(){
        $token = get_token();
        $_POST['token'] = get_token();
        $number = i('load_number');
        $sql = 'select t.*, from_unixtime(t.time) str_time,t1.nickname, t1.headimgurl, t2.path   from wp_student_question t left join wp_follow t1 on t.openid = t1.openid left join wp_picture t2 on t1.headimgurl = t2.id where t.token ="'.$token.'"  order by id desc limit '.$number;
        $select_data = M('student_question')->query($sql);

        $this->ajaxReturn($select_data);
    }

    function getTeacher()
    {
        $this->display(T('Addons://School@School/masterList'));
    }


    function getTeacherData()
    {

        $sql = <<<str
SELECT
	t1.*, t2.path,
	FROM_UNIXTIME(t1.time_in,"%Y-%m-%d") show_time_in,
	t4.*, CASE t1. LEVEL
WHEN 1 THEN
	"一级教练"
WHEN 2 THEN
	"二级教练"
WHEN 3 THEN
	"三级教练"
END AS level_name
FROM
	wp_teacher t1
LEFT JOIN wp_picture t2 ON t1.photo = t2.id
LEFT JOIN (
	SELECT
		id_teacher,
		count(id) apprise_num,
		TRUNCATE (sum(LEVEL) / count(id), 0) apprise_level
	FROM
		wp_student_apprise t3
	GROUP BY
		t3.id_teacher
) t4 ON t4.id_teacher = t1.id
where 1=1

str;

        $sql = $sql . 'and token ="'.get_token().'" ';
        if (!empty($_POST['course_type']) && $_POST['course_type'] != '0') {
            $sql = $sql . 'and subject like "%' . $_POST['course_type'] . '%"';
        }
        if (!empty($_POST['search_name'])) {
            $sql = $sql . 'and name like "%' . $_POST['search_name'] . '%"';
        }

        if ($_POST['level_type'] == '0') {
            $sql = $sql . 'order by apprise_level desc';
        } else if ($_POST['level_type'] == '1') {
            $sql = $sql . 'order by apprise_num desc';
        }

        $select_data = M('teacher')->query($sql);
        // add url
        foreach ($select_data as &$vo) {
            $vo[info_url] = U('getTeacherInfo', 'teacher_id=' . $vo[id]);
        }

        // return
        $this->ajaxReturn($select_data, 'JSON');
    }

    /**
     * get the teacher main info
     */
    function getTeacherInfo()
    {
        $teacher_id = $_REQUEST['teacher_id'];
        get_openid();
        $token = $this->setTeacherToken($teacher_id);
        $code_url = U('/home/Index/leaflets', 'token=' . get_token());
        $this->assign('code_url', $code_url);

        // teacher data
        $teacher_data = M('teacher')->where('id=' . $teacher_id)->find();
        switch ($teacher_data['level']) {
            case '1':
                $teacher_data['level_name'] = '一级教练 ';
                break;
            case '2':
                $teacher_data['level_name'] = '二级教练 ';
                break;
            case '3':
                $teacher_data['level_name'] = '三级教练 ';
                break;
            default:
                $teacher_data['level_name'] = '';
        }
        $this->assign('teacher_data', $teacher_data);

        // school data
        $schoolData = $this->getCompanyInfo();
        $this->assign('school_data', $schoolData);

        // phone
        if(!empty($teacher_data['phone'])){
            $this->assign('phone', $teacher_data['phone']);
        }else{
            $this->assign('phone', $schoolData['phone']);
        }

        // photo data
        $imgs = M('school_photo')->query('select t1.*, t2.path from wp_school_photo t1 left join wp_picture t2 on t1.photo = t2.id where  t1.type="1" and t1.object_id = '.$teacher_id.' and t1.token="' . $token. '" order by t1.sort');
        if(sizeof($imgs) < 1){
            $imgs[] = array('path' => ADDON_PUBLIC_PATH."/img/master_bg.png");
        }
        $this->assign('imgs', $imgs);

        // course
        $sql = 'select * from wp_school_course where token = "' .$token. '"';
        $course_data = M('school_course')->query($sql);
        $this->assign('course_data', $course_data);

        // display
        $this->display(T('Addons://School@School/master'));
    }

    function getTeacherMainInfo()
    {
        $teacher_id = $_REQUEST['teacher_id'];
        $token = get_token();
        // teacher
        $teacher_data = M('teacher')->where('id="' . $teacher_id . '" and token="' . $token . '"')->find();
        if ($teacher_data['subject']) {
            $course_names = '';
            $courses = explode(',', $teacher_data['subject']);
            foreach ($courses as $vo) {
                $course_names = $course_names . ' 科目' . $vo;
            }

            $teacher_data['course_names'] = $course_names;
        }
        $teacher_data['area_name'] = get_name_by_status($teacher_data['area'], 'area', $this->getModel('teacher')['id']);
        $this->assign('teacher_data', $teacher_data);

        // course
        $sql = 'select * from wp_school_course where token = "' . $token . '"';
        $course_data = M('school_course')->query($sql);
        $this->assign('course_data', $course_data);

        // display
        $this->display(T('Addons://School@School/master_info'));

    }

    function  getTeacherSchool()
    {
        $this->assign($this->getCompanyInfo());
        $data = M('school_info')->where('token = "' . get_token() . '" and type = 2')->find();
        $this->assign($data);
        $this->display(T('Addons://School@School/master_school'));
    }

    function  getTeacherReg()
    {
        if (empty($_REQUEST['id'])) {
            $course_data = M('school_course')->where('token = "' . get_token() . '"')->find();
            $this->assign($course_data);
        } else {
            $this->assign($_REQUEST);
        }
        $this->display(T('Addons://School@School/master_reg'));
    }

    function getTeacherApprise(){
        $token = get_token();
        $number = $_REQUEST['load_number'];
        $teacher_id =  $_REQUEST['teacher_id'];
        $sql = 'select t.*,FROM_UNIXTIME(t.time) format_time, t1.name show_name ,t2.headimgurl headimgurl from wp_student_apprise t left join  wp_student t1 on t.id_student = t1.id left join wp_follow  t2 on t1.openid = t2.openid where t.token = "' . $token . '" and t.id_teacher = '.$teacher_id.' limit '.$number;
        $apprise_data = M('student_apprise')->query($sql);
        $this->ajaxReturn($apprise_data, 'JSON');
    }

    /**
     * set the teacher token
     * @param $teacherId
     */
    private  function  setTeacherToken($teacherId){
        $info = M('teacher')->where('id='.$teacherId)->find();
        get_token($info['token']);
        $_REQUEST['token'] = $info['token'];
        return  $info['token'];
    }


    function  getTeacherReserve()
    {
        $this->display(T('Addons://School@School/master_reserve'));
    }

    function getMyInfo()
    {
        if (IS_POST) {
            $name = $_REQUEST['name'];
            $card_id = $_REQUEST['card_id'];
            $map = array('name' => $name, 'card_id' => $card_id);
            $myinfo = M('student')->where($map)->find();
            if (!empty($myinfo)) {
                $myinfo['openid'] = get_openid();
                M('student')->save($myinfo);
                $this->success ();
            }
            $myinfo = M('teacher')->where($map)->find();
            if (!empty($myinfo)) {
                $myinfo['openid'] = get_openid();
                M('teacher')->save($myinfo);
                $this->success ();
            }else{
                $this->error('查找不到对应注册人员!');
            }
        } else {

            $sql = 'select t.*, t2.headimgurl headimgurl from wp_student t left join wp_follow  t2 on t.openid = t2.openid where t.openid= "' . get_openid() . '" and t.token = "' . get_token() . '"';
            $myinfo = M('student')->query($sql)[0];
            if (!empty($myinfo)) {
                $this->assign('myinfo', $myinfo);
                $this->display(T('Addons://School@School/my'));
            }
            $sql = 'select t.*, t2.headimgurl headimgurl from wp_teacher t left join wp_follow  t2 on t.openid = t2.openid where t.openid= "' . get_openid() . '" and t.token = "' .  get_token() . '"';
            $myinfo = M('teacher')->query($sql)[0];
            if (!empty($myinfo)) {
                $this->assign('myinfo', $myinfo);
                //$this->assign('photo_path', get_cover_url($myinfo['photo']));
                $this->display(T('Addons://School@School/myMaster'));
            }
            if(empty($myinfo)){
            $this->display(T('Addons://School@School/bind'));
            }
        }
    }


    /**
     * show the teahcer map navigator
     */
    function teacher_navigator(){
        // show
        $this->display ( T ( 'Addons://School@School/master_navigator' ) );
    }


    /**
     * get the all teacher data
     */
    function getNavigatorTeachers(){
        // connect the sql
        $sql = <<<str
select t.*,t1.name school_name, t2.path ,t4.apprise_level from wp_teacher t left join wp_school t1 on t.token = t1.token
LEFT JOIN wp_picture t2 ON t.photo = t2.id
LEFT JOIN (
	SELECT
		id_teacher,
		count(id) apprise_num,
		TRUNCATE (sum(LEVEL) / count(id), 0) apprise_level
	FROM
		wp_student_apprise t3
	GROUP BY
		t3.id_teacher
) t4 ON t4.id_teacher = t.id
where 1=1
and t.position != ""
str;
        $select_data = M('teacher')->query($sql);

        // add url
        foreach ($select_data as &$vo) {
            $vo[info_url] = U('getTeacherInfo', 'teacher_id=' . $vo[id]);
        }

        $this->ajaxReturn($select_data);
    }

    /**
     * show the my student page
     */
    function showMyStudents(){
        $this->display ( T ( 'Addons://School@School/studentList' ) );
    }

    /**
     * show the page
     */
    function showPage(){
        $this->display ( T ( 'Addons://School@School/'.i('page') ) );
    }
}
