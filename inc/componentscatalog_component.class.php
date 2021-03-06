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

class PluginMonitoringComponentscatalog_Component extends CommonDBTM {
   

   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_monitoring']['component'][0];
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

   
   
   function showComponents($componentscatalogs_id) {
      global $DB,$LANG,$CFG_GLPI;

      $this->addComponent($componentscatalogs_id);
      
      $rand = mt_rand();
      
      $pmComponent = new PluginMonitoringComponent();
      $pmCommand   = new PluginMonitoringCommand();
      $pmCheck     = new PluginMonitoringCheck();
      $calendar    = new Calendar();
      
      echo "<form method='post' name='componentscatalog_component_form$rand' id='componentscatalog_component_form$rand' action=\"".
                $CFG_GLPI["root_doc"]."/plugins/monitoring/front/componentscatalog_component.form.php\">";
      
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr>";
      echo "<th>";
      echo $LANG['plugin_monitoring']['component'][2];
      echo "</th>";
      echo "</tr>";
      
      echo "</table>";
      
      echo "<table class='tab_cadre_fixe'>";
      
      echo "<tr>";
      echo "<th width='10'>&nbsp;</th>";
      echo "<th>".$LANG['common'][16]."</th>";
      echo "<th>".$LANG['plugin_monitoring']['command'][2]."</th>";
      echo "<th>".$LANG['plugin_monitoring']['check'][0]."</th>";      
      echo "<th>".$LANG['plugin_monitoring']['host'][9]."</th>";
      echo "<th>".$LANG['plugin_monitoring']['service'][8]."</th>";
      echo "</tr>";
      
      $used = array();
      $query = "SELECT * FROM `".$this->getTable()."`
         WHERE `plugin_monitoring_componentscalalog_id`='".$componentscatalogs_id."'";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $used[] = $data['plugin_monitoring_components_id'];
         $pmComponent->getFromDB($data['plugin_monitoring_components_id']);
         echo "<tr>";
         echo "<td>";
         echo "<input type='checkbox' name='item[".$data["id"]."]' value='1'>";
         echo "</td>";
         echo "<td class='center'>";
         echo $pmComponent->getLink(1);         
         echo "</td>";
         echo "<td class='center'>";
         $pmCommand->getFromDB($pmComponent->fields['plugin_monitoring_commands_id']);
         echo $pmCommand->getLink();
         echo "</td>";
         echo "<td class='center'>";
         $pmCheck->getFromDB($pmComponent->fields['plugin_monitoring_checks_id']);
         echo $pmCheck->getLink();
         echo "</td>";
         echo "<td class='center'>";
         $calendar->getFromDB($pmComponent->fields['calendars_id']);
         echo $calendar->getLink();
         echo "</td>";
         echo "<td class='center'>";
         if ($pmComponent->fields['remotesystem'] == '') {
            echo "-";
         } else {
            echo $pmComponent->fields['remotesystem'];
         }         
         echo "</td>";
         
         echo "</tr>";
      }
      
      openArrowMassive("componentscatalog_host_form$rand", true);
      closeArrowMassive('deleteitem', $LANG['buttons'][6]);
      
