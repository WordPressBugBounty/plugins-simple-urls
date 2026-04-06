<?php
/**
 * Row
 *
 * @package Row
 */

// phpcs:ignore
?>

<!-- SINGLE LINK -->
<div class="p-4 hover-gray">
	<div class="row align-items-center">
		<div class="col-4 import-title" title="${ element.post_title_attr || '' }">
			<strong class="import-title-text">${ element.post_title }</strong>
		</div>

		<div class="col import-target">
			${ element.shortcode != '' ? 
				`
					<code class="import-shortcode-lite import-target-clamp" title="${ element.shortcode_attr || '' }">${ element.shortcode }</code>
				` 
				: 
				`
				<a href="${ element.import_permalink }" title="${ element.import_permalink_attr || element.import_permalink }" target="_blank" class="purple underline import-target-clamp">
					${ element.import_permalink }
				</a>
				` 
			}
		</div>

		<div class="col-1">
			${ element.import_source }
		</div>

		<div class="col-1 text-center">
			${ 
			element.check_status == `checked` && element.locations > 0 
				? `<button class="btn btn-sm black white-bg black-border show-locations"
						data-import-id="${ element.id }"
						data-locations="${ element.locations }">
						See It
					</button>`
				:``
			}
		</div>

		<div class="col-1 text-center">
			${ element.check_status == `checked` ? 
				`<button class="btn btn-sm red-bg js-toggle-revert" 
					data-import-id="${ element.id }" 
					data-post-title="${ element.post_title }" 
					data-import-permalink="${ element.import_permalink }"
					data-post-type="${ element.post_type }"
					data-import-source="${ element.import_source }">
					Revert
				</button>
				<i class="far fa-check-circle fa-2x gray d-none"></i>
				`
				:
				`<button class="btn btn-sm js-toggle-import" 
					data-import-id="${ element.id }" 
					data-post-title="${ element.post_title }" 
					data-import-permalink="${ element.import_permalink }"
					data-post-type="${ element.post_type }"
					data-import-source="${ element.import_source }">
					Import
				</button>
				<i class="far fa-check-circle fa-2x green d-none"></i>
				`
			}
		</div>
	</div>
</div>
