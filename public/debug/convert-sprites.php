<?php

require_once('../../bootstrap.php');

use Kint;


$sprite_locations = array();

$sprite_locations['terrain'] = array(
  'image' => 'terrain.png',
  'tiles' => array(
    'grass' => array(
      array(
        array('x' => 22, 'y' => 3),
        array('x' => 22, 'y' => 5),
        array('x' => 23, 'y' => 5),
      ),
    ),
    'fog' => array(
      array(
        array('x' => 29, 'y' => 5),
      ),
    ),
    'unknown' => array(
      array(
        array('x' => 20, 'y' => 22),
      ),
    ),
    'lava' => array(
      array(
        array('x' => 15, 'y' => 6),
      ),
      array(
        array('x' => 15, 'y' => 7),
      ),
    ),
    'swamp' => array(
      array(
        array('x' => 12, 'y' => 5),
        array('x' => 13, 'y' => 5),
        array('x' => 13, 'y' => 4),
        array('x' => 14, 'y' => 5),
      ),
    ),
    'desert' => array(
      array(
        array('x' => 1, 'y' => 12),
        array('x' => 1, 'y' => 12),
        array('x' => 1, 'y' => 12),
        array('x' => 1, 'y' => 12),
      ),
    ),
    'field' => array(
      array(
        array('x' => 1, 'y' => 29),
        array('x' => 1, 'y' => 29),
        array('x' => 1, 'y' => 29),
        array('x' => 1, 'y' => 29),
      ),
      array(
        array('x' => 1, 'y' => 23),
        array('x' => 1, 'y' => 23),
        array('x' => 1, 'y' => 23),
        array('x' => 1, 'y' => 23),
      ),
    ),
  ),
);

$sprite_locations['capital'] = array(
  'image' => 'capital.png',
  'tiles' => array(
    // 'capital' => array(
    //   array(
    //     array('x' => 0, 'y' => 2),
    //     array('x' => 1, 'y' => 2),
    //     array('x' => 0, 'y' => 3),
    //     array('x' => 1, 'y' => 3),
    //   ),
    //   array(
    //     array('x' => 2, 'y' => 2),
    //     array('x' => 3, 'y' => 2),
    //     array('x' => 2, 'y' => 3),
    //     array('x' => 3, 'y' => 3),
    //   ),
    //   array(
    //     array('x' => 0, 'y' => 4),
    //     array('x' => 1, 'y' => 4),
    //     array('x' => 0, 'y' => 5),
    //     array('x' => 1, 'y' => 5),
    //   ),
    //   array(
    //     array('x' => 2, 'y' => 4),
    //     array('x' => 3, 'y' => 4),
    //     array('x' => 2, 'y' => 5),
    //     array('x' => 3, 'y' => 5),
    //   ),            
    // ),
    'estate' => array(
      array(
        array('x' => 6, 'y' => 0),
      ),
      array(
        array('x' => 6, 'y' => 1),
      ),
      array(
        array('x' => 4, 'y' => 2),
      ),
      array(
        array('x' => 6, 'y' => 4),
      ),
      array(
        array('x' => 4, 'y' => 3),
        array('x' => 5, 'y' => 3),
      ),
    ),
    'castle' => array(
      array(
        array('x' => 3, 'y' => 0),
      ),
      array(
        array('x' => 5, 'y' => 0),
      ),
      array(
        array('x' => 5, 'y' => 1),
      ),
    ),
    'city' => array(
      array(
        array('x' => 8, 'y' => 2),
        array('x' => 9, 'y' => 2),
        array('x' => 8, 'y' => 3),
        array('x' => 9, 'y' => 3),
      ),
    ),
    'town' => array(
      array(
        array('x' => 12, 'y' => 2),
        array('x' => 13, 'y' => 2),
        array('x' => 12, 'y' => 3),
        array('x' => 13, 'y' => 3),
      ),
      array(
        array('x' => 14, 'y' => 2),
        array('x' => 15, 'y' => 2),
        array('x' => 14, 'y' => 3),
        array('x' => 15, 'y' => 3),
      ),
      array(
        array('x' => 12, 'y' => 4),
        array('x' => 13, 'y' => 4),
        array('x' => 12, 'y' => 5),
        array('x' => 13, 'y' => 5),
      ),
      array(
        array('x' => 14, 'y' => 4),
        array('x' => 15, 'y' => 4),
        array('x' => 14, 'y' => 5),
        array('x' => 15, 'y' => 5),
      ),
    ),
    'tower' => array(
      array(
        array('x' => 0, 'y' => 0),
        array('x' => 0, 'y' => 1),
      ),
      array(
        array('x' => 7, 'y' => 0),
      ),
    ),
    'outpost' => array(
      array(
        array('x' => 11, 'y' => 4),
      ),
    ),
    'tree' => array(
      array(
        array('x' => 1, 'y' => 0),
        array('x' => 2, 'y' => 0),
        array('x' => 1, 'y' => 1),
        array('x' => 2, 'y' => 1),
      ),
    ),
    'ruin' => array(
      array(
        array('x' => 4, 'y' => 0)
      ),
      array(
        array('x' => 4, 'y' => 1)
      ),
      array(
        array('x' => 7, 'y' => 1)
      ),
    ),
    'lake' => array(
      array(
        array('x' => 7, 'y' => 3)
      ),
      array(
        array('x' => 11, 'y' => 10),
        array('x' => 12, 'y' => 10),
        array('x' => 11, 'y' => 11),
        array('x' => 12, 'y' => 11),
      ),
      array(
        array('x' => 13, 'y' => 10),
        array('x' => 13, 'y' => 11),
      ),
    ),
    'wall' => array(
      array(
        array('x' => 2, 'y' => 10),
        array('x' => 3, 'y' => 10),
        array('x' => 2, 'y' => 11),
        array('x' => 3, 'y' => 11),
      ),
      array(
        array('x' => 4, 'y' => 10),
        array('x' => 5, 'y' => 10),
        array('x' => 4, 'y' => 11),
        array('x' => 5, 'y' => 11),
      ),
    ),
  ),
);

