/**
 * Created by ilfate on 12/27/13.
 */


IL.Units = function(container, map, log)
{
  this.container = container;
  this.map = map;
  this.log = log;
  this.needDraw = true;
  this.units = [];
  this.time = 0;
  this.lastProcessedTime = 0;
  this.tickTime = 200;

  this.container.x = this.map.container.x + this.map.cell_width / 2;
  this.container.y = this.map.container.y + this.map.cell_width / 2;

  this.map.bindObject(this);

//  this.point = new IL.Point(this.map.centerPoint.x, this.map.centerPoint.y);
//  this.animation = new IL.Animation("move");
//
//  this.img =  new createjs.Bitmap(CanvasActions.getObject("map"));
//  this.img.sourceRect = new createjs.Rectangle(9 * this.map.cell_width,8 * this.map.cell_width, 64, 64);
//  this.img.regX = this.map.cell_width / 2;
//  this.img.regY = this.map.cell_width / 2;

  this.init = function() {
    for (var time in this.log) {
      this.time = this.lastProcessedTime = time;
      this.processLog(this.lastProcessedTime);
      break;
    }
  }

  this.processLog = function(time)
  {
    for (var i in this.log[time]) {
      switch (this.log[time][i][1]) {
        case 'init' :
          // here we will add new unit to draw it
          this.addUnit(this.log[time][i][0], this.log[time][i][2]);
          break;
        default:
          // here we will do nothing coz we need only to bootstrap visible units here
          break;
      }
    }
  }
  this.addUnit = function(unitId, unitData)
  {
    if (this.units[unitId]) {
      info("UNITS: trying to recreate already exiting unit id=" + unitId);
      return false;
    }
    var unit_container = new createjs.Container();
    var newUnit = new IL.Unit(unit_container, this.map, this);
    newUnit.init(unitId, unitData);
    //newUnit.draw();
    this.units[unitId] = newUnit;
    this.container.addChild(unit_container);
  }
  this.action = function(target, actionCode, data)
  {
    switch (actionCode) {
      case 'new':
        this.addUnit(target, data);
        this.units[target].cell = this.map.newCells[data[2]][data[3]];
        this.map.newCells[data[2]][data[3]].units.push(this.units[target]);
        break;
      case 'mf':
        // unit moves forward!
        this.units[target].move();
        break;
    }
  }

  this.move = function(x, y, speed)
  {
//    for (var i in this.units) {
//      this.units[i].move(x, y, speed);
//    }
    return this;
  }
  this.rotate = function(side)
  {
    if(this.animation.isRunning()) return false;
    this.animation.setStart(this.img.rotation);
    if(side > 0) {
      this.direction++;
      if(this.direction > 3) {
        this.direction = 0;
        this.animation.setStart(-90);
      }
    }
    if(side < 0) {
      this.direction--;
      if(this.direction < 0) {
        this.direction = 3;
        this.animation.setStart(360);
      }
    }
    this.animation
      .setType("rotate")
      .setSpeed(150)
      .setEnd(90 * this.direction)
      .start();
    this.needDraw = true;

    return this;
  }
  this.forward = function()
  {
    switch(this.direction){
      case 0:
        this.move(0, -1);
        break;
      case 1:
        this.move(1, 0);
        break;
      case 2:
        this.move(0, 1);
        break;
      case 3:
        this.move(-1, 0);
        break;
    }
  }
  this.backward = function()
  {
    switch(this.direction){
      case 0:
        this.move(0, 1);
        break;
      case 1:
        this.move(-1, 0);
        break;
      case 2:
        this.move(0, -1);
        break;
      case 3:
        this.move(1, 0);
        break;
    }
  }
  this.getPosition = function()
  {
    if(this.animation.isRunning() && this.animation.isType("move"))
    {
      return this.animation.getLast();
    } else {
      return new IL.Point(this.point.x * this.map.cell_width, this.point.y * this.map.cell_width);
    }
  }
  this.checkAngle = function()
  {
    if(this.animation.isRunning() && this.animation.isType("rotate")) {
      this.img.rotation = this.animation.getLast();
    } else {
      this.img.rotation = 90 * this.direction;
    }
  }
  this.draw = function(elapsedTime)
  {
    for (var i in this.units) {
      this.units[i].draw(elapsedTime);
    }
    return false;
  }
  this.update = function()
  {
  }
}