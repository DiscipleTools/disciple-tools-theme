/*
 * This is loaded after a custom build of Modernizr, and its sole purpose is to
 * detect whether the browser supports required features.
 *
 * This script should run successfully even in very old browsers, so don't use
 * any fancy features, and don't depend on any libraries except Modernizr.
 */

(function(Modernizr) {
  var requiredFeatures = [
    'arrow',
    'csscalc',
    'es5',
    'es5array',
    'es5function',
    'es5object',
    'es5string',
    'es5syntax',
    'es6string',
    'flexbox',
    'hidden',
    'promises',
    'strictmode',
    'templatestrings'
    // remember not to use a trailing comma, as older browsers don't support it
  ];

  var supportsAllFeatures = true;
  for (var i = 0; i < requiredFeatures.length; i++) {
    if (! Modernizr[requiredFeatures[i]]) {
      if (window.console && window.console.log) {
        console.log("Required feature is missing: " + requiredFeatures[i]);
      }
      supportsAllFeatures = false;
    }
  }

  if (! supportsAllFeatures) {
    if (window.console && window.console.log) {
      console.log("Not all required features are supported, please update your browser. Internet Explorer is not supported.");
    }

    ready(function() {
      try {
        document.querySelector("#js-missing-required-browser-features-notice").removeAttribute("hidden");
      } catch (e) {
        alert("Please update your browser. Internet Explorer is not supported.");
      }
    });
  }

  function ready(fn) {
    if (document.readyState != 'loading'){
      fn();
    } else if (document.addEventListener) {
      document.addEventListener('DOMContentLoaded', fn);
    } else {
      document.attachEvent('onreadystatechange', function() {
        if (document.readyState != 'loading')
          fn();
      });
    }
  }

})(window.Modernizr);
