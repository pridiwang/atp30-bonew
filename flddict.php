<?php
function flddict($in){
	$in=strtolower($in);
	$dict=array('id'=>'หมายเลข', 'code'=>'รหัส','vehicle'=>'รถ','plate'=>'ทะเบียนรถ',  'drivers'=>'พขร.', 'driver'=>'พขร','date'=>'วันที่', 'description'=>'รายละเอียด', 'sparepart'=>'อะไหล่','mechanic'=>'ช่าง', 'note'=>'บันทึก', 'milage'=>'เลขไมล์', 'status'=>'สถานะ', 'plan_date'=>'แผนวันที่', 'type'=>'ประเภท', 'parts'=>'อะไหล่', 'qty'=>'จำนวน', 'cost'=>'ต้นทุน/หน่วย', 'workorder'=>'ใบสั่งซ่อม', 'sparepart'=>'อะไหล่', 'request_by'=>'ผู้แจ้ง', 'request_date'=>'วันที่แจ้ง','insurance'=>'ประกัน', 'mobile'=>'มือถือ', 'amount'=>'ยอดเงิน', 'liter'=>'ลิตร', 'price'=>'ราคา', 'time'=>'เวลา' ,'date_time'=>'วันที่/เวลา', 'last_milage'=>'ไมล์ครั้งที่แล้ว' ,'consumption'=>'กม./ลิตร', 'total_milage'=>'กม.','labor'=>'ค่าแรง', 'start_time'=>'เวลา', 'name'=>'ชื่อ' , 'in_out'=>'เข้า/ออก', 'no'=>'เที่ยว#', 'customer'=>'ลูกค้า', 'route'=>'สาย' , 'trip'=>'เที่ยว', 'cost'=>'ต้นทุน', 'allowance'=>'เบี้ยเลี้ยง', 'standard_distance'=>'ระยะทาง', 'fuel'=>'น้ำมัน', 'income'=>'รายได้' , 'income_km'=>'กม.ทำงาน','total_km'=>'ระยะทางทั้งหมด','income_km/run'=>'Y Ratio','prb'=>'พรบ.', 'register'=>'ทะเบียน', 'installment'=>'ค่างวด', 'class1_3'=>'ค่าประกัน', 'tire_position'=>'ตำแหน่งยาง', 'tire_no'=>'หมายเลขยาง', 'used_km'=>'ใช้มาแล้ว', 'remain_km'=>'อายุคงเหลือ'
,'FL'=>'หน้าซ้าย','FR'=>'หน้าขวา','RLI'=>'หลังซ้ายใน','RRI'=>'หลังขวาใน','RLO'=>'หลังซ้ายนอก', 'RRO'=>'หลังขวานอก','engine1'=>'เครื่อง1','engine2'=>'เครื่อง2', 'air1'=>'แอร์1','air2'=>'แอร์2', 'radio'=>'เครื่องเสียง', 'customer_name'=>'ชื่อลูกค้า', 'km'=>'กม.', 'passengers'=>'ผู้โดยสาร', 'plan_start'=>'เวลา', 'control_speed'=>'ควบคุมความเร็ว','max_speed'=>'ความเร็วสูงสุด','actual_start'=>'เริ่มจริง', 'actual_finish'=>'เสร็จสิ้น', 'plan_by'=>'โดย', 'trips'=>'เที่ยว', 'employee'=>'พนักงาน','training'=>'ฝึกอบรม','history'=>'ประวัติ', 'course'=>'อบรม', 'title'=>'คำนำหน้า' , 'address'=>'ที่อยู่', 'card_id'=>'เลขบัตรประชาชน', 'card_address'=>'ที่อยู่ตามบัตร', 'birth'=>'วันเกิด', 'employed'=>'วันที่เริ่มงาน','education'=>'การศึกษา', 'position'=>'ตำแหน่ง','age'=>'อายุ','resign'=>'ลาออก','experience'=>'ประสบการณ์','daily_distance'=>'กม.ต่อวัน','tax_id'=>'เลขผู้เสียภาษี', 'speed'=>'ความเร็ว','resign_date'=>'วันที่ลาออก','l'=>'สาย','e'=>'ออกก่อน','a'=>'ขาด','lv'=>'ลา','o'=>'ปกติ','in_time'=>'เวลาเข้า','out_time'=>'เวลาออก','comment'=>'ความเห็น','badgenumber'=>'รหัส','remark'=>'หมายเหตุ','detail'=>'รายละเอียด', 'investigation'=>'ผลการตรวจสอบ','actions'=>'แผนการป้องกัน', 'license_expire_date'=>'ใบขับขี่หมดอายุ' ,'register_expire'=>'ทะเบียน หมดอายุ', 'prb_expire'=>'พรบ หมดอายุ', 'insurance_expire'=>'ประกัน หมดอายุ', 'bus'=>'รถ','request_description'=>'รายละเอียดแจ้ง','wo#'=>'WO#','plan_date'=>'วันที่ซ่อม','plan_location'=>'ทีมช่าง','done_complete'=>'งานเสร็จตามแผน','plan_detail'=>'รายการซ่อม', 'done_part'=>'อะไหล่ใช้ตามกำหนด', 'done_parts'=>'อะไหล่ขาดเกิน','plan_parts'=>'อะไหล่', 'plan_need_photo'=>'ต้องการรูปถ่าย','done_more'=>'ไม่มีงานเพิ่มเติม', 'done_extra'=>'งานเพิ่มเติม','done_comment'=>'ความคิดเห็น','done_photos'=>'รูปภาพ','done_reason'=>'เหตุผล','done_milage'=>'เลขไมล์','plan_photo_needed'=>'รูปที่ต้องการ', 'fuel_plan'=>'แผนการเติมน้ำมัน','result'=>'ผล','level'=>'ปริมาณ','ws_alcohol'=>'ตรวจสอบแอลกอฮอล์','ws_drug'=>'ตรวจสอบสารเสพติด','ws_busweek'=>'ตรวจรถประจำสัปดาห์','ws_busmonth'=>'ตรวจรถประจำเดือน','update'=>'บันทึก','shop'=>'อู่','report_by'=>'รายงานโดย','balance'=>'คงเหลือ','book_no'=>'เล่มที่', 'number'=>'เลขที่','streq'=>'ใบเบิก/ใบโอน/ใบรับ','st'=>'SKU','to_fleet'=>'ส่งไปที่','request'=>'ใบเบิก','transfer'=>'ใบโอน','receive'=>'ใบรับ','new'=>'ใหม่','start_date'=>'เริ่มวันที่', 'subject'=>'เรื่อง','issue_by'=>'ผู้ออก NC','response_by'=>'ผู้รับผิดชอบ','issue_dept'=>'ฝ่าย', 'response_dept'=>'ฝ่าย','response_dept'=>'ฝ่าย','recorder'=>'ผู้บันทึก','report'=>'รายงาน','correct'=>'ดำเนินการแก้ไข','analyze'=>'วิเคราะห์สาเหตุ','prevent'=>'การป้องกัน','follow1'=>'ติดตามแก้ไขครั้งที่1','follow2'=>'ติดตามแก้ไขครั้งที่2','dcc_number'=>'Form NO.(DCC)','dcc_valid_date'=>'Valid Date(DCC)','dcc_approve_by'=>'Approved By (DCC)','report_detail'=>'รายละเอียดความไม่สอดคล้อง','report_date'=>'วันที่','corrective_detail'=>'การแก้ไข (Correction)','corrective_date'=>'วันที่','corrective_by'=>'แก้ไขเรียบร้อยโดย','analyse_detail'=>'การวิเคราะห์สาเหตุ (Root Cause)','analyse_by'=>'วิเคราะห์โดย','analyse_date'=>'วันที่','preventive_detail'=>'การป้องกันไม่ให้เกิดขึ้นซ้า ( Corective Action)','preventive_by'=>'การป้องกันเรียบร้อยแล้ว ลงชื่อ','preventive_date'=>'วันที่','follow1_detail'=>'กำหนดการติดตามแก้ไขครั้งที่ 1','follow1_by'=>'ผู้ติดตามลงชื่อ','follow1_date'=>'วันที่','follow2_detail'=>'กำหนดการติดตามแก้ไขครั้งที่ 2','follow2_by'=>'ผู้จัดการลงชื่อ','follow2_date'=>'วันที่','employees'=>'-','nc_audit'=>'ข้อบกพร่องที่พบจากการทำ Internal Audit','nc_other'=>'ข้อบกพร่องอื่นๆ','analyse'=>'วิเคราะห์', 'corrective'=>'แก้ไข', 'preventive'=>'ป้องกัน', 'follow1'=>'ติดตาม1', 'follow2'=>'ติดตาม2','complete'=>'เสร็จสิ้น','consumed'=>'Consumption (km/liter)','supervisor'=>'หัวหน้างาน', 'cancel'=>'ยกเลิก'

		);
	if(array_key_exists($in,$dict)){
		$out=$dict[$in];
	}else{
		$out=ucwords(str_replace("_"," ",$in));
	}
	return $out;
}
?>