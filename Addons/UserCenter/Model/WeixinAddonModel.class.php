<?php

namespace Addons\UserCenter\Model;

use Home\Model\WeixinModel;

/**
 * UserCenter的微信模型
 */
class WeixinAddonModel extends WeixinModel {
	var $config = array ();
	function reply($dataArr, $keywordArr = array()) {
	}
	// 关注时的操作
	function subscribe($dataArr) {
		addWeixinLog('user subscribe',$dataArr);
		$info = D ( 'Common/Follow' )->update_follow ( $dataArr ['FromUserName'] );
		
		// 增加积分
		session ( 'mid', $info ['id'] );
		add_credit ( 'subscribe' );
	}
	// 取消关注公众号事件
	public function unsubscribe() {
        D ( 'Common/Follow' )->where('openid="'.get_openid().'"')->delete();
		// 增加积分
		add_credit ( 'unsubscribe' );
	}
}
        	