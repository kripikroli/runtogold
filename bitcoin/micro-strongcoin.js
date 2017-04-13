$(function() {
  $('.micro-buy').popover({ placement: 'above', trigger: 'manual', html: true });
  $('.micro-buy').click(function() {
    var the_button = $(this);
    var item = the_button.attr('data-item');
    if(getCookie(item) == null)
    {
      $.get('strongcoin-payments.php?cmd=getaddress&item=' + item, 
        function(addr) {   
          setCookie(item, addr);
          showPopup(addr, item, the_button);
        });
      }
      else
      {
        showPopup(getCookie(item), item, the_button);
      }
      return false;
  });
});

function showPopup(addr, item, the_button)
{
  the_button.attr('data-content', 
    '<table style="padding: 0; margin: 0"><tr><td>' + 
    '<strong style="" id="title-' + item + '">Waiting For Payment.</strong><br />' +
    '<div style="margin-top: 10px" id="content-' + item + '">' + addr.substr(0, 15) + '<br/>' +
    addr.substr(15) + '</div>' +
    '</td><td style="margin: 0; padding: 0; text-align: right;"><img style="margin: 0; padding: 0"' +
    'src="https://chart.googleapis.com/chart?chs=74x74&' +
    'cht=qr&chl=' + addr + '&choe=UTF-8" >' +
    '</td></tr></table>'
  );
  the_button.attr('data-original-title', 
    '<span id="main-title-' + item + '">Send payment to:</span>');
  the_button.popover('show');
  the_button.attr("disabled", true);
  setTimeout(function() {
    pollForPayment(item, addr);
  }, 1000);
}

function pollForPayment(item, addr)
{
  var text = $('#title-' + item).text();
  if(text == "Waiting For Payment...")
    text = "Waiting For Payment.";
  else if(text == "Waiting For Payment..")
    text = "Waiting For Payment...";
  else
  {
    text = "Waiting For Payment..";
    // Might as well see if we got paid.
    $.get('strongcoin-payments.php?cmd=poll&addr=' + addr, 
      function(data) {   
        if(data == '1')
        {
          $('#content-' + item).html(
            "<a href='strongcoin-payments.php?cmd=file&addr=" + addr
            + "'>Download Purchase</a>");
          $('#title-' + item).text("Payment Received.");
          $('#main-title-' + item).text("Thank You.");
        }
      });
  }

  if($('#content-' + item).html().indexOf("<a href") == -1)
  {
    $('#title-' + item).text(text);
    setTimeout(function() {
      pollForPayment(item, addr, false);
    }, 1000);
  }
}

function setCookie(name,value,days) 
{     
  if (days) {         
    var date = new Date();         
    date.setTime(date.getTime()+(days*24*60*60*1000));         
    var expires = "; expires="+date.toGMTString();     
  }     
  else var expires = "";     
  document.cookie = name+"="+value+expires+"; path=/"; 
}  

function getCookie(name) 
{     
  var nameEQ = name + "=";     
  var ca = document.cookie.split(';');     
  for(var i=0;i < ca.length;i++) 
  {         
    var c = ca[i];         
    while (c.charAt(0)==' ') 
      c = c.substring(1,c.length);         
    if (c.indexOf(nameEQ) == 0) 
      return c.substring(nameEQ.length,c.length);     
  }     return null; 
}
