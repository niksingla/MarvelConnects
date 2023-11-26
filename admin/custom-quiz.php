<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap">
    <?php
    global $wpdb;
    $prefix = $wpdb->prefix;
    $table = $prefix.'aysquiz_quizes';
    $query = "SELECT * FROM {$table}";
    $results = $wpdb->get_results($query);
    if (!empty($results)) {
        ?>
        <h2>Select the sign up quiz</h2>
        <form action="." method="post" class="quiz-select-form">
            <label for="selectQuiz">Choose an option:</label>
            <select id="selectQuiz" name="selectQuiz">
                <?php 
                $selected_quiz = get_option('selected_sign_up_quiz');
                foreach ($results as $row) {?>
                   <option value="<?= $row->id;?>" <?= $selected_quiz==$row->id ? 'selected':''; ?>><?= $row->title;?></option>
                <?php } ?>
            </select>
            <br><br>
            <input class="page-title-action" type="submit" value="Submit">
        </form>      
        <script type="text/javascript">
            jQuery('.quiz-select-form [type="submit"]').click(function(e){
                e.preventDefault();
                selectedQuiz = jQuery('#selectQuiz').val();
                data = {
                    action:'set_quiz_action',
                    selectedQuiz: selectedQuiz
                }
                jQuery.post(ajaxurl, data, function(response) {
                    try {
                        if (response.success) {
                            alert('Successful!');
                        } else {
                            alert('Error!');
                        }
                    } catch (error) {
                        alert('Something Went Wrong');
                    }
                });
            })
        </script>
        <?php
    } else {
        echo 'No record found.';
    }
    ?>
</div>

<?php 


?>