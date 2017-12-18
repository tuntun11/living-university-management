{{--Locations Admin รวมของ Project Living University --}}
@extends('svms.layouts.admin')

@section('title')
    สถานที่รับประทานอาหาร/ร้านอาหาร
@stop

@section('extraScripts')
    {{--Use Data Tables--}}
    {{ HTML::style('dependencies/DataTables/media/css/jquery.dataTables.min.css') }}
    {{ HTML::script('dependencies/DataTables/media/js/jquery.dataTables.min.js') }}
    {{--Use Bootstrap Select2--}}
    {{ HTML::script('dependencies/select2-4.0.0-beta.3/dist/js/select2.min.js') }}
    {{ HTML::style('dependencies/select2-4.0.0-beta.3/dist/css/select2.min.css') }}
    {{--Use Data Tables Row Grouping--}}
    {{ HTML::script('assets/js/jquery.dataTables.rowGrouping.js') }}
@stop

@section('extraStyles')
    <style type="text/css">
        .group,
        .group:hover {
            background-color: #f9dd34 !important;
        }
    </style>
@stop

@section('content')
    <div class="page-header">
        <h2>
            สถานที่รับประทานอาหาร/ร้านอาหาร
            <div class="pull-right">
                <a href="javascript:openCreate();" class="btn btn-small btn-primary"><span class="fa fa-plus"></span> สร้างใหม่</a>
            </div>
        </h2>
    </div>

    <table id="editor_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th>สถานที่ตั้ง</th>
            <th>ชื่อร้านอาหาร</th>
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
        <div class="modal fade" id="formModal" role="dialog" aria-labelledby="formModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="formModalLabel"></h4>
                    </div>
                    <div class="modal-body">
                        <div role="tabpanel">

                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" class="active"><a href="#general" aria-controls="home" role="tab" data-toggle="tab">ข้อมูลทั่วไป</a></li>
                                <li role="presentation"><a href="#contact" aria-controls="profile" role="tab" data-toggle="tab">ข้อมูลติดต่อ</a></li>
                            </ul>

                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div style="margin: 5px;" role="tabpanel" class="tab-pane fade in active" id="general">

                                    <div class="form-group">
                                        <label for="comboLocation">สถานที่</label>
                                        <select id="comboLocation" class="form-control" style="width: 100%;" required>
                                            @foreach($locations as $location)
                                                <option value="{{ $location->id }}">{{ $location->name. ', จ.' .$location->province_name. ' อ.' .$location->amphur_name. ' ต.' .$location->district_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="inputName">ชื่อสถานที่รับประทาน/ร้านอาหาร *</label>
                                        <input type="text" class="form-control" id="inputName" placeholder="" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="textareaAddress">ที่อยู่(เพิ่มเติม)</label>
                                        <textarea class="form-control" id="textareaAddress"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="textareaNote">บันทึก(เพิ่มเติม)</label>
                                        <textarea class="form-control" id="textareaNote"></textarea>
                                    </div>
                                </div>
                                <div style="margin: 5px;" role="tabpanel" class="tab-pane fade" id="contact">
                                    <table class="table table-bordered table-hover" id="tab_logic">
                                        <thead>
                                        <tr>
                                            <th class="text-center">
                                                ชื่อผู้ติดต่อ
                                            </th>
                                            <th class="text-center">
                                                E-mail
                                            </th>
                                            <th class="text-center">
                                                โทรศัพท์/มือถือ
                                            </th>
                                            <th class="text-center">
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr id='addr0'>
                                            <td>
                                                <input type="text" name='name0' class="form-control"/>
                                            </td>
                                            <td>
                                                <input type="email" name='email0' class="form-control"/>
                                            </td>
                                            <td>
                                                <input type="text" name='tel0' class="form-control"/>
                                            </td>
                                            <td></td>
                                        </tr>
                                        <tr id='addr1'></tr>
                                        </tbody>
                                    </table>
                                    <div class="pull-right">
                                        <a href="javascript:;" id="add_contact" class="btn-sm btn-default"><span class="glyphicon glyphicon-plus"></span> เพิ่มผู้ติดต่อ</a>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
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
                "order": [[3,'desc']],
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": "{{{ URL::action('AdminRestController@getData') }}}",
                /*"fnServerParams": function ( aoData ) {
                 aoData.push( { "name": "location_id", "value": location_id } );
                 },*/
                "fnDrawCallback": function ( oSettings ) {
                }
            }).rowGrouping({
                sGroupingClass : 'group',
                iGroupingColumnIndex : 0
            });

            /*Submit Form*/
            var data;
            $('form').submit(function(e){

                var create_new = $('input[name=new]').val();

                var i = 0;
                var contacts = [];
                $( "#tab_logic > tbody > tr:has(td)" ).each(function( index ) {

                    if ($('input[name=name' + i + ']').val()!="" || $('input[name=tel' + i + ']').val() || $('input[name=email' + i + ']').val())
                    {
                        var contact = {
                            'id' : (typeof $('input[name=id' + i + ']') != 'undefined') ? $('input[name=id' + i + ']').val() : 0,
                            'name' : $('input[name=name' + i + ']').val(),
                            'tel' : $('input[name=tel' + i + ']').val(),
                            'email' : $('input[name=email' + i + ']').val()
                        };

                        contacts.push(contact);
                    }

                    i++;
                });

                data = {
                    '_token' : $("input[name=_token]").val(),
                    'id' : $('#old_id').val(),
                    'name' : $('#inputName').val(),
                    'address' : $('#textareaAddress').val(),
                    'note' : $('#textareaNote').val(),
                    'location_id' : $('#comboLocation').val(),
                    'contacts' : contacts
                };

                $.ajax({
                    type: "POST",
                    url: (create_new=="true") ? "{{ URL::action('AdminRestController@postCreate') }}" : "{{ URL::action('AdminRestController@postEdit') }}",
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

            /*add delete contact*/
            $("#add_contact").click(function(e){
                e.preventDefault();

                var i= $('#tab_logic > tbody > tr:has(td)').size();

                $('#addr'+i).html("<td><input name='name" + i + "' type='text' class='form-control' /> </td> <td><input  name='email" + i + "' type='email' class='form-control'></td><td><input  name='tel" + i + "' type='text' class='form-control'></td> <td><button type='button' row='"+i+"' class='del_contact btn btn-link' onclick='delContract("+i+");'>ลบ</button></td>");

                $('#tab_logic').append('<tr id="addr'+(i+1)+'"></tr>');
                i++;
            });
        });

        function delContract(id)
        {
            $('#addr'+id).html('');
        };

        function delDbContract(id)
        {
            if(confirm('ต้องการยืนยันการลบผู้ติดต่อนี้หรือไม่ ?')==true)
            {
                $.ajax({
                    type: "POST",
                    url: "{{ URL::action('AdminRestController@postContactDelete') }}",
                    data :
                    {
                        '_token' : $('input[name=_token]').val(),
                        'id' : id //contact id
                    }
                }).done(function(data) {
                    var id = data.id;
                    $('#tab_logic > tbody > tr[contact_id=' + id + ']').html('');
                });
            }
            return false;
        }

        function openDelete(id)
        {
            if(confirm('ต้องการยืนยันการลบหรือไม่ ?')==true)
            {
                $.ajax({
                    type: "POST",
                    url: "{{ URL::action('AdminRestController@postDelete') }}",
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
            $('#formModalLabel').html('สร้างสถานที่รับประทานอาหาร/ร้านอาหาร');
            //reset form
            $('form')[0].reset();
            //set new value
            $('input[name=new]').val(true);
            //set table contact initial
            $( "#tab_logic > tbody > tr" ).remove();
            $('#tab_logic > tbody').html("<tr id='addr0'><td><input name='name0' type='text' class='form-control' /> </td> <td><input  name='email0' type='email' class='form-control'></td><td><input  name='tel0' type='text' class='form-control'></td> <td></td></tr><tr id='addr1'></tr>");
            //open modal
            $('#formModal').modal({ keyboard : false });
        }

        function openEdit(id)
        {
            //set title
            $('#formModalLabel').html('แก้ไขสถานที่รับประทานอาหาร/ร้านอาหาร');
            //set new value
            $('input[name=new]').val(false);

            //get Data From Ajax
            $.ajax({
                url: "{{ URL::action('AdminRestController@getById') }}",
                data :
                {
                    '_token' : $('input[name=_token]').val(),
                    'id' : id
                }
            }).done(function(data) {

                var contacts = data.contacts;
                var c = 0;

                //delete rows before
                $( "#tab_logic > tbody > tr" ).remove();
                //loop fill contacts in lists
                $.each(contacts, function(index, item){
                    var html = '';
                    html += '<tr contact_id="' + item.id + '" id="addr' + index + '">';
                    html += '<td><input name="name' + index + '" type="text" class="form-control" value="' + item.name + '" /> <input type="hidden" name="id' + index + '" value="' + item.id + '" /> </td>';
                    html += '<td><input name="email' + index + '" type="email" class="form-control" value="' + item.email + '" /> </td>';
                    html += '<td><input name="tel' + index + '" type="text" class="form-control" value="' + item.tel + '" /> </td>';
                    html += '<td> <button type="button" row="' + index + '" class="del_contact btn btn-link" onclick="return delDbContract(' + item.id + ');">ลบ</button> </td>';
                    html += '</tr>';

                    $( "#tab_logic > tbody").append(html);
                    c++;
                });

                $( "#tab_logic > tbody").append('<tr id="addr' + c + '"></tr>');

                //---- Set Value----
                var data = data.data;
                //set old value
                $('#old_id').val(data.id);
                $('#inputName').val(data.name);
                $('#textareaAddress').val(data.address);
                $('#textareaNote').val(data.note);
                $('#comboLocation').val(data.location_id).attr('selected');
            });

            //open modal
            $('#formModal').modal({ keyboard : false });
        }

    </script>
@stop