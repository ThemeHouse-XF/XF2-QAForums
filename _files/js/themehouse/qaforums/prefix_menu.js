!function($, window, document, _undefined)
{
	"use strict";

	XF.PrefixMenu = XF.extend(XF.PrefixMenu, {

		__backup: {
			'selectPrefix': '_selectPrefix'
		},

		selectPrefix: function(id)
		{
			var oldId = parseInt(this.$select.val(), 10);

			this._selectPrefix(id);

			id = parseInt(id, 10);

			if (id === oldId) {
				return;
			}

			var $form = this.$select.closest('form'),
				$checkbox = $form.find('input[name=th_is_qa_qaforum]');

			if (!$checkbox.length) {
				return;
			}

			var defaultStatus = parseInt($checkbox.data('defaultStatus')) ? true : false,
				defaultPrefix = parseInt($checkbox.data('defaultPrefix')),
				questionPrefix = parseInt($checkbox.data('questionPrefix')),
				answeredPrefix = parseInt($checkbox.data('answeredPrefix'));

			if ((questionPrefix && oldId === questionPrefix)
				|| (answeredPrefix && oldId === answeredPrefix)
				|| oldId === defaultPrefix) {
				if (id === defaultPrefix) {
					$checkbox.prop('checked', defaultStatus);
				} else {
					$checkbox.prop('checked', false);
				}
			}

			if ((questionPrefix && id === questionPrefix)
				|| (answeredPrefix && id === answeredPrefix)) {
				$checkbox.prop('checked', true);
			} else if (id === defaultPrefix) {
				$checkbox.prop('checked', defaultStatus);
			}
        }
	});

	XF.Element.register('prefix-menu', 'XF.PrefixMenu');
}
(jQuery, window, document);
