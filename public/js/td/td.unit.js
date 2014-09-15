/**
 * Created by Ilya Rubinchik (ilfate) on 12/09/14.
 */


TD.Unit = function (game) {
    this.game = game;
    this.direction = 0;
    this.active    = false;
    this.power     = 1
    this.unitId    = 0;
    this.oldX      = 0
    this.oldY      = 0
    this.x         = 0;
    this.y         = 0;
    this.owner     = '';

    this.init = function () {
        this.unitId = this.game.getNewUnitId();
        this.game.setUnit(this);
    }

    this.tick = function() {
        this.power ++;
        var center = this.game.getCenter();
//        if (center.x == this.x && center.y == this.y) {
//            // center power bonus
//            this.power ++;
//        }
        if (!this.active) {
            // inactive power charge bonus
            this.power ++;
        }

        if (this.active) {
            this.game.checkUnitDirection(this);
        }
        if (this.active) {
            this.move();
        }
    }

    this.move = function(x, y) {
        if (x && y) {
            // move to specified position
        } else {
            switch (this.direction) {
                case 0: x = this.x; y = this.y - 1; break;
                case 1: x = this.x + 1; y = this.y; break;
                case 2: x = this.x; y = this.y + 1; break;
                case 3: x = this.x - 1; y = this.y; break;
            }
        }
        this.oldX = this.x;
        this.oldY = this.y;
        this.x = x;
        this.y = y;
    }

    this.getId = function() {
        return this.unitId;
    }

    this.setPosition = function (x, y) {
        this.x    = x;
        this.oldX = x;
        this.y    = y;
        this.oldY = y;
    }

    this.setOwner = function (owner) {
        switch (owner) {
            case 'player':
            case 'bot':
                break;
            default:
                info('Unit owner is wrong');
                break;
        }
        this.owner = owner;
    }

    this.activate = function() {
        this.active = true;
    }
    this.deactivate = function() {
        this.active = false;
    }


}