/**
 * Created by Wuttichai on 5/27/2015.
 * This is javascript function
 */

//function with bootstrap3-alerts
function successBoxAlert(title, msg, element)
{
    var message = msg;

    var html = '';

    html += '<div class="alert alert-success alert-dismissible" role="alert">';
    html += '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
    html += '<h4>'+title+'</h4>';
    html += message;
    html += '</div>';

    $(element).html(html);

}

function errorBoxAlert(title, msg, element)
{
    var message = "";

    if( Object.prototype.toString.call( msg ) === '[object Array]' )
    {
        message += '<ul>';
        $(msg).each(function (index, item) {
            if (item != undefined || item != '')
            {
                message += '<li>' + item + '</li>';
            }
        });
        message += '</ul>';
    }
    else if( Object.prototype.toString.call( msg ) === '[object Object]' )
    {
        //message = 'Error กรุณาติดต่อ Admin';
        var array = $.map(msg, function(value, index) {
            return [value];
        });

        message += '<ul>';
        $(array).each(function (index, item) {
            if( Object.prototype.toString.call( item ) === '[object Array]' )
            {
                $(item).each(function (i, t) {
                    if (t != undefined || t != '')
                    {
                        message += '<li>' + t + '</li>';
                    }
                });
            }
            else
            {
                message += '<li>' + item + '</li>';
            }
        });
        message += '</ul>';
    }
    else
    {
        message = msg;
    }

    var html = '';

    html += '<div class="alert alert-danger alert-dismissible" role="alert">';
    html += '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
    html += '<h4>'+title+'</h4>';
    html += message;
    html += '</div>';

    $(element).html(html);

}

//function with bootstrap alert popup plugin
function confirmButton(title, msg, buttons)
{
    BootstrapDialog.show({
        title: title,
        message: msg,
        buttons: buttons
    });
}

function successButton(title, msg, buttons)
{
    BootstrapDialog.show({
        type: 'type-success',
        title: title,
        message: msg,
        buttons: buttons
    });
}

function warningButton(title, msg, buttons)
{
    BootstrapDialog.show({
        type: 'type-warning',
        title: title,
        message: msg,
        buttons: buttons
    });
}

//open a pre load step
function openPreLoad(msg)
{
    var d = BootstrapDialog.show({
        type: 'type-default',
        title: 'กรุณารอสักครู่',
        message: '<i class="fa fa-spinner fa-spin fa-lg fa-fw"></i> <span>กำลังโหลดข้อมูล...</span>',
        closable: false
    });
}

//close a pre load step
function closePreLoad()
{
    BootstrapDialog.close();
}

function successAlert(title, msg)
{
    var d = BootstrapDialog.show({
        type: 'type-success',
        title: title,
        message: msg,
        closable: false
    });
    //show in 1 sec and then disappear
    setTimeout(function() {
        d.close();
    }, 2000);
}

function warningAlert(title, msg)
{
    var d = BootstrapDialog.show({
        type: 'type-warning',
        title: title,
        message: msg,
        closable: false
    });
    //show in 1 sec and then disappear
    setTimeout(function() {
        d.close();
    }, 2000);
}

function errorAlert(title, msg)
{
    var message = "";

    if( Object.prototype.toString.call( msg ) === '[object Array]' )
    {
        message += '<ul>';
        $(msg).each(function (index, item) {
            if (item != undefined || item != '')
            {
                message += '<li>' + item + '</li>';
            }
        });
        message += '</ul>';
    }
    else if( Object.prototype.toString.call( msg ) === '[object Object]' )
    {
        //message = 'Error กรุณาติดต่อ Admin';
        var array = $.map(msg, function(value, index) {
            return [value];
        });

        message += '<ul>';
        $(array).each(function (index, item) {
            if( Object.prototype.toString.call( item ) === '[object Array]' )
            {
                $(item).each(function (i, t) {
                    if (t != undefined || t != '')
                    {
                        message += '<li>' + t + '</li>';
                    }
                });
            }
            else
            {
                message += '<li>' + item + '</li>';
            }
        });
        message += '</ul>';
    }
    else
    {
        message = msg;
    }

    var d = BootstrapDialog.show({
        type: 'type-danger',
        title: title,
        message: message,
        buttons: [{
            id: 'btn-close-dialog',
            icon: 'fa fa-close',
            label: 'ปิด',
            cssClass: 'btn-danger',
            autospin: false,
            action: function(dialogRef){
                dialogRef.close();
            }
        }]
    });
    //show in 5 sec and then disappear
    setTimeout(function() {
        d.close();
    }, 5000);
}

/*return month thai name*/
function monthThai(m)
{
	var monthThaiName = '';
	
	if (m=='01' || m=='1' || m==1)
	{
		monthThaiName = 'มกราคม';
	}
	if (m=='02' || m=='2' || m==2)
	{
		monthThaiName = 'กุมภาพันธ์';
	}
	if (m=='03' || m=='3' || m==3)
	{
		monthThaiName = 'มีนาคม';
	}
	if (m=='04' || m=='4' || m==4)
	{
		monthThaiName = 'เมษายน';
	}
	if (m=='05' || m=='5' || m==5)
	{
		monthThaiName = 'พฤษภาคม';
	}
	if (m=='06' || m=='6' || m==6)
	{
		monthThaiName = 'มิถุนายน';
	}
	if (m=='07' || m=='7' || m==7)
	{
		monthThaiName = 'กรกฎาคม';
	}
	if (m=='08' || m=='8' || m==8)
	{
		monthThaiName = 'สิงหาคม';
	}
	if (m=='09' || m=='9' || m==9)
	{
		monthThaiName = 'กันยายน';
	}
	if (m=='10' || m==10)
	{
		monthThaiName = 'ตุลาคม';
	}
	if (m=='11' || m==11)
	{
		monthThaiName = 'พฤศจิกายน';
	}
	if (m=='12' || m==12)
	{
		monthThaiName = 'ธันวาคม';
	}
	
	return monthThaiName;
}

