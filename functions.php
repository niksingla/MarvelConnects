<?php
/**
 * @package BuddyBoss Child
 * The parent theme functions are located at /buddyboss-theme/inc/theme/functions.php
 * Add your own functions at the bottom of this file.
 */


/****************************** THEME SETUP ******************************/

/**
 * Sets up theme for translation
 *
 * @since BuddyBoss Child 1.0.0
 */
function buddyboss_theme_child_languages()
{
  /**
   * Makes child theme available for translation.
   * Translations can be added into the /languages/ directory.
   */

  // Translate text from the PARENT theme.
  load_theme_textdomain( 'buddyboss-theme', get_stylesheet_directory() . '/languages' );

  // Translate text from the CHILD theme only.
  // Change 'buddyboss-theme' instances in all child theme files to 'buddyboss-theme-child'.
  // load_theme_textdomain( 'buddyboss-theme-child', get_stylesheet_directory() . '/languages' );

}
add_action( 'after_setup_theme', 'buddyboss_theme_child_languages' );

/**
 * Enqueues scripts and styles for child theme front-end.
 *
 * @since Boss Child Theme  1.0.0
 */
function buddyboss_theme_child_scripts_styles()
{
  /**
   * Scripts and Styles loaded by the parent theme can be unloaded if needed
   * using wp_deregister_script or wp_deregister_style.
   *
   * See the WordPress Codex for more information about those functions:
   * http://codex.wordpress.org/Function_Reference/wp_deregister_script
   * http://codex.wordpress.org/Function_Reference/wp_deregister_style
   **/

  // Styles
  wp_enqueue_style( 'buddyboss-child-css', get_stylesheet_directory_uri().'/assets/css/custom.css', '', '1.0.0' );
  wp_register_style( 'color-buddy', get_stylesheet_directory_uri().'/assets/css/color.css', array(), true, 'all',true );

  // Javascript
  wp_enqueue_script( 'buddyboss-child-js', get_stylesheet_directory_uri().'/assets/js/custom.js', '', '1.0.0' );
}
add_action( 'wp_enqueue_scripts', 'buddyboss_theme_child_scripts_styles', 9999 );


/****************************** CUSTOM FUNCTIONS ******************************/

// Custom functions by NS

require_once('ajax.php');

/**Display Quiz Before Registeration */
function custom_register_page(){
  require_once('quiz.php');
}
//add_action( 'bp_before_register_page', 'custom_register_page' );

//Custom Quiz Settings
// Function to create the custom quiz menu
function create_custom_quiz_menu() {
  add_menu_page(
      'Quiz Settings',        // Page title
      'Custom Quiz',        // Menu title
      'manage_options',     // Capability (who can access)
      'custom-quiz',        // Menu slug
      'custom_quiz_page',   // Callback function to display content
      'dashicons-list-view', // Icon URL or Dashicons class
      75                    // Menu position
  );
}

// Callback function to display custom quiz page content
function custom_quiz_page() {
  include 'admin/custom-quiz.php';
}
add_action('admin_menu', 'create_custom_quiz_menu');


function custom_menu_item_url($atts, $item, $args) {
  
  if ($item->ID == 232 && is_user_logged_in()) { 
      $atts['href'] = '/news-feed/';
  }
  return $atts;
}

add_filter('nav_menu_link_attributes', 'custom_menu_item_url', 10, 3);

function redirect_home_url_for_logged_in_users() {
    if (is_user_logged_in() && is_front_page() && !current_user_can('editor') && !current_user_can('administrator')) {
        wp_redirect(site_url().'/news-feed',301);
        exit;
    }
}

add_action('template_redirect', 'redirect_home_url_for_logged_in_users');


