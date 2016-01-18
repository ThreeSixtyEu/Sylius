/**
 * Created by dalexa on 15.12.15.
 */

$(document).ready(function() {
	$("#shippings").on('change, click', 'input[type="radio"]', function() {
		var $addressing = $("#addressing");
		var requiredIds = $("#shippings").data('shippingsRequiresAddressing');
		var id = parseInt($(this).val(), 10);

		if (requiredIds.ids.indexOf(id) !== -1) {
			$addressing.removeClass('js-hidden');
		} else {
			$addressing.addClass('js-hidden');
		}
	}).on('change', '#sylius_checkout_shipping_country_specific_country', function() {
		$(this).parents('form').submit();
	});

	if (!$("#shippings input[type='radio']:checked").length) {
		$("#shippings input[type='radio']").first().click();
	}
});
