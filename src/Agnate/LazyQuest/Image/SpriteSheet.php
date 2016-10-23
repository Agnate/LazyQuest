<?php

namespace Agnate\LazyQuest\Image;

use \Agnate\LazyQuest\App;

class SpriteSheet {

  // const DEBUG_URL = '/debug/sprites.png';
  // const DEBUG_LINED_FOLDER = '/debug/lined';
  // const DEFAULT_SPRITESHEET_URL = '/icons/sprites.png';
  // const DEFAULT_ICON_URL = '/icons/rough';
  // const DEFAULT_LINED_URL = '/icons/lined';
  // const FILENAME_LIST = '/icons/sprites.json';

  
  /**
   * Generate the final spritesheet to use for Map image generation.
   * @param boolean $debug If debug is TRUE, it will output to the GAME_SPRITESHEET_DEBUG_DIR path defined in config.
   * @return ??? Returns the spritesheet information.
   */
  public static function generate ($debug = FALSE) {
    // Get all the icons and merge into a single list.
    $all = static::all();
    $json = [
      'url' => '',
      'tiles' => array(),
    ];

    // Count the tiles.
    $num_tiles = 0;
    foreach ($all as $tile_group) {
      foreach ($tile_group as $tiles) {
        $num_tiles += count($tiles);
      }
    }

    // Count how many tiles we have and separate into a useful list.
    $tile_size = 32;
    $resizer = 0.5;
    $rtile_size = floor($tile_size * $resizer);
    $num_cols = 20;
    $num_rows = ceil($num_tiles / $num_cols);
    $width = $num_cols * $tile_size;
    $height = $num_rows * $tile_size;

    // Create the transparent initial image.
    $image = imagecreatetruecolor($width, $height);
    imagealphablending($image, TRUE);
    imagesavealpha($image, TRUE);
    $trans_colour = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $trans_colour);

    // Create the sprite sheet.
    $row = 0;
    $col = 0;
    foreach ($all as $type => $tile_group) {
      if (!isset($json['tiles'][$type])) $json['tiles'][$type] = array();
      $count = 0;

      foreach ($tile_group as $tiles) {
        $group = array();

        // Determine orientation beforehand.
        $tile_count = count($tiles);
        $orientation = 'n';
        if ($tile_count == 2) {
          if ($tiles[0]['x'] != $tiles[1]['x']) $orientation = 'h';
          else $orientation = 'v';
          // else if ($tiles[0]['y'] != $tiles[1]['y']) $orientation = 'v';
        }

        foreach ($tiles as $tile) {
          $x = $col * $tile['width'];
          $y = $row * $tile['height'];
          imagecopy($image, $tile['image'], $x, $y, $tile['x'], $tile['y'], $tile['width'], $tile['height']);

          // Store new coordinates.
          $group[] = array(
            'x' => $col * $rtile_size,
            'y' => $row * $rtile_size,
            'orientation' => $orientation,
            'width' => floor($tile['width'] * $resizer),
            'height' => floor($tile['height'] * $resizer),
          );

          $col++;
          if ($col >= $num_cols) {
            $col = 0;
            $row++;
          }
        }

        // Set a key which is the groupid for the icon.
        $json['tiles'][$type][''.$type.$count] = $group;
        $count++;
      }
    }

    // Resize the spritesheet to shrink the overall map size.
    $rwidth = floor($width * $resizer);
    $rheight = floor($height * $resizer);
    $resized = imagecreatetruecolor($rwidth, $rheight);
    imagealphablending($resized, TRUE);
    imagesavealpha($resized, TRUE);
    $rtrans_colour = imagecolorallocatealpha($resized, 0, 0, 0, 127);
    imagefill($resized, 0, 0, $rtrans_colour);
    imagecopyresampled($resized, $image, 0, 0, 0, 0, $rwidth, $rheight, $width, $height);

    // Output the resized image.
    $image_url = GAME_SPRITESHEET_FILENAME;
    $file_path = App::getPath($image_url);
    imagepng($resized, $file_path);
    $urls = ['url' => $image_url];

    // Save out JSON data for sprites.
    $json['url'] = $image_url;
    static::saveSpritesList($json);

    // If we're debugging, also output to the public area.
    if ($debug) {
      $debug_file_path = GAME_SPRITESHEET_DEBUG_FILENAME;
      $debug_url = App::getPublicUrl($debug_file_path);
      imagepng($resized, $debug_file_path);
      $urls['debug'] = $debug_url;
    }

