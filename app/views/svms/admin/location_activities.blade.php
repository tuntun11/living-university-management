{{--Locations Activities Admin รวมของ Project Living University --}}
@extends('svms.layouts.admin')

@section('title')
    รายชื่อกิจกรรมการรับคณะ
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
            รายชื่อกิจกรรมการรับคณะ
            <div class="pull-right">
                <a href="javascript:openCreate();" class="btn btn-small btn-primary"><span class="fa fa-plus"></span> สร้างใหม่</a>
            </div>
        </h2>
    </div>

    <table id="editor_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th>สถานที่</th>
            <th>ชื่อกิจกรรมไทย</th>
            <th>คำอธิบายไทย</th>
            <th>ชื่อกิจกรรม English</th>
            <th>คำอธิบาย English</th>
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
                            <label for="comboLocation">สถานที่ *</label>
                            <select id="comboLocation" class="form-control" style="width: 100%;" required>
								<option value=""></option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name. ', จ.' .$location->province_name. ' อ.' .$location->amphur_name. ' ต.' .$location->district_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="inputNameThai">ชื่อกิจกรรมไทย *</label>
                            <input type="text" class="form-control" id="inputNameThai" placeholder="" required>
                        </div>
						<div class="form-group">
                            <label for="textareaNoteThai">คำอธิบายไทย </label>
                            <textarea class="form-control" id="textareaNoteThai"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="inputNameEnglish">ชื่อกิจกรรม English</label>
                            <input type="text" class="form-control" id="inputNameEnglish" placeholder="">
                        </div>
						<div class="form-group">
                            <label for="textareaNoteEnglish">คำอธิบาย English</label>
                            <textarea class="form-control" id="textareaNoteEnglish"></textarea>
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
            var table;

            table = $('#editor_table').DataTable({
                "language": {
                    "url": "{{ URL::asset('assets/js/Thai.json') }}"
                },
                "order": [[0,'desc']],
                "processing": true,
                "serverSide": true,
                "ajax": "{{{ URL::action('AdminLocationActivitiesController@getData') }}}",
                "columnDefs":
                [
                    {
                        "targets": 5,
                        "data": function ( row, type, val, meta ) {

                            var html = '';
                            html += '<a onclick="openEdit(' + row.id + ');" href="javascript:;" class="btn btn-default btn-xs" ><span class="fa fa-pencil-square-o"></span> แก้ไข</a> ';
							html += '<a onclick="return openDelete(' + row.id + ');" href="javascript:;" class="btn btn-xs btn-danger"><span class="fa fa-trash-o"></span> ลบ</a>';
							
                            return html;

                        }
                    }
                ],
                "columns":
                [
                    { "data" : "location", "class" : "col-md-2", "title" : "สถานที่", "orderable": true, "searchable": true },
                    { "data" : "title_th", "class" : "col-md-2", "title" : "ชื่อกิจกรรมไทย", "orderable": true, "searchable": true },
					{ "data" : "note_th", "class" : "col-md-2", "title" : "คำอธิบายไทย", "orderable": true, "searchable": true },
                    { "data" : "title_en", "class" : "col-md-2", "title" : "ชื่อกิจกรรม English", "orderable": true, "searchable": true },
					{ "data" : "note_en", "class" : "col-md-2", "title" : "คำอธิบาย English", "orderable": true, "searchable": true },
                    { "class" : "col-md-2", "title" : "Actions", "orderable": false, "searchable": false }
                ],
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
                    'title_th' : $('#inputNameThai').val(),
					'title_en' : $('#inputNameEnglish').val(),
					'note_th' : $('#textareaNoteThai').val(),
					'note_en' : $('#textareaNoteEnglish').val(),
                    'location_id' : $('#comboLocation').val()
                };

                $.ajax({
                    type: "POST",
                    url: (create_new=="true") ? "{{ URL::action('AdminLocationActivitiesController@postCreate') }}" : "{{ URL::action('AdminLocationActivitiesController@postEdit') }}",
                    data: data,
                    success: function (data) {
                        if (data.status=='success')
                        {
                            $('#formModal').modal('hide');
                            table.ajax.reload();
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
                    url: "{{ URL::action('AdminLocationActivitiesController@postDelete') }}",
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
            $('#formModalLabel').html('สร้างรายการ');
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
            $('#formModalLabel').html('แก้ไขรายการ');
            //set new value
            $('input[name=new]').val(false);

            //get Data From Ajax
            $.ajax({
                url: "{{ URL::action('AdminLocationActivitiesController@getById') }}",
                data :
                {
                    '_token' : $('input[name=_token]').val(),
                    'id' : id
                }
            }).done(function(data) {
                var data = data.data;
                //set old value
                $('#old_id').val(data.id);
                $('#inputNameThai').val(data.title_th);
                $('#inputNameEnglish').val(data.title_en);
                $('#textareaNoteThai').val(data.note_th);
                $('#textareaNoteEnglish').val(data.note_en);

                setTimeout(function(){
                    $('#comboLocation').val(data.location_id).change();
                },500);

            });

            //open modal
            $('#formModal').modal({ keyboard : false });
        }

    </script>
@stop