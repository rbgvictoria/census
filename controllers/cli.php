<?php

class CLI extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('UpdateTables');
    }
    
    public function updateTables() {
        if (!$this->input->is_cli_request() ) {
            show_error('You don\'t have access to this page.', 403);
        }
        echo 'updateTaxon' . "\n";
        $this->updatetables->updateTaxon();
        echo "updateClassification\n";
        $this->updatetables->updateClassification();
        echo "updateBed\n";
        $this->updatetables->updateBed();
        echo "updateGrid\n";
        $this->updatetables->updateGrid();
        echo "updateAccession\n";
        $this->updatetables->updateAccession();
        echo "updatePlant\n";
        $this->updatetables->updatePlant();
        echo "updateDeaccessioned\n";
        $this->updatetables->updateDeaccessioned();
        //$this->updatetables->deleteDuplicates();
        //$this->updatetables->deduplicateGrids();
        echo "attributes\n";
        $this->updatetables->attributes();
        echo "collections\n";
        $this->updatetables->collections();
    }
    
    public function duplicateAccessions() {
        $this->updatetables->duplicateAccessions();
    }
    
    public function updateTaxon() {
        $this->updatetables->updateTaxon();
    }
    
    public function checkDeaccessioned() {
        $this->updatetables->checkDeaccessioned();
    }
    
    public function updateDeaccessioned() {
        $this->updatetables->updateDeaccessioned();
    }
}