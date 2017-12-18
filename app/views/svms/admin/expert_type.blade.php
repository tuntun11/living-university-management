{{--แก้ไขเป็นกิจกรรมพิเศษ--}}
{{--Admin รวมของ Project Living University --}}
@extends('svms.layouts.admin')

@section('title')
    อุปกรณ์ทำกิจกรรมพิเศษ
@stop

@section('extraScripts')
    {{--Use Data Tables--}}
    {{ HTML::style('dependencies/DataTables/media/css/jquery.dataTables.min.css') }}
    {{ HTML::script('dependencies/DataTables/media/js/jquery.dataTables.min.js') }}
    {{--Use Bootstrap Select2--}}
    {{ HTML::script('dependencies/select2-4.0.0-beta.3/dist/js/select2.min.js') }}
    {{ HTML::style('dependencies/select2-4.0.0-beta.3/dist/css/select2.min.css') }}
@stop

@section('content')
    <div class="page-header">
        <h2>
            ประเภทวิทยากร/ราคา
            <div class="pull-right">
                <a href="javascript:openCreate();" class="btn btn-small btn-primary"><span class="fa fa-plus"></span> สร้างใหม่</a>
            </div>
        </h2>
    </div>

    <table id="editor_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th>ชื่อประเภท</th>
            <th>หน่วย</th>
            <th class="col-md-2">ลำดับความสำคัญ</th>
            <th class="col-md-2">สร้างเมื่อ</th>
            <th class="col-md-2">Actions</th>
        </tr>
        </thead>
    </table>

    <form id="form" role="form" method="post">
        {{--Id--}}
        <input type="hidden" id="old_id" name="old_id">
        {{--Create New True--}}
        <input type="hidden" id="new" name="new" value="true">
        <!-- CSRF Token -->
        <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
        <!-- ./ csrf token -->

        {{--Create Edit Form--}}
        <div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-labelledby="formModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="formModalLabel"></h4>
                    </div>
                    <div class="modal-body">

                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">ข้อมูลหลัก</a></li>
                            <li role="presentation"><a href="#price" aria-controls="price" role="tab" data-toggle="tab">ข้อมูลราคา</a></li>
                        </ul>

                        <!-- Tab panes -->
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="home">
                                <div class="form-group">
                                    <label for="inputName">ชื่อประเภท *</label>
                                    <input name="name" type="text" class="form-control" id="inputName" placeholder="" required>
                                </div>
                                <div class="form-group">
                                    <label for="inputUnit">หน่วย *</label>
                                    <input name="unit" type="text" class="form-control" id="inputUnit" value="ท่าน" required>
                                </div>
                                <div class="form-group">
                                    <label for="selectPriority">ลำดับความสำคัญ</label>
                                    <select class="form-control" name="priority" id="selectPriority">
                                        @for($i=1;$i<=100;$i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="price">
                                <table class="table table-bordered table-hover" id="table_prices">
                                    <thead>
                                    <tr >
                                        <th class="text-center">
                                            ชื่อเรทราคา *
                                        </th>
                                        <th class="text-center">
                                            ราคาทุน *
                                        </th>
                                        <th class="text-center">
                                            ราคาขาย *
                                        </th>
                                        <th class="text-center">
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr id='rate0'>
                                        <td>
                                            <input type="hidden" name="rate_id[]" value="0"/>
                                            <input type="text" name="rate_name[]" class="form-control" required/>
                                        </td>
                                        <td>
                                            <input type="number" step='any' name="rate_cost[]" class="form-control" required/>
                                        </td>
                                        <td>
                                            <input type="number" step='any' name="rate_sale[]" class="form-control" required/>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr id='rate1'></tr>
                                    </tbody>
                                </table>
                                <div class="pull-right">
                                    <a id="add_rate" class="btn btn-sm btn-info"><span class="fa fa-plus"></span> เพิ่มเรทราคา</a>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-times"></span> ปิด</button>
                        <button type="submit" class="btn btn-success"><span class="fa fa-floppy-o"></span> ยืนยัน</button>
                    </div>
                </div>
            </div>
        </div>

    </form>

    <script type="text/javascript">
        $(document).ready(function() {
            /*Load DataTables*/
            var oTable;

            oTable = $('#editor_table').dataTable({
                "sDom": "<'row'<'col-md-6'l><'col-md-6'f>r>t<'row'<'col-md-6'i><'col-md-6'p>>",
                "language": {
                    "url": "{{ URL::asset('assets/js/Thai.json') }}"
                },
                "order": [[2,'asc']],
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": "{{{ URL::action('AdminExpertTypeController@getData') }}}",
                "fnDrawCallback": function ( oSettings ) {

                }
            });

            /*Submit Form*/
            var data;
            $('form').submit(function(e){

                var create_new = $('input[name=new]').val();

                $.ajax({
                    type: "POST",
                    url: (create_new=="true") ? "{{ URL::action('AdminExpertTypeController@postCreate') }}" : "{{ URL::action('AdminExpertTypeController@postEdit') }}",
                    data: $('form').serialize(),
                    success: function (data) {
                        if (data.status=='success')
                        {
                            $('#formModal').modal('hide');
                            oTable.fnDraw();
                        }
                        else
                        {
                            alert('Error');
                        }
                    },
                    dataType: 'json'
                });

                e.preventDefault(); //STOP default action
            });

        });

        /*Add rates*/
        $("#add_rate").click(function(e){

            e.preventDefault();

            var i= $('#table_prices > tbody > tr:has(td)').size();

            $('#rate'+i).html("<td><input type='hidden' name='rate_id[]' value='0'/><input name='rate_name[]' type='text' class='form-control' /> </td> <td><input step='any' name='rate_cost[]' type='number' class='form-control'></td><td><input step='any' name='rate_sale[]' type='number' class='form-control'></td> <td><a row='" + i + "' class='del_contact btn btn-xs btn-danger' href='javascript:openDeleteUnRate("+i+");' role='button'>ลบ</a></td>");

            $('#table_prices > tbody').append('<tr id="rate'+(i+1)+'"></tr>');
            i++;

        });

        function openCreate()
        {
            //set title
            $('#formModalLabel').html('สร้างประเภทวิทยากร');
            //reset form
            $('form')[0].reset();
            //set new value
            $('input[name=new]').val(true);
            //open modal
            $('#formModal').modal({ keyboard : false });
            //insert rates
            var html = '';
            html += '<tr id="rate0">';
            html += '<td><input type="hidden" name="rate_id[]" value="0"/><input type="text" name="rate_name[]" class="form-control" required/></td>';
            html += '<td><input type="number" step="0.01" name="rate_cost[]" class="form-control" required/></td>';
            html += '<td><input type="number" step="0.01" name="rate_sale[]" class="form-control" required/></td>';
            html += '<td></td>';
            html += '</tr>';

            html += '<tr id="rate1"></tr>';

            $('#table_prices > tbody').empty().append(html);
        }

        function openEdit(id)
        {
            //set title
            $('#formModalLabel').html('แก้ไขประเภทวิทยากร');
            //set new value
            $('input[name=new]').val(false);

            //get Data From Ajax
            $.ajax({
                url: "{{ URL::action('AdminExpertTypeController@getById') }}",
                data :
                {
                    '_token' : $('input[name=_token]').val(),
                    'id' : id
                }
            }).done(function(data) {
                var data = data.data;
                //set old value
                $('#old_id').val(data.id);
                $('#inputName').val(data.name);
                $('#inputUnit').val(data.unit);
                $('#selectPriority').val(data.priority).change();
                //loop rates
                var html = '';
                var num = 0;
                $.each(data.rates, function(index, item){
                    html += '<tr rate_id="' + item.id + '" id="rate' + num + '">';
                    html += '<td><input type="hidden" name="rate_id[]" value="' + item.id + '"/><input type="text" name="rate_name[]" class="form-control" value="' + item.name + '" required/></td>';
                    html += '<td><input type="number" step="0.01" name="rate_cost[]" class="form-control" value="' + item.cost_price + '" required/></td>';
                    html += '<td><input type="number" step="0.01" name="rate_sale[]" class="form-control" value="' + item.sale_price + '" required/></td>';
                    if (num==0)
                    {
                        html += '<td></td>';
                    }
                    else
                    {
                        html += '<td><a class="btn btn-xs btn-danger" href="javascript:openDeleteRate(' + item.id + ');" role="button">ลบ</a></td>';
                    }
                    html += '</tr>';

                    num++;
                });

                html += '<tr id="rate' + num + '"></tr>';

                $('#table_prices > tbody').empty().append(html);
            });

            //open modal
            $('#formModal').modal({ keyboard : false });
        }

        function openDelete(id)
        {
            if(confirm('ต้องการยืนยันการลบหรือไม่ ?')==true)
            {
                $.ajax({
                    type: "POST",
                    url: "{{ URL::action('AdminExpertTypeController@postDelete') }}",
                    data :
                    {
                        '_token' : $('input[name=_token]').val(),
                        'id' : id
                    }
                }).done(function(data) {
                    location.reload();
                });
            }
            return false;
        }

        function openDeleteRate(id)
        {
            if(confirm('ต้องการยืนยันการลบราคานี้หรือไม่ ?')==true)
            {
                $.ajax({
                    type: "POST",
                    url: "{{ URL::action('AdminExpertTypeController@postRateDelete') }}",
                    data :
                    {
                        '_token' : $('input[name=_token]').val(),
                        'id' : id
                    }
                }).done(function(data) {
                    var id = data.id;
                    $('#table_prices > tbody > tr[rate_id=' + id + ']').html('');
                });
            }
            else
            {
                return false;
            }
        }

        function openDeleteUnRate(id)
        {
            $('#rate'+id).html('');
        };

    </script>
@stop