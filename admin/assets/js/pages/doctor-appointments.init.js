/*
Template Name: Dosix - Admin & Dashboard Template
Author: Themesbrand
Website: https://Themesbrand.com/
Contact: Themesbrand@gmail.com
File: Appointments List init js
*/

var perPage = 8;

// checkAll
var checkAll = document.getElementById("checkAll");
if (checkAll) {
    checkAll.onclick = function () {
        var checkboxes = document.querySelectorAll('.form-check-all input[type="checkbox"]');
        var checkedCount = document.querySelectorAll('.form-check-all input[type="checkbox"]:checked').length;
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = this.checked;
            if (checkboxes[i].checked) {
                checkboxes[i].closest("tr").classList.add("table-active");
            } else {
                checkboxes[i].closest("tr").classList.remove("table-active");
            }

            if (checkboxes[i].closest("tr").classList.contains("table-active")) {
                (checkedCount > 0) ? document.getElementById("remove-actions").classList.add("d-none") : document.getElementById("remove-actions").classList.remove("d-none");
            } else {
                (checkedCount > 0) ? document.getElementById("remove-actions").classList.add("d-none") : document.getElementById("remove-actions").classList.remove("d-none");
            }
        }
    };
}

//Appointments Table

var options = {
    valueNames: [
        'id',
        "patientName",
        'dateTime',
        "email",
        "number",
        "diseases",
        "status"
    ],
    page: perPage,
    pagination: true,
    plugins: [
        ListPagination({
            left: 2,
            right: 2,
        }),
    ],
};

// Init list
var subscriptionList = new List("appointmentList", options).on("updated", function (list) {
    if (document.getElementsByClassName("noresult") && document.getElementsByClassName("noresult")[0]) {
        list.matchingItems.length == 0 ?
            (document.getElementsByClassName("noresult")[0].style.display = "block") :
            (document.getElementsByClassName("noresult")[0].style.display = "none");

        if (list.matchingItems.length > 0) {
            document.getElementsByClassName("noresult")[0].style.display = "none";
        } else {
            document.getElementsByClassName("noresult")[0].style.display = "block";
        }
    }
});