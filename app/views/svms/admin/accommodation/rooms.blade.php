{{--Locations Admin รวมของ Project Living University --}}
@extends('svms.layouts.admin')

@section('title')
    รายการห้อง/ที่พัก
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
            รายการห้อง/ที่พัก
            <div class="pull-right">
                <a href="javascript:openCreate();" class="btn btn-small btn-primary"><span class="fa fa-plus"></span> สร้างใหม่</a>
            </div>
        </h2>
    </div>

    <div class="panel panel-default">
        <div class="panel-body">
            <form id="search">
                <div class="form-group">
                    <select class="form-control" id="comboLocationRoom" name="comboLocationRoom" style="width: 100%;">
                        <option value="">ทั้งหมด</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->name. ', จ.' .$location->province_name. ' อ.' .$location->amphur_name. ' ต.' .$location->district_name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <table id="editor_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th>สถานที่พัก/โรงแรม</th>
            <th>ชื่อรายการ</th>
            <th>ราคาทุน</th>
            <th>ราคาขาย</th>
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
        <div class="modal fade" id="formModal" role="dialog" aria-labelledby="formModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="formModalLabel"></h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="comboLocation">สถานที่/โรงแรม</label>
                            <select id="comboLocation" class="form-control" style="width: 100%;" required>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name. ', จ.' .$location->province_name. ' อ.' .$location->amphur_name. ' ต.' .$location->district_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="inputName">ชื่อรายการห้อง/ที่พัก *</label>
                            <input type="text" class="form-control" id="inputName" placeholder="" required>
                        </div>
                        <div class="form-group">
                            <label for="inputCostPrice">ราคาต้นทุน *</label>
                            <div class="input-group">
                                <input type="number" value="0" class="form-control" id="inputCostPrice" aria-describedby="inputCostPrice-addon">
                                <span class="input-group-addon" id="inputCostPrice-addon">บาท</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputSalePrice">ราคาขาย *</label>
                            <div class="input-group">
                                <input type="number" value="0" class="form-control" id="inputSalePrice" aria-describedby="inputSalePrice-addon">
                                <span class="input-group-addon" id="inputSalePrice-addon">บาท</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputUnit">หน่วย *</label>
                            <input type="text" class="form-control" id="inputUnit" value="ห้อง" required>
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
            $('#comboLocationRoom').select2({
                placeholder: "เลือกสถานที่",
                allowClear: true
            });
            $('#comboLocationRoom').on('change', function(e){
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
                "order": [[4,'desc']],
                "ajax": {
                    "url" : "{{ URL::action('AdminAccomRoomsController@getData') }}",
                    "data" : function(d)
                    {
                        d.location_id = $("#comboLocationRoom").val();
                    }
                },
                "fnDrawCallback": function ( oSettings ) {
                }
            });

            /*Submit Form*/
            var data;
            $('form').submit(function(e){

                var create_new = $('input[name=new]').val();

                data = {
                    '_token' : $("input[name=_token]").val(),
                    'id' : $('#old_id').val(),
                    'name' : $('#inputName').val(),
                    'unit' : $('#inputUnit').val(),
                    'location_id' : $('#comboLocation').val(),
                    'cost_price' : $('#inputCostPrice').val(),
                    'sale_price' : $('#inputSalePrice').val()
                };

                $.ajax({
                    type: "POST",
                    url: (create_new=="true") ? "{{ URL::action('AdminAccomRoomsController@postCreate') }}" : "{{ URL::action('AdminAccomRoomsController@postEdit') }}",
                    data: data,
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

            $('#comboLocation').select2();
        });

        function openDelete(id)
        {
            if(confirm('ต้องการยืนยันการลบหรือไม่ ?')==true)
            {
                $.ajax({
                    type: "POST",
                    url: "{{ URL::action('AdminAccomRoomsController@postDelete') }}",
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
            $('#formModalLabel').html('สร้างรายการห้อง/ที่พัก');
            //reset form
            $('form')[0].reset();
            //set new value
            $('input[name=new]').val(true);
            //open modal
            $('#formModal').modal({ keyboard : false });
        }

        function openEdit(id)
        {
            //set title
            $('#formModalLabel').html('แก้ไขรายการห้อง/ที่พัก');
            //set new value
            $('input[name=new]').val(false);

            //get Data From Ajax
            $.ajax({
                url: "{{ URL::action('AdminAccomRoomsController@getById') }}",
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
                $('#inputCostPrice').val(data.cost_price);
                $('#inputSalePrice').val(data.sale_price);

                setTimeout(function(){
                    $('#comboLocation').val(data.location_id).change();
                },500);

            });

            //open modal
            $('#formModal').modal({ keyboard : false });
        }

    </script>
@stop