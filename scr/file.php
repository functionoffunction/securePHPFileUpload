<?php
/**
 *@package files
 *@version polymath  class 1.1 {backward compactible}
 *@author Oluwatimilehin Akogun
 *@license MIT licence
 *@copyright attribution required
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */

namespace fof;
require 'constants.php';

class Files{

	protected static $multiple_error = [];
	// Define allowed filetypes to check against during validations
	protected static $allowed_mime_types = ['image/png', 'image/gif', 'image/jpg', 'image/jpeg'];
	protected static $allowed_extensions = ['png', 'gif', 'jpg', 'jpeg'];

	protected static  $check_is_image = true;
	protected static  $check_for_php = true;
	protected static  $error = 0;
	protected static  $upload_path = PIC_UPLOAD_PATH_1;



	public static function get_mult_file_upload_err(){
		return static::$multiple_error;
	}

	public static function file_upload_error() {
		$upload_errors = array(
			// http://php.net/manual/en/features.file-upload.errors.php
			UPLOAD_ERR_OK 				=> "No errors.",
			UPLOAD_ERR_INI_SIZE  	=> "Larger than upload_max_filesize.",
		  UPLOAD_ERR_FORM_SIZE 	=> "Larger than form MAX_FILE_SIZE.",
		  UPLOAD_ERR_PARTIAL 		=> "Partial upload.",
		  UPLOAD_ERR_NO_FILE 		=> "No file.",
		  UPLOAD_ERR_NO_TMP_DIR => "No temporary directory.",
		  UPLOAD_ERR_CANT_WRITE => "Can't write to disk.",
		  UPLOAD_ERR_EXTENSION 	=> "File upload stopped by extension.",
			UPLOAD_C__NULL_TEMP_FILE => "Sorry we lost your file",
			UPLOAD_INVALID_FILE => "Invalid file type"
		);
		return $upload_errors[static::$error];
	}

	//prevent malicious file like badguys.png.php
	protected static function sanitize_fname($filename) {
		$filename = preg_replace("/([^A-Za-z0-9_\-\.]|[\.]{2})/", "", $filename);
		$filename = basename($filename);
		return $filename;
	}

	// Returns the file permissions in octal format.
	protected static function set_file_perms($file) {
		$numeric_perms = fileperms($file);
		$octal_perms = sprintf('%o', $numeric_perms);
		return substr($octal_perms, -4);
	}

	// gets the file extension of a file
	protected static function get_file_extension($file) {
		$path_parts = pathinfo($file);
		return isset($path_parts['extension'])? $path_parts['extension'] : false;
	}

	// Searches the contents of a file for a PHP embed
	protected static function has_hidden_php($file) {
		$contents = file_get_contents($file);
		$position = strpos($contents, '<?php');
		return ($position !== false);
	}

	// Runs file being uploaded through a series of validations.
	// If file passes, it is moved to a permanent upload directory
	// and its execute permissions are removed.
	public static function upload_file($field_name) {
		if(isset($_FILES[$field_name])) {

			// Sanitize the provided file name.
			$file_name = static::sanitize_fname($_FILES[$field_name]['name']);
			if (!($get_file_extension = static::get_file_extension($file_name))){
				static::$error = UPLOAD_ERR_NO_FILE;
				return false;
			}


			$file_type = $_FILES[$field_name]['type'];
			$tmp_file = $_FILES[$field_name]['tmp_name'];
			$error = $_FILES[$field_name]['error'];
			$file_size = $_FILES[$field_name]['size'];

			// Evil files like $file_name = '/etc/passwd' becomes harmless
			$file_path = static::$upload_path . '/' . $file_name;

			if($error > 0) {
				//errors caught by PHP
				static::$error = $error;
				return false;

			} elseif(!is_uploaded_file($tmp_file)) {
				static::$error = UPLOAD_C__NULL_TEMP_FILE;
				return false;

			} elseif($file_size > MAX_FILESIZE_ALLOWED) {
				// defence in depth
				static::$error = UPLOAD_ERR_INI_SIZE;
				return false;
			} elseif(!in_array($file_type, static::$allowed_mime_types)) {
				static::$error = UPLOAD_INVALID_FILE; // invalid mime_content_type
				return false;
			} elseif(!in_array($get_file_extension, static::$allowed_extensions)) {
				// Checking file extension prevents files like 'evil.jpg.php'
				static::$error = UPLOAD_INVALID_FILE;
				return false;
			} elseif(static::$check_is_image && (@getimagesize($tmp_file) === false)) { //@ will suppress warning
				// is valid image
				static::$error = UPLOAD_INVALID_FILE;
				return false;
			} elseif(static::$check_is_image && static::has_hidden_php($tmp_file)) {
				// checks if file contains embedded PHP.
				static::$error = UPLOAD_INVALID_FILE;
				return false;
			} else {
				if (file_exists($file_path)) {
					$file_num = 1;
					$basename = pathinfo($file_path)['filename'];
					$temp = str_replace($basename, $basename.'('.$file_num.')', $file_path);
					while (file_exists($temp)) {
						$file_num++;
						$temp = str_replace($basename, $basename.'('.$file_num.')', $file_path);
					}
					$file_path = $temp;
				}

				$tmp_filesize = filesize($tmp_file); // always in bytes

				if(move_uploaded_file($tmp_file, $file_path)) {
					// remove execute file permissions from the file
					if(chmod($file_path, 0644)) {
						$set_file_perms = static::set_file_perms($file_path);
					} else {
						error_log(CHMOD_ERR.__FILE__.' '.__LINE__);
					}
				}else {
					static::$error = UPLOAD_C__NULL_TEMP_FILE;
					return false;
				}
				return basename($file_path);
			}
		}else {
			static::$error = UPLOAD_ERR_NO_FILE;
			return false;
		}
	}

