/**
 * Created by Ilya Rubinchik (ilfate) on 12/09/14.
 */


TD.Map.Config = function () {

    this.size   = 0;
    this.cells  = [];
    this.spawnCells  = [];
    this.spawnCellsFlat  = [];
    this.totalSpawnCells = 0;
    this.center = 0;

    this.setSize = function(size) {
        this.size = size;
        this.center = Math.round(size / 2) - 1;
    }
    this.getSize = function () {
        return this.size;
    }
    this.setCells = function(cells) {
        this.height = cells;
    }

    this.getCenter = function () {
        return {'x' : this.center, 'y' : this.center};
    }

    this.setSpawn = function() {
        if (!this.size) {
            info('Map size it not setup for calculating spawn cells');
            return;
        }
        for (var x = 0; x < this.size; x++) {
            for (var y = 0; y < this.size; y++) {
                if (x == 0 || y == 0 || x == this.size - 1 || y == this.size - 1) {
                    this.totalSpawnCells++;
                    this.spawnCellsFlat[this.totalSpawnCells] = {'x' : x, 'y': y};
                }
            }
        }
    }

    this.getTotalSpawnCells = function () {
        return this.totalSpawnCells;
    }
    this.getSpawnCellsFlat = function (key) {
        return this.spawnCellsFlat[key];
    }

}