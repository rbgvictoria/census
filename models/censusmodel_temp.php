<?php

class CensusModel extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getBeds($location=FALSE) {
        $this->db->select('b.guid, b.bed_name');
        $this->db->from('rbgcensus.bed b');
        $this->db->join('rbgcensus.plant p', 'b.bed_id=p.bed_id');
        if ($location) {
            $this->db->where('b.location', $location);
        }
        $this->db->group_by('b.bed_id');
        $this->db->order_by('b.bed_name');
        $query = $this->db->get();
        $ret = array();
        foreach ($query->result() as $row)
            $ret[$row->bed_name] = $row->bed_name;
        return $ret;
    }
    
    public function getBedsJSON($location=FALSE) {
        $this->db->select('b.guid, b.bed_name');
        $this->db->from('rbgcensus.bed b');
        $this->db->join('rbgcensus.plant p', 'b.bed_id=p.bed_id');
        if ($location) {
            $this->db->where('b.location', $location);
        }
        $this->db->group_by('b.bed_id');
        $this->db->order_by('b.bed_name');
        $query = $this->db->get();
        $ret = array();
        foreach ($query->result() as $row)
            $ret[] = $row->bed_name;
        return $ret;
    }
    
    public function getPrecincts($location=FALSE) {
        $this->db->select('b.precinct_name');
        $this->db->from('rbgcensus.bed b');
        $this->db->join('rbgcensus.plant p', 'b.bed_id=p.bed_id');
        if ($location) {
            $this->db->where('b.location', $location);
        }
        $this->db->group_by('b.precinct_name');
        $this->db->order_by('b.precinct_name');
        $query = $this->db->get();
        $ret = array();
        foreach ($query->result() as $row)
            $ret[$row->precinct_name] = $row->precinct_name;
        return $ret;
    }

    public function getPrecinctsJSON($location=FALSE) {
        $this->db->select('b.precinct_name');
        $this->db->from('rbgcensus.bed b');
        $this->db->join('rbgcensus.plant p', 'b.bed_id=p.bed_id');
        if ($location) {
            $this->db->where('b.location', $location);
        }
        $this->db->group_by('b.precinct_name');
        $this->db->order_by('b.precinct_name');
        $query = $this->db->get();
        $ret = array();
        foreach ($query->result() as $row)
            $ret[] = $row->precinct_name;
        return $ret;
    }

    public function getGridCodes() {
        $this->db->select('guid, code');
        $this->db->from('rbgcensus.grid');
        $this->db->order_by('code');
        $query = $this->db->get();
        $ret = array();
        foreach ($query->result() as $row)
            $ret[$row->code] = $row->code;
        return $ret;
    }
    
    public function findTaxa($query, $inclDeaccessioned=FALSE) {
        $this->db->select('t.taxon_id, t.guid, t.taxon_name, t.family, t.common_name');
        $this->db->from('rbgcensus.taxon t');
        $this->db->join('rbgcensus.accession a', 't.taxon_id=a.taxon_id');
        $this->db->join('rbgcensus.plant p', 'a.accession_id=p.accession_id');
        $this->db->join('rbgcensus.bed b', 'p.bed_id=b.bed_id');
        $this->db->join('rbgcensus.grid g', 'p.grid_id=g.grid_id', 'left');
        if (!$inclDeaccessioned) {
            $this->db->join('rbgcensus.deaccession d', 'p.plant_id=d.plant_id', 'left');
            $this->db->where('d.deaccession_id IS NULL', FALSE, FALSE);
        }
        $this->db->group_by('t.taxon_id');
        $this->db->order_by('t.taxon_name');
        foreach ($query as $key => $value) {
            if ($key == 'taxon')
                $this->db->like('t.taxon_name', $value, 'after');
            if ($key == 'common_name')
                $this->db->where("t.common_name LIKE '$value'", FALSE, FALSE);
            if ($key == 'family')
                $this->db->where('t.family', $value);
            if ($key == 'grid')
                $this->db->where('g.code', $value);
            if ($key == 'location')
                $this->db->where('b.location', $value);
            if ($key == 'precinct')
                $this->db->where('b.precinct', $value);
            if ($key == 'bed')
                $this->db->where('b.bed_name', $value);
            if ($key == 'wgs_code') {
                $this->db->join('rbgcensus.taxon_area ta', 't.taxon_id=ta.taxon_id');
                $this->db->join('wgs.wgs_region r', 'ta.area_id=r.region_id');
                if (strlen($value) == 1)
                    $this->db->where('r.level1_code', $value);
                elseif (strlen($value) == 2)
                    $this->db->where('r.level2_code', $value);
                elseif (strlen($value) == 3)
                    $this->db->where('r.level3_code', $value);
                else
                    $this->db->where('r.level4_code', $value);
            }
        }
        $query = $this->db->get();
        return $query->result_array();
    }
    
    public function findPlants($where, $limit=100, $offset=0, $inclDeaccessioned=FALSE) {
        $this->db->select('t.guid as taxon_guid, t.taxon_name, t.family, t.common_name, b.guid as bed_guid, b.location, b.bed_name, g.guid as grid_guid, 
            g.code as grid_code, a.guid as accession_guid, a.accession_number, p.guid as plant_guid, 
            p.plant_number, a.provenance_type_code, a.identification_status, p.date_planted', FALSE);
        if ($this->session->userdata('id')) {
            $this->db->select('CASE WHEN b.is_restricted=-1 THEN 1 WHEN t.no_public_display=1 THEN 1 ELSE 0 END AS restricted', FALSE);
        }
        $this->db->from('rbgcensus.taxon t');
        $this->db->join('rbgcensus.accession a', 't.taxon_id=a.taxon_id');
        $this->db->join('rbgcensus.plant p', 'a.accession_id=p.accession_id');
        $this->db->join('rbgcensus.bed b', 'p.bed_id=b.bed_id');
        $this->db->join('rbgcensus.grid g', 'p.grid_id=g.grid_id', 'left');
        if (!$inclDeaccessioned) {
            $this->db->join('rbgcensus.deaccession d', 'p.plant_id=d.plant_id', 'left');
            $this->db->where('d.deaccession_id IS NULL', FALSE, FALSE);
        }
        $this->db->order_by('t.taxon_name, a.accession_number, p.plant_number');
        foreach ($where as $key => $value) {
            if ($key == 'taxon')
                $this->db->like('t.taxon_name', $value, 'after');
            if ($key == 'taxon_guid')
                $this->db->where('t.guid', $value);
            if ($key == 'common_name')
                $this->db->where("t.common_name LIKE '$value'", FALSE, FALSE);
            if ($key == 'family')
                $this->db->where('t.family', $value);
            if ($key == 'grid')
                $this->db->where('g.code', $value);
            if ($key == 'grid_guid')
                $this->db->where('g.guid', $value);
            if ($key == 'location')
                $this->db->where('b.location', $value);
            if ($key == 'precinct')
                $this->db->where('b.precinct_name', $value);
            if ($key == 'bed')
                $this->db->where('b.bed_name', $value);
            if ($key == 'bed_guid')
                $this->db->where('b.guid', $value);
            if ($key == 'wgs_code') {
                $this->db->join('rbgcensus.taxon_area ta', 't.taxon_id=ta.taxon_id');
                $this->db->join('wgs.wgs_region r', 'ta.area_id=r.region_id');
                $this->db->group_by('t.taxon_id, a.accession_id, p.plant_id, b.bed_id, g.grid_id');
                if (strlen($value) == 1)
                    $this->db->where('r.level1_code', $value);
                elseif (strlen($value) == 2)
                    $this->db->where('r.level2_code', $value);
                elseif (strlen($value) == 3)
                    $this->db->where('r.level3_code', $value);
                else
                    $this->db->where('r.level4_code', $value);
            }
        }
        
        // Restricted
        if (!$this->session->userdata('id')) {
            $this->db->where('b.is_restricted !=', -1);
            $this->db->where('t.no_public_display !=', 1);
        }
        
        if ($limit)
            $this->db->limit($limit, $offset);
        $query = $this->db->get();
        return $query->result_array();
    }
    
    public function countPlants($query, $inclDeaccessioned=FALSE) {
        $this->db->select('count(DISTINCT p.plant_id) as num_plants, 
              count(DISTINCT a.accession_id) as num_accessions, count(DISTINCT t.taxon_id) as num_taxa', FALSE);
        $this->db->from('rbgcensus.taxon t');
        $this->db->join('rbgcensus.accession a', 't.taxon_id=a.taxon_id');
        $this->db->join('rbgcensus.plant p', 'a.accession_id=p.accession_id');
        $this->db->join('rbgcensus.bed b', 'p.bed_id=b.bed_id');
        $this->db->join('rbgcensus.grid g', 'p.grid_id=g.grid_id', 'left');
        if (!$inclDeaccessioned) {
            $this->db->join('rbgcensus.deaccession d', 'p.plant_id=d.plant_id', 'left');
            $this->db->where('d.deaccession_id IS NULL', FALSE, FALSE);
        }
        //$this->db->order_by('t.taxon_name, a.accession_number, p.plant_number');
        foreach ($query as $key => $value) {
            if ($key == 'taxon')
                $this->db->like('t.taxon_name', $value, 'after');
            if ($key == 'taxon_guid')
                $this->db->where('t.guid', $value);
            if ($key == 'common_name')
                $this->db->where("t.common_name LIKE '$value'", FALSE, FALSE);
            if ($key == 'family')
                $this->db->where('t.family', $value);
            if ($key == 'grid')
                $this->db->where('g.code', $value);
            if ($key == 'grid_guid')
                $this->db->where('g.guid', $value);
            if ($key == 'location')
                $this->db->where('b.location', $value);
            if ($key == 'precinct')
                $this->db->where('b.precinct_name', $value);
            if ($key == 'bed')
                $this->db->where('b.bed_name', $value);
            if ($key == 'bed_guid')
                $this->db->where('b.guid', $value);
            if ($key == 'wgs_code') {
                $this->db->join('rbgcensus.taxon_area ta', 't.taxon_id=ta.taxon_id');
                $this->db->join('wgs.wgs_region r', 'ta.area_id=r.region_id');
                //$this->db->group_by('t.taxon_id, a.accession_id, p.plant_id, b.bed_id, g.grid_id');
                if (strlen($value) == 1)
                    $this->db->where('r.level1_code', $value);
                elseif (strlen($value) == 2)
                    $this->db->where('r.level2_code', $value);
                elseif (strlen($value) == 3)
                    $this->db->where('r.level3_code', $value);
                else
                    $this->db->where('r.level4_code', $value);
            }
        }

        // Restricted
        if (!$this->session->userdata('id')) {
            $this->db->where('b.is_restricted !=', -1);
            $this->db->where('t.no_public_display !=', 1);
        }
        
        $query = $this->db->get();
        return $query->row_array();
    }
    
    public function getTaxon($guid=FALSE, $taxonname=FALSE, $accessionno=FALSE) {
        $this->db->select('t.taxon_id, t.guid, t.taxon_name, t.scientific_name_authorship, t.family, t.common_name, t.plant_type');
        $this->db->from('rbgcensus.taxon t');
        $this->db->join('rbgcensus.accession a', 't.taxon_id=a.taxon_id');
        $this->db->group_by('t.taxon_id');
        if ($guid)
            $this->db->where('t.guid', $guid);
        elseif ($taxonname)
            $this->db->where('t.taxon_name', $taxonname);
        elseif ($accessionno)
            $this->db->where('a.accession_number', $accessionno);
        $query = $this->db->get();
        if ($query->num_rows())
            return $query->row_array();
        else
            return FALSE;
    }
    
    public function getWGSRegions($guid) {
        $this->db->select('level1_code, level1_name, level2_code, level2_name, 
            level3_code, level3_name, level4_code, level4_name');
        $this->db->from('rbgcensus.taxon t');
        $this->db->join('rbgcensus.taxon_area ta', 't.taxon_id=ta.taxon_id');
        $this->db->join('wgs.wgs_region r', 'ta.area_id=region_id');
        $this->db->where('t.guid', $guid);
        $this->db->order_by('r.node_number');
        $query = $this->db->get();
        return $query->result_array();
    }
    
    public function getBedInfo($guid) {
        $this->db->select('bed_name, bed_code, location, precinct_name');
        $this->db->from('rbgcensus.bed');
        $this->db->where('guid', $guid);
        $query = $this->db->get();
        if ($query->num_rows())
            return $query->row_array();
        else
            return FALSE;
    }
    
    public function getGridInfo($guid) {
        $this->db->select('code, section');
        $this->db->from('rbgcensus.grid');
        $this->db->where('guid', $guid);
        $query = $this->db->get();
        if ($query->num_rows())
            return $query->row_array();
        else
            return FALSE;
    }
    
    public function getAccessionInfo($guid) {
        $this->db->select('t.guid AS taxon_guid, t.taxon_name, t.family, t.common_name, t.plant_type,
            a.guid AS accession_guid, a.accession_number, a.provenance_type_code, a.provenance_history,
            a.collector_name, a.identification_status');
        $this->db->from('rbgcensus.taxon t');
        $this->db->join('rbgcensus.accession a', 't.taxon_id=a.taxon_id');
        $this->db->where('a.guid', $guid);
        $query = $this->db->get();
        if ($query->num_rows())
            return $query->row_array();
        else
            return FALSE;
    }
    
    public function getPlantInfoByAccession($guid, $inclDeaccessioned=FALSE) {
        $this->db->select('p.guid as plant_guid, p.plant_number, p.date_planted, b.guid as bed_guid, b.bed_name,
            g.guid as grid_guid, g.code as grid_code');
        $this->db->from('rbgcensus.accession a');
        $this->db->join('rbgcensus.plant p', 'a.accession_id=p.accession_id');
        $this->db->join('rbgcensus.bed b', 'p.bed_id=b.bed_id');
        $this->db->join('rbgcensus.grid g', 'p.grid_id=g.grid_id', 'left');
        $this->db->where('a.guid', $guid);
        if (!$inclDeaccessioned) {
            $this->db->join('rbgcensus.deaccession d', 'p.plant_id=d.plant_id', 'left');
            $this->db->where('d.deaccession_id IS NULL', FALSE, FALSE);
        }
        $this->db->order_by('p.plant_number');
        $query = $this->db->get();
        return $query->result_array();
    }
    
    public function getPlantInfo($guid) {
        $this->db->select('t.guid AS taxon_guid, t.taxon_name, t.family, t.common_name, t.plant_type,
            a.guid AS accession_guid, a.accession_number, a.provenance_type_code, a.provenance_history,
            a.collector_name, a.identification_status, p.guid as plant_guid, p.plant_number, 
            p.date_planted, b.guid as bed_guid, b.location, b.precinct_name, b.bed_name, g.guid as grid_guid, 
            g.code as grid_code');
        $this->db->from('rbgcensus.taxon t');
        $this->db->join('rbgcensus.accession a', 't.taxon_id=a.taxon_id');
        $this->db->join('rbgcensus.plant p', 'a.accession_id=p.accession_id');
        $this->db->join('rbgcensus.bed b', 'p.bed_id=b.bed_id');
        $this->db->join('rbgcensus.grid g', 'p.grid_id=g.grid_id', 'left');
        $this->db->where('p.guid', $guid);
        $query = $this->db->get();
        if ($query->num_rows())
            return $query->row_array();
        else
            return FALSE;
    }
    
    public function getWGSCode($fullname) {
        $query = $this->db->query("SELECT w.wgs_level, w.region_code
            FROM wgs.wgs_region w
            JOIN rbgcensus.taxon_area t ON w.region_id=t.area_id
            WHERE t.area_fullname='$fullname'
            GROUP BY w.wgs_level, w.region_code");
        if ($query->num_rows()) {
            $row = $query->row();
            return array(
                'level' => $row->wgs_level,
                'code' => $row->region_code
            );
        }
        else 
            return FALSE;
    }
    
}


/* End of file censusmodel.php */
/* Location: ./models/censusmodel.php */