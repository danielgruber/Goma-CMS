var multiFormFieldController = function(element, options) {
    this.element = $(element);

    for(var i in options) {
        if(options.hasOwnProperty(i)) {
            this[i] = options[i];
        }
    }

    this.init();

    return this;
};

multiFormFieldController.prototype = {
    sortable: false,
    deletable: false,

    init: function() {
        this.updateOrder();

        if(this.sortable) {
            this.element.find(".part-sort-button").css("cursor", "move").show();

            gloader.loadAsync("sortable").done(function(){
               this.element.sortable({
                    opacity: 0.6,
                    helper: 'clone',
                    handle: ".part-sort-button",
                    placeholder: 'placeholder',
                    revert: true,
                    tolerance: 'pointer',
                    cancel: "a, img, .actions",
                    start: function(event, ui) {
                        $(".placeholder")
                            .css({'width' : ui.item.width(), 'height': ui.item.height()})
                            .attr("class", ui.item.attr("class") + " placeholder");
                    },
                    update: function() {
                        this.updateOrder();
                    }.bind(this),
                    distance: 10,
                    items: " > .clusterformfield"
                });
            }.bind(this));
        } else {
            this.element.on("click", ".part-sort-button", function(){
                return false;
            });
        }

        if(this.deletable) {
            this.element.find(".part-delete-button").show();
        }
    },

    updateOrder: function() {
        var i = 0;
        this.element.find(".form-component").each(function(){
            $(this).attr("order", i);
            $(this).find("input[name*=__sortpart]").val(i);
            i++;
        });
    },

    hideCluster: function(animated) {
        this.sortHiddenInput.val(-1);

        this.clusterFormField.addClass("part-hidden");

        if(this.clusterFormField.find(".form_field_headline:visible").length > 0) {
            this.clusterFormField.find(".undo-template .headline").css("display", "");
            this.clusterFormField.find(".undo-template .headline .text").text(this.clusterFormField.find(".form_field_headline:visible input").val());
        } else {
            this.clusterFormField.find(".undo-template .headline").css("display", "none");
        }

        if(animated) {
            this.clusterFormField.find(" > div").not(".undo-template").slideUp("fast");
            this.clusterFormField.find(".undo-template").slideDown("fast", function(){
                articleController.sharedInstance.updateOrder();
            });
        } else {
            this.clusterFormField.find(" > div").not(".undo-template").css("display", "none");
            this.clusterFormField.find(".undo-template").css("display", "block");

            articleController.sharedInstance.updateOrder();
        }

        if(!this.clusterFormField.find(".undo-template").parent().hasClass("clusterformfield")) {
            this.clusterFormField.find(".undo-template").appendTo(this.clusterFormField);
            this.clusterFormField.find(".undo-template a.undo").click(this.undo.bind(this));
        }
    },

    undo: function() {
        this.clusterFormField.removeClass("part-hidden");
        this.clusterFormField.find(" > div").slideDown("fast");
        this.clusterFormField.find(".undo-template").slideUp("fast");

        articleController.sharedInstance.updateOrder();
    }
};
