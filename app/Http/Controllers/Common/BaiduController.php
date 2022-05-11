<?php

namespace App\Http\Controllers\Common;


use App\Models\Weibo;
use App\Services\Common\BaiduService;
use App\Services\Common\CommonService;
use App\Services\Common\WeiboService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class BaiduController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    
    public function callback(Request $request){
        $cond = $request->all();
        return (new BaiduService())->auth($cond);
    }
    
    
    
}
