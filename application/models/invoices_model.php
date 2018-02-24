<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Invoices - Open Source Web Scheduler
 *
 * @package     EasyInvoices
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) 2013 - 2016, Alex Tselegidis
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        http://easyinvoices.org
 * @since       v1.0.0
 * ---------------------------------------------------------------------------- */

/**
 * Invoices Model
 *
 * @package Models
 */
class Invoices_Model extends CI_Model {
    /**
     * Class Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Add an invoice record to the database.
     *
     * This method adds a new invoice to the database. If the 
     * invoice doesn't exists it is going to be inserted, otherwise
     * the record is going to be updated.
     *
     * @param array $invoice Associative array with the invoice
     * data. Each key has the same name with the database fields.
     * @return int Returns the invoices id.
     */
    public function add($invoice) {
        // Validate the invoice data before doing anything.
        $this->validate($invoice);

        // Perform insert() or update() operation.
        if (!isset($invoice['id'])) {
            $invoice['id'] = $this->insert($invoice);
        } else {
            $this->update($invoice);
        }

        return $invoice['id'];
    }


    /**
     * Insert a new invoice record to the database.
     *
     * @param array $invoice Associative array with the invoice's
     * data. Each key has the same name with the database fields.
     * @return int Returns the id of the new record.
     */
    private function insert($invoice) {
        $invoice['invoice_datetime'] = date('Y-m-d H:i:s');

        if (!$this->db->insert('ea_invoices', $invoice)) {
            throw new Exception('Could not insert invoice record.');
        }

        return intval($this->db->insert_id());
    }

    /**
     * Update an existing invoice record in the database.
     *
     * The invoice data argument should already include the record
     * id in order to process the update operation.
     *
     * @expectedException DatabaseException Raises when the update operation
     * failes to complete successfully.
     *
     * @param array $invoice Associative array with the invoice's
     * data. Each key has the same name with the database fields.
     */
    public function update($invoice) {
        $this->db->where('id', $invoice['id']);
        if (!$this->db->update('ea_invoices', $invoice)) {
            throw new Exception('Could not update invoice record.');
        }
    }


    /**
     * Validate invoice data before the insert or update operations
     * are executed.
     *
     * @param array $invoice Contains the invoice data.
     * @return bool Returns the validation result.
     */
    public function validate($invoice) {
        $this->load->helper('data_validation');

        // If a invoice id is given, check wether the record exists
        // in the database.
        if (isset($invoice['id'])) {
            $num_rows = $this->db->get_where('ea_invoices',
                    array('id' => $invoice['id']))->num_rows();
            if ($num_rows == 0) {
                throw new Exception('Provided invoice id does not '
                        . 'exist in the database.');
            }
        }


        return TRUE;
    }

    /**
     * Delete an existing invoice record from the database.
     *
     * @expectedException InvalidArgumentException Raises when the $invoice_id
     * is not an integer.
     *
     * @param numeric $invoice_id The record id to be deleted.
     * @return bool Returns the delete operation result.
     */
    public function delete($invoice_id) {
        if (!is_numeric($invoice_id)) {
            throw new Exception('Invalid argument type $invoice_id (value:"' . $invoice_id . '")');
        }

        $num_rows = $this->db->get_where('ea_invoices', array('id' => $invoice_id))->num_rows();

        if ($num_rows == 0) {
            return FALSE; // Record does not exist.
        }

        $this->db->where('id', $invoice_id);
        return $this->db->delete('ea_invoices');
    }

    /**
     * Get a specific row from the invoices table.
     *
     * @param numeric $invoice_id The record's id to be returned.
     * @return array Returns an associative array with the selected
     * record's data. Each key has the same name as the database
     * field names.
     */
    public function get_row($invoice_id) {
        if (!is_numeric($invoice_id)) {
            throw new Exception('Invalid argument given. Expected '
                    . 'integer for the $invoice_id : ' . $invoice_id);
        }
        return $this->db->get_where('ea_invoices',
                array('id' => $invoice_id))->row_array();
    }

    /**
     * Get a specific field value from the database.
     *
     * @param string $field_name The field name of the value to be returned.
     * @param numeric $invoice_id The selected record's id.
     * @return string Returns the records value from the database.
     */
    public function get_value($field_name, $invoice_id) {
        if (!is_numeric($invoice_id)) {
            throw new Exception('Invalid argument given, expected '
                    . 'integer for the $invoice_id : ' . $invoice_id);
        }

        if (!is_string($field_name)) {
            throw new Exception('Invalid argument given, expected '
                    . 'string for the $field_name : ' . $field_name);
        }

        if ($this->db->get_where('ea_invoices',
                array('id' => $invoice_id))->num_rows() == 0) {
            throw new Exception('The record with the provided id '
                    . 'does not exist in the database : ' . $invoice_id);
        }

        $row_data = $this->db->get_where('ea_invoices',
                array('id' => $invoice_id))->row_array();

        if (!isset($row_data[$field_name])) {
            throw new Exception('The given field name does not '
                    . 'exist in the database : ' . $field_name);
        }

        return $row_data[$field_name];
    }

    /**
     * Get all, or specific records from invoice's table.
     *
     * @example $this->Model->getBatch('id = ' . $recordId);
     *
     * @param string $where_clause (OPTIONAL) The WHERE clause of
     * the query to be executed. DO NOT INCLUDE 'WHERE' KEYWORD.
     * @return array Returns the rows from the database.
     */
    public function get_batch($where_clause = '') {
        if ($where_clause != '') {
            $this->db->where($where_clause);
        }

        return $this->db->get('ea_invoices')->result_array();
    }

    /**
     * Generate a unique hash for the given invoice data.
     *
     * This method uses the current date-time to generate a unique
     * hash string that is later used to identify this invoice.
     * Hash is needed when the email is send to the user with an
     * edit link.
     *
     * @return string Returns the unique invoice hash.
     */
    public function generate_hash() {
        $current_date = new DateTime();
        return md5($current_date->getTimestamp());
    }


}

/* End of file invoices_model.php */
/* Location: ./application/models/invoices_model.php */
