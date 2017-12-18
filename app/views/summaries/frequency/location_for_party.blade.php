{{--รายงานที่ต้องการดูความถี่ ของการเข้ามาของคณะในแต่ละสถานที่โดย แบ่งออกเป็นปี เดือน--}}
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<html>

<head>
    <title>รายการการเข้าชมศึกษาดูงานของสถานที่ {{ date('d/m/Y') }}</title>
    {{--Use Bootstrap Framework--}}
    {{ HTML::style('bootstrap/css/bootstrap.min.css') }}
    {{ HTML::style('bootstrap/css/bootstrap-theme.min.css') }}
    {{ HTML::script('bootstrap/js/bootstrap.min.js') }}
</head>

<body>

<h2>รายงานจำนวนการเข้าชมศึกษาดูงานของ 52 ไร่ ประจำปี {{ $year }}</h2>

<table class="table table-bordered">
    <thead>
    <tr>
        <th class="col-md-3">เดือน</th>
        <th class="col-md-2">รวมจำนวนคณะ</th>
        <th class="col-md-4">รายชื่อคณะ</th>
        <th class="col-md-3">รวมจำนวนคน</th>
    </tr>
    </thead>
    <tbody>
        <?php
           $sum_visit = 0;
           $sum_party = 0;
        ?>
        @foreach($months as $month)

            <?php
                //split m y
                $q = explode('-', array_search($month, $months));
               //Query party visit location in year/month
                $count_visit_parties = 0;
                $count_visitors = 0;

                $visit_parties = Party::select(
                                    'parties.budget_code',
                                    'parties.customer_code',
                                    'parties.name',
                                    'parties.people_quantity AS qty'
                                )
                                ->leftJoin('lu_schedules AS ls', 'parties.id', '=', 'ls.party_id')
                                ->leftJoin('lu_schedule_tasks AS st', 'ls.id', '=', 'st.lu_schedule_id')
                                ->leftJoin('lu_schedule_task_locations AS tl', 'st.id', '=', 'tl.lu_schedule_task_id')
                                ->where(DB::raw('YEAR(parties.start_date)'), '=', $q[1])
                                ->where(DB::raw('MONTH(parties.start_date)'), '=', $q[0])
                                ->where('tl.location_id', '=', 66)//52 ไร่ ทดสอบ
                                ->groupBy('parties.budget_code', 'parties.customer_code', 'parties.name', 'parties.people_quantity')
                                ->orderBy('parties.name')
                                ->get();

                if ($visit_parties)
                {
                    $count_visit_parties = count($visit_parties);
                }
            ?>

            <tr>
                <td class="col-md-3">{{ $month }}</td>
                <td class="col-md-2">{{ $count_visit_parties }}</td>
                <td class="col-md-4">
                    @foreach($visit_parties as $party)
                        {{ $party->budget_code }}{{ $party->customer_code }} {{ $party->name }} ({{ $party->qty }}) <br/>

                        <?php
                            $count_visitors += $party->qty;
                        ?>

                    @endforeach
                </td>
                <td class="col-md-3">{{ $count_visitors }}</td>
            </tr>

            <?php
                $sum_visit += $count_visitors;
                $sum_party += $count_visit_parties;
            ?>

        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td>รวม</td>
            <td>{{ $sum_party }}</td>
            <td></td>
            <td>{{ $sum_visit }}</td>
        </tr>
    </tfoot>
</table>

</body>

</html>