$sprite_locations['citytowns'] = array(
  'image' => 'citytowns.png',
  'tiles' => array(
    'lake' => array(
      array(
        array('x' => 4, 'y' => 0),
      ),
      array(
        array('x' => 5, 'y' => 0),
      ),
    ),
    'beanstalk' => array(
      array(
        array('x' => 7, 'y' => 0),
        array('x' => 7, 'y' => 1),
      ),
    ),
    'tree' => array(
      array(
        array('x' => 3, 'y' => 1),
      ),
    ),
    'pyramid' => array(
      array(
        array('x' => 2, 'y' => 1),
      ),
    ),
    'castle' => array(
      array(
        array('x' => 8, 'y' => 0),
        array('x' => 9, 'y' => 0),
        array('x' => 8, 'y' => 1),
        array('x' => 9, 'y' => 1),
      ),
      array(
        array('x' => 10, 'y' => 0),
        array('x' => 11, 'y' => 0),
        array('x' => 10, 'y' => 1),
        array('x' => 11, 'y' => 1),
      ),
      array(
        array('x' => 12, 'y' => 0),
        array('x' => 13, 'y' => 0),
        array('x' => 12, 'y' => 1),
        array('x' => 13, 'y' => 1),
      ),
      array(
        array('x' => 14, 'y' => 0),
        array('x' => 15, 'y' => 0),
        array('x' => 14, 'y' => 1),
        array('x' => 15, 'y' => 1),
      ),
      array(
        array('x' => 8, 'y' => 2),
        array('x' => 9, 'y' => 2),
        array('x' => 8, 'y' => 3),
        array('x' => 9, 'y' => 3),
      ),
      array(
        array('x' => 10, 'y' => 2),
        array('x' => 11, 'y' => 2),
        array('x' => 10, 'y' => 3),
        array('x' => 11, 'y' => 3),
      ),
      array(
        array('x' => 11, 'y' => 6),
        array('x' => 12, 'y' => 6),
        array('x' => 11, 'y' => 7),
        array('x' => 12, 'y' => 7),
      ),
    ),
    'city' => array(
      array(
        array('x' => 0, 'y' => 11),
        array('x' => 1, 'y' => 11),
        array('x' => 0, 'y' => 12),
        array('x' => 1, 'y' => 12),
      ),
    ),
    'tower' => array(
      array(
        array('x' => 0, 'y' => 14),
        array('x' => 0, 'y' => 15),
      ),
      array(
        array('x' => 2, 'y' => 14),
        array('x' => 2, 'y' => 15),
      ),
      array(
        array('x' => 3, 'y' => 14),
        array('x' => 3, 'y' => 15),
      ),
    ),
    'outpost' => array(
      array(
        array('x' => 5, 'y' => 14),
      ),
    ),
    'ruin' => array(
      array(
        array('x' => 12, 'y' => 2),
        array('x' => 13, 'y' => 2),
        array('x' => 12, 'y' => 3),
        array('x' => 13, 'y' => 3),
      ),
      array(
        array('x' => 14, 'y' => 2),
        array('x' => 15, 'y' => 2),
        array('x' => 14, 'y' => 3),
        array('x' => 15, 'y' => 3),
      ),
      array(
        array('x' => 1, 'y' => 14),
        array('x' => 1, 'y' => 15),
      ),
      array(
        array('x' => 5, 'y' => 15),
      ),
    ),
    'church' => array(
      array(
        array('x' => 6, 'y' => 14),
      ),
      array(
        array('x' => 6, 'y' => 15),
      ),
      array(
        array('x' => 7, 'y' => 15),
      ),
    ),
    'estate' => array(
      array(
        array('x' => 4, 'y' => 11),
        array('x' => 5, 'y' => 11),
        array('x' => 4, 'y' => 12),
        array('x' => 5, 'y' => 12),
      ),
      array(
        array('x' => 6, 'y' => 11),
        array('x' => 7, 'y' => 11),
        array('x' => 6, 'y' => 12),
        array('x' => 7, 'y' => 12),
      ),
    ),
    'farm' => array(
      array(
        array('x' => 4, 'y' => 7),
        array('x' => 5, 'y' => 7),
      ),
      array(
        array('x' => 2, 'y' => 8),
        array('x' => 3, 'y' => 8),
      ),
    ),
    'volcano' => array(
      array(
        array('x' => 0, 'y' => 5),
        array('x' => 1, 'y' => 5),
        array('x' => 0, 'y' => 6),
        array('x' => 1, 'y' => 6),
      ),
      array(
        array('x' => 0, 'y' => 4),
        array('x' => 1, 'y' => 4),
        array('x' => 0, 'y' => 6),
        array('x' => 1, 'y' => 6),
      ),
    ),
    'mountain' => array(
      array(
        array('x' => 2, 'y' => 3),
        array('x' => 3, 'y' => 3),
        array('x' => 2, 'y' => 4),
        array('x' => 3, 'y' => 4),
      ),
      array(
        array('x' => 4, 'y' => 3),
        array('x' => 5, 'y' => 3),
        array('x' => 4, 'y' => 4),
        array('x' => 5, 'y' => 4),
      ),
      array(
        array('x' => 6, 'y' => 3),
        array('x' => 7, 'y' => 3),
        array('x' => 6, 'y' => 4),
        array('x' => 7, 'y' => 4),
      ),
    ),
    'crater' => array(
      array(
        array('x' => 0, 'y' => 3),
      ),
    ),
  ),
);

