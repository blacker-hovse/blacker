$(function() {
  $(document).on('click', '.btn.disabled', function() {
    return false;
  }).on('click', '.btn:not(.btn-persistent)', function(e) {
    setTimeout(function() {
      $(e.target).addClass('disabled');
    }, 10);
  }).on('click', 'input[type=submit]:not(.btn-persistent)', function(e) {
    setTimeout(function() {
      $(this).prop('disabled', true);
    }, 10);
  });
});
