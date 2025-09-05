<?php

/**
 * labs report.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dr Alejandro Sergio D'Alessandro <adalessandro@epa-bienestar.com>
 * @copyright Copyright (c) 2025  Dr Alejandro Sergio D'Alessandro <adalessandro@epa-bienestar.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc.php");
require_once($GLOBALS['fileroot'] . "/library/patient.inc.php");

function labs_report($pid, $encounter, $cols, $id, $print = true)
{
    $count = 0;
    $data = formFetch("form_labs", $id);
    $patient_data = getPatientData($GLOBALS['pid']);

    $labs = "";
    if ($data) {
        $labs .= "<table><tr>";

        foreach ($data as $key => $value) {
            if (
                $key == "uuid" ||
                $key == "id" || $key == "pid" ||
                $key == "user" || $key == "groupname" ||
                $key == "authorized" || $key == "activity" ||
                $key == "date" || $value == "" ||
                $value == "0000-00-00 00:00:00" || $value == "0.0"
            ) {
                // skip certain data
                continue;
            }

            if ($value == "on") {
                $value = "yes";
            }

            $key = ucwords(str_replace("_", " ", $key));

            if ($key == "Glucose") {
                $value = floatval($value);
                $c_value = number_format($value, 2);
                $labs .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div></td><td><div class='text' style='display:inline-block'>" . text($c_value) . " " . xlt('mg/dL') . " (" . xlt('Normal') . ": < 100)</div></td>";
            } elseif ($key == "Cholesterol") {
                $value = floatval($value);
                $c_value = number_format($value, 2);
                $labs .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div></td><td><div class='text' style='display:inline-block'>" . text($c_value) . " " . xlt('mg/dL') . " (" . xlt('Normal') . ": < 200)</div></td>";
            } elseif ($key == "Triglycerides") {
                $value = floatval($value);
                $c_value = number_format($value, 2);
                $labs .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div></td><td><div class='text' style='display:inline-block'>" . text($c_value) . " " . xlt('mg/dL') . " (" . xlt('Normal') . ": < 150)</div></td>";
            } elseif ($key == "Uric Acid") {
                $value = floatval($value);
                $c_value = number_format($value, 2);
                $labs .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div></td><td><div class='text' style='display:inline-block'>" . text($c_value) . " " . xlt('mg/dL') . " (" . xlt('Normal') . ": < 6.5)</div></td>";
            } elseif ($key == "Cholinesterase") {
                $value = floatval($value);
                $c_value = number_format($value, 2);
                $labs .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div></td><td><div class='text' style='display:inline-block'>" . text($c_value) . " " . xlt('U/L') . "</div></td>";
            } elseif ($key == "Urinary Phenol") {
                $value = floatval($value);
                $c_value = number_format($value, 2);
                $labs .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div></td><td><div class='text' style='display:inline-block'>" . text($c_value) . " " . xlt('mg/L') . "</div></td>";
            } else {
                $labs .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div></td><td><div class='text' style='display:inline-block'>" . text($value) . "</div></td>";
            }

            $count++;

            if ($count == $cols) {
                $count = 0;
                $labs .= "</tr><tr>\n";
            }
        }

        $labs .= "</tr></table>";
    }

    if ($print) {
        echo $labs;
    } else {
        return $labs;
    }
}
