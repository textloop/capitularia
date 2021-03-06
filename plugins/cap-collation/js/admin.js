'use strict';

function add_ajax_action (data, action) {
    /* the AJAX action */
    data.action = action;
    /* the AJAX nonce  */
    data[ajax_object.ajax_nonce_param_name] =
        ajax_object.ajax_nonce;
    return data;
}

function get_manuscripts_list () {
    // Get sigla of all manuscript to collate
    var manuscripts = [];
    jQuery ('table.cap-collation-table-witnesses tbody input:checked').each (function () {
        manuscripts.push (jQuery (this).val ());
    });
    return manuscripts;
}

function get_ignored_manuscripts_list () {
    // Get sigla of all manuscript to ignore
    var manuscripts = [];
    jQuery ('table.cap-collation-table-witnesses tbody input:not(:checked)').each (function () {
        manuscripts.push (jQuery (this).val ());
    });
    return manuscripts;
}

function get_normalizations () {
    return jQuery ('#normalizations').val ().split ('\n');
}

function get_sections_params () {
    var data = {
        'bk' : jQuery ('#bk').val (),
    };
    return data;
}

function get_manuscripts_params () {
    var data = {
        'corresp' : jQuery ('#section').val (),
    };
    data = jQuery.extend (data, get_sections_params ());
    return data;
}

function get_collation_params () {
    var data = {
        'later_hands'          : jQuery ('#later_hands').prop ('checked'),
        'algorithm'            : jQuery ('#algorithm').val (),
        'levenshtein_distance' : jQuery ('#levenshtein_distance').val (),
        'levenshtein_ratio'    : jQuery ('#levenshtein_ratio').val (),
        'segmentation'         : jQuery ('#segmentation').prop ('checked'),
        'transpositions'       : jQuery ('#transpositions').prop ('checked'),
        'manuscripts'          : get_manuscripts_list (),
        'ignored'              : get_ignored_manuscripts_list (),
        'normalizations'       : get_normalizations (),
    };
    data = jQuery.extend (data, get_manuscripts_params ());
    return data;
}

/**
 * Check or uncheck checkboxes according to manuscript list
 *
 * @param sigla   List of sigla of the manuscripts to check or uncheck
 * @param checked To check or to uncheck
 */

function check_from_list (sigla, checked) {
    var $checkboxes = jQuery ('table.cap-collation-table-witnesses tbody input');
    $checkboxes.each (function () {
        var $checkbox = jQuery (this);
        if (jQuery.inArray ($checkbox.val (), sigla) !== -1) {
            $checkbox.prop ('checked', checked);
        }
    });
}

/**
 * Sort the sigla to the top of the table.
 *
 * @param sigla   List of sigla of the manuscripts
 */

function sort_from_list (sigla) {
    var $tbody = jQuery ('table.cap-collation-table-witnesses tbody');
    $.each (sigla.reverse (), function (index, siglum) {
        var $tr = $tbody.find ('tr[data-siglum="' + siglum + '"]');
        $tr.prependTo ($tbody); // sort to the top
    });
}

function check_all (checked) {
    jQuery ('table.cap-collation-table-witnesses tbody input').prop ('checked', checked);
}

