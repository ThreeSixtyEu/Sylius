/**
 * Created by dalexa on 15.12.15.
 */

$(document).ready(function() {
	$("body").on('change, click', '#shippings input[type="radio"]', function() {
		toggleAddressing($(this));
	}).on('change', '#sylius_checkout_shipping_country_specific_country, #sylius_checkout_addressing_shippingAddress_country', function() {
		var $form = $(this).parents('form');
		var $input = $form.prepend($("<input/>").attr('type', 'hidden').attr('name', 'doNotForward').val(true));
		$form.prepend($("<div />").addClass('ajax-loader'));
		setTimeout(function() {
			$.ajax({
				url: $form.attr('action'),
				method: 'POST',
				data: $form.serialize(),
				success: function(data) {
					var $content = $(data).find("#payment_shipping_form");
					if ($content.length) {
						$form.html($content.html());
						checkFirstShipping();
					} else {
						$form.find('.ajax-loader').remove();
						$input.remove();
					}
				},
				error: function() {
					$form.find('.ajax-loader').remove();
					$input.remove();
				}
			});
		}, 50);
	});

	function checkFirstShipping() {
		toggleAddressing($("#shippings input[type='radio']:checked"));
	}

	function toggleAddressing($selectedShipping) {
		var $addressing = $("#addressing");
		var requiredIds = $("#shippings").data('shippingsRequiresAddressing');
		var id = parseInt($selectedShipping.val(), 10);

		if (requiredIds.ids.indexOf(id) !== -1) {
			$addressing.removeClass('js-hidden');
		} else {
			$addressing.addClass('js-hidden');
		}
	}

	checkFirstShipping();
});