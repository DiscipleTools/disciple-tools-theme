const GA_ACCESS_CODE_MODAL_ID = "ga_access_code_modal";
const GA_ACCESS_CODE_TMP_ID = "ga_access_code_tmp";
const GA_ACCESS_CODE_ID = "ga_access_code";
const GA_FORM_ID = "ga_form";
const GA_MODAL_CLOSE_ID = 'ga_close';
const GA_MODAL_BTN_CLOSE_ID = 'ga_btn_close';
const GA_GOOGLE_AUTH_BTN_ID = 'ga_authorize_with_google_button';
const GA_SAVE_ACCESS_CODE_BTN_ID = 'ga_save_access_code';

(function ($) {

    ga_popup = {
        url: '',
        authorize: function (e, url) {
            e.preventDefault();
            ga_popup.url = url;
            $('#' + GA_ACCESS_CODE_MODAL_ID).appendTo("body").show();
            ga_popup.open();
        },
        open: function () {
            const p_width = Math.round(screen.width / 2);
            const p_height = Math.round(screen.height / 2);
            const p_left = Math.round(p_width / 2);
            const p_top = 300;
            window.open(ga_popup.url, 'ga_auth_popup', 'width=' + p_width + ',height='
                + p_height + ',top=' + p_top + ',left=' + p_left);
        },
        saveAccessCode: function (e) {
            e.preventDefault();
            e.target.disabled = 'disabled';
            ga_loader.show();
            const ac_tmp = $('#' + GA_ACCESS_CODE_TMP_ID).val();
            if (ac_tmp) {
                $('#' + GA_ACCESS_CODE_ID).val(ac_tmp);
                $('#' + GA_FORM_ID).submit();
            }
        }
    };

    ga_modal = {
        hide: function () {
            $('#' + GA_ACCESS_CODE_MODAL_ID).hide();
            ga_loader.hide();
            $('#' + GA_SAVE_ACCESS_CODE_BTN_ID).removeAttr('disabled');
        }
    };

    ga_events = {

        click: function (selector, callback) {
            $(selector).live('click', callback);
        },
        codeManuallyCallback: function (terms_accepted) {
            const button_disabled = $('#ga_authorize_with_google_button').attr('disabled');
            const selector_disabled = $('#ga_account_selector').attr('disabled');
            if (terms_accepted) {
                if (button_disabled) {
                    $('#ga_authorize_with_google_button').removeAttr('disabled').next().hide();
                } else {
                    $('#ga_authorize_with_google_button').attr('disabled',
                        'disabled').next().show();
                }

                if (selector_disabled) {
                    $('#ga_account_selector').removeAttr('disabled');
                } else {
                    $('#ga_account_selector').attr('disabled', 'disabled');
                }
            }

            $('#ga_manually_wrapper').toggle();
        },
        initModalEvents: function () {
            $('#' + GA_GOOGLE_AUTH_BTN_ID).on('click', function () {
                $('#' + GA_ACCESS_CODE_TMP_ID).focus();
            });

            $('#' + GA_MODAL_CLOSE_ID + ', #' + GA_MODAL_BTN_CLOSE_ID).on('click', function () {
                ga_modal.hide();
            });
        }
    };

    /**
     * Handles "disable all features" switch button
     * @type {{init: ga_switcher.init}}
     */
    ga_switcher = {
        init: function (state) {
            var checkbox = $("#ga-disable");

            if (state) {
                checkbox.prop('checked', 'checked');
            } else {
                checkbox.removeProp('checked');
            }

            $("#ga-slider").on("click", function (e) {
                var manually_enter_not_checked = $('#ga_enter_code_manually').not(':checked');
                if (checkbox.not(':checked').length > 0) {
                    if (confirm('This will disable Dashboards, Viral Alerts and Google API')) {
                        setTimeout(function () {
                            window.location.href = GA_DISABLE_FEATURE_URL;
                        }, 350);
                    } else {
                        setTimeout(function () {
                            checkbox.removeProp('checked');
                        }, 350);
                    }
                } else { // disable
                    setTimeout(function () {
                        window.location.href = GA_ENABLE_FEATURE_URL;
                    }, 350);
                }
            });
        }
    };

    $(document).ready(function () {
        ga_events.initModalEvents();
    });

    const offset = 50;
    const minWidth = 350;
    const wrapperSelector = '#ga-stats-container';
    const chartContainer = 'chart_div';

    ga_charts = {

        init: function (callback) {
            $(document).ready(function () {
                google.charts.load('current', {
                    'packages': ['corechart']
                });
                ga_loader.show();
                google.charts.setOnLoadCallback(callback);
            });
        },
        createTooltip: function (day, pageviews) {
            return '<div style="padding:10px;width:100px;">' + '<strong>' + day
                + '</strong><br>' + 'Pageviews:<strong> ' + pageviews
                + '</strong>' + '</div>';
        },
        events: function (data) {
            $(window).on('resize', function () {
                ga_charts.drawChart(data, ga_tools.recomputeChartWidth(minWidth, offset, wrapperSelector));
            });
        },
        drawChart: function (data, chartWidth) {

            if (typeof chartWidth == 'undefined') {
                chartWidth = ga_tools.recomputeChartWidth(minWidth, offset, wrapperSelector);
            }

            const options = {
                /*title : 'Page Views',*/
                lineWidth: 5,
                pointSize: 10,
                tooltip: {
                    isHtml: true
                },
                legend: {
                    position: (ga_tools.getCurrentWidth(wrapperSelector) <= minWidth ? 'top'
                        : 'top'),
                    maxLines: 5,
                    alignment: 'start',
                    textStyle: {color: '#000', fontSize: 12}
                },
                colors: ['#4285f4', '#ff9800'],
                hAxis: {
                    title: 'Day',
                    titleTextStyle: {
                        color: '#333'
                    }
                },
                vAxis: {
                    minValue: 0
                },
                width: chartWidth,
                height: 500,
                chartArea: {
                    top: 50,
                    left: 50,
                    right: 30,
                    bottom: 100
                },
            };
            var chart = new google.visualization.AreaChart(document
                .getElementById(chartContainer));
            chart.draw(data, options);
        }
    };
})(jQuery);
