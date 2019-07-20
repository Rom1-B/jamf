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

include('../../../inc/includes.php');
Session::checkRight("plugin_jamf_mobiledevice", CREATE);
Html::header('Jamf Plugin', '', 'tools', 'PluginJamfMenu', 'import');

global $DB, $CFG_GLPI;

$start = isset($_GET['start']) ? $_GET['start'] : 0;

$import = new PluginJamfImport();
$importcount = countElementsInTable(PluginJamfImport::getTable());
$pending = $DB->request([
   'FROM'   => PluginJamfImport::getTable(),
   'START'  => $start,
   'LIMIT'  => $_SESSION['glpilist_limit']
]);

$check_all = Html::getCheckAllAsCheckbox('import_table');

Html::printPager($start, $importcount, PluginJamfImport::getSearchURL(), '');
echo "<form>";
echo "<div class='center'><table id='import_table' class='tab_cadre' style='width: 50%'>";
echo "<thead>";
echo "<th>{$check_all}</th>";
echo "<th>".__('Jamf ID')."</th>";
echo "<th>".__('Name')."</th>";
echo "<th>".__('Type')."</th>";
echo "<th>".__('UDID')."</th>";
echo "<th>".__('Discovery Date')."</th>";
echo "</thead><tbody>";
while ($data = $pending->next()) {
   $rowid = $data['jamf_items_id'];
   echo "<tr>";
   $import_checkbox = Html::input("import{$rowid}", [
      'type'      => 'checkbox',
      'display'   => false
   ]);
   echo "<td>{$import_checkbox}</td>";
   echo "<td>{$data['jamf_items_id']}</td>";
   $jamf_link = Html::link($data['name'], PluginJamfMobileDevice::getJamfDeviceURL($data['udid']));
   echo "<td>{$jamf_link}</td>";
   echo "<td>{$data['type']}</td>";
   echo "<td>{$data['udid']}</td>";
   $date_discover = Html::convDateTime($data['date_discover']);
   echo "<td>{$date_discover}</td>";
   echo "</tr>";
}
echo "</tbody></table><br>";

echo "<a class='vsubmit' onclick='importDevices(); return false;'>".__('Import')."</a>";
echo "</div>";
$ajax_url = $CFG_GLPI['root_doc']."/plugins/jamf/ajax/import.php";
$js = <<<JAVASCRIPT
      function importDevices() {
         var ids = $(':checkbox:checked').map(function(){ return this.name.replace("import",""); }).toArray();
         var post_data = [];
         post_data['action'] = "import";
         post_data['item_ids'] = ids;

         $.ajax({
            type: "POST",
            url: "{$ajax_url}",
            data: {action: "import", item_ids: ids},
            contentType: 'application/json',
            beforeSend: function() {
               $('#loading-overlay').show();
            },
            complete: function() {
               location.reload();
            }
         });
      }
JAVASCRIPT;
Html::closeForm();
Html::printPager($start, $importcount, PluginJamfImport::getSearchURL(), '');
echo Html::scriptBlock($js);

// Create loading indicator
$position = "position: fixed; top: 0; left: 0; right: 0; bottom: 0;";
$style = "display: none; {$position} width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 2; cursor: progress;";
echo "<div id='loading-overlay' style='{$style}'><table class='tab_cadre' style='margin-top: 10%;'>";
echo "<thead><tr><th class='center'><h3>".__('Importing devices...', 'jamf')."</h3></th></tr></thead>";
echo "</table></div>";
Html::footer();
