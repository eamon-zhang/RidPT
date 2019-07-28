<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 7/21/2019
 * Time: 8:19 AM
 */
?>

<!-- TODO fix nav miss in sm -->
<nav id="nav" class="navbar navbar-default navbar-static-top navbar-custom" role="navigation">
    <div class="container">
        <div class="collapse navbar-collapse navbar-collapse-custom">
            <ul class="nav navbar-nav nav-justified">
                <li><a href="/"><?= __('nav_index') ?></a></li>
                <li><a href="/forums"><?= __('nav_forums') ?></a></li> <!-- TODO  -->
                <li><a href="/collections"><?= __('nav_collections') ?></a></li> <!-- TODO  -->
                <li><a href="/torrents"><?= __('nav_torrents') ?></a></li>
                <li><a href="/torrent/upload"><?= __('nav_upload') ?></a></li>
                <li><a href="/torrents/request"><?= __('nav_requests') ?></a></li> <!-- TODO  -->
                <li><a href="/subtitles"><?= __('nav_subtitles') ?></a></li> <!-- TODO  -->
                <li><a href="/site/rules"><?= __('nav_rules') ?></a></li> <!-- TODO  -->
                <li><a href="/site/staff"><?= __('nav_staff') ?></a></li> <!-- TODO  -->
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?= __('nav_more') ?> <b class="caret"></b></a>
                    <ul class="dropdown-menu" role="menu"><!-- FIXME class="active" in dropdown-munu -->
                        <li><a href="/site/topten"><?= __('nav_topten') ?></a></li> <!-- TODO  -->
                        <li class="divider"></li>
                        <li><a href="/site/stats"><?= __('nav_stats') ?></a></li> <!-- TODO  -->
                        <li><a href="/site/log"><?= __('nav_log') ?></a></li> <!-- TODO  -->
                    </ul>
                </li>
            </ul> <!-- END .navbar-nav -->
        </div><!-- END .navbar-collapse -->
    </div>
</nav> <!-- END /nav -->
<div class="clearfix"></div>

<div id="info_block">
    <div id="info_block_line_1">
        Welcome Back, <a href="/user" data-user-id="<?= app()->site->getCurUser()->getId() ?>"><?= app()->site->getCurUser()->getUsername() ?></a>&nbsp;
        <span data-item="logout"><!--suppress HtmlUnknownTarget -->[<a href="/auth/logout">Logout</a>]</span>
        <?php if (app()->site->getCurUser()->getClass(true) > \apps\models\User::ROLE_FORUM_MODERATOR): ?>
            <span><!--suppress HtmlUnknownTarget -->[<a href="/admin">Admin Panel</a>]</span>
        <?php endif; ?>
        <span data-item="favour"><!--suppress HtmlUnknownTarget -->[<a href="/torrents/favour">Favour</a>]</span>
        <span data-item="invite" data-invite="<?= app()->site->getCurUser()->getInvites() ?>" data-temp-invite="<?= app()->site->getCurUser()->getTempInvitesSum() ?>">
            <span class="color-invite">Invite [<a href="/user/invite">Send</a>]: </span> <?= app()->site->getCurUser()->getInvites() ?>
            <?php if (app()->site->getCurUser()->getTempInvitesSum() > 0): ?>
                <span data-toggle="tooltip" data-placement="bottom" title="Temporarily Invites" class="text-primary">(+<?= app()->site->getCurUser()->getTempInvitesSum() ?>)</span>
            <?php endif; ?>
        </span>
        <span data-item="bonus" data-bonus="">
            <span class="color-bonus">Bonus: </span> <a href="#">2345234</a> <!-- TODO  -->
        </span>
        <span data-item="bet">[<a href="#"><span class="color-bet">Bet(22)</span></a>]</span> <!-- TODO  -->
        <span data-item="blackjack">[<a href="#"><span class="color-blackjack">Blackjack</span></a>]</span> <!-- TODO  -->
        <div class="pull-right">
            <span data-item="chang_lang">
                <a href="#"><span class="flag flag-chn"></span></a>&nbsp;
                <a href="#"><span class="flag flag-hkg"></span></a>&nbsp;
                <a href="#"><span class="flag flag-gbr"></span></a>&nbsp;
            </span> <!-- TODO -->
            Now: <?= date('H:i (P)') ?>
        </div>
    </div>
    <div id="info_block_line_2">
        <span data-item="ratio" data-ratio="<?= $this->e(app()->site->getCurUser()->getRatio()) ?>">
            <span class="color-ratio">Ratio:</span> <?= is_string(app()->site->getCurUser()->getRatio()) ? app()->site->getCurUser()->getRatio() : round(app()->site->getCurUser()->getRatio(),3) ?>
        </span>&nbsp;
        <span data-item="uploaded" data-uploaded="<?= $this->e(app()->site->getCurUser()->getUploaded()) ?>">
            <span class="color-seeding">Uploaded:</span> <?= $this->e(app()->site->getCurUser()->getUploaded(), 'format_bytes') ?>
        </span>&nbsp;
        <span data-item="download" data-downloaded="<?= $this->e(app()->site->getCurUser()->getDownloaded()) ?>">
            <span class="color-leeching">Downloaded:</span> <?= $this->e(app()->site->getCurUser()->getDownloaded(), 'format_bytes') ?>
        </span>&nbsp;
        <span data-item="bt_activity">
            BT Activity:
            <span class="fas fa-arrow-up fa-fw color-seeding" data-seeding="<?= app()->site->getCurUser()->getActiveSeed() ?>">&nbsp;<?= app()->site->getCurUser()->getActiveSeed() ?></span>&nbsp;&nbsp;
            <span class="fas fa-arrow-down fa-fw color-leeching" data-leeching="<?= app()->site->getCurUser()->getActiveLeech() ?>">&nbsp;<?= app()->site->getCurUser()->getActiveLeech() ?></span>&nbsp;&nbsp;
            <?php if (app()->site->getCurUser()->getActivePartial()): ?>
            <span class="fas fa-minus fa-fw color-partial" data-partial="<?= app()->site->getCurUser()->getActivePartial() ?>">&nbsp;<?= app()->site->getCurUser()->getActivePartial() ?></span>&nbsp;&nbsp;
            <?php endif; ?>
        </span>
        <span data-item="connectable" data-connectable="IPv4/IPv6">
            <span class="color-connectable">Connectable:</span>
            IPv4/IPv6 <!-- TODO -->
        </span>&nbsp;
        <div class="pull-right">
            <a href="/user/message"><span class="fas fa-envelope fa-fw red"></span>Message Box (15)</a> <!-- TODO -->
            <a href="#"><span class="fas fa-user-friends fa-fw color-friends"></span></a> <!-- TODO -->
            <a href="#"><span class="fas fa-rss-square fa-fw color-rss"></span></a>  <!-- TODO -->
        </div>
    </div>
</div> <!-- END /info_block -->
