<!-- view: users_edit -->
<!-- this clears the form every time you navigate away or reload the page -->
<body onLoad="document.forms[0].reset();" onUnload="document.forms[0].reset();">
<div id="back">
    <h2>
        <div class="employee whitebox">
            <div id="date_time">
                <?= date('m/d/y'); ?> <span id="time"></span>
            </div>
            <div>
                <form method="post"> <!-- action="/public/admin/manage_users.php">  -->
                    <?php if (!empty($row)) { ?>
                        <div class="labels">
                            <p>userid: </p>
                            <p>username: </p>
                            <p><label for="first_name"> First Name: </label></p>
                            <p><label for="last_name"> Last Name: </label></p>
                            <p><label for="focus"> Password: </label></p>
                        </div>
                        <div class="info" style="float:left;">
                            <?= $row['userid']; ?> <br/>
                            <input type='hidden' name='userid' value='<?= $row["userid"]; ?>'/>
                            <?= $row['username']; ?> <br/>
                            <input id="first_name" type='text'     name='fname'    value='<?= $row["fname"]; ?>'/> <br/>
                            <input id="last_name"  type='text'     name='lname'    value='<?= $row["lname"]; ?>'/> <br/>
                            <input id='focus'      type='password' name='password' value='' autofocus/> <br/>
                        </div>
                        <div class="clear"></div>

                    <?php } ?>
                    <div align="center">
                        <button type="submit" name="submit" class="edit_up" formaction="manage_users.php">back</button>
                        <button type='submit' name='update' class='edit_up' formaction='manage_users.php'>Submit</button>
                    </div>
                </form>
            </div>
            <?php if (isset($message)) {
                echo $message;
            } ?>
            <br/>
        </div>
    </h2>
</div>
<?php render("footer", __DIR__, []); ?>
</body>
</html>
