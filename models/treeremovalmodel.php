<?php

class TreeRemovalModel extends CI_Model {
    private $mydb;
    public function __construct() {
        $this->mydb = $this->load->database('living_mysql', TRUE);
    }
    
    public function insertTreeRemovalRecord($data) {
        $insert = array(
            'timestamp_created' => date('Y-m-d H:i:s'),
            'request_date' => $data['date'],
            'accession_number' => $data['accession_number'],
            'counter_no' => $data['counter_no'] ? $data['counter_no'] : NULL,
            'species' => $data['species'],
            'grid' => $data['grid'] ? $data['grid'] : NULL,
            'location' => $data['location'] ? $data['location'] : NULL,
            'number_of_plants' => $data['number_of_plants'],
            'elsewhere_in_rbg' => $this->msAccessYesNo($data['elsewhere_in_rbg']),
            'priority' => $data['priority'],
            'psb_notified' => $this->msAccessYesNo($data['psb_notified']),
            'habitat_assessment_required' => $this->msAccessYesNo($data['habitat_assessment_required']),
            'comments' => $data['comments'] ? $data['comments'] : NULL,
            'requested_by' => $data['requested_by'],
            'feedback' => $this->msAccessYesNo($data['feedback'])
        );
        $this->mydb->insert('tree_removal', $insert);
        return $insert;
    }
    
    private function msAccessYesNo($value) {
        $ret = NULL;
        switch ($value) {
            case 1:
                $ret = -1;
                break;
            case 0:
                $ret = 0;
                break;
            default:
                break;
        }
        return $ret;
    }
}

/* End of file treeremovalmodel.php */
/* Location: ./models/treeremovalmodel.php */