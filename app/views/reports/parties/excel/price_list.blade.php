<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<html>

{{ HTML::style('assets/css/priceList.css') }}

<table>

    <tr>
        <td colspan="9" valign="middle" class="TitlePriceList">
           จำนวนผู้เข้าศึกษาดูงาน
        </td>
    </tr>

    <tr>
        <td>
           รายละเอียดค่าใช้จ่ายคณะ
        </td>
        <td>
            {{ $party->name }}
        </td>
        <td></td>
        <td>
            วันที่มา
        </td>
        <td>
            {{ ScheduleController::dateRangeStr($party->start_date, $party->start_date, true) }}
        </td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td>
            จำนวนผู้มาดูงาน
        </td>
        <td>
            {{ $party->people_quantity }}
        </td>
        <td>คน</td>
        <td>
            วันที่กลับ
        </td>
        <td>
            {{ ScheduleController::dateRangeStr($party->end_date, $party->end_date, true) }}
        </td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td>
            รหัสลูกค้า
        </td>
        <td>
            {{ $party->customer_code }}
        </td>
        <td></td>
        <td>
            จำนวนวัน
        </td>
        <td>
            {{ count(ScheduleController::dateRangeArray($party->end_date, $party->end_date)) }}
        </td>
        <td>วัน</td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    {{--loop selected items to fill--}}
    @if(count($items)>0)

        {{--Accommodation--}}
        @if(isset($items['accommodations'][$plan]))

            <tr>
                <td colspan="9" valign="middle" class="TitlePriceList">
                    ห้องพัก
                </td>
            </tr>

            <tr class="RowHeader">
                <td>รายการ</td>
                <td>ขาย</td>
                <td>ทุน</td>
                <td>จำนวน</td>
                <td>หน่วย</td>
                <td>คืน</td>
                <td>รวมขาย</td>
                <td>รวมทุน</td>
                <td>กำไร</td>
            </tr>

            @foreach(array_keys($items['accommodations'][$plan]) as $accommodation)
                <tr>
                    <td colspan="9" valign="middle" class="SubTitlePriceList">
                        {{ $accommodation }}
                    </td>
                </tr>

                @foreach($items['accommodations'][$plan][$accommodation] as $item)
                    <tr>
                        <td>{{ $item['expense_name'] }}</td>
                        <td align="right" class="Number">{{ number_format($item['sale']) }}</td>
                        <td align="right" class="Number">{{ number_format($item['cost']) }}</td>
                        <td align="right" class="Number">{{ $item['quantity'] }}</td>
                        <td>{{ $item['expense_unit'] }}</td>
                        <td align="right" class="Number">{{ $item['day'] }}</td>
                        <td align="right" class="Number">{{ number_format($item['item_total_sale']) }}</td>
                        <td align="right" class="Number">{{ number_format($item['item_total_cost']) }}</td>
                        <td align="right" class="Number">{{ number_format($item['item_total_profit']) }}</td>
                    </tr>
                @endforeach

            @endforeach
        @endif

        {{--Food Meals--}}
        @if(isset($items['foods'][$plan]))

            <tr>
                <td colspan="9" valign="middle" class="TitlePriceList">
                    อาหาร
                </td>
            </tr>

            <tr class="RowHeader">
                <td>รายการ</td>
                <td>ขาย</td>
                <td>ทุน</td>
                <td>จำนวน</td>
                <td>หน่วย</td>
                <td></td>
                <td>รวมขาย</td>
                <td>รวมทุน</td>
                <td>กำไร</td>
            </tr>

            @foreach(array_keys($items['foods'][$plan]) as $restaurant)
                <tr>
                    <td colspan="9" valign="middle" class="SubTitlePriceList">
                        {{ $restaurant }}
                    </td>
                </tr>

                @foreach($items['foods'][$plan][$restaurant] as $item)
                    <tr>
                        <td>{{ $item['expense_name'] }}</td>
                        <td align="right" class="Number">{{ number_format($item['sale']) }}</td>
                        <td align="right" class="Number">{{ number_format($item['cost']) }}</td>
                        <td align="right" class="Number">{{ $item['quantity'] }}</td>
                        <td>{{ $item['expense_unit'] }}</td>
                        <td align="right" class="Number"></td>
                        <td align="right" class="Number">{{ number_format($item['item_total_sale']) }}</td>
                        <td align="right" class="Number">{{ number_format($item['item_total_cost']) }}</td>
                        <td align="right" class="Number">{{ number_format($item['item_total_profit']) }}</td>
                    </tr>
                @endforeach

            @endforeach
        @endif

        {{--Cars--}}
        @if(isset($items['cars'][$plan]))

            <tr>
                <td colspan="9" valign="middle" class="TitlePriceList">
                    ยานพาหนะ
                </td>
            </tr>

            <tr class="RowHeader">
                <td>รายการ</td>
                <td>ขาย</td>
                <td>ทุน</td>
                <td>จำนวน</td>
                <td>หน่วย</td>
                <td>วัน</td>
                <td>รวมขาย</td>
                <td>รวมทุน</td>
                <td>กำไร</td>
            </tr>

            @foreach(array_keys($items['cars'][$plan]) as $facilitator)
                <tr>
                    <td colspan="9" valign="middle" class="SubTitlePriceList">
                        {{ $facilitator }}
                    </td>
                </tr>

                @foreach($items['cars'][$plan][$facilitator] as $item)
                    <tr>
                        <td>{{ $item['expense_name'] }}</td>
                        <td align="right" class="Number">{{ number_format($item['sale']) }}</td>
                        <td align="right" class="Number">{{ number_format($item['cost']) }}</td>
                        <td align="right" class="Number">{{ $item['quantity'] }}</td>
                        <td>{{ $item['expense_unit'] }}</td>
                        <td align="right" class="Number">{{ $item['day'] }}</td>
                        <td align="right" class="Number">{{ number_format($item['item_total_sale']) }}</td>
                        <td align="right" class="Number">{{ number_format($item['item_total_cost']) }}</td>
                        <td align="right" class="Number">{{ number_format($item['item_total_profit']) }}</td>
                    </tr>
                @endforeach

            @endforeach
        @endif

        {{--Expert--}}
        @if(isset($items['personnels'][$plan]))

            <tr>
                <td colspan="9" valign="middle" class="TitlePriceList">
                    วิทยากรและบุคคลากร
                </td>
            </tr>

            <tr class="RowHeader">
                <td>รายการ</td>
                <td>ขาย</td>
                <td>ทุน</td>
                <td>จำนวน</td>
                <td>หน่วย</td>
                <td>วัน</td>
                <td>รวมขาย</td>
                <td>รวมทุน</td>
                <td>กำไร</td>
            </tr>

            @foreach($items['personnels'][$plan] as $item)
                <tr>
                    <td>{{ $item['expense_name'] }}</td>
                    <td align="right" class="Number">{{ number_format($item['sale']) }}</td>
                    <td align="right" class="Number">{{ number_format($item['cost']) }}</td>
                    <td align="right" class="Number">{{ $item['quantity'] }}</td>
                    <td>{{ $item['expense_unit'] }}</td>
                    <td align="right" class="Number">{{ $item['day'] }}</td>
                    <td align="right" class="Number">{{ number_format($item['item_total_sale']) }}</td>
                    <td align="right" class="Number">{{ number_format($item['item_total_cost']) }}</td>
                    <td align="right" class="Number">{{ number_format($item['item_total_profit']) }}</td>
                </tr>
            @endforeach
        @endif

        {{--Conferences--}}
        @if(isset($items['conferences'][$plan]))

            <tr>
                <td colspan="9" valign="middle" class="TitlePriceList">
                    ห้องประชุม
                </td>
            </tr>

            <tr class="RowHeader">
                <td>รายการ</td>
                <td>ขาย</td>
                <td>ทุน</td>
                <td>จำนวน</td>
                <td>หน่วย</td>
                <td>วัน</td>
                <td>รวมขาย</td>
                <td>รวมทุน</td>
                <td>กำไร</td>
            </tr>

            @foreach($items['conferences'][$plan] as $item)
                <tr>
                    <td>{{ $item['expense_name'] }}</td>
                    <td align="right" class="Number">{{ number_format($item['sale']) }}</td>
                    <td align="right" class="Number">{{ number_format($item['cost']) }}</td>
                    <td align="right" class="Number">{{ $item['quantity'] }}</td>
                    <td>{{ $item['expense_unit'] }}</td>
                    <td align="right" class="Number">{{ $item['day'] }}</td>
                    <td align="right" class="Number">{{ number_format($item['item_total_sale']) }}</td>
                    <td align="right" class="Number">{{ number_format($item['item_total_cost']) }}</td>
                    <td align="right" class="Number">{{ number_format($item['item_total_profit']) }}</td>
                </tr>
            @endforeach
        @endif

        {{--Location Facilities--}}
        @if(isset($items['location_facilities'][$plan]))

            <tr>
                <td colspan="9" valign="middle" class="TitlePriceList">
                    อุปกรณ์ประจำสำนักงานหรือสถานที่
                </td>
            </tr>

            <tr class="RowHeader">
                <td>รายการ</td>
                <td>ขาย</td>
                <td>ทุน</td>
                <td>จำนวน</td>
                <td>หน่วย</td>
                <td></td>
                <td>รวมขาย</td>
                <td>รวมทุน</td>
                <td>กำไร</td>
            </tr>

            @foreach(array_keys($items['location_facilities'][$plan]) as $location)
                <tr>
                    <td colspan="9" valign="middle" class="SubTitlePriceList">
                        {{ $location }}
                    </td>
                </tr>

                @foreach($items['location_facilities'][$plan][$location] as $item)
                    <tr>
                        <td>{{ $item['expense_name'] }}</td>
                        <td align="right" class="Number">{{ number_format($item['sale']) }}</td>
                        <td align="right" class="Number">{{ number_format($item['cost']) }}</td>
                        <td align="right" class="Number">{{ $item['quantity'] }}</td>
                        <td>{{ $item['expense_unit'] }}</td>
                        <td align="right" class="Number"></td>
                        <td align="right" class="Number">{{ number_format($item['item_total_sale']) }}</td>
                        <td align="right" class="Number">{{ number_format($item['item_total_cost']) }}</td>
                        <td align="right" class="Number">{{ number_format($item['item_total_profit']) }}</td>
                    </tr>
                @endforeach

            @endforeach
        @endif

        {{--Tool or Acitity Fee--}}
        @if(isset($items['tools'][$plan]))

            <tr>
                <td colspan="9" valign="middle" class="TitlePriceList">
                    วัสดุและอุปกรณ์ประกอบการเรียนรู้
                </td>
            </tr>

            <tr class="RowHeader">
                <td>รายการ</td>
                <td>ขาย</td>
                <td>ทุน</td>
                <td>จำนวน</td>
                <td>หน่วย</td>
                <td></td>
                <td>รวมขาย</td>
                <td>รวมทุน</td>
                <td>กำไร</td>
            </tr>

            @foreach($items['tools'][$plan] as $item)
                <tr>
                    <td>{{ $item['expense_name'] }}</td>
                    <td align="right" class="Number">{{ number_format($item['sale']) }}</td>
                    <td align="right" class="Number">{{ number_format($item['cost']) }}</td>
                    <td align="right" class="Number">{{ $item['quantity'] }}</td>
                    <td>{{ $item['expense_unit'] }}</td>
                    <td align="right" class="Number"></td>
                    <td align="right" class="Number">{{ number_format($item['item_total_sale']) }}</td>
                    <td align="right" class="Number">{{ number_format($item['item_total_cost']) }}</td>
                    <td align="right" class="Number">{{ number_format($item['item_total_profit']) }}</td>
                </tr>
            @endforeach
        @endif

        {{--Special Event--}}
        @if(isset($items['special_events'][$plan]))

            <tr>
                <td colspan="9" valign="middle" class="TitlePriceList">
                    กิจกรรมพิเศษ
                </td>
            </tr>

            <tr class="RowHeader">
                <td>รายการ</td>
                <td>ขาย</td>
                <td>ทุน</td>
                <td>จำนวน</td>
                <td>หน่วย</td>
                <td></td>
                <td>รวมขาย</td>
                <td>รวมทุน</td>
                <td>กำไร</td>
            </tr>

            @foreach($items['special_events'][$plan] as $item)
                <tr>
                    <td>{{ $item['expense_name'] }}</td>
                    <td align="right" class="Number">{{ number_format($item['sale']) }}</td>
                    <td align="right" class="Number">{{ number_format($item['cost']) }}</td>
                    <td align="right" class="Number">{{ $item['quantity'] }}</td>
                    <td>กิจกรรม</td>
                    <td align="right" class="Number"></td>
                    <td align="right" class="Number">{{ number_format($item['item_total_sale']) }}</td>
                    <td align="right" class="Number">{{ number_format($item['item_total_cost']) }}</td>
                    <td align="right" class="Number">{{ number_format($item['item_total_profit']) }}</td>
                </tr>
            @endforeach
        @endif

    @endif

    {{--Add Detail Of Budget--}}
    <?php
        $budget = LuBudget::where('party_id', '=', $party->id)->first();

        if ($plan=='A')
        {
            $summarySaleTotal = $budget->accommodation_sale_a+$budget->food_sale_a+$budget->car_sale_a+$budget->personnel_sale_a+$budget->conference_sale_a+$budget->tool_sale_a+$budget->special_event_sale_a+$budget->other_sale_a;
            $summaryCostTotal = $budget->accommodation_cost_a+$budget->food_cost_a+$budget->car_cost_a+$budget->personnel_cost_a+$budget->conference_cost_a+$budget->tool_cost_a+$budget->special_event_cost_a+$budget->other_cost_a;
        }
        else
        {
            $summarySaleTotal = $budget->accommodation_sale_b+$budget->food_sale_b+$budget->car_sale_b+$budget->personnel_sale_b+$budget->conference_sale_b+$budget->tool_sale_b+$budget->special_event_sale_b+$budget->other_sale_b;
            $summaryCostTotal = $budget->accommodation_cost_b+$budget->food_cost_b+$budget->car_cost_b+$budget->personnel_cost_b+$budget->conference_cost_b+$budget->tool_cost_b+$budget->special_event_cost_b+$budget->other_cost_b;
        }

        $chargeSaleTotal = round(($budget->charge/100)*$summarySaleTotal);
        $grandSaleTotal = $summarySaleTotal+$chargeSaleTotal;
        $discountSaleTotal = round($grandSaleTotal*($budget->discount/100));
        if ($discountSaleTotal>0)
        {
            $grandSaleTotal = $grandSaleTotal-$discountSaleTotal;
        }
    ?>

    <tr style="height: 30px;">
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>ราคาขาย</td>
        <td>ราคาทุน</td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td>ห้องพัก</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td align="right" class="Number">{{{ ($plan=='A') ? number_format($budget->accommodation_sale_a) : number_format($budget->accommodation_sale_b) }}}</td>
        <td align="right" class="Number">{{{ ($plan=='A') ? number_format($budget->accommodation_cost_a) : number_format($budget->accommodation_cost_b) }}}</td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td>อาหาร</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td align="right" class="Number">{{{ ($plan=='A') ? number_format($budget->food_sale_a) : number_format($budget->food_sale_b) }}}</td>
        <td align="right" class="Number">{{{ ($plan=='A') ? number_format($budget->food_cost_a) : number_format($budget->food_cost_b) }}}</td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td>ยานพาหนะ</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td align="right" class="Number">{{{ ($plan=='A') ? number_format($budget->car_sale_a) : number_format($budget->car_sale_b) }}}</td>
        <td align="right" class="Number">{{{ ($plan=='A') ? number_format($budget->car_cost_a) : number_format($budget->car_cost_b) }}}</td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td>วิทยากรและบุคลากร</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td align="right" class="Number">{{{ ($plan=='A') ? number_format($budget->personnel_sale_a) : number_format($budget->personnel_sale_b) }}}</td>
        <td align="right" class="Number">{{{ ($plan=='A') ? number_format($budget->personnel_cost_a) : number_format($budget->personnel_cost_b) }}}</td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td>ห้องประชุม</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td align="right" class="Number">{{{ ($plan=='A') ? number_format($budget->conference_sale_a) : number_format($budget->conference_sale_b) }}}</td>
        <td align="right" class="Number">{{{ ($plan=='A') ? number_format($budget->conference_cost_a) : number_format($budget->conference_cost_b) }}}</td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td>วัสดุและอุปกรณ์ประกอบการเรียนรู้</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td align="right" class="Number">{{{ ($plan=='A') ? number_format($budget->tool_sale_a) : number_format($budget->tool_sale_b) }}}</td>
        <td align="right" class="Number">{{{ ($plan=='A') ? number_format($budget->tool_cost_a) : number_format($budget->tool_cost_b) }}}</td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td>กิจกรรมพิเศษ</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td align="right" class="Number">{{{ ($plan=='A') ? number_format($budget->special_event_sale_a) : number_format($budget->special_event_sale_b) }}}</td>
        <td align="right" class="Number">{{{ ($plan=='A') ? number_format($budget->special_event_cost_a) : number_format($budget->special_event_cost_b) }}}</td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td>อื่นๆ</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td align="right" class="Number">{{{ ($plan=='A') ? number_format($budget->other_sale_a) : number_format($budget->other_sale_b) }}}</td>
        <td align="right" class="Number">{{{ ($plan=='A') ? number_format($budget->other_cost_a) : number_format($budget->other_cost_b) }}}</td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td>รวมค่าใช้จ่าย</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td align="right" class="Number">{{ number_format($summarySaleTotal) }}</td>
        <td align="right" class="Number">{{ number_format($summaryCostTotal) }}</td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td>ค่าบริหารจัดการ {{ $budget->charge }} %</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td align="right" class="Number">{{ number_format($chargeSaleTotal) }}</td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td>ส่วนลด {{ $budget->discount }} %</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td align="right" class="Number">{{ number_format($discountSaleTotal) }}</td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td>รวมค่าใช้จ่ายทั้งสิ้น</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td align="right" class="Number">{{ number_format($grandSaleTotal) }}</td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td>จำนวนคน</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td align="right" class="Number">{{ $party->people_quantity }}</td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td>รวมราคาขาย</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td align="right" class="Number">{{ number_format($grandSaleTotal) }}</td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td>รวมราคาทุน</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td align="right" class="Number">{{ number_format($summaryCostTotal) }}</td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td>ส่วนต่าง(กำไร)</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td align="right" class="Number">{{ number_format($grandSaleTotal-$summaryCostTotal) }}</td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td>ราคาต่อหัว</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td align="right" class="Number">{{ number_format(ceil($grandSaleTotal/$party->people_quantity)) }}</td>
        <td></td>
        <td></td>
    </tr>

    <tr>
        <td></td>
        <td>กำไร %</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td align="right" class="Number">{{ round((($grandSaleTotal-$summaryCostTotal)/$grandSaleTotal)*100) }}</td>
        <td>%</td>
        <td></td>
    </tr>

</table>

</html>