$sprite_locations['decoration'] = array(
  'image' => 'decoration.png',
  'tiles' => array(
    'tree' => array(
      array(
        array('x' => 0, 'y' => 6),
        array('x' => 1, 'y' => 6),
        array('x' => 0, 'y' => 7),
        array('x' => 1, 'y' => 7),
      ),
    ),
  ),
);

$sprite_locations['dragons'] = array(
  'image' => 'dragons.png',
  'tiles' => array(
    'tree' => array(
      array(
        array('x' => 1, 'y' => 2),
        array('x' => 2, 'y' => 2),
        array('x' => 1, 'y' => 3),
        array('x' => 2, 'y' => 3),
      ),
      array(
        array('x' => 3, 'y' => 2),
        array('x' => 4, 'y' => 2),
        array('x' => 3, 'y' => 3),
        array('x' => 4, 'y' => 3),
      ),
    ),
    'beanstalk' => array(
      array(
        array('x' => 5, 'y' => 2),
        array('x' => 5, 'y' => 3),
      ),
    ),
    'pillar' => array(
      array(
        array('x' => 5, 'y' => 4),
        array('x' => 5, 'y' => 5),
      ),
      array(
        array('x' => 2, 'y' => 6),
        array('x' => 2, 'y' => 7),
      ),
    ),
    'volcano' => array(
      array(
        array('x' => 6, 'y' => 4),
        array('x' => 7, 'y' => 4),
        array('x' => 6, 'y' => 5),
        array('x' => 7, 'y' => 5),
      ),
    ),
    'statue' => array(
      array(
        array('x' => 0, 'y' => 8),
        array('x' => 1, 'y' => 8),
        array('x' => 0, 'y' => 9),
        array('x' => 1, 'y' => 9),
      ),
      array(
        array('x' => 2, 'y' => 8),
        array('x' => 3, 'y' => 8),
        array('x' => 2, 'y' => 9),
        array('x' => 3, 'y' => 9),
      ),
    ),
    'shrine' => array(
      array(
        array('x' => 4, 'y' => 8),
      ),
    ),
  ),
);

