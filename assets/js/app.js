
'use strict';

document.addEventListener('DOMContentLoaded', function () {

  var tabs = document.getElementsByClassName('tabs');
  if (tabs) {
    var _loop = function _loop() {
      var tabListItems = tabs[i].querySelectorAll('li');
      tabListItems.forEach(function (tabListItem) {

        // création d'un écouteur d'évènements sur le clic d'une tab
        tabListItem.addEventListener('click', function () {

          // suppression de la classe is-active sur chacune des tabs avant de la rajouter sur la tab qui a été cliquée
          tabListItems.forEach(function (tabListItem) {
            tabListItem.classList.remove('is-active');
          });
          tabListItem.classList.add('is-active');

          // tabName correspond à la valeur de l'attribut data-tab
          var tabName = tabListItem.dataset.tab;

          // on identifie tous les contenus possibles puis on applique la classe has-display-none si l'ID du contenu ne correspond pas à la valeur de l'attribut data-tab
          tabListItem.closest('.js-tabs-container').querySelectorAll('.js-tab-content').forEach(function (tabContent) {

            if (tabContent.id !== tabName) {
              tabContent.classList.add('has-display-none');
            } else {
              tabContent.classList.remove('has-display-none');
            }
          });
        }, false);
      });
    };

    for (var i = 0; i < tabs.length; i++) {
      _loop();
    }
  }
});



function toggle_vis_ue(e, new_state) { 
    // e is the span containg the clicked +/- icon
    var tr = e.parentNode.parentNode;
    if (new_state == undefined) {
  // current state: use alt attribute of current image
  if (e.childNodes[0].alt == '+') {
            new_state=false;
  } else {
            new_state=true;
  }
    } 
    // find next tr in siblings
    var tr = tr.nextSibling;
    //while ((tr != null) && sibl.tagName == 'TR') {
    var current = true;
    while ((tr != null) && current) {
  if ((tr.nodeType==1) && (tr.tagName == 'TR')) {
      for (var i=0; i < tr.classList.length; i++) {
    if (tr.classList[i] == 'notes_bulletin_row_ue') 
        current = false;
      }
      if (current) {
    if (new_state) {
        tr.style.display = 'none';
    } else {
        tr.style.display = 'table-row';
    }
      }
        }
        tr = tr.nextSibling;  
    }
    if (new_state) {
  e.innerHTML = '<img width="13" height="13" border="0" title="" alt="+" src="imgs/plus_img.png"/>';
    } else {
  e.innerHTML = '<img width="13" height="13" border="0" title="" alt="-" src="imgs/minus_img.png"/>';
    }
}


 var trigger = document.getElementById('toggler4');
var elements = document.getElementsByClassName('toggle4'); 

trigger.addEventListener('click', function(e) {
    e.preventDefault();
    [].forEach.call(elements, function (element) {element.style.display = (element.style.display == 'none') ? 'table-row' : 'none';});
    
});