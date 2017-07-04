<?php

namespace App\Http\Controllers\Voyager;

use App\Attendance;
use App\Http\Controllers\ExcelController;
use App\Memo;
use App\Provident;
use App\User;
use Illuminate\Http\Request;
use Symfony\Component\VarDumper\Dumper\DataDumperInterface;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Models\Role;

class VoyagerMemoController extends VoyagerBreadController
{

    public function index(Request $request)
    {
        // GET THE SLUG, ex. 'posts', 'pages', etc.
        $slug = $this->getSlug($request);

        // GET THE DataType based on the slug
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('browse_' . $dataType->name);

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
        $attendanceData = [
            'job_number'=>'工号',
            'remark'=>'备注',
        ];
        return view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable', 'attendanceData'));
    }

    public function show(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('read_'.$dataType->name);

        $relationships = $this->getRelationships($dataType);
        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);
            $dataTypeContent = call_user_func([$model->with($relationships), 'findOrFail'], $id);
        } else {
            // If Model doest exist, get data from table name
            $dataTypeContent = DB::table($dataType->name)->where('id', $id)->first();
        }

        //Replace relationships' keys for labels and create READ links if a slug is provided.
        $dataTypeContent = $this->resolveRelations($dataTypeContent, $dataType, true);

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($dataTypeContent);

        $view = 'voyager::bread.read';

        if (view()->exists("voyager::$slug.read")) {
            $view = "voyager::$slug.read";
        }

        return view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'));
    }


    public function edit(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('edit_' . $dataType->name);

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


    // POST BR(E)AD
    public function update(Request $request, $id)
    {
        //验证数据
        $this->validator($request->all())->validate();

        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('edit_' . $dataType->name);

        //Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->editRows);

        if ($val->fails()) {
            return response()->json(['errors' => $val->messages()]);
        }
        if (!$request->ajax()) {
            $data = call_user_func([$dataType->model_name, 'findOrFail'], $id);

            $this->insertUpdateData($request, $slug, $dataType->editRows, $data);

            return redirect()
                ->route("voyager.{$dataType->slug}.edit", ['id' => $id])
                ->with([
                    'message' => "Successfully Updated {$dataType->display_name_singular}",
                    'alert-type' => 'success',
                ]);
        }
    }

    // POST BRE(A)D
    public function store(Request $request)
    {
        //验证数据
        $this->validator($request->all())->validate();

        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('add_' . $dataType->name);

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
                    'message' => "Successfully Added New {$dataType->display_name_singular}",
                    'alert-type' => 'success',
                ]);
        }
    }


    /**
     * 获取备忘录列表
     * @param Request $request
     * @return mixed
     */
    public function getMemosList(Request $request)
    {
        $field_data = array_keys(ExcelController::MEMO_HEAD);
        //增加索引列
        \DB::statement(\DB::raw('set @rownum=0'));
        $field_data = array_merge($field_data, ['id']);
        $field_data = array_merge([\DB::raw('@rownum  := @rownum  + 1 AS rownum')], $field_data);
        $memos = Memo::select($field_data);
        $response_data = \Datatables::eloquent($memos);
        //添加姓名
        $response_data = $response_data->addColumn('username', function (Memo $memo) {
            $user = $memo->getUser;
            return empty($user) ? "" : $user->username;
        });
        //添加操作
        $response_data = $response_data->addColumn('action', function (Memo $memo) {
            return view('voyager::memos.operate', ['memo' => $memo]);
        });

        //指定搜索栏模糊匹配
        $response_data = $response_data->filter(function ($query) use ($request, $field_data) {
            foreach ($field_data as $key => $value) {
                if ($request->has($value)) {
                    $query->where($value, 'like', "%{$request->get($value)}%");
                }
            }
            //如果有开始日期
            if ($request->has('period_at_start')) {
                $firstday = date('Y-m-01', strtotime($request->get('period_at_start')));
                $query->where('period_at', '>=', "{$firstday}");
            }
            //如果有结束日期
            if ($request->has('period_at_end')) {
                $firstday = date('Y-m-01', strtotime($request->get('period_at_end')));
                $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
                $query->where('period_at', '<=', "{$lastday}");
            }
        });

        //格式化日期
        $response_data = $response_data->editColumn('period_at', function ( Memo $memo) {
            return empty($memo->period_at) ? "" : date("Y-m", strtotime($memo->period_at));
        });

        //删除id
        $response_data = $response_data->removeColumn('id');
        //生成实例
        $response_data = $response_data->make(true);
//        dd(\DB::getQueryLog());
        return $response_data;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return \Validator::make($data, [
            'job_number' => 'required|string',
        ]);
    }
}
