<?php

namespace Agnate\LazyQuest\Image;

use \Agnate\LazyQuest\App;
use \Agnate\LazyQuest\EntityBasic;
use \Agnate\LazyQuest\Location;
use \Agnate\LazyQuest\Map;

class MapImage extends EntityBasic {

  public $url;
  public $map;
  public $times;


  /**
   * Construct the entity and set data inside.
   * @param $data Array of keyed values that are dynamically saved to the Entity if declared in the class.
   */
  function __construct($data = array()) {
    // Assign data to instance properties.
    parent::__construct($data);

    // Add some defaults.
    if (empty($this->url)) $this->url = GAME_MAP_IMAGE_PATH;
    if (empty($this->times)) $this->times = array();
  }


  /**
     ______________  ________________
    / ___/_  __/   |/_  __/  _/ ____/
    \__ \ / / / /| | / /  / // /
   ___/ // / / ___ |/ / _/ // /___
  /____//_/ /_/  |_/_/ /___/\____/

  */

  /**
   * Create the map's image file based on Map instance.
   * @param Map $map Instance of Map to generate an image for.
   * @return MapImage Returns an instance of MapImage containing the image information for the map.
   */
  public static function generateImage ($map) {
    // Calculate time to start.
    $times = array();
    $times['start'] = App::microtimeFloat();
    $times['iteration'] = $times['start'];

    // Get all locations for this map.
    $locations = $map->getLocations();
    $loc_coords = array();

    // Figure out the row and col info.
    $info = [
      'row_lo' => Map::CAPITAL_START_ROW - Map::MIN_ROWS,
      'row_hi' => Map::CAPITAL_START_ROW + Map::MIN_ROWS,
      'col_lo' => Map::CAPITAL_START_COL - Map::MIN_COLS,
      'col_hi' => Map::CAPITAL_START_COL + Map::MIN_COLS,
    ];

    foreach ($locations as $location) {
      if ($location->row > $info['row_hi']) $info['row_hi'] = $location->row;
      if ($location->row < $info['row_lo']) $info['row_lo'] = $location->row;
      if ($location->col > $info['col_hi']) $info['col_hi'] = $location->col;
      if ($location->col < $info['col_lo']) $info['col_lo'] = $location->col;
      // Save the location to coordinate map.
      if (!isset($loc_coords[$location->row])) $loc_coords[$location->row] = array();
      $loc_coords[$location->row][$location->col] = $location;
    }

    // Fill in the blanks in the loc_coords.
    for ($r = $info['row_lo']; $r <= $info['row_hi']; $r++) {
      for ($c = $info['col_lo']; $c <= $info['col_hi']; $c++) {
        if (!isset($loc_coords[$r])) $loc_coords[$r] = array();
        if (!isset($loc_coords[$r][$c])) $loc_coords[$r][$c] = TRUE;
      }
    }

    // Time to massage locations.
    $times['massage_locations'] = App::microtimeFloat() - $times['iteration'];
    $times['iteration'] = App::microtimeFloat();

    // Create base image with extra row and col for letters and numbers.
    $num_rows = ($info['row_hi'] - $info['row_lo'] + 1) + 1;
    $num_cols = ($info['col_hi'] - $info['col_lo'] + 1) + 1;

    $icon_size = 16;
    $cell_size = $icon_size * 2;
    $width = $num_cols * $cell_size;
    $height = $num_rows * $cell_size;

    // Create the initial image.
    $image = imagecreatetruecolor($width+1, $height+1);

    // Set some colours.
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    $gray = imagecolorallocate($image, 80, 80, 80);

    // Fill the edges with white.
    imagefilledrectangle($image, 0, 0, $width+1, $cell_size, $white);
    imagefilledrectangle($image, 0, 0, $cell_size, $height+1, $white);
    
    // Load the spritesheet.
    $sheet = SpriteSheet::loadSpritesList();
    $spritesheet = imagecreatefrompng(App::getPath($sheet['url']));

    // Time to prep base.
    $times['prep_base'] = App::microtimeFloat() - $times['iteration'];
    $times['iteration'] = App::microtimeFloat();

    // Generate the tiles for locations.
    foreach ($loc_coords as $row => $cols) {
      foreach ($cols as $col => $location) {
        $x = ($col - $info['col_lo'] + 1) * $cell_size;
        $y = ($row - $info['row_lo'] + 1) * $cell_size;

        // If this location is revealed or needs lite fog of war, generated a grass time.
        if ($location !== TRUE && ($location->revealed || ($location->revealed == FALSE && $location->open))) {
          static::createRandomCells($image, $icon_size, $spritesheet, reset($sheet['tiles']['grass']), $x, $y);
        }

        // Lite fog of war.
        if ($location !== TRUE && $location->revealed == FALSE && $location->open) {
          static::createRandomCells($image, $icon_size, $spritesheet, reset($sheet['tiles']['fog']), $x, $y, 60);
        }
        // Fancy icon.
        else if ($location !== TRUE && $location->revealed && $location->type != Location::TYPE_EMPTY) {
          $icon = static::generalizeIcon($location->getMapIcon());
          // Check if we have an icon for this.
          if (isset($sheet['tiles'][$icon])) {
            // If there's never been an icon generated, pick one and save it.
            if (empty($location->map_icon)) {
              $location->map_icon = array_rand($sheet['tiles'][$icon]);
              $location->save();
            }
            // Get the tile we're rendering out.
            $fancy_tiles = $sheet['tiles'][$icon][$location->map_icon];
            static::createRandomCells($image, $icon_size, $spritesheet, $fancy_tiles, $x, $y, 100, FALSE);
          }
          else {
            $fancy_tiles = reset($sheet['tiles']['unknown']);
            static::createRandomCells($image, $icon_size, $spritesheet, $fancy_tiles, $x, $y);
          }
        }
      }
    }

    // Time to generate tiles.
    $times['gen_tiles'] = App::microtimeFloat() - $times['iteration'];
    $times['iteration'] = App::microtimeFloat();

    // Draw grid lines and letters/numbers.
    for ($r = 1; $r <= $num_rows; $r++) {
      $y = $r * $cell_size;
      imageline($image, ($cell_size / 4) * 3, $y, $width, $y, $gray);
      // Numbers
      $row_num = $info['row_lo'] + $r - 1;
      $x = ($row_num < 10) ? 20 : (($row_num < 100) ? 11 : 1);
      imagettftext($image, 13, 0, $x, $y + $cell_size - 8, $black, App::getPath(GAME_MAP_FONT_PATH), $row_num);
    }

    for ($c = 1; $c <= $num_cols; $c++) {
      $x = $c * $cell_size;
      imageline($image, $x, ($cell_size / 4) * 3, $x, $height, $gray);
      // Letters
      $col_num = $info['col_lo'] + $c - 1;
      imagettftext($image, 15, 0, $x + 5, $cell_size - 5, $black, App::getPath(GAME_MAP_FONT_PATH), Location::getLetter($col_num));
    }

    // Time to generate gridnums.
    $times['gen_gridnums'] = App::microtimeFloat() - $times['iteration'];
    $times['iteration'] = App::microtimeFloat();

    // Try resizing the image.
    // $rwidth = floor(($width+1) / 3);
    // $rheight = floor(($height+1) / 3);
    // $resized = imagecreatetruecolor($rwidth, $rheight);
    // imagecopyresized($resized, $image, 0, 0, 0, 0, $rwidth, $rheight, $width+1, $height+1);

    // Output the image.
    $image_url = GAME_MAP_IMAGE_PATH;
    $file_path = App::getPath($image_url, TRUE);
    d($file_path);
    // imagepng($resized, $file_path);
    imagepng($image, $file_path);

    // Time to output image.
    $times['output_image'] = App::microtimeFloat() - $times['iteration'];
    $times['iteration'] = App::microtimeFloat();

    // Calculate time to generate image.
    $times['end'] = App::microtimeFloat();
    $times['total'] = $times['end'] - $times['start'];

    // Create the object.
    return new static (['map' => $map, 'url' => $image_url, 'times' => $times]);
  }

