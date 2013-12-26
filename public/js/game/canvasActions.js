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
    this.map.draw();

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
    this.units.draw();
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

IL.Map = function(container, mapInfo, log)
{
  this.log = log;
  this.mapInfo = mapInfo;
  this.container = container;
  this.container_def_point = new IL.Point(container.x, container.y)

  this.needDraw = true;
  this.cells = [];
  this.newCells = [];
  this.cell_idx = [];
  this.binded_objects = [];
  this.cell_width = mapInfo['cell_size'];
  this.map_radius = mapInfo['visionRadius'];
  this.map_size   = mapInfo['visionRadius'] * 2 + 1;
  this.vision_radius = mapInfo['visionRadius'];
  this.x = 0;
  this.y = 0;
  this.camera = new IL.Point(0, 0);
  this.center = new IL.Point(0, 0);
  this.time = 0;
  this.lastTickTime = 0;
  this.tickTime = 200;
  this.unitWithCamera = 0;

  this.animation = new IL.Animation("move");

  this.init = function()
  {
    // parse initial map
    for(var time in this.log) {
      this.lastTickTime = this.time = parseInt(time);
      for(var i in this.log[time]) {
        // here we looking for map code. Map is one message from all of this
        if (this.log[time][i][1] == 'map') {
          this.unitWithCamera = this.log[time][i][0];
          var rawMap = this.log[time][i][2][0];
          var map = rawMap.split('|');
          var k = 0;
          for(var x = -this.vision_radius; x <= this.vision_radius; x++) {
            this.cells[x] = [];
            for(var y = -this.vision_radius; y <= this.vision_radius; y++) {
              this.cells[x][y] = new IL.Cell(new IL.Point(x, y), this.getSpriteType(map[k]));
              k++;
            }
          }
          break;
        }
      }
      break;
    }
  }

  this.setCamera = function(x, y, speed)
  {
    // if new point is out of vision radius we set this new point to our vision radius border
    if(Math.abs(x - this.center.x) > this.vision_radius * this.cell_width ) {
        x = this.center.x + this.vision_radius * this.cell_width * ( this.center.x > this.camera.x ? -1 : 1 );
      }
    if(Math.abs(y - this.center.y) > this.vision_radius * this.cell_width ) {
        y = this.center.y + this.vision_radius * this.cell_width * ( this.center.y > this.camera.y ? -1 : 1 );
//        info(y);
      }
    if(!speed) {
      this.camera.x = x;
      this.camera.y = y;
    } else {
      this.animation
        .setSpeed(speed)
        .setStart(this.camera)
        .setEnd(new IL.Point(x, y))
        .start();
    }
    this.needDraw = true;
  }
  this.move = function(x, y)
  {
    var cameraMoveSpeed = 500;
    // we wiil move camera to oposit direction
    this.camera.x += -x * this.cell_width;
    this.camera.y += -y * this.cell_width;
    // now we will move all cells in backward direction
    for(var cellX = -this.vision_radius; cellX <= this.vision_radius; cellX++) {
      if (!this.newCells[cellX - x]) {
        this.newCells[cellX - x] = [];
      }
      for(var cellY = -this.vision_radius; cellY <= this.vision_radius; cellY++) {
        this.cells[cellX][cellY].point.x -= x;
        this.cells[cellX][cellY].point.y -= y;
        this.newCells[cellX - x][cellY - y] = this.cells[cellX][cellY];
      }
    }
    this.cells = this.newCells;
    this.newCells = [];
    this.setCamera(0, 0, cameraMoveSpeed);//(this.map_radius * this.cell_width - this.center.distance(this.camera)) * 5 );
    this.needDraw = true;
  }
  var map = this;
  this.container.onPress = function(evt)
  {
    var offset = {x: map.camera.x + evt.stageX, y: map.camera.y + evt.stageY};

    evt.onMouseMove = function(ev)
    {
      map.setCamera(offset.x - ev.stageX, offset.y - ev.stageY);
    }
  }
  this.bindObject = function(obj)
  {
    this.binded_objects.push(obj);
  }
  this.getSpriteType = function(type)
  {
    var x = 0;
    var y = 0;
    switch(type) {
      case "floor":x = 0;y = 0;break;
      case "a6":x = 8;y = 5;break;
      case "a3":x = 9;y = 6;break;
      case "a7":x = 0;y = 8;break;
      case "a4":x = 7;y = 7;break;
      case "a5":x = 2;y = 7;break;
      case "w1":x = 3;y = 7;break;
      case "wall_d1_1":x = 9;y = 6;break;
      case "wall_d1_2":x = 0;y = 7;break;
      case "wall_d1_3":x = 1;y = 7;break;
      case "wall_d1_4":x = 2;y = 7;break;
      case "wall_d2_1":x = 3;y = 7;break;
      case "wall_d2_2":x = 4;y = 7;break;
      case "wall_d2_3":x = 5;y = 7;break;
      case "wall_d2_4":x = 6;y = 7;break;
      case "wall_d3_1":x = 7;y = 7;break;
      case "wall_d3_2":x = 8;y = 7;break;
      case "wall_d3_3":x = 9;y = 7;break;
      case "wall_d3_4":x = 0;y = 8;break;

      case "floor_d":x = 0;y = 1;break;
      case "s1":x = 2;y = 1;break;
      case "a1":x = 7;y = 0;break;
      case "a2":x = 3;y = 1;break;
      case "floor_4":x = 1;y = 1;break;

      case "monst_1":x = 1;y = 8;break;
      case "monst_2":x = 2;y = 8;break;
      case "monst_3":x = 3;y = 8;break;
      case "monst_4":x = 4;y = 8;break;
      case "monst_5":x = 5;y = 8;break;
      case "monst_6":x = 6;y = 8;break;
      case "monst_7":x = 7;y = 8;break;
      case "monst_8":x = 8;y = 8;break;

      case "robot_1":x = 9;y = 8;break;
      case "robot_2":x = 0;y = 9;break;
      case "robot_3":x = 1;y = 9;break;
      default : alert('no cell of type ' + type + ' found');
    }
    return {"x": x*this.cell_width,"y":y*this.cell_width};
  }

  this.addCell = function(Cell)
  {
    this.cells.push(Cell);
    var name = Cell.point.x + '_' + Cell.point.y;
    this.cell_idx.push(name);
  }
  this.addSimpleCell = function(x, y, type) {
    this.addCell(new IL.Cell(new IL.Point(x, y), type))
  }
  this.getCell = function(x, y)
  {
    if (!this.cells[x][y]) {
      this.cells[x][y] = new IL.Cell(new IL.Point(x, y));
    }
    return this.cells[x][y];
  }
  this.daleteCell = function(x, y) {
    var name = x + '_' + y;
    var idx = $.inArray(name, this.cell_idx)
    if(idx != -1) {
      this.cell_idx[idx] = '';
    }
  }
  this.createPath = function(from, to) {
//    info('CreatePath');
    var matrix = [], // двумерная матрица для хранения клеток
      route = {}; // объект для хранения инфы о найденном пути 

      matrix.opened = []; // открытый список, содержащий клетки, которые должны пройти проверку в последующей итерации цикла
      matrix.closed = []; // закрытый список, отработанные клетки волны, с ними уже ничего не делаем
      matrix.linear = []; // массив всех клеток матрицы в виде одномерного массива

      var start_cell = this.getCell(from.x, from.y);
      start_cell.closed = false;
      start_cell.opened = true;
      start_cell.cost = 0;
      matrix.opened.push(start_cell);
      var cell, iter = 0, done = false;

      while( (cell = matrix.opened.shift()) && iter < 2000 && !done )
      {
        matrix.closed.push(cell);
        cell.closed = true;
        var
          top = this.getCell(cell.point.x, cell.point.y - 1),
          bottom = this.getCell(cell.point.x, cell.point.y + 1),
          left = this.getCell(cell.point.x - 1, cell.point.y),
          right = this.getCell(cell.point.x + 1, cell.point.y);
        var arr = [top,bottom,left,right];
        for(var i in arr) {
          if(arr[i] && !arr[i].closed)
          {
            if(arr[i].point.x == to.x && arr[i].point.y == to.y)
            {
              arr[i].parent = cell;
              done = arr[i];

              break;
            }
            if(!arr[i].opened && arr[i].isPassable())
            {
              arr[i].opened = true;
              arr[i].parent = cell;
              arr[i].cost = function( type )
              {
                var
                  G = ((this.parent.cost && this.parent.cost('g')) || 0) + 10,
                  H = (Math.abs(this.x - to.x)) + (Math.abs(this.y - to.y));
                return (
                  type == 'g' ? G :
                  type == 'h' ? H :
                  G + H
                );
              }
              matrix.opened.push(arr[i]);
            }
          }
        }
        iter++;
      }
      for(var n in matrix.closed) {
        matrix.closed[n].opened = false;
        matrix.closed[n].closed = false;
        matrix.closed[n].cost = 0;
      }
      for(var n1 in matrix.opened) {
        matrix.opened[n1].opened = false;
        matrix.opened[n1].closed = false;
        matrix.opened[n1].cost = 0;
      }
      if(done)
      {
        done.opened = false;
        done.closed = false;
        done.cost = 0;
        var  path = [];
        if(done.point.x == from.x && done.point.y == from.y) {
          return path;
        }
        var next = done.parent;
        while(1)
        {
          if(next.point.x == from.x && next.point.y == from.y)
          {
            return path;
          }
          path.push(next);
          if(next.parent) {
            next = next.parent;
          } else {
            info('error at CreatePath. BackPath');
            break;
          }
        }
      } else {
        info(matrix.opened);
        info("closed =");
        info(matrix.closed);
        return false;
      }
  }
  this.cells_options = [
    "floor", "floor", "floor", "floor",
    "wall", "wall", "wall", "wall", "wall", "wall",
    "floor_d",
    "floor_1",
    "floor_2",
    "floor_3",
    "floor_4"
  ];
  this.loadCell = function(x, y)
  {
    var rand = Math.floor(Math.random() * this.cells_options.length);
    this.addSimpleCell(x, y, this.cells_options[rand]);
    return this.getCell(x, y);
  }

  this.getMiddlePoint = function()
  {
      return this.camera;
  }
  this.checkAllVisibleCells = function()
  {
    var middle = this.getMiddlePoint();
    var pixel_radius = this.map_radius * this.cell_width;
    var left_border = middle.x - pixel_radius;
    var top_border = middle.y - pixel_radius;
    if(left_border < 0) {var left_cut = -(left_border % this.cell_width);}
    else {var left_cut = (this.cell_width - left_border % this.cell_width);}
    if(top_border < 0) {var top_cut = -(top_border % this.cell_width);}
    else {var top_cut = (this.cell_width - top_border % this.cell_width);}

    if(left_cut) {
      this.container.x = this.container_def_point.x - (this.cell_width - left_cut);
    } else {
      this.container.x = this.container_def_point.x;
    }
    if(top_cut) {
      this.container.y = this.container_def_point.y - (this.cell_width - top_cut);
    } else {
      this.container.y = this.container_def_point.y;
    }
    var cells_to_left = (Math.floor(left_border / this.cell_width));
    var cells_to_top = (Math.floor(top_border / this.cell_width));
    var row_end = cells_to_left + this.map_radius*2 + 1 + ((left_cut == 0) ? 0 : 1);
    var col_end = cells_to_top + this.map_radius*2 + 1 + ((top_cut == 0) ? 0 : 1);

      var window_x = -this.map_radius;
      for(var x = cells_to_left; x < row_end; x++,window_x++)
      {
        var window_y = -this.map_radius;
        for(var y = cells_to_top; y < col_end; y++,window_y++)
        {
          var cell = this.getCell(x, y);
          if(!cell) {
            cell = this.loadCell(x, y);
          }
          cell.set(window_x, window_y);
      if(left_cut) {
        if(x == cells_to_left) {
          cell.cutX(left_cut - this.cell_width);
        } else if(x == row_end-1) {
          cell.cutX(left_cut);
        } else {
          cell.cutX(0);
        }
      } else {
        cell.cutX(0);
      }
      if(top_cut) {
        if(y == cells_to_top) {
          cell.cutY(top_cut - this.cell_width);
        } else if(y == col_end-1) {
          cell.cutY(top_cut);
        } else {
          cell.cutY(0);
        }
      } else {
        cell.cutY(0);
      }
      }
    }
  }
  this.draw = function(elapsedTime)
  {
    if (elapsedTime) {
      this.time += elapsedTime;
      if (this.time >= this.lastTickTime + this.tickTime) {
        // it is time to activate new portion of logs
        this.lastTickTime += this.tickTime;
        this.processLog(this.lastTickTime);
      }
    }
    if(this.needDraw)
    {
      for(var i in this.binded_objects)
      {
        this.binded_objects[i].update();
      }
      if(this.animation.isRunning()) {
        this.camera = this.animation.tic();
      }
      this.checkAllVisibleCells();
      this.container.removeAllChildren();
      var cell_need_more_draw = false;
      for(var x in this.cells)
      {
        for(var y in this.cells[x]) {
          if(this.cells[x][y].visible)
          {
            if(this.cells[x][y].draw(this.container, this.cell_width))
            {
              cell_need_more_draw = true;
            }
            this.cells[x][y].visible = false;
          }
        }
      }
      if(!this.animation.isRunning() && !cell_need_more_draw) {
        this.needDraw = false;
      }
    }
  }
  this.processLog = function(currentTime) {
    if (this.log[currentTime]) {
      for(var i in this.log[currentTime]) {
        switch (this.log[currentTime][i][1]) {
          case 'vm':
            // here vision point is moved!
            var direction = this.log[currentTime][i][2][1];
            switch (direction) {
              case 0: this.move(0, -1); break;
              case 1: this.move(1, 0); break;
              case 2: this.move(0, 1); break;
              case 3: this.move(-1, 0); break;
            }
          break;
          case 'c':
            var x = this.log[currentTime][i][2][0];
            var y = this.log[currentTime][i][2][1];
            if (!this.newCells[x]) {
              this.newCells[x] = [];
            }
            var newCell = new IL.Cell(new IL.Point(x, y), this.getSpriteType(this.log[currentTime][i][2][2]));
            this.newCells[x][y] = newCell;
          break;
          default:
            CanvasActions.units.action(this.log[currentTime][i][0], this.log[currentTime][i][1], this.log[currentTime][i][2]);
          break
        }
      }
    }
  }
  this.update = function()
  {
    this.needDraw = true;
  }

}

