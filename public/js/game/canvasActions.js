/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2012
 */

CanvasActions = function() {

  this.tick_methods = [];
  this.objects = [];
  this.object_names = [];
  this.stageMap = {};
  this.stageUnits = {};
  this.loader = new createjs.PreloadJS();
  this.assets = [];
  this.log = {};
  this.mapInfo = {};

  this.init = function(mapInfo, log)
  {
    this.log = log;
    this.mapInfo = mapInfo;
    var canvasMap = document.getElementById("canvasMap");
    var canvasUnits = document.getElementById("canvasUnits");
    this.stageMap = new createjs.Stage(canvasMap);
    this.stageUnits = new createjs.Stage(canvasUnits);
    //this.stageMap.enableMouseOver(10);
    //this.stageMap.mouseMoveOutside = true;
    this.containerMap = new createjs.Container();
    this.containerUnits = new createjs.Container();
    this.height = this.width = (this.mapInfo['screen_size'] * 2 + 1) * this.mapInfo['cell_size'];
    $('#canvasMap').attr({'height' : this.height, 'width' : this.width});
    $('#canvasUnits').attr({'height' : this.height, 'width' : this.width});

    var manifest = this.getMapManifest();
    this.loader.loadManifest(manifest);

    this.loader.onFileLoad = function(event){CanvasActions.handleFileLoad(event)};
    this.loader.onComplete = function(){CanvasActions.afterLoad()};


  }

  this.afterLoad = function()
  {
    this.createMap();
    this.createUnits();

    this.stageMap.addChild(this.containerMap);
    this.stageMap.update();
    this.stageUnits.addChild(this.containerUnits);
    this.stageUnits.update();
    createjs.Ticker.addListener(CanvasActions);
    createjs.Ticker.setFPS(30);
    createjs.Ticker.useRAF = true;
  }

  this.getMapManifest = function()
  {
    return [
//      {src:"/images/game/tile1.png",id:"floor"},
//      {src:"/images/game/tile1_damaged.png",id:"floor_d"},
//      {src:"/images/game/tile2.png",id:"empty"},
//      {src:"/images/game/block.png",id:"wall"},
      {src:"/images/game/map.png",id:"map"}
    ];
  }

  this.createMap = function()
  {
    var map_container = new createjs.Container();
    map_container.x = this.width/2 - 32;
    map_container.y = this.height/2 - 32;
    this.map = new IL.Map(map_container, this.mapInfo, this.log);
    this.map.init();
    //this.map.draw();

    this.addTick(function(elapsedTime){
      CanvasActions.map.draw(elapsedTime);
    });
    this.containerMap.addChild(map_container);
  }

  this.createUnits = function()
  {
    var units_container = new createjs.Container();
    this.units = new IL.Units(units_container, this.map, this.log);
    this.units.init();
    //this.units.draw();
    this.map.bindObject(this.units);
    this.addTick(function(elapsedTime){
      CanvasActions.units.draw(elapsedTime);
    });
    this.containerUnits.addChild(units_container);
  }

  this.addObject = function(obj, name) {
    this.objects.push(obj);
    this.object_names.push(name);
  }
  this.getObject = function(name) {
    var idx = $.inArray(name,this.object_names);
    if(idx != -1) {
      return this.objects[idx];
    } else {
      return false;
    }
  }

  this.addTick = function(func) {
    this.tick_methods.push(func);
  }

  this.tick = function(elapsedTime) {
    if(this.tick_methods)
    {
      for(var i in this.tick_methods)
      {
        this.tick_methods[i](elapsedTime);
      }
    }
    this.stageMap.update(elapsedTime);
    this.stageUnits.update(elapsedTime);
  }

  this.stop = function() {
    createjs.Ticker.setPaused(true);
  }
  this.start = function() {
    createjs.Ticker.setPaused(false);
  }
  this.handleFileLoad = function(event) {
    //CanvasActions.assets.push(event);
  this.addObject(event.result, event.id);
  }
}

CanvasActions = new CanvasActions();


function IL () {

}
IL = new IL();



////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////   POINT    ///////////////
////////////////////////////////////////////////////////////////////////////////

