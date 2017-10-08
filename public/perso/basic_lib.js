function goToPostBasedOnHash () {
    var hash = window.location.hash.slice(1);
    if (/^\d+$/.test(hash)){
        scrollTo(document.getElementById(hash));
    }
};

// hasClass
function hasClass(element, cls) { return (' ' + element.className + ' ').indexOf(' ' + cls + ' ') > -1; }

// Test if ANY/ALL page animations are currently active
function animationsTest (callback) {
    var testAnimationInterval = setInterval(function () {
        if (! $.timers.length) { // any page animations finished
            clearInterval(testAnimationInterval);
            callback();
        }
    }, 25);
};
// the same, but with any condition
function conditionTest (thingToTest,whatItIsSupposedToBeEqualTo,callback){
    var testAnimationInterval = setInterval(function () {
        if (thingToTest == whatItIsSupposedToBeEqualTo) { // any page animations finished
            clearInterval(testAnimationInterval);
            callback();
        }
    }, 25);
}

// toggle css rule
// ".exemple-class { ex-rule: 0; }"
function toggleCssRule (cssRule){
    var css = document.getElementById('special-style-created-with-js');
    if (css == null){
        css = document.createElement("style");
        css.type = "text/css";
        css.id = 'special-style-created-with-js';
        css.innerHTML = cssRule;
        document.body.appendChild(css);
    } else {
        css.innerHTML = (css.innerHTML == cssRule) ? '' : cssRule;
    }
}

// AJAX call
// URL to ping, HTML element, callback
function ajaxPingUrl(url,el,callback) {
    var xmlhttp = new XMLHttpRequest(),
    paramLength = arguments.length;

    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == XMLHttpRequest.DONE ) {
            if (xmlhttp.status == 200) {
                if (paramLength == 3){
                    callback(el,JSON.parse(xmlhttp.responseText));
                } else {
                    console.log(url + ', status: ' + xmlhttp.status + ' => ' + xmlhttp.responseText);
                }
            } else {
                console.log(url + ', status: ' + xmlhttp.status + ' => ' + xmlhttp.responseText);
            }
        }
    }.bind(paramLength);

    xmlhttp.open("GET", url, true);
    xmlhttp.send();
}

function scrollTo (el){
    if (el) $('html, body').animate( { scrollTop: $(el).offset().top }, 500);
}

// btn-copy-js : data-msg
$(document).on('click', '.btn-copy-js', function(){
    document.getElementById("snackbar").MaterialSnackbar.showSnackbar({message: this.getAttribute("data-msg")});
});

// data-scroll
$(document).on('click', 'a[data-scroll]', function() {
    scrollTo($($(this).attr('href')));
    return false;
});

window.onhashchange = goToPostBasedOnHash;

$(function(){
    new Clipboard (".btn-copy-js");
    animationsTest(function(){ setTimeout(goToPostBasedOnHash,500); });
});