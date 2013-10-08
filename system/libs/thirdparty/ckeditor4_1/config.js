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
		{ name: 'insert', items : [ 'Image','Table','PageBreak'] },
		{ name: 'styles', items : [ 'Styles','Format' ] },
		{ name: 'colors', items : [ 'TextColor','BGColor' ] },
		{ name: 'editing', items : [ 'BidiLtr','BidiRtl' ] },
		{ name: "Scayt", items: ["Scayt"]},
		{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent'] }
	];
	
	CKEDITOR.config.floatingtools = 'Basic';
	CKEDITOR.config.floatingtools_Basic =
	[
		['Format', 'Bold', 'Italic', 'Underline','-','RemoveFormat', '-', 'JustifyLeft','JustifyCenter','JustifyRight', '-', 'NumberedList', 'BulletedList', '-', 'Link']
	];
	
	config.extraPlugins = "autogrow,stylesheetparser,tableresize,sharedspace,scayt,imagepaste";
	config.autoGrow_onStartup = true;
	config.allowedContent = true;
	
	config.fillEmptyBlocks = function( element )
	{
		if ( element.attributes[ 'class' ].indexOf ( 'clear' ) != -1 )
			return false;
		
		if ( element.is("span") || element.is("strong") || element.is("em") || element.is("u") || element.is("b") )
			return false;
	}
	//config.fillEmptyBlocks = false; // Prevent filler nodes in all empty blocks.
};
