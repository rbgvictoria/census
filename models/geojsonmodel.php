<?php

class geoJSONModel extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getGridCells($terms) {
        $this->db->select('g.code, ST_AsGeoJSON(ST_Transform(g.geom, 3857), 7, 4) AS geometry', FALSE);
        $this->db->from('rbgcensus.taxon t');
        $this->db->join('rbgcensus.accession a', 't.taxon_id=a.taxon_id');
        $this->db->join('rbgcensus.plant p', 'a.accession_id=p.accession_id');
        $this->db->join('rbgcensus.bed b', 'p.bed_id=b.bed_id');
        $this->db->join('rbgcensus.grid g', 'p.grid_id=g.grid_id');
        $this->db->group_by('g.grid_id');
        
        foreach ($terms as $key => $value) {
            if ($key == 'bed_guid') {
                $this->db->where('b.guid', $value);
            }
            
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
            if ($key == 'accession_guid')
                $this->db->where('a.guid', $value);
            if ($key == 'wgs_code') {
                $this->db->join('rbgcensus.taxon_area ta', 't.taxon_id=ta.taxon_id');
                $this->db->join('wgs.wgs_region r', 'ta.area_id=r.region_id');
                if (strlen($value) == 1) {
                    $this->db->where('r.level1_code', $value);
                }
                elseif (strlen($value) == 2) {
                    $this->db->where('r.level2_code', $value);
                }
                elseif (strlen($value) == 3) {
                    $this->db->where('r.level3_code', $value);
                }
                else {
                    $this->db->where('r.level4_code', $value);
                }
            }
            
        }
        
        $query = $this->db->get();
        echo $this->db->last_query();
        return $query->result();
    }
    
    public function getCollectionCentroidsAmg($collection=FALSE) {
        $this->db->select("c.name as collection, a.accession_number, p.plant_number, 
                p.guid AS plant_guid, t.taxon_name, t.guid as taxon_guid,
                array_agg(att.name ORDER BY att.attribute_id) as attr_label, 
                array_agg(pa.value ORDER BY att.attribute_id) as attr_value,
                g.code as grid_code, ST_AsGeoJSON(ST_Transform(ST_Centroid(g.geom), '28355')) AS geometry", FALSE);
        $this->db->from('rbgcensus.plant p');
        $this->db->join('rbgcensus.collection_plant cp', 'p.plant_id=cp.plant_id');
        $this->db->join('rbgcensus.collection c', 'cp.collection_id=c.collection_id');
        $this->db->join('rbgcensus.accession a', 'p.accession_id=a.accession_id');
        $this->db->join('rbgcensus.taxon t', 'a.taxon_id=t.taxon_id');
        $this->db->join('rbgcensus.grid g', 'p.grid_id=g.grid_id');
        $this->db->join('rbgcensus.plantattr pa', 'p.plant_id=pa.plant_id', 'left');
        $this->db->join('rbgcensus.attribute att', 'pa.attribute_id=att.attribute_id', 'left');
        if ($collection)
            $this->db->where('c.collection_id', $collection);
        $this->db->group_by('c.collection_id, p.plant_id, a.accession_id, t.taxon_id, g.grid_id');
        
        $query = $this->db->get();
        if ($query->num_rows()) {
            $result = $query->result_array();
            foreach ($result as $index => $row) {
                $result[$index]['attr_label'] = $this->pg_array_parse($row['attr_label']);
                $result[$index]['attr_value'] = $this->pg_array_parse($row['attr_value']);
            }
            return $result;
        }
    }
    
    public function getSpeciesCentroidsAmg($guid) {
        $this->db->select("p.guid as plant_id, a.accession_number, p.plant_number, 
            t.guid as taxon_id, t.taxon_name, g.code as grid_code, b.bed_name, b.guid as bed_id,
            ST_AsGeoJSON(ST_Transform(ST_Centroid(g.geom), '28355'), 7, 4) AS geometry", FALSE);
        $this->db->from('rbgcensus.plant p');
        $this->db->join('rbgcensus.accession a', 'p.accession_id=a.accession_id');
        $this->db->join('rbgcensus.taxon t', 'a.taxon_id=t.taxon_id');
        $this->db->join('rbgcensus.grid g', 'p.grid_id=g.grid_id');
        $this->db->join('rbgcensus.bed b', 'p.bed_id=b.bed_id');
        $this->db->where('b.location', 'Melbourne');
        $this->db->where('t.guid', $guid);
        $query = $this->db->get();
        return $query->result();
    }
    
    /*
     * From: http://stackoverflow.com/questions/3068683/convert-postgresql-array-to-php-array
     */
    private function pg_array_parse($literal) {
        if ($literal == '') return;
        preg_match_all('/(?<=^\{|,)(([^,"{]*)|\s*"((?:[^"\\\\]|\\\\(?:.|[0-9]+|x[0-9a-f]+))*)"\s*)(,|(?<!^\{)(?=\}$))/i', $literal, $matches, PREG_SET_ORDER);
        $values = array();
        foreach ($matches as $match) {
            $values[] = $match[3] != '' ? stripcslashes($match[3]) : (strtolower($match[2]) == 'null' ? null : $match[2]);
        }
        return $values;
    }
}

/* End of file geojsonmodel.php */
/* Location: ./models/geojsonmodel.php */