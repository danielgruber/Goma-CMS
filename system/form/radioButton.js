initRadioButton = function(field, divid) {
    var div = $("#" + divid);
    var isSelect = div.find("> .select-wrapper > select").length > 0;

    field.getValue = function() {
        if(isSelect) {
            return $("#" + divid).find("select").val();
        }

        return div.find("> .inputHolder > .option > input[type=radio]:checked").attr("value");
    };
    field.setValue = function(value) {
        if(isSelect) {
            var option = div.find("> .select-wrapper select option[value=\"" + value + "\"]");
            if(option.length == 1 && !option.prop("disabled")) {
                div.find("> .select-wrapper select option:selected").prop("selected", false);
                option.prop("selected", true);
                div.find("> .select-wrapper select").change();
            }
        } else {
            var radio = div.find("> .inputHolder > .option > input[value=\"" + value + "\"]");
            console.log(radio);
            if (radio.length == 1 && radio.parent().css("display") != "none") {
                div.find("> .inputHolder > .option > input[type=radio]:checked").prop("checked", false);
                radio.prop("checked", true);
                radio.change();
            }
        }
        return this;
    };
    field.getPossibleValuesAsync = function() {
        var deferred = $.Deferred();

        setTimeout(function(){
            var values = [];
            if(isSelect) {
                div.find("> .select-wrapper select option").each(function () {
                    if (!$(this).prop("disabled")) {
                        values.push($(this).attr("value"));
                    }
                });
            } else {
                div.find("> .inputHolder > .option > input[type=radio]").each(function () {
                    if ($(this).parent().css("display") != "none") {
                        values.push($(this).attr("value"));
                    }
                });
            }
            deferred.resolve(values);
        });

        return deferred.promise();
    };
};
