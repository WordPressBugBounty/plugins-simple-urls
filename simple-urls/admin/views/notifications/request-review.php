<?php
/**
 * Review ask: dismissible corner card (#1077). Gate: Helper::show_request_review().
 *
 * @package Lasso Lite
 */

use LassoLite\Classes\Enum;
use LassoLite\Classes\Helper;
use LassoLite\Classes\SURL;

// Link count only: Lite has no reliable click data to show here.
$lasso_review_links = SURL::total();
?>

<div class="ls-review-card" role="dialog" aria-labelledby="ls-review-card-title">
	<button type="button" class="ls-review-card-close review-snooze" aria-label="Close">&times;</button>

	<div class="ls-review-card-head">
		<div class="ls-review-card-brand">
			<img src="<?php echo esc_url( SIMPLE_URLS_URL . '/admin/assets/images/lasso-icon.png' ); ?>" alt="Lasso">
			<div>
				<h3 id="ls-review-card-title">Nice work so far <span class="wp-exclude-emoji" aria-hidden="true">&#127881;</span></h3>
				<p>You've cloaked <strong><?php echo esc_html( number_format_i18n( $lasso_review_links ) ); ?></strong> links with Lasso Lite so far.</p>
			</div>
		</div>
	</div>

	<!-- Step 1: sentiment -->
	<div class="ls-review-card-step" data-step="ask">
		<p>Hi there! We noticed you're getting value from Lasso Lite. How do you like it so far?</p>
		<div class="ls-review-card-btns">
			<button type="button" class="ls-review-card-btn primary" data-go="review">It's awesome!</button>
			<button type="button" class="ls-review-card-btn ghost" data-go="support">I'm having some trouble</button>
		</div>
		<div class="ls-review-card-foot">
			<a href="#" class="review-snooze">I'm testing things out</a>
			<a href="#" class="review-dismiss">Don't ask again</a>
		</div>
	</div>

	<!-- Step 2a: happy → honest WordPress.org review -->
	<div class="ls-review-card-step" data-step="review" hidden>
		<div class="ls-review-card-title">
			<div class="ls-review-card-badge wp-exclude-emoji" aria-hidden="true">&#9997;&#65039;</div>
			<h4>That's what we like to hear. Would you leave an honest review on WordPress.org?</h4>
		</div>
		<p>It takes about 2 minutes and helps other bloggers decide whether Lasso fits them. Thanks for your support!</p>
		<a class="ls-review-card-btn primary block review-done" href="<?php echo esc_url( Enum::LASSO_REVIEW_URL ); ?>" target="_blank" rel="noopener noreferrer">Leave a review on WordPress.org &#8599;</a>
		<div class="ls-review-card-foot">
			<a href="#" class="review-snooze">Maybe later</a>
			<a href="#" class="review-done">I already did</a>
		</div>
	</div>

	<!-- Step 2b: not happy → support -->
	<div class="ls-review-card-step" data-step="support" hidden>
		<div class="ls-review-card-title">
			<div class="ls-review-card-badge is-support wp-exclude-emoji" aria-hidden="true">&#128172;</div>
			<h4>We're sorry to hear that.</h4>
		</div>
		<p>Please contact Lasso's Support Team, and we'll help however we can!</p>
		<a href="#" class="ls-review-card-btn primary block show-intercom">Contact Lasso's Support Team</a>
		<div class="ls-review-card-foot">
			<a href="#" class="review-snooze">Not now</a>
		</div>
	</div>

	<!-- Step 3: thanks (after a review click or "I already did"), then the card closes itself -->
	<div class="ls-review-card-step ls-review-card-done" data-step="done" hidden>
		<div class="ls-review-card-check" aria-hidden="true">&#10003;</div>
		<h4>Thank you!</h4>
		<p>Your support means a lot to the Lasso team.</p>
	</div>
</div>

<script>
	jQuery(document).ready(function() {
		var $card = jQuery('.ls-review-card');

		function show_step(step) {
			$card.find('.ls-review-card-step').attr('hidden', true)
				.filter('[data-step="' + step + '"]').removeAttr('hidden');
		}

		jQuery(document)
			.on('click', '.ls-review-card [data-go]', function() {
				show_step(jQuery(this).data('go'));
			})
			.on('click', '.ls-review-card .show-intercom', click_review_support)
			.on('click', '.ls-review-card .review-snooze', function(e) {
				e.preventDefault();
				review_action('lasso_lite_review_snooze', 0);
			})
			.on('click', '.ls-review-card .review-dismiss', function(e) {
				e.preventDefault();
				review_action('lasso_lite_disable_review', 0);
			})
			// Opening the review form or "I already did": thank the user, snooze to the next milestone, close.
			.on('click', '.ls-review-card .review-done', function(e) {
				if ( '#' === jQuery(this).attr('href') ) {
					e.preventDefault();
				}
				show_step('done');
				review_action('lasso_lite_review_snooze', 2500);
			})
		;

		function click_review_support(e) {
			e.preventDefault();
			jQuery('#support-launcher').trigger('click');
			if ( typeof Intercom === "function" ) {
				Intercom('show');
			} else {
				jQuery('.fake-intercom-bubble-chat-app-launcher-icon').trigger('click');
			}
		}

		function review_action(action, close_delay) {
			jQuery.post('<?php echo esc_url( Helper::get_ajax_url() ); ?>', {
				action: action,
				nonce: lassoLiteOptionsData.optionsNonce,
			});
			setTimeout(function () {
				$card.addClass('is-leaving');
				setTimeout(function () { $card.hide(); }, 300);
			}, close_delay);
		}
	});
</script>
