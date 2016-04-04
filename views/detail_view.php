<?php require_once('header.php');?>

<div class="container">
<?php if ($page == 'taxon'): ?>
    <?php if ($this->session->flashdata('redirect_url')): ?>
    <div class="redirect">Redirected from: <?=$this->session->flashdata('redirect_url')?></div>
    <?php endif; ?>
    <h1><?=formatName($taxon['taxon_name']); ?></h1>
<?php elseif ($page == 'accession'): ?>
    <h1><?=$accession_info['accession_number']?></h1>
<?php elseif ($page == 'plant'): ?>
    <h1><?=$plant_info['accession_number']?>.<?=$plant_info['plant_number']?></h1>
<?php elseif ($page == 'bed'): ?>
<?php 
    $types = array_keys($bed_info);
    $key = $types[count($types)-1];
?>
    <h1><?=$bed_info[$key]['name']?></h1>
<?php elseif ($page == 'grid'): ?>
    <h1>Grid: <?=$grid_info['code']?></h1>
<?php endif; ?>

<?php if ($page == 'taxon'): ?>
<h3>Taxon info.</h3>
<div class="row taxon-info">
    <div class="term"><div class="col-md-2 field-label">Scientific name</div><div class="col-md-4"><?=formatName($taxon['taxon_name'])?> <?=$taxon['scientific_name_authorship']?></div></div>
    <div class="term"><div class="col-md-2 field-label">Common name</div><div class="col-md-4"><?=$taxon['common_name']?></div></div>
    <div class="term"><div class="col-md-2 field-label">Australian native</div><div class="col-md-4"><?=($taxon['isAutralianNative']) ? '&check;' : '&ndash;';?></div></div>
    <div class="term"><div class="col-md-2 field-label">Endangered</div><div class="col-md-4"><?=($taxon['isEndangered']) ? '&check;' : '&ndash;';?></div></div>
</div>

<h4>Classification</h4>
<div class="row taxon-info">
    <div class="term"><div class="col-md-2 field-label">Kingdom</div><div class="col-md-4"><?=$taxon['kingdom']?></div></div>
    <div class="term"><div class="col-md-2 field-label">Phylum</div><div class="col-md-4"><?=$taxon['phylum']?></div></div>
    <div class="term"><div class="col-md-2 field-label">Class</div><div class="col-md-4"><?=$taxon['class']?></div></div>
    <div class="term"><div class="col-md-2 field-label">Subclass</div><div class="col-md-4"><?=$taxon['subclass']?></div></div>
    <div class="term"><div class="col-md-2 field-label">Superorder</div><div class="col-md-4"><?=$taxon['superorder']?></div></div>
    <div class="term"><div class="col-md-2 field-label">Order</div><div class="col-md-4"><?=$taxon['order']?></div></div>
    <div class="term"><div class="col-md-2 field-label">Family</div><div class="col-md-4"><?=anchor(site_url() . 'census/search?family=' . $taxon['family'], $taxon['family']); ?></div></div>
    <div class="term"><div class="col-md-2 field-label">Genus</div><div class="col-md-4"><?=$taxon['genus']?></div></div>
</div>

<h4>Links</h4>
<div class="taxon-links">
    <?=anchor('https://biodiversity.org.au/nsl/services/search?name=' . urlencode($taxon['taxon_name']) . '&display=apni&search=true', 'APNI', array('class' => 'btn btn-default')); ?>
    <?=anchor('http://www.ipni.org/ipni/simplePlantNameSearch.do?find_wholeName=' . urlencode($taxon['taxon_name']), 'IPNI', array('class' => 'btn btn-default')); ?>
    <?=anchor('http://www.tropicos.org/NameSearch.aspx?name=' . urlencode($taxon['taxon_name']), 'TROPICOS', array('class' => 'btn btn-default')); ?>
    <?=anchor('http://www.ars-grin.gov/cgi-bin/npgs/html/tax_search.pl?' . urlencode($taxon['taxon_name']), 'GRIN', array('class' => 'btn btn-default')); ?>
    <?=anchor('http://www.google.com/search?q=' . urlencode($taxon['taxon_name']), 'Google', array('class' => 'btn btn-default')); ?>
    <?=anchor('http://images.google.com/images?hl=en&lr=&ie=ISO-8859-1&sa=N&tab=wi&q=' . urlencode($taxon['taxon_name']), 'Google Images', array('class' => 'btn btn-default')); ?>
    <?php
        $crit = str_replace("'", '', str_replace(' ', ' and ', $taxon['taxon_name']));
    ?>
    <?=anchor('http://apps.rhs.org.uk/horticulturaldatabase/summary2.asp?crit=' . urlencode($crit), 'RHS Database', array('class' => 'btn btn-default')); ?>
