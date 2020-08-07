/**
 * Extend browser support for Element.closest and Element.matches all the way back to IE9.
 *
 * Author: MDN
 * https://developer.mozilla.org/en-US/docs/Web/API/Element/closest#Polyfill
 */

(function () {
  if (!Element.prototype.matches) {
    Element.prototype.matches = Element.prototype.msMatchesSelector || 
                                Element.prototype.webkitMatchesSelector;
  }

  if (!Element.prototype.closest) {
    Element.prototype.closest = function(s) {
      var el = this;

      do {
        if (el.matches(s)) return el;
        el = el.parentElement || el.parentNode;
      } while (el !== null && el.nodeType === 1);
      return null;
    };
  }
})();
