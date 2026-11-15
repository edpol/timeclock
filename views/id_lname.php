<?php global $session, $database; ?>
<!-- view: id_lname -->
<!-- this clears the form every time you navigate away or reload the page -->
<body>
<div id="back">
    <h2>
        <span class="employee whitebox">
            <div style="text-align:center;">
                <?= $title; ?><br/>
            </div>
            <div id="date_time">
                <?= date('m/d/y'); ?> <span id="time"></span>
            </div>
            <!-- OK, we need to lookup in users table NOT employee table -->
            <form id="form1" action="<?= $levels . $form_page; ?>" autocomplete="off" method="post"
                  data-enter-submit>
                <?= $session->csrfField(); ?>
                <div class="labels">
                    <p><label for="barcode">ID Number:</label> </p>
                    <p><label for="lastname">Last Name:</label> </p>
                </div>
                <div style="float:left;">
                    <input type="text" name="barcode" id="barcode" value=""/><br/>
                    <input type="text" name="lastname" id="lastname" value="" autofocus/><br/>
                    <!-- onclick="openWindow('hello.htm')" -->
                    <input type="hidden" name="calling_url" value="<?= $return_page; ?>"/>
                </div>
                <br class="clear"/>
                <div style="padding-top:20px; text-align:center;">
                    <button type="submit" name="submit" class="edit_btn">submit</button>
                    <button type="submit" name="submit" class="edit_btn" formaction="menu.php">back</button>
                </div>
            </form>
            <?php if (!empty($message)) echo $message; ?>
        </span>
    </h2>
</div>
<?php render("footer", __DIR__); ?>
</body>
<?php render("close_html", __DIR__); ?>