</div>

<?php elseif ($page == 'accession'): ?>
<h3>Taxon info.</h3>
<div class="row taxon-info">
    <div class="term"><div class="col-md-2 field-label">Taxon name</div><div class="col-md-4"><a href="<?=site_url()?>census/taxon/<?=$accession_info['taxon_guid']?>"><?=$accession_info['taxon_name']?></a></div></div>
    <div class="term"><div class="col-md-2 field-label">Common name</div><div class="col-md-4"><?=$accession_info['common_name']?></div></div>
    <div class="term"><div class="col-md-2 field-label">Family</div><div class="col-md-4"><a href="<?=site_url()?>census/search?family=<?=$accession_info['family']?>"><?=$accession_info['family']?></a></div></div>
    <div class="term"><div class="col-md-2 field-label">Australian native</div><div class="col-md-4"><?=($accession_info['isAutralianNative']) ? '&check;' : '&ndash;';?></div></div>
    <div class="term"><div class="col-md-2 field-label">Endangered</div><div class="col-md-4"><?=($accession_info['isEndangered']) ? '&ncheck;' : '&ndash;';?></div></div>
</div>
<?php elseif ($page == 'plant'): ?>
<h3>Taxon info.</h3>
<div class="row taxon-info">
    <div class="term"><div class="col-md-2 field-label">Taxon name</div><div class="col-md-4"><a href="<?=site_url()?>census/taxon/<?=$plant_info['taxon_guid']?>"><?=$plant_info['taxon_name']?></a></div></div>
    <div class="term"><div class="col-md-2 field-label">Common name</div><div class="col-md-4"><?=$plant_info['common_name']?></div></div>
    <div class="term"><div class="col-md-2 field-label">Family</div><div class="col-md-4"><a href="<?=site_url()?>census/search?family=<?=$plant_info['family']?>"><?=$plant_info['family']?></a></div></div>
    <div class="term"><div class="col-md-2 field-label">Australian native</div><div class="col-md-4"><?=($plant_info['isAutralianNative']) ? '&check;' : '&ndash;';?></div></div>
    <div class="term"><div class="col-md-2 field-label">Endangered</div><div class="col-md-4"><?=($plant_info['isEndangered']) ? '&ncheck;' : '&ndash;';?></div></div>
</div>
<?php endif; ?>

<?php if ($page == 'accession'): ?>
<h3>Accession info.</h3>
<div class="row accession-info">
    <div class="term"><div class="col-md-2 field-label">Accession number</div><div class="col-md-4"><?=$accession_info['accession_number']?></div></div>
    <div class="term"><div class="col-md-2 field-label">Provenance type</div><div class="col-md-4"><?=$accession_info['provenance_type_code']?></div></div>
    <div class="term"><div class="col-md-2 field-label">Provenance</div><div class="col-md-4"><?=$accession_info['provenance_history']?></div></div>
    <div class="term"><div class="col-md-2 field-label">Collector</div><div class="col-md-4"><?=$accession_info['collector_name']?></div></div>
    <div class="term"><div class="col-md-2 field-label">Identification status</div><div class="col-md-4"><?=$accession_info['identification_status']?>
        <?php 
            $substr = substr($accession_info['identification_status'],0,1);
            switch ($substr) {
                case '1':
                    echo ' (not verified)';
                    break;
                case '2':
                    echo ' (verified by botanist, some uncertainty)';
                    break;
                case '3':
                    echo ' (verified by botanist)';
                    break;
                default:
                    break;
            }
        ?>
    </div></div>
