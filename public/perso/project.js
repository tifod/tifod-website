smoothScroll.init();

$("img.lazyload").unveil(500, function(){
    $(this).load(function() {
        resizePlayer();
    });
});

var radioBtns = document.getElementsByClassName('link-radio-button');
for (var z = 0; z < radioBtns.length; z++){
    if (radioBtns[z].checked == true) showPost(radioBtns[z].parentNode.getAttribute('data-target'));
}

function doubleScroll(element) {
    if (element.parentNode.firstChild.className != 'dblscrollbar'){
        var scrollbar= document.createElement('div');
        scrollbar.appendChild(document.createElement('div'));
        scrollbar.className = 'dblscrollbar';
        scrollbar.style.overflow= 'auto';
        scrollbar.style.overflowY= 'hidden';
        scrollbar.style.marginBottom= '1em';
        scrollbar.firstChild.style.width= element.scrollWidth+'px';
        scrollbar.firstChild.style.paddingTop= '1px';
        scrollbar.firstChild.appendChild(document.createTextNode('\xA0'));
        scrollbar.onscroll= function() {
            element.scrollLeft= scrollbar.scrollLeft;
        };
        element.onscroll= function() {
            scrollbar.scrollLeft= element.scrollLeft;
        };
        element.parentNode.insertBefore(scrollbar, element);
    }
}

function handleImage(e){
    var postId = event.target.getAttribute('data-postid');
    
    var reader = new FileReader();
    reader.onload = function(event){
        var img = new Image();
        img.onload = function(){
            document.getElementById(postId).parentNode.getElementsByClassName('file-dimensions')[0].innerHTML = img.width + ' × ' + img.height;
            document.getElementById(postId + '-drawing-board').style.height = img.height + 'px';
            document.getElementById(postId + '-drawing-board').style.width = img.width + 'px';
            resetBoard(postId + '-drawing-board');
            resizePlayer();
            animationsTest(function(){
                var canvas = document.getElementById(postId + '-drawing-board').getElementsByTagName('canvas')[0];
                var ctx = canvas.getContext('2d');
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img,0,0);
            });
        }
        img.src = event.target.result;
    }
    reader.readAsDataURL(event.target.files[0]);     
}

// drawing board
$('.drawingboard-width-input').on('change',function(){ resizeDrawingBoardSize('width',this.getAttribute('data-postid')); });
$('.drawingboard-height-input').on('change',function(){ resizeDrawingBoardSize('height',this.getAttribute('data-postid')); });
function resizeDrawingBoardSize (dimension, postId){
    if (dimension == 'height'){
        document.getElementById(postId + '-drawing-board').style.height = event.target.value < 310 ? (event.target.value*1 + 89) +'px' : (event.target.value*1 + 33) +'px';
    } else if (dimension == 'width'){
        document.getElementById(postId + '-drawing-board').style.width = event.target.value + 'px';
    }
    var boardWidth = document.getElementById(postId + '-drawing-board').style.width.slice(0, -2);
    var decalage = boardWidth > 307 ? 33 : ( boardWidth > 257 ? 61 : 89 );
    document.getElementById(postId + '-drawing-board').style.height = (document.getElementById(postId + '-drawing-board').style.height.slice(0, -2) - decalage) + 'px';
    resetBoard(postId + '-drawing-board');
    resizePlayer();
}
var mainBoard;
function resetBoard (elId){
    if (document.getElementById(elId) != null){
        document.getElementById(elId).innerHTML = '';
        mainBoard = new DrawingBoard.Board(elId, {
            controls: [
                'Color',
                { Size: { type: 'dropdown' } },
                { DrawingMode: { filler: false } },
                'Navigation',
                'Download'
            ],
            size: 1,
            webStorage: 'session',
            enlargeYourContainer: true,
            droppable: true
        });
        
        doubleScroll(document.getElementById(elId).parentNode);
        
        $('#' + elId).parent().parent().on('submit', drawingSubmit);
        
        function drawingSubmit(){
           //get drawingboard content
          var img = mainBoard.getImg();
          
          //we keep drawingboard content only if it's not the 'blank canvas'
          var imgInput = (mainBoard.blankCanvas == img) ? '' : img;
          
          //put the drawingboard content in the form field to send it to the server
          $(this).find('input[name=image]').val( imgInput );

          //we can also assume that everything goes well server-side
          //and directly clear webstorage here so that the drawing isn't shown again after form submission
          //but the best would be to do when the server answers that everything went well
          mainBoard.clearWebStorage();
        }
    }
}
// checkbox-more init
var checkboxMore = document.getElementsByClassName('checkbox-more');
for (var z = 0; z < checkboxMore.length; z++){
    (checkboxMore[z]).onchange = function(){
        var cssRule = '.prev, .next { display: none; }';
        var cssId = 'style-for-prev-next';
        var css = document.getElementById(cssId);
        if (css == null){
            css = document.createElement("style");
            css.type = "text/css";
            css.id = cssId;
            css.innerHTML = cssRule;
            document.body.appendChild(css);
        } else {
            var oneChecked = false;
            var allCheckboxMore = document.getElementsByClassName('checkbox-more');
            for (var y = 0; y < allCheckboxMore.length; y++){
                if (allCheckboxMore[y].checked) oneChecked = true;
            }
            css.innerHTML = oneChecked ? cssRule : '';
        }
        
        resetBoard(this.getAttribute('data-postid') + '-drawing-board');
        resizePlayer();
    };
}

