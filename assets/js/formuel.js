jQuery(function ($) {
    function toggleConditionalField() {
        var inquiryType = $('#formuel-inquiry-type').val();
        var $conditionalField = $('.formuel-field--conditional');

        if (inquiryType === $conditionalField.data('condition')) {
            $conditionalField.removeClass('formuel-field--hidden');
        } else {
            $conditionalField.addClass('formuel-field--hidden');
            $conditionalField.find('textarea').val('');
        }
    }

    $('#formuel-inquiry-type').on('change', toggleConditionalField);
    toggleConditionalField();

    $('.formuel-form').on('submit', function () {
        $('.formuel-button').prop('disabled', true);
    });
});
