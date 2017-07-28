$(function() {
  $('.btn.disabled').click(function() {
    return false;
  });

  $('.btn:not(.btn-persistent)').click(function() {
    $(this).addClass('disabled');
  });

  $('input[type=submit]:not(.btn-persistent)').click(function() {
    $(this).prop('disabled', true);
  });
});
