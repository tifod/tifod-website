$(function(){
    function goToPostBasedOnHash () {
        var hash = window.location.hash.slice(1);
        if (/^\d+$/.test(hash)){
            scrollTo(document.getElementById(hash));
        }
    };
    animationsTest(goToPostBasedOnHash);
    window.onhashchange = goToPostBasedOnHash;
    function scrollTo (el){ $('html, body').animate( { scrollTop: $(el).offset().top }, 500); }
    
    $('.drawing-board-import').on('change', handleImage);
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
        }
    }
    
    resetBoard(document.getElementsByClassName('drawing-board')[0].getAttribute('id'));
    
    // Test if ANY/ALL page animations are currently active
    function animationsTest (callback) {
        var testAnimationInterval = setInterval(function () {
            if (! $.timers.length) { // any page animations finished
                clearInterval(testAnimationInterval);
                callback();
            }
        }, 25);
    };
    
    function updateScore (el, response){
        el.parentNode.getElementsByClassName('post-score')[0].innerHTML = response['0'].score_result;
        el.parentNode.getElementsByClassName('post-score-plus')[0].innerHTML = response['0'].vote_plus;
        el.parentNode.getElementsByClassName('post-score-minus')[0].innerHTML = response['0'].vote_minus;
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
    $('.vote-btn').on('click', function(){
        var id = this.getAttribute('id').split('-');
        var sign = (id[1] == 'upvote') ? 'plus' : 'minus';
        ajaxPingUrl('/vote/' + sign + '/' + id[0],this,updateScore)
    });
});