/*! admin-template 1.0.0 */
!function(a){"use strict";"function"==typeof define&&define.amd?define(["jquery","load-image","./jquery.fileupload-process"],a):a(window.jQuery,window.loadImage)}(function(a,b){"use strict";a.blueimp.fileupload.prototype.options.processQueue.unshift({action:"loadVideo",prefix:!0,fileTypes:"@",maxFileSize:"@",disabled:"@disableVideoPreview"},{action:"setVideo",name:"@videoPreviewName",disabled:"@disableVideoPreview"}),a.widget("blueimp.fileupload",a.blueimp.fileupload,{options:{loadVideoFileTypes:/^video\/.*$/},_videoElement:document.createElement("video"),processActions:{loadVideo:function(c,d){if(d.disabled)return c;var e,f,g=c.files[c.index];return this._videoElement.canPlayType&&this._videoElement.canPlayType(g.type)&&("number"!==a.type(d.maxFileSize)||g.size<=d.maxFileSize)&&(!d.fileTypes||d.fileTypes.test(g.type))&&(e=b.createObjectURL(g))?(f=this._videoElement.cloneNode(!1),f.src=e,f.controls=!0,c.video=f,c):c},setVideo:function(a,b){return a.video&&!b.disabled&&(a.files[a.index][b.name||"preview"]=a.video),a}}})});