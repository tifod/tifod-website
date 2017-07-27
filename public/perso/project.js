$(function(){
    function goToPostBasedOnHash () {
        var hash = window.location.hash.slice(1);
        if (/^\d+$/.test(hash)){
            goToPost(hash);
        }
    };
    animationsTest(goToPostBasedOnHash);
    window.onhashchange = goToPostBasedOnHash;

    $(document).on('click', 'a[data-scroll]', function() {
        scrollTo($($(this).attr('href')));
        return false;
    });

    function scrollTo (el){ $('html, body').animate( { scrollTop: $(el).offset().top }, 500); }

    new Clipboard (".btn-copy-js");
    $(document).on('click', '.btn-copy-js', function(){
        document.getElementById("snackbar").MaterialSnackbar.showSnackbar({message: this.getAttribute("data-msg")});
    });

    $("img.lazyload").unveil(500, function(){
        $(this).load(resizePlayer);
    });
    
    var windowWidth = $(window).width();
    $(window).resize(function(){
        if (windowWidth != $(window).width()){
            resizePlayer();
            windowWidth = $(window).width();
        }
    });
    
    var boards = document.getElementsByClassName('drawing-board');
    if (boards.length > 0) resetBoard(boards[boards.length - 1].getAttribute('id'));
    $(document).on('change', '.drawing-board-import', function(){
        var postId = event.target.getAttribute('data-postid');
        var reader = new FileReader();
        reader.onload = function(event){
            var img = new Image();
            img.onload = function(){
                $('#' + postId + ' .file-dimensions').html(img.width + ' × ' + img.height);
                $('#' + postId + '-drawing-board').css('height',img.height + 'px');
                $('#' + postId + '-drawing-board').css('width',img.width + 'px');
                var currentBoard = resetBoard(postId + '-drawing-board',true);
                resizePlayer();
                animationsTest(function(){
                    var canvas = $('#' + postId + '-drawing-board canvas')[0];
                    canvas.width = img.width;
                    canvas.height = img.height;
                    canvas.getContext('2d').drawImage(img,0,0);
                    currentBoard.saveHistory();
                });
            }
            img.src = event.target.result;
        }
        reader.readAsDataURL(event.target.files[0]);
    });

    // drawing board
    $(document).on('change', '.drawingboard-width-input', function(){ resizeDrawingBoardSize('width',this.getAttribute('data-postid')); });
    $(document).on('change', '.drawingboard-height-input', function(){ resizeDrawingBoardSize('height',this.getAttribute('data-postid')); });
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
    function resetBoard (elId, returnBoard){
        if (document.getElementById(elId) != null){
            var post = document.getElementById(elId).parentNode.parentNode.parentNode;
            if (!post.getElementsByClassName('editor-toolbar')[0]) var simplemde = new SimpleMDE({ element: post.getElementsByClassName('textarea-js')[0] });
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
            
            $('#' + elId).parent().parent().on('submit', drawingSubmit);
            
            function drawingSubmit(){
                //get drawingboard content
                var img = mainBoard.getImg();

                if (mainBoard.blankCanvas == img){
                    alert("Impossible d'envoyer un dessin vide");
                    return false;
                } else {
                    //put the drawingboard content in the form field to send it to the server
                    $(this).find('input[name=image]').val(img);
                    
                    //we can also assume that everything goes well server-side
                    //and directly clear webstorage here so that the drawing isn't shown again after form submission
                    //but the best would be to do when the server answers that everything went well
                    mainBoard.clearWebStorage();
                }
            }
            
            if (returnBoard) return mainBoard;
        }
    }
    // checkbox-more init
    $(document).on('change', '.checkbox-more', function(){
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
    });

    // tree-link init
    $(document).on('click', '.tree-link', function(){
        var postTreeId = this.getAttribute('href');
        $(postTreeId).addClass('tree-post-active');
        var scrollQuantity = $(postTreeId).offset().left - $(postTreeId).parent().offset().left + $(postTreeId).parent().scrollLeft();
        $(postTreeId).parent().stop().animate({ scrollLeft: scrollQuantity }, 500);
        animationsTest(function(){
            setTimeout(function(){
                $(postTreeId).removeClass('tree-post-active');
            },1000);
        });
    });

    // project player height init
    resizePlayer();

    // post nav init
    
    $(document).on('click', '.link', function(){
        showPost(this.getAttribute('data-target'));
    });

    // tree nav init
    $('#project-tree').on('click','.tree-post',function(){
        goToPost(this.getAttribute('data-id'));
    });

    // nextSlide & prevSlide
    function nextSlide (){
        var thisPost = this.parentNode.getElementsByClassName('active-post')[0].parentNode;
        if (thisPost.nextElementSibling == null){
            showPost(thisPost.parentNode.childNodes[0].getElementsByClassName('post')[0].getAttribute('id'));
        } else {
            showPost(thisPost.nextElementSibling.getElementsByClassName('post')[0].getAttribute('id'));
        }
    }
    function prevSlide (){
        var thisPost = this.parentNode.getElementsByClassName('active-post')[0].parentNode;
            if (thisPost.previousElementSibling == null){
                var t = thisPost.parentNode.getElementsByClassName('post');
                showPost(t[t.length - 1].getAttribute('id'));
            } else {
                showPost(thisPost.previousElementSibling.getElementsByClassName('post')[0].getAttribute('id'));
            }
    }

    // scroll post siblings init
    $(document).on('swiperight', '.many-posts', prevSlide);
    $(document).on('swipeleft', '.many-posts', nextSlide);
    // next/prev buttons init
    $(document).on('click', '.next', nextSlide);
    $(document).on('click', '.prev', prevSlide);

    function hasClass(element, cls) { return (' ' + element.className + ' ').indexOf(' ' + cls + ' ') > -1; }

    function goToPost (postId){
        // get all the ids from data-path
        var targetedPosts = JSON.parse("[" + document.getElementById(postId).getAttribute('data-path').slice(1, -1).replace(/\//g,', ') + "]");
        targetedPosts.shift();
        // show each of them
        for(var z = 0; z < targetedPosts.length; z++) showPost(targetedPosts[z]);
        // scroll to the targeted post
        scrollTo(document.getElementById(postId));
    }

    function showPost (postId, timing = 500) {
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
        $(document.getElementById(postId).parentNode.parentNode).find('.post').removeClass('active-post');
        $(document.getElementById(postId)).addClass('active-post');
        
        resizePlayer();
        
        // check the radio button matching with the post
        $(".link[data-target='" + postId + "'").find('.link-radio-button').prop('checked',true);
        
        // scroll to the post
        var scrollQuantity = $(document.getElementById(postId).parentNode).offset().left - $(document.getElementById(postId).parentNode.parentNode).offset().left + $(document.getElementById(postId).parentNode.parentNode).scrollLeft();
        $(document.getElementById(postId).parentNode.parentNode).stop().animate({ scrollLeft: scrollQuantity }, timing);
    };

    function resizePlayer(){
        if (document.getElementsByClassName('active-post').length != 0){
            // resize the ".posts" to the post height
            var posts = document.getElementsByClassName('active-post');
            for(var z = 0; z < posts.length; z++) {
                var nav = posts[z].parentNode.parentNode.parentNode.getElementsByClassName('post-siblings-nav')[0];
                var navHeight = (nav !== undefined && nav !== null) ? ((typeof nav === 'object') ? nav.clientHeight : getAbsoluteHeight(nav) ) : 0 ;
                posts[z].parentNode.parentNode.style.height = (getAbsoluteHeight(posts[z]) + navHeight) + 'px';
            }
            // refresh project tree
            if (document.getElementById('project-tree')){
                new Treant({
                    chart: { container: "#project-tree" },
                    nodeStructure: tree_structure
                });
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
    
    if (window.hasOwnProperty("project_data")){
        setInterval(function(){
            ajaxPingUrl('/get_last_posted_on/' + project_data.project_id + '/' + project_data.last_posted_on, document.getElementById('project-player'),function(el,response){
                if (response != false && response.post_data.posted_on != project_data.last_posted_on){
                    if (response.post_data.is_an_edit == 0){
                        if (response.post_data.siblings_amount >= 3){
                            var postChildren = document.getElementById(response.post_data.parent_id + '-children');
                            postChildren.getElementsByClassName('posts')[0].insertAdjacentHTML('afterbegin',response.html);
                            postChildren.getElementsByClassName('post-siblings-nav')[0].childNodes[0].insertAdjacentHTML('afterbegin',response.html_link);
                            showPost(postChildren.getElementsByClassName('active-post')[0].id,0);
                        } else if (response.post_data.siblings_amount == 1) {
                            document.getElementById(response.post_data.parent_id).parentNode.parentNode.parentNode.parentNode.insertAdjacentHTML('beforeend',response.html);
                        } else if (response.post_data.siblings_amount == 2) {
                            var postChildren = document.getElementById(response.post_data.parent_id + '-children');
                            postChildren.getElementsByClassName('posts')[0].insertAdjacentHTML('afterbegin',response.html);
                            postChildren.getElementsByClassName('post-siblings')[0].insertAdjacentHTML('afterbegin',response.html_link);
                            postChildren.getElementsByClassName('link')[1].setAttribute('data-target',postChildren.getElementsByClassName('post')[1].id);
                            showPost(postChildren.getElementsByClassName('post')[1].id,0);
                        }
                        document.getElementsByClassName('post-more-menus')[0].insertAdjacentHTML('beforeend',response.html_menu);
                        tree_structure = JSON.parse(response.tree_structure);
                        resizePlayer();
                        
                        componentHandler.upgradeDom();
                        document.getElementById("snackbar").MaterialSnackbar.showSnackbar({
                            message : "Un nouveau post vient d'être créé",
                            actionText: "Voir",
                            actionHandler: function (){ goToPost(response.post_data.id); },
                            timeout: (8 * 1000),
                        });
                    } else {
                        document.getElementById("snackbar").MaterialSnackbar.showSnackbar({
                            message : "Une modification vient d'être posté",
                            actionText: "Voir",
                            actionHandler: function (){ location = '/edit/' + response.post_data.parent_id + '#' + response.post_data.id; },
                            timeout: (8 * 1000),
                        });
                    }
                    if ($('#new_post_amount').length){
                        var current_amount = parseInt($('#new_post_amount').html());
                        $('#new_post_amount').html(current_amount + 1);
                    } else {
                        document.getElementsByClassName('project-header')[0].getElementsByClassName('list-inline')[0].insertAdjacentHTML('beforeend','<br><span>Il y a <span id="new_post_amount">1</span> nouveau post, <a href>cliquez ici</a> pour rafraichir la page</span>')
                    }
                    
                    // last_posted_on updated
                    project_data.last_posted_on = response.post_data.posted_on;
                }
            });
        },3000);
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
    $(document).on('click', '.vote-btn', function(){
        var id = this.getAttribute('id').split('-');
        var sign = (id[1] == 'upvote') ? 'plus' : 'minus';
        ajaxPingUrl('/vote/' + sign + '/' + id[0],this,updateScore)
    });
});