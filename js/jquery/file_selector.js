/*
 * file-selector.js - Add support for drag and drop file selection, and paste from clipboard on supported browsers.
 *
 * Ported from vichan to sakomoto
 */
function init_file_selector(max_images) {
        if(!max_images)return;
        $("head").append(`
<style>
.dropzone {
  color: black;
  padding: 4px;
  text-align: center;
  max-height: 140px;
  transition: 0.2s;
  background-color: rgba(200, 200, 200, 0.5);
  overflow-y: auto;
  outline: none;
}
.file-hint {
  opacity: 0.5;
  cursor: pointer;
  border-width: 2px;
  border-style: dashed;
  padding-top: 10px;
  padding-bottom: 10px;
}
.dropzone .remove-btn {
  cursor: pointer;
  vertical-align: bottom;
  opacity: 0.5;
  font-size: 20px;
  display: inline-block;
  margin-right: 5px;
  bottom: 10px;
  position: relative;
}
.dropzone .tmb-container {
  overflow-x: hidden;
  padding-top: 6px;
}
.file-tmb {
  display: inline-block;
  text-align: center;
  cursor: pointer;
  width: 70px;
  height: 40px;
  background-color: rgba(187, 187, 187, 0.5);
  background-size: cover;
  background-position: center;
  border-width: 1px;
  border-style: solid;
  border-color: rgba(0,0,0,0.5);
}
.dropzone .tmb-filename {
  display: inline-block;
  vertical-align: bottom;
  bottom: 12px;
  position: relative;
  margin-left: 5px;
}
.dropzone .file-thumbs{
  text-align:left;
}
</style>
`);

$('<div class="dropzone-wrap" style="display: none;">'+
	'<div class="dropzone" tabindex="0">'+
		'<div class="file-hint">Select/drop/paste files here</div>'+
			'<div class="file-thumbs"></div>'+
		'</div>'+
	'</div>'+
'</div>').prependTo($("#filerow td:nth-child(2)"));

var files = [];
$('#filerow .upfile').each(function(){$(this).parent().remove();});  // remove the original file selector
$('.dropzone-wrap').css('user-select', 'none').show();  // let jquery add browser specific prefix

function addFile(file) {
	if (files.length == max_images)
		return;

	files.push(file);
	addThumb(file);
}

function removeFile(file) {
	files.splice(files.indexOf(file), 1);
}

function getThumbElement(file) {
	return $('.tmb-container').filter(function(){return($(this).data('file-ref')==file);});
}

function addThumb(file) {

	var fileName = (file.name.length < 24) ? file.name : file.name.substr(0, 22) + '…';
	var fileType = file.type.split('/')[0];
	var fileExt = file.type.split('/')[1];
	var $container = $('<div>')
		.addClass('tmb-container')
		.data('file-ref', file)
		.append(
			$('<div>').addClass('remove-btn').html('✖'),
			$('<div>').addClass('file-tmb'),
			$('<div>').addClass('tmb-filename').html(fileName)
		)
		.appendTo('.file-thumbs');

	var $fileThumb = $container.find('.file-tmb');
	if (fileType == 'image') {
		// if image file, generate thumbnail
		var objURL = window.URL.createObjectURL(file);
		$fileThumb.css('background-image', 'url('+ objURL +')');
	} else {
		$fileThumb.html('<span>' + fileExt.toUpperCase() + '</span>');
	}
}

$(document).on('ajax_before_post', function (e, formData) {
	for (var i=0; i<max_images; i++) {
		var key = 'upfile'+i;
		if (typeof files[i] === 'undefined') break;
		formData.append(key, files[i]);
	}
});

// clear file queue and UI on success
$(document).on('ajax_after_post', function () {
	files = [];
	$('.file-thumbs').empty();
});

var dragCounter = 0;
var dropHandlers = {
	dragenter: function (e) {
		e.stopPropagation();
		e.preventDefault();

		if (dragCounter === 0) $('.dropzone').addClass('dragover');
		dragCounter++;
	},
	dragover: function (e) {
		// needed for webkit to work
		e.stopPropagation();
		e.preventDefault();
	},
	dragleave: function (e) {
		e.stopPropagation();
		e.preventDefault();

		dragCounter--;
		if (dragCounter === 0) $('.dropzone').removeClass('dragover');
	},
	drop: function (e) {
		e.stopPropagation();
		e.preventDefault();

		$('.dropzone').removeClass('dragover');
		dragCounter = 0;

		var fileList = e.originalEvent.dataTransfer.files;
		for (var i=0; i<fileList.length; i++) {
			addFile(fileList[i]);
		}
	}
};


// attach handlers
$(document).on(dropHandlers);

$(document).on('click', '.dropzone .remove-btn', function (e) {
	e.stopPropagation();

	var file = $(e.target).parent().data('file-ref');

	getThumbElement(file).remove();
	removeFile(file);
});

$(document).on('keypress click', '.dropzone', function (e) {
	e.stopPropagation();

	// accept mouse click or Enter
	if ((e.which != 1 || e.target.className != 'file-hint') &&
		 e.which != 13)
		return;

	var $fileSelector = $('<input type="file" multiple>');

	$fileSelector.on('change', function (e) {
		if (this.files.length > 0) {
			for (var i=0; i<this.files.length; i++) {
				addFile(this.files[i]);
			}
		}
		$(this).remove();
	});

	$fileSelector.click();
});

$(document).on('paste', function (e) {
	var clipboard = e.originalEvent.clipboardData;
	if (typeof clipboard.items != 'undefined' && clipboard.items.length != 0) {
		
		//Webkit
		for (var i=0; i<clipboard.items.length; i++) {
			if (clipboard.items[i].kind != 'file')
				continue;

			//convert blob to file
			var file = new File([clipboard.items[i].getAsFile()], 'ClipboardImage.png', {type: 'image/png'});
			addFile(file);
		}
	}
});

}

$(document).ready(function() { repod.file_selector.init(); });
try { repod; } catch(a) { repod = {}; }
repod.file_selector = {
	init: function() {
		this.config = {
			enabled: repod.suite_settings && repod_jsuite_getCookie("drag_enabled") ? repod_jsuite_getCookie("drag_enabled") === "true" : true
		}
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Miscellaneous',read:this.config.enabled,variable:'drag_enabled',label:'Use drag and drop file selection',hover:''}});
		this.update();
	},
	update: function() {
		if (repod.file_selector.config.enabled&&repod.ajax_post.config.enabled)
			init_file_selector($("#postform .upfile").length);
	}
}