      echo "</table>";
      
   }
   
   
   function addComponent($componentscatalogs_id) {
      global $DB,$LANG;
      
      $this->getEmpty();

      $this->showFormHeader();      

      $used = array();
      $query = "SELECT * FROM `".$this->getTable()."`
         WHERE `plugin_monitoring_componentscalalog_id`='".$componentscatalogs_id."'";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $used[] = $data['plugin_monitoring_components_id'];
      }      
     
      echo "<tr>";
      echo "<td colspan='2'>";
      echo $LANG['plugin_monitoring']['component'][1]."&nbsp;:";
      echo "<input type='hidden' name='plugin_monitoring_componentscalalog_id' value='".$componentscatalogs_id."'/>";
      echo "</td>";
      echo "<td colspan='2'>";
      Dropdown::show("PluginMonitoringComponent", array('name'=>'plugin_monitoring_components_id',
                                                        'used'=>$used));
      echo "</td>";
      echo "</tr>";
      
      $this->showFormButtons();
   }
   
   
   
   function addComponentToItems($componentscatalogs_id, $components_id) {
      global $DB;
      
      $pmService = new PluginMonitoringService();
      $pmComponentscatalog_rule = new PluginMonitoringComponentscatalog_rule();
      $pmNetworkport = new PluginMonitoringNetworkport();
      
      $pluginMonitoringNetworkport = 0;
      $query = "SELECT * FROM `".$pmComponentscatalog_rule->getTable()."`
         WHERE `itemtype`='PluginMonitoringNetworkport'
            AND `plugin_monitoring_componentscalalog_id`='".$componentscatalogs_id."'
         LIMIT 1";
      $result = $DB->query($query);
      if ($DB->numrows($result) > 0) {
         $pluginMonitoringNetworkport = 1;
      }
      
      $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
         WHERE `plugin_monitoring_componentscalalog_id`='".$componentscatalogs_id."'";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $itemtype = $data['itemtype'];
         $item = new $itemtype();
         $item->getFromDB($data['items_id']);
         if ($pluginMonitoringNetworkport == '0') {
            $input = array();
            $input['entities_id'] = $item->fields['entities_id'];
            $input['plugin_monitoring_componentscatalogs_hosts_id'] = $data['id'];
            $input['plugin_monitoring_components_id'] = $components_id;
            $input['name'] = Dropdown::getDropdownName("glpi_plugin_monitoring_components", $components_id);
            $input['state'] = 'WARNING';
            $input['state_type'] = 'HARD';
            $pmService->add($input); 
         } else if ($pluginMonitoringNetworkport == '1') {
            $a_services_created = array();
            $querys = "SELECT * FROM `glpi_plugin_monitoring_services`
               WHERE `plugin_monitoring_components_id`='".$components_id."'
                  AND `plugin_monitoring_componentscatalogs_hosts_id`='".$data['id']."'";
            $results = $DB->query($querys);
            while ($datas=$DB->fetch_array($results)) {
               $a_services_created[$datas['networkports_id']] = $datas['id'];
            }
            
            $a_ports = $pmNetworkport->find("`itemtype`='".$data['itemtype']."'
               AND `items_id`='".$data['items_id']."'");
            foreach ($a_ports as $datap) {
               if (isset($a_services_created[$datap["id"]])) {
                  unset($a_services_created[$datap["id"]]);
               } else {               
                  $input = array();
                  $input['networkports_id'] = $datap['networkports_id'];
                  $input['entities_id'] =  $item->fields['entities_id'];
                  $input['plugin_monitoring_componentscatalogs_hosts_id'] = $data['id'];
                  $input['plugin_monitoring_components_id'] = $components_id;
                  $input['name'] = Dropdown::getDropdownName("glpi_plugin_monitoring_components", $components_id);
                  $input['state'] = 'WARNING';
                  $input['state_type'] = 'HARD';
                  $pmService->add($input);
               }
            }
            foreach ($a_services_created as $id) {
               $_SESSION['plugin_monitoring_hosts'] = $data;
               $pmService->delete(array('id'=>$id));
            }
         }
      }
   }
   
   
   
   function removeComponentToItems($componentscatalogs_id, $components_id) {
      global $DB;
      
      $pmService = new PluginMonitoringService();
      
      $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
         WHERE `plugin_monitoring_componentscalalog_id`='".$componentscatalogs_id."'";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $querys = "SELECT * FROM `glpi_plugin_monitoring_services`
            WHERE `plugin_monitoring_componentscatalogs_hosts_id`='".$data['id']."'
               AND `plugin_monitoring_components_id`='".$components_id."'";
         $results = $DB->query($querys);
         while ($datas=$DB->fetch_array($results)) {
            $_SESSION['plugin_monitoring_hosts'] = $data;
            $pmService->delete(array('id'=>$datas['id']));
         } 
      }
   }
   
}

?>