/*! Lazy Load XT v1.0.4 2014-05-30
 * http://ressio.github.io/lazy-load-xt
 * (C) 2014 RESS.io
 * Licensed under MIT */

(function (document) {

    function loadImages() {
        var imgs = document.getElementsByTagName("img"),
            imgs_length = imgs.length;
        for (var i = 0; i < imgs_length; i++) {
            var el = imgs[i],
                src = el.getAttribute("data-src");
            if (src) {
                el.src = src;
            }
        }
    }

    if (document.addEventListener) {
        document.addEventListener("DOMContentLoaded", loadImages, false);
    } else if (document.attachEvent) {
        document.attachEvent("onreadystatechange", function () {
            if (document.readyState === "complete") {
                loadImages();
            }
        });
    }

})(document);