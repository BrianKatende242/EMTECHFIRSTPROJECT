(function($) {
  'use strict';

  if ($(".js-example-basic-single").length) {
    $(".js-example-basic-single").select2({
      width: '100%', // Important for full width
      dropdownParent: $('#bookDoctorModal'), // Important for modals
      placeholder: 'Select an option',
      allowClear: true
    });
  }
  if ($(".js-example-basic-multiple").length) {
    $(".js-example-basic-multiple").select2({
      width: '100%'
    });
  }
})(jQuery);