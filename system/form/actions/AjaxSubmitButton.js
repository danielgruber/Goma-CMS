var initAjaxSubmitbutton = function(id, divId, formId, url, appendix) {
    var button = $("#" + id);
    var container = $("#" + divId);
    var form = $("#" + formId);
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

        form.gForm().setLeaveCheck(false);
        button.css("display", "none");
        container.append("<img src=\"images/16x16/loading.gif\" alt=\"loading...\" class=\"loading\" />");
        $("body").css("cursor", "wait");

        goma.ui.updateFlexBoxes();
        $.ajax({
            url: url + appendix,
            type: "post",
            data: form.serialize(),
            dataType: "html",
            complete: function()
            {
                $("body").css("cursor", "default");
                $("body").css("cursor", "auto");
                container.find(".loading").remove();
                button.css("display", "inline");

                var eventb = jQuery.Event("ajaxresponded");
                form.trigger(eventb);

                goma.ui.updateFlexBoxes();
            },
            success: function(script, textStatus, jqXHR) {

                goma.ui.loadResources(jqXHR).done(function(){;
                    if (window.execScript)
                        window.execScript("method = " + "function(){" + script + "};",""); // execScript doesn’t return anything
                    else
                        method = eval("(function(){" + script + "});");
                    RunAjaxResources(jqXHR);
                    var r = method.call(form.get(0));

                    form.gForm().setLeaveCheck(false);

                    goma.ui.updateFlexBoxes();

                    return r;
                });
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                alert("There was an error while submitting your data, please check your Internet Connection or send an E-Mail to the administrator");

                goma.ui.updateFlexBoxes();
            }
        });
        return false;
    });
};