  /**
   * Categorize specific names into more generalized icons.
   * @param string $icon The name of the icon we want.
   * @return string Returns the image name for this icon.
   */
  protected static function generalizeIcon ($icon) {
    $list = array();
    $list['arch'] = array('arch');
    $list['beanstalk'] = array('beanstalk');
    $list['bridge'] = array('bridge');
    $list['canyon'] = array('canyon', 'gulch', 'gorge', 'ravine', 'crevice', 'chasm', 'ridge', 'glen', 'cleft', 'crag', 'bluff', 'abyss');
    $list['capital'] = array('capital');
    $list['castle'] = array('castle', 'fort', 'palace', 'fortress', 'stronghold', 'keep', 'citadel', 'empire', 'kingdom');
    $list['cave'] = array('dungeon', 'cave', 'lair', 'cavern', 'hollow', 'den', 'hole', 'tunnels', 'hideout', 'grotto');
    $list['church'] = array('cathedral', 'church', 'sanctuary', 'library');
    $list['city'] = array('city', 'metropolis');
    $list['crater'] = array('crater', 'pits', 'pit', 'comet');
    $list['crystal'] = array('crystal', 'mineral');
    $list['desert'] = array('desert', 'flatland', 'savanna', 'wasteland', 'barrens', 'expanse', 'dunes');
    $list['estate'] = array('ranch', 'estate', 'quarters', 'mansion');
    $list['farm'] = array('farm');
    $list['field'] = array('field', 'meadow', 'lowland', 'grassland', 'valley', 'vale', 'moor', 'heath', 'prairie', 'steppes');
    $list['flowers'] = array('flower', 'flowers', 'flower field');
    $list['forest'] = array('forest', 'thicket', 'brier', 'weald', 'dell', 'grove', 'coppice', 'glade', 'orchard', 'wilds');
    $list['fossils'] = array('fossils', 'bones', 'remains');
    $list['graveyard'] = array('graveyard', 'barrow', 'tomb', 'cemetery');
    $list['hill'] = array('hill', 'knoll', 'hillock', 'foothills');
    $list['hut'] = array('hut', 'witch hut', 'witch');
    $list['jungle'] = array('jungle');
    $list['lake'] = array('lake', 'river', 'stream', 'brook', 'creek', 'rill', 'basin', 'spring', 'loch', 'shallows', 'strand', 'cove', 'fjord', 'waterfall');
    $list['lava'] = array('lava lake', 'magma pool');
    $list['outpost'] = array('outpost', 'frontier', 'garrison');
    $list['mausoleum'] = array('crypt', 'mausoleum', 'sepulcher', 'catacomb', 'necropolis', 'cairn', 'dolmen');
    $list['maze'] = array('maze', 'labyrinth');
    $list['mesa'] = array('mesa');
    $list['mine'] = array('mine', 'abandoned mine');
    $list['moai'] = array('moai');
    $list['mountain'] = array('mountain', 'summit', 'pass', 'point', 'tor');
    $list['oasis'] = array('oasis');
    $list['obelisk'] = array('obelisk');
    $list['pillar'] = array('pillar', 'spire', 'monolith');
    $list['portal'] = array('portal', 'gateway');
    $list['prison'] = array('bastille', 'prison');
    $list['pyramid'] = array('pyramid');
    $list['ruin'] = array('ruin', 'ruins', 'castle ruins', 'fortress ruins');
    $list['shrine'] = array('shrine', 'dias');
    $list['stone'] = array('standing', 'stones', 'stone', 'menhir', 'rock');
    $list['statue'] = array('statue', 'statues');
    $list['swamp'] = array('swamp', 'quagmire', 'mire', 'fen', 'bog', 'marsh', 'wetland', 'lagoon');
    $list['throne'] = array('throne');
    $list['tree'] = array('tree', 'hollow tree');
    $list['tundra'] = array('tundra', 'taiga');
    $list['tower'] = array('tower', 'lookout');
    $list['town'] = array('town', 'village', 'enclave', 'borough');
    $list['vault'] = array('vault');
    $list['volcano'] = array('volcano');
    $list['wall'] = array('wall', 'walls');

    // If the icon is generalized in one of lists, return the generalized key.
    foreach ($list as $map_icon => $alts) {
      if (in_array($icon, $alts)) return $map_icon;
    }

    return $icon;
  }

