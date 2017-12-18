{{--Locations Admin รวมของ Project Living University --}}
@extends('svms.layouts.admin')

@section('title')
    สถานที่
@stop

@section('extraScripts')
    {{--Use Data Tables--}}
    {{ HTML::style('dependencies/DataTables/media/css/jquery.dataTables.min.css') }}
    {{ HTML::script('dependencies/DataTables/media/js/jquery.dataTables.min.js') }}
    {{--USe Jquery Gmap Picker--}}
    {{ HTML::script('http://maps.google.com/maps/api/js?sensor=false&libraries=places&key=AIzaSyAEWFSUUyL1mpGenMXF_AR-zsCW_GFxGMk') }}
    {{ HTML::script('dependencies/jquery-locationpicker-plugin-master/dist/locationpicker.jquery.js') }}
@stop

@section('extraStyles')
    <style type="text/css">
        .tab-pane{
            padding: 5px;
        }
        .group,
        .group:hover {
            background-color: #f9dd34 !important;
        }
    </style>
@stop

@section('content')
    <div class="page-header">
        <h2>
            สถานที่/ที่พัก/ร้านอาหาร/สถานที่จัดกิจกรรม
            <div class="pull-right">
                <a href="javascript:openCreate();" class="btn btn-small btn-primary"><span class="fa fa-plus"></span> สร้างใหม่</a>
            </div>
        </h2>
    </div>
    {{--<div class="panel panel-default">
        <div class="panel-body">
            <form id="search" class="form-inline">
                <div class="form-group">
                    <label for="comboLocationBase">พื้นที่ดูงาน</label>
                    <select class="form-control" id="comboLocationBase" name="comboLocationBase">
                        <option value="">ทั้งหมด</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}">{{ $area->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>--}}
    <table id="editor_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th class="col-md-3">ชื่อสถานที่</th>
            <th>จังหวัด</th>
            <th>อำเภอ</th>
            <th>ตำบล</th>
            <th class="col-md-2">พื้นที่</th>
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
                            <li role="presentation" class="active"><a href="#general" aria-controls="general" role="tab" data-toggle="tab"><i class="fa fa-info"></i> ข้อมูลทั่วไป</a></li>
                            <li role="presentation"><a href="#gmap" aria-controls="gmap" role="tab" data-toggle="tab"><i class="fa fa-map-marker"></i> แผนที่ Google</a></li>
                        </ul>

                        <!-- Tab panes -->
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="general">
                                <div class="form-group">
                                    <label for="inputName">ชื่อสถานที่ *</label>
                                    <input type="text" class="form-control" id="inputName" placeholder="" required>
                                </div>

                                <div class="form-group">
                                    <label for="comboArea">อยู่ในพื้นที่พัฒนามูลนิธิแม่ฟ้าหลวงฯ</label>
                                    <select id="comboArea" class="form-control">
                                        <option value="">ไม่ระบุ</option>
                                        @foreach($areas as $area)
                                            <option value="{{ $area->id }}">{{ $area->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>ประเภทสถานที่</label>
                                    <div>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="is_accommodation"> เป็นที่พัก/โรงแรม
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="is_restaurant"> เป็นร้านอาหาร
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="is_conference"> เป็นสถานที่จัดกิจกรรม/หอประชุม
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="comboProvince">จังหวัด *</label>
                                    <select id="comboProvince" class="form-control" onchange="getAmphur();" required>
                                        {{--Default เชียงราย--}}
                                        <option value="">เลือก</option>
                                        @foreach($provinces as $province)
                                            <option value="{{ $province->PROVINCE_ID }}">{{ $province->PROVINCE_NAME }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="comboAmphur">อำเภอ *</label>
                                    <select id="comboAmphur" class="form-control" onchange="getDistrict();" required>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="comboDistrict">ตำบล *</label>
                                    <select id="comboDistrict" class="form-control" required>
                                    </select>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="gmap">
                                <div class="form-group collapsed">
                                    <label for="us2-address">ค้นหาสถานที่</label>
                                    <input type="text" id="us2-address" class="form-control" placeholder="พิมพ์เพื่อค้นหาสถานที่"/>
                                </div>
                                <div class="form-group collapsed">
                                    <label for="us2-radius">รัศมี(ตารางเมตร)</label>
                                    <input type="number" id="us2-radius" class="form-control" readonly value="300"/>
                                </div>
                                <div id="us2" class="collapsed" style="width: 100%; height: 250px;"></div>
                                <div class="form-group">
                                    <label for="us2-lat">Latitude</label>
                                    <input type="text" id="us2-lat" class="form-control"/>
                                </div>
                                <div class="form-group">
                                    <label for="us2-lon">Longitude</label>
                                    <input type="text" id="us2-lon" class="form-control"/>
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
            /*Combo Area*/
            $('#comboArea').on('change', function(){

                if ($(this).val()!=undefined && $(this).val()!="")
                {
                    $.ajax({
                        type: "GET",
                        url: "{{ URL::action('AdminLocationController@getArea') }}",
                        data: { '_token' : $('input[name=_token]').val(), 'id' : $(this).val() },
                        success: function (data) {
                            var data = data.data;
                            //set default value
                            $('#comboProvince').val(data.province).change();
                            setTimeout(function(){
                                $('#comboAmphur').val(data.city).change();
                            },500);
                            setTimeout(function(){
                                $('#comboDistrict').val(data.district).change();
                            },1000);
                        },
                        dataType: 'json'
                    });
                }

            });
            /*Google Map*/
            $('#us2').locationpicker({
                location: {latitude: 20.287150, longitude: 99.811429},
                radius: 300,
                zoom: 15,
                enableAutocomplete: true,
                inputBinding: {
                    latitudeInput: $('#us2-lat'),
                    longitudeInput: $('#us2-lon'),
                    radiusInput: $('#us2-radius'),
                    locationNameInput: $('#us2-address')
                }
            });
            //fire location picker on modal
            $('#formModal').on('shown.bs.modal', function () {
                $('#us2').locationpicker('autosize');
            });
            //fire location picker on tab
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                e.target // newly activated tab
                e.relatedTarget // previous active tab

                $('#us2').locationpicker('autosize');
            })

            /*Load DataTables*/
            var oTable;
            oTable = $('#editor_table').dataTable({
                "sDom": "<'row'<'col-md-6'l><'col-md-6'f>r>t<'row'<'col-md-6'i><'col-md-6'p>>",
                "language": {
                    "url": "{{ URL::asset('assets/js/Thai.json') }}"
                },
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": "{{ URL::action('AdminLocationController@getData') }}",
                "columnDefs": [
                ],
                "order": [[ 4, 'asc' ]],
                "drawCallback": function ( settings ) {

                }
            });

            /*Submit Form*/
            var data;
            $('form').submit(function(e){

                var create_new = $('input[name=new]').val();

                var lat_lon = $('#us2-lat').val()+', '+$('#us2-lon').val();

                data = {
                    '_token' : $("input[name=_token]").val(),
                    'id' : $('#old_id').val(),
                    'name' : $('#inputName').val(),
                    'province' : $('#comboProvince').val(),
                    'city' : $('#comboAmphur').val(),
                    'district' : $('#comboDistrict').val(),
                    'area' : $('#comboArea').val(),
                    'is_accommodation' : ($('#is_accommodation').prop('checked')) ? 1 : 0,
                    'is_restaurant' : ($('#is_restaurant').prop('checked')) ? 1 : 0,
                    'is_conference' : ($('#is_conference').prop('checked')) ? 1 : 0,
                    'gmap' : lat_lon
                };

                $.ajax({
                    type: "POST",
                    url: (create_new=="true") ? "{{ URL::action('AdminLocationController@postCreate') }}" : "{{ URL::action('AdminLocationController@postEdit') }}",
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

        function getAmphur(province_id)
        {
            var province_id = (province_id===undefined) ? $('#comboProvince').val() : province_id;
            if (!province_id)
            {
                return false;
            }

            $.ajax({
                type: "GET",
                url: "{{ URL::action('AdminLocationController@getAmphur') }}",
                data: { '_token' : $('input[name=_token]').val(), 'province_id' : province_id },
                success: function (data) {
                    if ($('#comboAmphur').empty())
                    {
                        $('#comboAmphur').append($("<option>").val('').text('เลือก'));
                        $(data.data).each(function(index, item) {
                            $('#comboAmphur').append($("<option>").val(item.id).text(item.text));
                        });
                    }
                },
                dataType: 'json'
            });
        }

        function getDistrict(amphur_id)
        {
            var amphur_id = (amphur_id===undefined) ? $('#comboAmphur').val() : amphur_id;
            if (!amphur_id)
            {
                return false;
            }

            $.ajax({
                type: "GET",
                url: "{{ URL::action('AdminLocationController@getDistrict') }}",
                data: { '_token' : $('input[name=_token]').val(), 'amphur_id' : amphur_id },
                success: function (data) {
                    if ($('#comboDistrict').empty())
                    {
                        $('#comboDistrict').append($("<option>").val('').text('เลือก'));
                        $(data.data).each(function(index, item) {
                            $('#comboDistrict').append($("<option>").val(item.id).text(item.text));
                        });
                    }
                },
                dataType: 'json'
            });
        }

        function openCreate()
        {
            //set title
            $('#formModalLabel').html('สร้างสถานที่ใหม่');
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
            $('#formModalLabel').html('แก้ไขสถานที่');
            //set new value
            $('input[name=new]').val(false);

           //get Data From Ajax
            $.ajax({
                url: "{{ URL::action('AdminLocationController@getById') }}",
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

                var geo = data.geo.split(",");

                $('#us2-lat').val('');
                $('#us2-lon').val('');

                if (geo.length == 2)
                {
                    $('#us2-lat').val(geo[0].trim());
                    $('#us2-lon').val(geo[1].trim());
                    $('#us2').locationpicker();
                }

                $('#is_accommodation').prop('checked', (data.is_accommodation==1) ? true : false);
                $('#is_restaurant').prop('checked', (data.is_restaurant==1) ? true : false);
                $('#is_conference').prop('checked', (data.is_conference==1) ? true : false);

                $('#comboArea').val(data.area);
                //load amphur
                getAmphur(data.province);
                //load district
                getDistrict(data.city);

                setTimeout(function()
                {
                    $('#comboProvince').val(data.province).attr('selected');
                    $('#comboAmphur').val(data.city).attr('selected');
                    $('#comboDistrict').val(data.district).attr('selected');
                },500);
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
                    url: "{{ URL::action('AdminLocationController@postDelete') }}",
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

    </script>
@stop