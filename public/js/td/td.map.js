/**
 * Created by Ilya Rubinchik (ilfate) on 12/09/14.
 */


TD.Map = function (facet, config) {
    this.facet = facet;
    this.config = config;
    this.unitIds = {};
    this.mapEl = false;
    this.oneCellPixelSize = 64;
    this.deathAnimations = [];
    this.directionName = ['up', 'right', 'down', 'left'];
    this.bonuses = [];
    this.bonusesList = [];

    this.size   = config.getSize();
    this.center = config.getCenter().x;

    this.getRandomBotSpawnCell = function() {
        // from 0 to total + 1; There is a very small chance that it actually will be total+1.
        var randomNumber = rand(1, this.config.getTotalSpawnCells());
        if (randomNumber > this.config.getTotalSpawnCells()) randomNumber = this.config.getTotalSpawnCells();
        return this.config.getSpawnCellsFlat(randomNumber);
    }

    this.getCenter = function() {
        return  this.config.getCenter();
    }

    this.setUnit = function(unit) {

        if (this.unitIds[unit.x] !== undefined) {
            if (this.unitIds[unit.x][unit.y] !== undefined) {
                info ('We can`t put a unit to occupied cell (But this is ok some times =_=)');
            }
        } else {
            this.unitIds[unit.x] = {};
        }

        this.unitIds[unit.x][unit.y] = unit.getId();
    }

    this.removeUnit = function(unit) {
        var unitOnMap = this.get(unit.x, unit.y);
        if (unitOnMap) {
            this.unitIds[unit.x][unit.y] = undefined;
        }
    }

    this.get = function(x, y) {
        if (this.unitIds[x] !== undefined) {
            if (this.unitIds[x][y] !== undefined) {
                return this.unitIds[x][y];
            }
        }
        return false;
    }

    this.getRandomCoordNotinCenter = function() {
        if (rand(0, 1) == 1) {
            return rand(1, this.center - 2)
        } else {
            return rand(this.center + 2, this.size - 2);
        }
    }

    this.putBonusToMap = function(bonus, x, y) {
        if (!x && !y) {
            bonus.x = this.getRandomCoordNotinCenter();
            bonus.y = this.getRandomCoordNotinCenter();
            if (this.get(bonus.x, bonus.y)) {
                debug ('Can`t put bonus under unit!');
                return false;
            }
        }
        if (this.bonuses[bonus.x] !== undefined) {
            if (this.bonuses[bonus.x][bonus.y] !== undefined) {
                debug ('We can`t put a bonus to occupied cell (But this is ok some times =_=)');
                return false;
            }
        } else {
            this.bonuses[bonus.x] = {};
        }

        this.bonuses[bonus.x][bonus.y] = bonus;
        this.bonusesList.push(bonus);
    }

    this.getBonuses = function (map) {
        this.bonuses = map.bonuses;
        for (var key in map.bonusesList) {
            var bonus = map.bonusesList[key];
            if (bonus.active) {
                this.bonusesList.push(bonus);
            } else {
                delete this.bonuses[bonus.x][bonus.y];
            }
        }
    }

    this.getNextCell = function(x, y, d) {
        switch (d) {
            case 0: y--; break;
            case 1: x++; break;
            case 2: y++; break;
            case 3: x--; break;
        }
        if (x < 0 || y < 0 || x >= this.size || y >= this.size) {
            return false;
        }
        return {'x' : x, 'y': y};
    }

    this.checkUnitDirection = function(unit) {
        var nextCell = this.getNextCell(unit.x, unit.y, unit.direction);
        if (!nextCell) {
            unit.deactivate();
        }

        if (unit.owner == "bot" && (unit.x != unit.oldX || unit.y != unit.oldY)) {
            var center = this.getCenter().x;
            // here we will check is unit need to change direction
            if ((unit.x >= center - 1 && unit.x <= center + 1)
                || (unit.y >= center - 1 && unit.y <= center + 1)) {
                debug('BOT_PATH: unit is in roads');
                // yea unit is on one of the roads.
                // first of all let`s check was this unit already on the road before

                if ((unit.oldX >= center - 1 && unit.oldX <= center + 1)
                    || (unit.oldY >= center - 1 && unit.oldY <= center + 1)) {
                    debug('BOT_PATH: unit was already on roads');
                    // no he was here!
                    // Ok let`s check is he on the main road
                    if (unit.x == center || unit.y == center) {
                        debug('BOT_PATH: unit is on center road');
                        // yes he is on center road
                        // but maybe he already was here?
                        if (unit.oldX == center || unit.oldY == center) {
                            // yes he is on the main road, so we don`t care
                        } else {
                            debug('BOT_PATH: unit just entered center road');
                            // ok! he is on main road now, but he was not before!
                            // so we need change direction!
                            unit.direction = this.getDirectionToCenter(unit.x, unit.y);
                            debug('New direction for unit on main road = ' + unit.direction);
                        }
                    }
                } else {
                    // Unit is just entered the side road
                    // we need to direct him properly
                    unit.direction = this.getDirectionForSideRoad(unit.x, unit.y);
                    debug('New direction for unit on side road = ' + unit.direction);
                }
            }
        }
    }

    this.getDirectionToCenter = function(x, y) {
        //debug('main road directions for x='+x+', y='+y);
        var center = this.getCenter().x;
        if (x != center) {
            if (x < center) {
                return 1;
            } else {
                return 3;
            }
        } else {
            if (y < center) {
                return 2;
            } else {
                return 0;
            }
        }
    }

    this.getDirectionForSideRoad = function(x, y) {
        //debug('side road directions for x='+x+', y='+y);
        var center = this.getCenter().x;
        if (x == center - 1 || x == center + 1) {
            // unit is on vertical road
            if (y < center) {
                return 2;
            } else {
                return 0;
            }
        } else {
            if (x < center) {
                return 1;
            } else {
                return 3;
            }
        }
    }

    this.botUnitDirectionSetup = function(unit) {
        possibleDirections = [];
        if (unit.x == 0) {
            possibleDirections.push(1);
        } else if (unit.x == this.size - 1) {
            possibleDirections.push(3);
        }
        if (unit.y == 0) {
            possibleDirections.push(2);
        } else if (unit.y == this.size - 1) {
            possibleDirections.push(0);
        }
        if (possibleDirections.length > 1) {
            if (rand(0, 1) == 1) {
                unit.direction = possibleDirections[1];
            } else {
                unit.direction = possibleDirections[0];
            }
        } else {
            unit.direction = possibleDirections[0];
        }
    }

    this.animateDeath = function(unit) {
        this.deathAnimations.push({'id' : unit.getId(), 'x' : unit.x, 'y': unit.y});
    }

    this.draw = function(units) {
        this.mapEl = $('#tdMap');
        $('#tdMap .tdUnit').addClass('inUpdate');

        for (var key in units) {
            if (units[key]) {
                this.drawUnit(units[key]);
            }
        }
        for (var key in this.deathAnimations) {
            this.drawDeath(this.deathAnimations[key]);
        }
        this.drawBonus();

        this.deathAnimations = [];
        this.isDrawn = true;
        // ok map is already there.
    }

    this.drawMap = function() {
        var center = this.getCenter().x;
        this.mapEl = $('#tdMap');
        this.mapEl.html('').width(this.size * this.oneCellPixelSize);
        for (var y = 0; y < this.size; y ++) {
            for (var x = 0; x < this.size; x ++) {
                var el = $('<div></div>')
                    .addClass('tdCell')
                    .addClass('cell-'+x+'-'+y)
                    .addClass('emptyCell');
                if ((x >= center - 1 && x <= center + 1)
                    || (y >= center - 1 && y <= center + 1)) {
                    // this cell is part of the road
                    if (x == center || y == center) {
                        // this cell is part of main road
                        el.addClass('main-road');
                    } else {
                        el.addClass('side-road');
                    }
                }
                // empty cell
                this.mapEl.append(el);
            }
        }
        this.drawHelpPath();
    }

    this.drawBonus = function() {
        $('#tdMap .bonus').remove();
        for (var key in this.bonusesList) {
            var bonus = this.bonusesList[key];
            $('.cell-'+bonus.x+'-'+bonus.y).append(
                $('<div>' + bonus.getText() + '</div>').addClass('bonus').addClass('b-' + bonus.type)
            );

        }
    }

    this.helpPathShowCallback = function(x, y) {
        return function() {
            $('.arrow-for-' + x + '-' + y).fadeIn(400);
        }
    }
    this.helpPathHideCallback = function(x, y) {
        return function() {
            $('.arrow-for-' + x + '-' + y).clearQueue().hide().css('opacity', 100);
        }
    }

    this.drawHelpPath = function() {
        for (var y = 0; y < this.size; y ++) {
            for (var x = 0; x < this.size; x ++) {
                if (x == 0 || x == this.size -1 || y == 0 || y == this.size -1) {
                    // this is a spawn for bots
                    var el = $('.cell-'+x+'-'+y);
                    var d = 0;
                    if (x == 0) d = 1;
                    if (x == this.size -1) d = 3;
                    if (y == 0) d = 2;
                    if (y == this.size -1) d = 0;
                    this.drawHelpPathDirection(x, y, d);
                    if (x == y || (x == 0 && y == this.size - 1) || (x == this.size - 1 && y == 0)) {
                        // it is corner cell
                        if (x == 0) {
                            this.drawHelpPathDirection(x, y, 1);
                        } else {
                            this.drawHelpPathDirection(x, y, 3);
                        }
                    }
                    el.bind('mouseenter', this.helpPathShowCallback(x, y))
                    el.bind('mouseleave', this.helpPathHideCallback(x, y))
                }
            }
        }
    }

    this.drawHelpPathDirection = function(x, y, d) {
        var center = this.config.getCenter().x;
        var isOnMainRoad = false;
        var isOnSideRoad = false;
        if (x == center || y == center) {isOnMainRoad = true;}
        if ((x >= center - 1 && x <= center + 1)
            || (y >= center - 1 && y <= center + 1)) {isOnSideRoad = true;}
        var dx = x;
        var dy = y;
        while (dx != center || dy != center) {

            var arrow = $('.cell-'+dx+'-'+dy + ' .arrow-' + d);
            if (!arrow[0]) {
                var newArrow = $('<div></div>')
                    .addClass('arrow')
                    .addClass('arrow-for-' + x + '-' + y)
                    .addClass('arrow-' + d)
                    .addClass('fa fa-long-arrow-' + this.directionName[d])
                $('.cell-'+dx+'-'+dy).append(newArrow)
            } else {
                // we have that arrow
                arrow.addClass('arrow-for-' + x + '-' + y);
            }
            switch (d) {
                case 0: dy--; break;
                case 1: dx++; break;
                case 2: dy++; break;
                case 3: dx--; break;
            }
            if (!isOnSideRoad &&
                ((dx >= center - 1 && dx <= center + 1)
                || (dy >= center - 1 && dy <= center + 1))) {
                d = this.getDirectionForSideRoad(dx, dy);
                isOnSideRoad = true;
            }
            if (!isOnMainRoad &&
                (dx == center || dy == center)) {
                d = this.getDirectionToCenter(dx, dy);
                isOnMainRoad = true;
            }

        }
    }

    this.drawUnit = function(unit) {
        // is unit exist on map
        var el = $('.unit-' + unit.getId());
        if (el[0]) {
            // unit exist
            el.html(unit.power);
            if (unit.owner == 'player') {
                this.makeButtonsforUnit(el, unit);
            }
            el.removeClass('inUpdate');
            el.animate({
                'left' : unit.x * this.oneCellPixelSize,
                'top' : unit.y * this.oneCellPixelSize
            }, 1000);
            el  .removeClass('unit-move-0')
                .removeClass('unit-move-1')
                .removeClass('unit-move-2')
                .removeClass('unit-move-3');
        } else {
            // draw new unit
            var el = $('<div></div>')
                .addClass('tdUnit')
                .addClass('unit-' + unit.getId())
                .html(unit.power);
            if (unit.owner == 'player') {
                el.addClass('playerUnit');
                this.makeButtonsforUnit(el, unit);
            } else {
                el.addClass('botUnit');
            }
            if (unit.isBoss) {
                el.addClass('boss');
            }
            el.hide();
            el.css('left', unit.x * this.oneCellPixelSize);
            el.css('top',  unit.y * this.oneCellPixelSize);
            el.fadeIn(1000)
            this.mapEl.append(el);
        }
        if (unit.active) {
            el.addClass('unit-move-' + unit.direction);
        }
    }

    this.drawDeath = function (deadUnit) {
        var el = $('.unit-' + deadUnit.id);
        el.removeClass('inUpdate');
        el.animate({
            'left' : deadUnit.x * this.oneCellPixelSize,
            'top' : deadUnit.y * this.oneCellPixelSize,
            'opacity' : 0
        }, 1000, function(el) {
            $( this ).remove();
        });
    }

    this.makeButtonsforUnit = function(el, unit) {
        var buttons = [];
        if (unit.active == true) {
            //buttons.push('stop');
            switch (unit.direction) {
                case 0: buttons.push('go_1', 'go_2', 'go_3'); break;
                case 1: buttons.push('go_0', 'go_2', 'go_3'); break;
                case 2: buttons.push('go_0', 'go_1', 'go_3'); break;
                case 3: buttons.push('go_0', 'go_1', 'go_2'); break;
            }
        } else {
            buttons.push('go_0', 'go_1', 'go_2', 'go_3');
        }
        for(var key in buttons) {
            var button = $('<div></div>').addClass('tdButton');
            switch (buttons[key]) {
                case 'stop':
                    button
                        .addClass('fa fa-shield')
                        .bind("click", function(){
                            TD.Facet.userActionStopUnit(unit.getId());
                        });
                    switch (unit.direction) {
                        case 0: button.addClass('tdGoTopButton'); break;
                        case 1: button.addClass('tdGoRightButton'); break;
                        case 2: button.addClass('tdGoBottomButton'); break;
                        case 3: button.addClass('tdGoLeftButton'); break;
                    }
                    break;
                case 'go_0':
                    button
                        .addClass('tdGoTopButton')
                        .addClass('fa fa-arrow-circle-up')
                        .bind("click", function(){
                            TD.Facet.userActionMoveUnit(unit.getId(), 0);
                        });
                    break;
                case 'go_1':
                    button
                        .addClass('tdGoRightButton')
                        .addClass('fa fa-arrow-circle-right')
                        .bind("click", function(){
                            TD.Facet.userActionMoveUnit(unit.getId(), 1);
                        });
                    break;
                case 'go_2':
                    button
                        .addClass('tdGoBottomButton')
                        .addClass('fa fa-arrow-circle-down')
                        .bind("click", function(){
                            TD.Facet.userActionMoveUnit(unit.getId(), 2);
                        });
                    break;
                case 'go_3':
                    button
                        .addClass('tdGoLeftButton')
                        .addClass('fa fa-arrow-circle-left')
                        .bind("click", function(){
                            TD.Facet.userActionMoveUnit(unit.getId(), 3);
                        });
                    break;
                default :
                    button = false;
                    break;
            }
            if (button) {
                el.append(button);
            }
        }

    }
}