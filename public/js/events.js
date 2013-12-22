window.F = new F();

function customHandler(desc,page,line,chr)  {

  var error = '<b>JAVASCRIPT '+desc+'</b><br/>'+page+'('+line+') '+(chr?':'+chr:'');
  var errId     = F.errList.push({'message':'<b>JAVASCRIPT '+desc+'</b><br/>'+page+'('+line+') '+(chr?':'+chr:''),'state':0})-1;

  return true;
}
var clientW  = 0;
var clientH  = 0;

// events


window.onload = function(event){

  F.handleEvent('onload',event);
  F.handleEvent('onafterload',event);
};


window.onresize = function(event){
  if (window.resizeTimerMainWindow) {
    clearTimeout(window.resizeTimerMainWindow);
  }
  window.resizeTimerMainWindow = setTimeout(F.startResize, 100);
};

document.onmouseup = function(event){
  if(!event){
    event = window.event;
  }
  F.handleEvent('onmouseup', event);
};

F.manageEvent('onload',"$('body').click(function(event){F.handleEvent('onmousedown', event, this);})");

/**
 * Ilfate Manager ! main js class
 *
 */
function F()
{
  this.FObjects = [];
  this.clientH = 0;
  this.clientW = 0;
  
 //info('FateManager constructed');
  
  
  this.handlers = {
  'onload' : new Array(),
  'onresize' : new Array(),
  'ajaxonload' : new Array(),
  'ajaxonresize' : new Array(),
  'ajaxloadcompleted' : new Array(),
  'onmousedown' : new Array(),
  'onmouseup' : new Array()
  };
  this.errList  = new Array();
  //for debug
  this.__curentEvent  = '';
  this.__curentRun    = '';
  
  /**
   * trigger that calls event evName 
   */
  this.handleEvent = function( evName , event, target){
    //msg = '';
    clientW = this.clientW || document.getElementsByTagName('html')[0].clientWidth;
    clientH = this.clientH || document.getElementsByTagName('html')[0].clientHeight;
    this.clientW = clientW;
    this.clientH = clientH;
    this.__curentEvent = evName;
    for(var i in this.handlers[evName] || []){
      var f = this.handlers[evName][i];
      this.__curentRun = f;
      try{
        if (typeof(f)=='function'){
          f(event, target);
        }else{
          eval(f);
        }
      }catch (e){
        customHandler(e.name+' '+e.message,e.fileName,e.lineNumber,0);
      }
      this.__curentRun   = '';
    }
    if (evName=='onload'){
      this.handleEvent('onresize');
    }
  }
  /**
   * Make code to execute on event
   */
  this.manageEvent = function(evName, toEval){
    if ('function' === typeof evName.pop){
      for (var i in evName){
        this.manageEvent(evName[i], toEval);
      }
    }else{
      if (this.handlers[evName]==undefined){
        this.addHandler(evName);
      }
      this.handlers[evName][toEval] = toEval;
    }
  }

  this.removeEvent = function(evName, toEval){
    delete this.handlers[evName];
  }
  // clears event from all binded code
  this.clearEvent = function(evName, toEval){
    msg = '';
    if (this.handlers[evName]){
      
      if (toEval){
        delete this.handlers[evName][toEval];
      }else{
        this.handlers[evName] = new Array();
      }
    }
  }
  
  this.addHandler = function(hName){
    this.handlers[hName] = new Array();
  }
  
  this.get = function(id){
    for(var key in this.FObjects)
    {
      if(this.FObjects[key].id == id)
        return this.FObjects[key];
    }
  }
  this.add = function(obj){
    this.FObjects.push(obj);
  }
  
  /** this function will be callen on each resize */
  this.startResize = function(){
    F.clientW = Math.max(document.getElementsByTagName('html')[0].clientWidth, document.body.clientWidth);
    F.clientH = Math.max(document.getElementsByTagName('html')[0].clientHeight, document.body.clientHeight);

    F.handleEvent('onresize');
    F.handleEvent('ajaxonresize');
    //delete window.resizeEvent;
  }

}

function FObject(id)
{
  this.id = id;
}


