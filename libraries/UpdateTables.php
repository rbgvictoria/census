<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

require_once 'third_party/uuid/uuid.php';

class UpdateTables {
    private $ci;
    private $mydb;
    private $pgdb;
    
    private $nodeNumber;
    private $stmtChild;
    private $stmtLeft;
    private $stmtRight;

    public function __construct() {
        $this->ci =& get_instance();

        $this->mydb = $this->ci->load->database('living_mysql', TRUE);
        $this->pgdb = $this->ci->load->database('default', TRUE);
    }
    
    public function updateTaxon() {
        $selStmt = "SELECT taxon_id, taxon_name, scientific_name_authorship, genus, genus_id, common_name, plant_type, no_public_display 
            FROM rbgcensus.taxon
            WHERE species_id=?";
        
        $insStmt = "INSERT INTO rbgcensus.taxon (timestamp_created, timestamp_modified, guid, taxon_name, scientific_name_authorship,
              genus, common_name, plant_type, no_public_display, species_id)
            VALUES (NOW(), NOW(), ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $updStmt = "UPDATE rbgcensus.taxon
            SET timestamp_modified=NOW(), taxon_name=?, scientific_name_authorship=?, genus=?, common_name=?,
              plant_type=?, no_public_display=?
            WHERE taxon_id=?";
        
        $select = "SELECT s.Species AS taxon_name, REPLACE(s.Authorship, ')', ') ') AS author, s.Genus AS genus,
                REPLACE(s.InfraAuthor1, ')', ') ') AS InfraAuthor1, REPLACE(s.InfraAuthor2, ')', ') ') AS InfraAuthor2,
                s.InfraEpithet1, s.InfraEpithet2,
                coalesce(spl.CommonName, splc.CommonName) AS common_name,
                coalesce(spl.PlantType, splc.PlantType) AS plant_type,
                coalesce(spl.IsRestricted, splc.IsRestricted) AS is_restricted,
                s.SpeciesID AS species_id
            FROM mysql_species s
            LEFT JOIN (
                SELECT SpeciesID, GROUP_CONCAT(DISTINCT PlantType) AS PlantType, GROUP_CONCAT(DISTINCT CommonName) AS CommonName,
                  CAST(FLOOR(SUM(ABS(blnNoPublicDisplay))/count(SpeciesID)) AS unsigned) AS IsRestricted
                FROM mysql_plantlist
                GROUP BY SpeciesID) AS spl ON s.SpeciesID=spl.SpeciesID
            LEFT JOIN (
                SELECT SpeciesID, GROUP_CONCAT(DISTINCT PlantType) AS PlantType, GROUP_CONCAT(DISTINCT CommonName) AS CommonName,
                  CAST(FLOOR(SUM(ABS(blnNoPublicDisplay))/count(SpeciesID)) AS unsigned) AS IsRestricted
                FROM mysql_plantlist_rbgc
                GROUP BY SpeciesID) AS splc ON s.SpeciesID=splc.SpeciesID
            WHERE (spl.SpeciesID IS NOT NULL OR splc.SpeciesID IS NOT NULL)";
        $query = $this->mydb->query($select);
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $row->author = str_replace(array(')', '.&', '.ex ', ','), array(') ', '. &', '. ex ', ', '), $row->author);
                $row->author = str_replace('  ', ' ', $row->author);
                $row->InfraAuthor1 = str_replace(array(')', '.&', '.ex ', ','), array(') ', '. &', '. ex ', ', '), $row->InfraAuthor1);
                $row->InfraAuthor1 = str_replace('  ', ' ', $row->InfraAuthor1);
                $row->InfraAuthor2 = str_replace(array(')', '.&', '.ex ', ','), array(') ', '. &', '. ex ', ', '), $row->InfraAuthor2);
                $row->InfraAuthor2 = str_replace('  ', ' ', $row->InfraAuthor2);
                
                $sciname = $row->taxon_name;
                if ($row->InfraEpithet2) {
                    $namebits = explode(' ', $row->taxon_name);
                    $ranks = array();
                    foreach (array('subsp.', 'var.', 'f.') as $rank) {
                        $key = array_search($rank, $namebits);
                        if ($key !== FALSE) {
                            $ranks[] = $key;
                        }
                    }
                    if (count($ranks) > 1) {
                        $sciname = array();
                        for ($i = 0; $i < $ranks[0]; $i++) {
                            $sciname[] = $namebits[$i];
                        }
                        for ($i = $ranks[1]; $i < count($namebits); $i++) {
                            $sciname[] = $namebits[$i];
                        }
                        $sciname = implode(' ', $sciname);
                    }
                }
                
                $author = NULL;
                if (!(strpos($sciname, "'") !== FALSE || strpos($sciname, "(") !== FALSE || 
                        strpos($sciname, ' sp.') !== FALSE || strpos($sciname, ' sp ') !== FALSE ||
                        strpos($sciname, '?') !== FALSE)) {
                    if ($row->InfraEpithet2) {
                        $author = $row->InfraAuthor2;
                    }
                    elseif ($row->InfraEpithet1) {
                        $author = $row->InfraAuthor1;
                    }
                    else {
                        $author = $row->author;
                    }
                }
                
                $genusID = NULL;
                
                $query = $this->pgdb->query($selStmt, array($row->species_id));
                if ($query->num_rows()) {
                    $r = $query->row();
                    if ($sciname != $r->taxon_name || 
                        (($author || $r->scientific_name_authorship) && $author != $r->scientific_name_authorship) ||    
                        (($row->genus || $r->genus) && $row->genus != $r->genus) ||    
                        (($row->common_name || $r->common_name) && $row->common_name != $r->common_name) ||    
                        (($row->plant_type || $r->plant_type) && $row->plant_type != $r->plant_type) ||    
                        (($row->is_restricted || $r->no_public_display) && $row->is_restricted != $r->no_public_display)) {
                            $updArray = array(
                                $row->taxon_name,
                                $row->author,
                                $row->genus,
                                $row->common_name,
                                $row->plant_type,
                                $row->is_restricted,
                                $r->taxon_id
                            );
                            if (!$this->pgdb->query($updStmt, $updArray)) {
                                print_r($this->pgdb->error());
                            }
                    }
                }
                else {
                    $insArray = array(
                        UUID::v4(),
                        $row->taxon_name,
                        $row->author,
                        $row->genus,
                        $row->common_name,
                        $row->plant_type,
                        $row->is_restricted,
                        $row->species_id
                    );
                    if (!$this->pgdb->query($updStmt, $updArray)) {
                        print_r($this->pgdb->error());
                    }
                }
            }
        }
    }
    
    public function updateClassification() {
        $updStmt = "UPDATE rbgcensus.taxon
            SET genus_id=?
            WHERE genus=?";
        
        $genera = array();
        $sql = "SELECT DISTINCT t.genus, c.genus_id 
                FROM rbgcensus.taxon t
                JOIN rbgcensus.accession a ON t.taxon_id=a.taxon_id
                LEFT JOIN rbgcensus.classification c ON t.genus=c.genus
                WHERE t.accepted_name_usage_id IS NULL AND t.genus_id IS NULL
                ORDER BY genus";
        $query = $this->pgdb->query($sql);
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                if ($row->genus_id) {
                    if (!$this->pgdb->query($updStmt, array($row->genus_id, $row->genus))) {
                        print_r($this->pgdb->error());
                    }
                }
                else {
                    $genera[] = $row->genus;
                }
            }
        }
        
        $smax = "SELECT MAX(genus_id) AS max_id FROM rbgcensus.classification";
        $qmax = $this->pgdb->query($smax);
        $rmax = $qmax->row();
        $classificationID = $rmax->max_id + 1;
        
        $insStmt = "INSERT INTO rbgcensus.classification (genus_id, genus, family, \"order\", superorder, subclass, class, phylum, kingdom, in_melisr, in_plantlist)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";


        $melisrStmt = "SELECT Name, melisr.highertaxon(TaxonID, 'family') AS family, melisr.highertaxon(TaxonID, 'order') AS `order`,
                  melisr.highertaxon(TaxonID, 'superorder') as superorder, melisr.highertaxon(TaxonID, 'subclass') AS subclass
                FROM melisr.taxon
                WHERE TaxonTreeDefItemID=? AND Name=?";

        $plantlistStmt = "SELECT Family FROM apc.plantlist WHERE Genus=? LIMIT 1";

        foreach ($genera as $genus) {
            $taxon = new taxon();
            $taxon->genus_id = $classificationID;
            $taxon->genus = $genus;
            $q = $this->mydb->query($melisrStmt, array(12, $genus));
            if ($q->num_rows()) {
                $row = $q->row();
                $taxon->family = $row->family;
                $taxon->order = $row->order;
                $taxon->superorder = $row->superorder;
                $taxon->subclass = $row->subclass;
                $taxon->class = 'Equisetopsida';
                $taxon->phylum = 'Streptophyta';
                $taxon->kingdom = 'Plantae';
                $taxon->in_melisr = 't';

                if (!$this->pgdb->query($insStmt, array_values((array) $taxon))) {
                    $this->pgdb->error();
                }
                if (!$this->pgdb->query($updStmt, array($classificationID, $genus))) {
                    $this->pgdb->error();
                }
                $classificationID++;

            }
            else {
                $q1 = $this->mydb->query($plantlistStmt, array($genus));
                if ($q1->num_rows()) {
                    $row = $q1->row();
                    $taxon->in_plantlist = 't';
                    $q2 = $this->mydb->query($melisrStmt, array(11, $row->Family));
                    if ($q2->num_rows()) {
                        $row = $q2->row();
                        $taxon->family = $row->family;
                        $taxon->order = $row->order;
                        $taxon->superorder = $row->superorder;
                        $taxon->subclass = $row->subclass;
                        $taxon->class = 'Equisetopsida';
                        $taxon->phylum = 'Streptophyta';
                        $taxon->kingdom = 'Plantae';

                    }
                    if (!$this->pgdb->query($insStmt, array_values((array) $taxon))) {
                        $this->pgdb->error();
                    }
                    if (!$this->pgdb->query($updStmt, array($classificationID, $genus))) {
                        $this->pgdb->error();
                    }
                    $classificationID++;

                }
            }
        }
    }
    
    public function updateBed() {
        $select = "SELECT 'Melbourne' AS location, 'location' AS bed_type, 'Melbourne' AS bed_name, NULL AS bed_code, NULL AS parent_name, NULL AS parent_code, 0 AS restricted
            UNION
            SELECT 'Melbourne', 'section', section, NULL, 'Melbourne', NULL, max(restricted)
            FROM mysql_location l
            GROUP BY l.section
            UNION
            SELECT 'Melbourne', 'bed', BedName, BedCode, section, NULL, restricted
            FROM mysql_location
            WHERE BedCode IS NOT NULL
            GROUP BY BedName
            UNION
            SELECT 'Cranbourne', 'location', 'Cranbourne', NULL, NULL, NULL, 0
            UNION
            SELECT 'Cranbourne', 'precinct', PrecinctName, PrecinctCode, 'Cranbourne', NULL, max(restricted)
            FROM mysql_location_rbgc
            GROUP BY PrecinctCode
            UNION
            SELECT 'Cranbourne', 'subprecinct', SubPrecinctName, SubPrecinctCode, PrecinctName, PrecinctCode, max(restricted)
            FROM mysql_location_rbgc
            GROUP BY SubPrecinctName
            UNION
            SELECT 'Cranbourne', 'bed', PositionName, AdressographCode, SubPrecinctName, SubPrecinctCode, restricted
            FROM mysql_location_rbgc
            WHERE AdressographCode IS NOT NULL";
        $query = $this->mydb->query($select);
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                switch (true) {
                    case $row->bed_type == 'location':
                        $this->updateLocation($row);
                        break;
                    
                    case $row->location == 'Melbourne' && $row->bed_type == 'section':
                        $this->updateSectionM($row);
                        break;

                    case $row->location == 'Melbourne' && $row->bed_type == 'bed':
                        $this->updateBedM($row);
                        break;
                    
                    case $row->location == 'Cranbourne' && $row->bed_type == 'precinct':
                        $this->updatePrecinctC($row);
                        break;

                    case $row->location == 'Cranbourne' && $row->bed_type == 'subprecinct':
                        $this->updateSubprecinctC($row);
                        break;

                    case $row->location == 'Cranbourne' && $row->bed_type == 'bed':
                        $this->updateBedC($row);
                        break;

                    default:
                        break;
                }
            }
            $this->nestedSets();
        }
    }
    
    public function updateGrid() {
        $selStmt = "SELECT grid_id
            FROM rbgcensus.grid
            WHERE code=?";
        
        $selStmt2 = "SELECT ST_Transform(geom, 900913) AS geom 
            FROM rbgcensus.grid_polygon WHERE gridname=?";
        
        $insStmt = "INSERT INTO rbgcensus.grid (timestamp_created, timestamp_modified, guid, code, geom)
            VALUES (NOW(), NOW(), ?, ?, ?)";
        
        $select = "SELECT DISTINCT Grid FROM mysql_plantlist ORDER BY Grid";
        $query = $this->mydb->query($select);
        foreach ($query->result() as $row) {
            $grid = str_replace(' ', '', $row->Grid);
            $q = $this->pgdb->query($selStmt, array($grid));
            if (!$q->num_rows()) {
                $q2 = $this->pgdb->query($selStmt2, array($grid));
                if ($q2->num_rows()) {
                    $r2 = $q2->row();
                    $insArray = array(
                        UUID::v4(),
                        $grid,
                        $r2->geom
                    );
                    if (!$this->pgdb->query($insStmt, $insArray)) {
                        print_r($this->pgdb->error());
                    }
                }
            }
        }
    }
    
    public function updateAccession() {
        $selStmt = "SELECT a.accession_id, a.accession_number, a.provenance_type_code, a.provenance_history, a.collector_name, a.identification_status, 
              t.species_id, t.taxon_name
            FROM rbgcensus.accession AS a
            JOIN rbgcensus.taxon AS t ON a.taxon_id=t.taxon_id
            WHERE a.accession_number=?";
        
        $taxStmt = "SELECT taxon_id
            FROM rbgcensus.taxon
            WHERE species_id=?";
        
        $insStmt = "INSERT INTO rbgcensus.accession (timestamp_created, timestamp_modified, guid, accession_number, provenance_type_code, 
              provenance_history, collector_name, identification_status, taxon_id)
              VALUES (NOW(), NOW(), ?, ?, ?, ?, ?, ?, ?)";
        
        $updStmt = "UPDATE rbgcensus.accession
            SET timestamp_modified=NOW(), 
              accession_number=?, 
              provenance_type_code=?, 
              provenance_history=?, 
              collector_name=?, 
              identification_status=?, 
              taxon_id=?
            WHERE accession_id=?";
        
        $select = "SELECT CONCAT('RBGM ', SUBSTRING(AccessionNo, 3)) as accession_number, group_concat(distinct IDStatus) AS id_status, 
              group_concat(distinct ProvenanceTypeCode) AS provenance_type_code,
              group_concat(DISTINCT ProvenanceHistoryCode) AS provenance_history, group_concat(distinct CollectorName) AS collector_name,
              group_concat(distinct species) AS species, cast(group_concat(distinct SpeciesID) as unsigned) AS species_id
            FROM mysql_plantlist
            GROUP BY AccessionNo
            UNION
            SELECT CONCAT('RBGC ', SUBSTRING(AccessionNo, 3)), group_concat(distinct IDStatus), group_concat(distinct ProvenanceTypeCode),
              group_concat(DISTINCT ProvenanceHistoryCode), group_concat(distinct CollectorName),
              group_concat(distinct species), cast(group_concat(distinct SpeciesID) as unsigned)
            FROM mysql_plantlist_rbgc
            GROUP BY AccessionNo";
        $query = $this->mydb->query($select);
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $row->provenance_type_code = (in_array($row->provenance_type_code, array('W', 'Z'))) ? $row->provenance_type_code : NULL;
                
                $q = $this->pgdb->query($selStmt, array($row->accession_number));
                if ($q->num_rows()) {
                    $r = $q->row();
                    if ((($row->id_status || $r->identification_status) && $row->id_status != $r->identification_status) ||
                        (($row->provenance_type_code || $r->provenance_type_code) && $row->provenance_type_code != $r->provenance_type_code) ||
                        (($row->provenance_history || $r->provenance_history) && $row->provenance_history != $r->provenance_history) ||
                        (($row->collector_name || $r->collector_name) && $row->collector_name != $r->collector_name) ||
                        ($row->species_id != $r->species_id)) {
                            $q2 = $this->pgdb->query($taxStmt, array($row->species_id));
                            if ($q2->num_rows()) {
                                $tax = $q2->row();
                                $taxon_id = $tax->taxon_id;
                            }
                            else {
                                $taxon_id = NULL;
                                echo $row->accession_number . "\t" . $row->species_id . "\n";
                            }
                            $updArray = array(
                                $row->accession_number,
                                $row->provenance_type_code, 
                                $row->provenance_history, 
                                $row->collector_name, 
                                $row->id_status, 
                                $taxon_id,
                                $r->accession_id                            
                            );
                            if (!$this->pgdb->query($updStmt, $updArray)) {
                                print_r($this->pgdb->error());
                            }
                    }
                }
                else {
                    $q3 = $this->pgdb->query($taxStmt, array($row->species_id));
                    if ($q3->num_rows()) {
                        $tax = $q3->row();
                        $taxon_id = $tax->taxon_id;
                    }
                    else {
                        $taxon_id = NULL;
                        echo $row->accession_number . "\t" . $row->species_id . "\n";
                    }
                    
                    $insArray = array(
                        UUID::v4(), 
                        $row->accession_number, 
                        $row->provenance_type_code, 
                        $row->provenance_history, 
                        $row->collector_name, 
                        $row->id_status, 
                        $taxon_id
                    );
                    if (!$this->pgdb->query($insStmt, $insArray)) {
                        print_r($this->pgdb->error());
                    }
                }
            }
        }
    }
    
    public function updatePlant() {
        $selStmt = "SELECT b.location, p.plant_id, a.accession_id, a.accession_number, p.plant_number, b.bed_code, 
              b.bed_name, g.code AS grid_code, p.date_planted 
            FROM rbgcensus.plant p
            JOIN rbgcensus.accession a ON p.accession_id=a.accession_id
            LEFT JOIN rbgcensus.bed b ON p.bed_id=b.bed_id
            LEFT JOIN rbgcensus.grid g ON p.grid_id=g.grid_id
            WHERE a.accession_number=? AND p.plant_number=?";
        
        $accStmt = "SELECT accession_id
            FROM rbgcensus.accession
            WHERE accession_number=?";
        
        $bedStmt = "SELECT bed_id
            FROM rbgcensus.bed
            WHERE location=? AND bed_code=? AND bed_type=?";
        
        $gridStmt = "SELECT grid_id
            FROM rbgcensus.grid
            WHERE code=?";
        
        $insStmt = "INSERT INTO rbgcensus.plant (timestamp_created, timestamp_modified, guid, plant_number, date_planted, accession_id, grid_id, bed_id)
            VALUES (NOW(), NOW(), ?, ?, ?, ?, ?, ?)";
        
        $updStmt = "UPDATE rbgcensus.plant
            SET timestamp_modified=NOW(),
              plant_number=?,
              date_planted=?,
              accession_id=?,
              grid_id=?,
              bed_id=?
            WHERE plant_id=?";
        
        $select = "SELECT 'Melbourne' AS location, concat('RBGM ', substr(p.AccessionNo, 3)) AS accession_number, p.PlantMemberNo AS plant_number, p.BedCode AS bed_code,
              b.BedName AS bed_name, p.Grid AS grid_code, p.DatePlanted AS date_planted, 'bed' AS bed_type
            FROM mysql_plantlist p
            JOIN mysql_location b ON p.BedCode=b.BedCode
            UNION
            SELECT 'Cranbourne' AS location,
            concat('RBGC ', substr(p.AccessionNo, 3)), p.PlantMemberNo AS plant_number,
            IF(p.PositionName IS NOT NULL AND lower(p.PositionName) NOT IN ('zz', 'zzz'), p.AdressographCode, p.SubprecinctCode),
            IF(p.PositionName IS NOT NULL AND lower(p.PositionName) NOT IN ('zz', 'zzz'), p.PositionName, p.SubprecinctName),
            NULL, p.DatePlanted,
            IF(p.PositionName IS NOT NULL AND lower(p.PositionName) NOT IN ('zz', 'zzz'), 'bed', 'subprecinct')
            FROM mysql_plantlist_rbgc p
            JOIN mysql_location_rbgc b ON p.AdressographCode=b.AdressographCode";
        $query = $this->mydb->query($select);
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $q = $this->pgdb->query($selStmt, array($row->accession_number, $row->plant_number));
                if ($q->num_rows()) {
                    $r = $q->row();
                    if (($row->bed_name != $r->bed_name) ||
                        (($row->grid_code || $r->grid_code) && $row->grid_code != $r->grid_code) ||
                        (($row->date_planted || $r->date_planted) && $row->date_planted != $r->date_planted)) {
                        $bed = $this->pgdb->query($bedStmt, array($row->location, $row->bed_code, $row->bed_type));
                        if ($bed->num_rows()) {
                            $b = $bed->row();
                            $bed_id = $b->bed_id;
                        }

                        $grid_id = NULL;
                        if ($row->location == 'Melbourne') {
                            $grid = $this->pgdb->query($gridStmt, array($row->grid_code));
                            if ($grid->num_rows()) {
                                $g = $grid->row();
                                $grid_id = $g->grid_id;
                            }
                        }

                        $updArray = array(
                            $row->plant_number,
                            $row->date_planted,
                            $r->accession_id,
                            $grid_id,
                            $bed_id,
                            $r->plant_id
                        );
                        if (!$this->pgdb->query($updStmt, $updArray)) {
                            print_r($this->pgdb->error());
                        }
                     }   
                }
                else {
                    $q2 = $this->pgdb->query($accStmt, array($row->accession_number));
                    $acc = $q2->row();
                    $accession_id = $acc->accession_id;
                    
                    $bed = $this->pgdb->query($bedStmt, array($row->location, $row->bed_code, $row->bed_type));
                    if ($bed->num_rows()) {
                        $b = $bed->row();
                        $bed_id = $b->bed_id;
                    }
                    
                    $grid_id = NULL;
                    if ($row->location == 'Melbourne') {
                        $grid = $this->pgdb->query($gridStmt, array($row->grid_code));
                        if ($grid->num_rows()) {
                            $g = $grid->row();
                            $grid_id = $g->grid_id;
                        }
                    }
                    // guid, plant_number, date_planted, accession_id, grid_id, bed_id
                    $insArray = array(
                        UUID::v4(), $row->plant_number, $row->date_planted, $accession_id, $grid_id, $bed_id
                    );
                    if (!$this->pgdb->query($insStmt, $insArray)) {
                        print_r($this->pgdb->error());
                        print_r($row);
                    }
                }
            }
        }
    }
    
    public function updateDeaccessioned() {
        $rbgmStmt = "SELECT count(*) AS num
            FROM mysql_plantlist
            WHERE substring(AccessionNo, 3)=? AND PlantMemberNo=?";
        
        $rbgcStmt = "SELECT count(*) AS num
            FROM mysql_plantlist_rbgc
            WHERE substring(AccessionNo, 3)=? AND PlantMemberNo=?";
        
        $insStmt = "INSERT INTO rbgcensus.deaccession (timestamp_created, timestamp_modified, plant_id)
            VALUES (NOW(), NOW(), ?)";
                
        $select = "SELECT p.plant_id, a.accession_number, p.plant_number
            FROM rbgcensus.plant p
            JOIN rbgcensus.accession a ON p.accession_id=a.accession_id
            LEFT JOIN rbgcensus.deaccession d ON p.plant_id=d.plant_id
            WHERE d.deaccession_id IS NULL";
        $query = $this->pgdb->query($select);
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                list($garden, $accession_number) = explode(' ', $row->accession_number);
                if ($garden == 'RBGM') {
                    $q1 = $this->mydb->query($rbgmStmt, array($accession_number, $row->plant_number));
                    $res = $q1->row();
                }
                else {
                    $q1 = $this->mydb->query($rbgcStmt, array($accession_number, $row->plant_number));
                    $res = $q1->row();
                }
                if (!$res->num) {
                    if (!$this->pgdb->query($insStmt, array($row->plant_id))) {
                        print_r($this->pgdb->error());
                    }
                }
            }
        }
    }
    
    public function deleteDuplicates() {
        $delete = "DELETE FROM rbgcensus.plant
            WHERE accession_id=? AND plant_number=? AND timestamp_created<?";
        $delStmt = $this->pgdb->prepare($delete);
        
        $select = "SELECT accession_id, plant_number, max(timestamp_created) AS latest
            FROM rbgcensus.plant
            GROUP BY accession_id, plant_number
            HAVING count(*)>1";
        $query = $this->pgdb->query($select);
        $result = $query->fetchAll(5);
        if ($result) {
            foreach ($result as $row) {
                $delStmt->execute(array($row->accession_id, $row->plant_number, $row->latest));
                if ($delStmt->errorCode() != '00000')
                    print_r($delStmt->errorInfo());
            }
        }
    }
    
    public function deduplicateGrids() {
        $update = "UPDATE rbgcensus.plant
            SET grid_id=?
            WHERE grid_id=?";
        $updStmt = $this->pgdb->prepare($update);
        
        $delete = "DELETE FROM rbgcensus.grid
            WHERE grid_id=?";
        $delStmt = $this->pgdb->prepare($delete);
        
        $select = "SELECT code, array_agg(grid_id)
            FROM rbgcensus.grid
            GROUP BY code
            HAVING count(*)>1";
        $query = $this->pgdb->query($select);
        $result = $query->fetchAll(5);
        foreach ($result as $row) {
            $ids = explode(',', substr($row->array_agg, 1, strlen($row->array_agg)-2));
            foreach ($ids as $index => $gridid) {
                if ($index > 0) {
                    $updStmt->execute(array($ids[0], $gridid));
                    if ($updStmt->errorCode() != '00000')
                        print_r($updStmt->errorInfo());
                    
                    $delStmt->execute(array($gridid));
                    if ($delStmt->errorCode() != '00000')
                        print_r($delStmt->errorInfo());
                }
            }
        }
    }
    
    public function attributes() {
        $insStmt = "INSERT INTO rbgcensus.plantattr (timestamp_created, timestamp_modified, plant_id, attribute_id, value)
            VALUES (NOW(), NOW(), ?, ?, ?)";
        
        $updStmt = "UPDATE rbgcensus.plantattr
            SET timestamp_modified=NOW(), value=?
            WHERE plant_attr_id=?";
        
        $selStmt2 = "SELECT plant_attr_id, value
            FROM rbgcensus.plantattr
            WHERE plant_id=? AND attribute_id=?";
        
        $selStmt1 = "SELECT p.plant_id
            FROM rbgcensus.accession a
            JOIN rbgcensus.plant p ON a.accession_id=p.accession_id
            WHERE a.accession_number=? AND p.plant_number=?";
        
        $select = "SELECT AccessionNo, PlantMemberNo, Commemorative, InMemoryOf, DatePlanted, NationalTrustStatus, National_Trust_Significance AS NationalTrustSignificance
            FROM mysql_plantlist
            WHERE Commemorative IS NOT NULL OR InMemoryOf IS NOT NULL OR NationalTrustStatus IS NOT NULL OR National_Trust_Significance IS NOT NULL";
        $query = $this->mydb->query($select);
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $accno = 'RBGM ' . substr($row->AccessionNo, 2);
                $q = $this->pgdb->query($selStmt1, array($accno, $row->PlantMemberNo));
                $r = $q->row();
                $plantid = $r->plant_id;
                
                $attributes = array(
                    1 => 'Commemorative',
                    2 => 'InMemoryOf',
                    3 => 'DatePlanted',
                    4 => 'NationalTrustStatus',
                    5 => 'NationalTrustSignificance'
                );
                
                foreach($attributes as $attrid => $value) {
                    if ($row->$value) {
                        $q = $this->pgdb->query($selStmt2, array($plantid, $attrid));
                        if ($q->num_rows()) {
                            $r = $q->row();
                            if ($row->$value != $r->value) {
                                if (!$this->pgdb->query($updStmt, array($row->$value, $r->plant_attr_id))) {
                                   print_r($this->pgdb->error());
                                }
                            }
                        }
                        else {
                            if (!$this->pgdb->query($insStmt, array($plantid, $attrid, $row->$value))) {
                                print_r($this->pgdb->error());
                            }
                        }
                    }
                }
            }
        }
    }

    public function collections() {
        $insStmt = "INSERT INTO rbgcensus.collection_plant (timestamp_created, timestamp_modified, plant_id, collection_id)
            VALUES (NOW(), NOW(), ?, ?)";
        
        $selStmt2 = "SELECT collection_plant_id
            FROM rbgcensus.collection_plant
            WHERE plant_id=? AND collection_id=?";
        
        $selStmt1 = "SELECT p.plant_id
            FROM rbgcensus.accession a
            JOIN rbgcensus.plant p ON a.accession_id=p.accession_id
            WHERE a.accession_number=? AND p.plant_number=?";
        
        $select = "SELECT CONCAT('RBGM ', SUBSTRING(AccessionNo, 3)) AS AccessionNumber, PlantMemberNo, IF(NationalTrustStatus IS NOT NULL, 1, 0) AS NationalTrust,
              IF(Commemorative IS NOT NULL OR InMemoryOf IS NOT NULL, 1, 0) AS Commemorative
            FROM mysql_plantlist
            WHERE NationalTrustStatus IS NOT NULL OR Commemorative IS NOT NULL OR InMemoryOf IS NOT NULL";
        $query = $this->mydb->query($select);
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $q = $this->pgdb->query($selStmt1, array($row->AccessionNumber, $row->PlantMemberNo));
                $r = $q->row();
                $plantid = $r->plant_id;
                
                $collections = array(
                    1 => 'Commemorative',
                    2 => 'NationalTrust'
                );
                
                foreach($collections as $collid => $value) {
                    if ($row->$value) {
                        $q = $this->pgdb->query($selStmt2, array($plantid, $collid));
                        if (!$q->num_rows()) {
                            if (!$this->pgdb->query($insStmt, array($plantid, $collid))) {
                                print_r($this->pgdb->error());
                            }
                        }
                    }
                }
            }
        }
    }

    private function updateLocation($data) {
        $select = "SELECT bed_id
            FROM rbgcensus.bed
            WHERE bed_type='location' AND bed_name='$data->bed_name'";
        $query = $this->pgdb->query($select);
        if (!$query->num_rows()) {
            $uuid = UUID::v4();
            $insert = "INSERT INTO rbgcensus.bed (timestamp_created, timestamp_modified, guid, bed_name, bed_code, bed_type)
                VALUES (NOW(), NOW(), '$uuid', '$data->bed_name', NULL, 'location')";
            if (!$this->pgdb->query($insert)) {
                print_r($this->pgdb->error());
            }
        }
    }
    
    private function updateSectionM($data) {
        $select = "SELECT bed_id
            FROM rbgcensus.bed
            WHERE bed_type='location' AND bed_name='Melbourne'";
        $query = $this->pgdb->query($select);
        $row = $query->row();
        $parent_id = $row->bed_id;
        
        $insStmt = "INSERT INTO rbgcensus.bed (timestamp_created, timestamp_modified, guid, bed_name, bed_code, bed_type, parent_id, is_restricted, location)
            VALUES (NOW(), NOW(), ?, ?, ?, 'section', ?, ?, 'Melbourne')";
        
        $updStmt = "UPDATE rbgcensus.bed
            SET is_restricted=?
            WHERE bed_id=?";
            
        $selStmt = "SELECT bed_id, is_restricted
            FROM rbgcensus.bed
            WHERE bed_type='section' AND location='Melbourne' AND bed_name=?";
        $query = $this->pgdb->query($selStmt, array($data->bed_name));
        if ($query->num_rows()) {
            $row = $query->row();
            if ($row->is_restricted === NULL || $row->is_restricted != $data->restricted) {
                $updStmt->execute(array($data->restricted, $row->bed_id));
                if ($updStmt->errorCode() != '00000') {
                    print_r($updStmt->errorInfo());
                }
            }
        }
        else {
            $insArray = array(
                UUID::v4(),
                $data->bed_name,
                $data->bed_code,
                $parent_id,
                $data->restricted
            );
            if (!$this->pgdb->query($insStmt, $insArray)) {
                print_r($this->pgdb->error());
            }
        }
    }
    
    private function updateBedM($data) {
        $select = "SELECT bed_id
            FROM rbgcensus.bed
            WHERE bed_type='section' AND location='Melbourne' AND bed_name=?";
        $query = $this->pgdb->query($select, array($data->parent_name));
        $row = $query->row();
        $parent_id = $row->bed_id;
        
        $updStmt = "UPDATE rbgcensus.bed
            SET timestamp_modified=NOW(), bed_name=?, parent_id=?, is_restricted=?
            WHERE bed_id=?";
        
        $insStmt = "INSERT INTO rbgcensus.bed (timestamp_created, timestamp_modified, guid, bed_name, bed_code, bed_type, parent_id, is_restricted, location)
            VALUES (NOW(), NOW(), ?, ?, ?, 'bed', ?, ?, 'Melbourne')";

        $selStmt = "SELECT bed_id, bed_name, is_restricted, parent_id
            FROM rbgcensus.bed
            WHERE bed_type='bed' AND location='Melbourne' AND bed_code=?";
        $query = $this->pgdb->query($selStmt, array($data->bed_code));
        if ($query->num_rows()) {
            $row = $query->row();
            if (!$row->parent_id || $row->parent_id != $parent_id ||
                    $data->bed_name != $row->bed_name ||
                    $data->restricted != $row->is_restricted) {
                $updArray = array(
                    $data->bed_name,
                    $parent_id,
                    $data->restricted,
                    $row->bed_id
                );
                if (!$this->pgdb->query($updStmt, $updArray)) {
                    print_r($this->pgdb->error());
                }
            }
        }
        else {
            $insArray = array(
                UUID::v4(),
                $data->bed_name,
                $data->bed_code,
                $parent_id,
                $data->restricted
            );
            if (!$this->pgdb->query($insStmt, $insArray)) {
                print_r($insStmt->errorInfo());
            }
        }
    }
    
    private function updatePrecinctC($data) {
        $select = "SELECT bed_id
            FROM rbgcensus.bed
            WHERE bed_type='location' AND bed_name='Cranbourne'";
        $query = $this->pgdb->query($select);
        $row = $query->row();
        $parent_id = $row->bed_id;
        
        $insStmt = "INSERT INTO rbgcensus.bed (timestamp_created, timestamp_modified, guid, bed_name, bed_code, bed_type, parent_id, is_restricted, location)
            VALUES (NOW(), NOW(), ?, ?, ?, 'precinct', ?, ?, 'Cranbourne')";
        
        $updStmt = "UPDATE rbgcensus.bed
            SET is_restricted=?
            WHERE bed_id=?";
            
        $selStmt = "SELECT bed_id, is_restricted
            FROM rbgcensus.bed
            WHERE bed_type='precinct' AND location='Cranbourne' AND bed_name=?";
        $query = $this->pgdb->query($selStmt, array($data->bed_name));
        if ($query->num_rows()) {
            $row = $query->row();
            if ($row->is_restricted === NULL || $row->is_restricted != $data->restricted) {
                if (!$this->pgdb->query($updStmt, array($data->restricted, $row->bed_id))) {
                    print_r($this->pgdb->error());
                }
            }
        }
        else {
            $insArray = array(
                UUID::v4(),
                $data->bed_name,
                $data->bed_code,
                $parent_id,
                $data->restricted
            );
            if (!$this->pgdb->query($insStmt, $insArray)) {
                print_r($this->pgdb->error());
            }
        }
    }
    
    private function updateSubprecinctC($data) {
        $select = "SELECT bed_id
            FROM rbgcensus.bed
            WHERE bed_type='precinct' AND location='Cranbourne' AND bed_code=?";
        $query = $this->pgdb->query($select, array($data->parent_code));
        $row = $query->row();
        $parent_id = $row->bed_id;
        
        $updStmt = "UPDATE rbgcensus.bed
            SET timestamp_modified=NOW(), bed_name=?, parent_id=?, is_restricted=?
            WHERE bed_id=?";
        
        $insStmt = "INSERT INTO rbgcensus.bed (timestamp_created, timestamp_modified, guid, bed_name, bed_code, bed_type, parent_id, is_restricted, location)
            VALUES (NOW(), NOW(), ?, ?, ?, 'subprecinct', ?, ?, 'Cranbourne')";

        $select = "SELECT bed_id, bed_name, is_restricted, parent_id
            FROM rbgcensus.bed
            WHERE bed_type='subprecinct' AND location='Cranbourne' AND bed_code=?";
        $query = $this->pgdb->query($select, array($data->bed_code));
        if ($query->num_rows()) {
            $row = $query->row();
            if (!$row->parent_id || $row->parent_id != $parent_id ||
                    $data->bed_name != $row->bed_name ||
                    $data->restricted != $row->is_restricted) {
                $updArray = array(
                    $data->bed_name,
                    $parent_id,
                    $data->restricted,
                    $row->bed_id
                );
                if (!$this->pgdb->query($updStmt, $updArray)) {
                    print_r($this->pgdb->error());
                }
            }
        }
        else {
            $insArray = array(
                UUID::v4(),
                $data->bed_name,
                $data->bed_code,
                $parent_id,
                $data->restricted
            );
            if (!$this->pgdb->query($insStmt, $insArray)) {
                print_r($this->pgdb->error());
            }
        }
    }
    
    private function updateBedC($data) {
        $select = "SELECT bed_id
            FROM rbgcensus.bed
            WHERE bed_type='subprecinct' AND location='Cranbourne' AND bed_code=?";
        $query = $this->pgdb->query($select, array($data->parent_code));
        $row = $query->row();
        $parent_id = $row->bed_id;
        
        $updStmt = "UPDATE rbgcensus.bed
            SET timestamp_modified=NOW(), bed_name=?, parent_id=?, is_restricted=?
            WHERE bed_id=?";
        
        $insStmt = "INSERT INTO rbgcensus.bed (timestamp_created, timestamp_modified, guid, bed_name, bed_code, bed_type, parent_id, is_restricted, location)
            VALUES (NOW(), NOW(), ?, ?, ?, 'bed', ?, ?, 'Cranbourne')";

        $select = "SELECT bed_id, bed_name, is_restricted, parent_id
            FROM rbgcensus.bed
            WHERE bed_type='bed' AND location='Cranbourne' AND bed_code=?";
        $query = $this->pgdb->query($select, $data->bed_code);
        if ($query->num_rows()) {
            $row = $query->row();
            if (!$row->parent_id || $row->parent_id != $parent_id ||
                    $data->bed_name != $row->bed_name ||
                    $data->restricted != $row->is_restricted) {
                $updArray = array(
                    $data->bed_name,
                    $parent_id,
                    $data->restricted,
                    $row->bed_id
                );
                if (!$this->pgdb->query($updStmt, $updArray)) {
                    print_r($this->pgdb->error());
                }
            }
        }
        else {
            $insArray = array(
                UUID::v4(),
                $data->bed_name,
                $data->bed_code,
                $parent_id,
                $data->restricted
            );
            if (!$this->pgdb->query($insStmt, $insArray)) {
                print_r($this->pgdb->error());
            }
        }
    }
    
    private function nestedSets() {
        $this->nodeNumber = 0;
        
        $this->stmtChild = "SELECT bed_id
            FROM rbgcensus.bed
            WHERE parent_id=?";
        
        $this->stmtLeft = "UPDATE rbgcensus.bed
            SET node_number=?
            WHERE bed_id=?";
        
        $this->stmtRight = "UPDATE rbgcensus.bed
            SET highest_descendant_node_number=?
            WHERE bed_id=?";
        
        $select = "SELECT bed_id
            FROM rbgcensus.bed
            WHERE bed_type='location'";
        $query = $this->pgdb->query($select);
        foreach ($query->result() as $row) {
            $this->getNode($row->bed_id);
        }
    }
    
    private function getNode($parentId) {
        $this->nodeNumber++;
        $this->pgdb->query($this->stmtLeft, array($this->nodeNumber, $parentId));
        $query = $this->pgdb->query($this->stmtChild, array($parentId));
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $this->getNode($row->bed_id);
            }
        }
        $this->pgdb->query($this->stmtRight, array($this->nodeNumber, $parentId));
    }
    
    public function duplicateAccessions() {
        $this->pgdb->select('a.accession_number');
        $this->pgdb->from('rbgcensus.accession a');
        $this->pgdb->group_by('a.accession_number');
        $this->pgdb->having('count(*)>1');
        $query = $this->pgdb->get();
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $this->fixDuplicateAccessions($row->accession_number);
            }
        }
    }
    
    private function fixDuplicateAccessions($accession_number) {
        $this->pgdb->select('a.accession_id, p.plant_id, a.accession_number, p.plant_number, p.grid_id, p.bed_id, a.taxon_id');
        $this->pgdb->from('rbgcensus.accession a');
        $this->pgdb->join('rbgcensus.plant p', 'a.accession_id=p.accession_id', 'left');
        $this->pgdb->where('a.accession_number', $accession_number);
        $query = $this->pgdb->get();
        if ($query->num_rows()) {
            $this->pgdb->trans_start();
            $newAccID = FALSE;
            foreach ($query->result() as $row) {
                if (!$row->plant_id && !$row->taxon_id) {
                    $this->pgdb->where('accession_id', $row->accession_id);
                    $this->pgdb->delete('rbgcensus.accession');
                }
                elseif ($row->taxon_id) {
                    $newAccID = $row->accession_id;
                }
            }
            if ($newAccID) {
                foreach ($query->result() as $row) {
                    if ($row->plant_id) {
                        $this->pgdb->where('plant_id', $row->plant_id);
                        $this->pgdb->update('rbgcensus.plant', array('accession_id' => $newAccID));
                    }
                }
                foreach ($query->result() as $row) {
                    if (!$row->taxon_id) {
                        $this->pgdb->where('accession_id', $row->accession_id);
                        $this->pgdb->delete('rbgcensus.accession');
                    }
                }
            }
            $this->pgdb->trans_complete();
        }
        
    }
    
    function checkDeaccessioned() {
        $query = $this->pgdb->query("SELECT d.deaccession_id, a.accession_number, p.plant_number
            FROM rbgcensus.deaccession d
            JOIN rbgcensus.plant p ON d.plant_id=p.plant_id
            JOIN rbgcensus.accession a ON p.accession_id=a.accession_id");
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                list($garden, $accession_number) = explode(' ', $row->accession_number);
                $this->mydb->select('count(*) as num', FALSE);
                if ($garden = 'RBGM') {
                    $this->mydb->from('mysql_plantlist');
                }
                else {
                    $this->mydb->from('mysql_plantlist_rbgc');
                }
                $this->mydb->where("substring(AccessionNo, 3)='$accession_number'", FALSE, FALSE);
                $this->mydb->where('PlantMemberNo', $row->plant_number);
                $q = $this->mydb->get();
                $r = $q->row();
                if ($r->num) {
                    $this->pgdb->where('deaccession_id', $row->deaccession_id);
                    $this->pgdb->delete('rbgcensus.deaccession');
                }
            }
        }
    }

}

class taxon {
    var $genus_id = NULL;
    var $genus = NULL;
    var $family = NULL;
    var $order = NULL;
    var $superorder = NULL;
    var $subclass = NULL;
    var $class = NULL;
    var $phylum = NULL;
    var $kingdom = NULL;
    var $in_melisr = NULL;
    var $in_plantlist = NULL;
}
