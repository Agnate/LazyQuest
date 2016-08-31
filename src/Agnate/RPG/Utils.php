<?php

namespace Agnate\RPG;

class Utils {

  /**
   * Convert Markup (used in Slack) to HTML for browser debug viewing.
   */
  public static function convertMarkup ($string) {
    $info = array(
      '/:([A-Za-z0-9_\-\+]+?):/' => '<img class="icon" src="/debug/icons/\1.png" width="22px" height="22px">',
      '/\\n/' => '<br>',
      '/\*(.*?)\*/' => '<strong>\1</strong>',
      '/\b_((?:__|[\s\S])+?)_\b|^\*((?:\*\*|[\s\S])+?)\*(?!\*)/' => '<em>\1</em>',
      '/(`+)\s*([\s\S]*?[^`])\s*\1(?!`)/' => '<code>\2</code>',
    );

    return preg_replace(array_keys($info), array_values($info), $string);
  }
}