    // Create the object.
    return $urls;
  }

  /**
   * Take a sprite sheet and add a grid outline to it. This allows us to more easily
   * identify the row and column to create new icons.
   */
  public static function addGridToSheet ($local_url, $debug = FALSE) {
    // Create a list of all currently-used tiles.
    $all = static::getRawSpriteCoordinates();
    $all_tiles = array();
    foreach ($all as $sheet => &$sheet_set) {
      if ('/' . $sheet_set['image'] != $local_url) continue;
      foreach ($sheet_set['tiles'] as $type => &$tile_group) {
        foreach ($tile_group as &$tiles) {
          foreach ($tiles as &$tile) {
            $all_tiles[$tile['y'] + 1][$tile['x'] + 1] = TRUE;
          }
        }
      }
    }

    // Get the image information.
    $info = getimagesize(static::url($local_url));
    $tile_size = 32;
    $width = $info[0] + $tile_size;
    $height = $info[1] + $tile_size;

    // Figure out number of rows and columns.
    $num_rows = ceil($height / $tile_size);
    $num_cols = ceil($width / $tile_size);

    // Create the transparent initial image.
    $image = imagecreatetruecolor($width + 1, $height + 1);
    imagealphablending($image, TRUE);
    imagesavealpha($image, TRUE);
    $trans_colour = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $trans_colour);

    // Add colours.
    $gray = imagecolorallocate($image, 80, 80, 80);
    $pink = imagecolorallocatealpha($image, 223, 4, 101, 75);

    // Copy over the image we grabbed.
    $original_image = static::png($local_url);
    imagecopy($image, $original_image, $tile_size, $tile_size, 0, 0, $width, $height);

    // Add grid lines to make life easier.
    for ($r = 1; $r <= $num_rows; $r++) {
      $y = $r * $tile_size;

      // Add indicator to cell that it's been used.
      for ($c = 1; $c <= $num_cols; $c++) {
        if (!isset($all_tiles[$r]) || !isset($all_tiles[$r][$c]) || $all_tiles[$r][$c] != TRUE) continue;
        $x = $c * $tile_size;
        imagefilledrectangle($image, $x, $y, $x + $tile_size, $y + $tile_size, $pink);
      }
      
      imageline($image, ($tile_size - 6), $y, $width, $y, $gray);
      // Numbers
      if ($r == $num_rows) continue;
      $row = $r - 1;
      $x = ($row < 10) ? 13 : 4;
      imagettftext($image, 12, 0, $x, $y + $tile_size - 8, $gray, App::getPath(GAME_MAP_FONT_PATH), $row);
    }

    for ($c = 1; $c <= $num_cols; $c++) {
      $x = $c * $tile_size;
      imageline($image, $x, ($tile_size - 6), $x, $height, $gray);
      // Numbers
      if ($c == $num_cols) continue;
      $col = $c - 1;
      $x = ($col < 10) ? $x + 12 : $x + 6;
      imagettftext($image, 12, 0, $x, $tile_size - 10, $gray, App::getPath(GAME_MAP_FONT_PATH), $col);
    }

    // Save it back out.
    $image_url = static::linedUrl($local_url, FALSE);
    $file_path = static::linedUrl($local_url);
    imagepng($image, $file_path);
    $urls = array('url' => $image_url);

    // If we're debugging, also output to the public area.
    if ($debug) {
      $debug_url = App::getPath(GAME_SPRITESHEET_DEBUG_RAW_DIR, TRUE) . $local_url;
      $debug_file_path = App::getPublicUrl($debug_url);
      imagepng($image, $debug_file_path);
      $urls['debug'] = $debug_url;
    }

    return $urls;
  }

  /**
   * Load the coordinates from the JSON file. This should only be used when raw data is needed. Use the static::all() function
   * to pull a properly curated coordinate set.
   * @return Array Returns an associative Array of all of the raw sprite coordinates to generate a spritesheet from.
   */
  protected static function getRawSpriteCoordinates () {
    $file_name = App::getPath(GAME_SPRITESHEET_RAW_JSON);
    $sprite_locations = file_get_contents($file_name);
    return json_decode($sprite_locations, TRUE);
  }

  /**
   * Get the list of all sprite coordinates on the raw images. This is used to generate the final spritesheet
   * by extracting all of the sprites from raw files and putting into a single spritesheet. This function
   * provides those coordinates.
   * @return Array Returns an associative Array of all of the raw sprite coordinates to generate a spritesheet from.
   */
  protected static function all () {
    $tile_size = 32;

    $sprite_locations = static::getRawSpriteCoordinates();

    // Set width and height if they're not set.
    $all = array();
    foreach ($sprite_locations as $sprite_location => &$list) {
      // Do extra math and merge into single list.
      foreach ($list['tiles'] as $type => &$tile_group) {
        if (!isset($all[$type])) $all[$type] = array();

        // Load up the image sprite that contains these tiles.
        $list['loaded_image'] = static::png('/' . $list['image']);

        foreach ($tile_group as &$tiles) {
          $group = array();
          
          foreach ($tiles as &$tile) {
            $tile['col'] = $tile['x'];
            $tile['row'] = $tile['y'];
            $tile['x'] = $tile['x'] * $tile_size;
            $tile['y'] = $tile['y'] * $tile_size;
            if (!isset($tile['width'])) $tile['width'] = $tile_size;
            if (!isset($tile['height'])) $tile['height'] = $tile_size;
            $tile['image'] = $list['loaded_image'];
            $tile['origin'] = $sprite_location;
            $tile['type'] = $type;
            $group[] = $tile;
          }

          $all[$type][] = $group;
        }
      }
    }

    return $all;
  }

  protected static function png ($local_url) {
    return imagecreatefrompng(static::url($local_url));
  }

  protected static function jpg ($local_url) {
    return imagecreatefromjpeg(static::url($local_url));
  }

  protected static function url ($end) {
    return App::getPath(GAME_SPRITESHEET_RAW_DIR) . $end;
  }
  
  protected static function linedUrl ($end) {
    return App::getPath(GAME_SPRITESHEET_ALTERED_DIR) . $end;
  }

  /**
   * Load up the list of sprites that are still available.
   */
  public static function loadSpritesList () {
    $file_name = App::getPath(GAME_SPRITESHEET_JSON);
    $json_string = file_get_contents($file_name);
    return json_decode($json_string, TRUE);
  }

  /**
   * $data -> An array that can be properly encoded using PHP's json_encode function.
   */
  protected static function saveSpritesList ($data) {
    // Write out the JSON file to store the info.
    $fp = fopen(App::getPath(GAME_SPRITESHEET_JSON), 'w');
    fwrite($fp, json_encode($data));
    fclose($fp);
  }

}