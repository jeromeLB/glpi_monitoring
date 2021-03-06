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

class PluginMonitoringServicescatalog extends CommonDropdown {
   
   /**
   * Get name of this type
   *
   *@return text name of this type by language of the user connected
   *
   **/
   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_monitoring']['servicescatalog'][0];
   }



   function canCreate() {
      return PluginMonitoringProfile::haveRight("servicescatalog", 'w');
   }


   
   function canView() {
      return PluginMonitoringProfile::haveRight("servicescatalog", 'r');
   }


   
   function canCancel() {
      return PluginMonitoringProfile::haveRight("servicescatalog", 'w');
   }


   
   function canUndo() {
      return PluginMonitoringProfile::haveRight("servicescatalog", 'w');
   }

   
   
   function showForm($items_id, $options=array()) {
      global $LANG;

      if ($items_id!=''
              AND $items_id != '-1') {
         $this->getFromDB($items_id);
      } else {
         $this->getEmpty();
      }

      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['common'][16]." :</td>";
      echo "<td>";
      echo "<input type='text' name='name' value='".$this->fields["name"]."' size='30'/>";
      echo "</td>";
      echo "<td>".$LANG['plugin_monitoring']['check'][0]."&nbsp;:</td>";
      echo "<td>";
      Dropdown::show("PluginMonitoringCheck", 
                        array('name'=>'plugin_monitoring_checks_id',
                              'value'=>$this->fields['plugin_monitoring_checks_id']));
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['common'][25]."&nbsp;: </td>";
      echo "<td>";
      echo "<textarea cols='45' rows='2' name='comment'>".$this->fields["comment"]."</textarea>";
      echo "</td>";
      echo "<td>".$LANG['plugin_monitoring']['host'][9]."&nbsp;:</td>";
      echo "<td>";
      dropdown::show("Calendar", array('name'=>'calendars_id',
                                 'value'=>$this->fields['calendars_id']));
      echo "</td>";
      echo "</tr>";
      
      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;
   }

   
   
   function showBAChecks() {
      global $CFG_GLPI,$LANG;
      
      echo "<table class='tab_cadre' width='100%'>";
      echo "<tr class='tab_bg_4' style='background: #cececc;'>";
      
      $a_ba = $this->find();
      foreach ($a_ba as $data) {
         echo "<td>";

         echo $this->showWidget($data['id']);
         

         echo "</td>";
      }      
      
      echo "</tr>";
      echo "</table>";      
   }
   
   
   function showBADetail($id) {
      global $CFG_GLPI;
      
      $pMonitoringBusinessrule = new PluginMonitoringBusinessrule();
      $pMonitoringBusinessrulegroup = new PluginMonitoringBusinessrulegroup();
      $pMonitoringService = new PluginMonitoringService();
      
      $this->getFromDB($id);
      echo "<table class='tab_cadrehov'>";
      $a_groups = $pMonitoringBusinessrulegroup->find("`plugin_monitoring_servicescatalogs_id`='".$id."'");
      
      echo "<tr class='tab_bg_1'>";
      
      $color = PluginMonitoringDisplay::getState($this->fields['state'], $this->fields['state_type']);
      $pic = $color;
      $color = str_replace("_soft", "", $color);
      
      echo "<td rowspan='".count($a_groups)."' class='center' width='200' bgcolor='".$color."'>";
      echo "<strong style='font-size: 20px'>".$this->getName()."</strong><br/>";
      echo "<img src='".$CFG_GLPI['root_doc']."/plugins/monitoring/pics/box_".$pic."_40.png'/>";
      echo "</td>";
      
      $i = 0;
      foreach($a_groups as $gdata) {
         $a_brulesg = $pMonitoringBusinessrule->find("`plugin_monitoring_businessrulegroups_id`='".$gdata['id']."'");

         if ($i > 0) {
            echo "<tr>";
         }
         
         $state = array();
         $state['red'] = 0;
         $state['red_soft'] = 0;
         $state['orange'] = 0;
         $state['orange_soft'] = 0;
         $state['green'] = 0;
         $state['green_soft'] = 0;
         foreach ($a_brulesg as $brulesdata) {
            $pMonitoringService->getFromDB($brulesdata['plugin_monitoring_services_id']);
            $state[PluginMonitoringDisplay::getState($pMonitoringService->fields['state'], $pMonitoringService->fields['state_type'])]++;
         }
         $color = "";
         if ($gdata['operator'] == 'or') {
            if ($state['green'] >= 1) {
               $color = "green";
            } else if ($state['orange'] >= 1) {
               $color = "orange";
            } else if ($state['orange_soft'] >= 1) {
               $color = "orange";
            } else if ($state['red'] >= 1) {
               $color = "red";
            } else if ($state['red_soft'] >= 1) {
               $color = "red";
            }            
         } else {
            $num_min = str_replace(" of:", "", $gdata['operator']);
            if ($state['green'] >= $num_min) {
               $color = "green";
            } else if ($state['orange'] >= $num_min) {
               $color = "orange";
            } else if ($state['orange_soft'] >= $num_min) {
               $color = "orange";
            } else if ($state['red'] >= $num_min) {
               $color = "red";
            } else if ($state['red_soft'] >= $num_min) {
               $color = "red";
            } 
         }
         
         echo "<td class='center' bgcolor='".$color."'>";
         echo $gdata['name']."<br/>[ ".$gdata['operator']." ]";
         echo "</td>";
         echo "<td bgcolor='".$color."'>";
            echo "<table>";
            foreach ($a_brulesg as $brulesdata) {
               echo "<tr class='tab_bg_1'>";
               $pMonitoringService->getFromDB($brulesdata['plugin_monitoring_services_id']);
               PluginMonitoringDisplay::displayLine($pMonitoringService->fields);
              echo "</tr>";
            }
            echo "</table>";
         echo "</th>";
         echo "</tr>";
         $i++;
      }
      echo "</tr>";
      
      echo "</table>";
   }
   
   
   
   function showWidget($id) {
      global $LANG, $DB, $CFG_GLPI;

      $pMonitoringBusinessrule = new PluginMonitoringBusinessrule();
      $pMonitoringBusinessrulegroup = new PluginMonitoringBusinessrulegroup();
      $pMonitoringService = new PluginMonitoringService();

      $this->getFromDB($id);
      $data = $this->fields;
      $input = '';
      $input .= '<table  class="tab_cadre_fixe" style="width:200px;height:200px">';
      $input .= '<tr class="tab_bg_1">';
      $input .= '<th colspan="2" style="font-size:20px;" height="50">';
      $input .= $data['name'];
      $input .= '</th>';
      $input .= '</tr>';

      $input .= '<tr class="tab_bg_1">';
      $input .= '<td>';
      $input .= $LANG['state'][0]."&nbsp;:";
      $input .= '</td>';
      $input .= '<td width="40">';
      switch($data['state']) {

         case 'UP':
         case 'OK':
            $input .= '<img src="'.$CFG_GLPI['root_doc'].'/plugins/monitoring/pics/box_green_40.png"/>';
            break;

         case 'DOWN':
         case 'UNREACHABLE':
         case 'CRITICAL':
         case 'DOWNTIME':
            $input .= '<img src="'.$CFG_GLPI['root_doc'].'/plugins/monitoring/pics/box_red_40.png"/>';
            break;

         case 'WARNING':
         case 'UNKNOWN':
         case 'RECOVERY':
         case 'FLAPPING':
         case '':
            $input .= '<img src="'.$CFG_GLPI['root_doc'].'/plugins/monitoring/pics/box_orange_40.png"/>';
            break;

      }
      $input .= '</td>';
      $input .= '</tr>';

      $input .= '<tr class="tab_bg_1">';
      $input .= '<td>';
      $input .= $LANG['plugin_monitoring']['servicescatalog'][1]."&nbsp;:";
      $input .= '</td>';
      $input .= '<td width="40" align="center">';
      $a_group = $pMonitoringBusinessrulegroup->find("`plugin_monitoring_servicescatalogs_id`='".$data['id']."'");
      $a_gstate = array();
      foreach ($a_group as $gdata) {
         $a_brules = $pMonitoringBusinessrule->find("`plugin_monitoring_businessrulegroups_id`='".$gdata['id']."'");
         $state = array();
         $state['OK'] = 0;
         $state['WARNING'] = 0;
         $state['CRITICAL'] = 0;
         foreach ($a_brules as $brulesdata) {
            if ($pMonitoringService->getFromDB($brulesdata['plugin_monitoring_services_id'])) {
               switch($pMonitoringService->fields['state']) {

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
            }
         }
         if ($state['CRITICAL'] >= 1) {
            $a_gstate[$gdata['id']] = "CRITICAL";
         } else if ($state['WARNING'] >= 1) {
            $a_gstate[$gdata['id']] = "WARNING";
         } else {
            $a_gstate[$gdata['id']] = "OK";
         }            

      }
      $state = array();
      $state['OK'] = 0;
      $state['WARNING'] = 0;
      $state['CRITICAL'] = 0;
      foreach ($a_gstate as $value) {
         $state[$value]++;
      }
      $color = 'green';
      if ($state['CRITICAL'] > 0) {
         $color = 'red';
      } else if ($state['WARNING'] > 0) {
         $color = 'orange';
      }
      $input .= '<img src="'.$CFG_GLPI['root_doc'].'/plugins/monitoring/pics/box_'.$color.'_32.png" />';
      $input .= '</td>';
      $input .= '</tr>';

      $input .= '<tr class="tab_bg_1">';
      $input .= '<td colspan="2" align="center">';
      $input .= '<a href="'.$CFG_GLPI['root_doc'].'/plugins/monitoring/front/servicescatalog.form.php?id='.$data['id'].'&detail=1">Détail</a>';
      $input .= '</td>';
      $input .= '</tr>';

      $input .= '</table>';
      return $input;
   }
}

?>