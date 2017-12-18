{{--Locations Admin รวมของ Project Living University --}}
@extends('svms.layouts.admin')

@section('title')
    รายการรถยนต์
@stop

@section('extraScripts')
    {{--Use Data Tables--}}
    {{ HTML::style('dependencies/DataTables/media/css/jquery.dataTables.min.css') }}
    {{ HTML::script('dependencies/DataTables/media/js/jquery.dataTables.min.js') }}
    {{--Use Bootstrap Select2--}}
    {{ HTML::script('dependencies/select2-4.0.0-beta.3/dist/js/select2.min.js') }}
    {{ HTML::style('dependencies/select2-4.0.0-beta.3/dist/css/select2.min.css') }}

@stop

@section('extraStyles')
    <style type="text/css">
        .group,
        .group:hover {
            background-color: #f9dd34 !important;
        }
        .sub-group,
        .sub-group:hover {
            background-color: #ffffbb !important;
        }
    </style>
@stop

@section('content')
    <div class="page-header">
        <h2>
            รายการรถยนต์/ราคา
            <div class="pull-right">
                <a href="javascript:openCreate();" class="btn btn-small btn-primary"><span class="fa fa-plus"></span> สร้างใหม่</a>
            </div>
        </h2>
    </div>

    <div class="panel panel-default">
        <div class="panel-body">
            <form id="search">
                <div class="form-group">
                    <select class="form-control" id="comboCarFac" name="comboCarFac" style="width: 100%;">
                        <option value="">ทั้งหมด</option>
                        @foreach($facilitators as $facilitator)
                            <option value="{{ $facilitator->id }}">{{ $facilitator->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <table id="editor_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th>ผู้ให้บริการ</th>
            <th>ชื่อรายการ</th>
            <th>สร้างเมื่อ</th>
            <th>Actions</th>
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
                                    <label for="comboFac">ผู้ให้บริการ *</label>
                                    <select name="car_facilitator_id" id="comboFac" class="form-control" style="width: 100%;" required>
                                        @foreach($facilitators as $facilitator)
                                            <option value="{{ $facilitator->id }}">{{ $facilitator->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="inputName">ชื่อรถยนต์ *</label>
                                    <input name="name" type="text" class="form-control" id="inputName" placeholder="" required>
                                </div>
                                <div class="form-group">
                                    <label for="inputUnit">หน่วย *</label>
                                    <input name="unit" type="text" class="form-control" id="inputUnit" value="คัน" required>
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
            /*Select2*/
            $('#comboCarFac').select2({
                placeholder: "เลือกผู้ให้บริการ",
                allowClear: true
            });
            $('#comboCarFac').on('change', function(e){
                $('#editor_table').DataTable().draw();
            });
            /*Load DataTables*/
            var oTable;

            oTable = $('#editor_table').DataTable({
                "sDom": "<'row'<'col-md-6'l><'col-md-6'f>r>t<'row'<'col-md-6'i><'col-md-6'p>>",
                "language": {
                    "url": "{{ URL::asset('assets/js/Thai.json') }}"
                },
                "processing": true,
                "serverSide": true,
                "order": [[2,'desc']],
                "ajax": {
                    "url" : "{{ URL::action('AdminCarsController@getData') }}",
                    "data" : function(d)
                    {
                        d.car_facilitator_id = $("#comboCarFac").val();
                    }
                },
                "fnDrawCallback": function ( oSettings ) {
                }
            });

            /*Submit Form*/
            var data;
            $('form').submit(function(e){

                var create_new = $('input[name=new]').val();

                $.ajax({
                    type: "POST",
                    url: (create_new=="true") ? "{{ URL::action('AdminCarsController@postCreate') }}" : "{{ URL::action('AdminCarsController@postEdit') }}",
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

        function openDelete(id)
        {
            if(confirm('ต้องการยืนยันการลบหรือไม่ ?')==true)
            {
                $.ajax({
                    type: "POST",
                    url: "{{ URL::action('AdminCarsController@postDelete') }}",
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

        function openCreate()
        {
            //set title
            $('#formModalLabel').html('สร้างรายการรถยนต์');
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
            $('#formModalLabel').html('แก้ไขรายการรถยนต์');
            //set new value
            $('input[name=new]').val(false);

            //get Data From Ajax
            $.ajax({
                url: "{{ URL::action('AdminCarsController@getById') }}",
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
                $('#comboFac').val(data.car_facilitator_id).change();
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

        function openDeleteRate(id)
        {
            if(confirm('ต้องการยืนยันการลบราคานี้หรือไม่ ?')==true)
            {
                $.ajax({
                    type: "POST",
                    url: "{{ URL::action('AdminCarsController@postRateDelete') }}",
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