<?php
/**
 * Created by PhpStorm.
 * User: wuhanchu
 * Date: 15/12/20
 * Time: 10:42
 */

namespace Addons\GroupBuy\Controller;


class GroupBuyPartyController extends BaseController
{
    /**
     * init
     */
    function _initialize()
    {
        parent::_initialize();

        // 配置模型
        $this->model = $this->getModel('groupbuy_party');
    }

    /**
     * 显示列表
     */
    public function lists()
    {
        $this->assign('add_button', 0);
        $this->assign('del_button', 0);
        parent::lists(); // TODO: Change the autogenerated stub
    }

    /**
     * @param null $model
     * @param int $page
     * @param string $order
     * @return mixed
     */
    public function _get_model_list($model = null, $page = 0, $order = 'id desc')
    {
        $model || $model= $this->model;
        unset($_GET["openid"]);
        unset($_REQUEST["openid"]);
        $list_data = parent::_get_model_list($model, $page, $order); // TODO: Change the autogenerated stub
        $list_data ['list_data'] = $this->convertListField($list_data ['list_data'], 'openid', 'phone', 'student', 'openid');
        $list_data ['list_data'] = $this->convertListField($list_data ['list_data'], 'openid', 'name', 'student', 'openid');

        return $list_data;
    }


    /**
     * TODO 退款
     */
    function refund()
    {

    }

    /**
     *  团购的相关参与人
     */
    function getPartyList()
    {
        // 取得团购ID
        $groupBuyId = $_REQUEST['groupBuyId'];
        $token = get_token();

        // 查询团购信息的参与人
        $partyList = M($this->model['name'])->where('token = "' . $token . '" and group_buy_id = ' . $groupBuyId)->select();
        $this->ajaxReturn($partyList);
    }
}