$sprite_locations['gravetown'] = array(
  'image' => 'gravetown.png',
  'tiles' => array(
    'stone' => array(
      array(
        array('x' => 8, 'y' => 3),
        array('x' => 9, 'y' => 3),
        array('x' => 8, 'y' => 4),
        array('x' => 9, 'y' => 4),
      ),
    ),
    'city' => array(
      array(
        array('x' => 14, 'y' => 6),
        array('x' => 15, 'y' => 6),
        array('x' => 14, 'y' => 7),
        array('x' => 15, 'y' => 7),
      ),
      array(
        array('x' => 14, 'y' => 8),
        array('x' => 15, 'y' => 8),
        array('x' => 14, 'y' => 9),
        array('x' => 15, 'y' => 9),
      ),
      array(
        array('x' => 8, 'y' => 8),
        array('x' => 9, 'y' => 8),
        array('x' => 8, 'y' => 9),
        array('x' => 9, 'y' => 9),
      ),
      array(
        array('x' => 10, 'y' => 8),
        array('x' => 11, 'y' => 8),
        array('x' => 10, 'y' => 9),
        array('x' => 11, 'y' => 9),
      ),
      array(
        array('x' => 12, 'y' => 8),
        array('x' => 13, 'y' => 8),
        array('x' => 12, 'y' => 9),
        array('x' => 13, 'y' => 9),
      ),
    ),
    'mesa' => array(
      array(
        array('x' => 4, 'y' => 10),
      ),
      array(
        array('x' => 4, 'y' => 11),
      ),
      array(
        array('x' => 5, 'y' => 10),
      ),
      array(
        array('x' => 5, 'y' => 11),
      ),
      array(
        array('x' => 6, 'y' => 11),
      ),
      array(
        array('x' => 7, 'y' => 11),
      ),
    ),
    'graveyard' => array(
      array(
        array('x' => 3, 'y' => 9),
      ),
      array(
        array('x' => 4, 'y' => 9),
      ),
      array(
        array('x' => 5, 'y' => 9),
      ),
      array(
        array('x' => 6, 'y' => 9),
        array('x' => 6, 'y' => 10),
      ),
      array(
        array('x' => 7, 'y' => 9),
        array('x' => 7, 'y' => 10),
      ),
    ),
    'tower' => array(
      array(
        array('x' => 8, 'y' => 12),
        array('x' => 8, 'y' => 13),
      ),
      array(
        array('x' => 9, 'y' => 12),
        array('x' => 9, 'y' => 13),
      ),
      array(
        array('x' => 8, 'y' => 14),
        array('x' => 8, 'y' => 15),
      ),
      array(
        array('x' => 9, 'y' => 14),
        array('x' => 9, 'y' => 15),
      ),
    ),
  ),
);

