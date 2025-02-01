// function isAlphabaticKey(evt) {
//     var charCode = (evt.which) ? evt.which : event.keyCode;
//     if (!(charCode >= 65 && charCode <= 122) && (charCode == 32 && charCode == 0))
//         return false;
//     return true;
// }
function allLetter(event) {
    //alert("jhjh");
    var regex = new RegExp("^[a-zA-Z]+$");
    var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
    if (!regex.test(key)) {
        
        //event.preventDefault();
        return false;
    }
    else {
        //  alert("jhjh");
    }


}
function isNumberKey(evt) {
    var charCode = (evt.which) ? evt.which : event.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57))
        return false;
    return true;
}
function isEmailKey(evt)
{
    return (!pre)
}
function isDesimalNumberKey(evt) {
    var charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode > 31 && (charCode != 46 && (charCode < 48 || charCode > 57)))
        return false;
    return true;
}
function arr_diff(a1, a2) {

    var a = [], diff = [];

    for (var i = 0; i < a1.length; i++) {
        a[a1[i]] = true;
    }

    for (var i = 0; i < a2.length; i++) {
        if (a[a2[i]]) {
            delete a[a2[i]];
        } else {
            a[a2[i]] = true;
        }
    }

    for (var k in a) {
        diff.push(k);
    }

    return diff;
}