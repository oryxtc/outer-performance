<?php

namespace App\Http\Controllers\Voyager;

use App\Http\Controllers\ExcelController;
use App\User;
use App\Wage;
use Illuminate\Http\Request;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Models\Role;

class VoyagerWageController extends VoyagerBreadController
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
        $checkData = ExcelController::WAGE_HEAD;
        return view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable', 'checkData'));
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
            $view = "voyager::$slug.edit";
        }

        return view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'));
    }


    public function create(Request $request)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('add_' . $dataType->name);

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
     * 获取工资列表
     * @param Request $request
     * @return mixed
     */
    public function getWagesList(Request $request)
    {
        $head_list = ExcelController::WAGE_HEAD;
        if (empty($request->get('checkData', []))) {
            $check_data = ExcelController::WAGE_HEAD;
        } else {
            $request_check_data = $request->get('checkData', []);
            foreach ($request_check_data as $key => $value) {
                if ($value === 'true') {
                    $check_data[] = $key;
                }
            }
        }
        $wage = Wage::select(array_merge($check_data, ['id']))->orderBy('id', 'DESC');
        $response_data = \Datatables::eloquent($wage);
        //添加姓名
        $response_data = $response_data->addColumn('username', function (Wage $wage) {
            $user = $wage->getUser;
            return empty($user) ? "" : $user->username;
        });

        //过滤字段
        $response_data = $response_data->editColumn('status', function (Wage $wage) {
            if ($wage->status == '1') {
                $status = '已确认';
            } else {
                $status = '待确认';
            }
            return $status;
        });
        //添加编辑
        $response_data = $response_data->addColumn('action', function (Wage $wage) {
            return view('voyager::wages.operate', ['wage' => $wage]);
        });
        //指定搜索栏模糊匹配
        $response_data = $response_data->filter(function ($query) use ($request, $head_list) {
            foreach ($head_list as $key => $value) {
                if ($request->has($key)) {
                    $query->where($key, 'like', "%{$request->get($key)}%");
                }
            }
        });
        //删除id
        $response_data = $response_data->removeColumn('id');
        //生成实例
        $response_data = $response_data->make();
        return $response_data;
    }


    public function calculateWages()
    {
        //获取员工
        $users_list = User::where('status', '<>', '离职')
            ->whereDate('leave_at', '>',$this->_getLeaveLimitDate())
            ->get()
            ->toArray();
        dd($users_list);
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

    /**
     * 获取离职限定日期
     */
    private function _getLeaveLimitDate()
    {
        $today_date = date('Y-m', time());
        $limit_date = date('Y-m-d', strtotime(date('Y-m-01', strtotime($today_date)) . ' -2 month -1 day'));
        return $limit_date;
    }

}