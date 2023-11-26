<?php
defined( 'ABSPATH' ) || exit;
function custom_ajax_callback() {
    $quiz = $_POST['selectedQuiz'];
    $updated = update_option('selected_sign_up_quiz',$quiz);
    if($updated) {
      wp_send_json_success(array('quiz'=>$quiz));
      wp_die();
    }
    else wp_die();
  }
  add_action('wp_ajax_set_quiz_action', 'custom_ajax_callback');
  add_action('wp_ajax_nopriv_set_quiz_action', 'custom_ajax_callback');

?>