<?php

/**
 * labs C_FormLabs.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dr Alejandro Sergio D'Alessandro <adalessandro@epa-bienestar.com>
 * @copyright Copyright (c) 2025 Your Name <your.email@example.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once($GLOBALS['fileroot'] . "/library/forms.inc.php");
require_once($GLOBALS['fileroot'] . "/library/patient.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\ListService;
use OpenEMR\Common\Twig\TwigContainer;

/**
 * FormLabs class for managing lab data
 */
class FormLabs
{
    private $data = [];
    
    public function __construct()
    {
        $this->data = [
            'id' => '',
            'uuid' => '',
            'date' => date("Y-m-d H:i:s"),
            'pid' => $GLOBALS['pid'] ?? '',
            'user' => $_SESSION['authUser'] ?? '',
            'groupname' => $_SESSION['authProvider'] ?? '',
            'authorized' => $_SESSION['userauthorized'] ?? '',
            'activity' => 1,
            'glucose' => 0.0,
            'cholesterol' => 0.0,
            'triglycerides' => 0.0,
            'uric_acid' => 0.0,
            'cholinesterase' => 0.0,
            'urinary_phenol' => 0.0,
            'note' => '',
            'encounter' => $GLOBALS['encounter'] ?? ''
        ];
    }
    
    public function populate_array($res)
    {
        if (is_array($res)) {
            $this->data = array_merge($this->data, $res);
        }
    }
    
    public function __call($method, $args)
    {
        if (strpos($method, 'get_') === 0) {
            $field = str_replace('get_', '', $method);
            return $this->data[$field] ?? '';
        }
        if (strpos($method, 'set_') === 0) {
            $field = str_replace('set_', '', $method);
            $this->data[$field] = $args[0] ?? '';
            return $this;
        }
        return $this;
    }
    
    public function get_details_for_column($column)
    {
        return null;
    }
    
    public function has_reason_for_column($column)
    {
        return false;
    }
    
    public function get_uuid_string()
    {
        return $this->data['uuid'] ?? '';
    }
    
    public function get_id()
    {
        return $this->data['id'] ?? '';
    }
    
    public function persist()
    {
        if (empty($this->data['id']) || $this->data['id'] == '') {
            // Insertar nuevo registro
            $sql = "INSERT INTO form_labs SET ";
            $sql .= "date=?, ";
            $sql .= "pid=?, ";
            $sql .= "user=?, ";
            $sql .= "groupname=?, ";
            $sql .= "authorized=?, ";
            $sql .= "activity=1, ";
            $sql .= "glucose=?, ";
            $sql .= "cholesterol=?, ";
            $sql .= "triglycerides=?, ";
            $sql .= "uric_acid=?, ";
            $sql .= "cholinesterase=?, ";
            $sql .= "urinary_phenol=?, ";
            $sql .= "note=?";
            
            $results = sqlInsert(
                $sql,
                array(
                    $this->data['date'],
                    $this->data['pid'],
                    $this->data['user'],
                    $this->data['groupname'],
                    $this->data['authorized'],
                    floatval($this->data['glucose']),
                    floatval($this->data['cholesterol']),
                    floatval($this->data['triglycerides']),
                    floatval($this->data['uric_acid']),
                    floatval($this->data['cholinesterase']),
                    floatval($this->data['urinary_phenol']),
                    $this->data['note']
                )
            );
            $this->data['id'] = $results;
        } else {
            // Actualizar registro existente
            $sql = "UPDATE form_labs SET ";
            $sql .= "date=?, ";
            $sql .= "pid=?, ";
            $sql .= "user=?, ";
            $sql .= "groupname=?, ";
            $sql .= "authorized=?, ";
            $sql .= "glucose=?, ";
            $sql .= "cholesterol=?, ";
            $sql .= "triglycerides=?, ";
            $sql .= "uric_acid=?, ";
            $sql .= "cholinesterase=?, ";
            $sql .= "urinary_phenol=?, ";
            $sql .= "note=? ";
            $sql .= "WHERE id=?";

            sqlStatement(
                $sql,
                array(
                    $this->data['date'],
                    $this->data['pid'],
                    $this->data['user'],
                    $this->data['groupname'],
                    $this->data['authorized'],
                    floatval($this->data['glucose']),
                    floatval($this->data['cholesterol']),
                    floatval($this->data['triglycerides']),
                    floatval($this->data['uric_acid']),
                    floatval($this->data['cholinesterase']),
                    floatval($this->data['urinary_phenol']),
                    $this->data['note'],
                    $this->data['id']
                )
            );
        }
        return $this->data['id'];
    }
}

/**
 * FormLabDetails class for lab interpretations and reason codes
 */
class FormLabDetails
{
    private $id;
    private $form_id;
    private $labs_column;
    private $reason_code;
    private $reason_status;
    private $reason_description;

    public function get_id()
    {
        return $this->id;
    }

    public function get_reason_code()
    {
        return $this->reason_code;
    }

    public function get_reason_status()
    {
        return $this->reason_status;
    }