</div>
<?php elseif ($page == 'plant'): ?>
<h3>Accession info.</h3>
<div class="row accession-info">
    <div class="term"><div class="col-md-2 field-label">Accession_number</div><div class="col-md-4"><a href="<?=site_url()?>census/accession/<?=$plant_info['accession_guid']?>"><?=$plant_info['accession_number']?></a></div></div>
    <div class="term"><div class="col-md-2 field-label">Provenance type</div><div class="col-md-4"><?=$plant_info['provenance_type_code']?></div></div>
    <div class="term"><div class="col-md-2 field-label">Provenance</div><div class="col-md-4"><?=$plant_info['provenance_history']?></div></div>
    <div class="term"><div class="col-md-2 field-label">Collector</div><div class="col-md-4"><?=$plant_info['collector_name']?></div></div>
    <div class="term"><div class="col-md-2 field-label">Identification status</div><div class="col-md-4"><?=$plant_info['identification_status']?>
        <?php 
            $substr = substr($plant_info['identification_status'],0,1);
            switch ($substr) {
                case '1':
                    echo ' (not verified)';
                    break;
                case '2':
                    echo ' (verified by botanist, some uncertainty)';
                    break;
                case '3':
                    echo ' (verified by botanist)';
                    break;
                default:
                    break;
            }
        ?>
    </div></div>
</div>
<?php endif; ?>

<?php if ($page == 'plant'): ?>
<h3>Plant info.</h3>
<div class="row plant-info">
    <div class="term"><div class="col-md-2 field-label">Plant number</div><div class="col-md-4"><?=$plant_info['plant_number']?></div></div>
    <div class="term"><div class="col-md-2 field-label">Location</div><div class="col-md-4"><?=$plant_info['location']?></div></div>
    <div class="term"><div class="col-md-2 field-label">Precinct</div><div class="col-md-4"><?=$plant_info['precinct_name']?></div></div>
    <div class="term"><div class="col-md-2 field-label">Bed</div><div class="col-md-4"><a href="<?=site_url()?>census/bed/<?=$plant_info['bed_guid']?>"><?=$plant_info['bed_name']?></a></div></div>
    <div class="term"><div class="col-md-2 field-label">Grid</div><div class="col-md-4"><a href="<?=site_url()?>census/grid/<?=$plant_info['grid_guid']?>"><?=$plant_info['grid_code']?></a></div></div>
    <div class="term"><div class="col-md-2 field-label">Date planted</div><div class="col-md-4"><?=$plant_info['date_planted']?></div></div>
</div>
<?php if ($plant_info['location'] == 'Melbourne'  && $plant_info['grid_code']): ?>
<div id="tabs" role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#tab-map" data-toggle="tab">Map</a></li>
    </ul>    

    <div class="tab-content">
    <div id="tab-map" class="tab-pane active">
        <div id="map-frame">
            <div id="map" class="map"></div>
            <div id="mouse-position"></div>
            <div id="base-map-toggle" class="text-right">
                <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-default active">
                        <input type="radio" name="base-map-toggle" id="base-map-map" checked>Map
                    </label>
                    <label class="btn btn-default">
                        <input type="radio" name="base-map-toggle" id="base-map-aerial">Aerial photo
                    </label> 
                </div>
            </div>
        </div>
    </div> <!-- #tab-map -->
    </div> <!-- /.tab-content -->
</div>
<?php endif; ?>
<?php endif; ?>

<?php if ($page == 'bed'): ?>
<h3>Bed info.</h3>
<div class="row bed-info">
    <div class="term"><div class="col-md-2 field-label">Location</div><div class="col-md-4"><?=$bed_info['location']['name']?></div></div>
    <?php if (isset($bed_info['section'])): ?>
    <div class="term"><div class="col-md-2 field-label">Section</div><div class="col-md-4"><?=$bed_info['section']['name']?></div></div>
    <?php endif; ?>
    <?php if (isset($bed_info['precinct'])): ?>
    <div class="term"><div class="col-md-2 field-label">Precinct</div><div class="col-md-4"><?=$bed_info['precinct']['name']?></div></div>
    <?php endif; ?>
    <?php if (isset($bed_info['subprecinct'])): ?>
    <?php if (isset($bed_info['bed'])) :?>
    <div class="term"><div class="col-md-2 field-label">Subprecinct</div><div class="col-md-4"><?=anchor(base_url() . 'census/bed/' . $bed_info['subprecinct']['guid'], $bed_info['subprecinct']['name'])?></div></div>
    <?php else: ?>
    <div class="term"><div class="col-md-2 field-label">Subprecinct</div><div class="col-md-4"><?=$bed_info['subprecinct']['name']?></div></div>
    <?php endif; ?>
    <?php endif; ?>
    <?php if (isset($bed_info['bed'])): ?>
    <div class="term"><div class="col-md-2 field-label">Bed</div><div class="col-md-4"><?=$bed_info['bed']['name']?></div></div>
    <?php endif;?>
