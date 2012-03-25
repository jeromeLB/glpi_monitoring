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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringMessage extends CommonDBTM {
   
   
   static function getMessages() {
      $pmMessage = new self();

      $servicecatalog = '';
      $confchanges = '';
      
      $servicecatalog = $pmMessage->servicescatalogMessage();
      $confchanges = $pmMessage->configurationchangesMessage();
      $i = 0;
      if ($servicecatalog != ''
              OR $confchanges != '') {
         echo "<table class='tab_cadre' width='600'>";
         echo "<tr class='tab_bg_1'>";
         echo "<th><font class='red'>";
         if ($confchanges != '') {
            echo $confchanges;
            $i++;
         }
         if ($servicecatalog != '') {
            if($i > 0) {
               echo "</font></th>";
               echo "</tr>";
               echo "<tr class='tab_bg_1'>";
               echo "<th><font class='red'>";
            }
            echo $servicecatalog;
            $i++;
         }
         echo "</font></th>";
         echo "</tr>";
         echo "</table>";
         echo "<br/>";
      }      
   }
   
   
   
   /**
    * This fonction search if a services catalog has a resource deleted
    * 
    */
   function servicescatalogMessage() {
      global $DB,$LANG;
      
      $pmServicescatalog = new PluginMonitoringServicescatalog();
      $input = '';
      $a_catalogs = array();
      
      $query = "SELECT `glpi_plugin_monitoring_businessrules`.`id` FROM `glpi_plugin_monitoring_businessrules`
         
         LEFT JOIN `glpi_plugin_monitoring_services` ON `plugin_monitoring_services_id` = `glpi_plugin_monitoring_services`.`id`

         WHERE `glpi_plugin_monitoring_services`.`id` IS NULL";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $pmServicescatalog->getFromDB($data['id']);
         $a_catalogs[$data['id']] = $pmServicescatalog->getLink();
      }
      if (count($a_catalogs) > 0) {
         $input = $LANG['plugin_monitoring']['servicescatalog'][2]." : <br/>";
         $input .= implode(" - ", $a_catalogs);
      }
      return $input;
   }

   
   
   /**
    * Get modifications of resources (if have modifications);
    */
   function configurationchangesMessage() {
      global $DB,$LANG;
      
      $input = '';
      
      // Get id of last Shinken restart
      $id_restart = 0;
      $a_restarts = $this->find("`action`='restart'", "`id` DESC", 1);
      if (count($a_restarts) > 0) {
         $a_restart = current($a_restarts);
         $id_restart = $a_restart['id'];
      }
      // get number of modifications
      $nb_delete  = 0;
      $nb_add     = 0;
      $nb_delete = countElementsInTable(getTableForItemType('PluginMonitoringLog'), "`id` > '".$id_restart."'
         AND `action`='delete'");
      $nb_add = countElementsInTable(getTableForItemType('PluginMonitoringLog'), "`id` > '".$id_restart."'
         AND `action`='add'");
      
      if ($nb_delete > 0 OR $nb_add > 0) {
         $input .= $LANG['plugin_monitoring']['log'][1]."<br/>";
         if ($nb_add > 0) {
            $input .= $nb_add." ".$LANG['plugin_monitoring']['log'][2];
         }
         if ($nb_delete > 0) {
            if ($nb_add > 0) {
               $input .= " / ";
            }
            $input .= $nb_delete." ".$LANG['plugin_monitoring']['log'][3];
         }
         $input .= "<br/>";
         $input .= $LANG['plugin_monitoring']['log'][4];
      }
      return $input;
   }
   
   
}

?>