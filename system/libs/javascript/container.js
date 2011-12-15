gloader.load("dialog");
self.con_bluebox = new bluebox();
self.con_bluebox.removable = false;
function _con_open(url, title){
	self.con_bluebox.reset();
	self.con_bluebox.load(url, title);
	return false;
}

function con_fadeout(){
	self.con_bluebox.hide();
}