IL.Point = function(x, y)
{
  this.x = x ? x : 0;
  this.y = y ? y : 0;
  this.set = function(x,y)
  {
    this.x = x;
    this.y = y;
    return this;
  }
  this.distance = function(point)
  {
    return Math.sqrt(Math.pow((point.x - this.x), 2) + Math.pow((point.y - this.y), 2));
  }
  this.next = function(direction)
  {
    switch(direction)
    {
      case 0:
        return new IL.Point(this.x, this.y - 1);
      case 1:
        return new IL.Point(this.x + 1, this.y);
      case 2:
        return new IL.Point(this.x, this.y + 1);
      case 3:
        return new IL.Point(this.x - 1, this.y);

    }
  }
}

////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////   CELL     ///////////////
////////////////////////////////////////////////////////////////////////////////

IL.Cell = function(Point, sprite)
{
  this.visible = false;
  this.shape = new createjs.Shape();
  this.window_point = new IL.Point(0, 0);
  this.cutLeft = 0;
  this.cutRight = 0;
  this.cutTop = 0;
  this.cutBottom = 0;
  this.taken = false;
  this.sprite = sprite;
  this.units = [];

  this.newType = true;
  if(Point)
  {
    this.point = Point;
  } else {
    info('error. Cell needs a Point object')
  }

  this.setType = function(type)
  {
    this.type = type;
    this.sprite = CanvasActions.map.getSpriteType(type);
    return this;
  }

  this.runAnimation = function(name)
  {
    this.animation = new IL.Animation("spriteAnimation");
    var anims = [
      ["wall_d1_1", "wall_d1_2", "wall_d1_3", "wall_d1_4", "floor"],
      ["wall_d2_1", "wall_d2_2", "wall_d2_3", "wall_d2_4", "floor"],
      ["wall_d3_1", "wall_d3_2", "wall_d3_3", "wall_d3_4", "floor"]
    ];
    var rand = Math.floor(Math.random() * anims.length);
    if(rand == anims.length) rand--;
    this.animation
      .setSpeed(700)
      .setStart(anims[rand])
      .start()
    return this;
  }
  this.set = function(x, y)
  {
    this.window_point.x = x;
    this.window_point.y = y;
    this.visible = true;
    return this;
  }
  this.cutX = function(cut) {
	  if(cut < 0) {
		this.cutLeft = -cut;
		this.cutRight = 0;
	  } else if(cut > 0) {
		this.cutLeft = 0;
		this.cutRight = cut;
	  } else {
		this.cutRight = this.cutLeft = 0;
	  }
    return this;
  }
  this.cutY = function(cut) {
	  if(cut < 0) {
		this.cutTop = -cut;
		this.cutBottom = 0;
	  } else if(cut > 0) {
		this.cutTop = 0;
		this.cutBottom = cut;
	  } else {
		this.cutTop = this.cutBottom = 0;
	  }
    return this;
  }

  this.isPassable = function()
  {
    if(this.type == "wall" || this.type == "hole" || this.taken) {
      return false;
    }
    return true;
  }
  this.take = function()
  {
    this.taken = true;
    return this;
  }
  this.free = function()
  {
    this.taken = false;
    return this;
  }

  this.addUnit = function(id, unit)
  {
    this.units[id] = unit;
  }

  this.removeUnit = function(id)
  {
    this.units[id] = null;
  }

  this.draw = function(container, width)
  {
    if(this.newType) {
      this.bitmap = new createjs.Bitmap(CanvasActions.getObject("map"));
      this.bitmap.sourceRect = new createjs.Rectangle(0, 0, 0, 0);
      this.newType = false;
    }

    this.bitmap.x = width * this.window_point.x + this.cutLeft;
    this.bitmap.y = width * this.window_point.y + this.cutTop;
    this.bitmap.sourceRect.x = this.sprite.x + this.cutLeft;
    this.bitmap.sourceRect.y = this.sprite.y + this.cutTop;
    this.bitmap.sourceRect.width = width - this.cutLeft - this.cutRight;
    this.bitmap.sourceRect.height = width - this.cutTop - this.cutBottom;

    container.addChild(this.bitmap);

    for (var i in this.units) {
      if (this.units[i]) {
        this.units[i].needDraw = true;
      }
    }

    if(this.animation && this.animation.isRunning())
    {
      var new_type = this.animation.tic();
      if(new_type != this.type)
      this.setType(new_type);
      return true;
    }
    return false; // false means that we dont need to draw it. well cell dont need
  }
}