// tree-link init
var treeLinks = document.getElementsByClassName('tree-link');
for(var z = 0; z < treeLinks.length; z++) {
    treeLinks[z].onclick = function (){
        var postTreeId = this.getAttribute('href');
        $(postTreeId).addClass('tree-post-active');
        var scrollQuantity = $(postTreeId).offset().left - $(postTreeId).parent().offset().left + $(postTreeId).parent().scrollLeft();
        $(postTreeId).parent().stop().animate({ scrollLeft: scrollQuantity }, 500);
        animationsTest(function(){
            setTimeout(function(){
                $(postTreeId).removeClass('tree-post-active');
            },1000);
        });
    };
}

// project player height init
resizePlayer();

// post nav init
var links = document.getElementsByClassName('link');
for(var z = 0; z < links.length; z++) {
    var elem = links[z];
    elem.onclick = function (){ showPost(this.getAttribute('data-target')); };
}

// tree nav init
$('#project-tree').on('click','.tree-post',function(){
    goToPost(this.getAttribute('data-id'));
});

// scroll post siblings init
var postSiblings = document.getElementsByClassName('many-posts');
for(var z = 0; z < postSiblings.length; z++) {
    $(postSiblings[z]).on("swiperight",function(){ prevSlide(this); });
    $(postSiblings[z]).on("swipeleft",function(){ nextSlide(this); });
}

// nextSlide & prevSlide
function nextSlide (element){
    var thisPost = element.parentNode.getElementsByClassName('active-post')[0].parentNode;
    if (thisPost.nextElementSibling == null){
        showPost(thisPost.parentNode.childNodes[0].getElementsByClassName('post')[0].getAttribute('id'));
    } else {
        showPost(thisPost.nextElementSibling.getElementsByClassName('post')[0].getAttribute('id'));
    }
}
function prevSlide (element){
    var thisPost = element.parentNode.getElementsByClassName('active-post')[0].parentNode;
        if (thisPost.previousElementSibling == null){
            var t = thisPost.parentNode.getElementsByClassName('post');
            showPost(t[t.length - 1].getAttribute('id'));
        } else {
            showPost(thisPost.previousElementSibling.getElementsByClassName('post')[0].getAttribute('id'));
        }
}

