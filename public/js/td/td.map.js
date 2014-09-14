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
        var randomNumber = Math.floor(Math.random() * (this.config.getTotalSpawnCells() + 1));
        if (randomNumber > this.config.getTotalSpawnCells()) randomNumber = this.config.getTotalSpawnCells();
        return this.config.getSpawnCellsFlat(randomNumber);
    }

    this.getCenter = function() {
        return  this.config.getCenter();
    }

    this.setUnit = function(unit, isWinner) {
        info ('set to Map - x:' + unit.x + ' y:' + unit.y);

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

    this.get = function(x, y) {
        if (this.unitIds[x] !== undefined) {
            if (this.unitIds[x][y] !== undefined) {
                return this.unitIds[x][y];
            }
        }
        return false;
    }
}