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

class PluginMonitoringComponentscatalog extends CommonDropdown {
   
   /**
   * Get name of this type
   *
   *@return text name of this type by language of the user connected
   *
   **/
   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_monitoring']['componentscatalog'][0];
   }



   function canCreate() {
      return PluginMonitoringProfile::haveRight("componentscatalog", 'w');
   }


   
   function canView() {
      return PluginMonitoringProfile::haveRight("componentscatalog", 'r');
   }


   
   function canCancel() {
      return PluginMonitoringProfile::haveRight("componentscatalog", 'w');
   }


   
   function canUndo() {
      return PluginMonitoringProfile::haveRight("componentscatalog", 'w');
   }

   
   
   function defineTabs($options=array()){
      global $LANG;

      $ong = array();
      
      if ($_GET['id'] > 0) {
         $ong[1] = $LANG['plugin_monitoring']['component'][0];
         $ong[2] = $LANG['plugin_monitoring']['component'][3];
         $ong[3] = $LANG['rulesengine'][17];
         $ong[4] = $LANG['plugin_monitoring']['component'][4];
         $ong[5] = $LANG['plugin_monitoring']['contact'][20];
         $ong[6] = $LANG['plugin_monitoring']['availability'][0];
      }
      
      return $ong;
   }
   
   
   
   function getAdditionalFields() {
      global $LANG;

      return array(array('name'  => 'notification_interval',
                         'label' => $LANG['plugin_monitoring']['componentscatalog'][1],
                         'type'  => 'notificationinterval'));
   }
   
   
   
   function displaySpecificTypeField($ID, $field=array()) {
      
      
      switch ($field['type']) {
         case 'notificationinterval' :
            if ($ID > 0) {
//               $this->fields['notification_interval'];
            } else {
               $this->fields['notification_interval'] = 30;
            }
            Dropdown::showInteger('notification_interval', $this->fields['notification_interval'], 1, 1000);
            break;
      }
   }
   
   
   
   function showChecks() {
      global $DB,$CFG_GLPI,$LANG;
      

      echo "<table class='tab_cadre' width='100%'>";
      echo "<tr class='tab_bg_4' style='background: #cececc;'>";
      
      $a_componentscatalogs = $this->find();
      $i = 0;
      foreach ($a_componentscatalogs as $data) {
         echo "<td>";

         echo $this->showWidget($data['id']);
         
         echo "</td>";
         
         $i++;
         if ($i == '6') {
            echo "</tr>";
            echo "<tr class='tab_bg_4' style='background: #cececc;'>";
            $i = 0;
         }
      }      
      
      echo "</tr>";
      echo "</table>";      
   }
   
   
   
   static function replayRulesCatalog($item) {
      global $DB;
      
      $datas = getAllDatasFromTable("glpi_plugin_monitoring_componentscatalogs_rules", 
              "`plugin_monitoring_componentscalalog_id`='".$item->getID()."'");
      $pmComponentscatalog_rule = new PluginMonitoringComponentscatalog_rule();
      foreach($datas as $data) {
         $pmComponentscatalog_rule->getFromDB($data['id']);
         PluginMonitoringComponentscatalog_rule::getItemsDynamicly($pmComponentscatalog_rule);
      }
   }
  
   
   
   static function removeCatalog($item) {
      global $DB;
      
      $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
      $pmComponentscatalog_rule = new PluginMonitoringComponentscatalog_rule(); 
      
      $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
         WHERE `plugin_monitoring_componentscalalog_id`='".$item->fields["id"]."'
            AND `is_static`='1'";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $pmComponentscatalog_Host->delete($data);
      }
      
      $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_rules`
         WHERE `plugin_monitoring_componentscalalog_id`='".$item->fields["id"]."'";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $pmComponentscatalog_rule->delete($data);
      }
   }
   
   
   
   function showWidget($id) {
      global $LANG, $DB, $CFG_GLPI;
      
      $pmService = new PluginMonitoringService();
      $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
      
      $input = '';
      
      $this->getFromDB($id);
      $data = $this->fields;
      $input .= '<table  class="tab_cadre_fixe" style="width:158px;">';
      $input .= '<tr class="tab_bg_1">';
      $input .= '<th colspan="2" style="font-size:18px;" height="60">';
      $input .= $data['name']."&nbsp;";
      $input .= '</th>';
      $input .= '</tr>';
         
      $stateg = array();
      $stateg['OK'] = 0;
      $stateg['WARNING'] = 0;
      $stateg['CRITICAL'] = 0;
      $a_gstate = array();
      $nb_ressources = 0;
      $query = "SELECT * FROM `".$pmComponentscatalog_Host->getTable()."`
         WHERE `plugin_monitoring_componentscalalog_id`='".$data['id']."'";
      $result = $DB->query($query);
      while ($dataComponentscatalog_Host=$DB->fetch_array($result)) {
         $queryService = "SELECT * FROM `".$pmService->getTable()."`
            WHERE `plugin_monitoring_componentscatalogs_hosts_id`='".$dataComponentscatalog_Host['id']."'
               AND `entities_id` IN (".$_SESSION['glpiactiveentities_string'].")";
         $resultService = $DB->query($queryService);
         while ($dataService=$DB->fetch_array($resultService)) {
            $nb_ressources++;
            $state = array();
            $state['OK'] = 0;
            $state['WARNING'] = 0;
            $state['CRITICAL'] = 0;
            if ($dataService['state_type'] != "HARD") {
               $a_gstate[$dataService['id']] = "OK";
            } else {
               switch($dataService['state']) {

                  case 'UP':
                  case 'OK':
                     $state['OK']++;
                     break;

                  case 'DOWN':
                  case 'UNREACHABLE':
                  case 'CRITICAL':
                  case 'DOWNTIME':
                     $state['CRITICAL']++;
                     break;

                  case 'WARNING':
                  case 'UNKNOWN':
                  case 'RECOVERY':
                  case 'FLAPPING':
                     $state['WARNING']++;
                     break;

               }
               if ($state['CRITICAL'] >= 1) {
                  $a_gstate[$dataService['id']] = "CRITICAL";
               } else if ($state['WARNING'] >= 1) {
                  $a_gstate[$dataService['id']] = "WARNING";
               } else {
                  $a_gstate[$dataService['id']] = "OK";
               }
            }
         }
      }
      foreach ($a_gstate as $value) {
         $stateg[$value]++;
      }
      $input .= '<tr class="tab_bg_1">';
      $input .= '<td>';
      $input .= $LANG['plugin_monitoring']['service'][0]."&nbsp;:";
      $input .= '</td>';
      $input .= '<th align="center" height="40" width="50%">';
      $link = $CFG_GLPI['root_doc'].
         "/plugins/monitoring/front/service.php?reset=reset&field[0]=8&searchtype[0]=equals&contains[0]=".$id.
            "&itemtype=PluginMonitoringService&start=0&glpi_tab=3";
      $input .= '<a href="'.$link.'">'.$nb_ressources.'</a>';
      $input .= '</th>';
      $input .= '</tr>';

      $background = '';
      $count = 0;
            
      $link = '';
      if ($stateg['CRITICAL'] > 0) {
         $count = $stateg['CRITICAL'];
         $background = 'background="'.$CFG_GLPI['root_doc'].'/plugins/monitoring/pics/bg_critical.png"';
         $link = $CFG_GLPI['root_doc'].
         "/plugins/monitoring/front/service.php?reset=reset&field[0]=3&searchtype[0]=equals&contains[0]=CRITICAL".
            "&link[1]=AND&field[1]=8&searchtype[1]=equals&contains[1]=".$id.
            "&link[2]=OR&field[2]=3&searchtype[2]=equals&contains[2]=DOWN".
            "&link[3]=AND&field[3]=8&searchtype[3]=equals&contains[3]=".$id.
            "&link[4]=OR&field[4]=3&searchtype[4]=equals&contains[4]=UNREACHABLE".
            "&link[5]=AND&field[5]=8&searchtype[5]=equals&contains[5]=".$id.
            "&itemtype=PluginMonitoringService&start=0&glpi_tab=3";
      } else if ($stateg['WARNING'] > 0) {
         $count = $stateg['WARNING'];
         $background = 'background="'.$CFG_GLPI['root_doc'].'/plugins/monitoring/pics/bg_warning.png"';
         $link = $CFG_GLPI['root_doc'].
         "/plugins/monitoring/front/service.php?reset=reset&field[0]=3&searchtype[0]=equals&contains[0]=WARNING".
            "&link[1]=AND&field[1]=8&searchtype[1]=equals&contains[1]=".$id.
            "&link[2]=OR&field[2]=3&searchtype[2]=equals&contains[2]=UNKNOWN".
            "&link[3]=AND&field[3]=8&searchtype[3]=equals&contains[3]=".$id.
            "&link[4]=OR&field[4]=3&searchtype[4]=equals&contains[4]=RECOVERY".
            "&link[5]=AND&field[5]=8&searchtype[5]=equals&contains[5]=".$id.
            "&link[6]=OR&field[6]=3&searchtype[6]=equals&contains[6]=FLAPPING".
            "&link[7]=AND&field[7]=8&searchtype[7]=equals&contains[7]=".$id.
            "&itemtype=PluginMonitoringService&start=0&glpi_tab=3";
      } else if ($stateg['OK'] > 0) {
         $count = $stateg['OK'];
         $background = 'background="'.$CFG_GLPI['root_doc'].'/plugins/monitoring/pics/bg_ok.png"';
         $link = $CFG_GLPI['root_doc'].
         "/plugins/monitoring/front/service.php?reset=reset&field[0]=3&searchtype[0]=equals&contains[0]=OK".
            "&link[1]=AND&field[1]=8&searchtype[1]=equals&contains[1]=".$id.
            "&link[2]=OR&field[2]=3&searchtype[2]=equals&contains[2]=UP".
            "&itemtype=PluginMonitoringService&start=0&glpi_tab=3";
      }
      $input .= "<tr ".$background.">";
      $input .= '<th style="background-color:transparent;" '.$background.' colspan="2" height="100">';
      $input .= '<a href="'.$link.'"><font style="font-size: 52px; color:black">'.$count.'</font></a>';         
      $input .= '</th>';
      $input .= '</tr>';

      $input .= '</table>';
      return $input;
   }
   
}

?>