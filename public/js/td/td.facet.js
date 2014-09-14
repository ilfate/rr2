/**
 * Created by Ilya Rubinchik (ilfate) on 12/09/14.
 */


TD.Facet = function (game) {
    this.game = game;
    this.units = [];



    this.getNewUnitId = function() {
        return this.game.getNewUnitId();
    }

    this.setUnit = function(unit) {
        this.game.setUnit(unit);
    }

    this.getCenter = function () {
        return this.game.mapConfig.getCenter();
    }

    this.stopGame = function() {
        this.game.stop();
    }
}