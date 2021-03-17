
import { jQuery as $ } from '../src/jquery-3.5.1-min.js';
import '../src/jQuery-UI/jq-ui-core-min.js';
import '../src/jQuery-UI/effects/jq-ui-effects-min.js';
import '../src/jQuery-UI/modules/jq-ui-dialog-min.js';

export default {

	show: function() {


		var dialogShown = localStorage.getItem("dialog-shown");


		// If dialog last shown > 24hrs ago, show again
		if (dialogShown) {
			var dialogShownDate = new Date(dialogShown);
			if (Date.now() - dialogShownDate.getTime() > 86400000) dialogShown = false;
		}

		if (params.show_dialog && !dialogShown) {

			setTimeout(function() {
				$("#dialog-wrapper").dialog({
					autoOpen: true,
					modal: true,
					draggable: false,
					position: { my: "center", at: "center", of: window },
					resizable: false,
					classes: { "ui-dialog": "dialog-create-account" },
					minWidth: 650,
					create: function(event, ui) {
						$(event.target).parent().css('position', 'fixed');
					},
					open: function(event, ui) {   //added here
						localStorage.setItem("dialog-shown", Date.now());
						console.log('Set');
						$('.ui-widget-overlay').on('click', function() {
							$("#dialog-wrapper").dialog('close');
						});
					},
				});


			}, params.promo_popup_options.time_delay_msecs);
		}



	}



}
