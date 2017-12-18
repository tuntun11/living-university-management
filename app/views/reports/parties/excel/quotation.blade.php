<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<html>

{{ HTML::style('assets/css/quotation.css') }}

<table>

    <tr>
        <td width="500" colspan="10"><img src="assets/img/logo-header.jpg"></td>
    </tr>

    <?php
        //change thai or english
        if ($lang=='th')
        {
            $strTitle1 = 'ใบเสนอราคาการศึกษาดูงาน แผน '.$plan;
            $strTitle2 = 'วันที่ '.ScheduleController::dateRangeStr($party->start_date, $party->end_date, true);
            $strVersion = '(ครั้งที่ '.$revision.' ออกเอกสารเมื่อ '.ScheduleController::dateRangeStr(date('Y-m-d'), date('Y-m-d'), true).')';
        }
        else
        {
            $strTitle1 = 'Quotation (Plan '.$plan.')';
            $strTitle2 = ScheduleController::dateRangeStr($party->start_date, $party->end_date, false, false, 'en');
            $strVersion = '(version '.$revision.' created at '.ScheduleController::dateRangeStr(date('Y-m-d'), date('Y-m-d'), false, false, 'en').')';
        }

        //query saved budgeting sale value
        $quotation = LuBudget::where('party_id', '=', $party->id)->first();
        //set customer code to show fill 912 in only quotation
        $strCustomerCode = ($lang=='th') ? 'รหัสลูกค้า  : 912 ' . $party->customer_code : 'Customer Code : 912' . $party->customer_code;
        //set param to show
        $saleGrandTotal = ($plan=='A') ? $quotation->grand_total_a : $quotation->grand_total_b;

        //get absorb
        $total_absorb = 0;
        $absorbs = LuBudgetAbsorb::where('party_id', '=', $party->id)->whereIsPlanB(($plan=='A') ? 0 : 1)->get();

        /*generate text to display เอาข้อมูลจากแท็บแยกประเภทดึงมาใช้งาน*/
		
		//กิจกรรม
        $strActivityChargeThText = "กิจกรรมศึกษาดูงานการพัฒนาขั้นต้นน้ำ-กลางน้ำ-ปลายน้ำ และองค์การบริหารส่วนตำบลแม่ฟ้าหลวง";
        $strActivityChargeEnText = "Study Visit, Learning Activities and Facilities fee";
		
        //ห้องพัก
        $strAccommodationChargeThText = "ที่พัก";
        if (isset($items['accommodations']) && count($items['accommodations'])>0)
        {
            $a = 1;
            $accommodations = $party->returnAccommodationQuantities($plan);
            foreach($accommodations as $accommodation)
            {
                if (count($accommodations)>1 && count($accommodations)==$a)
                {
                    $strAccommodationChargeThText .= 'และ';
                }
                $strAccommodationName = Location::find($accommodation['task'])->name;
                $strAccommodationChargeThText .= $strAccommodationName.' '.$accommodation['days'].' คืน ';

                $a++;
            }
            $strAccommodationChargeThText .= ' (รวมอาหารเช้า)';
        }
        $strAccommodationChargeEnText = "Accommodation";

        //อาหาร
        $strMealChargeThText = "อาหาร";
        if (count($party->returnMealQuantities($plan))>0)
        {
            $strMealChargeThText .= ' (';
            $m = 1;
            $allMeal = 0;
            foreach($party->returnMealQuantities($plan) as $meal)
            {
                if (count($party->returnMealQuantities($plan))>1 && count($party->returnMealQuantities($plan))==$m)
                {
                    $strMealChargeThText .= 'และ';
                }
                $strMealChargeThText .= Food::strMeal($meal['meal']).' '.$meal['quantity'].' มื้อ ';

                $m++;
                $allMeal += $meal['quantity'];
            }
            $strMealChargeThText .= ' รวมทั้งหมด '.$allMeal.' มื้อ)';
        }
        $strMealChargeEnText = "Food and Beverage";

        //รถยนต์
        $strTransportChargeThText = "การเดินทางตลอดระยะเวลาในการศึกษาดูงาน";
        if (isset($items['cars']) && count($items['cars'])>0)
        {
            $car_facilitators = array_keys($items['cars'][$plan]);
            //ลูปผู้ให้บริการรถยนต์
            foreach($car_facilitators as $car_facilitator)
            {
                $c = 1;
                $strTransportChargeThText .= '('.$car_facilitator.':';
                //ลูปประเภทรถยนต์
                foreach($items['cars'][$plan][$car_facilitator] as $car)
                {
                    if (count($items['cars'][$plan][$car_facilitator])>1 && count($items['cars'][$plan][$car_facilitator])==$c)
                    {
                        $strTransportChargeThText .= 'และ';
                    }
                    $strTransportChargeThText .= $car['expense_name'].' '.$car['quantity'].' '.$car['expense_unit'].' จำนวน '.$car['day'].' วัน ';
                    $c++;
                }
                $strTransportChargeThText .= ')';
            }
        }
        $strTransportChargeEnText = "Transportation to all study visit sites";
    ?>

    <tr>
        <td colspan="10" align="center" class="Title">{{ $strTitle1 }}</td>
    </tr>

    <tr>
        <td colspan="10" align="center" class="Title">{{{ ($lang=='th') ? 'คณะ' : '' }}} {{ $party->name }}</td>
    </tr>

    <tr>
        <td colspan="10" align="center" class="Title">{{ $strTitle2 }}</td>
    </tr>

    <tr>
        <td colspan="7"></td>
        <td colspan="3" style="text-align: right; font-weight: bold;">{{ $strCustomerCode }}</td>
    </tr>

    <tr>
        <td colspan="7" class="ListHeader">{{{ ($lang=='th') ? 'รายการ' : 'Description' }}}</td>
        <td colspan="3" class="ListHeader" align="right">{{{ ($lang=='th') ? 'บาท' : 'THB' }}}</td>
    </tr>
	
	{{--Title แสดงหัวคำ--}}
    @if ($lang=='th')
		<tr>
			<td colspan="7" valign="top" style="font-weight: bold;">ค่าใช้จ่ายในการศึกษาดูงานจำนวน {{ $party->people_quantity }} ท่าน</td>
			<td colspan="3" valign="top"></td>
		</tr>
	@else
		<tr>
			<td colspan="7" valign="top" style="font-weight: bold;">Study visit programme at Doi Tung Development Project for {{ $party->people_quantity }} pax</td>
			<td colspan="3" valign="top"></td>
		</tr>
    @endif
	
	<tr>
        <td colspan="7" class="DetailList" valign="top">{{{ ($lang=='th') ? $strActivityChargeThText : $strActivityChargeEnText }}}</td>
        <td colspan="3" valign="top"></td>
    </tr>

	{{--วิทยากร โชว์เฉพาะภาษาไทย--}}
    @if ($lang=='th')
		@if(isset($items['personnels']) && count($items['personnels'])>0)
			<tr>
				<td colspan="7" class="DetailList" valign="top">ค่าวิทยากรและเอกสารประกอบการดูงาน</td>
				<td colspan="3" valign="top"></td>
			</tr>
		@endif
    @endif

    @if(isset($items['accommodations']) && count($items['accommodations'])>0)
        <tr>
            <td colspan="7" class="DetailList" valign="top">{{{ ($lang=='th') ? $strAccommodationChargeThText : $strAccommodationChargeEnText }}}</td>
            <td colspan="3" valign="top"></td>
        </tr>
    @endif

    @if (count($party->returnMealQuantities($plan))>0)
        <tr>
            <td colspan="7" class="DetailList" valign="top">{{{ ($lang=='th') ? $strMealChargeThText : $strMealChargeEnText }}}</td>
            <td colspan="3" valign="top"></td>
        </tr>
    @endif

    @if (isset($items['cars']) && count($items['cars'])>0)
        <tr>
            <td colspan="7" class="DetailList" valign="top">{{{ ($lang=='th') ? $strTransportChargeThText : $strTransportChargeEnText }}}</td>
            <td colspan="3" valign="top"></td>
        </tr>
    @endif

    @if ($quotation->discount>0)
        <?php
            $discount = round($saleGrandTotal*($quotation->discount/100));
            $saleGrandTotal = $saleGrandTotal-$discount;
        ?>

        <tr>
            <td colspan="7" valign="top">{{{ ($lang=='th') ? 'หักส่วนลด '.(int)$quotation->discount.'%' : 'Discount '.(int)$quotation->discount.'%' }}}</td>
            <td colspan="3" valign="top"></td>
        </tr>

    @endif

    @if (count($absorbs)>0)
        <?php
            foreach($absorbs as $absorb)
            {
                $total_absorb += $absorb->total;
            }

            $actualGrandTotal = $saleGrandTotal+$total_absorb;
        ?>

        <tr>
            <td colspan="7" valign="top">{{{ ($lang=='th') ? 'ราคารวม' : 'Total ' }}}</td>
            <td colspan="3" align="right" valign="top">{{ number_format ( $actualGrandTotal, 0, ".", "," ) }}</td>
        </tr>
        <tr>
            <td colspan="7" valign="top">{{{ ($lang=='th') ? 'มูลนิธิแม่ฟ้าหลวงฯ สนับสนุนค่าใช้จ่ายเป็นจำนวน' : 'Absorb by MFLF ' }}}</td>
            <td colspan="3" align="right" valign="top">{{ '-'.number_format ( $total_absorb, 0, ".", "," ) }}</td>
        </tr>

    @endif

    <tr>
        <td colspan="7" valign="top">{{{ ($lang=='th') ? 'ราคารวมทั้งสิ้น' : 'Grand Total' }}}</td>
        <td colspan="3" align="right" valign="top">{{ number_format ( $saleGrandTotal, 0, ".", "," ) }}</td>
    </tr>

    {{--ราคารวมทั้งสิ้น--}}
    <tr>
        <td colspan="10" class="SummaryString">{{{ ($lang=='th') ? 'จำนวนเงินทั้งสิ้น '.DocumentController::num2wordsThai($saleGrandTotal) : 'Grand Total '.DocumentController::convert_number_to_words($saleGrandTotal) }}}</td>
    </tr>

    <tr>
        <td colspan="10" style="text-align: right; font-size: 12px;">{{{ ($lang=='th') ? 'ราคานี้กรุณาชำระภายใน 7 วันหลังที่ได้รับบริการ' : 'Payment due within 7 days after receiving the service.'  }}}</td>
    </tr>

    {{--ส่วน Footer--}}
    <tr>
        <td colspan="6" align="center">{{{ ($lang=='th') ? 'ข้าพเจ้ายอมรับเงื่อนไขในการชำระเงิน' : 'I agree to the terms and conditions.'  }}}</td>
        <td colspan="4" align="center">{{{ ($lang=='th') ? '' : 'Quotation prepared by'  }}}</td>
    </tr>

    <tr>
        <td colspan="6"></td>
        <td colspan="4"></td>
    </tr>

    <tr>
        <td colspan="6" align="center">(______________________________)</td>
        <td colspan="4"></td>
    </tr>

    <tr>
        <td colspan="6" align="center">{{{ ($lang=='th') ? 'ผู้มีอำนาจลงนาม' : 'Signature of Authorized Person' }}}</td>
        <td colspan="4" align="center">{{{ ($lang=='th') ? Auth::user()->getSignName(0) : Auth::user()->getSignName(1) }}}</td>
    </tr>

    <tr>
        <td colspan="6" align="center">{{{ ($lang=='th') ? 'ตำแหน่ง _________________________' : 'Position __________________________' }}}</td>
        <td colspan="4" align="center">{{{ ($lang=='th') ? Auth::user()->getDepartment(0) : Auth::user()->getDepartment(1) }}}</td>
    </tr>

    <tr>
        <td colspan="6" align="center">{{{ ($lang=='th') ? 'หน่วยงาน _________________________' : 'Organization __________________________' }}}</td>
        <td colspan="4" align="center">{{{ ($lang=='th') ? 'มูลนิธิแม่ฟ้าหลวง ในพระบรมราชูปถัมภ์' : 'Maefahluang Foundation Under Royal Patronage' }}}</td>
    </tr>

    <tr>
        <td colspan="6" align="center">{{{ ($lang=='th') ? 'วันที่ ____________________________' : 'Date ____________________________' }}}</td>
        <td colspan="4" align="center">{{{ ($lang=='th') ? ScheduleController::dateRangeStr(date('Y-m-d'), date('Y-m-d'), true) : ScheduleController::dateRangeStr(date('Y-m-d'), date('Y-m-d'), false, false, 'en') }}}</td>
    </tr>

    <tr>
        <td colspan="10">{{{ ($lang=='th') ? 'เมื่อลงนามแล้วขอความกรุณาส่งโทรสารกลับมาที่ 02-253-6999' : 'Please sign and fax to 02-253-6999' }}}</td>
    </tr>
	
	<tr>
        <td colspan="10">{{{ ($lang=='th') ? 'หรือ '.Auth::user()->email : 'or email : '.Auth::user()->email }}}</td>
    </tr>

</table>

</html>