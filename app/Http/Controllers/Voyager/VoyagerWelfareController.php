<?php

namespace App\Http\Controllers\Voyager;

use App\Http\Controllers\ExcelController;
use App\User;
use Illuminate\Http\Request;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Models\Role;

class VoyagerWelfareController extends VoyagerBreadController
{

    public function index(Request $request)
    {
        // GET THE SLUG, ex. 'posts', 'pages', etc.
        $slug = $this->getSlug($request);

        // GET THE DataType based on the slug
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('browse_'.$dataType->name);

        $getter = $dataType->server_side ? 'paginate' : 'get';

        // Next Get or Paginate the actual content from the MODEL that corresponds to the slug DataType
        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);

            $relationships = $this->getRelationships($dataType);

            if ($model->timestamps) {
                $dataTypeContent = call_user_func([$model->with($relationships)->latest(), $getter]);
            } else {
                $dataTypeContent = call_user_func([
                    $model->with($relationships)->orderBy($model->getKeyName(), 'DESC'),
                    $getter,
                ]);
            }

            //Replace relationships' keys for labels and create READ links if a slug is provided.
            $dataTypeContent = $this->resolveRelations($dataTypeContent, $dataType);
        } else {
            // If Model doesn't exist, get data from table name
            $dataTypeContent = call_user_func([DB::table($dataType->name), $getter]);
            $model = false;
        }

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($model);

        $view = 'voyager::bread.browse';

        if (view()->exists("voyager::$slug.browse")) {
            $view = "voyager::$slug.browse";
        }
        //多选框字段
        $checkData=ExcelController::CHECK_DATA;
        return view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable','checkData'));
    }


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
            $view = "voyager::$slug.edit-add";
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

        if (view()->exists("voyager::$slug.edit-add")) {
            $view = "voyager::$slug.edit-add";
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
     * 验证管理级别
     * @param array $data
     * @return mixed
     */
    protected function validator(array $data)
    {
        return \Validator::make($data, [
            'management_rank' => 'required|string',
        ]);
    }
}
