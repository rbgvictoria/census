<?php require_once('header.php'); ?>
<div class="container">
<h1>Search</h1>

<?php if ($query_string): ?>
<div class="info-box info-box-terms">
    <div class="row">
    <div class="col-md-12 info-box-header">Search terms</div>
    <?php 
        $qarr = explode('&', $query_string);
        $terms = array();
        foreach ($qarr as $bit) {
            $bits = explode('=', $bit);
            $terms[$bits[0]] = (isset($bits[1])) ? $bits[1] : false;
        }
    ?>
    <?php if (isset($terms['taxon'])): ?>
    <div><div class="col-md-3 col-sm-4 col-xs-6 field-label">Scientific name</div><div class="col-md-9 col-sm-8 col-xs-6"><?=urldecode($terms['taxon']);?>%</div></div>
    <?php endif; ?>
    <?php if (isset($terms['common_name'])): ?>
    <div><div class="col-md-3 col-sm-4 col-xs-6 field-label">Common name</div><div class="col-md-9 col-sm-8 col-xs-6"><?=urldecode($terms['common_name'])?></div></div>
    <?php endif; ?>
    <?php if (isset($terms['family'])): ?>
    <div><div class="col-md-3 col-sm-4 col-xs-6 field-label">Family</div><div class="col-md-9 col-sm-8 col-xs-6"><?=urldecode($terms['family'])?></div></div>
    <?php endif; ?>
    <?php if (isset($terms['location'])): ?>
    <div><div class="col-md-3 col-sm-4 col-xs-6 field-label">Location</div><div class="col-md-9 col-sm-8 col-xs-6"><?=urldecode($terms['location'])?></div></div>
    <?php endif; ?>
    <?php if (isset($terms['precinct'])): ?>
    <div><div class="col-md-3 col-sm-4 col-xs-6 field-label">Precinct</div><div class="col-md-9 col-sm-8 col-xs-6"><?=urldecode($terms['precinct'])?></div></div>
    <?php endif; ?>
    <?php if (isset($terms['subprecinct'])): ?>
    <div><div class="col-md-3 col-sm-4 col-xs-6 field-label">Subprecinct</div><div class="col-md-9 col-sm-8 col-xs-6"><?=urldecode($terms['subprecinct'])?></div></div>
    <?php endif; ?>
    <?php if (isset($terms['bed'])): ?>
    <div><div class="col-md-3 col-sm-4 col-xs-6 field-label">Bed</div><div class="col-md-9 col-sm-8 col-xs-6"><?=urldecode($terms['bed'])?></div></div>
    <?php endif; ?>
    <?php if (isset($terms['grid'])): ?>
    <div><div class="col-md-3 col-sm-4 col-xs-6 field-label">Grid</div><div class="col-md-9 col-sm-8 col-xs-6"><?=$terms['grid']?></div></div>
    <?php endif; ?>
    <?php if (isset($terms['wgs_code'])): ?>
    <div><div class="col-md-3 col-sm-4 col-xs-6 field-label">WGS region</div><div class="col-md-9 col-sm-8 col-xs-6"><?=$terms['wgs_code']?></div></div>
    <?php endif; ?>
    <?php if (isset($terms['provenance_type'])): ?>
    <div><div class="col-md-3 col-sm-4 col-xs-6 field-label">Provenance type</div><div class="col-md-9 col-sm-8 col-xs-6"><?=$terms['provenance_type']?></div></div>
    <?php endif; ?>
    <?php if (isset($terms['identification_status'])): ?>
    <div><div class="col-md-3 col-sm-4 col-xs-6 field-label">Identification verification status</div><div class="col-md-9 col-sm-8 col-xs-6"><?=$terms['identification_status']?></div></div>
    <?php endif; ?>
    <?php if (isset($terms['inclDeaccessioned']) && $terms['inclDeaccessioned']): ?>
    <div><div class="col-md-3 col-sm-4 col-xs-6 field-label">Incl. deaccessioned</div><div class="col-md-9 col-sm-8 col-xs-6">true</div></div>
    <?php endif; ?>
    </div> <!-- /.terms -->
</div>
<?php endif; ?>


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
<?php if (isset($plants)): ?>
<div class="download text-right">
    <a class="btn btn-default" href="<?=site_url();?>census/download_plant_list?<?=$query_string?>">Download plant list</a>
    <a class="btn btn-default" href="<?=site_url();?>census/download_taxon_list?<?=$query_string?>">Download species list</a>