    public function get_reason_description()
    {
        return $this->reason_description;
    }

    public function set_reason_code($reason_code)
    {
        $this->reason_code = $reason_code;
    }

    public function set_reason_status($reason_status)
    {
        $this->reason_status = $reason_status;
    }

    public function set_reason_description($reason_description)
    {
        $this->reason_description = $reason_description;
    }

    public function clear_reason()
    {
        $this->reason_code = null;
        $this->reason_status = null;
        $this->reason_description = null;
    }
}

/**
 * LabsService class for database operations
 */
class LabsService
{
    public function getLabsForForm($form_id)
    {
        if (empty($form_id) || !is_numeric($form_id)) {
            return null;
        }
        
        $sql = "SELECT * FROM form_labs WHERE id = ?";
        $result = sqlQuery($sql, [$form_id]);
        return $result;
    }
    
    public function getLabsHistoryForPatient($pid, $form_id = null)
    {
        if (empty($pid) || !is_numeric($pid)) {
            return [];
        }
        
        $sql = "SELECT * FROM form_labs WHERE pid = ?";
        $params = [$pid];
        
        if (!empty($form_id) && is_numeric($form_id)) {
            $sql .= " AND id != ?";
            $params[] = $form_id;
        }
        
        $sql .= " ORDER BY date DESC LIMIT 10";
        
        $results = sqlStatement($sql, $params);
        $labs = [];
        
        while ($row = sqlFetchArray($results)) {
            $labs[] = $row;
        }
        
        return $labs;
    }
    
    public function saveLabsForm($labs)
    {
        try {
            return $labs->persist();
        } catch (Exception $e) {
            error_log("Error saving labs form: " . $e->getMessage());
            throw $e;
        }
    }
}

/**
 * Main controller class for labs form
 */
class C_FormLabs
{
    public $labs;
    var $template_dir;
    var $form_id;
    var $template_mod;
    var $context;

    private $interpretationsList = [];

    public function __construct($template_mod = "general", $context = '')
    {
        $this->template_mod = $template_mod;
        $this->template_dir = __DIR__ . "/templates/labs/";
        $this->context = $context;
        $this->interpretationsList = [];
    }

    public function setFormId($form_id)
    {
        $this->form_id = $form_id;
    }

