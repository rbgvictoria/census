<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>RBG Census</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    
    <link rel="shortcut icon" href="http://www.rbg.vic.gov.au/common/img/favicon.ico">
    <link rel="stylesheet" href="http://openlayers.org/en/v3.4.0/css/ol.css" type="text/css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" />
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>css/jqueryui.autocomplete.css" />
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>css/explore.css" />

    <script src="<?=base_url()?>js/jspath.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/proj4js/2.2.1/proj4.js" type="text/javascript"></script>
    <script src="http://epsg.io/28355.js" type="text/javascript"></script>
    <script src="http://openlayers.org/en/v3.4.0/build/ol.js" type="text/javascript"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
    <script src="<?=base_url()?>js/jquery.rbgcensus.explore.js"></script>
</head>
  <body>
      <nav class="navbar navbar-fixed-top navbar-inverse" role="navigation" id="rbgv-branding">
        <div class="container-fluid">
          <!-- Brand and toggle get grouped for better mobile display -->
          <div class="navbar-header">
              <a class="navbar-brand" href="http://data.rbg.vic.gov.au/dev/rbgcensus/">
                  <img src="http://data.rbg.vic.gov.au/dev/rbgcensus/css/images/rbg-vic-logo-transparent-34x25.png" 
                       alt="" 
                       class="rbgv-logo-navbar"
                  />
              </a>
            <a class="navbar-brand" href="http://data.rbg.vic.gov.au/dev/rbgcensus/">Royal Botanic Gardens Victoria</a>
          </div>
          <ul class="nav navbar-nav navbar-right social-media">
            <li><a href="https://twitter.com/RBG_Victoria" target="_blank"><span class="icon icon-twitter-solid"></span></a></li>
            <li><a href="https://www.facebook.com/BotanicGardensVictoria" target="_blank"><span class="icon icon-facebook-solid"></span></a></li>
            <li><a href="https://instagram.com/royalbotanicgardensvic/" target="_blank"><span class="icon icon-instagram-solid"></span></a></li>
          </ul>
        </div><!-- /.container-fluid -->
      </nav>

      <div class="navbar-offset"></div>
      <div id="map"></div>
      <div class="row main-row">
        <div class="col-sm-4 col-md-3 sidebar sidebar-left pull-left">
          <div class="panel-group sidebar-body" id="accordion-left">
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" href="#layers">
                    <i class="fa fa-list-alt"></i>
                    Layers
                  </a>
                  <span class="pull-right slide-submenu">
                    <i class="fa fa-chevron-left"></i>
                  </span>
                </h4>
              </div>
              <div id="layers" class="panel-collapse collapse in">
                <div class="panel-body list-group">
                    <div class="h4">Overlays</div>
                    <div id="rbg-grid" class="list-group-item checkbox">
                      <label>
                        <input type="checkbox"> RBG grid
                      </label>
                    </div>
                    <div id="rbg-map" class="list-group-item checkbox">
                      <label>
                        <input type="checkbox"> RBG map
                      </label>
                    </div>
                    <h4>Collections</h4>
                    <div id="commemorative" class="list-group-item checkbox">
                      <label>
                        <input type="checkbox"> <img src="http://data.rbg.vic.gov.au/dev/rbgcensus/img/tree-icons/yellow_40/tree65_yellow_40.png"
                                alt="" height="20" width="20"/> Commemorative trees
                      </label>
                    </div>
                    <div id="national-trust" class="list-group-item checkbox">
                      <label>
                          <input type="checkbox"> <img src="http://data.rbg.vic.gov.au/dev/rbgcensus/img/tree-icons/red_40/tree68_red_40.png"
                                alt="" height="20" width="20"/> National Trust listed trees
                      </label>
                    </div>
                  <!--a href="#" class="list-group-item">
                    <i class="fa fa-globe"></i> Bing
                  </a>
                  <a href="#" class="list-group-item">
                    <i class="fa fa-globe"></i> WMS
                  </a-->
                </div>
              </div>
            </div>
            <!--div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" href="#properties">
                    <i class="fa fa-list-alt"></i>
                    Properties
                  </a>
                </h4>
              </div>
              <div id="properties" class="panel-collapse collapse in">
                <div class="panel-body">
                  <p>
                  Lorem ipsum dolor sit amet, vel an wisi propriae. Sea ut graece gloriatur. Per ei quando dicant vivendum. An insolens appellantur eos, doctus convenire vis et, at solet aeterno intellegebat qui.
                  </p>
                  <p>
                  Elitr minimum inciderint qui no. Ne mea quaerendum scriptorem consequuntur. Mel ea nobis discere dignissim, aperiam patrioque ei ius. Stet laboramus eos te, his recteque mnesarchum an, quo id adipisci salutatus. Quas solet inimicus eu per. Sonet conclusionemque id vis.
                  </p>
                  <p>
                  Eam vivendo repudiandae in, ei pri sint probatus. Pri et lorem praesent periculis, dicam singulis ut sed. Omnis patrioque sit ei, vis illud impetus molestiae id. Ex viderer assentior mel, inani liber officiis pro et. Qui ut perfecto repudiandae, per no hinc tation labores.
                  </p>
                  <p>
                  Pro cu scaevola antiopam, cum id inermis salutatus. No duo liber gloriatur. Duo id vitae decore, justo consequat vix et. Sea id tale quot vitae.
                  </p>
                </div>
              </div>
            </div-->
          </div>
        </div>
        <div class="col-sm-4 col-md-6 mid"></div>
        <!--div class="col-sm-4 col-md-3 sidebar sidebar-right pull-right">
          <div class="panel-group sidebar-body" id="accordion-right">
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" href="#taskpane">
                    <i class="fa fa-tasks"></i>
                    Task Pane
                  </a>
                  <span class="pull-right slide-submenu">
                    <i class="fa fa-chevron-right"></i>
                  </span>
                </h4>
              </div>
              <div id="taskpane" class="panel-collapse collapse in">
                <div class="panel-body">
                  <p>
                  Lorem ipsum dolor sit amet, vel an wisi propriae. Sea ut graece gloriatur. Per ei quando dicant vivendum. An insolens appellantur eos, doctus convenire vis et, at solet aeterno intellegebat qui.
                  </p>
                  <p>
                  Elitr minimum inciderint qui no. Ne mea quaerendum scriptorem consequuntur. Mel ea nobis discere dignissim, aperiam patrioque ei ius. Stet laboramus eos te, his recteque mnesarchum an, quo id adipisci salutatus. Quas solet inimicus eu per. Sonet conclusionemque id vis.
                  </p>
                  <p>
                  Eam vivendo repudiandae in, ei pri sint probatus. Pri et lorem praesent periculis, dicam singulis ut sed. Omnis patrioque sit ei, vis illud impetus molestiae id. Ex viderer assentior mel, inani liber officiis pro et. Qui ut perfecto repudiandae, per no hinc tation labores.
                  </p>
                  <p>
                  Pro cu scaevola antiopam, cum id inermis salutatus. No duo liber gloriatur. Duo id vitae decore, justo consequat vix et. Sea id tale quot vitae.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div-->
      </div>
      <div class="mini-submenu mini-submenu-left pull-left">
        <i class="fa fa-list-alt"></i>
      </div>
      <div class="mini-submenu mini-submenu-right pull-right">
        <i class="fa fa-tasks"></i>
      </div>
    </div>
  </body></html>