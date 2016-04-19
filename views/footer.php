        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <?php if (isset($date_last_modified)): ?>
                        <div class="last-updated">Last updated: <?=date('d F Y', strtotime($date_last_modified))?></div>
                        <div class="copyright">&copy; <?=date('Y')?> Royal Botanic Gardens Victoria</div>
                        <?php endif;?>
                    </div>
                    <div class="col-md-6 text-right">
                        <a rel="license" class="cc" href="http://creativecommons.org/licenses/by-nc-nd/4.0/">
                            <img alt="Creative Commons Licence" src="https://i.creativecommons.org/l/by-nc-nd/4.0/88x31.png" />
                        </a>
                        This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nc-nd/4.0/">Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License</a>.
                    </div>
                </div>
            </div>
        </footer>
</body> <!-- /container -->
</html>

