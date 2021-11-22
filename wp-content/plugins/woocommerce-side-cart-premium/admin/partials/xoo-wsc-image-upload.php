<?php
	
	if(!isset($id) || !isset($value)){
		echo 'Input Id/ Value not set';
		return;
	}

?>

<a class="button-primary xoo-upload-icon">Select</a>
<input type="hidden" name="<?php echo $id; ?>" class="xoo-upload-url" value="<?php echo $value; ?>">
<a class="button xoo-remove-media">Remove</a>
<span class="xoo-upload-title"></span>
<p class="description">Supported format: JPEG,PNG </p>