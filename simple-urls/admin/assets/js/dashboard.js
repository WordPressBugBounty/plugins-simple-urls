/**
 * Load Lasso Lite click snapshot
 * Shows today, week, and month clicks in a bottom-right notification
 */
function get_use_cache_param() {
    var useCache = lasso_lite_helper.get_url_parameter('use_cache');
    if (useCache === null || useCache === undefined || useCache === '') {
        return '1';
    }
    return useCache;
}

function load_lasso_lite_click_snapshot() {
    var useCache = get_use_cache_param();
    // Only show the first time the user visits today
    var seenDate = localStorage.getItem('lasso_lite_snapshot_seen');
    var today = new Date().toDateString();
    
    if (seenDate === today) {
        return;
    }
    localStorage.setItem('lasso_lite_snapshot_seen', today);
    
    jQuery.ajax({
        url: lassoLiteOptionsData.ajax_url,
        type: 'post',
        data: {
            action: 'lasso_lite_get_click_snapshot',
            nonce: lassoLiteOptionsData.optionsNonce,
            use_cache: useCache
        },
        success: function(res) {
            if (res && res.success && res.data) {
                var snapshotData = res.data.data ? res.data.data : res.data;
                var todayClicks = Number(snapshotData.today_clicks || 0);
                var weekClicks = Number(snapshotData.week_clicks || 0);
                var monthClicks = Number(snapshotData.month_clicks || 0);
                var clickCount = 0;
                var periodLabel = '';

                if (todayClicks > 0) {
                    clickCount = todayClicks;
                    periodLabel = 'today';
                } else if (weekClicks > 0) {
                    clickCount = weekClicks;
                    periodLabel = 'this week';
                } else if (monthClicks > 0) {
                    clickCount = monthClicks;
                    periodLabel = 'this month';
                } else {
                    return;
                }

                jQuery('#snapshot-click-count').text(clickCount);
                jQuery('#snapshot-period').text(periodLabel);
                jQuery('#lasso-lite-click-snapshot-box').fadeIn();
            }
        },
        error: function(xhr, status, error) {
            console.error('Lasso Lite Snapshot Error:', error);
        }
    });
}

/**
 * Load Lasso Lite link issues snapshot
 * Shows broken/out-of-stock/international clicks in the last 30 days
 */
function load_lasso_lite_link_issues_snapshot(onComplete) {
    var useCache = get_use_cache_param();
    var now = new Date();
    var monthKey = now.getFullYear() + '-' + (now.getMonth() + 1);
    var seenMonth = localStorage.getItem('lasso_lite_link_issues_snapshot_seen');

    if (seenMonth === monthKey) {
        if (typeof onComplete === 'function') {
            onComplete(false);
        }
        return;
    }

    jQuery.ajax({
        url: lassoLiteOptionsData.ajax_url,
        type: 'post',
        data: {
            action: 'lasso_lite_get_link_issues_snapshot',
            nonce: lassoLiteOptionsData.optionsNonce,
            use_cache: useCache
        },
        success: function(res) {
            if (res && res.success && res.data) {
                var snapshotData = res.data.data ? res.data.data : res.data;
                var brokenClicks = Number(snapshotData.broken_clicks || 0);
                var outOfStockClicks = Number(snapshotData.out_of_stock_clicks || 0);
                var internationalClicks = Number(snapshotData.international_clicks || 0);
                var showPotential = !!snapshotData.show_potential_earnings;
                var potentialEarnings = snapshotData.potential_earnings_lost;

                if (brokenClicks <= 0 && outOfStockClicks <= 0 && internationalClicks <= 0 && !showPotential) {
                    if (typeof onComplete === 'function') {
                        onComplete(false);
                    }
                    return;
                }

                var monthLabel = now.toLocaleString('default', { month: 'long' });
                jQuery('#link-issues-snapshot-title').text(monthLabel + ' Snapshot:');
                jQuery('#link-issues-broken-clicks').text(brokenClicks);
                jQuery('#link-issues-out-of-stock-clicks').text(outOfStockClicks);
                jQuery('#link-issues-international-clicks').text(internationalClicks);

                if (showPotential && potentialEarnings !== null && potentialEarnings !== undefined) {
                    jQuery('#link-issues-potential-earnings').text('$' + Number(potentialEarnings).toFixed(2));
                    jQuery('#link-issues-potential-earnings-row').show();
                } else {
                    jQuery('#link-issues-potential-earnings-row').hide();
                }

                localStorage.setItem('lasso_lite_link_issues_snapshot_seen', monthKey);
                jQuery('#lasso-lite-link-issues-snapshot-box').fadeIn();

                if (typeof onComplete === 'function') {
                    onComplete(true);
                }
            } else if (typeof onComplete === 'function') {
                onComplete(false);
            }
        },
        error: function(xhr, status, error) {
            if (typeof onComplete === 'function') {
                onComplete(false);
            }
            console.error('Lasso Lite Link Issues Snapshot Error:', error);
        }
    });
}

/**
 * Load dashboard alert totals (broken/out-of-stock/opportunities)
 */
