function xmaze(style){

  style = typeof style !== 'undefined' ? style : 'default';

  this.data = document.getElementById('zone').innerHTML;
  this.uri = document.location.href;
  this.data = this.data.replace(/\n|\r/g, "")

  var json_regex = /^\s*([\[\{].*[\}\]])\s*$/;
  var jsonp_regex = /^[\s\u200B\uFEFF]*([\w$\[\]\.]+)[\s\u200B\uFEFF]*\([\s\u200B\uFEFF]*([\[{][\s\S]*[\]}])[\s\u200B\uFEFF]*\);?[\s\u200B\uFEFF]*$/;
  var is_json = json_regex.test(this.data);
  var is_jsonp = jsonp_regex.test(this.data);
  var bgsound = null;

  console.log("JSONView: is_json="+is_json+" is_jsonp="+is_jsonp);

  if(is_json || is_jsonp){

    console.log("JSONView: sexytime!");

    function JSONFormatter() {
    }
    JSONFormatter.prototype = {
      htmlEncode: function (t) {
        return t != null ? t.toString().replace(/&/g,"&amp;").replace(/"/g,"&quot;").replace(/</g,"&lt;").replace(/>/g,"&gt;") : '';
      },

      decorateWithSpan: function (value, className) {
        return '<span class="' + className + '">' + this.htmlEncode(value) + '</span>';
      },


      valueToHTML: function(value) {
        var valueType = typeof value;

        var output = "";
        if (value == null) {
          output += this.decorateWithSpan('null', 'null');
        }
        else if (value && value.constructor == Array) {
          output += this.arrayToHTML(value);
        }
        else if (valueType == 'object') {
          output += this.objectToHTML(value);
        }
        else if (valueType == 'number') {
          output += this.decorateWithSpan(value, 'num');
        }
        else if (valueType == 'string') {
          if ((/^(http|https):\/\/[^\s]+$/.test(value)) || (/^(.)\/[^\s]+$/.test(value))) {
            value = this.htmlEncode(value);
            output += '<a href="' + value + '">' + value + '</a>';
          } else {
            output += this.decorateWithSpan('"' + value + '"', 'string');
          }
        }
        else if (valueType == 'boolean') {
          output += this.decorateWithSpan(value, 'bool');
        }

        return output;
      },


      arrayToHTML: function(json) {
        var output = '[<ul class="array collapsible">';
        var hasContents = false;
        for ( var prop in json ) {
          hasContents = true;
          output += '<li>';
          output += this.valueToHTML(json[prop]);
          output += '</li>';
        }
        output += '</ul>]';

        if ( ! hasContents ) {
          output = "[ ]";
        }

        return output;
      },


      objectToHTML: function(json) {
        var output = '{<ul class="obj collapsible">';
        var hasContents = false;
        for ( var prop in json ) {
          hasContents = true;
          output += '<li>';
          output += '<span class="prop">' + this.htmlEncode(prop) + '</span>: ';
          output += this.valueToHTML(json[prop]);
          output += '</li>';
        }
        output += '</ul>}';

        if ( ! hasContents ) {
          output = "{ }";
        }

        return output;
      },


      jsonToHTML: function(json, callback, uri) {
        var output = '';
        if( callback ){
          output += '<div class="callback">' + callback + ' (</div>';
          output += '<div id="json">';
        }else{
          output += '<div id="json">';
        }
        output += this.valueToHTML(json);
        output += '</div>';
        if( callback ){
          output += '<div class="callback">)</div>';
        }
        return this.toHTML(output, uri);
      },


      errorPage: function(error, data, uri) {

        var output = '<div id="error">Error parsing JSON: '+error.message+'</div>';
        output += '<h1>'+error.stack+':</h1>';
        output += '<div id="json">' + this.htmlEncode(data) + '</div>';
        return this.toHTML(output, uri + ' - Error');
      },


      toHTML: function(content, title) {
        return '<doctype html>' +
          '<html><head><title>' + title + '</title>' +
          '<link rel="stylesheet" type="text/css" href="'+style+"/style.css"+'">' +
          '<script type="text/javascript" src="'+style+"/style.js"+'"></script>' +
          '</head><body>' +
          '<audio autoplay loop><source src="'+bgsound+'"></source></audio>' +
          content +
          '</body></html>';
      }
    };



    this.jsonFormatter = new JSONFormatter();

    var outputDoc = '';

    var cleanData = '',
        callback = '';

    var callback_results = jsonp_regex.exec(this.data);
    if( callback_results && callback_results.length == 3 ){
      console.log("THIS IS JSONp");
      callback = callback_results[1];
      cleanData = callback_results[2];
    } else {
      console.log("Vanilla JSON");
      cleanData = this.data;
    }
    console.log(cleanData);

    try {

      var jsonObj = JSON.parse(cleanData);

      if (jsonObj.hasOwnProperty("room")) {
        if (jsonObj["room"].hasOwnProperty("snd")) {
            bgsound = jsonObj["room"]["snd"];
        }
      }
      if (jsonObj.hasOwnProperty("door")) {
        if (jsonObj["door"].hasOwnProperty("snd")) {
            bgsound = jsonObj["door"]["snd"];
        }
      }

      if ( jsonObj ) {

        // Do not display items for EYES, because, lookup for X781 below.
        var itemlessObj = JSON.parse(JSON.stringify(jsonObj));
        if (itemlessObj.room) {
            delete itemlessObj.room.items;
            delete itemlessObj.room.snd;
            delete itemlessObj.room.seq;
        }
        if (itemlessObj.door) {
            delete itemlessObj.door.items;
            delete itemlessObj.door.snd;
        }

        outputDoc = this.jsonFormatter.jsonToHTML(itemlessObj, callback, this.uri);
      } else {
        throw "There was no object!";
      }
    } catch(e) {
      console.log(e);
      outputDoc = this.jsonFormatter.errorPage(e, this.data, this.uri);
    }

    // X781: For EYES, display the items rendered.
    // TBD: Support more types. Currently only images.
    var items = '';
    var linkTo3D = '';
    var form = '';

    if (typeof jsonObj !== 'undefined') {

      var spacetype = 'room';
      if ('door' in jsonObj) {
        spacetype = 'door';
        form = '<form method="get" autocomplete="off" id="getForm"><span class="key">key:</span> <input type="text" name="key" autocomplete="off"> <input type="submit" value="try"> <input type="button" value="answer" onclick="onclickAnswer();"></form><form method="POST" style="display:none;" id="postForm" enctype="multipart/form-data"><div><span class="key" id="reply-to">from:</span> <input type="text" name="email" required> <input type="button" value="<< back" onclick="onclickBack();"></div><div><span class="key">to:</span> <span class="value">&lt;maze-owner&gt;</span></div><div><br><span class="key">message:</span></div><div><textarea name="text" rows="5" cols="60"> </textarea></div><div class="box"><span class="key">file:</span> <input type="file" name="file" id="file" multiple onchange="uploadList()" /><label for="file">upload</label><span style="margin-left: 50px;"><input type="submit" value="send"></span></div><div id="fileList"></div></form>';
      }
      else {
        // currently display 3D icon only at home
        // later, make room renderable at any location
        if (window.location.href.endsWith("/room/home/")) {
          linkTo3D = '<input id="linkTo3D" type="button" value="3D" onclick="'+"location.href='/static/client/index.html';"+'">';
        }
      }

      // Start form-related.
      onclickAnswer = function() {
        document.getElementById('getForm').style.display = "none";
        document.getElementById('postForm').style.display = "inline";
      }
      onclickBack = function() {
        document.getElementById('postForm').style.display = "none";
        document.getElementById('getForm').style.display = "inline";
      }

      uploadList = function() {
        var input = document.getElementById('file');
        var output = document.getElementById('fileList');
        output.innerHTML = '<ul id="files">';
        for (var i = 0; i < input.files.length; ++i) {
          output.innerHTML += '<li>' + input.files.item(i).name + '</li>';
        }
        output.innerHTML += '</ul>';
      }
      // End form-related.

      var itemsHtml = '';
      if (jsonObj[spacetype]) {
        for ( var itemId in jsonObj[spacetype]['items'] ) {

          var item = jsonObj[spacetype]['items'][itemId];
          var itemType = typeof item;

          if ( (itemType === "string") && ( item.endsWith('.jpg') || item.endsWith('.jpeg') || item.endsWith('.png') || item.endsWith('.webp') || item.endsWith('.gif')  ) ) {
            itemsHtml += '<li class="item">';
            itemsHtml += '<img src="'+item+'">';
            itemsHtml += '</li>';
          }
          else if ( (itemType === "string") && ( item.endsWith('.mp4') || item.endsWith('.ogg') || item.endsWith('.webm')  ) ) {
            itemsHtml += '<li class="item"><video width="640" height="480" controls>';
            itemsHtml += '<source src="'+item+'" type="video/mp4">';
            itemsHtml += '</video></li>';
          }
          else if ( (itemType === "string") && ( item.endsWith('.mp3') ) ) {
            itemsHtml += '<li class="item"><audio controls>';
            itemsHtml += '<source src="'+item+'" type="audio/mpeg">';
            itemsHtml += '</audio></li>';
          }
          else if ( (itemType === "string") && ( item.endsWith('.gltf') || item.endsWith('.glb')) ) {
            if (item.startsWith('/static/media/')) {
              var full = location.protocol+'//'+location.hostname+(location.port ? ':'+location.port: '');
              item = full + item;
            }
            else {
              item = '/cors/?url=' + item;
            }
            itemsHtml += '<li class="item"><model-viewer src="'+item+'" alt="A 3D model" background-color="#70BCD1" shadow-intensity="1" camera-controls="" interaction-prompt="auto" auto-rotate="" ar="" magic-leap="" style="width: 640px; height: 480px" autoplay></model-viewer></li>';
          }
          else if ( (itemType === "string") && ( item.endsWith('.pdf') ) ) {
            itemsHtml += '<li class="item">';
            itemsHtml += '<embed src="'+item+'" width="100%" height="100%">';
            itemsHtml += '</li>';
          }
          else {
            itemsHtml += '<li class="item">';
            itemsHtml += '<pre>'+item+'</pre>';
            itemsHtml += '</li>';
          }
        }
      }

      items = '<div id="items"><ul>'+itemsHtml+'</ul></div>';
    }

    var links = '<link rel="stylesheet" type="text/css" href="'+style+"/style.css"+'">' +
                '<script type="text/javascript" src="'+style+"/style.js"+'"></script>';

    document.body.innerHTML = linkTo3D + form + links + outputDoc + items;

  }
  else {
    console.log("JSONView: this is not json, not formatting.");
    console.log(is_json);
    console.log(is_jsonp);
    console.log(this.data.replace(/\n|\r/g, ""));
  }

}
