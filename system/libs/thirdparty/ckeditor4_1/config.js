/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	
	config.toolbar_Goma =
	[
		{ name: 'document', items : [ 'Source'/*,'-','Templates'*/] },
		{ name: 'links', items : [ 'Link','Unlink','Anchor' ] },
		{ name: 'clipboard', items : [ 'Cut','PasteText','PasteFromWord','-','Undo','Redo' ] },
		{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','-','RemoveFormat' ] },
		{ name: 'justify', items: ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'] },
		{ name: 'tools', items : [ 'Maximize' ] },
		'/',
		{ name: 'insert', items : [ 'Image','Table','Symbol','PageBreak'] },
		{ name: 'styles', items : [ 'Styles','Format' ] },
		{ name: 'colors', items : [ 'TextColor','BGColor' ] },
		{ name: 'editing', items : [ 'Find','Replace' ,'BidiLtr','BidiRtl' ] },
		{ name: "Scayt", items: ["Scayt"]},
		{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent'] }
	];
	
	CKEDITOR.config.floatingtools = 'Basic';
	CKEDITOR.config.floatingtools_Basic =
	[
		['Bold', 'Italic', 'Underline','-','RemoveFormat', '-', 'JustifyLeft','JustifyCenter','JustifyRight', '-', 'NumberedList', 'BulletedList', '-', 'Link']
	];
	
	config.extraPlugins = "autogrow,stylesheetparser,tableresize,sharedspace,scayt";
	config.autoGrow_onStartup = true;
	config.allowedContent = true;
	
	config.fillEmptyBlocks = false; // Prevent filler nodes in all empty blocks.
};
