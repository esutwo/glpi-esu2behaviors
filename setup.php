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

// Init the hooks of the plugins -Needed
function plugin_init_esu2behaviors() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   Plugin::registerClass('PluginEsu2behaviorsConfig', ['addtabon' => 'Config']);
   $PLUGIN_HOOKS['config_page']['esu2behaviors'] = 'front/config.form.php';

   $PLUGIN_HOOKS['pre_item_add']['esu2behaviors'] =
      ['ITILFollowup'       => ['PluginEsu2BehaviorsITILFollowup',      'beforeAdd']];

   // End init, when all types are registered
   //$PLUGIN_HOOKS['post_init']['esu2behaviors'] = ['PluginEsu2BehaviorsCommon', 'postInit'];

   $PLUGIN_HOOKS['csrf_compliant']['esu2behaviors'] = true;

   foreach ($CFG_GLPI["asset_types"] as $type) {
      $PLUGIN_HOOKS['item_can']['esu2behaviors'][$type] = [$type => ['PluginEsu2behaviorsConfig', 'item_can']];
   }

   $PLUGIN_HOOKS['add_default_where']['esu2behaviors'] = ['PluginEsu2behaviorsConfig', 'add_default_where'];

}


function plugin_version_esu2behaviors() {

   return ['name'           => __('ESU2 Behaviors', 'esu2behaviors'),
           'version'        => '0.0.1',
           'license'        => 'AGPLv3+',
           'author'         => 'Cody Ernesti',
           'homepage'       => 'https://github.com/esu2two/glpi-esu2behaviors',
           'minGlpiVersion' => '10.0.2',
           'requirements'   => ['glpi' => ['min' => '10.0.0',
                                           'max' => '10.1.0']]];
}


// Check configuration process for plugin : need to return true if succeeded
// Can display a message only if failure and $verbose is true
function plugin_esu2behaviors_check_config($verbose=false) {
   return true;
}
