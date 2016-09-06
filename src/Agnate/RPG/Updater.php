<?php

use \Exception;
use \PDO;
use \Agnate\RPG\App;
use \Agnate\RPG\Entity;
use \Agnate\RPG\Update;

namespace Agnate\RPG;

/**
 * Use an Updater to perform database updates for new features that are released.
 * Note: Recommended to use Updater::get() to retrieve the newest Updater.
 */
class Updater extends Entity {

  public $uid;
  public $version;
  public $updated;

  protected $output_indent = 0;
  protected $output_CLI = TRUE;

  // Static vars
  static $db_table = 'updates';
  static $default_class = '\Agnate\RPG\Updater';
  static $partials = array();
  static $primary_key = 'uid';
  static $relationships = array();

  /**
   * Get the newest Updater.
   */
  public static function get () {
    // Generate the table if we don't have one yet.
    static::init();

    // Fetch the newest version from the table.
    $query = App::query("SELECT uid FROM " . static::$db_table . " ORDER BY updated DESC, version DESC LIMIT 1");
    $success = $query->execute();

    // If there are no items, generate the first Updater.
    if (!$success || $query->rowCount() <= 0) {
      return new Updater (array(
        'version' => '0.0.0',
        'updated' => 0,
      ));
    }

    // Fetch the row as an Array so we can load up the Updater.
    $row = $query->fetch(\PDO::FETCH_ASSOC);

    return Updater::load(array('uid' => $row['uid']));
  }

  /**
   * Initialize the database for Updater as it is necessary to construct the entire database.
   */
  protected static function init () {
    // If there's no table to store database versions, create it.
    if (!App::database()->tableExists(static::$db_table)) {
      $fields = array();
      $fields[] = "uid INT(11) UNSIGNED AUTO_INCREMENT";
      $fields[] = "version VARCHAR(30) NOT NULL";
      $fields[] = "updated INT(10) UNSIGNED NOT NULL";

      // Make the database.
      App::database()->createTable(static::$db_table, static::$primary_key, $fields);
    }
  }

  /**
   * Perform an update.
   * @param $version Version name to update to.
   * @param $force Force the current version to re-run it's update.
   * @param $fromCLI Indiciates if this command was run from the command line or not.
   * @return Boolean Returns if the updates were successfully performed or not.
   */
  public function perform ($version, $force = FALSE, $fromCLI = TRUE) {
    // Check that the version sent is valid.
    if (!$this->validVersion($version)) throw new \Exception ('Invalid version sent to Updater->perform(), ' . $version . ' given.');

    // Calculate version difference between this instance and version sent as parameter.
    $updates = $this->versionDiff($version, $force);

    // If the update-to version is not actually higher than current version, we're done.
    if (count($updates) <= 0) {
      if ($this->version == $version) {
        $this->output("Database is already up to date.");
      }
      else if ($this->versionGTE($version)) {
        $this->output("Cannot downgrade the database version.\nSelect a version higher than " . $this->version . ".");
      }
      else {
        $this->output("Not sure why there are no updates...?");
      }
      return FALSE;
    }

    // Set output.
    $this->output_CLI = $fromCLI;

    // Check that the database is properly initialized and fetch the most recent version.
    static::init();

    // Backup the database. Might only be able to do this from CLI, but try anyway.
    $this->output("Backing up database...");

    // Run database backup with exec() script for CLI use.
    $filename = 'backups/backup_' . DB_NAME . '_' . date('Y-m-d-H-i-s') . '.sql';
    $command = 'mysqldump -h ' . DB_HOST . ' -u ' . DB_USER . ' -p' . DB_PASS . ' ' . DB_NAME . ' > ' . $filename;
    $output = array();
    exec($command, $output, $error);
    if ($error) {
      $this->output("@redFailed to backup database.@end");
      exit;
    }

    $this->output("Database '" . DB_NAME ."' successfully backed up to: " .$filename);
    $this->output("Starting updates...\n");

    // Start a transaction for the updates.
    App::database()->connection()->beginTransaction();

    // For each update listed, run the update.
    $update_success = TRUE;
    foreach ($updates as $uversion => $update) {
      $this->output("Running update " . $uversion . ":");
      $this->outputIndent();

      // Get the queries to run.
      $queries = $update::run($force);
      $count = count($queries);

      if ($count <= 0) {
        $this->output("No queries to run.");
        continue;
      }

      $this->output("Running " . $count . " ". ($count > 1 ? "queries" : "query") . ":");
      $this->outputIndent();
      
      // Run the queries.
      foreach ($queries as $query) {
        // If this isn't an UpdateQuery, cancel the whole update.
        if (!$query instanceof \Agnate\RPG\Update\UpdateQuery) {
          $this->output("@redQuery was not an UpdateQuery instance.@end");
          $update_success = FALSE;
          break 2;
        }

        // If the query failed, rollback the transaction.
        if (!$this->processQuery($query)) {
          // Roll back transaction.
          $this->output("\n@redUpdate " . $uversion . " failed.@end");
          $update_success = FALSE;
          break 2;
        }
      }

      $this->outputOutdent();
      $this->output(($count > 1 ? "Queries" : "Query") . " complete.");
      $this->outputOutdent();
      $this->output("Update " . $version . " complete.\n");
    }

    $this->outputResetIndent();

    // If the update failed at any point.
    if (!$update_success) {
      App::database()->connection()->rollBack();
      $this->output("Rolling back database changes. Please resolve the issues with update and re-run it.");
      return $update_success;
    }

    // Update was a success, so commit the database changes.
    App::database()->connection()->commit();
    $this->output("Committed database transaction so all changes are permanent.");

    // Update version entry in the database.
    $this->version = $version;
    $this->updated = time();
    $saved = $this->save();
    if (empty($saved)) {
      $this->output("\n@redCould not save new version number to database...@end");
    }

    $this->output("\n@greenFinished updating to version " . $version ."@end");
  }

