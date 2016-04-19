<?php

class CensusModel extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getLastModifiedDate() {
        $query = $this->db->query("SELECT max(timestamp_modified) as timestamp_last_modified FROM rbgcensus.plant");
        $row = $query->row();
        return $row->timestamp_last_modified;
    }
    
    public function getBeds($location=FALSE, $access_key=FALSE) {
        $this->db->select("b.guid, CASE WHEN b.location='Cranbourne' AND b.bed_type='bed' 
            THEN pb.bed_name || ': ' || b.bed_name ELSE b.bed_name END AS bed_name", FALSE);
        $this->db->from('rbgcensus.bed b');
        $this->db->join('rbgcensus.plant p', 'b.bed_id=p.bed_id');
        $this->db->join('rbgcensus.bed pb', 'b.parent_id=pb.bed_id', 'left');
        if (!$this->session->userdata('id') && !$access_key) {
            $this->db->where('b.is_restricted !=', -1);
        }
        if ($location) {
            $this->db->where('b.location', $location);
        }
        $this->db->group_by('b.bed_id');
        $this->db->group_by('pb.bed_id');
        $this->db->order_by('bed_name');
        $query = $this->db->get();
        $ret = array();
        foreach ($query->result() as $row)
            $ret[$row->bed_name] = $row->bed_name;
        return $ret;
    }
    
    public function getBedsJSON($location=FALSE, $precinct=FALSE, $subprecinct=FALSE, $access_key=FALSE) {
        $this->db->select("b.guid, CASE WHEN b.location='Cranbourne' AND b.bed_type='bed' 
            THEN pb.bed_name || ': ' || b.bed_name ELSE b.bed_name END AS bed_name", FALSE);
        $this->db->from('rbgcensus.bed b');
        $this->db->join('rbgcensus.plant p', 'b.bed_id=p.bed_id');
        $this->db->join('rbgcensus.bed pb', 'b.parent_id=pb.bed_id', 'left');
        if (!$this->session->userdata('id') && !$access_key) {
            $this->db->where('b.is_restricted !=', -1);
        }
        if ($location) {
            $this->db->where('b.location', $location);
            if ($location == 'Cranbourne' && $precinct) {
                $this->db->where('b.precinct_name', $precinct);
            }
            if ($location == 'Cranbourne' && $subprecinct) {
                $this->db->where('b.subprecinct_name', $subprecinct);
            }
        }
        $this->db->group_by('b.bed_id');
        $this->db->group_by('pb.bed_id');
        $this->db->order_by('bed_name');
        $query = $this->db->get();
        $ret = array();
        foreach ($query->result() as $row) {
            $ret[] = $row->bed_name;
        }
        return $ret;
    }
    
    public function getSubprecinctsJSON($location=FALSE, $precinct=FALSE, $access_key=FALSE) {
        $this->db->select('b.subprecinct_name');
        $this->db->from('rbgcensus.bed b');
        $this->db->join('rbgcensus.plant p', 'b.bed_id=p.bed_id');
        if (!$this->session->userdata('id') && !$access_key) {
            $this->db->where('b.is_restricted !=', -1);
        }
        if ($location) {
            $this->db->where('b.location', $location);
        }
        if ($precinct) {
            $this->db->where('b.precinct_name', $precinct);
        }
        $this->db->group_by('b.subprecinct_name');
        $this->db->order_by('b.subprecinct_name');
        $query = $this->db->get();
        $ret = array();
        foreach ($query->result() as $row) {
            $ret[] = $row->subprecinct_name;
        }
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

    public function getSubprecincts($location=FALSE) {
        $this->db->select('b.subprecinct_name');
        $this->db->from('rbgcensus.bed b');
        $this->db->join('rbgcensus.plant p', 'b.bed_id=p.bed_id');
        if ($location) {
            $this->db->where('b.location', $location);
        }
        $this->db->group_by('b.subprecinct_name');
        $this->db->order_by('b.subprecinct_name');
        $query = $this->db->get();
        $ret = array();
        foreach ($query->result() as $row) {
            $ret[$row->subprecinct_name] = $row->subprecinct_name;
        }
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
    
    /*public function findTaxa($query, $inclDeaccessioned=FALSE) {
        $this->db->select('t.taxon_id, t.guid, t.taxon_name, c.family, t.common_name');
        $this->db->from('rbgcensus.taxon t');
        $this->db->join('rbgcensus.classification c', 't.genus_id=c.genus_id', 'left');
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
                $this->db->where('c.family', $value);
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
    }*/
    
    public function searchBaseQuery($where, $inclDeaccessioned=FALSE, $syn=false) {
        $this->db->from('rbgcensus.taxon t');
        $this->db->join('rbgcensus.classification c', 't.genus_id=c.genus_id', 'left');
        if ($syn) {
            $this->db->join('rbgcensus.taxon at', 't.accepted_name_usage_id=at.taxon_id');
            $this->db->join('rbgcensus.accession a', 'at.taxon_id=a.taxon_id');
        }
        else {
            $this->db->join('rbgcensus.accession a', 't.taxon_id=a.taxon_id');
        }
        $this->db->join('rbgcensus.plant p', 'a.accession_id=p.accession_id');
        $this->db->join('rbgcensus.bed b', 'p.bed_id=b.bed_id');
        $this->db->join('rbgcensus.bed pb', 'b.parent_id=pb.bed_id', 'left');
        $this->db->join('rbgcensus.grid g', 'p.grid_id=g.grid_id', 'left');
        $this->db->join('rbgcensus.deaccession d', 'p.plant_id=d.plant_id', 'left');
        if (!$inclDeaccessioned) {
            $this->db->where('d.deaccession_id IS NULL', FALSE, FALSE);
        }
        
        if ($where) {
            foreach ($where as $key => $value) {
                if ($key == 'taxon')
                    $this->db->like('t.taxon_name', $value, 'after');
                if ($key == 'taxon_guid')
                    $this->db->where('t.guid', $value);
                if ($key == 'common_name')
                    $this->db->where("t.common_name LIKE '$value'", FALSE, FALSE);
                if ($key == 'family')
                    $this->db->where('c.family', $value);
                if ($key == 'grid')
                    $this->db->where('g.code', $value);
                if ($key == 'grid_guid')
                    $this->db->where('g.guid', $value);
                if ($key == 'location') {
                    $this->db->where('b.location', $value);
                }
                if ($key == 'precinct') {
                    $this->db->where("b.precinct_name", $value);
                }
                if ($key == 'subprecinct') {
                    $this->db->where("b.subprecinct_name", $value);
                }
                if ($key == 'bed') {
                    if (strpos($value, ':') !== FALSE) {
                        $subprecinct = substr($value, 0, strpos($value, ':'));
                        $bed = substr($value, strpos($value, ':')+2);
                        $this->db->where('b.subprecinct_name', $subprecinct);
                        $this->db->where('b.bed_name', $bed);
                    }
                    else {
                        $this->db->where('b.bed_name', $value);
                    }
                }
                if ($key == 'bed_guid') {
                    $this->db->where('b.guid', $value);
                }
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
                if ($key == 'provenance_type') {
                    $this->db->where('a.provenance_type_code', $value);
                }
                if ($key == 'identification_status') {
                    if ($value == 2) {
                        $this->db->where_in('a.identification_status', array('2', '2E', '2L', '2N', '2X'));
                    }
                    else {
                        $this->db->where('a.identification_status', $value);
                    }
                }
            }
        }
        
        // Restricted
        if (!$this->session->userdata('id')) {
            $this->db->where('b.is_restricted !=', -1);
            $this->db->where('(t.no_public_display!=1 OR t.no_public_display IS NULL)', FALSE, FALSE);
        }
    }
    
    public function findPlants($where, $order='taxon_name', $limit=100, $offset=0, $inclDeaccessioned=FALSE) {
        $this->searchBaseQuery($where, $inclDeaccessioned);
        
        $this->db->select("t.guid as taxon_guid, t.taxon_name, c.family, t.common_name, b.guid as bed_guid, b.location, 
            b.bed_type, b.bed_name, pb.guid as parent_bed_guid, pb.bed_name as parent_bed_name, g.guid as grid_guid,
            g.code as grid_code, a.guid as accession_guid, a.accession_number, p.guid as plant_guid, 
            p.plant_number, a.provenance_type_code, a.identification_status, p.date_planted", FALSE);
        if ($this->session->userdata('id')) {
            $this->db->select('CASE WHEN b.is_restricted=-1 THEN 1 WHEN t.no_public_display=1 THEN 1 ELSE 0 END AS restricted', FALSE);
        }
        if ($inclDeaccessioned) {
            $this->db->select('CASE WHEN d.deaccession_id IS NOT NULL THEN 1 ELSE 0 END AS deaccessioned', FALSE);
        }
        
        if ($where) {
            foreach ($where as $key => $value) {
                if ($key == 'wgs_code') {
                    $this->db->group_by('t.taxon_id');
                    $this->db->group_by('c.genus_id');
                    $this->db->group_by('b.bed_id');
                    $this->db->group_by('pb.bed_id');
                    $this->db->group_by('g.grid_id');
                    $this->db->group_by('a.accession_id');
                    $this->db->group_by('p.plant_id');
                }
            }
        }
        
        switch ($order) {
            case 'taxon_name':
                $this->db->order_by('taxon_name');
                break;
            case 'family':
                $this->db->order_by('family, taxon_name');
                break;
            case 'bed':
                $this->db->order_by('bed_name, taxon_name');
                break;
            default:
                $this->db->order_by('taxon_name');
                break;
        };
        
        $this->db->order_by('a.accession_number, p.plant_number');
        
        if ($limit)
            $this->db->limit($limit, $offset);
        $query = $this->db->get();
        return $query->result_array();
    }
    
    public function findPlantsDownload($where, $inclDeaccessioned=FALSE) {
        $this->searchBaseQuery($where, $inclDeaccessioned);
        $this->db->select('a.accession_number as "accessionNumber", p.plant_number as "plantNumber", 
            c.family, t.taxon_name as "scientificName", t.common_name as "vernacularName"');
        if ($this->session->userdata('id')) {
            $this->db->select('a.identification_status as "identificationVerificationStatus"');
        }
        else {
            $this->db->select('substring(a.identification_status from 1 for 1) as "identificationVerificationStatus"', FALSE);
        }
        $this->db->select('b.location as locality, b.bed_name as bed');
        if ($this->session->userdata('id')) {
            $this->db->select('g.code as grid');
        }
        $this->db->select('a.provenance_type_code as "provenanceTypeCode", p.date_planted as "datePlanted"');
        if ($this->session->userdata('id')) {
            $this->db->select('CASE WHEN b.is_restricted=-1 THEN 1 WHEN t.no_public_display=1 THEN 1 ELSE 0 END AS restricted', FALSE);
            if ($inclDeaccessioned) {
                $this->db->select('CASE WHEN d.deaccession_id IS NOT NULL THEN 1 ELSE 0 END AS deaccessioned', FALSE);
            }
        }
        $this->db->select("'Royal Botanic Gardens Victoria' as \"rightsHolder\"", FALSE);
        $this->db->select("'http://creativecommons.org/licenses/by-nc-nd/4.0/' as license", FALSE);
        foreach ($where as $key) {
            if ($key == 'wgs_code') {
                $this->db->group_by('t.taxon_id, a.accession_id, p.plant_id, b.bed_id, g.grid_id');
            }
        }
        $this->db->order_by('t.taxon_name, a.accession_number, p.plant_number');
        $query = $this->db->get();
        return $query->result_array();
    }
    
    public function findTaxaDownload($where, $inclDeaccessioned=FALSE) {
        $this->searchBaseQuery($where, $inclDeaccessioned);
        $this->db->select("t.taxon_name AS \"scientificName\", scientific_name_authorship as \"scientificNameAuthorship\",
            common_name AS \"vernacularName\", 
            CASE plant_type WHEN '#' THEN 1 WHEN '&' THEN 1 ELSE 0 END AS \"isAutralianNative\", 
            CASE plant_type WHEN '#' THEN 1 WHEN '!' THEN 1 ELSE 0 END as \"isEndangered\",
            c.kingdom, c.phylum, c.class, c.subclass, c.superorder, c.order, c.family, c.genus", FALSE);
        /*if ($this->session->userdata('id')) {
            $this->db->select('CASE WHEN b.is_restricted=-1 THEN 1 WHEN t.no_public_display=1 THEN 1 ELSE 0 END AS restricted', FALSE);
        }*/
        /*if ($inclDeaccessioned) {
            $this->db->select('CASE WHEN d.deaccession_id IS NOT NULL THEN 1 ELSE 0 END AS deaccessioned', FALSE);
        }*/
        $this->db->select("'Royal Botanic Gardens Victoria' as \"rightsHolder\"", FALSE);
        $this->db->select("'http://creativecommons.org/licenses/by-nc-nd/4.0/' as license", FALSE);
        foreach ($where as $key) {
            if ($key == 'wgs_code') {
                $this->db->group_by('t.taxon_id, a.accession_id, p.plant_id, b.bed_id, g.grid_id');
            }
        }
        $this->db->group_by('t.taxon_id, c.genus_id');
        $this->db->order_by('t.taxon_name');
        $query = $this->db->get();
        return $query->result_array();
    }
    
    public function countPlants($where, $inclDeaccessioned=FALSE) {
        $this->searchBaseQuery($where, $inclDeaccessioned);

        $this->db->select("count(DISTINCT p.plant_id) as num_plants, 
              count(DISTINCT a.accession_id) as num_accessions, 
              count(DISTINCT t.taxon_id) as num_taxa,
              coalesce(COUNT(DISTINCT CASE WHEN b.location='Melbourne' THEN p.plant_id END), 0) AS melbourne, 
              coalesce(COUNT(DISTINCT CASE WHEN b.location='Cranbourne' THEN p.plant_id END), 0) AS cranbourne", FALSE);
        
        $query = $this->db->get();
        return $query->row_array();
    }
    
    public function getTaxon($guid=FALSE, $taxonname=FALSE, $accessionno=FALSE) {
        $this->db->select('t.taxon_id, t.guid, t.taxon_name, t.scientific_name_authorship, t.common_name');
        $this->db->select("CASE plant_type WHEN '#' THEN 1 WHEN '&' THEN 1 ELSE 0 END AS \"isAutralianNative\", 
            CASE plant_type WHEN '#' THEN 1 WHEN '!' THEN 1 ELSE 0 END as \"isEndangered\"", false);
        $this->db->select('c.genus, c.family, c.order, c.superorder, c.subclass, c.class, c.phylum, c.kingdom');
        $this->db->from('rbgcensus.taxon t');
        $this->db->join('rbgcensus.classification c', 't.genus_id=c.genus_id', 'left');
        $this->db->join('rbgcensus.accession a', 't.taxon_id=a.taxon_id');
        $this->db->group_by('t.taxon_id');
        $this->db->group_by('c.genus_id');
        if ($guid) {
            $this->db->where('t.guid', $guid);
        }
        elseif ($taxonname) {
            $this->db->where('t.taxon_name', $taxonname);
        }
        elseif ($accessionno) {
            $this->db->where('a.accession_number', $accessionno);
        }
        if (!$this->session->userdata('id')) {
            $this->db->where('t.no_public_display !=', 1);
        }
        $query = $this->db->get();
        if ($query->num_rows())
            return $query->row_array();
        else
            return FALSE;
    }
    
    public function remapSpeciesID($speciesId) {
        $this->db->select('guid');
        $this->db->from('rbgcensus.taxon');
        $this->db->where('species_id', $speciesId);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return $row->guid;
        }
        else {
            return FALSE;
        }
    }
    
    public function getAcceptedNames($where, $inclDeaccessioned=FALSE) {
        $this->searchBaseQuery($where, $inclDeaccessioned, true);
        $this->db->select('t.taxon_name, at.taxon_name AS accepted_name');
        
        $this->db->group_by('t.taxon_id, at.taxon_id');
        
        $query = $this->db->get();
        return $query->result_array();
    }
    
    public function getBedInfo($guid) {
        $this->db->select('node_number');
        $this->db->from('rbgcensus.bed');
        $this->db->where('guid', $guid);
        if (!$this->session->userdata('id')) {
            $this->db->where('is_restricted !=', -1);
        }
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            $this->db->select('guid, bed_type, bed_code, bed_name');
            $this->db->from('rbgcensus.bed');
            $this->db->where('node_number <=', $row->node_number);
            $this->db->where('highest_descendant_node_number >=', $row->node_number);
            $this->db->order_by('node_number');
            $query = $this->db->get();
            if ($query->num_rows()) {
                $ret = array();
                foreach ($query->result() as $row) {
                    $ret[$row->bed_type] = array(
                        'guid' => $row->guid,
                        'name' => $row->bed_name,
                        'code' => $row->bed_code
                    );
                }
                return $ret;
            }
        }
        else
            return FALSE;
    }
    
    public function getBedsSubprecinct($guid) {
        $this->db->select('c.bed_type, c.guid, c.bed_name');
        $this->db->from('rbgcensus.bed b');
        $this->db->join('rbgcensus.bed c', 'b.bed_id=c.parent_id');
        $this->db->where('b.guid', $guid);
        $query = $this->db->get();
        return $query->result_array();
    }
    
    public function getGridInfo($guid) {
        $this->db->select('code');
        $this->db->from('rbgcensus.grid');
        $this->db->where('guid', $guid);
        $query = $this->db->get();
        if ($query->num_rows())
            return $query->row_array();
        else
            return FALSE;
    }
    
    public function getAccessionInfo($guid) {
        $this->db->select('t.guid AS taxon_guid, t.taxon_name, c.family, t.common_name, 
            a.guid AS accession_guid, a.accession_number, a.provenance_type_code, a.provenance_history,
            a.collector_name, a.identification_status');
        $this->db->select("CASE t.plant_type WHEN '#' THEN 1 WHEN '&' THEN 1 ELSE 0 END AS \"isAutralianNative\", 
            CASE t.plant_type WHEN '#' THEN 1 WHEN '!' THEN 1 ELSE 0 END as \"isEndangered\"", false);
        $this->db->from('rbgcensus.taxon t');
        $this->db->join('rbgcensus.classification c', 't.genus_id=c.genus_id', 'left');
        $this->db->join('rbgcensus.accession a', 't.taxon_id=a.taxon_id');
        $this->db->where('a.guid', $guid);
        if (!$this->session->userdata('id')) {
            $this->db->where('t.no_public_display !=', 1);
        }
        $query = $this->db->get();
        if ($query->num_rows())
            return $query->row_array();
        else
            return FALSE;
    }
    
    public function getPlantInfoByAccession($guid, $inclDeaccessioned=FALSE) {
        $this->db->select('p.guid as plant_guid, p.plant_number, p.date_planted, b.guid as bed_guid, b.bed_name,
            b.bed_type, b.location, pb.guid as parent_bed_guid, pb.bed_name as parent_bed_name,
            g.guid as grid_guid, g.code as grid_code');
        $this->db->from('rbgcensus.accession a');
        $this->db->join('rbgcensus.plant p', 'a.accession_id=p.accession_id');
        $this->db->join('rbgcensus.bed b', 'p.bed_id=b.bed_id');
        $this->db->join('rbgcensus.bed pb', 'b.parent_id=pb.bed_id', 'left');
        $this->db->join('rbgcensus.grid g', 'p.grid_id=g.grid_id', 'left');
        $this->db->where('a.guid', $guid);
        $this->db->join('rbgcensus.deaccession d', 'p.plant_id=d.plant_id', 'left');
        if (!$inclDeaccessioned) {
            $this->db->where('d.deaccession_id IS NULL', FALSE, FALSE);
        }
        else {
            $this->db->select('CASE WHEN d.deaccession_id IS NOT NULL THEN 1 ELSE 0 END AS deaccessioned', FALSE);
        }
        $this->db->order_by('p.plant_number');
        $query = $this->db->get();
        return $query->result_array();
    }
    
    public function getPlantInfo($guid) {
        $this->db->select('t.guid AS taxon_guid, t.taxon_name, c.family, t.common_name,
            a.guid AS accession_guid, a.accession_number, a.provenance_type_code, a.provenance_history,
            a.collector_name, a.identification_status, p.guid as plant_guid, p.plant_number, 
            p.date_planted, b.guid as bed_guid, b.location, b.precinct_name, b.bed_name, g.guid as grid_guid, 
            g.code as grid_code');
        $this->db->select("CASE t.plant_type WHEN '#' THEN 1 WHEN '&' THEN 1 ELSE 0 END AS \"isAutralianNative\", 
            CASE t.plant_type WHEN '#' THEN 1 WHEN '!' THEN 1 ELSE 0 END as \"isEndangered\"", false);
        $this->db->from('rbgcensus.taxon t');
        $this->db->join('rbgcensus.classification c', 't.genus_id=c.genus_id', 'left');
        $this->db->join('rbgcensus.accession a', 't.taxon_id=a.taxon_id');
        $this->db->join('rbgcensus.plant p', 'a.accession_id=p.accession_id');
        $this->db->join('rbgcensus.bed b', 'p.bed_id=b.bed_id');
        $this->db->join('rbgcensus.grid g', 'p.grid_id=g.grid_id', 'left');
        $this->db->where('p.guid', $guid);
        if (!$this->session->userdata('id')) {
            $this->db->where('t.no_public_display !=', 1);
            $this->db->where('b.is_restricted !=', -1);
        }
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
    
    public function findByAccessionPlantNumber($accessionNo, $plantNo=FALSE) {
        $this->db->select('a.accession_number, p.plant_number, t.taxon_name, b.bed_name, g.code AS grid');
        $this->searchBaseQuery(FALSE);
        $this->db->like('a.accession_number', $accessionNo);
        if ($plantNo) {
            $this->db->where('p.plant_number', $plantNo);
        }
        $query = $this->db->get();
        if ($query->num_rows()) {
            if ($query->num_rows() == 1) {
                return $query->row();
            }
            else {
                $ret = array();
                $beds = array();
                $grids = array();
                foreach ($query->result() as $index => $row) {
                    if ($index == 0) {
                        $ret['accession_number'] = $row->accession_number;
                        $ret['plant_number'] = NULL;
                        $ret['taxon_name'] = $row->taxon_name;
                    }
                    $beds[] = $row->bed_name;
                    $grids[] = $row->grid;
                }
                $beds = array_unique($beds);
                $grids = array_unique($grids);
                $ret['bed_name'] = (count($beds) == 1) ? $beds[0] : NULL;
                $ret['grid'] = (count($grids) == 1) ? $grids[0] : NULL;
                return (object) $ret;
            }
        }
        else {
            return FALSE;
        }
    }

    
}


/* End of file censusmodel.php */
/* Location: ./models/censusmodel.php */