<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<html>

<head>
    <title>รายการราคาล่าสุด {{ date('d/m/Y') }}</title>
    {{--Use Bootstrap Framework--}}
    {{ HTML::style('bootstrap/css/bootstrap.min.css') }}
    {{ HTML::style('bootstrap/css/bootstrap-theme.min.css') }}
    {{ HTML::script('bootstrap/js/bootstrap.min.js') }}
</head>

    <table class="table table-bordered">
        <caption><strong>ที่พัก</strong></caption>
        <thead>
            <tr>
                <th class="col-md-1">ลำดับ</th>
                <th class="col-md-4">รายการ</th>
                <th class="col-md-1">ราคาขาย</th>
                <th class="col-md-1">ราคาทุน</th>
                <th class="col-md-1">หน่วย</th>
                <th class="col-md-2">สร้างเมื่อ</th>
                <th class="col-md-2">ปรับปรุงเมื่อ</th>
            </tr>
        </thead>
        <tbody>
        @foreach(array_keys($prices['accommodations']) as $location)
            <tr>
                <td class="active" colspan="7">{{ $location }}</td>
            </tr>
            <?php
                    $i=1;
            ?>
            @if(count($prices['accommodations'][$location])>0)
                @foreach($prices['accommodations'][$location] as $item)
                    <tr>
                        <td>{{ $i }}</td>
                        <td>{{ $item['name'] }}</td>
                        <td align="right">{{ number_format($item['sale_price']) }}</td>
                        <td align="right">{{ number_format($item['cost_price']) }}</td>
                        <td>{{ $item['unit'] }}</td>
                        <td>{{ ScheduleController::dateRangeStr($item['created_at'], $item['created_at'], true, false, 'th', true, true) }}</td>
                        <td>{{ ScheduleController::dateRangeStr($item['updated_at'], $item['updated_at'], true, false, 'th', true, true) }}</td>
                    </tr>
                    <?php
                    $i++;
                    ?>
                @endforeach
            @else
                <tr>
                    <td class="warning" colspan="7">ไม่มีรายการ</td>
                </tr>
            @endif

        @endforeach
        </tbody>
    </table>

    <table class="table table-bordered">
        <caption><strong>อาหาร</strong></caption>
        <thead>
        <tr>
            <th class="col-md-1">ลำดับ</th>
            <th class="col-md-4">รายการ</th>
            <th class="col-md-1">ราคาขาย</th>
            <th class="col-md-1">ราคาทุน</th>
            <th class="col-md-1">หน่วย</th>
            <th class="col-md-2">สร้างเมื่อ</th>
            <th class="col-md-2">ปรับปรุงเมื่อ</th>
        </tr>
        </thead>
        <tbody>
        @foreach(array_keys($prices['foods']) as $location)
            <tr>
                <td class="active" colspan="7">{{ $location }}</td>
            </tr>
            <?php
            $i=1;
            ?>
            @if(count($prices['foods'][$location])>0)
                @foreach($prices['foods'][$location] as $item)
                    <tr>
                        <td>{{ $i }}</td>
                        <td>{{ $item['name'] }}</td>
                        <td align="right">{{ number_format($item['sale_price']) }}</td>
                        <td align="right">{{ number_format($item['cost_price']) }}</td>
                        <td>{{ $item['unit'] }}</td>
                        <td>{{ ScheduleController::dateRangeStr($item['created_at'], $item['created_at'], true, false, 'th', true, true) }}</td>
                        <td>{{ ScheduleController::dateRangeStr($item['updated_at'], $item['updated_at'], true, false, 'th', true, true) }}</td>
                    </tr>
                    <?php
                    $i++;
                    ?>
                @endforeach
            @else
                <tr>
                    <td class="warning" colspan="7">ไม่มีรายการ</td>
                </tr>
            @endif

        @endforeach
        </tbody>
    </table>

    <table class="table table-bordered">
        <caption><strong>ยานพาหนะ</strong></caption>
        <thead>
        <tr>
            <th class="col-md-1">ลำดับ</th>
            <th class="col-md-4">รายการ</th>
            <th class="col-md-1">ราคาขาย</th>
            <th class="col-md-1">ราคาทุน</th>
            <th class="col-md-1">หน่วย</th>
            <th class="col-md-2">สร้างเมื่อ</th>
            <th class="col-md-2">ปรับปรุงเมื่อ</th>
        </tr>
        </thead>
        <tbody>
        @foreach(array_keys($prices['cars']) as $location)
            <tr>
                <td class="active" colspan="7">{{ $location }}</td>
            </tr>
            <?php
            $i=1;
            ?>
            @if(count($prices['cars'][$location])>0)
                @foreach($prices['cars'][$location] as $item)
                    <tr class="success">
                        <td>{{ $i }}</td>
                        <td colspan="3">{{ $item['name'] }}</td>
                        <td>{{ $item['unit'] }}</td>
                        <td>{{ ScheduleController::dateRangeStr($item['created_at'], $item['created_at'], true, false, 'th', true, true) }}</td>
                        <td>{{ ScheduleController::dateRangeStr($item['updated_at'], $item['updated_at'], true, false, 'th', true, true) }}</td>
                    </tr>

                    @foreach($item->rates as $rate)
                        <tr>
                            <td></td>
                            <td>{{ $rate['name'] }}</td>
                            <td align="right">{{ number_format($rate['sale_price']) }}</td>
                            <td align="right">{{ number_format($rate['cost_price']) }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endforeach

                    <?php
                    $i++;
                    ?>
                @endforeach
            @else
                <tr>
                    <td class="warning" colspan="7">ไม่มีรายการ</td>
                </tr>
            @endif

        @endforeach
        </tbody>
    </table>

    <table class="table table-bordered">
        <caption><strong>วิทยากรและบุคลากร</strong></caption>
        <thead>
        <tr>
            <th class="col-md-1">ลำดับ</th>
            <th class="col-md-4">รายการ</th>
            <th class="col-md-1">ราคาขาย</th>
            <th class="col-md-1">ราคาทุน</th>
            <th class="col-md-1">หน่วย</th>
            <th class="col-md-2">สร้างเมื่อ</th>
            <th class="col-md-2">ปรับปรุงเมื่อ</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $i=1;
        ?>
        @if(count($prices['personnels'])>0)
            @foreach($prices['personnels'] as $item)
                <tr class="success">
                    <td>{{ $i }}</td>
                    <td colspan="3">{{ $item['name'] }}</td>
                    <td>{{ $item['unit'] }}</td>
                    <td>{{ ScheduleController::dateRangeStr($item['created_at'], $item['created_at'], true, false, 'th', true, true) }}</td>
                    <td>{{ ScheduleController::dateRangeStr($item['updated_at'], $item['updated_at'], true, false, 'th', true, true) }}</td>
                </tr>

                @foreach($item['rates'] as $rate)
                    <tr>
                        <td></td>
                        <td>{{ $rate['name'] }}</td>
                        <td align="right">{{ number_format($rate['sale_price']) }}</td>
                        <td align="right">{{ number_format($rate['cost_price']) }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endforeach

                <?php
                $i++;
                ?>
            @endforeach
        @else
            <tr>
                <td class="warning" colspan="7">ไม่มีรายการ</td>
            </tr>
        @endif

        </tbody>
    </table>

    <table class="table table-bordered">
        <caption><strong>ห้องประชุม</strong></caption>
        <thead>
        <tr>
            <th class="col-md-1">ลำดับ</th>
            <th class="col-md-4">รายการ</th>
            <th class="col-md-1">ราคาขาย</th>
            <th class="col-md-1">ราคาทุน</th>
            <th class="col-md-1">หน่วย</th>
            <th class="col-md-2">สร้างเมื่อ</th>
            <th class="col-md-2">ปรับปรุงเมื่อ</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $i=1;
        ?>
        @if(count($prices['conferences'])>0)
            @foreach($prices['conferences'] as $item)
                <tr class="success">
                    <td>{{ $i }}</td>
                    <td colspan="3">{{ $item['name'] }}</td>
                    <td>ห้อง</td>
                    <td>{{ ScheduleController::dateRangeStr($item['created_at'], $item['created_at'], true, false, 'th', true, true) }}</td>
                    <td>{{ ScheduleController::dateRangeStr($item['updated_at'], $item['updated_at'], true, false, 'th', true, true) }}</td>
                </tr>

                @foreach($item['rates'] as $rate)
                    <tr>
                        <td></td>
                        <td>{{ $rate['name'] }}</td>
                        <td align="right">{{ number_format($rate['sale_price']) }}</td>
                        <td align="right">{{ number_format($rate['cost_price']) }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endforeach

                <?php
                $i++;
                ?>
            @endforeach
        @else
            <tr>
                <td class="warning" colspan="7">ไม่มีรายการ</td>
            </tr>
        @endif

        </tbody>
    </table>

    <table class="table table-bordered">
        <caption><strong>วัสดุและอุปกรณ์ประกอบการเรียนรู้</strong></caption>
        <thead>
        <tr>
            <th class="col-md-1">ลำดับ</th>
            <th class="col-md-4">รายการ</th>
            <th class="col-md-1">ราคาขาย</th>
            <th class="col-md-1">ราคาทุน</th>
            <th class="col-md-1">หน่วย</th>
            <th class="col-md-2">สร้างเมื่อ</th>
            <th class="col-md-2">ปรับปรุงเมื่อ</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $i=1;
        ?>
        @if(count($prices['tools'])>0)
            @foreach($prices['tools'] as $item)
                <tr>
                    <td>{{ $i }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td align="right">{{ number_format($item['sale_price']) }}</td>
                    <td align="right">{{ number_format($item['cost_price']) }}</td>
                    <td>{{ $item['unit'] }}</td>
                    <td>{{ ScheduleController::dateRangeStr($item['created_at'], $item['created_at'], true, false, 'th', true, true) }}</td>
                    <td>{{ ScheduleController::dateRangeStr($item['updated_at'], $item['updated_at'], true, false, 'th', true, true) }}</td>
                </tr>

                <?php
                $i++;
                ?>
            @endforeach
        @else
            <tr>
                <td class="warning" colspan="7">ไม่มีรายการ</td>
            </tr>
        @endif

        </tbody>
    </table>

    <table class="table table-bordered">
        <caption><strong>กิจกรรมพิเศษ</strong></caption>
        <thead>
        <tr>
            <th class="col-md-1">ลำดับ</th>
            <th class="col-md-4">รายการ</th>
            <th class="col-md-1">ราคาขาย</th>
            <th class="col-md-1">ราคาทุน</th>
            <th class="col-md-1">หน่วย</th>
            <th class="col-md-2">สร้างเมื่อ</th>
            <th class="col-md-2">ปรับปรุงเมื่อ</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $i=1;
        ?>
        @if(count($prices['special_events'])>0)
            @foreach($prices['special_events'] as $item)
                <tr>
                    <td>{{ $i }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td align="right">{{ number_format($item['sale_price']) }}</td>
                    <td align="right">{{ number_format($item['cost_price']) }}</td>
                    <td>กิจกรรม</td>
                    <td>{{ ScheduleController::dateRangeStr($item['created_at'], $item['created_at'], true, false, 'th', true, true) }}</td>
                    <td>{{ ScheduleController::dateRangeStr($item['updated_at'], $item['updated_at'], true, false, 'th', true, true) }}</td>
                </tr>

                <?php
                $i++;
                ?>
            @endforeach
        @else
            <tr>
                <td class="warning" colspan="7">ไม่มีรายการ</td>
            </tr>
        @endif

        </tbody>
    </table>

</html>