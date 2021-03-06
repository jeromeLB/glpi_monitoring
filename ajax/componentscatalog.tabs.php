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

header("Content-Type: text/html; charset=UTF-8");
header_nocache();

if (!isset($_POST["id"])) {
   exit();
}
if (!isset($_POST["sort"])) {
   $_POST["sort"] = "";
}
if (!isset($_POST["order"])) {
   $_POST["order"] = "";
}
if (!isset($_POST["withtemplate"])) {
   $_POST["withtemplate"] = "";
}


$pmComponentscatalog = new PluginMonitoringComponentscatalog();

if ($_POST["id"]>0 && $pmComponentscatalog->can($_POST["id"],'r')) {

   switch($_POST['glpi_tab']) {
      case -1 :

         break;

      case 1:
         $pmComponentscatalog_Component = new PluginMonitoringComponentscatalog_Component();
         $pmComponentscatalog_Component->showComponents($_POST['id']);         
         break;
      
      case 2 :
         $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
         $pmComponentscatalog_Host->showHosts($_POST['id'], 1);
         break;
      
      case 3 :
         $pmComponentscatalog_rule = new PluginMonitoringComponentscatalog_rule();
         $pmComponentscatalog_rule->showRules($_POST['id']);
         break;
      
      case 4 :
         $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
         $pmComponentscatalog_Host->showHosts($_POST['id'], 0);
         break;
      
      case 5 : 
         $pmContact_Item = new PluginMonitoringContact_Item();
         $pmContact_Item->showContacts("PluginMonitoringComponentscatalog", $_POST['id']);
         break;

      case 6:
         $pmUnavaibility = new PluginMonitoringUnavaibility();
         $pmUnavaibility->displayComponentscatalog($_POST['id']);
         break;
      
      default :

   }
}

ajaxFooter();

?>
