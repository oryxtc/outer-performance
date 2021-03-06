<?php

namespace App\Http\Controllers\Voyager;

use App\Attendance;
use App\Http\Controllers\ExcelController;
use App\Provident;
use App\User;
use Illuminate\Http\Request;
use Symfony\Component\VarDumper\Dumper\DataDumperInterface;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Models\Role;

class VoyagerAttendanceController extends VoyagerBreadController
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
            'username'=>'姓名',
            'type'=>'类型',
            'title'=>'标题',
            'reson'=>'事由',
            'status'=>'状态',
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

        //查询审核人
        $approver_arr=explode(',',Attendance::where('id',$id)->value('approver'));
        $relevant_arr=explode(',',Attendance::where('id',$id)->value('relevant'));

        $username_list=User::select(['job_number','username'])->whereIn('job_number',array_merge($approver_arr,$relevant_arr))->pluck('username','job_number')->toArray();
        foreach ($approver_arr as $key=>$value){
            if(!$value){
                continue;
            }
            $approver_str[]=$username_list[$value];
        }
        foreach ($relevant_arr as $key=>$value){
            if(!$value){
                continue;
            }
            $relevant_str[]=$username_list[$value];
        }
        $approver_str=implode(',',$approver_str);
        $relevant_str=implode(',',$relevant_str);


        return view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable','approver_str','relevant_str'));
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

        //查询审核人
        $approver_arr=explode(',',Attendance::where('id',$id)->value('approver'));
        $relevant_arr=explode(',',Attendance::where('id',$id)->value('relevant'));
        $username_list=User::select(['job_number','username'])->whereIn('job_number',array_merge($approver_arr,$relevant_arr))->pluck('username','job_number')->toArray();
        foreach ($approver_arr as $key=>$value){
            if(!$value){
                continue;
            }
            $approver_str[]=$username_list[$value];
        }
        foreach ($relevant_arr as $key=>$value){
            if(!$value){
                continue;
            }
            $relevant_str[]=$username_list[$value];
        }

        $approver_str=empty($approver_str)?"":implode(',',$approver_str);
        $relevant_str=empty($relevant_str)?"":implode(',',$relevant_str);


        return view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable','relevant_str','approver_str'));
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

            //更新转审
            $status=$data->status;
            $approver=$data->approver;
            $approver_arr=explode(',',$approver);

            //如果有
            if(empty($approver)){
                $retrial='{}';
            }else{
                foreach ($approver_arr as $value){
                    $retrial_arr[$value]=$status;
                }
                $retrial=json_encode($retrial_arr);
            }

            Attendance::where('id',$data->id)->update(['retrial'=>$retrial]);

            return redirect()
                ->route("voyager.{$dataType->slug}.index", ['id' => $id])
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
            //更新转审
            $status=$data->status;
            $approver=$data->approver;
            $approver_arr=explode(',',$approver);

            //如果有
            if(empty($approver)){
                $retrial='{}';
            }else{
                foreach ($approver_arr as $value){
                    $retrial_arr[$value]=$status;
                }
                $retrial=json_encode($retrial_arr);
            }

            Attendance::where('id',$data->id)->update(['retrial'=>$retrial]);
            return redirect()
                ->route("voyager.{$dataType->slug}.index", ['id' => $data->id])
                ->with([
                    'message' => "Successfully Added New {$dataType->display_name_singular}",
                    'alert-type' => 'success',
                ]);
        }
    }


    /**
     * 获取社保和公积金列表
     * @param Request $request
     * @return mixed
     */
    public function getAttendancesList(Request $request)
    {
        $field_data = array_keys(ExcelController::ATTENDANCE_HEAD);
        //增加索引列
        \DB::statement(\DB::raw('set @rownum=0'));
        $field_data = array_merge($field_data, ['id']);
        $field_data = array_merge([\DB::raw('@rownum  := @rownum  + 1 AS rownum')], $field_data);
        $attendances = Attendance::select($field_data)->where('status','<>',0);
        $response_data = \Datatables::eloquent($attendances);
        //添加操作
        $response_data = $response_data->addColumn('action', function (Attendance $attendance) {
            return view('voyager::attendances.operate', ['attendance' => $attendance]);
        });

        //指定搜索栏模糊匹配
        $response_data = $response_data->filter(function ($query) use ($request, $field_data) {
            foreach ($field_data as $key => $value) {
                if ($request->has($value)) {
                    if($value=='start_at'){
                        $query->where('start_at', '>=', "{$request->get('start_at')}");
                    }elseif ($value=='end_at') {
                        $query->where('end_at', '<=', "{$request->get('end_at')}");
                    }elseif ($value=='status'){
                        if (preg_match("/(.*)[\x{9000}](.*)/u",$request->get($value))){
                            $status=11;
                        }elseif (preg_match("/(.*)[\x{901A}](.*)/u",$request->get($value))){
                            $status=21;
                        }else{
                            $status=1;
                        }
                        $query->where('status', $status);
                    }else{
                        $query->where($value, 'like', "%{$request->get($value)}%");
                    }
                }
            }
        });

        //格式化状态
        $response_data = $response_data->editColumn('status', function (Attendance $attendance) {
            if($attendance->status=='1'){
                $status='未审核';
            }elseif ($attendance->status=='11'){
                $status='退审';
            }elseif ($attendance->status=='21'){
                $status='通过';
            }
            return $status;
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