$sprite_locations['graveyard'] = array(
  'image' => 'graveyard.png',
  'tiles' => array(
    'statue' => array(
      array(
        array('x' => 3, 'y' => 4),
        array('x' => 3, 'y' => 5),
      ),
      array(
        array('x' => 4, 'y' => 4),
        array('x' => 4, 'y' => 5),
      ),
      array(
        array('x' => 5, 'y' => 4),
        array('x' => 5, 'y' => 5),
      ),
      array(
        array('x' => 6, 'y' => 4),
        array('x' => 6, 'y' => 5),
      ),
      array(
        array('x' => 3, 'y' => 6),
        array('x' => 3, 'y' => 7),
      ),
      array(
        array('x' => 4, 'y' => 6),
        array('x' => 4, 'y' => 7),
      ),
      array(
        array('x' => 5, 'y' => 6),
        array('x' => 5, 'y' => 7),
      ),
      array(
        array('x' => 6, 'y' => 6),
        array('x' => 6, 'y' => 7),
      ),
    ),
    'graveyard' => array(
      array(
        array('x' => 0, 'y' => 1),
      ),
      array(
        array('x' => 1, 'y' => 4),
      ),
      array(
        array('x' => 2, 'y' => 6),
      ),
    ),
  ),
);

$sprite_locations['minetown'] = array(
  'image' => 'minetown.png',
  'tiles' => array(
    'mountain' => array(
      array(
        array('x' => 14, 'y' => 0),
        array('x' => 15, 'y' => 0),
        array('x' => 14, 'y' => 1),
        array('x' => 15, 'y' => 1),
      ),
    ),
    'tower' => array(
      array(
        array('x' => 4, 'y' => 3),
        array('x' => 4, 'y' => 4),
      ),
    ),
    'town' => array(
      array(
        array('x' => 11, 'y' => 8),
        array('x' => 12, 'y' => 8),
        array('x' => 11, 'y' => 9),
        array('x' => 12, 'y' => 9),
      ),
    ),
    'oasis' => array(
      array(
        array('x' => 8, 'y' => 9),
      ),
    ),
    'hut' => array(
      array(
        array('x' => 0, 'y' => 1),
      ),
    ),
  ),
);

$sprite_locations['morecastle'] = array(
  'image' => 'morecastle.png',
  'tiles' => array(
    'stone' => array(
      array(
        array('x' => 10, 'y' => 5),
      ),
      array(
        array('x' => 7, 'y' => 10),
      ),
      array(
        array('x' => 7, 'y' => 11),
      ),
    ),
    'cave' => array(
      array(
        array('x' => 11, 'y' => 5),
      ),
    ),
    'graveyard' => array(
      array(
        array('x' => 7, 'y' => 9),
      ),
    ),
    'canyon' => array(
      array(
        array('x' => 9, 'y' => 2),
      ),
    ),
  ),
);

$sprite_locations['pillars'] = array(
  'image' => 'pillars.png',
  'tiles' => array(
    'crystal' => array(
      array(
        array('x' => 12, 'y' => 7),
      ),
      array(
        array('x' => 13, 'y' => 7),
      ),
      array(
        array('x' => 14, 'y' => 7),
      ),
      array(
        array('x' => 15, 'y' => 7),
      ),
    ),
    'fossils' => array(
      array(
        array('x' => 10, 'y' => 5),
      ),
      array(
        array('x' => 11, 'y' => 5),
      ),
    ),
  ),
);

