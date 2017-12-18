{{--Dashboard Admin ของหน้า Tag --}}
@extends('svms.layouts.admin')

@section('title')
    Tag
@stop

@section('extraScripts')
    {{--Use Data Tables--}}
    {{ HTML::style('dependencies/DataTables/media/css/jquery.dataTables.min.css') }}
    {{ HTML::script('dependencies/DataTables/media/js/jquery.dataTables.min.js') }}
@stop

@section('content')
    <div class="page-header">
        <h2>
            Tag
            <div class="pull-right">
                <a href="javascript:openCreate();" class="btn btn-small btn-primary"><span class="fa fa-plus"></span> สร้างใหม่</a>
            </div>
        </h2>
    </div>

    <table id="editor_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th>Tag Name</th>
            <th class="col-md-2">Actions</th>
        </tr>
        </thead>
    </table>

    <form id="form" role="form" method="post">
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
                            <label for="inputName">Tag Name</label>
                            <input type="hidden" id="inputOld" name="inputOld">
                            <input type="text" class="form-control" id="inputName" placeholder="" required>
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
                "order": [[0,'desc']],
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": "{{{ URL::action('AdminTagController@getData') }}}",
                "fnDrawCallback": function ( oSettings ) {

                }
            });

            /*Submit Form*/
            var data;
            $('form').submit(function(e){

                var create_new = $('input[name=new]').val();

                data = {
                    '_token' : $("input[name=_token]").val(),
                    'old' : $('#inputOld').val(),
                    'tag' : $('#inputName').val()
                };

                $.ajax({
                    type: "POST",
                    url: (create_new=="true") ? "{{ URL::action('AdminTagController@postCreate') }}" : "{{ URL::action('AdminTagController@postEdit') }}",
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

        function openDelete(tag)
        {
            if(confirm('ต้องการยืนยันการลบหรือไม่ ?')==true)
            {
                $.ajax({
                    type: "POST",
                    url: "{{ URL::action('AdminTagController@postDelete') }}",
                    data :
                    {
                        '_token' : $('input[name=_token]').val(),
                        'tag' : tag
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
            $('#formModalLabel').html('สร้าง TAG');
            //reset form
            $('form')[0].reset();
            //set new value
            $('input[name=new]').val(true);
            $('#inputName').focus(10,true);
            //open modal
            $('#formModal').modal({ keyboard : false });
        }

        function openEdit(tag)
        {
            //set title
            $('#formModalLabel').html('แก้ไข TAG');
            //set new value
            $('input[name=new]').val(false);

            $('#inputOld').val(tag);
            $('#inputName').val(tag);
            //open modal
            $('#formModal').modal({ keyboard : false });
        }

    </script>
@stop