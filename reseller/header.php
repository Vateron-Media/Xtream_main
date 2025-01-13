<!-- I'll deal with it later. -->



<?php if ($rPermissions["is_reseller"]) { ?>
    <li class="notification-list">
        <a class="nav-link text-white waves-effect" href="#" role="button">
            <i class="fe-dollar-sign noti-icon text-warning"></i>
            <?php if (floor($rUserInfo["credits"]) == $rUserInfo["credits"]) {
                echo number_format($rUserInfo["credits"], 0);
            } else {
                echo number_format($rUserInfo["credits"], 2);
            } ?>
        </a>
    </li>
<?php }
if (($rPermissions["is_reseller"]) && ($rPermissions["reseller_client_connection_logs"])) { ?>
    <li class="has-submenu">
        <a href="#"><i class="mdi mdi-information-outline mdi-18px text-danger"></i><?= $_["logs"] ?>
            <div class="arrow-down"></div>
        </a>
        <ul class="submenu">
            <li><a href="./live_connections"><span class="mdi mdi-account-network-outline mdi-18px"></span>
                    <?= $_["live_connections"] ?></a></li>
            <li><a href="./user_activity"><span class="mdi mdi-file-document-outline mdi-18px">
                        <?= $_["activity_logs"] ?></a></li>
        </ul>
    </li>
<?php } ?>
<li class="has-submenu">
    <a href="#"> <i class="mdi mdi-account-outline mdi-18px text-pink"></i><?= $_["users"] ?>
        <div class="arrow-down"></div>
    </a>
    <ul class="submenu">
        <?php if ((!$rSettings["disable_trial"]) && ($rPermissions["total_allowed_gen_trials"] > 0) && ($rUserInfo["credits"] >= $rPermissions["minimum_trial_credits"])) { ?>
            <li><a href="./user_reseller?trial"><span class="mdi mdi-account-plus-outline mdi-18px"></span>
                    <?= $_["generate_trial"] ?></a></li>
        <?php } ?>
        <div class="separator"></div>
        <li><a href="./user_reseller"><span class="mdi mdi-account-plus-outline mdi-18px"></span>
                <?= $_["add_user"] ?></a></li>
        <li><a href="./users"><span class="mdi mdi-account-multiple-outline mdi-18px">
                    <?= $_["manage_users"] ?></a></li>
        <div class="separator"></div>
        <li><a href="./user_reseller?mag"><span class="mdi mdi-account-plus-outline mdi-18px"></span>
                <?= $_["add_mag"] ?></a></li>
        <li><a href="./mags"><span class="mdi mdi-account-multiple-outline mdi-18px">
                    <?= $_["manage_mag_devices"] ?></a></li>
        <div class="separator"></div>
        <li><a href="./user_reseller?e2"><span class="mdi mdi-account-plus-outline mdi-18px"></span>
                <?= $_["add_enigma"] ?></a></li>
        <li><a href="./enigmas"><span class="mdi mdi-account-multiple-outline mdi-18px">
                    <?= $_["manage_enigma_devices"] ?></a></li>
    </ul>
</li>
<?php
if (($rPermissions["is_reseller"]) && ($rPermissions["create_sub_resellers"])) { ?>
    <li class="has-submenu">
        <a href="#"> <i class="mdi mdi-account-multiple-outline mdi-18px text-primary"></i><?= $_["reg_users"] ?>
            <div class="arrow-down"></div>
        </a>
        <ul class="submenu">
            <?php if ($rPermissions["is_admin"]) { ?>
                <li><a href="./reg_user"><span class="mdi mdi-account-multiple-plus-outline mdi-18px">
                            <?= $_["add_subreseller"] ?></a></li>
            <?php } else { ?>
                <li><a href="./subreseller"><span class="mdi mdi-account-multiple-plus-outline mdi-18px">
                            <?= $_["add_subreseller"] ?></a></li>
            <?php } ?>
            <li><a href="./reg_users"><span class="mdi mdi-account-multiple-outline mdi-18px">
                        <?= $_["manage_subresellers"] ?></a></li>
        </ul>
    </li>
<?php }
if (($rPermissions["is_reseller"]) && ($rPermissions["reset_stb_data"])) { ?>
    <li class="has-submenu">
        <a href="#"> <i class="mdi mdi-play-circle-outline mdi-18px text-info"></i><?= $_["content"] ?>
            <div class="arrow-down"></div>
        </a>
        <ul class="submenu">
            <li><a href="./streams"><?= $_["streams"] ?></a></li>
            <li><a href="./movies"><?= $_["movies"] ?></a></li>
            <li><a href="./series"><?= $_["series"] ?></a></li>
            <li><a href="./episodes"><?= $_["episodes"] ?></a></li>
            <li><a href="./radios"><?= $_["stations"] ?></a></li>
        </ul>
    </li>
<?php }
if ($rPermissions["is_reseller"]) { ?>
    <li class="has-submenu">
        <a href="#"> <i class="mdi mdi-email-outline mdi-18px text-warning"></i><?= $_["support"] ?>
            <div class="arrow-down"></div>
        </a>
        <ul class="submenu">
            <li><a href="./ticket"><span class="mdi mdi-message-text-outline mdi-18px">
                        <?= $_["create_ticket"] ?></a></li>
            <li><a href="./tickets"><span class="mdi mdi-message-settings-variant mdi-18px">
                        <?= $_["manage_tickets"] ?></a></li>
            <?php if ($rPermissions["allow_import"]) { ?>
                <li><a href="./resellersmarters"><span class="mdi mdi-message-settings-variant mdi-18px"> Reseller API Key</a>
                </li>
            <?php } ?>
        </ul>
    </li>
    <li class="has-submenu">
        <a href="#"> <i class="mdi mdi-apps mdi-18px text-success"></i>Apps Iptv <div class="arrow-down"></div></a>
        <ul class="submenu">
            <li><a href="./duplexplay"><span class="mdi mdi-account-star mdi-18px"> DUPLEX
                        IPTV</a></li>
            <li><a href="./netiptv"><span class="mdi mdi-account-star mdi-18px"> NET
                        IPTV</a></li>
            <li><a href="./siptv"><span class="mdi mdi-account-star mdi-18px"> SMART
                        IPTV</a></li>
            <li><a href="./siptvextreme"><span class="mdi mdi-account-star mdi-18px"> IPTV
                        EXTREME</a></li>
            <li><a href="./nanomid"><span class="mdi mdi-account-star mdi-18px"> NANOMID</a>
            </li>
            <li><a href="./ss-iptv"><span class="mdi mdi-account-star mdi-18px"> SS IPTV</a>
            </li>
        </ul>
    </li>
<?php }
if (($rPermissions["is_reseller"]) && ($rSettings["active_mannuals"])) { ?>
    <li>
        <a href="./reseller_mannuals"> <i
                class="mdi mdi-book-open-page-variant mdi-18px text-info"></i><?= $_["mannuals"] ?></a>
    </li>
<?php } ?>