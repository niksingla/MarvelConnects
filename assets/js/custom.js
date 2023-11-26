/* This is your custom Javascript */
jQuery(document).ready(function ( $ ) {
    var $document = $( document ),
        $elementHeader = $( '.home-header' ),
        $homeheader = $('.site-header--bb'),
        className      = 'has-scrolled';    
    $document.scroll(
        function () {   
            $elementHeader.toggleClass( className, $document.scrollTop() >= $homeheader.height() );
            if($document.scrollTop() >= $homeheader.height()) $elementHeader.show();
            else $elementHeader.hide();
        }
    );
    var vid = document.querySelector('.assemble-vid video');
    var $start_quiz_btn = $('.start-quiz-btn a')
    $start_quiz_btn.click(function(e){
        $start_quiz_btn.fadeOut()
        e.preventDefault();
        var link = e.target.href;
        e.target.innerHTML = `<span class="spinner"></span>`;
        $start_quiz_btn.fadeIn();
        console.log(link)
        vid.play();
        vid.addEventListener("ended", function() {
            window.location.href = link;
        });
    })
})