{{--Admin รวมของ Project Living University --}}
@extends('svms.layouts.admin')

@section('title')
    วัสดุและอุปกรณ์ประกอบการเรียนรู้
@stop

@section('extraScripts')
    {{--Use Data Tables--}}
    {{ HTML::style('dependencies/DataTables/media/css/jquery.dataTables.min.css') }}
    {{ HTML::script('dependencies/DataTables/media/js/jquery.dataTables.min.js') }}
@stop

@section('extraStyles')
    <style type="text/css">
        tr.group,
        tr.group:hover {
            background-color: #f9dd34 !important;
        }
    </style>
@stop

@section('content')
    <div class="page-header">
        <h2>
            วัสดุและอุปกรณ์ประกอบการเรียนรู้
            <div class="pull-right">
                <a href="javascript:openCreate();" class="btn btn-small btn-primary"><span class="fa fa-plus"></span> สร้างใหม่</a>
            </div>
        </h2>
    </div>

    <table id="editor_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th>ชื่อวัสดุและอุปกรณ์</th>
            <th>ราคาทุน</th>
            <th>ราคาขาย</th>
            <th>สร้างโดย</th>
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
                        <div class="form-group">
                            <label for="inputName">ชื่อวัสดุและอุปกรณ์ *</label>
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
                "order": [[4,'desc']],
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": "{{{ URL::action('AdminMaterialController@getData') }}}",
                /*"fnServerParams": function ( aoData ) {
                 aoData.push( { "name": "location_id", "value": location_id } );
                 },*/
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
                    'cost_price' : $('#inputCostPrice').val(),
                    'sale_price' : $('#inputSalePrice').val()
                };

                $.ajax({
                    type: "POST",
                    url: (create_new=="true") ? "{{ URL::action('AdminMaterialController@postCreate') }}" : "{{ URL::action('AdminMaterialController@postEdit') }}",
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

        });

        function openDelete(id)
        {
            if(confirm('ต้องการยืนยันการลบหรือไม่ ?')==true)
            {
                $.ajax({
                    type: "POST",
                    url: "{{ URL::action('AdminMaterialController@postDelete') }}",
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
            $('#formModalLabel').html('สร้างวัสดุและอุปกรณ์ประกอบการเรียนรู้');
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
            $('#formModalLabel').html('แก้ไขวัสดุและอุปกรณ์ประกอบการเรียนรู้');
            //set new value
            $('input[name=new]').val(false);

            //get Data From Ajax
            $.ajax({
                url: "{{ URL::action('AdminMaterialController@getById') }}",
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
                $('#inputCostPrice').val(data.cost_price);
                $('#inputSalePrice').val(data.sale_price);
            });

            //open modal
            $('#formModal').modal({ keyboard : false });
        }

    </script>
@stop