////////////////////////////////////////////////////////////////////////////////
////////////////////   UNITS  //////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
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
        case 'new' :
          // here we will add new unit to draw it
          this.addUnit(this.log[time][i][0], this.log[time][i][2]);
        break;
        case 'mf':
          // unit moves forward!
          //this.units[this.log[time][i][0]].move();
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
    newUnit.draw();
    this.units[unitId] = newUnit;
    this.container.addChild(unit_container);
  }
  this.action = function(target, actionCode, data)
  {
    switch (actionCode) {
      case 'new':
        this.addUnit(target, data);
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

////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////   UNIT    /////////////
////////////////////////////////////////////////////////////////////////////////

IL.Unit = function(container, map, units)
{
  this.container = container;
  this.map = map;
  this.units = units;
  this.direction = 2;
  this.type = "crab";
  this.name = "";
  this.needDraw = true;
  this.spawn_radius = 0;
  this.spawn = new IL.Point(0, 1);
  this.sprite = new IL.Point(0, 0);
  this.path = false;
  this.no_path = false;
  this.spawned = false;
  this.unitId = 0;
  this.animationMove = new IL.Animation("move");
  this.animation = new IL.Animation("spriteAnimation");
//  this.animation
//    .setSpeed(1000)
//    .setStart(["monst_1", "monst_2", "monst_3", "monst_4", "monst_5", "monst_6", "monst_7", "monst_8"])
//    .start(true);

  this.img =  new createjs.Bitmap(CanvasActions.getObject("map"));
  this.img.sourceRect = new createjs.Rectangle(1 * this.map.cell_width, 8 * this.map.cell_width, this.map.cell_width, this.map.cell_width);
  this.img.regX = this.map.cell_width / 2;
  this.img.regY = this.map.cell_width / 2;

  this.init = function(unitId, data)
  {
    this.health    = data[0];
    this.maxHealth = data[1];
    this.cell      = this.map.getCell(data[2], data[3]); // unit is located on cell not on coordinats
    this.direction = data[4];
    this.subType   = data[6];
    this.unitId    = unitId;
    this.spawned   = true;
    this.setType(data[5]);
    this.cell.addUnit(this.unitId, this);
  }

  this.setName = function(name) {
    this.name = name;
  }

  this.move = function()
  {
    if(this.animationMove.isRunning()) return false;
    var next = this.cell.point.next(this.direction);
    this.cell.removeUnit(this.unitId);
    this.animationMove.setStart(new IL.Point(this.cell.point.x * this.map.cell_width, this.cell.point.y * this.map.cell_width));
    this.cell = this.map.getCell(next.x, next.y);
    this.cell.addUnit(this.unitId, this);

    this.animationMove
      .setEnd(new IL.Point(this.cell.point.x * this.map.cell_width, this.cell.point.y * this.map.cell_width))
      .setSpeed(850)
      .setType("move")
      .start();
    this.update();

    return this;
  }
  this.rotate = function(side)
  {
    if(this.animationMove.isRunning()) return false;
    this.animationMove.setStart(this.img.rotation);
    if(side > 0) {
      this.direction++;
      if(this.direction > 3) {
        this.direction = 0;
        this.animationMove.setStart(-90);
      }
    }
    if(side < 0) {
      this.direction--;
      if(this.direction < 0) {
        this.direction = 3;
        this.animationMove.setStart(360);
      }
    }
    this.animationMove
      .setType("rotate")
      .setSpeed(350)
      .setEnd(90 * this.direction)
      .start();
    this.needDraw = true;

    return this;
  }
  this.getPosition = function()
  {
    if(this.animationMove.isRunning() && this.animationMove.isType("move"))
    {
      return this.animationMove.getLast();
    } else {
      return new IL.Point(this.cell.point.x * this.map.cell_width, this.cell.point.y * this.map.cell_width);
    }
  }
  this.checkAngle = function()
  {
    if(this.animationMove.isRunning() && this.animationMove.isType("rotate")) {
        this.img.rotation = this.animationMove.getLast();
    } else {
      this.img.rotation = 90 * this.direction;
    }
  }
  this.setType = function(type)
  {
    this.type = type;
    this.sprite = CanvasActions.map.getSpriteType("monst_2");
  }
  this.findTarget = function() {
    this.target = CanvasActions.robot;
  }

  this.draw = function(time)
  {
    if(this.needDraw)
    {
      //this.action();
      if(this.animationMove.isRunning()) {
        this.animationMove.tic();
      }
      //this.setType(this.animation.tic());

      this.container.removeAllChildren();
      var position = this.getPosition();
      this.checkAngle();

      switch (this.direction) {
        case 0:
          var cutLeft = this.cell.cutLeft;
          var imgCutLeft = this.cell.cutLeft;
          var cutTop  = this.cell.cutTop;
          var imgCutTop  = this.cell.cutTop;
          var cutRight = this.cell.cutRight;
          var cutBottom  = this.cell.cutBottom;
          if (time && this.unitId == 6) {
            //info("l=" + this.cell.cutLeft + " t=" + this.cell.cutTop + " r" + this.cell.cutRight + " b" + this.cell.cutBottom);
          }
        break;
        case 1:
          var cutLeft = this.cell.cutTop;
          var imgCutLeft = 0;//-this.cell.cutLeft;
          var cutTop  = 0;//this.cell.cutRight;
          var imgCutTop = this.cell.cutTop;
          var cutRight = this.cell.cutBottom;
          var cutBottom  = this.cell.cutLeft;
        break;
        case 2:
          var cutLeft = 0;//this.cell.cutRight;
          var imgCutLeft = 0;//this.cell.cutRight;
          var cutTop  = 0;//this.cell.cutBottom;
          var imgCutTop  = 0;//this.cell.cutBottom;
          var cutRight = this.cell.cutLeft;
          var cutBottom  = this.cell.cutTop;
        break;
        case 3:
          var cutLeft = 0;//this.cell.cutBottom;
          var imgCutLeft = 0;//this.cell.cutBottom;
          var cutTop  = 0;//this.cell.cutLeft;
          var imgCutTop = 0;//this.cell.cutLeft;
          var cutRight = this.cell.cutTop;
          var cutBottom  = this.cell.cutRight;
          break;
      }

      this.img.x = -this.map.camera.x + position.x + imgCutLeft;
      this.img.y = -this.map.camera.y + position.y + imgCutTop;
      this.img.sourceRect.x = this.sprite.x + cutLeft;
      this.img.sourceRect.y = this.sprite.y + cutTop;
      this.img.sourceRect.width = this.map.cell_width - cutLeft - cutRight;
      this.img.sourceRect.height = this.map.cell_width - cutTop - cutBottom;
//      info(this.img.x + ", " + this.img.y);
      this.container.addChild(this.img);
      if (!this.animation.isRunning() && !this.animationMove.isRunning()) {
        this.needDraw = false;
      }
    }
  }
  this.update = function()
  {
    this.needDraw = true;
  }
}

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