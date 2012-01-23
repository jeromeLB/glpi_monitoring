<?php

/*
   ------------------------------------------------------------------------
   Plugin Monitoring for GLPI
   Copyright (C) 2011-2012 by the Plugin Monitoring for GLPI Development Team.

   https://forge.indepnet.net/projects/monitoring/
   ------------------------------------------------------------------------

   LICENSE

   This file is part of Plugin Monitoring project.

   Plugin Monitoring for GLPI is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Plugin Monitoring for GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Behaviors. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Monitoring for GLPI
   @author    David Durieux
   @co-author 
   @comment   
   @copyright Copyright (c) 2011-2012 Plugin Monitoring for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/monitoring/
   @since     2011
 
   ------------------------------------------------------------------------
 */

define('GLPI_ROOT', '../../..');

include (GLPI_ROOT . "/inc/includes.php");

checkCentralAccess();

commonHeader($LANG['plugin_monitoring']['title'][0],$_SERVER["PHP_SELF"], "plugins",
             "monitoring", "host");

$pluginMonitoringHost_Host = new PluginMonitoringHost_Host();
if (isset($_POST['parent_add'])) {
   // Add host in dependencies/parent of host

   $input = array();
   $input['plugin_monitoring_hosts_id_1'] = $_POST['id'];
   $input['plugin_monitoring_hosts_id_2'] = $_POST['parent_to_add'];
   $pluginMonitoringHost_Host->add($input);

   glpi_header($_SERVER['HTTP_REFERER']);
} else if (isset($_POST['parent_delete'])) {
   // Delete host in dependencies/parent of host

   foreach ($_POST['parent_to_delete'] as $delete_id) {
      $query = "DELETE FROM ".$pluginMonitoringHost_Host->getTable()."
         WHERE `plugin_monitoring_hosts_id_1`='".$_POST['id']."'
            AND `plugin_monitoring_hosts_id_2`='".$delete_id."'";
      $DB->query($query);
   }
   glpi_header($_SERVER['HTTP_REFERER']);
}

commonFooter();

?>