/**
 * Created by ilfate on 12/27/13.
 */


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
          var cutTop  = 0;//this.cell.cutTop;
          var imgCutTop  = 0;//this.cell.cutTop;
          var cutRight = this.cell.cutRight;
          var cutBottom  = this.cell.cutBottom;
          if (time && this.unitId == 1) {
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