  /**
   * Process an UpdateQuery through Database and output results.
   * @param $query The query statement to process.
   * @return Boolean Returns if the query was successfully performed or not.
   */
  protected function processQuery (\Agnate\RPG\Update\UpdateQuery $query) {
    // Run the query.
    $success = $query->execute();

    if ($success) $status = '[  @greenOK@end  ]';
    else $status = '[ @greenFAIL@end ]';

    $this->outputTableRow((string) $query, $status);

    return $success;
  }

  /**
   * Output a message to the screen.
   */
  public function output ($message, $linebreak = TRUE, $use_indent = TRUE) {
    // Add the indenting.
    if ($use_indent && $this->output_indent > 0) {
      $message = $this->getOutputIndent() . $message;
    }

    // If we're outputting to the CLI, print to screen.
    if ($this->output_CLI) {
      print $this->replaceColors($message) . ($linebreak ? "\n" : "");
    }
  }

  /**
   * Get the current indent spacing.
   */
  protected function getOutputIndent() {
    return implode('', array_fill(0, $this->output_indent, '  '));
  }

  /**
   * Replace colour tokens in message with CLI colours.
   * @param $message String message possibly containing colours to replace.
   */
  protected function replaceColors($message) {
    // Colour tokens.
    $colors = array(
      '@blue' => "\033[0;34m",
      '@green' => "\033[0;32m",
      '@cyan' => "\033[0;36m",
      '@red' => "\033[0;31m",
      '@purple' => "\033[0;35m",
      '@brown' => "\033[0;33m",
      '@lgray' => "\033[0;37m",
      '@gray' => "\033[1;30m",
      '@lblue' => "\033[1;34m",
      '@lgreen' => "\033[1;32m",
      '@lcyan' => "\033[1;36m",
      '@lred' => "\033[1;31m",
      '@lpurple' => "\033[1;35m",
      '@yellow' => "\033[1;33m",
      '@end' => "\033[0m",
    );

    return str_replace(array_keys($colors), array_values($colors), $message);
  }

  /**
   * Output a table (primarily for query status updates).
   * CLI: outputTable($mask, [$arg1, $arg2, etc.]);
   */
  public function outputTableRow() {
    $args = func_get_args();
    $mask_len = 90;
    $mask = $this->getOutputIndent() . "%-". $mask_len . "." . $mask_len . "s      %20.20s \n";
    
    if ($this->output_CLI) {
      foreach ($args as $key => $value) {
        $args[$key] = $this->replaceColors($value);
      }
      // If the first row is longer than the mask, print onto multiple rows with status at the end.
      $rows = array();
      $description = $args[0];
      $count = 0;
      while (strlen($description) > 0) {
        $rows[] = array(
          $mask,
          substr($description, 0, $mask_len),
          (!$count ? $args[1] : ''),
        );
        $description = substr($description, $mask_len);
        $count++;
      }

      // Print each row.
      foreach ($rows as $row) {
        call_user_func_array('printf', $row);
      }
    }
  }