function add_style_in_footer(){
  wp_enqueue_style('color-buddy');
}
add_action('wp_footer','add_style_in_footer');
function import_btn(){
	?>
	<button class="import-data">Import</button>
	<script>
		jQuery(document).ready(function($){
			$('.import-data').click(function(){
				console.log('importing...')
				$.ajax({
					type:'post',
					url:"<?= admin_url( 'admin-ajax.php' ) ?>",
					data:{action:"import_posts_from_json"},

				}).done(function(data){
					console.log(data)
				})
			})
		})
	</script>
	<?php
}
//add_action('wp_footer','import_btn');

function import_posts_from_json() {
  $json_file_path = get_stylesheet_directory() . '/file.json';
  if (file_exists($json_file_path)) {
      $json_data = file_get_contents($json_file_path);
      $dataall = json_decode($json_data);
      global $wpdb;
      $table = $wpdb->prefix.'aysquiz_questions';
      $data = $dataall; //array_slice($dataall,535);
      $count = 0;      
      if ($data) {
          foreach ($data as $index => $item) {
            $copyArray = $data;
            $excludeIndex = $index;
            unset($copyArray[$excludeIndex]);
            $randomKeys = array_rand($copyArray, 2);
            $randomKeys = is_array($randomKeys) ? $randomKeys : [$randomKeys];
            $randomAnswers = [];
            foreach ($randomKeys as $randomKey) {
              $randomAnswers[] = $data[$randomKey]->answer;
            }
            
            array_push($randomAnswers,$item->answer);
            shuffle($randomAnswers);
            $i = array_search($item->answer, $randomAnswers);
            $add = add_questions($item->question,$i, $randomAnswers);
            $count++;
          }

        echo $count.' questions added !!';
        wp_die();
      } else {
          echo 'Failed to parse JSON data.';
          wp_die();
      }
  } else {
      echo 'JSON file not found at ' . $json_file_path;
    wp_die();
  }
}
add_action('wp_ajax_import_posts_from_json','import_posts_from_json');
add_action('wp_ajax_nopriv_import_posts_from_json','import_posts_from_json');