/*set thousand separator*/
function addThousandsSeparator(value)
{
    return value.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
}

/*Copy Element To Clipboard*/
function copyToClipboard(elementId) 
{

  // Create a "hidden" input
  var aux = document.createElement("input");

  // Assign it the value of the specified element
  aux.setAttribute("value", document.getElementById(elementId).innerHTML);

  // Append it to the body
  document.body.appendChild(aux);

  // Highlight its content
  aux.select();

  // Copy the highlighted text
  document.execCommand("copy");

  // Remove it from the body
  document.body.removeChild(aux);

}

/*iseet param*/
function isset()
{
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: FremyCompany
    // +   improved by: Onno Marsman
    // +   improved by: Rafał Kukawski
    // *     example 1: isset( undefined, true);
    // *     returns 1: false
    // *     example 2: isset( 'Kevin van Zonneveld' );
    // *     returns 2: true

    var a = arguments,
        l = a.length,
        i = 0,
        undef;

    if (l === 0)
    {
        throw new Error('Empty isset');
    }

    while (i !== l)
    {
        if (a[i] === undef || a[i] === null)
        {
            return false;
        }
        i++;
    }
    return true;
}

//get json object
function getObjects(obj, key, val)
{
    var objects = [];
    for (var i in obj) {
        if (!obj.hasOwnProperty(i)) continue;
        if (typeof obj[i] == 'object') {
            objects = objects.concat(getObjects(obj[i], key, val));
        } else if (i == key && obj[key] == val) {
            objects.push(obj);
        }
    }
    return objects;
}

//return pattern of party information in expanding grid
function getPartyExpansion(d, full)
{
    var coordinators = "";

    $.each(d.coordinators, function(index, val){
        coordinators += val.name + ', เบอร์ติดต่อ:' + val.mobile + ', อีเมล:' + val.email;
        coordinators += '<br/>';
    });

    // `d` is the original data object for the row
    var str = '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px; background: none; background-color:transparent; width: 100%;">';

        if (full!=undefined)
        {
            //show fully data except name
            if (full==1)
            {
                str += '<tr>'+
                '<td width="13%" valign="top"><strong>รหัสคำร้อง</strong></td>'+
                '<td width="20%" valign="top">' + d.request_code + '</td>'+
                '<td width="13%" valign="top"><strong>ช่วงวันที่มา</strong></td>'+
                '<td width="20%" valign="top">' + d.type + '</td>'+
                '<td width="14%" valign="top"><strong>ผู้ส่งคำร้อง</strong></td>'+
                '<td width="20%" valign="top">' + d.request_person_name + '</td>'+
                '</tr>';
            }
        }

        str += '<tr>'+
        '<td width="13%" valign="top"><strong>มาจากประเทศ</strong></td>'+
        '<td width="20%" valign="top">' + d.country + '</td>'+
        '<td width="13%" valign="top"><strong>ประเภท</strong></td>'+
        '<td width="20%" valign="top">' + d.type + '</td>'+
        '<td width="14%" valign="top"><strong>ส่งคำร้องเมื่อ</strong></td>'+
        '<td width="20%" valign="top">' + d.created_at + '</td>'+
        '</tr>'+
        '<tr>'+
        '<td width="13%" valign="top"><strong>จำนวนผู้ร่วม</strong></td>'+
        '<td width="20%" valign="top">' + d.qty + '</td>'+
        '<td width="13%" valign="top"><strong>วัตถุประสงค์</strong></td>'+
        '<td width="20%" valign="top">' + d.objectives + '</td>'+
        '<td width="14%" valign="top"><strong>ไฟล์แนบ</strong></td>'+
        '<td width="20%" valign="top">' + d.file + '</td>'+
        '</tr>'+
        '<tr>'+
        '<td width="13%" valign="top"><strong>ประเด็นที่สนใจ</strong></td>'+
        '<td width="20%" valign="top">' + d.interested + '</td>'+
        '<td width="13%" valign="top"><strong>ความคาดหวัง</strong></td>'+
        '<td width="20%" valign="top">' + d.expected + '</td>'+
        '<td width="14%" valign="top"><strong>การเข้าร่วม</strong></td>'+
        '<td width="20%" valign="top">' + d.joined + '</td>'+
        '</tr>'+
        '<tr>'+
        '<td width="13%" valign="top"><strong>พื้นที่ดูงาน</strong></td>'+
        '<td valign="top" colspan="5">' + d.bases + '</td>'+
        '</tr>'+
        '<tr>'+
        '<td width="13%" valign="top"><strong>การชำระเงิน</strong></td>'+
        '<td valign="top" colspan="5">' + d.paid_method + '</td>'+
        '</tr>'+
        '<tr>'+
        '<td valign="top" colspan="6"><strong>ผู้ประสานงานของคณะที่มา</strong> <br/>'+
        coordinators +
        '</td>'+
        '</tr>'+
        '<tr>'+
        '<td valign="top" colspan="6"><strong>รายละเอียด(เพิ่มเติม)</strong> <br/>'+
        d.objective_detail +
        '</td>'+
        '</tr>'+
        '</table>';

        return str;
}

//check array compare to equal
function checkIfEqual(arr1, arr2) {
    if (arr1.length != arr2.length) {
        return false;
    }
    //sort them first, then join them and just compare the strings
    return arr1.sort().join() == arr2.sort().join();
}
