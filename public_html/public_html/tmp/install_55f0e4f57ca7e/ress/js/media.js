(function (window) {
    var isOldIE = !window.addEventListener;
    window[isOldIE ? 'attachEvent' : 'addEventListener'](isOldIE ? 'onload' : 'load', function () {
        var matchMedia = window.matchMedia,
            links = document.getElementsByTagName('link'),
            relCss = 'stylesheet';
        for (var i = 0, n = links.length; i < n; i++) {
            var link = links[i];
            if (link.rel === 'ress-stylesheet') {
                if (!matchMedia) {
                    link.rel = relCss;
                } else {
                    var mq = matchMedia(link.media || '');
                    if (mq.matches) {
                        link.rel = relCss;
                    } else {
                        (function (link) {
                            mq.addListener(function (mq) {
                                if (mq.matches) {
                                    link.rel = relCss;
                                }
                            });
                        })(link);
                    }
                }
            }
        }
    });
})(window);