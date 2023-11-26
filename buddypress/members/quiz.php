<?php

defined( 'ABSPATH' ) || exit;
$signup_quiz = get_option('selected_sign_up_quiz');
echo do_shortcode("[ays_quiz id='{$signup_quiz}']");
?>

<form action="" method="post" id="get-form">
    <input type="hidden" name='action' value='get_signup_form'>
</form>
<script type="text/javascript">
    jQuery(document).ready(function($){
        $(document).ajaxComplete(function (event, xhr, settings) {
            if (xhr.status === 200) {
                if('score' in JSON.parse(xhr.responseText)){
                    let data = JSON.parse(xhr.responseText)
                    if(data.score=='100%'){
                        setTimeout(function() {
                            $('#get-form').submit();                    
                        }, 3000);
                    }
                }
            } 
        });
        /*formelements = $("#register-page").html();
        $("#register-page > form").remove();*/
    })
</script>