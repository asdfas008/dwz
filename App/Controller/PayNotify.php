<?php

/*
 * The file is part of the payment lib.
 *
 * (c) Leo <dayugog@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Class TestNotify
 * @author  : helei
 * @date    : 2020/2/1 3:56 下午
 * @version : 1.0.0
 * @desc    : 测试异步通知
 *
 */
namespace App\Controller;

class PayNotify implements \Payment\Contracts\IPayNotify
{
    /**
     * @param string $channel 通知的渠道，如：支付宝、微信、招商
     * @param string $notifyType 通知的类型，如：支付、退款
     * @param string $notifyWay 通知的方式，如：异步 async，同步 sync
     * @param array $notifyData 通知的数据
     * @return bool
     */
    public function handle(string $channel,string $notifyType,string $notifyWay,array $notifyData) {
        $orderNo = $notifyData['out_trade_no'];
        $api = new Api();
        $api->dealVipOrder($orderNo,json_encode($notifyData));
    }
}
