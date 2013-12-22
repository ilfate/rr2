/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2012
 */

/**
 * Main js entrance to my engine
 */

function Action() 
{
  this.refresh = function()
  {
    document.location.reload(true);
  }
  this.redirect = function(data)
  {
	  window.location = data;
  }
}
Action = new Action();

function ilAlert(data)
{
  var html = '<div class="alert fade in"><button class="close" data-dismiss="alert" type="button"><i class="icon-off icon-white"></i></button><div id="alert-text">'+data+'</div></div>';
  $('.container.main').prepend(html);
  $('.alert').show('slow');
  $('.alert').alert();
}

function ilfate_init() {
  Ajax.init();
  $('.tip').tooltip();
  $('.tip-bottom').tooltip({placement:'bottom'});
  $('.tip-left').tooltip({placement:'left'});
  $('.tip-right').tooltip({placement:'right'});
  $('.label-stars').starred();
  $('.rounded_block').roundedBlock();
}
$(document).ready(function(){
  ilfate_init();
});

F.manageEvent('ajaxloadcompleted', ilfate_init);


function info(data)
{
  console.info(data);
}


$.fn.starred = function() 
{
  $(this).each(function(event, el){
    el = $(el);
    var value = el.data('value');
    if(el.prev().hasClass('before-stars')) return ;
    el.before('<div class="container-stars"></div>')
    .appendTo(el.prev()).before('<div class="before-stars label"></div>');
    
    el.prev().css({width: el.css('width'), height : el.css('height')});
    var star_div = '<div class="star"><div class="under-star"></div><div class="img-star"></div></div>';
    el.prev().append(star_div + star_div + star_div + star_div);
	
    for(var i = 0; i < 4; i++) {
      if(value < (i+1)*25) {
        var star = el.prev().find('.star .under-star').eq(i);
        if(value < i*25) {
          star.width('0px');
        } else {
          star.width(Math.floor(((value - i*25)/25)*18) + 'px');
        }
      }
    }
	});
};

$.fn.roundedBlock = function() 
{
  $(this).each(function(event, el){
    el = $(el);
    el.bind('mousedown', function(){
      $(this).addClass('mouse_down');
    })
    el.bind('mouseenter', function(){
      $(this).find('.text').bounce();
      $(this).find('img').animate({'opacity':0.4},150);
    })
    el.bind('mouseleave', function(){
      $(this).find('img').animate({'opacity':0.2},150);
      $(this).removeClass('mouse_down');
    })
    .bind('mouseup', function(){
      $(this).removeClass('mouse_down');
    });
	});
};

$.fn.bounce = function() 
{
  var n = 4;
  var intence = 2;
  var height = $(this).height();
  $(this).stop(true, true);
  for(var i = 0; i <= n; i++) {
    $(this).animate({
      'line-height':((i%2===0 ? height + (n-i)*intence : height - (n-i)*intence)+'px')
    },(80+i*5))
  }
};