/**
 * Created by Ilya Rubinchik (ilfate) on 12/09/14.
 */


TD.Map = function (facet, config) {
    this.facet = facet;
    this.config = config;
    this.unitIds = {};

    this.size  = config.getSize();

    this.getRandomBotSpawnCell = function() {
        // from 0 to total + 1; There is a very small chance that it actually will be total+1.
        var randomNumber = rand(1, this.config.getTotalSpawnCells());
        if (randomNumber > this.config.getTotalSpawnCells()) randomNumber = this.config.getTotalSpawnCells();
        return this.config.getSpawnCellsFlat(randomNumber);
    }

    this.getCenter = function() {
        return  this.config.getCenter();
    }

    this.setUnit = function(unit, isWinner) {

        if (this.unitIds[unit.x] !== undefined) {
            if (!isWinner && this.unitIds[unit.x][unit.y] !== undefined) {
                info ('We can`t put a unit to occupied cell');
                this.facet.stopGame();
                return;
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
            debug('BOT_PATH for:' + unit.getId());
            debug(unit);
            debug(center);
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
        debug('main road directions for x='+x+', y='+y);
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
        debug('side road directions for x='+x+', y='+y);
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

    this.draw = function(units) {
        var mapArea = $('#tdMap');
        mapArea.html('');
        for (var y = 0; y < this.size; y ++) {
            for (var x = 0; x < this.size; x ++) {
                var unitId = this.get(x, y);
                if (unitId && units[unitId] !== undefined) {

                    var unit = units[unitId];
                    var el = $('<div></div>').addClass('tdCell').addClass('tdUnit').html(unit.power);
                    if (unit.owner == 'player') {
                        el.addClass('playerUnit');
                        this.makeButtonsforUnit(el, unit);
                    } else {
                        el.addClass('botUnit');
                    }
                } else {
                    var el = $('<div></div>').addClass('tdCell').addClass('emptyCell');
                    // empty cell
                }
                mapArea.append(el);
            }
        }
    }

    this.makeButtonsforUnit = function(el, unit) {
        var buttons = [];
        if (unit.active == true) {
            buttons.push('stop');
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
                        .addClass('tdStopButton')
                        .bind("click", function(){
                            TD.Facet.userActionStopUnit(unit.getId());
                        });
                    break;
                case 'go_0':
                    button
                        .addClass('tdGoTopButton')
                        .bind("click", function(){
                            TD.Facet.userActionMoveUnit(unit.getId(), 0);
                        });
                    break;
                case 'go_1':
                    button
                        .addClass('tdGoRightButton')
                        .bind("click", function(){
                            TD.Facet.userActionMoveUnit(unit.getId(), 1);
                        });
                    break;
                case 'go_2':
                    button
                        .addClass('tdGoBottomButton')
                        .bind("click", function(){
                            TD.Facet.userActionMoveUnit(unit.getId(), 2);
                        });
                    break;
                case 'go_3':
                    button
                        .addClass('tdGoLeftButton')
                        .bind("click", function(){
                            TD.Facet.userActionMoveUnit(unit.getId(), 3);
                        });
                    break;
            }
            el.append(button);
        }

    }
}


/*
 #tdMap
 .tdUnit
 .emptyCell

 .tdButton
 .tdStopButton
 */