</div>

<?php endif; ?>

<?php if ($page == 'grid'): ?>
<h3>Grid info</h3>
<div class="row grid-info">
    <div class="term"><div class="col-md-2 field-label">Grid code</div><div class="col-md-4"><?=$grid_info['code']?></div></div>
</div>
<?php endif; ?>

<?php if (in_array($page, array('bed', 'grid'))): ?>
<?php if ($numbers): ?>
<div class="info-box info-box-stats">
    <div class="row">
        <div class="col-md-12 info-box-header">Stats</div>
        <div class="col-md-3 col-sm-4 col-xs-6 field-label">Number of plants</div><div class="col-md-9 col-sm-8 col-xs-6"><?=$numbers['num_plants']?></div>
        <div class="col-md-3 col-sm-4 col-xs-6 field-label">Number of accessions</div><div class="col-md-9 col-sm-8 col-xs-6"><?=$numbers['num_accessions']?></div>
        <div class="col-md-3 col-sm-4 col-xs-6 field-label">Number of taxa</div><div class="col-md-9 col-sm-8 col-xs-6"><?=$numbers['num_taxa']?></div>
        <div class="col-md-3 col-sm-4 col-xs-6 field-label">RBG Cranbourne</div><div class="col-md-9 col-sm-8 col-xs-6"><?=$numbers['cranbourne']?></div>
        <div class="col-md-3 col-sm-4 col-xs-6 field-label">RBG Melbourne</div><div class="col-md-9 col-sm-8 col-xs-6"><?=$numbers['melbourne']?></div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php if ($page == 'accession'):?>
<h3>Plant info.</h3>
<div class="row plant-info">
    <div class="col-md-12">
        <div id="tabs" role="tabpanel">
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation"><a href="#tab-list" data-toggle="tab">Plant list</a></li>
                <?php if (substr($accession_info['accession_number'], 0, 4) == 'RBGM'): ?>
                <li role="presentation" class="active"><a href="#tab-map" data-toggle="tab">Map</a></li>
                <?php endif; ?>
            </ul>    

            <div class="tab-content">
                <div id="tab-list" class="tab-pane">
                    <table class="table table-condensed result-table">
                        <thead>
                            <tr>
                                <th>Plant number</th>
                                <th>Bed</th>
                                <th>Grid</th>
                                <th>Date planted</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($plant_info as $plant): ?>
                            <tr>
                                <td><a href="<?=site_url()?>census/plant/<?=$plant['plant_guid']?>">
                                    <?=$accession_info['accession_number']?>.<?=$plant['plant_number']?>
                                    </a></td>
                                <td>
                                    <?php if($plant['location'] == 'Cranbourne' && $plant['bed_type'] == 'bed'): ?>
                                    <a href="<?=site_url()?>census/bed/<?=$plant['parent_bed_guid']?>"><?=$plant['parent_bed_name']?></a>:
                                    <?php endif; ?>
                                    <a href="<?=site_url()?>census/bed/<?=$plant['bed_guid']?>"><?=$plant['bed_name']?></a>
                                </td>
                                <td><a href="<?=site_url()?>census/grid/<?=$plant['grid_guid']?>">
                                    <?=$plant['grid_code']?>
                                    </a></td>
                                <td><?=$plant['date_planted']?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div> <!-- /#tab-list -->

                <div id="tab-map" class="tab-pane active">
                    <div id="map-frame">
                        <div id="map" class="map"></div>
                        <div id="mouse-position"></div>
                        <div id="base-map-toggle" class="text-right">
                            <div class="btn-group" data-toggle="buttons">
                                <label class="btn btn-default active">
                                    <input type="radio" name="base-map-toggle" id="base-map-map" checked>Map
                                </label>
                                <label class="btn btn-default">
                                    <input type="radio" name="base-map-toggle" id="base-map-aerial">Aerial photo
                                </label> 
                            </div>
                        </div>
                    </div>
                </div> <!-- /#tab-map -->
            </div> <!-- /.tab-content -->
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($page == 'taxon'): ?>
<?php 
    $hasGridCode = FALSE;
    foreach ($plants as $plant) {
        if ($plant['grid_code']) {
            $hasGridCode = TRUE;
            break;
        }
    }
