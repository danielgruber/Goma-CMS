/**
 * Ths JS for file upload sets.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 2.1
 */
var FileUploadSet = function(name, table, url) {

    "use strict";

    this.table = $(table);
    this.url = url;
    this.tbody = this.table.find("tbody");
    this.name = name;
    this.inSort = false;


	// rebuild the no-js-code to a browse-button
	this.table.find("thead .no-js").html('<div class="uploadBtn"><input type="button" class="button uploadbutton" value="'+lang("files.browse")+'" /></div>');
	this.table.find("thead .no-js").removeClass("no-js");
	this.browse = this.table.find("thead .uploadbutton");
	
	var $this = this;
	
	// bind delete actions and remove no-js-code
	this.tbody.find("tr td.actions .delete").html('<img src="images/16x16/del.png" height="16" width="16" alt="del" title="'+lang("delete")+'" />');
	
	// bindings
	var bindDeleteEvent = function(node) {
		$(node).click(function(){
			var tr = $(this).parent().parent().parent();
			var id = $(this).parent().parent().parent().attr("name");
			$(this).attr("src", "images/16x16/loading.gif");
			$.ajax({
				url: $this.url + "/remove/" + id,
				type: "post"
			}).done(function(){
                $this.uploader.removeTableRow(tr);
			});
		});
		$(node).css("cursor", "pointer");
	};

    var bindSortable = function() {
        $this.table.sortable({
            opacity: 0.75,
            revert: true,
            items: 'tbody tr:not(.uploading):not(.empty)',
            tolerance: 'pointer',
            containment: "parent",
            start: function(event, ui) {
                $this.inSort = true;
            },
            update: function(event, ui) {
                var data  = $(this).sortable("serialize", {key: "sorted[]", attribute: "data-id"});
                // save order
                $.ajax({
                    url: $this.url + "/saveSort/",
                    data: data,
                    type: "post",
                    dataType: "html"
                });
                redrawEvenOddMarkers();
            },
            stop: function() {
                setTimeout(function(){
                    $this.inSort = false;
                }, 33);
            }
        });
    };
	
	// redraw
	var redrawEvenOddMarkers = function() {
		if($this.tbody.find("tr").length > 1) {
            $this.table.find("tr").removeClass("grey").removeClass("white");
            $this.table.find("tr:even").addClass("grey");
		}
	};
	
	bindDeleteEvent(this.tbody.find("tr td.actions .delete img"));

    preloadLang(["loading", "waiting"]);

    bindSortable();
	
	// register the uploader
	this.uploader = new AjaxUpload(this.table, {
		url: url + "/frameUpload/",
		ajaxurl: url + "/ajaxUpload/",
		browse: this.browse,
		usePut: false,
		
		multiple: true,
		
		// events
		uploadStarted: function(fileIndex, upload) {
			var that = this;
			var id = $this.name+'_upload_'+fileIndex;
			var color = $this.tbody.find("tr:last").hasClass("grey") ? "white" : "grey";
			if($this.tbody.find(" > tr > th").length > 0) {
				$this.tbody.find(" > tr > th").parent().remove();
			}
			
			this.queue[fileIndex].tableid = id;

            var tableRow = this.addTableRow(id, color, upload.file.name);
            tableRow.find("td.filename").append('<div class="progressbar"><div class="progress"></div><span>'+lang("waiting")+'</span></div></div');
			tableRow.find(".delete img").click(function(){
				that.abort(fileIndex);
			});
			tableRow.find(".delete img").css("cursor", "pointer");

            redrawEvenOddMarkers();
		},
		
		dragInDocument: function() {
			$this.table.find(".filetable").addClass("dragInDoc");
		},
		dragLeaveDocument: function() {
			$this.table.find(".filetable").removeClass("dragInDoc");
			$this.table.find(".filetable").removeClass("dragOver");
		},
		
		dragOver: function() {
			$this.table.find(".filetable").addClass("dragOver");
		},
		
		/**
		 * called when the speed was updated, just for ajax-upload
		*/
		speedUpdate: function(fileIndex, file, KBperSecond) {
			if(typeof this.queue[fileIndex].tableid !== "undefined") {
				var ext = "KB/s";
				KBperSecond = Math.round(KBperSecond);
				if(KBperSecond > 1000) {
					KBperSecond = Math.round(KBperSecond / 1000, 2);
					ext = "MB/s";
				}
				$("#" + this.queue[fileIndex].tableid).find(".progressbar span").html(KBperSecond + ext);
				
			}
		},
		
		/**
		 * called when the progress was updated, just for ajax-upload
		*/
		progressUpdate: function(fileIndex, file, newProgress) {
			if(typeof this.queue[fileIndex].tableid != "undefined") {

                var tableRow = $("#" + this.queue[fileIndex].tableid);
				tableRow.find(".filename").attr("title", lang("loading") + " ("+newProgress+"%)");
                tableRow.find(".progress").stop().animate({width: newProgress + "%"}, 200);
			}
		},
		
		/**
		 * event is called when the upload is done
		*/
		always: function(time, fileIndex) {
			if(typeof this.queue[fileIndex].tableid !== "undefined") {

                var tableRow = $("#" + this.queue[fileIndex].tableid);
                tableRow.find(".progress span").html("100%");
                tableRow.find(".progress").css("width", "100%");
				tableRow.removeAttr("title");
			}
		},
		
		/**
		 * method which is called, when we receive the response
		*/
		done: function(html, fileIndex) {
			if(typeof this.queue[fileIndex].tableid !== "undefined") {
				var tablerow = $("#" + this.queue[fileIndex].tableid);
				try {
					var data = JSON.parse(html);
					if(data.status == 0 && !data.multiple) {
                        this.removeTableRowWithError(tablerow, data.errstring);
					} else {
						if(data.multiple) {
							for(var i in data.files) {
								if(data.files.hasOwnProperty(i)) {
									var file = data.files[i];
									// the current we can just update
									if (i === 0) {
										if(file.status == 0) {
											this.removeTableRowWithError(tablerow, file.errstring);
										} else {
											this.updateTableRowWhenDoneUploading(tablerow, file.file);
										}
									} else {
										// now add some records
										this.currentIndex++;
										var insertedTableRow = this.addTableRow(this.name + "_frameupload_" + this.currentIndex, "", file.name, tablerow);

										if(file.status == 0) {
											this.removeTableRowWithError(insertedTableRow, file.errstring);
										} else {
											this.updateTableRowWhenDoneUploading(insertedTableRow, file.file);
										}
										redrawEvenOddMarkers();
									}
								}
							}
						} else {
                            this.updateTableRowWhenDoneUploading(tablerow, data.file);
						}
					}
				} catch(err) {
					if(!this.isAbort) {
                        this.removeTableRowWithError(tablerow, data.errstring);
					}
				}
			}
		},

        /**
         * adds a file-entry.
         */
        addTableRow: function(id, classname, filename, after) {
            var html = '<tr class="'+classname+'" id="'+id+'">' +
				'<td class="icon"></td>' +
				'<td class="filename" title="'+filename+'"><span class="filename">'+filename+'</span></div></td>' +
				'<td class="actions"><div class="delete"><img src="images/16x16/del.png" height="16" width="16" alt="del" title="'+lang("delete")+'" /></div></td>' +
			'</tr>';

            if(after !== undefined) {
                after.after(html);
            } else {
                $this.tbody.append(html);
            }

            return $("#" + id);
        },

        /**
         * add error to table-row and remove it.
         */
        removeTableRowWithError: function(tableRow, error) {
            tableRow.removeClass("uploading");

            tableRow.find(".filename").html('<div class="error">'+error+'</div>');
            setTimeout(this.removeTableRow.bind(this, tableRow), 2000);
            tableRow.find(".delete img").remove();
        },

        /**
         * slides up table-row.
         */
        removeTableRow: function(tableRow) {
            tableRow.fadeOut(300, function(){
                tableRow.remove();
                if($this.tbody.find(" > tr").length === 0) {
                    $this.tbody.append('<tr class="empty"><th class="empty" colspan="3">'+lang("files.no_file")+'</th></tr>');
                } else {
                    redrawEvenOddMarkers();
                }
            });
        },

        /**
         * updates table-row.
         */
        updateTableRowWhenDoneUploading: function(tableRow, file) {
            tableRow.removeClass("uploading");
            tableRow.find(".filename").fadeTo(200, 0.0);
            tableRow.attr("name", file.id);
            tableRow.attr("data-id", "node_" + file.id);
            tableRow.find(".icon").fadeTo(200, 0.0, function(){
                tableRow.find(".icon").html('<img src="'+file.icon16+'" alt="icon" />');
                tableRow.find(".filename").attr("title", file.name);
                if(file.path) {
                    tableRow.find(".filename").html('<a href="' + file.path + '" target="_blank">' + file.name + '</a>');
                } else {
                    tableRow.find(".filename").html('<a target="_blank">' + file.name + '</a>');
                }

                tableRow.find(".filename").fadeTo(200, 1.0);
                tableRow.find(".icon").fadeTo(200, 1.0);
            });

            tableRow.find(".delete img").unbind("click");
            bindDeleteEvent(tableRow.find(".delete img"));
        },
		
		/**
		 * called on cancel
		*/
		cancel: function(fileIndex) {	
			if(typeof this.queue[fileIndex].tableid !== "undefined") {
				var tablerow = $("#" + this.queue[fileIndex].tableid);
                this.removeTableRow(tablerow);
			}
		}
		
	});
};
