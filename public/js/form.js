/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2012
 */

Form = function() {
  
  this.error = function(data)
  {
    info(data.field);
    info(data.error);
    var form = $("form[inited='inited']");
    if(form.length > 0)
    {
      var el =form.find('input[name=' + data.field + ']');
      el.before('<div class="control-group error"><span class="help-inline">' + data.error + '</span></div>');
      el.prependTo(el.prev());
      el.bind('blur', function(){
        $(this).parent().before($(this));
        $(this).next().remove();
        $(this).unbind('blur');
      });
    }
  }  
}

Form = new Form();

