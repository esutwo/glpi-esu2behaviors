<?php
/**
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Behaviors plugin for GLPI.

 Behaviors is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Behaviors is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Behaviors. If not, see <http://www.gnu.org/licenses/>.

 @package   behaviors
 @author    Remi Collet, Nelly Mahu-Lasson
 @copyright Copyright (c) 2010-2022 Behaviors plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/behaviors
 @link      http://www.glpi-project.org/
 @since     2010

 --------------------------------------------------------------------------
*/

class PluginEsu2behaviorsConfig extends CommonDBTM {

   static private $_instance = NULL;
   static $rightname         = 'config';


   static function canCreate() {
      return Session::haveRight('config', UPDATE);
   }


   static function canView() {
      return Session::haveRight('config', READ);
   }


   static function getTypeName($nb=0) {
      return __('Setup');
   }


   function getName($with_comment=0) {
      return __('ESU2 Behaviors', 'esu2behaviors');
   }


   /**
    * Singleton for the unique config record
    */
   static function getInstance() {

      if (!isset(self::$_instance)) {
         self::$_instance = new self();
         if (!self::$_instance->getFromDB(1)) {
            self::$_instance->getEmpty();
         }
      }
      return self::$_instance;
   }


   static function install(Migration $mig) {
      global $DB;

      $table = 'glpi_plugin_esu2behaviors_configs';
      $default_charset   = DBConnection::getDefaultCharset();
      $default_collation = DBConnection::getDefaultCollation();
      $default_key_sign  = DBConnection::getDefaultPrimaryKeySignOption();
      if (!$DB->tableExists($table)) { //not installed

         $query = "CREATE TABLE `". $table."`(
                     `id` int $default_key_sign NOT NULL,
                     `addfup_updatetech` tinyint NOT NULL default '0',
                     `date_mod` timestamp NULL default NULL,
                     `comment` text,
                     PRIMARY KEY  (`id`)
                   ) ENGINE=InnoDB  DEFAULT CHARSET = {$default_charset}
                     COLLATE = {$default_collation} ROW_FORMAT=DYNAMIC";
         $DB->queryOrDie($query, __('Error in creating glpi_plugin_esu2behaviors_configs', 'esu2behaviors').
                                 "<br>".$DB->error());

         $query = "INSERT INTO `$table`
                         (id, date_mod)
                   VALUES (1, NOW())";
         $DB->queryOrDie($query, __('Error during update glpi_plugin_esu2behaviors_configs', 'esu2behaviors').
                                 "<br>" . $DB->error());

      } else {
         // Upgrade

         // none for now
      }

   }


   static function uninstall(Migration $mig) {
      $mig->dropTable('glpi_plugin_esu2behaviors_configs');
   }


   static function showConfigForm($item) {

      $yesnoall = [0 => __('No'),
                   1 => __('First'),
                   2 => __('All')];

      $config = self::getInstance();

      $config->showFormHeader();

      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='4' class='center' width='60%'>".__('Update of a ticket')."</th>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>". __('Technician assignment when adding follow up', 'esu2behaviors');
      echo "</td><td>";
      Dropdown::showYesNo("addfup_updatetech", $config->fields['addfup_updatetech']);
      echo "</td><td colspan='2'></td></tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td rowspan='7' colspan='2' class='center'>";
      Html::textarea(['name'            => 'comment',
                      'value'           => $config->fields['comment'],
                      'cols'            => '60',
                      'rows'            => '12',
                      'enable_ricktext' => false]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='2'></th>";
      echo "<th colspan='2'>".sprintf(__('%1$s %2$s'), __('Last update'),
                                      Html::convDateTime($config->fields["date_mod"]));
      echo "</td></tr>";

      $config->showFormButtons(['formfooter' => true, 'candel'=>false]);

      return false;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()=='Config') {
            return self::getName();
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType()=='Config') {
         self::showConfigForm($item);
      }
      return true;
   }


   /**
    * Restrict visibility rights
    *
    * @since 1.5.0
    *
    * @param  $item
   **/
   static function item_can($item) {
      global $DB, $CFG_GLPI;

      $itemtype = $item->getType();
      if (in_array($item->getType(), $CFG_GLPI["asset_types"])
          && !Session::haveRight($itemtype::$rightname, UPDATE)) {

         $config = PluginEsu2behaviorsConfig::getInstance();
         if ($config->getField('myasset')
             && ($item->fields['users_id'] > 0)
             && ($item->fields['users_id'] <> Session::getLoginUserID())) {

            if ($config->getField('groupasset')
                && ($item->fields['groups_id'] > 0)
                && !in_array($item->fields['groups_id'], $_SESSION["glpigroups"])) {
               $item->right = '0';
            }
         }
         if ($config->getField('groupasset')
              && ($item->fields['groups_id'] > 0)
              && !in_array($item->fields['groups_id'], $_SESSION["glpigroups"])) {

            if ($config->getField('myasset')
                && ($item->fields['users_id'] > 0)
                && ($item->fields['users_id'] <> Session::getLoginUserID())) {
               $item->right = '0';
            }
         }
      }
   }


   /**
    * Restrict visibility rights
    *
    * @since 1.5.0
    *
    * @param  $item
   **/
   static function add_default_where($item) {
      global $DB, $CFG_GLPI;;

      $condition = "";
      list($itemtype, $condition) = $item;

      if (isCommandLine()) {
         return [$itemtype, $condition];
      }

      $dbu = new DbUtils();

      $config = PluginEsu2behaviorsConfig::getInstance();
      if (in_array($itemtype, $CFG_GLPI["asset_types"])
          && !Session::haveRight($itemtype::$rightname, UPDATE)) {

         $dbu = new DbUtils();
         $table  = $dbu->getTableForItemType($itemtype);
         if ($config->getField('myasset')) {
            $condition .= "(`".$table."`.`users_id` = ".Session::getLoginUserID().")";
            if ($config->getField('groupasset')
                && count($_SESSION["glpigroups"])) {
               $condition .= " OR ";
            }
         }
         if ($config->getField('groupasset')
             && count($_SESSION["glpigroups"])) {
            $condition .= " (`".$table."`.`groups_id` IN ('".implode("','", $_SESSION["glpigroups"])."'))";
         }
      }

      $filtre = [];
      if ($itemtype == 'AllAssets') {
         foreach ($CFG_GLPI[$CFG_GLPI["union_search_type"][$itemtype]] as $ctype) {
            if (($citem = $dbu->getItemForItemtype($ctype))
                && !$citem->canUpdate()) {
               $filtre[$ctype] = $ctype;
            }
         }

         if (count($filtre)) {
            if ($config->getField('myasset')) {
               $condition .= " (`asset_types`.`users_id` = ".Session::getLoginUserID().")";
               if ($config->getField('groupasset')
                   && count($_SESSION["glpigroups"])) {
                  $condition .= " OR ";
               }
            }
            if ($config->getField('groupasset')
                && count($_SESSION["glpigroups"])) {
               $condition .= " (`asset_types`.`groups_id` IN ('".implode("','", $_SESSION["glpigroups"])."'))";
            }
         }
      }
      return [$itemtype, $condition];
   }
}