  /**
   * Increase the indenting of any output by 1.
   */
  public function outputIndent() {
    $this->output_indent++;
  }

  /**
   * Decrease the indenting of any output by 1.
   */
  public function outputOutdent() {
    $this->output_indent--;
  }

  /**
   * Reset the indenting of any output to have no indentation.
   */
  public function outputResetIndent() {
    $this->output_indent = 0;
  }

  /**
   * Get all of the Update classes for every in-between version.
   * @param $version A version code to compare to current Updater's version. Example: '1.2.5'
   * @param $force_cur Include the current version in the update (mostly for debugging Updater scripts)
   * @return Array List of Update classes to run updates for.
   */
  protected function versionDiff ($version, $force_cur = FALSE) {
    if (!$this->validVersion($version)) throw new \Exception ('Invalid version sent to Updater->versionDiff(), ' . $version . ' given.');
    // Separate version code by decimal.
    $info = explode('.', $this->version);
    $upinfo = explode('.', $version);
    $versions = array();

    // If there aren't 3 parts to each version, we're done.
    if (count($info) < 3 || count($upinfo) < 3) return $versions;

    // Convert strings to numbers.
    foreach ($info as $key => $value) $info[$key] = (int)$value;
    foreach ($upinfo as $key => $value) $upinfo[$key] = (int)$value;

    // Keep track of indexes.
    $iprime = 0;
    $imajor = 1;
    $iminor = 2;

    // Track if we've tested all versions.
    $prime = FALSE;
    $major = FALSE;

    while (TRUE) {
      $info[$iminor]++;

      if ($this->versionInfoGTEUpdate($info, $upinfo)) break;

      $update_name = '\Agnate\RPG\Update\Update_' . implode('_', $info);
      
      // Check if there are any versions with the next number.
      if (@class_exists($update_name)) {
        $major = FALSE;
        $prime = FALSE;
        $versions[implode('.', $info)] = $update_name;
      }
      // Otherwise, skip to the next version.
      else {
        // Reset the minor.
        $info[$iminor] = -1;
        // If we didn't increment the major last time, do it now.
        if ($major == FALSE) {
          // Increase the major and mark it.
          $info[$imajor]++;
          $major = TRUE;
        }
        // If we already increased the major and didn't find anything, increase the prime.
        else if ($prime == FALSE) {
          // Reset the major.
          $info[$imajor] = 0;
          $major = FALSE;
          // Increase the prime and mark it.
          $info[$iprime]++;
          $prime = TRUE;
        }
        // We checked for a major increase AND a prime increase, and found nothing, so we're done!
        else {
          break;
        }
      }
    }

    // If we're forcing the current version to re-run, add it now.
    if ($force_cur) {
      $update_name = '\Agnate\RPG\Update\Update_' . implode('_', $upinfo);
      if (@class_exists($update_name)) {
        $versions[implode('.', $upinfo)] = $update_name;
      }
    }

    return $versions;
  }

  /**
   * Compare versions and see if the second one is >= the first one.
   */
  protected function versionInfoGTEUpdate (Array $info, Array $upinfo) {
    // Convert to a number (remove decimals) and check value.
    $numinfo = implode('', $info);
    $numup = implode('', $upinfo);
    return ((int)$numinfo > (int)$numup);
  }

  /**
   * Compare a version against this Updater's version and see if it's newer.
   * @param $version A version code to check. Example: '1.2.5'
   */
  public function versionGTE ($version) {
    return $this->validVersion($version) && $this->versionInfoGTEUpdate(explode('.', $this->version), explode('.', $version));
  }

  /**
   * Check if the version is valid.
   * @param $version A version code to check. Example: '1.2.5'
   */
  protected function validVersion ($version) {
    return (is_string($version) && count(explode('.', $version)) == 3);
  }

}