    public function default_action()
    {
        try {
            $labsService = new LabsService();
            $form_id = $this->form_id;

            $labs = new FormLabs();
            if (is_numeric($form_id) && $form_id > 0) {
                $labsArray = $labsService->getLabsForForm($form_id) ?? [];
                if (!empty($labsArray)) {
                    $labs->populate_array($labsArray);
                }
            } else {
                $this->populate_session_user_information($labs);
            }

            // get the patient's current age
            $patient_data = getPatientData($GLOBALS['pid']);
            $patient_dob = $patient_data['DOB'] ?? '';
            $patient_age = !empty($patient_dob) ? getPatientAge($patient_dob) : '';

            $records = $labsService->getLabsHistoryForPatient($GLOBALS['pid'], $form_id);
            $results = [];
            $i = 1;

            foreach ($records as $result) {
                $historicalLabs = new FormLabs();
                $historicalLabs->populate_array($result);
                $results[$i] = $historicalLabs;
                $i++;
            }

            $labFields = [
                [
                    'type' => 'textbox',
                    'title' => xl('Glucose'),
                    'vitalsValue' => "get_glucose",
                    'input' => 'glucose',
                    'unit' => 'mg/dL',
                    'unitLabel' => xl('mg/dL'),
                    'normalRange' => '< 100',
                    'codes' => 'LOINC:2345-7',
                    'precision' => 2
                ],
                [
                    'type' => 'textbox',
                    'title' => xl('Cholesterol'),
                    'vitalsValue' => "get_cholesterol",
                    'input' => 'cholesterol',
                    'unit' => 'mg/dL',
                    'unitLabel' => xl('mg/dL'),
                    'normalRange' => '< 200',
                    'codes' => 'LOINC:2093-3',
                    'precision' => 2
                ],
                [
                    'type' => 'textbox',
                    'title' => xl('Triglycerides'),
                    'vitalsValue' => "get_triglycerides",
                    'input' => 'triglycerides',
                    'unit' => 'mg/dL',
                    'unitLabel' => xl('mg/dL'),
                    'normalRange' => '< 150',
                    'codes' => 'LOINC:2571-8',
                    'precision' => 2
                ],
                [
                    'type' => 'textbox',
                    'title' => xl('Uric Acid'),
                    'vitalsValue' => "get_uric_acid",
                    'input' => 'uric_acid',
                    'unit' => 'mg/dL',
                    'unitLabel' => xl('mg/dL'),
                    'normalRange' => '< 6.5',
                    'codes' => 'LOINC:3084-1',
                    'precision' => 2
                ],
                [
                    'type' => 'textbox',
                    'title' => xl('Cholinesterase'),
                    'vitalsValue' => "get_cholinesterase",
                    'input' => 'cholinesterase',
                    'unit' => 'U/L',
                    'unitLabel' => xl('U/L'),
                    'normalRange' => 'Variable',
                    'codes' => 'LOINC:2019-8',
                    'precision' => 2
                ],
                [
                    'type' => 'textbox',
                    'title' => xl('Urinary Phenol'),
                    'vitalsValue' => "get_urinary_phenol",
                    'input' => 'urinary_phenol',
                    'unit' => 'mg/L',
                    'unitLabel' => xl('mg/L'),
                    'normalRange' => 'Variable',
                    'codes' => 'LOINC:33747-0',
                    'precision' => 2
                ],
                [
                    'type' => 'template',
                    'templateName' => 'labs_notes.html.twig',
                    'title' => xl('Notes'),
                    'input' => 'note',
                    'vitalsValue' => 'get_note'
                ]
            ];

            $resultsCount = count($results);
            $hasMoreLabs = false;
            $labsHistoryLookback = [];
            $maxHistoryCols = $GLOBALS['gbl_labs_max_history_cols'] ?? 2;
            
            if ($maxHistoryCols > 0 && $resultsCount > $maxHistoryCols) {
                $labsHistoryLookback = array_slice($results, 0, $maxHistoryCols);
                $hasMoreLabs = true;
            } else {
                $labsHistoryLookback = $results;
            }

            $reasonCodeStatii = [];
            // Solo cargar reason codes si la clase existe
            if (class_exists('OpenEMR\Common\Forms\ReasonStatusCodes')) {
                $reasonCodeStatii = \OpenEMR\Common\Forms\ReasonStatusCodes::getCodesWithDescriptions();
                $reasonCodeStatii[\OpenEMR\Common\Forms\ReasonStatusCodes::NONE]['description'] = xl("Select a status code");
            }

            $data = [
                'labs' => $labs,
                'labFields' => $labFields,
                'FORM_ACTION' => $GLOBALS['web_root'],
                'DONT_SAVE_LINK' => $GLOBALS['form_exit_url'] ?? '',
                'STYLE' => $GLOBALS['style'] ?? '',
                'CSRF_TOKEN_FORM' => CsrfUtils::collectCsrfToken(),
                'results' => $results,
                'labsHistoryLookback' => $labsHistoryLookback,
                'hasMoreLabs' => $hasMoreLabs,
                'results_count' => count($results),
                'reasonCodeStatii' => $reasonCodeStatii,
                'interpretation_options' => $this->interpretationsList,
                'VIEW' => true,
                'patient_age' => $patient_age,
                'patient_dob' => $patient_dob,
                'has_id' => $form_id,
                'assetVersion' => $GLOBALS['v_js_includes'] ?? '1'
            ];

            $twig = (new TwigContainer($this->template_dir, $GLOBALS['kernel']))->getTwig();
            echo $twig->render("labs.html.twig", $data);

        } catch (Exception $e) {
            error_log("Labs form error: " . $e->getMessage());
            echo "<div class='alert alert-danger'>Error loading labs form: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }

    public function default_action_process()
    {
        if (($_POST['process'] ?? '') != "true") {
            return;
        }

        try {
            $labsService = new LabsService();
            
            // Si hay un ID, cargar los datos existentes
            if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
                $labsArray = $labsService->getLabsForForm($_POST['id']) ?? [];
                $this->labs = new FormLabs();
                if (!empty($labsArray)) {
                    $this->labs->populate_array($labsArray);
                }
            } else {
                $this->labs = new FormLabs();
            }
            
            $this->populate_object($this->labs);
            $id = $labsService->saveLabsForm($this->labs);
            
            // Agregar el formulario al encounter si es nuevo
            if (empty($_POST['id']) && !empty($id)) {
                addForm($GLOBALS['encounter'], "Laboratory Results", $id, "labs", $GLOBALS['pid'], $_SESSION['userauthorized'] ?? '');
            }
            
            return;
        } catch (Exception $e) {
            error_log("Labs save error: " . $e->getMessage());
            return;
        }
    }

    public function populate_object(&$obj)
    {
        if (!is_object($obj)) {
            throw new InvalidArgumentException("populate_object called with invalid argument");
        }

        foreach ($_POST as $varname => $var) {
            $varname = preg_replace("/[^A-Za-z0-9_]/", "", $varname);
            if (!str_starts_with($varname, "_") && !empty($varname)) {
                $func = "set_" . $varname;
                $obj->$func($var);
            }
        }

        $this->populate_session_user_information($obj);

        if (($GLOBALS['encounter'] ?? 0) < 1) {
            $GLOBALS['encounter'] = date("Ymd");
        }

        $obj->set_encounter($GLOBALS['encounter']);
        $obj->set_pid($GLOBALS['pid']);
        $obj->set_authorized($_SESSION['userauthorized'] ?? '');
    }

    private function populate_session_user_information($labs)
    {
        $labs->set_groupname($_SESSION['authProvider'] ?? '');
        $labs->set_user($_SESSION['authUser'] ?? '');
    }
}