function add_questions($question,$correct_answer, $answer_vals){
  global $wpdb;
  $questions_table = $wpdb->prefix . "aysquiz_questions";
  $answers_table = $wpdb->prefix . "aysquiz_answers";
  
      
  $id = absint(0);

  // Question title ( Banner )
  $question_title     = '';
  
  $question_hint      = '';
  $question_image     = NULL;
  $category_id        = 1;
  $published          = 1;
  $type               = 'radio';
  $correct_answers    = [$correct_answer+1];
  
  $answer_values      =  $answer_vals; //array($correct_answer, 'Answer 2', 'Answer 3');
  //array_unshift($answer_values,$correct_answer);
  $answer_placeholders = array();
  $wrong_answer_text  = '';
  $right_answer_text  = '';
  $explanation        = '';
  $not_influence_to_score = 'off';

  $quest_create_date  = '0000-00-00 00:00:00';
  $author = '{"id":1,"name":"MarvelC"}';
  $author = json_decode($author, true);
  
  // Use HTML for answers
  $use_html = 'off';

  // Maximum length of a text field
  $enable_question_text_max_length = 'off';

  $question_text_max_length = '';

  // Limit by
  $question_limit_text_type = 'characters';

  // Show the counter-message
  $question_enable_text_message = 'off';


  // Maximum length of a text field
  $enable_question_number_max_length = 'off';

  // Length
  $question_number_max_length = '';

  // Hide question text on the front-end
  $quiz_hide_question_text = 'off';

  // Enable maximum selection number
  $enable_max_selection_number = 'off';

  // Max value
  $max_selection_number = '';

  // Note text
  $quiz_question_note_message = '';

  // Enable case sensitive text
  $enable_case_sensitive_text = 'off';

  // Minimum length of a text field
  $enable_question_number_min_length = 'off';

  // Length
  $question_number_min_length = '';

  // Show error message
  $enable_question_number_error_message = 'off';

  // Message
  $question_number_error_message = '';

  // Enable minimum selection number
  $enable_min_selection_number = 'off';

  // Min value
  $min_selection_number = '';

  // Disable strip slashes for answers
  $quiz_disable_answer_stripslashes = 'off';

  $options = array(
      'author'                                => json_encode($author),
      'use_html'                              => $use_html,
      'enable_question_text_max_length'       => $enable_question_text_max_length,
      'question_text_max_length'              => $question_text_max_length,
      'question_limit_text_type'              => $question_limit_text_type,
      'question_enable_text_message'          => $question_enable_text_message,
      'enable_question_number_max_length'     => $enable_question_number_max_length,
      'question_number_max_length'            => $question_number_max_length,
      'quiz_hide_question_text'               => $quiz_hide_question_text,
      'enable_max_selection_number'           => $enable_max_selection_number,
      'max_selection_number'                  => $max_selection_number,
      'quiz_question_note_message'            => $quiz_question_note_message,
      'enable_case_sensitive_text'            => $enable_case_sensitive_text,
      'enable_question_number_min_length'     => $enable_question_number_min_length,
      'question_number_min_length'            => $question_number_min_length,
      'enable_question_number_error_message'  => $enable_question_number_error_message,
      'question_number_error_message'         => $question_number_error_message,
      'enable_min_selection_number'           => $enable_min_selection_number,
      'min_selection_number'                  => $min_selection_number,
      'quiz_disable_answer_stripslashes'      => $quiz_disable_answer_stripslashes,
  );

  $text_types = array('text', 'short_text', 'number');
  if($id == 0) {
      $question_result = $wpdb->insert(
          $questions_table,
          array(
              'category_id'               => $category_id,
              'question'                  => $question,
              'question_title'            => $question_title,
              'question_image'            => $question_image,
              'type'                      => $type,
              'published'                 => $published,
              'wrong_answer_text'         => $wrong_answer_text,
              'right_answer_text'         => $right_answer_text,
              'question_hint'             => $question_hint,
              'explanation'               => $explanation,
              'create_date'               => $quest_create_date,
              'not_influence_to_score'    => $not_influence_to_score,
              'options'                   => json_encode($options),
          ),
          array(
              '%d', // category_id
              '%s', // question
              '%s', // question_title
              '%s', // question_image
              '%s', // type
              '%d', // published
              '%s', // wrong_answer_text
              '%s', // right_answer_text
              '%s', // question_hint
              '%s', // explanation
              '%s', // create_date
              '%s', // not_influence_to_score
              '%s', // options
          )
      );

      $answers_results = array();
      $question_id = $wpdb->insert_id;
      $flag = true;
      foreach ($answer_values as $index => $answer_value) {
          if ( $quiz_disable_answer_stripslashes == 'off' ) {
              $answer_value = stripslashes($answer_value);
          }
          if(in_array( $type, $text_types )){
              $correct = 1;
              $answer_value = htmlspecialchars_decode($answer_value, ENT_QUOTES );
          }else{
              $correct = (in_array(($index + 1), $correct_answers)) ? 1 : 0;
          }
          if (!in_array( $type, $text_types ) && trim($answer_value) == '') {
              continue;
          }
          $placeholder = '';
          if(isset($answer_placeholders[$index])){
              $placeholder = $answer_placeholders[$index];
          }
          $answers_results[] = $wpdb->insert(
              $answers_table,
              array(
                  'question_id'   => $question_id,
                  'answer'        => (trim($answer_value)),
                  'correct'       => $correct,
                  'ordering'      => ($index + 1),
                  'placeholder'   => $placeholder
              ),
              array(
                  '%d', // question_id
                  '%s', // answer
                  '%d', // correct
                  '%d', // ordering
                  '%s'  // placeholder
              )
          );
      }

      foreach ($answers_results as $answers_result) {
          if ($answers_result >= 0) {
              $flag = true;
          } else {
              $flag = false;
              break;
          }
      }
      $message = 'created';
      return $message;
  }

}

?>
