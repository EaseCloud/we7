/*
 * Swipe.Config.js
 *
 * Brad Birdsall
 * Copyright 2013, MIT License
 *
*/

var mySwipe = Swipe(document.getElementById('mySwipe'), {
  auto: 3000,
  speed:600,
  continuous: true,
  disableScroll: true,
  stopPropagation: true,
  callback: function(index, element) {
    slideTab(index);
  }
});
//点击数字导航跳转
var bullets = document.getElementById('dots').getElementsByTagName('a');
for (var i=0; i < bullets.length; i++) {
  var elem = bullets[i];
  elem.setAttribute('data-tab', i);
  elem.onclick = function(){
    mySwipe.slide(parseInt(this.getAttribute('data-tab'), 10), 500);
  }
}
//高亮当前数字导航
function slideTab(index){
  var i = bullets.length;
  while (i--) {
    bullets[i].className = bullets[i].className.replace('on',' ');
  }
  bullets[index].className = 'on';
};
(function() {
  var win = window,
      doc = win.document;
  if ( !location.hash || !win.addEventListener ) {
    window.scrollTo( 0, 1 );
    var scrollTop = 1,
    bodycheck = setInterval(function(){
      if( doc.body ){
        clearInterval( bodycheck );
        scrollTop = "scrollTop" in doc.body ? doc.body.scrollTop : 1;
        win.scrollTo( 0, scrollTop === 1 ? 0 : 1 );
      } 
    }, 15 );
    if (win.addEventListener) {
      win.addEventListener("load", function(){
        setTimeout(function(){
          //reset to hide addr bar at onload
          win.scrollTo( 0, scrollTop === 1 ? 0 : 1 );
        }, 0);
      }, false );
    }
  }
})();
