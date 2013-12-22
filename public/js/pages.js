/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2012
 */

function Photo() {

  this.photo_sized_margin = 0.4;

  this.openPhoto = function(el) 
  {
    $('#photoModal').modal();
    var img = $('<img src="'+$(el).children().first().attr('src')+'" />').click(Photo.nextPhoto);
    //img.css();
    $('#photoModal .modal-body').html('').append(img);
    $('.current-photo').removeClass('current-photo');
    $(el).parent().addClass('current-photo');
  }

  this.nextPhoto = function() 
  {
    var el = $('.current-photo').next('.photo');
    if(!el[0]) {
      el = $('.photo').first();  
    }
    Photo.setImage(el);
  }

  this.prevPhoto = function() 
  {
    var el = $('.current-photo').prev('.photo');
    if(!el[0]) {
      el = $('.photo').last();  
    }
    Photo.setImage(el);
  }
  
  this.setImage = function(el)
  {
    $('#photoModal .modal-body').html('').append($('<img src="'+el.find('img').attr('src')+'" />').click(Photo.nextPhoto));
    $('.current-photo').removeClass('current-photo');
    el.addClass('current-photo');
  }
  
  this.createRows = function(row_width, row_height, is_big) 
  {
    this.row_height = row_height;
    data = [];
    var margin = 2;
    $('.photo').each(function(){
      var img = $(this).find('img');
      var width = 0;
      var height = 0;
      if(!img.data('w')) 
      {
        width = img.width();
        height = img.height();
        img.attr('data-w', width).attr('data-h', height);
      } else {
        width = img.data('w');
        height = img.data('h');
      }
      var toWidth = width/(height/row_height);
      data.push({'width' : width, 'height' : height, k : width/height, 'toWidth':toWidth, 'img' : img, 'div':$(this)})
    });
    
    var current_row = row_width;
    var row = [];
    var row_num = 1;
    var big_width = 0;
    var max_elements = 0;
    var elements_in_row = 0;
    if(is_big)
    {
      if(is_big == parseInt(is_big))
      {
        var rand = is_big - 1;
      } else if((typeof is_big) == 'object') {
        var rand = $(is_big).index();
      } else {
        var rand = Math.floor((Math.random() * data.length));
      }
      big_width = data[rand].width/(data[rand].height/((row_height*2)+margin));
      //info('big_width=' + big_width);
      data[rand].img.width(big_width).css('margin-top',0);
      data[rand].div.prependTo(data[rand].div.parent()).height((row_height * 2) + margin).unbind('click');//.click(function(){Photo.openPhoto(this)});
      current_row -= big_width + margin;
    }
    for(var i = 0; i < data.length; i++)
    {
      if(current_row < data[i].toWidth) 
      {
        if(row.length < 1) {
          var d_add = data[i].toWidth - current_row > 1 ? data[i].toWidth - current_row : margin ;
          
          info('row is empty. we add '+d_add + ' it is '+row_num+' row');
          Photo.createRows(row_width + d_add, row_height, 1);
          return false;
        }
        Photo.fitRow(row, current_row);
        row = [];
        current_row = row_width;
        
        if(is_big && row_num <= 2) {elements_in_row++;}
        if(elements_in_row > max_elements) {max_elements = elements_in_row;}
        elements_in_row = 0;
        row_num++;
        if(is_big && row_num == 2)
        {
          current_row -= big_width + margin;
        }
      }
      if(!is_big || rand != i) 
      {
        if(current_row < data[i].toWidth + margin)
        {
          tempData = Photo.findReplacement(current_row, data, i);
          if(!tempData) {
            //info(data[i]);
            hoto.createRows(row_width + current_row - data[i].toWidth, row_height, 1);
//            $('.photo-gallery').css({visibility:'visible', 'width':row_width + (max_elements * margin)});
//            $('.loader').hide();
            
            return false;
          } else {
            data = tempData;
          }
        }
        data[i].div.unbind('click').click(function(){
          Photo.createRows(row_width, row_height, this);
        });
        row.push(data[i]);
        current_row -= data[i].toWidth + margin;
        elements_in_row++;
      }
    }
    //info('last_row' +current_row);
   
    Photo.fitRow(row,current_row);
    
    $('.photo-gallery').css({visibility:'visible', 'width':row_width + (max_elements * margin)});
    $('.loader').hide();
  }
  
  this.fitRow = function(row, width_fitting) 
  {
//    info(width_fitting);
    var row_fit_arr = [];
    var all_fit = 0;
    for(var i = 0; i < row.length; i++)
    {
      var can_fit = row[i].width-row[i].toWidth;
      row_fit_arr[i] = can_fit;
      all_fit += can_fit;
    }
    for(var i = 0; i < row.length; i++)
    {
      var avr = ( row_fit_arr[i] / all_fit ) * width_fitting;
      row[i].img.width(row[i].toWidth + avr);
      row[i].div.height(this.row_height);
	  var k = row[i].img.data('down-shift') ? row[i].img.data('down-shift') : this.photo_sized_margin;
      var d_height = (avr / row[i].k) * k;
      row[i].img.css({'margin-top':-d_height});
    }
  }
  this.findReplacement = function(left_width, data, i) 
  {
    for(var k = i; k < data.length; k++)
    {
      if(data[k].toWidth <= left_width) 
      {
        var temp = data[i];
        data[i] = data[k];
        data[k] = temp;
        swapNodes(data[i].div[0], data[k].div[0]);
        return data;
      }
    }
    return false;
  }
}

Photo = new Photo();

function swapNodes(a, b) 
{
  var aparent= a.parentNode;
  var asibling= a.nextSibling===b? a : a.nextSibling;
  b.parentNode.insertBefore(a, b);
  aparent.insertBefore(b, asibling);
}