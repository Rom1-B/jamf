<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of config
 *
 * @author Curtis Conard <cconard@cjdevstudios.com>
 */
class PluginJamfConfig extends CommonDBTM
{

   static protected $notable = true;

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
   {
      if (!$withtemplate) {
         if ($item->getType() == 'Config') {
            return __('JAMF Plugin');
         }
      }
      return '';
   }

   function showForm()
   {
      global $CFG_GLPI;
      if (!Session::haveRight("config", UPDATE)) {
         return false;
      }
      $config = self::getConfig(true);
      echo "<form name='form' action=\"".Toolbox::getItemTypeFormURL('Config')."\" method='post'>";
      echo "<div class='center' id='tabsbody'>";
      echo "<table class='tab_cadre_fixe'><thead>";
      echo "<th colspan='4'>" . __('Server Settings') . "</th></thead>";
      echo "<td>" . __('JSS Server:', 'jamf') . "</td>";
      echo "<td>";
      echo "<input type='hidden' name='config_class' value='".__CLASS__."'>";
      echo "<input type='hidden' name='config_context' value='plugin:Jamf'>";
      echo Html::input('jssserver', [
         'value' => $config['jssserver']
      ]);
      echo "</td><td>".__('JSS User:', 'jamf')."</td><td>";
      echo Html::input('jssuser', [
         'value' => $config['jssuser']
      ]);
      echo "</td></tr><tr><td>".__('JSS Password:', 'jamf')."</td><td>";
      echo Html::input('jsspassword', ['type' => 'password']);
      $msg = (isset($config['jsspassword']) && strlen($config['jsspassword'])) ? __('Password set') : '';
      echo "</td><td>{$msg}</td></tr>";
      echo "</table>";

      echo "<table class='tab_cadre_fixe'><thead>";
      echo "<th colspan='4'>" . __('Sync Settings') . "</th></thead>";

      echo "<tr><td>" . __('Sync Interval (minutes):', 'jamf') . "</td>";
      echo "<td>";
      Dropdown::showNumber('sync_interval', [
         'value'  => isset($config['sync_interval']) ? $config['sync_interval'] : 15
      ]);
      echo "</td></tr>";

      echo "<tr><td>" . __('Sync General:', 'jamf') . "</td>";
      echo "<td>";
      Dropdown::showYesNo('sync_general', isset($config['sync_general']) ? $config['sync_general'] : false);
      echo "</td>";

      echo "<td>" . __('Sync OS:', 'jamf') . "</td>";
      echo "<td>";
      Dropdown::showYesNo('sync_os', isset($config['sync_os']) ? $config['sync_os'] : false);
      echo "</td></tr>";

      echo "<tr><td>" . __('Sync Software:', 'jamf') . "</td>";
      echo "<td>";
      Dropdown::showYesNo('sync_software', isset($config['sync_software']) ? $config['sync_software'] : false);
      echo "</td>";

      echo "<td>" . __('Sync Financial:', 'jamf') . "</td>";
      echo "<td>";
      Dropdown::showYesNo('sync_financial', isset($config['sync_financial']) ? $config['sync_financial'] : false);
      echo "</td></tr>";

      echo "<tr><td>" . __('Sync Components:', 'jamf') . "</td>";
      echo "<td>";
      Dropdown::showYesNo('sync_components', isset($config['sync_components']) ? $config['sync_components'] : false);
      echo "</td></tr>";

      echo "<tr><td>" . __('Sync User:', 'jamf') . "</td>";
      echo "<td>";
      Dropdown::showYesNo('sync_user', isset($config['sync_user']) ? $config['sync_user'] : false);
      echo "</td>";

      echo "<td>" . __('Auto Import:', 'jamf') . "</td>";
      echo "<td>";
      Dropdown::showYesNo('autoimport', isset($config['autoimport']) ? $config['autoimport'] : false);
      echo "</td></tr>";

      echo "<table class='tab_cadre_fixe'><thead>";
      echo "<th colspan='4'>" . __('Default Type Settings') . "</th></thead>";
      echo "<td>" . __('Manufacturer:', 'jamf') . "</td>";
      echo "<td>";
      Dropdown::show('Manufacturer', [
         'name' => 'default_manufacturer',
         'value' => isset($config['default_manufacturer']) ? $config['default_manufacturer'] : false
      ]);
      echo "</td><td>".__('iPhone Type:', 'jamf')."</td><td>";
      Dropdown::show('PhoneType', [
         'name' => 'iphone_type',
         'value' => isset($config['iphone_type']) ? $config['iphone_type'] : false
      ]);
      echo "</td></tr><tr><td>".__('iPad Type:', 'jamf')."</td><td>";
      Dropdown::show('ComputerType', [
         'name' => 'ipad_type',
         'value' => isset($config['ipad_type']) ? $config['autoimport'] : false
      ]);
      echo "</td><td>".__('AppleTV Type', 'jamf')."</td><td>";
      Dropdown::show('ComputerType', [
         'name' => 'appletv_type',
         'value' => isset($config['appletv_type']) ? $config['appletv_type'] : false
      ]);
      echo "</td></tr></table>";

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_2'>";
      echo "<td colspan='4' class='center'>";
      echo "<input type='submit' name='update' class='submit' value=\""._sx('button', 'Save')."\">";
      echo "</td></tr>";
      echo "</table>";
      echo "</div>";
      Html::closeForm();
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
   {
      if ($item->getType() == 'Config') {
         $config = new self();
         $config->showForm();
      }
   }

   public static function undiscloseConfigValue($fields)
   {
      $to_hide = ['jsspassword'];
      if (in_array($fields, $to_hide)) {
         unset($fields[$to_hide]);
      }
      return $fields;
   }

   public static function getConfig(bool $force_all = false) : array
   {
      static $config = null;
      if (is_null($config)) {
         $config = Config::getConfigurationValues('plugin:Jamf');
      }
      if (!$force_all) {
         return self::undiscloseConfigValue($config);
      } else {
         return $config;
      }
   }
}
