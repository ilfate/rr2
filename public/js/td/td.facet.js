/**
 * Created by Ilya Rubinchik (ilfate) on 12/09/14.
 */


TD.Facet = function (game) {
    this.game = game;
    this.lockValue = false;

    this.stopGame = function() {
        this.game.stop();
    }

    this.lock = function () {
        this.lockValue = true;
    }
    this.unlock = function () {
        this.lockValue = false;
    }

    this.isLock = function() {
        return this.lockValue;
    }


    this.userActionMoveUnit = function(unitId, direction) {
        if (!this.isLock()) {
            this.lock();
            this.game.userActionMoveUnit(unitId, direction);
            this.unlock();
        }
    }
    this.userActionStopUnit = function(unitId) {
        if (!this.isLock()) {
            this.lock();
            this.game.userActionStopUnit(unitId);
            this.unlock();
        }
    }
}