<?php
/**
 * config
 *@version
 *@author Oluwatimilehin Akogun
 *@license MIT
 *@copyright
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */

//Numbers
defined('MAX_FILESIZE_ALLOWED')? NULL : define('MAX_FILESIZE_ALLOWED', 1048576); //1MB
defined('UPLOAD_C__NULL_TEMP_FILE')? NULL : define('UPLOAD_C__NULL_TEMP_FILE', 600);
defined('UPLOAD_INVALID_FILE')? NULL : define('UPLOAD_INVALID_FILE', 700);


//Directories


defined('PIC_UPLOAD_PATH_1')? NULL : define('PIC_UPLOAD_PATH_1', './files');


defined('CHMOD_ERR')? NULL : define('CHMOD_ERR', 'fof chmod failure in ');
?>
