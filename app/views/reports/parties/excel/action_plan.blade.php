<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<html>

{{ HTML::style('assets/css/actionPlan.css') }}

    <table>

        <tr>
            <td colspan="6" align="center" valign="middle" class="TitleExcel">
                Action Plan {{ $party->name }} จำนวน {{ $party->people_quantity }} ท่าน
            </td>
        </tr>

        <tr>
            <td colspan="6" align="center" valign="middle" class="TitleExcel">
                วันที่ {{ ScheduleController::dateRangeStr($party->start_date, $party->end_date, true) }}
            </td>
        </tr>

        <tr>
            <td colspan="6" valign="middle" class="TitleExcel">
                ผู้ประสานงานและรับคณะ : {{ $party->assignedCoordinator() }}
            </td>
        </tr>

        <tr>
            <td style="font-weight: bold; background-color: #c0c0c0;">วัน/เวลา</td>
            <td style="font-weight: bold; background-color: #c0c0c0;">กำหนดการ-รายละเอียด</td>
            <td style="font-weight: bold; background-color: #c0c0c0;">จำนวนคน</td>
            <td style="font-weight: bold; background-color: #c0c0c0;">จำนวนรถ</td>
            <td style="font-weight: bold; background-color: #c0c0c0;">ผู้รับผิดชอบ</td>
            <td style="font-weight: bold; background-color: #c0c0c0;">หมายเหตุ</td>
        </tr>

        {{--loop for create schedule for action plan--}}
        @foreach(array_keys($actions) as $day)

            <tr>
                <td colspan="2" class="headerRow">{{ ScheduleController::dateRangeStr($day, $day, true, true) }}</td>
                <td valign="top">&nbsp;</td>
                <td valign="top">&nbsp;</td>
                <td valign="top">&nbsp;</td>
                <td valign="top">&nbsp;</td>
            </tr>

            {{--loop for create task--}}
            @foreach($actions[$day] as $task)

                <tr>

                    @if($task['is_plan_b'])
                        <td valign="top" style="font-weight: bold;">{{ $task['time'] }}</td>
                        <td valign="top" style="font-weight: bold;">{{ $task['detail'] }}</td>
                    @else
                        <td valign="top">{{ $task['time'] }}</td>
                        <td valign="top">{{ $task['detail'] }}</td>
                    @endif

                    <td valign="top">&nbsp;</td>
                    <td valign="top">&nbsp;</td>
                    <td valign="top">&nbsp;</td>
                    <td valign="top">&nbsp;</td>
                </tr>

            @endforeach

        @endforeach

    </table>

</html>