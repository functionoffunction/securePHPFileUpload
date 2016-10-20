# SECURE FILE UPLOAD CLASS (PHP)

Secure upload of single, multiple file by post
checks size, embedded php scripts, mime type,... etc takes security indept

## Getting Started

1. include file.php at the top of the page, for example:
2. have write and execute permissions for 'files' directory

```
require('path/fof/file');
```
Note: uses namespace

### Prerequisites

PHP >/= 5.3



### Installing

Make sure you have php 5.3 and above. for instructions : http://php.net/downloads.php

include in for script, for example:
```
require('path/fof/file');
```





## Usage
### single file upload (pic):

```
fof\Files::upload_file('profile_picture'); // profile_picture is name of input tag
```
#### : Example:
```
<form action="" method="POST" enctype="multipart/form-data">
  <p>Choose a photo</p>
  <input type="file" name="profile_picture" multiple/><br />
  <input type="submit" name="submit" value="Upload file" />
</form>

$val = fof\Files::upload_file('profile_picture'); // returns bool, true for success
$error = fof\Files::get_file_upload_err(); // gets error
```
### Multiple file upload (pic):

```
fof\Files::upload_file('profile_picture'); // profile_picture is name of input tag
```
#### : Example:
```
<form action="" method="POST" enctype="multipart/form-data">
  <p>Choose a photo</p>
  <input type="file" name="profile_picture[]" multiple/><br />
  <input type="submit" name="submit" value="Upload file" />
</form>

$val = fof\Files::upload_multiple_file('profile_picture', 3); //  returns bool, true for success // option second argument '3' limits uploads to 3
$error = fof\Files::get_mult_file_upload_err(); // gets all error as associative array ['file' => 'error']
```

### single file upload (pic):
```
<form action="" method="POST" enctype="multipart/form-data">
  <p>Choose a photo</p>
  <input type="file" name="profile_picture" multiple/><br />
  <input type="submit" name="submit" value="Upload file" />
</form>

$val = fof\Files::upload_file('profile_picture'); //  returns bool, true for success
$error = fof\Files::file_upload_error(); // gets error if failed
```
### for other file types
```
<form action="" method="POST" enctype="multipart/form-data">
  <p>Choose a photo</p>
  <input type="file" name="profile_picture" multiple/><br />
  <input type="submit" name="submit" value="Upload file" />
</form>

//eg. for json
fof\Files::$allowed_mime_types = ['text/json'];
fof\Files::$allowed_extensions = ['json'];
fof\Files::$check_is_image = true;

$val = fof\Files::upload_file('profile_picture'); //  returns bool, true for success
$error = fof\Files::file_upload_error(); // gets error if failed
```
## Contributing

Very welcomed!

## Authors

* **Oluwatimilehin Akogun** - *Initial work* - [functionOffunction](https://github.com/functionOffunction)


## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

## Acknowledgments

inspiration: file upload security tutorial by kevin Skogland