function encodeRFC5987ValueChars (str) {
    return encodeURIComponent (str)
        // Note that although RFC3986 reserves '!', RFC5987 does not,
        // so we do not need to escape it
        .replace (/['()]/g, escape) // i.e., %27 %28 %29
        .replace (/\*/g, '%2A')
        // The following are not required for percent-encoding per RFC5987,
        // so we can allow for a little better readability over the wire: |`^
        .replace (/%(?:7C|60|5E)/g, unescape);
}

/* Save parameters to a user-local file. */

function save_params () { // eslint-disable-line no-unused-vars
    var params = get_collation_params ();
    var url = 'data:text/plain,' + encodeURIComponent (JSON.stringify (params, null, 2));
    var e = document.getElementById ('save-fake-download');
    e.setAttribute ('href', url);
    e.setAttribute (
        'download',
        'save-' + encodeRFC5987ValueChars (params.corresp.toLowerCase ()) + '.txt'
    );
    e.click ();

    return false;
}

function click_on_load_params (fileInput) { // eslint-disable-line no-unused-vars
    var e = document.getElementById ('load-params');
    e.click ();
}

function clear_manuscripts () {
    var $div     = jQuery ('#manuscripts-div');
    var deferred = jQuery.Deferred ();

    $div.slideUp (function () {
        deferred.resolve ();
    });
    return deferred.promise ();
}

function clear_collation () {
    var $div = jQuery ('#collation-tables');
    var deferred = jQuery.Deferred ();

    $div.fadeOut (function () {
        $div.children ().remove ();
        deferred.resolve ();
    });
    return deferred.promise ();
}

function add_spinner ($parent) {
    var spinner = jQuery ('<div class="spinner-div"><span class="spinner is-active" /></div>');
    spinner.hide ();
    $parent.append (spinner);
    spinner.fadeIn ();
    return spinner;
}

function clear_spinners () {
    var spinners = jQuery ('div.spinner-div');
    spinners.fadeOut (function () {
        jQuery (this).detach (); // Do not use remove here or promise () won't work.
    });
    return spinners.promise ();
}

function handle_message (div, response) {
    var msg = jQuery (response.message).hide ().prependTo (div);
    clear_spinners ().done (function () {
        msg.fadeIn ();
        /* Adds a 'dismiss this notice' button. */
        jQuery (document).trigger ('wp-plugin-update-error');
    });
}

function on_cap_load_sections (onReady) {
    var data = add_ajax_action (get_sections_params (), 'on_cap_load_sections');

    clear_manuscripts ();
    clear_collation ();

    var div = jQuery ('#collation-capitulary');
    add_spinner (div);

    jQuery.ajax ({
        'method' : 'POST',
        'url'    : ajaxurl,
        'data'   : data,
    }).done (function (response) {
        clear_spinners ().done (function () {
            jQuery ('#section').html (response.html);
            if (onReady !== undefined) {
                onReady ();
            }
        });
    }).always (function (response) {
        handle_message (div, response);
    });
    return false;  // don't submit form
}

function on_cap_load_manuscripts (onReady) {
    var data = add_ajax_action (get_manuscripts_params (), 'on_cap_load_manuscripts');
    var manuscripts = get_manuscripts_list ();
    var ignored = get_ignored_manuscripts_list ();
    var $div = jQuery ('#manuscripts-div');

    var p1 = clear_manuscripts ();
    var p2 = clear_collation ();
    var p3 = jQuery.ajax ({
        'method' : 'POST',
        'url'    : ajaxurl,
        'data'   : data,
    });

    jQuery.when (p1, p2).done (function () {
        var div = jQuery ('#collation-capitulary');
        add_spinner (div);
    });

    jQuery.when (p1, p2, p3).done (function () {
        // var $tbody = jQuery ('#manuscripts-tbody');
        // jQuery (p3.responseJSON.html).appendTo ($tbody);
        var $wrapper = jQuery ('div.witness-list-table-wrapper');
        $wrapper.empty ();
        jQuery (p3.responseJSON.html).appendTo ($wrapper);
        check_all (true);
        check_from_list (ignored, false);
        sort_from_list (ignored);
        sort_from_list (manuscripts);
        clear_spinners ().done (function () {
            $div.slideDown ();
            jQuery ('div.accordion').accordion ({
                'collapsible' : true,
                'active'      : false,
            });
            if (onReady !== undefined) {
                onReady ();
            }
        });
    }).always (function () {
        handle_message ($div, p3.responseJSON);

        jQuery ('table.cap-collation-table-witnesses').disableSelection ().sortable ({
            'items' : 'tr[data-siglum]',
        });
    });
    return false;  // don't submit form
}

function on_cap_load_collation () {         // eslint-disable-line no-unused-vars
    var data = add_ajax_action (get_collation_params (), 'on_cap_load_collation');

    var p1 = clear_collation ();
    var p2 = jQuery.ajax ({
        'method' : 'POST',
        'url'    : ajaxurl,
        'data'   : data,
    });

    p1.done (function () {
        var $div = jQuery ('#manuscripts-div');
        add_spinner ($div);
    });

    var $div = jQuery ('#collation-tables');
    jQuery.when (p1, p2).done (function () {
        jQuery (p2.responseJSON.html).appendTo ($div);
        clear_spinners ().done (function () {
            $div.fadeIn ();
            $div.find ('div.accordion').accordion ({
                'collapsible' : true,
                'active'      : false,
            });
        });
    }).always (function () {
        handle_message ($div, p2.responseJSON);

        var data_rows = jQuery ('tr[data-siglum]');
        data_rows.hover (function () {
            $div.find ('tr[data-siglum="' + jQuery (this).attr ('data-siglum') +  '"]').addClass ('highlight-witness');
        }, function () {
            data_rows.each (function () {
                jQuery (this).removeClass ('highlight-witness');
            });
        });
    });
    return false;  // don't submit form
}

/*
 * Activate the 'select all' checkboxes on the tables.
 * Stolen from wp-admin/js/common.js
 */

function make_cb_select_all (ev, ui) { // eslint-disable-line no-unused-vars
    ui.panel.find ('thead, tfoot').find ('.check-column :checkbox').on ('click.wp-toggle-checkboxes', function (event) {
        var $this = jQuery (this);
        var $table = $this.closest ('table');
        var controlChecked = $this.prop ('checked');
        var toggle = event.shiftKey || $this.data ('wp-toggle');

        $table.children ('tbody')
            .filter (':visible')
            .children ()
            .children ('.check-column')
            .find (':checkbox')
            .prop ('checked', function () {
                if (jQuery (this).is (':hidden,:disabled')) {
                    return false;
                }

                if (toggle) {
                    return !jQuery (this).prop ('checked');
                } else if (controlChecked) {
                    return true;
                }

                return false;
            });

        $table.children ('thead,  tfoot')
            .filter (':visible')
            .children ()
            .children ('.check-column')
            .find (':checkbox')
            .prop ('checked', function () {
                if (toggle) {
                    return false;
                } else if (controlChecked) {
                    return true;
                }
                return false;
            });
    });
}

/* Load parameters from a user-local file. */

function load_params (fileInput) { // eslint-disable-line no-unused-vars
    var files = fileInput.files;
    if (files.length !== 1) {
        return false;
    }

    var reader = new FileReader ();
    reader.onload = function (e) {
        var json = JSON.parse (e.target.result);

        /* Set the control value and then call the onclick function. */
        jQuery ('#bk').val (json.bk);
        on_cap_load_sections (function () {
            /* Set the control value and then call the onclick function. */
            jQuery ('#section').val (json.corresp);
            on_cap_load_manuscripts (function () {
                jQuery ('#algorithm').val (json.algorithm);
                jQuery ('#levenshtein_distance').val (json.levenshtein_distance);
                jQuery ('#levenshtein_ratio').val (json.levenshtein_ratio);
                jQuery ('#segmentation').prop ('checked', json.segmentation);
                jQuery ('#transpositions').prop ('checked', json.transpositions);
                jQuery ('#normalizations').val (json.normalizations.join ('\n'));
                check_all (false);
                check_from_list (json.manuscripts, true);
                sort_from_list (json.ignored);
                sort_from_list (json.manuscripts);
            });
        });
    };
    reader.readAsText (files[0]);

    return false; // Don't submit form
}

jQuery (document).ready (function () {
    clear_manuscripts ();
    clear_collation ();
});
