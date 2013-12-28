/**
 * Created by ilfate on 12/27/13.
 */



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
      this.time = parseInt(time);
      this.lastTickTime = this.time - this.tickTime;
      // yep, I know this is shit...
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
    // we will move camera to oposit direction
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
    if (!this.cells[x]) {
      this.cells[x] = [];
    }
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
          case 'map':
            this.unitWithCamera = this.log[currentTime][i][0];
            var rawMap = this.log[currentTime][i][2][0];
            var map = rawMap.split('|');
            var k = 0;
            for(var x = -this.vision_radius; x <= this.vision_radius; x++) {
              if (!this.cells[x]) {
                this.cells[x] = [];
              }
              for(var y = -this.vision_radius; y <= this.vision_radius; y++) {
                if (!this.cells[x][y]) {
                  this.cells[x][y] = new IL.Cell(new IL.Point(x, y), this.getSpriteType(map[k]));
                } else {
                  this.cells[x][y].sprite = this.getSpriteType(map[k]);
                }
                k++;
              }
            }
          break;
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