function load_lasso_lite_links_issues_totals() {
    var useCache = get_use_cache_param();

    function numberWithCommas(x) {
        return String(x).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function renderTotals(totals) {
        var broken = Number((totals && totals.totalBrokenLinks) || 0);
        var outOfStock = Number((totals && totals.totalOutOfStock) || 0);
        var opportunities = Number((totals && totals.totalOpportunities) || 0);

        jQuery('#total-broken-links').html('<i class="far fa-unlink"></i> ' + numberWithCommas(broken));
        jQuery('#total-out-of-stock').html('<i class="far fa-box-open"></i> ' + numberWithCommas(outOfStock));
        jQuery('#total-opportunities').html('<i class="far fa-lightbulb-on"></i> ' + numberWithCommas(opportunities));

        // Always show these pills for consistency, even if counts are zero
        jQuery('#total-broken-links-li').removeClass('d-none');
        jQuery('#total-out-of-stock-li').removeClass('d-none');
        jQuery('#total-opportunities-li').removeClass('d-none');
    }

    // Default state: show pills with 0 before API returns
    renderTotals({ totalBrokenLinks: 0, totalOutOfStock: 0, totalOpportunities: 0 });

    jQuery.ajax({
        url: lassoLiteOptionsData.ajax_url,
        type: 'post',
        data: {
            action: 'lasso_lite_get_links_issues_totals',
            nonce: lassoLiteOptionsData.optionsNonce,
            use_cache: useCache
        },
        success: function(res) {
            if (!res || !res.success || !res.data) {
                return;
            }

            var totals = res.data.data ? res.data.data : res.data;
            renderTotals(totals);
        },
        error: function(xhr, status, error) {
            console.error('Lasso Lite Link Issues Totals Error:', error);
        }
    });
}

function load_dashboard( page = '', keyword = '') {
    let container = jQuery('#report-content');
    if ( keyword === '' ) {
        keyword = lasso_lite_helper.get_url_parameter('link-search-input');
    }

    if ( page === '' ) {
        page = lasso_lite_helper.get_page_from_current_url();
    }

    jQuery.ajax({
        url: lassoLiteOptionsData.ajax_url,
        type: 'post',
        data: {
            action: 'lasso_lite_dashboard_get_list',
            nonce: lassoLiteOptionsData.optionsNonce,
            page: page,
            keyword: keyword
        },
        beforeSend: function () {
            container.html(lasso_lite_helper.get_loading_image());
        }
    })
    .done(function (res) {
        if ( res.success === true ) {
            let data = res.data;
            let json_data = data.output;

            lasso_lite_helper.inject_to_template(jQuery("#report-content"), 'dashboard-list', json_data);
            lasso_lite_helper.generate_paging( jQuery('.dashboard-pagination'), data.page, data.total, function (page_number) {
                load_dashboard(page_number);
            }, data.limit_on_page);

            if ( data.total === 0 || json_data.length == 0 ) {
                container.html(lasso_lite_helper.default_empty_data);
            }
        } else {
            container.html(lasso_lite_helper.default_empty_data);
        }
    })
    .fail(function (xhr, status, error) {
        container.html(lasso_lite_helper.default_empty_data);
    });
}

jQuery(document).ready(function () {
    if (typeof window.go_to_next_step_action !== 'function') {
        window.go_to_next_step_action = function() {
            jQuery('#lasso-lite-analytics-modal').modal('hide');
        };
    }

    var $liteProModal = jQuery('#lasso-lite-pro-modal');
    if ($liteProModal.length) {
        var copyMap = {
            'broken-links': {
                title: 'Broken link alerts are currently disabled'
            },
            'out-of-stock': {
                title: 'Out-of-stock alerts are currently disabled'
            },
            'opportunities': {
                title: 'Opportunities are currently disabled'
            }
        };

        $liteProModal.on('show.bs.modal', function() {
            jQuery('body').addClass('lasso-lite-pro-modal-open');
        });
        $liteProModal.on('hidden.bs.modal', function() {
            jQuery('body').removeClass('lasso-lite-pro-modal-open');
        });

        jQuery(document).on('click', '.lasso-lite-pro-trigger', function(e) {
            e.preventDefault();
            var key = jQuery(this).data('feature');
            var copy = copyMap[key] || copyMap['opportunities'];
            jQuery('#lasso-lite-pro-modal-title').text(copy.title);
            $liteProModal.modal('show');
        });
    }

    // Load dashboard content
    load_dashboard();

    // Load dashboard alert totals
    load_lasso_lite_links_issues_totals();
    
    // Load link issues snapshot first, fallback to click snapshot
    load_lasso_lite_link_issues_snapshot(function(didShow) {
        if (!didShow) {
            load_lasso_lite_click_snapshot();
        }
    });
    
    // Handle snapshot close button
    jQuery(document).on('click', '.close-snapshot', function() {
        jQuery('#lasso-lite-click-snapshot-box').fadeOut();
        jQuery('#lasso-lite-link-issues-snapshot-box').fadeOut();
    });
    
    // Handle search form
    jQuery("#links-filter").submit(function (e) {
        e.preventDefault();
        let keyword = jQuery("#link-search-input").val().trim();
        lasso_lite_helper.update_url_parameter('link-search-input', keyword);
        load_dashboard('', keyword );
    });
});
