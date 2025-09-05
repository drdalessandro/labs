<?php

/**
 * labs new.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dr Alejandro Sergio D'Alessandro <adalessandro@epa-bienestar.com>
 * @copyright Copyright (c) 2025 Dr Alejandro Sergio D'Alessandro <adalessandro@epa-bienestar.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");
require_once "C_FormLabs.class.php";

$c = new C_FormLabs();
$c->setFormId(0);
echo $c->default_action();
