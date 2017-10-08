$(function(){
    function updateScore (el, response){
        el.parentNode.getElementsByClassName('post-score')[0].innerHTML = response['0'].score_result;
        el.parentNode.getElementsByClassName('post-score-plus')[0].innerHTML = response['0'].vote_plus;
        el.parentNode.getElementsByClassName('post-score-minus')[0].innerHTML = response['0'].vote_minus;
    }
    
    $('.vote-btn').on('click', function(){
        var id = this.getAttribute('id').split('-');
        var sign = (id[1] == 'upvote') ? 'plus' : 'minus';
        ajaxPingUrl('/vote/' + sign + '/' + id[0],this,updateScore);
    });
});