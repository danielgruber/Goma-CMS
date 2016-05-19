<?php defined('IN_GOMA') OR die();

/**
 * Extends TableField with CSV-Export-Functionallity.
 *
 * Inspiration by Silverstripe 3.0 GridField
 * http://silverstripe.org
 *
 * @package     Goma\Form\TableField
 * @property 	array state set of objects
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.1.1
 */
class TableFieldCSVExporter implements TableField_HTMLProvider, TableField_URLHandler {

    const ID = "TableFieldCSVExporter";

    /**
     * @var string[]
     */
    protected $additionalColumns = array(

    );

    /**
     * TableFieldCSVExporter constructor.
     * @param string[] $additionalFields
     */
    public function __construct($additionalFields = array())
    {
        $this->additionalColumns = $additionalFields;
    }

    /**
     * @param string[] $columns
     * @return $this
     */
    public function setAdditionalColumns($columns) {
        if(!is_array($columns)) {
            throw new InvalidArgumentException("Columns must be an array.");
        }

        $this->additionalColumns = $columns;
        return $this;
    }

    /**
     * provides HTML-fragments
     *
     * @name provideFragments
     * @param TableField $tableField
     * @return array
     */
    public function provideFragments($tableField)
    {
        $view = new ViewAccessableData();
        if($tableField->getConfig()->getComponentByType('TableFieldPaginator')) {
            return array(
                "pagination-footer-right" => $view->customise(array("link" => $tableField->externalURL() . "/exportCSV" . URLEND . "?redirect=" . urlencode($tableField->form()->controller->getRequest()->url)))->renderWith("form/tableField/exportButton.html")
            );
        } else {
            return array("footer" => $view->customise(array("link" => $tableField->externalURL() . "/exportCSV" . URLEND . "?redirect=" . urlencode($tableField->form()->controller->getRequest()->url)))->renderWith("form/tableField/exportButtonWithFooter.html"));
        }
    }

    /**
     * provides url-handlers as in controller, but without any permissions-functionallity
     *
     * this is NOT namespaced, so please be unique
     *
     * @name getURLHandlers
     * @access public
     * @return array
     */
    public function getURLHandlers($tableField)
    {
        return array(
            'exportCSV' => "exportCSV"
        );
    }

    /**
     * @param TableField $tableField
     * @param Request $request
     */
    public function exportCSV($tableField, $request) {
        $csv = new CSV("");
        $titleRow = array();
        foreach($tableField->getColumns() as $column) {
            $metadata = $tableField->getColumnMetadata($column);
            $title = $metadata['title'];

            $titleRow[] = $title;
        }

        foreach($this->additionalColumns as $column => $title) {
            $titleRow[] = $title;
        }

        $csv->addRow($titleRow);

        $data = clone $tableField->getData();
        $data->disablePagination();
        foreach($data as $record) {
            $row = array();

            foreach ($tableField->getColumns() as $column) {
                if($column != "Actions") {
                    $row[] = strip_tags($tableField->getColumnContent($record, $column));
                }
            }

            foreach($this->additionalColumns as $column => $title) {
                $row[] = $tableField->getDataFieldValue($record, $column);
            }

            $csv->addRow($row);
        }

        $file = ROOT . CACHE_DIRECTORY . "/export_" . randomString(10) . ".csv";
        FileSystem::write($file, $csv->csv());
        FileSystem::sendFile($file, "export.csv");
        exit;
    }
}
