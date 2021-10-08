/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined) {
	"use strict";

	XF.THQAForumsVote = XF.Click.newHandler({
		eventNameSpace: 'THQAForumsVote',
		options: {
			qaControls: null
		},

		processing: false,

		init: function() {
		},

		click: function(e) {
			e.preventDefault();

			if (this.processing) {
				return;
			}
			this.processing = true;

			var href = this.$target.attr('href'),
				self = this;

			XF.ajax('POST', href, {}, $.proxy(this, 'handleAjax'), {skipDefaultSuccess: true})
				.always(function() {
					setTimeout(function() {
						self.processing = false;
					}, 250);
				});
		},

		handleAjax: function(data)
		{
			if (data.errors || data.exception) {
				return;
			}

			if (data.redirect) {
				XF.redirect(data.redirect);
			}

			var $target = this.$target;

			if (data.addClass) {
				$target.addClass(data.addClass);
			}
			if (data.removeClass) {
				$target.removeClass(data.removeClass);
			}
			if (data.text) {
				var $label = $target.find('.label');
				if (!$label.length) {
					$label = $target;
				}
				$label.text(data.text);
			}

			var $qaControls = this.options.qaControls ? XF.findRelativeIf(this.options.qaControls, $target) : $([]);

			if (typeof data.html !== 'undefined' && $qaControls.length) {
				if (data.html.content) {
					XF.setupHtmlInsert(data.html, function($html, container) {
						$qaControls.html($html);
					});
				}
			}
		}
	});

	XF.Click.register('th_qaForums_vote', 'XF.THQAForumsVote');
}(jQuery, window, document);