////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////   ANIMATION    ///////////
////////////////////////////////////////////////////////////////////////////////
IL.Animation = function(type)
{
  this.startObj;
  this.endObj;
  this.type = type;
  this.started = false;
  this.working = false;
  this.repeat = false;
  this.repeat_times = -1; // infinite
  this.speed = 0;
  this.start_time = 0;
  this.last_tic = false;

  this.start = function(repeat)
  {
    this.started = true;
    this.working = true;
    this.start_time = 0;
    this.repeat = !!repeat;
    return this;
  }
  this.stop = function() {
    this.working = false;
    return this;
  }
  this.isRunning = function() {
    return this.working;
  }
  this.setStart = function(e)
  {
    this.startObj = e;
    return this;
  }
  this.setEnd = function(e)
  {
    this.endObj = e;
    return this;
  }
  this.setSpeed = function(speed)
  {
    this.speed = speed;
    return this;
  }
  this.setType = function(type)
  {
    this.type = type;
    return this;
  }
  this.isType = function(type)
  {
    return this.type === type;
  }
  this.tic = function()
  {
    if(!this.working) {
      info('WTF tic try but animation is not working');
      return false;
    }

    if(!this.start_time)
    {
      this.start_time = new Date().getTime();
    }
    var time = new Date().getTime();
    var dT = time - this.start_time;
    if(dT >= this.speed) {
      dT = this.speed;
      if(!this.repeat) {
        this.stop();
      } else {
        if(this.repeat_times != 0) {
          this.repeat_times--;
          this.start(true);
        } else {
          this.stop();
        }
      }
    }
    var k = dT / this.speed;
    switch(this.type) {
      case "move":
        var dX = (this.endObj.x - this.startObj.x) * k + this.startObj.x;
        var dY = (this.endObj.y - this.startObj.y) * k + this.startObj.y;
        this.last_tic = new IL.Point(dX, dY);
        return this.last_tic;
      break;

      case "rotate":
        this.last_tic = (this.endObj - this.startObj) * k + this.startObj;
        return this.last_tic;
        break;

      case "spriteAnimation":
        var idx = Math.round(this.startObj.length * k);
        if(idx == this.startObj.length) idx = this.startObj.length - 1;
        this.last_tic = this.startObj[idx];
        return this.last_tic;
        break;
      case "wait":
        this.last_tic = true;
        return this.last_tic;
        break;
    }
    return false;
  }
  this.getLast = function()
  {
    return this.last_tic;
  }
}


IL.MapData = function ()
{
    this.map = [];

    this.set = function(x, y, cell)
    {
        this.map[x][y] = cell;
    }
}

$(document).keypress(function(event) {
//  info(event);
  switch(event.keyCode)
  {
    case 37: // left
      //CanvasActions.robot.move(-1, 0);
      CanvasActions.robot.rotate(-1);
      break;
    case 38: // down
//      CanvasActions.robot.move(0, -1);
      CanvasActions.robot.backward();
      break;
    case 39: // right
//      CanvasActions.robot.move(1, 0);
      CanvasActions.robot.rotate(1);
      break;
    case 40: // up
      CanvasActions.robot.forward();
      break;
    case 13:  // Enter
      break;
    case 119 : // w
      CanvasActions.robot.forward();
      break;
    case 97 : // a
      CanvasActions.robot.rotate(-1);
      break;
    case 115 : // s
      CanvasActions.robot.backward();
      break;
    case 100 : // d
      CanvasActions.robot.rotate(1);
      break;
    case 32 :  // space
      break;
    case 101 :  // e
      CanvasActions.robot.destroyWall();
      break;
    case 114 :  // r
      break;
    case 102 :  // f
      break;
    case 0 :                  //// For Mozila
      switch(event.charCode)
      {
        case 119 : // w
          CanvasActions.robot.forward();
          break;
        case 97 : // a
          CanvasActions.robot.rotate(-1);
          break;
        case 115 : // s
          CanvasActions.robot.backward();
          break;
        case 100 : // d
          CanvasActions.robot.rotate(1);
          break;
        case 32 :  // space
          break;
        case 101 :  // e
          CanvasActions.robot.destroyWall();
          break;
        case 114 :  // r
          break;
        case 102 :  // f
          break;
      }
      break;
  }
});