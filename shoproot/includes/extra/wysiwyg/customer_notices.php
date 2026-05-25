<?php
// WYSIWYG editor 
    switch($type) {
		// WYSIWYG editor customer_notices textarea named customer_notices[langID]
	   case 'customer_notices':
          $editorName = 'description['.$language_id.']';
            $default_editor_height = 400;
            break;
    }
?>