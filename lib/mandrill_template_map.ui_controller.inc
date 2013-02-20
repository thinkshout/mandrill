<?php
/**
 * @file
 * The UI controller for the template map entity.
 */

/**
 * Override EntityDefaultUIController to customize our menu items.
 */
class MandrillTemplateMapUIController extends EntityDefaultUIController {

  public function hook_menu() {
    $items = parent::hook_menu();
    $items[$this->path]['description'] = 'Manage Mandrill Template Mapping entity settings.';
    $items[$this->path]['type'] = MENU_LOCAL_TASK;
    return $items;
  }

}