</div>
<?php 
    $hasGridCode = FALSE;
    foreach ($plants as $plant) {
        if ($plant['grid_code']) {
            $hasGridCode = TRUE;
            break;
        }
    }

    if ($this->session->userdata('id')) {
        if (isset($plants[0]['deaccessioned'])) {
            $colspan = 9;
        }
        else {
            $colspan = 8;
        }
    }
    else {
        $colspan = 6;
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
            <div id="tab-list" class="tab-pane" >

                <?php $url = site_url() . 'census/search?' . $query_string . '&';?>
                <?php require_once('navigation.php'); ?>
                <div class="text-center query-result-nav">
                    <?=$first?>
                    <?=$prev?>
                    <span class="query-result-rows"><?=$current?></span>
                    <?=$next?>
                    <?=$last?>
                </div>

                <table class="table table-condensed result-table search" width="100%">
                    <thead>
                        <tr>
                            <th width="10%">Accession no.</th>
                            <th width="5%">Plant no.</th>
                            <th width="10%">Location</th>
                            <th width="*">Bed</th>
                            <?php if ($this->session->userdata('id')): ?>
                            <th width="6%">Grid</th>
                            <?php endif; ?>
                            <th width="8%" class="text-center">Provenance type</th>
                            <th width="8%" class="text-center">Identification status</th>
                            <?php if ($this->session->userdata('id')): ?>
                            <th width="8%" class="text-center">Restricted</th>
                            <?php if (isset($plants[0]['deaccessioned'])): ?>
                            <th width="4%" class="text-center">&dagger;</th>
                            <?php endif; ?>
                            <?php endif; ?>
                        </tr>
                    </thead>
                <?php if($plants): ?>
                <?php $taxa = array_unique($taxon_guids); ?>
                <?php foreach ($taxa as $key => $guid):?>
                    <?php $taxon = $plants[$key]; ?>
                    <tbody>
                        <tr>
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
                            <td><?=$plants[$index]['location']?></td>
                            <td>
                                <?php if($plants[$index]['location'] == 'Cranbourne' && $plants[$index]['bed_type'] == 'bed'): ?>
                                <a href="<?=site_url()?>census/bed/<?=$plants[$index]['parent_bed_guid']?>"><?=$plants[$index]['parent_bed_name']?></a>: 
                                <?php endif; ?>
                                <a href="<?=site_url()?>census/bed/<?=$plants[$index]['bed_guid']?>"><?=$plants[$index]['bed_name']?></a>
                            </td>
                            <?php if ($this->session->userdata('id')): ?>
                            <td><a href="<?=site_url()?>census/grid/<?=$plants[$index]['grid_guid']?>"><?=$plants[$index]['grid_code']?></a></td>
                            <?php endif; ?>
                            <td class="text-center"><?=$plants[$index]['provenance_type_code']?></td>
                            
                            <?php 
                                if (!$this->session->userdata('id')) {
                                    $idstatus = ($plants[$index]['identification_status']) ? (integer) $plants[$index]['identification_status'] : FALSE;
                                }
                                else {
                                    $idstatus = $plants[$index]['identification_status'];
                                }
                            ?>
                            <td class="text-center"><?=$idstatus?></td>
                            <?php if ($this->session->userdata('id')): ?>
                            <td class="text-center"><input type="checkbox"<?=($plants[$index]['restricted']) ? ' checked="checked"' : '';?>/></td>
                            <?php if (isset($plants[$index]['deaccessioned'])): ?>
                            <td class="text-center"><input type="checkbox"<?=($plants[$index]['deaccessioned']) ? ' checked="checked"' : '';?>/></td>
                            <?php endif; ?>
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
            <?php if($hasGridCode): ?>
            <div id="tab-map" class="tab-pane active" >
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
    </div> <!-- /[role=tabpanel] -->
<?php elseif (isset($accepted_names) && $accepted_names): ?>

<div class="row">
    <div class="col-md-12">
        <div class="alert alert-warning" role="alert">
            <p><b>The following synonyms that match your search criteria were found:</b></p>
            <?php foreach ($accepted_names as $name): ?>
            <?php 
                $t = $terms;
                $t['taxon'] = $name['accepted_name'];
                $qstring = array();
                foreach ($t as $k => $v) {
                    $qstring[] = $k . '=' . $v;
                }
                $qstring = implode('&', $qstring);
                
            ?>
            <div><?=$name['taxon_name']?> = <?=anchor(site_url() . '/census/search?' . $qstring, $name['accepted_name'])?></div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
    
<?php endif; ?>
</div> <!-- /.container -->
<?php require_once('footer.php'); ?>