$sprite_locations['statues'] = array(
  'image' => 'statues.png',
  'tiles' => array(
    'pillar' => array(
      array(
        array('x' => 10, 'y' => 2),
        array('x' => 10, 'y' => 3),
      ),
      array(
        array('x' => 3, 'y' => 3),
        array('x' => 3, 'y' => 4),
      ),
    ),
    'crystal' => array(
      array(
        array('x' => 10, 'y' => 0),
      ),
      array(
        array('x' => 11, 'y' => 0),
      ),
      array(
        array('x' => 12, 'y' => 0),
      ),
      array(
        array('x' => 13, 'y' => 0),
      ),
      array(
        array('x' => 14, 'y' => 0),
      ),
      array(
        array('x' => 15, 'y' => 0),
      ),
      array(
        array('x' => 12, 'y' => 1),
      ),
      array(
        array('x' => 13, 'y' => 1),
      ),
      array(
        array('x' => 14, 'y' => 1),
      ),
      array(
        array('x' => 15, 'y' => 1),
      ),
      array(
        array('x' => 11, 'y' => 2),
        array('x' => 11, 'y' => 3),
      ),
      array(
        array('x' => 12, 'y' => 2),
        array('x' => 12, 'y' => 3),
      ),
      array(
        array('x' => 13, 'y' => 2),
        array('x' => 13, 'y' => 3),
      ),
      array(
        array('x' => 14, 'y' => 2),
        array('x' => 14, 'y' => 3),
      ),
      array(
        array('x' => 15, 'y' => 2),
        array('x' => 15, 'y' => 3),
      ),
      array(
        array('x' => 10, 'y' => 4),
        array('x' => 10, 'y' => 5),
      ),
    ),
    'shrine' => array(
      array(
        array('x' => 3, 'y' => 0),
      ),
    ),
    'statue' => array(
      array(
        array('x' => 0, 'y' => 1),
        array('x' => 0, 'y' => 2),
      ),
      array(
        array('x' => 1, 'y' => 1),
        array('x' => 1, 'y' => 2),
      ),
      array(
        array('x' => 3, 'y' => 1),
        array('x' => 3, 'y' => 2),
      ),
      array(
        array('x' => 4, 'y' => 1),
        array('x' => 4, 'y' => 2),
      ),
      array(
        array('x' => 1, 'y' => 3),
        array('x' => 1, 'y' => 4),
      ),
      array(
        array('x' => 4, 'y' => 3),
        array('x' => 4, 'y' => 4),
      ),
      array(
        array('x' => 5, 'y' => 3),
        array('x' => 5, 'y' => 4),
      ),
      array(
        array('x' => 7, 'y' => 1),
        array('x' => 7, 'y' => 2),
      ),
      array(
        array('x' => 0, 'y' => 7),
        array('x' => 1, 'y' => 7),
        array('x' => 0, 'y' => 8),
        array('x' => 1, 'y' => 8),
      ),
      array(
        array('x' => 2, 'y' => 7),
        array('x' => 3, 'y' => 7),
        array('x' => 2, 'y' => 8),
        array('x' => 3, 'y' => 8),
      ),
      array(
        array('x' => 4, 'y' => 7),
        array('x' => 4, 'y' => 8),
      ),
      array(
        array('x' => 5, 'y' => 7),
        array('x' => 5, 'y' => 8),
      ),
    ),
  ),
);

$sprite_locations['bones'] = array(
  'image' => 'bones.png',
  'tiles' => array(
    'canyon' => array(
      array(
        array('x' => 5, 'y' => 2),
        array('x' => 5, 'y' => 3),
      ),
      array(
        array('x' => 6, 'y' => 2),
        array('x' => 6, 'y' => 3),
      ),
    ),
    'fossils' => array(
      array(
        array('x' => 8, 'y' => 9),
        array('x' => 9, 'y' => 9),
        array('x' => 8, 'y' => 10),
        array('x' => 9, 'y' => 10),
      ),
      array(
        array('x' => 10, 'y' => 9),
        array('x' => 10, 'y' => 10),
      ),
    ),
    'crater' => array(
      array(
        array('x' => 9, 'y' => 11),
      ),
      array(
        array('x' => 11, 'y' => 11),
      ),
      array(
        array('x' => 12, 'y' => 11),
      ),
    ),
  ),
);

