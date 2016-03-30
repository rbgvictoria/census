<?php

class AutoCompleteModel extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getTaxa($q, $access_key=FALSE, $inclDeaccessioned=FALSE) {
        if (strpos($q, ' ')) {
            $this->db->select('t.taxon_name');
            $this->db->from('rbgcensus.taxon t');
            $this->db->join('rbgcensus.accession a', 't.taxon_id=a.taxon_id');
            $this->db->join('rbgcensus.plant p', 'a.accession_id=p.accession_id');
            $this->db->join('rbgcensus.bed b', 'p.bed_id=b.bed_id');
            $this->db->like("lower(t.taxon_name)", $q, 'after');
            $this->db->where('t.accepted_names IS NULL', FALSE, FALSE);
            $this->db->group_by('taxon_name');
            $this->db->order_by('taxon_name');
            if (!$access_key) {
                $this->db->where('(t.no_public_display IS NULL OR t.no_public_display=0)', FALSE, FALSE);
                $this->db->where('(b.is_restricted IS NULL OR b.is_restricted=0)', FALSE, FALSE);
            }
            
            if (!$inclDeaccessioned) {
                $this->db->join('rbgcensus.deaccession d', 'p.plant_id=d.plant_id', 'left');
                $this->db->where('d.deaccession_id IS NULL', FALSE, FALSE);
            }

            $query = $this->db->get();
            if ($query->num_rows()) {
                $ret = array();
                foreach ($query->result() as $row)
                    $ret[] = $row->taxon_name;
                return $ret;
            }
            else
                return FALSE;
        }
        else {
            $this->db->select('t.genus');
            $this->db->from('rbgcensus.taxon t');
            $this->db->join('rbgcensus.accession a', 't.taxon_id=a.taxon_id');
            $this->db->join('rbgcensus.plant p', 'a.accession_id=p.accession_id');
            $this->db->join('rbgcensus.bed b', 'p.bed_id=b.bed_id');
            $this->db->like("lower(t.taxon_name)", $q, 'after');
            $this->db->where('t.accepted_names IS NULL', FALSE, FALSE);
            $this->db->group_by('genus');
            $this->db->order_by('genus');
            if (!$access_key) {
                $this->db->where('(t.no_public_display IS NULL OR t.no_public_display=0)', FALSE, FALSE);
                $this->db->where('(b.is_restricted IS NULL OR b.is_restricted=0)', FALSE, FALSE);
            }
            if (!$inclDeaccessioned) {
                $this->db->join('rbgcensus.deaccession d', 'p.plant_id=d.plant_id', 'left');
                $this->db->where('d.deaccession_id IS NULL', FALSE, FALSE);
            }

            $query = $this->db->get();
            if ($query->num_rows()) {
                $ret = array();
                foreach ($query->result() as $row)
                    $ret[] = $row->genus;
                return $ret;
            }
            else
                return FALSE;
        }
    }
    
    public function getFamilies($q, $inclDeaccessioned=FALSE) {
        $this->db->select('c.family');
        $this->db->from('rbgcensus.taxon t');
        $this->db->join('rbgcensus.classification c', 't.genus_id=c.genus_id');
        $this->db->join('rbgcensus.accession a', 't.taxon_id=a.taxon_id', 'left');
        $this->db->join('rbgcensus.plant p', 'a.accession_id=p.accession_id', 'left');
        $this->db->join('rbgcensus.bed b', 'p.bed_id=b.bed_id', 'left');
        $this->db->like("lower(c.family)", $q, 'after');
        //$this->db->where('t.accepted_names IS NULL', FALSE, FALSE);
        $this->db->group_by('c.family');
        $this->db->order_by('c.family');
        if (!$access_key) {
            $this->db->where('(t.no_public_display IS NULL OR t.no_public_display=0)', FALSE, FALSE);
            $this->db->where('(b.is_restricted IS NULL OR b.is_restricted=0)', FALSE, FALSE);
        }
        if (!$inclDeaccessioned) {
            $this->db->join('rbgcensus.deaccession d', 'p.plant_id=d.plant_id', 'left');
            $this->db->where('d.deaccession_id IS NULL', FALSE, FALSE);
        }

        $query = $this->db->get();
        if ($query->num_rows()) {
            $ret = array();
            foreach ($query->result() as $row)
                $ret[] = $row->family;
            return $ret;
        }
        else
            return FALSE;
    }
    
    public function getCommonNames($q, $access_key=FALSE) {
        $this->db->select('t.common_name');
        $this->db->from('rbgcensus.taxon t');
        $this->db->join('rbgcensus.accession a', 't.taxon_id=a.taxon_id', 'left');
        $this->db->join('rbgcensus.plant p', 'a.accession_id=p.accession_id', 'left');
        $this->db->join('rbgcensus.bed b', 'p.bed_id=b.bed_id', 'left');
        $this->db->like("lower(t.common_name)", $q, 'both');
        //$this->db->where('t.accepted_names IS NULL', FALSE, FALSE);
        $this->db->group_by('common_name');
        $this->db->order_by('common_name');
        if (!$access_key) {
            $this->db->where('(t.no_public_display IS NULL OR t.no_public_display=0)', FALSE, FALSE);
            $this->db->where('(b.is_restricted IS NULL OR b.is_restricted=0)', FALSE, FALSE);
        }

        $query = $this->db->get();
        if ($query->num_rows()) {
            $ret = array();
            foreach ($query->result() as $row)
                $ret[] = $row->common_name;
            return $ret;
        }
        else
            return FALSE;
    }
    
    public function getWgsFullName($q) {
        $this->db->select('area_fullname');
        $this->db->from('rbgcensus.taxon_area');
        $this->db->like("lower(area_fullname)", $q, 'both');
        //$this->db->where('t.accepted_names IS NULL', FALSE, FALSE);
        $this->db->group_by('area_fullname');
        $this->db->order_by('area_fullname');

        $query = $this->db->get();
        if ($query->num_rows()) {
            $ret = array();
            foreach ($query->result() as $row)
                $ret[] = $row->area_fullname;
            return $ret;
        }
        else
            return FALSE;
    }
    
}

/* End of file autocompletemodel.php */
/* Location: ./models/autocompletemodel.php */