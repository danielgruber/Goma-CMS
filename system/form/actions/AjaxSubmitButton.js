var initAjaxSubmitbutton = function(id, divId, formObject, field, url, appendix) {
    var button = $("#" + id);
    var container = $("#" + divId);
    var form = $("#" + formObject.id);

    button.click(function(){
        var eventb = jQuery.Event("beforesubmit");
        button.trigger(eventb);
        if ( eventb.result === false ) {
            return false;
        }
        var event = jQuery.Event("formsubmit");
        form.trigger(event);
        if ( event.result === false ) {
            return false;
        }

        button.css("display", "none");
        container.append("<img src=\"images/16x16/loading.gif\" alt=\"loading...\" class=\"loading\" />");
        $("body").css("cursor", "wait");

        goma.ui.updateFlexBoxes();
        $.ajax({
            url: url + appendix,
            type: "post",
            data: form.serialize() + "&" + encodeURIComponent($(this).attr("name")) + "=" + encodeURIComponent($(this).val()),
            dataType: "html",
            headers: {
                accept: "text/javascript; charset=utf-8"
            }
        }).always(function(){
            $(document.body).css("cursor", "default").css("cursor", "auto");
            container.find(".loading").remove();
            button.css("display", "inline");

            var eventb = jQuery.Event("ajaxresponded");
            form.trigger(eventb);

            goma.ui.updateFlexBoxes();
        }).done(function(script, textStatus, jqXHR){
            goma.ui.loadResources(jqXHR).done(function(){;
                try {
                    var method = new Function("field", "form", script);
                    var r = method.call(form.get(0), field, formObject);
                    RunAjaxResources(jqXHR);

                    goma.ui.updateFlexBoxes();

                    return r;
                } catch(e) {
                    alert(e);
                }
            });
        }).fail(function(jqXHR, textStatus, errorThrown) {
            alert("There was an error while submitting your data, please check your Internet Connection or send an E-Mail to the administrator");

            goma.ui.updateFlexBoxes();
        });
        return false;
    });
};
