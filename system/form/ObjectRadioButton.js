function initObjectRadioButtons(field, divid, radioids) {
    var updateRadios = function(animated) {
        for (var i in radioids) {
            if(radioids.hasOwnProperty(i)) {
                var id = radioids[i];
                var otherid = "displaycontainer_" + radioids[i];
                if (!$("#" + id).prop("checked")) {
                    if(animated) {
                        $("#" + otherid).slideUp("fast")
                    } else {
                        $("#" + otherid).css("display", "none");
                    }
                } else {
                    if(animated) {
                        $("#" + otherid).slideDown("fast")
                    } else {
                        $("#" + otherid).css("display", "block");
                    }
                }
            }
        }
    };

    $("#"+divid+" div > .option > input[type=radio]").change(function () {
        updateRadios(true);

        var currid = "displaycontainer_" + $(this).attr("id"),
            element = $("#" + currid);
        element.slideDown("fast");
        if (element.find(".form_field:first-child").find(".field").length > 0) {
            element.find(".form_field:first-child").find(".field").click();
        } else {
            element.find(".form_field:first-child").find(".input:not(type=checkbox)").click();
        }
    });

    updateRadios(false);
}
