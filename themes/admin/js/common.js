/* Clean Admin
 */

jQuery(function($) {
  // main menu
  $('#main-nav > li').bind('mouseover', openSubMenu);
  $('#main-nav > li').bind('mouseout', closeSubMenu);
  function openSubMenu() {
    $(this).find('ul').css('visibility', 'visible');  
  };
  function closeSubMenu() {
    $(this).find('ul').css('visibility', 'hidden');  
  };
  // menu preserves the mouse-over on top-level menu elements when hovering over children
  $('main-nav ul').each(function(i){
    $(this).hover(function(){
      $(this).parent().find('a').slice(0,1).addClass('active');
    },function(){
      $(this).parent().find('a').slice(0,1).removeClass('active');
    });
  });
  
  // sticky main menu
  if ($('#navigation').length && $('#navigation').hasClass('sticky')) {
    // check on load
    positionNav(118);
    
    // check on scroll
    $(window).scroll(function (event) {
      positionNav(118);
    });
  }
  function positionNav(min) {
     if ($.browser.webkit || $.browser.safari) {
       position = $('body').scrollTop();
     } else {
       position = $('html').scrollTop();
     }
   
     if (position >= min) {
       $('#navigation').css('position', 'fixed')
                       .css('top', 0);
     } else {
       $('#navigation').css('position', 'relative')
                       .css('top', '')
                       .css('border-radius', '0')
                       .css('-moz-border-radius', '0')
                       .css('-webkit-border-radius', '0');
     }
  }

  function checkCron() {
    var params = new Object();
    var method = 'cron/status';
    var apiPath = getAPIPath();

    // Set the Parameters
    params['accessKey'] = window.accessKey;

    $.ajax({
        url: apiPath + method,
        data: params,
        success: function( data ) {
            parseResult( data.data );
        },
        error: function (xhr, ajaxOptions, thrownError){
            alert(xhr.status + ' | ' + thrownError);
        },
        dataType: "json"
    });
  }
  function parseResult( data ) {
	var _dispMsg = "** API Error**";

	if( typeof data.isGood != "undefined" ) {
		if ( data.isGood == 'Y' ) {
			_dispMsg = data.Message;
		}
	}
	document.getElementById("cron-info").innerHTML = _dispMsg;
    if ( _dispMsg != "") {
        document.getElementById("cron-info").style.display = 'block';
    } else {
        document.getElementById("cron-info").style.display = 'none';
    }
  }
  // Check the Cron Status Every 5 Seconds
  setInterval(checkCron, 5000);

  // open/close boxes
  $('.show_hide span').click(function(elem){
    $(elem.target).toggleClass('open-icon');
    $(elem.target).parent().siblings('.module-box-inner').slideToggle();
  });
  
  // Tipsy
  $('.image-list li').tipsy({delayIn: 1200, delayOut: 1200, gravity: 'e'});
  
  // PrettyPhoto
  $("a[data-gal^='prettyPhoto']").prettyPhoto();
  
  // fancy scroll to top
  $('a#to-top').hide();
  $(window).scroll(function () {
    if ($(this).scrollTop() > 100) {
      $('a#to-top').fadeIn();
    } else {
      $('a#to-top').fadeOut();
    }
  });
  // scroll body to 0px on click
  $('a#to-top').click(function () {
    $('body,html').animate({
      scrollTop: 0
    }, 800);
    return false;
  });

  // WYSIWYG editor init
  var cleanSettings = {
    onShiftEnter:    {keepDefault:false, replaceWith:'<br />\n'},
    onCtrlEnter:    {keepDefault:false, openWith:'\n<p>', closeWith:'</p>'},
    onTab:        {keepDefault:false, replaceWith:'    '},
    markupSet:  [   
      {name:'Bold', key:'B', openWith:'(!(<strong>|!|<b>)!)', closeWith:'(!(</strong>|!|</b>)!)' },
      {name:'Italic', key:'I', openWith:'(!(<em>|!|<i>)!)', closeWith:'(!(</em>|!|</i>)!)'  },
      {name:'Stroke through', key:'S', openWith:'<del>', closeWith:'</del>' },
      {separator:'---------------' },
      {name:'Bulleted List', openWith:'    <li>', closeWith:'</li>', multiline:true, openBlockWith:'<ul>\n', closeBlockWith:'\n</ul>'},
      {name:'Numeric List', openWith:'    <li>', closeWith:'</li>', multiline:true, openBlockWith:'<ol>\n', closeBlockWith:'\n</ol>'},
      {separator:'---------------' },
      {name:'Picture', key:'P', replaceWith:'<img src="[![Source:!:http://]!]" alt="[![Alternative text]!]" />' },
      {name:'Link', key:'L', openWith:'<a href="[![Link:!:http://]!]"(!( title="[![Title]!]")!)>', closeWith:'</a>', placeHolder:'Your text to link...' },
      {separator:'---------------' },
      {name:'Clean', className:'clean', replaceWith:function(markitup) { return markitup.selection.replace(/<(.*?)>/g, "") } }
    ]
  }
  if ($('#markItUp').length) {
    $('#markItUp').markItUp(cleanSettings);
  }

  // datepickers init
  if ($('.input-date').length) {
    $('.input-date').datepicker({
      inline: true
    });
  }

  // fancy tables
  if ($('#dataTable').length) {
    $.tablesorter.defaults.widgets = ['zebra'];
    $('#dataTable').tablesorter();   
  }
  
  // Hover effect
  if (!jQuery.browser.msie) {
    $("ul.image-list li img, .image-list .actions a, #to-top, .table-icon").hover(function () {
      $(this).stop().animate({ opacity: 0.65 }, 200) }, 
      function () { $(this).stop().animate({ opacity: 1}, 200) });
  }
  
  // image actions menu
  $('ul.image-list li').hover(
    function() { $(this).find('div.actions').css('display', 'none').fadeIn('fast').css('display', 'block'); },
    function() { $(this).find('div.actions').fadeOut(100); }
  );
    
  // image delete confirmation
  $('.image-list .img-delete').click(function() {
    if (confirm("Are you sure you want to delete this image?")) {
      return true;
    } else {
      return false;
    }
  });
 
}); // onload