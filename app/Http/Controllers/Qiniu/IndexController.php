<?php
/**
 * Created by PhpStorm.
 * User: oryxt
 * Date: 2018/4/2
 * Time: 9:34
 */

namespace App\Http\Controllers\Qiniu;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IndexController extends Controller
{
    public function index(){
        $disk = Storage::disk('qiniu');
        $token=$disk->getDriver()->uploadToken();
        return view('qiniu.index', compact('token'));
    }

    public function getToken(){
        $disk = Storage::disk('qiniu');
        $token=$disk->getDriver()->uploadToken();
        return $token;
    }

    public function upload(Request $request){

    }
}