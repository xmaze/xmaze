function xmaze(style){

  style = typeof style !== 'undefined' ? style : 'default';

  this.data = document.getElementById('zone').innerHTML;
  this.uri = document.location.href;
  this.data = this.data.replace(/\n|\r/g, "")

  var json_regex = /^\s*([\[\{].*[\}\]])\s*$/;
  var jsonp_regex = /^[\s\u200B\uFEFF]*([\w$\[\]\.]+)[\s\u200B\uFEFF]*\([\s\u200B\uFEFF]*([\[{][\s\S]*[\]}])[\s\u200B\uFEFF]*\);?[\s\u200B\uFEFF]*$/;
  var is_json = json_regex.test(this.data);
  var is_jsonp = jsonp_regex.test(this.data);

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
      if ( jsonObj ) {
        outputDoc = this.jsonFormatter.jsonToHTML(jsonObj, callback, this.uri);
      } else {
        throw "There was no object!";
      }
    } catch(e) {
      console.log(e);
      outputDoc = this.jsonFormatter.errorPage(e, this.data, this.uri);
    }


    var links = '<link rel="stylesheet" type="text/css" href="'+style+"/style.css"+'">' +
                '<script type="text/javascript" src="'+style+"/style.js"+'"></script>';
    document.body.innerHTML = links + outputDoc;

  }
  else {
    console.log("JSONView: this is not json, not formatting.");
    console.log(is_json);
    console.log(is_jsonp);
    console.log(this.data.replace(/\n|\r/g, ""));
  }

}
