<?php

namespace App\Http\Controllers\Voyager;

use Illuminate\Http\Request;
use TCG\Voyager\Facades\Voyager;

class VoyagerUserController extends VoyagerBreadController
{
    public function edit(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('edit_'.$dataType->name);

        $relationships = $this->getRelationships($dataType);

        $dataTypeContent = (strlen($dataType->model_name) != 0)
            ? app($dataType->model_name)->with($relationships)->findOrFail($id)
            : DB::table($dataType->name)->where('id', $id)->first(); // If Model doest exist, get data from table name

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($dataTypeContent);

        $view = 'voyager::bread.edit-add';

        if (view()->exists("voyager::$slug.edit-add")) {
            $view = "voyager::$slug.edit";
        }

        return view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'));
    }


    public function create(Request $request)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('add_'.$dataType->name);

        $dataTypeContent = (strlen($dataType->model_name) != 0)
            ? new $dataType->model_name()
            : false;

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($dataTypeContent);

        $view = 'voyager::bread.edit-add';

        if (view()->exists("voyager::$slug.add")) {
            $view = "voyager::$slug.add";
        }

        return view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'));
    }


    // POST BRE(A)D
    public function store(Request $request)
    {
        //验证数据
        $this->validator($request->all())->validate();

        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('add_'.$dataType->name);

        //Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->addRows);

        if ($val->fails()) {
            return response()->json(['errors' => $val->messages()]);
        }

        if (!$request->ajax()) {
            $data = $this->insertUpdateData($request, $slug, $dataType->addRows, new $dataType->model_name());

            return redirect()
                ->route("voyager.{$dataType->slug}.edit", ['id' => $data->id])
                ->with([
                    'message'    => "Successfully Added New {$dataType->display_name_singular}",
                    'alert-type' => 'success',
                ]);
        }
    }

    /**
     * 重置密码
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPass(Request $request,$id)
    {
        //验证数据
        $validate=$this->validatorPass($request->all());

        if($validate->fails()){
            return $this->apiJson(false,$validate->errors()->first('password'));
        }

        $dataType = Voyager::model('DataType')->where('slug', '=', 'users')->first();

        // Check permission
        Voyager::canOrFail('edit_'.$dataType->name);

        //密码加密
        $password = bcrypt($request->get('password'));

        //更新数据库
        $update_result = \DB::table('users')
            ->where('id', $id)
            ->update(['password' => $password]);

        if ($update_result === false) {
            return $this->apiJson(false,'重置密码失败!');
        }
        return $this->apiJson(true,'重置密码成功!');
    }


    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return \Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);
    }

    /**
     * 验证密码
     * @param array $data
     * @return mixed
     */
    protected function validatorPass(array $data)
    {
        return \Validator::make($data, [
            'password' => 'required|string|min:6',
        ]);
    }
}
