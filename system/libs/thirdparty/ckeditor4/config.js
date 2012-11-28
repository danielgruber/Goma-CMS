/**
 * @license Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.toolbar_Goma =
	[
		{ name: 'document', items : [ 'Source','-','Templates' ] },
		{ name: 'links', items : [ 'Link','Unlink','Anchor' ] },
		{ name: 'clipboard', items : [ 'Cut','PasteText','PasteFromWord','-','Undo','Redo' ] },
		{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
		{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent', '-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-'] },
		'/',
		{ name: 'insert', items : [ 'Image','Table','SpecialChar','PageBreak'] },
		{ name: 'styles', items : [ 'Styles','Format','Font','FontSize' ] },
		{ name: 'colors', items : [ 'TextColor','BGColor' ] },
		{ name: 'tools', items : [ /*'Maximize',*/ 'ShowBlocks','-','About' ] },
		{ name: 'editing', items : [ 'Find','Replace' ,'BidiLtr','BidiRtl' ] }
	];
};
