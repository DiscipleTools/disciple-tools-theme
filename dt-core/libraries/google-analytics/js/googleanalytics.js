/**
 * Created by mdn on 2016-12-02.
 */

(function ($) {
    ga_loader = {
        show: function () {
            $('.ga-loader').show();
        },
        hide: function () {
            $('.ga-loader').hide();
        }
    };

    ga_tools = {
        getCurrentWidth: function (wrapperSelector) {
            return $(wrapperSelector).width();
        },
        recomputeChartWidth: function (minWidth, offset, wrapperSelector) {
            const currentWidth = ga_tools.getCurrentWidth(wrapperSelector);
            if (currentWidth >= minWidth) {
                return parseInt(currentWidth - offset);
            } else {
                return minWidth;
            }
        }
    };
    
    ga_trending_loader = {
        show: function() {
            $('.ga-trending-loader').show();
            ga_loader.show();
            $(document).ready(function () {
                $('.ga-trending-loader').hide();
            });
        }
    };
})(jQuery);
