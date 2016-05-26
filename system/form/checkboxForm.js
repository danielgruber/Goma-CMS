var form_initCheckbox = function(field, id){
    var obj = $("#" + id).gCheckBox();
    field.getValue = function(){
        return $("#" + field.id).prop("checked");
    };
    field.setValue = function(value) {
        $("#" + field.id).prop("checked", value);
        $("#" + field.id).change();
        return this;
    };
    field.getPossibleValuesAsync = function() {
        var deferred = $.Deferred();

        deferred.resolve([true, false]);

        return deferred.promise();
    };
};
