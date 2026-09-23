<?php
/**
 * Shared Add Link modal body (admin list + editor shells).
 *
 * @package Modal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_editor_shell = ! empty( $is_from_editor );
$intro_text      = $is_editor_shell
	? 'Enter the destination URL your Lasso link will redirect to.'
	: 'Enter the affiliate link you would like to track.';
?>

<div id="add_new_form">
	<h2>Add A New Link</h2>
	<p><?php echo esc_html( $intro_text ); ?></p>

	<div class="lasso-url-add-tabs" data-lasso-url-add-tabs>
		<ul class="nav nav-tabs lasso-url-add-tab-nav justify-content-center mb-4" role="tablist">
			<li class="nav-item" role="presentation">
				<button
					type="button"
					class="nav-link active lasso-url-add-tab-trigger"
					data-lasso-tab="url"
					role="tab"
					aria-selected="true"
				>
					URL
				</button>
			</li>
			<li class="nav-item" role="presentation">
				<button
					type="button"
					class="nav-link lasso-url-add-tab-trigger"
					data-lasso-tab="marketplace"
					role="tab"
					aria-selected="false"
				>
					Marketplace
				</button>
			</li>
		</ul>

		<div class="lasso-url-add-tab-panel" data-lasso-tab-panel="url" role="tabpanel">
			<div class="form-group mb-4">
				<input type="text" name="url" id="add-new-url-box" class="form-control" placeholder="https://www.example.com/affiliate-id">
				<input type="hidden" name="post_type" value="lasso-urls">
				<input type="hidden" name="page" value="url-details">
				<p class="js-error text-danger my-3"></p>
			</div>
			<div class="text-center">
				<?php if ( $is_editor_shell ) : ?>
					<span id="btn-lasso-add-new-link" class="btn btn-lasso-add-link" data-disabled="0">
						<i class="far fa-plus-circle"></i> Add Link
					</span>
				<?php else : ?>
					<button id="btn-lasso-add-new-link" class="btn" type="button">
						<i class="far fa-plus-circle"></i> Add Link
					</button>
				<?php endif; ?>
			</div>
		</div>

		<div class="lasso-url-add-tab-panel d-none" data-lasso-tab-panel="marketplace" role="tabpanel">
			<div class="lasso-url-add-marketplace-mount py-2" data-lasso-marketplace-root>
				<div class="form-group mb-3">
					<label class="sr-only" for="lasso-marketplace-search">Search Marketplace</label>
					<div class="input-group">
						<input
							type="search"
							id="lasso-marketplace-search"
							class="form-control"
							placeholder="Search Marketplace products"
							autocomplete="off"
						>
						<div class="input-group-append">
							<button type="button" class="btn btn-outline-secondary" id="lasso-marketplace-search-btn">
								Search
							</button>
						</div>
					</div>
				</div>
				<p class="js-marketplace-error text-danger d-none mb-2"></p>
				<div class="lasso-marketplace-results list-group mb-3" id="lasso-marketplace-results" aria-live="polite"></div>
				<p class="text-muted small d-none" id="lasso-marketplace-empty">No products found. Try another search.</p>
				<input type="hidden" id="lasso-marketplace-selected-asin" value="">
				<input type="hidden" id="lasso-marketplace-selected-url" value="">
				<div class="text-center">
					<?php if ( $is_editor_shell ) : ?>
						<span id="btn-lasso-add-marketplace-link" class="btn btn-lasso-add-link d-none" data-disabled="0">
							<i class="far fa-plus-circle"></i> Add Link
						</span>
					<?php else : ?>
						<button id="btn-lasso-add-marketplace-link" class="btn d-none" type="button">
							<i class="far fa-plus-circle"></i> Add Link
						</button>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