	public static function upload_multiple_file($field_name, $limit=''){
		$key = 0;
		if(isset($_FILES[$field_name])) {
			foreach ($_FILES[$field_name]['name'] as $name) {
				$current = $key++;
				if (!empty($limit) && $current >= $limit) break;

				// Sanitize the provided file name.
				$file_name = static::sanitize_fname($name);
				if (!($get_file_extension = static::get_file_extension($file_name))){
					static::$error = UPLOAD_ERR_NO_FILE;
					static::$multiple_error[] = [$name => static::file_upload_error()];
					break;
				}
				// Even more secure to assign a new name of your choosing.
				// Example: 'file_536d88d9021cb.png'
				// $unique_id = uniqid('file_', true);
				// $new_name = "{$unique_id}.{$get_file_extension}";

				$file_type = $_FILES[$field_name]['type'][$current];
				$tmp_file = $_FILES[$field_name]['tmp_name'][$current];
				$error = $_FILES[$field_name]['error'][$current];
				$file_size = $_FILES[$field_name]['size'][$current];

				// Prepend the base upload path to prevent hacking the path
				// Example: $file_name = '/etc/passwd' becomes harmless
				$file_path = static::$upload_path . '/' . $file_name;

				if($error > 0) {
					//errors as seen by PHP
					static::$error = $error;
					static::$multiple_error[] = [$name => static::file_upload_error()];
					break;

				} elseif(!is_uploaded_file($tmp_file)) {
					static::$error = UPLOAD_C__NULL_TEMP_FILE;
					static::$multiple_error[] = [$name => static::file_upload_error()];
					break;

				} elseif($file_size > MAX_FILESIZE_ALLOWED) {
					// defence in depth
					static::$error = UPLOAD_ERR_INI_SIZE;
					static::$multiple_error[] = [$name => static::file_upload_error()];
					break;
				} elseif(!in_array($file_type, static::$allowed_mime_types)) {
					static::$error = UPLOAD_INVALID_FILE; // invalid mime_content_type
					static::$multiple_error[] = [$name => static::file_upload_error()];
					break;
				} elseif(!in_array($get_file_extension, static::$allowed_extensions)) {
					// Checking file extension prevents files like 'evil.jpg.php'
					static::$error = UPLOAD_INVALID_FILE;
					static::$multiple_error[] = [$name => static::file_upload_error()];
					break;
				} elseif(static::$check_is_image && (@getimagesize($tmp_file) === false)) { //@ will suppress warning
					// is valid image
					static::$error = UPLOAD_INVALID_FILE;
					static::$multiple_error[] = [$name => static::file_upload_error()];
					break;
				} elseif(static::$check_is_image && static::has_hidden_php($tmp_file)) {
					// checks if file contains embedded PHP.
					static::$error = UPLOAD_INVALID_FILE;
					static::$multiple_error[] = [$name => static::file_upload_error()];
					break;
				} else {
					if (file_exists($file_path)) {
						$file_num = 1;
						$basename = pathinfo($file_path)['filename'];
						$temp = str_replace($basename, $basename.'('.$file_num.')', $file_path);
						while (file_exists($temp)) {
							$file_num++;
							$temp = str_replace($basename, $basename.'('.$file_num.')', $file_path);
						}
						$file_path = $temp;
					}

					$tmp_filesize = filesize($tmp_file); // always in bytes

					if(move_uploaded_file($tmp_file, $file_path)) {
						// remove execute file permissions from the file
						if(chmod($file_path, 0644)) {
							$set_file_perms = static::set_file_perms($file_path);
						} else {
							// log error
						}
					}else {
						static::$error = UPLOAD_C__NULL_TEMP_FILE;
						static::$multiple_error[] = [$name => static::file_upload_error()];
						break;
					}
					// return basename($file_path);
				}
			}

		}else {
			static::$error = UPLOAD_ERR_NO_FILE;
			static::$multiple_error[] = ['' => static::file_upload_error()];
		}
		return true;
	}
}

?>