// next buttons init
var next = document.getElementsByClassName('next');
for(var z = 0; z < next.length; z++) { $(next[z]).on("click",function(){ nextSlide(this); }); }

// prev buttons init
var prev = document.getElementsByClassName('prev');
for(var z = 0; z < prev.length; z++) { $(prev[z]).on("click",function(){ prevSlide(this); }); }

function hasClass(element, cls) { return (' ' + element.className + ' ').indexOf(' ' + cls + ' ') > -1; }

function goToPost (postId){
    // get all the ids from data-path
    var targetedPosts = JSON.parse("[" + document.getElementById(postId).getAttribute('data-path').slice(1, -1).replace(/\//g,', ') + "]");
    targetedPosts.shift();
    // show each of them
    for(var z = 0; z < targetedPosts.length; z++) showPost(targetedPosts[z]);
    // scroll to the targeted post
    smoothScroll.animateScroll(document.getElementById(postId));
}

function showPost (postId) {
    // Select '.post-level' among direct children
    var allSiblingsLvl = document.getElementById(postId).parentNode.parentNode.parentNode.parentNode.childNodes;
    var levels = [];
    for (var i = 0; i < allSiblingsLvl.length; i++) {
        if (hasClass(allSiblingsLvl[i], 'post-level')) {
            levels.push(allSiblingsLvl[i]);
        }
    }
    // and un-activate them
    for(var y = 0; y < levels.length; y++) {
        levels[y].className = 'post-level';
    }
    // for finally activating only the targeted post '.post-level'
    // only if there is a '.post-level' (all post don't necessarily have children)
    if (document.getElementById(postId + '-children') != null) {
        document.getElementById(postId + '-children').className = 'post-level active-level';
    }
    
    // same process but for .post
    var posts = document.getElementById(postId).parentNode.parentNode.childNodes;
    for(var y = 0; y < posts.length; y++) {
        $(posts[y].getElementsByClassName('post')[0]).removeClass('active-post');
    }
    $(document.getElementById(postId)).addClass('active-post');
    
    resizePlayer();
    
    // check the radio button matching with the post
    // if it exists
    if (document.getElementById(postId + '-radio-button') != null) document.getElementById(postId + '-radio-button').checked = true;
    
    // scroll to the post
    var scrollQuantity = $(document.getElementById(postId).parentNode).offset().left - $(document.getElementById(postId).parentNode.parentNode).offset().left + $(document.getElementById(postId).parentNode.parentNode).scrollLeft();
    $(document.getElementById(postId).parentNode.parentNode).stop().animate({ scrollLeft: scrollQuantity }, 500);
};

function resizePlayer(){
    if (document.getElementsByClassName('post').length != 0){
        // resize the ".posts" to the post height
        var posts = document.getElementsByClassName('active-post');
        for(var z = 0; z < posts.length; z++) {
            posts[z].parentNode.parentNode.style.height = getAbsoluteHeight(posts[z]) + 'px';
        }
        
        animationsTest(function(){
            // resize the whole '#project-player'
            document.getElementById('project-player').style.height = getAbsoluteHeight(document.getElementById('project-player').firstElementChild) + 'px';
        });
    }
}

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

function getAbsoluteHeight(el) {
  var styles = window.getComputedStyle(el);
  var margin = parseFloat(styles['marginTop']) +
               parseFloat(styles['marginBottom']);

  return Math.ceil(el.offsetHeight + margin);
}

function updateScore (el, response){
    el.parentNode.getElementsByClassName('post-score')[0].innerHTML = response['0'].score_result;
    el.parentNode.getElementsByClassName('post-score-plus')[0].innerHTML = response['0'].vote_plus;
    el.parentNode.getElementsByClassName('post-score-minus')[0].innerHTML = response['0'].vote_minus;
}

function updatePin (el,response){
    el.innerHTML = response['0'].has_pin == true ? el.getAttribute('data-unpin') : el.getAttribute('data-repin');
}

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