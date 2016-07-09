/**
 * The JS for field sets.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 1.1
 */
function ImageUploadController(field, updateUrl, options) {
    if(field.fileUpload === undefined) {
        throw new Error("Could not initialize ImageUploadController, it depends on fileUpload.");
    }

    this.updateUrl = updateUrl;
    this.field = field;

    field.imageUpload = this;

    this.widget = $("#" + this.field.id + "_widget");
    this.widget.find(".buttons .cancel").click(this.hideCrop.bind(this));
    this.widget.find(".buttons .save").click(this.saveCrop.bind(this));

    this.super = this.field.fileUpload;

    this.registerEventHandler();
    this.updateCropArea({
        status: this.field.upload != null,
        file: this.field.upload
    });

    if(typeof options == "object") {
        for(var i in options) {
            if(options.hasOwnProperty(i)) {
                this[i] = options[i];
            }
        }
    }

    return this;
}

ImageUploadController.prototype = {
    cropAreaPlaced: false,
    internalLeft: null,
    internalTop: null,
    internalWidth: null,
    internalHeight: null,
    jcropInstance: null,
    factor: 1,
    aspectRatio: null,

    updateCropArea: function(data) {
        if(data == null || !data.status) {
            if(this.cropAreaPlaced) {
                this.super.actions.find(".crop").remove();
                this.cropAreaPlaced = false;
                this.super.uploader.placeBrowseHandler();
            }
        } else {
            if(!this.cropAreaPlaced) {
                this.placeCropButton();
            }
        }
    },

    placeCropButton: function() {
        this.super.actions.append('<button class="button crop">'+lang("crop_image")+'</button>');
        this.super.actions.find(".crop").click(this.cropButtonClicked.bind(this));

        this.super.uploader.placeBrowseHandler();

        this.cropAreaPlaced = true;
    },

    cropButtonClicked: function() {
        if(this.jcropInstance != null) {
            this.jcropInstance.destroy();
            this.jcropInstance = null;
        }

        this.widget.fadeIn("fast");

        this.widget.find(".loading").show(0);
        this.widget.find(".image").hide(0);

        var $this = this,
            src = this.field.upload.sourceImage != null ? this.field.upload.sourceImage : this.field.upload.path,
            image = new Image();

        image.onload = function() {
            $this.widget.find(".image img").attr("src", src);
            $this.widget.find(".loading").hide(0);
            $this.widget.find(".image").show(0);

            var size = $this.getSize(image),
                options = {
                    onChange: $this.updateCoords.bind($this),
                    onSelect: $this.updateCoords.bind($this)
                };

            $this.widget.find(".image img").attr({
                height: size.height,
                width: size.width
            });

            $this.factor = size.width / image.width;

            var upload = $this.field.upload, thumbSelectionW = size.width, thumbSelectionH = size.height, y = 0, x = 0;

            if(this.aspectRatio != null) {
                options.aspectRatio = this.aspectRatio;
            }

            if(upload.thumbLeft != 50 || upload.thumbTop != 50 || upload.thumbWidth != 100 || upload.thumbHeight != 100) {
                thumbSelectionW = upload.thumbWidth / 100 * size.width;
                thumbSelectionH = upload.thumbHeight / 100 * size.height;
                y = (size.height - thumbSelectionH) * upload.thumbTop / 100;
                x = (size.width - thumbSelectionW) * upload.thumbLeft / 100;

                options.setSelect = [
                    x, y, x + thumbSelectionW, y + thumbSelectionH
                ];
            }

            if(this.aspectRatio != null && thumbSelectionW / thumbSelectionH != this.aspectRatio) {
                if(thumbSelectionW / thumbSelectionH > this.aspectRatio) {
                    x = (size.width - thumbSelectionH * this.aspectRatio) / 2;
                    thumbSelectionW = thumbSelectionH * this.aspectRatio;
                } else {
                    y = (size.height - size.width / this.aspectRatio) / 2;
                    thumbSelectionH = size.width / this.aspectRatio;
                }

                options.setSelect = [
                    x, y, x + thumbSelectionW, y + thumbSelectionH
                ];
            }

            $this.widget.find(".image img").Jcrop(options, function() {
                $this.jcropInstance = this;
            });
        };

        image.src = src;

        return false;
    },

    getSize: function(image) {
        var maxWidth = this.widget.find(".image").width();
        var maxHeight = this.widget.height() - this.widget.find(".buttons").outerHeight() - 32;

        if(image.width >= maxWidth || image.height >= maxHeight) {
            if(maxWidth / image.width * image.height >= maxHeight) {
                return {
                    height: maxHeight,
                    width: maxHeight / image.height * image.width
                };
            } else {
                return {
                    width: maxWidth,
                    height: maxWidth / image.width * image.height
                };
            }
        } else {
            return {
                width: image.width,
                height: image.height
            };
        }
    },

    updateCoords: function(data) {
        this.internalHeight = data.h / this.factor;
        this.internalWidth = data.w  / this.factor;
        this.internalLeft = data.x  / this.factor;
        this.internalTop = data.y  / this.factor;
    },

    hideCrop: function() {
        if(this.jcropInstance != null) {
            this.jcropInstance.destroy();
        }

        this.widget.fadeOut("fast");
        return false;
    },

    saveCrop: function() {
        var $this = this;

        if(this.jcropInstance != null) {
            this.jcropInstance.destroy();
        }
        this.widget.fadeOut("fast");

        $.ajax({
            url: this.updateUrl,
            type: "post",
            data: {
                thumbLeft: this.internalLeft,
                thumbTop: this.internalTop,
                thumbWidth: this.internalWidth,
                thumbHeight: this.internalHeight,
                useSource: this.field.upload.sourceImage != null
            },
            datatype: "json"
        }).done(function(data){
            $this.super.uploader.updateFile(data);
        }).fail(function(jqxhr){
            var data = $.parseJSON(jqxhr.responseText);
            if(data.error) {
                alert(data.class + ": " + data.code + " " + data.errstring);
            } else {
                alert(jqxhr.responseText);
            }
        });

        return false;
    },

    registerEventHandler: function() {
        var $this = this;

        var oldUpdateFile = this.field.fileUpload.uploader.updateFile;

        this.super.uploader.updateFile = function(data) {
            $this.updateCropArea(data);
            oldUpdateFile.apply(this, arguments);
        };
    }
};
