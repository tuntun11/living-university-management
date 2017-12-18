@extends('svms.layouts.default')

@section('title')
    Living University Management System
@stop

@section('extraScripts')
    {{--Use Bootstrap DataTables--}}
    {{ HTML::script('dependencies/DataTables/media/js/jquery.dataTables.min.js') }}
    {{ HTML::style('dependencies/DataTables/media/css/jquery.dataTables.min.css') }}
    {{--Use Bootstrap Select2--}}
    {{ HTML::script('dependencies/select2-4.0.0-beta.3/dist/js/select2.min.js') }}
    {{ HTML::style('dependencies/select2-4.0.0-beta.3/dist/css/select2.min.css') }}
    {{--Use Bootstrap Vertical Tab--}}
    {{ HTML::style('dependencies/bootstrap-vertical-tabs-master/bootstrap.vertical-tabs.css') }}
@stop

@section('extraStyles')
    <style type="text/css">
        h5{
            font-weight: bold;
            font-size: 15px;
        }
        .spaceH{
            height: 5px;
        }
        .planA{
            color: #265a88;
        }
        .planB{
            color: #c02e2a;
        }
        .aRight{
            text-align: right;
        }
        .inTablePrice{
            width: 80px;
        }
        .inTableDiscount{
            width: 50px;
        }
        .total{
            font-weight: bold;
            background-color: #EEEEEE;
        }
        .full-width{
            width: 100%;
        }
        .strong{
            font-weight: 600;
        }
        .numberTotal{
            text-decoration:underline;
            border-bottom: 1px solid #000;
        }
        hr.end-activity {
            height: 6px;
            border: 0;
            box-shadow: inset 0 6px 6px -6px rgba(0, 0, 0, 0.5);
        }
        .showbox{
            margin-top: 10px;
        }
    </style>
@stop

@section('header')
    <span class="fa fa-money"></span>
    การจัดการงบประมาณ
@stop

