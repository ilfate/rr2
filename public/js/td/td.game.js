/**
 * Created by Ilya Rubinchik (ilfate) on 12/09/14.
 */


function TD () {

}
TD = new TD();

$(document).ready(function() {

   var game = new TD.Game();
    game.init();
    game.tick();
});

TD.Game = function () {
    this.facet      = new TD.Facet(this);
    this.mapConfig  = {};
    this.currentMap = {};
    this.newMap     = {};
    this.running    = true;

    this.init = function() {
        this.mapConfig = new TD.Map.Config();
        this.mapConfig.setSize(5);

        this.currentMap = new TD.Map(this.facet, this.mapConfig);

        TD.Facet = this.facet;

        this.spawnPlayerUnit();
    }

    this.units = [];
    this.lastUnitId = 0;


    this.getNewUnitId = function () {
        return this.lastUnitId++;
    }

    this.setUnit = function(unit) {
        info('new unit:' + unit.getId());
        this.units[unit.getId()] = unit;
        this.currentMap.setUnit(unit);
    }

    this.removeUnit = function (unit) {
        info ('remove unit id = ' + unit.id);
        info (this.units);
        this.units.splice(unit.getId(), 1);
        info (this.units);
    }

    this.spawnPlayerUnit = function () {
        if (!this.running) {
            info ('can`t spawn unit. Game is stopped');
            return;
        }
        var unit = new TD.Unit(this.facet);
        var center = this.currentMap.getCenter();
        unit.setPosition(center.x, center.y);
        unit.setOwner('player');
        unit.init();
    }

    this.spawnBotUnit = function () {
        if (!this.running) {
            info ('can`t spawn unit. Game is stopped');
            return;
        }
        var unit = new TD.Unit(this.facet);
        var cell = this.currentMap.getRandomBotSpawnCell();
        unit.setPosition(cell.x, cell.y);
        unit.setOwner('bot');
        unit.init();
    }

    this.tick = function() {
        if (!this.running) {
            info('Game will not tick anymore!');
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
        // Spawn for bot
    }

    this.duels = function () {
        for (var unitId in this.units) {
            var unit1 = this.units[unitId];
            var unitIdWasInCell = this.currentMap.get(unit1.x, unit1.y);
            if (unitIdWasInCell) {
                var unit2 = this.units[unitIdWasInCell];
                if (unit2.x == unit1.oldX && unit2.y == unit1.oldY) {
                    // DUEL BEGINS!
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
                    }
                    loser.remove();
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
                }
                loser.remove();
            } else {
                // there is no one to battle
                this.newMap.setUnit(unit1);
            }
        }
    }

//    this.moveUnit = function(unit) {
//        var existingUnitId = this.newMap.get(unit.x, unit.y);
//        if (existingUnitId) {
//            // ok, here we want to put unit to a cell, but some one is already there.
//            this.addBattle(unit.x, unit.y, unit.getId(), existingUnitId);
//        }
//        unitIdWasOnThatCell = this.currentMap.get(unit.x, unit.y);
//        unitWasOnThatCell = this.units[unitIdWasOnThatCell];
//        if (unitWasOnThatCell.x = unit.oldX && unitWasOnThatCell.y == unit.oldY) {
//            this.addDuel(unit.getId(), unitIdWasOnThatCell);
//        }
//        this.newMap.setUnit(unit);
//    }

    this.addBattle = function (unit1Id, unit2Id, resultX, resultY) {
        this.battles.push({'unit1Id' : unit1Id, 'unit2Id' : unit2Id, 'resultX' : resultX, 'resultY' : resultY});
    }
    this.addDuel = function (unit1Id, unit2Id) {
        this.battles.push({'unit1Id' : unit1Id, 'unit2Id' : unit2Id, 'resultX' : resultX, 'resultY' : resultY});
    }

    this.stop = function () {
        this.running = false;
    }




}