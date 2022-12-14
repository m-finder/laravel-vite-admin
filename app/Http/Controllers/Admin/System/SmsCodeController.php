<?php

namespace App\Http\Controllers\Admin\System;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\ChuangLan\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmsCodeController extends Controller
{
    /**
     * @throws NotFoundException
     * @throws \App\Exceptions\TooManyRequestException
     * @throws \Exception
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->all();

        $this->validate($request, [
            'account' => 'required'
        ], [
            'account.required' => '账号必填'
        ]);

        if(isset($data['is_login'])){
            $admin = Admin::query()->where('phone','=',$data['account'])
                ->orWhere('name','=',$data['account'])
                ->first();

            if(empty($admin)){
                throw new NotFoundException('账户不存在');
            }
        }

        SmsService::sendSmsCode($data['account']);
        return $this->success();
    }
}