@section('content')

    <div id="party_section" class="container-fluid">
        <select class="form-control" id="party_select" style="width: 100%;">
            <option value="" selected disabled>กรุณาเลือกคณะ</option>
            @foreach($parties as $p)
                <?php
                $create_budgeted = ($p->budgetingPassed()) ? '*' : '';
                ?>
                <option {{ (isset($party) && $party->id===$p->id) ? 'selected' : '' }} value="{{ URL::to('coordinator/budget/'.$p->id.'/view') }}">{{ $p->customer_code.' '.$p->name.' ('.$p->people_quantity.') '.$create_budgeted }}</option>
            @endforeach
        </select>
    </div>

    <div class="clearfix" style="height: 10px;"></div>

    {{--Alert แสดงการทำงาน--}}
    @if(!isset($party))
        <div class="alert alert-warning alert-block" style="margin: 10px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <p>ท่านต้องทำการเลือกคณะเพื่อทำการลงงบประมาณ</p>
        </div>
    @else
        {{--Show how to work it--}}
        <div style="margin: 0px 15px 10px 15px;" class="alert alert-info alert-dismissible fade in" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <strong>วิธีการคิดงบประมาณ</strong>
            <br/>
            <i class="fa fa-hand-o-right"></i> ขั้นแรก กรุณาเลือกวันที่จากนั้นเลือก Facility ที่จะใช้ลงในกำหนดการที่ปุ่ม "ระบุงบประมาณตามกำหนดการ"
            <br/>
            <i class="fa fa-hand-o-right"></i> ขั้นที่สอง ตรวจสอบรายการ Facility ที่ใช้และผลการคำนวนงบแยกรายการที่ปุ่ม "สรุปงบแยกประเภท"
            <br/>
            <i class="fa fa-hand-o-right"></i> ขั้นสุดท้าย ดูสรุปผลงบประมาณรวมทั้งสิ้นพร้อมค่าบริหารจัดการกับส่วนลด และออกใบเสนอราคาได้ที่ปุ่ม "สรุปงบประมาณรวมและออกเอกสาร"
        </div>

        {{--if have party object--}}
        <form id="formBudget" role="form" method="post">
            <!--Party Id-->
            <input type="hidden" id="party_budget_id" value="{{ $party->id }}" />
            <!-- CSRF Token -->
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />

            <div id="schedule_section" class="container-fluid show-budget">

                <!-- Nav tabs -->
                <ul class="nav nav-pills" role="tablist">
                    <li role="presentation" class="active"><a class="main_panel" href="#select_budget" aria-controls="select_budget" role="tab" data-toggle="tab"><i class="fa fa-table"></i> ระบุงบประมาณตามกำหนดการ</a></li>
                    <li role="presentation"><a class="main_panel" href="#summary_type" aria-controls="summary_type" role="tab" data-toggle="tab"><i class="fa fa-calculator"></i> สรุปงบแยกประเภท</a></li>
                    <li role="presentation"><a class="main_panel" href="#total_summary" aria-controls="total_summary" role="tab" data-toggle="tab"><i class="fa fa-btc"></i> สรุปงบประมาณรวมและออกเอกสาร</a></li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content">
                    {{--select and calculate tab--}}
                    <div role="tabpanel" class="tab-pane active" id="select_budget" style="margin-top: 10px;">

                        <div style="margin-bottom: 10px; display: none;" class="form-group">
                            <select class="form-control">
                                <option value="">เลือกวันที่จัดการงบประมาณ</option>
                                @foreach($dates as $date)
                                    <option value="{{ $date }}">{{ ScheduleController::dateRangeStr($date, $date, true) }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{--tab for schedule plan managing--}}
                        <ul class="nav nav-tabs" role="tablist">
                            @foreach($plans as $plan)
                                <li role="presentation" class="{{ ($plan=='a') ? 'active' : '' }}"><a href="#tab_budget_{{ $plan }}" aria-controls="tab_budget_{{ $plan }}" role="tab" data-toggle="tab"><span class="plan{{ ucfirst($plan) }}">แผน {{ ucfirst($plan) }}</span></a></li>
                            @endforeach
                        </ul>

                        <!-- Tab panes -->
                        <div class="tab-content">
                            @foreach($plans as $plan)

                                <div role="tabpanel" class="tab-pane {{ ($plan=='a') ? 'active' : '' }}" id="tab_budget_{{ $plan }}">
                                    <div class="spaceH"></div>
                                    <div class="panel-group activities_budget" id="activities_budget_{{ $plan }}" role="tablist" aria-multiselectable="true">

                                        <?php
                                        $d = 1;
                                        ?>
                                        @foreach($dates as $date)
                                            <div class="panel panel-default">
                                                <div class="panel-heading" role="tab" id="{{ 'tab_'.$date.'_'.$plan }}">
                                                    <h4 class="panel-title">
                                                        <a title="คลิกเพื่อโหลดข้อมูลงบประมาณ" class="budgeting_plan" plan="{{ $plan }}" date="{{ $date }}" role="button" data-toggle="collapse" data-parent="#activities_budget_{{ $plan }}" href="#{{ $date.'_'.$plan }}" aria-expanded="true" aria-controls="{{ $date.'_'.$plan }}">
                                                            วันที่ <span id="{{ 'date_'.$date.'_'.$plan }}">{{ ScheduleController::dateRangeStr($date, $date, true) }}</span>
                                                            {{--<div class="pull-right">
                                                                <span class="label label-success">รวมทั้งสิ้น <u>10,000</u> บาท</span>
                                                            </div>
                                                            <div class="clearfix"></div>--}}
                                                        </a>
                                                    </h4>
                                                </div>

                                                <div id="{{ $date.'_'.$plan }}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="{{ 'tab_'.$date.'_'.$plan }}">
                                                    {{--Calculate Panel--}}
                                                    <div style="display:none;" id="{{ 'data_'.$date.'_'.$plan }}" class="panel-body">

                                                        <h4 class="text-primary"><u>คิดราคาที่พัก</u></h4>
                                                        <div id="{{ 'accommodations_'.$date.'_'.$plan }}">
                                                            {{--Load accommodations via ajax--}}
                                                        </div>

                                                        <h4 class="text-primary"><u>คิดราคาจากกำหนดการดูงาน</u></h4>
                                                        <div id="{{ 'activities_'.$date.'_'.$plan }}">
                                                            {{--Load activities via ajax--}}
                                                        </div>

                                                    </div>
                                                    {{--Loading Panel--}}
                                                    <div id="{{ 'loading_'.$date.'_'.$plan }}" class="panel-body">
                                                        <div class="alert alert-warning" role="alert"><i class="fa fa-spinner fa-spin fa-2x"></i> กำลังประมวลผลข้อมูลกรุณารอสักครู่</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                            $d++;
                                            ?>
                                        @endforeach

                                    </div>

                                </div>
                            @endforeach
                        </div>

                        <div class="clearfix"></div>

                    </div>
                    {{--calculate group by type and item--}}
                    <div role="tabpanel" class="tab-pane" id="summary_type" style="margin-top: 10px;">
                        {{--Get Data From Ajax--}}
                        {{--Calculate with summary item and group--}}
                        <span id="loading_summary_type"></span>
                        <div id="summary_type_data" style="display: none;">
                            <h3>งบแบบแยกประเภท</h3>
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" class="active"><a href="#tab_summary_type_a" aria-controls="tab_summary_type_a" role="tab" data-toggle="tab"><span class="planA">แผน A</span></a></li>
                                <li role="presentation"><a href="#tab_summary_type_b" aria-controls="tab_summary_type_b" role="tab" data-toggle="tab"><span class="planB">แผน B</span></a></li>
                            </ul>

                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane active" id="tab_summary_type_a">
                                    {{--For Plan A only--}}
                                    <div class="spaceH"></div>
                                    <ul class="nav nav-pills pull-right" role="tablist">
                                        <li role="presentation" class="active"><a href="#summary_type_a" aria-controls="summary_type_a" role="tab" data-toggle="tab"><i class="fa fa-plus-square-o"></i> รายได้</a></li>
                                        <li role="presentation"><a href="#damage_type_a" aria-controls="damage_type_a" role="tab" data-toggle="tab"><i class="fa fa-minus-square-o"></i> ค่าใช้จ่าย</a></li>
                                    </ul>

                                    <div class="tab-content">
                                        <div role="tabpanel" class="tab-pane active" id="summary_type_a"></div>
                                        <div role="tabpanel" class="tab-pane" id="damage_type_a"></div>
                                    </div>

                                </div>
                                <div role="tabpanel" class="tab-pane" id="tab_summary_type_b">
                                    {{--For Plan B only--}}
                                    <div class="spaceH"></div>
                                    <ul class="nav nav-pills pull-right" role="tablist">
                                        <li role="presentation" class="active"><a href="#summary_type_b" aria-controls="summary_type_b" role="tab" data-toggle="tab"><i class="fa fa-plus-square-o"></i> รายได้</a></li>
                                        <li role="presentation"><a href="#damage_type_b" aria-controls="damage_type_b" role="tab" data-toggle="tab"><i class="fa fa-minus-square-o"></i> ค่าใช้จ่าย</a></li>
                                    </ul>

                                    <div class="tab-content">
                                        <div role="tabpanel" class="tab-pane active" id="summary_type_b"></div>
                                        <div role="tabpanel" class="tab-pane" id="damage_type_b"></div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    {{--calculate group by type and and discount and service charge--}}
                    <div role="tabpanel" class="tab-pane" id="total_summary" style="margin-top: 10px;">
                        <span id="loading_summary_total"></span>
                        <div id="summary_total_data" style="display: none;">
                            <h3>สรุปยอดงบประมาณ</h3>
                            {{--show err or warning data--}}
                            <div id="summary_total_alert"></div>
                            <div id="summary_total_div" class="table-responsive">
                                {{--Start Element to keep json data--}}
                                <input type="hidden" id="budgeting_data">
                                {{--End Element to keep json data--}}
                                <table id="table_summary_total" class="table table-condensed">

                                    @if($count_plan_b>0)
                                        <thead>
                                        <tr>
                                            <th width="20%"> </th>
                                            <th class="aRight" colspan="3"><span class="planA">แผน A</span></th>
                                            <th class="aRight" colspan="3"><span class="planB">แผน B</span></th>
                                            <th class="aRight" colspan="3"><span class="">ผลต่าง</span></th>
                                        </tr>
                                        <tr>
                                            <th width="20%"> </th>
                                            <th class="aRight">ขาย A</th>
                                            <th class="aRight">ทุน A</th>
                                            <th class="aRight">กำไร A</th>
                                            <th class="aRight">ขาย B</th>
                                            <th class="aRight">ทุน B</th>
                                            <th class="aRight">กำไร B</th>
                                            <th class="aRight">ขายต่าง</th>
                                            <th class="aRight">ทุนต่าง</th>
                                            <th class="aRight">กำไรต่าง</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>ห้องพัก</td>
                                            <td class="aRight"><span id="saleAccommodationPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="costAccommodationPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="profitAccommodationPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="saleAccommodationPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="costAccommodationPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="profitAccommodationPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="saleAccommodationDiff" class=""></span></td>
                                            <td class="aRight"><span id="costAccommodationDiff" class=""></span></td>
                                            <td class="aRight"><span id="profitAccommodationDiff" class=""></span></td>
                                        </tr>
                                        <tr>
                                            <td>อาหาร</td>
                                            <td class="aRight"><span id="saleMealPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="costMealPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="profitMealPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="saleMealPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="costMealPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="profitMealPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="saleMealDiff" class=""></span></td>
                                            <td class="aRight"><span id="costMealDiff" class=""></span></td>
                                            <td class="aRight"><span id="profitMealDiff" class=""></span></td>
                                        </tr>
                                        <tr>
                                            <td>ยานพาหนะ</td>
                                            <td class="aRight"><span id="saleTransportPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="costTransportPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="profitTransportPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="saleTransportPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="costTransportPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="profitTransportPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="saleTransportDiff" class=""></span></td>
                                            <td class="aRight"><span id="costTransportDiff" class=""></span></td>
                                            <td class="aRight"><span id="profitTransportDiff" class=""></span></td>
                                        </tr>
                                        <tr>
                                            <td>วิทยากรและบุคลากร</td>
                                            <td class="aRight"><span id="saleActivityPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="costActivityPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="profitActivityPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="saleActivityPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="costActivityPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="profitActivityPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="saleActivityDiff" class=""></span></td>
                                            <td class="aRight"><span id="costActivityDiff" class=""></span></td>
                                            <td class="aRight"><span id="profitActivityDiff" class=""></span></td>
                                        </tr>
                                        <tr>
                                            <td>ห้องประชุม</td>
                                            <td class="aRight"><span id="saleHallPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="costHallPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="profitHallPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="saleHallPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="costHallPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="profitHallPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="saleHallDiff" class=""></span></td>
                                            <td class="aRight"><span id="costHallDiff" class=""></span></td>
                                            <td class="aRight"><span id="profitHallDiff" class=""></span></td>
                                        </tr>
                                        <tr>
                                            <td>วัสดุและอุปกรณ์ประกอบการเรียนรู้</td>
                                            <td class="aRight"><span id="saleToolPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="costToolPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="profitToolPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="saleToolPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="costToolPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="profitToolPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="saleToolDiff" class=""></span></td>
                                            <td class="aRight"><span id="costToolDiff" class=""></span></td>
                                            <td class="aRight"><span id="profitToolDiff" class=""></span></td>
                                        </tr>
                                        <tr>
                                            <td>กิจกรรมพิเศษ</td>
                                            <td class="aRight"><span id="saleSpecialPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="costSpecialPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="profitSpecialPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="saleSpecialPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="costSpecialPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="profitSpecialPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="saleSpecialDiff" class=""></span></td>
                                            <td class="aRight"><span id="costSpecialDiff" class=""></span></td>
                                            <td class="aRight"><span id="profitSpecialDiff" class=""></span></td>
                                        </tr>
                                        <tr style="display: none;">
                                            <td>อื่นๆ</td>
                                            <td class="aRight"><span id="saleOtherPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="costOtherPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="profitOtherPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="saleOtherPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="costOtherPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="profitOtherPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="saleOtherDiff" class=""></span></td>
                                            <td class="aRight"><span id="costOtherDiff" class=""></span></td>
                                            <td class="aRight"><span id="profitOtherDiff" class=""></span></td>
                                        </tr>

                                        </tbody>
                                        <tfoot>
                                        <tr class="total">
                                            <td>รวม</td>
                                            <td class="aRight"><span id="saleTotalPlanA" class="numPlanA"></span><input type="hidden" id="numSaleTotalPlanA"> <input type="hidden" id="numCostTotalPlanA"></td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight"><span id="saleTotalPlanB" class="numPlanB"></span><input type="hidden" id="numSaleTotalPlanB"> <input type="hidden" id="numCostTotalPlanB"></td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight"><span id="saleTotalDiff" class=""></span></td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                รวมค่าบริหารจัดการ
                                                <select id="numberSaleCharge" onchange="calculateChargeAndDiscount();">
                                                    @for($i=0;$i<=30;$i++)
                                                        <option value="{{ $i }}">{{ ($i==0) ? 'ไม่มี' : $i }}</option>
                                                    @endfor
                                                </select>
                                                %
                                            </td>
                                            <td class="aRight"><span id="saleChargePlanA" class="numPlanA"></span></td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight"><span id="saleChargePlanB" class="numPlanB"></span></td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight"><span id="saleChargeDiff" class=""></span></td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                        </tr>
										<tr class="total success">
                                            <td><span class="numberTotal">ราคารวม</span></td>
                                            <td class="aRight"><span id="saleGrandTotalBeforePlanA" class="numPlanA"></span></td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight"><span id="saleGrandTotalBeforePlanB" class="numPlanB"></span></td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                        </tr>
                                        <tr class="danger">
                                            <td>
                                                หักส่วนลด
                                                <select id="numberDiscountSale" onchange="calculateChargeAndDiscount();">
                                                    <?php
                                                    $d = 0;
                                                    ?>
                                                    @while($d<=50)
                                                        @if($d==0)
                                                            <option value="{{ $d }}">ไม่มี</option>
                                                        @else
                                                            <option value="{{ $d }}">{{ $d }}</option>
                                                        @endif

                                                        <?php
                                                        $d +=5;
                                                        ?>
                                                    @endwhile
                                                </select>
                                                %
                                            </td>
                                            <td class="aRight"><span id="saleDiscountPlanA" class="numPlanA"></span></td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight"><span id="saleDiscountPlanB" class="numPlanB"></span></td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight"><span id="saleDiscountDiff" class=""></span></td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                        </tr>
                                        {{-- because current not used.--}}
                                        <tr class="total success">
                                            <td><span class="numberTotal">ราคารวม  (ที่คณะต้องชำระจริง)</span></td>
                                            <td class="aRight"><span id="saleGrandTotalPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="costTotalPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="profitTotalPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="saleGrandTotalPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="costTotalPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="profitTotalPlanB" class="numPlanB"></span></td>
                                            <td class="aRight"><span id="saleGrandTotalDiff" class=""></span></td>
                                            <td class="aRight"><span id="costTotalDiff" class=""></span></td>
                                            <td class="aRight"><span id="profitTotalDiff" class=""></span></td>
                                        </tr>
                                        <tr>
                                            <td>ราคาต่อหัว
                                                ( <input readonly style="width:60px;" id="numberParticipant" type="number" value="{{ $party->people_quantity }}"> ท่าน )
                                            </td>
                                            <td class="aRight"><span id="averagePlanA" class="numPlanA"></span></td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight"><span id="averagePlanB" class="numPlanB"></span></td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight"><span id="averageDiff" class=""></span></td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                        </tr>
                                        <tr class="total">
                                            <td>กำไร %</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight"><span id="profitPercentPlanA" class="numPlanA"></span>%</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight"><span id="profitPercentPlanB" class="numPlanB"></span>%</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight"><span id="profitPercentDiff" class=""></span>%</td>
                                        </tr>
                                        <tr class="damage danger">
                                            <td><strong>ค่าใช้จ่ายรวมทั้งสิ้น</strong></td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight"><span id="expenseTotalPlanA" class="numPlanA strong"></span> <input type="hidden" id="numExpenseTotalPlanA"></td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight"><span id="expenseTotalPlanB" class="numPlanB strong"></span> <input type="hidden" id="numExpenseTotalPlanB"></td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight"><span id="expenseTotalDiff" class=""></span></td>
                                            <td class="aRight">&nbsp;</td>
                                        </tr>
                                        <tr class="damage success">
                                            <td><strong>รายได้ที่แท้จริง<br/>(หลังหักค่าใช้จ่าย)</strong></td>
                                            <td class="aRight"><span id="saleActualTotalPlanA" class="numPlanA strong"></span></td>
                                            <td class="aRight"><span id="costActualTotalPlanA" class="numPlanA strong"></span></td>
                                            <td class="aRight"><span id="profitActualTotalPlanA" class="numPlanA strong"></span></td>
                                            <td class="aRight"><span id="saleActualTotalPlanB" class="numPlanB strong"></span></td>
                                            <td class="aRight"><span id="costActualTotalPlanB" class="numPlanB strong"></span></td>
                                            <td class="aRight"><span id="profitActualTotalPlanB" class="numPlanA strong"></span></td>
                                            <td class="aRight"><span id="saleActualTotalDiff" class="strong"></span></td>
                                            <td class="aRight"><span id="costActualTotalDiff" class="strong"></span></td>
                                            <td class="aRight"><span id="profitActualTotalDiff" class="strong"></span></td>
                                        </tr>
                                        <tr class="total damage">
                                            <td>กำไรที่ได้จริง %</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight"><span id="profitPercentActualPlanA" class="numPlanA"></span>%</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight"><span id="profitPercentActualPlanB" class="numPlanB"></span>%</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight"><span id="profitPercentActualDiff" class=""></span>%</td>
                                        </tr>
                                        </tfoot>
                                    @else
                                        <thead>
                                        <tr>
                                            <th width="20%"> </th>
                                            <th class="aRight" colspan="3"><span class="planA">แผน A</span></th>
                                        </tr>
                                        <tr>
                                            <th width="20%"> </th>
                                            <th class="aRight">ขาย</th>
                                            <th class="aRight">ทุน</th>
                                            <th class="aRight">กำไร</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>ห้องพัก</td>
                                            <td class="aRight"><span id="saleAccommodationPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="costAccommodationPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="profitAccommodationPlanA" class="numPlanA"></span></td>
                                        </tr>
                                        <tr>
                                            <td>อาหาร</td>
                                            <td class="aRight"><span id="saleMealPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="costMealPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="profitMealPlanA" class="numPlanA"></span></td>
                                        </tr>
                                        <tr>
                                            <td>ยานพาหนะ</td>
                                            <td class="aRight"><span id="saleTransportPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="costTransportPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="profitTransportPlanA" class="numPlanA"></span></td>
                                        </tr>
                                        <tr>
                                            <td>วิทยากรและบุคลากร</td>
                                            <td class="aRight"><span id="saleActivityPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="costActivityPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="profitActivityPlanA" class="numPlanA"></span></td>
                                        </tr>
                                        <tr>
                                            <td>ห้องประชุม</td>
                                            <td class="aRight"><span id="saleHallPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="costHallPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="profitHallPlanA" class="numPlanA"></span></td>
                                        </tr>
                                        <tr>
                                            <td>วัสดุและอุปกรณ์ประกอบการเรียนรู้</td>
                                            <td class="aRight"><span id="saleToolPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="costToolPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="profitToolPlanA" class="numPlanA"></span></td>
                                        </tr>
                                        <tr>
                                            <td>กิจกรรมพิเศษ</td>
                                            <td class="aRight"><span id="saleSpecialPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="costSpecialPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="profitSpecialPlanA" class="numPlanA"></span></td>
                                        </tr>
                                        <tr>
                                            <td>อื่นๆ</td>
                                            <td class="aRight"><span id="saleOtherPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="costOtherPlanA" class="numPlanA"></span></td>
                                            <td class="aRight"><span id="profitOtherPlanA" class="numPlanA"></span></td>
                                        </tr>
                                        </tbody>
                                        <tfoot>
                                        <tr class="total">
                                            <td>รวม</td>
                                            <td class="aRight"><span id="saleTotalPlanA" class="numPlanA"></span> <input type="hidden" id="numSaleTotalPlanA"> <input type="hidden" id="numCostTotalPlanA"></td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                รวมค่าบริหารจัดการ
                                                <select id="numberSaleCharge" onchange="calculateChargeAndDiscount();">
                                                    @for($i=0;$i<=30;$i++)
                                                        <option value="{{ $i }}">{{ ($i==0) ? 'ไม่มี' : $i }}</option>
                                                    @endfor
                                                </select>
                                                %
                                            </td>
                                            <td class="aRight"><span id="saleChargePlanA" class="numPlanA"></span></td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                        </tr>
										<tr class="total">
                                            <td><span class="numberTotal">ราคารวม</span></td>
                                            <td class="aRight"><span id="saleGrandTotalBeforePlanA" class="numPlanA numberTotal"></span></td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                        </tr>
                                        <tr class="danger">
                                            <td>
                                                หักส่วนลด
                                                <select id="numberDiscountSale" onchange="calculateChargeAndDiscount();">
                                                    <?php
                                                    $d = 0;
                                                    ?>
                                                    @while($d<=50)
                                                        @if($d==0)
                                                            <option value="{{ $d }}">ไม่มี</option>
                                                        @else
                                                            <option value="{{ $d }}">{{ $d }}</option>
                                                        @endif

                                                        <?php
                                                        $d +=5;
                                                        ?>
                                                    @endwhile
                                                </select>
                                                %
                                            </td>
                                            <td class="aRight"><span id="saleDiscountPlanA" class="numPlanA"></span></td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                        </tr>
                                        <tr class="total">
                                            <td><span class="numberTotal">ราคารวม (ที่คณะต้องชำระจริง)</span></td>
                                            <td class="aRight"><span id="saleGrandTotalPlanA" class="numPlanA numberTotal"></span></td>
                                            <td class="aRight"><span id="costTotalPlanA" class="numPlanA numberTotal"></span></td>
                                            <td class="aRight"><span id="profitTotalPlanA" class="numPlanA numberTotal"></span></td>
                                        </tr>
                                        <tr>
                                            <td>ราคาต่อหัว
                                                ( <input readonly style="width:60px;" id="numberParticipant" type="number" value="{{ $party->people_quantity }}"> ท่าน )
                                            </td>
                                            <td class="aRight"><span id="averagePlanA" class="numPlanA"></span></td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                        </tr>
                                        <tr class="total">
                                            <td>กำไร %</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight"><span id="profitPercentPlanA" class="numPlanA"></span>%</td>
                                        </tr>
                                        <tr class="damage danger">
                                            <td><strong>ค่าใช้จ่ายรวมทั้งสิ้น</strong></td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight"><span id="expenseTotalPlanA" class="numPlanA strong"></span> <input type="hidden" id="numExpenseTotalPlanA"></td>
                                            <td class="aRight">&nbsp;</td>
                                        </tr>
                                        <tr class="damage success">
                                            <td><strong>รายได้ที่แท้จริง<br/>(หลังหักค่าใช้จ่าย)</strong></td>
                                            <td class="aRight"><span id="saleActualTotalPlanA" class="numPlanA strong"></span></td>
                                            <td class="aRight"><span id="costActualTotalPlanA" class="numPlanA strong"></span></td>
                                            <td class="aRight"><span id="profitActualTotalPlanA" class="numPlanA strong"></span></td>
                                        </tr>
                                        <tr class="total damage">
                                            <td>กำไรที่ได้จริง %</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight">&nbsp;</td>
                                            <td class="aRight"><span id="profitPercentActualPlanA" class="numPlanA"></span>%</td>
                                        </tr>
                                        </tfoot>
                                    @endif

                                </table>
                            </div>
                        </div>
                        <div class="row" style="margin: 20px auto;">
                            <input type="hidden" id="number_total_task" value="">
                            <div id="save_budget_transaction" class="pull-right">
                                <button id="submitCreateDocument" data-loading-text="กำลังดำเนินการ..." type="button" class="btn btn-success btn-lg"><i class="fa fa-file-text"></i> สร้างใบเสนอราคา</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </form>

        {{--Modal Insert and Update Expense Item--}}
        <div id="modalExpenseItem" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalExpenseItemLabel">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="modalExpenseItemLabel">{{-- Modal Title --}}</h4>
                    </div>

                    <div class="modal-body">
                        <form role="form" id="formExpenseItem">
                            <!-- CSRF Token -->
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                            <!-- ./ csrf token -->
                            {{--Hidden parameter--}}
                            <input type="hidden" id="expense_party" name="expense_party" value="{{ (isset($party)) ? $party->id : 0 }}">
                            <input type="hidden" id="expense_date" name="expense_date">
                            <input type="hidden" id="expense_task_location" name="expense_task_location">
                            <input type="hidden" id="expense_budget_id" name="expense_budget_id">
                            <input type="hidden" id="expense_new" name="expense_new" value="true">
                            <input type="hidden" id="expense_type_selected" name="expense_type_selected">
                            <input type="hidden" id="expense_plan" name="expense_plan">
                            <input type="hidden" id="expense_master_task" name="expense_master_task">
                            {{--this hidden for fix null value when edit--}}
                            <input type="hidden" id="expense_item_in_budget" name="expense_item_in_budget">
							{{--Use for scrollTop--}}
							 <input type="hidden" id="expense_main_div" name="expense_main_div">
                            {{--Static Show--}}
                            <div class="row">
                                <div class="col-md-2"><span class="strong">วันที่</span></div>
                                <div class="col-md-10"><span id="expense_day"></span></div>
                            </div>
                            <div class="row">
                                <div class="col-md-2"><span class="strong">กิจกรรม</span></div>
                                <div class="col-md-10"><span id="expense_activity"></span></div>
                            </div>
                            <div class="row">
                                <div class="col-md-2"><span class="strong">สถานที่</span></div>
                                <div class="col-md-10"><span id="expense_location"></span></div>
                            </div>
                            <hr/>
                            <div id="expense_information">{{--Create by javascript--}}</div>
                            <div id="expense_information_loading" style="display:none;">
                                <div class="alert alert-warning" role="alert"><i class="fa fa-spinner fa-spin fa-2x"></i> กำลังประมวลผลข้อมูลกรุณารอสักครู่</div>
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">ปิด</button>
                        <button id="submitExpenseForm" type="button" class="btn btn-success"><i class="fa fa-save"></i> บันทึกรายการ</button>
                    </div>
                </div>
            </div>
        </div>

        {{--Modal Insert and Update Damage Item--}}
        <div id="modalDamageItem" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalDamageItemLabel">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="modalDamageItemLabel">{{-- Modal Title --}}</h4>
                    </div>

                    <div class="modal-body">
                        <form role="form" id="formDamageItem">
                            <!-- CSRF Token -->
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                            <!-- ./ csrf token -->
                            {{--Hidden parameter--}}
                            <input type="hidden" id="damage_party" name="damage_party" value="{{ (isset($party)) ? $party->id : 0 }}">
                            <input type="hidden" id="damage_date" name="damage_date">
                            <input type="hidden" id="damage_task_location" name="damage_task_location">
                            <input type="hidden" id="damage_budget_id" name="damage_budget_id">
                            <input type="hidden" id="damage_new" name="damage_new" value="true">
                            <input type="hidden" id="damage_plan" name="damage_plan">
                            <input type="hidden" id="damage_master_task" name="damage_master_task">
							{{--Use for scrollTop--}}
							<input type="hidden" id="damage_main_div" name="damage_main_div">
                            {{--Static Show--}}
                            <div class="row">
                                <div class="col-md-2"><span class="strong">วันที่</span></div>
                                <div class="col-md-10"><span id="damage_day"></span></div>
                            </div>
                            <div class="row">
                                <div class="col-md-2"><span class="strong">กิจกรรม</span></div>
                                <div class="col-md-10"><span id="damage_activity"></span></div>
                            </div>
                            <div class="row">
                                <div class="col-md-2"><span class="strong">สถานที่</span></div>
                                <div class="col-md-10"><span id="damage_location"></span></div>
                            </div>
                            <hr/>
                            <div id="damage_information">
                                <div class="form-group">
                                    <label class="control-label">รายการค่าใช้จ่าย *</label>
                                    <input type="text" class="form-control" id="damage_activity_item" name="damage_activity_item" placeholder="พิมพ์รายการค่าใช้จ่ายที่เกิดขึ้นกับการรับคณะ">
                                </div>

                                <table cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td width="50%">
                                            <div class="form-group">
                                                <label class="control-label">ราคา</label>
                                                <div class="input-group">
                                                    <input type="number" readonly class="form-control" id="damage_activity_item_sale" name="damage_activity_item_sale" value="0" aria-describedby="damage_activity_item_sale-addon" required>
                                                    <span class="input-group-addon" id="damage_activity_item_sale-addon">บาท</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td width="50%" style="padding-left: 10px;">
                                            <div class="form-group">
                                                <label class="control-label">ทุน</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="damage_activity_item_cost" name="damage_activity_item_cost" value="0" aria-describedby="damage_activity_item_cost-addon" required>
                                                    <span class="input-group-addon" id="damage_activity_item_cost-addon">บาท</span>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                                <div class="form-group">
                                    <label class="control-label">จำนวน</label>
                                    <div class="input-group" style="width: 50%;">
                                        <input type="number" class="form-control" id="damage_activity_item_quantity" name="damage_activity_item_quantity" min="1" value="1" aria-describedby="damage_activity_item_quantity-addon" value="1" required>
                                        <span class="input-group-addon" id="damage_activity_item_quantity-addon">หน่วย</span>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">ปิด</button>
                        <button id="submitDamageForm" type="button" class="btn btn-success"><i class="fa fa-save"></i> บันทึกรายการ</button>
                    </div>
                </div>
            </div>
        </div>

    @endif

    <script type="text/javascript">

        //Global variable
        @if(isset($party))
            /*Static load location add into parameter*/
            var data_locations = {{ json_encode($locations) }};
            //var data_can_facilities =
        @endif

        $(function () {

            /*Select Party*/
            $('#party_select').select2({
                placeholder: "กรุณาเลือกคณะ"
            });
            /*On Change Party Select View*/
            $('#party_select').on('change', function(){
                //use laroute to redirect
                window.location = $(this).val();
            });

            @if(isset($party))

            /*Load activities for budget plan param plan and date*/
            $('.budgeting_plan').on('click', function(){
                var party_id = {{ $party->id }};
                var plan = $(this).attr('plan');
                var day = $(this).attr('date');
                //load by click panel
                loadPanel(party_id, plan, day);
            });

            /*Submit form to Create or Update*/
            $('#submitExpenseForm').on('click', function(){
                $.ajax({
                    type: "POST",
                    url: "{{ URL::action('BudgetController@postCreateOrUpdateBudget') }}",
                    data: $('#formExpenseItem').serialize(),
                    success: function (data) {
                        if (data.status=='success')
                        {
                            $('#modalExpenseItem').modal('hide');

                            var p = $('#expense_plan').val();

                            loadPanel($('#expense_party').val(), p.toLowerCase(), $('#expense_date').val(), data.div);
                        }
                        else
                        {
                            $('#modalExpenseItem').modal('hide');

                            errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                        }
                    },
                    dataType: 'json'
                });
            });

            $('#submitDamageForm').on('click', function(){
                $.ajax({
                    type: "POST",
                    url: "{{ URL::action('BudgetController@postCreateOrUpdateDamage') }}",
                    data: $('#formDamageItem').serialize(),
                    success: function (data) {
                        if (data.status=='success')
                        {
                            $('#modalDamageItem').modal('hide');

                            var p = $('#damage_plan').val();

                            loadPanel($('#damage_party').val(), p.toLowerCase(), $('#damage_date').val(), data.div);
                        }
                        else
                        {
                            $('#modalDamageItem').modal('hide');

                            errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                        }
                    },
                    dataType: 'json'
                });
            });

            /*Keep Tab History*/
            // save the latest tab; use cookies if you like 'em better:
            // localStorage.setItem('lastTab', $(this).attr('href'));

            //*Tab ajax load data*/
            $('a[data-toggle="tab"][class="main_panel"]').on('shown.bs.tab', function (e) {

                var activeTab = e.target; // newly activated tab

                if($(activeTab).attr('aria-controls')=="summary_type")
                {
                    //if summarize tab init or reload calculate
                    //call ajax with serialize
                    calculateSummary();
                }
                else if($(activeTab).attr('aria-controls')=="total_summary")
                {
                    //call calculate total
                    calculateSummaryTotal();
                }
                else
                {
                    //load activities or task for create or edit budget.

                }
            });

            // go to the latest tab, if it exists:
            var lastTab = localStorage.getItem('lastTab');
            if (lastTab) {
                $('[href="' + lastTab + '"]').tab('show');
            }

            /*submit to create document is Quotation*/
            $('#submitCreateDocument').on('click', function(){
                /*Bootstrap Dialog ask before submit*/
                var bd = BootstrapDialog.show({
                    title: 'ยืนยันการสร้างเอกสารใหม่',
                    message: 'กรุณาเลือกภาษาที่สร้างเอกสาร',
                    buttons: [{
                        label: 'ภาษาไทย',
                        action: function(dialog) {
                            //close before
                            bd.close();
                            saveExportQuotation($('#party_budget_id').val(), 0);
                        }
                    },
                        {
                            label: 'English',
                            action: function(dialog) {
                                //close before
                                bd.close();
                                saveExportQuotation($('#party_budget_id').val(), 1);
                            }
                        },
                        {
                            icon: 'fa fa-ban',
                            label: 'ยกเลิก',
                            cssClass: 'btn-danger',
                            action: function(dialog) {
                                dialog.close();
                            }
                        }]
                });
            });
            @endif
        });

        @if(isset($party))

        /*DOM Ready*/
        $(document).ready(function(){
            /*Hide and show plan b, this is for งบแยกประเภท*/
            @if($count_plan_b==0)
            $('#summary_type a:last').hide();
            @endif
        });

        //load Information Panel by Plan and Date
        function loadPanel(party_id, plan, day, div)
        {
            //initial show loading panel before
            $('#data_' + day + '_' + plan).hide();
            $('#loading_' + day + '_' + plan).show();

            $.ajax({
                type: "GET",
                url: "{{ URL::action('BudgetController@getBudgetTaskByPlanAndDate') }}",
                data: {
                    'party_id' : party_id,
                    'plan' : plan,
                    'date' : day
                },
                dataType: 'json',
                success: function (data) {
                    //initial show loading panel before
                    $('#loading_' + day + '_' + plan).hide();
                    $('#data_' + day + '_' + plan).fadeIn();

                    //set count value
                    var count_task_accommodation = data.accommodations.length;
                    var number_task_accommodation = 1;
                    var count_task_activity = data.activities.length;
                    var number_task_activity = 1;
                    //set static main box id
                    var box_accommodation_id = '#accommodations_' + day + '_' + plan;
                    var box_activity_id = '#activities_' + day + '_' + plan;

                    //For Accommodation
                    if (count_task_accommodation==0)
                    {
                        $(box_accommodation_id).empty().append('<div class="alert alert-warning" role="alert"><i class="fa fa-exclamation-circle"></i> ยังไม่ได้ระบุที่พักในวันนี้กรุณาระบุสถานที่พักได้ใน <a href="{{ URL::to('coordinator/schedule/'.$party->id.'/view') }}">การจัดการกำหนดการ</a> </div>');
                    }
                    else
                    {
                        //before loop create clear old data before
                        $(box_accommodation_id).empty();
                        //loop for tasks accommodations
                        $.each(data.accommodations, function(index, item){

                            //set combo box locations id
                            //var combo_accommodation_location_id = 'combo_accommodations_' + day + '_' + plan + '_' + item.id;

                            //set html parameter to show
                            var accommodation_html = '';

                            //date_plan_task location id
                            accommodation_html += '<div id="task_budget_' + day + '_' + plan + '_' + item.id + '" class="task_budget" style="width:100%;">';

                            //check if budget is selected show checked detail
                            //else show no select information
                            $.each(item.task_locations, function(i, obj){
								
								var main_accommodation_location_div = 'location_task_budget_' + day + '_' + plan + '_' + item.id + '_' + obj.id;  
								
                                accommodation_html += '<div id="' + main_accommodation_location_div + '">';
                                accommodation_html += '<h5 id="accommodation_' + day + '_' + plan + '_' + obj.id + '">' + obj.location_name + '</h5>';
                                accommodation_html += '<input type="hidden" id="plan_' + day + '_' + plan + '_' + obj.id + '" value="' + plan.toUpperCase() + '">';
                                accommodation_html += '<input type="hidden" id="location_' + day + '_' + plan + '_' + obj.id + '" value="' + obj.location_name + '">';
                                accommodation_html += '<input type="hidden" id="task_location_' + day + '_' + plan + '_' + obj.id + '" value="' + obj.id + '">';
                                accommodation_html += '<input type="hidden" id="master_task_' + day + '_' + plan + '_' + obj.id + '" value="' + item.master_task_id + '">';
								//set for scrollTop
								accommodation_html += '<input type="hidden" id="scroll_' + day + '_' + plan + '_' + obj.id + '" value="' + main_accommodation_location_div + '">';

                                var used_items = obj.used_items;//รายได้ของเรา
                                var damage_items = obj.damage_items;//รายจ่ายของเรา

                                //tab แสดงรายได้และรายจ่ายของสถานที่พัก
                                //set tab id
                                var accommodation_budget_tab_id = day + '_' + plan + '_' + item.id + '_' + obj.id;
                                //มีแท็บรายได้และค่าใช้จ่าย
                                accommodation_html += '<ul class="nav nav-tabs" role="tablist">';
                                accommodation_html += '<li role="presentation" class="active"><a href="#expense_' + accommodation_budget_tab_id + '" aria-controls="expense_' + accommodation_budget_tab_id + '" role="tab" data-toggle="tab"><span class="text-success strong">รายได้</span></a></li>';
                                accommodation_html += '<li role="presentation"><a href="#damage_' + accommodation_budget_tab_id + '" aria-controls="damage_' + accommodation_budget_tab_id + '" role="tab" data-toggle="tab"><span class="text-danger strong">ค่าใช้จ่าย</span></a></li>';
                                accommodation_html += '</ul>';

                                accommodation_html += '<div class="tab-content">';
                                //tab รายได้ ของสถานที่พัก
                                accommodation_html += '<div role="tabpanel" class="tab-pane active" id="expense_' + accommodation_budget_tab_id + '">';

                                var accommodation_budget_tab_table_id = 'table_expense_accommodation_' + accommodation_budget_tab_id;

                                accommodation_html += '<div class="pull-right" style="margin:5px;">';
                                accommodation_html += '<a class="addExpenseAccommodation btn btn-sm btn-success" href="javascript:extendExpenseItem(\'create\',\'accommodations\',\'' + plan + '\',\'' + day + '\',\'' + obj.id + '\', ' + obj.location_id + ');" role="button"><i class="fa fa-plus"></i> เพิ่มค่าที่พักของ' + obj.location_name + '</a>';
                                accommodation_html += '</div><div class="clearfix"></div>';

                                if (used_items.count>0)
                                {
                                    accommodation_html += '<table id="' + accommodation_budget_tab_table_id + '" class="table table-bordered">';
                                    accommodation_html += '<thead>';
                                    accommodation_html += '<tr class="active">';
                                    accommodation_html += '<th class="col-sm-4">รายการ</th>';
                                    accommodation_html += '<th class="col-sm-2 aRight">ราคาขาย</th>';
                                    accommodation_html += '<th class="col-sm-2 aRight">ราคาทุน</th>';
                                    accommodation_html += '<th class="col-sm-1 aRight">จำนวน</th>';
                                    accommodation_html += '<th class="col-sm-1">หน่วย</th>';
                                    accommodation_html += '<th class="col-sm-2"></th>';
                                    accommodation_html += '</tr>';
                                    accommodation_html += '</thead>';

                                    accommodation_html += '<tbody>';
                                    $.each(used_items.accommodations, function(i, room){
                                        var row_accommodation_expense_id = room.id;

                                        accommodation_html += '<tr id="' + row_accommodation_expense_id + '">';
                                        accommodation_html += '<td class="col-sm-4">' + room.expense_name + '</td>';
                                        accommodation_html += '<td class="col-sm-2 aRight">' + room.sale + '</td>';
                                        accommodation_html += '<td class="col-sm-2 aRight">' + room.cost + '</td>';
                                        accommodation_html += '<td class="col-sm-1 aRight">' + room.qty + '</td>';
                                        accommodation_html += '<td class="col-sm-1">' + room.expense_unit + '</td>';
                                        accommodation_html += '<td class="col-sm-2">';
                                        accommodation_html += '<a class="btn btn-xs btn-default" href="javascript:extendExpenseItem(\'edit\',\'accommodations\',\'' + plan + '\',\'' + day + '\',\'' + obj.id + '\',' + obj.location_id + ',' + room.id + ');" role="button"><i class="fa fa-pencil"> แก้ไข</i></a>';
                                        accommodation_html += ' <a class="btn btn-xs btn-danger" href="javascript:deleteItem(' + room.id + ');" role="button"><i class="fa fa-trash-o"> ลบ</i></a>';
                                        accommodation_html += '</td>';
                                        accommodation_html += '</tr>';
                                    });
                                    accommodation_html += '</tbody>';
                                    accommodation_html += '</table>';
                                }
                                else
                                {
                                    accommodation_html += '<div class="alert alert-warning" role="alert"><i class="fa fa-exclamation-circle"></i> ไม่คิดรายได้จากสถานที่พักแห่งนี้</div>';
                                }

                                accommodation_html += '</div>';
                                //tab ค่าใช้จ่าย ของสถานที่พัก
                                accommodation_html += '<div role="tabpanel" class="tab-pane" id="damage_' + accommodation_budget_tab_id + '">';

                                accommodation_html += '<div class="pull-right" style="margin:5px;">';
                                accommodation_html += '<a class="addDamageAccommodation btn btn-sm btn-danger" href="javascript:extendDamageItem(\'S\',\'' + plan + '\',\'' + day + '\',\'' + obj.id + '\');" role="button"><i class="fa fa-plus"></i> เพิ่มค่าใช้จ่ายที่' + obj.location_name + '</a>';
                                accommodation_html += '</div><div class="clearfix"></div>';

                                if (damage_items.length>0)
                                {
                                    accommodation_html += '<table id="table_damage_accommodation_' + accommodation_budget_tab_id + '" class="table table-bordered">';
                                    accommodation_html += '<thead>';
                                    accommodation_html += '<tr class="active">';
                                    accommodation_html += '<th class="col-sm-4">รายการ</th>';
                                    accommodation_html += '<th class="col-sm-2 aRight">ราคาขาย</th>';
                                    accommodation_html += '<th class="col-sm-2 aRight">ราคาทุน</th>';
                                    accommodation_html += '<th class="col-sm-1 aRight">จำนวน</th>';
                                    accommodation_html += '<th class="col-sm-1">หน่วย</th>';
                                    accommodation_html += '<th class="col-sm-2"></th>';
                                    accommodation_html += '</tr>';
                                    accommodation_html += '</thead>';

                                    accommodation_html += '<tbody>';
                                    $.each(damage_items, function(i, room){
                                        var row_accommodation_damage_id = room.id;

                                        accommodation_html += '<tr id="' + row_accommodation_damage_id + '">';
                                        accommodation_html += '<td class="col-sm-4">' + room.damage_text + '</td>';
                                        accommodation_html += '<td class="col-sm-2 aRight">' + room.sale_price + '</td>';
                                        accommodation_html += '<td class="col-sm-2 aRight">' + room.cost_price + '</td>';
                                        accommodation_html += '<td class="col-sm-1 aRight">' + room.qty + '</td>';
                                        accommodation_html += '<td class="col-sm-1">หน่วย</td>';
                                        accommodation_html += '<td class="col-sm-2">';
                                        accommodation_html += '<a class="btn btn-xs btn-default" href="javascript:extendDamageItem(\'S\',\'' + plan + '\',\'' + day + '\',\'' + obj.id + '\',' + room.id + ');" role="button"><i class="fa fa-pencil"> แก้ไข</i></a>';
                                        accommodation_html += ' <a class="btn btn-xs btn-danger" href="javascript:deleteItem(' + room.id + ');" role="button"><i class="fa fa-trash-o"> ลบ</i></a>';
                                        accommodation_html += '</td>';
                                        accommodation_html += '</tr>';
                                    });
                                    accommodation_html += '</tbody>';
                                    accommodation_html += '</table>';
                                }
                                else
                                {
                                    accommodation_html += '<div class="alert alert-warning" role="alert"><i class="fa fa-exclamation-circle"></i> ไม่มีค่าใช้จ่ายจากสถานที่พักแห่งนี้</div>';
                                }

                                accommodation_html += '</div>';

                                accommodation_html += '</div>';

                                accommodation_html += '</div>';
                            });

                            //end box div
                            accommodation_html += '</div>';

                            //Finally append to create html object
                            $(box_accommodation_id).append(accommodation_html);

                            number_task_accommodation++;
                        });

                    }
                    //For Activities
                    if (count_task_activity>0)
                    {

                        //before loop create clear old data before
                        $(box_activity_id).empty();
                        //loop for tasks activities
                        $.each(data.activities, function(index, item){

                            //set html parameter to show
                            var activity_html = '';

                            //date_plan_task location id
                            activity_html += '<div id="task_budget_' + day + '_' + plan + '_' + item.id + '" class="task_budget" style="width:100%;">';
                            //กิจกรรม โชว์เฉพาะ title กิจกรรม
                            activity_html += '<div id="location_task_budget_' + day + '_' + plan + '_' + item.id + '">';
                            var activity_label = (item.title_th==null || item.title_th=='') ? item.title_en : item.title_th;
                            activity_html += '<h5 id="activity_' + day + '_' + plan + '_' + item.id + '"><span class="label label-default">' + item.time_start + '</span> ' + activity_label + '</h5>';//for activity show label

                            $.each(item.task_locations, function(i, obj){

                                var used_items = obj.used_items;//รายได้ของเรา
                                var damage_items = obj.damage_items;//รายจ่ายของเรา

                                //tab แสดงรายได้และรายจ่ายของในแต่ละกิจกรรม
                                //set tab id
                                var activity_budget_tab_id = day + '_' + plan + '_' + item.id + '_' + obj.id;

								//set id for ScrollTop
								var main_activity_location_div = 'location_task_budget_' + day + '_' + plan + '_' + item.id + '_' + obj.id;  
								
                                //show location selected
                                var activity_meal = convertDateTimeToMeal(item.start);
                                activity_html += '<input type="hidden" id="meal_' + day + '_' + plan + '_' + obj.id + '" value="' + activity_meal + '">';
                                activity_html += '<input type="hidden" id="location_' + day + '_' + plan + '_' + obj.id + '" value="' + obj.location_name + '">';
                                activity_html += '<input type="hidden" id="plan_' + day + '_' + plan + '_' + obj.id + '" value="' + plan.toUpperCase() + '">';
                                activity_html += '<input type="hidden" id="task_location_' + day + '_' + plan + '_' + obj.id + '" value="' + obj.id + '">';
                                activity_html += '<input type="hidden" id="master_task_' + day + '_' + plan + '_' + obj.id + '" value="' + item.master_task_id + '">';
								//set for scrollTop
								activity_html += '<input type="hidden" id="scroll_' + day + '_' + plan + '_' + obj.id + '" value="' + main_activity_location_div + '">';
								
                                activity_html += '<div id="' + main_activity_location_div + '" style="padding: 5px 0;"><span class="strong">สถานที่ :</span> '+obj.location_name+'</div> <input type="hidden" id="activity_text_' + day + '_' + plan + '_' + obj.id + '" value="' + activity_label + '">';
                                //มีแท็บรายได้และค่าใช้จ่าย
                                activity_html += '<ul class="nav nav-tabs" role="tablist">';
                                activity_html += '<li role="presentation" class="active"><a href="#expense_' + activity_budget_tab_id + '" aria-controls="expense_' + activity_budget_tab_id + '" role="tab" data-toggle="tab"><span class="text-success strong">รายได้</span></a></li>';
                                activity_html += '<li role="presentation"><a href="#damage_' + activity_budget_tab_id + '" aria-controls="damage_' + activity_budget_tab_id + '" role="tab" data-toggle="tab"><span class="text-danger strong">ค่าใช้จ่าย</span></a></li>';
                                activity_html += '</ul>';

                                activity_html += '<div class="tab-content">';
                                //tab รายได้ ของสถานที่พัก
                                activity_html += '<div role="tabpanel" class="tab-pane active" id="expense_' + activity_budget_tab_id + '">';

                                activity_html += '<div class="pull-right" style="margin:5px;">';
                                activity_html += '<div class="btn-group">';
                                activity_html += '<button type="button" class="btn btn-sm btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                                activity_html += '<i class="fa fa-plus"></i> เพิ่มรายได้ในกิจกรรมนี้ <span class="caret"></span>';
                                activity_html += '</button>';
                                activity_html += '<ul class="dropdown-menu dropdown-menu-right">';
                                @foreach($budget_types as $budget_type)
                                activity_html += '<li><a class="addExpenseItem" href="javascript:extendExpenseItem(\'create\',\'{{ $budget_type->name }}\',\'' + plan + '\',\'' + day + '\',\'' + obj.id + '\', ' + obj.location_id + ');">{{ $budget_type->name_th }}</a></li>';
                                @endforeach
                            activity_html += '</ul>';
                                activity_html += '</div>';
                                activity_html += '</div><div class="clearfix"></div>';

                                if (used_items.count>0)
                                {
                                    activity_html += '<table id="table_expense_facility_' + activity_budget_tab_id + '" class="table table-bordered">';
                                    activity_html += '<thead>';
                                    activity_html += '<tr class="active">';
                                    activity_html += '<th class="col-sm-4">รายการ</th>';
                                    activity_html += '<th class="col-sm-2 aRight">ราคาขาย</th>';
                                    activity_html += '<th class="col-sm-2 aRight">ราคาทุน</th>';
                                    activity_html += '<th class="col-sm-1 aRight">จำนวน</th>';
                                    activity_html += '<th class="col-sm-1">หน่วย</th>';
                                    activity_html += '<th class="col-sm-2"></th>';
                                    activity_html += '</tr>';
                                    activity_html += '</thead>';

                                    activity_html += '<tbody>';
                                    $.each(Object.keys(used_items), function(i, type){
                                        //check หากในแต่ละ type มี item อยู่ถึงจะให้แสดง
                                        if (used_items[type].length>0)
                                        {
                                            //create grouping header
                                            activity_html += '<tr id="" class="strong info">';
                                            activity_html += '<td colspan="12">' + convertTypeToThai(type) + '</td>';
                                            activity_html += '</tr>';
                                            $.each(used_items[type], function(i, facility){
                                                //row id
                                                var row_facility_expense_id = facility.id;
                                                var facility_unit = (facility.expense_unit==null) ? '' : facility.expense_unit;
                                                activity_html += '<tr id="' + row_facility_expense_id + '">';
                                                if (type=='cars' || type=='conferences' || type=='personnels')
                                                {
                                                    activity_html += '<td class="col-sm-4">' + facility.expense_name + ' <span class="label label-primary">'+facility.expense_rate_name+'</span></td>';
                                                }
                                                else if (type=='foods')
                                                {
                                                    activity_html += '<td class="col-sm-4">' + facility.expense_name + ' <span class="label label-default">'+facility.expense_food_meal_name+'</span></td>';
                                                }
                                                else
                                                {
                                                    activity_html += '<td class="col-sm-4">' + facility.expense_name + '</td>';
                                                }
                                                activity_html += '<td class="col-sm-2 aRight">' + facility.sale + '</td>';
                                                activity_html += '<td class="col-sm-2 aRight">' + facility.cost + '</td>';
                                                activity_html += '<td class="col-sm-1 aRight">' + facility.qty + '</td>';
                                                activity_html += '<td class="col-sm-1">' + facility_unit + '</td>';
                                                activity_html += '<td class="col-sm-2">';
                                                activity_html += '<a class="btn btn-xs btn-default" href="javascript:extendExpenseItem(\'edit\',\'' + type + '\',\'' + plan + '\',\'' + day + '\',\'' + obj.id + '\',' + obj.location_id + ',' + facility.id + ');" role="button"><i class="fa fa-pencil"> แก้ไข</i></a>';
                                                activity_html += ' <a class="btn btn-xs btn-danger" href="javascript:deleteItem(' + facility.id + ');" role="button"><i class="fa fa-trash-o"> ลบ</i></a>';
                                                activity_html += '</td>';
                                                activity_html += '</tr>';
                                            });
                                        }
                                    });
                                    activity_html += '</tbody>';
                                    activity_html += '</table>';
                                }
                                else
                                {
                                    activity_html += '<div class="alert alert-warning" role="alert"><i class="fa fa-exclamation-circle"></i> ไม่คิดรายได้ในกิจกรรมนี้</div>';
                                }

                                activity_html += '</div>';
                                //tab ค่าใช้จ่าย
                                activity_html += '<div role="tabpanel" class="tab-pane" id="damage_' + activity_budget_tab_id + '">';

                                activity_html += '<div class="pull-right" style="margin:5px;">';
                                activity_html += '<a class="addDamageItem btn btn-sm btn-danger" href="javascript:extendDamageItem(\'A\',\'' + plan + '\',\'' + day + '\',\'' + obj.id + '\');" role="button"><i class="fa fa-plus"></i> เพิ่มค่าใช้จ่ายในกิจกรรมนี้</a>';
                                activity_html += '</div><div class="clearfix"></div>';

                                if (damage_items.length>0)
                                {
                                    activity_html += '<table id="table_damage_facility_' + activity_budget_tab_id + '"  class="table table-bordered">';
                                    activity_html += '<thead>';
                                    activity_html += '<tr class="active">';
                                    activity_html += '<th class="col-sm-4">รายการ</th>';
                                    activity_html += '<th class="col-sm-2 aRight">ราคาขาย</th>';
                                    activity_html += '<th class="col-sm-2 aRight">ราคาทุน</th>';
                                    activity_html += '<th class="col-sm-1 aRight">จำนวน</th>';
                                    activity_html += '<th class="col-sm-1">หน่วย</th>';
                                    activity_html += '<th class="col-sm-2"></th>';
                                    activity_html += '</tr>';
                                    activity_html += '</thead>';

                                    activity_html += '<tbody>';
                                    $.each(damage_items, function(i, facility){
                                        var row_facility_damage_id = facility.id;

                                        activity_html += '<tr id="' + row_facility_damage_id + '">';
                                        activity_html += '<td class="col-sm-4">' + facility.damage_text + '</td>';
                                        activity_html += '<td class="col-sm-2 aRight">' + facility.sale_price + '</td>';
                                        activity_html += '<td class="col-sm-2 aRight">' + facility.cost_price + '</td>';
                                        activity_html += '<td class="col-sm-1 aRight">' + facility.qty + '</td>';
                                        activity_html += '<td class="col-sm-1">หน่วย</td>';
                                        activity_html += '<td class="col-sm-2">';
                                        activity_html += '<a class="btn btn-xs btn-default" href="javascript:extendDamageItem(\'A\',\'' + plan + '\',\'' + day + '\',\'' + obj.id + '\',' + facility.id + ');" role="button"><i class="fa fa-pencil"> แก้ไข</i></a>';
                                        activity_html += ' <a class="btn btn-xs btn-danger" href="javascript:deleteItem(' + facility.id + ');" role="button"><i class="fa fa-trash-o"> ลบ</i></a>';
                                        activity_html += '</td>';
                                        activity_html += '</tr>';
                                    });
                                    activity_html += '</tbody>';
                                    activity_html += '</table>';
                                }
                                else
                                {
                                    activity_html += '<div class="alert alert-warning" role="alert"><i class="fa fa-exclamation-circle"></i> ไม่มีค่าใช้จ่ายในกิจกรรมนี้</div>';
                                }

                                activity_html += '</div>';

                                activity_html += '</div>';
                            });
                            activity_html += '</div>';

                            activity_html += '</div>';

                            activity_html += '<hr class="end-activity"/>';

                            //Finally append to create html object
                            $(box_activity_id).append(activity_html);

                            number_task_activity++;
                        });

                    }
                    //end of load ajax
					
					//load div id data is defined
					if (typeof div != 'undefined')
					{
						$('html, body').animate({
							scrollTop: $('#'+div).offset().top
						}, 1000);
					}
                }
            });
        }

        //create or update : expense accommodation and other facility function
        //parameter type, table_id
        function extendExpenseItem(action, type, plan, day, location, location_id, budget_detail_id)
        {
            //check this is create new or update old
            var party_id = {{ $party->id }};
            var create_new = false;
            var modal_type_title = '';
            //console.log(budget_detail_id);
            if (action=='create')
            {
                create_new = true;
                modal_type_title = 'เพิ่ม';
                $('#expense_new').val(true);
                $('#expense_budget_id').val(0);
				//trigger change to init rate
                /*setTimeout(function(){
					$("#expense_facility_rates").val($("#expense_facility_rates option:first").val()).change();
                }, 1000);*/
            }
            else
            {
                modal_type_title = 'ปรับปรุง';
                $('#expense_new').val(false);
                $('#expense_budget_id').val(budget_detail_id);
                //call function load and set edit หน่วงเวลา 0.5 วินาที
                setTimeout(function(){
                    loadAndSetSelectedFacility(type, plan, day, location, budget_detail_id);
                }, 510);
            }

            var party_name = '{{ $party->customer_code.' '.$party->name.' ('.$party->people_quantity.' คน)' }}';
            //set static show
            $('#expense_day').html($('#date_' + day + '_' + plan).html());
            $('#expense_task_location').val($('#task_location_' + day + '_' + plan + '_' + location).val());
            $('#expense_plan').val($('#plan_' + day + '_' + plan + '_' + location).val());
            $('#expense_date').val(day);
            $('#expense_master_task').val($('#master_task_' + day + '_' + plan + '_' + location).val());
			$('#expense_main_div').val($('#scroll_' + day + '_' + plan + '_' + location).val());
            //check type before add item
            if (type=='accommodations')
            {
                //set title
                $('#modalExpenseItemLabel').html(modal_type_title+'<span class="text-success">ค่าที่พัก</span>ของ '+party_name);
                //set hidden
                $('#expense_type_selected').val('accommodations');
                //open modal inline
                $('#modalExpenseItem').modal('show');
                //set activity show where work
                $('#expense_activity').html('สถานที่พัก');
                $('#expense_location').html($('#accommodation_' + day + '_' + plan + '_' + location).html());
                //load accommodation data with hotel or accommodation location fill in combo
                var html = '';
                html += '<div class="form-group">';
                html += '<label for="expense_accommodation_item">รายการห้องพัก *</label>';
                html += '<select required="required" style="width:100%;" class="form-control" id="expense_accommodation_item" name="expense_accommodation_item" onchange="selectFacilityExpense();"></select>';
                html += '</div>';
                html += '<table cellpadding="0" cellspacing="0" border="0">';
                html += '<tr>';
                html += '<td width="50%">';
                html += '<div class="form-group">';
                html += '<label>ราคาขาย</label>';
                html += '<div class="input-group">';
                html += '<input readonly type="number" class="form-control" id="expense_accommodation_item_sale" name="expense_accommodation_item_sale">';
                html += '<div class="input-group-addon">บาท</div>';
                html += '</div>';
                html += '</div>';
                html += '</td>';
                html += '<td width="50%" style="padding-left: 10px;">';
                html += '<div class="form-group">';
                html += '<label>ราคาทุน</label>';
                html += '<div class="input-group">';
                html += '<input readonly type="number" class="form-control" id="expense_accommodation_item_cost" name="expense_accommodation_item_cost">';
                html += '<div class="input-group-addon">บาท</div>';
                html += '</div>';
                html += '</div>';
                html += '</td>';
                html += '</tr>';
                html += '</table>';
                html += '<div class="form-group">';
                html += '<label for="expense_accommodation_quantity">จำนวน *</label>';
                html += '<div class="input-group" style="width: 50%;">';
                html += '<input required="required" type="number" class="form-control" name="expense_accommodation_item_quantity" id="expense_accommodation_item_quantity" value="1" placeholder="จำนวน">';
                html += '<div class="input-group-addon"><span id="expense_accommodation_item_unit">ห้อง</span></div>';
                html += '</div>';
                html += '</div>';

                $('#expense_information').empty().append(html);
                //set combo as select2
                $('#expense_accommodation_item').select2({
                    'placeholder' : "กรุณาเลือกห้องพัก"
                });
                //load item to create
                setTimeout(function(){
                    //load to add facility
                    loadFacility('expense_accommodation_item', 'accommodations', location_id, null);
                }, 200);
            }
            else
            {
                //for cars,conferences,foods,personnels,tools,special_events
                $('#expense_facility_item_quantity').prop('readOnly', false);//default not lock
                //change modal text
                var modal_title = '';
                var facility_name_text = '';
                var have_rates;
                switch(type)
                {
                    case 'cars' :
                        modal_title = modal_type_title+'<span class="text-success">ค่ายานพาหนะ</span>ของ ' + party_name;
                        facility_name_text = 'รายการรถยนต์ *';
                        have_rates = true;
                        break;
                    case 'conferences' :
                        modal_title = modal_type_title+'<span class="text-success">ค่าห้องประชุม</span>ของ ' + party_name;
                        facility_name_text = 'รายการห้องประชุม *';
                        have_rates = true;
                        break;
                    case 'personnels' :
                        modal_title = modal_type_title+'<span class="text-success">ค่าวิทยากรและบุคลากร</span>ของ ' + party_name;
                        facility_name_text = 'รายการวิทยากรและบุคลากร *';
                        have_rates = true;
                        break;
                    case 'foods' :
                        modal_title = modal_type_title+'<span class="text-success">ค่าอาหาร</span>ของ ' + party_name;
                        facility_name_text = 'รายการอาหาร *';
                        have_rates = false;
                        break;
                    case 'tools' :
                        modal_title = modal_type_title+'<span class="text-success">ค่าวัสดุและอุปกรณ์ประกอบการเรียนรู้</span>ของ ' + party_name;
                        facility_name_text = 'รายการวัสดุและอุปกรณ์ *';
                        have_rates = false;
                        break;
                    case 'special_events' :
                        modal_title = modal_type_title+'<span class="text-success">ค่ากิจกรรมพิเศษ</span>ของ ' + party_name;
                        facility_name_text = 'รายการกิจกรรมพิเศษ *';
                        have_rates = false;
                        break;
                    case 'other' :
                        modal_title = modal_type_title+'<span class="text-success">ค่าอื่นๆ</span>ของ ' + party_name;
                        facility_name_text = 'รายการอื่นๆ *';
                        have_rates = false;
                        break;
                }
                //set title
                $('#modalExpenseItemLabel').html(modal_title);
                //set hidden
                $('#expense_type_selected').val(type);
                //open modal inline
                $('#modalExpenseItem').modal('show');
                //set activity show where work
                $('#expense_activity').html($('#activity_text_' + day + '_' + plan + '_' + location).val());
                $('#expense_location').html($('#location_' + day + '_' + plan + '_' + location).val());
                //load facility data fill in combo
                var html = '';
                if (type=='cars')
                {
                    html += '<div class="form-group">';
                    html += '<label for="expense_facilitator">ผู้ให้บริการ *</label>';
                    html += '<select style="width:100%;" class="form-control" id="expense_facilitator" name="expense_facilitator" onchange="loadFacility(\'expense_facility_item\',\'cars\', null, \'#expense_facilitator\');">';
                        @foreach($car_facilitators as $car_facilitator)
                            html += '<option value="{{ $car_facilitator->id }}">{{ $car_facilitator->name }}</option>';
                        @endforeach
                    html += '</select>';
                    html += '</div>';
                    html += '<div class="form-group">';
                    html += '<label for="expense_facility_item">' + facility_name_text + '</label>';
                    html += '<select required="required" style="width:100%;" class="form-control" id="expense_facility_item" name="expense_facility_item" onchange="selectFacilityRates();"></select>';
                    html += '</div>';

                    setTimeout(function(){
                        //load to add facility
                        loadFacility('expense_facility_item', 'cars', null, '#expense_facilitator');
                    }, 500);
                }
                else if (type=='personnels' || type=='conferences')
                {
                    html += '<div class="form-group">';
                    html += '<label for="expense_facility_item">' + facility_name_text + '</label>';
                    html += '<select required="required" style="width:100%;" class="form-control" id="expense_facility_item" name="expense_facility_item" onchange="selectFacilityRates();"></select>';
                    html += '</div>';

                    setTimeout(function(){
                        if (type=='conferences')
                        {
                            $('#expense_facility_item_quantity').prop('readOnly', true);
                        }
                        //load to add facility
                        loadFacility('expense_facility_item', type, location_id, null);
                    }, 500);
                }
                else
                {
                    //for foods only insert มื้อ
                    if (type=='foods')
                    {
                        var this_is_meal = $('#meal_'+ day + '_' + plan + '_' + location).val();
                        html += '<div class="form-group">';
                        html += '<label for="expense_food_meal">ระบุมื้อ *</label>';
                        html += '<select required="required" style="width:100%;" class="form-control" id="expense_food_meal" name="expense_food_meal">';
                        html += '<option value="breakfast">มื้อเช้า</option>';
                        html += '<option value="lunch">มื้อเที่ยง</option>';
                        html += '<option value="dinner">มื้อเย็น</option>';
                        html += '<option value="break_morning">เบรกเช้า</option>';
                        html += '<option value="break_afternoon">เบรกบ่าย</option>';
                        html += '<option value="night">มื้อดึก</option>';
                        html += '</select>';
                        html += '</div>';

                        //set timeout to change
                        setTimeout(function(){
                            $('#expense_food_meal').val(this_is_meal).change();
                        }, 100);
                    }

                    //have no rates => foods, tools, special events, other
                    if (type=='other')
                    {
                        html += '<div class="form-group">';
                        html += '<label for="expense_facility_item">' + facility_name_text + '</label>';
                        html += '<input type="text" required="required" class="form-control" id="expense_facility_item" name="expense_facility_item">';
                        html += '</div>';
                    }
                    else
                    {
                        html += '<div class="form-group">';
                        html += '<label for="expense_facility_item">' + facility_name_text + '</label>';
                        html += '<select required="required" style="width:100%;" class="form-control" id="expense_facility_item" name="expense_facility_item" onchange="selectFacilityExpense();"></select>';
                        html += '</div>';

                        setTimeout(function(){
                            //load to add facility
                            loadFacility('expense_facility_item', type, location_id, null);
                        }, 500);
                    }
                }

                //setting html to show
                if (have_rates==true)
                {
                    html += '<div class="form-group">';
                    html += '<label for="expense_facility_rates">เรทราคา *</label>';
                    html += '<select required style="width:100%;" class="form-control" id="expense_facility_rates" name="expense_facility_rates" onchange="selectRateExpense();"></select>';
                    html += '</div>';
                }

                html += '<table cellpadding="0" cellspacing="0" border="0">';
                html += '<tr>';
                html += '<td width="50%">';
                html += '<div class="form-group">';
                html += '<label>ราคาขาย</label>';
                html += '<div class="input-group">';
                html += '<input readonly required type="number" class="form-control" id="expense_facility_item_sale" name="expense_facility_item_sale">';
                html += '<div class="input-group-addon">บาท</div>';
                html += '</div>';
                html += '</div>';
                html += '</td>';
                html += '<td width="50%" style="padding-left: 10px;">';
                html += '<div class="form-group">';
                html += '<label>ราคาทุน</label>';
                html += '<div class="input-group">';
                html += '<input readonly required type="number" class="form-control" id="expense_facility_item_cost" name="expense_facility_item_cost">';
                html += '<div class="input-group-addon">บาท</div>';
                html += '</div>';
                html += '</div>';
                html += '</td>';
                html += '</tr>';
                html += '</table>';
                html += '<div class="form-group">';
                html += '<label for="expense_facility_item_quantity">จำนวน *</label>';
                html += '<div class="input-group" style="width: 50%;">';
                html += '<input required type="number" class="form-control" name="expense_facility_item_quantity" id="expense_facility_item_quantity" value="1" placeholder="จำนวน">';
                html += '<div class="input-group-addon"><span id="expense_facility_item_unit">หน่วย</span></div>';
                html += '</div>';
                html += '</div>';

                if (type=='other')
                {
                    setTimeout(function(){
                        $('#expense_facility_item_sale').prop('readOnly', false);
                        $('#expense_facility_item_cost').prop('readOnly', false);
                    }, 100);
                }
                else
                {
                    setTimeout(function(){
                        //set combo as select2
                        $('#expense_facility_item').select2({
                            'placeholder' : "กรุณาเลือกรายการ"
                        });
                        $('#expense_facility_item_sale').prop('readOnly', true);
                        $('#expense_facility_item_cost').prop('readOnly', true);
                    }, 100);
                }

                //write html to div
                $('#expense_information').empty().append(html);
            }
        }

        //function for create or update damage items
        function extendDamageItem(type, plan, day, location, budget_detail_id)
        {
            //check this is create new or update old
            var party_id = {{ $party->id }};
            var create_new = false;
            var modal_type_title = '';

            if (budget_detail_id===undefined)
            {
                create_new = true;
                modal_type_title = 'เพิ่ม';
                $('#damage_new').val(true);
                $('#damage_budget_id').val(0);
            }
            else
            {
                modal_type_title = 'ปรับปรุง';
                $('#damage_new').val(false);
                $('#damage_budget_id').val(budget_detail_id);
                //call function load and set edit หน่วงเวลา 0.5 วินาที
                setTimeout(function(){
                    loadAndSetSelectedDamage(plan, day, location, budget_detail_id);
                }, 510);
            }

            var party_name = '{{ $party->customer_code.' '.$party->name.' ('.$party->people_quantity.' คน)' }}';
            //set static show
            $('#damage_day').html($('#date_' + day + '_' + plan).html());
            $('#damage_task_location').val($('#task_location_' + day + '_' + plan + '_' + location).val());
            $('#damage_plan').val($('#plan_' + day + '_' + plan + '_' + location).val());
            $('#damage_date').val(day);
            $('#damage_master_task').val($('#master_task_' + day + '_' + plan + '_' + location).val());
			//set for scrollTop
			$('#damage_main_div').val($('#scroll_' + day + '_' + plan + '_' + location).val());

            if (type=='S')
            {
                $('#damage_activity').html('สถานที่พัก');
                $('#damage_location').html($('#accommodation_' + day + '_' + plan + '_' + location).html());
            }
            else
            {
                $('#damage_activity').html($('#activity_text_' + day + '_' + plan + '_' + location).val());
                $('#damage_location').html($('#location_' + day + '_' + plan + '_' + location).val());
            }
            //set title
            $('#modalDamageItemLabel').html(modal_type_title+'<span class="text-danger">ค่าใช้จ่าย</span> '+party_name);

            //open modal inline
            $('#modalDamageItem').modal('show');
        }

        //delete Expense item
        function deleteItem(budget_detail_id)
        {
            if (confirm("ท่านต้องการลบรายการนี้หรือไม่ ?"))
            {
                $.ajax({
                    type: "POST",
                    url: "{{ URL::action('BudgetController@postDeleteBudgetAndDamage') }}",
                    data:
                    {
                        '_token' : $('input[name=_token]').val(),
                        'id' : budget_detail_id
                    },
                    success: function (data) {
                        if (data.status=='success')
                        {
                            successAlert('ลบสำเร็จ !', data.msg);
                            $('tr[id="'+budget_detail_id+'"]').empty();
                        }
                        else
                        {
                            errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                        }
                    },
                    dataType: 'json'
                });
            }
            else
            {
                return false;
            }
        }

        //Load for edit
        function loadAndSetSelectedFacility(type, plan, day, location, budget_detail_id)
        {
            //set busy load indicator
            $('#expense_information').hide();
            $('#expense_information_loading').show();

            //load data to set edit item;
            $.ajax({
                url: "{{ URL::action('BudgetController@getSelectedFacilityById') }}",
                data:
                {
                    'budget_detail_id' : budget_detail_id
                },
                dataType: 'json',
                success: function (data)
                {
                    if (data.status=='error')
                    {
                        errorAlert('โหลดรายการไม่สำเร็จ !', data.msg);
                        $('#modalExpenseItem').modal('show');
                    }
                    else
                    {
                        //delay time to show 1 วินาที
                        setTimeout(function(){
                            $('#expense_information').show();
                            $('#expense_information_loading').hide();
                        }, 1000);
                        //set data
                        var data = data.data;
                        //set old data for edit
                        if (type=='accommodations')
                        {
                            setTimeout(function(){
                                $('#expense_accommodation_item').val(data.expense_id).change();
                                $('#expense_accommodation_item_sale').val((data.sale_price==0) ? data.item.sale_price : data.sale_price);
                                $('#expense_accommodation_item_cost').val((data.cost_price==0) ? data.item.cost_price : data.cost_price);
                                $('#expense_accommodation_item_unit').val(data.item.unit);
                                $('#expense_accommodation_item_quantity').val(data.qty);
                            }, 100);
                        }
                        else if (type=='cars')
                        {
                            //console.log(data);
                            setTimeout(function(){
                                $('#expense_facilitator').val(data.item.facilitator.id).change();
                            }, 200);
                            setTimeout(function(){
                                $('#expense_facility_item').val(data.expense_id).change();
                                $('#expense_facility_item_unit').val(data.item.unit);
                                $('#expense_facility_item_quantity').val(data.qty);
                                //test or fix when id is disappear
                                $('#expense_item_in_budget').val(data.expense_id);
                                //console.log($('#expense_facility_item').val());
                            }, 250);
                            setTimeout(function(){
                                //console.log(data.expense_rate);
                                //$('select[id="expense_facility_rates"]>option[value='+data.expense_rate+']').attr('selected', true);
                                $('#expense_facility_rates').val(data.expense_rate).change();
                            }, 800);
                            setTimeout(function(){
                                $('#expense_facility_item_sale').val((data.sale_price==0) ? data.item.sale_price : data.sale_price);
                                $('#expense_facility_item_cost').val((data.cost_price==0) ? data.item.cost_price : data.cost_price);
                            }, 850);
                        }
                        else
                        {
                            //case มีเรทหรือราคาแฟลทๆ
                            if(type=='conferences' || type=='personnels')
                            {
                                //console.log(data);
                                setTimeout(function(){
                                    $('#expense_facility_item').val(data.expense_id).change();
                                    $('#expense_facility_item_unit').val(data.item.unit);
                                    $('#expense_facility_item_quantity').val(data.qty);
                                    //test or fix when id is disappear
                                    $('#expense_item_in_budget').val(data.expense_id);
                                }, 200);
                                setTimeout(function(){
                                    $('#expense_facility_rates').val(data.expense_rate).change();
                                }, 800);
                                setTimeout(function(){
                                    //$('#expense_facility_item_sale').val((data.sale_price==0) ? data.item.sale_price : data.sale_price);
                                    //$('#expense_facility_item_cost').val((data.cost_price==0) ? data.item.cost_price : data.cost_price);
                                    $('#expense_facility_item_sale').val(data.sale_price);
                                    $('#expense_facility_item_cost').val(data.cost_price);
                                }, 850);
                            }
                            else
                            {
                                setTimeout(function(){
                                    $('#expense_facility_item').val(data.expense_id).change();
                                }, 200);
                                setTimeout(function(){
                                    $('#expense_facility_item_sale').val((data.sale_price==0) ? data.item.sale_price : data.sale_price);
                                    $('#expense_facility_item_cost').val((data.cost_price==0) ? data.item.cost_price : data.cost_price);
                                    $('#expense_facility_item_unit').val(data.item.unit);
                                    $('#expense_facility_item_quantity').val(data.qty);
                                }, 500);
                            }
                        }
                    }
                }
            });
        }

        //load damage ค่าเสียหาย ค่าใช้จ่ายมา edit
        function loadAndSetSelectedDamage(plan, day, location, budget_detail_id)
        {
            //load data to set edit item;
            $.ajax({
                url: "{{ URL::action('BudgetController@getSelectedDamageById') }}",
                data:
                {
                    'budget_detail_id' : budget_detail_id
                },
                dataType: 'json',
                success: function (data)
                {
                    if (data.status=='error')
                    {
                        errorAlert('ปรับปรุงรายการไม่สำเร็จ !', data.msg);
                        setTimeout(function(){
                            //reload
                            //load by click panel
                            loadPanel(party_id, plan, day);
                        }, 1000);
                    }
                    else
                    {
                        var data = data.data;
                        //set old data for edit
                        $('#damage_activity_item').val(data.damage_text).change();
                        $('#damage_activity_item_sale').val(data.sale_price);
                        $('#damage_activity_item_cost').val(data.cost_price);
                        $('#damage_activity_item_quantity').val(data.qty);
                    }
                }
            });
        }

        //Load facilities function
        /*โหลดแบบมีเงื่อนไข เช่นเรียกข้อมูลห้องของสถานที่นั้นๆ หรือจะเอาข้อมูลทั้งหมดมาใช้*/
        function loadFacility(combo_box, facility_type, location_id, facilitator_control_id)
        {
            $.ajax({
                url: "{{ URL::action('BudgetController@getFacilityByCondition') }}",
                data: {
                    'facility_type' : facility_type,
                    'location_id' : location_id,
                    'facilitator_id' : $(facilitator_control_id).val()
                },
                success: function (data)
                {
                    if ($('#' + combo_box).empty())
                    {
                        $('#' + combo_box).append($("<option selected>").val("").text("กรุณาเลือก"));
                        $(data).each(function(i, obj) {
                            $('#' + combo_box).append($("<option>").val(obj.id).text(obj.text));
                        });
                    }
                },
                dataType: 'json'
            });
        }

        /*Load individual data to show value*/
        //for flat rate
        function loadFacilityInfoById(type, id)
        {
            $.ajax({
                url: "{{ URL::action('BudgetController@getFacilityInfoById') }}",
                data: {
                    'facility_type' : type,
                    'facility_id' : id
                },
                success: function (data)
                {
                    //fill data example sale,price in input to show and keep
                    if (type=='accommodations')
                    {
                        $('#expense_accommodation_item_sale').val(data.sale_price);
                        $('#expense_accommodation_item_cost').val(data.cost_price);
                        $('#expense_accommodation_item_unit').html(data.unit);
                    }
                    else
                    {
                        $('#expense_facility_item_sale').val(data.sale_price);
                        $('#expense_facility_item_cost').val(data.cost_price);
                        $('#expense_facility_item_unit').html(data.unit);
                    }
                },
                dataType: 'json'
            });
        }
        //for many rates
        function loadFacilityRatesById(type, id)
        {
            $.ajax({
                url: "{{ URL::action('BudgetController@getFacilityInfoById') }}",
                data: {
                    'facility_type' : type,
                    'facility_id' : id
                },
                success: function (data)
                {
                    //set unit
                    $('#expense_facility_item_unit').html(data.unit);
                    //fill rates in select combo
                    if ($('#expense_facility_rates').empty())
                    {
                        if (data.rates.length>0)
                        {
                            var r = 1;
                            //$('#expense_facility_rates').append($('<option>').val('').text('กรุณาเลือกเรท'));
                            $(data.rates).each(function(i, obj) {
                                var op = (r==1) ? "<option selected>" : "<option>";
                                $('#expense_facility_rates').append($(op).val(obj.id).text(obj.name).attr('sale', obj.sale_price).attr('cost', obj.cost_price));
                                r++;
                            });
                            //trigger change
                            setTimeout(function(){
                                $('#expense_facility_rates').trigger('change');
                            }, 600);
                        }
                        else
                        {
                            //หากไม่มีราคาจะเซ็ทราคาให้เป็นศูนย์
                            $('#expense_facility_item_sale').val('');
                            $('#expense_facility_item_cost').val('');
                            $('#expense_facility_item_quantity').val(1);
                        }
                    }
                },
                dataType: 'json'
            });
        }

        /*ในขณะที่มีการ select facility จะมีการโหลด Data และราคาลงใน input box เฉพาะรายการที่ไม่มีเรทราคา*/
        function selectFacilityExpense()
        {
            //for accommodations, foods, tools, special events
            var select_type = $('#expense_type_selected').val();
            var select_id = (select_type=='accommodations') ? $('#expense_accommodation_item').val() : $('#expense_facility_item').val();
            //load price and unit fill in input
            loadFacilityInfoById(select_type, select_id);
        }

        /*ในขณะที่มีการ select facility จะดึงเรทราคาออกมาให้เลือก เช่น cars, personnels, conferrences or location*/
        function selectFacilityRates()
        {
            var select_type = $('#expense_type_selected').val();
            var select_id = (select_type=='accommodations') ? $('#expense_accommodation_item').val() : $('#expense_facility_item').val();
            //load to fill rates
            loadFacilityRatesById(select_type, select_id);
            //also fill in hidden to save lost value
            $('#expense_item_in_budget').val(select_id);
        }

        /*set rate price of facility*/
        function selectRateExpense()
        {
            //for cars, conferences, personnel
            var select_rate = $('#expense_facility_rates option:selected');
            //set price sale cost unit
            $('#expense_facility_item_sale').val(select_rate.attr('sale'));
            $('#expense_facility_item_cost').val(select_rate.attr('cost'));
        }

        //Calculate function********************8
        /**Calculate For Party*/
        function calculateSummary()
        {
            //set busy load indicator
            $('#summary_type_data').hide();
            $('#loading_summary_type').empty().append('<div class="alert alert-warning" role="alert"><i class="fa fa-spinner fa-spin fa-2x"></i> กำลังประมวลผลข้อมูลกรุณารอสักครู่</div>');

            //send data by ajax to calculate
            $.ajax({
                type: "POST",
                url: "{{ URL::action('BudgetController@getTypeCalculate') }}",
                data: {
                    '_token' : $("input[name=_token]").val(),
                    'party_id' : $('#party_budget_id').val()
                },
                dataType: 'json',
                success : function(data){
                    $('#loading_summary_type').empty();
                    if (data.status=='success')
                    {
                        $('#summary_type_data').show();

                        var data = data.data;

                        //keep data in store
                        $('#budgeting_data').data(data);

                        //clear all html
                        $('#summary_type_a').empty();
                        $('#summary_type_b').empty();
                        $('#damage_type_a').empty();
                        $('#damage_type_b').empty();

                        //set data json
                        var accommodations = data.accommodations;
                        var foods = data.foods;
                        var cars = data.cars;
                        var personnels = data.personnels;
                        var conferences = data.conferences;
                        var location_facilities = data.location_facilities;
                        var tools = data.tools;
                        var special_events = data.special_events;
                        var other = data.other;

                        //add budget a
                        if (data.num_budget_a==0)
                        {
                            $('#summary_type_a').append('<div class="clearfix"></div><div class="showbox alert alert-warning" role="alert">แผน A ยังไม่ได้ทำการกรอกรายได้</div>');
                        }
                        else
                        {
                            //check accommodation and summary
                            var table_a_accommodations = getHtmlSummaryTable(accommodations['A'], 'A', 'accommodations');
                            $('#summary_type_a').append(table_a_accommodations);
                            //check food and summary
                            var table_a_foods = getHtmlSummaryTable(foods['A'], 'A', 'foods');
                            $('#summary_type_a').append(table_a_foods);
                            //check car and summary
                            var table_a_cars = getHtmlSummaryTable(cars['A'], 'A', 'cars');
                            $('#summary_type_a').append(table_a_cars);
                            //check personnels and summary
                            var table_a_personnels = getHtmlSummaryTable(personnels['A'], 'A', 'personnels');
                            $('#summary_type_a').append(table_a_personnels);
                            //check conferences and summary
                            var table_a_conferences = getHtmlSummaryTable(conferences['A'], 'A', 'conferences');
                            $('#summary_type_a').append(table_a_conferences);
                            //check location facilities and summary
                            var table_a_location_facilities = getHtmlSummaryTable(location_facilities['A'], 'A', 'location_facilities');
                            $('#summary_type_a').append(table_a_location_facilities);
                            //check tools and summary
                            var table_a_tools = getHtmlSummaryTable(tools['A'], 'A', 'tools');
                            $('#summary_type_a').append(table_a_tools);
                            //check special events and summary
                            var table_a_special_events = getHtmlSummaryTable(special_events['A'], 'A', 'special_events');
                            $('#summary_type_a').append(table_a_special_events);
                            //check tools and summary
                            var table_a_other = getHtmlSummaryTable(other['A'], 'A', 'other');
                            $('#summary_type_a').append(table_a_other);
                        }
                        //add budget b
                        if (data.num_budget_b==0)
                        {
                            $('#summary_type_b').append('<div class="clearfix"></div><div class="showbox alert alert-warning" role="alert">แผน B ยังไม่ได้ทำการกรอกรายได้</div>');
                        }
                        else
                        {
                            //check accommodation and summary
                            var table_b_accommodations = getHtmlSummaryTable(accommodations['B'], 'B', 'accommodations');
                            $('#summary_type_b').append(table_b_accommodations);
                            //check food and summary
                            var table_b_foods = getHtmlSummaryTable(foods['B'], 'B', 'foods');
                            $('#summary_type_b').append(table_b_foods);
                            //check car and summary
                            var table_b_cars = getHtmlSummaryTable(cars['B'], 'B', 'cars');
                            $('#summary_type_b').append(table_b_cars);
                            //check personnels and summary
                            var table_b_personnels = getHtmlSummaryTable(personnels['B'], 'B', 'personnels');
                            $('#summary_type_b').append(table_b_personnels);
                            //check conferences and summary
                            var table_b_conferences = getHtmlSummaryTable(conferences['B'], 'B', 'conferences');
                            $('#summary_type_b').append(table_b_conferences);
                            //check location facilities and summary
                            var table_b_location_facilities = getHtmlSummaryTable(location_facilities['B'], 'B', 'location_facilities');
                            $('#summary_type_b').append(table_b_location_facilities);
                            //check tools and summary
                            var table_b_tools = getHtmlSummaryTable(tools['B'], 'B', 'tools');
                            $('#summary_type_b').append(table_b_tools);
                            //check special events and summary
                            var table_b_special_events = getHtmlSummaryTable(special_events['B'], 'B', 'special_events');
                            $('#summary_type_b').append(table_b_special_events);
                            //check tools and summary
                            var table_b_other = getHtmlSummaryTable(other['B'], 'B', 'other');
                            $('#summary_type_b').append(table_b_other);
                        }

                        var damages = data.damages;
                        //add damage a
                        if (data.num_damage_a==0)
                        {
                            $('#damage_type_a').append('<div class="clearfix"></div><div class="showbox alert alert-warning" role="alert">แผน A ไม่มีค่าเสียหายหรือต้นทุนเพิ่ม</div>');
                        }
                        else
                        {
                            var table_a_damages = getHtmlSummaryTable(damages['A'], 'A', 'damages');
                            $('#damage_type_a').append(table_a_damages);
                        }

                        //add damage b
                        if (data.num_damage_b==0)
                        {
                            $('#damage_type_b').append('<div class="clearfix"></div><div class="showbox alert alert-warning" role="alert">แผน B ไม่มีค่าเสียหายหรือต้นทุนเพิ่ม</div>');
                        }
                        else
                        {
                            var table_b_damages = getHtmlSummaryTable(damages['B'], 'B', 'damages');
                            $('#damage_type_b').append(table_b_damages);
                        }
                    }
                    else
                    {
                        $('#loading_summary_type').append('<div class="alert alert-danger" role="alert"><i class="fa fa-exclamation-triangle fa-2x"></i> ' + data.msg + '</div>');
                    }
                }
            });
        }

        /**Summary for party*/
        function calculateSummaryTotal()
        {
            //set busy load indicator
            $('#summary_total_data').hide();
            $('#loading_summary_total').empty().append('<div class="alert alert-warning" role="alert"><i class="fa fa-spinner fa-spin fa-2x"></i> กำลังประมวลผลข้อมูลกรุณารอสักครู่</div>');
            //send data by ajax to calculate
            $.ajax({
                type: "POST",
                url: "{{ URL::action('BudgetController@getTypeCalculate') }}",
                data: {
                    '_token' : $("input[name=_token]").val(),
                    'party_id' : $('#party_budget_id').val()
                },
                dataType: 'json',
                success : function(data){
                    $('#loading_summary_total').empty();
                    if (data.status=='success')
                    {
                        $('#summary_total_data').show();

                        var data = data.data;

                        //keep data in store
                        $('#budgeting_data').data(data);

                        //set total summary
                        setSummaryTotal(data);
                    }
                    else
                    {
                        $('#loading_summary_total').append('<div class="alert alert-danger" role="alert"><i class="fa fa-exclamation-triangle fa-2x"></i> ' + data.msg + '</div>');
                    }
                }
            });
        }

        function getHtmlSummaryTable(data, plan, type)
        {
            var html = '';

            if (!jQuery.isEmptyObject(data))
            {
                if (type=='accommodations' || type=='foods' || type=='location_facilities')
                {
                    switch (type) {
                        case 'accommodations' :
                            html += '<table class="table table-condensed" id="table_summary_type_accommodation_' + plan + '">';
                            html += '<caption class="text-left"><strong>ห้องพัก</strong></caption>';
                            html += '<thead>';
                            html += '<tr class="active"> <th class="col-md-4">รายการ</th> <th class="aRight col-md-1">ขาย</th> <th class="aRight col-md-1">ทุน</th> <th class="aRight col-md-1">จำนวน</th> <th class="aRight col-md-1">หน่วย</th> <th class="aRight col-md-1">คืน</th> <th class="aRight col-md-1">รวมขาย</th> <th class="aRight col-md-1">รวมทุน</th> <th class="aRight col-md-1">กำไร</th> </tr>';
                            html += '</thead>';
                            break;
                        case 'foods' :
                            html += '<table class="table table-condensed" id="table_summary_type_meal_' + plan + '">';
                            html += '<caption class="text-left"><strong>อาหาร</strong></caption>';
                            html += '<thead>';
                            html += '<tr class="active"> <th class="col-md-4">รายการ</th> <th class="aRight col-md-1">ขาย</th> <th class="aRight col-md-1">ทุน</th> <th class="aRight col-md-1">จำนวน</th> <th class="aRight col-md-1">หน่วย</th> <th class="aRight col-md-1">มื้อ</th> <th class="aRight col-md-1">รวมขาย</th> <th class="aRight col-md-1">รวมทุน</th> <th class="aRight col-md-1">กำไร</th> </tr>';
                            html += '</thead>';
                            break;
                        case 'location_facilities' :
                            html += '<table class="table table-condensed" id="table_summary_type_location_facilitie_' + plan + '">';
                            html += '<caption class="text-left"><strong>ค่าใช้จ่ายในสถานที่</strong></caption>';
                            html += '<thead>';
                            html += '<tr class="active"> <th class="col-md-4">รายการ</th> <th class="aRight col-md-1">ขาย</th> <th class="aRight col-md-1">ทุน</th> <th class="aRight col-md-1">จำนวน</th> <th class="aRight col-md-1">หน่วย</th> <th class="col-md-1"></th> <th class="aRight col-md-1">รวมขาย</th> <th class="aRight col-md-1">รวมทุน</th> <th class="aRight col-md-1">กำไร</th> </tr>';
                            html += '</thead>';
                            break;
                    }

                    html += '<tbody>';

                    $(Object.keys(data)).each(function(index, item){
                        html += '<tr class="info"> <td colspan="9">' + item + '</td> </tr>';
                        $(data[item]).each(function(index, item)
                        {
                            if (item.quantity>0)
                            {
                                var day = item.day;

                                if (type=='location_facilities')
                                {
                                    day = '&nbsp;';
                                }

                                html += '<tr>';

                                if (type=='foods')
                                {
                                    html += '<td class="col-md-4">' + item.expense_name + ' <span class="label label-default">' + item.expense_food_meal_name + '</span></td>';
                                }
                                else
                                {
                                    html += '<td class="col-md-4">' + item.expense_name + '</td>';
                                }

                                html += '<td class="aRight col-md-1">' + addThousandsSeparator(parseInt(item.sale)) + '</td>';
                                html += '<td class="aRight col-md-1">' + addThousandsSeparator(parseInt(item.cost)) + '</td>';
                                html += '<td class="aRight col-md-1">' + item.quantity + '</td>';
                                html += '<td class="aRight col-md-1">' + item.expense_unit + '</td>';
                                html += '<td class="aRight col-md-1">' + day + '</td>';
                                html += '<td class="aRight col-md-1">' + addThousandsSeparator(item.item_total_sale) + '</td>';
                                html += '<td class="aRight col-md-1">' + addThousandsSeparator(item.item_total_cost) + '</td>';
                                html += '<td class="aRight col-md-1">' + addThousandsSeparator(item.item_total_profit) + '</td>';
                                html += '</tr>';
                            }
                        });
                    });

                    html += '</tbody>';
                    html += '</table>';
                }
                else if(type=='cars')
                {
                    html += '<table class="table table-condensed" id="table_summary_type_accommodation_' + plan + '">';
                    html += '<caption class="text-left"><strong>ยานพาหนะ</strong></caption>';
                    html += '<thead>';
                    html += '<tr> <th class="col-md-4">รายการ</th> <th class="aRight col-md-1">ขาย</th> <th class="aRight col-md-1">ทุน</th> <th class="aRight col-md-1">จำนวน</th> <th class="aRight col-md-1">หน่วย</th> <th class="aRight col-md-1">วัน</th> <th class="aRight col-md-1">รวมขาย</th> <th class="aRight col-md-1">รวมทุน</th> <th class="aRight col-md-1">กำไร</th> </tr>';
                    html += '</thead>';

                    html += '<tbody>';

                    $(Object.keys(data)).each(function(index, item){
                        html += '<tr class="info"> <td colspan="9">' + item + '</td> </tr>';
                        $(data[item]).each(function(index, item){
                            if (item.quantity>0)
                            {
                                html += '<tr>';
                                html += '<td class="col-md-4">' + item.expense_name + ' <span class="label label-primary">'+item.rate+'</span></td>';
                                html += '<td class="aRight col-md-1">' + addThousandsSeparator(parseInt(item.sale)) + '</td>';
                                html += '<td class="aRight col-md-1">' + addThousandsSeparator(parseInt(item.cost)) + '</td>';
                                html += '<td class="aRight col-md-1">' + item.quantity + '</td>';
                                html += '<td class="aRight col-md-1">' + item.expense_unit + '</td>';
                                html += '<td class="aRight col-md-1">' + item.day + '</td>';
                                html += '<td class="aRight col-md-1">' + addThousandsSeparator(item.item_total_sale) + '</td>';
                                html += '<td class="aRight col-md-1">' + addThousandsSeparator(item.item_total_cost) + '</td>';
                                html += '<td class="aRight col-md-1">' + addThousandsSeparator(item.item_total_profit) + '</td>';
                                html += '</tr>';
                            }
                        });
                    });

                    html += '</tbody>';
                    html += '</table>';
                }
                else if (type=='damages')
                {
                    html += '<table class="table table-condensed" id="table_damage_' + plan + '">';
                    html += '<thead>';
                    html += '<tr class="danger"> <th class="col-md-4">รายการ</th> <th class="aRight col-md-1">&nbsp;</th> <th class="aRight col-md-1">ทุน</th> <th class="aRight col-md-1">จำนวน</th> <th class="aRight col-md-1">&nbsp;</th> <th class="aRight col-md-1">&nbsp;</th> <th class="aRight col-md-1">&nbsp;</th> <th class="aRight col-md-1">รวมทุน</th> <th class="aRight col-md-1">&nbsp;</th> </tr>';
                    html += '</thead>';
                    html += '<tbody>';

                    $(data).each(function(index, item)
                    {
                        if (item.qty>0)
                        {
                            html += '<tr>';
                            html += '<td class="col-md-4">' + item.damage_text + '</td>';
                            html += '<td class="aRight col-md-1"></td>';
                            html += '<td class="aRight col-md-1">' + addThousandsSeparator(parseInt(item.cost_price)) + '</td>';
                            html += '<td class="aRight col-md-1">' + item.qty + '</td>';
                            html += '<td class="aRight col-md-1"></td>';
                            html += '<td class="aRight col-md-1"></td>';
                            html += '<td class="aRight col-md-1"></td>';
                            html += '<td class="aRight col-md-1">' + addThousandsSeparator(parseInt(item.cost_price)*parseInt(item.qty)) + '</td>';
                            html += '<td class="aRight col-md-1"></td>';
                            html += '</tr>';
                        }
                    });

                    html += '</tbody>';
                    html += '</table>';
                }
                else
                {
                    switch (type) {
                        case 'conferences' :
                            html += '<table class="table table-condensed" id="table_summary_type_conference_' + plan + '">';
                            html += '<caption class="text-left"><strong>ห้องประชุม</strong></caption>';
                            html += '<thead>';
                            html += '<tr class="active"> <th class="col-md-4">รายการ</th> <th class="aRight col-md-1">ขาย</th> <th class="aRight col-md-1">ทุน</th> <th class="aRight col-md-1">จำนวน</th> <th class="aRight col-md-1">&nbsp;</th> <th class="aRight col-md-1">วัน</th> <th class="aRight col-md-1">รวมขาย</th> <th class="aRight col-md-1">รวมทุน</th> <th class="aRight col-md-1">กำไร</th> </tr>';
                            html += '</thead>';
                            break;
                        case 'personnels' :
                            html += '<table class="table table-condensed" id="table_summary_type_personnel_' + plan + '">';
                            html += '<caption class="text-left"><strong>วิทยากรและบุคลากร</strong></caption>';
                            html += '<thead>';
                            html += '<tr class="active"> <th class="col-md-4">รายการ</th> <th class="aRight col-md-1">ขาย</th> <th class="aRight col-md-1">ทุน</th> <th class="aRight col-md-1">คน</th> <th class="aRight col-md-1">หน่วย</th> <th class="aRight col-md-1">วัน</th> <th class="aRight col-md-1">รวมขาย</th> <th class="aRight col-md-1">รวมทุน</th> <th class="aRight col-md-1">กำไร</th> </tr>';
                            html += '</thead>';
                            break;
                        case 'tools' :
                            html += '<table class="table table-condensed" id="table_summary_type_tool_' + plan + '">';
                            html += '<caption class="text-left"><strong>วัสดุและอุปกรณ์ประกอบการเรียนรู้</strong></caption>';
                            html += '<thead>';
                            html += '<tr class="active"> <th class="col-md-4">รายการ</th> <th class="aRight col-md-1">ขาย</th> <th class="aRight col-md-1">ทุน</th> <th class="aRight col-md-1">จำนวน</th> <th class="aRight col-md-1">หน่วย</th> <th class="aRight col-md-1">&nbsp;</th> <th class="aRight col-md-1">รวมขาย</th> <th class="aRight col-md-1">รวมทุน</th> <th class="aRight col-md-1">กำไร</th> </tr>';
                            html += '</thead>';
                            break;
                        case 'special_events' :
                            html += '<table class="table table-condensed" id="table_summary_type_special_event_' + plan + '">';
                            html += '<caption class="text-left"><strong>กิจกรรมพิเศษ</strong></caption>';
                            html += '<thead>';
                            html += '<tr class="active"> <th class="col-md-4">รายการ</th> <th class="aRight col-md-1">ขาย</th> <th class="aRight col-md-1">ทุน</th> <th class="aRight col-md-1">จำนวน</th> <th class="aRight col-md-1">หน่วย</th> <th class="aRight col-md-1">&nbsp;</th> <th class="aRight col-md-1">รวมขาย</th> <th class="aRight col-md-1">รวมทุน</th> <th class="aRight col-md-1">กำไร</th> </tr>';
                            html += '</thead>';
                            break;
                        case 'other' :
                            html += '<table class="table table-condensed" id="table_summary_type_other_' + plan + '">';
                            html += '<caption class="text-left"><strong>อื่นๆ</strong></caption>';
                            html += '<thead>';
                            html += '<tr class="active"> <th class="col-md-4">รายการ</th> <th class="aRight col-md-1">ขาย</th> <th class="aRight col-md-1">ทุน</th> <th class="aRight col-md-1">จำนวน</th> <th class="aRight col-md-1">หน่วย</th> <th class="aRight col-md-1">&nbsp;</th> <th class="aRight col-md-1">รวมขาย</th> <th class="aRight col-md-1">รวมทุน</th> <th class="aRight col-md-1">กำไร</th> </tr>';
                            html += '</thead>';
                            break;
                    }

                    $(data).each(function(index, item)
                    {  
                        if (item.quantity)
                        {
                            var day = item.day;

                            if (type=='tools' || type=='other' || type=='special_events')
                            {
                                day = '&nbsp;';
                            }

                            html += '<tr>';

                            if(type=='conferences' || type=='personnels')
                            {
                                html += '<td class="col-md-4">' + item.expense_name + ' <span class="label label-primary">'+item.rate+'</span></td>';
                            }
                            else
                            {
                                html += '<td class="col-md-4">' + item.expense_name + '</td>';
                            }

                            html += '<td class="aRight col-md-1">' + addThousandsSeparator(parseInt(item.sale)) + '</td>';
                            html += '<td class="aRight col-md-1">' + addThousandsSeparator(parseInt(item.cost)) + '</td>';
                            html += '<td class="aRight col-md-1">' + item.quantity + '</td>';
                            html += '<td class="aRight col-md-1">' + item.expense_unit + '</td>';
                            html += '<td class="aRight col-md-1">' + day + '</td>';
                            html += '<td class="aRight col-md-1">' + addThousandsSeparator(item.item_total_sale) + '</td>';
                            html += '<td class="aRight col-md-1">' + addThousandsSeparator(item.item_total_cost) + '</td>';
                            html += '<td class="aRight col-md-1">' + addThousandsSeparator(item.item_total_profit) + '</td>';
                            html += '</tr>';
                        }
                    });

                }
            }

            return html;
        }

        //set total summary data
        function setSummaryTotal(data)
        {
            if (jQuery.isEmptyObject(data))
            {
                $('#summary_total_data').hide();
                $('#loading_summary_total').empty().append('<div class="alert alert-danger" role="alert">Error ไม่สามารถแสดงผลการประมวลผลได้ !!!</div>');

                return false;
            }

            var sale_accommodation_a = 0;
            var sale_meal_a = 0;
            var sale_transport_a = 0;
            var sale_activity_a = 0;
            var sale_hall_a = 0;
            var sale_tool_a = 0;
            var sale_special_a = 0;
            var sale_other_a = 0;
            var cost_accommodation_a = 0;
            var cost_meal_a= 0;
            var cost_transport_a = 0;
            var cost_activity_a = 0;
            var cost_hall_a = 0;
            var cost_tool_a = 0;
            var cost_special_a = 0;
            var cost_other_a = 0;
            var profit_accommodation_a = 0;
            var profit_meal_a= 0;
            var profit_transport_a = 0;
            var profit_activity_a = 0;
            var profit_hall_a = 0;
            var profit_tool_a = 0;
            var profit_special_a = 0;
            var profit_other_a = 0;
            var sale_accommodation_b = 0;
            var sale_meal_b = 0;
            var sale_transport_b = 0;
            var sale_activity_b = 0;
            var sale_hall_b = 0;
            var sale_tool_b = 0;
            var sale_special_b = 0;
            var sale_other_b = 0;
            var cost_accommodation_b = 0;
            var cost_meal_b = 0;
            var cost_transport_b = 0;
            var cost_activity_b = 0;
            var cost_hall_b = 0;
            var cost_tool_b = 0;
            var cost_special_b = 0;
            var cost_other_b = 0;
            var profit_accommodation_b = 0;
            var profit_meal_b = 0;
            var profit_transport_b = 0;
            var profit_activity_b = 0;
            var profit_hall_b = 0;
            var profit_tool_b = 0;
            var profit_special_b = 0;
            var profit_other_b = 0;

            /*alert when data is incomplete.*/
            if (data.have_other_plan && data.num_budget_b==0)
            {
                $('#summary_total_alert').append('<div class="alert alert-danger" role="alert"><i class="fa fa-exclamation" aria-hidden="true"></i> ท่านยังไม่ได้ทำงบประมาณแผน B</div>');
            }

            /*set from database*/
            $('#numberSaleCharge').val(parseInt(data.charge)).change();
            $('#numberDiscountSale').val(parseInt(data.discount)).change();

            /*assign charge all*/
            var sale_charge = $('#numberSaleCharge').val();
            /*assign per perpon all*/
            var participant = $('#numberParticipant').val();
            /*assign discount*/
            var discount = $('#numberDiscountSale').val();

            //accommodation total a
            if (typeof data['accommodations'] != 'undefined')
            {
                var data_accommodation_a = data['accommodations']['A'];

                $(data_accommodation_a).each(function(index, item){
                    $(Object.keys(item)).each(function(index, item){
                        $( data_accommodation_a[item]).each(function(index, item){
                            sale_accommodation_a += parseInt(item.item_total_sale);
                            cost_accommodation_a += parseInt(item.item_total_cost);
                            profit_accommodation_a += parseInt(item.item_total_profit);
                        });
                    });
                });
            }
            $('#saleAccommodationPlanA').empty().append(addThousandsSeparator(sale_accommodation_a));
            $('#costAccommodationPlanA').empty().append(addThousandsSeparator(cost_accommodation_a));
            $('#profitAccommodationPlanA').empty().append(addThousandsSeparator(profit_accommodation_a));

            //meal total a
            if (typeof data['foods'] != 'undefined')
            {
                var data_meal_a = data['foods']['A'];

                $(data_meal_a).each(function(index, item){
                    $(Object.keys(item)).each(function(index, item){
                        $( data_meal_a[item]).each(function(index, item){
                            sale_meal_a += parseInt(item.item_total_sale);
                            cost_meal_a += parseInt(item.item_total_cost);
                            profit_meal_a += parseInt(item.item_total_profit);
                        });
                    });
                });
            }
            $('#saleMealPlanA').empty().append(addThousandsSeparator(sale_meal_a));
            $('#costMealPlanA').empty().append(addThousandsSeparator(cost_meal_a));
            $('#profitMealPlanA').empty().append(addThousandsSeparator(profit_meal_a));

            //other total a
            if (typeof data['other'] != 'undefined')
            {
                var data_other_a = data['other']['A'];

                $(data_other_a).each(function(index, item){
                    sale_other_a += parseInt(item.item_total_sale);
                    cost_other_a += parseInt(item.item_total_cost);
                    profit_other_a += parseInt(item.item_total_profit);
                });
            }
            $('#saleOtherPlanA').empty().append(addThousandsSeparator(sale_other_a));
            $('#costOtherPlanA').empty().append(addThousandsSeparator(cost_other_a));
            $('#profitOtherPlanA').empty().append(addThousandsSeparator(profit_other_a));

            //transport total a
            if (typeof data['cars'] != 'undefined')
            {
                var data_transport_a = data['cars']['A'];

                $(data_transport_a).each(function(index, item){
                    $(Object.keys(item)).each(function(index, item){
                        $( data_transport_a[item]).each(function(index, item){
                            sale_transport_a += parseInt(item.item_total_sale);
                            cost_transport_a += parseInt(item.item_total_cost);
                            profit_transport_a += parseInt(item.item_total_profit);
                        });
                    });
                });
            }
            $('#saleTransportPlanA').empty().append(addThousandsSeparator(sale_transport_a));
            $('#costTransportPlanA').empty().append(addThousandsSeparator(cost_transport_a));
            $('#profitTransportPlanA').empty().append(addThousandsSeparator(profit_transport_a));

            //activity total a
            if (typeof data['personnels'] != 'undefined')
            {
                var data_expert_a = data['personnels']['A'];

                $(data_expert_a).each(function(index, item){
                    sale_activity_a += parseInt(item.item_total_sale);
                    cost_activity_a += parseInt(item.item_total_cost);
                    profit_activity_a += parseInt(item.item_total_profit);
                });
            }
            $('#saleActivityPlanA').empty().append(addThousandsSeparator(sale_activity_a));
            $('#costActivityPlanA').empty().append(addThousandsSeparator(cost_activity_a));
            $('#profitActivityPlanA').empty().append(addThousandsSeparator(profit_activity_a));

            if (typeof data['tools'] != 'undefined')
            {
                var data_tool_a = data['tools']['A'];

                $(data_tool_a).each(function(index, item){
                    sale_tool_a += parseInt(item.item_total_sale);
                    cost_tool_a += parseInt(item.item_total_cost);
                    profit_tool_a += parseInt(item.item_total_profit);
                });
            }
            $('#saleToolPlanA').empty().append(addThousandsSeparator(sale_tool_a));
            $('#costToolPlanA').empty().append(addThousandsSeparator(cost_tool_a));
            $('#profitToolPlanA').empty().append(addThousandsSeparator(profit_tool_a));

            if (typeof data['conferences'] != 'undefined')
            {
                var data_conference_a = data['conferences']['A'];

                $(data_conference_a).each(function(index, item){
                    sale_hall_a += parseInt(item.item_total_sale);
                    cost_hall_a += parseInt(item.item_total_cost);
                    profit_hall_a += parseInt(item.item_total_profit);
                });
            }
            $('#saleHallPlanA').empty().append(addThousandsSeparator(sale_hall_a));
            $('#costHallPlanA').empty().append(addThousandsSeparator(cost_hall_a));
            $('#profitHallPlanA').empty().append(addThousandsSeparator(profit_hall_a));

            if (typeof data['special_events'] != 'undefined')
            {
                var data_special_a = data['special_events']['A'];

                $(data_special_a).each(function(index, item){
                    sale_special_a += parseInt(item.item_total_sale);
                    cost_special_a += parseInt(item.item_total_cost);
                    profit_special_a += parseInt(item.item_total_profit);
                });
            }
            $('#saleSpecialPlanA').empty().append(addThousandsSeparator(sale_special_a));
            $('#costSpecialPlanA').empty().append(addThousandsSeparator(cost_special_a));
            $('#profitSpecialPlanA').empty().append(addThousandsSeparator(profit_special_a));

            /*assign total a*/
            var total_sale_a = parseFloat(sale_accommodation_a+sale_meal_a+sale_transport_a+sale_activity_a+sale_hall_a+sale_tool_a+sale_special_a+sale_other_a);
            var charge_sale_a = Math.round((sale_charge/100)*total_sale_a);
            $('#numSaleTotalPlanA').val(total_sale_a);
            $('#saleTotalPlanA').empty().append(addThousandsSeparator(total_sale_a));
            $('#saleChargePlanA').empty().append(addThousandsSeparator(charge_sale_a));

            /*assign grand total a*/
            var grand_sale_a = total_sale_a+charge_sale_a;
            /*show regular grand total a*/
            $('#saleGrandTotalBeforePlanA').empty().append(addThousandsSeparator(grand_sale_a));

            /*assign total cost a*/
            var total_cost_a = parseFloat(cost_accommodation_a+cost_meal_a+cost_transport_a+cost_activity_a+cost_hall_a+cost_tool_a+cost_special_a+cost_other_a);
            /*show regular total cost a*/
            $('#numCostTotalPlanA').val(total_cost_a);

            /*assign calculate damage a*/
            var total_damage_a = 0;

            if (typeof data['damages'] != 'undefined')
            {
                var data_damage_a = data['damages']['A'];

                $(data_damage_a).each(function(index, item){
                    total_damage_a += parseInt(item.cost_price)*parseInt(item.qty);
                });
            }
            $('#numExpenseTotalPlanA').val(total_damage_a);
            $('#expenseTotalPlanA').empty().append(addThousandsSeparator(total_damage_a));

            /*Case if total damage > 0 show row after cal damage else not show row*/
            if (total_damage_a>0)
            {
                $('tr.damage').show();
            }
            else
            {
                $('tr.damage').hide();
            }


            /**For Plan B**/
            @if($count_plan_b>0)

            if (typeof data['accommodations'] != 'undefined')
            {
                var data_accommodation_b = data['accommodations']['B'];

                $(data_accommodation_b).each(function(index, item){
                    $(Object.keys(item)).each(function(index, item){
                        $( data_accommodation_b[item]).each(function(index, item){
                            sale_accommodation_b += parseInt(item.item_total_sale);
                            cost_accommodation_b += parseInt(item.item_total_cost);
                            profit_accommodation_b += parseInt(item.item_total_profit);
                        });
                    });
                });
            }
            $('#saleAccommodationPlanB').empty().append(addThousandsSeparator(sale_accommodation_b));
            $('#costAccommodationPlanB').empty().append(addThousandsSeparator(cost_accommodation_b));
            $('#profitAccommodationPlanB').empty().append(addThousandsSeparator(profit_accommodation_b));

            if (typeof data['foods'] != 'undefined')
            {
                var data_meal_b = data['foods']['B'];

                $(data_meal_b).each(function(index, item){
                    $(Object.keys(item)).each(function(index, item){
                        $( data_meal_b[item]).each(function(index, item){
                            sale_meal_b += parseInt(item.item_total_sale);
                            cost_meal_b += parseInt(item.item_total_cost);
                            profit_meal_b += parseInt(item.item_total_profit);
                        });
                    });
                });
            }
            $('#saleMealPlanB').empty().append(addThousandsSeparator(sale_meal_b));
            $('#costMealPlanB').empty().append(addThousandsSeparator(cost_meal_b));
            $('#profitMealPlanB').empty().append(addThousandsSeparator(profit_meal_b));

            if (typeof data['other'] != 'undefined')
            {
                var data_other_b = data['other']['B'];

                $(data_other_b).each(function(index, item){
                    sale_other_b += parseInt(item.item_total_sale);
                    cost_other_b += parseInt(item.item_total_cost);
                    profit_other_b += parseInt(item.item_total_profit);
                });
            }
            $('#saleOtherPlanB').empty().append(addThousandsSeparator(sale_other_b));
            $('#costOtherPlanB').empty().append(addThousandsSeparator(cost_other_b));
            $('#profitOtherPlanB').empty().append(addThousandsSeparator(profit_other_b));

            if (typeof data['cars'] != 'undefined')
            {
                var data_transport_b = data['cars']['B'];

                $(data_transport_b).each(function(index, item){
                    $(Object.keys(item)).each(function(index, item){
                        $( data_transport_b[item]).each(function(index, item){
                            sale_transport_b += parseInt(item.item_total_sale);
                            cost_transport_b += parseInt(item.item_total_cost);
                            profit_transport_b += parseInt(item.item_total_profit);
                        });
                    });
                });
            }
            $('#saleTransportPlanB').empty().append(addThousandsSeparator(sale_transport_b));
            $('#costTransportPlanB').empty().append(addThousandsSeparator(cost_transport_b));
            $('#profitTransportPlanB').empty().append(addThousandsSeparator(profit_transport_b));

            if (typeof data['personnels'] != 'undefined')
            {
                var data_expert_b = data['personnels']['B'];

                $(data_expert_b).each(function(index, item){
                    sale_activity_b += parseInt(item.item_total_sale);
                    cost_activity_b += parseInt(item.item_total_cost);
                    profit_activity_b += parseInt(item.item_total_profit);
                });
            }
            $('#saleActivityPlanB').empty().append(addThousandsSeparator(sale_activity_b));
            $('#costActivityPlanB').empty().append(addThousandsSeparator(cost_activity_b));
            $('#profitActivityPlanB').empty().append(addThousandsSeparator(profit_activity_b));

            if (typeof data['tools'] != 'undefined')
            {
                var data_tool_b = data['tools']['B'];

                $(data_tool_b).each(function(index, item){
                    sale_tool_b += parseInt(item.item_total_sale);
                    cost_tool_b += parseInt(item.item_total_cost);
                    profit_tool_b += parseInt(item.item_total_profit);
                });
            }
            $('#saleToolPlanB').empty().append(addThousandsSeparator(sale_tool_b));
            $('#costToolPlanB').empty().append(addThousandsSeparator(cost_tool_b));
            $('#profitToolPlanB').empty().append(addThousandsSeparator(profit_tool_b));

            if (typeof data['conferences'] != 'undefined')
            {
                var data_conference_b = data['conferences']['B'];

                $(data_conference_b).each(function(index, item){
                    sale_hall_b += parseInt(item.item_total_sale);
                    cost_hall_b += parseInt(item.item_total_cost);
                    profit_hall_b += parseInt(item.item_total_profit);
                });
            }
            $('#saleHallPlanB').empty().append(addThousandsSeparator(sale_hall_b));
            $('#costHallPlanB').empty().append(addThousandsSeparator(cost_hall_b));
            $('#profitHallPlanB').empty().append(addThousandsSeparator(profit_hall_b));

            if (typeof data['special_events'] != 'undefined')
            {
                var data_special_b = data['special_events']['B'];

                $(data_special_b).each(function(index, item){
                    sale_special_b += parseInt(item.item_total_sale);
                    cost_special_b += parseInt(item.item_total_cost);
                    profit_special_b += parseInt(item.item_total_profit);
                });
            }
            $('#saleSpecialPlanB').empty().append(addThousandsSeparator(sale_special_b));
            $('#costSpecialPlanB').empty().append(addThousandsSeparator(cost_special_b));
            $('#profitSpecialPlanB').empty().append(addThousandsSeparator(profit_special_b));

            /*assign diff*/
            var diff_sale_accommodation = parseFloat(sale_accommodation_a-sale_accommodation_b);
            var diff_cost_accommodation = parseFloat(cost_accommodation_a-cost_accommodation_b);
            var diff_profit_accommodation = parseFloat(profit_accommodation_a-profit_accommodation_b);
            $('#saleAccommodationDiff').empty().append(addThousandsSeparator(diff_sale_accommodation));
            $('#costAccommodationDiff').empty().append(addThousandsSeparator(diff_cost_accommodation));
            $('#profitAccommodationDiff').empty().append(addThousandsSeparator(diff_profit_accommodation));

            var diff_sale_meal = parseFloat(sale_meal_a-sale_meal_b);
            var diff_cost_meal = parseFloat(cost_meal_a-cost_meal_b);
            var diff_profit_meal = parseFloat(profit_meal_a-profit_meal_b);
            $('#saleMealDiff').empty().append(addThousandsSeparator(diff_sale_meal));
            $('#costMealDiff').empty().append(addThousandsSeparator(diff_cost_meal));
            $('#profitMealDiff').empty().append(addThousandsSeparator(diff_profit_meal));

            var diff_sale_transport = parseFloat(sale_transport_a-sale_transport_b);
            var diff_cost_transport = parseFloat(cost_transport_a-cost_transport_b);
            var diff_profit_transport = parseFloat(profit_transport_a-profit_transport_b);
            $('#saleTransportDiff').empty().append(addThousandsSeparator(diff_sale_transport));
            $('#costTransportDiff').empty().append(addThousandsSeparator(diff_cost_transport));
            $('#profitTransportDiff').empty().append(addThousandsSeparator(diff_profit_transport));

            var diff_sale_activity = parseFloat(sale_activity_a-sale_activity_b);
            var diff_cost_activity = parseFloat(cost_activity_a-cost_activity_b);
            var diff_profit_activity = parseFloat(profit_activity_a-profit_activity_b);
            $('#saleActivityDiff').empty().append(addThousandsSeparator(diff_sale_activity));
            $('#costActivityDiff').empty().append(addThousandsSeparator(diff_cost_activity));
            $('#profitActivityDiff').empty().append(addThousandsSeparator(diff_profit_activity));

            var diff_sale_activity = parseFloat(sale_activity_a-sale_activity_b);
            var diff_cost_activity = parseFloat(cost_activity_a-cost_activity_b);
            var diff_profit_activity = parseFloat(profit_activity_a-profit_activity_b);
            $('#saleActivityDiff').empty().append(addThousandsSeparator(diff_sale_activity));
            $('#costActivityDiff').empty().append(addThousandsSeparator(diff_cost_activity));
            $('#profitActivityDiff').empty().append(addThousandsSeparator(diff_profit_activity));

            var diff_sale_hall = parseFloat(sale_hall_a-sale_hall_b);
            var diff_cost_hall = parseFloat(cost_hall_a-cost_hall_b);
            var diff_profit_hall = parseFloat(profit_hall_a-profit_hall_b);
            $('#saleHallDiff').empty().append(addThousandsSeparator(diff_sale_hall));
            $('#costHallDiff').empty().append(addThousandsSeparator(diff_cost_hall));
            $('#profitHallDiff').empty().append(addThousandsSeparator(diff_profit_hall));

            var diff_sale_hall = parseFloat(sale_hall_a-sale_hall_b);
            var diff_cost_hall = parseFloat(cost_hall_a-cost_hall_b);
            var diff_profit_hall = parseFloat(profit_hall_a-profit_hall_b);
            $('#saleHallDiff').empty().append(addThousandsSeparator(diff_sale_hall));
            $('#costHallDiff').empty().append(addThousandsSeparator(diff_cost_hall));
            $('#profitHallDiff').empty().append(addThousandsSeparator(diff_profit_hall));

            var diff_sale_tool = parseFloat(sale_tool_a-sale_tool_b);
            var diff_cost_tool = parseFloat(cost_tool_a-cost_tool_b);
            var diff_profit_tool = parseFloat(profit_tool_a-profit_tool_b);
            $('#saleToolDiff').empty().append(addThousandsSeparator(diff_sale_tool));
            $('#costToolDiff').empty().append(addThousandsSeparator(diff_cost_tool));
            $('#profitToolDiff').empty().append(addThousandsSeparator(diff_profit_tool));

            var diff_sale_special = parseFloat(sale_special_a-sale_special_b);
            var diff_cost_special = parseFloat(cost_special_a-cost_special_b);
            var diff_profit_special = parseFloat(profit_special_a-profit_special_b);
            $('#saleSpecialDiff').empty().append(addThousandsSeparator(diff_sale_special));
            $('#costSpecialDiff').empty().append(addThousandsSeparator(diff_cost_special));
            $('#profitSpecialDiff').empty().append(addThousandsSeparator(diff_profit_special));

            var diff_sale_other = parseFloat(sale_other_a-sale_other_b);
            var diff_cost_other = parseFloat(cost_other_a-cost_other_b);
            var diff_profit_other = parseFloat(profit_other_a-profit_other_b);
            $('#saleOtherDiff').empty().append(addThousandsSeparator(diff_sale_other));
            $('#costOtherDiff').empty().append(addThousandsSeparator(diff_cost_other));
            $('#profitOtherDiff').empty().append(addThousandsSeparator(diff_profit_other));

            var total_sale_b = parseFloat(sale_accommodation_b+sale_meal_b+sale_transport_b+sale_activity_b+sale_hall_b+sale_tool_b+sale_special_b+sale_other_b);
            var charge_sale_b = Math.round((sale_charge/100)*total_sale_b);
            $('#numSaleTotalPlanB').val(total_sale_b);
            $('#saleTotalPlanB').empty().append(addThousandsSeparator(total_sale_b));
            $('#saleChargePlanB').empty().append(addThousandsSeparator(charge_sale_b));
            $('#saleChargeDiff').empty().append(addThousandsSeparator((charge_sale_a-charge_sale_b)));

            var grand_sale_b = parseFloat(total_sale_b+charge_sale_b);

            /*show regular grand total b*/
            $('#saleGrandTotalBeforePlanB').empty().append(addThousandsSeparator(grand_sale_b));

            /*assign total cost b*/
            var total_cost_b = parseFloat(cost_accommodation_b+cost_meal_b+cost_transport_b+cost_activity_b+cost_hall_b+cost_tool_b+cost_special_b+cost_other_b);
            /*show regular total cost b*/
            $('#numCostTotalPlanB').val(total_cost_b);

            /*assign calculate damage b*/
            var total_damage_b = 0;

            if (typeof data['damages'] != 'undefined')
            {
                var data_damage_b = data['damages']['B'];

                $(data_damage_b).each(function(index, item){
                    total_damage_b += parseInt(item.cost_price)*parseInt(item.qty);
                });
            }
            $('#numExpenseTotalPlanB').val(total_damage_b);
            $('#expenseTotalPlanB').empty().append(addThousandsSeparator(total_damage_b));

            @endif

            /*Calculate Actual(After Discount and Damage)*/
            setTimeout(function() {
                //executed after 1 second
                calculateChargeAndDiscount();
            }, 100);
        }

        //เปลี่ยนค่าบริหารจัดการและส่วนลด
        function calculateChargeAndDiscount()
        {
            //Set Param
            var charge = $('#numberSaleCharge').val();
            var discount = $('#numberDiscountSale').val();
            var participant = $('#numberParticipant').val();
            //For Plan A
            var total_sale_a = parseFloat($('#numSaleTotalPlanA').val());
            var total_cost_a = parseFloat($('#numCostTotalPlanA').val());
            
            var charge_sale_a = Math.round((charge/100)*total_sale_a);
            $('#saleChargePlanA').empty().append(addThousandsSeparator(charge_sale_a));

            var grand_sale_a = parseFloat((total_sale_a)+(charge_sale_a));
			
			//before decrease a
			$('#saleGrandTotalBeforePlanA').empty().append(addThousandsSeparator(grand_sale_a));

            /*after discount a*/
            if (discount>0)
            {
                var discount_a = Math.round((grand_sale_a*(discount/100)));
                grand_sale_a = parseFloat((grand_sale_a)-(discount_a));
                $('#saleDiscountPlanA').empty().append(addThousandsSeparator(discount_a));
            }

            var total_profit_a = parseFloat(grand_sale_a-total_cost_a);
            $('#saleGrandTotalPlanA').empty().append(addThousandsSeparator(grand_sale_a));
            $('#costTotalPlanA').empty().append(addThousandsSeparator(total_cost_a));
            $('#profitTotalPlanA').empty().append(addThousandsSeparator(total_profit_a));

            var average_sale_a = Math.ceil((grand_sale_a)/participant);
            $('#averagePlanA').empty().append(addThousandsSeparator(average_sale_a));

            var percent_a = Math.round((total_profit_a/grand_sale_a)*100);
            if (isNaN(percent_a))
            {
                percent_a = 0;
            }
            $('#profitPercentPlanA').empty().append(percent_a);
			
			//Damage A
			var damage_a = parseFloat($('#numExpenseTotalPlanA').val());
			var actual_cost_a = 0;
			var actual_profit_a = 0;
			
			if (damage_a>0)
			{
				actual_cost_a = parseFloat((total_cost_a)+(damage_a));
			}
			else
			{
				actual_cost_a = parseFloat(total_cost_a);
			}
			actual_profit_a = parseFloat(grand_sale_a-actual_cost_a);
			
			$('#saleActualTotalPlanA').empty().append(addThousandsSeparator(grand_sale_a));
			$('#costActualTotalPlanA').empty().append(addThousandsSeparator(actual_cost_a));
			$('#profitActualTotalPlanA').empty().append(addThousandsSeparator(actual_profit_a));
            //add actual percent of profit too
            var actual_percent_a = Math.round((actual_profit_a/grand_sale_a)*100);
            $('#profitPercentActualPlanA').empty().append(actual_percent_a);
			
            //For Plan B
            @if($count_plan_b>0)
                var total_sale_b = parseFloat($('#numSaleTotalPlanB').val());
                var total_cost_b = parseFloat($('#numCostTotalPlanB').val());
				
                var charge_sale_b = Math.round((charge/100)*total_sale_b);
                $('#saleChargePlanB').empty().append(addThousandsSeparator(charge_sale_b));

                var grand_sale_b = parseFloat((total_sale_b)+(charge_sale_b));
				
				//before decrease b
				$('#saleGrandTotalBeforePlanB').empty().append(addThousandsSeparator(grand_sale_b));

                /*after discount b*/
                if (discount>0)
                {
                    var discount_b = Math.round((grand_sale_b*(discount/100)));
                    grand_sale_b = parseFloat((grand_sale_b)-(discount_b));
                    $('#saleDiscountPlanB').empty().append(addThousandsSeparator(discount_b));
                }

                var total_profit_b = parseFloat(grand_sale_b-total_cost_b);
                $('#saleGrandTotalPlanB').empty().append(addThousandsSeparator(grand_sale_b));
				$('#saleGrandTotalDiff').empty().append(addThousandsSeparator((grand_sale_a)-(grand_sale_b)));
                $('#costTotalPlanB').empty().append(addThousandsSeparator(total_cost_b));
                $('#profitTotalPlanB').empty().append(addThousandsSeparator(total_profit_b));

                var average_sale_b = Math.ceil((grand_sale_b)/participant);
                $('#averagePlanB').empty().append(addThousandsSeparator(average_sale_b));
				
                /*assign diff total*/
                var diff_sale_total = parseFloat(grand_sale_a-grand_sale_b);
                var diff_cost_total = parseFloat(total_cost_a-total_cost_b);
                var diff_profit_total = parseFloat(total_profit_a-total_profit_b);
                $('#saleTotalDiff').empty().append(addThousandsSeparator(diff_sale_total));
                $('#costTotalDiff').empty().append(addThousandsSeparator(diff_cost_total));
                $('#profitTotalDiff').empty().append(addThousandsSeparator(diff_profit_total));

                $('#averageDiff').empty().append(addThousandsSeparator(average_sale_a-average_sale_b));

                /*assign profit percent*/
                var percent_b = Math.round((total_profit_b/grand_sale_b)*100);
                if (isNaN(percent_b))
                {
                    percent_b = 0;
                }
                $('#profitPercentPlanB').empty().append(percent_b);
                $('#profitPercentDiff').empty().append(percent_a-percent_b);
				
				//Damage B
				var damage_b = parseFloat($('#numExpenseTotalPlanB').val());
				var actual_cost_b = 0;
				var actual_profit_b = 0;
				
				if (damage_b>0)
				{
					actual_cost_b = parseFloat((total_cost_b)+(damage_b));
				}
				else
				{
					actual_cost_b = parseFloat(total_cost_b);
				}
				actual_profit_b = parseFloat(grand_sale_b-actual_cost_b);
				
				$('#saleActualTotalPlanB').empty().append(addThousandsSeparator(grand_sale_b));
				$('#costActualTotalPlanB').empty().append(addThousandsSeparator(actual_cost_b));
				$('#profitActualTotalPlanB').empty().append(addThousandsSeparator(actual_profit_b));
                //add actual percent of profit too for plan b
                var actual_percent_b = Math.round((actual_profit_b/grand_sale_b)*100);
                $('#profitPercentActualPlanB').empty().append(actual_percent_b);
				
				//Damage Diff
				var actual_sale_diff = parseFloat(grand_sale_a-grand_sale_b);
				var actual_cost_diff = parseFloat(actual_cost_a-actual_cost_b);
				var actual_profit_diff = parseFloat(actual_profit_a-actual_profit_b);
				
				$('#saleActualTotalDiff').empty().append(addThousandsSeparator(actual_sale_diff));
				$('#costActualTotalDiff').empty().append(addThousandsSeparator(actual_cost_diff));
				$('#profitActualTotalDiff').empty().append(addThousandsSeparator(actual_profit_diff));
                //add actual percent of profit too for diff
                var actual_percent_diff = (actual_percent_a)-(actual_percent_b);
                $('#profitPercentActualDiff').empty().append(actual_percent_diff);

            @endif
        }

        //function send ajax to save and export quotation
        function saveExportQuotation(party_id, en)
        {
            //keep data budget form calculaye to save
            @if($count_plan_b>0)
                var budgets = {
                    'accommodation_cost_a': parseFloat($('#costAccommodationPlanA').html().replace(",", "")),
                    'accommodation_sale_a': parseFloat($('#saleAccommodationPlanA').html().replace(",", "")),
                    'food_cost_a': parseFloat($('#costMealPlanA').html().replace(",", "")),
                    'food_sale_a': parseFloat($('#saleMealPlanA').html().replace(",", "")),
                    'car_cost_a': parseFloat($('#costTransportPlanA').html().replace(",", "")),
                    'car_sale_a': parseFloat($('#saleTransportPlanA').html().replace(",", "")),
                    'personnel_cost_a': parseFloat($('#costActivityPlanA').html().replace(",", "")),
                    'personnel_sale_a': parseFloat($('#saleActivityPlanA').html().replace(",", "")),
                    'tool_cost_a': parseFloat($('#costToolPlanA').html().replace(",", "")),
                    'tool_sale_a': parseFloat($('#saleToolPlanA').html().replace(",", "")),
                    'conference_cost_a': parseFloat($('#costHallPlanA').html().replace(",", "")),
                    'conference_sale_a': parseFloat($('#saleHallPlanA').html().replace(",", "")),
                    'special_event_cost_a': parseFloat($('#costSpecialPlanA').html().replace(",", "")),
                    'special_event_sale_a': parseFloat($('#saleSpecialPlanA').html().replace(",", "")),
                    'other_cost_a': parseFloat($('#costOtherPlanA').html().replace(",", "")),
                    'other_sale_a': parseFloat($('#saleOtherPlanA').html().replace(",", "")),
                    'accommodation_cost_b': parseFloat($('#costAccommodationPlanB').html().replace(",", "")),
                    'accommodation_sale_b': parseFloat($('#saleAccommodationPlanB').html().replace(",", "")),
                    'food_cost_b': parseFloat($('#costMealPlanB').html().replace(",", "")),
                    'food_sale_b': parseFloat($('#saleMealPlanB').html().replace(",", "")),
                    'car_cost_b': parseFloat($('#costTransportPlanB').html().replace(",", "")),
                    'car_sale_b': parseFloat($('#saleTransportPlanB').html().replace(",", "")),
                    'personnel_cost_b': parseFloat($('#costActivityPlanB').html().replace(",", "")),
                    'personnel_sale_b': parseFloat($('#saleActivityPlanB').html().replace(",", "")),
                    'tool_cost_b': parseFloat($('#costToolPlanB').html().replace(",", "")),
                    'tool_sale_b': parseFloat($('#saleToolPlanB').html().replace(",", "")),
                    'conference_cost_b': parseFloat($('#costHallPlanB').html().replace(",", "")),
                    'conference_sale_b': parseFloat($('#saleHallPlanB').html().replace(",", "")),
                    'special_event_cost_b': parseFloat($('#costSpecialPlanB').html().replace(",", "")),
                    'special_event_sale_b': parseFloat($('#saleSpecialPlanB').html().replace(",", "")),
                    'other_cost_b': parseFloat($('#costOtherPlanB').html().replace(",", "")),
                    'other_sale_b': parseFloat($('#saleOtherPlanB').html().replace(",", "")),
                    'charge': parseFloat($('#numberSaleCharge').val()),
                    'discount': parseFloat($('#numberDiscountSale').val()),
                    'grand_total_a': parseFloat($('#saleGrandTotalPlanA').html().replace(",", "")),
                    'grand_total_b': parseFloat($('#saleGrandTotalPlanB').html().replace(",", "")),
                    'total_damage_a': parseFloat($('#numExpenseTotalPlanA').val()),
                    'total_damage_b': parseFloat($('#numExpenseTotalPlanB').val())
                };
            @else
                var budgets =
                {
                    'accommodation_cost_a': parseFloat($('#costAccommodationPlanA').html().replace(",", "")),
                    'accommodation_sale_a': parseFloat($('#saleAccommodationPlanA').html().replace(",", "")),
                    'food_cost_a': parseFloat($('#costMealPlanA').html().replace(",", "")),
                    'food_sale_a': parseFloat($('#saleMealPlanA').html().replace(",", "")),
                    'car_cost_a': parseFloat($('#costTransportPlanA').html().replace(",", "")),
                    'car_sale_a': parseFloat($('#saleTransportPlanA').html().replace(",", "")),
                    'personnel_cost_a': parseFloat($('#costActivityPlanA').html().replace(",", "")),
                    'personnel_sale_a': parseFloat($('#saleActivityPlanA').html().replace(",", "")),
                    'tool_cost_a': parseFloat($('#costToolPlanA').html().replace(",", "")),
                    'tool_sale_a': parseFloat($('#saleToolPlanA').html().replace(",", "")),
                    'conference_cost_a': parseFloat($('#costHallPlanA').html().replace(",", "")),
                    'conference_sale_a': parseFloat($('#saleHallPlanA').html().replace(",", "")),
                    'special_event_cost_a': parseFloat($('#costSpecialPlanA').html().replace(",", "")),
                    'special_event_sale_a': parseFloat($('#saleSpecialPlanA').html().replace(",", "")),
                    'other_cost_a': parseFloat($('#costOtherPlanA').html().replace(",", "")),
                    'other_sale_a': parseFloat($('#saleOtherPlanA').html().replace(",", "")),
                    'charge': parseFloat($('#numberSaleCharge').val()),
                    'discount': parseFloat($('#numberDiscountSale').val()),
                    'grand_total_a': parseFloat($('#saleGrandTotalPlanA').html().replace(",", "")),
                    'total_damage_a': parseFloat($('#numExpenseTotalPlanA').val())
                };
            @endif

            var btn = $('#submitCreateDocument').button('loading');

            $.ajax({
                type: "POST",
                url: "{{ URL::action('BudgetController@getQuotationAndPriceList') }}",
                data: {
                    '_token' : $("input[name=_token]").val(),
                    'party_id' : party_id,
                    'budget' : budgets,
                    'items' : $('#budgeting_data').data(),
                    'en' : en
                },
                success: function (data) {
                    btn.button('reset');
                    if (data.status=='success')
                    {
                        //modal with download button
                        var buttons = [
                            {
                                icon: 'fa fa-file-word-o',
                                label: 'โหลดใบเสนอราคา',
                                action: function(){
                                    window.open(data.document.quotation);
                                }
                            },
							{
                                icon: 'fa fa-file-excel-o',
                                label: 'โหลดรายการราคาสินค้า',
                                action: function(){
                                    window.open(data.document.price);
                                }
                            },
                            {
                                icon: 'fa fa-users',
                                label: 'ไปยังหน้าคณะนี้เพื่อจัดการต่อ',
                                action: function(){
                                    window.open('{{ URL::to('party/'.$party->id.'/view') }}');
                                }
                            }
                        ];
                        successButton('ทำการบันทึกและสร้างเอกสารสำเร็จ', data.msg, buttons);
                    }
                    else
                    {
                        errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                    }
                },
                dataType: 'json'
            });
        }

        //re-declare json
        function convertJsonLocations(data)
        {
            if (data!=undefined)
            {
                var arr = [];
                var a = 0;
                $(Object.keys(data)).each(function(index, item) {
                    if (data[item].length>0 && item!="")
                    {
                        /*Loop array*/
                        var childrens = [];

                        $(data[item]).each(function(i, obj) {
                            childrens.push({
                                'id' : obj.id,
                                'text' : obj.text
                            });
                        });

                        /*Set object*/
                        arr[a] = {
                            'text' : item,
                            'children' : childrens
                        };

                        a++;
                    }
                });

                return arr;
            }
            else
            {
                return [];
            }
        }

        //convert type name to thai
        function convertTypeToThai(type)
        {
            var type_name = '';
            switch (type){
                case 'accommodations' :
                    type_name = 'ห้องพัก';
                break;
                case 'foods' :
                    type_name = 'อาหาร';
                break;
                case 'cars' :
                type_name = 'ยานพาหนะ';
                break;
                case 'personnels' :
                    type_name = 'วิทยากรและบุคลากร';
                break;
                case 'conferences' :
                    type_name = 'ห้องประชุม';
                break;
                case 'location_facilities' :
                    type_name = 'อุปกรณ์ประจำสำนักงานหรือสถานที่';
                break;
                case 'tools' :
                    type_name = 'วัสดุและอุปกรณ์ประกอบการเรียนรู้';
                    break;
                case 'special_events' :
                    type_name = 'กิจกรรมพิเศษ';
                break;
                case 'other' :
                    type_name = 'อื่นๆ';
                    break;
            }

            return type_name;
        }

        function convertDateTimeToMeal(datetime)
        {
            var d = datetime.split(" ");
            var h = d[1].split(":");
            var meal = null;
            var hour = h[0];
            if (hour>=6 && hour<=9)
            {
                meal = 'breakfast';
            }

            if (hour>=10 && hour<=11)
            {
                meal = 'break_morning';
            }

            if (hour>=12 && hour<=13)
            {
                meal = 'lunch';
            }

            if (hour>=14 && hour<=16)
            {
                meal = 'break_afternoon';
            }

            if (hour>=17 && hour<=19)
            {
                meal = 'dinner';
            }

            if (hour>=20 && hour<=23)
            {
                meal = 'night';
            }

            return meal;
        }

        @endif
    </script>

@stop