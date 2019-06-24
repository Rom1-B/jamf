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
      $jamf_config = self::getConfig(true);
      echo "<form name='form' action=\"".Toolbox::getItemTypeFormURL('Config')."\" method='post'>";
      echo "<div class='center' id='tabsbody'>";
      echo "<table class='tab_cadre_fixe'><thead>";
      echo "<th colspan='4'>" . __('Server Settings') . "</th></thead>";
      echo "<td>" . __('JSS Server:', 'jamf') . "</td>";
      echo "<td>";
      echo "<input type='hidden' name='config_class' value='".__CLASS__."'>";
      echo "<input type='hidden' name='config_context' value='plugin:Jamf'>";
      echo Html::input('jssserver', ['value' => $jamf_config['jssserver']]);
      echo "</td><td>".__('JSS User:', 'jamf')."</td><td>";
      echo Html::input('jssuser', ['value' => $jamf_config['jssuser']]);
      echo "</td></tr><tr><td>".__('JSS Password:', 'jamf')."</td><td>";
      echo Html::input('jsspassword', ['type' => 'password']);
      $msg = isset($jamf_config['jsspassword']) ? __('Password set') : '';
      echo "</td><td>{$msg}</td></tr>";
      echo "</table>";

      echo "<table class='tab_cadre_fixe'><thead>";
      echo "<th colspan='4'>" . __('Sync Settings') . "</th></thead>";

      echo "<tr><td>" . __('Sync Interval (minutes):', 'jamf') . "</td>";
      echo "<td>";
      Dropdown::showNumber('sync_interval', [
         'value'  => $jamf_config['sync_interval']
      ]);
      echo "</td></tr>";

      echo "<tr><td>" . __('Sync General:', 'jamf') . "</td>";
      echo "<td>";
      Dropdown::showYesNo('sync_general', $jamf_config['sync_general']);
      echo "</td>";

      echo "<td>" . __('Sync OS:', 'jamf') . "</td>";
      echo "<td>";
      Dropdown::showYesNo('sync_os', $jamf_config['sync_os']);
      echo "</td></tr>";

      echo "<tr><td>" . __('Sync Software:', 'jamf') . "</td>";
      echo "<td>";
      Dropdown::showYesNo('sync_software', $jamf_config['sync_software']);
      echo "</td>";

      echo "<td>" . __('Sync Financial:', 'jamf') . "</td>";
      echo "<td>";
      Dropdown::showYesNo('sync_financial', $jamf_config['sync_financial']);
      echo "</td></tr>";

      echo "<tr><td>" . __('Sync Components:', 'jamf') . "</td>";
      echo "<td>";
      Dropdown::showYesNo('sync_components', $jamf_config['sync_components']);
      echo "</td></tr>";

      echo "<tr><td>" . __('Sync User:', 'jamf') . "</td>";
      echo "<td>";
      Dropdown::showYesNo('sync_user', $jamf_config['sync_user']);
      echo "</td>";

      echo "<td>" . __('User Sync Mode:', 'jamf') . "</td>";
      echo "<td>";
      Dropdown::showFromArray('user_sync_mode', [
         'email' => __('Email'),
         'username' => __('Username')
      ]);
      echo "</td></tr>";

      echo "<tr><td>" . __('Auto Import:', 'jamf') . "</td>";
      echo "<td>";
      Dropdown::showYesNo('autoimport', $jamf_config['autoimport']);
      echo "</td></tr>";

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
      if ($fields['context'] == 'jamf' && in_array($fields['name'], $to_hide)) {
         unset($fields['value']);
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
