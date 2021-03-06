<?php


namespace App\Services\Pay;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Rspay extends PayStrategy
{

    protected static $url = 'http://www.rspay.in/api/';    // 网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public $company = 'rspay';   // 支付公司名

    public function _initialize()
    {
        $withdrawConfig = DB::table('settings')->where('setting_key','withdraw')->value('setting_value');
        $rechargeConfig = DB::table('settings')->where('setting_key','recharge')->value('setting_value');
        $withdrawConfig && $withdrawConfig = json_decode($withdrawConfig,true);
        $rechargeConfig && $rechargeConfig = json_decode($rechargeConfig,true);
//        $this->merchantID = config('pay.company.'.$this->company.'.merchant_id');
//        $this->secretkey = config('pay.company.'.$this->company.'.secret_key');
        $this->withdrawMerchantID = isset($withdrawConfig[$this->company])?$withdrawConfig[$this->company]['merchant_id']:"";
        $this->withdrawSecretkey = isset($withdrawConfig[$this->company])?$withdrawConfig[$this->company]['secret_key']:"";

        $this->rechargeMerchantID = isset($rechargeConfig[$this->company])?$rechargeConfig[$this->company]['merchant_id']:"";
        $this->rechargeSecretkey = isset($rechargeConfig[$this->company])?$rechargeConfig[$this->company]['secret_key']:"";

        $this->recharge_callback_url = self::$url_callback . '/api/recharge_callback' . '?type='.$this->company;
        $this->withdrawal_callback_url =  self::$url_callback . '/api/withdrawal_callback' . '?type='.$this->company;
    }

    /**
     * 生成签名  sign = Md5(key1=vaIue1&key2=vaIue2&key=签名密钥);
     */
    public  function generateSign(array $params, $type=1)
    {
        $secretKey = $type == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            $string[] = $key . '=' . $value;
        }
        $sign = (implode('&', $string)) . '&key=' .  $secretKey;
        return strtoupper(md5($sign));
    }

    public function generateSignRigorous(array $params, $type=1){
        $secretKey = $type == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            if($value)
                $string[] = $key . '=' . $value;
        }
        $sign = (implode('&', $string)) . '&key=' .  $secretKey;
        return strtoupper(md5($sign));
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'appId' => $this->rechargeMerchantID,
            'outOrderNo' => $order_no,
//            'outOrderNo' => 202101081957588261145717,
            'applyDate' => time(),
//            'applyDate' => 1610107078,
            'channel' => 912,
            'notifyUrl' => $this->recharge_callback_url,
            'amount' => intval($money),
            'userId' => $this->getUserId(),
            'clientIp' => $this->request->ip(),
//            'clientIp' => '182.239.92.158',
            'contactName' => 'ZhangSan',
            'email' => '11111111@qq.com',
            'contact' => 15988888888,
        ];
        $params['sign'] = $this->generateSign($params,1);

        \Illuminate\Support\Facades\Log::channel('mytest')->info('rspay_rechargeOrder', [$params]);
//        $res = $this->requestService->postJsonData(self::$url . 'pay' , $params);
//        if ($res['status'] != 'SUCCESS') {
//            \Illuminate\Support\Facades\Log::channel('mytest')->info('MTB_rechargeOrder_return', $res);
//            $this->_msg = $res['err_msg'];
//            return false;
//        }
//        $native_url = $res['order_data'];
//        if(strpos($native_url,"POST;") == 0){
//            $native_url = str_replace('POST;','',$native_url);
//            $is_post = 1;
//        }
        $is_post=2;
        $native_url = self::$url . 'pay';
        $resData = [
            'out_trade_no' => $order_no,
            'pay_type' => $pay_type,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => '',
            'verify_money' => '',
            'match_code' => '',
            'is_post' => isset($is_post)?$is_post:0,
            'params' => $params
        ];
        return $resData;
    }

    /**
     * 充值回调
     */
    function rechargeCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('rspay_rechargeCallback',$request->post());

        if ($request->payStatus != 1)  {
            $this->_msg = 'rspay-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSignRigorous($params,1) <> $sign) {
            $this->_msg = 'rspay-签名错误';
            return false;
        }

        $where = [
            'order_no' => $request->outOrderNo,
        ];
        return $where;
    }

    /**
     *  后台审核请求提现订单 (提款)  代付方式
     */
    public function withdrawalOrder(object $withdrawalRecord)
    {
        $money = $withdrawalRecord->payment;    // 打款金额
//        $ip = $this->request->ip();
//        $order_no = self::onlyosn();
        $order_no = $withdrawalRecord->order_no;
        $params = [
            'appId' => $this->withdrawMerchantID,
            'outOrderNo' => $order_no,
            'applyDate' => date('Y-m-d H:i:s'),
            'channel' => '912',
            'notifyUrl' => $this->withdrawal_callback_url,
            'amount' => intval($money),
            'mode' => 'IMPS',
            'account' => $withdrawalRecord->bank_number,
            'accountIFSC' => $withdrawalRecord->ifsc_code,
            'userId' => $withdrawalRecord->user_id,
            'clientIp' => $this->request->ip(),
        ];
        $params['sign'] = $this->generateSign($params,2);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('rspay_withdrawalOrder',$params);
        $res = $this->requestService->postJsonData(self::$url . 'payout', $params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('rspay_withdrawalOrder2',$res);
        if ($res['statusCode'] != '00') {
            $this->_msg = $res['message'];
            return false;
        }
        return  [
            'pltf_order_no' => $res['transactionId'],
            'order_no' => $order_no
        ];
    }

    /**
     * 提现回调
     */
    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('rspay_withdrawalCallback',$request->post());

        $pay_status = 0;
        $status = (string)($request->payStatus);
        if($status == '11'){
            $pay_status = 1;
        }
        if($status == '12'){
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'rspay-withdrawal-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        if ($this->generateSignRigorous($params,2) <> $sign) {
            $this->_msg = 'MTBpay-签名错误';
            return false;
        }
        $where = [
            'order_no' => $request->outOrderNo,
            'plat_order_id' => $request->transactionId,
            'pay_status' => $pay_status
        ];
        return $where;
    }

    protected function makeRequestNo($withdraw_id){
        return date('YmdDis') . $withdraw_id;
    }

    /**
     * 请求待付状态
     * @param $withdrawalRecord
     * @return array|false|mixed|string
     */
    public function callWithdrawBack($withdrawalRecord){
        $request_no = $this->makeRequestNo($withdrawalRecord->id);
        $request_time = date("YmdHis");
        $mer_no = $this->merchantID;
        $mer_order_no = $withdrawalRecord->order_no;

        $params = compact('request_no','request_time','mer_no','mer_order_no');
        $params['sign'] = $this->generateSign($params,2);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('MTBpay_withdrawSingleQuery_Param',$params);
        $res = $this->requestService->postJsonData(self::$url_cashout . 'withdraw/singleQuery', $params);
        if(!$res){
            return false;
        }
        if($res['query_status'] != 'SUCCESS'){
            \Illuminate\Support\Facades\Log::channel('mytest')->info('MTBpay_withdrawSingleQuery_Err',$res);
            return false;
        }
        return $res;
    }

}
