/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 17.03.2013
  * $Version 1.0
*/

goma.ui.Notifications = {
	/**
	 * notifications, which are sent or visible
	*/
	notifications: [],
	/**
	 * inits the Notification area
	 *
	 *@name Init
	 *@access public
	*/
	Init: function() {
		if($(".notificationRoot").length == 1) {
			$(".notificationRoot").append('<div id="notificationsHolder"><div id="notifications"></div></div>');
		} else {
			goma.ui.getDocRoot().append('<div id="notificationsHolder"><div id="notifications"></div></div>');
		}
	},
	
	/**
	 * notify a user about anything
	 * all you need is a class-name, a title, and an icon
	 * you can give also a text and a function which is executes if you click on the notification if you want
	 *
	 *@name notify
	 *@access public
	 *@param string - class-name, notification fired from
	 *@param string - title
	 *@param string - icon
	 *@param string - text
	 *@param function
	*/
	notify: function(class_name, title, icon, text, Clickfn) {
		var notificationID = "notification_" + class_name + goma.ui.Notifications.notifications.length;
		
		var notification = $("<div>").addClass("notification").attr("id", notificationID).append('<div class="icon"><img src="'+icon+'" alt="Icon" /></div>\
		<div class="title">'+title+'</div>\
		<div class="text">'+text+'</div>');
		
		if(typeof Clickfn != "undefined") {
			notification.click(Clickfn);
		}
		
		var notification = {
			node: notification,
			type: "notification",
			id: notificationID,
			fn: Clickfn,
			visible: false,
			class_name: class_name
		};
		
		goma.ui.Notifications.notifications[notificationID] = notification;
		
		setTimeout(function(){
			goma.ui.Notifications.makeVisible(notification, 5500);
		}, 250);
	},
	
	/**
	 * makes a notification visible
	 *
	 *@name notification
	 *@access public
	*/
	makeVisible: function(notification, durationClose) {
		var n = notification;
		n.node.css("display", "none");
		n.node.prependTo($("#notifications"));
		var nNode = n.node;
		
		goma.ui.Notifications.notifications[n.id].visible = true;
		nNode.slideDown("fast");
		
		var close = function(){
			goma.ui.Notifications.notifications[n.id].visible = false;
			nNode.css("position", "absolute");
			nNode.animate({
				left: $(window).width() + nNode.outerWidth() + 20
			}, 1000, function(){
				nNode.remove();
			});
			nNode.off(".close");
		};
		
		nNode.on("click.close", close);
		
		if(typeof durationClose != "undefined" && durationClose != -1) {
			setTimeout(close, durationClose);
		}
	}
};

$(function(){
	goma.ui.Notifications.Init();
});