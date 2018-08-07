<?php
    set_time_limit(0);    
    require_once('tinify-php/vendor/autoload.php');

    global $keys, $keyIndex ;

	//insert your keys inside of this array;
    $keys = array("","","");

	$keyIndex = 0;
	\Tinify\setKey($keys[$GLOBALS['keyIndex']]);
	
	optimizeImages('images_orginal//', 'imags_optimized//');

	function optimizeImages( $orginal_patch, $optimized_patch)
    {
        $FILE_OPTIMIZED = scandir($optimized_patch);//scandir('imags_optimized/');
        $FILE_ORGINALS  = scandir($orginal_patch);  //scandir('images_orginal/');
        $i = 0;
        foreach ($FILE_ORGINALS as $file_org) {
            echo $file_org;
            $exist = false;
            foreach ($FILE_OPTIMIZED as $file_opt) {
                if ($file_opt == $file_org) {
                    $exist = true;
                    $i++;
                    echo ' -> exist<br>';
                }
            }
            if (!$exist) {
                $pref = explode('.', $file_org)[1];
                if ($pref == 'jpg' || $pref == 'png' || $pref == 'JPG' || $pref == 'PNG') {
                    if(optimizeImage($orginal_patch . $file_org, $optimized_patch. $file_org, null)){
                        echo ' ->changed<br>';
                    }
                    else{
                        break;
                    }
                }
                else {
                    echo ' -> Its Not Image<br>';
                }
            }
        }
    }

    function optimizeImage ($originalFilePath, $optimizedFilePath, $newWidth){
        try {
            $source = \Tinify\fromFile($originalFilePath);
            if($newWidth !== null) {
                $source = $source->resize(array(
                    "method" => "scale",
                    "width" => $newWidth
                ));
            }
            $source = $source->preserve("copyright", "creation", "location");
            $source->toFile($optimizedFilePath);
            return true;
        }
        catch(\Tinify\AccountException $e) {

            if(changeKey()){
                optimizeImage($originalFilePath, $optimizedFilePath, $newWidth);
            }
            else{
                print("The error message is: " . $e->getMessage());
                echo 'Ran out of keys';
                return false;
            }

            // Verify your API key and account limit.
        }
        catch(\Tinify\ClientException $e) {
            echo 'Check your source image and request options.' . $e->getMessage();
        }
        catch(\Tinify\ServerException $e) {
            echo 'Temporary issue with the Tinify API.'. $e->getMessage();
        }
        catch(\Tinify\ConnectionException $e) {
            echo  'A network connection error occurred.' . $e->getMessage();
        }
        catch(Exception $e) {
            echo 'Something else went wrong, unrelated to the Tinify API.' . $e->getMessage();
        }
	}

	function changeKey(){
        $GLOBALS['keyIndex']++;
	    if($GLOBALS['keyIndex'] < sizeof($GLOBALS['keys'])){
            \Tinify\setKey($GLOBALS['keys'][$GLOBALS['keyIndex']]);
            return true;
        }
        return false;
    }
?>