?>
<h3>In Royal Botanic Gardens Victoria</h3>
<?php if ($numbers): ?>
<div class="info-box info-box-stats">
    <div class="row">
        <div class="col-md-12 info-box-header">Stats</div>
        <div class="col-md-3 col-sm-4 col-xs-6 field-label">Number of plants</div><div class="col-md-9 col-sm-8 col-xs-6"><?=$numbers['num_plants']?></div>
        <div class="col-md-3 col-sm-4 col-xs-6 field-label">Number of accessions</div><div class="col-md-9 col-sm-8 col-xs-6"><?=$numbers['num_accessions']?></div>
        <div class="col-md-3 col-sm-4 col-xs-6 field-label">Number of taxa</div><div class="col-md-9 col-sm-8 col-xs-6"><?=$numbers['num_taxa']?></div>
        <div class="col-md-3 col-sm-4 col-xs-6 field-label">RBG Cranbourne</div><div class="col-md-9 col-sm-8 col-xs-6"><?=$numbers['cranbourne']?></div>
        <div class="col-md-3 col-sm-4 col-xs-6 field-label">RBG Melbourne</div><div class="col-md-9 col-sm-8 col-xs-6"><?=$numbers['melbourne']?></div>
    </div>
</div>
<?php endif; ?>
<div id="tabs" role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation"><a href="#tab-list" data-toggle="tab">Plant list</a></li>
        <?php if ($hasGridCode): ?>
        <li role="presentation" class="active"><a href="#tab-map" data-toggle="tab">Map</a></li>
        <?php endif; ?>
    </ul>    

    <div class="tab-content">
        <div id="tab-list" class="tab-pane">
            <table class="table table-condensed result-table taxon">
                <thead>
                    <tr>
                        <th width="10%">Accession no.</th>
                        <th width="5%">Plant no.</th>
                        <th width="10%" class="text-center">Provenance type</th>
                        <th width="10%" class="text-center">Identification status</th>
                        <?php if ($this->session->userdata('id')): ?>
                        <th width="10%" class="text-center">Restricted</th>
                        <th>Grid</th>
                        <?php endif; ?>
                        <th>Date planted</th>
                    </tr>
                </thead>
                <?php foreach ($beds_unique as $key => $bed): ?>
                <tbody>
                    <tr>
                        <?php $colspan = ($this->session->userdata('id')) ? 4: 3; ?>
                        <td colspan="3" class="table_bed_header">
                            <div><a class="bed-link" href="<?=site_url()?>census/bed/<?=$plants[$key]['bed_guid']?>"><?=$bed?></a></div>
                        </td>
                    </tr>
                <?php foreach (array_keys($beds, $bed) as $index): ?>
                    <tr>
                        <td><a href="<?=site_url()?>census/accession/<?=$plants[$index]['accession_guid']?>"><?=$plants[$index]['accession_number']?></a></td>
                        <td><a href="<?=site_url()?>census/plant/<?=$plants[$index]['plant_guid']?>"><?=$plants[$index]['plant_number']?></a></td>
                        <td class="text-center"><?=$plants[$index]['provenance_type_code']?></td>
                        <td class="text-center"><?=$plants[$index]['identification_status']?></td>
                        <?php if ($this->session->userdata('id')): ?>
                        <td class="text-center"><input type="checkbox"<?=($plants[$index]['restricted']) ? ' checked="checked"' : '';?>/></td>
                        <td><a href="<?=site_url()?>census/grid/<?=$plants[$index]['grid_guid']?>"><?=$plants[$index]['grid_code']?></a></td>
                        <?php endif; ?>
                        <td><?=$plants[$index]['date_planted']?></td>
                    </tr>    
                <?php endforeach; ?>
                </tbody>
                <?php endforeach; ?>
            </table>
        </div>

        <?php if ($hasGridCode): ?>
        <div id="tab-map" class="tab-pane active">
            <div id="map-frame">
                <div id="map" class="map"></div>
                <div id="mouse-position"></div>
                <div id="base-map-toggle" class="text-right">
                    <div class="btn-group" data-toggle="buttons">
                        <label class="btn btn-default active">
                            <input type="radio" name="base-map-toggle" id="base-map-map" checked>Map
                        </label>
                        <label class="btn btn-default">
                            <input type="radio" name="base-map-toggle" id="base-map-aerial">Aerial photo
                        </label> 
                    </div>
                </div>
            </div>
        </div> <!-- /#tab-map -->
        <?php endif; ?>
    </div> <!-- /.tab-content -->
