/*
 * labs.js
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Dr Alejandro Sergio D'Alessandro <adalessandro@epa-bienestar.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

(function(window, oeUI) {

    let translations = {};
    let webroot = null;

    function labsFormSubmitted() {
        var invalid = "";
        var elementsToValidate = ['glucose_input', 'cholesterol_input', 'triglycerides_input', 'uric_acid_input', 'cholinesterase_input', 'urinary_phenol_input'];

        for (var i = 0; i < elementsToValidate.length; i++) {
            var current_elem_id = elementsToValidate[i];
            var element = document.getElementById(current_elem_id);
            if (!element) continue;

            element.classList.remove('error');

            if (element.value !== "" && isNaN(element.value)) {
                invalid += labsTranslations['invalidField'] + ": " + current_elem_id.replace('_input', '') + "\n";
                element.classList.add("error");
                element.focus();
            }
        }

        if (invalid.length > 0) {
            invalid += "\n" + labsTranslations['validateFailed'];
            alert(invalid);
            return false;
        } else {
            return top.restoreSession();
        }
    }

    function initDOMEvents() {
        let labsForm = document.getElementById('labsForm');
        if (!labsForm) {
            console.error("Failed to find labsForm DOM Node");
            return;
        }

        document.getElementById('labsForm').addEventListener('submit', function(event) {
            if (!labsFormSubmitted()) {
                event.preventDefault();
                let firstErrorElement = document.querySelector('.error');
                if (firstErrorElement) {
                    firstErrorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }
        });

        // setup reason code widgets
        if (oeUI.reasonCodeWidget) {
            oeUI.reasonCodeWidget.init(webroot);
        } else {
            console.error("Missing required dependency reason-code-widget");
        }
    }

    function init(webRootParam, labsTranslations) {
        webroot = webRootParam;
        translations = labsTranslations;
        window.document.addEventListener("DOMContentLoaded", initDOMEvents);
    }

    let labsForm = {
        "init": init
    };
    window.labsForm = labsForm;
})(window, window.oeUI || {});
