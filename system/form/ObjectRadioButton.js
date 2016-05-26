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
            element.find(".form_field:first-child").find(".input").click();
        }
    });

    updateRadios(false);

    field.getValue = function() {
        return $("#" + divid).find("input[type=radio]:checked").attr("value");
    };
    field.setValue = function(value) {
        var field = $("#" + divid);
        var radio =  field.find("input[value="+value+"]");
        if(radio.length == 1 && radio.parent().css("display") != "none") {
            field.find("input[type=radio]:checked").prop("checked", false);
            radio.prop("checked", true);
            updateRadios(true);
        }
        return this;
    };
    field.getPossibleValuesAsync = function() {
        var deferred = $.Deferred();

        setTimeout(function(){
            var values = [];
            var field = $("#" + divid);
            field.find("input[type=radio]").each(function(){
                if($(this).parent().css("display") != "none") {
                    values.push($(this).attr("value"));
                }
            });
            deferred.resolve(values);
        });

        return deferred.promise();
    };
}
