jQuery(document).ready(function($) {
	$('.custombtn.get_updates').on('click', function(e) {
		e.preventDefault();

		var button = $(this);
		buttonTxt = button.text();
		button.text('checking...');

		auto_check('get_updates', button, buttonTxt);
	});

	$('.custombtn.single_update').on('click', function(e) {
		e.preventDefault();

		var checks = document.getElementsByClassName('checkbox');
		var updates = [];
		var counter = 0;
		var button = $(this);

		$.each(checks, function() {
			if (this.checked) {
				updates[counter] = this.name;
				counter++;
			}
		});

		buttonTxt = button.text();
		if (updates.length != 0) {
			button.text('updating...');
			auto_check('bulk_update', button, buttonTxt, updates);
		}
	});

	/* functions */

	function auto_check(action, button, buttonTxt, additional = []) {
		var data = {
			action: action,
			noonce: 'pluginupdater',
			url: $('.input.url input').data('url'),
			additional: additional
		};

		console.log('TEST');

		$('.loader-wrap').show();
		var request = $.post(zip_theme.ajaxurl, data, function(response) {
			check_response(response, action);
		});

		// Callback handler that will be called on success
		request.done(function(response, textStatus, jqXHR) {
			$('.loader-wrap').hide();
			button.text(buttonTxt);
		});

		// Callback handler that will be called on failure
		request.fail(function(jqXHR, textStatus, errorThrown) {
			// Log the error to the console
			console.error('The following error occurred: ' + textStatus, errorThrown);
			$('.loader-wrap').hide();
		});
	}

	function check_response(response, action) {
		$('.plugin-list tbody tr.plugin').remove();
		if (action == 'get_updates') {
			if (response.data.plugins.length == 0) {
				$('.plugin-list').html('<tr class="tr-row plugin"><td>Plugins are up to date &#x1F43C; </td></tr>');
			} else {
				template(response);
			}
		} else {
			if (response.data.length == 0) {
				$('.plugin-list tbody').html(
					'<tr class="tr-row plugin"><td>The selected plugins are up to date &#x1F43C; </td></tr>'
				);
			}
		}
	}

	function template(response) {
		$('.plugin-list').html(
			'<tr class="tr-row"><th class="theading"></th><th class="theading">Name</th><th class="theading">Current Version</th><th class="theading">New Version</th><th class="theading">WP Version tested</th></tr>'
		);
		$.each(response.data.plugins, function(key, obj) {
			$('.plugin-list tbody').append(
				'<tr class="tr-row plugin"><td>' +
					'<input class="checkbox" type="checkbox" name="' +
					obj.update.plugin +
					'">' +
					'</td><td>' +
					obj.Name +
					'</td><td>' +
					obj.Version +
					'</td><td>' +
					obj.update.new_version +
					'</td><td>' +
					obj.update.tested +
					'</td></tr>'
			);
		});
	}
});
