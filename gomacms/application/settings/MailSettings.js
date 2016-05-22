/**
  * Mail-Settings DataObject.
  *
  *	@package 	goma cms
  *	@link 		http://goma-cms.org
  *	@license 	LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *	@author 	Goma-Team
  * @Version 	1.0
*/

$(function(){
	if(mailSettings_FieldSet && mailSettings_Switch && mailSettings_authToken) {
		var sw = $("#" + mailSettings_Switch);
		var form = sw.parents("form");
		var hasBeenValidated = false;
		var xhr;

		form.on("submit", function(){
			if(!hasBeenValidated && sw.find("input[type=radio]:checked").val() == 1) {
				$(".mailsettings-wrapper").removeClass("hasResponse").addClass("loading");
				xhr = $.ajax({
					url: "system/smtp",
					type: "post", 
					data: {
						"allow_smtp": mailSettings_authToken,
						"host": form.find("input[name=smtp_host]").val(),
						"auth": form.find("input[name=smtp_auth]").prop("checked"),
						"user": form.find("input[name=smtp_user]").val(),
						"pwd": form.find("input[name=smtp_pwd]").val(),
						"secure": form.find("select[name=smtp_secure]").val(),
						"port": form.find("input[name=smtp_port]").val(),
					},
					dataType: "text"
				}).always(function(){
					$(".mailsettings-wrapper").removeClass("loading").addClass("hasResponse");
				})
				.done(function(text){
					$(".mailsettings-wrapper .response").html(text.replace("\n", "<br />\n"));
					if(text.endsWith("CONNECTED")) {
						hasBeenValidated = true;
						form.find("input[type=submit]").click();
						$(".mailsettings-wrapper h3 .status").text(lang("success"));
					} else {
						$(".mailsettings-wrapper h3 .status").text(lang("error"));
					}
				}).fail(function(jqXHR){
					$(".mailsettings-wrapper h3 .status").text(lang("error"));
					$(".mailsettings-wrapper .response").text(jqXHR.responseText);
				});

				return false;
			}
		});

		$(".mailsettings-wrapper .submit a").click(function(){
			hasBeenValidated = true;
			$(".mailsettings-wrapper").removeClass("hasResponse").removeClass("loading");
			xhr.abort();
			form.find(".actions button[name=submit]").eq(0).click();
			return false;
		});
	}
});

String.prototype.endsWith = function(suffix) {
    return this.indexOf(suffix, this.length - suffix.length) !== -1;
};
