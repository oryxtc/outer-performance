<input type="datetime" class="form-control datepicker" name="{{ $row->field }}"
       value="@if(isset($dataTypeContent->{$row->field})){{ date('Y-m-d H:i:s', strtotime(old($row->field, $dataTypeContent->{$row->field})))  }}@else{{old($row->field)}}@endif">
