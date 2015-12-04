function initObjectRadioButtons(divid, radioids) {
    for (var i in radioids) {
        if(radioids.hasOwnProperty(i)) {
            var id = radioids[i];
            if (!$("#" + id).prop("checked")) {
                $("#displaycontainer_" + id).css("display", "none");
            }
        }
    }

    console.log(divid);

    $("#"+divid+" div > .option > input[type=radio]").click(function () {
        for (var i in radioids) {
            if(radioids.hasOwnProperty(i)) {
                var id = radioids[i];
                if (!$("#" + id).prop("checked")) {
                    var otherid = "displaycontainer_" + radioids[i];
                    $("#" + otherid).slideUp("fast");
                }
            }
        }

        var currid = "displaycontainer_" + $(this).attr("id"),
            element = $("#" + currid);
        element.slideDown("fast");
        if (element.find(".form_field:first-child").find(".field").length > 0) {
            element.find(".form_field:first-child").find(".field").click();
        } else {
            element.find(".form_field:first-child").find(".input").click();
        }
    });
}