</div>
<?php if($regions): ?>
<h3>Around the world</h3>
<div id="tabs2" role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation"><a href="#tab-list2" data-toggle="tab">List</a></li>
        <li role="presentation" class="active"><a href="#tab-map2" data-toggle="tab">Map</a></li>
    </ul>    

    <div class="tab-content">
        <div id="tab-list2" class="tab-pane">
            <table class="table table-condensed region-table">
                <thead>
                    <tr>
                        <th>Level 1</th>
                        <th>Level 2</th>
                        <th>Level 3</th>
                        <th>Level 4</th>
                    </tr>
                </thead>
                <tbody>
            <?php foreach ($regions as $index => $region):?>
                    <?php if ($index == 0 || $region['level1_code'] != $regions[$index-1]['level1_code']): ?>
                    <tr>
                        <td><?=anchor(site_url() . 'census/search?wgs_code=' . urlencode($region['level1_code']), $region['level1_name'])?></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php endif;?>
                    <?php if ($region['level2_code'] && ($index == 0 || ($region['level2_code'] != $regions[$index-1]['level2_code']))): ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td><?=anchor(site_url() . 'census/search?wgs_code=' . urlencode($region['level2_code']), $region['level2_name'])?></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php endif;?>
                    <?php if ($region['level3_code'] && ($index == 0 || ($region['level3_code'] != $regions[$index-1]['level3_code']))): ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td><?=anchor(site_url() . 'census/search?wgs_code=' . urlencode($region['level3_code']), $region['level3_name'])?></td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php endif;?>
                    <?php if ($region['level4_code'] && ($index == 0 || ($region['level4_code'] != $regions[$index-1]['level4_code']))): ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td><?=anchor(site_url() . 'census/search?wgs_code=' . urlencode($region['level4_code']), $region['level4_name'])?></td>
                    </tr>
                    <?php endif;?>
            <?php endforeach;?>
                </tbody>
            </table>
        </div>
        <div id="tab-map2" class="tab-pane active">
            <div id="map-frame2">
                <div id="map-world"></div>
                <div id="mouse-position2"></div>
            </div>
        </div> <!-- /#tab-map -->
    </div> <!-- /.tab-content -->
</div>
<?php endif; ?>
<?php endif; ?>

<?php if ($page == 'bed'): ?>

<?php if (isset($beds) && $beds): ?>
<div class="beds">
    <h3>Beds</h3>
    <table class="table table-condensed bed">
        <?php foreach ($beds as $bed): ?>
        <tr>
            <td>
                <?=anchor(base_url() . 'census/bed/' . $bed['guid'], $bed['bed_name']);?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php endif; ?>

<?php if ($plants): ?>
<?php     
    $hasGridCode = FALSE;
    foreach ($plants as $plant) {
        if ($plant['grid_code']) {
            $hasGridCode = TRUE;
            break;
        }
    }
 ?>

