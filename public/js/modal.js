/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2012
 */

Modal = function() {
  
  this.close = function()
  {
    $('#ilfateModal').modal('hide')
  }
  
  this.init = function()
  {
    $('#ilfateModal').on('hidden', function() {
      $(this).data('modal').$element.removeData();
    })
    
    $('#ilfateModal').on('shown', function () {
      F.handleEvent('ajaxonload');
    });
    $('#ilfateModal').on('shown', function () {
      if($('#ilfateModal .modal-body .move-to-footer').length > 0) {
        var el = $('#ilfateModal .modal-footer .custom-footer').html('');
        $('#ilfateModal .modal-body .move-to-footer').appendTo(el).show();
        $('#ilfateModal .default-footer').hide();
      } else {
        $('#ilfateModal .default-footer').show();
      }
      if($('#ilfateModal .modal-body .move-to-header').length > 0) {
        $('#ilfateModal #myModalLabel').html($('#ilfateModal .modal-body .move-to-header').html());
        $('#ilfateModal .modal-body .move-to-header').html('');
      } else {
        $('#ilfateModal #myModalLabel').html('');
      }
      F.handleEvent('ajaxloadcompleted');
    })
  }
}

Modal = new Modal();

$(document).ready(function(){
    Modal.init();
  });
  
  
  