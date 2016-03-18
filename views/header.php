<?php
    function formatName($name) {
        $formattedname = '<span class="taxon-name">';
        $formattedname .= str_replace(array(' subsp. ', ' var. ', ' f. '), array(
            '</span> subsp. <span class="taxon-name">',
            '</span> var. <span class="taxon-name">',
            '</span> f. <span class="taxon-name">',
        ), $name);
        $formattedname .= '</span>';
        return $formattedname;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>RBG Census</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    
    <link rel="shortcut icon" href="http://www.rbg.vic.gov.au/common/img/favicon.ico">
    <link rel="stylesheet" href="http://openlayers.org/en/v3.4.0/css/ol.css" type="text/css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>css/jqueryui.autocomplete.css" />
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>css/main.css" />

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/proj4js/2.2.1/proj4.js" type="text/javascript"></script>
    <script src="http://epsg.io/28355.js" type="text/javascript"></script>
    <script src="http://openlayers.org/en/v3.4.0/build/ol.js" type="text/javascript"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    <!--script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script-->
    <script src="<?=base_url()?>js/jquery-ui-1.11.4-autocomplete.min.js"></script>
    <script src="<?=base_url()?>js/jquery.rbgcensus.js"></script>
    <script src="<?=base_url()?>js/jquery.rbgcensus.ol3.js"></script>
    <?php if (isset($cql_filter)): ?>
    <!--script src="<?=base_url()?>js/jquery.rbgcensus.ol3.js"></script-->
    <script type="text/javascript">
        var cql_filter = "<?=$cql_filter?>";
    </script>
    <?php endif; ?>
    <?php if (isset($js)): ?>
        <?php foreach ($js as $file): ?>
    <script type="text/javascript" src="<?=$file?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

</head>
<body class="rbgcensus">
    <nav class="navbar navbar-default navbar-inverse" role="navigation" id="rbgv-branding">
      <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <a class="navbar-brand" href="http://www.rbg.vic.gov.au">
                <img src="http://data.rbg.vic.gov.au/dev/rbgcensus/css/images/rbg-vic-logo-transparent-48x35.png" 
                     alt="" 
                     class="rbgv-logo-navbar"
                />
            </a>
          <a class="navbar-brand" href="http://www.rbg.vic.gov.au">Royal Botanic Gardens Victoria</a>
        </div>
        <ul class="nav navbar-nav navbar-right social-media">
            <li><a href="https://twitter.com/RBG_Victoria" target="_blank"><span class="icon icon-twitter-solid"></span></a></li>
            <li><a href="https://www.facebook.com/BotanicGardensVictoria" target="_blank"><span class="icon icon-facebook-solid"></span></a></li>
            <li><a href="https://instagram.com/royalbotanicgardensvic/" target="_blank"><span class="icon icon-instagram-solid"></span></a></li>
        </ul>
      </div><!-- /.container-fluid -->
    </nav>

    <nav class="navbar navbar-default" id="rbg-census-navigation">
      <div class="container">
          <div class="row">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li><a href="<?=site_url()?>"><span class="glyphicon glyphicon-home"></a></li>
            <li><a href="<?=site_url()?>">Search</a></li>
            <li><a href="<?=site_url()?>explore">Explore</a></li>
          </ul>
          <?=form_open('census/search', array('method' => 'get', 'class' => 'navbar-form navbar-right')); ?>
            <div class="form-group">
              <?=form_input(array('name' => 'q', 'class' => 'form-control', 'placeholder' => 'Enter taxon name...')); ?>
            </div>
            <input type="submit" class="btn btn-default" value="Find"/>
          <?=form_close(); ?>
        </div><!--/.navbar-collapse -->
          </div><!--/.row -->
      </div><!--/.container -->
    </nav>
    <div class="page-header">
        <div class="container">
            <div class="inner">
                <?php if ($this->session->userdata('ip_address') == '172.16.1.72'): ?>
                <div class="login">
                    <?php if ($this->session->userdata('id')): ?>
                        Staff version | <?=anchor('admin/logout', 'Log out'); ?>
                    <?php else: ?>
                        <?=anchor('admin/login', 'Log in', array('class' => 'hidden-login-link')); ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <div id="name-and-subtitle">
                    <div id="site-name">
                        <a href="<?=site_url()?>">Living Collection Census</a>
                    </div>
                    <div id="subtitle">Royal Botanic Gardens Victoria</div>
                </div>
            </div>
        </div>
    </div>
