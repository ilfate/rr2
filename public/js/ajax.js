/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2012
 */

Ajax = function() {
  
  this.callBack = [];
  
  /**
   * ajax request that will return json data
   */
  this.json = function(url, options)
  {
    if(!options) options = {};
    options.url = url;
    options.dataType = "json";
    if(!options.params) options.params = '';
    options.params += '__csrf=' + Ajax.getCSRF();
    this.execute(options);
  }
  /**
   * ajax request that will return json data
   */
  this.html = function(url, target, options)
  {
    if(!options) options = {};
	if(url.indexOf('?') == -1)
    {
		url = url + '?__html=true';
	} else {
		url = url + '&__html=true';
	}
    options.url = url;
    options.type = "GET";
    options.target = target;
    options.dataType = "html";

    this.execute(options);
  }
  
  
  this.execute = function(options)
  {
    var opt = {};
    if(options.url.indexOf('?') == -1)
    {
      opt.url = options.url + '?__ajax=true';
    } else {
      opt.url = options.url + '&__ajax=true';
    }
    if(options.params)
    {
      opt.url += '&' + options.params;
    }
    if(options.callBack)
    {
      this.callBack.push(options.callBack);
      var n_cb = this.callBack.length - 1;
    } else {
      var n_cb = -1;
    }
    if(options.data) {
      opt.data = options.data;
    }
    if(options.type) {
      opt.type = options.type;
    } else {
      opt.type = "POST";
    }

    if(options.dataType) {
      opt.dataType = options.dataType;
    } else {
      opt.dataType = "json";
    }
      
    var request = $.ajax(opt);  
    
    if(options.dataType == "json") 
    {
      request.done(function(data){Ajax.doneJson(data, n_cb)});
    } else {
      request.done(function(data){Ajax.doneHtml(data, options.target, n_cb)});
    }
    
    request.fail(function(jqXHR, textStatus){Ajax.fail(jqXHR, textStatus)});
  }
  
  this.doneJson = function(data, n_cb)
  {
    F.handleEvent('ajaxonload');
    if(data.actions)
    {
      for(var key in data.actions)
      {
        if(data.args && data.args[key])
        {
          var args = data.args[key];
        } else {
          var args = [];
        }
        var handler = eval("(" + data.actions[key] + ")");
        handler.call(this, args);
      }
    }
    if(n_cb >= 0)
    {
      this.callBack[n_cb](data);
    }
    F.handleEvent('ajaxloadcompleted');
  }
  this.doneHtml = function(data, target, n_cb)
  {
    F.handleEvent('ajaxonload');
    $(target).html(data);
    F.handleEvent('ajaxloadcompleted');
  }
  
  this.fail = function(jqXHR, textStatus)
  {
    alert('FAIL');
    info(jqXHR);
    info(textStatus);
  }
  
  this.init = function()
  {
    $("form[method='post']").each(function() {
      var form = $(this);
      if (form.attr("inited") != "inited") {
        form.bind("submit", function() {
          if(form.hasClass("inactive")) return false;
          Ajax.formLoadingStart(form);
          Ajax.json(this.action, {
            params : '__csrf=' + Ajax.getCSRF(),
            data : form.serialize(),
            callBack : function(){Ajax.formLoadingEnd(form)}
          });
          return false;
        });
        form.attr("inited", "inited");
      }
    });
    
    $("a.ajax").each(function() {
      var link = $(this);
      if (link.attr("inited") != "inited") {
        link.bind("click", function() {
          if(link.hasClass("disabled")) return false;
          Ajax.linkLoadingStart(link);
          Ajax.json(this.href, {
            params : '__csrf=' + Ajax.getCSRF(),
            callBack : function(){Ajax.linkLoadingEnd(link)}
          });
          return false;
        });
        link.attr("inited", "inited");
      }
    });
    
    $("a.hajax").each(function() {
      var link = $(this);
      if (link.attr("inited") != "inited") {
        link.bind("click", function() {
          if(link.hasClass("disabled")) return false;
          Ajax.linkLoadingStart(link);
          Ajax.html(this.href, link.data('target'), {
            params : '__csrf=' + Ajax.getCSRF(),
            callBack : function(){Ajax.linkLoadingEnd(link)}
          });
          return false;
        });
        link.attr("inited", "inited");
      }
    });
  }
  
  this.formLoadingStart = function(form)
  {
    form.addClass("inactive");
    form.find('[type=submit]').button('loading');
  }
  this.formLoadingEnd = function(form)
  {
    form.find('[type=submit]').button('reset');
    form.removeClass("inactive");
  }
  this.linkLoadingStart = function(link)
  {
    link.addClass("disabled");
  }
  this.linkLoadingEnd = function(link)
  {
    link.removeClass("disabled");
  }
  
  
  this.getCSRF = function()
  {
    return $('#CSRF_TOKEN').val();
  }
}

Ajax = new Ajax();

