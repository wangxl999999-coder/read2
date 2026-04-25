<?php

namespace app;

use think\App;
use think\exception\ValidateException;
use think\Validate;

abstract class BaseController
{
    protected $app;
    protected $request;
    protected $middleware = [];

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $this->app->request;

        $this->initialize();
    }

    protected function initialize()
    {
        
    }

    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        if (!$v->batch($batch)->check($data)) {
            throw new ValidateException($v->getError());
        }

        return true;
    }

    protected function isLogin()
    {
        return session('?user_id');
    }

    protected function getUserId()
    {
        return session('user_id');
    }

    protected function getUser()
    {
        return [
            'id' => session('user_id'),
            'username' => session('username'),
            'nickname' => session('nickname'),
            'avatar' => session('avatar'),
        ];
    }

    protected function isAdminLogin()
    {
        return session('?admin_id');
    }

    protected function getAdminId()
    {
        return session('admin_id');
    }

    protected function success($msg = '操作成功', $data = null, $url = null, $code = 200)
    {
        $result = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
            'url' => $url,
        ];

        if ($this->request->isAjax()) {
            return json($result);
        }
        
        if ($url) {
            return redirect($url)->with('success', $msg);
        }
        
        return $result;
    }

    protected function error($msg = '操作失败', $data = null, $url = null, $code = 400)
    {
        $result = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
            'url' => $url,
        ];

        if ($this->request->isAjax()) {
            return json($result);
        }
        
        if ($url) {
            return redirect($url)->with('error', $msg);
        }
        
        return $result;
    }

    protected function needLogin()
    {
        if (!$this->isLogin()) {
            if ($this->request->isAjax()) {
                return json(['code' => 401, 'msg' => '请先登录', 'url' => url('/user/login')]);
            }
            return redirect(url('/user/login'));
        }
        return null;
    }

    protected function needAdminLogin()
    {
        if (!$this->isAdminLogin()) {
            if ($this->request->isAjax()) {
                return json(['code' => 401, 'msg' => '请先登录管理员账号', 'url' => url('/admin/login')]);
            }
            return redirect(url('/admin/login'));
        }
        return null;
    }
}