<div id="tabs" role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation"><a href="#tab-list" data-toggle="tab">Plant list</a></li>
        <?php if ($hasGridCode): ?>
        <li role="presentation" class="active"><a href="#tab-map" data-toggle="tab">Map</a></li>
        <?php endif; ?>
    </ul>    

    <div class="tab-content">
        <div id="tab-list" class="tab-pane">
            <?php
                $taxon_guids = array();
                foreach ($plants as $plant) 
                    $taxon_guids[] = $plant['taxon_guid'];
            ?>

            <?php $url = site_url() . 'census/bed/' . $guid . '?';?>
            <?php require_once('navigation.php'); ?>
            <div class="text-center query-result-nav">
                <?=$first?>
                <?=$prev?>
                <span class="query-result-rows"><?=$current?></span>
                <?=$next?>
                <?=$last?>
            </div>

            <table class="table table-condensed result-table bed">
                <thead>
                    <tr>
                        <th width="15%">Accession no.</th>
                        <th width="15%">Plant no.</th>
                        <th width="20%" class="text-center">Provenance type</th>
                        <th width="20%" class="text-center">Identification status</th>
                        <?php if ($this->session->userdata('id')): ?>
                        <th width="20%" class="text-center">Restricted</th>
                        <th width="*">Grid</th>
                        <?php endif; ?>
                    </tr>
                </thead>
            <?php if($plants): ?>
            <?php $taxa = array_unique($taxon_guids); ?>
            <?php foreach ($taxa as $key => $guid):?>
                <?php $taxon = $plants[$key]; ?>
                <tbody>
                    <tr>
                        <?php $colspan = ($this->session->userdata('id')) ? 6 : 4; ?>
                        <td colspan="<?=$colspan?>" class="table-taxon-header">
                            <div class="taxon-info">
                                <div><a href="<?=site_url()?>census/taxon/<?=$taxon['taxon_guid']?>"><?=formatName($taxon['taxon_name']); ?></a>
                                <?php if($taxon['common_name']): ?>
                                &ndash;
                                <span class="common-name"><?=$taxon['common_name']?></span>
                                <?php endif; ?><?=(!$key && $previous_taxon && $previous_taxon == $taxon['taxon_name']) ? ' (ctd)' : '';?>
                                </div>
                                <div><a href="<?=site_url()?>census/search?family=<?=$taxon['family']?>"><span 
                                    class="family"><?=$taxon['family']?></span></a></div>
                            </div>
                        </td>
                    </tr>

                <?php foreach (array_keys($taxon_guids, $guid) as $index): ?>
                    <tr>
                        <td><a href="<?=site_url()?>census/accession/<?=$plants[$index]['accession_guid']?>"><?=$plants[$index]['accession_number']?></a></td>
                        <td><a href="<?=site_url()?>census/plant/<?=$plants[$index]['plant_guid']?>"><?=$plants[$index]['plant_number']?></a></td>
                        <td class="text-center"><?=$plants[$index]['provenance_type_code']?></td>
                        <td class="text-center"><?=$plants[$index]['identification_status']?></td>
                        
                        <?php if ($this->session->userdata('id')): ?>
                        <td class="text-center"><input type="checkbox"<?=($plants[$index]['restricted']) ? ' checked="checked"' : '';?>/></td>
                        <td><a href="<?=site_url()?>census/grid/<?=$plants[$index]['grid_guid']?>"><?=$plants[$index]['grid_code']?></a></td>
                        <?php endif; ?>
                    </tr>    
                <?php endforeach; ?>
                </tbody>

            <?php endforeach; ?>
            <?php endif; ?>
            </table>

            <div class="text-center query-result-nav">
                <?=$first?>
                <?=$prev?>
                <span class="query-result-rows"><?=$current?></span>
                <?=$next?>
                <?=$last?>
            </div>
        </div> <!-- /#tab-list -->

        <?php if ($hasGridCode): ?>
        <div id="tab-map" class="tab-pane active">
            <div id="map-frame">
                <div id="map" class="map"></div>
                <div id="mouse-position"></div>
                <div id="base-map-toggle" class="text-right">
                    <div class="btn-group" data-toggle="buttons">
                        <label class="btn btn-default active">
                            <input type="radio" name="base-map-toggle" id="base-map-map" checked>Map
                        </label>
                        <label class="btn btn-default">
                            <input type="radio" name="base-map-toggle" id="base-map-aerial">Aerial photo
                        </label> 
                    </div>
                </div>
            </div>
        </div> <!-- /#tab-map -->
        <?php endif; ?>
    </div> <!-- /.tab-content -->
