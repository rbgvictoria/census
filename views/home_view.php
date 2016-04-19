<?php require_once('header.php'); ?>

<div class="container">
<?=form_open('census/search', array(
    'method' => 'get', 
    'class' => 'form-horizontal'
)); ?>

<div class="form-group">
    <?=form_label('Location', 'search_location', array('class' => 'col-md-2 control-label')); ?>
    <div class="col-md-4">
        <?=form_dropdown('location', array_merge(array('' => ''), 
                array('Cranbourne' => 'Cranbourne', 'Melbourne' => 'Melbourne')), FALSE, 'id="search_location" class="form-control"');?>
    </div>
</div>

<div class="form-group">
    <div class="col-md-2 text-right">
        <?=form_label('Species name', 'taxon', array('class' => 'control-label')); ?>
        <span data-toggle="popover" data-content="Scientific name of the species or lower taxon. A wild card will be 
              added automatically at the end, so filling in 'Acacia' will get records of all species of Acacia."><i class="fa fa-info-circle"></i></span>
    </div>
    <div class="col-md-4">
        <?=form_input(array('name' => 'taxon', 'id' => 'search_taxon', 'class' => 'form-control', 'placeholder' => 'Enter taxon name...')); ?>
    </div>
</div>

<div class="form-group">
    <?=form_label('Common name', 'search_common_name', array('class' => 'col-md-2 control-label')); ?>
    <div class="col-md-4">
        <?=form_input(array('name' => 'common_name', 'id' => 'search_common_name', 'class' => 'form-control', 'placeholder' => 'Enter common name...')); ?>
    </div>
</div>

<div class="form-group">
    <?=form_label('Family', 'search_family', array('class' => 'col-md-2 control-label')); ?>
    <div class="col-md-4">
        <?=form_input(array('name' => 'family', 'id' => 'search_family', 'class' => 'form-control', 'placeholder' => 'Enter family...')); ?>
    </div>
</div>

<div class="form-group">
    <?=form_label('Precinct', 'search_precinct', array('class' => 'col-md-2 control-label')); ?>
    <div class="col-md-4">
        <?=form_dropdown('precinct', array_merge(array('' => ''), $precincts), FALSE, 'id="search_precinct" class="form-control"');?>
    </div>
</div>

<div class="form-group">
    <?=form_label('Subprecinct', 'search_subprecinct', array('class' => 'col-md-2 control-label')); ?>
    <div class="col-md-4">
        <?=form_dropdown('subprecinct', array_merge(array('' => ''), $subprecincts), FALSE, 'id="search_subprecinct" class="form-control"');?>
    </div>
</div>

<div class="form-group">
    <?=form_label('Bed', 'search_bed', array('class' => 'col-md-2 control-label')); ?>
    <div class="col-md-4">
        <?=form_dropdown('bed', array_merge(array('' => ''), $beds), FALSE, 'id="search_bed" class="form-control"');?>
    </div>
</div>
    
<?php if($this->session->userdata('id')): ?>
<div class="form-group">
    <?=form_label('Grid', 'search_grid', array('class' => 'col-md-2 control-label')); ?>
    <div class="col-md-4">
        <?=form_dropdown('grid', array_merge(array('' => ''), $gridcodes), FALSE, 'id="search_grid" class="form-control"');?>
    </div>
</div>
<?php endif; ?>

<div class="form-group">
    <div class="col-md-2 text-right">
        <?=form_label('World region', 'search_wgs'); ?>
        <span data-toggle="popover" data-content="Regions in the world where species in the Gardens occur naturally. Rather than the ISO system of 
              countries and states/provinces, we still use the biogeographically more meaningful 
              <a href=&quot;https://en.wikipedia.org/wiki/World_Geographical_Scheme_for_Recording_Plant_Distributions&quot;>World Geographic Scheme
              for Recording Plant Distributions</a> (WGS) of the Taxonomic
              Databases Working Group (TDWG). The WGS recognises regions at four levels, which are concatenated here. The field will auto-complete, 
              so you can just start typing a country or state name."><i class="fa fa-info-circle"></i></span>
    </div>
    <div class="col-md-4">
        <?=form_input(array('name' => 'wgs_fullname', 'id' => 'search_wgs', 'class' => 'form-control', 'placeholder' => 'Enter WGS region')); ?>
    </div>
</div>
   
<div class="form-group">
    <div class="col-md-2 text-right">
        <label for="search_provenance_type">Provenance type</label>
        <span data-toggle="popover" data-content="Provenance indicates where a plant in the Gardens originated from. We recognise four types:
              <ul>
              <li>(W) Accessioned from wild source, meaning the plant has been collected from the wild</li>
              <li>(Z) Propagule(s) from a wild source plant in cultivation, meaning the plant has been grown from seeds or other parts taken from a 
              cultivated plant that was originally collected in the wild</li>
              <li>(G) Accession not of wild source</li>
              <li>(U) Unknown or insufficient data</li>
              </ul>
              This field has not been populated consitently, so using it might result in fewer records than one might expect." 
              data-html="true" data-popover-width="500"><i class="fa fa-info-circle"></i></span>
    </div>
    <div class="col-md-4">
        <select name="provenance_type" id="search_provenance_type" class="form-control">
            <option value=""></option>
            <option value="W">(W) Accession of wild source</option>
            <option value="Z">(Z) Propagule(s) from a wild source plant in cultivation</option>
            <option value="G">(G) Accession not of wild source</option>
            <option value="U">(U) Unknown/insufficient data</option>
        </select>
    </div>
</div>

<div class="form-group">
    <div class="col-md-2 text-right">
        <label for="search_identification_status">Identification verification status</label>
        <span data-toggle="popover" data-content="The identification verification status indicates 
              whether the identification has been verified by a botanist"><i class="fa fa-info-circle"></i></span>
        
    </div>
    <div class="col-md-4">
        <select name="identification_status" id="search_identification_status" class="form-control">
            <option value=""></option>
            <option value="1">(1) Not verified</option>
            <option value="2">(2) Verified by botanist, some uncertainty</option>
            <?php if ($this->session->userdata('id')): ?>
            <option value="2E">(2E)</option>
            <option value="2L">(2L)</option>
            <option value="2N">(2N)</option>
            <option value="2X">(2X)</option>
            <?php endif; ?>
            <option value="3">(3) Verified by botanist</option>
        </select>
    </div>
</div>
    
<div class="form-group">
    <?=form_label('Order by', 'order_results', array('class' => 'col-md-2 control-label')); ?>
    <div class="col-md-4">
        <?=form_dropdown('order_results', array('taxon_name' => 'Species name', 'family' => 'Family', 'bed' => 'Bed'), FALSE, 'id="order_results" class="form-control"');?>
    </div>
</div>
    
<?php if($this->session->userdata('id')): ?>
<div class="form-group">
  <div class="col-md-offset-2 col-md-4">
    <div class="checkbox">
        <label>
            <?=form_checkbox(array('name' => 'inclDeaccessioned', 'id' => 'incl_deaccessioned', 'value' => '1')); ?> Include deaccessioned collections
        </label>
    </div>
  </div>
</div>
<?php endif; ?>




<div class="form-group">
    <div class="col-md-6 text-right">
        <?=form_submit('submit', 'Submit', 'class="btn"'); ?>
        <?=form_reset('reset', 'Reset', 'class="btn"'); ?>
    </div>
</div>


<?=form_close(); ?>
</div> <!-- /.container -->

<?php require_once('footer.php'); ?>