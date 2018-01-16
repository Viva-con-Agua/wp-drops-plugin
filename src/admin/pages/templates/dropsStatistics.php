<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-1">
        <div id="postbox-container-1" class="postbox-container">
            <div class="postbox ">
                <h3 class="no-hover"><span><?= __('Drops - the ultimate user management connector'); ?></span></h3>
                <div class="inside"><p>
                        <?= __('Drops stores informations about logins so that you can see the peaks of the pool usage'); ?>


                        <form action="" method="post">
                            Show logins from
                            <input type="text" value="<?= $this->page['statistics']['from'] ?>" id="stat_from" name="stat_from" />

                            to
                            <input type="text" value="<?= $this->page['statistics']['to'] ?>" id="stat_to" name="stat_to" />

                            <?php submit_button('Apply filter'); ?>
                        </form>



                    <table class="wp-list-table widefat fixed striped posts">

                        <colgroup>
                            <col width="100">
                            <col width="100">
                            <col width="*">
                        </colgroup>

                        <thead>
                        <tr>
                            <th class="manage-column column-title column-primary">Date</th>
                            <th class="manage-column column-title column-primary">Logins</th>
                            <th class="manage-column column-title column-primary">Graph</th>
                        </tr>
                        </thead>

                        <?php foreach ($this->page['statistics']['logins'] AS $entry) { ?>

                            <tr>
                                <td><strong><?= date('d.m.y H:i', strtotime($entry->login_time)); ?></strong></td>
                                <td><strong><?= $entry->login_count; ?></strong></td>
                                <td style="vertical-align: middle;"><strong>
                                        <div style="background-color: blue; height: 5px; width: <?= $entry->login_count * 5 ?>px;"></div>
                                </strong></td>
                            </tr>

                        <?php } ?>

                    </table>

                </p></div>
            </div>

        </div>
    </div>
</div>
