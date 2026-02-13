/* global jQuery, autoblogAI */
(function ($) {
	'use strict';

	var pollTimer = null;
	var isPolling = false;

	/**
	 * Submit the generator form.
	 */
	function handleFormSubmit(e) {
		e.preventDefault();

		var $form = $(this);
		var $btn = $('#autoblog-ai-submit-btn');
		var $spinner = $('#autoblog-ai-spinner');
		var titlesRaw = $('#autoblog-ai-titles').val().trim();

		if (!titlesRaw) {
			alert(autoblogAI.i18n.noTitles);
			return;
		}

		var titles = titlesRaw.split('\n').filter(function (t) {
			return t.trim().length > 0;
		}).map(function (t) {
			return t.trim();
		});

		if (titles.length === 0) {
			alert(autoblogAI.i18n.noTitles);
			return;
		}

		var payload = {
			titles: titles,
			word_count: parseInt($('#autoblog-ai-word-count').val(), 10) || 1500,
			article_type: $('#autoblog-ai-article-type').val(),
			tone: $('#autoblog-ai-tone').val(),
			pov: $('#autoblog-ai-pov').val(),
			faq_count: parseInt($('#autoblog-ai-faq-count').val(), 10) || 0,
			takeaway_count: parseInt($('#autoblog-ai-takeaway-count').val(), 10) || 0,
			post_status: $('#autoblog-ai-post-status').val(),
			category: $('#autoblog-ai-category').val(),
			tags: $('#autoblog-ai-tags').val(),
			image_provider: $('#autoblog-ai-image-provider').val(),
			image_style: $('#autoblog-ai-image-style').val(),
			internal_linking: $('#autoblog-ai-internal-linking').is(':checked') ? 1 : 0,
		};

		$btn.prop('disabled', true);
		$spinner.addClass('is-active');

		$.ajax({
			url: autoblogAI.restUrl + 'generate',
			method: 'POST',
			headers: { 'X-WP-Nonce': autoblogAI.nonce },
			contentType: 'application/json',
			data: JSON.stringify(payload),
		})
			.done(function () {
				$('#autoblog-ai-titles').val('');
				refreshQueue();
				startPolling();
			})
			.fail(function () {
				alert(autoblogAI.i18n.submitError);
			})
			.always(function () {
				$btn.prop('disabled', false);
				$spinner.removeClass('is-active');
			});
	}

	/**
	 * Fetch current queue items and render the table.
	 */
	function refreshQueue() {
		$.ajax({
			url: autoblogAI.restUrl + 'queue',
			method: 'GET',
			headers: { 'X-WP-Nonce': autoblogAI.nonce },
		}).done(function (items) {
			renderQueue(items);

			// Stop polling if nothing is active.
			var hasActive = items.some(function (item) {
				return item.status === 'queued' || item.status === 'generating';
			});

			if (!hasActive) {
				stopPolling();
			}
		});
	}

	/**
	 * Render queue items into the DOM.
	 */
	function renderQueue(items) {
		var $container = $('#autoblog-ai-queue');

		if (!items || items.length === 0) {
			$container.html(
				'<div class="autoblog-ai-queue-empty">' +
				'No articles in the queue. Submit titles to get started.' +
				'</div>'
			);
			return;
		}

		var html =
			'<table class="autoblog-ai-queue-table">' +
			'<thead><tr>' +
			'<th>Title</th><th>Status</th><th>Actions</th>' +
			'</tr></thead><tbody>';

		items.forEach(function (item) {
			html += '<tr>';
			html += '<td class="queue-title">' + escHtml(item.title) + '</td>';
			html += '<td>' + statusBadge(item.status) + errorLine(item) + '</td>';
			html += '<td class="autoblog-ai-queue-actions">' + actionLinks(item) + '</td>';
			html += '</tr>';
		});

		html += '</tbody></table>';
		$container.html(html);
	}

	/**
	 * Build a status badge.
	 */
	function statusBadge(status) {
		var label = autoblogAI.i18n[status] || status;
		return '<span class="autoblog-ai-status autoblog-ai-status--' + escAttr(status) + '">' + escHtml(label) + '</span>';
	}

	/**
	 * Build error line if present.
	 */
	function errorLine(item) {
		if (item.status !== 'failed' || !item.error_message) {
			return '';
		}
		return '<div class="autoblog-ai-error-message">' + escHtml(item.error_message) + '</div>';
	}

	/**
	 * Build action links for a queue item.
	 */
	function actionLinks(item) {
		var links = [];

		if (item.status === 'complete' && item.post_id) {
			links.push(
				'<a href="' + escAttr(item.edit_url) + '" target="_blank">' + autoblogAI.i18n.view + '</a>'
			);
		}

		if (item.status === 'failed' && item.retry_count < 3) {
			links.push(
				'<button type="button" class="retry-action" data-id="' + item.id + '">' + autoblogAI.i18n.retry + '</button>'
			);
		}

		links.push(
			'<button type="button" class="delete-action" data-id="' + item.id + '">' + autoblogAI.i18n.delete + '</button>'
		);

		return links.join('<span class="separator">|</span>');
	}

	/**
	 * Handle retry click.
	 */
	function handleRetry(e) {
		var id = $(e.target).data('id');
		$.ajax({
			url: autoblogAI.restUrl + 'queue/' + id + '/retry',
			method: 'POST',
			headers: { 'X-WP-Nonce': autoblogAI.nonce },
		}).done(function () {
			refreshQueue();
			startPolling();
		});
	}

	/**
	 * Handle delete click.
	 */
	function handleDelete(e) {
		var id = $(e.target).data('id');
		if (!confirm(autoblogAI.i18n.confirmDel)) {
			return;
		}
		$.ajax({
			url: autoblogAI.restUrl + 'queue/' + id,
			method: 'DELETE',
			headers: { 'X-WP-Nonce': autoblogAI.nonce },
		}).done(function () {
			refreshQueue();
		});
	}

	/**
	 * Start polling the queue every 5 seconds.
	 */
	function startPolling() {
		if (isPolling) {
			return;
		}
		isPolling = true;
		pollTimer = setInterval(refreshQueue, 5000);
	}

	/**
	 * Stop polling.
	 */
	function stopPolling() {
		if (pollTimer) {
			clearInterval(pollTimer);
			pollTimer = null;
		}
		isPolling = false;
	}

	/**
	 * Minimal HTML escaping.
	 */
	function escHtml(str) {
		var div = document.createElement('div');
		div.appendChild(document.createTextNode(str || ''));
		return div.innerHTML;
	}

	function escAttr(str) {
		return escHtml(str).replace(/"/g, '&quot;');
	}

	/**
	 * Initialize on DOM ready.
	 */
	$(function () {
		$('#autoblog-ai-generator-form').on('submit', handleFormSubmit);
		$('#autoblog-ai-queue').on('click', '.retry-action', handleRetry);
		$('#autoblog-ai-queue').on('click', '.delete-action', handleDelete);

		// Initial queue load.
		refreshQueue();
	});
})(jQuery);
