<?php
/**
 * @file
 * The controller for the template map entity containing the CRUD operations.
 */

/**
 *
 */
class MandrillTemplateMapEntityController extends EntityAPIController {

  /**
   * Saves a template map.
   *
   * @param $map
   *   The full template map object to save.
   *
   * @return
   *   The saved template map object.
   */
  //@todo: review save & delete functions-- templated strictly from redhen_contact
  public function save($map, DatabaseTransaction $transaction = NULL) {
    $transaction = isset($transaction) ? $transaction : db_transaction();
    try {
      $map->updated = REQUEST_TIME;

      // New map, set created prop.
      if (isset($map->is_new) && $map->is_new) {
        $map->created = REQUEST_TIME;
      }
      // Set revision flags. By default, save a new revision and set it as
      // default.
      else {
        if (!isset($map->is_new_revision)) {
          $map->is_new_revision = TRUE;
        }
        if (!isset($map->default_revision)) {
          $map->default_revision = TRUE;
        }
      }

      parent::save($map, $transaction);

      return $map;
    }
    catch (Exception $e) {
      $transaction->rollback();
      watchdog_exception($this->entityType, $e);
      throw $e;
    }
  }

  /**
   * Deletes multiple contacts by ID.
   *
   * @param $map_ids
   *   An array of contact IDs to delete.
   *
   * @return
   *   TRUE on success, FALSE otherwise.
   */
  public function delete($map_ids, DatabaseTransaction $transaction = NULL) {
    if (!empty($map_ids)) {
      $maps = $this->load($map_ids, array());

      // If none of the specified maps can be deleted, return FALSE.
      if (empty($maps)) {
        return FALSE;
      }

      $transaction = db_transaction();
      try {
        parent::delete($map_ids, $transaction);
      }
      catch (Exception $e) {
        if (isset($transaction)) {
          $transaction->rollback();
        }
        watchdog_exception($this->entityType, $e);
        throw $e;
      }
    }

    return TRUE;
  }
}
