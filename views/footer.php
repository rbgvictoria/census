        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <?php if (isset($date_last_modified)): ?>
                        <div class="last-updated">Last updated: <?=date('d F Y', strtotime($date_last_modified))?></div>
                        <?php endif;?>
                    </div>
                    <div class="col-md-6 text-right">
                        <div class="licence"><a href="http://creativecommons.org/licenses/by/3.0/au/"><img src="http://i.creativecommons.org/l/by/1.0/88x31.png" alt="CC BY 3.0 AU" height="31" width="88" /></a></div>
                        <div class="copyright">&copy; <?=date('Y')?> Royal Botanic Gardens Victoria</div>
                    </div>
                </div>
            </div>
        </footer>
</body> <!-- /container -->
</html>