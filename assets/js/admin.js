/* global jQuery, autoblogAI */
(function ($) {
	'use strict';

	var MAX_BATCH_SIZE = 50;
	var pollTimer = null;
	var isPolling = false;

	/**
	 * Submit the generator form.
	 */
	function handleFormSubmit(e) {
		e.preventDefault();

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

		if (titles.length > MAX_BATCH_SIZE) {
			alert(autoblogAI.i18n.tooManyTitles);
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
			.done(function (response) {
				$('#autoblog-ai-titles').val('');
				showNotice(response.message || autoblogAI.i18n.queued);
				refreshQueue();
				startPolling();

				// Scroll to the queue on mobile where columns stack.
				var $queue = $('#autoblog-ai-queue');
				if ($queue.length && $(window).width() <= 960) {
					$('html, body').animate({ scrollTop: $queue.offset().top - 40 }, 300);
				}
			})
			.fail(function (xhr) {
				var msg = autoblogAI.i18n.submitError;
				if (xhr.responseJSON && xhr.responseJSON.message) {
					msg = xhr.responseJSON.message;
				}
				alert(msg);
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
		})
			.done(function (items) {
				renderQueue(items);

				// Stop polling if nothing is active.
				var hasActive = items.some(function (item) {
					return item.status === 'queued' || item.status === 'generating';
				});

				if (!hasActive) {
					stopPolling();
				}
			})
			.fail(function (xhr) {
				// If nonce expired (403), stop polling to avoid spamming.
				if (xhr.status === 403) {
					stopPolling();
					showNotice(autoblogAI.i18n.submitError);
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
				escHtml(autoblogAI.i18n.emptyQueue) +
				'</div>'
			);
			return;
		}

		// Check if there are finished items for the clear button.
		var hasFinished = items.some(function (item) {
			return item.status === 'complete' || item.status === 'failed';
		});

		var html = '';

		if (hasFinished) {
			html += '<div class="autoblog-ai-queue-toolbar">' +
				'<button type="button" class="button button-link-delete clear-queue-action">' +
				escHtml(autoblogAI.i18n.clearQueue) +
				'</button></div>';
		}

		html +=
			'<table class="autoblog-ai-queue-table">' +
			'<thead><tr>' +
			'<th>' + escHtml(autoblogAI.i18n.colTitle) + '</th>' +
			'<th>' + escHtml(autoblogAI.i18n.colStatus) + '</th>' +
			'<th>' + escHtml(autoblogAI.i18n.colActions) + '</th>' +
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
		var id = $(e.target).closest('.retry-action').data('id');
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
		var $btn = $(e.target).closest('.delete-action');
		var id = $btn.data('id');
		if (!id || !confirm(autoblogAI.i18n.confirmDel)) {
			return;
		}
		$btn.prop('disabled', true);
		$.ajax({
			url: autoblogAI.restUrl + 'queue/' + id,
			method: 'DELETE',
			headers: { 'X-WP-Nonce': autoblogAI.nonce },
		}).done(function () {
			refreshQueue();
		}).fail(function () {
			$btn.prop('disabled', false);
		});
	}

	/**
	 * Handle clear queue click.
	 */
	function handleClearQueue(e) {
		if (!confirm(autoblogAI.i18n.confirmClear)) {
			return;
		}
		var $btn = $(e.target);
		$btn.prop('disabled', true);
		$.ajax({
			url: autoblogAI.restUrl + 'queue/clear',
			method: 'POST',
			headers: { 'X-WP-Nonce': autoblogAI.nonce },
		}).done(function (response) {
			showNotice(response.message);
			refreshQueue();
		}).fail(function () {
			$btn.prop('disabled', false);
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
	 * Show a temporary success notice above the queue.
	 */
	function showNotice(message) {
		var $queue = $('.generator-queue-column .autoblog-ai-card');
		$queue.find('.autoblog-ai-inline-notice').remove();
		var $notice = $('<div class="autoblog-ai-inline-notice">' + escHtml(message) + '</div>');
		$queue.find('h2').after($notice);
		setTimeout(function () {
			$notice.fadeOut(300, function () { $notice.remove(); });
		}, 4000);
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
		$('#autoblog-ai-queue').on('click', '.clear-queue-action', handleClearQueue);

		// Initial queue load.
		refreshQueue();
	});
})(jQuery);
