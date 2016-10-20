<?php
	require_once('scr/file.php');
?>

<html>
	<head>

	</head>
	<body>
		<form action="" method="POST" enctype="multipart/form-data">
			<p>Choose a photo for your profile picture:</p>
			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_FILESIZE_ALLOWED; ?>" />
			<input type="file" name="profile_picture[]" multiple/><br />
			<input type="submit" name="submit" value="Upload file" />
		</form>

<?php

if (isset($_POST['submit'])) {
	$val = fof\Files::upload_multiple_file('profile_picture');
	$error = fof\Files::get_mult_file_upload_err();

	if (is_array($error) && (count($error) > 0)) {
		foreach ($error as $filename) {
		print_r($error);
		}
	}
}


?>
	</body>
</html>
