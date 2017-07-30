$(function() {
  $(document).on('click', '.btn.disabled', function() {
    return false;
  }).on('click', '.btn:not(.btn-persistent)', function() {
    $(this).addClass('disabled');
  }).on('click', 'input[type=submit]:not(.btn-persistent)', function() {
    $(this).prop('disabled', true);
  });
});
