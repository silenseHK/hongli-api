<?php


namespace App\Http\Controllers\Admin\agent;


use App\Http\Controllers\Controller;
use App\Services\Admin\agent\AgentFinanceService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AgentFinanceController extends Controller
{

    private $AgentFinanceService;

    public function __construct(AgentFinanceService $agentFinanceService){
        $this->AgentFinanceService = $agentFinanceService;
    }

    /**
     * 充值列表
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function rechargeList(){
        try{
            $validator = Validator::make(request()->input(), [
                'status' => [
                    'required',
                    'integer',
                    Rule::in([1,2])
                ]
            ]);
            if($validator->fails())
                return $this->AppReturn(401,$validator->errors()->first());
            $this->AgentFinanceService->rechargeList();
            return $this->AppReturn(
                $this->AgentFinanceService->_code,
                $this->AgentFinanceService->_msg,
                $this->AgentFinanceService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

    /**
     * 提现列表
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function withdrawList(){
        try{
            $validator = Validator::make(request()->input(), [
                'status' => [
                    'required',
                    'integer',
                    Rule::in([0,1,2])
                ]
            ]);
            if($validator->fails())
                return $this->AppReturn(401,$validator->errors()->first());
            $this->AgentFinanceService->withdrawList();
            return $this->AppReturn(
                $this->AgentFinanceService->_code,
                $this->AgentFinanceService->_msg,
                $this->AgentFinanceService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

    /**
     * 佣金列表
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function commissionList(){
        try{
            $validator = Validator::make(request()->input(), [
                'type' => [
                    'required',
                    'integer',
                    Rule::in([1,2])
                ]
            ]);
            if($validator->fails())
                return $this->AppReturn(401,$validator->errors()->first());
            $this->AgentFinanceService->commissionList();
            return $this->AppReturn(
                $this->AgentFinanceService->_code,
                $this->AgentFinanceService->_msg,
                $this->AgentFinanceService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

    /**
     * 裂变红包任务
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function envelopeList(){
        try{
            $this->AgentFinanceService->envelopeList();
            return $this->AppReturn(
                $this->AgentFinanceService->_code,
                $this->AgentFinanceService->_msg,
                $this->AgentFinanceService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

    /**
     * 签到红包
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function signInList(){
        try{
            $this->AgentFinanceService->signInList();
            return $this->AppReturn(
                $this->AgentFinanceService->_code,
                $this->AgentFinanceService->_msg,
                $this->AgentFinanceService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

    /**
     * 彩金
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function bonusList(){
        try{
            $this->AgentFinanceService->bonusList();
            return $this->AppReturn(
                $this->AgentFinanceService->_code,
                $this->AgentFinanceService->_msg,
                $this->AgentFinanceService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

    /**
     * 上下分列表
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function upAndDownList(){
        try{
            $validator = Validator::make(request()->input(), [
                'type' => [
                    'integer',
                    Rule::in([0,1,2])
                ]
            ]);
            if($validator->fails())
                return $this->AppReturn(401,$validator->errors()->first());
            $this->AgentFinanceService->upAndDownList();
            return $this->AppReturn(
                $this->AgentFinanceService->_code,
                $this->AgentFinanceService->_msg,
                $this->AgentFinanceService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

}
