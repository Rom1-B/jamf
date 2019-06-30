<?php

/*
 -------------------------------------------------------------------------
 JAMF plugin for GLPI
 Copyright (C) 2019 by Curtis Conard
 https://github.com/cconard96/jamf
 -------------------------------------------------------------------------
 LICENSE
 This file is part of JAMF plugin for GLPI.
 JAMF plugin for GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 JAMF plugin for GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with JAMF plugin for GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * JamfMobileDevice class.
 */
class PluginJamfMobileDevice extends CommonDBTM
{

   static $rightname = 'plugin_jamf_mobiledevice';

   public static function getTypeName($nb = 1)
   {
      return __('Jamf mobile device', 'Jamf mobile devices', $nb, 'jamf');
   }

   public static function showForComputerOrPhoneMain($params)
   {
      $item = $params['item'];

      if (!($item::getType() == 'Computer') && !($item::getType() == 'Phone')) {
         return;
      }
      $mobiledevice = new PluginJamfMobileDevice();
      $match = $mobiledevice->find([
         'itemtype' => $item::getType(),
         'items_id' => $item->getID()]);

      if (!count($match)) {
         return;
      }

      $getYesNo = function($value) {
         return $value ? __('Yes') : __('No');
      };

      $match = reset($match);
      $out = "<th colspan='4'>".__('Jamf General Information', 'jamf')."</th>";
      $out .= "<tr><td>".__('Import date', 'jamf')."</td>";
      $out .= "<td>".Html::convDateTime($match['import_date'])."</td>";
      $out .= "<td>".__('Last sync', 'jamf')."</td>";
      $out .= "<td>".Html::convDateTime($match['sync_date'])."</td></tr>";

      $out .= "<tr><td>".__('Jamf last inventory', 'jamf')."</td>";
      $out .= "<td>".Html::convDateTime($match['last_inventory'])."</td>";
      $out .= "<td>".__('Jamf import date', 'jamf')."</td>";
      $out .= "<td>".Html::convDateTime($match['entry_date'])."</td></tr>";

      $out .= "<tr><td>".__('Enrollment date', 'jamf')."</td>";
      $out .= "<td>".Html::convDateTime($match['enroll_date'])."</td>";
      $out .= "<td>".__('Shared device', 'jamf')."</td>";
      $out .= "<td>".$match['shared']."</td></tr>";

      $out .= "<tr><td>".__('Supervised', 'jamf')."</td>";
      $out .= "<<td>".$getYesNo($match['supervised'])."</td>";
      $out .= "<td>".__('Managed', 'jamf')."</td>";
      $out .= "<td>".$getYesNo($match['managed'])."</td></tr>";

      $out .= "<td>".__('Cloud backup enabled', 'jamf')."</td>";
      $out .= "<td>".$getYesNo($match['cloud_backup_enabled'])."</td>";
      $out .= "<td>".__('Activation locked', 'jamf')."</td>";
      $out .= "<td>".$getYesNo($match['activation_lock_enabled'])."</td></tr>";

      $out .= "<th colspan='4'>".__('Jamf Lost Mode Information', 'jamf')."</th>";
      $enabled = $match['lost_mode_enabled'];
      if (!$enabled) {
         $out .= "<tr><td colspan='4'>".__('Lost mode is not enabled')."</td><tr>";
      } else {
         $out .= "<tr><td>".__('Enabled', 'jamf')."</td>";
         $out .= "<td>".$enabled."</td>";
         $out .= "<td>".__('Enforced', 'jamf')."</td>";
         $out .= "<td>".$getYesNo($match['lost_mode_enforced'])."</td></tr>";

         $out .= "<tr><td>".__('Enable date', 'jamf')."</td>";
         $out .= "<td>".Html::convDateTime($match['lost_mode_enable_date'])."</td></tr>";

         $out .= "<tr><td>".__('Message', 'jamf')."</td>";
         $out .= "<td>".$match['lost_mode_message']."</td>";
         $out .= "<td>".__('Phone', 'jamf')."</td>";
         $out .= "<td>".$match['lost_mode_phone']."</td></tr>";

         $out .= "<td>".__('Latitude')."</td>";
         $out .= "<td>".$match['lost_location_latitude']."</td>";
         $out .= "<td>".__('Longitude')."</td>";
         $out .= "<td>".$match['lost_location_longitude']."</td></tr>";

         $out .= "<tr><td>".__('Altitude')."</td>";
         $out .= "<td>".$match['lost_location_altitude']."</td>";
         $out .= "<td>".__('Speed', 'jamf')."</td>";
         $out .= "<td>".$match['lost_location_speed']."</td></tr>";
         $out .= "<td>".Html::convDateTime($match['lost_location_date'])."</td></tr>";
      }

      echo $out;
   }

   public static function getJamfDeviceURL($udid)
   {
      $config = PluginJamfConfig::getConfig();
      return "{$config['jssserver']}/mobileDevices.html?udid={$udid}";
   }
}