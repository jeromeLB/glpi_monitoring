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

function pluginMonitoringInstall($version) {
   global $DB,$LANG,$CFG_GLPI;

   // ** Insert in DB
   $DB_file = GLPI_ROOT ."/plugins/monitoring/install/mysql/plugin_monitoring-"
              .$version."-empty.sql";
   $DBf_handle = fopen($DB_file, "rt");
   $sql_query = fread($DBf_handle, filesize($DB_file));
   fclose($DBf_handle);
   foreach ( explode(";\n", "$sql_query") as $sql_line) {
      if (get_magic_quotes_runtime()) $sql_line=stripslashes_deep($sql_line);
      if (!empty($sql_line)) $DB->query($sql_line);
   }

   include (GLPI_ROOT . "/plugins/monitoring/inc/command.class.php");
   $pluginMonitoringCommand = new PluginMonitoringCommand();
   $pluginMonitoringCommand->initCommands();
   include (GLPI_ROOT . "/plugins/monitoring/inc/notificationcommand.class.php");
   $pluginMonitoringNotificationcommand = new PluginMonitoringNotificationcommand();
   $pluginMonitoringNotificationcommand->initCommands();
   include (GLPI_ROOT . "/plugins/monitoring/inc/check.class.php");
   $pluginMonitoringCheck = new PluginMonitoringCheck();
   $pluginMonitoringCheck->initChecks();
   
   if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/monitoring')) {
      mkdir(GLPI_PLUGIN_DOC_DIR."/monitoring");
   }
   if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/monitoring/templates')) {
      mkdir(GLPI_PLUGIN_DOC_DIR."/monitoring/templates");
   }
   
   
   
   // initialise services suggests
   if (!class_exists('PluginMonitoringServicesuggest')) { // if plugin is unactive
      include(GLPI_ROOT . "/plugins/monitoring/inc/servicesuggest.class.php");
   }
   $pMonitoringServicesuggest = new PluginMonitoringServicesuggest();
   $pMonitoringServicesuggest->initSuggest();
   
   CronTask::Register('PluginMonitoringServiceevent', 'updaterrd', '300', 
                      array('mode' => 2, 'allowmode' => 3, 'logs_lifetime'=> 30));
  
   
}


function pluginMonitoringUninstall() {
   global $DB;

   $query = "SHOW TABLES;";
   $result=$DB->query($query);
   while ($data=$DB->fetch_array($result)) {
      if (strstr($data[0],"glpi_plugin_monitoring_")) {
         $query_delete = "DROP TABLE `".$data[0]."`;";
         $DB->query($query_delete) or die($DB->error());
      }
   }
   return true;
}

?>