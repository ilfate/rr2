/**
 * Created by Ilya Rubinchik (ilfate) on 12/09/14.
 */

function rand(min, max)
{
    return Math.floor(Math.random()*(max-min+1)+min);
}

function debug(data) {
    info(data);
}
function isInt(n){
    return typeof n== "number" && isFinite(n) && n%1===0;
}

function TD () {

}
TD = new TD();

$(document).ready(function() {

    $('body').append('<div id="tdMap"></div>');
   var game = new TD.Game();
    game.init();
});

TD.Game = function () {
    this.facet      = new TD.Facet(this);
    this.mapConfig  = {};
    this.currentMap = {};
    this.newMap     = {};
    this.running    = true;
    this.units      = {};
    this.lastUnitId = 1;

    this.statsKilledPower   = 0;
    this.statsKilledUnits   = 0;
    this.statsLostUnits     = 0;
    this.statsLostPower     = 0;
    this.statsTicksSurvived = 0;
    this.statsPoints        = 0;

    this.pointsPerKill   = 5;

    this.spawnBotsEveryTick = 1;
    this.turnsBotWasSpawnd  = 0;

    this.init = function() {
        this.mapConfig = new TD.Map.Config();
        this.mapConfig.setSize(5);
        this.mapConfig.setSpawn();

        this.currentMap = new TD.Map(this.facet, this.mapConfig);

        TD.Facet = this.facet;

        this.spawnPlayerUnit();
        this.spawnBotUnit();
        this.currentMap.drawMap();
        this.currentMap.draw(this.units);
    }

    this.getNewUnitId = function () {
        return this.lastUnitId++;
    }

    this.setUnit = function(unit) {
        debug('new unit:' + unit.getId() + '. Owner='+unit.owner);
        this.units[unit.getId()] = unit;
        this.currentMap.setUnit(unit);
    }

    this.removeUnit = function (unit) {
        debug ('remove unit id = ' + unit.getId());
        delete this.units[unit.getId()];
        //this.newMap.removeUnit(unit);
    }

    this.getCenter = function() {
        return this.currentMap.getCenter();
    }

    this.checkUnitDirection = function(unit) {
        this.currentMap.checkUnitDirection(unit);
    }

    this.spawnPlayerUnit = function () {
        if (!this.running) {
            debug ('can`t spawn unit. Game is stopped');
            return;
        }
        var center = this.currentMap.getCenter();
        var unitIdInCenter = this.currentMap.get(center.x, center.y);
        if (!unitIdInCenter) {
            // spawn only if center is empty.
            var unit = new TD.Unit(this);
            unit.setPosition(center.x, center.y);
            unit.setOwner('player');
            unit.init();
        }
    }

    this.spawnBotUnit = function () {
        if (!this.running) {
            debug ('can`t spawn unit. Game is stopped');
            return;
        }
        if (this.turnsBotWasSpawnd == 0) {
            this.turnsBotWasSpawnd = this.spawnBotsEveryTick;
        } else {
            this.turnsBotWasSpawnd--;
            return;
        }
        var emptyCell = false;
        for (var i = 0; i < 5; i++) {
            //we will do 3 attempts to find empty cell.
            var cell = this.currentMap.getRandomBotSpawnCell();
            if (!this.currentMap.get(cell.x, cell.y)) {
                debug('random coordinats x = ' + cell.x + ' y = ' + cell.y);
                emptyCell = true;
                break;
            }
        }
        if (!emptyCell) {
            // we failed to find empty cell
            return;
        }
        var unit = new TD.Unit(this);
        unit.setPosition(cell.x, cell.y);
        unit.setOwner('bot');
        unit.activate();
        unit.init();
        this.currentMap.botUnitDirectionSetup(unit);
    }

    this.tick = function() {
        if (!this.running) {
            debug('Game will not tick anymore!');
            return;
        }

        this.newMap = new TD.Map(this.facet, this.mapConfig);
        // boost units
        // move units
        for (var unitId in this.units) {
            this.units[unitId].tick();
        }
        // all unit moved.
        this.duels();
        this.battles();

        this.currentMap = this.newMap;
        this.newMap = {};

        // Spawn for player
        this.spawnPlayerUnit();

        // Spawn for bot
        this.spawnBotUnit();
        this.currentMap.draw(this.units);
        this.checkLoseConditions();
        this.statsTicksSurvived++;
    }

    this.duels = function () {
        for (var unitId in this.units) {
            var unit1 = this.units[unitId];
            if (unit1.active) {
                var unitIdWasInCell = this.currentMap.get(unit1.x, unit1.y);
                if (unitIdWasInCell && this.units[unitIdWasInCell] !== undefined) {
                    var unit2 = this.units[unitIdWasInCell];
                    if (unit2.x == unit1.oldX && unit2.y == unit1.oldY) {
                        // DUEL BEGINS!
                        debug('Duel p1:' + unit1.power + ' p2:' + unit2.power);
                        if (unit1.power > unit2.power) {
                            var winner = unit1;
                            var loser  = unit2;
                        } else {
                            var winner = unit2;
                            var loser  = unit1;
                        }
                        if (unit1.owner == unit2.owner) {
                            // well actualy it is not a duel, but a union
                            winner.power = unit1.power + unit2.power;
                        } else {
                            // yea here they will actually battle!
                            winner.power = winner.power - loser.power;
                            this.statsBattle(winner, loser);
                        }
                        debug('remove unit duel');
                        this.removeUnit(loser);
                        if (winner.power == 0) {
                            debug('remove unit duel and winner');
                            this.removeUnit(winner);
                        }
                    }
                }
            }
        }
    }

    this.battles = function() {
        for (var unitId in this.units) {
            var unit1 = this.units[unitId];
            var existingUnitId = this.newMap.get(unit1.x, unit1.y);
            if (existingUnitId) {
                // here will be battle

                var unit2 = this.units[existingUnitId];
                debug('Battle p1:' + unit1.power + ' p2:' + unit2.power);
                if (unit1.power > unit2.power) {
                    var winner = unit1;
                    var loser  = unit2;
                    this.newMap.setUnit(unit1, true);
                } else {
                    var winner = unit2;
                    var loser  = unit1;
                }
                if (unit1.owner == unit2.owner) {
                    // well actualy it is not a duel, but a union
                    winner.power = winner.power + loser.power;
                } else {
                    // yea here they will actually battle!
                    winner.power = winner.power - loser.power;
                    this.statsBattle(winner, loser);
                }
                debug('winner power:' + winner.power);
                this.removeUnit(loser);
                if (winner.power == 0) {
                    debug('remove unit battle and winner');
                    this.removeUnit(winner);
                }
            } else {
                debug ('set unit to map without battle p = ' + unit1.power);
                // there is no one to battle
                this.newMap.setUnit(unit1);
            }
        }
    }

    this.statsBattle = function(winner, loser) {
        if (winner.owner == 'player') {
            this.statsKilledUnits ++;
            this.statsPoints += this.pointsPerKill;
            if (winner.power == 0) {
                this.statsLostUnits ++;
            }
        } else {
            this.statsLostUnits ++;
            if (winner.power == 0) {
                this.statsKilledUnits ++;
                this.statsPoints += this.pointsPerKill;
            }
        }
        this.statsKilledPower += loser.power;
        this.statsLostPower   += loser.power;
        this.statsPoints      += loser.power
    }

    this.userActionMoveUnit = function(unitId, direction) {
        debug('unit move');
        this.checkUserUnit(unitId);
        var unit = this.units[unitId];
        if (unit.direction == direction && unit.active == true) {
            this.facet.stopGame();
            debug('unit is already moving to this direction');
            return;
        }
        if (!isInt(direction) || direction < 0 || direction > 3) {
            this.facet.stopGame();
            debug('wrong direction provided by user');
            return;
        }
        // ok we save from any bullshit

        unit.direction = direction;
        unit.activate();
        this.tick();
    }

    this.userActionStopUnit = function (unitId) {
        this.checkUserUnit(unitId);
        var unit = this.units[unitId];
        unit.deactivate();
        this.tick();
    }

    this.checkUserUnit = function(unitId) {
        if (!unitId || this.units[unitId] == undefined) {
            this.facet.stopGame();
            debug('unit not found');
            return;
        }
        var unit = this.units[unitId];
        if (unit.owner != 'player') {
            this.facet.stopGame();
            debug('unit don`t belong to user');
            return;
        }
    }

    this.checkLoseConditions = function () {
        var center = this.currentMap.getCenter();
        unitId = this.currentMap.get(center.x, center.y);
        if (unitId && this.units[unitId] !== undefined) {
            if (this.units[unitId].owner != 'player') {
                this.stop();
                debug('You killed ' + this.statsKilledUnits + ' units!');
                debug('You absorbed ' + this.statsKilledPower + ' points of power!');
                debug('You lost ' + this.statsLostUnits + ' units!');
                debug('You survived ' + this.statsTicksSurvived + ' turns!');
                debug('You earned ' + this.statsPoints + ' points!');
                alert('You lost!');
            }
        }
    }

    this.stop = function () {
        this.running = false;
    }




}