</div>
<?php endif; ?>
<?php endif; ?>
        

<?php if ($page == 'grid'): ?>
<div id="tabs" role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation"><a href="#tab-list" data-toggle="tab">Plant list</a></li>
        <li role="presentation" class="active"><a href="#tab-map" data-toggle="tab">Map</a></li>
    </ul>    

    <div class="tab-content">
        <div id="tab-list" class="tab-pane">
            <?php
                $taxon_guids = array();
                foreach ($plants as $plant) 
                    $taxon_guids[] = $plant['taxon_guid'];
            ?>

            <?php $url = site_url() . 'census/grid/' . $guid . '?';?>
            <?php require_once('navigation.php'); ?>
            <div class="text-center query-result-nav">
                <?=$first?>
                <?=$prev?>
                <span class="query-result-rows"><?=$current?></span>
                <?=$next?>
                <?=$last?>
            </div>

            <table class="table table-condensed result-table">
                <thead>
                    <tr>
                        <th width="15%">Accession no.</th>
                        <th width="15%">Plant no.</th>
                        <th width="*%">Bed</th>
                    </tr>
                </thead>
            <?php if($plants): ?>
            <?php $taxa = array_unique($taxon_guids); ?>
            <?php foreach ($taxa as $key => $guid):?>
                <?php $taxon = $plants[$key]; ?>
                <tbody>
                    <tr>
                        <td colspan="5" class="table-taxon-header">
                            <div><a href="<?=site_url()?>census/taxon/<?=$taxon['taxon_guid']?>"><?=formatName($taxon['taxon_name']); ?></a>
                            <?php if($taxon['common_name']): ?>
                            &ndash;
                            <span class="common-name"><?=$taxon['common_name']?></div>
                            <?php endif; ?><?=(!$key && $previous_taxon && $previous_taxon == $taxon['taxon_name']) ? ' (ctd)' : '';?>
                            </div>
                            <div><a href="<?=site_url()?>census/search?family=<?=$taxon['family']?>"><span 
                                        class="family"><?=$taxon['family']?></div></a></div>

                        </td>
                    </tr>

                <?php foreach (array_keys($taxon_guids, $guid) as $index): ?>
                    <tr>
                        <td><a href="<?=site_url()?>census/accession/<?=$plants[$index]['accession_guid']?>"><?=$plants[$index]['accession_number']?></a></td>
                        <td><a href="<?=site_url()?>census/plant/<?=$plants[$index]['plant_guid']?>"><?=$plants[$index]['plant_number']?></a></td>
                        <td><a href="<?=site_url()?>census/bed/<?=$plants[$index]['bed_guid']?>"><?=$plants[$index]['bed_name']?></a></td>
                    </tr>    
                <?php endforeach; ?>
                </tbody>

            <?php endforeach; ?>
            <?php endif; ?>
            </table>

            <div class="text-center query-result-nav">
                <?=$first?>
                <?=$prev?>
                <span class="query-result-rows"><?=$current?></span>
                <?=$next?>
                <?=$last?>
            </div>
        </div> <!-- /#tab-list -->

        <div id="tab-map" class="tab-pane active">
            <div id="map-frame">
                <div id="map" class="map"></div>
                <div id="mouse-position"></div>
                <div id="base-map-toggle" class="text-right">
                    <div class="btn-group" data-toggle="buttons">
                        <label class="btn btn-default active">
                            <input type="radio" name="base-map-toggle" id="base-map-map" checked>Map
                        </label>
                        <label class="btn btn-default">
                            <input type="radio" name="base-map-toggle" id="base-map-aerial">Aerial photo
                        </label> 
                    </div>
                </div>
            </div>
        </div> <!-- /#tab-map -->
    </div> <!-- /.tab-content -->
</div>
<?php endif; ?>

</div> <!-- /.container -->
<?php require_once('footer.php');?>