$sprite_locations['cities'] = array(
  'image' => 'cities.png',
  'tiles' => array(
    'bridge' => array(
      array(
        array('x' => 4, 'y' => 4),
      ),
      array(
        array('x' => 5, 'y' => 4),
      ),
      array(
        array('x' => 6, 'y' => 4),
      ),
      array(
        array('x' => 7, 'y' => 4),
      ),
    ),
    'mountain' => array(
      array(
        array('x' => 0, 'y' => 1),
      ),
    ),
    'volcano' => array(
      array(
        array('x' => 2, 'y' => 1),
      ),
    ),
    'cave' => array(
      array(
        array('x' => 1, 'y' => 1),
      ),
    ),
    'crater' => array(
      array(
        array('x' => 2, 'y' => 5),
      ),
      array(
        array('x' => 3, 'y' => 5),
      ),
    ),
    'capital' => array(
      array(
        array('x' => 4, 'y' => 8),
        array('x' => 5, 'y' => 8),
        array('x' => 4, 'y' => 9),
        array('x' => 5, 'y' => 9),
      ),
    ),
    'oasis' => array(
      array(
        array('x' => 6, 'y' => 8),
        array('x' => 7, 'y' => 8),
        array('x' => 6, 'y' => 9),
        array('x' => 7, 'y' => 9),
      ),
    ),
    'forest' => array(
      array(
        array('x' => 0, 'y' => 3),
        array('x' => 0, 'y' => 3),
        array('x' => 0, 'y' => 3),
        array('x' => 0, 'y' => 3),
      ),
      array(
        array('x' => 0, 'y' => 3),
        array('x' => 2, 'y' => 3),
        array('x' => 0, 'y' => 3),
        array('x' => 3, 'y' => 3),
      ),
      array(
        array('x' => 0, 'y' => 3),
        array('x' => 0, 'y' => 3),
        array('x' => 2, 'y' => 3),
        array('x' => 0, 'y' => 3),
      ),
    ),
    'city' => array(
      array(
        array('x' => 4, 'y' => 12),
        array('x' => 5, 'y' => 12),
        array('x' => 4, 'y' => 13),
        array('x' => 5, 'y' => 13),
      ),
    ),
    'mausoleum' => array(
      array(
        array('x' => 4, 'y' => 14),
        array('x' => 5, 'y' => 14),
        array('x' => 4, 'y' => 15),
        array('x' => 5, 'y' => 15),
      ),
      array(
        array('x' => 2, 'y' => 16),
        array('x' => 3, 'y' => 16),
        array('x' => 2, 'y' => 17),
        array('x' => 3, 'y' => 17),
      ),
    ),
    'town' => array(
      array(
        array('x' => 0, 'y' => 8),
      ),
    ),
    'church' => array(
      array(
        array('x' => 0, 'y' => 9),
      ),
    ),
    'prison' => array(
      array(
        array('x' => 4, 'y' => 10),
        array('x' => 5, 'y' => 10),
        array('x' => 4, 'y' => 11),
        array('x' => 5, 'y' => 11),
      ),
    ),
    'tundra' => array(
      array(
        array('x' => 4, 'y' => 0),
        array('x' => 4, 'y' => 0),
        array('x' => 4, 'y' => 0),
        array('x' => 4, 'y' => 0),
      ),
    ),
  ),
);

$sprite_locations['flowers'] = array(
  'image' => 'flowers.png',
  'tiles' => array(
    'flowers' => array(
      array(
        array('x' => 8, 'y' => 4),
        array('x' => 9, 'y' => 4),
        array('x' => 8, 'y' => 7),
        array('x' => 9, 'y' => 7),
      ),
      array(
        array('x' => 11, 'y' => 4),
        array('x' => 12, 'y' => 4),
        array('x' => 11, 'y' => 7),
        array('x' => 12, 'y' => 7),
      ),
      array(
        array('x' => 11, 'y' => 0),
        array('x' => 12, 'y' => 0),
        array('x' => 11, 'y' => 3),
        array('x' => 12, 'y' => 3),
      ),
    ),
    'lava' => array(
      array(
        array('x' => 12, 'y' => 12),
        array('x' => 13, 'y' => 12),
        array('x' => 12, 'y' => 15),
        array('x' => 13, 'y' => 15),
      ),
    ),
    'hill' => array(
      array(
        array('x' => 2, 'y' => 8),
        array('x' => 3, 'y' => 8),
        array('x' => 2, 'y' => 11),
        array('x' => 3, 'y' => 11),
      ),
    ),
  ),
);

$sprite_locations['flowersmore'] = array(
  'image' => 'flowersmore.png',
  'tiles' => array(
    'flowers' => array(
      array(
        array('x' => 0, 'y' => 4),
        array('x' => 1, 'y' => 4),
        array('x' => 0, 'y' => 5),
        array('x' => 1, 'y' => 5),
      ),
    ),
    'canyon' => array(
      array(
        array('x' => 4, 'y' => 6),
      ),
      array(
        array('x' => 4, 'y' => 7),
        array('x' => 5, 'y' => 7),
        array('x' => 4, 'y' => 8),
        array('x' => 5, 'y' => 8),
      ),
    ),
    'hill' => array(
      array(
        array('x' => 2, 'y' => 6),
      ),
    ),
  ),
);

