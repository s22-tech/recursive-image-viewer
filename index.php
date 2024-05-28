<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
  <head>
    <title>All The Things</title>
    <script src="jquery-1.11.3.min.js"></script>
    <script src="jquery.lazyload.js"></script>
    <style type="text/css">
      .container {
        display: flex;
        flex-wrap: wrap;
      }
      img {
        max-width: 300px;
      }
      .photo {
        max-width: 300px;
        margin: 12px;
        border: 1px solid #ccc;
        padding: 25px;
        font-size: 14px;
      }
      a{
        color:#4183c4;
        text-decoration:none;
        outline:none;
      }
      a:hover{
        text-decoration:underline;
      }
      a:active{
        outline:none;
      }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
	</head>
	<body>
    <div class="container">
<?php
	$output = '';
	if (!empty($_GET['dir'])) {
		$dir = $_GET['dir'];
	  // Add trailing slash if missing.
		if (!str_ends_with($dir, '/')) {
			$dir .= '/';
		}

		$d = dir($dir) 
			or die("get_images_recursive 1: Failed opening directory $dir for reading");
		$output .= '<ul>';
		while (($item = $d->read()) !== false) {
		  // Skip hidden files.
			if ($item[0] == '.') {
				continue;
			}
			if (is_dir(__DIR__ .'/'. $dir . $item)) {
				$output .= '<li><a href="?dir='.$dir . $item .'"> View images in /'. $dir . $item .'</a></li>';
			}
		}
		$output .= '</ul>';

	  // Fetch image details.
		$images = get_images_recursive($dir);

		$ratio = 0.3;
		foreach ($images as $img) {
			$width  = $img['size'][0] * $ratio;
			$height = $img['size'][1] * $ratio;

			// If you want to set the width and height in code, delete the
			// img class above in the CSS and add the following line to the image tag:
			// width=\"{$width}\" height=\"{$height}\"

			$output .= '<div class="photo">';
			$output .= "<a target=\"_blank\" href=\"{$img['path']}\">";
			$output .= '<img class="lazy" data-original="'.$img['path'].'" alt=""></a><br>' . PHP_EOL;
			
		  // Display image file name as link.
			$output .= '<a target="_blank" href="'.$img['path'].'">' . basename($img['file']) . '</a><br>' . PHP_EOL;
			
		  // Display image dimenstions.
			$output .= "({$img['size'][0]} x {$img['size'][1]} pixels)<br>" . PHP_EOL;
			
		  // Display mime_type.
			$output .= $img['size']['mime'];
			$output .= '</div>' . PHP_EOL;
		}
	}
  // Link to the main directories.
	else {
		$d = dir(__DIR__ . '/') 
			or die('get_images_recursive 2: Failed opening directory ' . __DIR__ . ' for reading');
		$output .= '<ul>';
		while (($item = $d->read()) !== false) {
		  // Skip hidden files.
			if ($item[0] == '.') continue;

			if (is_dir(__DIR__ .'/'. $item)) {
				$output .= '<li><a href="?dir='. $item .'"> View images from the "'. $item .'" directory</a></li>';
			}
		}
		$output .= '</ul>';
	}

	$output = str_replace('<ul></ul>', '', $output);
	if (!empty($output)) {
		echo $output;
}
?>
    </div>
<script charset="utf-8">
	$(function () {
		$('img.lazy').lazyload();
	});
</script>
  </body>
</html>

<?php

	/**
	 * https://github.com/tomgould/recursive-image-viewer
	 *
	 * Recursively scans a directory and returns an array of the images within.
	 *
	 * This can be used as a stand alone application for viewing images or if you
	 * are a web developer you can put this in the root of your site and easily
	 * have a way to search all the images on your site from one page.
	 *
	 * @global array $types
	 *     The image types to look for
	 * @param string $dir
	 *     The directory to scan
	 * @param array $images
	 *     An array to hold the images
	 *
	 * @return array
	 *     The Images
	 */
	function get_images_recursive($dir, $images = []) {
		global $types;

	  // Filetypes to display.
		$types = ['jpeg', 'jpg', 'gif', 'png'];

	  // Add trailing slash if missing.
		if (!str_ends_with($dir, '/')) {
			$dir .= '/';
		}

		$d = dir($dir) 
			or die("get_images_recursive 1: Failed opening directory $dir for reading");

		while (($item = $d->read()) !== false) {
		  // Skip hidden files.
			if ($item[0] == '.') continue;

			if (is_dir(__DIR__ .'/'. $dir . $item) && isset($_GET['all'])) {
				$this_dir = get_images_recursive($dir . $item);
				$images   = array_merge($images, $this_dir);
			}

		  // Check for image files.
			$path     = $dir . $item;
			$mimetype = pathinfo($path, PATHINFO_EXTENSION);

			if (in_array(strtolower($mimetype), $types)) {
				$images[] = [
					'path' => $path,
					'file' => '/' . $dir . $item,
					'size' => getimagesize($dir . $item)
				];
			}
		}
		$d->close();

		return $images;
	}
?>