  /**
   * Create random tiles from the options given.
   */
  protected static function createRandomCells (&$image, $tile_size, $icon_image, $icon_options, $x, $y, $opacity = 100, $fill_all = TRUE) {
    $tiles = array();
    // Select 4 tiles randomly.
    if ($fill_all) {
      for ($t = 1; $t <= 4; $t++) {
        $tiles[] = $icon_options[array_rand($icon_options)];
      }
    }
    // If there's only 1, centre it.
    // else if (count($icon_options) == 1) {
    //   return static::createCenteredCell($image, $tile_size, $icon_image, $icon_options[0], $x, $y, $opacity);
    // }
    // Grab them in order.
    else {
      foreach ($icon_options as $tile) {
        $tiles[] = $tile;
      }
    }

    return static::createCell($image, $tile_size, $icon_image, $tiles, $x, $y, $opacity);
  }

  /**
   * Creates icons in this order: top-left, top-right, bottom-left, bottom-right.
   */
  protected static function createCell (&$image, $tile_size, $icon_image, $icons, $x, $y, $opacity = 100, $centered = TRUE) {
    $num_icons = count($icons);
    if ($num_icons <= 0) return;

    $coords = [
      ['x' => $x, 'y' => $y],
      ['x' => $x + $tile_size, 'y' => $y],
      ['x' => $x, 'y' => $y + $tile_size],
      ['x' => $x + $tile_size, 'y' => $y + $tile_size],
    ];

    // Change the coord structure if they are centered.
    if ($centered) {
      $cell_size = $tile_size * 2;
      $x_centered = $x + floor(($cell_size - $icons[0]['width']) / 2);
      $y_centered = $y + floor(($cell_size - $icons[0]['height']) / 2);

      switch ($num_icons) {
        case 1:
          $coords = [
            ['x' => $x_centered, 'y' => $y_centered],
          ];
          break;

        case 2:
          if ($icons[0]['orientation'] == 'h') {
            $coords = [
              ['x' => $x, 'y' => $y_centered],
              ['x' => $x + $tile_size, 'y' => $y_centered],
            ];
          }
          else if ($icons[0]['orientation'] == 'v') {
            $coords = [
              ['x' => $x_centered, 'y' => $y],
              ['x' => $x_centered, 'y' => $y + $tile_size],
            ];
          }
          break;
      }
    }

    $num_coords = count($coords);
    for ($i = 0; $i < $num_coords; $i++) {
      if (!isset($icons[$i])) continue;

      if ($opacity == 100) {
        imagecopy($image, $icon_image, $coords[$i]['x'], $coords[$i]['y'], $icons[$i]['x'], $icons[$i]['y'], $icons[$i]['width'], $icons[$i]['height']);
      }
      else {
        imagecopymerge($image, $icon_image, $coords[$i]['x'], $coords[$i]['y'], $icons[$i]['x'], $icons[$i]['y'], $icons[$i]['width'], $icons[$i]['height'], $opacity);
      }
    }
  }

  /**
   * Centres a single icon in the middle of 4 tiles.
   */
  protected static function createCenteredCell (&$image, $tile_size, $icon_image, $icons, $x, $y, $opacity = 100) {
    $cell_size = $tile_size * 2;
    
    $x += floor(($cell_size - $icon['width']) / 2);
    $y += floor(($cell_size - $icon['height']) / 2);

    if ($opacity == 100) {
      imagecopy($image, $icon_image, $x, $y, $icon['x'], $icon['y'], $icon['width'], $icon['height']);
    }
    else {
      imagecopymerge($image, $icon_image, $x, $y, $icons[$i]['x'], $icons[$i]['y'], $icons[$i]['width'], $icons[$i]['height'], $opacity);
    }
  }

}