$sprite_locations['arch'] = array(
  'image' => 'arch.png',
  'tiles' => array(
    'cave' => array(
      array(
        array('x' => 12, 'y' => 11),
        array('x' => 13, 'y' => 11),
        array('x' => 12, 'y' => 12),
        array('x' => 13, 'y' => 12),
      ),
    ),
    'stone' => array(
      array(
        array('x' => 3, 'y' => 3),
        array('x' => 3, 'y' => 4),
      ),
    ),
  ),
);

$sprite_locations['forest'] = array(
  'image' => 'forest.png',
  'tiles' => array(
    'jungle' => array(
      array(
        array('x' => 8, 'y' => 4),
        array('x' => 9, 'y' => 4),
        array('x' => 8, 'y' => 5),
        array('x' => 9, 'y' => 5),
      ),
    ),
  ),
);

$sprite_locations['mountains'] = array(
  'image' => 'mountains.png',
  'tiles' => array(
    'hut' => array(
      array(
        array('x' => 16, 'y' => 1),
      ),
    ),
  ),
);

$sprite_locations['merged'] = array(
  'image' => 'merged.png',
  'tiles' => array(
    'throne' => array(
      array(
        array('x' => 0, 'y' => 0),
        array('x' => 1, 'y' => 0),
        array('x' => 0, 'y' => 1),
        array('x' => 1, 'y' => 1),
      ),
      array(
        array('x' => 3, 'y' => 0),
        array('x' => 4, 'y' => 0),
        array('x' => 3, 'y' => 1),
        array('x' => 4, 'y' => 1),
      ),
    ),
    'moai' => array(
      array(
        array('x' => 2, 'y' => 0),
        array('x' => 2, 'y' => 1),
      ),
    ),
    'portal' => array(
      array(
        array('x' => 5, 'y' => 0),
        array('x' => 6, 'y' => 0),
        array('x' => 5, 'y' => 1),
        array('x' => 6, 'y' => 1),
      ),
      array(
        array('x' => 2, 'y' => 2),
      ),
      array(
        array('x' => 2, 'y' => 3),
      ),
    ),
    'vault' => array(
      array(
        array('x' => 7, 'y' => 0),
        array('x' => 8, 'y' => 0),
        array('x' => 7, 'y' => 1),
        array('x' => 8, 'y' => 1),
      ),
      array(
        array('x' => 8, 'y' => 2),
        array('x' => 9, 'y' => 2),
        array('x' => 8, 'y' => 3),
        array('x' => 9, 'y' => 3),
      ),
    ),
    'mine' => array(
      array(
        array('x' => 9, 'y' => 0),
        array('x' => 10, 'y' => 0),
        array('x' => 9, 'y' => 1),
        array('x' => 10, 'y' => 1),
      ),
      array(
        array('x' => 0, 'y' => 2),
        array('x' => 1, 'y' => 2),
        array('x' => 0, 'y' => 3),
        array('x' => 1, 'y' => 3),
      ),
      array(
        array('x' => 10, 'y' => 2),
        array('x' => 11, 'y' => 2),
        array('x' => 10, 'y' => 3),
        array('x' => 11, 'y' => 3),
      ),
    ),
    'obelisk' => array(
      array(
        array('x' => 3, 'y' => 2),
        array('x' => 3, 'y' => 3),
      ),
      array(
        array('x' => 4, 'y' => 2),
        array('x' => 4, 'y' => 3),
      ),
      array(
        array('x' => 5, 'y' => 2),
        array('x' => 5, 'y' => 3),
      ),
    ),
    'arch' => array(
      array(
        array('x' => 6, 'y' => 2),
        array('x' => 7, 'y' => 2),
        array('x' => 6, 'y' => 3),
        array('x' => 7, 'y' => 3),
      ),
    ),
    'maze' => array(
      array(
        array('x' => 0, 'y' => 4),
        array('x' => 1, 'y' => 4),
        array('x' => 0, 'y' => 5),
        array('x' => 1, 'y' => 5),
      ),
    ),
  ),
);


d(json_encode($sprite_locations, JSON_PRETTY_PRINT));